@extends('layouts.app')
@section('header-title', __('Purchase Product'))
@section('content')
    <div>
        <div class="purchase-hotkeys-bar d-flex align-items-center justify-content-between px-3 mb-2">
            <div class="d-flex align-items-center gap-2.5 flex-wrap" style="font-size: 11px; color: #475569;">
                <span class="text-secondary fw-bold text-uppercase d-flex align-items-center gap-1" style="font-size: 10px; letter-spacing: 0.3px;">
                    <i class="fa-solid fa-keyboard text-primary"></i> {{ __('HOTKEYS:') }}
                </span>
                <span><kbd class="hotkey-kbd">F1</kbd> <span class="text-secondary">Add Row</span></span>
                <span><kbd class="hotkey-kbd">F3</kbd> / <kbd class="hotkey-kbd">Alt+V</kbd> <span class="text-secondary">Voucher No</span></span>
                <span><kbd class="hotkey-kbd">F4</kbd> / <kbd class="hotkey-kbd">Alt+C</kbd> <span class="text-secondary">Challan No</span></span>
                <span><kbd class="hotkey-kbd">F8</kbd> / <kbd class="hotkey-kbd">Alt+S</kbd> <span class="text-secondary">Submit</span></span>
                <span><kbd class="hotkey-kbd">F9</kbd> <span class="text-secondary">Toggle List</span></span>
                <span><kbd class="hotkey-kbd">Enter</kbd> <span class="text-secondary">Next Field</span></span>
            </div>
            <div class="text-muted d-none d-lg-block" style="font-size: 11px;">
                <span class="text-warning me-1">💡</span>Press <kbd class="hotkey-kbd">F1</kbd> to add row | <kbd class="hotkey-kbd">Enter</kbd> on Voucher No to load inward data
            </div>
        </div>

        <div>
            <div class="row">
                <div class="col-12" id="inwardProductList">
                    @include('shop.purchase-product.partials.purchase-product-table')
                </div>


                {{-- Form Code --}}

                <form id="searchForm" onsubmit="return false;"
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

    @include('shop.components-modal.design-master-modal')
    @include('shop.components-modal.item-master-modal')
    @include('shop.components-modal.item-tax-detail-modal')
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

        /* Non-scrolling Compact Table Design */
        .card.table-responsive,
        .inwardProductTableCard .table-responsive {
            overflow-x: hidden !important;
        }

        #inwardProductTable {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            margin-bottom: 0 !important;
        }

        #inwardProductTable th,
        #inwardProductTable td {
            vertical-align: middle !important;
            padding: 3px 2px !important;
            font-size: 11.5px !important;
        }

        #inwardProductTable th {
            font-weight: 700 !important;
            color: #475569 !important;
            background-color: #f8fafc !important;
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 2px solid #cbd5e1 !important;
            text-align: center !important;
            padding: 5px 2px !important;
            white-space: nowrap !important;
            text-transform: uppercase !important;
            font-size: 10px !important;
            letter-spacing: 0.2px !important;
        }

        #inwardProductTable input.form-control {
            width: 100% !important;
            height: 31px !important;
            min-height: 31px !important;
            max-height: 31px !important;
            padding: 2px 4px !important;
            font-size: 11.5px !important;
            border-radius: 4px !important;
            line-height: 1.3 !important;
            border-color: #cbd5e1 !important;
        }

        #inwardProductTable input.form-control:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        }

        #inwardProductTable tfoot tr.table-total-row th {
            background-color: #f8fafc !important;
            border-top: 2px solid #cbd5e1 !important;
            border-bottom: 2px solid #cbd5e1 !important;
            vertical-align: middle !important;
            padding: 3px 2px !important;
        }

        #inwardProductTable tfoot tr.table-total-row input.form-control {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            height: 28px !important;
            min-height: 28px !important;
            max-height: 28px !important;
            padding: 2px 4px !important;
            font-size: 11px !important;
            border-radius: 4px !important;
        }

        #inwardProductTable th:nth-child(1),
        #inwardProductTable td:nth-child(1) { width: 45px !important; min-width: 45px !important; max-width: 45px !important; text-align: center; } /* 1: SL */
        #inwardProductTable th:nth-child(2),
        #inwardProductTable td:nth-child(2) { width: 190px !important; min-width: 190px !important; } /* 2: Item */
        #inwardProductTable td:nth-child(3) { width: 11.0%; min-width: 95px; } /* 3: Design No */
        #inwardProductTable td:nth-child(4) { width: 6.5%; min-width: 60px; } /* 4: Color */
        #inwardProductTable td:nth-child(5) { width: 5.0%; min-width: 50px; } /* 5: Size */
        #inwardProductTable td:nth-child(6) { width: 4.2%; min-width: 45px; } /* 6: Qty */
        #inwardProductTable td:nth-child(7) { width: 5.8%; min-width: 65px; } /* 7: Purc Rate */
        #inwardProductTable td:nth-child(8) { width: 5.8%; min-width: 65px; } /* 8: Amount */
        #inwardProductTable td:nth-child(9) { width: 4.8%; min-width: 50px; } /* 9: Disc (%) */
        #inwardProductTable td:nth-child(10) { width: 5.2%; min-width: 55px; } /* 10: Disc Amt */
        #inwardProductTable td:nth-child(11) { width: 5.8%; min-width: 65px; } /* 11: Net PurcRate */
        #inwardProductTable td:nth-child(12) { width: 5.8%; min-width: 65px; } /* 12: MRP */
        #inwardProductTable td:nth-child(13) { width: 4.8%; min-width: 50px; } /* 13: Markup (%) */
        #inwardProductTable td:nth-child(14) { width: 4.8%; min-width: 50px; } /* 14: Markdown (%) */
        #inwardProductTable td:nth-child(15) { width: 6.5%; min-width: 75px; } /* 15: Tax Code */
        #inwardProductTable th:nth-child(16),
        #inwardProductTable td:nth-child(16) { width: 85px !important; min-width: 85px !important; } /* 16: Total GST (%) with (?) */
        #inwardProductTable th:nth-child(17),
        #inwardProductTable td:nth-child(17) { width: 45px !important; min-width: 45px !important; max-width: 45px !important; text-align: center; } /* 17: Action */

        #inwardList {
            display: none;
        }

        #create-record-btn {
            display: none;
        }

        /* Page Top Spacing & Hotkeys Bar matching Image 3 */
        .app-main .app-main-inner {
            padding-top: 10px !important;
        }

        .purchase-hotkeys-bar {
            margin-bottom: 8px !important;
            padding: 6px 14px !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
            position: static !important;
        }

        /* Ultra-compact single-line Inward Header */
        .inwardAccountDetails {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            background: #ffffff !important;
            margin-bottom: 8px !important;
        }

        .inwardAccountDetails .card-body {
            padding: 8px 12px !important;
        }

        .inward-header-grid {
            display: grid;
            grid-template-columns: minmax(90px, 1fr) minmax(90px, 1fr) minmax(120px, 1.2fr) minmax(90px, 0.9fr) minmax(95px, 1fr) minmax(120px, 1.2fr) minmax(100px, 1fr) minmax(180px, 2fr) minmax(85px, 0.9fr) minmax(85px, 0.9fr);
            gap: 6px;
            align-items: flex-end;
        }

        @media (max-width: 1300px) {
            .inward-header-grid {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 4px;
            }
            .inward-header-grid > div {
                flex-shrink: 0;
            }
        }

        .inward-header-grid .form-label {
            font-size: 10.5px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.2px !important;
            margin-bottom: 2px !important;
            white-space: nowrap !important;
            display: flex !important;
            align-items: center !important;
            gap: 2px !important;
        }

        .inward-header-grid .form-control,
        .inward-header-grid .form-select {
            height: 33px !important;
            min-height: 33px !important;
            padding: 4px 8px !important;
            font-size: 12.5px !important;
            border-radius: 6px !important;
            border-color: #cbd5e1 !important;
        }

        .inward-header-grid .select2-container .select2-selection--single {
            height: 33px !important;
            padding: 2px 4px !important;
            font-size: 12.5px !important;
            border-radius: 6px !important;
            border-color: #cbd5e1 !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Table Card Spacing */
        .inwardProductTableCard {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            margin-bottom: 8px !important;
        }

        /* Account & Bill Details Cards Spacing */
        .accountDetailsFinal {
            margin-bottom: 8px !important;
        }

        .accountDetailsFinal .card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            margin-bottom: 0 !important;
        }

        .accountDetailsFinal .row {
            --bs-gutter-x: 8px;
            --bs-gutter-y: 8px;
        }

        .accountDetailsFinal .select2-container {
            width: 100% !important;
            display: block !important;
        }

        .accountDetailsFinal .select2-container .select2-selection--single {
            height: 31px !important;
            min-height: 31px !important;
            border-radius: 4px !important;
            border-color: #cbd5e1 !important;
            display: flex !important;
            align-items: center !important;
            padding: 2px 4px !important;
            font-size: 12px !important;
            background-color: #ffffff !important;
            width: 100% !important;
        }

        .accountDetailsFinal .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 29px !important;
            padding-left: 6px !important;
            padding-right: 20px !important;
            color: #0f172a !important;
        }

        .accountDetailsFinal .select2-container .select2-selection--single .select2-selection__arrow {
            height: 29px !important;
            right: 6px !important;
        }

        .accountDetailsFinal .form-control,
        .accountDetailsFinal .form-select {
            height: 31px !important;
            min-height: 31px !important;
            font-size: 12px !important;
            border-radius: 4px !important;
            border-color: #cbd5e1 !important;
        }

        .accountDetailsFinal textarea.form-control {
            height: auto !important;
            min-height: 38px !important;
        }

        .purchase-action-footer {
            margin-top: 8px !important;
            margin-bottom: 15px !important;
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

        .hotkey-kbd {
            background-color: #f8fafc !important;
            color: #1e293b !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
            padding: 1.5px 5px !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            box-shadow: 0 1px 0 rgba(0,0,0,0.08) !important;
            font-family: var(--bs-font-monospace, monospace) !important;
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

        window.createRow = function (force = false) {
            // Reveal table, detail cards, and submit buttons if currently hidden
            $('.dataLoadToShow').removeClass('d-none');
            $('#rowsErrorTd').removeClass('bg-warning-light');
            $('#rowsErrorContainer').text('');

            // Do not add new row if the current/last row is still blank
            const $lastRow = $('#inwardProductTable tbody tr:last');
            if (!force && $lastRow.length > 0) {
                const itemVal = $lastRow.find('input[name="item[]"]').val() ? $lastRow.find('input[name="item[]"]').val().trim() : '';
                const itemId = $lastRow.find('input[name="itemid[]"]').val();
                if (!itemVal && !itemId) {
                    $lastRow.find('input.item').focus();
                    return false;
                }
            }

            let sl = $('#inwardProductTable tbody tr').length + 1;
            let deleteBtn = `
                <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center mx-auto deleteRow" style="width: 22px; height: 22px; border-radius: 4px;">
                    <i class="fa-solid fa-trash-can" style="font-size: 10.5px;"></i>
                </button>`;

            let row = `
            <tr data-sl="${sl}">
                <td class="text-center">${sl}</td>
                <td><input type="text" name="item[]" class="form-control item" placeholder="Select Item" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off"><input type="hidden" name="itemid[]"><input type="hidden" name="inwardProductId[]"></td>
                <td><input type="text" name="designNo[]" class="form-control designno text-center" placeholder="Design No" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off"><input type="hidden" name="designid[]"></td>
                <td class="text-center"><input type="text" name="colorName[]" class="form-control inward-color-input text-center" placeholder="Color" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off"><input type="hidden" name="colorInwardIds[]" class="color-id"></td>
                <td class="text-center"><input type="text" name="sizeName[]" class="form-control inward-size-input text-center" placeholder="Size" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off"><input type="hidden" name="sizeInwardIds[]" class="size-id"></td>

                <td><input type="text" name="qty[]" class="form-control qty text-center" value="1" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"></td>
                <td><input type="text" name="purcRate[]" class="form-control buy_price decimal-input text-end"></td>
                <td><input type="text" name="amount[]" class="form-control price decimal-input text-end"></td>
                <td><input type="text" name="disc[]" class="form-control discount_percentage decimal-input text-end"></td>
                <td><input type="text" name="discAmt[]" class="form-control discount_amount decimal-input text-end"></td>
                <td><input type="text" name="netPurcRate[]" class="form-control netPurcRate disabledCls decimal-input text-end bg-light" readonly></td>
                <td><input type="text" name="mrp[]" class="form-control mrp disabledCls decimal-input text-end bg-light" readonly></td>
                <td><input type="text" name="mark_up[]" class="form-control mark_up disabledCls decimal-input text-end bg-light" readonly></td>
                <td><input type="text" name="mark_down[]" class="form-control mark_down disabledCls decimal-input text-end bg-light" readonly></td>

                <td class="position-relative">
                    <input type="text" name="taxCode[]" class="form-control taxcode text-center" placeholder="Tax Code" autocomplete="off">
                    <div class="dropdown-taxCode"></div>
                    <input type="hidden" name="taxCodeId[]">
                </td>

                <td class="text-center p-1">
                    <div class="position-relative d-inline-flex align-items-center w-100" style="min-width: 76px;">
                        <input type="text" name="sgst[]" class="form-control sgst text-center disabledCls bg-light" readonly style="padding-right: 26px !important; font-weight: 500;">
                        <span class="position-absolute end-0 me-1.5 cursor-pointer open-item-tax-modal text-dark d-flex align-items-center justify-content-center" role="button" title="{{ __('View Tax Details') }}" style="cursor: pointer; width: 22px; height: 100%; top: 0; z-index: 2;">
                            <i class="fa-regular fa-circle-question" style="font-size: 15px; color: #1e293b;"></i>
                        </span>
                        <input type="hidden" name="sgstId[]">
                    </div>
                </td>
                 <td class="text-center">
                    ${deleteBtn}
                </td>
            </tr>`;
            $('#inwardProductTable tbody').append(row);
            $('#inwardProductTable tbody tr:last').find('input.item').focus();
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

            // Auto-focus Voucher No on page load
            setTimeout(function () {
                $('input[name="inward_voucher_no"]').focus().select();
            }, 250);

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
                    setTimeout(function () {
                        $('input[name="inward_voucher_no"]').focus().select();
                    }, 100);
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

            setTimeout(function () {
                $('input[name="inward_voucher_no"]').focus().select();
            }, 100);
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

                            $('#inward_party_name').val(res.inwardData.party_code ? res.inwardData.party_code.accountName : '');
                            $('#inward_total').val(parseFloat(res.inwardData.inward_total || 0).toFixed(2));
                            $('#inward_party_limit').val(parseFloat(res.inwardData.inward_party_limit || 0).toFixed(2));

                            if (res.inwardData.party_code) {
                                $('#inward_party_state_id').val(res.inwardData.party_code.state_id || '');
                                $('#inward_party_state_name').val(res.inwardData.party_code.state ? res.inwardData.party_code.state.name : '');
                            }

                            // Table View

                            let products = res.inwardData.inward_product;

                             if (Array.isArray(products)) {
                                 products.forEach((product, index) => {
                                     createRow(true);
                                     fillRowData(product, index + 1); // 👈 row number pass
                                 });
                             }

                             if (window.calculateNetPurcRateSum) {
                                 window.calculateNetPurcRateSum();
                             }

                             setTimeout(() => {
                                 $('#inwardProductTable tbody tr:first').find('input[name="qty[]"]').focus().select();
                             }, 300);

                            // Footer Value Set
                            $('#inward_acc_credit_day').val(res.inwardData.inward_credit_day);

                            if (res.inwardData.inward_acc_purchaser && res.inwardData.purchaser) {
                                var purchaserData = {
                                    id: res.inwardData.inward_acc_purchaser,
                                    text: (res.inwardData.purchaser.name || '') + ' - ' + (res.inwardData.purchaser.last_name || ''),
                                };

                                if ($("#inward_acc_purchaser").find("option[value='" + purchaserData.id + "']").length === 0) {
                                    var purchaserDataOption = new Option(purchaserData.text, purchaserData.id, true, true);
                                    $("#inward_acc_purchaser").append(purchaserDataOption).trigger('change');
                                } else {
                                    $("#inward_acc_purchaser").val(purchaserData.id).trigger('change');
                                }
                            }

                            if (res.inwardData.season_id && res.inwardData.season) {
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

                            if (res.inwardData.agent_id && res.inwardData.agent) {
                                var agentData = {
                                    id: res.inwardData.agent_id,
                                    text: (res.inwardData.agent.code || '') + ' - ' + (res.inwardData.agent.name || ''),
                                };

                                if ($("#inward_acc_agent").find("option[value='" + agentData.id + "']").length === 0) {
                                    var agentOption = new Option(agentData.text, agentData.id, true, true);
                                    $("#inward_acc_agent").append(agentOption).trigger('change');
                                } else {
                                    $("#inward_acc_agent").val(agentData.id).trigger('change');
                                }
                            }

                            if (res.inwardData.transport_id && res.inwardData.transport) {
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

                            if (res.inwardData.delivery_by_id && res.inwardData.delivery_by) {
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

                            $('#cash_or_credit').val(res.inwardData.cash_or_credit || 'Credit');
                            $('#bank_cash_discount_percent').val(parseFloat(res.inwardData.bank_cash_discount_percent || 0).toFixed(2));
                            $('#inward_acc_lr_no').val(res.inwardData.inward_acc_lr_no || '');
                            $('#inward_acc_lr_date').val(res.inwardData.inward_acc_lr_date || '');
                            $('#inward_acc_remark').val(res.inwardData.inward_acc_remark || '');

                            $('#bill_discount_percent').val(parseFloat(res.inwardData.bill_discount_percent || 0).toFixed(2));
                            $('#bill_discount_amount').val(parseFloat(res.inwardData.bill_discount_amount || 0).toFixed(2));
                            $('#cash_discount_percent').val(parseFloat(res.inwardData.cash_discount_percent || 0).toFixed(2));
                            $('#cash_discount_amount').val(parseFloat(res.inwardData.cash_discount_amount || 0).toFixed(2));
                            $('#agent_commission_percent').val(parseFloat(res.inwardData.agent_commission_percent || 0).toFixed(2));
                            $('#agent_commission_amount').val(parseFloat(res.inwardData.agent_commission_amount || 0).toFixed(2));
                            $('#expense_amount').val(parseFloat(res.inwardData.expense_amount || 0).toFixed(2));
                            $('#other_amount').val(parseFloat(res.inwardData.other_amount || 0).toFixed(2));
                            $('#inward_bill_remark').val(res.inwardData.inward_bill_remark || res.inwardData.bill_remark || '');

                            $('#inward_acc_gst_amount').val(parseFloat(res.inwardData.inward_acc_gst_amount || 0).toFixed(2));
                            $('#inward_acc_net_amount').val(parseFloat(res.inwardData.inward_acc_net_amount || 0).toFixed(2));
                            $('#inward_acc_freight_amount').val(parseFloat(res.inwardData.inward_acc_freight_amount || 0).toFixed(2));
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
                if (!$row.length) {
                    $row = $('#inwardProductTable tbody tr').eq(sl - 1);
                }
                if (!$row.length) {
                    $row = $('#inwardProductTable tbody tr:last');
                }

                let buyPrice = parseFloat(product.buy_price) || 0;
                let price = parseFloat(product.price) || 0;
                let disc = parseFloat(product.discount_price) || 0;
                let discAmt = (buyPrice * disc) / 100;
                let mrp = parseFloat(product.mrp) || 0;
                let markUp = parseFloat(product.mark_up) || 0;
                let markDown = parseFloat(product.mark_down) || 0;
                let netPurcRate = parseFloat(product.net_purc_rate) || 0;

                $row.find('input[name="inwardProductId[]"]').val(product.id);
                $row.find('input[name="qty[]"]').val(product.quantity).addClass('disabledCls');
                $row.find('input[name="purcRate[]"]').val(buyPrice.toFixed(2)).addClass('disabledCls');
                $row.find('input[name="amount[]"]').val(price.toFixed(2)).addClass('disabledCls');
                $row.find('input[name="disc[]"]').val(disc.toFixed(2)).addClass('disabledCls');
                $row.find('input[name="discAmt[]"]').val(discAmt > 0 ? discAmt.toFixed(2) : '0.00').addClass('disabledCls');
                $row.find('input[name="mrp[]"]').val(mrp > 0 ? mrp.toFixed(2) : '0.00').addClass('disabledCls');
                $row.find('input[name="mark_up[]"]').val(markUp.toFixed(2)).addClass('disabledCls');
                $row.find('input[name="mark_down[]"]').val(markDown.toFixed(2)).addClass('disabledCls');
                $row.find('input[name="netPurcRate[]"]').val(netPurcRate.toFixed(2)).addClass('disabledCls');

                // ---------- ITEM / DESIGN (hidden ids) ----------
                $row.find('input[name="item[]"]').val(product.products ? product.products.name : '').addClass('disabledCls');
                $row.find('input[name="itemid[]"]').val(product.product_id);

                let designNo = product.design_master?.design_number || product.designMaster?.design_number || '';
                $row.find('input[name="designNo[]"]').val(designNo).addClass('disabledCls');
                $row.find('input[name="designid[]"]').val(product.design_master_id || '');

                // ---------- TAX ----------
                let taxCode = product.hsn_master?.hsn_code || product.hsnMaster?.hsn_code || '';
                $row.find('input[name="taxCode[]"]').val(taxCode);
                $row.find('input[name="taxCodeId[]"]').val(product.hsn_master_id || '');

                // ---------- TAX ----------
                let sgstVal = (product.vat_tax && product.vat_tax.percentage !== undefined)
                    ? product.vat_tax.percentage
                    : ((product.vatTax && product.vatTax.percentage !== undefined) ? product.vatTax.percentage : '');
                $row.find('input[name="sgst[]"]').val(sgstVal);
                $row.find('input[name="sgstId[]"]').val(product.vat_tax_id || '');

                // ---------- COLOR ----------
                let firstColor = product.colors && product.colors.length > 0 ? product.colors[0] : null;
                if (firstColor) {
                    $row.find('input[name="colorName[]"]').val(firstColor.name);
                    $row.find('input[name="colorInwardIds[]"]').val(firstColor.id);
                } else {
                    $row.find('input[name="colorName[]"]').val('');
                    $row.find('input[name="colorInwardIds[]"]').val('');
                }

                // ---------- SIZE ----------
                let firstSize = product.sizes && product.sizes.length > 0 ? product.sizes[0] : null;
                if (firstSize) {
                    $row.find('input[name="sizeName[]"]').val(firstSize.name);
                    $row.find('input[name="sizeInwardIds[]"]').val(firstSize.id);
                } else {
                    $row.find('input[name="sizeName[]"]').val('');
                    $row.find('input[name="sizeInwardIds[]"]').val('');
                }

                // ---------- SELECT2 FALLBACK ----------
                let $colorSelect = $row.find('.colorSelectInward');
                if ($colorSelect.length > 0) {
                    $colorSelect.empty();
                    product.colors.forEach(color => {
                        $colorSelect.append(new Option(color.name, color.id, true, true));
                    });
                    $colorSelect.trigger('change.select2');
                }

                let $sizeSelect = $row.find('.sizeSelectInward');
                if ($sizeSelect.length > 0) {
                    $sizeSelect.empty();
                    product.sizes.forEach(size => {
                        $sizeSelect.append(new Option(size.name, size.id, true, true));
                    });
                    $sizeSelect.trigger('change.select2');
                }

                if (typeof window.calculateNetPurcRateSum === 'function') {
                    window.calculateNetPurcRateSum();
                }

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
    </script>

    <script>
        $(document).ready(function () {

            let debounceTimer;

            function executePurchaseSearch(search) {
                clearTimeout(debounceTimer);
                $.ajax({
                    url: "{{ route('shop.purchaseProduct.list') }}",
                    type: 'GET',
                    data: {search: search},
                    beforeSend: function () {
                        $('#inwardList tbody').html('<tr><td colspan="10" class="text-center">Loading...</td></tr>');
                    },
                    success: function (res) {
                        $('#inwardList').html(res);
                    },
                    error: function (err) {
                        toastr.error('Failed to load data');
                    }
                });
            }

            $(document).on('submit', '#searchForm', function (e) {
                e.preventDefault();
                let search = $(this).find('input[name="search"]').val();
                executePurchaseSearch(search);
                return false;
            });

            $(document).on('keydown', '#searchForm input[name="search"]', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    let search = $(this).val();
                    executePurchaseSearch(search);
                    return false;
                }
            });

            $(document).on('keyup', '#searchForm input[name="search"]', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    return;
                }
                clearTimeout(debounceTimer);
                let search = $(this).val();

                debounceTimer = setTimeout(function () {
                    executePurchaseSearch(search);
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
            // F3 or Alt+V -> Voucher No
            if (e.key === 'F3' || (e.altKey && (e.key === 'v' || e.key === 'V'))) {
                e.preventDefault();
                $('input[name="inward_voucher_no"]').focus().select();
            }
            // F4 or Alt+C -> Challan No
            if (e.key === 'F4' || (e.altKey && (e.key === 'c' || e.key === 'C'))) {
                e.preventDefault();
                $('#inward_challan_no').focus().select();
            }
            // F8 or Alt+S -> Submit Purchase
            if (e.key === 'F8' || (e.altKey && (e.key === 's' || e.key === 'S'))) {
                e.preventDefault();
                $('#saveButton').click();
            }
            // F9 -> Toggle List/Entry
            if (e.key === 'F9') {
                e.preventDefault();
                $('#list-btn').click();
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
    @include('shop.components-modal.master-modal.design-master-modal-script')
    @include('shop.components-modal.master-modal.item-master-modal-script')
@endpush