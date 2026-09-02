<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsnSubMaster extends Model
{
    use HasFactory;

    protected $fillable = ['hsn_master_id','vat_tax_id','from_sales_rate','to_sales_rate','from_purchase_rate','to_purchase_rate','from_date','to_date'];

    public function vattax()
    {
        return $this->belongsTo(VatTax::class, 'vat_tax_id', 'id');
    }

    public function getTaxRateAttribute()
    {
        return $this->vattax->percentage ?? 0;
    }
}