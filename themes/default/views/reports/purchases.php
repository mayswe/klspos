<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Purchases Report View Router
 *
 * /reports/purchases        => purchases_web.php
 * /reports/purchases?app=1  => purchases_mobile.php
 *
 * Keep this router and both Purchases Report view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_purchases_view = $is_app_mode
    ? __DIR__ . '/purchases_mobile.php'
    : __DIR__ . '/purchases_web.php';

if (!is_file($selected_purchases_view)) {
    show_error(
        'Purchases Report view file was not found: ' .
        html_escape(basename($selected_purchases_view)),
        500
    );
}

require $selected_purchases_view;
