<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Terminal Configuration
    |--------------------------------------------------------------------------
    |
    | Define active terminals, credentials, URLs, and secrets for the
    | integrated EDC payment terminal systems.
    |
    */

    'paytm' => [
        'base_url' => env('PAYTM_EDC_BASE_URL', 'https://localhost:8000/v1'),
        'merchant_id' => env('PAYTM_EDC_MERCHANT_ID', ''),
        'timeout' => env('PAYTM_EDC_TIMEOUT_SEC', 120),
    ],

    'phonepe' => [
        // Placeholder configs for PhonePe, protocol details will be implemented upon SDK availability
        'base_url' => env('PHONEPE_EDC_BASE_URL', 'https://localhost:8000/v1'),
        'merchant_id' => env('PHONEPE_EDC_MERCHANT_ID', ''),
        'timeout' => env('PHONEPE_EDC_TIMEOUT_SEC', 120),
    ],

    'mock' => [
        'base_url' => env('MOCK_EDC_BASE_URL', 'https://localhost:8000/v1'),
        'timeout' => 30,
    ],

    'bridge' => [
        'port' => env('EDC_BRIDGE_PORT', 8089),
        'allowed_origins' => explode(',', env('EDC_BRIDGE_ALLOWED_ORIGINS', 'https://apolo,http://localhost')),
        'signature_expiry_seconds' => env('EDC_BRIDGE_SIGNATURE_EXPIRY_SEC', 300),
    ],
];
