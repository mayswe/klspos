<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";

if ($this->input->post('product')){
    $v .= "&product=".$this->input->post('product');
}
if ($this->input->post('movement_type')) {
    $v .= "&movement_type=" . $this->input->post('movement_type');
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

    var table = $('#PrRData').DataTable({
        'processing': true,
        'serverSide': false, // set true if you want server-side processing
        'ajax': {
            url: '<?=site_url('reports/get_product_trace/'. $v);?>',
            type: 'POST',
            data: function(d) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }
        },
        'columns': [
            { "data": "id", "visible": false },
            { "data": "date" },
            { "data": "movement_type" },
            { "data": "from_store" },
            { "data": "to_store" },
            { "data": "qty_base" },
            { "data": "qty_secondary" },
            { "data": "qty_primary" },
            { "data": "sale_id" },
            { "data": "cogs_cost" },
            { "data": "cogs_total_cost" }
        ],
        "footerCallback": function (tfoot, data, start, end, display) {
            var api = this.api();

            var totalBase = 0;
            var totalSecondary = 0;
            var totalPrimary = 0;
            var totalCogs = 0;
            var totalIncome = 0;

            data.forEach(function(row){
                totalBase += parseFloat(row.qty_base) || 0;
                totalSecondary += parseFloat(row.qty_secondary) || 0;
                totalPrimary += parseFloat(row.qty_primary) || 0;
                totalCogs += parseFloat(row.cogs_total_cost) || 0;

                if(row.movement_type == 'sale'){
                    totalIncome += parseFloat(row.cogs_total_cost) || 0; // or calculate sale price if available
                }
            });

            // Update footer
            $(api.column(5).footer()).html(totalBase);
            $(api.column(6).footer()).html(totalSecondary);
            $(api.column(7).footer()).html(totalPrimary);
            $(api.column(10).footer()).html(totalCogs);

            // Add extra summary if needed (income/profit)
            $('#income_footer').html(totalIncome);
            $('#profit_footer').html(totalIncome - totalCogs);
        },
        "buttons": [
            { extend: 'copyHtml5', footer: true },
            { extend: 'excelHtml5', footer: true },
            { extend: 'csvHtml5', footer: true },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true },
            { extend: 'colvis', text: 'Columns' }
        ]
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
                            <?= form_open("reports/products");?>

                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="product"><?= lang("product"); ?></label>
                                        <?php
                                        $pr[0] = lang("select")." ".lang("product");
                                        foreach($products as $product){
                                            $pr[$product->id] = $product->name;
                                        }
                                        echo form_dropdown('product', $pr, set_value('product'), 'class="form-control select2" style="width:100%" id="product"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="movement_type"><?= lang("movement_type"); ?></label>
                                        <?php
                                        $movement_types = [
                                            '' => lang('select') . ' ' . lang('movement_type'),
                                            'sale' => lang('sale'),
                                            'transfer' => lang('transfer'),
                                            'opening' => lang('opening'),
                                            'purchase' => lang('purchase')
                                        ];
                                        echo form_dropdown('movement_type', $movement_types, set_value('movement_type'), 'class="form-control select2" id="movement_type" style="width:100%"');
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
                                <table id="PrRData" class="table table-striped table-bordered table-hover" style="margin-bottom:5px;">
    <thead>
        <tr class="active">
            <th style="max-width:30px;"><?= lang("id"); ?></th>
            <th><?= lang("date"); ?></th>
            <th><?= lang("movement_type"); ?></th>
            <th><?= lang("from_store"); ?></th>
            <th><?= lang("to_store"); ?></th>
            <th><?= lang("qty_base"); ?></th>
            <th><?= lang("qty_secondary"); ?></th>
            <th><?= lang("qty_primary"); ?></th>
            <th><?= lang("sale_id"); ?></th>
            <th><?= lang("cogs_cost"); ?></th>
            <th><?= lang("cogs_total_cost"); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
        </tr>
    </tbody>
    <tfoot>
    <tr>
        <th colspan="5" style="text-align:right;">Total:</th>
        <th></th> <!-- qty_base -->
        <th></th> <!-- qty_secondary -->
        <th></th> <!-- qty_primary -->
        <th></th> <!-- sale_id -->
        <th></th> <!-- cogs_total_cost -->
        <th></th> <!-- empty or profit -->
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
