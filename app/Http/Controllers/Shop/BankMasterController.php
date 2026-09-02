<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use App\Models\BankMaster;
use App\Http\Requests\BankMasterRequest;
use App\Repositories\BankMasterRepository;
use Illuminate\Http\Request;

class BankMasterController extends Controller
{
    public function index()
    {
        $shop = generaleSetting('shop');
        $bankMasters = BankMaster::basicFields()->with(['accountGroup:id,name,code'])->where('shop_id',$shop->id)->paginate(10);

        return view('shop.bank-master.index',compact('bankMasters'));
    }

    public function create()
    {
        $shop = generaleSetting('shop');
        $accounts_groups = AccountGroup::active()->get();
        return view('shop.bank-master.create',compact('accounts_groups'));
    }

    public function store(BankMasterRequest $request)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        BankMasterRepository::bankMasterCreate($request);
        return to_route('shop.bankMaster.index')->withSuccess(__('Bank master created successfully'));
    }

    public function edit(BankMaster $bankMaster)
    {
        $shop = generaleSetting('shop');
        $accounts_groups = AccountGroup::active()->get();

        return view('shop.bank-master.edit',compact('accounts_groups','bankMaster'));
    }

    public function update(BankMasterRequest $request, BankMaster $bankMaster)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        BankMasterRepository::bankMasterUpdate($request, $bankMaster);

        return to_route('shop.bankMaster.index')->withSuccess(__('Bank master updated successfully'));
    }

    public function statusToggle(BankMaster $bankMaster)
    {
        $bankMaster->update([
            'is_active' => ! $bankMaster->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }
}
