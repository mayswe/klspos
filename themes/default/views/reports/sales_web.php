<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";

if ($this->input->post('customer')){
    $v .= "&customer=".$this->input->post('customer');
}
if ($this->input->post('user')){
    $v .= "&user=".$this->input->post('user');
}
if ($this->input->post('status')){
    $v .= "&status=".$this->input->post('status');
}
if ($this->input->post('start_date')){
    $v .= "&start_date=".$this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=".$this->input->post('end_date');
}

?>
<?php

// Monday of current week
$start_date = date('Y-m-d 00:00:00', strtotime('monday this week'));

// Saturday of current week
$end_date = date('Y-m-d 23:59:59', strtotime('saturday this week'));

?>
<script type="text/javascript">
    $(document).ready(function() {

        function status(x) {
            var paid = '<?= lang('paid'); ?>';
            var partial = '<?= lang('partial'); ?>';
            var due = '<?= lang('due'); ?>';
            if (x == 'paid') {
                return '<div class="text-center"><span class="sale_status label label-success">'+paid+'</span></div>';
            } else if (x == 'partial') {
                return '<div class="text-center"><span class="sale_status label label-primary">'+partial+'</span></div>';
            } else if (x == 'due') {
                return '<div class="text-center"><span class="sale_status label label-danger">'+due+'</span></div>';
            } else {
                return '<div class="text-center"><span class="sale_status label label-default">'+x+'</span></div>';
            }
        }

        var table = $('#SLRData').DataTable({

            'ajax' : { url: '<?=site_url('reports/get_sales/'. $v);?>', type: 'POST', "data": function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }},
            "buttons": [
            { extend: 'copyHtml5', 'footer': true, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] } },
            { extend: 'excelHtml5', 'footer': true, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] } },
            { extend: 'csvHtml5', 'footer': true, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] } },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', 'footer': true,
            exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] } },
            { extend: 'colvis', text: 'Columns'},
            ],
           "columns": [
    { "data": "id", "visible": false },  // 👈 hide raw ID if not needed
    { "data": "date", "render": hrld },
    { "data": "customer_name" },
    { "data": "total", "render": currencyFormat },
    { "data": "total_tax", "render": currencyFormat },
    { "data": "total_discount", "render": currencyFormat },
    { "data": "grand_total", "render": currencyFormat },
    { "data": "paid", "render": currencyFormat },
    { "data": "balance", "render": currencyFormat },
    { "data": "status", "render": status },
    { "data": "actions", "orderable": false, "searchable": false } // 👈 NEW ACTIONS COLUMN
],

            "footerCallback": function (  tfoot, data, start, end, display ) {
                var api = this.api(), data;
                $(api.column(3).footer()).html( cf(api.column(3).data().reduce( function (a, b) { return pf(a) + pf(b); }, 0)) );
                $(api.column(4).footer()).html( cf(api.column(4).data().reduce( function (a, b) { return pf(a) + pf(b); }, 0)) );
                $(api.column(5).footer()).html( cf(api.column(5).data().reduce( function (a, b) { return pf(a) + pf(b); }, 0)) );
                $(api.column(6).footer()).html( cf(api.column(6).data().reduce( function (a, b) { return pf(a) + pf(b); }, 0)) );
                $(api.column(7).footer()).html( cf(api.column(7).data().reduce( function (a, b) { return pf(a) + pf(b); }, 0)) );
                $(api.column(8).footer()).html( cf(api.column(8).data().reduce( function (a, b) { return pf(a) + pf(b); }, 0)) );
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
        
        // ✅ Custom AND filter logic
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    var table = $('#SLRData').DataTable();

    var match = true;

    // Loop through all footer inputs/selects
    table.columns().every(function(i) {
        var input = $('input, select', this.footer());
        if (input.length > 0) {
            var filterVal = input.val()?.toLowerCase() || '';
            if (filterVal !== '') {
                var cellData = (data[i] + '').toLowerCase();
                if (cellData.indexOf(filterVal) === -1) {
                    match = false; // one column doesn't match → exclude row
                    return false;  // break loop early
                }
            }
        }
    });

    return match; // ✅ only include if ALL filters match
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
                            <?= form_open("reports");?>

                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                        <?php
                                        $cu[0] = lang("select")." ".lang("customer");
                                        foreach($customers as $customer){
                                            $cu[$customer->id] = $customer->name;
                                        }
                                        echo form_dropdown('customer', $cu, set_value('customer'), 'class="form-control select2" style="width:100%" id="customer"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="customer"><?= lang("status"); ?></label>
                                        <select name="status" class="form-control select2" style="width:100%" id="status">
                                            <option value="" <?= set_select('status', '', TRUE); ?>>All</option>
                                            <option value="paid" <?= set_select('status', 'paid'); ?>>Paid</option>
                                            <option value="partial" <?= set_select('status', 'partial'); ?>>Partial</option>
                                            <option value="due" <?= set_select('status', 'due'); ?>>Due</option>
                                        </select>

                                    </div>
                                </div>
                                
                                
                                

                                <div class="col-sm-3">
                                    <label><?= lang("start_date"); ?></label>
                                    <?= form_input(
                                        'start_date',
                                        set_value('start_date', $start_date),
                                        'class="form-control datetimepicker" id="start_date"'
                                    ); ?>
                                </div>
                                <div class="col-sm-3">
                                    <label><?= lang("end_date"); ?></label>
                                    <?= form_input(
                                        'end_date',
                                        set_value('end_date', $end_date),
                                        'class="form-control datetimepicker" id="end_date"'
                                    ); ?>
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
                                <table id="SLRData" class="table table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                        <tr class="active">
                                            <th style="max-width:30px;"><?= lang("id"); ?></th>
                                            <th class="col-sm-2"><?= lang("date"); ?></th>
                                            <th class="col-sm-2"><?= lang("customer"); ?></th>
                                            <th class="col-sm-1"><?= lang("total"); ?></th>
                                            <th class="col-sm-1"><?= lang("tax"); ?></th>
                                            <th class="col-sm-1"><?= lang("discount"); ?></th>
                                            <th class="col-sm-2"><?= lang("grand_total"); ?></th>
                                            <th class="col-sm-1"><?= lang("paid"); ?></th>
                                            <th class="col-sm-1"><?= lang("balance"); ?></th>
                                            <th class="col-sm-1"><?= lang("status"); ?></th>
                                            <th class="col-sm-1"><?= lang("View"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="active">
                                            <th style="max-width:30px;"><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
                                            <th class="col-sm-2"><span class="datepickercon"><input type="text" class="text_filter datepicker" placeholder="[<?= lang('date'); ?>]"></span></th>
                                            <th class="col-sm-2"><input type="text" class="text_filter" placeholder="[<?= lang('customer'); ?>]"></th>
                                            <th class="col-sm-1"><?= lang("total"); ?></th>
                                            <th class="col-sm-1"><?= lang("tax"); ?></th>
                                            <th class="col-sm-1"><?= lang("discount"); ?></th>
                                            <th class="col-sm-2"><?= lang("grand_total"); ?></th>
                                            <th class="col-sm-1"><?= lang("paid"); ?></th>
                                            <th class="col-sm-1"><?= lang("balance"); ?></th>
                                            <th class="col-sm-1">
                                                <select class="select2 select_filter"><option value=""><?= lang("all"); ?></option><option value="paid"><?= lang("paid"); ?></option><option value="partial"><?= lang("partial"); ?></option><option value="due"><?= lang("due"); ?></option></select>
                                            </th>
                                             <th class="col-sm-1"><?= lang("action"); ?></th>
                                        </tr>
                                        <tr>
                                            <td colspan="11" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
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
                <strong><?= $this->tec->formatMoney($total_sales->amount - $total_sales->paid); ?></strong>
                <?= lang("due"); ?>
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3" style="margin-top:15px">
            <a href="<?= site_url('reports/dueprintall/' . $this->input->post('customer') . '/1'); ?>" 
               class="btn btn-primary btn-lg tip" 
               title="<?= lang('view_invoice'); ?>" 
               data-toggle="ajax-modal">
                <i class="fa fa-list"></i> <?= lang('print_detail'); ?>
            </a>
        </div>
        <div class="col-md-3" style="margin-top:15px">
            <a href="<?= site_url('reports/dueprint/' . $this->input->post('customer') . '/1'); ?>" 
               class="btn btn-primary btn-lg tip" 
               title="<?= lang('view_invoice'); ?>" 
               data-toggle="ajax-modal">
                <i class="fa fa-list"></i> <?= lang('print'); ?>
            </a>
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
