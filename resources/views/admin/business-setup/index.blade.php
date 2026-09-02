@extends('layouts.app')

@section('header-title', __('Business Settings'))
@section('header-subtitle', __('Manage Business Settings'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="bi bi-buildings"></i> {{ __('Business Settings') }}
        </div>
    </div>

    <div class="mt-3">
        @include('admin.business-setup.header')
    </div>

    <form action="{{ route('admin.business-setting.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!--######## Basic Information ##########-->
        <div class="card mt-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
                <i class="bi bi-briefcase-fill"></i>
                <h5 class="mb-0">{{ __('Business Information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-lg-4 mt-4">
                        <x-input type="text" label="Company Name" name="name"
                            placeholder="Enter Company Name / Business Name" :value="$generaleSetting?->name" />
                    </div>

                    <div class="col-lg-4 mt-4">
                        <x-input type="text" label="Company Email" name="email" placeholder="Enter Company Email"
                            :value="$generaleSetting?->email" />
                    </div>

                    <div class="col-lg-4 mt-4">
                        <x-input type="text" label="Company Phone" name="mobile" placeholder="Enter Company Phone"
                            :value="$generaleSetting?->mobile" />
                    </div>

                    <div class="col-lg-4 mt-4">
                        <x-input type="text" label="GSTIN/UIN" name="gstin" placeholder="Enter GSTIN/UIN"
                            :value="$generaleSetting?->gstin" />
                    </div>

                    <div class="col-lg-4 mt-4">
                        <x-input type="text" label="Company CIN" name="cin" placeholder="Enter Company CIN"
                            :value="$generaleSetting?->cin" />
                    </div>

                    <div class="col-lg-4 mt-4">
                        <x-input type="text" label="State Name / Code" name="state_name" placeholder="Enter State (e.g. Gujarat, Code: 24)"
                            :value="$generaleSetting?->state_name" />
                    </div>

                    <div class="col-lg-4 mt-4">
                        <x-input type="text" label="Shiprocket Pickup Pincode" name="shiprocket_pickup_pincode" placeholder="Enter Shiprocket Pickup Pincode (e.g. 384170)"
                            :value="$generaleSetting?->shiprocket_pickup_pincode ?? '384170'" />
                    </div>

                    @php
                        $businessType = $generaleSetting?->shop_type ?? 'multi';
                    @endphp

                    <div class="col-lg-4 mt-4">
                        <label class="form-label">{{ __('Business Model') }}</label>
                        <div class="d-flex flex-wrap align-items-center gap-5 border rounded fw-medium"
                            style="padding: 10px;">
                            <div class="flex-grow-1">
                                <input type="radio" name="shop_type" value="single" class="form-check-input"
                                    id="single" {{ $businessType == 'single' ? 'checked' : '' }}>
                                <label for="single" class="m-0 cursor-pointer">
                                    {{ __('Single Shop') }}
                                </label>
                            </div>

                            <div class="flex-grow-1 d-flex align-items-center gap-2">
                                <input type="radio" name="shop_type" value="multi" class="form-check-input"
                                    id="multi" {{ $businessType == 'multi' ? 'checked' : '' }}>
                                <label for="multi" class="m-0 cursor-pointer">
                                    {{ __('Multi Shop') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <label class="form-label">
                            {{ __('Currency Position') }}
                        </label>
                        <div class="d-flex flex-wrap align-items-center gap-5 border rounded fw-medium"
                            style="padding: 10px;">
                            <div class="flex-grow-1">
                                <input type="radio" name="currency_position" value="prefix" class="form-check-input"
                                    id="prefix" {{ $generaleSetting?->currency_position == 'prefix' ? 'checked' : '' }}>
                                <label for="prefix" class="m-0">
                                    ({{ $generaleSetting?->currency ?? '$' }}) {{ __('Left') }}
                                </label>
                            </div>

                            <div class="flex-grow-1 d-flex align-items-center gap-2">
                                <input type="radio" name="currency_position" value="suffix" class="form-check-input"
                                    id="suffix" {{ $generaleSetting?->currency_position == 'suffix' ? 'checked' : '' }}>
                                <label for="suffix" class="m-0">
                                    {{ __('Right') }} ({{ $generaleSetting?->currency ?? '$' }})
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mt-4">
                        <x-select name="timezone" label="Time Zone">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone['zone'] }}"
                                    {{ config('app.timezone') == $timezone['zone'] ? 'selected' : '' }}>
                                    {{ $timezone['diff_from_GMT'] . ' - ' . $timezone['zone'] }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                </div>

            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header py-3">
                <h4 class="card-title m-0 fz-20">
                    <i class="bi bi-credit-card-2-front-fill"></i> {{ __('Payment Method Setup') }}
                </h4>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    <!-- Cash on Delivery -->
                    <label for="cash"
                        class="payment-card border p-3 rounded {{ $generaleSetting?->cash_on_delivery ? 'selected' : '' }}">
                        <div class="d-flex align-items-center">
                            <div class="me-3 fs-3">💰</div>
                            <span class="fw-semibold">
                                {{ __('Cash on Delivery') }}
                            </span>
                        </div>
                        <div class="mt-2 d-flex align-items-center gap-1">
                            <span class="fw-semibold text-muted">
                                {{ $generaleSetting?->cash_on_delivery ? __('Enable') : __('Disable') }}
                            </span>
                            <label class="switch mb-0">
                                <input id="cash" name="cash_on_delivery" type="checkbox"
                                    {{ $generaleSetting?->cash_on_delivery ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="check-icon">✅</div>
                    </label>

                    <!-- Online Payment -->
                    <label for="online"
                        class="payment-card border p-3 rounded {{ $generaleSetting?->online_payment ? 'selected' : '' }}">
                        <div class="d-flex align-items-center">
                            <div class="me-3 fs-3">💳</div>
                            <span class="fw-semibold">
                                {{ __('Online Payment') }}
                            </span>
                        </div>
                        <div class="mt-2 d-flex align-items-center gap-1">
                            <span class="fw-semibold text-muted">
                                {{ $generaleSetting?->online_payment ? __('Enable') : __('Disable') }}
                            </span>
                            <label class="switch mb-0">
                                <input id="online" name="online_payment" type="checkbox"
                                    {{ $generaleSetting?->online_payment ? 'checked' : '' }} />
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="check-icon">✅</div>
                    </label>
                </div>

            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header py-3">
                <h4 class="card-title m-0 fz-20">
                    <i class="bi bi-printer-fill"></i> {{ __('POS Receipt Settings') }}
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <!-- Enable e-Invoice -->
                        <label for="e_invoice"
                            class="payment-card border p-3 rounded w-100 {{ $generaleSetting?->is_e_invoice ? 'selected' : '' }}">
                            <div class="d-flex align-items-center">
                                <div class="me-3 fs-3">🧾</div>
                                <span class="fw-semibold">
                                    {{ __('Enable e-Invoice (QR Code)') }}
                                </span>
                            </div>
                            <div class="mt-2 d-flex align-items-center gap-1">
                                <span class="fw-semibold text-muted">
                                    {{ $generaleSetting?->is_e_invoice ? __('Enable') : __('Disable') }}
                                </span>
                                <label class="switch mb-0">
                                    <input id="e_invoice" name="is_e_invoice" type="checkbox"
                                        {{ $generaleSetting?->is_e_invoice ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="check-icon">✅</div>
                        </label>
                    </div>

                    @php
                        $printerType = $generaleSetting?->pos_printer_type ?? 'thermal';
                    @endphp

                    <div class="col-lg-6">
                        <label class="form-label fw-bold">{{ __('Invoice / Printer Type') }}</label>
                        <div class="d-flex flex-wrap align-items-center gap-4 border rounded p-3 bg-white">
                            <div class="form-check me-3">
                                <input type="radio" name="pos_printer_type" value="thermal" class="form-check-input"
                                    id="printer_thermal" {{ $printerType === 'thermal' ? 'checked' : '' }}>
                                <label for="printer_thermal" class="form-check-label fw-semibold cursor-pointer">
                                    🖨️ {{ __('Thermal Printer (Thermal Invoice)') }}
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="radio" name="pos_printer_type" value="normal" class="form-check-input"
                                    id="printer_normal" {{ ($printerType === 'normal' || $printerType === 'a4') ? 'checked' : '' }}>
                                <label for="printer_normal" class="form-check-label fw-semibold cursor-pointer">
                                    📄 {{ __('Normal Printer - Canon LBP2900B (A4 Invoice)') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @hasPermission('admin.business-setting.update')
            <div class="d-flex justify-content-end mt-4 mb-3">
                <button type="submit" class="btn btn-primary py-2.5 px-3">
                    {{ __('Save And Update') }}
                </button>
            </div>
        @endhasPermission
    </form>
@endsection
@push('css')
    <style>
        .payment-card {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
            position: relative;
            min-width: 280px;
        }

        .payment-card.selected {
            border-color: #28a745 !important;
            box-shadow: 0 0 4px rgba(40, 167, 69, 0.3);
        }

        .check-icon {
            position: absolute;
            top: 6px;
            right: 10px;
            font-size: 1rem;
            color: #28a745;
            display: none;
        }

        .payment-card.selected .check-icon {
            display: block;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $('#cash, #online').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.payment-card').addClass('selected');
            } else {
                $(this).closest('.payment-card').removeClass('selected');
                if (!$('#cash').is(':checked') && !$('#online').is(':checked')) {
                    $('#cash').closest('.payment-card').addClass('selected');
                    $('#cash').prop('checked', true);
                }
            }
        });

        $('#online').on('change', function() {
            if (!$(this).is(':checked')) {
                $('#cash').prop('checked', true);
                $('#cash').closest('.payment-card').addClass('selected');
            }
        });

        $('#commission').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.payment-card').addClass('selected');
                $('#subscription').closest('.payment-card').removeClass('selected');
            } else {
                $(this).closest('.payment-card').removeClass('selected');
                $('#subscription').closest('.payment-card').addClass('selected');
            }
        });

        $('#subscription').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.payment-card').addClass('selected');
                $('#commission').closest('.payment-card').removeClass('selected');
            } else {
                $(this).closest('.payment-card').removeClass('selected');
                $('#commission').closest('.payment-card').addClass('selected');
            }
        });

        $('#e_invoice').on('change', function() {
            const labelSpan = $(this).closest('.payment-card').find('.text-muted');
            if ($(this).is(':checked')) {
                $(this).closest('.payment-card').addClass('selected');
                labelSpan.text("{{ __('Enable') }}");
            } else {
                $(this).closest('.payment-card').removeClass('selected');
                labelSpan.text("{{ __('Disable') }}");
            }
        });

    </script>
@endpush
