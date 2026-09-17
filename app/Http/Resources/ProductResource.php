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

        $discountPercentage = ((float)($this->online_discount_percent ?? 0) > 0)
            ? (float) $this->online_discount_percent
            : $this->getDiscountPercentage($this->price, $this->discount_price);

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
        | inward_products.mrp = MRP (retail customer price)
        | Inward discount (inward_products.discount_price) is vendor purchase discount,
        | NOT retail customer discount. Customer selling price defaults to MRP.
        */

        $firstVariantOriginalPrice = null;
        $firstVariantSellingPrice = null;
        $firstVariantDiscount = 0;

        if ($firstInwardProduct) {
            // MRP is the customer retail price
            $firstVariantOriginalPrice = (float) (
                $firstInwardProduct->mrp ?? $firstInwardProduct->price ?? 0
            );

            // Default selling price is MRP (no vendor discount given to retail customers)
            $firstVariantSellingPrice = $firstVariantOriginalPrice;
            $firstVariantDiscount = 0;

            // Only apply retail discount if product has a genuine promotional discount
            if ($discountPercentage > 0) {
                $firstVariantDiscount = (float) $discountPercentage;
                $firstVariantSellingPrice = round(
                    $firstVariantOriginalPrice - ($firstVariantOriginalPrice * $firstVariantDiscount / 100),
                    2
                );
            }

            if ($firstVariantOriginalPrice <= 0) {
                $firstVariantOriginalPrice = (float) ($firstInwardProduct->price ?? $this->price ?? 0);
                $firstVariantSellingPrice = $firstVariantOriginalPrice;
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
