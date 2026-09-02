<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\PosPaymentAttemptStatus;
use App\Enums\PosPaymentMethod;

class PosPaymentAttempt extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'status' => PosPaymentAttemptStatus::class,
        'payment_method' => PosPaymentMethod::class,
        'amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'response_payload' => 'encrypted:json',
        'finalized_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
            if ($model->amount < 0) {
                throw new \InvalidArgumentException("Requested amount cannot be negative.");
            }
            if ($model->approved_amount < 0) {
                throw new \InvalidArgumentException("Approved amount cannot be negative.");
            }
        });

        static::updating(function ($model) {
            if ($model->amount < 0) {
                throw new \InvalidArgumentException("Requested amount cannot be negative.");
            }
            if ($model->approved_amount < 0) {
                throw new \InvalidArgumentException("Approved amount cannot be negative.");
            }
        });
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            PosPaymentAttemptStatus::CREATED,
            PosPaymentAttemptStatus::SENT_TO_TERMINAL,
            PosPaymentAttemptStatus::AWAITING_CUSTOMER,
            PosPaymentAttemptStatus::PROCESSING,
        ]);
    }

    public function scopeUnknown($query)
    {
        return $query->where('status', PosPaymentAttemptStatus::UNKNOWN);
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', [
            PosPaymentAttemptStatus::SUCCESS,
            PosPaymentAttemptStatus::VERIFIED,
            PosPaymentAttemptStatus::FINALIZED,
        ]);
    }

    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', [
            PosPaymentAttemptStatus::CREATED,
            PosPaymentAttemptStatus::SENT_TO_TERMINAL,
            PosPaymentAttemptStatus::AWAITING_CUSTOMER,
            PosPaymentAttemptStatus::PROCESSING,
            PosPaymentAttemptStatus::UNKNOWN,
        ]);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(PaymentTerminal::class, 'payment_terminal_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(POSShift::class, 'shift_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function terminalEvents(): HasMany
    {
        return $this->hasMany(PosTerminalEvent::class, 'pos_payment_attempt_id');
    }

    public function tenders(): HasMany
    {
        return $this->hasMany(PosPaymentTender::class, 'pos_payment_attempt_id');
    }
}
