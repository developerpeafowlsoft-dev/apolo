@extends('layouts.app')

@section('title', __('Visual Analytics & Dynamic Sales Dashboard'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-chart-pie me-2"></i>{{ __('Visual Analytics & Performance Dashboard') }}</h4>
            <p class="text-muted mb-0 small">{{ __('G-Soft / Marg Standard Retail Sales Trends, Payment Breakdown, Top Products & RFM Segmentation') }}</p>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.visualAnalytics') }}" class="row g-2 align-items-center">
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('From Date:') }}</label></div>
                <div class="col-auto"><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}"></div>
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('To Date:') }}</label></div>
                <div class="col-auto"><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}"></div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary fw-bold px-3">{{ __('Analyze') }}</button></div>
            </form>
        </div>
    </div>

    <!-- RFM Customer Segmentation Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('VIP Customers (Platinum)') }}</div>
                    <h2 class="fw-bold my-1">{{ $rfm['vip_customers'] }}</h2>
                    <div class="small">{{ __('High Spend / High Frequency') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Frequent Buyers') }}</div>
                    <h2 class="fw-bold my-1">{{ $rfm['frequent_customers'] }}</h2>
                    <div class="small">{{ __('Active within 30 Days') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3 text-center">
                    <div class="small text-uppercase fw-bold">{{ __('At Risk Customers') }}</div>
                    <h2 class="fw-bold my-1">{{ $rfm['at_risk_customers'] }}</h2>
                    <div class="small">{{ __('Inactive 30 - 90 Days') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Churned / Lost') }}</div>
                    <h2 class="fw-bold my-1">{{ $rfm['churned_customers'] }}</h2>
                    <div class="small">{{ __('Inactive > 90 Days') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="fa-solid fa-fire me-2 text-warning"></i> {{ __('Fast Moving Products & Stock Status') }}
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('Product Name') }}</th>
                                    <th class="text-end">{{ __('Total Quantity Sold') }}</th>
                                    <th class="text-end">{{ __('Remaining Available Stock') }}</th>
                                    <th>{{ __('Stock Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['top_products'] as $tp)
                                <tr>
                                    <td class="fw-bold">{{ $tp['name'] }}</td>
                                    <td class="text-end font-monospace fw-bold text-success">{{ $tp['sold_count'] }} Pcs</td>
                                    <td class="text-end font-monospace fw-bold">{{ $tp['stock'] }} Pcs</td>
                                    <td>
                                        @if($tp['stock'] > 10)
                                            <span class="badge bg-success">{{ __('Sufficient Stock') }}</span>
                                        @elseif($tp['stock'] > 0)
                                            <span class="badge bg-warning text-dark">{{ __('Low Stock Warning') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Out of Stock') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ __('No product sales recorded for selected period') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
