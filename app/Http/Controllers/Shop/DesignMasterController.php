<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\AccountMaster;
use App\Models\DesignMaster;
use Illuminate\Http\Request;
use App\Http\Requests\DesignMasterRequest;
use App\Repositories\DesignMasterRepository;

class DesignMasterController extends Controller
{
    public function index(Request $request)
    {
        $rootShop = generaleSetting('rootShop');
        $currentShop = generaleSetting('shop');
        $shopIds = array_filter(array_unique([$rootShop?->id, $currentShop?->id, 1, 14]));
        $search = $request->search;

        $designMasters = DesignMaster::whereIn('shop_id', $shopIds)
            ->when($search, function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('design_number', 'like', "%$search%")
                        ->orWhere('account_master_name', 'like', "%$search%")
                        ->orWhereHas('accountMasters', function($q2) use ($search) {
                            $q2->where('accountName', 'like', "%$search%")
                                ->orWhere('accountshortcode', 'like', "%$search%");
                        })
                        ->orWhereHas('products', function($q3) use ($search) {
                            $q3->where('name', 'like', "%$search%");
                        });
                });
            })
            ->with(['products:id,name', 'accountMasters:id,accountshortcode,accountName'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return view('shop.design-master.partials.design-master-table', compact('designMasters'))->render();
        }

        return view('shop.design-master.index', compact('designMasters'));
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

        $itemMasters = $query ? $query->get() : collect();

        $shopIds = array_unique(array_filter([1, 14, $shop?->id, $shopVendor?->id]));

        // Dropdown With AccountMaster
        $queryAccount = AccountMaster::whereIn('shop_id', $shopIds)
            ->where('is_party_code', 1)
            ->select('id', 'accountshortcode', 'accountName', 'other_info_act_limit', 'account_id', 'state_id')
            ->with(['state:id,name'])
            ->active();

        if ($request->has('searchAccountMaster') && trim($request->searchAccountMaster) != '') {
            $search = trim($request->searchAccountMaster);

            $queryAccount->where(function($q) use ($search) {
                $q->where('accountshortcode', 'like', '%' . $search . '%')
                    ->orWhere('accountName', 'like', '%' . $search . '%')
                    ->orWhere('cont_info_phone', 'like', '%' . $search . '%')
                    ->orWhere('cont_info_mobile1', 'like', '%' . $search . '%');
            })
            ->orderByRaw("CASE WHEN accountName LIKE ? THEN 0 WHEN accountshortcode = ? THEN 1 ELSE 2 END", ["{$search}%", $search])
            ->orderBy('accountName')
            ->limit(100);
        } else {
            $queryAccount->orderBy('accountName')->limit(80);
        }

        $accountMasters = $queryAccount->get();

        $allAccIds = $accountMasters->pluck('account_id')->merge($accountMasters->pluck('id'))->filter()->unique()->toArray();
        $accountBalances = \App\Models\AccountBalance::whereIn('account_id', $allAccIds)->get()->keyBy('account_id');
        $voucherSums = \App\Models\VoucherEntry::whereIn('account_id', $allAccIds)
            ->groupBy('account_id')
            ->selectRaw("account_id, SUM(CASE WHEN type = 'Cr' THEN amount WHEN type = 'Dr' THEN -amount ELSE 0 END) as net")
            ->pluck('net', 'account_id');

        foreach ($accountMasters as $acc) {
            $bal = 0.0;
            if ($acc->account_id && isset($accountBalances[$acc->account_id])) {
                $bal += (float)$accountBalances[$acc->account_id]->closing_balance;
            } elseif (isset($accountBalances[$acc->id])) {
                $bal += (float)$accountBalances[$acc->id]->closing_balance;
            }

            if ($acc->account_id && isset($voucherSums[$acc->account_id])) {
                $bal += (float)$voucherSums[$acc->account_id];
            } elseif (isset($voucherSums[$acc->id])) {
                $bal += (float)$voucherSums[$acc->id];
            }

            $acc->payable_amount = number_format(abs($bal), 2, '.', '');
        }


        // Dropdown With Employee
        $queryEmployee = $shopVendor?->employee()->select('id', 'name', 'last_name')->orderByDesc('id');

        if ($request->has('searchEmployeeName') && $request->searchEmployeeName != '') {
            $searchEmp = trim($request->searchEmployeeName);
            $queryEmployee->where(function($q) use ($searchEmp) {
                $q->where('name', 'like', '%' . $searchEmp . '%')
                    ->orWhere('last_name', 'like', '%' . $searchEmp . '%');
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
