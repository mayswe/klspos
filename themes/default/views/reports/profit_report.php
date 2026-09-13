<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";

if ($this->input->post('start_date')){
    $v .= "&start_date=".$this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=".$this->input->post('end_date');
}

?>

<script type="text/javascript">
    $(document).ready(function() {
    
     var table = $('#ExRData').DataTable({
            'ajax' : { url: '<?=site_url('reports/get_profit_ajax/'.$v);?>', type: 'POST', "data": function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }},
    "buttons": [
                { extend: 'copyHtml5', 'footer': true },
                { extend: 'excelHtml5', 'footer': true },
                { extend: 'csvHtml5', 'footer': true },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', 'footer': true },
                { extend: 'colvis', text: 'Columns'}
            ],
    columns: [
        { data: "sale_date" },
        { data: "sale_id" },
        { data: "sale_total", className: "text-right" },
        { data: "cogs_total", className: "text-right" },
        { data: "profit", className: "text-right" }
    ],
    footerCallback: function (row, data, start, end, display) {
        var api = this.api();
        let intVal = function (i) {
            return typeof i === 'string' ? parseFloat(i.replace(/[\$,]/g, '')) : typeof i === 'number' ? i : 0;
        };

        let saleTotal = api.column(2).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        let cogsTotal = api.column(3).data().reduce((a, b) => intVal(a) + intVal(b), 0);
        let profitTotal = api.column(4).data().reduce((a, b) => intVal(a) + intVal(b), 0);

        $(api.column(2).footer()).html(saleTotal.toFixed(2));
        $(api.column(3).footer()).html(cogsTotal.toFixed(2));
        $(api.column(4).footer()).html(profitTotal.toFixed(2));
    }
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
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header">
                    <a href="#" class="btn btn-default btn-sm toggle_form pull-right"><?= lang("show_hide"); ?></a>
                    <h4 class="pull-left"><?= $page_title; ?></h4>
                </div>

                <div class="box-body">
                    <div id="form" class="panel panel-warning" style="display:none;">
                        <div class="panel-body">
                            <?= form_open("reports/profit_report"); ?>
                            
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group form-group-lg">
                                        <label class="control-label" for="start_date"><?= lang("start_date"); ?></label>
                                        <?= form_input('start_date', set_value('start_date'), 'class="form-control datetimepicker" id="start_date"');?>
                                    </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group form-group-lg">
                                        <label class="control-label" for="end_date"><?= lang("end_date"); ?></label>
                                        <?= form_input('end_date', set_value('end_date'), 'class="form-control datetimepicker" id="end_date"');?>
                                    </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-primary"><?= lang("submit"); ?></button>
                                    </div>
                                </div>
                            <?= form_close(); ?>
                        </div>
                    </div>

                    <table id="ExRData" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Sale Date</th>
                                <th>Sale ID</th>
                                <th>Sale Total</th>
                                <th>COGS Total</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                            </tr>
                        </tfoot>
                        <tbody></tbody>
                    </table>
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
