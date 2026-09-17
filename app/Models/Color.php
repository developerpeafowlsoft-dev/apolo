<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the shop from the color.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TranslateUtility::class);
    }

    /**
     * Get the user who created the color.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this color is owned and editable/deletable by a shop.
     */
    public function isOwnedByShop(?int $shopId): bool
    {
        if (!$shopId || (int) $this->shop_id !== (int) $shopId) {
            return false;
        }

        // Colors created by Super Admin (user 1 or root role) or system migrated (no creator) are protected
        if (empty($this->created_by) || (int) $this->created_by === 1) {
            return false;
        }

        $creator = $this->creator;
        if ($creator && $creator->hasRole('root')) {
            return false;
        }

        return true;
    }

    /**
     * Scope a query to only include active colors.
     */
    public function scopeIsActive($query)
    {
        return $query->where('is_active', 1);
    }
}
