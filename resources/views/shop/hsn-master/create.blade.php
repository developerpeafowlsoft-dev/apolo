@extends('layouts.app')
@section('header-title', __('HSN Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Add HSN Master')}}
        </h4>
    </div>

    <form id="formData" action="{{route('shop.hsnMaster.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card mt-4">
            <div class="card-body">

                {{-- 🔹 Basic Info --}}
                <div class="row g-3">

                    <div class="col-md-6 col-lg-4">
                        <label class="form-label">
                            {{ __('HSN Code') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('hsn_code') is-invalid @enderror"
                               name="hsn_code"
                               value="{{ old('hsn_code') }}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                               required>
                        @error('hsn_code')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror

                    </div>

                    <div class="col-md-6 col-lg-4">
                        <label class="form-label">
                            {{ __('Description') }} <span class="text-danger">*</span>
                        </label>
                        <textarea name="hsn_description"
                                  class="form-control @error('hsn_description') is-invalid @enderror"
                                  rows="1" required>{{ old('hsn_description') }}</textarea>
                        @error('hsn_description')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <hr>

                {{-- 🔥 Multiple Slabs Section --}}
                <div id="hsnRows">

                    {{-- Row Start --}}
                    <div class="row g-3 hsn-row mt-2">

                        {{-- Tax --}}
                        <div class="col-md-6 col-lg-2">
                            <x-select label="Tax Type" name="vat_tax_id[]" required="true">
                                @foreach ($taxs as $tax)
                                    <option value="{{ $tax->id }}">
                                        {{ $tax->name }} {{ $tax->percentage }}%
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="col-3">
                            <div class="row">
                                {{-- From Sales --}}
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label">
                                        {{ __('From Sales Rt') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="from_sales_rate[]"
                                           class="form-control"
                                           value="0.00">
                                    @error('from_sales_rate.*')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- To Sales --}}
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label">
                                        {{ __('To Sales Rt') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="to_sales_rate[]"
                                           class="form-control"
                                           value="0.00">
                                    @error('to_sales_rate.*')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="row">
                                {{-- From Purchase --}}
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label">
                                        {{ __('From Purc Rt') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="from_purchase_rate[]"
                                           class="form-control"
                                           value="0.00">
                                    @error('from_purchase_rate.*')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- To Purchase --}}
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label">
                                        {{ __('To Purc Rt') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="to_purchase_rate[]"
                                           class="form-control"
                                           value="0.00">
                                    @error('to_purchase_rate.*')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-3">
                            <div class="row">
                                {{-- From Date --}}
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label">{{ __('From') }}</label>
                                    <input type="date" name="from_date[]" value="{{now()->format('Y-m-d')}}" class="form-control">
                                </div>

                                {{-- To Date --}}
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label">{{ __('To') }}</label>
                                    <input type="date" name="to_date[]" class="form-control">
                                </div>

                            </div>
                        </div>

                        {{-- Remove --}}
                        <div class="col-md-6 col-lg-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger removeRow">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>

                    </div>
                    {{-- Row End --}}

                </div>

                {{-- Add Button --}}
                <div class="mt-3">
                    <button type="button" id="addRow" class="btn btn-success">
                        <i class="fa fa-plus"></i> Add
                    </button>
                </div>

            </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex gap-3 justify-content-end align-items-center my-3">
            <button type="reset" class="btn btn-outline-secondary rounded py-2">
                {{ __('Reset') }}
            </button>
            <button class="btn btn-primary rounded py-2 px-5">
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

    </script>

    <script>
        $(document).ready(function () {

            $('#addRow').click(function () {
                let today = new Date().toISOString().split('T')[0];

                let newRow = `
        <div class="row g-3 hsn-row mt-2">

            <input type="hidden" name="row_id[]" value="new">

            <div class="col-md-2">
                <select name="vat_tax_id[]" class="form-control select2">

                    @foreach($taxs as $tax)
                <option value="{{ $tax->id }}">
                            {{ $tax->name }} {{ $tax->percentage }}%
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="from_sales_rate[]" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <input type="text" name="to_sales_rate[]" class="form-control">
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="from_purchase_rate[]" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="to_purchase_rate[]" class="form-control">
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="row">
                    <div class="col-md-6">
                        <input type="date" name="from_date[]" value="${today}" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <input type="date" name="to_date[]" class="form-control">
                    </div>
                </div>
            </div>

            <div class="col-md-1 d-flex align-items-start">
                <button type="button" class="btn btn-danger removeRow">X</button>
            </div>

        </div>`;

                $('#hsnRows').append(newRow);

                // 🔥 re-init select2
                $('.select2').select2();
            });

            // remove row
            $(document).on('click', '.removeRow', function () {
                if ($('.hsn-row').length > 1) {
                    $(this).closest('.hsn-row').remove();
                } else {
                    toastr.warning('At least one row required');
                }
            });

        });
    </script>

    <script>
        $(document).ready(function () {

            $('#formData').on('submit', function (e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = new FormData(this);

                // 🔥 clear old errors
                $('.text-danger').html('');
                $('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function (res) {

                        toastr.success(res.message);

                        // optional redirect
                        window.location.href = "{{ route('shop.hsnMaster.index') }}";
                    },

                    error: function (xhr) {

                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function (key, value) {

                                // 🔥 array field (example: to_purchase_rate.1)
                                if (key.includes('.')) {

                                    let parts = key.split('.');
                                    let field = parts[0];
                                    let index = parts[1];

                                    let input = $('[name="'+field+'[]"]').eq(index);

                                    input.addClass('is-invalid');

                                    input.after('<span class="text-danger">'+value[0]+'</span>');
                                } else {

                                    // 🔥 normal field
                                    let input = $('[name="'+key+'"]');
                                    input.addClass('is-invalid');

                                    input.after('<span class="text-danger">'+value[0]+'</span>');
                                }
                            });
                        }
                    }
                });
            });

        });
    </script>

@endpush