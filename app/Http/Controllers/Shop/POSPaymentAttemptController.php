<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Requests\PaymentResultRequest;
use App\Models\PosPaymentAttempt;
use App\Models\PaymentTerminal;
use App\Repositories\PaymentTerminalRepository;
use App\Repositories\PosPaymentAttemptRepository;
use App\Services\POS\PosPaymentAttemptService;
use App\Services\POS\BridgeRequestSigner;
use App\Services\POS\BridgeResponseVerifier;
use App\Services\POS\PosOrderFinalizationService;
use App\Enums\PosPaymentAttemptStatus;
use App\Exceptions\PaymentAmountMismatchException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class POSPaymentAttemptController extends Controller
{
    protected $terminalRepository;
    protected $attemptRepository;
    protected $attemptService;
    protected $signer;
    protected $verifier;
    protected $finalizationService;

    public function __construct(
        PaymentTerminalRepository $terminalRepository,
        PosPaymentAttemptRepository $attemptRepository,
        PosPaymentAttemptService $attemptService,
        BridgeRequestSigner $signer,
        BridgeResponseVerifier $verifier,
        PosOrderFinalizationService $finalizationService
    ) {
        $this->terminalRepository = $terminalRepository;
        $this->attemptRepository = $attemptRepository;
        $this->attemptService = $attemptService;
        $this->signer = $signer;
        $this->verifier = $verifier;
        $this->finalizationService = $finalizationService;
    }

    public function currentTerminal(Request $request)
    {
        $shop = auth()->user()->shop ?: \App\Models\Shop::first();
        $counterId = $request->query('counter_id');

        if (!$counterId) {
            return response()->json(['status' => false, 'message' => 'counter_id is required'], 422);
        }

        $terminal = $this->terminalRepository->findActiveByCounter($shop->id, $counterId);
        if (!$terminal) {
            return response()->json(['status' => false, 'message' => 'No active terminal mapped to this counter.'], 404);
        }

        return response()->json([
            'status' => true,
            'terminal' => [
                'id' => $terminal->id,
                'name' => $terminal->name,
                'provider' => $terminal->provider->value,
                'terminal_id' => $terminal->terminal_id,
                'merchant_id' => $terminal->merchant_id,
            ]
        ]);
    }

    public function initiate(InitiatePaymentRequest $request)
    {
        $shop = auth()->user()->shop ?: \App\Models\Shop::first();
        $cashierId = auth()->id();

        try {
            $attemptData = $this->attemptService->createAttempt($request->validated(), $shop, $cashierId);
            $terminal = PaymentTerminal::where('terminal_id', $attemptData['terminal']['terminal_id'])->first();
            $secret = $terminal->config_data['shared_secret'] ?? $terminal->shared_secret ?? 'default_secret';

            $tokenData = $this->signer->createSessionToken(
                userId: $cashierId,
                shopId: $shop->id,
                counterId: $request->input('counter_id'),
                terminalId: $attemptData['terminal']['terminal_id'],
                attemptUuid: $attemptData['attempt_id'],
                operation: 'sale',
                secretKey: $secret,
                ttlSeconds: 90
            );

            return response()->json([
                'status' => true,
                'message' => 'Payment attempt created successfully.',
                'attempt' => $attemptData,
                'bridge_session_token' => $tokenData['token'],
                'expires_at' => $tokenData['expires_at'],
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function acknowledge(Request $request, string $attemptId)
    {
        $attempt = $this->attemptRepository->findById($attemptId);
        if (!$attempt) {
            return response()->json(['status' => false, 'message' => 'Attempt not found.'], 404);
        }

        $attempt = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::SENT_TO_TERMINAL);

        return response()->json([
            'status' => true,
            'message' => 'Attempt acknowledged.',
            'attempt_status' => $attempt->status->value,
        ]);
    }

    public function result(PaymentResultRequest $request, string $attemptId)
    {
        $attempt = $this->attemptRepository->findById($attemptId, true);
        if (!$attempt) {
            return response()->json(['status' => false, 'message' => 'Attempt not found.'], 404);
        }

        if (in_array($attempt->status, [PosPaymentAttemptStatus::FINALIZED, PosPaymentAttemptStatus::VERIFIED, PosPaymentAttemptStatus::SUCCESS])) {
            return response()->json([
                'status' => true,
                'message' => 'Attempt result already processed.',
                'already_processed' => true,
                'attempt_status' => $attempt->status->value,
            ]);
        }

        $shop = auth()->user()->shop ?: \App\Models\Shop::first();
        $terminal = $attempt->terminal;
        $secret = $terminal->config_data['shared_secret'] ?? $terminal->shared_secret ?? 'default_secret';

        $this->verifier->verifySessionToken($request->input('bridge_token'), [
            'user_id' => auth()->id(),
            'shop_id' => $shop->id,
            'attempt_id' => $attemptId,
            'terminal_id' => $attempt->terminal_id,
        ], $secret);

        $reportedStatus = strtolower($request->input('status'));
        $approvedAmount = (float)$request->input('approved_amount');

        if ($reportedStatus === 'success' && abs($approvedAmount - (float)$attempt->amount) > 0.01) {
            $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::AMOUNT_MISMATCH, [
                'approved_amount' => $approvedAmount,
                'response_payload' => $request->input('raw_response'),
            ]);
            throw new PaymentAmountMismatchException($attempt->amount, $approvedAmount);
        }

        $targetStatus = match ($reportedStatus) {
            'success' => PosPaymentAttemptStatus::SUCCESS,
            'failed' => PosPaymentAttemptStatus::FAILED,
            'cancelled' => PosPaymentAttemptStatus::CANCELLED,
            default => PosPaymentAttemptStatus::UNKNOWN,
        };

        $updatedAttempt = $this->attemptRepository->transition($attempt, $targetStatus, [
            'approved_amount' => $approvedAmount,
            'transaction_id' => $request->input('transaction_id'),
            'reference_no' => $request->input('rrn'),
            'response_payload' => $request->input('raw_response'),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Result processed successfully.',
            'attempt_status' => $updatedAttempt->status->value,
        ]);
    }

    public function status(Request $request, string $attemptId)
    {
        $attempt = $this->attemptRepository->findById($attemptId);
        if (!$attempt) {
            return response()->json(['status' => false, 'message' => 'Attempt not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'attempt_status' => $attempt->status->value,
            'amount' => $attempt->amount,
            'approved_amount' => $attempt->approved_amount,
            'transaction_id' => $attempt->transaction_id,
        ]);
    }

    public function cancel(Request $request, string $attemptId)
    {
        $attempt = $this->attemptRepository->findById($attemptId);
        if (!$attempt) {
            return response()->json(['status' => false, 'message' => 'Attempt not found.'], 404);
        }

        $attempt = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::CANCELLED);

        return response()->json([
            'status' => true,
            'message' => 'Attempt cancelled.',
            'attempt_status' => $attempt->status->value,
        ]);
    }

    public function reconcile(Request $request, string $attemptId)
    {
        $attempt = $this->attemptRepository->findById($attemptId);
        if (!$attempt) {
            return response()->json(['status' => false, 'message' => 'Attempt not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'reconciled' => true,
            'attempt_status' => $attempt->status->value,
        ]);
    }
}
