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
$route['database/rejected_data'] = 'database/rejected_data';
$route['database/download_rejected_data'] = 'database/download_rejected_data';
$route['database/download_failed_import'] = 'database/download_failed_import';
$route['database/clear_rejected_data'] = 'database/clear_rejected_data';
$route['database/delete_rejected/(:num)'] = 'database/delete_rejected/$1';
$route['database/download_barcode_attachments'] = 'database/download_barcode_attachments';
$route['todo'] = 'todo';


// User routes
$route['user'] = 'user';
$route['user/tambah'] = 'user/tambah';
$route['user/edit/(:num)'] = 'user/edit/$1';
$route['user/hapus/(:num)'] = 'user/hapus/$1';
$route['user/profile'] = 'user/profile';

// Settings routes
$route['settings'] = 'settings';
$route['settings/backup_database'] = 'settings/backup_database';
$route['settings/backup_database_ftp'] = 'settings/backup_database_ftp';
$route['settings/download_backup/(:any)'] = 'settings/download_backup/$1';
$route['settings/delete_backup/(:any)'] = 'settings/delete_backup/$1';
$route['settings/get_backup_files'] = 'settings/get_backup_files';

// Email Management routes
$route['email_management'] = 'email_management';
$route['email_management/create'] = 'email_management/create';
$route['email_management/edit/(:any)'] = 'email_management/edit/$1';
$route['email_management/delete/(:any)'] = 'email_management/delete/$1';
$route['email_management/check_accounts'] = 'email_management/check_accounts';
$route['email_management/test_connection'] = 'email_management/test_connection';

// Email Middleware routes
$route['email_middleware'] = 'email_middleware';
$route['email_middleware/create'] = 'email_middleware/create';
$route['email_middleware/edit/(:any)'] = 'email_middleware/edit/$1';
$route['email_middleware/delete/(:any)'] = 'email_middleware/delete/$1';
$route['email_middleware/check_accounts'] = 'email_middleware/check_accounts';
$route['email_middleware/test_connection'] = 'email_middleware/test_connection';
$route['email_middleware/debug'] = 'email_middleware/debug_middleware';

// Test routes
$route['test_mysqldump'] = 'test_mysqldump';
$route['test_middleware'] = 'test_middleware_direct';
$route['test_middleware_simple'] = 'test_middleware_simple';
$route['email_middleware/test_simple'] = 'email_middleware/test_simple';
$route['email_middleware/test_middleware'] = 'email_middleware/test_middleware';

// Email management routes (new cPanel implementation)
$route['email'] = 'email/index';
$route['email/create'] = 'email/create';
$route['email/edit/(:any)'] = 'email/edit/$1';
$route['email/delete/(:any)'] = 'email/delete/$1';
$route['email/check_accounts'] = 'email/check_accounts';
$route['email/test_connection'] = 'email/test_connection';

// Test routes
$route['test_cpanel'] = 'test_cpanel/index';
$route['test_routing'] = 'test_routing/index';
$route['email_simple'] = 'email_simple/index';
$route['email_simple/test'] = 'email_simple/test';
$route['email_test'] = 'email_test/index';
$route['email_test/config'] = 'email_test/config';
$route['email_test/library'] = 'email_test/library';

// Email management routes (new improved implementation)
$route['email_new'] = 'email_new/index';
$route['email_new/create'] = 'email_new/create';
$route['email_new/edit/(:any)'] = 'email_new/edit/$1';
$route['email_new/delete/(:any)'] = 'email_new/delete/$1';
$route['email_new/check_accounts'] = 'email_new/check_accounts';
$route['email_new/test_connection'] = 'email_new/test_connection';

// Upload routes
$route['upload/upload_barcode'] = 'upload/upload_barcode';
$route['upload/delete_barcode'] = 'upload/delete_barcode';
$route['upload/view_barcode/(:any)'] = 'upload/view_barcode/$1';

// Setup routes
$route['setup'] = 'setup';
