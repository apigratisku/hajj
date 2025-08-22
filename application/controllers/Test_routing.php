<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_routing extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo "<h1>Test Routing Berhasil!</h1>";
        echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
        echo "<p>Server: " . $_SERVER['SERVER_NAME'] . "</p>";
        echo "<p>URI: " . $_SERVER['REQUEST_URI'] . "</p>";
        
        // Test load library
        try {
            $this->load->config('cpanel_config');
            $config = $this->config->item('cpanel');
            echo "<h2>cPanel Config:</h2>";
            echo "<pre>" . print_r($config, true) . "</pre>";
        } catch (Exception $e) {
            echo "<h2>Error loading config:</h2>";
            echo "<p>" . $e->getMessage() . "</p>";
        }
    }
}
