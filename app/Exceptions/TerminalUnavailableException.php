<?php

namespace App\Exceptions;

use Exception;

class TerminalUnavailableException extends Exception
{
    public function __construct(string $message = "The selected payment terminal is currently offline or unreachable.")
    {
        parent::__construct($message);
    }
}
