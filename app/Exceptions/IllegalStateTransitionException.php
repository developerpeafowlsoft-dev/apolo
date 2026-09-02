<?php

namespace App\Exceptions;

use Exception;
use App\Enums\PosPaymentAttemptStatus;

class IllegalStateTransitionException extends Exception
{
    public function __construct(PosPaymentAttemptStatus|string $from, PosPaymentAttemptStatus|string $to)
    {
        $fromVal = is_string($from) ? $from : $from->value;
        $toVal = is_string($to) ? $to : $to->value;

        parent::__construct("Illegal payment attempt state transition from '{$fromVal}' to '{$toVal}'.");
    }
}
