<?php
// test_api_direct.php
// Script untuk test API endpoint secara langsung

header('Content-Type: application/json');

// Test parameters
$tanggal = '2025-09-14';
$jam = '02:40:00';
$hours_ahead = 0;

echo json_encode([
    'message' => 'Testing API endpoint directly...',
    'test_params' => [
        'tanggal' => $tanggal,
        'jam' => $jam,
        'hours_ahead' => $hours_ahead
    ]
], JSON_PRETTY_PRINT);

// Load CodeIgniter environment
define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/application/');
require_once BASEPATH . 'core/CodeIgniter.php';

// Manually load database
$CI =& get_instance();
$CI->load->database();
$CI->load->model('transaksi_model');
$CI->load->helper('url');

date_default_timezone_set('Asia/Hong_Kong');

try {
    // Manually instantiate the Api controller
    $api_controller = new Api();
    $api_controller->__construct();
    
    // Set GET parameters
    $_GET['tanggal'] = $tanggal;
    $_GET['jam'] = $jam;
    $_GET['hours_ahead'] = $hours_ahead;
    
    // Capture output
    ob_start();
    $api_controller->schedule_notifications();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    echo json_encode([
        'api_response' => $response,
        'raw_output' => $output,
        'test_completed' => true
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
