<?php

namespace App\Services\POS;

use App\Models\Order;
use App\Models\POSReturn;
use App\Models\POSReturnProduct;
use App\Models\ProductBarcode;
use App\Models\Account;
use App\Models\VoucherSequence;
use App\Services\Accounting\VoucherService;
use Illuminate\Support\Facades\DB;
use Exception;

class POSReturnService
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Look up an order by its invoice number/code (resolving shop prefix & serial), order ID, or product barcode.
     */
    public function findOrderForReturn($shop, $invoiceNo)
    {
        $order = null;
        $invoiceNo = trim((string)$invoiceNo);

        // 1. Priority 1: Check order_products for exact barcode_number match sold by this shop
        $orderId = DB::table('order_products')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('orders.shop_id', $shop->id)
            ->where('order_products.barcode_number', $invoiceNo)
            ->latest('orders.id')
            ->value('orders.id');

        if ($orderId) {
            $order = Order::withoutGlobalScopes()
                ->where('shop_id', $shop->id)
                ->where('id', $orderId)
                ->first();
        }

        // 2. Priority 2: Try parsing prefix-code (e.g. RC-000008 or POS-000022)
        if (!$order && str_contains($invoiceNo, '-')) {
            $lastHyphenPos = strrpos($invoiceNo, '-');
            $prefix = substr($invoiceNo, 0, $lastHyphenPos);
            $code = substr($invoiceNo, $lastHyphenPos + 1);

            $order = Order::withoutGlobalScopes()
                ->where('shop_id', $shop->id)
                ->where('prefix', $prefix)
                ->where('order_code', $code)
                ->latest('id')
                ->first();
        }

        // 3. Priority 3: Try matching order_code directly
        if (!$order) {
            $order = Order::withoutGlobalScopes()
                ->where('shop_id', $shop->id)
                ->where('order_code', $invoiceNo)
                ->latest('id')
                ->first();
        }

        // 4. Priority 4: Match raw database ID ONLY if query is an integer string without leading zeros
        if (!$order && is_numeric($invoiceNo) && (string)(int)$invoiceNo === $invoiceNo && (int)$invoiceNo > 0) {
            $order = Order::withoutGlobalScopes()
                ->where('shop_id', $shop->id)
                ->where('id', (int)$invoiceNo)
                ->first();
        }

        if (!$order) {
            throw new Exception("Order not found with invoice number or barcode: " . $invoiceNo);
        }

        // Return order with products details, but without global scopes
        return Order::withoutGlobalScopes()
            ->with(['products' => function($q) {
                $q->withoutGlobalScopes();
            }, 'customer.user'])
            ->find($order->id);
    }

    /**
     * Perform returns transactional logic.
     */
    public function processReturn(array $data, $shop, $financialYear): POSReturn
    {
        return DB::transaction(function () use ($data, $shop, $financialYear) {
            $order = Order::withoutGlobalScopes()->findOrFail($data['order_id']);

            // Calculate return number sequence
            $seqKey = 'POS_RETURN';
            $seq = VoucherSequence::firstOrCreate(
                [
                    'shop_id' => $shop->id,
                    'financial_year_id' => $financialYear->id,
                    'voucher_type' => $seqKey,
                ],
                [
                    'prefix' => 'CN', // Credit Note
                    'padding' => 6,
                    'current_no' => 0,
                    'reset_policy' => 'yearly',
                ]
            );

            // Lock & increment sequence
            $seq = VoucherSequence::where('id', $seq->id)->lockForUpdate()->first();
            $nextVal = $seq->current_no + 1;
            $seq->current_no = $nextVal;
            $seq->save();

            $returnNo = $seq->prefix . '-' . str_pad((string)$nextVal, $seq->padding, '0', STR_PAD_LEFT);

            // Create Return Header
            $posReturn = POSReturn::create([
                'shop_id' => $shop->id,
                'counter_id' => $data['counter_id'] ?? null,
                'original_order_id' => $order->id,
                'return_no' => $returnNo,
                'customer_id' => $order->customer_id,
                'total_amount' => 0, // calculated dynamically below
                'tax_amount' => 0,
                'payment_method' => $data['refund_method'] ?? 'cash',
                'cashier_id' => auth()->id(),
            ]);

            $totalRefundAmount = 0;
            $totalTaxRefund = 0;

            foreach ($data['items'] as $item) {
                $barcode = $item['barcode'];
                $qty = (int)$item['qty'];

                // Verify barcode belongs to this order
                $orderProduct = DB::table('order_products')
                    ->where('order_id', $order->id)
                    ->where('barcode_number', $barcode)
                    ->first();

                if (!$orderProduct) {
                    throw new Exception("Barcode " . $barcode . " was not part of the original purchase invoice.");
                }

                // Check already returned qty for this barcode
                $prevReturned = POSReturnProduct::whereHas('returnHeader', function ($q) use ($order) {
                        $q->where('original_order_id', $order->id);
                    })
                    ->where('barcode_number', $barcode)
                    ->sum('qty');

                if (($prevReturned + $qty) > $orderProduct->quantity) {
                    throw new Exception("Barcode " . $barcode . " total return quantity exceeds purchased quantity.");
                }

                // Restore stock to main product record
                $product = \App\Models\Product::withoutGlobalScopes()->find($orderProduct->product_id);
                if ($product) {
                    $product->update([
                        'quantity' => $product->quantity + $qty
                    ]);
                }

                // Mark the barcode as unsold again
                ProductBarcode::where('barcode_number', $barcode)
                    ->update(['is_sold' => 0]);

                // Calculate refund rates
                $rate = (float)$orderProduct->price;
                $taxPercent = (float)$orderProduct->tax_percentage;
                $lineTotal = $rate * $qty;
                $taxAmt = round($lineTotal * ($taxPercent / 100), 2);

                $totalRefundAmount += $lineTotal;
                $totalTaxRefund += $taxAmt;

                // Save return product details
                POSReturnProduct::create([
                    'return_id' => $posReturn->id,
                    'product_id' => $orderProduct->product_id,
                    'barcode_number' => $barcode,
                    'qty' => $qty,
                    'rate' => $rate,
                    'tax_amt' => $taxAmt,
                    'reason' => $item['reason'] ?? 'Customer Return',
                ]);
            }

            // Update Return totals
            $posReturn->update([
                'total_amount' => $totalRefundAmount,
                'tax_amount' => $totalTaxRefund
            ]);

            // Double-entry accounting reverse entries
            $salesReturnAccountId = Account::whereIn('code', ['SALES_RET', 'SALES_POS', 'SALES'])->value('id') ?? 12;
            $cashAccountId        = Account::whereIn('code', ['CASH_DRAWER', 'CASH', 'PETTY_CASH'])->value('id') ?? 16;
            $bankAccountId        = Account::whereIn('code', ['BANK_HDFC', 'BANK'])->value('id') ?? 18;
            $roundAccountId       = Account::whereIn('code', ['EXP_ROF', 'ROUND', 'ROF'])->value('id') ?? 31;
            $cgstOutputId         = Account::whereIn('code', ['CGST_OUT', 'GST_OUT_C'])->value('id') ?? 1;
            $sgstOutputId         = Account::whereIn('code', ['SGST_OUT', 'GST_OUT_S'])->value('id') ?? 2;
            $igstOutputId         = Account::whereIn('code', ['IGST_OUT', 'GST_OUT_I'])->value('id') ?? 3;

            $taxableRefund = max(0, round($totalRefundAmount - $totalTaxRefund, 2));

            $entries = [];

            // 1. Debit Sales Return A/c (Taxable Amount Reversal)
            if ($taxableRefund > 0) {
                $entries[] = [
                    'account_id' => $salesReturnAccountId,
                    'type' => 'Dr',
                    'amount' => $taxableRefund,
                    'description' => 'Sales Return reversal for CN #' . $returnNo
                ];
            }

            // 2. Debit CGST / SGST output (Output Tax Reversal)
            if ($totalTaxRefund > 0) {
                $taxSplit = round($totalTaxRefund / 2, 2);
                $remainingTax = round($totalTaxRefund - $taxSplit, 2);
                if ($taxSplit > 0) {
                    $entries[] = [
                        'account_id' => $cgstOutputId,
                        'type' => 'Dr',
                        'amount' => $taxSplit,
                        'description' => 'Output CGST Reversal'
                    ];
                }
                if ($remainingTax > 0) {
                    $entries[] = [
                        'account_id' => $sgstOutputId,
                        'type' => 'Dr',
                        'amount' => $remainingTax,
                        'description' => 'Output SGST Reversal'
                    ];
                }
            }

            // 3. Credit Cash / Bank (Gross Refund Payout)
            $refundPayable = round($totalRefundAmount, 2);
            $creditId = ($data['refund_method'] === 'cash') ? $cashAccountId : $bankAccountId;
            $entries[] = [
                'account_id' => $creditId,
                'type' => 'Cr',
                'amount' => $refundPayable,
                'description' => 'Refund CN #' . $returnNo
            ];

            // 4. Minor paise Round Off adjustment if needed
            $drSum = 0.0; $crSum = 0.0;
            foreach ($entries as $ent) {
                if ($ent['type'] === 'Dr') $drSum += $ent['amount']; else $crSum += $ent['amount'];
            }
            $roundOff = round($drSum - $crSum, 2);
            if (abs($roundOff) >= 0.01) {
                $entries[] = [
                    'account_id' => $roundAccountId,
                    'type' => $roundOff > 0 ? 'Cr' : 'Dr',
                    'amount' => abs($roundOff),
                    'description' => 'Round off adjustment'
                ];
            }

            // Register accounting credit note posted voucher
            $this->voucherService->create([
                'voucher_type' => 'Credit Note',
                'date' => now(),
                'narration' => 'Sales Return Credit Note #' . $returnNo . ' for order #' . $order->prefix . '-' . $order->order_code,
                'shop_id' => $shop->id,
                'financial_year_id' => $financialYear->id,
                'seq_prefix' => 'CN-' . $shop->id . '-',
                'entries' => $entries
            ]);

            return $posReturn;
        });
    }
}
