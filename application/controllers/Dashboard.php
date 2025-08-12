<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('agent_model');
        $this->load->model('transaksi_model');
        $this->load->model('user_model');
        $this->load->library('session');
        $this->load->helper('url');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        $data['title'] = 'Dashboard';
        $data['total_agent'] = $this->agent_model->count_all();
        $data['total_peserta'] = $this->transaksi_model->count_all();
        $data['total_user'] = $this->user_model->count_all();
        
        // Get flag_doc filter from GET parameter
        $flag_doc = $this->input->get('flag_doc');
        $data['selected_flag_doc'] = $flag_doc;
        
        // Get all unique flag_doc values for dropdown
        $data['flag_doc_list'] = $this->transaksi_model->get_unique_flag_doc();
        
        // Get detailed statistics based on flag_doc and status = 2 (Done)
        $data['stats'] = $this->transaksi_model->get_dashboard_stats($flag_doc);
        
        // Get data by gender for selected flag_doc
        $data['gender_stats'] = $this->transaksi_model->get_gender_stats($flag_doc);
        
        // Get data by hour for selected flag_doc
        $data['hour_stats'] = $this->transaksi_model->get_hour_stats($flag_doc);
        // Get detailed hour x gender stats (Done only)
        $data['hour_gender_stats'] = $this->transaksi_model->get_hour_gender_stats($flag_doc);
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
    
    public function dermaga() {
         // Check if user is logged in
         if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $data['title'] = 'Modul - Dermaga' ;
        $data['transaksi'] = $this->transaksi_model->get_all_modul_Dermaga();
        $data['kapal'] = $this->kapal_model->get_all();
        $data['last_transaksi_dermaga1'] = $this->transaksi_model->get_last_transaksi_dermaga1();
        $data['last_transaksi_dermaga2'] = $this->transaksi_model->get_last_transaksi_dermaga2();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('modul/dermaga', $data);
        $this->load->view('templates/footer');
    }
    
    public function dermaga_1() {
        // Redirect to the new dermaga method
        redirect('dashboard/dermaga');
    }
    
    public function dermaga_2() {
        // This method is kept for backward compatibility but redirects to dermaga
        redirect('dashboard/dermaga');
    }
    
    public function update_transaksi() {
        $id = $this->input->post('id_transaksi');
        $action = $this->input->post('action');
        
        $transaksi = $this->transaksi_model->get_by_id($id);
        
        if (!$transaksi) {
            $response = [
                'status' => false,
                'message' => 'Data transaksi tidak ditemukan'
            ];
            echo json_encode($response);
            return;
        }
        
        $data = [];
        
        if ($action == 'sandar') {
            $data['status_sandar'] = 'Sandar';
            $data['waktu_mulai_sandar'] = date('Y-m-d H:i:s');
        } else if ($action == 'unsandar') {
            $data['status_sandar'] = 'Tidak Sandar';
            $data['waktu_selesai_sandar'] = date('Y-m-d H:i:s');
            $data['air_tawar_valve'] = 'Close';
        } else if ($action == 'selesai_sandar') {
            // Mark transaction as completed
            $data['status_trx'] = 1;
            $data['waktu_selesai_sandar'] = date('Y-m-d H:i:s');
            $data['air_tawar_valve'] = 'Close';
        } else if ($action == 'valve_open') {
            $data['air_tawar_valve'] = 'Open';
        } else if ($action == 'valve_close') {
            $data['air_tawar_valve'] = 'Close';
        } else if ($action == 'update_volume') {
            $volume = $this->input->post('volume');
            $liter_per_menit = $this->input->post('liter_per_menit');
            
            $data['volume_air'] = $volume;
            $data['liter_per_menit'] = $liter_per_menit;
            $data['volume_total'] = $volume;
        }
        
        $update = $this->transaksi_model->update($id, $data);
        
        $response = [
            'status' => $update ? true : false,
            'message' => $update ? 'Data berhasil diperbarui' : 'Gagal memperbarui data'
        ];
        
        echo json_encode($response);
    }
    
    public function create_transaksi() {
        $dermaga = $this->input->post('dermaga');
        //$id_kapal = $this->input->post('id_kapal');
        if($dermaga == 1){
            $relay = 'M0';
        }else if($dermaga == 2){
            $relay = 'M2';
        }
        if($dermaga == 1){
            if($this->transaksi_model->get_last_transaksi_dermaga1() == 0){
                $data = [
                    //'id_kapal' => $id_kapal,
                    'kode_transaksi' => $this->transaksi_model->generate_kode_transaksi(),
                    'dermaga' => $dermaga,
                    'relay' => $relay,
                    'status_sandar' => 'Sandar',
                    'waktu_mulai_sandar' => date('Y-m-d H:i:s'),
                    'air_tawar_valve' => 'Close',
                    'volume_air' => 0,
                    'liter_per_menit' => 0,
                    'volume_total' => 0
                ];
                
                $insert = $this->transaksi_model->insert($data);
                
                if ($insert) {
                    $this->session->set_flashdata('success', 'Kapal berhasil ditambahkan ke dermaga '.$dermaga);
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan kapal ke dermaga');
                }
            }
        }else{
            if($this->transaksi_model->get_last_transaksi_dermaga2() == 0){
                $data = [
                    //'id_kapal' => $id_kapal,
                    'kode_transaksi' => $this->transaksi_model->generate_kode_transaksi(),
                    'dermaga' => $dermaga,
                    'relay' => $relay,
                    'status_sandar' => 'Sandar',
                    'waktu_mulai_sandar' => date('Y-m-d H:i:s'),
                    'air_tawar_valve' => 'Close',
                    'volume_air' => 0,
                    'liter_per_menit' => 0,
                    'volume_total' => 0
                ];
                
                $insert = $this->transaksi_model->insert($data);
                
                if ($insert) {
                    $this->session->set_flashdata('success', 'Kapal berhasil ditambahkan ke dermaga '.$dermaga);
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan kapal ke dermaga');
                }
            }
        
        redirect('dashboard/dermaga/'.$dermaga);
        }
    }

public function auto_update_transaksi($dermaga)
{
    header('Content-Type: application/json'); // Pastikan header JSON

    $transaksi = $this->db->select('*')
                          ->from('transaksi_dermaga')
                          ->where('dermaga', $dermaga)
                          ->where('status_trx', 0)
                          ->order_by('id_transaksi', 'DESC')
                          ->limit(1)
                          ->get()
                          ->row();

    if (!$transaksi) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Tidak ada transaksi aktif ditemukan'
        ]);
        return;
    }

    $waktu_mulai = new DateTime($transaksi->waktu_mulai_sandar);
    $waktu_selesai = new DateTime();
    $durasi = $waktu_mulai->diff($waktu_selesai);

    $data = [
        'waktu_selesai_sandar' => $waktu_selesai->format('Y-m-d H:i:s'),
        'durasi_sandar' => $durasi->format('%H:%I:%S'),
        'status_trx' => 1
    ];

    try {
        $update = $this->transaksi_model->update($transaksi->id_transaksi, $data);

        if ($update) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Transaksi berhasil diupdate',
                'id_transaksi' => $transaksi->id_transaksi
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal mengupdate transaksi'
            ]);
        }
    } catch (Exception $e) {
        log_message('error', 'Error updating transaction: ' . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}


    public function check_transaction_changes() {
        // Pastikan request adalah AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        try {
            // Ambil semua transaksi aktif
            $transactions = $this->transaksi_model->get_all_active();
            
            // Siapkan data response
            $response_data = [];
            
            foreach ($transactions as $transaction) {
                // Data sudah termasuk max_volume_air dari join dengan tabel kapal
                
                // Format waktu ke format yang sesuai
                if ($transaction->waktu_mulai_sandar) {
                    $transaction->waktu_mulai_sandar = date('Y-m-d H:i:s', strtotime($transaction->waktu_mulai_sandar));
                }
                if ($transaction->waktu_selesai_sandar) {
                    $transaction->waktu_selesai_sandar = date('Y-m-d H:i:s', strtotime($transaction->waktu_selesai_sandar));
                }
                
                // Pastikan nilai numerik diformat dengan benar
                $transaction->volume_air = floatval($transaction->volume_air);
                $transaction->liter_per_menit = floatval($transaction->liter_per_menit);
                $transaction->max_volume_air = floatval($transaction->max_volume_air);
                
                // Tambahkan informasi tambahan yang mungkin diperlukan
                $transaction->formatted = [
                    'volume_air' => number_format($transaction->volume_air, 2, '.', ','),
                    'liter_per_menit' => number_format($transaction->liter_per_menit, 2, '.', ','),
                    'max_volume_air' => number_format($transaction->max_volume_air, 2, '.', ',')
                ];
                
                // Tambahkan ke array response
                $response_data[] = $transaction;
            }
            
            // Kirim response dalam format JSON
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'transactions' => $response_data,
                    'timestamp' => date('Y-m-d H:i:s')
                ]));
                
        } catch (Exception $e) {
            // Log error untuk debugging
            log_message('error', 'Error in check_transaction_changes: ' . $e->getMessage());
            
            // Kirim response error
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat memperbarui data'
                ]));
        }
    }

    // Fungsi helper untuk memastikan request AJAX
    private function _is_ajax_request() {
        return $this->input->is_ajax_request();
    }

    public function update_relay_status() {
        $id_transaksi = $this->input->post('id_transaksi');
        $relay_status = $this->input->post('relay_status');
        $read_relay = $this->input->post('read_relay');

        // Update status relay di database
        $data = array(
            'relay_status' => $relay_status,
            'read_relay' => $read_relay
        );

        $this->db->where('id_transaksi', $id_transaksi);
        $update = $this->db->update('transaksi_dermaga', $data);

        if ($update) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Relay status updated successfully'
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Failed to update relay status'
            ));
        }
    }

    public function update_flow_rate()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $id_transaksi = $this->input->post('id_transaksi');
        $flow_rate = $this->input->post('flow_rate');
        $total_flow = $this->input->post('total_flow');
        $meter_status = $this->input->post('meter_status');

        // Update data transaksi
        $data = array(
            'liter_per_menit' => $flow_rate,
            'volume_air' => $total_flow,
            'flow_meter_status' => $meter_status
        );

        $this->db->where('id_transaksi', $id_transaksi);
        $update = $this->db->update('transaksi_dermaga', $data);

        if ($update) {
            // Ambil data transaksi terbaru
            $this->db->select('*');
            $this->db->from('transaksi_dermaga');
            $this->db->where('id_transaksi', $id_transaksi);
            $query = $this->db->get();
            $transaction = $query->row();

            echo json_encode(array(
                'status' => 'success',
                'message' => 'Flow rate updated successfully',
                'data' => array(
                    'id_transaksi' => $id_transaksi,
                    'flow_rate' => $flow_rate,
                    'total_flow' => $total_flow,
                    'meter_status' => $meter_status,
                    'air_tawar_valve' => $transaction->air_tawar_valve
                )
            ));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Failed to update flow rate'));
        }
    }

    public function update_photobeam_status()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $id_transaksi = $this->input->post('id_transaksi');
        $status_sandar = $this->input->post('status_sandar');
        $relay_status = $this->input->post('relay_status');
        $read_relay = $this->input->post('read_relay');

        // Update data transaksi
        $data = array(
            'status_sandar' => $status_sandar,
            'relay_status' => $relay_status,
            'read_relay' => $read_relay
        );

        // Jika status berubah menjadi sandar, update waktu mulai sandar
        if ($status_sandar === 'Sandar') {
            $data['waktu_mulai_sandar'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id_transaksi', $id_transaksi);
        $update = $this->db->update('transaksi_dermaga', $data);

        if ($update) {
            // Ambil data transaksi terbaru
            $this->db->select('*');
            $this->db->from('transaksi_dermaga');
            $this->db->where('id_transaksi', $id_transaksi);
            $query = $this->db->get();
            $transaction = $query->row();

            echo json_encode(array(
                'status' => 'success',
                'message' => 'Status updated successfully',
                'data' => array(
                    'id_transaksi' => $id_transaksi,
                    'status_sandar' => $status_sandar,
                    'relay_status' => $relay_status,
                    'waktu_mulai_sandar' => $data['waktu_mulai_sandar'],
                    'air_tawar_valve' => $transaction->air_tawar_valve,
                    'volume_air' => $transaction->volume_air,
                    'liter_per_menit' => $transaction->liter_per_menit
                )
            ));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Failed to update status'));
        }
    }
    public function sensor(){
        $data['title'] = 'Modul - Sensor';
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('modul/sensor', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Method untuk mengecek status Photobeam
     */
    public function check_photobeam_status() {
        // Pastikan ini adalah request AJAX
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        // Load model yang diperlukan
        $this->load->model('photobeam_model');
        
        // Dapatkan status Photobeam
        $status = $this->photobeam_model->get_status();
        
        // Return response dalam format JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => $status]));
    }

    /**
     * Method untuk mengupdate status sandar
     */
    public function update_status() {
        // Pastikan ini adalah request AJAX
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $id_transaksi = $this->input->post('id_transaksi');
        $action = $this->input->post('action');
        
        // Load model yang diperlukan
        $this->load->model(['transaksi_model', 'photobeam_model']);
        
        // Jika action adalah sandar, cek status photobeam
        if ($action === 'sandar') {
            $photobeam_status = $this->photobeam_model->get_status();
            
            // Jika photobeam OFF, kirim response error
            if ($photobeam_status !== 'OFF') {
                $response = [
                    'success' => false,
                    'message' => 'Kapal tidak dapat disandarkan. Photobeam harus ON!'
                ];
                $this->output->set_content_type('application/json')->set_output(json_encode($response));
                return;
            }
        }
        
        // Jika sampai di sini, lanjutkan dengan update status
        $update_data = [
            'status_sandar' => ($action === 'sandar' ? 'Sandar' : 
                              ($action === 'unsandar' ? 'Tidak Sandar' : 
                              ($action === 'selesai_sandar' ? 'Selesai' : null))),
            'waktu_mulai_sandar' => ($action === 'sandar' ? date('Y-m-d H:i:s') : null),
            'waktu_selesai_sandar' => ($action === 'selesai_sandar' ? date('Y-m-d H:i:s') : null),
            'status_trx' => ($action === 'selesai_sandar' ? 1 : 0)
        ];
        
        $result = $this->transaksi_model->update($id_transaksi, $update_data);
        
        if ($result) {
            // Jika berhasil update, log aktivitas
            $this->log_activity($id_transaksi, $action);
            
            $response = [
                'success' => true,
                'message' => 'Status berhasil diupdate',
                'action' => $action,
                'status' => $update_data['status_sandar']
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Gagal mengupdate status'
            ];
        }
        
        // Return response dalam format JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Method untuk mencatat log aktivitas
     */
    private function log_activity($id_transaksi, $action) {
        $this->load->model('log_model');
        
        $log_data = [
            'id_transaksi' => $id_transaksi,
            'action' => $action,
            'created_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ];
        
        $this->log_model->insert($log_data);
    }
} 