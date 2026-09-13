<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
$(document).ready(function() {

    function status(x) {
            var on = '<?= lang('Active'); ?>';
            var off = '<?= lang('Inactive'); ?>';
            if (x == '1') {
                return '<div class="text-center"><span class="sale_status label label-success">'+on+'</span></div>';
            } else {
                return '<div class="text-center"><span class="sale_status label label-default">'+off+'</span></div>';
            }
        }

    var table = $('#catData').DataTable({
        'ajax' : {
            url: '<?=site_url('products/get_adjustments');?>',
            type: 'POST',
            data: function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }
        },
        "buttons": [
            { extend: 'copyHtml5', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6 ] } },
            { extend: 'excelHtml5', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6 ] } },
            { extend: 'csvHtml5',   exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6 ] } },
            { extend: 'pdfHtml5',   orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6 ] } },
            { extend: 'colvis', text: '<?=lang("columns")?>' }
        ],
        "columns": [
            { "data": "date" },
            { "data": "product_name" },
            { "data": "qty_base" },
            { "data": "qty_secondary" },
            { "data": "adjustment_type" },
            { "data": "note" },
            { "data": "username" },
            { "data": "Actions", "searchable": false, "orderable": false }
        ]
    });

    $('#search_table').on('keyup change', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if ((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === '')) {
            table.search(this.value).draw();
        }
    });

});
</script>

<script>
    $(document).ready(function() {
        
        $('#catData').on('click', '.edit-adjustment', function(e) {
    e.preventDefault();

    var id = $(this).data('id');
    var date = $(this).data('date');
    var product_id = $(this).data('product_id');
    var quantity = $(this).data('quantity');
    var adjustment_type = $(this).data('adjustment_type');
    var note = $(this).data('note');

    $('#edit_id').val(id);
    $('#edit_date').val(date);
    $('#edit_product_id').val(product_id).trigger('change');  // if using select2 or normal select
    $('#edit_store_id').val(store_id).trigger('change');
    $('#edit_quantity').val(quantity);
    $('#edit_adjustment_type').val(adjustment_type);
    $('#edit_note').val(note);

    $('#editAdjustmentModal').modal('show');
});


    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4 class="box-title pull-left"><?= $page_title; ?></h4>
                    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addAdjustmentModal">
                        <i class="fa fa-plus"></i> <?= lang('add_adjunstment'); ?>
                    </button>
                </div>
                <div class="clearfix"></div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table id="catData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                            <thead>
                                <tr class="active">
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('product'); ?></th>
                                    <th><?= lang('base_qty'); ?></th>
                                    <th><?= lang('second_qty'); ?></th>
                                    <th><?= lang('adjustment_type'); ?></th>
                                    <th><?= lang('note'); ?></th>
                                    <th><?= lang('created_by'); ?></th>
                                    <th style="width:75px;"><?= lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                    </div>
                    <div class="clearfix"></div>
                    <div class="modal fade" id="addAdjustmentModal" tabindex="-1" role="dialog" aria-labelledby="addAdjustmentModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" id="addAdjustmentModalLabel"><?= lang('add_adjustment'); ?></h4>
                            </div>
                            <div class="modal-body">
                               
                                <?php echo form_open_multipart("products/adjustments_add", 'class="validation" id="addAdjustmentForm"'); ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-group-lg">
                                    <?= lang('store', 'store'); ?>
                                    <select name="store_id" id="store_id" class="form-control select" required>
                                        <option value=""><?= lang('select').' '.lang('store'); ?></option>
                                        <?php foreach ($stores as $s): ?>
                                            <option value="<?= $s->id ?>"><?= $s->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>


                                <div class="form-group form-group-lg">
                                    <?= lang('product', 'product'); ?>
                                    <?php
                                    $p[''] = lang("select")." ".lang("product");
                                    foreach($products as $product) {
                                        $p[$product->id] = $product->name;
                                    }
                                    ?>
                                    <?= form_dropdown('product_id', $p, set_value('product_id'), 'class="form-control select2 tip" id="product"  required="required" style="width:100%;"'); ?>
                                </div>

                                <div class="form-group form-group-lg">
                                    <?= lang('base_quanity', 'base_quanity'); ?>
                                    <?= form_input('qty_base', set_value('qty_base'), 'class="form-control tip" id="qty_base" required="required"'); ?>
                                </div>

                                <div class="form-group form-group-lg">
                                    <?= lang('secondary_quanity', 'secondary_quanity'); ?>
                                    <?= form_input('qty_secondary', set_value('qty_secondary'), 'class="form-control tip" id="qty_secondary"'); ?>
                                </div>


                                <div class="form-group form-group-lg">
                                    <?= lang('adjustment_type', 'adjustment_type'); ?>
                                    <select name="adjustment_type" class="form-control select" required>
                                        <option value="used"><?= lang('used'); ?></option>
                                        <option value="damage"><?= lang('damage'); ?></option>
                                        <option value="lost"><?= lang('lost'); ?></option>
                                        <option value="expired"><?= lang('expired'); ?></option>
                                        
                                    </select>
                                </div>

                                <div class="form-group form-group-lg">
                                    <?= lang('note', 'note'); ?>
                                    <?= form_textarea('note', set_value('note'), 'class="form-control" id="note" style="height:80px;"'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group form-group-lg">
                            <?= form_submit('create', lang('submit'), 'class="btn btn-primary"'); ?>
                        </div>

                        <?= form_close(); ?>
                            </div>
                            </div>
                        </div>
                        </div>

                
                    </div>
                    <div class="modal fade" id="editAdjustmentModal" tabindex="-1" role="dialog" aria-labelledby="editAdjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?= lang('edit_adjustment'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo form_open('products/adjustments_edit', 'id="editAdjustmentForm" class="validation"'); ?>
                <input type="hidden" name="id" id="edit_id">

                <div class="form-group form-group-lg">
                    <?= lang('date', 'edit_date'); ?>
                    <input type="text" name="date" id="edit_date" class="form-control" readonly>
                </div>

                <div class="form-group form-group-lg">
                    <?= lang('product', 'edit_product_id'); ?>
                    <select name="product_id" id="edit_product_id" class="form-control select" required>
                        <option value=""><?= lang('select') . ' ' . lang('product'); ?></option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p->id ?>"><?= $p->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group form-group-lg">
                    <?= lang('warehouse', 'edit_store_id'); ?>
                    <select name="store_id" id="edit_store_id" class="form-control select" required>
                        <option value=""><?= lang('select') . ' ' . lang('warehouse'); ?></option>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= $w->id ?>"><?= $w->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group form-group-lg">
                    <?= lang('quantity', 'edit_quantity'); ?>
                    <input type="number" name="quantity" id="edit_quantity" class="form-control" required>
                </div>

                <div class="form-group form-group-lg">
                    <?= lang('adjustment_type', 'edit_adjustment_type'); ?>
                    <select name="adjustment_type" id="edit_adjustment_type" class="form-control select" required>
                        <option value="damage"><?= lang('damage'); ?></option>
                        <option value="lost"><?= lang('lost'); ?></option>
                        <option value="expired"><?= lang('expired'); ?></option>
                    </select>
                </div>

                <div class="form-group form-group-lg">
                    <?= lang('note', 'edit_note'); ?>
                    <textarea name="note" id="edit_note" class="form-control" style="height:80px;"></textarea>
                </div>

                <div class="form-group form-group-lg">
                    <button type="submit" class="btn btn-primary"><?= lang('update'); ?></button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

                    

            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="picModal" tabindex="-1" role="dialog" aria-labelledby="picModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div class="modal-body text-center">
                <img id="product_image" src="" alt="" />
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    $('#AdjTable').DataTable();
});
</script>
<script>

$('#addAdjustmentModal, #editAdjustmentModal').on('shown.bs.modal', function () {
    $(this).find('.select2').select2({
        dropdownParent: $(this)
    });
});

</script>
