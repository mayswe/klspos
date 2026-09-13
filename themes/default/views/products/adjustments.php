<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Stock Adjustments View Router
 *
 * /products/adjustments        => adjustments_web.php
 * /products/adjustments?app=1  => adjustments_mobile.php
 *
 * Keep this router and both Stock Adjustments view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_adjustments_view = $is_app_mode
    ? __DIR__ . '/adjustments_mobile.php'
    : __DIR__ . '/adjustments_web.php';

if (!is_file($selected_adjustments_view)) {
    show_error(
        'Stock Adjustments view file was not found: ' .
        html_escape(basename($selected_adjustments_view)),
        500
    );
}

require $selected_adjustments_view;
