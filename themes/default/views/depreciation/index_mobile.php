<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Depreciation Expense List mobile/app mode.
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

$add_depreciation_url =
    site_url('depreciation/create') . $app_query;
?>


<style type="text/css">
    .erp-page .box {
        border-top: 0;
        border-radius: 10px;
        box-shadow: 0 3px 14px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .erp-page .box-header.erp-header {
        padding: 16px 18px;
        border-bottom: 1px solid #edf1f5;
        background: linear-gradient(135deg, #f8fbff 0%, #ffffff 100%);
    }
    .erp-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .erp-title-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #3c8dbc;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: 0 6px 14px rgba(60,141,188,.25);
    }
    .erp-page .box-title,
    .erp-page h4 {
        margin: 0;
        font-weight: 700;
        color: #263238;
    }
    .erp-subtitle {
        display: block;
        margin-top: 3px;
        color: #7a8793;
        font-size: 12px;
    }
    .erp-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }
    .erp-summary-row {
        margin-bottom: 15px;
    }
    .erp-stat-card {
        min-height: 86px;
        border: 1px solid #edf1f5;
        border-radius: 10px;
        padding: 14px 14px 12px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
        position: relative;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .erp-stat-card:before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: #3c8dbc;
    }
    .erp-stat-card.success:before { background: #00a65a; }
    .erp-stat-card.warning:before { background: #f39c12; }
    .erp-stat-card.danger:before { background: #dd4b39; }
    .erp-stat-label {
        color: #7a8793;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 6px;
    }
    .erp-stat-value {
        font-size: 20px;
        font-weight: 800;
        color: #263238;
        line-height: 1.2;
        word-break: break-word;
    }
    .erp-stat-icon {
        position: absolute;
        right: 14px;
        bottom: 10px;
        color: rgba(0,0,0,.09);
        font-size: 30px;
    }
    .erp-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding: 12px;
        border: 1px solid #edf1f5;
        border-bottom: 0;
        background: #fbfdff;
        border-radius: 10px 10px 0 0;
    }
    .erp-toolbar-left,
    .erp-toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .erp-toolbar .form-control {
        min-width: 240px;
        height: 34px;
    }
    .erp-table-wrap {
        border: 1px solid #edf1f5;
        border-radius: 0 0 10px 10px;
        overflow: hidden;
    }
    .erp-page table.dataTable {
        margin-bottom: 0 !important;
    }
    .erp-page table thead tr.active th {
        background: #f4f7fb;
        color: #37474f;
        border-bottom: 1px solid #dfe7ef;
        font-weight: 700;
        vertical-align: middle;
        white-space: nowrap;
    }
    .erp-page table tfoot tr.active th {
        background: #f9fbfd;
        vertical-align: middle;
    }
    .erp-page table.dataTable tbody td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .erp-page .text_filter,
    .erp-page .select_filter {
        width: 100%;
        height: 30px;
        font-size: 12px;
        border-radius: 6px;
        border: 1px solid #d6dde5;
        padding: 4px 8px;
    }
    .erp-page .erp-actions {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex-wrap: wrap;
        white-space: nowrap;
    }
    .erp-page .erp-actions .btn {
        margin: 1px;
    }
    .erp-page .erp-method-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        background: #eef6ff;
        color: #31708f;
        font-weight: 700;
        font-size: 11px;
        text-transform: capitalize;
    }
    .erp-page .dt-buttons .btn,
    .erp-page .dt-buttons .dt-button {
        margin-right: 4px;
        margin-bottom: 4px;
    }
    .erp-page .erp-dt-top,
    .erp-page .erp-dt-bottom {
        margin-left: 0;
        margin-right: 0;
        background: #fbfdff;
    }
    .erp-page .erp-dt-top {
        padding: 12px 12px 8px;
        border-bottom: 1px solid #edf1f5;
    }
    .erp-page .erp-dt-bottom {
        padding: 10px 12px;
        border-top: 1px solid #edf1f5;
    }
    .erp-page .erp-dt-top .dataTables_length {
        margin: 0;
        padding-top: 2px;
        text-align: right;
    }
    .erp-page .erp-dt-top .dataTables_length label {
        margin-bottom: 0;
        font-weight: 600;
        color: #607080;
        white-space: nowrap;
    }
    .erp-page .erp-dt-top .dataTables_length select {
        height: 32px;
        min-width: 64px;
        margin: 0 6px;
        border: 1px solid #d6dde5;
        border-radius: 6px;
        padding: 4px 8px;
        background: #fff;
    }
    .erp-page .erp-dt-top .dt-buttons {
        margin-bottom: 0;
    }
    @media (max-width: 767px) {
        .erp-page { padding: 10px 8px; }
        .erp-page .box-body { padding: 10px; }
        .erp-page .box-header.erp-header { padding: 13px 12px; }
        .erp-page .erp-title-icon { width: 38px; height: 38px; }
        .erp-page .erp-header-actions .btn { width: 100%; margin-top: 10px; }
        .erp-summary-row { margin-left: -5px; margin-right: -5px; }
        .erp-summary-row > [class*="col-"] { padding-left: 5px; padding-right: 5px; }
        .erp-stat-card { min-height: 74px; padding: 10px 10px 9px; margin-bottom: 10px; }
        .erp-stat-label { font-size: 10px; margin-bottom: 4px; line-height: 1.35; padding-right: 20px; }
        .erp-stat-value { font-size: 17px; }
        .erp-stat-icon { right: 9px; bottom: 8px; font-size: 23px; }
        .erp-toolbar {
            display: block;
            padding: 10px;
        }
        .erp-toolbar-left,
        .erp-toolbar-right,
        .erp-header-actions {
            justify-content: flex-start;
            margin-top: 8px;
        }
        .erp-toolbar .form-control {
            width: 100%;
            min-width: 100%;
        }
        .erp-page .erp-dt-top .dataTables_length,
        .erp-page .erp-dt-top .dt-buttons {
            text-align: left;
            margin-top: 8px;
        }
        .erp-page .erp-dt-top { padding: 8px; }
        .erp-page .erp-dt-top .dt-buttons { display: flex; overflow-x: auto; padding-bottom: 3px; }
        .erp-page .erp-dt-top .dt-buttons .btn { flex: 0 0 auto; padding: 7px 10px; }
        .erp-page .erp-dt-top .dataTables_length { text-align: left; }
        .erp-page .erp-dt-bottom { padding: 10px 8px; text-align: center; }
        .erp-page .erp-dt-bottom .dataTables_info,
        .erp-page .erp-dt-bottom .dataTables_paginate { float: none !important; text-align: center !important; }

        /* Mobile card table */
        .erp-table-wrap { overflow: visible; border: 0; background: transparent; }
        #depreciationData, #depreciationData tbody { display: block; width: 100% !important; }
        #depreciationData thead, #depreciationData tfoot { display: none; }
        #depreciationData tbody tr { display: block; margin: 10px 0; padding: 7px 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
        #depreciationData tbody td { display: flex; align-items: flex-start; justify-content: space-between; gap: 15px; width: 100% !important; padding: 8px 0 !important; border: 0 !important; border-bottom: 1px dashed #e5eaf0 !important; white-space: normal !important; text-align: right !important; }
        #depreciationData tbody td:last-child { border-bottom: 0 !important; justify-content: flex-end; }
        #depreciationData tbody td:before { content: attr(data-label); flex: 0 0 43%; color: #718096; font-size: 11px; font-weight: 700; text-align: left; }
        #depreciationData tbody td:last-child:before { display: none; }
        #depreciationData tbody tr.child { display: none !important; }
        #depreciationData .dataTables_empty { display: block; text-align: center !important; border-bottom: 0 !important; }
        #depreciationData .dataTables_empty:before { display: none; }
    }
</style>

<?php if ($is_app_mode) { ?>
<style>
/* =========================================================
   KLSPOS Depreciation Expense List - True Mobile/App Layout
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

/* Full-width Depreciation page */
.content.erp-page {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.erp-page > .row {
    width: 100% !important;
    margin: 0 !important;
}

.erp-page > .row > .col-xs-12 {
    width: 100% !important;
    padding: 0 !important;
    float: none !important;
}

.erp-page .box.box-primary {
    width: 100% !important;
    margin: 0 !important;
    overflow: hidden !important;
    border: 1px solid #e3e9ef !important;
    border-top: 1px solid #e3e9ef !important;
    border-radius: 12px !important;
    background: #fff !important;
    box-shadow: none !important;
}

/* Header */
.erp-page .box-header.erp-header {
    padding: 13px !important;
}

.erp-page .box-header.erp-header > .row {
    display: block !important;
    margin: 0 !important;
}

.erp-page .box-header.erp-header > .row > [class*="col-"] {
    width: 100% !important;
    padding: 0 !important;
    float: none !important;
}

.erp-page .erp-title-wrap {
    width: 100% !important;
}

.erp-page .erp-title-icon {
    width: 38px !important;
    height: 38px !important;
    flex: 0 0 38px !important;
}

.erp-page .box-title {
    font-size: 17px !important;
    line-height: 1.45 !important;
}

.erp-page .erp-subtitle {
    font-size: 13px !important;
}

.erp-page .erp-header-actions {
    width: 100% !important;
    margin-top: 11px !important;
}

.erp-page .erp-header-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    width: 100% !important;
    min-height: 43px !important;
    margin: 0 !important;
    font-size: 14px !important;
}

/* Body */
.erp-page .box-body {
    padding: 10px !important;
    background: #f4f7fb !important;
}

/* Summary cards: stable 2 x 2 app layout */
.erp-page .erp-summary-row {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    grid-auto-flow: row !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
}

.erp-page .erp-summary-row::before,
.erp-page .erp-summary-row::after {
    display: none !important;
    content: none !important;
}

.erp-page .erp-summary-row > [class*="col-"] {
    width: auto !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}

.erp-page .erp-stat-card {
    width: 100% !important;
    min-height: 86px !important;
    margin: 0 !important;
    padding: 12px !important;
}

.erp-page .erp-stat-label {
    font-size: 12px !important;
    line-height: 1.4 !important;
    text-transform: none !important;
}

.erp-page .erp-stat-value {
    font-size: 17px !important;
    line-height: 1.35 !important;
}

/* Toolbar */
.erp-page .erp-toolbar {
    display: block !important;
    padding: 10px !important;
}

.erp-page .erp-toolbar-left {
    margin: 0 0 9px !important;
}

.erp-page .erp-toolbar-right {
    width: 100% !important;
    margin: 0 !important;
}

.erp-page #search_table {
    width: 100% !important;
    min-width: 0 !important;
    height: 42px !important;
    margin: 0 !important;
    border-radius: 8px !important;
    font-size: 14px !important;
}

/* Hide export and page-length controls in app mode */
.erp-page .erp-dt-top,
.erp-page .dt-buttons,
.erp-page .dataTables_length {
    display: none !important;
}

/*
 * The enhanced mobile file already renders each depreciation row as
 * a readable card. Keep that card layout and remove desktop table borders.
 */
.erp-page .erp-table-wrap {
    width: 100% !important;
    margin: 0 !important;
    overflow: visible !important;
    border: 0 !important;
    background: transparent !important;
}

.erp-page #depreciationData,
.erp-page #depreciationData tbody {
    display: block !important;
    width: 100% !important;
}

.erp-page #depreciationData thead,
.erp-page #depreciationData tfoot {
    display: none !important;
}

.erp-page #depreciationData tbody tr {
    display: block !important;
    width: 100% !important;
    margin: 10px 0 !important;
    padding: 7px 12px !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 10px !important;
    background: #fff !important;
    box-shadow: 0 2px 8px rgba(0,0,0,.04) !important;
}

.erp-page #depreciationData tbody td {
    display: flex !important;
    align-items: flex-start !important;
    justify-content: space-between !important;
    gap: 15px !important;
    width: 100% !important;
    padding: 9px 0 !important;
    border: 0 !important;
    border-bottom: 1px dashed #e5eaf0 !important;
    white-space: normal !important;
    text-align: right !important;
    word-break: break-word !important;
}

.erp-page #depreciationData tbody td:last-child {
    border-bottom: 0 !important;
    justify-content: flex-end !important;
}

.erp-page #depreciationData tbody td::before {
    content: attr(data-label);
    flex: 0 0 43%;
    color: #718096;
    font-size: 11px;
    font-weight: 700;
    text-align: left;
}

.erp-page #depreciationData tbody td:last-child::before,
.erp-page #depreciationData .dataTables_empty::before {
    display: none !important;
}

.erp-page #depreciationData .dataTables_empty {
    display: block !important;
    text-align: center !important;
    border-bottom: 0 !important;
}

.erp-page .erp-dt-bottom {
    padding: 10px 8px !important;
    text-align: center !important;
}

.erp-page .erp-dt-bottom .dataTables_info,
.erp-page .erp-dt-bottom .dataTables_paginate {
    float: none !important;
    text-align: center !important;
}

.erp-page .btn-group .dropdown-menu,
.erp-page .erp-actions .dropdown-menu,
.bootstrap-datetimepicker-widget {
    z-index: 999999 !important;
}

@media (max-width: 420px) {
    .erp-page .erp-summary-row {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script type="text/javascript">
$(document).ready(function() {

    function parseNumber(x) {
        if (x === null || x === undefined || x === '') {
            return 0;
        }
        if (typeof x === 'number') {
            return x;
        }
        return parseFloat((x + '').replace(/,/g, '').replace(/<[^>]*>/g, '')) || 0;
    }

    function moneyText(x) {
        var formatted = cf(parseNumber(x));
        return $('<div>').html(formatted).text() || '0.00';
    }

    function numberText(x) {
        var num = parseNumber(x);
        return (num % 1 === 0) ? num.toString() : num.toFixed(2);
    }

    function methodBadge(data, type) {
        if (type !== 'display') {
            return data;
        }
        if (!data) {
            return '<span class="text-muted">-</span>';
        }
        return '<span class="erp-method-badge">' + data + '</span>';
    }

    function updateSummary(api) {
        var rows = api.rows({ search: 'applied' }).data();
        var records = rows.length;
        var purchaseCost = 0;
        var depreciationAmount = 0;
        var usefulLifeTotal = 0;
        var usefulLifeCount = 0;

        $.each(rows, function(i, row) {
            purchaseCost += parseNumber(row.purchase_cost);
            depreciationAmount += parseNumber(row.depreciation_amount);
            var life = parseNumber(row.useful_life);
            if (life > 0) {
                usefulLifeTotal += life;
                usefulLifeCount++;
            }
        });

        var avgLife = usefulLifeCount ? (usefulLifeTotal / usefulLifeCount) : 0;

        $('#summary_records').text(records);
        $('#summary_purchase_cost').text(moneyText(purchaseCost));
        $('#summary_depreciation_amount').text(moneyText(depreciationAmount));
        $('#summary_useful_life').text(numberText(avgLife));
    }

    var table = $('#depreciationData').DataTable({
        ajax: {
            url: '<?= site_url('depreciation/get_depreciation_expenses'); ?>',
            type: 'POST',
            data: function(d) {
                d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
            }
        },
        dom: "<'row erp-dt-top'<'col-sm-6 erp-dt-buttons'B><'col-sm-6 erp-dt-length'l>>rt<'row erp-dt-bottom'<'col-sm-6'i><'col-sm-6'p>>",
        pageLength: 25,
        autoWidth: false,
        order: [[2, 'desc']],
        buttons: [
            { extend: 'copyHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
            { extend: 'excelHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
            { extend: 'csvHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
            { extend: 'colvis', text: '<i class="fa fa-columns"></i> <?= lang('columns'); ?>' }
        ],
        columns: [
            { data: 'id', visible: false },
            { data: 'asset_name' },
            { data: 'purchase_date', render: hrld },
            { data: 'purchase_cost', render: currencyFormat, className: 'text-right' },
            { data: 'useful_life', className: 'text-center' },
            { data: 'method', render: methodBadge, className: 'text-center' },
            { data: 'depreciation_amount', render: currencyFormat, className: 'text-right' },
            { data: 'note' },
            { data: 'user' },
            {
                data: 'Actions',
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    return '<div class="erp-actions">' + (data || '') + '</div>';
                }
            }
        ],
        footerCallback: function(tfoot, data, start, end, display) {
            var api = this.api();

            var purchaseCost = api.column(3, { search: 'applied' }).data().reduce(function(a, b) {
                return parseNumber(a) + parseNumber(b);
            }, 0);

            var depreciationAmount = api.column(6, { search: 'applied' }).data().reduce(function(a, b) {
                return parseNumber(a) + parseNumber(b);
            }, 0);

            $(api.column(3).footer()).html(moneyText(purchaseCost));
            $(api.column(6).footer()).html(moneyText(depreciationAmount));
        },
        drawCallback: function() {
            var api = this.api();
            updateSummary(api);
            // Visible TD order only (hidden ID column is not rendered in tbody).
            // Explicit labels prevent DataTables hidden/ColVis columns from shifting labels.
            var labels = [
                '<?= lang('asset_name'); ?>',
                '<?= lang('purchase_date'); ?>',
                '<?= lang('purchase_cost'); ?>',
                '<?= lang('useful_life'); ?>',
                '<?= lang('method'); ?>',
                '<?= lang('depreciation_amount'); ?>',
                '<?= lang('note'); ?>',
                '<?= lang('created_by'); ?>',
                '<?= lang('actions'); ?>'
            ];
            api.rows({ page: 'current' }).nodes().each(function(row) {
                $(row).children('td').each(function(i) {
                    $(this).attr('data-label', labels[i] || '');
                });
            });
        }
    });

    $('#search_table').on('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
            table.search(this.value).draw();
        }
    });

    table.columns().every(function() {
        var self = this;
        $('input.datepicker', this.footer()).on('dp.change', function() {
            self.search(this.value).draw();
        });
        $('input:not(.datepicker)', this.footer()).on('keyup change', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                self.search(this.value).draw();
            }
        });
        $('select', this.footer()).on('change', function() {
            self.search(this.value).draw();
        });
    });
});
</script>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content erp-page kls-mobile-ui">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header erp-header">
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="erp-title-wrap">
                                <div class="erp-title-icon"><i class="fa fa-calculator"></i></div>
                                <div>
                                    <h4 class="box-title"><?= $page_title; ?></h4>
                                    <span class="erp-subtitle"><?= lang('list'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="erp-header-actions">
                                <a
                                    href="<?= html_escape($add_depreciation_url); ?>"
                                    class="btn btn-primary"
                                >
                                    <i class="fa fa-plus"></i> <?= lang('add_depreciation_expense'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-body">
                    <div class="row erp-summary-row">
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="erp-stat-card">
                                <div class="erp-stat-label"><?= lang('records'); ?></div>
                                <div class="erp-stat-value" id="summary_records">0</div>
                                <i class="fa fa-list erp-stat-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="erp-stat-card success">
                                <div class="erp-stat-label"><?= lang('purchase_cost'); ?></div>
                                <div class="erp-stat-value" id="summary_purchase_cost">0.00</div>
                                <i class="fa fa-money erp-stat-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="erp-stat-card warning">
                                <div class="erp-stat-label"><?= lang('depreciation_amount'); ?></div>
                                <div class="erp-stat-value" id="summary_depreciation_amount">0.00</div>
                                <i class="fa fa-line-chart erp-stat-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="erp-stat-card danger">
                                <div class="erp-stat-label"><?= lang('useful_life'); ?></div>
                                <div class="erp-stat-value" id="summary_useful_life">0</div>
                                <i class="fa fa-clock-o erp-stat-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="erp-toolbar">
                        <div class="erp-toolbar-left">
                            <strong><i class="fa fa-table"></i> <?= $page_title; ?> <?= lang('list'); ?></strong>
                        </div>
                        <div class="erp-toolbar-right">
                            <input type="text" class="form-control" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>">
                        </div>
                    </div>

                    <div class="table-responsive erp-table-wrap">
                        <table id="depreciationData" class="table table-bordered table-hover table-striped table-condensed" style="width:100%;">
                            <thead>
                                <tr class="active">
                                    <th><?= lang('id'); ?></th>
                                    <th><?= lang('asset_name'); ?></th>
                                    <th><?= lang('purchase_date'); ?></th>
                                    <th><?= lang('purchase_cost'); ?></th>
                                    <th><?= lang('useful_life'); ?></th>
                                    <th><?= lang('method'); ?></th>
                                    <th><?= lang('depreciation_amount'); ?></th>
                                    <th><?= lang('note'); ?></th>
                                    <th><?= lang('created_by'); ?></th>
                                    <th style="width:100px; text-align:center;"><?= lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="active">
                                    <th></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('asset_name'); ?>]"></th>
                                    <th><input type="text" class="text_filter datepicker" placeholder="[<?= lang('purchase_date'); ?>]"></th>
                                    <th><?= lang('purchase_cost'); ?></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('useful_life'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('method'); ?>]"></th>
                                    <th><?= lang('depreciation_amount'); ?></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('note'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('created_by'); ?>]"></th>
                                    <th style="text-align:center;"><?= lang('actions'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('.datepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        showClear: true,
        showClose: true,
        useCurrent: false,
        widgetPositioning: { horizontal: 'auto', vertical: 'bottom' },
        widgetParent: $('.dataTable tfoot')
    });
});
</script>
