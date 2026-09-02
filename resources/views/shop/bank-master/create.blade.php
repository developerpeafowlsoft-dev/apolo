@extends('layouts.app')
@section('header-title', __('Bank Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Add Bank Master')}}
        </h4>
    </div>
    <form id="formData" action="{{route('shop.bankMaster.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- Account Information Card 1 --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <x-input type="text" name="bank_name" label="Bank Name" placeholder="Enter Bank Name" value="{{ old('bank_name') }}" required="true"/>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Short Name') }} <span class="text-danger">*</span></span>
                        </label>

                        <input type="text" id="short_name" name="short_name"
                               placeholder="Enter Short Name"
                               class="form-control @error('short_name') is-invalid @enderror" value="{{ old('short_name') }}"
                               required="true" maxlength="10"
                        />
                        @error('short_name')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-select label="Account Group" name="account_group_id" required="true">
                            @foreach ($accounts_groups as $accounts_group)
                                <option value="{{ $accounts_group->id }}" {{ old('account_group_id') == $accounts_group->id ? 'selected' : '' }}>
                                    {{ $accounts_group->name }} ({{ $accounts_group->code }})
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Account No.') }} <span class="text-danger">*</span></span>
                        </label>

                        <input type="text" id="bank_ac_no" name="bank_ac_no"
                               placeholder="Enter Account No."
                               class="form-control @error('bank_ac_no') is-invalid @enderror" value="{{ old('bank_ac_no') }}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 20);"
                               required="true" maxlength="20"
                        />
                        @error('bank_ac_no')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Branch Name') }} <span class="text-danger">*</span> </span>
                        </label>

                        <input type="text" id="bank_branch" name="bank_branch"
                               placeholder="Enter Branch Name"
                               class="form-control @error('bank_branch') is-invalid @enderror" value="{{ old('bank_branch') }}"
                               maxlength="170" required="true"/>
                        @error('bank_branch')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">

                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Swift Code') }} <span class="text-danger">*</span> </span>
                        </label>

                        <input type="text" id="bank_swift_code" name="bank_swift_code"
                               placeholder="Enter Swift Code"
                               pattern="^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$"
                               title="Enter valid SWIFT Code (8 or 11 characters, e.g., SBININBBXXX)"
                               class="form-control @error('bank_swift_code') is-invalid @enderror" value="{{ old('bank_swift_code') }}"
                               maxlength="11" required="true"/>
                        @error('bank_swift_code')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">

                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('IFSC Code') }} <span class="text-danger">*</span> </span>
                        </label>

                        <input type="text" id="bank_ifsc_code" name="bank_ifsc_code"
                               placeholder="Enter IFSC Code"
                               class="form-control @error('bank_ifsc_code') is-invalid @enderror" value="{{ old('bank_ifsc_code') }}"
                               pattern="^[A-Z]{4}0[A-Z0-9]{6}$"
                               title="Enter valid IFSC Code (e.g., SBIN0001234)"
                               maxlength="11" required="true"/>
                        @error('bank_ifsc_code')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label for="bank_address" class="form-label">
                            {{ __('Address') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="bank_address" class="form-control @error('bank_address') is-invalid @enderror" rows="1" placeholder="Enter Address" required="true">{{ old('bank_address') }}</textarea>
                        @error('bank_address')
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

        $("#short_name").on('keyup',function (){
            this.value = this.value.toUpperCase();
        });

        $('#bank_ifsc_code').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        $('#bank_swift_code').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        $('#bank_branch').on('keyup', function() {
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

    </script>

@endpush