<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InwardInvoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'inward_date' => 'date:Y-m-d',
        'inward_challan_date' => 'date:Y-m-d',
        'inward_acc_lr_date' => 'date:Y-m-d',
    ];

    public function inwardProduct(): HasMany
    {
        return $this->hasMany(InwardProduct::class,'inward_invoice_id','id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(CounterMaster::class,'counter_master_id','id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class,'shop_id','id');
    }

    public function partyCode(): BelongsTo
    {
        return $this->belongsTo(AccountMaster::class,'inward_party_code','id');
    }

    public function vattax()
    {
        return $this->belongsTo(VatTax::class,'vat_tax_id','id');
    }

//    public function purchaser(): BelongsTo
//    {
//        return $this->belongsTo(AccountMaster::class,'inward_acc_purchaser','id');
//    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class,'inward_acc_purchaser','id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class,'season_id','id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class,'agent_id','id');
    }

    public function transport(): BelongsTo
    {
        return $this->belongsTo(Transport::class,'transport_id','id');
    }

    public function deliveryBy(): BelongsTo
    {
        return $this->belongsTo(DeliveryBy::class,'delivery_by_id','id');
    }

    public function productPurchase()
    {
        return $this->hasOne(ProductPurchase::class, 'inward_invoice_id', 'id');
    }

    public function scopeIsPurchase($query)
    {
        return $query->where('is_purchase',1);
    }

}