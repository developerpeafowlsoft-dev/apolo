@extends('layouts.app')
@section('header-title', __('Design Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Design Master')}}
        </h4>
        <div>
            <button type="button" id="create-modal-btn" class="btn py-2 btn-primary">
                <i class="bi bi-patch-plus"></i>
                {{ __('Create New') }}
            </button>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <form id="searchForm"
                  class="d-flex align-items-center justify-content-end gap-3 mb-3 border-bottom pb-3 flex-wrap">
                {{--                <button type="button" class="btn btn-primary" data-bs-toggle="modal"--}}
                {{--                        data-bs-target="#filterItemMasterModal">--}}
                {{--                    {{ __('Filter') }}--}}
                {{--                </button>--}}

                <div class="input-group" style="max-width: 400px">
                    <input type="text" name="search" class="form-control"
                           placeholder="{{ __('Search by design number, item name, or account.') }}"
                           value="{{ request('search') }}">
                    {{--                    <button type="submit" class="input-group-text btn btn-primary">--}}
                    {{--                        <i class="fa fa-search"></i> {{ __('Search') }}--}}
                    {{--                    </button>--}}
                </div>
            </form>

            <div class="row">
                <div class="col-12" id="designMasterList">
                    @include('shop.design-master.partials.design-master-table', ['designMasters' => $designMasters])
                </div>
            </div>
        </div>
    </div>

    @include('shop.components-modal.design-master-modal')

    {{-- Item Master Modal Short Key --}}
    @include('shop.components-modal.item-master-modal')

    <!-- Modal -->
    {{--    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">--}}
    {{--        <div class="modal-dialog">--}}
    {{--            <div class="modal-content">--}}
    {{--                <div class="modal-header">--}}
    {{--                    <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>--}}
    {{--                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
    {{--                </div>--}}
    {{--                <div class="modal-body">--}}
    {{--                    ...--}}
    {{--                </div>--}}
    {{--                <div class="modal-footer">--}}
    {{--                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>--}}
    {{--                    <button type="button" class="btn btn-primary">Save changes</button>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}

@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/holdon/HoldOn.min.css') }}" type="text/css"/>
    <style>
        #designMasterList table tr:not(:first-child):not(.ui-datepicker-calendar tr) {
            opacity: 1 !important;
            display: table-row !important;
            animation: none !important;
        }

        .box-title {
            background: #f1f5f9;
            padding: 6px 10px;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
        }

        .app-theme-dark .box-title {
            background: #2d2d2d;
            border-color: #2d2d2d;
        }

        #colorBox,
        #sizeBox {
            margin-top: 20px;
        }

        .boxName {
            font-size: 16px;
            margin-bottom: 0;
        }

        .extraPriceForm {
            padding: 4px 6px;
            min-height: 34px;
        }

        #selectedSizesTableBody tr:last-child td,
        #selectedColorsTableBody tr:last-child td {
            border: 0 !important;
        }
    </style>
@endpush
@push('scripts')
    <script>
        // $('#filterItemMasterModal').on('shown.bs.modal', function () {
        //     $('select').select2({
        //         dropdownParent: $('#filterItemMasterModal')
        //     });
        // });

        // $('#design-master-modal').on('shown.bs.modal', function () {
        //     $('select').select2({
        //         dropdownParent: $('#design-master-modal')
        //     });
        // });

        $(document).on('hidden.bs.modal', function () {
            if ($('.modal.show').length) {
                $('body').addClass('modal-open');
            }
        });

    </script>
    <script src="{{ asset('assets/scripts/shortKey.js') }}"></script>
    <script src="{{ asset('assets/css/holdon/HoldOn.min.js') }}"></script>

    @include('shop.components-modal.master-modal.design-master-modal-script')
    {{-- Short Modal Open Code--}}
    @include('shop.components-modal.master-modal.item-master-modal-script')

    <script>
        {{--function showCustomLoader(position = 'center') {--}}
        {{--    const positions = {--}}
        {{--        'center': 'translate(-50%, -50%)',--}}
        {{--        'top': 'translate(-50%, 0)',--}}
        {{--        'bottom': 'translate(-50%, -100%)',--}}
        {{--        'left': 'translate(0, -50%)',--}}
        {{--        'right': 'translate(-100%, -50%)',--}}
        {{--    };--}}

        {{--    HoldOn.open({--}}
        {{--        theme: "custom",--}}
        {{--        content: `--}}
        {{--        <div style="--}}
        {{--            position: absolute;--}}
        {{--            top: ${position === 'center' ? '50%' : position === 'top' ? '10%' : position === 'bottom' ? '90%' : '50%'};--}}
        {{--            left: ${position === 'center' ? '50%' : position === 'left' ? '10%' : position === 'right' ? '90%' : '50%'};--}}
        {{--            transform: ${positions[position]};--}}
        {{--            z-index: 9999;--}}
        {{--        ">--}}
        {{--            <img src="{{ asset('assets/images/APOLO_GIF.gif') }}" alt="Loading..." style="width:80px; height:auto;" />--}}
        {{--        </div>--}}
        {{--    `,--}}
        {{--    });--}}
        {{--}--}}

        {{--function modelDesignMasterDataLoad(callback = null) {--}}
        {{--    $('#design_id').val('');--}}
        {{--    $('#formDataDesignMaster')[0].reset();--}}
        {{--    $(".errorSpan").empty();--}}

        {{--    $("#itemname").empty().append('<option value="">{{ __("Select Item Name") }}</option>');--}}
        {{--    $("#account_master").empty().append('<option value="">{{ __("Select Account Master") }}</option>');--}}

        {{--    $.ajax({--}}
        {{--        url: '{{ route('shop.designMaster.modalData') }}',--}}
        {{--        type: 'GET',--}}
        {{--        success: function (data) {--}}
        {{--            // data.itemMasters.forEach(function (itemMaster) {--}}
        {{--            //     $("#itemname").append(`<option value="${itemMaster.id}">${itemMaster.name}</option>`);--}}
        {{--            // });--}}
        {{--            //--}}
        {{--            // data.accountMasters.forEach(function (accountMaster) {--}}
        {{--            //     $("#account_master").append(`<option value="${accountMaster.id}" data-accName="${accountMaster.accountName}">${accountMaster.accountshortcode} - ${accountMaster.accountName}</option>`);--}}
        {{--            // });--}}


        {{--            $("#design-master-modal").modal("show");--}}
        {{--            HoldOn.close();--}}

        {{--            // 🔁 Call the callback if provided--}}
        {{--            if (typeof callback === 'function') {--}}
        {{--                callback();--}}
        {{--            }--}}
        {{--        },--}}
        {{--        error: function () {--}}
        {{--            toastr.error("Failed to load.");--}}
        {{--        }--}}
        {{--    });--}}


        {{--}--}}

        // Model Open
        $(document).on('click', '#create-modal-btn,.editData, #modelClose', function () {
            if ($(this).is('#create-modal-btn')) {
                showCustomLoader('center')
                $("#modalTitle").text("Add Design Master");
                $("#btnSubmit").text("Submit");
                modelDesignMasterDataLoad();

                // generateCode();

            } else if ($(this).is('.editData')) {
                var id = $(this).data('id');
                showCustomLoader('center');
                $("#modalTitle").text("Update Design Master");
                $("#btnSubmit").text("Update");

                if (id !== '') {

                    modelDesignMasterDataLoad(function () {

                        $.ajax({
                            url: `/shop/design-master/${id}/edit`,
                            type: 'GET',
                            success: function (res) {
                                console.log(res);

                                $('#design_id').val(id);

                                $('#design_number').val(res.designMaster.design_number);
                                $('#quantity').val(res.designMaster.quantity);
                                $('#minStock').val(res.designMaster.min_stock);
                                $('#maxStock').val(res.designMaster.max_stock);
                                $('#buy_price').val(res.designMaster.buy_price);
                                $('#price').val(res.designMaster.price);
                                $('#discount_percentage').val(res.designMaster.discount_percentage);
                                $('#mrp').val(res.designMaster.mrp);
                                $('#mark_up').val(res.designMaster.mark_up);
                                $('#mark_down').val(res.designMaster.mark_down);
                                $('#remark').val(res.designMaster.remark);
                                // $('#itemname').val(res.designMaster.account_master_id).trigger('change');

                                if (res.designMaster.product_id) {
                                    var itemData = {
                                        id: res.designMaster.product_id,
                                        text: res.designMaster.products.name,
                                    };

                                    // Agar option already exist nahi karta, to add kar do
                                    if ($("#itemname").find("option[value='" + itemData.id + "']").length === 0) {
                                        var newOption = new Option(itemData.text, itemData.id, true, true);
                                        $("#itemname").append(newOption).trigger('change'); // trigger Select2
                                    } else {
                                        $("#itemname").val(itemData.id).trigger('change');
                                    }
                                }


                                if (res.designMaster.account_master_id) {
                                    var accountData = {
                                        id: res.designMaster.account_master_id,
                                        text: res.designMaster.account_masters.accountshortcode + ' - ' + res.designMaster.account_masters.accountName,
                                        accName: res.designMaster.account_masters.accountName
                                    };

                                    // Agar option already exist nahi karta, to add kar do
                                    if ($("#account_master").find("option[value='" + accountData.id + "']").length === 0) {
                                        var newOption = new Option(accountData.text, accountData.id, true, true);
                                        $("#account_master").append(newOption).trigger('change'); // trigger Select2
                                    } else {
                                        $("#account_master").val(accountData.id).trigger('change');
                                    }

                                    $("#account_master_name").val(accountData.accName);
                                }


                                // You can set other values here too
                            },
                            error: function () {
                                toastr.error("Failed to load item data.");
                            }
                        });
                    });
                }
            } else {
                const modalIdName = $(this).data('modal-name');
                $("#" + modalIdName).modal("hide");
            }
        });

        {{--// Account Master On Change Name Get--}}
        {{--$(document).ready(function () {--}}
        {{--    $('#account_master').on('select2:select', function (e) {--}}
        {{--        const data = e.params.data;--}}
        {{--        $("#account_master_name").val(data.accName || '');--}}
        {{--    });--}}

        {{--    // Select clear hone par input blank--}}
        {{--    $('#account_master').on('select2:unselect', function (e) {--}}
        {{--        $("#account_master_name").val('');--}}
        {{--    });--}}
        {{--});--}}

        {{--// Design Code--}}
        {{--const generateCode = () => {--}}
        {{--    const code = document.getElementById('design_number');--}}
        {{--    code.value = Math.floor(Math.random() * 900000) + 100000;--}}
        {{--}--}}

        {{--// Upper Case--}}
        {{--$("#design_number").on('keyup', function () {--}}
        {{--    this.value = this.value.toUpperCase();--}}
        {{--});--}}


        {{--//Markup And Markdown Get--}}
        {{--$(document).on('input keyup', 'input[name="buy_price"], input[name="price"], input[name="discount_percentage"]', function () {--}}
        {{--    calculateAll();--}}
        {{--});--}}

        {{--function calculateAll() {--}}
        {{--    let buy_price = parseFloat($('input[name="buy_price"]').val()) || 0;--}}
        {{--    let price = parseFloat($('input[name="price"]').val()) || 0;--}}
        {{--    let discount_percentage = parseFloat($('input[name="discount_percentage"]').val()) || 0;--}}

        {{--    let mrp = 0;--}}
        {{--    let mark_up = 0;--}}
        {{--    let mark_down = 0;--}}

        {{--    if (discount_percentage > 0) {--}}
        {{--        let discount_value = (price * discount_percentage) / 100;--}}
        {{--        mrp = price - discount_value;--}}
        {{--    } else {--}}
        {{--        mrp = price;--}}
        {{--    }--}}

        {{--    $('input[name="mrp"]').val(mrp.toFixed(2));--}}

        {{--    if (buy_price > 0 && mrp > 0) {--}}
        {{--        mark_up = ((mrp - buy_price) / buy_price) * 100;--}}
        {{--        mark_down = ((mrp - buy_price) / mrp) * 100;--}}
        {{--    }--}}

        {{--    $('input[name="mark_up"]').val(mark_up.toFixed(2));--}}
        {{--    $('input[name="mark_down"]').val(mark_down.toFixed(2));--}}
        {{--}--}}

        {{--// Search Item Name Record Get--}}
        {{--$(document).on('shown.bs.modal', '#design-master-modal', function () {--}}

        {{--    // Item Name Search--}}
        {{--    $('#itemname').select2({--}}
        {{--        dropdownParent: $('#design-master-modal'),--}}
        {{--        placeholder: "Select Item Name",--}}
        {{--        allowClear: true,--}}
        {{--        ajax: {--}}
        {{--            url: '{{ route('shop.designMaster.modalData') }}',--}}
        {{--            dataType: 'json',--}}
        {{--            delay: 250,--}}
        {{--            data: function (params) {--}}
        {{--                return {searchItemName: params.term || ''};--}}
        {{--            },--}}
        {{--            processResults: function (data) {--}}
        {{--                console.log(data);--}}
        {{--                return {--}}
        {{--                    results: data.itemMasters.map(function (item) {--}}
        {{--                        return {id: item.id, text: item.name};--}}
        {{--                    })--}}
        {{--                };--}}
        {{--            },--}}
        {{--            cache: true--}}
        {{--        },--}}
        {{--        minimumInputLength: 0--}}
        {{--    });--}}

        {{--    // Account Code And Name Search--}}
        {{--    $('#account_master').select2({--}}
        {{--        dropdownParent: $('#design-master-modal'),--}}
        {{--        placeholder: "Select Account Master",--}}
        {{--        allowClear: true,--}}
        {{--        ajax: {--}}
        {{--            url: '{{ route('shop.designMaster.modalData') }}',--}}
        {{--            dataType: 'json',--}}
        {{--            delay: 250,--}}
        {{--            data: function (params) {--}}
        {{--                return {searchAccountMaster: params.term || ''};--}}
        {{--            },--}}
        {{--            processResults: function (data) {--}}
        {{--                return {--}}
        {{--                    results: data.accountMasters.map(function (accountMaster) {--}}
        {{--                        return {--}}
        {{--                            id: accountMaster.id,--}}
        {{--                            text: accountMaster.accountshortcode + ' - ' + accountMaster.accountName,--}}
        {{--                            accName: accountMaster.accountName--}}
        {{--                        };--}}
        {{--                    })--}}
        {{--                };--}}
        {{--            },--}}
        {{--            cache: true--}}
        {{--        },--}}
        {{--        minimumInputLength: 0--}}
        {{--    });--}}

        {{--});--}}

        {{--// Refresh Data--}}
        {{--function refreshDesignMasterList() {--}}
        {{--    $.ajax({--}}
        {{--        url: "{{ route('shop.designMaster.index') }}",--}}
        {{--        type: "GET",--}}
        {{--        success: function (data) {--}}
        {{--            console.log(data)--}}
        {{--            $("#designMasterList").html(data);--}}
        {{--        },--}}
        {{--        error: function () {--}}
        {{--            toastr.error("Failed to refresh list");--}}
        {{--        }--}}
        {{--    });--}}
        {{--}--}}



        //Status Active
        $(document).ready(function () {
            $(document).on('change', '.toggle-status', function () {
                var checkbox = $(this);

                var id = checkbox.data('id');
                var url = `/shop/design-master/${id}/toggle`;

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        if (response.status) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Something went wrong!');
                        }
                    },
                    error: function (xhr) {
                        toastr.error('Error occurred');
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    }
                });
            })
        });

        // Search Datatable
        $(document).ready(function () {

            let debounceTimer;

            $('#searchForm input[name="search"]').on('keyup', function (e) {
                clearTimeout(debounceTimer);

                let search = $(this).val();

                debounceTimer = setTimeout(function () {
                    $.ajax({
                        url: "{{ route('shop.designMaster.index') }}",
                        type: 'GET',
                        data: {search: search},
                        beforeSend: function () {
                            $('#designMasterList tbody').html('<tr><td colspan="10" class="text-center">Loading...</td></tr>');
                        },
                        success: function (res) {
                            $('#designMasterList').html(res);
                        },
                        error: function (err) {
                            toastr.error('Failed to load data');
                        }
                    });
                }, 300); // 300ms delay
            });

        });


    </script>

@endpush