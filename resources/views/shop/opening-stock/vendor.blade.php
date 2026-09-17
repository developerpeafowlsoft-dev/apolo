@extends('layouts.app')
@section('header-title', $party->accountName ?? __('Supplier'))
@section('content')
@include('shop.opening-stock._report-style')

@php
    $barClass = fn ($pct) => $pct >= 75 ? 'ok' : ($pct >= 40 ? '' : ($pct >= 15 ? 'warn' : 'low'));
    $pct = fn ($n, $d) => $d > 0 ? round($n / $d * 100, 1) : 0;
@endphp

<div class="d-flex align-items-start flex-wrap gap-2 justify-content-between px-3">
    <div>
        <h4 class="mb-0">{{ $party->accountName ?? __('(unnamed supplier)') }}</h4>
        <div class="text-muted small">
            <span class="osr-code">{{ $party->tax_info_gst_no ?: __('no GSTIN on record') }}</span>
            @if($party->cont_info_mobile1) &middot; {{ $party->cont_info_mobile1 }} @endif
            @if($party->cont_info_email) &middot; {{ $party->cont_info_email }} @endif
            @if(!($party->is_active ?? 1)) &middot; <span class="badge bg-secondary">{{ __('inactive') }}</span> @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('shop.openingStock.index', ['search' => $party->accountName]) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-barcode me-1"></i>{{ __('Open in register') }}
        </a>
        <a href="{{ route('shop.openingStock.vendors') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>{{ __('All suppliers') }}
        </a>
    </div>
</div>

<div class="container-fluid mt-3">

    <div class="row g-2 mb-3">
        @php
            $kpis = [
                ['label' => __('Units Purchased'), 'value' => number_format($summary['units']), 'sub' => number_format($summary['challans']) . ' ' . __('challans') . ' · ' . number_format($summary['line_count']) . ' ' . __('lines')],
                ['label' => __('Distinct Items'),  'value' => number_format($summary['products']), 'sub' => __('mapped products')],
                ['label' => __('Purchase Cost'),   'value' => '₹ ' . number_format($summary['cost_value'], 2), 'sub' => __('GST') . ' ₹ ' . number_format($summary['gst_value'], 2)],
                ['label' => __('Units Sold'),      'value' => number_format($summary['sold_units']), 'sub' => '₹ ' . number_format($summary['sold_cost'], 0) . ' ' . __('at cost')],
                ['label' => __('Sell-through'),    'value' => number_format($summary['sell_through'], 1) . '%', 'sub' => __('of units purchased')],
                ['label' => __('Still On Hand'),   'value' => number_format($summary['available_units']), 'sub' => '₹ ' . number_format($summary['onhand_cost'], 0) . ' ' . __('at cost')],
                ['label' => __('Retail Value'),    'value' => '₹ ' . number_format($summary['mrp_value'], 0), 'sub' => __('purchased, at MRP')],
            ];
        @endphp
        @foreach($kpis as $k)
            <div class="col-6 col-md-4 col-xl">
                <div class="osr-kpi">
                    <div class="k-label">{{ $k['label'] }}</div>
                    <div class="k-value">{{ $k['value'] }}</div>
                    <div class="k-sub">{{ $k['sub'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Year by year ---------------------------------------------------- --}}
    <div class="card mb-3">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-calendar-days me-1"></i>{{ __('Year by year') }}</h6>
            <span class="text-muted small">{{ __('Sold is shown against the year the stock came in — the sale files carry no usable date per barcode.') }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive osr-wrap">
                <table class="table align-middle osr-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Inward FY') }}</th>
                            <th class="text-center">{{ __('Challans') }}</th>
                            <th class="text-end">{{ __('Units In') }}</th>
                            <th class="text-end">{{ __('Cost') }}</th>
                            <th class="text-end">{{ __('MRP') }}</th>
                            <th class="text-end">{{ __('Sold') }}</th>
                            <th style="min-width:120px">{{ __('Sell-through') }}</th>
                            <th class="text-end">{{ __('On Hand') }}</th>
                            <th class="text-end">{{ __('On Hand Cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($byYear as $y)
                        @php $st = $pct($y->sold_units, $y->units); @endphp
                        <tr>
                            <td data-label="{{ __('Inward FY') }}" class="fw-semibold">{{ $y->fy ?: __('unknown') }}</td>
                            <td data-label="{{ __('Challans') }}" class="text-center osr-num">{{ number_format($yearChallans[$y->fy] ?? 0) }}</td>
                            <td data-label="{{ __('Units In') }}" class="text-end osr-num fw-semibold">{{ number_format($y->units) }}</td>
                            <td data-label="{{ __('Cost') }}" class="text-end osr-num">{{ number_format($y->cost_value, 2) }}</td>
                            <td data-label="{{ __('MRP') }}" class="text-end osr-num">{{ number_format($y->mrp_value, 0) }}</td>
                            <td data-label="{{ __('Sold') }}" class="text-end osr-num text-success fw-semibold">{{ number_format($y->sold_units) }}</td>
                            <td data-label="{{ __('Sell-through') }}">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="osr-num fw-semibold" style="min-width:42px">{{ number_format($st, 1) }}%</span>
                                    <span class="osr-bar flex-grow-1 {{ $barClass($st) }}" style="min-width:56px"><span style="width: {{ min(100, $st) }}%"></span></span>
                                </div>
                            </td>
                            <td data-label="{{ __('On Hand') }}" class="text-end osr-num">{{ number_format($y->available_units) }}</td>
                            <td data-label="{{ __('On Hand Cost') }}" class="text-end osr-num">{{ number_format($y->onhand_cost, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td data-label="{{ __('Total') }}">{{ __('Total') }}</td>
                            <td data-label="{{ __('Challans') }}" class="text-center osr-num">{{ number_format($summary['challans']) }}</td>
                            <td data-label="{{ __('Units In') }}" class="text-end osr-num">{{ number_format($summary['units']) }}</td>
                            <td data-label="{{ __('Cost') }}" class="text-end osr-num">{{ number_format($summary['cost_value'], 2) }}</td>
                            <td data-label="{{ __('MRP') }}" class="text-end osr-num">{{ number_format($summary['mrp_value'], 0) }}</td>
                            <td data-label="{{ __('Sold') }}" class="text-end osr-num">{{ number_format($summary['sold_units']) }}</td>
                            <td data-label="{{ __('Sell-through') }}" class="osr-num">{{ number_format($summary['sell_through'], 1) }}%</td>
                            <td data-label="{{ __('On Hand') }}" class="text-end osr-num">{{ number_format($summary['available_units']) }}</td>
                            <td data-label="{{ __('On Hand Cost') }}" class="text-end osr-num">{{ number_format($summary['onhand_cost'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Item by item ---------------------------------------------------- --}}
    <div class="card mb-3">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-shirt me-1"></i>{{ __('What was bought from this supplier') }}</h6>
            <form method="GET" class="d-flex gap-2">
                <input type="search" name="item" value="{{ $itemSearch }}" class="form-control form-control-sm"
                       style="min-width:220px" placeholder="{{ __('Find an item by name or code') }}">
                <button class="btn btn-sm btn-primary">{{ __('Find') }}</button>
                @if($itemSearch !== '')
                    <a href="{{ route('shop.openingStock.vendor', $party->id) }}" class="btn btn-sm btn-outline-secondary">{{ __('Clear') }}</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive osr-wrap">
                <table class="table align-middle osr-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:34px">{{ __('#') }}</th>
                            <th>{{ __('Item') }}</th>
                            <th class="text-end">{{ __('Units In') }}</th>
                            <th class="text-end">{{ __('Avg Cost') }}</th>
                            <th class="text-end">{{ __('Avg MRP') }}</th>
                            <th class="text-end">{{ __('Total Cost') }}</th>
                            <th class="text-end">{{ __('Sold') }}</th>
                            <th style="min-width:120px">{{ __('Sell-through') }}</th>
                            <th class="text-end">{{ __('On Hand') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $key => $it)
                        @php $st = $pct($it->sold_units, $it->units); @endphp
                        <tr>
                            <td class="text-center text-muted osr-num">{{ $items->firstItem() + $key }}</td>
                            <td data-label="{{ __('Item') }}" class="osr-name">
                                <div class="fw-semibold">{{ $it->product_name ?: __('(unmapped product)') }}</div>
                                <div class="osr-sub">
                                    <span class="osr-code">{{ $it->product_code ?: '—' }}</span>
                                    &middot; {{ __('HSN') }} {{ $it->hsn_code ?: '—' }}
                                </div>
                            </td>
                            <td data-label="{{ __('Units In') }}" class="text-end osr-num fw-semibold">{{ number_format($it->units) }}</td>
                            <td data-label="{{ __('Avg Cost') }}" class="text-end osr-num">{{ number_format($it->avg_cost, 2) }}</td>
                            <td data-label="{{ __('Avg MRP') }}" class="text-end osr-num">{{ number_format($it->avg_mrp, 2) }}</td>
                            <td data-label="{{ __('Total Cost') }}" class="text-end osr-num">{{ number_format($it->cost_value, 2) }}</td>
                            <td data-label="{{ __('Sold') }}" class="text-end osr-num text-success fw-semibold">{{ number_format($it->sold_units) }}</td>
                            <td data-label="{{ __('Sell-through') }}">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="osr-num fw-semibold" style="min-width:42px">{{ number_format($st, 1) }}%</span>
                                    <span class="osr-bar flex-grow-1 {{ $barClass($st) }}" style="min-width:56px"><span style="width: {{ min(100, $st) }}%"></span></span>
                                </div>
                            </td>
                            <td data-label="{{ __('On Hand') }}" class="text-end osr-num">
                                @if($it->available_units > 0)
                                    <span class="badge bg-success">{{ number_format($it->available_units) }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Sold out') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">{{ __('No items match.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $items->links() }}</div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Challans -------------------------------------------------------- --}}
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-file-invoice me-1"></i>{{ __('Inward challans') }}</h6>
                    <span class="text-muted small">
                        @if($challanTotal > $challans->count())
                            {{ __('newest :n of :t', ['n' => number_format($challans->count()), 't' => number_format($challanTotal)]) }}
                        @else
                            {{ number_format($challanTotal) }} {{ __('in total') }}
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive osr-wrap" style="max-height: 460px; overflow-y: auto;">
                        <table class="table align-middle osr-table mb-0">
                            <thead class="position-sticky top-0 bg-body">
                                <tr>
                                    <th>{{ __('Challan') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Lines') }}</th>
                                    <th class="text-end">{{ __('Units') }}</th>
                                    <th class="text-end">{{ __('Cost') }}</th>
                                    <th class="text-end">{{ __('GST') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($challans as $c)
                                <tr>
                                    <td data-label="{{ __('Challan') }}">
                                        @if($c->legacy_voucher_resolved)
                                            <div class="fw-semibold osr-code">{{ $c->inward_challan_no }}</div>
                                            <div class="osr-sub">{{ __('vch') }} {{ $c->inward_voucher_no }}</div>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ __('Snapshot') }}</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('Date') }}">
                                        <div>{{ $c->inward_date ? \Illuminate\Support\Carbon::parse($c->inward_date)->format('d M Y') : '—' }}</div>
                                        <div class="osr-sub">{{ $c->legacy_financial_year }}</div>
                                    </td>
                                    <td data-label="{{ __('Lines') }}" class="text-end osr-num">{{ number_format($c->line_count) }}</td>
                                    <td data-label="{{ __('Units') }}" class="text-end osr-num">{{ number_format($c->units) }}</td>
                                    <td data-label="{{ __('Cost') }}" class="text-end osr-num">{{ number_format($c->cost_value, 2) }}</td>
                                    <td data-label="{{ __('GST') }}" class="text-end osr-num">{{ number_format($c->gst_value, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No challans.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales that can be traced back to this supplier ------------------ --}}
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-receipt me-1"></i>{{ __('Sold on a bill') }}</h6>
                    <div class="text-muted" style="font-size:11px">
                        {{ __('Sold units matched to an order line. Only FY 2025-26 was replayed into orders, so this is a sample of the selling, not all of it.') }}
                    </div>
                </div>
                <div class="card-body">
                    @if($summary['traced_lines'] > 0)
                        <div class="d-flex flex-wrap gap-3 mb-2 small">
                            <span><span class="text-muted">{{ __('Traced units') }}</span> <strong class="osr-num">{{ number_format($summary['traced_units']) }}</strong></span>
                            <span><span class="text-muted">{{ __('Net sales') }}</span> <strong class="osr-num">₹ {{ number_format($summary['realised_net'], 2) }}</strong></span>
                            <span><span class="text-muted">{{ __('Tax') }}</span> <strong class="osr-num">₹ {{ number_format($summary['realised_tax'], 2) }}</strong></span>
                            @if($summary['returned_units'] > 0)
                                <span class="text-danger">{{ number_format($summary['returned_units']) }} {{ __('returned') }}</span>
                            @endif
                        </div>
                    @endif
                    <div class="table-responsive osr-wrap" style="max-height: 420px; overflow-y: auto;">
                        <table class="table align-middle osr-table mb-0">
                            <thead class="position-sticky top-0 bg-body">
                                <tr>
                                    <th>{{ __('Barcode') }}</th>
                                    <th>{{ __('Bill') }}</th>
                                    <th class="text-end">{{ __('Qty') }}</th>
                                    <th class="text-end">{{ __('Net') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($tracedSales as $s)
                                <tr>
                                    <td data-label="{{ __('Barcode') }}">
                                        <div class="osr-code fw-semibold">{{ $s->barcode_number }}</div>
                                        <div class="osr-sub">{{ \Illuminate\Support\Str::limit($s->product_name, 26) ?: '—' }}</div>
                                    </td>
                                    <td data-label="{{ __('Bill') }}">
                                        <div class="osr-code">{{ $s->order_code }}</div>
                                        <div class="osr-sub">{{ \Illuminate\Support\Carbon::parse($s->created_at)->format('d M Y') }}</div>
                                    </td>
                                    <td data-label="{{ __('Qty') }}" class="text-end osr-num {{ $s->quantity < 0 ? 'text-danger fw-semibold' : '' }}">{{ $s->quantity }}</td>
                                    <td data-label="{{ __('Net') }}" class="text-end osr-num">
                                        <div>{{ number_format($s->price * $s->quantity, 2) }}</div>
                                        <div class="osr-sub">{{ __('MRP') }} {{ number_format($s->mrp, 0) }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">
                                    {{ __('None of this supplier\'s barcodes appear on a replayed bill.') }}
                                </td></tr>
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
