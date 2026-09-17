<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountMaster;
use App\Models\BankMaster;
use App\Models\FinancialYear;
use App\Models\InwardInvoice;
use App\Models\Voucher;
use App\Services\Accounting\VoucherService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SupplierDuePaymentController extends Controller
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Display listing of supplier credit due payments with real-time due countdowns.
     */
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');
        $today = Carbon::today();

        // 1. Fetch active suppliers & banks for filter and payment modal
        $suppliers = AccountMaster::where('shop_id', $shop->id)
            ->where('is_active', 1)
            ->orderBy('accountName')
            ->get(['id', 'accountName', 'accountshortcode', 'cont_info_mobile1', 'contperson']);

        $bankMasters = BankMaster::where('shop_id', $shop->id)
            ->where('is_active', 1)
            ->with(['accountGroup:id,name,code'])
            ->get();

        // 2. Query Inward Invoices
        $query = InwardInvoice::where('shop_id', $shop->id)
            ->with(['partyCode', 'productPurchase'])
            ->orderBy('inward_date', 'desc');

        // Supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('inward_party_code', $request->supplier_id);
        }

        // Search by Voucher / Challan / Supplier Name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('inward_voucher_no', 'like', "%{$search}%")
                  ->orWhere('inward_challan_no', 'like', "%{$search}%")
                  ->orWhereHas('partyCode', function ($sq) use ($search) {
                      $sq->where('accountName', 'like', "%{$search}%")
                        ->orWhere('accountshortcode', 'like', "%{$search}%");
                  });
            });
        }

        // Inward Date Range Filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('inward_date', [$request->start_date, $request->end_date]);
        }

        $allInvoices = $query->get();

        // 3. Compute credit days, due date, status, paid & pending amounts
        $processedRows = collect();

        $totalCreditPayables = 0.00;
        $overdueCount = 0;
        $overdueAmount = 0.00;
        $due7DaysCount = 0;
        $due7DaysAmount = 0.00;
        $due15DaysCount = 0;
        $due15DaysAmount = 0.00;
        $settledCount = 0;

        foreach ($allInvoices as $inv) {
            $inwardDate = $inv->inward_date ? Carbon::parse($inv->inward_date) : Carbon::today();
            $creditDays = (int) ($inv->inward_credit_day ?? 0);
            $dueDate = $inwardDate->copy()->addDays($creditDays);

            // Days remaining relative to today
            $daysDiff = $today->diffInDays($dueDate, false); // negative if past, positive if future, 0 if today

            // Calculate total bill amount
            $totalAmount = (float) ($inv->inward_acc_amt_with_gst > 0 
                ? $inv->inward_acc_amt_with_gst 
                : ($inv->inward_total > 0 ? $inv->inward_total : 0));

            // Calculate paid amount from vouchers
            $voucherNo = $inv->inward_voucher_no;
            $supplierAccountId = $inv->partyCode?->account_id ?? 24;

            $paidAmount = (float) Voucher::where('shop_id', $shop->id)
                ->where('voucher_type', 'Payment')
                ->where(function ($vq) use ($voucherNo, $inv) {
                    $vq->where('narration', 'like', "%{$voucherNo}%")
                       ->orWhere('narration', 'like', "%INV-{$inv->id}%");
                })
                ->join('voucher_entries', 'vouchers.id', '=', 'voucher_entries.voucher_id')
                ->where('voucher_entries.type', 'Dr')
                ->where('voucher_entries.account_id', $supplierAccountId)
                ->sum('voucher_entries.amount');

            $outstanding = max(0.00, round($totalAmount - $paidAmount, 2));

            // Determine status
            if ($outstanding <= 0.01) {
                $status = 'settled';
                $statusText = __('Settled (Paid)');
                $badgeClass = 'badge bg-success';
                $settledCount++;
            } elseif ($daysDiff < 0) {
                $status = 'overdue';
                $overdueDays = abs($daysDiff);
                $statusText = __('Overdue by :days days', ['days' => $overdueDays]);
                $badgeClass = 'badge bg-danger';
                $overdueCount++;
                $overdueAmount += $outstanding;
                $totalCreditPayables += $outstanding;
            } elseif ($daysDiff == 0) {
                $status = 'due_today';
                $statusText = __('Due Today');
                $badgeClass = 'badge bg-warning text-dark';
                $due7DaysCount++;
                $due7DaysAmount += $outstanding;
                $totalCreditPayables += $outstanding;
            } elseif ($daysDiff <= 7) {
                $status = 'due_7_days';
                $statusText = __('Due in :days days', ['days' => $daysDiff]);
                $badgeClass = 'badge bg-primary';
                $due7DaysCount++;
                $due7DaysAmount += $outstanding;
                $totalCreditPayables += $outstanding;
            } elseif ($daysDiff <= 15) {
                $status = 'due_15_days';
                $statusText = __('Due in :days days', ['days' => $daysDiff]);
                $badgeClass = 'badge bg-info text-dark';
                $due15DaysCount++;
                $due15DaysAmount += $outstanding;
                $totalCreditPayables += $outstanding;
            } else {
                $status = 'due_later';
                $statusText = __('Due in :days days', ['days' => $daysDiff]);
                $badgeClass = 'badge bg-secondary';
                $totalCreditPayables += $outstanding;
            }

            $rowObj = (object) [
                'id' => $inv->id,
                'voucher_no' => $inv->inward_voucher_no,
                'challan_no' => $inv->inward_challan_no,
                'inward_date' => $inwardDate->format('Y-m-d'),
                'inward_date_formatted' => $inwardDate->format('d M, Y'),
                'credit_days' => $creditDays,
                'due_date' => $dueDate->format('Y-m-d'),
                'due_date_formatted' => $dueDate->format('d M, Y'),
                'days_remaining' => $daysDiff,
                'status' => $status,
                'status_text' => $statusText,
                'badge_class' => $badgeClass,
                'supplier_id' => $inv->partyCode?->id,
                'supplier_name' => $inv->partyCode?->accountName ?? __('Unknown Supplier'),
                'supplier_code' => $inv->partyCode?->accountshortcode ?? 'N/A',
                'supplier_phone' => $inv->partyCode?->cont_info_mobile1 ?? '',
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'outstanding' => $outstanding,
                'is_settled' => ($outstanding <= 0.01),
            ];

            // Apply Due Status Filter if specified
            $dueStatusFilter = $request->get('due_status', 'all');
            if ($dueStatusFilter === 'all') {
                $processedRows->push($rowObj);
            } elseif ($dueStatusFilter === 'overdue' && $status === 'overdue') {
                $processedRows->push($rowObj);
            } elseif ($dueStatusFilter === 'due_7_days' && in_array($status, ['due_today', 'due_7_days'])) {
                $processedRows->push($rowObj);
            } elseif ($dueStatusFilter === 'due_15_days' && in_array($status, ['due_today', 'due_7_days', 'due_15_days'])) {
                $processedRows->push($rowObj);
            } elseif ($dueStatusFilter === 'settled' && $status === 'settled') {
                $processedRows->push($rowObj);
            } elseif ($dueStatusFilter === 'pending' && $status !== 'settled') {
                $processedRows->push($rowObj);
            }
        }

        // Sort: Overdue first, then by days remaining ascending, then settled last
        $sortedRows = $processedRows->sortBy([
            ['is_settled', 'asc'],
            ['days_remaining', 'asc'],
        ]);

        // Paginate manually
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $sortedRows->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $invoices = new LengthAwarePaginator($currentItems, $sortedRows->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        $metrics = [
            'totalCreditPayables' => $totalCreditPayables,
            'overdueCount' => $overdueCount,
            'overdueAmount' => $overdueAmount,
            'due7DaysCount' => $due7DaysCount,
            'due7DaysAmount' => $due7DaysAmount,
            'due15DaysCount' => $due15DaysCount,
            'due15DaysAmount' => $due15DaysAmount,
            'settledCount' => $settledCount,
        ];

        return view('shop.supplier-due-payment.index', compact(
            'invoices',
            'suppliers',
            'bankMasters',
            'metrics'
        ));
    }

    /**
     * Process disbursement payment using selected Bank Master account.
     */
    public function pay(Request $request)
    {
        $request->validate([
            'inward_invoice_id' => 'required|exists:inward_invoices,id',
            'bank_master_id' => 'required|exists:bank_masters,id',
            'payment_mode' => 'required|string|in:neft_rtgs,cheque,net_banking,upi,cash',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference_no' => 'nullable|string|max:100',
            'narration' => 'nullable|string|max:500',
        ]);

        $shop = generaleSetting('shop');

        try {
            return DB::transaction(function () use ($request, $shop) {
                $inward = InwardInvoice::with('partyCode')->where('shop_id', $shop->id)->findOrFail($request->inward_invoice_id);
                $supplier = $inward->partyCode;

                if (!$supplier) {
                    throw new Exception(__('Supplier details not found for this invoice.'));
                }

                $bankMaster = BankMaster::where('shop_id', $shop->id)->findOrFail($request->bank_master_id);

                $payAmount = (float) $request->amount;
                $paymentMode = $request->payment_mode;
                $modeLabel = match ($paymentMode) {
                    'neft_rtgs' => 'NEFT / RTGS',
                    'cheque' => 'Cheque',
                    'net_banking' => 'Net Banking',
                    'upi' => 'UPI',
                    'cash' => 'Cash',
                    default => strtoupper($paymentMode),
                };

                // Resolve Supplier Payable Account
                $supplierAccountId = $supplier->account_id 
                    ?? Account::where('code', 'SUPP_TRADE')->value('id') 
                    ?? 24;

                // Resolve Bank Account from Bank Master
                $creditAccountId = Account::where('name', 'like', "%{$bankMaster->bank_name}%")->value('id')
                    ?? Account::where('code', 'BANK_HDFC')->value('id')
                    ?? Account::where('account_group_id', 18)->value('id')
                    ?? 20;

                // Active Financial Year
                $financialYear = FinancialYear::where('shop_id', $shop->id)->where('is_active', 1)->first()
                    ?? FinancialYear::latest()->first();

                if (!$financialYear) {
                    throw new Exception(__('Active financial year not found.'));
                }

                // Reference details
                $refString = $request->filled('reference_no') ? " [Ref/UTR: {$request->reference_no}]" : "";
                $invoiceRef = "Inward Inv #{$inward->inward_voucher_no}";

                // Double Entry
                $entries = [
                    // 1. DEBIT: Supplier Payable A/c (reducing liability)
                    [
                        'account_id' => $supplierAccountId,
                        'type' => 'Dr',
                        'amount' => $payAmount,
                        'description' => "Supplier Payment to {$supplier->accountName} for {$invoiceRef}{$refString}",
                    ],
                    // 2. CREDIT: Bank A/c (outflow of funds)
                    [
                        'account_id' => $creditAccountId,
                        'type' => 'Cr',
                        'amount' => $payAmount,
                        'description' => "Disbursed via {$bankMaster->bank_name} ({$bankMaster->bank_ac_no}) - {$modeLabel}",
                    ],
                ];

                $narration = $request->filled('narration') 
                    ? $request->narration 
                    : "Supplier payment to {$supplier->accountName} for {$invoiceRef} via {$bankMaster->bank_name} ({$modeLabel}){$refString}";

                // Post voucher via VoucherService
                $voucher = $this->voucherService->create([
                    'voucher_type' => 'Payment',
                    'date' => $request->payment_date,
                    'narration' => $narration,
                    'shop_id' => $shop->id,
                    'financial_year_id' => $financialYear->id,
                    'seq_prefix' => 'PMT-' . $shop->id . '-',
                    'entries' => $entries,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => __('Payment of ₹:amount successfully processed via :bank (:voucher)!', [
                        'amount' => number_format($payAmount, 2),
                        'bank' => $bankMaster->bank_name,
                        'voucher' => $voucher->voucher_no,
                    ]),
                    'voucher_no' => $voucher->voucher_no,
                ]);
            });
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() ?: __('Failed to process payment disbursement.'),
            ], 422);
        }
    }

    /**
     * Get payment history for a specific Inward Invoice.
     */
    public function history($inwardInvoiceId)
    {
        $shop = generaleSetting('shop');
        $inward = InwardInvoice::with('partyCode')->where('shop_id', $shop->id)->findOrFail($inwardInvoiceId);

        $voucherNo = $inward->inward_voucher_no;
        $supplierAccountId = $inward->partyCode?->account_id ?? 24;

        $vouchers = Voucher::with(['entries.account'])
            ->where('shop_id', $shop->id)
            ->where('voucher_type', 'Payment')
            ->where(function ($vq) use ($voucherNo, $inward) {
                $vq->where('narration', 'like', "%{$voucherNo}%")
                   ->orWhere('narration', 'like', "%INV-{$inward->id}%");
            })
            ->orderByDesc('date')
            ->get();

        $history = [];
        foreach ($vouchers as $v) {
            $paidEntry = $v->entries->where('type', 'Dr')->first();
            $crEntry = $v->entries->where('type', 'Cr')->first();

            $history[] = [
                'voucher_no' => $v->voucher_no,
                'date' => $v->date ? Carbon::parse($v->date)->format('d M, Y') : '-',
                'amount' => (float) ($paidEntry?->amount ?? 0),
                'bank_or_mode' => $crEntry?->account?->name ?? __('Bank Account'),
                'narration' => $v->narration,
            ];
        }

        return response()->json([
            'status' => true,
            'invoice_no' => $inward->inward_voucher_no,
            'supplier_name' => $inward->partyCode?->accountName ?? 'N/A',
            'history' => $history,
        ]);
    }
}
