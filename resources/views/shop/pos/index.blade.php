@extends('layouts.app')

@section('header-title', __('POS'))

@section('content')
<style>
    /* Full Screen Page Overrides to hide main sidebar and headers */
    .app-header {
        display: none !important;
    }
    .app-sidebar {
        display: none !important;
    }
    .app-main {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    .app-container.fixed-header {
        padding-top: 0 !important;
    }
    .app-main-outer {
        padding-left: 0 !important;
        padding-top: 0 !important;
        margin-left: 0 !important;
        width: 100% !important;
    }
    .app-main .app-main-inner {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100vw !important;
        width: 100% !important;
    }
    .app-page-title {
        display: none !important;
    }
    .app-wrapper-footer {
        display: none !important;
    }

    /* GRetail Sleek Theme Styling */
    .gretail-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: #f4f6f9;
        font-size: 13px;
        color: #333;
        padding: 8px !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }

    @media (min-width: 992px) {
        .gretail-container {
            height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            overflow: hidden !important;
        }
        .grid-card-main {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            min-height: 0 !important;
            margin-bottom: 8px !important;
        }
        .grid-card-main .card-body {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .grid-card-main .table-responsive-grid {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            margin-bottom: 0 !important;
        }
        .bottom-panels-row {
            flex: 0 0 auto !important;
        }
    }

    @media (max-width: 991.98px) {
        .gretail-container {
            height: auto !important;
            overflow: auto !important;
            display: block !important;
        }
        .grid-card-main {
            min-height: 350px !important;
            margin-bottom: 15px !important;
        }
        .table-responsive-grid {
            max-height: 400px !important;
            overflow-y: auto !important;
        }
    }
    .erp-header-panel {
        background: rgb(20 28 38 / 69%);
        backdrop-filter: blur(18px) saturate(180%);
        -webkit-backdrop-filter: blur(18px) saturate(180%);
        border: 1px solid rgba(255,255,255,0.12);
        color: #fff;
        padding: 8px 15px;
        border-radius: 8px;
        margin-bottom: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.06) inset;
        min-height: 52px;
    }
    .erp-header-panel input, .erp-header-panel select {
        background: rgba(255, 255, 255, 0.15) !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        color: #fff !important;
        font-weight: 600;
        height: 32px;
        font-size: 13px;
    }
    .erp-header-panel input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }
    .erp-clock {
        font-size: 13px;
        font-weight: 700;
        background: rgba(0,0,0,0.2);
        padding: 4px 12px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }
    .grid-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e1e6eb;
        margin-bottom: 15px;
    }
    .grid-card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e1e6eb;
        padding: 8px 15px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        color: #495057;
    }
    .table-responsive-grid {
        overflow-x: auto;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin-bottom: 10px;
    }
    .sales-grid-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }
    .sales-grid-table th {
        background: #e9ecef;
        color: #495057;
        font-weight: 700;
        font-size: 12px;
        text-align: center;
        padding: 8px 5px;
        border: 1px solid #dee2e6;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .sales-grid-table td {
        padding: 2px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
        background: #fff;
    }
    .sales-grid-table tr:hover td {
        background-color: #f1f3f5;
    }
    .sales-grid-table input, .sales-grid-table select {
        border: 0;
        outline: none;
        background: transparent;
        width: 100%;
        padding: 6px 4px;
        font-size: 13px;
        color: #333;
        transition: all 0.15s;
    }
    .sales-grid-table input:focus, .sales-grid-table select:focus {
        background: #fff;
        box-shadow: inset 0 0 0 2px #007bff;
        border-radius: 4px;
    }
    .sales-grid-table input[readonly] {
        background-color: #e9ecef;
        color: #6c757d;
        pointer-events: none;
    }
    .btn-grid-delete {
        color: #dc3545;
        background: none;
        border: 0;
        cursor: pointer;
        padding: 2px 8px;
    }
    .btn-grid-delete:hover {
        color: #a71d2a;
    }
    /* Bottom Panels */
    /* Bottom Panels */
    .bottom-panel-card {
        height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border-radius: 14px !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .bottom-panel-card:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 10px 30px -10px rgba(59, 130, 246, 0.08), 0 1px 5px rgba(59, 130, 246, 0.04) !important;
    }
    .bottom-panel-card .form-control:focus, .bottom-panel-card .form-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        background-color: #fff !important;
    }
    .bottom-tab-content {
        padding: 4px 5px !important;
    }
    .bottom-panel-card .card-body {
        padding: 8px 12px !important;
        flex: 1 1 auto !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        min-height: 0 !important;
    }
    .bottom-panel-card .form-control, .bottom-panel-card .form-select {
        height: 28px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 3px 8px !important;
        transition: all 0.15s !important;
    }
    /* Input Group & Attached Side Buttons styling */
    .bottom-panel-card .input-group {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }
    .bottom-panel-card .input-group > .form-control,
    .bottom-panel-card .input-group > .form-select {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-right: 0 !important;
    }
    .bottom-panel-card .input-group > .input-group-text {
        height: 28px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        background-color: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        border-right: 0 !important;
        border-top-left-radius: 6px !important;
        border-bottom-left-radius: 6px !important;
        padding: 3px 6px !important;
        color: #475569 !important;
    }
    .bottom-panel-card .input-group > .input-group-text + .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    .bottom-panel-card .input-group > .btn {
        height: 28px !important;
        padding: 0 8px !important;
        font-size: 11px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid #cbd5e1 !important;
        border-top-right-radius: 6px !important;
        border-bottom-right-radius: 6px !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        background-color: #f1f5f9 !important;
        color: #2563eb !important;
        transition: all 0.15s ease-in-out !important;
    }
    .bottom-panel-card .input-group > .btn:hover {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }
    .bottom-panel-card label {
        font-size: 10.5px !important;
        font-weight: 700 !important;
        color: #475569 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        margin-bottom: 2px !important;
    }
    .bottom-panel-card .nav-tabs {
        background: #f1f5f9 !important;
        border-radius: 8px !important;
        padding: 2px !important;
        border-bottom: none !important;
        display: flex !important;
        gap: 2px !important;
        flex-wrap: nowrap !important;
    }
    .bottom-panel-card .nav-tabs .nav-link {
        font-weight: 700 !important;
        font-size: 10px !important;
        padding: 4px 6px !important;
        text-transform: uppercase !important;
        border: none !important;
        background: transparent !important;
        color: #64748b !important;
        border-radius: 6px !important;
        transition: all 0.15s !important;
        flex-grow: 1 !important;
        text-align: center !important;
        white-space: nowrap !important;
    }
    .bottom-panel-card .nav-tabs .nav-link:hover {
        color: #1e293b !important;
        background-color: rgba(255,255,255,0.4) !important;
    }
    .bottom-panel-card .nav-tabs .nav-link.active {
        background: #ffffff !important;
        color: #2563eb !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
    }
    /* Key Shortcut Badges styling */
    .pos-kbd-badge {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        border-bottom: 2px solid #94a3b8 !important;
        font-family: inherit !important;
        font-weight: 800 !important;
        padding: 1.5px 4px !important;
        border-radius: 4px !important;
        font-size: 9px !important;
        margin-left: 6px !important;
        display: inline-block !important;
        vertical-align: middle !important;
        box-shadow: 0 1px 0 rgba(0,0,0,0.05) !important;
        font-weight: 800 !important;
        letter-spacing: normal !important;
        text-transform: none !important;
        line-height: 1 !important;
    }
    .pos-kbd-inline {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        border-bottom: 2px solid #94a3b8 !important;
        font-family: inherit !important;
        font-weight: 800 !important;
        padding: 1.5px 4px !important;
        border-radius: 4px !important;
        font-size: 9px !important;
        margin-left: 4px !important;
        display: inline-block !important;
        vertical-align: middle !important;
        box-shadow: 0 1px 0 rgba(0,0,0,0.05) !important;
        line-height: 1 !important;
    }
    /* Large Bill amount display */
    .bill-amount-display {
        background: linear-gradient(135deg, rgba(254, 243, 199, 0.45) 0%, rgba(254, 243, 199, 0.85) 100%) !important;
        border: 1px solid rgba(252, 211, 77, 0.6) !important;
        border-radius: 10px !important;
        padding: 6px 10px;
        text-align: center;
        margin-top: 6px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.4), 0 4px 10px rgba(217, 119, 6, 0.04) !important;
        transition: transform 0.2s ease-in-out;
    }
    .bill-amount-display:hover {
        transform: translateY(-1px);
    }
    .bill-amount-label {
        font-weight: 700;
        color: #d97706;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bill-amount-value {
        font-size: 28px;
        font-weight: 800;
        color: #b91c1c;
        margin: 2px 0 0 0;
        line-height: 1;
    }
    /* Large Cash Due display */
    .cash-received-display {
        background: linear-gradient(135deg, rgba(239, 246, 255, 0.45) 0%, rgba(219, 234, 254, 0.85) 100%) !important;
        border: 1px solid rgba(191, 219, 254, 0.7) !important;
        border-radius: 10px !important;
        padding: 6px 10px;
        text-align: center;
        margin-top: 6px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.4), 0 4px 10px rgba(37, 99, 235, 0.04) !important;
        transition: transform 0.2s ease-in-out;
    }
    .cash-received-display:hover {
        transform: translateY(-1px);
    }
    .cash-received-label {
        font-weight: 700;
        color: #2563eb;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .cash-received-value {
        font-size: 28px;
        font-weight: 800;
        color: #1d4ed8;
        margin: 2px 0 0 0;
        line-height: 1;
    }

    /* Glassmorphic Control Buttons */
    .glass-btn-base {
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        padding: 6px 4px !important;
        transition: all 0.2s ease-in-out !important;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04), inset 0 1px 0 rgba(255,255,255,0.4) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        text-shadow: none !important;
    }
    .glass-btn-base i {
        font-size: 15px !important;
        margin-bottom: 3px !important;
    }
    .glass-btn-base span {
        margin-bottom: 1px !important;
    }
    .glass-btn-base .badge {
        font-size: 8.5px !important;
        padding: 1.5px 4.5px !important;
        border-radius: 3px !important;
        font-weight: 700 !important;
    }

    /* Return Glass button */
    .glass-btn-return {
        background: rgba(108, 117, 125, 0.12) !important;
        color: #495057 !important;
        border: 1px solid rgba(108, 117, 125, 0.25) !important;
    }
    .glass-btn-return:hover {
        background: rgba(108, 117, 125, 0.22) !important;
        transform: translateY(-2px);
    }

    /* Hold Glass button */
    .glass-btn-hold {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #b45309 !important;
        border: 1px solid rgba(245, 158, 11, 0.25) !important;
    }
    .glass-btn-hold:hover {
        background: rgba(245, 158, 11, 0.22) !important;
        transform: translateY(-2px);
    }

    /* Hold List Glass button */
    .glass-btn-hold-list {
        background: rgba(6, 182, 212, 0.12) !important;
        color: #0891b2 !important;
        border: 1px solid rgba(6, 182, 212, 0.25) !important;
    }
    .glass-btn-hold-list:hover {
        background: rgba(6, 182, 212, 0.22) !important;
        transform: translateY(-2px);
    }

    /* Calculator Glass button */
    .glass-btn-calc {
        background: rgba(30, 41, 59, 0.08) !important;
        color: #334155 !important;
        border: 1px solid rgba(30, 41, 59, 0.2) !important;
    }
    .glass-btn-calc:hover {
        background: rgba(30, 41, 59, 0.18) !important;
        transform: translateY(-2px);
    }

    /* Cancel Glass button */
    .glass-btn-cancel {
        background: rgba(239, 68, 68, 0.12) !important;
        color: #b91c1c !important;
        border: 1px solid rgba(239, 68, 68, 0.25) !important;
    }
    .glass-btn-cancel:hover {
        background: rgba(239, 68, 68, 0.22) !important;
        transform: translateY(-2px);
    }

    /* Print & Save Glass button */
    .glass-btn-print {
        background: rgba(16, 185, 129, 0.12) !important;
        color: #047857 !important;
        border: 1px solid rgba(16, 185, 129, 0.25) !important;
    }
    .glass-btn-print:hover {
        background: rgba(16, 185, 129, 0.22) !important;
        transform: translateY(-2px);
    }

    /* Tactile Keyboard Key Badges */
    .kbd-shortcut-badge {
        background: #f1f5f9 !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
        border-bottom: 2.5px solid #94a3b8 !important;
        font-family: inherit !important;
        font-weight: 800 !important;
        padding: 3px 6px !important;
        border-radius: 5px !important;
        font-size: 10.5px !important;
        margin-right: 6px !important;
        box-shadow: 0 1.5px 0 rgba(0,0,0,0.06) !important;
        display: inline-block !important;
        line-height: 1 !important;
    }

    /* Symmetrical Action Key Shortcuts Footer */
    .shortcuts-footer {
        background: rgb(20 28 38 / 69%);
        backdrop-filter: blur(18px) saturate(180%);
        -webkit-backdrop-filter: blur(18px) saturate(180%);
        border: 1px solid rgba(255,255,255,0.12);
        color: #fff;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.06) inset;
        min-height: 52px;
    }
    .shortcut-badge {
        background: rgba(255, 255, 255, 0.15);
        color: #fab005;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        margin-right: 4px;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    /* Lookup Product Modal & Active navigation styling */
    .lookup-results-list {
        max-height: 300px;
        overflow-y: auto;
    }
    .lookup-item-row {
        cursor: pointer;
        transition: background 0.15s;
    }
    .lookup-item-row:hover {
        background-color: #f1f3f5;
    }
    .lookup-item-row.selected-active td {
        background-color: #0d6efd !important;
        color: #ffffff !important;
    }
    tr {
        display: table-row !important;
        opacity: 1 !important;
        animation: none !important;
    }

    /* ── Counter Selection Overlay ─────────────────────────── */
    #pos-counter-overlay {
        position: fixed; inset: 0; z-index: 1100000;
        background: rgba(5, 8, 18, 0.88);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 24px;
    }
    .counter-select-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(32px) saturate(200%);
        -webkit-backdrop-filter: blur(32px) saturate(200%);
        border: 1px solid rgba(255,255,255,1);
        box-shadow: 0 24px 64px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.8) inset;
        border-radius: 20px;
        padding: 32px 36px 28px;
        width: 100%;
        max-width: 780px;
        color: #1a2035;
    }
    .counter-select-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.3px;
        margin-bottom: 6px;
        color: #1a2035;
    }
    .counter-select-subtitle {
        font-size: 13px;
        color: rgba(30,40,70,0.55);
        margin-bottom: 24px;
    }
    .counter-tiles {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
    }
    .counter-tile {
        background: rgba(255,255,255,0.6);
        border: 2px solid rgba(180,195,220,0.5);
        border-radius: 14px;
        padding: 16px 18px;
        cursor: pointer;
        transition: border-color 0.18s, background 0.18s, transform 0.12s, box-shadow 0.18s;
        position: relative;
        user-select: none;
    }
    .counter-tile:hover {
        background: rgba(255,255,255,0.9);
        border-color: rgba(59,130,246,0.6);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(59,130,246,0.15);
    }
    .counter-tile.selected {
        background: rgba(239,246,255,0.95);
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    }
    .counter-tile-code {
        font-size: 11px;
        font-weight: 700;
        color: #c97c00;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .counter-tile-name {
        font-size: 15px;
        font-weight: 700;
        color: #1a2035;
        margin-bottom: 2px;
    }
    .counter-tile-short {
        font-size: 12px;
        color: rgba(30,40,70,0.5);
    }
    .counter-tile-floor {
        font-size: 11px;
        color: rgba(37,99,235,0.85);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .counter-tile-prefix {
        position: absolute;
        top: 10px; right: 12px;
        background: rgba(251,191,36,0.18);
        color: #b45309;
        font-size: 10px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 20px;
        letter-spacing: 0.8px;
    }
    .counter-confirm-btn {
        margin-top: 22px;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 11px 32px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: filter 0.18s, transform 0.1s;
        width: 100%;
    }
    .counter-confirm-btn:hover { filter: brightness(1.12); transform: translateY(-1px); }
    .counter-confirm-btn:disabled { opacity: 0.38; cursor: not-allowed; transform: none; }
    /* Counter badge in header */
    .counter-header-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 8px;
        padding: 4px 12px;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
    }
    .counter-header-badge .badge-code {
        background: #f6c90e;
        color: #1a1a2e;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 5px;
        letter-spacing: 0.5px;
    }
    .counter-header-badge .badge-change {
        font-size: 11px;
        color: rgba(255,255,255,0.55);
        cursor: pointer;
        border-left: 1px solid rgba(255,255,255,0.2);
        padding-left: 8px;
        margin-left: 2px;
        transition: color 0.15s;
    }
    .counter-header-badge .badge-change:hover { color: #63b3ed; }
    /* Bill No badge — same style as counter badge */
    .billno-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 8px;
        padding: 4px 12px;
        color: rgba(255,255,255,0.75);
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    .billno-badge .billno-label {
        font-size: 11px;
        color: rgba(255,255,255,0.5);
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .billno-badge .billno-value {
        background: #f6c90e;
        color: #1a1a2e;
        font-size: 12px;
        font-weight: 800;
        padding: 2px 9px;
        border-radius: 5px;
        letter-spacing: 0.5px;
        font-family: monospace;
    }
    /* Header action buttons — ghost glass style */
    .header-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 8px;
        padding: 4px 14px;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.18s, border-color 0.18s;
        white-space: nowrap;
        line-height: 1.6;
    }
    .header-action-btn:hover {
        background: rgba(255,255,255,0.18);
        border-color: rgba(255,255,255,0.35);
        color: #fff;
    }
    .header-action-btn.btn-exit {
        background: rgba(220,53,69,0.25);
        border-color: rgba(220,53,69,0.5);
        color: #ff8090;
    }
    @keyframes posFadeIn {
        from {
            opacity: 0;
            transform: translateY(-8px) scale(0.96);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    .barcode-input:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25) !important;
        background-color: #f8faff !important;
    }
    .barcode-input.is-invalid {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25) !important;
    }
</style>

<div class="container-fluid gretail-container">

    <!-- 1. Header Zone -->
    <div class="erp-header-panel d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="fs-5 fw-bold text-white d-flex align-items-center gap-2">
                <img src="{{ $generaleSetting?->favicon ?? asset('assets/favicon.png') }}" alt="POS" style="width:26px;height:26px;object-fit:contain;border-radius:4px;">POS
            </span>
            <!-- Counter Position Display -->
            <div id="counter-header-display" class="counter-header-badge" style="display:none!important;">
                <span class="badge-code" id="counter-header-code"></span>
                <span id="counter-header-name"></span>
                <span class="badge-change" onclick="openCounterSelector()" title="Change Counter">
                    <i class="fa-solid fa-rotate me-1"></i>Change
                </span>
            </div>
            <!-- Bill No badge -->
            <div class="billno-badge">
                <span class="billno-label">{{ __('Bill No') }}</span>
                <input type="hidden" id="selected-counter-id" name="counter_id" value="">
                <span class="billno-value" id="bill-no-display">00001</span>
                <input type="hidden" id="bill-no" value="00001">
            </div>
            <!-- Close Shift button -->
            <button class="header-action-btn" id="close-shift-btn" onclick="showCloseShiftModal()" style="background: rgba(255,193,7,0.15); border-color: rgba(255,193,7,0.35); color: #ffca2c; display: none;">
                <i class="fa-solid fa-lock"></i>{{ __('Close Shift (F8)') }}
            </button>
            <!-- Sales History button -->
            <button type="button" onclick="openSalesHistoryModal()" class="header-action-btn text-decoration-none d-inline-flex align-items-center" style="background: rgba(14,165,233,0.18); border: 1px solid rgba(14,165,233,0.4); color: #38bdf8; cursor: pointer;">
                <i class="fa-solid fa-clock-history me-1"></i>{{ __('Sales History') }}
            </button>
            <!-- Calculator button -->
            <button class="header-action-btn" onclick="toggleOnScreenCalculator()">
                <i class="fa-solid fa-calculator"></i>{{ __('Calculator (F2)') }}
            </button>
            <!-- Exit button -->
            <button class="header-action-btn btn-exit" onclick="confirmExit()">
                <i class="fa-solid fa-right-from-bracket"></i>{{ __('Exit (Esc)') }}
            </button>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <label class="text-white mb-0 fw-bold">{{ __('Tax Type') }}:</label>
                <select class="form-select" style="width: 100px;" id="tax-type">
                    <option value="GST">GST</option>
                    <option value="VAT">VAT</option>
                </select>
            </div>
            <span class="erp-clock" id="digital-clock">Loading live time...</span>
        </div>
    </div>

    <!-- ================================================================
         COUNTER POSITION SELECTION OVERLAY
         Appears on POS open; cashier must choose a counter to proceed.
    ================================================================ -->
    <div id="pos-counter-overlay">
        <div class="counter-select-card">
            <div class="d-flex align-items-center gap-3 mb-1">
                <img src="{{ $generaleSetting?->favicon ?? asset('assets/favicon.png') }}" alt="POS" style="width:36px;height:36px;object-fit:contain;border-radius:8px;">
                <div>
                    <div class="counter-select-title">{{ __('Select POS Counter') }}</div>
                    <div class="counter-select-subtitle">{{ __('Choose the counter position for this billing session') }}</div>
                </div>
            </div>

            @if($counters->isEmpty())
                <div class="text-center py-4" style="color:rgba(255,255,255,0.5);">
                    <i class="fa-solid fa-triangle-exclamation fa-2x mb-3" style="color:#f6c90e;"></i>
                    <div class="fw-bold">{{ __('No active counters found') }}</div>
                    <div class="mt-1" style="font-size:13px;">
                        {{ __('Please create counter positions from') }}
                        <a href="{{ route('shop.counterMaster.create') }}" style="color:#63b3ed;" target="_blank">{{ __('Counter Master') }}</a>.
                    </div>
                </div>
            @else
                <div class="counter-tiles" id="counter-tiles-container">
                    @foreach($counters as $counter)
                        <div class="counter-tile"
                             id="counter-tile-{{ $counter->id }}"
                             data-id="{{ $counter->id }}"
                             data-code="{{ $counter->code }}"
                             data-name="{{ $counter->counter_name }}"
                             data-short="{{ $counter->counter_short_name }}"
                             data-floor="{{ $counter->floor }}"
                             data-prefix="{{ $counter->voucher_prefix }}"
                             onclick="selectCounterTile(this)">
                            <span class="counter-tile-prefix">{{ $counter->voucher_prefix }}</span>
                            <div class="counter-tile-code">{{ $counter->code }}</div>
                            <div class="counter-tile-name">{{ $counter->counter_name }}</div>
                            <div class="counter-tile-short">{{ $counter->counter_short_name }}</div>
                            @if($counter->floor)
                            <div class="counter-tile-floor">
                                <i class="fa-solid fa-layer-group" style="font-size:10px;"></i>
                                {{ $counter->floor }}
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button class="counter-confirm-btn" id="counter-confirm-btn" onclick="confirmCounterSelection()" disabled>
                    <i class="fa-solid fa-circle-check me-2"></i>{{ __('Start Billing on Selected Counter') }}
                </button>
            @endif
        </div>
    </div>

    <!-- 2. Main Grid -->
    <div class="grid-card grid-card-main">
        <!-- Exchange Mode Banner -->
        <div id="exchange-active-banner" class="alert alert-warning d-none justify-content-between align-items-center mb-0 px-3 py-2" style="border-radius: 0; font-size: 13px; font-weight: 600; border-bottom: 2px solid #f59e0b; background-color: #fffbeb; color: #92400e;">
            <div>
                <i class="fa-solid fa-rotate me-2 text-warning"></i>
                <span>EXCHANGE MODE ACTIVE:</span>
                <span class="ms-1 font-monospace" id="exchange-banner-ref">#</span>
                <span class="ms-2">| Returned Credit:</span>
                <strong id="exchange-banner-credit" class="text-danger ms-1">₹0.00</strong>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold" onclick="cancelExchangeSession()" style="font-size: 11px;">
                <i class="fa-solid fa-xmark me-1"></i>Cancel Exchange
            </button>
        </div>
        <div class="grid-card-header d-flex justify-content-between align-items-center py-2">
            <span>{{ __('Sales Item Grid') }}</span>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary px-3 fw-bold" onclick="clickAddRowBtn()">
                    <i class="fa-solid fa-plus me-1"></i>{{ __('Add Row (F1)') }}
                </button>
                <button class="btn btn-sm btn-danger px-3 fw-bold" onclick="clearCartGrid()">
                    <i class="fa-solid fa-trash me-1"></i>{{ __('Clear Items (F4)') }}
                </button>
            </div>
        </div>
        <div class="card-body p-1">
            <div class="table-responsive-grid">
                <table class="table table-bordered sales-grid-table" id="sales-grid">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 140px;">{{ __('Barcode') }}</th>
                            <th>{{ __('Item Description') }}</th>
                            <th style="width: 90px;">{{ __('HSN Code') }}</th>
                            <th style="width: 140px;">{{ __('Salesman') }}</th>
                            <th style="width: 80px;">{{ __('Design No') }}</th>
                            <th style="width: 80px;">{{ __('Color') }}</th>
                            <th style="width: 70px;">{{ __('Size') }}</th>
                            <th style="width: 100px;">{{ __('Sales Rate') }}</th>
                            <th style="width: 70px;">{{ __('Qty') }}</th>
                            <th style="width: 70px;">{{ __('Disc (%)') }}</th>
                            <th style="width: 80px;">{{ __('Disc Amt') }}</th>
                            <th style="width: 70px;">{{ __('CGST %') }}</th>
                            <th style="width: 100px;">{{ __('Net Amt') }}</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="sales-grid-body">
                        <!-- Dynamic rows loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Bottom Split -->
    <div class="row bottom-panels-row">
        <!-- Bottom Left: Customer Master Panel -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="grid-card bottom-panel-card">
                <div class="grid-card-header p-1 px-2">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <ul class="nav nav-tabs card-header-tabs border-0 flex-grow-1" id="customer-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-1 px-2" id="cust-basic-tab" data-bs-toggle="tab" data-bs-target="#cust-basic" type="button" role="tab">{{ __('Customer') }} <kbd class="pos-kbd-badge">F3</kbd></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-1 px-2" id="cust-other-tab" data-bs-toggle="tab" data-bs-target="#cust-other" type="button" role="tab">{{ __('Other') }}</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-1 px-2" id="cust-history-tab" data-bs-toggle="tab" data-bs-target="#cust-history" type="button" role="tab">{{ __('History') }}</button>
                            </li>
                        </ul>
                        <button type="button" class="btn btn-xs btn-outline-danger ms-1 py-0 px-2 rounded-pill fw-bold" onclick="clearCustomerDetailsForm()" title="Clear Customer Fields" style="font-size: 10px; white-space: nowrap;">
                            <i class="fa-solid fa-rotate-left me-1"></i>{{ __('Clear') }}
                        </button>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="tab-content bottom-tab-content">
                        <!-- Tab 1: Customer Details (Core Info) -->
                        <div class="tab-pane fade show active" id="cust-basic" role="tabpanel">
                            <div class="row g-1">
                                <!-- Row 1: Mobile No. & Card No -->
                                <div class="col-7">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Mobile No.') }} <kbd class="pos-kbd-inline">F3</kbd></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text px-1 text-muted" style="font-size: 0.7rem;">+91</span>
                                        <input type="text" class="form-control form-control-sm px-1" id="customer-phone" placeholder="Enter phone" oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10); lookupCustomerByPhone(this.value);">
                                        <button class="btn btn-outline-secondary px-1" type="button" title="Edit Customer" onclick="focusCustomerName()"><i class="fa-solid fa-pen-to-square"></i></button>
                                    </div>
                                </div>
                                <div class="col-5">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Card No') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-card" placeholder="Card No">
                                </div>

                                <!-- Row 2: A/C Name -->
                                <div class="col-12">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('A/C Name') }}</label>
                                    <input type="text" class="form-control form-control-sm fw-bold" id="customer-name" placeholder="Walk-in Customer">
                                </div>

                                <!-- Row 3: Salesman -->
                                <div class="col-12">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Sold By (Salesman)') }}</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text px-1 text-muted" style="font-size: 0.7rem;"><i class="fa-solid fa-user-tie text-primary"></i></span>
                                        <select class="form-select form-select-sm px-1 fw-bold text-dark" id="bill-primary-salesman" onchange="syncPrimarySalesmanToGrid(this.value)" style="font-size: 0.72rem;">
                                            <option value="">-- None --</option>
                                            @foreach($salesmans as $sm)
                                                <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 3: Category & Sub Category -->
                                <div class="col-6">
                                    <label class="fw-bold mb-0 text-dark" style="font-size: 0.7rem;">{{ __('Category') }}</label>
                                    <div class="input-group input-group-sm">
                                        <select class="form-select form-select-sm px-1" id="customer-category" style="font-size: 0.72rem;">
                                            <option value="">-- None --</option>
                                            <option value="regular">Regular</option>
                                            <option value="vip">VIP</option>
                                            <option value="wholesale">Wholesale</option>
                                        </select>
                                        <button class="btn btn-outline-primary px-1 py-0" type="button" onclick="promptAddOption('customer-category', 'Category')"><i class="fa-solid fa-circle-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.7rem;">{{ __('Sub Category') }}</label>
                                    <div class="input-group input-group-sm">
                                        <select class="form-select form-select-sm px-1" id="customer-subcategory" style="font-size: 0.72rem;">
                                            <option value="">-- None --</option>
                                            <option value="tier1">Tier 1</option>
                                            <option value="tier2">Tier 2</option>
                                        </select>
                                        <button class="btn btn-outline-primary px-1 py-0" type="button" onclick="promptAddOption('customer-subcategory', 'Sub Category')"><i class="fa-solid fa-circle-plus"></i></button>
                                    </div>
                                </div>

                                <!-- Row 4: Address -->
                                <div class="col-12">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Address') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-address" placeholder="Address">
                                </div>

                                <!-- Row 5: City, Area & Pin Code -->
                                <div class="col-4">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.7rem;">{{ __('City') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-city" placeholder="City">
                                </div>
                                <div class="col-5">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.7rem;">{{ __('Area') }}</label>
                                    <div class="input-group input-group-sm">
                                        <select class="form-select form-select-sm px-1" id="customer-area" style="font-size: 0.72rem;">
                                            <option value="">-- None --</option>
                                            <option value="central">Central</option>
                                            <option value="north">North Zone</option>
                                            <option value="south">South Zone</option>
                                        </select>
                                        <button class="btn btn-outline-primary px-1 py-0" type="button" onclick="promptAddOption('customer-area', 'Area')"><i class="fa-solid fa-circle-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.7rem;">{{ __('Pin Code') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-pincode" placeholder="Pin">
                                </div>

                                <!-- Row 6: Birth Dt, Anv Dt & Total Sales -->
                                <div class="col-4">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.7rem;">{{ __('Birth Dt') }}</label>
                                    <input type="date" class="form-control form-control-sm px-1" id="customer-dob" style="font-size: 0.68rem;">
                                </div>
                                <div class="col-4">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.7rem;">{{ __('Anv Dt') }}</label>
                                    <input type="date" class="form-control form-control-sm px-1" id="customer-anniversary" style="font-size: 0.68rem;">
                                </div>
                                <div class="col-4 d-flex flex-column justify-content-end">
                                    <span class="text-primary fw-bold mb-0" style="font-size: 0.68rem;">{{ __('Total Sales') }} :</span>
                                    <span id="customer-total-sales-text" class="fw-bold text-primary font-monospace" style="font-size: 0.82rem;">₹0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Other Details (Reference & Tax Attributes) -->
                        <div class="tab-pane fade" id="cust-other" role="tabpanel">
                            <div class="row g-1">
                                <!-- Source From & Sub Source -->
                                <div class="col-6">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Source From') }}</label>
                                    <select class="form-select form-select-sm px-1" id="customer-source" style="font-size: 0.72rem;">
                                        <option value="">-- None --</option>
                                        <option value="walkin">Walk-in</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="referral">Referral</option>
                                        <option value="google">Google</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Sub Source') }}</label>
                                    <select class="form-select form-select-sm px-1" id="customer-subsource" style="font-size: 0.72rem;">
                                        <option value="">-- None --</option>
                                        <option value="ad">Online Ad</option>
                                        <option value="friend">Friend</option>
                                        <option value="banner">Store Banner</option>
                                    </select>
                                </div>

                                <!-- Ref By & Ref Mobile -->
                                <div class="col-6">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Ref By') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-ref-by" placeholder="Referred By">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Ref Mobile') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-ref-mobile" placeholder="Ref Mobile" oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);">
                                </div>

                                <!-- GST No & PAN No -->
                                <div class="col-6">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('GST No') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-gstin" placeholder="GSTIN">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('PAN No') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-pan" placeholder="PAN">
                                </div>

                                <!-- State & Email -->
                                <div class="col-5">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('State') }}</label>
                                    <input type="text" class="form-control form-control-sm px-1" id="customer-state" value="Gujarat">
                                </div>
                                <div class="col-7">
                                    <label class="fw-bold mb-0 text-muted" style="font-size: 0.72rem;">{{ __('Email') }}</label>
                                    <input type="email" class="form-control form-control-sm px-1" id="customer-email" placeholder="Email">
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: History -->
                        <div class="tab-pane fade" id="cust-history" role="tabpanel">
                            <div class="p-2 border rounded bg-light">
                                <p class="mb-1"><strong>{{ __('Total Sales') }}:</strong> <span id="history-total-sales">₹0.00</span></p>
                                <p class="mb-1"><strong>{{ __('Recent Order') }}:</strong> <span id="history-recent-order">N/A</span></p>
                                <p class="mb-0"><strong>{{ __('Ref By') }}:</strong> <span id="history-ref-by">Direct</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Middle: Bill details basic & TAX details -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="grid-card bottom-panel-card">
                <div class="grid-card-header p-2">
                    <ul class="nav nav-tabs card-header-tabs border-0" id="bill-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="bill-basic-tab" data-bs-toggle="tab" data-bs-target="#bill-basic" type="button" role="tab">{{ __('Bill Detail (Basic)') }}</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="bill-tax-tab" data-bs-toggle="tab" data-bs-target="#bill-tax" type="button" role="tab">{{ __('Tax Details') }}</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body py-2 d-flex flex-column justify-content-between">
                    <div class="tab-content bottom-tab-content flex-grow-1">
                        <!-- Tab 1: Basic Bill Summary -->
                        <div class="tab-pane fade show active" id="bill-basic" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('Taxable Amt') }}</label>
                                    <input type="text" class="form-control form-control-sm text-end fw-bold" id="summary-taxable" value="0.00" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('Total Discount') }}</label>
                                    <input type="text" class="form-control form-control-sm text-end fw-bold text-danger" id="summary-discount" value="0.00" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('Tax Amount') }}</label>
                                    <input type="text" class="form-control form-control-sm text-end fw-bold text-primary" id="summary-tax" value="0.00" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('Round Off (Rof)') }}</label>
                                    <input type="text" class="form-control form-control-sm text-end" id="summary-roundoff" value="0.00" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="fw-bold mb-0">{{ __('Collected By (Cashier)') }}</label>
                                    <select class="form-select form-select-sm fw-bold text-dark" id="payment-cashier-id">
                                        @if(isset($cashiers) && $cashiers->isNotEmpty())
                                            @foreach($cashiers as $c)
                                                <option value="{{ $c->id }}" {{ (auth()->id() == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="{{ auth()->id() }}">{{ auth()->user()?->name ?? 'Cashier' }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Tab 2: Detailed Tax splits -->
                        <div class="tab-pane fade" id="bill-tax" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('CGST Output') }}</label>
                                    <input type="text" class="form-control form-control-sm text-end" id="tax-cgst" value="0.00" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('SGST Output') }}</label>
                                    <input type="text" class="form-control form-control-sm text-end" id="tax-sgst" value="0.00" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="fw-bold mb-0">{{ __('IGST Output') }}</label>
                                    <input type="text" class="form-control form-control-sm text-end" id="tax-igst" value="0.00" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Large Highlighted Bill Amount Display -->
                    <div class="bill-amount-display">
                        <div class="bill-amount-label">{{ __('Bill Amount') }}</div>
                        <div class="bill-amount-value" id="big-bill-amount">{{ $currency }}0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Right: Payment & Tenders -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="grid-card bottom-panel-card">
                <div class="grid-card-header p-2">
                    <ul class="nav nav-tabs card-header-tabs border-0" id="payment-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="pay-basic-tab" data-bs-toggle="tab" data-bs-target="#pay-basic" type="button" role="tab">{{ __('Payment Tenders') }} <kbd class="pos-kbd-badge">F6</kbd></button>
                        </li>
                    </ul>
                </div>
                <div class="card-body py-2 d-flex flex-column justify-content-between">
                    <div class="tab-content bottom-tab-content flex-grow-1">
                        <div class="tab-pane fade show active" id="pay-basic" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('Bill Mode') }} <kbd class="pos-kbd-inline">F6</kbd></label>
                                    <select class="form-select form-select-sm fw-bold text-primary" id="payment-method" onchange="togglePaymentInputs()">
                                        <option value="cash">Cash (F9)</option>
                                        <option value="paytm_card">Paytm Card (Shift+F10)</option>
                                        <option value="paytm_upi">Paytm UPI (Shift+F11)</option>
                                        <option value="phonepe_card">PhonePe Card</option>
                                        <option value="phonepe_upi">PhonePe UPI</option>
                                        <option value="split">Split Payment</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('Cash Received') }} <kbd class="pos-kbd-inline">F9</kbd></label>
                                    <input type="number" step="0.01" class="form-control form-control-sm fw-bold text-success text-end" id="cash-received" value="0.00" onkeyup="calculateDueBalance()">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('Card Amount') }} <kbd class="pos-kbd-inline">Shift+F10</kbd></label>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end" id="card-received" value="0.00" onkeyup="calculateDueBalance()" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-0">{{ __('Loyalty / Gift Card') }}</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-end" id="gift-received" value="0.00">
                                </div>
                            </div>
                            <!-- Terminal Status Panel -->
                            <div class="terminal-status-panel border rounded p-1 mt-2 bg-light text-dark" id="terminal-info-panel" style="font-size: 0.75rem;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold"><i class="fa-solid fa-credit-card text-primary me-1"></i> <span id="edc-terminal-name">Searching Terminal...</span></span>
                                    <span class="badge bg-secondary" id="edc-connection-badge">Checking</span>
                                </div>
                                <div class="row g-1 text-muted" style="font-size: 0.7rem;">
                                    <div class="col-6"><strong>Provider:</strong> <span id="edc-provider">-</span></div>
                                    <div class="col-6"><strong>Terminal ID:</strong> <span id="edc-terminal-id">-</span></div>
                                    <div class="col-6"><strong>Methods:</strong> <span id="edc-supported-methods">Card, UPI</span></div>
                                    <div class="col-6"><strong>Health:</strong> <span id="edc-last-health">-</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Large Highlighted Cash Balance Display -->
                    <div class="cash-received-display">
                        <div class="cash-received-label" id="cash-due-label">{{ __('Change Due') }}</div>
                        <div class="cash-received-value" id="big-change-due">{{ $currency }}0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Right-Most: Shortcuts & Quick Controls -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="grid-card bottom-panel-card">
                <div class="grid-card-header p-2">
                    <ul class="nav nav-tabs card-header-tabs border-0" id="shortcut-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="shortcut-panel-tab" data-bs-toggle="tab" data-bs-target="#shortcut-panel" type="button" role="tab">{{ __('Quick Controls') }}</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body py-2 d-flex flex-column justify-content-between">
                    <div class="tab-content bottom-tab-content flex-grow-1">
                        <div class="tab-pane fade show active" id="shortcut-panel" role="tabpanel">
                            <!-- Quick Buttons Grid -->
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <button class="btn glass-btn-base glass-btn-return" onclick="showReturnF7Modal()">
                                        <i class="fa-solid fa-right-left"></i>
                                        <span>{{ __('Return') }}</span>
                                        <span class="badge bg-secondary text-white">F7</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn glass-btn-base glass-btn-hold" onclick="saveHoldBillSession()">
                                        <i class="fa-solid fa-hand"></i>
                                        <span>{{ __('Hold') }}</span>
                                        <span class="badge bg-secondary text-white">F10</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn glass-btn-base glass-btn-hold-list position-relative" onclick="showHoldBillsListModal()">
                                        <i class="fa-solid fa-folder-open"></i>
                                        <span>{{ __('Hold List') }}</span>
                                        <span class="badge bg-secondary text-white">Ctrl+H</span>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="hold-bills-count-badge" style="font-size: 9.5px; padding: 2.5px 5.5px;">0</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn glass-btn-base glass-btn-calc" onclick="toggleOnScreenCalculator()">
                                        <i class="fa-solid fa-calculator"></i>
                                        <span>{{ __('Calculator') }}</span>
                                        <span class="badge bg-secondary text-white">F2</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn glass-btn-base glass-btn-cancel" onclick="resetGridBill()">
                                        <i class="fa-solid fa-xmark"></i>
                                        <span>{{ __('Cancel Bill') }}</span>
                                        <span class="badge bg-secondary text-white">Reset</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn glass-btn-base glass-btn-print" onclick="submitGridOrder()">
                                        <i class="fa-solid fa-print"></i>
                                        <span>{{ __('Print & Save') }}</span>
                                        <span class="badge bg-secondary text-white">F12</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Cheat Sheet / Hotkeys List -->
                            <div class="border rounded p-2 bg-light text-dark" style="font-size: 12.5px !important; font-weight: 600 !important;">
                                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                    <span class="d-flex align-items-center"><span class="kbd-shortcut-badge">F1</span> Search/Add Row</span>
                                    <span class="d-flex align-items-center"><span class="kbd-shortcut-badge">F4</span> Clear Items</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                    <span class="d-flex align-items-center"><span class="kbd-shortcut-badge">F8</span> Close Shift</span>
                                    <span class="d-flex align-items-center"><span class="kbd-shortcut-badge">F9</span> Pay Cash</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="d-flex align-items-center"><span class="kbd-shortcut-badge">Shift+Del</span> Delete Row</span>
                                    <span class="d-flex align-items-center"><span class="kbd-shortcut-badge">Esc</span> Exit POS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer spacer to prevent clipping -->
    <div style="height: 10px;"></div>
</div>

<!-- =====================================================================
     EXIT CONFIRMATION — Glassmorphism Full-Screen Overlay
===================================================================== -->
<div id="pos-exit-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 999999;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    align-items: center; justify-content: center;
" onclick="if(event.target===this) closeExitModal()">

    <div style="
        background: rgba(18, 22, 36, 0.78);
        backdrop-filter: blur(28px) saturate(160%);
        -webkit-backdrop-filter: blur(28px) saturate(160%);
        border: 1px solid rgba(255,255,255,0.14);
        box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.06) inset;
        border-radius: 24px;
        padding: 40px 44px 36px;
        max-width: 420px;
        width: 90%;
        text-align: center;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
    ">
        <!-- Warning Icon -->
        <div style="
            width: 72px; height: 72px;
            background: rgba(220, 53, 69, 0.18);
            border: 2px solid rgba(220,53,69,0.45);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        ">
            <i class="fa-solid fa-right-from-bracket" style="font-size: 28px; color: #e05c6e;"></i>
        </div>

        <!-- Title -->
        <h5 style="color:#fff; font-weight:700; font-size:20px; margin-bottom:8px; letter-spacing:-0.3px;">
            Exit POS?
        </h5>

        <!-- Message -->
        <p style="color: rgba(255,255,255,0.55); font-size:14px; margin-bottom:30px; line-height:1.6;">
            Any unsaved bill data will be lost.<br>
            Are you sure you want to exit the POS terminal?
        </p>

        <!-- Buttons -->
        <div style="display:flex; gap:12px; justify-content:center;">
            <button id="exit-stay-btn" onclick="closeExitModal()" style="
                flex:1;
                background: rgba(255,255,255,0.08);
                border: 2px solid rgba(255,255,255,0.18);
                color: rgba(255,255,255,0.85);
                border-radius: 12px;
                padding: 12px 0;
                font-size: 15px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.18s, border-color 0.18s, box-shadow 0.18s;
                outline: none;
            ">
                <i class="fa-solid fa-arrow-left me-2"></i>Stay
            </button>
            <a id="exit-confirm-btn" href="{{ route('shop.dashboard.index') }}" style="
                flex:1;
                background: linear-gradient(135deg, #dc3545, #b02a37);
                border: 2px solid transparent;
                color: #fff;
                border-radius: 12px;
                padding: 12px 0;
                font-size: 15px;
                font-weight: 700;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 18px rgba(220,53,69,0.45);
                transition: filter 0.18s, border-color 0.18s, box-shadow 0.18s;
                outline: none;
            ">
                <i class="fa-solid fa-right-from-bracket me-2"></i>Yes, Exit
            </a>
        </div>
    </div>
</div>

<!-- =====================================================================
     RECALL HELD BILLS — Glassmorphism Full-Screen Overlay (White Glass Card)
===================================================================== -->
<div id="pos-recall-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 999999;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    align-items: center; justify-content: center;
    padding: 24px;
    box-sizing: border-box;
" onclick="if(event.target===this) closeRecallModal()">

    <div style="
        background: #f8fafc;
        color: #1e293b;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 680px;
        padding: 28px 32px;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
    ">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0" style="color: #0f172a;">
                <i class="fa-solid fa-rotate-left me-2 text-primary"></i>{{ __('Recall Suspended Bills') }}
            </h5>
            <button class="btn-close" onclick="closeRecallModal()" style="font-size:12px; outline:none; border:none;"></button>
        </div>

        <div style="max-height: 380px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff;" id="recall-list-container">
            <!-- Dynamically populated held bills list -->
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-secondary btn-sm px-4 fw-bold text-dark" onclick="closeRecallModal()" style="border-radius: 8px; border-color: #cbd5e1; background: #ffffff;">
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>

<!-- =====================================================================
     RETURN & EXCHANGE — Glassmorphism Full-Screen Overlay (White Glass Card)
===================================================================== -->
<div id="pos-return-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 1000010;
    background: rgba(5, 8, 18, 0.88);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
" onclick="if(event.target===this) closeReturnModal()">

    <div style="
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(32px) saturate(200%);
        -webkit-backdrop-filter: blur(32px) saturate(200%);
        border: 1px solid rgba(255,255,255,1);
        box-shadow: 0 24px 64px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.8) inset;
        border-radius: 20px;
        padding: 32px 36px 28px;
        width: 100%;
        max-width: 780px;
        color: #1a2035;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
    ">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0" style="color:#1a2035;">
                <i class="fa-solid fa-right-left me-2 text-warning"></i>{{ __('Invoice Return & Exchange') }}
            </h5>
            <button class="btn btn-close" onclick="closeReturnModal()" style="font-size:12px; outline:none; border:none;"></button>
        </div>

        <!-- Invoice Search Input -->
        <div class="row g-2 mb-3">
            <div class="col-8">
                <input type="text" class="form-control" id="return-invoice-search" style="height: 38px; font-size: 13px;" placeholder="Enter or scan original invoice no. (e.g. RC-000011)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); lookupInvoiceForReturn(); }">
            </div>
            <div class="col-4">
                <button class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-1" onclick="lookupInvoiceForReturn()" style="height: 38px; font-size: 13px;">
                    <i class="fa-solid fa-magnifying-glass"></i>Search
                </button>
            </div>
        </div>

        <!-- Search Result Details Container -->
        <div id="return-details-area" style="display:none;">
            <div class="p-2 mb-3 rounded bg-light" style="font-size: 13px; border: 1px solid #e2e8f0; color:#1a2035;">
                <div class="row">
                    <div class="col-6"><strong>Customer:</strong> <span id="return-cust-name"></span></div>
                    <div class="col-6"><strong>Phone:</strong> <span id="return-cust-phone"></span></div>
                    <div class="col-12"><strong>Original Invoice Ref:</strong> <span id="return-invoice-ref" class="text-primary font-monospace fw-bold"></span></div>
                </div>
            </div>

            <!-- Products Table -->
            <div style="max-height: 220px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle" style="font-size: 12px; color:#1a2035;" id="return-items-table">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>Invoice Ref</th>
                            <th>Barcode</th>
                            <th class="text-center">Rate</th>
                            <th class="text-center">Purchased</th>
                            <th class="text-center">Returned</th>
                            <th class="text-center" style="width: 80px;">Return Qty</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody id="return-items-body">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Refund controls -->
            <div class="row g-2 align-items-center mt-3 border-top pt-3">
                <div class="col-6">
                    <label class="fw-bold mb-1" style="font-size: 13px; display: block;">Refund Method</label>
                    <select class="form-select" id="return-refund-method" style="height: 38px; font-size: 13px;">
                        <option value="cash">Cash Refund</option>
                        <option value="online">Bank / UPI Transfer</option>
                    </select>
                </div>
                <div class="col-6 text-end">
                    <div style="font-size: 14px; font-weight:700; color:#dc3545;" class="mb-1">
                        Refund Payable: <span id="return-total-refund-val">0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-secondary px-4 fw-bold d-flex align-items-center justify-content-center" onclick="closeReturnModal()" style="height: 38px; font-size: 13px;">
                {{ __('Close') }}
            </button>
            <button class="btn btn-warning px-4 fw-bold d-flex align-items-center justify-content-center gap-1 text-dark" id="submit-exchange-btn" onclick="startExchangeProcess()" disabled style="height: 38px; font-size: 13px;">
                <i class="fa-solid fa-repeat"></i>{{ __('Exchange with New Product') }}
            </button>
            <button class="btn btn-danger px-4 fw-bold d-flex align-items-center justify-content-center gap-1" id="submit-return-btn" onclick="submitReturnTransaction()" disabled style="height: 38px; font-size: 13px;">
                <i class="fa-solid fa-circle-check"></i>{{ __('Confirm Return Only') }}
            </button>
        </div>
    </div>
</div>

<!-- Return (F7) Standalone Invoice Return & Exchange Overlay -->
<div id="pos-return-f7-overlay" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    z-index: 1000005;
    justify-content: center;
    align-items: center;
    padding: 20px;
">
    <div style="
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(32px) saturate(200%);
        -webkit-backdrop-filter: blur(32px) saturate(200%);
        border: 1px solid rgba(255,255,255,1);
        box-shadow: 0 24px 64px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.8) inset;
        border-radius: 20px;
        padding: 32px 36px 28px;
        width: 100%;
        max-width: 820px;
        color: #1a2035;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
    ">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0" style="color:#1a2035;">
                <i class="fa-solid fa-right-left me-2 text-warning"></i>{{ __('Invoice Return & Exchange (F7)') }}
            </h5>
            <button class="btn btn-close" onclick="closeReturnF7Modal()" style="font-size:12px; outline:none; border:none;"></button>
        </div>

        <!-- Two Separate Search Inputs: Barcode Search & Invoice Search -->
        <div class="row g-2 mb-3">
            <!-- Barcode Search Input -->
            <div class="col-md-6">
                <label class="form-label fw-bold mb-1" style="font-size: 12px; color: #475569;">
                    <i class="fa-solid fa-barcode me-1 text-primary"></i>Barcode Search & Add
                </label>
                <div class="input-group">
                    <input type="text" class="form-control" id="return-f7-barcode-search" style="height: 36px; font-size: 13px;" placeholder="Scan or enter barcode (e.g. 0000000104)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addBarcodeReturnF7(); }">
                    <button class="btn btn-primary fw-bold px-3 d-flex align-items-center gap-1" onclick="addBarcodeReturnF7()" style="height: 36px; font-size: 12px;">
                        <i class="fa-solid fa-plus"></i>Add Item
                    </button>
                </div>
            </div>

            <!-- Invoice Search Input -->
            <div class="col-md-6">
                <label class="form-label fw-bold mb-1" style="font-size: 12px; color: #475569;">
                    <i class="fa-solid fa-file-invoice me-1" style="color:#9333ea;"></i>Invoice Search & Add
                </label>
                <div class="input-group">
                    <input type="text" class="form-control" id="return-f7-invoice-search" style="height: 36px; font-size: 13px;" placeholder="Enter invoice no. (e.g. RC-000011)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); addInvoiceReturnF7(); }">
                    <button class="btn btn-secondary fw-bold px-3 d-flex align-items-center gap-1" onclick="addInvoiceReturnF7()" style="height: 36px; font-size: 12px; background-color: #9333ea; border-color: #9333ea;">
                        <i class="fa-solid fa-file-circle-plus"></i>Add Invoice
                    </button>
                </div>
            </div>
        </div>

        <!-- Details & Products Table Container -->
        <div id="return-f7-details-area" style="display:none;">
            <div class="p-2 mb-3 rounded bg-light" style="font-size: 13px; border: 1px solid #e2e8f0; color:#1a2035;">
                <div class="row">
                    <div class="col-6"><strong>Customer:</strong> <span id="return-f7-cust-name"></span></div>
                    <div class="col-6"><strong>Phone:</strong> <span id="return-f7-cust-phone"></span></div>
                    <div class="col-12"><strong>Invoices Included:</strong> <span id="return-f7-invoice-ref" class="text-primary font-monospace fw-bold"></span></div>
                </div>
            </div>

            <!-- Products Table -->
            <div style="max-height: 240px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle" style="font-size: 12px; color:#1a2035;" id="return-f7-items-table">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>Invoice Ref</th>
                            <th>Barcode</th>
                            <th class="text-center">Rate</th>
                            <th class="text-center">Purchased</th>
                            <th class="text-center">Returned</th>
                            <th class="text-center" style="width: 80px;">Return Qty</th>
                            <th>Reason</th>
                            <th class="text-center" style="width: 40px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="return-f7-items-body">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Refund controls -->
            <div class="row g-2 align-items-center mt-3 border-top pt-3">
                <div class="col-6">
                    <label class="fw-bold mb-1" style="font-size: 13px; display: block;">Refund Method</label>
                    <select class="form-select" id="return-f7-refund-method" style="height: 38px; font-size: 13px;">
                        <option value="cash">Cash Refund</option>
                        <option value="online">Bank / UPI Transfer</option>
                    </select>
                </div>
                <div class="col-6 text-end">
                    <div style="font-size: 14px; font-weight:700; color:#dc3545;" class="mb-1">
                        Refund Payable: <span id="return-f7-total-refund-val">0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-secondary px-4 fw-bold d-flex align-items-center justify-content-center" onclick="closeReturnF7Modal()" style="height: 38px; font-size: 13px;">
                {{ __('Close') }}
            </button>
            <button class="btn btn-warning px-4 fw-bold d-flex align-items-center justify-content-center gap-1 text-dark" id="submit-f7-exchange-btn" onclick="startExchangeF7Process()" disabled style="height: 38px; font-size: 13px;">
                <i class="fa-solid fa-repeat"></i>{{ __('Exchange with New Product') }}
            </button>
            <button class="btn btn-danger px-4 fw-bold d-flex align-items-center justify-content-center gap-1" id="submit-f7-return-btn" onclick="submitReturnF7Transaction()" disabled style="height: 38px; font-size: 13px;">
                <i class="fa-solid fa-circle-check"></i>{{ __('Confirm Return Only') }}
            </button>
        </div>
    </div>
</div>

<!-- =====================================================================
     OPEN SHIFT — Glassmorphism Modal Overlay (Forces Opening Cash input)
===================================================================== -->
<div id="pos-shift-open-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 999999;
    background: rgba(5, 8, 18, 0.94);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    align-items: center; justify-content: center;
">
    <div style="
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(32px) saturate(200%);
        -webkit-backdrop-filter: blur(32px) saturate(200%);
        border: 1px solid rgba(255,255,255,1);
        box-shadow: 0 24px 64px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.8) inset;
        border-radius: 20px;
        padding: 40px 44px 36px;
        width: 100%;
        max-width: 440px;
        color: #1a2035;
        text-align: center;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
    ">
        <div style="
            width: 72px; height: 72px;
            background: rgba(13, 110, 253, 0.1);
            border: 2px solid rgba(13, 110, 253, 0.35);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        ">
            <i class="fa-solid fa-cash-register" style="font-size: 28px; color: #0d6efd;"></i>
        </div>

        <h5 class="fw-bold mb-2" style="color:#1a2035;">{{ __('Open Register Shift') }}</h5>
        <p class="text-muted mb-4" style="font-size: 14px;">
            {{ __('Please enter the starting drawer cash to begin billing at this counter.') }}
        </p>

        <div class="mb-4 text-start">
            <label class="fw-bold mb-1" style="font-size: 13px;">{{ __('Opening Cash Balance') }}</label>
            <div class="input-group">
                <span class="input-group-text fw-bold">{{ $currency }}</span>
                <input type="number" step="0.01" class="form-control form-control-lg fw-bold text-success text-center" id="shift-opening-cash-input" value="0.00" style="font-size: 20px;">
            </div>
        </div>

        <button class="btn btn-primary w-100 py-2 fw-bold" onclick="submitOpenShiftSession()">
            <i class="fa-solid fa-play me-2"></i>{{ __('Start Shift & Billing') }}
        </button>
    </div>
</div>

<!-- =====================================================================
     CLOSE SHIFT & SUMMARY REPORT — Glassmorphism Modal Overlay (White Glass Card)
===================================================================== -->
<div id="pos-shift-close-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 999999;
    background: rgba(5, 8, 18, 0.88);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
" onclick="if(event.target===this) closeShiftModal()">

    <div style="
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(32px) saturate(200%);
        -webkit-backdrop-filter: blur(32px) saturate(200%);
        border: 1px solid rgba(255,255,255,1);
        box-shadow: 0 24px 64px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.8) inset;
        border-radius: 20px;
        padding: 32px 36px 28px;
        width: 100%;
        max-width: 580px;
        color: #1a2035;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
    ">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0" style="color:#1a2035;">
                <i class="fa-solid fa-file-invoice-dollar me-2 text-danger"></i>{{ __('Close Register & Shift Summary') }}
            </h5>
            <button class="btn btn-close" onclick="closeShiftModal()" style="font-size:12px;"></button>
        </div>

        <!-- Dynamic Shift Stats Breakdown -->
        <div class="row g-2 mb-3 bg-light p-3 rounded" style="font-size:13px; border:1px solid #e2e8f0; color:#1a2035;">
            <div class="col-6 mb-1"><strong>Counter Name:</strong> <span id="shift-disp-counter"></span></div>
            <div class="col-6 mb-1"><strong>Cashier:</strong> <span id="shift-disp-cashier"></span></div>
            <div class="col-6 mb-1"><strong>Opened At:</strong> <span id="shift-disp-opened"></span></div>
            <div class="col-6 mb-1"><strong>Opening Cash:</strong> <span id="shift-disp-opening" class="fw-bold"></span></div>
            <hr class="my-2">
            <div class="col-6 mb-1"><strong>(+) Cash Sales:</strong> <span id="shift-disp-sales-cash" class="text-success"></span></div>
            <div class="col-6 mb-1"><strong>(+) Card/UPI Sales:</strong> <span id="shift-disp-sales-card"></span></div>
            <div class="col-6 mb-1"><strong>(-) Returned Cash:</strong> <span id="shift-disp-returns-cash" class="text-danger"></span></div>
            <div class="col-6 mb-1"><strong>(-) Returned Card:</strong> <span id="shift-disp-returns-card"></span></div>
            <hr class="my-2">
            <div class="col-12 mb-1 text-end" style="font-size: 15px; font-weight:700;">
                {{ __('Expected Cash in Drawer') }}: <span id="shift-disp-expected" class="text-primary"></span>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-12 mb-2">
                <label class="fw-bold mb-1" style="font-size: 13px;">{{ __('Actual Cash in Drawer') }}</label>
                <input type="number" step="0.01" class="form-control form-control-sm fw-bold text-success text-center" id="shift-closing-cash-input" value="0.00" style="font-size: 18px;">
            </div>
            <div class="col-12 mb-2">
                <label class="fw-bold mb-1" style="font-size: 13px;">{{ __('Closing Note / Comments') }}</label>
                <textarea class="form-control form-control-sm" id="shift-closing-note-input" rows="2" placeholder="Describe any drawer cash mismatch discrepancies..."></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-secondary btn-sm px-4 fw-bold" onclick="closeShiftModal()">
                {{ __('Close') }}
            </button>
            <button class="btn btn-danger btn-sm px-4 fw-bold" onclick="submitCloseShiftSession()">
                <i class="fa-solid fa-circle-check me-1"></i>{{ __('Close Register & Exit Shift') }}
            </button>
        </div>
    </div>
</div>

<!-- =====================================================================
     HOLD BILLS LIST — Glassmorphism Modal Overlay (White Glass Card)
===================================================================== -->
<div id="pos-hold-bills-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 999999;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    align-items: center; justify-content: center;
    padding: 24px;
    box-sizing: border-box;
" onclick="if(event.target===this) closeHoldBillsListModal()">

    <div style="
        background: #f8fafc;
        color: #1e293b;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 850px;
        padding: 28px 32px;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
    ">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0" style="color: #0f172a;">
                <i class="fa-solid fa-folder-open me-2 text-warning"></i>{{ __('Hold Bills Register') }}
            </h5>
            <span class="badge bg-secondary py-1 px-2" style="font-size: 11px; background: #64748b !important; color: #fff;">
                {{ __('Navigate: Arrow keys | Restore: Enter | Remove: Delete') }}
            </span>
        </div>

        <!-- Search Filters -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <input type="text" class="form-control bg-white text-dark" id="hold-bills-search-input" placeholder="Search by customer name, phone, or hold number..." onkeyup="filterHoldBillsRegister()" style="border-color: #cbd5e1 !important; height: 32px !important; padding: 4px 8px !important; font-size: 12px !important; box-sizing: border-box !important;">
            </div>
            <div class="col-4">
                <input type="date" class="form-control bg-white text-dark" id="hold-bills-date-input" onchange="filterHoldBillsRegister()" style="border-color: #cbd5e1 !important; height: 32px !important; padding: 4px 8px !important; font-size: 12px !important; box-sizing: border-box !important;">
            </div>
            <div class="col-2">
                <button class="btn btn-outline-secondary w-100 fw-bold text-dark" onclick="resetHoldBillsFilters()" style="border-color: #cbd5e1 !important; height: 32px !important; padding: 0 !important; font-size: 12px !important; background: #ffffff !important; line-height: 30px !important; box-sizing: border-box !important;">
                    Clear
                </button>
            </div>
        </div>

        <!-- Holds Table -->
        <div style="max-height: 320px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff;">
            <table class="table table-sm table-hover align-middle m-0" style="font-size: 12px; color: #1e293b;" id="hold-bills-table">
                <thead class="table-light">
                    <tr>
                        <th style="color: #475569; font-weight: 600;">Hold No</th>
                        <th style="color: #475569; font-weight: 600;">Customer</th>
                        <th style="color: #475569; font-weight: 600;">Phone</th>
                        <th style="color: #475569; font-weight: 600;">Cashier</th>
                        <th class="text-right" style="color: #475569; font-weight: 600;">Bill Amount</th>
                        <th style="color: #475569; font-weight: 600;">Suspended At</th>
                        <th class="text-end" style="width: 240px; color: #475569; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody id="hold-bills-items-body" style="background: #ffffff;">
                    <!-- Populated dynamically via AJAX -->
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-secondary btn-sm px-4 fw-bold text-dark" onclick="closeHoldBillsListModal()" style="border-radius: 8px; border-color: #cbd5e1; background: #ffffff;">
                {{ __('Close (Esc)') }}
            </button>
        </div>
    </div>
</div>

<!-- =====================================================================
     THERMAL BILL PRINT PREVIEW — Glassmorphism Modal Overlay
===================================================================== -->
<div id="pos-invoice-print-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 1000000;
    background: rgba(5, 8, 18, 0.88);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
">
    <div id="pos-invoice-modal-card" style="
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 24px 64px rgba(0,0,0,0.55);
        width: 100%;
        max-width: 400px;
        max-height: 85%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        color: #000;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
        transition: max-width 0.3s ease, height 0.3s ease;
    ">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom" style="background:#f8f9fa;">
            <h6 class="fw-bold m-0" style="color:#1a2035; font-family:sans-serif;">
                <i class="fa-solid fa-print me-2 text-primary"></i><span id="pos-invoice-modal-title">Thermal Invoice Preview</span>
            </h6>
            <button class="btn-close" onclick="closePOSPrintPreview()" style="font-size:18px; outline:none; border:none; background:none; cursor:pointer;">&times;</button>
        </div>

        <div style="overflow-y: auto; flex-grow: 1; padding: 20px; background:#f1f5f9; display: flex; justify-content: center;">
            <div id="pos-invoice-print-body" style="background:#fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 10px; width: 80mm; box-sizing: border-box; text-align: left;">
                <!-- Dynamically loaded layout -->
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 px-4 py-3 border-top" style="background:#f8f9fa; font-family:sans-serif;">
            <button class="btn btn-outline-secondary btn-sm px-4 fw-bold" onclick="closePOSPrintPreview()">
                Close (Esc)
            </button>
            <button class="btn btn-primary btn-sm px-4 fw-bold" onclick="printPOSInvoice()">
                Print (Enter / Any Key)
            </button>
        </div>
    </div>
</div>

<!-- Hidden Iframe for print jobs -->
<iframe id="pos-print-iframe" style="display:none; position:absolute; left:-9999px;"></iframe>

<!-- =====================================================================
     SALES HISTORY — Full-Screen Edge-to-Edge Glassmorphism Overlay
===================================================================== -->
<div id="pos-sales-history-overlay" style="
    display: none;
    position: fixed; inset: 0; z-index: 999999;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    align-items: center; justify-content: center;
    padding: 24px;
    box-sizing: border-box;
">
    <div style="
        background: #f8fafc;
        color: #1e293b;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
    ">
        <!-- Modal Header -->
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
            <div>
                <h4 class="fw-bold m-0 text-dark" style="font-size: 18px; color: #0f172a;">
                    <i class="fa-solid fa-clock-history me-2 text-primary"></i>{{ __('Sales Invoice History Registry') }}
                </h4>
                <small class="text-muted" style="font-size: 11px; color: #64748b;">{{ __('Enterprise ledger audit dashboard & reprints') }}</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm fw-bold px-3" onclick="exportHistoryCSV()" style="border-radius: 8px; background: #10b981; border-color: #10b981;">
                    <i class="fa-solid fa-file-excel me-1"></i>{{ __('Export CSV') }}
                </button>
                <button class="btn btn-outline-secondary btn-sm fw-bold px-3 text-dark" onclick="closeSalesHistoryModal()" style="border-radius: 8px; border-color: #cbd5e1; background: #ffffff;">
                    <i class="fa-solid fa-xmark me-1"></i>{{ __('Close (Esc)') }}
                </button>
            </div>
        </div>

        <!-- Modal Body Content (Scrollable) -->
        <div style="overflow-y: auto; flex-grow: 1; padding: 20px; background: #f1f5f9;">

            <!-- Search & Filters Panel (Single Line Flex Layout) -->
            <div class="card p-3 border-0 mb-3" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div style="flex: 2 1 240px;">
                        <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Search Text</label>
                        <input type="text" class="form-control form-control-sm bg-white text-dark" id="filter-search" placeholder="Invoice No, Customer, Mobile, Barcode..." style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
                    </div>
                    <div style="flex: 1 1 130px;">
                        <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Date From</label>
                        <input type="date" class="form-control form-control-sm bg-white text-dark" id="filter-date-from" style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
                    </div>
                    <div style="flex: 1 1 130px;">
                        <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Date To</label>
                        <input type="date" class="form-control form-control-sm bg-white text-dark" id="filter-date-to" style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
                    </div>
                    <div style="flex: 1 1 130px;">
                        <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Payment Mode</label>
                        <select class="form-select form-select-sm bg-white text-dark" id="filter-payment-mode" style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
                            <option value="">All Modes</option>
                            <option value="Cash">Cash</option>
                            <option value="Online">Online</option>
                            <option value="Split Payment">Split Payment</option>
                        </select>
                    </div>
                    <div style="flex: 1 1 130px;">
                        <label class="form-label text-muted small fw-bold mb-1" style="color: #64748b; font-size: 11px;">Status</label>
                        <select class="form-select form-select-sm bg-white text-dark" id="filter-status" style="border-color: #cbd5e1 !important; height: 32px; font-size: 12px;">
                            <option value="">All Statuses</option>
                            <option value="delivered">Completed</option>
                            <option value="canceled">Cancelled</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2" style="flex: 0 0 180px;">
                        <button class="btn btn-primary btn-sm fw-bold w-100" onclick="applyHistoryFilters()" style="border-radius: 6px; background: #2563eb; border-color: #2563eb; height: 32px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                            <i class="fa-solid fa-magnifying-glass"></i>Filter
                        </button>
                        <button class="btn btn-outline-secondary btn-sm fw-bold w-100 text-dark" onclick="resetHistoryFilters()" style="border-radius: 6px; border-color: #cbd5e1; background: #ffffff; height: 32px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Invoices Table Card -->
            <div class="card border-0" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 68vh; overflow-y: auto;">
                        <table class="table table-hover align-middle m-0" style="font-size: 13px; color: #334155;">
                            <thead class="table-light text-dark" style="position: sticky; top: 0; z-index: 10; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th style="color: #475569; font-weight: 600;">Invoice No</th>
                                    <th style="color: #475569; font-weight: 600;">Customer</th>
                                    <th style="color: #475569; font-weight: 600;">Date</th>
                                    <th class="text-center" style="color: #475569; font-weight: 600;">Total Items</th>
                                    <th class="text-right" style="color: #475569; font-weight: 600;">Gross Amt</th>
                                    <th class="text-right" style="color: #475569; font-weight: 600;">Disc.</th>
                                    <th class="text-right" style="color: #475569; font-weight: 600;">GST</th>
                                    <th class="text-right" style="color: #475569; font-weight: 600;">Net Amount</th>
                                    <th class="text-right" style="color: #475569; font-weight: 600;">Paid Amount</th>
                                    <th style="color: #475569; font-weight: 600;">Payment</th>
                                    <th style="color: #475569; font-weight: 600;">Status</th>
                                    <th class="text-end" style="width: 250px; color: #475569; font-weight: 600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body" style="background: #ffffff;">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination controls -->
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-top:1px solid #e2e8f0; background: #f8fafc;">
                        <div class="text-muted small" style="color: #64748b !important;">
                            Showing <span id="pagination-info-start" class="fw-bold">0</span> to <span id="pagination-info-end" class="fw-bold">0</span> of <span id="pagination-info-total" class="fw-bold">0</span> entries
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm m-0" id="pagination-controls-list">
                                <!-- Controls generated dynamically -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@keyframes exitModalIn {
    from { opacity: 0; transform: scale(0.88) translateY(18px); }
    to   { opacity: 1; transform: scale(1)   translateY(0); }
}
</style>

<div id="floating-calculator" style="display:none;">
    <!-- Drag handle covers the whole widget -->
    <div id="calc-drag-handle">
        <div id="calc-header">
            <span><i class="fa-solid fa-calculator me-2"></i>Calculator</span>
            <button id="calc-close-btn" onclick="toggleOnScreenCalculator()" title="Close (F2)">✕</button>
        </div>

        <!-- Display -->
        <div id="calc-display-area">
            <div id="calc-expression">0</div>
            <div id="calc-result">0</div>
        </div>
    </div>

    <!-- Buttons -->
    <div id="calc-buttons">
        <!-- Row 1 -->
        <button class="calc-btn calc-fn" onclick="pressCalcKey('C')">C</button>
        <button class="calc-btn calc-fn" onclick="pressCalcKey('+/-')">+/-</button>
        <button class="calc-btn calc-fn" onclick="pressCalcKey('%')">%</button>
        <button class="calc-btn calc-op" onclick="pressCalcKey('/')">÷</button>
        <!-- Row 2 -->
        <button class="calc-btn calc-num" onclick="pressCalcKey('7')">7</button>
        <button class="calc-btn calc-num" onclick="pressCalcKey('8')">8</button>
        <button class="calc-btn calc-num" onclick="pressCalcKey('9')">9</button>
        <button class="calc-btn calc-op" onclick="pressCalcKey('*')">×</button>
        <!-- Row 3 -->
        <button class="calc-btn calc-num" onclick="pressCalcKey('4')">4</button>
        <button class="calc-btn calc-num" onclick="pressCalcKey('5')">5</button>
        <button class="calc-btn calc-num" onclick="pressCalcKey('6')">6</button>
        <button class="calc-btn calc-op" onclick="pressCalcKey('-')">-</button>
        <!-- Row 4 -->
        <button class="calc-btn calc-num" onclick="pressCalcKey('1')">1</button>
        <button class="calc-btn calc-num" onclick="pressCalcKey('2')">2</button>
        <button class="calc-btn calc-num" onclick="pressCalcKey('3')">3</button>
        <button class="calc-btn calc-op" onclick="pressCalcKey('+')">+</button>
        <!-- Row 5 -->
        <button class="calc-btn calc-num calc-wide" onclick="pressCalcKey('0')">0</button>
        <button class="calc-btn calc-num" onclick="pressCalcKey('.')">.</button>
        <button class="calc-btn calc-eq" onclick="pressCalcKey('=')"><i class="fa-solid fa-equals"></i></button>
    </div>
</div>

<style>
/* ── Glassmorphism Calculator ─────────────────────────── */
#floating-calculator {
    position: fixed;
    top: 80px;
    right: 30px;
    width: 280px;
    z-index: 99999;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(15, 18, 30, 0.72);
    backdrop-filter: blur(22px) saturate(180%);
    -webkit-backdrop-filter: blur(22px) saturate(180%);
    border: 1px solid rgba(255,255,255,0.12);
    box-shadow:
        0 8px 32px rgba(0,0,0,0.55),
        0 0 0 1px rgba(255,255,255,0.06) inset;
    user-select: none;
}
#calc-drag-handle {
    cursor: grab;
}
#calc-drag-handle:active {
    cursor: grabbing;
}
#calc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px 6px;
    color: rgba(255,255,255,0.85);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.4px;
}
#calc-close-btn {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.7);
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 11px;
    line-height: 1;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
}
#calc-close-btn:hover { background: rgba(220,53,69,0.5); color: #fff; }
/* Display area */
#calc-display-area {
    background: rgba(0,0,0,0.35);
    margin: 8px 12px;
    border-radius: 12px;
    padding: 10px 14px;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 1px solid rgba(255,255,255,0.07);
}
#calc-expression {
    font-size: 13px;
    color: rgba(255,255,255,0.4);
    text-align: right;
    min-height: 18px;
    word-break: break-all;
    letter-spacing: 0.3px;
}
#calc-result {
    font-size: 38px;
    font-weight: 700;
    color: #f8c94c;
    text-align: right;
    line-height: 1.1;
    letter-spacing: -1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}
/* Buttons grid */
#calc-buttons {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 4px 12px 14px;
}
.calc-btn {
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 600;
    height: 56px;
    cursor: pointer;
    transition: transform 0.08s, filter 0.08s, box-shadow 0.08s;
    display: flex; align-items: center; justify-content: center;
    position: relative;
    overflow: hidden;
}
.calc-btn:active {
    transform: scale(0.92);
    filter: brightness(1.3);
}
/* Number keys */
.calc-num {
    background: rgba(80, 85, 110, 0.55);
    color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
.calc-num:hover { background: rgba(100, 108, 140, 0.75); }
/* Function keys (C, +/-, %) */
.calc-fn {
    background: rgba(100, 110, 140, 0.45);
    color: #c8d6ff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
.calc-fn:hover { background: rgba(120, 130, 165, 0.65); }
/* Operator keys (+, -, *, /) */
.calc-op {
    background: rgba(251, 146, 60, 0.82);
    color: #fff;
    box-shadow: 0 2px 10px rgba(251,146,60,0.4);
    font-size: 22px;
}
.calc-op:hover { background: rgba(251, 146, 60, 1); }
.calc-op.active-op {
    background: #fff !important;
    color: #f59332 !important;
}
/* Equals key */
.calc-eq {
    background: linear-gradient(135deg, #f59332, #e67e22);
    color: #fff;
    box-shadow: 0 4px 14px rgba(230,126,34,0.5);
    font-size: 16px;
}
.calc-eq:hover { filter: brightness(1.15); }
/* Wide 0 button spans 2 columns */
.calc-wide {
    grid-column: span 2;
    justify-content: flex-start;
    padding-left: 22px;
}
/* Ripple effect on button press */
.calc-btn::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: rgba(255,255,255,0.18);
    opacity: 0;
    transition: opacity 0.15s;
}
.calc-btn:active::after { opacity: 1; }
</style>

<!-- Manual Product Search Lookup Modal -->
<div class="modal fade" id="productLookupModal" tabindex="-1" aria-labelledby="productLookupLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title" id="productLookupLabel"><i class="fa-solid fa-search me-2"></i>{{ __('Quick Product Search') }}</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <input type="text" class="form-control mb-3" id="lookup-query-input" placeholder="Type Product Name, Design No, or Barcode..." autocomplete="off">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <colgroup>
                                <col style="width: 130px;">
                                <col style="width: 180px;">
                                <col style="width: 160px;">
                                <col style="width: 80px;">
                                <col style="width: 100px;">
                            </colgroup>
                            <thead class="table-dark">
                            <tr>
                                <th>{{ __('Barcode') }}</th>
                                <th>{{ __('Item Description') }}</th>
                                <th>{{ __('Design No') }}</th>
                                <th class="text-center">{{ __('Stock') }}</th>
                                <th style="text-align: right;">{{ __('Rate') }}</th>
                            </tr>
                        </thead>
                        <tbody id="lookup-results-body" class="lookup-results-list">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">{{ __('Start typing to search products...') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeRowIndexForLookup = null;
    let currencySymbol = "{{ $currency }}";
    // Calculator state — declared once here, used by pressCalcKey/toggleOnScreenCalculator
    let calcExpression = "";
    let calcJustEvaled = false;
    let calcActiveOp   = null;

    // Custom centered premium glass Alert / Confirm / Prompt modals matching Exit modal
    window.alert = function(message, type = 'warning') {
        $('#pos-custom-alert-overlay').remove();
        let iconHtml = `
            <div style="width: 72px; height: 72px; background: rgba(220, 53, 69, 0.18); border: 2px solid rgba(220,53,69,0.45); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 28px; color: #e05c6e;"></i>
            </div>
        `;
        if (type === 'success') {
            iconHtml = `
                <div style="width: 72px; height: 72px; background: rgba(40, 167, 69, 0.18); border: 2px solid rgba(40,167,69,0.45); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fa-solid fa-circle-check" style="font-size: 28px; color: #2ecc71;"></i>
                </div>
            `;
        } else if (type === 'info') {
            iconHtml = `
                <div style="width: 72px; height: 72px; background: rgba(54, 162, 235, 0.18); border: 2px solid rgba(54,162,235,0.45); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fa-solid fa-circle-info" style="font-size: 28px; color: #3498db;"></i>
                </div>
            `;
        }

        const overlayHtml = `
            <div id="pos-custom-alert-overlay" style="
                position: fixed; inset: 0; z-index: 9999999;
                background: rgba(0, 0, 0, 0.55);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                display: flex; align-items: center; justify-content: center;
            ">
                <div style="
                    background: rgba(18, 22, 36, 0.78);
                    backdrop-filter: blur(28px) saturate(160%);
                    -webkit-backdrop-filter: blur(28px) saturate(160%);
                    border: 1px solid rgba(255,255,255,0.14);
                    box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.06) inset;
                    border-radius: 24px;
                    padding: 40px 44px 36px;
                    width: 90%;
                    max-width: 420px;
                    color: #fff;
                    text-align: center;
                    animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
                ">
                    <div style="margin-bottom: 16px;">${iconHtml}</div>
                    <h6 class="fw-bold mb-4" style="color: #fff; font-size:16px; line-height: 1.6; font-family: 'Inter', sans-serif;">
                        ${message}
                    </h6>
                    <button class="btn btn-sm px-4 fw-bold w-100" style="
                        background: rgba(255,255,255,0.08);
                        border: 2px solid rgba(255,255,255,0.18);
                        color: rgba(255,255,255,0.85);
                        border-radius: 12px;
                        padding: 12px 0;
                        font-size: 15px;
                        font-weight: 600;
                        cursor: pointer;
                        outline: none;
                        transition: background 0.18s, border-color 0.18s;
                    " id="pos-custom-alert-ok-btn">
                        OK
                    </button>
                </div>
            </div>
        `;
        $('body').append(overlayHtml);
        setTimeout(() => $('#pos-custom-alert-ok-btn').focus(), 50);

        const handleClose = () => {
            window.removeEventListener('keydown', handleAlertKey, { capture: true });
            $('#pos-custom-alert-overlay').remove();
        };

        const handleAlertKey = (e) => {
            if (e.key === 'Enter' || e.keyCode === 13 || e.key === 'Escape' || e.keyCode === 27) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                handleClose();
            }
        };

        $('#pos-custom-alert-ok-btn').on('click', handleClose);
        window.addEventListener('keydown', handleAlertKey, { capture: true, passive: false });
    };

    window.posConfirm = function(message, type = 'warning') {
        return new Promise((resolve) => {
            $('#pos-custom-confirm-overlay').remove();
            let iconHtml = `
                <div style="width: 72px; height: 72px; background: rgba(255, 193, 7, 0.18); border: 2px solid rgba(255,193,7,0.45); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fa-solid fa-circle-question" style="font-size: 28px; color: #f1c40f;"></i>
                </div>
            `;
            let confirmBtnColor = 'linear-gradient(135deg, #3b82f6, #1d4ed8)';
            let confirmBtnShadow = 'rgba(59, 130, 246, 0.45)';
            if (type === 'danger') {
                iconHtml = `
                    <div style="width: 72px; height: 72px; background: rgba(220, 53, 69, 0.18); border: 2px solid rgba(220,53,69,0.45); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 28px; color: #e05c6e;"></i>
                    </div>
                `;
                confirmBtnColor = 'linear-gradient(135deg, #dc3545, #b02a37)';
                confirmBtnShadow = 'rgba(220, 53, 69, 0.45)';
            }

            const overlayHtml = `
                <div id="pos-custom-confirm-overlay" style="
                    position: fixed; inset: 0; z-index: 9999999;
                    background: rgba(0, 0, 0, 0.55);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    display: flex; align-items: center; justify-content: center;
                ">
                    <div style="
                        background: rgba(18, 22, 36, 0.78);
                        backdrop-filter: blur(28px) saturate(160%);
                        -webkit-backdrop-filter: blur(28px) saturate(160%);
                        border: 1px solid rgba(255,255,255,0.14);
                        box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.06) inset;
                        border-radius: 24px;
                        padding: 40px 44px 36px;
                        width: 90%;
                        max-width: 420px;
                        color: #fff;
                        text-align: center;
                        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
                    ">
                        <div style="margin-bottom: 16px;">${iconHtml}</div>
                        <h6 class="fw-bold mb-4" style="color: #fff; font-size:16px; line-height: 1.6; font-family: 'Inter', sans-serif;">
                            ${message}
                        </h6>
                        <div style="display:flex; gap:12px; justify-content:center;">
                            <button id="pos-custom-confirm-cancel-btn" style="
                                flex:1;
                                background: rgba(255,255,255,0.08);
                                border: 2px solid rgba(255,255,255,0.18);
                                color: rgba(255,255,255,0.85);
                                border-radius: 12px;
                                padding: 12px 0;
                                font-size: 15px;
                                font-weight: 600;
                                cursor: pointer;
                                transition: background 0.18s, border-color 0.18s, box-shadow 0.18s;
                                outline: none;
                            ">Cancel</button>
                            <button id="pos-custom-confirm-yes-btn" style="
                                flex:1;
                                background: ${confirmBtnColor};
                                border: 2px solid transparent;
                                color: #fff;
                                border-radius: 12px;
                                padding: 12px 0;
                                font-size: 15px;
                                font-weight: 700;
                                cursor: pointer;
                                box-shadow: 0 4px 18px ${confirmBtnShadow};
                                transition: filter 0.18s, border-color 0.18s, box-shadow 0.18s;
                                outline: none;
                            ">Yes, Proceed</button>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(overlayHtml);

            let _confirmFocused = 'confirm'; // default focus: 'confirm' | 'cancel'

            function _confirmSetFocus(which) {
                _confirmFocused = which;
                const cancelBtn = document.getElementById('pos-custom-confirm-cancel-btn');
                const yesBtn = document.getElementById('pos-custom-confirm-yes-btn');
                if (!cancelBtn || !yesBtn) return;

                // Reset both
                cancelBtn.style.borderColor = 'rgba(255,255,255,0.18)';
                cancelBtn.style.boxShadow = 'none';
                cancelBtn.style.background = 'rgba(255,255,255,0.08)';

                yesBtn.style.borderColor = 'transparent';
                yesBtn.style.boxShadow = `0 4px 18px ${confirmBtnShadow}`;

                if (which === 'cancel') {
                    cancelBtn.style.borderColor = 'rgba(255,255,255,0.85)';
                    cancelBtn.style.boxShadow = '0 0 0 3px rgba(255,255,255,0.25)';
                    cancelBtn.style.background = 'rgba(255,255,255,0.18)';
                } else {
                    yesBtn.style.borderColor = '#fff';
                    yesBtn.style.boxShadow = `0 0 0 3px rgba(59, 130, 246, 0.55), 0 4px 18px ${confirmBtnShadow}`;
                    if (type === 'danger') {
                        yesBtn.style.boxShadow = `0 0 0 3px rgba(220, 53, 69, 0.55), 0 4px 18px ${confirmBtnShadow}`;
                    }
                }
            }

            // Default focus on Yes/Proceed
            _confirmSetFocus('confirm');

            const handleYes = () => {
                window.removeEventListener('keydown', handleConfirmKey, { capture: true });
                $('#pos-custom-confirm-overlay').remove();
                resolve(true);
            };
            const handleCancel = () => {
                window.removeEventListener('keydown', handleConfirmKey, { capture: true });
                $('#pos-custom-confirm-overlay').remove();
                resolve(false);
            };

            const handleConfirmKey = (e) => {
                if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    _confirmSetFocus(_confirmFocused === 'cancel' ? 'confirm' : 'cancel');
                } else if (e.key === 'Enter' || e.keyCode === 13 || e.code === 'Enter' || e.code === 'NumpadEnter') {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    if (_confirmFocused === 'cancel') {
                        handleCancel();
                    } else {
                        handleYes();
                    }
                } else if (e.key === 'Escape' || e.keyCode === 27) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    handleCancel();
                }
            };

            $('#pos-custom-confirm-yes-btn').on('click', handleYes);
            $('#pos-custom-confirm-cancel-btn').on('click', handleCancel);
            window.addEventListener('keydown', handleConfirmKey, { capture: true, passive: false });
        });
    };

    window.posPrompt = function(message, defaultValue = '') {
        return new Promise((resolve) => {
            $('#pos-custom-prompt-overlay').remove();
            const overlayHtml = `
                <div id="pos-custom-prompt-overlay" style="
                    position: fixed; inset: 0; z-index: 9999999;
                    background: rgba(0, 0, 0, 0.55);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    display: flex; align-items: center; justify-content: center;
                ">
                    <div style="
                        background: rgba(18, 22, 36, 0.78);
                        backdrop-filter: blur(28px) saturate(160%);
                        -webkit-backdrop-filter: blur(28px) saturate(160%);
                        border: 1px solid rgba(255,255,255,0.14);
                        box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.06) inset;
                        border-radius: 24px;
                        padding: 40px 44px 36px;
                        width: 90%;
                        max-width: 420px;
                        color: #fff;
                        text-align: left;
                        animation: exitModalIn 0.22s cubic-bezier(.4,0,.2,1);
                    ">
                        <h6 class="fw-bold mb-3" style="color: #fff; font-size:15px; font-family: 'Inter', sans-serif;">${message}</h6>
                        <div class="mb-4">
                            <input type="text" class="form-control" id="pos-custom-prompt-input" value="${defaultValue}" style="
                                background: rgba(255, 255, 255, 0.08);
                                border: 1px solid rgba(255, 255, 255, 0.2);
                                color: #fff;
                                border-radius: 8px;
                                padding: 10px 14px;
                                outline: none;
                                transition: border-color 0.18s, box-shadow 0.18s;
                            ">
                        </div>
                        <div style="display:flex; gap:12px; justify-content:center;">
                            <button id="pos-custom-prompt-cancel-btn" style="
                                flex:1;
                                background: rgba(255,255,255,0.08);
                                border: 2px solid rgba(255,255,255,0.18);
                                color: rgba(255,255,255,0.85);
                                border-radius: 12px;
                                padding: 12px 0;
                                font-size: 15px;
                                font-weight: 600;
                                cursor: pointer;
                                transition: background 0.18s, border-color 0.18s, box-shadow 0.18s;
                                outline: none;
                            ">Cancel</button>
                            <button id="pos-custom-prompt-ok-btn" style="
                                flex:1;
                                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                                border: 2px solid transparent;
                                color: #fff;
                                border-radius: 12px;
                                padding: 12px 0;
                                font-size: 15px;
                                font-weight: 700;
                                cursor: pointer;
                                box-shadow: 0 4px 18px rgba(59,130,246,0.45);
                                transition: filter 0.18s, border-color 0.18s, box-shadow 0.18s;
                                outline: none;
                            ">OK</button>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(overlayHtml);
            const inputEl = $('#pos-custom-prompt-input');
            inputEl.focus();
            inputEl.select();

            inputEl.on('focus', function() {
                $(this).css({
                    'border-color': 'rgba(59, 130, 246, 0.85)',
                    'box-shadow': '0 0 0 3px rgba(59, 130, 246, 0.25)'
                });
            }).on('blur', function() {
                $(this).css({
                    'border-color': 'rgba(255, 255, 255, 0.2)',
                    'box-shadow': 'none'
                });
            });

            // Default focus styling on input
            inputEl.trigger('focus');

            const handleOk = () => {
                const val = inputEl.val();
                window.removeEventListener('keydown', handlePromptKey, { capture: true });
                $('#pos-custom-prompt-overlay').remove();
                resolve(val);
            };
            const handleCancel = () => {
                window.removeEventListener('keydown', handlePromptKey, { capture: true });
                $('#pos-custom-prompt-overlay').remove();
                resolve(null);
            };

            const handlePromptKey = (e) => {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    handleOk();
                } else if (e.key === 'Escape' || e.keyCode === 27) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    handleCancel();
                }
            };

            $('#pos-custom-prompt-ok-btn').on('click', handleOk);
            $('#pos-custom-prompt-cancel-btn').on('click', handleCancel);
            window.addEventListener('keydown', handlePromptKey, { capture: true, passive: false });
        });
    };

    // Intercept native browser help window call completely
    window.onhelp = function(e) {
        e.preventDefault();
        return false;
    };

    // ================================================================
    // GLOBAL POS SHORTCUT KEYS
    // ================================================================
    // Chrome/Edge IGNORE e.preventDefault() on F1 unless the listener
    // is registered at window level with {capture:true, passive:false}.
    // We also block the keyup phase so no secondary browser handler fires.
    // ================================================================

    // Block F1-F12 on KEYUP too (Chrome fires help on keyup in some builds)
    window.addEventListener('keyup', function(e) {
        if (e.keyCode >= 112 && e.keyCode <= 123) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, { capture: true, passive: false });

    // Legacy IE/Firefox
    window.onhelp = function() { return false; };
    if (document.onhelp !== undefined) { document.onhelp = function() { return false; }; }

    // =========================================================================
    // WIRELESS / USB BARCODE SCANNER HARDWARE WEDGE INTERCEPTOR
    // =========================================================================
    let _scannerBuffer = '';
    let _lastScanTime = 0;
    const SCANNER_BURST_THRESHOLD_MS = 60; // Wireless/HID barcode scanners send characters < 40ms apart

    window.addEventListener('keydown', function(e) {
        // Skip global interceptor if modals or full-screen overlays are open
        if (
            $('#productLookupModal').hasClass('show') ||
            document.getElementById('pos-custom-confirm-overlay') ||
            document.getElementById('pos-custom-alert-overlay') ||
            document.getElementById('pos-custom-prompt-overlay') ||
            (document.getElementById('pos-exit-overlay') && document.getElementById('pos-exit-overlay').style.display === 'flex') ||
            (document.getElementById('pos-shift-close-overlay') && document.getElementById('pos-shift-close-overlay').style.display === 'flex') ||
            (document.getElementById('pos-hold-bills-overlay') && document.getElementById('pos-hold-bills-overlay').style.display === 'flex') ||
            (document.getElementById('pos-invoice-print-overlay') && document.getElementById('pos-invoice-print-overlay').style.display === 'flex') ||
            (document.getElementById('pos-sales-history-overlay') && document.getElementById('pos-sales-history-overlay').style.display === 'flex') ||
            (document.getElementById('floating-calculator') && document.getElementById('floating-calculator').style.display !== 'none')
        ) {
            _scannerBuffer = '';
            return;
        }

        const now = Date.now();
        const diff = now - _lastScanTime;
        _lastScanTime = now;

        // If Enter is pressed and buffer has at least 2 characters from rapid scanning
        if (e.key === 'Enter' || e.keyCode === 13) {
            if (_scannerBuffer.length >= 2) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const barcode = _scannerBuffer;
                _scannerBuffer = '';
                processScannedBarcode(barcode);
                return false;
            }
            _scannerBuffer = '';
            return;
        }

        // Buffer printable single character keys (letters, digits, dashes)
        if (e.key && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
            if (diff > SCANNER_BURST_THRESHOLD_MS && _scannerBuffer.length > 0) {
                // Slower than scanner threshold -> human typing reset
                _scannerBuffer = '';
            }
            _scannerBuffer += e.key;

            // Auto-clear buffer if timeout expires
            setTimeout(() => {
                if (Date.now() - _lastScanTime >= 100) {
                    _scannerBuffer = '';
                }
            }, 120);
        }
    }, { capture: true, passive: false });

    // Main shortcut capture — runs BEFORE any other handler on the page
    window.addEventListener('keydown', function(e) {

        // ── Skip shortcuts if custom confirmation or alert overlays are active ──
        if (
            document.getElementById('pos-custom-confirm-overlay') ||
            document.getElementById('pos-custom-alert-overlay') ||
            document.getElementById('pos-custom-prompt-overlay')
        ) {
            return;
        }

        // ── Print Preview Overlay Handler ───────────────────────────
        const printOverlay = document.getElementById('pos-invoice-print-overlay');
        if (printOverlay && printOverlay.style.display === 'flex') {
            if (e.key === 'Escape' || e.keyCode === 27) {
                // Let the Escape handler capture it
            } else {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                printPOSInvoice();
                return false;
            }
        }

        // ── F1: Quick Product Search / Add Row ──────────────────────
        if (e.key === 'F1' || e.keyCode === 112) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            triggerSearchFlow();
            return false;
        }

        // ── F2: Toggle Calculator ───────────────────────────────────
        if (e.key === 'F2' || e.keyCode === 113) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            toggleOnScreenCalculator();
            return false;
        }

        // ── F3: Focus Customer Mobile ───────────────────────────────
        if (e.key === 'F3' || e.keyCode === 114) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            $('#cust-basic-tab').click();
            $('#customer-phone').focus().select();
            return false;
        }

        // ── F6: Focus Payment Method ────────────────────────────────
        if (e.key === 'F6' || e.keyCode === 117) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            $('#pay-basic-tab').click();
            $('#payment-method').focus();
            return false;
        }

        // ── F11: Focus Cash Received ────────────────────────────────
        if ((e.key === 'F11' || e.keyCode === 122) && !e.shiftKey && !e.ctrlKey && !e.altKey) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            $('#pay-basic-tab').click();
            $('#cash-received').focus().select();
            return false;
        }

        // ── F9: Pay Cash ────────────────────────────────────────────
        if (e.key === 'F9' || e.keyCode === 120) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            $('#payment-method').val('cash').trigger('change');
            $('#cash-received').focus().select();
            return false;
        }

        // ── F12: Save & Print ───────────────────────────────────────
        if (e.key === 'F12' || e.keyCode === 123) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            submitGridOrder();
            return false;
        }

        // ── F4: Clear Current Cart Items ────────────────────────────
        if (e.key === 'F4' || e.keyCode === 115) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            clearCartGrid();
            return false;
        }

        // ── F5: Recall Suspended Bills ──────────────────────────────
        if (e.key === 'F5' || e.keyCode === 116) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            showRecallModal();
            return false;
        }

        // ── F7: Return / Exchange Credit Note ────────────────────────
        if (e.key === 'F7' || e.keyCode === 118) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            showReturnF7Modal();
            return false;
        }

        // ── F8: Close Shift Session ──────────────────────────────────
        if (e.key === 'F8' || e.keyCode === 119) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            showCloseShiftModal();
            return false;
        }

        // ── F10: Save / Suspend Hold Bill ────────────────────────────
        if ((e.key === 'F10' || e.keyCode === 121) && !e.shiftKey && !e.ctrlKey && !e.altKey) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            saveHoldBillSession();
            return false;
        }

        // ── Ctrl+H: Open Hold Bills List ─────────────────────────────
        if (e.ctrlKey && (e.key === 'h' || e.key === 'H' || e.keyCode === 72)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            showHoldBillsListModal();
            return false;
        }

        // ── Arrow Keys, Enter & Delete navigation inside Hold Bills Modal ──
        const holdOverlay = document.getElementById('pos-hold-bills-overlay');
        if (holdOverlay && holdOverlay.style.display === 'flex') {
            const activeRow = $('#hold-bills-items-body tr.active-row');
            if (e.keyCode === 40 || e.key === 'ArrowDown') { // Down
                e.preventDefault();
                let next = activeRow.next('tr');
                if (next.length === 0) next = $('#hold-bills-items-body tr').first();
                $('#hold-bills-items-body tr').removeClass('active-row bg-primary text-white');
                next.addClass('active-row bg-primary text-white');
                return false;
            }
            if (e.keyCode === 38 || e.key === 'ArrowUp') { // Up
                e.preventDefault();
                let prev = activeRow.prev('tr');
                if (prev.length === 0) prev = $('#hold-bills-items-body tr').last();
                $('#hold-bills-items-body tr').removeClass('active-row bg-primary text-white');
                prev.addClass('active-row bg-primary text-white');
                return false;
            }
            if (e.keyCode === 13 || e.key === 'Enter') { // Enter -> Restore
                e.preventDefault();
                if (activeRow.length > 0) {
                    const id = activeRow.data('id');
                    restoreHoldBillRecord(id);
                }
                return false;
            }
            if (e.keyCode === 46 || e.key === 'Delete') { // Delete -> Remove
                e.preventDefault();
                if (activeRow.length > 0) {
                    const id = activeRow.data('id');
                    deleteHoldBillRecord(id);
                }
                return false;
            }
        }

        // ── Escape: contextual close ────────────────────────────────
        if (e.key === 'Escape' || e.keyCode === 27) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const exitOverlay = document.getElementById('pos-exit-overlay');
            const recallOverlay = document.getElementById('pos-recall-overlay');
            const returnOverlay = document.getElementById('pos-return-overlay');
            const shiftCloseOverlay = document.getElementById('pos-shift-close-overlay');
            const holdBillsOverlay = document.getElementById('pos-hold-bills-overlay');
            const printOverlay = document.getElementById('pos-invoice-print-overlay');
            const historyOverlay = document.getElementById('pos-sales-history-overlay');
            if (printOverlay && printOverlay.style.display === 'flex') {
                closePOSPrintPreview();
            } else if (historyOverlay && historyOverlay.style.display === 'flex') {
                closeSalesHistoryModal();
            } else if (exitOverlay && exitOverlay.style.display === 'flex') {
                closeExitModal();   // Esc closes the exit dialog itself
            } else if (recallOverlay && recallOverlay.style.display === 'flex') {
                closeRecallModal(); // Esc closes recall dialog
            } else if (returnOverlay && returnOverlay.style.display === 'flex') {
                closeReturnModal(); // Esc closes return dialog
            } else if (shiftCloseOverlay && shiftCloseOverlay.style.display === 'flex') {
                closeShiftModal();  // Esc closes shift close modal
            } else if (holdBillsOverlay && holdBillsOverlay.style.display === 'flex') {
                closeHoldBillsListModal(); // Esc closes hold bills modal
            } else if ($('#productLookupModal').hasClass('show')) {
                $('#productLookupModal').modal('hide');
            } else {
                const calcEl = document.getElementById('floating-calculator');
                if (calcEl && calcEl.style.display !== 'none') {
                    toggleOnScreenCalculator();
                } else {
                    confirmExit();  // show glass confirmation instead of direct exit
                }
            }
            return false;
        }

    }, { capture: true, passive: false });

    $(document).ready(function() {
        window._activeExchangeSession = null;
        $('#exchange-active-banner').removeClass('d-flex').addClass('d-none').hide();
        $('#summary-exchange-row').hide().remove();

        // Clear browser form autocompleted summaries
        $('#summary-taxable').val('0.00');
        $('#summary-discount').val('0.00');
        $('#summary-tax').val('0.00');
        $('#summary-roundoff').val('0.00');
        $('#tax-cgst').val('0.00');
        $('#tax-sgst').val('0.00');
        $('#tax-igst').val('0.00');
        $('#big-bill-amount').text(currencySymbol + '0.00');
        $('#cash-received').val('0.00');
        $('#big-change-due').text(currencySymbol + '0.00');

        // Initial setup: start with one empty row
        addGridRow();
        setTimeout(() => {
            $('#sales-grid-body tr:first .barcode-input').focus().select();
        }, 200);

        // Start Clock
        setInterval(updateClock, 1000);
        updateClock();
        _billStartTime = null;

        // Enter key navigation inside customer details tab (#cust-basic)
        $(document).on('keydown', '#cust-basic input', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const inputs = $('#cust-basic input:not([readonly]):visible');
                const idx = inputs.index(this);
                if (idx > -1 && idx < inputs.length - 1) {
                    inputs.eq(idx + 1).focus().select();
                } else {
                    // Jump to Payment Mode
                    $('#pay-basic-tab').click();
                    $('#payment-method').focus();
                }
            }
        });

        // Enter key navigation inside payment tenders card (#pay-basic)
        $(document).on('keydown', '#pay-basic select, #pay-basic input', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                const inputs = $('#pay-basic select, #pay-basic input:not([readonly]):visible');
                const idx = inputs.index(this);
                if (idx > -1 && idx < inputs.length - 1) {
                    e.preventDefault();
                    inputs.eq(idx + 1).focus().select();
                } else {
                    // Last payment field -> submit order
                    e.preventDefault();
                    submitGridOrder();
                }
            }
        });

        // Shift + Delete / Shift + Backspace deletes the active row in sales grid
        $(document).on('keydown', '#sales-grid input, #sales-grid select', function(e) {
            if ((e.key === 'Delete' || e.key === 'Backspace') && e.shiftKey) {
                e.preventDefault();
                const tr = $(this).closest('tr');
                const rowId = tr.data('row-id');
                deleteGridRow(rowId);
            }
        });

        // Track currently active focused row to map F1 search actions
        $(document).on('focus', '.barcode-input, .qty, .sales-rate, .disc-percent', function() {
            activeRowIndexForLookup = $(this).closest('tr').attr('data-row-id') || $(this).closest('tr').data('row-id');
        });

        // Make calculator draggable (pure JS drag is initialised later — no jQuery UI needed)

        // ---------------------------------------------------------------
        // Quick Product Search: keyboard navigation
        // KEY DESIGN: keydown handles navigation only (arrow/enter).
        //             oninput handles search text changes only.
        //             The two paths are completely separate so that
        //             pressing ArrowDown never triggers a search re-render
        //             and therefore never resets the highlighted row.
        // ---------------------------------------------------------------

        // Navigation keys that must NOT trigger a search re-render
        const NAV_KEYS = new Set([
            'ArrowDown', 'ArrowUp', 'Enter', 'Escape',
            'Tab', 'Shift', 'Control', 'Alt', 'Meta',
            'PageUp', 'PageDown', 'Home', 'End'
        ]);

        // oninput fires ONLY when the text value actually changes
        // (not on ArrowDown, ArrowUp, Enter, Escape, etc.)
        $('#lookup-query-input').on('input', function() {
            queryPOSProducts(this.value);
        });

        // keydown handles ONLY navigation — never triggers search
        $('#lookup-query-input').on('keydown', function(e) {
            // Completely ignore keys that are not navigation
            if (!NAV_KEYS.has(e.key)) return;

            let rows      = $('#lookup-results-body tr.lookup-item-row');
            let activeRow = $('#lookup-results-body tr.selected-active');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (rows.length === 0) return;
                if (activeRow.length === 0) {
                    // Nothing selected yet — highlight first row
                    rows.first().addClass('selected-active');
                    scrollModalRowIntoView(rows.first());
                } else {
                    let next = activeRow.next('.lookup-item-row');
                    if (next.length > 0) {
                        activeRow.removeClass('selected-active');
                        next.addClass('selected-active');
                        scrollModalRowIntoView(next);
                    }
                    // If already on last row, do nothing (no wrap-around)
                }
            }
            else if (e.key === 'ArrowUp') {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (rows.length === 0) return;
                if (activeRow.length > 0) {
                    let prev = activeRow.prev('.lookup-item-row');
                    if (prev.length > 0) {
                        activeRow.removeClass('selected-active');
                        prev.addClass('selected-active');
                        scrollModalRowIntoView(prev);
                    }
                    // If already on first row, do nothing
                }
            }
            else if (e.key === 'Enter') {
                e.preventDefault();
                e.stopImmediatePropagation();
                // Click whichever row is currently highlighted
                if (activeRow.length > 0) {
                    activeRow.trigger('click');
                }
            }
            else if (e.key === 'Escape') {
                e.preventDefault();
                e.stopImmediatePropagation();
                $('#productLookupModal').modal('hide');
            }
        });

        // ─── When modal closes via X / Escape / backdrop ───────────────────
        // Return focus to the barcode field of the active row so the cashier
        // can immediately scan/type a barcode without re-clicking.
        $('#productLookupModal').on('hidden.bs.modal', function() {
            if (activeRowIndexForLookup) {
                const barcodeInput = $(`tr[data-row-id="${activeRowIndexForLookup}"] .barcode-input`);
                if (barcodeInput.length) {
                    barcodeInput.focus().select();
                }
            }
        });

        // ─── When modal opens, clear stale highlighted rows ────────────────
        $('#productLookupModal').on('show.bs.modal', function() {
            $('#lookup-results-body tr').removeClass('selected-active');
        });

        // ─── Delegate barcode Enter handler (backup for dynamically added rows)
        // The inline onkeydown on .barcode-input handles it, but if focus
        // returns to the input after modal close the delegate also catches it.
        $(document).on('keydown', '.barcode-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopImmediatePropagation();
                const barcode = $(this).val().trim();
                const rowId   = $(this).closest('tr').data('row-id');
                if (barcode && rowId) {
                    processScannedBarcode(barcode, rowId);
                }
            }
        });

    });

    // ================================================================
    // EXIT CONFIRMATION MODAL — with keyboard navigation
    // ArrowLeft / ArrowRight  → switch between Stay and Yes,Exit
    // Enter                   → activate focused button
    // Escape                  → same as Stay (handled in global handler)
    // ================================================================

    let _exitFocused = 'stay'; // 'stay' | 'exit' — tracks keyboard focus

    function _exitSetFocus(which) {
        _exitFocused = which;
        const stayBtn    = document.getElementById('exit-stay-btn');
        const confirmBtn = document.getElementById('exit-confirm-btn');
        if (!stayBtn || !confirmBtn) return;

        // Reset both
        stayBtn.style.borderColor  = 'rgba(255,255,255,0.18)';
        stayBtn.style.boxShadow    = 'none';
        confirmBtn.style.borderColor = 'transparent';
        confirmBtn.style.boxShadow   = '0 4px 18px rgba(220,53,69,0.45)';

        if (which === 'stay') {
            stayBtn.style.borderColor = 'rgba(255,255,255,0.85)';
            stayBtn.style.boxShadow   = '0 0 0 3px rgba(255,255,255,0.25)';
            stayBtn.style.background  = 'rgba(255,255,255,0.18)';
        } else {
            confirmBtn.style.borderColor = '#fff';
            confirmBtn.style.boxShadow   = '0 0 0 3px rgba(220,53,69,0.55), 0 4px 18px rgba(220,53,69,0.45)';
        }
    }

    // Keyboard handler — registered only while the exit modal is open
    function _exitKeyHandler(e) {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            e.preventDefault();
            e.stopImmediatePropagation();
            _exitSetFocus(_exitFocused === 'stay' ? 'exit' : 'stay');
        } else if (e.key === 'Enter') {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (_exitFocused === 'stay') {
                closeExitModal();
            } else {
                window.location.href = document.getElementById('exit-confirm-btn').href;
            }
        }
        // Escape is already handled by the global capture handler
    }

    function confirmExit() {
        const overlay = document.getElementById('pos-exit-overlay');
        if (!overlay) return;
        overlay.style.display = 'flex';
        // Default focus on Stay (safe) and apply highlight
        _exitSetFocus('stay');
        // Register keyboard listener at capture level so it runs first
        window.addEventListener('keydown', _exitKeyHandler, { capture: true, passive: false });
    }

    function closeExitModal() {
        const overlay = document.getElementById('pos-exit-overlay');
        if (!overlay) return;
        overlay.style.display = 'none';
        // Remove the exit-specific keyboard listener
        window.removeEventListener('keydown', _exitKeyHandler, { capture: true });
        // Reset stay button background when closed
        const stayBtn = document.getElementById('exit-stay-btn');
        if (stayBtn) stayBtn.style.background = 'rgba(255,255,255,0.08)';
    }

    // Close exit modal on Escape ONLY when exit overlay is visible
    // (handled inside the global Escape handler above)

    function scrollModalRowIntoView(rowElement) {
        let container = $('.lookup-results-list');
        if (container.length === 0) return;

        // Use the raw DOM offsetTop (relative to scrollable parent) — avoids
        // the double-scroll-offset bug that jQuery .position().top causes when
        // the container is already scrolled down.
        let containerEl   = container[0];
        let elemEl        = rowElement[0];

        let containerScrollTop    = containerEl.scrollTop;
        let containerVisibleBottom = containerScrollTop + containerEl.clientHeight;

        let elemTop    = elemEl.offsetTop;
        let elemBottom = elemTop + elemEl.offsetHeight;

        if (elemTop < containerScrollTop) {
            containerEl.scrollTop = elemTop;
        } else if (elemBottom > containerVisibleBottom) {
            containerEl.scrollTop = elemBottom - containerEl.clientHeight;
        }
    }

    function updateClock() {
        const now = new Date();
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        const dayName = days[now.getDay()];
        const dateNum = String(now.getDate()).padStart(2, '0');
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        
        const timeStr = now.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        
        const formatted = `${dayName}, ${dateNum} ${monthName} ${year}, ${timeStr}`;
        $('#digital-clock').text(formatted);
    }

    // =========================================================================
    // POS BILLING SPEED TIMER (Background Employee Performance Tracking)
    // Starts calculation ONLY when the first barcode is scanned or product added
    // =========================================================================
    let _billStartTime = null;

    function markFirstItemBillingStarted() {
        if (!_billStartTime) {
            _billStartTime = Date.now();
        }
    }

    function resetBillTimer() {
        _billStartTime = null;
    }

    function getBillDurationSeconds() {
        if (!_billStartTime) return 0;
        return Math.max(1, Math.round((Date.now() - _billStartTime) / 1000));
    }

    function getBillStartedAt() {
        if (!_billStartTime) return null;
        return new Date(_billStartTime).toISOString();
    }

    // ================================================================
    // GLASSMORPHISM CALCULATOR — toggle, keyboard, drag
    // ================================================================
    // Variables already declared at top of script block.

    function _calcUpdateDisplay() {
        const resultEl    = document.getElementById('calc-result');
        const expressionEl = document.getElementById('calc-expression');
        if (!resultEl) return;

        // Show expression above; show current input (or result) below
        expressionEl.textContent = calcExpression || '0';

        // Try to live-evaluate so the user sees a running result
        try {
            const cleaned = calcExpression.replace(/[^0-9+\-*/%.]/g, '');
            if (cleaned) {
                const live = Function('"use strict";return (' + cleaned + ')')();
                // Only show live result if it differs from expression
                if (String(live) !== cleaned) {
                    resultEl.textContent = parseFloat(live.toFixed(10));
                } else {
                    resultEl.textContent = calcExpression || '0';
                }
            } else {
                resultEl.textContent = '0';
            }
        } catch(e) {
            resultEl.textContent = calcExpression || '0';
        }
    }

    // Highlight active operator button
    function _calcHighlightOp(op) {
        document.querySelectorAll('.calc-op').forEach(b => b.classList.remove('active-op'));
        if (op) {
            document.querySelectorAll('.calc-op').forEach(b => {
                if (b.textContent.trim() === op) b.classList.add('active-op');
            });
        }
    }

    function toggleOnScreenCalculator() {
        const el = document.getElementById('floating-calculator');
        if (!el) return;
        const visible = el.style.display !== 'none';
        el.style.display = visible ? 'none' : 'block';
        if (!visible) {
            // Reset on open
            calcExpression = '';
            calcJustEvaled = false;
            _calcUpdateDisplay();
            _calcHighlightOp(null);
        }
    }

    function pressCalcKey(key) {
        const OPERATORS = ['+', '-', '*', '/'];

        if (key === 'C') {
            calcExpression = '';
            calcJustEvaled = false;
            _calcHighlightOp(null);
        }
        else if (key === 'back' || key === 'Backspace') {
            if (calcJustEvaled) { calcExpression = ''; calcJustEvaled = false; }
            else calcExpression = calcExpression.slice(0, -1);
        }
        else if (key === '+/-') {
            // Negate: wrap last number in -()
            if (calcExpression) {
                // Simple toggle: if expression is a pure number, negate it
                const num = parseFloat(calcExpression);
                if (!isNaN(num) && String(num) === calcExpression) {
                    calcExpression = String(-num);
                } else {
                    calcExpression = '-(' + calcExpression + ')';
                }
            }
        }
        else if (key === '%') {
            try {
                const cleaned = calcExpression.replace(/[^0-9+\-*/%.]/g, '');
                const result = Function('"use strict";return (' + cleaned + ')')();
                calcExpression = String(parseFloat((result / 100).toFixed(10)));
                calcJustEvaled = true;
            } catch(e) { calcExpression = 'Error'; }
        }
        else if (key === '=') {
            try {
                const cleaned = calcExpression.replace(/[^0-9+\-*/%.]/g, '');
                const result = Function('"use strict";return (' + cleaned + ')')();
                calcExpression = String(parseFloat(result.toFixed(10)));
                calcJustEvaled = true;
                _calcHighlightOp(null);
            } catch(e) {
                calcExpression = 'Error';
                calcJustEvaled = false;
            }
        }
        else if (OPERATORS.includes(key)) {
            if (calcJustEvaled) calcJustEvaled = false;
            // Avoid double operators — replace trailing operator
            if (calcExpression && OPERATORS.includes(calcExpression.slice(-1))) {
                calcExpression = calcExpression.slice(0, -1);
            }
            calcExpression += key;
            _calcHighlightOp(
                key === '*' ? '×' : key === '/' ? '÷' : key
            );
        }
        else if (key === '.') {
            if (calcJustEvaled) { calcExpression = '0'; calcJustEvaled = false; }
            // Only add dot if last segment doesn't already have one
            const lastNum = calcExpression.split(/[+\-*/]/).pop();
            if (!lastNum.includes('.')) {
                calcExpression += (calcExpression === '' ? '0' : '') + '.';
            }
        }
        else if ('0123456789'.includes(key)) {
            if (calcJustEvaled) { calcExpression = ''; calcJustEvaled = false; _calcHighlightOp(null); }
            // Replace leading lone zero
            if (calcExpression === '0') calcExpression = '';
            calcExpression += key;
        }

        _calcUpdateDisplay();
    }

    // ── Keyboard support for calculator ─────────────────────────────────
    // Uses WINDOW CAPTURE PHASE so keystrokes are intercepted BEFORE any
    // focused grid input, barcode field, or other element receives them.
    // This means the user can type 7+3= on the keyboard the moment the
    // calculator is open — no need to click anywhere first.
    //
    // Guard: skip if the Quick Product Search modal is open so that the
    // cashier can still type a product name without feeding the calculator.
    // ─────────────────────────────────────────────────────────────────────
    window.addEventListener('keydown', function(e) {

        // Only active when calculator is visible
        const calc = document.getElementById('floating-calculator');
        if (!calc || calc.style.display === 'none') return;

        // Never steal keys while the product search modal is open
        if (document.getElementById('productLookupModal') &&
            document.getElementById('productLookupModal').classList.contains('show')) return;

        // Keys we handle for the calculator
        const CALC_KEYS = new Set([
            '0','1','2','3','4','5','6','7','8','9',
            '+','-','*','/','.','%',
            'Enter','Backspace','Delete','Escape'
        ]);

        if (!CALC_KEYS.has(e.key)) return; // let all other keys pass through normally

        // Prevent default FIRST so the key never reaches the focused input
        e.preventDefault();
        e.stopImmediatePropagation();

        const key = e.key;
        if ('0123456789'.includes(key)) pressCalcKey(key);
        else if (key === '+')           pressCalcKey('+');
        else if (key === '-')           pressCalcKey('-');
        else if (key === '*')           pressCalcKey('*');
        else if (key === '/')           pressCalcKey('/');
        else if (key === '%')           pressCalcKey('%');
        else if (key === '.')           pressCalcKey('.');
        else if (key === 'Enter')       pressCalcKey('=');
        else if (key === 'Backspace')   pressCalcKey('back');
        else if (key === 'Delete')      pressCalcKey('C');
        else if (key === 'Escape')      toggleOnScreenCalculator();

    }, { capture: true, passive: false });

    // ── Drag anywhere on screen (pure JS, no jQuery UI) ───────────────
    (function initCalcDrag() {
        const calc   = document.getElementById('floating-calculator');
        const handle = document.getElementById('calc-drag-handle');
        if (!calc || !handle) return;

        let dragging = false, startX, startY, origLeft, origTop;

        handle.addEventListener('mousedown', function(e) {
            // Don't start drag on the close button
            if (e.target.id === 'calc-close-btn') return;
            dragging = true;
            startX   = e.clientX;
            startY   = e.clientY;
            const rect = calc.getBoundingClientRect();
            origLeft = rect.left;
            origTop  = rect.top;
            calc.style.right  = 'auto'; // switch from right/bottom anchor to left/top
            calc.style.bottom = 'auto';
            calc.style.left   = origLeft + 'px';
            calc.style.top    = origTop  + 'px';
            e.preventDefault();
        });

        document.addEventListener('mousemove', function(e) {
            if (!dragging) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            let newLeft = origLeft + dx;
            let newTop  = origTop  + dy;
            // Keep inside viewport
            newLeft = Math.max(0, Math.min(newLeft, window.innerWidth  - calc.offsetWidth));
            newTop  = Math.max(0, Math.min(newTop,  window.innerHeight - calc.offsetHeight));
            calc.style.left = newLeft + 'px';
            calc.style.top  = newTop  + 'px';
        });

        document.addEventListener('mouseup', function() { dragging = false; });
    })();

    // Initialise display on DOM ready
    $(document).ready(function() {
        _calcUpdateDisplay();
    });

    // Add row to grid
    function addGridRow() {
        const body = $('#sales-grid-body');
        const index = body.children().length + 1;

        let salesmenOptions = `<option value="">{{ __('No Salesman') }}</option>`;
        @foreach($salesmans as $sm)
            salesmenOptions += `<option value="{{ $sm->id }}">{{ $sm->name }}</option>`;
        @endforeach

        let colorOptions = `<option value="">-</option>`;
        let sizeOptions = `<option value="">-</option>`;

        const rowHtml = `
            <tr data-row-id="${index}">
                <td class="text-center fw-bold text-muted row-number">${index}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="text" class="barcode-input" name="items[${index}][barcode]" placeholder="Scan barcode" onkeydown="handleBarcodeKeydown(event, $(this).closest('tr').data('row-id'))">
                        <button class="btn btn-sm text-primary p-0 px-1 border-0" type="button" onclick="openLookupModal($(this).closest('tr').data('row-id'))">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>
                </td>
                <td><input type="text" class="item-name" readonly></td>
                <td><input type="text" class="hsn-code" readonly></td>
                <td>
                    <select class="salesman-id salesman-select" name="items[${index}][salesman_id]">
                        ${salesmenOptions}
                    </select>
                </td>
                <td><input type="text" class="design-no" readonly></td>
                <td>
                    <select class="color-select" name="items[${index}][color]">
                        ${colorOptions}
                    </select>
                </td>
                <td>
                    <select class="size-select" name="items[${index}][size]">
                        ${sizeOptions}
                    </select>
                </td>
                <td><input type="number" step="0.01" class="sales-rate text-end" name="items[${index}][rate]" value="0.00" onkeyup="recalculateCart()"></td>
                <td><input type="number" class="qty text-center" name="items[${index}][qty]" value="0" onkeyup="recalculateCart()" onkeydown="handleQtyKeydown(event, $(this).closest('tr').data('row-id'))"></td>
                <td><input type="number" step="0.1" class="disc-percent text-center" name="items[${index}][disc_percent]" value="0.0" onkeyup="handleDiscPercentChange($(this).closest('tr').data('row-id'))"></td>
                <td><input type="number" step="0.01" class="disc-amt text-end" value="0.00" onkeyup="handleDiscAmtChange($(this).closest('tr').data('row-id'))"></td>
                <td><input type="number" class="tax-percent text-center" value="0" readonly></td>
                <td><input type="text" class="net-amt text-end fw-bold" value="0.00" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn-grid-delete" onclick="deleteGridRow($(this).closest('tr').data('row-id'))" title="Delete Row (Shift + Delete / Backspace)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        body.append(rowHtml);
        const defaultSalesman = $('#bill-primary-salesman').val();
        if (defaultSalesman) {
            body.find(`tr[data-row-id="${index}"] .salesman-id`).val(defaultSalesman);
        }
        body.find(`tr[data-row-id="${index}"] .barcode-input`).focus();
        return index;
    }

    function syncPrimarySalesmanToGrid(salesmanId) {
        if (!salesmanId) return;
        $('#sales-grid-body tr .salesman-id').each(function() {
            if (!$(this).val()) {
                $(this).val(salesmanId);
            }
        });
    }

    // Flow selector to prevent overwriting existing products in rows
    function triggerSearchFlow() {
        let useRowId = activeRowIndexForLookup;
        
        // If the row doesn't exist in DOM, reset it
        if (useRowId && $(`tr[data-row-id="${useRowId}"]`).length === 0) {
            useRowId = null;
        }

        if (useRowId) {
            let barcodeVal = $(`tr[data-row-id="${useRowId}"] .barcode-input`).val();
            if (barcodeVal && barcodeVal.trim() !== "") {
                addGridRow();
                useRowId = $('#sales-grid-body tr:last').attr('data-row-id') || $('#sales-grid-body tr:last').data('row-id');
            }
        } else {
            let lastRow = $('#sales-grid-body tr:last');
            let lastRowId = lastRow.attr('data-row-id') || lastRow.data('row-id');
            let lastBarcode = lastRow.find('.barcode-input').val();
            
            if (lastBarcode && lastBarcode.trim() !== "") {
                addGridRow();
                useRowId = $('#sales-grid-body tr:last').attr('data-row-id') || $('#sales-grid-body tr:last').data('row-id');
            } else {
                useRowId = lastRowId;
            }
        }
        
        // Ensure the active target row has its jQuery data cache synced
        const finalRow = $(`tr[data-row-id="${useRowId}"]`);
        if (finalRow.length > 0) {
            finalRow.data('row-id', useRowId);
        }
        
        activeRowIndexForLookup = useRowId;
        openLookupModal(useRowId);
    }

    // Click helper for Add Row button (appends row and immediately opens manual search modal)
    function clickAddRowBtn() {
        triggerSearchFlow();
    }

    // Delete row from grid
    function deleteGridRow(index) {
        const body = $('#sales-grid-body');
        if (body.children().length <= 1) {
            alert('Cannot delete the last row.');
            return;
        }

        // Determine which row to focus next
        const targetRow = $(`tr[data-row-id="${index}"]`);
        let nextFocusRow = targetRow.next();
        if (nextFocusRow.length === 0) {
            nextFocusRow = targetRow.prev();
        }

        targetRow.remove();
        
        body.children().each(function(i) {
            const num = i + 1;
            $(this).attr('data-row-id', num);
            $(this).data('row-id', num);
            $(this).find('.row-number').text(num);
            $(this).find('.barcode-input').attr('name', `items[${num}][barcode]`);
            $(this).find('.salesman-id').attr('name', `items[${num}][salesman_id]`);
            $(this).find('.color-select').attr('name', `items[${num}][color]`);
            $(this).find('.size-select').attr('name', `items[${num}][size]`);
            $(this).find('.sales-rate').attr('name', `items[${num}][rate]`);
            $(this).find('.qty').attr('name', `items[${num}][qty]`);
            $(this).find('.disc-percent').attr('name', `items[${num}][disc_percent]`);
        });

        recalculateCart();

        // Focus the next/prev row's barcode input
        if (nextFocusRow && nextFocusRow.length > 0) {
            nextFocusRow.find('.barcode-input').focus().select();
        }
    }

    // =========================================================================
    // POS AUDIO FEEDBACK (Web Audio API Synthesizer)
    // =========================================================================
    function playScanBeep(isSuccess = true) {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            if (isSuccess) {
                // High-pitch crystal clear POS success chime (1200Hz)
                osc.type = 'sine';
                osc.frequency.setValueAtTime(1200, ctx.currentTime);
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.09);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.09);
            } else {
                // Low-pitch double buzz for invalid/duplicate barcode (320Hz)
                osc.type = 'square';
                osc.frequency.setValueAtTime(320, ctx.currentTime);
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.18);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.18);
            }
        } catch (e) {
            // Audio context restricted or unsupported; ignore silently
        }
    }

    // =========================================================================
    // POS NON-BLOCKING FLOATING TOAST NOTIFICATION
    // =========================================================================
    function showPosToast(type, message) {
        let container = document.getElementById('pos-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'pos-toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:8px;pointer-events:none;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const bg = type === 'success' ? '#10b981' : (type === 'warning' ? '#f59e0b' : '#ef4444');
        const icon = type === 'success' ? 'fa-circle-check' : (type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-xmark');

        toast.style.cssText = `background:${bg};color:#fff;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 4px 14px rgba(0,0,0,0.25);display:inline-flex;align-items:center;gap:8px;animation:posFadeIn 0.2s ease-out;pointer-events:auto;`;
        toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            toast.style.transition = 'all 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    // =========================================================================
    // PROCESS SCANNED BARCODE (From Wireless Scanner OR Keyboard)
    // =========================================================================
    function processScannedBarcode(rawBarcode, explicitRowId = null) {
        const barcode = (rawBarcode || '').trim();
        if (!barcode) return;

        let targetRowId = explicitRowId;

        if (!targetRowId) {
            // 1. If currently focused element is a .barcode-input, use its row
            const focusedEl = $(document.activeElement);
            if (focusedEl.hasClass('barcode-input') && focusedEl.closest('#sales-grid-body').length > 0) {
                targetRowId = focusedEl.closest('tr').data('row-id');
            }
        }

        // 2. If target row already has an item or is null, find first empty row
        if (!targetRowId || $(`tr[data-row-id="${targetRowId}"] .item-name`).val()?.trim() !== '') {
            targetRowId = null;
            $('#sales-grid-body tr').each(function() {
                const bcVal = $(this).find('.barcode-input').val().trim();
                const itemName = $(this).find('.item-name').val().trim();
                if (!bcVal && !itemName) {
                    targetRowId = $(this).data('row-id');
                    return false;
                }
            });
        }

        // 3. If no empty rows exist in grid, create a new row
        if (!targetRowId) {
            targetRowId = addGridRow();
        }

        const targetRow = $(`tr[data-row-id="${targetRowId}"]`);
        const barcodeInput = targetRow.find('.barcode-input');
        barcodeInput.val(barcode);

        // 4. Duplicate scan check across other rows
        let duplicateFound = false;
        $('#sales-grid-body tr').each(function() {
            const rId = $(this).data('row-id');
            if (rId != targetRowId) {
                const existingBc = $(this).find('.barcode-input').val().trim();
                if (existingBc === barcode) {
                    duplicateFound = true;
                    return false;
                }
            }
        });

        if (duplicateFound) {
            playScanBeep(false);
            barcodeInput.addClass('is-invalid').val('').focus();
            setTimeout(() => barcodeInput.removeClass('is-invalid'), 1200);
            showPosToast('warning', `Duplicate Scan: Barcode ${barcode} is already in the cart.`);
            return;
        }

        // 5. Resolve barcode via AJAX
        resolveProductBarcode(barcode, targetRowId);
    }

    // Barcode input keydown handler
    function handleBarcodeKeydown(e, index) {
        if (e.key === "Enter" || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            const barcodeVal = $(e.target).val().trim();
            if (barcodeVal) {
                processScannedBarcode(barcodeVal, index);
            }
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            const prevRow = $(e.target).closest('tr').prev();
            if (prevRow.length > 0) {
                prevRow.find('.barcode-input').focus().select();
            }
        } else if (e.key === "ArrowDown") {
            e.preventDefault();
            const nextRow = $(e.target).closest('tr').next();
            if (nextRow.length > 0) {
                nextRow.find('.barcode-input').focus().select();
            }
        }
    }

    function handleQtyKeydown(e, index) {
        if (e.key === "Enter" || e.key === "ArrowDown") {
            const body = $('#sales-grid-body');
            if (index === body.children().length) {
                e.preventDefault();
                addGridRow();
            }
        }
    }

    function getExistingBarcodesInCart() {
        let barcodes = [];
        $('.barcode-input').each(function() {
            let val = $(this).val();
            if (val) {
                barcodes.push(val.trim());
            }
        });
        return barcodes;
    }

    function resolveProductBarcode(barcode, index, callback = null) {
        $.ajax({
            url: "{{ route('shop.pos.resolveBarcode', '') }}/" + encodeURIComponent(barcode),
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                playScanBeep(true);
                populateGridRow(data, index);
                if (typeof callback === 'function') {
                    callback(data);
                }
            },
            error: function(xhr) {
                playScanBeep(false);
                const rowInput = $(`tr[data-row-id="${index}"] .barcode-input`);
                rowInput.addClass('is-invalid');
                setTimeout(() => rowInput.removeClass('is-invalid'), 1200);
                rowInput.val('').focus();
                let errMsg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Unsold barcode not found.';
                showPosToast('danger', errMsg);
                if (typeof callback === 'function') {
                    callback(null);
                }
            }
        });
    }

    function populateGridRow(data, index) {
        markFirstItemBillingStarted();
        const row = $(`tr[data-row-id="${index}"]`);
        if (row.length === 0) return;

        row.find('.barcode-input').removeClass('is-invalid').val(data.barcode);
        row.find('.item-name').val(data.item_name);
        row.find('.hsn-code').val(data.hsn_code);
        row.find('.design-no').val(data.design_no);
        row.find('.sales-rate').val(parseFloat(data.rate || 0).toFixed(2));
        row.find('.qty').val(1).prop('readonly', true); // Single unique barcode per unit
        row.find('.tax-percent').val(data.tax_percentage || 0);

        let colorOptions = `<option value="">-</option>`;
        if (data.colors && Array.isArray(data.colors)) {
            data.colors.forEach(c => {
                colorOptions += `<option value="${c.name}">${c.name}</option>`;
            });
        }
        row.find('.color-select').html(colorOptions);

        let sizeOptions = `<option value="">-</option>`;
        if (data.sizes && Array.isArray(data.sizes)) {
            data.sizes.forEach(s => {
                sizeOptions += `<option value="${s.name}">${s.name}</option>`;
            });
        }
        row.find('.size-select').html(sizeOptions);

        recalculateCart();
        showPosToast('success', `Added: ${data.item_name} (${data.barcode})`);

        // Automatically prepare next row and keep focus on next empty row's barcode input
        const body = $('#sales-grid-body');
        let nextEmptyRow = null;

        row.nextAll('tr').each(function() {
            const bc = $(this).find('.barcode-input').val().trim();
            const name = $(this).find('.item-name').val().trim();
            if (!bc && !name) {
                nextEmptyRow = $(this);
                return false;
            }
        });

        if (nextEmptyRow && nextEmptyRow.length > 0) {
            nextEmptyRow.find('.barcode-input').focus().select();
        } else {
            const newIndex = addGridRow();
            body.find(`tr[data-row-id="${newIndex}"] .barcode-input`).focus().select();
        }
    }

    function handleDiscPercentChange(index) {
        const row = $(`tr[data-row-id="${index}"]`);
        const rate = parseFloat(row.find('.sales-rate').val()) || 0;
        const qty = parseFloat(row.find('.qty').val()) || 0;
        const discPercent = parseFloat(row.find('.disc-percent').val()) || 0;

        const discAmt = round((rate * qty) * (discPercent / 100), 2);
        row.find('.disc-amt').val(discAmt.toFixed(2));

        recalculateCart();
    }

    // Change discount amount manually
    function handleDiscAmtChange(index) {
        const row = $(`tr[data-row-id="${index}"]`);
        const rate = parseFloat(row.find('.sales-rate').val()) || 0;
        const qty = parseFloat(row.find('.qty').val()) || 0;
        const discAmt = parseFloat(row.find('.disc-amt').val()) || 0;
        
        const sub = rate * qty;
        if (sub > 0) {
            const discPercent = round((discAmt / sub) * 100, 1);
            row.find('.disc-percent').val(discPercent);
        } else {
            row.find('.disc-percent').val(0);
        }

        recalculateCart();
    }

    function recalculateCart() {
        let totalTaxable = 0;
        let totalDiscount = 0;
        let totalTax = 0;
        let totalGross = 0;

        $('#sales-grid-body tr').each(function() {
            const row = $(this);
            const rate = parseFloat(row.find('.sales-rate').val()) || 0;
            const qty = parseFloat(row.find('.qty').val()) || 0;
            const discAmt = parseFloat(row.find('.disc-amt').val()) || 0;
            const taxPercent = parseFloat(row.find('.tax-percent').val()) || 0;

            const lineTotal = (rate * qty) - discAmt;
            const lineTaxable = taxPercent > 0 ? round((lineTotal * 100) / (100 + taxPercent), 2) : round(lineTotal, 2);
            const lineTax = round(lineTotal - lineTaxable, 2);
            const netAmt = lineTotal;

            row.find('.net-amt').val(netAmt.toFixed(2));

            totalGross += lineTotal;
            totalTaxable += lineTaxable;
            totalDiscount += discAmt;
            totalTax += lineTax;
        });

        let exchangeCredit = 0;
        if (window._activeExchangeSession) {
            exchangeCredit = parseFloat(window._activeExchangeSession.returned_total || 0);
        }

        const netBillTotal = totalGross - exchangeCredit;
        const roundedAmount = Math.round(netBillTotal);
        const roundOff = round(roundedAmount - netBillTotal, 2);

        if (exchangeCredit > 0 && window._activeExchangeSession) {
            if ($('#summary-exchange-row').length === 0) {
                const exchangeRowHtml = `
                    <div id="summary-exchange-row" class="d-flex justify-content-between align-items-center mb-1 text-danger fw-bold" style="font-size:12px;">
                        <span>Returned Credit (<span id="summary-exchange-ref"></span>):</span>
                        <span>-${currencySymbol}<span id="summary-exchange-val">0.00</span></span>
                    </div>
                `;
                $('#summary-discount').closest('.row').after(exchangeRowHtml);
            }
            $('#summary-exchange-ref').text('#' + window._activeExchangeSession.original_invoice_no);
            $('#summary-exchange-val').text(exchangeCredit.toFixed(2));
            $('#summary-exchange-row').show();
        } else {
            $('#summary-exchange-row').hide().remove();
        }

        $('#summary-taxable').val(totalTaxable.toFixed(2));
        $('#summary-discount').val(totalDiscount.toFixed(2));
        $('#summary-tax').val(totalTax.toFixed(2));
        $('#summary-roundoff').val(roundOff.toFixed(2));

        const splitTax = round(totalTax / 2, 2);
        $('#tax-cgst').val(splitTax.toFixed(2));
        $('#tax-sgst').val(round(totalTax - splitTax, 2).toFixed(2));
        $('#tax-igst').val((0.00).toFixed(2));

        $('#big-bill-amount').text(currencySymbol + (roundedAmount >= 0 ? roundedAmount.toFixed(2) : (0.00).toFixed(2)));

        calculateDueBalance();
    }

    function togglePaymentInputs() {
        const mode = $('#payment-method').val();
        const billAmt = parseFloat($('#big-bill-amount').text().replace(currencySymbol, '')) || 0;

        if (mode === 'cash') {
            $('#cash-received').prop('readonly', false).val(billAmt.toFixed(2));
            $('#card-received').prop('readonly', true).val((0.00).toFixed(2));
            $('#cash-due-label').text("Change Due");
        } else if (mode === 'card') {
            $('#cash-received').prop('readonly', true).val((0.00).toFixed(2));
            $('#card-received').prop('readonly', true).val(billAmt.toFixed(2));
            $('#cash-due-label').text("Balance Amount");
        } else if (mode === 'split') {
            const half = billAmt / 2;
            $('#cash-received').prop('readonly', false).val(half.toFixed(2));
            $('#card-received').prop('readonly', false).val(half.toFixed(2));
            $('#cash-due-label').text("Split Remaining");
        }
        calculateDueBalance();
    }

    function calculateDueBalance() {
        const mode = $('#payment-method').val();
        let exchangeCredit = (window._activeExchangeSession ? parseFloat(window._activeExchangeSession.returned_total || 0) : 0);
        let grossBill = 0;
        $('#sales-grid-body tr').each(function() {
            const row = $(this);
            const qty = parseInt(row.find('.qty').val()) || 0;
            const rate = parseFloat(row.find('.sales-rate').val()) || 0;
            const discPercent = parseFloat(row.find('.disc-percent').val()) || 0;
            const discAmt = round(rate * qty * (discPercent / 100), 2);
            grossBill += (rate * qty) - discAmt;
        });

        const netBillAmt = Math.round(grossBill - exchangeCredit);

        if (netBillAmt < 0) {
            $('#cash-due-label').text("Refund Due to Customer");
            $('#big-change-due').text(currencySymbol + Math.abs(netBillAmt).toFixed(2));
            return;
        }

        const billAmt = parseFloat($('#big-bill-amount').text().replace(currencySymbol, '')) || 0;

        if (mode === 'cash') {
            const cashRec = parseFloat($('#cash-received').val()) || 0;
            const due = Math.max(0, cashRec - billAmt);
            $('#big-change-due').text(currencySymbol + due.toFixed(2));
            $('#cash-due-label').text("Change Due");
        } else if (mode === 'card') {
            $('#big-change-due').text(currencySymbol + (0.00).toFixed(2));
            $('#cash-due-label').text("Balance Amount");
        } else if (mode === 'split') {
            const cashRec = parseFloat($('#cash-received').val()) || 0;
            const cardRec = parseFloat($('#card-received').val()) || 0;
            const totalRec = cashRec + cardRec;
            const diff = billAmt - totalRec;

            if (diff > 0.005) {
                $('#cash-due-label').text("Remaining to Pay");
                $('#big-change-due').text(currencySymbol + diff.toFixed(2));
            } else if (diff < -0.005) {
                $('#cash-due-label').text("Change Due (Cash)");
                $('#big-change-due').text(currencySymbol + Math.abs(diff).toFixed(2));
            } else {
                $('#cash-due-label').text("Paid Status");
                $('#big-change-due').text("Fully Paid");
            }
        }
    }

    function clearCustomerDetailsForm() {
        $('#customer-phone').val('');
        $('#customer-card').val('');
        $('#customer-name').val('');
        $('#customer-category').val('');
        $('#customer-subcategory').val('');
        $('#customer-address').val('');
        $('#customer-city').val('');
        $('#customer-area').val('');
        $('#customer-pincode').val('');
        $('#customer-dob').val('');
        $('#customer-anniversary').val('');
        $('#customer-source').val('');
        $('#customer-subsource').val('');
        $('#customer-ref-by').val('');
        $('#customer-ref-mobile').val('');
        $('#customer-total-sales-text').text('₹0.00');
        $('#history-total-sales').text('₹0.00');
        $('#history-recent-order').text('N/A');
        $('#history-ref-by').text('Direct');
        $('#customer-gstin').val('');
        $('#customer-pan').val('');
        $('#customer-email').val('');
        $('#bill-primary-salesman').val('');
    }

    function focusCustomerName() {
        $('#customer-name').focus().select();
    }

    function promptAddOption(selectId, title) {
        const val = prompt('Enter new ' + title + ':');
        if (val && val.trim() !== '') {
            const trimmed = val.trim();
            const optionKey = trimmed.toLowerCase().replace(/\s+/g, '_');
            const $select = $('#' + selectId);
            $select.append(new Option(trimmed, optionKey, true, true));
            $select.trigger('change');
        }
    }

    function lookupCustomerByPhone(phone) {
        if (phone.length >= 10) {
            $.ajax({
                url: "{{ route('shop.pos.searchCustomer', '') }}/" + encodeURIComponent(phone),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#customer-name').val(data.name || '');
                    $('#customer-address').val(data.address || '');
                    $('#customer-email').val(data.email || '');
                    if (data.card_no) $('#customer-card').val(data.card_no);
                    if (data.city) $('#customer-city').val(data.city);
                    if (data.pincode) $('#customer-pincode').val(data.pincode);
                    if (data.dob) $('#customer-dob').val(data.dob);
                    if (data.anniversary) $('#customer-anniversary').val(data.anniversary);
                    if (data.category) $('#customer-category').val(data.category);
                    if (data.area) $('#customer-area').val(data.area);
                    if (data.source) $('#customer-source').val(data.source);
                    if (data.ref_by) $('#customer-ref-by').val(data.ref_by);
                    if (data.ref_mobile) $('#customer-ref-mobile').val(data.ref_mobile);
                    
                    const totalSales = data.total_sales ? currencySymbol + parseFloat(data.total_sales).toFixed(2) : currencySymbol + '1540.00';
                    $('#customer-total-sales-text').text(totalSales);
                    $('#history-total-sales').text(totalSales);
                    $('#history-recent-order').text(data.recent_order || '#000021');
                    if (data.ref_by) $('#history-ref-by').text(data.ref_by);
                },
                error: function() {
                    $('#customer-name').val('');
                }
            });
        }
    }

    function openLookupModal(rowIndex) {
        activeRowIndexForLookup = rowIndex;
        $('#lookup-query-input').val('');
        $('#lookup-results-body').html(`<tr><td colspan="5" class="text-center text-muted py-3">{{ __('Start typing to search products...') }}</td></tr>`);
        $('#productLookupModal').modal('show');
        setTimeout(() => $('#lookup-query-input').focus(), 500);
    }

    // Track the last query that triggered a fetch so we know when the search
    // text genuinely changed (vs. just an arrow-key press).
    let _lastSearchQuery = null;

    function queryPOSProducts(query) {
        // Strip surrounding whitespace for comparison
        const trimmed = (query || '').trim();

        // Require at least 2 characters to search
        if (trimmed.length < 2) {
            _lastSearchQuery = null;
            $('#lookup-results-body').html(
                `<tr><td colspan="5" class="text-center text-muted py-3">{{ __('Start typing to search products...') }}</td></tr>`
            );
            return;
        }

        // Skip the AJAX call if the query hasn't actually changed
        // (e.g. user pressed ArrowDown — the 'input' event won't fire in that
        //  case but a stray call from old code might still reach here)
        if (trimmed === _lastSearchQuery) return;
        _lastSearchQuery = trimmed;

        let excludeString = getExistingBarcodesInCart().join(',');

        $.ajax({
            url: "{{ route('shop.pos.searchProducts') }}?query=" + encodeURIComponent(trimmed) + "&exclude_barcodes=" + encodeURIComponent(excludeString),
            type: 'GET',
            dataType: 'json',
            // Store the query we sent so we can discard stale responses
            queryValue: trimmed,
            success: function(data) {
                // Discard stale responses (fast typers may fire multiple requests)
                if (this.queryValue !== _lastSearchQuery) return;

                let html = '';
                if (data.length === 0) {
                    html = `<tr><td colspan="5" class="text-center py-2 text-danger">{{ __('No matches found') }}</td></tr>`;
                } else {
                    data.forEach((item, index) => {
                        // Auto-highlight ONLY the very first row when fresh
                        // results arrive. Once the user navigates with arrow
                        // keys the class is managed by the keydown handler
                        // and is NOT reset here.
                        let activeClass = (index === 0) ? 'selected-active' : '';
                        html += `
                            <tr class="lookup-item-row ${activeClass}"
                                data-barcode="${item.barcode}"
                                onclick="selectLookupProduct('${item.barcode}')">
                                <td class="fw-bold">${item.barcode}</td>
                                <td>${item.name}</td>
                                <td>${item.design_no}</td>
                                <td class="text-center font-monospace fw-bold text-secondary">${item.stock}</td>
                                <td class="text-end fw-bold">${currencySymbol}${item.rate.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                }
                // Replace results — because the SEARCH TEXT changed the old
                // selection is stale so starting from row 1 is correct.
                $('#lookup-results-body').html(html);
            }
        });
    }

    // Select product from modal
    function selectLookupProduct(barcode) {
        $('#productLookupModal').modal('hide');
        if (activeRowIndexForLookup) {
            resolveProductBarcode(barcode, activeRowIndexForLookup);
        }
    }

    // Reset grid
    async function resetGridBill() {
        if (await posConfirm("Reset current bill?")) {
            $('#sales-grid-body').empty();
            addGridRow();
            $('#customer-phone').val('');
            $('#customer-name').val('');
            $('#customer-address').val('');
            $('#customer-city').val('');
            $('#customer-pincode').val('');
            $('#customer-email').val('');
            $('#bill-primary-salesman').val('');
            $('#cash-received').val('0.00');
            recalculateCart();
            resetBillTimer();
            setTimeout(() => {
                $('#sales-grid-body tr:first .barcode-input').focus().select();
            }, 100);
        } else {
            setTimeout(() => {
                $('#sales-grid-body tr:last .barcode-input').focus().select();
            }, 100);
        }
    }

    // Clear cart grid items
    async function clearCartGrid() {
        if (await posConfirm("Are you sure you want to clear all items from the sales grid?")) {
            window._activeExchangeSession = null;
            $('#exchange-active-banner').removeClass('d-flex').addClass('d-none').hide();
            $('#summary-exchange-row').hide().remove();
            $('#sales-grid-body').empty();
            addGridRow();
            recalculateCart();
            resetBillTimer();
            setTimeout(() => {
                $('#sales-grid-body tr:first .barcode-input').focus().select();
            }, 100);
        } else {
            setTimeout(() => {
                $('#sales-grid-body tr:last .barcode-input').focus().select();
            }, 100);
        }
    }

    // Submit POS grid checkout order
    function submitGridOrder() {
        const items = [];
        let valid = true;

        $('#sales-grid-body tr').each(function() {
            const row = $(this);
            const barcode = row.find('.barcode-input').val().trim();
            const qty = parseInt(row.find('.qty').val()) || 0;
            const rate = parseFloat(row.find('.sales-rate').val()) || 0;
            const discPercent = parseFloat(row.find('.disc-percent').val()) || 0;
            const color = row.find('.color-select').val();
            const size = row.find('.size-select').val();
            const salesman_id = row.find('.salesman-id').val();

            if (!barcode) return;

            if (qty <= 0) {
                alert(`Row ${row.find('.row-number').text()}: Quantity must be > 0.`);
                valid = false;
                return false;
            }

            items.push({
                barcode: barcode,
                qty: qty,
                rate: rate,
                disc_percent: discPercent,
                color: color,
                size: size,
                salesman_id: salesman_id
            });
        });

        if (!valid) return;

        if (items.length === 0) {
            alert('Please add at least one item.');
            return;
        }

        const mode = $('#payment-method').val();
        const billAmt = parseFloat($('#big-bill-amount').text().replace(currencySymbol, '')) || 0;
        let splitPayments = null;

        if (mode === 'split') {
            const cashRec = parseFloat($('#cash-received').val()) || 0;
            const cardRec = parseFloat($('#card-received').val()) || 0;
            const totalRec = cashRec + cardRec;
            
            if (Math.abs(billAmt - totalRec) > 0.01) {
                alert(`Split amounts (Cash: ${currencySymbol}${cashRec.toFixed(2)} + Card: ${currencySymbol}${cardRec.toFixed(2)}) must sum up exactly to the total bill amount: ${currencySymbol}${billAmt.toFixed(2)}`);
                return;
            }

            splitPayments = [
                { method: 'cash', amount: cashRec },
                { method: 'online', amount: cardRec }
            ];
        }

        const payload = {
            _token: "{{ csrf_token() }}",
            customer_phone: $('#customer-phone').val().trim(),
            customer_name: $('#customer-name').val().trim(),
            customer_card: $('#customer-card').val().trim(),
            customer_address: $('#customer-address').val().trim(),
            customer_city: $('#customer-city').val().trim(),
            customer_pincode: $('#customer-pincode').val().trim(),
            customer_email: $('#customer-email').val().trim(),
            customer_dob: $('#customer-dob').val(),
            customer_anniversary: $('#customer-anniversary').val(),
            customer_category: $('#customer-category').val(),
            customer_subcategory: $('#customer-subcategory').val(),
            customer_area: $('#customer-area').val(),
            customer_source: $('#customer-source').val(),
            customer_subsource: $('#customer-subsource').val(),
            customer_ref_by: $('#customer-ref-by').val().trim(),
            customer_ref_mobile: $('#customer-ref-mobile').val().trim(),
            salesman_id: $('#bill-primary-salesman').val() || null,
            cashier_id: $('#payment-cashier-id').val() || null,
            billing_duration_seconds: getBillDurationSeconds(),
            billing_started_at: getBillStartedAt(),
            payment_method: mode,
            counter_id: $('#selected-counter-id').val(),
            split_payments: splitPayments,
            note: "Cashier checkout grid invoice",
            paid_amount: mode === 'cash' ? (parseFloat($('#cash-received').val()) || billAmt) : billAmt,
            items: items
        };

        if (window._activeExchangeSession) {
            payload.exchange_return_order_id = window._activeExchangeSession.order_id;
            payload.exchange_return_items = window._activeExchangeSession.returned_items;
            payload.exchange_refund_amount = window._activeExchangeSession.returned_total;
            payload.exchange_original_invoice_no = window._activeExchangeSession.original_invoice_no;
        }

        $.ajax({
            url: "{{ route('shop.pos.checkoutGrid') }}",
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function(res) {
                // Clear active exchange session
                window._activeExchangeSession = null;
                $('#exchange-active-banner').removeClass('d-flex').addClass('d-none').hide();
                $('#summary-exchange-row').hide().remove();

                // Clear active cart fields immediately in background so cashier is ready for next customer
                $('#sales-grid-body').empty();
                addGridRow();
                clearCustomerDetailsForm();
                $('#cash-received').val('0.00');
                $('#card-received').val('0.00').prop('readonly', true);
                $('#payment-method').val('cash');
                $('#cash-due-label').text("Change Due");
                recalculateCart();
                resetBillTimer();

                // Open thermal print modal overlay
                if (res.order && res.order.id) {
                    openPOSPrintPreview(res.order.id, true);
                } else {
                    alert("Order checked out successfully!");
                }
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Checkout failed.';
                alert(msg);
            }
        });
    }

    function openPOSPrintPreview(orderId, autoPrint = false) {
        // Load thermal/A4 layout HTML
        $.ajax({
            url: `/shop/pos/${orderId}/thermal-preview`,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $('#pos-invoice-print-body').html(html);

                const isA4 = html.includes('invoice-page') || html.includes('TAX INVOICE');
                const modalCard = document.getElementById('pos-invoice-modal-card');
                const modalTitle = document.getElementById('pos-invoice-modal-title');
                const printBody = document.getElementById('pos-invoice-print-body');

                if (isA4) {
                    if (modalCard) {
                        modalCard.style.maxWidth = '900px';
                        modalCard.style.height = '90vh';
                        modalCard.style.maxHeight = '90vh';
                    }
                    if (modalTitle) {
                        modalTitle.textContent = 'A4 Invoice Preview';
                    }
                    if (printBody) {
                        printBody.style.width = '100%';
                        printBody.style.maxWidth = '850px';
                        printBody.style.padding = '0';
                    }
                } else {
                    if (modalCard) {
                        modalCard.style.maxWidth = '400px';
                        modalCard.style.height = 'auto';
                        modalCard.style.maxHeight = '85%';
                    }
                    if (modalTitle) {
                        modalTitle.textContent = 'Thermal Invoice Preview';
                    }
                    if (printBody) {
                        printBody.style.width = '80mm';
                        printBody.style.maxWidth = '100%';
                        printBody.style.padding = '10px';
                    }
                }

                document.getElementById('pos-invoice-print-overlay').style.display = 'flex';
                // Trigger auto-focus and print preview automatically ONLY when autoPrint is true
                if (autoPrint) {
                    setTimeout(printPOSInvoice, 300);
                }
            },
            error: function() {
                alert("Failed to load invoice layout for preview.");
            }
        });
    }

    function openPOSReturnPrintPreview(returnNo, autoPrint = false) {
        $.ajax({
            url: `/shop/pos/return/${returnNo}/print`,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $('#pos-invoice-print-body').html(html);

                const isA4 = html.includes('invoice-page') || html.includes('TAX INVOICE');
                const modalCard = document.getElementById('pos-invoice-modal-card');
                const modalTitle = document.getElementById('pos-invoice-modal-title');
                const printBody = document.getElementById('pos-invoice-print-body');

                if (isA4) {
                    if (modalCard) {
                        modalCard.style.maxWidth = '900px';
                        modalCard.style.height = '90vh';
                        modalCard.style.maxHeight = '90vh';
                    }
                    if (modalTitle) {
                        modalTitle.textContent = 'A4 Invoice Preview';
                    }
                    if (printBody) {
                        printBody.style.width = '100%';
                        printBody.style.maxWidth = '850px';
                        printBody.style.padding = '0';
                    }
                } else {
                    if (modalCard) {
                        modalCard.style.maxWidth = '400px';
                        modalCard.style.height = 'auto';
                        modalCard.style.maxHeight = '85%';
                    }
                    if (modalTitle) {
                        modalTitle.textContent = 'Thermal Invoice Preview';
                    }
                    if (printBody) {
                        printBody.style.width = '80mm';
                        printBody.style.maxWidth = '100%';
                        printBody.style.padding = '10px';
                    }
                }

                document.getElementById('pos-invoice-print-overlay').style.display = 'flex';
                if (autoPrint) {
                    setTimeout(printPOSInvoice, 300);
                }
            },
            error: function() {
                alert("Failed to load return receipt layout for preview.");
            }
        });
    }

    function printPOSInvoice() {
        const printBody = $('#pos-invoice-print-body').html();
        if (!printBody) return;

        const iframe = document.getElementById('pos-print-iframe');
        const doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        // Include layout style dynamically
        doc.write(`
            <html>
                <head>
                    <title>POS Thermal Invoice</title>
                </head>
                <body onload="window.print();">
                    ${printBody}
                </body>
            </html>
        `);
        doc.close();
        
        setTimeout(() => {
            iframe.contentWindow.focus();
        }, 100);
    }

    function closePOSPrintPreview() {
        document.getElementById('pos-invoice-print-overlay').style.display = 'none';
        $('#pos-invoice-print-body').html('');
        // Focus the new row barcode field
        $('#sales-grid-body tr:last .barcode-input').focus();
    }

    let currentHistoryPage = 1;
    let historyLimitPerPage = 10;

    function openSalesHistoryModal() {
        document.getElementById('pos-sales-history-overlay').style.display = 'flex';
        loadHistoryRegister();
    }

    function closeSalesHistoryModal() {
        document.getElementById('pos-sales-history-overlay').style.display = 'none';
        $('#history-table-body').empty();
        $('#sales-grid-body tr:last .barcode-input').focus();
    }

    function getSelectedCounterId() {
        let cid = $('#selected-counter-id').val();
        if (!cid) {
            try {
                const stored = localStorage.getItem('pos_selected_counter');
                if (stored) {
                    const c = JSON.parse(stored);
                    cid = c.id;
                }
            } catch(e) {}
        }
        return cid || '';
    }

    function loadHistoryStats() {
        const counterId = getSelectedCounterId();
        $.ajax({
            url: "{{ route('shop.pos.history.stats') }}",
            type: 'GET',
            data: { counter_id: counterId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#stat-sales-today').text(currencySymbol + parseFloat(res.stats.sales_today).toFixed(2));
                    $('#stat-bills-today').text(res.stats.bills_today);
                    $('#stat-holds-active').text(res.stats.holds_active);
                    $('#stat-cancelled-bills').text(res.stats.cancelled_bills);
                    $('#stat-average-bill').text(currencySymbol + parseFloat(res.stats.average_bill).toFixed(2));
                    $('#stat-returns-today').text(res.stats.returns_today);
                }
            }
        });
    }

    function loadHistoryRegister() {
        const payload = {
            page: currentHistoryPage,
            limit: historyLimitPerPage,
            invoice_no: $('#filter-search').val().trim(),
            customer: $('#filter-search').val().trim(),
            payment_mode: $('#filter-payment-mode').val(),
            status: $('#filter-status').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
            counter_id: getSelectedCounterId()
        };

        $.ajax({
            url: "{{ route('shop.pos.history.index') }}",
            type: 'GET',
            data: payload,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    renderHistoryTable(res.data);
                    renderPaginationControls(res.pagination);
                }
            }
        });
    }

    function renderHistoryTable(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="11" class="text-center text-muted py-4">No invoice records matching filters.</td></tr>';
        } else {
            data.forEach(function(o) {
                const checkStatus = (o.status || '').toLowerCase();
                let statusColor = 'badge bg-success';
                let statusText = 'Completed';

                if (checkStatus === 'returned') {
                    statusColor = 'badge bg-danger';
                    statusText = 'Returned';
                } else if (checkStatus === 'partial returned') {
                    statusColor = 'badge bg-warning text-dark';
                    statusText = 'Partial Returned';
                } else if (checkStatus === 'cancelled') {
                    statusColor = 'badge bg-secondary';
                    statusText = 'Cancelled';
                }

                const canReturn = (checkStatus === 'delivered' || checkStatus === 'partial returned');
                const canVoid = (checkStatus === 'delivered');

                html += `
                    <tr>
                        <td class="fw-bold text-primary">${o.invoice_no}</td>
                        <td>
                            <div>${o.customer_name}</div>
                            <small class="text-muted">${o.customer_phone}</small>
                        </td>
                        <td>${o.date}</td>
                        <td class="text-center">${o.items_count}</td>
                        <td class="text-right">${currencySymbol}${o.gross_amount.toFixed(2)}</td>
                        <td class="text-right text-warning">${currencySymbol}${o.discount.toFixed(2)}</td>
                        <td class="text-right text-danger">${currencySymbol}${o.tax_amount.toFixed(2)}</td>
                        <td class="text-right fw-bold text-success">${currencySymbol}${o.net_amount.toFixed(2)}</td>
                        <td class="text-right fw-bold text-primary">
                            ${currencySymbol}${o.paid_amount.toFixed(2)}
                            ${(o.paid_amount - o.net_amount) < -0.005 ? ` <span class="text-danger" style="font-size: 11px; font-weight: normal;">(${(o.paid_amount - o.net_amount).toFixed(2)})</span>` : ''}
                        </td>
                        <td>${o.payment_method}</td>
                        <td><span class="${statusColor}">${statusText}</span></td>
                        <td class="text-end">
                            <button class="btn btn-xs py-0.5 px-2 fw-bold text-white" style="background-color: #1e293b !important; color: #ffffff !important; border: 1px solid #334155 !important; font-size:11px;" onmouseover="this.style.backgroundColor='#0f172a'; this.style.color='#3b82f6';" onmouseout="this.style.backgroundColor='#1e293b'; this.style.color='#ffffff';" onclick="openPOSPrintPreview(${o.id}, false)">View</button>
                            <button class="btn btn-primary btn-xs py-0.5 px-2 fw-bold" onclick="duplicateInvoiceInline(${o.id})" style="font-size:11px;">Duplicate</button>
                            ${canReturn ? `
                                <button class="btn btn-warning btn-xs py-0.5 px-2 fw-bold text-dark" onclick="triggerReturnWizardInline('${o.invoice_no}')" style="font-size:11px;">Return</button>
                            ` : ''}
                            ${canVoid ? `
                                <button class="btn btn-danger btn-xs py-0.5 px-2 fw-bold" onclick="voidInvoiceInline(${o.id})" style="font-size:11px;">Void</button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });
        }
        $('#history-table-body').html(html);
    }

    function renderPaginationControls(p) {
        $('#pagination-info-total').text(p.total);
        const start = p.total === 0 ? 0 : (p.current_page - 1) * p.per_page + 1;
        const end = Math.min(p.current_page * p.per_page, p.total);
        $('#pagination-info-start').text(start);
        $('#pagination-info-end').text(end);

        let html = '';
        const prevDisabled = p.current_page === 1 ? 'disabled' : '';
        html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" onclick="changeHistoryPage(${p.current_page - 1}); return false;">Prev</a></li>`;

        for (let i = 1; i <= p.last_page; i++) {
            const active = p.current_page === i ? 'active' : '';
            html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="changeHistoryPage(${i}); return false;">${i}</a></li>`;
        }

        const nextDisabled = p.current_page === p.last_page ? 'disabled' : '';
        html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" onclick="changeHistoryPage(${p.current_page + 1}); return false;">Next</a></li>`;

        $('#pagination-controls-list').html(html);
    }

    function changeHistoryPage(page) {
        currentHistoryPage = page;
        loadHistoryRegister();
    }

    function applyHistoryFilters() {
        currentHistoryPage = 1;
        loadHistoryRegister();
    }

    function resetHistoryFilters() {
        $('#filter-search').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        $('#filter-payment-mode').val('');
        $('#filter-status').val('');
        currentHistoryPage = 1;
        loadHistoryRegister();
    }

    function duplicateInvoiceInline(orderId) {
        if (!confirm('Are you sure you want to load this bill details back into active POS? This will overwrite the current cart.')) return;
        $.ajax({
            url: `/shop/pos/history/${orderId}/duplicate`,
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" },
            dataType: 'json',
            success: function(res) {
                const order = res.order;
                closeSalesHistoryModal();
                restoreDuplicateOrderToPOS(order);
            }
        });
    }

    function voidInvoiceInline(orderId) {
        if (!confirm('CAUTION: Are you sure you want to void this invoice? This will cancel the order, revert stock quantities, mark barcodes unsold, and post journal reverse entries.')) return;
        $.ajax({
            url: `/shop/pos/history/${orderId}/void`,
            type: 'POST',
            data: { _token: "{{ csrf_token() }}" },
            dataType: 'json',
            success: function(res) {
                alert(res.message);
                loadHistoryStats();
                loadHistoryRegister();
            },
            error: function(err) {
                alert(err.responseJSON?.error || 'Void failed.');
            }
        });
    }

    function triggerReturnWizardInline(invoiceNo) {
        showReturnModal();
        $('#return-invoice-search').val(invoiceNo);
        lookupInvoiceForReturn();
    }

    function exportHistoryCSV() {
        const query = $.param({
            invoice_no: $('#filter-search').val().trim(),
            customer: $('#filter-search').val().trim(),
            payment_mode: $('#filter-payment-mode').val(),
            status: $('#filter-status').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
            counter_id: $('#selected-counter-id').val()
        });
        window.location.href = "{{ route('shop.pos.history.export') }}?" + query;
    }

    // Standard round helper
    function round(num, decimals) {
        const factor = Math.pow(10, decimals);
        return Math.round(num * factor) / factor;
    }

    // ================================================================
    // COUNTER POSITION SELECTION LOGIC
    // ================================================================

    let _selectedCounter = null; // { id, code, name, short, floor, prefix }

    // ── Auto-show overlay on page load ─────────────────────────────────
    $(document).ready(function () {
        const storedCounterStr = localStorage.getItem('pos_selected_counter');
        if (storedCounterStr) {
            try {
                const c = JSON.parse(storedCounterStr);
                _applyCounterToUI(c);
                document.getElementById('pos-counter-overlay').style.display = 'none';
            } catch (e) {
                // If parsing fails, fall back to manual selection
                localStorage.removeItem('pos_selected_counter');
                _selectedCounter = null;
                document.getElementById('pos-counter-overlay').style.display = 'flex';
            }
        } else {
            _selectedCounter = null;
            
            // Ensure confirm button is disabled and all tiles are deselected
            const btn = document.getElementById('counter-confirm-btn');
            if (btn) btn.disabled = true;
            
            document.querySelectorAll('.counter-tile').forEach(function (t) {
                t.classList.remove('selected');
            });
            
            document.getElementById('pos-counter-overlay').style.display = 'flex';
        }

        // Check for duplicate order trigger
        const duplicate = localStorage.getItem('pos_duplicate_order');
        if (duplicate) {
            try {
                const orderObj = JSON.parse(duplicate);
                localStorage.removeItem('pos_duplicate_order');
                setTimeout(() => restoreDuplicateOrderToPOS(orderObj), 500);
            } catch (e) {}
        }

        // Check for return order trigger
        const returnInv = localStorage.getItem('pos_trigger_return_invoice');
        if (returnInv) {
            localStorage.removeItem('pos_trigger_return_invoice');
            setTimeout(() => {
                showReturnModal();
                $('#return-invoice-search').val(returnInv);
                lookupInvoiceForReturn();
            }, 500);
        }
    });

    function restoreDuplicateOrderToPOS(order) {
        // Load Customer details
        $('#customer-phone').val(order.customer?.user?.phone || '');
        $('#customer-name').val(order.customer?.user?.name || 'Walk-in Customer');
        $('#customer-email').val(order.customer?.user?.email || '');
        if (order.customer_id) {
            $('#customer-id').val(order.customer_id);
        }

        // Clear existing grid and restore items
        $('#sales-grid-body').empty();
        
        const itemsToLoad = order.products;
        let loadedCount = 0;

        if (itemsToLoad && itemsToLoad.length > 0) {
            itemsToLoad.forEach(function(p) {
                const newRowId = addGridRow();
                const tr = $(`#sales-grid-body tr[data-row-id="${newRowId}"]`);

                // Associate product id to table row
                tr.data('product-id', p.id);

                // Populate inputs
                const barcode = p.pivot.barcode_number || (p.barcodes && p.barcodes[0] ? p.barcodes[0].barcode_number : '');
                tr.find('.barcode-input').val(barcode);
                tr.find('.qty').val(p.pivot.quantity);
                tr.find('.sales-rate').val(p.pivot.mrp || p.pivot.price);
                tr.find('.disc-percent').val(p.pivot.discount);

                // Fetch product details
                resolveProductBarcode(barcode, newRowId, function() {
                    // Apply custom values
                    tr.find('.qty').val(p.pivot.quantity);
                    tr.find('.sales-rate').val(p.pivot.mrp || p.pivot.price);
                    tr.find('.disc-percent').val(p.pivot.discount);
                    if (p.pivot.color) tr.find('.color-select').val(p.pivot.color);
                    if (p.pivot.size) tr.find('.size-select').val(p.pivot.size);
                    if (p.pivot.salesman_id) tr.find('.salesman-select').val(p.pivot.salesman_id);
                    
                    loadedCount++;
                    if (loadedCount === itemsToLoad.length) {
                        recalculateCart();
                    }
                });
            });
        } else {
            addGridRow();
            recalculateCart();
        }
    }

    // ── Tile click handler ─────────────────────────────────────────────
    function selectCounterTile(el) {
        // Deselect all
        document.querySelectorAll('.counter-tile').forEach(function (t) {
            t.classList.remove('selected');
        });
        el.classList.add('selected');

        _selectedCounter = {
            id:     el.dataset.id,
            code:   el.dataset.code,
            name:   el.dataset.name,
            short:  el.dataset.short,
            floor:  el.dataset.floor,
            prefix: el.dataset.prefix,
        };

        const btn = document.getElementById('counter-confirm-btn');
        if (btn) btn.disabled = false;
    }

    // ── Confirm button click ───────────────────────────────────────────
    function confirmCounterSelection() {
        if (!_selectedCounter) return;
        localStorage.setItem('pos_selected_counter', JSON.stringify(_selectedCounter));
        _applyCounterToUI(_selectedCounter);
        document.getElementById('pos-counter-overlay').style.display = 'none';
        setTimeout(() => {
            $('#sales-grid-body tr:first .barcode-input').focus().select();
        }, 150);
    }

    // ── Apply counter data to the POS header + bill no prefix ─────────
    function _applyCounterToUI(c) {
        _selectedCounter = c;

        // Header badge
        const badge = document.getElementById('counter-header-display');
        if (badge) {
            badge.style.removeProperty('display');
            badge.style.display = 'inline-flex';
        }
        const codeEl = document.getElementById('counter-header-code');
        if (codeEl) codeEl.textContent = c.code;

        const nameEl = document.getElementById('counter-header-name');
        if (nameEl) nameEl.textContent = c.name;

        // Hidden counter_id field for form submission
        const cidInput = document.getElementById('selected-counter-id');
        if (cidInput) cidInput.value = c.id;

        // Build bill number with counter prefix
        const billValue = c.prefix ? c.prefix + '-00001' : '00001';

        // Update hidden input (used in form payloads)
        const billNoEl = document.getElementById('bill-no');
        if (billNoEl) billNoEl.value = billValue;

        // Update visible display span (yellow badge pill)
        const billDisplayEl = document.getElementById('bill-no-display');
        if (billDisplayEl) billDisplayEl.textContent = billValue;

        // Check active shift session for this counter
        checkShiftStatus(c.id);
    }

    // ── Open counter selector again (Change Counter) ───────────────────
    function openCounterSelector() {
        // Highlight the currently selected tile if any
        if (_selectedCounter) {
            document.querySelectorAll('.counter-tile').forEach(function(t) {
                t.classList.remove('selected');
            });
            const activeTile = document.getElementById('counter-tile-' + _selectedCounter.id);
            if (activeTile) {
                activeTile.classList.add('selected');
                const btn = document.getElementById('counter-confirm-btn');
                if (btn) btn.disabled = false;
            }
        }
        document.getElementById('pos-counter-overlay').style.display = 'flex';
    }

    // ================================================================
    // SUSPEND / HOLD & RECALL BILLS LOGIC
    // ================================================================

    $(document).ready(function() {
        loadHoldsCount();
    });

    function loadHoldsCount() {
        $.ajax({
            url: "{{ route('shop.pos.getHolds') }}",
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#holds-count-badge').text(data.length);
            }
        });
    }

    function holdCurrentBill() {
        // Collect grid items
        const items = [];
        let valid = true;
        let totalAmt = 0;

        $('#sales-grid-body tr').each(function() {
            const rowId = $(this).data('row-id');
            const barcode = $(this).find('.barcode-input').val() ? $(this).find('.barcode-input').val().trim() : '';
            const qty = parseFloat($(this).find('.qty').val()) || 0;
            const rate = parseFloat($(this).find('.sales-rate').val()) || 0;
            const discPercent = parseFloat($(this).find('.disc-percent').val()) || 0;
            const color = $(this).find('.color-select').val();
            const size = $(this).find('.size-select').val();
            const salesmanId = $(this).find('.salesman-select').val();

            if (barcode) {
                if (qty <= 0) {
                    alert('Please enter quantity for barcode: ' + barcode);
                    valid = false;
                    return false;
                }
                const discAmt = rate * qty * (discPercent / 100);
                totalAmt += (rate * qty) - discAmt;

                items.push({
                    barcode: barcode,
                    qty: qty,
                    rate: rate,
                    disc_percent: discPercent,
                    color: color,
                    size: size,
                    salesman_id: salesmanId
                });
            }
        });

        if (!valid) return;

        if (items.length === 0) {
            alert('Cannot hold an empty bill.');
            return;
        }

        const label = prompt("Enter a label/note to identify this suspended bill (Optional):", "Hold " + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
        if (label === null) return; // user cancelled prompt

        const payload = {
            _token: "{{ csrf_token() }}",
            bill_label: label.trim(),
            items: items,
            customer_phone: $('#customer-phone').val().trim(),
            customer_name: $('#customer-name').val().trim(),
            customer_email: $('#customer-email').val().trim(),
            subtotal: totalAmt,
            counter_id: $('#selected-counter-id').val(),
        };

        $.ajax({
            url: "{{ route('shop.pos.hold') }}",
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function(res) {
                alert(res.message);
                loadHoldsCount();
                resetGridBill();
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Failed to hold bill.';
                alert(msg);
            }
        });
    }

    function showRecallModal() {
        $.ajax({
            url: "{{ route('shop.pos.getHolds') }}",
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let html = '';
                if (data.length === 0) {
                    html = '<div class="text-center py-4 text-muted"><i class="fa-solid fa-folder-open fa-2x mb-2 text-secondary"></i><div>No suspended bills on hold.</div></div>';
                } else {
                    html = '<table class="table table-hover align-middle table-sm" style="font-size:13px; color:#1a2035;">';
                    html += '<thead class="table-light"><tr><th>Label</th><th>Customer</th><th>Amount</th><th>Suspended At</th><th class="text-end">Action</th></tr></thead><tbody>';
                    data.forEach(function(item) {
                        const dateStr = new Date(item.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ' ' + new Date(item.created_at).toLocaleDateString();
                        const clientName = item.customer_name ? `${item.customer_name} (${item.customer_phone || ''})` : 'Walk-in Customer';
                        html += `
                            <tr>
                                <td class="fw-bold text-dark">${item.bill_label}</td>
                                <td>${clientName}</td>
                                <td class="fw-bold">${currencySymbol}${parseFloat(item.subtotal).toFixed(2)}</td>
                                <td class="text-muted">${dateStr}</td>
                                <td class="text-end">
                                    <button class="btn btn-primary btn-xs py-1 px-2 fw-bold" onclick="resumeHeldBill(${item.id})">
                                        <i class="fa-solid fa-play me-1"></i>Resume
                                    </button>
                                    <button class="btn btn-danger btn-xs py-1 px-2 ms-1 fw-bold" onclick="deleteHeldBill(${item.id})">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    html += '</tbody></table>';
                }
                $('#recall-list-container').html(html);
                document.getElementById('pos-recall-overlay').style.display = 'flex';
            }
        });
    }

    function closeRecallModal() {
        document.getElementById('pos-recall-overlay').style.display = 'none';
    }

    function resumeHeldBill(holdId) {
        $.ajax({
            url: "{{ route('shop.pos.recallHold', '') }}/" + holdId,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(res) {
                closeRecallModal();
                loadHoldsCount();

                // Restore Customer Details
                $('#customer-phone').val(res.hold.customer_phone || '');
                $('#customer-name').val(res.hold.customer_name || 'Walk-in Customer');
                $('#customer-email').val(res.hold.customer_email || '');

                // Clear existing grid and restore items
                $('#sales-grid-body').empty();
                
                // Process each returned held item in order
                let loadedCount = 0;
                const itemsToLoad = res.hold.items_json;

                if (itemsToLoad && itemsToLoad.length > 0) {
                    itemsToLoad.forEach(function(item) {
                        const newRowId = addGridRow();
                        const tr = $(`#sales-grid-body tr[data-row-id="${newRowId}"]`);

                        // Populate item details
                        tr.find('.barcode-input').val(item.barcode);
                        tr.find('.qty').val(item.qty);
                        tr.find('.sales-rate').val(item.rate);
                        tr.find('.disc-percent').val(item.disc_percent);

                        // Trigger barcode resolution
                        resolveProductBarcode(item.barcode, newRowId, function() {
                            // After base barcode is resolved, apply held variants/prices
                            tr.find('.qty').val(item.qty);
                            tr.find('.sales-rate').val(item.rate);
                            tr.find('.disc-percent').val(item.disc_percent);
                            if (item.color) tr.find('.color-select').val(item.color);
                            if (item.size) tr.find('.size-select').val(item.size);
                            if (item.salesman_id) tr.find('.salesman-select').val(item.salesman_id);
                            
                            loadedCount++;
                            if (loadedCount === itemsToLoad.length) {
                                recalculateCart();
                            }
                        });
                    });
                } else {
                    addGridRow();
                    recalculateCart();
                }
            },
            error: function(err) {
                alert('Failed to resume held bill.');
            }
        });
    }

    function deleteHeldBill(holdId) {
        if (!confirm('Are you sure you want to delete this suspended bill?')) return;
        $.ajax({
            url: "{{ route('shop.pos.recallHold', '') }}/" + holdId,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function() {
                loadHoldsCount();
                showRecallModal(); // reload list
            }
        });
    }

    // ================================================================
    // ================================================================
    // ================================================================
    // RETURN & EXCHANGE / CREDIT NOTES JS
    // ================================================================

    let _returnModalItems = [];
    let _lockedInvoiceProducts = [];
    let _returnModalContext = 'GLOBAL_SEARCH'; // 'GLOBAL_SEARCH' or 'INVOICE_LOCKED'
    let _lockedInvoiceNo = null;

    function showReturnModal(context = 'GLOBAL_SEARCH', lockedInvoice = null) {
        _returnModalContext = context;
        _lockedInvoiceNo = lockedInvoice;
        _returnModalItems = [];
        _lockedInvoiceProducts = [];
        _activeReturnOrder = null;

        $('#return-invoice-search').val(lockedInvoice || '');
        $('#return-details-area').hide();
        $('#return-items-body').empty();
        $('#submit-return-btn, #submit-exchange-btn').prop('disabled', true);
        $('#return-total-refund-val').text(currencySymbol + '0.00');
        document.getElementById('pos-return-overlay').style.display = 'flex';
        setTimeout(() => $('#return-invoice-search').focus(), 300);
    }

    function closeReturnModal() {
        document.getElementById('pos-return-overlay').style.display = 'none';
    }

    function triggerReturnWizardInline(invoiceNo) {
        showReturnModal('INVOICE_LOCKED', invoiceNo);
        lookupInvoiceForReturn(invoiceNo);
    }

    function lookupInvoiceForReturn(forcedQuery = null) {
        const query = (forcedQuery || $('#return-invoice-search').val()).trim();

        // -----------------------------------------------------------------
        // CONTEXT A: Opened from Sales Invoice History Registry (INVOICE_LOCKED)
        // -----------------------------------------------------------------
        if (_returnModalContext === 'INVOICE_LOCKED') {
            if (_lockedInvoiceProducts.length > 0) {
                const searchLower = query.toLowerCase();
                const lockedLower = (_lockedInvoiceNo || '').toLowerCase();

                if (!query || searchLower === lockedLower || searchLower === `#${lockedLower}`) {
                    // Reset search filter to show all products of the locked invoice
                    _returnModalItems = [..._lockedInvoiceProducts];
                    renderReturnModalItemsTable();
                    $('#return-invoice-search').val(_lockedInvoiceNo);
                    return;
                }

                // Search ONLY within the products of this locked invoice
                const matched = _lockedInvoiceProducts.filter(function(p) {
                    const bc = (p.barcode || '').toString().toLowerCase();
                    const name = (p.name || '').toLowerCase();
                    return bc === searchLower || bc.endsWith(searchLower) || name.includes(searchLower);
                });

                if (matched.length > 0) {
                    // Show only the matched item(s) from this locked invoice
                    _returnModalItems = matched;
                    renderReturnModalItemsTable();

                    // Select/Focus Return Qty input of the matched item
                    setTimeout(() => {
                        $('#return-items-body').find('.return-qty-input:not([disabled])').first().focus().select();
                    }, 100);
                } else {
                    // Unrelated barcode or no match found within the locked invoice
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Item Not In Invoice',
                            text: `Barcode / item "${query}" is not part of Invoice #${_lockedInvoiceNo}.`,
                            confirmButtonColor: '#be185d'
                        });
                    } else {
                        alert(`Barcode / item "${query}" is not part of Invoice #${_lockedInvoiceNo}.`);
                    }
                    // Keep locked invoice reference fixed and restore original invoice products list
                    _returnModalItems = [..._lockedInvoiceProducts];
                    renderReturnModalItemsTable();
                    $('#return-invoice-search').val(_lockedInvoiceNo);
                }
                return;
            }
        }

        // -----------------------------------------------------------------
        // CONTEXT B: Initial Fetch for locked invoice OR Global Search (Return F7)
        // -----------------------------------------------------------------
        if (!query) {
            alert('Please enter or scan an invoice number to search.');
            return;
        }

        $.ajax({
            url: "{{ route('shop.pos.returnLookup', '') }}/" + encodeURIComponent(query),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                _activeReturnOrder = res;
                const invRef = `${res.prefix}-${res.order_code}`;

                // Populate invoice summaries
                $('#return-cust-name').text(res.customer_name);
                $('#return-cust-phone').text(res.customer_phone);
                $('#return-orig-total').text(currencySymbol + parseFloat(res.payable_amount).toFixed(2));
                $('#return-invoice-ref').text(`#${invRef}`);

                const loadedProducts = res.products.map(function(p) {
                    const maxReturnable = p.purchased_qty - p.returned_qty;
                    return {
                        order_id: res.order_id,
                        invoice_ref: invRef,
                        customer_name: res.customer_name,
                        customer_phone: res.customer_phone,
                        product_id: p.product_id,
                        name: p.name,
                        barcode: p.barcode,
                        purchased_qty: p.purchased_qty,
                        returned_qty: p.returned_qty,
                        price: p.price,
                        tax_percentage: p.tax_percentage,
                        mrp: p.mrp,
                        return_qty: 0,
                        reason: ''
                    };
                });

                _returnModalItems = loadedProducts;

                // Save master copy for locked invoice context
                if (_returnModalContext === 'INVOICE_LOCKED') {
                    _lockedInvoiceNo = invRef;
                    _lockedInvoiceProducts = [...loadedProducts];
                }

                $('#return-invoice-search').val(invRef);
                renderReturnModalItemsTable();
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Invoice lookup failed.';
                alert(msg);
                $('#return-details-area').hide();
                $('#submit-return-btn, #submit-exchange-btn').prop('disabled', true);
            }
        });
    }

    function renderReturnModalItemsTable() {
        if (!_activeReturnOrder || _returnModalItems.length === 0) {
            $('#return-items-body').html('<tr><td colspan="8" class="text-center text-muted py-3">No items available for return.</td></tr>');
            $('#return-details-area').hide();
            updateRefundPayable();
            return;
        }

        let html = '';
        _returnModalItems.forEach(function(item, i) {
            const maxReturnable = item.purchased_qty - item.returned_qty;

            html += `
                <tr id="return-item-row-${i}">
                    <td><strong>${item.name}</strong></td>
                    <td><span class="badge bg-secondary font-monospace" style="font-size:11px;">#${item.invoice_ref}</span></td>
                    <td class="fw-bold font-monospace">${item.barcode}</td>
                    <td class="text-center">${currencySymbol}${item.price.toFixed(2)}</td>
                    <td class="text-center">${item.purchased_qty}</td>
                    <td class="text-center text-danger">${item.returned_qty}</td>
                    <td class="text-center">
                        <input type="number" class="form-control text-center return-qty-input" 
                               style="width: 70px; height: 32px; font-size: 12px; padding: 4px; ${maxReturnable <= 0 ? 'background: rgba(255,255,255,0.05); cursor: not-allowed;' : ''}" 
                               min="0" max="${maxReturnable}" value="${item.return_qty}" 
                               onkeyup="updateReturnItemQty(${i}, this.value)" onchange="updateReturnItemQty(${i}, this.value)"
                               ${maxReturnable <= 0 ? 'disabled' : ''}>
                    </td>
                    <td>
                        <input type="text" class="form-control return-reason-input" 
                               placeholder="${maxReturnable <= 0 ? 'Already Returned' : 'Reason...'}" 
                               value="${item.reason}"
                               onchange="updateReturnItemReason(${i}, this.value)"
                               style="height: 32px; font-size: 12px; padding: 4px 8px; ${maxReturnable <= 0 ? 'background: rgba(255,255,255,0.05); cursor: not-allowed;' : ''}"
                               ${maxReturnable <= 0 ? 'disabled' : ''}>
                    </td>
                </tr>
            `;
        });

        $('#return-items-body').html(html);
        $('#return-details-area').show();
        updateRefundPayable();
    }

    function updateReturnItemQty(idx, val) {
        if (!_returnModalItems[idx]) return;
        const maxVal = _returnModalItems[idx].purchased_qty - _returnModalItems[idx].returned_qty;
        let numVal = parseInt(val) || 0;
        if (numVal > maxVal) numVal = maxVal;
        if (numVal < 0) numVal = 0;
        _returnModalItems[idx].return_qty = numVal;
        updateRefundPayable();
    }

    function updateReturnItemReason(idx, val) {
        if (!_returnModalItems[idx]) return;
        _returnModalItems[idx].reason = (val || '').trim();
    }

    function updateRefundPayable() {
        let totalRefund = 0;
        let activeQtyCount = 0;

        _returnModalItems.forEach(function(item) {
            const qty = parseInt(item.return_qty) || 0;
            if (qty > 0) {
                totalRefund += (parseFloat(item.price) || 0) * qty;
                activeQtyCount += qty;
            }
        });

        $('#return-total-refund-val').text(currencySymbol + totalRefund.toFixed(2));
        $('#submit-return-btn, #submit-exchange-btn').prop('disabled', activeQtyCount === 0);
    }

    function startExchangeProcess() {
        const items = [];
        const invoiceRefs = new Set();
        let totalRefund = 0;

        _returnModalItems.forEach(item => {
            if (item.return_qty > 0) {
                const lineTotal = item.price * item.return_qty;
                totalRefund += lineTotal;
                invoiceRefs.add(item.invoice_ref);
                items.push({
                    order_id: item.order_id,
                    original_invoice_no: item.invoice_ref,
                    barcode: item.barcode,
                    qty: item.return_qty,
                    rate: item.price,
                    name: item.name,
                    reason: item.reason || ''
                });
            }
        });

        if (items.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please specify quantity > 0 for at least one item to exchange.',
                    confirmButtonColor: '#be185d'
                });
            } else {
                alert('Please specify quantity > 0 for at least one item to exchange.');
            }
            return;
        }

        const invoiceRefStr = Array.from(invoiceRefs).map(r => '#' + r).join(', ');

        window._activeExchangeSession = {
            order_id: items[0].order_id,
            original_invoice_no: Array.from(invoiceRefs).join(', '),
            returned_items: items,
            returned_total: totalRefund,
            customer_phone: _returnModalItems[0]?.customer_phone || '',
            customer_name: _returnModalItems[0]?.customer_name || ''
        };

        closeReturnModal();

        if (!($('#customer-phone').val()) && window._activeExchangeSession.customer_phone) {
            $('#customer-phone').val(window._activeExchangeSession.customer_phone);
            lookupCustomerByPhone(window._activeExchangeSession.customer_phone);
        }

        $('#exchange-banner-ref').text(invoiceRefStr);
        $('#exchange-banner-credit').text(currencySymbol + totalRefund.toFixed(2));
        $('#exchange-active-banner').removeClass('d-none').addClass('d-flex').show();
        recalculateCart();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Exchange Mode Activated',
                text: 'Returned credit of ' + currencySymbol + totalRefund.toFixed(2) + ' loaded from ' + invoiceRefStr + '. Please add the new product(s) to the sales grid to complete the combined exchange.',
                confirmButtonColor: '#be185d'
            });
        }
    }

    function cancelExchangeSession() {
        window._activeExchangeSession = null;
        $('#exchange-active-banner').removeClass('d-flex').addClass('d-none').hide();
        $('#summary-exchange-row').hide().remove();
        recalculateCart();
    }

    function submitReturnTransaction() {
        const itemsPayload = [];
        _returnModalItems.forEach(item => {
            if (item.return_qty > 0) {
                itemsPayload.push({
                    order_id: item.order_id,
                    barcode: item.barcode,
                    qty: item.return_qty,
                    reason: item.reason || ''
                });
            }
        });

        if (itemsPayload.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please specify quantity > 0 for at least one item to return.',
                    confirmButtonColor: '#be185d'
                });
            } else {
                alert('Please specify quantity > 0 for at least one item to return.');
            }
            return;
        }

        const performSubmit = function() {
            const payload = {
                _token: "{{ csrf_token() }}",
                refund_method: $('#return-refund-method').val(),
                counter_id: $('#selected-counter-id').val(),
                items: itemsPayload
            };

            $.ajax({
                url: "{{ route('shop.pos.returnSubmit') }}",
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Return Completed',
                            text: res.message,
                            confirmButtonColor: '#be185d'
                        });
                    } else {
                        alert(res.message);
                    }
                    closeReturnModal();
                    resetGridBill();
                    if (res.return_no) {
                        openPOSReturnPrintPreview(res.return_no, false);
                    }
                },
                error: function(err) {
                    const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Failed to submit return transaction.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Return Failed',
                            text: msg,
                            confirmButtonColor: '#be185d'
                        });
                    } else {
                        alert(msg);
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirm Product Return',
                text: 'Are you sure you want to process this product return transaction?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#be185d',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Confirm',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performSubmit();
                }
            });
        } else {
            if (confirm('Are you sure you want to process this product return transaction?')) {
                performSubmit();
            }
        }
    }

    $(document).on('keydown', '#return-invoice-search', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            lookupInvoiceForReturn();
        }
    });

    // ================================================================
    // RETURN (F7) STANDALONE MULTI-ITEM / MULTI-INVOICE RETURN & EXCHANGE
    // ================================================================

    let _returnF7Items = [];

    function showReturnF7Modal() {
        _returnF7Items = [];
        $('#return-f7-barcode-search').val('');
        $('#return-f7-invoice-search').val('');
        $('#return-f7-details-area').hide();
        $('#return-f7-items-body').empty();
        $('#submit-f7-return-btn, #submit-f7-exchange-btn').prop('disabled', true);
        $('#return-f7-total-refund-val').text(currencySymbol + '0.00');
        document.getElementById('pos-return-f7-overlay').style.display = 'flex';
        setTimeout(() => $('#return-f7-barcode-search').focus(), 300);
    }

    function closeReturnF7Modal() {
        document.getElementById('pos-return-f7-overlay').style.display = 'none';
    }

    function addBarcodeReturnF7() {
        const barcodeQuery = $('#return-f7-barcode-search').val().trim();
        if (!barcodeQuery) {
            alert('Please scan or enter a product barcode.');
            return;
        }

        $.ajax({
            url: "{{ route('shop.pos.returnLookup', '') }}/" + encodeURIComponent(barcodeQuery),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const invRef = `${res.prefix}-${res.order_code}`;
                const qLower = barcodeQuery.toLowerCase();

                // Find ONLY the exact product matching this barcode in the returned order
                const matchedProducts = res.products.filter(function(p) {
                    const bc = (p.barcode || '').toString().trim().toLowerCase();
                    return bc === qLower;
                });

                if (matchedProducts.length === 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Barcode Not Found',
                            text: `No sold item found matching barcode: ${barcodeQuery}`,
                            confirmButtonColor: '#be185d'
                        });
                    } else {
                        alert(`No sold item found matching barcode: ${barcodeQuery}`);
                    }
                    $('#return-f7-barcode-search').focus();
                    return;
                }

                let lastAddedIndex = -1;
                matchedProducts.forEach(function(p) {
                    const existingIdx = _returnF7Items.findIndex(it => it.order_id === res.order_id && it.barcode === p.barcode);
                    if (existingIdx === -1) {
                        const maxReturnable = p.purchased_qty - p.returned_qty;
                        _returnF7Items.push({
                            order_id: res.order_id,
                            invoice_ref: invRef,
                            customer_name: res.customer_name,
                            customer_phone: res.customer_phone,
                            product_id: p.product_id,
                            name: p.name,
                            barcode: p.barcode,
                            purchased_qty: p.purchased_qty,
                            returned_qty: p.returned_qty,
                            price: p.price,
                            tax_percentage: p.tax_percentage,
                            mrp: p.mrp,
                            return_qty: (maxReturnable > 0 ? 1 : 0),
                            reason: ''
                        });
                        lastAddedIndex = _returnF7Items.length - 1;
                    } else {
                        lastAddedIndex = existingIdx;
                    }
                });

                $('#return-f7-barcode-search').val('').focus();
                renderReturnF7Table(lastAddedIndex);
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Barcode lookup failed.';
                alert(msg);
                $('#return-f7-barcode-search').focus();
            }
        });
    }

    function addInvoiceReturnF7() {
        const invoiceQuery = $('#return-f7-invoice-search').val().trim();
        if (!invoiceQuery) {
            alert('Please enter an invoice number to search.');
            return;
        }

        $.ajax({
            url: "{{ route('shop.pos.returnLookup', '') }}/" + encodeURIComponent(invoiceQuery),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const invRef = `${res.prefix}-${res.order_code}`;

                let lastAddedIndex = -1;
                res.products.forEach(function(p) {
                    const existingIdx = _returnF7Items.findIndex(it => it.order_id === res.order_id && it.barcode === p.barcode);
                    if (existingIdx === -1) {
                        const maxReturnable = p.purchased_qty - p.returned_qty;
                        _returnF7Items.push({
                            order_id: res.order_id,
                            invoice_ref: invRef,
                            customer_name: res.customer_name,
                            customer_phone: res.customer_phone,
                            product_id: p.product_id,
                            name: p.name,
                            barcode: p.barcode,
                            purchased_qty: p.purchased_qty,
                            returned_qty: p.returned_qty,
                            price: p.price,
                            tax_percentage: p.tax_percentage,
                            mrp: p.mrp,
                            return_qty: (maxReturnable > 0 ? 1 : 0),
                            reason: ''
                        });
                        lastAddedIndex = _returnF7Items.length - 1;
                    } else {
                        lastAddedIndex = existingIdx;
                    }
                });

                $('#return-f7-invoice-search').val('').focus();
                renderReturnF7Table(lastAddedIndex);
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Invoice lookup failed.';
                alert(msg);
                $('#return-f7-invoice-search').focus();
            }
        });
    }

    function renderReturnF7Table(highlightIdx = -1) {
        if (_returnF7Items.length === 0) {
            $('#return-f7-items-body').html('<tr><td colspan="9" class="text-center text-muted py-3">No items loaded. Scan barcode or enter invoice no.</td></tr>');
            $('#return-f7-details-area').hide();
            updateRefundPayableF7();
            return;
        }

        const invRefs = Array.from(new Set(_returnF7Items.map(it => '#' + it.invoice_ref)));
        const custNames = Array.from(new Set(_returnF7Items.map(it => it.customer_name).filter(Boolean)));
        const custPhones = Array.from(new Set(_returnF7Items.map(it => it.customer_phone).filter(Boolean)));

        $('#return-f7-cust-name').text(custNames.join(', ') || 'Walk-in Customer');
        $('#return-f7-cust-phone').text(custPhones.join(', ') || '-');
        $('#return-f7-invoice-ref').text(invRefs.join(', '));

        let html = '';
        _returnF7Items.forEach(function(item, i) {
            const maxReturnable = item.purchased_qty - item.returned_qty;
            const isHighlight = (i === highlightIdx);

            html += `
                <tr id="return-f7-item-row-${i}" style="${isHighlight ? 'background-color: #fef08a;' : ''}">
                    <td><strong>${item.name}</strong></td>
                    <td><span class="badge bg-secondary font-monospace" style="font-size:11px;">#${item.invoice_ref}</span></td>
                    <td class="fw-bold font-monospace">${item.barcode}</td>
                    <td class="text-center">${currencySymbol}${item.price.toFixed(2)}</td>
                    <td class="text-center">${item.purchased_qty}</td>
                    <td class="text-center text-danger">${item.returned_qty}</td>
                    <td class="text-center">
                        <input type="number" class="form-control text-center return-f7-qty-input" 
                               style="width: 70px; height: 32px; font-size: 12px; padding: 4px; ${maxReturnable <= 0 ? 'background: rgba(255,255,255,0.05); cursor: not-allowed;' : ''}" 
                               min="0" max="${maxReturnable}" value="${item.return_qty}" 
                               onkeyup="updateReturnF7Qty(${i}, this.value)" onchange="updateReturnF7Qty(${i}, this.value)"
                               ${maxReturnable <= 0 ? 'disabled' : ''}>
                    </td>
                    <td>
                        <input type="text" class="form-control return-f7-reason-input" 
                               placeholder="${maxReturnable <= 0 ? 'Already Returned' : 'Reason...'}" 
                               value="${item.reason}"
                               onchange="updateReturnF7Reason(${i}, this.value)"
                               style="height: 32px; font-size: 12px; padding: 4px 8px; ${maxReturnable <= 0 ? 'background: rgba(255,255,255,0.05); cursor: not-allowed;' : ''}"
                               ${maxReturnable <= 0 ? 'disabled' : ''}>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeReturnF7Item(${i})" title="Remove item">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#return-f7-items-body').html(html);
        $('#return-f7-details-area').show();
        updateRefundPayableF7();

        if (highlightIdx >= 0) {
            setTimeout(() => {
                $(`#return-f7-item-row-${highlightIdx}`).find('.return-f7-qty-input').focus().select();
            }, 100);
        }
    }

    function updateReturnF7Qty(idx, val) {
        if (!_returnF7Items[idx]) return;
        const maxVal = _returnF7Items[idx].purchased_qty - _returnF7Items[idx].returned_qty;
        let numVal = parseInt(val) || 0;
        if (numVal > maxVal) numVal = maxVal;
        if (numVal < 0) numVal = 0;
        _returnF7Items[idx].return_qty = numVal;
        updateRefundPayableF7();
    }

    function updateReturnF7Reason(idx, val) {
        if (!_returnF7Items[idx]) return;
        _returnF7Items[idx].reason = (val || '').trim();
    }

    function removeReturnF7Item(idx) {
        _returnF7Items.splice(idx, 1);
        renderReturnF7Table();
    }

    function updateRefundPayableF7() {
        let totalRefund = 0;
        let activeQtyCount = 0;

        _returnF7Items.forEach(function(item) {
            const qty = parseInt(item.return_qty) || 0;
            if (qty > 0) {
                totalRefund += (parseFloat(item.price) || 0) * qty;
                activeQtyCount += qty;
            }
        });

        $('#return-f7-total-refund-val').text(currencySymbol + totalRefund.toFixed(2));
        $('#submit-f7-return-btn, #submit-f7-exchange-btn').prop('disabled', activeQtyCount === 0);
    }

    function startExchangeF7Process() {
        const items = [];
        const invoiceRefs = new Set();
        let totalRefund = 0;

        _returnF7Items.forEach(item => {
            if (item.return_qty > 0) {
                const lineTotal = item.price * item.return_qty;
                totalRefund += lineTotal;
                invoiceRefs.add(item.invoice_ref);
                items.push({
                    order_id: item.order_id,
                    original_invoice_no: item.invoice_ref,
                    barcode: item.barcode,
                    qty: item.return_qty,
                    rate: item.price,
                    name: item.name,
                    reason: item.reason || ''
                });
            }
        });

        if (items.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please specify quantity > 0 for at least one item to exchange.',
                    confirmButtonColor: '#be185d'
                });
            } else {
                alert('Please specify quantity > 0 for at least one item to exchange.');
            }
            return;
        }

        const invoiceRefStr = Array.from(invoiceRefs).map(r => '#' + r).join(', ');

        window._activeExchangeSession = {
            order_id: items[0].order_id,
            original_invoice_no: Array.from(invoiceRefs).join(', '),
            returned_items: items,
            returned_total: totalRefund,
            customer_phone: _returnF7Items[0]?.customer_phone || '',
            customer_name: _returnF7Items[0]?.customer_name || ''
        };

        closeReturnF7Modal();

        if (!($('#customer-phone').val()) && window._activeExchangeSession.customer_phone) {
            $('#customer-phone').val(window._activeExchangeSession.customer_phone);
            lookupCustomerByPhone(window._activeExchangeSession.customer_phone);
        }

        $('#exchange-banner-ref').text(invoiceRefStr);
        $('#exchange-banner-credit').text(currencySymbol + totalRefund.toFixed(2));
        $('#exchange-active-banner').removeClass('d-none').addClass('d-flex').show();
        recalculateCart();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Exchange Mode Activated',
                text: 'Returned credit of ' + currencySymbol + totalRefund.toFixed(2) + ' loaded from ' + invoiceRefStr + '. Please add the new product(s) to the sales grid to complete the combined exchange.',
                confirmButtonColor: '#be185d'
            });
        }
    }

    function submitReturnF7Transaction() {
        const itemsPayload = [];
        _returnF7Items.forEach(item => {
            if (item.return_qty > 0) {
                itemsPayload.push({
                    order_id: item.order_id,
                    barcode: item.barcode,
                    qty: item.return_qty,
                    reason: item.reason || ''
                });
            }
        });

        if (itemsPayload.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please specify quantity > 0 for at least one item to return.',
                    confirmButtonColor: '#be185d'
                });
            } else {
                alert('Please specify quantity > 0 for at least one item to return.');
            }
            return;
        }

        const performSubmit = function() {
            const payload = {
                _token: "{{ csrf_token() }}",
                refund_method: $('#return-f7-refund-method').val(),
                counter_id: $('#selected-counter-id').val(),
                items: itemsPayload
            };

            $.ajax({
                url: "{{ route('shop.pos.returnSubmit') }}",
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Return Completed',
                            text: res.message,
                            confirmButtonColor: '#be185d'
                        });
                    } else {
                        alert(res.message);
                    }
                    closeReturnF7Modal();
                    resetGridBill();
                    if (res.return_no) {
                        openPOSReturnPrintPreview(res.return_no, false);
                    }
                },
                error: function(err) {
                    const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Failed to submit return transaction.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Return Failed',
                            text: msg,
                            confirmButtonColor: '#be185d'
                        });
                    } else {
                        alert(msg);
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirm Product Return',
                text: 'Are you sure you want to process this product return transaction?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#be185d',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Confirm',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performSubmit();
                }
            });
        } else {
            if (confirm('Are you sure you want to process this product return transaction?')) {
                performSubmit();
            }
        }
    }

    $(document).on('keydown', '#pos-return-overlay input, #pos-return-overlay select', function (e) {
        if (e.key === 'Enter') {
            if ($(this).attr('id') === 'return-invoice-search') {
                return;
            }
            e.preventDefault();
            let $inputs = $('#pos-return-overlay').find('input:not([readonly]):not([disabled]), select:not([readonly]):not([disabled])');
            let index = $inputs.index(this);
            if (index > -1 && index < $inputs.length - 1) {
                $inputs.eq(index + 1).focus().select();
            } else if (index === $inputs.length - 1) {
                $('#submit-return-btn').focus();
            }
        }
    });

    // ================================================================
    // REGISTER SHIFT JS LOGIC
    // ================================================================

    function checkShiftStatus(counterId) {
        if (!counterId) return;
        $.ajax({
            url: "{{ route('shop.pos.shiftStatus') }}?counter_id=" + counterId,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.has_active_shift) {
                    $('#close-shift-btn').show();
                    document.getElementById('pos-shift-open-overlay').style.display = 'none';
                } else {
                    $('#close-shift-btn').hide();
                    document.getElementById('pos-shift-open-overlay').style.display = 'flex';
                    setTimeout(() => $('#shift-opening-cash-input').focus().select(), 300);
                }
            }
        });
    }

    function submitOpenShiftSession() {
        const counterId = $('#selected-counter-id').val();
        const openingCash = parseFloat($('#shift-opening-cash-input').val()) || 0;

        if (openingCash < 0) {
            alert('Opening cash must be a positive number.');
            return;
        }

        $.ajax({
            url: "{{ route('shop.pos.shiftOpen') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                counter_id: counterId,
                opening_cash: openingCash
            },
            dataType: 'json',
            success: function(res) {
                alert(res.message, 'success');
                document.getElementById('pos-shift-open-overlay').style.display = 'none';
                $('#close-shift-btn').show();
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Failed to open shift register.';
                alert(msg);
            }
        });
    }

    function showCloseShiftModal() {
        const counterId = $('#selected-counter-id').val();
        if (!counterId) return;

        $.ajax({
            url: "{{ route('shop.pos.shiftStats') }}?counter_id=" + counterId,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                $('#shift-disp-counter').text(_selectedCounter ? _selectedCounter.name : 'Counter');
                $('#shift-disp-cashier').text(res.shift.user_id);
                
                const openedDate = new Date(res.shift.opened_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ' ' + new Date(res.shift.opened_at).toLocaleDateString();
                $('#shift-disp-opened').text(openedDate);
                $('#shift-disp-opening').text(currencySymbol + parseFloat(res.shift.opening_cash).toFixed(2));
                
                $('#shift-disp-sales-cash').text(currencySymbol + parseFloat(res.stats.cash_sales).toFixed(2));
                $('#shift-disp-sales-card').text(currencySymbol + parseFloat(res.stats.card_sales).toFixed(2));
                $('#shift-disp-returns-cash').text(currencySymbol + parseFloat(res.stats.cash_returns).toFixed(2));
                $('#shift-disp-returns-card').text(currencySymbol + parseFloat(res.stats.card_returns).toFixed(2));
                $('#shift-disp-expected').text(currencySymbol + parseFloat(res.stats.expected_cash).toFixed(2));

                $('#shift-closing-cash-input').val(res.stats.expected_cash.toFixed(2));
                $('#shift-closing-note-input').val('');
                
                document.getElementById('pos-shift-close-overlay').style.display = 'flex';
                setTimeout(() => $('#shift-closing-cash-input').focus().select(), 300);
            },
            error: function(err) {
                alert('No active shift session found on this counter.');
            }
        });
    }

    function closeShiftModal() {
        document.getElementById('pos-shift-close-overlay').style.display = 'none';
    }

    async function submitCloseShiftSession() {
        const counterId = $('#selected-counter-id').val();
        const closingCash = parseFloat($('#shift-closing-cash-input').val()) || 0;
        const note = $('#shift-closing-note-input').val().trim();

        if (!await posConfirm('Are you sure you want to close this register shift session? This action will log you out of this counter session.')) return;

        $.ajax({
            url: "{{ route('shop.pos.shiftClose') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                counter_id: counterId,
                closing_cash: closingCash,
                note: note
            },
            dataType: 'json',
            success: function(res) {
                alert(res.message + "\nDrawer Difference: " + currencySymbol + parseFloat(res.shift.difference).toFixed(2));
                closeShiftModal();
                // Reload page to prompt for new counter shift
                window.location.reload();
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Failed to close register shift.';
                alert(msg);
            }
        });
    }

    // Initialize hold bills count on page load
    $(document).ready(function() {
        loadHoldBillsRegisterCount();
    });

    function loadHoldBillsRegisterCount() {
        $.ajax({
            url: "{{ route('shop.pos.holdBills.list') }}",
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#hold-bills-count-badge').text(data.length);
            }
        });
    }

    // ================================================================
    // HOLD BILL MODULE ACTIONS
    // ================================================================

    async function saveHoldBillSession() {
        const items = [];
        let valid = true;
        let totalSubtotal = 0;
        let totalDiscount = 0;
        let totalTaxAmount = 0;
        const taxDetails = {};

        $('#sales-grid-body tr').each(function() {
            const row = $(this);
            const barcode = row.find('.barcode-input').val() ? row.find('.barcode-input').val().trim() : '';
            if (!barcode) return;

            const qty = parseFloat(row.find('.qty').val()) || 0;
            const rate = parseFloat(row.find('.sales-rate').val()) || 0;
            const discPercent = parseFloat(row.find('.disc-percent').val()) || 0;
            const color = row.find('.color-select').val();
            const size = row.find('.size-select').val();
            const salesmanId = row.find('.salesman-select').val() || null;
            const taxName = row.find('.tax-name-hidden').val() || 'GST'; // Fallback if hidden exists
            const taxPercent = parseFloat(row.find('.tax-percent-hidden').val()) || 0;

            if (qty <= 0) {
                alert(`Row ${row.find('.row-number').text()}: Quantity must be > 0.`);
                valid = false;
                return false;
            }

            const discAmt = rate * qty * (discPercent / 100);
            const lineTotal = (rate * qty) - discAmt;
            const lineTaxable = taxPercent > 0 ? round((lineTotal * 100) / (100 + taxPercent), 2) : round(lineTotal, 2);
            const taxAmt = round(lineTotal - lineTaxable, 2);

            totalSubtotal += (rate * qty);
            totalDiscount += discAmt;
            totalTaxAmount += taxAmt;

            // Track tax breakdown
            if (taxPercent > 0) {
                const taxKey = taxName + '_' + taxPercent;
                if (!taxDetails[taxKey]) {
                    taxDetails[taxKey] = { name: taxName, percentage: taxPercent, amount: 0 };
                }
                taxDetails[taxKey].amount += taxAmt;
            }

            const productId = row.data('product-id') || null;

            items.push({
                product_id: productId,
                barcode: barcode,
                qty: qty,
                rate: rate,
                disc_percent: discPercent,
                disc_amt: discAmt,
                tax_percent: taxPercent,
                tax_amt: taxAmt,
                tax_name: taxName,
                color: color,
                size: size,
                salesman_id: salesmanId
            });
        });

        if (!valid) return;

        if (items.length === 0) {
            alert('Cannot hold an empty cart.');
            return;
        }

        const remarks = await posPrompt("Enter remarks / notes for this held bill (Optional):");
        if (remarks === null) return; // user cancelled

        const payload = {
            _token: "{{ csrf_token() }}",
            counter_id: $('#selected-counter-id').val(),
            customer_id: $('#customer-id').val() || null, // Hidden resolved customer ID
            subtotal: totalSubtotal,
            discount: totalDiscount,
            tax_amount: totalTaxAmount,
            payable_amount: totalSubtotal - totalDiscount + totalTaxAmount,
            payment_method: $('#payment-method').val(),
            salesman_id: $('#sales-grid-body tr').first().find('.salesman-select').val() || null,
            remarks: remarks.trim(),
            tax_details: taxDetails,
            customer_name: $('#customer-name').val().trim(),
            customer_phone: $('#customer-phone').val().trim(),
            customer_email: $('#customer-email').val().trim(),
            items: items
        };

        $.ajax({
            url: "{{ route('shop.pos.holdBills.save') }}",
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function(res) {
                alert(res.message + "\nHold Number: " + res.hold_no);
                loadHoldBillsRegisterCount();
                resetGridBill();
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Failed to hold bill.';
                alert(msg);
            }
        });
    }

    let _holdBillsList = [];

    function showHoldBillsListModal() {
        $.ajax({
            url: "{{ route('shop.pos.holdBills.list') }}",
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                _holdBillsList = data;
                renderHoldBillsTable(data);
                document.getElementById('pos-hold-bills-overlay').style.display = 'flex';
                setTimeout(() => $('#hold-bills-search-input').focus(), 300);
            }
        });
    }

    function renderHoldBillsTable(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="7" class="text-center text-muted py-4">No held bills found.</td></tr>';
        } else {
            data.forEach(function(h, idx) {
                const isFirst = idx === 0 ? 'class="active-row bg-primary text-white"' : '';
                html += `
                    <tr data-id="${h.id}" ${isFirst} onclick="selectHoldBillRow($(this))" style="cursor: pointer;">
                        <td class="fw-bold">${h.hold_no}</td>
                        <td>${h.customer_name}</td>
                        <td>${h.customer_phone}</td>
                        <td>${h.cashier_name}</td>
                        <td class="text-right fw-bold">${currencySymbol}${parseFloat(h.amount).toFixed(2)}</td>
                        <td>${h.date}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <button class="btn btn-primary fw-bold text-white btn-action-restore" onclick="restoreHoldBillRecord(${h.id}); event.stopPropagation();" style="font-size: 10px !important; padding: 2px 6px !important; border-radius: 4px !important; font-weight: bold !important; line-height: 1.2 !important;">Restore</button>
                                <button class="btn btn-success fw-bold text-white btn-action-dup" onclick="duplicateHoldBillRecord(${h.id}); event.stopPropagation();" style="font-size: 10px !important; padding: 2px 6px !important; border-radius: 4px !important; font-weight: bold !important; line-height: 1.2 !important;">Duplicate</button>
                                <button class="btn btn-info fw-bold text-white btn-action-print" onclick="printHoldReceiptRecord(${h.id}); event.stopPropagation();" style="font-size: 10px !important; padding: 2px 6px !important; border-radius: 4px !important; font-weight: bold !important; line-height: 1.2 !important;">Print</button>
                                <button class="btn btn-danger fw-bold text-white btn-action-del" onclick="deleteHoldBillRecord(${h.id}); event.stopPropagation();" style="font-size: 10px !important; padding: 2px 6px !important; border-radius: 4px !important; font-weight: bold !important; line-height: 1.2 !important;">Delete</button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        $('#hold-bills-items-body').html(html);
    }

    function selectHoldBillRow(tr) {
        $('#hold-bills-items-body tr').removeClass('active-row bg-primary text-white');
        tr.addClass('active-row bg-primary text-white');
    }

    function closeHoldBillsListModal() {
        document.getElementById('pos-hold-bills-overlay').style.display = 'none';
    }

    function filterHoldBillsRegister() {
        const query = $('#hold-bills-search-input').val().trim().toLowerCase();
        const dateVal = $('#hold-bills-date-input').val();

        let filtered = _holdBillsList;

        if (query) {
            filtered = filtered.filter(function(h) {
                return h.hold_no.toLowerCase().includes(query) ||
                       h.customer_name.toLowerCase().includes(query) ||
                       h.customer_phone.toLowerCase().includes(query);
            });
        }

        if (dateVal) {
            filtered = filtered.filter(function(h) {
                return h.date.startsWith(dateVal);
            });
        }

        renderHoldBillsTable(filtered);
    }

    function resetHoldBillsFilters() {
        $('#hold-bills-search-input').val('');
        $('#hold-bills-date-input').val('');
        renderHoldBillsTable(_holdBillsList);
    }

    function restoreHoldBillRecord(id) {
        $.ajax({
            url: `/shop/pos/hold-bills/${id}/restore`,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(res) {
                closeHoldBillsListModal();
                loadHoldBillsRegisterCount();

                // Load Customer details
                $('#customer-phone').val(res.hold.customer_phone || '');
                $('#customer-name').val(res.hold.customer_name || 'Walk-in Customer');
                $('#customer-email').val(res.hold.customer_email || '');
                if (res.hold.customer_id) {
                    $('#customer-id').val(res.hold.customer_id);
                }

                // Load remarks
                if (res.hold.remarks) {
                    // Update notes if UI input exists
                    $('textarea[name="note"]').val(res.hold.remarks);
                }

                // Clear existing grid and restore items
                $('#sales-grid-body').empty();
                
                const itemsToLoad = res.hold.items;
                let loadedCount = 0;

                if (itemsToLoad && itemsToLoad.length > 0) {
                    itemsToLoad.forEach(function(item) {
                        const newRowId = addGridRow();
                        const tr = $(`#sales-grid-body tr[data-row-id="${newRowId}"]`);

                        // Associate product id to table row
                        tr.data('product-id', item.product_id);

                        // Populate inputs
                        tr.find('.barcode-input').val(item.barcode);
                        tr.find('.qty').val(item.qty);
                        tr.find('.sales-rate').val(item.rate);
                        tr.find('.disc-percent').val(item.disc_percent);

                        // Fetch product details
                        resolveProductBarcode(item.barcode, newRowId, function() {
                            // Apply custom values
                            tr.find('.qty').val(item.qty);
                            tr.find('.sales-rate').val(item.rate);
                            tr.find('.disc-percent').val(item.disc_percent);
                            if (item.color) tr.find('.color-select').val(item.color);
                            if (item.size) tr.find('.size-select').val(item.size);
                            if (item.salesman_id) tr.find('.salesman-select').val(item.salesman_id);
                            
                            loadedCount++;
                            if (loadedCount === itemsToLoad.length) {
                                recalculateCart();
                            }
                        });
                    });
                } else {
                    addGridRow();
                    recalculateCart();
                }
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Failed to restore hold bill.';
                alert(msg);
            }
        });
    }

    function duplicateHoldBillRecord(id) {
        $.ajax({
            url: `/shop/pos/hold-bills/${id}/duplicate`,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(res) {
                alert(res.message + "\nNew Hold Number: " + res.hold_no);
                loadHoldBillsRegisterCount();
                showHoldBillsListModal(); // reload table
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.error ? err.responseJSON.error : 'Duplication failed.';
                alert(msg);
            }
        });
    }

    function printHoldReceiptRecord(id) {
        const url = `/shop/pos/hold-bills/${id}/print`;
        window.open(url, '_blank', 'width=600,height=800');
    }

    async function deleteHoldBillRecord(id) {
        if (!await posConfirm('Are you sure you want to delete this suspended hold bill?', 'danger')) return;
        $.ajax({
            url: `/shop/pos/hold-bills/${id}`,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(res) {
                alert(res.message);
                loadHoldBillsRegisterCount();
                showHoldBillsListModal(); // reload list
            },
            error: function(err) {
                alert('Deletion failed. Ensure you have appropriate permissions.');
            }
        });
    }

</script>

<!-- Integrated EDC Terminal Payment Modal -->
<div class="modal fade" id="edcPaymentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fw-bold" id="edcModalTitle"><i class="fa-solid fa-cash-register me-2"></i>EDC Terminal Payment</h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3" id="edcModalSpinner">
                    <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mb-1" id="edcModalAmount">₹0.00</h3>
                <p class="text-muted mb-3" id="edcModalMethod">Paytm Card Payment</p>

                <div class="alert alert-info py-2 px-3 fw-bold" id="edcModalStatusMsg" role="alert">
                    Please complete the payment on the selected EDC terminal.
                </div>

                <div class="text-start bg-light p-2 rounded border small text-muted font-monospace" id="edcModalDetails">
                    <div>Attempt ID: <span id="edc-detail-attempt-id">-</span></div>
                    <div>Terminal: <span id="edc-detail-terminal">-</span></div>
                    <div>Status: <span id="edc-detail-status">INITIALIZING</span></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-edc-check-status" onclick="checkEdcPaymentStatus()" style="display: none;">
                    <i class="fa-solid fa-rotate me-1"></i> Check Status (Ctrl+F11)
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn-edc-cancel" onclick="cancelEdcPayment()">
                    <i class="fa-solid fa-xmark me-1"></i> Cancel (Ctrl+X)
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="btn-edc-reprint" onclick="reprintEdcInvoice()" style="display: none;">
                    <i class="fa-solid fa-print me-1"></i> Reprint Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/pos-terminal.js') }}"></script>
@endpush
