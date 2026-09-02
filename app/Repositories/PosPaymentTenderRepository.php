<?php

namespace App\Repositories;

use App\Models\PosPaymentTender;

class PosPaymentTenderRepository
{
    public function createTender(int $orderId, string $attemptId, string $paymentMethod, float $amount): PosPaymentTender
    {
        return PosPaymentTender::create([
            'order_id' => $orderId,
            'pos_payment_attempt_id' => $attemptId,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
        ]);
    }
}
