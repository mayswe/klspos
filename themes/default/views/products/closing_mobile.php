<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Closing Balance mobile/app mode.
 *
 * app=1 and app_lang are preserved after Filter Submit and Reset,
 * so the page stays in the mobile view.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$app_language =
    $this->input->get('app_lang', true) ?:
    $this->input->post('app_lang', true);

$app_query_data = [];

if ($is_app_mode) {
    $app_query_data['app'] = 1;
}

if (!empty($app_language)) {
    $app_query_data['app_lang'] = $app_language;
}

$app_query = !empty($app_query_data)
    ? '?' . http_build_query($app_query_data)
    : '';

$closing_form_action =
    site_url('products/closing') . $app_query;

$closing_reset_url =
    site_url('products/closing') . $app_query;
?>


<?php
$filter_date = $this->input->post('date', true)
    ? $this->input->post('date', true)
    : date('Y-m-d');

$filter_store_id = $this->input->post('store_id', true)
    ? $this->input->post('store_id', true)
    : '';

$query_params = [
    'v' => 1,
    'date' => $filter_date,
];

if ($filter_store_id !== '') {
    $query_params['store_id'] = $filter_store_id;
}

$closing_ajax_url = site_url('products/get_closing_balances')
    . '?' . http_build_query($query_params);
?>

<style>
/* =========================================================
   KLSPOS - Closing Balance | Mobile ERP
   ========================================================= */
.closing-erp {
    --erp-primary: #2f8191;
    --erp-primary-dark: #276b78;
    --erp-blue: #3b8fc0;
    --erp-green: #22a565;
    --erp-orange: #ef9a27;
    --erp-bg: #f4f7fb;
    --erp-card: #ffffff;
    --erp-border: #dfe6ee;
    --erp-text: #263548;
    --erp-muted: #718096;

    padding: 14px !important;
    background: var(--erp-bg);
    color: var(--erp-text);
    font-family: "Noto Sans Myanmar", "Pyidaungsu", "Myanmar Text",
                 "Noto Sans", Arial, sans-serif;
}

.closing-erp .closing-shell {
    max-width: 1450px;
    margin: 0 auto;
    overflow: visible;
    border: 1px solid var(--erp-border);
    border-radius: 14px;
    background: var(--erp-card);
    box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
}

/* Header */
.closing-erp .erp-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 17px 18px;
    border-bottom: 1px solid var(--erp-border);
    border-radius: 14px 14px 0 0;
    background: linear-gradient(135deg, #ffffff 0%, #f5fafb 100%);
}

.closing-erp .erp-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.closing-erp .erp-title-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    flex: 0 0 43px;
    border-radius: 11px;
    background: #e8f6f7;
    color: var(--erp-primary);
    font-size: 19px;
}

.closing-erp .erp-page-title {
    margin: 0;
    color: var(--erp-text);
    font-size: 20px;
    font-weight: 800;
    line-height: 1.45;
}

.closing-erp .erp-page-subtitle {
    margin: 2px 0 0;
    color: var(--erp-muted);
    font-size: 12px;
    line-height: 1.55;
}

.closing-erp .erp-header-actions .btn {
    min-height: 38px;
    border: 1px solid #d7e0e8;
    border-radius: 8px;
    background: #fff;
    color: #425466;
    font-weight: 800;
}

.closing-erp .erp-header-actions .btn.active {
    border-color: #b8d6db;
    background: #e8f6f7;
    color: var(--erp-primary-dark);
}

.closing-erp .closing-content {
    padding: 15px 16px 18px;
}

/* Filter */
.closing-erp .closing-filter-card {
    position: relative;
    z-index: 60;
    margin-bottom: 13px;
    overflow: visible !important;
    border: 1px solid #dfe8ef;
    border-radius: 11px;
    background: #fbfdff;
}

.closing-erp .closing-filter-card.picker-open {
    z-index: 99990;
}

.closing-erp .closing-filter-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 14px;
    border-bottom: 1px solid #e5ebf1;
    border-radius: 11px 11px 0 0;
    background: #f6f9fb;
    color: #33465c;
    font-size: 14px;
    font-weight: 800;
}

.closing-erp .closing-filter-header i {
    color: var(--erp-primary);
}

.closing-erp .closing-filter-body {
    position: relative;
    overflow: visible !important;
    padding: 14px;
}

.closing-erp .closing-filter-grid {
    display: grid;
    grid-template-columns: minmax(160px, .85fr) minmax(220px, 1.35fr) minmax(150px, .75fr);
    gap: 12px;
    align-items: end;
}

.closing-erp .erp-field {
    min-width: 0;
}

.closing-erp label {
    display: block;
    min-height: 20px;
    margin-bottom: 5px;
    color: #516173;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.5;
}

.closing-erp .form-control {
    width: 100%;
    height: 40px;
    border: 1px solid #cfd9e3;
    border-radius: 7px;
    background: #fff;
    color: var(--erp-text);
    box-shadow: none;
}

.closing-erp .form-control:focus {
    border-color: var(--erp-primary);
    box-shadow: 0 0 0 3px rgba(47, 129, 145, .10);
}

.closing-erp .filter-submit {
    min-height: 40px;
    border: 0;
    border-radius: 7px;
    background: var(--erp-primary);
    font-weight: 800;
}

.closing-erp .filter-submit:hover,
.closing-erp .filter-submit:focus {
    background: var(--erp-primary-dark);
}

/* Native ERP date picker */
.closing-erp .erp-date-picker {
    position: relative;
    display: flex;
    width: 100%;
}

.closing-erp .erp-date-picker input[type="date"] {
    width: 100%;
    height: 40px;
    padding: 7px 44px 7px 11px;
    border: 1px solid #cfd9e3;
    border-radius: 7px;
    background: #fff;
    color: var(--erp-text);
    box-shadow: none;
    font-size: 13px;
}

.closing-erp .erp-date-picker input[type="date"]:focus {
    border-color: var(--erp-primary);
    outline: 0;
    box-shadow: 0 0 0 3px rgba(47, 129, 145, .10);
}

.closing-erp .erp-date-picker-button {
    position: absolute;
    top: 1px;
    right: 1px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 38px;
    padding: 0;
    border: 0;
    border-left: 1px solid #e1e7ed;
    border-radius: 0 6px 6px 0;
    background: #f8fafc;
    color: var(--erp-primary);
    cursor: pointer;
}

.closing-erp .erp-date-picker-button:hover,
.closing-erp .erp-date-picker-button:focus {
    background: #e8f6f7;
    color: var(--erp-primary-dark);
    outline: 0;
}

.closing-erp .erp-date-picker input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
}

/* Select2 */
.closing-erp .select2-container {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
}

.closing-erp select.erp-select2.select2-hidden-accessible,
.closing-erp select.erp-select2.select2-offscreen {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    border: 0 !important;
    clip: rect(0 0 0 0) !important;
    overflow: hidden !important;
}

/* Select2 v3 */
.closing-erp .select2-container .select2-choice {
    width: 100% !important;
    height: 40px !important;
    min-height: 40px !important;
    padding: 0 36px 0 11px !important;
    border: 1px solid #cfd9e3 !important;
    border-radius: 7px !important;
    background: #fff !important;
    box-shadow: none !important;
    line-height: 38px !important;
}

.closing-erp .select2-container .select2-choice > .select2-chosen {
    line-height: 38px !important;
}

.closing-erp .select2-container .select2-choice .select2-arrow {
    width: 34px !important;
    height: 38px !important;
    border-left: 0 !important;
    background: transparent !important;
}

/* Select2 v4 */
.closing-erp .select2-container--default .select2-selection--single {
    width: 100% !important;
    height: 40px !important;
    min-height: 40px !important;
    border: 1px solid #cfd9e3 !important;
    border-radius: 7px !important;
    background: #fff !important;
    box-shadow: none !important;
}

.closing-erp .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    padding-left: 11px !important;
    padding-right: 36px !important;
    line-height: 38px !important;
}

.closing-erp .select2-container--default
.select2-selection--single
.select2-selection__arrow {
    width: 34px !important;
    height: 38px !important;
}

.closing-erp .select2-container-active .select2-choice,
.closing-erp .select2-container--focus .select2-selection--single,
.closing-erp .select2-container--open .select2-selection--single {
    border-color: var(--erp-primary) !important;
    box-shadow: 0 0 0 3px rgba(47, 129, 145, .10) !important;
}

.select2-drop,
.select2-dropdown,
.select2-container--open {
    z-index: 999999 !important;
}

/* Summary */
.closing-erp .closing-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 13px;
}

.closing-erp .erp-summary-card {
    position: relative;
    min-height: 88px;
    overflow: hidden;
    padding: 14px 15px;
    border-radius: 10px;
    color: #fff;
    box-shadow: 0 4px 12px rgba(15, 23, 42, .10);
}

.closing-erp .erp-summary-card:after {
    position: absolute;
    right: -20px;
    bottom: -24px;
    width: 92px;
    height: 92px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
    content: "";
}

.closing-erp .erp-summary-label {
    position: relative;
    z-index: 2;
    opacity: .92;
    font-size: 12px;
    line-height: 1.45;
}

.closing-erp .erp-summary-value {
    position: relative;
    z-index: 2;
    margin-top: 7px;
    font-size: 22px;
    font-weight: 900;
    line-height: 1.15;
    word-break: break-word;
}

.closing-erp .erp-card-blue {
    background: linear-gradient(135deg, #3b8fc0, #347fac);
}

.closing-erp .erp-card-green {
    background: linear-gradient(135deg, #22a565, #1b9258);
}

.closing-erp .erp-card-orange {
    background: linear-gradient(135deg, #ef9a27, #db8617);
}

/* Table card */
.closing-erp .closing-table-card {
    position: relative;
    z-index: 1;
    overflow: hidden;
    border: 1px solid #dfe8ef;
    border-radius: 11px;
    background: #fff;
}

.closing-erp .closing-table-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    padding: 12px 14px;
    border-bottom: 1px solid #e5ebf1;
    background: #f8fafc;
}

.closing-erp .closing-table-title {
    margin: 0;
    color: #304257;
    font-size: 15px;
    font-weight: 800;
}

.closing-erp .closing-table-title i {
    margin-right: 5px;
    color: var(--erp-primary);
}

.closing-erp .closing-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    padding: 10px 12px;
    border-bottom: 1px solid #e5ebf1;
    background: #fbfdff;
}

.closing-erp .closing-toolbar-left,
.closing-erp .closing-toolbar-right {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.closing-erp .closing-search {
    position: relative;
    width: 290px;
    max-width: 100%;
}

.closing-erp .closing-search i {
    position: absolute;
    top: 50%;
    left: 12px;
    z-index: 2;
    transform: translateY(-50%);
    color: #94a3b8;
}

.closing-erp .closing-search .form-control {
    height: 36px;
    padding-left: 36px;
}

.closing-erp .dt-buttons .btn,
.closing-erp .dt-buttons .dt-button {
    margin-right: 4px !important;
    margin-bottom: 3px !important;
    padding: 7px 10px !important;
    border: 1px solid #dbe3ed !important;
    border-radius: 7px !important;
    background: #fff !important;
    color: #334155 !important;
    box-shadow: none !important;
    font-size: 12px;
    font-weight: 800;
}

.closing-erp .closing-scroll-hint {
    display: none;
    margin: 0;
    padding: 8px 12px 0;
    color: #6f7e8d;
    font-size: 12px;
}

.closing-erp .closing-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.closing-erp .dataTables_wrapper,
.closing-erp .dataTables_scroll,
.closing-erp .dataTables_scrollHead,
.closing-erp .dataTables_scrollBody,
.closing-erp .dataTables_scrollFoot {
    width: 100% !important;
    max-width: 100% !important;
}

.closing-erp .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.closing-erp table.dataTable {
    width: 100% !important;
    min-width: 980px !important;
    margin: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0;
}

.closing-erp table.dataTable thead th {
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

.closing-erp table.dataTable tbody td {
    padding: 10px 9px !important;
    border-right: 1px solid #edf2f7 !important;
    border-bottom: 1px solid #edf2f7 !important;
    color: #334155;
    vertical-align: middle;
    white-space: nowrap;
}

.closing-erp table.dataTable tbody tr:nth-child(even) td {
    background: #fafafa;
}

.closing-erp table.dataTable tbody tr:hover td {
    background: #f1f9f7 !important;
}

.closing-erp table.dataTable tfoot th {
    padding: 8px !important;
    border-top: 1px solid #dfe6ef !important;
    border-right: 1px solid #edf2f7 !important;
    background: #f8fafc !important;
    vertical-align: middle;
    white-space: nowrap;
}

.closing-erp table.dataTable tfoot input.text_filter {
    width: 100%;
    height: 31px;
    padding: 4px 7px;
    border: 1px solid #d4dde6;
    border-radius: 6px;
    background: #fff;
    font-size: 11px;
    font-weight: 400;
}

.closing-erp .erp-text-right {
    text-align: right;
}

.closing-erp .erp-text-center {
    text-align: center;
}

.closing-erp .qty-value {
    display: block;
    text-align: right;
    font-weight: 800;
}

@media (max-width: 991px) {
    .closing-erp .closing-scroll-hint {
        display: block;
    }
}

@media (max-width: 767px) {
    .closing-erp {
        padding: 7px !important;
    }

    .closing-erp .erp-page-header {
        align-items: flex-start;
        padding: 13px;
    }

    .closing-erp .erp-page-title {
        font-size: 17px;
    }

    .closing-erp .erp-page-subtitle {
        display: none;
    }

    .closing-erp .closing-content {
        padding: 10px 9px 12px;
    }

    .closing-erp .closing-filter-grid,
    .closing-erp .closing-summary-grid {
        grid-template-columns: 1fr;
    }

    .closing-erp .closing-toolbar {
        display: block;
    }

    .closing-erp .closing-toolbar-left,
    .closing-erp .closing-toolbar-right,
    .closing-erp .closing-search {
        width: 100%;
    }

    .closing-erp .closing-toolbar-right {
        margin-top: 8px;
    }
}
</style>

<?php if ($is_app_mode) { ?>
<style>
/* =========================================================
   KLSPOS Closing Balance - True Mobile/App Layout
   Applied whenever ?app=1 is present.
   ========================================================= */

/* Remove AdminLTE desktop shell */
.main-header,
.main-sidebar,
.main-footer,
.control-sidebar,
.control-sidebar-bg,
.content-header,
.breadcrumb {
    display: none !important;
}

html,
body,
.wrapper {
    width: 100% !important;
    min-width: 0 !important;
    min-height: 100% !important;
    margin: 0 !important;
    background: #f4f7fb !important;
    overflow-x: hidden !important;
}

.content-wrapper,
.right-side,
.sidebar-mini .content-wrapper,
.sidebar-collapse .content-wrapper {
    width: 100% !important;
    min-width: 0 !important;
    min-height: 100vh !important;
    margin-left: 0 !important;
    padding-top: 0 !important;
    background: #f4f7fb !important;
}

/* Full-width app page */
.content.closing-erp {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.closing-erp .closing-shell {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    border-radius: 12px !important;
    box-shadow: none !important;
}

/* Header */
.closing-erp .erp-page-header {
    display: block !important;
    padding: 13px !important;
}

.closing-erp .erp-title-wrap {
    width: 100% !important;
}

.closing-erp .erp-page-title {
    font-size: 17px !important;
    line-height: 1.45 !important;
}

.closing-erp .erp-page-subtitle {
    display: block !important;
    margin-top: 3px !important;
    font-size: 13px !important;
}

.closing-erp .erp-header-actions {
    width: 100% !important;
    margin-top: 11px !important;
}

.closing-erp .erp-header-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    width: 100% !important;
    min-height: 43px !important;
    font-size: 14px !important;
}

/* Content */
.closing-erp .closing-content {
    padding: 10px !important;
}

/* Filter form */
.closing-erp .closing-filter-card {
    margin-bottom: 12px !important;
}

.closing-erp .closing-filter-header {
    padding: 12px !important;
    font-size: 15px !important;
}

.closing-erp .closing-filter-body {
    padding: 12px !important;
}

.closing-erp .closing-filter-grid {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 10px !important;
}

.closing-erp label {
    font-size: 13px !important;
}

.closing-erp .form-control,
.closing-erp .erp-date-picker input[type="date"],
.closing-erp .select2-container .select2-choice,
.closing-erp .select2-container--default .select2-selection--single {
    width: 100% !important;
    height: 42px !important;
    min-height: 42px !important;
    font-size: 14px !important;
}

.closing-erp .erp-date-picker-button {
    width: 42px !important;
    height: 40px !important;
}

.closing-erp .select2-container {
    width: 100% !important;
}

.closing-erp .select2-container .select2-choice,
.closing-erp .select2-container .select2-choice > .select2-chosen,
.closing-erp .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    line-height: 40px !important;
}

.closing-erp .closing-filter-actions {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
}

.closing-erp .closing-filter-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    width: 100% !important;
    min-height: 42px !important;
    margin: 0 !important;
    border-radius: 7px !important;
    font-size: 14px !important;
    font-weight: 800 !important;
}

/* Summary cards */
.closing-erp .closing-summary-grid {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    grid-auto-flow: row !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
}

.closing-erp .erp-summary-card {
    width: 100% !important;
    min-height: 86px !important;
    margin: 0 !important;
    padding: 12px !important;
}

.closing-erp .erp-summary-label {
    font-size: 13px !important;
    line-height: 1.4 !important;
}

.closing-erp .erp-summary-value {
    font-size: 18px !important;
    line-height: 1.35 !important;
}

/* Table card and toolbar */
.closing-erp .closing-table-heading {
    display: block !important;
    padding: 11px !important;
}

.closing-erp .closing-table-title {
    margin-bottom: 8px !important;
    font-size: 15px !important;
}

.closing-erp .closing-table-heading .text-muted {
    display: block !important;
    font-size: 12px !important;
    line-height: 1.5 !important;
}

.closing-erp .closing-toolbar {
    display: block !important;
    padding: 10px !important;
}

.closing-erp .closing-toolbar-left,
.closing-erp #closing_export_buttons,
.closing-erp .dt-buttons {
    display: none !important;
}

.closing-erp .closing-toolbar-right,
.closing-erp .closing-search {
    width: 100% !important;
}

.closing-erp .closing-search .form-control {
    width: 100% !important;
    height: 42px !important;
    font-size: 14px !important;
}

.closing-erp .closing-scroll-hint {
    display: block !important;
    font-size: 12px !important;
}

/* Wide table remains usable by horizontal swipe */
.closing-erp .closing-table-wrap {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.closing-erp .dataTables_wrapper,
.closing-erp .dataTables_scroll,
.closing-erp .dataTables_scrollHead,
.closing-erp .dataTables_scrollBody,
.closing-erp .dataTables_scrollFoot {
    width: 100% !important;
    max-width: 100% !important;
}

.closing-erp .dataTables_scrollBody {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
}

.closing-erp table.dataTable {
    width: 100% !important;
    min-width: 980px !important;
}

.closing-erp table.dataTable thead th,
.closing-erp table.dataTable tbody td,
.closing-erp table.dataTable tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
    white-space: nowrap !important;
}

/* Popups above app content */
.bootstrap-datetimepicker-widget,
.select2-drop,
.select2-dropdown,
.select2-container--open {
    z-index: 999999 !important;
}

@media (max-width: 560px) {
    .closing-erp .closing-summary-grid,
    .closing-erp .closing-filter-actions {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script>
var closingTable = null;

function closingToNumber(value) {
    if (typeof pf === 'function') {
        return pf(value);
    }

    value = value === null || value === undefined ? 0 : value;

    return parseFloat(
        String(value)
            .replace(/<[^>]*>/g, '')
            .replace(/,/g, '')
    ) || 0;
}

function closingFormatQty(data, type) {
    var number = closingToNumber(data);

    if (type !== 'display') {
        return number;
    }

    var text = number % 1 === 0
        ? String(number)
        : number.toFixed(2);

    return '<span class="qty-value">' + text + '</span>';
}

function closingFormatTotal(value) {
    var number = closingToNumber(value);

    return number % 1 === 0
        ? String(number)
        : number.toFixed(2);
}

function closingDate(data, type) {
    if (!data) {
        return '';
    }

    if (type === 'display' || type === 'filter') {
        return String(data).substr(0, 10);
    }

    return data;
}

$(document).ready(function () {

    function initialiseClosingSelect2(forceRebuild) {
        if (!$.fn.select2) {
            console.error('KLSPOS Closing Balance: Select2 library is not loaded.');
            return;
        }

        $('.closing-erp select.erp-select2').each(function () {
            var $select = $(this);

            var initialised =
                !!$select.data('select2') ||
                $select.hasClass('select2-hidden-accessible') ||
                $select.hasClass('select2-offscreen');

            if (initialised && !forceRebuild) {
                $select.next('.select2-container').css('width', '100%');
                return;
            }

            if (initialised) {
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

    closingTable = $('#ClosingData').DataTable({
        dom: 'Brtip',
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        responsive: false,

        ajax: {
            url: <?= json_encode($closing_ajax_url); ?>,
            type: 'POST',
            data: function (data) {
                data.<?= $this->security->get_csrf_token_name(); ?> =
                    <?= json_encode($this->security->get_csrf_hash()); ?>;
            }
        },

        paging: false,
        info: false,
        searching: true,
        ordering: true,

        columnDefs: [
            {
                orderable: false,
                targets: 0
            }
        ],

        buttons: [
            {
                extend: 'copyHtml5',
                footer: true,
                exportOptions: {columns: [0,1,2,3,4,5,6]}
            },
            {
                extend: 'excelHtml5',
                footer: true,
                exportOptions: {columns: [0,1,2,3,4,5,6]}
            },
            {
                extend: 'csvHtml5',
                footer: true,
                exportOptions: {columns: [0,1,2,3,4,5,6]}
            },
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {columns: [0,1,2,3,4,5,6]}
            },
            {
                extend: 'colvis',
                text: <?= json_encode(lang('columns'), JSON_UNESCAPED_UNICODE); ?>
            }
        ],

        columns: [
            {
                data: null,
                defaultContent: '',
                orderable: false,
                searchable: false,
                className: 'erp-text-center',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                data: 'date',
                render: closingDate
            },
            {
                data: 'store_name'
            },
            {
                data: 'product_code'
            },
            {
                data: 'product_name'
            },
            {
                data: 'qty_base',
                className: 'erp-text-right',
                render: closingFormatQty
            },
            {
                data: 'qty_secondary',
                className: 'erp-text-right',
                render: closingFormatQty
            }
        ],

        order: [[4, 'asc']],

        drawCallback: function () {
            var api = this.api();

            api
                .column(0, {search: 'applied', order: 'applied'})
                .nodes()
                .each(function (cell, index) {
                    cell.innerHTML = index + 1;
                });
        },

        footerCallback: function () {
            var api = this.api();

            var baseQty = api
                .column(5, {search: 'applied'})
                .data()
                .reduce(function (a, b) {
                    return closingToNumber(a) + closingToNumber(b);
                }, 0);

            var secondQty = api
                .column(6, {search: 'applied'})
                .data()
                .reduce(function (a, b) {
                    return closingToNumber(a) + closingToNumber(b);
                }, 0);

            $(api.column(5).footer()).html(
                '<span class="qty-value">' +
                    closingFormatTotal(baseQty) +
                '</span>'
            );

            $(api.column(6).footer()).html(
                '<span class="qty-value">' +
                    closingFormatTotal(secondQty) +
                '</span>'
            );

            $('#closing_records').text(
                api.rows({search: 'applied'}).count()
            );

            $('#closing_base_qty').text(
                closingFormatTotal(baseQty)
            );

            $('#closing_second_qty').text(
                closingFormatTotal(secondQty)
            );
        },

        initComplete: function () {
            var api = this.api();

            api.buttons()
                .container()
                .appendTo('#closing_export_buttons');

            api.columns.adjust();
        }
    });

    $('#search_table').on('keyup change', function (event) {
        var keyCode = event.keyCode || event.which;

        if (
            (keyCode === 13 && closingTable.search() !== this.value) ||
            (closingTable.search() !== '' && this.value === '')
        ) {
            closingTable.search(this.value).draw();
        }
    });

    closingTable.columns().every(function () {
        var column = this;

        $('input', column.footer()).on('keyup change', function (event) {
            var keyCode = event.keyCode || event.which;

            if (
                (keyCode === 13 && column.search() !== this.value) ||
                (column.search() !== '' && this.value === '')
            ) {
                column.search(this.value).draw();
            }
        });
    });

    $('.toggle_form').on('click', function (event) {
        event.preventDefault();

        var $button = $(this);

        $('#closing_filter_form').stop(true, true).slideToggle(180, function () {
            var isVisible = $(this).is(':visible');

            $button
                .toggleClass('active', isVisible)
                .attr('aria-expanded', isVisible ? 'true' : 'false');

            if (isVisible) {
                initialiseClosingSelect2(false);
            }
        });
    });

    $('#open_closing_calendar').on('click', function () {
        var input = document.getElementById('closing_date');

        if (!input) {
            return;
        }

        if (typeof input.showPicker === 'function') {
            try {
                input.showPicker();
                return;
            } catch (error) {}
        }

        input.focus();
        input.click();
    });

    initialiseClosingSelect2(true);

    window.setTimeout(function () {
        initialiseClosingSelect2(false);
        closingTable.columns.adjust();
    }, 120);

    $(window).on('resize orientationchange', function () {
        window.setTimeout(function () {
            initialiseClosingSelect2(false);
            closingTable.columns.adjust();
        }, 120);
    });
});
</script>

<section class="content closing-erp">
    <div class="closing-shell">

        <div class="erp-page-header">
            <div class="erp-title-wrap">
                <div class="erp-title-icon">
                    <i class="fa fa-calendar-check-o"></i>
                </div>

                <div>
                    <h1 class="erp-page-title"><?= $page_title; ?></h1>

                    <p class="erp-page-subtitle">
                        <?= lang('closing_balance_description'); ?>

                        <?php if (!empty($display_date)): ?>
                            &nbsp;•&nbsp;
                            <?= lang('date'); ?>:
                            <?= html_escape($display_date); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="erp-header-actions">
                <button
                    type="button"
                    class="btn toggle_form"
                    aria-expanded="false"
                    aria-controls="closing_filter_form"
                >
                    <i class="fa fa-filter"></i>
                    <?= lang('show_hide'); ?>
                </button>
            </div>
        </div>

        <div class="closing-content">

            <div id="closing_filter_form" class="closing-filter-card" style="display:none;">
                <div class="closing-filter-header">
                    <i class="fa fa-filter"></i>
                    <?= lang('filter'); ?>
                </div>

                <div class="closing-filter-body">
                    <?= form_open($closing_form_action); ?>

                    <div class="closing-filter-grid">

                        <div class="erp-field">
                            <label for="closing_date"><?= lang('date'); ?></label>

                            <div class="erp-date-picker">
                                <input
                                    type="date"
                                    name="date"
                                    id="closing_date"
                                    value="<?= html_escape(set_value('date', $filter_date)); ?>"
                                >

                                <button
                                    type="button"
                                    id="open_closing_calendar"
                                    class="erp-date-picker-button"
                                    aria-label="<?= html_escape(lang('date')); ?>"
                                    title="<?= html_escape(lang('date')); ?>"
                                >
                                    <i class="fa fa-calendar"></i>
                                </button>
                            </div>
                        </div>

                        <div class="erp-field">
                            <label for="store_id"><?= lang('store'); ?></label>

                            <select
                                name="store_id"
                                id="store_id"
                                class="form-control select2 erp-select2"
                                style="width:100%;"
                            >
                                <option value=""><?= lang('all_stores'); ?></option>

                                <?php foreach ($stores as $store): ?>
                                    <option
                                        value="<?= (int) $store->id; ?>"
                                        <?= set_select(
                                            'store_id',
                                            $store->id,
                                            (string) $filter_store_id === (string) $store->id
                                        ); ?>
                                    >
                                        <?= html_escape($store->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="erp-field">
                            <label>&nbsp;</label>

                            <div class="closing-filter-actions">
                                <button
                                    type="submit"
                                    class="btn btn-primary filter-submit"
                                >
                                    <i class="fa fa-search"></i>
                                    <?= lang('submit'); ?>
                                </button>

                                <a
                                    href="<?= html_escape($closing_reset_url); ?>"
                                    class="btn btn-default closing-reset"
                                >
                                    <i class="fa fa-refresh"></i>
                                    <?= lang('reset'); ?>
                                </a>
                            </div>
                        </div>

                    </div>

                    <?= form_close(); ?>
                </div>
            </div>

            <div class="closing-summary-grid">
                <div class="erp-summary-card erp-card-blue">
                    <div class="erp-summary-label"><?= lang('records'); ?></div>
                    <div class="erp-summary-value" id="closing_records">0</div>
                </div>

                <div class="erp-summary-card erp-card-green">
                    <div class="erp-summary-label"><?= lang('base_qty'); ?></div>
                    <div class="erp-summary-value" id="closing_base_qty">0</div>
                </div>

                <div class="erp-summary-card erp-card-orange">
                    <div class="erp-summary-label"><?= lang('second_qty'); ?></div>
                    <div class="erp-summary-value" id="closing_second_qty">0</div>
                </div>
            </div>

            <div class="closing-table-card">
                <div class="closing-table-heading">
                    <h4 class="closing-table-title">
                        <i class="fa fa-list"></i>
                        <?= lang('closing_balance'); ?>
                        <?= lang('list'); ?>
                    </h4>

                    <span class="text-muted">
                        <i class="fa fa-info-circle"></i>
                        <?= lang('closing_table_help'); ?>
                    </span>
                </div>

                <p class="closing-scroll-hint">
                    <i class="fa fa-arrows-h"></i>
                    <?= lang('swipe_table_horizontal'); ?>
                </p>

                <div class="closing-toolbar">
                    <div class="closing-toolbar-left" id="closing_export_buttons"></div>

                    <div class="closing-toolbar-right">
                        <div class="closing-search">
                            <i class="fa fa-search"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="search_table"
                                placeholder="<?= html_escape(lang('type_hit_enter')); ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="closing-table-wrap">
                    <table
                        id="ClosingData"
                        class="table table-striped table-bordered table-hover"
                        style="width:100%;"
                    >
                        <thead>
                            <tr>
                                <th style="width:50px;"><?= lang('no'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('store'); ?></th>
                                <th><?= lang('product_code'); ?></th>
                                <th><?= lang('product_name'); ?></th>
                                <th class="erp-text-right"><?= lang('base_qty'); ?></th>
                                <th class="erp-text-right"><?= lang('second_qty'); ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td colspan="7" class="dataTables_empty">
                                    <?= lang('loading_data_from_server'); ?>
                                </td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr>
                                <th></th>

                                <th>
                                    <input
                                        type="text"
                                        class="text_filter"
                                        placeholder="[<?= html_escape(lang('date')); ?>]"
                                    >
                                </th>

                                <th>
                                    <input
                                        type="text"
                                        class="text_filter"
                                        placeholder="[<?= html_escape(lang('store')); ?>]"
                                    >
                                </th>

                                <th>
                                    <input
                                        type="text"
                                        class="text_filter"
                                        placeholder="[<?= html_escape(lang('product_code')); ?>]"
                                    >
                                </th>

                                <th>
                                    <input
                                        type="text"
                                        class="text_filter"
                                        placeholder="[<?= html_escape(lang('product_name')); ?>]"
                                    >
                                </th>

                                <th class="erp-text-right"><?= lang('base_qty'); ?></th>
                                <th class="erp-text-right"><?= lang('second_qty'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</section>

