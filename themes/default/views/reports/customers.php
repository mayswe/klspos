<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";

if ($this->input->post('customer')){
    $v .= "&customer=".$this->input->post('customer');
}
if ($this->input->post('start_date')){
    $v .= "&start_date=".$this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=".$this->input->post('end_date');
}

?>

<script type="text/javascript">
    $(document).ready(function() {

        function image(n) {
            if (n !== null) {
                return '<div style="width:32px; margin: 0 auto;"><a href="<?=base_url();?>uploads/'+n+'" class="open-image"><img src="<?=base_url();?>uploads/thumbs/'+n+'" alt="" class="img-responsive"></a></div>';
            }
            return '';
        }

        function method(n) {
            return (n == 0) ? '<span class="label label-primary"><?= lang('inclusive'); ?></span>' : '<span class="label label-warning"><?= lang('exclusive'); ?></span>';
        }

        var table = $('#CustomerRData').DataTable({
            'ajax' : { url: '<?=site_url('reports/get_customers/'. $v);?>', type: 'POST', "data": function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }},
            "buttons": [
            { extend: 'copyHtml5', footer: true },
            { extend: 'excelHtml5', footer: true },
            { extend: 'csvHtml5', footer: true },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true },
            { extend: 'colvis', text: '<?= lang("columns"); ?>' },
            ],
            "columns": [
            { "data": "id", "visible": false },
            { "data": "name" },
            { "data": "phone" },
            { "data": "email" },
            { "data": "total_orders" },
            { "data": "total_spent", "render": currencyFormat },
            { "data": "last_purchase" },
            ],
            "footerCallback": function (tfoot, data, start, end, display) {
            var api = this.api();

            // Total Orders (Column 4)
            var totalOrders = api.column(4, { page: 'current' }).data().reduce(function (a, b) {
                return parseFloat(a) + parseFloat(b);
            }, 0);
            $(api.column(4).footer()).html(totalOrders);

            // Total Spent (Column 5)
            var totalSpent = api.column(5, { page: 'current' }).data().reduce(function (a, b) {
                return pf(a) + pf(b);
            }, 0);
            $(api.column(5).footer()).html(cf(totalSpent));
        }

        });

        $('#search_table').on( 'keyup change', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search( this.value ).draw();
            }
        });

        table.columns().every(function () {
            var self = this;
            $( 'input.datepicker', this.footer() ).on('dp.change', function (e) {
                self.search( this.value ).draw();
            });
            $( 'input:not(.datepicker)', this.footer() ).on('keyup change', function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                    self.search( this.value ).draw();
                }
            });
            $( 'select', this.footer() ).on('change', function (e) {
                self.search( this.value ).draw();
            });
        });

    });
</script>

<script type="text/javascript">
    $(document).ready(function(){
        $('#form').hide();
        $('.toggle_form').click(function(){
            $("#form").slideToggle();
            return false;
        });
    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <a href="#" class="btn btn-default btn-sm toggle_form pull-right"><?= lang("show_hide"); ?></a>
                    <div class="pull-left">
        <h4><?= $page_title; ?></h4>
    </div>
                </div>
                <div class="box-body">
                    <div id="form" class="panel panel-warning">
                        <div class="panel-body">
                            <?= form_open("reports/customers");?>

                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                        <?php
                                        $pr[0] = lang("select")." ".lang("customer");
                                        foreach($customers as $customer){
                                            $pr[$customer->id] = $customer->name;
                                        }
                                        echo form_dropdown('customer', $pr, set_value('customer'), 'class="form-control select2" style="width:100%" id="customer"');
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
                            <?= form_close();?>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-responsive">
                                <table id="CustomerRData" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr class="active">
                                            <th><?= lang("id"); ?></th>
                                            <th><?= lang("name"); ?></th>
                                            <th><?= lang("phone"); ?></th>
                                            <th><?= lang("email"); ?></th>
                                            <th><?= lang("total_orders"); ?></th>
                                            <th><?= lang("total_spent"); ?></th>
                                            <th><?= lang("last_purchase"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
    <tr class="active">
        <th><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
        <th><input type="text" class="text_filter" placeholder="[<?= lang('name'); ?>]"></th>
        <th><input type="text" class="text_filter" placeholder="[<?= lang('phone'); ?>]"></th>
        <th><input type="text" class="text_filter" placeholder="[<?= lang('email'); ?>]"></th>
        <th></th>  <!-- No input for total orders -->
        <th></th>  <!-- No input for total spent -->
        <th><input type="text" class="text_filter date_filter" placeholder="[<?= lang('last_purchase'); ?>]" /></th>
    </tr>
    <tr>
        <th colspan="7">
            <input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;">
        </th>
    </tr>
</tfoot>

                                </table>
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
