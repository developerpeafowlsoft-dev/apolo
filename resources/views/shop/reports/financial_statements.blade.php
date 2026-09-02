@extends('layouts.app')

@section('title', __('Financial Statements (Trial Balance, P&L, Balance Sheet)'))

@section('content')
<div class="content-body p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-chart-line me-2"></i>{{ __('Financial Statements & Trial Balance') }}</h4>
            <p class="text-muted mb-0 small">{{ __('Statutory Trial Balance, Profit & Loss Statement, and Balance Sheet') }}</p>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-3 shadow-sm border-0">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('shop.reports.financialStatements') }}" class="row g-2 align-items-center">
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('From Date:') }}</label></div>
                <div class="col-auto"><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}"></div>
                <div class="col-auto"><label class="fw-bold small mb-0">{{ __('To Date / As of:') }}</label></div>
                <div class="col-auto"><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}"></div>
                <div class="col-auto"><button type="submit" class="btn btn-sm btn-secondary fw-bold px-3">{{ __('Filter') }}</button></div>
            </form>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light p-2">
            <ul class="nav nav-tabs card-header-tabs" id="finTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold text-uppercase small" id="tb-tab" data-bs-toggle="tab" data-bs-target="#tb-pane" type="button"><i class="fa-solid fa-scale-balanced me-1"></i> {{ __('Trial Balance') }}</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold text-uppercase small" id="pnl-tab" data-bs-toggle="tab" data-bs-target="#pnl-pane" type="button"><i class="fa-solid fa-file-invoice-dollar me-1"></i> {{ __('Profit & Loss (P&L)') }}</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold text-uppercase small" id="bs-tab" data-bs-toggle="tab" data-bs-target="#bs-pane" type="button"><i class="fa-solid fa-vault me-1"></i> {{ __('Balance Sheet') }}</button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3">
            <div class="tab-content" id="finTabsContent">
                <!-- Trial Balance Pane -->
                <div class="tab-pane fade show active" id="tb-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('Account Name') }}</th>
                                    <th>{{ __('Group') }}</th>
                                    <th class="text-end">{{ __('Opening Debit') }}</th>
                                    <th class="text-end">{{ __('Opening Credit') }}</th>
                                    <th class="text-end">{{ __('Period Debit') }}</th>
                                    <th class="text-end">{{ __('Period Credit') }}</th>
                                    <th class="text-end">{{ __('Closing Debit') }}</th>
                                    <th class="text-end">{{ __('Closing Credit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trialBalance['rows'] as $r)
                                <tr>
                                    <td class="fw-bold">{{ $r['account_name'] }}</td>
                                    <td>{{ $r['account_group'] }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($r['opening_debit'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($r['opening_credit'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($r['period_debit'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($r['period_credit'], 2) }}</td>
                                    <td class="text-end font-monospace fw-bold text-success">₹{{ number_format($r['closing_debit'], 2) }}</td>
                                    <td class="text-end font-monospace fw-bold text-danger">₹{{ number_format($r['closing_credit'], 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted">{{ __('No account ledger entries found') }}</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="2">{{ __('Total Trial Balance') }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($trialBalance['totals']['opening_debit'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($trialBalance['totals']['opening_credit'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($trialBalance['totals']['period_debit'], 2) }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format($trialBalance['totals']['period_credit'], 2) }}</td>
                                    <td class="text-end font-monospace text-success">₹{{ number_format($trialBalance['totals']['closing_debit'], 2) }}</td>
                                    <td class="text-end font-monospace text-danger">₹{{ number_format($trialBalance['totals']['closing_credit'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- P&L Pane -->
                <div class="tab-pane fade" id="pnl-pane" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary">{{ __('Revenue & Gross Profit') }}</h6>
                                    <div class="d-flex justify-content-between my-2"><span>Gross Sales Revenue:</span><span class="font-monospace fw-bold">₹{{ number_format($pnl['revenue']['gross_sales'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2"><span>Less: Sales Returns:</span><span class="font-monospace text-danger">₹{{ number_format($pnl['revenue']['sales_returns'], 2) }}</span></div>
                                    <hr>
                                    <div class="d-flex justify-content-between my-2"><span class="fw-bold">Net Sales Revenue:</span><span class="font-monospace fw-bold">₹{{ number_format($pnl['revenue']['net_sales'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2"><span>Less: Cost of Goods Sold (COGS):</span><span class="font-monospace text-danger">₹{{ number_format($pnl['cogs']['total_cogs'], 2) }}</span></div>
                                    <hr>
                                    <div class="d-flex justify-content-between my-2"><span class="fw-bold h6 mb-0 text-success">Gross Profit:</span><span class="font-monospace fw-bold h6 mb-0 text-success">₹{{ number_format($pnl['gross_profit'], 2) }}</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary">{{ __('Operating Expenses & Net Profit') }}</h6>
                                    <div class="d-flex justify-content-between my-2"><span>Operating Expenses:</span><span class="font-monospace text-danger">₹{{ number_format($pnl['expenses']['operating_expenses'], 2) }}</span></div>
                                    <hr>
                                    <div class="d-flex justify-content-between my-2"><span class="fw-bold h5 mb-0 text-primary">Net Profit / (Loss):</span><span class="font-monospace fw-bold h5 mb-0 text-primary">₹{{ number_format($pnl['net_profit'], 2) }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Sheet Pane -->
                <div class="tab-pane fade" id="bs-pane" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="fw-bold text-success border-bottom pb-2 mb-3"><i class="fa-solid fa-vault me-1"></i> {{ __('Assets') }}</h6>
                                    <div class="d-flex justify-content-between my-2"><span>Closing Inventory Asset (WAC):</span><span class="font-monospace fw-bold">₹{{ number_format($balanceSheet['assets']['inventory_asset_value'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2"><span>Cash & Bank Balances:</span><span class="font-monospace">₹{{ number_format($balanceSheet['assets']['cash_and_bank_balance'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2"><span>Trade Receivables (Debtors):</span><span class="font-monospace">₹{{ number_format($balanceSheet['assets']['trade_receivables'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2"><span>GST Input Credit (ITC Asset):</span><span class="font-monospace text-primary">₹{{ number_format($balanceSheet['assets']['gst_input_credit'], 2) }}</span></div>
                                    <hr>
                                    <div class="d-flex justify-content-between my-2"><span class="fw-bold h6 mb-0 text-success">{{ __('Total Assets:') }}</span><span class="font-monospace fw-bold h5 mb-0 text-success">₹{{ number_format($balanceSheet['assets']['total_assets'], 2) }}</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="fw-bold text-danger border-bottom pb-2 mb-3"><i class="fa-solid fa-scale-unbalanced me-1"></i> {{ __('Liabilities & Equity') }}</h6>
                                    <div class="d-flex justify-content-between my-2"><span>Trade Payables (Creditors):</span><span class="font-monospace fw-bold">₹{{ number_format($balanceSheet['liabilities_and_equity']['trade_payables'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2"><span>Output GST Liability:</span><span class="font-monospace text-danger">₹{{ number_format($balanceSheet['liabilities_and_equity']['output_gst_liability'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2 text-muted small"><span>{{ __('Total Liabilities:') }}</span><span class="font-monospace">₹{{ number_format($balanceSheet['liabilities_and_equity']['total_liabilities'], 2) }}</span></div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between my-2"><span>Opening Capital / Initial Stock Equity:</span><span class="font-monospace">₹{{ number_format($balanceSheet['liabilities_and_equity']['opening_capital_equity'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2"><span>Reserves & Surplus (Current P&L):</span><span class="font-monospace {{ $balanceSheet['liabilities_and_equity']['reserves_and_surplus_net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">₹{{ number_format($balanceSheet['liabilities_and_equity']['reserves_and_surplus_net_profit'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between my-2 text-muted small"><span>{{ __('Total Equity & Capital:') }}</span><span class="font-monospace">₹{{ number_format($balanceSheet['liabilities_and_equity']['total_equity'], 2) }}</span></div>
                                    <hr>
                                    <div class="d-flex justify-content-between my-2"><span class="fw-bold h6 mb-0 text-danger">{{ __('Total Liabilities & Equity:') }}</span><span class="font-monospace fw-bold h5 mb-0 text-danger">₹{{ number_format($balanceSheet['liabilities_and_equity']['total_liabilities_and_equity'], 2) }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
