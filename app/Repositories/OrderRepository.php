<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enums\DiscountType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\OrderMailEvent;
use App\Http\Requests\OrderRequest;
use App\Models\AdminCoupon;
use App\Models\GeneraleSetting;
use App\Models\Order;
use App\Models\OrderVatTax;
use App\Models\Payment;
use App\Models\Shop;
use App\Services\NotificationServices;

class OrderRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return Order::class;
    }

    public static function getShopSales($shopId)
    {
        return self::query()->withoutGlobalScopes()->where('shop_id', $shopId)->get();
    }

    /**
     * Store new order from cart
     */
    public static function storeByRequestFromCart(OrderRequest $request, $paymentMethod, $carts): Payment
    {
        $totalPayableAmount = 0;

        $shippingDetails = collect($request->shipping_details ?? []);
//        dd($shippingDetails);

        $payment = Payment::create([
            'amount' => $totalPayableAmount,
            'payment_method' => $request->payment_method,
        ]);

        $shopProducts = $carts->groupBy('shop_id');

        foreach ($shopProducts as $shopId => $cartProducts) {

            $shopShipping = $shippingDetails->firstWhere('shop_id', (int)$shopId);

            $deliveryCharge = (float) (
                $shopShipping['shipping_charge']
                ?? $shopShipping['rate']
                ?? 0
            );

            $shop = Shop::find($shopId);

//            $getCartAmounts = self::getCartWiseAmounts($shop, collect($cartProducts), $request->coupon_code);
//            $getCartAmounts = self::getCartWiseAmounts($shop, collect($cartProducts), $request->coupon_code);
            $getCartAmounts = self::getCartWiseAmounts(
                $shop,
                collect($cartProducts),
                $request->coupon_code,
                $deliveryCharge
            );
            $order = self::createNewOrder($request, $shop, $paymentMethod, $getCartAmounts);

            $totalPayableAmount += $getCartAmounts['payableAmount'];
            $payment->orders()->attach($order->id);

            foreach ($cartProducts as $cart) {

                if (! $cart || ! $cart->product) {
                    continue;
                }

                $product = $cart->product;

                $product->decrement('quantity', $cart->quantity);

                $inwardProduct = $cart->inward_product_id
                    ? \App\Models\InwardProduct::find($cart->inward_product_id)
                    : null;

                $inwardInvoice = $cart->inward_invoice_id
                    ? \App\Models\InwardInvoice::find($cart->inward_invoice_id)
                    : null;

                /*
                 |--------------------------------------------------------------------------
                 | PRICE LOGIC
                 |--------------------------------------------------------------------------
                 | carts.price = Original MRP
                 | carts.mrp   = Final Selling Price
                 |
                 | Example:
                 | carts.price = 700
                 | carts.mrp   = 350
                 |
                 | order_products.price = 350
                 | order_products.mrp   = 700
                 */

                $sellingPrice = (float) ($cart->mrp ?? 0);

                if ($sellingPrice <= 0) {
                    if ($inwardProduct && (float) $inwardProduct->discount_price > 0) {
                        $sellingPrice = (float) $inwardProduct->discount_price;
                    } elseif ($inwardProduct && (float) $inwardProduct->price > 0) {
                        $sellingPrice = (float) $inwardProduct->price;
                    } elseif ((float) $product->discount_price > 0) {
                        $sellingPrice = (float) $product->discount_price;
                    } else {
                        $sellingPrice = (float) $product->price;
                    }
                }

                $mrp = (float) ($cart->price ?? 0);

                if ($mrp <= 0) {
                    if ($inwardProduct && (float) $inwardProduct->mrp > 0) {
                        $mrp = (float) $inwardProduct->mrp;
                    } elseif ((float) $product->mrp > 0) {
                        $mrp = (float) $product->mrp;
                    } else {
                        $mrp = $sellingPrice;
                    }
                }

                if ($mrp < $sellingPrice) {
                    $mrp = $sellingPrice;
                }

                $qty = (int) ($cart->quantity ?? 1);

                $discountPercent = (float) ($cart->discount ?? 0);
                $discountAmount = max(0, ($mrp - $sellingPrice) * $qty);

                /*
                 |--------------------------------------------------------------------------
                 | SIZE & COLOR NAME LOGIC
                 |--------------------------------------------------------------------------
                 */

//                $sizeName = null;
//                $colorName = null;

                $sizeName = null;
                $colorName = null;

                if (!empty($cart->size)) {
                    $sizeName = \App\Models\Size::where('id', $cart->size)->value('name');
                }

                if (!empty($cart->color)) {
                    $colorName = \App\Models\Color::where('id', $cart->color)->value('name');
                }

                /*
                 |--------------------------------------------------------------------------
                 | TAX LOGIC
                 |--------------------------------------------------------------------------
                 | Tax payable me add nahi karna.
                 | Sirf report/order_products me store karna.
                 */

                $taxPercentage = 0;
                $taxAmount = 0;
                $vatTaxName = null;

                if ($inwardProduct && $inwardProduct->vat_tax_id) {
                    $vatTax = \App\Models\VatTax::find($inwardProduct->vat_tax_id);

                    if ($vatTax) {
                        $taxPercentage = (float) $vatTax->percentage;
                        $taxAmount = round(($sellingPrice * $qty) * ($taxPercentage / 100), 2);
                        $vatTaxName = $vatTax->name;
                    }
                } elseif ($product->vat_tax_id) {
                    $vatTax = \App\Models\VatTax::find($product->vat_tax_id);

                    if ($vatTax) {
                        $taxPercentage = (float) $vatTax->percentage;
                        $taxAmount = round(($sellingPrice * $qty) * ($taxPercentage / 100), 2);
                        $vatTaxName = $vatTax->name;
                    }
                }

                $order->products()->attach($product->id, [
                    'quantity' => $qty,

                    'color' => $colorName,
                    'size' => $sizeName,
                    'unit' => $cart->unit,

                    // IMPORTANT FIX
                    'price' => round($sellingPrice, 2),
                    'mrp' => round($mrp, 2),

                    'discount' => round($discountPercent, 2),
                    'discount_amount' => round($discountAmount, 2),

                    'tax_percentage' => round($taxPercentage, 2),
                    'tax_amount' => round($taxAmount, 2),
                    'vat_tax_name' => $vatTaxName,

                    'inward_invoice_id' => $inwardInvoice?->id,
                    'inward_product_id' => $inwardProduct?->id,
                ]);
            }

            foreach ($getCartAmounts['allVatTaxes'] ?? [] as $vatTax) {
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

            $user = auth()->user();

            if ($user?->email) {
                try {
                    OrderMailEvent::dispatch($user->email, $order);
                } catch (\Throwable $th) {
                }
            }
        }

        $payment->update([
            'amount' => round($totalPayableAmount, 2),
        ]);

        $isBuyNow = $request->is_buy_now ?? false;
        $customer = auth()->user()->customer;

        $customer->carts()
            ->whereIn('shop_id', $request->shop_ids)
            ->where('is_buy_now', $isBuyNow)
            ->delete();

        return $payment;
    }

    /**
     * Create new order
     */
    private static function createNewOrder($request, $shop, $paymentMethod, $getCartAmounts)
    {
        $lastOrderId = self::query()->max('id');

        $order = self::create([
            'shop_id' => $shop->id,
            'order_code' => str_pad($lastOrderId + 1, 6, '0', STR_PAD_LEFT),
            'prefix' => $shop->prefix ?? 'RC',
            'customer_id' => auth()->user()->customer->id,
            'coupon_id' => $getCartAmounts['coupon'],
            'delivery_charge' => $getCartAmounts['deliveryCharge'],
            'payable_amount' => $getCartAmounts['payableAmount'],
            'total_amount' => $getCartAmounts['totalAmount'],
            'total_mrp' => $getCartAmounts['totalMrp'] ?? $getCartAmounts['totalAmount'],
            'tax_amount' => $getCartAmounts['totalTaxAmount'],
            'coupon_discount' => $getCartAmounts['discount'],
            'payment_method' => $paymentMethod->value,
            'order_status' => OrderStatus::PENDING->value,
            'address_id' => $request->address_id,
            'instruction' => $request->note,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);

        $generalSetting = generaleSetting('setting');

        if ($generalSetting?->business_based_on == 'subscription') {
            $subscription = $shop->currentSubscription;

            if ($subscription && $subscription->remaining_sales && $subscription->remaining_sales > 0) {
                $subscription->update([
                    'remaining_sales' => $subscription->remaining_sales - 1
                ]);
            }
        }

        return $order;
    }

    /**
     * Get cart wise amounts - FIXED
     */


    /**
     * Get cart wise amounts - FIXED
     */
//    private static function getCartWiseAmounts(Shop $shop, $carts, $couponCode = null): array
//    {
//        $totalAmount = 0;
//        $totalMrp = 0;
//        $discount = 0;
//        $coupon = null;
//        $totalTaxAmount = 0;
//
//        $orderQty = $carts->sum('quantity');
//        $deliveryCharge = getDeliveryCharge($orderQty);
//
//        $allVatTaxes = [];
//
//        foreach ($carts ?? [] as $cart) {
//
//            if (! $cart) {
//                continue;
//            }
//
//            $product = $cart->product;
//
//            // ✅ CORRECT: Use cart->mrp as selling price (discounted)
//            $sellingPrice = $cart->mrp ?? $product->price ?? 0;
//            $mrp = $cart->price ?? $product->mrp ?? $product->price ?? 0;
//
//            if ($sellingPrice == 0) {
//                $sellingPrice = $product->discount_price > 0 ? $product->discount_price : $product->price;
//            }
//            if ($mrp == 0) {
//                $mrp = $product->price;
//            }
//
//            // ✅ Flash sale check
//            $flashSale = $product->flashSales?->first();
//            $flashSaleProduct = null;
//            $quantity = 0;
//
//            if ($flashSale) {
//                $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();
//                $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;
//
//                if ($quantity == 0) {
//                    $flashSaleProduct = null;
//                } else {
//                    $sellingPrice = $flashSaleProduct->pivot->price;
//                }
//            }
//
//            // ✅ Add size and color extra price
//            $sizePrice = $product->sizes()?->where('id', $cart->size)->first()?->pivot?->price ?? 0;
//            $colorPrice = $product->colors()?->where('id', $cart->color)->first()?->pivot?->price ?? 0;
//            $sellingPrice = $sellingPrice + $sizePrice + $colorPrice;
//            $mrp = $mrp + $sizePrice + $colorPrice;
//
//            // ✅ Add to total
//            $totalAmount += ($sellingPrice * $cart->quantity);
//            $totalMrp += ($mrp * $cart->quantity);
//            $discount += (($mrp - $sellingPrice) * $cart->quantity);
//
//            // ✅ Calculate tax on selling price
//            $taxPercentage = 0;
//            $taxAmount = 0;
//            $vatTaxName = null;
//
//            $hsnMasterId = $product->hsn_master_id ?? null;
//
//            if (!$hsnMasterId && $cart->inward_product_id) {
//                $inwardProduct = \App\Models\InwardProduct::find($cart->inward_product_id);
//                if ($inwardProduct) {
//                    $hsnMasterId = $inwardProduct->hsn_master_id ?? null;
//                }
//            }
//
//            if ($hsnMasterId) {
//                $hsnMaster = \App\Models\HsnMaster::with(['subHsn', 'vattax'])->find($hsnMasterId);
//                if ($hsnMaster) {
//                    $selectedSubHsn = null;
//                    foreach ($hsnMaster->subHsn as $sub) {
//                        $fromRate = floatval($sub->from_sales_rate ?? 0);
//                        $toRate = floatval($sub->to_sales_rate ?? 0);
//                        if ($toRate == 0 || $toRate == null) {
//                            if ($sellingPrice >= $fromRate) {
//                                $selectedSubHsn = $sub;
//                                break;
//                            }
//                        } else {
//                            if ($sellingPrice >= $fromRate && $sellingPrice <= $toRate) {
//                                $selectedSubHsn = $sub;
//                                break;
//                            }
//                        }
//                    }
//
//                    if ($selectedSubHsn && $selectedSubHsn->vat_tax_id) {
//                        $vatTax = \App\Models\VatTax::find($selectedSubHsn->vat_tax_id);
//                        if ($vatTax) {
//                            $taxPercentage = floatval($vatTax->percentage);
//                            $taxAmount = $sellingPrice * ($taxPercentage / 100);
//                            $vatTaxName = $vatTax->name;
//                        }
//                    } elseif ($hsnMaster->vat_tax_id) {
//                        $vatTax = $hsnMaster->vattax;
//                        if ($vatTax) {
//                            $taxPercentage = floatval($vatTax->percentage);
//                            $taxAmount = $sellingPrice * ($taxPercentage / 100);
//                            $vatTaxName = $vatTax->name;
//                        }
//                    }
//                }
//            }
//
//            // ✅ Fallback to product vatTaxes
//            if ($taxPercentage == 0 && $product->vatTaxes) {
//                foreach ($product->vatTaxes as $tax) {
//                    if ($tax->percentage > 0) {
//                        $taxPercentage = floatval($tax->percentage);
//                        $taxAmount = $sellingPrice * ($taxPercentage / 100);
//                        $vatTaxName = $tax->name;
//                        break;
//                    }
//                }
//            }
//
//            $totalTaxAmount += $taxAmount;
//
//            // ✅ Store individual VAT for summary
//            if ($taxPercentage > 0 && $vatTaxName) {
//                $allVatTaxes[] = (object) [
//                    'name' => $vatTaxName,
//                    'percentage' => $taxPercentage,
//                    'amount' => $taxAmount,
//                ];
//            }
//        }
//
//        // ✅ Global VAT (if any)
//        $vatTaxes = VatTaxRepository::getActiveVatTaxes();
//
//        foreach ($vatTaxes ?? [] as $vatTax) {
//            if ($vatTax?->name && $vatTax?->percentage > 0) {
//                $exists = false;
//                foreach ($allVatTaxes as $existingTax) {
//                    if ($existingTax->name == $vatTax->name && $existingTax->percentage == $vatTax->percentage) {
//                        $exists = true;
//                        break;
//                    }
//                }
//
//                if (!$exists) {
//                    $taxAmount = round($totalAmount * ($vatTax->percentage / 100), 2);
//                    $allVatTaxes[] = (object) [
//                        'name' => $vatTax->name,
//                        'percentage' => $vatTax->percentage,
//                        'amount' => $taxAmount,
//                    ];
//                    $totalTaxAmount += $taxAmount;
//                }
//            }
//        }
//
//        // ✅ Get coupon discount
//        $couponDiscount = self::getCouponDiscount($totalAmount, $shop->id, $couponCode);
//
//        if ($couponDiscount['total_discount_amount'] > 0) {
//            $discount += $couponDiscount['total_discount_amount'];
//            $coupon = $couponDiscount['coupon'];
//        }
//
//        // ✅ Payable amount = Total after discount + delivery
//        $payableAmount = ($totalAmount + $deliveryCharge) - $discount;
//
//        return [
//            'totalAmount' => $totalAmount,
//            'totalMrp' => $totalMrp,
//            'totalTaxAmount' => $totalTaxAmount,
//            'payableAmount' => $payableAmount,
//            'discount' => $discount,
//            'deliveryCharge' => $deliveryCharge,
//            'coupon' => $coupon?->id,
//            'allVatTaxes' => $allVatTaxes,
//        ];
//    }

    private static function getCartWiseAmounts(Shop $shop, $carts, $couponCode = null,float $deliveryCharge = 0): array
    {
        $totalAmount = 0;
        $totalMrp = 0;
        $coupon = null;
        $couponDiscountAmount = 0;
        $totalTaxAmount = 0;
        $allVatTaxes = [];

//        $orderQty = $carts->sum('quantity');
//        $deliveryCharge = getDeliveryCharge($orderQty);

        foreach ($carts as $cart) {
            if (! $cart || ! $cart->product) {
                continue;
            }

            $product = $cart->product;
            $qty = (int) ($cart->quantity ?? 1);

            $inwardProduct = $cart->inward_product_id
                ? \App\Models\InwardProduct::find($cart->inward_product_id)
                : null;

            // cart->price = Original MRP
            // cart->mrp = Final selling price
            $mrp = (float) ($cart->price ?? 0);
            if ($mrp <= 0) {
                $mrp = (float) ($inwardProduct?->mrp ?? $product->mrp ?? $product->price ?? 0);
            }

            $sellingPrice = (float) ($cart->mrp ?? 0);
            if ($sellingPrice <= 0) {
                $sellingPrice = (float) (
                    ($inwardProduct?->discount_price > 0 ? $inwardProduct->discount_price : null)
                    ?? $inwardProduct?->price
                    ?? ($product->discount_price > 0 ? $product->discount_price : null)
                    ?? $product->price
                    ?? 0
                );
            }

            if ($mrp <= 0) {
                $mrp = $sellingPrice;
            }

            $lineTotal = $sellingPrice * $qty;
            $lineMrpTotal = $mrp * $qty;

            $totalAmount += $lineTotal;
            $totalMrp += $lineMrpTotal;

            // Tax only report/store ke liye hai, payable me add nahi karna
            $taxPercentage = 0;
            $taxAmount = 0;
            $vatTaxName = null;

            $hsnMasterId = $inwardProduct?->hsn_master_id ?? $product->hsn_master_id ?? null;

            if ($hsnMasterId) {
                $hsnMaster = \App\Models\HsnMaster::with(['subHsn', 'vattax'])->find($hsnMasterId);

                if ($hsnMaster) {
                    $vatTax = $hsnMaster->vattax;

                    if ($vatTax) {
                        $taxPercentage = (float) $vatTax->percentage;
                        $taxAmount = round($lineTotal * ($taxPercentage / 100), 2);
                        $vatTaxName = $vatTax->name;
                    }
                }
            }

            $totalTaxAmount += $taxAmount;

            if ($taxPercentage > 0 && $vatTaxName) {
                $taxKey = $vatTaxName . '_' . $taxPercentage;

                if (! isset($allVatTaxes[$taxKey])) {
                    $allVatTaxes[$taxKey] = (object) [
                        'name' => $vatTaxName,
                        'percentage' => $taxPercentage,
                        'amount' => 0,
                    ];
                }

                $allVatTaxes[$taxKey]->amount += $taxAmount;
            }
        }

        $couponDiscount = self::getCouponDiscount($totalAmount, $shop->id, $couponCode);

        if (($couponDiscount['total_discount_amount'] ?? 0) > 0) {
            $couponDiscountAmount = (float) $couponDiscount['total_discount_amount'];
            $coupon = $couponDiscount['coupon'];
        }

        // totalAmount already discounted selling price hai
        // product discount dobara minus nahi karna
        $payableAmount = ($totalAmount + $deliveryCharge) - $couponDiscountAmount;

        return [
            'totalAmount' => round($totalAmount, 2),
            'totalMrp' => round($totalMrp, 2),
            'totalTaxAmount' => round($totalTaxAmount, 2),
            'payableAmount' => round($payableAmount, 2),
            'discount' => round($couponDiscountAmount, 2),
            'deliveryCharge' => round($deliveryCharge, 2),
            'coupon' => $coupon?->id,
            'allVatTaxes' => array_values($allVatTaxes),
        ];
    }
    /**
     * Creates a new order based on the provided order
     */
    public static function reOrder(Order $order, $payment): Order
    {
        $lastOrderId = self::query()->max('id');

        $newOrder = self::create([
            'shop_id' => $order->shop_id,
            'order_code' => str_pad($lastOrderId + 1, 6, '0', STR_PAD_LEFT),
            'prefix' => 'RC',
            'customer_id' => $order->customer_id,
            'coupon_id' => $order->coupon_id ?? null,
            'delivery_charge' => $order->delivery_charge,
            'payable_amount' => $order->payable_amount,
            'total_amount' => $order->total_amount,
            'total_mrp' => $order->total_mrp ?? $order->total_amount,
            'tax_amount' => $order->tax_amount,
            'coupon_discount' => $order->coupon_discount,
            'payment_method' => $payment->payment_method ?? $order->payment_method,
            'order_status' => OrderStatus::PENDING->value,
            'address_id' => $order->address_id,
            'instruction' => $order->instruction,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);

        foreach ($order->products as $product) {
            $qty = $product->pivot->quantity;
            $product->decrement('quantity', $qty);

            $newOrder->products()->attach($product->id, [
                'quantity' => $product->pivot->quantity,
                'color' => $product->pivot->color ?? null,
                'size' => $product->pivot->size ?? null,
                'unit' => $product->pivot->unit ?? null,
                'price' => $product->pivot->price,
                'mrp' => $product->pivot->mrp ?? $product->pivot->price,
                'discount' => $product->pivot->discount ?? 0,
                'discount_amount' => $product->pivot->discount_amount ?? 0,
                'tax_percentage' => $product->pivot->tax_percentage ?? 0,
                'tax_amount' => $product->pivot->tax_amount ?? 0,
                'vat_tax_name' => $product->pivot->vat_tax_name ?? null,
                'inward_invoice_id' => $product->pivot->inward_invoice_id ?? null,
                'inward_product_id' => $product->pivot->inward_product_id ?? null,
            ]);
        }

        foreach ($order->vatTaxes ?? [] as $vatTax) {
            if (! $vatTax) continue;
            OrderVatTax::create([
                'order_id' => $newOrder->id,
                'name' => $vatTax->name,
                'percentage' => $vatTax->percentage,
                'amount' => $vatTax->amount,
            ]);
        }

        $user = auth()->user();
        if ($user?->email) {
            try {
                OrderMailEvent::dispatch($user->email, $newOrder);
            } catch (\Throwable $th) {
            }
        }

        return $newOrder;
    }

    /**
     * Get applied coupon orders
     */
    public static function getAppliedCouponOrders($coupon)
    {
        return auth()->user()->customer?->orders()?->where('coupon_id', $coupon->id)->get();
    }

    /**
     * Get coupon discount
     */
    public static function getCouponDiscount($totalAmount, $shopId, $couponCode = null)
    {
        $totalOrderAmount = 0;
        $totalDiscountAmount = 0;
        $coupon = null;

        if ($couponCode) {
            $shop = Shop::find($shopId);
            $coupon = $shop->coupons()->where('code', $couponCode)->Active()->isValid()->first();

            if (! $coupon) {
                $coupon = AdminCoupon::where('shop_id', $shopId)->whereHas('coupon', function ($query) use ($couponCode) {
                    $query->where('code', $couponCode)->Active()->isValid();
                })->first()?->coupon;
            }

            if ($coupon) {
                $discount = self::getCouponDiscountAmount($coupon, $totalAmount);

                $totalOrderAmount += $discount['total_amount'];
                $totalDiscountAmount += $discount['discount_amount'];
            }
        } else {

            $collectedCoupons = CouponRepository::getCollectedCoupons($shopId);

            foreach ($collectedCoupons as $collectedCoupon) {

                $discount = self::getCouponDiscountAmount($collectedCoupon, $totalAmount);

                $totalOrderAmount += $discount['total_amount'];

                if ($discount['discount_amount'] > 0) {
                    $coupon = $collectedCoupon;
                    $totalDiscountAmount += $discount['discount_amount'];
                    break;
                }
            }
        }

        return [
            'total_order_amount' => $totalOrderAmount,
            'total_discount_amount' => $totalDiscountAmount,
            'coupon' => $coupon,
        ];
    }

    /**
     * Get coupon discount amount
     */
    private static function getCouponDiscountAmount($coupon, $totalAmount)
    {
        $appliedOrders = self::getAppliedCouponOrders($coupon);

        $amount = $coupon->type->value == DiscountType::PERCENTAGE->value ? ($totalAmount * $coupon->discount) / 100 : $coupon->discount;

        $couponDiscount = 0;
        if ($appliedOrders->count() < ($coupon->limit_for_user ?? 500) && $coupon->min_amount <= $totalAmount) {
            $couponDiscount = $amount;
            if ($coupon->max_discount_amount && $coupon->max_discount_amount < $amount) {
                $couponDiscount = $coupon->max_discount_amount;
            }
        }

        return [
            'total_amount' => $totalAmount,
            'discount_amount' => (float) round($couponDiscount ?? 0, 2),
        ];
    }

    /**
     * Order status update from rider
     */
    public static function OrderStatusUpdateFromRider(Order $order, $driverOrder, $orderStatus)
    {
        if ($orderStatus == OrderStatus::PROCESSING->value) {
            $driverOrder->update(['is_accept' => true]);
        }

        $order->update([
            'order_status' => ($orderStatus == 'deliveredAndPaid') ? OrderStatus::DELIVERED->value : $orderStatus,
        ]);

        if ($orderStatus == OrderStatus::PICKUP->value) {
            $order->update([
                'pick_date' => now(),
                'order_status' => OrderStatus::ON_THE_WAY->value,
            ]);
        }

        $paymentMethod = $order->payment_method->value;

        $isDelivery = false;
        if ($paymentMethod != PaymentMethod::CASH->value && $orderStatus == OrderStatus::DELIVERED->value) {
            $isDelivery = true;
        }

        if (($orderStatus == 'deliveredAndPaid') || $isDelivery) {

            $driverOrder->update(['is_completed' => true]);

            if ($paymentMethod == PaymentMethod::CASH->value) {
                $driverOrder->update(['cash_collect' => true]);

                $totalCashCollected = $driverOrder->driver->total_cash_collected + $order->payable_amount;

                $driverOrder->driver->update([
                    'total_cash_collected' => $totalCashCollected,
                ]);
            }

            $generaleSetting = GeneraleSetting::first();

            $commission = 0;

            if ($generaleSetting?->business_based_on == 'commission' && $generaleSetting?->commission_charge != 'monthly') {

                if ($generaleSetting?->commission_type != 'fixed') {
                    $commission = $order->total_amount * $generaleSetting->commission / 100;
                } else {
                    $commission = $generaleSetting->commission ?? 0;
                }
            }

            $order->update([
                'delivery_date' => now(),
                'delivered_at' => now(),
                'payment_status' => PaymentStatus::PAID->value,
                'admin_commission' => $commission,
            ]);

            $wallet = $order->shop->user->wallet;

            WalletRepository::updateByRequest($wallet, $order->total_amount, 'credit');

            if ($generaleSetting?->business_based_on == 'commission') {
                TransactionRepository::storeByRequest($wallet, $commission, 'debit', true, true, 'admin commission added', 'order commission added in admin wallet');
            }

            $driverWallet = DriverRepository::getWallet($driverOrder->driver);

            $deliveryCharge = $order->delivery_charge;

            WalletRepository::updateByRequest($driverWallet, $deliveryCharge, 'credit');
        }

        $user = $order->customer?->user;

        $message = "Hello {$user?->name}. Your order status is {$orderStatus}. OrderID: {$order->prefix}{$order->order_code}";

        $title = 'Order Status Update';

        $devices = $user?->devices;

        if (count($devices) > 0) {

            $deviceKeys = $devices->pluck('key')->toArray();
            try {
                NotificationServices::sendNotification($message, $deviceKeys, $title);
            } catch (\Throwable $th) {
            }
        }

        NotificationRepository::storeByRequest((object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $user?->id,
            'url' => $order->id,
            'type' => 'order',
            'icon' => null,
            'is_read' => false,
        ]);
    }
}