<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4><?= lang('close_register'); ?></h4>
                </div>
                <div class="box-body">
                    <?php
                    $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                    echo form_open("pos/close_register", $attrib);
                    ?>

                    <div class="well well-sm">
                        <div class="form-group">
                            <label><?= lang("cash_in_hand"); ?>:</label>
                            <input type="text" class="form-control" value="<?= $cash_in_hand; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label><?= lang("cash_sales"); ?>:</label>
                            <input type="text" class="form-control" value="<?= $cashsales->paid; ?>" readonly>
                        </div>


                        <div class="form-group">
                            <label><?= lang("expenses"); ?>:</label>
                            <input type="text" class="form-control" value="<?= $expenses->total; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label><?= lang("total_cash"); ?>:</label>
                            <input type="text" class="form-control" value="<?= $total_cash; ?>" readonly>
                            <?= form_hidden('total_cash', $total_cash); ?>
                        </div>

                        <div class="form-group">
                            <label><?= lang("total_cash_submitted"); ?></label>
                            <?= form_input('total_cash_submitted', $total_cash, 'class="form-control" required'); ?>
                        </div>

                        <div class="form-group">
                            <label><?= lang("note"); ?></label>
                            <?= form_textarea('note', '', 'class="form-control" rows="3"'); ?>
                        </div>

                        <div class="form-group">
                            <?= form_submit('close_register', lang('close_register'), 'class="btn btn-danger"'); ?>
                            <a href="<?= site_url('pos'); ?>" class="btn btn-default"><?= lang('back_to_pos'); ?></a>
                        </div>
                    </div>

                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
