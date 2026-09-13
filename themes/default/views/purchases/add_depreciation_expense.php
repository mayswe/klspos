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
    <?= form_open_multipart("purchases/add_depreciation"); ?>

    <div class="form-group form-group-lg">
        <?= lang("date", "date"); ?>
        <?= form_input('date', set_value('date'), 'class="form-control datetimepicker" id="date" required'); ?>
    </div>

    <div class="form-group form-group-lg">
        <?= lang('category', 'category'); ?>
        <?php
        $cat[''] = lang("select") . " " . lang("category");
        foreach ($categories as $category) {
            $cat[$category->id] = $category->name;
        }
        ?>
        <?= form_dropdown('category', $cat, set_value('category'), 'class="form-control select2" id="category" required'); ?>
    </div>

    <div class="form-group form-group-lg">
        <?= lang("reference", "reference"); ?>
        <?= form_input('reference', set_value('reference'), 'class="form-control" id="reference"'); ?>
    </div>

    


    <div class="form-group form-group-lg">
        <?= lang("amount", "amount"); ?>
        <input name="amount" type="number" step="0.01" id="amount" value="<?= set_value('amount'); ?>" class="form-control" required />
    </div>

    <!-- Hidden calculated base MMK field -->
    <input type="hidden" name="base_amount_mmk" id="base_amount_mmk" value="">

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
            format: 'YYYY-MM-DD HH:mm'
        });
    });
</script>
<script>
$(document).ready(function () {
    function calculateBaseMMK() {
        var amount = parseFloat($('#amount').val()) || 0;
        var rate = parseFloat($('#exchange_rate').val()) || 1;
        var currency = $('#currency').val();

        let mmk_amount = (currency === 'MMK') ? amount : amount * rate;
        $('#base_amount_mmk').val(mmk_amount.toFixed(2));
    }

    $('#amount, #exchange_rate, #currency').on('change keyup', calculateBaseMMK);
});
</script>
