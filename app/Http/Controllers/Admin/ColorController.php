<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ColorRequest;
use App\Models\Color;
use App\Repositories\ColorRepository;

class ColorController extends Controller
{
    /**
     * Display the colors list.
     */
    public function index()
    {
        $rootShop = generaleSetting('rootShop');

        // Get all colors (Super Admin created + Shop created)
        $colors = Color::with('shop')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.color.index', compact('colors', 'rootShop'));
    }

    /**
     * store a new color
     */
    public function store(ColorRequest $request)
    {
        ColorRepository::storeByRequest($request);

        return to_route('admin.color.index')->withSuccess(__('Color created successfully'));
    }

    /**
     * update a color
     */
    public function update(ColorRequest $request, Color $color)
    {
        ColorRepository::updateByRequest($request, $color);

        return to_route('admin.color.index')->withSuccess(__('Color updated successfully'));
    }

    /**
     * status toggle a color
     */
    public function statusToggle(Color $color)
    {
        $color->update([
            'is_active' => ! $color->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }
}
