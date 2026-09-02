<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReconciliation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'reconciliation_date' => 'date',
        'total_attempted_amount' => 'decimal:2',
        'total_settled_amount' => 'decimal:2',
        'discrepancy_amount' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
