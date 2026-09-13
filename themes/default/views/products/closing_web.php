<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";

if ($this->input->post('date')){
    $v .= "&date=".$this->input->post('date');
}

if ($this->input->post('store_id')) {
    $v .= "&store_id=".$this->input->post('store_id');
} 

?>

<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#ClosingData').DataTable({

            'ajax' : { url: '<?=site_url('products/get_closing_balances/'. $v);?>', type: 'POST', "data": function ( d ) {
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
        exportOptions: { columns: [0,1,2,3,4,5] }
    },
    {
        extend: 'csvHtml5',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5] }
    },
    {
        extend: 'pdfHtml5',
        orientation: 'portrait',
        pageSize: 'A4',
        footer: true,
        exportOptions: { columns: [0,1,2,3,4,5] }
    }
],
            "columns": [
            { "data": null, "orderable": false, "render": function(data, type, row, meta) { return meta.row + 1; } },
            { "data": "date" },
            { "data": "store_name" },
            { "data": "product_code" },
            { "data": "product_name" },
            { "data": "qty_base" },
            { "data": "qty_secondary" }
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
        <h4><?= $page_title; ?> <strong>Date:</strong> <?= $display_date ?></h4>
        
    </div>
   

</div>

                <div class="box-body">
                    <div id="form" class="panel panel-warning">
                        <div class="panel-body">
                           
                            <?= form_open("products/closing");?>

                            <div class="row">
                                
                                <div class="col-sm-3">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="date"><?= lang("date"); ?></label>
                                        <?= form_input('date',
                                            set_value('date', date('Y-m-d')),
                                            'class="form-control datetimepicker" id="date"');?>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="store_id"><?= lang("store"); ?></label>
                                        <select name="store_id" id="store_id" class="form-control"> <option value="">All Stores</option> <?php foreach ($stores as $s): ?> <option value="<?= $s->id; ?>"><?= $s->name; ?></option> <?php endforeach; ?> </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary"><?= lang("submit"); ?></button>
                                </div>
                            </div>
                            <?= form_close();?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="table-responsive">
                                <table id="ClosingData" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><?= lang("no"); ?></th>
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("store"); ?></th>
                                    <th><?= lang("product_code"); ?></th>
                                    <th><?= lang("product_name"); ?></th>
                                    <th><?= lang("base_qty"); ?></th>
                                    <th><?= lang("second_qty"); ?></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="5" style="text-align:right"><?= lang("total"); ?></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
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
            format: 'YYYY-MM-DD'
        });
        $('.datepicker').datetimepicker({format: 'YYYY-MM-DD', showClear: true, showClose: true, useCurrent: false, widgetPositioning: {horizontal: 'auto', vertical: 'bottom'}, widgetParent: $('.dataTable tfoot')});
    });
</script>


