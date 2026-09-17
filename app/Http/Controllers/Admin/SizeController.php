<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SizeRequest;
use App\Models\Size;
use App\Repositories\SizeRepository;

use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display the size list.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;

        $sizes = Size::when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.size.index', compact('sizes', 'search'));
    }

    /**
     * store a new size
     */
    public function store(SizeRequest $request)
    {
        SizeRepository::storeByRequest($request);

        return to_route('admin.size.index')->withSuccess(__('Size created successfully'));
    }

    /**
     * update a size
     */
    public function update(SizeRequest $request, Size $size)
    {
        SizeRepository::updateByRequest($request, $size);

        return to_route('admin.size.index')->withSuccess(__('Size updated successfully'));
    }

    /**
     * status toggle a size
     */
    public function statusToggle(Size $size)
    {
        $size->update([
            'is_active' => ! $size->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * delete a size (Super Admin can delete any size)
     */
    public function destroy(Size $size)
    {
        $user = auth()->user();
        if (! $user || (! $user->hasRole('root') && ! $user->can('admin.size.destroy'))) {
            abort(403, __('Unauthorized action. Only Super Admin can delete sizes here.'));
        }

        $size->translations()->delete();
        $size->delete();

        return to_route('admin.size.index')->withSuccess(__('Size deleted successfully'));
    }
}
