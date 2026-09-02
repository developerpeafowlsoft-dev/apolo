<?php

namespace App\DTOs;

readonly class TerminalStatusRequest
{
    public function __construct(
        public string $attemptUuid,
        public ?string $transactionId = null,
        public string $provider = 'mock',
        public ?string $terminalId = null,
        public ?string $merchantId = null,
        public array $metadata = []
    ) {}
}
