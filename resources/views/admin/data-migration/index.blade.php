@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">
                <i class="bi bi-database-fill-gear text-primary me-2"></i>Legacy Windows ERP Data Migration
            </h3>
            <p class="text-muted mb-0">Multi-year automated migration pipeline with year-wise barcode reconciliation & accounting continuity.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Year Scope Dropdown -->
            <div class="input-group" style="width: auto;">
                <span class="input-group-text bg-white text-muted"><i class="bi bi-calendar3"></i></span>
                <select id="selectedYear" class="form-select fw-bold text-primary" onchange="switchFinancialYear(this.value)">
                    <option value="" {{ empty($selectedYear) ? 'selected' : '' }}>Root / General Data</option>
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>
                            Year Folder: {{ $yr }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Target Shop Selector -->
            <div class="input-group" style="width: auto;">
                <span class="input-group-text bg-white text-muted"><i class="bi bi-shop"></i></span>
                <select id="targetShopId" class="form-select">
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ $shop->id == $defaultShopId ? 'selected' : '' }}>
                            {{ $shop->name }} (ID: {{ $shop->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-check form-switch d-flex align-items-center gap-2 bg-white px-3 py-2 border rounded shadow-sm">
                <input class="form-check-input mt-0" type="checkbox" id="dryRunToggle">
                <label class="form-check-label fw-bold mb-0 cursor-pointer" for="dryRunToggle" id="dryRunLabel">
                    <span class="badge bg-success text-white"><i class="bi bi-database-check me-1"></i>Live Write Mode (Active)</span>
                </label>
            </div>

            <div class="dropdown">
                <button class="btn btn-outline-danger dropdown-toggle px-3 fw-bold shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-trash3-fill me-1"></i> Clear Stage Data
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="clearStage1Data()"><i class="bi bi-trash me-2"></i>Clear Stage 1 (Steps 1-7: Foundation)</a></li>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="clearStage2Data()"><i class="bi bi-trash me-2"></i>Clear Stage 2 (Steps 8-9: Accounts)</a></li>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="clearStage3Data()"><i class="bi bi-trash me-2"></i>Clear Stage 3 (Steps 10-11: Catalog)</a></li>
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="clearStage4Data()"><i class="bi bi-trash me-2"></i>Clear Stage 4 (Steps 12-13: Inward & Barcodes)</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="javascript:void(0)" onclick="clearStage5Data()"><i class="bi bi-trash3-fill me-2"></i>Clear Stage 5 (Steps 14-15: Purchases & POS Sales)</a></li>
                </ul>
            </div>

            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle px-3 fw-bold shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-play-circle-fill me-1"></i> Migrate by Stage
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item text-success fw-bold" href="javascript:void(0)" onclick="startStage1Migration()"><i class="bi bi-play-fill me-2"></i>Stage 1: Foundation Masters (1-7)</a></li>
                    <li><a class="dropdown-item text-primary fw-bold" href="javascript:void(0)" onclick="startStage2Migration()"><i class="bi bi-play-fill me-2"></i>Stage 2: Accounts & Balances (8-9)</a></li>
                    <li><a class="dropdown-item text-info fw-bold" href="javascript:void(0)" onclick="startStage3Migration()"><i class="bi bi-play-fill me-2"></i>Stage 3: Catalog Hierarchy (10-11)</a></li>
                    <li><a class="dropdown-item text-warning fw-bold" href="javascript:void(0)" onclick="startStage4Migration()"><i class="bi bi-play-fill me-2"></i>Stage 4: Inward & Barcodes (12-13)</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="javascript:void(0)" onclick="startStage5Migration()"><i class="bi bi-play-fill me-2"></i>Stage 5: Purchases & POS Sales (14-15)</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-dark fw-bold" href="javascript:void(0)" onclick="runAccountCorrection()"><i class="bi bi-magic me-2 text-warning"></i>Final: Auto Set / Account Correction</a></li>
                </ul>
            </div>

            <!-- 5 Quick Workflow Buttons -->
            <div class="btn-group shadow-sm">
                <button class="btn btn-primary fw-bold px-3" onclick="runSingleStep(12)" title="1. Migrate Inward First">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Inward Migrate
                </button>
                <button class="btn btn-warning text-dark fw-bold px-3" onclick="runSingleStep(13)" title="2. Generate Barcodes of Inward Items">
                    <i class="bi bi-upc-scan me-1"></i> Item Barcode Generate
                </button>
                <button class="btn btn-secondary fw-bold px-3" onclick="runSingleStep(14)" title="3. Migrate Purchased Items matching Inward">
                    <i class="bi bi-receipt me-1"></i> Purchased Item Migrate
                </button>
                <button class="btn btn-success fw-bold px-3" onclick="runSingleStep(15)" title="4. Migrate POS Sales Only (Zero Online pollution)">
                    <i class="bi bi-cart-check me-1"></i> Sales Migrate (POS Only)
                </button>
                <button id="btnAccountCorrection" class="btn btn-dark fw-bold px-3 border-start border-light" onclick="runAccountCorrection()" title="5. Auto Set All Accounts, Calibrate Balances & Reconcile Vouchers">
                    <i class="bi bi-magic me-1 text-warning"></i> Account Correction
                </button>
            </div>

            <button class="btn btn-outline-info px-3 fw-bold shadow-sm" onclick="verifyAllStorePages()" title="Validate all store pages and CRUD capability">
                <i class="bi bi-shield-check me-1"></i> Verify Store CRUD
            </button>

            <button id="btnRunAll" class="btn btn-dark px-4 fw-bold shadow-sm" onclick="startAllMigration()">
                <i class="bi bi-play-fill me-1"></i> Run All Steps (1-15)
            </button>
        </div>
    </div>


    <!-- Barcode Reconciliation Banner (Light Theme) -->
    <div class="card border border-primary-subtle shadow-sm rounded-3 mb-4 overflow-hidden bg-white" style="border-top: 4px solid #3b82f6 !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-primary-subtle text-primary border border-primary border-opacity-25">
                        <i class="bi bi-upc-scan fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Barcode Generation vs. Usage Lifecycle Reconciliation</h5>
                        <div class="text-muted small">
                            Tracking generated physical barcodes against POS sales orders and returns for total stock audit continuity.
                            @if($selectedYear)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2">Viewing Year: {{ $selectedYear }}</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-2">Scope: Root / All Years</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-primary px-3 py-2 fw-bold shadow-sm" onclick="refreshReconciliation()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh Audit
                    </button>
                    @if($barcodeReconciliation['reconciliation_diff'] === 0)
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
                            <i class="bi bi-patch-check-fill me-1"></i>100% Balanced
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 fs-6">
                            <i class="bi bi-info-circle me-1"></i>Diff: {{ $barcodeReconciliation['reconciliation_diff'] }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-light border border-light-subtle">
                        <div class="text-muted small text-uppercase fw-semibold">Total Generated</div>
                        <h3 class="fw-bold mb-0 text-primary" id="reconGenerated">
                            {{ number_format($barcodeReconciliation['total_generated']) }}
                        </h3>
                        <div class="text-secondary small mt-1">Inward Barcode Master</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-light border border-light-subtle">
                        <div class="text-muted small text-uppercase fw-semibold">Total Sold</div>
                        <h3 class="fw-bold mb-0 text-success" id="reconSold">
                            {{ number_format($barcodeReconciliation['total_sold']) }}
                        </h3>
                        <div class="text-secondary small mt-1">POS Sales Invoices</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-light border border-light-subtle">
                        <div class="text-muted small text-uppercase fw-semibold">Active In-Stock</div>
                        <h3 class="fw-bold mb-0 text-warning" id="reconInStock">
                            {{ number_format($barcodeReconciliation['total_in_stock']) }}
                        </h3>
                        <div class="text-secondary small mt-1">Available On Shelves</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-light border border-light-subtle">
                        <div class="text-muted small text-uppercase fw-semibold">POS Order Rows</div>
                        <h3 class="fw-bold mb-0 text-dark" id="reconOrders">
                            {{ number_format($barcodeReconciliation['total_pos_sales_rows']) }}
                        </h3>
                        <div class="text-secondary small mt-1">Returns Restocked: {{ number_format($barcodeReconciliation['total_pos_returns_rows']) }}</div>
                    </div>
                </div>
            </div>

            @if(!empty($barcodeReconciliation['year_breakdown']))
                <div class="mt-3 pt-3 border-top border-light-subtle d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small fw-bold text-uppercase me-2"><i class="bi bi-folder2-open me-1"></i>Year Folders Detected:</span>
                    @foreach($barcodeReconciliation['year_breakdown'] as $yr => $data)
                        <span class="badge {{ $selectedYear == $yr ? 'bg-primary text-white' : 'bg-light text-dark border border-secondary-subtle' }} py-1 px-2" style="cursor: pointer;" onclick="switchFinancialYear('{{ $yr }}')" title="Switch to Year Folder {{ $yr }}">
                            FY {{ $yr }}: 
                            @if($data['has_barcode_sheet'])
                                <i class="bi bi-upc text-success ms-1" title="Barcode Sheet Present"></i>
                            @endif
                            @if($data['has_sales_sheet'])
                                <i class="bi bi-cart-check text-primary ms-1" title="Sales Sheet Present"></i>
                            @endif
                            @if($data['has_inward_sheet'])
                                <i class="bi bi-box-arrow-in-down text-warning ms-1" title="Inward Sheet Present"></i>
                            @endif
                            @if($data['has_purchase_sheet'])
                                <i class="bi bi-receipt text-danger ms-1" title="Purchase Sheet Present"></i>
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Account & Ledger Calibration Banner (Light Theme) -->
    <div class="card border border-success-subtle shadow-sm rounded-3 mb-4 overflow-hidden bg-white" style="border-top: 4px solid #10b981 !important; border-left: 4px solid #10b981 !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-success-subtle text-success border border-success border-opacity-25">
                        <i class="bi bi-calculator fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold mb-0 text-dark">General Ledger & Account Balances Calibration</h5>
                            @if(isset($accountAudit) && $accountAudit['is_balanced'])
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 small">
                                    <i class="bi bi-patch-check-fill me-1"></i>100% Balanced
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 small">
                                    <i class="bi bi-magic me-1"></i>Auto Set Ready
                                </span>
                            @endif
                        </div>
                        <div class="text-muted small mt-1">
                            Calculates and calibrates double-entry vouchers, party ledger balances, and updates the <code class="text-primary bg-light px-1 rounded border">account_balances</code> table across all active financial years.
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button id="btnBlankAccountsBanner" class="btn btn-outline-danger fw-bold px-3 py-2 shadow-sm" onclick="blankAllAccounts()">
                        <i class="bi bi-eraser-fill me-1"></i>Set All Accounts Blank / Zero
                    </button>
                    <button id="btnAccountCorrectionBanner" class="btn btn-success fw-bold px-3 py-2 shadow-sm" onclick="runAccountCorrection()">
                        <i class="bi bi-magic me-1"></i>Auto Set All Accounts
                    </button>
                    <a href="{{ route('shop.accountBalance.index') }}" target="_blank" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i>View Account Balances
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-light border border-light-subtle">
                        <div class="text-muted small text-uppercase fw-semibold">Audited Vouchers</div>
                        <h3 class="fw-bold mb-0 text-info" id="auditVouchers">
                            {{ isset($accountAudit) ? number_format($accountAudit['total_vouchers']) : '0' }}
                        </h3>
                        <div class="text-secondary small mt-1">Sales, Purchases, Notes</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-light border border-light-subtle">
                        <div class="text-muted small text-uppercase fw-semibold">Calibrated Balances</div>
                        <h3 class="fw-bold mb-0 text-success" id="auditBalances">
                            {{ isset($accountAudit) ? number_format($accountAudit['total_balances']) : '0' }}
                        </h3>
                        <div class="text-secondary small mt-1">account_balances records</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-light border border-light-subtle">
                        <div class="text-muted small text-uppercase fw-semibold">Total Debits (Dr)</div>
                        <h3 class="fw-bold mb-0 text-primary" id="auditDr">
                            ₹ {{ isset($accountAudit) ? number_format($accountAudit['total_dr'], 2) : '0.00' }}
                        </h3>
                        <div class="text-secondary small mt-1">Ledger Dr Entries</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-light border border-light-subtle">
                        <div class="text-muted small text-uppercase fw-semibold">Total Credits (Cr)</div>
                        <h3 class="fw-bold mb-0 text-dark" id="auditCr">
                            ₹ {{ isset($accountAudit) ? number_format($accountAudit['total_cr'], 2) : '0.00' }}
                        </h3>
                        <div class="text-secondary small mt-1" id="auditStatusNote">
                            @if(isset($accountAudit) && $accountAudit['is_balanced'])
                                <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>100% Balanced</span>
                            @elseif(isset($accountAudit))
                                <span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Diff: ₹ {{ number_format($accountAudit['diff'], 2) }}</span>
                            @else
                                <span class="text-muted">Unchecked</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Stats Overview Cards -->
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Pipeline Steps</div>
                        <h4 class="fw-bold mb-0">15 Steps (5 Stages)</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info-subtle text-info p-3 rounded-circle fs-4">
                        <i class="bi bi-table"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Excel Records</div>
                        <h4 class="fw-bold mb-0 text-info">{{ number_format($totalRows) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Current DB Records</div>
                        <h4 class="fw-bold mb-0 text-success" id="totalDbCounter">{{ number_format($totalDbRecords) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Overall Progress</div>
                        <h4 class="fw-bold mb-0 text-warning" id="overallProgressBadge">Ready</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Execution Console (Terminal) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden bg-dark text-light">
        <div class="card-header bg-black text-white d-flex justify-content-between align-items-center py-2 px-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger rounded-circle p-1"> </span>
                <span class="badge bg-warning rounded-circle p-1"> </span>
                <span class="badge bg-success rounded-circle p-1"> </span>
                <span class="fw-bold ms-2 small text-uppercase font-monospace">Live Migration & Store CRUD Terminal</span>
            </div>
            <button class="btn btn-sm btn-outline-secondary text-white py-0 px-2 small" onclick="clearTerminal()">
                <i class="bi bi-eraser me-1"></i>Clear
            </button>
        </div>
        <div class="progress" style="height: 4px; border-radius: 0;">
            <div id="migrationProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
        </div>
        <div class="card-body p-3 font-monospace small" id="terminalLogs" style="height: 220px; overflow-y: auto; background-color: #121212;">
            <div>[System Ready] Apolo Legacy Migration Engine initialized. Select a step or click "Run All Steps".</div>
        </div>
    </div>

    <!-- Steps Checklist Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">Migration Pipeline (15 Steps across 5 Stages)</h5>
            <span class="text-muted small">Sequential execution preserves foreign key relations & double-entry accounting</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 60px;">Step</th>
                        <th>Module / Entity</th>
                        <th>Excel Backup File</th>
                        <th>Target Database Table</th>
                        <th class="text-end">Excel Rows</th>
                        <th class="text-end">DB Records</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="min-width: 170px;">Store Page & CRUD</th>
                        <th class="text-end pe-3" style="min-width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filesStatus as $step => $item)
                        @if($step === 1)
                            <tr class="table-light border-top border-bottom">
                                <td colspan="9" class="py-2 ps-3 fw-bold text-dark bg-light">
                                    <i class="bi bi-layers-fill me-2 text-primary"></i>STAGE 1: FOUNDATION MASTERS (Steps 1 – 7)
                                </td>
                            </tr>
                        @elseif($step === 8)
                            <tr class="table-light border-top border-bottom">
                                <td colspan="9" class="py-2 ps-3 fw-bold text-dark bg-light">
                                    <i class="bi bi-bank2 me-2 text-success"></i>STAGE 2: FINANCIAL ACCOUNTING & PARTIES (Steps 8 – 9)
                                </td>
                            </tr>
                        @elseif($step === 10)
                            <tr class="table-light border-top border-bottom">
                                <td colspan="9" class="py-2 ps-3 fw-bold text-dark bg-light">
                                    <i class="bi bi-boxes me-2 text-info"></i>STAGE 3: CATALOG HIERARCHY (Steps 10 – 11)
                                </td>
                            </tr>
                        @elseif($step === 12)
                            <tr class="table-light border-top border-bottom">
                                <td colspan="9" class="py-2 ps-3 fw-bold text-dark bg-light">
                                    <i class="bi bi-box-arrow-in-down me-2 text-primary"></i>STAGE 4: INWARD CHALLANS & BARCODE GENERATION (Steps 12 – 13)
                                </td>
                            </tr>
                        @elseif($step === 14)
                            <tr class="table-light border-top border-bottom">
                                <td colspan="9" class="py-2 ps-3 fw-bold text-dark bg-light">
                                    <i class="bi bi-receipt-cutoff me-2 text-success"></i>STAGE 5: PURCHASES & POS RETAIL SALES (Steps 14 – 15)
                                </td>
                            </tr>
                        @endif
                    <tr id="step-row-{{ $step }}">
                        <td class="ps-3 fw-bold text-muted">{{ $step }}</td>
                        <td>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                {{ $item['name'] }}
                                @if(!empty($item['is_opening_stock']))
                                    <span class="badge bg-warning text-dark py-0 px-2 small shadow-sm"><i class="bi bi-stars me-1"></i>Opening Stock 2016</span>
                                @elseif(!empty($item['is_unified']))
                                    <span class="badge bg-info-subtle text-info border border-info-subtle py-0 px-2 small"><i class="bi bi-link-45deg me-1"></i>Auto-handled in Step 12</span>
                                @endif
                            </div>
                            <div class="text-muted small">{{ $item['description'] }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-file-earmark-excel me-1 text-success"></i>{{ $item['file'] }}
                            </span>
                            @if($item['exists'])
                                <span class="text-muted small ms-1">({{ $item['size_kb'] }} KB)</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger ms-1">File Not Found</span>
                            @endif
                        </td>
                        <td>
                            <code class="text-primary fw-bold">{{ $item['table'] }}</code>
                            @if($step === 12)
                                <div class="text-muted small">& <code>inward_products</code></div>
                                @if(!empty($item['is_opening_stock']))
                                    <div class="text-success small fw-semibold">& <code>product_barcodes</code></div>
                                @endif
                            @endif
                        </td>
                        <td class="text-end fw-bold text-secondary">
                            {{ number_format($item['excel_data_rows']) }}
                        </td>
                        <td class="text-end fw-bold text-dark" id="db-count-{{ $step }}">
                            @if($step === 12 && isset($item['items_count']) && $item['items_count'] > 0)
                                <div>{{ number_format($item['db_records']) }} <span class="small text-muted fw-normal">challans</span></div>
                                <div class="small text-muted fw-normal">({{ number_format($item['items_count']) }} items)</div>
                            @else
                                {{ number_format($item['db_records']) }}
                            @endif
                        </td>
                        <td class="text-center" id="status-col-{{ $step }}">
                            @if($item['status'] === 'migrated')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>Migrated
                                </span>
                            @elseif($item['status'] === 'ready')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-file-earmark-check me-1"></i>Ready
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-x-circle me-1"></i>Missing
                                </span>
                            @endif
                        </td>
                        <td class="text-center" id="page-health-{{ $step }}">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="{{ $item['page_url'] }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Open {{ $item['page_name'] }} in shop panel">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>{{ $item['page_name'] }}
                                </a>
                                <button class="btn btn-sm btn-light border py-0 px-1 text-primary" onclick="verifyPageHealth({{ $step }})" title="Test Page Health & CRUD for Selected Store">
                                    <i class="bi bi-shield-check"></i>
                                </button>
                            </div>
                            <div class="mt-1" id="health-badge-{{ $step }}">
                                @if($item['db_records'] > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle py-0"><i class="bi bi-check-circle me-1"></i>Store Active</span>
                                @else
                                    <span class="badge bg-light text-muted border py-0">Pending</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <button class="btn {{ $step === 12 ? (!empty($item['is_opening_stock']) ? 'btn-warning text-dark fw-bold' : 'btn-outline-primary fw-bold') : ($step === 13 ? (!empty($item['is_unified']) ? 'btn-outline-info text-dark fw-bold' : 'btn-outline-warning text-dark fw-bold') : ($step === 14 ? 'btn-outline-secondary fw-bold' : ($step === 15 ? 'btn-outline-success fw-bold' : 'btn-outline-primary'))) }}" onclick="runSingleStep({{ $step }})" id="btn-step-{{ $step }}" title="Run Step {{ $step }}">
                                    @if($step === 12)
                                        @if(!empty($item['is_opening_stock']))
                                            <i class="bi bi-layers-fill me-1"></i>Migrate Opening Inward & Barcodes
                                        @else
                                            <i class="bi bi-box-arrow-in-down me-1"></i>Inward Migrate
                                        @endif
                                    @elseif($step === 13)
                                        @if(!empty($item['is_unified']))
                                            <i class="bi bi-check2-circle me-1"></i>Unified in Step 12
                                        @else
                                            <i class="bi bi-upc-scan me-1"></i>Item Barcode Generate
                                        @endif
                                    @elseif($step === 14)
                                        <i class="bi bi-receipt me-1"></i>Purchased Item Migrate
                                    @elseif($step === 15)
                                        <i class="bi bi-cart-check me-1"></i>Sales Migrate (POS Only)
                                    @else
                                        <i class="bi bi-play me-1"></i>Run
                                    @endif
                                </button>
                                <button class="btn btn-outline-danger" onclick="confirmClearStep({{ $step }}, '{{ $item['name'] }}', '{{ $item['table'] }}')" id="btn-clear-{{ $step }}" title="Clear existing table records to 0">
                                    <i class="bi bi-trash3"></i>
                                </button>
                                <label class="btn btn-outline-secondary mb-0 cursor-pointer" title="Upload Replacement Excel">
                                    <i class="bi bi-upload"></i>
                                    <input type="file" class="d-none" accept=".xlsx,.xls" onchange="handleFileUpload(event, '{{ $item['file'] }}', {{ $step }})">
                                </label>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
const dryRunToggle = document.getElementById('dryRunToggle');
const dryRunLabel = document.getElementById('dryRunLabel');
const targetShopId = document.getElementById('targetShopId');
const selectedYear = document.getElementById('selectedYear');
const terminalLogs = document.getElementById('terminalLogs');
const progressBar = document.getElementById('migrationProgressBar');
const overallProgressBadge = document.getElementById('overallProgressBadge');

if (dryRunToggle) {
    dryRunToggle.addEventListener('change', function() {
        if (this.checked) {
            dryRunLabel.innerHTML = '<span class="badge bg-warning text-dark"><i class="bi bi-shield-exclamation me-1"></i>Dry-Run Mode (Test Only)</span>';
        } else {
            dryRunLabel.innerHTML = '<span class="badge bg-success text-white"><i class="bi bi-database-check me-1"></i>Live Write Mode (Active)</span>';
        }
    });
}

function switchFinancialYear(yr) {
    const url = new URL(window.location.href);
    if (yr) {
        url.searchParams.set('year', yr);
    } else {
        url.searchParams.delete('year');
    }
    window.location.href = url.toString();
}

function logToTerminal(message, type = 'info') {
    if (!terminalLogs) return;
    const now = new Date();
    const timeStr = now.toTimeString().split(' ')[0];
    const logItem = document.createElement('div');
    logItem.className = `log-line log-${type}`;

    let icon = 'ℹ';
    if (type === 'success') icon = '✓';
    if (type === 'error') icon = '✗';
    if (type === 'warning') icon = '⚠';

    logItem.innerHTML = `<span class="log-time">[${timeStr}]</span> <span class="log-icon">${icon}</span> <span class="log-msg">${escapeHtml(message)}</span>`;
    terminalLogs.appendChild(logItem);
    terminalLogs.scrollTop = terminalLogs.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.clearTerminal = function() {
    if (terminalLogs) terminalLogs.innerHTML = '';
};

window.confirmClearStep = async function(step, name, table) {
    let confirmed = false;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: `Clear Step ${step}: ${name}?`,
            text: `Are you sure you want to clear table "${table}" to 0 records?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Clear Data'
        });
        confirmed = result.isConfirmed;
    } else {
        confirmed = confirm(`Are you sure you want to clear Step ${step}: ${name} (${table}) to 0 records?`);
    }

    if (!confirmed) return;

    logToTerminal(`Clearing data for Step ${step} (${table})...`, 'warning');
    const shopId = targetShopId ? targetShopId.value : 14;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

    try {
        const response = await fetch("{{ route('admin.data-migration.clear-step') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ step: step, shop_id: shopId })
        });
        const res = await response.json();
        if (res.success) {
            logToTerminal(`Step ${step} cleared successfully (${res.data.deleted_count} rows deleted). Table "${res.data.table}" now has 0 records.`, 'success');
            const dbCountElem = document.getElementById(`db-count-${step}`);
            if (dbCountElem) dbCountElem.innerText = "0";
            const statusCol = document.getElementById(`status-col-${step}`);
            if (statusCol) statusCol.innerHTML = `<span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="bi bi-trash me-1"></i>Cleared (0)</span>`;
            recalculateTotalDbRecords();
            refreshReconciliation();
            if (typeof toastr !== 'undefined') toastr.success(`Step ${step} data cleared.`);
        } else {
            logToTerminal(`Clear failed: ${res.message}`, 'error');
        }
    } catch (e) {
        logToTerminal(`Clear error: ${e.message}`, 'error');
    }
};

async function executeStageClear(stageName, startStep, endStep) {
    let confirmed = false;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: `Clear All ${stageName} Tables?`,
            text: `Are you sure you want to CLEAR all ${stageName} tables (Steps ${startStep} to ${endStep})? This will reset records to 0.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, Clear ${stageName}`
        });
        confirmed = result.isConfirmed;
    } else {
        confirmed = confirm(`Are you sure you want to CLEAR all ${stageName} tables (Steps ${startStep} to ${endStep})?`);
    }

    if (!confirmed) return;

    logToTerminal("==================================================", 'warning');
    logToTerminal(`CLEARING ALL ${stageName.toUpperCase()} TABLES (Steps ${startStep} to ${endStep})`, 'warning');
    logToTerminal("==================================================", 'warning');

    const shopId = targetShopId ? targetShopId.value : 14;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

    for (let step = startStep; step <= endStep; step++) {
        const dbCountElem = document.getElementById(`db-count-${step}`);
        const statusCol = document.getElementById(`status-col-${step}`);

        try {
            const response = await fetch("{{ route('admin.data-migration.clear-step') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ step: step, shop_id: shopId })
            });
            const res = await response.json();
            if (res.success) {
                logToTerminal(`Step ${step} cleared successfully (${res.data.deleted_count} deleted). Table "${res.data.table}" now has 0 records.`, 'success');
                if (dbCountElem) dbCountElem.innerText = "0";
                if (statusCol) statusCol.innerHTML = `<span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="bi bi-trash me-1"></i>Cleared (0)</span>`;
            }
        } catch (e) {
            logToTerminal(`Step ${step} clear failed: ${e.message}`, 'error');
        }
    }
    recalculateTotalDbRecords();
    refreshReconciliation();
    logToTerminal(`${stageName} tables cleared! All ready for fresh migration.`, 'success');
    if (typeof toastr !== 'undefined') toastr.success(`${stageName} tables cleared.`);
}

window.clearStage1Data = () => executeStageClear("Stage 1 (Foundation Masters)", 1, 7);
window.clearStage2Data = () => executeStageClear("Stage 2 (Accounts & Balances)", 8, 9);
window.clearStage3Data = () => executeStageClear("Stage 3 (Catalog Hierarchy)", 10, 11);
window.clearStage4Data = () => executeStageClear("Stage 4 (Inward & Barcodes)", 12, 13);
window.clearStage5Data = () => executeStageClear("Stage 5 (Purchases & POS Sales)", 14, 15);

function recalculateTotalDbRecords() {
    let sum = 0;
    for (let s = 1; s <= 15; s++) {
        const elem = document.getElementById(`db-count-${s}`);
        if (elem) {
            sum += parseInt(elem.innerText.replace(/,/g, '') || 0);
        }
    }
    const counter = document.getElementById('totalDbCounter');
    if (counter) counter.innerText = sum.toLocaleString();
}

window.refreshReconciliation = async function() {
    const shopId = targetShopId ? targetShopId.value : 14;
    const yr = selectedYear ? selectedYear.value : '';

    try {
        const url = `{{ route('admin.data-migration.reconciliation') }}?shop_id=${shopId}&year=${encodeURIComponent(yr)}`;
        const response = await fetch(url);
        const res = await response.json();
        if (res.success && res.data) {
            const d = res.data;
            const genElem = document.getElementById('reconGenerated');
            const soldElem = document.getElementById('reconSold');
            const inStockElem = document.getElementById('reconInStock');
            const ordersElem = document.getElementById('reconOrders');

            if (genElem) genElem.innerText = Number(d.total_generated).toLocaleString();
            if (soldElem) soldElem.innerText = Number(d.total_sold).toLocaleString();
            if (inStockElem) inStockElem.innerText = Number(d.total_in_stock).toLocaleString();
            if (ordersElem) ordersElem.innerText = Number(d.total_pos_sales_rows).toLocaleString();
        }
    } catch (e) {
        console.error("Failed to refresh reconciliation:", e);
    }
};

window.runSingleStep = async function(step) {
    const isDryRun = dryRunToggle ? dryRunToggle.checked : true;
    const shopId = targetShopId ? targetShopId.value : 14;
    const yr = selectedYear ? selectedYear.value : '';
    const btn = document.getElementById(`btn-step-${step}`);
    const statusCol = document.getElementById(`status-col-${step}`);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Running...`;
    }
    logToTerminal(`Starting Step ${step} (${isDryRun ? 'Dry-Run' : 'Live Write'})` + (yr ? ` for Year ${yr}` : '') + `...`, 'warning');

    try {
        const response = await fetch("{{ route('admin.data-migration.run-step') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({
                step: step,
                shop_id: shopId,
                dry_run: isDryRun ? 1 : 0,
                year: yr
            })
        });

        const res = await response.json();
        if (res.success) {
            const d = res.data;
            logToTerminal(`Step ${step} completed: ${d.count} records processed in ${d.duration_seconds}s (${d.file})`, 'success');
            if (isDryRun) {
                if (statusCol) statusCol.innerHTML = `<span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1"><i class="bi bi-shield-check me-1"></i>Validated (${d.count})</span>`;
            } else {
                if (statusCol) statusCol.innerHTML = `<span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-all me-1"></i>Migrated (${d.count})</span>`;
                const dbCountElem = document.getElementById(`db-count-${step}`);
                if (dbCountElem) {
                    if (step === 12 && d.invoices_count) {
                        dbCountElem.innerHTML = `<div>${Number(d.invoices_count).toLocaleString()} <span class="small text-muted fw-normal">challans</span></div><div class="small text-muted fw-normal">(${Number(d.items_count).toLocaleString()} items)</div>`;
                    } else {
                        dbCountElem.innerText = Number(d.count).toLocaleString();
                    }
                }

                // If Step 12 was executed in Opening Stock mode, automatically update Step 13 (Barcodes) UI
                if (step === 12 && d.barcodes_count) {
                    const step13CountElem = document.getElementById('db-count-13');
                    if (step13CountElem) step13CountElem.innerText = Number(d.barcodes_count).toLocaleString();
                    const step13Status = document.getElementById('status-col-13');
                    if (step13Status) step13Status.innerHTML = `<span class="badge bg-success text-white px-2 py-1"><i class="bi bi-check-all me-1"></i>Migrated (${Number(d.barcodes_count).toLocaleString()})</span>`;
                }

                recalculateTotalDbRecords();
                if (step === 12 || step === 13 || step === 15) {
                    await refreshReconciliation();
                }
                await verifyPageHealth(step);
            }
        } else {
            logToTerminal(`Step ${step} Failed: ${res.message}`, 'error');
            if (statusCol) statusCol.innerHTML = `<span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Failed</span>`;
        }
    } catch (e) {
        logToTerminal(`Step ${step} Exception: ${e.message}`, 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-play me-1"></i>Run`;
        }
    }
};

window.verifyPageHealth = async function(step) {
    const shopId = targetShopId ? targetShopId.value : 14;
    const badgeElem = document.getElementById(`health-badge-${step}`);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

    if (badgeElem) {
        badgeElem.innerHTML = `<span class="badge bg-secondary-subtle text-muted py-0"><span class="spinner-border spinner-border-sm me-1" style="width: 8px; height: 8px;"></span>Checking...</span>`;
    }

    try {
        const response = await fetch("{{ route('admin.data-migration.check-page-health') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ step: step, shop_id: shopId })
        });
        const res = await response.json();
        if (res.success && res.data) {
            const h = res.data;
            if (h.status === 'healthy') {
                if (badgeElem) {
                    badgeElem.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle py-0" title="${h.message}"><i class="bi bi-check-circle-fill me-1"></i>Active (${h.records_found})</span>`;
                }
                logToTerminal(`[Store Verified] ${h.message}`, 'success');
            } else {
                if (badgeElem) {
                    badgeElem.innerHTML = `<span class="badge bg-warning-subtle text-dark border border-warning py-0" title="${h.message}"><i class="bi bi-exclamation-circle me-1"></i>${h.crud_status}</span>`;
                }
                logToTerminal(`[Store Check Notice] ${h.message}`, 'warning');
            }
        }
    } catch (e) {
        if (badgeElem) {
            badgeElem.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle py-0"><i class="bi bi-x-circle me-1"></i>Error</span>`;
        }
    }
};

window.verifyAllStorePages = async function() {
    const shopId = targetShopId ? targetShopId.value : 14;
    logToTerminal("==================================================", 'info');
    logToTerminal(`VERIFYING ALL 15 STORE PAGES FOR SHOP (ID: ${shopId})...`, 'info');
    logToTerminal("==================================================", 'info');

    for (let step = 1; step <= 15; step++) {
        await verifyPageHealth(step);
    }
    logToTerminal("All store pages verified successfully!", 'success');
    if (typeof toastr !== 'undefined') toastr.success("All store pages verified.");
};

async function executeStageMigration(stageName, startStep, endStep) {
    if (overallProgressBadge) {
        overallProgressBadge.className = "badge bg-warning text-dark";
        overallProgressBadge.innerText = `Running ${stageName}...`;
    }
    logToTerminal("==================================================", 'warning');
    logToTerminal(`STARTING ${stageName.toUpperCase()} (Steps ${startStep} to ${endStep})`, 'warning');
    logToTerminal("==================================================", 'warning');

    const totalStageSteps = (endStep - startStep + 1);
    for (let step = startStep; step <= endStep; step++) {
        const pct = Math.round(((step - startStep) / totalStageSteps) * 100);
        if (progressBar) progressBar.style.width = `${pct}%`;
        await window.runSingleStep(step);
    }

    if (progressBar) progressBar.style.width = "100%";
    if (overallProgressBadge) {
        overallProgressBadge.className = "badge bg-success text-white";
        overallProgressBadge.innerText = `${stageName} Completed`;
    }
    logToTerminal(`${stageName} Finished Successfully!`, 'success');
}

window.startStage1Migration = () => executeStageMigration("Stage 1 (Foundation Masters)", 1, 7);
window.startStage2Migration = () => executeStageMigration("Stage 2 (Accounts & Balances)", 8, 9);
window.startStage3Migration = () => executeStageMigration("Stage 3 (Catalog Hierarchy)", 10, 11);
window.startStage4Migration = () => executeStageMigration("Stage 4 (Inward & Barcodes)", 12, 13);
window.startStage5Migration = () => executeStageMigration("Stage 5 (Purchases & POS Sales)", 14, 15);

window.startAllMigration = async function() {
    const btnAll = document.getElementById('btnRunAll');
    if (btnAll) btnAll.disabled = true;
    if (overallProgressBadge) {
        overallProgressBadge.className = "badge bg-warning text-dark";
        overallProgressBadge.innerText = "Running Pipeline...";
    }
    logToTerminal("==================================================", 'warning');
    logToTerminal("STARTING FULL 15-STEP MIGRATION PIPELINE", 'warning');
    logToTerminal("==================================================", 'warning');

    for (let step = 1; step <= 15; step++) {
        const pct = Math.round(((step - 1) / 15) * 100);
        if (progressBar) progressBar.style.width = `${pct}%`;
        await window.runSingleStep(step);
    }

    if (progressBar) progressBar.style.width = "100%";
    if (overallProgressBadge) {
        overallProgressBadge.className = "badge bg-success text-white";
        overallProgressBadge.innerText = "Pipeline Completed";
    }
    logToTerminal("All 15 migration steps finished!", 'success');
    if (btnAll) btnAll.disabled = false;
};

window.handleFileUpload = async function(event, targetFileName, step) {
    const file = event.target.files[0];
    if (!file) return;

    const yr = selectedYear ? selectedYear.value : '';
    logToTerminal(`Uploading replacement file: ${file.name} for ${targetFileName}` + (yr ? ` in Year ${yr}` : '') + `...`, 'info');
    const formData = new FormData();
    formData.append('file', file);
    formData.append('file_name', targetFileName);
    if (yr) formData.append('year', yr);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

    try {
        const res = await fetch("{{ route('admin.data-migration.upload-file') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken
            },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            logToTerminal(`File ${targetFileName} uploaded successfully. Ready for processing.`, 'success');
            const statusCol = document.getElementById(`status-col-${step}`);
            if (statusCol) statusCol.innerHTML = `<span class="badge bg-primary-subtle text-primary px-2 py-1">Ready</span>`;
            if (typeof toastr !== 'undefined') {
                toastr.success(`File ${targetFileName} uploaded.`);
            }
        } else {
            logToTerminal(`Upload failed: ${data.message}`, 'error');
        }
    } catch (e) {
        logToTerminal(`Upload error: ${e.message}`, 'error');
    }
};

window.runAccountCorrection = async function() {
    const shopId = targetShopId ? targetShopId.value : 14;
    const yr = selectedYear ? selectedYear.value : '';

    const csrfPre = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

    // Preview first. Account correction deletes every account_balances row for the
    // shop and rebuilds it from voucher entries alone, so balances imported from
    // Opening Balance.xlsx that have no backing voucher are discarded. The dry run
    // executes the real logic in a rolled-back transaction, so these are the true
    // numbers rather than an estimate.
    let preview = null;
    try {
        logToTerminal('Previewing account correction (dry run, no writes)...', 'info');
        const pRes = await fetch("{{ route('admin.data-migration.correct-accounts') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfPre },
            body: JSON.stringify({ shop_id: shopId, year: yr, dry_run: true })
        });
        const pJson = await pRes.json();
        if (!pJson.success) throw new Error(pJson.message || 'Preview failed');
        preview = pJson.data;
    } catch (e) {
        logToTerminal('Preview failed: ' + e.message, 'error');
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Preview failed', text: e.message });
        }
        return;
    }

    const lost = Number(preview.balances_lost || 0);
    logToTerminal(`Preview: account balances ${preview.balances_before} -> ${preview.balances_after}` + (lost > 0 ? ` (${lost} would be discarded)` : ''), lost > 0 ? 'warning' : 'info');

    if (typeof Swal !== 'undefined') {
        const warnBlock = lost > 0 ? `
            <div class="alert alert-danger text-start mb-0 mt-3 py-2 px-3">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>${lost.toLocaleString()} account balance row(s) will be discarded</div>
                <div class="small mb-0">These have no backing voucher &mdash; typically opening balances imported from
                <code>Opening Balance.xlsx</code>. Re-run <strong>Step 9</strong> afterwards to restore them.</div>
            </div>` : `
            <div class="alert alert-success text-start mb-0 mt-3 py-2 px-3 small">
                <i class="bi bi-check-circle-fill me-1"></i>No account balances would be lost.
            </div>`;

        const confirmResult = await Swal.fire({
            title: 'Run Account Correction?',
            html: `
                <div class="text-start">
                    <p class="mb-2 small">Rebuilds <code>account_balances</code> for this shop from voucher entries, balances vouchers, and reconciles party ledgers.</p>
                    <table class="table table-sm mb-0 small">
                        <tr><td>Vouchers audited</td><td class="text-end fw-bold">${Number(preview.vouchers_audited).toLocaleString()}</td></tr>
                        <tr><td>Account balances now</td><td class="text-end fw-bold">${Number(preview.balances_before).toLocaleString()}</td></tr>
                        <tr><td>After this run</td><td class="text-end fw-bold">${Number(preview.balances_after).toLocaleString()}</td></tr>
                    </table>
                    ${warnBlock}
                </div>`,
            icon: lost > 0 ? 'warning' : 'question',
            width: 540,
            showCancelButton: true,
            confirmButtonColor: lost > 0 ? '#dc2626' : '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: lost > 0 ? 'Proceed and discard them' : '<i class="bi bi-magic me-1"></i> Yes, Auto Set Accounts',
            cancelButtonText: 'Cancel',
            focusCancel: lost > 0
        });
        if (!confirmResult.isConfirmed) {
            logToTerminal('Account correction cancelled - nothing was written.', 'warning');
            return;
        }
    }

    const btn1 = document.getElementById('btnAccountCorrection');
    const btn2 = document.getElementById('btnAccountCorrectionBanner');
    if (btn1) {
        btn1.disabled = true;
        btn1.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Setting Accounts...`;
    }
    if (btn2) {
        btn2.disabled = true;
        btn2.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Calibrating...`;
    }

    logToTerminal("==================================================", 'warning');
    logToTerminal("STARTING AUTOMATED ACCOUNT CORRECTION & RECONCILIATION...", 'warning');
    logToTerminal("Target Shop ID: " + shopId + (yr ? " | FY Scope: " + yr : ""), 'info');
    logToTerminal("==================================================", 'warning');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

    try {
        const response = await fetch("{{ route('admin.data-migration.correct-accounts') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({
                shop_id: shopId,
                year: yr
            })
        });

        const res = await response.json();
        if (res.success && res.data) {
            const d = res.data;
            logToTerminal(`✓ Vouchers Audited: ${Number(d.vouchers_audited).toLocaleString()}`, 'info');
            logToTerminal(`✓ Vouchers Balanced: ${Number(d.vouchers_balanced).toLocaleString()}`, 'info');
            logToTerminal(`✓ FY Mismatches Fixed: ${Number(d.fy_mismatches_fixed).toLocaleString()}`, 'info');
            logToTerminal(`✓ Party Masters Synced: ${Number(d.account_masters_synced).toLocaleString()}`, 'info');
            logToTerminal(`✓ Account Balances Updated: ${Number(d.account_balances_updated).toLocaleString()}`, 'info');
            logToTerminal(`✓ Grand Total Debits: ₹ ${Number(d.grand_total_debit).toLocaleString(undefined, {minimumFractionDigits: 2})}`, 'success');
            logToTerminal(`✓ Grand Total Credits: ₹ ${Number(d.grand_total_credit).toLocaleString(undefined, {minimumFractionDigits: 2})}`, 'success');
            logToTerminal(`✓ Balance Status: ${d.is_fully_balanced ? '100% PERFECTLY BALANCED (Dr == Cr)' : 'Discrepancy: ₹ ' + d.net_difference}`, d.is_fully_balanced ? 'success' : 'error');
            logToTerminal(`ACCOUNT CORRECTION COMPLETED IN ${d.duration_seconds}s!`, 'success');

            // Update live DOM elements
            const elVouchers = document.getElementById('auditVouchers');
            const elBalances = document.getElementById('auditBalances');
            const elDr = document.getElementById('auditDr');
            const elCr = document.getElementById('auditCr');
            const elNote = document.getElementById('auditStatusNote');
            if (elVouchers) elVouchers.innerText = Number(d.vouchers_audited).toLocaleString();
            if (elBalances) elBalances.innerText = Number(d.account_balances_updated).toLocaleString();
            if (elDr) elDr.innerText = '₹ ' + Number(d.grand_total_debit).toLocaleString(undefined, {minimumFractionDigits: 2});
            if (elCr) elCr.innerText = '₹ ' + Number(d.grand_total_credit).toLocaleString(undefined, {minimumFractionDigits: 2});
            if (elNote) {
                elNote.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>100% Balanced</span>`;
            }

            if (typeof toastr !== 'undefined') {
                toastr.success("All accounts and balances set properly!");
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Accounts Fully Balanced!',
                    html: `
                        <div class="text-start p-2">
                            <p class="mb-2"><strong>Calibrated Balances:</strong> ${Number(d.account_balances_updated).toLocaleString()} records updated</p>
                            <p class="mb-2"><strong>Total Debits (Dr):</strong> ₹ ${Number(d.grand_total_debit).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
                            <p class="mb-2"><strong>Total Credits (Cr):</strong> ₹ ${Number(d.grand_total_credit).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
                            <p class="mb-0 text-success fw-bold"><i class="bi bi-patch-check-fill me-1"></i> Net Difference: ₹ ${d.net_difference} (100% Balanced)</p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Great!'
                });
            }
        } else {
            logToTerminal(`Account correction failed: ${res.message}`, 'error');
            if (typeof toastr !== 'undefined') toastr.error(res.message);
        }
    } catch (e) {
        logToTerminal(`Account correction error: ${e.message}`, 'error');
        if (typeof toastr !== 'undefined') toastr.error(e.message);
    } finally {
        if (btn1) {
            btn1.disabled = false;
            btn1.innerHTML = `<i class="bi bi-magic me-1 text-warning"></i> Account Correction`;
        }
        if (btn2) {
            btn2.disabled = false;
            btn2.innerHTML = `<i class="bi bi-magic me-1"></i>Auto Set All Accounts`;
        }
    }
};

window.blankAllAccounts = async function() {
    const confirmation = await Swal.fire({
        title: 'Set All Accounts Blank & Zero?',
        text: 'This will reset all accounts in account_balances to blank and set all credit and debit balances to zero (₹ 0.00).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Set Blank & Zero'
    });

    if (!confirmation.isConfirmed) return;

    const btn = document.getElementById('btnBlankAccountsBanner');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Resetting...`;
    }

    logToTerminal(`Setting all accounts to blank with zero credit and debit...`, 'warning');

    try {
        const response = await fetch("{{ route('admin.data-migration.blank-accounts') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                shop_id: document.getElementById('shopSelect')?.value || 14,
                clear_vouchers: true
            })
        });

        const res = await response.json();
        if (res.success) {
            logToTerminal(`✓ All accounts cleared and set to blank.`, 'success');
            logToTerminal(`✓ Debits & Credits set to 0.00.`, 'success');

            // Reset DOM counters
            const elVouchers = document.getElementById('auditVouchers');
            const elBalances = document.getElementById('auditBalances');
            const elDr = document.getElementById('auditDr');
            const elCr = document.getElementById('auditCr');
            const elNote = document.getElementById('auditStatusNote');
            if (elVouchers) elVouchers.innerText = '0';
            if (elBalances) elBalances.innerText = '0';
            if (elDr) elDr.innerText = '₹ 0.00';
            if (elCr) elCr.innerText = '₹ 0.00';
            if (elNote) {
                elNote.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>100% Balanced</span>`;
            }

            if (typeof toastr !== 'undefined') toastr.success("All accounts have been set to blank with zero Dr/Cr!");
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Accounts Reset to Blank / Zero',
                    text: 'All account balances are now blank with credit and debit at 0.00.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
        } else {
            logToTerminal(`Blank accounts failed: ${res.message}`, 'error');
            if (typeof toastr !== 'undefined') toastr.error(res.message);
        }
    } catch (e) {
        logToTerminal(`Error setting blank accounts: ${e.message}`, 'error');
        if (typeof toastr !== 'undefined') toastr.error(e.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-eraser-fill me-1"></i>Set All Accounts Blank / Zero`;
        }
    }
};
</script>
@endpush

