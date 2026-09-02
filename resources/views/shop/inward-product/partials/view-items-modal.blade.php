<style>
    #inward-items-view-modal table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    #inward-items-view-modal tbody {
        display: table-row-group !important;
    }
    #inward-items-view-modal table tr,
    #inward-items-view-modal table tr:not(:first-child) {
        display: table-row !important;
        opacity: 1 !important;
        visibility: visible !important;
        position: static !important;
        transform: none !important;
        animation: none !important;
    }
    #inward-items-view-modal table td,
    #inward-items-view-modal table th {
        display: table-cell !important;
        vertical-align: middle !important;
    }
</style>

<!-- View Inward Product Items Modal -->
<div class="modal fade" tabindex="-1" id="inward-items-view-modal" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            
            <!-- Modal Header -->
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark m-0 d-flex align-items-center gap-2" id="vItemModalTitle" style="font-size: 16.5px;">
                        <i class="bi bi-boxes text-primary fs-5"></i>
                        <span>{{ __('Items –') }}</span>
                        <span id="vItemVoucherNo" class="text-primary font-monospace fw-bold"></span>
                    </h5>
                    <small id="vItemSupplierInfo" class="text-muted d-block mt-1" style="font-size: 12px;"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light-subtle">
                <!-- Search Filter Box -->
                <div class="mb-3 position-relative">
                    <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               id="vItemSearchInput" 
                               class="form-control border-start-0 ps-1 py-2" 
                               placeholder="{{ __('Search by product, design, code or barcode') }}"
                               style="font-size: 13.5px;"
                               autocomplete="off">
                        <button type="button" class="btn btn-white border-start-0 text-muted pe-3 d-none" id="vItemClearSearchBtn" title="{{ __('Clear search') }}">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>

                <!-- Items Table Container Card -->
                <div class="card border shadow-2xs rounded-3 overflow-hidden bg-white">
                    <div class="table-responsive" style="max-height: 52vh; min-height: 200px;">
                        <table class="table table-hover align-middle mb-0" id="vItemsTable">
                            <thead class="table-light sticky-top" style="z-index: 2;">
                                <tr style="border-bottom: 1.5px solid #e2e8f0;">
                                    <th class="text-center" style="width: 55px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('SL') }}</th>
                                    <th style="min-width: 220px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Product') }}</th>
                                    <th style="min-width: 130px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Design / Code') }}</th>
                                    <th class="text-center" style="width: 80px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Qty') }}</th>
                                    <th class="text-end" style="width: 110px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Rate') }}</th>
                                    <th class="text-end" style="width: 110px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('MRP') }}</th>
                                    <th class="text-center" style="width: 90px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('GST') }}</th>
                                    <th class="text-end" style="width: 130px; font-size: 11.5px; color: #475569; text-transform: uppercase;">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody id="vItemsTableBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>

                        <!-- Loader State -->
                        <div id="vItemsLoader" class="text-center py-5 d-none">
                            <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-muted small fw-medium">{{ __('Loading voucher items...') }}</div>
                        </div>

                        <!-- Empty State / No Match -->
                        <div id="vItemsEmptyState" class="text-center py-5 d-none">
                            <i class="bi bi-search text-muted mb-2 d-block" style="font-size: 2.2rem; opacity: 0.4;"></i>
                            <h6 class="fw-bold text-dark mb-1" id="vItemsEmptyTitle">{{ __('No matching items found') }}</h6>
                            <p class="text-muted small mb-0" id="vItemsEmptySubtitle">{{ __('Try searching with another product name, code or design number.') }}</p>
                        </div>

                        <!-- Error State -->
                        <div id="vItemsErrorState" class="text-center py-5 d-none">
                            <i class="bi bi-exclamation-triangle text-danger mb-2 d-block" style="font-size: 2.2rem;"></i>
                            <h6 class="fw-bold text-dark mb-1">{{ __('Failed to load items') }}</h6>
                            <p class="text-muted small mb-3">{{ __('An error occurred while fetching items for this voucher.') }}</p>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3 py-1.5 rounded-2" id="vItemsRetryBtn">
                                <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Retry Loading') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light border-top px-4 py-2.5 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <span id="vItemTotalCount" class="fw-bold text-dark">0</span> {{ __('items listed') }}
                </div>
                <button type="button" class="btn btn-secondary btn-sm px-4 fw-semibold rounded-2" data-bs-dismiss="modal">
                    {{ __('Close') }}
                </button>
            </div>

        </div>
    </div>
</div>
