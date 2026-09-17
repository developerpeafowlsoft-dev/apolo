<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategoryRequest;
use App\Models\SubCategory;
use App\Repositories\SubCategoryRepository;

use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');

        $query = ($shop && $shop->subCategories()->exists()) ? $shop->subCategories() : SubCategory::query();

        $subCategories = $query
            ->with(['creator', 'categories'])
            ->select('sub_categories.*')
            ->selectSub(function ($q) {
                $q->from('inward_products')
                    ->join('product_subcategories', 'inward_products.product_id', '=', 'product_subcategories.product_id')
                    ->whereColumn('product_subcategories.sub_category_id', 'sub_categories.id')
                    ->selectRaw('count(*)');
            }, 'inward_products_count')
            ->selectSub(function ($q) {
                $q->from('products')
                    ->join('product_subcategories', 'products.id', '=', 'product_subcategories.product_id')
                    ->whereColumn('product_subcategories.sub_category_id', 'sub_categories.id')
                    ->where('products.is_online_product', 1)
                    ->where('products.is_active', 1)
                    ->selectRaw('count(*)');
            }, 'online_products_count')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('sub_categories.name', 'like', "%{$search}%")
                        ->orWhereHas('categories', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('sub_categories.id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.sub-category.index', compact('subCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $shop = generaleSetting('shop');

        $categories = $shop->categories()->active()->get();

        return view('admin.sub-category.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubCategoryRequest $request)
    {
        SubCategoryRepository::storeByRequest($request);

        return to_route('admin.subcategory.index')->with('success', __('Sub Category created successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubCategory $subCategory)
    {
        $shop = generaleSetting('shop');

        $categories = $shop->categories()->active()->get();

        return view('admin.sub-category.edit', compact('subCategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubCategoryRequest $request, SubCategory $subCategory)
    {
        SubCategoryRepository::updateByRequest($request, $subCategory);

        return to_route('admin.subcategory.index')->with('success', __('Sub Category updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        if ($subCategory->products()->exists()) {
            return back()->with('error', __('Cannot delete sub category because products are associated with it'));
        }

        $subCategory->delete();

        return to_route('admin.subcategory.index')->with('success', __('Deleted successfully'));
    }

    /**
     * status toggle
     */
    public function statusToggle(SubCategory $subCategory)
    {
        $subCategory->update(['is_active' => ! $subCategory->is_active]);

        return back()->with('success', __('Status updated successfully'));
    }
}
