<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth routes
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';

// Installation routes
$route['install'] = 'install/index';
$route['update-database'] = 'install/update';

// Dashboard routes
$route['dashboard'] = 'dashboard';

// Agent routes (formerly master/kapal routes)
$route['master'] = 'master';
$route['master/tambah'] = 'master/tambah';
$route['master/edit/(:num)'] = 'master/edit/$1';
$route['master/hapus/(:num)'] = 'master/hapus/$1';
$route['master/import'] = 'master/import';
$route['master/export'] = 'master/export';

// Database routes (for peserta data)
$route['database'] = 'database';
$route['database/download/(:num)'] = 'database/download/$1';
$route['database/print_laporan/(:num)'] = 'database/print_laporan/$1';
$route['database/hapus/(:num)'] = 'database/hapus/$1';
$route['database/index2'] = 'database/index2';

// User routes
$route['user'] = 'user';
$route['user/tambah'] = 'user/tambah';
$route['user/edit/(:num)'] = 'user/edit/$1';
$route['user/hapus/(:num)'] = 'user/hapus/$1';
$route['user/profile'] = 'user/profile';
