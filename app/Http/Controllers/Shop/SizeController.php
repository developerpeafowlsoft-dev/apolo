<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\SizeRequest;
use App\Models\Size;
use App\Repositories\SizeRepository;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display the size list.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;
        $shop = $user?->shop ?? $user?->myShop ?? ($currentShopId ? \App\Models\Shop::find($currentShopId) : null);

        $query = $shop ? $shop->sizes() : Size::whereRaw('1 = 0');

        // Get sizes belonging to current shop
        $sizes = $query
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.size.index', compact('sizes', 'search', 'currentShopId'));
    }

    /**
     * store a new size
     */
    public function store(SizeRequest $request)
    {
        SizeRepository::storeByRequest($request);

        return to_route('shop.size.index')->withSuccess(__('Size created successfully'));
    }

    /**
     * update a size
     */
    public function update(SizeRequest $request, Size $size)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $size->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created sizes cannot be edited by shop.'));
        }

        SizeRepository::updateByRequest($request, $size);

        return to_route('shop.size.index')->withSuccess(__('Size updated successfully'));
    }

    /**
     * status toggle a size
     */
    public function statusToggle(Size $size)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $size->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created sizes cannot be modified by shop.'));
        }

        $size->update([
            'is_active' => ! $size->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * delete a size (Shop can only delete sizes created and owned by that same shop)
     */
    public function destroy(Size $size)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $size->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created sizes cannot be deleted by shop.'));
        }

        $size->translations()->delete();
        $size->delete();

        return to_route('shop.size.index')->withSuccess(__('Size deleted successfully'));
    }
}
