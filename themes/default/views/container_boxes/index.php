<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#GCData').DataTable({

            'ajax' : { url: '<?=site_url('container_boxes/get_container_boxes');?>', type: 'POST', "data": function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }},
            "buttons": [
            { extend: 'copyHtml5', 'footer': false, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] } },
            { extend: 'excelHtml5', 'footer': false, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] } },
            { extend: 'csvHtml5', 'footer': false, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] } },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', 'footer': false,
            exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] } },
            { extend: 'colvis', text: 'Columns'},
            ],
            "columns": [
            { "data": "id", "visible": true },
            { "data": "box_name" },
            { "data": "created_at" },
            { "data": "Actions", "searchable": false, "orderable": false }
            ]

        });

        $('#search_table').on( 'keyup change', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search( this.value ).draw();
            }
        });

    });
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4 class="pull-left"><?= $page_title; ?></h4>
                    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addContainerboxModal">
                        <i class="fa fa-plus"></i> <?= lang('add_container_box'); ?>
                    </button>
                </div>
                <div class="clearfix"></div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="GCData" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th style="max-width:30px;"><?= lang("id"); ?></th>
                                    <th><?= lang("box_name"); ?></th>
                                    <th><?= lang("created_at"); ?></th>
                                    <th style="width:75px;"><?= lang("actions"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="addContainerboxModal" tabindex="-1" role="dialog" aria-labelledby="addContainerboxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="addContainerboxModalLabel"><?= lang('add_container_box'); ?></h4>
            </div>
            <div class="modal-body">
                        <?php $attrib = array('class' => 'validation', 'role' => 'form');
                        echo form_open("container_boxes/add", $attrib); ?>
                        
                            
                                <div class="form-group form-group-lg">
                                    <?= lang("box_name", "box_name"); ?>
                                    <div class="input-group">
                                        <?php echo form_input('box_name', '', 'class="form-control" id="box_name" required="required"'); ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                           id="genNo"><i
                                           class="fa fa-cogs"></i></a></div>
                                       </div>
                                   </div>
                                   
                                <div class="form-group form-group-lg">
                                    <?= form_submit('create', lang('create'), 'class="btn btn-primary"'); ?>
                                </div>
                            
                        
                        <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

</section>
<script src="<?= $assets ?>plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
<script type="text/javascript">

    $(document).ready(function () {
        $('#genNo').click(function () {
            var button = $(this);
            $.ajax({
                type: 'GET',
                url: '<?= site_url('container_boxes/generate_box_name'); ?>',
                success: function (data) {
                    button.closest('.input-group').find('input').val(data);
                },
                error: function () {
                    alert('Could not generate box name.');
                }
            });
            return false;
        });

    });
</script>    