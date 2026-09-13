<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$SL = function ($key, $fallback) {
    $text = lang($key);

    return (
        $text !== false &&
        $text !== null &&
        $text !== '' &&
        $text !== $key
    ) ? $text : $fallback;
};

$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$app_language =
    $this->input->get('app_lang', true) ?:
    $this->input->post('app_lang', true);

$active_sales_tab =
    $this->input->get('tab', true) === 'due'
        ? 'due'
        : 'sales';

$base_refresh_query = [
    '_refresh' => time(),
];

if ($is_app_mode) {
    $base_refresh_query['app'] = 1;
}

if (!empty($app_language)) {
    $base_refresh_query['app_lang'] = $app_language;
}

$sales_refresh_url =
    site_url('sales') . '?' . http_build_query($base_refresh_query);

$due_refresh_query = $base_refresh_query;
$due_refresh_query['tab'] = 'due';

$due_refresh_url =
    site_url('sales') . '?' . http_build_query($due_refresh_query);

$resolved_sales_language = !empty($app_language)
    ? $app_language
    : (
        isset($Settings->selected_language)
            ? $Settings->selected_language
            : ''
    );

$is_myanmar_action_language = in_array(
    strtolower(trim((string) $resolved_sales_language)),
    ['myanmar', 'burmese', 'mm', 'my'],
    true
);

$sales_listing_labels = $is_myanmar_action_language
    ? [
        'payment_status'   => 'ငွေပေးချေမှု အခြေအနေ',
        'receipt_status'   => 'ပစ္စည်းပို့ဆောင်မှု အခြေအနေ',
        'receive'          => 'ပစ္စည်းပို့ရန်',
        'partial_delivery' => 'တစ်စိတ်တစ်ပိုင်း ပို့ပြီး',
        'received'         => 'ပစ္စည်းပို့ပြီး',
        'legend_title'     => 'လုပ်ဆောင်ချက်ပုံများ၏ အဓိပ္ပာယ်',
        'payment_group'    => 'ငွေပေးချေမှု :',
        'paid_meaning'     => 'အပြည့်ပေးပြီး',
        'partial_meaning'  => 'တစ်စိတ်တစ်ပိုင်းပေးပြီး',
        'due_meaning'      => 'မပေးရသေး',
        'receipt_group'    => 'ပစ္စည်းပို့ဆောင်မှု :',
        'actions_group'    => 'လုပ်ဆောင်ချက် :',
        'view_details'     => 'အသေးစိတ်ကြည့်ရန်',
        'view_payments'    => 'ငွေပေးချေမှုကြည့်ရန်',
        'delete'           => 'ဖျက်ရန်',
    ]
    : [
        'payment_status'   => 'Payment Status',
        'receipt_status'   => 'Delivery Status',
        'receive'          => 'Pending delivery',
        'partial_delivery' => 'Partially delivered',
        'received'         => 'Delivered',
        'legend_title'     => 'Label and action meanings',
        'payment_group'    => 'Payment:',
        'paid_meaning'     => 'Paid',
        'partial_meaning'  => 'Partially paid',
        'due_meaning'      => 'Unpaid',
        'receipt_group'    => 'Delivery:',
        'actions_group'    => 'Actions:',
        'view_details'     => 'View details',
        'view_payments'    => 'View payments',
        'delete'           => 'Delete',
    ];

$sales_listing_labels['paid_badge'] = $SL(
    'payment_paid',
    $is_myanmar_action_language ? 'ပေးပြီး' : 'Paid'
);

$sales_listing_labels['partial_badge'] = $SL(
    'payment_partial',
    $is_myanmar_action_language ? 'အချို့ပေးပြီး' : 'Partial'
);

$sales_listing_labels['due_badge'] = $SL(
    'payment_due',
    $is_myanmar_action_language ? 'မပေးရသေး' : 'Due'
);
?>

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

.content.sales-tabs-page {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.sales-tabs-page .erp-shell {
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
</style>
<?php } ?>


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
    font-size: 16px;
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
    font-size: 16px;
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
    font-size: 16px;
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
    border: 1px solid #338191;
    border-radius: 10px;
    background: #eff7f9;
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
    font-size: 16px;
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
    font-size: 16px;
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
   Customer Due - Customer First Selection
   ========================================================= */
.sales-tabs-page .erp-customer-selector-panel {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 16px;
    padding: 18px;
    border: 1px solid #dfe5ec;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
}

.sales-tabs-page .erp-customer-selector-header {
    flex: 1 1 auto;
    min-width: 0;
}

.sales-tabs-page .erp-customer-selector-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.sales-tabs-page .erp-customer-selector-title {
    margin: 0;
    color: #263238;
    font-size: 17px;
    font-weight: 700;
}

.sales-tabs-page .erp-customer-selector-title i {
    margin-right: 7px;
    color: #1976d2;
}

.sales-tabs-page .erp-customer-selector-note {
    margin: 8px 0 0;
    color: #6b7785;
    font-size: 16px;
    line-height: 1.6;
}

.sales-tabs-page .erp-customer-selector-control {
    flex: 0 0 330px;
    max-width: 100%;
}

.sales-tabs-page .erp-customer-selector-control label {
    display: block;
    margin-bottom: 7px;
    color: #37474f;
    font-weight: 600;
}

.sales-tabs-page .erp-customer-selector-control .select2-container {
    width: 100% !important;
}

.sales-tabs-page .erp-customer-empty-state {
    margin-bottom: 18px;
    padding: 50px 20px;
    border: 1px dashed #cbd4df;
    border-radius: 8px;
    background: #fafbfd;
    text-align: center;
}

.sales-tabs-page .erp-customer-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 62px;
    height: 62px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: #eaf3fd;
    color: #1976d2;
    font-size: 26px;
}

.sales-tabs-page .erp-customer-empty-state h4 {
    margin: 0 0 8px;
    color: #37474f;
    font-size: 17px;
    font-weight: 700;
}

.sales-tabs-page .erp-customer-empty-state p {
    max-width: 480px;
    margin: 0 auto;
    color: #78909c;
    line-height: 1.7;
}

.sales-tabs-page #customerDueWorkspace {
    animation: customerDueFadeIn .2s ease-in-out;
}

@keyframes customerDueFadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.sales-tabs-page .customer-due-help-toggle {
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

.sales-tabs-page .customer-due-help-toggle:hover,
.sales-tabs-page .customer-due-help-toggle:focus {
    border-color: #60a5fa;
    background: #dbeafe;
    color: #1d4ed8;
    outline: 0;
    transform: translateY(-1px);
}

.sales-tabs-page .customer-due-help-toggle.is-active {
    border-color: #fca5a5;
    background: #fff1f2;
    color: #dc2626;
}

.sales-tabs-page #customer-due-tab .customer-due-help-item {
    display: none !important;
}

.sales-tabs-page
#customer-due-tab.customer-due-help-open
.customer-due-help-item {
    display: block !important;
}

/* Checkbox column တွင် sorting icon မပြပါ။ */
.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th.due-checkbox-column,
.sales-tabs-page
#customerDueData
thead
th.due-checkbox-column {
    background-image: none !important;
    cursor: default !important;
}

.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th.due-checkbox-column::before,
.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th.due-checkbox-column::after,
.sales-tabs-page
#customerDueData
thead
th.due-checkbox-column::before,
.sales-tabs-page
#customerDueData
thead
th.due-checkbox-column::after {
    display: none !important;
    content: none !important;
}

/* DataTables v1/v2 နှင့် Bootstrap theme sort icon များကိုပါ ဖျောက်မည်။ */
.sales-tabs-page
#customerDueData_wrapper
.dataTables_scrollHead
table.dataTable
thead
th:first-child,
.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th:first-child {
    padding-right: 6px !important;
    background-image: none !important;
    cursor: default !important;
}

.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th:first-child::before,
.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th:first-child::after {
    display: none !important;
    content: none !important;
}

.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th:first-child
.dt-column-order,
.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th:first-child
.DataTables_sort_icon,
.sales-tabs-page
#customerDueData_wrapper
table.dataTable
thead
th:first-child
.sorting-icon {
    display: none !important;
}

@media (max-width: 767px) {
    .sales-tabs-page .erp-customer-selector-panel {
        display: block;
        padding: 14px;
    }

    .sales-tabs-page .erp-customer-selector-control {
        width: 100%;
        margin-top: 14px;
    }

    .sales-tabs-page .erp-customer-empty-state {
        padding: 35px 15px;
    }

    .sales-tabs-page .customer-due-help-toggle {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
        font-size: 18px;
    }
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
    font-size: 16px;
    font-weight: 800;
}

.sales-tabs-page .erp-payment-grid {
    display: grid;
    grid-template-columns:
        minmax(135px, .85fr)
        minmax(190px, 1.15fr)
        minmax(190px, 1.05fr);
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
    font-size: 16px;
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
    font-size: 16px;
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
    font-size: 16px;
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
    min-width: 889px !important;
}

.sales-tabs-page table.dataTable thead th {
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

.sales-tabs-page table.dataTable tbody td {
    padding: 11px 9px !important;
    border-right: 1px solid #edf2f7 !important;
    border-bottom: 1px solid #edf2f7 !important;
    color: #334155;
    font-size: 16px;
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
    font-size: 16px;
    font-weight: 900;
    white-space: nowrap;
}

/*
 * Sales list နှင့် Customer Due list နှစ်ခုလုံး၏ Header/Body ကော်လံများ
 * တူညီစေရန် fixed layout နှင့် အကျယ်များကို တိတိကျကျ သတ်မှတ်ထားသည်။
 * ရှည်သော header စာသားများသည် မိမိကော်လံအတွင်းမှာပဲ လိုင်းဆင်းမည်။
 */
#SLData_wrapper table.dataTable,
#SLData_wrapper .dataTables_scrollHead table.dataTable,
#SLData_wrapper .dataTables_scrollBody table.dataTable,
#SLData_wrapper .dataTables_scrollFoot table.dataTable {
    width: 100% !important;
    min-width: 1022px !important;
    max-width: none !important;
    table-layout: fixed !important;
}

/*
 * Purchase list ကဲ့သို့ outer .erp-table-wrap တစ်ခုတည်းကိုသာ
 * horizontal scroll အဖြစ်သုံးပြီး DataTables clone header ကိုဖျောက်ထားသည်။
 * မူရင်း table THEAD နှင့် TBODY သည် colgroup တစ်ခုတည်းကိုသုံးသဖြင့်
 * header/body ကော်လံများ အမြဲတန်းညီနေမည်။
 */
#SLData_wrapper {
    width: 100% !important;
    min-width: 1022px !important;
    max-width: none !important;
}

#SLData_wrapper .dataTables_scroll {
    width: 100% !important;
    min-width: 1022px !important;
    max-width: none !important;
}

#SLData_wrapper .dataTables_scrollHead {
    display: none !important;
}

#SLData_wrapper .dataTables_scrollBody {
    width: 100% !important;
    min-width: 1022px !important;
    max-width: none !important;
    max-height: none !important;
    overflow: visible !important;
}

#customerDueData_wrapper table.dataTable,
#customerDueData_wrapper .dataTables_scrollHead table.dataTable,
#customerDueData_wrapper .dataTables_scrollBody table.dataTable,
#customerDueData_wrapper .dataTables_scrollFoot table.dataTable {
    width: 100% !important;
    min-width: 889px !important;
    max-width: none !important;
    table-layout: fixed !important;
}

#SLData_wrapper table.dataTable th,
#SLData_wrapper table.dataTable td,
#customerDueData_wrapper table.dataTable th,
#customerDueData_wrapper table.dataTable td {
    box-sizing: border-box !important;
}

#customerDueData_wrapper .dataTables_scrollHead table.dataTable thead th {
    height: auto !important;
    padding: 9px 6px !important;
    line-height: 1.3 !important;
    text-align: center !important;
    vertical-align: middle !important;
    white-space: normal !important;
    overflow: hidden !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
}

#SLData_wrapper table#SLData thead {
    display: table-header-group !important;
    visibility: visible !important;
    height: auto !important;
}

#SLData_wrapper table#SLData thead tr {
    display: table-row !important;
    visibility: visible !important;
    height: auto !important;
}

#SLData_wrapper table#SLData thead th,
#SLData_wrapper table#SLData thead td {
    display: table-cell !important;
    visibility: visible !important;
    height: auto !important;
    padding: 10px 6px !important;
    border-bottom: 1px solid #dfe6ef !important;
    background: #f3f6f9 !important;
    color: #334155 !important;
    line-height: 1.3 !important;
    text-align: center !important;
    vertical-align: middle !important;
    white-space: normal !important;
    overflow: hidden !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
}

#SLData_wrapper table#SLData thead .dataTables_sizing {
    display: block !important;
    visibility: visible !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
    color: inherit !important;
    line-height: inherit !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
    box-sizing: border-box !important;
}

.sales-tabs-page .sales-table-header-label {
    display: block !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    color: inherit !important;
    line-height: 1.25 !important;
    text-align: center !important;
    white-space: normal !important;
    overflow: visible !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
}

/*
 * scrollX သုံးသည့် DataTables က body table အတွင်း sizing header တစ်ခု
 * ထပ်ထည့်ထားသည်။ ၎င်းကို အမြင့်မယူဘဲ ဝှက်ထားမှ header တစ်ခုပဲ ပေါ်မည်။
 */
#customerDueData_wrapper .dataTables_scrollBody table.dataTable thead {
    visibility: hidden !important;
    height: 0 !important;
}

#customerDueData_wrapper .dataTables_scrollBody table.dataTable thead tr,
#customerDueData_wrapper .dataTables_scrollBody table.dataTable thead th {
    height: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    border-top: 0 !important;
    border-bottom: 0 !important;
    line-height: 0 !important;
    overflow: hidden !important;
}

#customerDueData_wrapper .dataTables_scrollBody table.dataTable thead .dataTables_sizing,
#customerDueData_wrapper .dataTables_scrollBody table.dataTable thead .sales-table-header-label {
    visibility: hidden !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    line-height: 0 !important;
}

/* Sales list: 8 columns */
#SLData_wrapper table.dataTable th:nth-child(1),
#SLData_wrapper table.dataTable td:nth-child(1) {
    width: 52px !important;
    min-width: 52px !important;
    max-width: 52px !important;
}

#SLData_wrapper table.dataTable th:nth-child(2),
#SLData_wrapper table.dataTable td:nth-child(2) {
    width: 170px !important;
    min-width: 170px !important;
    max-width: 170px !important;
}

#SLData_wrapper table.dataTable th:nth-child(3),
#SLData_wrapper table.dataTable td:nth-child(3) {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
}

#SLData_wrapper table.dataTable th:nth-child(4),
#SLData_wrapper table.dataTable td:nth-child(4) {
    width: 120px !important;
    min-width: 120px !important;
    max-width: 120px !important;
}

#SLData_wrapper table.dataTable th:nth-child(5),
#SLData_wrapper table.dataTable td:nth-child(5) {
    width: 110px !important;
    min-width: 110px !important;
    max-width: 110px !important;
}

#SLData_wrapper table.dataTable th:nth-child(6),
#SLData_wrapper table.dataTable td:nth-child(6),
#SLData_wrapper table.dataTable th:nth-child(7),
#SLData_wrapper table.dataTable td:nth-child(7) {
    width: 130px !important;
    min-width: 130px !important;
    max-width: 130px !important;
}

#SLData_wrapper table.dataTable th:nth-child(8),
#SLData_wrapper table.dataTable td:nth-child(8) {
    width: 160px !important;
    min-width: 160px !important;
    max-width: 160px !important;
}

/* Customer Due list: 9 columns */
#customerDueData_wrapper table.dataTable th:nth-child(1),
#customerDueData_wrapper table.dataTable td:nth-child(1) {
    width: 42px !important;
    min-width: 42px !important;
    max-width: 42px !important;
}

#customerDueData_wrapper table.dataTable th:nth-child(2),
#customerDueData_wrapper table.dataTable td:nth-child(2) {
    width: 52px !important;
    min-width: 52px !important;
    max-width: 52px !important;
}

#customerDueData_wrapper table.dataTable th:nth-child(3),
#customerDueData_wrapper table.dataTable td:nth-child(3) {
    width: 170px !important;
    min-width: 170px !important;
    max-width: 170px !important;
}

#customerDueData_wrapper table.dataTable th:nth-child(4),
#customerDueData_wrapper table.dataTable td:nth-child(4) {
    width: 150px !important;
    min-width: 150px !important;
    max-width: 150px !important;
}

#customerDueData_wrapper table.dataTable th:nth-child(5),
#customerDueData_wrapper table.dataTable td:nth-child(5) {
    width: 120px !important;
    min-width: 120px !important;
    max-width: 120px !important;
}

#customerDueData_wrapper table.dataTable th:nth-child(6),
#customerDueData_wrapper table.dataTable td:nth-child(6),
#customerDueData_wrapper table.dataTable th:nth-child(7),
#customerDueData_wrapper table.dataTable td:nth-child(7) {
    width: 110px !important;
    min-width: 110px !important;
    max-width: 110px !important;
}

#customerDueData_wrapper table.dataTable th:nth-child(8),
#customerDueData_wrapper table.dataTable td:nth-child(8) {
    width: 135px !important;
    min-width: 135px !important;
    max-width: 135px !important;
}

/* Date နှင့် customer စာသားများကြောင့် table ထပ်မဆန့်စေရန် */
#SLData_wrapper table.dataTable td:nth-child(2),
#SLData_wrapper table.dataTable td:nth-child(3),
#customerDueData_wrapper table.dataTable td:nth-child(3),
#customerDueData_wrapper table.dataTable td:nth-child(4) {
    line-height: 1.35 !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
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
    font-size: 16px;
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
    font-size: 16px;
    font-weight: 800;
}

.sales-tabs-page .erp-actions {
    width: 100%;
    min-width: 220px;
    white-space: normal !important;
}

.sales-tabs-page .erp-action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
    width: 100%;
}

.sales-tabs-page .erp-action-icon-btn {
    display: inline-flex !important;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    border: 1px solid #d7e0e8 !important;
    border-radius: 7px !important;
    background: #f8fafc !important;
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
    transition: transform .16s ease, box-shadow .16s ease,
                border-color .16s ease, background-color .16s ease,
                color .16s ease;
    touch-action: manipulation;
}

.sales-tabs-page .erp-action-icon-btn i {
    margin: 0 !important;
    font-size: 16px;
    line-height: 1;
}

.sales-tabs-page .erp-action-icon-btn:hover,
.sales-tabs-page .erp-action-icon-btn:focus {
    transform: translateY(-2px) scale(1.28);
    z-index: 20;
    box-shadow: 0 7px 18px rgba(35, 55, 80, .20) !important;
    outline: 0;
    text-decoration: none !important;
}

.sales-tabs-page .erp-action-icon-btn:active {
    transform: translateY(-1px) scale(1.18);
    z-index: 20;
    box-shadow: 0 4px 12px rgba(35, 55, 80, .18) !important;
    outline: 0;
}

.sales-tabs-page .erp-action-icon-btn:hover i,
.sales-tabs-page .erp-action-icon-btn:focus i {
    font-size: 17px;
}

.sales-tabs-page .erp-action-view {
    border-color: #bcd8ff !important;
    background: #f2f7ff !important;
    color: #2563eb !important;
}

.sales-tabs-page .erp-action-view:hover,
.sales-tabs-page .erp-action-view:focus {
    border-color: #8dbdff !important;
    background: #e8f1ff !important;
}

.sales-tabs-page .erp-action-payments {
    border-color: #f5d79e !important;
    background: #fff9ec !important;
    color: #d97706 !important;
}

.sales-tabs-page .erp-action-payments:hover,
.sales-tabs-page .erp-action-payments:focus {
    border-color: #edc066 !important;
    background: #fff4d9 !important;
}

.sales-tabs-page .erp-action-add-payment {
    border-color: #b9e3c8 !important;
    background: #f1fbf5 !important;
    color: #15803d !important;
}

.sales-tabs-page .erp-action-add-payment:hover,
.sales-tabs-page .erp-action-add-payment:focus {
    border-color: #8fd0a8 !important;
    background: #e6f8ed !important;
}

.sales-tabs-page .erp-action-edit {
    border-color: #f1d59b !important;
    background: #fff9e9 !important;
    color: #b7791f !important;
}

.sales-tabs-page .erp-action-return {
    border-color: #d9c6f5 !important;
    background: #f8f3ff !important;
    color: #7c3aed !important;
}

.sales-tabs-page .erp-action-print {
    border-color: #c8d0f4 !important;
    background: #f3f5ff !important;
    color: #4f46e5 !important;
}

.sales-tabs-page .erp-action-delivery {
    border-color: #b8e4d8 !important;
    background: #f0fbf8 !important;
    color: #0f8a70 !important;
}

/* Purchase Listing ပုံစံ Sales ပစ္စည်းပို့ဆောင်မှု action များ */
.sales-tabs-page .erp-action-receive {
    border-color: #ef4444 !important;
    background: #ef4444 !important;
    color: #ffffff !important;
}

.sales-tabs-page .erp-action-receive:hover,
.sales-tabs-page .erp-action-receive:focus {
    border-color: #b91c1c !important;
    background: #dc2626 !important;
    color: #ffffff !important;
}

.sales-tabs-page .erp-action-received-disabled {
    border-color: #16a34a !important;
    background: #16a34a !important;
    color: #ffffff !important;
    cursor: pointer !important;
    pointer-events: auto !important;
    opacity: 1 !important;
}

.sales-tabs-page .erp-action-delivery-partial {
    border-color: #f59e0b !important;
    background: #f59e0b !important;
    color: #ffffff !important;
}

.sales-tabs-page .deliver_preorder.erp-action-icon-btn {
    margin: 0 !important;
}

/* Keep every delivery status button above DataTables cell overlays. */
.sales-tabs-page button.sales-delivery-open {
    position: relative !important;
    z-index: 12 !important;
    cursor: pointer !important;
    pointer-events: auto !important;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

/* Purchase receive popup style reused for Sales product delivery. */
.sales-delivery-dialog {
    width: 980px;
    max-width: calc(100vw - 20px);
    margin: 10px auto;
}

.sales-delivery-modal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 13px;
    box-shadow: 0 22px 65px rgba(15, 23, 42, .32);
}

#salesDeliveryHistoryModal {
    z-index: 1070;
}

.sales-delivery-history-backdrop {
    z-index: 1060 !important;
}

.sales-delivery-modal .modal-header {
    padding: 12px 16px;
    border-bottom: 1px solid #dbe5ef;
    background: #ffffff;
}

.sales-delivery-modal .modal-title {
    color: #1e293b;
    font-size: 18px;
    font-weight: 900;
}

.sales-delivery-modal .modal-body {
    max-height: calc(100vh - 145px);
    overflow-y: auto;
    padding: 12px;
    background: #f8fafc;
}

.sales-delivery-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin-bottom: 9px;
    padding: 10px;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    background: #eff6ff;
}

.sales-delivery-meta-item,
.sales-delivery-summary-item {
    min-width: 0;
}

.sales-delivery-meta-label,
.sales-delivery-summary-label {
    display: block;
    margin-bottom: 3px;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.45;
}

.sales-delivery-meta-value,
.sales-delivery-summary-value {
    display: block;
    overflow-wrap: anywhere;
    color: #1e293b;
    font-size: 15px;
    font-weight: 900;
}

.sales-delivery-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 7px;
    margin-bottom: 9px;
}

.sales-delivery-summary-item {
    padding: 9px 10px;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #3b82f6;
    border-radius: 9px;
    background: #ffffff;
}

.sales-delivery-summary-item.is-delivered {
    border-left-color: #16a34a;
    background: #f0fdf4;
}

.sales-delivery-summary-item.is-remaining {
    border-left-color: #f59e0b;
    background: #fffbeb;
}

.sales-delivery-fields {
    display: grid;
    grid-template-columns: minmax(190px, .8fr) minmax(260px, 1.2fr);
    gap: 8px;
    margin-bottom: 9px;
}

.sales-delivery-field label {
    display: block;
    margin-bottom: 5px;
    color: #334155;
    font-size: 13px;
    font-weight: 900;
}

.sales-delivery-field .form-control {
    width: 100%;
    height: 42px;
    border-color: #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
}

.sales-delivery-table-card {
    overflow: hidden;
    border: 1px solid #dbe3ec;
    border-radius: 10px;
    background: #ffffff;
}

.sales-delivery-fill-row {
    padding: 7px;
    border-bottom: 1px solid #fed7aa;
    background: #fff7ed;
}

.sales-delivery-fill-all {
    display: inline-flex;
    width: 100%;
    min-height: 40px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 1px solid #f97316;
    border-radius: 8px;
    background: #f97316;
    color: #ffffff;
    font-size: 14px;
    font-weight: 900;
}

.sales-delivery-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.sales-delivery-table {
    width: 100%;
    min-width: 700px;
    margin: 0;
    table-layout: fixed;
    border-collapse: collapse;
}

.sales-delivery-table th,
.sales-delivery-table td {
    padding: 9px 8px !important;
    border: 1px solid #dbe3ec !important;
    vertical-align: middle !important;
}

.sales-delivery-table th {
    background: #f1f5f9;
    color: #334155;
    font-size: 13px;
    font-weight: 900;
    text-align: center;
    white-space: normal;
}

.sales-delivery-product-name {
    color: #1e293b;
    font-size: 14px;
    font-weight: 900;
}

.sales-delivery-product-code {
    display: block;
    margin-top: 3px;
    color: #94a3b8;
    font-size: 12px;
    font-weight: 800;
}

.sales-delivery-qty-input {
    width: 100% !important;
    min-width: 0 !important;
    height: 38px !important;
    padding: 6px 8px !important;
    border-radius: 8px !important;
    text-align: right;
    font-size: 14px !important;
    font-weight: 900;
}

.sales-delivery-current-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 5px;
}

.sales-delivery-history-btn,
.sales-delivery-history-action {
    display: inline-flex;
    min-height: 36px;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 7px 9px;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 12px;
    font-weight: 900;
}

.sales-delivery-history-action.is-edit {
    border-color: #fde68a;
    background: #fffbeb;
    color: #d97706;
}

.sales-delivery-history-action.is-delete {
    border-color: #fecaca;
    background: #fff1f2;
    color: #dc2626;
}

.sales-delivery-modal .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 10px 12px;
    border-top: 1px solid #dbe5ef;
    background: #ffffff;
}

.sales-delivery-modal .modal-footer .btn {
    min-height: 42px;
    margin: 0;
    border-radius: 8px;
    font-weight: 900;
}

.sales-delivery-loading,
.sales-delivery-empty {
    padding: 28px 12px !important;
    color: #64748b;
    text-align: center;
    font-weight: 800;
}

.sales-delivery-action-dialog {
    position: fixed;
    inset: 0;
    z-index: 15050;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.sales-delivery-action-dialog.is-open {
    display: flex;
}

.sales-delivery-action-backdrop {
    position: absolute;
    inset: 0;
    border: 0;
    background: rgba(15, 23, 42, .72);
}

.sales-delivery-action-panel {
    position: relative;
    z-index: 1;
    width: 430px;
    max-width: 100%;
    overflow: hidden;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 20px 60px rgba(0, 0, 0, .35);
}

.sales-delivery-action-head,
.sales-delivery-action-body,
.sales-delivery-action-foot {
    padding: 12px 14px;
}

.sales-delivery-action-head {
    border-bottom: 1px solid #e2e8f0;
    color: #1e293b;
    font-size: 16px;
    font-weight: 900;
}

.sales-delivery-action-foot {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

.sales-delivery-action-error {
    display: none;
    margin-top: 8px;
    color: #dc2626;
    font-size: 12px;
    font-weight: 800;
}

.sales-tabs-page .erp-action-email {
    border-color: #b9dce8 !important;
    background: #f0f9fc !important;
    color: #18748b !important;
}

.sales-tabs-page .erp-action-delete {
    border-color: #f4c1c6 !important;
    background: #fff5f6 !important;
    color: #e54856 !important;
}

.sales-tabs-page .erp-action-delete:hover,
.sales-tabs-page .erp-action-delete:focus {
    border-color: #ee929c !important;
    background: #ffeaec !important;
}

.sales-tabs-page table.dataTable th.sales-actions-heading,
.sales-tabs-page table.dataTable td.sales-actions-cell {
    min-width: 220px !important;
    width: 220px !important;
    text-align: center !important;
    vertical-align: middle !important;
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
    font-size: 16px;
}

.sales-tabs-page .dataTables_info {
    padding-top: 11px !important;
    color: #64748b;
    font-size: 16px;
    font-weight: 700;
}

.sales-tabs-page .pagination > li > a,
.sales-tabs-page .pagination > li > span {
    margin-left: 4px;
    border-color: #dbe3ed;
    border-radius: 7px !important;
    color: #334155;
    font-size: 16px;
}

.sales-tabs-page .pagination > .active > a,
.sales-tabs-page .pagination > .active > span {
    border-color: #2c7f75 !important;
    background: #2c7f75 !important;
    color: #fff !important;
}

/* =========================================================
   Purchase Listing-style label/action meanings
   ========================================================= */
.sales-tabs-page .sales-label-meaning {
    margin: 14px 0 0;
    padding: 12px 14px 14px;
    border: 1px solid #d7e3ef;
    border-radius: 12px;
    background: #f8fbff;
}

.sales-tabs-page .sales-label-meaning-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: #172033;
    font-size: 15px;
    font-weight: 700;
}

.sales-tabs-page .sales-label-meaning-groups {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
    gap: 8px;
}

.sales-tabs-page .sales-label-group {
    display: contents;
}

.sales-tabs-page .sales-label-item {
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

.sales-tabs-page .sales-label-item > span:last-child {
    min-width: 0;
    overflow-wrap: anywhere;
}

.sales-tabs-page .sales-label-item .sale_status {
    min-width: 52px;
    text-align: center;
    flex-shrink: 0;
}

.sales-tabs-page .sales-label-item .erp-action-icon-btn {
    width: 30px;
    height: 30px;
    flex-basis: 30px;
    flex-shrink: 0;
    pointer-events: none;
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
    .sales-tabs-page .sales-label-meaning {
        padding: 11px 10px 12px;
    }

    .sales-tabs-page .sales-label-meaning-groups {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .sales-tabs-page .sales-label-item {
        padding: 7px 8px;
        font-size: 12px;
    }

    .sales-tabs-page .erp-actions {
        min-width: 190px;
    }

    .sales-tabs-page .erp-action-buttons {
        gap: 5px;
    }

    .sales-tabs-page .erp-action-icon-btn {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
    }

    .sales-tabs-page table.dataTable th.sales-actions-heading,
    .sales-tabs-page table.dataTable td.sales-actions-cell {
        min-width: 190px !important;
        width: 190px !important;
    }

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
        font-size: 16px;
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
        font-size: 16px;
    }

    .sales-tabs-page .erp-tab-content {
        padding: 8px;
    }

    /* Four sales cards = 2 x 2 */
    .sales-tabs-page .erp-summary-four {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 5px !important;
        margin: 15px 0;
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
        min-height: 72px !important;
        height: 72px !important;
        padding: 10px 11px 8px !important;
        border-radius: 7px !important;
        box-shadow: none !important;
    }

    .sales-tabs-page .erp-stat-card::before {
        width: 3px !important;
    }

    .sales-tabs-page .erp-stat-label {
        margin: 0 0 2px !important;
        font-size: 16px !important;
        line-height: 1.35 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .sales-tabs-page .erp-stat-value {
        padding: 0 !important;
        font-size: 18px !important;
        line-height: 1.3 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .sales-tabs-page .erp-stat-icon {
        display: none !important;
    }

    /* Keep three filters in one row on mobile */
    .sales-tabs-page .erp-filter-card {
        padding: 11px;
        margin-bottom: 15px;
    }

    .sales-tabs-page .erp-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 9px !important;
    }

    .sales-tabs-page .erp-filter-card label {
        min-height: 20px;
        margin-bottom: 5px;
        font-size: 16px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sales-tabs-page .erp-filter-card .form-control,
    .sales-tabs-page .erp-filter-card .select2-container .select2-choice,
    .sales-tabs-page .erp-filter-card .select2-container--default .select2-selection--single {
        height: 38px !important;
        min-height: 38px !important;
        font-size: 16px !important;
    }

    .sales-tabs-page .erp-filter-card .select2-container .select2-choice,
    .sales-tabs-page .erp-filter-card .select2-container .select2-choice > .select2-chosen,
    .sales-tabs-page .erp-filter-card .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        line-height: 36px !important;
    }

    .sales-tabs-page .erp-filter-card .select2-container .select2-choice .select2-arrow,
    .sales-tabs-page .erp-filter-card .select2-container--default
    .select2-selection--single
    .select2-selection__arrow {
        height: 36px !important;
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
        min-height: 38px;
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
        padding: 7px 10px !important;
        font-size: 16px !important;
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
        height: 38px;
        padding-left: 34px;
        font-size: 16px;
    }

    .sales-tabs-page .erp-search-box i {
        left: 11px;
        font-size: 16px;
    }

    .sales-tabs-page .erp-toolbar .btn {
        min-height: 38px;
        padding: 7px 10px;
        font-size: 16px;
        white-space: nowrap;
    }

    .sales-tabs-page .erp-scroll-hint {
        margin-bottom: 7px;
        font-size: 16px;
    }

    .sales-tabs-page table.dataTable thead th,
    .sales-tabs-page table.dataTable tbody td,
    .sales-tabs-page table.dataTable tfoot th {
        padding: 7px 6px !important;
        font-size: 16px !important;
    }

    .sales-tabs-page .dataTables_info {
        font-size: 16px;
    }

    .sales-tabs-page .pagination > li > a,
    .sales-tabs-page .pagination > li > span {
        padding: 7px 10px;
        font-size: 16px;
    }
}

@media (max-width: 380px) {
    .sales-tabs-page .sales-label-meaning-groups {
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 360px) {
    .sales-tabs-page .erp-stat-value {
        font-size: 16px !important;
    }

    .sales-tabs-page ul.nav.nav-tabs.erp-tabs > li > a {
        font-size: 16px !important;
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
        font-size: 16px;
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

    /* Only the filter card is collapsed on the Sales tab */
    .sales-tabs-page .erp-filter-card.mobile-filter-collapsed {
        display: none !important;
    }

    /* Due summary cards may still use the existing summary toggle */
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


.sales-tabs-page #refresh_sales,
.sales-tabs-page #refresh_due {
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
    .sales-tabs-page .erp-summary-four {
        gap: 8px !important;
        margin-bottom: 20px !important;
    }

    .sales-tabs-page .mobile-filter-toggle {
        margin: 10px 0 1px !important;
        padding: 11px 12px !important;
        font-size: 16px !important;
    }

    .sales-tabs-page .erp-filter-card .select2-container .select2-choice > .select2-chosen,
    .sales-tabs-page .erp-filter-card .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        font-size: 16px !important;
    }

    .sales-tabs-page .erp-search-box .form-control,
    .sales-tabs-page .erp-toolbar .btn,
    .sales-tabs-page .dataTables_info,
    .sales-tabs-page .pagination > li > a,
    .sales-tabs-page .pagination > li > span {
        font-size: 16px !important;
    }

    .sales-tabs-page .sale_status,
    .sales-tabs-page .preorder_status,
    .sales-tabs-page .deliver_preorder {
        font-size: 16px !important;
    }

    .sales-delivery-dialog {
        width: calc(100vw - 10px);
        max-width: calc(100vw - 10px);
        margin: 5px auto;
    }

    .sales-delivery-modal .modal-header {
        padding: 10px 11px;
    }

    .sales-delivery-modal .modal-title {
        font-size: 16px;
    }

    .sales-delivery-modal .modal-body {
        max-height: calc(100vh - 125px);
        padding: 7px;
    }

    .sales-delivery-meta,
    .sales-delivery-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 5px;
        padding: 7px;
    }

    .sales-delivery-summary {
        padding: 0;
    }

    .sales-delivery-meta-label,
    .sales-delivery-summary-label {
        min-height: 32px;
        font-size: 10px;
    }

    .sales-delivery-meta-value,
    .sales-delivery-summary-value {
        font-size: 13px;
    }

    .sales-delivery-fields {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .sales-delivery-table {
        min-width: 650px;
    }

    .sales-delivery-table th,
    .sales-delivery-table td {
        padding: 8px 6px !important;
        font-size: 13px;
    }

    .sales-delivery-modal .modal-footer {
        padding: 7px;
    }

    .sales-delivery-modal .modal-footer .btn {
        flex: 1 1 0;
        padding: 8px 6px;
        font-size: 13px;
    }
}

</style>

<script>
var salesTable = null;
var customerDueTable = null;
var salesSummaryRequest = null;
</script>

<script type="text/javascript">
window.toggleMobileFilter = function (button) {
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
                window.salesTable &&
                window.salesTable.columns
            ) {
                window.salesTable.columns.adjust();
            }
        } catch (error) {}
    }, 60);

    return false;
};

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

    /*
     * Delivery status buttons are created by DataTables after page load.
     * Bind in capture phase before any other Sales initialisation so a
     * separate runtime error or legacy handler cannot block the popup.
     */
    var activeSalesDeliveryId = 0;
    var activeSalesDeliveryItemId = 0;
    var salesDeliveryActionMode = '';
    var salesDeliveryActionId = 0;
    var salesDeliveryCsrfName =
        <?= json_encode($this->security->get_csrf_token_name()); ?>;
    var salesDeliveryCsrfHash =
        <?= json_encode($this->security->get_csrf_hash()); ?>;

    if (!window.klSalesDeliveryCaptureBound) {
        document.addEventListener('click', function (event) {
            var button = event.target && event.target.closest
                ? event.target.closest(
                    '.sales-delivery-open, ' +
                    '.js-sales-delivery-history, ' +
                    '.js-sales-delivery-edit, ' +
                    '.js-sales-delivery-delete, ' +
                    '[data-sales-delivery-action-close], ' +
                    '#confirmSalesDeliveryAction, ' +
                    '#fillAllSalesDeliveryRemaining, ' +
                    '#saveSalesDeliveryBtn'
                )
                : null;

            if (!button || !button.closest('.sales-tabs-page')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            if (button.classList.contains('sales-delivery-open')) {
                loadSalesDeliveryForm(
                    button.getAttribute('data-id'),
                    true,
                    false
                );
                return;
            }

            if (button.classList.contains('js-sales-delivery-history')) {
                loadSalesDeliveryHistory(
                    button.getAttribute('data-item-id'),
                    button.getAttribute('data-product-name') || ''
                );
                return;
            }

            if (button.classList.contains('js-sales-delivery-edit')) {
                openSalesDeliveryAction(
                    'edit',
                    button.getAttribute('data-id'),
                    button.getAttribute('data-quantity')
                );
                return;
            }

            if (button.hasAttribute('data-sales-delivery-action-close')) {
                closeSalesDeliveryAction(false);
                return;
            }

            if (button.id === 'confirmSalesDeliveryAction') {
                confirmSalesDeliveryHistoryAction();
                return;
            }

            if (button.id === 'fillAllSalesDeliveryRemaining') {
                fillAllSalesDeliveryRemaining();
                return;
            }

            if (button.id === 'saveSalesDeliveryBtn') {
                saveSalesDelivery();
                return;
            }

            openSalesDeliveryAction(
                'delete',
                button.getAttribute('data-id'),
                0
            );
        }, true);

        window.klSalesDeliveryCaptureBound = true;
    }

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

    /*
     * Controller က permission အလိုက် ပို့လာသော Actions links ကိုပဲယူပြီး
     * Purchase Listing ပုံစံ icon buttons အဖြစ် ပြန်ပြမည်။
     */
    function renderResponsiveActions(data, type, row) {
        if (type && type !== 'display') {
            return data;
        }

        if (!data) {
            return '<div class="erp-actions"></div>';
        }

        var $temp = $('<div>').html(data);
        var actionHtml = '';

        $temp.find('a, button').each(function () {
            var $link = $(this);

            /* Dropdown ကိုဖွင့်သည့် မူလ Actions button ကို မထည့်ပါ။ */
            if ($link.hasClass('dropdown-toggle')) {
                return;
            }

            /* မူလ preorder စည်းမျဉ်းအတိုင်း Edit action ကို ဖျောက်ထားမည်။ */
            if (
                String($link.closest('li').attr('data-edit-visible') || '') === '1'
            ) {
                return;
            }

            var href = ($link.attr('href') || '').toLowerCase();
            var originalClass = ($link.attr('class') || '').toLowerCase();
            var originalText = $.trim($link.text());
            var iconClasses = ($link.find('i').first().attr('class') || '').toLowerCase();
            var matcher = [
                href,
                originalClass,
                originalText.toLowerCase(),
                iconClasses
            ].join(' ');

            var actionClass = 'erp-action-default';
            var iconHtml = '';
            var title = $link.attr('title') || originalText || 'Action';
            var isDeleteAction = false;

            /*
             * Purchases Listing ပုံစံအတိုင်း Add Payment ကို
             * သီးခြား action အဖြစ် မပြပါ။ Payment စာမျက်နှာထဲမှသာ
             * ငွေပေးချေမှုအသစ် ထည့်မည်။
             */
            if (
                matcher.indexOf('add_payment') !== -1 ||
                matcher.indexOf('add-payment') !== -1 ||
                matcher.indexOf('အကြွေးပေး') !== -1
            ) {
                return;
            }
            else if (
                matcher.indexOf('/payments/') !== -1 ||
                matcher.indexOf('payment history') !== -1 ||
                matcher.indexOf('view payment') !== -1 ||
                matcher.indexOf('ငွေပေးချေမှု') !== -1
            ) {
                actionClass = 'erp-action-payments';
                iconHtml = '<i class="fa fa-credit-card"></i>';
            }
            else if (
                matcher.indexOf('/view/') !== -1 ||
                matcher.indexOf('fa-eye') !== -1 ||
                matcher.indexOf('ကြည့်ရန်') !== -1
            ) {
                actionClass = 'erp-action-view';
                iconHtml = '<i class="fa fa-eye"></i>';
            }
            else if (
                matcher.indexOf('/edit/') !== -1 ||
                matcher.indexOf('fa-edit') !== -1 ||
                matcher.indexOf('fa-pencil') !== -1
            ) {
                actionClass = 'erp-action-edit';
                iconHtml = '<i class="fa fa-pencil"></i>';
            }
            else if (
                matcher.indexOf('/return') !== -1 ||
                matcher.indexOf('fa-undo') !== -1 ||
                matcher.indexOf('fa-reply') !== -1
            ) {
                actionClass = 'erp-action-return';
                iconHtml = '<i class="fa fa-undo"></i>';
            }
            else if (
                matcher.indexOf('/delete/') !== -1 ||
                matcher.indexOf('delete') !== -1 ||
                matcher.indexOf('text-danger') !== -1 ||
                matcher.indexOf('fa-trash') !== -1 ||
                matcher.indexOf('ဖျက်ရန်') !== -1
            ) {
                actionClass = 'erp-action-delete';
                isDeleteAction = true;
                iconHtml = '<i class="fa fa-trash"></i>';
            }
            else if (
                matcher.indexOf('print') !== -1 ||
                matcher.indexOf('invoice') !== -1 ||
                matcher.indexOf('fa-print') !== -1
            ) {
                actionClass = 'erp-action-print';
                iconHtml = '<i class="fa fa-print"></i>';
            }
            else if (
                matcher.indexOf('delivery') !== -1 ||
                matcher.indexOf('deliver') !== -1 ||
                matcher.indexOf('fa-truck') !== -1
            ) {
                actionClass = 'erp-action-delivery';
                iconHtml = '<i class="fa fa-truck"></i>';
            }
            else if (
                matcher.indexOf('email') !== -1 ||
                matcher.indexOf('fa-envelope') !== -1
            ) {
                actionClass = 'erp-action-email';
                iconHtml = '<i class="fa fa-envelope"></i>';
            }
            else if ($link.find('i').length) {
                iconHtml = $('<div>')
                    .append($link.find('i').first().clone())
                    .html();
            }
            else {
                iconHtml = '<i class="fa fa-circle-o"></i>';
            }

            /* Clone သုံးထားသဖြင့် onclick, data-id, modal classes စသည် မပျက်ပါ။ */
            var $button = $link.clone(false);

            $button
                .removeClass(
                    'btn btn-xs btn-sm btn-primary btn-info ' +
                    'btn-success btn-warning btn-danger'
                )
                .addClass('erp-action-icon-btn ' + actionClass)
                .attr('title', title)
                .attr('aria-label', title)
                .html(iconHtml);

            /*
             * Server ကပို့လာသော native onclick confirm() ကို Delete button မှ
             * ဖယ်ထားမှ browser popup ထဲ website URL မပေါ်တော့မည်။
             */
            if (isDeleteAction) {
                $button
                    .removeAttr('onclick')
                    .addClass('erp-delete-sale');
            }

            if ($button.is('button') && !$button.attr('type')) {
                $button.attr('type', 'button');
            }

            actionHtml += $('<div>').append($button).html();
        });

        return '<div class="erp-actions">' +
            '<div class="erp-action-buttons">' + actionHtml + '</div>' +
        '</div>';
    }

    function statusBadge(data, type) {
        if (type !== 'display') {
            return data;
        }

        var label = data || '';
        var labelClass = 'label-default';

        if (data === 'paid') {
            label = <?= json_encode($sales_listing_labels['paid_badge']); ?>;
            labelClass = 'label-success';
        } else if (data === 'partial') {
            label = <?= json_encode($sales_listing_labels['partial_badge']); ?>;
            labelClass = 'label-primary';
        } else if (data === 'due') {
            label = <?= json_encode($sales_listing_labels['due_badge']); ?>;
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
            return toNumber(data);
        }

        var pendingDeliveryTitle = $('<div>').text(
            <?= json_encode($sales_listing_labels['receive']); ?>
        ).html();
        var deliveredTitle = $('<div>').text(
            <?= json_encode($sales_listing_labels['received']); ?>
        ).html();
        var partialTitle = $('<div>').text(
            <?= json_encode($sales_listing_labels['partial_delivery']); ?>
        ).html();
        var deliveryStatus = toNumber(data);

        if (deliveryStatus === 0) {
            return '<div class="text-center">' +
                '<button type="button" ' +
                    'class="erp-action-icon-btn erp-action-receive deliver_preorder sales-delivery-open" ' +
                    'data-id="' + row.id + '" ' +
                    'title="' + pendingDeliveryTitle + '" ' +
                    'aria-label="' + pendingDeliveryTitle + '">' +
                    '<i class="fa fa-truck"></i>' +
                '</button>' +
            '</div>';
        }

        if (deliveryStatus === 2) {
            return '<div class="text-center">' +
                '<button type="button" ' +
                    'class="erp-action-icon-btn erp-action-delivery-partial deliver_preorder sales-delivery-open" ' +
                    'data-id="' + row.id + '" ' +
                    'title="' + partialTitle + '" ' +
                    'aria-label="' + partialTitle + '">' +
                    '<i class="fa fa-truck"></i>' +
                '</button>' +
            '</div>';
        }

        return '<div class="text-center">' +
            '<button type="button" ' +
                'class="erp-action-icon-btn erp-action-received-disabled deliver_preorder sales-delivery-open" ' +
                'data-id="' + row.id + '" ' +
                'title="' + deliveredTitle + '" ' +
                'aria-label="' + deliveredTitle + '">' +
                '<i class="fa fa-check"></i>' +
            '</button>' +
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

    function applyOverallSalesSummary(summary) {
        summary = summary || {};

        $('#sales_records').text(
            parseInt(summary.sales_records || 0, 10)
        );

        $('#sales_tab_count').text(
            parseInt(summary.sales_records || 0, 10)
        );

        $('#sales_grand_total').text(
            moneyText(summary.grand_total)
        );

        $('#sales_paid_total').text(
            moneyText(summary.paid_total)
        );

        $('#sales_due_total').text(
            moneyText(summary.due_total)
        );

        $('#due_tab_count').text(
            parseInt(summary.outstanding_sales || 0, 10)
        );

        /* Due cards ကို ရွေးထားသော Customer ၏ table data ဖြင့်ပဲတွက်မည်။ */
        if (!$.trim(String($('#due_customer_filter').val() || ''))) {
            $('#due_sales_records').text('0');
            $('#customer_due_total').text('0.00');
        }
    }

    function loadOverallSalesSummary() {
        if (salesSummaryRequest) {
            try {
                salesSummaryRequest.abort();
            } catch (abortError) {}
        }

        salesSummaryRequest = $.ajax({
            url: '<?= site_url('sales/get_sales_summary'); ?>',
            type: 'GET',
            dataType: 'json',
            cache: false,
            data: {
                _refresh: new Date().getTime()
            },
            success: function (response) {
                if (!response || response.success === false) {
                    console.error(
                        'KLSPOS Sales summary response is invalid:',
                        response
                    );
                    return;
                }

                applyOverallSalesSummary(
                    response.summary || response
                );
            },
            error: function (xhr, status, error) {
                if (status === 'abort') {
                    return;
                }

                console.error(
                    'KLSPOS Sales summary AJAX error:',
                    status,
                    error,
                    xhr.responseText
                );
            },
            complete: function () {
                salesSummaryRequest = null;
            }
        });
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
        $('#bulk_pay_btn').prop(
            'disabled',
            selectedCount === 0 || totalDue <= 0
        );
    }

    function resetCustomerDueSelection() {
        $('#check_all_due').prop('checked', false);
        $('#customerDueData tbody .sale-check').prop('checked', false);
        $('#selected_due_total').text('0.00');
        $('#selected_sale_count').text('0');
        $('#bulk_pay_amount').val('');
        $('#bulk_pay_btn').prop('disabled', true);
    }

    function resetCustomerDueSummary() {
        $('#due_sales_records').text('0');
        $('#customer_due_total').text('0.00');
        resetCustomerDueSelection();
    }

    function updateCustomerDueWorkspace() {
        var customerName = $.trim(
            String($('#due_customer_filter').val() || '')
        );
        var workspaceWasVisible = $('#customerDueWorkspace').is(':visible');

        resetCustomerDueSummary();

        if (!customerName) {
            $('#customerDueWorkspace').hide();
            $('#customerDueEmptyState').show();

            if (
                workspaceWasVisible &&
                customerDueTable &&
                customerDueTable.ajax
            ) {
                customerDueTable.ajax.reload(null, true);
            }

            return;
        }

        $('#customerDueEmptyState').hide();
        $('#customerDueWorkspace').show();

        if (customerDueTable && customerDueTable.ajax) {
            customerDueTable.ajax.reload(function () {
                customerDueTable.columns.adjust();
            }, true);
        }
    }

    /*
     * Card totals come from a separate aggregate endpoint.
     * They do not depend on DataTable page length, search, filters
     * or pagination.
     */
    loadOverallSalesSummary();

    /*
     * Purchase table ကဲ့သို့ Sales table ကို Header/Body တစ်ခုတည်းဖြစ်စေမည်။
     * DataTables clone header မသုံးဘဲ အပြင်ဘက် .erp-table-wrap တစ်ခုတည်းကို
     * horizontal scroll လုပ်ထားသောကြောင့် ဘယ်/ညာဆွဲသည့်အခါ ကော်လံမလွဲပါ။
     */
    function syncSalesTableColumns(resetScrollPosition) {
        var $wrapper = $('#SLData_wrapper');
        var $table = $('#SLData');

        if (!$wrapper.length || !$table.length) {
            return;
        }

        var minimumWidths = [52, 170, 150, 120, 110, 130, 130, 160];
        var minimumTotalWidth = 1022;
        var $outerWrap = $wrapper.closest('.erp-table-wrap');
        var availableWidth = Math.floor($outerWrap.innerWidth() || 0);
        var totalWidth = Math.max(minimumTotalWidth, availableWidth);
        var extraWidth = totalWidth - minimumTotalWidth;
        var expandWeights = [0, 0.08, 0.25, 0.12, 0.10, 0.12, 0.12, 0.21];
        var widths = minimumWidths.slice();
        var allocatedExtra = 0;

        if (extraWidth > 0) {
            for (var widthIndex = 0; widthIndex < widths.length - 1; widthIndex++) {
                var addition = Math.floor(extraWidth * expandWeights[widthIndex]);

                widths[widthIndex] += addition;
                allocatedExtra += addition;
            }

            widths[widths.length - 1] += extraWidth - allocatedExtra;
        }

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

        /* Sales tab ကိုဖွင့်ချိန်မှာ ID ကော်လံဘက်ကနေ စပြမည်။ */
        if (resetScrollPosition) {
            $outerWrap.scrollLeft(0);
        }
    }

    function mergeSalesHeaderIntoBody(api) {
        var $wrapper = $('#SLData_wrapper');

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
            .find('.dataTables_scrollBody table#SLData')
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

    function scheduleSalesTableColumnSync(api, resetScrollPosition) {
        window.setTimeout(function () {
            mergeSalesHeaderIntoBody(api);

            if (api) {
                api.columns.adjust();
            }

            window.setTimeout(function () {
                mergeSalesHeaderIntoBody(api);
                syncSalesTableColumns(resetScrollPosition === true);
            }, 0);
        }, 30);
    }

    /* Global layout က အရင် initialize လုပ်ထားလျှင် Purchase လိုပဲ ပြန်တည်ဆောက်မည်။ */
    var $salesTableElement = $('#SLData');

    if (
        $.fn.DataTable &&
        $.fn.DataTable.isDataTable($salesTableElement.get(0))
    ) {
        $salesTableElement.DataTable().destroy();
    }

    salesTable = $salesTableElement.DataTable({
        dom: 'Brtip',
        /* Purchase table ကဲ့သို့ outer wrapper တစ်ခုတည်းကို scroll သုံးမည်။ */
        scrollX: '',
        scrollCollapse: false,
        autoWidth: false,
        responsive: false,
        columnDefs: [
            {targets: 0, width: '52px'},
            {targets: 1, width: '170px'},
            {targets: 2, width: '150px'},
            {targets: 3, width: '120px'},
            {targets: 4, width: '110px'},
            {targets: [5, 6], width: '130px'},
            {targets: 7, width: '160px'}
        ],
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
            {data: 'delivery_status', render: preorderBadge, className: 'text-center'},
            {
                data: 'Actions',
                searchable: false,
                orderable: false,
                className: 'text-center sales-actions-cell',
                render: function (data, type, row) {
                    return renderResponsiveActions(data, type, row);
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

            /*
             * Table footer follows the current DataTable result only.
             * Summary cards are loaded separately from the server.
             */
        },
        drawCallback: function () {
            var api = this.api();

            $('li[data-edit-visible]').each(function () {
                if ($(this).attr('data-edit-visible') == 1) {
                    $(this).hide();
                }
            });

            scheduleSalesTableColumnSync(api, false);
        },
        initComplete: function () {
            var api = this.api();

            api.buttons()
                .container()
                .appendTo('#salesDataButtons');

            scheduleSalesTableColumnSync(api, true);
        }
    });

    customerDueTable = $('#customerDueData').DataTable({
        dom: 'Brtip',
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        responsive: false,
        columnDefs: [
            {
                targets: 0,
                width: '42px',
                orderable: false,
                bSortable: false,
                searchable: false,
                className: 'text-center due-checkbox-column'
            },
            {targets: 1, width: '52px'},
            {targets: 2, width: '170px'},
            {targets: 3, width: '150px'},
            {targets: 4, width: '120px'},
            {targets: [5, 6], width: '110px'},
            {targets: 7, width: '135px'}
        ],
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

                if (!$.trim(String($('#due_customer_filter').val() || ''))) {
                    return [];
                }

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
                        '<td colspan="8" class="text-center text-danger">' +
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
                exportOptions: {columns: [1,2,3,4,5,6,7]}
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                footer: true,
                exportOptions: {columns: [1,2,3,4,5,6,7]}
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                footer: true,
                exportOptions: {columns: [1,2,3,4,5,6,7]}
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {columns: [1,2,3,4,5,6,7]}
            }
        ],
        columns: [
            {
                data: 'id',
                searchable: false,
                orderable: false,
                bSortable: false,
                className: 'text-center due-checkbox-column',
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
            {data: 'status', render: statusBadge, className: 'text-center'}
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

            $('#due_sales_records').text(
                api.rows({search: 'applied'}).count()
            );
            $('#customer_due_total').text(moneyText(dueTotal));
        },
        drawCallback: function () {
            $('#customerDueData_wrapper, #customerDueData')
                .find('thead th:first-child')
                .removeClass('sorting sorting_asc sorting_desc')
                .addClass('sorting_disabled due-checkbox-column')
                .removeAttr('aria-sort');

            $('#check_all_due').prop('checked', false);
            updateSelectedDue();
        },
        initComplete: function () {
            var api = this.api();

            api.buttons()
                .container()
                .appendTo('#dueDataButtons');

            $('#customerDueData_wrapper, #customerDueData')
                .find('thead th:first-child')
                .removeClass('sorting sorting_asc sorting_desc')
                .addClass('sorting_disabled due-checkbox-column')
                .removeAttr('aria-sort');

            api.columns.adjust();
        }
    });

    /*
     * URL မပါတဲ့ Sales Delete Confirmation Modal
     * Capture phase မှာဖမ်းထားသဖြင့် မူလ inline/global confirm handler မလုပ်တော့ပါ။
     */
    var pendingSalesDeleteUrl = '';

    document.addEventListener('click', function (event) {
        var deleteButton = event.target.closest
            ? event.target.closest('.erp-delete-sale')
            : null;
        var salesTableWrapper = $('#SLData_wrapper').get(0);

        if (
            !deleteButton ||
            !salesTableWrapper ||
            !salesTableWrapper.contains(deleteButton)
        ) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        pendingSalesDeleteUrl = deleteButton.getAttribute('href') || '';

        if (!pendingSalesDeleteUrl || pendingSalesDeleteUrl === '#') {
            return;
        }

        $('#salesDeleteReason').val('');
        $('#salesDeleteConfirmError').hide().text('');

        $('#salesDeleteConfirmModal')
            .one('shown.bs.modal', function () {
                $('#salesDeleteReason').trigger('focus');
            })
            .modal('show');
    }, true);

    $('#confirmSalesDeleteBtn').on('click', function () {
        var deleteUrl = pendingSalesDeleteUrl;
        var $button = $(this);
        var deleteReason = $.trim($('#salesDeleteReason').val() || '');

        if (!deleteUrl || $button.prop('disabled')) {
            return;
        }

        if (!deleteReason) {
            $('#salesDeleteConfirmError')
                .text(
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ဖျက်ရသည့်အကြောင်းပြချက်ကို ထည့်ပေးပါ။'
                            : 'Please enter a deletion reason.'
                    ); ?>
                )
                .show();
            $('#salesDeleteReason').trigger('focus');
            return;
        }

        $('#salesDeleteConfirmError').hide().text('');

        $button
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> ' +
                <?= json_encode($is_myanmar_action_language ? 'ဖျက်နေသည်...' : 'Deleting...'); ?>
            );

        $.ajax({
            url: deleteUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                sale_id: (deleteUrl.match(/\/delete\/(\d+)/) || [0, 0])[1],
                delete_reason: deleteReason,
                ajax_delete: 1,
                '<?= $this->security->get_csrf_token_name(); ?>':
                    '<?= $this->security->get_csrf_hash(); ?>'
            },
            success: function (response) {
                $('#salesDeleteConfirmModal').modal('hide');

                $('#salesDeliveryNotice')
                    .removeClass('alert-danger')
                    .addClass('alert-success')
                    .html(
                        '<i class="fa fa-check-circle"></i> ' +
                        $('<div>').text(
                            response.message ||
                            <?= json_encode(
                                $is_myanmar_action_language
                                    ? 'အရောင်းဘောင်ချာကို အောင်မြင်စွာ ဖျက်ပြီးပါပြီ။'
                                    : 'Sale deleted successfully.'
                            ); ?>
                        ).html()
                    )
                    .stop(true, true)
                    .slideDown(160)
                    .delay(4000)
                    .slideUp(220);

                if (salesTable) {
                    salesTable.ajax.reload(null, false);
                }

                if (customerDueTable) {
                    customerDueTable.ajax.reload(null, false);
                }

                loadOverallSalesSummary();
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                var message = response.message || $.trim(xhr.responseText || '') ||
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'အရောင်းဘောင်ချာကို ဖျက်၍မရပါ။'
                            : 'The sale could not be deleted.'
                    ); ?>;

                $('#salesDeleteConfirmError')
                    .text(message)
                    .show();

                $button
                    .prop('disabled', false)
                    .html('<i class="fa fa-trash"></i> ' +
                        <?= json_encode(
                            $is_myanmar_action_language
                                ? 'ဖျက်မည်'
                                : 'Delete'
                        ); ?>
                    );
            }
        });
    });

    $('#salesDeleteConfirmModal').on('hidden.bs.modal', function () {
        pendingSalesDeleteUrl = '';
        $('#salesDeleteReason').val('');
        $('#salesDeleteConfirmError').hide().text('');

        $('#confirmSalesDeleteBtn')
            .prop('disabled', false)
            .html('<i class="fa fa-trash"></i> ' +
                <?= json_encode($is_myanmar_action_language ? 'ဖျက်မည်' : 'Delete'); ?>
            );
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

    $('#customerDueHelpToggle')
        .off('click.customerDueHelp')
        .on('click.customerDueHelp', function () {
            var $tab = $('#customer-due-tab');
            var isOpen = !$tab.hasClass('customer-due-help-open');

            $tab.toggleClass('customer-due-help-open', isOpen);

            $(this)
                .attr('aria-expanded', isOpen ? 'true' : 'false')
                .attr(
                    'title',
                    isOpen
                        ? <?= json_encode($is_myanmar_action_language ? 'အကူအညီပိတ်ရန်' : 'Hide help'); ?>
                        : <?= json_encode($is_myanmar_action_language ? 'အကူအညီကြည့်ရန်' : 'View help'); ?>
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
                        ? <?= json_encode($is_myanmar_action_language ? 'အကူအညီပိတ်ရန်' : 'Hide help'); ?>
                        : <?= json_encode($is_myanmar_action_language ? 'အကူအညီကြည့်ရန်' : 'View help'); ?>
                );
        });

    $('#due_customer_filter').on('change', function () {
        updateCustomerDueWorkspace();
    });

    updateCustomerDueWorkspace();

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
                loadOverallSalesSummary();
            },
            error: function (xhr) {
                alert(xhr.responseText || '<?= lang('payment_failed'); ?>');
            }
        });
    });

    /*
     * Purchase partial-receive flow mirrored as Sales partial delivery.
     * Every action stays inside the app and uses POST + CSRF.
     */
    function salesDeliveryPostData(extra) {
        extra = extra || {};
        extra[salesDeliveryCsrfName] = salesDeliveryCsrfHash;
        return extra;
    }

    function salesDeliveryEscape(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function salesDeliveryQty(value) {
        var number = parseFloat(value || 0);
        return (isFinite(number) ? number : 0).toFixed(2);
    }

    function salesDeliveryErrorMessage(xhr, fallback) {
        var response = xhr && xhr.responseJSON ? xhr.responseJSON : {};
        return response.message || $.trim((xhr && xhr.responseText) || '') ||
            fallback;
    }

    function showSalesDeliveryNotice(message, isError) {
        $('#salesDeliveryNotice')
            .removeClass('alert-success alert-danger')
            .addClass(isError ? 'alert-danger' : 'alert-success')
            .html(
                '<i class="fa ' +
                (isError ? 'fa-exclamation-triangle' : 'fa-check-circle') +
                '"></i> ' + salesDeliveryEscape(message)
            )
            .stop(true, true)
            .slideDown(160)
            .delay(4000)
            .slideUp(220);
    }

    function cleanSalesDeliveryQty(value) {
        value = String(value || '').replace(/[^0-9.]/g, '');
        var firstDot = value.indexOf('.');

        if (firstDot !== -1) {
            value = value.substring(0, firstDot + 1) +
                value.substring(firstDot + 1).replace(/\./g, '');
        }

        if (value.indexOf('.') !== -1) {
            var parts = value.split('.');
            value = parts[0] + '.' + (parts[1] || '').substring(0, 2);
        }

        return value;
    }

    function renderSalesDeliveryForm(response, preserveFields) {
        var previousDate = preserveFields
            ? $('#salesDeliveredAt').val()
            : '';
        var previousNote = preserveFields
            ? $('#salesDeliveryNote').val()
            : '';
        var sale = response.sale || {};
        var summary = response.summary || {};
        var items = response.items || [];

        $('#salesDeliverySaleId').val(sale.id || '');
        $('#salesDeliveryVoucher').text(
            (sale.reference_no || '') || ('#' + (sale.id || ''))
        );
        $('#salesDeliveryCustomer').text(sale.customer_name || '-');
        $('#salesDeliveryStore').text(sale.store_name || '-');
        $('#salesDeliveryOrderedTotal').text(
            salesDeliveryQty(summary.ordered)
        );
        $('#salesDeliveryDeliveredTotal').text(
            salesDeliveryQty(summary.delivered)
        );
        $('#salesDeliveryRemainingTotal').text(
            salesDeliveryQty(summary.remaining)
        );
        $('#salesDeliveredAt').val(
            previousDate || response.default_delivered_at || ''
        );
        $('#salesDeliveryNote').val(previousNote);

        var rows = '';

        $.each(items, function (_, item) {
            var hasRemaining = Number(item.remaining) > 0.000001;
            /*
             * Completed legacy rows may predate sale_delivery_logs.  Keep the
             * History button visible whenever any quantity is delivered; the
             * history popup will clearly show when no old log exists.
             */
            var hasHistory =
                Number(item.history_count) > 0 ||
                Number(item.delivered) > 0.000001;
            var currentCell = '<div class="sales-delivery-current-actions">';

            if (hasRemaining) {
                currentCell +=
                    '<input type="text" inputmode="decimal" ' +
                    'class="form-control sales-delivery-qty-input" ' +
                    'data-item-id="' + Number(item.id) + '" ' +
                    'data-max="' + Number(item.remaining) + '" ' +
                    'placeholder="0">';
            }

            if (hasHistory) {
                currentCell +=
                    '<button type="button" ' +
                    'class="sales-delivery-history-btn js-sales-delivery-history" ' +
                    'data-item-id="' + Number(item.id) + '" ' +
                    'data-product-name="' +
                    salesDeliveryEscape(item.product_name) + '">' +
                    '<i class="fa fa-history"></i>' +
                    '<span>' +
                    <?= json_encode(
                        $is_myanmar_action_language ? 'မှတ်တမ်း' : 'History'
                    ); ?> +
                    '</span></button>';
            }

            if (!hasRemaining && !hasHistory) {
                currentCell += '<span class="text-success">' +
                    '<i class="fa fa-check"></i></span>';
            }

            currentCell += '</div>';

            rows +=
                '<tr>' +
                    '<td>' +
                        '<span class="sales-delivery-product-name">' +
                            salesDeliveryEscape(item.product_name) +
                        '</span>' +
                        '<span class="sales-delivery-product-code">' +
                            salesDeliveryEscape(item.product_code) +
                        '</span>' +
                    '</td>' +
                    '<td>' + currentCell + '</td>' +
                    '<td class="text-right"><strong>' +
                        salesDeliveryQty(item.ordered) + ' ' +
                        salesDeliveryEscape(item.unit_name) +
                    '</strong></td>' +
                    '<td class="text-right"><strong>' +
                        salesDeliveryQty(item.delivered) + ' ' +
                        salesDeliveryEscape(item.unit_name) +
                    '</strong></td>' +
                    '<td class="text-right"><strong>' +
                        salesDeliveryQty(item.remaining) + ' ' +
                        salesDeliveryEscape(item.unit_name) +
                    '</strong></td>' +
                '</tr>';
        });

        if (!rows) {
            rows = '<tr><td colspan="5" class="sales-delivery-empty">' +
                <?= json_encode(
                    $is_myanmar_action_language
                        ? 'ပို့ဆောင်ရန်ပစ္စည်း မရှိပါ။'
                        : 'No items are available for delivery.'
                ); ?> +
                '</td></tr>';
        }

        $('#salesDeliveryItemsBody').html(rows);
        $('#salesDeliveryFillRow').toggle(
            Number(summary.remaining) > 0.000001
        );
        $('#saveSalesDeliveryBtn').toggle(
            Number(summary.remaining) > 0.000001
        );
        $('#salesDeliveryFormError').hide().text('');
    }

    function loadSalesDeliveryForm(saleId, openModal, preserveFields) {
        activeSalesDeliveryId = Number(saleId) || 0;

        if (!activeSalesDeliveryId) {
            return;
        }

        $('#salesDeliveryItemsBody').html(
            '<tr><td colspan="5" class="sales-delivery-loading">' +
                '<i class="fa fa-spinner fa-spin"></i> ' +
                <?= json_encode(
                    $is_myanmar_action_language
                        ? 'အချက်အလက်ရယူနေသည်...'
                        : 'Loading delivery details...'
                ); ?> +
            '</td></tr>'
        );

        if (openModal) {
            $('#salesDeliveryModal').modal('show');
        }

        $.ajax({
            url: <?= json_encode(site_url('sales/delivery_form')); ?>,
            type: 'POST',
            dataType: 'json',
            data: salesDeliveryPostData({sale_id: activeSalesDeliveryId})
        }).done(function (response) {
            if (response && response.success) {
                renderSalesDeliveryForm(response, preserveFields === true);
                return;
            }

            $('#salesDeliveryFormError')
                .text(
                    (response && response.message) ||
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပို့ဆောင်မှုအချက်အလက် ရယူ၍မရပါ။'
                            : 'Delivery details could not be loaded.'
                    ); ?>
                )
                .show();
        }).fail(function (xhr) {
            $('#salesDeliveryFormError')
                .text(salesDeliveryErrorMessage(
                    xhr,
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပို့ဆောင်မှုအချက်အလက် ရယူ၍မရပါ။'
                            : 'Delivery details could not be loaded.'
                    ); ?>
                ))
                .show();
        });
    }

    $(document)
        .off('input.salesDeliveryQty', '.sales-delivery-qty-input')
        .on('input.salesDeliveryQty', '.sales-delivery-qty-input', function () {
            var cleaned = cleanSalesDeliveryQty($(this).val());
            var maximum = Number($(this).data('max')) || 0;

            if (cleaned !== '' && Number(cleaned) > maximum) {
                cleaned = salesDeliveryQty(maximum);
            }

            $(this).val(cleaned);
        });

    function fillAllSalesDeliveryRemaining() {
        $('.sales-delivery-qty-input').each(function () {
            $(this).val(salesDeliveryQty($(this).data('max')));
        });
    }

    function saveSalesDelivery() {
        var $button = $('#saveSalesDeliveryBtn');
        var request = {
            sale_id: activeSalesDeliveryId,
            delivered_at: $('#salesDeliveredAt').val(),
            delivery_note: $('#salesDeliveryNote').val()
        };
        var hasQuantity = false;
        var invalidQuantity = false;

        $('.sales-delivery-qty-input').each(function () {
            var itemId = Number($(this).data('item-id')) || 0;
            var maximum = Number($(this).data('max')) || 0;
            var quantity = Number($(this).val()) || 0;

            if (quantity > maximum + 0.000001) {
                invalidQuantity = true;
                return false;
            }

            if (itemId && quantity > 0) {
                request['delivery_qty[' + itemId + ']'] = quantity;
                hasQuantity = true;
            }
        });

        if (invalidQuantity) {
            $('#salesDeliveryFormError')
                .text(
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပို့မည့်အရေအတွက်သည် ကျန်အရေအတွက်ထက် မကျော်ရပါ။'
                            : 'Delivery quantity cannot exceed the remaining quantity.'
                    ); ?>
                )
                .show();
            return;
        }

        if (!hasQuantity) {
            $('#salesDeliveryFormError')
                .text(
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပို့မည့် ပစ္စည်းအရေအတွက် တစ်ခုခုထည့်ပါ။'
                            : 'Enter at least one delivery quantity.'
                    ); ?>
                )
                .show();
            return;
        }

        if (!request.delivered_at) {
            $('#salesDeliveryFormError')
                .text(
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပို့ဆောင်သည့်ရက်စွဲ ရွေးပါ။'
                            : 'Choose the delivery date.'
                    ); ?>
                )
                .show();
            return;
        }

        request = salesDeliveryPostData(request);
        $('#salesDeliveryFormError').hide().text('');
        $button
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> ' +
                <?= json_encode(
                    $is_myanmar_action_language
                        ? 'သိမ်းနေသည်...'
                        : 'Saving...'
                ); ?>
            );

        $.ajax({
            url: <?= json_encode(site_url('sales/deliver_products')); ?>,
            type: 'POST',
            dataType: 'json',
            data: request
        }).done(function (response) {
            if (response && response.success) {
                $('#salesDeliveryModal').modal('hide');
                showSalesDeliveryNotice(response.message, false);
                salesTable.ajax.reload(null, false);
                customerDueTable.ajax.reload(null, false);
                return;
            }

            $('#salesDeliveryFormError')
                .text(
                    (response && response.message) ||
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပစ္စည်းပို့ဆောင်မှု သိမ်း၍မရပါ။'
                            : 'The delivery could not be saved.'
                    ); ?>
                )
                .show();
        }).fail(function (xhr) {
            $('#salesDeliveryFormError')
                .text(salesDeliveryErrorMessage(
                    xhr,
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပစ္စည်းပို့ဆောင်မှု သိမ်း၍မရပါ။'
                            : 'The delivery could not be saved.'
                    ); ?>
                ))
                .show();
        }).always(function () {
            $button
                .prop('disabled', false)
                .html('<i class="fa fa-truck"></i> ' +
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပစ္စည်းပို့ဆောင်မှု သိမ်းရန်'
                            : 'Save delivery'
                    ); ?>
                );
        });
    }

    function loadSalesDeliveryHistory(itemId, productName) {
        activeSalesDeliveryItemId = Number(itemId) || 0;
        $('#salesDeliveryHistoryProduct').text(productName || '');
        $('#salesDeliveryHistoryBody').html(
            '<tr><td colspan="5" class="sales-delivery-loading">' +
                '<i class="fa fa-spinner fa-spin"></i></td></tr>'
        );
        $('#salesDeliveryHistoryModal').modal('show');

        $.ajax({
            url: <?= json_encode(site_url('sales/delivery_history')); ?>,
            type: 'POST',
            dataType: 'json',
            data: salesDeliveryPostData({
                sale_id: activeSalesDeliveryId,
                sale_item_id: activeSalesDeliveryItemId
            })
        }).done(function (response) {
            var rows = '';

            if (response && response.success) {
                $.each(response.history || [], function (_, history) {
                    var actions = '-';

                    if (response.can_manage) {
                        actions =
                            '<div class="sales-delivery-current-actions">' +
                                '<button type="button" ' +
                                'class="sales-delivery-history-action is-edit js-sales-delivery-edit" ' +
                                'data-id="' + Number(history.id) + '" ' +
                                'data-quantity="' + Number(history.quantity) + '">' +
                                '<i class="fa fa-edit"></i></button>' +
                                '<button type="button" ' +
                                'class="sales-delivery-history-action is-delete js-sales-delivery-delete" ' +
                                'data-id="' + Number(history.id) + '">' +
                                '<i class="fa fa-trash"></i></button>' +
                            '</div>';
                    }

                    rows +=
                        '<tr>' +
                            '<td>' + salesDeliveryEscape(history.delivered_at) + '</td>' +
                            '<td class="text-right"><strong>' +
                                salesDeliveryQty(history.quantity) +
                            '</strong></td>' +
                            '<td>' + salesDeliveryEscape(history.created_by_name) + '</td>' +
                            '<td>' + salesDeliveryEscape(history.note || '-') + '</td>' +
                            '<td>' + actions + '</td>' +
                        '</tr>';
                });
            }

            if (!rows) {
                rows = '<tr><td colspan="5" class="sales-delivery-empty">' +
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'ပို့ဆောင်မှုမှတ်တမ်း မရှိသေးပါ။'
                            : 'No delivery history yet.'
                    ); ?> +
                    '</td></tr>';
            }

            $('#salesDeliveryHistoryBody').html(rows);
        }).fail(function (xhr) {
            $('#salesDeliveryHistoryBody').html(
                '<tr><td colspan="5" class="sales-delivery-empty text-danger">' +
                    salesDeliveryEscape(salesDeliveryErrorMessage(
                        xhr,
                        <?= json_encode(
                            $is_myanmar_action_language
                                ? 'ပို့ဆောင်မှုမှတ်တမ်း ဖွင့်၍မရပါ။'
                                : 'Delivery history could not be loaded.'
                        ); ?>
                    )) +
                '</td></tr>'
            );
        });
    }

    function openSalesDeliveryAction(mode, deliveryId, quantity) {
        salesDeliveryActionMode = mode;
        salesDeliveryActionId = Number(deliveryId) || 0;
        $('#salesDeliveryActionError').hide().text('');
        $('#salesDeliveryActionQuantity').val('');

        if (mode === 'edit') {
            $('#salesDeliveryActionTitle').text(
                <?= json_encode(
                    $is_myanmar_action_language
                        ? 'ပို့ဆောင်သည့်အရေအတွက် ပြင်ရန်'
                        : 'Edit delivered quantity'
                ); ?>
            );
            $('#salesDeliveryActionMessage').text(
                <?= json_encode(
                    $is_myanmar_action_language
                        ? 'ပို့ထားသည့် အရေအတွက်အသစ်ကို ထည့်ပါ။'
                        : 'Enter the new delivered quantity.'
                ); ?>
            );
            $('#salesDeliveryActionField').show();
            $('#salesDeliveryActionQuantity').val(
                salesDeliveryQty(quantity)
            );
            $('#confirmSalesDeliveryAction')
                .removeClass('btn-danger')
                .addClass('btn-primary')
                .text(
                    <?= json_encode(
                        $is_myanmar_action_language ? 'ပြင်မည်' : 'Update'
                    ); ?>
                );
        } else {
            $('#salesDeliveryActionTitle').text(
                <?= json_encode(
                    $is_myanmar_action_language
                        ? 'ပို့ဆောင်မှုမှတ်တမ်း ဖျက်ရန်'
                        : 'Delete delivery history'
                ); ?>
            );
            $('#salesDeliveryActionMessage').text(
                <?= json_encode(
                    $is_myanmar_action_language
                        ? 'ဤမှတ်တမ်းကို ဖျက်ပါက ဆက်စပ် preorder stock ကိုပါ ပြန်တင်ပါမည်။'
                        : 'Deleting this record also reverses its related preorder stock.'
                ); ?>
            );
            $('#salesDeliveryActionField').hide();
            $('#confirmSalesDeliveryAction')
                .removeClass('btn-primary')
                .addClass('btn-danger')
                .text(
                    <?= json_encode(
                        $is_myanmar_action_language ? 'ဖျက်မည်' : 'Delete'
                    ); ?>
                );
        }

        $('#salesDeliveryActionDialog')
            .addClass('is-open')
            .attr('aria-hidden', 'false');

        if (mode === 'edit') {
            window.setTimeout(function () {
                $('#salesDeliveryActionQuantity')
                    .trigger('focus')
                    .trigger('select');
            }, 50);
        }
    }

    function closeSalesDeliveryAction(forceClose) {
        if (
            $('#confirmSalesDeliveryAction').prop('disabled') &&
            forceClose !== true
        ) {
            return;
        }

        salesDeliveryActionMode = '';
        salesDeliveryActionId = 0;
        $('#salesDeliveryActionDialog')
            .removeClass('is-open')
            .attr('aria-hidden', 'true');
        $('#salesDeliveryActionError').hide().text('');
    }

    $('#salesDeliveryActionQuantity').on('input', function () {
        $(this).val(cleanSalesDeliveryQty($(this).val()));
    });

    function confirmSalesDeliveryHistoryAction() {
        var $button = $('#confirmSalesDeliveryAction');
        var url;
        var data = {delivery_id: salesDeliveryActionId};

        if (!salesDeliveryActionId || $button.prop('disabled')) {
            return;
        }

        if (salesDeliveryActionMode === 'edit') {
            var quantity = Number($('#salesDeliveryActionQuantity').val()) || 0;

            if (quantity <= 0) {
                $('#salesDeliveryActionError')
                    .text(
                        <?= json_encode(
                            $is_myanmar_action_language
                                ? '0 ထက်ကြီးသော အရေအတွက်ထည့်ပါ။'
                                : 'Enter a quantity greater than zero.'
                        ); ?>
                    )
                    .show();
                return;
            }

            data.quantity = quantity;
            url = <?= json_encode(site_url('sales/update_delivery_history')); ?>;
        } else {
            url = <?= json_encode(site_url('sales/delete_delivery_history')); ?>;
        }

        data = salesDeliveryPostData(data);
        $('#salesDeliveryActionError').hide().text('');
        $button.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data
        }).done(function (response) {
            if (response && response.success) {
                closeSalesDeliveryAction(true);
                loadSalesDeliveryHistory(
                    activeSalesDeliveryItemId,
                    $('#salesDeliveryHistoryProduct').text()
                );
                loadSalesDeliveryForm(activeSalesDeliveryId, false, true);
                salesTable.ajax.reload(null, false);
                customerDueTable.ajax.reload(null, false);
                showSalesDeliveryNotice(response.message, false);
                return;
            }

            $('#salesDeliveryActionError')
                .text(
                    (response && response.message) ||
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'လုပ်ဆောင်၍မရပါ။'
                            : 'The action could not be completed.'
                    ); ?>
                )
                .show();
        }).fail(function (xhr) {
            $('#salesDeliveryActionError')
                .text(salesDeliveryErrorMessage(
                    xhr,
                    <?= json_encode(
                        $is_myanmar_action_language
                            ? 'လုပ်ဆောင်၍မရပါ။'
                            : 'The action could not be completed.'
                    ); ?>
                ))
                .show();
        }).always(function () {
            $button.prop('disabled', false);
        });
    }

    $('#salesDeliveryHistoryModal')
        .on('shown.bs.modal', function () {
            $('.modal-backdrop').last()
                .addClass('sales-delivery-history-backdrop');
        })
        .on('hidden.bs.modal', function () {
            if ($('#salesDeliveryModal').hasClass('in')) {
                $('body').addClass('modal-open');
            }
        });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        var salesTabIsShown = $(this).attr('href') === '#sales-list-tab';

        initialiseSalesSelect2(false);

        window.setTimeout(function () {
            if (salesTable) {
                salesTable.columns.adjust().draw(false);
                scheduleSalesTableColumnSync(salesTable, salesTabIsShown);
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
                scheduleSalesTableColumnSync(salesTable, false);
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
                <li
                    role="presentation"
                    class="<?= $active_sales_tab === 'sales'
                        ? 'active'
                        : ''; ?>"
                >
                    <a
                        href="#sales-list-tab"
                        aria-controls="sales-list-tab"
                        role="tab"
                        data-toggle="tab"
                    >
                        
                        <?= lang('sales_list_tab'); ?>
                        
                    </a>
                </li>

                <li
                    role="presentation"
                    class="<?= $active_sales_tab === 'due'
                        ? 'active'
                        : ''; ?>"
                >
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
            <div
                role="tabpanel"
                class="tab-pane <?= $active_sales_tab === 'sales'
                    ? 'active'
                    : ''; ?>"
                id="sales-list-tab"
            >

                <div
                    id="salesDeliveryNotice"
                    class="alert"
                    role="status"
                    aria-live="polite"
                    style="display:none; margin:0 0 12px;"
                ></div>

                <div
                    id="salesSummaryGrid"
                    class="erp-summary-grid erp-summary-four"
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

                <button
                    type="button"
                    class="mobile-summary-toggle mobile-filter-toggle"
                    data-target="#salesFilterCard"
                    aria-expanded="false"
                    onclick="return toggleMobileFilter(this);"
                >
                    <span class="toggle-left">
                        <i class="fa fa-filter"></i>
                        <span class="toggle-label"><?= lang('filter'); ?></span>
                    </span>
                    <i class="fa fa-chevron-down toggle-icon"></i>
                </button>

                <div
                    id="salesFilterCard"
                    class="erp-filter-card mobile-filter-collapsed"
                >
                    <div class="erp-filter-grid">
                        <div class="erp-filter-cell">
                            <label for="sales_customer_filter"><?= lang('customer'); ?></label>

                            <select
                                id="sales_customer_filter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= lang('select_all'); ?></option>

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
                            <label for="sales_status_filter"><?= html_escape($sales_listing_labels['payment_status']); ?></label>

                            <select
                                id="sales_status_filter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= lang('select_all'); ?></option>
                                <option value="paid"><?= html_escape($sales_listing_labels['paid_badge']); ?></option>
                                <option value="partial"><?= html_escape($sales_listing_labels['partial_badge']); ?></option>
                                <option value="due"><?= html_escape($sales_listing_labels['due_badge']); ?></option>
                                <option value="partial|due"><?= lang('partial_and_due'); ?></option>
                            </select>
                        </div>

                        <div class="erp-filter-cell">
                            <label for="sales_preorder_filter"><?= html_escape($sales_listing_labels['receipt_status']); ?></label>

                            <select
                                id="sales_preorder_filter"
                                class="form-control select2 erp-select2"
                            >
                                <option value=""><?= lang('select_all'); ?></option>
                                <option value="0"><?= html_escape($sales_listing_labels['receive']); ?></option>
                                <option value="2"><?= html_escape($sales_listing_labels['partial_delivery']); ?></option>
                                <option value="1"><?= html_escape($sales_listing_labels['received']); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <p class="erp-scroll-hint" style="margin-top:18px;">
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

                        
                    </div>
                </div>

                <div class="erp-table-wrap">
                    <table
                        id="SLData"
                        class="table table-striped table-bordered table-hover"
                        style="width:100%;"
                    >
                        <colgroup>
                            <col style="width:52px;">
                            <col style="width:170px;">
                            <col style="width:150px;">
                            <col style="width:120px;">
                            <col style="width:110px;">
                            <col style="width:130px;">
                            <col style="width:130px;">
                            <col style="width:160px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th><?= lang('id'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('customer'); ?></th>
                                <th class="text-right">
                                    <span class="sales-table-header-label">
                                        <?= $is_myanmar_action_language
                                            ? 'စုစုပေါင်း<br>ကျသင့်ငွေ'
                                            : 'Grand<br>Total'; ?>
                                    </span>
                                </th>
                                <th class="text-right">
                                    <span class="sales-table-header-label">
                                        <?= $is_myanmar_action_language
                                            ? 'ပေးဆောင်<br>ပြီး'
                                            : 'Paid'; ?>
                                    </span>
                                </th>
                                <th>
                                    <span class="sales-table-header-label">
                                        <?= $is_myanmar_action_language
                                            ? 'ငွေပေးချေမှု<br>အခြေအနေ'
                                            : 'Payment<br>Status'; ?>
                                    </span>
                                </th>
                                <th>
                                    <span class="sales-table-header-label">
                                        <?= $is_myanmar_action_language
                                            ? 'ပစ္စည်းပို့ဆောင်မှု<br>အခြေအနေ'
                                            : 'Delivery<br>Status'; ?>
                                    </span>
                                </th>
                                <th class="text-center sales-actions-heading">
                                    <span class="sales-table-header-label">
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

                <div class="sales-label-meaning">
                    <div class="sales-label-meaning-title">
                        <i class="fa fa-info-circle"></i>
                        <span><?= html_escape($sales_listing_labels['legend_title']); ?></span>
                    </div>

                    <div class="sales-label-meaning-groups">
                        <div class="sales-label-group">
                            <span class="sales-label-item">
                                <span class="sale_status label label-success"><?= html_escape($sales_listing_labels['paid_badge']); ?></span>
                                <span><?= html_escape($sales_listing_labels['paid_meaning']); ?></span>
                            </span>

                            <span class="sales-label-item">
                                <span class="sale_status label label-primary"><?= html_escape($sales_listing_labels['partial_badge']); ?></span>
                                <span><?= html_escape($sales_listing_labels['partial_meaning']); ?></span>
                            </span>

                            <span class="sales-label-item">
                                <span class="sale_status label label-danger"><?= html_escape($sales_listing_labels['due_badge']); ?></span>
                                <span><?= html_escape($sales_listing_labels['due_meaning']); ?></span>
                            </span>
                        </div>

                        <div class="sales-label-group">
                            <span class="sales-label-item">
                                <span class="erp-action-icon-btn erp-action-received-disabled"><i class="fa fa-check"></i></span>
                                <span><?= html_escape($sales_listing_labels['received']); ?></span>
                            </span>

                            <span class="sales-label-item">
                                <span class="erp-action-icon-btn erp-action-delivery-partial"><i class="fa fa-truck"></i></span>
                                <span><?= html_escape($sales_listing_labels['partial_delivery']); ?></span>
                            </span>

                            <span class="sales-label-item">
                                <span class="erp-action-icon-btn erp-action-receive"><i class="fa fa-truck"></i></span>
                                <span><?= html_escape($sales_listing_labels['receive']); ?></span>
                            </span>
                        </div>

                        <div class="sales-label-group">
                            <span class="sales-label-item">
                                <span class="erp-action-icon-btn erp-action-view"><i class="fa fa-eye"></i></span>
                                <span><?= html_escape($sales_listing_labels['view_details']); ?></span>
                            </span>

                            <span class="sales-label-item">
                                <span class="erp-action-icon-btn erp-action-payments"><i class="fa fa-credit-card"></i></span>
                                <span><?= html_escape($sales_listing_labels['view_payments']); ?></span>
                            </span>

                            <span class="sales-label-item">
                                <span class="erp-action-icon-btn erp-action-delete"><i class="fa fa-trash"></i></span>
                                <span><?= html_escape($sales_listing_labels['delete']); ?></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Due Payment Tab -->
            <div
                role="tabpanel"
                class="tab-pane <?= $active_sales_tab === 'due'
                    ? 'active'
                    : ''; ?>"
                id="customer-due-tab"
            >

                <div class="erp-customer-selector-panel">
                    <div class="erp-customer-selector-header">
                        <div class="erp-customer-selector-title-row">
                            <h4 class="erp-customer-selector-title">
                                <i class="fa fa-users"></i>
                                <?= lang('customer_due_payment'); ?>
                            </h4>

                            <button
                                type="button"
                                id="customerDueHelpToggle"
                                class="customer-due-help-toggle"
                                aria-expanded="false"
                                aria-controls="customerDueHelpContent"
                                title="<?= $is_myanmar_action_language
                                    ? 'အကူအညီကြည့်ရန်'
                                    : 'View help'; ?>"
                            >
                                <i class="fa fa-question-circle"></i>
                                <span class="sr-only">
                                    <?= $is_myanmar_action_language
                                        ? 'အကူအညီကြည့်ရန်'
                                        : 'View help'; ?>
                                </span>
                            </button>
                        </div>

                        <p
                            id="customerDueHelpContent"
                            class="erp-customer-selector-note customer-due-help-item"
                        >
                            <?= $is_myanmar_action_language
                                ? 'Customer ကို အရင်ရွေးချယ်ပါ။ ရွေးထားသော Customer ၏ အကြွေးဘောင်ချာများသာ ဖော်ပြပေးပါမည်။'
                                : 'Select a customer first. Only that customer’s outstanding sales will be shown.'; ?>
                        </p>
                    </div>

                    <div class="erp-customer-selector-control">
                        <label for="due_customer_filter">
                            <?= lang('customer'); ?>
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            id="due_customer_filter"
                            class="form-control select2 erp-select2"
                        >
                            <option value="">
                                <?= $is_myanmar_action_language
                                    ? 'Customer ရွေးချယ်ပါ'
                                    : 'Select customer'; ?>
                            </option>

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

                <div id="customerDueEmptyState" class="erp-customer-empty-state">
                    <div class="erp-customer-empty-icon">
                        <i class="fa fa-hand-pointer-o"></i>
                    </div>

                    <h4>
                        <?= $is_myanmar_action_language
                            ? 'Customer ရွေးချယ်ပါ'
                            : 'Select a customer'; ?>
                    </h4>

                    <p>
                        <?= $is_myanmar_action_language
                            ? 'အကြွေးစာရင်းကြည့်ရန်နှင့် ငွေလက်ခံရန် အပေါ်မှ Customer တစ်ဦးကို အရင်ရွေးချယ်ပေးပါ။'
                            : 'Select a customer above to view outstanding sales and receive payment.'; ?>
                    </p>
                </div>

                <div id="customerDueWorkspace" style="display:none;">
                    <div
                        id="dueSummaryGrid"
                        class="erp-summary-grid erp-summary-three"
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

                    <p class="erp-help-note customer-due-help-item">
                        <i class="fa fa-info-circle"></i>
                        <?= $is_myanmar_action_language
                            ? 'ငွေလက်ခံမည့် အရောင်းဘောင်ချာများကို ရွေးပြီး အောက်တွင် လက်ခံမည့်ပမာဏကို ထည့်ပါ။'
                            : 'Select the sales to pay, then enter the received amount below.'; ?>
                    </p>

                    <p class="erp-scroll-hint customer-due-help-item">
                        <i class="fa fa-arrows-h"></i>
                        <?= lang('swipe_table_horizontal'); ?>
                    </p>

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
                                    <th
                                        class="text-center due-checkbox-column"
                                        data-dt-order="disable"
                                        style="width:38px;"
                                    >
                                        <input type="checkbox" id="check_all_due">
                                    </th>
                                    <th><?= lang('id'); ?></th>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('customer'); ?></th>
                                    <th class="text-right"><?= lang('grand_total'); ?></th>
                                    <th class="text-right"><?= lang('paid'); ?></th>
                                    <th class="text-right"><?= lang('due'); ?></th>
                                    <th>
                                        <span class="sales-table-header-label">
                                            <?= $is_myanmar_action_language
                                                ? 'ငွေပေးချေမှု<br>အခြေအနေ'
                                                : 'Payment<br>Status'; ?>
                                        </span>
                                    </th>
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
                                    disabled
                                >
                                    <i class="fa fa-money"></i>
                                    <?= $is_myanmar_action_language
                                        ? 'ရွေးထားသည့်အကြွေးများ ငွေလက်ခံရန်'
                                        : 'Receive selected payments'; ?>
                                </button>
                            </div>
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

    <!-- Purchase receive flow ကဲ့သို့ item အလိုက် Sales delivery popup -->
    <div
        class="modal fade sales-delivery-modal"
        id="salesDeliveryModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="salesDeliveryTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog sales-delivery-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('close'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="salesDeliveryTitle">
                        <i class="fa fa-truck text-primary"></i>
                        <?= $is_myanmar_action_language ? 'ပစ္စည်းပို့ဆောင်ရန်' : 'Product Delivery'; ?>
                    </h4>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="salesDeliverySaleId" value="">

                    <div class="sales-delivery-meta">
                        <div class="sales-delivery-meta-item">
                            <span class="sales-delivery-meta-label"><?= $is_myanmar_action_language ? 'ဘောင်ချာ' : 'Invoice'; ?></span>
                            <span class="sales-delivery-meta-value" id="salesDeliveryVoucher">-</span>
                        </div>
                        <div class="sales-delivery-meta-item">
                            <span class="sales-delivery-meta-label"><?= $is_myanmar_action_language ? 'ဝယ်ယူသူ' : 'Customer'; ?></span>
                            <span class="sales-delivery-meta-value" id="salesDeliveryCustomer">-</span>
                        </div>
                        <div class="sales-delivery-meta-item">
                            <span class="sales-delivery-meta-label"><?= $is_myanmar_action_language ? 'ပို့ဆောင်သည့်နေရာ' : 'Store'; ?></span>
                            <span class="sales-delivery-meta-value" id="salesDeliveryStore">-</span>
                        </div>
                    </div>

                    <div class="sales-delivery-summary">
                        <div class="sales-delivery-summary-item">
                            <span class="sales-delivery-summary-label"><?= $is_myanmar_action_language ? 'ရောင်းထားသည့် စုစုပေါင်း' : 'Ordered total'; ?></span>
                            <span class="sales-delivery-summary-value" id="salesDeliveryOrderedTotal">0.00</span>
                        </div>
                        <div class="sales-delivery-summary-item is-delivered">
                            <span class="sales-delivery-summary-label"><?= $is_myanmar_action_language ? 'ပို့ဆောင်ပြီး' : 'Delivered'; ?></span>
                            <span class="sales-delivery-summary-value" id="salesDeliveryDeliveredTotal">0.00</span>
                        </div>
                        <div class="sales-delivery-summary-item is-remaining">
                            <span class="sales-delivery-summary-label"><?= $is_myanmar_action_language ? 'ပို့ဆောင်ရန်ကျန်' : 'Remaining'; ?></span>
                            <span class="sales-delivery-summary-value" id="salesDeliveryRemainingTotal">0.00</span>
                        </div>
                    </div>

                    <div class="sales-delivery-fields">
                        <div class="sales-delivery-field">
                            <label for="salesDeliveredAt">
                                <?= $is_myanmar_action_language ? 'ပို့ဆောင်သည့်ရက်စွဲ' : 'Delivery date'; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" id="salesDeliveredAt" class="form-control">
                        </div>
                        <div class="sales-delivery-field">
                            <label for="salesDeliveryNote"><?= $is_myanmar_action_language ? 'မှတ်ချက်' : 'Note'; ?></label>
                            <input type="text" id="salesDeliveryNote" class="form-control" maxlength="500" autocomplete="off">
                        </div>
                    </div>

                    <div class="sales-delivery-table-card">
                        <div class="sales-delivery-fill-row" id="salesDeliveryFillRow">
                            <button type="button" class="sales-delivery-fill-all" id="fillAllSalesDeliveryRemaining">
                                <i class="fa fa-check-circle"></i>
                                <span><?= $is_myanmar_action_language ? 'ကျန်အားလုံး ဖြည့်ရန်' : 'Fill all remaining'; ?></span>
                            </button>
                        </div>

                        <div class="sales-delivery-table-wrap">
                            <table class="table sales-delivery-table">
                                <colgroup>
                                    <col style="width:28%;"><col style="width:22%;"><col style="width:16%;"><col style="width:17%;"><col style="width:17%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th><?= $is_myanmar_action_language ? 'ပစ္စည်း' : 'Product'; ?></th>
                                        <th><?= $is_myanmar_action_language ? 'ယခုပို့မည်' : 'Deliver now'; ?></th>
                                        <th><?= $is_myanmar_action_language ? 'ရောင်းထား' : 'Ordered'; ?></th>
                                        <th><?= $is_myanmar_action_language ? 'ပို့ပြီး' : 'Delivered'; ?></th>
                                        <th><?= $is_myanmar_action_language ? 'ကျန်' : 'Remaining'; ?></th>
                                    </tr>
                                </thead>
                                <tbody id="salesDeliveryItemsBody">
                                    <tr><td colspan="5" class="sales-delivery-loading"><i class="fa fa-spinner fa-spin"></i></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="salesDeliveryFormError" class="alert alert-danger" role="alert" style="display:none; margin:9px 0 0;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> <?= $is_myanmar_action_language ? 'မလုပ်တော့ပါ' : 'Cancel'; ?>
                    </button>
                    <button type="button" class="btn btn-success" id="saveSalesDeliveryBtn">
                        <i class="fa fa-truck"></i> <?= $is_myanmar_action_language ? 'ပစ္စည်းပို့ဆောင်မှု သိမ်းရန်' : 'Save delivery'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Item delivery history popup -->
    <div
        class="modal fade sales-delivery-modal"
        id="salesDeliveryHistoryModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="salesDeliveryHistoryTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog sales-delivery-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= lang('close'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="salesDeliveryHistoryTitle">
                        <i class="fa fa-history text-primary"></i>
                        <?= $is_myanmar_action_language ? 'ပို့ဆောင်မှုမှတ်တမ်း' : 'Delivery History'; ?>
                        <small id="salesDeliveryHistoryProduct"></small>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="sales-delivery-table-card">
                        <div class="sales-delivery-table-wrap">
                            <table class="table sales-delivery-table">
                                <thead>
                                    <tr>
                                        <th><?= $is_myanmar_action_language ? 'ရက်စွဲ' : 'Date'; ?></th>
                                        <th><?= $is_myanmar_action_language ? 'အရေအတွက်' : 'Quantity'; ?></th>
                                        <th><?= $is_myanmar_action_language ? 'မှတ်တမ်းတင်သူ' : 'User'; ?></th>
                                        <th><?= $is_myanmar_action_language ? 'မှတ်ချက်' : 'Note'; ?></th>
                                        <th><?= $is_myanmar_action_language ? 'လုပ်ဆောင်မှု' : 'Actions'; ?></th>
                                    </tr>
                                </thead>
                                <tbody id="salesDeliveryHistoryBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> <?= lang('close'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- URL မပါသော edit/delete action dialog -->
    <div
        class="sales-delivery-action-dialog"
        id="salesDeliveryActionDialog"
        role="dialog"
        aria-modal="true"
        aria-hidden="true"
        aria-labelledby="salesDeliveryActionTitle"
    >
        <button type="button" class="sales-delivery-action-backdrop" data-sales-delivery-action-close aria-label="<?= lang('close'); ?>"></button>
        <div class="sales-delivery-action-panel">
            <div class="sales-delivery-action-head" id="salesDeliveryActionTitle"></div>
            <div class="sales-delivery-action-body">
                <p id="salesDeliveryActionMessage"></p>
                <div class="sales-delivery-field" id="salesDeliveryActionField">
                    <label for="salesDeliveryActionQuantity"><?= $is_myanmar_action_language ? 'ပို့ဆောင်သည့်အရေအတွက်အသစ်' : 'New delivered quantity'; ?></label>
                    <input type="text" inputmode="decimal" class="form-control" id="salesDeliveryActionQuantity" autocomplete="off">
                </div>
                <div class="sales-delivery-action-error" id="salesDeliveryActionError"></div>
            </div>
            <div class="sales-delivery-action-foot">
                <button type="button" class="btn btn-default" data-sales-delivery-action-close><?= $is_myanmar_action_language ? 'မလုပ်တော့ပါ' : 'Cancel'; ?></button>
                <button type="button" class="btn btn-primary" id="confirmSalesDeliveryAction"></button>
            </div>
        </div>
    </div>

    <!-- Browser URL မပါတဲ့ Sales Delete Confirmation Modal -->
    <div
        class="modal fade"
        id="salesDeleteConfirmModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="salesDeleteConfirmTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="<?= lang('close'); ?>"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>

                    <h4 class="modal-title" id="salesDeleteConfirmTitle">
                        <i class="fa fa-exclamation-triangle text-danger"></i>
                        <?= $is_myanmar_action_language
                            ? 'အရောင်းဘောင်ချာ ဖျက်ရန်အတည်ပြုခြင်း'
                            : 'Confirm Sale Delete'; ?>
                    </h4>
                </div>

                <div class="modal-body">
                    <p style="margin:0; font-size:16px; line-height:1.7;">
                        <?= $is_myanmar_action_language
                            ? 'ငွေလက်ခံမှုနှင့် Sales Return မရှိမှသာ Stock ကိုပြန်လှန်ပြီး ဖျက်ပေးပါမည်။'
                            : 'The sale can be deleted only when it has no payments or sales returns. Stock will be reversed automatically.'; ?>
                    </p>

                    <div class="form-group" style="margin:16px 0 0;">
                        <label for="salesDeleteReason" style="font-weight:800;">
                            <?= $is_myanmar_action_language
                                ? 'ဖျက်ရသည့်အကြောင်းပြချက်'
                                : 'Deletion reason'; ?>
                            <span class="text-danger">*</span>
                        </label>

                        <textarea
                            id="salesDeleteReason"
                            class="form-control"
                            rows="3"
                            maxlength="500"
                            autocomplete="off"
                            placeholder="<?= $is_myanmar_action_language
                                ? 'ဥပမာ - ဘောင်ချာမှားရိုက်မိသောကြောင့်'
                                : 'Example: Invoice entered by mistake'; ?>"
                        ></textarea>
                    </div>

                    <div
                        id="salesDeleteConfirmError"
                        class="alert alert-danger"
                        role="alert"
                        style="display:none; margin:14px 0 0;"
                    ></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i>
                        <?= $is_myanmar_action_language ? 'မဖျက်တော့ပါ' : 'Cancel'; ?>
                    </button>

                    <button type="button" class="btn btn-danger" id="confirmSalesDeleteBtn">
                        <i class="fa fa-trash"></i>
                        <?= $is_myanmar_action_language ? 'ဖျက်မည်' : 'Delete'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>