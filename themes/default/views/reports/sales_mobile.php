<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Sales Report mobile/app mode.
 *
 * Preserve app=1 and app_lang when the report filter form is submitted
 * or reset, so the page does not switch back to the desktop view.
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

$sales_report_form_action =
    site_url('reports') . $app_query;

$sales_report_reset_url =
    site_url('reports') . $app_query;
?>


<?php
$v = "?v=1";

if ($this->input->post('customer')) {
    $v .= "&customer=" . urlencode($this->input->post('customer'));
}
if ($this->input->post('user')) {
    $v .= "&user=" . urlencode($this->input->post('user'));
}
if ($this->input->post('status')) {
    $v .= "&status=" . urlencode($this->input->post('status'));
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . urlencode($this->input->post('start_date'));
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . urlencode($this->input->post('end_date'));
}

$start_date = date('Y-m-d 00:00', strtotime('monday this week'));
$end_date   = date('Y-m-d 23:59', strtotime('saturday this week'));
?>

<style>
    .erp-page .erp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 14px 16px;
        border-bottom: 1px solid #edf1f5;
        background: #fff;
    }
    .erp-page .erp-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .erp-page .erp-title-icon {
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
    .erp-page .erp-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #273444;
        line-height: 1.25;
    }
    .erp-page .erp-subtitle {
        margin-top: 3px;
        color: #7b8794;
        font-size: 12px;
    }
    .erp-page .erp-filter-toggle {
        border-radius: 20px;
        padding: 7px 14px;
        font-weight: 600;
    }
    .erp-page .erp-body {
        background: #f6f8fb;
        padding: 15px;
    }
    .erp-page .erp-filter-card,
    .erp-page .erp-table-card,
    .erp-page .erp-summary-card {
        background: #fff;
        border: 1px solid #edf1f5;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(36, 52, 71, .05);
        margin-bottom: 15px;
    }
    .erp-page .erp-filter-card {
        padding: 15px 15px 5px;
        border-left: 4px solid #2f80ed;
    }
    .erp-page .erp-filter-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e6ebf1;
    }
    .erp-page .erp-filter-title h5 {
        margin: 0;
        font-weight: 700;
        color: #273444;
    }
    .erp-page .erp-filter-card label {
        font-size: 12px;
        color: #5d6d7e;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .erp-page .erp-filter-card .form-control,
    .erp-page .erp-filter-card .select2-container .select2-choice,
    .erp-page .erp-filter-card .select2-container .select2-selection {
        min-height: 38px;
        border-radius: 6px !important;
        border-color: #dfe6ee;
        box-shadow: none;
    }
    .erp-page .erp-filter-actions {
        padding-top: 22px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .erp-page .erp-filter-actions .btn {
        border-radius: 6px;
        font-weight: 600;
        padding: 8px 14px;
    }
    .erp-page .erp-stat-grid {
        display: flex;
        flex-wrap: wrap;
        margin-left: -7px;
        margin-right: -7px;
    }
    .erp-page .erp-stat-item {
        padding-left: 7px;
        padding-right: 7px;
        margin-bottom: 14px;
    }
    .erp-page .erp-stat-card {
        padding: 14px 15px;
        min-height: 86px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        overflow: hidden;
        position: relative;
    }
    .erp-page .erp-stat-card:after {
        content: '';
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: rgba(47, 128, 237, .08);
        position: absolute;
        right: -25px;
        bottom: -28px;
    }
    .erp-page .erp-stat-label {
        font-size: 12px;
        color: #7b8794;
        font-weight: 700;
        text-transform: uppercase;
    }
    .erp-page .erp-stat-value {
        margin-top: 6px;
        font-size: 19px;
        font-weight: 800;
        color: #273444;
    }
    .erp-page .erp-stat-icon {
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
    .erp-page .erp-table-card {
        padding: 12px;
    }
    .erp-page .erp-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .erp-page .erp-table-title {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: #273444;
    }
    .erp-page #search_table {
        border-radius: 20px;
        border-color: #dfe6ee;
        height: 36px;
        min-width: 260px;
        box-shadow: none;
    }
    .erp-page table.dataTable thead th {
        background: #f1f5f9 !important;
        color: #334155;
        font-size: 12px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle !important;
        border-bottom: 1px solid #dfe6ee !important;
    }
    .erp-page table.dataTable tbody td {
        vertical-align: middle !important;
        color: #344054;
        white-space: nowrap;
    }
    .erp-page table.dataTable tfoot th {
        background: #fbfcfe !important;
        vertical-align: middle !important;
    }
    .erp-page .text_filter,
    .erp-page .select_filter {
        width: 100% !important;
        height: 30px;
        border: 1px solid #dfe6ee;
        border-radius: 5px;
        padding: 4px 7px;
        font-size: 12px;
        font-weight: normal;
    }
    .erp-page .sale_status {
        display: inline-block;
        min-width: 68px;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    .erp-page .erp-actions {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
    }
    .erp-page .erp-actions .btn,
    .erp-page .erp-actions a {
        margin: 1px;
        border-radius: 5px;
    }
    .erp-page .dt-buttons .btn,
    .erp-page .dt-buttons .dt-button {
        border-radius: 5px !important;
        margin-right: 4px;
        margin-bottom: 4px;
    }
    .erp-page .dataTables_length select {
        border-radius: 5px;
        border-color: #dfe6ee;
        padding: 3px 6px;
    }
    .erp-page .erp-print-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }
    .erp-page .erp-print-actions .btn {
        border-radius: 6px;
        font-weight: 700;
    }
    @media (max-width: 767px) {
        .erp-page .erp-header,
        .erp-page .erp-table-toolbar {
            display: block;
        }
        .erp-page .erp-filter-toggle,
        .erp-page #search_table {
            width: 100%;
            margin-top: 10px;
        }
    }
</style>

<?php if ($is_app_mode) { ?>
<style>
/* =========================================================
   KLSPOS Sales Report - True Mobile/App Layout
   Applied whenever ?app=1 is present, independent of viewport width.
   ========================================================= */

/* Remove the AdminLTE desktop shell */
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

/* Remove Bootstrap desktop gutters */
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

.erp-page > .row > .col-sm-12 {
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
    background: #ffffff !important;
    box-shadow: none !important;
}

/* Header */
.erp-page .erp-header {
    display: block !important;
    padding: 13px !important;
}

.erp-page .erp-title-wrap {
    width: 100% !important;
}

.erp-page .erp-title {
    font-size: 17px !important;
    line-height: 1.45 !important;
}

.erp-page .erp-subtitle {
    font-size: 13px !important;
}

.erp-page .erp-filter-toggle {
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
.erp-page .erp-body {
    padding: 10px !important;
}

/* Filter as a vertical mobile form */
.erp-page .erp-filter-card {
    padding: 13px !important;
    border-left-width: 3px !important;
}

.erp-page .erp-filter-title {
    display: block !important;
}

.erp-page .erp-filter-title h5 {
    margin-bottom: 7px !important;
    font-size: 15px !important;
}

.erp-page .erp-filter-title .text-muted {
    display: block !important;
    font-size: 12px !important;
    line-height: 1.55 !important;
    overflow-wrap: anywhere !important;
}

.erp-page .erp-filter-card .row {
    margin-right: 0 !important;
    margin-left: 0 !important;
}

.erp-page .erp-filter-card .col-md-3,
.erp-page .erp-filter-card .col-sm-6,
.erp-page .erp-filter-card .col-sm-12 {
    width: 100% !important;
    padding-right: 0 !important;
    padding-left: 0 !important;
    float: none !important;
}

.erp-page .erp-filter-card label {
    font-size: 13px !important;
    text-transform: none !important;
}

.erp-page .erp-filter-card .form-control,
.erp-page .erp-filter-card .select2-container .select2-choice,
.erp-page .erp-filter-card .select2-container--default .select2-selection--single {
    width: 100% !important;
    height: 42px !important;
    min-height: 42px !important;
    font-size: 14px !important;
}

.erp-page .erp-filter-card .select2-container {
    width: 100% !important;
}

.erp-page .erp-filter-card .select2-container .select2-choice,
.erp-page .erp-filter-card .select2-container .select2-choice > .select2-chosen,
.erp-page .erp-filter-card .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    line-height: 40px !important;
}

.erp-page .erp-filter-actions {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
    padding-top: 5px !important;
}

.erp-page .erp-filter-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    min-height: 42px !important;
    margin: 0 !important;
    font-size: 14px !important;
}

/* Summary cards: 2 x 2 in app mode */
.erp-page .erp-stat-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 13px !important;
}

.erp-page .erp-stat-item {
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
    border-radius: 10px !important;
}

.erp-page .erp-stat-label {
    font-size: 13px !important;
    line-height: 1.4 !important;
    text-transform: none !important;
}

.erp-page .erp-stat-value {
    margin-top: 5px !important;
    font-size: 18px !important;
    line-height: 1.35 !important;
    overflow-wrap: anywhere !important;
}

.erp-page .erp-stat-icon {
    width: 38px !important;
    height: 38px !important;
    flex: 0 0 38px !important;
}

/* Print buttons */
.erp-page .erp-print-actions {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
}

.erp-page .erp-print-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    min-height: 42px !important;
    margin: 0 !important;
    font-size: 14px !important;
}

/* Table card and toolbar */
.erp-page .erp-table-card {
    padding: 10px !important;
}

.erp-page .erp-table-toolbar {
    display: block !important;
}

.erp-page .erp-table-title {
    margin-bottom: 9px !important;
    font-size: 15px !important;
}

.erp-page #search_table {
    width: 100% !important;
    min-width: 0 !important;
    height: 42px !important;
    margin: 0 !important;
    border-radius: 8px !important;
    font-size: 14px !important;
}

/* Hide export/length controls in app mode */
.erp-page .erp-dt-top {
    display: none !important;
}

/* Keep the wide report table usable */
.erp-page .table-responsive {
    width: 100% !important;
    margin: 0 !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    border: 0 !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.erp-page #SLRData {
    width: 100% !important;
    min-width: 1080px !important;
}

.erp-page #SLRData thead th,
.erp-page #SLRData tbody td,
.erp-page #SLRData tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
}

.erp-page .dataTables_info {
    font-size: 12px !important;
}

.erp-page .pagination > li > a,
.erp-page .pagination > li > span {
    padding: 7px 10px !important;
    font-size: 12px !important;
}

/* Datetime picker / Select2 above the app content */
.bootstrap-datetimepicker-widget,
.select2-drop,
.select2-dropdown,
.select2-container--open {
    z-index: 999999 !important;
}

@media (max-width: 420px) {
    .erp-page .erp-stat-grid,
    .erp-page .erp-filter-actions,
    .erp-page .erp-print-actions {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script type="text/javascript">
    $(document).ready(function() {

        function status(x) {
            var paid    = '<?= lang('paid'); ?>';
            var partial = '<?= lang('partial'); ?>';
            var due     = '<?= lang('due'); ?>';
            var value   = (x || '').toString().toLowerCase();

            if (value == 'paid') {
                return '<div class="text-center"><span class="sale_status label label-success">' + paid + '</span></div>';
            } else if (value == 'partial') {
                return '<div class="text-center"><span class="sale_status label label-primary">' + partial + '</span></div>';
            } else if (value == 'due') {
                return '<div class="text-center"><span class="sale_status label label-danger">' + due + '</span></div>';
            } else {
                return '<div class="text-center"><span class="sale_status label label-default">' + x + '</span></div>';
            }
        }

        var table = $('#SLRData').DataTable({
            'ajax': {
                url: '<?= site_url('reports/get_sales/' . $v); ?>',
                type: 'POST',
                data: function(d) {
                    d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
                }
            },
            'dom': "<'row erp-dt-top'<'col-sm-6'B><'col-sm-6 text-right'l>>rt<'row erp-dt-bottom'<'col-sm-6'i><'col-sm-6'p>>",
            'pageLength': 25,
            'order': [[1, 'desc']],
            'autoWidth': false,
            'buttons': [
                { extend: 'copyHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9] } },
                { extend: 'excelHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9] } },
                { extend: 'csvHtml5', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9] } },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true, exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8, 9] } },
                { extend: 'colvis', text: '<i class="fa fa-columns"></i> Columns' }
            ],
            'columns': [
                { data: 'id', visible: false },
                { data: 'date', render: hrld },
                { data: 'customer_name' },
                { data: 'total', render: currencyFormat, className: 'text-right' },
                { data: 'total_tax', render: currencyFormat, className: 'text-right' },
                { data: 'total_discount', render: currencyFormat, className: 'text-right' },
                { data: 'grand_total', render: currencyFormat, className: 'text-right' },
                { data: 'paid', render: currencyFormat, className: 'text-right' },
                { data: 'balance', render: currencyFormat, className: 'text-right' },
                { data: 'status', render: status, className: 'text-center' },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-center action-col',
                    render: function(data, type, row) {
                        return '<div class="erp-actions">' + (data || '') + '</div>';
                    }
                }
            ],
            'footerCallback': function(tfoot, data, start, end, display) {
                var api = this.api();
                $(api.column(3).footer()).html(cf(api.column(3).data().reduce(function(a, b) { return pf(a) + pf(b); }, 0)));
                $(api.column(4).footer()).html(cf(api.column(4).data().reduce(function(a, b) { return pf(a) + pf(b); }, 0)));
                $(api.column(5).footer()).html(cf(api.column(5).data().reduce(function(a, b) { return pf(a) + pf(b); }, 0)));
                $(api.column(6).footer()).html(cf(api.column(6).data().reduce(function(a, b) { return pf(a) + pf(b); }, 0)));
                $(api.column(7).footer()).html(cf(api.column(7).data().reduce(function(a, b) { return pf(a) + pf(b); }, 0)));
                $(api.column(8).footer()).html(cf(api.column(8).data().reduce(function(a, b) { return pf(a) + pf(b); }, 0)));
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

            $('input.datepicker', this.footer()).on('dp.change', function(e) {
                self.search(this.value).draw();
            });

            $('input:not(.datepicker)', this.footer()).on('keyup change', function(e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                    self.search(this.value).draw();
                }
            });

            $('select.select_filter', this.footer()).on('change', function(e) {
                self.search(this.value).draw();
            });
        });

        $('#form').hide();
        $('.toggle_form').on('click', function() {
            $('#form').slideToggle(180);
            return false;
        });
    });
</script>

<section class="content erp-page">
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary" style="border-radius:10px; border-top:0; overflow:hidden;">
                <div class="erp-header">
                    <div class="erp-title-wrap">
                        <div class="erp-title-icon"><i class="fa fa-line-chart"></i></div>
                        <div>
                            <h4 class="erp-title"><?= $page_title; ?></h4>
                            <div class="erp-subtitle">
                                <?= lang('sales'); ?> <?= lang('report'); ?> / <?= lang('listing'); ?>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="btn btn-primary btn-sm toggle_form erp-filter-toggle">
                        <i class="fa fa-filter"></i> <?= lang('show_hide'); ?> <?= lang('filter'); ?>
                    </a>
                </div>

                <div class="erp-body">
                    <div id="form" class="erp-filter-card">
                        <?= form_open($sales_report_form_action, 'id="report_filter_form"'); ?>
                        <div class="erp-filter-title">
                            <h5><i class="fa fa-sliders"></i> <?= lang('filter'); ?></h5>
                            <span class="text-muted"><i class="fa fa-calendar"></i> <?= $start_date; ?> - <?= $end_date; ?></span>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="customer"><i class="fa fa-user"></i> <?= lang('customer'); ?></label>
                                    <?php
                                    $cu[0] = lang('select') . ' ' . lang('customer');
                                    foreach ($customers as $customer) {
                                        $cu[$customer->id] = $customer->name;
                                    }
                                    echo form_dropdown('customer', $cu, set_value('customer'), 'class="form-control select2" style="width:100%" id="customer"');
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="status"><i class="fa fa-check-circle"></i> <?= lang('status'); ?></label>
                                    <select name="status" class="form-control select2" style="width:100%" id="status">
                                        <option value="" <?= set_select('status', '', TRUE); ?>><?= lang('all'); ?></option>
                                        <option value="paid" <?= set_select('status', 'paid'); ?>><?= lang('paid'); ?></option>
                                        <option value="partial" <?= set_select('status', 'partial'); ?>><?= lang('partial'); ?></option>
                                        <option value="due" <?= set_select('status', 'due'); ?>><?= lang('due'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="start_date"><i class="fa fa-calendar-o"></i> <?= lang('start_date'); ?></label>
                                    <?= form_input('start_date', set_value('start_date', $start_date), 'class="form-control datetimepicker" id="start_date"'); ?>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="end_date"><i class="fa fa-calendar-check-o"></i> <?= lang('end_date'); ?></label>
                                    <?= form_input('end_date', set_value('end_date', $end_date), 'class="form-control datetimepicker" id="end_date"'); ?>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="erp-filter-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> <?= lang('submit'); ?>
                                    </button>
                                    <a href="<?= html_escape($sales_report_reset_url); ?>" class="btn btn-default">
                                        <i class="fa fa-refresh"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>

                    <?php if ($this->input->post('customer')) { ?>
                        <div class="erp-stat-grid">
                            <div class="col-md-3 col-sm-6 erp-stat-item">
                                <div class="erp-summary-card erp-stat-card">
                                    <div>
                                        <div class="erp-stat-label"><?= lang('sales'); ?></div>
                                        <div class="erp-stat-value"><?= $this->tec->formatMoney($total_sales->number, 0); ?></div>
                                    </div>
                                    <div class="erp-stat-icon"><i class="fa fa-shopping-cart"></i></div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 erp-stat-item">
                                <div class="erp-summary-card erp-stat-card">
                                    <div>
                                        <div class="erp-stat-label"><?= lang('amount'); ?></div>
                                        <div class="erp-stat-value"><?= $this->tec->formatMoney($total_sales->amount); ?></div>
                                    </div>
                                    <div class="erp-stat-icon"><i class="fa fa-money"></i></div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 erp-stat-item">
                                <div class="erp-summary-card erp-stat-card">
                                    <div>
                                        <div class="erp-stat-label"><?= lang('paid'); ?></div>
                                        <div class="erp-stat-value"><?= $this->tec->formatMoney($total_sales->paid); ?></div>
                                    </div>
                                    <div class="erp-stat-icon"><i class="fa fa-check"></i></div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 erp-stat-item">
                                <div class="erp-summary-card erp-stat-card">
                                    <div>
                                        <div class="erp-stat-label"><?= lang('due'); ?></div>
                                        <div class="erp-stat-value"><?= $this->tec->formatMoney($total_sales->amount - $total_sales->paid); ?></div>
                                    </div>
                                    <div class="erp-stat-icon"><i class="fa fa-warning"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="erp-print-actions">
                            <a href="<?= site_url('reports/dueprintall/' . $this->input->post('customer') . '/1'); ?>"
                               class="btn btn-primary tip"
                               title="<?= lang('view_invoice'); ?>"
                               data-toggle="ajax-modal">
                                <i class="fa fa-list"></i> <?= lang('print_detail'); ?>
                            </a>
                            <a href="<?= site_url('reports/dueprint/' . $this->input->post('customer') . '/1'); ?>"
                               class="btn btn-success tip"
                               title="<?= lang('view_invoice'); ?>"
                               data-toggle="ajax-modal">
                                <i class="fa fa-print"></i> <?= lang('print'); ?>
                            </a>
                        </div>
                    <?php } ?>

                    <div class="erp-table-card">
                        <div class="erp-table-toolbar">
                            <h5 class="erp-table-title"><i class="fa fa-table"></i> <?= lang('sales'); ?> <?= lang('list'); ?></h5>
                            <input type="text" class="form-control" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>">
                        </div>

                        <div class="table-responsive">
                            <table id="SLRData" class="table table-striped table-bordered table-condensed table-hover" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th style="max-width:30px;"><?= lang('id'); ?></th>
                                        <th><?= lang('date'); ?></th>
                                        <th><?= lang('customer'); ?></th>
                                        <th><?= lang('total'); ?></th>
                                        <th><?= lang('tax'); ?></th>
                                        <th><?= lang('discount'); ?></th>
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('paid'); ?></th>
                                        <th><?= lang('balance'); ?></th>
                                        <th><?= lang('status'); ?></th>
                                        <th><?= lang('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th style="max-width:30px;"><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
                                        <th><span class="datepickercon"><input type="text" class="text_filter datepicker" placeholder="[<?= lang('date'); ?>]"></span></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('customer'); ?>]"></th>
                                        <th class="text-right"><?= lang('total'); ?></th>
                                        <th class="text-right"><?= lang('tax'); ?></th>
                                        <th class="text-right"><?= lang('discount'); ?></th>
                                        <th class="text-right"><?= lang('grand_total'); ?></th>
                                        <th class="text-right"><?= lang('paid'); ?></th>
                                        <th class="text-right"><?= lang('balance'); ?></th>
                                        <th>
                                            <select class="form-control select_filter" style="width:100%;">
                                                <option value=""><?= lang('all'); ?></option>
                                                <option value="paid"><?= lang('paid'); ?></option>
                                                <option value="partial"><?= lang('partial'); ?></option>
                                                <option value="due"><?= lang('due'); ?></option>
                                            </select>
                                        </th>
                                        <th><?= lang('action'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
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
    $(function() {
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
