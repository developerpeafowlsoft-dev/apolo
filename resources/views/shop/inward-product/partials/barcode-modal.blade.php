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
        padding: 12px 16px !important;
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
        padding: 12px 16px !important;
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
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 shadow-lg" style="border-radius: 12px;">
            <form id="formDataBarcodeGenerate" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="modalTitle">
                        <i class="bi bi-upc-scan text-primary me-2"></i>{{ __('Barcode Manager') }} — <span class="text-secondary small font-monospace">Bill No: </span><span id="voucherNumber" class="text-primary fw-bold font-monospace"></span>
                    </h5>
                    <button type="button" class="btn-close" id="modelClose" data-modal-name="barcode-generate-modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light-subtle" style="max-height: 70vh; overflow-y: auto;">
                    <div class="barcode-table-card">
                        <table class="barcode-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">{{ __('SL') }}</th>
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
            </form>
        </div>
    </div>
</div>
