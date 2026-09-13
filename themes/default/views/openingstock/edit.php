<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4><?= $page_title ?? 'Edit Opening Stock'; ?></h4>
                </div>
                <div class="box-body">
                    <div class="col-lg-12">

                        <?= form_open('openingstock/edit/' . $stock->id, 'id="editOpeningStockForm" class="validation"'); ?>

                        <table class="table table-bordered" id="editOpeningStockTable">
                            <thead>
                                <tr>
                                    <th><?= lang('product'); ?></th>
                                    <th><?= lang('location'); ?></th>
                                    <th><?= lang('batch_no'); ?></th>
                                    <th><?= lang('quantity_base'); ?></th>
                                    <th><?= lang('quantity_secondary'); ?></th>
                                    <th><?= lang('cost_per_base'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="product_id" class="form-control select2" required>
                                            <option value=""><?= lang('select_product'); ?></option>
                                            <?php foreach ($products as $p): ?>
                                                <option value="<?= $p->id ?>" <?= $stock->product_id == $p->id ? 'selected' : ''; ?>>
                                                    <?= $p->name ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="store_id" class="form-control select2">
                                            <option value=""><?= lang('select_location'); ?></option>
                                            <?php foreach ($stores as $w): ?>
                                                <option value="<?= $w->id ?>" <?= $stock->store_id == $w->id ? 'selected' : ''; ?>>
                                                    <?= $w->name ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="batch_no" class="form-control" value="<?= $stock->batch_no; ?>"></td>
                                    <td><input type="number" step="0.000001" name="qty_base" class="form-control" required value="<?= $stock->qty_base; ?>"></td>
                                    <td><input type="number" step="0.000001" name="qty_secondary" class="form-control" value="<?= $stock->qty_secondary; ?>"></td>
                                    <td><input type="number" step="0.000001" name="cost_per_base" class="form-control" required value="<?= $stock->cost_per_base; ?>"></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><?= lang('update'); ?></button>
                            <a href="<?= site_url('openingstock'); ?>" class="btn btn-default"><?= lang('cancel'); ?></a>
                        </div>

                        <?= form_close(); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function(){
    // Optionally, if you want to add/remove rows dynamically for edit:
    $('#editOpeningStockTable').on('click', '.removeRow', function(){
        if($('#editOpeningStockTable tbody tr').length > 1){
            $(this).closest('tr').remove();
        }
    });
});
</script>
