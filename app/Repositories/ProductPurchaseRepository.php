<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\AccountMaster;
use App\Models\HsnSubMaster;
use App\Models\InwardInvoice;
use App\Models\InwardProduct;
use App\Models\ProductPurchase;
use App\Models\HsnMaster;
use App\Models\VatTax;

class ProductPurchaseRepository extends Repository
{
    public static function model()
    {
        return ProductPurchase::class;    
    }

    public static function storeByProductPurchaseRequest(array $data,InwardInvoice $invoiceInward): array
    {
        $inwardProduct = InwardProduct::where('inward_invoice_id', $invoiceInward->id)->get();

        // Supplier se state_id nikalna hai
        $supplier = AccountMaster::with('account')->findOrFail($invoiceInward->inward_party_code);
        $supplierStateId = $supplier->state_id;

        $totTaxable = 0;
        $totCgst = 0;
        $totSgst = 0;
        $totIgst = 0;
        $grand = 0;
        foreach ($inwardProduct as &$it) {
            $qty = (float)($it['quantity'] ?? 1);
            $buyPrice = (float)($it['buy_price'] ?? 0);
            $discPct = (float)($it['discount_price'] ?? 0);

            // Net taxable rate after discount
            if (isset($it['net_purc_rate']) && (float)$it['net_purc_rate'] > 0) {
                $taxable = round((float)$it['net_purc_rate'], 2);
            } elseif ($discPct > 0) {
                $taxable = round($qty * ($buyPrice - ($buyPrice * $discPct / 100)), 2);
            } else {
                $taxable = round($qty * $buyPrice, 2);
            }

            // 1. Vat Tax ID: Use existing vat_tax_id from inward_product if present, otherwise lookup by HSN
            $vatTaxId = $it['vat_tax_id'] ?? null;
            if (!$vatTaxId && !empty($it['hsn_master_id'])) {
                $perUnitTaxable = $qty > 0 ? ($taxable / $qty) : $taxable;
                $vatTaxId = self::getVatTaxIdByHsnAndAmount($it['hsn_master_id'], $perUnitTaxable);
            }

            // 2. Vat Tax percentage
            $vatTax = $vatTaxId ? VatTax::find($vatTaxId) : null;
            $taxPercentage = $vatTax ? (float)$vatTax->percentage : 0;

            $shopStateId = $invoiceInward->shop?->state_id ?? 12;

            // 3. State ke hisaab se CGST/SGST ya IGST calculate karna
            if ($supplierStateId == $shopStateId) {  // Same State (Intra-State)
                $cgstRate = $taxPercentage / 2;
                $sgstRate = $taxPercentage / 2;
                $igstRate = 0;
            } else {  // Other State (Inter-State)
                $cgstRate = 0;
                $sgstRate = 0;
                $igstRate = $taxPercentage;
            }

            $it['taxable_value'] = $taxable;
            $it['cgst_rate'] = $cgstRate;
            $it['sgst_rate'] = $sgstRate;
            $it['igst_rate'] = $igstRate;
            $it['cgst_amount'] = round($taxable * $cgstRate / 100, 2);
            $it['sgst_amount'] = round($taxable * $sgstRate / 100, 2);
            $it['igst_amount'] = round($taxable * $igstRate / 100, 2);
            $it['line_total'] = $taxable + $it['cgst_amount'] + $it['sgst_amount'] + $it['igst_amount'];

            $totTaxable += $taxable;
            $totCgst += $it['cgst_amount'];
            $totSgst += $it['sgst_amount'];
            $totIgst += $it['igst_amount'];
            $grand += $it['line_total'];
        }

        $freight = (float)($data['inward_acc_freight_amount'] ?? $invoiceInward->inward_acc_freight_amount ?? 0);
        $parcel  = (float)($data['inward_acc_parcel_amount'] ?? $invoiceInward->inward_acc_parcel_amount ?? 0);
        $freightTotal = $freight + $parcel;
        $billDiscAmt = (float)($data['bill_discount_amount'] ?? $invoiceInward->bill_discount_amount ?? 0);
        $cashDiscAmt = (float)($data['cash_discount_amount'] ?? $invoiceInward->cash_discount_amount ?? 0);
        $totalDiscAmt = $billDiscAmt + $cashDiscAmt;
        $otherAmt = (float)($data['other_amount'] ?? $invoiceInward->other_amount ?? 0);
        $expAmt   = (float)($data['expense_amount'] ?? $invoiceInward->expense_amount ?? 0);

        $subtotal = $totTaxable + $totCgst + $totSgst + $totIgst + $freightTotal + $otherAmt + $expAmt - $totalDiscAmt;
        $grandRounded = (float)round($subtotal, 0);
        $roundOff = (float)($grandRounded - $subtotal);
        $grand = $grandRounded;

//        return ProductPurchase::create([
//            'inward_invoice_id' => $invoiceInward->id,
//            'purchase_date'     => $data['purchase_date'] ?? null,
//            'purchase_day_name' => $data['purchase_day_name'] ?? null,
//            'purchase_time'     => $data['purchase_time'] ?? null,
//            'bill_date'         => $data['bill_date'] ?? null,
//            'total_taxable'     => $totTaxable,
//            'total_cgst'        => $totCgst,
//            'total_sgst'        => $totSgst,
//            'total_igst'        => $totIgst,
//            'round_off'         => $roundOff,
//            'grand_total'       => $grand,
//            'is_purchase'       => 1,
//            'is_return'         => 0,
//        ]);
        $purchase = ProductPurchase::create([
            'inward_invoice_id'          => $invoiceInward->id,
            'purchase_date'              => $data['purchase_date'] ?? null,
            'purchase_day_name'          => $data['purchase_day_name'] ?? null,
            'purchase_time'              => $data['purchase_time'] ?? null,
            'bill_date'                  => $data['bill_date'] ?? null,
            'cash_or_credit'             => $data['cash_or_credit'] ?? $invoiceInward->cash_or_credit ?? 'Credit',
            'bank_cash_discount_percent' => $data['bank_cash_discount_percent'] ?? $invoiceInward->bank_cash_discount_percent ?? 0,
            'gross_amount'               => $data['gross_amount'] ?? $invoiceInward->gross_amount ?? $totTaxable,
            'bill_discount_percent'      => $data['bill_discount_percent'] ?? $invoiceInward->bill_discount_percent ?? 0,
            'bill_discount_amount'       => $data['bill_discount_amount'] ?? $invoiceInward->bill_discount_amount ?? 0,
            'cash_discount_percent'      => $data['cash_discount_percent'] ?? $invoiceInward->cash_discount_percent ?? 0,
            'cash_discount_amount'       => $data['cash_discount_amount'] ?? $invoiceInward->cash_discount_amount ?? 0,
            'agent_commission_percent'   => $data['agent_commission_percent'] ?? $invoiceInward->agent_commission_percent ?? 0,
            'agent_commission_amount'    => $data['agent_commission_amount'] ?? $invoiceInward->agent_commission_amount ?? 0,
            'expense_amount'             => $data['expense_amount'] ?? $invoiceInward->expense_amount ?? 0,
            'other_amount'               => $data['other_amount'] ?? $invoiceInward->other_amount ?? 0,
            'bill_remark'                => $data['inward_bill_remark'] ?? $invoiceInward->inward_bill_remark ?? null,
            'total_taxable'              => $totTaxable,
            'total_cgst'                 => $totCgst,
            'total_sgst'                 => $totSgst,
            'total_igst'                 => $totIgst,
            'round_off'                  => $roundOff,
            'grand_total'                => $grand,
            'is_purchase'                => 1,
            'is_return'                  => 0,
        ]);

        return [
            'purchase'     => $purchase,
            'supplier'     => $supplier,
            'totTaxable'   => $totTaxable,
            'totCgst'      => $totCgst,
            'totSgst'      => $totSgst,
            'totIgst'      => $totIgst,
            'roundOff'     => $roundOff,
            'grand'        => $grand,
            'inwardProduct' => $inwardProduct,
        ];
    }

    private static function getVatTaxIdByHsnAndAmount($hsnMasterId, $taxableAmount)
    {
        $hsnSubMaster = HsnSubMaster::where('hsn_master_id', $hsnMasterId)
            ->where('from_purchase_rate', '<=', $taxableAmount)
            ->where('to_purchase_rate', '>=', $taxableAmount)
            ->first();

        if ($hsnSubMaster) {
            return $hsnSubMaster->vat_tax_id;
        }

        // Agar koi range match nahi karti to default hsn_masters ka vat_tax_id le sakte ho
        $hsnMaster = HsnMaster::find($hsnMasterId);
        return $hsnMaster ? $hsnMaster->vat_tax_id : null;
    }
}