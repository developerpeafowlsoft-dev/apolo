<?php

namespace App\Http\Resources;

use App\Models\InwardProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class ProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->load(['reviews', 'orders', 'colors', 'shop', 'sizes', 'unit', 'brand', 'flashSales', 'categories']);

        $lang = request()->header('accept-language') ?? 'en';

        $favorite = false;
        $user = Auth::guard('api')->user();

        if ($user && $user->customer) {
            $favorite = $user->customer->favorites()->where('product_id', $this->id)->exists();
        }

        $discountPercentage = ((float)($this->online_discount_percent ?? 0) > 0)
            ? (float) $this->online_discount_percent
            : $this->getDiscountPercentage($this->price, $this->discount_price);
        $totalSold = $this->orders->sum('pivot.quantity');

        $flashSale = $this->flashSales?->first();
        $flashSaleProduct = null;
        $quantity = null;

        if ($flashSale) {
            $flashSaleProduct = $flashSale->products()->where('id', $this->id)->first();
            $quantity = $flashSaleProduct->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;

            if ($quantity == 0) {
                $quantity = null;
                $flashSaleProduct = null;
            } else {
                $discountPercentage = $flashSale->pivot->discount;
            }
        }

        $price = $this->price;
        $discountPrice = $flashSaleProduct ? $flashSaleProduct->pivot?->price : $this->discount_price;

        $translation = $this->translations()?->where('lang', $lang)->first();
        $name = $translation?->name ?? $this->name;
        $description = $translation?->description ?? $this->description;
        $shortDescription = $translation?->short_description ?? $this->short_description;

        $brandTranslation = $this->brand?->translations()?->where('lang', $lang)->first();
        $brandName = $brandTranslation?->name ?? $this->brand?->name;
        $category = $this->categories?->first();
        $categoryName = $category?->name;
        $shop = $this->shop;

        $lastOnline = $this->last_online >= now() ? true : false;

        // ✅ Get inward product data
        $inwardProductData = $this->getInwardProductData();

        return [
            'id' => $this->id,
            'name' => $name,
            'code' => $this->code,
            'short_description' => $shortDescription,
            'price' => (float) number_format((float) ($price ?? 0), 2, '.', ''),
            'discount_price' => (float) number_format((float) ($discountPrice ?? 0), 2, '.', ''),
            'discount_percentage' => (float) number_format((float) ($discountPercentage ?? 0), 2, '.', ''),
            'rating' => (float) $this->averageRating ?? 0.0,
            'total_reviews' => (string) Number::abbreviate($this->reviews?->count(), maxPrecision: 2),
            'total_sold' => (string) number_format($totalSold, 0, '.', ','),
            'quantity' => (int) ($quantity ?? $this->quantity),
            'is_favorite' => (bool) $favorite,
            'thumbnails' => $this->thumbnails(),
            'sizes' => SizeResource::collection($this->sizes),
            'colors' => ColorResource::collection($this->colors),
            'brand' => $brandName,
            'category' => $categoryName,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'weight' => $this->weight,
            'min_order_quantity' => $this->min_order_quantity,
            'unit' => $this->unit ? UnitResource::make($this->unit) : null,
            'description' => $description,
            'shop' => [
                'id' => $shop?->id,
                'name' => $shop?->name,
                'logo' => $shop?->logo,
                'rating' => (float) round($shop?->averageRating, 1),
                'estimated_delivery_time' => (string) ($shop?->estimated_delivery_time ?? '2-4 days'),
                'delivery_charge' => (float) getDeliveryCharge(1),
                'last_online' => $lastOnline
            ],
            'flash_sale' => $flashSaleProduct ? FlashSaleResource::make($flashSale) : null,
            'meta_title' => $this->meta_title ?? $name,
            'meta_description' => $this->meta_description ?? $name,
            'meta_keywords' => $this->meta_keywords,
            'inward_product_data' => $inwardProductData, // ✅ Important: Add this
        ];
    }

    /**
     * ✅ Get inward product data with colors, sizes, and prices
     */
    private function getInwardProductData(): array
    {
        $inwardProductData = [];

        // Get invoice IDs from product
        $invoiceIds = $this->inward_invoice_ids;

        if ($this->design_master_id) {
            $inwardProducts = InwardProduct::where('design_master_id', $this->design_master_id)
                ->with(['designMaster'])
                ->get();
        } else {
            // Handle different formats
            if (is_string($invoiceIds)) {
                $invoiceIds = json_decode($invoiceIds, true) ?? [];
            }

            if (!is_array($invoiceIds) || empty($invoiceIds)) {
                return [];
            }

            $inwardProducts = InwardProduct::whereIn('inward_invoice_id', $invoiceIds)
                ->with(['designMaster'])
                ->get();
        }

        foreach ($inwardProducts as $inwardProduct) {
            // Skip variant if disabled from online & mobile app sales
            if (isset($inwardProduct->is_online_product) && (int) $inwardProduct->is_online_product === 0) {
                continue;
            }

            // ✅ Get colors from inward_product_colors
            $colorData = DB::table('inward_product_colors')
                ->where('inward_product_id', $inwardProduct->id)
                ->join('colors', 'inward_product_colors.color_id', '=', 'colors.id')
                ->select('colors.id', 'colors.name', 'colors.color_code')
                ->get();

            // ✅ Get sizes from inward_product_sizes
            $sizeData = DB::table('inward_product_sizes')
                ->where('inward_product_id', $inwardProduct->id)
                ->join('sizes', 'inward_product_sizes.size_id', '=', 'sizes.id')
                ->select('sizes.id', 'sizes.name')
                ->get();

            // ✅ If no colors, add default
            if ($colorData->isEmpty()) {
                $colorData = collect([(object) ['id' => null, 'name' => 'N/A', 'color_code' => '#ccc']]);
            }

            // ✅ If no sizes, add default
            if ($sizeData->isEmpty()) {
                $sizeData = collect([(object) ['id' => null, 'name' => 'N/A']]);
            }

            // ✅ Combine all color-size variations
            foreach ($colorData as $color) {
                foreach ($sizeData as $size) {
                    $vDisc = ((float)($inwardProduct->online_discount_percent ?? 0) > 0)
                        ? (float) $inwardProduct->online_discount_percent
                        : (((float)($this->online_discount_percent ?? 0) > 0)
                            ? (float) $this->online_discount_percent
                            : 0);
                    $vPrice = ($vDisc > 0)
                        ? round($inwardProduct->mrp - ($inwardProduct->mrp * $vDisc / 100), 2)
                        : (float) $inwardProduct->mrp;

                    $inwardProductData[] = [
                        'color_id' => $color->id,
                        'color_name' => $color->name ?? 'N/A',
                        'color_code' => $color->color_code ?? '#ccc',
                        'size_id' => $size->id,
                        'size_name' => $size->name ?? 'N/A',
                        'qty' => $inwardProduct->quantity,
                        'purc_rate' => (float) number_format($inwardProduct->buy_price, 2, '.', ''),
                        'mrp' => (float) number_format($inwardProduct->mrp, 2, '.', ''),
                        'price' => (float) number_format($vPrice, 2, '.', ''),
                        'discount_percent' => $vDisc,
                    ];
                }
            }
        }

        return $inwardProductData;
    }

    /**
     * Calculate discount percentage
     */
    private function getDiscountPercentage($price, $discountPrice): float
    {
        if ($price > 0 && $discountPrice > 0 && $discountPrice < $price) {
            return round((($price - $discountPrice) / $price) * 100, 2);
        }
        return 0.0;
    }
}
