<?php

namespace App\Services\Accounting;

use App\Models\Order;
use App\Models\InwardInvoice;
use App\Models\AccountMaster;
use App\Models\Shop;
use Carbon\Carbon;

class OutstandingAgeingService
{
    /**
     * Generate Outstanding Ageing Matrix for Customer Receivables (Debtors) or Vendor Payables (Creditors).
     *
     * @param int $shopId
     * @param string $type ('DEBTORS' or 'CREDITORS')
     * @param string|null $asOfDate
     * @return array Ageing matrix categorized into 0-30, 31-60, 61-90, 90+ days
     */
    public function generateOutstandingAgeing(int $shopId, string $type = 'DEBTORS', ?string $asOfDate = null): array
    {
        $asOf = $asOfDate ? Carbon::parse($asOfDate) : Carbon::now();
        $type = strtoupper($type);

        $buckets = [
            '0_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '90_plus' => 0,
            'total_outstanding' => 0,
        ];

        $details = [];

        if ($type === 'DEBTORS') {
            // Recognized / Delivered Unpaid Orders (Excludes pending unvouchered COD orders)
            $orders = Order::where('shop_id', $shopId)
                ->where('payment_status', '!=', \App\Enums\PaymentStatus::PAID->value)
                ->where(function ($q) {
                    $q->whereNotNull('voucher_id')
                      ->orWhere('order_status', \App\Enums\OrderStatus::DELIVERED->value)
                      ->orWhere('order_status', 'Delivered');
                })
                ->with('customer.user')
                ->get();

            $asOf = $asOfDate ? Carbon::parse($asOfDate)->startOfDay() : Carbon::now()->startOfDay();

            foreach ($orders as $ord) {
                $invDate = Carbon::parse($ord->created_at)->startOfDay();
                $ageDays = max(0, (int)$invDate->diffInDays($asOf, false));
                $unpaidAmt = (float)$ord->payable_amount;

                $bucketKey = '0_30';
                if ($ageDays > 90) {
                    $bucketKey = '90_plus';
                } elseif ($ageDays > 60) {
                    $bucketKey = '61_90';
                } elseif ($ageDays > 30) {
                    $bucketKey = '31_60';
                }

                $buckets[$bucketKey] += $unpaidAmt;
                $buckets['total_outstanding'] += $unpaidAmt;

                $details[] = [
                    'party_name' => $ord->customer?->user?->name ?? 'Walk-in Customer',
                    'invoice_number' => $ord->order_code,
                    'invoice_date' => $invDate->format('Y-m-d'),
                    'age_days' => $ageDays,
                    'amount' => round($unpaidAmt, 2),
                    'bucket' => $bucketKey,
                ];
            }
        } else {
            // Unpaid Inward Invoices (Vendor Creditors)
            $invoices = InwardInvoice::where('shop_id', $shopId)
                ->with(['partyCode', 'productPurchase'])
                ->get();

            $asOf = $asOfDate ? Carbon::parse($asOfDate)->startOfDay() : Carbon::now()->startOfDay();

            foreach ($invoices as $inv) {
                $invDate = Carbon::parse($inv->inward_date ?? $inv->created_at)->startOfDay();
                $ageDays = max(0, (int)$invDate->diffInDays($asOf, false));
                
                $purc = $inv->productPurchase;
                $unpaidAmt = (float)($purc?->grand_total ?? $inv->inward_acc_amt_with_gst ?? (($inv->inward_total ?? 0) + ($inv->inward_acc_gst_amount ?? 0)));
                if ($unpaidAmt <= 0) {
                    continue;
                }

                $bucketKey = '0_30';
                if ($ageDays > 90) {
                    $bucketKey = '90_plus';
                } elseif ($ageDays > 60) {
                    $bucketKey = '61_90';
                } elseif ($ageDays > 30) {
                    $bucketKey = '31_60';
                }

                $buckets[$bucketKey] += $unpaidAmt;
                $buckets['total_outstanding'] += $unpaidAmt;

                $partyName = $inv->partyCode?->accountName ?? $inv->partyCode?->name ?? 'Vendor Party';
                $voucherNo = $inv->inward_voucher_no ?? $inv->inward_challan_no ?? 'INV-001';

                $details[] = [
                    'party_name' => $partyName,
                    'invoice_number' => $voucherNo,
                    'invoice_date' => $invDate->format('Y-m-d'),
                    'age_days' => $ageDays,
                    'amount' => round($unpaidAmt, 2),
                    'bucket' => $bucketKey,
                ];
            }
        }

        return [
            'type' => $type,
            'as_of_date' => $asOf->format('Y-m-d'),
            'summary' => [
                'bucket_0_30' => round($buckets['0_30'], 2),
                'bucket_31_60' => round($buckets['31_60'], 2),
                'bucket_61_90' => round($buckets['61_90'], 2),
                'bucket_90_plus' => round($buckets['90_plus'], 2),
                'total_outstanding' => round($buckets['total_outstanding'], 2),
            ],
            'details' => $details,
        ];
    }
}
