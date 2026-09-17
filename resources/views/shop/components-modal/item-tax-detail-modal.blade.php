{{-- Item Tax Detail Modal (styled like Inward Products Table) --}}
<div class="modal fade" id="itemTaxDetailModal" tabindex="-1" aria-labelledby="itemTaxDetailModalLabel" aria-hidden="true" style="backdrop-filter: blur(4px); z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px; overflow: hidden; background: #ffffff;">
            
            {{-- Header styled to match application theme --}}
            <div class="modal-header py-2.5 px-4 bg-white border-bottom align-items-center" style="border-color: #e2e8f0 !important;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">
                        <i class="fa-solid fa-percent" style="font-size: 13px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold text-dark" style="font-size: 15px;" id="itemTaxDetailModalLabel">{{ __('Item Tax Breakdown Details') }}</h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
            </div>

            <div class="modal-body p-3 bg-white">
                {{-- Item Summary Info Card (matching Inward Header Card) --}}
                <div class="card mb-3 border shadow-2xs" style="border-color: #e2e8f0; border-radius: 8px; background: #ffffff;">
                    <div class="card-body p-2.5 px-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <span class="text-secondary text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.3px;">{{ __('Item Name') }}</span>
                                <span class="fw-bold text-dark text-truncate d-block" style="font-size: 13.5px;" id="taxModalItemName">-</span>
                            </div>
                            <div class="col-md-2">
                                <span class="text-secondary text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.3px;">{{ __('Design No') }}</span>
                                <span class="fw-bold text-dark font-monospace" style="font-size: 13.5px;" id="taxModalDesignNo">-</span>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="text-secondary text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.3px;">{{ __('Qty') }}</span>
                                <span class="fw-bold text-dark font-monospace" style="font-size: 13.5px;" id="taxModalQty">0</span>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="text-secondary text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.3px;">{{ __('Net Purc Price') }}</span>
                                <span class="fw-bold text-primary font-monospace" style="font-size: 13.5px;" id="taxModalNetPurcPrice">₹0.00</span>
                            </div>
                            <div class="col-md-3 text-end">
                                <span class="text-secondary text-uppercase d-block" style="font-size: 10px; font-weight: 700; letter-spacing: 0.3px;">{{ __('Net Line Amount') }}</span>
                                <span class="fw-bold text-success font-monospace" style="font-size: 13.5px;" id="taxModalNetPurcRate">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status & Location Bar --}}
                <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                    <div id="taxModalSupplyTypeBadge" class="d-flex align-items-center gap-2">
                        {{-- Dynamically populated badge --}}
                    </div>
                    <div class="text-secondary" style="font-size: 12px;">
                        <span>{{ __('Party State') }}: <strong id="taxModalPartyState" class="text-dark">-</strong></span>
                        <span class="mx-2 text-muted">|</span>
                        <span>{{ __('Shop State') }}: <strong id="taxModalShopState" class="text-dark">-</strong></span>
                    </div>
                </div>

                {{-- Detailed Tax Table matching Inward Products Table styling --}}
                <div class="card mb-2 border shadow-2xs" style="border-color: #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle" id="itemTaxModalGridTable" style="font-size: 12.5px; border-collapse: separate; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr style="background-color: #f8fafc;">
                                    <th class="py-2 px-3 text-start text-uppercase" style="font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.3px; border-bottom: 2px solid #cbd5e1; border-top: none;">{{ __('Tax Code') }}</th>
                                    <th class="py-2 px-2 text-end text-uppercase" style="font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.3px; border-bottom: 2px solid #cbd5e1; border-top: none;">{{ __('SGST (%)') }}</th>
                                    <th class="py-2 px-2 text-end text-uppercase" style="font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.3px; border-bottom: 2px solid #cbd5e1; border-top: none;">{{ __('SGST Amt') }}</th>
                                    <th class="py-2 px-2 text-end text-uppercase" style="font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.3px; border-bottom: 2px solid #cbd5e1; border-top: none;">{{ __('CGST (%)') }}</th>
                                    <th class="py-2 px-2 text-end text-uppercase" style="font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.3px; border-bottom: 2px solid #cbd5e1; border-top: none;">{{ __('CGST Amt') }}</th>
                                    <th class="py-2 px-2 text-end text-uppercase" style="font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.3px; border-bottom: 2px solid #cbd5e1; border-top: none;">{{ __('IGST (%)') }}</th>
                                    <th class="py-2 px-2 text-end text-uppercase" style="font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.3px; border-bottom: 2px solid #cbd5e1; border-top: none;">{{ __('IGST Amt') }}</th>
                                    <th class="py-2 px-3 text-end text-uppercase" style="font-size: 11px; font-weight: 700; color: #1e293b; background-color: #f1f5f9; letter-spacing: 0.3px; border-bottom: 2px solid #cbd5e1; border-top: none;">{{ __('With Tax Amount') }}</th>
                                    <th class="py-2 px-3 text-end text-uppercase" style="font-size: 11px; font-weight: 700; color: #1d4ed8; background-color: #eff6ff; letter-spacing: 0.3px; border-bottom: 2px solid #93c5fd; border-top: none;">{{ __('Per Item CostRate') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background-color: #ffffff;">
                                    <td class="py-2.5 px-3 text-start fw-semibold text-dark" id="taxModalTaxCode" style="border-color: #e2e8f0;">GST 5 %</td>
                                    <td class="py-2.5 px-2 text-end font-monospace" id="taxModalSgstPct" style="border-color: #e2e8f0;">2.50</td>
                                    <td class="py-2.5 px-2 text-end font-monospace fw-semibold text-danger" id="taxModalSgstAmt" style="border-color: #e2e8f0;">0.00</td>
                                    <td class="py-2.5 px-2 text-end font-monospace" id="taxModalCgstPct" style="border-color: #e2e8f0;">2.50</td>
                                    <td class="py-2.5 px-2 text-end font-monospace fw-semibold text-danger" id="taxModalCgstAmt" style="border-color: #e2e8f0;">0.00</td>
                                    <td class="py-2.5 px-2 text-end font-monospace" id="taxModalIgstPct" style="border-color: #e2e8f0;">0.00</td>
                                    <td class="py-2.5 px-2 text-end font-monospace fw-semibold text-danger" id="taxModalIgstAmt" style="border-color: #e2e8f0;">0.00</td>
                                    <td class="py-2.5 px-3 text-end font-monospace fw-bold text-dark" style="background-color: #f8fafc; border-color: #e2e8f0; font-size: 13.5px;" id="taxModalWithTaxAmount">0.00</td>
                                    <td class="py-2.5 px-3 text-end font-monospace fw-bold text-primary" style="background-color: #f0fdf4; border-color: #bbf7d0; font-size: 13.5px; color: #15803d !important;" id="taxModalPerItemCostRate">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Calculation Basis Note --}}
                <div class="p-2 px-3 rounded bg-light border text-muted d-flex align-items-center" style="font-size: 11.5px; border-color: #e2e8f0 !important;">
                    <i class="fa-solid fa-circle-info text-primary me-2" style="font-size: 13px;"></i>
                    <div>
                        <strong class="text-dark">{{ __('Calculation Basis') }}:</strong>
                        <span id="taxModalCalculationNote" class="ms-1">
                            {{ __('Intra-State: GST is split 50/50 between SGST & CGST. Per Item CostRate = With Tax Amount / Quantity.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer py-2 px-4 bg-white border-top d-flex justify-content-end" style="border-color: #e2e8f0 !important;">
                <button type="button" class="btn btn-secondary px-4 btn-sm" data-bs-dismiss="modal" style="font-size: 12px; font-weight: 500;">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
