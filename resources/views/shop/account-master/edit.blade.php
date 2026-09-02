@extends('layouts.app')
@section('header-title', __('Account Master'))
@section('content')
    <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
        <h4>
            {{__('Update Account Master')}}
        </h4>
    </div>
    <form id="formData" action="{{route('shop.accountMaster.update',$accountMaster->id)}}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        {{-- Account Information Card 1 --}}
        <div class="card mt-4">
            <div class="card-header fw-bold">
                {{ __('Account Information') }}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-6">
                                <x-input type="text" name="accountName" label="Account Name" placeholder="Enter Account Name" value="{{ old('accountName',$accountMaster->accountName) }}" required="true"/>
                            </div>
                            <div class="col-md-6 col-lg-6">
                                <x-select label="Account" name="account_id" required="true">
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', $accountMaster->account_id ?? '') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->code }})
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="col-md-12 col-lg-12">
                                <x-select label="Region / Area" name="cities_id" id="cities_id" required="true">
                                    @if(isset($accountMaster) && $accountMaster->regionArea)
                                        <option value="{{ $accountMaster->regionArea->id }}" selected>
                                            {{ $accountMaster->regionArea->name }}
                                        </option>
                                    @endif
                                </x-select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12 mt-4 mt-lg-0">
                        <div class="row g-3">

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>
                                            {{ __('Short Code') }}
                                            <span class="text-danger">*</span>
                                        </span>
                                    </div>
                                </label>
                                <div class="input-group flex-nowrap">
                                    <input type="text" class="form-control disabledCls @error('accountshortcode') is-invalid @enderror" name="accountshortcode" placeholder="Short Code" id="shortCode" value="{{ old('accountshortcode',$accountMaster->accountshortcode) }}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" required="true" readonly>

{{--                                    <button class="btn btn-secondary" type="button" id="generateShortCode" onclick="generateCode()" data-toggle="tooltip" data-placement="top" title="Generate Short Code">--}}
{{--                                        <i class="bi bi-arrow-repeat"></i>--}}
{{--                                    </button>--}}
                                </div>
                                @error('accountshortcode')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                    <span>{{ __('Agent Comm(%)') }} <span class="text-danger">*</span> </span>
                                </label>

                                <input type="text" id="agentcomm" name="agentcomm"
                                       placeholder="Enter Agent Comm(%)"
                                       class="form-control @error('agentcomm') is-invalid @enderror" value="{{ old('agentcomm',$accountMaster->agentcomm) }}"
                                       oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                       required="true"
                                />
                                @error('agentcomm')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror

                            </div>
                            <div class="col-md-6 col-lg-4">
                                <x-input type="text" name="agentcommremark" label="Remark" placeholder="Enter Remark" value="{{ old('agentcommremark',$accountMaster->agentcommremark) }}" />
                            </div>

                            <div class="col-md-6 col-lg-12">
                                <x-input type="text" name="referenceby" label="Reference By" placeholder="Reference By" value="{{ old('referenceby',$accountMaster->referenceby) }}" />
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Address & Contact Information Card 2 And 3 --}}
        <div class="col-md-12 col-lg-12">
            <div class="row">
                {{-- Contact Address --}}
                <div class="col-12 col-md-6">
                    <div class="card mt-4">
                        <div class="card-header fw-bold">
                            {{ __('Contact Address') }}
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-6">
                                    <x-input type="text" name="contperson" label="Contact Person" placeholder="Enter Contact Person" value="{{ old('contperson',$accountMaster->contperson) }}" required="true"/>
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Pincode') }} <span class="text-danger">*</span> </span>
                                    </label>

                                    <input type="text" id="contpincode" name="contpincode"
                                           placeholder="Enter Pincode"
                                           class="form-control @error('contpincode') is-invalid @enderror" value="{{ old('contpincode',$accountMaster->contpincode) }}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);"
                                           required="true"
                                    />
                                    @error('contpincode')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-lg-12">
                                    <label for="contaddress" class="form-label">
                                        {{ __('Address') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="contaddress" class="form-control @error('contaddress') is-invalid @enderror" rows="1" placeholder="Enter Contact Address" required="true">{{ old('contaddress',$accountMaster->contaddress) }}</textarea>
                                    @error('contaddress')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                {{-- Country --}}
                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Country" name="country_id" required="true">
                                        @if($accountMaster->country)
                                            <option value="{{ $accountMaster->country->id }}" selected>
                                                {{ $accountMaster->country->name }}
                                            </option>
                                        @endif
                                    </x-select>
                                </div>

                                {{-- State --}}
                                <div class="col-md-12 col-lg-4">
                                    <x-select label="State" name="state_id" required="true">
                                        <option value="">{{ __('Select a state') }}</option>
                                    </x-select>
                                </div>

                                {{-- City --}}
                                <div class="col-md-12 col-lg-4">
                                    <x-select label="City" name="city_id" required="true">
                                        <option value="">{{ __('Select a city') }}</option>
                                    </x-select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Information --}}
                <div class="col-12 col-md-6">
                    <div class="card mt-4">
                        <div class="card-header fw-bold">
                            {{ __('Contact Information') }}
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-6">

                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Mobile No. (1)') }} <span class="text-danger">*</span> </span>
                                    </label>

                                    <input type="text" id="cont_info_mobile1" name="cont_info_mobile1"
                                           placeholder="Enter Mobile No. (1)"
                                           class="form-control @error('cont_info_mobile1') is-invalid @enderror" value="{{ old('cont_info_mobile1',$accountMaster->cont_info_mobile1) }}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);"
                                           maxlength="10" required="true"/>

                                    @error('cont_info_mobile1')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Mobile No. (2)') }}</span>
                                    </label>

                                    <input type="text" id="cont_info_mobile2" name="cont_info_mobile2"
                                           placeholder="Enter Mobile No. (2)"
                                           class="form-control @error('cont_info_mobile2') is-invalid @enderror" value="{{ old('cont_info_mobile2',$accountMaster->cont_info_mobile2) }}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);"
                                           maxlength="10"/>

                                    @error('cont_info_mobile2')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Phone No.') }}</span>
                                    </label>

                                    <input type="text" id="cont_info_phone" name="cont_info_phone"
                                           placeholder="Enter Phone No."
                                           class="form-control @error('cont_info_phone') is-invalid @enderror" value="{{ old('cont_info_phone',$accountMaster->cont_info_phone) }}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);"
                                           maxlength="13"/>

                                    @error('cont_info_phone')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <div class="d-block gap-3 mt-0 mt-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" id="cont_info_send_sms" name="cont_info_send_sms" class="form-check-input" {{ old('cont_info_send_sms',$accountMaster->cont_info_send_sms) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cont_info_send_sms">{{ __('Send SMS') }}</label>
                                        </div>

                                        <div class="form-check">
                                            <input type="checkbox" id="cont_info_dndactivate" name="cont_info_dndactivate" class="form-check-input" {{ old('cont_info_dndactivate',$accountMaster->cont_info_dndactivate) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cont_info_dndactivate">{{ __('DND Activate (Do not send SMS)') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <x-input type="email" name="cont_info_email" label="Email" placeholder="Enter Email Address" value="{{ old('cont_info_email',$accountMaster->cont_info_email) }}"/>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <x-input type="url" name="cont_info_website_url" label="Website URL" placeholder="Enter Website URL" value="{{ old('cont_info_website_url',$accountMaster->cont_info_website_url) }}"/>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <x-input type="date" name="cont_info_birth_date" label="Birth Date" value="{{ old('cont_info_birth_date',$accountMaster->cont_info_birth_date) }}"/>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Tax Information & Other Information & Bank Information Card 4 And 5 And 6 --}}
        <div class="col-md-12 col-lg-12">
            <div class="row">
                {{-- Tax Information --}}
                <div class="col-12 col-md-6">
                    <div class="card mt-4 mb-4">
                        <div class="card-header fw-bold">
                            {{ __('Tax Information') }}
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-6">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>
                                            {{ __('GST No.') }} <span class="text-danger">*</span>
                                        </span>
                                    </label>
                                    <div class="input-group flex-nowrap">
                                        <input type="text" class="form-control @error('tax_info_gst_no') is-invalid @enderror" name="tax_info_gst_no" placeholder="Enter GST No." id="tax_info_gst_no" value="{{ old('tax_info_gst_no',$accountMaster->tax_info_gst_no) }}" pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$" title="Enter a valid GST Number (e.g., 22AAAAA0000A1Z5)" required="true" maxlength="15">

                                        <button class="btn btn-secondary" type="button" id="checkGstButton" data-toggle="tooltip" data-placement="top" title="Check GST No.">
                                            {{ __('Check') }}
                                        </button>
                                    </div>
                                    @error('tax_info_gst_no')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <x-input type="date" name="tax_info_gst_reg_date" label="GST Reg. Date" value="{{ old('tax_info_gst_reg_date',$accountMaster->tax_info_gst_reg_date) }}"/>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <x-input type="date" name="tax_info_gst_cancel_date" label="GST Cancel Date" value="{{ old('tax_info_gst_cancel_date',$accountMaster->tax_info_gst_cancel_date) }}"/>
                                </div>

                                {{-- Tax Type --}}
                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Tax Type" name="vat_tax_id">
                                        <option value="">{{ __('Select a tax type') }}</option>
                                        @foreach ($taxs as $tax)
                                            <option value="{{ $tax->id }}" {{ old('vat_tax_id',$accountMaster->vat_tax_id) == $tax->id ? 'selected' : '' }}>
                                                {{ $tax->name . " " .$tax->percentage . "%"}}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('TAN No.') }}</span>
                                    </label>

                                    <input type="text" id="tax_info_tan_no" name="tax_info_tan_no"
                                           placeholder="Enter TAN No."
                                           class="form-control @error('tax_info_tan_no') is-invalid @enderror" value="{{ old('tax_info_tan_no',$accountMaster->tax_info_tan_no) }}"
                                           pattern="[A-Z]{4}[0-9]{5}[A-Z]{1}"
                                           title="Please enter a valid PAN number (e.g., ABCD12345E)"
                                           maxlength="10"
                                    />
                                    @error('tax_info_tan_no')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>


                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('PAN No.') }} <span class="text-danger">*</span></span>
                                    </label>

                                    <input type="text" id="tax_info_pan_no" name="tax_info_pan_no"
                                           placeholder="Enter PAN No."
                                           class="form-control @error('tax_info_pan_no') is-invalid @enderror" value="{{ old('tax_info_pan_no',$accountMaster->tax_info_pan_no) }}"
                                           pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}"
                                           title="Please enter a valid PAN number (e.g., ABCDE1234F)"
                                           maxlength="10" required="true"
                                    />
                                    @error('tax_info_pan_no')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-12 col-lg-12">
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input type="checkbox" id="tax_info_tds_deduct" name="tax_info_tds_deduct" class="form-check-input" {{ old('tax_info_tds_deduct',$accountMaster->tax_info_tds_deduct) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tax_info_tds_deduct">{{ __('TDS Deduct') }}</label>
                                        </div>

                                        <div class="form-check">
                                            <input type="checkbox" id="tax_info_tcs_deduct" name="tax_info_tcs_deduct" class="form-check-input" {{ old('tax_info_tcs_deduct',$accountMaster->tax_info_tcs_deduct) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tax_info_tcs_deduct">{{ __('TCS Deduct') }}</label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Other Information Card 6 --}}
                    <div class="card mt-4 mb-4">
                        <div class="card-header fw-bold">
                            {{ __('Other Information') }}
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Discount (%)') }}</span>
                                    </label>

                                    <input type="text" id="other_info_discount" name="other_info_discount"
                                           placeholder="Enter Discount"
                                           class="form-control @error('other_info_discount') is-invalid @enderror" value="{{ old('other_info_discount',$accountMaster->other_info_discount) }}"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                    />
                                    @error('other_info_discount')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Discount Limit (%)') }}</span>
                                    </label>

                                    <input type="text" id="other_info_discount_limit" name="other_info_discount_limit"
                                           placeholder="Enter Discount Limit"
                                           class="form-control @error('other_info_discount_limit') is-invalid @enderror" value="{{ old('other_info_discount_limit',$accountMaster->other_info_discount_limit) }}"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                    />
                                    @error('other_info_discount_limit')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Cash Disc (%)') }}</span>
                                    </label>

                                    <input type="text" id="other_info_cash_disc" name="other_info_cash_disc"
                                           placeholder="Enter Cash Disc"
                                           class="form-control @error('other_info_cash_disc') is-invalid @enderror" value="{{ old('other_info_cash_disc',$accountMaster->other_info_cash_disc) }}"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                    />
                                    @error('other_info_cash_disc')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Special Disc (%)') }}</span>
                                    </label>

                                    <input type="text" id="other_info_special_disc" name="other_info_special_disc"
                                           placeholder="Enter Special Disc"
                                           class="form-control @error('other_info_special_disc') is-invalid @enderror" value="{{ old('other_info_special_disc',$accountMaster->other_info_special_disc) }}"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                    />
                                    @error('other_info_special_disc')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Bank Cash Disc (%)') }}</span>
                                    </label>

                                    <input type="text" id="other_info_bank_cs_disc" name="other_info_bank_cs_disc"
                                           placeholder="Enter Bank Cash Disc"
                                           class="form-control @error('other_info_bank_cs_disc') is-invalid @enderror" value="{{ old('other_info_bank_cs_disc',$accountMaster->other_info_bank_cs_disc) }}"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                    />
                                    @error('other_info_bank_cs_disc')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Credit Days') }}</span>
                                    </label>

                                    <input type="text" id="other_info_credit_day" name="other_info_credit_day"
                                           placeholder="Enter Credit Days"
                                           class="form-control @error('other_info_credit_day') is-invalid @enderror" value="{{ old('other_info_credit_day',$accountMaster->other_info_credit_day) }}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                    />
                                    @error('other_info_credit_day')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Account Limit') }}</span>
                                    </label>

                                    <input type="text" id="other_info_act_limit" name="other_info_act_limit"
                                           placeholder="Enter Account Limit"
                                           class="form-control @error('other_info_act_limit') is-invalid @enderror" value="{{ old('other_info_act_limit',$accountMaster->other_info_act_limit) }}"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                    />
                                    @error('other_info_act_limit')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Adjustment Type" name="other_info_adjustment_type">
                                        <option value="">{{ __('Select a Adjustment Type') }}</option>
                                    </x-select>
                                </div>

                                <div class="col-md-12 col-lg-4">
                                    <x-select label="Delivery Type" name="other_info_delivery_type">
                                        <option value="">{{ __('Select a Delivery Type') }}</option>
                                    </x-select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bank Information --}}
                <div class="col-12 col-md-6">
                    <div class="card mt-4 mb-4">
                        <div class="card-header fw-bold">
                            {{ __('Bank Information') }}
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <x-input type="text" name="bank_info_bank_name" label="Bank Name" placeholder="Enter Bank Name" value="{{ old('bank_info_bank_name',$accountMaster->bank_info_bank_name) }}" required="true"/>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('A/C No.') }} <span class="text-danger">*</span></span>
                                    </label>

                                    <input type="text" id="bank_info_ac_no" name="bank_info_ac_no"
                                           placeholder="Enter A/C No."
                                           class="form-control @error('bank_info_ac_no') is-invalid @enderror" value="{{ old('bank_info_ac_no',$accountMaster->bank_info_ac_no) }}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 20);"
                                           required="true" maxlength="20"
                                    />
                                    @error('bank_info_ac_no')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">

                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('Swift Code') }} </span>
                                    </label>

                                    <input type="text" id="bank_info_swift_code" name="bank_info_swift_code"
                                           placeholder="Enter Swift Code"
                                           pattern="^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$"
                                           title="Enter valid SWIFT Code (8 or 11 characters, e.g., SBININBBXXX)"
                                           class="form-control @error('bank_info_swift_code') is-invalid @enderror" value="{{ old('bank_info_swift_code',$accountMaster->bank_info_swift_code) }}"
                                           maxlength="11"/>
                                    @error('bank_info_swift_code')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">

                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('IFSC Code') }} <span class="text-danger">*</span> </span>
                                    </label>

                                    <input type="text" id="bank_info_ifsc_code" name="bank_info_ifsc_code"
                                           placeholder="Enter IFSC Code"
                                           class="form-control @error('bank_info_ifsc_code') is-invalid @enderror" value="{{ old('bank_info_ifsc_code',$accountMaster->bank_info_ifsc_code) }}"
                                           pattern="^[A-Z]{4}0[A-Z0-9]{6}$"
                                           title="Enter valid IFSC Code (e.g., SBIN0001234)"
                                           maxlength="11" required="true"/>
                                    @error('bank_info_ifsc_code')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <x-input type="text" name="bank_info_branch" label="Branch" placeholder="Enter Branch" value="{{ old('bank_info_branch',$accountMaster->bank_info_branch) }}" required="true"/>
                                </div>


                                <div class="col-md-6 col-lg-4">

                                    <label class="form-label d-flex align-items-center gap-2 justify-content-between">
                                        <span>{{ __('UPI ID') }}  </span>
                                    </label>

                                    <input type="text" id="bank_info_upi_id" name="bank_info_upi_id"
                                           placeholder="Enter UPI ID"
                                           class="form-control @error('bank_info_upi_id') is-invalid @enderror" value="{{ old('bank_info_upi_id',$accountMaster->bank_info_upi_id) }}"
                                           pattern="^[a-zA-Z0-9._]{2,256}@[a-zA-Z]{2,64}$"
                                           title="Enter a valid UPI ID (e.g. username@bank)"
                                           />
                                    @error('bank_info_upi_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <x-input type="text" name="bank_info_payment_name" label="Payment Name" placeholder="Enter Payment Name" value="{{ old('bank_info_payment_name',$accountMaster->bank_info_payment_name) }}" required="true"/>
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <label for="bank_info_address" class="form-label">
                                        {{ __('Address') }}
                                    </label>
                                    <textarea name="bank_info_address" class="form-control @error('bank_info_address') is-invalid @enderror" rows="1" placeholder="Enter Address">{{ old('bank_info_address',$accountMaster->bank_info_address) }}</textarea>
                                    @error('bank_info_address')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
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
            <button type="submit" class="btn btn-primary rounded py-2 px-5">
                {{ __('Update') }}
            </button>
        </div>
    </form>
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/holdon/HoldOn.min.css') }}" type="text/css" />
@endpush
@push('scripts')
    <script src="{{ asset('assets/css/holdon/HoldOn.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Region Area (Account Information)

            $('#cities_id').select2({
                placeholder: 'Select a region/area',
                ajax: {
                    url: '{{ route('shop.cities.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data) {
                        var seen = {};
                        var results = [];
                        $.each(data, function (i, city) {
                            var nameKey = city.name ? city.name.toLowerCase().trim() : city.id;
                            if (!seen[nameKey]) {
                                seen[nameKey] = true;
                                results.push({
                                    id: city.id,
                                    text: city.name
                                });
                            }
                        });
                        return { results: results };
                    },
                    cache: true
                }
            });

            // Country
            $('#country_id').select2({
                placeholder: 'Select a country',
                ajax: {
                    url: '{{ route('shop.country.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data) {
                        var seen = {};
                        var results = [];
                        $.each(data, function (i, country) {
                            var nameKey = country.name ? country.name.toLowerCase().trim() : country.id;
                            if (!seen[nameKey]) {
                                seen[nameKey] = true;
                                results.push({
                                    id: country.id,
                                    text: country.name
                                });
                            }
                        });
                        return { results: results };
                    },
                    cache: true
                }
            });

            var selectedCountry = "{{ $accountMaster->country_id ?? '' }}";
            var selectedState   = "{{ $accountMaster->state_id ?? '' }}";
            var selectedStateName = "{{ $accountMaster->state->name ?? '' }}";
            var selectedCity    = "{{ $accountMaster->city_id ?? '' }}";
            var selectedCityName  = "{{ $accountMaster->city->name ?? '' }}";

            // ================= COUNTRY -> STATES =================
            $('#country_id').on('change', function () {
                var country_id = this.value;

                $("#state_id").html('<option value="">Loading states...</option>').prop('disabled', true);
                $("#city_id").html('<option value="">Select a city</option>').prop('disabled', true);

                if (country_id) {
                    $.ajax({
                        url: "{{ route('shop.get.states') }}",
                        type: "POST",
                        data: {
                            country_id: country_id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            $("#state_id").html('<option value="">Select a state</option>');
                            var seen = {};
                            $.each(res, function (key, value) {
                                var nameKey = value.name ? value.name.toLowerCase().trim() : value.id;
                                if (!seen[nameKey]) {
                                    seen[nameKey] = true;
                                    var isSelected = (value.id == selectedState || (selectedStateName && value.name.toLowerCase() === selectedStateName.toLowerCase())) ? 'selected' : '';
                                    $("#state_id").append('<option value="'+value.id+'" '+isSelected+'>'+value.name+'</option>');
                                }
                            });
                            $("#state_id").prop('disabled', false).trigger('change');
                        },
                        error: function() {
                            $("#state_id").html('<option value="">Error loading states</option>');
                        }
                    });
                } else {
                    $("#state_id").html('<option value="">Select a state</option>').prop('disabled', true);
                    $("#city_id").html('<option value="">Select a city</option>').prop('disabled', true);
                }
            });

            // ================= STATE -> CITIES =================
            $('#state_id').on('change', function () {
                var state_id = this.value;

                $("#city_id").html('<option value="">Loading cities...</option>').prop('disabled', true);

                if (state_id) {
                    $.ajax({
                        url: "{{ route('shop.get.cities') }}",
                        type: "POST",
                        data: {
                            state_id: state_id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            $("#city_id").html('<option value="">Select a city</option>');
                            var seen = {};
                            $.each(res, function (key, value) {
                                var nameKey = value.name ? value.name.toLowerCase().trim() : value.id;
                                if (!seen[nameKey]) {
                                    seen[nameKey] = true;
                                    var isSelected = (value.id == selectedCity || (selectedCityName && value.name.toLowerCase() === selectedCityName.toLowerCase())) ? 'selected' : '';
                                    $("#city_id").append('<option value="'+value.id+'" '+isSelected+'>'+value.name+'</option>');
                                }
                            });
                            $("#city_id").prop('disabled', false);
                        },
                        error: function() {
                            $("#city_id").html('<option value="">Error loading cities</option>');
                        }
                    });
                } else {
                    $("#city_id").html('<option value="">Select a city</option>').prop('disabled', true);
                }
            });

            // ================= AUTO-TRIGGER EDIT CASE =================
            if (selectedCountry) {
                $('#country_id').val(selectedCountry).trigger('change');
            }

        });

        $('#tax_info_gst_no').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        $('#tax_info_tan_no').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        $('#tax_info_pan_no').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        $('#bank_info_payment_name').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        $('#bank_info_ifsc_code').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        $('#bank_info_swift_code').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        $('#bank_info_branch').on('keyup', function() {
            this.value = this.value.toUpperCase();
        });

        // Hold On

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
                message: 'Please wait...',
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

        document.addEventListener('DOMContentLoaded', function () {
            console.log("hello")
            // Form submit loader
            const form = document.getElementById('formData');
            if (form) {
                form.addEventListener('submit', () => showCustomLoader('center'));
            }
        });

    </script>

@endpush