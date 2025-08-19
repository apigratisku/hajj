<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('session');
        $this->load->library('telegram_notification');
        $this->load->helper('url');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Check if user is admin
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini');
            redirect('dashboard');
        }
    }

    public function index() {
        $data['title'] = 'Pengaturan Sistem';
        
        // Add debug information
        $data['debug_info'] = $this->get_debug_info();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('settings/index', $data);
        $this->load->view('templates/footer');
    }

    public function backup_database() {
        // Check if request is AJAX or has X-Requested-With header
        if (!$this->input->is_ajax_request() && !$this->input->get_request_header('X-Requested-With')) {
            show_404();
            return;
        }

        // Check if user is still logged in
        if (!$this->session->userdata('logged_in')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Session expired. Silakan login kembali.',
                    'redirect' => base_url('auth')
                ]));
            return;
        }
        
        // Check if user is admin
        if ($this->session->userdata('role') != 'admin') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke halaman ini.',
                    'redirect' => base_url('dashboard')
                ]));
            return;
        }
        
        // Check if this is a test request
        if ($this->input->post('test')) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'test_success',
                    'message' => 'Backup endpoint is accessible',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'server_info' => [
                        'php_version' => PHP_VERSION,
                        'exec_available' => function_exists('exec'),
                        'mysqli_available' => extension_loaded('mysqli'),
                        'backup_dir_exists' => is_dir(FCPATH . 'backups'),
                        'backup_dir_writable' => is_writable(FCPATH . 'backups'),
                        'max_execution_time' => ini_get('max_execution_time'),
                        'memory_limit' => ini_get('memory_limit')
                    ]
                ]));
            return;
        }

        // Enhanced logging for debugging
        log_message('info', '=== BACKUP DATABASE STARTED ===');
        log_message('info', 'Request Method: ' . $_SERVER['REQUEST_METHOD']);
        log_message('info', 'User Agent: ' . $_SERVER['HTTP_USER_AGENT']);
        log_message('info', 'Remote IP: ' . $_SERVER['REMOTE_ADDR']);
        log_message('info', 'Session User ID: ' . $this->session->userdata('user_id'));
        log_message('info', 'Session Role: ' . $this->session->userdata('role'));

        try {
            // Get database configuration
            $hostname = $this->db->hostname;
            $username = $this->db->username;
            $password = $this->db->password;
            $database = $this->db->database;
            
            // Log database configuration (without password)
            log_message('info', 'Database Host: ' . $hostname);
            log_message('info', 'Database User: ' . $username);
            log_message('info', 'Database Name: ' . $database);
            log_message('info', 'Database Password: [HIDDEN]');
            
            
            // Create backup filename with timestamp
            $backup_filename = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';
            $backup_path = FCPATH . 'backups/' . $backup_filename;
            
            log_message('info', 'Backup Filename: ' . $backup_filename);
            log_message('info', 'Backup Path: ' . $backup_path);
            log_message('info', 'FCPATH: ' . FCPATH);
            
            // Create backups directory if it doesn't exist (cPanel optimized)
            if (!is_dir(FCPATH . 'backups')) {
                log_message('info', 'Backup directory does not exist, creating...');
                if (!mkdir(FCPATH . 'backups', 0755, true)) {
                    log_message('error', 'Failed to create backup directory: ' . FCPATH . 'backups');
                    log_message('error', 'Current permissions: ' . substr(sprintf('%o', fileperms(FCPATH)), -4));
                    throw new Exception('Gagal membuat direktori backups. Periksa permission folder.');
                } else {
                    log_message('info', 'Backup directory created successfully');
                }
            } else {
                log_message('info', 'Backup directory already exists');
            }
            
            // Ensure directory is writable
            if (!is_writable(FCPATH . 'backups')) {
                log_message('error', 'Backup directory is not writable: ' . FCPATH . 'backups');
                log_message('error', 'Directory permissions: ' . substr(sprintf('%o', fileperms(FCPATH . 'backups')), -4));
                log_message('error', 'Current user: ' . get_current_user());
                log_message('error', 'Process user: ' . posix_getpwuid(posix_geteuid())['name']);
                throw new Exception('Direktori backups tidak dapat ditulis. Periksa permission folder.');
            } else {
                log_message('info', 'Backup directory is writable');
            }
            
            // Test database connection first
            log_message('info', 'Testing database connection...');
            $this->test_database_connection($hostname, $username, $password, $database);
            log_message('info', 'Database connection test passed');
            
            // Force using PHP-based backup method for better hosting compatibility
            // Skip mysqldump entirely to avoid escapeshellarg() and exec() issues
            log_message('info', 'Using PHP-based backup method (phpMyAdmin format) - forced for hosting compatibility');
                $this->create_php_backup($hostname, $username, $password, $database, $backup_path);
                $return_var = 0;
                $output = [];
            

            
            log_message('info', 'Checking backup result...');
            log_message('info', 'Return var: ' . $return_var);
            log_message('info', 'File exists: ' . (file_exists($backup_path) ? 'Yes' : 'No'));
            
            if ($return_var === 0 && file_exists($backup_path)) {
                log_message('info', 'Backup file created successfully');
                // Get file size
                $file_size = filesize($backup_path);
                log_message('info', 'Backup file size: ' . $file_size . ' bytes');
                
                if ($file_size === 0) {
                    log_message('error', 'Backup file is empty');
                    throw new Exception('File backup kosong. Periksa konfigurasi database.');
                }
                
                $file_size_formatted = $this->format_bytes($file_size);
                log_message('info', 'Backup file size formatted: ' . $file_size_formatted);
                
                // Clean up old backup files
                log_message('info', 'Cleaning up old backup files...');
                $deleted_count = $this->cleanup_old_backups(7);
                log_message('info', 'Deleted ' . $deleted_count . ' old backup files');
                
                // Kirim notifikasi Telegram untuk backup berhasil
                $this->telegram_notification->backup_notification('Backup', $backup_filename, true);
                
                $response = [
                    'status' => 'success',
                    'message' => 'Backup database berhasil dibuat' . ($deleted_count > 0 ? " (dihapus {$deleted_count} file lama)" : ''),
                    'filename' => $backup_filename,
                    'file_size' => $file_size_formatted,
                    'download_url' => base_url('settings/download_backup/' . $backup_filename)
                ];
                
                log_message('info', 'Backup completed successfully');
                log_message('info', 'Response: ' . json_encode($response));
            } else {
                log_message('error', 'Backup failed - return var: ' . $return_var . ', file exists: ' . (file_exists($backup_path) ? 'Yes' : 'No'));
                $error_msg = 'Gagal membuat backup database';
                if (!empty($output)) {
                    log_message('error', 'Backup output: ' . implode(' ', $output));
                    $error_msg .= ': ' . implode(' ', $output);
                    
                    // Check for specific mysqldump errors
                    $output_str = implode(' ', $output);
                    if (strpos($output_str, 'Access denied') !== false) {
                        log_message('error', 'Access denied error detected');
                        $error_msg = 'Access denied untuk user database. Periksa username dan password database.';
                    } elseif (strpos($output_str, 'Unknown database') !== false) {
                        log_message('error', 'Unknown database error detected');
                        $error_msg = 'Database tidak ditemukan. Periksa nama database.';
                    } elseif (strpos($output_str, 'Can\'t connect') !== false) {
                        log_message('error', 'Connection error detected');
                        $error_msg = 'Tidak dapat terhubung ke server database. Periksa hostname database.';
                    } elseif (strpos($output_str, 'command not found') !== false) {
                        log_message('error', 'Command not found error detected');
                        $error_msg = 'mysqldump tidak ditemukan. Sistem akan menggunakan metode backup PHP.';
                    }
                }
                
                // If exec failed and we have a backup file, check if it's valid
                if (file_exists($backup_path)) {
                    $file_size = filesize($backup_path);
                    if ($file_size > 0) {
                        // File exists and has content, consider it successful
                        $file_size_formatted = $this->format_bytes($file_size);
                        $response = [
                            'status' => 'success',
                            'message' => 'Backup database berhasil dibuat (metode alternatif)',
                            'filename' => $backup_filename,
                            'file_size' => $file_size_formatted,
                            'download_url' => base_url('settings/download_backup/' . $backup_filename)
                        ];
                        
                        $this->output
                            ->set_content_type('application/json')
                            ->set_output(json_encode($response));
                        return;
                    }
                }
                
                throw new Exception($error_msg);
            }
            
        } catch (Exception $e) {
            // Kirim notifikasi Telegram untuk backup gagal
            $this->telegram_notification->backup_notification('Backup', '', false);
            
            log_message('error', '=== BACKUP DATABASE EXCEPTION ===');
            log_message('error', 'Exception message: ' . $e->getMessage());
            log_message('error', 'Exception file: ' . $e->getFile());
            log_message('error', 'Exception line: ' . $e->getLine());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
            log_message('error', '=== END BACKUP DATABASE EXCEPTION ===');
            
            $response = [
                'status' => 'error',
                'message' => 'Gagal membuat backup database: ' . $e->getMessage()
            ];
        }
        
        log_message('info', '=== BACKUP DATABASE COMPLETED ===');
        log_message('info', 'Final response: ' . json_encode($response));
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function backup_database_ftp() {
        // Check if request is AJAX or has X-Requested-With header
        if (!$this->input->is_ajax_request() && !$this->input->get_request_header('X-Requested-With')) {
            show_404();
            return;
        }

        try {
            // Get FTP configuration from POST
            $ftp_host = $this->input->post('ftp_host');
            $ftp_username = $this->input->post('ftp_username');
            $ftp_password = $this->input->post('ftp_password');
            $ftp_port = $this->input->post('ftp_port') ?: 21;
            $ftp_path = $this->input->post('ftp_path') ?: '/';
            
            if (empty($ftp_host) || empty($ftp_username) || empty($ftp_password)) {
                throw new Exception('Konfigurasi FTP tidak lengkap');
            }
            
            // First create local backup using the same method as local backup
            $hostname = $this->db->hostname;
            $username = $this->db->username;
            $password = $this->db->password;
            $database = $this->db->database;
            
            $backup_filename = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';
            $backup_path = FCPATH . 'backups/' . $backup_filename;
            
            // Create backups directory if it doesn't exist (cPanel optimized)
            if (!is_dir(FCPATH . 'backups')) {
                if (!mkdir(FCPATH . 'backups', 0755, true)) {
                    throw new Exception('Gagal membuat direktori backups. Periksa permission folder.');
                }
            }
            
            // Ensure directory is writable
            if (!is_writable(FCPATH . 'backups')) {
                throw new Exception('Direktori backups tidak dapat ditulis. Periksa permission folder.');
            }
            
            // Test database connection first
            $this->test_database_connection($hostname, $username, $password, $database);
            
            // Force using PHP-based backup method for better hosting compatibility
            // Skip mysqldump entirely to avoid escapeshellarg() and exec() issues
            log_message('debug', 'Using PHP-based backup method (phpMyAdmin format) - forced for hosting compatibility');
                $this->create_php_backup($hostname, $username, $password, $database, $backup_path);
                $return_var = 0;
                $output = [];
            
            if ($return_var !== 0 || !file_exists($backup_path)) {
                $error_msg = 'Gagal membuat backup database lokal';
                if (!empty($output)) {
                    $error_msg .= ': ' . implode(' ', $output);
                    
                    // Check for specific mysqldump errors
                    $output_str = implode(' ', $output);
                    if (strpos($output_str, 'Access denied') !== false) {
                        $error_msg = 'Access denied untuk user database. Periksa username dan password database.';
                    } elseif (strpos($output_str, 'Unknown database') !== false) {
                        $error_msg = 'Database tidak ditemukan. Periksa nama database.';
                    } elseif (strpos($output_str, 'Can\'t connect') !== false) {
                        $error_msg = 'Tidak dapat terhubung ke server database. Periksa hostname database.';
                    }
                }
                throw new Exception($error_msg);
            }
            
            // Check if file is not empty
            if (filesize($backup_path) === 0) {
                throw new Exception('File backup kosong. Periksa konfigurasi database.');
            }
            
            // Upload to FTP server
            $ftp_connection = ftp_connect($ftp_host, $ftp_port, 30);
            if (!$ftp_connection) {
                throw new Exception('Tidak dapat terhubung ke server FTP: ' . $ftp_host . ':' . $ftp_port);
            }
            
            if (!ftp_login($ftp_connection, $ftp_username, $ftp_password)) {
                throw new Exception('Gagal login ke server FTP. Periksa username dan password.');
            }
            
            // Enable passive mode
            ftp_pasv($ftp_connection, true);
            
            // Create remote directory if it doesn't exist
            $remote_path = rtrim($ftp_path, '/') . '/' . $backup_filename;
            
            // Upload file
            if (!ftp_put($ftp_connection, $remote_path, $backup_path, FTP_BINARY)) {
                $ftp_error = error_get_last();
                $error_message = isset($ftp_error['message']) ? $ftp_error['message'] : 'Unknown error';
                throw new Exception('Gagal mengupload file ke server FTP: ' . $error_message);
            }
            
            // Close FTP connection
            ftp_close($ftp_connection);
            
            // Delete local backup file
            if (file_exists($backup_path)) {
                unlink($backup_path);
            }
            
            // Clean up old backup files on FTP server
            $deleted_ftp_count = $this->cleanup_old_ftp_backups($ftp_host, $ftp_username, $ftp_password, $ftp_path, 7);
            
            $response = [
                'status' => 'success',
                'message' => 'Backup database berhasil diupload ke server FTP' . ($deleted_ftp_count > 0 ? " (dihapus {$deleted_ftp_count} file lama di FTP)" : ''),
                'filename' => $backup_filename,
                'ftp_host' => $ftp_host,
                'ftp_path' => $ftp_path
            ];
            
        } catch (Exception $e) {
            log_message('error', 'FTP backup error: ' . $e->getMessage());
            $response = [
                'status' => 'error',
                'message' => 'Gagal upload backup ke FTP: ' . $e->getMessage()
            ];
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function download_backup($filename) {
        // Validate filename
        if (empty($filename) || !preg_match('/^backup_.*\.sql$/', $filename)) {
            $this->session->set_flashdata('error', 'Nama file tidak valid');
            redirect('settings');
        }
        
        $file_path = FCPATH . 'backups/' . $filename;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File backup tidak ditemukan');
            redirect('settings');
        }
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Output file content
        readfile($file_path);
        exit;
    }

    public function delete_backup($filename) {
        // Check if request is AJAX or has X-Requested-With header
        if (!$this->input->is_ajax_request() && !$this->input->get_request_header('X-Requested-With')) {
            show_404();
            return;
        }
        
        // Validate filename
        if (empty($filename) || !preg_match('/^backup_.*\.sql$/', $filename)) {
            $response = [
                'status' => 'error',
                'message' => 'Nama file tidak valid'
            ];
        } else {
            $file_path = FCPATH . 'backups/' . $filename;
            
            if (file_exists($file_path) && unlink($file_path)) {
                $response = [
                    'status' => 'success',
                    'message' => 'File backup berhasil dihapus'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Gagal menghapus file backup'
                ];
            }
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function get_backup_files() {
        // Check if request is AJAX or has X-Requested-With header
        if (!$this->input->is_ajax_request() && !$this->input->get_request_header('X-Requested-With')) {
            show_404();
            return;
        }
        
        $backup_dir = FCPATH . 'backups/';
        $files = [];
        
        if (is_dir($backup_dir)) {
            $backup_files = glob($backup_dir . 'backup_*.sql');
            
            foreach ($backup_files as $file) {
                $filename = basename($file);
                $file_size = filesize($file);
                $file_date = date('Y-m-d H:i:s', filemtime($file));
                
                $files[] = [
                    'filename' => $filename,
                    'size' => $this->format_bytes($file_size),
                    'date' => $file_date,
                    'download_url' => base_url('settings/download_backup/' . $filename)
                ];
            }
            
            // Sort by date (newest first)
            usort($files, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($files));
    }

    private function format_bytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    private function find_mysqldump() {
        // Common paths for mysqldump (prioritized for cPanel)
        log_message('info', '=== SEARCHING FOR MYSQLDUMP ===');
        $possible_paths = array(
            'mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/mysql/bin/mysqldump',
            '/opt/alt/mysql80/bin/mysqldump',
            '/opt/alt/mysql57/bin/mysqldump',
            '/opt/alt/mysql56/bin/mysqldump',
            '/opt/alt/mysql55/bin/mysqldump',
            '/opt/alt/mysql51/bin/mysqldump',
            '/opt/alt/mysql50/bin/mysqldump',
            '/opt/alt/mysql41/bin/mysqldump',
            '/opt/alt/mysql40/bin/mysqldump',
            '/opt/alt/mysql32/bin/mysqldump',
            '/opt/alt/mysql31/bin/mysqldump',
            '/opt/alt/mysql30/bin/mysqldump',
            '/opt/alt/mysql29/bin/mysqldump',
            '/opt/alt/mysql28/bin/mysqldump',
            '/opt/alt/mysql27/bin/mysqldump',
            '/opt/alt/mysql26/bin/mysqldump',
            '/opt/alt/mysql25/bin/mysqldump',
            '/opt/alt/mysql24/bin/mysqldump',
            '/opt/alt/mysql23/bin/mysqldump',
            '/opt/alt/mysql22/bin/mysqldump',
            '/opt/alt/mysql21/bin/mysqldump',
            '/opt/alt/mysql20/bin/mysqldump',
            '/opt/alt/mysql19/bin/mysqldump',
            '/opt/alt/mysql18/bin/mysqldump',
            '/opt/alt/mysql17/bin/mysqldump',
            '/opt/alt/mysql16/bin/mysqldump',
            '/opt/alt/mysql15/bin/mysqldump',
            '/opt/alt/mysql14/bin/mysqldump',
            '/opt/alt/mysql13/bin/mysqldump',
            '/opt/alt/mysql12/bin/mysqldump',
            '/opt/alt/mysql11/bin/mysqldump',
            '/opt/alt/mysql10/bin/mysqldump',
            '/opt/alt/mysql9/bin/mysqldump',
            '/opt/alt/mysql8/bin/mysqldump',
            '/opt/alt/mysql7/bin/mysqldump',
            '/opt/alt/mysql6/bin/mysqldump',
            '/opt/alt/mysql5/bin/mysqldump',
            '/opt/alt/mysql4/bin/mysqldump',
            '/opt/alt/mysql3/bin/mysqldump',
            '/opt/alt/mysql2/bin/mysqldump',
            '/opt/alt/mysql1/bin/mysqldump',
            '/opt/alt/mysql/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
            '/usr/local/bin/mysql/bin/mysqldump',
            '/usr/bin/mysql/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/bin/mysqldump',
            '/opt/mysql/bin/mysqldump',
            // Windows paths (for local development)
            'D:\\XAMPP\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp\\bin\\mysql\\mysql5.7.36\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe'
        );
        
        foreach ($possible_paths as $path) {
            log_message('info', 'Checking path: ' . $path);
            if (is_executable($path) || $this->is_command_available($path)) {
                log_message('info', 'Found mysqldump at: ' . $path);
                log_message('info', '=== MYSQLDUMP SEARCH COMPLETED ===');
                return $path;
            } else {
                log_message('info', 'Path not available: ' . $path);
            }
        }
        
        log_message('info', 'No mysqldump found in any of the paths');
        log_message('info', '=== MYSQLDUMP SEARCH COMPLETED ===');
        return false;
    }
    
    private function is_command_available($command) {
        // Check if exec function is available first
        if (!function_exists('exec')) {
            log_message('info', 'Exec function not available');
            return false;
        }
        
        $output = array();
        $return_var = 0;
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            log_message('info', 'Windows OS detected, using "where" command');
            @exec("where {$command} 2>nul", $output, $return_var);
            log_message('info', 'Windows command result: ' . $return_var . ', output: ' . implode(', ', $output));
        } else {
            // Unix/Linux (cPanel compatible)
            log_message('info', 'Unix/Linux OS detected, trying multiple methods');
            // Try multiple methods to find the command
            $methods = array(
                "which {$command} 2>/dev/null",
                "command -v {$command} 2>/dev/null",
                "type {$command} 2>/dev/null"
            );
            
            foreach ($methods as $method) {
                log_message('info', 'Trying method: ' . $method);
                @exec($method, $output, $return_var);
                log_message('info', 'Method result: ' . $return_var . ', output: ' . implode(', ', $output));
                if ($return_var === 0 && !empty($output)) {
                    log_message('info', 'Command found via method: ' . $method);
                    return true;
                }
            }
            
            // If command not found via PATH, check if it's a direct path
            log_message('info', 'Checking if command is a direct executable path');
            if (file_exists($command) && is_executable($command)) {
                log_message('info', 'Command found as direct executable: ' . $command);
                return true;
            } else {
                log_message('info', 'Command not found as direct executable: ' . $command);
            }
        }
        
        log_message('info', 'Command not available: ' . $command);
        return false;
    }
    
    private function find_mysqldump_alternative() {
        // Alternative method for cPanel - try to find mysqldump in common cPanel locations
        $possible_paths = array(
            '/usr/local/bin/mysqldump',
            '/usr/bin/mysqldump',
            '/opt/alt/mysql80/bin/mysqldump',
            '/opt/alt/mysql57/bin/mysqldump',
            '/opt/alt/mysql56/bin/mysqldump',
            '/opt/alt/mysql55/bin/mysqldump',
            '/opt/alt/mysql51/bin/mysqldump',
            '/opt/alt/mysql50/bin/mysqldump',
            '/opt/alt/mysql41/bin/mysqldump',
            '/opt/alt/mysql40/bin/mysqldump',
            '/opt/alt/mysql32/bin/mysqldump',
            '/opt/alt/mysql31/bin/mysqldump',
            '/opt/alt/mysql30/bin/mysqldump',
            '/opt/alt/mysql29/bin/mysqldump',
            '/opt/alt/mysql28/bin/mysqldump',
            '/opt/alt/mysql27/bin/mysqldump',
            '/opt/alt/mysql26/bin/mysqldump',
            '/opt/alt/mysql25/bin/mysqldump',
            '/opt/alt/mysql24/bin/mysqldump',
            '/opt/alt/mysql23/bin/mysqldump',
            '/opt/alt/mysql22/bin/mysqldump',
            '/opt/alt/mysql21/bin/mysqldump',
            '/opt/alt/mysql20/bin/mysqldump',
            '/opt/alt/mysql19/bin/mysqldump',
            '/opt/alt/mysql18/bin/mysqldump',
            '/opt/alt/mysql17/bin/mysqldump',
            '/opt/alt/mysql16/bin/mysqldump',
            '/opt/alt/mysql15/bin/mysqldump',
            '/opt/alt/mysql14/bin/mysqldump',
            '/opt/alt/mysql13/bin/mysqldump',
            '/opt/alt/mysql12/bin/mysqldump',
            '/opt/alt/mysql11/bin/mysqldump',
            '/opt/alt/mysql10/bin/mysqldump',
            '/opt/alt/mysql9/bin/mysqldump',
            '/opt/alt/mysql8/bin/mysqldump',
            '/opt/alt/mysql7/bin/mysqldump',
            '/opt/alt/mysql6/bin/mysqldump',
            '/opt/alt/mysql5/bin/mysqldump',
            '/opt/alt/mysql4/bin/mysqldump',
            '/opt/alt/mysql3/bin/mysqldump',
            '/opt/alt/mysql2/bin/mysqldump',
            '/opt/alt/mysql1/bin/mysqldump',
            '/opt/alt/mysql/bin/mysqldump'
        );
        
        foreach ($possible_paths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }
        
        // Try to find via shell command (only if exec is available)
        if (function_exists('exec')) {
            $output = array();
            $return_var = 0;
            
            // Try multiple shell commands
            $commands = array(
                'which mysqldump',
                'command -v mysqldump',
                'type mysqldump',
                'find /opt -name mysqldump 2>/dev/null | head -1',
                'find /usr -name mysqldump 2>/dev/null | head -1',
                'find /usr/local -name mysqldump 2>/dev/null | head -1'
            );
            
            foreach ($commands as $cmd) {
                @exec($cmd . ' 2>/dev/null', $output, $return_var);
                if ($return_var === 0 && !empty($output)) {
                    $found_path = trim($output[0]);
                    if (file_exists($found_path) && is_executable($found_path)) {
                        return $found_path;
                    }
                }
                $output = array(); // Reset for next command
            }
        }
        
        return false;
    }
    
    private function test_database_connection($hostname, $username, $password, $database) {
        // Test database connection using mysqli - mengikuti pola syncdb.php
        log_message('info', '=== TESTING DATABASE CONNECTION ===');
        log_message('info', 'Testing connection to: ' . $hostname . ' / ' . $database);
        
        $mysqli = new mysqli($hostname, $username, $password, $database);
        
        if ($mysqli->connect_error) {
            log_message('error', 'Database connection failed: ' . $mysqli->connect_error);
            log_message('error', 'Connection errno: ' . $mysqli->connect_errno);
            
            $error_msg = 'Koneksi gagal: ' . $mysqli->connect_error;
            
            // Provide specific error messages for common issues
            if ($mysqli->connect_errno == 1045) {
                log_message('error', 'Access denied error (1045) detected');
                $error_msg = 'Access denied untuk user database. Periksa username dan password database.';
            } elseif ($mysqli->connect_errno == 1049) {
                log_message('error', 'Unknown database error (1049) detected');
                $error_msg = 'Database tidak ditemukan. Periksa nama database.';
            } elseif ($mysqli->connect_errno == 2002) {
                log_message('error', 'Connection error (2002) detected');
                $error_msg = 'Tidak dapat terhubung ke server database. Periksa hostname database.';
            }
            
            log_message('error', '=== DATABASE CONNECTION TEST FAILED ===');
            throw new Exception($error_msg);
        }
        
        log_message('info', 'Database connection successful');
        
        // Test if user has SELECT privilege
        log_message('info', 'Testing SELECT privilege...');
        $result = $mysqli->query("SHOW TABLES");
        if (!$result) {
            log_message('error', 'SELECT privilege test failed: ' . $mysqli->error);
            $mysqli->close();
            log_message('error', '=== DATABASE CONNECTION TEST FAILED ===');
            throw new Exception('User database tidak memiliki privilege SELECT. Hubungi administrator hosting.');
        }
        
        log_message('info', 'SELECT privilege test passed');
        $mysqli->close();
        log_message('info', '=== DATABASE CONNECTION TEST PASSED ===');
    }
    
    private function create_php_backup($hostname, $username, $password, $database, $backup_path) {
        // Create backup using pure PHP without exec() - Format phpMyAdmin SQL Dump
        // Mengikuti skema dari syncdb.php yang berhasil
        log_message('info', '=== PHP BACKUP STARTED ===');
        log_message('info', 'Hostname: ' . $hostname);
        log_message('info', 'Username: ' . $username);
        log_message('info', 'Database: ' . $database);
        log_message('info', 'Backup path: ' . $backup_path);
        
        $mysqli = new mysqli($hostname, $username, $password, $database);
        
        if ($mysqli->connect_error) {
            log_message('error', 'MySQL connection failed: ' . $mysqli->connect_error);
            log_message('error', 'MySQL connect errno: ' . $mysqli->connect_errno);
            throw new Exception('Gagal terhubung ke database: ' . $mysqli->connect_error);
        }
        
        log_message('info', 'MySQL connection successful');
        
        // Buka file
        log_message('info', 'Opening backup file for writing: ' . $backup_path);
        $handle = fopen($backup_path, 'w');
        if (!$handle) {
            log_message('error', 'Failed to open backup file for writing: ' . $backup_path);
            log_message('error', 'File permissions: ' . substr(sprintf('%o', fileperms(dirname($backup_path))), -4));
            log_message('error', 'Directory writable: ' . (is_writable(dirname($backup_path)) ? 'Yes' : 'No'));
            $mysqli->close();
            throw new Exception('Gagal buka file untuk menulis: ' . $backup_path);
        }
        
        log_message('info', 'Backup file opened successfully');
        
        // -------------------------------
        // HEADER phpMyAdmin
        // -------------------------------
        fwrite($handle, "-- phpMyAdmin SQL Dump\n");
        fwrite($handle, "-- version 5.2.2\n");
        fwrite($handle, "-- https://www.phpmyadmin.net/\n");
        fwrite($handle, "--\n");
        fwrite($handle, "-- Host: $hostname\n");
        fwrite($handle, "-- Database: `$database`\n");
        fwrite($handle, "-- Generation Time: " . date('M d, Y \a\t h:i A') . "\n");
        fwrite($handle, "-- Server version: " . $mysqli->server_info . "\n");
        fwrite($handle, "-- PHP Version: " . PHP_VERSION . "\n");
        fwrite($handle, "\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "START TRANSACTION;\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n");
        fwrite($handle, "\n");
        fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
        fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
        fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
        fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n");
        fwrite($handle, "\n");
        
        // -------------------------------
        // DUMP SEMUA TABEL
        // -------------------------------
        log_message('info', 'Getting list of tables...');
        $tables = $mysqli->query("SHOW TABLES");
        if (!$tables) {
            log_message('error', 'Failed to get tables list: ' . $mysqli->error);
            fclose($handle);
            $mysqli->close();
            throw new Exception('Gagal mendapatkan daftar tabel: ' . $mysqli->error);
        }
        
        log_message('info', 'Tables query executed successfully');
        $table_count = 0;
        while ($table_row = $tables->fetch_row()) {
            $table = $table_row[0];
            $table_count++;
            log_message('info', 'Processing table ' . $table_count . ': ' . $table);
            
            // Struktur Tabel
            fwrite($handle, "\n--\n-- Table structure for table `$table`\n--\n\n");
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            
            $create_result = $mysqli->query("SHOW CREATE TABLE `$table`");
            if ($create_result) {
                $create_row = $create_result->fetch_row();
                fwrite($handle, $create_row[1] . ";\n\n");
                log_message('info', 'Table structure written for: ' . $table);
            } else {
                log_message('warning', 'Failed to get structure for table: ' . $table . ' - Error: ' . $mysqli->error);
                continue;
            }
            
            // Data Tabel
            fwrite($handle, "--\n-- Dumping data for table `$table`\n--\n\n");
            fwrite($handle, "INSERT INTO `$table` (");
            
            // Get column names
            $columns_result = $mysqli->query("SHOW COLUMNS FROM `$table`");
            if ($columns_result) {
                $columns = [];
                while ($col = $columns_result->fetch_row()) {
                    $columns[] = $col[0];
                }
                fwrite($handle, "`" . implode('`, `', $columns) . "`");
            }
            
            fwrite($handle, ") VALUES\n");
            
            // Get table data
            $data_result = $mysqli->query("SELECT * FROM `$table`");
            if ($data_result && $data_result->num_rows > 0) {
                log_message('info', 'Table ' . $table . ' has ' . $data_result->num_rows . ' rows');
                $rows = [];
                $row_count = 0;
                while ($row = $data_result->fetch_row()) {
                    $row_count++;
                    $values = array_map(function($value) use ($mysqli) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . $mysqli->real_escape_string($value) . "'";
                    }, $row);
                    $rows[] = '(' . implode(', ', $values) . ')';
                    
                    // Log progress every 1000 rows
                    if ($row_count % 1000 === 0) {
                        log_message('info', 'Processed ' . $row_count . ' rows for table ' . $table);
                    }
                }
                
                if (count($rows) > 0) {
                    fwrite($handle, implode(",\n", $rows));
                    fwrite($handle, ";\n\n");
                    log_message('info', 'Data written for table ' . $table . ' (' . count($rows) . ' rows)');
                } else {
                    fwrite($handle, ";\n\n");
                    log_message('info', 'No data rows for table ' . $table);
                }
            } else {
                fwrite($handle, ";\n\n");
                log_message('info', 'Table ' . $table . ' is empty or query failed');
            }
        }
        
        // -------------------------------
        // AKHIR DUMP
        // -------------------------------
        fwrite($handle, "COMMIT;\n\n");
        fwrite($handle, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
        fwrite($handle, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
        fwrite($handle, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");
        
        fclose($handle);
            $mysqli->close();
        
        log_message('info', 'PHP backup completed successfully. Tables backed up: ' . $table_count);
        log_message('info', '=== PHP BACKUP COMPLETED ===');
    }
    
    private function cleanup_old_backups($max_days = 7) {
        // Clean up old backup files (older than $max_days days)
        // Mengikuti pola dari syncdb.php yang berhasil
        $backup_dir = FCPATH . 'backups/';
        $now = time();
        $deleted_count = 0;
        
        if (is_dir($backup_dir)) {
            $backup_files = glob($backup_dir . 'backup_*.sql');
            
            foreach ($backup_files as $file) {
                $filename = basename($file);
                
                // Check if filename matches backup pattern - mengikuti pola syncdb.php
                if (preg_match('/^backup_.*_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
                    // Extract date from filename like in syncdb.php
                    if (preg_match('/_(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})-(\d{2})\.sql/', $filename, $matches)) {
                        $file_time = strtotime($matches[1] . ' ' . $matches[2] . ':' . $matches[3] . ':' . $matches[4]);
                        $days_old = ($now - $file_time) / (60 * 60 * 24);
                        
                        if ($days_old > $max_days) {
                            if (unlink($file)) {
                                $deleted_count++;
                                log_message('info', 'Deleted old backup file: ' . $filename);
                            } else {
                                log_message('warning', 'Failed to delete old backup file: ' . $filename);
                            }
                        }
                    }
                }
            }
        }
        
        return $deleted_count;
    }
    
    private function cleanup_old_ftp_backups($ftp_host, $ftp_username, $ftp_password, $ftp_path, $max_days = 7) {
        // Clean up old backup files on FTP server (older than $max_days days)
        // Mengikuti pola dari syncdb.php yang berhasil
        $deleted_count = 0;
        $now = time();
        
        try {
            $ftp_connection = ftp_connect($ftp_host, 21);
            if (!$ftp_connection) {
                log_message('warning', 'Failed to connect to FTP for cleanup: ' . $ftp_host);
                return 0;
            }
            
            if (!ftp_login($ftp_connection, $ftp_username, $ftp_password)) {
                log_message('warning', 'Failed to login to FTP for cleanup');
                ftp_close($ftp_connection);
                return 0;
            }
            
            ftp_pasv($ftp_connection, true);
            
            // Get list of files
            $files = ftp_nlist($ftp_connection, $ftp_path);
            if ($files !== false) {
                foreach ($files as $file) {
                    $filename = basename($file);
                    
                    // Check if filename matches backup pattern - mengikuti pola syncdb.php
                    if (preg_match('/^backup_.*_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
                        // Extract date from filename like in syncdb.php
                        if (preg_match('/_(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})-(\d{2})\.sql/', $file, $matches)) {
                            $file_time = strtotime($matches[1] . ' ' . $matches[2] . ':' . $matches[3] . ':' . $matches[4]);
                            $days_old = ($now - $file_time) / (60 * 60 * 24);
                            
                            if ($days_old > $max_days) {
                                if (ftp_delete($ftp_connection, $file)) {
                                    $deleted_count++;
                                    log_message('info', 'Deleted old FTP backup file: ' . $filename);
                                } else {
                                    log_message('warning', 'Failed to delete old FTP backup file: ' . $filename);
                                }
                            }
                        }
                    }
                }
            }
            
            ftp_close($ftp_connection);
            
        } catch (Exception $e) {
            log_message('error', 'Error during FTP cleanup: ' . $e->getMessage());
        }
        
        return $deleted_count;
    }
    
    private function get_debug_info() {
        $debug = array();
        
        // Check exec function
        $debug['exec_available'] = function_exists('exec');
        
        // Check mysqldump availability
        if (function_exists('exec')) {
            $debug['mysqldump_path'] = $this->find_mysqldump();
            $debug['mysqldump_alternative'] = $this->find_mysqldump_alternative();
        } else {
            $debug['mysqldump_path'] = 'N/A (exec disabled)';
            $debug['mysqldump_alternative'] = 'N/A (exec disabled)';
        }
        
        // Check backup directory
        $backup_dir = FCPATH . 'backups';
        $debug['backup_dir_exists'] = is_dir($backup_dir);
        $debug['backup_dir_writable'] = is_writable($backup_dir);
        $debug['backup_dir_path'] = $backup_dir;
        
        // Check database connection
        try {
            $hostname = $this->db->hostname;
            $username = $this->db->username;
            $password = $this->db->password;
            $database = $this->db->database;
            
            $mysqli = new mysqli($hostname, $username, $password, $database);
            $debug['db_connection'] = !$mysqli->connect_error;
            $debug['db_error'] = $mysqli->connect_error;
            $mysqli->close();
        } catch (Exception $e) {
            $debug['db_connection'] = false;
            $debug['db_error'] = $e->getMessage();
        }
        
        // PHP version and extensions
        $debug['php_version'] = PHP_VERSION;
        $debug['mysqli_available'] = extension_loaded('mysqli');
        $debug['ftp_available'] = extension_loaded('ftp');
        
        return $debug;
    }
}
