<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parsing_test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 180);
    }

    public function simple_test()
    {
        $test_data = array(
            'success' => true,
            'message' => 'Simple test endpoint berfungsi',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_info' => array(
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            )
        );

        $this->send_json_response(200, $test_data);
    }

    public function debug()
    {
        $test_data = array(
            'success' => true,
            'message' => 'Debug endpoint berfungsi',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_info' => array(
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            )
        );

        $this->send_json_response(200, $test_data);
    }

    public function test()
    {
        $test_data = array(
            'success' => true,
            'message' => 'Test endpoint berfungsi',
            'timestamp' => date('Y-m-d H:i:s'),
            'user' => $this->session->userdata('username'),
            'logged_in' => $this->session->userdata('logged_in')
        );

        $this->send_json_response(200, $test_data);
    }

    public function parse()
    {
        try {
            $test_data = array(
                'success' => true,
                'message' => 'Parse endpoint berfungsi',
                'timestamp' => date('Y-m-d H:i:s'),
                'file_info' => array(
                    'files_received' => isset($_FILES) ? count($_FILES) : 0,
                    'pdf_uploaded' => isset($_FILES['pdf']) ? true : false
                )
            );

            if (isset($_FILES['pdf'])) {
                $test_data['file_details'] = array(
                    'name' => $_FILES['pdf']['name'],
                    'size' => $_FILES['pdf']['size'],
                    'type' => $_FILES['pdf']['type'],
                    'error' => $_FILES['pdf']['error']
                );
            }

            $this->send_json_response(200, $test_data);

        } catch (Exception $e) {
            $error_data = array(
                'success' => false,
                'error' => 'Exception occurred: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            );

            $this->send_json_response(500, $error_data);
        }
    }

    private function send_json_response($code, $data)
    {
        // Clean any output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        // Set HTTP response code
        http_response_code($code);
        
        // Send JSON response
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
