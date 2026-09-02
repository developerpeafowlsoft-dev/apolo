<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ColorRequest;
use App\Models\Color;

class ColorController extends Controller
{
    /**
     * Display the colors list.
     */
    public function index()
    {
        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        // Get colors (created by Super Admin / Root Shop or by current Shop)
        $colors = Color::where(function ($query) use ($rootShop, $shop) {
                $query->where('shop_id', $shop?->id)
                      ->orWhere('shop_id', $rootShop?->id)
                      ->orWhereNull('shop_id');
            })
            ->with('shop')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.color.index', compact('colors', 'rootShop', 'shop'));
    }

    /**
     * Store a new color created from Shop side.
     */
    public function store(ColorRequest $request)
    {
        $shop = generaleSetting('shop');

        Color::create([
            'name' => $request->name,
            'color_code' => $request->color_code ?? '#000000',
            'is_active' => true,
            'shop_id' => $shop?->id,
        ]);

        return to_route('shop.color.index')->withSuccess(__('Color created successfully'));
    }

    /**
     * Toggle color status.
     */
    public function statusToggle(Color $color)
    {
        $color->update([
            'is_active' => !$color->is_active,
        ]);

        return to_route('shop.color.index')->withSuccess(__('Color status updated'));
    }
}
