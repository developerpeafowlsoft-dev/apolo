@extends('layouts.app')

@section('title', __('General Journal & Vouchers'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-receipt me-2"></i>{{ __('General Journal & Vouchers') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Complete audit trail of double-entry Sales, Purchases, Payments, Receipts, and Debit/Credit Notes') }}</p>
        </div>
        <div>
            <a href="{{ route('shop.reports.accountingDashboard') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Hub') }}
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.vouchers') }}" class="row g-2 align-items-center">
                <div class="col-auto">
                    <select name="voucher_type" class="form-select form-select-sm">
                        <option value="">{{ __('All Voucher Types') }}</option>
                        @foreach($voucherTypes as $vt)
                            <option value="{{ $vt }}" {{ $voucherType === $vt ? 'selected' : '' }}>{{ $vt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}"></div>
                <div class="col-auto"><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}"></div>
                <div class="col-auto"><input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('Search Voucher / Narration...') }}" value="{{ $search }}"></div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary fw-bold px-3">{{ __('Filter') }}</button></div>
                @if($voucherType || $search)
                    <div class="col-auto"><a href="{{ route('shop.reports.vouchers') }}" class="btn btn-sm btn-outline-secondary">{{ __('Reset') }}</a></div>
                @endif
            </form>
        </div>
    </div>

    <!-- Vouchers List Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 130px;">{{ __('Voucher No') }}</th>
                            <th style="width: 110px;">{{ __('Date') }}</th>
                            <th style="width: 120px;">{{ __('Type') }}</th>
                            <th>{{ __('Narration / Particulars') }}</th>
                            <th class="text-end" style="width: 140px;">{{ __('Debit (Dr)') }}</th>
                            <th class="text-end" style="width: 140px;">{{ __('Credit (Cr)') }}</th>
                            <th class="text-center" style="width: 80px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers as $v)
                        @php
                            $drSum = $v->entries->whereIn('type', ['Dr', 'debit'])->sum('amount');
                            $crSum = $v->entries->whereIn('type', ['Cr', 'credit'])->sum('amount');
                            $isBalanced = abs($drSum - $crSum) < 0.01;
                        @endphp
                        <tr>
                            <td class="fw-bold text-primary font-monospace">{{ $v->voucher_no }}</td>
                            <td>{{ $v->date ? date('d-M-Y', strtotime($v->date)) : '-' }}</td>
                            <td>
                                <span class="badge 
                                    {{ $v->voucher_type === 'Sales' ? 'bg-success' : '' }}
                                    {{ $v->voucher_type === 'Purchase' ? 'bg-primary' : '' }}
                                    {{ $v->voucher_type === 'Payment' ? 'bg-danger' : '' }}
                                    {{ $v->voucher_type === 'Receipt' ? 'bg-info text-dark' : '' }}
                                    {{ $v->voucher_type === 'Debit Note' ? 'bg-warning text-dark' : '' }}
                                    {{ $v->voucher_type === 'Credit Note' ? 'bg-secondary' : '' }}
                                    {{ !in_array($v->voucher_type, ['Sales','Purchase','Payment','Receipt','Debit Note','Credit Note']) ? 'bg-dark' : '' }}">
                                    {{ $v->voucher_type }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $v->narration ?? 'General Transaction' }}</div>
                                @if(!$isBalanced)
                                    <span class="badge bg-danger small mt-1">{{ __('Unbalanced Entry Warning') }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold font-monospace text-success">₹{{ number_format($drSum, 2) }}</td>
                            <td class="text-end fw-bold font-monospace text-danger">₹{{ number_format($crSum, 2) }}</td>
                            <td class="text-center">
                                <button class="btn btn-xs btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#v-entries-{{ $v->id }}">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </td>
                        </tr>
                        <!-- Collapsible Entries Breakdown -->
                        <tr class="collapse bg-light" id="v-entries-{{ $v->id }}">
                            <td colspan="7" class="p-3">
                                <div class="card border border-secondary-subtle">
                                    <div class="card-header bg-white py-1 px-3 d-flex justify-content-between">
                                        <span class="fw-bold small text-secondary"><i class="fa-solid fa-list-check me-1"></i> {{ __('Voucher Ledger Entries Breakdown') }} ({{ $v->voucher_no }})</span>
                                        <span class="small font-monospace text-muted">{{ $isBalanced ? 'Balanced (Dr = Cr)' : 'Mismatch' }}</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Account Name') }}</th>
                                                    <th>{{ __('Group') }}</th>
                                                    <th>{{ __('Description') }}</th>
                                                    <th class="text-end" style="width: 130px;">{{ __('Dr Amount') }}</th>
                                                    <th class="text-end" style="width: 130px;">{{ __('Cr Amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($v->entries as $entry)
                                                <tr>
                                                    <td class="fw-bold text-dark">{{ $entry->account?->name ?? 'Account #' . $entry->account_id }}</td>
                                                    <td><span class="badge bg-light text-dark border">{{ $entry->account?->accountGroup?->name ?? '-' }}</span></td>
                                                    <td class="text-muted small">{{ $entry->description ?? '-' }}</td>
                                                    <td class="text-end font-monospace {{ in_array($entry->type, ['Dr', 'debit']) ? 'fw-bold text-success' : 'text-muted' }}">
                                                        {{ in_array($entry->type, ['Dr', 'debit']) ? '₹' . number_format($entry->amount, 2) : '-' }}
                                                    </td>
                                                    <td class="text-end font-monospace {{ in_array($entry->type, ['Cr', 'credit']) ? 'fw-bold text-danger' : 'text-muted' }}">
                                                        {{ in_array($entry->type, ['Cr', 'credit']) ? '₹' . number_format($entry->amount, 2) : '-' }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ __('No voucher records found for this period') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($vouchers->hasPages())
        <div class="card-footer bg-white p-2">
            {{ $vouchers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
