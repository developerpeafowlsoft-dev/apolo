@extends('layouts.app')
@section('header-title', __('Opening Stock'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <div>
            <h4 class="mb-0">{{ __('Opening Stock') }}</h4>
            <span class="text-muted small">
                {{ __('Legacy stock imported with its original printed barcodes. These items bypass the Purchase stage.') }}
            </span>
        </div>
        <a href="{{ route('shop.openingStock.vendors') }}" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-truck-field me-1"></i>{{ __('Vendor-wise purchase & sale') }}
        </a>
    </div>

    <div class="container-fluid mt-3">

        {{-- Summary --}}
        <div class="row g-3 mb-3">
            @php
                // Each card carries the metric key its detail page is keyed by, so
                // a figure and the page that explains it cannot drift apart.
                $cards = [
                    ['key' => 'units',     'label' => __('Total Units'),      'value' => number_format($totals->units), 'sub' => __('barcodes imported')],
                    ['key' => 'available', 'label' => __('Available'),        'value' => number_format($barcodeStats->available ?? 0), 'sub' => __('sellable at POS')],
                    ['key' => 'sold',      'label' => __('Sold'),             'value' => number_format($barcodeStats->sold ?? 0), 'sub' => __('consumed by invoices')],
                    ['key' => 'lines',     'label' => __('Stock Lines'),      'value' => number_format($totals->line_count), 'sub' => __('distinct SKU / price rows')],
                    ['key' => 'cost',      'label' => __('Value at Cost'),    'value' => '₹ ' . number_format($totals->value_cost, 2), 'sub' => __('net purchase cost')],
                    ['key' => 'mrp',       'label' => __('Value at MRP'),     'value' => '₹ ' . number_format($totals->value_mrp, 2), 'sub' => __('retail value')],
                    ['key' => 'linked',    'label' => __('Challan Linked'),   'value' => number_format($linkage->linked_units ?? 0), 'sub' => __('units with a real inward voucher')],
                    ['key' => 'snapshot',  'label' => __('Snapshot Only'),    'value' => number_format($linkage->unlinked_units ?? 0), 'sub' => __('no challan in the exports')],
                    ['key' => 'gst',       'label' => __('GST on Purchase'),  'value' => '₹ ' . number_format($linkage->gst_total ?? 0, 2), 'sub' => __('SGST + CGST + IGST from challans')],
                ];
            @endphp
            @foreach($cards as $c)
                <div class="col-6 col-sm-4 col-xl-2">
                    <a href="{{ route('shop.openingStock.insight', $c['key']) }}" class="card h-100 os-card text-decoration-none text-body">
                        <div class="card-body py-3">
                            <div class="text-muted small text-uppercase">{{ $c['label'] }}</div>
                            <div class="fw-bold fs-5">{{ $c['value'] }}</div>
                            <div class="text-muted" style="font-size:11px">{{ $c['sub'] }}</div>
                            <div class="os-card-go">{{ __('open detail') }} &rarr;</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="card mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('shop.openingStock.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label small mb-1">{{ __('Search') }}</label>
                        <input type="text" name="search" value="{{ $search }}" class="form-control"
                               placeholder="{{ __('Scan a barcode, or type product / party / voucher no') }}" autofocus>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small mb-1">{{ __('Online Status') }}</label>
                        <select name="online" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="1" {{ $online === '1' ? 'selected' : '' }}>{{ __('Published online') }}</option>
                            <option value="0" {{ $online === '0' ? 'selected' : '' }}>{{ __('Not online') }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small mb-1">{{ __('Challan') }}</label>
                        <select name="linked" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="1" {{ $linked === '1' ? 'selected' : '' }}>{{ __('Linked to challan') }}</option>
                            <option value="0" {{ $linked === '0' ? 'selected' : '' }}>{{ __('Snapshot only') }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small mb-1">{{ __('Inward FY') }}</label>
                        <select name="fy" class="form-select">
                            <option value="">{{ __('All years') }}</option>
                            @foreach($financialYears as $y)
                                <option value="{{ $y }}" {{ $fy === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small mb-1">{{ __('Stock') }}</label>
                        <select name="stock" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="available" {{ $stock === 'available' ? 'selected' : '' }}>{{ __('In stock') }}</option>
                            <option value="sold" {{ $stock === 'sold' ? 'selected' : '' }}>{{ __('Fully sold') }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-xl-auto d-flex gap-2 align-items-end">
                        <button class="btn btn-primary flex-grow-1">{{ __('Filter') }}</button>
                        <a href="{{ route('shop.openingStock.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bulk actions --}}
        <div class="card mb-3 d-none" id="bulkBar">
            <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
                <span class="fw-semibold"><span id="bulkCount">0</span> {{ __('selected') }}</span>
                <button class="btn btn-success btn-sm" onclick="bulkToggle(true)">{{ __('Publish online') }}</button>
                <button class="btn btn-outline-danger btn-sm" onclick="bulkToggle(false)">{{ __('Remove from online') }}</button>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
<style>
                    /* The summary figures are doorways now, so they have to look
                       like one: the arrow only appears on hover, which keeps the
                       strip as quiet as it was when the cards were inert. */
                    .os-card { transition: box-shadow .12s, border-color .12s, transform .12s; }
                    .os-card:hover { border-color: var(--bs-primary, #0d6efd); box-shadow: 0 2px 12px rgba(13,110,253,.15); transform: translateY(-1px); }
                    .os-card-go { font-size: 10px; font-weight: 600; color: var(--bs-primary, #0d6efd); opacity: 0; transition: opacity .12s; margin-top: .15rem; }
                    .os-card:hover .os-card-go { opacity: 1; }
                    @media (hover: none) { .os-card-go { opacity: .75; } }
                    /* Half a phone screen is about 150px of card; "39,514,889.25"
                       at fs-5 does not fit and was breaking mid-number into
                       "39,514," / "889.25". Shrink it and keep it whole. */
                    @media (max-width: 575.98px) {
                        .os-card .card-body { padding: .6rem .7rem; }
                        .os-card .fs-5 { font-size: .95rem !important; word-break: normal; overflow-wrap: normal; }
                        .os-card .text-uppercase { font-size: 10px; }
                    }

                    /* The printed sticker numbers. A line can carry a dozen, so they
                       wrap inside a capped, scrollable cell rather than stretching the
                       row; a sold one is struck through so a part-sold line reads at
                       a glance without opening anything. */
                    .os-codes { max-width: 210px; }
                    .os-codes .os-bc {
                        display: inline-block;
                        font-family: ui-monospace, Menlo, monospace;
                        font-size: 10.5px; line-height: 1.5;
                        background: var(--bs-tertiary-bg, #f1f3f5);
                        border: 1px solid var(--bs-border-color-translucent, #e3e6ea);
                        border-radius: 3px;
                        padding: 0 4px; margin: 1px 2px 1px 0;
                        white-space: nowrap;
                    }
                    .os-codes .os-bc.sold {
                        text-decoration: line-through;
                        opacity: .55;
                        background: var(--bs-danger-bg-subtle, #f8d7da);
                        border-color: var(--bs-danger-border-subtle, #f1aeb5);
                    }
                    .os-table .os-sub { font-size: 10px; line-height: 1.3; }
                    /* Below lg the 28-column register cannot be read by scrolling
                       sideways, so each row becomes a labelled card. Done in CSS on
                       the one table rather than by rendering a second markup block,
                       which would duplicate every .rowCheck and break bulk select. */
                    /* 28 columns only fit a desktop if the cell box is tight. Default
                       Bootstrap padding pushes the register about 400px past a 1080p
                       screen, which is what forced the sideways scroll. */
                    @media (min-width: 992px) {
                        /* The shell lays the main column out with flex, and a flex item
                           defaults to min-width:auto - it will not shrink below its
                           content. So a 28-column table pushed app-main-inner, and with
                           it the whole page, wider than the viewport: the register ran
                           off the right edge and .table-responsive never got to scroll,
                           because it was never the thing being constrained.
                           min-width:0 lets the column shrink so the wrapper can clip.
                           Scoped to this view; the rule ships inside this page's style
                           block and loads nowhere else. */
                        .app-main-outer, .app-main-inner, .card, .card-body { min-width: 0; }
                        .os-wrap { max-width: 100%; }

                        .os-table { font-size: 12px; }
                        /* Cells stack a headline value over its supporting figures, so
                           thirteen columns carry what twenty-eight used to and the
                           register stops needing a sideways scroll. */
                        .os-table td.os-item { max-width: 210px; }
                        .os-table td.os-party { max-width: 160px; }
                        .os-table td.os-item .fw-semibold,
                        .os-table td.os-party > div:first-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                        .os-table thead th { padding: .4rem .25rem; font-size: 10.5px; letter-spacing: 0; }
                        .os-table tbody td { padding: .3rem .25rem; }
                        .os-table .badge { font-size: 9.5px; padding: .22em .45em; font-weight: 600; }
                        /* The two widest text columns get a ceiling and an ellipsis;
                           the full value stays reachable as a tooltip. */
                        .os-table td.os-product { max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
                        .os-table td.os-party { max-width: 116px; overflow: hidden; text-overflow: ellipsis; }
                        .os-table td.os-product > div, .os-table td.os-party { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                        .os-table .os-gstin { font-size: 10px; letter-spacing: -.2px; }
                        .os-table input[type=checkbox] { transform: scale(.85); }
                        .os-codes .os-bc-wrap { max-height: 78px; overflow-y: auto; }
                    }
                    @media (max-width: 991.98px) {
                        .os-table thead { display: none; }
                        .os-table, .os-table tbody, .os-table tr { display: block; width: 100%; }
                        .os-table tr {
                            border: 1px solid var(--bs-border-color, #dee2e6);
                            border-radius: .5rem;
                            margin-bottom: .75rem;
                            padding: .25rem .5rem;
                            background: var(--bs-body-bg, #fff);
                        }
                        /* Bootstrap's .text-nowrap carries !important, so the row's
                           nowrap has to be beaten with the same weapon or every
                           value is clipped instead of wrapping inside its card. */
                        .os-table tr, .os-table td { white-space: normal !important; word-break: break-word; }
                        /* Stacking replaces sideways scrolling; leaving the wrapper
                           scrollable lets the card grow wider than the screen. */
                        .os-wrap { overflow-x: visible !important; }
                        .os-table { min-width: 0 !important; }

                        /* Grid, not flex: a cell like Product holds a name and a code
                           as two elements, and flex would sit them beside each other
                           instead of stacking them under one label. */
                        .os-table td {
                            display: grid;
                            grid-template-columns: minmax(84px, 38%) 1fr;
                            gap: 0 .75rem;
                            align-items: start;
                            border: 0;
                            border-bottom: 1px solid var(--bs-border-color-translucent, #eee);
                            padding: .5rem .25rem;
                            text-align: right !important;
                        }
                        .os-table td:last-child { border-bottom: 0; }
                        .os-table td::before {
                            content: attr(data-label);
                            grid-column: 1; grid-row: 1;
                            font-weight: 600; font-size: 11px; letter-spacing: .04em;
                            text-transform: uppercase; color: var(--bs-secondary-color, #6c757d);
                            text-align: left;
                        }
                        .os-table td > * { grid-column: 2; justify-self: end; }

                        /* Cells with no label are structural (the row checkbox). */
                        .os-table td:not([data-label]) { grid-template-columns: 1fr; }
                        .os-table td:not([data-label])::before { content: none; }
                        .os-table td:not([data-label]) > * { grid-column: 1; justify-self: start; }
                    }
                </style>
                <div class="table-responsive os-wrap">
                    <table class="table border-left-right align-middle os-table">
                        <thead>
                            <tr>
                                <th style="width:30px"><input type="checkbox" id="checkAll"></th>
                                <th class="text-center">{{ __('SL') }}</th>
                                <th>{{ __('Item') }}</th>
                                <th>{{ __('Supplier') }}</th>
                                <th>{{ __('Challan') }}</th>
                                <th>{{ __('Barcodes') }}</th>
                                <th class="text-center">{{ __('Qty') }}</th>
                                <th class="text-end">{{ __('Cost') }}</th>
                                <th class="text-end">{{ __('Margin') }}</th>
                                <th class="text-end">{{ __('Retail') }}</th>
                                <th class="text-end">{{ __('Tax') }}</th>
                                <th class="text-end">{{ __('Net Amt') }}</th>
                                <th class="text-center">{{ __('Online') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $key => $row)
                            @php
                                $available = $row->barcode_count - $row->sold_count;
                                // A dash means the source carries nothing; 0.00 means it
                                // recorded a zero. The two must stay distinguishable.
                                $n = fn ($v, $d = 2) => $v === null ? '—' : number_format($v, $d);
                                $codes = $barcodesByLine[$row->id] ?? collect();
                                $gst = ($row->sgst_amount ?? 0) + ($row->cgst_amount ?? 0) + ($row->igst_amount ?? 0);
                            @endphp
                            <tr id="row-{{ $row->id }}">
                                <td><input type="checkbox" class="rowCheck" value="{{ $row->id }}"></td>
                                <td class="text-center">{{ $rows->firstItem() + $key }}</td>

                                <td data-label="Item" class="os-item">
                                    <div class="fw-semibold">{{ $row->product_name ?? __('(unmapped product)') }}</div>
                                    <div class="text-muted os-sub">
                                        {{ $row->product_code ?: '—' }} &middot; {{ __('HSN') }} {{ $row->hsn_code ?: '—' }}
                                    </div>
                                </td>

                                <td data-label="Supplier" class="os-party">
                                    <div>{{ $row->party_name ?? '—' }}</div>
                                    <div class="text-muted os-sub">{{ $row->legacy_party_gstin ?: '—' }}</div>
                                </td>

                                <td data-label="Challan">
                                    @if($row->legacy_voucher_resolved)
                                        <div class="fw-semibold">{{ $row->inward_challan_no }}</div>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ __('Snapshot') }}</span>
                                    @endif
                                    <div class="text-muted os-sub">
                                        {{ $row->inward_date }}
                                        @if($row->legacy_financial_year)
                                            &middot; {{ substr($row->legacy_financial_year, 2, 2) }}-{{ substr($row->legacy_financial_year, 7, 2) }}
                                        @endif
                                    </div>
                                    <div class="os-sub text-muted" title="{{ $row->legacy_line_match }}">
                                        {{ ['inward_detail' => __('detail'), 'inward_summary' => __('summary'), 'snapshot_only' => __('snapshot')][$row->legacy_source] ?? '—' }}
                                        @if($row->legacy_line_match && $row->legacy_line_match !== 'unresolved')
                                            &middot; {{ $row->legacy_line_match }}
                                        @endif
                                    </div>
                                </td>

                                {{-- The printed stickers themselves. Sold ones are struck
                                     through, so a part-sold line reads at a glance. --}}
                                <td data-label="Barcodes" class="os-codes">
                                    <div class="os-bc-wrap">
                                    @forelse($codes as $c)
                                        <span class="os-bc {{ $c->is_sold ? 'sold' : '' }}"
                                              title="{{ $c->is_sold ? __('Sold') : __('In stock') }}">{{ $c->barcode_number }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                    </div>
                                </td>

                                <td data-label="Qty" class="text-center">
                                    <div class="fw-semibold">{{ number_format($row->quantity) }}</div>
                                    <div class="os-sub">
                                        @if($available > 0)
                                            <span class="badge bg-success">{{ number_format($available) }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Sold out') }}</span>
                                        @endif
                                        @if($row->sold_count > 0)
                                            <span class="text-muted">{{ number_format($row->sold_count) }} {{ __('sold') }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td data-label="Cost" class="text-end">
                                    <div class="fw-semibold">{{ $n($row->buy_price) }}</div>
                                    <div class="text-muted os-sub">
                                        {{ __('net') }} {{ $n($row->net_purc_rate) }} &middot;
                                        {{ __('cost') }} {{ $n($row->purcost_rate) }} &middot;
                                        {{ __('exp') }} {{ $n($row->pur_exp_rate) }}
                                    </div>
                                </td>

                                <td data-label="Margin" class="text-end">
                                    <div>&#9650; {{ $n($row->mark_up) }}%</div>
                                    <div class="text-muted os-sub">&#9660; {{ $n($row->mark_down) }}%</div>
                                </td>

                                <td data-label="Retail" class="text-end">
                                    <div class="fw-semibold">{{ $n($row->mrp) }}</div>
                                    <div class="text-muted os-sub">{{ __('sale') }} {{ $n($row->inward_sales_rate) }}</div>
                                </td>

                                <td data-label="Tax" class="text-end">
                                    <div>{{ $row->tax_name ?: '—' }}</div>
                                    <div class="text-muted os-sub">
                                        @if($row->tax_name === null)
                                            {{ __('not per line') }}
                                        @else
                                            S {{ $n($row->sgst_amount) }} &middot; C {{ $n($row->cgst_amount) }} &middot; I {{ $n($row->igst_amount) }}
                                        @endif
                                    </div>
                                </td>

                                <td data-label="Net Amt" class="text-end">{{ $n($row->inward_net_amount) }}</td>

                                <td data-label="Online" class="text-center">
                                    <label class="switch mb-0">
                                        <input type="checkbox" class="onlineToggle" data-id="{{ $row->id }}"
                                               {{ $row->is_online_product ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted py-4">
                                    {{ __('No opening stock found. Run the opening stock import first.') }}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $rows->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const notify = (ok, msg) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: ok ? 'success' : 'error', title: msg, timer: ok ? 1600 : 3000, showConfirmButton: !ok });
        } else if (!ok) {
            alert(msg);
        }
    };

    const csrf = '{{ csrf_token() }}';

    const refreshBulkBar = () => {
        const n = document.querySelectorAll('.rowCheck:checked').length;
        document.getElementById('bulkCount').textContent = n;
        document.getElementById('bulkBar').classList.toggle('d-none', n === 0);
    };


    document.getElementById('checkAll')?.addEventListener('change', function () {
        document.querySelectorAll('.rowCheck').forEach(c => { c.checked = this.checked; });
        refreshBulkBar();
    });

    document.querySelectorAll('.rowCheck').forEach(c => c.addEventListener('change', refreshBulkBar));

    document.querySelectorAll('.onlineToggle').forEach(t => {
        t.addEventListener('change', async function () {
            const id = this.dataset.id;
            const wanted = this.checked;
            this.disabled = true;
            try {
                const res = await fetch(`{{ route('shop.openingStock.toggleOnline', ':id') }}`.replace(':id', id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!data.status) throw new Error(data.message || 'Failed');
                this.checked = data.is_online;
                notify(true, data.message);
            } catch (e) {
                this.checked = !wanted;   // revert on failure
                notify(false, e.message);
            } finally {
                this.disabled = false;
            }
        });
    });

    async function bulkToggle(state) {
        const ids = [...document.querySelectorAll('.rowCheck:checked')].map(c => c.value);
        if (!ids.length) return;
        try {
            const res = await fetch('{{ route('shop.openingStock.bulkToggleOnline') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids, state })
            });
            const data = await res.json();
            if (!data.status) throw new Error(data.message || 'Failed');
            ids.forEach(id => {
                const t = document.querySelector(`.onlineToggle[data-id="${id}"]`);
                if (t) t.checked = state;
            });
            notify(true, data.message);
        } catch (e) {
            notify(false, e.message);
        }
    }
</script>
@endpush
