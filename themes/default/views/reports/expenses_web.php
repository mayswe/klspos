<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";
if ($this->input->post('category')){
    $v .= "&category=".$this->input->post('category');
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
    function formatQty(data, type) {
        var num = parseFloat(data || 0);
    
        if (type === 'display') {
            return (num % 1 === 0) ? num : num.toFixed(2);
        }
    
        return num; // ✅ important for sorting
    }

    $(document).ready(function() {

        var table = $('#ExRData').DataTable({
        ajax: {
            url: '<?=site_url('reports/get_expenses/'.$v);?>',
            type: 'POST',
            data: function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }
        },
    
        buttons: [
            { extend: 'copyHtml5', footer: true },
            { extend: 'excelHtml5', footer: true },
            { extend: 'csvHtml5', footer: true },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true },
            { extend: 'colvis', text: 'Columns'}
        ],
    
        columns: [
            { data: "id", visible: false },
            { data: "date" },
            { data: "category" },
            { data: "reference" },
            { data: "amount", render: currencyFormat },
            { data: "quantity" },
            { data: "total", render: currencyFormat }
        ],
    
        footerCallback: function (tfoot, data, start, end, display) {
            var api = this.api();
    
            var total = api.column(6, { page: 'current' }).data().reduce(function (a, b) {
                return pf(a) + pf(b);
            }, 0);
    
            $(api.column(6).footer()).html(cf(total));
        }
    
    }); // ✅ CLOSE DataTable properly here

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
                            <?= form_open("reports/expenses");?>
                            <div class="row">
                                
                                <div class="col-xs-3">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="product"><?= lang("categories"); ?></label>
                                        <?php
                                        $pr[0] = lang("select")." ".lang("categories");
                                        foreach($categories as $category){
                                            $pr[$category->id] = $category->name;
                                        }
                                        echo form_dropdown('category', $pr, set_value('category'), 'class="form-control select2" style="width:100%" id="product"');
                                        ?>
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
    <th><?= lang("date"); ?></th>
    <th><?= lang("category"); ?></th>
    <th><?= lang("reference"); ?></th>
    <th><?= lang("amount"); ?></th>
    <th><?= lang("quantity"); ?></th>
    <th><?= lang("total"); ?></th>
</tr>
</thead>
<tfoot>
<tr>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
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
