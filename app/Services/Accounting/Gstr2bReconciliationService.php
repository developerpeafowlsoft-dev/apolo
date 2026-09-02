<?php

namespace App\Services\Accounting;

use App\Models\InwardInvoice;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Exception;

class Gstr2bReconciliationService
{
    /**
     * Reconcile GSTR-2B JSON payload against ERP Purchase Invoices (InwardInvoice).
     *
     * @param int $shopId
     * @param array $gstr2bPayload Decoded GSTR-2B JSON array
     * @return array Reconciliation matrix categorized by match status
     */
    public function reconcileGstr2b(int $shopId, array $gstr2bPayload): array
    {
        $erpPurchasesRaw = InwardInvoice::where('shop_id', $shopId)
            ->with('partyCode')
            ->get();

        $erpPurchases = collect();
        foreach ($erpPurchasesRaw as $inv) {
            $key = $inv->inward_challan_no ?? $inv->inward_voucher_no ?? $inv->invoice_no;
            if ($key) {
                $erpPurchases->put($key, $inv);
            }
        }

        $matched = [];
        $mismatched = [];
        $missingInBooks = [];
        $processedInvoiceNumbers = [];

        $portalInvoices = $gstr2bPayload['b2b'] ?? $gstr2bPayload['data']['b2b'] ?? [];

        foreach ($portalInvoices as $vendorGroup) {
            $vendorGstin = $vendorGroup['gstin'] ?? $vendorGroup['ctin'] ?? null;
            $invList = $vendorGroup['inv'] ?? [$vendorGroup];

            foreach ($invList as $inv) {
                $invNo = $inv['inum'] ?? $inv['invoice_number'] ?? null;
                if (!$invNo) continue;

                $processedInvoiceNumbers[] = $invNo;
                $invDate = $inv['dt'] ?? $inv['invoice_date'] ?? null;
                $val = (float)($inv['val'] ?? $inv['invoice_value'] ?? 0);
                $taxable = (float)($inv['taxable_value'] ?? $inv['txval'] ?? 0);
                $igst = (float)($inv['igst'] ?? $inv['iamt'] ?? 0);
                $cgst = (float)($inv['cgst'] ?? $inv['camt'] ?? 0);
                $sgst = (float)($inv['sgst'] ?? $inv['samt'] ?? 0);

                if ($erpPurchases->has($invNo)) {
                    $erpInv = $erpPurchases->get($invNo);
                    $erpVal = (float)($erpInv->inward_acc_net_amount ?? $erpInv->inward_total ?? $erpInv->total_amount ?? 0);

                    // Variance tolerance check (within ± 2.00 INR)
                    if (abs($erpVal - $val) <= 2.00) {
                        $matched[] = [
                            'vendor_gstin' => $vendorGstin,
                            'invoice_number' => $invNo,
                            'invoice_date' => $invDate,
                            'portal_value' => $val,
                            'erp_value' => $erpVal,
                            'status' => 'Matched',
                            'itc_eligibility' => 'Eligible',
                        ];
                    } else {
                        $mismatched[] = [
                            'vendor_gstin' => $vendorGstin,
                            'invoice_number' => $invNo,
                            'invoice_date' => $invDate,
                            'portal_value' => $val,
                            'erp_value' => $erpVal,
                            'difference' => round($val - $erpVal, 2),
                            'status' => 'Mismatched',
                            'itc_eligibility' => 'Pending Verification',
                        ];
                    }
                } else {
                    $missingInBooks[] = [
                        'vendor_gstin' => $vendorGstin,
                        'invoice_number' => $invNo,
                        'invoice_date' => $invDate,
                        'portal_value' => $val,
                        'status' => 'Missing in Books',
                        'itc_eligibility' => 'Unclaimed ITC',
                    ];
                }
            }
        }

        // Identify ERP purchase invoices that vendor failed to file in GSTR-2B
        $missingInPortal = [];
        foreach ($erpPurchases as $invNo => $erpInv) {
            if (!in_array($invNo, $processedInvoiceNumbers)) {
                $missingInPortal[] = [
                    'vendor_gstin' => $erpInv->partyCode?->tax_info_gst_no ?? 'N/A',
                    'invoice_number' => $invNo,
                    'invoice_date' => $erpInv->inward_date ?? $erpInv->date,
                    'erp_value' => (float)($erpInv->inward_acc_net_amount ?? $erpInv->inward_total ?? $erpInv->total_amount ?? 0),
                    'status' => 'Missing in Portal',
                    'itc_eligibility' => 'Ineligible ITC Warning (Vendor Unfiled)',
                ];
            }
        }

        return [
            'summary' => [
                'total_matched' => count($matched),
                'total_mismatched' => count($mismatched),
                'total_missing_in_books' => count($missingInBooks),
                'total_missing_in_portal' => count($missingInPortal),
            ],
            'matched' => $matched,
            'mismatched' => $mismatched,
            'missing_in_books' => $missingInBooks,
            'missing_in_portal' => $missingInPortal,
        ];
    }

    /**
     * Auto-fetch GSTR-2B data via GSP API credentials.
     */
    public function fetchGstr2bViaGsp(int $shopId, string $gstin, string $returnPeriod): array
    {
        $gspBaseUrl = config('services.gsp.base_url', 'https://api.gsp.gst.gov.in');
        $gspClientKey = config('services.gsp.client_key');

        if (!$gspClientKey) {
            // Mock GSP API response when live credentials are not set
            return [
                'b2b' => [
                    [
                        'gstin' => '24AAACV1234F1Z9',
                        'inv' => [
                            [
                                'inum' => 'INV-001',
                                'dt' => '2026-07-01',
                                'val' => 1000.00,
                                'taxable_value' => 952.38,
                                'cgst' => 23.81,
                                'sgst' => 23.81,
                            ]
                        ]
                    ]
                ]
            ];
        }

        $response = Http::withHeaders([
            'client-id' => $gspClientKey,
            'state-cd' => substr($gstin, 0, 2),
        ])->get("{$gspBaseUrl}/gstr2b?gstin={$gstin}&ret_period={$returnPeriod}");

        if ($response->failed()) {
            throw new Exception("GSP API Error: " . $response->body());
        }

        return $response->json();
    }
}
