@extends('layouts.app')
@section('header-title', __('Account Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Account Master')}}
            @if(isset($search) && $search)
                <span class="badge bg-primary fs-6 ms-2">{{ __('Found') }}: {{ $accountMasters->total() }}</span>
            @endif
        </h4>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="{{ route('shop.accountMaster.index') }}" method="GET" class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ __('Search Party / GSTIN / Mobile...') }}" value="{{ request('search') }}" style="min-width: 250px;">
                    @if(request('search'))
                        <a href="{{ route('shop.accountMaster.index') }}" class="btn btn-outline-secondary border-start-0" title="{{ __('Clear Search') }}">
                            <i class="fa fa-times text-danger"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                </div>
            </form>

            <div>
                <a href="{{route('shop.accountMaster.create')}}" class="btn py-2 btn-primary text-nowrap">
                    <i class="bi bi-patch-plus"></i>
                    {{ __('Create New') }}
                </a>
            </div>
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
                                <th>{{ __('Short Code') }}</th>
                                <th>{{ __('Account Name') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('City') }}</th>
                                <th>{{ __('Contact Person') }}</th>
                                <th>{{ __('Mobile No') }}</th>
                                <th>{{ __('Bank Name') }}</th>
                                <th class="text-center">{{ __('Party Code') }}</th>
                                @hasPermission('shop.accountMaster.toggle')
                                <th>{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('shop.accountMaster.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($accountMasters as $key => $accountMaster)
                                @php
                                    $serial = $accountMasters->firstItem() + $key;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td><span class="badge bg-light-primary text-primary fw-bold font-monospace">{{ $accountMaster->accountshortcode ?: '-' }}</span></td>
                                    <td>{{ $accountMaster->accountName ?? '-' }}</td>
                                    <td>{{ $accountMaster->account ? $accountMaster->account->name . " (" . $accountMaster->account->code . ")" : '-' }}</td>
                                    <td>{{ $accountMaster->city?->name ?? '-' }}</td>
                                    <td>{{ $accountMaster->contperson ?: '-' }}</td>
                                    <td>{{ $accountMaster->cont_info_mobile1 ?: '-' }}</td>
                                    <td>{{ $accountMaster->bank_info_bank_name ?: '-' }}</td>
                                    <td class="text-center">
                                        <label class="switch mb-0" title="{{ __('Toggle Show as Party Code in Dropdowns') }}">
                                            <a href="{{ route('shop.accountMaster.partyCodeToggle', $accountMaster->id) }}" class="toggle-status-link">
                                                <input type="checkbox" {{ $accountMaster->is_party_code ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @hasPermission('shop.accountMaster.toggle')
                                    <td class="text-center">
                                        <label class="switch mb-0">
                                            <a href="{{ route('shop.accountMaster.toggle', $accountMaster->id) }}" class="toggle-status-link">
                                                <input type="checkbox" {{ $accountMaster->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @endhasPermission
                                    @hasPermission('shop.accountMaster.edit')
                                    <td class="text-center">
                                        <div class="d-flex gap-3 justify-content-center">
                                            <a href="{{route('shop.accountMaster.edit',$accountMaster->id)}}" class="btn btn-outline-primary btn-sm circleIcon editData"
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
                        {{ $accountMasters->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection