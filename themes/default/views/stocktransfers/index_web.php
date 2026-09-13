<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
$(document).ready(function() {

    var table = $('#transferData').DataTable({
        'ajax': {
            url: '<?= site_url('stocktransfers/get_transfers'); ?>',
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
            { "data": "id", "visible": false },
            { "data": "created_at" },
            { "data": "product_name" },
            { "data": "from_store" },
            { "data": "to_store" },
            { "data": "movement_type" },
            { "data": "qty_base" },
            { "data": "qty_secondary" },
            { "data": "qty_primary" },
            { "data": "Actions", "searchable": false, "orderable": false }
        ]
    });

    // search box
    $('#search_table').on('keyup change', function(e) {
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
                  <h4 class="box-title pull-left"><?= lang('stock_transfers'); ?></h4>
                  <a href="<?= site_url('stocktransfers/add'); ?>" class="btn btn-primary pull-right">
                    <i class="fa fa-plus"></i> <?= lang('add_transfer'); ?>
                  </a>
                </div>
                <div class="clearfix"></div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="transferData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                            <thead>
                                <tr class="active">
                                    <th><?= lang("id"); ?></th>
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("product"); ?></th>
                                    <th><?= lang("from_store"); ?></th>
                                    <th><?= lang("to_store"); ?></th>
                                    <th><?= lang("movement_type"); ?></th>
                                    <th><?= lang("quantity_base"); ?></th>
                                    <th><?= lang("quantity_secondary"); ?></th>
                                    <th><?= lang("quantity_primary"); ?></th>
                                    <th style="width:100px;"><?= lang("actions"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="10" class="p0">
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
