@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh; background: #f8fafc;">
    
    <!-- Header panel -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3" style="border-bottom: 1px solid #cbd5e1;">
        <div>
            <h4 class="fw-bold m-0 text-dark">
                <i class="fa-solid fa-user-check me-2 text-primary"></i>{{ __('Cashier Performance & Billing Report') }}
            </h4>
            <small class="text-muted" style="font-size: 12px; color: #64748b;">
                {{ __('Auditing cashier billing efficiency, total sales volume, and transaction timelines') }}
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('shop.pos.index') }}" class="btn btn-outline-secondary btn-sm fw-bold px-3 text-dark" style="border-radius: 8px; border-color: #cbd5e1; background: #ffffff;">
                <i class="fa-solid fa-cash-register me-1"></i>{{ __('Back to POS Billing') }}
            </a>
            <a href="{{ route('shop.cashier-report.export', request()->query()) }}" class="btn btn-success btn-sm fw-bold px-3" style="border-radius: 8px; background: #10b981; border-color: #10b981;">
                <i class="fa-solid fa-file-excel me-1"></i>{{ __('Export CSV') }}
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 mb-4 p-3" style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <form method="GET" action="{{ route('shop.cashier-report.index') }}" class="row g-3 align-items-end">
            <!-- Period Filter -->
            <div class="col-md-3 col-6">
                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 12px;">{{ __('Date Period') }}</label>
                <select name="period" id="filter-period" class="form-select form-select-sm" style="border-radius: 6px;" onchange="toggleCustomDates(this.value)">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>{{ __('Today / Daily') }}</option>
                    <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>{{ __('This Week') }}</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>{{ __('Custom Range') }}</option>
                </select>
            </div>

            <!-- Custom Date From -->
            <div class="col-md-2 col-6 custom-date-box {{ $period === 'custom' ? '' : 'd-none' }}">
                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 12px;">{{ __('Date From') }}</label>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm" style="border-radius: 6px;">
            </div>

            <!-- Custom Date To -->
            <div class="col-md-2 col-6 custom-date-box {{ $period === 'custom' ? '' : 'd-none' }}">
                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 12px;">{{ __('Date To') }}</label>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm" style="border-radius: 6px;">
            </div>

            <!-- Cashier Filter -->
            <div class="col-md-3 col-6">
                <label class="form-label fw-bold text-secondary mb-1" style="font-size: 12px;">{{ __('Cashier Filter') }}</label>
                <select name="cashier_id" class="form-select form-select-sm" style="border-radius: 6px;">
                    <option value="all" {{ $cashierFilter === 'all' ? 'selected' : '' }}>{{ __('All Cashiers') }}</option>
                    <option value="unassigned" {{ $cashierFilter === 'unassigned' ? 'selected' : '' }}>{{ __('Unassigned Cashier') }}</option>
                    @foreach($cashiersList as $c)
                        <option value="{{ $c->id }}" {{ (string)$cashierFilter === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Buttons -->
            <div class="col-md-2 col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm fw-bold w-100" style="border-radius: 6px;">
                    <i class="fa-solid fa-filter me-1"></i>{{ __('Filter') }}
                </button>
                <a href="{{ route('shop.cashier-report.index') }}" class="btn btn-outline-secondary btn-sm w-50" style="border-radius: 6px;">
                    {{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">{{ __('Total Bills Created') }}</small>
                <h4 class="fw-bold m-0 text-dark">{{ number_format($totalBillsCount) }}</h4>
                <small class="text-muted" style="font-size: 11px;">{{ __('Period:') }} {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">{{ __('Total Sales Amount') }}</small>
                <h4 class="fw-bold m-0 text-primary">{{ $currency }}{{ number_format($totalSalesAmt, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">
                    {{ __('Cash:') }} {{ $currency }}{{ number_format($totalCashSales, 2) }} | {{ __('Card:') }} {{ $currency }}{{ number_format($totalCardSales, 2) }}
                </small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">{{ __('Returned / Cancelled') }}</small>
                <h4 class="fw-bold m-0 text-danger">{{ $currency }}{{ number_format($totalCancelledAmt, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">{{ __('Reversals & voided invoices') }}</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card p-3 border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size:10px;">{{ __('Net Collected Amount') }}</small>
                <h4 class="fw-bold m-0 text-success">{{ $currency }}{{ number_format($totalNetCollected, 2) }}</h4>
                <small class="text-muted" style="font-size: 11px;">{{ __('Actual net billing revenue') }}</small>
            </div>
        </div>
    </div>

    <!-- Cashier Performance Summary Table -->
    <div class="card border-0 mb-4" style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden;">
        <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold m-0 text-dark">
                <i class="fa-solid fa-users-gear me-2 text-primary"></i>{{ __('Cashier Performance Summary') }}
            </h6>
            <span class="badge bg-light text-dark fw-bold" style="font-size: 11px;">{{ $cashierPerformances->count() }} {{ __('Active Cashier(s)') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead style="background: #f1f5f9; color: #475569;">
                    <tr>
                        <th class="ps-3">{{ __('Cashier Name') }}</th>
                        <th class="text-center">{{ __('Bills Created') }}</th>
                        <th class="text-end">{{ __('Total Sales') }}</th>
                        <th class="text-end">{{ __('Avg Bill Amount') }}</th>
                        <th class="text-end">{{ __('Cash Sales') }}</th>
                        <th class="text-end">{{ __('Card/Online') }}</th>
                        <th class="text-end">{{ __('Returned/Void') }}</th>
                        <th class="text-end">{{ __('Net Collected') }}</th>
                        <th>{{ __('First Bill') }}</th>
                        <th>{{ __('Last Bill') }}</th>
                        <th>{{ __('Working Time') }}</th>
                        <th>{{ __('Avg Time / Bill') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashierPerformances as $cp)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">
                                <i class="fa-solid fa-user-circle me-1 text-secondary"></i>{{ $cp['cashier_name'] }}
                            </td>
                            <td class="text-center fw-bold">
                                <span class="badge bg-primary rounded-pill px-2 py-1">{{ $cp['total_bills'] }}</span>
                            </td>
                            <td class="text-end fw-bold text-dark">{{ $currency }}{{ number_format($cp['gross_sales'], 2) }}</td>
                            <td class="text-end text-info fw-bold">{{ $currency }}{{ number_format($cp['avg_bill_amount'], 2) }}</td>
                            <td class="text-end text-success">{{ $currency }}{{ number_format($cp['cash_sales'], 2) }}</td>
                            <td class="text-end text-primary">{{ $currency }}{{ number_format($cp['card_sales'], 2) }}</td>
                            <td class="text-end text-danger">
                                @if($cp['cancelled_amount'] > 0)
                                    {{ $currency }}{{ number_format($cp['cancelled_amount'], 2) }}
                                    <small class="d-block text-muted" style="font-size: 10px;">({{ $cp['cancelled_count'] }} {{ __('bills') }})</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success" style="font-size: 14px;">
                                {{ $currency }}{{ number_format($cp['net_collected'], 2) }}
                            </td>
                            <td class="small text-muted">{{ $cp['first_bill_time'] }}</td>
                            <td class="small text-muted">{{ $cp['last_bill_time'] }}</td>
                            <td>
                                <span class="badge bg-secondary font-monospace" style="font-size: 11px;">{{ $cp['working_time'] }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">{{ $cp['avg_time_between'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                <i class="fa-solid fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                                {{ __('No cashier performance records found for the selected filter period.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Bill Audit List -->
    <div class="card border-0 mb-4" style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden;">
        <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold m-0 text-dark">
                <i class="fa-solid fa-list-check me-2 text-primary"></i>{{ __('Detailed Bill Audit List') }}
            </h6>
            <small class="text-muted">{{ __('Showing') }} {{ $detailOrders->firstItem() ?? 0 }} {{ __('to') }} {{ $detailOrders->lastItem() ?? 0 }} {{ __('of') }} {{ $detailOrders->total() }} {{ __('entries') }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead style="background: #f8fafc; color: #475569;">
                    <tr>
                        <th class="ps-3">{{ __('Invoice No') }}</th>
                        <th>{{ __('Date & Time') }}</th>
                        <th>{{ __('Cashier') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-center">{{ __('Items') }}</th>
                        <th class="text-end">{{ __('Gross Amt') }}</th>
                        <th class="text-end">{{ __('Tax Amt') }}</th>
                        <th class="text-end">{{ __('Net Amount') }}</th>
                        <th>{{ __('Payment') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end pe-3">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailOrders as $o)
                        @php
                            $status = strtolower($o->order_status->value ?? $o->order_status);
                            $badgeClass = 'bg-success';
                            if ($status === 'returned') $badgeClass = 'bg-danger';
                            elseif ($status === 'partial returned') $badgeClass = 'bg-warning text-dark';
                            elseif ($status === 'canceled' || $status === 'cancelled') $badgeClass = 'bg-secondary';
                        @endphp
                        <tr>
                            <td class="ps-3 fw-bold text-primary">{{ $o->prefix }}-{{ $o->order_code }}</td>
                            <td class="small text-muted">{{ $o->created_at->format('Y-m-d h:i A') }}</td>
                            <td class="fw-bold text-dark">
                                {{ $o->cashier?->name ?? 'Unassigned Cashier' }}
                            </td>
                            <td>
                                <div>{{ $o->customer?->user?->name ?? 'Walk-in Customer' }}</div>
                                <small class="text-muted font-monospace" style="font-size: 11px;">{{ $o->customer?->user?->phone ?? '-' }}</small>
                            </td>
                            <td class="text-center fw-bold">{{ $o->products->sum('pivot.quantity') }}</td>
                            <td class="text-end">{{ $currency }}{{ number_format($o->total_amount, 2) }}</td>
                            <td class="text-end text-danger">{{ $currency }}{{ number_format($o->tax_amount, 2) }}</td>
                            <td class="text-end fw-bold text-success">{{ $currency }}{{ number_format($o->payable_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size: 11px;">
                                    {{ str_replace(' Payment', '', $o->payment_method->value ?? $o->payment_method) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }}" style="font-size: 11px;">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-dark btn-xs py-0.5 px-2 fw-bold text-white" style="font-size: 11px;" onclick="openPOSPrintPreview({{ $o->id }})">
                                    <i class="fa-solid fa-eye me-1"></i>{{ __('View') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                {{ __('No detailed invoice records found for this selection.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top py-3 px-3">
            {{ $detailOrders->withQueryString()->links() }}
        </div>
    </div>

</div>

<!-- Thermal / A4 Print Preview Overlay Modal -->
<div id="pos-invoice-print-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="card border-0 shadow-lg" id="pos-invoice-modal-card" style="width: 90%; max-width: 440px; max-height: 90vh; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
        <div class="card-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold" style="font-size: 14px;" id="pos-invoice-modal-title"><i class="fa-solid fa-receipt me-2"></i>Invoice Preview</span>
            <button type="button" class="btn-close btn-close-white" onclick="closePOSPrintPreview()"></button>
        </div>
        <div class="card-body p-3 overflow-auto" id="pos-invoice-print-body" style="background: #f8fafc;">
            <!-- Rendered via AJAX -->
        </div>
        <div class="card-footer bg-white py-2 px-3 d-flex justify-content-between border-top">
            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="closePOSPrintPreview()">Close</button>
            <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="printPOSInvoice()"><i class="fa-solid fa-print me-1"></i>Print Invoice</button>
        </div>
    </div>
</div>

<script>
    function toggleCustomDates(val) {
        if (val === 'custom') {
            $('.custom-date-box').removeClass('d-none');
        } else {
            $('.custom-date-box').addClass('d-none');
        }
    }

    function openPOSPrintPreview(orderId) {
        $.ajax({
            url: `/shop/pos/${orderId}/thermal-preview`,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $('#pos-invoice-print-body').html(html);
                document.getElementById('pos-invoice-print-overlay').style.display = 'flex';
            }
        });
    }

    function closePOSPrintPreview() {
        document.getElementById('pos-invoice-print-overlay').style.display = 'none';
        $('#pos-invoice-print-body').html('');
    }

    function printPOSInvoice() {
        const content = document.getElementById('pos-invoice-print-body').innerHTML;
        const win = window.open('', '_blank', 'width=800,height=800');
        win.document.write('<html><head><title>Print Invoice</title>');
        win.document.write('<link rel="stylesheet" href="{{ asset("assets/css/bootstrap.min.css") }}"/>');
        win.document.write('</head><body onload="window.print(); window.close();">');
        win.document.write(content);
        win.document.write('</body></html>');
        win.document.close();
    }
</script>
@endsection
