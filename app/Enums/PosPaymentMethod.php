<?php

namespace App\Enums;

enum PosPaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case UPI = 'upi';
    case SPLIT = 'split';
}
