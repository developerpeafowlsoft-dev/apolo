<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Http\Requests\AccountTypeRequest;
use App\Models\AccountType;
use App\Repositories\AccountTypeRepository;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    public function index()
    {
        $accountTypes = AccountType::paginate(10);
        return view('admin/admin-type/index',compact('accountTypes'));
    }

    public function store(AccountTypeRequest $request)
    {
        AccountTypeRepository::accountTypeUpadetOrCreate($request);
        return back()->withSuccess(__('Account type created successfully'));

    }

    public function edit(AccountType $account)
    {

        if ($account->is_default == 0) {
            return redirect()->route('admin.accountType.index')
                ->with('error', __('You are not allowed to edit this account.'));
        }
        $accountTypes = AccountType::paginate(10);
        return view('admin/admin-type/index',compact('accountTypes','account'));
    }

    public function statusToggle(AccountType $account)
    {
        $account->update([
            'is_active' => ! $account->is_active,
        ]);

        return back()->withSuccess(__('Account type status updated'));
    }

    public function update(AccountTypeRequest $request, AccountType $account)
    {
        if ($account->is_default == 0) {
            return redirect()->route('admin.account.index')
                ->with('error', __('You are not allowed to edit this account.'));
        }
        AccountTypeRepository::accountTypeUpadetOrCreate($request);
        return redirect()->route('admin.accountType.index')->withSuccess(__('Account type updated successfully'));
    }
}
