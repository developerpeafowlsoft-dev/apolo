<?php

namespace App\Http\Controllers\Admin;

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

        // Get colors
        $colors = Color::when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('color_code', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.color.index', compact('colors', 'search'));
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

    /**
     * delete a color (Super Admin can delete any color)
     */
    public function destroy(Color $color)
    {
        $user = auth()->user();
        if (! $user || (! $user->hasRole('root') && ! $user->can('admin.color.destroy'))) {
            abort(403, __('Unauthorized action. Only Super Admin can delete colors here.'));
        }

        $color->translations()->delete();
        $color->delete();

        return to_route('admin.color.index')->withSuccess(__('Color deleted successfully'));
    }
}
