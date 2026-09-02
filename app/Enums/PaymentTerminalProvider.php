<?php

namespace App\Enums;

enum PaymentTerminalProvider: string
{
    case PAYTM = 'paytm';
    case PHONEPE = 'phonepe';
    case MOCK = 'mock';
}
