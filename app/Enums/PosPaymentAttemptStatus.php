<?php

namespace App\Enums;

enum PosPaymentAttemptStatus: string
{
    case CREATED = 'created';
    case SENT_TO_TERMINAL = 'sent_to_terminal';
    case AWAITING_CUSTOMER = 'awaiting_customer';
    case PROCESSING = 'processing';
    case SUCCESS = 'success';
    case VERIFIED = 'verified';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case UNKNOWN = 'unknown';
    case AMOUNT_MISMATCH = 'amount_mismatch';
    case FINALIZED = 'finalized';
    case VOIDED = 'voided';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case REFUNDED = 'refunded';
}
