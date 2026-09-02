<style>
    .purchase-details-content table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    .purchase-details-content tbody {
        display: table-row-group !important;
    }
    .purchase-details-content table tr,
    .purchase-details-content table tr:not(:first-child) {
        display: table-row !important;
        opacity: 1 !important;
        visibility: visible !important;
        position: static !important;
        transform: none !important;
        animation: none !important;
    }
    .purchase-details-content table td,
    .purchase-details-content table th {
        display: table-cell !important;
        vertical-align: middle !important;
    }

    @media print {
        body {
            background: #ffffff !important;
            padding: 20px !important;
            margin: 0 !important;
        }
        .purchase-details-content .table-responsive {
            max-height: none !important;
            overflow: visible !important;
        }
        #btnDownloadPurchasePdf, #btnPrintPurchase {
            display: none !important;
        }
        .purchase-details-content table tr {
            page-break-inside: avoid !important;
        }
    }
</style>

<div class="purchase-details-content">
    
    <!-- Hidden helper data for modal header/footer -->
    <span id="purchaseDetailsVoucherNo" class="d-none">{{ $purchase->inwardInvoice->inward_voucher_no ?? '' }}</span>
    <input type="hidden" id="purchaseDetailsItemCount" value="{{ $purchase->inwardInvoice->inwardProduct ? $purchase->inwardInvoice->inwardProduct->count() : 0 }}">

    <!-- Document Heading Banner -->
    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
        <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                {{ __('PURCHASE ORDER') }}
            </span>
            <h6 class="fw-bold text-dark mt-1.5 mb-0" style="font-size: 14.5px;">
                Voucher: <span class="text-primary font-monospace">{{ $purchase->inwardInvoice->inward_voucher_no ?? '-' }}</span>
            </h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" 
                    id="btnDownloadPurchasePdf" 
                    class="btn btn-outline-danger btn-sm fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-2 shadow-2xs" 
                    style="font-size: 12.5px;"
                    title="{{ __('Download Purchase PDF') }}">
                <i class="bi bi-file-earmark-pdf-fill fs-6"></i>
                <span>{{ __('Download PDF') }}</span>
            </button>
            <button type="button" 
                    id="btnPrintPurchase" 
                    class="btn btn-outline-secondary btn-sm fw-semibold d-inline-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded-2 shadow-2xs" 
                    style="font-size: 12.5px;"
                    title="{{ __('Print Purchase Order') }}">
                <i class="bi bi-printer fs-6"></i>
            </button>
        </div>
    </div>

    <!-- Summary Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Supplier Details Card -->
        <div class="col-md-6">
            <div class="card h-100 border shadow-2xs rounded-3 bg-white">
                <div class="card-header bg-light-subtle py-2 px-3 border-bottom">
                    <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-1.5" style="font-size: 12.5px;">
                        <i class="bi bi-building text-secondary"></i>
                        {{ __('Supplier Details') }}
                    </h6>
                </div>
                <div class="card-body p-3" style="font-size: 12.5px;">
                    <div class="fw-bold text-dark fs-6 mb-1.5">
                        {{ $purchase->inwardInvoice->partyCode->accountName ?? '-' }}
                    </div>
                    <div class="d-flex flex-column gap-1 text-secondary" style="font-size: 12px;">
                        <div><span class="text-muted">{{ __('Party Code:') }}</span> <span class="fw-semibold text-dark ms-1">{{ $purchase->inwardInvoice->partyCode->accountshortcode ?? '-' }}</span></div>
                        <div><span class="text-muted">{{ __('GST No.:') }}</span> <span class="font-monospace fw-semibold text-dark ms-1">{{ $purchase->inwardInvoice->partyCode->tax_info_gst_no ?? '-' }}</span></div>
                        <div><span class="text-muted">{{ __('Mobile:') }}</span> <span class="fw-semibold text-dark ms-1">{{ $purchase->inwardInvoice->partyCode->cont_info_mobile1 ?? '-' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Information Card -->
        <div class="col-md-6">
            <div class="card h-100 border shadow-2xs rounded-3 bg-white">
                <div class="card-header bg-light-subtle py-2 px-3 border-bottom">
                    <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-1.5" style="font-size: 12.5px;">
                        <i class="bi bi-file-earmark-spreadsheet text-secondary"></i>
                        {{ __('Purchase Information') }}
                    </h6>
                </div>
                <div class="card-body p-3" style="font-size: 12.5px;">
                    <div class="d-flex flex-column gap-1.5" style="font-size: 12px;">
                        <div class="d-flex justify-content-between border-bottom pb-1"><span class="text-muted">{{ __('Voucher No.:') }}</span> <span class="font-monospace fw-bold text-primary">{{ $purchase->inwardInvoice->inward_voucher_no ?? '-' }}</span></div>
                        <div class="d-flex justify-content-between border-bottom pb-1"><span class="text-muted">{{ __('Challan No.:') }}</span> <span class="font-monospace fw-semibold text-secondary">{{ $purchase->inwardInvoice->inward_challan_no ?? '-' }}</span></div>
                        <div class="d-flex justify-content-between border-bottom pb-1"><span class="text-muted">{{ __('Bill Date:') }}</span> <span class="font-monospace text-dark">{{ $purchase->bill_date ? \Carbon\Carbon::parse($purchase->bill_date)->format('d M, Y') : '-' }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted">{{ __('Challan Date:') }}</span> <span class="font-monospace text-dark">{{ !empty($purchase->inwardInvoice->inward_challan_date) ? $purchase->inwardInvoice->inward_challan_date->format('d M, Y') : '-' }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table Section -->
    <div class="card border shadow-2xs rounded-3 overflow-hidden bg-white mb-4">
        <div class="table-responsive" style="max-height: 48vh; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" id="purchaseDetailsItemsTable">
                <thead class="table-light sticky-top" style="z-index: 2; border-bottom: 1.5px solid #e2e8f0;">
                    <tr>
                        <th class="text-center" style="width: 45px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('SL') }}</th>
                        <th style="min-width: 180px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Item') }}</th>
                        <th class="text-center" style="width: 60px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Qty') }}</th>
                        <th style="min-width: 90px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Color') }}</th>
                        <th style="min-width: 90px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Size') }}</th>
                        <th class="text-end" style="min-width: 90px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Purc Rate') }}</th>
                        <th class="text-end" style="min-width: 90px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Amount') }}</th>
                        <th class="text-end" style="min-width: 75px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Disc(%)') }}</th>
                        <th class="text-end" style="min-width: 90px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('MRP') }}</th>
                        <th class="text-end" style="min-width: 110px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Net PurcRate') }}</th>
                        <th class="text-center" style="min-width: 85px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Tax Code') }}</th>
                        <th class="text-center" style="min-width: 80px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('SGST %') }}</th>
                        <th class="text-center" style="min-width: 95px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Sell Online') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchase->inwardInvoice->inwardProduct as $key => $item)
                        @php
                            $qty = $item->qty ?? $item->pcs ?? $item->quantity ?? 0;

                            $productName = '-';
                            if(isset($item->products)) {
                                if($item->products instanceof \Illuminate\Support\Collection) {
                                    $productName = $item->products->pluck('name')->implode(', ');
                                } else {
                                    $productName = $item->products->name ?? '-';
                                }
                            }

                            $colorName = '-';
                            if(isset($item->colors)) {
                                if($item->colors instanceof \Illuminate\Support\Collection) {
                                    $colorName = $item->colors->pluck('name')->implode(', ');
                                } else {
                                    $colorName = $item->colors->name ?? '-';
                                }
                            }

                            $sizeName = '-';
                            if(isset($item->sizes)) {
                                if($item->sizes instanceof \Illuminate\Support\Collection) {
                                    $sizeName = $item->sizes->pluck('name')->implode(', ');
                                } else {
                                    $sizeName = $item->sizes->name ?? '-';
                                }
                            }

                            // Check barcode generation for THIS SPECIFIC inward product variant
                            $hasBarcode = \App\Models\ProductBarcode::where('inward_product_id', $item->id)->exists();
                            $isItemOnline = $hasBarcode && (bool)($item->is_online_product ?? false);
                        @endphp

                        <tr>
                            <td class="text-center text-secondary fw-semibold">{{ $key + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 13px;">{{ $productName }}</div>
                                @if(!empty($item->designMaster?->design_number))
                                    <small class="text-muted d-block mt-0.5" style="font-size: 11px;">
                                        Design: <span class="font-monospace fw-semibold">{{ $item->designMaster->design_number }}</span>
                                    </small>
                                @endif
                            </td>
                            <td class="text-center font-monospace fw-bold">{{ $qty }}</td>
                            <td><small class="text-dark">{{ $colorName }}</small></td>
                            <td><small class="text-dark">{{ $sizeName }}</small></td>
                            <td class="text-end font-monospace">₹{{ number_format((float)$item->buy_price, 2) }}</td>
                            <td class="text-end font-monospace">₹{{ number_format((float)$item->price, 2) }}</td>
                            <td class="text-end font-monospace">{{ $item->discount_price }}</td>
                            <td class="text-end font-monospace">₹{{ number_format((float)$item->mrp, 2) }}</td>
                            <td class="text-end font-monospace fw-semibold text-primary">₹{{ number_format((float)$item->net_purc_rate, 2) }}</td>
                            <td class="text-center font-monospace small text-secondary">{{ $item->hsnMaster->hsn_code ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 11px;">{{ $item->vatTax->percentage ?? 0 }}%</span>
                            </td>
                            <td class="text-center">
                                <label class="switch mb-0">
                                    <input type="checkbox" 
                                           class="single-item-online-toggle" 
                                           data-id="{{ $item->id }}" 
                                           data-item-name="{{ $productName }}"
                                           data-voucher="{{ $purchase->inwardInvoice->inward_voucher_no ?? '' }}"
                                           data-online="{{ $isItemOnline ? '1' : '0' }}" 
                                           data-barcode="{{ $hasBarcode ? '1' : '0' }}"
                                           {{ $isItemOnline ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                                {{ __('No Product Found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Amount Summary Card -->
    <div class="row justify-content-end">
        <div class="col-md-5 col-lg-4">
            <div class="card border shadow-2xs rounded-3 overflow-hidden bg-white">
                <div class="card-body p-0">
                    <table class="table table-borderless mb-0" style="font-size: 13px;">
                        <tbody>
                            <tr class="border-bottom">
                                <th class="ps-3 py-2 text-muted fw-semibold" style="width: 50%;">{{ __('Net Amount') }}</th>
                                <td class="pe-3 py-2 text-end font-monospace text-dark fw-medium">
                                    ₹{{ number_format((float)$purchase->inwardInvoice->inward_acc_net_amount, 2) }}
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <th class="ps-3 py-2 text-muted fw-semibold">{{ __('GST Amount') }}</th>
                                <td class="pe-3 py-2 text-end font-monospace text-danger fw-medium">
                                    +₹{{ number_format((float)$purchase->inwardInvoice->inward_acc_gst_amount, 2) }}
                                </td>
                            </tr>
                            <tr class="bg-success-subtle">
                                <th class="ps-3 py-2.5 text-success fw-bold">{{ __('Final Total') }}</th>
                                <td class="pe-3 py-2.5 text-end font-monospace fw-bold text-success" style="font-size: 14.5px;">
                                    ₹{{ number_format((float)$purchase->inwardInvoice->inward_acc_amt_with_gst, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
