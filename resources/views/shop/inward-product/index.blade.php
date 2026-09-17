@extends('layouts.app')
@section('header-title', __('Inward Product'))
@section('header-subtitle')
    <span class="d-inline-flex align-items-center gap-2 mt-1" style="font-size: 11px; color: #64748b;">
        <i class="fa-solid fa-keyboard text-primary"></i>
        <span>Hotkeys:</span>
        <span class="cursor-pointer" onclick="showCustomLoader('center'); modelItemMasterDataLoad();" style="cursor: pointer;" title="Add Item (Alt + I)">
            <kbd class="px-1 py-0.5 bg-white text-dark border border-secondary rounded shadow-sm font-monospace fw-bold" style="font-size: 10px; border-color: #cbd5e1 !important;">Alt + I</kbd> Add Item
        </span>
        <span class="text-muted">|</span>
        <span class="cursor-pointer" onclick="showCustomLoader('center'); modelDesignMasterDataLoad();" style="cursor: pointer;" title="Add Design (Alt + D)">
            <kbd class="px-1 py-0.5 bg-white text-dark border border-secondary rounded shadow-sm font-monospace fw-bold" style="font-size: 10px; border-color: #cbd5e1 !important;">Alt + D</kbd> Add Design
        </span>
        <span class="text-muted">|</span>
        <span class="cursor-pointer" onclick="window.createRow(true);" style="cursor: pointer;" title="Add Row (F1)">
            <kbd class="px-1 py-0.5 bg-white text-dark border border-secondary rounded shadow-sm font-monospace fw-bold" style="font-size: 10px; border-color: #cbd5e1 !important;">F1</kbd> Add Row
        </span>
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
                <form id="searchForm" onsubmit="return false;"
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

    {{-- Item Tax Detail Modal --}}
    @include('shop.components-modal.item-tax-detail-modal')

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

        #inwardProductTable td:nth-child(1) { width: 45px !important; min-width: 45px !important; max-width: 45px !important; text-align: center; } /* 1: SL */
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
        #inwardProductTable td:nth-child(17) { width: 45px !important; min-width: 45px !important; max-width: 45px !important; text-align: center; } /* 17: Delete Action */

        #inwardList {
            display: none;
        }

        #create-record-btn {
            display: none;
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
            grid-template-columns: minmax(85px, 0.9fr) minmax(95px, 1fr) minmax(115px, 1.2fr) minmax(85px, 0.9fr) minmax(95px, 1fr) minmax(95px, 1fr) minmax(115px, 1.2fr) minmax(110px, 1.1fr) minmax(170px, 2fr) minmax(85px, 0.9fr) minmax(85px, 0.9fr);
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

        .inward-header-grid .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px !important;
            padding-left: 4px !important;
        }

        /* Account & Bill Details Select2 & Controls full-width styling */
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

            // Helper to get currently selected Inward Party context (ID, Code, Name) for Design Master modal
            window.getInwardPartyContext = function () {
                const partyId = $('#inward_party_code').val();
                let partyCode = $('#inward_party_code_display').val();
                if (!partyCode && $('#inward_party_code option:selected').val()) {
                    partyCode = $('#inward_party_code option:selected').text();
                }
                const partyName = $('#inward_party_name').val() || '';

                if (partyId && partyName) {
                    return {
                        id: partyId,
                        code: (partyCode || '').trim(),
                        name: partyName.trim()
                    };
                }
                return null;
            };

            // Alt + I (Open Item Master) & Alt + D (Open Design Master) shortcut listeners - Capture Phase to prevent character insertion on macOS/Windows
            window.addEventListener('keydown', function(e) {
                const key = e.key ? e.key.toLowerCase() : '';
                const code = e.code || '';

                if (e.altKey && (code === 'KeyI' || key === 'i' || e.keyCode === 73)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    if ($('#inwardItemSpotlightModal').hasClass('show')) {
                        $('#inwardItemSpotlightModal').modal('hide');
                    }
                    if ($('#inwardPartySpotlightModal').hasClass('show')) {
                        $('#inwardPartySpotlightModal').modal('hide');
                    }
                    showCustomLoader('center');
                    modelItemMasterDataLoad();
                    return false;
                }
                if (e.altKey && (code === 'KeyD' || key === 'd' || e.keyCode === 68)) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    if ($('#inwardItemSpotlightModal').hasClass('show')) {
                        $('#inwardItemSpotlightModal').modal('hide');
                    }
                    if ($('#inwardPartySpotlightModal').hasClass('show')) {
                        $('#inwardPartySpotlightModal').modal('hide');
                    }
                    showCustomLoader('center');
                    modelDesignMasterDataLoad();
                    return false;
                }
            }, { capture: true, passive: false });
        });
        // New Code

        let editData = false;

        window.createRow = function (force = false) {
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
            let deleteBtn = '';
            if (editData === false) {
                if (sl > 1) {
                    deleteBtn = `
                        <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center mx-auto deleteRow" style="width: 22px; height: 22px; border-radius: 4px;">
                            <i class="fa-solid fa-trash-can" style="font-size: 10.5px;"></i>
                        </button>`;
                }
            } else {
                deleteBtn = `
                        <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center mx-auto deleteRow" style="width: 22px; height: 22px; border-radius: 4px;">
                            <i class="fa-solid fa-trash-can" style="font-size: 10.5px;"></i>
                        </button>`;
            }


            let row = `
            <tr data-sl="${sl}">
                <td class="text-center">${sl}</td>
                <td><input type="text" name="item[]" class="form-control item" placeholder="Select Item" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off"><input type="hidden" name="itemid[]"><input type="hidden" name="inwardProductId[]"></td>

                <td><input type="text" name="designNo[]" class="form-control designno" placeholder="Design No" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off"><input type="hidden" name="designid[]"></td>
                <td class="text-center"><input type="text" name="colorName[]" class="form-control inward-color-input text-center" placeholder="Color" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off"><input type="hidden" name="colorInwardIds[]" class="color-id"></td>
                <td class="text-center"><input type="text" name="sizeName[]" class="form-control inward-size-input text-center" placeholder="Size" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off"><input type="hidden" name="sizeInwardIds[]" class="size-id"></td>

                <td><input type="text" name="qty[]" class="form-control qty text-center" value="1" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"></td>
                <td><input type="text" name="purcRate[]" class="form-control buy_price decimal-input text-end"></td>
                <td><input type="text" name="amount[]" class="form-control price decimal-input text-end"></td>
                <td><input type="text" name="disc[]" class="form-control discount_percentage decimal-input text-end"></td>
                <td><input type="text" name="discAmt[]" class="form-control discount_amount decimal-input text-end"></td>
                <td><input type="text" name="netPurcPrice[]" class="form-control netPurcPrice disabledCls decimal-input text-end bg-light" readonly></td>
                <td><input type="text" name="mrp[]" class="form-control mrp disabledCls decimal-input text-end bg-light" readonly></td>
                <td><input type="text" name="mark_up[]" class="form-control mark_up disabledCls decimal-input text-end bg-light" readonly></td>
                <td><input type="text" name="mark_down[]" class="form-control mark_down disabledCls decimal-input text-end bg-light" readonly></td>
                <input type="hidden" name="netPurcRate[]" class="netPurcRate">

                <td class="position-relative">
                    <input type="text" name="taxCode[]" class="form-control taxcode" placeholder="Tax Code">
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

            // Default focus on Voucher No input on page load
            setTimeout(function () {
                $('#inward_voucher_no').focus().select();
            }, 300);

            $create_record_btn.on('click', function () {
                editData = false;
                $('#saveButton').show();
                $('#alertBoxPurchase').removeClass('alert alert-danger').text('');
                clearValidationBorders();
                $create_record_btn.hide();
                resetFormInward();
                setTimeout(function () {
                    $('#inward_voucher_no').focus().select();
                }, 150);
            });


            // Default Hide inwardList
            $inwardList.hide();
            $create_record_btn.hide();

            $list_btn.on('click', function () {
                $('#saveButton').show();
                $('#alertBoxPurchase').removeClass('alert alert-danger').text('');
                $inwardProductList.toggle();
                $inwardList.toggle();

                if ($inwardList.is(":visible")) {
                    showCustomLoader('center');
                    refreshInwardList();
                    $list_btn.text('Back');
                    $create_btn.hide();
                    $create_record_btn.hide();
                    $searchForm.removeClass('d-none').addClass('d-flex')[0].reset();
                    oldPriceTableClear();
                } else {
                    $list_btn.text('Inward List');
                    $create_btn.show();
                    $searchForm.removeClass('d-flex').addClass('d-none')[0].reset();
                    if ($("#inward_invoice_id").val() !== '') {
                        $create_record_btn.show();
                    } else {
                        $create_record_btn.hide();
                    }
                    HoldOn.close();
                }
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
                HoldOn.close();
            }
        });

        function resetFormInward() {
            $('#itemForm')[0].reset();
            $(".errorSpan").empty();
            $("#inward_invoice_id").val('');
            fillDayAndTime();

            // Top Data

            $("#day_book_id").val("{{ $dayBooks->first()?->id ?? 1 }}");
            $("#inward_party_code_display").val('');
            $("#inward_party_code").empty();
            $("#inward_party_name").val('');
            $("#inward_party_limit").val('0.00');

            // Row
            $('#inwardProductTable tbody').html('');
            window.createRow();
            // Footer Data Clear

            $("#inward_acc_purchaser").empty().append('<option value="">{{ __("Select Purchaser") }}</option>');
            $("#inward_acc_season").empty().append('<option value="">{{ __("Select Season") }}</option>');
            $("#inward_acc_agent").empty().append('<option value="">{{ __("Select a Agent") }}</option>');
            $("#inward_acc_transport").empty().append('<option value="">{{ __("Select a Transport") }}</option>');
            $("#inward_acc_delivery_by").empty().append('<option value="">{{ __("Select a Delivery By") }}</option>');
            $("#inward_acc_remark").val('');
            $("#inward_bill_remark").val('');

            setTimeout(function () {
                $('#inward_voucher_no').focus().select();
            }, 100);
        }

        function buildInwardRowHtml(product, sl) {
            let buyPrice = parseFloat(product.buy_price) || 0;
            let price = parseFloat(product.price) || 0;
            let disc = parseFloat(product.discount_price) || 0;
            let discAmt = (buyPrice * disc) / 100;
            let netPurc = (product.net_purc_price !== null && product.net_purc_price !== undefined) ? parseFloat(product.net_purc_price) : (buyPrice - discAmt);
            let mrp = parseFloat(product.mrp) || 0;
            let markUp = parseFloat(product.mark_up) || 0;
            let markDown = parseFloat(product.mark_down) || 0;
            let netPurcRate = parseFloat(product.net_purc_rate) || 0;

            let itemName = product.products ? (product.products.name || '') : '';
            let itemId = product.product_id || '';
            let inwardProductId = product.id || '';

            let designNo = product.design_master?.design_number || product.designMaster?.design_number || '';
            let designId = product.design_master_id || '';
            let designDisabledCls = designNo ? ' disabledCls' : '';

            let firstColor = product.colors && product.colors.length > 0 ? product.colors[0] : null;
            let colorName = firstColor ? (firstColor.name || '') : '';
            let colorId = firstColor ? (firstColor.id || '') : '';

            let firstSize = product.sizes && product.sizes.length > 0 ? product.sizes[0] : null;
            let sizeName = firstSize ? (firstSize.name || '') : '';
            let sizeId = firstSize ? (firstSize.id || '') : '';

            let taxCode = product.hsn_master?.hsn_code || product.hsnMaster?.hsn_code || '';
            let taxCodeId = product.hsn_master_id || '';

            let sgstVal = (product.vat_tax && product.vat_tax.percentage !== undefined)
                ? product.vat_tax.percentage
                : ((product.vatTax && product.vatTax.percentage !== undefined) ? product.vatTax.percentage : '');
            let sgstId = product.vat_tax_id || '';

            let qtyVal = (product.quantity !== null && product.quantity !== undefined) ? product.quantity : 1;
            let purcRateStr = buyPrice.toFixed(2);
            let priceStr = price.toFixed(2);
            let discStr = disc.toFixed(2);
            let discAmtStr = discAmt > 0 ? discAmt.toFixed(2) : '0.00';
            let netPurcStr = netPurc > 0 ? netPurc.toFixed(2) : (buyPrice > 0 ? buyPrice.toFixed(2) : '0.00');
            let mrpStr = mrp > 0 ? mrp.toFixed(2) : '0.00';
            let markUpStr = markUp.toFixed(2);
            let markDownStr = markDown.toFixed(2);
            let netPurcRateStr = netPurcRate.toFixed(2);

            let deleteBtn = `
                <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center mx-auto deleteRow" style="width: 22px; height: 22px; border-radius: 4px;">
                    <i class="fa-solid fa-trash-can" style="font-size: 10.5px;"></i>
                </button>`;

            return `
            <tr data-sl="${sl}">
                <td class="text-center">${sl}</td>
                <td><input type="text" name="item[]" class="form-control item disabledCls" placeholder="Select Item" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off" value="${escapeHtml(itemName)}"><input type="hidden" name="itemid[]" value="${escapeHtml(itemId)}"><input type="hidden" name="inwardProductId[]" value="${escapeHtml(inwardProductId)}"></td>

                <td><input type="text" name="designNo[]" class="form-control designno${designDisabledCls}" placeholder="Design No" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off" value="${escapeHtml(designNo)}"><input type="hidden" name="designid[]" value="${escapeHtml(designId)}"></td>
                <td class="text-center"><input type="text" name="colorName[]" class="form-control inward-color-input text-center" placeholder="Color" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off" value="${escapeHtml(colorName)}"><input type="hidden" name="colorInwardIds[]" class="color-id" value="${escapeHtml(colorId)}"></td>
                <td class="text-center"><input type="text" name="sizeName[]" class="form-control inward-size-input text-center" placeholder="Size" readonly style="cursor: pointer; background-color: #ffffff;" autocomplete="off" value="${escapeHtml(sizeName)}"><input type="hidden" name="sizeInwardIds[]" class="size-id" value="${escapeHtml(sizeId)}"></td>

                <td><input type="text" name="qty[]" class="form-control qty text-center" value="${escapeHtml(qtyVal)}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"></td>
                <td><input type="text" name="purcRate[]" class="form-control buy_price decimal-input text-end" value="${purcRateStr}"></td>
                <td><input type="text" name="amount[]" class="form-control price decimal-input text-end" value="${priceStr}"></td>
                <td><input type="text" name="disc[]" class="form-control discount_percentage decimal-input text-end" value="${discStr}"></td>
                <td><input type="text" name="discAmt[]" class="form-control discount_amount decimal-input text-end" value="${discAmtStr}"></td>
                <td><input type="text" name="netPurcPrice[]" class="form-control netPurcPrice disabledCls decimal-input text-end bg-light" readonly value="${netPurcStr}"></td>
                <td><input type="text" name="mrp[]" class="form-control mrp disabledCls decimal-input text-end bg-light" readonly value="${mrpStr}"></td>
                <td><input type="text" name="mark_up[]" class="form-control mark_up disabledCls decimal-input text-end bg-light" readonly value="${markUpStr}"></td>
                <td><input type="text" name="mark_down[]" class="form-control mark_down disabledCls decimal-input text-end bg-light" readonly value="${markDownStr}"></td>
                <input type="hidden" name="netPurcRate[]" class="netPurcRate" value="${netPurcRateStr}">

                <td class="position-relative">
                    <input type="text" name="taxCode[]" class="form-control taxcode" placeholder="Tax Code" value="${escapeHtml(taxCode)}">
                    <div class="dropdown-taxCode"></div>
                    <input type="hidden" name="taxCodeId[]" value="${escapeHtml(taxCodeId)}">
                </td>

                <td class="text-center p-1">
                    <div class="position-relative d-inline-flex align-items-center w-100" style="min-width: 76px;">
                        <input type="text" name="sgst[]" class="form-control sgst text-center disabledCls bg-light" readonly style="padding-right: 26px !important; font-weight: 500;" value="${escapeHtml(sgstVal)}">
                        <span class="position-absolute end-0 me-1.5 cursor-pointer open-item-tax-modal text-dark d-flex align-items-center justify-content-center" role="button" title="{{ __('View Tax Details') }}" style="cursor: pointer; width: 22px; height: 100%; top: 0; z-index: 2;">
                            <i class="fa-regular fa-circle-question" style="font-size: 15px; color: #1e293b;"></i>
                        </span>
                        <input type="hidden" name="sgstId[]" value="${escapeHtml(sgstId)}">
                    </div>
                </td>
                <td class="text-center">
                    ${deleteBtn}
                </td>
            </tr>`;
        }

        function editDataLoad(id) {
            showCustomLoader('center');
            $.ajax({
                url: `/shop/inward-product/${id}/edit`,
                type: 'GET',
                success: function (res) {
                    if (!res || !res.inwardData) {
                        toastr.error("Failed to load inward bill data.");
                        return;
                    }

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
                        $('#day_book_id').val(res.inwardData.counter_master_id);
                    } else {
                        $("#day_book_id").val("{{ $dayBooks->first()?->id ?? 1 }}");
                    }

                    $('#inward_voucher_no').val(res.inwardData.inward_voucher_no);
                    $('#inward_date').val(res.inwardData.inward_date);
                    $('#inward_day_name').val(res.inwardData.inward_day_name);
                    $('#inward_time').val(res.inwardData.inward_time);
                    $('#inward_challan_no').val(res.inwardData.inward_challan_no);
                    $('#inward_challan_date').val(res.inwardData.inward_challan_date);

                    if (res.inwardData.inward_party_code) {
                        var partyCodeText = res.inwardData.party_code ? res.inwardData.party_code.accountshortcode : res.inwardData.inward_party_code;
                        var partyNameText = res.inwardData.party_code ? res.inwardData.party_code.accountName : '';
                        var partyLimitText = res.inwardData.party_code ? res.inwardData.party_code.other_info_act_limit : '0.00';

                        $("#inward_party_code_display").val(partyCodeText);
                        $("#inward_party_code").empty().append(new Option(partyCodeText, res.inwardData.inward_party_code, true, true)).val(res.inwardData.inward_party_code);
                        $("#inward_party_name").val(partyNameText);
                        $("#inward_party_limit").val(partyLimitText);
                        if (res.inwardData.party_code) {
                            $("#inward_party_state_id").val(res.inwardData.party_code.state_id || '');
                            $("#inward_party_state_name").val(res.inwardData.party_code.state ? res.inwardData.party_code.state.name : '');
                        }
                    }

                    $('#inward_total').val(parseFloat(res.inwardData.inward_total || 0).toFixed(2));

                    // Table View - High performance single batch render
                    let products = res.inwardData.inward_product;
                    if (Array.isArray(products) && products.length > 0) {
                        let rowsHtml = '';
                        for (let i = 0; i < products.length; i++) {
                            rowsHtml += buildInwardRowHtml(products[i], i + 1);
                        }
                        $('#inwardProductTable tbody').html(rowsHtml);
                    } else {
                        $('#inwardProductTable tbody').empty();
                        window.createRow(true);
                    }

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
                    $('#inward_bill_remark').val(res.inwardData.inward_bill_remark || '');

                    $('#inward_acc_gst_amount').val(parseFloat(res.inwardData.inward_acc_gst_amount || 0).toFixed(2));
                    $('#inward_acc_net_amount').val(parseFloat(res.inwardData.inward_acc_net_amount || 0).toFixed(2));
                    $('#inward_acc_freight_amount').val(parseFloat(res.inwardData.inward_acc_freight_amount || 0).toFixed(2));

                    if (window.calculateNetPurcRateSum) {
                        window.calculateNetPurcRateSum();
                    }

                },
                error: function () {
                    toastr.error("Failed to load item data.");
                },
                complete: function () {
                    setTimeout(function () {
                        HoldOn.close();
                    }, 50);
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

        function fillRowData(product, sl, calculateSum = false) {

            let $row = $(`#inwardProductTable tbody tr[data-sl="${sl}"]`);
            if (!$row.length) {
                $row = $('#inwardProductTable tbody tr').eq(sl - 1);
            }
            if (!$row.length) {
                $row = $('#inwardProductTable tbody tr:last');
            }

            // ---------- BASIC INPUTS ----------
            let buyPrice = parseFloat(product.buy_price) || 0;
            let price = parseFloat(product.price) || 0;
            let disc = parseFloat(product.discount_price) || 0;
            let discAmt = (buyPrice * disc) / 100;
            let netPurc = product.net_purc_price !== null && product.net_purc_price !== undefined ? parseFloat(product.net_purc_price) : (buyPrice - discAmt);
            let mrp = parseFloat(product.mrp) || 0;
            let markUp = parseFloat(product.mark_up) || 0;
            let markDown = parseFloat(product.mark_down) || 0;
            let netPurcRate = parseFloat(product.net_purc_rate) || 0;

            $row.find('input[name="inwardProductId[]"]').val(product.id);
            $row.find('input[name="qty[]"]').val(product.quantity);
            $row.find('input[name="purcRate[]"]').val(buyPrice.toFixed(2));
            $row.find('input[name="amount[]"]').val(price.toFixed(2));
            $row.find('input[name="disc[]"]').val(disc.toFixed(2));
            $row.find('input[name="discAmt[]"]').val(discAmt > 0 ? discAmt.toFixed(2) : '0.00');
            $row.find('input[name="netPurcPrice[]"]').val(netPurc > 0 ? netPurc.toFixed(2) : (buyPrice > 0 ? buyPrice.toFixed(2) : '0.00'));
            $row.find('input[name="mrp[]"]').val(mrp > 0 ? mrp.toFixed(2) : '0.00');
            $row.find('input[name="mark_up[]"]').val(markUp.toFixed(2));
            $row.find('input[name="mark_down[]"]').val(markDown.toFixed(2));
            $row.find('input[name="netPurcRate[]"]').val(netPurcRate.toFixed(2));

            // ---------- ITEM / DESIGN (hidden ids) ----------
            $row.find('input[name="item[]"]').val(product.products ? product.products.name : '').addClass('disabledCls');
            $row.find('input[name="itemid[]"]').val(product.product_id);

            let designNo = product.design_master?.design_number || product.designMaster?.design_number || '';
            $row.find('input[name="designNo[]"]').val(designNo);
            if (designNo) {
                $row.find('input[name="designNo[]"]').addClass('disabledCls');
            }
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

            // ---------- COLOR SELECT2 ----------
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

            if (calculateSum && typeof window.calculateNetPurcRateSum === 'function') {
                window.calculateNetPurcRateSum();
            }
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
            $.ajax({
                url: "{{ route('shop.inwardProduct.list') }}",
                type: "GET",
                success: function (data) {
                    $("#inwardList").html(data);
                    applyColumnVisibility();
                },
                error: function () {
                    toastr.error("Failed to refresh list");
                },
                complete: function () {
                    HoldOn.close();
                }
            });
        }

        $(document).on('click', '#pagination-links a, #inwardList .pagination a', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (!url || url === '#' || url === 'javascript:void(0)') {
                return;
            }
            loadInwardList(url);
        });

        $(document).ready(function () {
            // Initial column visibility run
            applyColumnVisibility();
        });

        // Common function for inward list pagination
        function loadInwardList(url) {
            showCustomLoader('center');
            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    $('#inwardList').html(response);
                    applyColumnVisibility();
                },
                error: function (xhr) {
                    console.log('Error:', xhr.responseText);
                    toastr.error('Failed to load page');
                },
                complete: function () {
                    HoldOn.close();
                }
            });
        }

        $(document).ready(function () {

            let debounceTimer;

            function executeInwardSearch(search) {
                clearTimeout(debounceTimer);
                $.ajax({
                    url: "{{ route('shop.inwardProduct.list') }}",
                    type: 'GET',
                    data: {search: search},
                    beforeSend: function () {
                        $('#inwardList tbody').html('<tr><td colspan="10" class="text-center">Loading...</td></tr>');
                    },
                    success: function (res) {
                        $('#inwardList').html(res);
                        applyColumnVisibility();
                    },
                    error: function (err) {
                        toastr.error('Failed to load data');
                    }
                });
            }

            $(document).on('submit', '#searchForm', function (e) {
                e.preventDefault();
                let search = $(this).find('input[name="search"]').val();
                executeInwardSearch(search);
                return false;
            });

            $(document).on('keydown', '#searchForm input[name="search"]', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    let search = $(this).val();
                    executeInwardSearch(search);
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
                    executeInwardSearch(search);
                }, 300); // 300ms delay
            });
        });


        // Barcode Garnette
        $(document).ready(function () {
            let currentBarcodeInwardInvoiceId = null;

            function updateModalBarcodeStats() {
                let totalSelectedBarcodes = 0;
                let checkedCount = 0;
                $('.modal-item-check:checked').each(function () {
                    checkedCount++;
                    let barcodesCount = parseInt($(this).data('barcodes-count') || 0);
                    let itemQty = parseInt($(this).data('qty') || 0);
                    totalSelectedBarcodes += (barcodesCount > 0 ? barcodesCount : itemQty);
                });
                $('#selectedBarcodeCount').text(totalSelectedBarcodes > 0 ? totalSelectedBarcodes : checkedCount);
            }

            $(document).on('change', '#selectAllModalItems', function () {
                $('.modal-item-check').prop('checked', $(this).is(':checked'));
                updateModalBarcodeStats();
            });

            $(document).on('change', '.modal-item-check', function () {
                let total = $('.modal-item-check').length;
                let checked = $('.modal-item-check:checked').length;
                $('#selectAllModalItems').prop('checked', total > 0 && total === checked);
                updateModalBarcodeStats();
            });

            function inwardProductLoad(id){
                currentBarcodeInwardInvoiceId = id;
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

                                rows += `
                             <tr>
                                 <td class="text-center">
                                     <input class="form-check-input modal-item-check cursor-pointer" 
                                            type="checkbox" 
                                            value="${item.id}" 
                                            data-barcodes-count="${item.barcodes_count || 0}"
                                            data-qty="${item.quantity || 0}"
                                            checked>
                                 </td>
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
                            $('#selectAllModalItems').prop('checked', true);
                            updateModalBarcodeStats();
                            $("#barcode-generate-modal").modal("show");
                        }
                    },

                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.error || "Failed to load barcode items");
                    }
                });
            }

            // Generate All Barcodes in Modal
            $(document).on('click', '#btnGenerateAllModalBarcodes', function (e) {
                e.preventDefault();
                if (!currentBarcodeInwardInvoiceId) return;

                let btn = $(this);
                let originalHtml = btn.html();

                let pendingQty = 0;
                $('.modal-item-check').each(function () {
                    let qty = parseInt($(this).data('qty') || 0);
                    let existing = parseInt($(this).data('barcodes-count') || 0);
                    if (qty > existing) {
                        pendingQty += (qty - existing);
                    }
                });

                if (pendingQty <= 0) {
                    toastr.info("All barcodes for this bill have already been generated!");
                    return;
                }

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Generating...');
                showCustomLoader();

                $.ajax({
                    url: "{{ route('shop.inwardProduct.generateAllBarcodes', ':id') }}".replace(':id', currentBarcodeInwardInvoiceId),
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        btn.prop('disabled', false).html(originalHtml);
                        HoldOn.close();
                        if (res.success) {
                            toastr.success(res.success);
                            inwardProductLoad(currentBarcodeInwardInvoiceId);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html(originalHtml);
                        HoldOn.close();
                        toastr.error(xhr.responseJSON?.error || "Failed to generate all barcodes");
                    }
                });
            });

            // Print Barcodes for Checked Items in Modal
            $(document).on('click', '#btnPrintSelectedModalBarcodes', function (e) {
                e.preventDefault();
                if (!currentBarcodeInwardInvoiceId) return;

                let checkedIds = [];
                let ungeneratedCount = 0;

                $('.modal-item-check:checked').each(function () {
                    checkedIds.push($(this).val());
                    let qty = parseInt($(this).data('qty') || 0);
                    let existing = parseInt($(this).data('barcodes-count') || 0);
                    if (existing === 0) {
                        ungeneratedCount++;
                    }
                });

                if (checkedIds.length === 0) {
                    toastr.warning("Please select at least one item to print barcodes.");
                    return;
                }

                function executePrint(invoiceId, productIds) {
                    let form = $('#bulkBarcodePrintForm');
                    let printUrl = "{{ route('shop.inwardProduct.printMultipleBarcodes', ':id') }}".replace(':id', invoiceId);
                    form.attr('action', printUrl);
                    $('#bulk_inward_product_ids').val(productIds.join(','));
                    form.submit();
                }

                if (ungeneratedCount > 0) {
                    Swal.fire({
                        title: 'Generate Missing Barcodes?',
                        text: `${ungeneratedCount} selected item(s) do not have barcodes generated yet. Generate them now and print together?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#0d6efd',
                        confirmButtonText: '<i class="bi bi-cpu me-1"></i> Generate & Print All',
                        cancelButtonText: '<i class="bi bi-printer me-1"></i> Print Only Generated'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showCustomLoader();
                            $.ajax({
                                url: "{{ route('shop.inwardProduct.generateAllBarcodes', ':id') }}".replace(':id', currentBarcodeInwardInvoiceId),
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    inward_product_ids: checkedIds
                                },
                                success: function (res) {
                                    HoldOn.close();
                                    inwardProductLoad(currentBarcodeInwardInvoiceId);
                                    executePrint(currentBarcodeInwardInvoiceId, checkedIds);
                                },
                                error: function (xhr) {
                                    HoldOn.close();
                                    toastr.error(xhr.responseJSON?.error || "Failed to generate barcodes");
                                }
                            });
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            executePrint(currentBarcodeInwardInvoiceId, checkedIds);
                        }
                    });
                } else {
                    executePrint(currentBarcodeInwardInvoiceId, checkedIds);
                }
            });

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
            if (text === null || text === undefined || text === '') return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

    </script>
@endpush