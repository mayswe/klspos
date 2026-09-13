<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header">
      <h4 class="box-title"><?= lang('transfer_details'); ?></h4>
    </div>
    <div class="box-body">
      
      <table class="table table-bordered">
        <tr>
          <th><?= lang('date'); ?></th>
          <td><?= $transfer->date; ?></td>
        </tr>
        <tr>
          <th><?= lang('from_warehouse'); ?></th>
          <td><?= $this->site->getWarehouseNameByID($transfer->from_warehouse); ?></td>
        </tr>
        <tr>
          <th><?= lang('to_warehouse'); ?></th>
          <td><?= $this->site->getWarehouseNameByID($transfer->to_warehouse); ?></td>
        </tr>
        <tr>
          <th><?= lang('note'); ?></th>
          <td><?= $transfer->note; ?></td>
        </tr>
        <tr>
          <th><?= lang('created_by'); ?></th>
          <td><?= $this->site->getUserNameByID($transfer->created_by); ?></td>
        </tr>
      </table>

      <h4><?= lang('transfer_items'); ?></h4>
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th><?= lang('product'); ?></th>
              <th><?= lang('quantity'); ?></th>
              <th><?= lang('unit'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td><?= $this->site->getProductNameByID($item->product_id); ?></td>
                <td><?= $item->quantity; ?></td>
                <td><?= $this->site->getUnitNameByID($item->unit_id); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <a href="<?= site_url('stocktransfers'); ?>" class="btn btn-default"><?= lang('back'); ?></a>

    </div>
  </div>
</section>
