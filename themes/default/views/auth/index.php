<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Users List View Router
 *
 * /users        => index_web.php
 * /users?app=1  => index_mobile.php
 *
 * Keep this router and both Users List view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_users_view = $is_app_mode
    ? __DIR__ . '/index_mobile.php'
    : __DIR__ . '/index_web.php';

if (!is_file($selected_users_view)) {
    show_error(
        'Users List view file was not found: ' .
        html_escape(basename($selected_users_view)),
        500
    );
}

require $selected_users_view;
