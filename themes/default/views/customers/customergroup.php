<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>

<script type="text/javascript">
    $(document).ready(function() {

        function image(n) {
            if (n !== null) {
                return '<div style="width:32px; margin: 0 auto;"><a href="<?= base_url(); ?>uploads/' + n + '" class="open-image"><img src="<?= base_url(); ?>uploads/thumbs/' + n + '" alt="" class="img-responsive"></a></div>';
            }
            return '';
        }

        var table = $('#catData').DataTable({

            'ajax': {
                url: '<?= site_url('customers/get_customergroup'); ?>',
                type: 'POST',
                "data": function(d) {
                    d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash() ?>";
                }
            },
            "buttons": [{
                    extend: 'copyHtml5',
                    'footer': false,
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'excelHtml5',
                    'footer': false,
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'csvHtml5',
                    'footer': false,
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    'footer': false,
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'colvis',
                    text: 'Columns'
                },
            ],
            "columns": [{
                    "data": "id",
                    "visible": false
                },
                {
                    "data": "image",
                    "searchable": false,
                    "orderable": false,
                    "render": image
                },
                {
                    "data": "code"
                },
                {
                    "data": "name"
                },
                {
                    "data": "Actions",
                    "searchable": false,
                    "orderable": false
                }
            ]

        });

        $('#search_table').on('keyup change', function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
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
            $('#product_image').attr('src', a_href);
            $('#picModal').modal();
            return false;
        });
        $('#catData').on('click', '.open-image', function() {
            var a_href = $(this).attr('href');
            var code = $(this).closest('tr').find('.image').attr('id');
            $('#myModalLabel').text(code);
            $('#product_image').attr('src', a_href);
            $('#picModal').modal();
            return false;
        });
        $('#catData').on('click', '.edit-customergroup', function() {
            var id = $(this).data('id');
            var code = $(this).data('code');
            var name = $(this).data('name');
            var image = $(this).data('image');

            $('#edit_customergroup_id').val(id);
            $('#edit_customergroup_code').val(code);
            $('#edit_customergroup_name').val(name);

            $('#editCustomergroupModal').modal('show');
        });

    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4 class="pull-left"><?= $page_title; ?></h4>
                    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addCustomergroupModal">
                        <i class="fa fa-plus"></i> <?= lang('add_customergroup'); ?>
                    </button>
                </div>
                <div class="clearfix"></div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table id="catData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                            <thead>
                                <tr class="active">
                                    <th style="max-width:30px;"><?= lang("id"); ?></th>
                                    <th style="max-width:30px;"><?= lang("image"); ?></th>
                                    <th><?= lang('code'); ?></th>
                                    <th><?= lang('name'); ?></th>
                                    <th style="width:75px;"><?= lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
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
<div class="modal fade" id="addCustomergroupModal" tabindex="-1" role="dialog" aria-labelledby="addCustomergroupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="addCustomergroupModalLabel"><?= lang('add_customergroup'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart("customers/customergroupadd", 'class="validation"'); ?>


                <div class="form-group form-group-lg">
                    <?= lang('code', 'code'); ?>
                    <?= form_input('code', set_value('code'), 'class="form-control tip" id="code"  required="required"'); ?>
                </div>
                <div class="form-group form-group-lg">
                    <?= lang('name', 'name'); ?>
                    <?= form_input('name', set_value('name'), 'class="form-control tip" id="name"  required="required"'); ?>
                </div>
               



                <div class="form-group form-group-lg">
                    <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editCustomergroupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open_multipart("customers/customergroupupdate", 'class="validation" id="editCustomergroupForm"'); ?>
            <input type="hidden" name="id" id="edit_customergroup_id">
                <div class="modal-header">
                    <h4 class="modal-title"><?= lang('edit_customergroup') ?></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><?= lang('code') ?></label>
                        <input type="text" class="form-control" name="code" id="edit_customergroup_code" required>
                    </div>
                    <div class="form-group">
                        <label><?= lang('name') ?></label>
                        <input type="text" class="form-control" name="name" id="edit_customergroup_name" required>
                    </div>
                    <div class="form-group">
                        <label><?= lang('customers') ?></label>
                        <select name="customers[]" 
                                id="edit_customergroup_customers" 
                                class="form-control select2" 
                                multiple="multiple"
                                style="width:100%;">
                            
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer->id ?>">
                                    <?= $customer->name ?>
                                </option>
                            <?php endforeach; ?>

                            
                        </select>
                    </div>

                  
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?= lang('update') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>