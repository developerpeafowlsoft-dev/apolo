<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ColorRequest;
use App\Models\Color;
use App\Repositories\ColorRepository;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Display the colors list.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;
        $shop = $user?->shop ?? $user?->myShop ?? ($currentShopId ? \App\Models\Shop::find($currentShopId) : null);

        $query = $shop ? $shop->colors() : Color::whereRaw('1 = 0');

        // Get colors belonging to current shop
        $colors = $query
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('color_code', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.color.index', compact('colors', 'search', 'currentShopId'));
    }

    /**
     * store a new color
     */
    public function store(ColorRequest $request)
    {
        ColorRepository::storeByRequest($request);

        return to_route('shop.color.index')->withSuccess(__('Color created successfully'));
    }

    /**
     * update a color
     */
    public function update(ColorRequest $request, Color $color)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $color->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created colors cannot be edited by shop.'));
        }

        ColorRepository::updateByRequest($request, $color);

        return to_route('shop.color.index')->withSuccess(__('Color updated successfully'));
    }

    /**
     * status toggle a color
     */
    public function statusToggle(Color $color)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $color->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created colors cannot be modified by shop.'));
        }

        $color->update([
            'is_active' => ! $color->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * delete a color (Shop can only delete colors created and owned by that same shop)
     */
    public function destroy(Color $color)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $color->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created colors cannot be deleted by shop.'));
        }

        $color->translations()->delete();
        $color->delete();

        return to_route('shop.color.index')->withSuccess(__('Color deleted successfully'));
    }
}
