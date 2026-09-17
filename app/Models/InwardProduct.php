<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InwardProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'inward_product_colors')
            ->withPivot('price','inward_product_id');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'inward_product_sizes')
            ->withPivot('price','inward_product_id');
    }

    public function products(): BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function designMaster()
    {
        return $this->belongsTo(DesignMaster::class,'design_master_id','id');
    }

    public function hsnMaster()
    {
        return $this->belongsTo(HsnMaster::class,'hsn_master_id','id');
    }

    public function vatTax()
    {
        return $this->belongsTo(VatTax::class,'vat_tax_id','id');
    }

    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class, 'inward_product_id','id');
    }

    public function productBarcode()
    {
        return $this->hasMany(ProductBarcode::class, 'inward_product_id','id');
    }


}