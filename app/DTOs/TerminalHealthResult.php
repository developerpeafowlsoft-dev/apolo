<?php

namespace App\DTOs;

readonly class TerminalHealthResult
{
    public function __construct(
        public bool $isHealthy,
        public string $provider,
        public ?string $terminalId = null,
        public ?int $batteryLevel = null,
        public bool $isOnline = true,
        public int $responseTimeMs = 0,
        public array $details = []
    ) {}
}
