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
            $taxable = round(($it['quantity'] * $it['buy_price']), 2);

            // 1. HSN se vat_tax_id nikalna
            $vatTaxId = self::getVatTaxIdByHsnAndAmount($it['hsn_master_id'], $taxable);

            // 2. Vat Tax percentage nikalna
            $vatTax = VatTax::find($vatTaxId);
            $taxPercentage = $vatTax ? $vatTax->percentage : 0;

            // 3. State ke hisaab se CGST/SGST ya IGST calculate karna
            if ($supplierStateId == 12) {  // Gujarat
                $cgstRate = $taxPercentage / 2;
                $sgstRate = $taxPercentage / 2;
                $igstRate = 0;
            } else {  // Other State
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

        $roundOff = round($grand) - $grand;
        $grand = round($grand + $roundOff, 2);

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
            'inward_invoice_id' => $invoiceInward->id,
            'purchase_date'     => $data['purchase_date'] ?? null,
            'purchase_day_name' => $data['purchase_day_name'] ?? null,
            'purchase_time'     => $data['purchase_time'] ?? null,
            'bill_date'         => $data['bill_date'] ?? null,
            'total_taxable'     => $totTaxable,
            'total_cgst'        => $totCgst,
            'total_sgst'        => $totSgst,
            'total_igst'        => $totIgst,
            'round_off'         => $roundOff,
            'grand_total'       => $grand,
            'is_purchase'       => 1,
            'is_return'         => 0,
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