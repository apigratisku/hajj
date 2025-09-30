<?php
// test_remove_5_hours.php
// Script untuk test apakah penambahan 5 jam sudah dihilangkan

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
    echo json_encode(['message' => 'Testing removal of +5 hours...'], JSON_PRETTY_PRINT);
    
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
    
    // Verifikasi bahwa tidak ada penambahan 5 jam
    if ($response && isset($response['data']) && !empty($response['data'])) {
        $schedule = $response['data'][0];
        
        $jam_field = $schedule['jam'] ?? 'N/A';
        $jam_formatted = $schedule['jam_formatted'] ?? 'N/A';
        
        // Cek apakah ada field jam_adjusted (seharusnya sudah tidak ada)
        $has_jam_adjusted = isset($schedule['jam_adjusted']);
        
        echo json_encode([
            'verification' => [
                'jam_field' => $jam_field,
                'jam_formatted' => $jam_formatted,
                'has_jam_adjusted_field' => $has_jam_adjusted,
                'jam_field_is_ampm' => preg_match('/\d{1,2}:\d{2}\s*(AM|PM)/i', $jam_field),
                'fields_match' => $jam_field === $jam_formatted,
                'expected_time' => '09:40 PM', // 21:40 dalam format AM/PM
                'actual_matches_expected' => $jam_field === '09:40 PM'
            ]
        ], JSON_PRETTY_PRINT);
        
        // Test logika: 21:40 seharusnya menjadi 09:40 PM (tanpa penambahan 5 jam)
        if ($jam_field === '09:40 PM' && !$has_jam_adjusted) {
            echo json_encode(['status' => '✅ SUCCESS: +5 hours removed, time format correct'], JSON_PRETTY_PRINT);
        } else if ($jam_field === '02:40 AM') {
            echo json_encode(['status' => '❌ FAILED: Still adding +5 hours (21:40 + 5 = 02:40 AM)'], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['status' => '⚠️ UNEXPECTED: Time format is different than expected'], JSON_PRETTY_PRINT);
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
