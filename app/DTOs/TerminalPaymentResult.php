<?php

namespace App\DTOs;

use App\Enums\PosPaymentAttemptStatus;
use InvalidArgumentException;

readonly class TerminalPaymentResult
{
    public PosPaymentAttemptStatus $normalizedStatus;

    public function __construct(
        PosPaymentAttemptStatus|string $status,
        public string $provider,
        public ?string $providerRequestId = null,
        public ?string $providerTransactionId = null,
        public string $paymentMethod = 'card',
        public float $approvedAmount = 0.00,
        public string $currency = 'INR',
        public ?string $terminalId = null,
        public ?string $merchantId = null,
        public ?string $rrn = null,
        public ?string $approvalCode = null,
        public ?string $maskedCard = null,
        public ?string $cardNetwork = null,
        public ?string $cardType = null,
        public ?string $upiReference = null,
        public ?string $failureCode = null,
        public ?string $failureMessage = null,
        public string $processedTimestamp = '',
        public array $safeRawResponse = []
    ) {
        if (is_string($status)) {
            $enumVal = PosPaymentAttemptStatus::tryFrom($status);
            if (!$enumVal) {
                throw new InvalidArgumentException("Invalid payment attempt status: {$status}");
            }
            $this->normalizedStatus = $enumVal;
        } else {
            $this->normalizedStatus = $status;
        }

        if ($approvedAmount < 0) {
            throw new InvalidArgumentException("Approved amount cannot be negative.");
        }
    }

    public function isSuccess(): bool
    {
        return in_array($this->normalizedStatus, [
            PosPaymentAttemptStatus::SUCCESS,
            PosPaymentAttemptStatus::VERIFIED,
            PosPaymentAttemptStatus::FINALIZED,
        ], true);
    }
}
