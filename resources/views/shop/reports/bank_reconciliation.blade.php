@extends('layouts.app')

@section('title', __('Cash & Bank Reconciliation Statement (BRS)'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-building-columns me-2"></i>{{ __('Bank Reconciliation Statement (BRS)') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Reconcile Cash Book Ledger Balance against Bank Passbook Statement') }}</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.bankReconciliation') }}" class="row g-2 align-items-center">
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('As of Date:') }}</label></div>
                <div class="col-auto"><input type="date" name="as_of_date" class="form-control form-control-sm" value="{{ $asOfDate }}"></div>
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('Bank Passbook Balance:') }}</label></div>
                <div class="col-auto"><input type="number" step="0.01" name="passbook_balance" class="form-control form-control-sm" value="{{ $passbookBal }}" placeholder="0.00"></div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary fw-bold px-3"><i class="fa-solid fa-calculator me-1"></i> {{ __('Reconcile BRS') }}</button></div>
            </form>
        </div>
    </div>

    <!-- BRS Output Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="fa-solid fa-scale-balanced me-2"></i> {{ __('BRS Statement Summary - ') }} {{ $brsData['account_name'] }}
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <tbody>
                        <tr>
                            <td class="fw-bold fs-6">{{ __('Balance as per Company Cash Book (ERP Ledger)') }}</td>
                            <td class="text-end font-monospace fw-bold fs-6 text-primary">₹{{ number_format($brsData['balance_as_per_cash_book'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-plus text-success me-2"></i> {{ __('Add: Cheques Issued but Not Yet Presented for Payment') }}</td>
                            <td class="text-end font-monospace text-success">₹{{ number_format($brsData['add_cheques_issued_not_presented'], 2) }}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-minus text-danger me-2"></i> {{ __('Less: Cheques Deposited but Not Yet Cleared by Bank') }}</td>
                            <td class="text-end font-monospace text-danger">₹{{ number_format($brsData['less_cheques_deposited_not_cleared'], 2) }}</td>
                        </tr>
                        <tr class="table-secondary">
                            <td class="fw-bold h6 mb-0">{{ __('Reconciled Bank Balance') }}</td>
                            <td class="text-end font-monospace fw-bold h6 mb-0 text-success">₹{{ number_format($brsData['reconciled_balance'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">{{ __('Physical Bank Passbook Statement Balance') }}</td>
                            <td class="text-end font-monospace fw-bold">₹{{ number_format($brsData['passbook_balance'], 2) }}</td>
                        </tr>
                        <tr class="{{ $brsData['is_fully_reconciled'] ? 'table-success' : 'table-warning' }}">
                            <td class="fw-bold">{{ __('BRS Variance Difference') }}</td>
                            <td class="text-end font-monospace fw-bold {{ $brsData['is_fully_reconciled'] ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($brsData['variance_difference'], 2) }}
                                @if($brsData['is_fully_reconciled'])
                                    <span class="badge bg-success ms-2"><i class="fa-solid fa-check me-1"></i> {{ __('RECONCILED') }}</span>
                                @else
                                    <span class="badge bg-danger ms-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> {{ __('VARIANCE') }}</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
