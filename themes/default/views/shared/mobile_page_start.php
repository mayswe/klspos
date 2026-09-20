<?php defined('BASEPATH') or exit('No direct script access allowed');
$mobile_language = $this->input->get('app_lang',true) ?: $this->input->post('app_lang',true) ?: $Settings->selected_language;
$mobile_params = ['app'=>1, 'app_lang'=>$mobile_language];
$mobile_parent = explode('/', $mobile_page)[0];
if (in_array($mobile_page,['purchases/edit_expense'],true)) { $mobile_parent = 'purchases/expenses'; }
if (strpos($mobile_page,'expensetype') !== false) { $mobile_parent = 'purchases/expensetype'; }
$mobile_wide_pages = [
    'purchases/expenses', 'stocktransfers/add', 'openingstock/edit',
    'warehouses/index', 'currencies/index', 'container_boxes/index', 'shippings/index', 'gift_cards/index',
    'customers/customergroup', 'products/selling_prices', 'suppliers/advances', 'suppliers/opening',
    'settings/printers', 'reports/container_box', 'reports/customer_order_report', 'reports/customers',
    'reports/dailysales', 'reports/dailyspurchases', 'reports/investment', 'reports/monthly',
    'reports/payments', 'reports/product_summary', 'reports/products', 'reports/profit_report',
    'reports/profitloss', 'reports/purchasesupplier', 'reports/registers', 'reports/stocks',
    'reports/supplieradvances', 'reports/warehouse_stock', 'sales/opened'
];
$mobile_page_classes = 'kls-mobile-ui erp-page mobile-shared-page' .
    (in_array($mobile_page, $mobile_wide_pages, true) ? ' mobile-wide-page' : '');
$mobile_back = site_url($mobile_parent) . '?' . http_build_query($mobile_params);
?>
<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<link rel="stylesheet" href="<?= $assets ?>css/mobile-shared-pages.css?v=5">
<link rel="stylesheet" href="<?= $assets ?>css/mobile-actions.css?v=1">
<div class="<?= $mobile_page_classes; ?>" data-mobile-page="<?= htmlspecialchars($mobile_page,ENT_QUOTES,'UTF-8'); ?>" data-app-language="<?= htmlspecialchars($mobile_language,ENT_QUOTES,'UTF-8'); ?>">
<nav class="mobile-page-nav" aria-label="Page navigation">
<a class="btn btn-default" href="<?= htmlspecialchars($mobile_back,ENT_QUOTES,'UTF-8'); ?>"><i class="fa fa-arrow-left"></i> <?= lang('back'); ?></a>
</nav>
