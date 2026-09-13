<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4><?= $page_title; ?></h4>
                </div>
                <div class="box-body">
                    <div class="col-lg-12">
                        <?php echo form_open_multipart("purchases/add", 'class="validation"'); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang("date", "date"); ?>
                                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetimepicker" id="date" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang('reference', 'reference'); ?>
                                    <?= form_input('reference', set_value('reference'), 'class="form-control tip" id="reference"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-lg">
                            <input type="text" placeholder="<?= lang('search_product_by_name_code'); ?>" id="add_item" class="form-control">
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="poTable" class="table table-striped table-bordered">
                                        <thead>
                                        <tr class="active">
                                            <th><?= lang('product'); ?></th>
                                            <th class="col-xs-2"><?= lang('primary_qty'); ?></th>
                                            <th class="col-xs-2"><?= lang('primary_unit'); ?></th>
                                            <th class="col-xs-2"><?= lang('secondary_qty'); ?></th>
                                            <th class="col-xs-2"><?= lang('secondary_unit'); ?></th>
                                            <th class="col-xs-2"><?= lang('unit_cost'); ?></th>
                                            <th class="col-xs-2"><?= lang('subtotal'); ?></th>
                                            <th class="col-xs-2"><?= lang('transportation'); ?></th>
                                            <th style="width:25px;"><i class="fa-solid fa-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="9"><?= lang('add_product_by_searching_above_field'); ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="active">
                                            <th><?= lang('total'); ?></th>
                                            <th class="col-xs-2"></th>
                                            <th class="col-xs-2"></th>
                                            <th class="col-xs-2"></th>
                                            <th class="col-xs-2"></th>
                                            <th class="col-xs-2"></th>
                                            <th class="col-xs-2"></th>
                                            <th class="col-xs-2 text-right"><span id="gtotal">0.00</span></th>
                                            <th style="width:25px;"></th>
                                        </tr>
                                    </tfoot>

                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang('supplier', 'supplier'); ?>
                                    <?php
                                    $sp[''] = lang("select")." ".lang("supplier");
                                    foreach($suppliers as $supplier) {
                                        $sp[$supplier->id] = $supplier->name;
                                    }
                                    ?>
                                    <?= form_dropdown('supplier', $sp, set_value('supplier'), 'class="form-control select2 tip" id="supplier"  required="required" style="width:100%;"'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                    <div class="form-group form-group-lg">
                                        <?= lang('paid', 'paid'); ?>
                                        <input type="number" class="form-control" id="advance_deducted" name="advance_deducted">
                                    </div>
                                </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang('location', 'location'); ?>
                                    <?php
                                        $wh = [];
                                        $wh[''] = lang("select")." ".lang("location");
                                        foreach ($stores as $store) {
                                            $wh[$store->id] = $store->name;
                                        }
                                        echo form_dropdown('store', $wh, '', 'class="form-control select2" required');

                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang('received', 'received'); ?>
                                    <?php $sts = array(1 => lang('received'), 0 => lang('not_received_yet')); ?>
                                    <?= form_dropdown('received', $sts, set_value('received'), 'class="form-control select2 tip" id="received"  required="required" style="width:100%;"'); ?>
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            
                            <div class="col-md-6">
                                    <div class="form-group form-group-lg">
                                        <?= lang('attachment', 'attachment'); ?>
                                        <input type="file" name="userfile" class="form-control tip" id="attachment">
                                    </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang('transportation charges', 'transportation charges'); ?>
                                    <input type="number" class="form-control" id="delivery" name="delivery">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                    <div class="form-group form-group-lg">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="opening" id="opening" value="1">
                                                <?= lang('opening_due'); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="form-group form-group-lg">
                            <?= lang("note", 'note'); ?>
                            <?= form_textarea('note', set_value('note'), 'class="form-control redactor" id="note"'); ?>
                        </div>
                        <div class="form-group form-group-lg">
                            <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
                            <button type="button" id="reset" class="btn btn-danger"><?= lang('reset'); ?></button>
                        </div>

                        <?php echo form_close();?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    var spoitems = {};
    if (localStorage.getItem('remove_spo')) {
        if (localStorage.getItem('spoitems')) {
            localStorage.removeItem('spoitems');
        }
        localStorage.removeItem('remove_spo');
    }
</script>
<script>
  var spoitems = {};
  var product_units = <?= json_encode($product_units ?? []); ?>;
  var product_conversions = <?= json_encode($product_conversions ?? []); ?>;
  var product_unit_prices = <?= json_encode($product_unit_prices ?? []); ?>;
  var product_unit_conversions = <?= json_encode($product_unit_conversions ?? []); ?>;
</script>

<script>
var all_units = <?php 
    $units = $this->site->getAllUnits(); // return id => name
    echo json_encode($units); 
?>;
</script>
<script type="text/javascript">
    $(function () {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });
    });
</script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>dist/js/purchases.min.js" type="text/javascript"></script>



