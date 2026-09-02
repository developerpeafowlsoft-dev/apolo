<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSReturn extends Model
{
    use HasFactory;

    protected $table = 'pos_returns';

    protected $fillable = [
        'shop_id',
        'counter_id',
        'original_order_id',
        'return_no',
        'customer_id',
        'total_amount',
        'tax_amount',
        'payment_method',
        'cashier_id',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function counter()
    {
        return $this->belongsTo(CounterMaster::class);
    }

    public function originalOrder()
    {
        return $this->belongsTo(Order::class, 'original_order_id')->withoutGlobalScopes();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function products()
    {
        return $this->hasMany(POSReturnProduct::class, 'return_id');
    }
}
