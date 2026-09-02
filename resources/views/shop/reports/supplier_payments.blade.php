@extends('layouts.app')

@section('title', __('Supplier Bill Settlements'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-money-check-dollar me-2"></i>{{ __('Supplier Bill Settlements & Payments') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Bill-by-bill vendor payment allocation, invoice outstanding tracking, and disbursement history') }}</p>
        </div>
        <div>
            <a href="{{ route('shop.reports.accountingDashboard') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Hub') }}
            </a>
        </div>
    </div>

    <!-- Supplier Selector -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.supplierPayments') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <label class="small fw-bold mb-1">{{ __('Select Supplier / Trade Creditor:') }}</label>
                    <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="0">{{ __('-- Choose a Supplier to View Outstanding Bills --') }}</option>
                        @foreach($suppliers as $supp)
                            <option value="{{ $supp->id }}" {{ $supplierId === $supp->id ? 'selected' : '' }}>
                                {{ $supp->accountName }} ({{ $supp->accountshortcode }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto align-self-end">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">{{ __('View Bills') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if($outstandingBills)
    <!-- Outstanding Summary -->
    <div class="card border-0 shadow-sm mb-3 bg-light">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="fw-bold text-dark mb-1">{{ $outstandingBills['supplier_name'] }} <span class="badge bg-secondary">{{ $outstandingBills['supplier_code'] }}</span></h5>
                    <div class="text-muted small">{{ __('Total Purchase Invoices:') }} <strong>{{ $outstandingBills['total_invoices_count'] }}</strong></div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="me-3">{{ __('Total Invoiced:') }} <strong class="font-monospace">₹{{ number_format($outstandingBills['total_original_amount'], 2) }}</strong></span>
                    <span>{{ __('Total Net Outstanding:') }} <strong class="font-monospace text-danger fs-6">₹{{ number_format($outstandingBills['total_outstanding_amount'], 2) }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bills Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-file-invoice me-1"></i> {{ __('Purchase Invoices Breakdown') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('Invoice / Voucher No') }}</th>
                            <th>{{ __('Bill Date') }}</th>
                            <th class="text-end">{{ __('Original Amount') }}</th>
                            <th class="text-end">{{ __('Debit Notes (Returns)') }}</th>
                            <th class="text-end">{{ __('Previous Payments') }}</th>
                            <th class="text-end">{{ __('Net Outstanding Due') }}</th>
                            <th class="text-center">{{ __('Settlement Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outstandingBills['bills'] as $b)
                        <tr>
                            <td class="fw-bold text-primary font-monospace">{{ $b['invoice_no'] }}</td>
                            <td>{{ $b['bill_date'] ?? '-' }}</td>
                            <td class="text-end font-monospace">₹{{ number_format($b['original_amount'], 2) }}</td>
                            <td class="text-end font-monospace text-warning">₹{{ number_format($b['debit_notes_amount'], 2) }}</td>
                            <td class="text-end font-monospace text-success">₹{{ number_format($b['paid_amount'], 2) }}</td>
                            <td class="text-end font-monospace fw-bold {{ $b['outstanding_amount'] > 0 ? 'text-danger' : 'text-muted' }}">
                                ₹{{ number_format($b['outstanding_amount'], 2) }}
                            </td>
                            <td class="text-center">
                                @if($b['is_fully_settled'])
                                    <span class="badge bg-success">{{ __('Settled (₹0 Due)') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Pending Payment') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">{{ __('No purchase invoices found for this supplier') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Payment History -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-history me-1"></i> {{ __('Recent Supplier Payment Vouchers') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Payment Voucher No') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Supplier / Particulars') }}</th>
                            <th>{{ __('Disbursed Via') }}</th>
                            <th class="text-end">{{ __('Total Paid Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentHistory as $pmt)
                        @php
                            $paidAmount = $pmt->entries->whereIn('type', ['Dr', 'debit'])->sum('amount');
                            $crAccount = $pmt->entries->whereIn('type', ['Cr', 'credit'])->first()?->account?->name ?? 'Bank / Cash';
                        @endphp
                        <tr>
                            <td class="fw-bold font-monospace text-primary">{{ $pmt->voucher_no }}</td>
                            <td>{{ $pmt->date ? date('d-M-Y', strtotime($pmt->date)) : '-' }}</td>
                            <td>{{ $pmt->narration }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $crAccount }}</span></td>
                            <td class="text-end fw-bold font-monospace text-success">₹{{ number_format($paidAmount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No payment vouchers recorded') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
