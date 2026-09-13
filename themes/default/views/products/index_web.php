<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<style type="text/css">
    .table td:first-child { padding: 1px; }
    /* Center and right alignment */
    .table td:nth-child(6),
    .table td:nth-child(8),
    .table td:nth-child(10) { text-align: right; }
    .table td:nth-child(5),
    .table td:nth-child(7),
    .table td:nth-child(9) { text-align: center; }
</style>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header clearfix">
                    <div class="clearfix d-flex align-items-center justify-content-between flex-wrap">

            <?php if ($Admin && $qty_alert_num && $this->session->userdata('store_id')) { ?>
                <div class="alert alert-warning mb-3 d-flex align-items-center" style="border-left: 5px solid #f39c12; background: #fff8e1; color: #c0392b; font-size: 16px;">
                    <i class="fa fa-bullhorn fa-lg mr-2" style="margin-right: 10px;"></i>
                    <a href="<?= site_url('reports/alerts'); ?>" style="text-decoration:none; color:#c0392b;">
                        ပစ္စည်းအမျိုးအစား 
                        <strong><?= $qty_alert_num; ?></strong> 
                        ကျော် အသစ်ထပ်မံ ဝယ်ယူထည့်သွင်းရန် လိုအပ်နေပါသည်။
                    </a>
                </div>
            <?php } ?>
            </div>
                    <div class="pull-left">
                        <h4><?= $page_title; ?></h4>
                    </div>
                    <div class="pull-right btn-group">
                        <?php if (!$this->session->userdata('has_store_id')) { ?>
                        <div class="btn-group" style="margin-right:5px;">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                <?= $store->name . ' (' . $store->code . ')'; ?> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <?php foreach ($stores as $st) {
                                    if ($store->id != $st->id) {
                                        echo "<li><a href='" . site_url('products/?store_id=' . $st->id) . "'>{$st->name} ({$st->code})</a></li>";
                                    }
                                } ?>
                            </ul>
                        </div>
                        <?php } ?>
                        <a href="<?= site_url('products/add'); ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> <?= lang('add_product'); ?>
                        </a>
                    </div>
                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table id="prTables" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr class="active">
                                    <th><?= lang("id"); ?></th>
                                    <th><?= lang("image"); ?></th>
                                    <th><?= lang("code"); ?></th>
                                    <th><?= lang("name"); ?></th>
                                    <th><?= lang("category"); ?></th>
                                    <th><?= lang("base_quantity"); ?></th>
                                    <th><?= lang("base_unit"); ?></th>
                                    <th><?= lang("secondary_quantity"); ?></th>
                                    <th><?= lang("secondary_unit"); ?></th>
                                    <th><?= lang("cost"); ?></th>
                                    <th><?= lang("Dual"); ?></th>
                                    <th><?= lang("actions"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
                                    <th><?= lang("image"); ?></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('code'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('name'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('category'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('base_quantity'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('base_unit'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('secondary_quantity'); ?>]"></th>
                                    <th><input type="text" class="text_filter" placeholder="[<?= lang('secondary_unit'); ?>]"></th>
                                    <th><?= lang("cost"); ?></th>
                                    <th><?= lang("Dual"); ?></th>
                                    <th><?= lang("actions"); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Image Modal -->
                    <div class="modal fade" id="picModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><i class="fa fa-times"></i></button>
                                    <button type="button" class="close mr10" onclick="window.print();"><i class="fa fa-print"></i></button>
                                    <h4 class="modal-title" id="myModalLabel">title</h4>
                                </div>
                                <div class="modal-body text-center">
                                    <img class="img-responsive" id="product_image" src="" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
$(document).ready(function() {

    function image(n) {
        if (n !== null && n !== '') {
            return '<div style="width:32px; margin:0 auto;">' +
                   '<a href="<?= base_url(); ?>uploads/' + n + '" class="open-image">' +
                   '<img src="<?= base_url(); ?>uploads/thumbs/' + n + '" class="img-responsive"></a></div>';
        }
        return '';
    }
    
    // Render payment status
    function is_dual_unit(data, type, row) {
        var yes = '<?= lang('Yes'); ?>';
        var no = '<?= lang('No'); ?>';
        if (data == '1') {
            return '<div class="text-center"><span class="sale_status label label-success">'+yes+'</span></div>';
        } else if (data == '0') {
            return '<div class="text-center"><span class="sale_status label label-danger">'+no+'</span></div>';
        }
    }

    function quantityFormat(q) { return q ? parseFloat(q).toFixed(2) : '0.00'; }
    function currencyFormat(c) { return c ? parseFloat(c).toFixed(2) : '0.00'; }

    var table = $('#prTables').DataTable({
        'ajax': {
            url: '<?= site_url('products/get_products/'.$store->id); ?>',
            type: 'POST',
            data: function(d) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }
        },
        "columns": [
            { "data": "pid", "visible": false },
            { "data": "image", "searchable": false, "orderable": false, "render": image },
            { "data": "code" },
            { "data": "pname" },
            { "data": "cname" },
            { "data": "base_quantity", "render": quantityFormat },
            { "data": "base_unit_name" },
            { "data": "secondary_quantity", "render": quantityFormat },
            { "data": "secondary_unit_name" },
            { "data": "base_price", "render": currencyFormat, "searchable": false },
            { "data": "is_dual_unit", "render": is_dual_unit },
            { "data": "Actions", "searchable": false, "orderable": false }
        ],
        "buttons": [
            { extend: 'copyHtml5', exportOptions: { columns: ':visible' } },
            { extend: 'excelHtml5', exportOptions: { columns: ':visible' } },
            { extend: 'csvHtml5', exportOptions: { columns: ':visible' } },
            { extend: 'pdfHtml5', exportOptions: { columns: ':visible' }, orientation: 'landscape', pageSize: 'A4' },
            { extend: 'colvis', text: 'Columns' }
        ],
        "order": [[3, 'asc']],
        "initComplete": function() {
            // Apply footer search
            this.api().columns().every(function() {
                var that = this;
                $('input', this.footer()).on('keyup change clear', function() {
                    if (that.search() !== this.value) {
                        that.search(this.value).draw();
                    }
                });
            });
        }
        
    });

    // Search on Enter
    $('#search_table').on('keyup change', function(e) {
        var code = e.keyCode || e.which;
        if ((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === '')) {
            table.search(this.value).draw();
        }
    });

    // Open Image Modal
    $('#prTables').on('click', '.open-image', function(e) {
        e.preventDefault();
        var a_href = $(this).attr('href');
        var code = $(this).closest('tr').find('td:eq(2)').text(); // product code
        $('#myModalLabel').text(code);
        $('#product_image').attr('src', a_href);
        $('#picModal').modal();
    });

});
</script>
