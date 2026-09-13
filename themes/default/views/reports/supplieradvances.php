<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";

if ($this->input->post('suppliers')){
    $v .= "&suppliers=".$this->input->post('suppliers');
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

        var table = $('#ExRData').DataTable({
            'ajax' : { url: '<?=site_url('reports/get_supplieradvances/'.$v);?>', type: 'POST', "data": function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }},
            "buttons": [
                { extend: 'copyHtml5', 'footer': true },
                { extend: 'excelHtml5', 'footer': true },
                { extend: 'csvHtml5', 'footer': true },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', 'footer': true },
                { extend: 'colvis', text: 'Columns'},
            ],
            "columns": [
                { "data": "id", "visible": false },
                { "data": "supplier_name" },
                { "data": "total_advance", "className": "text-right" },
                { "data": "deducted_amount", "className": "text-right" },
                { "data": "remaining_balance", "className": "text-right" }
            ],

            
        });


        $('#search_table').on( 'keyup change', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search( this.value ).draw();
            }
        });

        table.columns().every(function () {
            var self = this;
            $('input.datepicker', this.footer()).on('dp.change', function (e) {
                self.search(this.value).draw();
            });
            $('input:not(.datepicker)', this.footer()).on('keyup change', function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                    self.search(this.value).draw();
                }
            });
            $('select', this.footer()).on('change', function (e) {
                self.search(this.value).draw();
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
                            <?= form_open("reports/supplieradvances");?>
                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="suppliers"><?= lang("suppliers"); ?></label>
                                        <?php
                                        $pr[0] = lang("select")." ".lang("suppliers");
                                        foreach($suppliers as $supplier){
                                            $pr[$supplier->id] = $supplier->name;
                                        }
                                        echo form_dropdown('supplier', $pr, set_value('supplier'), 'class="form-control select2" style="width:100%" id="supplier"');
                                        ?>
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
                                <table id="ExRData" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= lang("id"); ?></th>
                                            <th><?= lang("supplier_name"); ?></th>
                                            <th><?= lang("total_advance"); ?></th>
                                            <th><?= lang("deducted_amount"); ?></th>
                                            <th><?= lang("remaining_balance"); ?></th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th><input type="text" class="text_filter" placeholder="<?= lang("supplier_name"); ?>" /></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
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