<form id="itemForm">
    @csrf
    <div class="card inwardAccountDetails mb-2">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-1">
                    <x-select label="Day Book" name="day_book_id">
                        @foreach ($dayBooks as $dayBook)
                            <option value="{{ $dayBook->id }}" {{ old('day_book_id') == $dayBook->id ? 'selected' : '' }}>
                                {{ $dayBook->counter_name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <div class="col-md-6 col-lg-1">
                    <x-select label="Tax Type" name="inward_vat_tax_id" required="true">
                        @foreach ($inwardTaxs as $tax)
                            <option value="{{ $tax->id }}" {{ old('inward_vat_tax_id') == $tax->id ? 'selected' : '' }}>
                                {{ $tax->name . " " .$tax->percentage . "%"}}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <div class="col-md-6 col-lg-1">
                    <x-input type="text" name="inward_voucher_no" label="Voucher No" placeholder="Voucher No" value="{{ old('inward_voucher_no') }}" required="true"/>
                </div>
                <div class="col-md-6 col-lg-1-5">
                    <x-input type="date" name="inward_date" label="Inward Date" value="{{ old('inward_date') }}"/>
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
                        <input type="text" class="form-control disabledCls" name="inward_day_name" placeholder="Day Name" id="inward_day_name" required="true" readonly>
                    </div>
                    <span id="inward_day_nameErrorMessage" class="text-danger errorSpan"></span>
                </div>
                <div class="col-md-6 col-lg-1-5">
                    <x-input type="time" name="inward_time" label="Inward Time" value="{{ old('inward_time') }}"/>
                </div>
                <div class="col-md-6 col-lg-1">
                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                        <span>{{ __('Challan No.') }} </span>
                    </label>

                    <input type="text" id="inward_challan_no" name="inward_challan_no"
                           placeholder="Challan No."
                           class="form-control @error('inward_challan_no') is-invalid @enderror" value="{{ old('inward_challan_no') }}"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                    />
                    @error('inward_challan_no')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror

                </div>
                <div class="col-md-6 col-lg-1-5">
                    <x-input type="date" name="inward_challan_date" label="Challan Date" value="{{ old('inward_challan_date') }}"/>
                </div>
                <div class="col-md-6 col-lg-1">
                    <x-select label="Party Code" name="inward_party_code" required="true">
                    </x-select>
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

    <div class="card table-responsive mb-2">
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
                    <th>{{ __('SGST (%)') }}</th>
                    <th class="text-center">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>

    </div>

    <div class="accountDetailsFinal">
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

                                <div class="col-md-6 col-lg-6 ms-auto">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Amt With GST') }}</span>
                                    </label>

                                    <input type="text" id="inward_acc_amt_with_gst" name="inward_acc_amt_with_gst"
                                           placeholder="Enter Amt With GST"
                                           class="form-control disabledCls decimal-input @error('inward_acc_amt_with_gst') is-invalid @enderror" value="{{ old('inward_acc_amt_with_gst','0.00') }}"
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

    <div class="d-flex gap-3 justify-content-end align-items-center my-3">
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
                'input keyup blur',
                'input[name="netPurcRate[]"], #inwardProductTable, input[name="sgst[]"]',
                function () {
                    calculateNetPurcRateSum();
                }
            );

            function calculateNetPurcRateSum() {

                let netTotal = 0;
                let gstTotal = 0;
                let grandTotal = 0;

                $('#inwardProductTable tbody tr').each(function () {

                    let row = $(this);

                    let netAmount = parseFloat(row.find('input[name="netPurcRate[]"]').val()) || 0;
                    let gstPercent = parseFloat(row.find('input[name="sgst[]"]').val()) || 0;

                    // GST amount for this row
                    let gstAmount = (netAmount * gstPercent) / 100;

                    netTotal += netAmount;
                    gstTotal += gstAmount;
                });

                grandTotal = netTotal + gstTotal;

                console.log("Net Total:", netTotal);
                console.log("GST Total:", gstTotal);
                console.log("Grand Total:", grandTotal);

                // Set values in inputs
                $("#inward_acc_net_amount").val(netTotal.toFixed(2));
                $("#inward_acc_gst_amount").val(gstTotal.toFixed(2));
                $("#inward_acc_amt_with_gst").val(grandTotal.toFixed(2));

                return {
                    netTotal,
                    gstTotal,
                    grandTotal
                };
            }



            // Aside Bar Close
            $('#appContent').addClass('closed-sidebar');
            $('#appContent .app-header .hamburger').addClass('is-active');

            //

            window.createRow = function() {
                let sl = $('#inwardProductTable tbody tr').length + 1;

                let deleteBtn = '';
                if (sl > 1) {
                    deleteBtn = `
                        <button type="button" class="btn btn-outline-danger deleteRow">
                            <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="trash" loading="lazy">
                        </button>`;
                }

                let row = `
            <tr data-sl="${sl}">
                <td class="text-center">${sl}</td>
                <td><input type="text" name="item[]" class="form-control w-auto item"><div class="dropdown-suggestions"></div><input type="hidden" name="itemid[]"></td>
                <td><input type="text" name="designNo[]" class="form-control w-min designno"><div class="dropdown-designNo"></div><input type="hidden" name="designid[]"></td>
                <td class="text-start"><select name="colorInwardIds[0][]" class="form-control colorSelectInward row-${sl}" multiple></select></td>
                <td class="text-start"><select name="sizeInwardIds[0][]" class="form-control sizeSelectInward row-${sl}" multiple></select></td>

                <td><input type="text" name="qty[]" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"></td>
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
                        data: function(params) {
                            return { colorSearch: params.term };
                        },
                        processResults: function(data) {
                            return {
                                results: data.designWithColorData.map(c => ({ id: c.id, text: c.name }))
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
                        data: function(params) {
                            return { sizeSearch: params.term }; // backend me sizeSearch parameter handle karna
                        },
                        processResults: function(data) {
                            return {
                                results: data.designWithSizeData.map(s => ({ id: s.id, text: s.name }))
                            };
                        }
                    }
                });

            }

            // Default row on page load
            createRow();

            //  Add Table Row
            $(document).on('click','#create-modal-btn',function() {
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
                $(this).closest('tr').remove();
                updateSL();
                calculateNetPurcRateSum();
            });

            // Item Name Find

            $(document).on('keyup', '.item', function(e) {
                const input = $(this);
                const itemName = input.val().trim();
                const dropdown = input.siblings('.dropdown-suggestions');

                // Keyboard navigation
                const items = dropdown.find('li');
                let index = items.index(dropdown.find('li.active'));

                if(e.key === 'ArrowDown') {
                    e.preventDefault();
                    items.removeClass('active');
                    index = (index + 1) % items.length;
                    items.eq(index).addClass('active')[0].scrollIntoView({ block: "nearest" });
                    return;
                } else if(e.key === 'ArrowUp') {
                    e.preventDefault();
                    items.removeClass('active');
                    index = (index - 1 + items.length) % items.length;
                    items.eq(index).addClass('active')[0].scrollIntoView({ block: "nearest" });
                    return;
                } else if(e.key === 'Enter') {
                    e.preventDefault();
                    if(index >= 0) {
                        items.eq(index).trigger('click'); // select active item
                        dropdown.hide(); // hide dropdown
                    }
                    return;
                }

                if(itemName.length < 2) {
                    dropdown.html('').hide();
                    return;
                }

                // AJAX call
                $.ajax({
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    type: "GET",
                    data: { itemSearch: itemName },
                    success: function(response) {
                        let html = '<ul class="list-group">';
                        response.designWithItemData.forEach((item, idx) => {
                            html += `<li class="list-group-item suggestion-item ${idx===0?'active':''}"
                    data-name="${item.products.name}"
                    data-itemid="${item.products.id}"
                    data-designno="${item.design_number}"
                    data-designid="${item.id}"
                    data-qty="${item.quantity}"
                    data-purcrate="${item.buy_price}"
                    data-mrp="${item.mrp}"
                    data-markup="${item.mark_up}"
                    data-markdown="${item.mark_down}"
                    data-disc="${item.discount_percentage}"
                    data-amount="${item.price}"
                    data-taxcode="${item.products.hsn_master?.hsn_code || ''}"
                    data-taxcodeid="${item.products.hsn_master?.id || ''}"
                    data-sgstid="${item.products.vat_tax?.id || ''}"
                    data-sgst="${item.products.vat_tax?.percentage || ''}"
                >${item.products.name}</li>`;
                        });
                        html += '</ul>';
                        dropdown.html(html).show();

                        // Pehla item auto focus
                        dropdown.find('li.active')[0]?.scrollIntoView({ block: "nearest" });
                    }
                });
            });

            $(document).on('click', '.suggestion-item', function() {
                const name = $(this).data('name');
                const itemid = $(this).data('itemid');
                const designNo = $(this).data('designno');
                const designid = $(this).data('designid');
                const qty = $(this).data('qty');
                const purcrate = $(this).data('purcrate');
                const mrp = $(this).data('mrp');
                const markup = $(this).data('markup');
                const markdown = $(this).data('markdown');
                const disc = $(this).data('disc');
                const amount = $(this).data('amount');
                const taxcode = $(this).data('taxcode');
                const taxcodeid = $(this).data('taxcodeid');
                const sgst = $(this).data('sgst');
                const sgstid = $(this).data('sgstid');


                const parentInput = $(this).closest('td').find('.item');

                parentInput.val(name);
                parentInput.closest('tr').find('input[name="itemid[]"]').val(itemid);
                parentInput.closest('tr').find('input[name="designNo[]"]').val(designNo);
                parentInput.closest('tr').find('input[name="designid[]"]').val(designid);
                parentInput.closest('tr').find('input[name="qty[]"]').val(qty);
                parentInput.closest('tr').find('input[name="purcRate[]"]').val(purcrate);
                parentInput.closest('tr').find('input[name="mrp[]"]').val(mrp);
                parentInput.closest('tr').find('input[name="mark_up[]"]').val(markup);
                parentInput.closest('tr').find('input[name="mark_down[]"]').val(markdown);
                parentInput.closest('tr').find('input[name="disc[]"]').val(disc);
                parentInput.closest('tr').find('input[name="amount[]"]').val(amount);
                parentInput.closest('tr').find('input[name="netPurcRate[]"]').val(mrp);
                parentInput.closest('tr').find('input[name="taxCode[]"]').val(taxcode);
                parentInput.closest('tr').find('input[name="taxCodeId[]"]').val(taxcodeid);
                parentInput.closest('tr').find('input[name="sgst[]"]').val(sgst);
                parentInput.closest('tr').find('input[name="sgstId[]"]').val(sgstid);


                $(this).parent().hide(); // dropdown hide
            });

            $(document).on('focusin', function(e) {
                const isTaxInput = $(e.target).hasClass('item');

                $('.dropdown-suggestions').each(function() {
                    const dropdown = $(this);
                    const input = dropdown.siblings('.item');

                    if (!$(e.target).is(input)) {
                        dropdown.hide();
                    }
                });
            });

            $(document).on('keydown', '.item', function(e) {
                if (e.key === 'Tab') {
                    const dropdown = $(this).siblings('.dropdown-suggestions');
                    setTimeout(() => dropdown.hide(), 50);
                }
            });

            $(document).on('blur', '.item', function() {
                const $input = $(this);
                const dropdown = $input.siblings('.dropdown-suggestions');
                const typedValue = $input.val().trim();

                const row = $input.closest('tr');

                // check if typed value matches any li in dropdown
                const match = dropdown.find('li').filter(function() {
                    return $(this).text().trim() === typedValue;
                });

                if (typedValue && match.length === 0) {
                    // No match → clear all related fields
                    row.find('input[name="itemid[]"], input[name="designNo[]"], input[name="designid[]"], input[name="qty[]"], input[name="purcRate[]"], input[name="mrp[]"], input[name="mark_up[]"], input[name="mark_down[]"], input[name="disc[]"], input[name="amount[]"], input[name="netPurcRate[]"], input[name="taxCode[]"], input[name="taxCodeId[]"], input[name="sgst[]"], input[name="sgstId[]"]').val('');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Item',
                        text: 'This item does not exist. You can add it or close this alert.',
                        showCancelButton: true,
                        confirmButtonText: 'Add',
                        cancelButtonText: 'Close',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // // Redirect to add item page (ya open modal)
                            // window.location.href = "/shop/items/create"; // change your route
                            showCustomLoader('center');
                            new bootstrap.Modal(document.getElementById('design-master-modal'), {
                                backdrop: false
                            }).show();
                            $('#itemname,#account_master').select2({
                                // theme: 'bootstrap-5',
                                width: '100%',
                                dropdownParent: $('#design-master-modal')
                            });
                            modelDesignMasterDataLoad();
                        } else {
                            // Close → focus back to input
                            $input.focus();
                        }
                    });
                }
            });


            // Design Number Find
            $(document).on('keyup', '.designno', function(e) {
                const input = $(this);
                const designNumber = input.val().trim();
                const dropdown = input.siblings('.dropdown-designNo');

                console.log(designNumber)

                // Keyboard navigation
                const items = dropdown.find('li');
                let index = items.index(dropdown.find('li.active'));

                if(e.key === 'ArrowDown') {
                    e.preventDefault();
                    items.removeClass('active');
                    index = (index + 1) % items.length;
                    items.eq(index).addClass('active')[0].scrollIntoView({ block: "nearest" });
                    return;
                } else if(e.key === 'ArrowUp') {
                    e.preventDefault();
                    items.removeClass('active');
                    index = (index - 1 + items.length) % items.length;
                    items.eq(index).addClass('active')[0].scrollIntoView({ block: "nearest" });
                    return;
                } else if(e.key === 'Enter') {
                    e.preventDefault();
                    if(index >= 0) {
                        items.eq(index).trigger('click'); // select active item
                        dropdown.hide(); // hide dropdown
                    }
                    return;
                }

                if(designNumber.length < 2) {
                    dropdown.html('').hide();
                    return;
                }

                // AJAX call
                $.ajax({
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    type: "GET",
                    data: { designNoSearch: designNumber },
                    success: function(response) {
                        let html = '<ul class="list-group">';
                        response.designWithItemData.forEach((item, idx) => {
                            html += `<li class="list-group-item suggestion-designNo ${idx===0?'active':''}"
                    data-name="${item.products.name}"
                    data-itemid="${item.products.id}"
                    data-designno="${item.design_number}"
                    data-designid="${item.id}"
                    data-qty="${item.quantity}"
                    data-purcrate="${item.buy_price}"
                    data-mrp="${item.mrp}"
                    data-markup="${item.mark_up}"
                    data-markdown="${item.mark_down}"
                    data-disc="${item.discount_percentage}"
                    data-amount="${item.price}"
                    data-taxcode="${item.products.hsn_master?.hsn_code || ''}"
                    data-taxcodeid="${item.products.hsn_master?.id || ''}"
                    data-sgstid="${item.products.vat_tax?.id || ''}"
                    data-sgst="${item.products.vat_tax?.percentage || ''}"
                >${item.design_number}</li>`;
                        });
                        html += '</ul>';
                        dropdown.html(html).show();


                        // Pehla item auto focus
                        dropdown.find('li.active')[0]?.scrollIntoView({ block: "nearest" });
                    }
                });
            });

            $(document).on('click', '.suggestion-designNo', function() {
                const name = $(this).data('name');
                const itemid = $(this).data('itemid');
                const designNo = $(this).data('designno');
                const designid = $(this).data('designid');
                const qty = $(this).data('qty');
                const purcrate = $(this).data('purcrate');
                const mrp = $(this).data('mrp');
                const markup = $(this).data('markup');
                const markdown = $(this).data('markdown');
                const disc = $(this).data('disc');
                const amount = $(this).data('amount');
                const taxcode = $(this).data('taxcode');
                const taxcodeid = $(this).data('taxcodeid');
                const sgst = $(this).data('sgst');
                const sgstid = $(this).data('sgstid');


                const parentInput = $(this).closest('td').find('.designno');

                parentInput.val(designNo);
                parentInput.closest('tr').find('input[name="item[]"]').val(name);
                parentInput.closest('tr').find('input[name="itemid[]"]').val(itemid);
                parentInput.closest('tr').find('input[name="designid[]"]').val(designid);
                parentInput.closest('tr').find('input[name="qty[]"]').val(qty);
                parentInput.closest('tr').find('input[name="purcRate[]"]').val(purcrate);
                parentInput.closest('tr').find('input[name="mrp[]"]').val(mrp);
                parentInput.closest('tr').find('input[name="mark_up[]"]').val(markup);
                parentInput.closest('tr').find('input[name="mark_down[]"]').val(markdown);
                parentInput.closest('tr').find('input[name="disc[]"]').val(disc);
                parentInput.closest('tr').find('input[name="amount[]"]').val(amount);
                parentInput.closest('tr').find('input[name="netPurcRate[]"]').val(mrp);
                parentInput.closest('tr').find('input[name="taxCode[]"]').val(taxcode);
                parentInput.closest('tr').find('input[name="taxCodeId[]"]').val(taxcodeid);
                parentInput.closest('tr').find('input[name="sgst[]"]').val(sgst);
                parentInput.closest('tr').find('input[name="sgstId[]"]').val(sgstid);

                $(this).parent().hide(); // dropdown hide
            });

            $(document).on('focusin', function(e) {
                const isTaxInput = $(e.target).hasClass('designno');

                $('.dropdown-designNo').each(function() {
                    const dropdown = $(this);
                    const input = dropdown.siblings('.designno');

                    if (!$(e.target).is(input)) {
                        dropdown.hide();
                    }
                });
            });

            $(document).on('keydown', '.designno', function(e) {
                if (e.key === 'Tab') {
                    const dropdown = $(this).siblings('.dropdown-designNo');
                    setTimeout(() => dropdown.hide(), 50);
                }
            });


            $(document).on('blur', '.designno', function() {
                const $input = $(this);
                const dropdown = $input.siblings('.dropdown-designNo');
                const typedValue = $input.val().trim();

                const row = $input.closest('tr');

                // check if typed value matches any li in dropdown
                const match = dropdown.find('li').filter(function() {
                    return $(this).text().trim() === typedValue;
                });

                if (typedValue && match.length === 0) {
                    // No match → clear all related fields
                    row.find('input[name="itemid[]"], input[name="item[]"], input[name="designid[]"], input[name="qty[]"], input[name="purcRate[]"], input[name="mrp[]"], input[name="mark_up[]"], input[name="mark_down[]"], input[name="disc[]"], input[name="amount[]"], input[name="netPurcRate[]"], input[name="taxCode[]"], input[name="taxCodeId[]"], input[name="sgst[]"], input[name="sgstId[]"]').val('');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Item',
                        text: 'This item does not exist. You can add it or close this alert.',
                        showCancelButton: true,
                        confirmButtonText: 'Add',
                        cancelButtonText: 'Close',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // // Redirect to add item page (ya open modal)
                            // window.location.href = "/shop/items/create"; // change your route
                            showCustomLoader('center');
                            new bootstrap.Modal(document.getElementById('design-master-modal'), {
                                backdrop: false
                            }).show();
                            $('#itemname,#account_master').select2({
                                // theme: 'bootstrap-5',
                                width: '100%',
                                dropdownParent: $('#design-master-modal')
                            });
                            modelDesignMasterDataLoad();
                        } else {
                            // Close → focus back to input
                            $input.focus();
                        }
                    });
                }
            });
            // Tax Code Find

            $(document).on('keyup', '.taxcode', function(e) {
                const input = $(this);
                const taxCode = input.val().trim();
                const dropdown = input.siblings('.dropdown-taxCode');

                // Keyboard navigation
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
                        items.eq(index).trigger('click'); // select active item
                        dropdown.hide(); // hide dropdown
                    }
                    return;
                }

                if(taxCode.length < 2) {
                    dropdown.html('').hide();
                    return;
                }

                // AJAX request
                $.ajax({
                    url: "{{ route('shop.designMaster.designDataGet') }}",
                    type: "GET",
                    data: { taxCodeSearch: taxCode },
                    success: function(response) {
                        let html = '<ul class="list-group">';
                        response.designWithtaxCodeData.forEach((code, idx) => {
                            html += `<li class="list-group-item suggestion-taxCode ${idx===0?'active':''}"
                    data-hsn-id="${code.id}"
                    data-hsn-code="${code.hsn_code}"
                    data-vattax-id="${code.vattax?.id}"
                    data-vattax-percentage="${code.vattax?.percentage}"
                >${code.hsn_code}</li>`;
                        });
                        html += '</ul>';
                        dropdown.html(html).show();

                        // Pehla item auto focus
                        dropdown.find('li.active')[0]?.scrollIntoView({ block: "nearest" });
                    }
                });
            });

            $(document).on('click', '.suggestion-taxCode', function() {
                const hsnCode = $(this).data('hsn-code');
                const hsnId = $(this).data('hsn-id');
                const vatTaxId = $(this).data('vattax-id');
                const vatTaxPercentage = $(this).data('vattax-percentage');

                const parentInput = $(this).closest('td').find('.taxcode');

                parentInput.val(hsnCode);
                parentInput.closest('tr').find('input[name="taxCodeId[]"]').val(hsnId);
                parentInput.closest('tr').find('input[name="sgst[]"]').val(vatTaxPercentage);
                parentInput.closest('tr').find('input[name="sgstId[]"]').val(vatTaxId);
                // parentInput.closest('tr').find('input[name="purcRate[]"]').val(purcrate);
                // parentInput.closest('tr').find('input[name="mrp[]"]').val(mrp);
                // parentInput.closest('tr').find('input[name="mark_up[]"]').val(markup);
                // parentInput.closest('tr').find('input[name="mark_down[]"]').val(markdown);
                // parentInput.closest('tr').find('input[name="disc[]"]').val(disc);
                // parentInput.closest('tr').find('input[name="amount[]"]').val(amount);
                // parentInput.closest('tr').find('input[name="netPurcRate[]"]').val(mrp);
                // parentInput.closest('tr').find('input[name="taxCode[]"]').val(taxcode);
                // parentInput.closest('tr').find('input[name="taxCodeId[]"]').val(taxcodeid);
                // parentInput.closest('tr').find('input[name="sgst[]"]').val(sgst);
                $(this).parent().hide(); // dropdown hide
            });

            $(document).on('focusin', function(e) {
                const isTaxInput = $(e.target).hasClass('taxcode');

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

            //Markup And Markdown Get
            $(document).on('input keyup', '.buy_price, .price, .discount_percentage', function () {
                calculateRowAll($(this).closest('tr'));
            });

            function calculateRowAll(row) {
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


                row.find('input[name="mrp[]"]').val(mrp.toFixed(2));
                row.find('input[name="netPurcRate[]"]').val(mrp.toFixed(2));

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

                   inward_date: $('#inward_date').val(),
                   inward_day_name: $('#inward_day_name').val(),
                   inward_time: $('#inward_time').val(),
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

               $.ajaxSetup({
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   }
               });
               $.ajax({
                   url: "{{ route('shop.inwardProduct.store') }}",
                   type: "POST",
                   data: {rows:formData,invoiceData:headerData},
                   success: function (res) {
                       if (res.status) {
                           $('#inwardProductTable tbody').html('');
                           window.createRow();
                           toastr.success(res.message);
                       }else{
                           toastr.error(res.message);
                       }
                       submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');
                   },
                   error: function(xhr) {
                       console.error(xhr);
                       submitButton.prop('disabled', false).html(originalButtonText).text('Submit').addClass('px-5');
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
           $('#inward_party_code').select2({
               dropdownParent: $('#itemForm'),
               placeholder: "Select Account Master",
               allowClear: true,
               ajax: {
                   url: '{{ route('shop.designMaster.modalData') }}',
                   dataType: 'json',
                   delay: 250,
                   data: function (params) {
                       return {searchAccountMaster: params.term || ''};
                   },
                   processResults: function (data) {
                       return {
                           results: data.accountMasters.map(function (accountMaster) {
                               return {
                                   id: accountMaster.id,
                                   text: accountMaster.accountshortcode,
                                   accName: accountMaster.accountName,
                                   accLimit: accountMaster.other_info_act_limit,
                               };
                           })
                       };
                   },
                   cache: true
               },
               minimumInputLength: 0
           });

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

           fillDayAndTime();

           $('#inward_date').on('change', function () {
               fillDayAndTime();
           });

       });



    </script>

    {{-- Purchase Code And Name Search--}}
    <script>
        $(document).ready(function (){
            $('#inward_acc_purchaser').select2({
                dropdownParent: $('#itemForm'),
                placeholder: "Select Purchaser",
                allowClear: true,
                ajax: {
                    url: '{{ route('shop.designMaster.modalData') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {searchAccountMaster: params.term || ''};
                    },
                    processResults: function (data) {
                        return {
                            results: data.accountMasters.map(function (accountMaster) {
                                return {
                                    id: accountMaster.id,
                                    text: accountMaster.accountshortcode + ' - ' + accountMaster.accountName,
                                    accName: accountMaster.accountName
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