@extends('layouts.app')
@section('header-title', __('HSN Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('HSN Master')}}
        </h4>
        <div>
            <a href="{{route('shop.hsnMaster.create')}}" class="btn py-2 btn-primary">
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
                                <th>{{ __('HSN Code') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Tax Type') }}</th>
                                <th>{{ __('From Sale') }}</th>
                                <th>{{ __('To Sale') }}</th>
                                <th>{{ __('To Purchase') }}</th>
                                @hasPermission('shop.hsnMaster.toggle')
                                <th>{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('shop.hsnMaster.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($hsnMasters as $key => $hsnMaster)
                                @php
                                    $serial = $hsnMasters->firstItem() + $key;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td>{{ $hsnMaster->hsn_code ?? '' }}</td>
                                    <td>{{ $hsnMaster->hsn_description ?? '' }}</td>
                                    <td>
                                        {{ ($hsnMaster->vattax?->name ?? '') }}
                                        <br>
                                        {{ ($hsnMaster->vattax?->percentage . ' %'  ?? '') }}
                                    </td>

                                    <td>
                                        {{ ($hsnMaster->from_sales_rate ?? '') }}
                                    </td>
                                    <td>
                                        {{ ($hsnMaster->to_sales_rate ?? '') }}
                                    </td>
                                    <td>
                                        {{ ($hsnMaster->to_purchase_rate ?? '') }}
                                    </td>

                                    @hasPermission('shop.hsnMaster.toggle')
                                    <td class="text-center">
                                        <label class="switch mb-0">
                                            <a href="{{ route('shop.hsnMaster.toggle', $hsnMaster->id) }}" class="toggle-status-link">
                                                <input type="checkbox" {{ $hsnMaster->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @endhasPermission
                                    @hasPermission('shop.hsnMaster.edit')
                                    <td class="text-center">
                                        <div class="d-flex gap-3 justify-content-center">
                                            <a href="{{route('shop.hsnMaster.edit',$hsnMaster->id)}}" class="btn btn-outline-primary btn-sm circleIcon editData"
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
                        {{ $hsnMasters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection