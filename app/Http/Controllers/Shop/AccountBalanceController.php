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
    public function index(Request $request)
    {
        $rootShop = generaleSetting('rootShop');
        $currentShop = generaleSetting('shop');
        $shopIds = array_filter(array_unique([$rootShop?->id, $currentShop?->id, 1, 14]));
        $search = $request->search ?? null;

        $accounts = Account::active()->orderBy('name')->get();
        
        $accountBalances = AccountBalance::with('account:id,name,code', 'financialYear:id,name')
            ->whereIn('shop_id', $shopIds)
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('account', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
        
        return view('shop.account-balance.index', compact('accounts', 'accountBalances', 'search'));
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
