<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountMaster;
use App\Models\VoucherEntry;
use App\Models\PosShift;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    /**
     * Generate Bank Reconciliation Statement (BRS) for a bank account as of a specific date.
     *
     * @param int $shopId
     * @param int $bankAccountId Account ID of the bank ledger
     * @param float $passbookBalance Balance as per physical bank statement/passbook
     * @param array $unclearedCheques List of uncleared cheques/transactions
     * @return array BRS Matrix
     */
    public function generateBrs(int $shopId, int $bankAccountId, float $passbookBalance, array $unclearedCheques = []): array
    {
        $bankAccount = Account::find($bankAccountId) 
            ?? Account::whereIn('code', ['BANK_HDFC', 'BANK'])->first()
            ?? AccountMaster::where('shop_id', $shopId)->find($bankAccountId);

        $bankAccId = $bankAccount?->id ?? $bankAccountId;
        $accBal = \App\Models\AccountBalance::where('shop_id', $shopId)->where('account_id', $bankAccId)->first();
        $bookBalance = (float)($accBal?->opening_balance ?? 0);

        // Fetch voucher debits (deposits) and credits (withdrawals/cheques issued)
        $totalDebit = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })->where('account_id', $bankAccountId)
          ->whereIn('type', ['Dr', 'debit'])
          ->sum('amount');

        $totalCredit = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        })->where('account_id', $bankAccountId)
          ->whereIn('type', ['Cr', 'credit'])
          ->sum('amount');

        $bookBalance += ($totalDebit - $totalCredit);

        $chequesIssuedNotPresented = 0;
        $chequesDepositedNotCleared = 0;

        foreach ($unclearedCheques as $chq) {
            $amt = (float)($chq['amount'] ?? 0);
            $type = strtoupper($chq['type'] ?? 'ISSUED');
            if ($type === 'ISSUED') {
                $chequesIssuedNotPresented += $amt;
            } else {
                $chequesDepositedNotCleared += $amt;
            }
        }

        $reconciledBalance = $bookBalance + $chequesIssuedNotPresented - $chequesDepositedNotCleared;
        $difference = round($reconciledBalance - $passbookBalance, 2);

        return [
            'account_name' => $bankAccount?->name ?? $bankAccount?->accountName ?? 'HDFC Main Bank Current A/c',
            'balance_as_per_cash_book' => round($bookBalance, 2),
            'add_cheques_issued_not_presented' => round($chequesIssuedNotPresented, 2),
            'less_cheques_deposited_not_cleared' => round($chequesDepositedNotCleared, 2),
            'reconciled_balance' => round($reconciledBalance, 2),
            'passbook_balance' => round($passbookBalance, 2),
            'variance_difference' => $difference,
            'is_fully_reconciled' => (abs($difference) <= 0.05),
        ];
    }

    /**
     * Reconcile POS Shift Drawer Opening/Closing Cash against system sales.
     *
     * @param int $shiftId
     * @param float $actualPhysicalCash Physical cash counted at drawer closing
     * @return array Shift Drawer Reconciliation Matrix
     */
    public function reconcileShiftDrawer(int $shiftId, float $actualPhysicalCash): array
    {
        $shift = PosShift::find($shiftId);
        if (!$shift) {
            throw new \Exception("POS Shift ID {$shiftId} not found.");
        }

        $openingCash = (float)$shift->opening_cash;
        $cashSales = (float)($shift->total_sales_cash ?? Order::where('shop_id', $shift->shop_id)->where('created_at', '>=', $shift->opened_at)->sum('payable_amount'));

        $expectedCash = $openingCash + $cashSales;
        $variance = round($actualPhysicalCash - $expectedCash, 2);

        $status = 'EXACT';
        if ($variance < 0) {
            $status = 'SHORTAGE';
        } elseif ($variance > 0) {
            $status = 'EXCESS';
        }

        // Close shift record
        $shift->update([
            'closing_cash' => $actualPhysicalCash,
            'expected_cash' => $expectedCash,
            'difference' => $variance,
            'closed_at' => now(),
            'status' => 'closed',
        ]);

        return [
            'shift_id' => $shiftId,
            'cashier' => $shift->user?->name ?? 'Cashier',
            'opening_cash' => round($openingCash, 2),
            'cash_sales' => round($cashSales, 2),
            'expected_cash' => round($expectedCash, 2),
            'actual_physical_cash' => round($actualPhysicalCash, 2),
            'variance' => $variance,
            'status' => $status,
        ];
    }
}
