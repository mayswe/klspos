<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Users List mobile/app mode.
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

$add_user_url =
    site_url('users/add') . $app_query;
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
        background: #eef6ff;
        color: #2563eb;
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
        background: rgba(37, 99, 235, .09);
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
        color: #2563eb;
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
    .erp-page .erp-dt-top {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 10px;
        row-gap: 8px;
    }
    .erp-page .erp-dt-top > div {
        margin-bottom: 6px;
    }
    .erp-page .dataTables_length {
        margin-top: 2px;
        margin-bottom: 6px;
        font-size: 12px;
        color: #64748b;
    }
    .erp-page .dataTables_length label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        margin-bottom: 0;
    }
    .erp-page .dataTables_length select {
        border-radius: 5px;
        border: 1px solid #dfe6ee;
        height: 31px;
        padding: 3px 8px;
        box-shadow: none;
        margin: 0 4px;
    }
    .erp-page .dataTables_info {
        color: #64748b;
        padding-top: 12px;
    }
    .erp-page .pagination {
        margin: 8px 0 0;
    }
    .erp-page .label {
        display: inline-block;
        min-width: 68px;
        padding: 5px 8px;
        border-radius: 12px;
        font-size: 11px;
    }
    .erp-page .action-col {
        min-width: 92px;
    }
    .erp-page .btn-group .dropdown-menu {
        border-radius: 8px;
        border-color: #e5e7eb;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .12);
    }
    .erp-page .btn-group .dropdown-menu > li > a {
        padding: 8px 12px;
        font-size: 12px;
    }
    @media (max-width: 767px) {
        .erp-page .erp-header,
        .erp-page .erp-table-toolbar,
        .erp-page .erp-dt-top {
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
        .erp-page .erp-dt-top .text-right {
            text-align: left !important;
        }
    }
</style>

<?php if ($is_app_mode) { ?>
<style>
/* =========================================================
   KLSPOS Users List - True Mobile/App Layout
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

/* Full-width app page */
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

/* Hide desktop export and length controls in app mode */
.erp-page .erp-dt-top {
    display: none !important;
}

/* Keep user table usable with horizontal swipe */
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

.erp-page #UTable {
    width: 100% !important;
    min-width: 920px !important;
}

.erp-page #UTable thead th,
.erp-page #UTable tbody td,
.erp-page #UTable tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
    white-space: nowrap !important;
}

.erp-page .text_filter,
.erp-page .select_filter {
    height: 34px !important;
    font-size: 12px !important;
}

.erp-page .dataTables_info {
    font-size: 12px !important;
}

.erp-page .pagination > li > a,
.erp-page .pagination > li > span {
    padding: 7px 10px !important;
    font-size: 12px !important;
}

.erp-page .label {
    font-size: 12px !important;
}

.erp-page .btn-group .dropdown-menu {
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

        function stripHtml(value) {
            return $('<div>').html(value || '').text().trim();
        }

        function updateSummaryCards(api) {
            var records = api.rows({ search: 'applied' }).count();
            var active = 0;
            var inactive = 0;
            var groups = {};

            api.rows({ search: 'applied' }).every(function() {
                var rowData = this.data();
                var statusText = stripHtml(rowData[5]).toLowerCase();
                var groupText = stripHtml(rowData[3]);

                if (statusText === '<?= strtolower(lang('active')); ?>' || statusText === 'active') {
                    active++;
                } else if (statusText === '<?= strtolower(lang('inactive')); ?>' || statusText === 'inactive') {
                    inactive++;
                }

                if (groupText !== '') {
                    groups[groupText] = true;
                }
            });

            $('#user_records').html(records);
            $('#user_active').html(active);
            $('#user_inactive').html(inactive);
            $('#user_groups').html(Object.keys(groups).length);
        }

        var table = $('#UTable').DataTable({
            dom: "<'row erp-dt-top'<'col-sm-6'B><'col-sm-6 text-right'l>>rt<'row erp-dt-bottom'<'col-sm-6'i><'col-sm-6'p>>",
            order: [[0, 'desc']],
            pageLength: (typeof Settings !== 'undefined' && Settings.rows_per_page) ? Settings.rows_per_page : 25,
            processing: false,
            serverSide: false,
            autoWidth: false,
            buttons: [
                { extend: 'copyHtml5', footer: false, exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
                { extend: 'excelHtml5', footer: false, exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
                { extend: 'csvHtml5', footer: false, exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: false, exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
                { extend: 'colvis', text: '<i class="fa fa-columns"></i> <?= lang('columns'); ?>' }
            ],
            columnDefs: [
                { targets: 5, className: 'text-center' },
                { targets: 6, searchable: false, orderable: false, className: 'text-center action-col' }
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

            $('input.text_filter', this.footer()).on('keyup change', function(e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                    self.search(this.value).draw();
                }
            });

            $('select.select_filter', this.footer()).on('change', function() {
                var value = this.value;
                if (value) {
                    self.search('^' + value + '$', true, false).draw();
                } else {
                    self.search('').draw();
                }
            });
        });
    });
</script>

<section class="content erp-page">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary" style="border-radius:10px; border-top:0; overflow:hidden;">
                <div class="erp-header">
                    <div class="erp-title-wrap">
                        <div class="erp-title-icon"><i class="fa fa-users"></i></div>
                        <div>
                            <h4 class="erp-title"><?= $page_title; ?></h4>
                            <div class="erp-subtitle"><?= lang('list'); ?></div>
                        </div>
                    </div>
                    <a
                        href="<?= html_escape($add_user_url); ?>"
                        class="btn btn-primary"
                    >
                        <i class="fa fa-plus"></i> <?= lang('add_user'); ?>
                    </a>
                </div>

                <div class="erp-body">
                    <div class="erp-stat-grid">
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('records'); ?></div>
                                    <div class="erp-stat-value" id="user_records">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-list"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('active'); ?></div>
                                    <div class="erp-stat-value" id="user_active">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-check-circle"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('inactive'); ?></div>
                                    <div class="erp-stat-value" id="user_inactive">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-ban"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 erp-stat-item">
                            <div class="erp-summary-card erp-stat-card">
                                <div>
                                    <div class="erp-stat-label"><?= lang('group'); ?></div>
                                    <div class="erp-stat-value" id="user_groups">0</div>
                                </div>
                                <div class="erp-stat-icon"><i class="fa fa-sitemap"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="erp-table-card">
                        <div class="erp-table-toolbar">
                            <h5 class="erp-table-title"><i class="fa fa-table"></i> <?= $page_title; ?> <?= lang('list'); ?></h5>
                            <input type="text" class="form-control" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>">
                        </div>

                        <div class="table-responsive">
                            <table id="UTable" class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom:5px;">
                                <thead>
                                    <tr>
                                        <th><?= lang('first_name'); ?></th>
                                        <th><?= lang('last_name'); ?></th>
                                        <th><?= lang('email'); ?></th>
                                        <th><?= lang('group'); ?></th>
                                        <th><?= lang('store'); ?></th>
                                        <th style="width:100px; text-align:center;"><?= lang('status'); ?></th>
                                        <th style="width:92px; text-align:center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user) { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($user->group, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($user->store, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-center">
                                                <?php if ($user->active) { ?>
                                                    <span class="label label-success"><?= lang('active'); ?></span>
                                                <?php } else { ?>
                                                    <span class="label label-danger"><?= lang('inactive'); ?></span>
                                                <?php } ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="erp-actions">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-cog"></i> <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right">
                                                            <li>
                                                                <a class="tip" title="<?= lang('profile'); ?>" href="<?= site_url('users/profile/' . $user->id); ?>">
                                                                    <i class="fa fa-user"></i> <?= lang('profile'); ?>
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="tip text-danger" title="<?= lang('delete'); ?>" href="<?= site_url('auth/delete/' . $user->id); ?>" onclick="return confirm('<?= lang('alert_x_user'); ?>')">
                                                                    <i class="fa fa-trash"></i> <?= lang('delete'); ?>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('first_name'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('last_name'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('email'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('group'); ?>]"></th>
                                        <th><input type="text" class="text_filter" placeholder="[<?= lang('store'); ?>]"></th>
                                        <th>
                                            <select class="select_filter">
                                                <option value=""><?= lang('all'); ?></option>
                                                <option value="<?= lang('active'); ?>"><?= lang('active'); ?></option>
                                                <option value="<?= lang('inactive'); ?>"><?= lang('inactive'); ?></option>
                                            </select>
                                        </th>
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
