@extends('layouts.app')

@section('title', __('Purchase Returns & Debit Notes'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-truck-ramp-box me-2"></i>{{ __('Purchase Returns & Debit Notes') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Vendor return processing, barcode inventory reduction, and Input GST (ITC) reversals') }}</p>
        </div>
        <div>
            <a href="{{ route('shop.reports.accountingDashboard') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Hub') }}
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light p-2">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold small text-uppercase" data-bs-toggle="tab" data-bs-target="#debit-notes-tab" type="button">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> {{ __('Posted Debit Notes') }} ({{ $debitNotes->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold small text-uppercase" data-bs-toggle="tab" data-bs-target="#eligible-purchases-tab" type="button">
                        <i class="fa-solid fa-boxes-packing me-1"></i> {{ __('Purchases Eligible for Return') }} ({{ $eligiblePurchases->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3">
            <div class="tab-content">
                <!-- Posted Debit Notes Pane -->
                <div class="tab-pane fade show active" id="debit-notes-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('Debit Note No') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Supplier / Party') }}</th>
                                    <th>{{ __('Particulars') }}</th>
                                    <th class="text-end">{{ __('Reversed Taxable') }}</th>
                                    <th class="text-end">{{ __('Reversed ITC') }}</th>
                                    <th class="text-end">{{ __('Total Debit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($debitNotes as $dn)
                                @php
                                    $suppDr = $dn->entries->whereIn('type', ['Dr', 'debit'])->sum('amount');
                                    $retCr = $dn->entries->where('account.code', 'PUR_RET')->sum('amount');
                                    $itcCr = $dn->entries->whereIn('account.code', ['CGST_IN', 'SGST_IN', 'IGST_IN'])->sum('amount');
                                @endphp
                                <tr>
                                    <td class="fw-bold text-primary font-monospace">{{ $dn->voucher_no }}</td>
                                    <td>{{ $dn->date ? date('d-M-Y', strtotime($dn->date)) : '-' }}</td>
                                    <td class="fw-bold">{{ $dn->entries->whereIn('type', ['Dr', 'debit'])->first()?->account?->name ?? 'Supplier' }}</td>
                                    <td>{{ $dn->narration }}</td>
                                    <td class="text-end font-monospace text-secondary">₹{{ number_format($retCr, 2) }}</td>
                                    <td class="text-end font-monospace text-danger">₹{{ number_format($itcCr, 2) }}</td>
                                    <td class="text-end fw-bold font-monospace text-success">₹{{ number_format($suppDr, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">{{ __('No Debit Note vouchers posted yet') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Eligible Purchases Pane -->
                <div class="tab-pane fade" id="eligible-purchases-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Purchase ID') }}</th>
                                    <th>{{ __('Bill Date') }}</th>
                                    <th>{{ __('Invoice / Voucher No') }}</th>
                                    <th>{{ __('Supplier Name') }}</th>
                                    <th class="text-end">{{ __('Taxable Amount') }}</th>
                                    <th class="text-end">{{ __('Input GST (ITC)') }}</th>
                                    <th class="text-end">{{ __('Grand Total') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($eligiblePurchases as $p)
                                <tr>
                                    <td class="font-monospace">#{{ $p->id }}</td>
                                    <td>{{ $p->bill_date ?? '-' }}</td>
                                    <td class="fw-bold text-primary">{{ $p->inwardInvoice?->inward_voucher_no ?? $p->bill_no ?? '-' }}</td>
                                    <td class="fw-bold">{{ $p->inwardInvoice?->partyCode?->accountName ?? 'Supplier' }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($p->total_taxable, 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($p->total_cgst + $p->total_sgst + $p->total_igst, 2) }}</td>
                                    <td class="text-end fw-bold font-monospace">₹{{ number_format($p->grand_total, 2) }}</td>
                                    <td>
                                        @if($p->is_return)
                                            <span class="badge bg-warning text-dark">{{ __('Return Processed') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Finalized Purchase') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">{{ __('No purchase invoices available') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
