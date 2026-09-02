<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSShift extends Model
{
    use HasFactory;

    protected $table = 'pos_shifts';

    protected $fillable = [
        'shop_id',
        'counter_id',
        'user_id',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'total_sales_cash',
        'total_sales_card',
        'total_returns_cash',
        'total_returns_card',
        'status',
        'opened_at',
        'closed_at',
        'difference',
        'note',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function counter()
    {
        return $this->belongsTo(CounterMaster::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
