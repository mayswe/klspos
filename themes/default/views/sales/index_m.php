<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<style>
/* =========================================================
   KLSPOS - Sales List + Customer Due Payment ERP Tabs
   Clean Full Version
   ========================================================= */

.sales-tabs-page {
    padding: 16px 20px 26px !important;
    background: #f4f7fb;
}

.sales-tabs-page .erp-shell {
    overflow: hidden;
    background: #fff;
    border: 1px solid #dfe6ef;
    border-radius: 14px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
}

/* Header */
.sales-tabs-page .erp-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    border-bottom: 1px solid #e8eef5;
    background: linear-gradient(135deg, #ffffff 0%, #f7fbfc 100%);
}

.sales-tabs-page .erp-title-wrap {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.sales-tabs-page .erp-title-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    flex: 0 0 43px;
    border-radius: 11px;
    background: #e7f7f4;
    color: #2c7f75;
    font-size: 18px;
}

.sales-tabs-page .erp-title-text h3 {
    margin: 0;
    color: #172033;
    font-size: 20px;
    font-weight: 800;
    line-height: 1.25;
}

/* =========================================================
   Tabs - Desktop / Tablet / Mobile all side by side
   ========================================================= */

.sales-tabs-page .erp-tabs-wrap {
    padding: 12px 16px 0;
    background: #fff;
}

.sales-tabs-page ul.nav.nav-tabs.erp-tabs {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 7px !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 5px !important;
    border: 1px solid #e1e8ef;
    border-radius: 10px;
    background: #f4f7fa;
}

/* Bootstrap .nav pseudo elements must not become grid cells */
.sales-tabs-page ul.nav.nav-tabs.erp-tabs::before,
.sales-tabs-page ul.nav.nav-tabs.erp-tabs::after {
    display: none !important;
    content: none !important;
}

.sales-tabs-page ul.nav.nav-tabs.erp-tabs > li {
    display: block !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}

.sales-tabs-page ul.nav.nav-tabs.erp-tabs > li > a {
    display: flex !important;
    width: 100% !important;
    min-width: 0 !important;
    min-height: 44px !important;
    margin: 0 !important;
    padding: 10px 10px !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    border: 0 !important;
    border-radius: 8px !important;
    background: transparent;
    color: #526274;
    font-weight: 800;
    line-height: 1.4;
    text-align: center;
    white-space: normal;
    word-break: break-word;
}

.sales-tabs-page ul.nav.nav-tabs.erp-tabs > li > a:hover {
    background: #e8f0f5;
    color: #2f5968;
}

.sales-tabs-page ul.nav.nav-tabs.erp-tabs > li.active > a,
.sales-tabs-page ul.nav.nav-tabs.erp-tabs > li.active > a:hover,
.sales-tabs-page ul.nav.nav-tabs.erp-tabs > li.active > a:focus {
    background: #2f8191;
    color: #fff;
    box-shadow: 0 4px 11px rgba(47, 129, 145, .24);
}

.sales-tabs-page .tab-count {
    min-width: 24px;
    padding: 2px 7px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .22);
    font-size: 11px;
    text-align: center;
}

.sales-tabs-page .erp-tabs > li:not(.active) .tab-count {
    background: #dfe8ee;
    color: #496071;
}

.sales-tabs-page .erp-tab-content {
    padding: 15px 16px 18px;
}

/* =========================================================
   Summary cards
   ========================================================= */

.sales-tabs-page .erp-summary-grid {
    display: grid;
    gap: 12px;
    width: 100%;
    margin: 0 0 12px;
}

.sales-tabs-page .erp-summary-four {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.sales-tabs-page .erp-summary-three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.sales-tabs-page .erp-summary-cell {
    min-width: 0;
}

.sales-tabs-page .erp-stat-card {
    position: relative;
    height: 100%;
    min-height: 88px;
    margin: 0;
    overflow: hidden;
    padding: 14px 14px 12px;
    border: 1px solid #e6edf3;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 9px rgba(15, 23, 42, .04);
}

.sales-tabs-page .erp-stat-card::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    content: "";
    background: #3c8dbc;
}

.sales-tabs-page .erp-stat-card.success::before { background: #21a366; }
.sales-tabs-page .erp-stat-card.warning::before { background: #f39c12; }
.sales-tabs-page .erp-stat-card.danger::before  { background: #e5533d; }

.sales-tabs-page .erp-stat-label {
    margin-bottom: 6px;
    color: #7a8793;
    font-size: 12px;
    line-height: 1.45;
}

.sales-tabs-page .erp-stat-value {
    padding-right: 38px;
    color: #263238;
    font-size: 20px;
    font-weight: 800;
    line-height: 1.2;
    word-break: break-word;
}

.sales-tabs-page .erp-stat-icon {
    position: absolute;
    right: 14px;
    bottom: 10px;
    color: rgba(0, 0, 0, .09);
    font-size: 30px;
}

/* =========================================================
   Filters
   ========================================================= */

.sales-tabs-page .erp-filter-card {
    margin-bottom: 12px;
    padding: 12px;
    border: 1px solid #e2e9ef;
    border-radius: 10px;
    background: #fbfdff;
}

.sales-tabs-page .erp-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    width: 100%;
}

.sales-tabs-page .erp-filter-cell {
    min-width: 0;
}

.sales-tabs-page .erp-filter-card label,
.sales-tabs-page .erp-payment-panel label,
.sales-tabs-page .erp-field-label {
    display: block;
    min-height: 20px;
    margin-bottom: 5px;
    
    color: #5d6e80;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.5;
}

.sales-tabs-page .erp-filter-card .form-control,
.sales-tabs-page .erp-payment-panel .form-control {
    width: 100%;
    height: 38px;
    border: 1px solid #cfd9e3;
    border-radius: 7px;
    background: #fff;
    box-shadow: none;
}

/* =========================================================
   Select2
   ========================================================= */

.sales-tabs-page .select2-container {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
}

.sales-tabs-page select.erp-select2.select2-hidden-accessible,
.sales-tabs-page select.erp-select2.select2-offscreen {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    border: 0 !important;
    clip: rect(0 0 0 0) !important;
    overflow: hidden !important;
}

/* Select2 v3 */
.sales-tabs-page .select2-container .select2-choice {
    width: 100% !important;
    height: 38px !important;
    min-height: 38px !important;
    padding: 0 34px 0 11px !important;
    border: 1px solid #cfd9e3 !important;
    border-radius: 7px !important;
    background: #fff !important;
    box-shadow: none !important;
    line-height: 36px !important;
}

.sales-tabs-page .select2-container .select2-choice > .select2-chosen {
    line-height: 36px !important;
}

.sales-tabs-page .select2-container .select2-choice .select2-arrow {
    width: 32px !important;
    height: 36px !important;
    border-left: 0 !important;
    background: transparent !important;
}

/* Select2 v4 */
.sales-tabs-page .select2-container--default .select2-selection--single {
    width: 100% !important;
    height: 38px !important;
    min-height: 38px !important;
    border: 1px solid #cfd9e3 !important;
    border-radius: 7px !important;
    background: #fff !important;
    box-shadow: none !important;
}

.sales-tabs-page .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    padding-left: 11px !important;
    padding-right: 34px !important;
    line-height: 36px !important;
}

.sales-tabs-page .select2-container--default
.select2-selection--single
.select2-selection__arrow {
    width: 32px !important;
    height: 36px !important;
}

.select2-drop,
.select2-dropdown,
.select2-container--open {
    z-index: 999999 !important;
}

/* =========================================================
   Payment workspace
   ========================================================= */

.sales-tabs-page .erp-payment-panel {
    margin-top: 13px;
    margin-bottom: 13px;
    padding: 14px;
    border: 1px solid #d7e7ea;
    border-left: 4px solid #2f8191;
    border-radius: 10px;
    background: linear-gradient(135deg, #f6fcfd 0%, #ffffff 100%);
}

.sales-tabs-page .erp-payment-title {
    margin: 0 0 12px;
    color: #294e5b;
    font-size: 15px;
    font-weight: 800;
}

.sales-tabs-page .erp-payment-grid {
    display: grid;
    grid-template-columns:
        minmax(135px, .85fr)
        minmax(190px, 1.15fr)
        minmax(190px, 1.05fr)
        minmax(190px, 1.15fr);
    gap: 12px;
    align-items: end;
    width: 100%;
}

.sales-tabs-page .erp-payment-cell {
    display: flex;
    min-width: 0;
    height: 100%;
    flex-direction: column;
    justify-content: flex-end;
}

.sales-tabs-page .erp-payment-cell .erp-field-label,
.sales-tabs-page .erp-payment-cell label {
    min-height: 20px;
    margin-bottom: 5px;
}

.sales-tabs-page .erp-selected-due {
    min-height: 38px;
    color: #dd4b39;
    font-size: 20px;
    font-weight: 800;
    line-height: 38px;
}

.sales-tabs-page .erp-payment-panel .btn {
    width: 100%;
    min-height: 38px;
    border: 0;
    border-radius: 7px;
    font-weight: 700;
}

.sales-tabs-page #bulk_pay_btn {
    min-height: 38px;
    white-space: normal;
    line-height: 1.45;
}

.sales-tabs-page .erp-help-note {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 8px;
    background: #fff8e8;
    color: #786025;
    font-size: 12px;
    line-height: 1.55;
}

/* =========================================================
   DataTable toolbar
   ========================================================= */

.sales-tabs-page .erp-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    min-height: 60px;
    flex-wrap: wrap;
    padding: 11px 12px;
    border: 1px solid #e2e9ef;
    border-bottom: 0;
    border-radius: 10px 10px 0 0;
    background: #fbfdff;
}

.sales-tabs-page .erp-toolbar-left,
.sales-tabs-page .erp-toolbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    flex-wrap: wrap;
}

.sales-tabs-page .erp-toolbar-right {
    margin-left: auto;
}

.sales-tabs-page .erp-search-box {
    position: relative;
    width: 290px;
    max-width: 100%;
}

.sales-tabs-page .erp-search-box i {
    position: absolute;
    top: 50%;
    left: 12px;
    z-index: 2;
    transform: translateY(-50%);
    color: #94a3b8;
}

.sales-tabs-page .erp-search-box .form-control {
    width: 100%;
    height: 36px;
    padding-left: 36px;
    border: 1px solid #cfd9e3;
    border-radius: 7px;
    box-shadow: none;
}

.sales-tabs-page .erp-toolbar .btn {
    min-height: 36px;
    border-radius: 7px;
}

.sales-tabs-page .dt-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.sales-tabs-page .dt-buttons .btn,
.sales-tabs-page .dt-buttons .dt-button {
    margin: 0 !important;
    padding: 7px 11px !important;
    border: 1px solid #dbe3ed !important;
    border-radius: 7px !important;
    background: #f8fafc !important;
    color: #334155 !important;
    box-shadow: none !important;
    font-size: 12px;
    font-weight: 800;
}

/* =========================================================
   Table
   ========================================================= */

.sales-tabs-page .erp-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    border: 1px solid #e2e9ef;
    border-radius: 0 0 10px 10px;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.sales-tabs-page .dataTables_wrapper,
.sales-tabs-page .dataTables_scroll,
.sales-tabs-page .dataTables_scrollHead,
.sales-tabs-page .dataTables_scrollBody,
.sales-tabs-page .dataTables_scrollFoot {
    width: 100% !important;
    max-width: 100% !important;
}

.sales-tabs-page .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.sales-tabs-page table.dataTable {
    width: 100% !important;
    min-width: 920px !important;
    margin: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0;
}

.sales-tabs-page #customerDueData.dataTable {
    min-width: 1120px !important;
}

.sales-tabs-page table.dataTable thead th {
    padding: 10px 9px !important;
    border-right: 1px solid #edf2f7 !important;
    border-bottom: 1px solid #dfe6ef !important;
    background: #f3f6f9 !important;
    color: #334155;
    font-size: 12px;
    font-weight: 900;
    vertical-align: middle;
    white-space: nowrap;
}

.sales-tabs-page table.dataTable tbody td {
    padding: 11px 9px !important;
    border-right: 1px solid #edf2f7 !important;
    border-bottom: 1px solid #edf2f7 !important;
    color: #334155;
    vertical-align: middle;
    white-space: nowrap;
}

.sales-tabs-page table.dataTable tbody tr:nth-child(even) td {
    background: #fafafa;
}

.sales-tabs-page table.dataTable tbody tr:hover td {
    background: #f1f9f7 !important;
}

.sales-tabs-page table.dataTable tfoot th {
    padding: 8px !important;
    border-top: 1px solid #dfe6ef !important;
    border-right: 1px solid #edf2f7 !important;
    background: #f8fafc !important;
    font-weight: 900;
    white-space: nowrap;
}

.sales-tabs-page .amount-cell,
.sales-tabs-page .amount-total {
    display: block;
    text-align: right;
    font-weight: 800;
}

.sales-tabs-page .sale_status,
.sales-tabs-page .preorder_status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 64px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 900;
}

.sales-tabs-page .label-success { background: #16a34a !important; }
.sales-tabs-page .label-primary { background: #2563eb !important; }
.sales-tabs-page .label-danger  { background: #ef4444 !important; }
.sales-tabs-page .label-warning { background: #f59e0b !important; }

.sales-tabs-page .deliver_preorder {
    margin-left: 6px;
    padding: 4px 9px;
    border-radius: 999px !important;
    font-size: 11px;
    font-weight: 800;
}

.sales-tabs-page .erp-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex-wrap: wrap;
    white-space: nowrap;
}

.sales-tabs-page .sale-check,
.sales-tabs-page #check_all_due {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.sales-tabs-page .erp-scroll-hint {
    display: none;
    margin: 0 0 7px;
    color: #6f7e8d;
    font-size: 12px;
}

.sales-tabs-page .dataTables_info {
    padding-top: 11px !important;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.sales-tabs-page .pagination > li > a,
.sales-tabs-page .pagination > li > span {
    margin-left: 4px;
    border-color: #dbe3ed;
    border-radius: 7px !important;
    color: #334155;
}

.sales-tabs-page .pagination > .active > a,
.sales-tabs-page .pagination > .active > span {
    border-color: #2c7f75 !important;
    background: #2c7f75 !important;
    color: #fff !important;
}

/* Modal */
.sales-tabs-page .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 12px;
    box-shadow: 0 15px 40px rgba(15, 23, 42, .22);
}

.sales-tabs-page .modal-header {
    border-bottom: 1px solid #e8eef5;
    background: #f8fafc;
}

/* =========================================================
   Tablet
   ========================================================= */

@media (max-width: 1024px) {
    .sales-tabs-page .erp-scroll-hint {
        display: block;
    }

    .sales-tabs-page .erp-summary-four {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sales-tabs-page .erp-summary-three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .sales-tabs-page .erp-payment-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* =========================================================
   Mobile compact layout
   ========================================================= */

@media (max-width: 767px) {
    .sales-tabs-page {
        padding: 8px !important;
    }

    .sales-tabs-page .erp-page-header {
        padding: 12px;
    }

    .sales-tabs-page .erp-title-icon {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
        border-radius: 9px;
        font-size: 15px;
    }

    .sales-tabs-page .erp-title-text h3 {
        font-size: 16px;
    }

    .sales-tabs-page .erp-tabs-wrap {
        padding: 8px 8px 0;
    }

    .sales-tabs-page ul.nav.nav-tabs.erp-tabs {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 5px !important;
        padding: 4px !important;
    }

    .sales-tabs-page ul.nav.nav-tabs.erp-tabs > li > a {
        min-height: 40px !important;
        padding: 7px 5px !important;
        gap: 4px !important;
        font-size: 15px !important;
        line-height: 1.3 !important;
    }

    .sales-tabs-page .tab-count {
        min-width: 20px;
        padding: 1px 5px;
        font-size: 9px;
    }

    .sales-tabs-page .erp-tab-content {
        padding: 8px;
    }

    /* Four sales cards = 2 x 2 */
    .sales-tabs-page .erp-summary-four {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 5px !important;
        margin-bottom: 7px !important;
    }

    /* Due cards = 2 + full width last card */
    .sales-tabs-page .erp-summary-three {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 5px !important;
        margin-bottom: 7px !important;
    }

    .sales-tabs-page .erp-summary-three .erp-summary-cell:last-child {
        grid-column: 1 / -1 !important;
    }

    .sales-tabs-page .erp-stat-card {
        min-height: 52px !important;
        height: 52px !important;
        padding: 6px 8px 5px !important;
        border-radius: 7px !important;
        box-shadow: none !important;
    }

    .sales-tabs-page .erp-stat-card::before {
        width: 3px !important;
    }

    .sales-tabs-page .erp-stat-label {
        margin: 0 0 2px !important;
        font-size: 9px !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .sales-tabs-page .erp-stat-value {
        padding: 0 !important;
        font-size: 14px !important;
        line-height: 1.25 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .sales-tabs-page .erp-stat-icon {
        display: none !important;
    }

    /* Keep three filters in one row on mobile */
    .sales-tabs-page .erp-filter-card {
        padding: 8px;
        margin-bottom: 8px;
    }

    .sales-tabs-page .erp-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 6px !important;
    }

    .sales-tabs-page .erp-filter-card label {
        min-height: 16px;
        margin-bottom: 3px;
        font-size: 9px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sales-tabs-page .erp-filter-card .form-control,
    .sales-tabs-page .erp-filter-card .select2-container .select2-choice,
    .sales-tabs-page .erp-filter-card .select2-container--default .select2-selection--single {
        height: 32px !important;
        min-height: 32px !important;
        font-size: 10px !important;
    }

    .sales-tabs-page .erp-filter-card .select2-container .select2-choice,
    .sales-tabs-page .erp-filter-card .select2-container .select2-choice > .select2-chosen,
    .sales-tabs-page .erp-filter-card .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        line-height: 30px !important;
    }

    .sales-tabs-page .erp-filter-card .select2-container .select2-choice .select2-arrow,
    .sales-tabs-page .erp-filter-card .select2-container--default
    .select2-selection--single
    .select2-selection__arrow {
        height: 30px !important;
    }

    /* Due payment section stacked */
    .sales-tabs-page .erp-payment-grid {
        grid-template-columns: 1fr !important;
        gap: 8px !important;
    }

    /* Compact toolbar */
    .sales-tabs-page .erp-toolbar {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(135px, .9fr);
        gap: 6px;
        min-height: 0;
        padding: 7px;
    }

    .sales-tabs-page .erp-toolbar-left,
    .sales-tabs-page .erp-toolbar-right {
        width: 100%;
        min-width: 0;
        min-height: 32px;
        margin: 0;
        flex-wrap: nowrap;
    }

    .sales-tabs-page .erp-toolbar-left {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .sales-tabs-page .dt-buttons {
        flex-wrap: nowrap;
        gap: 3px;
    }

    .sales-tabs-page .dt-buttons .btn,
    .sales-tabs-page .dt-buttons .dt-button {
        flex: 0 0 auto;
        padding: 5px 7px !important;
        font-size: 9px !important;
        white-space: nowrap;
    }

    .sales-tabs-page .erp-toolbar-right {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 5px;
    }

    .sales-tabs-page .erp-search-box {
        width: 100%;
        min-width: 0;
    }

    .sales-tabs-page .erp-search-box .form-control {
        height: 32px;
        padding-left: 28px;
        font-size: 10px;
    }

    .sales-tabs-page .erp-search-box i {
        left: 9px;
        font-size: 10px;
    }

    .sales-tabs-page .erp-toolbar .btn {
        min-height: 32px;
        padding: 5px 7px;
        font-size: 9px;
        white-space: nowrap;
    }

    .sales-tabs-page .erp-scroll-hint {
        margin-bottom: 5px;
        font-size: 9px;
    }

    .sales-tabs-page table.dataTable thead th,
    .sales-tabs-page table.dataTable tbody td,
    .sales-tabs-page table.dataTable tfoot th {
        padding: 7px 6px !important;
        font-size: 15px !important;
    }

    .sales-tabs-page .dataTables_info {
        font-size: 9px;
    }

    .sales-tabs-page .pagination > li > a,
    .sales-tabs-page .pagination > li > span {
        padding: 5px 8px;
        font-size: 10px;
    }
}

@media (max-width: 360px) {
    .sales-tabs-page .erp-stat-value {
        font-size: 12px !important;
    }

    .sales-tabs-page ul.nav.nav-tabs.erp-tabs > li > a {
        font-size: 9px !important;
    }

    .sales-tabs-page .erp-toolbar {
        grid-template-columns: 1fr;
    }
}

/* =========================================================
   Mobile Summary Toggle + Hide DataTable Export Buttons
   ========================================================= */
.sales-tabs-page .mobile-summary-toggle {
    display: none;
}

@media (max-width: 767px) {
    .sales-tabs-page .mobile-summary-toggle {
        display: flex;
        width: 100%;
        min-height: 34px;
        padding: 16px 10px;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        border: 1px solid #dce6ed;
        border-radius: 7px;
        background: #f8fafc;
        color: #45606f;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.3;
        box-shadow: none;
        margin:15px 0px ;
    }

    .sales-tabs-page .mobile-summary-toggle:hover,
    .sales-tabs-page .mobile-summary-toggle:focus {
        border-color: #2f8191;
        background: #eef7f8;
        color: #2f8191;
        outline: 0;
    }

    .sales-tabs-page .mobile-summary-toggle .toggle-left {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .sales-tabs-page .mobile-summary-toggle .toggle-icon {
        flex: 0 0 auto;
    }

    /* Summary cards are collapsed by default on mobile only */
    .sales-tabs-page .erp-summary-grid.mobile-summary-collapsed {
        display: none !important;
    }

    .sales-tabs-page .erp-summary-grid:not(.mobile-summary-collapsed) {
        display: grid !important;
    }

    /* Hide Copy / Excel / CSV / PDF / Columns buttons on mobile */
    .sales-tabs-page .erp-toolbar-left {
        display: none !important;
    }

    /* Search and refresh use the whole toolbar width */
    .sales-tabs-page .erp-toolbar {
        display: block !important;
        min-height: 0 !important;
        padding: 7px !important;
    }

    .sales-tabs-page .erp-toolbar-right {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto !important;
        gap: 5px !important;
        width: 100% !important;
        min-height: 32px !important;
        margin: 0 !important;
    }

    .sales-tabs-page .erp-search-box {
        width: 100% !important;
        min-width: 0 !important;
    }
}

</style>

<script>
var salesTable = null;
var customerDueTable = null;
</script>

<script type="text/javascript">
window.toggleMobileSummary = function (button) {
    if (!button) {
        return false;
    }

    var targetSelector = button.getAttribute('data-target');
    var summary = targetSelector
        ? document.querySelector(targetSelector)
        : null;

    if (!summary) {
        return false;
    }

    var isCollapsed = summary.classList.contains(
        'mobile-summary-collapsed'
    );

    var label = button.querySelector('.toggle-label');
    var icon = button.querySelector('.toggle-icon');

    if (isCollapsed) {
        summary.classList.remove('mobile-summary-collapsed');
        summary.style.setProperty('display', 'grid', 'important');

        button.setAttribute('aria-expanded', 'true');

        if (label) {
            label.textContent = 'အကျဉ်းချုပ်';
        }

        if (icon) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    } else {
        summary.classList.add('mobile-summary-collapsed');
        summary.style.removeProperty('display');

        button.setAttribute('aria-expanded', 'false');

        if (label) {
            label.textContent = 'အကျဉ်းချုပ်';
        }

        if (icon) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }

    window.setTimeout(function () {
        try {
            if (
                window.salesTable &&
                window.salesTable.columns
            ) {
                window.salesTable.columns.adjust();
            }

            if (
                window.customerDueTable &&
                window.customerDueTable.columns
            ) {
                window.customerDueTable.columns.adjust();
            }
        } catch (error) {}
    }, 60);

    return false;
};

$(document).ready(function () {

    function initialiseSalesSelect2(forceRebuild) {
        if (!$.fn.select2) {
            console.error('KLSPOS Sales: Select2 library is not loaded.');
            return;
        }

        $('.sales-tabs-page select.erp-select2').each(function () {
            var $select = $(this);

            var isInitialised =
                !!$select.data('select2') ||
                $select.hasClass('select2-hidden-accessible') ||
                $select.hasClass('select2-offscreen');

            if (isInitialised && !forceRebuild) {
                return;
            }

            if (isInitialised) {
                try {
                    $select.select2('destroy');
                } catch (destroyError) {}
            }

            $select.siblings('.select2-container').remove();

            $select
                .removeClass('select2-hidden-accessible select2-offscreen')
                .removeAttr('data-select2-id')
                .css('width', '100%');

            $select.find('option').removeAttr('data-select2-id');

            try {
                $select.select2({
                    width: '100%',
                    dropdownAutoWidth: true,
                    minimumResultsForSearch: 0
                });
            } catch (select2Error) {
                $select.select2();
                $select.next('.select2-container').css('width', '100%');
            }
        });
    }

    window.setTimeout(function () {
        initialiseSalesSelect2(true);
    }, 100);

    function toNumber(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        if (typeof value === 'number') {
            return value;
        }

        value = value
            .toString()
            .replace(/<[^>]*>/g, '')
            .replace(/,/g, '')
            .replace(/[^0-9.\-]/g, '');

        return parseFloat(value) || 0;
    }

    function moneyText(value) {
        return toNumber(value).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function moneyCell(data, type) {
        if (type !== 'display') {
            return toNumber(data);
        }

        return '<span class="amount-cell">' + moneyText(data) + '</span>';
    }

    function statusBadge(data, type) {
        if (type !== 'display') {
            return data;
        }

        var label = data || '';
        var labelClass = 'label-default';

        if (data === 'paid') {
            label = '<?= lang('paid'); ?>';
            labelClass = 'label-success';
        } else if (data === 'partial') {
            label = '<?= lang('partial'); ?>';
            labelClass = 'label-primary';
        } else if (data === 'due') {
            label = '<?= lang('due'); ?>';
            labelClass = 'label-danger';
        }

        return '<div class="text-center">' +
            '<span class="sale_status label ' + labelClass + '" data-status="' + (data || '') + '">' +
                label +
            '</span>' +
        '</div>';
    }

    function preorderBadge(data, type, row) {
        if (type !== 'display') {
            return toNumber(data) === 1 ? '1' : '0';
        }

        if (toNumber(data) === 1) {
            return '<div class="text-center">' +
                '<span class="preorder_status label label-warning"><?= lang('preorder'); ?></span>' +
                '<button type="button" class="btn btn-xs btn-success deliver_preorder" data-id="' + row.id + '">' +
                    '<i class="fa fa-truck"></i> <?= lang('deliver'); ?>' +
                '</button>' +
            '</div>';
        }

        return '<div class="text-center">' +
            '<span class="preorder_status label label-success"><?= lang('delivered'); ?></span>' +
        '</div>';
    }

    function dueAmount(row) {
        return Math.max(
            toNumber(row.grand_total) - toNumber(row.paid),
            0
        );
    }

    function customerOptionsAreEmpty($select) {
        return $select.find('option').filter(function () {
            return $.trim($(this).val()) !== '';
        }).length === 0;
    }

    function syncCustomerOptions(json) {
        var rows = json.data || json.aaData || [];
        var names = {};

        $.each(rows, function (_, row) {
            var name = $.trim(String(row.customer_name || ''));

            if (name !== '') {
                names[name] = true;
            }
        });

        var customerNames = Object.keys(names).sort(function (a, b) {
            return a.localeCompare(b);
        });

        $('#sales_customer_filter, #due_customer_filter').each(function () {
            var $select = $(this);

            if (!customerOptionsAreEmpty($select)) {
                return;
            }

            $.each(customerNames, function (_, name) {
                $select.append(
                    $('<option>', {
                        value: name,
                        text: name
                    })
                );
            });

            try {
                if ($select.data('select2')) {
                    $select.trigger('change.select2');
                } else {
                    initialiseSalesSelect2(false);
                }
            } catch (error) {
                initialiseSalesSelect2(false);
            }
        });
    }

    function updateSalesSummary(api) {
        var rows = api.rows({search: 'applied'}).data();
        var grandTotal = 0;
        var paidTotal = 0;
        var dueTotal = 0;

        rows.each(function (row) {
            grandTotal += toNumber(row.grand_total);
            paidTotal += toNumber(row.paid);
            dueTotal += dueAmount(row);
        });

        $('#sales_records').text(rows.length);
        $('#sales_tab_count').text(rows.length);
        $('#sales_grand_total').text(moneyText(grandTotal));
        $('#sales_paid_total').text(moneyText(paidTotal));
        $('#sales_due_total').text(moneyText(dueTotal));
    }

    function updateDueSummary(api) {
        var rows = api.rows({search: 'applied'}).data();
        var dueTotal = 0;

        rows.each(function (row) {
            dueTotal += dueAmount(row);
        });

        $('#due_sales_records').text(rows.length);
        $('#due_tab_count').text(rows.length);
        $('#customer_due_total').text(moneyText(dueTotal));
    }

    function updateSelectedDue() {
        var totalDue = 0;
        var selectedCount = 0;

        $('#customerDueData .sale-check:checked').each(function () {
            var rowData = customerDueTable.row($(this).closest('tr')).data();

            if (rowData) {
                totalDue += dueAmount(rowData);
                selectedCount++;
            }
        });

        $('#selected_due_total').text(moneyText(totalDue));
        $('#selected_sale_count').text(selectedCount);
    }

    salesTable = $('#SLData').DataTable({
        dom: 'Brtip',
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        responsive: false,
        ajax: {
            url: '<?= site_url('sales/get_sales'); ?>',
            type: 'POST',
            data: function (data) {
                data.<?= $this->security->get_csrf_token_name(); ?> =
                    '<?= $this->security->get_csrf_hash(); ?>';

                data.customer_filter = $('#sales_customer_filter').val();
                data.status_filter = $('#sales_status_filter').val();
                data.preorder_filter = $('#sales_preorder_filter').val();
            },
            dataSrc: function (json) {
                syncCustomerOptions(json);

                var rows = json && (json.data || json.aaData)
                    ? (json.data || json.aaData)
                    : [];

                return $.isArray(rows) ? rows : [];
            },
            error: function (xhr, status, error) {
                console.error(
                    'Sales DataTable AJAX error:',
                    status,
                    error,
                    xhr.responseText
                );

                $('#SLData tbody').html(
                    '<tr>' +
                        '<td colspan="8" class="text-center text-danger">' +
                            '<i class="fa fa-exclamation-triangle"></i> ' +
                            'Sales data could not be loaded.' +
                        '</td>' +
                    '</tr>'
                );
            }
        },
        buttons: [
            {
                extend: 'copyHtml5',
                text: '<?= lang('copy'); ?>',
                footer: true,
                exportOptions: {columns: ':visible:not(:last-child)'}
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                footer: true,
                exportOptions: {columns: ':visible:not(:last-child)'}
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                footer: true,
                exportOptions: {columns: ':visible:not(:last-child)'}
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {columns: ':visible:not(:last-child)'}
            },
            {
                extend: 'colvis',
                text: '<?= lang('columns'); ?>',
                columns: [0,1,2,3,4,5,6]
            }
        ],
        columns: [
            {data: 'id', className: 'text-center'},
            {data: 'date', render: hrld},
            {data: 'customer_name'},
            {data: 'grand_total', render: moneyCell, className: 'text-right'},
            {data: 'paid', render: moneyCell, className: 'text-right'},
            {data: 'status', render: statusBadge, className: 'text-center'},
            {data: 'is_preorder', render: preorderBadge, className: 'text-center'},
            {
                data: 'Actions',
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function (data) {
                    return '<div class="erp-actions">' + (data || '') + '</div>';
                }
            }
        ],
        order: [[0, 'desc']],
        footerCallback: function () {
            var api = this.api();

            [3,4].forEach(function (columnIndex) {
                var total = api
                    .column(columnIndex, {search: 'applied'})
                    .data()
                    .reduce(function (a, b) {
                        return toNumber(a) + toNumber(b);
                    }, 0);

                $(api.column(columnIndex).footer()).html(
                    '<span class="amount-total">' + moneyText(total) + '</span>'
                );
            });

            updateSalesSummary(api);
        },
        drawCallback: function () {
            $('li[data-edit-visible]').each(function () {
                if ($(this).attr('data-edit-visible') == 1) {
                    $(this).hide();
                }
            });
        },
        initComplete: function () {
            salesTable.buttons().container().appendTo('#salesDataButtons');
            salesTable.columns.adjust();
        }
    });

    customerDueTable = $('#customerDueData').DataTable({
        dom: 'Brtip',
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        responsive: false,
        ajax: {
            url: '<?= site_url('sales/get_sales'); ?>',
            type: 'POST',
            data: function (data) {
                data.<?= $this->security->get_csrf_token_name(); ?> =
                    '<?= $this->security->get_csrf_hash(); ?>';

                data.customer_filter = $('#due_customer_filter').val();
                data.status_filter = 'partial|due';
                data.preorder_filter = '';
            },
            dataSrc: function (json) {
                syncCustomerOptions(json);

                var rows = json && (json.data || json.aaData)
                    ? (json.data || json.aaData)
                    : [];

                if (!$.isArray(rows)) {
                    rows = [];
                }

                return $.grep(rows, function (row) {
                    return row.status === 'partial' || row.status === 'due';
                });
            },
            error: function (xhr, status, error) {
                console.error(
                    'Customer Due DataTable AJAX error:',
                    status,
                    error,
                    xhr.responseText
                );

                $('#customerDueData tbody').html(
                    '<tr>' +
                        '<td colspan="9" class="text-center text-danger">' +
                            '<i class="fa fa-exclamation-triangle"></i> ' +
                            'Customer due data could not be loaded.' +
                        '</td>' +
                    '</tr>'
                );
            }
        },
        buttons: [
            {
                extend: 'copyHtml5',
                text: '<?= lang('copy'); ?>',
                footer: true,
                exportOptions: {columns: [1,2,3,4,5,6,7,8]}
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                footer: true,
                exportOptions: {columns: [1,2,3,4,5,6,7,8]}
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                footer: true,
                exportOptions: {columns: [1,2,3,4,5,6,7,8]}
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {columns: [1,2,3,4,5,6,7,8]}
            }
        ],
        columns: [
            {
                data: 'id',
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function (id) {
                    return '<input type="checkbox" class="sale-check" value="' + id + '">';
                }
            },
            {data: 'id', className: 'text-center'},
            {data: 'date', render: hrld},
            {data: 'customer_name'},
            {data: 'grand_total', render: moneyCell, className: 'text-right'},
            {data: 'paid', render: moneyCell, className: 'text-right'},
            {
                data: null,
                render: function (data, type, row) {
                    var due = dueAmount(row);

                    if (type !== 'display') {
                        return due;
                    }

                    return '<span class="amount-cell">' + moneyText(due) + '</span>';
                },
                className: 'text-right'
            },
            {data: 'status', render: statusBadge, className: 'text-center'},
            {
                data: 'Actions',
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function (data) {
                    return '<div class="erp-actions">' + (data || '') + '</div>';
                }
            }
        ],
        order: [[1, 'desc']],
        footerCallback: function () {
            var api = this.api();
            var grandTotal = 0;
            var paidTotal = 0;
            var dueTotal = 0;

            api.rows({search: 'applied'}).data().each(function (row) {
                grandTotal += toNumber(row.grand_total);
                paidTotal += toNumber(row.paid);
                dueTotal += dueAmount(row);
            });

            $(api.column(4).footer()).html(
                '<span class="amount-total">' + moneyText(grandTotal) + '</span>'
            );

            $(api.column(5).footer()).html(
                '<span class="amount-total">' + moneyText(paidTotal) + '</span>'
            );

            $(api.column(6).footer()).html(
                '<span class="amount-total">' + moneyText(dueTotal) + '</span>'
            );

            updateDueSummary(api);
        },
        drawCallback: function () {
            $('#check_all_due').prop('checked', false);
            updateSelectedDue();
        },
        initComplete: function () {
            customerDueTable.buttons().container().appendTo('#dueDataButtons');
            customerDueTable.columns.adjust();
        }
    });

    $('#sales_search').on('keyup change', function (event) {
        var code = event.keyCode || event.which;

        if (
            (code == 13 && salesTable.search() !== this.value) ||
            (salesTable.search() !== '' && this.value === '')
        ) {
            salesTable.search(this.value).draw();
        }
    });

    $('#due_search').on('keyup change', function (event) {
        var code = event.keyCode || event.which;

        if (
            (code == 13 && customerDueTable.search() !== this.value) ||
            (customerDueTable.search() !== '' && this.value === '')
        ) {
            customerDueTable.search(this.value).draw();
        }
    });

    $('#sales_customer_filter, #sales_status_filter, #sales_preorder_filter')
        .on('change', function () {
            salesTable.ajax.reload();
        });

    $('#due_customer_filter').on('change', function () {
        customerDueTable.ajax.reload();
    });

    $('#refresh_sales').on('click', function () {
        salesTable.ajax.reload(null, false);
    });

    $('#refresh_due').on('click', function () {
        customerDueTable.ajax.reload(null, false);
    });

    $('#check_all_due').on('change', function () {
        $('input.sale-check', customerDueTable.rows({search: 'applied'}).nodes())
            .prop('checked', this.checked);

        updateSelectedDue();
    });

    $('#customerDueData').on('change', '.sale-check', function () {
        updateSelectedDue();
    });

    $('#bulk_pay_btn').on('click', function () {
        var amount = toNumber($('#bulk_pay_amount').val());
        var saleIds = [];

        if (!amount || amount <= 0) {
            alert('<?= lang('enter_valid_payment_amount'); ?>');
            return;
        }

        $('#customerDueData .sale-check:checked').each(function () {
            var rowData = customerDueTable.row($(this).closest('tr')).data();

            if (rowData && rowData.status !== 'paid') {
                saleIds.push($(this).val());
            }
        });

        if (saleIds.length === 0) {
            alert('<?= lang('select_at_least_one_sale'); ?>');
            return;
        }

        if (!confirm('<?= lang('confirm_apply_payment_selected_sales'); ?>')) {
            return;
        }

        $.ajax({
            url: '<?= site_url('sales/bulk_due_payment'); ?>',
            type: 'POST',
            data: {
                sale_ids: saleIds,
                amount: amount,
                '<?= $this->security->get_csrf_token_name(); ?>':
                    '<?= $this->security->get_csrf_hash(); ?>'
            },
            success: function () {
                alert('<?= lang('payment_completed'); ?>');
                $('#bulk_pay_amount').val('');
                $('#check_all_due').prop('checked', false);
                customerDueTable.ajax.reload(null, false);
                salesTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseText || '<?= lang('payment_failed'); ?>');
            }
        });
    });

    $(document).on('click', '.deliver_preorder', function () {
        var saleId = $(this).data('id');

        if (!saleId) {
            return;
        }

        if (!confirm('<?= lang('confirm_deliver_preorder'); ?>')) {
            return;
        }

        $.ajax({
            url: '<?= site_url('sales/deliver_preorder'); ?>',
            type: 'POST',
            data: {
                sale_id: saleId,
                '<?= $this->security->get_csrf_token_name(); ?>':
                    '<?= $this->security->get_csrf_hash(); ?>'
            },
            success: function () {
                alert('<?= lang('preorder_delivered_successfully'); ?>');
                salesTable.ajax.reload(null, false);
                customerDueTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseText || '<?= lang('delivery_failed'); ?>');
            }
        });
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        initialiseSalesSelect2(false);

        window.setTimeout(function () {
            if (salesTable) {
                salesTable.columns.adjust().draw(false);
            }

            if (customerDueTable) {
                customerDueTable.columns.adjust().draw(false);
            }
        }, 90);
    });


    $(window).on('resize orientationchange', function () {
        window.setTimeout(function () {
            if (salesTable) {
                salesTable.columns.adjust();
            }

            if (customerDueTable) {
                customerDueTable.columns.adjust();
            }
        }, 130);
    });
});
</script>

<section class="content sales-tabs-page">
    <div class="erp-shell">

        <div class="erp-page-header" style="display:none;">
            <div class="erp-title-wrap">
                <div class="erp-title-icon">
                    <i class="fa fa-list-alt"></i>
                </div>

                <div class="erp-title-text">
                    <h3><?= lang('list_sales'); ?></h3>
                </div>
            </div>

            <div class="erp-header-actions">&nbsp;</div>
        </div>

        <div class="erp-tabs-wrap">
            <ul class="nav nav-tabs erp-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a
                        href="#sales-list-tab"
                        aria-controls="sales-list-tab"
                        role="tab"
                        data-toggle="tab"
                    >
                        
                        <?= lang('sales_list_tab'); ?>
                        
                    </a>
                </li>

                <li role="presentation">
                    <a
                        href="#customer-due-tab"
                        aria-controls="customer-due-tab"
                        role="tab"
                        data-toggle="tab"
                    >
                        
                        <?= lang('customer_due_payment'); ?>
                        
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content erp-tab-content">

            <!-- Sales List Tab -->
            <div role="tabpanel" class="tab-pane active" id="sales-list-tab">

                <button
                    type="button"
                    class="mobile-summary-toggle"
                    data-target="#salesSummaryGrid"
                    aria-expanded="false"
                    onclick="return toggleMobileSummary(this);"
                >
                    <span class="toggle-left">
                        <i class="fa fa-bar-chart"></i>
                        <span class="toggle-label">အကျဉ်းချုပ်</span>
                    </span>
                    <i class="fa fa-chevron-down toggle-icon"></i>
                </button>

                <div
                    id="salesSummaryGrid"
                    class="erp-summary-grid erp-summary-four mobile-summary-collapsed"
                >
                    <div class="erp-summary-cell">
                        <div class="erp-stat-card">
                            <div class="erp-stat-label"><?= lang('sales_records'); ?></div>
                            <div class="erp-stat-value" id="sales_records">0</div>
                            <i class="fa fa-list erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card">
                            <div class="erp-stat-label"><?= lang('sales_grand_total'); ?></div>
                            <div class="erp-stat-value" id="sales_grand_total">0.00</div>
                            <i class="fa fa-calculator erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card success">
                            <div class="erp-stat-label"><?= lang('sales_paid_total'); ?></div>
                            <div class="erp-stat-value" id="sales_paid_total">0.00</div>
                            <i class="fa fa-check-circle erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card danger">
                            <div class="erp-stat-label"><?= lang('sales_due_total'); ?></div>
                            <div class="erp-stat-value" id="sales_due_total">0.00</div>
                            <i class="fa fa-warning erp-stat-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="erp-filter-card">
                    <div class="erp-filter-grid">
                        <div class="erp-filter-cell">
                            <label for="sales_customer_filter"><?= lang('customer'); ?></label>

                            <select
                                id="sales_customer_filter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= lang('all_customers'); ?></option>

                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <?php if (isset($customer->name) && trim($customer->name) !== ''): ?>
                                            <option value="<?= html_escape($customer->name); ?>">
                                                <?= html_escape($customer->name); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="erp-filter-cell">
                            <label for="sales_status_filter"><?= lang('status'); ?></label>

                            <select
                                id="sales_status_filter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= lang('all_statuses'); ?></option>
                                <option value="paid"><?= lang('paid'); ?></option>
                                <option value="partial"><?= lang('partial'); ?></option>
                                <option value="due"><?= lang('due'); ?></option>
                                <option value="partial|due"><?= lang('partial_and_due'); ?></option>
                            </select>
                        </div>

                        <div class="erp-filter-cell">
                            <label for="sales_preorder_filter"><?= lang('is_preorder'); ?></label>

                            <select
                                id="sales_preorder_filter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= lang('all_preorders'); ?></option>
                                <option value="1"><?= lang('preorder'); ?></option>
                                <option value="0"><?= lang('delivered'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <p class="erp-scroll-hint">
                    <i class="fa fa-arrows-h"></i>
                    <?= lang('swipe_table_horizontal'); ?>
                </p>

                <div class="erp-toolbar">
                    <div class="erp-toolbar-left" id="salesDataButtons"></div>

                    <div class="erp-toolbar-right">
                        <div class="erp-search-box">
                            <i class="fa fa-search"></i>

                            <input
                                type="text"
                                id="sales_search"
                                class="form-control"
                                placeholder="<?= lang('type_hit_enter'); ?>"
                            >
                        </div>

                        <button type="button" id="refresh_sales" class="btn btn-default">
                            <i class="fa fa-refresh"></i>
                            <?= lang('refresh'); ?>
                        </button>
                    </div>
                </div>

                <div class="erp-table-wrap">
                    <table
                        id="SLData"
                        class="table table-striped table-bordered table-hover"
                        style="width:100%;"
                    >
                        <thead>
                            <tr>
                                <th><?= lang('id'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('customer'); ?></th>
                                <th class="text-right"><?= lang('grand_total'); ?></th>
                                <th class="text-right"><?= lang('paid'); ?></th>
                                <th><?= lang('status'); ?></th>
                                <th><?= lang('is_preorder'); ?></th>
                                <th class="text-center"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= lang('total'); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Customer Due Payment Tab -->
            <div role="tabpanel" class="tab-pane" id="customer-due-tab">

                <button
                    type="button"
                    class="mobile-summary-toggle"
                    data-target="#dueSummaryGrid"
                    aria-expanded="false"
                    onclick="return toggleMobileSummary(this);"
                >
                    <span class="toggle-left">
                        <i class="fa fa-bar-chart"></i>
                        <span class="toggle-label">အကျဉ်းချုပ်</span>
                    </span>
                    <i class="fa fa-chevron-down toggle-icon"></i>
                </button>

                <div
                    id="dueSummaryGrid"
                    class="erp-summary-grid erp-summary-three mobile-summary-collapsed"
                >
                    <div class="erp-summary-cell">
                        <div class="erp-stat-card warning">
                            <div class="erp-stat-label"><?= lang('outstanding_sales'); ?></div>
                            <div class="erp-stat-value" id="due_sales_records">0</div>
                            <i class="fa fa-file-text-o erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card danger">
                            <div class="erp-stat-label"><?= lang('total_customer_due'); ?></div>
                            <div class="erp-stat-value" id="customer_due_total">0.00</div>
                            <i class="fa fa-warning erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card success">
                            <div class="erp-stat-label"><?= lang('selected_sale_count'); ?></div>
                            <div class="erp-stat-value" id="selected_sale_count">0</div>
                            <i class="fa fa-check-square-o erp-stat-icon"></i>
                        </div>
                    </div>
                </div>

                

                

                

                <div class="erp-toolbar">
                    <div class="erp-toolbar-left" id="dueDataButtons"></div>

                    <div class="erp-toolbar-right">
                        <div class="erp-search-box">
                            <i class="fa fa-search"></i>

                            <input
                                type="text"
                                id="due_search"
                                class="form-control"
                                placeholder="<?= lang('type_hit_enter'); ?>"
                            >
                        </div>

                        <button type="button" id="refresh_due" class="btn btn-default">
                            <i class="fa fa-refresh"></i>
                            <?= lang('refresh'); ?>
                        </button>
                    </div>
                </div>

                <div class="erp-table-wrap">
                    <table
                        id="customerDueData"
                        class="table table-striped table-bordered table-hover"
                        style="width:100%;"
                    >
                        <thead>
                            <tr>
                                <th class="text-center" style="width:38px;">
                                    <input type="checkbox" id="check_all_due">
                                </th>
                                <th><?= lang('id'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('customer'); ?></th>
                                <th class="text-right"><?= lang('grand_total'); ?></th>
                                <th class="text-right"><?= lang('paid'); ?></th>
                                <th class="text-right"><?= lang('due'); ?></th>
                                <th><?= lang('status'); ?></th>
                                <th class="text-center"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= lang('total'); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="erp-payment-panel">
                    <h4 class="erp-payment-title">
                        <i class="fa fa-money"></i>
                        <?= lang('customer_due_payment'); ?>
                    </h4>

                    <div class="erp-payment-grid">
                        <div class="erp-payment-cell">
                            <span class="erp-field-label"><?= lang('selected_due'); ?></span>
                            <div class="erp-selected-due" id="selected_due_total">0.00</div>
                        </div>

                        <div class="erp-payment-cell">
                            <label for="bulk_pay_amount"><?= lang('payment_amount'); ?></label>

                            <input
                                type="number"
                                id="bulk_pay_amount"
                                class="form-control"
                                min="0"
                                step="0.01"
                                placeholder="<?= lang('payment_amount'); ?>"
                            >
                        </div>

                        <div class="erp-payment-cell">
                            <span class="erp-field-label">&nbsp;</span>

                            <button
                                type="button"
                                id="bulk_pay_btn"
                                class="btn btn-success btn-block"
                            >
                                <i class="fa fa-money"></i>
                                <?= lang('pay_selected'); ?>
                            </button>
                        </div>

                        <div class="erp-payment-cell">
                            <label for="due_customer_filter"><?= lang('customer'); ?></label>

                            <select
                                id="due_customer_filter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= lang('all_customers'); ?></option>

                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <?php if (isset($customer->name) && trim($customer->name) !== ''): ?>
                                            <option value="<?= html_escape($customer->name); ?>">
                                                <?= html_escape($customer->name); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($Admin) { ?>
        <div class="modal fade" id="stModal" tabindex="-1" role="dialog" aria-labelledby="stModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="<?= lang('close'); ?>"
                        >
                            <span aria-hidden="true">
                                <i class="fa fa-times"></i>
                            </span>
                        </button>

                        <h4 class="modal-title" id="stModalLabel">
                            <?= lang('update_status'); ?>
                            <span id="status-id"></span>
                        </h4>
                    </div>

                    <?= form_open('sales/status'); ?>

                    <div class="modal-body">
                        <input type="hidden" id="sale_id" name="sale_id" value="">

                        <div class="form-group form-group-lg">
                            <?= lang('status', 'status'); ?>

                            <?php
                            $status_options = [
                                'paid'    => lang('paid'),
                                'partial' => lang('partial'),
                                'due'     => lang('due')
                            ];
                            ?>

                            <?= form_dropdown(
                                'status',
                                $status_options,
                                set_value('status'),
                                'class="form-control select2 tip" id="status" required="required" style="width:100%;"'
                            ); ?>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-default"
                            data-dismiss="modal"
                        >
                            <?= lang('close'); ?>
                        </button>

                        <button type="submit" class="btn btn-primary">
                            <?= lang('update'); ?>
                        </button>
                    </div>

                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    <?php } ?>
</section>