<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>
<?php /* KLSPOS Purchase Listing - Receive action in Received column - 2026-08-08 */ ?>
<?php
$PL = function ($key, $fallback) {
    $text = lang($key);

    return (
        $text !== false &&
        $text !== null &&
        $text !== '' &&
        $text !== $key
    ) ? $text : $fallback;
};
?>
<?php
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$app_language =
    $this->input->get('app_lang', true) ?:
    (
        $this->input->post('app_lang', true) ?:
        $Settings->selected_language
    );

$app_query = $is_app_mode
    ? '?app=1&app_lang=' . rawurlencode($app_language)
    : '';

/*
|--------------------------------------------------------------------------
| Mobile Action Button Language
|--------------------------------------------------------------------------
*/
$current_action_language = strtolower(
    trim((string) $app_language)
);

$is_myanmar_action_language = in_array(
    $current_action_language,
    [
        'myanmar',
        'burmese',
        'mm',
        'my'
    ],
    true
);

if ($is_myanmar_action_language) {
    $mobile_action_labels = [
        'view_purchase' => 'အဝယ်ဘောင်ချာကြည့်ရန်',
        'view_payments' => 'ငွေပေးချေမှုများကြည့်ရန်',
        'add_payment'   => 'အကြွေးပေးရန်',
        'receive'       => 'လက်ခံရန်',
        'received'      => 'လက်ခံပြီး',
        'delete'        => 'ဖျက်ရန်',
        'delete_confirm'=> 'ဤအဝယ်ဘောင်ချာကို ဖျက်ရန် သေချာပါသလား။',
    ];
} else {
    $mobile_action_labels = [
        'view_purchase' => 'View Purchase',
        'view_payments' => 'View Payments',
        'add_payment'   => 'Add Payment',
        'receive'       => 'Receive',
        'received'      => 'Received',
        'delete'        => 'Delete',
        'delete_confirm'=> 'Are you sure you want to delete this purchase?',
    ];
}

$purchase_page_labels = [
    'show_summary' => lang('show_summary'),
    'hide_summary' => lang('hide_summary'),
];

$product_purchase_tab_label = lang('product_purchase_list');

if (
    empty($product_purchase_tab_label) ||
    $product_purchase_tab_label === 'product_purchase_list'
) {
    $product_purchase_tab_label = 'ပစ္စည်းအဝယ်စာရင်း';
}

$product_purchase_start_date =
    date('Y-m-d', strtotime('monday this week'));

$product_purchase_end_date =
    date('Y-m-d', strtotime('saturday this week'));
?>

<style>
/* =========================================================
   Supplier Due - Supplier First Selection
========================================================= */

.erp-supplier-selector-panel {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    padding: 18px;
    margin-bottom: 16px;
    background: #ffffff;
    border: 1px solid #dfe5ec;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.erp-supplier-selector-header {
    flex: 1 1 auto;
    min-width: 0;
}

.erp-supplier-selector-title {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #263238;
}

.erp-supplier-selector-title i {
    margin-right: 7px;
    color: #1976d2;
}

.erp-supplier-selector-note {
    margin: 0;
    color: #6b7785;
    font-size: 16px;
    line-height: 1.6;
}

.erp-supplier-selector-control {
    flex: 0 0 330px;
    max-width: 100%;
}

.erp-supplier-selector-control label {
    display: block;
    margin-bottom: 7px;
    font-weight: 600;
    color: #37474f;
}

.erp-supplier-selector-control .select2-container {
    width: 100% !important;
}

.erp-supplier-empty-state {
    padding: 50px 20px;
    margin-bottom: 18px;
    text-align: center;
    background: #fafbfd;
    border: 1px dashed #cbd4df;
    border-radius: 8px;
}

.erp-supplier-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 62px;
    height: 62px;
    margin: 0 auto 15px;
    background: #eaf3fd;
    border-radius: 50%;
    color: #1976d2;
    font-size: 26px;
}

.erp-supplier-empty-state h4 {
    margin: 0 0 8px;
    font-size: 17px;
    font-weight: 700;
    color: #37474f;
}

.erp-supplier-empty-state p {
    max-width: 480px;
    margin: 0 auto;
    color: #78909c;
    line-height: 1.7;
}

#supplierDueWorkspace {
    animation: supplierDueFadeIn 0.2s ease-in-out;
}

@keyframes supplierDueFadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 767px) {
    .erp-supplier-selector-panel {
        display: block;
        padding: 14px;
    }

    .erp-supplier-selector-control {
        width: 100%;
        margin-top: 14px;
    }

    .erp-supplier-empty-state {
        padding: 35px 15px;
    }
}
/* =========================================================
   KLSPOS - Sales List + Customer Due Payment ERP Tabs
   Clean Full Version
   ========================================================= */

.purchase-tabs-page {
    padding: 16px 20px 26px !important;
    background: #f4f7fb;
}

.purchase-tabs-page .erp-shell {
    overflow: hidden;
    background: #fff;
    border: 1px solid #dfe6ef;
    border-radius: 14px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
}

/* Header */
.purchase-tabs-page .erp-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    border-bottom: 1px solid #e8eef5;
    background: linear-gradient(135deg, #ffffff 0%, #f7fbfc 100%);
}

.purchase-tabs-page .erp-title-wrap {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.purchase-tabs-page .erp-title-icon {
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

.purchase-tabs-page .erp-title-text h3 {
    margin: 0;
    color: #172033;
    font-size: 20px;
    font-weight: 800;
    line-height: 1.25;
}

/* =========================================================
   Tabs - Desktop / Tablet / Mobile all side by side
   ========================================================= */

.purchase-tabs-page .erp-tabs-wrap {
    padding: 12px 16px 0;
    background: #fff;
}

.purchase-tabs-page ul.nav.nav-tabs.erp-tabs {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 7px !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 5px !important;
    border: 1px solid #e1e8ef;
    border-radius: 10px;
    background: #f4f7fa;
}

/* Bootstrap .nav pseudo elements must not become grid cells */
.purchase-tabs-page ul.nav.nav-tabs.erp-tabs::before,
.purchase-tabs-page ul.nav.nav-tabs.erp-tabs::after {
    display: none !important;
    content: none !important;
}

.purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li {
    display: block !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}

.purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li > a {
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

.purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li > a:hover {
    background: #e8f0f5;
    color: #2f5968;
}

.purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li.active > a,
.purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li.active > a:hover,
.purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li.active > a:focus {
    background: #2f8191;
    color: #fff;
    box-shadow: 0 4px 11px rgba(47, 129, 145, .24);
}

.purchase-tabs-page .tab-count {
    min-width: 24px;
    padding: 2px 7px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .22);
    font-size: 16px;
    text-align: center;
}

.purchase-tabs-page .erp-tabs > li:not(.active) .tab-count {
    background: #dfe8ee;
    color: #496071;
}

.purchase-tabs-page .erp-tab-content {
    padding: 15px 16px 18px;
}

/* =========================================================
   Summary cards
   ========================================================= */

.purchase-tabs-page .erp-summary-grid {
    display: grid;
    gap: 12px;
    width: 100%;
    margin: 0 0 12px;
}

.purchase-tabs-page .erp-summary-four {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.purchase-tabs-page .erp-summary-three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.purchase-tabs-page .erp-summary-cell {
    min-width: 0;
}

.purchase-tabs-page .erp-stat-card {
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

.purchase-tabs-page .erp-stat-card::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    content: "";
    background: #3c8dbc;
}

.purchase-tabs-page .erp-stat-card.success::before { background: #21a366; }
.purchase-tabs-page .erp-stat-card.warning::before { background: #f39c12; }
.purchase-tabs-page .erp-stat-card.danger::before  { background: #e5533d; }

.purchase-tabs-page .erp-stat-label {
    margin-bottom: 6px;
    color: #7a8793;
    font-size: 16px;
    line-height: 1.45;
}

.purchase-tabs-page .erp-stat-value {
    padding-right: 38px;
    color: #263238;
    font-size: 20px;
    font-weight: 800;
    line-height: 1.2;
    word-break: break-word;
}

.purchase-tabs-page .erp-stat-icon {
    position: absolute;
    right: 14px;
    bottom: 10px;
    color: rgba(0, 0, 0, .09);
    font-size: 30px;
}

/* =========================================================
   Filters
   ========================================================= */

.purchase-tabs-page .erp-filter-card {
    margin-bottom: 12px;
    padding: 12px;
    border: 1px solid #338191;
    border-radius: 10px;
    background: #eff7f9;
}

.purchase-tabs-page .erp-filter-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    width: 100%;
}

.purchase-tabs-page .erp-filter-cell {
    min-width: 0;
}

.purchase-tabs-page .erp-filter-card label,
.purchase-tabs-page .erp-payment-panel label,
.purchase-tabs-page .erp-field-label {
    display: block;
    min-height: 20px;
    margin-bottom: 5px;
    color: #5d6e80;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.5;
}

.purchase-tabs-page .erp-filter-card .form-control,
.purchase-tabs-page .erp-payment-panel .form-control {
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

.purchase-tabs-page .select2-container {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
}

.purchase-tabs-page select.erp-select2.select2-hidden-accessible,
.purchase-tabs-page select.erp-select2.select2-offscreen {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    border: 0 !important;
    clip: rect(0 0 0 0) !important;
    overflow: hidden !important;
}

/* Select2 v3 */
.purchase-tabs-page .select2-container .select2-choice {
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

.purchase-tabs-page .select2-container .select2-choice > .select2-chosen {
    line-height: 36px !important;
}

.purchase-tabs-page .select2-container .select2-choice .select2-arrow {
    width: 32px !important;
    height: 36px !important;
    border-left: 0 !important;
    background: transparent !important;
}

/* Select2 v4 */
.purchase-tabs-page .select2-container--default .select2-selection--single {
    width: 100% !important;
    height: 38px !important;
    min-height: 38px !important;
    border: 1px solid #cfd9e3 !important;
    border-radius: 7px !important;
    background: #fff !important;
    box-shadow: none !important;
}

.purchase-tabs-page .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    padding-left: 11px !important;
    padding-right: 34px !important;
    line-height: 36px !important;
}

.purchase-tabs-page .select2-container--default
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

.purchase-tabs-page .erp-payment-panel {
    margin-top: 13px;
    margin-bottom: 13px;
    padding: 14px;
    border: 1px solid #d7e7ea;
    border-left: 4px solid #2f8191;
    border-radius: 10px;
    background: linear-gradient(135deg, #f6fcfd 0%, #ffffff 100%);
}

.purchase-tabs-page .erp-payment-title {
    margin: 0 0 12px;
    color: #294e5b;
    font-size: 16px;
    font-weight: 800;
}

.purchase-tabs-page .erp-payment-grid {
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

.purchase-tabs-page .erp-payment-cell {
    display: flex;
    min-width: 0;
    height: 100%;
    flex-direction: column;
    justify-content: flex-end;
}

.purchase-tabs-page .erp-payment-cell .erp-field-label,
.purchase-tabs-page .erp-payment-cell label {
    min-height: 20px;
    margin-bottom: 5px;
}

.purchase-tabs-page .erp-selected-due {
    min-height: 38px;
    color: #dd4b39;
    font-size: 20px;
    font-weight: 800;
    line-height: 38px;
}

.purchase-tabs-page .erp-payment-panel .btn {
    width: 100%;
    min-height: 38px;
    border: 0;
    border-radius: 7px;
    font-weight: 700;
}

.purchase-tabs-page #bulkPayBtn {
    min-height: 38px;
    white-space: normal;
    line-height: 1.45;
}

.purchase-tabs-page .erp-help-note {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 8px;
    background: #fff8e8;
    color: #786025;
    font-size: 16px;
    line-height: 1.55;
}

/* =========================================================
   DataTable toolbar
   ========================================================= */

.purchase-tabs-page .erp-toolbar {
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

.purchase-tabs-page .erp-toolbar-left,
.purchase-tabs-page .erp-toolbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    flex-wrap: wrap;
}

.purchase-tabs-page .erp-toolbar-right {
    margin-left: auto;
}

.purchase-tabs-page .erp-search-box {
    position: relative;
    width: 290px;
    max-width: 100%;
}

.purchase-tabs-page .erp-search-box i {
    position: absolute;
    top: 50%;
    left: 12px;
    z-index: 2;
    transform: translateY(-50%);
    color: #94a3b8;
}

.purchase-tabs-page .erp-search-box .form-control {
    width: 100%;
    height: 36px;
    padding-left: 36px;
    border: 1px solid #cfd9e3;
    border-radius: 7px;
    box-shadow: none;
}

.purchase-tabs-page .erp-toolbar .btn {
    min-height: 36px;
    border-radius: 7px;
}

.purchase-tabs-page .dt-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.purchase-tabs-page .dt-buttons .btn,
.purchase-tabs-page .dt-buttons .dt-button {
    margin: 0 !important;
    padding: 7px 11px !important;
    border: 1px solid #dbe3ed !important;
    border-radius: 7px !important;
    background: #f8fafc !important;
    color: #334155 !important;
    box-shadow: none !important;
    font-size: 16px;
    font-weight: 800;
}

/* =========================================================
   Table
   ========================================================= */

.purchase-tabs-page .erp-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    border: 1px solid #e2e9ef;
    border-radius: 0 0 10px 10px;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.purchase-tabs-page .dataTables_wrapper,
.purchase-tabs-page .dataTables_scroll,
.purchase-tabs-page .dataTables_scrollHead,
.purchase-tabs-page .dataTables_scrollBody,
.purchase-tabs-page .dataTables_scrollFoot {
    width: 100% !important;
    max-width: 100% !important;
}

.purchase-tabs-page .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.purchase-tabs-page table.dataTable {
    width: 100% !important;
    min-width: 920px !important;
    margin: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0;
}

.purchase-tabs-page #dueData.dataTable {
    min-width: 1120px !important;
}

.purchase-tabs-page table.dataTable thead th {
    padding: 10px 9px !important;
    border-right: 1px solid #edf2f7 !important;
    border-bottom: 1px solid #dfe6ef !important;
    background: #f3f6f9 !important;
    color: #334155;
    font-size: 16px;
    font-weight: 900;
    vertical-align: middle;
    white-space: nowrap;
}

.purchase-tabs-page table.dataTable tbody td {
    padding: 11px 9px !important;
    border-right: 1px solid #edf2f7 !important;
    border-bottom: 1px solid #edf2f7 !important;
    color: #334155;
    vertical-align: middle;
    white-space: nowrap;
}

.purchase-tabs-page table.dataTable tbody tr:nth-child(even) td {
    background: #fafafa;
}

.purchase-tabs-page table.dataTable tbody tr:hover td {
    background: #f1f9f7 !important;
}

.purchase-tabs-page table.dataTable tfoot th {
    padding: 8px !important;
    border-top: 1px solid #dfe6ef !important;
    border-right: 1px solid #edf2f7 !important;
    background: #f8fafc !important;
    font-weight: 900;
    white-space: nowrap;
}

/*
 * scrollX mode keeps hidden sizing copies of THEAD/TFOOT inside
 * .dataTables_scrollBody.  The general !important padding rules above
 * must not give those sizing rows visible height.
 */
.purchase-tabs-page .dataTables_scrollBody table.dataTable:not(#purData) thead th,
.purchase-tabs-page .dataTables_scrollBody table.dataTable:not(#purData) tfoot th {
    height: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    border-top: 0 !important;
    border-bottom: 0 !important;
    line-height: 0 !important;
    background: transparent !important;
    color: transparent !important;
}

.purchase-tabs-page .dataTables_scrollBody table.dataTable:not(#purData) .dataTables_sizing {
    height: 0 !important;
    margin: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    overflow: hidden !important;
    line-height: 0 !important;
}

.purchase-tabs-page .amount-cell,
.purchase-tabs-page .amount-total {
    display: block;
    text-align: right;
    font-weight: 800;
}

.purchase-tabs-page .sale_status,
.purchase-tabs-page .preorder_status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 64px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 16px;
    font-weight: 900;
}

.purchase-tabs-page .label-success { background: #16a34a !important; }
.purchase-tabs-page .label-primary { background: #2563eb !important; }
.purchase-tabs-page .label-danger  { background: #ef4444 !important; }
.purchase-tabs-page .label-warning { background: #f59e0b !important; }

.purchase-tabs-page .deliver_preorder {
    margin-left: 6px;
    padding: 4px 9px;
    border-radius: 999px !important;
    font-size: 16px;
    font-weight: 800;
}

.purchase-tabs-page .erp-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex-wrap: wrap;
    white-space: nowrap;
}

.purchase-tabs-page .purchase_check,
.purchase-tabs-page #checkAllDue {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.purchase-tabs-page .erp-scroll-hint {
    display: none;
    margin: 0 0 7px;
    color: #6f7e8d;
    font-size: 16px;
}

.purchase-tabs-page .dataTables_info {
    padding-top: 11px !important;
    color: #64748b;
    font-size: 16px;
    font-weight: 700;
}

.purchase-tabs-page .pagination > li > a,
.purchase-tabs-page .pagination > li > span {
    margin-left: 4px;
    border-color: #dbe3ed;
    border-radius: 7px !important;
    color: #334155;
}

.purchase-tabs-page .pagination > .active > a,
.purchase-tabs-page .pagination > .active > span {
    border-color: #2c7f75 !important;
    background: #2c7f75 !important;
    color: #fff !important;
}


/* =========================================================
   Paging enabled, totals calculated from all filtered rows
   ========================================================= */

.purchase-tabs-page .dataTables_paginate,
.purchase-tabs-page .dataTables_info,
.purchase-tabs-page .dataTables_length {
    display: block !important;
}

.purchase-tabs-page .dataTables_length {
    margin-bottom: 8px;
}

/* =========================================================
   Product Purchase List Tab
   ========================================================= */

.purchase-tabs-page #productPurchaseData.dataTable {
    min-width: 620px !important;
}

.purchase-tabs-page .product-purchase-filter-actions {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    min-height: 100%;
}

.purchase-tabs-page .product-purchase-filter-actions .btn {
    width: 100%;
    min-height: 38px;
    border-radius: 7px;
    font-weight: 800;
}

.purchase-tabs-page .product-purchase-date {
    padding-left: 10px;
    padding-right: 10px;
}

@media (max-width: 767px) {
    .purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li > a {
        padding-left: 3px !important;
        padding-right: 3px !important;
        font-size: 16px !important;
    }

    .purchase-tabs-page .product-purchase-filter-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 5px;
    }

    .purchase-tabs-page #productPurchaseData.dataTable {
        min-width: 620px !important;
    }
}

/* =========================================================
   Mobile DataTable - Compact Column Widths
   ========================================================= */

/*
 * Product Purchase List
 * 6 columns ကို အရင်လို အရမ်းကျယ်မနေစေဘဲ
 * 700px ဝန်းကျင်အတွင်း ချုံ့ထားသည်။
 */
#productPurchaseData_wrapper table.dataTable {
    table-layout: fixed !important;
    min-width: 620px !important;
}

#productPurchaseData_wrapper table.dataTable th:nth-child(1),
#productPurchaseData_wrapper table.dataTable td:nth-child(1) {
    width: 52px !important;
    min-width: 52px !important;
    max-width: 52px !important;
    text-align: center !important;
}

#productPurchaseData_wrapper table.dataTable th:nth-child(2),
#productPurchaseData_wrapper table.dataTable td:nth-child(2) {
    width: 125px !important;
    min-width: 125px !important;
    max-width: 125px !important;
}

#productPurchaseData_wrapper table.dataTable th:nth-child(3),
#productPurchaseData_wrapper table.dataTable td:nth-child(3) {
    width: 175px !important;
    min-width: 175px !important;
    max-width: 175px !important;
}

#productPurchaseData_wrapper table.dataTable th:nth-child(4),
#productPurchaseData_wrapper table.dataTable td:nth-child(4) {
    width: 155px !important;
    min-width: 155px !important;
    max-width: 155px !important;
}

#productPurchaseData_wrapper table.dataTable th:nth-child(5),
#productPurchaseData_wrapper table.dataTable td:nth-child(5) {
    width: 145px !important;
    min-width: 145px !important;
    max-width: 145px !important;
}

/* Product name ရှည်လျှင် wrap ဖြစ်စေမည် */
#productPurchaseData_wrapper table.dataTable td:nth-child(3) {
    white-space: normal !important;
    overflow-wrap: anywhere;
    word-break: break-word;
    line-height: 1.35;
}

#productPurchaseData_wrapper table.dataTable td:nth-child(2) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap !important;
}


/*
 * Purchase Voucher List
 * Column 9 ခုရှိသဖြင့် horizontal scroll ကိုထားမည်။
 * ဒါပေမယ့် column တစ်ခုချင်း မလိုအပ်ဘဲ မကျယ်စေပါ။
 */
#purData_wrapper table.dataTable {
    table-layout: fixed !important;
    width: 100% !important;
    min-width: 1107px !important;
    max-width: none !important;
}

/*
 * Purchase table အတွက် scroll ကို .erp-table-wrap တစ်ခုတည်းမှာပဲထားပါမည်။
 * Site-wide DataTables script က scrollX ဖြင့် အရင် initialize လုပ်ထားလျှင်
 * clone header ကိုဖျောက်ပြီး body table ထဲက မူရင်း THEAD ကို ပြန်ပြမည်။
 * ဒါကြောင့် Header/Body သည် table တစ်ခုတည်း၏ colgroup ကိုသုံးပြီး
 * horizontal scroll ဆွဲသည့်အချိန် လုံးဝခွာမသွားတော့ပါ။
 */
#purData_wrapper {
    width: 100% !important;
    min-width: 1107px !important;
    max-width: none !important;
}

#purData_wrapper .dataTables_scroll {
    width: 100% !important;
    min-width: 1107px !important;
    max-width: none !important;
}

#purData_wrapper .dataTables_scrollHead {
    display: none !important;
}

#purData_wrapper .dataTables_scrollBody {
    width: 100% !important;
    min-width: 1107px !important;
    max-width: none !important;
    max-height: none !important;
    overflow: visible !important;
}

#purData_wrapper .dataTables_scrollBody table#purData thead {
    display: table-header-group !important;
    visibility: visible !important;
    height: auto !important;
}

#purData_wrapper .dataTables_scrollBody table#purData thead tr {
    display: table-row !important;
    height: auto !important;
}

#purData_wrapper .dataTables_scrollBody table#purData thead th,
#purData_wrapper .dataTables_scrollBody table#purData thead td {
    display: table-cell !important;
    visibility: visible !important;
    height: auto !important;
    padding: 10px 6px !important;
    border-bottom: 1px solid #dfe6ef !important;
    background: #f3f6f9 !important;
    color: #334155 !important;
    line-height: 1.3 !important;
    white-space: normal !important;
    overflow: hidden !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    text-align: center !important;
}

#purData_wrapper .dataTables_scrollBody table#purData thead .dataTables_sizing {
    display: block !important;
    visibility: visible !important;
    height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
    color: inherit !important;
    line-height: inherit !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
}

#purData_wrapper table#purData thead .pur-header-label {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    text-align: center !important;
    line-height: 1.25 !important;
}

#purData_wrapper .dataTables_scrollHead table.dataTable,
#purData_wrapper .dataTables_scrollBody table.dataTable,
#purData_wrapper .dataTables_scrollFoot table.dataTable {
    width: 100% !important;
    min-width: 1107px !important;
    max-width: none !important;
    table-layout: fixed !important;
}

#purData_wrapper table.dataTable th,
#purData_wrapper table.dataTable td {
    box-sizing: border-box !important;
}

#purData_wrapper table.dataTable th:nth-child(1),
#purData_wrapper table.dataTable td:nth-child(1) {
    width: 52px !important;
    min-width: 52px !important;
    max-width: 52px !important;
}

#purData_wrapper table.dataTable th:nth-child(2),
#purData_wrapper table.dataTable td:nth-child(2) {
    width: 105px !important;
    min-width: 105px !important;
    max-width: 105px !important;
}

#purData_wrapper table.dataTable th:nth-child(3),
#purData_wrapper table.dataTable td:nth-child(3) {
    width: 170px !important;
    min-width: 170px !important;
    max-width: 170px !important;
}

#purData_wrapper table.dataTable th:nth-child(4),
#purData_wrapper table.dataTable td:nth-child(4),
#purData_wrapper table.dataTable th:nth-child(5),
#purData_wrapper table.dataTable td:nth-child(5),
#purData_wrapper table.dataTable th:nth-child(6),
#purData_wrapper table.dataTable td:nth-child(6) {
    width: 120px !important;
    min-width: 120px !important;
    max-width: 120px !important;
}

#purData_wrapper table.dataTable th:nth-child(7),
#purData_wrapper table.dataTable td:nth-child(7) {
    width: 145px !important;
    min-width: 145px !important;
    max-width: 145px !important;
}

#purData_wrapper table.dataTable th:nth-child(8),
#purData_wrapper table.dataTable td:nth-child(8) {
    width: 125px !important;
    min-width: 125px !important;
    max-width: 125px !important;
}

#purData_wrapper .dataTables_scrollHead table.dataTable th:nth-child(7),
#purData_wrapper .dataTables_scrollHead table.dataTable th:nth-child(8) {
    white-space: normal !important;
    line-height: 1.35 !important;
    text-align: center !important;
}

#purData_wrapper table.dataTable th:nth-child(9),
#purData_wrapper table.dataTable td:nth-child(9) {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
    white-space: nowrap !important;
}

/* Supplier name ကို အရမ်းရှည်လို့ table ဆွဲမကျယ်စေပါ */
#purData_wrapper table.dataTable td:nth-child(3) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap !important;
}


/*
 * Mobile မှာ padding ကို နည်းနည်းထပ်ချုံ့ပြီး
 * header စာသားတွေ လိုအပ်ရင် 2 လိုင်း wrap လုပ်ပါမယ်။
 */
@media (max-width: 767px) {
    #productPurchaseData_wrapper table.dataTable,
    #purData_wrapper table.dataTable {
        table-layout: fixed !important;
    }

    #purData_wrapper table.dataTable,
    #purData_wrapper .dataTables_scrollHead table.dataTable,
    #purData_wrapper .dataTables_scrollBody table.dataTable,
    #purData_wrapper .dataTables_scrollFoot table.dataTable {
        width: 100% !important;
        min-width: 1107px !important;
        max-width: none !important;
    }

    #productPurchaseData_wrapper table.dataTable thead th,
    #productPurchaseData_wrapper table.dataTable tbody td,
    #productPurchaseData_wrapper table.dataTable tfoot th,
    #purData_wrapper table.dataTable thead th,
    #purData_wrapper table.dataTable tbody td,
    #purData_wrapper table.dataTable tfoot th {
        padding-left: 6px !important;
        padding-right: 6px !important;
    }

    #productPurchaseData_wrapper
    .dataTables_scrollHead table.dataTable thead th,
    #purData_wrapper
    .dataTables_scrollHead table.dataTable thead th {
        white-space: normal !important;
        line-height: 1.3 !important;
        vertical-align: middle !important;
    }

    /* Amount / Quantity ကို ညာဘက်တန်းထားမယ် */
    #productPurchaseData_wrapper table.dataTable td:nth-child(4),
    #productPurchaseData_wrapper table.dataTable td:nth-child(5),
    #purData_wrapper table.dataTable td:nth-child(4),
    #purData_wrapper table.dataTable td:nth-child(5),
    #purData_wrapper table.dataTable td:nth-child(6) {
        text-align: right !important;
    }
}

/* Modal */
.purchase-tabs-page .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 12px;
    box-shadow: 0 15px 40px rgba(15, 23, 42, .22);
}

.purchase-tabs-page .modal-header {
    border-bottom: 1px solid #e8eef5;
    background: #f8fafc;
}

/* =========================================================
   Tablet
   ========================================================= */

@media (max-width: 1024px) {
    .purchase-tabs-page .erp-scroll-hint {
        display: block;
    }

    .purchase-tabs-page .erp-summary-four {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .purchase-tabs-page .erp-summary-three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .purchase-tabs-page .erp-payment-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* =========================================================
   Mobile compact layout
   ========================================================= */

@media (max-width: 767px) {
    .purchase-tabs-page {
        padding: 8px !important;
    }

    .purchase-tabs-page .erp-page-header {
        padding: 12px;
    }

    .purchase-tabs-page .erp-title-icon {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
        border-radius: 9px;
        font-size: 16px;
    }

    .purchase-tabs-page .erp-title-text h3 {
        font-size: 16px;
    }

    .purchase-tabs-page .erp-tabs-wrap {
        padding: 8px 8px 0;
    }

    .purchase-tabs-page ul.nav.nav-tabs.erp-tabs {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 5px !important;
        padding: 4px !important;
    }

    .purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li > a {
        min-height: 40px !important;
        padding: 7px 5px !important;
        gap: 4px !important;
        font-size: 15px !important;
        line-height: 1.3 !important;
    }

    .purchase-tabs-page .tab-count {
        min-width: 20px;
        padding: 1px 5px;
        font-size: 16px;
    }

    .purchase-tabs-page .erp-tab-content {
        padding: 8px;
    }

    /* Four sales cards = 2 x 2 */
    .purchase-tabs-page .erp-summary-four {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 5px !important;
        margin: 15px 0;
    }

    /* Due cards = 2 + full width last card */
    .purchase-tabs-page .erp-summary-three {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 5px !important;
        margin-bottom: 7px !important;
    }

    .purchase-tabs-page .erp-summary-three .erp-summary-cell:last-child {
        grid-column: 1 / -1 !important;
    }

    .purchase-tabs-page .erp-stat-card {
        min-height: 72px !important;
        height: 72px !important;
        padding: 10px 11px 8px !important;
        border-radius: 7px !important;
        box-shadow: none !important;
    }

    .purchase-tabs-page .erp-stat-card::before {
        width: 3px !important;
    }

    .purchase-tabs-page .erp-stat-label {
        margin: 0 0 2px !important;
        font-size: 16px !important;
        line-height: 1.35 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .purchase-tabs-page .erp-stat-value {
        padding: 0 !important;
        font-size: 18px !important;
        line-height: 1.3 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .purchase-tabs-page .erp-stat-icon {
        display: none !important;
    }

    /* Keep three filters in one row on mobile */
    .purchase-tabs-page .erp-filter-card {
        padding: 11px;
        margin-bottom: 15px;
    }

    .purchase-tabs-page .erp-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 9px !important;
    }

    .purchase-tabs-page .erp-filter-card label {
        min-height: 20px;
        margin-bottom: 5px;
        font-size: 16px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .purchase-tabs-page .erp-filter-card .form-control,
    .purchase-tabs-page .erp-filter-card .select2-container .select2-choice,
    .purchase-tabs-page .erp-filter-card .select2-container--default .select2-selection--single {
        height: 38px !important;
        min-height: 38px !important;
        font-size: 16px !important;
    }

    .purchase-tabs-page .erp-filter-card .select2-container .select2-choice,
    .purchase-tabs-page .erp-filter-card .select2-container .select2-choice > .select2-chosen,
    .purchase-tabs-page .erp-filter-card .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        line-height: 36px !important;
    }

    .purchase-tabs-page .erp-filter-card .select2-container .select2-choice .select2-arrow,
    .purchase-tabs-page .erp-filter-card .select2-container--default
    .select2-selection--single
    .select2-selection__arrow {
        height: 36px !important;
    }

    /* Due payment section stacked */
    .purchase-tabs-page .erp-payment-grid {
        grid-template-columns: 1fr !important;
        gap: 8px !important;
    }

    /* Compact toolbar */
    .purchase-tabs-page .erp-toolbar {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(135px, .9fr);
        gap: 6px;
        min-height: 0;
        padding: 7px;
    }

    .purchase-tabs-page .erp-toolbar-left,
    .purchase-tabs-page .erp-toolbar-right {
        width: 100%;
        min-width: 0;
        min-height: 38px;
        margin: 0;
        flex-wrap: nowrap;
    }

    .purchase-tabs-page .erp-toolbar-left {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .purchase-tabs-page .dt-buttons {
        flex-wrap: nowrap;
        gap: 3px;
    }

    .purchase-tabs-page .dt-buttons .btn,
    .purchase-tabs-page .dt-buttons .dt-button {
        flex: 0 0 auto;
        padding: 7px 10px !important;
        font-size: 16px !important;
        white-space: nowrap;
    }

    .purchase-tabs-page .erp-toolbar-right {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 5px;
    }

    .purchase-tabs-page .erp-search-box {
        width: 100%;
        min-width: 0;
    }

    .purchase-tabs-page .erp-search-box .form-control {
        height: 38px;
        padding-left: 34px;
        font-size: 16px;
    }

    .purchase-tabs-page .erp-search-box i {
        left: 11px;
        font-size: 16px;
    }

    .purchase-tabs-page .erp-toolbar .btn {
        min-height: 38px;
        padding: 7px 10px;
        font-size: 16px;
        white-space: nowrap;
    }

    .purchase-tabs-page .erp-scroll-hint {
        margin-bottom: 7px;
        font-size: 16px;
        margin-top:18px;
    }

    .purchase-tabs-page table.dataTable thead th,
    .purchase-tabs-page table.dataTable tbody td,
    .purchase-tabs-page table.dataTable tfoot th {
        padding: 7px 6px !important;
        font-size: 16px !important;
    }

    .purchase-tabs-page .dataTables_info {
        font-size: 16px;
    }

    .purchase-tabs-page .pagination > li > a,
    .purchase-tabs-page .pagination > li > span {
        padding: 7px 10px;
        font-size: 16px;
    }
}

@media (max-width: 360px) {
    .purchase-tabs-page .erp-stat-value {
        font-size: 16px !important;
    }

    .purchase-tabs-page ul.nav.nav-tabs.erp-tabs > li > a {
        font-size: 16px !important;
    }

    .purchase-tabs-page .erp-toolbar {
        grid-template-columns: 1fr;
    }
}

/* =========================================================
   Mobile Summary Toggle + Hide DataTable Export Buttons
   ========================================================= */
.purchase-tabs-page .mobile-summary-toggle {
    display: none;
}

@media (max-width: 767px) {
    .purchase-tabs-page .mobile-summary-toggle {
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
        font-size: 16px;
        font-weight: 800;
        line-height: 1.3;
        box-shadow: none;
        margin:15px 0px ;
    }

    .purchase-tabs-page .mobile-summary-toggle:hover,
    .purchase-tabs-page .mobile-summary-toggle:focus {
        border-color: #2f8191;
        background: #eef7f8;
        color: #2f8191;
        outline: 0;
    }

    .purchase-tabs-page .mobile-summary-toggle .toggle-left {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .purchase-tabs-page .mobile-summary-toggle .toggle-icon {
        flex: 0 0 auto;
    }

    /* Only the filter card is collapsed on the Sales tab */
    .purchase-tabs-page .erp-filter-card.mobile-filter-collapsed {
        display: none !important;
    }

    /* Due summary cards may still use the existing summary toggle */
    .purchase-tabs-page .erp-summary-grid.mobile-summary-collapsed {
        display: none !important;
    }

    .purchase-tabs-page .erp-summary-grid:not(.mobile-summary-collapsed) {
        display: grid !important;
    }

    /* Hide Copy / Excel / CSV / PDF / Columns buttons on mobile */
    .purchase-tabs-page .erp-toolbar-left {
        display: none !important;
    }

    /* Search and refresh use the whole toolbar width */
    .purchase-tabs-page .erp-toolbar {
        display: block !important;
        min-height: 0 !important;
        padding: 7px !important;
    }

    .purchase-tabs-page .erp-toolbar-right {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto !important;
        gap: 5px !important;
        width: 100% !important;
        min-height: 32px !important;
        margin: 0 !important;
    }

    .purchase-tabs-page .erp-search-box {
        width: 100% !important;
        min-width: 0 !important;
    }
}


.purchase-tabs-page #refreshPurchases,
.purchase-tabs-page #refreshDuePurchases {
    position: relative;
    z-index: 10;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    pointer-events: auto !important;
    touch-action: manipulation;
    text-decoration: none !important;
}


/* Mobile readability improvements */
@media (max-width: 767px) {
    .purchase-tabs-page .erp-summary-four {
        gap: 8px !important;
        margin-bottom: 20px !important;
    }

    .purchase-tabs-page .mobile-filter-toggle {
        margin: 10px 0 1px !important;
        padding: 11px 12px !important;
        font-size: 16px !important;
    }

    .purchase-tabs-page .erp-filter-card .select2-container .select2-choice > .select2-chosen,
    .purchase-tabs-page .erp-filter-card .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        font-size: 16px !important;
    }

    .purchase-tabs-page .erp-search-box .form-control,
    .purchase-tabs-page .erp-toolbar .btn,
    .purchase-tabs-page .dataTables_info,
    .purchase-tabs-page .pagination > li > a,
    .purchase-tabs-page .pagination > li > span {
        font-size: 16px !important;
    }

    .purchase-tabs-page .sale_status,
    .purchase-tabs-page .preorder_status,
    .purchase-tabs-page .deliver_preorder {
        font-size: 16px !important;
    }
}

/* =========================================================
   Product-style Purchase Action Buttons
   ========================================================= */

.purchase-tabs-page .erp-actions {
    width: 100%;
    min-width: 126px;
    white-space: normal !important;
}

.purchase-tabs-page .erp-action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: nowrap;
    gap: 6px;
    width: 100%;
}

.purchase-tabs-page .erp-action-icon-btn {
    display: inline-flex !important;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    border: 1px solid #d7e0e8;
    border-radius: 7px;
    background: #f8fafc;
    color: #526579 !important;
    box-shadow: none !important;
    font-size: 16px;
    line-height: 1;
    text-align: center;
    text-decoration: none !important;
    cursor: pointer;
    position: relative;
    z-index: 1;
    transform-origin: center center;
    transition: transform 0.16s ease, box-shadow 0.16s ease,
                border-color 0.16s ease, background-color 0.16s ease,
                color 0.16s ease;
    touch-action: manipulation;
}

.purchase-tabs-page .erp-action-icon-btn i {
    margin: 0 !important;
    font-size: 16px;
    line-height: 1;
}

.purchase-tabs-page .erp-action-icon-btn:hover,
.purchase-tabs-page .erp-action-icon-btn:focus {
    transform: translateY(-2px) scale(1.28);
    z-index: 20;
    box-shadow: 0 7px 18px rgba(35, 55, 80, 0.20) !important;
    outline: 0;
    text-decoration: none !important;
}

/* Touch/click လုပ်နေချိန်မှာလည်း button ကို ထင်ရှားစေမည်။ */
.purchase-tabs-page .erp-action-icon-btn:active {
    transform: translateY(-1px) scale(1.18);
    z-index: 20;
    box-shadow: 0 4px 12px rgba(35, 55, 80, 0.18) !important;
    outline: 0;
    text-decoration: none !important;
}

.purchase-tabs-page .erp-action-icon-btn:hover i,
.purchase-tabs-page .erp-action-icon-btn:focus i {
    font-size: 17px;
}

.purchase-tabs-page .erp-action-view {
    border-color: #bcd8ff;
    background: #f2f7ff;
    color: #2563eb !important;
}

.purchase-tabs-page .erp-action-view:hover,
.purchase-tabs-page .erp-action-view:focus {
    border-color: #8dbdff;
    background: #e8f1ff;
}

.purchase-tabs-page .erp-action-payments {
    border-color: #f5d79e;
    background: #fff9ec;
    color: #d97706 !important;
}

.purchase-tabs-page .erp-action-payments:hover,
.purchase-tabs-page .erp-action-payments:focus {
    border-color: #edc066;
    background: #fff4d9;
}

.purchase-tabs-page .erp-action-add-payment {
    border-color: #b9e3c8;
    background: #f1fbf5;
    color: #15803d !important;
}

.purchase-tabs-page .erp-action-add-payment:hover,
.purchase-tabs-page .erp-action-add-payment:focus {
    border-color: #8fd0a8;
    background: #e6f8ed;
}

.purchase-tabs-page .erp-action-receive {
    border-color: #ef4444;
    background: #ef4444;
    color: #ffffff !important;
}

.purchase-tabs-page .erp-action-received-disabled {
    border-color: #16a34a;
    background: #16a34a;
    color: #ffffff !important;
}

.purchase-tabs-page .erp-action-receive:hover,
.purchase-tabs-page .erp-action-receive:focus {
    border-color: #b91c1c;
    background: #dc2626;
    color: #ffffff !important;
}

.purchase-tabs-page .erp-action-delete {
    border-color: #f4c1c6;
    background: #fff5f6;
    color: #e54856 !important;
}

.purchase-tabs-page .erp-action-delete:hover,
.purchase-tabs-page .erp-action-delete:focus {
    border-color: #ee929c;
    background: #ffeaec;
}

.purchase-tabs-page .erp-action-default {
    border-color: #d7e0e8;
    background: #f8fafc;
    color: #526579 !important;
}

.purchase-tabs-page .erp-action-received-disabled {
    cursor: default !important;
    
    opacity: 1 !important;
}

@media (max-width: 767px) {
    

    .purchase-tabs-page .erp-action-buttons {
        flex-wrap: nowrap;
        gap: 5px;
    }

    .purchase-tabs-page .erp-action-icon-btn {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
    }
}
.purchase-tabs-page .erp-action-receive-partial {
    border-color: #f59e0b !important;
    background: #f59e0b !important;
    color: #ffffff !important;
}

.purchase-tabs-page .erp-action-receive-partial:hover,
.purchase-tabs-page .erp-action-receive-partial:focus {
    border-color: #d97706 !important;
    background: #d97706 !important;
    color: #ffffff !important;
}

/* Purchase list label/icon meanings */
.purchase-tabs-page .purchase-label-meaning {
    margin: 14px 0 0;
    padding: 12px 14px 14px;
    border: 1px solid #d7e3ef;
    border-radius: 12px;
    background: #f8fbff;
}

.purchase-tabs-page .purchase-label-meaning-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: #172033;
    font-weight: 700;
    font-size: 15px;
}

.purchase-tabs-page .purchase-label-meaning-groups {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
    gap: 8px;
}

.purchase-tabs-page .purchase-label-group {
    display: contents;
}

.purchase-tabs-page .purchase-label-item {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    min-height: 46px;
    padding: 7px 9px;
    border: 1px solid #e1eaf3;
    border-radius: 9px;
    background: #ffffff;
    color: #27364a;
    font-size: 13px;
    line-height: 1.35;
    white-space: normal;
}

.purchase-tabs-page .purchase-label-item > span:last-child {
    min-width: 0;
    overflow-wrap: anywhere;
}

.purchase-tabs-page .purchase-label-item .sale_status {
    min-width: 52px;
    text-align: center;
    flex-shrink: 0;
}

.purchase-tabs-page .purchase-label-item .erp-action-icon-btn {
    width: 30px;
    height: 30px;
    flex-basis: 30px;
    flex-shrink: 0;
    pointer-events: none;
}

@media (max-width: 767px) {
    .purchase-tabs-page .purchase-label-meaning {
        padding: 11px 10px 12px;
    }

    .purchase-tabs-page .purchase-label-meaning-groups {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .purchase-tabs-page .purchase-label-item {
        padding: 7px 8px;
        font-size: 12px;
    }
}

@media (max-width: 380px) {
    .purchase-tabs-page .purchase-label-meaning-groups {
        grid-template-columns: minmax(0, 1fr);
    }
}

/* Product Purchase summary: secondary unit removed => always 3 cards */
#productPurchaseSummaryGrid.erp-summary-three {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
}

#productPurchaseSummaryGrid.erp-summary-three .erp-summary-cell:last-child {
    grid-column: auto !important;
}


#productPurchaseData_wrapper table.dataTable td:nth-child(4) {
    white-space: nowrap !important;
}

/* =========================================================
   Supplier Due Help Toggle
   ========================================================= */
.purchase-tabs-page .erp-supplier-selector-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.purchase-tabs-page
.erp-supplier-selector-title-row
.erp-supplier-selector-title {
    margin: 0;
}

.purchase-tabs-page .supplier-due-help-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    padding: 0;
    border: 1px solid #93c5fd;
    border-radius: 50%;
    background: #eff6ff;
    color: #2563eb;
    font-size: 19px;
    cursor: pointer;
    transition: background .16s ease, color .16s ease,
        border-color .16s ease, transform .16s ease;
}

.purchase-tabs-page .supplier-due-help-toggle:hover,
.purchase-tabs-page .supplier-due-help-toggle:focus {
    border-color: #60a5fa;
    background: #dbeafe;
    color: #1d4ed8;
    outline: 0;
    transform: translateY(-1px);
}

.purchase-tabs-page .supplier-due-help-toggle.is-active {
    border-color: #fca5a5;
    background: #fff1f2;
    color: #dc2626;
}

/* မူလတွင် help စာသားအားလုံးကို ဖျောက်ထားမည်။ */
.purchase-tabs-page
#supplier-payment-tab
.supplier-due-help-item {
    display: none !important;
}

.purchase-tabs-page
#supplier-payment-tab.supplier-due-help-open
.supplier-due-help-item {
    display: block !important;
}

.purchase-tabs-page
#supplier-payment-tab.supplier-due-help-open
.erp-supplier-selector-note {
    margin-top: 8px;
}

@media (max-width: 575px) {
    .purchase-tabs-page .supplier-due-help-toggle {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
        font-size: 18px;
    }
}

/* =========================================================
   Due Table Checkbox Column - No Sorting
   ========================================================= */
.purchase-tabs-page
#dueData_wrapper
table.dataTable
thead
th.due-checkbox-column,
.purchase-tabs-page
#dueData
thead
th.due-checkbox-column {
    background-image: none !important;
    cursor: default !important;
}

.purchase-tabs-page
#dueData_wrapper
table.dataTable
thead
th.due-checkbox-column::before,
.purchase-tabs-page
#dueData_wrapper
table.dataTable
thead
th.due-checkbox-column::after,
.purchase-tabs-page
#dueData
thead
th.due-checkbox-column::before,
.purchase-tabs-page
#dueData
thead
th.due-checkbox-column::after {
    display: none !important;
    content: none !important;
}

</style>

<?php if ($is_app_mode) { ?>
<style>
.main-header,
.main-sidebar,
.main-footer,
.control-sidebar,
.breadcrumb,
.content-header {
    display: none !important;
}

.content-wrapper,
.right-side {
    min-height: 100vh !important;
    margin-left: 0 !important;
    padding-top: 0 !important;
    background: #f4f7fb !important;
}

.content.purchase-tabs-page {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.purchase-tabs-page .erp-shell {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    border-radius: 12px !important;
    box-shadow: none !important;
}

body,
html {
    min-height: 100% !important;
    background: #f4f7fb !important;
}
/* Product Purchase မှာ တစ်မျက်နှာပဲရှိရင် Paging မပြပါ */
#productPurchaseData_wrapper.product-one-page
.dataTables_paginate {
    display: none !important;
}
</style>
<?php } ?>

<script>
var purchaseTable = null;
var productPurchaseTable = null;
var dueTable = null;
</script>

<script type="text/javascript">
window.togglePurchaseFilter = function (button) {
    if (!button) {
        return false;
    }

    var targetSelector = button.getAttribute('data-target');
    var panel = targetSelector
        ? document.querySelector(targetSelector)
        : null;

    if (!panel) {
        return false;
    }

    var isCollapsed = panel.classList.contains(
        'mobile-filter-collapsed'
    );

    var icon = button.querySelector('.toggle-icon');

    if (isCollapsed) {
        panel.classList.remove('mobile-filter-collapsed');
        button.setAttribute('aria-expanded', 'true');

        if (icon) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    } else {
        panel.classList.add('mobile-filter-collapsed');
        button.setAttribute('aria-expanded', 'false');

        if (icon) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }

    window.setTimeout(function () {
        try {
            if (
                window.purchaseTable &&
                window.purchaseTable.columns
            ) {
                window.purchaseTable.columns.adjust();
            }

            if (
                window.productPurchaseTable &&
                window.productPurchaseTable.columns
            ) {
                window.productPurchaseTable.columns.adjust();
            }
        } catch (error) {}
    }, 60);

    return false;
};

window.togglePurchaseSummary = function (button) {
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

    var showLabel =
        button.getAttribute('data-show-label') || '';

    var hideLabel =
        button.getAttribute('data-hide-label') || '';

    if (isCollapsed) {
        summary.classList.remove('mobile-summary-collapsed');
        button.setAttribute('aria-expanded', 'true');

        if (label) {
            label.textContent = hideLabel;
        }

        if (icon) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    } else {
        summary.classList.add('mobile-summary-collapsed');
        button.setAttribute('aria-expanded', 'false');

        if (label) {
            label.textContent = showLabel;
        }

        if (icon) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }

    window.setTimeout(function () {
        try {
            if (window.purchaseTable && window.purchaseTable.columns) {
                window.purchaseTable.columns.adjust();
            }

            if (window.dueTable && window.dueTable.columns) {
                window.dueTable.columns.adjust();
            }
        } catch (error) {}
    }, 60);

    return false;
};

$(document).ready(function () {

    function initialisePurchaseSelect2(forceRebuild) {
        if (!$.fn.select2) {
            console.error('KLSPOS Purchase Listing: Select2 library is not loaded.');
            return;
        }

        $('.purchase-tabs-page select.erp-select2').each(function () {
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

            /*
             * Remove stale Select2 containers before rebuilding.
             */
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
                /*
                 * Compatibility fallback for older KLSPOS Select2.
                 */
                $select.select2();
                $select.next('.select2-container').css('width', '100%');
            }
        });
    }

    /*
     * Build Select2 after KLSPOS global ready handlers finish.
     */
    window.setTimeout(function () {
        initialisePurchaseSelect2(true);
    }, 100);

    if (get('remove_spo')) {
        if (get('spoitems')) {
            remove('spoitems');
        }
        remove('remove_spo');
    }

    <?php if ($this->session->userdata('remove_spo')) { ?>
        if (get('spoitems')) {
            remove('spoitems');
        }
        <?php $this->tec->unset_data('remove_spo'); ?>
    <?php } ?>

    function statusBadge(data) {
        var paid = '<?= lang('payment_paid'); ?>';
        var partial = '<?= lang('payment_partial'); ?>';
        var due = '<?= lang('payment_due'); ?>';

        if (data === 'paid') {
            return '<div class="text-center"><span class="sale_status label label-success">' + paid + '</span></div>';
        }

        if (data === 'partial') {
            return '<div class="text-center"><span class="sale_status label label-primary">' + partial + '</span></div>';
        }

        if (data === 'due') {
            return '<div class="text-center"><span class="sale_status label label-danger">' + due + '</span></div>';
        }

        return '<div class="text-center"><span class="sale_status label label-default">' + (data || '') + '</span></div>';
    }

    function renderReceivedAction(value, type, row) {
        var receiveStatus = parseInt(value || 0, 10);
        var isReceived = receiveStatus === 1;
        var isPartial  = receiveStatus === 2;

        if (type && type !== 'display') {
            return receiveStatus;
        }

        /*
         * Fully received:
         * အရင်ကလို အစိမ်းရောင် square check button
         */
         
        if (isReceived) {
            var purchaseId = row && row.id
                ? parseInt(row.id, 10)
                : 0;
            var receiveHref =
                purchaseActionConfig.viewReceivedUrl +
                purchaseId +
                purchaseActionConfig.receivePageQuery;
            
            return '<div class="text-center">' +
                '<a ' +
                    'href="' + escapeActionHtml(receiveHref) + '" ' +
                    'class="erp-action-icon-btn erp-action-received-disabled" ' +
                    'data-toggle="ajax" ' +
                    'title="အကုန်လက်ခံပြီး"' +
                    'aria-label="အကုန်လက်ခံပြီး">' +
                        '<i class="fa fa-check"></i>' +
                '</a>'+
            '</div>';
        }

        /*
         * Purchase ID ကို row ကနေ တိုက်ရိုက်ယူမယ်။
         * Action column ထဲက receive link ကို မမှီတော့ပါ။
         */
        var purchaseId = row && row.id
            ? parseInt(row.id, 10)
            : 0;

        if (!purchaseId) {
            return '<div class="text-center"></div>';
        }

        var receiveHref =
            purchaseActionConfig.receivePageUrl +
            purchaseId +
            purchaseActionConfig.receivePageQuery;

        /*
         * Partial = orange square
         * Not received = red square
         *
         * Button position / size က အရင် style အတိုင်းပါ။
         * နှိပ်မှ Receive page အသစ်သို့ သွားပါမယ်။
         */
        var buttonClass = isPartial
            ? 'erp-action-icon-btn erp-action-receive-partial'
            : 'erp-action-icon-btn erp-action-receive';

        var titleText = isPartial
            ? 'တစိတ်တပိုင်းလက်ခံပြီး - ကျန်ပစ္စည်းလက်ခံရန်'
            : purchaseActionConfig.receiveText;

        var iconHtml = isPartial
            ? '<i class="fa fa-hourglass-half"></i>'
            : '<i class="fa fa-check"></i>';

        return '<div class="text-center">' +
            '<a ' +
                'href="' + escapeActionHtml(receiveHref) + '" ' +
                'class="' + buttonClass + '" ' +
                'title="' + escapeActionHtml(titleText) + '" ' +
                'aria-label="' + escapeActionHtml(titleText) + '">' +
                iconHtml +
            '</a>' +
        '</div>';
    }

    /*
     * Controller က permission အလိုက် ပို့လာသော Action links ကိုယူပြီး
     * Product Listing ပုံစံ icon buttons အဖြစ် ပြန်ပြမည်။
     */
    var purchaseActionConfig = {
    viewUrl:
        <?= json_encode(site_url('purchases/view/')); ?>,

    paymentsUrl:
        <?= json_encode(site_url('purchases/payments/')); ?>,

    addPaymentUrl:
        <?= json_encode(site_url('purchases/add_payment/')); ?>,

    deleteUrl:
        <?= json_encode(site_url('purchases/delete/')); ?>,

    viewText:
        <?= json_encode($mobile_action_labels['view_purchase']); ?>,

    paymentsText:
        <?= json_encode($mobile_action_labels['view_payments']); ?>,

    addPaymentText:
        <?= json_encode($mobile_action_labels['add_payment']); ?>,

    receiveText:
        <?= json_encode($mobile_action_labels['receive']); ?>,

    receivePageUrl:
        <?= json_encode(site_url('purchases/receive/')); ?>,

    receivePageQuery:
        <?= json_encode($app_query); ?>,

    receivedText:
        <?= json_encode($mobile_action_labels['received']); ?>,

    deleteText:
        <?= json_encode($mobile_action_labels['delete']); ?>,

    deleteConfirm:
        <?= json_encode($mobile_action_labels['delete_confirm']); ?>,
        
    viewReceivedUrl:
        <?= json_encode(site_url('purchases/view_received/')); ?>, 
    
    productReceiveEditPageQuery:
        <?= json_encode($app_query); ?>,
    
};

    function escapeActionHtml(value) {
        return $('<div>')
            .text(value === null || value === undefined ? '' : String(value))
            .html();
    }

    function renderResponsiveActions(data, row) {
        if (!data) {
            return '<div class="erp-actions"></div>';
        }

        /* Controller က permission အလိုက် ပို့လာသော links ကိုပဲ သုံးမည်။ */
        var $temp = $('<div>').html(data);

        /*
         * Receive action ကို Action column မှာ လုံးဝမပြပါ။
         * Controller မှာ receive-link class ရှိတာ/မရှိတာ နှစ်မျိုးလုံး
         * ကာနိုင်အောင် class + URL နှစ်မျိုးလုံးနဲ့ ဖယ်မယ်။
         */
        $temp
            .find('a.receive-link, a[href*="/receive/"]')
            .closest('li')
            .remove();

        $temp
            .find('a.receive-link, a[href*="/receive/"]')
            .remove();

        /* Add Payment ကို Purchase List Action ထဲတွင် မပြပါ။ */
        $temp.find('a[href*="/add_payment/"]').closest('li').remove();
        $temp.find('a[href*="/add_payment/"]').remove();

        var actionHtml = '<div class="erp-action-buttons">';

        $temp.find('a').each(function () {
            var $link = $(this);
            var href = $link.attr('href') || '#';
            if (purchaseActionConfig.receivePageQuery && href !== '#') {
                var actionUrl = new URL(href, window.location.href);
                if (actionUrl.origin === window.location.origin) {
                    var appParams = new URLSearchParams(purchaseActionConfig.receivePageQuery);
                    appParams.forEach(function (value, key) { actionUrl.searchParams.set(key, value); });
                    href = actionUrl.href;
                }
            }
            var originalClass = $link.attr('class') || '';
            var dataToggle = $link.attr('data-toggle') || '';
            var dataTarget = $link.attr('data-target') || '';
            var dataId = $link.attr('data-id') || '';
            var target = $link.attr('target') || '';

            var actionClass = 'erp-action-default';
            var title = $.trim($link.text()) || 'Action';
            var iconHtml = '<i class="fa fa-circle-o"></i>';

            /* Add Payment ကို Payments မတိုင်ခင် စစ်ရမည်။ */
            if (href.indexOf('/add_payment/') !== -1) {
                actionClass = 'erp-action-add-payment';
                title = purchaseActionConfig.addPaymentText;
                iconHtml = '<i class="fa fa-plus"></i>';
            }
            else if (href.indexOf('/payments/') !== -1) {
                actionClass = 'erp-action-payments';
                title = purchaseActionConfig.paymentsText;
                iconHtml = '<i class="fa fa-credit-card"></i>';
            }
            else if (href.indexOf('/view/') !== -1) {
                actionClass = 'erp-action-view';
                title = purchaseActionConfig.viewText;
                iconHtml = '<i class="fa fa-eye"></i>';
            }
            else if (
                href.indexOf('/delete/') !== -1 ||
                $link.hasClass('text-danger')
            ) {
                actionClass = 'erp-action-delete';
                originalClass += ' erp-delete-purchase';
                title = purchaseActionConfig.deleteText;
                iconHtml = '<i class="fa fa-trash"></i>';
            }
            else if ($link.find('i').length) {
                iconHtml = $('<div>')
                    .append($link.find('i').first().clone())
                    .html();
            }

            actionHtml +=
                '<a' +
                    ' href="' + escapeActionHtml(href) + '"' +
                    ' class="erp-action-icon-btn ' +
                        actionClass + ' ' +
                        escapeActionHtml(originalClass) + '"' +
                    ' title="' + escapeActionHtml(title) + '"' +
                    ' aria-label="' + escapeActionHtml(title) + '"' +
                    (dataToggle
                        ? ' data-toggle="' + escapeActionHtml(dataToggle) + '"'
                        : '') +
                    (dataTarget
                        ? ' data-target="' + escapeActionHtml(dataTarget) + '"'
                        : '') +
                    (dataId
                        ? ' data-id="' + escapeActionHtml(dataId) + '"'
                        : '') +
                    (target
                        ? ' target="' + escapeActionHtml(target) + '"'
                        : '') +
                '>' +
                    iconHtml +
                '</a>';
        });

        actionHtml += '</div>';

        return '<div class="erp-actions">' + actionHtml + '</div>';
    }
   
    function parseNumber(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        if (typeof value === 'number') {
            return value;
        }

        return parseFloat(
            (value + '')
                .replace(/,/g, '')
                .replace(/<[^>]*>/g, '')
        ) || 0;
    }

    function moneyText(value) {
        var formatted = cf(parseNumber(value));
        return $('<div>').html(formatted).text() || '0.00';
    }

    function filterPurchaseRows(rows) {
        var supplier = ($('#purchaseSupplierFilter').val() || '').toLowerCase();
        var status = $('#purchaseStatusFilter').val() || '';
        var received = $('#purchaseReceivedFilter').val() || '';

        return $.grep(rows, function (row) {
            if (supplier && String(row.name || '').toLowerCase() !== supplier) {
                return false;
            }

            if (status) {
                if (status === 'notpaid') {
                    if (row.status !== 'partial' && row.status !== 'due') {
                        return false;
                    }
                } else if (row.status !== status) {
                    return false;
                }
            }

            if (received !== '' && String(row.received) !== String(received)) {
                return false;
            }

            return true;
        });
    }

    function filterDueRows(rows) {
        return $.grep(rows, function (row) {
            return row.status === 'partial' || row.status === 'due';
        });
    }

    function updatePurchaseSummary(api) {
    $.ajax({
        url: '<?= site_url('purchases/get_purchases'); ?>',
        type: 'POST',
        dataType: 'json',

        data: {
            summary_request: 1,

            supplier_filter:
                $('#purchaseSupplierFilter').val() || '',

            status_filter:
                $('#purchaseStatusFilter').val() || '',

            received_filter:
                $('#purchaseReceivedFilter').val() || '',

            global_search:
                $('#purchaseSearch').val() || '',

            <?= $this->security->get_csrf_token_name(); ?>:
                '<?= $this->security->get_csrf_hash(); ?>'
        },

        success: function (json) {

            console.log(
                'Purchase Summary Response:',
                json
            );

            var summary =
                json && json.summary
                    ? json.summary
                    : {};

            var records =
                parseInt(summary.records, 10) || 0;

            var total =
                parseNumber(summary.total);

            var paid =
                parseNumber(summary.paid);

            var due =
                parseNumber(summary.due);

            $('#summary_records').text(records);

            $('#purchase_tab_count').text(records);

            $('#summary_total').text(
                moneyText(total)
            );

            $('#summary_paid').text(
                moneyText(paid)
            );

            $('#summary_due').text(
                moneyText(due)
            );

            /*
             * Footer ကို page 10 rows မဟုတ်ဘဲ
             * filtered records အားလုံးရဲ့ total ပြမည်
             */
            if (purchaseTable) {

                $(purchaseTable.column(3).footer())
                    .html(cf(total));

                $(purchaseTable.column(4).footer())
                    .html(cf(paid));

                $(purchaseTable.column(5).footer())
                    .html(cf(due));
            }
        },

        error: function (xhr, status, error) {

            console.error(
                'Purchase Summary AJAX Error:',
                status,
                error,
                xhr.responseText
            );
        }
    });
}

    function updateDueSummary(api) {
        var rows = api.rows({search: 'applied', page: 'all'}).data();
        var totalDue = 0;

        rows.each(function (row) {
            totalDue += parseNumber(row.balance);
        });

        $('#due_records').text(rows.length);
        $('#due_tab_count').text(rows.length);
        $('#due_total').text(moneyText(totalDue));
    }

    function updateSelectedDue() {
        var selectedDue = 0;
        var selectedCount = 0;
    
        $('#dueData .purchase_check:checked').each(function () {
            var rowData = dueTable
                .row($(this).closest('tr'))
                .data();
    
            if (rowData) {
                selectedDue += parseNumber(rowData.balance);
                selectedCount++;
            }
        });
    
        $('#selected_due_total').text(
            moneyText(selectedDue)
        );
    
        $('#selected_purchase_count').text(
            selectedCount
        );
    
        /*
         * အနည်းဆုံး Purchase တစ်ခုရွေးထားမှ
         * Pay button ကိုဖွင့်မည်။
         */
        $('#bulkPayBtn').prop(
            'disabled',
            selectedCount === 0
        );
}

    /*
     * Purchase table ကို Header/Body တစ်ခုတည်းဖြစ်စေရန် DataTables scrollX
     * မသုံးတော့ပါ။ အပြင်ဘက် .erp-table-wrap ကိုသာ horizontal scroll လုပ်မည်။
     */
    function syncPurchaseTableColumns() {
        var $wrapper = $('#purData_wrapper');
        var $table = $('#purData');

        if (!$wrapper.length || !$table.length) {
            return;
        }

        /*
         * Mobile မှာ horizontal scroll ရအောင် minimum width ထားပြီး၊
         * Tablet/Desktop မှာ မိဘ container အပြည့် ဖြန့်မည်။
         */
        var minimumWidths = [52, 105, 170, 120, 120, 120, 145, 125, 150];
        var minimumTotalWidth = 1107;
        var $outerWrap = $wrapper.closest('.erp-table-wrap');
        var availableWidth = Math.floor($outerWrap.innerWidth() || 0);
        var totalWidth = Math.max(minimumTotalWidth, availableWidth);
        var extraWidth = totalWidth - minimumTotalWidth;
        var expandWeights = [0, 0.05, 0.30, 0.13, 0.13, 0.13, 0.10, 0.06, 0.10];
        var widths = minimumWidths.slice();
        var allocatedExtra = 0;

        if (extraWidth > 0) {
            for (var widthIndex = 0; widthIndex < widths.length - 1; widthIndex++) {
                var addition = Math.floor(extraWidth * expandWeights[widthIndex]);

                widths[widthIndex] += addition;
                allocatedExtra += addition;
            }

            /* Rounding ကြောင့်ကျန်သည့် pixel များကို Action column ထဲထည့်မည်။ */
            widths[widths.length - 1] += extraWidth - allocatedExtra;
        }

        /* Single outer horizontal scroll only. */
        $wrapper.css({
            width: totalWidth + 'px',
            minWidth: totalWidth + 'px',
            maxWidth: 'none'
        });

        $outerWrap.css({
            width: '100%',
            maxWidth: '100%',
            overflowX: 'auto',
            overflowY: 'hidden'
        });

        $wrapper
            .find('.dataTables_scroll, .dataTables_scrollBody, .dataTables_scrollFoot')
            .css({
                width: totalWidth + 'px',
                minWidth: totalWidth + 'px',
                maxWidth: 'none'
            });

        var table = $table.get(0);

        table.style.setProperty('width', totalWidth + 'px', 'important');
        table.style.setProperty('min-width', totalWidth + 'px', 'important');
        table.style.setProperty('max-width', totalWidth + 'px', 'important');
        table.style.setProperty('table-layout', 'fixed', 'important');

        var $colgroup = $table.children('colgroup').first();

        if (!$colgroup.length) {
            $colgroup = $('<colgroup></colgroup>').prependTo($table);
        }

        var $cols = $colgroup.children('col');

        if ($cols.length !== widths.length) {
            $colgroup.empty();

            $.each(widths, function () {
                $colgroup.append('<col>');
            });

            $cols = $colgroup.children('col');
        }

        $cols.each(function (index) {
            var width = widths[index];

            this.style.setProperty('width', width + 'px', 'important');
            this.style.setProperty('min-width', width + 'px', 'important');
            this.style.setProperty('max-width', width + 'px', 'important');
        });

        $table.find('thead tr, tbody tr, tfoot tr').each(function () {
            $(this).children('th, td').each(function (index) {
                if (widths[index] === undefined) {
                    return;
                }

                var width = widths[index];

                this.style.setProperty('width', width + 'px', 'important');
                this.style.setProperty('min-width', width + 'px', 'important');
                this.style.setProperty('max-width', width + 'px', 'important');
                this.style.setProperty('box-sizing', 'border-box', 'important');
            });
        });
    }

    /*
     * DataTables scrollX သည် Header table ကိုသီးခြား clone လုပ်ထားနိုင်သည်။
     * Clone THEAD ကို Body table ထဲသို့ တကယ်ရွှေ့ပေါင်းပြီး DataTables settings
     * ထဲက horizontal scroll ကိုလည်း ပိတ်ထားမည်။ Header/Body သည် DOM table
     * တစ်ခုတည်းဖြစ်သွားသဖြင့် scroll position နှင့် column boundary မခွာနိုင်ပါ။
     */
    function mergePurchaseHeaderIntoBody(api) {
        var $wrapper = $('#purData_wrapper');

        if (!$wrapper.length) {
            return;
        }

        if (api && api.settings) {
            var settings = api.settings()[0];

            if (settings && settings.oScroll) {
                settings.oScroll.sX = '';
                settings.oScroll.sXInner = '';
                settings.oScroll.sY = '';
            }
        }

        var $bodyTable = $wrapper
            .find('.dataTables_scrollBody table#purData')
            .first();

        var $clonedHeader = $wrapper
            .find('.dataTables_scrollHead table thead')
            .first();

        if ($bodyTable.length && $clonedHeader.length) {
            $bodyTable.children('thead').remove();
            $bodyTable.prepend($clonedHeader.detach());

            $wrapper
                .find('.dataTables_scrollHead')
                .attr('aria-hidden', 'true')
                .hide();

            $wrapper.find('.dataTables_scrollBody').css({
                overflow: 'visible',
                maxHeight: 'none'
            });
        }
    }

    function schedulePurchaseTableColumnSync(api) {
        window.setTimeout(function () {
            mergePurchaseHeaderIntoBody(api);

            if (api) {
                api.columns.adjust();
            }

            /* columns.adjust() က colgroup ကိုရေးပြီးမှ ကျွန်ုပ်တို့ width တင်မည်။ */
            window.setTimeout(function () {
                mergePurchaseHeaderIntoBody(api);
                syncPurchaseTableColumns();
            }, 0);
        }, 30);
    }

    /*
     * Layout/global script က #purData ကို အရင် initialize လုပ်ထားနိုင်သည်။
     * အဟောင်းကို destroy မလုပ်ဘဲ option အသစ်ပေးလျှင် DataTables က
     * scrollX အဟောင်းနှင့် cloned header ကို ဆက်သုံးနိုင်သောကြောင့်
     * Purchase table instance ကို သေချာပြန်တည်ဆောက်ပါမည်။
     */
    var $purchaseTableElement = $('#purData');

    if (
        $.fn.DataTable &&
        $.fn.DataTable.isDataTable($purchaseTableElement.get(0))
    ) {
        $purchaseTableElement.DataTable().destroy();
    }

    purchaseTable = $purchaseTableElement.DataTable({
        dom: 'Brtip',
        serverSide: true,
        processing: true,
        paging: true,
        pageLength: 10,
        info: true,
        lengthChange: true,
        /* Older DataTables versions require an empty string to disable X scroll. */
        scrollX: '',
        scrollCollapse: false,
        autoWidth: false,
        responsive: false,
        columnDefs: [
            { targets: 0, width: '52px' },
            { targets: 1, width: '105px' },
            { targets: 2, width: '170px' },
            { targets: [3, 4, 5], width: '120px' },
            { targets: 6, width: '145px' },
            { targets: 7, width: '125px' },
            { targets: 8, width: '150px' }
        ],
        ajax: {
            url: '<?= site_url('purchases/get_purchases'); ?>',
            type: 'POST',
            data: function (data) {
                data.<?= $this->security->get_csrf_token_name(); ?> =
                    '<?= $this->security->get_csrf_hash(); ?>';

                data.supplier_filter = $('#purchaseSupplierFilter').val() || '';
                data.status_filter = $('#purchaseStatusFilter').val() || '';
                data.received_filter = $('#purchaseReceivedFilter').val() || '';
            }
        },
        buttons: [
            {
                extend: 'copyHtml5',
                footer: true,
                exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6, 7]}
            },
            {
                extend: 'excelHtml5',
                footer: true,
                exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6, 7]}
            },
            {
                extend: 'csvHtml5',
                footer: true,
                exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6, 7]}
            },
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6, 7]}
            },
            {
                extend: 'colvis',
                text: '<?= lang('columns'); ?>'
            }
        ],
        columns: [
            {data: 'id'},
            {
                data: 'date',
                render: function (data) {
                    return data ? data.substr(0, 10) : '';
                }
            },
            {data: 'name'},
            {data: 'total', render: currencyFormat, className: 'text-right'},
            {data: 'paid', render: currencyFormat, className: 'text-right'},
            {
                data: 'balance',
                orderable: false,
                searchable: false,
                render: currencyFormat,
                className: 'text-right'
            },
            {data: 'status', render: statusBadge},
            {
                data: 'received',
                render: function (data, type, row) {
                    return renderReceivedAction(data, type, row);
                }
            },
            {
                data: 'Actions',
                orderable: false,
                searchable: false,
                className: 'text-center pur-actions-cell',
                render: function (data, type, row) {
                    return renderResponsiveActions(data, row);
                }
            }
        ],
        footerCallback: function () {
            var api = this.api();
            var rows = api.rows({page: 'current'}).data();
            var total = 0;
            var paid = 0;
            var due = 0;

            rows.each(function (row) {
                total += parseNumber(row.total);
                paid += parseNumber(row.paid);
                due += parseNumber(row.balance);
            });

            $(api.column(3).footer()).html(cf(total));
            $(api.column(4).footer()).html(cf(paid));
            $(api.column(5).footer()).html(cf(due));

            updatePurchaseSummary(api);
        },
        drawCallback: function () {
            var api = this.api();
            schedulePurchaseTableColumnSync(api);
        },
        initComplete: function () {
            var api = this.api();

            api.buttons().container().appendTo('#purDataButtons');

            schedulePurchaseTableColumnSync(api);
        }
    });


    function productPurchaseQty(data, type) {
        var value = parseNumber(data);

        if (type === 'display' || type === 'filter') {
            return value % 1 === 0
                ? String(value)
                : value.toFixed(2);
        }

        return value;
    }

    /*
     * Product Purchase Quantity Display
     * Example:
     *   2.00 ဖာ + 5.00 ထုပ်
     *   39.00 Box
     *
     * Secondary quantity 0 ဖြစ်ရင် မပြပါ။
     */
    function productPurchaseCombinedQty(data, type, row) {

        var rawPrimary = $.trim(
            row && row.primary_qty !== undefined &&
            row.primary_qty !== null
                ? String(row.primary_qty)
                : ''
        );

        var rawSecondary = $.trim(
            row && row.secondary_qty !== undefined &&
            row.secondary_qty !== null
                ? String(row.secondary_qty)
                : ''
        );

        var primaryQty   = parseNumber(rawPrimary);
        var secondaryQty = parseNumber(rawSecondary);

        var primaryUnit = $.trim(
            row && row.primary_unit_name
                ? String(row.primary_unit_name)
                : ''
        );

        var secondaryUnit = $.trim(
            row && row.secondary_unit_name
                ? String(row.secondary_unit_name)
                : ''
        );

        /*
         * Sorting အတွက် numeric primary quantity ပဲသုံးမယ်။
         */
        if (
            type !== 'display' &&
            type !== 'filter'
        ) {
            return primaryQty;
        }

        /*
         * IMPORTANT:
         * Server က primary_qty ကို
         *
         *   "7.00 ထုပ်"
         *   "2.00 ဖာ + 5.00 ထုပ်"
         *
         * လို unit ပါပြီးသား string ပြန်ပေးနေပါက
         * parseNumber() မလုပ်ဘဲ အဲဒီစာသားကို တိုက်ရိုက်ပြမယ်။
         *
         * Screenshot Network Preview မှာ
         * primary_qty: "5.00 ထုပ်"
         * လို့ရောက်နေတာကို ထိန်းထားဖို့ပါ။
         */
        if (
            rawPrimary !== '' &&
            /[^0-9.,\s-]/.test(rawPrimary)
        ) {
            return rawPrimary;
        }

        function qtyText(value) {
            return parseNumber(value).toFixed(2);
        }

        var parts = [];

        if (
            primaryQty !== 0 ||
            primaryUnit
        ) {
            parts.push(
                qtyText(primaryQty) +
                (primaryUnit ? ' ' + primaryUnit : '')
            );
        }

        /*
         * Server က secondary_qty ကို unit ပါပြီးသား
         * string ပြန်ပေးထားရင်လည်း အဲဒီအတိုင်းထည့်မယ်။
         */
        if (
            rawSecondary !== '' &&
            /[^0-9.,\s-]/.test(rawSecondary)
        ) {
            parts.push(rawSecondary);
        }
        else if (secondaryQty > 0) {
            parts.push(
                qtyText(secondaryQty) +
                (secondaryUnit ? ' ' + secondaryUnit : '')
            );
        }

        if (!parts.length) {
            return rawPrimary || '0.00';
        }

        return parts.join(' + ');
    }

    var productPurchaseAppQuery =
        <?= json_encode(ltrim($app_query, '?')); ?>;

    function productPurchaseInAppUrl(href) {
        href = String(href || '');

        if (
            !href ||
            !productPurchaseAppQuery ||
            href.charAt(0) === '#' ||
            /^javascript:/i.test(href)
        ) {
            return href;
        }

        if (/(?:\?|&)app=1(?:&|$)/.test(href)) {
            return href;
        }

        var hash = '';
        var hashPosition = href.indexOf('#');

        if (hashPosition !== -1) {
            hash = href.substring(hashPosition);
            href = href.substring(0, hashPosition);
        }

        return href +
            (href.indexOf('?') === -1 ? '?' : '&') +
            productPurchaseAppQuery +
            hash;
    }

    function renderProductPurchaseName(data, type) {
        var $holder = $('<div>').html(data || '');

        if (type !== 'display') {
            return $.trim($holder.text());
        }

        $holder.find('a').each(function () {
            var $link = $(this);

            $link
                .removeAttr('target')
                .attr(
                    'href',
                    productPurchaseInAppUrl(
                        $link.attr('href') || ''
                    )
                );
        });

        return $holder.html();
    }

    function productPurchaseUrl() {
        var product = $('#productPurchaseProductFilter').val() || '';
        var startDate = $('#productPurchaseStartDate').val() || '';
        var endDate = $('#productPurchaseEndDate').val() || '';

        var query = [
            'v=1',
            'product=' + encodeURIComponent(product)
        ];

        <?php if ($is_app_mode): ?>
        query.push('app=1');
        query.push(
            'app_lang=' +
            encodeURIComponent(<?= json_encode($app_language); ?>)
        );
        <?php endif; ?>

        if (startDate) {
            query.push(
                'start_date=' +
                encodeURIComponent(startDate + ' 00:00:00')
            );
        }

        if (endDate) {
            query.push(
                'end_date=' +
                encodeURIComponent(endDate + ' 23:59:59')
            );
        }

        return '<?= site_url('reports/get_product_purchase'); ?>' +
            '?' + query.join('&');
    }

    function updateProductPurchaseSummary(api) {
        var rows = api.rows({search: 'applied', page: 'all'}).data();
        var primaryQty = 0;
        var purchaseAmount = 0;

        rows.each(function (row) {
            primaryQty += parseNumber(row.primary_qty);
            purchaseAmount += parseNumber(row.total_amount);
        });

        $('#product_purchase_records').text(rows.length);
        $('#product_purchase_primary_qty').text(
            productPurchaseQty(primaryQty, 'display')
        );
        $('#product_purchase_amount').text(
            moneyText(purchaseAmount)
        );
    }

    productPurchaseTable = $('#productPurchaseData').DataTable({
        dom: 'Brtip',
    
        /*
         * ဒီ Endpoint က စာရင်းအားလုံးကို data/aaData နဲ့ပြန်ပေးတာမို့
         * Client-side paging ကိုပဲသုံးရပါမယ်။
         */
        serverSide: false,
        processing: true,
        stateSave: false,
    
        paging: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
    
        info: true,
        lengthChange: true,
    
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        responsive: false,
        ordering: true,
        ajax: {
            url: productPurchaseUrl(),
            type: 'POST',
            data: function (data) {
                data.<?= $this->security->get_csrf_token_name(); ?> =
                    '<?= $this->security->get_csrf_hash(); ?>';
            },
            dataSrc: function (json) {
                var rows = [];
            
                if (json && $.isArray(json.data)) {
                    rows = json.data;
                } else if (json && $.isArray(json.aaData)) {
                    rows = json.aaData;
                } else if ($.isArray(json)) {
                    rows = json;
                }
            
                return rows;
            },
            error: function (xhr, status, error) {
                console.error(
                    'Product Purchase DataTable AJAX error:',
                    status,
                    error,
                    xhr.responseText
                );

                $('#productPurchaseData tbody').html(
                    '<tr>' +
                        '<td colspan="5" class="text-center text-danger">' +
                            '<i class="fa fa-exclamation-triangle"></i> ' +
                            'Product purchase data could not be loaded.' +
                        '</td>' +
                    '</tr>'
                );
            }
        },
        columnDefs: [
            {
                orderable: false,
                targets: 0,
                width: '52px'
            },
            {
                targets: 1,
                width: '125px'
            },
            {
                targets: 2,
                width: '175px'
            },
            {
                targets: 3,
                width: '155px'
            },
            {
                targets: 4,
                width: '145px'
            }
        ],
        buttons: [
            {
                extend: 'copyHtml5',
                footer: true,
                exportOptions: {columns: [0, 1, 2, 3, 4]}
            },
            {
                extend: 'excelHtml5',
                footer: true,
                exportOptions: {columns: [0, 1, 2, 3, 4]}
            },
            {
                extend: 'csvHtml5',
                footer: true,
                exportOptions: {columns: [0, 1, 2, 3, 4]}
            },
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {columns: [0, 1, 2, 3, 4]}
            },
            {
                extend: 'colvis',
                text: '<?= lang('columns'); ?>'
            }
        ],
        columns: [
            {
                data: null,
                defaultContent: '',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {data: 'product_code'},
            {
                data: 'product_name',
                render: renderProductPurchaseName
            },
            {
                data: 'primary_qty',
                className: 'text-right',
                render: function (data, type, row) {
                    return productPurchaseCombinedQty(
                        data,
                        type,
                        row
                    );
                }
            },
            {
                data: 'total_amount',
                className: 'text-right',
                render: currencyFormat
            }
        ],
        footerCallback: function () {
            var api = this.api();
            var primaryQty = 0;
            var purchaseAmount = 0;

            api.rows({search: 'applied', page: 'all'}).data().each(function (row) {
                primaryQty += parseNumber(row.primary_qty);
                purchaseAmount += parseNumber(row.total_amount);
            });

            $(api.column(3).footer()).html(
                productPurchaseQty(primaryQty, 'display')
            );

            $(api.column(4).footer()).html(
                cf(purchaseAmount)
            );

            updateProductPurchaseSummary(api);
        },
        drawCallback: function () {
            var api = this.api();
            var pageInfo = api.page.info();
        
            updateProductPurchaseSummary(api);
        
            /*
             * တစ်မျက်နှာထက်ပိုမှ Paging ပြပါမယ်။
             */
            $('#productPurchaseData_wrapper').toggleClass(
                'product-one-page',
                pageInfo.pages <= 1
            );
        },
        initComplete: function () {
            var api = this.api();

            api.buttons()
                .container()
                .appendTo('#productPurchaseDataButtons');

            api.columns.adjust();
        }
    });

    <?php if ($is_app_mode): ?>
    $(document)
        .off(
            'click.productPurchaseInApp',
            '#productPurchaseData tbody a'
        )
        .on(
            'click.productPurchaseInApp',
            '#productPurchaseData tbody a',
            function (event) {
                var href = productPurchaseInAppUrl(
                    $(this).attr('href') || ''
                );

                if (
                    !href ||
                    href.charAt(0) === '#' ||
                    /^javascript:/i.test(href)
                ) {
                    return;
                }

                event.preventDefault();
                window.location.href = href;
            }
        );
    <?php endif; ?>

    dueTable = $('#dueData').DataTable({
        dom: 'Brtip',
        paging: false,
        pageLength: 10,
        info: true,
        lengthChange: true,
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        responsive: false,
        order: [],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                className: 'text-center due-checkbox-column'
            }
        ],
        ajax: {
    url: '<?= site_url('purchases/get_purchases'); ?>',
    type: 'POST',

    data: function (data) {
        data.<?= $this->security->get_csrf_token_name(); ?> =
            '<?= $this->security->get_csrf_hash(); ?>';

        data.due_request = 1;

        data.supplier_id =
            $('#dueSupplierFilter').val() || '';

        data.status_filter = 'notpaid';

        /* Due summary ကိုလည်း မှတ်တမ်းအားလုံးဖြင့် တွက်မည်။ */
        data.start = 0;
        data.length = -1;
    },

    dataSrc: function (json) {
        if (!$('#dueSupplierFilter').val()) {
            return [];
        }

        return filterDueRows(json.data || []);
    }
},
        buttons: [
            {
                extend: 'copyHtml5',
                footer: true,
                exportOptions: {columns: [1, 2, 3, 4, 5, 6, 7]}
            },
            {
                extend: 'excelHtml5',
                footer: true,
                exportOptions: {columns: [1, 2, 3, 4, 5, 6, 7]}
            },
            {
                extend: 'csvHtml5',
                footer: true,
                exportOptions: {columns: [1, 2, 3, 4, 5, 6, 7]}
            },
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {columns: [1, 2, 3, 4, 5, 6, 7]}
            }
        ],
        columns: [
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data) {
                    return '<input type="checkbox" class="purchase_check" value="' + data + '">';
                }
            },
            {data: 'id'},
            {
                data: 'date',
                render: function (data) {
                    return data ? data.substr(0, 10) : '';
                }
            },
            {data: 'name'},
            {data: 'total', render: currencyFormat, className: 'text-right'},
            {data: 'paid', render: currencyFormat, className: 'text-right'},
            {
                data: 'balance',
                orderable: false,
                searchable: false,
                render: currencyFormat,
                className: 'text-right'
            },
            {data: 'status', render: statusBadge},
            
        ],
        footerCallback: function () {
            var api = this.api();
            var rows = api.rows({search: 'applied', page: 'all'}).data();
            var total = 0;
            var paid = 0;
            var due = 0;

            rows.each(function (row) {
                total += parseNumber(row.total);
                paid += parseNumber(row.paid);
                due += parseNumber(row.balance);
            });

            $(api.column(4).footer()).html(cf(total));
            $(api.column(5).footer()).html(cf(paid));
            $(api.column(6).footer()).html(cf(due));

            updateDueSummary(api);
        },
        drawCallback: function () {
            $('#dueData_wrapper, #dueData')
                .find('thead th:first-child')
                .removeClass('sorting sorting_asc sorting_desc')
                .addClass('sorting_disabled due-checkbox-column')
                .removeAttr('aria-sort');

            $('#checkAllDue').prop('checked', false);
            updateSelectedDue();
        },
        initComplete: function () {
            $('#dueData_wrapper, #dueData')
                .find('thead th:first-child')
                .removeClass('sorting sorting_asc sorting_desc')
                .addClass('sorting_disabled due-checkbox-column')
                .removeAttr('aria-sort');

            dueTable.buttons().container().appendTo('#dueDataButtons');
        }
    });
    
    

    $('#refreshPurchases').on('click', function () {
        if (purchaseTable) {
            purchaseTable.ajax.reload(null, false);
        }
    });


    $('#applyProductPurchaseFilter').on('click', function () {
        if (!productPurchaseTable) {
            return;
        }

        productPurchaseTable
            .ajax
            .url(productPurchaseUrl())
            .load();
    });

    $('#refreshProductPurchases').on('click', function () {
        if (!productPurchaseTable) {
            return;
        }

        productPurchaseTable
            .ajax
            .url(productPurchaseUrl())
            .load(null, false);
    });

    $('#productPurchaseSearch').on('keyup change', function (event) {
        var code = event.keyCode || event.which;

        if (
            (code == 13 &&
                productPurchaseTable.search() !== this.value) ||
            (
                productPurchaseTable.search() !== '' &&
                this.value === ''
            )
        ) {
            productPurchaseTable.search(this.value).draw();
        }
    });

    

    $('#purchaseSearch').on('keyup change', function (event) {
        var code = event.keyCode || event.which;

        if (
            (code == 13 && purchaseTable.search() !== this.value) ||
            (purchaseTable.search() !== '' && this.value === '')
        ) {
            purchaseTable.search(this.value).draw();
        }
    });

    $('#dueSearch').on('keyup change', function (event) {
        var code = event.keyCode || event.which;

        if (
            (code == 13 && dueTable.search() !== this.value) ||
            (dueTable.search() !== '' && this.value === '')
        ) {
            dueTable.search(this.value).draw();
        }
    });

    $('#purchaseSupplierFilter, #purchaseStatusFilter, #purchaseReceivedFilter')
        .on('change', function () {
            purchaseTable.ajax.reload();
        });

   

    $('#checkAllDue').on('change', function () {
        $('input.purchase_check', dueTable.rows({search: 'applied', page: 'all'}).nodes())
            .prop('checked', this.checked);

        updateSelectedDue();
    });

    $('#dueData').on('change', '.purchase_check', function () {
        updateSelectedDue();
    });

    $('#bulkPayBtn').on('click', function () {
        var amount = parseNumber($('#bulk_pay_amount').val());
        var ids = [];

        if (!amount || amount <= 0) {
            alert('<?= lang('enter_valid_payment_amount'); ?>');
            return;
        }

        $('#dueData .purchase_check:checked').each(function () {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            alert('<?= lang('select_at_least_one_purchase'); ?>');
            return;
        }

        if (!confirm('<?= lang('confirm_pay_selected_purchases'); ?>')) {
            return;
        }

        var postData = {
            amount: amount,
            purchase_ids: ids
        };

        postData['<?= $this->security->get_csrf_token_name(); ?>'] =
            '<?= $this->security->get_csrf_hash(); ?>';

        $.ajax({
            url: '<?= site_url('purchases/bulk_pay_due'); ?>',
            type: 'POST',
            dataType: 'json',
            data: postData,
            success: function (response) {
                if (response.status === 'success') {
                    alert('<?= lang('payment_completed'); ?>');
                    $('#bulk_pay_amount').val('');
                    $('#checkAllDue').prop('checked', false);
                    dueTable.ajax.reload(null, false);
                    purchaseTable.ajax.reload(null, false);
                } else {
                    alert(response.message || '<?= lang('payment_failed'); ?>');
                }
            },
            error: function () {
                alert('<?= lang('server_error'); ?>');
            }
        });
    });

    /*
     * Mobile delete confirmation.
     * Browser native confirm() ကို မသုံးသဖြင့် popup တွင် website URL မပေါ်ပါ။
     */
    var pendingPurchaseDeleteUrl = '';

    $(document).on('click', '.erp-delete-purchase', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        pendingPurchaseDeleteUrl = $(this).attr('href') || '';

        if (!pendingPurchaseDeleteUrl || pendingPurchaseDeleteUrl === '#') {
            return false;
        }

        $('#purchaseDeleteConfirmMessage').text(
            purchaseActionConfig.deleteConfirm
        );
        $('#purchaseDeleteConfirmModal').modal('show');

        return false;
    });

    $('#confirmPurchaseDeleteBtn').on('click', function () {
        var deleteUrl = pendingPurchaseDeleteUrl;
        var $button = $(this);

        if (!deleteUrl || $button.prop('disabled')) {
            return;
        }

        $button
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> ' +
                <?= json_encode($is_myanmar_action_language ? 'ဖျက်နေသည်...' : 'Deleting...'); ?>
            );

        var ajaxDeleteUrl = deleteUrl +
            (deleteUrl.indexOf('?') === -1 ? '?' : '&') +
            'ajax_delete=1';

        $.ajax({
            url: ajaxDeleteUrl,
            type: 'GET',
            /* JSON parser error ကို network error ဟု မှားမပြစေရန် text အဖြစ်ယူပြီး parse မည်။ */
            dataType: 'text',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (rawResponse) {
                var response = rawResponse;

                if (typeof rawResponse === 'string') {
                    try {
                        response = JSON.parse($.trim(rawResponse));
                    } catch (parseError) {
                        response = {
                            status: 'error',
                            message: <?= json_encode($is_myanmar_action_language ? 'Server မှ ပြန်လာသောအဖြေ မမှန်ကန်ပါ။ Controller ဖိုင်ကို အသစ်ပြောင်းထားကြောင်း စစ်ဆေးပါ။' : 'The server returned an invalid response. Please verify that the updated controller is installed.'); ?>
                        };
                    }
                }

                var deleteSucceeded = response && response.status === 'success';

                var resultMessage = response && response.message
                    ? response.message
                    : (deleteSucceeded
                        ? <?= json_encode($is_myanmar_action_language ? 'အဝယ်ဘောင်ချာကို အောင်မြင်စွာ ဖျက်ပြီးပါပြီ။' : 'The purchase was deleted successfully.'); ?>
                        : <?= json_encode($is_myanmar_action_language ? 'အဝယ်ဘောင်ချာကို ဖျက်၍မရပါ။' : 'The purchase could not be deleted.'); ?>);

                pendingPurchaseDeleteUrl = '';

                /* ပထမ modal ပိတ်ပြီးမှ result modal ဖွင့်ရမည်။ */
                $('#purchaseDeleteConfirmModal')
                    .one('hidden.bs.modal', function () {
                        showPurchaseDeleteResult(
                            deleteSucceeded,
                            resultMessage
                        );
                    })
                    .modal('hide');

                if (deleteSucceeded) {
                    if (purchaseTable) {
                        purchaseTable.ajax.reload(null, false);
                    }
                    if (productPurchaseTable) {
                        productPurchaseTable.ajax.reload(null, false);
                    }
                    if (dueTable) {
                        dueTable.ajax.reload(null, false);
                    }
                }
            },
            error: function (xhr) {
                var message = <?= json_encode($is_myanmar_action_language ? 'Server နှင့် ချိတ်ဆက်၍မရပါ။ ကျေးဇူးပြု၍ ထပ်မံကြိုးစားပါ။' : 'Could not connect to the server. Please try again.'); ?>;

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        var errorResponse = JSON.parse($.trim(xhr.responseText));
                        if (errorResponse && errorResponse.message) {
                            message = errorResponse.message;
                        }
                    } catch (ignoreParseError) {
                        /* Default connection message ကိုသာ ဆက်သုံးမည်။ */
                    }
                }

                pendingPurchaseDeleteUrl = '';
                $('#purchaseDeleteConfirmModal')
                    .one('hidden.bs.modal', function () {
                        showPurchaseDeleteResult(false, message);
                    })
                    .modal('hide');
            },
            complete: function () {
                $button
                    .prop('disabled', false)
                    .html('<i class="fa fa-trash"></i> ' +
                        <?= json_encode($is_myanmar_action_language ? 'ဖျက်မည်' : 'Delete'); ?>
                    );
            }
        });
    });

    $('#purchaseDeleteConfirmModal').on('hidden.bs.modal', function () {
        pendingPurchaseDeleteUrl = '';
    });

    /*
     * Delete ပြီး/မပြီး ရလဒ်ကို Listing ပြန်ရောက်ရောက်ချင်း ပြမည်။
     * ပြပြီးနောက် address bar မှ query flag ကိုရှင်းထားမည်။
     */
    var purchaseDeleteResult =
        <?= json_encode((string) $this->session->flashdata('purchase_delete_result')); ?>;

    function showPurchaseDeleteResult(deleteSucceeded, message) {
        $('#purchaseDeleteResultIcon')
            .removeClass('fa-check-circle text-success fa-times-circle text-danger')
            .addClass(
                deleteSucceeded
                    ? 'fa-check-circle text-success'
                    : 'fa-times-circle text-danger'
            );

        $('#purchaseDeleteResultTitle').text(
            deleteSucceeded
                ? <?= json_encode($is_myanmar_action_language ? 'ဖျက်ခြင်း အောင်မြင်ပါသည်' : 'Delete Successful'); ?>
                : <?= json_encode($is_myanmar_action_language ? 'ဖျက်ခြင်း မအောင်မြင်ပါ' : 'Delete Failed'); ?>
        );

        $('#purchaseDeleteResultMessage').text(message || '');
        $('#purchaseDeleteResultModal').modal('show');
    }

    if (
        purchaseDeleteResult === 'success' ||
        purchaseDeleteResult === 'failed'
    ) {
        var deleteSucceeded = purchaseDeleteResult === 'success';

        showPurchaseDeleteResult(
            deleteSucceeded,
            deleteSucceeded
                ? <?= json_encode($is_myanmar_action_language ? 'အဝယ်ဘောင်ချာကို အောင်မြင်စွာ ဖျက်ပြီးပါပြီ။' : 'The purchase was deleted successfully.'); ?>
                : <?= json_encode($is_myanmar_action_language ? 'အဝယ်ဘောင်ချာကို ဖျက်၍မရပါ။ ကျေးဇူးပြု၍ ထပ်မံစစ်ဆေးပါ။' : 'The purchase could not be deleted. Please check and try again.'); ?>
        );
    }
    


    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        initialisePurchaseSelect2(false);

        window.setTimeout(function () {
            if (purchaseTable) {
                purchaseTable.columns.adjust().draw(false);
                schedulePurchaseTableColumnSync(purchaseTable);
            }

            if (productPurchaseTable) {
                productPurchaseTable.columns.adjust().draw(false);
            }

            if (dueTable) {
                dueTable.columns.adjust().draw(false);
            }
        }, 80);
    });

    /*
     * Recalculate widths after tablet orientation or browser resizing.
     */
    $(window).on('resize orientationchange', function () {
        window.setTimeout(function () {
            if (purchaseTable) {
                purchaseTable.columns.adjust();
                schedulePurchaseTableColumnSync(purchaseTable);
            }

            if (productPurchaseTable) {
                productPurchaseTable.columns.adjust();
            }

            if (dueTable) {
                dueTable.columns.adjust();
            }
        }, 120);
    });
});
</script>
<script>
$(document).ready(function () {

    const $supplierFilter = $('#dueSupplierFilter');
    const $workspace      = $('#supplierDueWorkspace');
    const $emptyState     = $('#supplierDueEmptyState');
    const $supplierHelpTab = $('#supplier-payment-tab');
    const $supplierHelpToggle = $('#supplierDueHelpToggle');

    /*
     * Help icon နှိပ်မှ Supplier Due ရှင်းလင်းချက်များကို ပြမည်။
     * နောက်တစ်ကြိမ်နှိပ်လျှင် ပြန်ဖျောက်မည်။
     */
    $supplierHelpToggle
        .off('click.supplierDueHelp')
        .on('click.supplierDueHelp', function () {
            var isOpen = !$supplierHelpTab.hasClass(
                'supplier-due-help-open'
            );

            $supplierHelpTab.toggleClass(
                'supplier-due-help-open',
                isOpen
            );

            $(this)
                .attr('aria-expanded', isOpen ? 'true' : 'false')
                .attr(
                    'title',
                    isOpen
                        ? 'အကူအညီပိတ်ရန်'
                        : 'အကူအညီကြည့်ရန်'
                )
                .toggleClass('is-active', isOpen);

            $(this)
                .find('i')
                .toggleClass('fa-question-circle', !isOpen)
                .toggleClass('fa-times', isOpen);

            $(this)
                .find('.sr-only')
                .text(
                    isOpen
                        ? 'အကူအညီပိတ်ရန်'
                        : 'အကူအညီကြည့်ရန်'
                );
        });

    /**
     * Reset selected purchases and payment values.
     */
    function resetSupplierDueSelection() {
        $('#checkAllDue').prop('checked', false);

        $('#dueData tbody input[type="checkbox"]')
            .prop('checked', false);

        $('#selected_purchase_count').text('0');
        $('#selected_due_total').text('0.00');
        $('#bulk_pay_amount').val('');
        $('#bulkPayBtn').prop('disabled', true);
    }

    /**
     * Reset summary cards.
     */
    function resetSupplierDueSummary() {
        $('#due_records').text('0');
        $('#due_total').text('0.00');

        resetSupplierDueSelection();
    }

    /**
     * Get due DataTable instance safely.
     */
    function getDueDataTable() {
        if (
            $.fn.DataTable &&
            $.fn.DataTable.isDataTable('#dueData')
        ) {
            return $('#dueData').DataTable();
        }

        return null;
    }

    /**
     * Show or hide supplier due workspace.
     */
    function updateSupplierDueWorkspace() {
        const supplierId = $supplierFilter.val();
        const dueTable   = getDueDataTable();

        resetSupplierDueSummary();

        if (!supplierId) {
            $workspace.hide();
            $emptyState.show();
            return;
        }

        $emptyState.hide();
        $workspace.show();

        /*
         * Server-side DataTable ဖြစ်ပါက supplier_id ပါအောင်
         * Ajax reload လုပ်ပါမည်။
         */
        if (dueTable && dueTable.ajax) {
            dueTable.ajax.reload(function () {
                dueTable.columns.adjust();

                if (dueTable.responsive) {
                    dueTable.responsive.recalc();
                }
            }, true);
        }
    }

    /**
     * Supplier changed.
     */
    $supplierFilter.on('change', function () {
        updateSupplierDueWorkspace();
    });

    /**
     * Refresh current supplier data only.
     */
    $('#refreshDuePurchases').on('click', function () {
        const supplierId = $supplierFilter.val();
        const dueTable   = getDueDataTable();

        if (!supplierId) {
            return;
        }

        resetSupplierDueSelection();

        if (dueTable && dueTable.ajax) {
            dueTable.ajax.reload(null, false);
        }
    });

    /**
     * Initial state.
     */
    updateSupplierDueWorkspace();
});
</script>
<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content purchase-tabs-page kls-mobile-ui">
    <div class="erp-shell">

        

        <div class="erp-tabs-wrap">
            <ul class="nav nav-tabs erp-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a
                        href="#purchase-list-tab"
                        aria-controls="purchase-list-tab"
                        role="tab"
                        data-toggle="tab"
                    >
                        
                        <?= html_escape(lang('purchase_voucher_list')); ?>
                        
                    </a>
                </li>

                <li role="presentation">
                    <a
                        href="#product-purchase-tab"
                        aria-controls="product-purchase-tab"
                        role="tab"
                        data-toggle="tab"
                    >
                        <?= html_escape(lang('purchase_product_list')); ?>
                    </a>
                </li>

                <li role="presentation">
                    <a
                        href="#supplier-payment-tab"
                        aria-controls="supplier-payment-tab"
                        role="tab"
                        data-toggle="tab"
                    >
                       
                        <?= html_escape(lang('supplier_due_payment')); ?>
                        
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content erp-tab-content">

            <!-- Purchase List -->
            <div role="tabpanel" class="tab-pane active" id="purchase-list-tab">

                <div
                    id="purchaseSummaryGrid"
                    class="erp-summary-grid erp-summary-four"
                >
                    <div class="erp-summary-cell">
                        <div class="erp-stat-card">
                            <div class="erp-stat-label"><?= html_escape(lang('records')); ?></div>
                            <div class="erp-stat-value" id="summary_records">0</div>
                            <i class="fa fa-list erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card">
                            <div class="erp-stat-label"><?= html_escape(lang('total')); ?></div>
                            <div class="erp-stat-value" id="summary_total">0.00</div>
                            <i class="fa fa-calculator erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card success">
                            <div class="erp-stat-label"><?= html_escape(lang('paid')); ?></div>
                            <div class="erp-stat-value" id="summary_paid">0.00</div>
                            <i class="fa fa-check-circle erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card danger">
                            <div class="erp-stat-label"><?= html_escape(lang('due')); ?></div>
                            <div class="erp-stat-value" id="summary_due">0.00</div>
                            <i class="fa fa-warning erp-stat-icon"></i>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="mobile-summary-toggle mobile-filter-toggle"
                    data-target="#purchaseFilterCard"
                    aria-expanded="false"
                    onclick="return togglePurchaseFilter(this);"
                >
                    <span class="toggle-left">
                        <i class="fa fa-filter"></i>
                        <span class="toggle-label">
                            <?= html_escape(lang('filter')); ?>
                        </span>
                    </span>

                    <i class="fa fa-chevron-down toggle-icon"></i>
                </button>

                <!-- Exact same filter component structure as Sales Listing -->
                <div
                    id="purchaseFilterCard"
                    class="erp-filter-card mobile-filter-collapsed"
                >
                    <div class="erp-filter-grid">

                        <div class="erp-filter-cell">
                            <label for="purchaseSupplierFilter">
                                <?= html_escape(lang('supplier')); ?>
                            </label>

                            <select
                                id="purchaseSupplierFilter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= html_escape(lang('all')); ?></option>

                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= html_escape($supplier->name); ?>">
                                        <?= html_escape($supplier->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="erp-filter-cell">
                            <label for="purchaseStatusFilter">
                                <?= html_escape($is_myanmar_action_language ? 'ငွေပေးချေမှုအခြေအနေ' : 'Payment Status'); ?>
                            </label>

                            <select
                                id="purchaseStatusFilter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= html_escape(lang('all')); ?></option>
                                <option value="paid"><?= html_escape($is_myanmar_action_language ? 'ပေးချေပြီး' : 'Paid'); ?></option>
                                <option value="partial"><?= html_escape($is_myanmar_action_language ? 'တစ်စိတ်တစ်ပိုင်းပေးပြီး' : 'Partially Paid'); ?></option>
                                <option value="due"><?= html_escape($is_myanmar_action_language ? 'မပေးရသေး' : 'Unpaid'); ?></option>
                            </select>
                        </div>

                        <div class="erp-filter-cell">
                            <label for="purchaseReceivedFilter">
                                <?= html_escape($is_myanmar_action_language ? 'ပစ္စည်းရောက်ရှိမှု' : 'Goods Arrival Status'); ?>
                            </label>

                            <select
                                id="purchaseReceivedFilter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= html_escape(lang('all')); ?></option>
                                <option value="1"><?= html_escape($is_myanmar_action_language ? 'ပစ္စည်းရောက်ရှိပြီး' : 'Arrived'); ?></option>
                                <option value="2"><?= html_escape($is_myanmar_action_language ? 'တစ်စိတ်တစ်ပိုင်းရောက်ရှိ' : 'Partially Arrived'); ?></option>
                                <option value="0"><?= html_escape($is_myanmar_action_language ? 'ပစ္စည်းမရောက်သေး' : 'Not Arrived'); ?></option>
                            </select>
                        </div>

                    </div>
                </div>

                <p class="erp-scroll-hint" >
                    <i class="fa fa-arrows-h"></i>
                    <?= html_escape(lang('swipe_table_horizontal')); ?>
                </p>

                <div class="erp-toolbar">
                    <div class="erp-toolbar-left" id="purDataButtons"></div>

                    <div class="erp-toolbar-right">
                        <div class="erp-search-box">
                            <i class="fa fa-search"></i>

                            <input
                                type="text"
                                id="purchaseSearch"
                                class="form-control"
                                placeholder="<?= html_escape(lang('type_hit_enter')); ?>"
                            >
                        </div>

                        
                    </div>
                </div>

                <div class="erp-table-wrap">
                    <table
                        id="purData"
                        class="table table-striped table-bordered table-hover"
                        style="width:100%;"
                    >
                        <colgroup>
                            <col style="width:52px;">
                            <col style="width:105px;">
                            <col style="width:170px;">
                            <col style="width:120px;">
                            <col style="width:120px;">
                            <col style="width:120px;">
                            <col style="width:145px;">
                            <col style="width:125px;">
                            <col style="width:150px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th><?= html_escape(lang('id')); ?></th>
                                <th><?= html_escape(lang('date')); ?></th>
                                <th><?= html_escape(lang('supplier')); ?></th>
                                <th class="text-right"><?= html_escape(lang('total')); ?></th>
                                <th class="text-right"><?= html_escape(lang('paid')); ?></th>
                                <th class="text-right"><?= html_escape(lang('due')); ?></th>
                                <th>
                                    <span class="pur-header-label">
                                        <?= $is_myanmar_action_language
                                            ? 'ငွေပေးချေမှု<br>အခြေအနေ'
                                            : 'Payment<br>Status'; ?>
                                    </span>
                                </th>
                                <th>
                                    <span class="pur-header-label">
                                        <?= $is_myanmar_action_language
                                            ? 'ပစ္စည်းရောက်<br>ရှိမှု'
                                            : 'Goods<br>Arrival'; ?>
                                    </span>
                                </th>
                                <th class="text-center">
                                    <span class="pur-header-label">
                                        <?= $is_myanmar_action_language
                                            ? 'လုပ်ဆောင်<br>မှုများ'
                                            : 'Actions'; ?>
                                    </span>
                                </th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= html_escape(lang('total')); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="purchase-label-meaning">
                    <div class="purchase-label-meaning-title">
                        <i class="fa fa-info-circle"></i>
                        <span><?= $is_myanmar_action_language ? 'လုပ်ဆောင်ချက်ပုံများ၏ အဓိပ္ပာယ်' : 'Label and action meanings'; ?></span>
                    </div>

                    <div class="purchase-label-meaning-groups">
                        <div class="purchase-label-group">
                            <span class="purchase-label-item">
                                <span class="sale_status label label-success"><?= html_escape(lang('payment_paid')); ?></span>
                                <span><?= $is_myanmar_action_language ? 'အပြည့်ပေးပြီး' : 'Paid'; ?></span>
                            </span>

                            <span class="purchase-label-item">
                                <span class="sale_status label label-primary"><?= html_escape(lang('payment_partial')); ?></span>
                                <span><?= $is_myanmar_action_language ? 'တစ်စိတ်တစ်ပိုင်းပေးပြီး' : 'Partially paid'; ?></span>
                            </span>

                            <span class="purchase-label-item">
                                <span class="sale_status label label-danger"><?= html_escape(lang('payment_due')); ?></span>
                                <span><?= $is_myanmar_action_language ? 'မပေးရသေး' : 'Unpaid'; ?></span>
                            </span>
                        </div>

                        <div class="purchase-label-group">
                            <span class="purchase-label-item">
                                <span class="erp-action-icon-btn erp-action-received-disabled"><i class="fa fa-check"></i></span>
                                <span><?= $is_myanmar_action_language ? 'ပစ္စည်းရောက်ရှိပြီး' : 'Arrived'; ?></span>
                            </span>

                            <span class="purchase-label-item">
                                <span class="erp-action-icon-btn erp-action-receive"><i class="fa fa-check"></i></span>
                                <span><?= $is_myanmar_action_language ? 'ပစ္စည်းလက်ခံရန်' : 'Receive goods'; ?></span>
                            </span>

                            <span class="purchase-label-item">
                                <span class="erp-action-icon-btn erp-action-receive-partial"><i class="fa fa-hourglass-half"></i></span>
                                <span><?= $is_myanmar_action_language ? 'ပစ္စည်းရောက်ရှိရန်ကျန်' : 'Pending arrival'; ?></span>
                            </span>
                        </div>

                        <div class="purchase-label-group">
                            <span class="purchase-label-item">
                                <span class="erp-action-icon-btn erp-action-view"><i class="fa fa-eye"></i></span>
                                <span><?= $is_myanmar_action_language ? 'အသေးစိတ်ကြည့်ရန်' : 'View details'; ?></span>
                            </span>

                            <span class="purchase-label-item">
                                <span class="erp-action-icon-btn erp-action-payments"><i class="fa fa-credit-card"></i></span>
                                <span><?= $is_myanmar_action_language ? 'ငွေပေးချေမှုကြည့်ရန်' : 'View payments'; ?></span>
                            </span>

                            <span class="purchase-label-item">
                                <span class="erp-action-icon-btn erp-action-delete"><i class="fa fa-trash"></i></span>
                                <span><?= $is_myanmar_action_language ? 'ဖျက်ရန်' : 'Delete'; ?></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Purchase List -->
            <div
                role="tabpanel"
                class="tab-pane"
                id="product-purchase-tab"
            >
                <div
                    id="productPurchaseSummaryGrid"
                    class="erp-summary-grid erp-summary-three"
                >
                    <div class="erp-summary-cell">
                        <div class="erp-stat-card">
                            <div class="erp-stat-label">
                                <?= html_escape(lang('records')); ?>
                            </div>
                            <div class="erp-stat-value" id="product_purchase_records">0</div>
                            <i class="fa fa-list erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card">
                            <div class="erp-stat-label">
                                <?= html_escape(lang('quantity')); ?>
                            </div>
                            <div class="erp-stat-value" id="product_purchase_primary_qty">0</div>
                            <i class="fa fa-cubes erp-stat-icon"></i>
                        </div>
                    </div>

                    <div class="erp-summary-cell">
                        <div class="erp-stat-card warning">
                            <div class="erp-stat-label">
                                <?= html_escape(lang('purchase_amount')); ?>
                            </div>
                            <div class="erp-stat-value" id="product_purchase_amount">0.00</div>
                            <i class="fa fa-money erp-stat-icon"></i>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="mobile-summary-toggle mobile-filter-toggle"
                    data-target="#productPurchaseFilterCard"
                    aria-expanded="false"
                    onclick="return togglePurchaseFilter(this);"
                >
                    <span class="toggle-left">
                        <i class="fa fa-filter"></i>
                        <span class="toggle-label">
                            <?= html_escape(lang('filter')); ?>
                        </span>
                    </span>
                    <i class="fa fa-chevron-down toggle-icon"></i>
                </button>

                <div
                    id="productPurchaseFilterCard"
                    class="erp-filter-card mobile-filter-collapsed"
                >
                    <div class="erp-filter-grid">
                        <div class="erp-filter-cell">
                            <label for="productPurchaseProductFilter">
                                <?= html_escape(lang('product')); ?>
                            </label>

                            <select
                                id="productPurchaseProductFilter"
                                class="form-control select2 erp-select2"
                            >
                                <option value="">
                                    <?= html_escape(lang('all_products')); ?>
                                </option>

                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= (int) $product->id; ?>">
                                            <?= html_escape($product->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="erp-filter-cell">
                            <label for="productPurchaseStartDate">
                                <?= html_escape(lang('start_date')); ?>
                            </label>

                            <input
                                type="date"
                                id="productPurchaseStartDate"
                                class="form-control product-purchase-date"
                                value="<?= html_escape($product_purchase_start_date); ?>"
                            >
                        </div>

                        <div class="erp-filter-cell">
                            <label for="productPurchaseEndDate">
                                <?= html_escape(lang('end_date')); ?>
                            </label>

                            <input
                                type="date"
                                id="productPurchaseEndDate"
                                class="form-control product-purchase-date"
                                value="<?= html_escape($product_purchase_end_date); ?>"
                            >
                        </div>
                    </div>

                    <div class="product-purchase-filter-actions" style="margin-top:10px;">
                        <button
                            type="button"
                            id="applyProductPurchaseFilter"
                            class="btn btn-primary"
                        >
                            <i class="fa fa-search"></i>
                            <?= html_escape(lang('submit')); ?>
                        </button>
                    </div>
                </div>

                <p class="erp-scroll-hint" >
                    <i class="fa fa-arrows-h"></i>
                    <?= html_escape(lang('swipe_table_horizontal')); ?>
                </p>

                <div class="erp-toolbar">
                    <div class="erp-toolbar-left" id="productPurchaseDataButtons"></div>

                    <div class="erp-toolbar-right">
                        <div class="erp-search-box">
                            <i class="fa fa-search"></i>
                            <input
                                type="text"
                                id="productPurchaseSearch"
                                class="form-control"
                                placeholder="<?= html_escape(lang('type_hit_enter')); ?>"
                            >
                        </div>

                        
                    </div>
                </div>

                <div class="erp-table-wrap">
                    <table
                        id="productPurchaseData"
                        class="table table-striped table-bordered table-hover"
                        style="width:100%;"
                    >
                        <thead>
                            <tr>
                                <th><?= html_escape(lang('no.')); ?></th>
                                <th><?= html_escape(lang('product_code')); ?></th>
                                <th><?= html_escape(lang('product_name')); ?></th>
                                <th class="text-right"><?= html_escape(lang('quantity')); ?></th>
                                <th class="text-right"><?= html_escape(lang('purchase_amount')); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= html_escape(lang('total')); ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Supplier Due Payment -->
<div role="tabpanel" class="tab-pane" id="supplier-payment-tab">

    <!-- Supplier First Selection -->
    <div class="erp-supplier-selector-panel">
        <div class="erp-supplier-selector-header">
            <div>
                <div class="erp-supplier-selector-title-row">
                    <h4 class="erp-supplier-selector-title">
                        <i class="fa fa-truck"></i>
                        <?= html_escape(lang('supplier_due_payment')); ?>
                    </h4>

                    <button
                        type="button"
                        id="supplierDueHelpToggle"
                        class="supplier-due-help-toggle"
                        aria-expanded="false"
                        aria-controls="supplierDueHelpContent"
                        title="အကူအညီကြည့်ရန်"
                    >
                        <i class="fa fa-question-circle"></i>
                        <span class="sr-only">အကူအညီကြည့်ရန်</span>
                    </button>
                </div>

                <p
                    id="supplierDueHelpContent"
                    class="erp-supplier-selector-note supplier-due-help-item"
                >
                    Supplier ကိုအရင်ရွေးချယ်ပါ။ ရွေးချယ်ထားသော Supplier ၏
                    အကြွေးစာရင်းများသာ ဖော်ပြပေးပါမည်။
                </p>
            </div>
        </div>

        <div class="erp-supplier-selector-control">
            <label for="dueSupplierFilter">
                <?= html_escape(lang('supplier')); ?>
                <span class="text-danger">*</span>
            </label>

            <select
                id="dueSupplierFilter"
                class="form-control select2 erp-select2"
                data-placeholder="<?= html_escape(lang('supplier')); ?>"
            >
                <option value="">Supplier ရွေးချယ်ပါ</option>

                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= (int) $supplier->id; ?>">
                        <?= html_escape($supplier->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Empty State Before Supplier Selection -->
    <div id="supplierDueEmptyState" class="erp-supplier-empty-state">
        <div class="erp-supplier-empty-icon">
            <i class="fa fa-hand-pointer-o"></i>
        </div>

        <h4>Supplier ရွေးချယ်ပါ</h4>

        <p>
            အကြွေးစာရင်းကြည့်ရန်နှင့် ငွေပေးချေရန် အပေါ်မှ Supplier တစ်ဦးကို
            အရင်ရွေးချယ်ပေးပါ။
        </p>
    </div>

    <!-- Due Workspace -->
    <div id="supplierDueWorkspace" style="display:none;">

        <!-- Summary - Always Visible -->
        <div
            id="dueSummaryGrid"
            class="erp-summary-grid erp-summary-three"
        >
            <div class="erp-summary-cell">
                <div class="erp-stat-card warning">
                    <div class="erp-stat-label">
                        <?= html_escape(lang('outstanding_purchases')); ?>
                    </div>

                    <div class="erp-stat-value" id="due_records">0</div>

                    <i class="fa fa-file-text-o erp-stat-icon"></i>
                </div>
            </div>

            <div class="erp-summary-cell">
                <div class="erp-stat-card danger">
                    <div class="erp-stat-label">
                        <?= html_escape(lang('total_outstanding_due')); ?>
                    </div>

                    <div class="erp-stat-value" id="due_total">0.00</div>

                    <i class="fa fa-warning erp-stat-icon"></i>
                </div>
            </div>

            <div class="erp-summary-cell">
                <div class="erp-stat-card success">
                    <div class="erp-stat-label">
                        <?= html_escape(lang('selected_purchase_count')); ?>
                    </div>

                    <div
                        class="erp-stat-value"
                        id="selected_purchase_count"
                    >
                        0
                    </div>

                    <i class="fa fa-check-square-o erp-stat-icon"></i>
                </div>
            </div>
        </div>

        <p class="erp-help-note supplier-due-help-item">
            <i class="fa fa-info-circle"></i>
            <?= html_escape(lang('due_payment_tab_help')); ?>
        </p>

        <p class="erp-scroll-hint supplier-due-help-item">
            <i class="fa fa-arrows-h"></i>
            <?= html_escape(lang('swipe_table_horizontal')); ?>
        </p>

        <!-- Toolbar -->
        <div class="erp-toolbar">
            <div class="erp-toolbar-left" id="dueDataButtons"></div>

            <div class="erp-toolbar-right">
                <div class="erp-search-box">
                    <i class="fa fa-search"></i>

                    <input
                        type="text"
                        id="dueSearch"
                        class="form-control"
                        placeholder="<?= html_escape(lang('search_supplier_due_placeholder')); ?>"
                    >
                </div>

                
            </div>
        </div>

        <!-- Due Table -->
        <div class="erp-table-wrap">
            <table
                id="dueData"
                class="table table-striped table-bordered table-hover"
                style="width:100%;"
            >
                <thead>
                    <tr>
                        <th
                            class="text-center due-checkbox-column"
                            style="width:38px;"
                        >
                            <input type="checkbox" id="checkAllDue">
                        </th>

                        <th><?= html_escape(lang('id')); ?></th>

                        <th><?= html_escape(lang('date')); ?></th>

                        <th><?= html_escape(lang('supplier')); ?></th>

                        <th class="text-right">
                            <?= html_escape(lang('total')); ?>
                        </th>

                        <th class="text-right">
                            <?= html_escape(lang('paid')); ?>
                        </th>

                        <th class="text-right">
                            <?= html_escape(lang('due')); ?>
                        </th>

                        <th><?= html_escape(lang('status')); ?></th>

                        
                    </tr>
                </thead>

                <tbody></tbody>

                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>

                        <th class="text-right">
                            <?= html_escape(lang('total')); ?>
                        </th>

                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment Panel -->
        <div class="erp-payment-panel">
            <h4 class="erp-payment-title">
                <i class="fa fa-money"></i>
                <?= html_escape(lang('supplier_due_payment')); ?>
            </h4>

            <div class="erp-payment-grid">

                <div class="erp-payment-cell">
                    <span class="erp-field-label">
                        <?= html_escape(lang('selected_due')); ?>
                    </span>

                    <div
                        class="erp-selected-due"
                        id="selected_due_total"
                    >
                        0.00
                    </div>
                </div>

                <div class="erp-payment-cell">
                    <label for="bulk_pay_amount">
                        <?= html_escape(lang('payment_amount')); ?>
                    </label>

                    <input
                        type="number"
                        id="bulk_pay_amount"
                        class="form-control"
                        min="0"
                        step="0.01"
                        placeholder="<?= html_escape(lang('payment_amount')); ?>"
                    >
                </div>

                <div class="erp-payment-cell">
                    <span class="erp-field-label">&nbsp;</span>

                    <button
                        id="bulkPayBtn"
                        type="button"
                        class="btn btn-success btn-block"
                        disabled
                    >
                        <i class="fa fa-money"></i>
                        <?= html_escape(lang('pay_selected')); ?>
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>

        </div>
    </div>
</section>

<!-- Purchase Delete Result Modal -->
<div
    class="modal fade"
    id="purchaseDeleteResultModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="purchaseDeleteResultTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i id="purchaseDeleteResultIcon" class="fa"></i>
                    <span id="purchaseDeleteResultTitle"></span>
                </h4>
            </div>
            <div class="modal-body">
                <p id="purchaseDeleteResultMessage" style="margin:0; font-size:16px; line-height:1.7;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    <i class="fa fa-check"></i>
                    <?= $is_myanmar_action_language ? 'ကောင်းပါပြီ' : 'OK'; ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- URL မပါတဲ့ Purchase Delete Confirmation Modal -->
<div
    class="modal fade"
    id="purchaseDeleteConfirmModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="purchaseDeleteConfirmTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="purchaseDeleteConfirmTitle">
                    <i class="fa fa-exclamation-triangle text-danger"></i>
                    <?= $is_myanmar_action_language ? 'ဖျက်ရန် အတည်ပြုခြင်း' : 'Confirm Delete'; ?>
                </h4>
            </div>

            <div class="modal-body">
                <p id="purchaseDeleteConfirmMessage" style="margin:0; font-size:16px; line-height:1.7;"></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                    <?= $is_myanmar_action_language ? 'မဖျက်တော့ပါ' : 'Cancel'; ?>
                </button>
                <button type="button" class="btn btn-danger" id="confirmPurchaseDeleteBtn">
                    <i class="fa fa-trash"></i>
                    <?= $is_myanmar_action_language ? 'ဖျက်မည်' : 'Delete'; ?>
                </button>
            </div>
        </div>
    </div>
</div>
