<style>
    /* Add Item Master Modal Styling Overrides */
    #itme-master-modal .modal-content {
        border-radius: 14px !important;
        border: none !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        background: #f8fafc;
        overflow: hidden;
    }
    #itme-master-modal .modal-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
        color: #fff !important;
        border-bottom: none !important;
        padding: 16px 24px !important;
    }
    #itme-master-modal .modal-title {
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
        font-size: 16px !important;
        color: #fff !important;
    }
    #itme-master-modal .btn-close {
        filter: invert(1) grayscale(1) brightness(2) !important;
        opacity: 0.8 !important;
        outline: none !important;
        box-shadow: none !important;
    }
    #itme-master-modal .btn-close:hover {
        opacity: 1 !important;
    }
    #itme-master-modal .modal-body {
        padding: 20px 24px !important;
        max-height: 70vh;
        overflow-y: auto;
    }
    #itme-master-modal .form-section-card {
        background: #fff !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        padding: 18px !important;
        margin-bottom: 16px !important;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.02) !important;
        transition: border-color 0.2s;
    }
    #itme-master-modal .form-section-card:focus-within {
        border-color: #cbd5e1 !important;
    }
    #itme-master-modal .section-title {
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: #475569 !important;
        margin-bottom: 14px !important;
        letter-spacing: 0.5px !important;
        border-left: 3px solid #3b82f6 !important;
        padding-left: 8px !important;
        line-height: 1.2 !important;
    }
    #itme-master-modal .form-label {
        font-weight: 600 !important;
        color: #334155 !important;
        font-size: 12px !important;
        margin-bottom: 5px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    #itme-master-modal .form-control, #itme-master-modal .form-select {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 6px 10px !important;
        font-size: 13px !important;
        transition: all 0.15s !important;
    }
    #itme-master-modal .form-control:focus, #itme-master-modal .form-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    /* Select2 custom overrides inside modal */
    #itme-master-modal .select2-container--default .select2-selection--single,
    #itme-master-modal .select2-container--default .select2-selection--multiple {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        min-height: 33px !important;
        padding: 1px 4px !important;
    }
    #itme-master-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 3px !important;
    }
    #itme-master-modal .select2-container--default.select2-container--focus .select2-selection--single,
    #itme-master-modal .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    /* Nested Quick Create Brand/Category/SubCategory/Unit/Material Modal Stacking Overrides */
    #modalQuickCreateBrand, #modalQuickCreateCategory, #modalQuickCreateSubCategory, #modalQuickCreateUnit, #modalQuickCreateMaterial {
        z-index: 1080 !important;
    }
    #modalQuickCreateBrand .modal-dialog, #modalQuickCreateCategory .modal-dialog, #modalQuickCreateSubCategory .modal-dialog, #modalQuickCreateUnit .modal-dialog, #modalQuickCreateMaterial .modal-dialog {
        z-index: 1085 !important;
        position: relative !important;
    }
    #modalQuickCreateBrand .modal-content, #modalQuickCreateCategory .modal-content, #modalQuickCreateSubCategory .modal-content, #modalQuickCreateUnit .modal-content, #modalQuickCreateMaterial .modal-content {
        z-index: 1090 !important;
        position: relative !important;
        pointer-events: auto !important;
    }
    body.modal-open .modal-backdrop + .modal-backdrop {
        z-index: 1075 !important;
    }
    #itme-master-modal .modal-footer {
        background: #f1f5f9 !important;
        border-top: 1px solid #e2e8f0 !important;
        padding: 12px 24px !important;
    }
    #itme-master-modal .btn-submit {
        background-color: #1e293b !important;
        border-color: #1e293b !important;
        color: #fff !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        padding: 8px 30px !important;
        transition: all 0.2s !important;
    }
    #itme-master-modal .btn-submit:hover {
        background-color: #0f172a !important;
    }
    #itme-master-modal .btn-reset {
        border-radius: 6px !important;
        padding: 8px 20px !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
    }
    #itme-master-modal .btn-reset:hover {
        background-color: #f1f5f9 !important;
    }
    .modal-kbd-hint {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
        border-radius: 3px;
        font-family: SFMono-Regular, Consolas, monospace;
        font-size: 8px;
        padding: 0px 3px;
        font-weight: 700;
        text-transform: none;
    }
</style>

<div class="modal fade" tabindex="-1" id="itme-master-modal">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="formDataItemMaster" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">{{ __('Add Item Master') }}</h5>
                    <button type="button" class="btn-close" id="modelClose" data-modal-name="itme-master-modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Section 1: Basic Information -->
                    <div class="form-section-card">
                        <div class="section-title">
                            {{ __('Basic Information') }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Item Name') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input name="name" type="text" placeholder="Enter Item Name" required="true" />
                                <span id="nameErrorMessage" class="text-danger errorSpan"></span>
                                <label class="mt-1 text-primary d-block font-monospace text-xs" id="slug" style="font-size: 11px;"></label>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Item Short Name') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input name="item_short_name" id="item_short_name" type="text" placeholder="Enter Item Short Name" />
                                <span id="item_short_nameErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Barcode') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <div class="input-group flex-nowrap">
                                    <input type="text" class="form-control disabledCls" name="code" placeholder="Enter Barcode" id="code" value="{{ old('barcode') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);" required="true" maxlength="8" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="generateShortCode" onclick="generateItemMasterCode()" data-toggle="tooltip" data-placement="top" title="Generate Barcode">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                                <span id="codeErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label d-flex align-items-center justify-content-between mb-1">
                                    <div>
                                        <span>{{ __('Select Brand') }} <span class="text-danger">*</span></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center" id="btnQuickCreateBrand" style="width: 22px; height: 22px; border-radius: 4px;" title="{{ __('Add New Brand (Alt + B / Option + B)') }}">
                                            <i class="bi bi-plus-lg" style="font-size: 11px;"></i>
                                        </button>
                                        <kbd class="modal-kbd-hint">Alt + B</kbd>
                                    </div>
                                </label>
                                <x-select name="brand_id" placeholder="Select Brand" required="true"></x-select>
                                <span id="brand_idErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label d-flex align-items-center justify-content-between mb-1">
                                    <div>
                                        <span>{{ __('Select Category') }} <span class="text-danger">*</span></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center" id="btnQuickCreateCategory" style="width: 22px; height: 22px; border-radius: 4px;" title="{{ __('Add New Category (Alt + C / Option + C)') }}">
                                            <i class="bi bi-plus-lg" style="font-size: 11px;"></i>
                                        </button>
                                        <kbd class="modal-kbd-hint">Alt + C</kbd>
                                    </div>
                                </label>
                                <x-select name="category" placeholder="Select Category" required="true"></x-select>
                                <span id="categoryErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label d-flex align-items-center justify-content-between mb-1">
                                    <div>
                                        <span>{{ __('Select Sub Categories') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center" id="btnQuickCreateSubCategory" style="width: 22px; height: 22px; border-radius: 4px;" title="{{ __('Add New Sub Category (Alt + S / Option + S)') }}">
                                            <i class="bi bi-plus-lg" style="font-size: 11px;"></i>
                                        </button>
                                        <kbd class="modal-kbd-hint">Alt + S</kbd>
                                    </div>
                                </label>
                                <select name="sub_category[]" id="sub_category" data-placeholder="Select Sub Category" class="form-control select2" multiple style="width: 100%">
                                    <option value="" disabled>{{ __('Select Sub Category') }}</option>
                                </select>
                                <span id="sub_categoryErrorMessage" class="text-danger errorSpan"></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="id" id="item_id">

                    <!-- Section 2: Attributes & Inventory Taxonomy -->
                    <div class="form-section-card">
                        <div class="section-title">
                            {{ __('Attributes & Tax') }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Select Color') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <select name="colorIds[]" id="colorIds" data-placeholder="Select Color" class="form-control colorSelect" multiple style="width: 100%"></select>
                                <span id="colorErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Select Size') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <select name="sizeIds[]" id="sizeIds" data-placeholder="Select Size" class="form-control sizeSelector" multiple="true" style="width: 100%"></select>
                                <span id="sizeErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label d-flex align-items-center justify-content-between mb-1">
                                    <div>
                                        <span>{{ __('Select Unit') }} <span class="text-danger">*</span></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center" id="btnQuickCreateUnit" style="width: 22px; height: 22px; border-radius: 4px;" title="{{ __('Add New Unit (Alt + U / Option + U)') }}">
                                            <i class="bi bi-plus-lg" style="font-size: 11px;"></i>
                                        </button>
                                        <kbd class="modal-kbd-hint">Alt + U</kbd>
                                    </div>
                                </label>
                                <x-select name="unit_id" id="unit_id" placeholder="Select Unit" required="true"></x-select>
                                <span id="unit_idErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Select HSN Code') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="hsn_master_id" placeholder="Select HSN Code" required="true"></x-select>
                                <span id="hsn_master_idErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Select Tax Type') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <select id="vat_tax_id" class="form-control select2" disabled></select>
                                <span id="vat_tax_idErrorMessage" class="text-danger errorSpan"></span>
                                <input type="hidden" name="vat_tax_id" id="vat_tax_id_hidden">
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label d-flex align-items-center justify-content-between mb-1">
                                    <div>
                                        <span>{{ __('Select Material') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center" id="btnQuickCreateMaterial" style="width: 22px; height: 22px; border-radius: 4px;" title="{{ __('Add New Material (Alt + M / Option + M)') }}">
                                            <i class="bi bi-plus-lg" style="font-size: 11px;"></i>
                                        </button>
                                        <kbd class="modal-kbd-hint">Alt + M</kbd>
                                    </div>
                                </label>
                                <x-select name="material_id" id="material_id" placeholder="Select Material"></x-select>
                                <span id="material_idErrorMessage" class="text-danger errorSpan"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Salesman & Commission -->
                    <div class="form-section-card">
                        <div class="section-title">
                            {{ __('Salesman & Settings') }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Select Salesman') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="salesman_id" placeholder="Select Salesman"></x-select>
                                <span id="salesman_idErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4" id="commission_typeDiv" style="display: none">
                                <label class="form-label">
                                    <span>{{ __('Commission Type') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="commission_type" id="commission_type">
                                    <option value="">{{ __('--Select Commission Type--') }}</option>
                                    <option value="1" data-type="comm%">{{ __('Commission Percentage (%)') }}</option>
                                    <option value="2" data-type="comm₹">{{ __('Commission Amount (₹)') }}</option>
                                </x-select>
                                <span id="commission_typeErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4" id="salesman_comm_div" style="display: none">
                                <label class="form-label">
                                    <span>{{ __('Commission Percentage (%)') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="salesman_comm" placeholder="Enter Commission Percentage (%)" onlyNumber="true" value="0.00" />
                                <span id="salesman_commErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4" id="salesman_comm_amt_div" style="display: none">
                                <label class="form-label">
                                    <span>{{ __('Commission Amount (₹)') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="salesman_comm_amt" placeholder="Enter Commission Amount (₹)" onlyNumber="true" value="0.00" />
                                <span id="salesman_comm_amtErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4 d-flex align-items-center">
                                <div class="form-check mt-md-4">
                                    <input type="checkbox" id="is_online_product" name="is_online_product" class="form-check-input" {{ old('is_online') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_online_product">{{ __('Is Online Product') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Dynamic Extra Tables -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-0 position-relative overflow-hidden" id="colorBox" style="display: none">
                                <p class="fw-bolder box-title bg-light p-2 mb-0 border-bottom text-dark" style="font-size: 13px;">
                                    {{ __('Color wise extra price') }}
                                </p>
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Extra Price') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selectedColorsTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-0 position-relative overflow-hidden" id="sizeBox" style="display: none">
                                <p class="fw-bold box-title bg-light p-2 mb-0 border-bottom text-dark" style="font-size: 13px;">
                                    {{ __('Size wise extra price') }}
                                </p>
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Size') }}</th>
                                            <th>{{ __('Extra Price') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selectedSizesTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn btn-outline-secondary btn-reset">
                        {{ __('Reset') }}
                    </button>
                    <button type="submit" class="btn btn-submit" id="btnSubmit">
                        {{ __('Submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=== Quick Create Brand Modal ===-->
<div class="modal fade" id="modalQuickCreateBrand" tabindex="-1" aria-labelledby="modalQuickCreateBrandLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <form id="formQuickCreateBrand" method="POST" action="{{ route('shop.brand.store') }}">
                @csrf
                <div class="modal-header d-flex align-items-center justify-content-between" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                    <h6 class="modal-title m-0 text-white font-weight-bold" id="modalQuickCreateBrandLabel">
                        <i class="bi bi-patch-plus me-1"></i> {{ __('Create New Brand') }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" id="btnCloseQuickBrandX" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="z-index: 1080; cursor: pointer; position: relative; opacity: 1;"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="mb-3">
                        <label for="quick_brand_name" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Brand Name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="quick_brand_name" name="name" placeholder="{{ __('Enter Brand Name') }}" required />
                        <span id="quick_brand_name_error" class="text-danger small mt-1 d-block"></span>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; padding: 10px 20px;">
                    <button type="button" class="btn btn-secondary py-1 px-3" id="btnCloseQuickBrandBtn" data-bs-dismiss="modal" data-dismiss="modal" style="z-index: 1080; cursor: pointer; position: relative;">
                        {{ __('Close') }}
                    </button>
                    <button type="submit" class="btn btn-primary py-1 px-4" id="btnSubmitQuickBrand">
                        {{ __('Save Brand') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=== Quick Create Category Modal ===-->
<div class="modal fade" id="modalQuickCreateCategory" tabindex="-1" aria-labelledby="modalQuickCreateCategoryLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <form id="formQuickCreateCategory" method="POST" action="{{ route('shop.category.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header d-flex align-items-center justify-content-between" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                    <h6 class="modal-title m-0 text-white font-weight-bold" id="modalQuickCreateCategoryLabel">
                        <i class="bi bi-patch-plus me-1"></i> {{ __('Create New Category') }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" id="btnCloseQuickCategoryX" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="z-index: 1080; cursor: pointer; position: relative; opacity: 1;"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="mb-3">
                        <label for="quick_category_name" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Category Name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="quick_category_name" name="name" placeholder="{{ __('Enter Category Name') }}" required />
                        <span id="quick_category_name_error" class="text-danger small mt-1 d-block"></span>
                    </div>
                    <div class="mb-3">
                        <label for="quick_category_thumbnail" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Category Image') }} <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="quick_category_thumbnail" name="thumbnail" accept="image/*" required onchange="previewQuickCategoryImg(this)" />
                        <span id="quick_category_thumbnail_error" class="text-danger small mt-1 d-block"></span>
                        <div class="mt-2 text-center d-none" id="quickCategoryImgPreviewContainer">
                            <img id="quickCategoryImgPreviewTag" src="#" alt="Preview" class="img-thumbnail" style="max-height: 100px;" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="quick_category_description" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Description') }}
                        </label>
                        <textarea name="description" id="quick_category_description" class="form-control" rows="3" placeholder="{{ __('Enter description') }}"></textarea>
                        <span id="quick_category_description_error" class="text-danger small mt-1 d-block"></span>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; padding: 10px 20px;">
                    <button type="button" class="btn btn-secondary py-1 px-3" id="btnCloseQuickCategoryBtn" data-bs-dismiss="modal" data-dismiss="modal" style="z-index: 1080; cursor: pointer; position: relative;">
                        {{ __('Close') }}
                    </button>
                    <button type="submit" class="btn btn-primary py-1 px-4" id="btnSubmitQuickCategory">
                        {{ __('Save Category') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=== Quick Create Sub Category Modal ===-->
<div class="modal fade" id="modalQuickCreateSubCategory" tabindex="-1" aria-labelledby="modalQuickCreateSubCategoryLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <form id="formQuickCreateSubCategory" method="POST" action="{{ route('shop.subcategory.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header d-flex align-items-center justify-content-between" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                    <h6 class="modal-title m-0 text-white font-weight-bold" id="modalQuickCreateSubCategoryLabel">
                        <i class="bi bi-patch-plus me-1"></i> {{ __('Create New Sub Category') }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" id="btnCloseQuickSubCategoryX" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="z-index: 1080; cursor: pointer; position: relative; opacity: 1;"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="mb-3">
                        <label for="quick_subcategory_category_select" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Select Category') }} <span class="text-danger">*</span>
                        </label>
                        <select name="category[]" id="quick_subcategory_category_select" class="form-control select2" data-placeholder="{{ __('Select Category') }}" multiple required style="width: 100%;">
                            @foreach(\App\Models\Category::active()->get() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <span id="quick_subcategory_category_error" class="text-danger small mt-1 d-block"></span>
                    </div>
                    <div class="mb-3">
                        <label for="quick_subcategory_name" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Sub Category Name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="quick_subcategory_name" name="name" placeholder="{{ __('Enter Sub Category Name') }}" required />
                        <span id="quick_subcategory_name_error" class="text-danger small mt-1 d-block"></span>
                    </div>
                    <div class="mb-3">
                        <label for="quick_subcategory_thumbnail" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Sub Category Image') }} <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="quick_subcategory_thumbnail" name="thumbnail" accept="image/*" required onchange="previewQuickSubCategoryImg(this)" />
                        <span id="quick_subcategory_thumbnail_error" class="text-danger small mt-1 d-block"></span>
                        <div class="mt-2 text-center d-none" id="quickSubCategoryImgPreviewContainer">
                            <img id="quickSubCategoryImgPreviewTag" src="#" alt="Preview" class="img-thumbnail" style="max-height: 100px;" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; padding: 10px 20px;">
                    <button type="button" class="btn btn-secondary py-1 px-3" id="btnCloseQuickSubCategoryBtn" data-bs-dismiss="modal" data-dismiss="modal" style="z-index: 1080; cursor: pointer; position: relative;">
                        {{ __('Close') }}
                    </button>
                    <button type="submit" class="btn btn-primary py-1 px-4" id="btnSubmitQuickSubCategory">
                        {{ __('Save Sub Category') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=== Quick Create Unit Modal ===-->
<div class="modal fade" id="modalQuickCreateUnit" tabindex="-1" aria-labelledby="modalQuickCreateUnitLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <form id="formQuickCreateUnit" method="POST" action="{{ route('shop.unit.store') }}">
                @csrf
                <div class="modal-header d-flex align-items-center justify-content-between" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                    <h6 class="modal-title m-0 text-white font-weight-bold" id="modalQuickCreateUnitLabel">
                        <i class="bi bi-patch-plus me-1"></i> {{ __('Create New Unit') }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" id="btnCloseQuickUnitX" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="z-index: 1080; cursor: pointer; position: relative; opacity: 1;"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="mb-3">
                        <label for="quick_unit_name" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="quick_unit_name" name="name" placeholder="{{ __('Enter Unit Name') }}" required />
                        <span id="quick_unit_name_error" class="text-danger small mt-1 d-block"></span>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; padding: 10px 20px;">
                    <button type="button" class="btn btn-secondary py-1 px-3" id="btnCloseQuickUnitBtn" data-bs-dismiss="modal" data-dismiss="modal" style="z-index: 1080; cursor: pointer; position: relative;">
                        {{ __('Close') }}
                    </button>
                    <button type="submit" class="btn btn-primary py-1 px-4" id="btnSubmitQuickUnit">
                        {{ __('Save Unit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--=== Quick Create Material Modal ===-->
<div class="modal fade" id="modalQuickCreateMaterial" tabindex="-1" aria-labelledby="modalQuickCreateMaterialLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <form id="formQuickCreateMaterial" method="POST" action="{{ route('shop.material.store') }}">
                @csrf
                <div class="modal-header d-flex align-items-center justify-content-between" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                    <h6 class="modal-title m-0 text-white font-weight-bold" id="modalQuickCreateMaterialLabel">
                        <i class="bi bi-patch-plus me-1"></i> {{ __('Create New Material') }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" id="btnCloseQuickMaterialX" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="z-index: 1080; cursor: pointer; position: relative; opacity: 1;"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="mb-3">
                        <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                            <span>{{ __('Code') }} <span class="text-danger">*</span></span>
                        </label>
                        <div class="input-group flex-nowrap">
                            <input type="text" class="form-control disabledCls" name="code" placeholder="{{ __('Code') }}" id="quick_material_code" value="" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);" required readonly>
                            <button class="btn btn-secondary" type="button" id="btnGenerateQuickMaterialCode" onclick="generateQuickMaterialCode()" data-bs-toggle="tooltip" title="{{ __('Generate Code') }}">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <span id="quick_material_code_error" class="text-danger small mt-1 d-block"></span>
                    </div>
                    <div class="mb-3">
                        <label for="quick_material_name" class="form-label font-weight-bold text-dark mb-1">
                            {{ __('Name') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="quick_material_name" name="name" placeholder="{{ __('Enter Material Name') }}" required />
                        <span id="quick_material_name_error" class="text-danger small mt-1 d-block"></span>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; padding: 10px 20px;">
                    <button type="button" class="btn btn-secondary py-1 px-3" id="btnCloseQuickMaterialBtn" data-bs-dismiss="modal" data-dismiss="modal" style="z-index: 1080; cursor: pointer; position: relative;">
                        {{ __('Close') }}
                    </button>
                    <button type="submit" class="btn btn-primary py-1 px-4" id="btnSubmitQuickMaterial">
                        {{ __('Save Material') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
