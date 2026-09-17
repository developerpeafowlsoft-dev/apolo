<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use App\Repositories\BrandRepository;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;
        $shop = $user?->shop ?? $user?->myShop ?? ($currentShopId ? \App\Models\Shop::find($currentShopId) : null);

        $query = $shop ? $shop->brands() : Brand::whereRaw('1 = 0');

        // Get brands belonging to current shop
        $brands = $query
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.brand.index', compact('brands', 'search', 'currentShopId'));
    }

    /**
     * store a new brand
     */
    public function store(BrandRequest $request)
    {
        BrandRepository::storeByRequest($request);

        return to_route('shop.brand.index')->withSuccess(__('Brand created successfully'));
    }

    /**
     * update a brand
     */
    public function update(BrandRequest $request, Brand $brand)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $brand->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created brands cannot be edited by shop.'));
        }

        BrandRepository::updateByRequest($request, $brand);

        return to_route('shop.brand.index')->withSuccess(__('Brand updated successfully'));
    }

    /**
     * status toggle a brand
     */
    public function statusToggle(Brand $brand)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $brand->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created brands cannot be modified by shop.'));
        }

        $brand->update([
            'is_active' => ! $brand->is_active,
        ]);

        return to_route('shop.brand.index')->withSuccess(__('Brand status updated'));
    }

    /**
     * delete a brand (Shop can only delete brands created and owned by that same shop)
     */
    public function destroy(Brand $brand)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $brand->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created brands cannot be deleted by shop.'));
        }

        $brand->translations()->delete();
        $brand->delete();

        return to_route('shop.brand.index')->withSuccess(__('Brand deleted successfully'));
    }
}
