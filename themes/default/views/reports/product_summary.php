<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        var table = $('#SummaryData').DataTable({
            'ajax' : {
                url: '<?=site_url('reports/get_product_summary' . $v);?>',
                type: 'POST',
                data: function (d) {
                    d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
                }
            },
            "buttons": [
                { extend: 'copyHtml5', footer: true },
                { extend: 'excelHtml5', footer: true },
                { extend: 'csvHtml5', footer: true },
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true },
                { extend: 'colvis', text: 'Columns' }
            ],
            "columns": [
                { data: "id", visible: false },
                { data: "product_code" },
                { data: "product_name" },
                { data: "total_quantity" },
                { data: "unit" }
            ],
            "footerCallback": function (tfoot, data, start, end, display) {
                var api = this.api();
                $(api.column(3).footer()).html(
                    cf(api.column(3).data().reduce(function (a, b) {
                        return pf(a) + pf(b);
                    }, 0))
                );
            }
        });

        $('#SummaryData tfoot input.text_filter').on('keyup change', function () {
            table
                .column($(this).parent().index())
                .search(this.value)
                .draw();
        });

        $('.toggle_form').click(function () {
            $('#form').slideToggle();
            return false;
        });

        $('#form').hide();
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
                            <?= form_open("reports/product_summary"); ?>
                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="form-group form-group-lg">
                                        <label class="control-label" for="product"><?= lang("product"); ?></label>
                                        <?php
                                        $pr[0] = lang("select") . " " . lang("product");
                                        foreach ($products as $product) {
                                            $pr[$product->id] = $product->name;
                                        }
                                        echo form_dropdown('product', $pr, set_value('product'), 'class="form-control select2" style="width:100%" id="product"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-xs-12">
                                    <button type="submit" class="btn btn-primary"><?= lang("submit"); ?></button>
                                </div>
                            </div>
                            <?= form_close(); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-responsive">
                                <table id="SummaryData" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= lang("id"); ?></th>
                                            <th><?= lang("product_code"); ?></th>
                                            <th><?= lang("product_name"); ?></th>
                                            <th><?= lang("quantity"); ?></th>
                                            <th><?= lang("unit"); ?></th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th><input type="text" class="text_filter" placeholder="<?= lang("code"); ?>" /></th>
                                            <th><input type="text" class="text_filter" placeholder="<?= lang("name"); ?>" /></th>
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
