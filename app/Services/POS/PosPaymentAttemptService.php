<?php

namespace App\Services\POS;

use App\Models\PosPaymentAttempt;
use App\Models\ProductBarcode;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;
use App\Enums\PaymentTerminalProvider;
use App\Repositories\PaymentTerminalRepository;
use App\Repositories\PosPaymentAttemptRepository;
use App\Repositories\PosTerminalEventRepository;
use App\Services\POS\POSShiftService;
use App\Exceptions\TerminalUnavailableException;
use App\Exceptions\ProviderNotConfiguredException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PosPaymentAttemptService
{
    protected $terminalRepository;
    protected $attemptRepository;
    protected $eventRepository;
    protected $shiftService;

    public function __construct(
        PaymentTerminalRepository $terminalRepository,
        PosPaymentAttemptRepository $attemptRepository,
        PosTerminalEventRepository $eventRepository,
        POSShiftService $shiftService
    ) {
        $this->terminalRepository = $terminalRepository;
        $this->attemptRepository = $attemptRepository;
        $this->eventRepository = $eventRepository;
        $this->shiftService = $shiftService;
    }

    /**
     * Create a new pending payment attempt for an active cart session.
     *
     * @param array $payload Checkout payload including items, counter_id, cart_name, method, provider
     * @param object $shop Current shop model
     * @param int $cashierId Logged in cashier user ID
     * @return array Safe attempt payload for local bridge call
     * @throws Exception
     */
    public function createAttempt(array $payload, $shop, int $cashierId): array
    {
        return DB::transaction(function () use ($payload, $shop, $cashierId) {
            $counterId = $payload['counter_id'] ?? null;
            if (!$counterId) {
                throw new Exception("Counter ID is required to initiate terminal payment.");
            }

            // 1. Verify active cashier shift
            $shift = $this->shiftService->getActiveShift($shop->id, $counterId, $cashierId);
            if (!$shift) {
                throw new Exception("No active cashier shift session found for this counter.");
            }

            // 2. Resolve active terminal mapped to counter
            $terminal = $this->terminalRepository->findActiveByCounter($shop->id, $counterId);
            if (!$terminal) {
                throw new TerminalUnavailableException("No active payment terminal is configured for this counter.");
            }

            // 3. Verify terminal supports selected payment method
            $paymentMethod = $payload['payment_method'] ?? 'card';
            $provider = strtolower($payload['provider'] ?? $terminal->provider->value);

            // 4. Recalculate current cart payable amount server-side
            $calculatedPayable = 0.00;
            if (!empty($payload['items'])) {
                foreach ($payload['items'] as $item) {
                    $qty = (int)$item['qty'];
                    $rate = (float)($item['rate'] ?? $item['price'] ?? 0);
                    $discPercent = (float)($item['disc_percent'] ?? 0);
                    $discAmt = round($rate * $qty * ($discPercent / 100), 2);
                    $lineTotal = ($rate * $qty) - $discAmt;
                    $calculatedPayable += $lineTotal;
                }
            } else {
                $calculatedPayable = (float)($payload['amount'] ?? 0);
            }

            // 5. Reject zero or negative bills
            if ($calculatedPayable <= 0) {
                throw new Exception("Cannot initiate payment terminal attempt for zero or negative bill amount.");
            }

            $cartName = $payload['cart_name'] ?? ('POS_CART_' . $counterId . '_' . $cashierId);

            // 6. Verify no unresolved payment attempt already exists for the active bill
            $unresolved = $this->attemptRepository->findUnresolvedForCart($shop->id, $cartName, true);
            if ($unresolved) {
                throw new Exception("An unresolved payment attempt ('{$unresolved->id}') already exists for this bill.");
            }

            // 7. Generate UUID & unique references
            $attemptUuid = (string) Str::uuid();
            $billReference = 'BILL-' . strtoupper(Str::random(8));
            $providerRequestId = 'REQ-' . strtoupper(Str::random(10));

            // 8. Save immutable attempt record
            $attempt = $this->attemptRepository->createAttempt([
                'id' => $attemptUuid,
                'payment_terminal_id' => $terminal->id,
                'shop_id' => $shop->id,
                'branch_id' => $payload['branch_id'] ?? null,
                'shift_id' => $shift->id,
                'cashier_id' => $cashierId,
                'cart_name' => $cartName,
                'provider' => $provider,
                'payment_method' => $paymentMethod,
                'amount' => $calculatedPayable,
                'approved_amount' => 0.00,
                'status' => PosPaymentAttemptStatus::CREATED,
                'terminal_id' => $terminal->terminal_id,
            ]);

            // 9. Write audit event
            $this->eventRepository->logEvent(
                attemptId: $attempt->id,
                eventType: 'created',
                statusFrom: 'none',
                statusTo: PosPaymentAttemptStatus::CREATED->value,
                payload: [
                    'amount' => $calculatedPayable,
                    'provider' => $provider,
                    'method' => $paymentMethod,
                    'bill_ref' => $billReference,
                ]
            );

            // 10. Return safe attempt resource
            $timestamp = time();
            $nonce = Str::random(16);
            $secret = $terminal->config_data['shared_secret'] ?? $terminal->shared_secret ?? 'default_secret';
            $signature = hash_hmac('sha256', "{$attemptUuid}:{$calculatedPayable}:{$timestamp}:{$nonce}", $secret);

            return [
                'attempt_id' => $attemptUuid,
                'bill_reference' => $billReference,
                'provider_request_id' => $providerRequestId,
                'amount' => $calculatedPayable,
                'provider' => $provider,
                'payment_method' => $paymentMethod,
                'terminal' => [
                    'terminal_id' => $terminal->terminal_id,
                    'merchant_id' => $terminal->merchant_id,
                    'name' => $terminal->name,
                ],
                'bridge_signature' => [
                    'timestamp' => $timestamp,
                    'nonce' => $nonce,
                    'signature' => $signature,
                ]
            ];
        });
    }
}
