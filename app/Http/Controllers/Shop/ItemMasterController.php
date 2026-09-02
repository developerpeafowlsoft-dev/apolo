<?php

namespace App\Http\Controllers\Shop;

use App\Events\AdminProductRequestEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ItemMasterRequest;
use App\Models\Product;
use App\Models\Salesman;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\VatTax;
use App\Repositories\ItemMasterRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;

class ItemMasterController extends Controller
{
    public function index(Request $request)
    {
        // get category, brand, color and search from request
        $category = $request->categoryFilter;
        $brand = $request->brandFilter;
        $color = $request->colorFilter;
        $search = $request->search;

        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        // filter products based on category, brand, color and search
        $itemMasters = $shop?->products()->when($brand, function ($query) use ($brand) {
            return $query->where('brand_id', $brand);
        })->when($category, function ($query) use ($category) {
            return $query->whereHas('categories', function ($query) use ($category) {
                return $query->where('category_id', $category);
            });
        })->when($color, function ($query) use ($color) {
            return $query->whereHas('colors', function ($query) use ($color) {
                return $query->where('color_id', $color);
            });
        })->when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->basicFields()->isItemmaster(1)->where('shop_id',$shop->id)->orderByDesc('id')->paginate(20)->withQueryString();
//        dd($products);

        // get brands, colors and categories
        $brandFilters = $rootShop?->brands()->get();
        $colorFilters = $rootShop?->colors()->get();
        $categorieFilters = $rootShop?->categories()->get();

        if ($request->ajax()) {
            return view('shop.item-master.partials.item-master-table', compact('itemMasters','brandFilters','colorFilters','categorieFilters'))->render();
        }

        return view('shop.item-master.index',compact('itemMasters','brandFilters','colorFilters','categorieFilters'));
    }

    public function modalData()
    {
        $shop = generaleSetting('rootShop');
        $shopVendor = generaleSetting('shop');

        $brands = $shop?->brands()->isActive()->get();
        $categories = $shop?->categories()->active()->get();
        $colors = $shop?->colors()->isActive()->get();
        $taxs = VatTax::active()->get(['id', 'name', 'percentage']);
        $sizes = $shop?->sizes()->isActive()->get();
        $units = $shop?->units()->isActive()->get();
        $materials = $shop?->materials()->isActive()->get();
        $hsnMasters = $shopVendor?->hsnmasters()->isActive()->get();
        $salesmans = Salesman::active()->get(['id','name']);

//        dd($shopVendor,$hsnMasters);
        return response()->json([
            'brands' => $brands,
            'categories' => $categories,
            'colors' => $colors,
            'taxs' => $taxs,
            'sizes' => $sizes,
            'units' => $units,
            'materials' => $materials,
            'hsnMasters' => $hsnMasters,
            'salesmans' => $salesmans,
        ]);
    }

    public function store(ItemMasterRequest $request)
    {
        $shop = generaleSetting('shop');

        $product = ProductRepository::storeByItemRequest($request);
        $product->load(['hsnMaster', 'vatTax']);

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

        return response()->json([
            'status' => true,
            'message' => __('Product created successfully!'),
            'product' => $product,
        ]);

//        return to_route('shop.product.index')->withSuccess(__('Product created successfully!'));
    }

    public function update(ItemMasterRequest $request, Product $itemMaster)
    {
        $shop = generaleSetting('shop');

        ProductRepository::updateByItemRequest($request, $itemMaster);

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

        return response()->json([
            'status' => true,
            'message' => __('Product updated successfully!'),
        ]);
    }

    public function edit(Product $itemMaster)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        // get brands, colors, units, sizes and categories
        $brands = $rootShop?->brands()->isActive()->get();
        $colors = $rootShop?->colors()->isActive()->get();
        $categories = $rootShop?->categories()->active()->get();
        $units = $rootShop?->units()->isActive()->get();
        $sizes = $rootShop?->sizes()->isActive()->get();

        $categoryId = $itemMaster->categories()?->latest('id')->first()?->id;

        $subCategories = SubCategory::whereHas('categories', function ($query) use ($categoryId) {
            return $query->where('category_id', $categoryId);
        })->isActive()->get();


        return response()->json([
            'itemMaster' => $itemMaster,
            'categoryIds' => $itemMaster->categories->pluck('id')->toArray(),
            'subCategoryIds' => $itemMaster->subcategories->pluck('id')->toArray(),
            'colorIds' => $itemMaster->colors->pluck('id')->toArray(),
            'sizeIds' => $itemMaster->sizes->pluck('id')->toArray(),
        ]);
    }

    public function destroy(Product $itemMaster)
    {
        $itemMaster->delete();

        return back()->withSuccess(__('Item master deleted successfully'));
    }
    public function onlineProductToggle(Product $itemMaster)
    {
        $itemMaster->update([
            'is_online_product' => !$itemMaster->is_online_product,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }
}
