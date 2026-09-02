@extends('layouts.app')

@section('title', __('Financial Year'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center justify-content-between">
            <div>
                <i class="bi bi-graph-up-arrow"></i> {{ __('Financial Year') }}
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
                                    <th>{{ __('Financial Name') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Current Year') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @forelse($financialYears as $key => $financialYear)
                                        @php
                                            $serial = $financialYears->firstItem() + $key;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $serial }}</td>
                                            <td>{{ $financialYear->name ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($financialYear->start_date)->format('d-m-Y') ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($financialYear->end_date)->format('d-m-Y') ?? '' }}</td>
                                            <td>
                                                {!! (now()->between(
                                                        \Carbon\Carbon::parse($financialYear->start_date),
                                                        \Carbon\Carbon::parse($financialYear->end_date)
                                                    ))
                                                    ? '<span class="badge bg-primary fs-6"><i class="bi bi-check"></i> Current</span>'
                                                    : ''
                                                !!}
                                            </td>
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
                            {{ $financialYears->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection