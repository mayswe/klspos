<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4><?= $page_title; ?></h4>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="well well-sm col-sm-6">
                                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'open-register-form');
                                echo form_open_multipart("pos/open_register", $attrib); ?>
                                <div class="form-group form-group-lg">
                                    <?= lang('cash_in_hand', 'cash_in_hand') ?>
                                    <input type="hidden" name="cash_in_hand" value="4864021">
                                    <p class="form-control-static">4864021</p>
                                </div>

                                <?php echo form_submit('open_register', lang('open_register'), 'class="btn btn-primary"'); ?>
                                <?php echo form_close(); ?>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>