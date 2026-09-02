<style>
    /* Clean Simple Table styling */
    .clean-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
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
    .list-item-row {
        padding: 3px 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 12px;
    }
    .list-item-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
</style>

<div class="clean-table-card">
    <table class="clean-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">{{ __('SL') }}</th>
                <th class="col-voucher">{{ __('Voucher / Bill') }}</th>
                <th class="col-challan">{{ __('Challan Details') }}</th>
                <th class="col-supplier">{{ __('Supplier / Party') }}</th>
                <th class="col-items" style="min-width: 280px;">{{ __('Items (Products & Prices)') }}</th>
                <th class="col-gstno">{{ __('GST No.') }}</th>
                <th class="col-net">{{ __('Net Value') }}</th>
                <th class="col-tax">{{ __('Tax (GST)') }}</th>
                <th class="col-total">{{ __('Final Total') }}</th>
                @hasPermission('shop.itemMaster.edit')
                <th class="text-center" style="width: 120px;">{{ __('Action') }}</th>
                @endhasPermission
            </tr>
        </thead>
        <tbody>
            @forelse($inwardLists as $key => $inwardList)
                @php
                    $serial = $inwardLists->firstItem() + $key;
                @endphp
                <tr>
                    <!-- Serial Number -->
                    <td class="text-center text-secondary fw-semibold">{{ $serial }}</td>
                    
                    <!-- Voucher / Bill Info -->
                    <td class="col-voucher">
                        <div class="d-flex align-items-center gap-1">
                            <span class="fw-semibold text-primary">{{ $inwardList->inward_voucher_no ?? '-' }}</span>
                            @if(!empty($inwardList->is_kachi))
                                <span class="badge bg-warning text-dark px-1.5 py-0.5" style="font-size: 10px;" title="{{ __('Kachi Entry') }}">Kachi</span>
                            @endif
                        </div>
                        <small class="text-muted font-monospace d-block" style="font-size: 11px;">
                            {{ (!empty($inwardList->inward_date) ? $inwardList->inward_date->format('d M, Y') : '-') }}
                        </small>
                    </td>
                    
                    <!-- Challan Details -->
                    <td class="col-challan">
                        <div class="fw-semibold text-secondary">{{ $inwardList->inward_challan_no ?? '-' }}</div>
                        <small class="text-muted font-monospace d-block" style="font-size: 11px;">
                            {{ (!empty($inwardList->inward_challan_date) ? $inwardList->inward_challan_date->format('d M, Y') : '-') }}
                        </small>
                    </td>
                    
                    <!-- Supplier / Party Details -->
                    <td class="col-supplier">
                        <div class="fw-semibold">{{ $inwardList->partyCode?->accountName ?? '-' }}</div>
                        <div class="text-muted small mt-0.5" style="font-size: 11px;">
                            Code: {{ $inwardList->partyCode?->accountshortcode ?? '-' }} 
                            @if(!empty($inwardList->partyCode?->cont_info_mobile1))
                                | Mobile: <a href="tel:{{ $inwardList->partyCode->cont_info_mobile1 }}" class="text-secondary text-decoration-none">{{ $inwardList->partyCode->cont_info_mobile1 }}</a>
                            @endif
                        </div>
                    </td>

                    <!-- Items Details (Products List) -->
                    <td class="col-items">
                        @php
                            $totalItemsCount = $inwardList->inwardProduct ? $inwardList->inwardProduct->count() : 0;
                            $previewItems = $inwardList->inwardProduct ? $inwardList->inwardProduct->take(2) : collect();
                            $remainingCount = max(0, $totalItemsCount - 2);

                            $jsonItems = $inwardList->inwardProduct ? $inwardList->inwardProduct->map(function($p) {
                                return [
                                    'id' => $p->id,
                                    'quantity' => $p->quantity,
                                    'buy_price' => (float)$p->buy_price,
                                    'mrp' => (float)$p->mrp,
                                    'net_purc_rate' => (float)($p->net_purc_rate ?? ($p->buy_price * $p->quantity)),
                                    'discount' => (float)($p->discount_percentage ?? $p->discount ?? 0),
                                    'products' => [
                                        'name' => $p->products?->name ?? 'Unknown Item',
                                        'code' => $p->products?->item_code ?? $p->products?->code ?? '-',
                                        'barcode' => $p->barcode ?? $p->products?->barcode ?? '',
                                        'sku' => $p->products?->sku ?? ''
                                    ],
                                    'design_master' => [
                                        'design_number' => $p->designMaster?->design_number ?? '-'
                                    ],
                                    'vat_tax' => [
                                        'percentage' => (float)($p->vatTax?->percentage ?? $p->tax_rate ?? 0)
                                    ]
                                ];
                            }) : [];
                        @endphp
                        <div class="d-flex align-items-start justify-content-between position-relative">
                            <div class="flex-grow-1 pe-2" style="overflow: hidden;">
                                @forelse($previewItems as $item)
                                    <div class="list-item-row mb-1 pb-1 border-bottom border-light-subtle">
                                        <div class="fw-semibold text-dark" style="font-size: 12.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $item->products->name ?? 'Unknown Item' }}
                                        </div>
                                        <div class="text-muted mt-0.5" style="font-size: 11px;">
                                            Design: {{ $item->designMaster?->design_number ?? '-' }} | 
                                            Qty: <strong>{{ $item->quantity }}</strong> | 
                                            Rate: ₹{{ number_format($item->buy_price, 2) }} | 
                                            MRP: ₹{{ number_format($item->mrp, 2) }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted font-italic" style="font-size: 11px;">
                                        {{ __('No Items Listed') }}
                                    </div>
                                @endforelse

                                @if($remainingCount > 0)
                                    <button type="button" 
                                            class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5 mt-1 view-voucher-items-trigger"
                                            style="font-size: 10.5px; font-weight: 600; cursor: pointer;"
                                            data-id="{{ $inwardList->id }}"
                                            data-voucher-no="{{ $inwardList->inward_voucher_no ?? '-' }}"
                                            data-party-name="{{ $inwardList->partyCode?->accountName ?? '' }}"
                                            data-party-code="{{ $inwardList->partyCode?->accountshortcode ?? '' }}"
                                            data-items="{{ json_encode($jsonItems) }}">
                                        <i class="bi bi-box-seam me-1"></i>+{{ $remainingCount }} {{ __('more items') }}
                                    </button>
                                @endif
                            </div>

                            @if($totalItemsCount > 0)
                                <button type="button" 
                                        class="btn btn-sm btn-light text-primary border shadow-2xs p-1 rounded-2 view-voucher-items-trigger flex-shrink-0"
                                        style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="{{ __('View all items') }}"
                                        data-id="{{ $inwardList->id }}"
                                        data-voucher-no="{{ $inwardList->inward_voucher_no ?? '-' }}"
                                        data-party-name="{{ $inwardList->partyCode?->accountName ?? '' }}"
                                        data-party-code="{{ $inwardList->partyCode?->accountshortcode ?? '' }}"
                                        data-items="{{ json_encode($jsonItems) }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                    
                    <!-- GST No. -->
                    <td class="col-gstno">
                        <span class="font-monospace text-secondary">{{ $inwardList->partyCode->tax_info_gst_no ?? '-' }}</span>
                    </td>
                    
                    <!-- Net Value -->
                    <td class="col-net font-monospace text-end">
                        {{ number_format($inwardList->inward_acc_net_amount ?? 0, 2) }}
                    </td>
                    
                    <!-- Tax (GST) Amt -->
                    <td class="col-tax font-monospace text-end text-danger">
                        +{{ number_format($inwardList->inward_acc_gst_amount ?? 0, 2) }}
                    </td>
                    
                    <!-- Final Bill Total -->
                    <td class="col-total font-monospace text-end fw-bold text-success">
                        {{ number_format($inwardList->inward_acc_amt_with_gst ?? 0, 2) }}
                    </td>
                    
                    <!-- Action Buttons -->
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            @hasPermission('shop.inwardProduct.edit')
                            <a href="javascript:void(0)" data-id="{{$inwardList->id}}"
                               class="btn btn-outline-info circleIcon btn-sm barcodeCreate barcodeGarnette"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               data-bs-title="{{ __('Generate Barcode') }}">
                                <i class="bi bi-upc-scan"></i>
                            </a>
                            @endhasPermission

                            @hasPermission('shop.inwardProduct.edit')
                            <a href="javascript:void(0)" data-id="{{$inwardList->id}}" 
                               class="btn btn-outline-primary btn-sm circleIcon editData"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top"
                               data-bs-title="{{ __('Edit Bill') }}">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @endhasPermission
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-center py-4 text-muted" colspan="100%">
                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                        {{ __('No Inward Bills Found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="my-4 d-flex justify-content-end" id="pagination-links">
    {{ $inwardLists->links() }}
</div>