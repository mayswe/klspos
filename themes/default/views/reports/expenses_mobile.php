<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
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

$expenses_report_form_action =
    site_url('reports/expenses') . $app_query;

$expenses_report_reset_url =
    site_url('reports/expenses') . $app_query;
?>


<?php
$v = "?v=1";

if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
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
    #ExRData thead tr {
        background: #eef5fb;
    }
    #ExRData thead th {
        vertical-align: middle;
        white-space: nowrap;
        color: #2c3b41;
        border-bottom: 2px solid #dbe7f0;
    }
    #ExRData tbody td {
        vertical-align: middle;
    }
    #ExRData tfoot tr.active th {
        background: #f7fafc;
        vertical-align: middle;
    }
    #ExRData tfoot input.text_filter,
    #ExRData tfoot select.select_filter {
        width: 100%;
        height: 30px;
        padding: 3px 6px;
        border: 1px solid #d2d6de;
        border-radius: 4px;
        font-size: 12px;
        font-weight: normal;
    }
    #ExRData tfoot select.select_filter {
        background: #fff;
    }
    .erp-text-right {
        text-align: right;
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

.content.erp-expenses-page {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.erp-expenses-page > .row {
    width: 100% !important;
    margin: 0 !important;
}

.erp-expenses-page > .row > .col-xs-12 {
    width: 100% !important;
    padding: 0 !important;
    float: none !important;
}

.erp-expenses-page .box.box-primary {
    width: 100% !important;
    margin: 0 !important;
    overflow: hidden !important;
    border: 1px solid #e3e9ef !important;
    border-top: 1px solid #e3e9ef !important;
    border-radius: 12px !important;
    background: #fff !important;
    box-shadow: none !important;
}

.erp-expenses-page .box-header.with-border {
    padding: 0 !important;
}

.erp-expenses-page .erp-page-header {
    display: block !important;
    padding: 13px !important;
}

.erp-expenses-page .erp-title-text h4 {
    font-size: 17px !important;
    line-height: 1.45 !important;
}

.erp-expenses-page .erp-title-text small {
    font-size: 13px !important;
}

.erp-expenses-page .toggle_form {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    width: 100% !important;
    min-height: 43px !important;
    margin-top: 11px !important;
    font-size: 14px !important;
}

.erp-expenses-page .box-body {
    padding: 10px !important;
    background: #f4f7fb !important;
}

.erp-expenses-page .erp-filter-panel .row {
    margin: 0 !important;
}

.erp-expenses-page .erp-filter-panel .col-sm-3 {
    width: 100% !important;
    padding: 0 !important;
    float: none !important;
}

.erp-expenses-page .erp-filter-panel label {
    font-size: 13px !important;
}

.erp-expenses-page .erp-filter-panel .form-control,
.erp-expenses-page .erp-filter-panel .select2-container,
.erp-expenses-page .erp-filter-panel .select2-container .select2-choice,
.erp-expenses-page .erp-filter-panel
.select2-container--default
.select2-selection--single {
    width: 100% !important;
    min-height: 42px !important;
    font-size: 14px !important;
}

.erp-expenses-page .erp-filter-actions {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
}

.erp-expenses-page .erp-filter-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    min-height: 42px !important;
    margin: 0 !important;
}

.erp-expenses-page .erp-summary-row {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    grid-auto-flow: row !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
}

.erp-expenses-page .erp-summary-row::before,
.erp-expenses-page .erp-summary-row::after {
    display: none !important;
    content: none !important;
}

.erp-expenses-page .erp-summary-row > [class*="col-"] {
    width: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}

.erp-expenses-page .erp-summary-card {
    width: 100% !important;
    min-height: 86px !important;
    margin: 0 !important;
    padding: 12px !important;
}

.erp-expenses-page .erp-summary-label {
    font-size: 13px !important;
    text-transform: none !important;
}

.erp-expenses-page .erp-summary-value {
    font-size: 18px !important;
}

.erp-expenses-page .erp-table-toolbar {
    display: block !important;
    padding: 11px !important;
}

.erp-expenses-page .erp-table-toolbar h4 {
    margin-bottom: 10px !important;
    font-size: 15px !important;
}

.erp-expenses-page #expense_export_buttons,
.erp-expenses-page .dataTables_length,
.erp-expenses-page .dt-buttons {
    display: none !important;
}

.erp-expenses-page .erp-table-toolbar .input-group {
    width: 100% !important;
    max-width: none !important;
}

.erp-expenses-page #search_table {
    height: 42px !important;
    font-size: 14px !important;
}

.erp-expenses-page .erp-table-wrap {
    padding: 8px !important;
}

.erp-expenses-page .table-responsive {
    width: 100% !important;
    margin: 0 !important;
    overflow-x: auto !important;
    border: 0 !important;
    -webkit-overflow-scrolling: touch;
}

.erp-expenses-page #ExRData {
    width: 100% !important;
    min-width: 850px !important;
}

.erp-expenses-page #ExRData thead th,
.erp-expenses-page #ExRData tbody td,
.erp-expenses-page #ExRData tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
    white-space: nowrap !important;
}

.bootstrap-datetimepicker-widget,
.select2-drop,
.select2-dropdown,
.select2-container--open {
    z-index: 999999 !important;
}

@media (max-width: 420px) {
    .erp-expenses-page .erp-summary-row,
    .erp-expenses-page .erp-filter-actions {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script type="text/javascript">
    function formatQty(data, type) {
        var num = parseFloat(data || 0);

        if (type === 'display') {
            return (num % 1 === 0) ? num : num.toFixed(2);
        }

        return num;
    }

    $(document).ready(function() {

        function updateSummary(api) {
            var records = api.rows({ search: 'applied' }).data().length;

            var amount = api.column(4, { search: 'applied' }).data().reduce(function (a, b) {
                return pf(a) + pf(b);
            }, 0);

            var quantity = api.column(5, { search: 'applied' }).data().reduce(function (a, b) {
                return pf(a) + pf(b);
            }, 0);

            var total = api.column(6, { search: 'applied' }).data().reduce(function (a, b) {
                return pf(a) + pf(b);
            }, 0);

            $('#expense_records').html(records);
            $('#expense_amount').html(cf(amount));
            $('#expense_quantity').html(formatQty(quantity, 'display'));
            $('#expense_total').html(cf(total));
        }

        var table = $('#ExRData').DataTable({
            ajax: {
                url: '<?=site_url('reports/get_expenses/'.$v);?>',
                type: 'POST',
                data: function (d) {
                    d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
                }
            },
            buttons: [
                { extend: 'copyHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6] } },
                { extend: 'excelHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6] } },
                { extend: 'csvHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6] } },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6] } },
                { extend: 'colvis', text: '<?= lang('columns'); ?>' }
            ],
            columns: [
                { data: "id", visible: false },
                {
                    data: "date",
                    render: function(data, type) {
                        if (!data) {
                            return '';
                        }
                        return type === 'display' ? data.substr(0, 10) : data;
                    }
                },
                { data: "category" },
                { data: "reference" },
                { data: "amount", render: currencyFormat, className: "erp-text-right" },
                { data: "quantity", render: formatQty, className: "erp-text-right" },
                { data: "total", render: currencyFormat, className: "erp-text-right" }
            ],
            footerCallback: function (tfoot, data, start, end, display) {
                var api = this.api();

                var amount = api.column(4, { search: 'applied' }).data().reduce(function (a, b) {
                    return pf(a) + pf(b);
                }, 0);
                $(api.column(4).footer()).html(cf(amount));

                var quantity = api.column(5, { search: 'applied' }).data().reduce(function (a, b) {
                    return pf(a) + pf(b);
                }, 0);
                $(api.column(5).footer()).html(formatQty(quantity, 'display'));

                var total = api.column(6, { search: 'applied' }).data().reduce(function (a, b) {
                    return pf(a) + pf(b);
                }, 0);
                $(api.column(6).footer()).html(cf(total));

                updateSummary(api);
            }
        });

        if (table.buttons) {
            table.buttons().container().appendTo('#expense_export_buttons');
        }

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

            $('select.select_filter', this.footer()).on('change', function() {
                self.search(this.value).draw();
            });
        });

        $('#form').hide();
        $('.toggle_form').click(function(){
            $('#form').slideToggle();
            return false;
        });
    });
</script>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content erp-expenses-page kls-mobile-ui">
    <div class="row">
        <div class="col-xs-12">

            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="erp-page-header">
                        <div class="erp-title-wrap">
                            <div class="erp-title-icon">
                                <i class="fa fa-money"></i>
                            </div>
                            <div class="erp-title-text">
                                <h4><?= $page_title; ?></h4>
                                <small><?= lang('expenses'); ?> <?= lang('report'); ?></small>
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
                            <?= form_open($expenses_report_form_action); ?>

                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label" for="category"><?= lang("categories"); ?></label>
                                        <?php
                                        $pr[0] = lang("select") . " " . lang("categories");
                                        foreach ($categories as $category) {
                                            $pr[$category->id] = $category->name;
                                        }
                                        echo form_dropdown('category', $pr, set_value('category'), 'class="form-control select2" style="width:100%" id="category"');
                                        ?>
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

                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="erp-filter-actions">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-search"></i>
                                                <?= lang("submit"); ?>
                                            </button>
                                            <a
                                                href="<?= html_escape($expenses_report_reset_url); ?>"
                                                class="btn btn-default"
                                            >
                                                <i class="fa fa-refresh"></i>
                                                <?= lang('reset'); ?>
                                            </a>
                                        </div>
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
                                <div class="erp-summary-value" id="expense_records">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="erp-summary-card erp-card-orange">
                                <div class="erp-summary-label"><?= lang('amount'); ?></div>
                                <div class="erp-summary-value" id="expense_amount">0.00</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="erp-summary-card erp-card-green">
                                <div class="erp-summary-label"><?= lang('quantity'); ?></div>
                                <div class="erp-summary-value" id="expense_quantity">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="erp-summary-card erp-card-red">
                                <div class="erp-summary-label"><?= lang('total'); ?></div>
                                <div class="erp-summary-value" id="expense_total">0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-table-card">
                        <div class="erp-table-toolbar">
                            <h4><i class="fa fa-list"></i> <?= lang('expenses'); ?> <?= lang('list'); ?></h4>

                            <div class="pull-right">
                                <div id="expense_export_buttons" class="btn-group"></div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>">
                            </div>
                        </div>

                        <div class="erp-table-wrap">
                            <div class="table-responsive">
                                <table id="ExRData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                                    <thead>
                                        <tr>
                                            <th><?= lang("id"); ?></th>
                                            <th><?= lang("date"); ?></th>
                                            <th><?= lang("category"); ?></th>
                                            <th><?= lang("reference"); ?></th>
                                            <th><?= lang("amount"); ?></th>
                                            <th><?= lang("quantity"); ?></th>
                                            <th><?= lang("total"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="active">
                                            <th></th>
                                            <th>
                                                <span class="datepickercon">
                                                    <input type="text" class="text_filter datepicker" placeholder="[<?= lang('date'); ?>]">
                                                </span>
                                            </th>
                                            <th>
                                                <select class="select_filter">
                                                    <option value=""><?= lang("all"); ?></option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?= $category->name; ?>"><?= $category->name; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </th>
                                            <th>
                                                <input type="text" class="text_filter" placeholder="[<?= lang('reference'); ?>]">
                                            </th>
                                            <th class="erp-text-right"><?= lang('amount'); ?></th>
                                            <th class="erp-text-right"><?= lang('quantity'); ?></th>
                                            <th class="erp-text-right"><?= lang('total'); ?></th>
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
