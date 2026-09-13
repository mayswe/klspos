<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    
                    <div class="pull-left">
        <h4><?= $page_title; ?></h4>
    </div>
                </div>
                <div class="box-body">
                    
                    <div id="form" class="panel panel-warning">
                        <div class="panel-body">
                            <form id="filterForm">
                             <div class="row">
                                
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="product"><?= lang("container_box"); ?></label>
                                        <?php
                                        $cb[0] = lang("select")." ".lang("container_box");
                                        foreach($container_boxes as $container_box){
                                            $cb[$container_box->id] = $container_box->box_name;
                                        }
                                        echo form_dropdown('container_box', $cb, set_value('container_box'), 'class="form-control select2" style="width:100%" id="container_box"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="start_date"><?= lang("start_date"); ?></label>
                                        <?= form_input('start_date', set_value('start_date'), 'class="form-control datetimepicker" id="start_date"');?>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="end_date"><?= lang("end_date"); ?></label>
                                        <?= form_input('end_date', set_value('end_date'), 'class="form-control datetimepicker" id="end_date"');?>
                                    </div>
                                </div>
                                <div class="col-xs-12">
                                    <button type="submit" class="btn btn-primary"><?= lang("submit"); ?></button>
                                </div>
                            </div>
                            
                            </form>

                        </div>
                    </div>
                    <div class="clearfix"></div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-responsive">
                               <div id="reportResult"></div>

                            </div>
                        </div>
                    </div>

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
            format: 'YYYY-MM-DD HH:mm'
        });
        $('.datepicker').datetimepicker({format: 'YYYY-MM-DD', showClear: true, showClose: true, useCurrent: false, widgetPositioning: {horizontal: 'auto', vertical: 'bottom'}, widgetParent: $('.dataTable tfoot')});
    });
</script>
<script>
$('#filterForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '<?= site_url("reports/get_profit_loss") ?>',
        type: 'POST',
        data: $(this).serialize() + '&<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>',
        dataType: 'json',
        success: function(r) {
            $('#reportResult').html(`
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr><th colspan="2" class="text-center bg-primary"><?= lang('profit_loss_report'); ?></th></tr>
                    </thead>
                    <tbody>
                        <tr><th><?= lang('total_sales'); ?></th><td>${r.total_sales.toLocaleString()}</td></tr>
                        <tr><th><?= lang('discounts'); ?></th><td>${r.discounts.toLocaleString()}</td></tr>
                        <tr><th><?= lang('net_sales'); ?></th><td>${r.net_sales.toLocaleString()}</td></tr>
                        <tr><th><?= lang('cogs'); ?></th><td>${r.cogs.toLocaleString()}</td></tr>
                        <tr><th><?= lang('gross_profit'); ?></th><td>${r.gross_profit.toLocaleString()}</td></tr>
                        <tr><th><?= lang('expenses'); ?></th><td>${r.expenses.toLocaleString()}</td></tr>
                        <tr><th><strong><?= lang('net_profit'); ?></strong></th><td><strong style="color: ${r.net_profit >= 0 ? 'green' : 'red'}">${r.net_profit.toLocaleString()}</strong></td></tr>
                    </tbody>
                </table>
            `);
        },
        error: function(xhr, status, error) {
            console.error('XHR:', xhr);
            console.error('Status:', status);
            console.error('Error:', error);
            
            $('#reportResult').html(`
                <div class="alert alert-danger">
                    <strong>AJAX Error:</strong><br>
                    Status: ${status}<br>
                    Error: ${error}<br>
                    Response:<br>
                    <pre>${xhr.responseText}</pre>
                </div>
            `);
        }

    });
});

</script>