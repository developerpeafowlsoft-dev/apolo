<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SalesmanRequest;
use App\Repositories\SalesmanRepository;
use App\Models\Salesman;

class SalesmanController extends Controller
{
    public function index()
    {
        $shop = generaleSetting('shop');
        $salesmans = Salesman::where('shop_id',$shop->id)->paginate(10);

        return view('shop.salesman.index',compact('salesmans'));
    }

    public function create()
    {
        $shop = generaleSetting('shop');
//        $bankMasters = BankMaster::basicFields()->with(['accountGroup:id,name,code'])->where('shop_id',$shop->id)->paginate(10);

        return view('shop.salesman.create');
    }

    public function store(SalesmanRequest $request)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        SalesmanRepository::salesmanCreate($request);
        return to_route('shop.salesman.index')->withSuccess(__('Salesman created successfully'));
    }

    public function edit(Salesman $salesman)
    {
        $shop = generaleSetting('shop');

        return view('shop.salesman.edit',compact('salesman'));
    }
//
    public function update(SalesmanRequest $request, Salesman $salesman)
    {
        $shop = generaleSetting('shop');
        $request->merge(['shop_id' => $shop->id]);

        SalesmanRepository::salesmanUpdate($request, $salesman);

        return to_route('shop.salesman.index')->withSuccess(__('Salesman updated successfully'));
    }

    public function statusToggle(Salesman $salesman)
    {
        $salesman->update([
            'is_active' => ! $salesman->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }
}
