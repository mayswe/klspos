<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Expense Type List View Router
 *
 * /purchases/expensetype        => expensetype_web.php
 * /purchases/expensetype?app=1  => expensetype_mobile.php
 *
 * Keep this router and both Expense Type view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_expensetype_view = $is_app_mode
    ? __DIR__ . '/expensetype_mobile.php'
    : __DIR__ . '/expensetype_web.php';

if (!is_file($selected_expensetype_view)) {
    show_error(
        'Expense Type List view file was not found: ' .
        html_escape(basename($selected_expensetype_view)),
        500
    );
}

require $selected_expensetype_view;
