@extends('layouts.app')

@section('header-title', __('Orders'))

@push('css')
<style>
    .orders-compact-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .orders-compact-table th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.03em !important;
        padding: 0.65rem 0.85rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
        border-top: none !important;
    }
    .orders-compact-table td {
        padding: 0.65rem 0.85rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 0.84rem !important;
        color: #334155 !important;
    }
    .orders-compact-table tbody tr:hover td {
        background-color: #f8fafc !important;
    }
    .order-code {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.84rem;
        letter-spacing: -0.01em;
    }
    .order-date {
        color: #64748b;
        font-size: 0.8125rem;
        white-space: nowrap;
    }
    .order-customer {
        font-weight: 500;
        color: #1e293b;
    }
    .order-shop {
        color: #475569;
    }
    .order-amount {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.85rem;
    }
    .order-status-badge {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 500;
        padding: 0.15rem 0.55rem;
        border-radius: 12px;
        line-height: 1.25;
        margin-top: 0.2rem;
        text-transform: capitalize;
    }
    .badge-paid {
        background-color: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .badge-pending {
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .badge-failed {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .order-action-group {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .action-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .action-icon-btn:hover {
        background: #ede9fe;
        border-color: #c4b5fd;
        transform: translateY(-1px);
    }
    .action-icon-btn img {
        width: 15px;
        height: 15px;
    }
    .tab-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.725rem;
        font-weight: 600;
        line-height: 1;
        padding: 0.2rem 0.5rem;
        border-radius: 12px;
        background-color: #f1f5f9;
        color: #475569;
        margin-left: 0.35rem;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .nav-link.active .tab-count-badge {
        background-color: #000768;
        color: #ffffff;
        border-color: #000768;
    }
</style>
@endpush

@section('content')
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3">
            <ul class="nav nav-tabs mb-3">
            @php
                use App\Enums\OrderStatus;
                $orderStatuses = OrderStatus::cases();

                if (!isset($statusCounts)) {
                    $shop = generaleSetting('shop');
                    $statusCounts = \App\Repositories\OrderRepository::query()
                        ->where('shop_id', $shop?->id)
                        ->selectRaw('order_status, count(*) as total')
                        ->groupBy('order_status')
                        ->pluck('total', 'order_status')
                        ->toArray();
                    $totalOrdersCount = array_sum($statusCounts);
                }
            @endphp

                    <li class="nav-item">
                        <a href="{{ route('shop.order.index') }}"
                        class="nav-link {{ request()->url() === route('shop.order.index') ? 'active' : '' }}">
                        {{ __('All') }}
                        <span class="tab-count-badge">{{ $totalOrdersCount }}</span>
                        </a>
                    </li>

                    @foreach ($orderStatuses as $statusItem)
                    @php
                        $tabCount = $statusCounts[$statusItem->value] ?? $statusCounts[str_replace(' ', '_', $statusItem->value)] ?? 0;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('shop.order.index', str_replace(' ', '_', $statusItem->value)) }}"
                            class="nav-link {{ request()->url() === route('shop.order.index', str_replace(' ', '_', $statusItem->value)) ? 'active' : '' }}">
                            <span>{{ __($statusItem->value) }}</span>
                            <span class="tab-count-badge">{{ $tabCount }}</span>
                        </a>
                    </li>
                    @endforeach

            </ul>
            <div class="table-responsive">

                <table class="table align-middle orders-compact-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">{{ __('Order ID') }}</th>
                            <th style="width: 18%;">{{ __('Order Date') }}</th>
                            <th style="width: 18%;">{{ __('Customer') }}</th>
                            @if (($businessModel ?? '') == 'multi')
                                <th style="width: 16%;">{{ __('Shop') }}</th>
                            @endif
                            <th style="width: 14%;">{{ __('Total Amount') }}</th>
                            <th style="width: 14%;">{{ __('Payment Method') }}</th>
                            <th style="width: 8%;" class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $statusText = $order->payment_status instanceof \BackedEnum
                                    ? $order->payment_status->value
                                    : (string) ($order->payment_status ?? '');

                                $statusLower = strtolower($statusText);
                                $badgeClass = match($statusLower) {
                                    'paid' => 'badge-paid',
                                    'pending' => 'badge-pending',
                                    'unpaid', 'failed', 'cancelled' => 'badge-failed',
                                    default => 'badge-pending',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="order-code">{{ $order->prefix . $order->order_code }}</span>
                                </td>
                                <td>
                                    <span class="order-date">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="order-customer">{{ $order->customer?->user?->name }}</span>
                                </td>

                                @if (($businessModel ?? '') == 'multi')
                                    <td>
                                        <span class="order-shop">{{ $order->shop?->name }}</span>
                                    </td>
                                @endif
                                <td>
                                    <div class="order-amount">{{ showCurrency($order->payable_amount) }}</div>
                                    <span class="order-status-badge {{ $badgeClass }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    <span class="text-secondary">{{ is_string($order->payment_method) ? $order->payment_method : ($order->payment_method?->value ?? $order->payment_method) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="order-action-group">
                                        @hasPermission('shop.order.show')
                                            <a href="{{ route('shop.order.show', $order->id) }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-title="{{ __('view details') }}"
                                                class="action-icon-btn">
                                                <img src="{{ asset('assets/icons-admin/eye.svg') }}" alt="icon"
                                                    loading="lazy" />
                                            </a>
                                        @endhasPermission
                                        <a href="{{ route('shop.download-invoice', $order->id) }}" data-bs-toggle="tooltip"
                                            data-bs-placement="left" data-bs-title="{{ __('Download Invoice') }}"
                                            class="action-icon-btn">
                                            <img src="{{ asset('assets/icons-admin/download-alt.svg') }}" alt="icon"
                                                loading="lazy" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center py-4 text-muted">
                                    {{ __('No order found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

        </div>
    </div>

    <div class="my-3">
        {{ $orders->links() }}
    </div>

@endsection
