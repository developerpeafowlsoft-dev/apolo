<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSReturnProduct extends Model
{
    use HasFactory;

    protected $table = 'pos_return_products';

    protected $fillable = [
        'return_id',
        'product_id',
        'barcode_number',
        'qty',
        'rate',
        'tax_amt',
        'reason',
    ];

    public function returnHeader()
    {
        return $this->belongsTo(POSReturn::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }
}
