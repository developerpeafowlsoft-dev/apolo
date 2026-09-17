@extends('layouts.app')
@section('header-title', $def['label'])
@section('content')
@include('shop.opening-stock._report-style')

@php
    $fmt = fn ($v) => $def['money'] ? '₹ ' . number_format((float) $v, 2) : number_format((float) $v);
    $fmtAny = fn ($v, $money) => $money ? '₹ ' . number_format((float) $v, 2) : number_format((float) $v);
    $qs = fn (array $extra = []) => array_filter(array_merge(['fy' => $fy], $extra));
@endphp

<div class="d-flex align-items-start flex-wrap gap-2 justify-content-between px-3">
    <div>
        <h4 class="mb-0"><i class="fa-solid {{ $def['icon'] }} me-2 text-primary"></i>{{ $def['label'] }}</h4>
        <div class="text-muted small">{{ __('Opening Stock') }} &middot; {{ $def['sub'] }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('shop.openingStock.vendors', $qs()) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-truck-field me-1"></i>{{ __('Vendor report') }}
        </a>
        <a href="{{ route('shop.openingStock.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Back to register') }}
        </a>
    </div>
</div>

<div class="container-fluid mt-3">

    {{-- What this number is, and exactly how it is counted. --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-lg-4">
                    <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing:.05em">
                        {{ $fy !== '' ? $fy : __('All years') . ' ' . $span }}
                    </div>
                    <div class="fw-bold osr-hero">{{ $fmt($headline) }}</div>
                    <div class="text-muted small">
                        {{ $def['grain'] === 'line' ? __('measured per inward line') : __('measured per printed barcode') }}
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <p class="mb-2">{{ $def['blurb'] }}</p>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ __('How it is counted') }}</span>
                        <code class="osr-code">{{ $def['sql'] }}</code>
                    </div>
                    @isset($def['note'])
                        <p class="text-muted small mb-0 mt-2">
                            <i class="fa-solid fa-circle-info me-1"></i>{{ $def['note'] }}
                        </p>
                    @endisset
                </div>
            </div>
        </div>
    </div>

    {{-- Every card, so this page is also the way between them. --}}
    <div class="row g-2 mb-3">
        @foreach($siblings as $key => $s)
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('shop.openingStock.insight', $key) . ($fy !== '' ? '?fy=' . urlencode($fy) : '') }}"
                   class="osr-kpi {{ $key === $metric ? 'is-current' : '' }}">
                    <div class="k-label">{{ $s['label'] }}</div>
                    <div class="k-value">{{ $fmtAny($s['value'], $s['money']) }}</div>
                    <div class="k-go">{{ $key === $metric ? __('you are here') : __('open →') }}</div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
                <div>
                    <label class="form-label small mb-1">{{ __('Narrow to one inward financial year') }}</label>
                    <select name="fy" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:200px">
                        <option value="">{{ __('All years') }}</option>
                        @foreach($financialYears as $y)
                            <option value="{{ $y }}" {{ $fy === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                @if($fy !== '')
                    <a href="{{ route('shop.openingStock.insight', $metric) }}" class="btn btn-sm btn-outline-secondary">{{ __('All years') }}</a>
                @endif
            </form>
        </div>
    </div>

    {{-- One section per way of cutting the number. --}}
    @foreach($breakdowns as $dim => $b)
        @php
            $meta = $b['meta'];
            $capped = isset($meta['limit']) && $b['rows']->count() >= $meta['limit'];
        @endphp
        <div class="card mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold mb-0">{{ $meta['label'] }}</h6>
                <span class="text-muted small">{{ $meta['note'] }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive osr-wrap">
                    <table class="table align-middle osr-table mb-0">
                        <thead>
                            <tr>
                                <th style="width:34px" class="text-center">{{ __('#') }}</th>
                                <th>{{ $meta['head'] }}</th>
                                <th class="text-end">{{ $def['label'] }}</th>
                                <th style="min-width:150px">{{ __('Share of total') }}</th>
                                @if($def['filter'])
                                    <th class="text-end">{{ $def['grain'] === 'line' ? __('Lines here') : __('Units here') }}</th>
                                    <th class="text-end">{{ __('Hit rate') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($b['rows'] as $k => $r)
                            @php
                                $share = $b['total'] > 0 ? $r->value / $b['total'] * 100 : 0;
                                $width = $b['max'] > 0 ? $r->value / $b['max'] * 100 : 0;
                                $hit   = ($r->universe ?? 0) > 0 ? $r->value / $r->universe * 100 : 0;
                            @endphp
                            <tr>
                                <td class="text-center text-muted osr-num">{{ $k + 1 }}</td>
                                <td data-label="{{ $meta['head'] }}" class="osr-name">
                                    @if($dim === 'vendor' && $r->ref)
                                        <a href="{{ route('shop.openingStock.vendor', $r->ref) }}" class="fw-semibold text-decoration-none">
                                            {{ $r->label ?: __('(unnamed)') }}
                                        </a>
                                    @else
                                        <span class="fw-semibold">{{ $r->label !== null && $r->label !== '' ? $r->label : __('(not recorded)') }}</span>
                                    @endif
                                </td>
                                <td data-label="{{ $def['label'] }}" class="text-end osr-num fw-semibold">{{ $fmt($r->value) }}</td>
                                <td data-label="{{ __('Share of total') }}">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="osr-num" style="min-width:44px">{{ number_format($share, 1) }}%</span>
                                        <span class="osr-bar flex-grow-1" style="min-width:60px"><span style="width: {{ min(100, $width) }}%"></span></span>
                                    </div>
                                </td>
                                @if($def['filter'])
                                    <td data-label="{{ __('Total here') }}" class="text-end osr-num">{{ number_format($r->universe) }}</td>
                                    <td data-label="{{ __('Hit rate') }}" class="text-end osr-num">{{ number_format($hit, 1) }}%</td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $def['filter'] ? 6 : 4 }}" class="text-center text-muted py-4">{{ __('Nothing to show for this year.') }}</td></tr>
                        @endforelse
                        </tbody>
                        @if($b['rows']->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td></td>
                                <td data-label="{{ __('Total') }}">
                                    @if($capped){{ __('Top :n shown', ['n' => $meta['limit']]) }} — @endif{{ __('Total') }}
                                </td>
                                <td data-label="{{ $def['label'] }}" class="text-end osr-num">{{ $fmt($b['total']) }}</td>
                                <td data-label="{{ __('Share') }}">
                                    {{ $headline > 0 ? number_format($b['total'] / $headline * 100, 1) . '% ' . __('of the headline') : '' }}
                                </td>
                                @if($def['filter'])
                                    <td data-label="{{ __('Total here') }}" class="text-end osr-num">{{ number_format($b['rows']->sum('universe')) }}</td>
                                    <td></td>
                                @endif
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                @if($capped)
                    <p class="text-muted small mb-0 mt-2">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        {{ __('Only the top :n rows are listed, so this table adds to less than the headline.', ['n' => $meta['limit']]) }}
                        @if($dim === 'vendor')
                            <a href="{{ route('shop.openingStock.vendors', $qs()) }}">{{ __('See every supplier') }}</a>.
                        @endif
                    </p>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
