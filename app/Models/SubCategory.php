<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SubCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function translations(): HasMany
    {
        return $this->hasMany(TranslateUtility::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_subcategories');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_subcategories');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getInwardProductsCountAttribute($value)
    {
        if ($value !== null) {
            return (int) $value;
        }

        return (int) \Illuminate\Support\Facades\DB::table('inward_products')
            ->join('product_subcategories', 'inward_products.product_id', '=', 'product_subcategories.product_id')
            ->where('product_subcategories.sub_category_id', $this->id)
            ->count();
    }

    /**
     * Get the number of active online products associated with this subcategory.
     */
    public function getOnlineProductsCountAttribute($value)
    {
        if ($value !== null) {
            return (int) $value;
        }

        return (int) \Illuminate\Support\Facades\DB::table('products')
            ->join('product_subcategories', 'products.id', '=', 'product_subcategories.product_id')
            ->where('product_subcategories.sub_category_id', $this->id)
            ->where('products.is_online_product', 1)
            ->where('products.is_active', 1)
            ->count();
    }

    /**
     * Scopes a query to only include active records.
     *
     * @param  mixed  $query  The query parameter.
     * @return mixed The return value.
     */
    public function scopeIsActive($query)
    {
        return $query->where('is_active', 1);
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

//    public function thumbnail(): Attribute
//    {
//        $thumbnail = asset('default/default.jpg');
//        $symlinkExists = file_exists(public_path('storage'));
//
//        if ($this->media && $symlinkExists && Storage::exists($this->media->src)) {
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
            $thumbnail = Storage::disk('public')->url($this->media->src);
        }

        return new Attribute(
            get: fn () => $thumbnail
        );
    }

}
