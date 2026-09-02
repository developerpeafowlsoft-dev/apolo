<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\PosPaymentAttempt;
use App\Models\PaymentTerminal;
use App\Models\CounterMaster;
use App\Services\POS\PosPaymentReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PosPaymentReconciliationController extends Controller
{
    protected $reconciliationService;

    public function __construct(PosPaymentReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    public function index(Request $request)
    {
        $shop = auth()->user()->shop ?? \App\Models\Shop::first();
        $query = PosPaymentAttempt::with(['terminal', 'order'])->where('shop_id', $shop->id ?? 1);

        if ($request->filled('counter_id')) {
            $query->whereHas('terminal', function ($q) use ($request) {
                $q->where('counter_id', $request->input('counter_id'));
            });
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->input('provider'));
        }

        if ($request->filled('terminal_id')) {
            $query->where('terminal_id', $request->input('terminal_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->input('transaction_id') . '%');
        }

        if ($request->filled('rrn')) {
            $query->where('reference_no', 'like', '%' . $request->input('rrn') . '%');
        }

        $attempts = $query->orderBy('created_at', 'desc')->paginate(20);
        $terminals = PaymentTerminal::where('shop_id', $shop->id ?? 1)->get();
        $counters = CounterMaster::where('shop_id', $shop->id ?? 1)->get();

        return view('shop.pos.reconciliation', compact('attempts', 'terminals', 'counters'));
    }

    public function recheck(Request $request, string $id): JsonResponse
    {
        $attempt = PosPaymentAttempt::findOrFail($id);
        $result = $this->reconciliationService->recoverUnknownAttempt($attempt);

        return response()->json([
            'status' => true,
            'result' => $result,
        ]);
    }

    public function markFailed(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $attempt = PosPaymentAttempt::findOrFail($id);
        $updatedAttempt = $this->reconciliationService->markVerifiedFailure($attempt, auth()->user(), $request->input('reason'));

        return response()->json([
            'status' => true,
            'attempt' => $updatedAttempt,
        ]);
    }

    public function export(Request $request)
    {
        $shop = auth()->user()->shop ?? \App\Models\Shop::first();
        $date = $request->input('date', date('Y-m-d'));
        $reconciliation = $this->reconciliationService->reconcileDailyTerminalPayments($shop->id ?? 1, $date);

        return response()->json([
            'status' => true,
            'reconciliation' => $reconciliation,
        ]);
    }
}
