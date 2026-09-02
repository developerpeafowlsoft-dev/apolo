<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CounterMasterRequest;
use App\Repositories\CounterMasterRepository;
use App\Models\CounterMaster;

class CounterMasterController extends Controller
{
    public function index()
    {
        $shop = generaleSetting('shop');

        $counterMasters = CounterMaster::where('shop_id',$shop->id)->paginate(10);

        return view('shop.counter-master.index',compact('counterMasters'));
    }

    public function create()
    {
        $shop = generaleSetting('shop');
        return view('shop.counter-master.create');
    }

    public function store(CounterMasterRequest $request)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        CounterMasterRepository::counterMasterCreate($request);
        return to_route('shop.counterMaster.index')->withSuccess(__('Counter master created successfully'));
    }

    public function edit(CounterMaster $counterMaster)
    {
        $shop = generaleSetting('shop');

        return view('shop.counter-master.edit',compact('counterMaster'));
    }

    public function update(CounterMasterRequest $request, CounterMaster $counterMaster)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        CounterMasterRepository::counterMasterUpdate($request, $counterMaster);

        return to_route('shop.counterMaster.index')->withSuccess(__('Counter master updated successfully'));
    }

    public function statusToggle(CounterMaster $counterMaster)
    {
        $counterMaster->update([
            'is_active' => ! $counterMaster->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }
}
