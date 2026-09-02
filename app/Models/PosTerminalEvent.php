<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTerminalEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'encrypted:json',
    ];

    public function paymentAttempt(): BelongsTo
    {
        return $this->belongsTo(PosPaymentAttempt::class, 'pos_payment_attempt_id');
    }
}
