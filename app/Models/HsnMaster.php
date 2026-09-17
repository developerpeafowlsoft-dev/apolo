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


    public function getTaxForPurchaseRateAndDate($purchaseRate, $date = null)
    {
        $purchaseRate = floatval($purchaseRate);
        $checkDate = $date ? date('Y-m-d', strtotime($date)) : date('Y-m-d');

        $matchingSub = $this->subHsn->filter(function($sub) use ($purchaseRate, $checkDate) {
            $fromDate = $sub->from_date ? date('Y-m-d', strtotime($sub->from_date)) : null;
            $toDate = $sub->to_date ? date('Y-m-d', strtotime($sub->to_date)) : null;

            if ($fromDate && $checkDate < $fromDate) return false;
            if ($toDate && $checkDate > $toDate) return false;

            $fromRate = floatval($sub->from_purchase_rate ?? 0);
            $toRate = floatval($sub->to_purchase_rate ?? 0);

            if ($toRate == 0) {
                return $purchaseRate >= $fromRate;
            }
            return $purchaseRate >= $fromRate && $purchaseRate <= $toRate;
        })->first();

        if ($matchingSub && $matchingSub->vat_tax_id) {
            $vatTax = $matchingSub->vattax ?? \App\Models\VatTax::find($matchingSub->vat_tax_id);
            return [
                'vat_tax_id' => $matchingSub->vat_tax_id,
                'percentage' => floatval($vatTax?->percentage ?? 0),
            ];
        }

        return [
            'vat_tax_id' => $this->vat_tax_id,
            'percentage' => floatval($this->vattax?->percentage ?? 0),
        ];
    }

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