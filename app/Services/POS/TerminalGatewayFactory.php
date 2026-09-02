<?php

namespace App\Services\POS;

use App\Contracts\Payments\PaymentTerminalGateway;
use App\Enums\PaymentTerminalProvider;
use App\Exceptions\ProviderNotConfiguredException;

class TerminalGatewayFactory
{
    /**
     * Resolve a terminal gateway instance based on PaymentTerminalProvider enum or string.
     *
     * @param PaymentTerminalProvider|string $provider
     * @param array $config
     * @return PaymentTerminalGateway
     * @throws ProviderNotConfiguredException
     */
    public function make(PaymentTerminalProvider|string $provider, array $config = []): PaymentTerminalGateway
    {
        $providerKey = is_string($provider)
            ? PaymentTerminalProvider::tryFrom(strtolower($provider))
            : $provider;

        if (!$providerKey) {
            throw new ProviderNotConfiguredException(is_string($provider) ? $provider : 'unknown');
        }

        return match ($providerKey) {
            PaymentTerminalProvider::MOCK => new MockTerminalGateway(),
            PaymentTerminalProvider::PAYTM => $this->resolvePaytmGateway($config),
            PaymentTerminalProvider::PHONEPE => $this->resolvePhonePeGateway($config),
        };
    }

    protected function resolvePaytmGateway(array $config): PaymentTerminalGateway
    {
        if (class_exists(\App\Services\POS\PaytmTerminalGateway::class)) {
            return new \App\Services\POS\PaytmTerminalGateway($config);
        }
        throw new ProviderNotConfiguredException(PaymentTerminalProvider::PAYTM->value);
    }

    protected function resolvePhonePeGateway(array $config): PaymentTerminalGateway
    {
        if (class_exists(\App\Services\POS\PhonePeTerminalGateway::class)) {
            return new \App\Services\POS\PhonePeTerminalGateway($config);
        }
        throw new ProviderNotConfiguredException(PaymentTerminalProvider::PHONEPE->value);
    }
}
