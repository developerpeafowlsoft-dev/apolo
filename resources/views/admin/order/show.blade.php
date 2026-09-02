@extends('layouts.app')
@section('header-title', __('Order Details'))

@section('content')

    <div class="row my-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 py-3">
                    <h4 class="card-title mb-0">{{ __('Order Details') }}</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        @if(!$order->shiprocket_shipment_id && !$order->shiprocket_order_id)
                            <button type="button" class="btn btn-indigo text-white py-2.5 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#confirmCreateShipmentModal" style="background-color: #4f46e5; border-color: #4f46e5;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                                <span>{{ __('Ship with Shiprocket') }}</span>
                            </button>
                        @elseif(!$order->shiprocket_awb_code)
                            <button type="button" class="btn btn-indigo text-white py-2.5 d-inline-flex align-items-center gap-1.5 btn-open-courier-modal" style="background-color: #4f46e5; border-color: #4f46e5;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                                <span>{{ __('Ship Now') }}</span>
                            </button>
                        @elseif($order->shiprocket_pickup_status !== 'Scheduled')
                            <button type="button" class="btn btn-success py-2.5 d-inline-flex align-items-center gap-1.5 btn-schedule-pickup">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                                <span>{{ __('Schedule Pickup') }}</span>
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-primary py-2.5 d-inline-flex align-items-center gap-1.5 btn-track-shipment">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>{{ __('Track Shipment') }}</span>
                            </button>
                        @endif
                        <a href="{{ route('shop.payment-slip', $order->id) }}" target="_blank" class="btn btn-success py-2.5">
                            <img src="{{ asset('assets/icons-admin/download-alt.svg') }}" alt="icon" loading="lazy"
                                width="20" />
                            {{ __('Payment Slip') }}
                        </a>
                        <a href="{{ route('shop.download-invoice', $order->id) }}" target="_blank" class="btn btn-primary py-2.5">
                            <img src="{{ asset('assets/icons-admin/download-alt.svg') }}" alt="icon" loading="lazy"
                                width="20" />
                            {{ __('Download Invoice') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 flex-wrap align-items-center">
                        <div class="flex-grow-1">
                            <div class="order-item">
                                <label class="label">{{ __('Order Id') }}:</label>
                                <span class="value">#{{ $order->prefix . $order->order_code }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Payment Status') }}:</label>
                                <span class="value">{{ $order->payment_status }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Payment Method') }}:</label>
                                <span class="value">{{ $order->payment_method }}</span>
                            </div>
                        </div>

                        <div class="item-divider"></div>

                        <div class="flex-grow-1">
                            <div class="order-item">
                                <label class="label">{{ __('Order Status') }}:</label>
                                <span class="value">{{ $order->order_status }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Order Date') }}:</label>
                                <span class="value">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Delivery Date') }}:</label>
                                <span
                                    class="value">{{ $order->delivery_date ? Carbon\Carbon::parse($order->delivery_date)->format('M d, Y') : '-' }}</span>
                            </div>
                        </div>

                        @if($order->pos_order || $order->cashier || $order->salesman || $order->billing_duration_seconds > 0)
                        <div class="item-divider"></div>
                        <div class="flex-grow-1">
                            <div class="order-item">
                                <label class="label">{{ __('Cashier (Collected By)') }}:</label>
                                <span class="value fw-bold text-dark">{{ $order->cashier?->name ?? 'POS Cashier' }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Sales Attendant') }}:</label>
                                <span class="value text-primary fw-bold">{{ $order->salesman?->name ?? 'Direct' }}</span>
                            </div>
                            <div class="order-item">
                                <label class="label">{{ __('Billing Speed') }}:</label>
                                @php
                                    $badge = $order->billing_performance_badge;
                                @endphp
                                <span class="badge {{ $badge['class'] }} px-2 py-1 rounded-pill" style="font-size: 11px;">
                                    <i class="fa-solid {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="table-responsive mt-4 mb-0">
                        <table class="table border-left-right">
                            <thead>
                                <tr>
                                    <th>{{ __('SL') }}</th>
                                    <th>{{ __('Product') }}</th>
                                    @if ($businessModel == 'multi')
                                        <th>{{ __('Shop') }}</th>
                                    @endif
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Size') }}</th>
                                    <th>{{ __('Color') }}</th>
                                    <th>{{ __('HSN') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Discount') }}</th>
                                    <th>{{ __('Tax') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $calculatedTotalTax = 0;
                                @endphp
                                @foreach ($order->products as $key => $product)
                                    @php
                                        $price = (float) (
                                            $product->pivot->price > 0
                                                ? $product->pivot->price
                                                : ($product->discount_price > 0
                                                    ? $product->discount_price
                                                    : $product->price)
                                        );

                                        $quantity = (float) ($product->pivot->quantity ?? 1);

                                        $hsnCode = $product->hsn
                                            ?? $product->hsn_code
                                            ?? $product->hsnMaster?->hsn_code
                                            ?? $product->pivot->hsn
                                            ?? '-';

                                        $itemDiscount = (float) (
                                            $product->pivot->discount_amount
                                            ?? $product->pivot->discount
                                            ?? 0
                                        );

                                        $hsnMaster = $product->hsnMaster ?? null;
                                        if (!$hsnMaster && !empty($product->hsn_master_id)) {
                                            $hsnMaster = \App\Models\HsnMaster::with('subHsn')->find($product->hsn_master_id);
                                        }

                                        if ($hsnMaster) {
                                            $taxRate = (float) $hsnMaster->getTaxForPrice($price);
                                        } else {
                                            $taxRate = (float) (
                                                $product->pivot->tax_percentage
                                                ?? $product->vatTax?->percentage
                                                ?? $product->vat_tax_percentage
                                                ?? 0
                                            );
                                        }

                                        $lineTotal = ($price * $quantity) - $itemDiscount;
                                        if ($taxRate > 0) {
                                            $inclusiveTax = \App\Services\Accounting\GSTPostingService::extractInclusiveTax($lineTotal, $taxRate);
                                            $itemTaxAmount = (float) $inclusiveTax['tax_amount'];
                                        } else {
                                            $itemTaxAmount = 0.0;
                                        }

                                        $calculatedTotalTax += $itemTaxAmount;
                                    @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="d-flex gap-1 align-items-center">
                                                <img src="{{ $product->thumbnail }}" alt="" width="40"
                                                    height="40" loading="lazy">
                                                <span>{{ $product->name }}</span>
                                            </div>
                                        </td>
                                        @if ($businessModel == 'multi')
                                            <td>{{ $product->shop?->name }}</td>
                                        @endif
                                        <td>{{ $product->pivot->quantity }}</td>
                                        <td>{{ $product->pivot->size ?? '-' }}</td>
                                        <td>{{ $product->pivot->color ?? '-' }}</td>
                                        <td>{{ $hsnCode }}</td>
                                        <td>{{ showCurrency($price) }}</td>
                                        <td>{{ showCurrency($itemDiscount) }}</td>
                                        <td>{{ showCurrency(round($itemTaxAmount, 2)) }}</td>
                                        <td class="text-end">
                                            {{ showCurrency($product->pivot->quantity * $price) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="max-300 ms-auto d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('Sub Total') }}</div>
                            <div>{{ showCurrency($order->total_amount) }}</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('Coupon Discount') }}</div>
                            <div>- {{ showCurrency($order->coupon_discount) }}</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('Delivery Charge') }}</div>
                            <div>+ {{ showCurrency($order->delivery_charge) }}</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div>{{ __('VAT & Tax') }}</div>
                            <div>+ {{ showCurrency(round($calculatedTotalTax, 2)) }}</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2 border-top pt-1 mt-1">
                            <div class="fw-bold">{{ __('Grand Total') }}</div>
                            <div class="fw-bold">{{ showCurrency($order->payable_amount) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!--##### Customer Info #####-->
            <div class="mt-3 card">
                <h5 class="fz-16 border-bottom px-3 py-12 m-0">{{ __('Customer Info') }}</h5>

                <div class="border-bottom px-3 py-2 d-flex  align-items-center gap-3">
                    <span class="text-color">{{ __('Name') }}: </span>
                    <span class="fw-medium">{{ $order->customer?->user?->name }}</span>
                </div>
                <div class="px-3 py-2 d-flex  align-items-center gap-3">
                    <span class="text-color">{{ __('Phone') }}: </span>
                    <span class="fw-medium">{{ $order->customer?->user?->phone }}</span>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <!--##### Order & Shipping Info #####-->
            <div class="card">
                <h5 class="fz-18 border-bottom p-3 m-0">{{ __('Order & Shipping Info') }}</h5>

                <div class="px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                    <div class="text-color">{{ __('Change Order Status') }}</div>
                    <div class="dropdown">
                        <a class="btn border text-start dropdown-toggle" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $order->order_status->value }}
                        </a>

                        @hasPermission(['admin.order.status.change'])
                            <ul class="dropdown-menu order-status">
                                @foreach ($orderStatus as $status)
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('admin.order.status.change', $order->id) }}?status={{ $status->value }}">
                                            {{ __($status->value) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endhasPermission
                    </div>
                </div>

                <div class="border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 p-3">
                    <div class="text-color">{{ __('Payment Status') }}</div>
                    <div class="d-flex align-items-center gap-1">
                        <span>{{ $order->payment_status }}</span>
                        @hasPermission('admin.order.payment.status.toggle')
                            <label class="switch mb-0">
                                <a href="{{ route('admin.order.payment.status.toggle', $order->id) }}">
                                    <input type="checkbox" {{ $order->payment_status->value == 'Paid' ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </a>
                            </label>
                        @endhasPermission
                    </div>
                </div>

                @hasPermission('admin.rider.assign.order')
                    @if ($order->order_status->value != 'Pending')
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3">
                            <div class="fw-medium text-color">{{ __('Assign Rider') }}</div>
                            <div class="d-flex align-items-center gap-1">

                                @if ($order->driverOrder)
                                    <span>{{ $order->driverOrder->driver?->user?->fullName }}</span>
                                @else
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#assignRider">
                                        <img src="{{ asset('assets/icons-admin/truck-fill.svg') }}" alt="icon"
                                            loading="lazy" />
                                        {{ __('Assign') }}
                                    </button>
                                @endif

                            </div>
                        </div>
                    @endif
                @endhasPermission
            </div>

            <!--##### Shiprocket Shipment Card #####-->
            <div class="card mt-3" id="shiprocketShipmentCard">
                <div class="border-bottom p-3 d-flex align-items-center justify-content-between">
                    <h5 class="fz-18 m-0">{{ __('Shiprocket Shipment') }}</h5>
                    @if($order->shiprocket_shipment_id || $order->shiprocket_order_id)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" id="shipmentCreatedBadge">{{ __('Shipment Created') }} ✓</span>
                    @endif
                </div>
                <div class="p-3">
                    @if($order->shiprocket_shipment_id || $order->shiprocket_order_id)
                        <div class="d-flex flex-column gap-2 text-sm">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-secondary">{{ __('Shipment ID') }}:</span>
                                <span class="fw-bold text-dark" id="displayShipmentId">{{ $order->shiprocket_shipment_id ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-secondary">{{ __('Shiprocket Order ID') }}:</span>
                                <span class="fw-semibold" id="displayOrderId">{{ $order->shiprocket_order_id ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-secondary">{{ __('Courier Partner') }}:</span>
                                <span class="fw-semibold text-primary" id="displayCourierName">{{ $order->shiprocket_courier_name ?? 'Not Assigned Yet' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-secondary">{{ __('AWB Number') }}:</span>
                                <span class="badge bg-dark text-white fs-6 px-2 py-1" id="displayAwbCode">{{ $order->shiprocket_awb_code ?? 'Pending AWB' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-secondary">{{ __('Payment Mode') }}:</span>
                                @php
                                    $pmStr = is_string($order->payment_method) ? $order->payment_method : ($order->payment_method?->value ?? (string)$order->payment_method);
                                    $isCod = strtolower((string)$pmStr) === 'cash' || strtolower((string)$pmStr) === 'cash payment';
                                @endphp
                                <span class="badge {{ $isCod ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' }} px-2 py-1">
                                    {{ $isCod ? 'COD' : 'Prepaid' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-secondary">{{ __('Pickup Status') }}:</span>
                                <span class="fw-semibold text-success" id="displayPickupStatus">{{ $order->shiprocket_pickup_status ?? 'Pending Request' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 {{ $order->shiprocket_pickup_date ? '' : 'd-none' }}" id="displayPickupDateRow">
                                <span class="text-secondary">{{ __('Pickup Date') }}:</span>
                                <span class="fw-medium" id="displayPickupDate">{{ $order->shiprocket_pickup_date ?? '' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <span class="text-secondary">{{ __('Shipment Status') }}:</span>
                                <span class="badge bg-info-subtle text-info-emphasis px-2 py-1" id="displayShipmentStatus">{{ $order->shiprocket_status ?? 'NEW' }}</span>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="mt-3 pt-2 border-top" id="shiprocketCardActions">
                            @if(!$order->shiprocket_awb_code)
                                <button type="button" class="btn btn-indigo text-white w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 btn-open-courier-modal" style="background-color: #4f46e5; border-color: #4f46e5;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                    <span class="fw-semibold">{{ __('Ship Now') }}</span>
                                </button>
                            @elseif($order->shiprocket_pickup_status !== 'Scheduled')
                                <button type="button" class="btn btn-success w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 btn-schedule-pickup">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="3" width="15" height="13"></rect>
                                        <polygon points="16 8 20 8 23 11 23 16 16 8"></polygon>
                                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                    </svg>
                                    <span class="fw-semibold">{{ __('Schedule Pickup') }}</span>
                                </button>
                            @else
                                <button type="button" class="btn btn-outline-primary w-100 py-2.5 d-flex align-items-center justify-content-center gap-2 btn-track-shipment">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span class="fw-semibold">{{ __('Track Shipment') }}</span>
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-2">
                            <p class="text-muted small mb-3">
                                {{ __('Create shipment in Shiprocket to assign courier and schedule pickup.') }}
                            </p>
                            <button type="button" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2" data-bs-toggle="modal" data-bs-target="#confirmCreateShipmentModal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="1" y="3" width="15" height="13"></rect>
                                    <polygon points="16 8 20 8 23 11 23 16 16 8"></polygon>
                                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                </svg>
                                <span>{{ __('Create Shipment / Ship with Shiprocket') }}</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!--##### Shipping Address #####-->
            <div class="card mt-3">
                <h5 class="fz-18 border-bottom p-3 m-0">{{ __('Shipping Address') }}</h5>

                @if($order->address)
                    <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                        <span class="text-color">{{ __('Name') }}: </span>
                        <span class="fw-medium">{{ $order->address->name }}</span>
                    </div>
                    <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                        <span class="text-color">{{ __('Phone') }}: </span>
                        <span class="fw-medium">{{ $order->address->phone }}</span>
                    </div>
                    <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                        <span class="text-color">{{ __('Address Type') }}: </span>
                        <span class="fw-medium text-capitalize">{{ $order->address->address_type }}</span>
                    </div>
                    @if($order->address->flat_no)
                        <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                            <span class="text-color">{{ __('Flat No') }}: </span>
                            <span class="fw-medium">{{ $order->address->flat_no }}</span>
                        </div>
                    @endif
                    @if($order->address->house_no)
                        <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                            <span class="text-color">{{ __('House No') }}: </span>
                            <span class="fw-medium">{{ $order->address->house_no }}</span>
                        </div>
                    @endif
                    @if($order->address->road_no)
                        <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                            <span class="text-color">{{ __('Road No') }}: </span>
                            <span class="fw-medium">{{ $order->address->road_no }}</span>
                        </div>
                    @endif
                    @if($order->address->address_line)
                        <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                            <span class="text-color">{{ __('Address Line') }}: </span>
                            <span class="fw-medium">{{ $order->address->address_line }}</span>
                        </div>
                    @endif
                    @if($order->address->address_line2)
                        <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                            <span class="text-color">{{ __('Address Line 2') }}: </span>
                            <span class="fw-medium">{{ $order->address->address_line2 }}</span>
                        </div>
                    @endif
                    @if($order->address->area)
                        <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                            <span class="text-color">{{ __('Area') }}: </span>
                            <span class="fw-medium">{{ $order->address->area }}</span>
                        </div>
                    @endif
                    @if($order->address->post_code)
                        <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                            <span class="text-color">{{ __('Post Code') }}: </span>
                            <span class="fw-medium">{{ $order->address->post_code }}</span>
                        </div>
                    @endif
                    <div class="border-bottom d-flex align-items-center justify-content-between gap-2 px-3 py-12">
                        <span class="text-color">{{ __('Country') }}: </span>
                        <span class="fw-medium">{{ $order->address->country?->name ?? 'India' }}</span>
                    </div>
                    @php
                        $fullAddressParts = array_filter([
                            $order->address->flat_no,
                            $order->address->house_no ? 'House No: ' . $order->address->house_no : null,
                            $order->address->road_no ? 'Road No: ' . $order->address->road_no : null,
                            $order->address->address_line,
                            $order->address->address_line2,
                            $order->address->area,
                            $order->address->post_code,
                            $order->address->country?->name ?? 'India',
                        ], fn($val) => !empty(trim((string)$val)));
                        $fullAddressString = implode(', ', $fullAddressParts);
                    @endphp
                    @if($fullAddressString)
                        <div class="d-flex align-items-start justify-content-between gap-2 px-3 py-12">
                            <span class="text-color shrink-0">{{ __('Full Address') }}: </span>
                            <span class="fw-medium text-end">{{ $fullAddressString }}</span>
                        </div>
                    @endif
                @else
                    <div class="p-3 text-muted">
                        {{ __('No shipping address available') }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Assign Rider Modal -->
    <form action="{{ route('admin.rider.assign.order', $order->id) }}" method="POST">
        @csrf
        <div class="modal fade" id="assignRider">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title fs-5">{{ __('Select a rider') }}</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex gap-2 flex-column">
                            @foreach ($riders as $rider)
                                <div class="w-100">
                                    <input type="radio" name="rider" value="{{ $rider->id }}"
                                        id="rider{{ $rider->id }}" class="btn-check">
                                    <label for="rider{{ $rider->id }}" class="btn riderSelectBtn">
                                        <div>
                                            <img src="{{ $rider->user->thumbnail }}" alt="profile"
                                                class="profilePhoto" />
                                            <span class="riderName">
                                                {{ $rider->user->fullName }}
                                            </span>
                                        </div>
                                        <div class="d-flex gap-1 align-items-center">
                                            <span class="text-muted inCompleted">
                                                {{ __('Incomplete Orders') }}:
                                            </span>
                                            <span class="totalOrders">{{ $rider->incompleteOrders()->count() }}</span>
                                        </div>

                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Assign Now') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!--##### Modal: Confirm Create Shiprocket Shipment #####-->
    <div class="modal fade" id="confirmCreateShipmentModal" tabindex="-1" aria-labelledby="confirmCreateShipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-body text-center p-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background-color: #ede9fe; color: #6366f1;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                    </div>
                    <h5 class="fw-bold fs-5 text-dark mb-2" id="confirmCreateShipmentModalLabel">
                        {{ __('Create Shiprocket Shipment?') }}
                    </h5>
                    <p class="text-dark mb-2">
                        {{ __('Are you sure you want to create this shipment in Shiprocket?') }}
                    </p>
                    <p class="text-muted small mb-4">
                        {{ __('Once confirmed, this order will be created in your connected Shiprocket account.') }}
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal" id="btnCancelCreateShipment">
                            {{ __('Cancel') }}
                        </button>
                        <form action="{{ route('admin.order.create-shipment', $order->id) }}" method="POST" id="formConfirmCreateShipment" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-indigo text-white px-4 py-2 d-inline-flex align-items-center gap-2" id="btnSubmitCreateShipment" style="background-color: #4f46e5; border-color: #4f46e5;">
                                <span class="spinner-border spinner-border-sm d-none" id="spinnerCreateShipment" role="status" aria-hidden="true"></span>
                                <span id="textCreateShipment">{{ __('Yes, Create Shipment') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--##### Modal: Select Courier Partner #####-->
    <div class="modal fade" id="selectCourierPartnerModal" tabindex="-1" aria-labelledby="selectCourierPartnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header border-bottom py-3 px-4" style="background-color: #f8fafc; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background-color: #ede9fe; color: #6366f1;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold fs-5 text-dark m-0" id="selectCourierPartnerModalLabel">{{ __('Select Courier Partner') }}</h5>
                            <span class="text-muted small">{{ __('Choose the best shipping partner for Order #') }}{{ $order->prefix . $order->order_code }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Shipment Parameters Bar -->
                    <div class="p-3 mb-4 rounded-3 border bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <span class="text-muted small d-block">{{ __('Pickup From') }}</span>
                            <span class="fw-bold text-dark" id="modalPickupFrom"><i class="bi bi-geo-alt text-primary"></i> --</span>
                        </div>
                        <div class="border-start ps-3 d-none d-md-block" style="height: 30px;"></div>
                        <div>
                            <span class="text-muted small d-block">{{ __('Deliver To') }}</span>
                            <span class="fw-bold text-dark" id="modalDeliverTo"><i class="bi bi-geo-alt-fill text-danger"></i> --</span>
                        </div>
                        <div class="border-start ps-3 d-none d-md-block" style="height: 30px;"></div>
                        <div>
                            <span class="text-muted small d-block">{{ __('Payment Mode') }}</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold" id="modalPaymentMode">--</span>
                        </div>
                        <div class="border-start ps-3 d-none d-md-block" style="height: 30px;"></div>
                        <div>
                            <span class="text-muted small d-block">{{ __('Applicable Weight') }}</span>
                            <span class="fw-bold text-dark" id="modalApplicableWeight">--</span>
                        </div>
                        <div class="border-start ps-3 d-none d-md-block" style="height: 30px;"></div>
                        <div>
                            <span class="text-muted small d-block">{{ __('Order Value') }}</span>
                            <span class="fw-bold text-dark" id="modalOrderValue">--</span>
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <ul class="nav nav-pills gap-2" id="courierFilterTabs">
                            <li class="nav-item">
                                <button class="nav-link active py-1.5 px-3 rounded-pill fw-medium filter-tab-btn" data-filter="recommended">
                                    <i class="bi bi-patch-check-fill me-1"></i> {{ __('Recommended') }}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-1.5 px-3 rounded-pill fw-medium filter-tab-btn" data-filter="surface">
                                    <i class="bi bi-truck me-1"></i> {{ __('Surface') }}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-1.5 px-3 rounded-pill fw-medium filter-tab-btn" data-filter="air">
                                    <i class="bi bi-airplane me-1"></i> {{ __('Air') }}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-1.5 px-3 rounded-pill fw-medium filter-tab-btn" data-filter="all">
                                    {{ __('All Couriers') }}
                                </button>
                            </li>
                        </ul>
                        <div class="text-muted small" id="courierCountText">
                            <!-- Couriers count -->
                        </div>
                    </div>

                    <!-- Recommendation Banner -->
                    <div class="alert alert-light border border-primary-subtle d-flex align-items-center gap-2 py-2 px-3 mb-3 rounded-3" style="background-color: #f0fdf4;">
                        <i class="bi bi-stars text-success fs-5"></i>
                        <span class="text-secondary small">
                            <strong class="text-success">{{ __('Smart Courier Allocation') }}:</strong>
                            {{ __('Couriers are rated and prioritized based on live SLA performance, pickup speed, and real-time deliverability on this route.') }}
                        </span>
                    </div>

                    <!-- Couriers List Container -->
                    <div id="couriersLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-3 mb-0">{{ __('Fetching serviceable courier partners from Shiprocket...') }}</p>
                    </div>

                    <div id="couriersError" class="alert alert-danger d-none my-3" role="alert">
                        <div class="d-flex align-items-center justify-content-between">
                            <span id="couriersErrorMessage">{{ __('Failed to load couriers.') }}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnRetryCouriers">{{ __('Retry') }}</button>
                        </div>
                    </div>

                    <div id="couriersList" class="d-flex flex-column gap-3 d-none">
                        <!-- Dynamic Courier Cards rendered by JavaScript -->
                    </div>

                    <div id="couriersEmpty" class="text-center py-5 d-none">
                        <div class="text-muted fs-1 mb-2"><i class="bi bi-inbox"></i></div>
                        <h6 class="fw-bold text-dark">{{ __('No courier partner found for this filter.') }}</h6>
                        <p class="text-muted small">{{ __('Try selecting "All Couriers" tab or verify order weight and pincodes.') }}</p>
                    </div>
                </div>
                <div class="modal-footer py-2 px-4 bg-light border-top">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!--##### Modal: Shiprocket Live Tracking #####-->
    <div class="modal fade" id="shiprocketTrackingModal" tabindex="-1" aria-labelledby="shiprocketTrackingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header border-bottom py-3 px-4" style="background-color: #f8fafc; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                        <div>
                            <h5 class="modal-title fw-bold fs-5 text-dark m-0" id="shiprocketTrackingModalLabel">{{ __('Shipment Tracking') }}</h5>
                            <span class="text-muted small" id="trackingModalAwb">AWB: --</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="trackingLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">{{ __('Fetching real-time tracking updates...') }}</p>
                    </div>
                    <div id="trackingContent" class="d-none">
                        <!-- Tracking timeline rendered here -->
                    </div>
                    <div id="trackingError" class="alert alert-warning d-none">
                        <span id="trackingErrorMessage">{{ __('Tracking information is not available yet.') }}</span>
                    </div>
                </div>
                <div class="modal-footer py-2 px-4 bg-light border-top">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    <style>
        .dropdown-menu.order-status {
            min-width: 200px;
            padding: 8px;
            border: 1px solid #e5e5e5;
            box-shadow: 0 0 10px #e5e5e5;
        }

        .dropdown-menu.order-status .dropdown-item {
            border-bottom: 1px solid #f1f1f1;
        }

        .app-theme-dark .dropdown-menu.order-status {
            border: 1px solid #343a40;
            box-shadow: 0 0 10px #343a40;
        }
        .app-theme-dark .dropdown-menu.order-status .dropdown-item {
            border-bottom: 1px solid #343a40;
        }

        .max-300 {
            max-width: 340px;
        }

        .min-w-200 {
            min-width: 200px;
            display: inline;
        }

        .item-divider {
            height: 80px;
            width: 1px;
            background: #e5e5e5;
            margin: 0 20px;
        }

        .app-theme-dark .item-divider {
            background: #343a40;
        }

        .order-item {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .order-item:last-child {
            margin-bottom: 0;
        }

        .order-item .label {
            color: #687387;
            line-height: 22px;
        }

       .app-theme-dark .order-item .label {
            color: #8f96a6;
        }

        .order-item .value {
            line-height: 22px;
            font-weight: 500;
            color: #000;
        }

        .app-theme-dark .order-item .value {
            color: #fff;
        }

        @media (max-width: 768px) {
            .item-divider {
                display: none;
            }
        }
    </style>
@endpush
@push('js')
    <script>
        $(document).ready(function () {
            // Confirm Create Shipment Submit Handler (Prevent cancel on submit and show spinner)
            $('#formConfirmCreateShipment').on('submit', function (e) {
                let btn = $('#btnSubmitCreateShipment');
                let cancelBtn = $('#btnCancelCreateShipment');
                if (btn.data('submitting')) {
                    e.preventDefault();
                    return false;
                }
                btn.data('submitting', true);
                btn.css('pointer-events', 'none').css('opacity', '0.85');
                cancelBtn.css('pointer-events', 'none').css('opacity', '0.5');
                $('#spinnerCreateShipment').removeClass('d-none');
                $('#textCreateShipment').text('{{ __("Creating Shipment...") }}');
            });

            $('#confirmCreateShipmentModal').on('hidden.bs.modal', function () {
                let btn = $('#btnSubmitCreateShipment');
                let cancelBtn = $('#btnCancelCreateShipment');
                btn.data('submitting', false);
                btn.css('pointer-events', '').css('opacity', '');
                cancelBtn.css('pointer-events', '').css('opacity', '');
                $('#spinnerCreateShipment').addClass('d-none');
                $('#textCreateShipment').text('{{ __("Yes, Create Shipment") }}');
            });

            let couriersData = [];
            let currentFilter = 'recommended';
            let courierModalElement = document.getElementById('selectCourierPartnerModal');
            let trackingModalElement = document.getElementById('shiprocketTrackingModal');
            let courierModal = courierModalElement ? new bootstrap.Modal(courierModalElement) : null;
            let trackingModal = trackingModalElement ? new bootstrap.Modal(trackingModalElement) : null;

            // Open Courier Modal & Fetch Couriers
            $(document).on('click', '.btn-open-courier-modal', function (e) {
                e.preventDefault();
                if (courierModal) {
                    courierModal.show();
                    fetchCouriers();
                }
            });

            function fetchCouriers() {
                $('#couriersLoading').removeClass('d-none');
                $('#couriersError').addClass('d-none');
                $('#couriersList').addClass('d-none');
                $('#couriersEmpty').addClass('d-none');

                $.ajax({
                    url: "{{ route('admin.order.shiprocket.couriers', $order->id) }}",
                    type: "GET",
                    dataType: "json",
                    success: function (response) {
                        $('#couriersLoading').addClass('d-none');
                        if (response.status && response.data) {
                            let d = response.data;
                            $('#modalPickupFrom').html('<i class="bi bi-geo-alt text-primary me-1"></i> ' + (d.pickup_postcode || '384170'));
                            $('#modalDeliverTo').html('<i class="bi bi-geo-alt-fill text-danger me-1"></i> ' + (d.delivery_postcode || '382010'));
                            $('#modalPaymentMode').text(d.payment_mode || 'COD');
                            $('#modalApplicableWeight').text((d.applicable_weight || '0.5') + ' Kg');
                            $('#modalOrderValue').text('₹' + parseFloat(d.order_value || 0).toFixed(2));

                            couriersData = d.couriers || [];
                            renderCouriers();
                        } else {
                            $('#couriersError').removeClass('d-none');
                            $('#couriersErrorMessage').text(response.message || 'Unable to fetch courier rates.');
                        }
                    },
                    error: function (xhr) {
                        $('#couriersLoading').addClass('d-none');
                        $('#couriersError').removeClass('d-none');
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error communicating with Shiprocket service.';
                        $('#couriersErrorMessage').text(msg);
                    }
                });
            }

            $('#btnRetryCouriers').on('click', function () {
                fetchCouriers();
            });

            // Filter tabs
            $('.filter-tab-btn').on('click', function () {
                $('.filter-tab-btn').removeClass('active');
                $(this).addClass('active');
                currentFilter = $(this).data('filter');
                renderCouriers();
            });

            function renderCouriers() {
                let filtered = couriersData.filter(function (c) {
                    if (currentFilter === 'recommended') {
                        return c.is_recommended;
                    } else if (currentFilter === 'surface') {
                        return c.is_surface;
                    } else if (currentFilter === 'air') {
                        return c.is_air;
                    }
                    return true;
                });

                // Fallback: If recommended tab has 0 couriers, show all
                if (currentFilter === 'recommended' && filtered.length === 0 && couriersData.length > 0) {
                    filtered = couriersData;
                }

                $('#courierCountText').text(filtered.length + ' {{ __("Couriers Found") }}');

                if (filtered.length === 0) {
                    $('#couriersList').addClass('d-none');
                    $('#couriersEmpty').removeClass('d-none');
                    return;
                }

                $('#couriersEmpty').addClass('d-none');
                let html = '';

                filtered.forEach(function (c) {
                    let isRec = c.is_recommended;
                    let rating = parseFloat(c.rating || 4.0).toFixed(1);
                    let ratingColor = rating >= 4.0 ? 'bg-success' : (rating >= 3.0 ? 'bg-warning text-dark' : 'bg-secondary');

                    html += `
                    <div class="card border rounded-3 p-3 position-relative ${isRec ? 'border-primary shadow-sm' : ''}" style="background-color: ${isRec ? '#f8fafc' : '#ffffff'};">
                        ${isRec ? '<span class="badge bg-primary text-white position-absolute" style="top: -10px; left: 16px; font-size: 11px; padding: 4px 10px;"><i class="bi bi-patch-check-fill me-1"></i> {{ __("Recommended") }}</span>' : ''}
                        
                        <div class="row align-items-center g-3 pt-1">
                            <!-- Courier Info -->
                            <div class="col-lg-4 col-md-5">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-bold text-dark fs-6">${c.courier_name}</span>
                                    <span class="badge ${ratingColor} text-white px-2 py-0.5 rounded-pill" style="font-size: 11px;">★ ${rating}</span>
                                </div>
                                <div class="text-muted small mb-1">
                                    <span class="badge bg-light text-dark border me-1">${c.mode}</span>
                                    <span>Min Weight: ${c.min_weight} Kg</span>
                                </div>
                                <div class="text-secondary small">
                                    <span><i class="bi bi-clock-history me-1"></i>ETD: <strong>${c.etd}</strong></span>
                                </div>
                            </div>

                            <!-- Pickup & Delivery Timeline -->
                            <div class="col-lg-3 col-md-3">
                                <div class="small text-muted mb-1">{{ __("Expected Pickup") }}: <strong class="text-dark">${c.expected_pickup}</strong></div>
                                <div class="small text-muted mb-1">{{ __("Chargeable Weight") }}: <strong class="text-dark">${c.charge_weight} Kg</strong></div>
                                <div class="small text-muted">{{ __("RTO Charges") }}: <span class="text-danger fw-medium">₹${parseFloat(c.rto_charges || 0).toFixed(2)}</span></div>
                            </div>

                            <!-- Pricing Breakdown -->
                            <div class="col-lg-3 col-md-4">
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>{{ __("Shipping Rate") }}:</span>
                                        <span class="fw-medium text-dark">₹${parseFloat(c.rate || 0).toFixed(2)}</span>
                                    </div>
                                    ${c.whatsapp_charges > 0 ? `
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>{{ __("WhatsApp Fee") }}:</span>
                                        <span class="fw-medium text-dark">+ ₹${parseFloat(c.whatsapp_charges).toFixed(2)}</span>
                                    </div>` : ''}
                                    <div class="d-flex justify-content-between align-items-center border-top pt-1 mt-1">
                                        <span class="fw-bold text-dark small">{{ __("Shiprocket Total") }}:</span>
                                        <span class="fs-5 fw-bolder text-primary">₹${parseFloat(c.total_cost || c.rate).toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ship Now Action Button -->
                            <div class="col-lg-2 col-md-12 text-end">
                                <button type="button" 
                                        class="btn btn-indigo text-white w-100 py-2.5 d-flex align-items-center justify-content-center gap-1.5 btn-assign-courier" 
                                        data-courier-id="${c.courier_company_id}" 
                                        data-courier-name="${c.courier_name}"
                                        style="background-color: #4f46e5; border-color: #4f46e5;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                    <span class="btn-text fw-semibold">{{ __("Ship Now") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>`;
                });

                $('#couriersList').html(html).removeClass('d-none');
            }

            // Assign Courier / Generate AWB
            $(document).on('click', '.btn-assign-courier', function (e) {
                e.preventDefault();
                let btn = $(this);
                let courierId = btn.data('courier-id');
                let courierName = btn.data('courier-name');

                if (btn.prop('disabled')) return;

                // Disable all buttons in modal to prevent double submission
                $('.btn-assign-courier').prop('disabled', true);
                btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Generating AWB...") }}');

                $.ajax({
                    url: "{{ route('admin.order.shiprocket.assign-courier', $order->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        courier_company_id: courierId
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.status) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'AWB generated successfully!');
                            }
                            if (courierModal) courierModal.hide();
                            setTimeout(function () {
                                location.reload();
                            }, 800);
                        } else {
                            $('.btn-assign-courier').prop('disabled', false);
                            btn.html('<span>{{ __("Ship Now") }}</span>');
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'Failed to assign courier.');
                            } else {
                                alert(response.message || 'Failed to assign courier.');
                            }
                        }
                    },
                    error: function (xhr) {
                        $('.btn-assign-courier').prop('disabled', false);
                        btn.html('<span>{{ __("Ship Now") }}</span>');
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error assigning courier.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        } else {
                            alert(msg);
                        }
                    }
                });
            });

            // Schedule Pickup
            $(document).on('click', '.btn-schedule-pickup', function (e) {
                e.preventDefault();
                let btn = $(this);

                if (btn.prop('disabled')) return;
                btn.prop('disabled', true);
                let origHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Scheduling...") }}');

                $.ajax({
                    url: "{{ route('admin.order.shiprocket.schedule-pickup', $order->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.status) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'Pickup scheduled successfully!');
                            }
                            setTimeout(function () {
                                location.reload();
                            }, 800);
                        } else {
                            btn.prop('disabled', false).html(origHtml);
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'Failed to schedule pickup.');
                            }
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html(origHtml);
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to schedule pickup.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        } else {
                            alert(msg);
                        }
                    }
                });
            });

            // Track Shipment
            $(document).on('click', '.btn-track-shipment', function (e) {
                e.preventDefault();
                if (trackingModal) trackingModal.show();
                $('#trackingLoading').removeClass('d-none');
                $('#trackingContent').addClass('d-none');
                $('#trackingError').addClass('d-none');

                let awb = $('#displayAwbCode').text() || '{{ $order->shiprocket_awb_code }}';
                $('#trackingModalAwb').text('AWB: ' + awb);

                $.ajax({
                    url: "{{ route('admin.order.shiprocket.track', $order->id) }}",
                    type: "GET",
                    dataType: "json",
                    success: function (response) {
                        $('#trackingLoading').addClass('d-none');
                        if (response.status && response.data) {
                            let td = response.data;
                            let trackingData = td.tracking_data || td;
                            let tracks = trackingData.shipment_track_activities || trackingData.track_status || [];

                            if (tracks && tracks.length > 0) {
                                let thtml = '<div class="timeline ps-3">';
                                tracks.forEach(function (tr) {
                                    thtml += `
                                    <div class="border-start border-2 border-primary ps-3 pb-3 position-relative">
                                        <span class="position-absolute rounded-circle bg-primary" style="width: 10px; height: 10px; left: -6px; top: 4px;"></span>
                                        <div class="fw-bold text-dark">${tr.activity || tr.status || 'Status Update'}</div>
                                        <div class="text-muted small">${tr.location || ''} &bull; ${tr.date || ''}</div>
                                    </div>`;
                                });
                                thtml += '</div>';
                                $('#trackingContent').html(thtml).removeClass('d-none');
                            } else {
                                let currentStatus = trackingData.current_status || 'In Transit / Processing';
                                $('#trackingContent').html(`
                                    <div class="p-3 bg-light rounded-3 text-center">
                                        <div class="fw-bold text-dark fs-6 mb-1">${currentStatus}</div>
                                        <p class="text-muted small mb-0">{{ __('Tracking updates will be updated in real-time as the courier scans your package.') }}</p>
                                    </div>
                                `).removeClass('d-none');
                            }
                        } else {
                            $('#trackingError').removeClass('d-none');
                            $('#trackingErrorMessage').text(response.message || 'Tracking updates are currently pending.');
                        }
                    },
                    error: function (xhr) {
                        $('#trackingLoading').addClass('d-none');
                        $('#trackingError').removeClass('d-none');
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Unable to fetch tracking data.';
                        $('#trackingErrorMessage').text(msg);
                    }
                });
            });
        });
    </script>
@endpush
