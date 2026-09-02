@extends('layouts.app')
@section('header-title', __('Bank Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Bank Master')}}
        </h4>
        <div>
            <a href="{{route('shop.bankMaster.create')}}" class="btn py-2 btn-primary">
                <i class="bi bi-patch-plus"></i>
                {{ __('Create New') }}
            </a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table border-left-right table-responsive-md">
                            <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Bank Name') }}</th>
                                <th>{{ __('Account Group') }}</th>
                                <th>{{ __('Account No.') }}</th>
                                <th>{{ __('Branch Name') }}</th>
                                @hasPermission('shop.bankMaster.toggle')
                                <th>{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('shop.bankMaster.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($bankMasters as $key => $bankMaster)
                                @php
                                    $serial = $bankMasters->firstItem() + $key;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td>{{ $bankMaster->bank_name . "(" . $bankMaster->short_name . ")" ?? '' }}</td>
                                    <td>{{ $bankMaster->accountGroup?->name . " (" . $bankMaster->accountGroup?->code . ")" ?? '' }}</td>
                                    <td>{{ $bankMaster->bank_ac_no ?? '' }}</td>
                                    <td>{{ $bankMaster->bank_branch ?? '' }}</td>
                                    @hasPermission('shop.bankMaster.toggle')
                                    <td class="text-center">
                                        <label class="switch mb-0">
                                            <a href="{{ route('shop.bankMaster.toggle', $bankMaster->id) }}" class="toggle-status-link">
                                                <input type="checkbox" {{ $bankMaster->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @endhasPermission
                                    @hasPermission('shop.bankMaster.edit')
                                    <td class="text-center">
                                        <div class="d-flex gap-3 justify-content-center">
                                            <a href="{{route('shop.bankMaster.edit',$bankMaster->id)}}" class="btn btn-outline-primary btn-sm circleIcon editData"
                                            >
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" />
                                            </a>
                                        </div>
                                    </td>
                                    @endhasPermission
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="100%">{{ __('No Data Found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="my-3">
                        {{ $bankMasters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection