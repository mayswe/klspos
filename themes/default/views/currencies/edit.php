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

                        <?php echo form_open_multipart("currencies/edit/".$currency->id);?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang("date", "date"); ?>
                                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $currency->date), 'class="form-control datetimepicker" id="date" required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang('name', 'name'); ?>
                                    <?= form_input('currency_name', $currency->currency_name, 'class="form-control tip" id="code"  required="required"'); ?>
                                </div>
                                
                                <div class="form-group form-group-lg">
                                    <?= lang('code', 'code'); ?>
                                    <?= form_input('currency_code', $currency->currency_code, 'class="form-control tip" id="code"  required="required"'); ?>
                                </div>
                                
                                <div class="form-group form-group-lg">
                                    <?= lang('exchangerate', 'exchangerate'); ?>
                                    <?= form_input('exchange_rate', $currency->exchange_rate, 'class="form-control tip" id="code"  required="required"'); ?>
                                </div>

                                <div class="form-group form-group-lg">
                                    <?= lang('status', 'status'); ?>
                                    <?php
                                        $status_options = ['1' => 'Active', '0' => 'Inactive'];
                                        echo form_dropdown('status', $status_options, (isset($_POST['status']) ? $_POST['status'] : $currency->status), 'class="form-control" id="status" required="required"');
                                    ?>
                                </div>

                                
                            </div>
                        </div>

                        <div class="form-group form-group-lg">
                            <?= form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
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
