@extends('layouts.app')
@section('header-title', __('Edit Shop'))
@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="fa-solid fa-shop"></i>{{ __('Edit Shop') }}
        </div>
    </div>

    <form action="{{ route('shop.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card mt-3">
            <div class="card-body">

                <div class="d-flex gap-2 border-bottom pb-2">
                    <i class="fa-solid fa-user"></i>
                    <h5>
                        {{ __('User Information') }}
                    </h5>
                </div>

                <div class="row">
                    <div class="col-lg-7">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mt-3">
                                    <x-input label="First Name" name="first_name" type="text" placeholder="First Name"
                                        :value="$shop->user?->name" required="true" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-3">
                                    <x-input label="Last Name" name="last_name" type="text" placeholder="Last Name"
                                        :value="$shop->user?->last_name" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <x-input label="Phone Number" name="phone" type="number" placeholder="phone number"
                                :value="$shop->user?->phone" required="true" />
                        </div>

                        <div class="mt-3">
                            <x-select label="Gender" name="gender">
                                <option value="male" {{ $shop->user?->gender == 'male' ? 'selected' : '' }}>
                                    {{ __('Male') }}</option>
                                <option value="female" {{ $shop->user?->gender == 'female' ? 'selected' : '' }}>
                                    {{ __('Female') }}</option>
                                <option value="other" {{ $shop->user?->gender == 'other' ? 'selected' : '' }}>
                                    {{ __('Other') }}</option>
                            </x-select>
                        </div>

                        <div class="mt-3">
                            <x-input type="email" name="email" label="Email" required="true"
                                placeholder="Enter Email Address" :value="$shop->user?->email" :readonly="$shop->user_id != auth()->id() ? true : false" />
                        </div>

                    </div>
                    <div class="col-lg-5">
                        <div class="mt-3 mt-lg-5 d-flex align-items-center justify-content-center">
                            <div class="ratio1x1 mt-lg-5">
                                <img id="previewProfile" src="{{ $shop->user?->thumbnail ?? asset('default/default.jpg') }}"
                                    alt="" width="100%">
                            </div>
                        </div>
                        <div class="mt-3">
                            <x-file name="profile_photo" label="User profile (Ratio 1:1)" preview="previewProfile" />
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!--######## Shop Information ##########-->
        <div class="card mt-4 mb-4">
            <div class="card-body">

                <div class="d-flex gap-2 border-bottom pb-2">
                    <i class="fa-solid fa-user"></i>
                    <h5>
                        {{ __('Shop Information') }}
                    </h5>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <x-input type="text" name="shop_name" label="Shop Name" placeholder="Shop Name" :value="$shop->name"
                            required="true" />
                    </div>

                    <div class="col-md-6">
                        <div>
                            <x-input type="text" name="min_order_amount" label="Minimum Order Amount" :value="$shop->min_order_amount"
                                onlyNumber="true" />
                        </div>
                    </div>

                    {{-- Address matching Image 3 --}}
                    <div class="col-12 mt-3">
                        <label class="form-label fw-semibold">{{ __('Address') }} <span class="text-danger">*</span></label>
                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="{{ __('Address') }}" required>{{ old('address', $shop->address) }}</textarea>
                        @error('address')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Country --}}
                    <div class="col-md-4 mt-3">
                        <x-select label="Country" name="country_id" required="true">
                            @if($shop->country)
                                <option value="{{ $shop->country->id }}" selected>
                                    {{ $shop->country->name }}
                                </option>
                            @endif
                        </x-select>
                    </div>

                    {{-- State --}}
                    <div class="col-md-4 mt-3">
                        <x-select label="State" name="state_id" required="true">
                            @if($shop->state)
                                <option value="{{ $shop->state->id }}" selected>
                                    {{ $shop->state->name }}
                                </option>
                            @else
                                <option value="">{{ __('Select a state') }}</option>
                            @endif
                        </x-select>
                    </div>

                    {{-- City --}}
                    <div class="col-md-4 mt-3">
                        <x-select label="City" name="city_id" required="true">
                            @if($shop->city)
                                <option value="{{ $shop->city->id }}" selected>
                                    {{ $shop->city->name }}
                                </option>
                            @else
                                <option value="">{{ __('Select a city') }}</option>
                            @endif
                        </x-select>
                    </div>

                    <div class="col-md-4 mt-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <div class="ratio1x1">
                                <img src="{{ $shop->logo ?? asset('default/default.jpg') }}" id="previewShopLogo"
                                    alt="" width="100%">
                            </div>
                        </div>
                        <x-file name="shop_logo" label="Shop logo(Ratio 1:1)" preview="previewShopLogo" />
                    </div>

                    <div class="col-md-4 mt-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <div class="ratio4x1">
                                <img src="{{ $shop->banner ?? asset('default/default.jpg') }}" id="shopBanner"
                                    alt="" width="100%">
                            </div>
                        </div>
                        <x-file name="shop_banner" label="Shop banner Ratio 4:1 (2000 x 500 px)" preview="shopBanner" />
                    </div>

                    <div class="col-md-4 mt-3">
                        <div>
                            <x-input type="text" name="opening_time" label="Opening Time" :value="Carbon\Carbon::parse($shop->opening_time)->format('H:i')"
                                id="timepicker" required="true" />
                        </div>

                        <div class="mt-3">
                            <x-input type="text" name="closing_time" label="Closing Time" :value="Carbon\Carbon::parse($shop->closing_time)->format('H:i')"
                                id="timepicker2" required="true" />
                        </div>

                        <div class="mt-3">
                            <x-input type="text" name="estimated_delivery_time" label="Estimated Delivery"
                                :value="$shop->estimated_delivery_time" required="true" />
                        </div>

                    </div>

                    <div class="col-lg-4 mt-3">
                        <div>
                            <x-input type="text" name="prefix" label="Order ID Prefix" :value="$shop->prefix"
                                required="true" />
                        </div>
                    </div>

                </div>

                <div class="mt-3">
                    <label for="">
                        {{ __('Description') }}
                    </label>
                    <textarea name="description" class="form-control" id="description" rows="2" placeholder="Enter Description" onkeyup="checkDescription()">{{ old('description') ?? $shop->description }}</textarea>
                    @error('description')
                        <p class="text text-danger m-0">{{ $message }}</p>
                    @enderror
                    <p class="text text-danger m-0" id="descriptionError"></p>
                </div>

                <!-- Google Gemini AI Engine Card (Matches Exact Design) -->
                <div class="card border rounded-3 mt-4 mb-3" style="background-color: #f8f9ff; border-color: #e2e8f0 !important;">
                    <div class="card-body p-4">
                        
                        <!-- Header Row -->
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded-circle text-white shadow-xs" style="width: 32px; height: 32px; background: #6366f1; font-size: 15px;">
                                    <i class="bi bi-stars"></i>
                                </div>
                                <h5 class="fw-bold text-dark m-0" style="font-size: 16px; letter-spacing: -0.2px;">
                                    {{ __('Google Gemini AI Engine') }}
                                </h5>
                            </div>
                            <span class="badge" style="background: #ede9fe; color: #6366f1; font-weight: 600; font-size: 11.5px; border-radius: 20px; padding: 5px 12px;">
                                {{ __('Multi-Tenant Key') }}
                            </span>
                        </div>

                        <!-- Subtitle Description -->
                        <p class="text-secondary small mt-2 mb-3" style="color: #64748b; font-size: 12px; line-height: 1.5;">
                            {{ __('Configure dedicated Google Gemini API Key and AI Model for') }} <strong>{{ __('Ask GAS-AI Executive Copilot') }}</strong> {{ __('and real-time predictive analytics.') }}
                        </p>

                        <!-- 2-Column Form Fields -->
                        <div class="row g-3 align-items-start">
                            
                            <!-- Left: COMPANY GEMINI API KEY -->
                            <div class="col-lg-6">
                                <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px; color: #475569;">
                                    {{ __('COMPANY GEMINI API KEY') }}
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                        name="gemini_api_key" 
                                        id="gemini_api_key" 
                                        class="form-control font-monospace bg-white" 
                                        placeholder="AIzaSy... (Paste your Google Gemini API Key here)" 
                                        value="{{ old('gemini_api_key', $shop->gemini_api_key) }}"
                                        style="font-size: 13px;">
                                    <button type="button" class="btn btn-outline-secondary border-secondary-subtle bg-white px-2.5" id="btn_toggle_gemini_key" title="Toggle visibility">
                                        <i class="bi bi-eye-slash" id="toggle_gemini_icon" style="font-size: 14px; color: #64748b;"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary d-flex align-items-center gap-1.5 px-3 fw-semibold shadow-xs" id="btn_test_gemini_key" style="background: #6366f1; border-color: #6366f1; font-size: 13px;">
                                        <i class="bi bi-patch-check" id="test_gemini_icon"></i>
                                        <span id="test_gemini_text">{{ __('Test') }}</span>
                                        <div class="spinner-border spinner-border-sm text-light d-none" id="test_gemini_spinner" role="status"></div>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px; color: #64748b;">
                                    {{ __('Leave empty to use system default fallback key.') }}
                                </small>
                                @error('gemini_api_key')
                                    <p class="text text-danger m-0 small mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Right: GEMINI AI MODEL -->
                            <div class="col-lg-6">
                                <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px; color: #475569;">
                                    {{ __('GEMINI AI MODEL') }}
                                </label>
                                <select name="gemini_model" id="gemini_model" class="form-select bg-white" style="font-size: 13px; height: 38px;">
                                    <option value="gemini-2.5-flash" {{ old('gemini_model', $shop->gemini_model ?? 'gemini-2.5-flash') == 'gemini-2.5-flash' ? 'selected' : '' }}>
                                        gemini-2.5-flash (Recommended)
                                    </option>
                                    <option value="gemini-1.5-flash" {{ old('gemini_model', $shop->gemini_model) == 'gemini-1.5-flash' ? 'selected' : '' }}>
                                        gemini-1.5-flash
                                    </option>
                                    <option value="gemini-1.5-pro" {{ old('gemini_model', $shop->gemini_model) == 'gemini-1.5-pro' ? 'selected' : '' }}>
                                        gemini-1.5-pro (High Reasoning)
                                    </option>
                                    <option value="gemini-2.0-flash" {{ old('gemini_model', $shop->gemini_model) == 'gemini-2.0-flash' ? 'selected' : '' }}>
                                        gemini-2.0-flash
                                    </option>
                                </select>
                                <small class="text-muted d-block mt-1" style="font-size: 11px; color: #64748b;">
                                    {{ __('Select the AI model for content generation and copilot capabilities.') }}
                                </small>
                                @error('gemini_model')
                                    <p class="text text-danger m-0 small mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        <!-- Live Test Feedback Alert (Dynamic) -->
                        <div class="alert mt-3 py-2 px-3 border-0 rounded-3 d-none" id="gemini_test_feedback" style="font-size: 12.5px;"></div>

                    </div>
                </div>

                <div class="border-top mt-4 mb-4"></div>

                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary py-2 px-5">
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#timepicker').timepicker({
                'timeFormat': 'H:i:s'
            });

            $('#timepicker2').timepicker({
                'timeFormat': 'H:i:s'
            });

            // Country Select2
            $('#country_id').select2({
                placeholder: 'Select a country',
                ajax: {
                    url: '{{ route('shop.country.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        var seen = {};
                        var results = [];
                        $.each(data, function (i, country) {
                            var nameKey = country.name ? country.name.toLowerCase().trim() : country.id;
                            if (!seen[nameKey]) {
                                seen[nameKey] = true;
                                results.push({ id: country.id, text: country.name });
                            }
                        });
                        return { results: results };
                    },
                    cache: true
                }
            });

            var selectedCountry = "{{ $shop->country_id ?? '' }}";
            var selectedState   = "{{ $shop->state_id ?? '' }}";
            var selectedStateName = "{{ $shop->state->name ?? '' }}";
            var selectedCity    = "{{ $shop->city_id ?? '' }}";
            var selectedCityName  = "{{ $shop->city->name ?? '' }}";

            // If no country is set yet, default to India
            if (!selectedCountry) {
                $.ajax({
                    url: '{{ route('shop.country.search') }}',
                    dataType: 'json',
                    data: { q: 'India' },
                    success: function (data) {
                        var india = data.find(c => c.name.toLowerCase() === 'india');
                        if (india) {
                            var option = new Option(india.name, india.id, true, true);
                            $('#country_id').append(option).trigger('change');
                        }
                    }
                });
            }

            // Country -> States
            $('#country_id').on('change', function () {
                var country_id = this.value;

                $("#state_id").html('<option value="">Loading states...</option>').prop('disabled', true);
                $("#city_id").html('<option value="">Select a city</option>').prop('disabled', true);

                if (country_id) {
                    $.ajax({
                        url: "{{ route('shop.get.states') }}",
                        type: "POST",
                        data: {
                            country_id: country_id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            $("#state_id").html('<option value="">Select a state</option>');
                            var seen = {};
                            $.each(res, function (key, value) {
                                var nameKey = value.name ? value.name.toLowerCase().trim() : value.id;
                                if (!seen[nameKey]) {
                                    seen[nameKey] = true;
                                    var isSelected = (value.id == selectedState || (selectedStateName && value.name.toLowerCase() === selectedStateName.toLowerCase())) ? 'selected' : '';
                                    $("#state_id").append('<option value="'+value.id+'" '+isSelected+'>'+value.name+'</option>');
                                }
                            });
                            $("#state_id").prop('disabled', false).trigger('change');
                        },
                        error: function() {
                            $("#state_id").html('<option value="">Error loading states</option>');
                        }
                    });
                } else {
                    $("#state_id").html('<option value="">Select a state</option>').prop('disabled', true);
                    $("#city_id").html('<option value="">Select a city</option>').prop('disabled', true);
                }
            });

            // State -> Cities
            $('#state_id').on('change', function () {
                var state_id = this.value;

                $("#city_id").html('<option value="">Loading cities...</option>').prop('disabled', true);

                if (state_id) {
                    $.ajax({
                        url: "{{ route('shop.get.cities') }}",
                        type: "POST",
                        data: {
                            state_id: state_id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            $("#city_id").html('<option value="">Select a city</option>');
                            var seen = {};
                            $.each(res, function (key, value) {
                                var nameKey = value.name ? value.name.toLowerCase().trim() : value.id;
                                if (!seen[nameKey]) {
                                    seen[nameKey] = true;
                                    var isSelected = (value.id == selectedCity || (selectedCityName && value.name.toLowerCase() === selectedCityName.toLowerCase())) ? 'selected' : '';
                                    $("#city_id").append('<option value="'+value.id+'" '+isSelected+'>'+value.name+'</option>');
                                }
                            });
                            $("#city_id").prop('disabled', false);
                        },
                        error: function() {
                            $("#city_id").html('<option value="">Error loading cities</option>');
                        }
                    });
                } else {
                    $("#city_id").html('<option value="">Select a city</option>').prop('disabled', true);
                }
            });

            if (selectedCountry) {
                $('#country_id').val(selectedCountry).trigger('change');
            }

            $('#btn_toggle_gemini_key').on('click', function() {
                var input = $('#gemini_api_key');
                var icon = $('#toggle_gemini_icon');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                }
            });

            // Live Gemini API & Model Test
            $('#btn_test_gemini_key').on('click', function(e) {
                e.preventDefault();
                var apiKey = $('#gemini_api_key').val().trim();
                var model = $('#gemini_model').val();
                var btn = $(this);
                var spinner = $('#test_gemini_spinner');
                var icon = $('#test_gemini_icon');
                var text = $('#test_gemini_text');
                var alertBox = $('#gemini_test_feedback');

                alertBox.addClass('d-none').removeClass('alert-success alert-danger alert-warning bg-success-subtle bg-danger-subtle bg-warning-subtle text-success-emphasis text-danger text-dark');

                if (!apiKey) {
                    alertBox.removeClass('d-none').addClass('alert-warning bg-warning-subtle text-dark border border-warning-subtle')
                        .html('<i class="bi bi-info-circle-fill text-warning me-1.5"></i> {{ __('Please enter or paste your Google Gemini API Key in the field above first, then click Test.') }}');
                    $('#gemini_api_key').focus();
                    return;
                }

                spinner.removeClass('d-none');
                icon.addClass('d-none');
                btn.prop('disabled', true);
                text.text("{{ __('Testing...') }}");

                $.ajax({
                    url: "{{ route('shop.profile.test-gemini-key') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        api_key: apiKey,
                        model: model
                    },
                    dataType: "json",
                    timeout: 20000,
                    success: function(res) {
                        spinner.addClass('d-none');
                        icon.removeClass('d-none');
                        btn.prop('disabled', false);
                        text.text("{{ __('Test') }}");

                        if (res.success) {
                            alertBox.removeClass('d-none').addClass('alert-success bg-success-subtle text-success-emphasis border border-success-subtle')
                                .html('<i class="bi bi-check-circle-fill text-success me-1.5 fs-6"></i> <strong>{{ __('Key Verified:') }}</strong> ' + res.message);
                        } else {
                            alertBox.removeClass('d-none').addClass('alert-danger bg-danger-subtle text-danger border border-danger-subtle')
                                .html('<i class="bi bi-exclamation-octagon-fill text-danger me-1.5 fs-6"></i> <strong>{{ __('Validation Error:') }}</strong> ' + (res.message || "{{ __('Connection test failed.') }}"));
                        }
                    },
                    error: function(xhr) {
                        spinner.addClass('d-none');
                        icon.removeClass('d-none');
                        btn.prop('disabled', false);
                        text.text("{{ __('Test') }}");

                        var err = "{{ __('Connection test failed. Please check your internet connection or API key.') }}";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            err = xhr.responseJSON.message;
                        }
                        alertBox.removeClass('d-none').addClass('alert-danger bg-danger-subtle text-danger border border-danger-subtle')
                            .html('<i class="bi bi-exclamation-octagon-fill text-danger me-1.5 fs-6"></i> ' + err);
                    }
                });
            });
        });
        function checkDescription() {
            if (document.getElementById('description').value.length > 200) {
                document.getElementById('descriptionError').innerHTML =
                    'Description must be less than or equal to 220 characters';
            } else {
                document.getElementById('descriptionError').innerHTML = '';
            }
        }
    </script>
@endpush
