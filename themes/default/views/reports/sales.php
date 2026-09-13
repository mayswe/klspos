<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Sales Report View Router
 *
 * /reports        => sales_web.php
 * /reports?app=1  => sales_mobile.php
 *
 * Keep this router and both Sales Report view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_sales_view = $is_app_mode
    ? __DIR__ . '/sales_mobile.php'
    : __DIR__ . '/sales_web.php';

if (!is_file($selected_sales_view)) {
    show_error(
        'Sales Report view file was not found: ' .
        html_escape(basename($selected_sales_view)),
        500
    );
}

require $selected_sales_view;
