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
    .select2-container--open {
        z-index: 99999999 !important;
    }
    .select2-dropdown {
        z-index: 99999999 !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        padding: 6px 10px !important;
        outline: none !important;
        font-size: 13px !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }
</style>

<div class="modal fade" id="itme-master-modal">
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
                                    <span>{{ __('Item No / ID') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <div class="input-group flex-nowrap">
                                    <input type="text" class="form-control" name="code" placeholder="{{ __('Enter Item No / ID') }}" id="code" value="{{ old('code') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);" required="true" maxlength="15">
                                    <button class="btn btn-outline-secondary" type="button" id="generateShortCode" onclick="generateItemMasterCode()" data-toggle="tooltip" data-placement="top" title="{{ __('Generate Item No / ID') }}">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                                <span id="codeErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Select Brand') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="brand_id" required="true"></x-select>
                                <span id="brand_idErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Select Category') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="category" required="true"></x-select>
                                <span id="categoryErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-8">
                                <label class="form-label">
                                    <span>{{ __('Select Sub Categories') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
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
                                <label class="form-label">
                                    <span>{{ __('Select Unit') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="unit_id" id="unit_id" required="true"></x-select>
                                <span id="unit_idErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Select HSN Code') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="hsn_master_id" required="true"></x-select>
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
                                <label class="form-label">
                                    <span>{{ __('Select Material') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="material_id" id="material_id"></x-select>
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
                                <x-select name="salesman_id"></x-select>
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
