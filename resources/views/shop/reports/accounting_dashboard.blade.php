@extends('layouts.app')

@section('title', __('Accounting & Finance Hub'))

@section('content')
<div class="content-body p-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-calculator me-2"></i>{{ __('Accounting & Financial Control Center') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Indian Double-Entry Accounting, Statutory Reports, Ledger Statements & Reconciliations') }}</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fw-bold">
                <i class="fa-solid fa-circle-check me-1"></i> {{ __('Accounting Engine Active') }}
            </span>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.accountingDashboard') }}" class="row g-2 align-items-center">
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('Accounting Period:') }}</label></div>
                <div class="col-auto"><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}"></div>
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('To Date:') }}</label></div>
                <div class="col-auto"><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}"></div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary fw-bold px-3"><i class="fa-solid fa-filter me-1"></i> {{ __('Filter') }}</button></div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50 text-uppercase fw-bold">{{ __('Net Sales (GL Recognized)') }}</div>
                            <h4 class="fw-bold my-1">₹{{ number_format($pnl['revenue']['net_sales'] ?? 0, 2) }}</h4>
                            <div class="small text-white-50">{{ __('Product Sales + Delivery Income') }}</div>
                        </div>
                        <div><i class="fa-solid fa-chart-line fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50 text-uppercase fw-bold">{{ __('Gross Profit') }}</div>
                            <h4 class="fw-bold my-1">₹{{ number_format($pnl['gross_profit'] ?? 0, 2) }}</h4>
                            <div class="small text-white-50">{{ __('Sales less Sold Goods Cost') }}</div>
                        </div>
                        <div><i class="fa-solid fa-coins fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50 text-uppercase fw-bold">{{ __('Closing Stock Asset') }}</div>
                            <h4 class="fw-bold my-1">₹{{ number_format($valuationData['total_inventory_asset_value'] ?? 0, 2) }}</h4>
                            <div class="small text-white-50">{{ number_format($valuationData['total_available_units'] ?? 0) }} {{ __('Units Available') }}</div>
                        </div>
                        <div><i class="fa-solid fa-warehouse fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50 text-uppercase fw-bold">{{ __('Total Vouchers') }}</div>
                            <h4 class="fw-bold my-1">{{ number_format($vouchersCount) }}</h4>
                            <div class="small text-white-50">{{ $accountsCount }} {{ __('Ledger Accounts in Chart') }}</div>
                        </div>
                        <div><i class="fa-solid fa-receipt fa-2x text-white-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Module Cards Grid -->
    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-cubes me-2"></i>{{ __('Accounting Modules & Sub-Systems') }}</h5>
    <div class="row g-3">
        <!-- 1. Vouchers & General Journal -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-circle me-2"><i class="fa-solid fa-receipt"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('Vouchers & Journal Entries') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('View and audit double-entry Sales, Purchases, Payments, Receipts, Debit Notes, and Journal vouchers.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.vouchers') }}" class="btn btn-sm btn-outline-primary fw-bold w-100">{{ __('Open Voucher Explorer') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 2. General Ledger -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info-subtle text-info p-2 rounded-circle me-2"><i class="fa-solid fa-book"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('General Ledger Statements') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('Inspect individual account statements with opening balance, chronological Dr/Cr movements, and running balance.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.generalLedger') }}" class="btn btn-sm btn-outline-info fw-bold w-100">{{ __('Open General Ledger') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 3. Financial Statements -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success-subtle text-success p-2 rounded-circle me-2"><i class="fa-solid fa-scale-balanced"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('Trial Balance, P&L & Balance Sheet') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('Statutory financial statements with balanced debit/credit verification, weighted COGS, and asset/liability schedules.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.financialStatements') }}" class="btn btn-sm btn-outline-success fw-bold w-100">{{ __('View Financial Statements') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 4. Purchase Returns & Debit Notes -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-warning-subtle text-warning p-2 rounded-circle me-2"><i class="fa-solid fa-truck-ramp-box"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('Purchase Returns & Debit Notes') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('Process vendor returns, reduce physical barcode inventory, reverse Input GST ITC, and generate Debit Notes.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.purchaseReturns') }}" class="btn btn-sm btn-outline-warning fw-bold w-100">{{ __('Manage Purchase Returns') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 5. Supplier Payments -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-danger-subtle text-danger p-2 rounded-circle me-2"><i class="fa-solid fa-money-check-dollar"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('Supplier Bill Settlements') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('Bill-by-bill vendor payment allocation, invoice outstanding tracking, and cash/bank disbursement vouchers.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.supplierPayments') }}" class="btn btn-sm btn-outline-danger fw-bold w-100">{{ __('Settle Supplier Bills') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 6. COD Remittance & Reconciliation -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary-subtle text-secondary p-2 rounded-circle me-2"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('COD Remittance & Settlement') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('Track E-Commerce COD receivables (CUST_WEB), record courier bank remittances, and account logistics fees.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.codReconciliation') }}" class="btn btn-sm btn-outline-secondary fw-bold w-100">{{ __('Reconcile COD') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 7. GST Statutory Reports -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-purple-subtle text-primary p-2 rounded-circle me-2"><i class="fa-solid fa-file-invoice"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('GST Returns (GSTR-1, 3B, ITC)') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('Indian GST statutory filing tables, B2B/B2C breakup, GSTR-1 JSON export, and Input Tax Credit reconciliation.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.gstDashboard') }}" class="btn btn-sm btn-outline-primary fw-bold w-100">{{ __('Open GST Dashboard') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 8. Inventory Valuation -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-dark-subtle text-dark p-2 rounded-circle me-2"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('Inventory Valuation (WAC)') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('SKU-wise stock valuation based on Weighted Average Cost of inward batches and active available quantities.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.inventoryValuation') }}" class="btn btn-sm btn-outline-dark fw-bold w-100">{{ __('View Inventory Valuation') }} &rarr;</a>
                </div>
            </div>
        </div>

        <!-- 9. Outstanding Ageing & Party Balances -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-circle me-2"><i class="fa-solid fa-clock-rotate-left"></i></span>
                            <h6 class="fw-bold mb-0">{{ __('Outstanding Ageing (0-30-60-90+)') }}</h6>
                        </div>
                        <p class="text-muted small mb-3">{{ __('Aging analysis for Sundry Debtors (Customers) and Sundry Creditors (Suppliers) with party balances.') }}</p>
                    </div>
                    <a href="{{ route('shop.reports.outstandingAgeing') }}" class="btn btn-sm btn-outline-primary fw-bold w-100">{{ __('View Outstanding Ageing') }} &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
