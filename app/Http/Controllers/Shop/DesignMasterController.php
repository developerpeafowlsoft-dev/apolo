<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\DesignMaster;
use Illuminate\Http\Request;
use App\Http\Requests\DesignMasterRequest;
use App\Repositories\DesignMasterRepository;

class DesignMasterController extends Controller
{
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');
//        $hsnMasters = HsnMaster::basicFields()->with('vattax:id,name,percentage')->paginate(10);
//        $category = $request->categoryFilter;
//        $brand = $request->brandFilter;
//        $color = $request->colorFilter;
        $search = $request->search;

        $designMasters = $shop?->designMasters()
            ->when($search, function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('design_number', 'like', "%$search%")
                        ->orWhereHas('accountMasters', function($q2) use ($search) {
                            $q2->where('accountName', 'like', "%$search%")
                                ->orWhere('accountshortcode', 'like', "%$search%");
                        })
                        ->orWhereHas('products', function($q3) use ($search) {
                            $q3->where('name', 'like', "%$search%");
                        });
                });
            })
            ->with('products:id,name')->with('accountMasters:id,accountshortcode,accountName')->whereHas('products')->whereHas('accountMasters')->orderByDesc('id')->paginate(20)->withQueryString();
//        dd($designMasters);

        if ($request->ajax()) {
            return view('shop.design-master.partials.design-master-table', compact('designMasters'))->render();
        }


        return view('shop.design-master.index',compact('designMasters'));
    }

    public function modalData(Request $request)
    {
        $shop = generaleSetting('rootShop');
        $shopVendor = generaleSetting('shop');

        // Dropdown With Product
        $query = $shopVendor?->products()->select('id', 'name')->isItemmaster(1)->orderByDesc('id');

        if ($request->has('searchItemName') && $request->searchItemName != '') {
            $query->where('name', 'like', '%' . $request->searchItemName . '%');
        } else {
            $query->take(5);
        }

        $itemMasters = $query->get();

        // Dropdown With AccountMaster

        $queryAccount = $shopVendor?->accountMasters()->select('id', 'accountshortcode', 'accountName','other_info_act_limit')->active()->orderByDesc('id');

        if ($request->has('searchAccountMaster') && $request->searchAccountMaster != '') {
            $search = $request->searchAccountMaster;

            $queryAccount->where(function($q) use ($search) {
                $q->where('accountshortcode', 'like', '%' . $search . '%')
                    ->orWhere('accountName', 'like', '%' . $search . '%');
            });
        } else {
            $queryAccount->take(2);
        }

        $accountMasters = $queryAccount->get();


        // Dropdown With Employee
        $queryEmployee = $shopVendor?->employee()->select('id', 'name', 'last_name')->orderByDesc('id');

        if ($request->has('searchEmployeeName') && $request->searchEmployeeName != '') {
            $queryEmployee->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        } else {
            $queryEmployee->take(5);
        }

        $employeeMaster = $queryEmployee->get();


        return response()->json([
            'itemMasters' => $itemMasters,
            'accountMasters' => $accountMasters,
            'employeeMasters' => $employeeMaster,
        ]);
    }

    public function store(DesignMasterRequest $request)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        $design = DesignMasterRepository::designMasterCreate($request);
        $design->load(['products.hsnMaster', 'products.vatTax']);
        return response()->json([
            'status' => true,
            'message' => __('Design master created successfully!'),
            'design' => $design,
        ]);
    }

    public function edit(DesignMaster $designMaster)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        $designMaster->load([
            'products:id,name',
            'accountMasters:id,accountshortcode,accountName'
        ]);

        return response()->json([
            'designMaster' => $designMaster,
        ]);
    }

    public function update(DesignMasterRequest $request, DesignMaster $designMaster)
    {
        $shop = generaleSetting('shop');

        DesignMasterRepository::designMasterupdate($request, $designMaster);


        return response()->json([
            'status' => true,
            'message' => __('Design master updated successfully!'),
        ]);
    }

    public function destroy(DesignMaster $designMaster)
    {
        $designMaster->delete();

        return back()->withSuccess(__('Design master deleted successfully'));
    }

    public function statusToggle(DesignMaster $designMaster)
    {
//        dd($designMaster);
        $designMaster->update([
            'is_active' => !$designMaster->is_active,
        ]);

        return response()->json([
            'status' => true,
            'message' => __('Status updated successfully'),
        ]);

    }
}
