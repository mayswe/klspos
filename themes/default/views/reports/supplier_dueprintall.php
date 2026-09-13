<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>

<?php if ($modal) { ?>
    <div class="modal-dialog" role="document"<?= $Settings->rtl ? ' dir="rtl"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-times"></i>
                </button>
<?php } else { ?>
<!doctype html>
<html<?= $Settings->rtl ? ' dir="rtl"' : ''; ?>>
<head>
    <meta charset="utf-8">
    <title><?= lang('supplier_due_invoices'); ?></title>
    <base href="<?= base_url() ?>"/>
    <link href="<?= $assets ?>dist/css/styles.css" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        body { color: #000; }
        #wrapper { max-width: 520px; margin: 0 auto; padding-top: 20px; }
        .table th { background: #f5f5f5; }
        .table th, .table td { vertical-align: middle !important; }
        h3 { margin: 5px 0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<?php } ?>

<div id="wrapper">

    <?php foreach ($all_invoices as $invoice): 
        $purchase = $invoice['inv'];
        $supplier = $invoice['supplier'];
        $store    = $invoice['store'];
        $purchase_items = $invoice['rows'];
        $payments = $invoice['payments']; // Payment history
    ?>

    <div id="receiptData" style="width: auto; max-width: 580px; min-width: 250px; margin: 0 auto;">
        <div class="no-print">
            <?php if ($message) { ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?= is_array($message) ? print_r($message, true) : $message; ?>
                </div>
            <?php } ?>
        </div>

        <div id="receipt-data">
            <!-- Store Info -->
           
            <!-- Purchase Info -->
            <p><strong><?= lang('purchase_no'); ?>:</strong> <?= $purchase->id; ?></p>
            <p><strong><?= lang('date'); ?>:</strong> <?= $this->tec->hrsd($purchase->date); ?></p>
            <p><strong><?= lang('supplier'); ?>:</strong> <?= $supplier->name; ?></p>

            <!-- Purchase Items Table -->
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th><?= lang('product'); ?></th>
                        <th><?= lang('qty'); ?></th>
                        <th><?= lang('unit_cost'); ?></th>
                        <th><?= lang('subtotal'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $r = 1; $total = 0; ?>
                    <?php foreach ($purchase_items as $item): ?>
                        <tr>
                            <td><?= $r++; ?></td>
                            <td><?= $item->product_name; ?></td>
                            <td><?= $this->tec->formatQuantity($item->primary_qty); ?></td>
                            <td><?= $this->tec->formatMoney($item->net_unit_cost); ?></td>
                            <td><?= $this->tec->formatMoney($item->subtotal); ?></td>
                        </tr>
                        <?php $total += $item->subtotal; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:right;"><?= lang('total'); ?></th>
                        <th><?= $this->tec->formatMoney($total); ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" style="text-align:right;"><?= lang('paid'); ?></th>
                        <th><?= $this->tec->formatMoney($purchase->paid); ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" style="text-align:right;"><?= lang('balance'); ?></th>
                        <th><?= $this->tec->formatMoney($purchase->total - $purchase->paid); ?></th>
                    </tr>
                </tfoot>
            </table>

            <!-- Payment History -->
            <?php if (!empty($payments)): ?>
                <h4><?= lang('payment_history'); ?></h4>
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('reference_no'); ?></th>
                            <th><?= lang('paid_by'); ?></th>
                            <th><?= lang('amount'); ?></th>
                            <th><?= lang('note'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $pay): ?>
                            <tr>
                                <td><?= $this->tec->hrsd($pay->date); ?></td>
                                <td><?= $pay->reference_no; ?></td>
                                <td><?= $pay->paid_by; ?></td>
                                <td><?= $this->tec->formatMoney($pay->amount); ?></td>
                                <td><?= $pay->note; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

           

        </div>
    </div>

    <hr style="border-top:1px dashed #000; margin:20px 0;">

    <?php endforeach; ?>

    <!-- Buttons -->
    <div id="buttons" class="no-print" style="padding-top:10px;">
        <hr>
        <?php if ($modal) { ?>
            <div class="btn-group btn-group-justified" role="group">
                <div class="btn-group">
                    <button onclick="window.print();" class="btn btn-block btn-primary">
                        <?= lang('print'); ?>
                    </button>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                </div>
            </div>
        <?php } else { ?>
            <span class="pull-right col-xs-12">
                <button onclick="window.print();" class="btn btn-block btn-primary">
                    <?= lang('print'); ?>
                </button>
            </span>
            <span class="col-xs-12">
                <a class="btn btn-block btn-warning" href="<?= site_url('purchases'); ?>">
                    <?= lang('back_to_purchases'); ?>
                </a>
            </span>
        <?php } ?>
    </div>
</div>

<?php if (!$modal) { ?>
<script src="<?= $assets ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="<?= $assets ?>dist/js/libraries.min.js"></script>
<script src="<?= $assets ?>dist/js/scripts.min.js"></script>
<?php } ?>

<?php if ($modal) { ?>
    </div>
</div>
<?php } else { ?>
</body>
</html>
<?php } ?>
