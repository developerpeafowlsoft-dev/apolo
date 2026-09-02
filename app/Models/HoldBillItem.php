<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoldBillItem extends Model
{
    use HasFactory;

    protected $table = 'hold_bill_items';

    protected $fillable = [
        'hold_bill_id',
        'product_id',
        'barcode',
        'qty',
        'rate',
        'disc_percent',
        'disc_amt',
        'tax_percent',
        'tax_amt',
        'tax_name',
        'color',
        'size',
        'salesman_id',
    ];

    public function holdBill()
    {
        return $this->belongsTo(HoldBill::class, 'hold_bill_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class);
    }
}
