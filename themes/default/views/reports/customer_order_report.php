<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";

if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}

if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
?>


<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#OrderData').DataTable({

            'ajax' : { url: '<?=site_url('reports/get_customer_orders/'. $v);?>', type: 'POST', "data": function ( d ) {
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
        exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }, // adjust columns as per your table
        customize: function (xlsx) {
            var sheet = xlsx.xl.worksheets['sheet1.xml'];

            // Get start and end dates from form inputs
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            // Reference sheetData
            var sheetData = sheet.getElementsByTagName('sheetData')[0];

            // Shift all existing rows (header + data) down by 1
            $(sheetData).find('row').each(function() {
                var r = parseInt($(this).attr('r'));
                $(this).attr('r', r + 1);
                $(this).find('c').each(function() {
                    var cellR = $(this).attr('r');
                    var col = cellR.replace(/\d+/g, '');
                    $(this).attr('r', col + (r + 1));
                });
            });

            // Insert new row at the very top (row 1) with date range
            var row = '<row r="1"><c t="inlineStr" r="A1"><is><t>Date: ' + startDate + ' to ' + endDate + '</t></is></c></row>';
            sheetData.innerHTML = row + sheetData.innerHTML;
        }
    },
    {
        extend: 'copyHtml5',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
    },
    {
        extend: 'csvHtml5',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
    },
    {
        extend: 'pdfHtml5',
        orientation: 'portrait',
        pageSize: 'A4',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
    }
],
            "columns": [
            { data: null },             // Serial number
            { data: "sale_id" },        // Real Sale ID
            { data: "customer_name" },
            { data: "product_name" },
            { data: "price" },
            { data: "qty" },
            { data: "total" },
            { data: "date" }
        ],




            "drawCallback": function(settings) {
                var api = this.api();
                api.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            },
            "footerCallback": function (tfoot, data, start, end, display) {
    var api = this.api();

    // Qty Total (column 5)
    var qtyTotal = api
        .column(5, { page: 'current' })
        .data()
        .reduce(function (a, b) {
            return pf(a) + pf(b);
        }, 0);

    // Grand Total (column 6)
    var grandTotal = api
        .column(6, { page: 'current' })
        .data()
        .reduce(function (a, b) {
            return pf(a) + pf(b);
        }, 0);

    // Show in footer
    $(api.column(5).footer()).html(cf(qtyTotal));
    $(api.column(6).footer()).html(cf(grandTotal));
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


<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <div class="box box-primary">
                <div class="box-header">
                    <a href="#" class="btn btn-default btn-sm toggle_form pull-right">Show/Hide</a>
                    <h4><?= $page_title; ?></h4>
                </div>

                <div class="box-body">

                    <div id="form" class="panel panel-warning">
                        <div class="panel-body">

                            <?= form_open("reports/customer_order_report"); ?>

                            <div class="row">

                                <!-- Customer Dropdown -->
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                        <?php
                                        $cr[0] = lang("select")." ".lang("customer");
                                        foreach($customers as $customer){
                                            $cr[$customer->id] = $customer->name;
                                        }
                                        echo form_dropdown('customer', $cr, set_value('customer'), 'class="form-control select2" style="width:100%" id="customer"');
                                        ?>
                                    </div>
                                </div>

                                <!-- Product Dropdown -->
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="product"><?= lang("product"); ?></label>
                                        <?php
                                        $pr[0] = lang("select") . " " . lang("product");
                                        foreach ($products as $product) {
                                            $pr[$product->id] = $product->name;
                                        }
                                        echo form_dropdown('product', $pr, set_value('product'), 'class="form-control select2" style="width:100%" id="product"');
                                        ?>
                                    </div>
                                </div>

                                <div class="col-xs-12">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>

                            </div>
                            <?= form_close(); ?>

                        </div>
                    </div>

                    <table id="OrderData" class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Sale ID</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Date</th>
        </tr>
    </thead>

    <tfoot>
    <tr>
        <th colspan="5" style="text-align:right">Total:</th>
        <th></th> <!-- Qty Total -->
        <th></th> <!-- Grand Total -->
        <th></th>
    </tr>
</tfoot>
</table>

                </div>
            </div>

        </div>
    </div>
</section>
