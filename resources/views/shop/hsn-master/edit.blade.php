@extends('layouts.app')
@section('header-title', __('HSN Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Update HSN Master')}}
        </h4>
    </div>

    <form id="formData" action="{{route('shop.hsnMaster.update',$hsnMaster->id)}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card mt-4">
            <div class="card-body">

                {{-- 🔹 Basic Info --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>HSN Code *</label>
                        <input type="text" name="hsn_code"
                               value="{{ $hsnMaster->hsn_code }}"
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Description *</label>
                        <input type="text" name="hsn_description"
                               value="{{ $hsnMaster->hsn_description }}"
                               class="form-control">
                    </div>
                </div>

                <hr>

                {{-- 🔥 Rows --}}
                <div id="hsnRows">



                    {{-- 🔹 Sub Rows --}}
                    @foreach($hsnMaster->subHsn as $sub)
                        <div class="row g-3 hsn-row mt-2">

                            <input type="hidden" name="row_id[]" value="{{ $sub->id }}">

                            <div class="col-md-2">
                                <select name="vat_tax_id[]" class="form-control select2">
                                    @foreach($taxs as $tax)
                                        <option value="{{ $tax->id }}"
                                                {{ $sub->vat_tax_id == $tax->id ? 'selected' : '' }}>
                                            {{ $tax->name }} {{ $tax->percentage }}%
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="from_sales_rate[]"
                                               value="{{ $sub->from_sales_rate }}"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <input type="text" name="to_sales_rate[]"
                                               value="{{ $sub->to_sales_rate }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="from_purchase_rate[]"
                                               value="{{ $sub->from_purchase_rate }}"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <input type="text" name="to_purchase_rate[]"
                                               value="{{ $sub->to_purchase_rate }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="row">

                                    <div class="col-md-6">
                                        <input type="date" name="from_date[]"
                                               value="{{ $sub->from_date }}"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <input type="date" name="to_date[]"
                                               value="{{ $sub->to_date }}"
                                               class="form-control">
                                    </div>

                                </div>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger removeRow">X</button>
                            </div>

                        </div>
                    @endforeach

                </div>

                <button type="button" id="addRow" class="btn btn-success mt-3">+ Add</button>

            </div>
        </div>

        <div class="text-end mt-3">
            <button class="btn btn-primary">Update</button>
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

            // 🔥 Add Row
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

            // 🔥 Remove Row
            $(document).on('click', '.removeRow', function () {

                let row = $(this).closest('.hsn-row');

                if ($('.hsn-row').length > 1) {
                    row.remove();
                } else {
                    alert('At least one row required');
                }
            });

        });
    </script>

    <script>
        $(document).ready(function () {

            // 🔥 form submit
            $('#formData').submit(function (e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                // 🔥 clear old errors
                $('.text-danger').remove();
                $('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,

                    success: function (res) {
                        toastr.success(res.message);
                        window.location.href = "{{ route('shop.hsnMaster.index') }}";
                    },

                    error: function (xhr) {

                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function (key, value) {

                                // 🔥 ARRAY FIELD (row wise)
                                if (key.includes('.')) {

                                    let parts = key.split('.');
                                    let field = parts[0];
                                    let index = parts[1];

                                    let input = $('[name="'+field+'[]"]').eq(index);

                                    input.addClass('is-invalid');
                                    input.after('<span class="text-danger">'+value[0]+'</span>');

                                } else {

                                    // 🔥 NORMAL FIELD
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