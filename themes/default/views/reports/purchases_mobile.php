<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Purchases Report mobile/app mode.
 *
 * app=1 and app_lang are preserved after Filter Submit and Reset,
 * so the report stays in the mobile view.
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

$purchases_report_form_action =
    site_url('reports/purchases') . $app_query;

$purchases_report_reset_url =
    site_url('reports/purchases') . $app_query;
?>


<?php
$v = "?v=1";

if ($this->input->post('supplier')) {
    $v .= "&supplier=" . $this->input->post('supplier');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('status')) {
    $v .= "&status=" . $this->input->post('status');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

// Monday of current week
$start_date = date('Y-m-d 00:00:00', strtotime('monday this week'));

// Saturday of current week
$end_date = date('Y-m-d 23:59:59', strtotime('saturday this week'));
?>

<style type="text/css">
    .erp-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .erp-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .erp-title-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #3c8dbc;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .erp-title-text h4 {
        margin: 0;
        font-weight: 700;
        color: #2c3b41;
    }
    .erp-title-text small {
        color: #777;
    }
    .erp-filter-panel {
        border: 1px solid #e7edf3;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        overflow: hidden;
        margin-bottom: 15px;
    }
    .erp-filter-panel .panel-heading {
        background: #f7fafc;
        border-bottom: 1px solid #e7edf3;
        font-weight: 700;
        color: #2c3b41;
    }
    .erp-summary-row {
        margin-bottom: 15px;
    }
    .erp-summary-card {
        border-radius: 10px;
        padding: 14px 16px;
        color: #fff;
        min-height: 86px;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
        position: relative;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .erp-summary-card:after {
        content: "";
        position: absolute;
        right: -22px;
        bottom: -22px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: rgba(255,255,255,.16);
    }
    .erp-summary-card .erp-summary-label {
        opacity: .9;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .erp-summary-card .erp-summary-value {
        font-size: 22px;
        font-weight: 800;
        margin-top: 8px;
        line-height: 1.1;
        word-break: break-word;
    }
    .erp-card-blue { background: linear-gradient(135deg, #3c8dbc, #2f6f9f); }
    .erp-card-green { background: linear-gradient(135deg, #00a65a, #008d4c); }
    .erp-card-orange { background: linear-gradient(135deg, #f39c12, #d58512); }
    .erp-card-red { background: linear-gradient(135deg, #dd4b39, #b13b2e); }
    .erp-table-card {
        border: 1px solid #e7edf3;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }
    .erp-table-toolbar {
        padding: 12px 15px;
        background: #f7fafc;
        border-bottom: 1px solid #e7edf3;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .erp-table-toolbar h4 {
        margin: 0;
        font-weight: 700;
        color: #2c3b41;
    }
    .erp-table-toolbar .input-group {
        max-width: 360px;
    }
    .erp-table-wrap {
        padding: 12px;
    }
    #SLRData thead tr {
        background: #eef5fb;
    }
    #SLRData thead th {
        vertical-align: middle;
        white-space: nowrap;
        color: #2c3b41;
        border-bottom: 2px solid #dbe7f0;
    }
    #SLRData tbody td {
        vertical-align: middle;
    }
    #SLRData tfoot tr.active th {
        background: #f7fafc;
        vertical-align: middle;
    }
    #SLRData tfoot input.text_filter,
    #SLRData tfoot select.select_filter {
        width: 100%;
        height: 30px;
        padding: 3px 6px;
        border: 1px solid #d2d6de;
        border-radius: 4px;
        font-size: 12px;
        font-weight: normal;
    }
    #SLRData tfoot select.select_filter {
        background: #fff;
    }
    .erp-actions {
        text-align: center;
        white-space: nowrap;
    }
    .erp-actions .btn,
    .erp-actions a {
        margin: 1px;
    }
    .erp-checkbox-col {
        width: 36px;
        text-align: center;
    }
    .erp-text-right {
        text-align: right;
    }
    .purchase_status.label,
    .sale_status.label {
        display: inline-block;
        min-width: 60px;
        padding: 5px 8px;
        border-radius: 12px;
        font-size: 11px;
    }
    @media (max-width: 767px) {
        .erp-page-header,
        .erp-table-toolbar {
            align-items: stretch;
        }
        .erp-table-toolbar .input-group {
            max-width: none;
            width: 100%;
        }
    }
</style>

<?php if ($is_app_mode) { ?>
<style>
/* =========================================================
   KLSPOS Purchases Report - True Mobile/App Layout
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

/* Full-width report page */
.content.erp-purchases-page {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.erp-purchases-page > .row {
    width: 100% !important;
    margin: 0 !important;
}

.erp-purchases-page > .row > .col-sm-12 {
    width: 100% !important;
    padding: 0 !important;
    float: none !important;
}

.erp-purchases-page .box.box-primary {
    width: 100% !important;
    margin: 0 !important;
    overflow: hidden !important;
    border: 1px solid #e3e9ef !important;
    border-top: 1px solid #e3e9ef !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    box-shadow: none !important;
}

/* Header */
.erp-purchases-page .box-header.with-border {
    padding: 0 !important;
    border-bottom: 1px solid #e8eef5 !important;
}

.erp-purchases-page .erp-page-header {
    display: block !important;
    padding: 13px !important;
}

.erp-purchases-page .erp-title-wrap {
    width: 100% !important;
}

.erp-purchases-page .erp-title-text h4 {
    font-size: 17px !important;
    line-height: 1.45 !important;
}

.erp-purchases-page .erp-title-text small {
    font-size: 13px !important;
}

.erp-purchases-page .toggle_form {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    width: 100% !important;
    min-height: 43px !important;
    margin-top: 11px !important;
    padding: 9px 12px !important;
    border-radius: 9px !important;
    font-size: 14px !important;
}

/* Body */
.erp-purchases-page .box-body {
    padding: 10px !important;
    background: #f4f7fb !important;
}

/* Filter panel */
.erp-purchases-page .erp-filter-panel {
    margin-bottom: 12px !important;
    border-radius: 10px !important;
}

.erp-purchases-page .erp-filter-panel .panel-heading {
    padding: 12px !important;
    font-size: 15px !important;
}

.erp-purchases-page .erp-filter-panel .panel-body {
    padding: 12px !important;
}

.erp-purchases-page .erp-filter-panel .row {
    margin-right: 0 !important;
    margin-left: 0 !important;
}

.erp-purchases-page .erp-filter-panel .col-sm-3,
.erp-purchases-page .erp-filter-panel .col-sm-12 {
    width: 100% !important;
    padding-right: 0 !important;
    padding-left: 0 !important;
    float: none !important;
}

.erp-purchases-page .erp-filter-panel label {
    font-size: 13px !important;
}

.erp-purchases-page .erp-filter-panel .form-control,
.erp-purchases-page .erp-filter-panel .select2-container .select2-choice,
.erp-purchases-page .erp-filter-panel
.select2-container--default
.select2-selection--single {
    width: 100% !important;
    height: 42px !important;
    min-height: 42px !important;
    font-size: 14px !important;
}

.erp-purchases-page .erp-filter-panel .select2-container {
    width: 100% !important;
}

.erp-purchases-page .erp-filter-panel .select2-container .select2-choice,
.erp-purchases-page .erp-filter-panel
.select2-container .select2-choice > .select2-chosen,
.erp-purchases-page .erp-filter-panel
.select2-container--default
.select2-selection--single
.select2-selection__rendered {
    line-height: 40px !important;
}

.erp-purchases-page .erp-filter-actions {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
    padding-top: 4px !important;
}

.erp-purchases-page .erp-filter-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    min-height: 42px !important;
    margin: 0 !important;
    font-size: 14px !important;
}

/* Four summary cards = 2 x 2 */
.erp-purchases-page .erp-summary-row {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    grid-auto-flow: row !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
}

/*
 * Bootstrap .row creates ::before and ::after pseudo elements.
 * When .row becomes CSS Grid, those pseudo elements become extra
 * grid cells and push the first summary card to the second column.
 */
.erp-purchases-page .erp-summary-row::before,
.erp-purchases-page .erp-summary-row::after {
    display: none !important;
    content: none !important;
}

.erp-purchases-page .erp-summary-row > [class*="col-"] {
    width: auto !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}

.erp-purchases-page .erp-summary-card {
    width: 100% !important;
    min-height: 86px !important;
    margin: 0 !important;
    padding: 12px !important;
    border-radius: 10px !important;
}

.erp-purchases-page .erp-summary-label {
    font-size: 13px !important;
    line-height: 1.4 !important;
    text-transform: none !important;
}

.erp-purchases-page .erp-summary-value {
    margin-top: 6px !important;
    font-size: 18px !important;
    line-height: 1.35 !important;
    overflow-wrap: anywhere !important;
}

/* Table card and toolbar */
.erp-purchases-page .erp-table-card {
    border-radius: 10px !important;
}

.erp-purchases-page .erp-table-toolbar {
    display: block !important;
    padding: 11px !important;
}

.erp-purchases-page .erp-table-toolbar h4 {
    margin-bottom: 10px !important;
    font-size: 15px !important;
}

.erp-purchases-page #purchase_export_buttons {
    display: none !important;
}

.erp-purchases-page .erp-table-toolbar .input-group {
    width: 100% !important;
    max-width: none !important;
}

.erp-purchases-page #search_table {
    height: 42px !important;
    font-size: 14px !important;
}

.erp-purchases-page .erp-table-wrap {
    padding: 8px !important;
}

.erp-purchases-page .table-responsive {
    width: 100% !important;
    margin: 0 !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    border: 0 !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.erp-purchases-page #SLRData {
    width: 100% !important;
    min-width: 1050px !important;
}

.erp-purchases-page #SLRData thead th,
.erp-purchases-page #SLRData tbody td,
.erp-purchases-page #SLRData tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
    white-space: nowrap !important;
}

.erp-purchases-page .dataTables_length,
.erp-purchases-page .dt-buttons {
    display: none !important;
}

.erp-purchases-page .dataTables_info {
    font-size: 12px !important;
}

.erp-purchases-page .pagination > li > a,
.erp-purchases-page .pagination > li > span {
    padding: 7px 10px !important;
    font-size: 12px !important;
}

/* Popups above app content */
.bootstrap-datetimepicker-widget,
.select2-drop,
.select2-dropdown,
.select2-container--open {
    z-index: 999999 !important;
}

@media (max-width: 420px) {
    .erp-purchases-page .erp-summary-row,
    .erp-purchases-page .erp-filter-actions {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script type="text/javascript">
    $(document).ready(function() {

        function status(x, type) {
            var paid = '<?= lang('paid'); ?>';
            var partial = '<?= lang('partial'); ?>';
            var due = '<?= lang('due'); ?>';

            if (x == 'paid') {
                return type === 'display' ? '<div class="text-center"><span class="purchase_status label label-success">' + paid + '</span></div>' : paid;
            } else if (x == 'partial') {
                return type === 'display' ? '<div class="text-center"><span class="purchase_status label label-primary">' + partial + '</span></div>' : partial;
            } else if (x == 'due') {
                return type === 'display' ? '<div class="text-center"><span class="purchase_status label label-danger">' + due + '</span></div>' : due;
            } else {
                return type === 'display' ? '<div class="text-center"><span class="purchase_status label label-default">' + x + '</span></div>' : x;
            }
        }

        function received(x, type) {
            var yes = '<?= lang('Yes'); ?>';
            var no  = '<?= lang('No'); ?>';
            var text = (x == '1') ? yes : no;

            if (type !== 'display') {
                return text;
            }

            return '<div class="text-center"><span class="purchase_status label label-' + (x == '1' ? 'success' : 'danger') + '">' + text + '</span></div>';
        }

        function updateSummary(api) {
            var records = api.rows({ search: 'applied' }).data().length;

            var total = api.column(4, { search: 'applied' }).data().reduce(function (a, b) {
                return pf(a) + pf(b);
            }, 0);

            var paid = api.column(5, { search: 'applied' }).data().reduce(function (a, b) {
                return pf(a) + pf(b);
            }, 0);

            var due = api.column(6, { search: 'applied' }).data().reduce(function (a, b) {
                return pf(a) + pf(b);
            }, 0);

            $('#purchase_records').html(records);
            $('#purchase_total').html(cf(total));
            $('#purchase_paid').html(cf(paid));
            $('#purchase_due').html(cf(due));
        }

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'SLRData') {
                return true;
            }

            var rowData = settings.aoData[dataIndex] ? settings.aoData[dataIndex]._aData : null;
            if (!rowData) {
                return true;
            }

            var supplierFilter = ($('#footer_supplier_filter').val() || '').toLowerCase();
            var statusFilter   = ($('#footer_status_filter').val() || '').toLowerCase();
            var receivedFilter = ($('#footer_received_filter').val() || '');

            if (supplierFilter !== '') {
                var supplierName = (rowData.name || '').toLowerCase();
                if (supplierName.indexOf(supplierFilter) === -1) {
                    return false;
                }
            }

            if (statusFilter !== '') {
                var rowStatus = (rowData.status || '').toLowerCase();

                if (statusFilter === 'notpaid') {
                    if (rowStatus !== 'partial' && rowStatus !== 'due') {
                        return false;
                    }
                } else if (rowStatus !== statusFilter) {
                    return false;
                }
            }

            if (receivedFilter !== '') {
                if ((rowData.received + '') !== receivedFilter) {
                    return false;
                }
            }

            return true;
        });

        var table = $('#SLRData').DataTable({
            'ajax': {
                url: '<?=site_url('reports/get_purchase/'. $v);?>',
                type: 'POST',
                "data": function (d) {
                    d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
                }
            },
            "buttons": [
                { extend: 'copyHtml5', 'footer': true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'excelHtml5', 'footer': true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'csvHtml5', 'footer': true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', 'footer': true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] } },
                { extend: 'colvis', text: '<?= lang('columns'); ?>' }
            ],
            "columns": [
                {
                    data: "id",
                    orderable: false,
                    searchable: false,
                    className: "erp-checkbox-col",
                    render: function(data) {
                        return '<input type="checkbox" class="purchase_check" value="' + data + '">';
                    }
                },
                { data: "id" },
                {
                    data: "date",
                    render: function(data, type) {
                        if (!data) {
                            return '';
                        }
                        return type === 'display' ? data.substr(0, 10) : data;
                    }
                },
                { data: "name" },
                { data: "total", render: currencyFormat, className: "erp-text-right" },
                { data: "paid", render: currencyFormat, className: "erp-text-right" },
                { data: "balance", orderable: false, searchable: false, render: currencyFormat, className: "erp-text-right" },
                { data: "status", render: status },
                { data: "received", render: received },
                {
                    data: "Actions",
                    orderable: false,
                    searchable: false,
                    className: "erp-actions",
                    render: function(data) {
                        return '<div class="erp-actions">' + (data ? data : '') + '</div>';
                    }
                }
            ],
            "footerCallback": function (tfoot, data, start, end, display) {
                var api = this.api();

                var total = api.column(4, { search: 'applied' }).data().reduce(function (a, b) {
                    return pf(a) + pf(b);
                }, 0);
                $(api.column(4).footer()).html(cf(total));

                var paid = api.column(5, { search: 'applied' }).data().reduce(function (a, b) {
                    return pf(a) + pf(b);
                }, 0);
                $(api.column(5).footer()).html(cf(paid));

                var due = api.column(6, { search: 'applied' }).data().reduce(function (a, b) {
                    return pf(a) + pf(b);
                }, 0);
                $(api.column(6).footer()).html(cf(due));

                updateSummary(api);
            }
        });

        if (table.buttons) {
            table.buttons().container().appendTo('#purchase_export_buttons');
        }

        $('#checkAll').on('change', function() {
            $('.purchase_check').prop('checked', this.checked);
        });

        $('#SLRData').on('draw.dt', function() {
            $('#checkAll').prop('checked', false);
        });

        $('#search_table').on('keyup change', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search(this.value).draw();
            }
        });

        table.columns().every(function () {
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
        });

        $('.erp-special-filter').on('change', function() {
            table.draw();
        });

        $('#form').hide();
        $('.toggle_form').click(function(){
            $("#form").slideToggle();
            return false;
        });
    });
</script>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content erp-purchases-page kls-mobile-ui">
    <div class="row">
        <div class="col-sm-12">

            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="erp-page-header">
                        <div class="erp-title-wrap">
                            <div class="erp-title-icon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <div class="erp-title-text">
                                <h4><?= $page_title; ?></h4>
                                <small><?= lang('purchases'); ?> <?= lang('report'); ?></small>
                            </div>
                        </div>

                        <a href="#" class="btn btn-default btn-sm toggle_form">
                            <i class="fa fa-filter"></i> <?= lang("show_hide"); ?>
                        </a>
                    </div>
                </div>

                <div class="box-body">

                    <div id="form" class="panel erp-filter-panel">
                        <div class="panel-heading">
                            <i class="fa fa-filter"></i> <?= lang('filter'); ?>
                        </div>
                        <div class="panel-body">
                            <?= form_open($purchases_report_form_action); ?>

                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label" for="supplier"><?= lang("supplier"); ?></label>
                                        <?php
                                        $cu[0] = lang("select") . " " . lang("supplier");
                                        foreach ($suppliers as $supplier) {
                                            $cu[$supplier->id] = $supplier->name;
                                        }
                                        echo form_dropdown('supplier', $cu, set_value('supplier'), 'class="form-control select2" style="width:100%" id="supplier"');
                                        ?>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label" for="status"><?= lang("status"); ?></label>
                                        <select name="status" class="form-control select2" style="width:100%" id="status">
                                            <option value="" <?= set_select('status', '', TRUE); ?>><?= lang('all'); ?></option>
                                            <option value="paid" <?= set_select('status', 'paid'); ?>><?= lang('paid'); ?></option>
                                            <option value="partial" <?= set_select('status', 'partial'); ?>><?= lang('partial'); ?></option>
                                            <option value="due" <?= set_select('status', 'due'); ?>><?= lang('due'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="start_date"><?= lang("start_date"); ?></label>
                                        <?= form_input(
                                            'start_date',
                                            set_value('start_date', $start_date),
                                            'class="form-control datetimepicker" id="start_date"'
                                        ); ?>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="end_date"><?= lang("end_date"); ?></label>
                                        <?= form_input(
                                            'end_date',
                                            set_value('end_date', $end_date),
                                            'class="form-control datetimepicker" id="end_date"'
                                        ); ?>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="erp-filter-actions">
                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >
                                            <i class="fa fa-search"></i>
                                            <?= lang("submit"); ?>
                                        </button>

                                        <a
                                            href="<?= html_escape(
                                                $purchases_report_reset_url
                                            ); ?>"
                                            class="btn btn-default"
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

                    <div class="row erp-summary-row">
                        <div class="col-md-3 col-sm-6">
                            <div class="erp-summary-card erp-card-blue">
                                <div class="erp-summary-label"><?= lang('records'); ?></div>
                                <div class="erp-summary-value" id="purchase_records">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="erp-summary-card erp-card-green">
                                <div class="erp-summary-label"><?= lang('total'); ?></div>
                                <div class="erp-summary-value" id="purchase_total">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="erp-summary-card erp-card-orange">
                                <div class="erp-summary-label"><?= lang('paid'); ?></div>
                                <div class="erp-summary-value" id="purchase_paid">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="erp-summary-card erp-card-red">
                                <div class="erp-summary-label"><?= lang('due'); ?></div>
                                <div class="erp-summary-value" id="purchase_due">0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-table-card">
                        <div class="erp-table-toolbar">
                            <h4><i class="fa fa-list"></i> <?= lang('purchases'); ?> <?= lang('list'); ?></h4>

                            <div class="pull-right">
                                <div id="purchase_export_buttons" class="btn-group"></div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>">
                            </div>
                        </div>

                        <div class="erp-table-wrap">
                            <div class="table-responsive">
                                <table id="SLRData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                                    <thead>
                                        <tr>
                                            <th class="erp-checkbox-col">
                                                <input type="checkbox" id="checkAll">
                                            </th>
                                            <th><?= lang("id"); ?></th>
                                            <th><?= lang('date'); ?></th>
                                            <th><?= lang('name'); ?></th>
                                            <th><?= lang('total'); ?></th>
                                            <th><?= lang('paid'); ?></th>
                                            <th><?= lang('due'); ?></th>
                                            <th><?= lang('status'); ?></th>
                                            <th><?= lang('Receive'); ?></th>
                                            <th style="width:90px; text-align:center;"><?= lang('actions'); ?></th>
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
                                            <th style="max-width:50px;">
                                                <input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]">
                                            </th>
                                            <th>
                                                <span class="datepickercon">
                                                    <input type="text" class="text_filter datepicker" placeholder="[<?= lang('date'); ?>]">
                                                </span>
                                            </th>
                                            <th>
                                                <select class="select_filter erp-special-filter" id="footer_supplier_filter" data-column="3">
                                                    <option value=""><?= lang("all"); ?></option>
                                                    <?php foreach ($suppliers as $cs): ?>
                                                        <option value="<?= $cs->name; ?>"><?= $cs->name; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </th>
                                            <th class="erp-text-right"><?= lang('total'); ?></th>
                                            <th class="erp-text-right"><?= lang('paid'); ?></th>
                                            <th class="erp-text-right"><?= lang('due'); ?></th>
                                            <th>
                                                <select class="select_filter erp-special-filter" id="footer_status_filter" data-column="7">
                                                    <option value=""><?= lang("all"); ?></option>
                                                    <option value="paid"><?= lang("paid"); ?></option>
                                                    <option value="partial"><?= lang("partial"); ?></option>
                                                    <option value="due"><?= lang("due"); ?></option>
                                                    <option value="notpaid">Partial &amp; Due</option>
                                                </select>
                                            </th>
                                            <th>
                                                <select class="select_filter erp-special-filter" id="footer_received_filter" data-column="8">
                                                    <option value=""><?= lang("all"); ?></option>
                                                    <option value="1"><?= lang("Yes"); ?></option>
                                                    <option value="0"><?= lang("No"); ?></option>
                                                </select>
                                            </th>
                                            <th style="width:90px; text-align:center;"><?= lang('actions'); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
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
    $(function () {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });

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