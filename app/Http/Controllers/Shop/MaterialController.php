<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaterialRequest;
use App\Models\Material;

class MaterialController extends Controller
{
    /**
     * Display the material list.
     */
    public function index()
    {
        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        // Get materials (created by Super Admin / Root Shop or by current Shop)
        $materials = Material::where(function ($query) use ($rootShop, $shop) {
                $query->where('shop_id', $shop?->id)
                      ->orWhere('shop_id', $rootShop?->id)
                      ->orWhereNull('shop_id');
            })
            ->with('shop')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.material.index', compact('materials', 'rootShop', 'shop'));
    }

    /**
     * Store a new material created from Shop side.
     */
    public function store(MaterialRequest $request)
    {
        $shop = generaleSetting('shop');

        $code = $request->code;
        if (!$code) {
            do {
                $code = (string)rand(1000, 9999);
            } while (Material::where('code', $code)->exists());
        }

        $material = Material::create([
            'name' => $request->name,
            'code' => $code,
            'is_active' => true,
            'shop_id' => $shop?->id,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => __('Material created successfully'),
                'material' => $material,
            ]);
        }

        return to_route('shop.material.index')->withSuccess(__('Material created successfully'));
    }

    /**
     * Toggle material status.
     */
    public function statusToggle(Material $material)
    {
        $material->update([
            'is_active' => !$material->is_active,
        ]);

        return to_route('shop.material.index')->withSuccess(__('Material status updated'));
    }
}
