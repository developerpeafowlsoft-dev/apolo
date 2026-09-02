<?php

namespace App\Repositories;

use App\Models\PosPrintJob;
use App\Enums\PosPrintJobStatus;

class PosPrintJobRepository
{
    public function createPrintJob(int $orderId, string $payload): PosPrintJob
    {
        return PosPrintJob::create([
            'order_id' => $orderId,
            'status' => PosPrintJobStatus::PENDING,
            'print_payload' => $payload,
            'attempts' => 0,
        ]);
    }
}
