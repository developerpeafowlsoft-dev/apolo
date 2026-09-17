<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaterialRequest;
use App\Models\Material;
use App\Repositories\MaterialRepository;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;
        $shop = $user?->shop ?? $user?->myShop ?? ($currentShopId ? \App\Models\Shop::find($currentShopId) : null);

        $query = $shop ? $shop->materials() : Material::whereRaw('1 = 0');

        // Get materials belonging to current shop
        $materials = $query
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.material.index', compact('materials', 'search', 'currentShopId'));
    }

    public function store(MaterialRequest $request)
    {
        MaterialRepository::materialrCreate($request);

        return to_route('shop.material.index')->withSuccess(__('Material created successfully'));
    }

    public function update(MaterialRequest $request, Material $material)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $material->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created materials cannot be edited by shop.'));
        }

        MaterialRepository::materialUpdate($request, $material);

        return to_route('shop.material.index')->withSuccess(__('Material updated successfully'));
    }

    public function statusToggle(Material $material)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $material->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created materials cannot be modified by shop.'));
        }

        $material->update([
            'is_active' => ! $material->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    public function destroy(Material $material)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $material->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created materials cannot be deleted by shop.'));
        }

        $material->delete();

        return to_route('shop.material.index')->withSuccess(__('Material deleted successfully'));
    }
}
