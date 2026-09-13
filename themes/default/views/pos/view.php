<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>

<?php
if ($modal) {
    ?>
    <div class="modal-dialog" role="document"<?= $Settings->rtl ? ' dir="rtl"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
<?php
} else {
?>
<!doctype html>
<html<?= $Settings->rtl ? ' dir="rtl"' : ''; ?>>
<head>
<meta charset="utf-8">
<title><?= $page_title . ' ' . lang('no.') . ' ' . $inv->id; ?></title>
<base href="<?= base_url() ?>"/>
<meta http-equiv="cache-control" content="max-age=0"/>
<meta http-equiv="cache-control" content="no-cache"/>
<meta http-equiv="expires" content="0"/>
<meta http-equiv="pragma" content="no-cache"/>
<link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
<link href="<?= $assets ?>dist/css/styles.css" rel="stylesheet" type="text/css" />
<style type="text/css" media="all">
body { color: #000; }
#wrapper { max-width: 520px; margin: 0 auto; padding-top: 20px; }
.btn { margin-bottom: 5px; }
.table { border-radius: 3px; }
.table th { background: #f5f5f5; }
.table th, .table td { vertical-align: middle !important; }
h3 { margin: 5px 0; }

@media print {
    .no-print { display: none; }
    #wrapper { max-width: 480px; width: 100%; min-width: 250px; margin: 0 auto; }
    table tfoot { display: table-row-group; }
}
<?php if ($Settings->rtl) { ?>
.text-right { text-align: left; }
.text-left { text-align: right; }
tfoot tr th:first-child { text-align: left; }
<?php } else { ?>
tfoot tr th:first-child { text-align: right; }
<?php } ?>
</style>
<style>
    .watermark {
    position: absolute;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 80px;
    font-weight: bold;
    opacity: 0.12;
    z-index: 1;
    pointer-events: none;
    white-space: nowrap;
}

.watermark-paid {
    color: #28a745;
}

.watermark-due {
    color: #dc3545;
}

#receipt-data {
    position: relative;
    z-index: 2;
}
</style>
</head>
<body>
<?php
}
?>
<div id="wrapper">
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
            <div>
                <table class="table table-striped table-condensed">
                    <tbody>
                        <tr>
                            <td style="vertical-align:middle;">
                                <?php
                                    if ($store) {
                                        echo '<img src="' . base_url('uploads/' . $store->logo) . '" alt="' . $store->name . '">';
                                    }
                                ?>
                            </td>
                            <td style="text-align:right; vertical-align:middle;">
                                <?php
                                if ($store) {
                                    
                                    echo '<h3 style="text-align:center;">';
                                    echo '' . $store->name . '</h3><br>';
                                    echo '<p style="text-align:center; font-size: 13px;">';
                                    echo $store->address1 . '</p>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr><td colspan="2"><?php
                                if ($store) {
                                    
                                    echo '<p style="text-align:center;">';
                                    echo  $store->address2;
                                    echo $store->city . '<br>' . $store->phone;
                                    echo '</p>';
                                }
                                ?></td></tr>
                    </tbody>
                    
                </table>
                
                <hr>
                <?php if ($is_preorder) { ?>
                    <div style="text-align:center; font-weight:bold; color:red;"><p>Preorder Sale</p></div>
                <?php } ?>
                <table class="table">
                    <tr>
                        <td><p>
                    <?= lang('date') . ': ' . $this->tec->hrld($inv->date); ?> <br>
                    <?= lang('sale_no_ref') . ': ' . $inv->id; ?><br>
                    <?= lang('customer') . ': ' . $inv->customer_name . ' (' . $customer->phone . ')'; ?> <br>
                    <?= lang('sales_person') . ': ' . $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
                </p></td>
                        <td>
                            <?php
                $is_paid = ($inv->paid >= $inv->grand_total);
        ?>
                <div style="position: relative; z-index: 2;">
<?php if ($is_paid) { ?>
    <div style="
    width:130px;
    height:130px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    font-weight:bold;
    color:#28a745;
    border:4px solid #28a745;
    border-radius:50%;
    background:#f8fff9;
    text-align:center;
">
    ငွေရှင်းပြီး
</div>


<?php } else { ?>
    <div style="
        
        top:30px;
        right:10px;
        padding:6px 14px;
        font-size:22px;
        font-weight:bold;
        color:#dc3545;
        border:3px solid #dc3545;
        border-radius:6px;
        z-index:5;
        background:#fff;
        text-align:center;
    ">
        ငွေရှင်းရန်ကျန်
    </div>
<?php } ?>


</div>
                        </td>
                    </tr>
                </table>
                
                <div style="clear:both;"></div>
                
                <table class="table table-striped table-condensed">
    <thead>
        <tr>
            <th class="text-center" style="width: 45%;"><?= lang('product'); ?></th>
            <th class="text-center" style="width: 15%;"><?= lang('qty'); ?></th>
            <th class="text-center" style="width: 20%;"><?= lang('စျေးနှုန်း'); ?></th>
            <th class="text-center" style="width: 20%;"><?= lang('ကျသင့်ငွေ'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($rows as $row) {
            // Highlight preorder items
            $preorder_class = $row->preorder_qty > 0 ? ' style="background:#fff3cd;"' : '';
            echo '<tr' . $preorder_class . '>';
            echo '<td>';
            echo $row->product_name;
            if (!empty($row->unit_name)) {
                echo ' <small>(' . $row->unit_name . ')</small>';
            }
            
            echo '</td>';
            if ($row->preorder_qty > 0) {
                echo '<td style="text-align:center;">' . $row->preorder_qty . '</td>';
            }else{
                echo '<td style="text-align:center;">' . $row->quantity . '</td>';
            }
            
            echo '<td style="text-align:center;">' . $this->tec->formatMoney($row->net_unit_price + ($row->item_tax / max($row->quantity, 1))) . '</td>';
            echo '<td class="text-right">' . $this->tec->formatMoney($row->subtotal) . '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
    <tfoot>
                                        <tr>
                                            <th class="text-left" colspan="2"><?= lang('total'); ?></th>
                                            <th colspan="2" class="text-right"><?= $this->tec->formatMoney($inv->total + $inv->product_tax); ?></th>
                                        </tr>
                                        <?php
                                        if ($inv->order_tax != 0) {
                                            echo '<tr><th class="text-left" colspan="2">' . lang('order_tax') . '</th><th colspan="2" class="text-right">' . $this->tec->formatMoney($inv->order_tax) . '</th></tr>';
                                        }
                                        if ($inv->total_discount != 0) {
                                            echo '<tr><th class="text-left" colspan="2">' . lang('order_discount') . '</th><th colspan="2" class="text-right">' . $this->tec->formatMoney($inv->total_discount) . '</th></tr>';
                                        }
                                        if ($inv->total_tax != 0) {
                                            echo '<tr><th class="text-left" colspan="2">' . lang('tax') . '</th><th colspan="2" class="text-right">' . $this->tec->formatMoney($inv->total_tax) . '</th></tr>';
                                        }

                                        if ($Settings->rounding) {
                                            $round_total = $this->tec->roundNumber($inv->grand_total, $Settings->rounding);
                                            $rounding    = $this->tec->formatDecimal($round_total - $inv->grand_total); ?>
                                            <tr>
                                                <th class="text-left" colspan="2"><?= lang('rounding'); ?></th>
                                                <th colspan="2" class="text-right"><?= $this->tec->formatMoney($rounding); ?></th>
                                            </tr>
                                            <tr>
                                                <th class="text-left" colspan="2"><?= lang('grand_total'); ?></th>
                                                <th colspan="2" class="text-right"><?= $this->tec->formatMoney($inv->grand_total + $rounding); ?></th>
                                            </tr>
                                            <?php
                                        } else {
                                            $round_total = $inv->grand_total; ?>
                                            <tr>
                                                <th class="text-left" colspan="2"><?= lang('grand_total'); ?></th>
                                                <th colspan="2" class="text-right"><?= $this->tec->formatMoney($inv->grand_total); ?></th>
                                            </tr>
                                            <?php
                                        }
                                        if ($inv->paid < $round_total) { ?>
                                        <tr>
                                            <th class="text-left" colspan="2"><?= lang('paid_amount'); ?></th>
                                            <th colspan="2" class="text-right"><?= $this->tec->formatMoney($inv->paid); ?></th>
                                        </tr>
                                        <tr>
                                            <th class="text-left" colspan="2"><?= lang('due_amount'); ?></th>
                                            <th colspan="2" class="text-right"><?= $this->tec->formatMoney($inv->grand_total - $inv->paid); ?></th>
                                        </tr>
                                        <?php } ?>
                                        
                                    </tfoot>
</table>


                <?php
                                if ($payments) {
                                    echo '<table class="table table-striped table-condensed" style="margin-top:10px; font-size:20px; font-weight:bold"><tbody>';
                                    foreach ($payments as $payment) {
                                        echo '<tr>';
                                        if ($payment->paid_by == 'cash' && $payment->pos_paid) {
                                            echo '<td class="text-right" style="font-size:22px;">' . lang('paid') . ' :</td><td style="font-size:22px;">' . $this->tec->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . '</td>';
                                            
                                            echo '<td class="text-right">' . lang('change') . ' :</td><td>' . ($payment->pos_balance > 0 ? $this->tec->formatMoney($payment->pos_balance) : 0) . '</td>';
                                        }
                                        if ($payment->paid_by == 'other' && $payment->amount) {
                                            echo '<td class="text-right">' . lang('paid') . ' :</td><td>' . lang($payment->paid_by) . '</td>';
                                            echo '<td class="text-right">' . lang('amount') . ' :</td><td>' . $this->tec->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid) . '</td>';
                                            echo $payment->note ? '</tr><td colspan="2">' . lang('payment_note') . ' :</td><td>' . $payment->note . '</td>' : '';
                                        }
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                }

                                ?>

                <?= $inv->note ? '<p style="margin-top:10px; text-align: center;">' . $this->tec->decode_html($inv->note) . '</p>' : ''; ?>
                <?php if (!empty($store->receipt_footer)) { ?>
                <div class="well well-sm"  style="margin-top:10px;">
                    
                    <div style="text-align: center;"><?= nl2br($store->receipt_footer); ?></div>
                </div>
                <?php } ?>
            </div>
            <div style="clear:both;"></div>
        </div>

        <!-- buttons -->
        <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">
            <hr>
            <?php if ($modal) { ?>
            <div class="btn-group btn-group-justified" role="group">
                <div class="btn-group">
                    <?php
                    if (!$Settings->remote_printing) {
                        echo '<a href="' . site_url('pos/print_receipt/' . $inv->id . '/0') . '" id="print" class="btn btn-block btn-primary">' . lang('print') . '</a>';
                    } else {
                        echo '<button onclick="window.print();" class="btn btn-block btn-primary">' . lang('print') . '</button>';
                    }
                    ?>
                </div>
                <div class="btn-group"><a class="btn btn-block btn-success" href="#" id="email"><?= lang('email'); ?></a></div>
                <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button></div>
            </div>
            <?php } else { ?>
            <span class="pull-right col-xs-12">
                <?php
                if (!$Settings->remote_printing) {
                    echo '<a href="' . site_url('pos/print_receipt/' . $inv->id . '/1') . '" id="print" class="btn btn-block btn-primary">' . lang('print') . '</a>';
                } else {
                    echo '<button onclick="window.print();" class="btn btn-block btn-primary">' . lang('print') . '</button>';
                }
                ?>
            </span>
            <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?= lang('email'); ?></a></span>
            <span class="col-xs-12"><a class="btn btn-block btn-warning" href="<?= site_url('pos'); ?>"><?= lang('back_to_pos'); ?></a></span>
            <?php } ?>
            <div style="clear:both;"></div>
        </div>
        <!-- end buttons -->

    </div>
</div>

<?php if (!$modal) { ?>
<script src="<?= $assets ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="<?= $assets ?>dist/js/libraries.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>dist/js/scripts.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#print').click(function (e) {
        e.preventDefault();
        var link = $(this).attr('href');
        $.get(link);
        return false;
    });
    $('#email').click(function () {
        bootbox.prompt({
            title: "<?= lang('email_address'); ?>",
            inputType: 'email',
            value: "<?= $customer->email; ?>",
            callback: function (email) {
                if (email != null) {
                    $.ajax({
                        type: "post",
                        url: "<?= site_url('pos/email_receipt') ?>",
                        data: {<?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: <?= $inv->id; ?>},
                        dataType: "json",
                        success: function (data) {
                            bootbox.alert({message: data.msg, size: 'small'});
                        },
                        error: function () {
                            bootbox.alert({message: '<?= lang('ajax_request_failed'); ?>', size: 'small'});
                            return false;
                        }
                    });
                }
            }
        });
        return false;
    });
});
</script>
<?php include 'remote_printing.php'; ?>
<?php } ?>

<?php if ($modal) { ?>
            </div>
        </div>
    </div>
<?php } else { ?>
</body>
</html>
<?php } ?>
