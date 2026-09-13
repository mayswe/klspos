<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
?>

<script type="text/javascript">
    function formatQty(data, type) {
        var num = parseFloat(data || 0);
    
        if (type === 'display') {
            return (num % 1 === 0) ? num : num.toFixed(2);
        }
    
        return num; // ✅ important for sorting
    }

    $(document).ready(function() {

        var table = $('#ProductSalesData').DataTable({

            'ajax' : { url: '<?=site_url('reports/get_product_stock/'. $v);?>', type: 'POST', "data": function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }},
            "paging": false,
            "info": false,
            "searching": true,
            "ordering": true,
            "columnDefs": [
                { "orderable": false, "targets": 0 } // disable ordering on Serial No
            ],
            "buttons": [
    {
        extend: 'excelHtml5',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5,6] }, // adjust columns as per your table
        
    },
    {
        extend: 'copyHtml5',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5,6] }
    },
    {
        extend: 'csvHtml5',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5,6] }
    },
    {
        extend: 'pdfHtml5',
        orientation: 'portrait',
        pageSize: 'A4',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5,6] }
    }
],
    "columns": [
    {
        "data": null,
        "defaultContent": "",
        "orderable": false,
        "render": function (data, type, row, meta) {
            return meta.row + 1;
        }
    },
    { "data": "product_code" },
    { "data": "product_name" },

    { "data": "store1_balance1", "render": formatQty },
    { "data": "store1_balance2", "render": formatQty },
    { "data": "store2_balance1", "render": formatQty },
    { "data": "store2_balance2", "render": formatQty }
],




            "drawCallback": function(settings) {
                var api = this.api();
                api.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            },
            "footerCallback": function (tfoot, data, start, end, display) {
    var api = this.api();

    // Sum total_amount (column 5)
    var totalAmount = api
        .column(5, { page: 'current' }) // current page only
        .data()
        .reduce(function (a, b) {
            return pf(a) + pf(b);
        }, 0);

    // Format and set footer
    $(api.column(5).footer()).html(cf(totalAmount));
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
            $( 'select', this.footer() ).on( 'change', function (e) {
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
        <div class="col-sm-12">
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
                            <?= form_open("reports/stocks");?>

                            <div class="row">
                                
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="product"><?= lang("product"); ?></label>
                                        <?php
                                        $pr[''] = lang("all_products"); // or 'All Products'
                                        foreach ($products as $product) {
                                            $pr[$product->id] = $product->name;
                                        }
                                        echo form_dropdown('product', $pr, set_value('product'), 'class="form-control select2" style="width:100%" id="product"');
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary"><?= lang("search"); ?></button>
                                </div>
                            </div>
                            <?= form_close();?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-responsive">
                                <table id="ProductSalesData" class="table table-bordered table-striped">
    <thead>
<tr>
    <th><?= lang("no."); ?></th>
    <th><?= lang("product_code"); ?></th>
    <th><?= lang("product_name"); ?></th>
    <th><?= lang("sh_base_qty"); ?></th> 
    <th><?= lang("sh_second_qty"); ?></th> 
    <th><?= lang("wh_base_qty"); ?></th> 
    <th><?= lang("wh_second_qty"); ?></th> 
</tr>

</thead>



</table>

                            </div>
                        </div>
                    </div>

                    <?php if ($this->input->post('customer')) { ?>
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn bg-purple btn-lg btn-block" style="cursor:default;">
                                <strong><?= $this->tec->formatMoney($total_sales->number, 0); ?></strong>
                                <?= lang("sales"); ?>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-lg btn-block" style="cursor:default;">
                                <strong><?= $this->tec->formatMoney($total_sales->amount); ?></strong>
                                <?= lang("amount"); ?>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success btn-lg btn-block" style="cursor:default;">
                                <strong><?= $this->tec->formatMoney($total_sales->paid); ?></strong>
                                <?= lang("paid"); ?>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning btn-lg btn-block" style="cursor:default;">
                                <strong><?= $this->tec->formatMoney($total_sales->amount-$total_sales->paid); ?></strong>
                                <?= lang("due"); ?>
                            </button>
                        </div>
                    </div>
                    <?php } ?>

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


