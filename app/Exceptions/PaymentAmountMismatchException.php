<?php

namespace App\Exceptions;

use Exception;

class PaymentAmountMismatchException extends Exception
{
    public function __construct(float $requested, float $approved)
    {
        parent::__construct("Payment approved amount ({$approved}) does not match requested amount ({$requested}).");
    }
}
