<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Requests\PosCartRequest;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\OrderVatTax;
use App\Models\PosCart;
use App\Models\PosCartProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class PosCartRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return PosCart::class;
    }

    public static function getLatestCart(Request $request)
    {
        $shop = generaleSetting('shop');

        $name = $request->name ?? null;

        return self::query()->where('shop_id', $shop->id)->when($name, function ($query) use ($name) {
            return $query->where('name', $name);
        })->whereIpAddress($request->ip())->latest('id')->first();
    }

    public static function storeByRequest(PosCartRequest $request, Product $product)
    {
        $shop = generaleSetting('shop');

        $posCart = self::query()->where('shop_id', $shop->id)->where('name', $request->name)->first();

        if ($posCart) {
            self::attachProduct($posCart, $product->id, $request);

            $calculateAmount = self::calculateTotal($posCart);
            $posCart->update([
                'subtotal' => $calculateAmount['subtotal'],
                'total' => $calculateAmount['total'],
                'discount' => $calculateAmount['discount'],
            ]);

            return $posCart;
        }

        $posCart = self::create([
            'name' => $request->name ?? str()->random(12),
            'shop_id' => $shop->id,
            'ip_address' => $request->ip(),
            'user_id' => $request->user_id,
        ]);

        self::attachProduct($posCart, $product->id, $request);

        $calculateAmount = self::calculateTotal($posCart);
        $posCart->update([
            'subtotal' => $calculateAmount['subtotal'],
            'total' => $calculateAmount['total'],
            'discount' => (float) $calculateAmount['discount'],
        ]);

        return $posCart;
    }

    public static function updateByRequest(PosCartRequest $request, PosCartProduct $posCartProduct)
    {
        $posCartProduct->update([
            'quantity' => $request->quantity,
            'color' => $request->color,
            'size' => $request->size,
            'unit' => $request->unit,
        ]);

        $calculateAmount = self::calculateTotal($posCartProduct->posCart);

        $posCartProduct->posCart->update([
            'subtotal' => $calculateAmount['subtotal'],
            'total' => $calculateAmount['total'],
            'discount' => $calculateAmount['discount'],
        ]);

        return $posCartProduct->posCart;
    }

    private static function attachProduct(PosCart $postCart, $productID, $request)
    {
        // check if product exist
        $existCartProduct = PosCartProduct::where('pos_cart_id', $postCart->id)->where('product_id', $productID)->where('color', $request->color)->where('size', $request->size)->first();

        if ($existCartProduct) {
            $existCartProduct->update([
                'quantity' => $request->quantity,
            ]);

            return $existCartProduct;
        }

        // attach new product
        return $postCart->products()->attach($productID, [
            'quantity' => $request->quantity,
            'color' => $request->color,
            'size' => $request->size,
            'unit' => $request->unit ?? null,
        ]);
    }

    private static function calculateTotal(PosCart $posCart)
    {
        $subtotal = 0;
        $total = 0;
        $discount = 0;

        foreach ($posCart->products as $product) {
            $productPrice = $product->discount_price > 0 ? $product->discount_price : $product->price;

            $mainProduct = Product::find($product->id);

            $size = $mainProduct->sizes()->where('id', $product->pivot->size)->first();
            $color = $mainProduct->colors()->where('id', $product->pivot->color)->first();

            $colorPrice = $color?->pivot->price ?? 0;
            $sizePrice = $size?->pivot->price ?? 0;
            $extraPrice = $colorPrice + $sizePrice;

            $subtotal += ($product->pivot->quantity * $productPrice) + $extraPrice;
        }

        $total = $subtotal - ($posCart->discount ?? 0);

        if ($posCart->coupon) {
            $couponDiscount = CouponRepository::getCouponDiscountAmount($posCart->coupon, $subtotal)['discount_amount'];

            if ($couponDiscount > 0) {
                $discount = $couponDiscount;
                $total = $total - $discount;
            }
        }

        return [
            'subtotal' => $subtotal,
            'total' => $total,
            'discount' => $discount,
        ];
    }

    public static function applyCoupon($request, Coupon $coupon, PosCart $postCart)
    {
        if ($coupon) {
            $getDiscount = CouponRepository::getCouponDiscountAmount($coupon, $postCart->subtotal);

            if ($getDiscount['discount_amount'] > 0) {
                $totalAmount = $postCart->subtotal - $getDiscount['discount_amount'];

                $postCart->update([
                    'discount' => $getDiscount['discount_amount'],
                    'total' => $totalAmount,
                    'coupon_id' => $coupon->id,
                ]);
            }
        }

        return $postCart;
    }

    public static function removeCoupon(PosCart $postCart)
    {
        $postCart->update([
            'coupon_id' => null,
            'discount' => 0,
        ]);

        $calculateAmount = self::calculateTotal($postCart);

        $postCart->update([
            'subtotal' => $calculateAmount['subtotal'],
            'total' => $calculateAmount['total'],
            'discount' => $calculateAmount['discount'],
        ]);

        return $postCart;
    }

    public static function destroyProduct($request, PosCart $postCart)
    {
        $postCartProduct = PosCartProduct::find($request->pos_cart_id);
        if ($postCartProduct) {
            $postCartProduct->delete();
        }

        $calculateAmount = self::calculateTotal($postCart);
        $postCart->update([
            'subtotal' => $calculateAmount['subtotal'],
            'total' => $calculateAmount['total'],
            'discount' => $calculateAmount['discount'],
        ]);

        return $postCart;
    }

    public static function storeOrder(PosCart $posCart, $request)
    {
        $shop = generaleSetting('shop');

        if ($request->order_type == 'draft') {
            $customer = null;
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
            }
            $posCart->update([
                'is_draft' => true,
                'user_id' => $customer?->user?->id ?? null,
            ]);
            self::createNewCart();

            return true;
        }

        $paymentMethod = $request->payment_method == 'cash' ? PaymentMethod::CASH->value : PaymentMethod::ONLINE->value;

        $vatTaxes = VatTaxRepository::getActiveVatTaxes();
        $allVatTaxes = [];
        $totalTaxAmount = 0;

        $subtotal = $posCart->subtotal;

        foreach ($vatTaxes ?? [] as $vatTax) {
            if ($vatTax?->name && $vatTax?->percentage > 0 && $subtotal > 0) {
                $taxAmount = round($subtotal * ($vatTax->percentage / 100), 2);

                $allVatTaxes[] = (object) [
                    'name' => $vatTax->name,
                    'percentage' => $vatTax->percentage,
                    'amount' => $taxAmount,
                ];

                $totalTaxAmount += $taxAmount;
            }
        }
        $total = $posCart->total ?? 0;

        $lastOrderId = self::query()->max('id');
        $order = OrderRepository::create([
            'shop_id' => $shop->id,
            'pos_order' => true,
            'customer_id' => $request->customer_id ?? null,
            'order_code' => str_pad($lastOrderId + 1, 6, '0', STR_PAD_LEFT),
            'prefix' => $shop->prefix ?? 'RC',
            'coupon_id' => $posCart->coupon_id,
            'delivery_charge' => 0,
            'total_amount' => $posCart->subtotal,
            'coupon_discount' => $posCart->discount,
            'tax_amount' => $totalTaxAmount,
            'payable_amount' => $total + $totalTaxAmount,
            'payment_method' => $paymentMethod,
            'order_status' => OrderStatus::DELIVERED->value,
            'instruction' => $request->note,
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        foreach ($posCart->products as $product) {
            $quantity = $product->quantity - $product->pivot->quantity;
            $product->update([
                'quantity' => ($quantity > 0) ? $quantity : 0,
            ]);

            $size = $product->sizes()?->where('id', $product->pivot->size)->first();
            $color = $product->colors()?->where('id', $product->pivot->color)->first();

            $order->products()->attach($product->id, [
                'quantity' => $product->pivot->quantity,
                'color' => $color?->name,
                'size' => $size?->name,
                'unit' => $product->pivot->unit,
            ]);
        }

        // store order vat taxes
        foreach ($allVatTaxes ?? [] as $vatTax) {
            if (! $vatTax) {
                continue;
            }
            OrderVatTax::create([
                'order_id' => $order->id,
                'name' => $vatTax->name,
                'percentage' => $vatTax->percentage,
                'amount' => $vatTax->amount,
            ]);
        }

        // ✅ ERP Accounting Integration for POS Sale
        try {
            $date = now();
            $financialYear = \App\Models\FinancialYear::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->where('is_active', 1)
                ->first();

            // Retrieve account IDs with fallbacks
            $salesAccountId = \App\Models\Account::whereIn('code', ['SALES_POS', 'SALES'])->value('id') ?? \App\Models\Account::where('name', 'like', '%Sales%')->value('id') ?? 9;
            $cashAccountId  = \App\Models\Account::whereIn('code', ['CASH_DRAWER', 'CASH', 'PETTY_CASH'])->value('id') ?? \App\Models\Account::where('name', 'like', '%Cash%')->value('id') ?? 16;
            $bankAccountId  = \App\Models\Account::whereIn('code', ['BANK_HDFC', 'BANK'])->value('id') ?? \App\Models\Account::where('name', 'like', '%Bank%')->value('id') ?? 18;
            $roundAccountId = \App\Models\Account::whereIn('code', ['EXP_ROF', 'ROUND', 'ROF'])->value('id') ?? \App\Models\Account::where('name', 'like', '%Round%')->value('id') ?? 31;
            
            $cgstOutputId   = \App\Models\Account::whereIn('code', ['CGST_OUT', 'GST_OUT_C'])->value('id') ?? \App\Models\Account::where('name', 'like', '%CGST Output%')->value('id') ?? 1;
            $sgstOutputId   = \App\Models\Account::whereIn('code', ['SGST_OUT', 'GST_OUT_S'])->value('id') ?? \App\Models\Account::where('name', 'like', '%SGST Output%')->value('id') ?? 2;
            $igstOutputId   = \App\Models\Account::whereIn('code', ['IGST_OUT', 'GST_OUT_I'])->value('id') ?? \App\Models\Account::where('name', 'like', '%IGST Output%')->value('id') ?? 3;

            $entries = [];

            // 1. Debit Cash/Bank for the full payable amount
            $debitAccountId = ($request->payment_method === 'cash') ? $cashAccountId : $bankAccountId;
            $entries[] = [
                'account_id' => $debitAccountId,
                'type' => 'Dr',
                'amount' => $order->payable_amount,
                'description' => 'POS Cashier Sale ' . $order->order_code
            ];

            // 2. Credit Sales for the net taxable amount
            $netSales = $order->total_amount - $order->coupon_discount;
            if ($netSales > 0) {
                $entries[] = [
                    'account_id' => $salesAccountId,
                    'type' => 'Cr',
                    'amount' => $netSales,
                    'description' => 'Sales taxable net'
                ];
            }

            // 3. Credit Output CGST & SGST (Split sales tax 50/50 for counter sale)
            if ($order->tax_amount > 0) {
                $taxSplit = round($order->tax_amount / 2, 2);
                
                if ($taxSplit > 0) {
                    $entries[] = [
                        'account_id' => $cgstOutputId,
                        'type' => 'Cr',
                        'amount' => $taxSplit,
                        'description' => 'Output CGST'
                    ];

                    $entries[] = [
                        'account_id' => $sgstOutputId,
                        'type' => 'Cr',
                        'amount' => $taxSplit,
                        'description' => 'Output SGST'
                    ];
                }
            }

            // Verify balanced sum; calculate any cent rounding error dynamically
            $drSum = 0;
            $crSum = 0;
            foreach ($entries as $ent) {
                if ($ent['type'] === 'Dr') {
                    $drSum += $ent['amount'];
                } else {
                    $crSum += $ent['amount'];
                }
            }
            
            $roundOff = round($drSum - $crSum, 2);
            if (abs($roundOff) >= 0.01) {
                $entries[] = [
                    'account_id' => $roundAccountId,
                    'type' => $roundOff > 0 ? 'Cr' : 'Dr',
                    'amount' => abs($roundOff),
                    'description' => 'Round off'
                ];
            }

            // Post voucher via service
            $voucherService = app(\App\Services\Accounting\VoucherService::class);
            $voucher = $voucherService->create([
                'voucher_type' => 'Sales',
                'date' => $order->created_at ?? now(),
                'narration' => 'POS Cashier checkout sale #' . $order->order_code,
                'shop_id' => $order->shop_id,
                'financial_year_id' => $financialYear ? $financialYear->id : null,
                'seq_prefix' => 'POS-' . $order->shop_id . '-',
                'entries' => $entries
            ]);

            // Save voucher link in order
            $order->update([
                'voucher_id' => $voucher->id
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("POS Accounting Voucher creation failed: " . $e->getMessage());
            throw $e;
        }

        // ✅ Mark physical barcodes as sold
        try {
            foreach ($posCart->products as $product) {
                $soldQty = $product->pivot->quantity;
                
                $barcodes = \App\Models\ProductBarcode::where('product_id', $product->id)
                    ->where('shop_id', $order->shop_id)
                    ->where('is_sold', 0)
                    ->limit($soldQty)
                    ->get();
                
                foreach ($barcodes as $bc) {
                    $bc->update([
                        'is_sold' => 1
                    ]);

                    if ($bc->inward_product_id) {
                        $inwardProd = \App\Models\InwardProduct::find($bc->inward_product_id);
                        if ($inwardProd) {
                            $newInwardQty = max(0, (int)$inwardProd->quantity - 1);
                            $inwardProd->update(['quantity' => $newInwardQty]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("POS Barcode sold status update failed: " . $e->getMessage());
        }

        $posCart->products()->detach();
        $posCart->delete();

        self::createNewCart();

        return $order;
    }

    private static function createNewCart()
    {
        $shop = generaleSetting('shop');

        return self::create([
            'name' => str()->random(12),
            'shop_id' => $shop->id,
            'ip_address' => request()->ip(),
            'is_draft' => false,
        ]);
    }
}
