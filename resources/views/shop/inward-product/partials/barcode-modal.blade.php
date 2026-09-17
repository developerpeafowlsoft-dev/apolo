<style>
    /* Barcode Table styling */
    .barcode-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .barcode-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    .barcode-table thead tr {
        border-bottom: 1px solid #e2e8f0;
        background-color: #f8fafc;
    }
    .barcode-table thead tr th {
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 11.5px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding: 12px 14px !important;
        border: none !important;
        text-align: left;
    }
    .barcode-table tbody tr {
        border-bottom: 1px solid #f1f5f9 !important;
        transition: background-color 0.15s ease !important;
    }
    .barcode-table tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .barcode-table tbody tr td {
        padding: 12px 14px !important;
        vertical-align: middle !important;
        color: #1e293b !important;
        font-size: 13px !important;
        border: none !important;
    }
    .btn-action-outline-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        font-size: 11.5px;
        font-weight: 600;
        border-radius: 6px;
        border: 1px solid #dbeafe;
        background-color: #eff6ff;
        color: #2563eb;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-action-outline-primary:hover {
        background-color: #2563eb;
        color: #ffffff !important;
        border-color: #2563eb;
    }
</style>

<div class="modal fade" tabindex="-1" id="barcode-generate-modal" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw;">
        <div class="modal-content overflow-hidden border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2 py-2.5 px-4">
                <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center" id="modalTitle">
                    <i class="bi bi-upc-scan text-primary me-2"></i>{{ __('Barcode Manager') }} — <span class="text-secondary small font-monospace ms-1">Bill No: </span><span id="voucherNumber" class="text-primary fw-bold font-monospace ms-1"></span>
                </h5>

                <!-- Action Buttons before Close (X) button -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Button 1: Generate All Barcodes -->
                    <button type="button" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-1.5 shadow-sm" id="btnGenerateAllModalBarcodes" title="{{ __('Generate barcodes for all items in this bill in one click') }}" style="border-radius: 6px; height: 34px;">
                        <i class="bi bi-cpu"></i> <span>{{ __('Generate All Barcodes') }}</span>
                    </button>

                    <!-- Button 2: Print Barcodes for checked items -->
                    <button type="button" class="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-1.5 shadow-sm" id="btnPrintSelectedModalBarcodes" title="{{ __('Print barcodes for selected items (2 per row on barcode printer)') }}" style="border-radius: 6px; height: 34px;">
                        <i class="bi bi-printer"></i> <span>{{ __('Print Barcodes') }}</span> (<span id="selectedBarcodeCount">0</span>)
                    </button>

                    <!-- Close (X) button -->
                    <button type="button" class="btn-close ms-2" id="modelClose" data-modal-name="barcode-generate-modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4 bg-light-subtle" style="max-height: 75vh; overflow-y: auto;">
                <div class="barcode-table-card">
                    <table class="barcode-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input class="form-check-input cursor-pointer" type="checkbox" id="selectAllModalItems" checked title="{{ __('Select / Deselect All') }}">
                                </th>
                                <th class="text-center" style="width: 45px;">{{ __('SL') }}</th>
                                <th>{{ __('Product Details') }}</th>
                                <th>{{ __('Design No') }}</th>
                                <th>{{ __('Color & Size Variants') }}</th>
                                <th class="text-center" style="width: 70px;">{{ __('Qty') }}</th>
                                <th>{{ __('Rates / MRP') }}</th>
                                <th>{{ __('GST Tax Info') }}</th>
                                <th class="text-end">{{ __('Net Value') }}</th>
                                <th class="text-center">{{ __('Status') }}</th>
                                @hasPermission('shop.inwardProduct.edit')
                                <th class="text-center" style="width: 140px;">{{ __('Action') }}</th>
                                @endhasPermission
                            </tr>
                        </thead>
                        <tbody id="barcodeTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Hidden Form for Bulk Barcode Printing -->
            <form id="bulkBarcodePrintForm" method="POST" target="_blank" style="display: none;">
                @csrf
                <input type="hidden" name="inward_product_ids" id="bulk_inward_product_ids">
            </form>
        </div>
    </div>
</div>
