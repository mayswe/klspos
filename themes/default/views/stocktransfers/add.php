<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box box-primary">
    <div class="box-header">
        <h4><?= $page_title ?? 'Stock Transfer'; ?></h4>
    </div>
    <div class="box-body">
        <div class="col-lg-12">
            <?= form_open('stocktransfers/add', 'class="validation" id="addTransferForm"'); ?>

            <!-- Transfer Date & Note -->
            <div class="form-group form-group-lg">
                <?= lang("transfer_date", "transfer_date"); ?>
                <?= form_input('date', set_value('date'), 'class="form-control datetimepicker" id="transfer_date" required'); ?>
            </div>
            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <textarea name="note" class="form-control" rows="3"><?= set_value('note'); ?></textarea>
            </div>

            <table class="table table-bordered" id="transferTable">
                <thead>
                    <tr>
                        <th><?= lang('product'); ?></th>
                        <th><?= lang('from_location'); ?></th>
                        <th><?= lang('to_location'); ?></th>
                        <th><?= lang('available_stock'); ?></th> <!-- new -->
                        <th><?= lang('primary_qty'); ?></th>
                        <th><?= lang('secondary_qty'); ?></th>
                        <th><button type="button" class="btn btn-success" id="addRow">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="product_id[]" class="form-control select2" required>
                                <option value=""><?= lang('select_product'); ?></option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p->id ?>"><?= $p->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="from_location[]" class="form-control select2" required>
                                <option value=""><?= lang('select_location'); ?></option>
                                <?php foreach ($stores as $w): ?>
                                    <option value="<?= $w->id ?>"><?= $w->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="to_location[]" class="form-control select2" required>
                                <option value=""><?= lang('select_location'); ?></option>
                                <?php foreach ($stores as $w): ?>
                                    <option value="<?= $w->id ?>"><?= $w->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <span class="stock-available text-info">-</span>
                        </td>
                        <td><input type="number" name="primary_qty[]" class="form-control" value="0" min="0" step="0.01" required></td>

                        <td><input type="number" name="secondary_qty[]" class="form-control" value="0" min="0" step="0.01"></td>

                        
                        <td><button type="button" class="btn btn-danger removeRow">-</button></td>
                    </tr>
                </tbody>
            </table>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?= lang('create_transfer'); ?></button>
                <a href="<?= site_url('stock_transfers'); ?>" class="btn btn-default"><?= lang('cancel'); ?></a>
            </div>

            <?= form_close(); ?>
        </div>
    </div>
</div>
</div>
</div>
</section>

<script>
$(document).ready(function(){
    $('.datetimepicker').datetimepicker({ format: 'YYYY-MM-DD HH:mm' });

    // Add new row
    $('#addRow').click(function(){
        let newRow = $('#transferTable tbody tr:first').clone();

        // Reset values
        newRow.find('input').val(0);
        newRow.find('select').val('').trigger('change');

        // Remove select2 wrapper
        newRow.find('.select2').removeClass("select2-hidden-accessible").next(".select2-container").remove();

        // Re-initialize select2
        newRow.find('select').select2();

        $('#transferTable tbody').append(newRow);
    });

    // Remove row
    $(document).on('click', '.removeRow', function(){
        if($('#transferTable tbody tr').length > 1){
            $(this).closest('tr').remove();
        }
    });
});
$(document).on('change', 'select[name="product_id[]"], select[name="from_location[]"]', function() {
    let $row = $(this).closest('tr');
    let productId = $row.find('select[name="product_id[]"]').val();
    let storeId   = $row.find('select[name="from_location[]"]').val();

    if(productId && storeId) {
        $.ajax({
            url: "<?= site_url('stocktransfers/get_stock_qty'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                product_id: productId,
                store_id: storeId,
                "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
            },
            success: function(res) {
                $row.find('.stock-available').text(res.qty);
            },
            error: function() {
                $row.find('.stock-available').text("Err");
            }
        });
    } else {
        $row.find('.stock-available').text("-");
    }
});

</script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });
    });
</script>