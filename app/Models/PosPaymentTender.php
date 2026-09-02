<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PosPaymentMethod;

class PosPaymentTender extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payment_method' => PosPaymentMethod::class,
        'amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function paymentAttempt(): BelongsTo
    {
        return $this->belongsTo(PosPaymentAttempt::class, 'pos_payment_attempt_id');
    }
}
