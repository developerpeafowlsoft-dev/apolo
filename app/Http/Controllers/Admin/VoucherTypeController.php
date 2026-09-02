<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherTypeRequest;
use App\Models\VoucherType;
use App\Repositories\VoucherTypeRepository;
use Illuminate\Http\Request;

class VoucherTypeController extends Controller
{
    public function index()
    {
        $voucherTypes = VoucherType::paginate(10);
        return view('admin/voucher-type/index',compact('voucherTypes'));
    }

    public function store(VoucherTypeRequest $request)
    {
        VoucherTypeRepository::voucherTypeUpadetOrCreate($request);
        return back()->withSuccess(__('Voucher type created successfully'));
    }

    public function edit(VoucherType $voucher)
    {

        if ($voucher->is_default == 0) {
            return redirect()->route('admin.voucherType.index')
                ->with('error', __('You are not allowed to edit this voucher.'));
        }
        $voucherTypes = VoucherType::paginate(10);
        return view('admin/voucher-type/index',compact('voucherTypes','voucher'));
    }

    public function statusToggle(VoucherType $voucher)
    {
        $voucher->update([
            'is_active' => ! $voucher->is_active,
        ]);

        return back()->withSuccess(__('Voucher type status updated'));
    }

    public function update(VoucherTypeRequest $request, VoucherType $voucher)
    {
        if ($voucher->is_default == 0) {
            return redirect()->route('admin.voucherType.index')
                ->with('error', __('You are not allowed to edit this voucher.'));
        }
        VoucherTypeRepository::voucherTypeUpadetOrCreate($request);
        return redirect()->route('admin.voucherType.index')->withSuccess(__('Voucher type updated successfully'));
    }
}
