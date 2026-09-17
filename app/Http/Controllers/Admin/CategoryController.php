<?php

namespace App\Http\Controllers\Admin;

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
        $shop = generaleSetting('rootShop');

        $query = ($shop && $shop->categories()->exists()) ? $shop->categories() : Category::query();

        // Get categories with search, inward items count, and pagination
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
                return $q->where('name', 'like', '%'.$search.'%');
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.category.index', compact('categories', 'search'));
    }

    /**
     * create a new category
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * store a new category
     */
    public function store(CategoryRequest $request)
    {
        $category = CategoryRepository::storeByRequest($request);

        $shop = generaleSetting('rootShop');
        if ($shop) {
            $shop->categories()->syncWithoutDetaching([$category->id]);
        }

        return to_route('admin.category.index')->withSuccess(__('Category created successfully'));
    }

    /**
     * edit a category
     */
    public function edit(Category $category)
    {
        return view('admin.category.edit', compact('category'));
    }

    /**
     * update a category
     */
    public function update(CategoryRequest $request, Category $category)
    {
        CategoryRepository::updateByRequest($request, $category);

        return to_route('admin.category.index')->withSuccess(__('Category updated successfully'));
    }

    /**
     * category status toggle
     */
    public function statusToggle(Category $category)
    {
        $category->update(['status' => ! $category->status]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * category hero section display toggle
     */
    public function heroToggle(Category $category)
    {
        $category->update(['show_in_hero' => ! $category->show_in_hero]);

        return back()->withSuccess(__('Hero section display updated successfully'));
    }

    /**
     * delete a category
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

        $shop = generaleSetting('rootShop');
        if ($shop) {
            $shop->categories()->detach($category->id);
        }

        if (!$category->products()->exists() && $category->shops()->count() === 0) {
            $category->delete();
        }

        return back()->withSuccess(__('Category deleted successfully'));
    }
}
