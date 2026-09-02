<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translations(): HasMany
    {
        return $this->hasMany(TranslateUtility::class);
    }

    /**
     * Get the shop that owns the color.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Scope a query to only include active colors.
     */
    public function scopeIsActive($query)
    {
        return $query->where('is_active', 1);
    }
}
