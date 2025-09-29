<?php
// add_test_data.php
// Script untuk menambahkan data test jika tidak ada

header('Content-Type: application/json');

// Load CodeIgniter environment
define('ENVIRONMENT', 'development');
define('BASEPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/application/');
require_once BASEPATH . 'core/CodeIgniter.php';

// Manually load database
$CI =& get_instance();
$CI->load->database();

date_default_timezone_set('Asia/Hong_Kong');

try {
    echo json_encode(['message' => 'Adding test data...'], JSON_PRETTY_PRINT);
    
    // Cek apakah data sudah ada
    $CI->db->where('tanggal', '2025-09-14');
    $CI->db->where('jam', '02:40:00');
    $existing_count = $CI->db->count_all_results('peserta');
    
    if ($existing_count > 0) {
        echo json_encode(['message' => "Data already exists: $existing_count records"], JSON_PRETTY_PRINT);
    } else {
        // Tambahkan data test
        $test_data = [
            [
                'nama' => 'Test Peserta 1',
                'nomor_paspor' => 'TEST001',
                'tanggal' => '2025-09-14',
                'jam' => '02:40:00',
                'barcode' => '', // Kosong untuk test
                'gender' => 'L',
                'status' => '0'
            ],
            [
                'nama' => 'Test Peserta 2',
                'nomor_paspor' => 'TEST002',
                'tanggal' => '2025-09-14',
                'jam' => '02:40:00',
                'barcode' => 'BARCODE123', // Ada barcode
                'gender' => 'P',
                'status' => '1'
            ],
            [
                'nama' => 'Test Peserta 3',
                'nomor_paspor' => 'TEST003',
                'tanggal' => '2025-09-14',
                'jam' => '02:40:00',
                'barcode' => '', // Kosong untuk test
                'gender' => 'L',
                'status' => '0'
            ]
        ];
        
        $inserted_count = 0;
        foreach ($test_data as $data) {
            if ($CI->db->insert('peserta', $data)) {
                $inserted_count++;
            }
        }
        
        echo json_encode([
            'message' => "Test data added successfully",
            'inserted_count' => $inserted_count,
            'test_data' => $test_data
        ], JSON_PRETTY_PRINT);
    }
    
    // Verifikasi data
    $CI->db->where('tanggal', '2025-09-14');
    $CI->db->where('jam', '02:40:00');
    $final_count = $CI->db->count_all_results('peserta');
    
    echo json_encode(['final_count' => $final_count], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
