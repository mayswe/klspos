<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>

<script type="text/javascript">
$(document).ready(function() {

    function closed_at(x) {
        return (x !== null) ? hrld(x) : '';
    }

    var table = $('#registerTable').DataTable({
        'ajax': { 
            url: '<?=site_url('reports/get_register_logs/'. $v);?>', 
            type: 'POST', 
            data: function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }
        },
        "buttons": [
            { extend: 'copyHtml5', footer: true, exportOptions: { columns: [1,2,3,4,5,6] } },
            { extend: 'excelHtml5', footer: true, exportOptions: { columns: [1,2,3,4,5,6] } },
            { extend: 'csvHtml5', footer: true, exportOptions: { columns: [1,2,3,4,5,6] } },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true, 
              exportOptions: { columns: [1,2,3,4,5,6] } },
            { extend: 'colvis', text: 'Columns' }
        ],
        "columns": [
            { "data": "id", "visible": false },
            { "data": "date", "render": hrld },
            { "data": "closed_at", "render": hrld },
            { "data": "user" },
            { "data": "total_cash", "render": currencyFormat },
            { "data": "total_cash_submitted", "render": currencyFormat },
            { "data": "note" }
        ],
        "footerCallback": function (tfoot, data, start, end, display) {
            var api = this.api();
            var totalCash = api.column(4).data().reduce(function(a, b) { return pf(a) + pf(b); }, 0);
            var totalSubmitted = api.column(6).data().reduce(function(a, b) { return pf(a) + pf(b); }, 0);
            $(api.column(6).footer()).html(
                '<span class="text-success">'+cf(totalSubmitted)+'</span> / <span class="text-info">'+cf(totalCash)+'</span>'
            );
        }
    });

    $('#search_table').on('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
            table.search(this.value).draw();
        }
    });

    table.columns().every(function() {
        var self = this;
        $('input.datepicker', this.footer()).on('dp.change', function() {
            self.search(this.value).draw();
        });
        $('input:not(.datepicker)', this.footer()).on('keyup change', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                self.search(this.value).draw();
            }
        });
        $('select', this.footer()).on('change', function() {
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

<style type="text/css">
.table td:nth-child(5), .table td:nth-child(6) { text-align: center; }
.topborder div { border-top: 1px solid #CCC; }
</style>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <a href="#" class="btn btn-default btn-sm toggle_form pull-right"><?= lang("show_hide"); ?></a>
                    <h3 class="box-title"><?= lang('register_logs_report'); ?>
                        <?php if ($this->input->post('start_date')) { echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date'); } ?>
                    </h3>
                </div>

                <div class="box-body">
                    <div id="form" class="panel panel-warning">
                        <div class="panel-body">
                            <?= form_open("reports/registers"); ?>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="user"><?= lang("user"); ?></label>
                                        <?php
                                        $us[""] = "";
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . " " . $user->last_name;
                                        }
                                        echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 
                                            'class="form-control select2" id="user" data-placeholder="' . lang("select") . " " . lang("user") . '" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group form-group-lg">
                                        <?= lang("start_date", "start_date"); ?>
                                        <?= form_input('start_date', set_value('start_date'), 'class="form-control datetime datetimepicker" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group form-group-lg">
                                        <?= lang("end_date", "end_date"); ?>
                                        <?= form_input('end_date', set_value('end_date', date('Y-m-d H:i')), 'class="form-control datetime datetimepicker" id="end_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary"><?= lang("submit"); ?></button>
                                </div>
                            </div>
                            <?= form_close(); ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="registerTable" class="table table-bordered table-hover table-striped reports-table">
                            <thead>
                                <tr>
                                    <th style="max-width:30px;"><?= lang("id"); ?></th>
                                    <th><?= lang('open_time'); ?></th>
                                    <th><?= lang('close_time'); ?></th>
                                    <th><?= lang('user'); ?></th>
                                    <th><?= lang('total_cash'); ?></th>
                                    <th><?= lang('cash_submitted'); ?></th>
                                    <th><?= lang('note'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="active">
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
                                    <th><span class="datepickercon"><input type="text" class="text_filter datepicker" placeholder="[<?= lang('open_time'); ?>]"></span></th>
                                    <th><span class="datepickercon"><input type="text" class="text_filter datepicker" placeholder="[<?= lang('close_time'); ?>]"></span></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('user'); ?>]"></th>
                                   
                                    <th><?= lang('total_cash'); ?></th>
                                    <th><?= lang('cash_submitted'); ?></th>
                                    <th><?= lang('note'); ?></th>
                                </tr>
                                <tr>
                                    <td colspan="10" class="p0">
                                        <input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;">
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
    $('.datetimepicker').datetimepicker({ format: 'YYYY-MM-DD HH:mm' });
    $('.datepicker').datetimepicker({ format: 'YYYY-MM-DD', showClear: true, showClose: true, useCurrent: false });
});
</script>
