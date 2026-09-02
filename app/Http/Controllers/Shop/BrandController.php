<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Models\Brand;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     */
    public function index()
    {
        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        // Get brands (created by Super Admin / Root Shop or by current Shop)
        $brands = Brand::where(function ($query) use ($rootShop, $shop) {
                $query->where('shop_id', $shop?->id)
                      ->orWhere('shop_id', $rootShop?->id)
                      ->orWhereNull('shop_id');
            })
            ->with('shop')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.brand.index', compact('brands', 'rootShop', 'shop'));
    }

    /**
     * Store a new brand created from Shop side.
     */
    public function store(BrandRequest $request)
    {
        $shop = generaleSetting('shop');

        $brand = Brand::create([
            'name' => $request->name,
            'is_active' => true,
            'shop_id' => $shop?->id,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => __('Brand created successfully'),
                'brand' => $brand,
            ]);
        }

        return to_route('shop.brand.index')->withSuccess(__('Brand created successfully'));
    }

    /**
     * Toggle brand status.
     */
    public function statusToggle(Brand $brand)
    {
        $brand->update([
            'is_active' => !$brand->is_active,
        ]);

        return to_route('shop.brand.index')->withSuccess(__('Brand status updated'));
    }
}
