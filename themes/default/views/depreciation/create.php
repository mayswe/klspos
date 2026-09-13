<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box box-primary">
<div class="box-header">
    <h4><?= $page_title; ?></h4>
</div>
<div class="box-body">
<div class="col-md-6">
    <?= form_open_multipart("depreciation/create"); ?>

    <div class="form-group form-group-lg">
        <?= lang("date", "date"); ?>
        <?= form_input('purchase_date', set_value('purchase_date'), 'class="form-control datetimepicker" id="date" required'); ?>
    </div>

    <div class="form-group form-group-lg">
        <?= lang("asset_name", "asset_name"); ?>
        <?= form_input('asset_name', set_value('asset_name'), 'class="form-control" id="asset_name" required'); ?>
    </div>

    <div class="form-group form-group-lg">
        <?= lang("purchase_cost", "purchase_cost"); ?>
        <input name="purchase_cost" type="number" step="0.01" id="purchase_cost" value="<?= set_value('purchase_cost'); ?>" class="form-control" required />
    </div>

    <div class="form-group form-group-lg">
        <?= lang("useful_life_years", "useful_life"); ?>
        <input name="useful_life" type="number" id="useful_life" value="<?= set_value('useful_life'); ?>" class="form-control" required />
    </div>

    <div class="form-group form-group-lg">
        <?= lang("method", "method"); ?>
        <?php
        $methods = [
            'straight_line' => 'Straight Line',
            'reducing_balance' => 'Reducing Balance',
        ];
        ?>
        <?= form_dropdown('method', $methods, set_value('method', 'straight_line'), 'class="form-control select2" id="method" required'); ?>
    </div>

    <div class="form-group form-group-lg">
        <?= lang("depreciation_amount", "depreciation_amount"); ?>
        <input name="depreciation_amount" type="number" step="0.01" id="depreciation_amount" value="<?= set_value('depreciation_amount'); ?>" class="form-control" required />
    </div>

    <div class="form-group form-group-lg">
        <?= lang("attachment", "attachment"); ?>
        <input id="attachment" type="file" name="userfile" class="form-control">
    </div>

    <div class="form-group form-group-lg">
        <?= lang("note", "note"); ?>
        <?= form_textarea('note', set_value('note'), 'class="form-control" id="note"'); ?>
    </div>

    <div class="form-group form-group-lg">
        <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
    </div>

    <?= form_close(); ?>
</div>
<div class="clearfix"></div>
</div>
</div>
</div>
</section>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
    });
</script>
