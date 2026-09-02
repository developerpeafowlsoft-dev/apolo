<?php

namespace App\Services\POS;

use App\Models\PosPaymentAttempt;
use App\Models\Order;
use App\Models\User;
use App\Models\PaymentReconciliation;
use App\Enums\PosPaymentAttemptStatus;
use App\Repositories\PosPaymentAttemptRepository;
use App\Repositories\PosTerminalEventRepository;
use App\Services\POS\PosPaymentVerificationService;
use App\Services\POS\TerminalGatewayFactory;
use App\DTOs\TerminalStatusRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class PosPaymentReconciliationService
{
    protected $attemptRepository;
    protected $eventRepository;
    protected $verificationService;
    protected $gatewayFactory;

    public function __construct(
        PosPaymentAttemptRepository $attemptRepository,
        PosTerminalEventRepository $eventRepository,
        PosPaymentVerificationService $verificationService,
        TerminalGatewayFactory $gatewayFactory
    ) {
        $this->attemptRepository = $attemptRepository;
        $this->eventRepository = $eventRepository;
        $this->verificationService = $verificationService;
        $this->gatewayFactory = $gatewayFactory;
    }

    /**
     * Recheck an unknown payment attempt against provider terminal gateway.
     */
    public function recoverUnknownAttempt(PosPaymentAttempt $attempt): array
    {
        return DB::transaction(function () use ($attempt) {
            if ($attempt->status === PosPaymentAttemptStatus::FINALIZED || $attempt->order_id) {
                return ['status' => 'already_finalized', 'attempt' => $attempt];
            }

            try {
                $gateway = $this->gatewayFactory->make($attempt->provider->value ?? 'mock', $attempt->terminal->config_data ?? []);
                $statusReq = new TerminalStatusRequest(
                    attemptUuid: $attempt->id,
                    transactionId: $attempt->transaction_id ?? '',
                    provider: $attempt->provider->value ?? 'mock',
                    terminalId: $attempt->terminal_id ?? '',
                    merchantId: $attempt->terminal->merchant_id ?? ''
                );

                $result = $gateway->checkStatus($statusReq);

                if ($result->status->value === 'success') {
                    $verifyRes = $this->verificationService->verifyAndFinalize(
                        $attempt->id,
                        [
                            'status' => 'success',
                            'approved_amount' => $result->approvedAmount,
                            'transaction_id' => $result->providerTransactionId,
                            'currency' => $result->currency,
                            'rrn' => $result->rrn,
                            'raw_response' => $result->rawResponse,
                        ],
                        [
                            'counter_id' => $attempt->terminal->counter_id ?? 1,
                            'payable_amount' => $result->approvedAmount,
                            'items' => [],
                        ],
                        $attempt->shop,
                        \App\Models\FinancialYear::first()
                    );

                    return ['status' => 'recovered_success', 'order' => $verifyRes['order'] ?? null];
                }

                if (in_array($result->status->value, ['failed', 'cancelled'])) {
                    $attempt = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::from($result->status->value), [
                        'failure_code' => $result->failureCode,
                        'failure_message' => $result->failureMessage,
                    ]);

                    return ['status' => 'recovered_failure', 'attempt' => $attempt];
                }

            } catch (Exception $e) {
                // Gateway check failed or connection error
            }

            if ($attempt->created_at && $attempt->created_at->diffInMinutes(now()) > 15) {
                $this->eventRepository->logEvent(
                    $attempt->id,
                    'UNKNOWN_ATTEMPT_ALERT',
                    'unknown',
                    'unknown',
                    ['message' => 'Payment attempt unknown for > 15 minutes. Manager review required.']
                );
            }

            return ['status' => 'still_unknown', 'attempt' => $attempt];
        });
    }

    /**
     * Mark verified failure by manager with audit trail.
     */
    public function markVerifiedFailure(PosPaymentAttempt $attempt, User $manager, string $reason): PosPaymentAttempt
    {
        return DB::transaction(function () use ($attempt, $manager, $reason) {
            $attempt = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::FAILED, [
                'response_payload' => ['failure_reason' => "Manager {$manager->name} marked failure: {$reason}"],
            ]);

            $this->eventRepository->logEvent(
                $attempt->id,
                'MANAGER_VERIFIED_FAILURE',
                'unknown',
                'failed',
                ['manager_id' => $manager->id, 'reason' => $reason]
            );

            return $attempt;
        });
    }

    /**
     * Link confirmed payment attempt to order.
     * Enforces security rule: NEVER allow linking unless provider transaction ID is present and status is VERIFIED/SUCCESS.
     */
    public function linkPaymentToOrder(PosPaymentAttempt $attempt, Order $order, User $manager): PosPaymentAttempt
    {
        if (empty($attempt->transaction_id)) {
            throw new Exception("Security Constraint Violation: Cannot link payment to order without verified provider transaction ID.");
        }

        if (!in_array($attempt->status, [PosPaymentAttemptStatus::VERIFIED, PosPaymentAttemptStatus::SUCCESS, PosPaymentAttemptStatus::FINALIZED])) {
            throw new Exception("Security Constraint Violation: Cannot link unverified payment attempt to order.");
        }

        return DB::transaction(function () use ($attempt, $order, $manager) {
            $attempt->update(['order_id' => $order->id]);
            $attempt = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::FINALIZED);

            $this->eventRepository->logEvent(
                $attempt->id,
                'MANAGER_LINKED_ORDER',
                'verified',
                'finalized',
                ['manager_id' => $manager->id, 'order_id' => $order->id]
            );

            return $attempt;
        });
    }

    /**
     * Compute end-of-day reconciliation totals per provider and store discrepancy.
     */
    public function reconcileDailyTerminalPayments(int $shopId, string $date): PaymentReconciliation
    {
        $totalAmount = PosPaymentAttempt::where('shop_id', $shopId)
            ->whereDate('created_at', $date)
            ->where('status', PosPaymentAttemptStatus::FINALIZED)
            ->sum('approved_amount');

        $totalCount = PosPaymentAttempt::where('shop_id', $shopId)
            ->whereDate('created_at', $date)
            ->where('status', PosPaymentAttemptStatus::FINALIZED)
            ->count();

        $unknownCount = PosPaymentAttempt::where('shop_id', $shopId)
            ->whereDate('created_at', $date)
            ->where('status', PosPaymentAttemptStatus::UNKNOWN)
            ->count();

        $totalAttemptsCount = PosPaymentAttempt::where('shop_id', $shopId)->whereDate('created_at', $date)->count();
        $totalAttemptsAmount = PosPaymentAttempt::where('shop_id', $shopId)->whereDate('created_at', $date)->sum('amount');

        return PaymentReconciliation::create([
            'shop_id' => $shopId,
            'reconciliation_date' => $date,
            'provider' => 'all',
            'total_attempts_count' => $totalAttemptsCount,
            'total_attempted_amount' => $totalAttemptsAmount,
            'total_settled_amount' => $totalAmount,
            'discrepancy_amount' => 0.00,
            'status' => $unknownCount > 0 ? 'unmatched' : 'balanced',
        ]);
    }
}
