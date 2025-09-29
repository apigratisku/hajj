<?php
// check_database_data.php
// Script untuk mengecek data yang ada di database

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
    echo json_encode(['message' => 'Checking database data...'], JSON_PRETTY_PRINT);
    
    // 1. Cek total data di tabel peserta
    $total_peserta = $CI->db->count_all('peserta');
    echo json_encode(['total_peserta' => $total_peserta], JSON_PRETTY_PRINT);
    
    // 2. Cek data dengan tanggal 2025-09-14
    $CI->db->where('tanggal', '2025-09-14');
    $count_2025_09_14 = $CI->db->count_all_results('peserta');
    echo json_encode(['count_2025_09_14' => $count_2025_09_14], JSON_PRETTY_PRINT);
    
    // 3. Cek data dengan jam 02:40:00
    $CI->db->where('jam', '02:40:00');
    $count_02_40_00 = $CI->db->count_all_results('peserta');
    echo json_encode(['count_02_40_00' => $count_02_40_00], JSON_PRETTY_PRINT);
    
    // 4. Cek data dengan tanggal 2025-09-14 dan jam 02:40:00
    $CI->db->where('tanggal', '2025-09-14');
    $CI->db->where('jam', '02:40:00');
    $count_exact = $CI->db->count_all_results('peserta');
    echo json_encode(['count_exact_match' => $count_exact], JSON_PRETTY_PRINT);
    
    // 5. Ambil sample data dari tabel peserta
    $CI->db->select('tanggal, jam, barcode, COUNT(*) as count');
    $CI->db->from('peserta');
    $CI->db->where('tanggal IS NOT NULL');
    $CI->db->where('jam IS NOT NULL');
    $CI->db->where('tanggal !=', '');
    $CI->db->where('jam !=', '');
    $CI->db->group_by('tanggal, jam');
    $CI->db->order_by('tanggal DESC, jam DESC');
    $CI->db->limit(10);
    $sample_data = $CI->db->get()->result();
    
    echo json_encode(['sample_data' => $sample_data], JSON_PRETTY_PRINT);
    
    // 6. Cek data dengan barcode kosong
    $CI->db->select('
        tanggal,
        jam,
        COUNT(*) as total_count,
        SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode_count,
        SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode_count
    ');
    $CI->db->from('peserta');
    $CI->db->where('tanggal', '2025-09-14');
    $CI->db->where('jam', '02:40:00');
    $CI->db->group_by('tanggal, jam');
    $barcode_data = $CI->db->get()->result();
    
    echo json_encode(['barcode_data' => $barcode_data], JSON_PRETTY_PRINT);
    
    echo json_encode(['message' => 'Database check complete'], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
