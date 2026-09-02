<?php

namespace App\DTOs;

use InvalidArgumentException;

readonly class TerminalVoidRequest
{
    public function __construct(
        public string $attemptUuid,
        public string $transactionId,
        public float $originalAmount,
        public string $provider = 'mock',
        public ?string $terminalId = null,
        public ?string $merchantId = null,
        public ?string $reason = null,
        public array $metadata = []
    ) {
        if ($originalAmount <= 0) {
            throw new InvalidArgumentException("Original amount must be positive.");
        }
    }
}
