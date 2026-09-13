<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Expenses Report View Router
 *
 * /reports/expenses        => expenses_web.php
 * /reports/expenses?app=1  => expenses_mobile.php
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_expenses_view = $is_app_mode
    ? __DIR__ . '/expenses_mobile.php'
    : __DIR__ . '/expenses_web.php';

if (!is_file($selected_expenses_view)) {
    show_error(
        'Expenses Report view file was not found: ' .
        html_escape(basename($selected_expenses_view)),
        500
    );
}

require $selected_expenses_view;
