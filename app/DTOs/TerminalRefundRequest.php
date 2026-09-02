<?php

namespace App\DTOs;

use InvalidArgumentException;

readonly class TerminalRefundRequest
{
    public function __construct(
        public string $attemptUuid,
        public string $transactionId,
        public float $refundAmount,
        public float $originalAmount,
        public string $provider = 'mock',
        public ?string $terminalId = null,
        public ?string $merchantId = null,
        public ?string $reason = null,
        public array $metadata = []
    ) {
        if ($refundAmount <= 0) {
            throw new InvalidArgumentException("Refund amount must be positive.");
        }
        if ($refundAmount > $originalAmount) {
            throw new InvalidArgumentException("Refund amount cannot exceed original amount.");
        }
    }
}
