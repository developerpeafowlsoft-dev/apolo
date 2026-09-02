<?php

namespace App\Repositories;

use App\Models\PosPaymentAttempt;
use App\Enums\PosPaymentAttemptStatus;
use App\Exceptions\IllegalStateTransitionException;
use Illuminate\Support\Facades\DB;

class PosPaymentAttemptRepository
{
    /**
     * Allowed state transitions map.
     */
    protected array $allowedTransitions = [
        'created' => ['sent_to_terminal', 'cancelled', 'failed'],
        'sent_to_terminal' => ['awaiting_customer', 'processing', 'failed', 'cancelled'],
        'awaiting_customer' => ['processing', 'success', 'failed', 'cancelled', 'unknown'],
        'processing' => ['success', 'verified', 'failed', 'cancelled', 'unknown', 'amount_mismatch'],
        'success' => ['verified', 'finalized'],
        'verified' => ['finalized'],
        'unknown' => ['success', 'verified', 'failed', 'cancelled'],
        'finalized' => ['voided', 'partially_refunded', 'refunded'],
    ];

    public function findById(string $id, bool $lock = false): ?PosPaymentAttempt
    {
        $query = PosPaymentAttempt::where('id', $id);
        if ($lock) {
            $query->lockForUpdate();
        }
        return $query->first();
    }

    public function findUnresolvedForCart(int $shopId, string $cartName, bool $lock = false): ?PosPaymentAttempt
    {
        $query = PosPaymentAttempt::where('shop_id', $shopId)
            ->where('cart_name', $cartName)
            ->unresolved();

        if ($lock) {
            $query->lockForUpdate();
        }
        return $query->first();
    }

    public function transition(PosPaymentAttempt $attempt, PosPaymentAttemptStatus|string $targetStatus, array $extraData = []): PosPaymentAttempt
    {
        $from = is_string($attempt->status) ? $attempt->status : $attempt->status->value;
        $to = is_string($targetStatus) ? $targetStatus : $targetStatus->value;

        if ($from === $to) {
            return $attempt;
        }

        $allowed = $this->allowedTransitions[$from] ?? [];
        if (!in_array($to, $allowed, true)) {
            throw new IllegalStateTransitionException($from, $to);
        }

        return DB::transaction(function () use ($attempt, $from, $to, $extraData) {
            $lockedAttempt = PosPaymentAttempt::where('id', $attempt->id)->lockForUpdate()->first();

            $updatePayload = array_merge($extraData, [
                'status' => $to,
            ]);

            if ($to === PosPaymentAttemptStatus::FINALIZED->value || $to === 'finalized') {
                $updatePayload['finalized_at'] = now();
            }

            $lockedAttempt->update($updatePayload);

            app(PosTerminalEventRepository::class)->logEvent(
                attemptId: $lockedAttempt->id,
                eventType: 'state_transition',
                statusFrom: $from,
                statusTo: $to,
                payload: $extraData
            );

            return $lockedAttempt;
        });
    }

    public function createAttempt(array $data): PosPaymentAttempt
    {
        return PosPaymentAttempt::create($data);
    }
}
