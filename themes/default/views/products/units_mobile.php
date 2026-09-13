<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Units List mobile/app mode.
 *
 * app=1 and app_lang are preserved in Add/Edit Unit forms,
 * so the page stays in the mobile view after submitting.
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

$unit_add_action =
    site_url('products/add_unit') . $app_query;

$unit_edit_action =
    site_url('products/edit_unit') . $app_query;
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

    /* Stronger ERP shell */
    .erp-page .box.box-primary {
        overflow: hidden;
        border: 1px solid #dfe6ee !important;
        border-top: 0 !important;
        border-radius: 14px !important;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
    }

    .erp-page .erp-header {
        border-left: 5px solid #2f8191;
        background: linear-gradient(135deg, #ffffff 0%, #f5fafb 100%);
    }

    .erp-page .erp-title-icon {
        background: #e8f6f7;
        color: #2f8191;
    }

    .erp-page .erp-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin: 0 0 15px;
    }

    .erp-page .erp-stat-item {
        width: auto !important;
        min-width: 0;
        float: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .erp-page .erp-stat-card {
        height: 100%;
        margin: 0;
        border-left: 4px solid #2f8191;
    }

    .erp-page .erp-stat-item:nth-child(2) .erp-stat-card {
        border-left-color: #20a464;
    }

    .erp-page .erp-stat-item:nth-child(3) .erp-stat-card {
        border-left-color: #f39c12;
    }

    .erp-page .erp-stat-item:nth-child(2) .erp-stat-icon {
        color: #20a464;
    }

    .erp-page .erp-stat-item:nth-child(3) .erp-stat-icon {
        color: #f39c12;
    }

    .erp-page .erp-table-card {
        border-radius: 11px;
    }

    /* Unit Add/Edit ERP modal */
    .erp-unit-modal .modal-dialog {
        max-width: 720px;
    }

    .erp-unit-modal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 14px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .24);
    }

    .erp-unit-modal .modal-header {
        padding: 15px 17px;
        border-bottom: 1px solid #e7edf3;
        background: linear-gradient(135deg, #f8fafc 0%, #eef8f9 100%);
    }

    .erp-unit-modal .modal-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #263548;
        font-size: 16px;
        font-weight: 800;
    }

    .erp-unit-modal .modal-title i {
        color: #2f8191;
    }

    .erp-unit-modal .modal-body {
        padding: 17px;
        background: #fff;
    }

    .erp-unit-modal .modal-footer {
        padding: 12px 17px;
        border-top: 1px solid #e7edf3;
        background: #fbfcfd;
    }

    .erp-unit-modal .modal-footer .btn {
        min-width: 110px;
        min-height: 40px;
        border-radius: 7px;
        font-weight: 800;
    }

    .erp-unit-modal .unit-modal-note {
        margin-bottom: 14px;
        padding: 10px 12px;
        border-left: 4px solid #2f8191;
        border-radius: 7px;
        background: #f3fafb;
        color: #526274;
        font-size: 12px;
        line-height: 1.6;
    }

    .erp-unit-modal .unit-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .erp-unit-modal .unit-field {
        min-width: 0;
    }

    .erp-unit-modal label {
        display: flex;
        align-items: center;
        gap: 4px;
        min-height: 22px;
        margin-bottom: 6px;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
    }

    .erp-unit-modal .required-star {
        color: #e5533d;
    }

    .erp-unit-modal .form-control {
        width: 100%;
        min-height: 43px;
        border: 1px solid #cfd9e3;
        border-radius: 7px;
        box-shadow: none;
    }

    .erp-unit-modal .form-control:focus {
        border-color: #2f8191;
        box-shadow: 0 0 0 3px rgba(47, 129, 145, .10);
    }

    .erp-unit-modal .unit-code-control {
        display: flex;
        align-items: stretch;
        gap: 8px;
    }

    .erp-unit-modal .unit-code-control .form-control {
        flex: 1;
        min-width: 0;
    }

    .erp-unit-modal .btn-code-auto {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 128px;
        min-height: 43px;
        padding: 8px 11px;
        border: 1px solid #b9d6db;
        border-radius: 7px;
        background: #eef8f9;
        color: #276b78;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .erp-unit-modal .btn-code-auto:hover,
    .erp-unit-modal .btn-code-auto:focus {
        border-color: #2f8191;
        background: #dff1f3;
        color: #276b78;
    }

    .erp-unit-modal .unit-code-meta {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
        margin-top: 6px;
    }

    .erp-unit-modal .unit-code-mode {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        padding: 3px 8px;
        border-radius: 999px;
        background: #e9f8ef;
        color: #18733c;
        font-size: 10px;
        font-weight: 900;
    }

    .erp-unit-modal .unit-code-mode.manual {
        background: #fff4df;
        color: #9a6700;
    }

    .erp-unit-modal .unit-help {
        color: #718096;
        font-size: 11px;
        line-height: 1.5;
    }

    @media (max-width: 991px) {
        .erp-page .erp-stat-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .erp-page .erp-stat-grid,
        .erp-unit-modal .unit-modal-grid {
            grid-template-columns: 1fr;
        }

        .erp-unit-modal .btn-code-auto {
            width: 46px;
            min-width: 46px;
            padding-left: 8px;
            padding-right: 8px;
        }

        .erp-unit-modal .btn-code-auto .auto-button-text {
            display: none;
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
   KLSPOS Units List - True Mobile/App Layout
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

/* Full-width Units page */
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
    background: #ffffff !important;
    box-shadow: none !important;
}

/* Header */
.erp-page .erp-header {
    display: block !important;
    padding: 13px !important;
    border-left: 0 !important;
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

/* Three summary cards */
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

/* Hide desktop-only controls in app mode */
.erp-page .erp-dt-top,
.erp-page .dt-buttons,
.erp-page .dataTables_length {
    display: none !important;
}

/* Keep Units table usable by horizontal swipe */
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

/* Add/Edit Unit modals */
.erp-unit-modal .modal-dialog,
.erp-modal .modal-dialog {
    width: auto !important;
    max-width: none !important;
    margin: 10px !important;
}

.erp-unit-modal .modal-content,
.erp-modal .modal-content {
    border-radius: 12px !important;
}

.erp-unit-modal .modal-header,
.erp-unit-modal .modal-body,
.erp-unit-modal .modal-footer {
    padding-left: 13px !important;
    padding-right: 13px !important;
}

.erp-unit-modal .unit-modal-grid {
    grid-template-columns: 1fr !important;
}

.erp-unit-modal .unit-code-control {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto !important;
    gap: 7px !important;
}

.erp-unit-modal .form-control {
    min-height: 43px !important;
    font-size: 14px !important;
}

.erp-unit-modal .modal-footer {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
}

.erp-unit-modal .modal-footer .btn {
    width: 100% !important;
    min-width: 0 !important;
    margin: 0 !important;
    float: none !important;
}

#addUnitModal .modal-footer .btn-primary {
    grid-column: 1 / -1 !important;
}

.erp-page .btn-group .dropdown-menu,
.erp-page .erp-actions .dropdown-menu,
.erp-unit-modal,
.erp-modal {
    z-index: 999999 !important;
}

@media (max-width: 560px) {
    .erp-page .erp-stat-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script type="text/javascript">
    $(document).ready(function() {

        var addUnitCodeAutoMode = true;

        function unitPadNumber(number, length) {
            var value = String(number);

            while (value.length < length) {
                value = '0' + value;
            }

            return value;
        }

        function unitCodePrefix(name) {
            var cleanName = String(name || '')
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '');

            if (cleanName.length >= 3) {
                return cleanName.substring(0, 3);
            }

            if (cleanName.length > 0) {
                return cleanName;
            }

            return 'UNT';
        }

        function existingUnitCodes(excludedId) {
            var codes = {};

            if ($.fn.DataTable.isDataTable('#catData')) {
                $('#catData').DataTable().rows().data().each(function(row) {
                    if (
                        row &&
                        row.code &&
                        String(row.id) !== String(excludedId || '')
                    ) {
                        codes[String(row.code).toUpperCase()] = true;
                    }
                });
            }

            return codes;
        }

        function nextUnitSequence(prefix, excludedId) {
            var maximum = 0;
            var expression = new RegExp(
                '^' + prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') +
                '-(\\d+)$',
                'i'
            );

            if ($.fn.DataTable.isDataTable('#catData')) {
                $('#catData').DataTable().rows().data().each(function(row) {
                    if (
                        !row ||
                        !row.code ||
                        String(row.id) === String(excludedId || '')
                    ) {
                        return;
                    }

                    var match = String(row.code).match(expression);

                    if (match) {
                        maximum = Math.max(
                            maximum,
                            parseInt(match[1], 10) || 0
                        );
                    }
                });
            }

            return maximum + 1;
        }

        function buildAutomaticUnitCode(name, excludedId) {
            var prefix = unitCodePrefix(name);
            var sequence = nextUnitSequence(prefix, excludedId);
            var codes = existingUnitCodes(excludedId);
            var code = '';

            do {
                code = prefix + '-' + unitPadNumber(sequence, 4);
                sequence++;
            } while (codes[code.toUpperCase()]);

            return code;
        }

        function setAddUnitCodeMode(isAuto) {
            addUnitCodeAutoMode = !!isAuto;
            var $mode = $('#add_unit_code_mode');

            if (addUnitCodeAutoMode) {
                $mode
                    .removeClass('manual')
                    .html(
                        '<i class="fa fa-magic"></i> ' +
                        <?= json_encode(lang('automatic'), JSON_UNESCAPED_UNICODE); ?>
                    );
            } else {
                $mode
                    .addClass('manual')
                    .html(
                        '<i class="fa fa-keyboard-o"></i> ' +
                        <?= json_encode(lang('manual'), JSON_UNESCAPED_UNICODE); ?>
                    );
            }
        }

        function regenerateAddUnitCode() {
            $('#add_unit_code').val(
                buildAutomaticUnitCode($('#add_unit_name').val())
            );

            setAddUnitCodeMode(true);
        }

        function regenerateEditUnitCode() {
            $('#edit_code').val(
                buildAutomaticUnitCode(
                    $('#edit_name').val(),
                    $('#edit_unit_id').val()
                )
            );
        }

        function updateSummaryCards(api) {
            var records = api.rows({ search: 'applied' }).count();
            var withCode = 0;

            api.rows({ search: 'applied' }).every(function() {
                var row = this.data();
                if (row && row.code && $.trim(row.code) !== '') {
                    withCode++;
                }
            });

            $('#unit_records').html(records);
            $('#unit_with_code').html(withCode);
            $('#unit_without_code').html(records - withCode);
        }

        var table = $('#catData').DataTable({
            ajax: {
                url: '<?= site_url('products/get_units'); ?>',
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
                { extend: 'copyHtml5', exportOptions: { columns: [0, 1, 2] } },
                { extend: 'excelHtml5', exportOptions: { columns: [0, 1, 2] } },
                { extend: 'csvHtml5', exportOptions: { columns: [0, 1, 2] } },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0, 1, 2] } },
                { extend: 'colvis', text: '<i class="fa fa-columns"></i> <?= lang('columns'); ?>' }
            ],
            columns: [
                { data: 'id', className: 'text-center', width: '60px' },
                { data: 'name' },
                { data: 'code' },
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

        $('#addUnitModal').on('show.bs.modal', function() {
            var form = document.getElementById('addUnitForm');

            if (form) {
                form.reset();
            }

            setAddUnitCodeMode(true);
            regenerateAddUnitCode();
        });

        $('#addUnitModal').on('shown.bs.modal', function() {
            $('#add_unit_name').trigger('focus');
        });

        $('#add_unit_name').on('input keyup change', function() {
            if (addUnitCodeAutoMode) {
                $('#add_unit_code').val(
                    buildAutomaticUnitCode($(this).val())
                );
            }
        });

        $('#add_unit_code').on('input', function() {
            setAddUnitCodeMode(false);
        });

        $('#add_unit_code').on('blur', function() {
            var value = $.trim($(this).val());

            if (!value) {
                regenerateAddUnitCode();
                return;
            }

            $(this).val(
                value
                    .toUpperCase()
                    .replace(/\s+/g, '-')
            );
        });

        $('#regenerate_add_unit_code').on('click', function() {
            regenerateAddUnitCode();
            $('#add_unit_code').trigger('focus').select();
        });

        $('#reset_add_unit_form').on('click', function() {
            var form = document.getElementById('addUnitForm');

            if (form) {
                form.reset();
            }

            $('#add_unit_name').val('');
            regenerateAddUnitCode();
            $('#add_unit_name').trigger('focus');
        });

        $('#regenerate_edit_unit_code').on('click', function() {
            regenerateEditUnitCode();
            $('#edit_code').trigger('focus').select();
        });

        $('#catData').on('click', '.edit-unit', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var code = $(this).data('code');

            $('#edit_unit_id').val(id);
            $('#edit_name').val(name);
            $('#edit_code').val(code);

            $('#editUnitModal').modal('show');
        });
    });
</script>

<section class="content erp-page">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary" style="border-radius:10px; border-top:0; overflow:hidden;">
                <div class="erp-header">
                    <div class="erp-title-wrap">
                        <div class="erp-title-icon"><i class="fa fa-balance-scale"></i></div>
                        <div>
                            <h4 class="erp-title"><?= $page_title; ?></h4>
                            <div class="erp-subtitle"><?= lang('units'); ?> <?= lang('list'); ?></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUnitModal">
                        <i class="fa fa-plus"></i> <?= lang('add_unit'); ?>
                    </button>
                </div>

                <div class="erp-body">
                    <div class="erp-stat-grid">
                        <div class="col-md-4 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('records'); ?></div>
                                    <div class="erp-stat-value" id="unit_records">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-list"></i></div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('code'); ?></div>
                                    <div class="erp-stat-value" id="unit_with_code">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-barcode"></i></div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('no_code'); ?></div>
                                    <div class="erp-stat-value" id="unit_without_code">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-ban"></i></div>
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
                                        <th><?= lang('name'); ?></th>
                                        <th><?= lang('code'); ?></th>
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
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('name'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('code'); ?>]"></th>
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
    class="modal fade erp-modal erp-unit-modal"
    id="addUnitModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="addUnitModalLabel"
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

                <h4 class="modal-title" id="addUnitModalLabel">
                    <i class="fa fa-plus-circle"></i>
                    <?= lang('add_unit'); ?>
                </h4>
            </div>

            <?= form_open_multipart(
                $unit_add_action,
                'class="validation" id="addUnitForm"'
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
                    <div class="unit-modal-note">
                        <i class="fa fa-info-circle"></i>
                        <?= lang('unit_auto_code_description'); ?>
                    </div>

                    <div class="unit-modal-grid">

                        <div class="unit-field">
                            <div class="form-group">
                                <label for="add_unit_name">
                                    <?= lang('name'); ?>
                                    <span class="required-star">*</span>
                                </label>

                                <?= form_input(
                                    'name',
                                    set_value('name'),
                                    'class="form-control"
                                     id="add_unit_name"
                                     required="required"
                                     autocomplete="off"'
                                ); ?>
                            </div>
                        </div>

                        <div class="unit-field">
                            <div class="form-group">
                                <label for="add_unit_code">
                                    <?= lang('code'); ?>
                                    <span class="required-star">*</span>
                                </label>

                                <div class="unit-code-control">
                                    <?= form_input(
                                        'code',
                                        set_value('code'),
                                        'class="form-control"
                                         id="add_unit_code"
                                         required="required"
                                         autocomplete="off"'
                                    ); ?>

                                    <button
                                        type="button"
                                        id="regenerate_add_unit_code"
                                        class="btn btn-code-auto"
                                        title="<?= html_escape(lang('generate_code')); ?>"
                                    >
                                        <i class="fa fa-magic"></i>
                                        <span class="auto-button-text">
                                            <?= lang('auto_generate'); ?>
                                        </span>
                                    </button>
                                </div>

                                <div class="unit-code-meta">
                                    <span
                                        id="add_unit_code_mode"
                                        class="unit-code-mode"
                                    >
                                        <i class="fa fa-magic"></i>
                                        <?= lang('automatic'); ?>
                                    </span>

                                    <span class="unit-help">
                                        <?= lang('unit_code_edit_help'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        id="reset_add_unit_form"
                        class="btn btn-default pull-left"
                    >
                        <i class="fa fa-refresh"></i>
                        <?= lang('reset'); ?>
                    </button>

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
                        <?= lang('create'); ?>
                    </button>
                </div>

            <?= form_close(); ?>
        </div>
    </div>
</div>

<div
    class="modal fade erp-modal erp-unit-modal"
    id="editUnitModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="editUnitModalLabel"
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

                <h4 class="modal-title" id="editUnitModalLabel">
                    <i class="fa fa-pencil-square-o"></i>
                    <?= lang('edit_unit'); ?>
                </h4>
            </div>

            <?= form_open_multipart(
                $unit_edit_action,
                'class="validation" id="editUnitForm"'
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
                    <input type="hidden" name="id" id="edit_unit_id">

                    <div class="unit-modal-grid">

                        <div class="unit-field">
                            <div class="form-group">
                                <label for="edit_name">
                                    <?= lang('name'); ?>
                                    <span class="required-star">*</span>
                                </label>

                                <?= form_input(
                                    'name',
                                    '',
                                    'class="form-control"
                                     id="edit_name"
                                     required="required"
                                     autocomplete="off"'
                                ); ?>
                            </div>
                        </div>

                        <div class="unit-field">
                            <div class="form-group">
                                <label for="edit_code">
                                    <?= lang('code'); ?>
                                    <span class="required-star">*</span>
                                </label>

                                <div class="unit-code-control">
                                    <?= form_input(
                                        'code',
                                        '',
                                        'class="form-control"
                                         id="edit_code"
                                         required="required"
                                         autocomplete="off"'
                                    ); ?>

                                    <button
                                        type="button"
                                        id="regenerate_edit_unit_code"
                                        class="btn btn-code-auto"
                                        title="<?= html_escape(lang('generate_code')); ?>"
                                    >
                                        <i class="fa fa-magic"></i>
                                        <span class="auto-button-text">
                                            <?= lang('auto_generate'); ?>
                                        </span>
                                    </button>
                                </div>

                                <div class="unit-code-meta">
                                    <span class="unit-help">
                                        <?= lang('edit_unit_code_help'); ?>
                                    </span>
                                </div>
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