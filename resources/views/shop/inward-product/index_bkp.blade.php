@extends('layouts.app')
@section('header-title', __('Inward Product'))
@section('content')


    <div>
        <div>
{{--            <div class="d-flex align-items-center flex-wrap gap-3 justify-content-end px-3 mb-2">--}}
{{--                <div>--}}
{{--                    <button type="button" id="list-btn" class="btn py-2 btn-primary">--}}
{{--                        {{ __('Inward List') }}--}}
{{--                    </button>--}}
{{--                    <button type="button" id="create-modal-btn" class="btn py-2 btn-primary">--}}
{{--                        <i class="bi bi-patch-plus"></i>--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </div>--}}

            <div class="row">
                <div class="col-12" id="inwardProductList">
                    @include('shop.inward-product.partials.inward-product-table')
                </div>

                <div class="col-12" id="inwardList">
                    @if(isset($inwardLists))
                        @include('shop.inward-product.partials.inward-list',['inwardLists' => $inwardLists])
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Design Master Modal Short Key --}}
    @include('shop.components-modal.design-master-modal')

    {{-- Design Master Modal Short Key --}}
    @include('shop.components-modal.item-master-modal')

@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/holdon/HoldOn.min.css') }}" type="text/css"/>
    <style>
        #inwardList table tr:not(:first-child):not(.ui-datepicker-calendar tr) {
            opacity: 1 !important;
            display: table-row !important;
            animation: none !important;
        }
        .dropdown-suggestions {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            z-index: 1000;
            width: auto;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }

        .dropdown-suggestions ul {
            margin: 0;
            padding: 0;
            list-style: none;
            width: 100%;
        }

        .dropdown-suggestions li {
            padding: 5px 10px;
            cursor: pointer;
        }

        .dropdown-suggestions li:hover {
            background-color: #f0f0f0;
        }

        .dropdown-taxCode, .dropdown-designNo {
            /*position: absolute;*/
            background: #fff;
            border: 1px solid #ccc;
            border-top: none;
            z-index: 10000;
            width: 100%;
            display: none;
            max-height: 150px;
            overflow-y: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border-radius: 0 0 4px 4px;
        }

        .dropdown-taxCode ul, .dropdown-designNo ul {
            margin: 0;
            padding: 0;
            list-style: none;
            width: 100%;
        }

        .dropdown-taxCode li, .dropdown-designNo li {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .dropdown-taxCode li:last-child,  .dropdown-designNo li:last-child{
            border-bottom: none;
        }

        .dropdown-taxCode li:hover, .dropdown-designNo li:hover {
            background-color: #e9ecef;
        }


        .app-main-outer {
            overflow-x: auto;
        }

        #inwardProductTable {
            min-width: 1600px; /* jitni columns hain utna space */
            width: 100%;
            border-collapse: collapse;
        }

        #inwardProductTable th,
        #inwardProductTable td {
            white-space: nowrap; /* line break disable */
            vertical-align: middle;
            text-align: center;
            padding: 6px;
        }

        #inwardProductTable input.form-control {
            width: 100%;
            min-width: 100px;
            font-size: 13px;
        }

        #inwardProductTable th:nth-child(6) { width: 70px;}
        #inwardProductTable td:nth-child(6) input{
            width: 70px;
            min-width: 70px;
        }

        #inwardProductTable td:nth-child(1) { width: 50px; }  /* SL */
        #inwardProductTable td:nth-child(2) { min-width: 180px; } /* Item */
        #inwardProductTable td:nth-child(3) { min-width: 120px; } /* Design No */
        #inwardProductTable td:nth-child(4),
        #inwardProductTable td:nth-child(5) { min-width: 100px; } /* Color/Size */
        #inwardProductTable td:nth-child(6),
        #inwardProductTable td:nth-child(7),
        #inwardProductTable td:nth-child(8),
        #inwardProductTable td:nth-child(9),
        #inwardProductTable td:nth-child(10),
        #inwardProductTable td:nth-child(11),
        #inwardProductTable td:nth-child(12),
        #inwardProductTable td:nth-child(13),
        #inwardProductTable td:nth-child(14),
        #inwardProductTable td:nth-child(15) {
            min-width: 70px;
        }

        #inwardList{
            display: none;
        }

    </style>
    <style>
        #inwardProductList table tr:not(:first-child):not(.ui-datepicker-calendar tr) {
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

        $('#design-master-modal').on('shown.bs.modal', function () {
            $('select').select2({
                dropdownParent: $('#design-master-modal')
            });
        });

        $(document).on('hidden.bs.modal', function () {
            if ($('.modal.show').length) {
                $('body').addClass('modal-open');
            }
        });

    </script>
    <script src="{{ asset('assets/scripts/shortKey.js') }}"></script>
    <script src="{{ asset('assets/css/holdon/HoldOn.min.js') }}"></script>

    {{-- Short Modal Open Code Design Master--}}
    @include('shop.components-modal.master-modal.design-master-modal-script')
    {{-- Short Modal Open Code Item Master--}}
    @include('shop.components-modal.master-modal.item-master-modal-script')

    <script>
        $(document).on('click','#modelClose',function (){
            const modalIdName = $(this).data('modal-name');

            if (modalIdName === 'design-master-modal'){
                $('#inwardProductTable tbody').html('');
                window.createRow();
            }
            $("#" + modalIdName).modal("hide");
        });


        $(document).ready(function () {
            const $list_btn = $("#list-btn");
            const $create_btn = $("#create-modal-btn");
            const $inwardProductList = $("#inwardProductList");
            const $inwardList = $("#inwardList");

            // Default Hide inwardList
            $inwardList.hide();

            $list_btn.on('click', function () {
                showCustomLoader();
                $inwardProductList.toggle();
                $inwardList.toggle();

                if ($inwardList.is(":visible")) {
                    refreshInwardList()
                    $list_btn.text('Back');
                    $create_btn.hide();
                } else {
                    $list_btn.text('Inward List');
                    $create_btn.show();
                }
                HoldOn.close();
            });

        });

       

        $(document).on('click', '.deleteConfirm', function(e) {
            e.preventDefault();
            let id = $(this).data('id');

            Swal.fire({
                title: "Are you sure?",
                text: "You want to delete this inward product",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/shop/inward-product/' + id + '/destroy',
                        type: 'GET', // keep GET if your route is GET
                        success: function(response) {
                            toastr.success("Inward Product deleted successfully");
                            refreshInwardList(); // refresh the table
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.error || "Failed to delete item");
                        }
                    });
                }
            });
        });


        function refreshInwardList() {
            $.ajax({
                url: "{{ route('shop.inwardProduct.list') }}",
                type: "GET",
                success: function (data) {
                    console.log(data)
                    $("#inwardList").html(data);
                },
                error: function () {
                    toastr.error("Failed to refresh list");
                }
            });
        }

    </script>
@endpush