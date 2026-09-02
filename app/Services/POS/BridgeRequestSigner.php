<?php

namespace App\Services\POS;

use Illuminate\Support\Str;

class BridgeRequestSigner
{
    /**
     * Generate a short-lived signed bridge session token for the browser client.
     *
     * @param int $userId
     * @param int $shopId
     * @param int $counterId
     * @param string $terminalId
     * @param string $attemptUuid
     * @param string $operation
     * @param string $secretKey
     * @param int $ttlSeconds
     * @return array
     */
    public function createSessionToken(
        int $userId,
        int $shopId,
        int $counterId,
        string $terminalId,
        string $attemptUuid,
        string $operation,
        string $secretKey = 'default_secret',
        int $ttlSeconds = 90
    ): array {
        $timestamp = time();
        $expiresAt = $timestamp + $ttlSeconds;
        $nonce = Str::random(16);

        $payload = [
            'user_id' => $userId,
            'shop_id' => $shopId,
            'counter_id' => $counterId,
            'terminal_id' => $terminalId,
            'attempt_id' => $attemptUuid,
            'operation' => $operation,
            'expires_at' => $expiresAt,
            'nonce' => $nonce,
        ];

        $dataToSign = "{$userId}:{$shopId}:{$counterId}:{$terminalId}:{$attemptUuid}:{$operation}:{$expiresAt}:{$nonce}";
        $signature = hash_hmac('sha256', $dataToSign, $secretKey);

        $payload['signature'] = $signature;

        return [
            'token' => base64_encode(json_encode($payload)),
            'expires_at' => $expiresAt,
            'signature' => $signature,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
        ];
    }
}
