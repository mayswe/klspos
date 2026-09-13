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
                url: '<?= site_url('categories/get_categories'); ?>',
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
        $('#catData').on('click', '.edit-category', function() {
            var id = $(this).data('id');
            var code = $(this).data('code');
            var name = $(this).data('name');
            var image = $(this).data('image');

            $('#edit_category_id').val(id);
            $('#edit_category_code').val(code);
            $('#edit_category_name').val(name);

            $('#editCategoryModal').modal('show');
        });

    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4 class="pull-left"><?= $page_title; ?></h4>
                    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addCategoryModal">
                        <i class="fa fa-plus"></i> <?= lang('add_category'); ?>
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
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="addCategoryModalLabel"><?= lang('add_category'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart("categories/add", 'class="validation"'); ?>


                <div class="form-group form-group-lg">
                    <?= lang('code', 'code'); ?>
                    <?= form_input('code', set_value('code'), 'class="form-control tip" id="code"  required="required"'); ?>
                </div>
                <div class="form-group form-group-lg">
                    <?= lang('name', 'name'); ?>
                    <?= form_input('name', set_value('name'), 'class="form-control tip" id="name"  required="required"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('image', 'image'); ?>
                    <input type="file" name="userfile" id="image">
                </div>



                <div class="form-group form-group-lg">
                    <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('categories/update') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h4 class="modal-title"><?= lang('edit_category') ?></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_category_id">
                    <div class="form-group">
                        <label><?= lang('code') ?></label>
                        <input type="text" class="form-control" name="code" id="edit_category_code" required>
                    </div>
                    <div class="form-group">
                        <label><?= lang('name') ?></label>
                        <input type="text" class="form-control" name="name" id="edit_category_name" required>
                    </div>
                    <div class="form-group">
                        <label><?= lang('image') ?></label>
                        <input type="file" name="userfile">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?= lang('update') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>