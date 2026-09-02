<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Http\Requests\CartRequest;
use App\Http\Resources\ColorResource;
use App\Http\Resources\SizeResource;
use App\Models\Cart;
use App\Models\Color;
use App\Models\HsnMaster;
use App\Models\Product;
use App\Models\Size;
use App\Models\VatTax;
use Illuminate\Support\Number;

class CartRepository extends Repository
{
    public static function model()
    {
        return Cart::class;
    }

//    public static function ShopWiseCartProducts($groupCart)
//    {
//        $totalItems = 0;
//        $shopWiseProducts = collect([]);
//        $info = null;
//
//        foreach ($groupCart as $key => $products) {
//            $productArray = collect([]);
//
//            foreach ($products as $cart) {
//
//                $product = $cart->product;
//
//                if (! $product) {
//                    $cart->delete();
//                    $info = 'Some products are removed from cart due to unavailability';
//                    continue;
//                }
//
//                $totalItems++;
//
//                $discountPercentage = $product->getDiscountPercentage($product->price, $product->discount_price);
//
//                $totalSold = $product->orders->sum('pivot.quantity');
//
//                $flashSale = $product->flashSales?->first();
//                $flashSaleProduct = null;
//                $quantity = null;
//
//                if ($flashSale) {
//                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();
//
//                    $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;
//
//                    if ($quantity == 0) {
//                        $quantity = null;
//                        $flashSaleProduct = null;
//                    } else {
//                        $discountPercentage = $flashSale?->pivot->discount;
//                    }
//                }
//
//                $size = $product->sizes()?->where('id', $cart->size)->first();
//                $color = $product->colors()?->where('id', $cart->color)->first();
//
//                $sizePrice = $size?->pivot?->price ?? 0;
//                $colorPrice = $color?->pivot?->price ?? 0;
//                $extraPrice = $sizePrice + $colorPrice;
//
//                $discountPrice = $product->discount_price > 0 ? ($product->discount_price + $extraPrice) : 0;
//                if ($flashSaleProduct) {
//                    $discountPrice = $flashSaleProduct->pivot->price + $extraPrice;
//                }
//
//                $mainPrice = $product->price + $extraPrice;
//
//                // calculate vat taxes
//                $priceTaxAmount = 0;
//                $discountTaxAmount = 0;
//                foreach ($product->vatTaxes ?? [] as $tax) {
//                    if ($tax->percentage > 0) {
//                        $priceTaxAmount += $mainPrice * ($tax->percentage / 100);
//                        $discountPrice > 0 ? $discountTaxAmount += $discountPrice * ($tax->percentage / 100) : null;
//                    }
//                }
//
//                $mainPrice += $priceTaxAmount;
//                $discountPrice > 0 ? $discountPrice += $discountTaxAmount : null;
//
//                if ($discountPrice > 0) {
//                    $discountPercentage = ($mainPrice - $discountPrice) / $mainPrice * 100;
//                }
//
//                $productArray[] = (object) [
//                    'id' => $product->id,
//                    'quantity' => (int) $cart->quantity,
//                    'name' => $product->name,
//                    'thumbnail' => $product->thumbnail,
//                    'brand' => $product->brand?->name ?? null,
//                    'price' => (float) number_format($mainPrice, 2, '.', ''),
//                    'discount_price' => (float) number_format($discountPrice, 2, '.', ''),
//                    'discount_percentage' => (float) number_format($discountPercentage, 2, '.', ''),
//                    'rating' => (float) $product->averageRating,
//                    'total_reviews' => (string) Number::abbreviate($product->reviews->count(), maxPrecision: 2),
//                    'total_sold' => (string) number_format($totalSold, 0, '.', ','),
//                    'color' => $color ? ColorResource::make($color) : null,
//                    'size' => $size ? SizeResource::make($size) : null,
//                    'unit' => $cart->unit,
//                ];
//            }
//
//            if ($productArray->isEmpty()) {
//                continue;
//            }
//
//            $shop = $products[0]?->shop;
//
//            $lastOnline = $shop->last_online >= now() ? true : false;
//
//            $shopWiseProducts[] = (object) [
//                'shop_id' => $key,
//                'shop_name' => $shop->name,
//                'shop_logo' => $shop->logo,
//                'shop_rating' => (float) $shop->averageRating,
//                'shop_online' => $lastOnline,
//                'products' => $productArray,
//            ];
//        }
//
//        return [
//            'total_items' => $totalItems,
//            'shop_wise_products' => $shopWiseProducts,
//            'info' => $info,
//        ];
//    }
//    public static function ShopWiseCartProducts($groupCart)
//    {
//        $totalItems = 0;
//        $shopWiseProducts = collect([]);
//        $info = null;
//
//        foreach ($groupCart as $key => $products) {
//            $productArray = collect([]);
//
//            foreach ($products as $cart) {
//
//                $product = $cart->product;
//
//                if (! $product) {
//                    $cart->delete();
//                    $info = 'Some products are removed from cart due to unavailability';
//                    continue;
//                }
//
//                $totalItems++;
//
//                // ✅ Use variant price from cart
//                $variantPrice = $cart->price ?? 0;
//                $variantMrp = $cart->mrp ?? $product->mrp ?? $product->price ?? 0;
//                $variantDiscount = $cart->discount ?? 0;
//
//                // ✅ If no variant price stored, calculate from product
//                if ($variantPrice == 0) {
//                    $size = $product->sizes()?->where('id', $cart->size)->first();
//                    $color = $product->colors()?->where('id', $cart->color)->first();
//
//                    $sizePrice = $size?->pivot?->price ?? 0;
//                    $colorPrice = $color?->pivot?->price ?? 0;
//                    $extraPrice = $sizePrice + $colorPrice;
//
//                    $discountPrice = $product->discount_price > 0 ? ($product->discount_price + $extraPrice) : 0;
//
//                    $mainPrice = $product->price + $extraPrice;
//                    $variantPrice = $discountPrice > 0 ? $discountPrice : $mainPrice;
//                    $variantMrp = $mainPrice;
//                    $variantDiscount = $product->discount_price ?? 0;
//                }
//
//                // ✅ Calculate discount percentage
//                $discountPercentage = 0;
//                if ($variantMrp > 0 && $variantPrice < $variantMrp) {
//                    $discountPercentage = (($variantMrp - $variantPrice) / $variantMrp) * 100;
//                }
//
//                $totalSold = $product->orders->sum('pivot.quantity');
//
//                $flashSale = $product->flashSales?->first();
//                $flashSaleProduct = null;
//                $quantity = null;
//
//                if ($flashSale) {
//                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();
//                    $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;
//
//                    if ($quantity == 0) {
//                        $quantity = null;
//                        $flashSaleProduct = null;
//                    } else {
//                        $discountPercentage = $flashSale?->pivot->discount;
//                    }
//                }
//
//                $size = $product->sizes()?->where('id', $cart->size)->first();
//                $color = $product->colors()?->where('id', $cart->color)->first();
//
//                // ✅ Get color and size names
//                $colorName = $color?->name ?? ($cart->color ?? 'N/A');
//                $sizeName = $size?->name ?? ($cart->size ?? 'N/A');
//
//                // ✅ Calculate VAT taxes on variant price
//                $priceTaxAmount = 0;
//                $discountTaxAmount = 0;
//                foreach ($product->vatTaxes ?? [] as $tax) {
//                    if ($tax->percentage > 0) {
//                        $priceTaxAmount += $variantMrp * ($tax->percentage / 100);
//                        $variantPrice > 0 ? $discountTaxAmount += $variantPrice * ($tax->percentage / 100) : null;
//                    }
//                }
//
//                $finalMrp = $variantMrp + $priceTaxAmount;
//                $finalPrice = $variantPrice > 0 ? $variantPrice + $discountTaxAmount : $variantMrp + $priceTaxAmount;
//
//                $productArray[] = (object) [
//                    'id' => $product->id,
//                    'quantity' => (int) $cart->quantity,
//                    'name' => $product->name,
//                    'thumbnail' => $product->thumbnail,
//                    'brand' => $product->brand?->name ?? null,
//                    'price' => (float) number_format($finalMrp, 2, '.', ''),
//                    'discount_price' => (float) number_format($finalPrice, 2, '.', ''),
//                    'discount_percentage' => (float) number_format($discountPercentage, 2, '.', ''),
//                    'rating' => (float) $product->averageRating,
//                    'total_reviews' => (string) Number::abbreviate($product->reviews->count(), maxPrecision: 2),
//                    'total_sold' => (string) number_format($totalSold, 0, '.', ','),
//                    'color' => $color ? ColorResource::make($color) : null,
//                    'color_name' => $colorName,
//                    'size' => $size ? SizeResource::make($size) : null,
//                    'size_name' => $sizeName,
//                    'unit' => $cart->unit,
//                ];
//            }
//
//            if ($productArray->isEmpty()) {
//                continue;
//            }
//
//            $shop = $products[0]?->shop;
//
//            $lastOnline = $shop->last_online >= now() ? true : false;
//
//            $shopWiseProducts[] = (object) [
//                'shop_id' => $key,
//                'shop_name' => $shop->name,
//                'shop_logo' => $shop->logo,
//                'shop_rating' => (float) $shop->averageRating,
//                'shop_online' => $lastOnline,
//                'products' => $productArray,
//            ];
//        }
//
//        return [
//            'total_items' => $totalItems,
//            'shop_wise_products' => $shopWiseProducts,
//            'info' => $info,
//        ];
//    }

//    public static function ShopWiseCartProducts($groupCart)
//    {
//        $totalItems = 0;
//        $shopWiseProducts = collect([]);
//        $info = null;
//
//        foreach ($groupCart as $key => $products) {
//            $productArray = collect([]);
//
//            foreach ($products as $cart) {
//
//                $product = $cart->product;
//
//                if (! $product) {
//                    $cart->delete();
//                    $info = 'Some products are removed from cart due to unavailability';
//                    continue;
//                }
//
//                $totalItems++;
//
//                // ✅ Use variant price from cart
//                $variantPrice = $cart->price ?? 0;
//                $variantMrp = $cart->mrp ?? $product->mrp ?? $product->price ?? 0;
//                $variantDiscount = $cart->discount ?? 0;
//
//                // ✅ If no variant price stored, calculate from product
//                if ($variantPrice == 0) {
//                    $size = $product->sizes()?->where('id', $cart->size)->first();
//                    $color = $product->colors()?->where('id', $cart->color)->first();
//
//                    $sizePrice = $size?->pivot?->price ?? 0;
//                    $colorPrice = $color?->pivot?->price ?? 0;
//                    $extraPrice = $sizePrice + $colorPrice;
//
//                    $discountPrice = $product->discount_price > 0 ? ($product->discount_price + $extraPrice) : 0;
//
//                    $mainPrice = $product->price + $extraPrice;
//                    $variantPrice = $discountPrice > 0 ? $discountPrice : $mainPrice;
//                    $variantMrp = $mainPrice;
//                    $variantDiscount = $product->discount_price ?? 0;
//                }
//
//                // ✅ Calculate discount percentage
//                $discountPercentage = 0;
//                if ($variantMrp > 0 && $variantPrice < $variantMrp) {
//                    $discountPercentage = (($variantMrp - $variantPrice) / $variantMrp) * 100;
//                }
//
//                $totalSold = $product->orders->sum('pivot.quantity');
//
//                $flashSale = $product->flashSales?->first();
//                $flashSaleProduct = null;
//                $quantity = null;
//
//                if ($flashSale) {
//                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();
//                    $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;
//
//                    if ($quantity == 0) {
//                        $quantity = null;
//                        $flashSaleProduct = null;
//                    } else {
//                        $discountPercentage = $flashSale?->pivot->discount;
//                    }
//                }
//
//                // ✅ Get size and color with their names from database
//                $size = $product->sizes()?->where('id', $cart->size)->first();
//                $color = $product->colors()?->where('id', $cart->color)->first();
//
//                // ✅ Get color name
//                $colorName = 'N/A';
//                if ($color) {
//                    $colorName = $color->name;
//                } elseif ($cart->color) {
//                    $colorModel = Color::find($cart->color);
//                    if ($colorModel) {
//                        $colorName = $colorModel->name;
//                    } else {
//                        $colorName = $cart->color;
//                    }
//                }
//
//                // ✅ Get size name
//                $sizeName = 'N/A';
//                if ($size) {
//                    $sizeName = $size->name;
//                } elseif ($cart->size) {
//                    $sizeModel = Size::find($cart->size);
//                    if ($sizeModel) {
//                        $sizeName = $sizeModel->name;
//                    } else {
//                        $sizeName = $cart->size;
//                    }
//                }
//
//                // ✅ Calculate VAT taxes on variant price
//                $priceTaxAmount = 0;
//                $discountTaxAmount = 0;
//                foreach ($product->vatTaxes ?? [] as $tax) {
//                    if ($tax->percentage > 0) {
//                        $priceTaxAmount += $variantMrp * ($tax->percentage / 100);
//                        $variantPrice > 0 ? $discountTaxAmount += $variantPrice * ($tax->percentage / 100) : null;
//                    }
//                }
//
//                // ✅ CORRECT ORDER FOR CART DISPLAY:
//                // price = Discounted Price (460.60) - Main bold price
//                // discount_price = MRP (470.00) - Strikethrough
//                $finalPrice = $variantPrice > 0 ? $variantPrice + $discountTaxAmount : $variantMrp + $priceTaxAmount;
//                $finalMrp = $variantMrp + $priceTaxAmount;
//
//                // ✅ Recalculate discount percentage
//                $discountPercentage = 0;
//                if ($finalMrp > 0 && $finalPrice < $finalMrp) {
//                    $discountPercentage = (($finalMrp - $finalPrice) / $finalMrp) * 100;
//                }
//
//                $productArray[] = (object) [
//                    'id' => $product->id,
//                    'quantity' => (int) $cart->quantity,
//                    'name' => $product->name,
//                    'thumbnail' => $product->thumbnail,
//                    'brand' => $product->brand?->name ?? null,
//                    // ✅ price = Discounted Price (460.60) - Main bold
//                    'price' => (float) number_format($finalPrice, 2, '.', ''),
//                    // ✅ discount_price = MRP (470.00) - Strikethrough
//                    'discount_price' => (float) number_format($finalMrp, 2, '.', ''),
//                    'discount_percentage' => (float) number_format($discountPercentage, 2, '.', ''),
//                    'rating' => (float) $product->averageRating,
//                    'total_reviews' => (string) Number::abbreviate($product->reviews->count(), maxPrecision: 2),
//                    'total_sold' => (string) number_format($totalSold, 0, '.', ','),
//                    'color' => $color ? ColorResource::make($color) : null,
//                    'color_name' => $colorName,
//                    'size' => $size ? SizeResource::make($size) : null,
//                    'size_name' => $sizeName,
//                    'unit' => $cart->unit,
//                ];
//            }
//
//            if ($productArray->isEmpty()) {
//                continue;
//            }
//
//            $shop = $products[0]?->shop;
//
//            $lastOnline = $shop->last_online >= now() ? true : false;
//
//            $shopWiseProducts[] = (object) [
//                'shop_id' => $key,
//                'shop_name' => $shop->name,
//                'shop_logo' => $shop->logo,
//                'shop_rating' => (float) $shop->averageRating,
//                'shop_online' => $lastOnline,
//                'products' => $productArray,
//            ];
//        }
//
//        return [
//            'total_items' => $totalItems,
//            'shop_wise_products' => $shopWiseProducts,
//            'info' => $info,
//        ];
//    }

    public static function ShopWiseCartProducts($groupCart)
    {
        $totalItems = 0;
        $shopWiseProducts = collect([]);
        $info = null;

        foreach ($groupCart as $key => $products) {
            $productArray = collect([]);

            foreach ($products as $cart) {

                $product = $cart->product;

                if (! $product) {
                    $cart->delete();
                    $info = 'Some products are removed from cart due to unavailability';
                    continue;
                }

                $totalItems++;

                // ✅ Use variant price from cart
                $variantPrice = $cart->price ?? 0;
                $variantMrp = $cart->mrp ?? $product->mrp ?? $product->price ?? 0;
                $variantDiscount = $cart->discount ?? 0;

                // ✅ If no variant price stored, calculate from product
                if ($variantPrice == 0) {
                    $size = $product->sizes()?->where('id', $cart->size)->first();
                    $color = $product->colors()?->where('id', $cart->color)->first();

                    $sizePrice = $size?->pivot?->price ?? 0;
                    $colorPrice = $color?->pivot?->price ?? 0;
                    $extraPrice = $sizePrice + $colorPrice;

                    $discountPrice = $product->discount_price > 0 ? ($product->discount_price + $extraPrice) : 0;

                    $mainPrice = $product->price + $extraPrice;
                    $variantPrice = $discountPrice > 0 ? $discountPrice : $mainPrice;
                    $variantMrp = $mainPrice;
                    $variantDiscount = $product->discount_price ?? 0;
                }

                // ✅ Get size and color
                $size = $product->sizes()?->where('id', $cart->size)->first();
                $color = $product->colors()?->where('id', $cart->color)->first();

                // ✅ Get color name
                $colorName = 'N/A';
                if ($color) {
                    $colorName = $color->name;
                } elseif ($cart->color) {
                    $colorModel = Color::find($cart->color);
                    if ($colorModel) {
                        $colorName = $colorModel->name;
                    } else {
                        $colorName = $cart->color;
                    }
                }

                // ✅ Get size name
                $sizeName = 'N/A';
                if ($size) {
                    $sizeName = $size->name;
                } elseif ($cart->size) {
                    $sizeModel = Size::find($cart->size);
                    if ($sizeModel) {
                        $sizeName = $sizeModel->name;
                    } else {
                        $sizeName = $cart->size;
                    }
                }

                // ✅ ================================================ //
                // ✅ HSN MASTER & TAX CALCULATION - FIXED
                // ✅ ================================================ //

                $hsnCode = null;
                $taxPercentage = 0;
                $taxAmount = 0;
                $vatTaxName = null;

                // ✅ CORRECT: Use $variantMrp (460.60) for tax calculation
                // NOT $variantPrice (470.00)
                $taxablePrice = $variantMrp > 0 ? $variantMrp : $product->price;

                // ✅ Get HSN Master ID
                $hsnMasterId = $product->hsn_master_id ?? null;

                // ✅ If no hsn_master_id on product, try cart inward_product_id
                if (!$hsnMasterId && $cart->inward_product_id) {
                    $inwardProduct = \App\Models\InwardProduct::find($cart->inward_product_id);
                    if ($inwardProduct && $inwardProduct->hsn_master_id) {
                        $hsnMasterId = $inwardProduct->hsn_master_id;
                    }
                }

                // ✅ If still no hsn_master_id, try product's vat_tax_id directly
                if (!$hsnMasterId && $product->vat_tax_id) {
                    $vatTax = VatTax::find($product->vat_tax_id);
                    if ($vatTax && $vatTax->percentage > 0) {
                        $taxPercentage = floatval($vatTax->percentage);
                        $taxAmount = $taxablePrice * ($taxPercentage / 100);
                        $vatTaxName = $vatTax->name;
                    }
                }

                // ✅ If HSN Master ID exists, process HSN
                if ($hsnMasterId) {
                    $hsnMaster = HsnMaster::with(['subHsn', 'vattax'])->find($hsnMasterId);

                    if ($hsnMaster) {
                        $hsnCode = $hsnMaster->hsn_code;

                        // ✅ Find sub HSN based on price ($taxablePrice = 460.60)
                        $price = floatval($taxablePrice);
                        $selectedSubHsn = null;

                        foreach ($hsnMaster->subHsn as $sub) {
                            $fromRate = floatval($sub->from_sales_rate ?? 0);
                            $toRate = floatval($sub->to_sales_rate ?? 0);

                            if ($toRate == 0 || $toRate == null) {
                                if ($price >= $fromRate) {
                                    $selectedSubHsn = $sub;
                                    break;
                                }
                            } else {
                                if ($price >= $fromRate && $price <= $toRate) {
                                    $selectedSubHsn = $sub;
                                    break;
                                }
                            }
                        }

                        // ✅ Get tax from selected sub HSN
                        if ($selectedSubHsn && $selectedSubHsn->vat_tax_id) {
                            $vatTax = VatTax::find($selectedSubHsn->vat_tax_id);
                            if ($vatTax && $vatTax->percentage > 0) {
                                $taxPercentage = floatval($vatTax->percentage);
                                $inclusiveTax = \App\Services\Accounting\GSTPostingService::extractInclusiveTax($taxablePrice, $taxPercentage);
                                $taxAmount = $inclusiveTax['tax_amount'];
                                $vatTaxName = $vatTax->name;
                            }
                        }
                        // ✅ Fallback to master vat tax
                        elseif ($hsnMaster->vat_tax_id) {
                            $vatTax = $hsnMaster->vattax;
                            if ($vatTax && $vatTax->percentage > 0) {
                                $taxPercentage = floatval($vatTax->percentage);
                                $inclusiveTax = \App\Services\Accounting\GSTPostingService::extractInclusiveTax($taxablePrice, $taxPercentage);
                                $taxAmount = $inclusiveTax['tax_amount'];
                                $vatTaxName = $vatTax->name;
                            }
                        }
                    }
                }

                // ✅ If still no tax, try product's vatTaxes relation
                if ($taxPercentage == 0 && $product->vatTaxes) {
                    foreach ($product->vatTaxes as $tax) {
                        if ($tax->percentage > 0) {
                            $taxPercentage = floatval($tax->percentage);
                            $inclusiveTax = \App\Services\Accounting\GSTPostingService::extractInclusiveTax($taxablePrice, $taxPercentage);
                            $taxAmount = $inclusiveTax['tax_amount'];
                            $vatTaxName = $tax->name;
                            break;
                        }
                    }
                }

                // ✅ REMOVE dd() - Comment this out
                // dd([...]);

                // ✅ DEBUG LOG
                \Log::info('Cart Product Tax Calculation:', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'hsn_master_id' => $hsnMasterId,
                    'hsn_code' => $hsnCode,
                    'taxablePrice (variantMrp)' => $taxablePrice,
                    'variantPrice' => $variantPrice,
                    'variantMrp' => $variantMrp,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'vat_tax_name' => $vatTaxName,
                ]);

                // ✅ Calculate total sold
                $totalSold = $product->orders->sum('pivot.quantity');

                // ✅ Flash sale check
                $flashSale = $product->flashSales?->first();
                $flashSaleProduct = null;
                $quantity = null;

                if ($flashSale) {
                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();
                    $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;

                    if ($quantity == 0) {
                        $quantity = null;
                        $flashSaleProduct = null;
                    }
                }

                // ✅ Calculate discount percentage
                $discountPercentage = 0;
                if ($variantMrp > 0 && $variantPrice < $variantMrp) {
                    $discountPercentage = (($variantMrp - $variantPrice) / $variantMrp) * 100;
                }

                // ✅ CORRECT ORDER FOR CART DISPLAY
                // discount_price = Discounted Price (variantMrp = 460.60) - Main bold
                // price = MRP (variantPrice = 470.00) - Strikethrough
                $finalPrice = $variantMrp > 0 ? $variantMrp : $product->price;
                $finalMrp = $variantPrice > 0 ? $variantPrice : $product->price;

                $productArray[] = (object) [
                    'id' => $product->id,
                    'quantity' => (int) $cart->quantity,
                    'name' => $product->name,
                    'thumbnail' => $product->thumbnail,
                    'brand' => $product->brand?->name ?? null,
                    'price' => (float) number_format($finalMrp, 2, '.', ''),
                    'discount_price' => (float) number_format($finalPrice, 2, '.', ''),
                    'discount_percentage' => (float) number_format($discountPercentage, 2, '.', ''),
                    'rating' => (float) $product->averageRating,
                    'total_reviews' => (string) Number::abbreviate($product->reviews->count(), maxPrecision: 2),
                    'total_sold' => (string) number_format($totalSold, 0, '.', ','),
                    'color' => $color ? ColorResource::make($color) : null,
                    'color_name' => $colorName,
                    'size' => $size ? SizeResource::make($size) : null,
                    'size_name' => $sizeName,
                    'unit' => $cart->unit,
                    'inward_invoice_id' => $cart->inward_invoice_id,
                    'inward_product_id' => $cart->inward_product_id,
                    'hsn_code' => $hsnCode,
                    'tax_percentage' => (float) number_format($taxPercentage, 2, '.', ''),
                    'tax_amount' => (float) number_format($taxAmount, 2, '.', ''),
                    'vat_tax_name' => $vatTaxName,
                ];
            }

            if ($productArray->isEmpty()) {
                continue;
            }

            $shop = $products[0]?->shop;

            $lastOnline = $shop->last_online >= now() ? true : false;

            $shopWiseProducts[] = (object) [
                'shop_id' => $key,
                'shop_name' => $shop->name,
                'shop_logo' => $shop->logo,
                'shop_rating' => (float) $shop->averageRating,
                'shop_online' => $lastOnline,
                'products' => $productArray,
            ];
        }

        return [
            'total_items' => $totalItems,
            'shop_wise_products' => $shopWiseProducts,
            'info' => $info,
        ];
    }

    /**
     * Store or update cart by request.
     */
//    public static function storeOrUpdateByRequest(CartRequest $request, Product $product): Cart
//    {
//        $size = $request->size;
//        $color = $request->color;
//        $unit = $request->unit ?? $product->unit?->name;
//
//        $isBuyNow = $request->is_buy_now ?? false;
//
//        $customer = auth()->user()->customer;
//
//        $cart = $customer->carts()?->where('product_id', $product->id)->where('is_buy_now', $isBuyNow)->first();
//
//        if ($cart) {
//            $cart->update([
//                'quantity' => $isBuyNow ? 1 : $cart->quantity + 1,
//                'size' => $request->size ?? $cart->size,
//                'color' => $request->color ?? $cart->color,
//                'unit' => $request->unit ?? $cart->unit,
//            ]);
//
//            return $cart;
//        }
//
//        return self::create([
//            'product_id' => $request->product_id,
//            'shop_id' => $product->shop->id,
//            'is_buy_now' => $isBuyNow,
//            'customer_id' => $customer->id,
//            'quantity' => $request->quantity ?? 1,
//            'size' => $size,
//            'color' => $color,
//            'unit' => $unit,
//        ]);
//    }

// app/Repositories/CartRepository.php

    public static function storeOrUpdateByRequest(CartRequest $request, Product $product): Cart
    {
        $size = $request->size;
        $color = $request->color;
        $unit = $request->unit ?? $product->unit?->name;

        $isBuyNow = $request->is_buy_now ?? false;

        $customer = auth()->user()->customer;

        // ✅ Get variant prices from request
        $variantPrice = $request->price ?? 0;
        $variantMrp = $request->mrp ?? $product->mrp ?? $product->price ?? 0;
        $variantDiscount = $request->discount ?? 0;
        $inwardInvoiceId = $request->inward_invoice_id ?? null;
        $inwardProductId = $request->inward_product_id ?? null;

        // ✅ Find cart by product_id AND color AND size (unique variant)
        $cart = $customer->carts()
            ->where('product_id', $product->id)
            ->where('is_buy_now', $isBuyNow)
            ->where('color', $color)
            ->where('size', $size)
            ->first();

        if ($cart) {
            $cart->update([
                'quantity' => $isBuyNow ? 1 : $cart->quantity + 1,
                'size' => $size ?? $cart->size,
                'color' => $color ?? $cart->color,
                'unit' => $unit ?? $cart->unit,
                'price' => $variantPrice,
                'mrp' => $variantMrp,
                'discount' => $variantDiscount,
                'inward_invoice_id' => $inwardInvoiceId,  // ✅ Store
                'inward_product_id' => $inwardProductId,  // ✅ Store
            ]);

            return $cart;
        }

        return self::create([
            'product_id' => $request->product_id,
            'shop_id' => $product->shop->id,
            'is_buy_now' => $isBuyNow,
            'customer_id' => $customer->id,
            'quantity' => $request->quantity ?? 1,
            'size' => $size,
            'color' => $color,
            'unit' => $unit,
            'price' => $variantPrice,
            'mrp' => $variantMrp,
            'discount' => $variantDiscount,
            'inward_invoice_id' => $inwardInvoiceId,  // ✅ Store
            'inward_product_id' => $inwardProductId,  // ✅ Store
        ]);
    }
    public static function checkoutByRequest($request, $carts)
    {
        $totalAmount = 0;
        $deliveryCharge = 0;
        $couponDiscount = 0;
        $payableAmount = 0;

        $shopWiseTotalAmount = [];
        $totalOrderTaxAmount = 0;
        $vatTaxesArray = [];

        foreach ($carts ?? [] as $cart) {

            if (! $cart) {
                continue;
            }

            $product = $cart->product;
            $flashSale = $product->flashSales?->first();
            $flashSaleProduct = null;
            $quantity = null;

            $price = $product->discount_price > 0 ? $product->discount_price : $product->price;

            if ($flashSale) {
                $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();

                $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;

                if ($quantity == 0) {
                    $quantity = null;
                    $flashSaleProduct = null;
                } else {
                    $price = $flashSaleProduct->pivot->price;
                }
            }

            $sizePrice = $product->sizes()?->where('id', $cart->size)->first()?->pivot?->price ?? 0;
            $price = $price + $sizePrice;

            $colorPrice = $product->colors()?->where('id', $cart->color)->first()?->pivot?->price ?? 0;
            $price = $price + $colorPrice;

            // get shop wise total amount
            $shop = $product->shop;
            if (array_key_exists($shop->id, $shopWiseTotalAmount)) {
                $currentAmount = $shopWiseTotalAmount[$shop->id];
                $shopWiseTotalAmount[$shop->id] = $currentAmount + ($price * $cart->quantity);
            } else {
                $shopWiseTotalAmount[$shop->id] = $price * $cart->quantity;
            }

            $totalAmount += $price * $cart->quantity;
        }

        $groupCarts = $carts->groupBy('shop_id');

        // get delivery charge
        $deliveryCharge = 0;
        foreach ($groupCarts as $shopId => $shopCarts) {

            $productQty = 0;

            foreach ($shopCarts as $cart) {
                $productQty += $cart->quantity;
            }

            if ($productQty > 0) {
                $deliveryCharge += getDeliveryCharge($productQty);
            }
        }

        // generate array for get discount
        $products = collect([]);
        foreach ($carts as $cart) {
            $products->push([
                'id' => $cart->product_id,
                'quantity' => (int) $cart->quantity,
                'shop_id' => $cart->shop_id,
            ]);
        }
        $array = (object) [
            'coupon_code' => $request->coupon_code,
            'products' => $products,
        ];

        // get coupon discount
        $getDiscount = CouponRepository::getCouponDiscount($array);

        $couponDiscount = $getDiscount['discount_amount'];

        $payableAmount = $totalAmount + $deliveryCharge - $couponDiscount;

        // get order base tax
        $vatTaxes = VatTaxRepository::getActiveVatTaxes();

        foreach ($shopWiseTotalAmount as $shopId => $subtotal) {

            $thisFinalTax = [];

            foreach ($vatTaxes as $vatTax) {
                if ($vatTax->name && $vatTax->percentage > 0) {

                    $totalTaxAmount = round($subtotal * ($vatTax->percentage / 100), 2);

                    if (array_key_exists($vatTax->id, $thisFinalTax)) {
                        $currentAmount = $thisFinalTax[$vatTax->id];
                        $thisFinalTax[$vatTax->id] = $currentAmount + $totalTaxAmount;
                    } else {
                        $thisFinalTax[$vatTax->id] = $totalTaxAmount;
                    }
                    $totalOrderTaxAmount += $totalTaxAmount;
                }
            }

            $vatTaxesArray = $vatTaxes->map(function ($vatTax) use ($thisFinalTax) {
                return [
                    'id' => $vatTax->id,
                    'name' => $vatTax->name,
                    'percentage' => $vatTax->percentage,
                    'amount' => $thisFinalTax[$vatTax->id] ?? 0,
                ];
            })->toArray();
        }

        $payableAmount += $totalOrderTaxAmount;

        return [
            'total_amount' => (float) round($totalAmount, 2),
            'delivery_charge' => (float) round($deliveryCharge, 2),
            'coupon_discount' => (float) round($couponDiscount, 2),
            'order_tax_amount' => (float) round($totalOrderTaxAmount, 2),
            'payable_amount' => (float) round($payableAmount, 2),
            'all_vat_taxes' => $vatTaxesArray,
        ];
    }
}
