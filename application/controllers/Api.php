<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // Load required models
        $this->load->model('transaksi_model');
        $this->load->model('photobeam_model');
        // Optional: Set header agar output selalu JSON
        header('Content-Type: application/json');
    }

    public function sensor()
    {
        $response = [
            "data" => [
                "dermaga1" => [
                    "flow" => [
                        "meter_status" => "OFF",
                        "rate" => 0.0,
                        "total" => 0.0
                    ],
                    "photobeam" => [
                        "status" => "ON",
                        "value" => TRUE
                    ]
                ],
                "dermaga2" => [
                    "flow" => [
                        "meter_status" => "ON",
                        "rate" => 60.0,
                        "total" => 423.0710186958313
                    ],
                    "photobeam" => [
                        "status" => "ON",
                        "value" => true
                    ]
                ]
            ],
            "status" => "success"
        ];

        echo json_encode($response);
    }

    public function update_status() {
        try {
            // Validate request
            if (!$this->input->is_ajax_request()) {
                throw new Exception('Invalid request method');
            }

            $id_transaksi = $this->input->post('id_transaksi');
            $action = $this->input->post('action');

            if (!$id_transaksi || !$action) {
                throw new Exception('Missing required parameters');
            }

            // Get current transaction
            $transaction = $this->transaksi_model->get_by_id($id_transaksi);
            if (!$transaction) {
                throw new Exception('Transaction not found');
            }

            // Prepare update data based on action
            $update_data = [];
            switch ($action) {
                case 'sandar':
                    // Check photobeam status if action is sandar
                    $photobeam_status = $this->photobeam_model->get_status();
                    if ($photobeam_status !== 'ON') {
                        throw new Exception('Cannot dock: Photobeam must be ON');
                    }
                    $update_data = [
                        'status_sandar' => 'Sandar',
                        'waktu_mulai_sandar' => date('Y-m-d H:i:s')
                    ];
                    break;

                case 'unsandar':
                    $update_data = [
                        'status_sandar' => 'Tidak Sandar',
                        'waktu_selesai_sandar' => date('Y-m-d H:i:s'),
                        'air_tawar_valve' => 'Close'
                    ];
                    break;

                case 'selesai_sandar':
                    $update_data = [
                        'status_trx' => 1,
                        'waktu_selesai_sandar' => date('Y-m-d H:i:s'),
                        'air_tawar_valve' => 'Close'
                    ];
                    break;

                default:
                    throw new Exception('Invalid action');
            }

            // Perform update
            $result = $this->transaksi_model->update($id_transaksi, $update_data);
            if (!$result) {
                throw new Exception('Failed to update transaction');
            }

            // Get updated transaction data
            $updated_transaction = $this->transaksi_model->get_by_id($id_transaksi);

            echo json_encode([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'id_transaksi' => $id_transaksi,
                    'status_sandar' => $updated_transaction->status_sandar,
                    'waktu_mulai_sandar' => $updated_transaction->waktu_mulai_sandar,
                    'waktu_selesai_sandar' => $updated_transaction->waktu_selesai_sandar,
                    'air_tawar_valve' => $updated_transaction->air_tawar_valve,
                    'status_trx' => $updated_transaction->status_trx
                ]
            ]);

        } catch (Exception $e) {
            // Log the error
            log_message('error', 'API update_status error: ' . $e->getMessage());
            
            // Return error response
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function get_flow_meter_dermaga1() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        if (!$data) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'Tidak ada data yang diterima']));
            return;
        }
    
       // $receivedData = isset($data['data']) ? $data['data'] : [];
        $volume_air = $data['data'][0]; // Haiwell_PLC_asdp.Hasil_Liter_1
        $pulse_flow = $data['data'][1]; // Haiwell_PLC_asdp.Pulse_Flow_1
    
    
        // Data yang akan diupdate
        $updateData = [
            'volume_air' => $volume_air,
        ];
    
        $row = $this->db->where('dermaga', 1)->order_by('id_transaksi', 'DESC')->limit(1)->get('transaksi_dermaga')->row();
        $this->db->where('id_transaksi', $row->id_transaksi);
        $this->db->update('transaksi_dermaga', $updateData);
    
        // Simpan ke log file (opsional)
        $logLine = date("Y-m-d H:i:s") . " - Updated Data: " . json_encode($updateData) . "\n";
        $logPath = FCPATH . 'application/logs/flow_log1.txt';
        file_put_contents($logPath, $logLine, FILE_APPEND);
    
        $response = [
            'status' => 'success',
            'updated_data' => $updateData
        ];
    
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    

    public function get_flow_meter_dermaga2() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    
        if (!$data) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'Tidak ada data yang diterima']));
            return;
        }
    
        $volume_air = $data['data'][0]; // Haiwell_PLC_asdp.Hasil_Liter_2
        $pulse_flow = $data['data'][1]; // Haiwell_PLC_asdp.Pulse_Flow_2

        // Data yang akan diupdate
        $updateData = [
            'volume_air' => $volume_air,
        ];
    
        $row = $this->db->where('dermaga', 2)->order_by('id_transaksi', 'DESC')->limit(1)->get('transaksi_dermaga')->row();
        $this->db->where('id_transaksi', $row->id_transaksi);
        $this->db->update('transaksi_dermaga', $updateData);
    
        // Simpan ke log file (opsional)
        $logLine = date("Y-m-d H:i:s") . " - Updated Data: " . json_encode($updateData) . "\n";
        $logPath = FCPATH . 'application/logs/flow_log2.txt';
        file_put_contents($logPath, $logLine, FILE_APPEND);
    
        $response = [
            'status' => 'success',
            'updated_data' => $updateData
        ];
    
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
}
