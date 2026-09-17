@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh; background: #f8fafc;">
    
    <!-- Header panel -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3" style="border-bottom: 1px solid #cbd5e1;">
        <div>
            <h4 class="fw-bold m-0 text-dark">
                <i class="fa-solid fa-clock-history me-2 text-primary"></i>{{ __('Sales Invoice History Registry') }}
            </h4>
            <small class="text-muted" style="font-size: 12px; color: #64748b;">{{ __('Enterprise ledger audit dashboard & reprints') }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('shop.pos.index') }}" class="btn btn-outline-secondary btn-sm fw-bold px-3 text-dark" style="border-radius: 8px; border-color: #cbd5e1; background: #ffffff;">
                <i class="fa-solid fa-cash-register me-1"></i>{{ __('Back to POS Billing') }}
            </a>
            <button class="btn btn-success btn-sm fw-bold px-3" onclick="exportHistoryCSV()" style="border-radius: 8px; background: #10b981; border-color: #10b981;">
                <i class="fa-solid fa-file-excel me-1"></i>{{ __('Export CSV') }}
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="d-flex mb-4 registry-tab-container">
        <button id="btn-tab-sales" type="button" class="registry-tab-btn active-sales" onclick="switchActiveTab('sales')">
            <i class="fa-solid fa-file-invoice tab-icon"></i>
            <span>Sales Invoices</span>
        </button>
        <button id="btn-tab-returns" type="button" class="registry-tab-btn ms-1" onclick="switchActiveTab('returns')">
            <i class="fa-solid fa-rotate-left tab-icon"></i>
            <span>Product Returns Registry</span>
        </button>
    </div>

    <!-- 1. Stats Dashboard Summary Cards panel (Sales) -->
    <div id="stats-sales-panel" class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">Today's Sales</small>
                <h5 class="fw-bold m-0 text-success" id="stat-sales-today">₹0.00</h5>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">Today's Bills</small>
                <h5 class="fw-bold m-0 text-dark" id="stat-bills-today">0</h5>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">Hold Bills</small>
                <h5 class="fw-bold m-0 text-warning" id="stat-holds-active">0</h5>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">Cancelled Bills</small>
                <h5 class="fw-bold m-0 text-danger" id="stat-cancelled-bills">0</h5>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">Average Ticket</small>
                <h5 class="fw-bold m-0 text-info" id="stat-average-bill">₹0.00</h5>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">Returns Count</small>
                <h5 class="fw-bold m-0 text-danger" id="stat-returns-today">0</h5>
            </div>
        </div>
    </div>

    <!-- 1b. Stats Dashboard Summary Cards panel (Returns) -->
    <div id="stats-returns-panel" class="row g-3 mb-4 d-none">
        <div class="col-md-3 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">Today's Returns</small>
                <h5 class="fw-bold m-0 text-danger" id="stat-ret-today">₹0.00</h5>
                <small class="text-muted" style="font-size:10px;" id="stat-ret-today-count">0 CNs</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">This Week's Returns</small>
                <h5 class="fw-bold m-0 text-warning" id="stat-ret-week">₹0.00</h5>
                <small class="text-muted" style="font-size:10px;" id="stat-ret-week-count">0 CNs</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">This Month's Returns</small>
                <h5 class="fw-bold m-0 text-info" id="stat-ret-month">₹0.00</h5>
                <small class="text-muted" style="font-size:10px;" id="stat-ret-month-count">0 CNs</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">This Financial Year</small>
                <h5 class="fw-bold m-0 text-success" id="stat-ret-year">₹0.00</h5>
                <small class="text-muted" style="font-size:10px;" id="stat-ret-year-count">0 CNs</small>
            </div>
        </div>
    </div>

    <!-- 2. Search & Filters Panel -->
    <div class="card p-3 border-0 mb-3" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="d-flex flex-wrap align-items-end gap-2">
            <div style="flex: 2 1 240px;">
                <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Search Text</label>
                <input type="text" class="form-control form-control-sm bg-white text-dark" id="filter-search" placeholder="Invoice No, Customer, Mobile, Barcode..." style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
            </div>
            <div style="flex: 1 1 130px;">
                <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Date From</label>
                <input type="date" class="form-control form-control-sm bg-white text-dark" id="filter-date-from" style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
            </div>
            <div style="flex: 1 1 130px;">
                <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Date To</label>
                <input type="date" class="form-control form-control-sm bg-white text-dark" id="filter-date-to" style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
            </div>
            <div style="flex: 1 1 130px;" id="filter-payment-mode-box">
                <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Payment Mode</label>
                <select class="form-select form-select-sm bg-white text-dark" id="filter-payment-mode" style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
                    <option value="">All Modes</option>
                    <option value="Cash">Cash</option>
                    <option value="Online">Online</option>
                    <option value="Split Payment">Split Payment</option>
                </select>
            </div>
            <div style="flex: 1 1 130px;" id="filter-status-box">
                <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Status</label>
                <select class="form-select form-select-sm bg-white text-dark" id="filter-status" style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
                    <option value="">All Statuses</option>
                    <option value="delivered">Completed</option>
                    <option value="canceled">Cancelled</option>
                </select>
            </div>
            <div class="d-flex gap-2" style="flex: 0 0 180px;">
                <button class="btn btn-primary btn-sm fw-bold w-100" onclick="applyHistoryFilters()" style="border-radius: 6px; background: #2563eb; border-color: #2563eb; height: 32px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                    <i class="fa-solid fa-magnifying-glass"></i>Filter
                </button>
                <button class="btn btn-outline-secondary btn-sm fw-bold w-100 text-dark" onclick="resetHistoryFilters()" style="border-radius: 6px; border-color: #cbd5e1; background: #ffffff; height: 32px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Invoices Table Card -->
    <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); color: #1e293b;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0" style="font-size: 13px; color: #1e293b;">
                    <thead class="table-light text-dark" id="history-table-header" style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="color: #475569; font-weight: 600;">Invoice No</th>
                            <th style="color: #475569; font-weight: 600;">Customer</th>
                            <th style="color: #475569; font-weight: 600;">Date</th>
                            <th class="text-center" style="color: #475569; font-weight: 600;">Total Items</th>
                            <th class="text-right" style="color: #475569; font-weight: 600;">Gross Amt</th>
                            <th class="text-right" style="color: #475569; font-weight: 600;">Disc.</th>
                            <th class="text-right" style="color: #475569; font-weight: 600;">GST</th>
                            <th class="text-right" style="color: #475569; font-weight: 600;">Net Amount</th>
                            <th class="text-right" style="color: #475569; font-weight: 600;">Paid Amount</th>
                            <th style="color: #475569; font-weight: 600;">Payment</th>
                            <th style="color: #475569; font-weight: 600;">Status</th>
                            <th class="text-end" style="width: 250px; color: #475569; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body" style="background: #ffffff; color: #1e293b;">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Server-side Pagination controls -->
            <div class="d-flex justify-content-between align-items-center p-3" style="border-top:1px solid #e2e8f0; background: #f8fafc;">
                <div class="text-muted small" style="color: #64748b !important;">
                    Showing <span id="pagination-info-start" class="fw-bold">0</span> to <span id="pagination-info-end" class="fw-bold">0</span> of <span id="pagination-info-total" class="fw-bold">0</span> entries
                </div>
                <nav>
                    <ul class="pagination pagination-sm m-0" id="pagination-controls-list">
                        <!-- Controls generated dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     POS INVOICE PRINT OVERLAY MODAL
===================================================================== -->
<div id="pos-invoice-print-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 1000000;
    background: rgba(5, 8, 18, 0.88);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
" onclick="if(event.target===this) closePOSPrintPreview()">

    <div id="pos-invoice-modal-card" style="
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 24px 64px rgba(0,0,0,0.55);
        width: 100%;
        max-width: 400px;
        max-height: 85%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        color: #000;
        transition: max-width 0.3s ease, height 0.3s ease;
    ">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom" style="background:#f8f9fa;">
            <h6 class="fw-bold m-0" style="color:#1a2035; font-family:sans-serif;">
                <i class="fa-solid fa-print me-2 text-primary"></i><span id="pos-invoice-modal-title">Thermal Invoice Preview</span>
            </h6>
            <button class="btn-close" onclick="closePOSPrintPreview()" style="font-size:18px; outline:none; border:none; background:none; cursor:pointer;">&times;</button>
        </div>

        <div style="overflow-y: auto; overflow-x: auto; flex-grow: 1; padding: 20px; background:#f1f5f9; display: flex; justify-content: center;">
            <div id="pos-invoice-print-body" style="background:#fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 10px; width: 80mm; box-sizing: border-box; text-align: left;">
                <!-- Rendered dynamically -->
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 px-4 py-3 border-top" style="background:#f8f9fa; font-family:sans-serif;">
            <button class="btn btn-outline-secondary btn-sm px-4 fw-bold" onclick="closePOSPrintPreview()">
                Close (Esc)
            </button>
            <button class="btn btn-primary btn-sm px-4 fw-bold" onclick="printPOSInvoice()">
                Print (Enter / Any Key)
            </button>
        </div>
    </div>
</div>

<iframe id="pos-print-iframe" style="display:none; position:absolute; left:-9999px;"></iframe>

<style>
.table-hover tbody tr:hover {
    background-color: #f1f5f9 !important;
}
.text-right {
    text-align: right;
}

.registry-tab-container {
    background: #e2e8f0;
    padding: 4px;
    border-radius: 12px;
    width: fit-content;
    border: 1px solid #cbd5e1;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
}

.registry-tab-btn {
    padding: 8px 20px;
    font-weight: 600;
    font-size: 13px;
    border-radius: 9px;
    border: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    background: transparent;
    color: #475569 !important;
    opacity: 1 !important;
}

.registry-tab-btn:hover:not(.active-sales):not(.active-returns) {
    background: rgba(255, 255, 255, 0.7);
    color: #0f172a !important;
}

.registry-tab-btn.active-sales {
    background: #2563eb !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
}

.registry-tab-btn.active-returns {
    background: #dc2626 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
}

.registry-tab-btn .tab-icon {
    font-size: 14px;
}
</style>

<script>
    const currencySymbol = "{{ generaleSetting('defaultCurrency')?->symbol ?? (generaleSetting('setting')?->currency ?? '₹') }}";
    let currentPage = 1;
    let limitPerPage = 10;
    const urlParams = new URLSearchParams(window.location.search);
    let activeTab = urlParams.get('tab') || 'sales';

    let historyPageInitialized = false;
    function initHistoryPage() {
        if (historyPageInitialized) return;
        if (typeof $ === 'undefined') return;
        historyPageInitialized = true;
        
        switchActiveTab(activeTab);
    }

    if (typeof $ !== 'undefined') {
        $(document).ready(initHistoryPage);
    }
    document.addEventListener('DOMContentLoaded', initHistoryPage);
    window.addEventListener('load', initHistoryPage);
    document.addEventListener('turbolinks:load', initHistoryPage);
    document.addEventListener('pjax:end', initHistoryPage);

    function switchActiveTab(tab) {
        activeTab = tab;
        currentPage = 1;
        
        const $btnSales = $('#btn-tab-sales');
        const $btnReturns = $('#btn-tab-returns');
        
        if (activeTab === 'sales') {
            $btnSales.removeClass('active-returns').addClass('active-sales');
            $btnReturns.removeClass('active-sales active-returns');
            
            $('#stats-sales-panel').removeClass('d-none');
            $('#stats-returns-panel').addClass('d-none');
            
            $('#history-table-header').html(`
                <tr>
                    <th style="color: #475569; font-weight: 600;">Invoice No</th>
                    <th style="color: #475569; font-weight: 600;">Customer</th>
                    <th style="color: #475569; font-weight: 600;">Date</th>
                    <th class="text-center" style="color: #475569; font-weight: 600;">Total Items</th>
                    <th class="text-right" style="color: #475569; font-weight: 600;">Gross Amt</th>
                    <th class="text-right" style="color: #475569; font-weight: 600;">Disc.</th>
                    <th class="text-right" style="color: #475569; font-weight: 600;">GST</th>
                    <th class="text-right" style="color: #475569; font-weight: 600;">Net Amount</th>
                    <th class="text-right" style="color: #475569; font-weight: 600;">Paid Amount</th>
                    <th style="color: #475569; font-weight: 600;">Payment</th>
                    <th style="color: #475569; font-weight: 600;">Status</th>
                    <th class="text-end" style="width: 250px; color: #475569; font-weight: 600;">Actions</th>
                </tr>
            `);
            
            $('#filter-payment-mode-box').removeClass('d-none');
            $('#filter-status-box').removeClass('d-none');
            loadHistoryStats();
        } else {
            $btnReturns.removeClass('active-sales').addClass('active-returns');
            $btnSales.removeClass('active-sales active-returns');
            
            $('#stats-sales-panel').addClass('d-none');
            $('#stats-returns-panel').removeClass('d-none');
            
            $('#history-table-header').html(`
                <tr>
                    <th style="color: #475569; font-weight: 600;">Credit Note No</th>
                    <th style="color: #475569; font-weight: 600;">Orig Invoice</th>
                    <th style="color: #475569; font-weight: 600;">Date & Time</th>
                    <th style="color: #475569; font-weight: 600;">Customer</th>
                    <th style="color: #475569; font-weight: 600;">Product Name</th>
                    <th style="color: #475569; font-weight: 600;">Barcode</th>
                    <th class="text-center" style="color: #475569; font-weight: 600;">Qty</th>
                    <th class="text-right" style="color: #475569; font-weight: 600;">Rate</th>
                    <th class="text-right" style="color: #475569; font-weight: 600;">Refund Total</th>
                    <th style="color: #475569; font-weight: 600;">Reason</th>
                    <th style="color: #475569; font-weight: 600;">Cashier</th>
                    <th class="text-end" style="width: 120px; color: #475569; font-weight: 600;">Actions</th>
                </tr>
            `);
            
            $('#filter-payment-mode-box').addClass('d-none');
            $('#filter-status-box').addClass('d-none');
        }
        
        loadHistoryRegister();
    }

    function loadHistoryStats() {
        $.ajax({
            url: "{{ route('shop.pos.history.stats') }}",
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#stat-sales-today').text(currencySymbol + (parseFloat(res.stats.sales_today) || 0).toFixed(2));
                    $('#stat-bills-today').text(res.stats.bills_today || 0);
                    $('#stat-holds-active').text(res.stats.holds_active || 0);
                    $('#stat-cancelled-bills').text(res.stats.cancelled_bills || 0);
                    $('#stat-average-bill').text(currencySymbol + (parseFloat(res.stats.average_bill) || 0).toFixed(2));
                    $('#stat-returns-today').text(res.stats.returns_today || 0);
                }
            },
            error: function(err) {
                console.error("Failed to load history stats:", err);
            }
        });
    }

    function loadHistoryRegister() {
        const searchVal = $('#filter-search').val().trim();
        const payload = {
            page: currentPage,
            limit: limitPerPage,
            tab: activeTab,
            search: searchVal,
            invoice_no: searchVal,
            customer: searchVal,
            payment_mode: $('#filter-payment-mode').val(),
            status: $('#filter-status').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val()
        };

        $.ajax({
            url: "{{ route('shop.pos.history.index') }}",
            type: 'GET',
            data: payload,
            dataType: 'json',
            success: function(res) {
                console.log("AJAX Response received:", res);
                window.lastHistoryResponse = res;
                if (res.success) {
                    if (activeTab === 'sales') {
                        renderHistoryTable(res.data);
                    } else {
                        renderReturnsTable(res.data);
                        if (res.stats) {
                            $('#stat-ret-today').text(currencySymbol + (parseFloat(res.stats.today_amt) || 0).toFixed(2));
                            $('#stat-ret-today-count').text((res.stats.today_count || 0) + ' CNs');
                            
                            $('#stat-ret-week').text(currencySymbol + (parseFloat(res.stats.week_amt) || 0).toFixed(2));
                            $('#stat-ret-week-count').text((res.stats.week_count || 0) + ' CNs');
                            
                            $('#stat-ret-month').text(currencySymbol + (parseFloat(res.stats.month_amt) || 0).toFixed(2));
                            $('#stat-ret-month-count').text((res.stats.month_count || 0) + ' CNs');
                            
                            $('#stat-ret-year').text(currencySymbol + (parseFloat(res.stats.year_amt) || 0).toFixed(2));
                            $('#stat-ret-year-count').text((res.stats.year_count || 0) + ' CNs');
                        }
                    }
                    renderPaginationControls(res.pagination);
                }
            },
            error: function(err) {
                console.error("Failed to load history register:", err);
            }
        });
    }

    function renderHistoryTable(data) {
        var $tbody = $('#history-table-body');
        $tbody.empty();

        if (!data || data.length === 0) {
            $tbody.append('<tr><td colspan="12" class="text-center text-muted py-4">No invoice records matching filters.</td></tr>');
            return;
        }

        $.each(data, function(index, o) {
            try {
                var checkStatus = (o.status || '').toLowerCase();
                var statusColor = 'bg-success text-white';
                var statusText = 'Completed';

                if (checkStatus === 'returned') {
                    statusColor = 'bg-danger text-white';
                    statusText = 'Returned';
                } else if (checkStatus === 'partial returned') {
                    statusColor = 'bg-warning text-dark';
                    statusText = 'Partial Returned';
                } else if (checkStatus === 'cancelled' || checkStatus === 'canceled') {
                    statusColor = 'bg-secondary text-white';
                    statusText = 'Cancelled';
                }

                var gross = (parseFloat(o.gross_amount) || 0).toFixed(2);
                var disc = (parseFloat(o.discount) || 0).toFixed(2);
                var tax = (parseFloat(o.tax_amount) || 0).toFixed(2);
                var net = (parseFloat(o.net_amount) || 0).toFixed(2);
                var paid = (parseFloat(o.paid_amount) || 0).toFixed(2);
                var custName = o.customer_name || 'Walk-in Customer';
                var custPhone = o.customer_phone || '-';
                var invNo = o.invoice_no || '';
                var itemsCount = o.items_count || 0;
                var payMethod = (o.payment_method || 'Cash').replace(' Payment', '');
                var invDate = o.date || '';

                var $tr = $('<tr>');
                
                $tr.append($('<td>').addClass('fw-bold text-primary').text(invNo));
                $tr.append($('<td>').html('<div>' + custName + '</div><small class="text-muted">' + custPhone + '</small>'));
                $tr.append($('<td>').text(invDate));
                $tr.append($('<td>').addClass('text-center').text(itemsCount));
                $tr.append($('<td>').addClass('text-right').text(currencySymbol + gross));
                $tr.append($('<td>').addClass('text-right text-warning').text(currencySymbol + disc));
                $tr.append($('<td>').addClass('text-right text-danger').text(currencySymbol + tax));
                $tr.append($('<td>').addClass('text-right fw-bold text-success').text(currencySymbol + net));

                // Paid Amount with loss bracket:
                var diff = parseFloat(paid) - parseFloat(net);
                var paidHtml = currencySymbol + paid;
                if (diff < -0.005) {
                    paidHtml += ' <span class="text-danger" style="font-size: 11px; font-weight: normal;">(' + diff.toFixed(2) + ')</span>';
                }
                $tr.append($('<td>').addClass('text-right fw-bold text-primary').html(paidHtml));

                $tr.append($('<td>').text(payMethod));
                $tr.append($('<td>').html('<span class="badge ' + statusColor + '">' + statusText + '</span>'));

                var $actionsTd = $('<td>').addClass('text-end');

                var $viewBtn = $('<button>')
                    .addClass('btn btn-xs py-0.5 px-2 fw-bold text-white me-1')
                    .css({ 'background-color': '#1e293b', 'border': '1px solid #334155', 'font-size': '11px' })
                    .text('View')
                    .attr('onclick', 'viewThermalInvoice(' + o.id + ')');
                $actionsTd.append($viewBtn);

                var $dupBtn = $('<button>')
                    .addClass('btn btn-primary btn-xs py-0.5 px-2 fw-bold me-1')
                    .css('font-size', '11px')
                    .text('Duplicate')
                    .attr('onclick', 'duplicateInvoiceBack(' + o.id + ')');
                $actionsTd.append($dupBtn);

                if (checkStatus === 'delivered' || checkStatus === 'partial returned') {
                    var $retBtn = $('<button>')
                        .addClass('btn btn-warning btn-xs py-0.5 px-2 fw-bold text-dark me-1')
                        .css('font-size', '11px')
                        .text('Return')
                        .attr('onclick', 'triggerReturnWizard("' + invNo + '")');
                    $actionsTd.append($retBtn);
                }

                if (checkStatus === 'delivered') {
                    var $voidBtn = $('<button>')
                        .addClass('btn btn-danger btn-xs py-0.5 px-2 fw-bold')
                        .css('font-size', '11px')
                        .text('Void')
                        .attr('onclick', 'voidInvoice(' + o.id + ')');
                    $actionsTd.append($voidBtn);
                }

                $tr.append($actionsTd);
                $tbody.append($tr);

            } catch (err) {
                console.error("Error rendering row " + index + ":", err);
            }
        });

        // Ensure all dynamically loaded rows are displayed, bypassing CSS animation-delay defaults
        $tbody.find('tr').each(function(index) {
            $(this).css({
                "display": "table-row",
                "animation-delay": (index * 0.1) + "s"
            });
        });
    }

    function renderReturnsTable(data) {
        var $tbody = $('#history-table-body');
        $tbody.empty();

        if (!data || data.length === 0) {
            $tbody.append('<tr><td colspan="12" class="text-center text-muted py-4">No product return records matching filters.</td></tr>');
            return;
        }

        $.each(data, function(index, item) {
            try {
                var retNo = item.return_no || '';
                var origInv = item.original_invoice || '';
                var itemDate = item.date || '';
                var cust = item.customer || 'Walk-in Customer';
                var prodName = item.product_name || 'N/A';
                var barcode = item.barcode || '';
                var qty = item.qty || 0;
                var rate = (parseFloat(item.rate) || 0).toFixed(2);
                var total = (parseFloat(item.total) || 0).toFixed(2);
                var reason = item.reason || '-';
                var cashier = item.cashier || '-';

                var $tr = $('<tr>');

                $tr.append($('<td>').addClass('fw-bold text-danger').text(retNo));
                $tr.append($('<td>').addClass('fw-bold text-primary').text(origInv));
                $tr.append($('<td>').text(itemDate));
                $tr.append($('<td>').text(cust));
                $tr.append($('<td>').addClass('fw-bold').text(prodName));
                $tr.append($('<td>').html('<small class="text-muted font-monospace">' + barcode + '</small>'));
                $tr.append($('<td>').addClass('text-center fw-bold text-warning').text(qty));
                $tr.append($('<td>').addClass('text-right').text(currencySymbol + rate));
                $tr.append($('<td>').addClass('text-right fw-bold text-danger').text(currencySymbol + total));
                $tr.append($('<td>').html('<small class="text-muted">' + reason + '</small>'));
                $tr.append($('<td>').text(cashier));

                var $actionsTd = $('<td>').addClass('text-end');
                var $printBtn = $('<button>')
                    .addClass('btn btn-info btn-xs py-0.5 px-2 fw-bold text-dark')
                    .css('font-size', '11px')
                    .text('Print CN')
                    .attr('onclick', 'printCreditNote("' + retNo + '")');
                $actionsTd.append($printBtn);

                $tr.append($actionsTd);
                $tbody.append($tr);

            } catch (err) {
                console.error("Error rendering returns row " + index + ":", err);
            }
        });

        // Ensure all dynamically loaded rows are displayed, bypassing CSS animation-delay defaults
        $tbody.find('tr').each(function(index) {
            $(this).css({
                "display": "table-row",
                "animation-delay": (index * 0.1) + "s"
            });
        });
    }

    function renderPaginationControls(p) {
        $('#pagination-info-total').text(p.total);
        const start = p.total === 0 ? 0 : (p.current_page - 1) * p.per_page + 1;
        const end = Math.min(p.current_page * p.per_page, p.total);
        $('#pagination-info-start').text(start);
        $('#pagination-info-end').text(end);

        let html = '';
        const prevDisabled = p.current_page === 1 ? 'disabled' : '';
        html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" onclick="changeHistoryPage(${p.current_page - 1}); return false;">Prev</a></li>`;

        for (let i = 1; i <= p.last_page; i++) {
            const active = p.current_page === i ? 'active' : '';
            html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="changeHistoryPage(${i}); return false;">${i}</a></li>`;
        }

        const nextDisabled = p.current_page === p.last_page ? 'disabled' : '';
        html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" onclick="changeHistoryPage(${p.current_page + 1}); return false;">Next</a></li>`;

        $('#pagination-controls-list').html(html);
    }

    function changeHistoryPage(page) {
        currentPage = page;
        loadHistoryRegister();
    }

    function applyHistoryFilters() {
        currentPage = 1;
        loadHistoryRegister();
    }

    function resetHistoryFilters() {
        $('#filter-search').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        $('#filter-payment-mode').val('');
        $('#filter-status').val('');
        currentPage = 1;
        loadHistoryRegister();
    }

    function openPOSPrintPreview(orderId, autoPrint = false) {
        $.ajax({
            url: `/shop/pos/${orderId}/thermal-preview`,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $('#pos-invoice-print-body').html(html);

                const isA4 = html.includes('invoice-page') || html.includes('TAX INVOICE');
                const modalCard = document.getElementById('pos-invoice-modal-card');
                const modalTitle = document.getElementById('pos-invoice-modal-title');
                const printBody = document.getElementById('pos-invoice-print-body');

                if (isA4) {
                    if (modalCard) {
                        modalCard.style.maxWidth = '800px';
                        modalCard.style.height = '85vh';
                        modalCard.style.maxHeight = '85vh';
                    }
                    if (modalTitle) {
                        modalTitle.textContent = 'A4 Invoice Preview';
                    }
                    if (printBody) {
                        printBody.style.width = '100%';
                        printBody.style.maxWidth = '100%';
                        printBody.style.padding = '0';
                    }
                } else {
                    if (modalCard) {
                        modalCard.style.maxWidth = '400px';
                        modalCard.style.height = 'auto';
                        modalCard.style.maxHeight = '85vh';
                    }
                    if (modalTitle) {
                        modalTitle.textContent = 'Thermal Invoice Preview';
                    }
                    if (printBody) {
                        printBody.style.width = '80mm';
                        printBody.style.maxWidth = '100%';
                        printBody.style.padding = '10px';
                    }
                }

                const overlay = document.getElementById('pos-invoice-print-overlay');
                if (overlay) {
                    if (overlay.parentNode !== document.body) {
                        document.body.appendChild(overlay);
                    }
                    overlay.style.display = 'flex';
                }
                if (autoPrint) {
                    setTimeout(printPOSInvoice, 300);
                }
            },
            error: function() {
                alert("Failed to load invoice layout for preview.");
            }
        });
    }

    function closePOSPrintPreview() {
        const overlay = document.getElementById('pos-invoice-print-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
        $('#pos-invoice-print-body').empty();
    }

    function printPOSInvoice() {
        const printBody = document.getElementById('pos-invoice-print-body');
        if (!printBody) return;
        
        const content = printBody.innerHTML;
        const iframe = document.getElementById('pos-print-iframe');
        if (!iframe) return;

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Invoice</title>
                <style>
                    @media print {
                        @page { margin: 0; }
                        body { margin: 0; padding: 0; }
                    }
                </style>
            </head>
            <body>
                ${content}
            </body>
            </html>
        `);
        doc.close();

        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 250);
    }

    function viewThermalInvoice(orderId) {
        openPOSPrintPreview(orderId, false);
    }

    function closeInvoiceViewModal() {
        closePOSPrintPreview();
    }

    function printThermalInvoiceContent() {
        printPOSInvoice();
    }

    $(document).on('keydown', function(e) {
        const printOverlay = document.getElementById('pos-invoice-print-overlay');
        if (printOverlay && printOverlay.style.display === 'flex') {
            if (e.key === 'Escape') {
                closePOSPrintPreview();
            } else if (e.key === 'Enter') {
                printPOSInvoice();
            }
        }
    });

    function duplicateInvoiceBack(orderId) {
        if (!confirm('Are you sure you want to load this bill details back into active POS? This will overwrite the current cart.')) return;
        $.ajax({
            url: `/shop/pos/history/${orderId}/duplicate`,
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" },
            dataType: 'json',
            success: function(res) {
                const order = res.order;
                localStorage.setItem('pos_duplicate_order', JSON.stringify(order));
                window.location.href = "{{ route('shop.pos.index') }}";
            }
        });
    }

    function voidInvoice(orderId) {
        if (!confirm('CAUTION: Are you sure you want to void this invoice? This will cancel the order, revert stock quantities, mark barcodes unsold, and post journal reverse entries in double-entry accounts.')) return;
        $.ajax({
            url: `/shop/pos/history/${orderId}/void`,
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" },
            dataType: 'json',
            success: function(res) {
                alert(res.message);
                loadHistoryStats();
                loadHistoryRegister();
            },
            error: function(err) {
                alert(err.responseJSON?.error || 'Void failed.');
            }
        });
    }

    function triggerReturnWizard(invoiceNo) {
        localStorage.setItem('pos_trigger_return_invoice', invoiceNo);
        window.location.href = "{{ route('shop.pos.index') }}";
    }

    function printCreditNote(invoiceNo) {
        const printUrl = "{{ route('shop.pos.return.print', 'PLACEHOLDER') }}".replace('PLACEHOLDER', encodeURIComponent(invoiceNo));
        window.open(printUrl, '_blank');
    }

    function exportHistoryCSV() {
        const query = $.param({
            invoice_no: $('#filter-search').val().trim(),
            customer: $('#filter-search').val().trim(),
            payment_mode: $('#filter-payment-mode').val(),
            status: $('#filter-status').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val()
        });
        window.location.href = "{{ route('shop.pos.history.export') }}?" + query;
    }
</script>
@endsection
