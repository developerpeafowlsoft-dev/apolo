<?php

namespace App\Exceptions;

use Exception;

class BridgeAuthenticationException extends Exception
{
    public function __construct(string $message = "Bridge authentication failed. Invalid token, signature mismatch, or expired timestamp.")
    {
        parent::__construct($message);
    }
}
