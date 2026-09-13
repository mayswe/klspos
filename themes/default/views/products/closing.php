<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Closing Balance View Router
 *
 * /products/closing        => closing_web.php
 * /products/closing?app=1  => closing_mobile.php
 *
 * Keep this router and both Closing Balance view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_closing_view = $is_app_mode
    ? __DIR__ . '/closing_mobile.php'
    : __DIR__ . '/closing_web.php';

if (!is_file($selected_closing_view)) {
    show_error(
        'Closing Balance view file was not found: ' .
        html_escape(basename($selected_closing_view)),
        500
    );
}

require $selected_closing_view;
