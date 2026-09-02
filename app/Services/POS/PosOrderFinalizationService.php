<?php

namespace App\Services\POS;

use App\Models\Order;
use App\Models\Customer;
use App\Models\ProductBarcode;
use App\Models\HsnMaster;
use App\Models\OrderVatTax;
use App\Models\Account;
use App\Models\CounterMaster;
use App\Models\FinancialYear;
use App\Models\VoucherSequence;
use App\Models\PosPaymentAttempt;
use App\Models\PosCart;
use App\Services\Accounting\VoucherService;
use App\Services\BranchContext;
use Illuminate\Support\Facades\DB;
use Exception;

class PosOrderFinalizationService
{
    protected $voucherService;
    protected $branchContext;

    public function __construct(VoucherService $voucherService, BranchContext $branchContext)
    {
        $this->voucherService = $voucherService;
        $this->branchContext = $branchContext;
    }

    /**
     * Finalize POS billing transaction.
     *
     * @param array $data Validated checkout payload
     * @param \App\Models\Shop $shop Current shop model
     * @param \App\Models\FinancialYear $financialYear Active financial year
     * @param string|null $idempotencyKey Client-supplied idempotency key
     * @param string|null $paymentAttemptId Payment attempt UUID if paid via EDC
     * @return Order
     * @throws Exception
     */
    public function finalize(array $data, $shop, $financialYear, ?string $idempotencyKey = null, ?string $paymentAttemptId = null): Order
    {
        // 1. Idempotency Check
        if ($idempotencyKey) {
            $existing = Order::withoutGlobalScopes()
                ->where('idempotency_key', $idempotencyKey)
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($data, $shop, $financialYear, $idempotencyKey, $paymentAttemptId) {
            
            // 2. Validate active shift
            $counterId = $data['counter_id'] ?? null;
            if (!$counterId) {
                throw new Exception("Counter ID is required for POS checkout.");
            }

            $shiftService = app(\App\Services\POS\POSShiftService::class);
            $shift = $shiftService->getActiveShift($shop->id, $counterId, auth()->id() ?? $data['user_id'] ?? null);
            if (!$shift) {
                throw new Exception("No active shift found for this cashier/counter.");
            }

            // 3. Lock Cart/Session if name is supplied
            if (!empty($data['cart_name'])) {
                PosCart::where('shop_id', $shop->id)
                    ->where('name', $data['cart_name'])
                    ->lockForUpdate()
                    ->first();
            }

            // 4. Lock and Validate Payment Attempt
            $paymentAttempt = null;
            if ($paymentAttemptId) {
                $paymentAttempt = PosPaymentAttempt::where('id', $paymentAttemptId)
                    ->lockForUpdate()
                    ->first();

                if (!$paymentAttempt) {
                    throw new Exception("Payment attempt not found.");
                }

                if ($paymentAttempt->status !== 'success') {
                    throw new Exception("Cannot finalize order: Payment attempt status is not SUCCESS.");
                }

                if ($paymentAttempt->finalized_at) {
                    // Already finalized - Idempotency handle
                    $existingOrder = Order::withoutGlobalScopes()
                        ->where('payment_attempt_id', $paymentAttemptId)
                        ->first();
                    if ($existingOrder) {
                        return $existingOrder;
                    }
                    throw new Exception("Payment attempt has already been finalized, but no associated order was found.");
                }
            }

            // 5. Lock affected stock products to prevent race conditions
            $barcodes = collect($data['items'])->pluck('barcode')->toArray();
            $barcodeModels = ProductBarcode::whereIn('barcode_number', $barcodes)
                ->where('shop_id', $shop->id)
                ->where('is_sold', 0)
                ->lockForUpdate()
                ->get()
                ->keyBy('barcode_number');

            // Revalidate all barcodes & stock
            $productIds = [];
            foreach ($data['items'] as $item) {
                $barcodeStr = $item['barcode'] ?? null;
                if ($barcodeStr) {
                    $bc = $barcodeModels->get($barcodeStr);
                    if (!$bc) {
                        throw new Exception("Barcode " . $barcodeStr . " is invalid or sold out.");
                    }
                    $productIds[] = $bc->product_id;
                } elseif (!empty($item['product_id'])) {
                    $productIds[] = $item['product_id'];
                }
            }

            $products = \App\Models\Product::whereIn('id', array_unique($productIds))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 6. Resolve or Create Customer
            $customerId = $data['customer_id'] ?? null;
            if (!$customerId && !empty($data['customer_phone'])) {
                $cust = Customer::whereHas('user', function ($q) use ($data) {
                    $q->where('phone', $data['customer_phone']);
                })->first();

                if ($cust) {
                    $customerId = $cust->id;
                } else {
                    $firstName = ($data['customer_name'] ?? null) ?: 'Walk-in';
                    $lastName = 'Customer';
                    $nameParts = explode(' ', $firstName, 2);
                    if (count($nameParts) > 1) {
                        $firstName = $nameParts[0];
                        $lastName = $nameParts[1];
                    }

                    $userReq = new \Illuminate\Http\Request([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $data['customer_phone'],
                        'email' => ($data['customer_email'] ?? null) ?: (str()->random(10) . '@walkin.com'),
                        'is_active' => 1
                    ]);

                    $user = \App\Repositories\UserRepository::registerNewUser($userReq);
                    $user->assignRole(\App\Enums\Roles::CUSTOMER->value);
                    $customerModel = \App\Repositories\CustomerRepository::storeByRequest($user);
                    $customerId = $customerModel->id;
                }
            }

            if (!$customerId) {
                $walkin = Customer::whereHas('user', function ($q) {
                    $q->where('phone', '9999999999');
                })->first();

                if (!$walkin) {
                    $userReq = new \Illuminate\Http\Request([
                        'first_name' => 'Walk-in',
                        'last_name' => 'Customer',
                        'phone' => '9999999999',
                        'email' => 'walkin@customer.com',
                        'is_active' => 1
                    ]);
                    $user = \App\Repositories\UserRepository::registerNewUser($userReq);
                    $user->assignRole(\App\Enums\Roles::CUSTOMER->value);
                    $walkin = \App\Repositories\CustomerRepository::storeByRequest($user);
                }
                $customerId = $walkin->id;
            }

            // 7. Resolve Counter Prefix and Voucher Sequence
            $counter = CounterMaster::where('shop_id', $shop->id)
                ->where('id', $counterId)
                ->first();
            $seqPrefix = ($counter && $counter->voucher_prefix) ? $counter->voucher_prefix : ($shop->prefix ?? 'RC');

            $branchId = $this->branchContext->getCurrentBranchId();
            $branch = $this->branchContext->getCurrentBranch();

            $seqKey = 'POS_ORDER_COUNTER_' . $counterId;
            $seq = VoucherSequence::firstOrCreate(
                [
                    'shop_id' => $shop->id,
                    'financial_year_id' => $financialYear->id,
                    'voucher_type' => $seqKey,
                    'branch_id' => $branchId,
                ],
                [
                    'prefix' => $seqPrefix,
                    'padding' => 6,
                    'current_no' => $branch ? ($branch->voucher_range_start - 1) : 0,
                    'range_start' => $branch ? $branch->voucher_range_start : null,
                    'range_end' => $branch ? $branch->voucher_range_end : null,
                    'reset_policy' => 'yearly',
                ]
            );

            $seq = VoucherSequence::where('id', $seq->id)->lockForUpdate()->first();
            $nextVal = $seq->current_no + 1;

            if ($seq->range_end && $nextVal > $seq->range_end) {
                throw new Exception("Order sequence exceeded the range limit of {$seq->range_end} for this branch.");
            }

            $seq->current_no = $nextVal;
            $seq->save();

            $orderCode = str_pad((string)$nextVal, $seq->padding, '0', STR_PAD_LEFT);
            $prefix = $seq->prefix;
            if ($this->branchContext->isBranch() && $this->branchContext->getBranchCode()) {
                $prefix = $this->branchContext->getBranchCode() . '-' . $prefix;
            }

            // 8. Aggregate required quantities per product & validate stock availability
            $requiredQtyByProduct = [];
            foreach ($data['items'] as $item) {
                $barcodeStr = $item['barcode'] ?? null;
                $bc = $barcodeStr ? $barcodeModels->get($barcodeStr) : null;
                $pId = $bc ? $bc->product_id : ($item['product_id'] ?? null);
                if ($pId) {
                    $requiredQtyByProduct[$pId] = ($requiredQtyByProduct[$pId] ?? 0) + (int)$item['qty'];
                }
            }

            foreach ($requiredQtyByProduct as $pId => $reqQty) {
                $prod = $products->get($pId);
                if ($prod) {
                    $avail = $prod->available_stock;
                    if ($avail < $reqQty) {
                        throw new Exception("Product " . $prod->name . " does not have enough stock. Required: " . $reqQty . ", Available: " . $avail);
                    }
                }
            }

            // 9. Process items and update stock
            $subtotal = 0;
            $totalTax = 0;
            $discount = 0;
            $itemsData = [];
            $vatTaxGroups = [];

            foreach ($data['items'] as $item) {
                $barcodeStr = $item['barcode'] ?? null;
                $bc = $barcodeStr ? $barcodeModels->get($barcodeStr) : null;
                $productId = $bc ? $bc->product_id : ($item['product_id'] ?? null);
                $product = $products->get($productId);
                $inwardProd = $bc ? $bc->inwardProduct : null;

                $qty = (int)$item['qty'];
                $rate = (float)$item['rate'];
                $discPercent = (float)($item['disc_percent'] ?? 0);
                $discAmt = round($rate * $qty * ($discPercent / 100), 2);
                $lineTotal = ($rate * $qty) - $discAmt;

                $taxPercent = 0;
                $taxAmt = 0;
                $taxName = null;

                $hsnId = $inwardProd?->hsn_master_id ?? $product->hsn_master_id ?? null;
                if ($hsnId) {
                    $hsn = HsnMaster::with(['subHsn', 'vattax'])->find($hsnId);
                    if ($hsn) {
                        $taxPercent = (float)$hsn->getTaxForPrice($rate);
                        $taxName = 'GST';
                        $inclusiveTax = \App\Services\Accounting\GSTPostingService::extractInclusiveTax($lineTotal, $taxPercent);
                        $taxAmt = $inclusiveTax['tax_amount'];
                    }
                }

                $subtotal += $lineTotal;
                $totalTax += $taxAmt;
                $discount += $discAmt;

                if ($taxPercent > 0 && $taxName) {
                    $taxKey = $taxName . '_' . $taxPercent;
                    if (!isset($vatTaxGroups[$taxKey])) {
                        $vatTaxGroups[$taxKey] = (object)[
                            'name' => $taxName,
                            'percentage' => $taxPercent,
                            'amount' => 0
                        ];
                    }
                    $vatTaxGroups[$taxKey]->amount += $taxAmt;
                }

                $itemsData[] = [
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'rate' => $rate,
                    'disc_percent' => $discPercent,
                    'disc_amt' => $discAmt,
                    'tax_percent' => $taxPercent,
                    'tax_amt' => $taxAmt,
                    'tax_name' => $taxName,
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'salesman_id' => $item['salesman_id'] ?? null,
                    'inward_invoice_id' => $inwardProd?->inward_invoice_id,
                    'inward_product_id' => $inwardProd?->id,
                    'barcode_number' => $bc?->barcode_number
                ];
            }

            $payable = $subtotal;
            $actualPaid = isset($data['paid_amount']) ? (float)$data['paid_amount'] : $payable;
            if ($actualPaid > $payable) {
                $actualPaid = $payable;
            }

            $isSplit = !empty($data['split_payments']);
            $orderPaymentMethod = \App\Enums\PaymentMethod::CASH->value;

            if ($isSplit) {
                $orderPaymentMethod = 'Split Payment';
            } else {
                $orderPaymentMethod = $data['payment_method'] === 'cash' 
                    ? \App\Enums\PaymentMethod::CASH->value 
                    : \App\Enums\PaymentMethod::ONLINE->value;
            }

            if ($paymentAttempt) {
                $orderPaymentMethod = $paymentAttempt->provider === 'paytm'
                    ? ($paymentAttempt->type === 'card' ? \App\Enums\PaymentMethod::PAYTM_CARD->value : \App\Enums\PaymentMethod::PAYTM_UPI->value)
                    : ($paymentAttempt->type === 'card' ? \App\Enums\PaymentMethod::PHONEPE_CARD->value : \App\Enums\PaymentMethod::PHONEPE_UPI->value);
            }

            $isEInvoiceSetting = generaleSetting('setting')?->is_e_invoice ?? false;
            $eInvoiceIrn = null;
            $eInvoiceAckNo = null;
            $eInvoiceAckDate = null;

            if ($isEInvoiceSetting) {
                $eInvoiceIrn = md5(uniqid('', true)) . '-' . substr(md5(uniqid('', true)), 0, 30);
                $eInvoiceAckNo = (string)rand(10000000000000, 99999999999999);
                $eInvoiceAckDate = now();
            }

            // Primary Bill Salesman: from $data['salesman_id'], or fallback to first item's salesman
            $primarySalesmanId = !empty($data['salesman_id']) ? (int)$data['salesman_id'] : null;
            if (!$primarySalesmanId) {
                foreach ($itemsData as $it) {
                    if (!empty($it['salesman_id'])) {
                        $primarySalesmanId = (int)$it['salesman_id'];
                        break;
                    }
                }
            }

            // Cashier / Payment Collector: from $data['cashier_id'] or logged-in user
            $cashierId = !empty($data['cashier_id']) ? (int)$data['cashier_id'] : (auth()->id() ?? null);

            // Billing Speed & Duration
            $billingDurationSeconds = isset($data['billing_duration_seconds']) ? (int)$data['billing_duration_seconds'] : 0;
            $billingStartedAt = !empty($data['billing_started_at']) ? \Carbon\Carbon::parse($data['billing_started_at']) : null;

            // Handle Exchange Return if attached to checkout
            $isExchange = !empty($data['exchange_return_order_id']) && !empty($data['exchange_return_items']);
            $exchangeReturnModel = null;
            $exchangeRefundTotal = 0;
            $exchangeInstruction = null;

            if ($isExchange) {
                $returnService = app(\App\Services\POS\POSReturnService::class);
                $exchangeRefundTotal = 0;
                $returnNos = [];
                $returnedItemsFormatted = [];

                $itemsByOrder = [];
                foreach ($data['exchange_return_items'] as $item) {
                    $itemOrderId = $item['order_id'] ?? $data['exchange_return_order_id'];
                    $itemsByOrder[$itemOrderId][] = $item;
                }

                foreach ($itemsByOrder as $orderId => $orderItems) {
                    $exchangeReturnModel = $returnService->processReturn([
                        'order_id' => $orderId,
                        'refund_method' => ($data['payment_method'] ?? 'cash') === 'cash' ? 'cash' : 'online',
                        'counter_id' => $counterId,
                        'items' => $orderItems
                    ], $shop, $financialYear);

                    $exchangeRefundTotal += (float)$exchangeReturnModel->total_amount;
                    if ($exchangeReturnModel->return_no) {
                        $returnNos[] = $exchangeReturnModel->return_no;
                    }

                    foreach ($exchangeReturnModel->products as $p) {
                        $prodModel = $p->product;
                        $hsnMaster = $prodModel?->hsnMaster;
                        if (!$hsnMaster && !empty($prodModel?->hsn_master_id)) {
                            $hsnMaster = \App\Models\HsnMaster::find($prodModel->hsn_master_id);
                        }
                        $hsnCode = (string)($prodModel?->hsn ?? $prodModel?->hsn_code ?? $hsnMaster?->hsn_code ?? '-');
                        $taxRate = $hsnMaster ? (float)$hsnMaster->getTaxForPrice((float)$p->rate) : (float)($prodModel?->vatTax?->percentage ?? 0);

                        $returnedItemsFormatted[] = [
                            'name' => $p->product?->name ?? 'Product',
                            'barcode' => $p->barcode_number,
                            'qty' => $p->qty,
                            'rate' => (float)$p->rate,
                            'total' => (float)($p->rate * $p->qty),
                            'reason' => $p->reason,
                            'hsn' => $hsnCode ?: '-',
                            'tax_percentage' => $taxRate
                        ];
                    }
                }

                $origOrder = Order::withoutGlobalScopes()->find($data['exchange_return_order_id']);
                $origInvoiceNo = $data['exchange_original_invoice_no'] ?? implode(', ', array_unique($returnNos));

                $exchangeData = [
                    'type' => 'EXCHANGE',
                    'original_order_id' => $data['exchange_return_order_id'],
                    'original_invoice_no' => $origInvoiceNo,
                    'returned_total' => $exchangeRefundTotal,
                    'return_no' => implode(', ', $returnNos),
                    'returned_items' => count($returnedItemsFormatted) > 0 ? $returnedItemsFormatted : $data['exchange_return_items']
                ];
                $exchangeInstruction = json_encode($exchangeData);
                $payable = max(0, $subtotal - $exchangeRefundTotal);
                $actualPaid = isset($data['paid_amount']) ? (float)$data['paid_amount'] : $payable;
                if ($actualPaid > $payable) {
                    $actualPaid = $payable;
                }
            }

            // 9. Create Order Model
            $order = Order::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'shop_id' => $shop->id,
                'branch_id' => $branchId,
                'pos_order' => true,
                'original_id' => $isExchange ? $data['exchange_return_order_id'] : null,
                'customer_id' => $customerId,
                'counter_id' => $counterId,
                'salesman_id' => $primarySalesmanId,
                'cashier_id' => $cashierId,
                'billing_duration_seconds' => $billingDurationSeconds,
                'billing_started_at' => $billingStartedAt,
                'order_code' => $orderCode,
                'prefix' => $prefix,
                'delivery_charge' => 0,
                'total_amount' => $subtotal + $discount,
                'coupon_discount' => $discount,
                'tax_amount' => $totalTax,
                'payable_amount' => $payable,
                'payment_method' => $orderPaymentMethod,
                'order_status' => \App\Enums\OrderStatus::DELIVERED->value,
                'instruction' => $exchangeInstruction ?: ($data['note'] ?? null),
                'payment_status' => \App\Enums\PaymentStatus::PAID->value,
                'e_invoice_irn' => $eInvoiceIrn,
                'e_invoice_ack_no' => $eInvoiceAckNo,
                'e_invoice_ack_date' => $eInvoiceAckDate,
                'payment_attempt_id' => $paymentAttemptId,
                'idempotency_key' => $idempotencyKey,
            ]);

            // Save Payments
            if ($isSplit) {
                foreach ($data['split_payments'] as $sp) {
                    $payment = \App\Models\Payment::create([
                        'amount' => (float)$sp['amount'],
                        'currency' => generaleSetting('setting')?->currency ?? 'INR',
                        'payment_method' => $sp['method'] === 'cash' ? \App\Enums\PaymentMethod::CASH->value : \App\Enums\PaymentMethod::ONLINE->value,
                        'is_paid' => true,
                    ]);
                    $order->payments()->attach($payment->id);
                }
            } else {
                $payment = \App\Models\Payment::create([
                    'amount' => $actualPaid,
                    'currency' => generaleSetting('setting')?->currency ?? 'INR',
                    'payment_method' => $orderPaymentMethod,
                    'is_paid' => true,
                ]);
                $order->payments()->attach($payment->id);
            }

            // 10. Attach products and mark barcodes as sold
            foreach ($itemsData as $it) {
                $order->products()->attach($it['product_id'], [
                    'barcode_number' => $it['barcode_number'],
                    'salesman_id' => $it['salesman_id'] ?? $primarySalesmanId,
                    'quantity' => $it['qty'],
                    'price' => round($it['rate'] - ($it['disc_amt'] / $it['qty']), 2),
                    'mrp' => round($it['rate'], 2),
                    'discount' => round($it['disc_percent'], 2),
                    'discount_amount' => round($it['disc_amt'], 2),
                    'tax_percentage' => round($it['tax_percent'], 2),
                    'tax_amount' => round($it['tax_amt'], 2),
                    'vat_tax_name' => $it['tax_name'],
                    'color' => $it['color'],
                    'size' => $it['size'],
                    'inward_invoice_id' => $it['inward_invoice_id'],
                    'inward_product_id' => $it['inward_product_id']
                ]);

                ProductBarcode::where('barcode_number', $it['barcode_number'])
                    ->update(['is_sold' => 1]);

                $soldQty = (int)($it['qty'] ?? 1);

                // Deduct sold quantity from specific inward product variant Qty
                if (!empty($it['inward_product_id'])) {
                    $inwardProd = \App\Models\InwardProduct::find($it['inward_product_id']);
                    if ($inwardProd) {
                        $newInwardQty = max(0, (int)$inwardProd->quantity - $soldQty);
                        $inwardProd->update(['quantity' => $newInwardQty]);
                    }
                }

                // Deduct sold quantity from Product Qty
                if (!empty($it['product_id'])) {
                    $prod = \App\Models\Product::find($it['product_id']);
                    if ($prod) {
                        $newProdQty = max(0, (int)$prod->quantity - $soldQty);
                        $prod->update(['quantity' => $newProdQty]);
                    }
                }
            }

            foreach ($products as $pModel) {
                $pModel->syncStockQuantity();
            }

            // 11. Record VAT/GST tax details
            foreach ($vatTaxGroups as $vGroup) {
                OrderVatTax::create([
                    'order_id' => $order->id,
                    'name' => $vGroup->name,
                    'percentage' => $vGroup->percentage,
                    'amount' => $vGroup->amount
                ]);
            }

            // 12. Double-Entry Accounting
            $salesAccountId = Account::whereIn('code', ['SALES_POS', 'SALES'])->value('id') ?? Account::where('name', 'like', '%Sales%')->value('id') ?? 9;
            $cashAccountId  = Account::whereIn('code', ['CASH_DRAWER', 'CASH', 'PETTY_CASH'])->value('id') ?? Account::where('name', 'like', '%Cash%')->value('id') ?? 16;
            $bankAccountId  = Account::whereIn('code', ['BANK_HDFC', 'BANK'])->value('id') ?? Account::where('name', 'like', '%Bank%')->value('id') ?? 18;
            $roundAccountId = Account::whereIn('code', ['EXP_ROF', 'ROUND', 'ROF'])->value('id') ?? Account::where('name', 'like', '%Round%')->value('id') ?? 31;
            $cgstOutputId   = Account::whereIn('code', ['CGST_OUT', 'GST_OUT_C'])->value('id') ?? Account::where('name', 'like', '%CGST Output%')->value('id') ?? 1;
            $sgstOutputId   = Account::whereIn('code', ['SGST_OUT', 'GST_OUT_S'])->value('id') ?? Account::where('name', 'like', '%SGST Output%')->value('id') ?? 2;

            $entries = [];

            if ($isSplit) {
                foreach ($data['split_payments'] as $sp) {
                    $debitId = ($sp['method'] === 'cash') ? $cashAccountId : $bankAccountId;
                    $entries[] = [
                        'account_id' => $debitId,
                        'type' => 'Dr',
                        'amount' => (float)$sp['amount'],
                        'description' => 'POS split checkout #' . $order->prefix . '-' . $order->order_code
                    ];
                }
            } else {
                $debitId = ($data['payment_method'] === 'cash') ? $cashAccountId : $bankAccountId;
                $entries[] = [
                    'account_id' => $debitId,
                    'type' => 'Dr',
                    'amount' => $actualPaid,
                    'description' => 'POS checkout #' . $order->prefix . '-' . $order->order_code
                ];
            }

            $taxableSales = max(0, $subtotal - $totalTax);
            if ($taxableSales > 0) {
                $entries[] = [
                    'account_id' => $salesAccountId,
                    'type' => 'Cr',
                    'amount' => $taxableSales,
                    'description' => 'Sales'
                ];
            }

            if ($totalTax > 0) {
                $taxSplit = round($totalTax / 2, 2);
                if ($taxSplit > 0) {
                    $entries[] = [
                        'account_id' => $cgstOutputId,
                        'type' => 'Cr',
                        'amount' => $taxSplit,
                        'description' => 'Output CGST'
                    ];
                    $entries[] = [
                        'account_id' => $sgstOutputId,
                        'type' => 'Cr',
                        'amount' => $taxSplit,
                        'description' => 'Output SGST'
                    ];
                }
            }

            $drSum = 0; $crSum = 0;
            foreach ($entries as $ent) {
                if ($ent['type'] === 'Dr') $drSum += $ent['amount']; else $crSum += $ent['amount'];
            }
            $roundOff = round($drSum - $crSum, 2);
            if (abs($roundOff) >= 0.01) {
                $entries[] = [
                    'account_id' => $roundAccountId,
                    'type' => $roundOff > 0 ? 'Cr' : 'Dr',
                    'amount' => abs($roundOff),
                    'description' => 'Round off'
                ];
            }

            $voucher = $this->voucherService->create([
                'voucher_type' => 'Sales',
                'date' => $order->created_at ?? now(),
                'narration' => 'POS checkout sale #' . $order->prefix . '-' . $order->order_code,
                'shop_id' => $order->shop_id,
                'financial_year_id' => $financialYear->id,
                'seq_prefix' => 'POS-' . $order->shop_id . '-',
                'entries' => $entries
            ]);

            $order->voucher_id = $voucher->id;
            $order->save();

            if (!empty($data['cart_name'])) {
                PosCart::where('shop_id', $shop->id)
                    ->where('name', $data['cart_name'])
                    ->delete();
            }

            if ($paymentAttempt) {
                $paymentAttempt->update([
                    'finalized_at' => now(),
                ]);
            }

            return $order;
        });
    }
}
