<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>

<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#openingStockData').DataTable({
            'ajax': {
                url: '<?= site_url('openingstock/get_opening_stock'); ?>',
                type: 'POST',
                "data": function(d) {
                    d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash() ?>";
                }
            },
            "buttons": [
                { extend: 'copyHtml5', exportOptions: { columns: ':visible' }},
                { extend: 'excelHtml5', exportOptions: { columns: ':visible' }},
                { extend: 'csvHtml5', exportOptions: { columns: ':visible' }},
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: ':visible' }},
                { extend: 'colvis', text: 'Columns' }
            ],
            "columns": [
                { "data": "created_at" },
                { "data": "product_code" },
                { "data": "product_name" },
                { "data": "store_name" },
                { "data": "movement_type" },
                { "data": "opening_qty_base" },
                { "data": "opening_qty_secondary" },
                { "data": "Actions", "searchable": false, "orderable": false }
            ]
        });

        // search
        $('#search_table').on('keyup change', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search(this.value).draw();
            }
        });

        // handle delete action

    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4 class="pull-left"><?= $page_title ?? 'Opening Stock List'; ?></h4>
                    <a href="<?= site_url('openingstock/add'); ?>" class="btn btn-primary pull-right">
                        <i class="fa fa-plus"></i> <?= lang('add_opening_stock'); ?>
                    </a>
                </div>
                <div class="clearfix"></div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table id="openingStockData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                            <thead>
                                <tr class="active">
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("code"); ?></th>
                                    <th><?= lang("product"); ?></th>
                                    <th><?= lang("location"); ?></th>
                                    <th><?= lang("batch_no"); ?></th>
                                    <th><?= lang("quantity_base"); ?></th>
                                    <th><?= lang("quantity_secondary"); ?></th>
                                    <th style="width:100px;"><?= lang("actions"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8" class="p0">
                                        <input type="text" class="form-control b0" name="search_table" id="search_table"
                                            placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;">
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