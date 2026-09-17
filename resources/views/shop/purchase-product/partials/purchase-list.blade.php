<style>
    /* Clean Table styling matching Inward Product design system */
    .clean-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow-x: auto;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .clean-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    .clean-table thead tr {
        border-bottom: 1px solid #e2e8f0;
        background-color: #f8fafc;
    }
    .clean-table thead tr th {
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        padding: 12px 16px !important;
        border: none !important;
        text-align: left;
    }
    .clean-table tbody tr {
        border-bottom: 1px solid #f1f5f9 !important;
        transition: background-color 0.15s ease !important;
    }
    .clean-table tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .clean-table tbody tr td {
        padding: 12px 16px !important;
        vertical-align: middle !important;
        color: #1e293b !important;
        font-size: 13px !important;
        border: none !important;
    }
</style>

<div class="clean-table-card">
    <table class="clean-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">{{ __('SL') }}</th>
                <th class="col-voucher">{{ __('Voucher') }}</th>
                <th class="col-challan">{{ __('Challan') }}</th>
                <th class="col-supplier">{{ __('Party Name') }}</th>
                <th class="col-gstno">{{ __('GST No.') }}</th>
                <th class="col-net text-end">{{ __('Net Amt') }}</th>
                <th class="col-tax text-end">{{ __('GST Amt') }}</th>
                <th class="col-total text-end">{{ __('Total Amount') }}</th>
                <th class="text-center" style="width: 140px;">{{ __('Is Online Product') }}</th>
                @hasPermission('shop.itemMaster.edit')
                <th class="text-center" style="width: 100px;">{{ __('Action') }}</th>
                @endhasPermission
            </tr>
        </thead>
        <tbody>
        @forelse($inwardLists as $key => $purchase)
            @php
                $serial = $inwardLists->firstItem() + $key;
                $invoice = $purchase->inwardInvoice;
            @endphp
            <tr>
                <!-- SL -->
                <td class="text-center text-secondary fw-semibold">{{ $serial }}</td>

                <!-- Voucher -->
                <td class="col-voucher">
                    <div class="fw-semibold text-primary" style="font-size: 13.5px;">{{ $invoice?->inward_voucher_no ?? '-' }}</div>
                    <small class="text-muted font-monospace d-block" style="font-size: 11px;">
                        {{ !empty($invoice?->inward_date) ? $invoice->inward_date->format('d M, Y') : '-' }}
                    </small>
                </td>

                <!-- Challan -->
                <td class="col-challan">
                    <div class="fw-semibold text-secondary" style="font-size: 13.5px;">{{ $invoice?->inward_challan_no ?? '-' }}</div>
                    <small class="text-muted font-monospace d-block" style="font-size: 11px;">
                        {{ !empty($invoice?->inward_challan_date) ? $invoice->inward_challan_date->format('d M, Y') : '-' }}
                    </small>
                </td>

                <!-- Party Name -->
                <td class="col-supplier">
                    <div class="fw-semibold text-dark" style="font-size: 13.5px;">{{ $invoice?->partyCode?->accountName ?? '-' }}</div>
                    <div class="text-muted small mt-0.5" style="font-size: 11px;">
                        Code: {{ $invoice?->partyCode?->accountshortcode ?? '-' }}
                        @if(!empty($invoice?->partyCode?->cont_info_mobile1))
                            | Mobile: <a href="tel:{{ $invoice->partyCode->cont_info_mobile1 }}" class="text-secondary text-decoration-none" onclick="event.stopPropagation();">{{ $invoice->partyCode->cont_info_mobile1 }}</a>
                        @endif
                    </div>
                </td>

                <!-- GST No. -->
                <td class="col-gstno">
                    <span class="font-monospace text-secondary" style="font-size: 12.5px;">{{ $invoice?->partyCode?->tax_info_gst_no ?? '-' }}</span>
                </td>

                <!-- Net Amt -->
                <td class="col-net font-monospace text-end">
                    {{ is_numeric($invoice?->inward_acc_net_amount) ? number_format((float)$invoice->inward_acc_net_amount, 2) : ($invoice?->inward_acc_net_amount ?? '-') }}
                </td>

                <!-- GST Amt -->
                <td class="col-tax font-monospace text-end text-danger">
                    {{ is_numeric($invoice?->inward_acc_gst_amount) ? '+' . number_format((float)$invoice->inward_acc_gst_amount, 2) : ($invoice?->inward_acc_gst_amount ?? '-') }}
                </td>

                <!-- Total Amount -->
                <td class="col-total font-monospace text-end fw-bold text-success" style="font-size: 13.5px;">
                    {{ is_numeric($invoice?->inward_acc_amt_with_gst) ? number_format((float)$invoice->inward_acc_amt_with_gst, 2) : ($invoice?->inward_acc_amt_with_gst ?? '-') }}
                </td>

                <td class="text-center">
                    @php
                        $inwardItems = $invoice?->inwardProduct ?? collect();
                        $barcodedItems = $inwardItems->filter(fn($item) => \App\Models\ProductBarcode::where('inward_product_id', $item->id)->exists());
                        $totalBarcodedCount = $barcodedItems->count();
                        $onlineItemsCount = $barcodedItems->filter(fn($item) => (bool)($item->is_online_product ?? false))->count();
                    @endphp
                    @if($totalBarcodedCount > 0)
                        @if($onlineItemsCount > 0)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 viewData cursor-pointer" 
                                  data-id="{{ $purchase->id }}" 
                                  data-bs-toggle="tooltip" 
                                  title="{{ __('Click to manage individual item online availability') }}"
                                  style="font-size: 11.5px; cursor: pointer;">
                                <i class="bi bi-globe me-1"></i>{{ $onlineItemsCount }}/{{ $totalBarcodedCount }} {{ __('Online') }}
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 viewData cursor-pointer" 
                                  data-id="{{ $purchase->id }}" 
                                  data-bs-toggle="tooltip" 
                                  title="{{ __('Click to enable individual items for online selling') }}"
                                  style="font-size: 11.5px; cursor: pointer;">
                                <i class="bi bi-globe me-1"></i>0/{{ $totalBarcodedCount }} {{ __('Online') }}
                            </span>
                        @endif
                    @else
                        <span class="badge bg-light text-muted border px-2 py-1 viewData cursor-pointer" 
                              data-id="{{ $purchase->id }}"
                              style="font-size: 11.5px; cursor: pointer;" 
                              title="{{ __('Click to view details. Generate barcode first to sell online.') }}">
                            {{ __('No Barcode') }}
                        </span>
                    @endif
                </td>

                <!-- Action -->
                @hasPermission('shop.itemMaster.edit')
                <td class="text-center">
                    <div class="d-flex gap-2 justify-content-center">
                        @hasPermission('shop.purchaseProduct.edit')
                        <a href="javascript:void(0)"
                           data-id="{{ $purchase->id }}"
                           class="btn btn-outline-primary btn-sm circleIcon viewData"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           data-bs-title="{{ __('View purchase') }}">
                            <i class="bi bi-eye"></i>
                        </a>
                        @endhasPermission
                    </div>
                </td>
                @endhasPermission
            </tr>
        @empty
            <tr>
                <td class="text-center py-4 text-muted" colspan="100%">
                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                    {{ __('No Data Found') }}
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="my-3" id="pagination-links">
    {{ $inwardLists->links() }}
</div>