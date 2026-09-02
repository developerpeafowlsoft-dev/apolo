@extends('layouts.app')

@section('title', __('GST Statutory Engine & Compliance Dashboard'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-file-invoice-dollar me-2"></i>{{ __('GST Statutory Engine & Tax Dashboard') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Indian Retail ERP Tax Compliance, GSTR-1, GSTR-2B Reconciliation, GSTR-3B & E-Invoicing') }}</p>
        </div>
        <div>
            <a href="{{ route('shop.reports.gstr1Export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-success fw-bold me-2">
                <i class="fa-solid fa-download me-1"></i> {{ __('Export GSTR-1 JSON (GST Portal)') }}
            </a>
            <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#gstr2bModal">
                <i class="fa-solid fa-arrows-rotate me-1"></i> {{ __('GSTR-2B Vendor Recon') }}
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.gstDashboard') }}" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-bold small mb-0">{{ __('From Date:') }}</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-auto">
                    <label class="fw-bold small mb-0">{{ __('To Date:') }}</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-secondary fw-bold px-3">{{ __('Filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- GSTR-3B Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Outward Taxable Value') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($gstr3bData['outward_supplies']['taxable_value'], 2) }}</h3>
                    <div class="small">{{ __('Total Outward Sales') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Outward Tax Liability') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($gstr3bData['outward_supplies']['cgst'] + $gstr3bData['outward_supplies']['sgst'] + $gstr3bData['outward_supplies']['igst'], 2) }}</h3>
                    <div class="small">CGST: ₹{{ number_format($gstr3bData['outward_supplies']['cgst'], 2) }} | SGST: ₹{{ number_format($gstr3bData['outward_supplies']['sgst'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3">
                    <div class="small text-white-50 text-uppercase fw-bold">{{ __('Eligible Input Tax Credit (ITC)') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($gstr3bData['eligible_itc']['cgst'] + $gstr3bData['eligible_itc']['sgst'] + $gstr3bData['eligible_itc']['igst'], 2) }}</h3>
                    <div class="small">{{ __('Purchase Tax Credit Available') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3">
                    <div class="small text-uppercase fw-bold">{{ __('Net Cash Tax Payable') }}</div>
                    <h3 class="fw-bold my-1">₹{{ number_format($gstr3bData['net_cash_payable']['total_cash_payable'], 2) }}</h3>
                    <div class="small">{{ __('Statutory GSTR-3B Tax Due') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statutory Tables Tabs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light p-2">
            <ul class="nav nav-tabs card-header-tabs" id="gstTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold text-uppercase small" id="gstr1-tab" data-bs-toggle="tab" data-bs-target="#gstr1-pane" type="button"><i class="fa-solid fa-list-check me-1"></i> {{ __('GSTR-1 Outward Tables') }}</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold text-uppercase small" id="hsn-tab" data-bs-toggle="tab" data-bs-target="#hsn-pane" type="button"><i class="fa-solid fa-boxes-stacked me-1"></i> {{ __('Table 12: HSN Summary') }}</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold text-uppercase small" id="valuation-tab" data-bs-toggle="tab" data-bs-target="#valuation-pane" type="button"><i class="fa-solid fa-calculator me-1"></i> {{ __('Stock Valuation (Weighted Average)') }}</button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3">
            <div class="tab-content" id="gstTabsContent">
                <!-- GSTR-1 Pane -->
                <div class="tab-pane fade show active" id="gstr1-pane" role="tabpanel">
                    <h6 class="fw-bold mb-2">{{ __('Table 7: B2C Small (POS Counter Sales)') }}</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('Place of Supply') }}</th>
                                    <th>{{ __('Supply Type') }}</th>
                                    <th class="text-end">{{ __('Taxable Value') }}</th>
                                    <th class="text-end">{{ __('CGST') }}</th>
                                    <th class="text-end">{{ __('SGST') }}</th>
                                    <th class="text-end">{{ __('IGST') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gstr1Data['b2cs'] as $b2c)
                                <tr>
                                    <td>State Code {{ $b2c['place_of_supply'] }}</td>
                                    <td>{{ $b2c['type'] }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($b2c['taxable_value'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($b2c['cgst'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($b2c['sgst'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($b2c['igst'], 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted">{{ __('No B2C small sales found for selected period') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- HSN Summary Pane -->
                <div class="tab-pane fade" id="hsn-pane" role="tabpanel">
                    <h6 class="fw-bold mb-2">{{ __('Table 12: HSN/SAC Code Outward Summary') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('HSN Code') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('UQC') }}</th>
                                    <th class="text-end">{{ __('Total Quantity') }}</th>
                                    <th class="text-end">{{ __('Taxable Value') }}</th>
                                    <th class="text-end">{{ __('CGST') }}</th>
                                    <th class="text-end">{{ __('SGST') }}</th>
                                    <th class="text-end">{{ __('IGST') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gstr1Data['hsn'] as $hsn)
                                <tr>
                                    <td class="fw-bold">{{ $hsn['hsn_code'] }}</td>
                                    <td>{{ $hsn['description'] }}</td>
                                    <td>{{ $hsn['uqc'] }}</td>
                                    <td class="text-end font-monospace">{{ $hsn['total_quantity'] }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($hsn['taxable_value'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($hsn['cgst'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($hsn['sgst'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($hsn['igst'], 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted">{{ __('No HSN records found for selected period') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Stock Valuation Pane -->
                <div class="tab-pane fade" id="valuation-pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">{{ __('Inventory Asset Valuation (Weighted Average Costing)') }}</h6>
                        <span class="badge bg-secondary font-monospace">Total Valuation: ₹{{ number_format($valuationData['total_inventory_asset_value'], 2) }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('Product Name') }}</th>
                                    <th class="text-end">{{ __('Stock Qty') }}</th>
                                    <th class="text-end">{{ __('Weighted Avg Cost') }}</th>
                                    <th class="text-end">{{ __('Total Asset Value') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($valuationData['products'] as $prodVal)
                                <tr>
                                    <td class="fw-bold">{{ $prodVal['name'] }}</td>
                                    <td class="text-end font-monospace">{{ $prodVal['stock_quantity'] }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($prodVal['weighted_avg_cost'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($prodVal['asset_value'], 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ __('No stock inventory records found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GSTR-2B Recon Modal -->
<div class="modal fade" id="gstr2bModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">{{ __('GSTR-2B Vendor Input Tax Credit Reconciliation') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="gstr2bForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Upload GSTR-2B Portal JSON File:') }}</label>
                        <input type="file" name="gstr2b_json" class="form-control" accept=".json">
                        <div class="form-text small">{{ __('Upload monthly GSTR-2B JSON downloaded from GST portal, or leave empty to auto-fetch via GSP API.') }}</div>
                    </div>
                    <button type="button" class="btn btn-primary fw-bold" onclick="runGstr2bRecon()"><i class="fa-solid fa-calculator me-1"></i> {{ __('Run 4-Way ITC Matching') }}</button>
                </form>
                <div id="reconResult" class="mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
function runGstr2bRecon() {
    let formData = new FormData(document.getElementById('gstr2bForm'));
    fetch("{{ route('shop.reports.gstr2bReconcile') }}", {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(data => {
        if(data.success) {
            let res = data.reconciliation.summary;
            let html = `<div class="alert alert-info">
                <h6><strong>Reconciliation Summary:</strong></h6>
                <ul>
                    <li>Matched: ${res.total_matched}</li>
                    <li>Mismatched: ${res.total_mismatched}</li>
                    <li>Missing in Books: ${res.total_missing_in_books}</li>
                    <li>Missing in Portal: ${res.total_missing_in_portal}</li>
                </ul>
            </div>`;
            document.getElementById('reconResult').innerHTML = html;
            document.getElementById('reconResult').style.display = 'block';
        }
    });
}
</script>
@endsection
