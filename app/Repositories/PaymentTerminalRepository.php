<?php

namespace App\Repositories;

use App\Models\PaymentTerminal;

class PaymentTerminalRepository
{
    public function findActiveByCounter(int $shopId, int $counterId): ?PaymentTerminal
    {
        return PaymentTerminal::where('shop_id', $shopId)
            ->where('counter_id', $counterId)
            ->where('is_active', true)
            ->first();
    }

    public function findByTerminalId(string $terminalId): ?PaymentTerminal
    {
        return PaymentTerminal::where('terminal_id', $terminalId)
            ->where('is_active', true)
            ->first();
    }
}
