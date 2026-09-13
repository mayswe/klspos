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
                        <?php echo form_open_multipart("currencies/add", 'class="validation"'); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang('name', 'name'); ?>
                                    <?= form_input('currency_name', set_value('currency_name'), 'class="form-control tip" id="currency_name"  required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang('code', 'code'); ?>
                                    <?= form_input('currency_code', set_value('currency_code'), 'class="form-control tip" id="currency_code"  required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang('rate', 'rate'); ?>
                                    <?= form_input('exchange_rate', set_value('exchange_rate'), 'class="form-control tip" id="exchange_rate"  required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang("date", "date"); ?>
                                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetimepicker" id="date" required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang('status', 'status'); ?>
                                    <?php
                                    $opt = array('' => '', 1 => lang('active'), 0 => lang('inactive'));
                                    echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" data-placeholder="' . lang("select") . ' ' . lang("status") . '" class="form-control input-tip select2" style="width:100%;"');
                                    ?>
                                </div>
                        <div class="form-group form-group-lg">
                            <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
                        </div>

                        <?php echo form_close();?>
                    </div>
                    <div class="clearfix"></div>
                </div>
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
