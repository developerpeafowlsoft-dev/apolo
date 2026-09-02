<?php

namespace App\Exceptions;

use Exception;

class BridgeConnectionException extends Exception
{
    public function __construct(string $message = "Unable to connect to local POS device bridge service on 127.0.0.1:8089.")
    {
        parent::__construct($message);
    }
}
