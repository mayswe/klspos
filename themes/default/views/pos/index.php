<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $page_title . ' | ' . $Settings->site_name; ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link href="<?= $assets ?>dist/css/styles.css" rel="stylesheet" type="text/css" />
    <link href="<?= $assets ?>/fontawesome/css/fontawesome.css" rel="stylesheet" type="text/css" />
    <link href="<?= $assets ?>/fontawesome/css/solid.css" rel="stylesheet" type="text/css" />
    <link href="<?= $assets ?>/fontawesome/css/brands.css" rel="stylesheet" type="text/css" />
    <?= $Settings->rtl ? '<link href="' . $assets . 'dist/css/rtl.css" rel="stylesheet" />' : ''; ?>
    <script src="<?= $assets ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <style>
    @media print {
      html, body, .pos, .modal, .modal-dialog {
        min-width: 200px !important;
        width: auto !important;
      }
    }
    #list-table-div {
    max-width: 100%;
    overflow-x: auto;
}

.table-responsive table {
    min-width: 600px; /* optional: ensures table doesn’t shrink too much */
}

@media (max-width: 768px) {
    #posTable thead, .fixed-table-header table thead {
        display: none; /* hide headers on very small screens */
    }

    #posTable tbody tr {
        display: block;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        padding: 8px;
        border-radius: 6px;
    }

    #posTable tbody td {
        display: flex;
        justify-content: space-between;
        padding: 6px 8px;
        border-bottom: 1px solid #eee;
    }

    #posTable tbody td:before {
        content: attr(data-label);
        font-weight: bold;
    }
}

    </style>
    <style>
.custom-table{
    width:100%;
    table-layout:fixed;
}

.custom-table th,
.custom-table td{
    vertical-align:middle;
    text-align:center;
    white-space:nowrap;
}

.custom-table .col-product{
    width:25%;
    text-align:left;
}

.custom-table .col-action{
    width:60px;
}

.dual-billing-qty {
    margin-top: 3px;
    white-space: normal;
    line-height: 1.2;
}
</style>
</head>
<body class="skin-<?= $Settings->theme_style; ?> sidebar-collapse sidebar-mini pos">
    <?php if ($this->session->userdata('store_id')) { ?>

<style>
#stockAlertBox{
    position: fixed;
    top: 20px;
    right: 20px;
    width: 360px;
    z-index: 9999;
    background: #fff8e1;
    border-left: 5px solid #f39c12;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    padding: 15px 20px;
    animation: slideIn 0.4s ease;
}

#stockAlertBox .close-btn{
    position: absolute;
    top: 8px;
    right: 12px;
    cursor: pointer;
    font-size: 18px;
    color: #999;
}

#stockAlertBox .close-btn:hover{
    color: #333;
}

#stockAlertBox a{
    text-decoration: none;
    color: #c0392b;
    font-size: 15px;
    line-height: 24px;
    display: block;
    padding-right: 20px;
}

#stockAlertBox i{
    margin-right: 8px;
    color: #f39c12;
}

@keyframes slideIn{
    from{
        opacity: 0;
        transform: translateX(100%);
    }
    to{
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<div id="stockAlertBox">
    
    <span class="close-btn" onclick="closeStockAlert()">
        &times;
    </span>

    <a href="<?= site_url('reports/alerts'); ?>">
        <i class="fa fa-bell"></i>
        ပစ္စည်းအမျိုးအစား 
        <strong><?= $qty_alert_num; ?></strong> 
        ကျော် အသစ်ထပ်မံ ဝယ်ယူထည့်သွင်းရန် လိုအပ်နေပါသည်။
    </a>

</div>

<script>

function closeStockAlert() {
    $('#stockAlertBox').fadeOut();
}

// Auto hide after 5 seconds
setTimeout(function() {
    $('#stockAlertBox').fadeOut();
}, 5000);

</script>

<?php } ?>
    <div class="wrapper rtl rtl-inv">

        <header class="main-header">
            <?php if (!$Sales) { ?>
                <a href="<?= site_url(); ?>" class="logo">
            <?php } else { ?>
                <div class="logo">
            <?php } ?>

                <?php if ($store) { ?>
                    <span class="logo-mini"><?= $store->code; ?></span>
                    <span class="logo-lg"><?= $store->name == 'SimplePOS' ? 'Simple<b>POS</b>' : $store->name; ?></span>
                <?php } else { ?>
                    <span class="logo-mini">POS</span>
                    <span class="logo-lg"><?= $Settings->site_name == 'SimplePOS' ? 'Simple<b>POS</b>' : $Settings->site_name; ?></span>
                <?php } ?>

            <?php if (!$Sales) { ?>
                </a>
            <?php } else { ?>
                </div>
            <?php } ?>

            <nav class="navbar navbar-static-top" role="navigation">
                <ul class="nav navbar-nav pull-left">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="<?= $assets; ?>images/<?= $Settings->selected_language; ?>.png" alt="<?= $Settings->selected_language; ?>"></a>
                        <ul class="dropdown-menu">
                            <?php $scanned_lang_dir = array_map(function ($path) {
    return basename($path);
                            }, glob(APPPATH . 'language/*', GLOB_ONLYDIR));
                                                                                             foreach ($scanned_lang_dir as $entry) { ?>
                            <li><a href="<?= site_url('pos/language/' . $entry); ?>"><img
                                src="<?= $assets; ?>images/<?= $entry; ?>.png"
                                class="language-img"> &nbsp;&nbsp;<?= ucwords($entry); ?></a></li>
                                                                                             <?php } ?>
                            </ul>
                        </li>
                    </ul>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li><a href="#" class="clock"></a></li>
                            <?php if (!$Sales) { ?>
                                <li><a href="<?= site_url(); ?>"><i class="fa fa-dashboard"></i></a></li>
                                <li></li><button id="fullscreen-btn" class="btn btn-dark">
    <i class="fas fa-expand"></i> Fullscreen
</button></li>

                            <?php } ?>
                            <?php if ($Admin) { ?>
                            <!--li><a href="<?= site_url('settings'); ?>"><i class="fa fa-cogs"></i></a></li-->
                            <?php } ?>
                            <?php if ($this->db->dbdriver != 'sqlite3') { ?>
                            <li><a href="<?= site_url('pos/view_bill'); ?>" target="_blank"><i class="fa fa-desktop"></i></a></li>
                            <?php } ?>
                            <li class="hidden-xs hidden-sm"><a href="<?= site_url('pos/shortcuts'); ?>" data-toggle="ajax"><i class="fa fa-key"></i></a></li>
                            <li><a href="<?= site_url('pos/register_details'); ?>" data-toggle="ajax"><?= lang('register_details'); ?></a></li>
                            <?php if ($Admin) { ?>
                            <li><a href="<?= site_url('pos/today_sale'); ?>" data-toggle="ajax"><?= lang('today_sale'); ?></a></li>
                            <?php } ?>
                            <li><a href="<?= site_url('pos/close_register'); ?>" data-toggle="ajax"><?= lang('close_register'); ?></a></li>
                            <?php if ($suspended_sales) { ?>
                            <li class="dropdown notifications-menu" id="suspended_sales">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-bell-o"></i>
                                    <span class="label label-warning"><?=sizeof($suspended_sales);?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="header">
                                        <input type="text" autocomplete="off" data-list=".list-suspended-sales" name="filter-suspended-sales" id="filter-suspended-sales" class="form-control input-sm kb-text clearfix" placeholder="<?= lang('filter_by_reference'); ?>">
                                    </li>
                                    <li>
                                        <ul class="menu">
                                            <li class="list-suspended-sales">
                                                <?php
                                                foreach ($suspended_sales as $ss) {
                                                    echo '<a href="' . site_url('pos/?hold=' . $ss->id) . '" class="load_suspended">' . $this->tec->hrld($ss->date) . ' (' . $ss->customer_name . ')<br><div class="bold">' . $ss->hold_ref . '</div></a>';
                                                }
                                                ?>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="footer"><a href="<?= site_url('sales/opened'); ?>"><?= lang('view_all'); ?></a></li>
                                </ul>
                            </li>
                            <?php } ?>
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="<?= base_url('uploads/avatars/thumbs/' . ($this->session->userdata('avatar') ? $this->session->userdata('avatar') : $this->session->userdata('gender') . '.png')) ?>" class="user-image" alt="Avatar" />
                                    <span><?= $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'); ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header">
                                        <img src="<?= base_url('uploads/avatars/' . ($this->session->userdata('avatar') ? $this->session->userdata('avatar') : $this->session->userdata('gender') . '.png')) ?>" class="img-circle" alt="Avatar" />
                                        <p>
                                            <?= $this->session->userdata('email'); ?>
                                            <small><?= lang('member_since') . ' ' . $this->session->userdata('created_on'); ?></small>
                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>" class="btn btn-default btn-flat"><?= lang('profile'); ?></a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="<?= site_url('logout'); ?>" class="btn btn-default btn-flat<?= $this->session->userdata('register_id') ? ' sign_out' : ''; ?>"><?= lang('sign_out'); ?></a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            
                        </ul>
                    </div>
                </nav>
            </header>

            <aside class="main-sidebar">
                <section class="sidebar">
                    <ul class="sidebar-menu">
                        

                        <?php if ($Admin) { ?>
                        <li class="mm_welcome"><a href="<?= site_url(); ?>"><i class="fa fa-dashboard"></i></a></li>
                        <?php if ($Settings->multi_store && !$this->session->userdata('store_id')) { ?>
                        <li class="mm_stores"><a href="<?= site_url('stores'); ?>"><i class="fa fa-building-o"></i> <span><?= lang('stores'); ?></span></a></li>
                        <?php } ?>
                        <li class="mm_pos"><a href="<?= site_url('pos'); ?>"><i class="fa fa-th"></i></a></li>
                        
                        <li class="treeview mm_products">
                            <a href="<?= site_url('products'); ?>">
                                <i class="fa-brands fa-product-hunt"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                        <li class="treeview mm_categories">
                            <a href="<?= site_url('categories'); ?>">
                                <i class="fa-solid fa-folder"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                            <?php if ($this->session->userdata('store_id')) { ?>
                        <li class="treeview mm_sales">
                            <a href="<?= site_url('sales'); ?>">
                                <i class="fa fa-shopping-cart"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                        </li>
                        <li class="treeview mm_purchases">
                            <a href="<?= site_url('purchases'); ?>">
                                <i class="fa fa-plus"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                            <?php } ?>
                        <li class="treeview mm_gift_cards">
                            <a href="<?= site_url('gift_cards'); ?>">
                                <i class="fa fa-credit-card"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                        </li>
                        <li class="treeview mm_auth mm_customers mm_suppliers">
                            <a href="<?= site_url('customers'); ?>">
                                <i class="fa fa-users"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                        </li>
                        <li class="treeview mm_reports">
                            <a href="<?= site_url('reports/daily_sales'); ?>">
                                <i class="fa fa-bar-chart-o"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                        <?php } elseif ($Owner) { ?>
                        <li class="mm_welcome"><a href="<?= site_url(); ?>"><i class="fa fa-dashboard"></i></a></li>
                        <?php if ($Settings->multi_store && !$this->session->userdata('store_id')) { ?>
                        <li class="mm_stores"><a href="<?= site_url('stores'); ?>"><i class="fa fa-building-o"></i> <span><?= lang('stores'); ?></span></a></li>
                        <?php } ?>
                        <li class="mm_pos"><a href="<?= site_url('pos'); ?>"><i class="fa fa-th"></i></a></li>
                        
                        <li class="treeview mm_products">
                            <a href="<?= site_url('products'); ?>">
                                <i class="fa-brands fa-product-hunt"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                        <li class="treeview mm_categories">
                            <a href="<?= site_url('categories'); ?>">
                                <i class="fa-solid fa-folder"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                            <?php if ($this->session->userdata('store_id')) { ?>
                        <li class="treeview mm_sales">
                            <a href="<?= site_url('sales'); ?>">
                                <i class="fa fa-shopping-cart"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                        </li>
                        <li class="treeview mm_purchases">
                            <a href="<?= site_url('purchases'); ?>">
                                <i class="fa fa-plus"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                            <?php } ?>
                        <li class="treeview mm_gift_cards">
                            <a href="<?= site_url('gift_cards'); ?>">
                                <i class="fa fa-credit-card"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                        </li>
                        <li class="treeview mm_auth mm_customers mm_suppliers">
                            <a href="<?= site_url('customers'); ?>">
                                <i class="fa fa-users"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                        </li>
                        <li class="treeview mm_reports">
                            <a href="<?= site_url('reports/daily_sales'); ?>">
                                <i class="fa fa-bar-chart-o"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                        <?php } elseif ($Supervisor) { ?>
                        <li class="mm_welcome"><a href="<?= site_url(); ?>"><i class="fa fa-dashboard"></i></a></li>
                        <?php if ($Settings->multi_store && !$this->session->userdata('store_id')) { ?>
                        <li class="mm_stores"><a href="<?= site_url('stores'); ?>"><i class="fa fa-building-o"></i> <span><?= lang('stores'); ?></span></a></li>
                        <?php } ?>
                        <li class="mm_pos"><a href="<?= site_url('pos'); ?>"><i class="fa fa-th"></i></a></li>
                        <li class="treeview mm_products">
                            <a href="<?= site_url('products'); ?>">
                                <i class="fa-brands fa-product-hunt"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                        <li class="mm_categories">
                            <a href="<?= site_url('categories'); ?>">
                                <i class="fa fa-folder-open"></i> 
                            </a></li>
                            <?php if ($this->session->userdata('store_id')) { ?>
                        <li class="treeview mm_sales">
                            <a href="<?= site_url('sales'); ?>">
                                <i class="fa fa-shopping-cart"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                        <li class="treeview mm_purchases">
                            <a href="<?= site_url('purchases'); ?>">
                                <i class="fa fa-plus"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                            <?php } ?>
                        <li class="treeview mm_gift_cards">
                            <a href="<?= site_url('gift_cards'); ?>">
                                <i class="fa fa-credit-card"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                        <li class="treeview mm_customers">
                            <a href="<?= site_url('customers'); ?>">
                                <i class="fa fa-users"></i>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            
                        </li>
                        <?php } else { ?>
                        <li class="mm_pos"><a href="<?= site_url('pos'); ?>"><i class="fa fa-th"></i></a></li>
                        <?php } ?>
                    </ul>
                </section>
            </aside>

            <div class="content-wrapper">

                <div class="col-lg-12 alerts">
                <?php if ($error) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <h4><i class="icon fa fa-ban"></i> <?= lang('error'); ?></h4>
                        <?= $error; ?>
                    </div>
                <?php } if ($message) { ?>
                    <div class="alert alert-success alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <h4><i class="icon fa fa-check"></i> <?= lang('Success'); ?></h4>
                        <?= $message; ?>
                    </div>
                <?php } ?>
                </div>

                <table style="width:97%;" class="layout-table">
                    <tr>
                        
                        <td style="width: 100%">

                            <div id="pos">
                                <?= form_open_multipart('pos', 'id="pos-sale-form"'); ?>
                                <div class="well well-sm" id="leftdiv">
                                    <div class="col-sm-6">
                                    <div class="form-group" style="margin-bottom:5px;">
                                            <input type="text" name="code" id="add_item" class="form-control" placeholder="<?=lang('search__scan')?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                    <div class="form-group" style="margin-bottom:5px;">
                                            <div class="input-group">
                                                <?php foreach ($customers as $customer) {
                                                    $cus[$customer->id] = $customer->name;
                                                } ?>
                                                <?= form_dropdown('customer_id', $cus, set_value('customer_id', $Settings->default_customer), 'id="spos_customer" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" class="form-control select2" style="width:100%;position:absolute;"'); ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                                    <a href="#" id="add-customer" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a>
                                                </div>
                                            </div>
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                    <div id="lefttop" class="row" style="margin-bottom:5px;">
                                        
                                        <?php if ($eid && $Admin) { ?>
                                        <div class="col-sm-6"><div class="form-group" style="padding:0 15px; margin-bottom:5px;">
                                            <?= form_input('date', set_value('date', $sale->date), 'id="date" required="required" class="form-control"'); ?>
                                        </div></div>
                                        <?php } ?>
                                        <div class="form-group" style="margin-bottom:5px; display:none;">
                                            <input type="text" name="hold_ref" value="<?= $reference_note; ?>" id="hold_ref" class="form-control kb-text" placeholder="<?=lang('reference_note')?>" />
                                        </div>
                                        
                                    </div>
                                    <div id="printhead" class="print">
                                        <?= $Settings->header; ?>
                                        <p><?= lang('date'); ?>: <?=date($Settings->dateformat)?></p>
                                    </div>
                                    <div id="print" class="fixed-table-container">
                                        <div class="table-responsive" style="overflow-x:auto;">
    <div id="list-table-div">
        <div class="fixed-table-header">
            <table class="table table-striped table-condensed table-hover list-table custom-table" style="margin:0;">
                <thead>
                    <tr class="success text-center">
                        <th class="col-product"><?= lang('product') ?></th>
                        <th><?= lang('WH Qty') ?></th>
                        <th>Actual Weight <small>(Dual)</small></th>
                        <th><?= lang('unit') ?></th>
                        <th><?= lang('cost') ?></th>
                        <th><?= lang('price') ?></th>
                        <th><?= lang('qty') ?> / Count</th>
                        <th><?= lang('subtotal') ?></th>
                        <th class="col-action">
                            <i class="fa-solid fa-trash"></i>
                        </th>
                    </tr>

                </thead>
            </table>
            
        </div>
        <table id="posTable" class="table table-striped table-condensed table-hover list-table custom-table" style="margin:0px;" data-height="100">
            <thead>
                <tr class="success text-center">
                        <th class="col-product"><?= lang('product') ?></th>
                        <th><?= lang('WH Qty') ?></th>
                        <th>Actual Weight <small>(Dual)</small></th>
                        <th><?= lang('unit') ?></th>
                        <th><?= lang('cost') ?></th>
                        <th><?= lang('price') ?></th>
                        <th><?= lang('qty') ?> / Count</th>
                        <th><?= lang('subtotal') ?></th>
                        <th class="col-action">
                            <i class="fa-solid fa-trash"></i>
                        </th>
                    </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

                                        <div style="clear:both;"></div>
                                        <div id="totaldiv">
                                            <table id="totaltbl" class="table table-condensed totals" style="margin-bottom:10px;">
                                                <tbody>
                                                    <tr class="info">
                                                        <td width="25%"><?=lang('total_items')?></td>
                                                        <td class="text-right" style="padding-right:10px;"><span id="count">0</span></td>
                                                        <td width="25%"><?=lang('total')?></td>
                                                        <td class="text-right" colspan="2"><span id="total">0</span></td>
                                                    </tr>
                                                    <tr class="info">
                                                        <td width="25%"><a href="#" id="add_discount"><?=lang('discount')?></a></td>
                                                        <td class="text-right" style="padding-right:10px;"><span id="ds_con">0</span></td>
                                                        <td width="25%"><a href="#" id="add_tax"><?=lang('order_tax')?></a></td>
                                                        <td class="text-right"><span id="ts_con">0</span></td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td colspan="2" style="font-weight:bold;">
                                                            <?=lang('total_payable')?>
                                                            <a role="button" data-toggle="modal" data-target="#noteModal">
                                                                <i class="fa fa-comment"></i>
                                                            </a>
                                                        </td>
                                                        <td class="text-right" colspan="2" style="font-weight:bold;"><span id="total-payable">0</span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div id="botbuttons" class="col-xs-12 text-center">
                                        <div class="row">
                                            <div class="col-xs-6" style="padding: 0;">
                                                <div class="btn-group-vertical btn-block">
                                                    
                                                    <button type="button" class="btn btn-danger btn-block btn-flat"
                                                    id="reset"><?= lang('cancel'); ?></button>
                                                </div>

                                            </div>
                                           
                                            
                                            
                                            <div class="col-xs-6" style="padding: 0;">
                                                <div class="btn-group-vertical btn-block">
                                                <button type="button" class="btn btn-primary btn-block btn-flat" id="<?= $eid ? 'submit-sale' : 'payment'; ?>" ><?= $eid ? lang('submit') : lang('payment'); ?></button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="clearfix"></div>
                                    <span id="hidesuspend"></span>
                                    <input type="hidden" name="spos_note" value="" id="spos_note">

                                    <div id="payment-con">
                                        <input type="hidden" name="amount" id="amount_val" value="<?= $eid ? $sale->paid : ''; ?>"/>
                                        <input type="hidden" name="balance_amount" id="balance_val" value=""/>
                                        <input type="hidden" name="paid_by" id="paid_by_val" value="cash"/>
                                        <input type="hidden" name="cc_no" id="cc_no_val" value=""/>
                                        <input type="hidden" name="paying_gift_card_no" id="paying_gift_card_no_val" value=""/>
                                        <input type="hidden" name="cc_holder" id="cc_holder_val" value=""/>
                                        <input type="hidden" name="cheque_no" id="cheque_no_val" value=""/>
                                        <input type="hidden" name="cc_month" id="cc_month_val" value=""/>
                                        <input type="hidden" name="cc_year" id="cc_year_val" value=""/>
                                        <input type="hidden" name="cc_type" id="cc_type_val" value=""/>
                                        <input type="hidden" name="cc_cvv2" id="cc_cvv2_val" value=""/>
                                        <input type="hidden" name="balance" id="balance_v" value=""/>
                                        <input type="hidden" name="payment_note" id="payment_note_val" value=""/>
                                        <input type="hidden" name="sale_token" value="<?= $this->session->userdata('sale_token'); ?>">
                                    </div>
                                    <input type="hidden" name="customer" id="customer" value="<?=$Settings->default_customer?>" />
                                    <input type="hidden" name="order_tax" id="tax_val" value="" />
                                    <input type="hidden" name="order_discount" id="discount_val" value="" />
                                    <input type="hidden" name="count" id="total_item" value="" />
                                    <input type="hidden" name="did" id="did_delete" value="<?=$sid;?>" />
                                    <input type="hidden" name="eid" id="eid_delete" value="<?=$eid;?>" />
                                    <input type="hidden" name="total_items" id="total_items" value="0" />
                                    <input type="hidden" name="total_quantity" id="total_quantity" value="0" />
                                    <input type="submit" id="submit" value="Submit Sale" style="display: none;" />
                                </div>
                                <div class="modal" data-easein="flipYIn" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-success">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="payModalLabel">
                    <?=lang('payment')?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="font16">
                            <table class="table table-bordered table-condensed" style="margin-bottom: 0;">
                                <tbody>
                                    <tr>
                                        <td width="25%" style="border-right-color: #FFF !important;"><?= lang('total_items'); ?></td>
                                        <td width="25%" class="text-right"><span id="item_count">0.00</span></td>
                                        <td width="25%" style="border-right-color: #FFF !important;"><?= lang('total_payable'); ?></td>
                                        <td width="25%" class="text-right"><span id="twt">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <td style="border-right-color: #FFF !important;"><?= lang('total_paying'); ?></td>
                                        <td class="text-right"><span id="total_paying">0.00</span></td>
                                        <td style="border-right-color: #FFF !important;"><?= lang('total_balance'); ?></td>
                                        <td class="text-right"><span id="balance">0.00</span></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group form-group-lg">
                                    <?= lang('note', 'note'); ?>
                                    <textarea name="note" id="note" class="pa form-control kb-text"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group form-group-lg">
                                    <?= lang('amount', 'amount'); ?>
                                    <input name="amount" type="text" id="amount"
                                    class="pa form-control kb-pad amount"/>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group form-group-lg">
                                    <?= lang('paying_by', 'paid_by'); ?>
                                    <select id="paid_by" class="form-control paid_by select2" style="width:100%;">
                                        <option value="cash"><?= lang('cash'); ?></option>
                                        <option value="kpay"><?= lang('KPAY'); ?></option>
                                        <option value="mobile_banking"><?= lang('Mobile Banking'); ?></option>
                                        <option value="quick_pay"><?= lang('Quick Pay'); ?></option>
                                        <option value="other"><?= lang('other'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group gc" style="display: none;">
                                    <?= lang('gift_card_no', 'gift_card_no'); ?>
                                    <input type="text" id="gift_card_no"
                                    class="pa form-control kb-pad gift_card_no gift_card_input"/>

                                    <div id="gc_details"></div>
                                </div>
                                <div class="pcc" style="display:none;">
                                    <div class="form-group form-group-lg">
                                        <input type="text" id="swipe" class="form-control swipe swipe_input"
                                        placeholder="<?= lang('focus_swipe_here') ?>"/>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group form-group-lg">
                                                <input type="text" id="pcc_no"
                                                class="form-control kb-pad"
                                                placeholder="<?= lang('cc_no') ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group form-group-lg">

                                                <input type="text" id="pcc_holder"
                                                class="form-control kb-text"
                                                placeholder="<?= lang('cc_holder') ?>"/>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="form-group form-group-lg">
                                                <select id="pcc_type"
                                                class="form-control pcc_type select2"
                                                placeholder="<?= lang('card_type') ?>">
                                                <option value="Visa"><?= lang('Visa'); ?></option>
                                                <option
                                                value="MasterCard"><?= lang('MasterCard'); ?></option>
                                                <option value="Amex"><?= lang('Amex'); ?></option>
                                                <option
                                                value="Discover"><?= lang('Discover'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-3">
                                        <div class="form-group form-group-lg">
                                            <input type="text" id="pcc_month"
                                            class="form-control kb-pad"
                                            placeholder="<?= lang('month') ?>"/>
                                        </div>
                                    </div>
                                    <div class="col-xs-3">
                                        <div class="form-group form-group-lg">

                                            <input type="text" id="pcc_year"
                                            class="form-control kb-pad"
                                            placeholder="<?= lang('year') ?>"/>
                                        </div>
                                    </div>
                                    <div class="col-xs-3">
                                        <div class="form-group form-group-lg">

                                            <input type="text" id="pcc_cvv2"
                                            class="form-control kb-pad"
                                            placeholder="<?= lang('cvv2') ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pcheque" style="display:none;">
                                <div class="form-group form-group-lg"><?= lang('cheque_no', 'cheque_no'); ?>
                                    <input type="text" id="cheque_no"
                                    class="form-control cheque_no kb-text"/>
                                </div>
                            </div>
                            <div class="pcash">
                                <div class="form-group form-group-lg"><?= lang('payment_note', 'payment_note'); ?>
                                    <input type="text" id="payment_note"
                                    class="form-control payment_note kb-text"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group form-group-lg">
                                        <?= lang('attachment', 'attachment'); ?>
                                        <input type="file" name="attachment" id="attachment" 
                                               class="form-control"/>
                                        <small class="text-muted">Allowed: jpg, jpeg, png, pdf</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-3 text-center" style="display:none;">
                    <!-- <span style="font-size: 1.2em; font-weight: bold;"><?= lang('quick_cash'); ?></span> -->

                    <div class="btn-group btn-group-vertical" style="width:100%;">
                        <button type="button" class="btn btn-info btn-block quick-cash" id="quick-payable">0.00
                        </button>
                        <?php
                        foreach (lang('quick_cash_notes') as $cash_note_amount) {
                            echo '<button type="button" class="btn btn-block btn-warning quick-cash">' . $cash_note_amount . '</button>';
                        }
                        ?>
                        <button type="button" class="btn btn-block btn-danger"
                        id="clear-cash-notes"><?= lang('clear'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal"> <?=lang('close')?> </button>
            <button class="btn btn-primary" id="<?= $eid ? '' : 'submit-sale'; ?>"><?=lang('submit')?></button>
        </div>
    </div>
</div>
</div>
                                <?=form_close();?>
                            </div>

                        </td>
                        

                        
                    </tr>
                </table>

            </div>
        </div>

        <aside class="control-sidebar control-sidebar-dark" id="categories-list">
            <div class="tab-content sb">
                <div class="tab-pane active sb" id="control-sidebar-home-tab">
                    <div id="filter-categories-con">
                        <input type="text" autocomplete="off" data-list=".control-sidebar-menu" name="filter-categories" id="filter-categories" class="form-control sb col-xs-12 kb-text" placeholder="<?= lang('filter_categories'); ?>" style="margin-bottom: 20px;">
                    </div>
                    <div class="clearfix sb"></div>
                    <div id="category-sidebar-menu">
                        <ul class="control-sidebar-menu">
                            <?php
                            foreach ($categories as $category) {
                                echo '<li><a href="#" class="category' . ($category->id == $Settings->default_category ? ' active' : '') . '" id="' . $category->id . '">';
                                if ($category->image) {
                                    echo '<div class="menu-icon"><img src="' . base_url('uploads/thumbs/' . $category->image) . '" alt="" class="img-thumbnail img-responsive"></div>';
                                } else {
                                    echo '<i class="menu-icon fa fa-folder-open bg-red"></i>';
                                }
                                echo '<div class="menu-info"><h4 class="control-sidebar-subheading">' . $category->code . '</h4><p>' . $category->name . '</p></div>
                            </a></li>';
                            }
                            ?>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
    <div class="control-sidebar-bg sb"></div>
</div>
</div>
<div id="order_tbl" style="display:none;"><span id="order_span"></span>
    <table id="order-table" class="prT table table-condensed" style="width:100%;margin-bottom:0;"></table>
</div>
<div id="bill_tbl" style="display:none;"><span id="bill_span"></span>
    <table id="bill-table" width="100%" class="prT table table-condensed" style="width:100%;margin-bottom:0;"></table>
    <table id="bill-total-table" width="100%" class="prT table table-condensed" style="width:100%;margin-bottom:0;"></table>
</div>
<div style="width:435px;background:#FFF;display:block">
    <div id="order-data" style="display:none;" class="text-center">
        <h1><?= $store->name; ?></h1>
        <h2><?= lang('order'); ?></h2>
        <div id="preo" class="text-left"></div>
    </div>
    <div id="bill-data" style="display:none;" class="text-center">
        <h1><?= $store->name; ?></h1>
        <h2><?= lang('bill'); ?></h2>
        <div id="preb" class="text-left"></div>
    </div>
</div>

<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>

<div class="modal" data-easein="flipYIn" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group form-group-lg">
                    <?= lang('card_no', 'gccard_no'); ?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#" id="genNo"><i class="fa fa-cogs"></i></a></div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname"/>
                <div class="form-group form-group-lg">
                    <?= lang('value', 'gcvalue'); ?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group form-group-lg">
                    <?= lang('price', 'gcprice'); ?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group form-group-lg">
                    <?= lang('expiry_date', 'gcexpiry'); ?>
                    <?php echo form_input('gcexpiry', '', 'class="form-control" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?=lang('close')?></button>
                <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" data-easein="flipYIn" id="dsModal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="dsModalLabel"><?= lang('discount_title'); ?></h4>
            </div>
            <div class="modal-body">
                <input type='text' class='form-control input-sm kb-pad' id='get_ds' onClick='this.select();' value=''>

                <label class="checkbox" for="apply_to_order">
                    <input type="radio" name="apply_to" value="order" id="apply_to_order" checked="checked"/>
                    <?= lang('apply_to_order') ?>
                </label>
                <label class="checkbox" for="apply_to_products">
                    <input type="radio" name="apply_to" value="products" id="apply_to_products"/>
                    <?= lang('apply_to_products') ?>
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal"><?=lang('close')?></button>
                <button type="button" id="updateDiscount" class="btn btn-primary btn-sm"><?= lang('update') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" data-easein="flipYIn" id="tsModal" tabindex="-1" role="dialog" aria-labelledby="tsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="tsModalLabel"><?= lang('tax_title'); ?></h4>
            </div>
            <div class="modal-body">
                <input type='text' class='form-control input-sm kb-pad' id='get_ts' onClick='this.select();' value=''>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal"><?=lang('close')?></button>
                <button type="button" id="updateTax" class="btn btn-primary btn-sm"><?= lang('update') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" data-easein="flipYIn" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="noteModalLabel"><?= lang('note'); ?></h4>
            </div>
            <div class="modal-body">
                <textarea name="snote" id="snote" class="pa form-control kb-text"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal"><?=lang('close')?></button>
                <button type="button" id="update-note" class="btn btn-primary btn-sm"><?= lang('update') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" data-easein="flipYIn" id="proModal" tabindex="-1" role="dialog" aria-labelledby="proModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-primary">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="proModalLabel">
                    <?=lang('payment')?>
                </h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th style="width:25%;"><?= lang('net_price'); ?></th>
                        <th style="width:25%;"><span id="net_price"></span></th>
                        <th style="width:25%;"><?= lang('product_tax'); ?></th>
                        <th style="width:25%;"><span id="pro_tax"></span> <span id="pro_tax_method"></span></th>
                    </tr>
                </table>
                <input type="hidden" id="row_id" />
                <input type="hidden" id="item_id" />
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group form-group-lg">
                            <?=lang('unit_price', 'nPrice')?>
                            <input type="text" class="form-control input-sm kb-pad" id="nPrice" onClick="this.select();" placeholder="<?=lang('new_price')?>">
                        </div>
                        <div class="form-group form-group-lg">
                            <?=lang('discount', 'nDiscount')?>
                            <input type="text" class="form-control input-sm kb-pad" id="nDiscount" onClick="this.select();" placeholder="<?=lang('discount')?>">
                        </div>
                    </div>
                    <div class="col-sm-6" >
                        <div class="form-group form-group-lg" style="display:none;">
                            <?=lang('quantity', 'nQuantity')?>
                            <input type="text" class="form-control input-sm kb-pad" id="nQuantity" onClick="this.select();" placeholder="<?=lang('current_quantity')?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group form-group-lg">
                            <?=lang('comment', 'nComment')?>
                            <textarea class="form-control kb-text" id="nComment"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?=lang('close')?></button>
                <button class="btn btn-success" id="editItem"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" data-easein="flipYIn" id="susModal" tabindex="-1" role="dialog" aria-labelledby="susModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="susModalLabel"><?= lang('suspend_sale'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('type_reference_note'); ?></p>

                <div class="form-group form-group-lg">
                    <?= lang('reference_note', 'reference_note'); ?>
                    <?php echo form_input('reference_note', $reference_note, 'class="form-control kb-text" id="reference_note"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"> <?=lang('close')?> </button>
                <button type="button" id="suspend_sale" class="btn btn-primary"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>



<div class="modal" data-easein="flipYIn" id="saleModal" tabindex="-1" role="dialog" aria-labelledby="saleModalLabel" aria-hidden="true"></div>
<div class="modal" data-easein="flipYIn" id="opModal" tabindex="-1" role="dialog" aria-labelledby="opModalLabel" aria-hidden="true"></div>

<!-- pay model -->

<div class="modal" data-easein="flipYIn" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="cModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-primary">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="cModalLabel">
                    <?=lang('add_customer')?>
                </h4>
            </div>
            <?= form_open('pos/add_customer', 'id="customer-form"'); ?>
            <div class="modal-body">
                <div id="c-alert" class="alert alert-danger" style="display:none;"></div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group form-group-lg">
                            <label class="control-label" for="code">
                                <?= lang('name'); ?>
                            </label>
                            <?= form_input('name', '', 'class="form-control input-sm kb-text" id="cname"'); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group form-group-lg">
                            <label class="control-label" for="cemail">
                                <?= lang('email_address'); ?>
                            </label>
                            <?= form_input('email', '', 'class="form-control input-sm kb-text" id="cemail"'); ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group form-group-lg">
                            <label class="control-label" for="phone">
                                <?= lang('phone'); ?>
                            </label>
                            <?= form_input('phone', '', 'class="form-control input-sm kb-pad" id="cphone"');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group form-group-lg">
                            <label class="control-label" for="cf1">
                                <?= lang('cf1'); ?>
                            </label>
                            <?= form_input('cf1', '', 'class="form-control input-sm kb-text" id="cf1"'); ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group form-group-lg">
                            <label class="control-label" for="cf2">
                                <?= lang('cf2'); ?>
                            </label>
                            <?= form_input('cf2', '', 'class="form-control input-sm kb-text" id="cf2"');?>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer" style="margin-top:0;">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"> <?=lang('close')?> </button>
                <button type="submit" class="btn btn-primary" id="add_customer"> <?=lang('add_customer')?> </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<div class="modal" data-easein="flipYIn" id="printModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width:400px;">
    <div class="modal-content">
        <div class="modal-header np">
            <button type="button" class="close" id="print-modal-close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
            <button type="button" class="close mr10" onclick="window.print();"><i class="fa fa-print"></i></button>
            <h4 class="modal-title" id="print-title"></h4>
        </div>
        <div class="modal-body">
            <div id="print-body"></div>
        </div>
    </div>
    </div>
</div>
<div class="modal" data-easein="flipYIn" id="posModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
<div class="modal" data-easein="flipYIn" id="posModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
<?php
$product_conversions = $product_conversions ?? [];
$product_unit_prices = $product_unit_prices ?? [];
$product_unit_conversions = $product_unit_conversions ?? [];
?>
<script>
  var product_units = <?= json_encode($product_units); ?>;
  var product_conversions = <?= json_encode($product_conversions); ?>;
  var product_unit_prices = <?= json_encode($product_unit_prices) ?>;
  var product_unit_conversions = <?= json_encode($product_unit_conversions); ?>;
  var base_units = <?= json_encode($base_units) ?>;
</script>
<script type="text/javascript">
    var base_url = '<?=base_url();?>', assets = '<?= $assets ?>';
    var dateformat = '<?=$Settings->dateformat;?>', timeformat = '<?= $Settings->timeformat ?>';
    <?php unset($Settings->protocol, $Settings->smtp_host, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->smtp_crypto, $Settings->mailpath, $Settings->timezone, $Settings->setting_id, $Settings->default_email, $Settings->version, $Settings->stripe, $Settings->stripe_secret_key, $Settings->stripe_publishable_key); ?>
    var Settings = <?= json_encode($Settings); ?>;
    var sid = false, username = '<?=$this->session->userdata('username');?>', spositems = {};
    $(window).load(function () {
        $('#mm_<?=$m?>').addClass('active');
        $('#<?=$m?>_<?=$v?>').addClass('active');
    });
    var pro_limit = <?=$Settings->pro_limit?>, java_applet = 0, count = 1, total = 0, an = 1, p_page = 0, page = 0, cat_id = <?=$Settings->default_category?>, tcp = <?=$tcp?>;
    var gtotal = 0, order_discount = 0, order_tax = 0, protect_delete = <?= ($Admin) ? 0 : ($Settings->pin_code ? 1 : 0); ?>;
    var order_data = {}, bill_data = {};
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';
    <?php
    if ($Settings->remote_printing == 2) {
        ?>
        var ob_store_name = "<?= printText($store->name, (!empty($printer) ? $printer->char_per_line : '')); ?>\r\n";
        order_data.store_name = ob_store_name;
        bill_data.store_name = ob_store_name;

        ob_header = "";
        ob_header += "<?= printText($store->name . ' (' . $store->code . ')', (!empty($printer) ? $printer->char_per_line : '')); ?>\r\n";
        <?php
        if ($store->address1) { ?>
            ob_header += "<?= printText($store->address1, (!empty($printer) ? $printer->char_per_line : ''));?>\r\n";
            <?php
        }
        if ($store->address2) { ?>
            ob_header += "<?= printText($store->address2, (!empty($printer) ? $printer->char_per_line : ''));?>\r\n";
            <?php
        }
        if ($store->city) { ?>
            ob_header += "<?= printText($store->city, (!empty($printer) ? $printer->char_per_line : ''));?>\r\n";
            <?php
        } ?>
        ob_header += "<?= printText(lang('tel') . ': ' . $store->phone, (!empty($printer) ? $printer->char_per_line : '')); ?>\r\n\r\n";
        ob_header += "<?= printText(str_replace(["\n", "\r"], ['\\n', '\\r'], $store->receipt_header), (!empty($printer) ? $printer->char_per_line : '')); ?>\r\n\r\n";

        order_data.header = ob_header + "<?= printText(lang('order'), (!empty($printer) ? $printer->char_per_line : '')); ?>\r\n\r\n";
        bill_data.header = ob_header + "<?= printText(lang('bill'), (!empty($printer) ? $printer->char_per_line : '')); ?>\r\n\r\n";
        order_data.totals = '';
        order_data.payments = '';
        bill_data.payments = '';
        order_data.footer = '';
        bill_data.footer = "<?= lang('merchant_copy'); ?> \n";
        <?php
    }
    ?>
    var lang = new Array();
    lang['code_error'] = '<?= lang('code_error'); ?>';
    lang['r_u_sure'] = '<?= lang('r_u_sure'); ?>';
    lang['please_add_product'] = '<?= lang('please_add_product'); ?>';
    lang['paid_less_than_amount'] = '<?= lang('paid_less_than_amount'); ?>';
    lang['x_suspend'] = '<?= lang('x_suspend'); ?>';
    lang['discount_title'] = '<?= lang('discount_title'); ?>';
    lang['update'] = '<?= lang('update'); ?>';
    lang['tax_title'] = '<?= lang('tax_title'); ?>';
    lang['leave_alert'] = '<?= lang('leave_alert'); ?>';
    lang['close'] = '<?= lang('close'); ?>';
    lang['delete'] = '<?= lang('delete'); ?>';
    lang['no_match_found'] = '<?= lang('no_match_found'); ?>';
    lang['wrong_pin'] = '<?= lang('wrong_pin'); ?>';
    lang['file_required_fields'] = '<?= lang('file_required_fields'); ?>';
    lang['enter_pin_code'] = '<?= lang('enter_pin_code'); ?>';
    lang['incorrect_gift_card'] = '<?= lang('incorrect_gift_card'); ?>';
    lang['card_no'] = '<?= lang('card_no'); ?>';
    lang['value'] = '<?= lang('value'); ?>';
    lang['balance'] = '<?= lang('balance'); ?>';
    lang['unexpected_value'] = '<?= lang('unexpected_value'); ?>';
    lang['inclusive'] = '<?= lang('inclusive'); ?>';
    lang['exclusive'] = '<?= lang('exclusive'); ?>';
    lang['total'] = '<?= lang('total'); ?>';
    lang['total_items'] = '<?= lang('total_items'); ?>';
    lang['order_tax'] = '<?= lang('order_tax'); ?>';
    lang['order_discount'] = '<?= lang('order_discount'); ?>';
    lang['total_payable'] = '<?= lang('total_payable'); ?>';
    lang['rounding'] = '<?= lang('rounding'); ?>';
    lang['grand_total'] = '<?= lang('grand_total'); ?>';
    lang['register_open_alert'] = '<?= lang('register_open_alert'); ?>';
    lang['discount'] = '<?= lang('discount'); ?>';
    lang['order'] = '<?= lang('order'); ?>';
    lang['bill'] = '<?= lang('bill'); ?>';
    lang['print'] = '<?= lang('print'); ?>';
    lang['merchant_copy'] = '<?= lang('merchant_copy'); ?>';

    $(document).ready(function() {
        <?php if ($this->session->userdata('rmspos')) { ?>
            if (get('spositems')) { remove('spositems'); }
            if (get('spos_discount')) { remove('spos_discount'); }
            if (get('spos_tax')) { remove('spos_tax'); }
            if (get('spos_note')) { remove('spos_note'); }
            if (get('spos_customer')) { remove('spos_customer'); }
            if (get('amount')) { remove('amount'); }
            <?php $this->tec->unset_data('rmspos');
        } ?>

            if (get('rmspos')) {
                if (get('spositems')) { remove('spositems'); }
                if (get('spos_discount')) { remove('spos_discount'); }
                if (get('spos_tax')) { remove('spos_tax'); }
                if (get('spos_note')) { remove('spos_note'); }
                if (get('spos_customer')) { remove('spos_customer'); }
                if (get('amount')) { remove('amount'); }
                remove('rmspos');
            }
            <?php if ($sid) { ?>
                store('spositems', JSON.stringify(<?=$items;?>));
                store('spos_discount', '<?=$suspend_sale->order_discount_id;?>');
                store('spos_tax', '<?=$suspend_sale->order_tax_id;?>');
                store('spos_customer', '<?=$suspend_sale->customer_id;?>');
                $('#spos_customer').select2().select2('val', '<?=$suspend_sale->customer_id;?>');
                store('rmspos', '1');
                $('#tax_val').val('<?=$suspend_sale->order_tax_id;?>');
                $('#discount_val').val('<?=$suspend_sale->order_discount_id;?>');
            <?php } elseif ($eid) { ?>
                    $('#date').inputmask("y-m-d h:s:s", { "placeholder": "YYYY/MM/DD HH:mm:ss" });
                    store('spositems', JSON.stringify(<?=$items;?>));
                    store('spos_discount', '<?=$sale->order_discount_id;?>');
                    store('spos_tax', '<?=$sale->order_tax_id;?>');
                    store('spos_customer', '<?=$sale->customer_id;?>');
                    store('sale_date', '<?=$sale->date;?>');
                    $('#spos_customer').select2().select2('val', '<?=$sale->customer_id;?>');
                    $('#date').val('<?=$sale->date;?>');
                    store('rmspos', '1');
                    $('#tax_val').val('<?=$sale->order_tax_id;?>');
                    $('#discount_val').val('<?=$sale->order_discount_id;?>');
            <?php } else { ?>
                        if (! get('spos_discount')) {
                            store('spos_discount', '<?=$Settings->default_discount;?>');
                            $('#discount_val').val('<?=$Settings->default_discount;?>');
                        }
                        if (! get('spos_tax')) {
                            store('spos_tax', '<?=$Settings->default_tax_rate;?>');
                            $('#tax_val').val('<?=$Settings->default_tax_rate;?>');
                        }
            <?php } ?>

                        if (ots = get('spos_tax')) {
                            $('#tax_val').val(ots);
                        }
                        if (ods = get('spos_discount')) {
                            $('#discount_val').val(ods);
                        }
                        bootbox.addLocale('bl',{OK:'<?= lang('ok'); ?>',CANCEL:'<?= lang('no'); ?>',CONFIRM:'<?= lang('yes'); ?>'});
                        bootbox.setDefaults({closeButton:false,locale:"bl"});
                        <?php if ($eid) { ?>
                            $('#suspend').attr('disabled', true);
                            $('#print_order').attr('disabled', true);
                            $('#print_bill').attr('disabled', true);
                        <?php } ?>
                        });
                    </script>

                    <script type="text/javascript">
                        var socket = null;
                        <?php
                        if ($Settings->remote_printing == 2) {
                            ?>
                            try {
                                socket = new WebSocket('ws://127.0.0.1:6441');
                                socket.onopen = function () {
                                    console.log('Connected');
                                    return;
                                };
                                socket.onclose = function () {
                                    console.log('Connection closed');
                                    return;
                                };
                            } catch (e) {
                                console.log(e);
                            }
                            <?php
                        }
                        ?>
                        function printBill(bill) {
                            if (Settings.remote_printing == 1) {
                                Popup($('#bill_tbl').html(), 'bill');
                            } else if (Settings.remote_printing == 2) {
                                if (socket.readyState == 1) {
                                    if (Settings.print_img == 1) {
                                        $('#bill-data').show();
                                        $('#preb').html(
                                            '<pre style="background:#FFF;font-size:20px;margin:0;border:0;color:#000 !important;">' +
                                            bill_data.info +
                                            bill_data.items +
                                            '\n' +
                                            bill_data.totals +
                                            '</pre>'
                                            );
                                        var element = $('#bill-data').get(0);
                                        html2canvas(element, {scrollY: 0, scale: 1.7}).then(function(canvas) {
                                            var dataURL = canvas.toDataURL();
                                            var socket_data = {
                                                'printer': <?= $Settings->local_printers ? "''" : json_encode($printer); ?>,
                                                'text': dataURL, 'cash_drawer': 0
                                            };
                                            socket.send(JSON.stringify({
                                                type: 'print-img',
                                                data: socket_data
                                            }));
                                            // return Canvas2Image.saveAsPNG(canvas);
                                        });
                                        setTimeout(function() {
                                            $('#bill-data').hide();
                                        }, 500);
                                    } else {
                                        var socket_data = {'printer': <?= $Settings->local_printers ? "''" : json_encode($printer); ?>, 'logo': '<?= !empty($store->logo) ? base_url('uploads/' . $store->logo) : ''; ?>', 'text': bill};
                                        socket.send(JSON.stringify({
                                            type: 'print-receipt',
                                            data: socket_data
                                        }));
                                    }
                                    return false;
                                } else {
                                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                                    return false;
                                }
                            }
                        }
                        var order_printers = <?= $Settings->local_printers ? "''" : json_encode($order_printers); ?>;
                        function printOrder(order) {
                            if (Settings.remote_printing == 1) {
                                Popup($('#order_tbl').html(), 'order');
                            } else if (Settings.remote_printing == 2) {
                                if (socket.readyState == 1) {
                                    if (Settings.print_img == 1) {
                                        $('#order-data').show();
                                        $('#preo').html(
                                            '<pre style="background:#FFF;font-size:20px;margin:0;border:0;color:#000 !important;">' + order_data.info + order_data.items + '</pre>'
                                            );
                                        var element = $('#order-data').get(0);
                                        html2canvas(element, {scrollY: 0, scale: 1.7}).then(function(canvas) {
                                            var dataURL = canvas.toDataURL();
                                            var socket_data = {
                                                'printer': <?= $Settings->local_printers ? "''" : json_encode($printer); ?>,
                                                'text': dataURL, 'order': 1, 'cash_drawer': 0
                                            };
                                            socket.send(JSON.stringify({
                                                type: 'print-img',
                                                data: socket_data
                                            }));
                                            // return Canvas2Image.saveAsPNG(canvas);
                                        });
                                        setTimeout(function() {
                                            $('#order-data').hide();
                                        }, 500);
                                    } else {
                                        if (order_printers == '') {
                                            var socket_data = { 'printer': false, 'order': true,
                                            'logo': '<?= !empty($store->logo) ? base_url('uploads/' . $store->logo) : ''; ?>',
                                            'text': order };
                                            socket.send(JSON.stringify({type: 'print-receipt', data: socket_data}));
                                        } else {
                                            $.each(order_printers, function() {
                                                var socket_data = {'printer': this, 'logo': '<?= !empty($store->logo) ? base_url('uploads/' . $store->logo) : ''; ?>', 'text': order};
                                                socket.send(JSON.stringify({type: 'print-receipt', data: socket_data}));
                                            });
                                        }
                                    }
                                    return false;
                                } else {
                                    bootbox.alert('<?= lang('pos_print_error'); ?>');
                                    return false;
                                }
                            }
                        }
                    </script>
                    <?php
                    if (isset($print) && !empty($print)) {
                        /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */
                        include 'remote_printing.php';
                    }
                    ?>

                    <script src="<?= $assets ?>dist/js/libraries.min.js" type="text/javascript"></script>
                    <script src="<?= $assets ?>dist/js/scripts.min.js" type="text/javascript"></script>
                    <script src="<?= $assets ?>dist/js/pos.min.js" type="text/javascript"></script>
                    <?php if ($Settings->remote_printing != 1 && $Settings->print_img) { ?>
                    <script src="<?= $assets ?>dist/js/htmlimg.js"></script>
                    <?php } ?>
                    <script>
document.getElementById("fullscreen-btn").addEventListener("click", function() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        this.innerHTML = '<i class="fas fa-compress"></i> Exit Fullscreen';
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
            this.innerHTML = '<i class="fas fa-expand"></i> Fullscreen';
        }
    }
});
</script>

                </body>
                </html>
