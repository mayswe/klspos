<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Stock Adjustments mobile/app mode.
 *
 * app=1 and app_lang are preserved in Add/Edit forms,
 * so the page stays in mobile mode after submitting.
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

$adjustment_add_action =
    site_url('products/adjustments_add') . $app_query;

$adjustment_edit_action =
    site_url('products/adjustments_edit') . $app_query;
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
    .erp-page table.dataTable tfoot th,
    .erp-page table.dataTable tfoot td {
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
        background: #fff;
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
        min-width: 110px;
    }
    .erp-page .adjustment-badge {
        display: inline-block;
        min-width: 70px;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
        color: #fff;
        text-transform: uppercase;
    }
    .erp-page .badge-used { background: #00a65a; }
    .erp-page .badge-damage { background: #dd4b39; }
    .erp-page .badge-lost { background: #f39c12; }
    .erp-page .badge-expired { background: #777; }
    .erp-page .badge-default { background: #607d8b; }
    .erp-modal .modal-content {
        border-radius: 10px;
        overflow: hidden;
        border: 0;
    }
    .erp-modal .modal-header {
        background: #f7fafc;
        border-bottom: 1px solid #edf1f5;
    }
    .erp-modal .modal-title {
        font-weight: 800;
        color: #273444;
    }
    .erp-modal .form-control {
        border-radius: 6px;
        border-color: #dfe6ee;
        box-shadow: none;
    }
    .erp-modal .modal-footer {
        background: #fbfcfe;
        border-top: 1px solid #edf1f5;
    }

    /* =========================================================
       Adjustment modal ERP + Select2 placement fix
       ========================================================= */
    .adjustment-erp-modal .modal-dialog {
        max-width: 760px;
    }

    .adjustment-erp-modal .modal-content {
        overflow: visible !important;
        border: 0;
        border-radius: 14px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .24);
    }

    .adjustment-erp-modal .modal-header {
        padding: 15px 17px;
        border-bottom: 1px solid #e7edf3;
        border-radius: 14px 14px 0 0;
        background: linear-gradient(135deg, #f8fafc 0%, #eef8f9 100%);
    }

    .adjustment-erp-modal .modal-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #263548;
        font-size: 16px;
        font-weight: 800;
    }

    .adjustment-erp-modal .modal-title i {
        color: #2f8191;
    }

    .adjustment-erp-modal .modal-body {
        overflow: visible !important;
        padding: 17px;
        background: #fff;
    }

    .adjustment-erp-modal .modal-footer {
        padding: 12px 17px;
        border-top: 1px solid #e7edf3;
        border-radius: 0 0 14px 14px;
        background: #fbfcfd;
    }

    .adjustment-erp-modal .modal-footer .btn {
        min-width: 110px;
        min-height: 40px;
        border-radius: 7px;
        font-weight: 800;
    }

    .adjustment-erp-modal .adjustment-modal-note {
        margin-bottom: 14px;
        padding: 10px 12px;
        border-left: 4px solid #2f8191;
        border-radius: 7px;
        background: #f3fafb;
        color: #526274;
        font-size: 12px;
        line-height: 1.6;
    }

    .adjustment-erp-modal .adjustment-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .adjustment-erp-modal .adjustment-field {
        min-width: 0;
    }

    .adjustment-erp-modal .adjustment-field-full {
        grid-column: 1 / -1;
    }

    .adjustment-erp-modal label {
        display: flex;
        align-items: center;
        gap: 4px;
        min-height: 22px;
        margin-bottom: 6px;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
    }

    .adjustment-erp-modal .form-group {
        margin-bottom: 0;
    }

    .adjustment-erp-modal .form-control {
        width: 100%;
        min-height: 43px;
        border: 1px solid #cfd9e3;
        border-radius: 7px;
        box-shadow: none;
    }

    .adjustment-erp-modal textarea.form-control {
        min-height: 92px;
        resize: vertical;
    }

    .adjustment-erp-modal .form-control:focus {
        border-color: #2f8191;
        box-shadow: 0 0 0 3px rgba(47, 129, 145, .10);
    }

    /* Keep exactly one full-width Select2 control */
    .adjustment-erp-modal .select2-container {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    .adjustment-erp-modal select.erp-adjustment-select2.select2-hidden-accessible,
    .adjustment-erp-modal select.erp-adjustment-select2.select2-offscreen {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        border: 0 !important;
        clip: rect(0 0 0 0) !important;
        overflow: hidden !important;
    }

    /* Select2 v3 */
    .adjustment-erp-modal .select2-container .select2-choice {
        width: 100% !important;
        height: 43px !important;
        min-height: 43px !important;
        padding: 0 36px 0 11px !important;
        border: 1px solid #cfd9e3 !important;
        border-radius: 7px !important;
        background: #fff !important;
        box-shadow: none !important;
        line-height: 41px !important;
    }

    .adjustment-erp-modal .select2-container .select2-choice > .select2-chosen {
        line-height: 41px !important;
    }

    .adjustment-erp-modal .select2-container .select2-choice .select2-arrow {
        width: 34px !important;
        height: 41px !important;
        border-left: 0 !important;
        background: transparent !important;
    }

    /* Select2 v4 */
    .adjustment-erp-modal .select2-container--default .select2-selection--single {
        width: 100% !important;
        height: 43px !important;
        min-height: 43px !important;
        border: 1px solid #cfd9e3 !important;
        border-radius: 7px !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .adjustment-erp-modal .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        padding-left: 11px !important;
        padding-right: 36px !important;
        line-height: 41px !important;
    }

    .adjustment-erp-modal .select2-container--default
    .select2-selection--single
    .select2-selection__arrow {
        width: 34px !important;
        height: 41px !important;
    }

    /*
     * Dropdown stays attached to body. This avoids the transformed Bootstrap
     * modal offset that caused the dropdown to appear below the quantity row.
     */
    .select2-drop,
    .select2-dropdown,
    .select2-container--open {
        z-index: 10650 !important;
    }

    @media (max-width: 767px) {
        .adjustment-erp-modal .adjustment-form-grid {
            grid-template-columns: 1fr;
        }

        .adjustment-erp-modal .adjustment-field-full {
            grid-column: auto;
        }
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
   KLSPOS Stock Adjustments - True Mobile/App Layout
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

/* Full-width page */
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

/* Summary cards: 2 x 2 */
.erp-page .erp-stat-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
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

/* Wide table stays usable by horizontal swipe */
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
    min-width: 1080px !important;
}

.erp-page #catData thead th,
.erp-page #catData tbody td,
.erp-page #catData tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
    white-space: nowrap !important;
}

.erp-page .text_filter,
.erp-page .select_filter {
    height: 34px !important;
    font-size: 12px !important;
}

/* Add/Edit Adjustment modals */
.adjustment-erp-modal .modal-dialog {
    width: auto !important;
    max-width: none !important;
    margin: 10px !important;
}

.adjustment-erp-modal .modal-content {
    border-radius: 12px !important;
}

.adjustment-erp-modal .modal-header,
.adjustment-erp-modal .modal-body,
.adjustment-erp-modal .modal-footer {
    padding-left: 13px !important;
    padding-right: 13px !important;
}

.adjustment-erp-modal .adjustment-form-grid {
    grid-template-columns: 1fr !important;
}

.adjustment-erp-modal .adjustment-field-full {
    grid-column: auto !important;
}

.adjustment-erp-modal .form-control,
.adjustment-erp-modal .select2-container .select2-choice,
.adjustment-erp-modal
.select2-container--default
.select2-selection--single {
    width: 100% !important;
    min-height: 43px !important;
    font-size: 14px !important;
}

.adjustment-erp-modal .select2-container {
    width: 100% !important;
}

.adjustment-erp-modal .modal-footer {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
}

.adjustment-erp-modal .modal-footer .btn {
    width: 100% !important;
    min-width: 0 !important;
    margin: 0 !important;
}

.select2-drop,
.select2-dropdown,
.select2-container--open,
.erp-page .btn-group .dropdown-menu,
.erp-page .erp-actions .dropdown-menu {
    z-index: 999999 !important;
}

@media (max-width: 420px) {
    .erp-page .erp-stat-grid,
    .adjustment-erp-modal .modal-footer {
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

        function renderAdjustmentType(x, type) {
            if (type !== 'display') {
                return x || '';
            }

            var labels = {
                used: '<?= lang('used'); ?>',
                damage: '<?= lang('damage'); ?>',
                lost: '<?= lang('lost'); ?>',
                expired: '<?= lang('expired'); ?>'
            };

            var value = (x || '').toString().toLowerCase();
            var label = labels[value] || x || '-';
            var cls = 'badge-' + (labels[value] ? value : 'default');

            return '<div class="text-center"><span class="adjustment-badge ' + cls + '">' + label + '</span></div>';
        }

        function updateSummaryCards(api) {
            var records = api.rows({ search: 'applied' }).count();
            var totalBaseQty = 0;
            var totalSecondQty = 0;
            var damageLost = 0;

            api.rows({ search: 'applied' }).every(function() {
                var row = this.data();
                if (!row) {
                    return;
                }

                totalBaseQty += parseFloat(row.qty_base || 0);
                totalSecondQty += parseFloat(row.qty_secondary || 0);

                var adjType = (row.adjustment_type || '').toString().toLowerCase();
                if (adjType === 'damage' || adjType === 'lost') {
                    damageLost++;
                }
            });

            $('#adj_records').html(records);
            $('#adj_base_qty').html(formatQty(totalBaseQty, 'display'));
            $('#adj_second_qty').html(formatQty(totalSecondQty, 'display'));
            $('#adj_damage_lost').html(damageLost);
        }

        var table = $('#catData').DataTable({
            ajax: {
                url: '<?= site_url('products/get_adjustments'); ?>',
                type: 'POST',
                data: function(d) {
                    d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
                }
            },
            dom: "<'row erp-dt-top'<'col-sm-6'B><'col-sm-6 text-right'l>>rt<'row erp-dt-bottom'<'col-sm-6'i><'col-sm-6'p>>",
            pageLength: 25,
            order: [[0, 'desc']],
            autoWidth: false,
            buttons: [
                { extend: 'copyHtml5', footer: true, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'excelHtml5', footer: true, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'csvHtml5', footer: true, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
                { extend: 'colvis', text: '<i class="fa fa-columns"></i> <?= lang('columns'); ?>' }
            ],
            columns: [
                { data: 'date' },
                { data: 'product_name' },
                { data: 'qty_base', className: 'text-right', render: formatQty },
                { data: 'qty_secondary', className: 'text-right', render: formatQty },
                { data: 'adjustment_type', render: renderAdjustmentType },
                { data: 'note' },
                { data: 'username' },
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
            footerCallback: function(tfoot, data, start, end, display) {
                var api = this.api();

                var baseQty = api.column(2, { search: 'applied' }).data().reduce(function(a, b) {
                    return parseFloat(a || 0) + parseFloat(b || 0);
                }, 0);

                var secondQty = api.column(3, { search: 'applied' }).data().reduce(function(a, b) {
                    return parseFloat(a || 0) + parseFloat(b || 0);
                }, 0);

                $(api.column(2).footer()).html(formatQty(baseQty, 'display'));
                $(api.column(3).footer()).html(formatQty(secondQty, 'display'));
            },
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
            $('select.select_filter', this.footer()).on('change', function() {
                self.search(this.value).draw();
            });
        });

        $('#catData').on('click', '.edit-adjustment', function(e) {
            e.preventDefault();

            var id = $(this).data('id');
            var date = $(this).data('date');
            var product_id = $(this).data('product_id');
            var store_id = $(this).data('store_id') || $(this).data('warehouse_id') || $(this).data('store');
            var quantity = $(this).data('quantity') || $(this).data('qty_base') || '';
            var adjustment_type = $(this).data('adjustment_type');
            var note = $(this).data('note');

            $('#edit_id').val(id);
            $('#edit_date').val(date);
            $('#edit_product_id').val(product_id).trigger('change');
            $('#edit_store_id').val(store_id).trigger('change');
            $('#edit_quantity').val(quantity);
            $('#edit_adjustment_type').val(adjustment_type).trigger('change');
            $('#edit_note').val(note);

            $('#editAdjustmentModal').modal('show');
        });

        /*
         * Bootstrap 3 keeps focus inside the modal. Select2 places its search
         * field under body, so disable enforceFocus for Select2 compatibility.
         */
        if (
            $.fn.modal &&
            $.fn.modal.Constructor &&
            $.fn.modal.Constructor.prototype
        ) {
            $.fn.modal.Constructor.prototype.enforceFocus = function() {};
        }

        function initialiseAdjustmentSelect2($modal) {
            if (!$.fn.select2) {
                console.error('KLSPOS Adjustment: Select2 library is not loaded.');
                return;
            }

            $modal.find('select.erp-adjustment-select2').each(function() {
                var $select = $(this);

                var initialised =
                    !!$select.data('select2') ||
                    $select.hasClass('select2-hidden-accessible') ||
                    $select.hasClass('select2-offscreen');

                /*
                 * Do not initialize the same select twice. The old code did
                 * that on every modal open and produced the wrong offset.
                 */
                if (initialised) {
                    $select.next('.select2-container').css('width', '100%');
                    return;
                }

                $select
                    .siblings('.select2-container')
                    .remove();

                $select
                    .removeClass('select2-hidden-accessible select2-offscreen')
                    .removeAttr('data-select2-id')
                    .css('width', '100%');

                $select.find('option').removeAttr('data-select2-id');

                try {
                    /*
                     * No dropdownParent here. Keeping the dropdown under body
                     * prevents Bootstrap modal transform offset errors.
                     */
                    $select.select2({
                        width: '100%',
                        dropdownAutoWidth: true,
                        minimumResultsForSearch: 0
                    });
                } catch (error) {
                    $select.select2();
                    $select.next('.select2-container').css('width', '100%');
                }
            });
        }

        $('#addAdjustmentModal, #editAdjustmentModal')
            .on('shown.bs.modal', function() {
                var $modal = $(this);

                initialiseAdjustmentSelect2($modal);

                window.setTimeout(function() {
                    $modal.find('.select2-container').css('width', '100%');
                }, 50);
            })
            .on('hidden.bs.modal', function() {
                /*
                 * Close any Select2 dropdown still open before the next modal.
                 */
                try {
                    $(this)
                        .find('select.erp-adjustment-select2')
                        .select2('close');
                } catch (error) {}
            });

        /*
         * Initialize once for controls already visible in the DOM.
         */
        initialiseAdjustmentSelect2($('#addAdjustmentModal'));
        initialiseAdjustmentSelect2($('#editAdjustmentModal'));
    });
</script>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content erp-page kls-mobile-ui">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary" style="border-radius:10px; border-top:0; overflow:hidden;">
                <div class="erp-header">
                    <div class="erp-title-wrap">
                        <div class="erp-title-icon"><i class="fa fa-sliders"></i></div>
                        <div>
                            <h4 class="erp-title"><?= $page_title; ?></h4>
                            <div class="erp-subtitle"><?= lang('adjustment_type'); ?> <?= lang('list'); ?></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAdjustmentModal">
                        <i class="fa fa-plus"></i> <?= lang('add_adjustment'); ?>
                    </button>
                </div>

                <div class="erp-body">
                    <div class="erp-stat-grid">
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('records'); ?></div>
                                    <div class="erp-stat-value" id="adj_records">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-list"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('base_qty'); ?></div>
                                    <div class="erp-stat-value" id="adj_base_qty">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-cubes"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('second_qty'); ?></div>
                                    <div class="erp-stat-value" id="adj_second_qty">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-cube"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('damage'); ?> / <?= lang('lost'); ?></div>
                                    <div class="erp-stat-value" id="adj_damage_lost">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
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
                                        <th><?= lang('date'); ?></th>
                                        <th><?= lang('product'); ?></th>
                                        <th class="text-right"><?= lang('base_qty'); ?></th>
                                        <th class="text-right"><?= lang('second_qty'); ?></th>
                                        <th><?= lang('adjustment_type'); ?></th>
                                        <th><?= lang('note'); ?></th>
                                        <th><?= lang('created_by'); ?></th>
                                        <th style="width:110px; text-align:center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><input type="text" class="text_filter datepicker" placeholder="[<?= lang('date'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('product'); ?>]"></th>
                                        <th class="text-right"><?= lang('base_qty'); ?></th>
                                        <th class="text-right"><?= lang('second_qty'); ?></th>
                                        <th>
                                            <select class="select_filter">
                                                <option value=""><?= lang('all'); ?></option>
                                                <option value="used"><?= lang('used'); ?></option>
                                                <option value="damage"><?= lang('damage'); ?></option>
                                                <option value="lost"><?= lang('lost'); ?></option>
                                                <option value="expired"><?= lang('expired'); ?></option>
                                            </select>
                                        </th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('note'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('created_by'); ?>]"></th>
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

<div
    class="modal fade erp-modal adjustment-erp-modal"
    id="addAdjustmentModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="addAdjustmentModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="<?= html_escape(lang('close')); ?>"
                >
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title" id="addAdjustmentModalLabel">
                    <i class="fa fa-plus-circle"></i>
                    <?= lang('add_adjustment'); ?>
                </h4>
            </div>

            <?= form_open_multipart(
                $adjustment_add_action,
                'class="validation" id="addAdjustmentForm"'
            ); ?>

                <?php if ($is_app_mode): ?>
                    <input type="hidden" name="app" value="1">

                    <?php if (!empty($app_language)): ?>
                        <input
                            type="hidden"
                            name="app_lang"
                            value="<?= html_escape($app_language); ?>"
                        >
                    <?php endif; ?>
                <?php endif; ?>

                <div class="modal-body">
                    <div class="adjustment-modal-note">
                        <i class="fa fa-info-circle"></i>
                        <?= lang('adjustment_entry_help'); ?>
                    </div>

                    <div class="adjustment-form-grid">

                        <div class="adjustment-field">
                            <div class="form-group">
                                <label for="store_id"><?= lang('store'); ?></label>

                                <select
                                    name="store_id"
                                    id="store_id"
                                    class="form-control select2 erp-adjustment-select2"
                                    required="required"
                                    style="width:100%;"
                                >
                                    <option value="">
                                        <?= lang('select') . ' ' . lang('store'); ?>
                                    </option>

                                    <?php foreach ($stores as $s): ?>
                                        <option value="<?= (int) $s->id; ?>">
                                            <?= html_escape($s->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="adjustment-field">
                            <div class="form-group">
                                <label for="product"><?= lang('product'); ?></label>

                                <?php
                                $product_options = [
                                    '' => lang('select') . ' ' . lang('product')
                                ];

                                foreach ($products as $product) {
                                    $product_options[$product->id] = $product->name;
                                }
                                ?>

                                <?= form_dropdown(
                                    'product_id',
                                    $product_options,
                                    set_value('product_id'),
                                    'class="form-control select2 erp-adjustment-select2 tip"
                                     id="product"
                                     required="required"
                                     style="width:100%;"'
                                ); ?>
                            </div>
                        </div>

                        <div class="adjustment-field">
                            <div class="form-group">
                                <label for="qty_base"><?= lang('base_quanity'); ?></label>

                                <?= form_input(
                                    'qty_base',
                                    set_value('qty_base'),
                                    'class="form-control tip"
                                     id="qty_base"
                                     type="number"
                                     step="any"
                                     required="required"'
                                ); ?>
                            </div>
                        </div>

                        <div class="adjustment-field">
                            <div class="form-group">
                                <label for="qty_secondary">
                                    <?= lang('secondary_quanity'); ?>
                                </label>

                                <?= form_input(
                                    'qty_secondary',
                                    set_value('qty_secondary'),
                                    'class="form-control tip"
                                     id="qty_secondary"
                                     type="number"
                                     step="any"'
                                ); ?>
                            </div>
                        </div>

                        <div class="adjustment-field adjustment-field-full">
                            <div class="form-group">
                                <label for="adjustment_type">
                                    <?= lang('adjustment_type'); ?>
                                </label>

                                <select
                                    name="adjustment_type"
                                    id="adjustment_type"
                                    class="form-control select2 erp-adjustment-select2"
                                    required="required"
                                    style="width:100%;"
                                >
                                    <option value="used"><?= lang('used'); ?></option>
                                    <option value="damage"><?= lang('damage'); ?></option>
                                    <option value="lost"><?= lang('lost'); ?></option>
                                    <option value="expired"><?= lang('expired'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="adjustment-field adjustment-field-full">
                            <div class="form-group">
                                <label for="note"><?= lang('note'); ?></label>

                                <?= form_textarea(
                                    'note',
                                    set_value('note'),
                                    'class="form-control"
                                     id="note"
                                     rows="4"'
                                ); ?>
                            </div>
                        </div>

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

                    <button
                        type="submit"
                        name="create"
                        class="btn btn-primary"
                    >
                        <i class="fa fa-save"></i>
                        <?= lang('submit'); ?>
                    </button>
                </div>

            <?= form_close(); ?>
        </div>
    </div>
</div>

<div
    class="modal fade erp-modal adjustment-erp-modal"
    id="editAdjustmentModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="editAdjustmentModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="<?= html_escape(lang('close')); ?>"
                >
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title" id="editAdjustmentModalLabel">
                    <i class="fa fa-pencil-square-o"></i>
                    <?= lang('edit_adjustment'); ?>
                </h4>
            </div>

            <?= form_open(
                $adjustment_edit_action,
                'id="editAdjustmentForm" class="validation"'
            ); ?>

                <?php if ($is_app_mode): ?>
                    <input type="hidden" name="app" value="1">

                    <?php if (!empty($app_language)): ?>
                        <input
                            type="hidden"
                            name="app_lang"
                            value="<?= html_escape($app_language); ?>"
                        >
                    <?php endif; ?>
                <?php endif; ?>

                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="adjustment-form-grid">

                        <div class="adjustment-field">
                            <div class="form-group">
                                <label for="edit_date"><?= lang('date'); ?></label>

                                <input
                                    type="text"
                                    name="date"
                                    id="edit_date"
                                    class="form-control"
                                    readonly="readonly"
                                >
                            </div>
                        </div>

                        <div class="adjustment-field">
                            <div class="form-group">
                                <label for="edit_store_id"><?= lang('warehouse'); ?></label>

                                <select
                                    name="store_id"
                                    id="edit_store_id"
                                    class="form-control select2 erp-adjustment-select2"
                                    required="required"
                                    style="width:100%;"
                                >
                                    <option value="">
                                        <?= lang('select') . ' ' . lang('warehouse'); ?>
                                    </option>

                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= (int) $warehouse->id; ?>">
                                            <?= html_escape($warehouse->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="adjustment-field adjustment-field-full">
                            <div class="form-group">
                                <label for="edit_product_id"><?= lang('product'); ?></label>

                                <select
                                    name="product_id"
                                    id="edit_product_id"
                                    class="form-control select2 erp-adjustment-select2"
                                    required="required"
                                    style="width:100%;"
                                >
                                    <option value="">
                                        <?= lang('select') . ' ' . lang('product'); ?>
                                    </option>

                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= (int) $product->id; ?>">
                                            <?= html_escape($product->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="adjustment-field">
                            <div class="form-group">
                                <label for="edit_quantity"><?= lang('quantity'); ?></label>

                                <input
                                    type="number"
                                    step="any"
                                    name="quantity"
                                    id="edit_quantity"
                                    class="form-control"
                                    required="required"
                                >
                            </div>
                        </div>

                        <div class="adjustment-field">
                            <div class="form-group">
                                <label for="edit_adjustment_type">
                                    <?= lang('adjustment_type'); ?>
                                </label>

                                <select
                                    name="adjustment_type"
                                    id="edit_adjustment_type"
                                    class="form-control select2 erp-adjustment-select2"
                                    required="required"
                                    style="width:100%;"
                                >
                                    <option value="used"><?= lang('used'); ?></option>
                                    <option value="damage"><?= lang('damage'); ?></option>
                                    <option value="lost"><?= lang('lost'); ?></option>
                                    <option value="expired"><?= lang('expired'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="adjustment-field adjustment-field-full">
                            <div class="form-group">
                                <label for="edit_note"><?= lang('note'); ?></label>

                                <textarea
                                    name="note"
                                    id="edit_note"
                                    class="form-control"
                                    rows="4"
                                ></textarea>
                            </div>
                        </div>

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
                        <i class="fa fa-save"></i>
                        <?= lang('update'); ?>
                    </button>
                </div>

            <?= form_close(); ?>
        </div>
    </div>
</div>