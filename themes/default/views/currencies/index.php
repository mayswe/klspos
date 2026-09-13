<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
$(document).ready(function() {

    function status(x) {
            var on = '<?= lang('Active'); ?>';
            var off = '<?= lang('Inactive'); ?>';
            if (x == '1') {
                return '<div class="text-center"><span class="sale_status label label-success">'+on+'</span></div>';
            } else {
                return '<div class="text-center"><span class="sale_status label label-default">'+off+'</span></div>';
            }
        }

    var table = $('#catData').DataTable({
        'ajax' : {
            url: '<?=site_url('currencies/get_exchangerate');?>',
            type: 'POST',
            data: function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }
        },
        "buttons": [
            { extend: 'copyHtml5', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] } },
            { extend: 'excelHtml5', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] } },
            { extend: 'csvHtml5',   exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] } },
            { extend: 'pdfHtml5',   orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] } },
            { extend: 'colvis', text: '<?=lang("columns")?>' },
        ],
        "columns": [
            { "data": "id" },
            { "data": "currency_name" },
            { "data": "currency_code" },
            { "data": "exchange_rate" },
            { "data": "status", "render": status },
            { "data": "date" },
            { "data": "Actions", "searchable": false, "orderable": false }
        ]
    });

    $('#search_table').on('keyup change', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if ((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === '')) {
            table.search(this.value).draw();
        }
    });

});
</script>

<script>
    $(document).ready(function() {
        $('#catData').on('click', '.image', function() {
            var a_href = $(this).attr('href');
            var code = $(this).attr('id');
            $('#myModalLabel').text(code);
            $('#product_image').attr('src',a_href);
            $('#picModal').modal();
            return false;
        });
        $('#catData').on('click', '.open-image', function() {
            var a_href = $(this).attr('href');
            var code = $(this).closest('tr').find('.image').attr('id');
            $('#myModalLabel').text(code);
            $('#product_image').attr('src',a_href);
            $('#picModal').modal();
            return false;
        });
        $('#catData').on('click', '.edit-currency', function() {
            var id = $(this).data('id');
            var date = $(this).data('date');
            var name = $(this).data('name');
            var code = $(this).data('code');
            var rate = $(this).data('rate');
            var status = $(this).data('status');

            $('#edit_id').val(id);
            $('#edit_date').val(date);
            $('#edit_currency_name').val(name);
            $('#edit_currency_code').val(code);
            $('#edit_exchange_rate').val(rate);
            $('#edit_status').val(status).trigger('change');

            $('#editCurrencyForm').attr('action', '<?= site_url("currencies/edit/") ?>' + id);
            $('#editCurrencyModal').modal('show');
        });


    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                        <h4 class="box-title pull-left"><?= $page_title; ?></h4>
                        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addCurrencyModal">
                            <i class="fa fa-plus"></i> <?= lang('add_exchangerate'); ?>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="catData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                            <thead>
                                <tr class="active">
                                    <th style="max-width:30px;"><?= lang("id"); ?></th>
                                    <th><?= lang("currency_name"); ?></th>
                                    <th><?= lang('currency_code'); ?></th>
                                    <th><?= lang('exchangerate'); ?></th>
                                    <th><?= lang('status'); ?></th>
                                    <th><?= lang('date'); ?></th>
                                    <th style="width:75px;"><?= lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="addCurrencyModal" tabindex="-1" role="dialog" aria-labelledby="addCurrencyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="addCurrencyModalLabel"><?= lang('add_exchangerate'); ?></h4>
      </div>
      <div class="modal-body">
        <?= form_open_multipart("currencies/add", 'class="validation" id="addCurrencyForm"'); ?>
        <div class="form-group form-group-lg">
          <?= lang('currency_name', 'currency_name'); ?>
          <?= form_input('currency_name', set_value('currency_name'), 'class="form-control" id="currency_name" required'); ?>
        </div>
        <div class="form-group form-group-lg">
          <?= lang('currency_code', 'currency_code'); ?>
          <?= form_input('currency_code', set_value('currency_code'), 'class="form-control" id="currency_code" required'); ?>
        </div>
        <div class="form-group form-group-lg">
          <?= lang('exchangerate', 'exchangerate'); ?>
          <?= form_input('exchange_rate', set_value('exchange_rate'), 'class="form-control" id="exchange_rate" required'); ?>
        </div>
        <div class="form-group form-group-lg">
          <?= lang("date", "date"); ?>
          <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetimepicker" id="date" required'); ?>
        </div>
        <div class="form-group form-group-lg">
          <?= lang('status', 'status'); ?>
          <?php
          $opt = array('' => '', 1 => lang('active'), 0 => lang('inactive'));
          echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" data-placeholder="' . lang("select") . ' ' . lang("status") . '" class="form-control select2" style="width:100%;"');
          ?>
        </div>
        <div class="form-group form-group-lg">
          <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
        </div>
        <?= form_close(); ?>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="editCurrencyModal" tabindex="-1" role="dialog" aria-labelledby="editCurrencyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="editCurrencyModalLabel"><?= lang('edit_exchangerate'); ?></h4>
      </div>
      <div class="modal-body">
        <?= form_open_multipart("", 'class="validation" id="editCurrencyForm"'); ?>
        <input type="hidden" name="id" id="edit_id">

        <div class="form-group form-group-lg">
          <?= lang("date", "edit_date"); ?>
          <?= form_input('date', '', 'class="form-control datetimepicker" id="edit_date" required'); ?>
        </div>
        <div class="form-group form-group-lg">
          <?= lang('currency_name', 'edit_currency_name'); ?>
          <?= form_input('currency_name', '', 'class="form-control" id="edit_currency_name" required'); ?>
        </div>
        <div class="form-group form-group-lg">
          <?= lang('currency_code', 'edit_currency_code'); ?>
          <?= form_input('currency_code', '', 'class="form-control" id="edit_currency_code" required'); ?>
        </div>
        <div class="form-group form-group-lg">
          <?= lang('exchangerate', 'edit_exchange_rate'); ?>
          <?= form_input('exchange_rate', '', 'class="form-control" id="edit_exchange_rate" required'); ?>
        </div>
        <div class="form-group form-group-lg">
          <?= lang('status', 'edit_status'); ?>
          <?php
          $status_options = ['1' => lang('active'), '0' => lang('inactive')];
          echo form_dropdown('status', $status_options, '', 'class="form-control select2" id="edit_status" required');
          ?>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary"><?= lang('update'); ?></button>
        </div>

        <?= form_close(); ?>
      </div>
    </div>
  </div>
</div>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
    });
</script>