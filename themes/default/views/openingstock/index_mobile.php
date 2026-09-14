<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Opening Stock List mobile/app mode.
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

$add_opening_stock_url =
    site_url('openingstock/add') . $app_query;
?>


<style type="text/css">
    .erp-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #edf1f5;
        background: #fff;
    }
    .erp-page-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #2f3b52;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .erp-page-title .fa {
        width: 34px;
        height: 34px;
        line-height: 34px;
        text-align: center;
        border-radius: 8px;
        background: #eef6ff;
        color: #3c8dbc;
    }
    .erp-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .erp-summary-row {
        margin-bottom: 15px;
    }
    .erp-stat-card {
        background: #fff;
        border: 1px solid #edf1f5;
        border-radius: 10px;
        padding: 14px 15px;
        min-height: 78px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        margin-bottom: 12px;
    }
    .erp-stat-card .erp-stat-label {
        color: #7b8794;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .35px;
        margin-bottom: 6px;
        display: block;
    }
    .erp-stat-card .erp-stat-value {
        color: #243447;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        word-break: break-word;
    }
    .erp-table-card {
        background: #fff;
        border: 1px solid #edf1f5;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .erp-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .erp-table-toolbar .dt-buttons {
        display: inline-flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    .erp-table-toolbar .dt-buttons .btn,
    .erp-table-toolbar .dt-buttons button {
        border-radius: 6px !important;
        border: 1px solid #d9e2ec !important;
        background: #fff !important;
        color: #334e68 !important;
        box-shadow: none !important;
        padding: 6px 10px !important;
    }
    .erp-search-box {
        max-width: 320px;
        min-width: 240px;
    }
    .erp-search-box .form-control {
        border-radius: 6px;
        height: 34px;
    }
    .erp-table-card table thead tr.active th,
    .erp-table-card table tfoot tr.active th {
        background: #f6f9fc !important;
        color: #334e68;
        border-color: #e5edf5 !important;
        vertical-align: middle;
        white-space: nowrap;
    }
    .erp-table-card table tbody td {
        vertical-align: middle !important;
    }
    .erp-table-card .text_filter,
    .erp-table-card .select_filter {
        width: 100%;
        height: 30px;
        border: 1px solid #d9e2ec;
        border-radius: 5px;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: normal;
        background: #fff;
    }
    .erp-actions {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex-wrap: nowrap;
        white-space: nowrap;
        min-width: 90px;
    }
    .erp-actions .btn,
    .erp-actions a.btn {
        margin: 0 1px !important;
        border-radius: 5px !important;
        padding: 4px 7px !important;
    }
    .erp-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 999px;
        background: #eef6ff;
        color: #31708f;
        font-size: 12px;
        font-weight: 600;
    }
    .erp-total-cell {
        font-weight: 700;
        text-align: right;
    }
    @media (max-width: 767px) {
        .erp-page-header,
        .erp-table-toolbar {
            display: block;
        }
        .erp-header-actions,
        .erp-table-toolbar > div {
            margin-top: 10px;
        }
        .erp-search-box {
            max-width: 100%;
            min-width: 100%;
        }
    }
</style>

<?php if ($is_app_mode) { ?>
<style>
/* =========================================================
   KLSPOS Opening Stock List - True Mobile/App Layout
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

/* Full-width Opening Stock page */
.content.erp-opening-stock-page {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.erp-opening-stock-page > .row {
    width: 100% !important;
    margin: 0 !important;
}

.erp-opening-stock-page > .row > .col-xs-12 {
    width: 100% !important;
    padding: 0 !important;
    float: none !important;
}

.erp-opening-stock-page .box.box-primary {
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
.erp-opening-stock-page .erp-page-header {
    display: block !important;
    padding: 13px !important;
}

.erp-opening-stock-page .erp-page-title {
    font-size: 17px !important;
    line-height: 1.45 !important;
}

.erp-opening-stock-page .erp-header-actions {
    width: 100% !important;
    margin-top: 11px !important;
}

.erp-opening-stock-page .erp-header-actions .btn {
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
.erp-opening-stock-page .box-body {
    padding: 10px !important;
    background: #f4f7fb !important;
}

/* Three balanced summary cards */
.erp-opening-stock-page .erp-summary-row {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    grid-auto-flow: row !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
}

.erp-opening-stock-page .erp-summary-row::before,
.erp-opening-stock-page .erp-summary-row::after {
    display: none !important;
    content: none !important;
}

.erp-opening-stock-page .erp-summary-row > [class*="col-"] {
    width: auto !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
}

.erp-opening-stock-page .erp-stat-card {
    width: 100% !important;
    min-height: 86px !important;
    margin: 0 !important;
    padding: 12px !important;
}

.erp-opening-stock-page .erp-stat-label {
    font-size: 13px !important;
    line-height: 1.4 !important;
    text-transform: none !important;
}

.erp-opening-stock-page .erp-stat-value {
    font-size: 18px !important;
    line-height: 1.35 !important;
}

/* Table card and toolbar */
.erp-opening-stock-page .erp-table-card {
    padding: 10px !important;
    border-radius: 10px !important;
}

.erp-opening-stock-page .erp-table-toolbar {
    display: block !important;
    margin-bottom: 10px !important;
}

.erp-opening-stock-page #openingStockButtons,
.erp-opening-stock-page .dt-buttons,
.erp-opening-stock-page .dataTables_length {
    display: none !important;
}

.erp-opening-stock-page .erp-search-box {
    width: 100% !important;
    min-width: 0 !important;
    max-width: none !important;
    margin: 0 !important;
}

.erp-opening-stock-page #search_table {
    width: 100% !important;
    height: 42px !important;
    border-radius: 8px !important;
    font-size: 14px !important;
}

/* Wide table remains usable by horizontal swipe */
.erp-opening-stock-page .table-responsive {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    border: 0 !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.erp-opening-stock-page #openingStockData {
    width: 100% !important;
    min-width: 1050px !important;
}

.erp-opening-stock-page #openingStockData thead th,
.erp-opening-stock-page #openingStockData tbody td,
.erp-opening-stock-page #openingStockData tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
    white-space: nowrap !important;
}

.erp-opening-stock-page .text_filter {
    height: 34px !important;
    font-size: 12px !important;
}

.erp-opening-stock-page .btn-group .dropdown-menu,
.erp-opening-stock-page .erp-actions .dropdown-menu {
    z-index: 999999 !important;
}

@media (max-width: 560px) {
    .erp-opening-stock-page .erp-summary-row {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script type="text/javascript">
    function openingStockToNumber(value) {
        if (typeof pf === 'function') {
            return pf(value);
        }
        value = (value === null || value === undefined) ? 0 : value;
        return parseFloat(String(value).replace(/,/g, '')) || 0;
    }

    function openingStockFormatQty(data, type) {
        var num = openingStockToNumber(data);

        if (type === 'display' || type === 'filter') {
            return (num % 1 === 0) ? String(num) : num.toFixed(2);
        }

        return num;
    }

    function openingStockFormatTotal(num) {
        num = openingStockToNumber(num);
        return (num % 1 === 0) ? String(num) : num.toFixed(2);
    }

    function openingStockDate(data, type) {
        if (!data) {
            return '';
        }
        if (type === 'display' || type === 'filter') {
            return String(data).substr(0, 10);
        }
        return data;
    }

    $(document).ready(function() {
        var table = $('#openingStockData').DataTable({
            dom: 'Brtip',
            ajax: {
                url: '<?= site_url('openingstock/get_opening_stock'); ?>',
                type: 'POST',
                data: function(d) {
                    d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash() ?>";
                }
            },
            buttons: [
                { extend: 'copyHtml5', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'excelHtml5', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'csvHtml5', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'colvis', text: '<?= lang("columns"); ?>' }
            ],
            columns: [
                { data: 'created_at', render: openingStockDate },
                { data: 'product_code' },
                { data: 'product_name' },
                { data: 'store_name' },
                {
                    data: 'movement_type',
                    render: function(data, type) {
                        if (type !== 'display') {
                            return data || '';
                        }
                        return data ? '<span class="erp-badge">' + data + '</span>' : '';
                    }
                },
                { data: 'opening_qty_base', className: 'text-right', render: openingStockFormatQty },
                { data: 'opening_qty_secondary', className: 'text-right', render: openingStockFormatQty },
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

                var baseQty = api.column(5, { search: 'applied' }).data().reduce(function(a, b) {
                    return openingStockToNumber(a) + openingStockToNumber(b);
                }, 0);

                var secondaryQty = api.column(6, { search: 'applied' }).data().reduce(function(a, b) {
                    return openingStockToNumber(a) + openingStockToNumber(b);
                }, 0);

                $(api.column(5).footer()).html(openingStockFormatTotal(baseQty));
                $(api.column(6).footer()).html(openingStockFormatTotal(secondaryQty));

                $('#summary_records').text(api.rows({ search: 'applied' }).count());
                $('#summary_base_qty').text(openingStockFormatTotal(baseQty));
                $('#summary_secondary_qty').text(openingStockFormatTotal(secondaryQty));
            }
        });

        table.buttons().container().appendTo('#openingStockButtons');

        $('#search_table').on('keyup change', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search(this.value).draw();
            }
        });

        $('#openingStockData tfoot').on('keyup change', 'input.text_filter', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            var column = $(this).data('column');
            if (column === undefined) {
                return;
            }
            if (((code == 13 && table.column(column).search() !== this.value) || (table.column(column).search() !== '' && this.value === ''))) {
                table.column(column).search(this.value).draw();
            }
        });
    });
</script>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content erp-opening-stock-page kls-mobile-ui">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="erp-page-header">
                    <h4 class="erp-page-title">
                        <i class="fa fa-cubes"></i>
                        <span><?= $page_title ?? lang('opening_stock'); ?></span>
                    </h4>
                    <div class="erp-header-actions">
                        <a
                            href="<?= html_escape($add_opening_stock_url); ?>"
                            class="btn btn-primary"
                        >
                            <i class="fa fa-plus"></i> <?= lang('add_opening_stock'); ?>
                        </a>
                    </div>
                </div>

                <div class="box-body">
                    <div class="row erp-summary-row">
                        <div class="col-sm-4 col-xs-12">
                            <div class="erp-stat-card">
                                <span class="erp-stat-label"><?= lang('records'); ?></span>
                                <div class="erp-stat-value" id="summary_records">0</div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="erp-stat-card">
                                <span class="erp-stat-label"><?= lang('quantity_base'); ?></span>
                                <div class="erp-stat-value" id="summary_base_qty">0</div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="erp-stat-card">
                                <span class="erp-stat-label"><?= lang('quantity_secondary'); ?></span>
                                <div class="erp-stat-value" id="summary_secondary_qty">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-table-card">
                        <div class="erp-table-toolbar">
                            <div id="openingStockButtons"></div>
                            <div class="erp-search-box">
                                <input type="text" class="form-control" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="openingStockData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px; width:100%;">
                                <thead>
                                    <tr class="active">
                                        <th><?= lang('date'); ?></th>
                                        <th><?= lang('code'); ?></th>
                                        <th><?= lang('product'); ?></th>
                                        <th><?= lang('location'); ?></th>
                                        <th><?= lang('batch_no'); ?></th>
                                        <th class="text-right"><?= lang('quantity_base'); ?></th>
                                        <th class="text-right"><?= lang('quantity_secondary'); ?></th>
                                        <th style="width:100px; text-align:center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <th><input type="text" class="text_filter" data-column="0" placeholder="[<?= lang('date'); ?>]"></th>
                                        <th><input type="text" class="text_filter" data-column="1" placeholder="[<?= lang('code'); ?>]"></th>
                                        <th><input type="text" class="text_filter" data-column="2" placeholder="[<?= lang('product'); ?>]"></th>
                                        <th><input type="text" class="text_filter" data-column="3" placeholder="[<?= lang('location'); ?>]"></th>
                                        <th><input type="text" class="text_filter" data-column="4" placeholder="[<?= lang('batch_no'); ?>]"></th>
                                        <th class="erp-total-cell"></th>
                                        <th class="erp-total-cell"></th>
                                        <th style="text-align:center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-actions.css?v=1">
<script src="<?= $assets ?>js/mobile-actions.js?v=3"></script>
