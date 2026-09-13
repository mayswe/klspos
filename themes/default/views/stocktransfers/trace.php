<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<script type="text/javascript">
$(document).ready(function() {
    $('#productTraceData').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
        url: '<?= site_url('stocktransfers/get_product_trace/'.$product->id); ?>',
        type: 'GET', // ✅ changed from POST to GET
        data: function(d) {
            d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash() ?>";
        }
    },
    buttons: [
        { extend: 'copyHtml5', exportOptions: { columns: ':visible' }},
        { extend: 'excelHtml5', exportOptions: { columns: ':visible' }},
        { extend: 'csvHtml5', exportOptions: { columns: ':visible' }},
        { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: ':visible' }},
        { extend: 'colvis', text: 'Columns' }
    ],
    order: [[0, 'desc']],
    columns: [
        { data: 'date' },
        { data: 'movement_type' },
        { data: 'qty_base' },
        { data: 'qty_secondary' },
        { data: 'sale_id' },
        { data: 'party_name' },
        { data: 'from_store' },
        { data: 'to_store' },

    ]
});


    // search field
    $('#search_table').on('keyup change', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
            table.search(this.value).draw();
        }
    });
});
</script>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                   
<h1 class="box-title pull-left">
    Product Trace - <?= $product->name; ?>
    <?php if (!empty($opening_date)): ?>
        <span class="text-muted">
            (Opening: <?= date('Y-m-d', strtotime($opening_date)); ?> |
            <?= number_format((float)$opening_bqty, 2); ?> <?= lang('base'); ?> ,
            <?= number_format((float)$opening_sqty, 2); ?> <?= lang('secondary'); ?>)
        </span>
        <br>
    <?php endif; ?>
</h1>


                </div>
                <div class="box-header with-border">
                    <!-- Stock balance info -->
    
                <?php if (!empty($latest_purchase_stock)): ?>
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">
                 လက်ကျန်အခြေအနေ
            </h3>

            <div class="pull-right text-muted">
                Date:
                <strong>
                    <?= date('Y-m-d', strtotime($latest_purchase_stock->purchase_date)); ?>
                </strong>

                <?php if (!empty($latest_purchase_stock->reference)): ?>
                    |
                    Voucher:
                    <strong><?= html_escape($latest_purchase_stock->reference); ?></strong>
                <?php endif; ?>

                <?php if (!empty($latest_purchase_stock->store_name)): ?>
                    |
                    Store:
                    <strong><?= html_escape($latest_purchase_stock->store_name); ?></strong>
                <?php endif; ?>
            </div>
        </div>

        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped" style="margin-bottom:0;">
                    <thead>
                        <tr class="active">
                            <th style="width:25%;">Unit</th>
                            <th class="text-right">ယခင်လက်ကျန်</th>
                            <th class="text-right">အဝယ်ဝင်</th>
                            <th class="text-right">လက်ကျန်အသစ်</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong><?= lang('base'); ?></strong>
                            </td>
                            <td class="text-right">
                                <?= number_format((float) $latest_purchase_stock->primary_stock_before, 2); ?>
                            </td>
                            <td class="text-right text-success">
                                + <?= number_format((float) $latest_purchase_stock->base_in, 2); ?>
                            </td>
                            <td class="text-right">
                                <strong>
                                    <?= number_format((float) $latest_purchase_stock->primary_stock_after, 2); ?>
                                </strong>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <strong><?= lang('secondary'); ?></strong>
                            </td>
                            <td class="text-right">
                                <?= number_format((float) $latest_purchase_stock->secondary_stock_before, 2); ?>
                            </td>
                            <td class="text-right text-success">
                                + <?= number_format((float) $latest_purchase_stock->secondary_in, 2); ?>
                            </td>
                            <td class="text-right">
                                <strong>
                                    <?= number_format((float) $latest_purchase_stock->secondary_stock_after, 2); ?>
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
<?php endif; ?>
                </div>

                
                
                <div class="box-body">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-3 col-sm-6">
                            
                            <?php
                                $total_base = $sh_balance->total_base; // 37.44
                                $small_units_per_base = 12;
                                
                                $base_qty = floor($total_base); // 37
                                $fraction = $total_base - $base_qty; // 0.44
                                $small_units = ceil($fraction * $small_units_per_base); // ceil(0.44*12)=ceil(5.28)=6
                                ?>
                                <div class="info-box bg-aqua">
                                    <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Shop <?= lang('base_stock'); ?></span>
                                        <span class="info-box-number">
                                            <?= $base_qty ?>-<?= $small_units ?> 
                                        </span>
                                    </div>
                                </div>


                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-cube"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Shop <?= lang('secondary_stock'); ?></span>
                                    <span class="info-box-number"><?= $sh_balance->total_secondary; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            
                            <?php
                                $total_base = $wh_balance->total_base; // 37.44
                                $small_units_per_base = 12;
                                
                                $base_qty = floor($total_base); // 37
                                $fraction = $total_base - $base_qty; // 0.44
                                $small_units = ceil($fraction * $small_units_per_base); // ceil(0.44*12)=ceil(5.28)=6
                                ?>
                                <div class="info-box bg-aqua">
                                    <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Warehouse <?= lang('base_stock'); ?></span>
                                        <span class="info-box-number">
                                            <?= $base_qty ?>-<?= $small_units ?> 
                                        </span>
                                    </div>
                                </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-cube"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Warehouse <?= lang('secondary_stock'); ?></span>
                                    <span class="info-box-number"><?= $wh_balance->total_secondary; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table id="productTraceData" class="table table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <tr class="active">
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("movement_type"); ?></th>
                                    <th><?= lang("qty_base"); ?></th>
                                    <th><?= lang("qty_secondary"); ?></th>
                                    <th><?= lang("voucher_id"); ?></th>
                                    <th><?= lang("party_name"); ?></th>
                                    <th><?= lang("from_store"); ?></th>
                                    <th><?= lang("to_store"); ?></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8" class="p0">
                                        <input type="text" class="form-control b0" id="search_table"
                                            placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;">
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
