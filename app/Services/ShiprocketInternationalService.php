<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ShiprocketInternationalService
{
    private string $baseUrl;

    public function __construct(
        private readonly ShiprocketService $shiprocketService
    ) {
        $this->baseUrl = rtrim(
            config('services.shiprocket.base_url'),
            '/'
        );
    }

    /**
     * International rates fetch karne ka method.
     *
     * Important:
     * Exact endpoint aur request fields aapke Shiprocket X API access ke
     * according configure karne honge.
     */
    public function rates(array $data): array
    {
        $endpoint = config(
            'services.shiprocket.international_rate_endpoint'
        );

        if (! $endpoint) {
            throw new RuntimeException(
                'Shiprocket international rate endpoint is not configured.'
            );
        }

        $response = Http::acceptJson()
            ->withToken($this->shiprocketService->getToken())
            ->timeout(45)
            ->post(
                $this->baseUrl.'/'.$endpoint,
                $data
            );

        if ($response->unauthorized()) {
            Cache::forget('shiprocket_api_token');

            $response = Http::acceptJson()
                ->withToken($this->shiprocketService->getToken())
                ->timeout(45)
                ->post(
                    $this->baseUrl.'/'.$endpoint,
                    $data
                );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('message')
                ?? 'Unable to fetch international shipping rates.'
            );
        }

        return $response->json();
    }
}