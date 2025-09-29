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
            
            // Ambil data jadwal dari database
            $schedules = $this->get_schedule_data($target_date, $target_time, $hours_ahead);
            
            $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'success' => true,
                    'data' => $schedules,
                    'target_datetime' => $target_datetime,
                    'hours_ahead' => $hours_ahead
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
                    'total' => count($overdue_schedules)
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
        // Query untuk mendapatkan data jadwal
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
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam', $jam);
        $this->db->where('status !=', '2'); // Exclude status "Done"
        $this->db->group_by('tanggal, jam');
        
        $query = $this->db->get();
        $results = $query->result();
        
        // Format data untuk response
        $schedules = [];
        foreach ($results as $row) {
            $schedules[] = [
                'tanggal' => $row->tanggal,
                'jam' => $row->jam,
                'total_count' => (int)$row->total_count,
                'no_barcode_count' => (int)$row->no_barcode_count,
                'with_barcode_count' => (int)$row->with_barcode_count,
                'male_count' => (int)$row->male_count,
                'female_count' => (int)$row->female_count,
                'hours_ahead' => $hours_ahead
            ];
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
                $overdue_schedules[] = [
                    'tanggal' => $row->tanggal,
                    'jam' => $row->jam,
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
                'server_time' => time()
            ]));
    }
}
?>
