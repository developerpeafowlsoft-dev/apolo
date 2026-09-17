<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translations(): HasMany
    {
        return $this->hasMany(TranslateUtility::class);
    }

    /**
     * Retrieves the products associated with this instance.
     *
     * @return BelongsToMany The products associated with this instance.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'category_id');
    }

    public function subCategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'category_subcategories')->where('is_active', 1);
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_categories');
    }

    /**
     * Scopes a query to only include active records.
     *
     * @param  mixed  $query  The query parameter.
     * @return mixed The return value.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scopes a query to only include categories enabled for hero section.
     */
    public function scopeInHero($query)
    {
        return $query->where('show_in_hero', 1);
    }

    /**
     * Get the user who created this category.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the number of inward product items associated with this category.
     */
    public function getInwardProductsCountAttribute($value)
    {
        if ($value !== null) {
            return (int) $value;
        }

        return (int) \Illuminate\Support\Facades\DB::table('inward_products')
            ->join('product_categories', 'inward_products.product_id', '=', 'product_categories.product_id')
            ->where('product_categories.category_id', $this->id)
            ->count();
    }

    /**
     * Get the number of active online products associated with this category.
     */
    public function getOnlineProductsCountAttribute($value)
    {
        if ($value !== null) {
            return (int) $value;
        }

        return (int) \Illuminate\Support\Facades\DB::table('products')
            ->join('product_categories', 'products.id', '=', 'product_categories.product_id')
            ->where('product_categories.category_id', $this->id)
            ->where('products.is_online_product', 1)
            ->where('products.is_active', 1)
            ->count();
    }

    /**
     * Retrieves the associated media for this model.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Generates a thumbnail attribute for the media.
     *
     * @return Attribute The generated thumbnail attribute.
     */
//    public function thumbnail(): Attribute
//    {
//        $thumbnail = asset('default/default.jpg');
//        if ($this->media && Storage::exists($this->media->src)) {
//            $thumbnail = Storage::url($this->media->src);
//        }
//
//        return Attribute::make(
//            get: fn () => $thumbnail
//        );
//    }

    public function thumbnail(): Attribute
    {
        $thumbnail = asset('default/default.jpg');

        if (
            $this->media &&
            Storage::disk('public')->exists($this->media->src) &&
            file_exists(public_path('storage'))
        ) {
            $thumbnail = Storage::url($this->media->src);
        }

        return new Attribute(
            get: fn () => $thumbnail
        );
    }
}
