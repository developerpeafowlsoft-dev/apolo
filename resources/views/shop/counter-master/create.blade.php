@extends('layouts.app')
@section('header-title', __('Counter Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Add Counter Master')}}
        </h4>
    </div>

    <form id="formData" action="{{route('shop.counterMaster.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- Account Information Card 1 --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span>
                                    {{ __('Code') }}
                                    <span class="text-danger">*</span>
                                </span>
                            </div>
                        </label>
                        <div class="input-group flex-nowrap">
                            <input type="text" class="form-control disabledCls @error('code') is-invalid @enderror" name="code" placeholder="Code" id="shortCode" value="{{ old('code') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);" required="true" readonly>

                            <button class="btn btn-secondary" type="button" id="generateShortCode" onclick="generateCode()" data-toggle="tooltip" data-placement="top" title="Generate Short Code">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        @error('code')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Counter Name') }} <span class="text-danger">*</span></span>
                        </label>

                        <input type="text" id="counter_name" name="counter_name"
                               placeholder="Enter Counter Name"
                               class="form-control @error('counter_name') is-invalid @enderror" value="{{ old('counter_name') }}"
                               required="true" maxlength="170"
                        />
                        @error('counter_name')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Counter Short Name') }} <span class="text-danger">*</span></span>
                        </label>

                        <input type="text" id="counter_short_name" name="counter_short_name"
                               placeholder="Enter Counter Short Name"
                               class="form-control @error('counter_short_name') is-invalid @enderror" value="{{ old('counter_short_name') }}"
                               required="true" maxlength="21"
                        />
                        @error('counter_short_name')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Floor / Location') }}</span>
                        </label>

                        <input type="text" id="floor" name="floor"
                               placeholder="e.g. Ground Floor, First Floor"
                               class="form-control @error('floor') is-invalid @enderror" value="{{ old('floor') }}"
                               maxlength="100"
                        />
                        @error('floor')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Voucher Prefix') }} <span class="text-danger">*</span></span>
                        </label>

                        <input type="text" id="voucher_prefix" name="voucher_prefix"
                               placeholder="Enter Voucher Prefix"
                               class="form-control @error('voucher_prefix') is-invalid @enderror" value="{{ old('voucher_prefix') }}"
                               required="true" maxlength="5"
                        />
                        @error('voucher_prefix')
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

        $("#counter_name").on('keyup',function (){
            this.value = this.value.toUpperCase();
        })

        $("#counter_short_name").on('keyup',function (){
            this.value = this.value.toUpperCase();
        })

        $("#voucher_prefix").on('keyup',function (){
            this.value = this.value.toUpperCase();
        });

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