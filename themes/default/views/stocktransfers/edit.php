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

                        <?php echo form_open_multipart("categories/edit/".$category->id);?>
                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group form-group-lg">
                                    <?= lang('code', 'code'); ?>
                                    <?= form_input('code', $category->code, 'class="form-control tip" id="code"  required="required"'); ?>
                                </div>

                                <div class="form-group form-group-lg">
                                    <?= lang('name', 'name'); ?>
                                    <?= form_input('name', $category->name, 'class="form-control tip" id="name"  required="required"'); ?>
                                </div>
                                <div class="form-group">
                                    <?= lang('image', 'image'); ?>
                                    <input type="file" name="userfile" id="image">
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
