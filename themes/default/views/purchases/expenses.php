<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
    $(document).ready(function() {

        function attach(x) {
            if (x !== null) {
                return '<a href="<?=base_url();?>uploads/'+x+'" target="_blank" class="btn btn-primary btn-block"><i class="fa fa-chain"></i></a>';
            }
            return '';
        }

        var table = $('#expData').DataTable({

            'ajax' : { url: '<?=site_url('purchases/get_expenses');?>', type: 'POST', "data": function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }},
            "buttons": [
            { extend: 'copyHtml5', 'footer': true, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7 ] } },
            { extend: 'csvHtml5', 'footer': true, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7 ] } },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', 'footer': true,
            exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7 ] } },
            { extend: 'colvis', text: 'Columns'},
            ],
            "columns": [
            { "data": "id", "visible": false },
            { "data": "date", "render": hrld },
            { "data": "cname" },
            { "data": "reference" },
            { "data": "quantity" },
            { "data": "amount", "render": currencyFormat },
            { "data": "total", "render": currencyFormat },
            { "data": "user" },
            { "data": "attachment", "render": attach, "searchable": false, "orderable": false },
            { "data": "Actions", "searchable": false, "orderable": false }
            ],
            "footerCallback": function (tfoot, data, start, end, display) {
                var api = this.api(), data;
                $(api.column(4).footer()).html(
                    cf(api.column(4).data().reduce(function (a, b) {
                        return pf(a) + pf(b);
                    }, 0))
                );
            }

        });

        table.buttons().container().appendTo('#expDataButtons');

        $('#expenseSearch').on('keyup change', function (e) {
            var code = e.keyCode || e.which;
            if ((code === 13 && table.search() !== this.value) ||
                (table.search() !== '' && this.value === '')) {
                table.search(this.value).draw();
            }
        });

        table.on('draw', function () {
            var rows = table.rows({ search: 'applied' }).data();
            var total = 0;
            $.each(rows, function (_, row) {
                total += parseFloat(String(row.total || 0).replace(/[^0-9.-]/g, '')) || 0;
            });
            $('#expenseRecordCount').text(rows.length);
            $('#expenseTotal').html(cf(total));
        });

        $('#search_table').on( 'keyup change', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search( this.value ).draw();
            }
        });

        table.columns().every(function () {
            var self = this;
            $( 'input.datepicker', this.footer() ).on('dp.change', function (e) {
                self.search( this.value ).draw();
            });
            $( 'input:not(.datepicker)', this.footer() ).on('keyup change', function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                    self.search( this.value ).draw();
                }
            });
            $( 'select', this.footer() ).on( 'change', function (e) {
                self.search( this.value ).draw();
            });
        });

    });
</script>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<style>
    .main-header, .main-sidebar, .main-footer, .control-sidebar, .breadcrumb, .content-header { display: none !important; }
    .content-wrapper, .right-side { min-height: 100vh !important; margin-left: 0 !important; padding-top: 0 !important; background: #f4f7fb !important; }
    .content.expense-tabs-page { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 8px !important; background: #f4f7fb !important; }
    .expense-tabs-page { padding: 16px 20px 26px !important; background: #f4f7fb; }
    .expense-tabs-page .erp-shell { overflow: hidden; background: #fff; border: 1px solid #dfe6ef; border-radius: 14px; box-shadow: 0 10px 28px rgba(15,23,42,.06); }
    .expense-tabs-page .erp-tabs-wrap { padding: 12px 16px 0; }
    .expense-tabs-page .erp-tabs { display: grid !important; grid-template-columns: 1fr; margin: 0 !important; padding: 5px !important; border: 1px solid #e1e8ef; border-radius: 10px; background: #f4f7fa; }
    .expense-tabs-page .erp-tabs:before, .expense-tabs-page .erp-tabs:after { display: none !important; }
    .expense-tabs-page .erp-tabs > li { float: none !important; width: 100% !important; margin: 0 !important; }
    .expense-tabs-page .erp-tabs > li > a { display: flex !important; min-height: 44px; align-items: center; justify-content: center; margin: 0 !important; border: 0 !important; border-radius: 8px !important; background: #2f8191 !important; color: #fff !important; font-weight: 800; }
    .expense-tabs-page .erp-tab-content { padding: 15px 16px 18px; }
    .expense-tabs-page .erp-summary-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 12px; margin-bottom: 12px; }
    .expense-tabs-page .erp-stat-card { position: relative; min-height: 88px; padding: 14px; overflow: hidden; border: 1px solid #e6edf3; border-radius: 10px; background: #fff; box-shadow: 0 2px 9px rgba(15,23,42,.04); }
    .expense-tabs-page .erp-stat-card:before { position: absolute; top: 0; left: 0; width: 4px; height: 100%; content: ''; background: #3c8dbc; }
    .expense-tabs-page .erp-stat-card.success:before { background: #21a366; }
    .expense-tabs-page .erp-stat-label { margin-bottom: 6px; color: #7a8793; font-size: 16px; }
    .expense-tabs-page .erp-stat-value { color: #263238; font-size: 20px; font-weight: 800; }
    .expense-tabs-page .erp-stat-icon { position: absolute; right: 14px; bottom: 10px; color: rgba(0,0,0,.09); font-size: 30px; }
    .expense-tabs-page .erp-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 11px 12px; border: 1px solid #e2e9ef; border-bottom: 0; border-radius: 10px 10px 0 0; background: #fbfdff; }
    .expense-tabs-page .erp-toolbar-left, .expense-tabs-page .erp-toolbar-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .expense-tabs-page .erp-toolbar-right { margin-left: auto; }
    .expense-tabs-page .erp-search-box { position: relative; width: 290px; max-width: 100%; }
    .expense-tabs-page .erp-search-box i { position: absolute; top: 50%; left: 12px; transform: translateY(-50%); color: #94a3b8; }
    .expense-tabs-page .erp-search-box .form-control { height: 38px; padding-left: 36px; border: 1px solid #cfd9e3; border-radius: 7px; box-shadow: none; }
    .expense-tabs-page .dt-buttons { display: flex; gap: 4px; flex-wrap: wrap; }
    .expense-tabs-page .dt-buttons .btn { margin: 0 !important; padding: 7px 11px !important; border: 1px solid #dbe3ed !important; border-radius: 7px !important; background: #f8fafc !important; color: #334155 !important; font-weight: 800; }
    .expense-tabs-page .erp-table-wrap { overflow-x: auto; border: 1px solid #e2e9ef; border-radius: 0 0 10px 10px; -webkit-overflow-scrolling: touch; }
    .expense-tabs-page table.dataTable { min-width: 1100px; margin: 0 !important; }
    .expense-tabs-page table.dataTable thead th { padding: 10px 9px !important; background: #f3f6f9 !important; color: #334155; font-weight: 900; }
    .expense-tabs-page table.dataTable tbody td { padding: 11px 9px !important; color: #334155; vertical-align: middle; }
    .expense-tabs-page #expData tfoot { display: none; }
    .expense-tabs-page .erp-action-legend { display: none !important; }
    .expense-tabs-page #expData_wrapper > .row:last-child { margin: 0; padding: 16px 12px 18px; border-top: 1px solid #e2e9ef; }
    .expense-tabs-page #expData_wrapper .dataTables_info,
    .expense-tabs-page #expData_wrapper .dataTables_paginate { margin-top: 0; margin-bottom: 0; }
    @media (max-width: 767px) { .expense-tabs-page { padding: 8px !important; } .expense-tabs-page .erp-summary-grid { grid-template-columns: 1fr; } .expense-tabs-page .erp-toolbar-right { width: 100%; margin-left: 0; } .expense-tabs-page .erp-search-box { width: 100%; } }
</style>

<section class="content expense-tabs-page kls-mobile-ui">
    <div class="erp-shell">
        <div class="erp-tabs-wrap">
            <ul class="nav nav-tabs erp-tabs"><li class="active"><a href="#expense-list" data-toggle="tab"><?= html_escape($page_title); ?></a></li></ul>
        </div>
        <div class="tab-content erp-tab-content">
            <div class="tab-pane active" id="expense-list">
                <div class="erp-summary-grid">
                    <div class="erp-stat-card"><div class="erp-stat-label"><?= html_escape(lang('records')); ?></div><div class="erp-stat-value" id="expenseRecordCount">0</div><i class="fa fa-list erp-stat-icon"></i></div>
                    <div class="erp-stat-card success"><div class="erp-stat-label"><?= html_escape(lang('total')); ?></div><div class="erp-stat-value" id="expenseTotal">0.00</div><i class="fa fa-calculator erp-stat-icon"></i></div>
                </div>
                <div class="erp-toolbar">
                    <div class="erp-toolbar-left" id="expDataButtons"></div>
                    <div class="erp-toolbar-right">
                        <a href="<?= site_url('purchases/add_expense'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> <?= lang('add_expense'); ?></a>
                        <div class="erp-search-box"><i class="fa fa-search"></i><input type="text" id="expenseSearch" class="form-control" placeholder="<?= lang('type_hit_enter'); ?>"></div>
                    </div>
                </div>
                <div class="erp-table-wrap table-responsive">
                        <table id="expData" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr class="active">
                                    <th style="max-width:30px;"><?= lang("id"); ?></th>
                                    <th class="col-xs-2"><?= lang("date"); ?></th>
                                    <th class="col-xs-2"><?= lang("type"); ?></th>
                                    <th class="col-xs-2"><?= lang("reference"); ?></th>
                                    <th class="col-xs-1"><?= lang("quantity"); ?></th>
                                    <th class="col-xs-1"><?= lang("amount"); ?></th>
                                    <th class="col-xs-1"><?= lang("total"); ?></th>
                                    <th class="col-xs-2"><?= lang("created_by"); ?></th>
                                    <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                    <th style="width:100px;"><?= lang("actions"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="active">
                                    <th style="max-width:30px;"><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
                                    <th class="col-sm-2"><span class="datepickercon"><input type="text" class="text_filter datepicker" placeholder="[<?= lang('date'); ?>]"></span></th>
                                    <th class="col-sm-2"><input type="text" class="text_filter" placeholder="[<?= lang('type'); ?>]"></th>
                                    <th class="col-sm-2"><input type="text" class="text_filter" placeholder="[<?= lang('reference'); ?>]"></th>
                                    <th class="col-xs-1"><?= lang('quantity'); ?></th>
                                    <th class="col-xs-1"><?= lang('amount'); ?></th>
                                    <th class="col-xs-1"><?= lang('total'); ?></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('created_by'); ?>]"></th>
                                    <th style="width:25px; padding-right:5px;"><i class="fa fa-chain"></i></th>
                                    <th style="width:75px;"><?= lang('actions'); ?></th>
                                </tr>
                                <tr>
                                    <td colspan="10" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</section>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datetimepicker({format: 'YYYY-MM-DD', showClear: true, showClose: true, useCurrent: false, widgetPositioning: {horizontal: 'auto', vertical: 'bottom'}, widgetParent: $('.dataTable tfoot')});
    });
</script>
