@extends('layouts.app')
@section('header-title', __('Counter Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Counter Master')}}
        </h4>
        <div>
            <a href="{{route('shop.counterMaster.create')}}" class="btn py-2 btn-primary">
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
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Counter Name') }}</th>
                                <th>{{ __('Floor / Location') }}</th>
                                <th>{{ __('Voucher Prefix') }}</th>
                                @hasPermission('shop.counterMaster.toggle')
                                <th>{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('shop.counterMaster.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($counterMasters as $key => $counterMaster)
                                @php
                                    $serial = $counterMasters->firstItem() + $key;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td>{{ $counterMaster->code ?? '' }}</td>
                                    <td>{{ $counterMaster->counter_name . ' (' . $counterMaster->counter_short_name . ')' }}</td>
                                    <td>{{ $counterMaster->floor ?? '—' }}</td>
                                    <td>{{ $counterMaster->voucher_prefix ?? '' }}</td>
                                    @hasPermission('shop.counterMaster.toggle')
                                    <td>
                                        <label class="switch mb-0">
                                            <a href="{{ route('shop.counterMaster.toggle', $counterMaster->id) }}" class="toggle-status-link">
                                                <input type="checkbox" {{ $counterMaster->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @endhasPermission
                                    @hasPermission('shop.counterMaster.edit')
                                    <td class="text-center">
                                        <div class="d-flex gap-3 justify-content-center">
                                            <a href="{{route('shop.counterMaster.edit',$counterMaster->id)}}" class="btn btn-outline-primary btn-sm circleIcon editData"
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
                        {{ $counterMasters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection