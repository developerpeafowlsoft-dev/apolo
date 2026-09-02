<?php

namespace App\Http\Resources;

use App\Models\InwardProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->load([
            'reviews',
            'orders',
            'sizes',
            'colors',
            'unit',
            'brand',
            'shop',
            'flashSales',
        ]);

        $lang = request()->header('accept-language') ?? 'en';

        $favorite = false;
        $user = Auth::guard('api')->user();

        if ($user && $user->customer) {
            $favorite = $user->customer
                ->favorites()
                ->where('product_id', $this->id)
                ->exists();
        }

        /*
        |--------------------------------------------------------------------------
        | Default product pricing
        |--------------------------------------------------------------------------
        */

        $discountPercentage = $this->getDiscountPercentage(
            $this->price,
            $this->discount_price
        );

        $totalSold = $this->orders->sum('pivot.quantity');

        /*
        |--------------------------------------------------------------------------
        | Flash sale pricing
        |--------------------------------------------------------------------------
        */

        $flashSale = $this->flashSales?->first();
        $flashSaleProduct = null;
        $quantity = null;

        if ($flashSale) {
            $flashSaleProduct = $flashSale
                ?->products()
                ->where('id', $this->id)
                ->first();

            if ($flashSaleProduct) {
                $quantity =
                    $flashSaleProduct->pivot->quantity
                    - $flashSaleProduct->pivot->sale_quantity;

                if ($quantity <= 0) {
                    $quantity = null;
                    $flashSaleProduct = null;
                } else {
                    $discountPercentage = (float) (
                        $flashSaleProduct->pivot->discount
                        ?? $flashSale->pivot?->discount
                        ?? 0
                    );
                }
            }
        }

        $price = (float) $this->price;

        $discountPrice = $flashSaleProduct
            ? (float) $flashSaleProduct->pivot->price
            : (float) $this->discount_price;

        /*
        |--------------------------------------------------------------------------
        | First inward variant
        |--------------------------------------------------------------------------
        | Product card par first inward variant ka price dikhayenge.
        */

        $invoiceIds = $this->inward_invoice_ids;

        if (is_string($invoiceIds)) {
            $invoiceIds = json_decode($invoiceIds, true) ?? [];
        }

        if (! is_array($invoiceIds)) {
            $invoiceIds = [];
        }

        $firstInwardProduct = null;

        if (! empty($invoiceIds)) {
            $firstInwardProduct = InwardProduct::query()
                ->whereIn('inward_invoice_id', $invoiceIds)
                ->where('design_master_id', $this->design_master_id)
                ->where('is_active', 1)
                ->orderByRaw(
                    'FIELD(inward_invoice_id, ' .
                    implode(',', array_map('intval', $invoiceIds)) .
                    ')'
                )
                ->orderBy('id')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | First variant price calculation
        |--------------------------------------------------------------------------
        | inward_products.mrp = Original MRP
        | inward_products.discount_price = Discount percentage
        |
        | Example:
        | MRP = 699
        | Discount = 10
        | Selling price = 629.10
        */

        $firstVariantOriginalPrice = null;
        $firstVariantSellingPrice = null;
        $firstVariantDiscount = 0;

        if ($firstInwardProduct) {
            // inward_products.price = original price before discount
            $firstVariantOriginalPrice = (float) (
                $firstInwardProduct->price ?? 0
            );

            // inward_products.mrp = final selling price after discount
            $firstVariantSellingPrice = (float) (
                $firstInwardProduct->mrp ?? 0
            );

            // inward_products.discount_price = discount percentage
            $firstVariantDiscount = (float) (
                $firstInwardProduct->discount_price ?? 0
            );

            // Fallback if final selling price is not stored
            if ($firstVariantSellingPrice <= 0) {
                if (
                    $firstVariantDiscount > 0 &&
                    $firstVariantDiscount < 100 &&
                    $firstVariantOriginalPrice > 0
                ) {
                    $firstVariantSellingPrice = round(
                        $firstVariantOriginalPrice
                        - ($firstVariantOriginalPrice * $firstVariantDiscount / 100),
                        2
                    );
                } else {
                    $firstVariantSellingPrice = $firstVariantOriginalPrice;
                }
            }

            if ($firstVariantOriginalPrice <= 0) {
                $firstVariantOriginalPrice = $firstVariantSellingPrice;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Translation
        |--------------------------------------------------------------------------
        */

        $translation = $this->translations()
            ?->where('lang', $lang)
            ->first();

        $name = $translation?->name ?? $this->name;

        $brandTranslation = $this->brand
            ?->translations()
            ?->where('lang', $lang)
            ->first();

        $brandName = $brandTranslation?->name ?? $this->brand?->name;

        return [
            'id' => $this->id,
            'name' => $name,
            'thumbnail' => $this->thumbnail,

            /*
            |--------------------------------------------------------------------------
            | Normal product price
            |--------------------------------------------------------------------------
            */

            'price' => (float) number_format(
                $price,
                2,
                '.',
                ''
            ),

            'discount_price' => (float) number_format(
                $discountPrice,
                2,
                '.',
                ''
            ),

            'discount_percentage' => (float) number_format(
                $discountPercentage,
                2,
                '.',
                ''
            ),

            /*
            |--------------------------------------------------------------------------
            | First inward variant price
            |--------------------------------------------------------------------------
            */

            'first_variant_price' => $firstVariantSellingPrice !== null
                ? (float) number_format(
                    $firstVariantSellingPrice,
                    2,
                    '.',
                    ''
                )
                : null,

            'first_variant_mrp' => $firstVariantOriginalPrice !== null
                ? (float) number_format(
                    $firstVariantOriginalPrice,
                    2,
                    '.',
                    ''
                )
                : null,

            'first_variant_discount' => (float) number_format(
                $firstVariantDiscount,
                2,
                '.',
                ''
            ),

            'first_variant_inward_invoice_id' =>
                $firstInwardProduct?->inward_invoice_id,

            'first_variant_inward_product_id' =>
                $firstInwardProduct?->id,

            /*
            |--------------------------------------------------------------------------
            | Other product information
            |--------------------------------------------------------------------------
            */

            'rating' => (float) ($this->averageRating ?? 0.0),

            'total_reviews' => (string) Number::abbreviate(
                $this->reviews?->count(),
                maxPrecision: 2
            ),

            'total_sold' => (string) number_format(
                $totalSold,
                0,
                '.',
                ','
            ),

            'quantity' => (int) (
            $flashSaleProduct
                ? $quantity
                : $this->quantity
            ),

            'is_favorite' => (bool) $favorite,

            'sizes' => SizeResource::collection($this->sizes),

            'colors' => ColorResource::collection($this->colors),

            'unit' => $this->unit
                ? UnitResource::make($this->unit)
                : null,

            'brand' => $brandName,

            'shop' => ProductShopResource::make($this->shop),
        ];
    }
}


//namespace App\Http\Resources;
//
//use Illuminate\Http\Request;
//use Illuminate\Http\Resources\Json\JsonResource;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Number;
//
//class ProductResource extends JsonResource
//{
//    /**
//     * Transform the resource into an array.
//     *
//     * @return array<string, mixed>
//     */
//    public function toArray(Request $request): array
//    {
//        $this->load(['reviews', 'orders', 'sizes', 'colors', 'unit', 'brand', 'shop', 'flashSales']);
//
//        $lang = request()->header('accept-language') ?? 'en';
//
//        $favorite = false;
//        $user = Auth::guard('api')->user();
//
//        if ($user && $user->customer) {
//            $favorite = $user->customer->favorites()->where('product_id', $this->id)->exists();
//        }
//
//        $discountPercentage = $this->getDiscountPercentage($this->price, $this->discount_price);
//        $totalSold = $this->orders->sum('pivot.quantity');
//
//        $flashSale = $this->flashSales?->first();
//        $flashSaleProduct = null;
//        $quantity = null;
//
//        if ($flashSale) {
//            $flashSaleProduct = $flashSale?->products()->where('id', $this->id)->first();
//
//            $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;
//
//            if ($quantity == 0) {
//                $quantity = null;
//                $flashSaleProduct = null;
//            } else {
//                $discountPercentage = $flashSale?->pivot->discount;
//            }
//        }
//
//        $price = $this->price;
//        $discountPrice = $flashSaleProduct ? $flashSaleProduct->pivot->price : $this->discount_price;
//
//        $translation = $this->translations()?->where('lang', $lang)->first();
//        $name = $translation?->name ?? $this->name;
//
//        $brandTranslation = $this->brand?->translations()?->where('lang', $lang)->first();
//        $brandName = $brandTranslation?->name ?? $this->brand?->name;
//
//        return [
//            'id' => $this->id,
//            'name' => $name,
//            'thumbnail' => $this->thumbnail,
//            'price' => (float)number_format($price, 2, '.', ''),
//            'discount_price' => (float)number_format($discountPrice, 2, '.', ''),
//            'discount_percentage' => (float)number_format($discountPercentage, 2, '.', ''),
//            'rating' => (float)$this->averageRating ?? 0.0,
//            'total_reviews' => (string)Number::abbreviate($this->reviews?->count(), maxPrecision: 2),
//            'total_sold' => (string)number_format($totalSold, 0, '.', ','),
//            'quantity' => (int)($flashSaleProduct ? $quantity : $this->quantity),
//            'is_favorite' => (bool)$favorite,
//            'sizes' => SizeResource::collection($this->sizes),
//            'colors' => ColorResource::collection($this->colors),
//            'unit' => $this->unit ? UnitResource::make($this->unit) : null,
//            'brand' => $brandName,
//            'shop' => ProductShopResource::make($this->shop),
//        ];
//    }
//}
