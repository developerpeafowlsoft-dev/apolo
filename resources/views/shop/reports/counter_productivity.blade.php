@extends('layouts.app')

@section('title', __('Counter & Cashier Productivity Matrix'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-cash-register me-2"></i>{{ __('Counter & Cashier Productivity Matrix') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Billing volume, cash vs digital payment split, and average transaction values per counter') }}</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.counterProductivity') }}" class="row g-2 align-items-center">
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('From Date:') }}</label></div>
                <div class="col-auto"><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}"></div>
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('To Date:') }}</label></div>
                <div class="col-auto"><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}"></div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary fw-bold px-3">{{ __('Generate Productivity Report') }}</button></div>
            </form>
        </div>
    </div>

    <!-- Productivity Matrix Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="fa-solid fa-list-check me-2"></i> {{ __('Counter Performance & Collection Breakdown') }}
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('Counter Name') }}</th>
                            <th class="text-end">{{ __('Total Orders') }}</th>
                            <th class="text-end">{{ __('Total Revenue') }}</th>
                            <th class="text-end">{{ __('Avg Order Value (AOV)') }}</th>
                            <th class="text-end">{{ __('Cash Collection') }}</th>
                            <th class="text-end">{{ __('Digital Collection') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productivity as $prod)
                        <tr>
                            <td class="fw-bold"><i class="fa-solid fa-desktop text-primary me-2"></i>{{ $prod['counter_name'] }}</td>
                            <td class="text-end font-monospace fw-bold">{{ $prod['total_orders'] }}</td>
                            <td class="text-end font-monospace fw-bold text-success">₹{{ number_format($prod['total_revenue'], 2) }}</td>
                            <td class="text-end font-monospace">₹{{ number_format($prod['avg_order_value'], 2) }}</td>
                            <td class="text-end font-monospace text-primary">₹{{ number_format($prod['cash_sales'], 2) }}</td>
                            <td class="text-end font-monospace text-info">₹{{ number_format($prod['digital_sales'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">{{ __('No counter performance data available for selected period') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
