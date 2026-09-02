@extends('layouts.app')

@section('title', __('COD Remittance & Reconciliation'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-hand-holding-dollar me-2"></i>{{ __('COD Remittance & Settlement Reconciliation') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Tracking E-Commerce COD receivables (CUST_WEB), bank remittances, and courier expense deductions') }}</p>
        </div>
        <div>
            <a href="{{ route('shop.reports.accountingDashboard') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Hub') }}
            </a>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Net COD Receivable in General Ledger') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($codSummary['net_cod_receivable_in_gl'], 2) }}</h3>
                    <div class="small text-white-50">{{ __('Ledger Account: CUST_WEB (ID 23)') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Delivered COD Orders Pending') }}</div>
                    <h3 class="fw-bold my-1">{{ number_format($codSummary['total_delivered_cod_orders']) }}</h3>
                    <div class="small text-white-50">{{ __('Orders delivered awaiting courier payout') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Reconciliation Mode') }}</div>
                    <h5 class="fw-bold my-1"><i class="fa-solid fa-file-invoice me-1"></i> {{ __('Bank Statement Matching') }}</h5>
                    <div class="small text-white-50">{{ __('Manual settlement / remittance voucher posting') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivered COD Orders Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-truck-fast me-1"></i> {{ __('Delivered COD Orders (Recognized into CUST_WEB)') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('Order Code') }}</th>
                            <th>{{ __('Delivered At') }}</th>
                            <th>{{ __('AWB Number') }}</th>
                            <th>{{ __('Shiprocket Status') }}</th>
                            <th class="text-end">{{ __('Delivery Charge') }}</th>
                            <th class="text-end">{{ __('Payable COD Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($codSummary['delivered_orders'] as $ord)
                        <tr>
                            <td class="fw-bold font-monospace text-primary">#{{ $ord->prefix }}{{ $ord->order_code }}</td>
                            <td>{{ $ord->delivered_at ?? '-' }}</td>
                            <td class="font-monospace">{{ $ord->shiprocket_awb_code ?? '-' }}</td>
                            <td><span class="badge bg-success">{{ $ord->shiprocket_status ?? 'Delivered' }}</span></td>
                            <td class="text-end font-monospace">₹{{ number_format($ord->delivery_charge, 2) }}</td>
                            <td class="text-end fw-bold font-monospace text-success">₹{{ number_format($ord->payable_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No delivered COD orders awaiting remittance at this time') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Settlement History -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-receipt me-1"></i> {{ __('Posted COD Remittance Settlement Vouchers') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Receipt Voucher No') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Particulars') }}</th>
                            <th class="text-end">{{ __('Net Deposited in Bank') }}</th>
                            <th class="text-end">{{ __('Courier Fee Deductions') }}</th>
                            <th class="text-end">{{ __('Cleared COD Receivable') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settlements as $st)
                        @php
                            $bankDr = $st->entries->whereIn('account.code', ['BANK_HDFC', 'BANK'])->sum('amount');
                            $expDr = $st->entries->whereIn('account.code', ['EXP_PACKING', 'EXP_FREIGHT'])->sum('amount');
                            $codCr = $st->entries->where('account.code', 'CUST_WEB')->sum('amount');
                        @endphp
                        <tr>
                            <td class="fw-bold font-monospace text-primary">{{ $st->voucher_no }}</td>
                            <td>{{ $st->date ? date('d-M-Y', strtotime($st->date)) : '-' }}</td>
                            <td>{{ $st->narration }}</td>
                            <td class="text-end font-monospace text-success fw-bold">₹{{ number_format($bankDr, 2) }}</td>
                            <td class="text-end font-monospace text-danger">₹{{ number_format($expDr, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-dark">₹{{ number_format($codCr, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No COD remittance settlement vouchers recorded yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
