<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Low Stock Alerts View Router
 *
 * /reports/alerts        => alerts_web.php
 * /reports/alerts?app=1  => alerts_mobile.php
 *
 * Keep this router and both Alerts view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_alerts_view = $is_app_mode
    ? __DIR__ . '/alerts_mobile.php'
    : __DIR__ . '/alerts_web.php';

if (!is_file($selected_alerts_view)) {
    show_error(
        'Low Stock Alerts view file was not found: ' .
        html_escape(basename($selected_alerts_view)),
        500
    );
}

require $selected_alerts_view;
