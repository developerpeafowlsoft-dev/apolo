<form id="itemForm">
    @csrf
    <div class="card inwardAccountDetails mb-2 shadow-2xs border-0">
        <div class="card-body p-2 px-3">
            <input type="hidden" name="inward_invoice_id" id="inward_invoice_id">

            <div class="inward-header-grid">
                <div>
                    <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                    <input type="text" name="inward_voucher_no" id="inward_voucher_no" class="form-control form-control-sm @error('inward_voucher_no') is-invalid @enderror" placeholder="Voucher No" value="{{ old('inward_voucher_no') }}" required autofocus>
                </div>

                <div>
                    <label class="form-label">Challan No <span class="text-danger">*</span></label>
                    <input type="text" id="inward_challan_no" name="inward_challan_no" placeholder="Challan No" class="form-control form-control-sm @error('inward_challan_no') is-invalid @enderror" value="{{ old('inward_challan_no') }}" required>
                </div>

                <div>
                    <label class="form-label">Date</label>
                    <input type="date" name="purchase_date" id="purchase_date" class="form-control form-control-sm" value="{{ old('purchase_date') }}">
                </div>

                <div>
                    <label class="form-label">Day Name</label>
                    <input type="text" class="form-control form-control-sm disabledCls bg-light text-center" name="purchase_day_name" placeholder="Day Name" id="purchase_day_name" readonly>
                </div>

                <div>
                    <label class="form-label">Time</label>
                    <input type="time" name="purchase_time" id="purchase_time" class="form-control form-control-sm" value="{{ old('purchase_time') }}">
                </div>

                <div>
                    <label class="form-label">Bill Date</label>
                    <input type="date" name="bill_date" id="bill_date" class="form-control form-control-sm" value="{{ old('bill_date') }}">
                </div>

                <div>
                    <label class="form-label">Party Code</label>
                    <input type="text" class="form-control form-control-sm disabledCls bg-light" name="inward_party_code_value" placeholder="Party Code" id="inward_party_code_value" readonly>
                    <input type="hidden" name="inward_party_code" id="inward_party_code">
                    <input type="hidden" id="shop_state_id" value="{{ $shop?->state_id ?? 12 }}">
                    <input type="hidden" id="shop_state_name" value="{{ $shop?->state?->name ?? 'Gujarat' }}">
                    <input type="hidden" id="inward_party_state_id" name="inward_party_state_id" value="">
                    <input type="hidden" id="inward_party_state_name" value="">
                </div>

                <div>
                    <label class="form-label">Party Name</label>
                    <input type="text" class="form-control form-control-sm disabledCls bg-light" name="inward_party_name" placeholder="Party Name" id="inward_party_name" readonly>
                </div>

                <div>
                    <label class="form-label">Total</label>
                    <input type="text" id="inward_total" name="inward_total" placeholder="Total" class="form-control form-control-sm decimal-input text-end @error('inward_total') is-invalid @enderror" value="{{ old('inward_total') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" readonly>
                </div>

                <div>
                    <label class="form-label">Party Limit</label>
                    <input type="text" id="inward_party_limit" name="inward_party_limit" placeholder="Limit" class="form-control form-control-sm disabledCls bg-light text-end @error('inward_party_limit') is-invalid @enderror" value="{{ old('inward_party_limit', '0.00') }}" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="card inwardProductTableCard mb-2 shadow-2xs border-0 dataLoadToShow d-none">
        <div class="card-body p-2">
            <div class="table-responsive" style="overflow-x: hidden;">
                <table class="table table-bordered mb-0 align-middle" id="inwardProductTable" style="overflow: auto;">
                    <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">{{ __('SL') }}</th>
                        <th style="width: 200px;">{{ __('Item') }}</th>
                        <th>{{ __('Design No') }}</th>
                        <th class="text-center">{{ __('Color') }}</th>
                        <th class="text-center">{{ __('Size') }}</th>
                        <th class="text-center">{{ __('Qty') }}</th>
                        <th class="text-end">{{ __('Purc Rate') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th class="text-end">{{ __('Disc %') }}</th>
                        <th class="text-end">{{ __('Disc Amt') }}</th>
                        <th class="text-end">{{ __('Net Rate') }}</th>
                        <th class="text-end">{{ __('MRP') }}</th>
                        <th class="text-end">{{ __('Mark Up%') }}</th>
                        <th class="text-end">{{ __('Mark Dn%') }}</th>
                        <th class="text-center">{{ __('Tax Code') }}</th>
                        <th class="text-end">{{ __('GST %') }}</th>
                        <th class="text-center" style="width: 50px;"><i class="fa-solid fa-trash-can text-muted" style="font-size: 11px;"></i></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot>
                        <tr class="table-total-row">
                            <th class="text-center"></th>
                            <th class="ps-2 text-start text-uppercase font-weight-bold" style="font-size: 11px; color: #475569; letter-spacing: 0.3px;">{{ __('Total') }}</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <input type="text" id="total_sum_qty" class="form-control text-center fw-bold" readonly placeholder="0" value="0">
                            </th>
                            <th>
                                <input type="text" id="total_sum_purc_rate" class="form-control text-end fw-bold font-monospace" readonly placeholder="0.00" value="0.00">
                            </th>
                            <th>
                                <input type="text" id="total_sum_amount" class="form-control text-end fw-bold font-monospace" readonly placeholder="0.00" value="0.00">
                            </th>
                            <th></th>
                            <th>
                                <input type="text" id="total_sum_disc_amt" class="form-control text-end fw-bold font-monospace" readonly placeholder="0.00" value="0.00">
                            </th>
                            <th>
                                <input type="text" id="total_sum_net_rate" class="form-control text-end fw-bold font-monospace" readonly placeholder="0.00" value="0.00">
                            </th>
                            <th>
                                <input type="text" id="total_sum_mrp" class="form-control text-end fw-bold font-monospace" readonly placeholder="0.00" value="0.00">
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <td colspan="17" id="rowsErrorTd" class="p-1">
                                <span id="rowsErrorContainer" class="text-danger mb-0" style="font-size: 12px;"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="accountDetailsFinal dataLoadToShow d-none">
        <div class="row g-2">
            {{-- 1. Account Details (Left Panel) --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-2xs mb-2 h-100" style="border-radius: 10px;">
                    <div class="card-header bg-light border-bottom py-2 px-3 fw-bold d-flex align-items-center gap-2" style="font-size: 13px;">
                        <i class="fa-solid fa-file-invoice text-primary"></i>
                        <span>{{ __('Account Details') }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            {{-- Cash/Credit & Credit Days --}}
                            <div class="col-6 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Cash / Credit') }}</label>
                                <select name="cash_or_credit" id="cash_or_credit" class="form-select form-select-sm">
                                    <option value="Credit" selected>{{ __('Credit') }}</option>
                                    <option value="Cash">{{ __('Cash') }}</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Credit Days') }}</label>
                                <input type="text" id="inward_acc_credit_day" name="inward_credit_day"
                                       placeholder="Credit Days"
                                       class="form-control form-control-sm @error('inward_credit_day') is-invalid @enderror" value="{{ old('inward_credit_day','0') }}"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                />
                            </div>

                            {{-- Purchaser --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Purchaser') }}</label>
                                <select name="inward_acc_purchaser" id="inward_acc_purchaser" class="form-select form-select-sm">
                                    <option value="">{{ __('Select Purchaser') }}</option>
                                </select>
                            </div>

                            {{-- Agent Account --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Agent Account') }}</label>
                                <select name="inward_acc_agent" id="inward_acc_agent" class="form-select form-select-sm">
                                    <option value="">{{ __('Select Agent') }}</option>
                                </select>
                            </div>

                            {{-- Transport & Delivery By --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Transport') }}</label>
                                <select name="inward_acc_transport" id="inward_acc_transport" class="form-select form-select-sm">
                                    <option value="">{{ __('Select Transport') }}</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Delivery By') }}</label>
                                <select name="inward_acc_delivery_by" id="inward_acc_delivery_by" class="form-select form-select-sm">
                                    <option value="">{{ __('Select Delivery By') }}</option>
                                </select>
                            </div>

                            {{-- LR No & LR Date --}}
                            <div class="col-6 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('LR No') }}</label>
                                <input type="text" name="inward_acc_lr_no" id="inward_acc_lr_no" class="form-control form-control-sm" placeholder="LR No" value="{{ old('inward_acc_lr_no') }}" />
                            </div>

                            <div class="col-6 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('LR Date') }}</label>
                                <input type="date" name="inward_acc_lr_date" id="inward_acc_lr_date" class="form-control form-control-sm" value="{{ old('inward_acc_lr_date') }}"/>
                            </div>

                            {{-- Bank Cash Disc (%) & Season --}}
                            <div class="col-6 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Bank Cash Disc (%)') }}</label>
                                <input type="text" name="bank_cash_discount_percent" id="bank_cash_discount_percent"
                                       class="form-control form-control-sm decimal-input text-end"
                                       placeholder="0.0000"
                                       value="{{ old('bank_cash_discount_percent', '0.0000') }}"
                                       oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" />
                            </div>

                            <div class="col-6 col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Season') }}</label>
                                <select name="inward_acc_season" id="inward_acc_season" class="form-select form-select-sm">
                                    <option value="">{{ __('Select Season') }}</option>
                                </select>
                            </div>

                            {{-- Remarks --}}
                            <div class="col-12">
                                <label for="inward_acc_remark" class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Remarks') }}</label>
                                <textarea name="inward_acc_remark" id="inward_acc_remark" class="form-control form-control-sm @error('inward_acc_remark') is-invalid @enderror" rows="2" placeholder="Enter Remarks" style="resize: vertical; height: 38px;">{{ old('inward_acc_remark') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Payment Details (Right Panel Tabs) --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-2xs mb-2 h-100" style="border-radius: 10px;">
                    <div class="card-header bg-light border-bottom py-2 px-3 fw-bold d-flex align-items-center justify-content-between" style="font-size: 13px;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-receipt text-primary"></i>
                            <span>{{ __('Payment Details') }}</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            {{-- Sub-Column A: Deductions & Discounts --}}
                            <div class="col-12 col-md-6 pe-md-2" style="border-right: 1px solid #e2e8f0;">
                                {{-- Disc % & Disc Amt --}}
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="form-label mb-0" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Disc %') }}</label>
                                        <label class="form-label mb-0" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('(-) Disc Amt') }}</label>
                                    </div>
                                    <div class="input-group input-group-sm mt-1">
                                        <input type="text" id="bill_discount_percent" name="bill_discount_percent" class="form-control decimal-input text-end" placeholder="0.00" value="{{ old('bill_discount_percent', '0.00') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" style="max-width: 45%;">
                                        <span class="input-group-text px-1 text-muted" style="font-size: 11px;">%</span>
                                        <input type="text" id="bill_discount_amount" name="bill_discount_amount" class="form-control decimal-input text-end font-monospace" placeholder="0.00" value="{{ old('bill_discount_amount', '0.00') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                    </div>
                                </div>

                                {{-- Cash Disc % & Cash Disc Amt --}}
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="form-label mb-0" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Cash Disc %') }}</label>
                                        <label class="form-label mb-0" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('(-) Cash Disc Amt') }}</label>
                                    </div>
                                    <div class="input-group input-group-sm mt-1">
                                        <input type="text" id="cash_discount_percent" name="cash_discount_percent" class="form-control decimal-input text-end" placeholder="0.00" value="{{ old('cash_discount_percent', '0.00') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" style="max-width: 45%;">
                                        <span class="input-group-text px-1 text-muted" style="font-size: 11px;">%</span>
                                        <input type="text" id="cash_discount_amount" name="cash_discount_amount" class="form-control decimal-input text-end font-monospace" placeholder="0.00" value="{{ old('cash_discount_amount', '0.00') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                    </div>
                                </div>

                                {{-- Agent Comm % & Comm Amt --}}
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="form-label mb-0" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Agent Comm %') }}</label>
                                        <label class="form-label mb-0" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Comm Amt') }}</label>
                                    </div>
                                    <div class="input-group input-group-sm mt-1">
                                        <input type="text" id="agent_commission_percent" name="agent_commission_percent" class="form-control decimal-input text-end" placeholder="0.00" value="{{ old('agent_commission_percent', '0.00') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" style="max-width: 45%;">
                                        <span class="input-group-text px-1 text-muted" style="font-size: 11px;">%</span>
                                        <input type="text" id="agent_commission_amount" name="agent_commission_amount" class="form-control decimal-input text-end font-monospace" placeholder="0.00" value="{{ old('agent_commission_amount', '0.00') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                    </div>
                                </div>

                                {{-- Round Off with Checkbox --}}
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <div class="form-check d-flex align-items-center gap-1 p-0 m-0">
                                            <input class="form-check-input ms-0 me-1" type="checkbox" id="chk_auto_round_off" checked>
                                            <label class="form-check-label" for="chk_auto_round_off" style="font-size: 11px; font-weight: 600; text-transform: uppercase; cursor: pointer;">
                                                {{ __('Round Off Amt') }}
                                            </label>
                                        </div>
                                    </div>
                                    <input type="text" id="inward_acc_round_off" name="inward_acc_round_off" class="form-control form-control-sm bg-light text-end font-monospace" value="0.00" readonly>
                                </div>

                                {{-- Expense Amount --}}
                                <div class="mb-1">
                                    <label class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Exp Amt') }}</label>
                                    <input type="text" id="expense_amount" name="expense_amount" class="form-control form-control-sm decimal-input text-end font-monospace" placeholder="0.00" value="{{ old('expense_amount', '0.00') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                </div>
                            </div>

                            {{-- Sub-Column B: Summary & Grand Payable --}}
                            <div class="col-12 col-md-6 ps-md-2">
                                {{-- Gross Amount --}}
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-secondary" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">{{ __('Gross Amt') }}</span>
                                    <input type="text" id="inward_acc_gross_amount" name="gross_amount" class="form-control form-control-sm bg-light text-end font-monospace fw-semibold" readonly style="width: 125px;" value="0.00">
                                </div>

                                {{-- Net Amount --}}
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-secondary" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">{{ __('Net Amt') }}</span>
                                    <input type="text" id="inward_acc_net_amount" name="inward_acc_net_amount" class="form-control form-control-sm bg-light text-end font-monospace fw-semibold" readonly style="width: 125px;" value="0.00">
                                </div>

                                {{-- Freight Amt --}}
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-secondary" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">{{ __('Freight Amt') }}</span>
                                    <input type="text" id="inward_acc_freight_amount" name="inward_acc_freight_amount" class="form-control form-control-sm decimal-input text-end font-monospace" placeholder="0.00" style="width: 125px;" value="{{ old('inward_acc_freight_amount', '0.00') }}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                </div>

                                {{-- (+) Total Tax Amt --}}
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-secondary" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">{{ __('(+) Total Tax Amt') }}</span>
                                    <input type="text" id="inward_acc_gst_amount" name="inward_acc_gst_amount" class="form-control form-control-sm bg-light text-end font-monospace fw-semibold" readonly style="width: 125px;" value="0.00">
                                </div>

                                {{-- (+/-) Other Amt --}}
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-secondary" style="font-size: 11.5px; font-weight: 600; text-transform: uppercase;">{{ __('(+/-) Other Amt') }}</span>
                                    <input type="text" id="other_amount" name="other_amount" class="form-control form-control-sm signed-decimal-input text-end font-monospace" placeholder="0.00" style="width: 125px;" value="{{ old('other_amount', '0.00') }}">
                                </div>

                                {{-- Bill Amount Highlight Box (Red bold) --}}
                                <div class="p-2 rounded mt-2" style="background-color: #fef2f2; border: 1.5px solid #fecaca;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fw-bold text-danger" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px;">{{ __('Bill Amount') }}</span>
                                        <input type="text" id="inward_acc_amt_with_gst" name="inward_acc_amt_with_gst" class="form-control border-0 bg-transparent text-end fw-bold font-monospace text-danger p-0" readonly value="0.00" style="font-size: 18px !important; box-shadow: none;">
                                    </div>
                                </div>
                            </div>

                            {{-- Payment / Bill Remark (Spanning Full Width of Payment Details) --}}
                            <div class="col-12 mt-2">
                                <label for="inward_bill_remark" class="form-label mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">{{ __('Remark') }}</label>
                                <textarea name="inward_bill_remark" id="inward_bill_remark" class="form-control form-control-sm" rows="2" placeholder="Enter Payment / Bill Remark" style="resize: vertical; height: 38px;">{{ old('inward_bill_remark') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="purchase-action-footer d-flex gap-3 justify-content-end align-items-center dataLoadToShow d-none">
        <button type="reset" class="btn btn-outline-secondary rounded-pill px-4 py-1.5 fw-semibold" style="font-size: 13px;">
            <i class="fa-solid fa-rotate-left me-1"></i> {{ __('Reset') }}
        </button>
        <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm" id="saveButton" style="font-size: 14px;">
            <i class="fa-solid fa-check-double me-1.5"></i> {{ __('Submit') }}
        </button>
    </div>
</form>

<!-- Inward Item & Design Spotlight Search Modal -->
<div class="modal fade" id="inwardItemSpotlightModal" tabindex="-1" aria-labelledby="inwardItemSpotlightModalLabel" aria-hidden="true" style="backdrop-filter: blur(6px); background: rgba(15, 23, 42, 0.65); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 760px;">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="border-radius: 20px; background: #ffffff;">
            <!-- Search Header -->
            <div class="p-3 border-bottom d-flex align-items-center bg-light-subtle position-relative">
                <i class="fa-solid fa-magnifying-glass text-primary ms-2 me-3" style="font-size: 18px;"></i>
                <input type="text" id="inwardSpotlightSearchInput" class="form-control form-control-lg border-0 bg-transparent shadow-none px-0" 
                    placeholder="Search Item by Name, Code, Design No... (Press Esc to exit)" 
                    style="font-size: 15px; font-weight: 500; color: #0f172a;" autocomplete="off">
                <button type="button" id="btnClearInwardSpotlightSearch" class="btn btn-link text-muted p-0 me-2 d-none" style="text-decoration: none;">
                    <i class="fa-solid fa-circle-xmark" style="font-size: 16px;"></i>
                </button>
                <div id="inwardSpotlightSpinner" class="spinner-border spinner-border-sm text-primary me-2 d-none" role="status"></div>
                <kbd class="bg-white border text-muted shadow-2xs px-2 py-1 rounded text-xs ms-1 me-2" style="font-size: 11px; font-family: inherit;">ESC</kbd>
            </div>
            <!-- Search Results Body -->
            <div class="modal-body p-2" style="max-height: 440px; min-height: 220px; overflow-y: auto;" id="inwardSpotlightResultsContainer">
                <div class="text-center py-5 text-muted" id="inwardSpotlightEmptyState">
                    <i class="fa-solid fa-box-open mb-2" style="font-size: 36px; color: #94a3b8;"></i>
                    <p class="mb-0 fw-medium" style="font-size: 14px;">Type to search Item Master & Design numbers...</p>
                    <small class="text-muted">Showing all matching items with design code, purchase rates, and tax details</small>
                </div>
                <div id="inwardSpotlightResultsList" class="d-flex flex-column gap-1"></div>
            </div>
            <!-- Footer / Shortcuts -->
            <div class="p-2.5 px-3 bg-light border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↑</kbd> <kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↓</kbd> Navigate</span>
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↵ Enter</kbd> Select Item</span>
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">ESC</kbd> Close</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="fa-solid fa-bolt me-1"></i> Item Master Search
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inward Color Spotlight Search Modal -->
<div class="modal fade" id="inwardColorSpotlightModal" tabindex="-1" aria-labelledby="inwardColorSpotlightModalLabel" aria-hidden="true" style="backdrop-filter: blur(6px); background: rgba(15, 23, 42, 0.65); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="border-radius: 20px; background: #ffffff;">
            <!-- Search Header -->
            <div class="p-3 border-bottom d-flex align-items-center bg-light-subtle position-relative">
                <i class="fa-solid fa-palette text-primary ms-2 me-3" style="font-size: 18px;"></i>
                <input type="text" id="inwardColorSpotlightSearchInput" class="form-control form-control-lg border-0 bg-transparent shadow-none px-0" 
                    placeholder="Search Color by Name or Code... (Press Esc to exit)" 
                    style="font-size: 15px; font-weight: 500; color: #0f172a;" autocomplete="off">
                <button type="button" id="btnClearInwardColorSearch" class="btn btn-link text-muted p-0 me-2 d-none" style="text-decoration: none;">
                    <i class="fa-solid fa-circle-xmark" style="font-size: 16px;"></i>
                </button>
                <div id="inwardColorSpinner" class="spinner-border spinner-border-sm text-primary me-2 d-none" role="status"></div>
                <kbd class="bg-white border text-muted shadow-2xs px-2 py-1 rounded text-xs ms-1 me-2" style="font-size: 11px; font-family: inherit;">ESC</kbd>
            </div>
            <!-- Search Results Body -->
            <div class="modal-body p-2" style="max-height: 400px; min-height: 200px; overflow-y: auto;" id="inwardColorResultsContainer">
                <div class="text-center py-5 text-muted" id="inwardColorEmptyState">
                    <i class="fa-solid fa-paint-roller mb-2" style="font-size: 36px; color: #94a3b8;"></i>
                    <p class="mb-0 fw-medium" style="font-size: 14px;">Type to search Color Master...</p>
                </div>
                <div id="inwardColorResultsList" class="d-flex flex-column gap-1"></div>
            </div>
            <!-- Footer / Shortcuts -->
            <div class="p-2.5 px-3 bg-light border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↑</kbd> <kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↓</kbd> Navigate</span>
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↵ Enter</kbd> Select Color</span>
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">ESC</kbd> Close</span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="fa-solid fa-palette me-1"></i> Color Master (Single Select)
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Inward Size Spotlight Search Modal -->
<div class="modal fade" id="inwardSizeSpotlightModal" tabindex="-1" aria-labelledby="inwardSizeSpotlightModalLabel" aria-hidden="true" style="backdrop-filter: blur(6px); background: rgba(15, 23, 42, 0.65); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="border-radius: 20px; background: #ffffff;">
            <!-- Search Header -->
            <div class="p-3 border-bottom d-flex align-items-center bg-light-subtle position-relative">
                <i class="fa-solid fa-ruler-combined text-primary ms-2 me-3" style="font-size: 18px;"></i>
                <input type="text" id="inwardSizeSpotlightSearchInput" class="form-control form-control-lg border-0 bg-transparent shadow-none px-0" 
                    placeholder="Search Size (e.g. S, M, L, 16, 28, 0-6M)... (Press Esc to exit)" 
                    style="font-size: 15px; font-weight: 500; color: #0f172a;" autocomplete="off">
                <button type="button" id="btnClearInwardSizeSearch" class="btn btn-link text-muted p-0 me-2 d-none" style="text-decoration: none;">
                    <i class="fa-solid fa-circle-xmark" style="font-size: 16px;"></i>
                </button>
                <div id="inwardSizeSpinner" class="spinner-border spinner-border-sm text-primary me-2 d-none" role="status"></div>
                <kbd class="bg-white border text-muted shadow-2xs px-2 py-1 rounded text-xs ms-1 me-2" style="font-size: 11px; font-family: inherit;">ESC</kbd>
            </div>
            <!-- Search Results Body -->
            <div class="modal-body p-2" style="max-height: 400px; min-height: 200px; overflow-y: auto;" id="inwardSizeResultsContainer">
                <div class="text-center py-5 text-muted" id="inwardSizeEmptyState">
                    <i class="fa-solid fa-maximize mb-2" style="font-size: 36px; color: #94a3b8;"></i>
                    <p class="mb-0 fw-medium" style="font-size: 14px;">Type to search Size Master...</p>
                </div>
                <div id="inwardSizeResultsList" class="d-flex flex-column gap-1"></div>
            </div>
            <!-- Footer / Shortcuts -->
            <div class="p-2.5 px-3 bg-light border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↑</kbd> <kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↓</kbd> Navigate</span>
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">↵ Enter</kbd> Select Size</span>
                    <span><kbd class="bg-white border px-1.5 py-0.5 rounded text-xs">ESC</kbd> Close</span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="fa-solid fa-ruler me-1"></i> Size Master (Single Select)
                </span>
            </div>
        </div>
    </div>
</div>

<style>
    .spotlight-item, .spotlight-color-item, .spotlight-size-item {
        transition: all 0.15s ease-in-out;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 10px;
    }
    .spotlight-item:hover, .spotlight-item.active,
    .spotlight-color-item:hover, .spotlight-color-item.active,
    .spotlight-size-item:hover, .spotlight-size-item.active {
        background-color: #eff6ff !important;
        border-color: #3b82f6 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .spotlight-item.active .select-spotlight-btn,
    .spotlight-color-item.active .select-spotlight-btn,
    .spotlight-size-item.active .select-spotlight-btn {
        background-color: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
</style>


@push('scripts')
    <script>
        $(document).ready(function (){

            $(document).on('input', '.decimal-input', function () {
                let v = this.value.replace(/[^0-9.]/g, '');
                let parts = v.split('.');
                if (parts.length > 2) {
                    v = parts[0] + '.' + parts.slice(1).join('');
                }
                this.value = v;
            });

            $(document).on('input', '.signed-decimal-input', function () {
                let val = this.value;
                let isNegative = val.startsWith('-');
                let clean = val.replace(/[^0-9.]/g, '');
                let parts = clean.split('.');
                if (parts.length > 2) {
                    clean = parts[0] + '.' + parts.slice(1).join('');
                }
                this.value = (isNegative ? '-' : '') + clean;
            });

            $(document).on('blur', '.signed-decimal-input', function () {
                let val = this.value.trim();
                if (val === '' || val === '-' || isNaN(parseFloat(val))) {
                    this.value = '0.00';
                } else {
                    this.value = parseFloat(val).toFixed(2);
                }
                window.calculateNetPurcRateSum();
            });

            // $(document).on('blur','#inwardProductTable',function (){
            //     calculateNetPurcRateSum();
            // Auto format on blur for decimal and currency inputs
            $(document).on('blur', '.buy_price, .price, .discount_percentage, .discount_amount, #inward_total', function () {
                let val = this.value.trim();
                if (val !== '' && !isNaN(parseFloat(val))) {
                    this.value = parseFloat(val).toFixed(2);
                }
            });

            $(document).on('blur', '#bill_discount_percent, #bill_discount_amount, #cash_discount_percent, #cash_discount_amount, #agent_commission_percent, #agent_commission_amount, #expense_amount, #inward_acc_freight_amount', function () {
                let val = this.value.trim();
                if (val !== '' && !isNaN(parseFloat(val))) {
                    this.value = parseFloat(val).toFixed(2);
                } else if (val === '') {
                    this.value = '0.00';
                }
            });

            // Bidirectional Bill Discount Calculation
            $(document).on('input keyup', '#bill_discount_percent', function () {
                let gross = parseFloat($('#inward_acc_gross_amount').val()) || 0;
                let pct = parseFloat($(this).val()) || 0;
                let amt = (gross * pct) / 100;
                $('#bill_discount_amount').val(amt > 0 ? amt.toFixed(2) : '0.00');
                window.calculateNetPurcRateSum();
            });

            $(document).on('input keyup', '#bill_discount_amount', function () {
                let gross = parseFloat($('#inward_acc_gross_amount').val()) || 0;
                let amt = parseFloat($(this).val()) || 0;
                let pct = gross > 0 ? (amt * 100) / gross : 0;
                $('#bill_discount_percent').val(pct > 0 ? pct.toFixed(2) : '0.00');
                window.calculateNetPurcRateSum();
            });

            // Bidirectional Cash Discount Calculation
            $(document).on('input keyup', '#cash_discount_percent', function () {
                let net = parseFloat($('#inward_acc_net_amount').val()) || 0;
                let pct = parseFloat($(this).val()) || 0;
                let amt = (net * pct) / 100;
                $('#cash_discount_amount').val(amt > 0 ? amt.toFixed(2) : '0.00');
                window.calculateNetPurcRateSum();
            });

            $(document).on('input keyup', '#cash_discount_amount', function () {
                let net = parseFloat($('#inward_acc_net_amount').val()) || 0;
                let amt = parseFloat($(this).val()) || 0;
                let pct = net > 0 ? (amt * 100) / net : 0;
                $('#cash_discount_percent').val(pct > 0 ? pct.toFixed(2) : '0.00');
                window.calculateNetPurcRateSum();
            });

            // Bidirectional Agent Commission Calculation
            $(document).on('input keyup', '#agent_commission_percent', function () {
                let net = parseFloat($('#inward_acc_net_amount').val()) || 0;
                let pct = parseFloat($(this).val()) || 0;
                let amt = (net * pct) / 100;
                $('#agent_commission_amount').val(amt > 0 ? amt.toFixed(2) : '0.00');
            });

            $(document).on('input keyup', '#agent_commission_amount', function () {
                let net = parseFloat($('#inward_acc_net_amount').val()) || 0;
                let amt = parseFloat($(this).val()) || 0;
                let pct = net > 0 ? (amt * 100) / net : 0;
                $('#agent_commission_percent').val(pct > 0 ? pct.toFixed(2) : '0.00');
            });

            // Change listeners for charges & round off
            $(document).on('input keyup change', '#inward_acc_freight_amount, #other_amount, #expense_amount, #chk_auto_round_off', function () {
                window.calculateNetPurcRateSum();
            });

            $(document).on(
                'input keyup change',
                'input[name="qty[]"], input[name="purcRate[]"], input[name="amount[]"], input[name="discAmt[]"], input[name="netPurcRate[]"], input[name="mrp[]"], #inwardProductTable, input[name="sgst[]"]',
                function () {
                    window.calculateNetPurcRateSum();
                }
            );

            window.calculateNetPurcRateSum = function () {

                let grossTotal = 0;
                let gstTotal = 0;

                let totQty = 0;
                let totPurcRate = 0;
                let totAmount = 0;
                let totDiscAmt = 0;
                let totNetRate = 0;
                let totMrp = 0;

                $('#inwardProductTable tbody tr').each(function () {

                    let row = $(this);

                    let qty = parseFloat(row.find('input[name="qty[]"]').val()) || 0;
                    let purcRate = parseFloat(row.find('input[name="purcRate[]"]').val()) || 0;
                    let amount = parseFloat(row.find('input[name="amount[]"]').val()) || (qty * purcRate);
                    let discAmt = parseFloat(row.find('input[name="discAmt[]"]').val()) || 0;
                    let netAmount = parseFloat(row.find('input[name="netPurcRate[]"]').val()) || 0;
                    let mrp = parseFloat(row.find('input[name="mrp[]"]').val()) || 0;
                    let gstPercent = parseFloat(row.find('input[name="sgst[]"]').val()) || 0;

                    // GST amount for this row
                    let gstAmount = (netAmount * gstPercent) / 100;

                    grossTotal += netAmount; // Net line items total
                    gstTotal += gstAmount;

                    totQty += qty;
                    totPurcRate += purcRate;
                    totAmount += amount;
                    totDiscAmt += discAmt;
                    totNetRate += netAmount;
                    totMrp += mrp;
                });

                // Update Table Footer Totals
                $("#total_sum_qty").val(totQty > 0 ? (totQty % 1 === 0 ? totQty : totQty.toFixed(2)) : '0');
                $("#total_sum_purc_rate").val(totPurcRate.toFixed(2));
                $("#total_sum_amount").val(totAmount.toFixed(2));
                $("#total_sum_disc_amt").val(totDiscAmt.toFixed(2));
                $("#total_sum_net_rate").val(totNetRate.toFixed(2));
                $("#total_sum_mrp").val(totMrp.toFixed(2));

                // Bill level calculations
                let billDiscAmt = parseFloat($('#bill_discount_amount').val()) || 0;
                let netTaxable = Math.max(0, grossTotal - billDiscAmt);

                let cashDiscAmt = parseFloat($('#cash_discount_amount').val()) || 0;
                let freight = parseFloat($('#inward_acc_freight_amount').val()) || 0;
                let otherVal = $('#other_amount').val();
                let otherAmt = (otherVal === '-' || isNaN(parseFloat(otherVal))) ? 0 : parseFloat(otherVal);

                let subTotal = netTaxable - cashDiscAmt + freight + gstTotal + otherAmt;

                let isAutoRoundOff = $('#chk_auto_round_off').is(':checked');
                let roundedGrand = isAutoRoundOff ? Math.round(subTotal) : subTotal;
                let roundOff = isAutoRoundOff ? (roundedGrand - subTotal) : 0;

                // Set values in inputs
                $("#inward_acc_gross_amount").val(grossTotal.toFixed(2));
                $("#inward_acc_net_amount").val(netTaxable.toFixed(2));
                $("#inward_acc_gst_amount").val(gstTotal.toFixed(2));
                $("#inward_acc_round_off").val(roundOff.toFixed(2));
                $("#inward_acc_amt_with_gst").val(roundedGrand.toFixed(2));

                return {
                    grossTotal,
                    netTaxable,
                    gstTotal,
                    grandTotal: roundedGrand,
                    roundOff,
                    totQty,
                    totPurcRate,
                    totAmount,
                    totDiscAmt,
                    totNetRate,
                    totMrp
                };
            };



            // Aside Bar Close
            $('#appContent').addClass('closed-sidebar');
            $('#appContent .app-header .hamburger').addClass('is-active');

            //



            // Default row on page load
            // createRow();

            //  Add Table Row
            $(document).on('click','#create-modal-btn',function() {
                $('#rowsErrorTd').removeClass('bg-warning-light');
                $('#rowsErrorContainer').text('');
                createRow();
            });

            // Update Row SL
            function updateSL() {
                $('#inwardProductTable tbody tr').each(function(index){
                    $(this).find('td:first').text(index + 1);
                });
            }

            // Remove Row
            $(document).on('click','.deleteRow',function() {
                // $(this).closest('tr').remove();

                let row = $(this).closest('tr');
                let inwardProductId = row.find('input[name="inwardProductId[]"]').val();
                row.remove();

                if (inwardProductId !== ''){
                    // function deleteToDataFromInwardTable(){
                    //
                    // }

                    $.ajax({
                        url: '/shop/inward-product/' + inwardProductId + '/destroy-inward-product',
                        type: 'GET', // keep GET if your route is GET
                        success: function(response) {
                            toastr.success("Inward Product deleted successfully");
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.error || "Failed to delete item");
                        }
                    });
                }


                let rowCount = $('#inwardProductTable tbody tr').length;
                if (rowCount === 0) {

                    $('#rowsErrorContainer').text('The rows field is required.');
                    $('#rowsErrorTd').addClass('bg-warning-light');

                } else {
                    // Error remove kar do agar rows present hain
                    $('#rowsErrorContainer').text('');
                    $('#rowsErrorTd').removeClass('bg-warning-light');

                }
                updateSL();
                calculateNetPurcRateSum();
            });

            // Item Name Find



            // Check Old Price New Code

            function checkOldPrice(designid, parentInput) {
                if (!designid) {
                    $('#oldPriceTable').addClass('d-none');
                    return;
                }

                if (designid != '') {

                    $.ajax({
                        url: '/shop/inward-product/' + designid + '/get-old-price',
                        type: 'GET',
                        success: function (response) {

                            if (response.length > 0) {

                                let dateHeader = '<th></th>';
                                let buyPriceRow = '<th class="text-start">Buy Price</th>';
                                let mrpRow = '<th class="text-start">MRP</th>';

                                response.forEach(function (item) {

                                    dateHeader += `<th>${item.created_at}</th>`;
                                    buyPriceRow += `<td class="bg-warning-light">${item.buy_price}</td>`;
                                    mrpRow += `<td class="bg-success-subtle">${item.mrp}</td>`;

                                });

                                $('#dateHeaderRow').html(dateHeader);
                                $('#buyPriceRow').html(buyPriceRow);
                                $('#mrpRow').html(mrpRow);

                                $('#oldPriceTable').removeClass('d-none');

                            } else {
                                $('#oldPriceTable').addClass('d-none');
                            }
                        },
                        error: function () {
                            $('#oldPriceTable').addClass('d-none');
                        }
                    });

                }
            }



            $(document).ready(function (){
                $(document).on('click','.old-price-btn',function (){
                    const designId = $(this).attr('data-designid');

                    if (designId != ''){
                        $('#old-price-check-modal').modal('show');
                        $.ajax({
                            url: '/shop/inward-product/' + inwardProductId + '/destroy-inward-product',
                            type: 'GET', // keep GET if your route is GET
                            success: function(response) {
                                toastr.success("Inward Product deleted successfully");
                            },
                            error: function(xhr) {
                                toastr.error(xhr.responseJSON?.error || "Failed to delete item");
                            }
                        });
                    }
                });
            })

                 // Track last focused row for spotlight modal targeting
            $(document).on('focusin', '#inwardProductTable tbody tr input, #inwardProductTable tbody tr select', function() {
                window.lastFocusedInwardRow = $(this).closest('tr');
            });

            // ==========================================
            // Item Master & Design Spotlight Search Logic
            // ==========================================
            let spotlightSearchTimer = null;

            function openItemSpotlightModal(initialQuery, targetRow) {
                window.activeInwardRow = targetRow || window.lastFocusedInwardRow || $('#inwardProductTable tbody tr:last');
                const modalEl = $('#inwardItemSpotlightModal');
                const searchInput = $('#inwardSpotlightSearchInput');
                
                searchInput.val(initialQuery || '');
                if (initialQuery && initialQuery.length > 0) {
                    $('#btnClearInwardSpotlightSearch').removeClass('d-none');
                } else {
                    $('#btnClearInwardSpotlightSearch').addClass('d-none');
                }

                modalEl.modal('show');
            }

            $('#inwardItemSpotlightModal').on('shown.bs.modal', function () {
                const searchInput = $('#inwardSpotlightSearchInput');
                searchInput.focus();
                const val = searchInput.val();
                searchInput.val('').val(val); // put cursor at end
                performSpotlightSearch(val);
            });

            $('#inwardItemSpotlightModal').on('hidden.bs.modal', function () {
                if (window.activeInwardRow && window.activeInwardRow.length > 0) {
                    const itemInput = window.activeInwardRow.find('.item');
                    if (!itemInput.val()) {
                        itemInput.focus();
                    }
                }
            });

            // Trigger modal on click or keypress in .item or .designno
            $(document).on('click', '.item, .designno', function (e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                openItemSpotlightModal($(this).val().trim(), row);
            });

            $(document).on('keydown', '.item, .designno', function (e) {
                if (e.key === 'Tab' || e.key === 'Shift' || e.key === 'Alt' || e.key === 'Control' || e.key === 'Meta') {
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const row = $(this).closest('tr');
                    openItemSpotlightModal($(this).val().trim(), row);
                    return;
                }
                if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    e.preventDefault();
                    const row = $(this).closest('tr');
                    openItemSpotlightModal(e.key, row);
                }
            });

            $('#inwardSpotlightSearchInput').on('input keyup', function (e) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === 'Escape') {
                    return;
                }
                const query = $(this).val().trim();
                if (query.length > 0) {
                    $('#btnClearInwardSpotlightSearch').removeClass('d-none');
                } else {
                    $('#btnClearInwardSpotlightSearch').addClass('d-none');
                }

                clearTimeout(spotlightSearchTimer);
                spotlightSearchTimer = setTimeout(() => {
                    performSpotlightSearch(query);
                }, 150);
            });

            $('#btnClearInwardSpotlightSearch').on('click', function () {
                $('#inwardSpotlightSearchInput').val('').focus();
                $(this).addClass('d-none');
                performSpotlightSearch('');
            });

            function performSpotlightSearch(query) {
                $('#inwardSpotlightSpinner').removeClass('d-none');

                $.ajax({
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    type: "GET",
                    data: { itemSearch: query },
                    success: function (response) {
                        $('#inwardSpotlightSpinner').addClass('d-none');
                        const resultsList = $('#inwardSpotlightResultsList');
                        const emptyState = $('#inwardSpotlightEmptyState');
                        resultsList.empty();

                        const items = response.designWithItemData || [];

                        if (items.length === 0) {
                            emptyState.html(`
                                <i class="fa-solid fa-face-frown mb-2" style="font-size: 36px; color: #94a3b8;"></i>
                                <p class="mb-0 fw-medium" style="font-size: 14px;">No matching items found for "${query}"</p>
                                <small class="text-muted">Press Alt+I to add a new Item or Alt+D for new Design</small>
                            `).removeClass('d-none');
                            return;
                        }

                        emptyState.addClass('d-none');

                        items.forEach((item, idx) => {
                            let hsnRules = item.products?.hsn_master?.sub_hsn || [];
                            let hsnRulesJson = encodeURIComponent(JSON.stringify(hsnRules));
                            let taxCode = item.products?.hsn_master?.hsn_code || '';
                            let taxCodeId = item.products?.hsn_master?.id || '';
                            let gstPercent = item.products?.vat_tax?.percentage || '';
                            let gstTaxId = item.products?.vat_tax?.id || item.products?.hsn_master?.vat_tax_id || '';

                            let designBadge = item.design_number
                                ? `<span class="badge" style="font-size: 11px; background-color: #f3e8ff !important; color: #7e22ce !important; border: 1px solid #e9d5ff;"><i class="fa-solid fa-hashtag me-0.5"></i> Design: ${item.design_number}</span>`
                                : `<span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">Master Item (No Design)</span>`;

                            let priceBadges = '';
                            if (item.buy_price) {
                                priceBadges += `<span class="badge bg-light text-dark border">Purc Rate: <strong>₹${item.buy_price}</strong></span>`;
                            }
                            if (item.price || item.mrp) {
                                priceBadges += `<span class="badge bg-light text-dark border">Amount/MRP: <strong>₹${item.price || item.mrp}</strong></span>`;
                            }
                            if (item.discount_percentage) {
                                priceBadges += `<span class="badge bg-light text-dark border">Disc: <strong>${item.discount_percentage}%</strong></span>`;
                            }
                            if (taxCode) {
                                priceBadges += `<span class="badge bg-light text-muted border">HSN: ${taxCode}</span>`;
                            }
                            if (gstPercent) {
                                priceBadges += `<span class="badge bg-success-subtle text-success">GST: ${gstPercent}%</span>`;
                            }

                            let itemHtml = `
                            <div class="spotlight-item p-2.5 px-3 d-flex align-items-center justify-content-between cursor-pointer mb-1.5 ${idx === 0 ? 'active' : ''}"
                                data-name="${item.products?.name || ''}"
                                data-itemid="${item.products?.id || ''}"
                                data-designno="${item.design_number || ''}"
                                data-designid="${item.id || ''}"
                                data-qty="${item.quantity || 1}"
                                data-purcrate="${item.buy_price || ''}"
                                data-mrp="${item.mrp || ''}"
                                data-markup="${item.mark_up || ''}"
                                data-markdown="${item.mark_down || ''}"
                                data-disc="${item.discount_percentage || 0}"
                                data-amount="${item.price || item.mrp || ''}"
                                data-taxcode="${taxCode}"
                                data-taxcodeid="${taxCodeId}"
                                data-sgstid="${gstTaxId}"
                                data-sgst="${gstPercent}"
                                data-hsn-rules="${hsnRulesJson}">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle text-primary flex-shrink-0" style="width: 36px; height: 36px; font-size: 15px;">
                                        <i class="fa-solid fa-shirt"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-bold text-dark" style="font-size: 14px;">${item.products?.name || ''}</span>
                                            ${designBadge}
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap" style="font-size: 11.5px;">
                                            ${priceBadges}
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 select-spotlight-btn" style="font-size: 11.5px;">
                                    Select <i class="fa-solid fa-arrow-right ms-1"></i>
                                </button>
                            </div>`;
                            resultsList.append(itemHtml);
                        });

                        resultsList.find('.spotlight-item.active')[0]?.scrollIntoView({ block: "nearest" });
                    },
                    error: function () {
                        $('#inwardSpotlightSpinner').addClass('d-none');
                    }
                });
            }

            // Spotlight Modal Keyboard Navigation
            $('#inwardSpotlightSearchInput').on('keydown', function (e) {
                const items = $('#inwardSpotlightResultsList .spotlight-item');
                let activeIndex = items.index($('#inwardSpotlightResultsList .spotlight-item.active'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (items.length > 0) {
                        items.removeClass('active');
                        activeIndex = (activeIndex + 1) % items.length;
                        const activeEl = items.eq(activeIndex).addClass('active');
                        activeEl[0]?.scrollIntoView({ block: "nearest" });
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (items.length > 0) {
                        items.removeClass('active');
                        activeIndex = (activeIndex - 1 + items.length) % items.length;
                        const activeEl = items.eq(activeIndex).addClass('active');
                        activeEl[0]?.scrollIntoView({ block: "nearest" });
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && items.length > 0) {
                        items.eq(activeIndex).trigger('click');
                    }
                }
            });

            // Select item from spotlight
            $(document).on('click', '.spotlight-item', function () {
                const itemEl = $(this);
                const name = itemEl.data('name');
                const itemid = itemEl.data('itemid');
                const designNo = itemEl.data('designno');
                const designid = itemEl.data('designid');
                const qty = itemEl.data('qty') || 1;
                const purcrate = itemEl.data('purcrate');
                const mrp = itemEl.data('mrp');
                const markup = itemEl.data('markup');
                const markdown = itemEl.data('markdown');
                const disc = itemEl.data('disc') || 0;
                const amount = itemEl.data('amount');
                const taxcode = itemEl.data('taxcode');
                const taxcodeid = itemEl.data('taxcodeid');
                const sgst = itemEl.data('sgst');
                const sgstid = itemEl.data('sgstid');
                let hsnRules = itemEl.data('hsn-rules');
                if (typeof hsnRules === 'string') {
                    try { hsnRules = JSON.parse(decodeURIComponent(hsnRules)); } catch (err) { hsnRules = []; }
                }

                let row = window.activeInwardRow;
                if (!row || row.length === 0) {
                    row = $('#inwardProductTable tbody tr:last');
                }

                const itemInput = row.find('.item');
                itemInput.val(name);
                itemInput.data('selected-id', itemid);
                itemInput.data('selected-name', name);

                const designInput = row.find('.designno');
                designInput.val(designNo);
                designInput.data('selected-designno', designNo);
                designInput.data('selected-designid', designid);

                row.find('input[name="itemid[]"]').val(itemid);
                row.find('input[name="designid[]"]').val(designid);
                row.find('input[name="qty[]"]').val(qty);
                row.find('input[name="purcRate[]"]').val(purcrate !== '' && !isNaN(parseFloat(purcrate)) ? parseFloat(purcrate).toFixed(2) : '');
                row.find('input[name="amount[]"]').val(amount !== '' && !isNaN(parseFloat(amount)) ? parseFloat(amount).toFixed(2) : '');
                row.find('input[name="disc[]"]').val(disc !== '' && !isNaN(parseFloat(disc)) ? parseFloat(disc).toFixed(2) : '0.00');

                let taxInput = row.find('input[name="taxCode[]"]');
                taxInput.val(taxcode);
                taxInput.data('hsn-rules', hsnRules);
                taxInput.data('default-tax-id', sgstid);
                taxInput.data('default-tax-percent', sgst);
                row.find('input[name="taxCodeId[]"]').val(taxcodeid);
                row.find('input[name="sgst[]"]').val(sgst);
                row.find('input[name="sgstId[]"]').val(sgstid);

                calculateRowAll(row);

                if (designid) {
                    checkOldPrice(designid, itemInput);
                }

                $('#inwardItemSpotlightModal').modal('hide');

                // Auto-advance to Color Spotlight Search Modal
                setTimeout(() => {
                    const colorInput = row.find('.inward-color-input');
                    if (colorInput.length > 0) {
                        openColorSpotlightModal('', row);
                    } else {
                        const purcRateInput = row.find('input[name="purcRate[]"]');
                        if (purcRateInput.length > 0) {
                            purcRateInput.focus().select();
                        }
                    }
                }, 200);
            });

            // ==========================================
            // Color Master Spotlight Search Modal Logic
            // ==========================================
            let colorSpotlightTimer = null;
            let currentColorSearchXHR = null;

            function openColorSpotlightModal(initialQuery, targetRow) {
                const target = targetRow || window.activeInwardRow || $('#inwardProductTable tbody tr:last');
                window.activeInwardRow = target;
                const modalEl = $('#inwardColorSpotlightModal');
                modalEl.data('target-row', target);
                const searchInput = $('#inwardColorSpotlightSearchInput');
                
                searchInput.val(initialQuery || '');
                if (initialQuery && initialQuery.length > 0) {
                    $('#btnClearInwardColorSearch').removeClass('d-none');
                } else {
                    $('#btnClearInwardColorSearch').addClass('d-none');
                }

                modalEl.modal('show');
            }

            $('#inwardColorSpotlightModal').on('shown.bs.modal', function () {
                const searchInput = $('#inwardColorSpotlightSearchInput');
                searchInput.focus();
                const val = searchInput.val();
                searchInput.val('').val(val);
                performColorSpotlightSearch(val);
            });

            $(document).on('click', '.inward-color-input', function (e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                openColorSpotlightModal($(this).val().trim(), row);
            });

            $(document).on('keydown', '.inward-color-input', function (e) {
                if (e.key === 'Tab' || e.key === 'Shift' || e.key === 'Alt' || e.key === 'Control' || e.key === 'Meta') {
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const row = $(this).closest('tr');
                    openColorSpotlightModal($(this).val().trim(), row);
                    return;
                }
                if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    e.preventDefault();
                    const row = $(this).closest('tr');
                    openColorSpotlightModal(e.key, row);
                }
            });

            $('#inwardColorSpotlightSearchInput').on('input keyup', function (e) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === 'Escape') {
                    return;
                }
                const query = $(this).val().trim();
                if (query.length > 0) {
                    $('#btnClearInwardColorSearch').removeClass('d-none');
                } else {
                    $('#btnClearInwardColorSearch').addClass('d-none');
                }

                clearTimeout(colorSpotlightTimer);
                colorSpotlightTimer = setTimeout(() => {
                    performColorSpotlightSearch(query);
                }, 150);
            });

            $('#btnClearInwardColorSearch').on('click', function () {
                $('#inwardColorSpotlightSearchInput').val('').focus();
                $(this).addClass('d-none');
                performColorSpotlightSearch('');
            });

            function performColorSpotlightSearch(query, autoSelectOnComplete = false) {
                $('#inwardColorSpinner').removeClass('d-none');
                query = String(query || '').trim();

                if (currentColorSearchXHR) {
                    currentColorSearchXHR.abort();
                }

                currentColorSearchXHR = $.ajax({
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    type: "GET",
                    data: { colorSearch: query },
                    success: function (response) {
                        try {
                            $('#inwardColorSpinner').addClass('d-none');
                            const resultsList = $('#inwardColorResultsList');
                            const emptyState = $('#inwardColorEmptyState');
                            resultsList.empty();

                            let colors = response.designWithColorData || [];

                            if (colors.length === 0) {
                                emptyState.html(`
                                    <i class="fa-solid fa-face-frown mb-2" style="font-size: 36px; color: #94a3b8;"></i>
                                    <p class="mb-0 fw-medium" style="font-size: 14px;">No matching colors found for "${query}"</p>
                                `).removeClass('d-none');
                                return;
                            }

                            emptyState.addClass('d-none');

                            colors.forEach((color, idx) => {
                                const cId = color.id || '';
                                const cName = color.name || '';
                                const cCode = color.color_code || '';

                                let colorBox = cCode 
                                    ? `<div style="width: 22px; height: 22px; border-radius: 6px; background-color: ${cCode}; border: 1px solid rgba(0,0,0,0.15);" class="flex-shrink-0 shadow-2xs"></div>`
                                    : `<div style="width: 22px; height: 22px; border-radius: 6px; background: linear-gradient(135deg, #e2e8f0, #cbd5e1); border: 1px solid rgba(0,0,0,0.1);" class="flex-shrink-0"></div>`;

                                let itemHtml = `
                                <div class="spotlight-color-item p-2 px-3 d-flex align-items-center justify-content-between cursor-pointer mb-1 border rounded-3 ${idx === 0 ? 'active' : ''}"
                                    data-color-id="${cId}"
                                    data-color-name="${cName}"
                                    data-color-code="${cCode}">
                                    <div class="d-flex align-items-center gap-2.5">
                                        ${colorBox}
                                        <span class="fw-semibold text-dark" style="font-size: 13.5px;">${cName}</span>
                                        ${cCode ? `<small class="text-muted font-monospace" style="font-size: 11px;">(${cCode})</small>` : ''}
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 select-spotlight-btn" style="font-size: 11px;">
                                        Select <i class="fa-solid fa-check ms-1"></i>
                                    </button>
                                </div>`;
                                resultsList.append(itemHtml);
                            });

                            if (autoSelectOnComplete && colors.length > 0) {
                                resultsList.find('.spotlight-color-item:first').trigger('click');
                                return;
                            }

                            resultsList.find('.spotlight-color-item.active')[0]?.scrollIntoView({ block: "nearest" });
                        } catch (err) {
                            console.error("Error processing color search results:", err);
                        }
                    },
                    error: function (xhr, status) {
                        if (status !== 'abort') {
                            $('#inwardColorSpinner').addClass('d-none');
                        }
                    }
                });
            }

            // Keyboard Navigation in Color Spotlight
            $('#inwardColorSpotlightSearchInput').on('keydown', function (e) {
                const items = $('#inwardColorResultsList .spotlight-color-item');
                let activeIndex = items.index($('#inwardColorResultsList .spotlight-color-item.active'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (items.length > 0) {
                        items.removeClass('active');
                        activeIndex = (activeIndex + 1) % items.length;
                        const activeEl = items.eq(activeIndex).addClass('active');
                        activeEl[0]?.scrollIntoView({ block: "nearest" });
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (items.length > 0) {
                        items.removeClass('active');
                        activeIndex = (activeIndex - 1 + items.length) % items.length;
                        const activeEl = items.eq(activeIndex).addClass('active');
                        activeEl[0]?.scrollIntoView({ block: "nearest" });
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = $('#inwardColorSpotlightSearchInput').val().trim();
                    if (colorSpotlightTimer || $('#inwardColorSpinner').is(':visible')) {
                        clearTimeout(colorSpotlightTimer);
                        colorSpotlightTimer = null;
                        performColorSpotlightSearch(query, true);
                        return;
                    }
                    if (activeIndex >= 0 && items.length > 0) {
                        items.eq(activeIndex).trigger('click');
                    }
                }
            });

            $(document).on('click', '.spotlight-color-item', function () {
                const colorId = $(this).attr('data-color-id') || $(this).data('color-id');
                const colorName = String($(this).attr('data-color-name') || $(this).data('color-name') || '');

                let row = $('#inwardColorSpotlightModal').data('target-row') || window.activeInwardRow || $('#inwardProductTable tbody tr:last');

                row.find('.inward-color-input').val(colorName);
                row.find('.color-id').val(colorId);
                row.find('input[name="colorInwardIds[]"]').val(colorId);

                $('#inwardColorSpotlightModal').modal('hide');

                // Auto-advance to Size Spotlight Search Modal
                setTimeout(() => {
                    const sizeInput = row.find('.inward-size-input');
                    if (sizeInput.length > 0) {
                        openSizeSpotlightModal('', row);
                    } else {
                        const purcRateInput = row.find('input[name="purcRate[]"]');
                        if (purcRateInput.length > 0) {
                            purcRateInput.focus().select();
                        }
                    }
                }, 200);
            });

            // ==========================================
            // Size Master Spotlight Search Modal Logic
            // ==========================================
            let sizeSpotlightTimer = null;
            let currentSizeSearchXHR = null;

            function openSizeSpotlightModal(initialQuery, targetRow) {
                const target = targetRow || window.activeInwardRow || $('#inwardProductTable tbody tr:last');
                window.activeInwardRow = target;
                const modalEl = $('#inwardSizeSpotlightModal');
                modalEl.data('target-row', target);
                const searchInput = $('#inwardSizeSpotlightSearchInput');
                
                searchInput.val(initialQuery || '');
                if (initialQuery && initialQuery.length > 0) {
                    $('#btnClearInwardSizeSearch').removeClass('d-none');
                } else {
                    $('#btnClearInwardSizeSearch').addClass('d-none');
                }

                modalEl.modal('show');
            }

            $('#inwardSizeSpotlightModal').on('shown.bs.modal', function () {
                const searchInput = $('#inwardSizeSpotlightSearchInput');
                searchInput.focus();
                const val = searchInput.val();
                searchInput.val('').val(val);
                performSizeSpotlightSearch(val);
            });

            $(document).on('click', '.inward-size-input', function (e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                openSizeSpotlightModal($(this).val().trim(), row);
            });

            $(document).on('keydown', '.inward-size-input', function (e) {
                if (e.key === 'Tab' || e.key === 'Shift' || e.key === 'Alt' || e.key === 'Control' || e.key === 'Meta') {
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const row = $(this).closest('tr');
                    openSizeSpotlightModal($(this).val().trim(), row);
                    return;
                }
                if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    e.preventDefault();
                    const row = $(this).closest('tr');
                    openSizeSpotlightModal(e.key, row);
                }
            });

            $('#inwardSizeSpotlightSearchInput').on('input keyup', function (e) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === 'Escape') {
                    return;
                }
                const query = $(this).val().trim();
                if (query.length > 0) {
                    $('#btnClearInwardSizeSearch').removeClass('d-none');
                } else {
                    $('#btnClearInwardSizeSearch').addClass('d-none');
                }

                clearTimeout(sizeSpotlightTimer);
                sizeSpotlightTimer = setTimeout(() => {
                    performSizeSpotlightSearch(query);
                }, 150);
            });

            $('#btnClearInwardSizeSearch').on('click', function () {
                $('#inwardSizeSpotlightSearchInput').val('').focus();
                $(this).addClass('d-none');
                performSizeSpotlightSearch('');
            });

            function performSizeSpotlightSearch(query, autoSelectOnComplete = false) {
                $('#inwardSizeSpinner').removeClass('d-none');
                query = String(query || '').trim();

                if (currentSizeSearchXHR) {
                    currentSizeSearchXHR.abort();
                }

                currentSizeSearchXHR = $.ajax({
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    type: "GET",
                    data: { sizeSearch: query },
                    success: function (response) {
                        try {
                            $('#inwardSizeSpinner').addClass('d-none');
                            const resultsList = $('#inwardSizeResultsList');
                            const emptyState = $('#inwardSizeEmptyState');
                            resultsList.empty();

                            let sizes = response.designWithSizeData || [];

                            if (sizes.length === 0) {
                                emptyState.html(`
                                    <i class="fa-solid fa-face-frown mb-2" style="font-size: 36px; color: #94a3b8;"></i>
                                    <p class="mb-0 fw-medium" style="font-size: 14px;">No matching sizes found for "${query}"</p>
                                `).removeClass('d-none');
                                return;
                            }

                            emptyState.addClass('d-none');

                            sizes.forEach((size, idx) => {
                                const sId = size.id || '';
                                const sName = size.name || '';

                                let itemHtml = `
                                <div class="spotlight-size-item p-2 px-3 d-flex align-items-center justify-content-between cursor-pointer mb-1 border rounded-3 ${idx === 0 ? 'active' : ''}"
                                    data-size-id="${sId}"
                                    data-size-name="${sName}">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light border text-secondary flex-shrink-0" style="width: 26px; height: 26px; font-size: 11px; font-weight: bold;">
                                            ${sName.slice(0, 3)}
                                        </div>
                                        <span class="fw-semibold text-dark" style="font-size: 14px;">${sName}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 select-spotlight-btn" style="font-size: 11px;">
                                        Select <i class="fa-solid fa-check ms-1"></i>
                                    </button>
                                </div>`;
                                resultsList.append(itemHtml);
                            });

                            if (autoSelectOnComplete && sizes.length > 0) {
                                resultsList.find('.spotlight-size-item:first').trigger('click');
                                return;
                            }

                            resultsList.find('.spotlight-size-item.active')[0]?.scrollIntoView({ block: "nearest" });
                        } catch (err) {
                            console.error("Error processing size search results:", err);
                        }
                    },
                    error: function (xhr, status) {
                        if (status !== 'abort') {
                            $('#inwardSizeSpinner').addClass('d-none');
                        }
                    }
                });
            }

            // Keyboard Navigation in Size Spotlight
            $('#inwardSizeSpotlightSearchInput').on('keydown', function (e) {
                const items = $('#inwardSizeResultsList .spotlight-size-item');
                let activeIndex = items.index($('#inwardSizeResultsList .spotlight-size-item.active'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (items.length > 0) {
                        items.removeClass('active');
                        activeIndex = (activeIndex + 1) % items.length;
                        const activeEl = items.eq(activeIndex).addClass('active');
                        activeEl[0]?.scrollIntoView({ block: "nearest" });
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (items.length > 0) {
                        items.removeClass('active');
                        activeIndex = (activeIndex - 1 + items.length) % items.length;
                        const activeEl = items.eq(activeIndex).addClass('active');
                        activeEl[0]?.scrollIntoView({ block: "nearest" });
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = $('#inwardSizeSpotlightSearchInput').val().trim();
                    if (sizeSpotlightTimer || $('#inwardSizeSpinner').is(':visible')) {
                        clearTimeout(sizeSpotlightTimer);
                        sizeSpotlightTimer = null;
                        performSizeSpotlightSearch(query, true);
                        return;
                    }
                    if (activeIndex >= 0 && items.length > 0) {
                        items.eq(activeIndex).trigger('click');
                    }
                }
            });

            $(document).on('click', '.spotlight-size-item', function () {
                const sizeId = $(this).attr('data-size-id') || $(this).data('size-id');
                const sizeName = String($(this).attr('data-size-name') || $(this).data('size-name') || '');

                let row = $('#inwardSizeSpotlightModal').data('target-row') || window.activeInwardRow || $('#inwardProductTable tbody tr:last');

                row.find('.inward-size-input').val(sizeName);
                row.find('.size-id').val(sizeId);
                row.find('input[name="sizeInwardIds[]"]').val(sizeId);

                $('#inwardSizeSpotlightModal').modal('hide');

                // Move focus to Purc Rate in that row
                setTimeout(() => {
                    const purcRateInput = row.find('input[name="purcRate[]"]');
                    if (purcRateInput.length > 0) {
                        purcRateInput.focus().select();
                    }
                }, 200);
            });

            // ==========================================
            // Tax Code Autocomplete & Calculation Logic
            // ==========================================
            $(document).on('keyup', '.taxcode', function(e) {
                const input = $(this);
                const taxCode = input.val().trim();
                const dropdown = input.siblings('.dropdown-taxCode');

                const items = dropdown.find('li');
                let index = items.index(dropdown.find('li.active'));

                if(e.key === 'ArrowDown') {
                    e.preventDefault();
                    items.removeClass('active');
                    index = (index + 1) % items.length;
                    items.eq(index).addClass('active')[0]?.scrollIntoView({ block: "nearest" });
                    return;
                } else if(e.key === 'ArrowUp') {
                    e.preventDefault();
                    items.removeClass('active');
                    index = (index - 1 + items.length) % items.length;
                    items.eq(index).addClass('active')[0]?.scrollIntoView({ block: "nearest" });
                    return;
                } else if(e.key === 'Enter') {
                    e.preventDefault();
                    if(index >= 0) {
                        items.eq(index).trigger('mousedown');
                        dropdown.hide();
                    }
                    return;
                }

                if(taxCode.length < 2) {
                    dropdown.html('').hide();
                    return;
                }

                $.ajax({
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    type: "GET",
                    data: { taxCodeSearch: taxCode },
                    success: function(response) {
                        let html = '<ul class="list-group">';
                        response.designWithtaxCodeData.forEach((code, idx) => {
                            let hsnRules = code.sub_hsn || [];
                            let hsnRulesJson = encodeURIComponent(JSON.stringify(hsnRules));
                            html += `<li class="list-group-item suggestion-taxCode ${idx===0?'active':''}"
                                data-hsn-id="${code.id}"
                                data-hsn-code="${code.hsn_code}"
                                data-vattax-id="${code.vattax?.id || code.vat_tax_id || ''}"
                                data-vattax-percentage="${code.vattax?.percentage || ''}"
                                data-hsn-rules="${hsnRulesJson}"
                            >${code.hsn_code}</li>`;
                        });
                        html += '</ul>';
                        dropdown.html(html).show();
                        dropdown.find('li.active')[0]?.scrollIntoView({ block: "nearest" });
                    }
                });
            });

            $(document).on('mousedown', '.suggestion-taxCode', function(e) {
                const hsnCode = $(this).data('hsn-code');
                const hsnId = $(this).data('hsn-id');
                const vatTaxId = $(this).data('vattax-id');
                const vatTaxPercentage = $(this).data('vattax-percentage');
                let hsnRules = $(this).data('hsn-rules');
                if (typeof hsnRules === 'string') {
                    try { hsnRules = JSON.parse(decodeURIComponent(hsnRules)); } catch(err) { hsnRules = []; }
                }

                const parentInput = $(this).closest('td').find('.taxcode');
                const row = parentInput.closest('tr');

                parentInput.val(hsnCode);
                parentInput.data('hsn-rules', hsnRules);
                parentInput.data('default-tax-id', vatTaxId);
                parentInput.data('default-tax-percent', vatTaxPercentage);

                row.find('input[name="taxCodeId[]"]').val(hsnId);
                row.find('input[name="sgst[]"]').val(vatTaxPercentage);
                row.find('input[name="sgstId[]"]').val(vatTaxId);

                calculateRowAll(row);

                $(this).parent().hide();
            });

            $(document).on('focusin', function(e) {
                $('.dropdown-taxCode').each(function() {
                    const dropdown = $(this);
                    const input = dropdown.siblings('.taxcode');
                    if (!$(e.target).is(input)) {
                        dropdown.hide();
                    }
                });
            });

            $(document).on('keydown', '.taxcode', function(e) {
                if (e.key === 'Tab') {
                    const dropdown = $(this).siblings('.dropdown-taxCode');
                    setTimeout(() => dropdown.hide(), 50);
                }
            });

            function updateRowHsnTax(row) {
                let taxCodeInput = row.find('input[name="taxCode[]"]');
                let rules = taxCodeInput.data('hsn-rules');
                let purcRate = parseFloat(row.find('input[name="purcRate[]"]').val()) || 0;
                let inwardDate = $('#inward_date').val() || new Date().toISOString().split('T')[0];

                if (rules && Array.isArray(rules) && rules.length > 0) {
                    let matchedRule = rules.find(rule => {
                        let fromDate = rule.from_date ? rule.from_date.split('T')[0] : null;
                        let toDate = rule.to_date ? rule.to_date.split('T')[0] : null;

                        if (fromDate && inwardDate < fromDate) return false;
                        if (toDate && inwardDate > toDate) return false;

                        let fromRate = parseFloat(rule.from_purchase_rate || 0);
                        let toRate = parseFloat(rule.to_purchase_rate || 0);

                        if (toRate === 0) {
                            return purcRate >= fromRate;
                        }
                        return purcRate >= fromRate && purcRate <= toRate;
                    });

                    if (matchedRule) {
                        let taxPct = matchedRule.vattax ? matchedRule.vattax.percentage : (matchedRule.percentage !== undefined ? matchedRule.percentage : 0);
                        row.find('input[name="sgst[]"]').val(taxPct);
                        row.find('input[name="sgstId[]"]').val(matchedRule.vat_tax_id || '');
                        return;
                    }
                }

                let defaultTaxPercent = taxCodeInput.data('default-tax-percent');
                let defaultTaxId = taxCodeInput.data('default-tax-id');
                if (defaultTaxPercent !== undefined && defaultTaxPercent !== '') {
                    row.find('input[name="sgst[]"]').val(defaultTaxPercent);
                    row.find('input[name="sgstId[]"]').val(defaultTaxId || '');
                }
            }

            // Real-time calculation on rate, qty, price, disc input
            $(document).on('input keyup change', '.buy_price, .price, .discount_percentage, .discount_amount, .qty', function () {
                let $this = $(this);
                let row = $this.closest('tr');
                let buy_price = parseFloat(row.find('input[name="purcRate[]"]').val()) || 0;

                if ($this.hasClass('discount_amount')) {
                    let discAmt = parseFloat($this.val()) || 0;
                    if (buy_price > 0) {
                        let discPct = (discAmt / buy_price) * 100;
                        row.find('input[name="disc[]"]').val(discPct > 0 ? parseFloat(discPct.toFixed(4)) : '');
                    }
                } else if ($this.hasClass('discount_percentage') || $this.hasClass('buy_price')) {
                    let discPct = parseFloat(row.find('input[name="disc[]"]').val()) || 0;
                    if (buy_price > 0 && discPct > 0) {
                        let discAmt = (buy_price * discPct) / 100;
                        row.find('input[name="discAmt[]"]').val(discAmt > 0 ? discAmt.toFixed(2) : '');
                    } else if (discPct === 0) {
                        row.find('input[name="discAmt[]"]').val('');
                    }
                }

                calculateRowAll(row);
            });

            function calculateRowAll(row) {
                let qty = parseFloat(row.find('input[name="qty[]"]').val()) || 0;
                let buy_price = parseFloat(row.find('input[name="purcRate[]"]').val()) || 0;
                let price = parseFloat(row.find('input[name="amount[]"]').val()) || 0;
                let discount_percentage = parseFloat(row.find('input[name="disc[]"]').val()) || 0;

                // Net PurcRate = buy_price - discount_value
                let discount_value = 0;
                if (discount_percentage > 0 && buy_price > 0) {
                    discount_value = (buy_price * discount_percentage) / 100;
                } else {
                    let discAmtInput = parseFloat(row.find('input[name="discAmt[]"]').val()) || 0;
                    if (discAmtInput > 0) {
                        discount_value = discAmtInput;
                        if (buy_price > 0 && discount_percentage === 0) {
                            discount_percentage = (discount_value / buy_price) * 100;
                            row.find('input[name="disc[]"]').val(discount_percentage.toFixed(2));
                        }
                    }
                }
                row.find('input[name="discAmt[]"]').val(discount_value > 0 ? discount_value.toFixed(2) : '0.00');

                let netPurcPrice = buy_price - discount_value;

                // MRP = price (Amount)
                let mrp = price;
                let mark_up = 0;
                let mark_down = 0;

                if (netPurcPrice > 0 && mrp > 0) {
                    mark_up = ((mrp - netPurcPrice) / netPurcPrice) * 100;
                    mark_down = ((mrp - netPurcPrice) / mrp) * 100;
                }

                let totalAmount = qty * netPurcPrice;

                row.find('input[name="mrp[]"]').val(mrp > 0 ? mrp.toFixed(2) : '0.00');
                row.find('input[name="mark_up[]"]').val(mark_up !== 0 ? mark_up.toFixed(2) : '0.00');
                row.find('input[name="mark_down[]"]').val(mark_down !== 0 ? mark_down.toFixed(2) : '0.00');
                row.find('input[name="netPurcRate[]"]').val(totalAmount.toFixed(2));

                // Dynamic tax determination
                updateRowHsnTax(row);

                window.calculateNetPurcRateSum();
            }

            // 2D Grid Arrow Key & Enter Navigation for Purchase Product Table
            $(document).on('keydown', '#inwardProductTable tbody input, #inwardProductTable tbody select', function(e) {
                if ($('.modal.show').length > 0) {
                    return;
                }

                const key = e.key;
                if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter'].includes(key)) {
                    return;
                }

                const $input = $(this);
                const $currentRow = $input.closest('tr');
                const $allRows = $('#inwardProductTable tbody tr');
                const rowIndex = $allRows.index($currentRow);

                const inputsInRow = $currentRow.find('input:visible:not([tabindex="-1"]), select:visible:not([tabindex="-1"])');
                const colIndex = inputsInRow.index($input);

                if (key === 'ArrowDown') {
                    e.preventDefault();
                    if (rowIndex < $allRows.length - 1) {
                        const $nextRow = $allRows.eq(rowIndex + 1);
                        const nextInputs = $nextRow.find('input:visible:not([tabindex="-1"]), select:visible:not([tabindex="-1"])');
                        const target = (colIndex >= 0 && colIndex < nextInputs.length) ? nextInputs.eq(colIndex) : nextInputs.first();
                        target.focus().select();
                    }
                    return;
                }

                if (key === 'ArrowUp') {
                    e.preventDefault();
                    if (rowIndex > 0) {
                        const $prevRow = $allRows.eq(rowIndex - 1);
                        const prevInputs = $prevRow.find('input:visible:not([tabindex="-1"]), select:visible:not([tabindex="-1"])');
                        const target = (colIndex >= 0 && colIndex < prevInputs.length) ? prevInputs.eq(colIndex) : prevInputs.first();
                        target.focus().select();
                    }
                    return;
                }

                if (key === 'ArrowRight') {
                    const isReadOnly = $input.prop('readonly') || $input.hasClass('item') || $input.hasClass('designno') || $input.hasClass('inward-color-input') || $input.hasClass('inward-size-input');
                    const isAtEnd = isReadOnly || ($input[0].selectionEnd === $input[0].value.length);

                    if (isAtEnd) {
                        if (colIndex < inputsInRow.length - 1) {
                            e.preventDefault();
                            inputsInRow.eq(colIndex + 1).focus().select();
                        } else if (rowIndex < $allRows.length - 1) {
                            e.preventDefault();
                            $allRows.eq(rowIndex + 1).find('input:visible:first').focus().select();
                        }
                    }
                    return;
                }

                if (key === 'ArrowLeft') {
                    const isReadOnly = $input.prop('readonly') || $input.hasClass('item') || $input.hasClass('designno') || $input.hasClass('inward-color-input') || $input.hasClass('inward-size-input');
                    const isAtStart = isReadOnly || ($input[0].selectionStart === 0);

                    if (isAtStart) {
                        if (colIndex > 0) {
                            e.preventDefault();
                            inputsInRow.eq(colIndex - 1).focus().select();
                        } else if (rowIndex > 0) {
                            e.preventDefault();
                            $allRows.eq(rowIndex - 1).find('input:visible:last').focus().select();
                        }
                    }
                    return;
                }

                if (key === 'Enter') {
                    e.preventDefault();
                    if (colIndex >= 0 && colIndex < inputsInRow.length - 1) {
                        inputsInRow.eq(colIndex + 1).focus().select();
                    } else if (colIndex === inputsInRow.length - 1) {
                        if (rowIndex < $allRows.length - 1) {
                            $allRows.eq(rowIndex + 1).find('input.item').focus();
                        } else {
                            createRow();
                        }
                    }
                    return;
                }
            });

            // F1 / Fn+F1 Key Shortcut: Adds a new row at any time from anywhere on the page
            $(document).on('keydown', function(e) {
                if (e.key === 'F1') {
                    e.preventDefault();
                    createRow();
                }
            });

            // Open Item Tax Breakdown Detail Modal (matching Image 2)
            $(document).on('click', '.open-item-tax-modal', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const row = $(this).closest('tr');
                const itemName = row.find('input[name="item[]"]').val() || '-';
                const designNo = row.find('input[name="designNo[]"]').val() || '-';
                const qty = parseFloat(row.find('input[name="qty[]"]').val()) || 0;
                const purcRate = parseFloat(row.find('input[name="purcRate[]"]').val()) || 0;
                const discAmt = parseFloat(row.find('input[name="discAmt[]"]').val()) || 0;
                const netPurcPrice = purcRate - discAmt;
                const netPurcRate = parseFloat(row.find('input[name="netPurcRate[]"]').val()) || (qty * netPurcPrice);
                const taxCode = row.find('input[name="taxCode[]"]').val() || 'GST';
                const gstPct = parseFloat(row.find('input[name="sgst[]"]').val()) || 0;

                const shopStateId = parseInt($('#shop_state_id').val()) || 12;
                const shopStateName = $('#shop_state_name').val() || 'Gujarat';
                const partyStateId = parseInt($('#inward_party_state_id').val()) || shopStateId; // default to intra-state if unset
                const partyStateName = $('#inward_party_state_name').val() || shopStateName;

                // Check supply type: intra-state (same state) vs inter-state (different state)
                const isIntraState = (partyStateId === shopStateId);

                let sgstPct = 0, cgstPct = 0, igstPct = 0;
                let sgstAmt = 0, cgstAmt = 0, igstAmt = 0;

                const totalTaxAmt = (netPurcRate * gstPct) / 100;

                if (isIntraState) {
                    sgstPct = gstPct / 2;
                    cgstPct = gstPct / 2;
                    sgstAmt = totalTaxAmt / 2;
                    cgstAmt = totalTaxAmt / 2;
                    igstPct = 0;
                    igstAmt = 0;
                } else {
                    sgstPct = 0;
                    cgstPct = 0;
                    sgstAmt = 0;
                    cgstAmt = 0;
                    igstPct = gstPct;
                    igstAmt = totalTaxAmt;
                }

                const withTaxAmount = netPurcRate + totalTaxAmt;
                const perItemCostRate = qty > 0 ? (withTaxAmount / qty) : withTaxAmount;

                // Populate Modal
                $('#taxModalItemName').text(itemName);
                $('#taxModalDesignNo').text(designNo);
                $('#taxModalQty').text(qty);
                $('#taxModalNetPurcPrice').text('₹' + netPurcPrice.toFixed(2));
                $('#taxModalNetPurcRate').text('₹' + netPurcRate.toFixed(2));

                $('#taxModalShopState').text(shopStateName);
                $('#taxModalPartyState').text(partyStateName);

                if (isIntraState) {
                    $('#taxModalSupplyTypeBadge').html(`
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fs-7">
                            <i class="fa-solid fa-check-double me-1"></i> Intra-State Supply (CGST + SGST)
                        </span>
                    `);
                    $('#taxModalCalculationNote').text('Intra-State: Shop and Party are in the same state (' + shopStateName + '). GST of ' + gstPct + '% is divided equally into ' + sgstPct.toFixed(2) + '% SGST and ' + cgstPct.toFixed(2) + '% CGST.');
                } else {
                    $('#taxModalSupplyTypeBadge').html(`
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fs-7">
                            <i class="fa-solid fa-right-left me-1"></i> Inter-State Supply (IGST)
                        </span>
                    `);
                    $('#taxModalCalculationNote').text('Inter-State: Shop (' + shopStateName + ') and Party (' + partyStateName + ') are in different states. Full GST of ' + gstPct + '% is applied as IGST.');
                }

                $('#taxModalTaxCode').text((taxCode ? taxCode : 'GST') + ' ' + (gstPct > 0 ? gstPct + ' %' : ''));
                $('#taxModalSgstPct').text(sgstPct.toFixed(2));
                $('#taxModalSgstAmt').text(sgstAmt.toFixed(2));
                $('#taxModalCgstPct').text(cgstPct.toFixed(2));
                $('#taxModalCgstAmt').text(cgstAmt.toFixed(2));
                $('#taxModalIgstPct').text(igstPct.toFixed(2));
                $('#taxModalIgstAmt').text(igstAmt.toFixed(2));
                $('#taxModalWithTaxAmount').text(withTaxAmount.toFixed(2));
                $('#taxModalPerItemCostRate').text(perItemCostRate.toFixed(2));

                $('#itemTaxDetailModal').modal('show');
            });
        });
    </script>

    {{-- Data Save --}}
    <script>
        $(document).ready(function (){
            $(document).on('click','#saveButton', function(e){
                e.preventDefault();
                var submitButton = $(this).find("#saveButton");
                var originalButtonText = submitButton.text();



                let headerData = {
                    day_book_id: $('#day_book_id').val(),
                    inward_vat_tax_id: $('#inward_vat_tax_id').val(),
                    inward_voucher_no: $('#inward_voucher_no').val(),
                    inward_invoice_id: $('#inward_invoice_id').val(),

                    purchase_date: $('#purchase_date').val(),
                    purchase_day_name: $('#purchase_day_name').val(),
                    purchase_time: $('#purchase_time').val(),
                    bill_date: $('#bill_date').val(),
                    inward_challan_no: $('#inward_challan_no').val(),
                    inward_challan_date: $('#inward_challan_date').val(),
                    inward_party_code: $('#inward_party_code').val(),
                    inward_total: $('#inward_total').val(),
                    inward_party_limit: $('#inward_party_limit').val(),
                    inward_acc_credit_day: $('#inward_acc_credit_day').val(),
                    inward_acc_purchaser: $('#inward_acc_purchaser').val(),
                    inward_acc_season: $('#inward_acc_season').val(),
                    inward_acc_agent: $('#inward_acc_agent').val(),
                    inward_acc_transport: $('#inward_acc_transport').val(),
                    inward_acc_delivery_by: $('#inward_acc_delivery_by').val(),
                    inward_acc_lr_no: $('#inward_acc_lr_no').val(),
                    inward_acc_lr_date: $('#inward_acc_lr_date').val(),
                    inward_acc_remark: $('#inward_acc_remark').val(),
                    inward_bill_remark: $('#inward_bill_remark').val(),
                    cash_or_credit: $('#cash_or_credit').val(),
                    bank_cash_discount_percent: $('#bank_cash_discount_percent').val(),
                    gross_amount: $('#inward_acc_gross_amount').val(),
                    bill_discount_percent: $('#bill_discount_percent').val(),
                    bill_discount_amount: $('#bill_discount_amount').val(),
                    cash_discount_percent: $('#cash_discount_percent').val(),
                    cash_discount_amount: $('#cash_discount_amount').val(),
                    agent_commission_percent: $('#agent_commission_percent').val(),
                    agent_commission_amount: $('#agent_commission_amount').val(),
                    expense_amount: $('#expense_amount').val(),
                    other_amount: $('#other_amount').val(),
                    inward_acc_gst_amount: $('#inward_acc_gst_amount').val(),
                    inward_acc_net_amount: $('#inward_acc_net_amount').val(),
                    inward_acc_freight_amount: $('#inward_acc_freight_amount').val(),
                    inward_acc_parcel_amount: $('#inward_acc_parcel_amount').val(),
                    inward_acc_amt_with_gst: $('#inward_acc_amt_with_gst').val(),
                };

                // Prepare data
                let formData = [];

                $('#inwardProductTable tbody tr').each(function(index, tr){
                    const $tr = $(tr);
                    const row = {
                        inwardProductId:$tr.find('input[name="inwardProductId[]"]').val(),
                        item: $tr.find('input[name="item[]"]').val(),
                        itemid: $tr.find('input[name="itemid[]"]').val(),
                        designNo: $tr.find('input[name="designNo[]"]').val(),
                        designid: $tr.find('input[name="designid[]"]').val(),
                        qty: $tr.find('input[name="qty[]"]').val(),
                        purcRate: $tr.find('input[name="purcRate[]"]').val(),
                        amount: $tr.find('input[name="amount[]"]').val(),
                        disc: $tr.find('input[name="disc[]"]').val(),
                        discAmt: $tr.find('input[name="discAmt[]"]').val(),
                        mrp: $tr.find('input[name="mrp[]"]').val(),
                        mark_up: $tr.find('input[name="mark_up[]"]').val(),
                        mark_down: $tr.find('input[name="mark_down[]"]').val(),
                        netPurcRate: $tr.find('input[name="netPurcRate[]"]').val(),
                        taxCode: $tr.find('input[name="taxCode[]"]').val(),
                        taxCodeId: $tr.find('input[name="taxCodeId[]"]').val(),
                        sgst: $tr.find('input[name="sgst[]"]').val(),
                        sgstId: $tr.find('input[name="sgstId[]"]').val(),
                        colorInwardIds: $tr.find('input[name="colorInwardIds[]"]').val() ? [$tr.find('input[name="colorInwardIds[]"]').val()] : ($tr.find('.colorSelectInward').val() || []),
                        sizeInwardIds: $tr.find('input[name="sizeInwardIds[]"]').val() ? [$tr.find('input[name="sizeInwardIds[]"]').val()] : ($tr.find('.sizeSelectInward').val() || [])
                    };
                    formData.push(row);
                });
                console.log(formData)

                var inwardId = $('#inward_invoice_id').val();

                {{--if (inwardId) {--}}
                {{--    // Update--}}
                {{--    url = `/shop/inward-product/${inwardId}/update`;--}}
                {{--    method = 'PUT';--}}
                {{--} else {--}}
                {{--    // Create--}}
                {{--    url = "{{ route('shop.purchaseProduct.store') }}";--}}
                {{--    method = 'POST';--}}
                {{--}--}}

                    url = "{{ route('shop.purchaseProduct.store') }}";
                    method = 'POST';

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: url,
                    type: method,
                    data: {rows:formData,purchaseData:headerData},
                    success: function (res) {
                        if (res.status) {
                            $("#create-record-btn").hide();
                            $(".dataLoadToShow").addClass('d-none')
                            $('#inwardProductTable tbody').html('');
                            resetFormInward();
                            toastr.success(res.message);
                        }else{
                            toastr.error(res.message);
                        }
                        submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');

                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;
                            console.log(errors)
                            // Pehle sab red border remove karo
                            clearValidationBorders();

                            $.each(errors, function (key, messages) {

                                if (key.startsWith('rows.')) {

                                    let parts = key.split('.');
                                    let rowIndex = parts[1];
                                    let fieldName = parts[2];

                                    $('#inwardProductTable tbody tr').eq(rowIndex)
                                        .find('[name="' + fieldName + '[]"]')
                                        .addClass('is-invalid-border');

                                } else if (key.startsWith('purchaseData.')) {

                                    let fieldName = key.split('.')[1];
                                    let $field = $('[name="' + fieldName + '"]');

                                    if ($field.hasClass('select2-hidden-accessible')) {
                                        // Select2 field
                                        $field.next('.select2-container')
                                            .addClass('select2-invalid');
                                    } else {
                                        // Normal input
                                        $field.addClass('is-invalid-border');
                                    }
                                }else if(key === 'rows') {

                                    $('#rowsErrorContainer').text(messages[0]);
                                    $('#rowsErrorTd').addClass('bg-warning-light');

                                }

                            });

                            let $firstInvalid = $('.is-invalid-border:first');

                            if ($firstInvalid.length) {
                                $firstInvalid.focus();
                            } else {
                                // Agar normal input nahi mila to select2 open karo
                                $('.select2-invalid:first')
                                    .prev('select')
                                    .select2('open');
                            }
                        }

                        // if (xhr.status === 422) {
                        //     var errors = xhr.responseJSON.errors;
                        //     $(".errorSpan").text('');
                        //
                        //     // Show new errors
                        //     $.each(errors, function (key, messages) {
                        //         var errorSpan = $('#' + key +'ErrorMessage');
                        //
                        //         if (errorSpan.length) {
                        //             errorSpan.text(messages[0]);
                        //         }
                        //     });
                        //
                        // } else {
                        //     console.log("Unexpected error:", xhr);
                        //     submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');
                        // }
                    }

                });
            });
        });
    </script>

    {{-- Account Code And Name Search--}}
    <script>
        $(document).ready(function (){
            {{--$('#inward_party_code').select2({--}}
            {{--    dropdownParent: $('#itemForm'),--}}
            {{--    placeholder: "Select Account Master",--}}
            {{--    allowClear: true,--}}
            {{--    ajax: {--}}
            {{--        url: '{{ route('shop.designMaster.modalData') }}',--}}
            {{--        dataType: 'json',--}}
            {{--        delay: 250,--}}
            {{--        data: function (params) {--}}
            {{--            return {searchAccountMaster: params.term || ''};--}}
            {{--        },--}}
            {{--        processResults: function (data) {--}}
            {{--            return {--}}
            {{--                results: data.accountMasters.map(function (accountMaster) {--}}
            {{--                    return {--}}
            {{--                        id: accountMaster.id,--}}
            {{--                        text: accountMaster.accountshortcode,--}}
            {{--                        accName: accountMaster.accountName,--}}
            {{--                        accLimit: accountMaster.other_info_act_limit,--}}
            {{--                    };--}}
            {{--                })--}}
            {{--            };--}}
            {{--        },--}}
            {{--        cache: true--}}
            {{--    },--}}
            {{--    minimumInputLength: 0--}}
            {{--});--}}

            // Account Master On Change Name Get
            $('#inward_party_code').on('select2:select', function (e) {
                const data = e.params.data;
                $("#inward_party_name").val(data.accName || '');
                $("#inward_party_limit").val(data.accLimit || '');
            });

            // Select clear hone par input blank
            $('#inward_party_code').on('select2:unselect', function (e) {
                $("#inward_party_name").val('');
                $("#inward_party_limit").val('');
            });


            fillDayAndTime();

            $('#purchase_date').on('change', function () {
                fillDayAndTime();
            });

        });



    </script>

    {{-- Purchase Code And Name Search--}}
    <script>
        $(document).ready(function (){
            {{--$('#inward_acc_purchaser').select2({--}}
            {{--    dropdownParent: $('#itemForm'),--}}
            {{--    placeholder: "Select Purchaser",--}}
            {{--    allowClear: true,--}}
            {{--    ajax: {--}}
            {{--        url: '{{ route('shop.designMaster.modalData') }}',--}}
            {{--        dataType: 'json',--}}
            {{--        delay: 250,--}}
            {{--        data: function (params) {--}}
            {{--            return {searchAccountMaster: params.term || ''};--}}
            {{--        },--}}
            {{--        processResults: function (data) {--}}
            {{--            return {--}}
            {{--                results: data.accountMasters.map(function (accountMaster) {--}}
            {{--                    return {--}}
            {{--                        id: accountMaster.id,--}}
            {{--                        text: accountMaster.accountshortcode + ' - ' + accountMaster.accountName,--}}
            {{--                        accName: accountMaster.accountName--}}
            {{--                    };--}}
            {{--                })--}}
            {{--            };--}}
            {{--        },--}}
            {{--        cache: true--}}
            {{--    },--}}
            {{--    minimumInputLength: 0--}}
            {{--});--}}

            $('#inward_acc_purchaser').select2({
                dropdownParent: $('#itemForm'),
                placeholder: "Select Purchaser",
                allowClear: true,
                ajax: {
                    url: '{{ route('shop.designMaster.modalData') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {searchEmployeeName: params.term || ''};
                    },
                    processResults: function (data) {
                        return {
                            results: data.employeeMasters.map(function (employeeMaster) {
                                return {
                                    id: employeeMaster.id,
                                    text: employeeMaster.name + ' - ' + employeeMaster.last_name,
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });


        });
    </script>

    {{-- Season Name Search --}}
    <script>
        $(document).ready(function (){
            $('#inward_acc_season').select2({
                dropdownParent: $('#itemForm'),
                placeholder: "Select Season",
                allowClear: true,
                ajax: {
                    url: '{{ route('shop.masterSeason.seasonFind') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {searchSeasonMaster: params.term || ''};
                    },
                    processResults: function (data) {
                        return {
                            results: data.seasonMasters.map(function (seasonMaster) {
                                return {
                                    id: seasonMaster.id,
                                    text: seasonMaster.name,
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });


        });
    </script>

    {{-- Agent Code And Name Search --}}
    <script>
        $(document).ready(function (){
            $('#inward_acc_agent').select2({
                dropdownParent: $('#itemForm'),
                placeholder: "Select a Agent",
                allowClear: true,
                ajax: {
                    url: '{{ route('shop.masterAgent.agentFind') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {searchAgentMaster: params.term || ''};
                    },
                    processResults: function (data) {
                        return {
                            results: data.agentMasters.map(function (agentMaster) {
                                return {
                                    id: agentMaster.id,
                                    text: agentMaster.code + ' - ' + agentMaster.name,
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });


        });
    </script>

    {{-- Transport Name Search --}}
    <script>
        $(document).ready(function (){
            $('#inward_acc_transport').select2({
                dropdownParent: $('#itemForm'),
                placeholder: "Select a Transport",
                allowClear: true,
                ajax: {
                    url: '{{ route('shop.masterTransport.transportFind') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {searchTransportMaster: params.term || ''};
                    },
                    processResults: function (data) {
                        return {
                            results: data.transportMasters.map(function (transportMaster) {
                                return {
                                    id: transportMaster.id,
                                    text: transportMaster.name,
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });


        });
    </script>

    {{-- Delivery By Name Search --}}
    <script>
        $(document).ready(function (){
            $('#inward_acc_delivery_by').select2({
                dropdownParent: $('#itemForm'),
                placeholder: "Select a Delivery by",
                allowClear: true,
                ajax: {
                    url: '{{ route('shop.masterdeliveryBy.deliveryByFind') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {searchDeliveryByMaster: params.term || ''};
                    },
                    processResults: function (data) {
                        return {
                            results: data.deliveryByMasters.map(function (deliveryByMaster) {
                                return {
                                    id: deliveryByMaster.id,
                                    text: deliveryByMaster.name,
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });


        });

    </script>

@endpush