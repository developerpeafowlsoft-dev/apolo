<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\InwardInvoice;

class InwardInvoiceRepository extends Repository
{
    public static function model()
    {
        return InwardInvoice::class;    
    }

    public static function storeByInwardInvoiceRequest($inwardData): InwardInvoice
    {
        $shop = generaleSetting('shop');

        $inwardInvoice = self::create([
            'shop_id' => $shop?->id,
            'counter_master_id' => $inwardData['day_book_id'],
            'vat_tax_id' => $inwardData['inward_vat_tax_id'],
            'inward_voucher_no' => $inwardData['inward_voucher_no'],
            'inward_date' => $inwardData['inward_date'],
            'inward_day_name' => $inwardData['inward_day_name'],
            'inward_time' => $inwardData['inward_time'],
            'inward_challan_no' => $inwardData['inward_challan_no'],
            'inward_challan_date' => $inwardData['inward_challan_date'],
            'inward_party_code' => $inwardData['inward_party_code'],
            'inward_total' => $inwardData['inward_total'] ?? 0,
            'inward_party_limit' => $inwardData['inward_party_limit'],
            'inward_credit_day' => $inwardData['inward_acc_credit_day'],
            'inward_acc_purchaser' => $inwardData['inward_acc_purchaser'],
            'season_id' => $inwardData['inward_acc_season'],
            'agent_id' => $inwardData['inward_acc_agent'],
            'transport_id' => $inwardData['inward_acc_transport'],
            'delivery_by_id' => $inwardData['inward_acc_delivery_by'],
            'inward_acc_lr_no' => $inwardData['inward_acc_lr_no'],
            'inward_acc_lr_date' => $inwardData['inward_acc_lr_date'],
            'inward_acc_remark' => $inwardData['inward_acc_remark'],
            'inward_acc_gst_amount' => $inwardData['inward_acc_gst_amount'],
            'inward_acc_net_amount' => $inwardData['inward_acc_net_amount'],
            'inward_acc_freight_amount' => $inwardData['inward_acc_freight_amount'],
            'inward_acc_parcel_amount' => $inwardData['inward_acc_parcel_amount'],
            'inward_acc_amt_with_gst' => $inwardData['inward_acc_amt_with_gst'],
            'is_kachi' => !empty($inwardData['is_kachi']) ? 1 : 0,
        ]);

        return $inwardInvoice;
    }

    public static function updateByInwardInvoiceRequest(InwardInvoice $invoice, array $inwardData): InwardInvoice
    {
        $invoice->update([
            'counter_master_id' => $inwardData['day_book_id'],
            'vat_tax_id' => $inwardData['inward_vat_tax_id'],
            'inward_voucher_no' => $inwardData['inward_voucher_no'],
            'inward_date' => $inwardData['inward_date'],
            'inward_day_name' => $inwardData['inward_day_name'],
            'inward_time' => $inwardData['inward_time'],
            'inward_challan_no' => $inwardData['inward_challan_no'],
            'inward_challan_date' => $inwardData['inward_challan_date'],
            'inward_party_code' => $inwardData['inward_party_code'],
            'inward_total' => $inwardData['inward_total'] ?? 0,
            'inward_party_limit' => $inwardData['inward_party_limit'],
            'inward_credit_day' => $inwardData['inward_acc_credit_day'],
            'inward_acc_purchaser' => $inwardData['inward_acc_purchaser'],
            'season_id' => $inwardData['inward_acc_season'],
            'agent_id' => $inwardData['inward_acc_agent'],
            'transport_id' => $inwardData['inward_acc_transport'],
            'delivery_by_id' => $inwardData['inward_acc_delivery_by'],
            'inward_acc_lr_no' => $inwardData['inward_acc_lr_no'],
            'inward_acc_lr_date' => $inwardData['inward_acc_lr_date'],
            'inward_acc_remark' => $inwardData['inward_acc_remark'],
            'inward_acc_gst_amount' => $inwardData['inward_acc_gst_amount'],
            'inward_acc_net_amount' => $inwardData['inward_acc_net_amount'],
            'inward_acc_freight_amount' => $inwardData['inward_acc_freight_amount'],
            'inward_acc_parcel_amount' => $inwardData['inward_acc_parcel_amount'],
            'inward_acc_amt_with_gst' => $inwardData['inward_acc_amt_with_gst'],
            'is_kachi' => !empty($inwardData['is_kachi']) ? 1 : 0,
        ]);

        return $invoice;
    }

    public static function isPurchaseUpdate(InwardInvoice $invoice): InwardInvoice
    {
        $invoice->update([
           'is_purchase' => 1
        ]);

        return $invoice;
    }

}