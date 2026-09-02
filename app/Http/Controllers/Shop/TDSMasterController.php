<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\AccountMaster;
use App\Http\Requests\TDSMasterRequest;
use App\Repositories\TDSMasterRepository;
use App\Models\TDSMaster;
use Illuminate\Http\Request;

class TDSMasterController extends Controller
{
    public function index()
    {
        $shop = generaleSetting('shop');

        $tdsMasters = TDSMaster::with('tdsPayable:id,accountshortcode,accountName,account_id','tdsPayable.account:id,name,code','tdsReceivable:id,accountshortcode,accountName,account_id','tdsReceivable.account:id,name,code')->where('shop_id',$shop->id)->paginate(10);

        return view('shop.tds-master.index',compact('tdsMasters'));
    }

    public function create()
    {
        $shop = generaleSetting('shop');
        $accountMasters = AccountMaster::select('id','accountshortcode','accountName','account_id')->with('account:id,name,code')->active()->where('shop_id',$shop->id)->get();
//        dd($accountMasters);
        return view('shop.tds-master.create',compact('accountMasters'));
    }

    public function store(TDSMasterRequest $request)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        TDSMasterRepository::tdsMasterCreate($request);
        return to_route('shop.tdsMaster.index')->withSuccess(__('TDS master created successfully'));
    }

    public function edit(TDSMaster $tdsMaster)
    {
        $shop = generaleSetting('shop');
        $accountMasters = AccountMaster::select('id','accountshortcode','accountName','account_id')->with('account:id,name,code')->active()->where('shop_id',$shop->id)->get();

        return view('shop.tds-master.edit',compact('accountMasters','tdsMaster'));
    }

    public function update(TDSMasterRequest $request, TDSMaster $tdsMaster)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        TDSMasterRepository::tdsMasterUpdate($request, $tdsMaster);

        return to_route('shop.tdsMaster.index')->withSuccess(__('TDS master updated successfully'));
    }

    public function statusToggle(TDSMaster $tdsMaster)
    {
        $tdsMaster->update([
            'is_active' => ! $tdsMaster->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }
}
