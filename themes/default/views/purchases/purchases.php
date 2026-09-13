<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Sales View Router
 *
 * /sales        => purchases_web.php
 * /sales?app=1  => purchases_mobile.php
 *
 * Keep this file, sales_web.php and sales_mobile.php
 * together in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_sales_view = $is_app_mode
    ? __DIR__ . '/purchases_mobile.php'
    : __DIR__ . '/purchases_web.php';

if (!is_file($selected_sales_view)) {
    show_error(
        'Purchases view file was not found: ' .
        html_escape(basename($selected_sales_view)),
        500
    );
}

require $selected_sales_view;
