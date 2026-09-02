@extends('layouts.app')
@section('header-title', __('Purchase Product'))
@section('content')
    <div>
        <div>
            <div class="keyboard-shortcuts-bar bg-white border rounded p-3 mb-4 shadow-sm d-flex align-items-center gap-3 flex-wrap">
                <span class="text-secondary fw-bold small text-uppercase tracking-wider"><i class="bi bi-keyboard me-1.5"></i>Hot keys:</span>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div><kbd>F3</kbd> or <kbd>Alt+V</kbd> <span class="text-muted small">Voucher No</span></div>
                    <div><kbd>F4</kbd> or <kbd>Alt+C</kbd> <span class="text-muted small">Challan No</span></div>
                    <div><kbd>F8</kbd> or <kbd>Alt+S</kbd> <span class="text-muted small">Submit Purchase</span></div>
                    <div><kbd>F9</kbd> <span class="text-muted small">Toggle List/Entry</span></div>
                    <div><kbd>Enter</kbd> <span class="text-muted small">Move to Next Input</span></div>
                </div>
            </div>

            <div class="row">
                <div class="col-12" id="inwardProductList">
                    @include('shop.purchase-product.partials.purchase-product-table')
                </div>


                {{-- Form Code --}}

                <form id="searchForm"
                      class="d-none align-items-center justify-content-end gap-3 mb-3 border-bottom pb-3 flex-wrap">
                    {{--                <button type="button" class="btn btn-primary" data-bs-toggle="modal"--}}
                    {{--                        data-bs-target="#filterItemMasterModal">--}}
                    {{--                    {{ __('Filter') }}--}}
                    {{--                </button>--}}

                    <div class="input-group" style="max-width: 400px">
                        <input type="text" name="search" class="form-control"
                               placeholder="{{ __('Search by voucher number, challan number, or party name.') }}"
                               value="{{ request('search') }}">
                        {{--                    <button type="submit" class="input-group-text btn btn-primary">--}}
                        {{--                        <i class="fa fa-search"></i> {{ __('Search') }}--}}
                        {{--                    </button>--}}
                    </div>
                </form>
                <div class="col-12" id="inwardList">
                    <!-- Kachi Entry List Active Banner -->
                    <div id="kachiListBanner" class="alert alert-warning border-warning d-none align-items-center justify-content-between py-2 px-3 mb-2 shadow-sm" style="border-radius: 8px; background-color: #fffbe6; color: #856404; font-weight: 600;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark px-2 py-1 fs-6" style="letter-spacing: 0.5px;"><i class="bi bi-funnel-fill me-1"></i> {{ __('Kachi Entry List Active') }}</span>
                            <span>{{ __('Showing ONLY Kachi Entries. Press') }} <kbd class="bg-dark text-white px-1 font-monospace">Alt + K</kbd> {{ __('to switch back to Normal Entries.') }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark text-dark fw-bold" onclick="toggleKachiListFilter()">{{ __('Show Normal Entries') }}</button>
                    </div>

                    @if(isset($inwardLists))
                        @include('shop.purchase-product.partials.purchase-list',['inwardLists' => $inwardLists])
                    @endif
                </div>

            </div>
        </div>
    </div>

    <!-- Purchase Details View Modal -->
    <div class="modal fade purchase-details-modal" id="purchaseViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">

                <div class="modal-header bg-light border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark m-0 d-flex align-items-center gap-2" style="font-size: 16.5px;">
                        <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                        <span>{{ __('Purchase Details') }}</span>
                        <span id="purchaseViewModalVoucherNo" class="text-primary font-monospace fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light-subtle" id="purchaseViewBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="text-muted small fw-medium">{{ __('Loading purchase details...') }}</div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top px-4 py-2.5 d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        <span id="purchaseViewItemCount" class="fw-bold text-dark">0</span> {{ __('items listed') }}
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm px-4 fw-semibold rounded-2" data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Online Product Status Confirmation Modal -->
    <div class="modal fade" id="onlineProductConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div id="onlineConfirmIconWrapper" class="d-inline-flex align-items-center justify-content-center rounded-circle p-3 bg-primary-subtle text-primary" style="width: 64px; height: 64px;">
                            <i class="bi bi-globe fs-2" id="onlineConfirmIcon"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-2" id="onlineConfirmTitle">{{ __('Are you sure?') }}</h5>
                    <p class="text-muted small mb-4" id="onlineConfirmMessage" style="font-size: 13.5px; line-height: 1.5;"></p>
                    
                    <input type="hidden" id="onlineConfirmPurchaseId">

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 fw-semibold border rounded-2" data-bs-dismiss="modal" id="onlineConfirmNoBtn">
                            {{ __('No') }}
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2 fw-semibold rounded-2 d-inline-flex align-items-center justify-content-center" id="onlineConfirmYesBtn">
                            <span id="onlineConfirmYesBtnText">{{ __('Yes, Make Online') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/holdon/HoldOn.min.css') }}" type="text/css"/>
    <style>
        .purchase-details-modal .modal-dialog {
            max-width: 1180px !important;
            width: calc(100% - 32px) !important;
        }
        .purchase-details-modal table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .purchase-details-modal tbody {
            display: table-row-group !important;
        }
        .purchase-details-modal table tr,
        .purchase-details-modal table tr:not(:first-child) {
            display: table-row !important;
            opacity: 1 !important;
            visibility: visible !important;
            position: static !important;
            transform: none !important;
            animation: none !important;
        }
        .purchase-details-modal table td,
        .purchase-details-modal table th {
            display: table-cell !important;
            vertical-align: middle !important;
        }
        #inwardList table tr:not(:first-child):not(.ui-datepicker-calendar tr), 
        #purchaseProductList table tr:not(:first-child):not(.ui-datepicker-calendar tr),
        #purchaseViewModal table tr:not(:first-child):not(.ui-datepicker-calendar tr) {
            opacity: 1 !important;
            display: table-row !important;
            animation: none !important;
        }

        #barcode-generate-modal table tr:not(:first-child):not(.ui-datepicker-calendar tr) {
            opacity: 1 !important;
            display: table-row !important;
            animation: none !important;
        }

        .dropdown-suggestions {
            position: absolute;
            background: #fff;
            border: 1px solid #e2e8f0;
            z-index: 1000;
            width: auto;
            display: none;
            max-height: 200px;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .dropdown-suggestions ul {
            margin: 0;
            padding: 0;
            list-style: none;
            width: 100%;
        }

        .dropdown-suggestions li {
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .dropdown-suggestions li:hover {
            background-color: #f1f5f9;
        }

        .dropdown-taxCode, .dropdown-designNo {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-top: none;
            z-index: 10000;
            width: 100%;
            display: none;
            max-height: 150px;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 0 0 8px 8px;
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
            border-bottom: 1px solid #f1f5f9;
            font-size: 13.5px;
            transition: background-color 0.2s;
        }

        .dropdown-taxCode li:last-child, .dropdown-designNo li:last-child {
            border-bottom: none;
        }

        .dropdown-taxCode li:hover, .dropdown-designNo li:hover {
            background-color: #f1f5f9;
        }

        .app-main-outer {
            overflow-x: auto;
        }

        #inwardProductTable {
            min-width: 1600px;
            width: 100%;
            border-collapse: collapse;
        }

        #purchaseProductTable th,
        #purchaseProductTable td {
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
            padding: 8px 6px;
        }

        #inwardProductTable th {
            background-color: #f8fafc;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 11.5px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 12px 8px !important;
        }

        #inwardProductTable td {
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
            padding: 8px 6px;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        #inwardProductTable input.form-control {
            width: 100%;
            min-width: 100px;
            font-size: 13px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            transition: all 0.2s;
        }

        #inwardProductTable input.form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
        }

        #inwardProductTable th:nth-child(6) {
            width: 70px;
        }

        #inwardProductTable td:nth-child(6) input {
            width: 70px;
            min-width: 70px;
        }

        #inwardProductTable td:nth-child(1) {
            width: 50px;
        }

        #inwardProductTable td:nth-child(2) {
            min-width: 180px;
        }

        #inwardProductTable td:nth-child(3) {
            min-width: 120px;
        }

        #inwardProductTable td:nth-child(4),
        #inwardProductTable td:nth-child(5) {
            min-width: 100px;
        }

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

        #inwardList {
            display: none;
        }

        #create-record-btn {
            display: none;
        }

        /* Modern UI Tweaks */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.025);
            transition: all 0.25s ease;
            background: #ffffff;
        }

        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        }

        .card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700 !important;
            color: #1e293b;
            padding: 14px 20px;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            font-size: 13.5px;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 12.5px;
            margin-bottom: 6px;
        }

        /* Keyboard Shortcut Elements */
        kbd {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
            border-bottom-width: 2px !important;
            padding: 2px 6px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            border-radius: 4px !important;
            box-shadow: 0 1px 0 rgba(0,0,0,0.15) !important;
        }

        /* Table Highlight Class */
        #inwardProductTable tbody tr {
            transition: background-color 0.2s ease;
        }

        #inwardProductTable tbody tr.active-row-focus {
            background-color: rgba(37, 99, 235, 0.04) !important;
        }

        #inwardProductTable tbody tr.active-row-focus td {
            border-bottom-color: #bfdbfe !important;
        }

        /* Rounded badge style for totals */
        .summary-accent {
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            color: #1e40af;
            font-weight: 700;
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
    {{-- Create Row --}}
    <script>
        $(function () {
            $("#oldPriceTable").draggable({
                handle: ".card-header"
            });
        });
        // New Code

        let editData = false;

        window.createRow = function () {
            let sl = $('#inwardProductTable tbody tr').length + 1;
            let deleteBtn = '';
            if (editData === false) {
                if (sl > 1) {
                    deleteBtn = `
                        <button type="button" class="btn btn-outline-danger deleteRow">
                            <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="trash" loading="lazy">
                        </button>`;
                }
            } else {
                deleteBtn = `
                        <button type="button" class="btn btn-outline-danger deleteRow">
                            <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="trash" loading="lazy">
                        </button>`;
            }


            let row = `
            <tr data-sl="${sl}">
                <td class="text-center">${sl}</td>
                <td><input type="text" name="item[]" class="form-control w-auto item"><div class="dropdown-suggestions"></div><input type="hidden" name="itemid[]"><input type="hidden" name="inwardProductId[]"></td>

                <td><input type="text" name="designNo[]" class="form-control w-min designno"><div class="dropdown-designNo"></div><input type="hidden" name="designid[]"></td>
                <td class="text-start"><select name="colorInwardIds[0][]" class="form-control colorSelectInward row-${sl}" multiple></select></td>
                <td class="text-start"><select name="sizeInwardIds[0][]" class="form-control sizeSelectInward row-${sl}" multiple></select></td>

                <td><input type="text" name="qty[]" class="form-control qty" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"></td>
                <td><input type="text" name="purcRate[]" class="form-control buy_price decimal-input"></td>
                <td><input type="text" name="amount[]" class="form-control price decimal-input"></td>
                <td><input type="text" name="disc[]" class="form-control discount_percentage decimal-input"></td>
                <td><input type="text" name="mrp[]" class="form-control disabledCls decimal-input" readonly></td>
                <td><input type="text" name="mark_up[]" class="form-control disabledCls decimal-input" readonly></td>
                <td><input type="text" name="mark_down[]" class="form-control disabledCls decimal-input" readonly></td>

                <td><input type="text" name="netPurcRate[]" class="form-control netPurcRate disabledCls decimal-input" readonly></td>



                <td class="position-relative">
    <input type="text" name="taxCode[]" class="form-control taxcode">
    <div class="dropdown-taxCode"></div>
    <input type="hidden" name="taxCodeId[]">
</td>

                <td><input type="number" name="sgst[]" class="form-control"><input type="hidden" name="sgstId[]"></td>
                 <td class="text-center">
                    ${deleteBtn}
                </td>
            </tr>`;
            $('#inwardProductTable tbody').append(row);
            // $('#inwardProductTable tbody tr:last').find('input.item').focus();

            $(`.row-${sl}.colorSelectInward`).select2({
                placeholder: "Select Color",
                width: '100%',
                ajax: {
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    delay: 250,
                    data: function (params) {
                        return {colorSearch: params.term};
                    },
                    processResults: function (data) {
                        return {
                            results: data.designWithColorData.map(c => ({id: c.id, text: c.name}))
                        };
                    }
                }
            });

            // Initialize Size Select2
            $(`.row-${sl}.sizeSelectInward`).select2({
                placeholder: "Select Size",
                width: '100%',
                ajax: {
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    delay: 250,
                    data: function (params) {
                        return {sizeSearch: params.term}; // backend me sizeSearch parameter handle karna
                    },
                    processResults: function (data) {
                        return {
                            results: data.designWithSizeData.map(s => ({id: s.id, text: s.name}))
                        };
                    }
                }
            });

        }

        function fillDayAndTime() {

            let dateInput = $('#inward_date');

            // Agar empty hai → set today's date
            if (!dateInput.val()) {
                let today = new Date();
                let yyyy = today.getFullYear();
                let mm = (today.getMonth() + 1).toString().padStart(2, '0'); // month 0-11
                let dd = today.getDate().toString().padStart(2, '0');
                let todayStr = `${yyyy}-${mm}-${dd}`;
                dateInput.val(todayStr);
            }

            let dateVal = dateInput.val();
            let dateObj = new Date(dateVal);

            if (!isNaN(dateObj)) {
                let dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                $('#inward_day_name').val(dayNames[dateObj.getDay()]);
            }

            // Current time
            let now = new Date();
            let hours = now.getHours().toString().padStart(2, '0');
            let minutes = now.getMinutes().toString().padStart(2, '0');
            $('#inward_time').val(hours + ':' + minutes);
        }

        function clearValidationBorders() {
            $('input, select').removeClass('is-invalid-border select2-error');
            $('.select2-container').removeClass('select2-invalid');
            $('#rowsErrorContainer').text('');
            $('#rowsErrorTd').removeClass('bg-warning-light');
        }

        // Close
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
    <script src="{{ asset('assets/css/holdon/HoldOn.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            const $list_btn = $("#list-btn");
            const $create_btn = $("#create-modal-btn");
            const $inwardProductList = $("#inwardProductList");
            const $inwardList = $("#inwardList");
            const $searchForm = $("#searchForm");

            const $create_record_btn = $("#create-record-btn");

            $create_record_btn.on('click', function () {
                editData = false;
                clearValidationBorders();
                $create_record_btn.hide();
                resetFormInward();
            });


            // Default Hide inwardList
            $inwardList.hide();
            $create_record_btn.hide();

            $list_btn.on('click', function () {

                showCustomLoader();
                $inwardProductList.toggle();
                $inwardList.toggle();

                if ($inwardList.is(":visible")) {
                    refreshInwardList()
                    $list_btn.text('Back');
                    $create_btn.hide();
                    $create_record_btn.hide();
                    $searchForm.removeClass('d-none').addClass('d-flex')[0].reset();
                    // oldPriceTableClear()

                } else {
                    $list_btn.text('Purchase List');
                    $create_btn.show();
                    $searchForm.removeClass('d-flex').addClass('d-none')[0].reset();
                    if ($("#inward_invoice_id").val() !== '') {
                        $create_record_btn.show();
                    } else {
                        $create_record_btn.hide();
                    }

                }
                HoldOn.close();
            });

        });
    </script>

    {{-- Js File Code    --}}
    <script>
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
    </script>
    <script>
        function resetFormInward() {
            $('#itemForm')[0].reset();
            $(".errorSpan").empty();
            $("#inward_invoice_id").val('');
            fillDayAndTime();

            // Top Data

            $("#day_book_id").val($("#day_book_id option:first").val()).trigger('change');
            $("#inward_vat_tax_id").val($("#inward_vat_tax_id option:first").val()).trigger('change');
            $("#inward_party_code").empty().append('<option value="">{{ __("Select Account Master") }}</option>');

            // Row
            $('#inwardProductTable tbody').html('');
            // window.createRow();
            // Footer Data Clear

            $("#inward_acc_purchaser").empty().append('<option value="">{{ __("Select Purchaser") }}</option>');
            $("#inward_acc_season").empty().append('<option value="">{{ __("Select Season") }}</option>');
            $("#inward_acc_agent").empty().append('<option value="">{{ __("Select a Agent") }}</option>');
            $("#inward_acc_transport").empty().append('<option value="">{{ __("Select a Transport") }}</option>');
            $("#inward_acc_delivery_by").empty().append('<option value="">{{ __("Select a Delivery By") }}</option>');

            $('#kachiLoadedBanner').addClass('d-none').removeClass('d-flex');
        }
    </script>
    {{-- Close --}}

    <script>
        function refreshInwardList() {
            $.ajax({
                url: "{{ route('shop.purchaseProduct.list') }}",
                type: "GET",
                success: function (data) {
                    $("#inwardList").html(data);
                },
                error: function () {
                    toastr.error("Failed to refresh list");
                }
            });
        }
    </script>

    <script>
        $(document).ready(function () {

            $('input[name="inward_voucher_no"], #inward_challan_no').on('keypress', function (e) {

                if (e.which == 13) { // Enter key
                    e.preventDefault();

                    let value = $(this).val().trim();
                    showCustomLoader();
                    if (value.length >= 4) {
                        resetFormInward();
                        editDataLoad(value)
                        HoldOn.close()
                    } else {
                        toastr.error('Minimum 4 letters required');
                        HoldOn.close();

                    }

                }

            });

            function editDataLoad(value) {
                $.ajax({
                    url: `/shop/purchase-product/${value}/edit`,
                    type: 'GET',
                    success: function (res) {
                        console.log("Response",res)
                        if (res.inwardData){
                            $(".dataLoadToShow").removeClass('d-none')

                            if (res.inwardData.is_kachi == 1 || res.inwardData.is_kachi === true) {
                                $('#kachiLoadedBanner').removeClass('d-none').addClass('d-flex');
                                toastr.info("{{ __('Kachi Entry Loaded') }}", "{{ __('Notice') }}");
                            } else {
                                $('#kachiLoadedBanner').addClass('d-none').removeClass('d-flex');
                            }

                            // Header Value Set

                            $('#inward_invoice_id').val(res.inwardData.id);

                            if (res.inwardData.counter_master_id !== '' && res.inwardData.counter_master_id !== 0) {
                                $('#day_book_id').val(res.inwardData.counter_master_id).trigger('change.select2');
                            } else {
                                $("#day_book_id").val($("#day_book_id option:first").val()).trigger('change');
                            }

                            let selectedTaxId = res.inwardData.vat_tax_id;
                            if (res.inwardData.inward_product && res.inwardData.inward_product.length > 0 && res.inwardData.inward_product[0].vat_tax_id) {
                                selectedTaxId = res.inwardData.inward_product[0].vat_tax_id;
                            }
                            if (selectedTaxId !== '' && selectedTaxId !== 0) {
                                $('#inward_vat_tax_id').val(selectedTaxId).trigger('change.select2');
                            } else {
                                $("#inward_vat_tax_id").val($("#inward_vat_tax_id option:first").val()).trigger('change');
                            }

                            $('#inward_voucher_no').val(res.inwardData.inward_voucher_no);

                            $('#inward_date').val(res.inwardData.inward_date);

                            $('#inward_day_name').val(res.inwardData.inward_day_name);
                            $('#inward_time').val(res.inwardData.inward_time);
                            $('#inward_challan_no').val(res.inwardData.inward_challan_no);

                            $('#inward_challan_date').val(res.inwardData.inward_challan_date);

                            $("#inward_party_code_value").val(res.inwardData.party_code.accountshortcode)
                            $("#inward_party_code").val(res.inwardData.inward_party_code)

                            if (res.inwardData.inward_party_code) {
                                var itemData = {
                                    id: res.inwardData.inward_party_code,
                                    text: res.inwardData.party_code.accountshortcode,
                                    accName: res.inwardData.party_code.accountName,
                                    accLimit: res.inwardData.party_code.other_info_act_limit,
                                };

                                if ($("#inward_party_code").find("option[value='" + itemData.id + "']").length === 0) {
                                    var partyCodeOption = new Option(itemData.text, itemData.id, true, true);
                                    $("#inward_party_code").append(partyCodeOption).trigger('change');
                                } else {
                                    $("#inward_party_code").val(itemData.id).trigger('change');
                                }
                            }

                            $('#inward_party_name').val(res.inwardData.party_code.accountName);
                            $('#inward_total').val(res.inwardData.inward_total);
                            $('#inward_party_limit').val(res.inwardData.inward_party_limit);

                            // Table View

                            let products = res.inwardData.inward_product;

                             products.forEach((product, index) => {
                                 createRow();
                                 fillRowData(product, index + 1); // 👈 row number pass
                             });
                             setTimeout(() => {
                                 $('#inwardProductTable tbody tr:first').find('input[name="qty[]"]').focus().select();
                             }, 300);

                            // Footer Value Set
                            $('#inward_acc_credit_day').val(res.inwardData.inward_credit_day);

                            if (res.inwardData.inward_acc_purchaser) {
                                var purchaserData = {
                                    id: res.inwardData.inward_acc_purchaser,
                                    text: res.inwardData.purchaser.name + ' - ' + res.inwardData.purchaser.last_name,
                                };

                                if ($("#inward_acc_purchaser").find("option[value='" + purchaserData.id + "']").length === 0) {
                                    var purchaserDataOption = new Option(purchaserData.text, purchaserData.id, true, true);
                                    $("#inward_acc_purchaser").append(purchaserDataOption).trigger('change');
                                } else {
                                    $("#inward_acc_purchaser").val(purchaserData.id).trigger('change');
                                }
                            }

                            if (res.inwardData.season_id) {
                                var seasonData = {
                                    id: res.inwardData.season_id,
                                    text: res.inwardData.season.name,
                                };

                                if ($("#inward_acc_season").find("option[value='" + seasonData.id + "']").length === 0) {
                                    var seasonDataOption = new Option(seasonData.text, seasonData.id, true, true);
                                    $("#inward_acc_season").append(seasonDataOption).trigger('change');
                                } else {
                                    $("#inward_acc_season").val(seasonData.id).trigger('change');
                                }
                            }

                            if (res.inwardData.agent_id) {
                                var agentData = {
                                    id: res.inwardData.agent_id,
                                    text: res.inwardData.agent.code + ' - ' + res.inwardData.agent.name,
                                };

                                if ($("#inward_acc_agent").find("option[value='" + agentData.id + "']").length === 0) {
                                    var agentOption = new Option(agentData.text, agentData.id, true, true);
                                    $("#inward_acc_agent").append(agentOption).trigger('change');
                                } else {
                                    $("#inward_acc_agent").val(agentData.id).trigger('change');
                                }
                            }

                            if (res.inwardData.transport_id) {
                                var transportData = {
                                    id: res.inwardData.transport_id,
                                    text: res.inwardData.transport.name,
                                };

                                if ($("#inward_acc_transport").find("option[value='" + transportData.id + "']").length === 0) {
                                    var transportOption = new Option(transportData.text, transportData.id, true, true);
                                    $("#inward_acc_transport").append(transportOption).trigger('change');
                                } else {
                                    $("#inward_acc_transport").val(transportData.id).trigger('change');
                                }
                            }

                            if (res.inwardData.delivery_by_id) {
                                var deliveryByData = {
                                    id: res.inwardData.delivery_by_id,
                                    text: res.inwardData.delivery_by.name,
                                };

                                if ($("#inward_acc_delivery_by").find("option[value='" + deliveryByData.id + "']").length === 0) {
                                    var deliveryByOption = new Option(deliveryByData.text, deliveryByData.id, true, true);
                                    $("#inward_acc_delivery_by").append(deliveryByOption).trigger('change');
                                } else {
                                    $("#inward_acc_delivery_by").val(deliveryByData.id).trigger('change');
                                }
                            }

                            $('#inward_acc_lr_no').val(res.inwardData.inward_acc_lr_no);

                            $('#inward_acc_lr_date').val(res.inwardData.inward_acc_lr_date);

                            $('#inward_acc_remark').val(res.inwardData.inward_acc_remark);
                            $('#inward_acc_gst_amount').val(res.inwardData.inward_acc_gst_amount);
                            $('#inward_acc_net_amount').val(res.inwardData.inward_acc_net_amount);
                            $('#inward_acc_freight_amount').val(res.inwardData.inward_acc_freight_amount || '0.00');
                            $('#inward_acc_parcel_amount').val(res.inwardData.inward_acc_parcel_amount || '0.00');
                            window.calculateNetPurcRateSum();
                        }else{
                            $(".dataLoadToShow").addClass('d-none')
                            toastr.error(res.message);
                        }


                    },
                    error: function () {
                        $(".dataLoadToShow").addClass('d-none')
                        toastr.error("Failed to load item data.");
                    }
                });
            }

            function fillRowData(product, sl) {

                let $row = $(`#inwardProductTable tbody tr[data-sl="${sl}"]`);

                // ---------- BASIC INPUTS ----------
                $row.find('input[name="inwardProductId[]"]').val(product.id);
                $row.find('input[name="qty[]"]').val(product.quantity).addClass('disabledCls');
                $row.find('input[name="purcRate[]"]').val(product.buy_price).addClass('disabledCls');
                $row.find('input[name="amount[]"]').val(product.price).addClass('disabledCls');
                $row.find('input[name="disc[]"]').val(product.discount_price).addClass('disabledCls');
                $row.find('input[name="mrp[]"]').val(product.mrp).addClass('disabledCls');
                $row.find('input[name="mark_up[]"]').val(product.mark_up).addClass('disabledCls');
                $row.find('input[name="mark_down[]"]').val(product.mark_down).addClass('disabledCls');
                $row.find('input[name="netPurcRate[]"]').val(product.net_purc_rate).addClass('disabledCls');

                // ---------- ITEM / DESIGN (hidden ids) ----------
                $row.find('input[name="item[]"]').val(product.products.name).addClass('disabledCls');
                $row.find('input[name="itemid[]"]').val(product.product_id);

                $row.find('input[name="designNo[]"]').val(product.design_master.design_number).addClass('disabledCls');
                $row.find('input[name="designid[]"]').val(product.design_master_id);

                // ---------- TAX ----------
                $row.find('input[name="taxCode[]"]').val(product.hsn_master.hsn_code);
                $row.find('input[name="taxCodeId[]"]').val(product.hsn_master_id);

                // ---------- TAX ----------
                $row.find('input[name="sgst[]"]').val(product.vat_tax.percentage);
                $row.find('input[name="sgstId[]"]').val(product.vat_tax_id);

                // ---------- COLOR SELECT2 ----------
                let $colorSelect = $row.find('.colorSelectInward');

                $colorSelect.empty(); // safety

                product.colors.forEach(color => {
                    let option = new Option(
                        color.name,  // text
                        color.id,    // value
                        true,        // defaultSelected
                        true         // selected
                    );

                    $colorSelect.append(option);
                });

                $colorSelect.trigger('change.select2');

                // ---------- SIZE SELECT2 ----------
                let $sizeSelect = $row.find('.sizeSelectInward');

                $sizeSelect.empty();

                product.sizes.forEach(size => {
                    let option = new Option(
                        size.name,
                        size.id,
                        true,
                        true
                    );

                    $sizeSelect.append(option);
                });

                $sizeSelect.trigger('change.select2');

            }

        });
    </script>

    <script>
        function fillDayAndTime() {

            let dateInput = $('#purchase_date');

            // Agar empty hai → set today's date
            if (!dateInput.val()) {
                let today = new Date();
                let yyyy = today.getFullYear();
                let mm = (today.getMonth() + 1).toString().padStart(2, '0'); // month 0-11
                let dd = today.getDate().toString().padStart(2, '0');
                let todayStr = `${yyyy}-${mm}-${dd}`;
                dateInput.val(todayStr);
            }

            let dateVal = dateInput.val();
            let dateObj = new Date(dateVal);

            if (!isNaN(dateObj)) {
                let dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                $('#purchase_day_name').val(dayNames[dateObj.getDay()]);
            }

            // Current time
            let now = new Date();
            let hours = now.getHours().toString().padStart(2, '0');
            let minutes = now.getMinutes().toString().padStart(2, '0');
            $('#purchase_time').val(hours + ':' + minutes);
        }

        // Kachi Entry List Mode Filter Logic
        window.isKachiListActive = false;

        window.toggleKachiListFilter = function() {
            window.isKachiListActive = !window.isKachiListActive;

            const $banner = $('#kachiListBanner');

            if (window.isKachiListActive) {
                toastr.info("{{ __('Showing Kachi Entries Only') }}", "{{ __('Kachi Entry List Active') }}");
                $banner.removeClass('d-none').addClass('d-flex');
            } else {
                toastr.info("{{ __('Showing Normal Purchase Entries Only') }}", "{{ __('Normal Mode Active') }}");
                $banner.addClass('d-none').removeClass('d-flex');
            }

            refreshInwardList();
        };

        function refreshInwardList() {
            let isKachiVal = window.isKachiListActive ? 1 : 0;
            let searchVal = $('#searchForm input[name="search"]').val() || '';
            loadInwardList("{{ route('shop.purchaseProduct.list') }}", { is_kachi: isKachiVal, search: searchVal });
        }

        $(document).on('click', '.pagination a, #pagination-links a', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            loadInwardList(url);
        });

        // Common function for loading Purchase List with mode & search parameters
        function loadInwardList(url, extraData = {}) {
            let reqData = extraData || {};
            if (typeof reqData === 'object' && !('is_kachi' in reqData)) {
                reqData.is_kachi = window.isKachiListActive ? 1 : 0;
            }
            if (typeof reqData === 'object' && !('search' in reqData)) {
                let sVal = $('#searchForm input[name="search"]').val();
                if (sVal) reqData.search = sVal;
            }

            $.ajax({
                url: url,
                type: 'GET',
                data: reqData,
                success: function (response) {
                    $('#inwardList').html(response);
                },
                error: function (xhr) {
                    console.log('Error:', xhr.responseText);
                }
            });
        }

        $(document).ready(function () {

            let debounceTimer;

            $(document).on('keyup', '#searchForm input[name="search"]', function (e) {
                clearTimeout(debounceTimer);
                let search = $(this).val();

                debounceTimer = setTimeout(function () {
                    loadInwardList("{{ route('shop.purchaseProduct.list') }}", { search: search, is_kachi: window.isKachiListActive ? 1 : 0 });
                }, 300); // 300ms delay
            });
        });
    </script>

    <script>
        $(document).on('click','.viewData',function(e){
            e.preventDefault();
            let id = $(this).data('id');

            $('#purchaseViewModalVoucherNo').text('');
            $('#purchaseViewItemCount').text('0');

            $('#purchaseViewBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-muted small fw-medium">{{ __('Loading purchase details...') }}</div>
                </div>
            `);

            $('#purchaseViewModal').modal('show');

            $.ajax({
                url: '/shop/purchase-product/view/'+id,
                type:'GET',
                success:function(response){
                    $('#purchaseViewBody').html(response);

                    const voucherNo = $('#purchaseDetailsVoucherNo').text() || '';
                    if (voucherNo) {
                        $('#purchaseViewModalVoucherNo').text(' — ' + voucherNo.trim());
                    } else {
                        $('#purchaseViewModalVoucherNo').text('');
                    }

                    const itemCount = $('#purchaseDetailsItemCount').val() || '0';
                    $('#purchaseViewItemCount').text(itemCount);
                },
                error: function() {
                    $('#purchaseViewBody').html(`
                        <div class="text-center py-5 text-danger">
                            <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                            <h6>{{ __('Failed to load purchase details') }}</h6>
                        </div>
                    `);
                }
            });
        });

        // Download PDF Handler
        $(document).on('click', '#btnDownloadPurchasePdf', function (e) {
            e.preventDefault();
            const btn = $(this);
            const originalHtml = btn.html();
            const voucherNo = $('#purchaseDetailsVoucherNo').text().trim() || 'Purchase-Order';
            const originalElement = document.querySelector('.purchase-details-content');

            if (!originalElement) return;

            btn.html('<i class="spinner-border spinner-border-sm me-1"></i> {{ __("Downloading...") }}').prop('disabled', true);

            // Clone container to prevent viewport / max-height clipping
            const clone = originalElement.cloneNode(true);
            const btnGroup = clone.querySelector('#btnDownloadPurchasePdf')?.parentElement;
            if (btnGroup) btnGroup.remove();

            clone.querySelectorAll('.table-responsive').forEach(el => {
                el.style.maxHeight = 'none';
                el.style.overflow = 'visible';
            });

            // Create temporary offscreen container for html2pdf rendering
            const pdfWrapper = document.createElement('div');
            pdfWrapper.style.position = 'absolute';
            pdfWrapper.style.left = '-9999px';
            pdfWrapper.style.top = '0';
            pdfWrapper.style.width = '1050px';
            pdfWrapper.style.background = '#ffffff';
            pdfWrapper.style.padding = '20px';
            pdfWrapper.appendChild(clone);
            document.body.appendChild(pdfWrapper);

            function cleanup() {
                if (pdfWrapper && pdfWrapper.parentNode) {
                    pdfWrapper.parentNode.removeChild(pdfWrapper);
                }
                btn.html(originalHtml).prop('disabled', false);
            }

            function printPurchaseFallback() {
                const printWindow = window.open('', '_blank', 'width=1100,height=800');
                const styles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(s => s.outerHTML).join('');
                
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Purchase Order ${voucherNo}</title>
                        ${styles}
                        <style>
                            body { padding: 25px; background: #fff !important; }
                            .table-responsive { max-height: none !important; overflow: visible !important; }
                            #btnDownloadPurchasePdf, #btnPrintPurchase { display: none !important; }
                        </style>
                    </head>
                    <body>
                        ${clone.outerHTML}
                        <script>
                            setTimeout(() => { window.print(); window.close(); }, 400);
                        <\/script>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                cleanup();
            }

            function triggerHtml2Pdf() {
                const opt = {
                    margin:       [0.3, 0.3, 0.3, 0.3],
                    filename:     `Purchase_Order_${voucherNo}.pdf`,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, logging: false },
                    jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
                };

                html2pdf().set(opt).from(clone).save().then(() => {
                    cleanup();
                }).catch(() => {
                    printPurchaseFallback();
                });
            }

            if (typeof html2pdf !== 'undefined') {
                triggerHtml2Pdf();
            } else {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                script.onload = triggerHtml2Pdf;
                script.onerror = printPurchaseFallback;
                document.head.appendChild(script);
            }
        });

        // Print Handler
        $(document).on('click', '#btnPrintPurchase', function (e) {
            e.preventDefault();
            const voucherNo = $('#purchaseDetailsVoucherNo').text().trim() || 'Purchase-Order';
            const originalElement = document.querySelector('.purchase-details-content');
            if (!originalElement) return;

            const clone = originalElement.cloneNode(true);
            const btnGroup = clone.querySelector('#btnDownloadPurchasePdf')?.parentElement;
            if (btnGroup) btnGroup.remove();

            clone.querySelectorAll('.table-responsive').forEach(el => {
                el.style.maxHeight = 'none';
                el.style.overflow = 'visible';
            });

            const printWindow = window.open('', '_blank', 'width=1100,height=800');
            const styles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(s => s.outerHTML).join('');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Purchase Order ${voucherNo}</title>
                    ${styles}
                    <style>
                        @page {
                            size: landscape;
                            margin: 6mm;
                        }
                        html, body {
                            background: #ffffff !important;
                            padding: 0 !important;
                            margin: 0 !important;
                            font-size: 88% !important;
                            line-height: 1.2 !important;
                        }
                        .table-responsive {
                            max-height: none !important;
                            overflow: visible !important;
                        }
                        #btnDownloadPurchasePdf, #btnPrintPurchase {
                            display: none !important;
                        }
                        .mb-4 {
                            margin-bottom: 0.5rem !important;
                        }
                        .mb-3 {
                            margin-bottom: 0.4rem !important;
                        }
                        .pb-3 {
                            padding-bottom: 0.3rem !important;
                        }
                        .p-3 {
                            padding: 0.5rem !important;
                        }
                        .py-2 {
                            padding-top: 0.25rem !important;
                            padding-bottom: 0.25rem !important;
                        }
                        .card {
                            box-shadow: none !important;
                        }
                        * {
                            page-break-before: auto !important;
                            break-before: auto !important;
                            page-break-after: auto !important;
                            break-after: auto !important;
                        }
                    </style>
                </head>
                <body>
                    <div class="purchase-print-page">
                        ${clone.outerHTML}
                    </div>
                    <script>
                        setTimeout(() => { window.print(); window.close(); }, 400);
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        });

        let activeToggleCheckbox = null;

        // Intercept toggle click
        $(document).on('click', '.toggle-status', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const checkbox = $(this);
            const purchaseId = checkbox.data('id');
            const voucherNo = checkbox.attr('data-voucher') || ('#' + purchaseId);
            
            // Read status from data-online attribute (1 or 0)
            const isCurrentlyOnline = checkbox.attr('data-online') === '1';
            const targetOnlineState = !isCurrentlyOnline;

            // Preserve current checkbox state visually while modal is shown
            checkbox.prop('checked', isCurrentlyOnline);

            $('#onlineConfirmPurchaseId').val(purchaseId);
            activeToggleCheckbox = checkbox;

            if (targetOnlineState) {
                // Turning ON confirmation
                $('#onlineConfirmIconWrapper').attr('class', 'd-inline-flex align-items-center justify-content-center rounded-circle p-3 bg-primary-subtle text-primary');
                $('#onlineConfirmIcon').attr('class', 'bi bi-globe fs-2');
                $('#onlineConfirmTitle').text('{{ __("Are you sure?") }}');
                $('#onlineConfirmMessage').html(`{{ __("Do you want to make the products from voucher") }} <strong>${voucherNo}</strong> {{ __("available on the online shop?") }}`);
                $('#onlineConfirmYesBtn').attr('class', 'btn btn-primary px-4 py-2 fw-semibold rounded-2 d-inline-flex align-items-center justify-content-center');
                $('#onlineConfirmYesBtnText').text('{{ __("Yes, Make Online") }}');
            } else {
                // Turning OFF confirmation
                $('#onlineConfirmIconWrapper').attr('class', 'd-inline-flex align-items-center justify-content-center rounded-circle p-3 bg-danger-subtle text-danger');
                $('#onlineConfirmIcon').attr('class', 'bi bi-exclamation-triangle fs-2');
                $('#onlineConfirmTitle').text('{{ __("Are you sure?") }}');
                $('#onlineConfirmMessage').html(`{{ __("Do you want to remove the products from voucher") }} <strong>${voucherNo}</strong> {{ __("from the online shop?") }}`);
                $('#onlineConfirmYesBtn').attr('class', 'btn btn-danger px-4 py-2 fw-semibold rounded-2 d-inline-flex align-items-center justify-content-center');
                $('#onlineConfirmYesBtnText').text('{{ __("Yes, Make Offline") }}');
            }

            $('#onlineProductConfirmModal').modal('show');
        });

        // Handle "Yes" confirmation click
        $(document).on('click', '#onlineConfirmYesBtn', function (e) {
            e.preventDefault();

            const purchaseId = $('#onlineConfirmPurchaseId').val();
            const yesBtn = $(this);
            const noBtn = $('#onlineConfirmNoBtn');

            if (!purchaseId) return;

            // Set loading state
            yesBtn.prop('disabled', true);
            noBtn.prop('disabled', true);
            if (activeToggleCheckbox) {
                activeToggleCheckbox.prop('disabled', true);
            }

            const originalBtnText = $('#onlineConfirmYesBtnText').text();
            $('#onlineConfirmYesBtnText').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("Processing...") }}');

            $.ajax({
                url: '/shop/purchase-product/is-online/' + purchaseId,
                type: 'GET',
                success: function (response) {
                    yesBtn.prop('disabled', false);
                    noBtn.prop('disabled', false);
                    $('#onlineConfirmYesBtnText').text(originalBtnText);
                    $('#onlineProductConfirmModal').modal('hide');

                    if (activeToggleCheckbox) {
                        activeToggleCheckbox.prop('disabled', false);
                    }

                    if (response.status) {
                        if (activeToggleCheckbox) {
                            activeToggleCheckbox.prop('checked', response.is_online);
                            activeToggleCheckbox.attr('data-online', response.is_online ? '1' : '0');
                        }
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || '{{ __("Something went wrong!") }}');
                    }
                },
                error: function (xhr) {
                    yesBtn.prop('disabled', false);
                    noBtn.prop('disabled', false);
                    $('#onlineConfirmYesBtnText').text(originalBtnText);
                    $('#onlineProductConfirmModal').modal('hide');

                    if (activeToggleCheckbox) {
                        activeToggleCheckbox.prop('disabled', false);
                        const isCurrentlyOnline = activeToggleCheckbox.attr('data-online') === '1';
                        activeToggleCheckbox.prop('checked', isCurrentlyOnline);
                    }

                    const errorMsg = xhr.responseJSON?.message || '{{ __("Failed to update status. Please try again.") }}';
                    toastr.error(errorMsg);
                }
            });
        });

        // Handle single item online toggle inside Purchase Details Modal
        $(document).on('click', '.single-item-online-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const checkbox = $(this);
            const inwardProductId = checkbox.data('id');
            const isCurrentlyOnline = checkbox.attr('data-online') === '1';
            const hasBarcode = checkbox.attr('data-barcode') === '1';

            if (!isCurrentlyOnline && !hasBarcode) {
                checkbox.prop('checked', false);
                toastr.error('{{ __("Please generate the barcode for this product variant before enabling Sell Online.") }}');
                return false;
            }

            checkbox.prop('disabled', true);

            $.ajax({
                url: '/shop/purchase-product/toggle-item-online/' + inwardProductId,
                type: 'GET',
                success: function (response) {
                    checkbox.prop('disabled', false);

                    if (response.status) {
                        checkbox.prop('checked', response.is_online);
                        checkbox.attr('data-online', response.is_online ? '1' : '0');
                        toastr.success(response.message);

                        if (response.purchase_id && response.total_count !== undefined) {
                            const badge = $(`span.viewData[data-id="${response.purchase_id}"]`);
                            if (badge.length) {
                                if (response.online_count > 0) {
                                    badge.attr('class', 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1 viewData cursor-pointer');
                                    badge.html(`<i class="bi bi-globe me-1"></i>${response.online_count}/${response.total_count} {{ __('Online') }}`);
                                } else {
                                    badge.attr('class', 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 viewData cursor-pointer');
                                    badge.html(`<i class="bi bi-globe me-1"></i>0/${response.total_count} {{ __('Online') }}`);
                                }
                            }
                        }
                    } else {
                        checkbox.prop('checked', isCurrentlyOnline);
                        toastr.error(response.message || '{{ __("Please generate the barcode for this product variant before enabling Sell Online.") }}');
                    }
                },
                error: function (xhr) {
                    checkbox.prop('disabled', false);
                    checkbox.prop('checked', isCurrentlyOnline);
                    const errorMsg = xhr.responseJSON?.message || '{{ __("Please generate the barcode for this product variant before enabling Sell Online.") }}';
                    toastr.error(errorMsg);
                }
            });
        });

        // Window Keydown listener for quick shortcuts (F3, F4, F8, F9, Alt keys)
        window.addEventListener('keydown', function (e) {
            const key = e.key ? e.key.toLowerCase() : '';
            const code = e.code || '';

            // F3 or Alt+V -> Voucher No
            if (e.key === 'F3' || (e.altKey && (key === 'v' || code === 'KeyV'))) {
                e.preventDefault();
                $('input[name="inward_voucher_no"]').focus().select();
            }
            // F4 or Alt+C -> Challan No
            if (e.key === 'F4' || (e.altKey && (key === 'c' || code === 'KeyC'))) {
                e.preventDefault();
                $('#inward_challan_no').focus().select();
            }
            // F8 or Alt+S -> Submit Purchase
            if (e.key === 'F8' || (e.altKey && (key === 's' || code === 'KeyS'))) {
                e.preventDefault();
                $('#saveButton').click();
            }
            // F9 -> Toggle List/Entry
            if (e.key === 'F9') {
                e.preventDefault();
                $('#list-btn').click();
            }
            // Alt + K -> Kachi List Filter Toggle (when Purchase List is visible)
            if (e.altKey && (key === 'k' || code === 'KeyK' || e.keyCode === 75)) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const $inwardList = $("#inwardList");
                const $inwardProductList = $("#inwardProductList");

                if ($inwardList.is(':visible') || !$inwardProductList.is(':visible')) {
                    if (typeof window.toggleKachiListFilter === 'function') {
                        window.toggleKachiListFilter();
                    }
                }
                return false;
            }
        }, { capture: true });

        // Enter key traversal across all form elements (excluding buttons/textareas)
        $(document).on('keydown', '#itemForm input, #itemForm select', function (e) {
            if (e.key === 'Enter') {
                if ($(this).is('textarea') || $(this).attr('type') === 'submit' || $(this).attr('name') === 'inward_voucher_no' || $(this).attr('id') === 'inward_challan_no') {
                    // Let default handler process Voucher/Challan enter key for AJAX loading
                    return;
                }
                e.preventDefault();
                
                let $inputs = $('#itemForm').find('input:not([readonly]):not([disabled]):not([type="hidden"]), select:not([readonly]):not([disabled])');
                let index = $inputs.index(this);
                
                if (index > -1 && index < $inputs.length - 1) {
                    let next = $inputs.eq(index + 1);
                    if (next.hasClass('select2-hidden-accessible')) {
                        next.select2('open');
                    } else {
                        next.focus().select();
                    }
                } else if (index === $inputs.length - 1) {
                    $('#saveButton').focus();
                }
            }
        });

        // Focus next field on select2 close selection
        $(document).on('select2:close', '#itemForm select', function () {
            let $inputs = $('#itemForm').find('input:not([readonly]):not([disabled]):not([type="hidden"]), select:not([readonly]):not([disabled])');
            let index = $inputs.index(this);
            if (index > -1 && index < $inputs.length - 1) {
                let next = $inputs.eq(index + 1);
                setTimeout(() => {
                    if (next.hasClass('select2-hidden-accessible')) {
                        next.select2('open');
                    } else {
                        next.focus().select();
                    }
                }, 50);
            }
        });

        // Highlight active row during editing
        $(document).on('focusin', '#inwardProductTable tbody input, #inwardProductTable tbody select', function() {
            $(this).closest('tr').addClass('active-row-focus');
        });
        $(document).on('focusout', '#inwardProductTable tbody input, #inwardProductTable tbody select', function() {
            $(this).closest('tr').removeClass('active-row-focus');
        });

    </script>
@endpush