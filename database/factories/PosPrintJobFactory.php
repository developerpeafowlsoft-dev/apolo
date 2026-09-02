<?php

namespace Database\Factories;

use App\Models\PosPrintJob;
use App\Models\Order;
use App\Enums\PosPrintJobStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class PosPrintJobFactory extends Factory
{
    protected $model = PosPrintJob::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => PosPrintJobStatus::PENDING,
            'print_payload' => 'GST Receipt details here...',
            'attempts' => 0,
            'error_message' => null,
        ];
    }
}
