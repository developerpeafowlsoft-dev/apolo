<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InwardInvoice;
use App\Models\InwardProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function inwardProduct(): BelongsTo
    {
        return $this->belongsTo(
            InwardProduct::class,
            'inward_product_id'
        );
    }

    public function inwardInvoice(): BelongsTo
    {
        return $this->belongsTo(
            InwardInvoice::class,
            'inward_invoice_id'
        );
    }
}
