@extends('layouts.app')
@section('header-title', __('Vendor-wise Purchase & Sale'))
@section('content')
@include('shop.opening-stock._report-style')

@php
    $money = fn ($v, $d = 2) => '₹ ' . number_format((float) $v, $d);
    // Sort links keep the year filter, so changing the order of the table never
    // silently widens it back to every year.
    $sortUrl = function ($key) use ($sort, $dir, $fy) {
        $next = ($sort === $key && $dir === 'desc') ? 'asc' : 'desc';
        return route('shop.openingStock.vendors', array_filter(['fy' => $fy, 'sort' => $key, 'dir' => $next]));
    };
    $caret = fn ($key) => $sort === $key ? ($dir === 'desc' ? ' ▾' : ' ▴') : '';
    $barClass = fn ($pct) => $pct >= 75 ? 'ok' : ($pct >= 40 ? '' : ($pct >= 15 ? 'warn' : 'low'));
@endphp

<div class="d-flex align-items-center flex-wrap gap-2 justify-content-between px-3">
    <div>
        <h4 class="mb-0">{{ __('Vendor-wise Purchase & Sale') }}</h4>
        <div class="text-muted small">
            {{ __('What came in from each supplier across ten years, and how much of it has gone out.') }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('shop.openingStock.vendorsExport', array_filter(['fy' => $fy])) }}" class="btn btn-sm btn-outline-success">
            <i class="fa-solid fa-file-csv me-1"></i>{{ __('Export CSV') }}
        </a>
        <a href="{{ route('shop.openingStock.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Back to register') }}
        </a>
    </div>
</div>

<div class="container-fluid mt-3">

    {{-- Headline. These are the same rows the register counts, grouped by party. --}}
    <div class="row g-2 mb-3">
        @php
            $kpis = [
                ['label' => __('Suppliers'),        'value' => number_format($totals['vendors']),        'sub' => __('parties with opening stock')],
                ['label' => __('Units Purchased'),  'value' => number_format($totals['units']),          'sub' => number_format($totals['line_count']) . ' ' . __('lines') . ' · ' . number_format($totals['challans']) . ' ' . __('challans')],
                ['label' => __('Purchase Cost'),    'value' => $money($totals['cost_value']),            'sub' => __('net rate per barcode')],
                ['label' => __('Units Sold'),       'value' => number_format($totals['sold_units']),     'sub' => $money($totals['sold_cost']) . ' ' . __('at cost')],
                ['label' => __('Sell-through'),     'value' => number_format($totals['sell_through'], 1) . '%', 'sub' => __('of everything purchased')],
                ['label' => __('Still On Hand'),    'value' => number_format($totals['available_units']),'sub' => $money($totals['onhand_cost']) . ' ' . __('at cost')],
                ['label' => __('Retail Value'),     'value' => $money($totals['mrp_value']),             'sub' => __('purchased, at MRP')],
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

    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <form method="GET" class="col-12 col-md-auto d-flex gap-2 align-items-end">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="dir" value="{{ $dir }}">
                    <div>
                        <label class="form-label small mb-1">{{ __('Inward financial year') }}</label>
                        <select name="fy" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">{{ __('All years') }}</option>
                            @foreach($financialYears as $y)
                                <option value="{{ $y }}" {{ $fy === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($fy !== '')
                        <a href="{{ route('shop.openingStock.vendors') }}" class="btn btn-sm btn-outline-secondary">{{ __('All years') }}</a>
                    @endif
                </form>
                <div class="col-12 col-md">
                    <label class="form-label small mb-1">{{ __('Find a supplier') }}</label>
                    <input type="search" id="vendorFilter" class="form-control form-control-sm"
                           placeholder="{{ __('Type part of a supplier name or GSTIN — filters the list as you type') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <span class="text-muted small" id="vendorCount">{{ number_format($totals['vendors']) }} {{ __('suppliers shown') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive osr-wrap">
                <table class="table align-middle osr-table" id="vendorTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:34px">{{ __('#') }}</th>
                            <th class="{{ $sort === 'name' ? 'sorted' : '' }}"><a href="{{ $sortUrl('name') }}">{{ __('Supplier') }}{{ $caret('name') }}</a></th>
                            <th class="text-center {{ $sort === 'challans' ? 'sorted' : '' }}"><a href="{{ $sortUrl('challans') }}">{{ __('Documents') }}{{ $caret('challans') }}</a></th>
                            <th class="text-end {{ $sort === 'units' ? 'sorted' : '' }}"><a href="{{ $sortUrl('units') }}">{{ __('Purchased') }}{{ $caret('units') }}</a></th>
                            <th class="text-end {{ $sort === 'cost_value' ? 'sorted' : '' }}"><a href="{{ $sortUrl('cost_value') }}">{{ __('Purchase Cost') }}{{ $caret('cost_value') }}</a></th>
                            <th class="text-end {{ $sort === 'sold_units' ? 'sorted' : '' }}"><a href="{{ $sortUrl('sold_units') }}">{{ __('Sold') }}{{ $caret('sold_units') }}</a></th>
                            <th style="min-width:118px" class="{{ $sort === 'sell_through' ? 'sorted' : '' }}"><a href="{{ $sortUrl('sell_through') }}">{{ __('Sell-through') }}{{ $caret('sell_through') }}</a></th>
                            <th class="text-end {{ $sort === 'available_units' ? 'sorted' : '' }}"><a href="{{ $sortUrl('available_units') }}">{{ __('On Hand') }}{{ $caret('available_units') }}</a></th>
                            <th class="text-end {{ $sort === 'realised_net' ? 'sorted' : '' }}">
                                <a href="{{ $sortUrl('realised_net') }}" title="{{ __('Only sales replayed into orders (FY 2025-26) can be traced back to a supplier.') }}">{{ __('Realised') }}{{ $caret('realised_net') }}</a>
                            </th>
                            <th>{{ __('Period') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $key => $r)
                        <tr class="vendor-row" data-search="{{ mb_strtolower($r['name'] . ' ' . $r['gstin']) }}">
                            <td class="text-center text-muted osr-num">{{ $key + 1 }}</td>

                            <td data-label="{{ __('Supplier') }}" class="osr-name">
                                <div class="fw-semibold">
                                    <a href="{{ route('shop.openingStock.vendor', $r['party_id']) }}" class="text-decoration-none">{{ $r['name'] }}</a>
                                </div>
                                <div class="osr-sub">
                                    <span class="osr-code">{{ $r['gstin'] ?: __('no GSTIN') }}</span>
                                    @if($r['mobile']) &middot; {{ $r['mobile'] }} @endif
                                </div>
                            </td>

                            <td data-label="{{ __('Documents') }}" class="text-center osr-num">
                                <div class="fw-semibold">{{ number_format($r['challans']) }}</div>
                                <div class="osr-sub">
                                    {{ number_format($r['line_count']) }} {{ __('lines') }} &middot;
                                    {{ number_format($r['products']) }} {{ __('items') }}
                                </div>
                            </td>

                            <td data-label="{{ __('Purchased') }}" class="text-end osr-num">
                                <div class="fw-semibold">{{ number_format($r['units']) }}</div>
                                <div class="osr-sub">{{ __('units') }}</div>
                            </td>

                            <td data-label="{{ __('Purchase Cost') }}" class="text-end osr-num">
                                <div class="fw-semibold">{{ number_format($r['cost_value'], 2) }}</div>
                                <div class="osr-sub">
                                    {{ __('MRP') }} {{ number_format($r['mrp_value'], 0) }}
                                    @if($r['gst_value'] > 0) &middot; {{ __('GST') }} {{ number_format($r['gst_value'], 0) }} @endif
                                </div>
                            </td>

                            <td data-label="{{ __('Sold') }}" class="text-end osr-num">
                                <div class="fw-semibold text-success">{{ number_format($r['sold_units']) }}</div>
                                <div class="osr-sub">{{ number_format($r['sold_cost'], 0) }} {{ __('at cost') }}</div>
                            </td>

                            <td data-label="{{ __('Sell-through') }}">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="osr-num fw-semibold" style="min-width:42px">{{ number_format($r['sell_through'], 1) }}%</span>
                                    <span class="osr-bar flex-grow-1 {{ $barClass($r['sell_through']) }}" style="min-width:56px">
                                        <span style="width: {{ min(100, $r['sell_through']) }}%"></span>
                                    </span>
                                </div>
                            </td>

                            <td data-label="{{ __('On Hand') }}" class="text-end osr-num">
                                <div class="fw-semibold">{{ number_format($r['available_units']) }}</div>
                                <div class="osr-sub">{{ number_format($r['onhand_cost'], 0) }} {{ __('at cost') }}</div>
                            </td>

                            <td data-label="{{ __('Realised') }}" class="text-end osr-num">
                                @if($r['traced_lines'] > 0)
                                    <div class="fw-semibold">{{ number_format($r['realised_net'], 0) }}</div>
                                    <div class="osr-sub">
                                        {{ number_format($r['traced_units']) }} {{ __('traced') }}
                                        @if($r['returned_units'] > 0)
                                            &middot; <span class="text-danger">{{ number_format($r['returned_units']) }} {{ __('ret') }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td data-label="{{ __('Period') }}">
                                <div class="osr-sub">
                                    {{ $r['first_date'] ? \Illuminate\Support\Carbon::parse($r['first_date'])->format('M Y') : '—' }}
                                    →
                                    {{ $r['last_date'] ? \Illuminate\Support\Carbon::parse($r['last_date'])->format('M Y') : '—' }}
                                </div>
                                @if($r['line_count'] > 0 && $r['linked_lines'] < $r['line_count'])
                                    <div class="osr-sub text-warning-emphasis">
                                        {{ number_format($r['line_count'] - $r['linked_lines']) }} {{ __('lines snapshot only') }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($rows->isEmpty())
                        <tr><td colspan="10" class="text-center text-muted py-4">{{ __('No opening stock found for this year.') }}</td></tr>
                    @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td data-label="{{ __('Total') }}">{{ __('Total') }} — {{ number_format($totals['vendors']) }} {{ __('suppliers') }}</td>
                            <td data-label="{{ __('Documents') }}" class="text-center osr-num">{{ number_format($totals['challans']) }}</td>
                            <td data-label="{{ __('Purchased') }}" class="text-end osr-num">{{ number_format($totals['units']) }}</td>
                            <td data-label="{{ __('Purchase Cost') }}" class="text-end osr-num">{{ number_format($totals['cost_value'], 2) }}</td>
                            <td data-label="{{ __('Sold') }}" class="text-end osr-num">{{ number_format($totals['sold_units']) }}</td>
                            <td data-label="{{ __('Sell-through') }}" class="osr-num">{{ number_format($totals['sell_through'], 1) }}%</td>
                            <td data-label="{{ __('On Hand') }}" class="text-end osr-num">{{ number_format($totals['available_units']) }}</td>
                            <td data-label="{{ __('Realised') }}" class="text-end osr-num">{{ number_format($totals['realised_net'], 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <p class="text-muted small mb-0 mt-2">
                <i class="fa-solid fa-circle-info me-1"></i>
                {{ __('Purchased, sold and on-hand cover every year and every barcode. Realised covers only the sales replayed into orders — FY 2025-26 — so it is a window onto the selling, not the whole of it.') }}
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filters the rendered list rather than round-tripping: the whole supplier
    // master is on the page already, and typing should feel immediate.
    (function () {
        const input = document.getElementById('vendorFilter');
        const rows  = [...document.querySelectorAll('#vendorTable tbody tr.vendor-row')];
        const count = document.getElementById('vendorCount');
        if (!input) return;

        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let shown = 0;
            rows.forEach(r => {
                const hit = q === '' || (r.dataset.search || '').includes(q);
                r.style.display = hit ? '' : 'none';
                if (hit) shown++;
            });
            count.textContent = shown.toLocaleString() + ' {{ __('suppliers shown') }}';
        });
    })();
</script>
@endpush
