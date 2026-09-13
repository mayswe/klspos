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
            url: '<?=site_url('warehouses/get_warehouses');?>',
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
            { "data": "code" },
            { "data": "name" },
            { "data": "address" },
            { "data": "phone" },
            { "data": "email" },
            { "data": "status", "render": status },
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
        $('#catData').on('click', '.edit-warehouses', function() {
          var id = $(this).data('id');
          var name = $(this).data('name');
          var code = $(this).data('code');
          var address = $(this).data('address');
          var phone = $(this).data('phone');
          var email = $(this).data('email');
          var status = $(this).data('status');

          $('#edit_id').val(id);
          $('#edit_name').val(name);
          $('#edit_code').val(code);
          $('#edit_address').val(address);
          $('#edit_phone').val(phone);
          $('#edit_email').val(email);
          $('#edit_status').val(status).trigger('change');

          $('#editWarehouseForm').attr('action', '<?= site_url("warehouses/edit/") ?>' + id);
          $('#editWarehousesModal').modal('show');
      });



    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                        <h4 class="box-title pull-left"><?= $page_title; ?></h4>
                        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addWarehousesModal">
                            <i class="fa fa-plus"></i> <?= lang('add_warehouses'); ?>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="catData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                            <thead>
                              
                                <tr class="active">
                                    <th style="max-width:30px;"><?= lang("id"); ?></th>
                                    <th><?= lang("code"); ?></th>
                                    <th><?= lang('name'); ?></th>
                                    <th><?= lang('address'); ?></th>
                                    <th><?= lang('phone'); ?></th>
                                    <th><?= lang('email'); ?></th>
                                    <th><?= lang('status'); ?></th>
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

<div class="modal fade" id="addWarehousesModal" tabindex="-1" role="dialog" aria-labelledby="addWarehousesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="addWarehousesModalLabel"><?= lang('add_warehouses'); ?></h4>
      </div>
      <div class="modal-body">
        <?= form_open("warehouses/add", 'class="validation" id="addWarehouseForm"'); ?>
          <div class="form-group">
            <?= lang('warehouse_name', 'warehouse_name'); ?>
            <?= form_input('name', set_value('name'), 'class="form-control" id="warehouse_name" required'); ?>
          </div>
          <div class="form-group">
            <?= lang('warehouse_code', 'warehouse_code'); ?>
            <?= form_input('code', set_value('code'), 'class="form-control" id="warehouse_code" required'); ?>
          </div>
          <div class="form-group">
            <?= lang('address', 'address'); ?>
            <?= form_input('address', set_value('address'), 'class="form-control" id="address"'); ?>
          </div>
          <div class="form-group">
            <?= lang('phone', 'phone'); ?>
            <?= form_input('phone', set_value('phone'), 'class="form-control" id="phone"'); ?>
          </div>
          <div class="form-group">
            <?= lang('email', 'email'); ?>
            <?= form_input('email', set_value('email'), 'class="form-control" id="email"'); ?>
          </div>
          <div class="form-group">
            <?= lang('status', 'status'); ?>
            <?php
            $opt = [1 => lang('active'), 0 => lang('inactive')];
            echo form_dropdown('status', $opt, 1, 'id="status" class="form-control select2" style="width:100%;"');
            ?>
          </div>
          <div class="form-group">
            <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
          </div>
          <?= form_close(); ?>

      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="editWarehousesModal" tabindex="-1" role="dialog" aria-labelledby="editWarehousesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="editWarehousesModalLabel"><?= lang('edit_warehouses'); ?></h4>
      </div>
      <div class="modal-body">
        <?= form_open("", 'class="validation" id="editWarehouseForm"'); ?>
          <input type="hidden" name="id" id="edit_id">
          <div class="form-group">
            <?= lang('warehouse_name', 'edit_name'); ?>
            <?= form_input('name', '', 'class="form-control" id="edit_name" required'); ?>
          </div>
          <div class="form-group">
            <?= lang('warehouse_code', 'edit_code'); ?>
            <?= form_input('code', '', 'class="form-control" id="edit_code" required'); ?>
          </div>
          <div class="form-group">
            <?= lang('address', 'edit_address'); ?>
            <?= form_input('address', '', 'class="form-control" id="edit_address"'); ?>
          </div>
          <div class="form-group">
            <?= lang('phone', 'edit_phone'); ?>
            <?= form_input('phone', '', 'class="form-control" id="edit_phone"'); ?>
          </div>
          <div class="form-group">
            <?= lang('email', 'edit_email'); ?>
            <?= form_input('email', '', 'class="form-control" id="edit_email"'); ?>
          </div>
          <div class="form-group">
            <?= lang('status', 'edit_status'); ?>
            <?php
            $status_options = [1 => lang('active'), 0 => lang('inactive')];
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