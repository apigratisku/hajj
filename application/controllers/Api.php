<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('transaksi_model');
        
        // Set JSON header
        $this->output->set_content_type('application/json');
    }

    /**
     * Get schedule data for specific date
     * GET /api/schedule?tanggal=YYYY-MM-DD
     */
    public function schedule() {
        try {
            $tanggal = $this->input->get('tanggal');
            
            if (!$tanggal) {
                $tanggal = date('Y-m-d');
            }
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Format tanggal tidak valid. Gunakan YYYY-MM-DD'
                    ]));
                return;
            }
            
            $data = $this->transaksi_model->get_schedule_for_api($tanggal);
            
            $this->output->set_output(json_encode([
                'status' => 'success',
                'data' => $data,
                'tanggal' => $tanggal
            ]));
            
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]));
        }
    }
    
    /**
     * Get pending barcode data for specific schedule
     * GET /api/pending-barcode?tanggal=YYYY-MM-DD&jam=HH:MM:SS
     */
    public function pending_barcode() {
        try {
            $tanggal = $this->input->get('tanggal');
            $jam = $this->input->get('jam');
            
            if (!$tanggal || !$jam) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Tanggal dan jam harus diisi'
                    ]));
                return;
            }
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Format tanggal tidak valid. Gunakan YYYY-MM-DD'
                    ]));
                return;
            }
            
            // Validate time format - accept both HH:MM and HH:MM:SS
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $jam)) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Format jam tidak valid. Gunakan HH:MM atau HH:MM:SS'
                    ]));
                return;
            }
            
            // Normalize jam format
            if (strlen($jam) == 5) {
                $jam = $jam . ':00'; // Add seconds if missing
            }
            
            // Get data from model - try flexible method first
            $data = $this->transaksi_model->get_data_flexible_time($tanggal, $jam);
            
            // If no data found, try the original method
            if (empty($data)) {
                $data = $this->transaksi_model->get_pending_barcode_for_api($tanggal, $jam);
            }
            
            // Debug: Log query for troubleshooting
            log_message('debug', 'API pending_barcode - Tanggal: ' . $tanggal . ', Jam: ' . $jam);
            log_message('debug', 'API pending_barcode - Data count: ' . count($data));
            log_message('debug', 'API pending_barcode - Last query: ' . $this->db->last_query());
            log_message('debug', 'API pending_barcode - Raw data: ' . json_encode($data));
            
            // Format jam ke AM/PM
            $jam_sistem = date('h:i A', strtotime($jam));
            $jam_mekkah = date('h:i A', strtotime($jam . ' +5 hours'));
            
            // Count statistics
            $count_total = count($data);
            $count_barcode_lengkap = 0;
            $count_tidak_ada_barcode = 0;
            
            foreach ($data as $item) {
                if (!empty($item['barcode']) && $item['barcode'] !== '') {
                    $count_barcode_lengkap++;
                } else {
                    $count_tidak_ada_barcode++;
                }
            }
            
            $this->output->set_output(json_encode([
                'status' => 'success',
                'data' => $data,
                'tanggal' => $tanggal,
                'jam_sistem' => $jam_sistem,
                'jam_mekkah' => $jam_mekkah,
                'count_total' => $count_total,
                'count_barcode_lengkap' => $count_barcode_lengkap,
                'count_tidak_ada_barcode' => $count_tidak_ada_barcode
                ]));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]));
        }
    }
    
    /**
     * Get overdue schedules
     * GET /api/overdue-schedules
     */
    public function overdue_schedules() {
        try {
            $data = $this->transaksi_model->get_overdue_schedules_for_api();
            
            $this->output->set_output(json_encode([
                'status' => 'success',
                'data' => $data,
                'count' => count($data)
                ]));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]));
        }
    }
    
    /**
     * Get all pending barcode data for today and tomorrow
     * GET /api/pending-barcode-all
     */
    public function pending_barcode_all() {
        try {
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            
            $data = [];
            
            // Get today's data
            $today_data = $this->transaksi_model->get_pending_barcode_all_for_api($today);
            if (!empty($today_data)) {
                $data = array_merge($data, $today_data);
            }
            
            // Get tomorrow's data
            $tomorrow_data = $this->transaksi_model->get_pending_barcode_all_for_api($tomorrow);
            if (!empty($tomorrow_data)) {
                $data = array_merge($data, $tomorrow_data);
            }
            
            $this->output->set_output(json_encode([
                'status' => 'success',
                'data' => $data,
                'count' => count($data),
                'date_range' => [$today, $tomorrow]
            ]));
            
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]));
        }
    }

    /**
     * Test Telegram notification endpoint
     * POST /api/test-telegram
     */
    public function test_telegram() {
        try {
            $message = $this->input->post('message');
            $tanggal = $this->input->post('tanggal');
            $jam = $this->input->post('jam');
            
            if (!$message) {
                $message = "Test notification dari API Hajj - " . date('Y-m-d H:i:s');
            }
            
            // Format jam ke AM/PM jika ada
            $jam_formatted = '';
            if ($jam) {
                $jam_formatted = date('h:i A', strtotime($jam));
            }
            
            // Build test message
            $test_message = "🧪 <b>TEST NOTIFICATION</b>\n\n";
            $test_message .= "📅 Tanggal: " . ($tanggal ?: date('Y-m-d')) . "\n";
            if ($jam_formatted) {
                $test_message .= "🕐 Jam Sistem: " . $jam_formatted . "\n";
                $test_message .= "🕐 Jam Mekkah: " . date('h:i A', strtotime($jam . ' +5 hours')) . "\n";
            }
            $test_message .= "💬 Pesan: " . $message . "\n";
            $test_message .= "⏰ Waktu Test: " . date('d F Y H:i:s') . "\n";
            $test_message .= "🔗 API Endpoint: " . base_url('api/test-telegram');
            
            // Send to Telegram using Python script
            $result = $this->send_telegram_via_python($test_message);
            
            if ($result) {
                $this->output->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Test notification sent successfully',
                    'telegram_message' => $test_message,
                    'timestamp' => date('Y-m-d H:i:s')
                ]));
            } else {
                $this->output
                    ->set_status_header(500)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Failed to send test notification',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]));
            }
            
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Test notification failed: ' . $e->getMessage(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]));
        }
    }
    
    /**
     * Send Telegram message via Python script
     */
    private function send_telegram_via_python($message) {
        try {
            // Path to Python script
            $python_script = FCPATH . 'vendor/notification/telegram_scheduler.py';
            
            // Escape message for command line
            $escaped_message = escapeshellarg($message);
            
            // Execute Python script with test flag
            $command = "python \"$python_script\" --test $escaped_message 2>&1";
            $output = shell_exec($command);
            
            // Check if successful (Python script returns "SUCCESS" on success)
            return strpos($output, 'SUCCESS') !== false;
            
        } catch (Exception $e) {
            log_message('error', 'Failed to send Telegram via Python: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Debug endpoint untuk troubleshooting
     * GET /api/debug?tanggal=YYYY-MM-DD&jam=HH:MM:SS
     */
    public function debug() {
        try {
            $tanggal = $this->input->get('tanggal');
            $jam = $this->input->get('jam');
            
            if (!$tanggal || !$jam) {
                $this->output
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Parameter tanggal dan jam diperlukan'
                    ]));
                return;
            }
            
            // Normalize jam format
            if (strlen($jam) == 5) {
                $jam = $jam . ':00'; // Add seconds if missing
            }
            
            // Test query langsung
            $this->db->select('*');
            $this->db->from('peserta');
            $this->db->where('tanggal', $tanggal);
            $this->db->where('jam', $jam);
            $this->db->limit(5);
            $raw_data = $this->db->get()->result_array();
            
            // Test dengan filter yang lebih longgar
            $this->db->select('*');
            $this->db->from('peserta');
            $this->db->where('tanggal', $tanggal);
            $this->db->where('jam', $jam);
            $this->db->where('tanggal IS NOT NULL');
            $this->db->where('jam IS NOT NULL');
            $this->db->limit(5);
            $filtered_data = $this->db->get()->result_array();
            
            // Test count total
            $this->db->from('peserta');
            $this->db->where('tanggal', $tanggal);
            $this->db->where('jam', $jam);
            $total_count = $this->db->count_all_results();
            
            $this->output->set_output(json_encode([
                'status' => 'success',
                'debug_info' => [
                    'tanggal' => $tanggal,
                    'jam' => $jam,
                    'total_count' => $total_count,
                    'raw_data_count' => count($raw_data),
                    'filtered_data_count' => count($filtered_data),
                    'last_query' => $this->db->last_query(),
                    'raw_data_sample' => $raw_data,
                    'filtered_data_sample' => $filtered_data
                ]
            ]));
            
        } catch (Exception $e) {
        $this->output
                ->set_status_header(500)
            ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Debug failed: ' . $e->getMessage()
                ]));
        }
    }

    /**
     * Health check endpoint
     * GET /api/health
     */
    public function health() {
        try {
            // Test database connection
            $this->db->simple_query('SELECT 1');
            
            $this->output->set_output(json_encode([
                'status' => 'success',
                'message' => 'API is healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'connected'
            ]));
            
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'API health check failed: ' . $e->getMessage(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]));
        }
    }
}