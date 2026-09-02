<?php

namespace App\Http\Controllers\Shop;

use App\Events\AdminProductRequestEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\InwardProduct;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\SubCategory;
use App\Models\User;
use App\Repositories\FlashSaleRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display the product list.
     */
    public function index(Request $request)
    {

        // get category, brand, color, search and sort from request
        $category = $request->category;
        $brand = $request->brand;
        $color = $request->color;
        $search = $request->search;
        $sort = $request->sort ?? 'latest';

        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');
        if (!$shop || ($shop->id == 1 && $shop->products()->count() == 0)) {
            $shop = \App\Models\Shop::whereHas('products')->first() ?? $shop;
        }

        $isSuperAdmin = auth()->user()?->hasRole('root') || auth()->user()?->hasRole('admin');

        $query = $shop?->products()->with([
            'designMaster.inwardProductDesign.colors',
            'designMaster.inwardProductDesign.sizes',
            'colors',
            'sizes',
            'barcodes'
        ]);

        if ($isSuperAdmin) {
            $query->where(function ($q) {
                $q->where('is_update_product', 1)
                    ->orWhere('is_online_product', 1);
            });
        } else {
            $query->where('is_update_product', 1)->where('is_online_product', 1);
        }

        // Backend sorting logic
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'stock_high':
                $query->orderBy('quantity', 'desc');
                break;
            case 'stock_low':
                $query->orderBy('quantity', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                break;
        }

        // filter products based on category, brand, color and search
        $products = $query->when($brand, function ($q) use ($brand) {
            return $q->where('brand_id', $brand);
        })->when($category, function ($q) use ($category) {
            return $q->whereHas('categories', function ($q) use ($category) {
                return $q->where('category_id', $category);
            });
        })->when($color, function ($q) use ($color) {
            return $q->whereHas('colors', function ($q) use ($color) {
                return $q->where('color_id', $color);
            });
        })->when($search, function ($q) use ($search) {
            return $q->where('name', 'like', "%$search%");
        })->paginate(20)->withQueryString();

        // get brands, colors and categories
        $brands = $rootShop?->brands()->get();
        $colors = $rootShop?->colors()->get();
        $categories = $rootShop?->categories()->get();

        $flashSale = FlashSaleRepository::getIncoming();

        return view('shop.product.index', compact('products', 'brands', 'colors', 'categories', 'flashSale'));
    }

    /**
     * Display the product details.
     */
    public function show(Product $product)
    {
        return view('shop.product.show', compact('product'));
    }

    /**
     * crete new product.
     */
    public function create()
    {
        $shop = generaleSetting('rootShop');

        // get brands, colors and categories
        $brands = $shop?->brands()->isActive()->get();
        $colors = $shop?->colors()->isActive()->get();
        $categories = $shop?->categories()->active()->get();
        $units = $shop?->units()->isActive()->get();
        $sizes = $shop?->sizes()->isActive()->get();

        return view('shop.product.create', compact('brands', 'colors', 'categories', 'units', 'sizes'));
    }

    /**
     * store new product.
     */
    public function store(ProductRequest $request)
    {
        $shop = generaleSetting('shop');

        $skuCode = $shop?->products()->where('code', $request->code)->exists();

        if ($skuCode) {
            return back()->withInput()->withErrors(['code' => __('Product code already exists!')])->with('error', __('Product code already exists!'));
        }

        ProductRepository::storeByRequest($request);

        /** @var User $user */
        $user = auth()->user();
        $isRootUser = $user?->hasRole('root');

        // admin notification message
        if (! $isRootUser && generaleSetting('setting')->shop_type != 'single') {
            $message = 'New product Created Request';
            try {
                AdminProductRequestEvent::dispatch($message);
            } catch (\Throwable $th) {
            }

            $data = (object) [
                'title' => $message,
                'content' => 'New product Created Request from '.$shop->name,
                'url' => '/admin/products?status=0',
                'icon' => 'bi-shop',
                'type' => 'success',
            ];
            // store notification
            NotificationRepository::storeByRequest($data);
        }

        return to_route('shop.product.index')->withSuccess(__('Product created successfully!'));
    }

    /**
     * Display the product edit form.
     */
//    public function edit(Product $product)
//    {
//        $shop = generaleSetting('shop');
//        $rootShop = generaleSetting('rootShop');
//
//        // get brands, colors, units, sizes and categories
//        $brands = $rootShop?->brands()->isActive()->get();
//        $colors = $rootShop?->colors()->isActive()->get();
//        $categories = $rootShop?->categories()->active()->get();
//        $units = $rootShop?->units()->isActive()->get();
//        $sizes = $rootShop?->sizes()->isActive()->get();
//
//        $categoryId = $product->categories()?->latest('id')->first()?->id;
//
//        $subCategories = SubCategory::whereHas('categories', function ($query) use ($categoryId) {
//            return $query->where('category_id', $categoryId);
//        })->isActive()->get();
//
//        $metaKeywords = explode(',', $product->meta_keywords) ?: [];
//
//        return view('shop.product.edit', compact('product', 'brands', 'colors', 'categories', 'units', 'sizes', 'subCategories', 'metaKeywords'));
//    }

    public function edit(Product $product)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $brands = $rootShop?->brands()->isActive()->get();
        $colors = $rootShop?->colors()->isActive()->get();
        $categories = $rootShop?->categories()->active()->get();
        $units = $rootShop?->units()->isActive()->get();
        $categoryId = $product->categories()?->latest('id')->first()?->id;

        // ✅ Inherit subcategories from linked Item Master if product has no subcategories saved yet
        $selectedSubCategoryIds = $product->subcategories?->pluck('id')->toArray() ?? [];

        $itemMaster = null;
        if ($product->design_master_id) {
            $srcId = InwardProduct::where('design_master_id', $product->design_master_id)->whereNotNull('product_id')->value('product_id');
            if ($srcId) {
                $itemMaster = Product::with('subcategories')->find($srcId);
            }
        }
        if (!$itemMaster) {
            $itemMaster = Product::where('is_item_master', 1)->where('name', $product->name)->with('subcategories')->first()
                ?? Product::where('is_item_master', 1)->where('design_master_id', $product->design_master_id)->with('subcategories')->first();
        }

        if (empty($selectedSubCategoryIds) && $itemMaster && $itemMaster->subcategories->isNotEmpty()) {
            $selectedSubCategoryIds = $itemMaster->subcategories->pluck('id')->toArray();
        }

        if (!$categoryId && $itemMaster) {
            $categoryId = $itemMaster->categories()?->latest('id')->first()?->id;
        }

        $subCategories = SubCategory::whereHas('categories', function ($query) use ($categoryId) {
            return $query->where('category_id', $categoryId);
        })->isActive()->get();

        $metaKeywords = explode(',', $product->meta_keywords ?? '') ?: [];

        // ✅ Fetch inward product data specifically belonging to this product / design master
        $inwardProductData = collect();

        if ($product->design_master_id) {
            $inwardProducts = InwardProduct::where('design_master_id', $product->design_master_id)
                ->with(['designMaster'])
                ->get();
        } else {
            $invoiceIds = $product->inward_invoice_ids ?? [];
            if (!empty($invoiceIds) && is_array($invoiceIds)) {
                $inwardProducts = InwardProduct::whereIn('inward_invoice_id', $invoiceIds)
                    ->where('product_id', $product->id)
                    ->with(['designMaster'])
                    ->get();
            } else {
                $inwardProducts = InwardProduct::where('product_id', $product->id)
                    ->with(['designMaster'])
                    ->get();
            }
        }

        if ($inwardProducts->isNotEmpty()) {
            foreach ($inwardProducts as $inwardProduct) {
                // Check if Sell Online is enabled and barcode exists for this specific inward product variant
                $hasBarcode = ProductBarcode::where('inward_product_id', $inwardProduct->id)->exists();
                $isOnline = (bool)($inwardProduct->is_online_product ?? false);

                if (!$hasBarcode || !$isOnline) {
                    continue;
                }
                // Get all colors
                $colorData = DB::table('inward_product_colors')
                    ->where('inward_product_id', $inwardProduct->id)
                    ->join('colors', 'inward_product_colors.color_id', '=', 'colors.id')
                    ->select('colors.name as color_name')
                    ->get();

                // Get all sizes
                $sizeData = DB::table('inward_product_sizes')
                    ->where('inward_product_id', $inwardProduct->id)
                    ->join('sizes', 'inward_product_sizes.size_id', '=', 'sizes.id')
                    ->select('sizes.name as size_name')
                    ->get();

                // Combine colors and sizes with comma
                $colorNames = $colorData->pluck('color_name')->implode(', ');
                if (empty($colorNames)) {
                    $colorNames = 'N/A';
                }

                $sizeNames = $sizeData->pluck('size_name')->implode(', ');
                if (empty($sizeNames)) {
                    $sizeNames = 'N/A';
                }

                $sourceProduct = Product::find($inwardProduct->product_id);
                $itemName = $sourceProduct->name ?? $product->name ?? 'Product';

                $inwardProductData->push((object) [
                    'item_name' => $itemName,
                    'design_no' => $inwardProduct->designMaster->design_number ?? 'N/A',
                    'colors' => $colorNames,
                    'sizes' => $sizeNames,
                    'qty' => $inwardProduct->quantity,
                    'purc_rate' => $inwardProduct->buy_price,
                    'amount' => $inwardProduct->buy_price * $inwardProduct->quantity,
                    'disc_percent' => $inwardProduct->discount_price,
                    'mrp' => $inwardProduct->mrp,
                ]);
            }
        }

        $sizes = $rootShop?->sizes()->isActive()->get();

        return view('shop.product.edit', compact(
            'product',
            'brands',
            'colors',
            'categories',
            'units',
            'sizes',
            'subCategories',
            'selectedSubCategoryIds',
            'metaKeywords',
            'inwardProductData'
        ));
    }

    /**
     * Update the product.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $shop = generaleSetting('shop');

        $skuCode = $shop?->products()->where('code', $request->code)->where('id', '!=', $product->id)->exists();

        if ($skuCode) {
            return back()->withInput()->withErrors(['code' => __('Product code already exists!')])->with('error', __('Product code already exists!'));
        }

        ProductRepository::updateByRequest($request, $product);

        /** @var User $user */
        $user = auth()->user();
        $isRootUser = $user?->hasRole('root');

        // admin notification message
        if (! $isRootUser && generaleSetting('setting')->shop_type != 'single') {
            $message = 'Product Updated Request';
            try {
                AdminProductRequestEvent::dispatch($message);
            } catch (\Throwable $th) {
            }

            $data = (object) [
                'title' => $message,
                'content' => 'Product Updated Request from '.$shop->name,
                'url' => '/admin/products?status=1',
                'icon' => 'bi-shop',
                'type' => 'success',
            ];
            // store notification
            NotificationRepository::storeByRequest($data);
        }

        return to_route('shop.product.index')->withSuccess(__('Product updated successfully!'));
    }

    /**
     * delete thumbnail
     */
    public function thumbnailDestroy(Product $product, Media $media)
    {
        $product->medias()->detach($media->id);
        if (Storage::exists($media->src)) {
            Storage::delete($media->src);
        }

        $media->delete();

        return back()->withSuccess(__('Thumbnail deleted successfully!'));
    }

    /**
     * status toggle a product
     */
    public function statusToggle(Product $product)
    {
        if (! $product->is_approve) {
            return back()->withError(__('Sorry! Your Product is not approved yet!'));
        }

        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * generate barcode
     */
    public function generateBarcode(Product $product)
    {
        if (! $product->code) {
            return back()->withError(__('Sorry! Your Product code is not generated yet!'));
        }

        $quantities = request('qty', 4);

        return view('shop.product.barcode', compact('product', 'quantities'));
    }
}
