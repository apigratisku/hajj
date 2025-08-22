<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_cpanel extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Load cPanel config
        $this->load->config('cpanel_config');
        $this->cpanel_config = $this->config->item('cpanel');
    }

    public function index() {
        try {
            echo "<h1>Test cPanel Connection</h1>";
            echo "<p><strong>Config:</strong></p>";
            echo "<pre>" . print_r($this->cpanel_config, true) . "</pre>";
            
            // Load cPanel library
            $this->load->library('Cpanel', $this->cpanel_config);
            
            echo "<h2>Testing Connection...</h2>";
            
            // Test connection
            $result = $this->cpanel->testConnection();
            echo "<p><strong>Test Connection Result:</strong></p>";
            echo "<pre>" . print_r($result, true) . "</pre>";
            
            echo "<h2>Testing Email List...</h2>";
            
            // Test email list
            $domain = $this->cpanel_config['host'];
            $email_result = $this->cpanel->listEmailAccounts($domain);
            echo "<p><strong>Email List Result:</strong></p>";
            echo "<pre>" . print_r($email_result, true) . "</pre>";
            
            echo "<h2>Session Token:</h2>";
            echo "<p>" . $this->cpanel->getSessionToken() . "</p>";
            
        } catch (Exception $e) {
            echo "<h2>Error:</h2>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
        }
    }
}
