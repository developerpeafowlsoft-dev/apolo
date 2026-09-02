<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaterialRequest;
use App\Models\Material;
use App\Repositories\MaterialRepository;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $rootShop = generaleSetting('rootShop');

        // Get all materials (Super Admin created + Shop created)
        $materials = Material::with('shop')->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.material.index', compact('materials', 'rootShop'));
    }

    public function store(MaterialRequest $request)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

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
}
