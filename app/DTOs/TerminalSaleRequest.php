<?php

namespace App\DTOs;

use InvalidArgumentException;

readonly class TerminalSaleRequest
{
    public function __construct(
        public string $attemptUuid,
        public string $billReference,
        public string $provider,
        public string $paymentMethod,
        public float $amount,
        public string $currency = 'INR',
        public int $shopId = 1,
        public ?int $counterId = null,
        public ?string $terminalId = null,
        public int $cashierId = 1,
        public string $timestamp = '',
        public array $metadata = []
    ) {
        if (empty($attemptUuid)) {
            throw new InvalidArgumentException("Attempt UUID cannot be empty.");
        }
        if ($amount <= 0) {
            throw new InvalidArgumentException("Sale amount must be positive.");
        }
    }
}
