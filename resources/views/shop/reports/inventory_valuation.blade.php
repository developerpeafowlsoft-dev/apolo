@extends('layouts.app')

@section('title', __('Closing Inventory Valuation (WAC)'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-boxes-stacked me-2"></i>{{ __('Closing Inventory Valuation') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Authoritative Canonical Physical SKU Valuation based on Inwards, GL Purchases, and Active Reservations') }}</p>
        </div>
        <div>
            <a href="{{ route('shop.reports.accountingDashboard') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Hub') }}
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Accounting Owned Asset Value') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($valuationData['total_accounting_owned_value'] ?? $valuationData['total_inventory_asset_value'] ?? 0, 2) }}</h3>
                    <div class="small text-white-50">{{ __('Total owned inventory for Balance Sheet') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Immediately Available Stock') }}</div>
                    <h3 class="fw-bold my-1">{{ number_format($valuationData['total_stock_quantity'] ?? 0) }} {{ __('Units') }}</h3>
                    <div class="small text-white-50">₹{{ number_format($valuationData['total_available_value'] ?? 0, 2) }} {{ __('available asset value') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body p-3">
                    <div class="small text-muted text-uppercase fw-bold">{{ __('Pending Web Reservations') }}</div>
                    <h3 class="fw-bold my-1">{{ number_format($valuationData['total_reserved_quantity'] ?? 0) }} {{ __('Units') }}</h3>
                    <div class="small text-muted">₹{{ number_format($valuationData['total_reserved_value'] ?? 0, 2) }} {{ __('reserved in unshipped orders') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Valuation Method') }}</div>
                    <h6 class="fw-bold my-1"><i class="fa-solid fa-calculator me-1"></i> {{ __('Canonical SKU WAC') }}</h6>
                    <div class="small text-white-50">{{ __('Consolidated batch inward purchase rates') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Valuation Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-list me-1"></i> {{ __('Canonical Physical SKU Stock & Valuation Breakdown') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small mb-1">{{ __('Find a product') }}</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control"
                   placeholder="{{ __('Type part of a product name') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary">{{ __('Search') }}</button>
            <a href="{{ route('shop.reports.inventoryValuation') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
        <div class="col-md-4 text-md-end small text-muted">
            {{ __('Showing') }} {{ number_format($products->count()) }} {{ __('of') }} {{ number_format($products->total()) }} {{ __('SKUs') }}
        </div>
    </form>

                <table class="table table-hover table-striped align-middle mb-0 text-nowrap">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('Canonical Physical SKU') }}</th>
                            <th>{{ __('Mapped Product IDs') }}</th>
                            <th class="text-end">{{ __('Purchased') }}</th>
                            <th class="text-end">{{ __('Sold') }}</th>
                            <th class="text-end">{{ __('Reserved') }}</th>
                            <th class="text-end">{{ __('Accounting Owned') }}</th>
                            <th class="text-end">{{ __('Available') }}</th>
                            <th class="text-end">{{ __('Unit Cost (WAC)') }}</th>
                            <th class="text-end">{{ __('Accounting Owned Value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $item)
                        <tr>
                            <td class="fw-bold text-dark">{{ $item['name'] }}</td>
                            <td>
                                @foreach($item['alias_product_ids'] ?? [$item['product_id']] as $aliasId)
                                    <span class="badge bg-light text-dark border font-monospace me-1">#{{ $aliasId }}</span>
                                @endforeach
                            </td>
                            <td class="text-end font-monospace">{{ number_format($item['purchased_quantity'] ?? 0) }}</td>
                            <td class="text-end font-monospace text-danger">{{ number_format($item['sold_quantity'] ?? 0) }}</td>
                            <td class="text-end font-monospace text-warning fw-bold">{{ number_format($item['reserved_quantity'] ?? 0) }}</td>
                            <td class="text-end font-monospace fw-bold text-primary">{{ number_format($item['accounting_owned_quantity'] ?? $item['stock_quantity']) }}</td>
                            <td class="text-end font-monospace fw-bold text-success">{{ number_format($item['stock_quantity']) }}</td>
                            <td class="text-end font-monospace text-secondary">₹{{ number_format($item['weighted_avg_cost'], 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-primary">₹{{ number_format($item['asset_value'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted py-3">{{ __('No inventory products found') }}</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td colspan="5">{{ __('Total Inventory Valuation') }}</td>
                            <td class="text-end font-monospace text-primary fs-6">{{ number_format($valuationData['total_accounting_owned_quantity'] ?? 0) }}</td>
                            <td class="text-end font-monospace text-success fs-6">{{ number_format($valuationData['total_stock_quantity'] ?? 0) }}</td>
                            <td class="text-end">-</td>
                            <td class="text-end font-monospace text-primary fs-6">₹{{ number_format($valuationData['total_accounting_owned_value'] ?? $valuationData['total_inventory_asset_value'] ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

    <div class="mt-3">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
