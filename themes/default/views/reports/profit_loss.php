<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Profit & Loss View Router
 *
 * /reports/profit_loss        => profit_loss_web.php
 * /reports/profit_loss?app=1  => profit_loss_mobile.php
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_profit_loss_view = $is_app_mode
    ? __DIR__ . '/profit_loss_mobile.php'
    : __DIR__ . '/profit_loss_web.php';

if (!is_file($selected_profit_loss_view)) {
    show_error(
        'Profit & Loss view file was not found: ' .
        html_escape(basename($selected_profit_loss_view)),
        500
    );
}

require $selected_profit_loss_view;
