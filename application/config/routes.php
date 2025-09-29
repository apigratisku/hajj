<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth routes
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['nusukbot'] = 'nusukbot';
$route['telegram_bot'] = 'telegram_bot';
$route['telegram_bot/webhook'] = 'telegram_bot/webhook';
$route['telegram_bot/set_webhook'] = 'telegram_bot/set_webhook';
$route['telegram_bot/delete_webhook'] = 'telegram_bot/delete_webhook';
$route['telegram_bot/get_webhook_info'] = 'telegram_bot/get_webhook_info';
$route['api/test'] = 'api/test';
$route['api/timezone_info'] = 'api/timezone_info';
$route['api/test_flexible_search'] = 'api/test_flexible_search';
$route['api/check_barcode_status'] = 'api/check_barcode_status';
$route['api/debug_database'] = 'api/debug_database';
$route['api/schedule_notifications'] = 'api/schedule_notifications';
$route['api/overdue_schedules'] = 'api/overdue_schedules';




// Parsing routes
$route['parsing'] = 'parsing/index';
$route['parsing/upload_pdf'] = 'parsing/upload_pdf';
$route['parsing/download_excel'] = 'parsing/download_excel';
$route['parsing/clear_session'] = 'parsing/clear_session';
$route['parsing/view_data'] = 'parsing/view_data';
$route['parsing/delete_data/(:num)'] = 'parsing/delete_data/$1';
$route['parsing/bulk_delete'] = 'parsing/bulk_delete';
$route['parsing/debug_parsing'] = 'parsing/debug_parsing';

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

// Parsing routes
$route['parsing'] = 'parsing';
$route['parsing/upload_pdf'] = 'parsing/upload_pdf';
$route['parsing/download_excel'] = 'parsing/download_excel';
$route['parsing/clear_session'] = 'parsing/clear_session';
$route['parsing/view_data'] = 'parsing/view_data';
$route['parsing/delete_data/(:num)'] = 'parsing/delete_data/$1';


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
$route['email'] = 'email/index';
$route['email/create'] = 'email/create';
$route['email/edit/(:any)'] = 'email/edit/$1';
$route['email/delete/(:any)'] = 'email/delete/$1';
$route['email/check_accounts'] = 'email/check_accounts';
$route['email/test_connection'] = 'email/test_connection';
$route['email/get_quota_info/(:any)'] = 'email/get_email_quota_info/$1';
$route['email/bulk_delete'] = 'email/bulk_delete';

// Upload routes
$route['upload/upload_barcode'] = 'upload/upload_barcode';
$route['upload/delete_barcode'] = 'upload/delete_barcode';
$route['upload/view_barcode/(:any)'] = 'upload/view_barcode/$1';

// Setup routes
$route['setup'] = 'setup';

// Test routes
$route['email_test/jupiter'] = 'email_test/jupiter';
$route['parsing/test'] = 'parsing/test';
$route['parsing/debug'] = 'parsing/debug';
$route['parsing/debug_text'] = 'parsing/debug_text';
$route['parsing/simple_test'] = 'parsing/simple_test';

// Test parsing routes
$route['parsing_test/test'] = 'parsing_test/test';
$route['parsing_test/debug'] = 'parsing_test/debug';
$route['parsing_test/simple_test'] = 'parsing_test/simple_test';
$route['parsing_test/parse'] = 'parsing_test/parse';
