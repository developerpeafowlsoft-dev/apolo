<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountMaster;
use App\Models\AccountGroup;
use App\Models\VoucherEntry;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class FinancialStatementService
{
    protected $inventoryValuationService;

    public function __construct(InventoryValuationService $inventoryValuationService)
    {
        $this->inventoryValuationService = $inventoryValuationService;
    }

    /**
     * Generate 6-Column Trial Balance for a shop up to a specific date.
     *
     * @param int $shopId
     * @param string $asOfDate (YYYY-MM-DD)
     * @return array Grouped Trial Balance
     */
    public function generateTrialBalance(int $shopId, string $asOfDate): array
    {
        $accounts = \App\Models\Account::with(['accountGroup.accountType'])->get();

        // Pre-aggregate instead of querying per account: this loop previously ran
        // three queries for each of ~490 accounts on every report load.
        $openingMap = \App\Models\AccountBalance::where('shop_id', $shopId)
            ->orderBy('id')
            ->get(['account_id', 'opening_balance'])
            ->groupBy('account_id')
            ->map(fn ($g) => (float)$g->first()->opening_balance)
            ->toArray();

        $entryTotals = DB::table('voucher_entries')
            ->join('vouchers', 'voucher_entries.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.shop_id', $shopId)
            ->where('vouchers.date', '<=', $asOfDate)
            ->groupBy('voucher_entries.account_id', 'voucher_entries.type')
            ->selectRaw('voucher_entries.account_id as aid, voucher_entries.type as t, SUM(voucher_entries.amount) as total')
            ->get();

        $debitMap = [];
        $creditMap = [];
        foreach ($entryTotals as $row) {
            $type = strtolower((string)$row->t);
            if (in_array($type, ['dr', 'debit'], true)) {
                $debitMap[(int)$row->aid] = ($debitMap[(int)$row->aid] ?? 0.0) + (float)$row->total;
            } elseif (in_array($type, ['cr', 'credit'], true)) {
                $creditMap[(int)$row->aid] = ($creditMap[(int)$row->aid] ?? 0.0) + (float)$row->total;
            }
        }

        $rows = [];
        $totalOpeningDebit = 0;
        $totalOpeningCredit = 0;
        $totalPeriodDebit = 0;
        $totalPeriodCredit = 0;
        $totalClosingDebit = 0;
        $totalClosingCredit = 0;

        foreach ($accounts as $acc) {
            $opening = (float)($openingMap[$acc->id] ?? 0);
            $opDebit = ($opening > 0) ? $opening : 0;
            $opCredit = ($opening < 0) ? abs($opening) : 0;

            $periodDebit = (float)($debitMap[$acc->id] ?? 0);
            $periodCredit = (float)($creditMap[$acc->id] ?? 0);

            if ($opDebit == 0 && $opCredit == 0 && $periodDebit == 0 && $periodCredit == 0) {
                continue;
            }

            $netClosing = ($opDebit + $periodDebit) - ($opCredit + $periodCredit);
            $closingDebit = ($netClosing > 0) ? $netClosing : 0;
            $closingCredit = ($netClosing < 0) ? abs($netClosing) : 0;

            $totalOpeningDebit += $opDebit;
            $totalOpeningCredit += $opCredit;
            $totalPeriodDebit += $periodDebit;
            $totalPeriodCredit += $periodCredit;
            $totalClosingDebit += $closingDebit;
            $totalClosingCredit += $closingCredit;

            $rows[] = [
                'account_id' => $acc->id,
                'account_name' => $acc->name,
                'account_group' => $acc->accountGroup?->name ?? 'General',
                'account_type' => $acc->accountGroup?->accountType?->name ?? 'Asset',
                'opening_debit' => $opDebit,
                'opening_credit' => $opCredit,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'closing_debit' => $closingDebit,
                'closing_credit' => $closingCredit,
            ];
        }

        return [
            'as_of_date' => $asOfDate,
            'rows' => $rows,
            'totals' => [
                'opening_debit' => round($totalOpeningDebit, 2),
                'opening_credit' => round($totalOpeningCredit, 2),
                'period_debit' => round($totalPeriodDebit, 2),
                'period_credit' => round($totalPeriodCredit, 2),
                'closing_debit' => round($totalClosingDebit, 2),
                'closing_credit' => round($totalClosingCredit, 2),
                'is_balanced' => (abs($totalClosingDebit - $totalClosingCredit) <= 0.05),
            ]
        ];
    }

    /**
     * Generate Profit & Loss Statement (P&L) for a shop and date range.
     *
     * @param int $shopId
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return array Profit & Loss Statement
     */
    public function generateProfitAndLoss(int $shopId, string $startDate, string $endDate): array
    {
        // 1. Sales / Revenue from General Ledger Voucher Entries (Primary Source of Truth)
        $glSalesCredit = (float) VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $startDate, $endDate) {
            $q->where('shop_id', $shopId)->whereBetween('date', [$startDate, $endDate]);
        })->whereHas('account', function ($q) {
            $q->whereIn('code', ['SALES_POS', 'SALES_WEB', 'SALES_B2B', 'SALES']);
        })->whereIn('type', ['Cr', 'credit'])->sum('amount');

        $glDeliveryIncomeCredit = (float) VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $startDate, $endDate) {
            $q->where('shop_id', $shopId)->whereBetween('date', [$startDate, $endDate]);
        })->whereHas('account', function ($q) {
            $q->whereIn('code', ['REV_DELIVERY']);
        })->whereIn('type', ['Cr', 'credit'])->sum('amount');

        $glSalesReturnsDebit = (float) VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $startDate, $endDate) {
            $q->where('shop_id', $shopId)->whereBetween('date', [$startDate, $endDate]);
        })->whereHas('account', function ($q) {
            $q->whereIn('code', ['SALES_RET']);
        })->whereIn('type', ['Dr', 'debit'])->sum('amount');

        $hasGlSalesVouchers = ($glSalesCredit > 0 || $glDeliveryIncomeCredit > 0 || $glSalesReturnsDebit > 0);

        if ($hasGlSalesVouchers) {
            $salesRevenue = $glSalesCredit;
            $deliveryIncome = $glDeliveryIncomeCredit;
            $taxableSalesReturns = $glSalesReturnsDebit;
            $netSales = max(0, $salesRevenue - $taxableSalesReturns);
        } else {
            // Fallback for pre-integration / unvouchered historical periods
            $orders = \App\Models\Order::withoutGlobalScopes()
                ->where('shop_id', $shopId)
                ->where('order_status', '!=', 'Cancelled')
                ->where('order_status', '!=', \App\Enums\OrderStatus::CANCELLED->value)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->with('products')
                ->get();

            $grossProductSales = (float)$orders->sum('total_amount');
            $salesTax = (float)$orders->sum('tax_amount');
            $salesRevenue = max(0, $grossProductSales - $salesTax);
            $deliveryIncome = (float)$orders->sum('delivery_charge');

            $posReturnsGross = (float)\App\Models\POSReturn::where('shop_id', $shopId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->sum('total_amount');

            $posReturnsTax = (float)\App\Models\POSReturn::where('shop_id', $shopId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->sum('tax_amount');

            $taxableSalesReturns = max(0, $posReturnsGross - $posReturnsTax);
            $netSales = max(0, $salesRevenue - $taxableSalesReturns);
        }

        // 2. Cost of Goods Sold (COGS) for recognized sold goods in period
        $ordersQuery = \App\Models\Order::withoutGlobalScopes()
            ->where('shop_id', $shopId)
            ->where('order_status', '!=', 'Cancelled')
            ->where('order_status', '!=', \App\Enums\OrderStatus::CANCELLED->value)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('products');

        if ($hasGlSalesVouchers) {
            $ordersQuery->where(function ($q) {
                $q->whereNotNull('voucher_id')
                  ->orWhere('order_status', \App\Enums\OrderStatus::DELIVERED->value)
                  ->orWhere('order_status', 'Delivered');
            });
        }

        // Eager-load the lines: without this, $ord->products lazy-loads per order
        // and the cost lookup fired once per line. Harmless while POS sales are
        // empty, crippling once a full financial year is replayed.
        $ordersInPeriod = $ordersQuery->with('products')->get();

        $costProductIds = [];
        foreach ($ordersInPeriod as $ord) {
            foreach ($ord->products as $p) {
                $costProductIds[] = $p->id;
            }
        }
        $costMap = $this->inventoryValuationService->getWeightedAverageCostMap($costProductIds);

        $cogs = 0.00;
        foreach ($ordersInPeriod as $ord) {
            foreach ($ord->products as $p) {
                $soldQty = (int)($p->pivot->quantity ?? 1);
                $cogs += ($soldQty * (float)($costMap[$p->id] ?? 0.00));
            }
        }

        $cogs = round($cogs, 2);

        $inventoryValuation = $this->inventoryValuationService->calculateShopInventoryValuation($shopId);
        $closingStockAsset = (float)$inventoryValuation['total_inventory_asset_value'];

        $grossProfit = round(($netSales + $deliveryIncome) - $cogs, 2);

        // 3. Operating & Indirect Expenses
        $expenseVouchers = VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $startDate, $endDate) {
            $q->where('shop_id', $shopId)
              ->whereBetween('date', [$startDate, $endDate]);
        })->whereHas('account.accountGroup', function ($q) {
            $q->where('name', 'like', '%Expense%');
        })->whereIn('type', ['Dr', 'debit'])->sum('amount');

        $operatingExpenses = (float)$expenseVouchers;
        $netProfit = round($grossProfit - $operatingExpenses, 2);

        return [
            'period' => "{$startDate} to {$endDate}",
            'revenue' => [
                'gross_sales' => round($salesRevenue, 2),
                'delivery_income' => round($deliveryIncome, 2),
                'sales_returns' => round($taxableSalesReturns, 2),
                'net_sales' => round($netSales + $deliveryIncome, 2),
            ],
            'cogs' => [
                'closing_stock_asset' => round($closingStockAsset, 2),
                'total_cogs' => round($cogs, 2),
            ],
            'gross_profit' => round($grossProfit, 2),
            'expenses' => [
                'operating_expenses' => round($operatingExpenses, 2),
            ],
            'net_profit' => round($netProfit, 2),
            'source_of_truth' => $hasGlSalesVouchers ? 'General Ledger Vouchers' : 'Operational Orders (Unvouchered Period)',
        ];
    }

    /**
     * Generate Statutory Balance Sheet for a shop as of a specific date.
     * Enforces Assets = Liabilities + Capital.
     *
     * @param int $shopId
     * @param string $asOfDate (YYYY-MM-DD)
     * @return array Balance Sheet Matrix
     */
    public function generateBalanceSheet(int $shopId, string $asOfDate): array
    {
        $startDate = date('Y-04-01', strtotime($asOfDate));
        if ($asOfDate < $startDate) {
            $startDate = date('Y-04-01', strtotime('-1 year', strtotime($asOfDate)));
        }

        $pnl = $this->generateProfitAndLoss($shopId, $startDate, $asOfDate);
        $netProfit = $pnl['net_profit'];

        $inventoryValuation = $this->inventoryValuationService->calculateShopInventoryValuation($shopId);
        $closingStockAsset = (float)$inventoryValuation['total_inventory_asset_value'];

        // Cash & Bank Balances from Opening Balance + VoucherEntries
        $cashBankOp = (float)\App\Models\AccountBalance::where('shop_id', $shopId)
            ->whereHas('account.accountGroup', function ($q) {
                $q->where('name', 'like', '%Cash%')->orWhere('name', 'like', '%Bank%');
            })->sum('opening_balance');

        $cashBankDr = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $asOfDate) {
            $q->where('shop_id', $shopId)->where('date', '<=', $asOfDate);
        })->whereHas('account.accountGroup', function ($q) {
            $q->where('name', 'like', '%Cash%')->orWhere('name', 'like', '%Bank%');
        })->whereIn('type', ['Dr', 'debit'])->sum('amount');

        $cashBankCr = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $asOfDate) {
            $q->where('shop_id', $shopId)->where('date', '<=', $asOfDate);
        })->whereHas('account.accountGroup', function ($q) {
            $q->where('name', 'like', '%Cash%')->orWhere('name', 'like', '%Bank%');
        })->whereIn('type', ['Cr', 'credit'])->sum('amount');

        $cashBankBalance = max(0, $cashBankOp + ($cashBankDr - $cashBankCr));

        // Customer Receivables (Debtors)
        $tradeReceivables = (float)\App\Models\AccountBalance::where('shop_id', $shopId)
            ->whereHas('account.accountGroup', function ($q) {
                $q->where('name', 'like', '%Sundry Debtors%')->orWhere('name', 'like', '%Customer%');
            })->sum('opening_balance');

        // Vendor Payables (Creditors) from AccountBalance + VoucherEntries
        $tradePayablesOp = (float)\App\Models\AccountBalance::where('shop_id', $shopId)
            ->whereHas('account.accountGroup', function ($q) {
                $q->where('name', 'like', '%Sundry Creditors%')->orWhere('name', 'like', '%Vendor%');
            })->sum('opening_balance');

        $tradePayablesPeriodCr = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $asOfDate) {
            $q->where('shop_id', $shopId)->where('date', '<=', $asOfDate);
        })->whereHas('account.accountGroup', function ($q) {
            $q->where('name', 'like', '%Sundry Creditors%')->orWhere('name', 'like', '%Vendor%');
        })->whereIn('type', ['Cr', 'credit'])->sum('amount');

        $tradePayablesPeriodDr = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $asOfDate) {
            $q->where('shop_id', $shopId)->where('date', '<=', $asOfDate);
        })->whereHas('account.accountGroup', function ($q) {
            $q->where('name', 'like', '%Sundry Creditors%')->orWhere('name', 'like', '%Vendor%');
        })->whereIn('type', ['Dr', 'debit'])->sum('amount');

        $tradePayables = max(0, $tradePayablesOp + ($tradePayablesPeriodCr - $tradePayablesPeriodDr));

        // Output GST Liability
        $outputGstCr = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $asOfDate) {
            $q->where('shop_id', $shopId)->where('date', '<=', $asOfDate);
        })->whereHas('account', function ($q) {
            $q->whereIn('code', ['CGST_OUT', 'SGST_OUT', 'IGST_OUT', 'GST_OUT_C', 'GST_OUT_S', 'GST_OUT_I']);
        })->whereIn('type', ['Cr', 'credit'])->sum('amount');

        $outputGstDr = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $asOfDate) {
            $q->where('shop_id', $shopId)->where('date', '<=', $asOfDate);
        })->whereHas('account', function ($q) {
            $q->whereIn('code', ['CGST_OUT', 'SGST_OUT', 'IGST_OUT', 'GST_OUT_C', 'GST_OUT_S', 'GST_OUT_I']);
        })->whereIn('type', ['Dr', 'debit'])->sum('amount');

        $outputGstLiability = max(0, $outputGstCr - $outputGstDr);

        // GST Input Credit Asset (ITC)
        $gstInputCreditDr = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $asOfDate) {
            $q->where('shop_id', $shopId)->where('date', '<=', $asOfDate);
        })->whereHas('account', function ($q) {
            $q->whereIn('code', ['CGST_IN', 'SGST_IN', 'IGST_IN', 'GST_IN_C', 'GST_IN_S', 'GST_IN_I']);
        })->whereIn('type', ['Dr', 'debit'])->sum('amount');

        $gstInputCreditCr = (float)VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $asOfDate) {
            $q->where('shop_id', $shopId)->where('date', '<=', $asOfDate);
        })->whereHas('account', function ($q) {
            $q->whereIn('code', ['CGST_IN', 'SGST_IN', 'IGST_IN', 'GST_IN_C', 'GST_IN_S', 'GST_IN_I']);
        })->whereIn('type', ['Cr', 'credit'])->sum('amount');

        $gstInputCredit = max(0, $gstInputCreditDr - $gstInputCreditCr);

        // Owner's Equity / Opening Capital from General Ledger AccountBalance
        $openingCapital = (float)\App\Models\AccountBalance::where('shop_id', $shopId)
            ->whereHas('account.accountGroup', function ($q) {
                $q->where('name', 'like', '%Capital%')->orWhere('name', 'like', '%Equity%');
            })->sum('opening_balance');

        $totalAssets = round($closingStockAsset + $cashBankBalance + $tradeReceivables + $gstInputCredit, 2);
        $totalLiabilities = round($tradePayables + $outputGstLiability, 2);
        $totalEquity = round($openingCapital + $netProfit, 2);
        $totalLiabilitiesAndCapital = round($totalLiabilities + $totalEquity, 2);

        return [
            'as_of_date' => $asOfDate,
            'assets' => [
                'inventory_asset_value' => round($closingStockAsset, 2),
                'cash_and_bank_balance' => round($cashBankBalance, 2),
                'trade_receivables' => round($tradeReceivables, 2),
                'gst_input_credit' => round($gstInputCredit, 2),
                'total_assets' => round($totalAssets, 2),
            ],
            'liabilities_and_equity' => [
                'trade_payables' => round($tradePayables, 2),
                'output_gst_liability' => round($outputGstLiability, 2),
                'total_liabilities' => round($totalLiabilities, 2),
                'opening_capital_equity' => round($openingCapital, 2),
                'reserves_and_surplus_net_profit' => round($netProfit, 2),
                'total_equity' => round($totalEquity, 2),
                'total_liabilities_and_equity' => round($totalLiabilitiesAndCapital, 2),
            ],
            'is_balanced' => (abs($totalAssets - $totalLiabilitiesAndCapital) <= 0.05),
        ];
    }

    /**
     * Generate General Ledger running statement for any account.
     */
    public function getGeneralLedger(int $shopId, int $accountId, string $startDate, string $endDate): array
    {
        $account = Account::with('accountGroup')->findOrFail($accountId);

        $openingBalanceRecord = \App\Models\AccountBalance::where('shop_id', $shopId)
            ->where('account_id', $accountId)
            ->first();

        $openingBal = (float)($openingBalanceRecord?->opening_balance ?? 0.00);

        $preStartDr = (float) VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $startDate) {
            $q->where('shop_id', $shopId)->where('date', '<', $startDate);
        })->where('account_id', $accountId)->whereIn('type', ['Dr', 'debit'])->sum('amount');

        $preStartCr = (float) VoucherEntry::whereHas('voucher', function ($q) use ($shopId, $startDate) {
            $q->where('shop_id', $shopId)->where('date', '<', $startDate);
        })->where('account_id', $accountId)->whereIn('type', ['Cr', 'credit'])->sum('amount');

        $effectiveOpening = $openingBal + ($preStartDr - $preStartCr);

        $entries = VoucherEntry::with('voucher')
            ->whereHas('voucher', function ($q) use ($shopId, $startDate, $endDate) {
                $q->where('shop_id', $shopId)->whereBetween('date', [$startDate, $endDate]);
            })
            ->where('account_id', $accountId)
            ->get();

        $runningBalance = $effectiveOpening;
        $ledgerRows = [];
        $totalDr = 0.0;
        $totalCr = 0.0;

        foreach ($entries as $e) {
            $amt = (float)$e->amount;
            $type = in_array($e->type, ['Dr', 'debit']) ? 'Dr' : 'Cr';
            if ($type === 'Dr') {
                $runningBalance += $amt;
                $totalDr += $amt;
            } else {
                $runningBalance -= $amt;
                $totalCr += $amt;
            }

            $ledgerRows[] = [
                'date' => $e->voucher?->date ? date('Y-m-d', strtotime($e->voucher->date)) : '',
                'voucher_no' => $e->voucher?->voucher_no ?? '',
                'voucher_type' => $e->voucher?->voucher_type ?? '',
                'narration' => $e->description ?? $e->voucher?->narration ?? '',
                'debit' => ($type === 'Dr') ? $amt : 0.00,
                'credit' => ($type === 'Cr') ? $amt : 0.00,
                'running_balance' => round($runningBalance, 2),
            ];
        }

        return [
            'account_id' => $account->id,
            'account_name' => $account->name,
            'account_code' => $account->code,
            'account_group' => $account->accountGroup?->name,
            'period' => "{$startDate} to {$endDate}",
            'opening_balance' => round($effectiveOpening, 2),
            'total_debit' => round($totalDr, 2),
            'total_credit' => round($totalCr, 2),
            'closing_balance' => round($runningBalance, 2),
            'rows' => $ledgerRows,
        ];
    }
}
