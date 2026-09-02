<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a category listing.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;

        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        // Get categories with search and pagination (including Super Admin and Shop categories)
        $categories = \App\Models\Category::where(function ($q) use ($shop, $rootShop) {
            $q->whereHas('shops', function ($sq) use ($shop) {
                $sq->where('shops.id', $shop?->id);
            })
            ->orWhere('shop_id', $shop?->id)
            ->orWhere('shop_id', $rootShop?->id)
            ->orWhereNull('shop_id');
        })
        ->when($search, function ($query) use ($search) {
            return $query->where('name', 'like', '%'.$search.'%');
        })
        ->with('shop')
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

        return view('shop.category.index', compact('categories', 'rootShop', 'shop'));
    }

    /**
     * Store a new category created from Shop side.
     */
    public function store(CategoryRequest $request)
    {
        $category = CategoryRepository::storeByRequest($request);

        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        if ($rootShop) {
            $rootShop->categories()->syncWithoutDetaching([$category->id]);
        }
        if ($shop && $shop->id !== $rootShop?->id) {
            $shop->categories()->syncWithoutDetaching([$category->id]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => __('Category created successfully'),
                'category' => $category,
            ]);
        }

        return to_route('shop.category.index')->withSuccess(__('Category created successfully'));
    }
}
