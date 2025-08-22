<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_test extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo "<h1>Email Test Controller</h1>";
        echo "<p>Controller berhasil diakses!</p>";
        echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
        echo "<p>PHP Version: " . PHP_VERSION . "</p>";
        
        // Test basic functionality
        echo "<h2>Basic Tests:</h2>";
        
        // Test cURL
        if (function_exists('curl_init')) {
            echo "<p style='color: green;'>✓ cURL Available</p>";
        } else {
            echo "<p style='color: red;'>✗ cURL Not Available</p>";
        }
        
        // Test config loading
        try {
            $this->load->config('cpanel_config');
            $config = $this->config->item('cpanel');
            echo "<p style='color: green;'>✓ Config loaded successfully</p>";
            echo "<h3>cPanel Config:</h3>";
            echo "<pre>" . print_r($config, true) . "</pre>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Config loading failed: " . $e->getMessage() . "</p>";
        }
        
        // Test library loading
        try {
            $this->load->library('Cpanel', $config);
            echo "<p style='color: green;'>✓ cPanel Library loaded successfully</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Library loading failed: " . $e->getMessage() . "</p>";
        }
        
        // Test session
        try {
            $this->load->library('session');
            echo "<p style='color: green;'>✓ Session library loaded</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Session loading failed: " . $e->getMessage() . "</p>";
        }
    }

    public function config() {
        echo "<h1>Config Test</h1>";
        try {
            $this->load->config('cpanel_config');
            $config = $this->config->item('cpanel');
            echo "<pre>" . print_r($config, true) . "</pre>";
        } catch (Exception $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    }

    public function library() {
        echo "<h1>Library Test</h1>";
        try {
            $this->load->config('cpanel_config');
            $config = $this->config->item('cpanel');
            $this->load->library('Cpanel', $config);
            echo "<p>cPanel Library loaded successfully!</p>";
        } catch (Exception $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    }
}
