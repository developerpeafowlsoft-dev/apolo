@extends('layouts.app')
@section('header-title', __('Inward Product'))
@section('header-subtitle')
    <span class="d-inline-flex align-items-center gap-2 mt-1" style="font-size: 11px; color: #64748b;">
        <i class="fa-solid fa-keyboard text-primary"></i>
        <span>Hotkeys:</span>
        <kbd class="px-1 py-0.5 bg-white text-dark border border-secondary rounded shadow-sm font-monospace fw-bold" style="font-size: 10px; border-color: #cbd5e1 !important;">Alt + I</kbd> Add Item
        <span class="text-muted">|</span>
        <kbd class="px-1 py-0.5 bg-white text-dark border border-secondary rounded shadow-sm font-monospace fw-bold" style="font-size: 10px; border-color: #cbd5e1 !important;">Alt + D</kbd> Add Design
        <span class="text-muted">|</span>
        <kbd class="px-1 py-0.5 bg-white text-dark border border-secondary rounded shadow-sm font-monospace fw-bold" style="font-size: 10px; border-color: #cbd5e1 !important;">F1</kbd> Add Row
        <span class="text-muted">|</span>
        <kbd class="px-1 py-0.5 bg-warning text-dark border border-warning rounded shadow-sm font-monospace fw-bold" style="font-size: 10px; cursor: pointer;" id="badgeAltKShortcut" title="{{ __('Toggle Kachi Entry Mode (Alt + K)') }}" onclick="toggleKachiEntryMode()">Alt + K</kbd> <span id="lblKachiHotkeyText" style="cursor: pointer;" onclick="toggleKachiEntryMode()">Kachi Entry</span>
    </span>
@endsection
@section('content')

    <div>
        <div>
            <div id="alertBoxPurchase"></div>
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
                <form id="searchForm"
                      class="d-none align-items-center justify-content-end gap-3 mb-3 border-bottom pb-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group" style="max-width: 400px">
                            <input type="text" name="search" class="form-control"
                                   placeholder="{{ __('Search by voucher number, challan number, or party name.') }}"
                                   value="{{ request('search') }}">
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" id="columnToggleDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <i class="bi bi-columns-gap"></i> Columns
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="columnToggleDropdown" style="min-width: 240px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                                <h6 class="dropdown-header px-0 pt-0 pb-2 border-bottom mb-2 text-dark fw-bold">Show/Hide Columns</h6>
                                <li>
                                    <div class="form-check">
                                        <input class="form-check-input column-toggle-chk" type="checkbox" value="col-voucher" id="chkColVoucher" checked>
                                        <label class="form-check-label font-monospace small" for="chkColVoucher">Voucher / Bill</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input column-toggle-chk" type="checkbox" value="col-challan" id="chkColChallan" checked>
                                        <label class="form-check-label font-monospace small" for="chkColChallan">Challan Details</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input column-toggle-chk" type="checkbox" value="col-supplier" id="chkColSupplier" checked>
                                        <label class="form-check-label font-monospace small" for="chkColSupplier">Supplier / Party</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input column-toggle-chk" type="checkbox" value="col-items" id="chkColItems" checked>
                                        <label class="form-check-label font-monospace small" for="chkColItems">Items (Products & Prices)</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input column-toggle-chk" type="checkbox" value="col-gstno" id="chkColGstNo" checked>
                                        <label class="form-check-label font-monospace small" for="chkColGstNo">GST No.</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input column-toggle-chk" type="checkbox" value="col-net" id="chkColNet" checked>
                                        <label class="form-check-label font-monospace small" for="chkColNet">Net Value</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input column-toggle-chk" type="checkbox" value="col-tax" id="chkColTax" checked>
                                        <label class="form-check-label font-monospace small" for="chkColTax">Tax (GST)</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input column-toggle-chk" type="checkbox" value="col-total" id="chkColTotal" checked>
                                        <label class="form-check-label font-monospace small" for="chkColTotal">Final Total</label>
                                    </div>
                                </li>
                            </ul>
                        </div>
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
                        @include('shop.inward-product.partials.inward-list',['inwardLists' => $inwardLists])
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Barcode Generate Modal --}}
    @include('shop.inward-product.partials.barcode-modal')

    {{-- View Inward Items Modal --}}
    @include('shop.inward-product.partials.view-items-modal')

    {{-- Design Master Modal Short Key --}}
    @include('shop.components-modal.design-master-modal')

    {{-- Design Master Modal Short Key --}}
    @include('shop.components-modal.item-master-modal')

    <div class="card shadow border-0 table-floating d-none" id="oldPriceTable">

        <div class="card-header bg-dark text-white py-1 d-flex justify-content-between align-items-center">
            <small>Last Five Rate</small>

            <button type="button" class="btn-close btn-close-white btn-sm" id="closeOldPrice"></button>
        </div>

        <div class="card-body p-0">
            <div class="table-box">
                <table class="table table-bordered table-sm mb-0 text-center align-middle">
                    <thead class="table-light">
                    <tr id="dateHeaderRow">
                    </tr>
                    </thead>
                    <tbody>
                    <tr id="buyPriceRow">

                    </tr>
                    <tr id="mrpRow">
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/holdon/HoldOn.min.css') }}" type="text/css"/>
    <style>
        #inwardList table tr:not(:first-child):not(.ui-datepicker-calendar tr) {
            opacity: 1 !important;
            display: table-row !important;
            animation: none !important;
        }

        #barcode-generate-modal table tr:not(:first-child):not(.ui-datepicker-calendar tr),
        #inward-items-view-modal table tr:not(:first-child):not(.ui-datepicker-calendar tr) {
            opacity: 1 !important;
            display: table-row !important;
            animation: none !important;
        }

        #inwardProductTable,
        #inwardProductTable tbody,
        #inwardProductTable tr,
        #inwardProductTable td {
            overflow: visible !important;
        }
        .table-box {
            overflow: visible !important;
        }
        #inwardProductTable td {
            position: relative !important;
        }

        .dropdown-suggestions, .dropdown-designNo, .dropdown-taxCode {
            position: absolute !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            z-index: 100000 !important;
            width: 100% !important;
            min-width: 280px !important;
            display: none;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.05) !important;
            border-radius: 6px !important;
            margin-top: 4px !important;
            padding: 4px 0 !important;
        }

        .dropdown-suggestions ul, .dropdown-designNo ul, .dropdown-taxCode ul {
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
            width: 100% !important;
        }

        .dropdown-suggestions li, .dropdown-designNo li, .dropdown-taxCode li {
            padding: 8px 14px !important;
            cursor: pointer !important;
            border: none !important;
            font-size: 13px !important;
            color: #1e293b !important;
            transition: all 0.15s ease !important;
            background: transparent !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .dropdown-suggestions li:last-child, .dropdown-designNo li:last-child, .dropdown-taxCode li:last-child {
            border-bottom: none !important;
        }

        .dropdown-suggestions li.active, .dropdown-designNo li.active, .dropdown-taxCode li.active {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        .dropdown-suggestions li:hover, .dropdown-designNo li:hover, .dropdown-taxCode li:hover {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
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

        /* SL */
        #inwardProductTable td:nth-child(2) {
            min-width: 180px;
        }

        /* Item */
        #inwardProductTable td:nth-child(3) {
            min-width: 120px;
        }

        /* Design No */
        #inwardProductTable td:nth-child(4),
        #inwardProductTable td:nth-child(5) {
            min-width: 100px;
        }

        /* Color/Size */
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
        $(function () {
            $("#oldPriceTable").draggable({
                handle: ".card-header"
            });

            // Alt + I (Item), Alt + D (Design), Alt + K (Kachi Entry) shortcut listeners - Capture Phase
            window.addEventListener('keydown', function(e) {
                const key = e.key ? e.key.toLowerCase() : '';
                const code = e.code || '';

                if (e.altKey && (code === 'KeyI' || key === 'i' || e.keyCode === 73)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    showCustomLoader('center');
                    modelItemMasterDataLoad();
                    return false;
                }
                if (e.altKey && (code === 'KeyD' || key === 'd' || e.keyCode === 68)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    showCustomLoader('center');
                    modelDesignMasterDataLoad();
                    return false;
                }
                if (e.altKey && (code === 'KeyK' || key === 'k' || e.keyCode === 75)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    const $inwardList = $("#inwardList");
                    const $inwardProductList = $("#inwardProductList");

                    if ($inwardList.is(':visible') || !$inwardProductList.is(':visible')) {
                        if (typeof window.toggleKachiListFilter === 'function') {
                            window.toggleKachiListFilter();
                        }
                    } else {
                        if (typeof window.toggleKachiEntryMode === 'function') {
                            window.toggleKachiEntryMode();
                        }
                    }
                    return false;
                }
            }, { capture: true, passive: false });
        });

        // Kachi Entry Mode Toggle & Sequence Logic
        window.isKachiModeActive = false;
        let lastKachiToggleTime = 0;

        window.fetchNextKachiSequence = function() {
            $.ajax({
                url: "{{ route('shop.inwardProduct.nextKachiSequence') }}",
                type: 'GET',
                success: function(res) {
                    if (res.status && res.sequence) {
                        if (window.isKachiModeActive) {
                            $('#inward_voucher_no').val(res.sequence);
                            $('#inward_challan_no').val(res.sequence);
                        }
                    }
                },
                error: function(err) {
                    console.error('Error fetching Kachi sequence:', err);
                }
            });
        };

        window.toggleKachiEntryMode = function() {
            window.isKachiModeActive = !window.isKachiModeActive;
            lastKachiToggleTime = Date.now();

            const $banner = $('#kachiEntryBanner');
            const $submitBtn = $('#saveButton');

            if (window.isKachiModeActive) {
                toastr.info("{{ __('This is a Kachi Entry form.') }}", "{{ __('Kachi Entry Mode') }}");
                $banner.removeClass('d-none').addClass('d-flex');
                if ($submitBtn.length) {
                    $submitBtn.removeClass('btn-primary').addClass('btn-warning text-dark fw-bold')
                              .html('<i class="bi bi-file-earmark-diff me-1"></i> {{ __("Save Kachi Entry") }}');
                }
                window.fetchNextKachiSequence();
            } else {
                toastr.info("{{ __('Kachi Entry Mode Disabled. Normal Inward Entry Mode Active.') }}", "{{ __('Normal Mode') }}");
                $banner.addClass('d-none').removeClass('d-flex');
                if ($submitBtn.length) {
                    $submitBtn.removeClass('btn-warning text-dark fw-bold').addClass('btn-primary')
                              .html('{{ __("Submit") }}');
                }
                // Requirement 5 & 13: Clear ONLY Voucher No and Challan No when Kachi Mode is turned OFF
                $('#inward_voucher_no').val('');
                $('#inward_challan_no').val('');
            }
        };

        // Kachi Entry List Mode Filter Logic
        window.isKachiListActive = false;

        window.toggleKachiListFilter = function() {
            window.isKachiListActive = !window.isKachiListActive;
            lastKachiToggleTime = Date.now();

            const $banner = $('#kachiListBanner');

            if (window.isKachiListActive) {
                toastr.info("{{ __('Showing Kachi Entries Only') }}", "{{ __('Kachi Entry List Active') }}");
                $banner.removeClass('d-none').addClass('d-flex');
            } else {
                toastr.info("{{ __('Showing Normal Inward Entries Only') }}", "{{ __('Normal Mode Active') }}");
                $banner.addClass('d-none').removeClass('d-flex');
            }

            refreshInwardList();
        };

        window.addEventListener('keyup', function(e) {
            const key = e.key ? e.key.toLowerCase() : '';
            const code = e.code || '';
            if ((code === 'KeyK' || key === 'k' || e.keyCode === 75) && (Date.now() - lastKachiToggleTime < 500)) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, { capture: true, passive: false });
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
            $('#inwardProductTable tbody tr:last').find('input.item').focus();

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
    <script src="{{ asset('assets/scripts/shortKey.js') }}"></script>
    <script src="{{ asset('assets/css/holdon/HoldOn.min.js') }}"></script>

    {{-- Short Modal Open Code Design Master--}}
    @include('shop.components-modal.master-modal.design-master-modal-script')
    {{-- Short Modal Open Code Item Master--}}
    @include('shop.components-modal.master-modal.item-master-modal-script')

    <script>

        function oldPriceTableClear(){
            $('#oldPriceTable').addClass('d-none');

            // clear table data
            $('#dateHeaderRow').html('<th></th>');
            $('#buyPriceRow').html('<th class="text-start bg-light">Buy Price</th>');
            $('#mrpRow').html('<th class="text-start bg-light">MRP</th>');
        }

        $(document).on('click', '#closeOldPrice', function () {
            oldPriceTableClear();
        });

        $(document).on('click', '#modelClose', function () {
            const modalIdName = $(this).data('modal-name');

            if (modalIdName === 'design-master-modal') {
                // $('#inwardProductTable tbody').html('');
                // window.createRow();
            }
            $("#" + modalIdName).modal("hide");
        });

        $(document).ready(function () {
            const $list_btn = $("#list-btn");
            const $create_btn = $("#create-modal-btn");
            const $inwardProductList = $("#inwardProductList");
            const $inwardList = $("#inwardList");
            const $searchForm = $("#searchForm");

            const $create_record_btn = $("#create-record-btn");

            $create_record_btn.on('click', function () {
                editData = false;
                $('#saveButton').show();
                $('#alertBoxPurchase').removeClass('alert alert-danger').text('');
                clearValidationBorders();
                $create_record_btn.hide();
                resetFormInward();
            });


            // Default Hide inwardList
            $inwardList.hide();
            $create_record_btn.hide();

            $list_btn.on('click', function () {
                $('#saveButton').show();
                $('#alertBoxPurchase').removeClass('alert alert-danger').text('');
                showCustomLoader();
                $inwardProductList.toggle();
                $inwardList.toggle();

                if ($inwardList.is(":visible")) {
                    refreshInwardList()
                    $list_btn.text('Back');
                    $create_btn.hide();
                    $create_record_btn.hide();
                    $searchForm.removeClass('d-none').addClass('d-flex')[0].reset();
                    oldPriceTableClear()

                } else {
                    $list_btn.text('Inward List');
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

        // New Code

        $(document).on('click', '.editData', function () {

            clearValidationBorders();
            editData = true;

            var id = $(this).data('id');
            showCustomLoader();

            if (id !== '') {

                resetFormInward();

                const $inwardProductList = $("#inwardProductList");
                const $inwardList = $("#inwardList");
                const $list_btn = $("#list-btn");
                const $create_btn = $("#create-modal-btn");
                const $create_record_btn = $("#create-record-btn");
                const $searchForm = $("#searchForm");

                $inwardProductList.toggle();
                $inwardList.toggle();

                if ($inwardList.is(":visible")) {
                    refreshInwardList()
                    $list_btn.text('Back');
                    $create_btn.hide();
                } else {
                    editDataLoad(id)
                    $list_btn.text('Inward List');
                    $create_btn.show();
                    $create_record_btn.show();
                    $searchForm.removeClass('d-flex').addClass('d-none')[0].reset();
                }
            } else {
                toastr.error("Failed to load item data.");
            }


            HoldOn.close();
        });

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
            window.createRow();
            // Footer Data Clear

            $("#inward_acc_purchaser").empty().append('<option value="">{{ __("Select Purchaser") }}</option>');
            $("#inward_acc_season").empty().append('<option value="">{{ __("Select Season") }}</option>');
            $("#inward_acc_agent").empty().append('<option value="">{{ __("Select a Agent") }}</option>');
            $("#inward_acc_transport").empty().append('<option value="">{{ __("Select a Transport") }}</option>');
            $("#inward_acc_delivery_by").empty().append('<option value="">{{ __("Select a Delivery By") }}</option>');

            if (window.isKachiModeActive) {
                // If Kachi mode is active when form resets (e.g. after successful save), fetch the next sequence number for the next Kachi entry (Requirement 9)
                window.fetchNextKachiSequence();
            } else {
                $('#inward_voucher_no').val('');
                $('#inward_challan_no').val('');
            }
        }

        function editDataLoad(id) {
            $.ajax({
                url: `/shop/inward-product/${id}/edit`,
                type: 'GET',
                success: function (res) {
                    // Header Value Set
                    if(res.inwardData.is_purchase){
                        $('#saveButton').hide();
                        $('#alertBoxPurchase').addClass('alert alert-danger').text('already purchased cannot edited');
                    }else{
                        $('#saveButton').show();
                        $('#alertBoxPurchase').removeClass('alert alert-danger').text('');
                    }

                    $('#inward_invoice_id').val(id);

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
                        createRow(); // 👈 new row create
                        fillRowData(product, index + 1); // 👈 row number pass
                    });

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
                    $('#inward_acc_freight_amount').val(res.inwardData.inward_acc_freight_amount);
                    $('#inward_acc_parcel_amount').val(res.inwardData.inward_acc_parcel_amount);
                    $('#inward_acc_amt_with_gst').val(res.inwardData.inward_acc_amt_with_gst);

                },
                error: function () {
                    toastr.error("Failed to load item data.");
                }
            });
        }

        // Close

        $(document).on('click', '.deleteConfirm', function (e) {
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
                        success: function (response) {
                            toastr.success("Inward Product deleted successfully");
                            refreshInwardList(); // refresh the table
                        },
                        error: function (xhr) {
                            toastr.error(xhr.responseJSON?.error || "Failed to delete item");
                        }
                    });
                }
            });
        });

        function fillRowData(product, sl) {

            let $row = $(`#inwardProductTable tbody tr[data-sl="${sl}"]`);

            // ---------- BASIC INPUTS ----------
            $row.find('input[name="inwardProductId[]"]').val(product.id);
            $row.find('input[name="qty[]"]').val(product.quantity);
            $row.find('input[name="purcRate[]"]').val(product.buy_price);
            $row.find('input[name="amount[]"]').val(product.price);
            $row.find('input[name="disc[]"]').val(product.discount_price);
            $row.find('input[name="mrp[]"]').val(product.mrp);
            $row.find('input[name="mark_up[]"]').val(product.mark_up);
            $row.find('input[name="mark_down[]"]').val(product.mark_down);
            $row.find('input[name="netPurcRate[]"]').val(product.net_purc_rate);

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

        function applyColumnVisibility() {
            $('.column-toggle-chk').each(function() {
                var colClass = $(this).val();
                var state = localStorage.getItem(colClass);
                if (state === 'hide') {
                    $(this).prop('checked', false);
                    $('.' + colClass).hide();
                } else {
                    $(this).prop('checked', true);
                    $('.' + colClass).show();
                }
            });
        }

        $(document).on('change', '.column-toggle-chk', function() {
            var colClass = $(this).val();
            if ($(this).is(':checked')) {
                $('.' + colClass).show();
                localStorage.setItem(colClass, 'show');
            } else {
                $('.' + colClass).hide();
                localStorage.setItem(colClass, 'hide');
            }
        });

        function refreshInwardList() {
            let isKachiVal = window.isKachiListActive ? 1 : 0;
            let searchVal = $('#searchForm input[name="search"]').val() || '';
            loadInwardList("{{ route('shop.inwardProduct.list') }}", { is_kachi: isKachiVal, search: searchVal });
        }

        $(document).on('click', '.pagination a, #pagination-links a', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            loadInwardList(url);
        });

        $(document).ready(function () {
            // Initial column visibility run
            applyColumnVisibility();
        });

        // Common function for loading Inward List with mode & search parameters
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
                    applyColumnVisibility();
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
                    loadInwardList("{{ route('shop.inwardProduct.list') }}", { search: search, is_kachi: window.isKachiListActive ? 1 : 0 });
                }, 300); // 300ms delay
            });
        });


        // Barcode Garnette
        $(document).ready(function () {
            function inwardProductLoad(id){
                $.ajax({
                    url: '{{route('shop.inwardProductByBarcode.productLoad',':id')}}'.replace(':id',id),
                    data: id,
                    type: 'GET',
                    success: function (response) {
                        console.log(response)
                        let rows = '';
                        let sl = 1;
                        let buttonHtml;
                        const dataBarcode = response.inwardBarcodeData;

                        let urlTemplate = "{{ route(
                            'shop.productByBarcodeGet.barcodeLoad',
                            ['inwardInvoiceId' => '__invoice__',
                             'inwardProductId' => '__inwardProduct__',
                             'productId' => '__product__']
                        ) }}";

                        if(dataBarcode && dataBarcode.inward_product){

                            $("#voucherNumber").text(dataBarcode.inward_voucher_no);

                            $.each(dataBarcode.inward_product, function(index, item){
                                let inwardInvoiceId = dataBarcode.id;
                                let inwardProductId = item.id;
                                let productId = item.product_id;

                                let viewUrl = urlTemplate
                                    .replace('__invoice__', inwardInvoiceId)
                                    .replace('__inwardProduct__', inwardProductId)
                                    .replace('__product__', productId);

                                let maxQty = item.quantity - (item.barcodes_count || 0);
                                let statusHtml;

                                if (item.barcodes_count === 0) {
                                    statusHtml = `<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded" style="font-size:11px;"><i class="bi bi-hourglass-split me-1"></i>Pending</span>`;
                                    
                                    buttonHtml = `
                                        <button type="button" class="btn btn-sm btn-success productWiseBarcodeGenerate d-flex align-items-center justify-content-center gap-1.5 py-1.5 rounded w-100"
                                            data-inwardinvoiceid="${dataBarcode.id}"
                                            data-inwardproductid="${item.id}"
                                            data-productid="${item.product_id}"
                                            data-maxqty="${maxQty}"
                                            style="font-size: 11.5px; font-weight: 600;">
                                              <i class="bi bi-cpu"></i> {{__('Generate')}}
                                        </button>`;
                                } else if (item.barcodes_count < item.quantity) {
                                    statusHtml = `<span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded" style="font-size:11px;"><i class="bi bi-info-circle-fill me-1"></i>Partial (${item.barcodes_count}/${item.quantity})</span>`;
                                    
                                    buttonHtml = `
                                        <div class="d-flex flex-column gap-1">
                                            <a href="${viewUrl}" class="btn btn-sm btn-action-outline-primary d-flex align-items-center justify-content-center gap-1.5 py-1 rounded"
                                               target="_blank">
                                                  <i class="bi bi-printer"></i> {{__('Print')}} (${item.barcodes_count})
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success productWiseBarcodeGenerate d-flex align-items-center justify-content-center gap-1.5 py-1 rounded"
                                                data-inwardinvoiceid="${dataBarcode.id}"
                                                data-inwardproductid="${item.id}"
                                                data-productid="${item.product_id}"
                                                data-maxqty="${maxQty}"
                                                style="font-size: 11px; font-weight: 600;">
                                                  <i class="bi bi-cpu"></i> Generate More (${maxQty})
                                            </button>
                                        </div>`;
                                } else {
                                    statusHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded" style="font-size:11px;"><i class="bi bi-check-circle-fill me-1"></i>Generated</span>`;
                                    
                                    buttonHtml = `
                                        <a href="${viewUrl}" class="btn btn-sm btn-action-outline-primary d-flex align-items-center justify-content-center gap-1.5 py-1.5 rounded w-100"
                                           target="_blank">
                                              <i class="bi bi-printer"></i> {{__('Print')}} (${item.barcodes_count})
                                        </a>
                                    `;
                                }

                                let colorsStr = item.colors && item.colors.length 
                                    ? item.colors.map(c => `<span class="border px-1.5 py-0.5 rounded mr-1" style="font-size:10px; background-color: #f1f5f9; color: #475569 !important; display: inline-block; font-weight: 600;">${c.name}</span>`).join(' ') 
                                    : '<span class="text-muted" style="font-size:11px;">-</span>';
                                
                                let sizesStr = item.sizes && item.sizes.length 
                                    ? item.sizes.map(s => `<span class="border px-1.5 py-0.5 rounded mr-1" style="font-size:10px; background-color: #f1f5f9; color: #1e293b !important; display: inline-block; font-weight: 600;">${s.name}</span>`).join(' ') 
                                    : '<span class="text-muted" style="font-size:11px;">-</span>';
                                
                                let taxStr = (item.hsn_master ? `<div style="font-size:11.5px;"><span class="text-secondary">HSN:</span> <strong>${item.hsn_master.hsn_code}</strong></div>` : '') + 
                                             (item.vat_tax ? `<div style="font-size:11.5px;" class="mt-0.5"><span class="text-secondary">GST:</span> <strong>${item.vat_tax.percentage}%</strong></div>` : '');

                                console.log(index, item)
                                rows += `
                             <tr>
                                 <td class="text-center text-secondary fw-semibold">${sl++}</td>
                                 <td>
                                     <div class="fw-bold text-dark" style="font-size: 13px;">${item.products?.name || ''}</div>
                                     <small class="text-muted d-block font-monospace mt-0.5" style="font-size:10.5px;">Code: ${item.products?.code || '-'}</small>
                                 </td>
                                 <td><span class="border font-monospace" style="font-size:11px; background-color: #f1f5f9; color: #1e293b !important; padding: 3px 6px; border-radius: 4px; display: inline-block; font-weight: 600;">${item.design_master?.design_number || '-'}</span></td>
                                 <td>
                                     <div class="d-flex flex-column gap-1">
                                         <div><span class="text-muted" style="font-size:10px;">Color:</span> ${colorsStr}</div>
                                         <div><span class="text-muted" style="font-size:10px;">Size:</span> ${sizesStr}</div>
                                     </div>
                                 </td>
                                 <td class="text-center font-monospace fw-bold">${item.quantity || '0'}</td>
                                 <td>
                                     <div style="font-size: 12px;"><span class="text-muted">Buy:</span> <strong class="font-monospace">₹${parseFloat(item.buy_price || 0).toFixed(2)}</strong></div>
                                     <div class="mt-0.5" style="font-size: 12px;"><span class="text-muted">MRP:</span> <strong class="font-monospace">₹${parseFloat(item.mrp || 0).toFixed(2)}</strong></div>
                                 </td>
                                 <td>${taxStr || '-'}</td>
                                 <td class="font-monospace fw-semibold text-end">₹${parseFloat(item.net_purc_rate || 0).toFixed(2)}</td>
                                 <td class="text-center">${statusHtml}</td>
                                 <td>
                                     ${buttonHtml}
                                 </td>
                             </tr>`;
                            });

                            $("#barcodeTableBody").html(rows);
                            $("#barcode-generate-modal").modal("show");
                        }
                    },

                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.error || "Failed to delete item");
                    }
                });
            }

            $(document).on('click', '.barcodeGarnette', function () {
                showCustomLoader();
                const id = $(this).data('id');
                if (id !== 0 && id !== '') {
                    $("#barcodeTableBody").empty();
                    inwardProductLoad(id)
                }

                HoldOn.close();
            });

            // Product Wise Barcode Generate

            function productWiseBarcodeGenerate(inwardInvoiceId, inwardProductId, productId, qty){
                $.ajax({
                    url: '{{route('shop.inwardProductByBarcode.productWiseBarcodeGenerate',[':inwardInvoiceId',':inwardProductId',':productId'])}}'.replace(':inwardInvoiceId',inwardInvoiceId).replace(':inwardProductId',inwardProductId).replace(':productId',productId),
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        qty: qty
                    },
                    success: function (response) {
                        HoldOn.close();
                        if (response.success){
                            toastr.success(response.success);
                            inwardProductLoad(inwardInvoiceId);
                        }
                    },

                    error: function (xhr) {
                        HoldOn.close();
                        toastr.error(xhr.responseJSON?.error || "Failed to generate barcode");
                    }
                });
            }

            $(document).on('click', '.productWiseBarcodeGenerate', function (e) {
                e.preventDefault();
                let btn = $(this);
                let inwardInvoiceId = btn.data('inwardinvoiceid');
                let inwardProductId = btn.data('inwardproductid');
                let productId = btn.data('productid');
                let maxQty = parseInt(btn.data('maxqty') || 0);

                if (maxQty <= 0) {
                    toastr.warning("All barcodes for this product have already been generated.");
                    return;
                }

                Swal.fire({
                    title: 'Generate Barcodes',
                    text: `Enter the quantity of barcodes to generate (Max: ${maxQty}):`,
                    input: 'number',
                    inputAttributes: {
                        min: 1,
                        max: maxQty,
                        step: 1
                    },
                    inputValue: maxQty,
                    showCancelButton: true,
                    confirmButtonText: 'Generate',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    showLoaderOnConfirm: true,
                    didOpen: () => {
                        const input = Swal.getInput();
                        if (input) {
                            input.focus();
                            input.select();
                            
                            // Block non-numeric characters (allow navigation/editing keys)
                            input.addEventListener('keydown', function(e) {
                                // Allow: backspace (8), tab (9), enter (13), escape (27), delete (46)
                                if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
                                    // Allow: Ctrl+A / Cmd+A
                                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                                    // Allow: home, end, left, right
                                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                                    return;
                                }
                                // Stop other key events if it's not a digit (numeric keys and numpad keys)
                                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                                    e.preventDefault();
                                }
                            });

                            // Dynamically cap input values to maxQty
                            input.addEventListener('input', function() {
                                let val = parseInt(this.value);
                                if (val > maxQty) {
                                    this.value = maxQty;
                                }
                            });
                        }
                    },
                    preConfirm: (qty) => {
                        let parsedQty = parseInt(qty);
                        if (!parsedQty || parsedQty <= 0 || parsedQty > maxQty) {
                            Swal.showValidationMessage(`Please enter a valid quantity between 1 and ${maxQty}`);
                            return false;
                        }
                        return parsedQty;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        let qtyToGenerate = result.value;
                        showCustomLoader();
                        productWiseBarcodeGenerate(inwardInvoiceId, inwardProductId, productId, qtyToGenerate);
                    }
                });
            });
        });

        // View Items Modal Handler
        let vItemModalCurrentItems = [];
        let vItemModalCurrentVoucherId = null;

        $(document).on('click', '.view-voucher-items-trigger', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            const voucherId = $btn.attr('data-id');
            const voucherNo = $btn.attr('data-voucher-no') || '-';
            const partyName = $btn.attr('data-party-name') || '';
            const partyCode = $btn.attr('data-party-code') || '';
            const rawItemsData = $btn.attr('data-items');

            vItemModalCurrentVoucherId = voucherId;
            vItemModalCurrentItems = [];

            $("#vItemVoucherNo").text(voucherNo);
            if (partyName) {
                $("#vItemSupplierInfo").text(`Supplier: ${partyName} ${partyCode ? '(Code: ' + partyCode + ')' : ''}`);
            } else {
                $("#vItemSupplierInfo").text('');
            }

            $("#vItemSearchInput").val('');
            $("#vItemClearSearchBtn").addClass('d-none');
            $("#vItemsTableBody").empty();
            $("#vItemsEmptyState").addClass('d-none');
            $("#vItemsErrorState").addClass('d-none');
            $("#vItemsTable").removeClass('d-none');
            $("#vItemsLoader").removeClass('d-none');

            const modalEl = document.getElementById("inward-items-view-modal");
            if (modalEl) {
                let bsModal = bootstrap.Modal.getInstance(modalEl);
                if (!bsModal) {
                    bsModal = new bootstrap.Modal(modalEl, { keyboard: true });
                }
                bsModal.show();
            }

            if (rawItemsData) {
                try {
                    const parsedItems = JSON.parse(rawItemsData);
                    if (Array.isArray(parsedItems) && parsedItems.length > 0) {
                        vItemModalCurrentItems = parsedItems;
                        $("#vItemsLoader").addClass('d-none');
                        renderVoucherItemsModal(vItemModalCurrentItems);
                        return;
                    }
                } catch (err) {
                    console.warn('Failed to parse pre-embedded items, falling back to AJAX', err);
                }
            }

            fetchVoucherItemsModal(voucherId);
        });

        function fetchVoucherItemsModal(voucherId) {
            $("#vItemsLoader").removeClass('d-none');
            $("#vItemsTable").addClass('d-none');
            $("#vItemsEmptyState").addClass('d-none');
            $("#vItemsErrorState").addClass('d-none');

            const loadUrl = "{{ route('shop.productByBarcodeGet.barcodeLoad', ['inwardInvoiceId' => '__invoice__', 'inwardProductId' => '0', 'productId' => '0']) }}".replace('__invoice__', voucherId);

            $.ajax({
                url: loadUrl,
                type: 'GET',
                success: function (response) {
                    $("#vItemsLoader").addClass('d-none');
                    const dataBarcode = response.inwardBarcodeData;
                    if (dataBarcode && Array.isArray(dataBarcode.inward_product)) {
                        vItemModalCurrentItems = dataBarcode.inward_product.map(p => {
                            return {
                                id: p.id,
                                quantity: p.quantity,
                                buy_price: parseFloat(p.buy_price || 0),
                                mrp: parseFloat(p.mrp || 0),
                                net_purc_rate: parseFloat(p.net_purc_rate || (p.buy_price * p.quantity) || 0),
                                discount: parseFloat(p.discount_percentage || p.discount || 0),
                                products: {
                                    name: p.products?.name || 'Unknown Item',
                                    code: p.products?.code || p.products?.item_code || '-',
                                    barcode: p.barcode || p.products?.barcode || '',
                                    sku: p.products?.sku || ''
                                },
                                design_master: {
                                    design_number: p.design_master?.design_number || p.designMaster?.design_number || '-'
                                },
                                vat_tax: {
                                    percentage: parseFloat(p.vat_tax?.percentage || p.vatTax?.percentage || 0)
                                }
                            };
                        });
                        renderVoucherItemsModal(vItemModalCurrentItems);
                    } else {
                        vItemModalCurrentItems = [];
                        renderVoucherItemsModal([]);
                    }
                },
                error: function () {
                    $("#vItemsLoader").addClass('d-none');
                    $("#vItemsTable").addClass('d-none');
                    $("#vItemsErrorState").removeClass('d-none');
                }
            });
        }

        $("#vItemsRetryBtn").on('click', function () {
            if (vItemModalCurrentVoucherId) {
                fetchVoucherItemsModal(vItemModalCurrentVoucherId);
            }
        });

        function renderVoucherItemsModal(itemsList) {
            $("#vItemsTableBody").empty();
            $("#vItemTotalCount").text(itemsList.length);

            if (!itemsList || itemsList.length === 0) {
                $("#vItemsTable").addClass('d-none');
                $("#vItemsEmptyTitle").text("{{ __('No items found') }}");
                $("#vItemsEmptySubtitle").text("{{ __('This voucher does not contain any listed items.') }}");
                $("#vItemsEmptyState").removeClass('d-none');
                return;
            }

            $("#vItemsEmptyState").addClass('d-none');
            $("#vItemsTable").removeClass('d-none');

            let rowsHtml = '';
            itemsList.forEach((item, index) => {
                const prodName = item.products?.name || 'Unknown Item';
                const prodCode = item.products?.code || item.products?.item_code || '-';
                const barcode = item.products?.barcode || item.barcode || '';
                const sku = item.products?.sku || '';
                const designNo = item.design_master?.design_number || item.designMaster?.design_number || '-';
                const qty = item.quantity || 0;
                const buyPrice = parseFloat(item.buy_price || 0);
                const mrp = parseFloat(item.mrp || 0);
                const gstRate = item.vat_tax?.percentage || item.vatTax?.percentage || 0;
                const netAmount = parseFloat(item.net_purc_rate || (buyPrice * qty) || 0);

                rowsHtml += `
                    <tr>
                        <td class="text-center text-secondary fw-semibold">${index + 1}</td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 13px;">${escapeHtml(prodName)}</div>
                            ${prodCode !== '-' ? `<small class="text-muted font-monospace d-block" style="font-size:11px;">Code: ${escapeHtml(prodCode)}</small>` : ''}
                            ${barcode ? `<small class="text-muted font-monospace d-block" style="font-size:11px;">Barcode: ${escapeHtml(barcode)}</small>` : ''}
                            ${sku ? `<small class="text-muted font-monospace d-block" style="font-size:11px;">SKU: ${escapeHtml(sku)}</small>` : ''}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">${escapeHtml(designNo)}</span>
                        </td>
                        <td class="text-center font-monospace fw-bold">${qty}</td>
                        <td class="text-end font-monospace">₹${buyPrice.toFixed(2)}</td>
                        <td class="text-end font-monospace">₹${mrp.toFixed(2)}</td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info border border-info-subtle">${gstRate}%</span>
                        </td>
                        <td class="text-end font-monospace fw-bold text-success">₹${netAmount.toFixed(2)}</td>
                    </tr>
                `;
            });

            $("#vItemsTableBody").html(rowsHtml);
        }

        // Live Modal Search
        $("#vItemSearchInput").on('input', function () {
            const query = $(this).val().trim().toLowerCase();
            
            if (query.length > 0) {
                $("#vItemClearSearchBtn").removeClass('d-none');
            } else {
                $("#vItemClearSearchBtn").addClass('d-none');
            }

            if (!vItemModalCurrentItems || vItemModalCurrentItems.length === 0) return;

            if (query === '') {
                renderVoucherItemsModal(vItemModalCurrentItems);
                return;
            }

            const filtered = vItemModalCurrentItems.filter(item => {
                const name = (item.products?.name || '').toLowerCase();
                const code = (item.products?.code || item.products?.item_code || '').toLowerCase();
                const barcode = (item.products?.barcode || item.barcode || '').toLowerCase();
                const sku = (item.products?.sku || '').toLowerCase();
                const design = (item.design_master?.design_number || item.designMaster?.design_number || '').toLowerCase();

                return name.includes(query) || code.includes(query) || barcode.includes(query) || sku.includes(query) || design.includes(query);
            });

            if (filtered.length === 0) {
                $("#vItemsTableBody").empty();
                $("#vItemsTable").addClass('d-none');
                $("#vItemsEmptyTitle").text("{{ __('No matching items found') }}");
                $("#vItemsEmptySubtitle").text("{{ __('No items matched your search query.') }}");
                $("#vItemsEmptyState").removeClass('d-none');
            } else {
                renderVoucherItemsModal(filtered);
            }
        });

        $("#vItemClearSearchBtn").on('click', function () {
            $("#vItemSearchInput").val('').trigger('input').focus();
        });

        // Reset modal state on close
        $('#inward-items-view-modal').on('hidden.bs.modal', function () {
            $("#vItemSearchInput").val('');
            $("#vItemClearSearchBtn").addClass('d-none');
            $("#vItemsTableBody").empty();
            vItemModalCurrentItems = [];
            vItemModalCurrentVoucherId = null;
        });

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

    </script>
@endpush