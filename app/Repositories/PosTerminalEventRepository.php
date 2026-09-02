<?php

namespace App\Repositories;

use App\Models\PosTerminalEvent;

class PosTerminalEventRepository
{
    public function logEvent(string $attemptId, string $eventType, string $statusFrom, string $statusTo, array $payload = []): PosTerminalEvent
    {
        $sanitizedPayload = $this->redactSensitiveFields($payload);
        $sanitizedPayload['_audit'] = [
            'user_id' => auth()->id(),
            'ip' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'POS Client',
            'timestamp' => now()->toIso8601String(),
        ];

        return PosTerminalEvent::create([
            'pos_payment_attempt_id' => $attemptId,
            'event_type' => $eventType,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'payload' => $sanitizedPayload,
        ]);
    }

    protected function redactSensitiveFields(array $data): array
    {
        $sensitiveKeys = ['pan', 'card_number', 'cvv', 'cvc', 'pin', 'track', 'upi_pin', 'secret', 'password'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redactSensitiveFields($value);
            } elseif (is_string($key)) {
                $lowerKey = strtolower($key);
                foreach ($sensitiveKeys as $sensitive) {
                    if (str_contains($lowerKey, $sensitive)) {
                        $data[$key] = '[REDACTED]';
                        break;
                    }
                }
            }
        }

        return $data;
    }
}
