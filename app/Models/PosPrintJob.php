<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PosPrintJobStatus;

class PosPrintJob extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => PosPrintJobStatus::class,
        'print_payload' => 'array',
        'is_reprint' => 'boolean',
        'print_count' => 'integer',
        'printed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
