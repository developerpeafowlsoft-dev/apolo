<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountMaster;
use App\Models\FinancialYear;
use App\Models\InwardInvoice;
use App\Models\ProductPurchase;
use App\Models\Shop;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Exception;
use Illuminate\Support\Facades\DB;

class SupplierPaymentService
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Get all outstanding purchase bills for a specific supplier with paid, returned, and net balances.
     */
    public function getSupplierOutstandingBills(int $shopId, int $supplierId): array
    {
        $supplier = AccountMaster::findOrFail($supplierId);

        $purchases = ProductPurchase::whereHas('inwardInvoice', function ($q) use ($shopId, $supplierId) {
            $q->where('shop_id', $shopId)->where('inward_party_code', $supplierId);
        })
        ->with('inwardInvoice')
        ->orderBy('bill_date')
        ->get();

        $bills = [];
        $totalOriginal = 0.00;
        $totalOutstanding = 0.00;

        foreach ($purchases as $p) {
            $inward = $p->inwardInvoice;
            $invoiceNo = $inward?->inward_voucher_no ?? $p->bill_no ?? ('PUR-' . $p->id);
            $grandTotal = (float) $p->grand_total;

            // Debit Notes (Returns) against this invoice
            $debitNotesAmount = (float) Voucher::where('shop_id', $shopId)
                ->where('voucher_type', 'Debit Note')
                ->where('narration', 'like', "%{$invoiceNo}%")
                ->join('voucher_entries', 'vouchers.id', '=', 'voucher_entries.voucher_id')
                ->where('voucher_entries.type', 'Dr')
                ->where('voucher_entries.account_id', $supplier->account_id ?? 24)
                ->sum('voucher_entries.amount');

            // Previous Payments allocated to this invoice
            $paidAmount = (float) Voucher::where('shop_id', $shopId)
                ->where('voucher_type', 'Payment')
                ->where('narration', 'like', "%{$invoiceNo}%")
                ->join('voucher_entries', 'vouchers.id', '=', 'voucher_entries.voucher_id')
                ->where('voucher_entries.type', 'Dr')
                ->where('voucher_entries.account_id', $supplier->account_id ?? 24)
                ->sum('voucher_entries.amount');

            $outstanding = max(0, round($grandTotal - $debitNotesAmount - $paidAmount, 2));

            $bills[] = [
                'purchase_id' => $p->id,
                'inward_invoice_id' => $inward?->id,
                'invoice_no' => $invoiceNo,
                'bill_date' => $p->bill_date ?? $inward?->inward_date?->toDateString(),
                'original_amount' => $grandTotal,
                'debit_notes_amount' => $debitNotesAmount,
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $outstanding,
                'is_fully_settled' => ($outstanding <= 0.00),
            ];

            $totalOriginal += $grandTotal;
            $totalOutstanding += $outstanding;
        }

        return [
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->accountName,
            'supplier_code' => $supplier->accountshortcode,
            'total_invoices_count' => count($bills),
            'total_original_amount' => round($totalOriginal, 2),
            'total_outstanding_amount' => round($totalOutstanding, 2),
            'bills' => $bills,
        ];
    }

    /**
     * Process a bill-by-bill supplier payment and post a double-entry Payment voucher.
     * Idempotent & atomic.
     */
    public function processSupplierPayment(array $data, Shop $shop, FinancialYear $financialYear): array
    {
        return DB::transaction(function () use ($data, $shop, $financialYear) {
            $supplierId = (int) $data['supplier_id'];
            $supplier = AccountMaster::findOrFail($supplierId);
            $supplierAccountId = $supplier->account_id ?? Account::where('code', 'SUPP_TRADE')->value('id') ?? 24;

            $paymentMode = strtolower($data['payment_mode'] ?? 'cash');
            $creditAccountId = ($paymentMode === 'cash')
                ? (Account::where('code', 'CASH_DRAWER')->value('id') ?? 17)
                : ($data['bank_account_id'] ?? Account::where('code', 'BANK_HDFC')->value('id') ?? 20);

            $totalAmount = (float) $data['total_payment_amount'];
            if ($totalAmount <= 0.00) {
                throw new Exception("Payment amount must be greater than 0.");
            }

            $allocations = $data['allocations'] ?? [];
            $allocatedTotal = 0.00;
            $allocatedBillNumbers = [];

            foreach ($allocations as $alloc) {
                $purchaseId = (int) $alloc['purchase_id'];
                $amount = (float) $alloc['amount'];

                if ($amount <= 0.00) {
                    continue;
                }

                $purchase = ProductPurchase::with('inwardInvoice')->findOrFail($purchaseId);
                $invoiceNo = $purchase->inwardInvoice?->inward_voucher_no ?? $purchase->bill_no ?? ('PUR-' . $purchase->id);

                $allocatedTotal += $amount;
                $allocatedBillNumbers[] = "Inv #{$invoiceNo}: ₹" . number_format($amount, 2);
            }

            $allocationSummary = !empty($allocatedBillNumbers) ? implode(', ', $allocatedBillNumbers) : "On-Account Payment";

            // Double-Entry Accounting Entries
            $entries = [];

            // 1. DEBIT: Supplier Payable A/c (Clearing liability)
            $entries[] = [
                'account_id' => $supplierAccountId,
                'type' => 'Dr',
                'amount' => $totalAmount,
                'description' => "Supplier Payment to {$supplier->accountName} ({$allocationSummary})",
            ];

            // 2. CREDIT: Cash / Bank A/c (Outflow of funds)
            $entries[] = [
                'account_id' => $creditAccountId,
                'type' => 'Cr',
                'amount' => $totalAmount,
                'description' => "Payment Disbursed to {$supplier->accountName} via " . ucfirst($paymentMode),
            ];

            // Post Voucher via VoucherService
            $refNo = !empty($data['reference_no']) ? " Ref: {$data['reference_no']}" : "";
            $voucher = $this->voucherService->create([
                'voucher_type' => 'Payment',
                'date' => $data['payment_date'] ?? now()->toDateString(),
                'narration' => $data['narration'] ?? "Payment to {$supplier->accountName} for {$allocationSummary}{$refNo}",
                'shop_id' => $shop->id,
                'financial_year_id' => $financialYear->id,
                'seq_prefix' => 'PMT-' . $shop->id . '-',
                'entries' => $entries,
            ]);

            return [
                'success' => true,
                'voucher_id' => $voucher->id,
                'voucher_no' => $voucher->voucher_no,
                'supplier_name' => $supplier->accountName,
                'total_paid' => $totalAmount,
                'payment_mode' => $paymentMode,
                'allocations' => $allocations,
                'allocation_summary' => $allocationSummary,
            ];
        });
    }
}
