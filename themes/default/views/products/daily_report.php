<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                  <h4 class="box-title pull-left">Daily Closing Stock Report - Store <?= $store_id ?></h4>
                  <a href="<?= site_url('stocktransfers/add'); ?>" class="btn btn-primary pull-right">
                    <i class="fa fa-plus"></i> <?= lang('add_transfer'); ?>
                  </a>
                </div>
                <div class="clearfix"></div>
                <div class="box-body">
                    <div class="table-responsive">
                         <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Closing Qty</th>
                <th>Unit</th>
                <th>Cost Value</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($closing_stocks as $row): ?>
            <tr>
                <td><?= $row->closing_date ?></td>
                <td><?= $row->product_name ?></td>
                <td><?= $row->closing_qty ?></td>
                <td><?= $row->unit ?></td>
                <td><?= number_format($row->cost_value, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>
