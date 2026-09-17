<?php

namespace App\Http\Controllers\Admin;

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

        // Get Materials
        $materials = Material::when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.material.index', compact('materials', 'search'));
    }

    public function store(MaterialRequest $request)
    {
        MaterialRepository::materialrCreate($request);

        return to_route('admin.material.index')->withSuccess(__('Material created successfully'));
    }

    public function update(MaterialRequest $request, Material $material)
    {
        MaterialRepository::materialUpdate($request, $material);

        return to_route('admin.material.index')->withSuccess(__('Material updated successfully'));
    }

    public function statusToggle(Material $material)
    {
        $material->update([
            'is_active' => ! $material->is_active,
        ]);

        return to_route('admin.material.index')->withSuccess(__('Material status updated'));
    }

    /**
     * delete a material (Super Admin can delete any material)
     */
    public function destroy(Material $material)
    {
        $user = auth()->user();
        if (! $user || (! $user->hasRole('root') && ! $user->can('admin.material.destroy'))) {
            abort(403, __('Unauthorized action. Only Super Admin can delete materials here.'));
        }

        $material->delete();

        return to_route('admin.material.index')->withSuccess(__('Material deleted successfully'));
    }
}
