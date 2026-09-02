<?php

namespace Database\Factories;

use App\Models\PaymentTerminal;
use App\Models\Shop;
use App\Models\CounterMaster;
use App\Enums\PaymentTerminalProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTerminalFactory extends Factory
{
    protected $model = PaymentTerminal::class;

    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'counter_id' => CounterMaster::first() ? CounterMaster::first()->id : null,
            'name' => $this->faker->word . ' Terminal',
            'provider' => $this->faker->randomElement([PaymentTerminalProvider::PAYTM, PaymentTerminalProvider::PHONEPE, PaymentTerminalProvider::MOCK]),
            'terminal_id' => 'TERM-' . $this->faker->unique()->randomNumber(6),
            'merchant_id' => 'MERCH-' . $this->faker->randomNumber(6),
            'config_data' => ['api_key' => 'dummy_key', 'port' => 8089],
            'is_active' => true,
        ];
    }
}
