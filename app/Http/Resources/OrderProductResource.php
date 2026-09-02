<?php
//
//namespace App\Http\Resources;
//
//use Illuminate\Http\Request;
//use Illuminate\Http\Resources\Json\JsonResource;
//
//class OrderProductResource extends JsonResource
//{
//    /**
//     * Transform the resource into an array.
//     *
//     * @return array<string, mixed>
//     */
//    public function toArray(Request $request): array
//    {
//        $this->load('brand', 'reviews');
//
//        $review = $this->reviews()->where('customer_id', auth()->user()->customer?->id)->where('product_id', $this->id)->where('order_id', $request->order_id)->first();
//
//        $price = $this->pivot->price > 0 ? $this->pivot->price : ($this->discount_price > 0 ? $this->discount_price : $this->price);
//
//        return [
//            'id' => $this->id,
//            'name' => $this->name,
//            'brand' => $this->brand?->name ?? null,
//            'thumbnail' => $this->thumbnail,
////            'price' => (float) $price,
//            // Final Selling Price
//            'price' => (float) $this->pivot->price,
//
//            // Original MRP
//            'mrp' => (float) $this->pivot->mrp,
//            'discount_price' => (float) $this->discount_price > 0 ? $price : 0,
//            'order_qty' => (int) $this->pivot->quantity,
//            'color' => $this->pivot->color ?? null,
//            'size' => $this->pivot->size ?? null,
//            'rating' => $review ? (float) $review->rating : null,
//            'unit' => $this->pivot->unit ?? null,
//        ];
//    }
//}


namespace App\Http\Resources;

use App\Models\InwardInvoice;
use App\Models\InwardProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing([
            'brand',
            'reviews',
        ]);

        $review = $this->reviews()
            ->where('customer_id', auth()->user()->customer?->id)
            ->where('product_id', $this->id)
            ->where('order_id', $request->order_id)
            ->first();

        $inwardProduct = null;
        $inwardInvoice = null;

        if ($this->pivot?->inward_product_id) {
            $inwardProduct = InwardProduct::find(
                $this->pivot->inward_product_id
            );
        }

        if ($this->pivot?->inward_invoice_id) {
            $inwardInvoice = InwardInvoice::find(
                $this->pivot->inward_invoice_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Historical order pricing
        |--------------------------------------------------------------------------
        | Order details me hamesha order_products pivot ka price use hoga.
        | Current products table ka price use nahi karna.
        */

        $sellingPrice = (float)($this->pivot?->price ?? 0);

        if ($sellingPrice <= 0) {
            $sellingPrice = (float)(
            $inwardProduct?->discount_price > 0
                ? $inwardProduct->discount_price
                : (
                $inwardProduct?->price
                ?? ($this->discount_price > 0
                ? $this->discount_price
                : $this->price)
                ?? 0
            )
            );
        }

        $mrp = (float)($this->pivot?->mrp ?? 0);

        if ($mrp <= 0) {
            $mrp = (float)(
                $inwardProduct?->mrp
                ?? $this->price
                ?? $sellingPrice
            );
        }

        if ($mrp < $sellingPrice) {
            $mrp = $sellingPrice;
        }

        $quantity = (int)($this->pivot?->quantity ?? 1);

        $discountAmount = (float)(
            $this->pivot?->discount_amount
            ?? max(0, ($mrp - $sellingPrice) * $quantity)
        );

        $discountPercentage = (float)(
            $this->pivot?->discount ?? 0
        );

        if (
            $discountPercentage <= 0 &&
            $mrp > 0 &&
            $sellingPrice < $mrp
        ) {
            $discountPercentage = (($mrp - $sellingPrice) / $mrp) * 100;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand?->name,
            'thumbnail' => $this->thumbnail,

            // Historical order values
            'price' => round($sellingPrice, 2),
            'mrp' => round($mrp, 2),

            // Checkout component compatibility
            'discount_price' => $mrp > $sellingPrice
                ? round($sellingPrice, 2)
                : 0,

            'discount_percentage' => round($discountPercentage, 2),
            'discount_amount' => round($discountAmount, 2),

            'order_qty' => $quantity,
            'quantity' => $quantity,

            'color' => $this->pivot?->color,
            'color_name' => $this->pivot?->color,

            'size' => $this->pivot?->size,
            'size_name' => $this->pivot?->size,

            'unit' => $this->pivot?->unit,

            'tax_percentage' => (float)(
                $this->pivot?->tax_percentage ?? 0
            ),

            'tax_amount' => (float)(
                $this->pivot?->tax_amount ?? 0
            ),

            'vat_tax_name' => $this->pivot?->vat_tax_name,

            'inward_invoice_id' => $this->pivot?->inward_invoice_id,
            'inward_product_id' => $this->pivot?->inward_product_id,

            'inward_invoice' => $inwardInvoice
                ? [
                    'id' => $inwardInvoice->id,
                    'voucher_no' => $inwardInvoice->inward_voucher_no,
                ]
                : null,

            'rating' => $review
                ? (float)$review->rating
                : null,
        ];
    }
}