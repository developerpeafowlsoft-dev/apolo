@extends('layouts.app')
@section('header-title', __('Item Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Item Master')}}
        </h4>
        <div>
            <button type="button" id="create-modal-btn" class="btn py-2 btn-primary">
                <i class="bi bi-patch-plus"></i>
                {{ __('Create New') }}
            </button>
        </div>
    </div>

    <!-- Filter Product Modal -->
    <form action="" method="GET">
        <div class="modal fade" id="filterItemMasterModal" tabindex="-1" aria-labelledby="filterItemMasterModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterItemMasterModalLabel">{{ __('Filter Item Master') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <x-select label="Category" name="categoryFilter" placeholder="Select Category">
                                <option value="">
                                    {{ __('Select Category') }}
                                </option>
                                @foreach ($categorieFilters as $category)
                                    <option value="{{ $category->id }}"
                                            {{ request('categoryFilter') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mt-3">
                            <x-select label="Brand" name="brandFilter" placeholder="All Brand">
                                <option value="">
                                    {{ __('All Brand') }}
                                </option>
                                @foreach ($brandFilters as $brand)
                                    <option value="{{ $brand->id }}"
                                            {{ request('brandFilter') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="mt-3">
                            <x-select label="Color" name="colorFilter" placeholder="All Color">
                                <option value="">
                                    {{ __('All Color') }}
                                </option>
                                @foreach ($colorFilters as $color)
                                    <option value="{{ $color->id }}"
                                            {{ request('colorFilter') == $color->id ? 'selected' : '' }}>
                                        {{ $color->name }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                    <div class=" modal-footer d-flex justify-content-between flex-wrap gap-2">
                        <a href="{{ route('shop.itemMaster.index') }}" class="btn btn-light py-2 px-4">
                            {{ __('Reset') }}
                        </a>

                        <button type="submit" class="btn btn-primary">
                            {{ __('Apply Filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- End Filter Product Modal -->

    <div class="card mt-4">
        <div class="card-body">
            <form action="" class="d-flex align-items-center justify-content-end gap-3 mb-3 border-bottom pb-3 flex-wrap">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#filterItemMasterModal">
                    {{ __('Filter') }}
                </button>

                <div class="input-group" style="max-width: 400px">
                    <input type="text" name="search" class="form-control"
                           placeholder="{{ __('Search by item master') }}" value="{{ request('search') }}">
                    <button type="submit" class="input-group-text btn btn-primary">
                        <i class="fa fa-search"></i> {{ __('Search') }}
                    </button>
                </div>
            </form>

            <div class="row">
                <div class="col-12" id="itemMasterList">
                    @include('shop.item-master.partials.item-master-table', ['itemMasters' => $itemMasters])
                </div>
            </div>
        </div>
    </div>


    @include('shop.components-modal.item-master-modal')

@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/holdon/HoldOn.min.css') }}" type="text/css" />
    <style>
        #itemMasterList table tr:not(:first-child):not(.ui-datepicker-calendar tr) {
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

        // $('#itme-master-modal').on('shown.bs.modal', function () {
        //     $('select').select2({
        //         dropdownParent: $('#itme-master-modal')
        //     });
        // });
    </script>
    <script src="{{ asset('assets/scripts/shortKey.js') }}"></script>
    <script src="{{ asset('assets/css/holdon/HoldOn.min.js') }}"></script>
    @include('shop.components-modal.master-modal.item-master-modal-script')
    <script>
        $(document).on('click', '#create-modal-btn,.editData, #modelClose', function () {

            if ($(this).is('#create-modal-btn')) {
                showCustomLoader('center')
                $("#modalTitle").text("Add Item Master");
                $("#btnSubmit").text("Submit");
                $("#generateShortCode").show();
                modelItemMasterDataLoad();
                $('#item_short_name').val('');

                generateItemMasterCode();

            } else if($(this).is('.editData')) {
                var id = $(this).data('id');
                showCustomLoader('center');
                $("#modalTitle").text("Update Item Master");
                $("#btnSubmit").text("Update");
                $("#generateShortCode").hide();

                if (id !== '') {

                    modelItemMasterDataLoad(function () {

                        $.ajax({
                            url: `/shop/item-master/${id}/edit`,
                            type: 'GET',
                            success: function (res) {
                                console.log(res);

                                $('#name').val(res.itemMaster.name);
                                $('#item_short_name').val(res.itemMaster.item_short_name || '');
                                $('#slug').text(res.itemMaster.slug);
                                $('#brand_id').val(res.itemMaster.brand_id).trigger('change');
                                $('#code').val(res.itemMaster.code).trigger('change');
                                $("#hsn_master_id").val(res.itemMaster.hsn_master_id).trigger('change');
                                $("#vat_tax_id").val(res.itemMaster.vat_tax_id).trigger('change');
                                $("#vat_tax_id_hidden").val(res.itemMaster.vat_tax_id);
                                $("#unit_id").val(res.itemMaster.unit_id).trigger('change');
                                $("#material_id").val(res.itemMaster.material_id).trigger('change');


                                $('#buy_price').val(res.itemMaster.buy_price);
                                $('#price').val(res.itemMaster.price);
                                $('#discount_percentage').val(res.itemMaster.discount_price);
                                $('#mrp').val(res.itemMaster.mrp);
                                $('#mark_up').val(res.itemMaster.mark_up);
                                $('#mark_down').val(res.itemMaster.mark_down);
                                $('#quantity').val(res.itemMaster.quantity);

                                $("#salesman_id").val(res.itemMaster.salesman_id).trigger('change');
                                $("#commission_type").val(res.itemMaster.commission_type).trigger('change');

                                $('#salesman_comm').val(res.itemMaster.salesman_comm);
                                $('#salesman_comm_amt').val(res.itemMaster.salesman_comm_amt);

                                $('#is_online_product').prop('checked', res.itemMaster.is_online_product);
                                $('#item_id').val(id);

                                // $('select[name="colorIds[]"]').val(res.colorIds).trigger('change');
                                // $('select[name="sizeIds[]"]').val(res.sizeIds).trigger('change');

                                $('#category').val(res.categoryIds).trigger('change');
                                loadSubcategories(res.categoryIds, res.subCategoryIds);
                                // $('#colorBox').hide();
                                // $('#sizeBox').hide();
                                renderColorsWithPrices(res.itemMaster.colors);
                                renderSizesWithPrices(res.itemMaster.sizes);
                                setDefaultPrice();

                                if(res.colorIds.length == '0'){
                                    $('#colorBox').hide();
                                }
                                if(res.sizeIds.length == '0'){
                                    $('#sizeBox').hide();
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
                $("#itme-master-modal").modal("hide");
            }
        });
    </script>
@endpush