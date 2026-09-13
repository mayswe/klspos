<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>
<?php

// Monday of current week
$start_date = date('Y-m-d 00:00:00', strtotime('monday this week'));

// Saturday of current week
$end_date = date('Y-m-d 23:59:59', strtotime('saturday this week'));

$L = function ($key, $fallback) {
    $line = lang($key);
    return ($line !== false && $line !== null && $line !== $key) ? $line : $fallback;
};

$pl_labels = [
    'summary'             => $L('summary', 'Summary'),
    'filter'              => $L('filter', 'Filter'),
    'report'              => $L('report', 'Report'),
    'print'               => $L('print', 'Print'),
    'create_pdf'          => $L('create_pdf', 'PDF ပြုလုပ်ရန်'),
    'generating_pdf'      => $L('generating_pdf', 'PDF ပြုလုပ်နေသည်...'),
    'uploading_pdf'       => $L('uploading_pdf', 'Server သို့ သိမ်းနေသည်...'),
    'pdf_ready'           => $L('pdf_ready', 'PDF အဆင်သင့်ဖြစ်ပါပြီ'),
    'pdf_saved_server'    => $L('pdf_saved_server', 'PDF ကို Server တွင် သိမ်းထားပါသည်။'),
    'open_pdf'            => $L('open_pdf', 'PDF ဖွင့်ရန်'),
    'download_pdf'        => $L('download_pdf', 'Download'),
    'share_pdf'           => $L('share_pdf', 'မျှဝေရန်'),
    'copy_link'           => $L('copy_link', 'Link ကူးရန်'),
    'link_copied'         => $L('link_copied', 'PDF Share Link ကို ကူးပြီးပါပြီ။'),
    'share_link_note'     => $L('share_link_note', 'Share Link သည် ၂၄ နာရီအတွင်း အသုံးပြုနိုင်ပါသည်။'),
    'pdf_failed'          => $L('pdf_failed', 'PDF ပြုလုပ်၍မရပါ။ ထပ်မံကြိုးစားပါ။'),
    'server_save_failed'  => $L('server_save_failed', 'PDF ကို Server တွင် သိမ်း၍မရပါ။'),
    'amount_mmk'          => $L('amount_mmk', 'Amount (MMK)'),
    'particulars'         => $L('particulars', 'အချက်အလက်'),
    'sales_revenue'       => $L('sales_revenue', 'အရောင်းဝင်ငွေ (Sales Revenue)'),
    'discounts_returns'   => $L('discounts_returns', 'လျှော့ချခြင်း/ပြန်လည်အမ်းပေးခြင်း (Less: Discounts/Returns)'),
    'net_sales'           => $L('net_sales', 'အရောင်းသန့် (Net Sales)'),
    'cogs'                => $L('cogs', 'ပစ္စည်းဝယ်ဈေး (COGS)'),
    'gross_profit'        => $L('gross_profit', 'အမြတ်အစွန်းမတိုင်မီ (Gross Profit)'),
    'operating_expenses'  => $L('operating_expenses', 'စီးပွားရေးစရိတ် (Operating Expenses)'),
    'depreciation'        => $L('depreciation', 'ပစ္စည်းတန်ဖိုးကျဆင်းမှု (Depreciation)'),
    'total_expenses'      => $L('total_expenses', 'စုစုပေါင်းစရိတ် (Total Expenses)'),
    'net_profit'          => $L('net_profit', 'အမြတ်သန့် (Net Profit)'),
    'period'              => $L('period', 'Period'),
    'loading'             => $L('loading', 'Loading...'),
    'error_loading_data'  => $L('error_loading_data', 'Unable to load report data.'),
];
?>

<style>
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
    }
    .erp-pl-page .erp-title-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef5ff;
        color: #2f80ed;
        font-size: 18px;
    }
    .erp-pl-page .erp-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #273444;
        line-height: 1.25;
    }
    .erp-pl-page .erp-subtitle {
        margin-top: 3px;
        color: #7b8794;
        font-size: 12px;
    }
    .erp-pl-page .erp-header-actions {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
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
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
        border: 1px dashed #86efac;
        border-radius: 7px;
        background: #fff;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
        overflow-wrap: anywhere;
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
    .erp-pl-page .erp-body {
        background: #f6f8fb;
        padding: 15px;
    }
    .erp-pl-page .erp-filter-card,
    .erp-pl-page .erp-summary-card,
    .erp-pl-page .erp-report-card {
        background: #fff;
        border: 1px solid #edf1f5;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(36, 52, 71, .05);
        margin-bottom: 15px;
    }
    .erp-pl-page .erp-filter-card {
        padding: 15px;
    }
    .erp-pl-page .erp-filter-title,
    .erp-pl-page .erp-report-title {
        margin: 0 0 12px;
        font-size: 15px;
        font-weight: 800;
        color: #273444;
    }
    .erp-pl-page .erp-filter-card label {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .erp-pl-page .erp-filter-card .form-control {
        border-radius: 6px;
        border-color: #dfe6ee;
        box-shadow: none;
    }
    .erp-pl-page .erp-stat-grid {
        display: flex;
        flex-wrap: wrap;
        margin-left: -7px;
        margin-right: -7px;
    }
    .erp-pl-page .erp-stat-item {
        padding-left: 7px;
        padding-right: 7px;
        margin-bottom: 14px;
    }
    .erp-pl-page .erp-stat-card {
        padding: 14px 15px;
        min-height: 88px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        overflow: hidden;
        position: relative;
    }
    .erp-pl-page .erp-stat-card:after {
        content: '';
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: rgba(47, 128, 237, .08);
        position: absolute;
        right: -25px;
        bottom: -28px;
    }
    .erp-pl-page .erp-stat-label {
        font-size: 12px;
        color: #7b8794;
        font-weight: 700;
        text-transform: uppercase;
    }
    .erp-pl-page .erp-stat-value {
        margin-top: 6px;
        font-size: 19px;
        font-weight: 800;
        color: #273444;
    }
    .erp-pl-page .erp-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f2f6fb;
        color: #2f80ed;
        font-size: 18px;
        z-index: 1;
    }
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
        background: #f1f5f9 !important;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle !important;
        border-bottom: 1px solid #dfe6ee !important;
    }
    .erp-pl-page .erp-pl-table tbody td {
        vertical-align: middle !important;
        color: #344054;
        padding: 10px 9px !important;
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
        text-align: center;
        color: #64748b;
        border: 1px dashed #dfe6ee;
        border-radius: 10px;
        background: #fbfcfe;
    }
    .erp-pl-page .erp-error {
        color: #b42318;
        background: #fff1f2;
        border-color: #fecdd3;
    }
    @media (max-width: 767px) {
        .erp-pl-page .erp-header,
        .erp-pl-page .erp-report-toolbar {
            display: block;
        }
        .erp-pl-page .erp-header-actions {
            justify-content: flex-start;
            margin-top: 10px;
            width: 100%;
        }
        .erp-pl-page .desktop-print-action {
            display: none !important;
        }
        .erp-pl-page .mobile-pdf-actions {
            display: flex;
            width: 100%;
        }
        .erp-pl-page .mobile-pdf-actions .btn {
            flex: 1 1 100%;
            width: 100%;
            min-width: 0;
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
    }
</style>

<section class="content erp-pl-page">
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="erp-header">
                    <div class="erp-title-wrap">
                        <span class="erp-title-icon"><i class="fa fa-line-chart"></i></span>
                        <div>
                            <h4 class="erp-title"><?= $page_title; ?></h4>
                            <div class="erp-subtitle"><?= $pl_labels['report']; ?> / <?= $pl_labels['summary']; ?></div>
                        </div>
                    </div>
                    <div class="erp-header-actions">
                        <a href="#" class="btn btn-default btn-sm toggle_form"><i class="fa fa-filter"></i> <?= lang("show_hide"); ?></a>
                        <button type="button" class="btn btn-primary btn-sm desktop-print-action" id="print_profit_loss">
                            <i class="fa fa-print"></i> <?= $pl_labels['print']; ?>
                        </button>
                        <div class="mobile-pdf-actions" id="mobile_pdf_actions">
                            <button type="button" class="btn btn-primary btn-sm" id="generate_profit_loss_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= $pl_labels['create_pdf']; ?>
                            </button>
                        </div>
                        <div class="pdf-status-message" id="pdf_status_message" aria-live="polite"></div>
                    </div>
                </div>

                <div class="box-body erp-body">
                    <div id="form" class="erp-filter-card">
                        <h4 class="erp-filter-title"><i class="fa fa-sliders"></i> <?= $pl_labels['filter']; ?></h4>
                        <?= form_open("reports/profit_loss", 'id="profit_loss_form"'); ?>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?= lang("start_date"); ?></label>
                                    <?= form_input(
                                        'start_date',
                                        set_value('start_date', $start_date),
                                        'class="form-control datetimepicker" id="start_date"'
                                    ); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?= lang("end_date"); ?></label>
                                    <?= form_input(
                                        'end_date',
                                        set_value('end_date', $end_date),
                                        'class="form-control datetimepicker" id="end_date"'
                                    ); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> <?= lang("submit"); ?></button>
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>

                    <div class="erp-stat-grid">
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= $pl_labels['net_sales']; ?></div>
                                    <div class="erp-stat-value" id="pl_net_sales">0.00</div>
                                </div>
                                <span class="erp-stat-icon"><i class="fa fa-shopping-cart"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= $pl_labels['gross_profit']; ?></div>
                                    <div class="erp-stat-value" id="pl_gross_profit">0.00</div>
                                </div>
                                <span class="erp-stat-icon"><i class="fa fa-bar-chart"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= $pl_labels['total_expenses']; ?></div>
                                    <div class="erp-stat-value" id="pl_total_expenses">0.00</div>
                                </div>
                                <span class="erp-stat-icon"><i class="fa fa-credit-card"></i></span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= $pl_labels['net_profit']; ?></div>
                                    <div class="erp-stat-value" id="pl_net_profit">0.00</div>
                                </div>
                                <span class="erp-stat-icon"><i class="fa fa-money"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="pdf-result-card" id="profit_loss_pdf_result">
                        <div class="pdf-result-head">
                            <span class="pdf-result-icon"><i class="fa fa-check"></i></span>
                            <div>
                                <h4 class="pdf-result-title"><?= $pl_labels['pdf_ready']; ?></h4>
                                <div class="pdf-result-text"><?= $pl_labels['pdf_saved_server']; ?></div>
                            </div>
                        </div>

                        <div class="pdf-file-name">
                            <i class="fa fa-file-pdf-o"></i>
                            <span id="profit_loss_pdf_filename"></span>
                        </div>

                        <div class="pdf-result-actions">
                            <a href="#" class="btn btn-success btn-sm" id="profit_loss_pdf_open">
                                <i class="fa fa-eye"></i> <?= $pl_labels['open_pdf']; ?>
                            </a>
                            <a href="#" class="btn btn-primary btn-sm" id="profit_loss_pdf_download">
                                <i class="fa fa-download"></i> <?= $pl_labels['download_pdf']; ?>
                            </a>
                            <button type="button" class="btn btn-default btn-sm" id="profit_loss_pdf_share">
                                <i class="fa fa-share-alt"></i> <?= $pl_labels['share_pdf']; ?>
                            </button>
                        </div>

                        <div class="pdf-share-note">
                            <i class="fa fa-clock-o"></i> <?= $pl_labels['share_link_note']; ?>
                        </div>
                    </div>

                    <div class="erp-report-card" id="profit_loss_print_area">
                        <div class="erp-report-toolbar">
                            <h4 class="erp-report-title"><i class="fa fa-file-text-o"></i> <?= $page_title; ?></h4>
                            <span class="erp-period-badge"><i class="fa fa-calendar"></i> <span id="pl_period"><?= $pl_labels['period']; ?></span></span>
                        </div>
                        <div id="report-content">
                            <div class="erp-loading"><i class="fa fa-spinner fa-spin"></i> <?= $pl_labels['loading']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(function () {
    var plLabels = <?= json_encode($pl_labels, JSON_UNESCAPED_UNICODE); ?>;
    var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
    var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";

    function erpPF(value) {
        if (typeof pf === 'function') {
            return pf(value);
        }
        value = (value === null || value === undefined || value === '') ? 0 : value;
        return parseFloat(String(value).replace(/,/g, '')) || 0;
    }

    function erpCF(value) {
        if (typeof cf === 'function') {
            return cf(erpPF(value));
        }
        return Number(erpPF(value)).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getAmount(data, key) {
        return erpPF(data && data[key] !== undefined ? data[key] : 0);
    }

    function safeText(text) {
        return $('<div/>').text(text).html();
    }

    function amountCell(value) {
        return '<td class="amount-col">' + erpCF(value) + '</td>';
    }

    function updateSummary(data) {
        var netProfit = getAmount(data, 'net_profit');

        $('#pl_net_sales').html(erpCF(getAmount(data, 'net_sales')));
        $('#pl_gross_profit').html(erpCF(getAmount(data, 'gross_profit')));
        $('#pl_total_expenses').html(erpCF(getAmount(data, 'total_expenses')));
        $('#pl_net_profit')
            .html(erpCF(netProfit))
            .toggleClass('text-danger', netProfit < 0)
            .toggleClass('text-success', netProfit >= 0);

        $('#pl_period').html(safeText($('#start_date').val() + ' - ' + $('#end_date').val()));
    }

    function renderProfitLoss(data) {
        updateSummary(data);

        var netProfit = getAmount(data, 'net_profit');
        var profitRowClass = netProfit < 0 ? 'loss-row' : 'profit-row';

        var html = '';
        html += '<div class="table-responsive">';
        html += '<table class="table table-bordered table-striped erp-pl-table">';
        html += '<thead>';
        html += '<tr>';
        html += '<th style="width:70%;">' + safeText(plLabels.particulars) + '</th>';
        html += '<th style="width:30%; text-align:right;">' + safeText(plLabels.amount_mmk) + '</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        html += '<tr class="section-row"><td colspan="2">Revenue</td></tr>';
        html += '<tr><td>' + safeText(plLabels.sales_revenue) + '</td>' + amountCell(getAmount(data, 'sales')) + '</tr>';
        html += '<tr><td>' + safeText(plLabels.discounts_returns) + '</td>' + amountCell(getAmount(data, 'discounts')) + '</tr>';
        html += '<tr class="subtotal-row"><td>' + safeText(plLabels.net_sales) + '</td>' + amountCell(getAmount(data, 'net_sales')) + '</tr>';

        html += '<tr class="section-row"><td colspan="2">Cost / Profit</td></tr>';
        html += '<tr class="expense-row"><td>' + safeText(plLabels.cogs) + '</td>' + amountCell(getAmount(data, 'cogs')) + '</tr>';
        html += '<tr class="subtotal-row"><td>' + safeText(plLabels.gross_profit) + '</td>' + amountCell(getAmount(data, 'gross_profit')) + '</tr>';

        html += '<tr class="section-row"><td colspan="2">Expenses</td></tr>';
        html += '<tr class="expense-row"><td>' + safeText(plLabels.operating_expenses) + '</td>' + amountCell(getAmount(data, 'operating_expenses')) + '</tr>';
        html += '<tr class="expense-row"><td>' + safeText(plLabels.depreciation) + '</td>' + amountCell(getAmount(data, 'depreciation')) + '</tr>';
        html += '<tr class="subtotal-row"><td>' + safeText(plLabels.total_expenses) + '</td>' + amountCell(getAmount(data, 'total_expenses')) + '</tr>';

        html += '<tr class="' + profitRowClass + '"><td>' + safeText(plLabels.net_profit) + '</td>' + amountCell(netProfit) + '</tr>';
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

        $('#report-content').html('<div class="erp-loading"><i class="fa fa-spinner fa-spin"></i> ' + safeText(plLabels.loading) + '</div>');

        $.ajax({
            url: "<?= site_url('reports/get_profit_losss'); ?>",
            type: "POST",
            data: postData,
            dataType: "json",
            success: function(data) {
                renderProfitLoss(data || {});
            },
            error: function() {
                $('#report-content').html('<div class="erp-error"><i class="fa fa-exclamation-triangle"></i> ' + safeText(plLabels.error_loading_data) + '</div>');
            }
        });
    }

    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm'
    });

    $('.toggle_form').on('click', function(e) {
        e.preventDefault();
        $('#form').slideToggle();
    });

    $('#profit_loss_form').on('submit', function(e) {
        e.preventDefault();
        loadProfitLoss();
    });

    $('#start_date, #end_date').on('dp.change change', function() {
        loadProfitLoss();
    });


    var pdfStatusTimer = null;
    var currentPdf = null;

    function showPdfStatus(message, isError) {
        var $status = $('#pdf_status_message');

        if (pdfStatusTimer) {
            clearTimeout(pdfStatusTimer);
        }

        $status
            .text(message || '')
            .toggleClass('is-error', !!isError)
            .addClass('is-visible');

        pdfStatusTimer = setTimeout(function () {
            $status.removeClass('is-visible is-error').text('');
        }, 5000);
    }

    function setPdfLoading(isLoading) {
        var $button = $('#generate_profit_loss_pdf');

        $button
            .toggleClass('pdf-action-loading', isLoading)
            .prop('disabled', isLoading);

        if (isLoading) {
            $button.html(
                '<i class="fa fa-spinner fa-spin"></i> ' +
                safeText(plLabels.generating_pdf)
            );
        } else {
            $button.html(
                '<i class="fa fa-file-pdf-o"></i> ' +
                safeText(plLabels.create_pdf)
            );
        }
    }

    function updatePdfResult(response) {
        currentPdf = response;

        $('#profit_loss_pdf_filename').text(response.file_name || '');
        $('#profit_loss_pdf_open').attr('href', response.open_url || '#');
        $('#profit_loss_pdf_download').attr('href', response.download_url || '#');
        $('#profit_loss_pdf_result').addClass('is-visible');

        $('html, body').animate({
            scrollTop: $('#profit_loss_pdf_result').offset().top - 15
        }, 250);
    }

    function generateProfitLossPdfOnServer() {
        var postData = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val()
        };

        postData[csrfName] = csrfHash;

        $('#profit_loss_pdf_result').removeClass('is-visible');
        setPdfLoading(true);

        $.ajax({
            url: "<?= site_url('reports/generate_profit_loss_pdf'); ?>",
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function (response) {
                if (response && response.csrf_hash) {
                    csrfHash = response.csrf_hash;
                }

                if (!response || response.success !== true) {
                    showPdfStatus(
                        response && response.message
                            ? response.message
                            : plLabels.pdf_failed,
                        true
                    );
                    return;
                }

                updatePdfResult(response);
                showPdfStatus(
                    response.message || plLabels.pdf_ready,
                    false
                );
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                var message = response.message || plLabels.pdf_failed;

                if (response.csrf_hash) {
                    csrfHash = response.csrf_hash;
                }

                showPdfStatus(message, true);
            },
            complete: function () {
                setPdfLoading(false);
            }
        });
    }

    function copyTextToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            var textarea = document.createElement('textarea');

            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '0';

            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                var copied = document.execCommand('copy');
                document.body.removeChild(textarea);

                copied ? resolve() : reject(new Error('COPY_FAILED'));
            } catch (error) {
                document.body.removeChild(textarea);
                reject(error);
            }
        });
    }

    $('#generate_profit_loss_pdf').on('click', function () {
        generateProfitLossPdfOnServer();
    });

    $('#profit_loss_pdf_share').on('click', function () {
        if (!currentPdf || !currentPdf.share_url) {
            return;
        }

        if (navigator.share) {
            navigator.share({
                title: '<?= html_escape($page_title); ?>',
                text: $('#pl_period').text(),
                url: currentPdf.share_url
            }).catch(function (error) {
                if (!error || error.name !== 'AbortError') {
                    copyTextToClipboard(currentPdf.share_url)
                        .then(function () {
                            showPdfStatus(plLabels.link_copied, false);
                        })
                        .catch(function () {
                            showPdfStatus(plLabels.pdf_failed, true);
                        });
                }
            });

            return;
        }

        copyTextToClipboard(currentPdf.share_url)
            .then(function () {
                showPdfStatus(plLabels.link_copied, false);
            })
            .catch(function () {
                showPdfStatus(plLabels.pdf_failed, true);
            });
    });

    $('#print_profit_loss').on('click', function() {
        var printContent = $('#profit_loss_print_area').html();
        var printWindow = window.open('', '_blank');

        printWindow.document.open();
        printWindow.document.write('<html><head><title><?= html_escape($page_title); ?></title>');
        printWindow.document.write('<style>body{font-family:Arial,sans-serif;padding:20px;color:#273444;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #dfe6ee;padding:9px;}th{background:#f1f5f9;text-align:left;}.amount-col{text-align:right;font-weight:bold;}.section-row td{background:#fbfcfe;font-weight:bold;}.subtotal-row td{background:#eef6ff;font-weight:bold;}.profit-row td{background:#ecfdf3;font-weight:bold;}.loss-row td{background:#fff1f2;color:#b42318;font-weight:bold;}.erp-report-toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}.erp-period-badge{font-size:12px;color:#475569;}</style>');
        printWindow.document.write('</head><body>' + printContent + '</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    });

    loadProfitLoss();
});
</script>
