<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Http\Requests\AccountRequest;
use App\Repositories\AccountRepository;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accountGroups = AccountGroup::active()->get();
        $accountsTables = Account::with('accountGroup','accountGroup.accountType')->paginate(10);

        return view('admin/account/index',compact('accountGroups', 'accountsTables'));
    }

    public function store(AccountRequest $request)
    {
        AccountRepository::accountsAddorUpdate($request);
        return back()->withSuccess(__('Account created successfully'));
    }

    public function edit(Account $account)
    {
        if ($account->is_default == 0) {
            return redirect()->route('admin.accounts.index')
                ->with('error', __('You are not allowed to edit this account.'));
        }

        $accountGroups = AccountGroup::active()->get();
        $accountsTables = Account::with('accountGroup','accountGroup.accountType')->paginate(10);
        return view('admin/account/index',compact('accountGroups','accountsTables','account'));
    }

    public function statusToggle(Account $account)
    {
        $account->update([
            'is_active' => ! $account->is_active,
        ]);

        return back()->withSuccess(__('Account status updated'));
    }

    public function update(AccountRequest $request, Account $account)
    {
        AccountRepository::accountsAddorUpdate($request);

        return redirect()->route('admin.accounts.index')->withSuccess(__('Account updated successfully'));
    }
}
