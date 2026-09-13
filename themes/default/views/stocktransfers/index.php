<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Stock Transfers List View Router
 *
 * /stocktransfers        => index_web.php
 * /stocktransfers?app=1  => index_mobile.php
 *
 * Keep this router and both Stock Transfers view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_stock_transfers_view = $is_app_mode
    ? __DIR__ . '/index_mobile.php'
    : __DIR__ . '/index_web.php';

if (!is_file($selected_stock_transfers_view)) {
    show_error(
        'Stock Transfers List view file was not found: ' .
        html_escape(basename($selected_stock_transfers_view)),
        500
    );
}

require $selected_stock_transfers_view;
