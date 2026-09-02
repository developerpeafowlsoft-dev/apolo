@extends('layouts.app')
@section('header-title', __('TDS Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Add TDS Master')}}
        </h4>
    </div>

    <form id="formData" action="{{route('shop.tdsMaster.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card mt-4">
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span>
                                    {{ __('TDS Code') }}
                                    <span class="text-danger">*</span>
                                </span>
                            </div>
                        </label>
                        <div class="input-group flex-nowrap">
                            <input type="text" class="form-control disabledCls @error('tds_code') is-invalid @enderror" name="tds_code" placeholder="TDS Code" id="shortCode" value="{{ old('tds_code') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);" required="true" readonly>

                            <button class="btn btn-secondary" type="button" id="generateShortCode" onclick="generateCode()" data-toggle="tooltip" data-placement="top" title="Generate Short Code">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        @error('tds_code')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label for="tds_description" class="form-label">
                            {{ __('TDS Description') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="tds_description" class="form-control @error('tds_description') is-invalid @enderror" rows="1" placeholder="Enter TDS Description" required="true">{{ old('tds_description') }}</textarea>
                        @error('tds_description')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-select label="TDS Payable Account" name="tds_payable_id" required="true">
                            <option value="">{{ __('-- Select a TDS Payable Account --') }}</option>

                            @foreach ($accountMasters as $accountMaster)
                                <option value="{{ $accountMaster->id }}" {{ old('tds_payable_id') == $accountMaster->id ? 'selected' : '' }}>
                                    {{ $accountMaster->accountshortcode }} |
                                    {{ $accountMaster->accountName }} |
                                    {{ $accountMaster->account?->name }} ({{ $accountMaster->account?->code }})
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-select label="TDS Receivable Account" name="tds_receivable_id" required="true">
                            <option value="">{{ __('-- Select a TDS Receivable Account --') }}</option>

                            @foreach ($accountMasters as $accountMaster)
                                <option value="{{ $accountMaster->id }}" {{ old('tds_receivable_id') == $accountMaster->id ? 'selected' : '' }}>
                                    {{ $accountMaster->accountshortcode }} |
                                    {{ $accountMaster->accountName }} |
                                    {{ $accountMaster->account?->name }} ({{ $accountMaster->account?->code }})
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-input type="date" name="from_date" label="From Date" value="{{ old('from_date') }}" required/>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-input type="date" name="to_date" label="To Date" value="{{ old('to_date') }}" required/>
                    </div>

                    <div class="col-md-6 col-lg-2">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('TDS Percentage') }} <span class="text-danger">*</span></span>
                        </label>

                        <input type="text" id="tds_percentage" name="tds_percentage"
                               placeholder="Enter TDS Percentage"
                               class="form-control @error('tds_percentage') is-invalid @enderror" value="{{ old('tds_percentage','0.00') }}"
                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                        />
                        @error('tds_percentage')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-2">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('TDS Limit') }} <span class="text-danger">*</span></span>
                        </label>

                        <input type="text" id="tds_limit" name="tds_limit"
                               placeholder="Enter TDS Limit"
                               class="form-control @error('tds_limit') is-invalid @enderror" value="{{ old('tds_limit','0') }}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                        />
                        @error('tds_limit')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-2">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('TDS Single Trans Limit') }} <span class="text-danger">*</span></span>
                        </label>

                        <input type="text" id="tds_single_trans_limit" name="tds_single_trans_limit"
                               placeholder="Enter TDS Single Trans Limit"
                               class="form-control @error('tds_single_trans_limit') is-invalid @enderror" value="{{ old('tds_single_trans_limit','0') }}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                        />
                        @error('tds_single_trans_limit')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>


                </div>
            </div>
        </div>
        <div class="d-flex gap-3 justify-content-end align-items-center my-3">
            <button type="reset" class="btn btn-outline-secondary rounded py-2">
                {{ __('Reset') }}
            </button>
            <button type="submit" class="btn btn-primary rounded py-2 px-5">
                {{ __('Submit') }}
            </button>
        </div>
    </form>

@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/holdon/HoldOn.min.css') }}" type="text/css" />
@endpush
@push('scripts')
    <script src="{{ asset('assets/css/holdon/HoldOn.min.js') }}"></script>
    <script>

        // Hold On
        function showCustomLoader(position = 'center') {
            const positions = {
                'center': 'translate(-50%, -50%)',
                'top': 'translate(-50%, 0)',
                'bottom': 'translate(-50%, -100%)',
                'left': 'translate(0, -50%)',
                'right': 'translate(-100%, -50%)',
            };

            HoldOn.open({
                theme: "custom",
                message: 'Please wait...',
                content: `
                <div style="
                    position: absolute;
                    top: ${position === 'center' ? '50%' : position === 'top' ? '10%' : position === 'bottom' ? '90%' : '50%'};
                    left: ${position === 'center' ? '50%' : position === 'left' ? '10%' : position === 'right' ? '90%' : '50%'};
                    transform: ${positions[position]};
                    z-index: 9999;
                ">
                    <img src="{{ asset('assets/images/APOLO_GIF.gif') }}" alt="Loading..." style="width:80px; height:auto;" />
                </div>
            `,
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Form submit loader
            const form = document.getElementById('formData');
            if (form) {
                form.addEventListener('submit', () => showCustomLoader('center'));
            }
        });

        const code = document.getElementById('shortCode');
        code.value = Math.floor(Math.random() * 9000) + 1000;

        const generateCode = () => {
            const code = document.getElementById('shortCode');
            code.value = Math.floor(Math.random() * 9000) + 1000;
        }

    </script>

@endpush