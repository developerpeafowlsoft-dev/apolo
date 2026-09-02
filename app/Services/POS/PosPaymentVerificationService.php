<?php

namespace App\Services\POS;

use App\Models\PosPaymentAttempt;
use App\Models\Order;
use App\Enums\PosPaymentAttemptStatus;
use App\Repositories\PosPaymentAttemptRepository;
use App\Repositories\PosPaymentTenderRepository;
use App\Repositories\PosTerminalEventRepository;
use App\Repositories\PosPrintJobRepository;
use App\Services\POS\PosOrderFinalizationService;
use App\Services\POS\BridgeResponseVerifier;
use App\Exceptions\PaymentAmountMismatchException;
use App\Exceptions\PaymentStatusUnknownException;
use App\Exceptions\BridgeAuthenticationException;
use Illuminate\Support\Facades\DB;
use Exception;

class PosPaymentVerificationService
{
    protected $attemptRepository;
    protected $tenderRepository;
    protected $eventRepository;
    protected $printJobRepository;
    protected $finalizationService;
    protected $bridgeVerifier;

    public function __construct(
        PosPaymentAttemptRepository $attemptRepository,
        PosPaymentTenderRepository $tenderRepository,
        PosTerminalEventRepository $eventRepository,
        PosPrintJobRepository $printJobRepository,
        PosOrderFinalizationService $finalizationService,
        BridgeResponseVerifier $bridgeVerifier
    ) {
        $this->attemptRepository = $attemptRepository;
        $this->tenderRepository = $tenderRepository;
        $this->eventRepository = $eventRepository;
        $this->printJobRepository = $printJobRepository;
        $this->finalizationService = $finalizationService;
        $this->bridgeVerifier = $bridgeVerifier;
    }

    /**
     * Verify terminal payment result and finalize bill atomically.
     *
     * @param string $attemptId Attempt UUID
     * @param array $resultPayload Payload containing bridge_token, status, approved_amount, transaction_id, etc.
     * @param array $checkoutData Order checkout payload (items, customer, etc.)
     * @param object $shop Current shop
     * @param object $financialYear Current FY
     * @return array Array containing finalized Order model, attempt, and print job info
     * @throws Exception
     */
    public function verifyAndFinalize(string $attemptId, array $resultPayload, array $checkoutData, $shop, $financialYear): array
    {
        return DB::transaction(function () use ($attemptId, $resultPayload, $checkoutData, $shop, $financialYear) {
            $attempt = PosPaymentAttempt::where('id', $attemptId)->lockForUpdate()->first();
            if (!$attempt) {
                throw new Exception("Payment attempt '{$attemptId}' does not exist.");
            }

            if ($attempt->status === PosPaymentAttemptStatus::FINALIZED || $attempt->order_id) {
                $existingOrder = Order::withoutGlobalScopes()->find($attempt->order_id);
                return [
                    'status' => true,
                    'already_finalized' => true,
                    'order' => $existingOrder,
                    'attempt' => $attempt,
                    'invoice_url' => $existingOrder ? ('/shop/pos/' . $existingOrder->id . '/invoice') : null,
                ];
            }

            if ((int)$attempt->shop_id !== (int)$shop->id) {
                throw new Exception("Payment attempt does not belong to the current shop.");
            }

            if ($attempt->status === PosPaymentAttemptStatus::CANCELLED || $attempt->status === PosPaymentAttemptStatus::EXPIRED) {
                throw new Exception("Payment attempt has expired or been cancelled.");
            }

            $reportedStatus = strtolower($resultPayload['status'] ?? 'unknown');
            $approvedAmount = (float)($resultPayload['approved_amount'] ?? 0.00);
            $txId = $resultPayload['transaction_id'] ?? null;
            $currency = strtoupper($resultPayload['currency'] ?? 'INR');

            if ($currency !== 'INR') {
                throw new Exception("Payment currency must be INR.");
            }

            if ($reportedStatus === 'success' && abs($approvedAmount - (float)$attempt->amount) > 0.01) {
                $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::AMOUNT_MISMATCH, [
                    'approved_amount' => $approvedAmount,
                    'transaction_id' => $txId,
                    'response_payload' => $resultPayload,
                ]);
                throw new PaymentAmountMismatchException((float)$attempt->amount, $approvedAmount);
            }

            if ($reportedStatus !== 'success') {
                if ($reportedStatus === 'unknown') {
                    $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::UNKNOWN, [
                        'response_payload' => $resultPayload,
                    ]);
                    throw new PaymentStatusUnknownException($attemptId);
                }

                $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::FAILED, [
                    'response_payload' => $resultPayload,
                ]);
                throw new Exception("Terminal payment failed or declined.");
            }

            if (empty($txId)) {
                throw new Exception("Provider transaction ID is missing from payment result.");
            }

            $duplicateTx = PosPaymentAttempt::where('provider', $attempt->provider)
                ->where('transaction_id', $txId)
                ->where('id', '!=', $attempt->id)
                ->exists();

            if ($duplicateTx) {
                throw new Exception("Provider transaction ID '{$txId}' has already been processed by another attempt.");
            }

            if (!empty($resultPayload['bridge_token'])) {
                $secret = $attempt->terminal->config_data['shared_secret'] ?? $attempt->terminal->shared_secret ?? 'default_secret';
                $this->bridgeVerifier->verifySessionToken($resultPayload['bridge_token'], [
                    'shop_id' => $shop->id,
                    'attempt_id' => $attemptId,
                    'terminal_id' => $attempt->terminal_id,
                ], $secret);
            }

            $attempt = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::VERIFIED, [
                'approved_amount' => $approvedAmount,
                'transaction_id' => $txId,
                'reference_no' => $resultPayload['rrn'] ?? null,
                'masked_pan' => $resultPayload['masked_pan'] ?? null,
                'card_type' => $resultPayload['card_type'] ?? null,
                'response_payload' => $resultPayload['raw_response'] ?? $resultPayload,
            ]);

            $checkoutData['payment_attempt_id'] = $attempt->id;
            $checkoutData['idempotency_key'] = $attempt->id;
            $checkoutData['payment_method'] = $attempt->payment_method->value;
            $checkoutData['payment_status'] = 'Paid';
            $checkoutData['amount'] = $approvedAmount;

            $order = $this->finalizationService->finalize($checkoutData, $shop, $financialYear);

            $this->tenderRepository->createTender(
                orderId: $order->id,
                attemptId: $attempt->id,
                paymentMethod: $attempt->payment_method->value,
                amount: $approvedAmount
            );

            $attempt = $this->attemptRepository->transition($attempt, PosPaymentAttemptStatus::FINALIZED, [
                'order_id' => $order->id,
            ]);

            $printJob = $this->printJobRepository->createPrintJob(
                orderId: $order->id,
                payload: json_encode([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => $approvedAmount,
                    'payment_method' => $attempt->payment_method->value,
                    'terminal_id' => $attempt->terminal_id,
                    'transaction_id' => $txId,
                ])
            );

            return [
                'status' => true,
                'already_finalized' => false,
                'order' => $order,
                'attempt' => $attempt,
                'print_job_id' => $printJob->id,
                'invoice_url' => '/shop/pos/' . $order->id . '/invoice',
            ];
        });
    }
}
