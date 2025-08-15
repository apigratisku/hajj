<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_mysqldump extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        
        // Check if user is logged in and is admin
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') != 'admin') {
            show_404();
            return;
        }
    }

    public function index() {
        echo "<h2>Test mysqldump Configuration</h2>";

        // Function to find mysqldump
        $mysqldump_path = $this->find_mysqldump();
        
        // Test 1: Check if mysqldump is available
        echo "<h3>1. Checking mysqldump availability</h3>";
        if ($mysqldump_path) {
            echo "<p style='color: green;'>✓ mysqldump found at: <strong>{$mysqldump_path}</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ mysqldump not found!</p>";
            echo "<p>Please install MySQL client tools or add mysqldump to your PATH.</p>";
        }

        // Test 2: Check PHP exec function
        echo "<h3>2. Checking PHP exec function</h3>";
        if (function_exists('exec')) {
            echo "<p style='color: green;'>✓ exec() function is available</p>";
        } else {
            echo "<p style='color: red;'>✗ exec() function is disabled</p>";
        }

        // Test 3: Check if we can execute mysqldump
        echo "<h3>3. Testing mysqldump execution</h3>";
        if ($mysqldump_path) {
            $output = array();
            $return_var = 0;
            exec("{$mysqldump_path} --version 2>&1", $output, $return_var);
            
            if ($return_var === 0) {
                echo "<p style='color: green;'>✓ mysqldump is executable</p>";
                echo "<p><strong>Version:</strong> " . implode(' ', $output) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ mysqldump execution failed</p>";
                echo "<p><strong>Error:</strong> " . implode(' ', $output) . "</p>";
            }
        }

        // Test 4: Check database configuration
        echo "<h3>4. Checking database configuration</h3>";
        try {
            // Get database configuration from CodeIgniter
            $hostname = $this->db->hostname;
            $username = $this->db->username;
            $password = $this->db->password;
            $database = $this->db->database;
            
            echo "<p><strong>Database config:</strong></p>";
            echo "<ul>";
            echo "<li>Host: {$hostname}</li>";
            echo "<li>Username: {$username}</li>";
            echo "<li>Database: {$database}</li>";
            echo "</ul>";
            
            // Test database connection
            $mysqli = new mysqli($hostname, $username, $password, $database);
            
            if ($mysqli->connect_error) {
                echo "<p style='color: red;'>✗ Database connection failed: " . $mysqli->connect_error . "</p>";
            } else {
                echo "<p style='color: green;'>✓ Database connection successful</p>";
                $mysqli->close();
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
        }

        // Test 5: Check backup directory
        echo "<h3>5. Checking backup directory</h3>";
        $backup_dir = FCPATH . 'backups';
        if (is_dir($backup_dir)) {
            echo "<p style='color: green;'>✓ Backup directory exists: {$backup_dir}</p>";
            if (is_writable($backup_dir)) {
                echo "<p style='color: green;'>✓ Backup directory is writable</p>";
            } else {
                echo "<p style='color: red;'>✗ Backup directory is not writable</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Backup directory does not exist, will be created automatically</p>";
        }

        // Test 6: Simulate backup command
        echo "<h3>6. Simulating backup command</h3>";
        if ($mysqldump_path && function_exists('exec')) {
            $test_file = $backup_dir . '/test_backup.sql';
            $escaped_password = escapeshellarg($password);
            $command = "{$mysqldump_path} --host=" . escapeshellarg($hostname) . 
                      " --user=" . escapeshellarg($username) . 
                      " --password={$escaped_password} " . 
                      escapeshellarg($database) . " > " . escapeshellarg($test_file) . " 2>&1";
            
            echo "<p><strong>Command:</strong> " . str_replace($password, '***', $command) . "</p>";
            
            $output = array();
            $return_var = 0;
            exec($command, $output, $return_var);
            
            if ($return_var === 0 && file_exists($test_file)) {
                $file_size = filesize($test_file);
                echo "<p style='color: green;'>✓ Test backup successful</p>";
                echo "<p><strong>File size:</strong> " . number_format($file_size) . " bytes</p>";
                
                // Clean up test file
                unlink($test_file);
                echo "<p>Test file cleaned up</p>";
            } else {
                echo "<p style='color: red;'>✗ Test backup failed</p>";
                echo "<p><strong>Return code:</strong> {$return_var}</p>";
                echo "<p><strong>Output:</strong> " . implode('<br>', $output) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Cannot test backup - mysqldump or exec not available</p>";
        }

        echo "<hr>";
        echo "<p><strong>Note:</strong> If you see any errors above, please fix them before using the backup feature.</p>";
        echo "<p><strong>Common solutions:</strong></p>";
        echo "<ul>";
        echo "<li>Install MySQL client tools</li>";
        echo "<li>Add mysqldump to your system PATH</li>";
        echo "<li>Enable exec() function in php.ini</li>";
        echo "<li>Check database credentials</li>";
        echo "<li>Ensure backup directory is writable</li>";
        echo "</ul>";
    }
    
    private function find_mysqldump() {
        // Common paths for mysqldump (cPanel optimized)
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
            // Windows paths (for local development)
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
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
        $output = array();
        $return_var = 0;
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            exec("where {$command} 2>nul", $output, $return_var);
        } else {
            // Unix/Linux
            exec("which {$command} 2>/dev/null", $output, $return_var);
        }
        
        return $return_var === 0 && !empty($output);
    }
}
