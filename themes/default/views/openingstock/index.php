<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Opening Stock List View Router
 *
 * /openingstock        => index_web.php
 * /openingstock?app=1  => index_mobile.php
 *
 * Keep this router and both Opening Stock view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_opening_stock_view = $is_app_mode
    ? __DIR__ . '/index_mobile.php'
    : __DIR__ . '/index_web.php';

if (!is_file($selected_opening_stock_view)) {
    show_error(
        'Opening Stock List view file was not found: ' .
        html_escape(basename($selected_opening_stock_view)),
        500
    );
}

require $selected_opening_stock_view;
