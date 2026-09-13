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
            url: '<?=site_url('products/get_units');?>',
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
            { "data": "name" },
            { "data": "code" },
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
        $('#catData').on('click', '.edit-unit', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var code = $(this).data('code');

            $('#edit_unit_id').val(id);
            $('#edit_name').val(name);
            $('#edit_code').val(code);

            $('#editUnitModal').modal('show');
        });

    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4 class="box-title pull-left"><?= $page_title; ?></h4>
                    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addUnitModal">
                        <i class="fa fa-plus"></i> <?= lang('add_unit'); ?>
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
                                    <th><?= lang('code'); ?></th>
                                    <th style="width:75px;"><?= lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="clearfix"></div>
                    <div class="modal fade" id="addUnitModal" tabindex="-1" role="dialog" aria-labelledby="addUnitModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" id="addUnitModalLabel"><?= lang('add_unit'); ?></h4>
                            </div>
                            <div class="modal-body">
                                <?= form_open_multipart("products/add_unit", 'class="validation" id="addUnitForm"'); ?>
                                <div class="form-group">
                                <?= lang('name', 'name'); ?>
                                <?= form_input('name', set_value('name'), 'class="form-control" id="name" required="required"'); ?>
                                </div>
                                <div class="form-group">
                                <?= lang('code', 'code'); ?>
                                <?= form_input('code', set_value('code'), 'class="form-control" id="code" required="required"'); ?>
                                </div>
                                <div class="form-group">
                                <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
                                </div>
                                <?= form_close(); ?>
                            </div>
                            </div>
                        </div>
                        </div>

                
                    </div>
                    <div class="modal fade" id="editUnitModal" tabindex="-1" role="dialog" aria-labelledby="editUnitModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" id="editUnitModalLabel"><?= lang('edit_unit'); ?></h4>
                            </div>
                            <div class="modal-body">
                                <?= form_open_multipart("products/edit_unit", 'class="validation" id="editUnitForm"'); ?>
                                <input type="hidden" name="id" id="edit_unit_id">
                                <div class="form-group">
                                <?= lang('name', 'edit_name'); ?>
                                <?= form_input('name', '', 'class="form-control" id="edit_name" required="required"'); ?>
                                </div>
                                <div class="form-group">
                                <?= lang('code', 'edit_code'); ?>
                                <?= form_input('code', '', 'class="form-control" id="edit_code" required="required"'); ?>
                                </div>
                                <div class="form-group">
                                <button type="submit" class="btn btn-primary"><?= lang('update'); ?></button>
                                </div>
                                <?= form_close(); ?>
                            </div>
                            </div>
                        </div>
                        </div>

            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="picModal" tabindex="-1" role="dialog" aria-labelledby="picModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div class="modal-body text-center">
                <img id="product_image" src="" alt="" />
            </div>
        </div>
    </div>
</div>
