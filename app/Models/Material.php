<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this material is owned and editable/deletable by a shop.
     */
    public function isOwnedByShop(?int $shopId): bool
    {
        if (!$shopId || (int) $this->shop_id !== (int) $shopId) {
            return false;
        }

        // Materials created by Super Admin (user 1 or root role) or system migrated (no creator) are protected
        if (empty($this->created_by) || (int) $this->created_by === 1) {
            return false;
        }

        $creator = $this->creator;
        if ($creator && $creator->hasRole('root')) {
            return false;
        }

        return true;
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', 1);
    }
}