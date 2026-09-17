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
use App\Services\AI\GeminiContentService;
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
        $categories = $rootShop?->categories()->active()->inHero()->get();

        $flashSale = FlashSaleRepository::getIncoming();

        return view('shop.product.index', compact('products', 'brands', 'colors', 'categories', 'flashSale'));
    }

    /**
     * Display the product details.
     */
    public function show(Product $product)
    {
        // Auto-activate product status after view product by shop admin
        if (! $product->is_active || ! $product->is_approve) {
            $product->update([
                'is_active' => true,
                'is_approve' => true,
            ]);
            $product->refresh();
        }

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
        $categories = $shop?->categories()->active()->inHero()->get();
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
        $categories = $rootShop?->categories()->active()->inHero()->get();
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
                // Check if barcode exists for this specific inward product variant
                $hasBarcode = ProductBarcode::where('inward_product_id', $inwardProduct->id)->exists();

                if (!$hasBarcode) {
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
                    'inward_product_id' => $inwardProduct->id,
                    'item_name' => $itemName,
                    'design_no' => $inwardProduct->designMaster->design_number ?? 'N/A',
                    'colors' => $colorNames,
                    'sizes' => $sizeNames,
                    'qty' => $inwardProduct->quantity,
                    'purc_rate' => $inwardProduct->buy_price,
                    'amount' => $inwardProduct->buy_price * $inwardProduct->quantity,
                    'disc_percent' => $inwardProduct->discount_price,
                    'mrp' => $inwardProduct->mrp,
                    'online_discount_percent' => (float) ($inwardProduct->online_discount_percent ?? $product->online_discount_percent ?? 0),
                    'is_online_product' => (bool) ($inwardProduct->is_online_product ?? false),
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
        $product->update([
            'is_approve' => true,
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

    /**
     * Generate product descriptions using Google Gemini AI.
     */
    public function aiGenerateContent(Request $request, GeminiContentService $geminiService)
    {
        $request->validate([
            'product_name' => ['nullable', 'string', 'max:500'],
            'url' => ['nullable', 'string', 'max:10000'],
            'keywords' => ['nullable', 'string', 'max:10000'],
            'tone' => ['nullable', 'string', 'max:100'],
        ]);

        if (!empty($request->url) && !filter_var($request->url, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//i', $request->url)) {
            return response()->json([
                'success' => false,
                'message' => __('Please provide a valid website URL starting with http:// or https://'),
            ], 422);
        }

        if (empty($request->url) && empty($request->keywords) && empty($request->product_name)) {
            return response()->json([
                'success' => false,
                'message' => __('Please provide at least a Product Name, reference URL, or keywords/highlights.'),
            ], 422);
        }

        $shop = auth()->user()?->shop ?? generaleSetting('shop');
        $apiKey = $geminiService->resolveApiKey($shop);

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => __('Google Gemini API key is missing. Please configure it in Store Profile / Settings first.'),
                'config_url' => route('shop.profile.edit'),
            ], 422);
        }

        $model = $geminiService->resolveModel($shop);

        $result = $geminiService->generateContent(
            $apiKey,
            $request->product_name,
            $request->url,
            $request->keywords,
            [
                'tone' => $request->tone,
                'model' => $model,
            ]
        );

        if (!($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? __('Failed to generate content with Gemini AI.'),
            ], 400);
        }

        $shortDesc = trim(strip_tags($result['short_description'] ?? ''));
        if (mb_strlen($shortDesc) > 191) {
            $shortDesc = mb_substr($shortDesc, 0, 191);
        }

        return response()->json([
            'success' => true,
            'meta_title' => $result['meta_title'] ?? '',
            'meta_description' => $result['meta_description'] ?? $shortDesc,
            'short_description' => $shortDesc,
            'description' => $result['description'],
            'meta_keywords' => $result['meta_keywords'],
            'length' => $result['length'] ?? null,
            'width' => $result['width'] ?? null,
            'height' => $result['height'] ?? null,
            'weight' => $result['weight'] ?? null,
            'url_scraped' => $result['url_scraped'] ?? null,
            'message' => __('AI Content generated successfully!'),
        ]);
    }

    /**
     * Toggle variant sell online status (Web & Mobile App).
     */
    public function variantToggleOnline(Request $request)
    {
        $request->validate([
            'inward_product_id' => 'required|integer',
            'is_online' => 'required',
        ]);

        $inwardProduct = InwardProduct::findOrFail($request->inward_product_id);
        $isOnline = filter_var($request->is_online, FILTER_VALIDATE_BOOLEAN);

        $inwardProduct->update([
            'is_online_product' => $isOnline ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'is_online' => (bool) $inwardProduct->is_online_product,
            'message' => $inwardProduct->is_online_product
                ? __('Variant enabled for Online Store & Mobile App!')
                : __('Variant disabled from Online Store & Mobile App (Physical POS only)!'),
        ]);
    }

    /**
     * Generate 5 AI product image variations based on reference image, keywords, and background color.
     */
    public function aiGenerateImages(Request $request, GeminiContentService $geminiService)
    {
        $request->validate([
            'reference_image' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
            'reference_image_url' => ['nullable', 'string'],
            'reference_image_base64' => ['nullable', 'string'],
            'background_color' => ['nullable', 'string', 'max:50'],
            'keywords' => ['nullable', 'string', 'max:10000'],
            'product_name' => ['nullable', 'string', 'max:255'],
        ]);

        $shop = auth()->user()?->shop ?? generaleSetting('shop');
        $apiKey = $geminiService->resolveApiKey($shop);

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => __('Google Gemini API key is missing. Please configure it in Store Profile / Settings first.'),
            ], 422);
        }

        $refBase64 = null;
        $refMime = 'image/png';

        if ($request->hasFile('reference_image')) {
            $file = $request->file('reference_image');
            $optimized = $geminiService->optimizeReferenceImage($file->getRealPath());
            if ($optimized) {
                $refBase64 = $optimized['base64'];
                $refMime = $optimized['mime'];
            } else {
                $refBase64 = base64_encode(file_get_contents($file->getRealPath()));
                $refMime = $file->getMimeType() ?: 'image/png';
            }
        } elseif ($request->filled('reference_image_base64')) {
            $raw = $request->reference_image_base64;
            if (preg_match('/^data:([^;]+);base64,(.+)$/', $raw, $matches)) {
                $refMime = $matches[1];
                $refBase64 = $matches[2];
            } else {
                $refBase64 = $raw;
            }
        } elseif ($request->filled('reference_image_url')) {
            $url = $request->reference_image_url;
            try {
                if (str_starts_with($url, '/storage/') || str_contains($url, '/storage/')) {
                    $relativePath = preg_replace('#^.*?/storage/#', '', $url);
                    if (Storage::disk('public')->exists($relativePath)) {
                        $content = Storage::disk('public')->get($relativePath);
                        $refBase64 = base64_encode($content);
                        $refMime = Storage::disk('public')->mimeType($relativePath) ?: 'image/png';
                    }
                } elseif (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                    $imgRes = Http::timeout(10)->get($url);
                    if ($imgRes->successful()) {
                        $refBase64 = base64_encode($imgRes->body());
                        $refMime = $imgRes->header('Content-Type') ?: 'image/jpeg';
                    }
                }
            } catch (\Throwable $e) {
                // Ignore reference reading error
            }
        }

        $result = $geminiService->generateProductImages($apiKey, [
            'product_name' => $request->product_name ?: 'Product',
            'keywords' => $request->keywords ?: '',
            'background_color' => $request->background_color ?: '#FFFFFF',
            'reference_image_base64' => $refBase64,
            'reference_image_mime' => $refMime,
        ]);

        return response()->json($result);
    }

    /**
     * Upload a manual product image from the AI modal studio.
     */
    public function aiUploadManualImage(Request $request)
    {
        $request->validate([
            'manual_image' => ['required', 'file', 'mimes:png,jpg,jpeg,webp', 'max:10240'],
        ]);

        $file = $request->file('manual_image');
        $aiDir = storage_path('app/public/products/ai');
        if (!file_exists($aiDir)) {
            mkdir($aiDir, 0777, true);
        }

        $filename = 'ai_manual_' . uniqid() . '.' . ($file->getClientOriginalExtension() ?: 'png');
        $file->move($aiDir, $filename);

        $publicUrl = asset('storage/products/ai/' . $filename);
        $fullPath = $aiDir . '/' . $filename;
        $base64 = 'data:' . ($file->getClientMimeType() ?: 'image/png') . ';base64,' . base64_encode(file_get_contents($fullPath));

        return response()->json([
            'success' => true,
            'image' => [
                'id' => 'manual_' . uniqid(),
                'label' => __('Manual Photo: ') . $file->getClientOriginalName(),
                'description' => __('Manually uploaded product photo'),
                'filename' => $filename,
                'url' => $publicUrl,
                'base64' => $base64,
            ],
            'message' => __('Manual image added to studio!'),
        ]);
    }
}

