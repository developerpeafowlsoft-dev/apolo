<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HsnMaster extends Model
{
    use HasFactory;

    protected $fillable = ['shop_id','hsn_code','hsn_description','vat_tax_id','from_sales_rate','to_sales_rate','from_purchase_rate','to_purchase_rate','from_date','to_date','is_active'];

    public function scopeBasicFields($query)
    {
        return $query->select([
            'id',
            'shop_id',
            'hsn_code',
            'hsn_description',
            'vat_tax_id',
            'from_sales_rate',
            'to_sales_rate',
            'to_purchase_rate',
            'is_active'
        ]);
    }

    public function vattax()
    {
        return $this->belongsTo(VatTax::class,'vat_tax_id','id');
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

    public function subHsn(): HasMany
    {
        return $this->hasMany(HsnSubMaster::class, 'hsn_master_id', 'id');
    }

    public function getTaxRateAttribute()
    {
        return $this->vattax->percentage ?? 0;
    }


// ✅ Add this method to get tax for a specific price
    public function getTaxForPrice($price)
    {
        // Find sub HSN for price range
        $subHsn = $this->subHsn->filter(function($sub) use ($price) {
            $fromRate = floatval($sub->from_sales_rate ?? 0);
            $toRate = floatval($sub->to_sales_rate ?? 0);

            if ($toRate == 0) {
                return $price >= $fromRate;
            }
            return $price >= $fromRate && $price <= $toRate;
        })->first();

        if ($subHsn && $subHsn->vat_tax_id) {
            $vatTax = \App\Models\VatTax::find($subHsn->vat_tax_id);
            return $vatTax?->percentage ?? 0;
        }

        return $this->vattax?->percentage ?? 0;
    }
}