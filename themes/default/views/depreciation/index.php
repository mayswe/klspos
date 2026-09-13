<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Depreciation Expense List View Router
 *
 * /depreciation        => index_web.php
 * /depreciation?app=1  => index_mobile.php
 *
 * Keep this router and both Depreciation view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_depreciation_view = $is_app_mode
    ? __DIR__ . '/index_mobile.php'
    : __DIR__ . '/index_web.php';

if (!is_file($selected_depreciation_view)) {
    show_error(
        'Depreciation Expense List view file was not found: ' .
        html_escape(basename($selected_depreciation_view)),
        500
    );
}

require $selected_depreciation_view;
