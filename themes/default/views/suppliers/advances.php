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
                url: '<?=site_url('suppliers/get_supplieradvances');?>',
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
                { "data": "id", "visible": false },
                { "data": "name" },
                { "data": "date" },
                { "data": "amount", "render": currencyFormat },
                { "data": "note" },
                { "data": "created_at" },
                { "data": "Actions", "searchable": false, "orderable": false }
            ],
            "footerCallback": function (tfoot, data, start, end, display) {
                var api = this.api(), data;
                $(api.column(3).footer()).html(
                    cf(api.column(3).data().reduce(function (a, b) {
                        return pf(a) + pf(b);
                    }, 0))
                );
            }

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
        $('#catData').on('click', '.edit-SupplierAdvances', function() {
            var id = $(this).data('id');
            var supplier_id = $(this).data('supplier_id');
            var date = $(this).data('date');
            var amount = $(this).data('amount');
            var note = $(this).data('note');

            $('#edit_id').val(id);
            $('#edit_supplier_id').val(supplier_id);
            $('#edit_date').val(date);
            $('#edit_amount').val(amount);
            $('#edit_note').val(note);

            $('#editSupplierAdvancesForm').attr('action', '<?= site_url("suppliers/edit_advance/") ?>' + id);
            $('#editSupplierAdvancesModal').modal('show');
        });



    });
</script>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                        <h4 class="box-title pull-left"><?= $page_title; ?></h4>
                        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addSupplierAdvancesModal">
                            <i class="fa fa-plus"></i> <?= lang('add_SupplierAdvances'); ?>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="catData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                            <thead>
                              
                                <tr class="active">
                                    <th style="max-width:30px;"><?= lang("id"); ?></th>
                                    <th><?= lang("name"); ?></th>
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("amount"); ?></th>
                                    <th><?= lang("note"); ?></th>
                                    <th><?= lang("created_at"); ?></th>     
                                    <th style="width:65px;"><?= lang("actions"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <!-- Total Amount Row -->
                                <tr class="active">
                                    <th colspan="3" style="text-align:right"><?= lang('total'); ?>:</th>
                                    <th id="amount-total" style="text-align:right"></th>
                                    <th colspan="3"></th>
                                </tr>
                            
                                <!-- Search Row -->
                                <tr>
                                    <td colspan="7" class="p0">
                                        <input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;">
                                    </td>
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

<div class="modal fade" id="addSupplierAdvancesModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierAdvancesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="addSupplierAdvancesModalLabel"><?= lang('add_SupplierAdvances'); ?></h4>
      </div>
      <div class="modal-body">
        <?= form_open("suppliers/add_advance", 'class="validation" id="addSupplierAdvancesForm"'); ?>

                        <div class="form-group">
                            <label>Supplier</label>
                            <select name="supplier_id" class="form-control" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier) { ?>
                                <option value="<?= $supplier->id; ?>"><?= $supplier->name; ?></option>
                            <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Amount (MMK)</label>
                            <input type="number" name="amount" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Note</label>
                            <textarea name="note" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                        <?= form_close(); ?>

      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="editSupplierAdvancesModal" tabindex="-1" role="dialog" aria-labelledby="editSupplierAdvancesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="editSupplierAdvancesModalLabel"><?= lang('edit_SupplierAdvances'); ?></h4>
      </div>
      <div class="modal-body">
        <?= form_open("", 'class="validation" id="editSupplierAdvancesForm"'); ?>
          <input type="hidden" name="id" id="edit_id">

          <div class="form-group">
            <label><?= lang('supplier'); ?></label>
            <select name="supplier_id" id="edit_supplier_id" class="form-control" required>
              <option value="">Select Supplier</option>
              <?php foreach ($suppliers as $supplier) { ?>
                <option value="<?= $supplier->id; ?>"><?= $supplier->name; ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="form-group">
            <label><?= lang('date'); ?></label>
            <input type="date" name="date" id="edit_date" class="form-control" required>
          </div>

          <div class="form-group">
            <label><?= lang('amount'); ?> (MMK)</label>
            <input type="number" name="amount" id="edit_amount" class="form-control" required>
          </div>

          <div class="form-group">
            <label><?= lang('note'); ?></label>
            <textarea name="note" id="edit_note" class="form-control"></textarea>
          </div>

          <button type="submit" class="btn btn-primary"><?= lang('update'); ?></button>
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