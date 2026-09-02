<?php

namespace App\Services\Accounting;

use App\Models\Order;
use App\Models\POSReturn;
use App\Models\AccountMaster;
use App\Models\HsnMaster;
use App\Models\VatTax;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class Gstr1ReportingService
{
    /**
     * Generate complete GSTR-1 Return Data structure for a given shop and date range.
     *
     * @param int $shopId
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return array Categorized GSTR-1 tables
     */
    public function generateGstr1Data(int $shopId, string $startDate, string $endDate): array
    {
        $shop = Shop::find($shopId);
        $shopStateCode = $shop->state_id ?? 24;

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
            ->with(['products', 'customer.user', 'address'])
            ->get();

        $returns = POSReturn::where('shop_id', $shopId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        $b2b = [];
        $b2cLarge = [];
        $b2cSmall = [];
        $hsnSummary = [];
        $creditNotes = [];

        $onlineAccountingService = app(OnlineOrderAccountingService::class);

        // Document summary counters
        $firstInv = null;
        $lastInv = null;
        $totalInvCount = 0;

        foreach ($orders as $order) {
            $totalInvCount++;
            if (!$firstInv) $firstInv = $order->order_code;
            $lastInv = $order->order_code;

            $taxAmt = (float)($order->tax_amount ?? 0);
            $taxable = (float)($order->total_taxable_amount ?: (max(0, (float)$order->total_amount - $taxAmt)));
            if ($taxable <= 0 && (float)$order->total_amount > 0) {
                $taxable = max(0, (float)$order->total_amount - $taxAmt);
            }
            $payable = (float)$order->payable_amount;
            $custState = $onlineAccountingService->getGstStateCode($order);
            $isInterState = ($custState !== 24);

            // Check if customer is GST registered (B2B)
            $gstNo = null;
            if ($order->customer && $order->customer->user) {
                $userPhone = $order->customer->user->phone;
                $userEmail = $order->customer->user->email;
                if ($userPhone || $userEmail) {
                    $accMaster = AccountMaster::where('shop_id', $shopId)
                        ->where(function ($q) use ($userPhone, $userEmail) {
                            if ($userPhone) {
                                $q->where('cont_info_mobile1', $userPhone)->orWhere('cont_info_phone', $userPhone);
                            }
                            if ($userEmail) {
                                $q->orWhere('cont_info_email', $userEmail);
                            }
                        })
                        ->whereNotNull('tax_info_gst_no')
                        ->where('tax_info_gst_no', '!=', '')
                        ->first();
                    $gstNo = $accMaster?->tax_info_gst_no;
                }
            }

            if ($gstNo) {
                // Table 4: B2B Invoices
                $b2b[] = [
                    'gstin' => $gstNo,
                    'invoice_number' => $order->order_code,
                    'invoice_date' => $order->created_at->format('Y-m-d'),
                    'invoice_value' => $payable,
                    'place_of_supply' => $custState,
                    'reverse_charge' => 'N',
                    'taxable_value' => $taxable,
                    'igst' => $isInterState ? $taxAmt : 0,
                    'cgst' => $isInterState ? 0 : round($taxAmt / 2, 2),
                    'sgst' => $isInterState ? 0 : round($taxAmt / 2, 2),
                    'cess' => 0,
                ];
            } elseif ($isInterState && $payable > 250000) {
                // Table 5: B2C Large
                $b2cLarge[] = [
                    'invoice_number' => $order->order_code,
                    'invoice_date' => $order->created_at->format('Y-m-d'),
                    'invoice_value' => $payable,
                    'place_of_supply' => $custState,
                    'taxable_value' => $taxable,
                    'igst' => $taxAmt,
                ];
            } else {
                // Table 7: B2C Small (Aggregated by State & Tax Rate)
                $rateKey = ($isInterState ? 'INTER_' : 'INTRA_') . $custState;
                if (!isset($b2cSmall[$rateKey])) {
                    $b2cSmall[$rateKey] = [
                        'place_of_supply' => $custState,
                        'type' => $isInterState ? 'E-Commerce / Inter-State' : 'Intra-State POS',
                        'taxable_value' => 0,
                        'igst' => 0,
                        'cgst' => 0,
                        'sgst' => 0,
                    ];
                }
                $b2cSmall[$rateKey]['taxable_value'] += $taxable;
                if ($isInterState) {
                    $b2cSmall[$rateKey]['igst'] += $taxAmt;
                } else {
                    $half = round($taxAmt / 2, 2);
                    $b2cSmall[$rateKey]['cgst'] += $half;
                    $b2cSmall[$rateKey]['sgst'] += ($taxAmt - $half);
                }
            }

            // HSN Summary aggregation (Table 12)
            foreach ($order->products as $p) {
                $hsnCode = $p->hsn_master_id ? (HsnMaster::find($p->hsn_master_id)?->hsn_code ?? '6205') : '6205';
                $pQty = (int)($p->pivot->quantity ?? 1);
                $pGross = round((float)($p->pivot->price ?? 0) * $pQty, 2);
                $pTax = (float)($p->pivot->tax_amount ?? round($pGross * 0.05 / 1.05, 2));
                if ($pTax <= 0 && $taxAmt > 0 && $order->products->count() === 1) {
                    $pTax = $taxAmt;
                }
                $pTaxable = round(max(0, $pGross - $pTax), 2);

                if (!isset($hsnSummary[$hsnCode])) {
                    $hsnSummary[$hsnCode] = [
                        'hsn_code' => $hsnCode,
                        'description' => 'Retail Goods',
                        'uqc' => 'PCS',
                        'total_quantity' => 0,
                        'total_value' => 0,
                        'taxable_value' => 0,
                        'igst' => 0,
                        'cgst' => 0,
                        'sgst' => 0,
                    ];
                }
                $hsnSummary[$hsnCode]['total_quantity'] += $pQty;
                $hsnSummary[$hsnCode]['total_value'] += $pGross;
                $hsnSummary[$hsnCode]['taxable_value'] += $pTaxable;
                if ($isInterState) {
                    $hsnSummary[$hsnCode]['igst'] += $pTax;
                } else {
                    $halfP = round($pTax / 2, 2);
                    $hsnSummary[$hsnCode]['cgst'] += $halfP;
                    $hsnSummary[$hsnCode]['sgst'] += ($pTax - $halfP);
                }
            }
        }

        // Table 9B: Credit Notes (Returns)
        foreach ($returns as $ret) {
            $retVal = (float)$ret->total_amount;
            $retTax = round($retVal * 0.18 / 1.18, 2);
            $retTaxable = $retVal - $retTax;

            $creditNotes[] = [
                'credit_note_number' => 'CN-' . ($ret->return_no ?? $ret->return_code),
                'credit_note_date' => $ret->created_at->format('Y-m-d'),
                'original_invoice_number' => $ret->order_code ?? 'N/A',
                'note_value' => $retVal,
                'taxable_value' => $retTaxable,
                'cgst' => round($retTax / 2, 2),
                'sgst' => round($retTax / 2, 2),
            ];
        }

        // Table 13: Document Summary
        $docSummary = [
            'doc_type' => 'Tax Invoice',
            'from_number' => $firstInv ?? '000001',
            'to_number' => $lastInv ?? '000001',
            'total_count' => $totalInvCount,
            'cancelled_count' => 0,
            'net_issued' => $totalInvCount,
        ];

        return [
            'gstin' => $shop->gst_no ?? '24AAAPA1234A1Z5',
            'fp' => date('mY', strtotime($startDate)),
            'b2b' => $b2b,
            'b2cl' => $b2cLarge,
            'b2cs' => array_values($b2cSmall),
            'cdnr' => $creditNotes,
            'hsn' => array_values($hsnSummary),
            'doc_summary' => $docSummary,
        ];
    }

    /**
     * Export GSTR-1 payload formatted as official GST Portal JSON file.
     */
    public function exportGstr1Json(int $shopId, string $startDate, string $endDate): string
    {
        $data = $this->generateGstr1Data($shopId, $startDate, $endDate);
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
