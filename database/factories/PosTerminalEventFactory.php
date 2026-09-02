<?php

namespace Database\Factories;

use App\Models\PosTerminalEvent;
use App\Models\PosPaymentAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

class PosTerminalEventFactory extends Factory
{
    protected $model = PosTerminalEvent::class;

    public function definition(): array
    {
        return [
            'pos_payment_attempt_id' => PosPaymentAttempt::factory(),
            'event_type' => 'state_change',
            'status_from' => 'created',
            'status_to' => 'sent_to_terminal',
            'payload' => ['log' => 'payload sent to device bridge'],
        ];
    }
}
