<?php

namespace App\Exceptions;

use Exception;

class ProviderNotConfiguredException extends Exception
{
    public function __construct(string $provider)
    {
        parent::__construct("Payment terminal provider '{$provider}' is not configured.");
    }
}
