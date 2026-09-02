@extends('layouts.app')

@section('title', __('Outstanding Ageing Matrix (Debtors & Creditors)'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>{{ __('Outstanding Ageing Analysis Matrix') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Customer Receivables (Debtors) & Vendor Payables (Creditors) Ageing Buckets') }}</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.outstandingAgeing') }}" class="row g-2 align-items-center">
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('Report Type:') }}</label></div>
                <div class="col-auto">
                    <select name="type" class="form-select form-select-sm">
                        <option value="DEBTORS" {{ $type === 'DEBTORS' ? 'selected' : '' }}>{{ __('Customer Receivables (Debtors)') }}</option>
                        <option value="CREDITORS" {{ $type === 'CREDITORS' ? 'selected' : '' }}>{{ __('Vendor Payables (Creditors)') }}</option>
                    </select>
                </div>
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('As of Date:') }}</label></div>
                <div class="col-auto"><input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate }}"></div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary fw-bold px-3">{{ __('Generate Matrix') }}</button></div>
            </form>
        </div>
    </div>

    <!-- Ageing Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3">
                    <div class="small text-uppercase fw-bold">{{ __('0 - 30 Days (Current)') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($ageingData['summary']['bucket_0_30'], 2) }}</h3>
                    <div class="small">{{ __('Normal Credit Period') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-3">
                    <div class="small text-uppercase fw-bold">{{ __('31 - 60 Days') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($ageingData['summary']['bucket_31_60'], 2) }}</h3>
                    <div class="small">{{ __('Slightly Overdue') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3">
                    <div class="small text-uppercase fw-bold">{{ __('61 - 90 Days') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($ageingData['summary']['bucket_61_90'], 2) }}</h3>
                    <div class="small">{{ __('Severely Overdue') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-3">
                    <div class="small text-uppercase fw-bold">{{ __('90+ Days (Critical)') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($ageingData['summary']['bucket_90_plus'], 2) }}</h3>
                    <div class="small">{{ __('High Risk / Bad Debt Warning') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Matrix Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-list me-2"></i> {{ __('Outstanding Ledger Breakdown') }}</span>
            <span class="badge bg-primary fs-6 font-monospace">Total Outstanding: ₹{{ number_format($ageingData['summary']['total_outstanding'], 2) }}</span>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('Party Name') }}</th>
                            <th>{{ __('Invoice / Voucher No') }}</th>
                            <th>{{ __('Invoice Date') }}</th>
                            <th class="text-end">{{ __('Age (Days)') }}</th>
                            <th class="text-end">{{ __('Outstanding Amount') }}</th>
                            <th>{{ __('Ageing Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ageingData['details'] as $det)
                        <tr>
                            <td class="fw-bold">{{ $det['party_name'] }}</td>
                            <td>{{ $det['invoice_number'] }}</td>
                            <td>{{ $det['invoice_date'] }}</td>
                            <td class="text-end font-monospace">{{ $det['age_days'] }} Days</td>
                            <td class="text-end font-monospace fw-bold">₹{{ number_format($det['amount'], 2) }}</td>
                            <td>
                                @if($det['bucket'] === '0_30')
                                    <span class="badge bg-success">0 - 30 Days (Current)</span>
                                @elseif($det['bucket'] === '31_60')
                                    <span class="badge bg-info">31 - 60 Days</span>
                                @elseif($det['bucket'] === '61_90')
                                    <span class="badge bg-warning text-dark">61 - 90 Days</span>
                                @else
                                    <span class="badge bg-danger">90+ Days (Critical)</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">{{ __('No outstanding balances found for selected criteria') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
