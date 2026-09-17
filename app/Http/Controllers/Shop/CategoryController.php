<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a category listing.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $currentShop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $shop = $currentShop ?? $rootShop;

        // Query categories attached to the shop (or root shop if none)
        $query = ($shop && $shop->categories()->exists()) ? $shop->categories() : Category::query();

        $categories = $query->with('creator')
            ->select('categories.*')
            ->selectSub(function ($q) {
                $q->from('inward_products')
                    ->join('product_categories', 'inward_products.product_id', '=', 'product_categories.product_id')
                    ->whereColumn('product_categories.category_id', 'categories.id')
                    ->selectRaw('count(*)');
            }, 'inward_products_count')
            ->selectSub(function ($q) {
                $q->from('products')
                    ->join('product_categories', 'products.id', '=', 'product_categories.product_id')
                    ->whereColumn('product_categories.category_id', 'categories.id')
                    ->where('products.is_online_product', 1)
                    ->where('products.is_active', 1)
                    ->selectRaw('count(*)');
            }, 'online_products_count')
            ->when($search, function ($q) use ($search) {
                return $q->where('name', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.category.index', compact('categories', 'search'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('shop.category.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request)
    {
        $category = CategoryRepository::storeByRequest($request);

        $currentShop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        if ($currentShop) {
            $currentShop->categories()->syncWithoutDetaching([$category->id]);
        }
        if ($rootShop && $rootShop->id !== $currentShop?->id) {
            $rootShop->categories()->syncWithoutDetaching([$category->id]);
        }

        return to_route('shop.category.index')->withSuccess(__('Category created successfully'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        return view('shop.category.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        CategoryRepository::updateByRequest($request, $category);

        return to_route('shop.category.index')->withSuccess(__('Category updated successfully'));
    }

    /**
     * Toggle status of the category.
     */
    public function statusToggle(Category $category)
    {
        $category->update(['status' => ! $category->status]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * Toggle hero section display for the category.
     */
    public function heroToggle(Category $category)
    {
        $category->update(['show_in_hero' => ! $category->show_in_hero]);

        return back()->withSuccess(__('Hero section display updated successfully'));
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        $inwardCount = DB::table('inward_products')
            ->join('product_categories', 'inward_products.product_id', '=', 'product_categories.product_id')
            ->where('product_categories.category_id', $category->id)
            ->count();

        if ($inwardCount > 0) {
            return back()->withErrors(__('Cannot delete category because it is associated with inward product items.'));
        }

        $currentShop = generaleSetting('shop');
        if ($currentShop) {
            $currentShop->categories()->detach($category->id);
        }

        if (!$category->products()->exists() && $category->shops()->count() === 0) {
            $category->delete();
        }

        return back()->withSuccess(__('Category deleted successfully'));
    }
}
