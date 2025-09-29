<?php
// test_jam_format_fix.php
// Script untuk test apakah field jam sudah dalam format AM/PM

header('Content-Type: application/json');

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
    echo json_encode(['message' => 'Testing jam format fix...'], JSON_PRETTY_PRINT);
    
    // Test API endpoint
    $api_controller = new Api();
    $api_controller->__construct();
    
    // Test dengan data yang ada
    $_GET['tanggal'] = '2025-09-29';
    $_GET['jam'] = '21:40';
    $_GET['hours_ahead'] = 0;
    
    // Capture output
    ob_start();
    $api_controller->schedule_notifications();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    
    echo json_encode([
        'test_result' => 'API Response',
        'response' => $response
    ], JSON_PRETTY_PRINT);
    
    // Verifikasi format jam
    if ($response && isset($response['data']) && !empty($response['data'])) {
        $schedule = $response['data'][0];
        
        $jam_field = $schedule['jam'] ?? 'N/A';
        $jam_formatted = $schedule['jam_formatted'] ?? 'N/A';
        $jam_adjusted = $schedule['jam_adjusted'] ?? 'N/A';
        
        echo json_encode([
            'verification' => [
                'jam_field' => $jam_field,
                'jam_formatted' => $jam_formatted,
                'jam_adjusted' => $jam_adjusted,
                'jam_field_is_ampm' => preg_match('/\d{1,2}:\d{2}\s*(AM|PM)/i', $jam_field),
                'jam_formatted_is_ampm' => preg_match('/\d{1,2}:\d{2}\s*(AM|PM)/i', $jam_formatted),
                'fields_match' => $jam_field === $jam_formatted
            ]
        ], JSON_PRETTY_PRINT);
        
        if (preg_match('/\d{1,2}:\d{2}\s*(AM|PM)/i', $jam_field)) {
            echo json_encode(['status' => '✅ SUCCESS: jam field is now in AM/PM format'], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['status' => '❌ FAILED: jam field is still in 24-hour format'], JSON_PRETTY_PRINT);
        }
    } else {
        echo json_encode(['status' => '⚠️ No data found in API response'], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
