<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
$edit_locked = (int)$purchase->received !== 0 || (float)$purchase->paid != 0;
$edit_mobile = $this->input->get('app') == 1 || $this->input->get('mobile') == 1;
$edit_query = $edit_mobile ? '?' . http_build_query(['app'=>1,'app_lang'=>$this->input->get('app_lang',true) ?: $this->Settings->selected_language]) : '';
?>
<?php if ($edit_mobile): ?>
<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<link rel="stylesheet" href="<?= $assets ?>css/purchase-edit-mobile.css?v=1">
<?php endif; ?>
<section class="content <?= $edit_mobile ? 'kls-mobile-ui erp-page purchase-edit-mobile' : ''; ?>">
<div class="row">
<div class="col-xs-12">
<div class="box box-primary">
<div class="box-header">
<?php if ($edit_mobile): ?>
<a class="btn btn-default" href="<?= htmlspecialchars(site_url('purchases') . $edit_query, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-arrow-left"></i> <?= lang('purchases'); ?></a>
<?php endif; ?>
<h4><?= $page_title; ?></h4>
</div>
<div class="box-body">
<div class="col-lg-12">
<?php if ($edit_locked): ?>
<div class="alert alert-info">This purchase has receipts or payments. Reverse them before editing purchase items.</div>
<?php endif; ?>
<?php
$edit_action = 'purchases/edit/' . (int)$purchase->id;
if ($this->input->get('app') == 1 || $this->input->get('mobile') == 1) {
    $edit_action .= '?' . http_build_query(['app'=>1,'app_lang'=>$this->input->get('app_lang',true) ?: $this->Settings->selected_language]);
}
echo form_open_multipart($edit_action, 'class="validation edit-po-form"');
?>

<div class="row">
    <div class="col-md-6">
        <div class="form-group form-group-lg">
            <?= lang("date", "date"); ?>
            <?= form_input('date', set_value('date', $purchase->date), 'class="form-control datetimepicker" id="date" required="required"'); ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group form-group-lg">
            <?= lang('reference', 'reference'); ?>
            <?= form_input('reference', $purchase->reference, 'class="form-control tip" id="reference"'); ?>
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
                        <th style="width:25px;"><i class="fa-solid fa-trash"></i></th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="active">
                        <th><?= lang('total'); ?></th>
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
            <?= form_dropdown('supplier', $sp, set_value('supplier', $purchase->supplier_id), 'class="form-control select2 tip" id="supplier" required="required" style="width:100%;"'); ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group form-group-lg">
            <?= lang('paid', 'paid'); ?>
            <input type="number" class="form-control" id="advance_deducted" name="advance_deducted"
                   step="0.01" value="<?= (float)$purchase->paid; ?>" readonly>
            <p class="help-block">Use the payment action to record payments.</p>
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
                echo form_dropdown('store', $wh, $purchase->store_id, 'class="form-control select2"');
            ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group form-group-lg">
            <?= lang('received', 'received'); ?>
            <?php $sts = [0 => lang('not_received_yet'), 1 => lang('received'), 2 => 'Partially received']; ?>
            <?= form_dropdown('received', $sts, $purchase->received, 'class="form-control select2 tip" id="received" disabled style="width:100%;"'); ?>
            <input type="hidden" name="received" value="<?= (int)$purchase->received; ?>">
        </div>
    </div>
</div>

<div class="form-group form-group-lg">
    <?= lang('attachment', 'attachment'); ?>
    <input type="file" name="userfile" class="form-control tip" id="attachment">
</div>

<div class="form-group form-group-lg">
    <label for="delivery">Delivery cost</label>
    <input type="number" name="delivery" id="delivery" class="form-control" min="0" step="0.01" value="<?= (float)$purchase->delivery; ?>">
    <p class="help-block">Allocated across items by primary quantity when saved.</p>
</div>

<div class="form-group form-group-lg">
    <?= lang("note", 'note'); ?>
    <?= form_textarea('note', $purchase->note, 'class="form-control redactor" id="note"'); ?>
</div>

<div class="form-group form-group-lg">
    <?= form_submit('update', lang('update'), 'class="btn btn-primary" id="edit_purchase"' . ($edit_locked ? ' disabled' : '')); ?>
    <button type="button" id="reset" class="btn btn-danger"><?= lang('reset'); ?></button>
</div>

<?php echo form_close(); ?>
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
$(document).ready(function() {
    // preload old items
    localStorage.setItem('spoitems', JSON.stringify(<?= $items; ?>));
});
$('#reset').click(function () {
    $(window).unbind('beforeunload');
});
$('#edit_purchase').click(function () {
    $(window).unbind('beforeunload');
});
</script>

<script>
var product_units = <?= json_encode($product_units); ?>;
var product_conversions = <?= json_encode($product_conversions ?? []); ?>;
var product_unit_prices = <?= json_encode($product_unit_prices ?? []); ?>;
var product_unit_conversions = <?= json_encode($product_unit_conversions ?? []); ?>;
var all_units = <?php 
    $units = $this->site->getAllUnits();
    echo json_encode($units); 
?>;
</script>

<script src="<?= $assets ?>dist/js/purchases.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(function () {
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm'
    });
});
</script>
