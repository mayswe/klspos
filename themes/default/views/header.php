<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $page_title . ' | ' . $Settings->site_name; ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
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
    .btn {
    -webkit-box-shadow: none;
    box-shadow: none;
    border: 1px solid transparent
}
    </style>

    <script>
    /*
     * KLSPOS sidebar fallback
     * -----------------------
     * The old layout normally relies on AdminLTE's offcanvas/treeview
     * JavaScript.  Keep the sidebar usable even when that binding is not
     * available on a page.
     */
    (function () {
        document.addEventListener('click', function (event) {
            var toggle = event.target.closest ? event.target.closest('#kls-sidebar-toggle') : null;

            if (toggle) {
                event.preventDefault();
                event.stopPropagation();

                if (window.innerWidth <= 767) {
                    document.body.classList.toggle('sidebar-open');
                } else {
                    document.body.classList.toggle('sidebar-collapse');
                }
                return;
            }

            var link = event.target.closest ? event.target.closest('.sidebar-menu li.treeview > a') : null;
            if (!link) {
                return;
            }

            var parent = link.parentNode;
            var submenu = null;

            for (var i = 0; i < parent.children.length; i++) {
                if (parent.children[i].classList && parent.children[i].classList.contains('treeview-menu')) {
                    submenu = parent.children[i];
                    break;
                }
            }

            // A treeview item with a real URL and no submenu should navigate normally.
            if (!submenu) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var isOpen = parent.classList.contains('menu-open') ||
                         parent.classList.contains('active') ||
                         window.getComputedStyle(submenu).display !== 'none';

            // Close other open top-level menus.
            var siblings = parent.parentNode.children;
            for (var s = 0; s < siblings.length; s++) {
                var sibling = siblings[s];
                if (sibling === parent || !sibling.classList || !sibling.classList.contains('treeview')) {
                    continue;
                }

                sibling.classList.remove('menu-open');
                sibling.classList.remove('active');

                for (var c = 0; c < sibling.children.length; c++) {
                    if (sibling.children[c].classList && sibling.children[c].classList.contains('treeview-menu')) {
                        if (window.jQuery) {
                            window.jQuery(sibling.children[c]).stop(true, true).slideUp(180);
                        } else {
                            sibling.children[c].style.display = 'none';
                        }
                    }
                }
            }

            if (isOpen) {
                parent.classList.remove('menu-open');
                parent.classList.remove('active');

                if (window.jQuery) {
                    window.jQuery(submenu).stop(true, true).slideUp(180);
                } else {
                    submenu.style.display = 'none';
                }
            } else {
                parent.classList.add('menu-open');
                parent.classList.add('active');

                if (window.jQuery) {
                    window.jQuery(submenu).stop(true, true).slideDown(180);
                } else {
                    submenu.style.display = 'block';
                }
            }
        }, true);

        // On phones/tablets, touching the page content closes the sidebar.
        document.addEventListener('click', function (event) {
            if (window.innerWidth > 767 || !document.body.classList.contains('sidebar-open')) {
                return;
            }

            var insideContent = event.target.closest ? event.target.closest('.content-wrapper') : null;
            if (insideContent) {
                document.body.classList.remove('sidebar-open');
            }
        }, true);
    })();
    </script>


</head>
<body class="skin-<?= $Settings->theme_style; ?> fixed sidebar-mini">
<div class="wrapper rtl rtl-inv">

    <header class="main-header">
        <a href="<?= site_url(); ?>" class="logo">
            <?php if ($store) { ?>
            <span class="logo-mini"><?= $store->code; ?></span>
            <span class="logo-lg"><?= $store->name == 'SimplePOS' ? 'Simple<b>POS</b>' : $store->name; ?></span>
            <?php } else { ?>
            <span class="logo-mini">POS</span>
            <span class="logo-lg"><?= $Settings->site_name == 'SimplePOS' ? 'Simple<b>POS</b>' : $Settings->site_name; ?></span>
            <?php } ?>
        </a>
        <nav class="navbar navbar-static-top" role="navigation">
            <a href="#" class="sidebar-toggle" id="kls-sidebar-toggle" role="button" aria-label="Toggle navigation">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <ul class="nav navbar-nav pull-left">
                <li class="dropdown hidden-xs">
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
                <?php if ($Settings->multi_store && !$this->session->userdata('has_store_id') && $this->session->userdata('store_id')) { ?>
                <li>
                    <a href="<?= site_url('stores/deselect_store'); ?>" data-toggle="tooltip" data-placement="right" title="<?= lang('deselect_store'); ?>"><i class="fa fa-square"></i></a>
                </li>
                <?php } ?>
            </ul>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="hidden-xs hidden-sm"><a href="#" class="clock"></a></li>
                    <li class="hidden-xs"><a href="<?= site_url(); ?>" data-toggle="tooltip" data-placement="bottom" title="<?= lang('dashboard'); ?>"><i class="fa fa-dashboard"></i></a></li>
                    
                    <?php if ($this->db->dbdriver != 'sqlite3') { ?>
                    <li><a href="<?= site_url('pos/view_bill'); ?>" target="_blank" data-toggle="tooltip" data-placement="bottom" title="<?= lang('view_bill'); ?>"><i class="fa fa-desktop"></i></a></li>
                    <?php } ?>
                    <li><a href="<?= site_url('pos'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?= lang('pos'); ?>"><i class="fa fa-th"></i></a></li>
                    
                    <?php     
                    if ($Admin && $preorder_alert_num && $this->session->userdata('store_id')) { ?>
                    <li>
                        <a href="<?= site_url('sales'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?= lang('pre'); ?>">
                            <i class="fa fa-bullhorn"></i>
                            <span class="label label-warning"><?= $preorder_alert_num; ?></span>
                        </a>
                    </li>
                    <?php } ?>
                    <?php     
                    if ($Admin && $due_alert_num && $this->session->userdata('store_id')) { ?>
                    <li>
                        <a href="<?= site_url('sales'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?= lang('due'); ?>">
                            <i class="fa fa-bullhorn"></i>
                            <span class="label label-warning"><?= $due_alert_num; ?></span>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if ($suspended_sales && $this->session->userdata('store_id')) { ?>
                    <li class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell"></i>
                            <span class="label label-warning"><?=sizeof($suspended_sales);?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header"><?=lang('recent_suspended_sales');?></li>
                            <li>
                                <ul class="menu">
                                    <li>
                                    <?php
                                    foreach ($suspended_sales as $ss) {
                                        echo '<a href="' . site_url('pos/?hold=' . $ss->id) . '" class="load_suspended">' . $this->tec->hrld($ss->date) . ' (' . $ss->customer_name . ')<br><strong>' . $ss->hold_ref . '</strong></a>';
                                    }
                                    ?>
                                    </li>
                                </ul>
                            </li>
                            <li class="footer"><a href="<?= site_url('sales/opened'); ?>"><?= lang('view_all'); ?></a></li>
                        </ul>
                    </li>
                    <?php } ?>
                    <li class="dropdown user user-menu" style="padding-right:5px;">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="<?= base_url('uploads/avatars/thumbs/' . ($this->session->userdata('avatar') ? $this->session->userdata('avatar') : $this->session->userdata('gender') . '.png')) ?>" class="user-image" alt="Avatar" />
                            <span class="hidden-xs"><?= $this->session->userdata('first_name') . ' ' . $this->session->userdata('last_name'); ?></span>
                        </a>
                        <ul class="dropdown-menu" style="padding-right:3px;">
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
                                    <a href="<?= site_url('auth/logout'); ?>" class="btn btn-default btn-flat<?= $this->session->userdata('register_id') ? ' sign_out' : ''; ?>"><?= lang('sign_out'); ?></a>
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
                <!-- <li class="header"><?= lang('mian_navigation'); ?></li> -->
                
                <?php if ($Admin) { ?>
                <li class="mm_welcome">
                    <a href="<?= site_url(); ?>"><i class="fa fa-dashboard"></i> <span><?= lang('dashboard'); ?></span></a>
                </li>
                <?php if ($Settings->multi_store && !$this->session->userdata('store_id')) { ?>
                <li class="mm_stores">
                    <a href="<?= site_url('stores'); ?>"><i class="fa fa-building"></i> <span><?= lang('stores'); ?></span></a>
                </li>
                <?php } ?>
                <li class="mm_pos">
                    <a href="<?= site_url('pos'); ?>"><i class="fa fa-th"></i> <span><?= lang('pos'); ?></span></a>
                </li>    
                <li class="treeview mm_reports">
                    <a href="#">
                        <i class="fa fa-square-poll-vertical"></i>
                        <span><?= lang('reports'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="reports_profit_loss"><a href="<?= site_url('reports/profit_loss'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('profit_loss_report'); ?></a></li>
                        
                        <li id="reports_sales"><a href="<?= site_url('reports/sales'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('sales_report'); ?> </a></li>
                        
                        <li id="reports_purchases"><a href="<?= site_url('reports/purchases'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('purchase_report'); ?> </a></li>
                        
                        <li id="reports_expenses"><a href="<?= site_url('reports/expenses'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('expense_report'); ?></a></li>
                        
                        <li id="reports_stocks"><a href="<?= site_url('reports/stocks'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('stock_report'); ?></a></li>
                        
                        
                        <li id="reports_dailysales"><a href="<?= site_url('reports/dailysales'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('product_summary'); ?></a></li>
                        
                        <li id="reports_dailyspurchases"><a href="<?= site_url('reports/dailyspurchases'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('reports_dailyspurchases'); ?></a></li>
                        
                        
                        
                    </ul>
                </li>
                <li class="treeview mm_warehouses">
                    <a href="<?= site_url('settings/stores'); ?>">
                        <i class="fa fa-warehouse"></i>
                        <span><?= lang('location'); ?></span>
                    </a>
                    
                </li>
                
                
              
                
                </li>
                
                <li class="treeview mm_products">
                    <a href="#">
                        <i class="fa-brands fa-product-hunt"></i>
                        <span><?= lang('products'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="products_index"><a href="<?= site_url('products'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_products'); ?></a></li>
                        <li id="products_closing"><a href="<?= site_url('products/closing'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('products_closing'); ?></a></li>
                        <li id="products_unitindex"><a href="<?= site_url('products/units'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_unit'); ?></a></li>
                        <li id="products_adjustmentsindex"><a href="<?= site_url('products/adjustments'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_adjustments'); ?></a></li>
                       <li class="products_stocktransfer">
                            <a href="<?= site_url('stocktransfers'); ?>">
                                <i class="fa-regular fa-circle-dot"></i>
                                <span><?= lang('stock_transfers'); ?></span>
                            </a>
                            
                        </li>
                        <li class="products_openingstock">
                            <a href="<?= site_url('openingstock'); ?>">
                                <i class="fa-regular fa-circle-dot"></i>
                                <span><?= lang('opening_stock'); ?></span>
                            </a>
                            
                        </li>
                        
                       
                       
                        
                    </ul>
                </li>
                <li class="treeview mm_categories">
                    <a href="#">
                        <i class="fa fa-folder"></i>
                        <span><?= lang('categories'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="categories_index"><a href="<?= site_url('categories'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_categories'); ?></a></li>
                        
                    </ul>
                </li>
                <li class="treeview mm_sales">
                    <a href="#">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('sales'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="sales_index"><a href="<?= site_url('sales'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_sales'); ?></a></li>
                        <li id="sales_report"><a href="<?= site_url('reports'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('sales_report'); ?></a></li>
                        <li id="sales_index"><a href="<?= site_url('reports/customer_order_report'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('customer_order_report'); ?></a></li>
                        <li id="sales_opened"><a href="<?= site_url('sales/opened'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_opened_bills'); ?></a></li>
                    </ul>
                </li>
                <li class="treeview mm_purchases">
                    <a href="#">
                        <i class="fa fa-plus"></i>
                        <span><?= lang('purchases'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="purchases_index"><a href="<?= site_url('purchases'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_purchases'); ?></a></li>
                        <li id="purchases_expenses"><a href="<?= site_url('purchases/expenses'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_expenses'); ?></a></li>
                        <li id="purchases_expensetype"><a href="<?= site_url('purchases/expensetype'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_expensetype'); ?></a></li>
                        
                    </ul>
                </li>
                <li class="treeview mm_depreciation">
                    <a href="#">
                        <i class="fa fa-plus"></i>
                        <span><?= lang('depreciation'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="depreciation_index"><a href="<?= site_url('depreciation'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_depreciation'); ?></a></li>
                        <li id="depreciation_create"><a href="<?= site_url('depreciation/create'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('create_depreciation'); ?></a></li>
                        
                    </ul>
                </li>
                
                <li class="treeview mm_auth mm_customers mm_suppliers">
                    <a href="#">
                        
                        <i class="fa fa-user"></i>
                        <span><?= lang('people'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="auth_users"><a href="<?= site_url('users'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_users'); ?></a></li>
                        
                        <li id="customers_index"><a href="<?= site_url('customers'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_customers'); ?></a></li>
                        <li id="customers_group"><a href="<?= site_url('customers/customergroup'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_customers_group'); ?></a></li>
                        <li id="suppliers_index"><a href="<?= site_url('suppliers'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_suppliers'); ?></a></li>
                        <li id="supplier_advances"><a href="<?= site_url('suppliers/advances'); ?>"><i class="fa fa-hand-holding-dollar"></i><?= lang('supplier_advances'); ?></a></li>
                        <li id="supplier_advances"><a href="<?= site_url('suppliers/opening'); ?>"><i class="fa fa-hand-holding-dollar"></i><?= lang('Supplier Due List'); ?></a></li>
                    </ul>
                </li>
                
                <?php } elseif ($Owner) { ?>
                <li class="mm_welcome">
                    <a href="<?= site_url(); ?>"><i class="fa fa-dashboard"></i> <span><?= lang('dashboard'); ?></span></a>
                </li>
                <?php if ($Settings->multi_store && !$this->session->userdata('store_id')) { ?>
                <li class="mm_stores">
                    <a href="<?= site_url('stores'); ?>"><i class="fa fa-building-o"></i> <span><?= lang('stores'); ?></span></a>
                </li>
                <?php } ?>
                <li class="mm_pos">
                    <a href="<?= site_url('pos'); ?>"><i class="fa fa-th"></i> <span><?= lang('pos'); ?></span></a>
                </li>
                <li class="treeview mm_stocktransfer">
                    <a href="<?= site_url('stocktransfers'); ?>">
                        <i class="fa fa-warehouse"></i>
                        <span><?= lang('stock_transfers'); ?></span>
                    </a>
                    
                </li>
                <li class="treeview mm_reports">
                        <li id="reports_dailysales"><a href="<?= site_url('reports/dailysales'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('product_summary'); ?></a></li>
                </li>
                <li class="treeview mm_purchases">
                    <a href="#">
                        <i class="fa fa-square-up-right"></i>
                        <span><?= lang('purchases'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="purchases_index"><a href="<?= site_url('purchases'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_purchases'); ?></a></li>
                        <li id="purchases_expenses"><a href="<?= site_url('purchases/expenses'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_expenses'); ?></a></li>
                        <li id="purchases_expensetype"><a href="<?= site_url('purchases/expensetype'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_expensetype'); ?></a></li>
                        </ul>
                </li>
                
                
                <li class="treeview mm_products">
                    <a href="#">
                        <i class="fa-brands fa-product-hunt"></i>&nbsp;
                        <span><?= lang('products'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="products_index"><a href="<?= site_url('products'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_products'); ?></a></li>
                        <li id="products_closing"><a href="<?= site_url('closing'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_closing'); ?></a></li>
                        <li id="products_unitindex"><a href="<?= site_url('products/units'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_unit'); ?></a></li>
                        <li id="products_adjustmentsindex"><a href="<?= site_url('products/adjustments'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_adjustments'); ?></a></li>
                        <li class="products_stocktransfer">
                    <a href="<?= site_url('stocktransfers'); ?>">
                        <i class="fa-regular fa-circle-dot"></i>
                        <?= lang('stock_transfers'); ?>
                    </a>
                    
                </li>
                       
                        
                    </ul>
                </li>
                
                    <?php if ($this->session->userdata('store_id')) { ?>
                <li class="treeview mm_sales">
                    <a href="#">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('sales'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="sales_index"><a href="<?= site_url('sales'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_sales'); ?></a></li>
                        <li id="sales_report"><a href="<?= site_url('reports'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('sales_report'); ?></a></li>
                        <li id="sales_opened"><a href="<?= site_url('sales/opened'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_opened_bills'); ?></a></li>
                    </ul>
                </li>
                
                    <?php } ?>
                
                <li class="treeview mm_auth mm_customers mm_suppliers">
                    <a href="#">
                        
                        <i class="fa fa-user"></i>
                        <span><?= lang('people'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        
                        <li id="customers_index"><a href="<?= site_url('customers'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_customers'); ?></a></li>
                        <li id="customers_group"><a href="<?= site_url('customers/customergroup'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_customers_group'); ?></a></li>
                        <li id="suppliers_index"><a href="<?= site_url('suppliers'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_suppliers'); ?></a></li>
                       </ul>
                </li>      
                <li class="treeview mm_settings">
                    <a href="#">
                        <i class="fa fa-gear"></i>
                        <span><?= lang('settings'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        
                
                <li class="treeview mm_categories">
                    <a href="<?= site_url('categories'); ?>">
                        <i class="fa fa-folder"></i>
                        <span><?= lang('categories'); ?></span>
                    </a>
                    
                </li>
                
                    </ul>
                </li>
                <li class="treeview mm_gift_cards">
                    <a href="#">
                        <i class="fa fa-credit-card"></i>
                        <span><?= lang('gift_cards'); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li id="gift_cards_index"><a href="<?= site_url('gift_cards'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_gift_cards'); ?></a></li>
                        <li id="gift_cards_add"><a href="<?= site_url('gift_cards/add'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('add_gift_card'); ?></a></li>
                    </ul>
                </li>
                <?php } elseif ($Supervisor) { ?>
                <li class="treeview mm_currencies">
                    <a href="<?= site_url('currencies'); ?>">
                        <i class="fa fa-money-bill-1"></i>
                        <span><?= lang('exchangerate'); ?></span>
                    </a>
                    
                </li>
                <li class="mm_products"><a href="<?= site_url('products'); ?>"><i class="fa fa-barcode"></i> <span><?= lang('products'); ?></span></a></li>
                <li class="mm_categories"><a href="<?= site_url('categories'); ?>"><i class="fa fa-folder-open"></i> <span><?= lang('categories'); ?></span></a></li>
                    <?php if ($this->session->userdata('store_id')) { ?>
                        <li class="treeview mm_sales">
                            <a href="#">
                                <i class="fa fa-shopping-cart"></i>
                                <span><?= lang('sales'); ?></span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li id="sales_index"><a href="<?= site_url('sales'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_sales'); ?></a></li>
                                <li id="sales_report"><a href="<?= site_url('reports'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('sales_report'); ?></a></li>
                                <li id="sales_opened"><a href="<?= site_url('sales/opened'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_opened_bills'); ?></a></li>
                            </ul>
                        </li>
                        <li class="treeview mm_purchases">
                            <a href="#">
                                <i class="fa fa-plus"></i>
                                <span><?= lang('purchases'); ?></span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li id="purchases_index"><a href="<?= site_url('purchases'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_purchases'); ?></a></li>
                            </ul>
                        </li>
                        <?php } ?>
                            <li class="treeview mm_gift_cards">
                                <a href="#">
                                    <i class="fa fa-credit-card"></i>
                                    <span><?= lang('gift_cards'); ?></span>
                                    <i class="fa fa-angle-left pull-right"></i>
                                </a>
                                <ul class="treeview-menu">
                                    <li id="gift_cards_index"><a href="<?= site_url('gift_cards'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_gift_cards'); ?></a></li>
                                    <li id="gift_cards_add"><a href="<?= site_url('gift_cards/add'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('add_gift_card'); ?></a></li>
                                </ul>
                            </li>
                            <li class="treeview mm_customers">
                                <a href="#">
                                    <i class="fa fa-users"></i>
                                    <span><?= lang('customers'); ?></span>
                                    <i class="fa fa-angle-left pull-right"></i>
                                </a>
                                <ul class="treeview-menu">
                                    <li id="customers_index"><a href="<?= site_url('customers'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_customers'); ?></a></li>
                                    <li id="customers_group"><a href="<?= site_url('customers/customergroup'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_customers_group'); ?></a></li>
                                    <li id="suppliers_index"><a href="<?= site_url('suppliers'); ?>"><i class="fa-regular fa-circle-dot"></i> <?= lang('list_suppliers'); ?></a></li>
                                </ul>
                            </li>
                <?php } else { ?>
                
                
                <li class="mm_pos">
                    <a href="<?= site_url('pos'); ?>"><i class="fa fa-th"></i> <span><?= lang('pos'); ?></span></a>
                </li>
                <?php } ?>

            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <ol class="breadcrumb">
                <li><a href="<?= site_url(); ?>"><i class="fa fa-dashboard"></i> <?= lang('home'); ?></a></li>
                <?php
                foreach ($bc as $b) {
                    if ($b['link'] === '#') {
                        echo '<li class="active">' . $b['page'] . '</li>';
                    } else {
                        echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
                    }
                }
                ?>
            </ol>
        </section>

        <div class="col-lg-12 alerts">
            <div id="custom-alerts" style="display:none;">
                <div class="alert alert-dismissable">
                    <div class="custom-msg"></div>
                </div>
            </div>
            <?php if ($error) { ?>
            <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <h4><i class="icon fa fa-ban"></i> <?= lang('error'); ?></h4>
                <?= $error; ?>
            </div>
            <?php } if ($warning) { ?>
            <div class="alert alert-warning alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <h4><i class="icon fa fa-warning"></i> <?= lang('warning'); ?></h4>
                <?= $warning; ?>
            </div>
            <?php } if ($message) { ?>
            <div class="alert alert-success alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <h4>    <i class="icon fa fa-check"></i> <?= lang('Success'); ?></h4>
                <?= $message; ?>
            </div>
            <?php } ?>
        </div>
        <div class="clearfix"></div>