<?php

namespace App\Services\POS;

use App\Models\PosPaymentAttempt;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Enums\PosPaymentAttemptStatus;
use App\Repositories\PosPaymentAttemptRepository;
use App\Repositories\PosTerminalEventRepository;
use App\Services\POS\TerminalGatewayFactory;
use App\Services\Accounting\VoucherService;
use App\DTOs\TerminalVoidRequest;
use App\DTOs\TerminalRefundRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PosRefundService
{
    protected $attemptRepository;
    protected $eventRepository;
    protected $gatewayFactory;
    protected $voucherService;

    public function __construct(
        PosPaymentAttemptRepository $attemptRepository,
        PosTerminalEventRepository $eventRepository,
        TerminalGatewayFactory $gatewayFactory,
        VoucherService $voucherService
    ) {
        $this->attemptRepository = $attemptRepository;
        $this->eventRepository = $eventRepository;
        $this->gatewayFactory = $gatewayFactory;
        $this->voucherService = $voucherService;
    }

    /**
     * Perform same-day terminal void.
     */
    public function voidTransaction(PosPaymentAttempt $attempt, User $manager, string $reason): array
    {
        return DB::transaction(function () use ($attempt, $manager, $reason) {
            if ($attempt->status === PosPaymentAttemptStatus::VOIDED) {
                return ['status' => 'already_voided', 'attempt' => $attempt];
            }

            if (!in_array($attempt->status, [PosPaymentAttemptStatus::FINALIZED, PosPaymentAttemptStatus::VERIFIED, PosPaymentAttemptStatus::SUCCESS])) {
                throw new Exception("Only verified or finalized payment attempts can be voided.");
            }

            if (empty($attempt->transaction_id)) {
                throw new Exception("Cannot void payment attempt without a valid provider transaction ID.");
            }

            $gateway = $this->gatewayFactory->make($attempt->provider->value ?? 'mock', $attempt->terminal->config_data ?? []);
            $voidReq = new TerminalVoidRequest(
                attemptUuid: $attempt->id,
                transactionId: $attempt->transaction_id,
                originalAmount: (float)$attempt->approved_amount,
                provider: $attempt->provider->value ?? 'mock',
                terminalId: $attempt->terminal_id ?? '',
                merchantId: $attempt->terminal->merchant_id ?? '',
                reason: $reason
            );

            $result = $gateway->void($voidReq);

            if ($result->normalizedStatus->value !== 'voided') {
                throw new Exception("Terminal void request failed: " . ($result->failureMessage ?? 'Declined by gateway.'));
            }

            $attempt = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::VOIDED, [
                'response_payload' => $result->safeRawResponse,
            ]);

            if ($order = Order::withoutGlobalScopes()->find($attempt->order_id)) {
                foreach ($order->products as $prod) {
                    $qty = $prod->pivot->quantity ?? 1;
                    Product::where('id', $prod->id)->increment('quantity', $qty);
                }
            }

            $this->eventRepository->logEvent(
                $attempt->id,
                'TERMINAL_VOID_SUCCESS',
                'finalized',
                'voided',
                ['manager_id' => $manager->id, 'reason' => $reason]
            );

            return [
                'status' => 'voided',
                'attempt' => $attempt,
                'provider_result' => $result,
            ];
        });
    }

    /**
     * Perform post-settlement terminal refund (partial or full).
     */
    public function refundTransaction(PosPaymentAttempt $attempt, float $refundAmount, User $manager, string $reason, array $returnItems = []): array
    {
        return DB::transaction(function () use ($attempt, $refundAmount, $manager, $reason, $returnItems) {
            if ($refundAmount <= 0) {
                throw new Exception("Refund amount must be greater than zero.");
            }

            $payload = is_array($attempt->response_payload) ? $attempt->response_payload : (json_decode($attempt->response_payload ?? '[]', true) ?: []);
            $approvedAmount = (float)$attempt->approved_amount;
            $previouslyRefunded = (float)($payload['refunded_total'] ?? 0.00);
            $refundableBalance = round($approvedAmount - $previouslyRefunded, 2);

            if (round($refundAmount, 2) > $refundableBalance) {
                throw new Exception("Refund amount ₹{$refundAmount} exceeds refundable balance ₹{$refundableBalance}.");
            }

            if (empty($attempt->transaction_id)) {
                throw new Exception("Cannot process refund without a valid provider transaction ID.");
            }

            $refundUuid = (string) Str::uuid();

            $gateway = $this->gatewayFactory->make($attempt->provider->value ?? 'mock', $attempt->terminal->config_data ?? []);
            $refundReq = new TerminalRefundRequest(
                attemptUuid: $attempt->id,
                transactionId: $attempt->transaction_id,
                refundAmount: $refundAmount,
                originalAmount: $approvedAmount,
                provider: $attempt->provider->value ?? 'mock',
                terminalId: $attempt->terminal_id ?? '',
                merchantId: $attempt->terminal->merchant_id ?? '',
                reason: $reason
            );

            $result = $gateway->refund($refundReq);

            if (in_array($result->normalizedStatus->value, ['refunded', 'partially_refunded'])) {
                $newRefundedTotal = round($previouslyRefunded + $refundAmount, 2);
                $newStatus = ($newRefundedTotal >= $approvedAmount)
                    ? PosPaymentAttemptStatus::REFUNDED
                    : PosPaymentAttemptStatus::PARTIALLY_REFUNDED;

                $payload['refunded_total'] = $newRefundedTotal;
                $payload['last_refund_reference'] = $refundUuid;

                $attempt = $this->attemptRepository->transition($attempt, $newStatus, [
                    'response_payload' => array_merge($payload, (array)$result->safeRawResponse),
                ]);

                foreach ($returnItems as $item) {
                    if (!empty($item['product_id']) && !empty($item['qty'])) {
                        Product::where('id', $item['product_id'])->increment('quantity', (int)$item['qty']);
                    }
                }

                $this->eventRepository->logEvent(
                    $attempt->id,
                    'TERMINAL_REFUND_SUCCESS',
                    'finalized',
                    $newStatus->value,
                    [
                        'refund_uuid' => $refundUuid,
                        'refund_amount' => $refundAmount,
                        'manager_id' => $manager->id,
                        'reason' => $reason,
                    ]
                );

                return [
                    'status' => $newStatus->value,
                    'refund_uuid' => $refundUuid,
                    'refunded_amount' => $refundAmount,
                    'attempt' => $attempt,
                    'provider_result' => $result,
                ];
            }

            if ($result->normalizedStatus->value === 'pending' || $result->normalizedStatus->value === 'unknown') {
                return [
                    'status' => 'pending',
                    'refund_uuid' => $refundUuid,
                    'message' => 'Refund submitted to gateway. Processing pending.',
                    'attempt' => $attempt,
                ];
            }

            throw new Exception("Refund request declined or failed by terminal gateway.");
        });
    }
}
