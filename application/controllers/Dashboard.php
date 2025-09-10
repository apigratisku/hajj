<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('transaksi_model');
        $this->load->model('user_model');
        $this->load->library('session');
        $this->load->library('telegram_notification');
        $this->load->helper('url');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        $data['title'] = 'Dashboard';

        // Get flag_doc filter from GET parameter
        $flag_doc = $this->input->get('flag_doc');

        if(empty($flag_doc)){
            $data['total_peserta'] = $this->transaksi_model->count_all();
        }else{
            $data['total_peserta'] = $this->transaksi_model->count_all_filtered($flag_doc);
        }
        $data['total_user'] = $this->user_model->count_all();
        
        
        $data['selected_flag_doc'] = $flag_doc;
        
        // Get all unique flag_doc values for dropdown
        $data['flag_doc_list'] = $this->transaksi_model->get_unique_flag_doc();
        
        // Get detailed statistics based on flag_doc and status = 2 (Done)
        $data['stats'] = $this->transaksi_model->get_dashboard_stats($flag_doc);
        $data['stats_on_target'] = $this->transaksi_model->get_dashboard_stats_on_target($flag_doc);
        $data['stats_already'] = $this->transaksi_model->get_dashboard_stats_already($flag_doc);
        
        // Get data by gender for selected flag_doc
        $data['gender_stats'] = $this->transaksi_model->get_gender_stats($flag_doc);
        
        // Get data by hour for selected flag_doc
        $data['hour_stats'] = $this->transaksi_model->get_hour_stats($flag_doc);
        // Get detailed hour x gender stats (Done only)
        $data['hour_gender_stats'] = $this->transaksi_model->get_hour_gender_stats($flag_doc);
        
        // Get schedule grouped by date
        $data['schedule_by_date'] = $this->transaksi_model->get_schedule_by_date($flag_doc);
        
        // Get monthly visa import statistics for the last 12 months
        $data['monthly_visa_stats'] = $this->transaksi_model->get_monthly_visa_import_stats();
        
        // Get unique travel names for filter
        $data['travel_list'] = $this->transaksi_model->get_unique_nama_travel();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
    
    
    
    

    public function mark_schedule_complete() {
        // Set header untuk AJAX response
        header('Content-Type: application/json');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            echo json_encode(['status' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $tanggal = $this->input->post('tanggal');
        $jam = $this->input->post('jam');
        $flag_doc = $this->input->post('flag_doc');
        
        // Validasi input
        if (empty($tanggal) || empty($jam)) {
            echo json_encode(['status' => false, 'message' => 'Tanggal dan jam harus diisi']);
            return;
        }
        
        // Cek apakah tanggal dan jam sudah lewat
        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');
        
        // Validasi: tombol hanya bisa diklik jika:
        // 1. Tanggal sudah lewat, atau
        // 2. Tanggal sama tapi jam sudah lewat
        $can_complete = false;
        if ($tanggal < $current_date) {
            $can_complete = true;
        } elseif ($tanggal == $current_date && $jam <= $current_time) {
            $can_complete = true;
        }
        
        if (!$can_complete) {
            echo json_encode(['status' => false, 'message' => 'Jadwal belum waktunya untuk diselesaikan']);
        return;
    }

        // Update status massal
        $result = $this->transaksi_model->update_status_massal($tanggal, $jam, $flag_doc);
        
        if ($result) {
            // Kirim notifikasi Telegram untuk mark schedule complete
            if($this->session->userdata('username') != 'adhit'):
            $this->telegram_notification->dashboard_notification('Mark Schedule Complete', "Tanggal: {$tanggal}, Jam: {$jam}, Flag Doc: {$flag_doc}");
            endif;
            echo json_encode(['status' => true, 'message' => 'Status berhasil diperbarui untuk jadwal ' . $tanggal . ' jam ' . $jam]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal memperbarui status']);
        }
    }

    /**
     * Get monthly visa import statistics by travel
     */
    public function get_monthly_visa_by_travel() {
        // Set header untuk AJAX response
        header('Content-Type: application/json');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            echo json_encode(['status' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $nama_travel = $this->input->post('nama_travel');
        
        try {
            $monthly_stats = $this->transaksi_model->get_monthly_visa_import_by_travel($nama_travel);
            
            echo json_encode([
                'status' => true,
                'data' => $monthly_stats
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    
} 