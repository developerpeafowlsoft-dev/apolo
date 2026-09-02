<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountBalanceRequest;
use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\FinancialYear;
use App\Repositories\AccountBalanceRepository;
use Illuminate\Http\Request;

class AccountBalanceController extends Controller
{
    public function index()
    {
        $shop = generaleSetting('shop');

        $accounts = Account::active()->get();
        $accountBalances = AccountBalance::with('account:id,name,code','financialYear:id,name')->where('shop_id',$shop->id)->paginate(10);
        
        return view('shop.account-balance.index',compact('accounts','accountBalances'));
    }

    public function store(AccountBalanceRequest $request)
    {
        $shop = generaleSetting('shop');

        $financialYear = FinancialYear::select('id','name')->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        $request->merge(['shop_id' => $shop->id]);
        $request->merge(['financial_year_id' => $financialYear->id]);

        AccountBalanceRepository::accountBalanaceCreate($request);
        return back()->withSuccess(__('Account balance created successfully'));

    }
}
