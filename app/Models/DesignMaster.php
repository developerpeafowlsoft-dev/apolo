<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesignMaster extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function products(): BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function accountMasters(): BelongsTo
    {
        return $this->belongsTo(AccountMaster::class,'account_master_id','id');
    }

    public function scopeActive($query)
    {
        return $this->where('is_active',1);
    }

    public function inwardProductDesign(): HasMany
    {
        return $this->hasMany(InwardProduct::class,'design_master_id','id');
    }

}