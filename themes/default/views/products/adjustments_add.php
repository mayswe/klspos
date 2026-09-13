<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4 class="box-title"><?= lang('add_product_adjustment'); ?></h4>
                </div>
                <div class="box-body">
                    <div class="col-lg-12">
                        
                        <?php echo form_open_multipart("adjustments/add", 'class="validation"'); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang('store', 'store'); ?>
                                    <select name="store_id" id="store_id" class="form-control select" required>
                                        <option value=""><?= lang('select').' '.lang('store'); ?></option>
                                        <?php foreach ($stores as $s): ?>
                                            <option value="<?= $s->id ?>"><?= $s->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <?= lang('warehouse', 'warehouse'); ?>
                                    <select name="store_id" id="store_id" class="form-control select" required>
                                        <option value=""><?= lang('select').' '.lang('warehouse'); ?></option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <?= lang('product', 'product'); ?>
                                    <select name="product_id" class="form-control select" required>
                                        <option value=""><?= lang('select').' '.lang('product'); ?></option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= $p->id ?>"><?= $p->name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <?= lang('quantity', 'quantity'); ?>
                                    <?= form_input('quantity', set_value('quantity'), 'class="form-control tip" id="quantity" required="required"'); ?>
                                </div>

                                <div class="form-group">
                                    <?= lang('adjustment_type', 'adjustment_type'); ?>
                                    <select name="adjustment_type" class="form-control select" required>
                                        <option value="damage"><?= lang('damage'); ?></option>
                                        <option value="lost"><?= lang('lost'); ?></option>
                                        <option value="expired"><?= lang('expired'); ?></option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <?= lang('note', 'note'); ?>
                                    <?= form_textarea('note', set_value('note'), 'class="form-control" id="note" style="height:80px;"'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <?= form_submit('create', lang('submit'), 'class="btn btn-primary"'); ?>
                        </div>

                        <?= form_close(); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>


