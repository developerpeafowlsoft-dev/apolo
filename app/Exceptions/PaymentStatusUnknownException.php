<?php

namespace App\Exceptions;

use Exception;

class PaymentStatusUnknownException extends Exception
{
    public function __construct(string $attemptUuid)
    {
        parent::__construct("Payment status for attempt '{$attemptUuid}' is currently UNKNOWN. Do not retry payment.");
    }
}
