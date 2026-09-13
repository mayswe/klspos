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
                        <?php $attrib = array('class' => 'validation', 'role' => 'form');
                        echo form_open("container_boxes/add", $attrib); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-group-lg">
                                    <?= lang("card_no", "card_no"); ?>
                                    <div class="input-group">
                                        <?php echo form_input('card_no', '', 'class="form-control" id="card_no" required="required"'); ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                           id="genNo"><i
                                           class="fa fa-cogs"></i></a></div>
                                       </div>
                                   </div>
                                   <div class="form-group form-group-lg">
                                    <?= lang("value", "value"); ?>
                                    <?php echo form_input('value', '', 'class="form-control" id="value" required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang("expiry_date", "expiry"); ?>
                                    <?php echo form_input('expiry', '', 'class="form-control date" id="expiry"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>

                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="<?= $assets ?>plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
<script type="text/javascript">

    $(document).ready(function () {
        $('#card_no').inputmask("9999 9999 9999 9999");
        $('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
        $("#expiry").inputmask("yyyy-mm-dd", {"placeholder": "yyyy-mm-dd"});
    });
</script>    