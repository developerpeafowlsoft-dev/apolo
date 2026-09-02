<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSHold extends Model
{
    use HasFactory;

    protected $table = 'pos_holds';

    protected $fillable = [
        'shop_id',
        'counter_id',
        'cashier_id',
        'bill_label',
        'items_json',
        'customer_phone',
        'customer_name',
        'customer_email',
        'subtotal',
    ];

    protected $casts = [
        'items_json' => 'array',
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
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
