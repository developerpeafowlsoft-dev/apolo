<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBarcode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function inwardInvoice(): BelongsTo
    {
        return $this->belongsTo(InwardInvoice::class,'inward_invoice_id','id');
    }

    public function inwardProduct(): BelongsTo
    {
        return $this->belongsTo(InwardProduct::class,'inward_product_id','id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function orderProduct(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(OrderProduct::class, 'barcode_number', 'barcode_number');
    }
}