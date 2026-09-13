<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Categories List View Router
 *
 * /categories        => index_web.php
 * /categories?app=1  => index_mobile.php
 *
 * Keep this router and both Categories List view files
 * in the same view directory.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$selected_categories_view = $is_app_mode
    ? __DIR__ . '/index_mobile.php'
    : __DIR__ . '/index_web.php';

if (!is_file($selected_categories_view)) {
    show_error(
        'Categories List view file was not found: ' .
        html_escape(basename($selected_categories_view)),
        500
    );
}

require $selected_categories_view;
