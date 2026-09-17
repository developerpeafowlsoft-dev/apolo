<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display the unit list.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;
        $rootShopId = generaleSetting('rootShop')?->id ?? 1;

        // Shop can view Super Admin units (rootShopId or created_by=1) AND its own units (currentShopId)
        $query = Unit::where(function ($q) use ($currentShopId, $rootShopId) {
            $q->where('shop_id', $rootShopId)
              ->orWhere('created_by', 1);
            if ($currentShopId) {
                $q->orWhere('shop_id', $currentShopId);
            }
        });

        $units = $query
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.unit.index', compact('units', 'search', 'currentShopId'));
    }

    /**
     * store a new unit
     */
    public function store(UnitRequest $request)
    {
        UnitRepository::storeByRequest($request);

        return to_route('shop.unit.index')->withSuccess(__('Unit created successfully'));
    }

    /**
     * update a unit
     */
    public function update(UnitRequest $request, Unit $unit)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $unit->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created units cannot be edited by shop.'));
        }

        UnitRepository::updateByRequest($request, $unit);

        return to_route('shop.unit.index')->withSuccess(__('Unit updated successfully'));
    }

    /**
     * status toggle a unit
     */
    public function statusToggle(Unit $unit)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $unit->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created units cannot be modified by shop.'));
        }

        $unit->update([
            'is_active' => ! $unit->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * delete a unit (Shop can only delete units created and owned by that same shop)
     */
    public function destroy(Unit $unit)
    {
        $user = auth()->user();
        $currentShopId = $user?->shop?->id ?? $user?->myShop?->id ?? $user?->shop_id;

        if (! $currentShopId || ! $unit->isOwnedByShop($currentShopId)) {
            abort(403, __('Unauthorized action. Super Admin-created units cannot be deleted by shop.'));
        }

        $unit->translations()->delete();
        $unit->delete();

        return to_route('shop.unit.index')->withSuccess(__('Unit deleted successfully'));
    }
}
