<?php
/**
 * API Endpoints untuk Telegram Notification Scheduler
 * Tambahkan endpoint ini ke aplikasi hajj Anda
 */

// Tambahkan route ini di application/config/routes.php
// $route['api/schedule_notifications'] = 'api/schedule_notifications';
// $route['api/overdue_schedules'] = 'api/overdue_schedules';

class Api extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('transaksi_model');
        $this->load->helper('url');
        
        // Set timezone ke GMT +8 (Asia/Hong_Kong)
        date_default_timezone_set('Asia/Hong_Kong');
        
        // Set header untuk API response
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Handle preflight request
        if ($this->input->method() === 'options') {
            exit();
        }
    }
    
    /**
     * API untuk mendapatkan data jadwal notifikasi
     * GET /api/schedule_notifications?tanggal=2025-01-20&jam=10:00:00&hours_ahead=2
     */
    public function schedule_notifications() {
        try {
            $tanggal = $this->input->get('tanggal');
            $jam = $this->input->get('jam');
            $hours_ahead = $this->input->get('hours_ahead', 2);
            
            if (empty($tanggal) || empty($jam)) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Parameter tanggal dan jam diperlukan'
                    ]));
                return;
            }
            
            // Hitung waktu target berdasarkan hours_ahead
            $target_datetime = date('Y-m-d H:i:s', strtotime("$tanggal $jam - $hours_ahead hours"));
            $target_date = date('Y-m-d', strtotime($target_datetime));
            $target_time = date('H:i:s', strtotime($target_datetime));
            
            // Ambil data jadwal dari database dengan pencarian fleksibel
            $schedules = $this->get_schedule_data_flexible($target_date, $target_time, $hours_ahead);
            
            $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'success' => true,
                    'data' => $schedules,
                    'target_datetime' => $target_datetime,
                    'hours_ahead' => $hours_ahead,
                    'timezone' => 'Asia/Hong_Kong (GMT+8)',
                    'current_time' => date('Y-m-d H:i:s'),
                    'current_timestamp' => time()
                ]));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]));
        }
    }
    
    /**
     * API untuk mendapatkan jadwal yang sudah terlewat
     * GET /api/overdue_schedules
     */
    public function overdue_schedules() {
        try {
            $overdue_schedules = $this->get_overdue_schedule_data();
            
            $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'success' => true,
                    'data' => $overdue_schedules,
                    'total' => count($overdue_schedules),
                    'timezone' => 'Asia/Hong_Kong (GMT+8)',
                    'current_time' => date('Y-m-d H:i:s'),
                    'current_timestamp' => time()
                ]));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]));
        }
    }
    
    /**
     * Mendapatkan data jadwal berdasarkan tanggal dan waktu
     */
    private function get_schedule_data($tanggal, $jam, $hours_ahead) {
        // Debug: Log query parameters
        log_message('debug', "API get_schedule_data - Tanggal: $tanggal, Jam: $jam, Hours ahead: $hours_ahead");
        
        // Query untuk mendapatkan data jadwal - lebih fleksibel
        $this->db->select('
            tanggal,
            jam,
            COUNT(*) as total_count,
            SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode_count,
            SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode_count,
            SUM(CASE WHEN gender = "L" THEN 1 ELSE 0 END) as male_count,
            SUM(CASE WHEN gender = "P" THEN 1 ELSE 0 END) as female_count,
            GROUP_CONCAT(DISTINCT status) as status_list
        ');
        
        $this->db->from('peserta');
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam', $jam);
        // Hapus filter status yang terlalu ketat - ambil semua data untuk notifikasi
        $this->db->group_by('tanggal, jam');
        
        $query = $this->db->get();
        $results = $query->result();
        
        // Debug: Log query dan hasil
        log_message('debug', "API Query: " . $this->db->last_query());
        log_message('debug', "API Results count: " . count($results));
        
        // Format data untuk response
        $schedules = [];
        foreach ($results as $row) {
            // Tambah 5 jam pada jam yang diambil dari data
            $adjusted_time = date('H:i:s', strtotime($row->jam . ' +5 hours'));
            $formatted_time = date('h:i A', strtotime($adjusted_time));
            
                $schedules[] = [
                    'tanggal' => $row->tanggal,
                    'jam' => $formatted_time, // Gunakan format AM/PM untuk field jam
                    'jam_formatted' => $formatted_time,
                    'jam_adjusted' => $adjusted_time,
                    'total_count' => (int)$row->total_count,
                    'no_barcode_count' => (int)$row->no_barcode_count,
                    'with_barcode_count' => (int)$row->with_barcode_count,
                    'male_count' => (int)$row->male_count,
                    'female_count' => (int)$row->female_count,
                    'hours_ahead' => $hours_ahead,
                    'status_list' => $row->status_list // Debug info
                ];
        }
        
        return $schedules;
    }
    
    /**
     * Mendapatkan data jadwal dengan pencarian yang lebih fleksibel
     */
    private function get_schedule_data_flexible($tanggal, $jam, $hours_ahead) {
        // Debug: Log query parameters
        log_message('debug', "API get_schedule_data_flexible - Tanggal: $tanggal, Jam: $jam, Hours ahead: $hours_ahead");
        
        $schedules = [];
        
        // 1. Coba exact match dulu
        $exact_results = $this->get_exact_schedule_data($tanggal, $jam, $hours_ahead);
        if (!empty($exact_results)) {
            $schedules = array_merge($schedules, $exact_results);
        }
        
        // 2. Jika tidak ada, coba dengan format jam yang berbeda
        if (empty($schedules)) {
            $jam_variants = $this->get_jam_variants($jam);
            foreach ($jam_variants as $jam_variant) {
                $variant_results = $this->get_exact_schedule_data($tanggal, $jam_variant, $hours_ahead);
                if (!empty($variant_results)) {
                    $schedules = array_merge($schedules, $variant_results);
                    break; // Ambil yang pertama ditemukan
                }
            }
        }
        
        // 3. Jika masih tidak ada, cari dengan tanggal saja
        if (empty($schedules)) {
            $date_results = $this->get_schedule_by_date_only($tanggal, $hours_ahead);
            if (!empty($date_results)) {
                $schedules = array_merge($schedules, $date_results);
            }
        }
        
        return $schedules;
    }
    
    /**
     * Mendapatkan data jadwal dengan exact match
     * Hanya return data jika masih ada barcode yang kosong
     */
    private function get_exact_schedule_data($tanggal, $jam, $hours_ahead) {
        $this->db->select('
            tanggal,
            jam,
            COUNT(*) as total_count,
            SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode_count,
            SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode_count,
            SUM(CASE WHEN gender = "L" THEN 1 ELSE 0 END) as male_count,
            SUM(CASE WHEN gender = "P" THEN 1 ELSE 0 END) as female_count,
            GROUP_CONCAT(DISTINCT status) as status_list
        ');
        
        $this->db->from('peserta');
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam', $jam);
        $this->db->group_by('tanggal, jam');
        
        $query = $this->db->get();
        $results = $query->result();
        
        log_message('debug', "Exact match query: " . $this->db->last_query());
        log_message('debug', "Exact match results: " . count($results));
        
        $schedules = [];
        foreach ($results as $row) {
            // Hanya include jadwal yang masih ada peserta tanpa barcode
            if ($row->no_barcode_count > 0) {
                // Tambah 5 jam pada jam yang diambil dari data
                $adjusted_time = date('H:i:s', strtotime($row->jam . ' +5 hours'));
                $formatted_time = date('h:i A', strtotime($adjusted_time));
                
                $schedules[] = [
                    'tanggal' => $row->tanggal,
                    'jam' => $formatted_time, // Gunakan format AM/PM untuk field jam
                    'jam_formatted' => $formatted_time,
                    'jam_adjusted' => $adjusted_time,
                    'total_count' => (int)$row->total_count,
                    'no_barcode_count' => (int)$row->no_barcode_count,
                    'with_barcode_count' => (int)$row->with_barcode_count,
                    'male_count' => (int)$row->male_count,
                    'female_count' => (int)$row->female_count,
                    'hours_ahead' => $hours_ahead,
                    'status_list' => $row->status_list,
                    'match_type' => 'exact',
                    'notification_needed' => true,
                    'reason' => 'Ada ' . $row->no_barcode_count . ' peserta tanpa barcode'
                ];
            } else {
                log_message('info', "Jadwal $tanggal $jam: Semua barcode sudah terisi, skip notifikasi");
            }
        }
        
        return $schedules;
    }
    
    /**
     * Mendapatkan variasi format jam
     */
    private function get_jam_variants($jam) {
        $variants = [$jam]; // Original format
        
        // Coba format yang berbeda
        if (strpos($jam, ':') !== false) {
            $parts = explode(':', $jam);
            if (count($parts) >= 2) {
                // Format H:MM:SS -> HH:MM:SS
                if (strlen($parts[0]) == 1) {
                    $variants[] = '0' . $jam;
                }
                
                // Format HH:MM:SS -> H:MM:SS
                if (strlen($parts[0]) == 2 && $parts[0][0] == '0') {
                    $variants[] = substr($jam, 1);
                }
                
                // Format HH:MM:SS -> HH:MM
                if (count($parts) == 3) {
                    $variants[] = $parts[0] . ':' . $parts[1];
                }
                
                // Format HH:MM -> HH:MM:SS
                if (count($parts) == 2) {
                    $variants[] = $jam . ':00';
                }
            }
        }
        
        return array_unique($variants);
    }
    
    /**
     * Mendapatkan data jadwal berdasarkan tanggal saja
     * Hanya return data jika masih ada barcode yang kosong
     */
    private function get_schedule_by_date_only($tanggal, $hours_ahead) {
        $this->db->select('
            tanggal,
            jam,
            COUNT(*) as total_count,
            SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode_count,
            SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode_count,
            SUM(CASE WHEN gender = "L" THEN 1 ELSE 0 END) as male_count,
            SUM(CASE WHEN gender = "P" THEN 1 ELSE 0 END) as female_count,
            GROUP_CONCAT(DISTINCT status) as status_list
        ');
        
        $this->db->from('peserta');
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam IS NOT NULL');
        $this->db->where('jam !=', '');
        $this->db->group_by('tanggal, jam');
        $this->db->order_by('jam', 'ASC');
        
        $query = $this->db->get();
        $results = $query->result();
        
        log_message('debug', "Date only query: " . $this->db->last_query());
        log_message('debug', "Date only results: " . count($results));
        
        $schedules = [];
        foreach ($results as $row) {
            // Hanya include jadwal yang masih ada peserta tanpa barcode
            if ($row->no_barcode_count > 0) {
                // Tambah 5 jam pada jam yang diambil dari data
                $adjusted_time = date('H:i:s', strtotime($row->jam . ' +5 hours'));
                $formatted_time = date('h:i A', strtotime($adjusted_time));
                
                $schedules[] = [
                    'tanggal' => $row->tanggal,
                    'jam' => $formatted_time, // Gunakan format AM/PM untuk field jam
                    'jam_formatted' => $formatted_time,
                    'jam_adjusted' => $adjusted_time,
                    'total_count' => (int)$row->total_count,
                    'no_barcode_count' => (int)$row->no_barcode_count,
                    'with_barcode_count' => (int)$row->with_barcode_count,
                    'male_count' => (int)$row->male_count,
                    'female_count' => (int)$row->female_count,
                    'hours_ahead' => $hours_ahead,
                    'status_list' => $row->status_list,
                    'match_type' => 'date_only',
                    'notification_needed' => true,
                    'reason' => 'Ada ' . $row->no_barcode_count . ' peserta tanpa barcode'
                ];
            } else {
                log_message('info', "Jadwal $tanggal {$row->jam}: Semua barcode sudah terisi, skip notifikasi");
            }
        }
        
        return $schedules;
    }
    
    /**
     * Mendapatkan data jadwal yang sudah terlewat
     */
    private function get_overdue_schedule_data() {
        $current_datetime = date('Y-m-d H:i:s');
        
        // Query untuk mendapatkan jadwal yang sudah terlewat
        $this->db->select('
            tanggal,
            jam,
            COUNT(*) as total_count,
            SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode_count,
            SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode_count,
            SUM(CASE WHEN gender = "L" THEN 1 ELSE 0 END) as male_count,
            SUM(CASE WHEN gender = "P" THEN 1 ELSE 0 END) as female_count
        ');
        
        $this->db->from('peserta');
        $this->db->where("CONCAT(tanggal, ' ', jam) <", $current_datetime);
        $this->db->where('status !=', '2'); // Exclude status "Done"
        $this->db->group_by('tanggal, jam');
        $this->db->order_by('tanggal ASC, jam ASC');
        
        $query = $this->db->get();
        $results = $query->result();
        
        // Format data untuk response
        $overdue_schedules = [];
        foreach ($results as $row) {
            // Hanya include jadwal yang masih ada peserta tanpa barcode
            if ($row->no_barcode_count > 0) {
                // Tambah 5 jam pada jam yang diambil dari data
                $adjusted_time = date('H:i:s', strtotime($row->jam . ' +5 hours'));
                $formatted_time = date('h:i A', strtotime($adjusted_time));
                
                $overdue_schedules[] = [
                    'tanggal' => $row->tanggal,
                    'jam' => $formatted_time, // Gunakan format AM/PM untuk field jam
                    'jam_formatted' => $formatted_time,
                    'jam_adjusted' => $adjusted_time,
                    'total_count' => (int)$row->total_count,
                    'no_barcode_count' => (int)$row->no_barcode_count,
                    'with_barcode_count' => (int)$row->with_barcode_count,
                    'male_count' => (int)$row->male_count,
                    'female_count' => (int)$row->female_count,
                    'overdue_minutes' => $this->calculate_overdue_minutes($row->tanggal, $row->jam)
                ];
            }
        }
        
        return $overdue_schedules;
    }
    
    /**
     * Menghitung berapa menit jadwal sudah terlewat
     */
    private function calculate_overdue_minutes($tanggal, $jam) {
        $schedule_datetime = strtotime("$tanggal $jam");
        $current_datetime = time();
        
        $diff_minutes = ($current_datetime - $schedule_datetime) / 60;
        return max(0, round($diff_minutes));
    }
    
    /**
     * API untuk test koneksi
     * GET /api/test
     */
    public function test() {
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'success' => true,
                'message' => 'API Hajj Telegram Notification berjalan normal',
                'timestamp' => date('Y-m-d H:i:s'),
                'server_time' => time(),
                'timezone' => 'Asia/Jakarta (GMT+8)',
                'timezone_offset' => '+08:00',
                'current_time' => date('Y-m-d H:i:s'),
                'current_timestamp' => time()
            ]));
    }
    
    /**
     * API untuk mendapatkan informasi timezone dan waktu server
     * GET /api/timezone_info
     */
    public function timezone_info() {
        $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'success' => true,
                'timezone' => date_default_timezone_get(),
                'timezone_name' => 'Asia/Hong_Kong',
                'timezone_offset' => '+08:00',
                'current_time' => date('Y-m-d H:i:s'),
                'current_timestamp' => time(),
                'formatted_time' => date('l, d F Y H:i:s T'),
                'utc_time' => gmdate('Y-m-d H:i:s'),
                'utc_timestamp' => time(),
                'timezone_abbr' => date('T'),
                'daylight_saving' => date('I') ? 'Yes' : 'No'
            ]));
    }
    
    /**
     * API untuk test pencarian fleksibel
     * GET /api/test_flexible_search?tanggal=2025-09-14&jam=02:40:00&hours_ahead=0
     */
    public function test_flexible_search() {
        try {
            $tanggal = $this->input->get('tanggal');
            $jam = $this->input->get('jam');
            $hours_ahead = $this->input->get('hours_ahead', 0);
            
            if (empty($tanggal) || empty($jam)) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Parameter tanggal dan jam diperlukan'
                    ]));
                return;
            }
            
            // Test pencarian fleksibel
            $schedules = $this->get_schedule_data_flexible($tanggal, $jam, $hours_ahead);
            
            // Test variasi jam
            $jam_variants = $this->get_jam_variants($jam);
            
            // Test pencarian berdasarkan tanggal saja
            $date_schedules = $this->get_schedule_by_date_only($tanggal, $hours_ahead);
            
            $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'success' => true,
                    'requested' => [
                        'tanggal' => $tanggal,
                        'jam' => $jam,
                        'hours_ahead' => $hours_ahead
                    ],
                    'jam_variants' => $jam_variants,
                    'flexible_search_results' => $schedules,
                    'date_only_results' => $date_schedules,
                    'timezone' => 'Asia/Hong_Kong (GMT+8)',
                    'current_time' => date('Y-m-d H:i:s')
                ]));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Test Error: ' . $e->getMessage()
                ]));
        }
    }
    
    /**
     * API untuk mengecek status barcode pada jadwal tertentu
     * GET /api/check_barcode_status?tanggal=2025-09-14&jam=02:40:00
     */
    public function check_barcode_status() {
        try {
            $tanggal = $this->input->get('tanggal');
            $jam = $this->input->get('jam');
            
            if (empty($tanggal) || empty($jam)) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Parameter tanggal dan jam diperlukan'
                    ]));
                return;
            }
            
            // Query untuk mengecek status barcode
            $this->db->select('
                tanggal,
                jam,
                COUNT(*) as total_count,
                SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode_count,
                SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode_count,
                SUM(CASE WHEN gender = "L" THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN gender = "P" THEN 1 ELSE 0 END) as female_count,
                GROUP_CONCAT(DISTINCT status) as status_list
            ');
            
            $this->db->from('peserta');
            $this->db->where('tanggal', $tanggal);
            $this->db->where('jam', $jam);
            $this->db->group_by('tanggal, jam');
            
            $query = $this->db->get();
            $result = $query->row();
            
            if ($result) {
                $no_barcode_count = (int)$result->no_barcode_count;
                $with_barcode_count = (int)$result->with_barcode_count;
                $total_count = (int)$result->total_count;
                
                $notification_needed = $no_barcode_count > 0;
                $completion_percentage = $total_count > 0 ? round(($with_barcode_count / $total_count) * 100, 2) : 0;
                
                // Tambah 5 jam pada jam yang diambil dari data
                $adjusted_time = date('H:i:s', strtotime($result->jam . ' +5 hours'));
                $formatted_time = date('h:i A', strtotime($adjusted_time));
                
                $this->output
                    ->set_status_header(200)
                    ->set_output(json_encode([
                        'success' => true,
                        'schedule' => [
                            'tanggal' => $result->tanggal,
                            'jam' => $formatted_time, // Gunakan format AM/PM untuk field jam
                            'jam_formatted' => $formatted_time,
                            'jam_adjusted' => $adjusted_time,
                            'total_count' => $total_count,
                            'no_barcode_count' => $no_barcode_count,
                            'with_barcode_count' => $with_barcode_count,
                            'male_count' => (int)$result->male_count,
                            'female_count' => (int)$result->female_count,
                            'status_list' => $result->status_list
                        ],
                        'barcode_status' => [
                            'notification_needed' => $notification_needed,
                            'completion_percentage' => $completion_percentage,
                            'all_barcodes_filled' => $no_barcode_count === 0,
                            'reason' => $notification_needed ? 
                                "Ada $no_barcode_count peserta tanpa barcode" : 
                                "Semua barcode sudah terisi ($completion_percentage%)"
                        ],
                        'timezone' => 'Asia/Hong_Kong (GMT+8)',
                        'current_time' => date('Y-m-d H:i:s')
                    ]));
            } else {
                $this->output
                    ->set_status_header(404)
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Jadwal tidak ditemukan',
                        'requested' => [
                            'tanggal' => $tanggal,
                            'jam' => $jam
                        ]
                    ]));
            }
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]));
        }
    }
    
    /**
     * API untuk debugging database - melihat data yang tersedia
     * GET /api/debug_database?tanggal=2025-09-29&jam=18:00:00
     */
    public function debug_database() {
        try {
            $tanggal = $this->input->get('tanggal');
            $jam = $this->input->get('jam');
            
            $debug_info = [];
            
            // 1. Cek total data di tabel peserta
            $total_peserta = $this->db->count_all('peserta');
            $debug_info['total_peserta'] = $total_peserta;
            
            // 2. Cek data dengan tanggal dan jam yang diminta
            if ($tanggal && $jam) {
                $this->db->where('tanggal', $tanggal);
                $this->db->where('jam', $jam);
                $count_exact = $this->db->count_all_results('peserta');
                $debug_info['count_exact_match'] = $count_exact;
                
                // 3. Cek data dengan tanggal saja
                $this->db->where('tanggal', $tanggal);
                $count_date = $this->db->count_all_results('peserta');
                $debug_info['count_date_match'] = $count_date;
                
                // 4. Cek data dengan jam saja
                $this->db->where('jam', $jam);
                $count_time = $this->db->count_all_results('peserta');
                $debug_info['count_time_match'] = $count_time;
            }
            
            // 5. Cek data dengan tanggal dan jam yang ada
            $this->db->select('tanggal, jam, COUNT(*) as count');
            $this->db->from('peserta');
            $this->db->where('tanggal IS NOT NULL');
            $this->db->where('jam IS NOT NULL');
            $this->db->where('tanggal !=', '');
            $this->db->where('jam !=', '');
            $this->db->group_by('tanggal, jam');
            $this->db->order_by('tanggal DESC, jam DESC');
            $this->db->limit(10);
            $available_schedules = $this->db->get()->result();
            $debug_info['available_schedules'] = $available_schedules;
            
            // 6. Cek status yang ada
            $this->db->select('status, COUNT(*) as count');
            $this->db->from('peserta');
            $this->db->group_by('status');
            $status_distribution = $this->db->get()->result();
            $debug_info['status_distribution'] = $status_distribution;
            
            // 7. Cek data dengan barcode
            $this->db->select('
                COUNT(*) as total,
                SUM(CASE WHEN barcode IS NULL OR barcode = "" THEN 1 ELSE 0 END) as no_barcode,
                SUM(CASE WHEN barcode IS NOT NULL AND barcode != "" THEN 1 ELSE 0 END) as with_barcode
            ');
            $this->db->from('peserta');
            $barcode_stats = $this->db->get()->row();
            $debug_info['barcode_stats'] = $barcode_stats;
            
            $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'success' => true,
                    'debug_info' => $debug_info,
                    'requested_date' => $tanggal,
                    'requested_time' => $jam,
                    'timezone' => 'Asia/Hong_Kong (GMT+8)',
                    'current_time' => date('Y-m-d H:i:s')
                ]));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Debug Error: ' . $e->getMessage()
                ]));
        }
    }
}
?>
