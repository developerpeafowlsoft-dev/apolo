<style>
    /* Add Design Master Modal Styling Overrides */
    #design-master-modal .modal-content {
        border-radius: 14px !important;
        border: none !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        background: #f8fafc;
        overflow: hidden;
    }
    #design-master-modal .modal-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
        color: #fff !important;
        border-bottom: none !important;
        padding: 16px 24px !important;
    }
    #design-master-modal .modal-title {
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
        font-size: 16px !important;
        color: #fff !important;
    }
    #design-master-modal .btn-close {
        filter: invert(1) grayscale(1) brightness(2) !important;
        opacity: 0.8 !important;
        outline: none !important;
        box-shadow: none !important;
    }
    #design-master-modal .btn-close:hover {
        opacity: 1 !important;
    }
    #design-master-modal .modal-body {
        padding: 20px 24px !important;
        max-height: 70vh;
        overflow-y: auto;
    }
    #design-master-modal .form-section-card {
        background: #fff !important;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        padding: 18px !important;
        margin-bottom: 16px !important;
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.02) !important;
        transition: border-color 0.2s;
    }
    #design-master-modal .form-section-card:focus-within {
        border-color: #cbd5e1 !important;
    }
    #design-master-modal .section-title {
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
    #design-master-modal .form-label {
        font-weight: 600 !important;
        color: #334155 !important;
        font-size: 12px !important;
        margin-bottom: 5px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    #design-master-modal .form-control, #design-master-modal .form-select {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 6px 10px !important;
        font-size: 13px !important;
        transition: all 0.15s !important;
    }
    #design-master-modal .form-control:focus, #design-master-modal .form-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    /* Select2 custom overrides inside modal */
    #design-master-modal .select2-container--default .select2-selection--single,
    #design-master-modal .select2-container--default .select2-selection--multiple {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        min-height: 33px !important;
        padding: 1px 4px !important;
    }
    #design-master-modal .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 3px !important;
    }
    #design-master-modal .select2-container--default.select2-container--focus .select2-selection--single,
    #design-master-modal .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    #design-master-modal .modal-footer {
        background: #f1f5f9 !important;
        border-top: 1px solid #e2e8f0 !important;
        padding: 12px 24px !important;
    }
    #design-master-modal .btn-submit {
        background-color: #1e293b !important;
        border-color: #1e293b !important;
        color: #fff !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        padding: 8px 30px !important;
        transition: all 0.2s !important;
    }
    #design-master-modal .btn-submit:hover {
        background-color: #0f172a !important;
    }
    #design-master-modal .btn-reset {
        border-radius: 6px !important;
        padding: 8px 20px !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
    }
    #design-master-modal .btn-reset:hover {
        background-color: #f1f5f9 !important;
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

<div class="modal fade" id="design-master-modal">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="formDataDesignMaster" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">{{ __('Add Design Master') }}</h5>
                    <button type="button" class="btn-close" id="modelClose" data-modal-name="design-master-modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Section 1: Design & Item Details -->
                    <div class="form-section-card">
                        <div class="section-title">
                            {{ __('Design & Item Details') }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Design Number') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <div class="input-group flex-nowrap">
                                    <input type="text" class="form-control" name="design_number" placeholder="Enter Design Number" id="design_number" value="{{ old('design_number') }}" required="true">
                                    <button class="btn btn-outline-secondary" type="button" id="generateShortCode" onclick="generateCode()" data-toggle="tooltip" data-placement="top" title="Generate Design Number">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                                <span id="design_numberErrorMessage" class="text-danger errorSpan"></span>
                            </div>
                            <input type="hidden" name="design_id" id="design_id">

                            <div class="col-md-6 col-lg-8">
                                <label class="form-label">
                                    <span>{{ __('Item Name') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="itemname" required="true"></x-select>
                                <span id="itemnameErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Purchase Stock Quantity') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="quantity" placeholder="Purchase Stock Quantity" onlyNumber="true" value="100" required="true"/>
                                <span id="quantityErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Min Stock') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="minStock" placeholder="Min Stock" onlyNumber="true" value="0"/>
                                <span id="minStockErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Max Stock') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="maxStock" placeholder="Max Stock" onlyNumber="true" value="0"/>
                                <span id="maxStockErrorMessage" class="text-danger errorSpan"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Pricing & Margins -->
                    <div class="form-section-card">
                        <div class="section-title">
                            {{ __('Pricing & Margins') }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Purchase Price') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="buy_price" placeholder="Purchase Price" onlyNumber="true" value="0.00" required="true"/>
                                <span id="buy_priceErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Selling Price') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="price" placeholder="Selling Price" required="true" onlyNumber="true" value="0.00"/>
                                <span id="priceErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Discount Percentage(%)') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="discount_percentage" placeholder="Discount Percentage" onlyNumber="true" value="0" />
                                <span id="discount_percentageErrorMessage" class="text-danger errorSpan"></span>
                                <span class="text-warning text-xs d-block font-monospace" style="font-size: 11px;">{{ __('Calculated from Selling Price') }}</span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('MRP') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <input type="text" class="form-control disabledCls" name="mrp" placeholder="MRP" id="mrp" value="0.00" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);" required="true" readonly>
                                <span id="mrpErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Mark Up(%)') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="mark_up" placeholder="Enter Mark Up" onlyNumber="true" value="0.00" required="true"/>
                                <span id="mark_upErrorMessage" class="text-danger errorSpan"></span>
                                <span class="text-warning text-xs d-block font-monospace" style="font-size: 11px;">{{ __('Profit % based on Purchase Price') }}</span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Mark Down(%)') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-input type="text" name="mark_down" placeholder="Enter Mark Down" onlyNumber="true" value="0.00" required="true"/>
                                <span id="mark_downErrorMessage" class="text-danger errorSpan"></span>
                                <span class="text-warning text-xs d-block font-monospace" style="font-size: 11px;">{{ __('Reduction % based on MRP') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Vendor & Account Info -->
                    <div class="form-section-card">
                        <div class="section-title">
                            {{ __('Vendor & Account Info') }}
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Account Master') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <x-select name="account_master" required="true"></x-select>
                                <span id="account_masterErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Account Master Name') }} <span class="text-danger">*</span></span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <input type="text" class="form-control disabledCls" name="account_master_name" placeholder="Account Master Name" id="account_master_name" required="true" readonly>
                                <span id="account_master_nameErrorMessage" class="text-danger errorSpan"></span>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <span>{{ __('Remark') }}</span>
                                    <kbd class="modal-kbd-hint">Enter ↵</kbd>
                                </label>
                                <textarea name="remark" id="remark" class="form-control" rows="1" placeholder="Enter Remark"></textarea>
                                <span id="remarkErrorMessage" class="text-danger errorSpan"></span>
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
