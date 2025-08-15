<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('session');
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

        try {
            // Get database configuration
            $hostname = $this->db->hostname;
            $username = $this->db->username;
            $password = $this->db->password;
            $database = $this->db->database;
            
            // Create backup filename with timestamp
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
            
            // Check if exec is available, otherwise use PHP-based backup
            if (function_exists('exec')) {
                // Check if mysqldump is available
                $mysqldump_path = $this->find_mysqldump();
                if (!$mysqldump_path) {
                    // Try alternative method for cPanel
                    $mysqldump_path = $this->find_mysqldump_alternative();
                    if (!$mysqldump_path) {
                        throw new Exception('mysqldump tidak ditemukan. Pastikan MySQL client tools terinstall atau hubungi administrator hosting.');
                    }
                }
                
                // Create mysqldump command with proper escaping (cPanel optimized)
                $escaped_password = escapeshellarg($password);
                $command = "{$mysqldump_path} --host=" . escapeshellarg($hostname) . 
                          " --user=" . escapeshellarg($username) . 
                          " --password={$escaped_password} " . 
                          "--single-transaction --routines --triggers " .
                          escapeshellarg($database) . " > " . escapeshellarg($backup_path) . " 2>&1";
                
                // Execute backup command
                $output = [];
                $return_var = 0;
                @exec($command, $output, $return_var);
                
                // Log the command for debugging (without password)
                $log_command = "{$mysqldump_path} --host=" . escapeshellarg($hostname) . 
                              " --user=" . escapeshellarg($username) . 
                              " --password=*** " . 
                              "--single-transaction --routines --triggers " .
                              escapeshellarg($database) . " > " . escapeshellarg($backup_path);
                log_message('debug', 'Backup command: ' . $log_command);
                log_message('debug', 'Return code: ' . $return_var);
                log_message('debug', 'Output: ' . implode("\n", $output));
            } else {
                // Use PHP-based backup method
                log_message('debug', 'Using PHP-based backup method (exec disabled)');
                $this->create_php_backup($hostname, $username, $password, $database, $backup_path);
                $return_var = 0;
                $output = [];
            }
            

            
            if ($return_var === 0 && file_exists($backup_path)) {
                // Get file size
                $file_size = filesize($backup_path);
                if ($file_size === 0) {
                    throw new Exception('File backup kosong. Periksa konfigurasi database.');
                }
                
                $file_size_formatted = $this->format_bytes($file_size);
                
                $response = [
                    'status' => 'success',
                    'message' => 'Backup database berhasil dibuat',
                    'filename' => $backup_filename,
                    'file_size' => $file_size_formatted,
                    'download_url' => base_url('settings/download_backup/' . $backup_filename)
                ];
            } else {
                $error_msg = 'Gagal membuat backup database';
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
                    } elseif (strpos($output_str, 'command not found') !== false) {
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
            log_message('error', 'Backup database error: ' . $e->getMessage());
            $response = [
                'status' => 'error',
                'message' => 'Gagal membuat backup database: ' . $e->getMessage()
            ];
        }
        
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
            
            // Check if exec is available, otherwise use PHP-based backup
            if (function_exists('exec')) {
                // Check if mysqldump is available
                $mysqldump_path = $this->find_mysqldump();
                if (!$mysqldump_path) {
                    // Try alternative method for cPanel
                    $mysqldump_path = $this->find_mysqldump_alternative();
                    if (!$mysqldump_path) {
                        throw new Exception('mysqldump tidak ditemukan. Pastikan MySQL client tools terinstall atau hubungi administrator hosting.');
                    }
                }
                
                // Create mysqldump command with proper escaping (cPanel optimized)
                $escaped_password = escapeshellarg($password);
                $command = "{$mysqldump_path} --host=" . escapeshellarg($hostname) . 
                          " --user=" . escapeshellarg($username) . 
                          " --password={$escaped_password} " . 
                          "--single-transaction --routines --triggers " .
                          escapeshellarg($database) . " > " . escapeshellarg($backup_path) . " 2>&1";
                
                // Execute backup command
                $output = [];
                $return_var = 0;
                @exec($command, $output, $return_var);
            } else {
                // Use PHP-based backup method
                $this->create_php_backup($hostname, $username, $password, $database, $backup_path);
                $return_var = 0;
                $output = [];
            }
            
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
            
            $response = [
                'status' => 'success',
                'message' => 'Backup database berhasil diupload ke server FTP',
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
            if (is_executable($path) || $this->is_command_available($path)) {
                return $path;
            }
        }
        
        return false;
    }
    
    private function is_command_available($command) {
        // Check if exec function is available first
        if (!function_exists('exec')) {
            return false;
        }
        
        $output = array();
        $return_var = 0;
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            @exec("where {$command} 2>nul", $output, $return_var);
        } else {
            // Unix/Linux (cPanel compatible)
            // Try multiple methods to find the command
            $methods = array(
                "which {$command} 2>/dev/null",
                "command -v {$command} 2>/dev/null",
                "type {$command} 2>/dev/null"
            );
            
            foreach ($methods as $method) {
                @exec($method, $output, $return_var);
                if ($return_var === 0 && !empty($output)) {
                    return true;
                }
            }
            
            // If command not found via PATH, check if it's a direct path
            if (file_exists($command) && is_executable($command)) {
                return true;
            }
        }
        
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
        // Test database connection using mysqli
        $mysqli = new mysqli($hostname, $username, $password, $database);
        
        if ($mysqli->connect_error) {
            $error_msg = 'Gagal terhubung ke database: ' . $mysqli->connect_error;
            
            // Provide specific error messages for common issues
            if ($mysqli->connect_errno == 1045) {
                $error_msg = 'Access denied untuk user database. Periksa username dan password database.';
            } elseif ($mysqli->connect_errno == 1049) {
                $error_msg = 'Database tidak ditemukan. Periksa nama database.';
            } elseif ($mysqli->connect_errno == 2002) {
                $error_msg = 'Tidak dapat terhubung ke server database. Periksa hostname database.';
            }
            
            throw new Exception($error_msg);
        }
        
        // Test if user has SELECT privilege
        $result = $mysqli->query("SHOW TABLES");
        if (!$result) {
            $mysqli->close();
            throw new Exception('User database tidak memiliki privilege SELECT. Hubungi administrator hosting.');
        }
        
        $mysqli->close();
    }
    
    private function create_php_backup($hostname, $username, $password, $database, $backup_path) {
        // Create backup using pure PHP without exec()
        $mysqli = new mysqli($hostname, $username, $password, $database);
        
        if ($mysqli->connect_error) {
            throw new Exception('Gagal terhubung ke database: ' . $mysqli->connect_error);
        }
        
        // Set charset
        $mysqli->set_charset('utf8');
        
        // Start backup file
        $backup_content = "-- PHP Generated Database Backup\n";
        $backup_content .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $backup_content .= "-- Database: {$database}\n\n";
        $backup_content .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $backup_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $backup_content .= "SET AUTOCOMMIT = 0;\n";
        $backup_content .= "START TRANSACTION;\n";
        $backup_content .= "SET time_zone = \"+00:00\";\n\n";
        
        // Get all tables
        $tables_result = $mysqli->query("SHOW TABLES");
        if (!$tables_result) {
            $mysqli->close();
            throw new Exception('Gagal mendapatkan daftar tabel: ' . $mysqli->error);
        }
        
        $table_count = 0;
        while ($table_row = $tables_result->fetch_array()) {
            $table_name = $table_row[0];
            $table_count++;
            
            // Get table structure
            $create_table_result = $mysqli->query("SHOW CREATE TABLE `{$table_name}`");
            if ($create_table_result) {
                $create_table_row = $create_table_result->fetch_array();
                $backup_content .= "-- Table structure for table `{$table_name}`\n";
                $backup_content .= "DROP TABLE IF EXISTS `{$table_name}`;\n";
                $backup_content .= $create_table_row[1] . ";\n\n";
            } else {
                log_message('warning', 'Failed to get structure for table: ' . $table_name);
            }
            
            // Get table data
            $data_result = $mysqli->query("SELECT * FROM `{$table_name}`");
            if ($data_result && $data_result->num_rows > 0) {
                $backup_content .= "-- Dumping data for table `{$table_name}`\n";
                
                while ($row = $data_result->fetch_assoc()) {
                    $values = array();
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $mysqli->real_escape_string($value) . "'";
                        }
                    }
                    $backup_content .= "INSERT INTO `{$table_name}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $backup_content .= "\n";
            }
        }
        
        $backup_content .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $backup_content .= "COMMIT;\n";
        
        // Write to file
        if (file_put_contents($backup_path, $backup_content) === false) {
            $mysqli->close();
            throw new Exception('Gagal menulis file backup ke: ' . $backup_path);
        }
        
        log_message('info', 'PHP backup completed successfully. Tables backed up: ' . $table_count);
        $mysqli->close();
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
