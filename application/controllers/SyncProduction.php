<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SyncProduction extends CI_Controller {

    /**
     * Maximum number of rows to insert per batch during sync.
     *
     * @var int
     */
    private $batch_size = 500;

    /**
     * Tables to exclude from synchronization.
     *
     * @var array
     */
    private $excluded_tables = array('ci_sessions');

    public function __construct() {
        parent::__construct();

        $this->load->library('session');
        $this->load->helper(array('url', 'form'));
        $this->load->database();

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
            redirect('dashboard');
        }
    }

    public function index() {
        $data['title'] = 'Sync Production';
        $data['sync_summary'] = $this->session->flashdata('sync_summary');

        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('sync_production/index', $data);
        $this->load->view('templates/footer');
    }

    public function run() {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        // Allow long running process
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        @ini_set('memory_limit', '1024M');

        $productionDb = $this->load->database('production', TRUE);

        $connection_initialized = $productionDb && $productionDb->initialize();

        if (!$connection_initialized) {
            $debug_details = array(
                'timestamp'        => date('Y-m-d H:i:s'),
                'mysqli_errno'     => function_exists('mysqli_connect_errno') ? mysqli_connect_errno() : null,
                'mysqli_error'     => function_exists('mysqli_connect_error') ? mysqli_connect_error() : null,
                'db_error'         => method_exists($productionDb, 'error') ? $productionDb->error() : null,
                'hostname'         => $productionDb ? $productionDb->hostname : null,
                'username'         => $productionDb ? $productionDb->username : null,
                'database'         => $productionDb ? $productionDb->database : null,
                'environment'      => ENVIRONMENT,
                'client_ip'        => $this->input->ip_address(),
                'user_id'          => $this->session->userdata('user_id'),
                'session_username' => $this->session->userdata('username'),
            );

            log_message('error', 'SyncProduction: koneksi ke database production gagal :: ' . json_encode($debug_details));

            $this->handle_failure(
                'Gagal terhubung ke database production. Periksa kembali konfigurasi koneksi.',
                $debug_details
            );
            return;
        }

        $tables = $productionDb->list_tables();

        if (empty($tables)) {
            $this->handle_failure('Tidak ditemukan tabel pada database production.');
            return;
        }

        $sync_summary = array();

        $this->db->trans_begin();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                if (in_array($table, $this->excluded_tables, true)) {
                    continue;
                }

                $this->db->query('TRUNCATE TABLE `' . $table . '`');

                $offset = 0;
                $total_synced = 0;

                while (true) {
                    $productionDb->limit($this->batch_size, $offset);
                    $query = $productionDb->get($table);

                    $rows = $query->result_array();
                    $row_count = count($rows);

                    if ($row_count === 0) {
                        break;
                    }

                    $this->db->insert_batch($table, $rows);
                    $total_synced += $row_count;
                    $offset += $this->batch_size;

                    $productionDb->reset_query();
                    $this->db->reset_query();
                }

                $sync_summary[] = array(
                    'table' => $table,
                    'rows' => $total_synced
                );
            }

            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal diproses.');
            }

            $this->db->trans_commit();

            $this->session->set_flashdata('success', 'Sinkronisasi data dari production berhasil diselesaikan.');
            $this->session->set_flashdata('sync_summary', $sync_summary);

            $this->redirect_success();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            log_message('error', 'Sync Production gagal: ' . $e->getMessage());
            $this->handle_failure(
                'Sinkronisasi gagal: ' . $e->getMessage(),
                array(
                    'exception' => $e->getMessage(),
                    'trace'     => $e->getTraceAsString()
                )
            );
        }
    }

    private function redirect_success() {
        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Sinkronisasi data dari production berhasil diselesaikan.',
                    'redirect' => base_url('syncproduction')
                )));
        } else {
            redirect('syncproduction');
        }
    }

    private function handle_failure($message, array $debug_details = array()) {
        if (!empty($debug_details)) {
            log_message('error', 'SyncProduction failure detail: ' . json_encode($debug_details));
        }

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => $message,
                    'debug'   => $debug_details
                )));
        } else {
            $this->session->set_flashdata('error', $message);
            if (!empty($debug_details)) {
                $this->session->set_flashdata('sync_error_details', $debug_details);
            }
            redirect('syncproduction');
        }
    }
}

