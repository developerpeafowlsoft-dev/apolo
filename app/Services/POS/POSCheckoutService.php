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
use App\Services\Accounting\VoucherService;
use Illuminate\Support\Facades\DB;
use Exception;

class POSCheckoutService
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Process POS grid checkout transactional logic.
     *
     * @param array $data Validated checkout payload
     * @param \App\Models\Shop $shop Current shop model
     * @param \App\Models\FinancialYear $financialYear Active financial year
     * @return Order
     * @throws Exception
     */
    public function checkout(array $data, $shop, $financialYear): Order
    {
        return DB::transaction(function () use ($data, $shop, $financialYear) {
            // 1. Resolve or Create Customer
            $customerId = $data['customer_id'] ?? null;
            if (!$customerId && !empty($data['customer_phone'])) {
                $cust = Customer::whereHas('user', function ($q) use ($data) {
                    $q->where('phone', $data['customer_phone']);
                })->first();

                if ($cust) {
                    $customerId = $cust->id;
                } else {
                    $firstName = $data['customer_name'] ?: 'Walk-in';
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
                        'email' => $data['customer_email'] ?: (str()->random(10) . '@walkin.com'),
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

            // 2. Resolve Counter Prefix and Voucher Sequence
            $counterId = $data['counter_id'] ?? null;
            $counter = null;
            $seqPrefix = $shop->prefix ?? 'RC';

            if ($counterId) {
                $counter = CounterMaster::where('shop_id', $shop->id)
                    ->where('id', $counterId)
                    ->first();
                if ($counter && $counter->voucher_prefix) {
                    $seqPrefix = $counter->voucher_prefix;
                }
            }

            $branchContext = app(\App\Services\BranchContext::class);
            $branchId = $branchContext->getCurrentBranchId();
            $branch = $branchContext->getCurrentBranch();

            // Generate atomic sequential order number using database lock
            $seqKey = $counterId ? 'POS_ORDER_COUNTER_' . $counterId : 'POS_ORDER';
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

            // Lock sequence record for update to prevent race conditions
            $seq = VoucherSequence::where('id', $seq->id)->lockForUpdate()->first();
            $nextVal = $seq->current_no + 1;

            if ($seq->range_end && $nextVal > $seq->range_end) {
                throw new Exception("Order sequence exceeded the range limit of {$seq->range_end} for this branch.");
            }

            $seq->current_no = $nextVal;
            $seq->save();

            $orderCode = str_pad((string)$nextVal, $seq->padding, '0', STR_PAD_LEFT);
            $prefix = $seq->prefix;
            if ($branchContext->isBranch() && $branchContext->getBranchCode()) {
                $prefix = $branchContext->getBranchCode() . '-' . $prefix;
            }

            // 3. Process items and update stock
            $subtotal = 0;
            $totalTax = 0;
            $discount = 0;
            $itemsData = [];
            $vatTaxGroups = [];

            foreach ($data['items'] as $item) {
                $bc = ProductBarcode::where('barcode_number', $item['barcode'])
                    ->where('shop_id', $shop->id)
                    ->where('is_sold', 0)
                    ->first();

                if (!$bc) {
                    throw new Exception("Barcode " . $item['barcode'] . " is invalid or sold out.");
                }

                $product = $bc->product;
                $inwardProd = $bc->inwardProduct;

                $qty = (int)$item['qty'];
                $rate = (float)$item['rate'];
                $discPercent = (float)($item['disc_percent'] ?? 0);
                $discAmt = round($rate * $qty * ($discPercent / 100), 2);
                $lineTotal = ($rate * $qty) - $discAmt;

                $product->syncStockQuantity();

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
                    'barcode_number' => $bc->barcode_number
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
                $orderPaymentMethod = $data['payment_method'] === 'cash' ? \App\Enums\PaymentMethod::CASH->value : \App\Enums\PaymentMethod::ONLINE->value;
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

            // 4. Create Order Model
            $order = Order::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'shop_id' => $shop->id,
                'branch_id' => $branchId,
                'pos_order' => true,
                'customer_id' => $customerId,
                'counter_id' => $counterId,
                'order_code' => $orderCode,
                'prefix' => $prefix,
                'delivery_charge' => 0,
                'total_amount' => $subtotal + $discount,
                'coupon_discount' => $discount,
                'tax_amount' => $totalTax,
                'payable_amount' => $payable,
                'payment_method' => $orderPaymentMethod,
                'order_status' => \App\Enums\OrderStatus::DELIVERED->value,
                'instruction' => $data['note'] ?? null,
                'payment_status' => \App\Enums\PaymentStatus::PAID->value,
                'e_invoice_irn' => $eInvoiceIrn,
                'e_invoice_ack_no' => $eInvoiceAckNo,
                'e_invoice_ack_date' => $eInvoiceAckDate,
            ]);

            // Save actual Payment records and link them to the order
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

            // 5. Attach products and mark barcodes as sold
            foreach ($itemsData as $it) {
                $order->products()->attach($it['product_id'], [
                    'barcode_number' => $it['barcode_number'],
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

            // 6. Record VAT/GST tax details
            foreach ($vatTaxGroups as $vGroup) {
                OrderVatTax::create([
                    'order_id' => $order->id,
                    'name' => $vGroup->name,
                    'percentage' => $vGroup->percentage,
                    'amount' => $vGroup->amount
                ]);
            }

            // 7. Double-Entry Accounting
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

            if ($subtotal > 0) {
                $entries[] = [
                    'account_id' => $salesAccountId,
                    'type' => 'Cr',
                    'amount' => $subtotal,
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

            // Calculate Round off
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

            // Generateposted sales voucher
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

            return $order;
        });
    }
}
