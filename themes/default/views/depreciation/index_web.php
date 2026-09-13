<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#depreciationData').DataTable({
            'ajax' : { 
                url: '<?=site_url('depreciation/get_depreciation_expenses');?>', 
                type: 'POST', 
                "data": function ( d ) {
                    d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
                }
            },
            "buttons": [
                { extend: 'copyHtml5', 'footer': true },
                { extend: 'excelHtml5', 'footer': true },
                { extend: 'csvHtml5', 'footer': true },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', 'footer': true },
                { extend: 'colvis', text: 'Columns'}
            ],
            "columns": [
                { "data": "id", "visible": false },
                { "data": "asset_name" },
                { "data": "purchase_date", "render": hrld },
                { "data": "purchase_cost", "render": currencyFormat },
                { "data": "useful_life" },
                { "data": "method" },
                { "data": "depreciation_amount", "render": currencyFormat },
                { "data": "note" },
                { "data": "user" },
                { "data": "Actions", "searchable": false, "orderable": false }
            ],
            "footerCallback": function (tfoot, data, start, end, display) {
                var api = this.api();
                $(api.column(6).footer()).html(
                    cf(api.column(6).data().reduce(function (a, b) {
                        return pf(a) + pf(b);
                    }, 0))
                );
            }
        });

        $('#search_table').on('keyup change', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search(this.value).draw();
            }
        });
    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4><?= $page_title; ?></h4>
                    <div class="pull-right">
                        <a href="<?= site_url('depreciation/create'); ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> <?= lang('add_depreciation_expense'); ?>
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="depreciationData" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr class="active">
                                    <th><?= lang("id"); ?></th>
                                    <th><?= lang("asset_name"); ?></th>
                                    <th><?= lang("purchase_date"); ?></th>
                                    <th><?= lang("purchase_cost"); ?></th>
                                    <th><?= lang("useful_life"); ?></th>
                                    <th><?= lang("method"); ?></th>
                                    <th><?= lang("depreciation_amount"); ?></th>
                                    <th><?= lang("note"); ?></th>
                                    <th><?= lang("created_by"); ?></th>
                                    <th><?= lang("actions"); ?></th>
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
                                    <th></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('useful_life'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('method'); ?>]"></th>
                                    <th><?= lang("depreciation_amount"); ?></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('note'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('created_by'); ?>]"></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <td colspan="10" class="p0">
                                        <input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;">
                                    </td>
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
