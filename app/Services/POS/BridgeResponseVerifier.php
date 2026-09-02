<?php

namespace App\Services\POS;

use App\Exceptions\BridgeAuthenticationException;

class BridgeResponseVerifier
{
    /**
     * Verify short-lived session token context and HMAC signature.
     *
     * @param string $encodedToken
     * @param array $expectedContext
     * @param string $secretKey
     * @return array Decoded token payload
     * @throws BridgeAuthenticationException
     */
    public function verifySessionToken(string $encodedToken, array $expectedContext, string $secretKey = 'default_secret'): array
    {
        $jsonStr = base64_decode($encodedToken);
        $payload = json_decode($jsonStr, true);

        if (!$payload || !is_array($payload)) {
            throw new BridgeAuthenticationException("Invalid or malformed bridge session token.");
        }

        if (empty($payload['expires_at']) || time() > $payload['expires_at']) {
            throw new BridgeAuthenticationException("Bridge session token has expired (>2m TTL limit).");
        }

        $userId = $payload['user_id'] ?? 0;
        $shopId = $payload['shop_id'] ?? 0;
        $counterId = $payload['counter_id'] ?? 0;
        $terminalId = $payload['terminal_id'] ?? '';
        $attemptId = $payload['attempt_id'] ?? '';
        $operation = $payload['operation'] ?? '';
        $expiresAt = $payload['expires_at'] ?? 0;
        $nonce = $payload['nonce'] ?? '';
        $sig = $payload['signature'] ?? '';

        $dataToSign = "{$userId}:{$shopId}:{$counterId}:{$terminalId}:{$attemptId}:{$operation}:{$expiresAt}:{$nonce}";
        $expectedSig = hash_hmac('sha256', $dataToSign, $secretKey);

        if (!hash_equals($expectedSig, $sig)) {
            throw new BridgeAuthenticationException("Bridge session token signature mismatch.");
        }

        if (!empty($expectedContext['user_id']) && (int)$expectedContext['user_id'] !== (int)$userId) {
            throw new BridgeAuthenticationException("Session token user_id mismatch.");
        }
        if (!empty($expectedContext['shop_id']) && (int)$expectedContext['shop_id'] !== (int)$shopId) {
            throw new BridgeAuthenticationException("Session token shop_id mismatch.");
        }
        if (!empty($expectedContext['counter_id']) && (int)$expectedContext['counter_id'] !== (int)$counterId) {
            throw new BridgeAuthenticationException("Session token counter_id mismatch.");
        }
        if (!empty($expectedContext['terminal_id']) && $expectedContext['terminal_id'] !== $terminalId) {
            throw new BridgeAuthenticationException("Session token terminal_id mismatch.");
        }
        if (!empty($expectedContext['attempt_id']) && $expectedContext['attempt_id'] !== $attemptId) {
            throw new BridgeAuthenticationException("Session token attempt_id mismatch.");
        }

        return $payload;
    }
}
