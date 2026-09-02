<!-- Global Command Palette Spotlight Search Modal -->
<div class="modal fade" id="commandPaletteModal" tabindex="-1" aria-labelledby="commandPaletteModalLabel" aria-hidden="true" style="backdrop-filter: blur(5px); background: rgba(15, 23, 42, 0.6);">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 680px;">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="border-radius: 20px; background: #ffffff;">
            
            <!-- Search Header -->
            <div class="p-3 border-bottom d-flex align-items-center bg-light-subtle">
                <i class="fa-solid fa-magnifying-glass text-primary ms-2 me-3" style="font-size: 18px;"></i>
                <input type="text" id="commandPaletteSearchInput" class="form-control form-control-lg border-0 bg-transparent shadow-none px-0" 
                    placeholder="{{ __('Search pages, reports, masters, or type a command... (Press Esc to exit)') }}" 
                    style="font-size: 15px; font-weight: 500; color: #0f172a;" autocomplete="off">
                <button type="button" id="btnClearCommandSearch" class="btn btn-link text-muted p-0 me-2 d-none" style="text-decoration: none;">
                    <i class="fa-solid fa-circle-xmark" style="font-size: 16px;"></i>
                </button>
                <kbd class="bg-white border text-muted shadow-2xs px-2 py-1 rounded text-xs ms-1 me-2" style="font-size: 11px; font-family: inherit;">ESC</kbd>
            </div>

            <!-- Search Results Body -->
            <div class="modal-body p-2" style="max-height: 440px; overflow-y: auto;" id="commandPaletteResultsContainer">
                
                <!-- Quick Category Group: Quick Actions & POS -->
                <div class="command-group mb-3" data-group="pos">
                    <div class="px-3 py-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.8px;">
                        ⚡ {{ __('Sales & POS Operations') }}
                    </div>
                    @hasPermission('shop.pos.index')
                    <a href="{{ route('shop.pos.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="pos billing checkout cashier invoice terminal fast">
                        <div class="command-icon me-3 text-primary d-flex align-items-center justify-content-center bg-primary-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('POS Terminal') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Fast counter checkout & instant invoicing') }}</small>
                        </div>
                        <span class="badge bg-light text-secondary border fw-normal" style="font-size: 10px;">{{ __('Open') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.pos.sales')
                    <a href="{{ route('shop.pos.sales') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="pos sales history invoices receipts">
                        <div class="command-icon me-3 text-success d-flex align-items-center justify-content-center bg-success-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('POS Sales History') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('View counter sales & receipt logs') }}</small>
                        </div>
                    </a>
                    <a href="{{ route('shop.pos.history.index') }}?tab=returns" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="pos return refund credit note">
                        <div class="command-icon me-3 text-danger d-flex align-items-center justify-content-center bg-danger-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-rotate-left"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('POS Returns') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Process counter sales returns & refunds') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.pos.draft')
                    <a href="{{ route('shop.pos.draft') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="pos draft hold bill pending quote">
                        <div class="command-icon me-3 text-warning d-flex align-items-center justify-content-center bg-warning-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-file-invoice"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('POS Drafts & Hold Bills') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Resume held counter invoices') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.order.index')
                    <a href="{{ route('shop.order.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="orders online customer orders status shipping delivery">
                        <div class="command-icon me-3 text-info d-flex align-items-center justify-content-center bg-info-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('All Orders') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Manage online & customer orders') }}</small>
                        </div>
                    </a>
                    @endhasPermission
                </div>

                <!-- Catalog & Inventory -->
                <div class="command-group mb-3" data-group="catalog">
                    <div class="px-3 py-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.8px;">
                        🛍️ {{ __('Catalog & Inventory') }}
                    </div>
                    @hasPermission('shop.product.index')
                    <a href="{{ route('shop.product.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="products item inventory stock price SKU">
                        <div class="command-icon me-3 text-primary d-flex align-items-center justify-content-center bg-primary-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Products Catalog') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Manage store inventory, prices & variants') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.inwardProduct.index')
                    <a href="{{ route('shop.inwardProduct.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="inward product invoice supplier stock entry GRN arrival">
                        <div class="command-icon me-3 text-success d-flex align-items-center justify-content-center bg-success-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-file-import"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Inward Invoices') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Record supplier stock arrivals & inward invoices') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.purchaseProduct.index')
                    <a href="{{ route('shop.purchaseProduct.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="purchase vendor bill supplier purchase order PO">
                        <div class="command-icon me-3 text-purple d-flex align-items-center justify-content-center bg-purple-subtle rounded-2" style="width: 34px; height: 34px; background-color: #f3e8ff !important; color: #9333ea !important;">
                            <i class="fa-solid fa-cart-flatbed"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Purchases') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Vendor purchase bills & purchase orders') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.category.index')
                    <a href="{{ route('shop.category.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="categories category department classification">
                        <div class="command-icon me-3 text-warning d-flex align-items-center justify-content-center bg-warning-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Categories') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Product category classification') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.subcategory.index')
                    <a href="{{ route('shop.subcategory.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="sub category subcategory taxonomy">
                        <div class="command-icon me-3 text-warning d-flex align-items-center justify-content-center bg-warning-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-sitemap"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Sub Categories') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Sub category taxonomy setup') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('admin.flashSale.index')
                    <a href="{{ route('shop.flashSale.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="flash sale discount deal limited time">
                        <div class="command-icon me-3 text-danger d-flex align-items-center justify-content-center bg-danger-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Flash Sales') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Time-limited store promotions & deals') }}</small>
                        </div>
                    </a>
                    @endhasPermission
                </div>

                <!-- Catalog Masters -->
                <div class="command-group mb-3" data-group="masters">
                    <div class="px-3 py-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.8px;">
                        📦 {{ __('Catalog Masters') }}
                    </div>
                    @hasPermission('shop.itemMaster.index')
                    <a href="{{ route('shop.itemMaster.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="item master design barcode code SKU">
                        <i class="fa-solid fa-tags me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Item Master') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.designMaster.index')
                    <a href="{{ route('shop.designMaster.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="design master pattern style apparel">
                        <i class="fa-solid fa-pen-nib me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Design Master') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.brand.index')
                    <a href="{{ route('shop.brand.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="brand master manufacturer trademark">
                        <i class="fa-solid fa-copyright me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Brand Master') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.color.index')
                    <a href="{{ route('shop.color.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="color master shade variant">
                        <i class="fa-solid fa-palette me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Color Master') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.size.index')
                    <a href="{{ route('shop.size.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="size master dimensions measurement S M L XL">
                        <i class="fa-solid fa-ruler-combined me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Size Master') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.unit.index')
                    <a href="{{ route('shop.unit.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="unit master UOM piece kg pcs meter box">
                        <i class="fa-solid fa-boxes-packing me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Unit Master') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.material.index')
                    <a href="{{ route('shop.material.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="material master fabric cotton polyester silk">
                        <i class="fa-solid fa-shirt me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Material Master') }}</span>
                    </a>
                    @endhasPermission
                </div>

                <!-- Accounting & Finance -->
                <div class="command-group mb-3" data-group="accounting">
                    <div class="px-3 py-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.8px;">
                        💰 {{ __('Accounting & Finance') }}
                    </div>
                    @hasPermission('shop.accountMaster.index')
                    <a href="{{ route('shop.accountMaster.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="account master chart of accounts COA ledger journal debit credit">
                        <div class="command-icon me-3 text-primary d-flex align-items-center justify-content-center bg-primary-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-book-journal-whills"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Account Master') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Chart of accounts & accounting ledgers') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.accountBalance.index')
                    <a href="{{ route('shop.accountBalance.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="account balance trial balance opening balance ledger statement">
                        <div class="command-icon me-3 text-success d-flex align-items-center justify-content-center bg-success-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Account Balance') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Ledger opening balances & statements') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.bankMaster.index')
                    <a href="{{ route('shop.bankMaster.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="bank master bank account BRS cheque netbanking IFSC">
                        <div class="command-icon me-3 text-info d-flex align-items-center justify-content-center bg-info-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Bank Master') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Store bank accounts & cheque books') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.counterMaster.index')
                    <a href="{{ route('shop.counterMaster.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="counter master POS counter cash register drawer">
                        <div class="command-icon me-3 text-secondary d-flex align-items-center justify-content-center bg-secondary-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-desktop"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Counter Master') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Physical store cashier checkout points') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.tdsMaster.index')
                    <a href="{{ route('shop.tdsMaster.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="TDS master tax deduction at source section rate">
                        <i class="fa-solid fa-percent me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('TDS Master') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.hsnMaster.index')
                    <a href="{{ route('shop.hsnMaster.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="HSN master SAC code GST tax percentage rate">
                        <i class="fa-solid fa-barcode me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('HSN Master') }}</span>
                    </a>
                    @endhasPermission

                    @if (!auth()->user()->hasRole('root'))
                        @hasPermission('shop.withdraw.index')
                        <a href="{{ route('shop.withdraw.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="withdraw payout bank transfer wallet balance">
                            <div class="command-icon me-3 text-success d-flex align-items-center justify-content-center bg-success-subtle rounded-2" style="width: 34px; height: 34px;">
                                <i class="fa-solid fa-money-bill-transfer"></i>
                            </div>
                            <div class="flex-grow-1">
                                <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Withdrawals') }}</span>
                                <small class="text-muted" style="font-size: 11.5px;">{{ __('Payout requests & wallet balance transfers') }}</small>
                            </div>
                        </a>
                        @endhasPermission
                    @endif
                </div>

                <!-- Reports & Analytics -->
                <div class="command-group mb-3" data-group="reports">
                    <div class="px-3 py-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.8px;">
                        📊 {{ __('Reports & Statutory Analytics') }}
                    </div>
                    <a href="{{ route('shop.reports.gstDashboard') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="GST report reports statutory GSTR-1 GSTR-3B tax return">
                        <div class="command-icon me-3 text-primary d-flex align-items-center justify-content-center bg-primary-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('GST Statutory Report') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('GSTR-1, GSTR-3B tax dashboards & returns') }}</small>
                        </div>
                    </a>
                    <a href="{{ route('shop.reports.financialStatements') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="financial statements report reports profit and loss P&L balance sheet trading account">
                        <div class="command-icon me-3 text-success d-flex align-items-center justify-content-center bg-success-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Financial Statements') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Profit & Loss, Balance Sheet & Trading AC') }}</small>
                        </div>
                    </a>
                    <a href="{{ route('shop.reports.bankReconciliation') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="cash bank BRS report reports reconciliation bank statement clearance">
                        <div class="command-icon me-3 text-info d-flex align-items-center justify-content-center bg-info-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Cash & Bank BRS') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Bank reconciliation statements & cash logs') }}</small>
                        </div>
                    </a>
                    <a href="{{ route('shop.reports.outstandingAgeing') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="outstanding ageing report reports receivables payables due overdue days customer vendor">
                        <div class="command-icon me-3 text-warning d-flex align-items-center justify-content-center bg-warning-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Outstanding Ageing') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Debtors & Creditors due ageing report') }}</small>
                        </div>
                    </a>
                    <a href="{{ route('shop.reports.visualAnalytics') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="visual analytics sales report reports chart graph trends revenue breakdown">
                        <div class="command-icon me-3 text-danger d-flex align-items-center justify-content-center bg-danger-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Visual Analytics & Sales') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Sales velocity, charts & revenue trends') }}</small>
                        </div>
                    </a>
                    <a href="{{ route('shop.reports.counterProductivity') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="counter productivity report reports cashier performance sales velocity register audit">
                        <div class="command-icon me-3 text-secondary d-flex align-items-center justify-content-center bg-secondary-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Counter Productivity') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Cashier productivity & register audits') }}</small>
                        </div>
                    </a>
                </div>

                <!-- Store Settings & Staff -->
                <div class="command-group mb-3" data-group="settings">
                    <div class="px-3 py-1 text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.8px;">
                        ⚙️ {{ __('Store Management & Utilities') }}
                    </div>
                    @hasPermission('shop.profile.index')
                    <a href="{{ route('shop.profile.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="store profile shop settings logo address info contact phone">
                        <div class="command-icon me-3 text-primary d-flex align-items-center justify-content-center bg-primary-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Store Profile') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Store settings, address, tax & logo info') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.employee.index')
                    <a href="{{ route('shop.employee.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="employees staff users permissions ACL roles manager cashier">
                        <div class="command-icon me-3 text-success d-flex align-items-center justify-content-center bg-success-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Employee Management') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Store employees & ACL permissions') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.salesman.index')
                    <a href="{{ route('shop.salesman.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="salesman sales representative commission staff agent">
                        <div class="command-icon me-3 text-info d-flex align-items-center justify-content-center bg-info-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Salesmen') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Sales representatives & counter commission') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.voucher.index')
                    <a href="{{ route('shop.voucher.index') }}" class="command-item d-flex align-items-center px-3 py-2.5 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="promo codes coupon discount voucher promotion marketing">
                        <div class="command-icon me-3 text-warning d-flex align-items-center justify-content-center bg-warning-subtle rounded-2" style="width: 34px; height: 34px;">
                            <i class="fa-solid fa-ticket-simple"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold d-block text-dark" style="font-size: 13.5px;">{{ __('Promo Codes') }}</span>
                            <small class="text-muted" style="font-size: 11.5px;">{{ __('Discount vouchers & coupons') }}</small>
                        </div>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.bulk-product-export.index')
                    <a href="{{ route('shop.bulk-product-export.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="bulk export CSV Excel product download backup">
                        <i class="fa-solid fa-file-export me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Bulk Export') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.bulk-product-import.index')
                    <a href="{{ route('shop.bulk-product-import.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="bulk import CSV Excel product upload inward">
                        <i class="fa-solid fa-file-import me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Bulk Import') }}</span>
                    </a>
                    @endhasPermission

                    @hasPermission('shop.gallery.index')
                    <a href="{{ route('shop.gallery.index') }}" class="command-item d-flex align-items-center px-3 py-2 rounded-3 text-decoration-none text-dark my-0.5" data-keywords="gallery import images media upload product photos">
                        <i class="fa-solid fa-images me-3 text-muted" style="width: 24px; text-align: center;"></i>
                        <span class="fw-semibold text-dark" style="font-size: 13px;">{{ __('Gallery Import') }}</span>
                    </a>
                    @endhasPermission
                </div>

                <!-- Empty State -->
                <div id="commandPaletteEmptyState" class="text-center py-5 d-none">
                    <i class="fa-solid fa-magnifying-glass text-muted mb-3" style="font-size: 32px; opacity: 0.5;"></i>
                    <h6 class="fw-bold text-dark mb-1">{{ __('No matching results found') }}</h6>
                    <p class="text-muted small m-0">{{ __('Try searching for "POS", "GST", "Inward", or "Orders"') }}</p>
                </div>

            </div>

            <!-- Footer Hints -->
            <div class="px-3 py-2.5 border-top bg-light-subtle d-flex align-items-center justify-content-between text-muted" style="font-size: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <span><kbd class="bg-white border text-dark px-1.5 py-0.5 rounded shadow-2xs">↑</kbd> <kbd class="bg-white border text-dark px-1.5 py-0.5 rounded shadow-2xs">↓</kbd> {{ __('Navigate') }}</span>
                    <span><kbd class="bg-white border text-dark px-1.5 py-0.5 rounded shadow-2xs">↵</kbd> {{ __('Select') }}</span>
                    <span><kbd class="bg-white border text-dark px-1.5 py-0.5 rounded shadow-2xs">ESC</kbd> {{ __('Close') }}</span>
                </div>
                <span class="fw-medium text-primary"><i class="fa-solid fa-bolt me-1"></i> Quick Spotlight</span>
            </div>

        </div>
    </div>
</div>

<style>
    .command-item {
        transition: all 0.12s ease-in-out;
        border: 1.5px solid transparent !important;
    }
    .command-item:hover, .command-item.active-item {
        background-color: #eff6ff !important;
        border-color: #2563eb !important;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.12) !important;
        cursor: pointer;
    }
    .command-item.active-item .fw-semibold {
        color: #1d4ed8 !important;
    }
    .command-item.active-item .command-icon {
        transform: scale(1.08);
        background-color: #2563eb !important;
        color: #ffffff !important;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modalElement = document.getElementById("commandPaletteModal");
        if (!modalElement) return;

        const searchInput = document.getElementById("commandPaletteSearchInput");
        const btnClear = document.getElementById("btnClearCommandSearch");
        const resultsContainer = document.getElementById("commandPaletteResultsContainer");
        const items = Array.from(document.querySelectorAll(".command-item"));
        const groups = Array.from(document.querySelectorAll(".command-group"));
        const emptyState = document.getElementById("commandPaletteEmptyState");

        let activeIndex = -1;

        // Initialize Bootstrap modal instance
        let paletteModal = null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            paletteModal = new bootstrap.Modal(modalElement, { keyboard: true });
        }

        const openPalette = () => {
            if (paletteModal) {
                paletteModal.show();
            } else {
                $(modalElement).modal('show');
            }
        };

        const closePalette = () => {
            if (paletteModal) {
                paletteModal.hide();
            } else {
                $(modalElement).modal('hide');
            }
        };

        // Get strictly visible matching items (checking Bootstrap d-none class)
        const getVisibleItems = () => {
            return items.filter((el) => {
                const group = el.closest(".command-group");
                const isGroupHidden = group && group.classList.contains("d-none");
                const isItemHidden = el.classList.contains("d-none");
                return !isItemHidden && !isGroupHidden;
            });
        };

        const setActiveIndex = (index) => {
            const visibleItems = getVisibleItems();
            items.forEach((el) => el.classList.remove("active-item"));

            if (visibleItems.length === 0) {
                activeIndex = -1;
                return;
            }

            if (index >= visibleItems.length) activeIndex = 0;
            else if (index < 0) activeIndex = visibleItems.length - 1;
            else activeIndex = index;

            const selected = visibleItems[activeIndex];
            if (selected) {
                selected.classList.add("active-item");
                selected.scrollIntoView({ block: "nearest", behavior: "smooth" });
            }
        };

        // Filter functionality
        const filterCommands = () => {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            if (query.length > 0 && btnClear) {
                btnClear.classList.remove('d-none');
            } else if (btnClear) {
                btnClear.classList.add('d-none');
            }

            items.forEach((item) => {
                const text = item.innerText.toLowerCase();
                const keywords = (item.getAttribute("data-keywords") || "").toLowerCase();

                if (query === "" || text.includes(query) || keywords.includes(query)) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            // Toggle category groups visibility using d-none class
            groups.forEach((group) => {
                const hasVisibleItem = Array.from(group.querySelectorAll(".command-item")).some(
                    (el) => !el.classList.contains('d-none')
                );
                if (hasVisibleItem) {
                    group.classList.remove('d-none');
                } else {
                    group.classList.add('d-none');
                }
            });

            if (visibleCount === 0) {
                emptyState.classList.remove('d-none');
                setActiveIndex(-1);
            } else {
                emptyState.classList.add('d-none');
                // Automatically highlight first visible matching result
                setActiveIndex(0);
            }
        };

        // Focus search input when modal opens
        modalElement.addEventListener('shown.bs.modal', function () {
            searchInput.focus();
            searchInput.select();
            filterCommands();
        });

        // Clear input button
        if (btnClear) {
            btnClear.addEventListener('click', function () {
                searchInput.value = '';
                filterCommands();
                searchInput.focus();
            });
        }

        searchInput.addEventListener("input", filterCommands);

        // Keyboard Navigation (ArrowUp, ArrowDown, Enter, Esc)
        const handleKeyDownNav = (e) => {
            if (["ArrowDown", "ArrowUp", "Enter", "Escape"].includes(e.key)) {
                const visibleItems = getVisibleItems();

                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    e.stopPropagation();
                    if (visibleItems.length === 0) return;
                    const nextIndex = (activeIndex < 0 || activeIndex >= visibleItems.length - 1) ? 0 : activeIndex + 1;
                    setActiveIndex(nextIndex);
                } else if (e.key === "ArrowUp") {
                    e.preventDefault();
                    e.stopPropagation();
                    if (visibleItems.length === 0) return;
                    const prevIndex = (activeIndex <= 0) ? visibleItems.length - 1 : activeIndex - 1;
                    setActiveIndex(prevIndex);
                } else if (e.key === "Enter") {
                    e.preventDefault();
                    e.stopPropagation();
                    if (visibleItems.length === 0) return;
                    const targetIndex = (activeIndex >= 0 && activeIndex < visibleItems.length) ? activeIndex : 0;
                    const targetUrl = visibleItems[targetIndex].getAttribute("href");
                    if (targetUrl && targetUrl !== "#" && targetUrl !== "javascript:void(0)") {
                        window.location.href = targetUrl;
                    }
                } else if (e.key === "Escape") {
                    e.preventDefault();
                    e.stopPropagation();
                    closePalette();
                }
            }
        };

        searchInput.addEventListener("keydown", handleKeyDownNav);
        modalElement.addEventListener("keydown", handleKeyDownNav);

        // Allow hover selection
        items.forEach((item) => {
            item.addEventListener("mouseenter", function () {
                const visibleItems = getVisibleItems();
                const idx = visibleItems.indexOf(item);
                if (idx !== -1) setActiveIndex(idx);
            });
        });

        // Global Hotkey Listener: Cmd+K, Ctrl+K, or Slash '/'
        document.addEventListener("keydown", function (e) {
            // Cmd+K or Ctrl+K
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === "k") {
                e.preventDefault();
                openPalette();
                return;
            }

            // Pressing '/' key when not inside an editable field
            if (e.key === "/" && !["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
                e.preventDefault();
                openPalette();
            }
        });

        // Bind header trigger buttons if present
        const searchBtn = document.getElementById("searchBtn");
        const btnGlobalSearch = document.getElementById("btn-global-command-search");

        if (searchBtn) {
            searchBtn.addEventListener("click", function (e) {
                e.preventDefault();
                openPalette();
            });
        }

        if (btnGlobalSearch) {
            btnGlobalSearch.addEventListener("click", function (e) {
                e.preventDefault();
                openPalette();
            });
        }
    });
</script>
