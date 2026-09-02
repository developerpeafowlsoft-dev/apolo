<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\FinancialYear;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Voucher;
use Exception;
use Illuminate\Support\Facades\DB;

class CodSettlementService
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Get summary of unsettled COD orders and current CUST_WEB ledger balance.
     */
    public function getCodReceivableSummary(int $shopId): array
    {
        $codAccountId = Account::where('code', 'CUST_WEB')->value('id') ?? 23;

        // Balance of CUST_WEB from General Ledger
        $totalDr = (float) DB::table('voucher_entries')
            ->join('vouchers', 'voucher_entries.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.shop_id', $shopId)
            ->where('voucher_entries.account_id', $codAccountId)
            ->where('voucher_entries.type', 'Dr')
            ->sum('voucher_entries.amount');

        $totalCr = (float) DB::table('voucher_entries')
            ->join('vouchers', 'voucher_entries.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.shop_id', $shopId)
            ->where('voucher_entries.account_id', $codAccountId)
            ->where('voucher_entries.type', 'Cr')
            ->sum('voucher_entries.amount');

        $netCodReceivable = max(0, round($totalDr - $totalCr, 2));

        // Operational delivered COD orders
        $deliveredCodOrders = Order::where('shop_id', $shopId)
            ->where('order_status', 'Delivered')
            ->where(function ($q) {
                $q->where('payment_method', 'cash payment')
                  ->orWhere('payment_method', 'Cash Payment')
                  ->orWhere('payment_method', 'cash');
            })
            ->get(['id', 'order_code', 'prefix', 'payable_amount', 'delivery_charge', 'shiprocket_awb_code', 'shiprocket_status', 'delivered_at']);

        return [
            'cod_ledger_account' => 'E-Commerce Customer Clearing A/c (CUST_WEB)',
            'net_cod_receivable_in_gl' => $netCodReceivable,
            'total_delivered_cod_orders' => $deliveredCodOrders->count(),
            'delivered_orders' => $deliveredCodOrders,
            'shiprocket_settlement_api_available' => false,
            'settlement_mode' => 'Manual Remittance Reconciliation via Bank Statement',
        ];
    }

    /**
     * Post COD Settlement Voucher when bank remittance from Shiprocket is received.
     * Clears CUST_WEB receivable, recognizes bank deposit, and accounts courier deduction expenses.
     * Idempotent & atomic.
     */
    public function processCodRemittanceSettlement(array $data, Shop $shop, FinancialYear $financialYear): array
    {
        return DB::transaction(function () use ($data, $shop, $financialYear) {
            $codAccountId     = Account::where('code', 'CUST_WEB')->value('id') ?? 23;
            $bankAccountId    = $data['bank_account_id'] ?? Account::where('code', 'BANK_HDFC')->value('id') ?? 20;
            $courierExpId     = Account::where('code', 'EXP_PACKING')->value('id') ?? Account::where('code', 'EXP_FREIGHT')->value('id') ?? 26;

            $netBankAmount    = (float) $data['net_bank_amount'];
            $courierDeduction = (float) ($data['courier_charges_deducted'] ?? 0);
            $totalClearedCod  = round($netBankAmount + $courierDeduction, 2);

            if ($totalClearedCod <= 0.00) {
                throw new Exception("Settlement amount must be greater than 0.");
            }

            $remittanceRef = $data['remittance_reference'] ?? 'SR-REMIT-' . date('Ymd');
            $ordersSummary = !empty($data['settled_orders_summary']) ? " for {$data['settled_orders_summary']}" : "";

            // Double-Entry Accounting Entries
            $entries = [];

            // 1. DEBIT: Bank Account (Net remittance deposited by courier)
            if ($netBankAmount > 0) {
                $entries[] = [
                    'account_id' => $bankAccountId,
                    'type' => 'Dr',
                    'amount' => $netBankAmount,
                    'description' => "Shiprocket COD Remittance received (Ref: {$remittanceRef}){$ordersSummary}",
                ];
            }

            // 2. DEBIT: Courier / Logistics Expense (Shiprocket freight / COD charges deducted at source)
            if ($courierDeduction > 0) {
                $entries[] = [
                    'account_id' => $courierExpId,
                    'type' => 'Dr',
                    'amount' => $courierDeduction,
                    'description' => "Shiprocket Courier/Remittance fee deduction (Ref: {$remittanceRef})",
                ];
            }

            // 3. CREDIT: COD Receivable (Clearing customer receivable)
            $entries[] = [
                'account_id' => $codAccountId,
                'type' => 'Cr',
                'amount' => $totalClearedCod,
                'description' => "Clear COD Courier Receivable (Ref: {$remittanceRef})",
            ];

            // Post Voucher via VoucherService
            $voucher = $this->voucherService->create([
                'voucher_type' => 'Receipt',
                'date' => $data['remittance_date'] ?? now()->toDateString(),
                'narration' => $data['narration'] ?? "Shiprocket COD Settlement Ref #{$remittanceRef}{$ordersSummary}",
                'shop_id' => $shop->id,
                'financial_year_id' => $financialYear->id,
                'seq_prefix' => 'RCT-' . $shop->id . '-',
                'entries' => $entries,
            ]);

            return [
                'success' => true,
                'voucher_id' => $voucher->id,
                'voucher_no' => $voucher->voucher_no,
                'net_bank_amount' => $netBankAmount,
                'courier_deduction' => $courierDeduction,
                'total_cleared_cod' => $totalClearedCod,
                'remittance_reference' => $remittanceRef,
            ];
        });
    }
}
