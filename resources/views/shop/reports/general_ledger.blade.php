@extends('layouts.app')

@section('title', __('General Ledger Statement'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-book-open me-2"></i>{{ __('General Ledger Statement') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Account-wise detailed ledger statement with opening balance, transactions, and running balances') }}</p>
        </div>
        <div>
            <a href="{{ route('shop.reports.accountingDashboard') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Hub') }}
            </a>
        </div>
    </div>

    <!-- Account Selector & Date Filter -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.generalLedger') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="account_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $selectedAccount?->id === $acc->id ? 'selected' : '' }}>
                                [{{ $acc->code }}] {{ $acc->name }} ({{ $acc->accountGroup?->name ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}"></div>
                <div class="col-auto"><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}"></div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary fw-bold px-3">{{ __('Generate Statement') }}</button></div>
            </form>
        </div>
    </div>

    @if($ledgerData)
    <!-- Account Summary Header -->
    <div class="card border-0 shadow-sm mb-3 bg-light">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="fw-bold text-dark mb-1">{{ $ledgerData['account_name'] }} <span class="badge bg-secondary font-monospace">{{ $ledgerData['account_code'] }}</span></h5>
                    <div class="text-muted small">{{ __('Group:') }} <strong>{{ $ledgerData['account_group'] }}</strong> | {{ __('Period:') }} <strong>{{ $ledgerData['period'] }}</strong></div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="me-3">{{ __('Opening Balance:') }} <strong class="font-monospace">₹{{ number_format($ledgerData['opening_balance'], 2) }}</strong></span>
                    <span>{{ __('Closing Balance:') }} <strong class="font-monospace text-primary fs-6">₹{{ number_format($ledgerData['closing_balance'], 2) }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 120px;">{{ __('Date') }}</th>
                            <th style="width: 140px;">{{ __('Voucher No') }}</th>
                            <th style="width: 120px;">{{ __('Type') }}</th>
                            <th>{{ __('Particulars / Narration') }}</th>
                            <th class="text-end" style="width: 130px;">{{ __('Debit (Dr)') }}</th>
                            <th class="text-end" style="width: 130px;">{{ __('Credit (Cr)') }}</th>
                            <th class="text-end" style="width: 140px;">{{ __('Running Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance Row -->
                        <tr class="table-warning">
                            <td>{{ $startDate }}</td>
                            <td colspan="3" class="fw-bold text-secondary">{{ __('Opening Balance B/F') }}</td>
                            <td class="text-end">-</td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold font-monospace">₹{{ number_format($ledgerData['opening_balance'], 2) }}</td>
                        </tr>

                        @forelse($ledgerData['rows'] as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td class="fw-bold font-monospace text-primary">{{ $row['voucher_no'] }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $row['voucher_type'] }}</span></td>
                            <td>{{ $row['narration'] }}</td>
                            <td class="text-end font-monospace {{ $row['debit'] > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                                {{ $row['debit'] > 0 ? '₹' . number_format($row['debit'], 2) : '-' }}
                            </td>
                            <td class="text-end font-monospace {{ $row['credit'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $row['credit'] > 0 ? '₹' . number_format($row['credit'], 2) : '-' }}
                            </td>
                            <td class="text-end fw-bold font-monospace text-dark">
                                ₹{{ number_format($row['running_balance'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ __('No transaction entries recorded for this account in the selected period') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td colspan="4">{{ __('Total Period Movements & Closing Balance') }}</td>
                            <td class="text-end font-monospace text-success">₹{{ number_format($ledgerData['total_debit'], 2) }}</td>
                            <td class="text-end font-monospace text-danger">₹{{ number_format($ledgerData['total_credit'], 2) }}</td>
                            <td class="text-end font-monospace text-primary fs-6">₹{{ number_format($ledgerData['closing_balance'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
