<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Unit;

class UnitController extends Controller
{
    /**
     * Display the unit list.
     */
    public function index()
    {
        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        // Get units (created by Super Admin / Root Shop or by current Shop)
        $units = Unit::where(function ($query) use ($rootShop, $shop) {
                $query->where('shop_id', $shop?->id)
                      ->orWhere('shop_id', $rootShop?->id)
                      ->orWhereNull('shop_id');
            })
            ->with('shop')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.unit.index', compact('units', 'rootShop', 'shop'));
    }

    /**
     * Store a new unit created from Shop side.
     */
    public function store(UnitRequest $request)
    {
        $shop = generaleSetting('shop');

        $unit = Unit::create([
            'name' => $request->name,
            'is_active' => true,
            'shop_id' => $shop?->id,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => __('Unit created successfully'),
                'unit' => $unit,
            ]);
        }

        return to_route('shop.unit.index')->withSuccess(__('Unit created successfully'));
    }

    /**
     * Toggle unit status.
     */
    public function statusToggle(Unit $unit)
    {
        $unit->update([
            'is_active' => !$unit->is_active,
        ]);

        return to_route('shop.unit.index')->withSuccess(__('Unit status updated'));
    }
}
