@extends('layouts.app')

@section('header-title', __('Supplier Credit Due Payments'))

@section('content')
<style>
    /* Dynamic Theme Color Integration */
    .filter-card {
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .custom-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .custom-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .custom-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .custom-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Badges & Avatars */
    .badge-theme {
        background-color: var(--theme-hover-bg) !important;
        color: var(--theme-color) !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
    }

    .supplier-avatar {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: var(--theme-hover-bg) !important;
        color: var(--theme-color) !important;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
    }
    .status-pill-overdue {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .status-pill-today {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .status-pill-upcoming {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    .status-pill-later {
        background-color: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .status-pill-settled {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }

    /* Buttons */
    .btn-theme-primary {
        background: var(--theme-color) !important;
        border-color: var(--theme-color) !important;
        color: #ffffff !important;
        white-space: nowrap !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.2s ease;
    }
    .btn-theme-primary:hover {
        filter: brightness(0.92);
        color: #ffffff !important;
    }

    .modal-theme-header {
        background: var(--theme-color) !important;
        color: #ffffff !important;
    }

    .modal-summary-box {
        background: var(--theme-hover-bg) !important;
        border: 1px solid rgba(0, 0, 0, 0.06) !important;
    }

    .quick-preset-chip {
        cursor: pointer;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        color: #475569;
        transition: all 0.15s ease;
    }
    .quick-preset-chip:hover,
    .quick-preset-chip.active {
        border-color: var(--theme-color) !important;
        color: var(--theme-color) !important;
        background: var(--theme-hover-bg) !important;
    }
</style>

<div class="content-body p-3">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                {{ __('Supplier Credit Due Payments') }}
            </h4>
            <p class="text-muted small mb-0">
                {{ __('Real-time tracking of inward credit periods (e.g., 15 days), overdue liabilities, and direct settlement via Bank Master.') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shop.bankMaster.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="height: 36px; border-radius: 6px;">
                <i class="bi bi-building"></i> {{ __('Bank Master') }}
            </a>
            <a href="{{ route('shop.inwardProduct.index') }}" class="btn btn-theme-primary btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="height: 36px; border-radius: 6px;">
                <i class="bi bi-plus-lg"></i> {{ __('New Inward Entry') }}
            </a>
        </div>
    </div>

    <!-- KPI Summary Box (Native Apolo Dashboard Box) -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="dashboard-box item-1">
                        <h2 class="count">₹{{ number_format($metrics['totalCreditPayables'], 2) }}</h2>
                        <h3 class="title">{{ __('Total Credit Payables') }}</h3>
                        <div class="icon">
                            <img src="{{ asset('assets/icons-admin/wallet.svg') }}" alt="icon" loading="lazy" />
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="dashboard-box item-3">
                        <h2 class="count">₹{{ number_format($metrics['overdueAmount'], 2) }}</h2>
                        <h3 class="title">{{ __('Overdue Invoices') }} ({{ $metrics['overdueCount'] }})</h3>
                        <div class="icon">
                            <img src="{{ asset('assets/icons-admin/credit-card-times.svg') }}" alt="icon" loading="lazy" />
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="dashboard-box item-2">
                        <h2 class="count">₹{{ number_format($metrics['due7DaysAmount'], 2) }}</h2>
                        <h3 class="title">{{ __('Due Within 7 Days') }} ({{ $metrics['due7DaysCount'] }})</h3>
                        <div class="icon">
                            <img src="{{ asset('assets/icons-admin/clock.svg') }}" alt="icon" loading="lazy" />
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="dashboard-box item-4">
                        <h2 class="count">₹{{ number_format($metrics['due15DaysAmount'], 2) }}</h2>
                        <h3 class="title">{{ __('Due in 8-15+ Days') }} ({{ $metrics['due15DaysCount'] }})</h3>
                        <div class="icon">
                            <img src="{{ asset('assets/icons-admin/account-balance.svg') }}" alt="icon" loading="lazy" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="filter-card mb-3 p-3">
        <form method="GET" action="{{ route('shop.supplierDuePayment.index') }}" class="row g-2 align-items-end">
            <!-- Search Text -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">
                    {{ __('Search:') }}
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 6px 0 0 6px;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="{{ __('Voucher, Challan, Supplier...') }}" style="border-radius: 0 6px 6px 0; font-size: 0.85rem; height: 36px;">
                </div>
            </div>

            <!-- Supplier Filter -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">
                    {{ __('Supplier / Party:') }}
                </label>
                <select name="supplier_id" class="form-select" style="border-radius: 6px; font-size: 0.85rem; height: 36px;">
                    <option value="">{{ __('-- All Suppliers --') }}</option>
                    @foreach($suppliers as $supp)
                        <option value="{{ $supp->id }}" {{ request('supplier_id') == $supp->id ? 'selected' : '' }}>
                            {{ $supp->accountName }} ({{ $supp->accountshortcode }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Due Status Filter -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">
                    {{ __('Due Status:') }}
                </label>
                <select name="due_status" class="form-select" style="border-radius: 6px; font-size: 0.85rem; height: 36px;">
                    <option value="all" {{ request('due_status') == 'all' ? 'selected' : '' }}>{{ __('All Invoices') }}</option>
                    <option value="pending" {{ request('due_status') == 'pending' ? 'selected' : '' }}>{{ __('All Pending Dues') }}</option>
                    <option value="overdue" {{ request('due_status') == 'overdue' ? 'selected' : '' }}>🔴 {{ __('Overdue') }}</option>
                    <option value="due_7_days" {{ request('due_status') == 'due_7_days' ? 'selected' : '' }}>🟡 {{ __('Due in <= 7 Days') }}</option>
                    <option value="due_15_days" {{ request('due_status') == 'due_15_days' ? 'selected' : '' }}>🔵 {{ __('Due in <= 15 Days') }}</option>
                    <option value="settled" {{ request('due_status') == 'settled' ? 'selected' : '' }}>🟢 {{ __('Settled (Paid)') }}</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-secondary mb-1">
                    {{ __('Inward Dates:') }}
                </label>
                <div class="input-group">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control px-2" title="{{ __('Start Date') }}" style="border-radius: 6px 0 0 6px; font-size: 0.8rem; height: 36px;">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control px-2" title="{{ __('End Date') }}" style="border-radius: 0 6px 6px 0; font-size: 0.8rem; height: 36px;">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-theme-primary fw-semibold flex-fill d-inline-flex align-items-center justify-content-center gap-1" style="border-radius: 6px; font-size: 0.85rem; height: 36px;">
                    <i class="bi bi-filter"></i> {{ __('Filter') }}
                </button>
                <a href="{{ route('shop.supplierDuePayment.index') }}" class="btn btn-outline-secondary px-3 d-inline-flex align-items-center justify-content-center" style="border-radius: 6px; height: 36px;" title="{{ __('Reset Filter') }}">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Main Invoices Table Card -->
    <div class="card border-0 shadow-sm rounded-2 overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">{{ __('SL') }}</th>
                            <th>{{ __('Invoice & Challan') }}</th>
                            <th>{{ __('Inward Date') }}</th>
                            <th>{{ __('Supplier / Party') }}</th>
                            <th class="text-center">{{ __('Credit Terms') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th class="text-center">{{ __('Due Status') }}</th>
                            <th class="text-end">{{ __('Bill Amount') }}</th>
                            <th class="text-end">{{ __('Paid') }}</th>
                            <th class="text-end">{{ __('Net Due Balance') }}</th>
                            <th class="text-center" style="min-width: 140px; white-space: nowrap;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $key => $inv)
                        @php
                            $serial = $invoices->firstItem() + $key;
                            $initials = strtoupper(substr($inv->supplier_name, 0, 2));
                        @endphp
                        <tr>
                            <!-- SL -->
                            <td class="text-center fw-semibold text-secondary" style="font-size: 13px;">{{ $serial }}</td>

                            <!-- Voucher & Challan -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill badge-theme font-monospace fw-bold px-2.5 py-1" style="font-size: 12px;">
                                        {{ $inv->voucher_no }}
                                    </span>
                                </div>
                                @if($inv->challan_no)
                                    <div class="small text-muted font-monospace mt-1" style="font-size: 11px;">
                                        <i class="bi bi-receipt me-1"></i>Ch: {{ $inv->challan_no }}
                                    </div>
                                @endif
                            </td>

                            <!-- Inward Date -->
                            <td>
                                <div class="text-dark fw-medium" style="font-size: 13px;">
                                    {{ $inv->inward_date_formatted }}
                                </div>
                            </td>

                            <!-- Supplier Details -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="supplier-avatar">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size: 13.5px;">{{ $inv->supplier_name }}</div>
                                        <div class="text-muted small font-monospace d-flex align-items-center gap-1.5" style="font-size: 11px;">
                                            <span>#{{ $inv->supplier_code }}</span>
                                            @if($inv->supplier_phone)
                                                <span>•</span>
                                                <span><i class="bi bi-telephone text-secondary"></i> {{ $inv->supplier_phone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Credit Days -->
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill font-monospace fw-semibold" style="font-size: 11.5px;">
                                    <i class="bi bi-stopwatch me-1 text-secondary"></i>{{ $inv->credit_days }} {{ __('Days') }}
                                </span>
                            </td>

                            <!-- Due Date -->
                            <td>
                                <div class="fw-semibold {{ $inv->status === 'overdue' ? 'text-danger' : 'text-dark' }}" style="font-size: 13px;">
                                    {{ $inv->due_date_formatted }}
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="text-center">
                                @if($inv->status === 'overdue')
                                    <span class="status-pill status-pill-overdue">
                                        <span class="spinner-grow spinner-grow-sm text-danger" style="width: 7px; height: 7px;" role="status"></span>
                                        {{ $inv->status_text }}
                                    </span>
                                @elseif($inv->status === 'due_today')
                                    <span class="status-pill status-pill-today">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        {{ __('Due Today') }}
                                    </span>
                                @elseif($inv->status === 'due_7_days')
                                    <span class="status-pill status-pill-upcoming">
                                        <i class="bi bi-clock-history"></i>
                                        {{ $inv->status_text }}
                                    </span>
                                @elseif($inv->status === 'settled')
                                    <span class="status-pill status-pill-settled">
                                        <i class="bi bi-check-circle-fill"></i>
                                        {{ __('Settled') }}
                                    </span>
                                @else
                                    <span class="status-pill status-pill-later">
                                        <i class="bi bi-calendar"></i>
                                        {{ $inv->status_text }}
                                    </span>
                                @endif
                            </td>

                            <!-- Financial Columns -->
                            <td class="text-end font-monospace fw-semibold text-secondary">
                                ₹{{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td class="text-end font-monospace fw-semibold text-success">
                                ₹{{ number_format($inv->paid_amount, 2) }}
                            </td>
                            <td class="text-end font-monospace fw-bold {{ $inv->outstanding > 0 ? 'text-danger fs-6' : 'text-success' }}">
                                ₹{{ number_format($inv->outstanding, 2) }}
                            </td>

                            <!-- Actions -->
                            <td class="text-center" style="white-space: nowrap;">
                                <div class="d-inline-flex align-items-center justify-content-center gap-1.5">
                                    @if(!$inv->is_settled)
                                        <button type="button" 
                                                class="btn btn-sm btn-theme-primary px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 open-pay-modal"
                                                data-invoice-id="{{ $inv->id }}"
                                                data-voucher-no="{{ $inv->voucher_no }}"
                                                data-supplier-id="{{ $inv->supplier_id }}"
                                                data-supplier-name="{{ $inv->supplier_name }}"
                                                data-supplier-code="{{ $inv->supplier_code }}"
                                                data-due-date="{{ $inv->due_date_formatted }}"
                                                data-credit-days="{{ $inv->credit_days }}"
                                                data-total-amount="{{ $inv->total_amount }}"
                                                data-paid-amount="{{ $inv->paid_amount }}"
                                                data-outstanding="{{ $inv->outstanding }}"
                                                style="height: 32px; font-size: 12.5px; border-radius: 6px; white-space: nowrap;">
                                            <i class="bi bi-bank2" style="font-size: 13px;"></i>
                                            <span>{{ __('Pay by Bank') }}</span>
                                        </button>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 12px; height: 32px; white-space: nowrap;">
                                            <i class="bi bi-check2-all"></i>
                                            <span>{{ __('Settled') }}</span>
                                        </span>
                                    @endif

                                    @if($inv->paid_amount > 0)
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 view-history-btn d-inline-flex align-items-center justify-content-center" data-invoice-id="{{ $inv->id }}" title="{{ __('Disbursement History') }}" style="height: 32px; width: 32px; padding: 0;">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <div class="rounded-circle bg-light d-inline-flex p-3 text-secondary mb-3">
                                        <i class="bi bi-inbox fs-1"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">{{ __('No matching supplier credit invoices found') }}</h6>
                                    <p class="small text-muted mb-0">{{ __('Try adjusting your filters or date range above.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    {{ __('Showing') }} <strong class="text-dark">{{ $invoices->firstItem() ?? 0 }}</strong> {{ __('to') }} <strong class="text-dark">{{ $invoices->lastItem() ?? 0 }}</strong> {{ __('of') }} <strong class="text-dark">{{ $invoices->total() }}</strong> {{ __('inward credit records') }}
                </div>
                <div>
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal: Pay by Bank Master Modal (Dynamic Theme Color)                     -->
<!-- ========================================================================= -->
<div class="modal fade" id="payByBankModal" tabindex="-1" aria-labelledby="payByBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <!-- Modal Header with Dynamic Theme Color -->
            <div class="modal-header modal-theme-header p-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bank2 fs-5 text-white"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="payByBankModalLabel" style="font-size: 1.05rem;">
                            {{ __('Disburse Supplier Payment via Bank Master') }}
                        </h5>
                        <small class="text-white text-opacity-75" style="font-size: 0.76rem;">{{ __('Automated double-entry journal voucher posting') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="payByBankForm">
                @csrf
                <input type="hidden" name="inward_invoice_id" id="modal_inward_invoice_id">

                <div class="modal-body p-4">
                    <!-- Invoice Summary Card with Dynamic Theme Tint -->
                    <div class="card border-0 rounded-2 p-3 mb-3 modal-summary-box">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <span class="text-uppercase fw-bold text-muted small" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ __('Payee Vendor') }}</span>
                                <h5 class="fw-bold text-dark mb-1" id="modal_supplier_name">-</h5>
                                <div class="small text-muted font-monospace d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill bg-white text-theme border px-2 py-0.5" id="modal_supplier_code">-</span>
                                    <span>{{ __('Voucher:') }} <strong class="text-dark font-monospace" id="modal_voucher_no">-</strong></span>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <div class="small text-muted mb-1">
                                    {{ __('Due Date:') }} <strong class="text-danger" id="modal_due_date">-</strong> (<span id="modal_credit_days">0</span> {{ __('days credit') }})
                                </div>
                                <div class="small text-muted">
                                    {{ __('Total:') }} <span class="font-monospace" id="modal_total_amount">₹0.00</span> | {{ __('Paid:') }} <span class="font-monospace text-success" id="modal_paid_amount">₹0.00</span>
                                </div>
                                <div class="mt-1">
                                    <span class="text-muted small me-1">{{ __('Net Due Balance:') }}</span>
                                    <strong class="text-danger font-monospace fs-5" id="modal_outstanding_amount">₹0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Inputs -->
                    <div class="row g-3">
                        <!-- Select Bank -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1">
                                <i class="bi bi-building-check text-theme me-1"></i>{{ __('Disbursing Bank (Bank Master):') }} <span class="text-danger">*</span>
                            </label>
                            <select name="bank_master_id" id="modal_bank_master_id" class="form-select" style="border-radius: 6px; height: 38px; font-size: 0.85rem;" required>
                                <option value="">{{ __('-- Select Active Bank --') }}</option>
                                @foreach($bankMasters as $bm)
                                    <option value="{{ $bm->id }}">
                                        {{ $bm->bank_name }} • A/C: {{ $bm->bank_ac_no }} ({{ $bm->bank_branch ?? 'Branch' }})
                                    </option>
                                @endforeach
                            </select>
                            @if($bankMasters->isEmpty())
                                <small class="text-danger d-block mt-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ __('No bank accounts found in Bank Master. Please configure one.') }}
                                </small>
                            @endif
                        </div>

                        <!-- Payment Mode -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1">
                                <i class="bi bi-credit-card text-theme me-1"></i>{{ __('Payment Transfer Mode:') }} <span class="text-danger">*</span>
                            </label>
                            <select name="payment_mode" id="modal_payment_mode" class="form-select" style="border-radius: 6px; height: 38px; font-size: 0.85rem;" required>
                                <option value="neft_rtgs">{{ __('NEFT / RTGS / IMPS (Bank Transfer)') }}</option>
                                <option value="cheque">{{ __('Cheque') }}</option>
                                <option value="net_banking">{{ __('Net Banking') }}</option>
                                <option value="upi">{{ __('UPI / QR Scan') }}</option>
                                <option value="cash">{{ __('Cash') }}</option>
                            </select>
                        </div>

                        <!-- Payment Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1">
                                <i class="bi bi-calendar-check text-theme me-1"></i>{{ __('Payment Date:') }} <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="payment_date" id="modal_payment_date" class="form-control" value="{{ date('Y-m-d') }}" style="border-radius: 6px; height: 38px; font-size: 0.85rem;" required>
                        </div>

                        <!-- Paying Amount with Quick Chips -->
                        <div class="col-12 col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-dark mb-0">
                                    <i class="bi bi-currency-rupee text-theme"></i>{{ __('Paying Amount (₹):') }} <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-1">
                                    <span class="quick-preset-chip" id="chip_full_amount">{{ __('100% Full') }}</span>
                                    <span class="quick-preset-chip" id="chip_half_amount">{{ __('50% Half') }}</span>
                                </div>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text font-monospace bg-light" style="height: 38px;">₹</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="modal_amount" class="form-control font-monospace fw-bold fs-6" style="border-radius: 0 6px 6px 0; height: 38px;" required>
                            </div>
                        </div>

                        <!-- Reference / Cheque No -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1">
                                <i class="bi bi-hash text-theme me-1"></i>{{ __('UTR / Cheque / Ref Number:') }}
                            </label>
                            <input type="text" name="reference_no" id="modal_reference_no" class="form-control" placeholder="{{ __('e.g., UTR9823741 or CHQ-0012') }}" style="border-radius: 6px; height: 38px; font-size: 0.85rem;">
                        </div>

                        <!-- Narration / Notes -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1">
                                <i class="bi bi-chat-text text-theme me-1"></i>{{ __('Remarks / Narration:') }}
                            </label>
                            <input type="text" name="narration" id="modal_narration" class="form-control" placeholder="{{ __('Optional payment note...') }}" style="border-radius: 6px; height: 38px; font-size: 0.85rem;">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light p-3 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-2 fw-semibold" data-bs-dismiss="modal" style="height: 38px;">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-theme-primary px-4 py-2 rounded-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="submitPaymentBtn" style="height: 38px;">
                        <i class="bi bi-check-circle-fill"></i> <span>{{ __('Disburse Payment') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal: Payment History / Timeline Modal                                   -->
<!-- ========================================================================= -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header bg-dark text-white p-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history fs-5 text-warning"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="paymentHistoryModalLabel" style="font-size: 1.05rem;">
                            {{ __('Disbursement History') }} - <span id="history_invoice_no" class="font-monospace text-warning">-</span>
                        </h5>
                        <small class="text-muted" id="history_supplier_name">-</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table custom-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Voucher No') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Disbursed Via') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th>{{ __('Narration') }}</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr><td colspan="5" class="text-center py-4 text-muted">{{ __('Loading history...') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-2 px-3" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentOutstanding = 0;

    // Open Pay by Bank Modal
    $(document).on('click', '.open-pay-modal', function() {
        let btn = $(this);
        currentOutstanding = parseFloat(btn.data('outstanding')) || 0;

        $('#modal_inward_invoice_id').val(btn.data('invoice-id'));
        $('#modal_voucher_no').text(btn.data('voucher-no'));
        $('#modal_supplier_name').text(btn.data('supplier-name'));
        $('#modal_supplier_code').text('#' + btn.data('supplier-code'));
        $('#modal_due_date').text(btn.data('due-date'));
        $('#modal_credit_days').text(btn.data('credit-days'));
        $('#modal_total_amount').text('₹' + parseFloat(btn.data('total-amount')).toFixed(2));
        $('#modal_paid_amount').text('₹' + parseFloat(btn.data('paid-amount')).toFixed(2));
        $('#modal_outstanding_amount').text('₹' + currentOutstanding.toFixed(2));

        // Default paying amount = full pending balance
        $('#modal_amount').val(currentOutstanding.toFixed(2));
        $('#modal_amount').attr('max', currentOutstanding.toFixed(2));
        $('#modal_reference_no').val('');
        $('#modal_narration').val('');

        $('#payByBankModal').modal('show');
    });

    // Quick Amount Presets
    $('#chip_full_amount').on('click', function() {
        $('#modal_amount').val(currentOutstanding.toFixed(2));
    });

    $('#chip_half_amount').on('click', function() {
        let half = (currentOutstanding / 2).toFixed(2);
        $('#modal_amount').val(half);
    });

    // Handle Pay Form Submission
    $('#payByBankForm').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let submitBtn = $('#submitPaymentBtn');
        let originalBtnHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: "{{ route('shop.supplierDuePayment.pay') }}",
            type: "POST",
            data: form.serialize(),
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#payByBankModal').modal('hide');
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    toastr.error(response.message || 'Payment processing failed');
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                let message = 'An error occurred while processing payment.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    });

    // Handle View Payment History
    $(document).on('click', '.view-history-btn', function() {
        let invoiceId = $(this).data('invoice-id');
        let historyModal = $('#paymentHistoryModal');
        let tbody = $('#historyTableBody');

        tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Loading payment records...</td></tr>');
        historyModal.modal('show');

        $.ajax({
            url: `/shop/supplier-due-payments/${invoiceId}/history`,
            type: "GET",
            success: function(res) {
                if (res.status && res.history.length > 0) {
                    $('#history_invoice_no').text(res.invoice_no);
                    $('#history_supplier_name').text(res.supplier_name);
                    let html = '';
                    res.history.forEach(item => {
                        html += `
                            <tr>
                                <td><span class="badge rounded-pill badge-theme font-monospace fw-bold">${item.voucher_no}</span></td>
                                <td class="text-dark fw-medium">${item.date}</td>
                                <td><span class="badge bg-light text-dark border px-2 py-1">${item.bank_or_mode}</span></td>
                                <td class="text-end font-monospace fw-bold text-success">₹${item.amount.toFixed(2)}</td>
                                <td class="small text-muted">${item.narration || '-'}</td>
                            </tr>
                        `;
                    });
                    tbody.html(html);
                } else {
                    tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted">No past disbursements recorded for this invoice.</td></tr>');
                }
            },
            error: function() {
                tbody.html('<tr><td colspan="5" class="text-center text-danger py-4">Failed to load payment history.</td></tr>');
            }
        });
    });
});
</script>
@endpush
