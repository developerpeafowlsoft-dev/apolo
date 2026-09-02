<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategoryRequest;
use App\Models\Category;
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
        $search = $request->search ?? null;
        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        $subCategories = SubCategory::where(function ($query) use ($shop, $rootShop) {
                $query->where('shop_id', $shop?->id)
                      ->orWhere('shop_id', $rootShop?->id)
                      ->orWhereNull('shop_id');
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%'.$search.'%');
            })
            ->with(['shop', 'categories'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Get categories for create modal dropdown
        $categories = Category::active()->get();

        return view('shop.sub-category.index', compact('subCategories', 'categories', 'rootShop', 'shop'));
    }

    /**
     * Store a new subcategory created from Shop side.
     */
    public function store(SubCategoryRequest $request)
    {
        $subCategory = SubCategoryRepository::storeByRequest($request);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => __('Subcategory created successfully'),
                'sub_category' => $subCategory,
            ]);
        }

        return to_route('shop.subcategory.index')->withSuccess(__('Subcategory created successfully'));
    }
}
