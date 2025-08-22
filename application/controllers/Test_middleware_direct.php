<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_middleware_direct extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        // Load the test view
        $this->load->view('test_middleware_direct');
    }
}
