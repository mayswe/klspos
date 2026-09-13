<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|   example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|   http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|   $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|   $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|   $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|       my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['users'] = 'auth/users';
$route['login'] = 'auth/login';
$route['mobile-login'] = 'auth/mobile_login';
$route['logout'] = 'auth/logout';
$route['pos/(:num)'] = 'pos/index/$1';
$route['users/add'] = 'auth/create_user';
$route['logout/(:any)'] = 'auth/logout/$1';
$route['users/profile/(:num)'] = 'auth/profile/$1';
$route['cron/closing-balance'] = 'cron/add_closing_bulk';

$route['api/products'] = 'api/products/index';
$route['api/products/(:num)'] = 'api/products/show/$1';
$route['api/products/(:num)/batches'] = 'api/products/batches/$1';


// Sales API
$route['api/sales']       = 'api/sales/index';
$route['api/sales/store'] = 'api/sales/store';
$route['api/sales/view/(:num)'] = 'api/sales/view/$1';
$route['api/sales/update/(:num)'] = 'api/sales/update/$1';
$route['api/sales/delete/(:num)'] = 'api/sales/delete/$1';
$route['api/sales/search']       = 'api/sales/search';


// Customers API
$route['api/customers']       = 'api/customers/index';
$route['api/customers/store'] = 'api/customers/store';
