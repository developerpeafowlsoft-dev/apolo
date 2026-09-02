<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Repositories\AccountGroupRepository;
use App\Http\Requests\AccountGroupRequest;

class AccountManagementController extends Controller
{
    public function index()
    {
        $accounts = AccountType::active()->get();
        $accountTables = AccountGroup::with('accountType')->paginate(10);
        return view('admin/account-management/index',compact('accounts','accountTables'));
    }

    public function store(AccountGroupRequest $request)
    {
        AccountGroupRepository::accountAddorUpdate($request);
        return back()->withSuccess(__('Account group created successfully'));
    }

    public function edit(AccountGroup $account)
    {

        if ($account->is_default == 0) {
            return redirect()->route('admin.account.index')
                ->with('error', __('You are not allowed to edit this account.'));
        }
        $accounts = AccountType::active()->get();
        $accountTables = AccountGroup::with('accountType')->paginate(10);
        return view('admin/account-management/index',compact('accounts','accountTables','account'));
    }

    public function statusToggle(AccountGroup $account)
    {
        $account->update([
            'is_editable' => ! $account->is_editable,
        ]);

        return back()->withSuccess(__('Account group status updated'));
    }

    public function update(AccountGroupRequest $request, AccountGroup $account)
    {
        if ($account->is_default == 0) {
            return redirect()->route('admin.account.index')
                ->with('error', __('You are not allowed to edit this account.'));
        }

        AccountGroupRepository::accountAddorUpdate($request);

        return redirect()->route('admin.account.index')->withSuccess(__('Account group updated successfully'));
    }

//    public function destroy(AccountGroup $account)
//    {
//        $account->delete();
//
//        return redirect()->route('admin.account.index')
//            ->with('success', __('Account deleted successfully'));
//    }

}
