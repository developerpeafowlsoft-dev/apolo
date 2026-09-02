@extends('layouts.app')
@section('header-title', __('TDS Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('TDS Master')}}
        </h4>
        <div>
            <a href="{{route('shop.tdsMaster.create')}}" class="btn py-2 btn-primary">
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
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Payable Account') }}</th>
                                <th>{{ __('Receivable Account') }}</th>
                                <th>{{ __('From Date') }}</th>
                                <th>{{ __('To Date') }}</th>
                                <th>{{ __('Percentage') }}</th>
                                @hasPermission('shop.tdsMaster.toggle')
                                <th>{{ __('Status') }}</th>
                                @endhasPermission
                                @hasPermission('shop.tdsMaster.edit')
                                <th class="text-center">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($tdsMasters as $key => $tdsMaster)
                                @php
                                    $serial = $tdsMasters->firstItem() + $key;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td>{{ $tdsMaster->tds_code ?? '' }}</td>
                                    <td>{{ $tdsMaster->tds_description ?? '' }}</td>
                                    <td>
                                        {{ ($tdsMaster->tdsPayable?->accountshortcode ?? '') . ' | ' . ($tdsMaster->tdsPayable?->accountName ?? '') }}
                                        <br>
                                        {{ ($tdsMaster->tdsPayable?->account?->name ?? '') . ' (' . ($tdsMaster->tdsPayable?->account?->code ?? '') . ')' }}
                                    </td>

                                    <td>
                                        {{ ($tdsMaster->tdsReceivable?->accountshortcode ?? '') . ' | ' . ($tdsMaster->tdsReceivable?->accountName ?? '') }}
                                        <br>
                                        {{ ($tdsMaster->tdsReceivable?->account?->name ?? '') . ' (' . ($tdsMaster->tdsReceivable?->account?->code ?? '') . ')' }}
                                    </td>

                                    <td>{{ $tdsMaster->from_date->format('d-m-Y') ?? '' }}</td>
                                    <td>{{ $tdsMaster->to_date->format('d-m-Y') ?? '' }}</td>
                                    <td>{{ $tdsMaster->tds_percentage ?? '' }}</td>
                                    @hasPermission('shop.tdsMaster.toggle')
                                    <td class="text-center">
                                        <label class="switch mb-0">
                                            <a href="{{ route('shop.tdsMaster.toggle', $tdsMaster->id) }}" class="toggle-status-link">
                                                <input type="checkbox" {{ $tdsMaster->is_active ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </a>
                                        </label>
                                    </td>
                                    @endhasPermission
                                    @hasPermission('shop.tdsMaster.edit')
                                    <td class="text-center">
                                        <div class="d-flex gap-3 justify-content-center">
                                            <a href="{{route('shop.tdsMaster.edit',$tdsMaster->id)}}" class="btn btn-outline-primary btn-sm circleIcon editData"
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
                        {{ $tdsMasters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection