<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HoldBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hold_bills';

    protected $fillable = [
        'shop_id',
        'counter_id',
        'user_id',
        'customer_id',
        'hold_no',
        'subtotal',
        'discount',
        'tax_amount',
        'payable_amount',
        'payment_method',
        'salesman_id',
        'remarks',
        'tax_details',
        'customer_name',
        'customer_phone',
        'customer_email',
        'status',
    ];

    protected $casts = [
        'tax_details' => 'array',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class);
    }

    public function items()
    {
        return $this->hasMany(HoldBillItem::class, 'hold_bill_id');
    }
}
