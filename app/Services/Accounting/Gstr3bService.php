<?php

namespace App\Services\Accounting;

use App\Models\Order;
use App\Models\InwardInvoice;
use App\Models\Shop;

class Gstr3bService
{
    /**
     * Compute GSTR-3B tax summary, ITC set-off calculation, and net tax payable.
     *
     * @param int $shopId
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return array Statutory GSTR-3B Computation Matrix
     */
    public function computeGstr3b(int $shopId, string $startDate, string $endDate): array
    {
        $shop = Shop::find($shopId);

        $orders = Order::withoutGlobalScopes()
            ->where('shop_id', $shopId)
            ->where('order_status', '!=', 'Cancelled')
            ->where('order_status', '!=', \App\Enums\OrderStatus::CANCELLED->value)
            ->where(function ($q) {
                $q->whereNotNull('voucher_id')
                  ->orWhere('order_status', \App\Enums\OrderStatus::DELIVERED->value)
                  ->orWhere('order_status', 'Delivered');
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $purchases = \App\Models\ProductPurchase::whereHas('inwardInvoice', function ($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('bill_date', [$startDate, $endDate])
              ->orWhereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        })->get();

        // 3.1 Outward Tax Liabilities
        $outwardTaxable = 0;
        $outwardCgst = 0;
        $outwardSgst = 0;
        $outwardIgst = 0;

        foreach ($orders as $order) {
            $tax = (float)($order->tax_amount ?? 0);
            $taxable = (float)($order->total_taxable_amount ?: max(0, (float)$order->total_amount - $tax));
            if ($taxable <= 0 && (float)$order->total_amount > 0) {
                $taxable = max(0, (float)$order->total_amount - $tax);
            }
            $tax = (float)($order->tax_amount ?? 0);
            $custState = (int)($order->customer_state ?? $shop?->state_id ?? 24);
            $isInter = ($custState !== (int)($shop?->state_id ?? 24));

            $outwardTaxable += $taxable;
            if ($isInter) {
                $outwardIgst += $tax;
            } else {
                $half = round($tax / 2, 2);
                $outwardCgst += $half;
                $outwardSgst += ($tax - $half);
            }
        }

        // 4. Eligible Input Tax Credit (ITC)
        $itcIgst = 0;
        $itcCgst = 0;
        $itcSgst = 0;

        foreach ($purchases as $purc) {
            $itcCgst += (float)$purc->total_cgst;
            $itcSgst += (float)$purc->total_sgst;
            $itcIgst += (float)$purc->total_igst;
        }

        // Apply Statutory Tax Set-Off Rules (IGST -> CGST -> SGST)
        $remIgst = $outwardIgst;
        $remCgst = $outwardCgst;
        $remSgst = $outwardSgst;

        $usedIgstItc = min($itcIgst, $remIgst);
        $remIgst -= $usedIgstItc;

        $usedCgstItc = min($itcCgst, $remCgst);
        $remCgst -= $usedCgstItc;

        $usedSgstItc = min($itcSgst, $remSgst);
        $remSgst -= $usedSgstItc;

        $cashPayableCgst = max(0, $remCgst);
        $cashPayableSgst = max(0, $remSgst);
        $cashPayableIgst = max(0, $remIgst);

        return [
            'period' => date('F Y', strtotime($startDate)),
            'outward_supplies' => [
                'taxable_value' => round($outwardTaxable, 2),
                'cgst' => round($outwardCgst, 2),
                'sgst' => round($outwardSgst, 2),
                'igst' => round($outwardIgst, 2),
            ],
            'eligible_itc' => [
                'cgst' => round($itcCgst, 2),
                'sgst' => round($itcSgst, 2),
                'igst' => round($itcIgst, 2),
            ],
            'net_cash_payable' => [
                'cgst' => round($cashPayableCgst, 2),
                'sgst' => round($cashPayableSgst, 2),
                'igst' => round($cashPayableIgst, 2),
                'total_cash_payable' => round($cashPayableCgst + $cashPayableSgst + $cashPayableIgst, 2),
            ]
        ];
    }
}
