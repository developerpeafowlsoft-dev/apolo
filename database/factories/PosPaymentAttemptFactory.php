<?php

namespace Database\Factories;

use App\Models\PosPaymentAttempt;
use App\Models\PaymentTerminal;
use App\Models\Shop;
use App\Models\Branch;
use App\Models\POSShift;
use App\Models\User;
use App\Models\Order;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PosPaymentAttemptFactory extends Factory
{
    protected $model = PosPaymentAttempt::class;

    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'payment_terminal_id' => PaymentTerminal::factory(),
            'shop_id' => Shop::factory(),
            'branch_id' => Branch::first() ? Branch::first()->id : null,
            'shift_id' => POSShift::first() ? POSShift::first()->id : null,
            'cashier_id' => User::factory(),
            'order_id' => null,
            'cart_name' => 'MainCart',
            'provider' => 'mock',
            'payment_method' => $this->faker->randomElement([PosPaymentMethod::CARD, PosPaymentMethod::UPI]),
            'amount' => 500.00,
            'approved_amount' => 0.00,
            'status' => PosPaymentAttemptStatus::CREATED,
            'transaction_id' => 'TX-' . $this->faker->unique()->randomNumber(8),
            'reference_no' => 'RRN-' . $this->faker->randomNumber(8),
            'terminal_id' => 'TERM-SERIAL-123',
            'response_payload' => null,
        ];
    }
}
