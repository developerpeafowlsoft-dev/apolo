<?php

namespace Database\Factories;

use App\Models\PaymentReconciliation;
use App\Models\Shop;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentReconciliationFactory extends Factory
{
    protected $model = PaymentReconciliation::class;

    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'branch_id' => Branch::first() ? Branch::first()->id : null,
            'reconciliation_date' => $this->faker->date(),
            'provider' => 'paytm',
            'total_attempts_count' => 10,
            'total_attempted_amount' => 5000.00,
            'total_settled_amount' => 5000.00,
            'discrepancy_amount' => 0.00,
            'status' => 'balanced',
            'resolution_note' => null,
        ];
    }
}
