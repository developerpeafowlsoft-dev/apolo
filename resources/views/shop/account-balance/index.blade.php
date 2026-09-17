@extends('layouts.app')
@section('header-title', __('Account Balance'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Account Balance')}}
        </h4>
    </div>

        <form id="formData" action="{{ route('shop.accountBalance.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mt-4 mt-lg-0">
                        <x-select label="Account" name="account_id" required="true">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->code }})
                                </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-input type="number" name="acOpenBalance" label="Opening Balance" placeholder="Enter Opening Balance" value="{{ old('acOpenBalance','0.00') }}" required="true"/>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-input type="number" name="acCloseBalance" label="Closing Balance" placeholder="Enter Closing Balance" value="{{ old('acCloseBalance','0.00') }}" required="true"/>
                    </div>
                </div>
            </div>
        </div>
        @hasPermission('shop.accountBalance.store')
        <div class="d-flex justify-content-end mt-4 mb-3">
            <button type="submit" class="btn btn-primary py-2.5 px-3">
                {{ __('Save') }}
            </button>
        </div>
        @endhasPermission
    </form>

    <div class="card mt-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="mb-0 fw-bold">
                {{ __('Account Opening Balances') }}
                @if(isset($search) && $search)
                    <span class="badge bg-primary fs-6 ms-2">{{ __('Found') }}: {{ $accountBalances->total() }}</span>
                @else
                    <span class="badge bg-secondary fs-6 ms-2">{{ __('Total') }}: {{ $accountBalances->total() }}</span>
                @endif
            </h5>

            <form action="{{ route('shop.accountBalance.index') }}" method="GET" class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="{{ __('Search Account / Code...') }}" value="{{ request('search') }}" style="min-width: 250px;">
                    @if(request('search'))
                        <a href="{{ route('shop.accountBalance.index') }}" class="btn btn-outline-secondary border-start-0" title="{{ __('Clear Search') }}">
                            <i class="fa fa-times text-danger"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table border-left-right table-responsive-md">
                            <thead>
                            <tr>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Financial Year') }}</th>
                                <th>{{ __('Opening Balance') }}</th>
                                <th>{{ __('Closing Balance') }}</th>
                                <th>{{ __('Created') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($accountBalances as $key => $accountBalance)
                                @php
                                    $serial = $accountBalances->firstItem() + $key;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $serial }}</td>
                                    <td>{{ $accountBalance->account?->name . " (" . $accountBalance->account?->code . ")" ?? '' }}</td>
                                    <td>{{ $accountBalance->financialYear?->name ?? '' }}</td>
                                    <td>{{ $accountBalance->opening_balance ?? '' }}</td>
                                    <td>{{ $accountBalance->closing_balance ?? '' }}</td>
                                    <td>{{ $accountBalance->created_at?->format('d-m-Y') ?? '' }}</td>
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
                        {{ $accountBalances->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection