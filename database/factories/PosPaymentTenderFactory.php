<?php

namespace Database\Factories;

use App\Models\PosPaymentTender;
use App\Models\Order;
use App\Models\PosPaymentAttempt;
use App\Enums\PosPaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PosPaymentTenderFactory extends Factory
{
    protected $model = PosPaymentTender::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'pos_payment_attempt_id' => PosPaymentAttempt::factory(),
            'payment_method' => PosPaymentMethod::CARD,
            'amount' => 500.00,
        ];
    }
}
