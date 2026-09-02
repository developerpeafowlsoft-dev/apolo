<?php

namespace App\Services\POS;

use Illuminate\Support\Facades\Http;
use App\Exceptions\BridgeConnectionException;
use Exception;

class LocalBridgeClient
{
    protected string $baseUrl;
    protected string $deviceId;
    protected string $secretKey;

    public function __construct(string $baseUrl = 'http://127.0.0.1:8089', string $deviceId = 'POS-BRIDGE-CLIENT-01', string $secretKey = 'default_secret')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->deviceId = $deviceId;
        $this->secretKey = $secretKey;
    }

    public function checkHealth(): array
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/v1/health");
            if ($response->successful()) {
                return $response->json();
            }
            throw new BridgeConnectionException("Local bridge healthcheck returned status {$response->status()}");
        } catch (Exception $e) {
            throw new BridgeConnectionException("Failed to reach local bridge: " . $e->getMessage());
        }
    }

    public function sendSignedRequest(string $endpoint, array $payload = [], string $method = 'POST'): array
    {
        $timestamp = (string) time();
        $nonce = 'srv-nonce-' . \Illuminate\Support\Str::random(16);
        $bodyStr = json_encode($payload);

        $dataToSign = "{$this->deviceId}:{$timestamp}:{$nonce}:{$bodyStr}";
        $signature = hash_hmac('sha256', $dataToSign, $this->secretKey);

        try {
            $headers = [
                'X-Bridge-Device-ID' => $this->deviceId,
                'X-Bridge-Timestamp' => $timestamp,
                'X-Bridge-Nonce' => $nonce,
                'X-Bridge-Signature' => $signature,
                'Content-Type' => 'application/json',
            ];

            $url = "{$this->baseUrl}/v1/" . ltrim($endpoint, '/');

            $response = match (strtoupper($method)) {
                'GET' => Http::timeout(5)->withHeaders($headers)->get($url),
                'POST' => Http::timeout(10)->withHeaders($headers)->withBody($bodyStr, 'application/json')->post($url),
                default => throw new Exception("Unsupported method {$method}"),
            };

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'failed',
                'failure_code' => 'BRIDGE_HTTP_' . $response->status(),
                'failure_message' => $response->body(),
            ];
        } catch (Exception $e) {
            throw new BridgeConnectionException("Local bridge communication failed: " . $e->getMessage());
        }
    }
}
