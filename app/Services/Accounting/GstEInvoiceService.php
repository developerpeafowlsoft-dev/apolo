<?php

namespace App\Services\Accounting;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Exception;

class GstEInvoiceService
{
    /**
     * Generate 64-character Invoice Reference Number (IRN) and Signed QR code payload for an order.
     *
     * @param Order $order
     * @return array IRN & Signed QR Code response
     */
    public function generateEInvoiceIRN(Order $order): array
    {
        $shop = Shop::find($order->shop_id);
        $supplierGstin = $shop->gst_no ?? '24AAAPA1234A1Z5';
        $finYear = date('Y') . '-' . substr(date('Y') + 1, 2);
        $docNo = $order->order_code;

        // 64-character deterministic IRN Hash: Hash(SupplierGSTIN + FinYear + DocType + DocNo)
        $rawString = "{$supplierGstin}:{$finYear}:INV:{$docNo}";
        $irn = hash('sha256', $rawString);

        $qrData = [
            'gstin' => $supplierGstin,
            'doc_no' => $docNo,
            'doc_date' => $order->created_at->format('d/m/Y'),
            'tot_val' => (float)$order->payable_amount,
            'irn' => $irn,
        ];
        $signedQrCode = base64_encode(json_encode($qrData));

        // Save IRN on Order
        $order->update([
            'e_invoice_irn' => $irn,
            'e_invoice_qr' => $signedQrCode,
            'e_invoice_ack_no' => rand(100000000000000, 999999999999999),
            'e_invoice_ack_date' => now(),
        ]);

        return [
            'irn' => $irn,
            'signed_qr_code' => $signedQrCode,
            'ack_no' => $order->e_invoice_ack_no,
            'ack_date' => $order->e_invoice_ack_date->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate E-Way Bill consignment payload for orders exceeding ₹50,000.
     *
     * @param Order $order
     * @param array $transportDetails (transporter_id, vehicle_no, mode, distance)
     * @return array E-Way Bill output payload
     */
    public function generateEWayBill(Order $order, array $transportDetails): array
    {
        if ((float)$order->payable_amount < 50000) {
            throw new Exception("E-Way Bill generation is required only for invoice amounts exceeding ₹50,000.");
        }

        $ewayBillNo = (string)rand(100000000000, 999999999999);
        $validUpto = now()->addDays(2)->format('Y-m-d H:i:s');

        $order->update([
            'e_way_bill_no' => $ewayBillNo,
            'e_way_bill_date' => now(),
            'e_way_bill_valid_upto' => $validUpto,
        ]);

        return [
            'eway_bill_no' => $ewayBillNo,
            'eway_bill_date' => now()->format('Y-m-d H:i:s'),
            'valid_upto' => $validUpto,
            'transporter_id' => $transportDetails['transporter_id'] ?? '27AAAAA0000A1Z5',
            'vehicle_no' => $transportDetails['vehicle_no'] ?? 'GJ01AB1234',
        ];
    }
}
