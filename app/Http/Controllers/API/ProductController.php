<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddFavoriteRequest;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ColorResource;
use App\Http\Resources\ProductDetailsResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\SizeResource;
use App\Models\FlashSale;
use App\Models\InwardProduct;
use App\Repositories\ProductRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Retrieve a paginated list of products based on the provided request parameters.
     *
     * @param  Request  $request  The request object containing page, per_page, and search parameters
     * @return Some_Return_Value The JSON response containing total and products data
     */
    public function index(Request $request)
    {
        $page = $request->page;
        $perPage = $request->per_page;
        $skip = ($page * $perPage) - $perPage;

        $search = $request->search;
        $shopID = $request->shop_id;
        $categoryID = $request->category_id;
        $subCategoryID = $request->sub_category_id;

        $rating = $request->rating; // 4.0
        $sortType = $request->sort_type;
        $minPrice = $request->min_price;
        $maxPrice = $request->max_price;
        $brandID = $request->brand_id;
        $colorID = $request->color_id;
        $sizeID = $request->size_id;

        $generaleSetting = generaleSetting('setting');
        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = generaleSetting('rootShop');
        }

        // get data for
        $rootShop = $shop ?? generaleSetting('rootShop');
        $productQuery = ProductRepository::query()->where('is_online_product', 1)->where('is_update_product', 1)->when($shop, function ($query) use ($shop) {
            return $query->where('shop_id', $shop->id);
        })->isActive();

        $flashSale = FlashSale::isActive()->first();
        $flashSaleMinPrice = $flashSale ? $flashSale->products->min('pivot.price') : null;

        $productMinPrice = $productQuery->min('price');
        if ($flashSaleMinPrice && $flashSaleMinPrice < $productMinPrice) {
            $productMinPrice = $flashSaleMinPrice;
        }

        $productMaxPrice = $productQuery->max('price');

        $inwardMinPrice = InwardProduct::where('is_active', 1)->where('mrp', '>', 0)->min('mrp');
        $inwardMaxPrice = InwardProduct::where('is_active', 1)->max('mrp');

        if ($inwardMinPrice && ($productMinPrice == 0 || $inwardMinPrice < $productMinPrice)) {
            $productMinPrice = $inwardMinPrice;
        }
        if ($inwardMaxPrice && $inwardMaxPrice > $productMaxPrice) {
            $productMaxPrice = $inwardMaxPrice;
        }

        $sizes = $rootShop?->sizes()->isActive()->get();
        $colors = $rootShop?->colors()->isActive()->get();
        $brands = $rootShop?->brands()->isActive()->get();

        // filter query
        $products = ProductRepository::query()
            ->where('is_online_product', 1)
            ->where('is_update_product', 1)
            ->withSum('orders as orders_count', 'order_products.quantity')
            ->withAvg('reviews as average_rating', 'rating')
            ->isActive()
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })->when($shopID && ! $shop, function ($query) use ($shopID) {
                return $query->where('shop_id', $shopID);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('short_description', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })->when($brandID, function ($query) use ($brandID) {
                return $query->where('brand_id', $brandID);
            })->when($colorID, function ($query) use ($colorID) {
                return $query->where(function ($q) use ($colorID) {
                    $q->whereHas('colors', function ($c) use ($colorID) {
                        $c->where('id', $colorID);
                    })->orWhereIn('id', function ($q2) use ($colorID) {
                        $q2->select('product_id')
                            ->from('inward_products')
                            ->join('inward_product_colors', 'inward_products.id', '=', 'inward_product_colors.inward_product_id')
                            ->where('inward_product_colors.color_id', $colorID)
                            ->whereNotNull('product_id');
                    })->orWhereIn('design_master_id', function ($q2) use ($colorID) {
                        $q2->select('design_master_id')
                            ->from('inward_products')
                            ->join('inward_product_colors', 'inward_products.id', '=', 'inward_product_colors.inward_product_id')
                            ->where('inward_product_colors.color_id', $colorID)
                            ->whereNotNull('design_master_id');
                    });
                });
            })->when($sizeID, function ($query) use ($sizeID) {
                return $query->where(function ($q) use ($sizeID) {
                    $q->whereHas('sizes', function ($s) use ($sizeID) {
                        $s->where('id', $sizeID);
                    })->orWhereIn('id', function ($q2) use ($sizeID) {
                        $q2->select('product_id')
                            ->from('inward_products')
                            ->join('inward_product_sizes', 'inward_products.id', '=', 'inward_product_sizes.inward_product_id')
                            ->where('inward_product_sizes.size_id', $sizeID)
                            ->whereNotNull('product_id');
                    })->orWhereIn('design_master_id', function ($q2) use ($sizeID) {
                        $q2->select('design_master_id')
                            ->from('inward_products')
                            ->join('inward_product_sizes', 'inward_products.id', '=', 'inward_product_sizes.inward_product_id')
                            ->where('inward_product_sizes.size_id', $sizeID)
                            ->whereNotNull('design_master_id');
                    });
                });
            })->when($categoryID, function ($query) use ($categoryID) {
                return $query->whereHas('categories', function ($query) use ($categoryID) {
                    return $query->where('id', $categoryID);
                });
            })->when($subCategoryID, function ($query) use ($subCategoryID) {
                $query->whereHas('subcategories', function ($query) use ($subCategoryID) {
                    return $query->where('id', $subCategoryID);
                });
            })->when($rating, function ($query) use ($rating) {
                $ratingValue = floatval($rating);
                $upperBound = $ratingValue + 1;

                return $query->havingRaw('average_rating >= '.$rating.' AND average_rating < '.$upperBound);
            })->when($sortType == 'top_selling', function ($query) {
                return $query->orderByDesc('orders_count');
            })->when($sortType == 'popular_product', function ($query) {
                return $query->orderByDesc('orders_count')->orderByDesc('average_rating');
            })->when($sortType == 'newest' || $sortType == 'just_for_you' || $sortType == 'new_arrivals', function ($query) {
                return $query->orderBy('id', 'desc');
            })->when($minPrice || $maxPrice, function ($query) use ($minPrice, $maxPrice) {
                $query->whereRaw('
                    COALESCE(
                        (SELECT flash_sale_products.price
                         FROM flash_sale_products
                         INNER JOIN flash_sales ON flash_sales.id = flash_sale_products.flash_sale_id
                         WHERE flash_sale_products.product_id = products.id
                         AND flash_sale_products.quantity > 0
                         AND flash_sales.status = 1
                         AND flash_sales.start_date <= CURDATE()
                         AND flash_sales.end_date >= CURDATE()
                         AND (flash_sales.start_time <= CURTIME() OR flash_sales.end_time >= CURTIME())
                         ORDER BY flash_sale_products.price ASC LIMIT 1
                        ),
                        (SELECT inward_products.mrp
                         FROM inward_products
                         WHERE inward_products.design_master_id = products.design_master_id
                         AND inward_products.is_active = 1
                         ORDER BY inward_products.id ASC LIMIT 1
                        ),
                        IF(discount_price > 0, discount_price, price)
                    ) BETWEEN ? AND ?
                ', [$minPrice ?? 0, $maxPrice ?? PHP_INT_MAX]);
            })
            ->when(in_array($sortType, ['high_to_low', 'low_to_high']), function ($query) use ($sortType) {
                $order = $sortType === 'high_to_low' ? 'DESC' : 'ASC';

                return $query->orderByRaw("
                    COALESCE(
                        (SELECT flash_sale_products.price
                         FROM flash_sale_products
                         INNER JOIN flash_sales ON flash_sales.id = flash_sale_products.flash_sale_id
                         WHERE flash_sale_products.product_id = products.id
                         AND flash_sale_products.quantity > 0
                         AND flash_sales.status = 1
                         AND flash_sales.start_date <= CURDATE()
                         AND flash_sales.end_date >= CURDATE()
                         AND (flash_sales.start_time <= CURTIME() OR flash_sales.end_time >= CURTIME())
                         ORDER BY flash_sale_products.price $order LIMIT 1
                        ),
                        (SELECT inward_products.mrp
                         FROM inward_products
                         WHERE inward_products.design_master_id = products.design_master_id
                         AND inward_products.is_active = 1
                         ORDER BY inward_products.id ASC LIMIT 1
                        ),
                        IF(discount_price > 0, discount_price, price)
                    ) $order
                ")->orderByDesc('id');
            });

        $total = $products->count();
        $products = $products->when($perPage && $page, function ($query) use ($perPage, $skip) {
            return $query->skip($skip)->take($perPage);
        })->get();

        return $this->json('products', [
            'total' => $total,
            'products' => ProductResource::collection($products),
            'filters' => [
                'sizes' => $sizes ? SizeResource::collection($sizes) : [],
                'colors' => $colors ? ColorResource::collection($colors) : [],
                'brands' => $brands ? BrandResource::collection($brands) : [],
                'min_price' => (int) intval($productMinPrice),
                'max_price' => (int) intval($productMaxPrice),
            ],
        ]);
    }

    /**
     * Show the product details.
     *
     * @param  datatype  $id  description
     * @return response
     */
//    public function show(Request $request)
//    {
//        $request->validate([
//            'product_id' => 'required|exists:products,id',
//        ]);
//
//        $product = ProductRepository::find($request->product_id);
//        ProductRepository::recentView($product);
//
//        $relatedProducts = ProductRepository::query()->whereHas('categories', function ($query) use ($product) {
//            $query->whereIn('categories.id', $product->categories->pluck('id'));
//        })->where('id', '!=', $product->id)
//            ->isActive()
//            ->inRandomOrder()
//            ->limit(6)->get();
//
//        $shop = $product->shop;
//
//        $popularProducts = $shop->products()->isActive()->where('id', '!=', $product->id)->withCount('orders')->withAvg('reviews as average_rating', 'rating')->orderByDesc('average_rating')->orderByDesc('orders_count')->take(6)->get();
//
//        return $this->json('product details', [
//            'product' => ProductDetailsResource::make($product),
//            'related_products' => ProductResource::collection($relatedProducts),
//            'popular_products' => ProductResource::collection($popularProducts),
//        ]);
//    }

    // app/Http/Controllers/API/ProductController.php

//    public function show(Request $request)
//    {
//        $request->validate([
//            'product_id' => 'required|exists:products,id',
//        ]);
//
//        $product = ProductRepository::find($request->product_id);
//        ProductRepository::recentView($product);
//
//        $relatedProducts = ProductRepository::query()->whereHas('categories', function ($query) use ($product) {
//            $query->whereIn('categories.id', $product->categories->pluck('id'));
//        })->where('id', '!=', $product->id)
//            ->isActive()
//            ->inRandomOrder()
//            ->limit(6)->get();
//
//        $shop = $product->shop;
//
//        $popularProducts = $shop->products()->isActive()->where('id', '!=', $product->id)->withCount('orders')->withAvg('reviews as average_rating', 'rating')->orderByDesc('average_rating')->orderByDesc('orders_count')->take(6)->get();
//
//        // ✅ Fetch inward product data with colors and sizes
//        $colorVariants = collect();
//        $sizeOnlyVariants = collect();
//
//        // ✅ For simple products (no variants) - store inward data
//        $inwardInvoiceId = null;
//        $inwardProductId = null;
//
//        if ($product->inward_invoice_ids) {
//            $invoiceIds = $product->inward_invoice_ids;
//
//            if (is_string($invoiceIds)) {
//                $invoiceIds = json_decode($invoiceIds, true) ?? [];
//            }
//
//            if (is_array($invoiceIds) && !empty($invoiceIds)) {
//                $inwardProducts = InwardProduct::whereIn('inward_invoice_id', $invoiceIds)
//                    ->with(['designMaster'])
//                    ->get();
//
//                // ✅ Store inward data for simple product (first inward product)
//                if ($inwardProducts->isNotEmpty()) {
//                    $firstInward = $inwardProducts->first();
//                    $inwardInvoiceId = $firstInward->inward_invoice_id;
//                    $inwardProductId = $firstInward->id;
//                }
//
//                $groupedByColor = [];
//                $hasValidColors = false;
//                $hasValidSizes = false;
//
//                foreach ($inwardProducts as $inwardProduct) {
//                    $colorData = DB::table('inward_product_colors')
//                        ->where('inward_product_id', $inwardProduct->id)
//                        ->join('colors', 'inward_product_colors.color_id', '=', 'colors.id')
//                        ->select('colors.id', 'colors.name', 'colors.color_code')
//                        ->get();
//
//                    $sizeData = DB::table('inward_product_sizes')
//                        ->where('inward_product_id', $inwardProduct->id)
//                        ->join('sizes', 'inward_product_sizes.size_id', '=', 'sizes.id')
//                        ->select('sizes.id', 'sizes.name')
//                        ->get();
//
//                    // ✅ Check if valid colors exist (not N/A)
//                    $validColors = $colorData->filter(function($color) {
//                        return $color->name !== 'N/A' && $color->name !== 'NA';
//                    });
//
//                    // ✅ Check if valid sizes exist (not N/A)
//                    $validSizes = $sizeData->filter(function($size) {
//                        return $size->name !== 'N/A' && $size->name !== 'NA';
//                    });
//
//                    if ($validColors->isNotEmpty()) {
//                        $hasValidColors = true;
//                    }
//
//                    if ($validSizes->isNotEmpty()) {
//                        $hasValidSizes = true;
//                    }
//
//                    // ✅ If NO valid colors AND NO valid sizes - skip (no variants)
//                    if (!$hasValidColors && !$hasValidSizes) {
//                        // ✅ For simple product, store inward data from this inward product
//                        $inwardInvoiceId = $inwardProduct->inward_invoice_id;
//                        $inwardProductId = $inwardProduct->id;
//                        continue;
//                    }
//
//                    // ✅ If ONLY valid sizes exist (no colors)
//                    if (!$hasValidColors && $hasValidSizes) {
//                        foreach ($validSizes as $size) {
//                            $sizeOnlyVariants->push([
//                                'id' => $size->id,
//                                'name' => $size->name,
//                                'mrp' => (float) number_format($inwardProduct->mrp, 2, '.', ''),
//                                'purc_rate' => (float) number_format($inwardProduct->buy_price, 2, '.', ''),
//                                'discount_percent' => (float) $inwardProduct->discount_price,
//                                'qty' => $inwardProduct->quantity,
//                                'inward_invoice_id' => $inwardProduct->inward_invoice_id,
//                                'inward_product_id' => $inwardProduct->id,
//                            ]);
//                        }
//                        continue;
//                    }
//
//                    // ✅ If valid colors exist, process with colors
//                    $colorData = $validColors->isNotEmpty() ? $validColors : collect([(object) ['id' => null, 'name' => 'N/A', 'color_code' => '#ccc']]);
//                    $sizeData = $validSizes->isNotEmpty() ? $validSizes : collect([(object) ['id' => null, 'name' => 'N/A']]);
//
//                    foreach ($colorData as $color) {
//                        $colorKey = $color->id ?? 'na';
//
//                        if (!isset($groupedByColor[$colorKey])) {
//                            $groupedByColor[$colorKey] = [
//                                'color_id' => $color->id,
//                                'color_name' => $color->name,
//                                'color_code' => $color->color_code,
//                                'sizes' => [],
//                                'size_details' => [],
//                                'mrp' => $inwardProduct->mrp,
//                                'purc_rate' => $inwardProduct->buy_price,
//                                'discount_percent' => $inwardProduct->discount_price,
//                                'qty' => 0,
//                                'inward_invoice_id' => $inwardProduct->inward_invoice_id,
//                                'inward_product_id' => $inwardProduct->id,
//                            ];
//                        }
//
//                        foreach ($sizeData as $size) {
//                            // Skip N/A sizes if colors exist
//                            if ($size->name === 'N/A' || $size->name === 'NA') {
//                                continue;
//                            }
//
//                            $sizeExists = false;
//                            foreach ($groupedByColor[$colorKey]['size_details'] as $existingSize) {
//                                if ($existingSize['id'] == $size->id) {
//                                    $sizeExists = true;
//                                    break;
//                                }
//                            }
//
//                            if (!$sizeExists) {
//                                $groupedByColor[$colorKey]['size_details'][] = [
//                                    'id' => $size->id,
//                                    'name' => $size->name,
//                                ];
//                                $groupedByColor[$colorKey]['sizes'][] = $size->name;
//                            }
//                        }
//
//                        $groupedByColor[$colorKey]['qty'] += $inwardProduct->quantity;
//                    }
//                }
//
//                // Convert grouped colors to collection - skip N/A colors
//                foreach ($groupedByColor as $color) {
//                    // Skip if color is N/A
//                    if ($color['color_name'] === 'N/A' || $color['color_name'] === 'NA') {
//                        continue;
//                    }
//
//                    $colorVariants->push([
//                        'id' => $color['color_id'],
//                        'name' => $color['color_name'],
//                        'color_code' => $color['color_code'],
//                        'mrp' => (float) number_format($color['mrp'], 2, '.', ''),
//                        'purc_rate' => (float) number_format($color['purc_rate'], 2, '.', ''),
//                        'discount_percent' => (float) $color['discount_percent'],
//                        'qty' => $color['qty'],
//                        'sizes' => $color['size_details'],
//                        'inward_invoice_id' => $color['inward_invoice_id'] ?? null,
//                        'inward_product_id' => $color['inward_product_id'] ?? null,
//                    ]);
//                }
//            }
//        }
//
//        return $this->json('product details', [
//            'product' => ProductDetailsResource::make($product),
//            'related_products' => ProductResource::collection($relatedProducts),
//            'popular_products' => ProductResource::collection($popularProducts),
//            'color_variants' => $colorVariants,
//            'size_only_variants' => $sizeOnlyVariants,
//            // ✅ Add simple product inward data
//            'inward_invoice_id' => $inwardInvoiceId,
//            'inward_product_id' => $inwardProductId,
//        ]);
//    }
    public function show(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = ProductRepository::find($request->product_id);
        ProductRepository::recentView($product);

        $relatedProducts = ProductRepository::query()->where('is_online_product', 1)->where('is_update_product', 1)->whereHas('categories', function ($query) use ($product) {
            $query->whereIn('categories.id', $product->categories->pluck('id'));
        })->where('id', '!=', $product->id)
            ->isActive()
            ->inRandomOrder()
            ->limit(6)->get();

        $shop = $product->shop;

        $popularProducts = $shop->products()->where('is_online_product', 1)->where('is_update_product', 1)->isActive()->where('id', '!=', $product->id)->withCount('orders')->withAvg('reviews as average_rating', 'rating')->orderByDesc('average_rating')->orderByDesc('orders_count')->take(6)->get();

        // ✅ Fetch inward product data with colors and sizes
        $colorVariants = collect();
        $sizeOnlyVariants = collect();

        // ✅ For simple products (no variants) - store inward data
        $inwardInvoiceId = null;
        $inwardProductId = null;
        $inwardData = null;

        if ($product->inward_invoice_ids) {
            $invoiceIds = $product->inward_invoice_ids;

            if (is_string($invoiceIds)) {
                $invoiceIds = json_decode($invoiceIds, true) ?? [];
            }

            if (is_array($invoiceIds) && !empty($invoiceIds)) {
                $inwardProducts = InwardProduct::whereIn('inward_invoice_id', $invoiceIds)
                    ->with(['designMaster'])
                    ->get();

                // ✅ Store inward data for simple product (first inward product)
                if ($inwardProducts->isNotEmpty()) {
                    $firstInward = $inwardProducts->first();
                    $inwardInvoiceId = $firstInward->inward_invoice_id;
                    $inwardProductId = $firstInward->id;

                    // ✅ Store full inward data for simple products
                    $inwardData = [
                        'price' => $firstInward->price,
                        'mrp' => $firstInward->mrp,
                        'buy_price' => $firstInward->buy_price,
                        'discount_price' => $firstInward->discount_price,
                        'quantity' => $firstInward->quantity,
                        'inward_invoice_id' => $firstInward->inward_invoice_id,
                        'inward_product_id' => $firstInward->id,
                    ];
                }

                $groupedByColor = [];
                $hasValidColors = false;
                $hasValidSizes = false;

                foreach ($inwardProducts as $inwardProduct) {
                    $colorData = DB::table('inward_product_colors')
                        ->where('inward_product_id', $inwardProduct->id)
                        ->join('colors', 'inward_product_colors.color_id', '=', 'colors.id')
                        ->select('colors.id', 'colors.name', 'colors.color_code')
                        ->get();

                    $sizeData = DB::table('inward_product_sizes')
                        ->where('inward_product_id', $inwardProduct->id)
                        ->join('sizes', 'inward_product_sizes.size_id', '=', 'sizes.id')
                        ->select('sizes.id', 'sizes.name')
                        ->get();

                    // ✅ Check if valid colors exist (not N/A)
                    $validColors = $colorData->filter(function($color) {
                        return $color->name !== 'N/A' && $color->name !== 'NA';
                    });

                    // ✅ Check if valid sizes exist (not N/A)
                    $validSizes = $sizeData->filter(function($size) {
                        return $size->name !== 'N/A' && $size->name !== 'NA';
                    });

                    if ($validColors->isNotEmpty()) {
                        $hasValidColors = true;
                    }

                    if ($validSizes->isNotEmpty()) {
                        $hasValidSizes = true;
                    }

                    // ✅ If NO valid colors AND NO valid sizes - skip (no variants)
                    if (!$hasValidColors && !$hasValidSizes) {
                        // ✅ For simple product, store inward data from this inward product
                        $inwardInvoiceId = $inwardProduct->inward_invoice_id;
                        $inwardProductId = $inwardProduct->id;
                        $inwardData = [
                            'price' => $inwardProduct->price,
                            'mrp' => $inwardProduct->mrp,
                            'buy_price' => $inwardProduct->buy_price,
                            'discount_price' => $inwardProduct->discount_price,
                            'quantity' => $inwardProduct->quantity,
                            'inward_invoice_id' => $inwardProduct->inward_invoice_id,
                            'inward_product_id' => $inwardProduct->id,
                        ];
                        continue;
                    }

                    // ✅ If ONLY valid sizes exist (no colors)
                    if (!$hasValidColors && $hasValidSizes) {
                        foreach ($validSizes as $size) {
                            $sizeOnlyVariants->push([
                                'id' => $size->id,
                                'name' => $size->name,
                                'mrp' => (float) number_format($inwardProduct->mrp, 2, '.', ''),
                                'purc_rate' => (float) number_format($inwardProduct->buy_price, 2, '.', ''),
                                'discount_percent' => (float) $inwardProduct->discount_price,
                                'qty' => $inwardProduct->quantity,
                                'inward_invoice_id' => $inwardProduct->inward_invoice_id,
                                'inward_product_id' => $inwardProduct->id,
                            ]);
                        }
                        continue;
                    }

                    // ✅ If valid colors exist, process with colors
                    $colorData = $validColors->isNotEmpty() ? $validColors : collect([(object) ['id' => null, 'name' => 'N/A', 'color_code' => '#ccc']]);
                    $sizeData = $validSizes->isNotEmpty() ? $validSizes : collect([(object) ['id' => null, 'name' => 'N/A']]);

                    foreach ($colorData as $color) {
                        $colorKey = $color->id ?? 'na';

                        if (!isset($groupedByColor[$colorKey])) {
                            $groupedByColor[$colorKey] = [
                                'color_id' => $color->id,
                                'color_name' => $color->name,
                                'color_code' => $color->color_code,
                                'sizes' => [],
                                'size_details' => [],
                                'mrp' => $inwardProduct->mrp,
                                'purc_rate' => $inwardProduct->buy_price,
                                'discount_percent' => $inwardProduct->discount_price,
                                'qty' => 0,
                                'inward_invoice_id' => $inwardProduct->inward_invoice_id,
                                'inward_product_id' => $inwardProduct->id,
                            ];
                        }

                        foreach ($sizeData as $size) {
                            // Skip N/A sizes if colors exist
                            if ($size->name === 'N/A' || $size->name === 'NA') {
                                continue;
                            }

                            $sizeExists = false;
                            foreach ($groupedByColor[$colorKey]['size_details'] as $existingSize) {
                                if ($existingSize['id'] == $size->id) {
                                    $sizeExists = true;
                                    break;
                                }
                            }

                            if (!$sizeExists) {
//                                $groupedByColor[$colorKey]['size_details'][] = [
//                                    'id' => $size->id,
//                                    'name' => $size->name,
//                                ];

                                $groupedByColor[$colorKey]['size_details'][] = [
                                    'id' => $size->id,
                                    'name' => $size->name,
                                    'mrp' => (float) number_format($inwardProduct->mrp, 2, '.', ''),
                                    'purc_rate' => (float) number_format($inwardProduct->buy_price, 2, '.', ''),
                                    'discount_percent' => (float) $inwardProduct->discount_price,
                                    'qty' => (int) $inwardProduct->quantity,
                                    'inward_invoice_id' => $inwardProduct->inward_invoice_id,
                                    'inward_product_id' => $inwardProduct->id,
                                ];
                                $groupedByColor[$colorKey]['sizes'][] = $size->name;
                            }
                        }

                        $groupedByColor[$colorKey]['qty'] += $inwardProduct->quantity;
                    }
                }

                // Convert grouped colors to collection - skip N/A colors
                foreach ($groupedByColor as $color) {
                    // Skip if color is N/A
                    if ($color['color_name'] === 'N/A' || $color['color_name'] === 'NA') {
                        continue;
                    }

                    $colorVariants->push([
                        'id' => $color['color_id'],
                        'name' => $color['color_name'],
                        'color_code' => $color['color_code'],
                        'mrp' => (float) number_format($color['mrp'], 2, '.', ''),
                        'purc_rate' => (float) number_format($color['purc_rate'], 2, '.', ''),
                        'discount_percent' => (float) $color['discount_percent'],
                        'qty' => $color['qty'],
                        'sizes' => $color['size_details'],
                        'inward_invoice_id' => $color['inward_invoice_id'] ?? null,
                        'inward_product_id' => $color['inward_product_id'] ?? null,
                    ]);
                }
            }
        }

        return $this->json('product details', [
            'product' => ProductDetailsResource::make($product),
            'related_products' => ProductResource::collection($relatedProducts),
            'popular_products' => ProductResource::collection($popularProducts),
            'color_variants' => $colorVariants,
            'size_only_variants' => $sizeOnlyVariants,
            // ✅ Add simple product inward data
            'inward_invoice_id' => $inwardInvoiceId,
            'inward_product_id' => $inwardProductId,
            'inward_data' => $inwardData, // ✅ Full inward data for simple products
        ]);
    }
    /**
     * Add or remove favorite product for the user.
     *
     * @param  AddFavoriteRequest  $request  The request for adding a favorite.
     * @return json Response with favorite updated successfully
     */
    public function addFavorite(AddFavoriteRequest $request)
    {
        $product = ProductRepository::find($request->product_id);

        auth()->user()?->customer->favorites()->toggle($product->id);

        return $this->json('favorite updated successfully', [
            'product' => ProductResource::make($product),
        ]);
    }

    /**
     * get list of favorite products.
     *
     * @return json Response
     */
    public function favoriteProducts()
    {
        $products = auth()->user()->customer->favorites;

        return $this->json('favorite products', [
            'products' => ProductResource::collection($products),
        ]);
    }

    /**
     * Store a new review.
     *
     * @param  ReviewRequest  $request  The review request
     * @return json Response
     */
    public function storeReview(ReviewRequest $request)
    {
        $product = ProductRepository::find($request->product_id);

        $hasReview = $product->reviews()->where('customer_id', auth()->user()->customer->id)->where('order_id', $request->order_id)->first();

        if ($hasReview) {
            return $this->json('review already exists', [
                'review' => ReviewResource::make($hasReview),
            ]);
        }

        $review = ReviewRepository::storeByRequest($request, $product);

        return $this->json('review added successfully', [
            'review' => ReviewResource::make($review),
        ]);
    }
}
