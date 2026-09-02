<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\PaymentTerminalProvider;

class PaymentTerminal extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $hidden = ['config_data'];

    protected $casts = [
        'provider' => PaymentTerminalProvider::class,
        'config_data' => 'encrypted:json',
        'is_active' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(CounterMaster::class, 'counter_id');
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PosPaymentAttempt::class, 'payment_terminal_id');
    }
}
