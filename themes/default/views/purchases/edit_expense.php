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
                        <?= form_open_multipart("purchases/edit_expense/".$expense->id); ?>

                        
                            <div class="form-group form-group-lg">
                                <?= lang("date", "date"); ?>
                                <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $expense->date), 'class="form-control datetimepicker" id="date" required="required"'); ?>
                            </div>
                            
                            <div class="form-group form-group-lg">
                                    <?= lang('category', 'category'); ?>
                                    <?php
                                    $cat[''] = lang("select")." ".lang("category");
                                    foreach($categories as $category) {
                                        $cat[$category->id] = $category->name;
                                    }
                                    ?>
                                    <?= form_dropdown('category', $cat, $expense->type_id, 'class="form-control select2 tip" id="category"  required="required"'); ?>
                                </div>
                            <div class="form-group form-group-lg">
                                <?= lang("reference", "reference"); ?>
                                <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $expense->reference), 'class="form-control tip" id="reference"'); ?>
                            </div>
                            <div class="form-group form-group-lg">
                                <?= lang("quantity", "quantity"); ?>
                                <?= form_input('quantity', (isset($_POST['quantity']) ? $_POST['quantity'] : $expense->quantity), 'class="form-control tip" id="quantity"'); ?>
                            </div>
                            <div class="form-group form-group-lg">
                                <?= lang("amount", "amount"); ?>
                                <?= form_input('amount', (isset($_POST['amount']) ? $_POST['amount'] : $expense->amount), 'class="form-control tip" id="amount"'); ?>
                            </div>
                            <div class="form-group form-group-lg">
                                <?= lang("total", "total"); ?>
                                <?= form_input('total', (isset($_POST['total']) ? $_POST['total'] : $expense->total), 'class="form-control tip" id="total"'); ?>
                            </div>
                            <div class="form-group form-group-lg">
                                <?= lang("attachment", "attachment") ?>
                                <input type="file" name="userfile" class="form-control file">
                            </div>

                            <div class="form-group form-group-lg">
                                <?= lang("note", "note"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $expense->note), 'class="form-control redactor" id="note"'); ?>
                            </div>

                            <div class="form-group form-group-lg">
                                <?php echo form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
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

