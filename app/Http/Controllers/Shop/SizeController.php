<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\SizeRequest;
use App\Models\Size;

class SizeController extends Controller
{
    /**
     * Display the size list.
     */
    public function index()
    {
        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        // Get sizes (created by Super Admin / Root Shop or by current Shop)
        $sizes = Size::where(function ($query) use ($rootShop, $shop) {
                $query->where('shop_id', $shop?->id)
                      ->orWhere('shop_id', $rootShop?->id)
                      ->orWhereNull('shop_id');
            })
            ->with('shop')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('shop.size.index', compact('sizes', 'rootShop', 'shop'));
    }

    /**
     * Store a new size created from Shop side.
     */
    public function store(SizeRequest $request)
    {
        $shop = generaleSetting('shop');

        Size::create([
            'name' => $request->name,
            'is_active' => true,
            'shop_id' => $shop?->id,
        ]);

        return to_route('shop.size.index')->withSuccess(__('Size created successfully'));
    }

    /**
     * Toggle size status.
     */
    public function statusToggle(Size $size)
    {
        $size->update([
            'is_active' => !$size->is_active,
        ]);

        return to_route('shop.size.index')->withSuccess(__('Size status updated'));
    }
}
