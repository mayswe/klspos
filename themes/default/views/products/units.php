<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Units List View Router
 *
 * /products/units        => units_web.php
 * /products/units?app=1  => units_mobile.php
 *
 * Keep this router and both Units List view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_units_view = $is_app_mode
    ? __DIR__ . '/units_mobile.php'
    : __DIR__ . '/units_web.php';

if (!is_file($selected_units_view)) {
    show_error(
        'Units List view file was not found: ' .
        html_escape(basename($selected_units_view)),
        500
    );
}

require $selected_units_view;
