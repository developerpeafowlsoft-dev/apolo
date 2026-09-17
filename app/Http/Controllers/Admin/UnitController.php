<?php

namespace App\Http\Controllers\Admin;

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

        $units = Unit::when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.unit.index', compact('units', 'search'));
    }

    /**
     * store a new unit
     */
    public function store(UnitRequest $request)
    {
        UnitRepository::storeByRequest($request);

        return to_route('admin.unit.index')->withSuccess(__('Unit created successfully'));
    }

    /**
     * update a unit
     */
    public function update(UnitRequest $request, Unit $unit)
    {
        UnitRepository::updateByRequest($request, $unit);

        return to_route('admin.unit.index')->withSuccess(__('Unit updated successfully'));
    }

    /**
     * status toggle a unit
     */
    public function statusToggle(Unit $unit)
    {
        $unit->update([
            'is_active' => ! $unit->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * delete a unit (Super Admin can delete any unit)
     */
    public function destroy(Unit $unit)
    {
        $user = auth()->user();
        if (! $user || (! $user->hasRole('root') && ! $user->can('admin.unit.destroy'))) {
            abort(403, __('Unauthorized action. Only Super Admin can delete units here.'));
        }

        $unit->translations()->delete();
        $unit->delete();

        return to_route('admin.unit.index')->withSuccess(__('Unit deleted successfully'));
    }
}
