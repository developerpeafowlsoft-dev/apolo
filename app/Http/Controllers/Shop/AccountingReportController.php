<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountMaster;
use App\Models\Voucher;
use App\Services\Accounting\Gstr1ReportingService;
use App\Services\Accounting\Gstr2bReconciliationService;
use App\Services\Accounting\Gstr3bService;
use App\Services\Accounting\GstEInvoiceService;
use App\Services\Accounting\InventoryValuationService;
use App\Services\Accounting\FinancialStatementService;
use App\Services\Accounting\BankReconciliationService;
use App\Services\Accounting\OutstandingAgeingService;
use App\Services\Accounting\PurchaseReturnService;
use App\Services\Accounting\SupplierPaymentService;
use App\Services\Accounting\CodSettlementService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountingReportController extends Controller
{
    protected $gstr1Service;
    protected $gstr2bService;
    protected $gstr3bService;
    protected $eInvoiceService;
    protected $valuationService;
    protected $statementService;
    protected $brsService;
    protected $ageingService;
    protected $purchaseReturnService;
    protected $supplierPaymentService;
    protected $codSettlementService;

    public function __construct(
        Gstr1ReportingService $gstr1Service,
        Gstr2bReconciliationService $gstr2bService,
        Gstr3bService $gstr3bService,
        GstEInvoiceService $eInvoiceService,
        InventoryValuationService $valuationService,
        FinancialStatementService $statementService,
        BankReconciliationService $brsService,
        OutstandingAgeingService $ageingService,
        PurchaseReturnService $purchaseReturnService,
        SupplierPaymentService $supplierPaymentService,
        CodSettlementService $codSettlementService
    ) {
        $this->gstr1Service = $gstr1Service;
        $this->gstr2bService = $gstr2bService;
        $this->gstr3bService = $gstr3bService;
        $this->eInvoiceService = $eInvoiceService;
        $this->valuationService = $valuationService;
        $this->statementService = $statementService;
        $this->brsService = $brsService;
        $this->ageingService = $ageingService;
        $this->purchaseReturnService = $purchaseReturnService;
        $this->supplierPaymentService = $supplierPaymentService;
        $this->codSettlementService = $codSettlementService;
    }

    /**
     * Accounting Overview Dashboard.
     */
    public function accountingDashboard(Request $request)
    {
        $shop = generaleSetting('shop');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $pnl = $this->statementService->generateProfitAndLoss($shop->id, $startDate, $endDate);
        $trialBalance = $this->statementService->generateTrialBalance($shop->id, $endDate);
        $valuationData = $this->valuationService->calculateShopInventoryValuation($shop->id);
        $codSummary = $this->codSettlementService->getCodReceivableSummary($shop->id);
        $gstr3b = $this->gstr3bService->computeGstr3b($shop->id, $startDate, $endDate);

        $vouchersCount = Voucher::where('shop_id', $shop->id)->count();
        $accountsCount = Account::count();

        return view('shop.reports.accounting_dashboard', compact(
            'pnl',
            'trialBalance',
            'valuationData',
            'codSummary',
            'gstr3b',
            'vouchersCount',
            'accountsCount',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Vouchers & Journal Entries Explorer.
     */
    public function vouchers(Request $request)
    {
        $shop = generaleSetting('shop');
        $voucherType = $request->get('voucher_type');
        $search = $request->get('search');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $vouchers = Voucher::with(['entries.account.accountGroup'])
            ->where('shop_id', $shop->id)
            ->when($voucherType, fn($q) => $q->where('voucher_type', $voucherType))
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->when($search, fn($q) => $q->where(function($qq) use ($search) {
                $qq->where('voucher_no', 'like', "%{$search}%")
                   ->orWhere('narration', 'like', "%{$search}%");
            }))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $voucherTypes = ['Sales', 'Purchase', 'Payment', 'Receipt', 'Debit Note', 'Credit Note', 'Journal'];

        return view('shop.reports.vouchers', compact('vouchers', 'voucherTypes', 'voucherType', 'startDate', 'endDate', 'search'));
    }

    /**
     * General Ledger Statement for any specific account.
     */
    public function generalLedger(Request $request)
    {
        $shop = generaleSetting('shop');
        $accountId = (int) $request->get('account_id', 9); // Defaults to POS Sales (9) or first account
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $accounts = Account::with('accountGroup.accountType')->orderBy('name')->get();
        $selectedAccount = Account::find($accountId) ?? $accounts->first();

        $ledgerData = null;
        if ($selectedAccount) {
            $ledgerData = $this->statementService->getGeneralLedger($shop->id, $selectedAccount->id, $startDate, $endDate);
        }

        return view('shop.reports.general_ledger', compact('accounts', 'selectedAccount', 'ledgerData', 'startDate', 'endDate'));
    }

    /**
     * Purchase Returns & Debit Notes.
     */
    public function purchaseReturns(Request $request)
    {
        $shop = generaleSetting('shop');
        $eligiblePurchases = $this->purchaseReturnService->getEligiblePurchaseInvoices($shop->id);

        $debitNotes = Voucher::with('entries.account')
            ->where('shop_id', $shop->id)
            ->where('voucher_type', 'Debit Note')
            ->orderByDesc('date')
            ->get();

        return view('shop.reports.purchase_returns', compact('eligiblePurchases', 'debitNotes'));
    }

    /**
     * Supplier Bill-by-Bill Payment Settlements.
     */
    public function supplierPayments(Request $request)
    {
        $shop = generaleSetting('shop');
        $supplierId = (int) $request->get('supplier_id', 0);
        $suppliers = AccountMaster::where('shop_id', $shop->id)->where('is_active', 1)->get();

        $outstandingBills = null;
        if ($supplierId > 0) {
            $outstandingBills = $this->supplierPaymentService->getSupplierOutstandingBills($shop->id, $supplierId);
        }

        $paymentHistory = Voucher::with('entries.account')
            ->where('shop_id', $shop->id)
            ->where('voucher_type', 'Payment')
            ->orderByDesc('date')
            ->paginate(20);

        return view('shop.reports.supplier_payments', compact('suppliers', 'supplierId', 'outstandingBills', 'paymentHistory'));
    }

    /**
     * COD Remittance & Reconciliation.
     */
    public function codReconciliation(Request $request)
    {
        $shop = generaleSetting('shop');
        $codSummary = $this->codSettlementService->getCodReceivableSummary($shop->id);

        $settlements = Voucher::with('entries.account')
            ->where('shop_id', $shop->id)
            ->where('voucher_type', 'Receipt')
            ->where('narration', 'like', '%COD%')
            ->orderByDesc('date')
            ->get();

        return view('shop.reports.cod_reconciliation', compact('codSummary', 'settlements'));
    }

    /**
     * Inventory Valuation Detail.
     */
    public function inventoryValuation(Request $request)
    {
        $shop = generaleSetting('shop');
        $valuationData = $this->valuationService->calculateShopInventoryValuation($shop->id);

        // The breakdown is one row per physical SKU - 4,132 of them here, which
        // rendered a 4.4 MB page. Totals still come from $valuationData, so
        // paginating the rows leaves every headline figure intact.
        $search = trim((string)$request->input('search'));
        $rows = collect($valuationData['products'] ?? []);

        if ($search !== '') {
            $rows = $rows->filter(fn ($r) => stripos((string)($r['name'] ?? ''), $search) !== false)->values();
        }

        $perPage = 50;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $products = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('shop.reports.inventory_valuation', compact('valuationData', 'products', 'search'));
    }

    /**
     * GST Statutory Report Dashboard.
     */
    public function gstDashboard(Request $request)
    {
        $shop = generaleSetting('shop');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $gstr1Data = $this->gstr1Service->generateGstr1Data($shop->id, $startDate, $endDate);
        $gstr3bData = $this->gstr3bService->computeGstr3b($shop->id, $startDate, $endDate);
        $valuationData = $this->valuationService->calculateShopInventoryValuation($shop->id);

        return view('shop.reports.gst_dashboard', compact('gstr1Data', 'gstr3bData', 'valuationData', 'startDate', 'endDate'));
    }

    public function exportGstr1Json(Request $request)
    {
        $shop = generaleSetting('shop');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $json = $this->gstr1Service->exportGstr1Json($shop->id, $startDate, $endDate);
        $filename = "GSTR1_Export_{$shop->id}_" . date('Ymd') . ".json";

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function reconcileGstr2b(Request $request)
    {
        $shop = generaleSetting('shop');
        $payload = [];

        if ($request->hasFile('gstr2b_json')) {
            $jsonString = file_get_contents($request->file('gstr2b_json')->getRealPath());
            $payload = json_decode($jsonString, true) ?? [];
        } else {
            $payload = $this->gstr2bService->fetchGstr2bViaGsp($shop->id, $shop->gst_no ?? '24AAAPA1234A1Z5', date('mY'));
        }

        $reconResult = $this->gstr2bService->reconcileGstr2b($shop->id, $payload);
        return response()->json(['success' => true, 'reconciliation' => $reconResult]);
    }

    public function financialStatements(Request $request)
    {
        $shop = generaleSetting('shop');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $trialBalance = $this->statementService->generateTrialBalance($shop->id, $endDate);
        $pnl = $this->statementService->generateProfitAndLoss($shop->id, $startDate, $endDate);
        $balanceSheet = $this->statementService->generateBalanceSheet($shop->id, $endDate);

        return view('shop.reports.financial_statements', compact('trialBalance', 'pnl', 'balanceSheet', 'startDate', 'endDate'));
    }

    public function bankReconciliation(Request $request)
    {
        $shop = generaleSetting('shop');
        $asOfDate = $request->get('as_of_date', now()->toDateString());
        $bankAccountId = (int)$request->get('bank_account_id', 1);
        $passbookBal = (float)$request->get('passbook_balance', 0.00);

        $brsData = $this->brsService->generateBrs($shop->id, $bankAccountId, $passbookBal);
        return view('shop.reports.bank_reconciliation', compact('brsData', 'asOfDate', 'bankAccountId', 'passbookBal'));
    }

    public function outstandingAgeing(Request $request)
    {
        $shop = generaleSetting('shop');
        $type = strtoupper($request->get('type', 'DEBTORS'));
        $asOfDate = $request->get('as_of_date', now()->toDateString());

        $ageingData = $this->ageingService->generateOutstandingAgeing($shop->id, $type, $asOfDate);
        return view('shop.reports.outstanding_ageing', compact('ageingData', 'type', 'asOfDate'));
    }
}
