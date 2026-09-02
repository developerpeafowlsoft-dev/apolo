<form id="itemForm">
    @csrf
    <!-- Kachi Entry Loaded Banner -->
    <div id="kachiLoadedBanner" class="alert alert-warning border-warning d-none align-items-center justify-content-between py-2 px-3 mb-2 shadow-sm" style="border-radius: 8px; background-color: #fffbe6; color: #856404; font-weight: 600;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark px-2 py-1 fs-6" style="letter-spacing: 0.5px;"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ __('Kachi Entry Loaded') }}</span>
            <span>{{ __('This Voucher / Challan belongs to a temporary / Kachi Entry.') }}</span>
        </div>
    </div>
    <div class="card inwardAccountDetails mb-2">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-1">
                    <x-input type="text" name="inward_voucher_no" label="Voucher No" placeholder="Voucher No" value="{{ old('inward_voucher_no') }}" required="true"/>
                </div>

                <div class="col-md-6 col-lg-1">
                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                        <span>{{ __('Challan No.') }} <span class="text-danger">*</span></span>
                    </label>

                    <input type="text" id="inward_challan_no" name="inward_challan_no"
                           placeholder="Challan No."
                           class="form-control @error('inward_challan_no') is-invalid @enderror" value="{{ old('inward_challan_no') }}"
                            {{--                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"--}}
                    />
                    @error('inward_challan_no')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                </div>
                <div class="col-md-6 col-lg-1-5">
                    <x-input type="date" name="purchase_date" label="Date" value="{{ old('purchase_date') }}"/>
                </div>


                {{--            <div class="col-md-6 col-lg-2">--}}
                {{--                <x-input type="text" name="inward_day_name" label="Day Name" placeholder="Enter Day Name" value="{{ old('inward_day_name') }}" required="true" readonly="true"/>--}}
                {{--            </div>--}}

                <div class="col-md-6 col-lg-1">
                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                                        <span>
                                            {{ __('Day Name') }}
                                            <span class="text-danger">*</span>
                                        </span>
                        </div>
                    </label>
                    <div class="input-group flex-nowrap">
                        <input type="text" class="form-control disabledCls" name="purchase_day_name" placeholder="Day Name" id="purchase_day_name" required="true" readonly>
                    </div>
                    <span id="purchase_day_nameErrorMessage" class="text-danger errorSpan"></span>
                </div>
                <input type="hidden" name="inward_invoice_id" id="inward_invoice_id">
                <div class="col-md-6 col-lg-1-5">
                    <x-input type="time" name="purchase_time" label="Time" value="{{ old('purchase_time') }}"/>
                </div>

                <div class="col-md-6 col-lg-1-5">
                    <x-input type="date" name="bill_date" label="Bill Date" value="{{ old('bill_date') }}"/>
                </div>

                <div class="col-md-6 col-lg-1">
{{--                    <x-select label="Party Code" name="inward_party_code" required="true">--}}
{{--                    </x-select>--}}
{{--                    <span id="inward_party_codeErrorMessage" class="text-danger errorSpan"></span>--}}

                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                                        <span>
                                            {{ __('Party Code') }}
                                            <span class="text-danger">*</span>
                                        </span>
                        </div>
                    </label>
                    <div class="input-group flex-nowrap">
                        <input type="text" class="form-control disabledCls" name="inward_party_code_value" placeholder="Party Code" id="inward_party_code_value" required="true" readonly>
                    </div>

                    <div class="input-group flex-nowrap">
                        <input type="hidden" class="form-control disabledCls" name="inward_party_code" placeholder="Party Code" id="inward_party_code" required="true" readonly>
                    </div>
                    <span id="inward_party_codeErrorMessage" class="text-danger errorSpan"></span>
                </div>

                <div class="col-md-6 col-lg-2">
                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                                        <span>
                                            {{ __('Party Name') }}
                                            <span class="text-danger">*</span>
                                        </span>
                        </div>
                    </label>
                    <div class="input-group flex-nowrap">
                        <input type="text" class="form-control disabledCls" name="inward_party_name" placeholder="Party Name" id="inward_party_name" required="true" readonly>
                    </div>
                    <span id="inward_party_nameErrorMessage" class="text-danger errorSpan"></span>
                </div>
                <div class="col-md-6 col-lg-1">
                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                        <span>{{ __('Total') }} <span class="text-danger">*</span> </span>
                    </label>

                    <input type="text" id="inward_total" name="inward_total"
                           placeholder="Party Limit"
                           class="form-control @error('inward_total') is-invalid @enderror" value="{{ old('inward_total') }}"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                           required="true"
                    />
                    @error('inward_total')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                </div>
                <div class="col-md-6 col-lg-1">
                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                        <span>{{ __('Party Limit') }} <span class="text-danger">*</span> </span>
                    </label>

                    <input type="text" id="inward_party_limit" name="inward_party_limit"
                           placeholder="Party Limit"
                           class="form-control disabledCls @error('inward_party_limit') is-invalid @enderror" value="{{ old('inward_party_limit') }}"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                           required="true"
                    />
                    @error('inward_party_limit')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                </div>
            </div>
        </div>
    </div>

    <div class="card table-responsive mb-2 dataLoadToShow d-none">
        <div class="card-body">
            <table class="table border-left-right table-responsive-md" id="inwardProductTable">
                <thead>
                <tr>
                    <th class="text-center">{{ __('SL') }}</th>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('Design No') }}</th>
                    <th>{{ __('Color') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('Qty') }}</th>
                    <th>{{ __('Purc Rate') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Disc (%)') }}</th>
                    <th>{{ __('MRP') }}</th>
                    <th>{{ __('Markup (%)') }}</th>
                    <th>{{ __('Markdown (%)') }}</th>

                    <th>{{ __('Net PurcRate') }}</th>
                    <th>{{ __('Tax Code') }}</th>
                    <th>{{ __('Total GST (%)') }}</th>
                    <th class="text-center">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                <tr>
                    <td colspan="16" id="rowsErrorTd">
                        <span id="rowsErrorContainer" class="text-danger mb-2"></span>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <div class="accountDetailsFinal dataLoadToShow d-none">
        <div class="col-md-12 col-lg-12">
            <div class="row">
                {{-- Tax Information --}}
                <div class="col-12 col-md-6">
                    <div class="card mb-4">
                        <div class="card-header fw-bold">
                            {{ __('Account Detail') }}
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Credit Days') }}</span>
                                    </label>

                                    <input type="text" id="inward_acc_credit_day" name="inward_credit_day"
                                           placeholder="Enter Credit Days"
                                           class="form-control @error('inward_credit_day') is-invalid @enderror" value="{{ old('inward_credit_day','0') }}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                    />
                                    @error('inward_credit_day')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Purchaser" name="inward_acc_purchaser">
                                        <option value="">{{ __('Select a Purchaser') }}</option>
                                    </x-select>
                                </div>

                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Season" name="inward_acc_season">
                                        <option value="">{{ __('Select a Season') }}</option>
                                    </x-select>
                                </div>

                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Agent Code" name="inward_acc_agent">
                                        <option value="">{{ __('Select a Agent') }}</option>
                                    </x-select>
                                </div>


                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Transport" name="inward_acc_transport">
                                        <option value="">{{ __('Select a Transport') }}</option>
                                    </x-select>
                                </div>

                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Delivery By" name="inward_acc_delivery_by">
                                        <option value="">{{ __('Select a Delivery By') }}</option>
                                    </x-select>
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <x-input type="text" name="inward_acc_lr_no" label="LR No" placeholder="LR No" value="{{ old('inward_acc_lr_no') }}" />
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <x-input type="date" name="inward_acc_lr_date" label="LR Date" value="{{ old('inward_acc_lr_date') }}"/>
                                </div>

                                <div class="col-md-12 col-lg-12">
                                    <label for="inward_acc_remark" class="form-label">
                                        {{ __('Remark') }}
                                    </label>
                                    <textarea name="inward_acc_remark" id="inward_acc_remark" class="form-control @error('inward_acc_remark') is-invalid @enderror" rows="1" placeholder="Enter Remark" >{{ old('inward_acc_remark') }}</textarea>
                                    @error('inward_acc_remark')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bank Information --}}
                <div class="col-12 col-md-6">
                    <div class="card mb-4">
                        <div class="card-header fw-bold">
                            {{ __('Bill Detail') }}
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('GST Amount') }}</span>
                                    </label>

                                    <input type="text" id="inward_acc_gst_amount" name="inward_acc_gst_amount"
                                           placeholder="Enter GST Amount"
                                           class="form-control disabledCls decimal-input @error('inward_acc_gst_amount') is-invalid @enderror" value="{{ old('inward_acc_gst_amount','0.00') }}"
                                           readonly
                                    />
                                    @error('inward_acc_gst_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Net Amount') }}</span>
                                    </label>

                                    <input type="text" id="inward_acc_net_amount" name="inward_acc_net_amount"
                                           placeholder="Enter Net Amount"
                                           class="form-control disabledCls decimal-input @error('inward_acc_net_amount') is-invalid @enderror" value="{{ old('inward_acc_net_amount','0.00') }}"
                                           readonly
                                    />
                                    @error('inward_acc_net_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Freight Amount') }}</span>
                                    </label>

                                    <input type="text" id="inward_acc_freight_amount" name="inward_acc_freight_amount"
                                           placeholder="Enter Freight Amount"
                                           class="form-control decimal-input @error('inward_acc_freight_amount') is-invalid @enderror" value="{{ old('inward_acc_freight_amount','0.00') }}"
                                    />
                                    @error('inward_acc_freight_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Parcel Amount') }}</span>
                                    </label>

                                    <input type="text" id="inward_acc_parcel_amount" name="inward_acc_parcel_amount"
                                           placeholder="Enter Parcel Amount"
                                           class="form-control decimal-input @error('inward_acc_parcel_amount') is-invalid @enderror" value="{{ old('inward_acc_parcel_amount','0.00') }}"
                                    />
                                    @error('inward_acc_parcel_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                 <div class="col-md-6 col-lg-6">
                                     <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                         <span>{{ __('Round Off') }}</span>
                                     </label>

                                     <input type="text" id="inward_acc_round_off" name="inward_acc_round_off"
                                            placeholder="0.00"
                                            class="form-control disabledCls decimal-input" value="0.00"
                                            readonly
                                     />
                                 </div>

                                 <div class="col-md-6 col-lg-6">
                                     <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                         <span>{{ __('Amt With GST') }}</span>
                                     </label>

                                     <input type="text" id="inward_acc_amt_with_gst" name="inward_acc_amt_with_gst"
                                            placeholder="Enter Amt With GST"
                                            class="form-control disabledCls decimal-input summary-accent text-primary fw-bold font-monospace fs-5 @error('inward_acc_amt_with_gst') is-invalid @enderror" value="{{ old('inward_acc_amt_with_gst','0.00') }}"
                                            readonly
                                     />
                                     @error('inward_acc_amt_with_gst')
                                     <span class="text-danger">{{ $message }}</span>
                                     @enderror
                                 </div>


                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="d-flex gap-3 justify-content-end align-items-center my-3 dataLoadToShow d-none">
        <button type="reset" class="btn btn-outline-secondary rounded py-2">
            {{ __('Reset') }}
        </button>
        <button type="button" class="btn btn-primary rounded py-2 px-5" id="saveButton">
            {{ __('Submit') }}
        </button>
    </div>
</form>


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

            // $(document).on('blur','#inwardProductTable',function (){
            //     calculateNetPurcRateSum();
            // });

             $(document).on(
                'input keyup change',
                'input[name="netPurcRate[]"], #inwardProductTable, input[name="sgst[]"], #inward_acc_freight_amount, #inward_acc_parcel_amount',
                function () {
                    window.calculateNetPurcRateSum();
                }
            );

            window.calculateNetPurcRateSum = function () {

                let netTotal = 0;
                let gstTotal = 0;

                $('#inwardProductTable tbody tr').each(function () {

                    let row = $(this);

                    let netAmount = parseFloat(row.find('input[name="netPurcRate[]"]').val()) || 0;
                    let gstPercent = parseFloat(row.find('input[name="sgst[]"]').val()) || 0;

                    // GST amount for this row
                    let gstAmount = (netAmount * gstPercent) / 100;

                    netTotal += netAmount;
                    gstTotal += gstAmount;
                });

                let freight = parseFloat($('#inward_acc_freight_amount').val()) || 0;
                let parcel = parseFloat($('#inward_acc_parcel_amount').val()) || 0;

                let subTotal = netTotal + gstTotal + freight + parcel;
                let roundedGrand = Math.round(subTotal);
                let roundOff = roundedGrand - subTotal;

                console.log("Net Total:", netTotal);
                console.log("GST Total:", gstTotal);
                console.log("Freight:", freight);
                console.log("Parcel:", parcel);
                console.log("Grand Total Rounded:", roundedGrand);
                console.log("Round Off:", roundOff);

                // Set values in inputs
                $("#inward_acc_net_amount").val(netTotal.toFixed(2));
                $("#inward_acc_gst_amount").val(gstTotal.toFixed(2));
                $("#inward_acc_round_off").val(roundOff.toFixed(2));
                $("#inward_acc_amt_with_gst").val(roundedGrand.toFixed(2));

                return {
                    netTotal,
                    gstTotal,
                    grandTotal: roundedGrand,
                    roundOff
                };
            }



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

            // Close


            // Tax Code Find



            //Markup And Markdown Get
            $(document).on('input keyup', '.buy_price, .price, .discount_percentage,.qty', function () {
                calculateRowAll($(this).closest('tr'));
            });

            function calculateRowAll(row) {
                let qty = parseFloat(row.find('input[name="qty[]"]').val()) || 0;
                let buy_price = parseFloat(row.find('input[name="purcRate[]"]').val()) || 0;
                let price = parseFloat(row.find('input[name="amount[]"]').val()) || 0;
                let discount_percentage = parseFloat(row.find('input[name="disc[]"]').val()) || 0;

                let mrp = 0;
                let mark_up = 0;
                let mark_down = 0;

                if (discount_percentage > 0) {
                    let discount_value = (price * discount_percentage) / 100;
                    mrp = price - discount_value;
                } else {
                    mrp = price;
                }

                let totalAmount = qty * mrp;

                row.find('input[name="mrp[]"]').val(mrp.toFixed(2));
                row.find('input[name="netPurcRate[]"]').val(totalAmount.toFixed(2));

                if (buy_price > 0 && mrp > 0) {
                    mark_up = ((mrp - buy_price) / buy_price) * 100;
                    mark_down = ((mrp - buy_price) / mrp) * 100;
                }

                // $('input[name="mark_up"]').val(mark_up.toFixed(2));
                // $('input[name="mark_down"]').val(mark_down.toFixed(2));

                row.find('input[name="mark_up[]"]').val(mark_up.toFixed(2));
                row.find('input[name="mark_down[]"]').val(mark_down.toFixed(2));
            }

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
                        mrp: $tr.find('input[name="mrp[]"]').val(),
                        mark_up: $tr.find('input[name="mark_up[]"]').val(),
                        mark_down: $tr.find('input[name="mark_down[]"]').val(),
                        netPurcRate: $tr.find('input[name="netPurcRate[]"]').val(),
                        taxCode: $tr.find('input[name="taxCode[]"]').val(),
                        taxCodeId: $tr.find('input[name="taxCodeId[]"]').val(),
                        sgst: $tr.find('input[name="sgst[]"]').val(),
                        sgstId: $tr.find('input[name="sgstId[]"]').val(),
                        colorInwardIds: $tr.find('.colorSelectInward').val(),  // array of selected colors
                        sizeInwardIds: $tr.find('.sizeSelectInward').val()     // array of selected sizes
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