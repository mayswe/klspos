<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Expense Type List mobile/app mode.
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

$add_expensetype_url =
    site_url('purchases/add_expensetype') . $app_query;
?>


<style type="text/css">
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
        background: #fff4e8;
        color: #f59e0b;
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
    .erp-page .erp-body {
        background: #f6f8fb;
        padding: 15px;
    }
    .erp-page .erp-summary-card,
    .erp-page .erp-table-card {
        background: #fff;
        border: 1px solid #edf1f5;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(36, 52, 71, .05);
        margin-bottom: 15px;
    }
    .erp-page .erp-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin: 0 0 15px;
}

.erp-page .erp-stat-item {
    min-width: 0;
    padding: 0;
    margin: 0;
}

.erp-page .erp-summary-card {
    margin-bottom: 0;
}

.erp-page .erp-stat-card {
    width: 100%;
    min-height: 86px;
    padding: 14px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
}

@media (max-width: 767px) {
    .erp-page .erp-stat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .erp-page .erp-stat-card {
        min-height: 82px;
        padding: 10px 9px;
    }

    .erp-page .erp-stat-label {
        font-size: 10px;
        line-height: 1.35;
        word-break: break-word;
    }

    .erp-page .erp-stat-value {
        margin-top: 4px;
        font-size: 18px;
    }

    .erp-page .erp-stat-icon {
        width: 32px;
        height: 32px;
        min-width: 32px;
        margin-left: 5px;
        border-radius: 9px;
        font-size: 15px;
    }
}

@media (max-width: 380px) {
    .erp-page .erp-stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .erp-page .erp-stat-item:last-child {
        grid-column: 1 / -1;
    }
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
    .erp-page table.dataTable tfoot th,
    .erp-page table.dataTable tfoot td {
        background: #fbfcfe !important;
        vertical-align: middle !important;
    }
    .erp-page .text_filter {
        width: 100% !important;
        height: 30px;
        border: 1px solid #dfe6ee;
        border-radius: 5px;
        padding: 4px 7px;
        font-size: 12px;
        font-weight: normal;
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
    .erp-page .action-col {
        min-width: 100px;
    }
    @media (max-width: 767px) {
        .erp-page .erp-header,
        .erp-page .erp-table-toolbar {
            display: block;
        }
        .erp-page #search_table {
            width: 100%;
            min-width: 100%;
            margin-top: 10px;
        }
        .erp-page .erp-header .btn {
            margin-top: 10px;
            width: 100%;
        }
    }
</style>

<?php if ($is_app_mode) { ?>
<style>
/* =========================================================
   KLSPOS Expense Type List - True Mobile/App Layout
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

/* Full-width Expense Type page */
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

.erp-page .erp-header > .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    width: 100% !important;
    min-height: 43px !important;
    margin-top: 11px !important;
    font-size: 14px !important;
}

/* Body */
.erp-page .erp-body {
    padding: 10px !important;
}

/* Three balanced summary cards */
.erp-page .erp-stat-grid {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    grid-auto-flow: row !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
}

.erp-page .erp-stat-grid::before,
.erp-page .erp-stat-grid::after {
    display: none !important;
    content: none !important;
}

.erp-page .erp-stat-grid > .erp-stat-item {
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
    font-size: 13px !important;
    line-height: 1.4 !important;
    text-transform: none !important;
}

.erp-page .erp-stat-value {
    margin-top: 5px !important;
    font-size: 18px !important;
    line-height: 1.35 !important;
}

.erp-page .erp-stat-icon {
    width: 38px !important;
    height: 38px !important;
    flex: 0 0 38px !important;
}

/* Table card and search */
.erp-page .erp-table-card {
    padding: 10px !important;
    border-radius: 10px !important;
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

/* Hide desktop-only controls */
.erp-page .erp-dt-top,
.erp-page .dt-buttons,
.erp-page .dataTables_length {
    display: none !important;
}

/* Keep the table usable by horizontal swipe */
.erp-page .table-responsive {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    border: 0 !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.erp-page #catData {
    width: 100% !important;
    min-width: 620px !important;
}

.erp-page #catData thead th,
.erp-page #catData tbody td,
.erp-page #catData tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
    white-space: nowrap !important;
}

.erp-page .text_filter {
    height: 34px !important;
    font-size: 12px !important;
}

.erp-page .btn-group .dropdown-menu,
.erp-page .erp-actions .dropdown-menu {
    z-index: 999999 !important;
}

@media (max-width: 420px) {
    .erp-page .erp-stat-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script type="text/javascript">
    $(document).ready(function() {

        function updateSummaryCards(api) {
            var records = api.rows({ search: 'applied' }).count();
            var withCode = 0;

            api.rows({ search: 'applied' }).every(function() {
                var row = this.data();
                if (row && row.code && $.trim(row.code) !== '') {
                    withCode++;
                }
            });

            $('#expense_type_records').html(records);
            $('#expense_type_with_code').html(withCode);
            $('#expense_type_without_code').html(records - withCode);
        }

        var table = $('#catData').DataTable({
            ajax: {
                url: '<?= site_url('purchases/get_expensetype'); ?>',
                type: 'POST',
                data: function(d) {
                    d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
                }
            },
            dom: "<'row erp-dt-top'<'col-sm-6'B><'col-sm-6 text-right'l>>rt<'row erp-dt-bottom'<'col-sm-6'i><'col-sm-6'p>>",
            pageLength: 25,
            order: [[1, 'asc']],
            autoWidth: false,
            buttons: [
                { extend: 'copyHtml5', footer: false, exportOptions: { columns: [0, 1, 2] } },
                { extend: 'excelHtml5', footer: false, exportOptions: { columns: [0, 1, 2] } },
                { extend: 'csvHtml5', footer: false, exportOptions: { columns: [0, 1, 2] } },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: false, exportOptions: { columns: [0, 1, 2] } },
                { extend: 'colvis', text: '<i class="fa fa-columns"></i> <?= lang('columns'); ?>' }
            ],
            columns: [
                { data: 'id', className: 'text-center', width: '60px' },
                { data: 'code' },
                { data: 'name' },
                {
                    data: 'Actions',
                    searchable: false,
                    orderable: false,
                    className: 'text-center action-col',
                    render: function(data, type, row) {
                        return '<div class="erp-actions">' + (data || '') + '</div>';
                    }
                }
            ],
            drawCallback: function(settings) {
                updateSummaryCards(this.api());
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
            $('input', this.footer()).on('keyup change', function(e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                    self.search(this.value).draw();
                }
            });
        });
    });
</script>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content erp-page kls-mobile-ui">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary" style="border-radius:10px; border-top:0; overflow:hidden;">
                <div class="erp-header">
                    <div class="erp-title-wrap">
                        <div class="erp-title-icon"><i class="fa fa-tags"></i></div>
                        <div>
                            <h4 class="erp-title"><?= $page_title; ?></h4>
                            <div class="erp-subtitle"><?= lang('list'); ?></div>
                        </div>
                    </div>
                    <a
                        href="<?= html_escape($add_expensetype_url); ?>"
                        class="btn btn-primary"
                    >
                        <i class="fa fa-plus"></i> <?= lang('add_expensetype'); ?>
                    </a>
                </div>

                <div class="erp-body">
                    <div class="erp-stat-grid">

    <div class="erp-stat-item">
        <div class="erp-summary-card erp-stat-card">
            <div>
                <div class="erp-stat-label">
                    <?= lang('records'); ?>
                </div>

                <div
                    class="erp-stat-value"
                    id="expense_type_records"
                >
                    0
                </div>
            </div>

            <div class="erp-stat-icon">
                <i class="fa fa-list"></i>
            </div>
        </div>
    </div>

    <div class="erp-stat-item">
        <div class="erp-summary-card erp-stat-card">
            <div>
                <div class="erp-stat-label">
                    <?= lang('code'); ?>
                </div>

                <div
                    class="erp-stat-value"
                    id="expense_type_with_code"
                >
                    0
                </div>
            </div>

            <div class="erp-stat-icon">
                <i class="fa fa-barcode"></i>
            </div>
        </div>
    </div>

    <div class="erp-stat-item">
        <div class="erp-summary-card erp-stat-card">
            <div>
                <div class="erp-stat-label">
                    <?= lang('no_code'); ?>
                </div>

                <div
                    class="erp-stat-value"
                    id="expense_type_without_code"
                >
                    0
                </div>
            </div>

            <div class="erp-stat-icon">
                <i class="fa fa-ban"></i>
            </div>
        </div>
    </div>

</div>

                    <div class="erp-table-card">
                        <div class="erp-table-toolbar">
                            <h5 class="erp-table-title"><i class="fa fa-table"></i> <?= $page_title; ?> <?= lang('list'); ?></h5>
                            <input type="text" class="form-control" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>">
                        </div>

                        <div class="table-responsive">
                            <table id="catData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px; width:100%;">
                                <thead>
                                    <tr>
                                        <th style="width:60px; text-align:center;"><?= lang('id'); ?></th>
                                        <th><?= lang('code'); ?></th>
                                        <th><?= lang('name'); ?></th>
                                        <th style="width:100px; text-align:center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('code'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('name'); ?>]"></th>
                                        <th style="text-align:center;"><?= lang('actions'); ?></th>
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
