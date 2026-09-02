@extends('layouts.shop')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="fa-solid fa-scale-balanced me-2 text-primary"></i>POS Payment Reconciliation Dashboard</h4>
        <button class="btn btn-sm btn-outline-primary" onclick="exportReconciliationReport()"><i class="fa-solid fa-file-export me-1"></i> Export Daily Reconciliation</button>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('pos.reconciliation.index') }}" class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Provider</label>
                    <select name="provider" class="form-select form-select-sm">
                        <option value="">All Providers</option>
                        <option value="paytm" {{ request('provider') == 'paytm' ? 'selected' : '' }}>Paytm</option>
                        <option value="phonepe" {{ request('provider') == 'phonepe' ? 'selected' : '' }}>PhonePe</option>
                        <option value="mock" {{ request('provider') == 'mock' ? 'selected' : '' }}>Mock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="unknown" {{ request('status') == 'unknown' ? 'selected' : '' }}>Unknown</option>
                        <option value="finalized" {{ request('status') == 'finalized' ? 'selected' : '' }}>Finalized</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="amount_mismatch" {{ request('status') == 'amount_mismatch' ? 'selected' : '' }}>Amount Mismatch</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Tx ID / RRN</label>
                    <input type="text" name="transaction_id" class="form-control form-control-sm" value="{{ request('transaction_id') }}" placeholder="Search Tx ID...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date', date('Y-m-d')) }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attempt Register Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Attempt ID / Date</th>
                            <th>Terminal / Counter</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Tx ID / RRN</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attempts as $attempt)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ substr($attempt->id, 0, 8) }}...</div>
                                <div class="text-muted small">{{ $attempt->created_at ? $attempt->created_at->format('d/m/Y H:i:s') : '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $attempt->terminal->name ?? 'Terminal' }}</div>
                                <div class="text-muted small">{{ strtoupper($attempt->provider->value ?? $attempt->provider) }} ({{ $attempt->terminal_id }})</div>
                            </td>
                            <td><span class="badge bg-secondary">{{ strtoupper($attempt->payment_method->value ?? $attempt->payment_method) }}</span></td>
                            <td class="fw-bold">₹{{ number_format($attempt->amount, 2) }}</td>
                            <td>
                                <div class="font-monospace small">{{ $attempt->transaction_id ?? '-' }}</div>
                                <div class="text-muted small">RRN: {{ $attempt->reference_no ?? '-' }}</div>
                            </td>
                            <td>
                                @if($attempt->status->value == 'finalized')
                                    <span class="badge bg-success">FINALIZED</span>
                                @elseif($attempt->status->value == 'unknown')
                                    <span class="badge bg-warning text-dark">UNKNOWN</span>
                                @elseif($attempt->status->value == 'amount_mismatch')
                                    <span class="badge bg-danger">AMOUNT MISMATCH</span>
                                @else
                                    <span class="badge bg-secondary">{{ strtoupper($attempt->status->value) }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($attempt->status->value == 'unknown')
                                    <button class="btn btn-xs btn-outline-primary me-1" onclick="recheckAttempt('{{ $attempt->id }}')"><i class="fa-solid fa-rotate"></i> Recheck</button>
                                    <button class="btn btn-xs btn-outline-danger me-1" onclick="markFailure('{{ $attempt->id }}')"><i class="fa-solid fa-xmark"></i> Mark Failed</button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No terminal payment attempts found.</td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light p-2">
            {{ $attempts->links() }}
        </div>
    </div>
</div>

<script>
function recheckAttempt(id) {
    $.post('/shop/pos/reconciliation/' + id + '/recheck', { _token: '{{ csrf_token() }}' }, function(res) {
        alert('Recheck status: ' + (res.result ? res.result.status : 'complete'));
        location.reload();
    });
}

function markFailure(id) {
    const reason = prompt('Enter manager verification reason for marking failure:');
    if (!reason) return;
    $.post('/shop/pos/reconciliation/' + id + '/mark-failed', { _token: '{{ csrf_token() }}', reason: reason }, function(res) {
        alert('Payment attempt marked as failed.');
        location.reload();
    });
}

function exportReconciliationReport() {
    $.get('/shop/pos/reconciliation/export', function(res) {
        alert('Daily Reconciliation Export:\nSales Count: ' + res.reconciliation.total_sales_count + '\nTotal Sales: ₹' + res.reconciliation.total_sales_amount);
    });
}
</script>
@endsection
