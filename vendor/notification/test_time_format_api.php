<?php
// test_time_format_api.php
// Script untuk test format waktu AM/PM dan penambahan 5 jam pada API

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

header('Content-Type: application/json');
date_default_timezone_set('Asia/Hong_Kong');

echo json_encode(['message' => 'Testing Time Format API...'], JSON_PRETTY_PRINT);

try {
    // Test data
    $test_date = '2025-09-14';
    $test_time = '02:40:00';
    
    echo json_encode(['test_scenario' => "Testing time format for $test_date $test_time"], JSON_PRETTY_PRINT);
    
    // Manually instantiate the Api controller
    $api_controller = new Api();
    $api_controller->__construct();
    
    // Test schedule_notifications endpoint
    echo json_encode(['testing' => 'schedule_notifications endpoint'], JSON_PRETTY_PRINT);
    
    // Simulate the API call
    $_GET['tanggal'] = $test_date;
    $_GET['jam'] = $test_time;
    $_GET['hours_ahead'] = 0;
    
    // Capture output
    ob_start();
    $api_controller->schedule_notifications();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    if ($response && isset($response['data']) && !empty($response['data'])) {
        $schedule = $response['data'][0];
        echo json_encode([
            'original_time' => $schedule['jam'] ?? 'N/A',
            'adjusted_time' => $schedule['jam_adjusted'] ?? 'N/A',
            'formatted_time' => $schedule['jam_formatted'] ?? 'N/A',
            'time_adjustment' => '+5 hours',
            'format_used' => 'h:i A (12-hour format with AM/PM)'
        ], JSON_PRETTY_PRINT);
        
        // Test time calculation
        $original_time = $schedule['jam'] ?? $test_time;
        $calculated_adjusted = date('H:i:s', strtotime($original_time . ' +5 hours'));
        $calculated_formatted = date('h:i A', strtotime($calculated_adjusted));
        
        echo json_encode([
            'verification' => [
                'original' => $original_time,
                'calculated_adjusted' => $calculated_adjusted,
                'calculated_formatted' => $calculated_formatted,
                'matches_api_adjusted' => ($calculated_adjusted === ($schedule['jam_adjusted'] ?? '')),
                'matches_api_formatted' => ($calculated_formatted === ($schedule['jam_formatted'] ?? ''))
            ]
        ], JSON_PRETTY_PRINT);
        
    } else {
        echo json_encode([
            'status' => 'No data found',
            'message' => 'No schedules found for the test date/time',
            'response' => $response
        ], JSON_PRETTY_PRINT);
    }
    
    // Test check_barcode_status endpoint
    echo json_encode(['testing' => 'check_barcode_status endpoint'], JSON_PRETTY_PRINT);
    
    ob_start();
    $api_controller->check_barcode_status();
    $output2 = ob_get_clean();
    
    $response2 = json_decode($output2, true);
    
    if ($response2 && isset($response2['schedule'])) {
        $schedule2 = $response2['schedule'];
        echo json_encode([
            'barcode_status_test' => [
                'original_time' => $schedule2['jam'] ?? 'N/A',
                'adjusted_time' => $schedule2['jam_adjusted'] ?? 'N/A',
                'formatted_time' => $schedule2['jam_formatted'] ?? 'N/A'
            ]
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'barcode_status_test' => 'No schedule found or error occurred',
            'response' => $response2
        ], JSON_PRETTY_PRINT);
    }
    
    echo json_encode(['message' => 'Time format API test complete'], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Test Error: ' . $e->getMessage(),
        'error_trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
