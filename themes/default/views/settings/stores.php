<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Stores List View Router
 *
 * /settings/stores        => stores_web.php
 * /settings/stores?app=1  => stores_mobile.php
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_stores_view = $is_app_mode
    ? __DIR__ . '/stores_mobile.php'
    : __DIR__ . '/stores_web.php';

if (!is_file($selected_stores_view)) {
    show_error(
        'Stores List view file was not found: ' .
        html_escape(basename($selected_stores_view)),
        500
    );
}

require $selected_stores_view;
