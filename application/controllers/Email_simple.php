<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_simple extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        echo "<h1>Email Management (Simple)</h1>";
        echo "<p>Controller Email_simple berhasil diakses!</p>";
        echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
        echo "<p>User: " . $this->session->userdata('nama_lengkap') . "</p>";
        
        // Test load config
        try {
            $this->load->config('cpanel_config');
            $config = $this->config->item('cpanel');
            echo "<h2>cPanel Config:</h2>";
            echo "<pre>" . print_r($config, true) . "</pre>";
        } catch (Exception $e) {
            echo "<h2>Error loading config:</h2>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
        
        // Test load library
        try {
            $this->load->library('Cpanel', $config);
            echo "<h2>cPanel Library loaded successfully!</h2>";
            echo "<p>Session Token: " . $this->cpanel->getSessionToken() . "</p>";
        } catch (Exception $e) {
            echo "<h2>Error loading cPanel library:</h2>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
    }

    public function test() {
        echo "<h1>Email Test Function</h1>";
        echo "<p>Test function berhasil diakses!</p>";
    }
}
