<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;
?>

<?php

// Monday of current week
$start_date = date(
    'Y-m-d 00:00:00',
    strtotime('monday this week')
);

// Saturday of current week
$end_date = date(
    'Y-m-d 23:59:59',
    strtotime('saturday this week')
);

$pl_labels = [
    'summary'             => lang('summary'),
    'filter'              => lang('filter'),
    'report'              => lang('report'),
    'print'               => lang('print'),

    'create_pdf'          => lang('create_pdf'),
    'generating_pdf'      => lang('generating_pdf'),
    'uploading_pdf'       => lang('uploading_pdf'),
    'pdf_ready'           => lang('pdf_ready'),
    'pdf_saved_server'    => lang('pdf_saved_server'),
    'open_pdf'            => lang('open_pdf'),
    'download_pdf'        => lang('download_pdf'),
    'share_pdf'           => lang('share_pdf'),
    'copy_link'           => lang('copy_link'),
    'link_copied'         => lang('link_copied'),
    'share_link_note'     => lang('share_link_note'),
    'pdf_failed'          => lang('pdf_failed'),
    'server_save_failed'  => lang('server_save_failed'),

    'amount_mmk'          => lang('amount_mmk'),
    'particulars'         => lang('particulars'),
    'sales_revenue'       => lang('sales_revenue'),
    'discounts_returns'   => lang('discounts_returns'),
    'net_sales'           => lang('net_sales'),
    'cogs'                => lang('cogs'),
    'gross_profit'        => lang('gross_profit'),
    'operating_expenses'  => lang('operating_expenses'),
    'depreciation'        => lang('depreciation'),
    'total_expenses'      => lang('total_expenses'),
    'net_profit'          => lang('net_profit'),

    'period'              => lang('period'),
    'loading'             => lang('loading'),
    'error_loading_data'  => lang('error_loading_data'),

    'revenue_section'     => lang('revenue_section'),
    'cost_profit_section' => lang('cost_profit_section'),
    'expenses_section'    => lang('expenses_section'),
];
?>


<?php if ($is_app_mode) { ?>
<style>
/* KLSPOS Profit & Loss - App/Mobile Shell */
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

.content.erp-pl-page {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.erp-pl-page > .row {
    width: 100% !important;
    margin: 0 !important;
}

.erp-pl-page > .row > .col-sm-12 {
    width: 100% !important;
    padding: 0 !important;
    float: none !important;
}

.erp-pl-page .box.box-primary {
    width: 100% !important;
    margin: 0 !important;
    overflow: hidden !important;
    border: 1px solid #e3e9ef !important;
    border-top: 1px solid #e3e9ef !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    box-shadow: none !important;
}

.erp-pl-page .erp-header {
    display: block !important;
    padding: 13px !important;
}

.erp-pl-page .erp-title-wrap {
    width: 100% !important;
}

.erp-pl-page .erp-title {
    font-size: 17px !important;
}

.erp-pl-page .erp-subtitle {
    font-size: 13px !important;
}

.erp-pl-page .erp-header-actions {
    display: grid !important;
    grid-template-columns: 1fr !important;
    width: 100% !important;
    margin-top: 11px !important;
}

.erp-pl-page .toggle_form {
    width: 100% !important;
    min-height: 42px !important;
    margin: 0 !important;
    font-size: 14px !important;
}

.erp-pl-page .desktop-print-action {
    display: none !important;
}

.erp-pl-page .mobile-pdf-actions {
    display: flex !important;
    width: 100% !important;
}

.erp-pl-page .mobile-pdf-actions .btn {
    width: 100% !important;
    min-height: 42px !important;
    font-size: 14px !important;
}

.erp-pl-page .erp-body {
    padding: 10px !important;
}

.erp-pl-page .erp-stat-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
}

.erp-pl-page .erp-stat-item {
    width: auto !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}

.erp-pl-page .erp-stat-card {
    width: 100% !important;
    min-height: 88px !important;
    margin: 0 !important;
    padding: 12px !important;
    border-radius: 10px !important;
}

.erp-pl-page .erp-stat-label {
    font-size: 13px !important;
    line-height: 1.4 !important;
    text-transform: none !important;
}

.erp-pl-page .erp-stat-value {
    margin-top: 5px !important;
    font-size: 18px !important;
    line-height: 1.35 !important;
}

.erp-pl-page .erp-stat-icon {
    width: 38px !important;
    height: 38px !important;
    flex: 0 0 38px !important;
}

.erp-pl-page .erp-filter-card {
    padding: 12px !important;
}

.erp-pl-page .erp-filter-card .row {
    margin-right: 0 !important;
    margin-left: 0 !important;
}

.erp-pl-page .erp-filter-card .col-sm-4 {
    width: 100% !important;
    padding-right: 0 !important;
    padding-left: 0 !important;
    float: none !important;
}

.erp-pl-page .erp-filter-card label {
    font-size: 13px !important;
}

.erp-pl-page .erp-filter-card .form-control,
.erp-pl-page .erp-filter-card .btn {
    min-height: 42px !important;
    font-size: 14px !important;
}

.erp-pl-page .erp-report-card {
    padding: 10px !important;
}

.erp-pl-page .erp-report-toolbar {
    display: block !important;
}

.erp-pl-page .erp-report-toolbar .erp-report-title {
    margin-bottom: 9px !important;
    font-size: 15px !important;
}

.erp-pl-page .erp-period-badge {
    max-width: 100% !important;
    font-size: 12px !important;
    overflow-wrap: anywhere !important;
}

.erp-pl-page .table-responsive {
    width: 100% !important;
    margin: 0 !important;
    overflow-x: auto !important;
    border: 0 !important;
    -webkit-overflow-scrolling: touch;
}

.erp-pl-page .erp-pl-table {
    width: 100% !important;
    min-width: 560px !important;
}

.erp-pl-page .erp-pl-table thead th,
.erp-pl-page .erp-pl-table tbody td {
    font-size: 13px !important;
}

.erp-pl-page .pdf-result-actions {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
}

.erp-pl-page .pdf-result-actions .btn {
    width: 100% !important;
    margin: 0 !important;
}

@media (max-width: 420px) {
    .erp-pl-page .erp-stat-grid {
        grid-template-columns: 1fr !important;
    }

    .erp-pl-page .erp-stat-card {
        min-height: 82px !important;
    }

    .erp-pl-page .pdf-result-actions {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>

<style>
/* =========================================================
   KLSPOS - Profit & Loss Report | Mobile ERP
   ========================================================= */

.erp-pl-page {
    font-family:
        "Noto Sans Myanmar",
        "Pyidaungsu",
        "Myanmar Text",
        "Noto Sans",
        Arial,
        sans-serif;
}

/* Header */
.erp-pl-page .erp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 14px 16px;
    border-bottom: 1px solid #edf1f5;
    background: #fff;
}

.erp-pl-page .erp-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.erp-pl-page .erp-title-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    border-radius: 12px;
    background: #eef5ff;
    color: #2f80ed;
    font-size: 18px;
}

.erp-pl-page .erp-title {
    margin: 0;
    color: #273444;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.35;
}

.erp-pl-page .erp-subtitle {
    margin-top: 3px;
    color: #7b8794;
    font-size: 12px;
}

.erp-pl-page .erp-header-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
    flex-wrap: wrap;
}

/* Filter button */
.erp-pl-page .toggle_form {
    border-color: #d7e0e8;
    background: #fff;
    color: #425466;
    font-weight: 700;
}

.erp-pl-page .toggle_form:hover,
.erp-pl-page .toggle_form:focus {
    border-color: #b8d6db;
    background: #f3fafb;
    color: #276b78;
}

.erp-pl-page .toggle_form.active {
    border-color: #b8d6db;
    background: #e8f6f7;
    color: #276b78;
}

/* Mobile PDF */
.erp-pl-page .mobile-pdf-actions {
    display: none;
    gap: 7px;
}

.erp-pl-page .pdf-action-loading {
    pointer-events: none;
    opacity: .7;
}

.erp-pl-page .pdf-status-message {
    display: none;
    width: 100%;
    margin-top: 8px;
    padding: 9px 11px;
    border-radius: 7px;
    background: #ecfdf3;
    color: #067647;
    font-size: 12px;
    font-weight: 700;
}

.erp-pl-page .pdf-status-message.is-error {
    background: #fff1f2;
    color: #b42318;
}

.erp-pl-page .pdf-status-message.is-visible {
    display: block;
}

.erp-pl-page .pdf-result-card {
    display: none;
    margin: 0 0 15px;
    padding: 14px;
    border: 1px solid #b7ebc6;
    border-radius: 10px;
    background: #f0fdf4;
    box-shadow: 0 2px 10px rgba(6, 118, 71, .06);
}

.erp-pl-page .pdf-result-card.is-visible {
    display: block;
}

.erp-pl-page .pdf-result-head {
    display: flex;
    align-items: flex-start;
    gap: 11px;
}

.erp-pl-page .pdf-result-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    border-radius: 11px;
    background: #dcfce7;
    color: #067647;
    font-size: 19px;
}

.erp-pl-page .pdf-result-title {
    margin: 0;
    color: #166534;
    font-size: 15px;
    font-weight: 900;
}

.erp-pl-page .pdf-result-text {
    margin-top: 3px;
    color: #3f6212;
    font-size: 12px;
}

.erp-pl-page .pdf-file-name {
    margin-top: 8px;
    padding: 8px 10px;
    overflow-wrap: anywhere;
    border: 1px dashed #86efac;
    border-radius: 7px;
    background: #fff;
    color: #344054;
    font-size: 12px;
    font-weight: 800;
}

.erp-pl-page .pdf-result-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-top: 11px;
}

.erp-pl-page .pdf-share-note {
    margin-top: 8px;
    color: #64748b;
    font-size: 11px;
}

/* Body */
.erp-pl-page .erp-body {
    padding: 15px;
    background: #f6f8fb;
}

.erp-pl-page .erp-filter-card,
.erp-pl-page .erp-summary-card,
.erp-pl-page .erp-report-card {
    margin-bottom: 15px;
    border: 1px solid #edf1f5;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(36, 52, 71, .05);
}

/* Filter is closed by default */
.erp-pl-page #form {
    display: none;
}

.erp-pl-page .erp-filter-card {
    padding: 15px;
}

.erp-pl-page .erp-filter-title,
.erp-pl-page .erp-report-title {
    margin: 0 0 12px;
    color: #273444;
    font-size: 15px;
    font-weight: 800;
}

.erp-pl-page .erp-filter-card label {
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.erp-pl-page .erp-filter-card .form-control {
    height: 40px;
    border-color: #dfe6ee;
    border-radius: 6px;
    box-shadow: none;
}

.erp-pl-page .erp-filter-card .form-control:focus {
    border-color: #2f80ed;
    box-shadow: 0 0 0 3px rgba(47, 128, 237, .10);
}

/* Fix datetime picker visibility */
.bootstrap-datetimepicker-widget {
    z-index: 999999 !important;
}

/* Summary cards */
.erp-pl-page .erp-stat-grid {
    display: flex;
    flex-wrap: wrap;
    margin-right: -7px;
    margin-left: -7px;
}

.erp-pl-page .erp-stat-item {
    margin-bottom: 14px;
    padding-right: 7px;
    padding-left: 7px;
}

.erp-pl-page .erp-stat-card {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 88px;
    padding: 14px 15px;
    overflow: hidden;
}

.erp-pl-page .erp-stat-card:after {
    position: absolute;
    right: -25px;
    bottom: -28px;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: rgba(47, 128, 237, .08);
    content: "";
}

.erp-pl-page .erp-stat-label {
    color: #7b8794;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.erp-pl-page .erp-stat-value {
    margin-top: 6px;
    color: #273444;
    font-size: 19px;
    font-weight: 800;
    word-break: break-word;
}

.erp-pl-page .erp-stat-icon {
    z-index: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    border-radius: 12px;
    background: #f2f6fb;
    color: #2f80ed;
    font-size: 18px;
}

/* Colorful summary cards */
.erp-pl-page .erp-stat-item:nth-child(1) .erp-stat-card {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-color: #2f6fd8;
}

.erp-pl-page .erp-stat-item:nth-child(2) .erp-stat-card {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: #0d9f74;
}

.erp-pl-page .erp-stat-item:nth-child(3) .erp-stat-card {
    background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
    border-color: #eb7a08;
}

.erp-pl-page .erp-stat-item:nth-child(4) .erp-stat-card {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    border-color: #7a46f0;
}

.erp-pl-page .erp-stat-item .erp-stat-card:after {
    background: rgba(255, 255, 255, .12);
}

.erp-pl-page .erp-stat-item .erp-stat-label {
    color: rgba(255, 255, 255, .85);
}

.erp-pl-page .erp-stat-item .erp-stat-value {
    color: #ffffff;
}

.erp-pl-page .erp-stat-item .erp-stat-icon {
    background: rgba(255, 255, 255, .18);
    color: #ffffff;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12);
}

/* keep red/green state on net profit text if needed */
.erp-pl-page #pl_net_profit.text-danger {
    color: #fff5f5 !important;
}

.erp-pl-page #pl_net_profit.text-success {
    color: #ffffff !important;
}


/* Report */
.erp-pl-page .erp-report-card {
    padding: 12px;
}

.erp-pl-page .erp-report-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.erp-pl-page .erp-report-toolbar .erp-report-title {
    margin-bottom: 0;
}

.erp-pl-page .erp-period-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
}

.erp-pl-page .erp-pl-table {
    margin-bottom: 0;
}

.erp-pl-page .erp-pl-table thead th {
    padding: 10px 9px !important;
    border-bottom: 1px solid #dfe6ee !important;
    background: #f1f5f9 !important;
    color: #334155;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    vertical-align: middle !important;
    white-space: nowrap;
}

.erp-pl-page .erp-pl-table tbody td {
    padding: 10px 9px !important;
    color: #344054;
    vertical-align: middle !important;
}

.erp-pl-page .erp-pl-table .amount-col {
    text-align: right;
    white-space: nowrap;
    font-weight: 700;
}

.erp-pl-page .erp-pl-table .section-row td {
    background: #fbfcfe;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
}

.erp-pl-page .erp-pl-table .subtotal-row td {
    background: #eef6ff;
    font-weight: 800;
}

.erp-pl-page .erp-pl-table .expense-row .amount-col {
    color: #b45309;
}

.erp-pl-page .erp-pl-table .profit-row td {
    background: #ecfdf3;
    font-size: 15px;
    font-weight: 900;
}

.erp-pl-page .erp-pl-table .loss-row td {
    background: #fff1f2;
    color: #b42318;
    font-size: 15px;
    font-weight: 900;
}

.erp-pl-page .erp-loading,
.erp-pl-page .erp-error {
    padding: 25px;
    border: 1px dashed #dfe6ee;
    border-radius: 10px;
    background: #fbfcfe;
    color: #64748b;
    text-align: center;
}

.erp-pl-page .erp-error {
    border-color: #fecdd3;
    background: #fff1f2;
    color: #b42318;
}

/* Mobile */
@media (max-width: 767px) {
    .erp-pl-page {
        padding-right: 7px !important;
        padding-left: 7px !important;
    }

    .erp-pl-page .erp-header,
    .erp-pl-page .erp-report-toolbar {
        display: block;
    }

    .erp-pl-page .erp-header {
        padding: 13px;
    }

    .erp-pl-page .erp-title {
        font-size: 16px;
    }

    .erp-pl-page .erp-header-actions {
        justify-content: flex-start;
        width: 100%;
        margin-top: 10px;
    }

    .erp-pl-page .desktop-print-action {
        display: none !important;
    }

    .erp-pl-page .mobile-pdf-actions {
        display: flex;
        width: 100%;
    }

    .erp-pl-page .mobile-pdf-actions .btn {
        width: 100%;
        min-width: 0;
        flex: 1 1 100%;
    }

    .erp-pl-page .pdf-result-actions .btn {
        flex: 1 1 calc(50% - 7px);
    }

    .erp-pl-page .toggle_form {
        width: 100%;
        margin-bottom: 1px;
    }

    .erp-pl-page .erp-body {
        padding: 10px;
    }

    .erp-pl-page .erp-filter-card {
        padding: 12px;
    }

    .erp-pl-page .erp-stat-value {
        font-size: 17px;
    }

    .erp-pl-page .erp-report-toolbar .erp-report-title {
        margin-bottom: 9px;
    }

    .erp-pl-page .table-responsive {
        border: 0;
    }

    .erp-pl-page .erp-pl-table {
        min-width: 590px;
    }
}
</style>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content erp-pl-page kls-mobile-ui">
    <div class="row">
        <div class="col-sm-12">

            <div class="box box-primary">

                <!-- Page header -->
                <div class="erp-header">

                    <div class="erp-title-wrap">
                        <span class="erp-title-icon">
                            <i class="fa fa-line-chart"></i>
                        </span>

                        <div>
                            <h4 class="erp-title">
                                <?= html_escape($page_title); ?>
                            </h4>

                            <div class="erp-subtitle">
                                <?= html_escape($pl_labels['report']); ?>
                                /
                                <?= html_escape($pl_labels['summary']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="erp-header-actions">

                        <a
                            href="#"
                            class="btn btn-default btn-sm toggle_form"
                            aria-expanded="false"
                            aria-controls="form"
                        >
                            <i class="fa fa-filter"></i>
                            <?= lang('show_hide'); ?>
                        </a>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm desktop-print-action"
                            id="print_profit_loss"
                        >
                            <i class="fa fa-print"></i>
                            <?= html_escape($pl_labels['print']); ?>
                        </button>

                        <div
                            class="mobile-pdf-actions"
                            id="mobile_pdf_actions"
                        >
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                id="generate_profit_loss_pdf"
                            >
                                <i class="fa fa-file-pdf-o"></i>
                                <?= html_escape($pl_labels['create_pdf']); ?>
                            </button>
                        </div>

                        <div
                            class="pdf-status-message"
                            id="pdf_status_message"
                            aria-live="polite"
                        ></div>

                    </div>
                </div>

                <div class="box-body erp-body">

                    <!-- Filter -->
                    <div
                        id="form"
                        class="erp-filter-card"
                        aria-hidden="true"
                    >
                        <h4 class="erp-filter-title">
                            <i class="fa fa-sliders"></i>
                            <?= html_escape($pl_labels['filter']); ?>
                        </h4>

                        <?= form_open(
                            'reports/profit_loss',
                            'id="profit_loss_form"'
                        ); ?>

                        <div class="row">

                            <div class="col-sm-4">
                                <div class="form-group">

                                    <label for="start_date">
                                        <?= lang('start_date'); ?>
                                    </label>

                                    <?= form_input(
                                        'start_date',
                                        set_value(
                                            'start_date',
                                            $start_date
                                        ),
                                        'class="form-control datetimepicker"
                                         id="start_date"
                                         autocomplete="off"'
                                    ); ?>

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">

                                    <label for="end_date">
                                        <?= lang('end_date'); ?>
                                    </label>

                                    <?= form_input(
                                        'end_date',
                                        set_value(
                                            'end_date',
                                            $end_date
                                        ),
                                        'class="form-control datetimepicker"
                                         id="end_date"
                                         autocomplete="off"'
                                    ); ?>

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">

                                    <label>&nbsp;</label>

                                    <button
                                        type="submit"
                                        class="btn btn-primary btn-block"
                                    >
                                        <i class="fa fa-search"></i>
                                        <?= lang('submit'); ?>
                                    </button>

                                </div>
                            </div>

                        </div>

                        <?= form_close(); ?>
                    </div>

                    <!-- Summary cards -->
                    <div class="erp-stat-grid">

                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">

                                <div>
                                    <div class="erp-stat-label">
                                        <?= html_escape($pl_labels['net_sales']); ?>
                                    </div>

                                    <div
                                        class="erp-stat-value"
                                        id="pl_net_sales"
                                    >
                                        0.00
                                    </div>
                                </div>

                                <span class="erp-stat-icon">
                                    <i class="fa fa-shopping-cart"></i>
                                </span>

                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">

                                <div>
                                    <div class="erp-stat-label">
                                        <?= html_escape($pl_labels['gross_profit']); ?>
                                    </div>

                                    <div
                                        class="erp-stat-value"
                                        id="pl_gross_profit"
                                    >
                                        0.00
                                    </div>
                                </div>

                                <span class="erp-stat-icon">
                                    <i class="fa fa-bar-chart"></i>
                                </span>

                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">

                                <div>
                                    <div class="erp-stat-label">
                                        <?= html_escape($pl_labels['total_expenses']); ?>
                                    </div>

                                    <div
                                        class="erp-stat-value"
                                        id="pl_total_expenses"
                                    >
                                        0.00
                                    </div>
                                </div>

                                <span class="erp-stat-icon">
                                    <i class="fa fa-credit-card"></i>
                                </span>

                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">

                                <div>
                                    <div class="erp-stat-label">
                                        <?= html_escape($pl_labels['net_profit']); ?>
                                    </div>

                                    <div
                                        class="erp-stat-value"
                                        id="pl_net_profit"
                                    >
                                        0.00
                                    </div>
                                </div>

                                <span class="erp-stat-icon">
                                    <i class="fa fa-money"></i>
                                </span>

                            </div>
                        </div>

                    </div>

                    <!-- Generated PDF result -->
                    <div
                        class="pdf-result-card"
                        id="profit_loss_pdf_result"
                    >
                        <div class="pdf-result-head">

                            <span class="pdf-result-icon">
                                <i class="fa fa-check"></i>
                            </span>

                            <div>
                                <h4 class="pdf-result-title">
                                    <?= html_escape($pl_labels['pdf_ready']); ?>
                                </h4>

                                <div class="pdf-result-text">
                                    <?= html_escape($pl_labels['pdf_saved_server']); ?>
                                </div>
                            </div>

                        </div>

                        <div class="pdf-file-name">
                            <i class="fa fa-file-pdf-o"></i>
                            <span id="profit_loss_pdf_filename"></span>
                        </div>

                        <div class="pdf-result-actions">

                            <a
                                href="#"
                                class="btn btn-success btn-sm"
                                id="profit_loss_pdf_open"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="fa fa-eye"></i>
                                <?= html_escape($pl_labels['open_pdf']); ?>
                            </a>

                            <a
                                href="#"
                                class="btn btn-primary btn-sm"
                                id="profit_loss_pdf_download"
                            >
                                <i class="fa fa-download"></i>
                                <?= html_escape($pl_labels['download_pdf']); ?>
                            </a>

                            <button
                                type="button"
                                class="btn btn-default btn-sm"
                                id="profit_loss_pdf_share"
                            >
                                <i class="fa fa-share-alt"></i>
                                <?= html_escape($pl_labels['share_pdf']); ?>
                            </button>

                        </div>

                        <div class="pdf-share-note">
                            <i class="fa fa-clock-o"></i>
                            <?= html_escape($pl_labels['share_link_note']); ?>
                        </div>
                    </div>

                    <!-- Report -->
                    <div
                        class="erp-report-card"
                        id="profit_loss_print_area"
                    >
                        <div class="erp-report-toolbar">

                            <h4 class="erp-report-title">
                                <i class="fa fa-file-text-o"></i>
                                <?= html_escape($page_title); ?>
                            </h4>

                            <span class="erp-period-badge">
                                <i class="fa fa-calendar"></i>

                                <span id="pl_period">
                                    <?= html_escape($pl_labels['period']); ?>
                                </span>
                            </span>

                        </div>

                        <div id="report-content">
                            <div class="erp-loading">
                                <i class="fa fa-spinner fa-spin"></i>
                                <?= html_escape($pl_labels['loading']); ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<script
    src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js"
    type="text/javascript"
></script>

<script
    src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"
    type="text/javascript"
></script>

<script type="text/javascript">
$(function () {

    var plLabels = <?= json_encode(
        $pl_labels,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;

    var pageTitle = <?= json_encode(
        $page_title,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;

    var csrfName = <?= json_encode(
        $this->security->get_csrf_token_name()
    ); ?>;

    var csrfHash = <?= json_encode(
        $this->security->get_csrf_hash()
    ); ?>;

    var pdfStatusTimer = null;
    var currentPdf = null;

    function erpPF(value) {
        if (typeof pf === 'function') {
            return pf(value);
        }

        value = (
            value === null ||
            value === undefined ||
            value === ''
        ) ? 0 : value;

        return parseFloat(
            String(value)
                .replace(/<[^>]*>/g, '')
                .replace(/,/g, '')
        ) || 0;
    }

    function erpCF(value) {
        var number = erpPF(value);

        /*
         * KLSPOS cf() may return formatted HTML such as:
         * <div class="text-right">4,059,000.00</div>
         *
         * This report needs plain amount text, so strip the wrapper HTML
         * before inserting the value into summary cards and table cells.
         */
        if (typeof cf === 'function') {
            var formatted = cf(number);

            var plainText = $('<div>')
                .html(
                    formatted === null ||
                    formatted === undefined
                        ? ''
                        : String(formatted)
                )
                .text()
                .trim();

            if (plainText !== '') {
                return plainText;
            }
        }

        return Number(number).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getAmount(data, key) {
        return erpPF(
            data && data[key] !== undefined
                ? data[key]
                : 0
        );
    }

    function safeText(text) {
        return $('<div/>')
            .text(text === null || text === undefined ? '' : text)
            .html();
    }

    function amountCell(value) {
        return (
            '<td class="amount-col">' +
                safeText(erpCF(value)) +
            '</td>'
        );
    }

    function updateCsrfHash(response) {
        if (response && response.csrf_hash) {
            csrfHash = response.csrf_hash;
        }
    }

    function updateSummary(data) {
        var netProfit = getAmount(
            data,
            'net_profit'
        );

        $('#pl_net_sales').html(
            safeText(
                erpCF(
                    getAmount(data, 'net_sales')
                )
            )
        );

        $('#pl_gross_profit').html(
            safeText(
                erpCF(
                    getAmount(data, 'gross_profit')
                )
            )
        );

        $('#pl_total_expenses').html(
            safeText(
                erpCF(
                    getAmount(data, 'total_expenses')
                )
            )
        );

        $('#pl_net_profit')
            .html(
                safeText(
                    erpCF(netProfit)
                )
            )
            .toggleClass(
                'text-danger',
                netProfit < 0
            )
            .toggleClass(
                'text-success',
                netProfit >= 0
            );

        $('#pl_period').html(
            safeText(
                $('#start_date').val() +
                ' - ' +
                $('#end_date').val()
            )
        );
    }

    function renderProfitLoss(data) {
        updateSummary(data);

        var netProfit = getAmount(
            data,
            'net_profit'
        );

        var profitRowClass =
            netProfit < 0
                ? 'loss-row'
                : 'profit-row';

        var html = '';

        html += '<div class="table-responsive">';
        html += '<table class="table table-bordered table-striped erp-pl-table">';

        html += '<thead>';
        html += '<tr>';

        html +=
            '<th style="width:70%;">' +
                safeText(plLabels.particulars) +
            '</th>';

        html +=
            '<th style="width:30%; text-align:right;">' +
                safeText(plLabels.amount_mmk) +
            '</th>';

        html += '</tr>';
        html += '</thead>';

        html += '<tbody>';

        /* Revenue */
        html +=
            '<tr class="section-row">' +
                '<td colspan="2">' +
                    safeText(plLabels.revenue_section) +
                '</td>' +
            '</tr>';

        html +=
            '<tr>' +
                '<td>' +
                    safeText(plLabels.sales_revenue) +
                '</td>' +
                amountCell(
                    getAmount(data, 'sales')
                ) +
            '</tr>';

        html +=
            '<tr>' +
                '<td>' +
                    safeText(plLabels.discounts_returns) +
                '</td>' +
                amountCell(
                    getAmount(data, 'discounts')
                ) +
            '</tr>';

        html +=
            '<tr class="subtotal-row">' +
                '<td>' +
                    safeText(plLabels.net_sales) +
                '</td>' +
                amountCell(
                    getAmount(data, 'net_sales')
                ) +
            '</tr>';

        /* Cost / Profit */
        html +=
            '<tr class="section-row">' +
                '<td colspan="2">' +
                    safeText(plLabels.cost_profit_section) +
                '</td>' +
            '</tr>';

        html +=
            '<tr class="expense-row">' +
                '<td>' +
                    safeText(plLabels.cogs) +
                '</td>' +
                amountCell(
                    getAmount(data, 'cogs')
                ) +
            '</tr>';

        html +=
            '<tr class="subtotal-row">' +
                '<td>' +
                    safeText(plLabels.gross_profit) +
                '</td>' +
                amountCell(
                    getAmount(data, 'gross_profit')
                ) +
            '</tr>';

        /* Expenses */
        html +=
            '<tr class="section-row">' +
                '<td colspan="2">' +
                    safeText(plLabels.expenses_section) +
                '</td>' +
            '</tr>';

        html +=
            '<tr class="expense-row">' +
                '<td>' +
                    safeText(plLabels.operating_expenses) +
                '</td>' +
                amountCell(
                    getAmount(data, 'operating_expenses')
                ) +
            '</tr>';

        html +=
            '<tr class="expense-row">' +
                '<td>' +
                    safeText(plLabels.depreciation) +
                '</td>' +
                amountCell(
                    getAmount(data, 'depreciation')
                ) +
            '</tr>';

        html +=
            '<tr class="subtotal-row">' +
                '<td>' +
                    safeText(plLabels.total_expenses) +
                '</td>' +
                amountCell(
                    getAmount(data, 'total_expenses')
                ) +
            '</tr>';

        /* Net profit */
        html +=
            '<tr class="' +
                profitRowClass +
            '">' +
                '<td>' +
                    safeText(plLabels.net_profit) +
                '</td>' +
                amountCell(netProfit) +
            '</tr>';

        html += '</tbody>';
        html += '</table>';
        html += '</div>';

        $('#report-content').html(html);
    }

    function loadProfitLoss() {
        var postData = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val()
        };

        postData[csrfName] = csrfHash;

        $('#report-content').html(
            '<div class="erp-loading">' +
                '<i class="fa fa-spinner fa-spin"></i> ' +
                safeText(plLabels.loading) +
            '</div>'
        );

        $.ajax({
            url: <?= json_encode(
                site_url('reports/get_profit_losss')
            ); ?>,

            type: 'POST',
            data: postData,
            dataType: 'json',

            success: function (data) {
                updateCsrfHash(data);
                renderProfitLoss(data || {});
            },

            error: function (xhr) {
                var response = xhr.responseJSON || {};

                updateCsrfHash(response);

                $('#report-content').html(
                    '<div class="erp-error">' +
                        '<i class="fa fa-exclamation-triangle"></i> ' +
                        safeText(plLabels.error_loading_data) +
                    '</div>'
                );
            }
        });
    }

    /* Date picker */
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        useCurrent: false,
        showTodayButton: true,
        showClear: false,
        showClose: true,
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-crosshairs',
            clear: 'fa fa-trash',
            close: 'fa fa-times'
        }
    });

    /* Filter opens only after clicking button */
    $('.erp-pl-page .toggle_form').on(
        'click',
        function (event) {
            event.preventDefault();

            var $button = $(this);
            var $form = $('.erp-pl-page #form');

            $form
                .stop(true, true)
                .slideToggle(
                    180,
                    function () {
                        var isVisible =
                            $form.is(':visible');

                        $button
                            .toggleClass(
                                'active',
                                isVisible
                            )
                            .attr(
                                'aria-expanded',
                                isVisible
                                    ? 'true'
                                    : 'false'
                            );

                        $form.attr(
                            'aria-hidden',
                            isVisible
                                ? 'false'
                                : 'true'
                        );
                    }
                );
        }
    );

    $('#profit_loss_form').on(
        'submit',
        function (event) {
            event.preventDefault();
            loadProfitLoss();
        }
    );

    $('#start_date, #end_date').on(
        'dp.change change',
        function () {
            loadProfitLoss();
        }
    );

    function showPdfStatus(message, isError) {
        var $status = $('#pdf_status_message');

        if (pdfStatusTimer) {
            clearTimeout(pdfStatusTimer);
        }

        $status
            .text(message || '')
            .toggleClass(
                'is-error',
                !!isError
            )
            .addClass('is-visible');

        pdfStatusTimer = setTimeout(
            function () {
                $status
                    .removeClass(
                        'is-visible is-error'
                    )
                    .text('');
            },
            5000
        );
    }

    function setPdfLoading(isLoading) {
        var $button =
            $('#generate_profit_loss_pdf');

        $button
            .toggleClass(
                'pdf-action-loading',
                isLoading
            )
            .prop(
                'disabled',
                isLoading
            );

        if (isLoading) {
            $button.html(
                '<i class="fa fa-spinner fa-spin"></i> ' +
                safeText(
                    plLabels.generating_pdf
                )
            );
        } else {
            $button.html(
                '<i class="fa fa-file-pdf-o"></i> ' +
                safeText(
                    plLabels.create_pdf
                )
            );
        }
    }

    function updatePdfResult(response) {
        currentPdf = response;

        $('#profit_loss_pdf_filename').text(
            response.file_name || ''
        );

        $('#profit_loss_pdf_open').attr(
            'href',
            response.open_url || '#'
        );

        $('#profit_loss_pdf_download').attr(
            'href',
            response.download_url || '#'
        );

        $('#profit_loss_pdf_result')
            .addClass('is-visible');

        var $result =
            $('#profit_loss_pdf_result');

        if ($result.length) {
            $('html, body').animate({
                scrollTop:
                    $result.offset().top - 15
            }, 250);
        }
    }

    function generateProfitLossPdfOnServer() {
        var postData = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val()
        };

        postData[csrfName] = csrfHash;

        $('#profit_loss_pdf_result')
            .removeClass('is-visible');

        setPdfLoading(true);

        $.ajax({
            url: <?= json_encode(
                site_url(
                    'reports/generate_profit_loss_pdf'
                )
            ); ?>,

            type: 'POST',
            data: postData,
            dataType: 'json',

            success: function (response) {
                updateCsrfHash(response);

                if (
                    !response ||
                    response.success !== true
                ) {
                    showPdfStatus(
                        response &&
                        response.message
                            ? response.message
                            : plLabels.pdf_failed,
                        true
                    );

                    return;
                }

                updatePdfResult(response);

                showPdfStatus(
                    response.message ||
                    plLabels.pdf_ready,
                    false
                );
            },

            error: function (xhr) {
                var response =
                    xhr.responseJSON || {};

                updateCsrfHash(response);

                showPdfStatus(
                    response.message ||
                    plLabels.pdf_failed,
                    true
                );
            },

            complete: function () {
                setPdfLoading(false);
            }
        });
    }

    function copyTextToClipboard(text) {
        if (
            navigator.clipboard &&
            window.isSecureContext
        ) {
            return navigator.clipboard.writeText(
                text
            );
        }

        return new Promise(
            function (resolve, reject) {
                var textarea =
                    document.createElement(
                        'textarea'
                    );

                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                textarea.style.top = '0';

                document.body.appendChild(
                    textarea
                );

                textarea.focus();
                textarea.select();

                try {
                    var copied =
                        document.execCommand(
                            'copy'
                        );

                    document.body.removeChild(
                        textarea
                    );

                    if (copied) {
                        resolve();
                    } else {
                        reject(
                            new Error(
                                'COPY_FAILED'
                            )
                        );
                    }
                } catch (error) {
                    document.body.removeChild(
                        textarea
                    );

                    reject(error);
                }
            }
        );
    }

    $('#generate_profit_loss_pdf').on(
        'click',
        function () {
            generateProfitLossPdfOnServer();
        }
    );

    $('#profit_loss_pdf_share').on(
        'click',
        function () {
            if (
                !currentPdf ||
                !currentPdf.share_url
            ) {
                return;
            }

            if (navigator.share) {
                navigator.share({
                    title: pageTitle,
                    text: $('#pl_period').text(),
                    url: currentPdf.share_url
                }).catch(
                    function (error) {
                        if (
                            !error ||
                            error.name !== 'AbortError'
                        ) {
                            copyTextToClipboard(
                                currentPdf.share_url
                            )
                            .then(function () {
                                showPdfStatus(
                                    plLabels.link_copied,
                                    false
                                );
                            })
                            .catch(function () {
                                showPdfStatus(
                                    plLabels.pdf_failed,
                                    true
                                );
                            });
                        }
                    }
                );

                return;
            }

            copyTextToClipboard(
                currentPdf.share_url
            )
            .then(function () {
                showPdfStatus(
                    plLabels.link_copied,
                    false
                );
            })
            .catch(function () {
                showPdfStatus(
                    plLabels.pdf_failed,
                    true
                );
            });
        }
    );

    $('#print_profit_loss').on(
        'click',
        function () {
            var printContent =
                $('#profit_loss_print_area')
                    .html();

            var printWindow =
                window.open('', '_blank');

            if (!printWindow) {
                return;
            }

            printWindow.document.open();

            printWindow.document.write(
                '<!DOCTYPE html>' +
                '<html>' +
                '<head>' +
                '<meta charset="UTF-8">' +
                '<meta name="viewport" content="width=device-width, initial-scale=1">' +
                '<title>' +
                    safeText(pageTitle) +
                '</title>' +
                '<style>' +
                    'body{' +
                        'font-family:"Noto Sans Myanmar","Pyidaungsu","Myanmar Text",Arial,sans-serif;' +
                        'padding:20px;' +
                        'color:#273444;' +
                    '}' +

                    'table{' +
                        'width:100%;' +
                        'border-collapse:collapse;' +
                    '}' +

                    'th,td{' +
                        'border:1px solid #dfe6ee;' +
                        'padding:9px;' +
                    '}' +

                    'th{' +
                        'background:#f1f5f9;' +
                        'text-align:left;' +
                    '}' +

                    '.amount-col{' +
                        'text-align:right;' +
                        'font-weight:bold;' +
                    '}' +

                    '.section-row td{' +
                        'background:#fbfcfe;' +
                        'font-weight:bold;' +
                    '}' +

                    '.subtotal-row td{' +
                        'background:#eef6ff;' +
                        'font-weight:bold;' +
                    '}' +

                    '.profit-row td{' +
                        'background:#ecfdf3;' +
                        'font-weight:bold;' +
                    '}' +

                    '.loss-row td{' +
                        'background:#fff1f2;' +
                        'color:#b42318;' +
                        'font-weight:bold;' +
                    '}' +

                    '.erp-report-toolbar{' +
                        'display:flex;' +
                        'justify-content:space-between;' +
                        'align-items:center;' +
                        'margin-bottom:12px;' +
                    '}' +

                    '.erp-period-badge{' +
                        'font-size:12px;' +
                        'color:#475569;' +
                    '}' +
                '</style>' +
                '</head>' +
                '<body>' +
                    printContent +
                '</body>' +
                '</html>'
            );

            printWindow.document.close();
            printWindow.focus();

            window.setTimeout(
                function () {
                    printWindow.print();
                },
                250
            );
        }
    );

    /* Initial report loading */
    loadProfitLoss();
});
</script>