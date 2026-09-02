<?php

namespace App\Enums;

enum PosPrintJobStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PRINTED = 'printed';
    case FAILED = 'failed';
}
