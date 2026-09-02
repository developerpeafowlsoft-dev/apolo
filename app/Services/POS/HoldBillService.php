<?php

namespace App\Services\POS;

use App\Models\HoldBill;
use App\Models\HoldBillItem;
use App\Models\VoucherSequence;
use App\Models\ProductBarcode;
use Illuminate\Support\Facades\DB;
use Exception;

class HoldBillService
{
    /**
     * Store a suspended Hold Bill.
     */
    public function saveHold(array $data, $shop, $financialYear): HoldBill
    {
        if (empty($data['items'])) {
            throw new Exception("Cannot hold an empty cart.");
        }

        return DB::transaction(function () use ($data, $shop, $financialYear) {
            // Generate Hold Number atomically using sequence table lock
            $seqKey = 'HOLD_BILL';
            $seq = VoucherSequence::firstOrCreate(
                [
                    'shop_id' => $shop->id,
                    'financial_year_id' => $financialYear->id,
                    'voucher_type' => $seqKey,
                ],
                [
                    'prefix' => 'HB',
                    'padding' => 6,
                    'current_no' => 0,
                    'reset_policy' => 'yearly',
                ]
            );

            $seq = VoucherSequence::where('id', $seq->id)->lockForUpdate()->first();
            $nextVal = $seq->current_no + 1;
            $seq->current_no = $nextVal;
            $seq->save();

            $holdNo = $seq->prefix . str_pad((string)$nextVal, $seq->padding, '0', STR_PAD_LEFT);

            // Create Hold Bill
            $hold = HoldBill::create([
                'shop_id' => $shop->id,
                'counter_id' => $data['counter_id'] ?? null,
                'user_id' => auth()->id(),
                'customer_id' => $data['customer_id'] ?? null,
                'hold_no' => $holdNo,
                'subtotal' => (float)($data['subtotal'] ?? 0),
                'discount' => (float)($data['discount'] ?? 0),
                'tax_amount' => (float)($data['tax_amount'] ?? 0),
                'payable_amount' => (float)($data['payable_amount'] ?? 0),
                'payment_method' => $data['payment_method'] ?? null,
                'salesman_id' => $data['salesman_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'tax_details' => $data['tax_details'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'status' => 'active',
            ]);

            // Save items
            foreach ($data['items'] as $item) {
                $productId = $item['product_id'] ?? null;
                if (!$productId) {
                    $bc = ProductBarcode::where('barcode_number', $item['barcode'])
                        ->where('shop_id', $shop->id)
                        ->first();
                    if ($bc) {
                        $productId = $bc->product_id;
                    }
                }

                if (!$productId) {
                    throw new Exception("Product could not be resolved for barcode " . $item['barcode']);
                }

                HoldBillItem::create([
                    'hold_bill_id' => $hold->id,
                    'product_id' => $productId,
                    'barcode' => $item['barcode'],
                    'qty' => (float)($item['qty'] ?? 1),
                    'rate' => (float)($item['rate'] ?? 0),
                    'disc_percent' => (float)($item['disc_percent'] ?? 0),
                    'disc_amt' => (float)($item['disc_amt'] ?? 0),
                    'tax_percent' => (float)($item['tax_percent'] ?? 0),
                    'tax_amt' => (float)($item['tax_amt'] ?? 0),
                    'tax_name' => $item['tax_name'] ?? null,
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'salesman_id' => $item['salesman_id'] ?? null,
                ]);
            }

            return $hold;
        });
    }

    /**
     * Restore a hold bill back to the active billing screen.
     * Prevents simultaneous restores using a database write lock.
     */
    public function restoreHold($id, $shop): HoldBill
    {
        return DB::transaction(function () use ($id, $shop) {
            $hold = HoldBill::where('shop_id', $shop->id)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$hold) {
                throw new Exception("Hold bill not found.");
            }

            if ($hold->status === 'restored') {
                throw new Exception("This hold bill has already been restored by another cashier.");
            }

            // Mark status as restored
            $hold->update(['status' => 'restored']);

            // Load items and salesman/customer relationships before returning
            return HoldBill::with(['items.product', 'customer.user', 'salesman'])->find($hold->id);
        });
    }

    /**
     * Duplicate an existing Hold Bill to a new active hold bill.
     */
    public function duplicateHold($id, $shop, $financialYear): HoldBill
    {
        return DB::transaction(function () use ($id, $shop, $financialYear) {
            $original = HoldBill::where('shop_id', $shop->id)
                ->where('id', $id)
                ->with('items')
                ->first();

            if (!$original) {
                throw new Exception("Original hold bill not found.");
            }

            // Re-use saveHold data mapping
            $itemsData = [];
            foreach ($original->items as $it) {
                $itemsData[] = [
                    'product_id' => $it->product_id,
                    'barcode' => $it->barcode,
                    'qty' => $it->qty,
                    'rate' => $it->rate,
                    'disc_percent' => $it->disc_percent,
                    'disc_amt' => $it->disc_amt,
                    'tax_percent' => $it->tax_percent,
                    'tax_amt' => $it->tax_amt,
                    'tax_name' => $it->tax_name,
                    'color' => $it->color,
                    'size' => $it->size,
                    'salesman_id' => $it->salesman_id,
                ];
            }

            $payload = [
                'counter_id' => $original->counter_id,
                'customer_id' => $original->customer_id,
                'subtotal' => $original->subtotal,
                'discount' => $original->discount,
                'tax_amount' => $original->tax_amount,
                'payable_amount' => $original->payable_amount,
                'payment_method' => $original->payment_method,
                'salesman_id' => $original->salesman_id,
                'remarks' => $original->remarks,
                'tax_details' => $original->tax_details,
                'customer_name' => $original->customer_name,
                'customer_phone' => $original->customer_phone,
                'customer_email' => $original->customer_email,
                'items' => $itemsData
            ];

            return $this->saveHold($payload, $shop, $financialYear);
        });
    }
}
