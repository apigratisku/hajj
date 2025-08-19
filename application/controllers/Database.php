<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Database extends CI_Controller {

    private function is_ajax_request() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function __construct() {
        parent::__construct();
        $this->load->model('transaksi_model');
        $this->load->model('peserta_reject_model');
        $this->load->model('user_model');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('telegram_notification');
        $this->load->helper('url');
        $this->load->library('excel');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            // If it's an AJAX request, return JSON error instead of redirecting
            if ($this->is_ajax_request()) {
                $this->output->set_status_header(401);
                $this->output->set_output(json_encode(['success' => false, 'message' => 'Session expired. Please login again.']));
                return;
            }
            redirect('auth');
        }
    }

    public function index() {
        $this->load->model('transaksi_model');
        $data['title'] = 'Data Peserta';
        // Get filters from GET parameters and clean them
        $filters = [
            'nama' => trim($this->input->get('nama')),
            'nomor_paspor' => trim($this->input->get('nomor_paspor')),
            'no_visa' => trim($this->input->get('no_visa')),
            'flag_doc' => trim($this->input->get('flag_doc')),
            'tanggaljam' => trim($this->input->get('tanggaljam')),
            'status' => trim($this->input->get('status')),
            'gender' => trim($this->input->get('gender'))
        ];
        
        // Remove empty filters to avoid unnecessary WHERE clauses
        $filters = array_filter($filters, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Pagination settings
        $per_page = 25;
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $offset = ($page - 1) * $per_page;
        
        // Get data
        $data['peserta'] = $this->transaksi_model->get_paginated_filtered($per_page, $offset, $filters);
        // Provide flag_doc options for filter select
        $data['flag_doc_list'] = $this->transaksi_model->get_unique_flag_doc();
        $data['tanggaljam_list'] = $this->transaksi_model->get_unique_tanggaljam();
   
        
        // Get total count for pagination
        $total_rows = $this->transaksi_model->count_filtered($filters);
        
        // Load pagination library
        $this->load->library('pagination');
        
        // Build base URL with current filters
        $base_url = base_url('database/index');
        $query_params = [];
        
        // Preserve current filters in pagination links
        if (!empty($filters['nama'])) {
            $query_params['nama'] = $filters['nama'];
        }
        if (!empty($filters['nomor_paspor'])) {
            $query_params['nomor_paspor'] = $filters['nomor_paspor'];
        }
        if (!empty($filters['no_visa'])) {
            $query_params['no_visa'] = $filters['no_visa'];
        }
        if (!empty($filters['flag_doc'])) {
            $query_params['flag_doc'] = $filters['flag_doc'];
        }
        if (!empty($filters['tanggaljam'])) {
            $query_params['tanggaljam'] = $filters['tanggaljam'];
        }
        if (!empty($filters['status'])) {
            $query_params['status'] = $filters['status'];
        }
        if (!empty($filters['gender'])) {
            $query_params['gender'] = $filters['gender'];
        }
        
        // Build query string
        if (!empty($query_params)) {
            $base_url .= '?' . http_build_query($query_params);
        }
        
        $config['base_url'] = $base_url;
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['use_page_numbers'] = TRUE;
        $config['num_links'] = 5;
        
        // Enhanced Pagination styling
        $config['full_tag_open'] = '<nav aria-label="Data navigation"><ul class="pagination pagination-custom justify-content-center">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['anchor_class'] = 'page-link';
        $config['next_link'] = '<i class="fas fa-chevron-right"></i>';
        $config['prev_link'] = '<i class="fas fa-chevron-left"></i>';
        $config['first_link'] = '<i class="fas fa-angle-double-left"></i>';
        $config['last_link'] = '<i class="fas fa-angle-double-right"></i>';
        

        
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        
        // Pass pagination info to view
        $data['total_rows'] = $total_rows;
        $data['per_page'] = $per_page;
        $data['current_page'] = $page;
        $data['offset'] = $offset;
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('database/index', $data);
        $this->load->view('templates/footer');
    }
    public function index2() {
        $data['title'] = 'Data Peserta';
        $data['peserta'] = $this->transaksi_model->get_all();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('database/index2', $data);
        $this->load->view('templates/footer');
    }
    public function delete($id) {
        // Get peserta data before deletion
        $peserta = $this->transaksi_model->get_by_id($id);
        
        if ($peserta) {
            // Delete barcode file if exists
            if (!empty($peserta->barcode)) {
                $this->delete_barcode_file($peserta->barcode);
            }
        }
        
        // Kirim notifikasi Telegram untuk delete data peserta
        if ($peserta) {
            $this->telegram_notification->peserta_crud_notification('delete', $peserta->nama, 'ID: ' . $id);
        }
        
        $this->transaksi_model->delete($id);
        
        // Check if redirect URL is provided
        $redirect_url = $this->input->get('redirect');
        if ($redirect_url) {
            // Decode the redirect URL
            $redirect_url = urldecode($redirect_url);
            
            // Validate that the redirect URL is safe (only allow redirect to our own domain)
            if (strpos($redirect_url, base_url()) === 0 || strpos($redirect_url, '/database/') === 0) {
                redirect($redirect_url);
            }
        }
        
        // Fallback: Redirect back to previous page with filters
        $redirect_url = $this->get_redirect_url_with_filters();
        redirect($redirect_url);
    }   
    
    public function download($id) {
        $peserta = $this->transaksi_model->get_by_id($id);
        
        if (!$peserta) {
            $this->session->set_flashdata('error', 'Data peserta tidak ditemukan');
            redirect('database');
        }
        
        // Create PDF
        $this->load->library('pdf');
        
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        
        // Header
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'LAPORAN DATA PESERTA', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Content
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, 'ID Agent:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $transaksi->agent_id, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, 'Nama Peserta:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $transaksi->nama, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, 'Nomor Passpor:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $transaksi->no, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, 'Dermaga:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Dermaga ' . $transaksi->dermaga, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, 'Waktu Mulai Sandar:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $transaksi->waktu_mulai_sandar, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, 'Waktu Selesai Sandar:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $transaksi->waktu_selesai_sandar, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, 'Volume Air Tawar:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $transaksi->volume_total . ' Liter', 0, 1);
        
        $pdf->Output('Transaksi_' . $transaksi->kode_transaksi . '.pdf', 'D');
    }
    
    public function print_laporan($id) {
        $transaksi = $this->transaksi_model->get_by_id($id);
        
        if (!$transaksi) {
            $this->session->set_flashdata('error', 'Data transaksi tidak ditemukan');
            redirect('database');
        }
        
        $data['title'] = 'Print Laporan Transaksi';
        $data['transaksi'] = $transaksi;
        
        // Load print view
        $this->load->view('database/print_laporan', $data);
    }

    public function edit($id) {
        // Load required models
        $this->load->model('transaksi_model');
        
        // Get peserta data
        $data['peserta'] = $this->transaksi_model->get_by_id($id);
        if (!$data['peserta']) {
            $this->session->set_flashdata('error', 'Data peserta tidak ditemukan');
            redirect('database');
        }
        
        $data['title'] = 'Edit Data Peserta';
        
        // Load view
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('database/edit', $data);
        $this->load->view('templates/footer');
    }
    
    public function update($id) {
        $current_peserta = $this->transaksi_model->get_by_id($id);
        
        if (!$current_peserta) {
            $this->session->set_flashdata('error', 'Data peserta tidak ditemukan');
            redirect('database');
        }
        
        $this->form_validation->set_rules('nama', 'Nama Peserta', 'required');
        $this->form_validation->set_rules('nomor_paspor', 'Nomor Paspor', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            // Check if barcode field is being cleared (file deletion)
            $new_barcode = trim($this->input->post('barcode')) ?: null;
            $old_barcode = $current_peserta->barcode;
            
            // If barcode is being cleared and there was a previous file, delete it
            if (empty($new_barcode) && !empty($old_barcode)) {
                $this->delete_barcode_file($old_barcode);
            }
            
            // Prepare data with proper handling of empty values
            $data = [
                'nama' => trim($this->input->post('nama')),
                'nomor_paspor' => trim($this->input->post('nomor_paspor')),
                'no_visa' => trim($this->input->post('no_visa')) ?: null,
                'tgl_lahir' => $this->input->post('tgl_lahir') ? $this->input->post('tgl_lahir') : null,
                'password' => trim($this->input->post('password')),
                'nomor_hp' => trim($this->input->post('nomor_hp')) ?: null,
                'email' => trim($this->input->post('email')) ?: null,
                'barcode' => $new_barcode,
                'gender' => $this->input->post('gender') ?: null,
                'status' => $this->input->post('status') !== null ? $this->input->post('status', true) : null,
                'tanggal' => $this->input->post('tanggal') ?: null,
                'jam' => $this->input->post('jam') ?: null,
                'flag_doc' => trim($this->input->post('flag_doc')) ?: null,
                'history_update' => $this->session->userdata('user_id') ?: null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Debug: Log the data being updated
            log_message('debug', 'Updating peserta ID: ' . $id . ' with data: ' . json_encode($data));
            
            try {
                $result = $this->transaksi_model->update($id, $data);
                
                if ($result) {
                    // Kirim notifikasi Telegram untuk update data peserta
                    $this->telegram_notification->peserta_crud_notification('update', $data['nama'], 'ID: ' . $id);
                    
                    $this->session->set_flashdata('success', 'Data peserta berhasil diperbarui');
                    
                    // Redirect back to previous page with filters
                    $redirect_url = $this->get_redirect_url_with_filters();
                    redirect($redirect_url);
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui data peserta. Silakan coba lagi.');
                    $this->edit($id);
                }
            } catch (Exception $e) {
                log_message('error', 'Exception during update: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
                $this->edit($id);
            }
            
            redirect('database');
        }
    }

    public function update_ajax($id) {
        // Check if it's an AJAX request
        if (!$this->is_ajax_request()) {
            $this->output->set_status_header(400);
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid request']));
            return;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        
        if (!$input) {
            $this->output->set_status_header(400);
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid input data']));
            return;
        }
        
        // For inline mobile edits, allow partial updates (no hard required fields)
        
        // Check if peserta exists
        $current_peserta = $this->transaksi_model->get_by_id($id);
        if (!$current_peserta) {
            $this->output->set_status_header(404);
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Data peserta tidak ditemukan']));
            return;
        }
        
        // Prepare data for update only for fields provided
        $allowedFields = ['nama','flag_doc','nomor_paspor','no_visa','tgl_lahir','password','nomor_hp','email','barcode','gender','status','tanggal','jam'];
        $data = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $value = $input[$field];
        
                if ($field === 'tgl_lahir' && empty($value)) {
                    $data[$field] = null;
                } 
                // ✅ Khusus field yang boleh bernilai "0", jangan pakai ?: null
                elseif ($field === 'status') {
                    $data[$field] = $value; // Biarkan "0", "1", "2" tetap masuk
                }
                // Untuk field lain: tetap pakai trim dan null jika kosong
                else {
                    $data[$field] = trim($value) ?: null;
                }
            }
        }
        
        // Add system fields
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['history_update'] = $this->session->userdata('user_id') ?: null;
        
        // Debug: Log the data being updated
        log_message('debug', 'Database update_ajax - Updating peserta ID: ' . $id . ' with data: ' . json_encode($data));
        log_message('debug', 'Database update_ajax - Raw input: ' . json_encode($input));
        log_message('debug', 'Database update_ajax - Barcode value: ' . (isset($data['barcode']) ? $data['barcode'] : 'NOT SET'));
        log_message('debug', 'Database update_ajax - Allowed fields: ' . json_encode($allowedFields));
        log_message('debug', 'Database update_ajax - History update value: ' . (isset($data['history_update']) ? $data['history_update'] : 'NOT SET'));
        log_message('debug', 'Database update_ajax - User ID from session: ' . $this->session->userdata('user_id'));
       
        
        try {
            // Update data
            $result = $this->transaksi_model->update($id, $data);
            
            if ($result) {
                // Kirim notifikasi Telegram untuk update data peserta via AJAX
                $peserta_name = isset($data['nama']) ? $data['nama'] : $current_peserta->nama;
                $this->telegram_notification->peserta_crud_notification('update', $peserta_name, 'ID: ' . $id);
                
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']));
            } else {
                $this->output->set_status_header(500);
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode(['success' => false, 'message' => 'Gagal memperbarui data. Silakan coba lagi.']));
            }
        } catch (Exception $e) {
            log_message('error', 'Exception in Database update_ajax: ' . $e->getMessage());
            $this->output->set_status_header(500);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()]));
        }
    }

    public function get_transaksi_data() {
        // Pastikan ini adalah request AJAX
        if (!$this->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $page = $this->input->get('page') ? $this->input->get('page') : 0;
        $per_page = 10;

        $data = $this->transaksi_model->get_paginated($per_page, $page);
        $total = $this->transaksi_model->count_all();

        if ($data) {
            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data found'
            ]);
        }
    }

    public function export() {
        $this->load->model('transaksi_model');
        
        // Get filters from GET parameters
        $filters = [
            'nama' => $this->input->get('nama'),
            'nomor_paspor' => $this->input->get('nomor_paspor'),
            'no_visa' => $this->input->get('no_visa'),
            'flag_doc' => $this->input->get('flag_doc'),
            'status' => $this->input->get('status')
        ];
        
        // Handle null flag_doc (for data without flag_doc)
        if (isset($filters['flag_doc']) && $filters['flag_doc'] === 'null') {
            $filters['flag_doc'] = null;
        }
        
        // Get data
        $peserta = $this->transaksi_model->get_paginated_filtered(1000, 0, $filters);
        
        // Get format from parameters
        $format = $this->input->get('format');
        
        if ($format === 'pdf') {
            $this->export_pdf($peserta, $filters);
        } else {
            $this->export_excel($peserta, $filters);
        }
    }
    
    private function export_excel($peserta, $filters) {
        // Check if PHPExcel library exists
        $phpexcel_path = APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        if (!file_exists($phpexcel_path)) {
            $this->session->set_flashdata('error', 'Library PHPExcel tidak ditemukan. Silakan install library terlebih dahulu.');
            redirect('database');
        }
        
        // Load PHPExcel library
        require_once $phpexcel_path;
        
        try {
            $excel = new PHPExcel();
            
            // Set document properties
            $excel->getProperties()
                ->setCreator("Hajj System")
                ->setLastModifiedBy("Hajj System")
                ->setTitle("Database Peserta")
                ->setSubject("Data Peserta")
                ->setDescription("Export data peserta dari sistem hajj");
            
            // Set column headers
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Nama Peserta')
                ->setCellValue('B1', 'No Paspor')
                ->setCellValue('C1', 'No Visa')
                ->setCellValue('D1', 'Tgl Lahir')
                ->setCellValue('E1', 'Password')
                ->setCellValue('F1', 'No HP')
                ->setCellValue('G1', 'Email')
                ->setCellValue('H1', 'Barcode')
                ->setCellValue('I1', 'Gender')
                ->setCellValue('J1', 'Tanggal')
                ->setCellValue('K1', 'Jam')
                ->setCellValue('L1', 'Status')
                ->setCellValue('M1', 'Flag Dokumen');
            
            // Set column widths
            $excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
            $excel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
            $excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
            $excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
            // Style header row
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => '8B4513'],
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
            ];
            
            $excel->getActiveSheet()->getStyle('A1:M1')->applyFromArray($headerStyle);
            
            // Populate data
            $row = 2;
            $on_target_count = 0;
            $already_count = 0;
            $done_count = 0;
            
            foreach ($peserta as $p) {
                $status = '';
                if ($p->status == 0) {
                    $status = 'On Target';
                    $on_target_count++;
                } elseif ($p->status == 1) {
                    $status = 'Already';
                    $already_count++;
                } elseif ($p->status == 2) {
                    $status = 'Done';
                    $done_count++;
                }
                
                $gender = '';
                if ($p->gender == 'L') {
                    $gender = 'Laki-laki';
                } elseif ($p->gender == 'P') {
                    $gender = 'Perempuan';
                }
                
                $excel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $row, $p->nama)
                    ->setCellValue('B' . $row, $p->nomor_paspor)
                    ->setCellValue('C' . $row, $p->no_visa ? $p->no_visa : '-')
                    ->setCellValue('D' . $row, $p->tgl_lahir ? date('d/m/Y', strtotime($p->tgl_lahir)) : '-')
                    ->setCellValue('E' . $row, $p->password)
                    ->setCellValue('F' . $row, $p->nomor_hp ? $p->nomor_hp : '-')
                    ->setCellValue('G' . $row, $p->email ? $p->email : '-')
                    ->setCellValue('H' . $row, $p->barcode ?: '-')
                    ->setCellValue('I' . $row, $gender ?: '-')
                    ->setCellValue('J' . $row, $p->tanggal ?: '-')
                    ->setCellValue('K' . $row, $p->jam ?: '-')
                    ->setCellValue('L' . $row, $status)
                    ->setCellValue('M' . $row, $p->flag_doc ?: '-');
                $row++;
            }
            
            // Add summary statistics 3 rows below the last data
            $summary_row = $row + 3;
            $total_count = count($peserta);
            
            // Add summary headers
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A' . $summary_row, 'RINGKASAN STATUS PESERTA')
                ->setCellValue('B' . $summary_row, '');
            
            $summary_row++;
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A' . $summary_row, 'Status')
                ->setCellValue('B' . $summary_row, 'Jumlah')
                ;
            
            $summary_row++;
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A' . $summary_row, 'On Target')
                ->setCellValue('B' . $summary_row, $on_target_count);
            
            $summary_row++;
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A' . $summary_row, 'Already')
                ->setCellValue('B' . $summary_row, $already_count);
            
            $summary_row++;
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A' . $summary_row, 'Done')
                ->setCellValue('B' . $summary_row, $done_count);
            
            $summary_row++;
            $excel->setActiveSheetIndex(0)
                ->setCellValue('A' . $summary_row, 'TOTAL')
                ->setCellValue('B' . $summary_row, $total_count);
            
            // Style data rows
            $dataStyle = [
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            if ($row > 2) {
                $excel->getActiveSheet()->getStyle('A2:M' . ($row - 1))->applyFromArray($dataStyle);
            }
            
            // Style summary section
            $summaryStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => '2E8B57'],
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
            ];
            
            $summaryDataStyle = [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F0F8FF'],
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            // Apply summary styles
            $excel->getActiveSheet()->getStyle('A' . ($row + 3) . ':B' . ($row + 3))->applyFromArray($summaryStyle);
            $excel->getActiveSheet()->getStyle('A' . ($row + 4) . ':B' . ($row + 4))->applyFromArray($summaryStyle);
            $excel->getActiveSheet()->getStyle('A' . ($row + 5) . ':B' . ($row + 8))->applyFromArray($summaryDataStyle);
            
            // Set filename
            $filename = 'Database_Peserta_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Create Excel writer
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Error saat export Excel: ' . $e->getMessage());
            redirect('database');
        }
    }
    
    private function export_pdf($peserta, $filters) {
        try {
            // Load TCPDF library
            $this->load->library('pdf');
            
            // Check if TCPDF is available
            if (!class_exists('TCPDF')) {
                throw new Exception('TCPDF library tidak tersedia. Silakan install TCPDF terlebih dahulu.');
            }
            // Create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('Hajj System');
            $pdf->SetAuthor('Hajj System');
            $pdf->SetTitle('Database Peserta');
            $pdf->SetSubject('Data Peserta');
            $pdf->SetKeywords('hajj, peserta, database');
            
            // Set default header data
            $pdf->SetHeaderData('', 0, 'DAFTAR PESERTA KUNJUNGAN', 'Export Data Peserta - ' . date('d/m/Y H:i:s'));
            
            // Set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // Set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
            // Set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            // Set landscape orientation
            $pdf->setPageOrientation('L');
            
            // Add a page
            $pdf->AddPage();
            
            // Set font
            $pdf->SetFont('helvetica', '', 8);
            
            // Calculate statistics
            $on_target_count = 0;
            $already_count = 0;
            $done_count = 0;
            
            foreach ($peserta as $p) {
                if ($p->status == 0) $on_target_count++;
                elseif ($p->status == 1) $already_count++;
                elseif ($p->status == 2) $done_count++;
            }
            
            $total_count = count($peserta);
            
            // Create table header
            $html = '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%; font-size: 8px;">
                <thead>
                    <tr style="background-color: #8B4513; color: white; font-weight: bold; text-align: center;">
                        <th width="15%">Nama Peserta</th>
                        <th width="10%">No Paspor</th>
                        <th width="8%">No Visa</th>
                        <th width="8%">Tgl Lahir</th>
                        <th width="8%">Password</th>
                        <th width="10%">No HP</th>
                        <th width="12%">Email</th>
                        <th width="6%">Barcode</th>
                        <th width="6%">Gender</th>
                        <th width="8%">Tanggal</th>
                        <th width="6%">Jam</th>
                        <th width="8%">Status</th>
                        <th width="5%">Flag</th>
                    </tr>
                </thead>
                <tbody>';
            
            // Add data rows
            foreach ($peserta as $p) {
                $status = '';
                if ($p->status == 0) {
                    $status = 'On Target';
                } elseif ($p->status == 1) {
                    $status = 'Already';
                } elseif ($p->status == 2) {
                    $status = 'Done';
                }
                
                $gender = '';
                if ($p->gender == 'L') {
                    $gender = 'Laki-laki';
                } elseif ($p->gender == 'P') {
                    $gender = 'Perempuan';
                }
                
                $html .= '<tr>
                    <td>' . htmlspecialchars($p->nama) . '</td>
                    <td>' . htmlspecialchars($p->nomor_paspor) . '</td>
                    <td>' . htmlspecialchars($p->no_visa ?: '-') . '</td>
                    <td>' . ($p->tgl_lahir ? date('d/m/Y', strtotime($p->tgl_lahir)) : '-') . '</td>
                    <td>' . htmlspecialchars($p->password) . '</td>
                    <td>' . htmlspecialchars($p->nomor_hp ?: '-') . '</td>
                    <td>' . htmlspecialchars($p->email ?: '-') . '</td>
                    <td>' . htmlspecialchars($p->barcode ?: '-') . '</td>
                    <td>' . htmlspecialchars($gender ?: '-') . '</td>
                    <td>' . htmlspecialchars($p->tanggal ?: '-') . '</td>
                    <td>' . htmlspecialchars($p->jam ?: '-') . '</td>
                    <td>' . htmlspecialchars($status) . '</td>
                    <td>' . htmlspecialchars($p->flag_doc ?: '-') . '</td>
                </tr>';
            }
            
            // Add summary section
            $html .= '</tbody></table>';
            
            // Add summary statistics
            $html .= '<div class="summary-section">
                <table class="summary-table" border="1" cellpadding="6" cellspacing="0">
                    <tr style="background-color: #2E8B57; color: white; font-weight: bold; text-align: center;">
                        <td colspan="2">RINGKASAN STATUS PESERTA</td>
                    </tr>
                    <tr style="background-color: #2E8B57; color: white; font-weight: bold; text-align: center;">
                        <td>Status</td>
                        <td>Jumlah</td>
                    </tr>
                    <tr style="background-color: #F0F8FF; font-weight: bold;">
                        <td>On Target</td>
                        <td>' . $on_target_count . '</td>
                    </tr>
                    <tr style="background-color: #F0F8FF; font-weight: bold;">
                        <td>Already</td>
                        <td>' . $already_count . '</td>
                    </tr>
                    <tr style="background-color: #F0F8FF; font-weight: bold;">
                        <td>Done</td>
                        <td>' . $done_count . '</td>
                    </tr>
                    <tr style="background-color: #F0F8FF; font-weight: bold;">
                        <td><strong>TOTAL</strong></td>
                        <td><strong>' . $total_count . '</strong></td>
                    </tr>
                </table>
            </div>';
            
            // Print text using writeHTMLCell()
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Set filename
            $filename = 'Database_Peserta_' . date('Y-m-d_H-i-s') . '.pdf';
            
            // Output PDF
            $pdf->Output($filename, 'D');
            exit;
            
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Error saat export PDF: ' . $e->getMessage());
            redirect('database');
        }
    }

    public function tambah() {
        $data['title'] = 'Tambah Data Peserta';
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('database/tambah', $data);
        $this->load->view('templates/footer');
    }
    
    public function store() {
        $this->form_validation->set_rules('nama', 'Nama Peserta', 'required');
        $this->form_validation->set_rules('nomor_paspor', 'Nomor Paspor', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->tambah();
        } else {
            $data = [
                'nama' => trim($this->input->post('nama')),
                'nomor_paspor' => trim($this->input->post('nomor_paspor')),
                'no_visa' => trim($this->input->post('no_visa')) ?: null,
                'tgl_lahir' => $this->input->post('tgl_lahir') ? $this->input->post('tgl_lahir') : null,
                'password' => trim($this->input->post('password')),
                'nomor_hp' => trim($this->input->post('nomor_hp')) ?: null,
                'email' => trim($this->input->post('email')) ?: null,
                'barcode' => trim($this->input->post('barcode')) ?: null,
                'gender' => $this->input->post('gender') ?: null,
                'status' => $this->input->post('status') !== '' ? $this->input->post('status') : 0,
                'tanggal' => $this->input->post('tanggal') ?: null,
                'jam' => $this->input->post('jam') ?: null,
                'flag_doc' => trim($this->input->post('flag_doc')) ?: null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            try {
                $result = $this->transaksi_model->insert($data);
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Data peserta berhasil ditambahkan');
                    redirect('database');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan data peserta. Silakan coba lagi.');
                    $this->tambah();
                }
            } catch (Exception $e) {
                log_message('error', 'Exception during insert: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat menambahkan data: ' . $e->getMessage());
                $this->tambah();
            }
            
            redirect('database');
        }
    }

    private function insert_reject_data($reject_data, $row) {
        try {
            $this->peserta_reject_model->insert($reject_data);
            return true;
        } catch (Exception $e) {
            log_message('error', 'Failed to insert reject data for row ' . $row . ': ' . $e->getMessage());
            return false;
        }
    }

    public function import() {
        $data['title'] = 'Import Data Peserta';
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('database/import', $data);
        $this->load->view('templates/footer');
    }

    public function process_import() {
        // Set error reporting to prevent database errors from showing
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        ini_set('display_errors', 0);
        
        // Check if file was uploaded
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'File tidak ditemukan atau terjadi error saat upload');
            redirect('database/import');
        }
    
        $file = $_FILES['excel_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file extension
        if (!in_array($file_ext, ['xls', 'xlsx'])) {
            $this->session->set_flashdata('error', 'File harus berformat Excel (.xls atau .xlsx)');
            redirect('database/import');
        }
    
        // Load PHPExcel library
        require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        
        try {
            $inputFileName = $file['tmp_name'];
            
            if ($file_ext == 'xlsx') {
                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            } else {
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
            }
            
            $objPHPExcel = $objReader->load($inputFileName);
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            $rejected_data = [];
            $successful_data = []; // Array untuk menyimpan data yang berhasil di import
    
            // Fungsi konversi tanggal seragam
            $convertDate = function($dateValue) {
                $bulan_map = [
                    'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
                    'Mei' => '05', 'Jun' => '06', 'Jul' => '07', 'Agu' => '08',
                    'Sep' => '09', 'Okt' => '10', 'Nov' => '11', 'Des' => '12'
                ];
                if (empty($dateValue)) return null;
                if (is_numeric($dateValue)) {
                    $phpDate = PHPExcel_Shared_Date::ExcelToPHP($dateValue);
                    return date('Y-m-d', $phpDate);
                } else {
                    $dateValue = trim($dateValue);
                    // Format dd/MM/yyyy
                    if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $dateValue)) {
                        $dt = DateTime::createFromFormat('d/m/Y', $dateValue);
                        return $dt ? $dt->format('Y-m-d') : null;
                    }
                    // Format dd-MMM-yyyy (Indonesia)
                    elseif (preg_match('/^\d{1,2}-[A-Za-z]{3}-\d{4}$/', $dateValue)) {
                        list($d, $m, $y) = explode('-', $dateValue);
                        $m = ucfirst(strtolower($m));
                        if (isset($bulan_map[$m])) {
                            return sprintf('%04d-%02d-%02d', $y, $bulan_map[$m], $d);
                        }
                    }
                    // Fallback: parse otomatis
                    $parsed_date = date_parse($dateValue);
                    if ($parsed_date['error_count'] == 0 && checkdate($parsed_date['month'], $parsed_date['day'], $parsed_date['year'])) {
                        return date('Y-m-d', strtotime($dateValue));
                    }
                }
                return null;
            };
    
            $convertTime = function($timeValue) {
                if ($timeValue === '' || $timeValue === null) return null;
                
                // Clean the input
                $timeValue = trim($timeValue);
                
                // Handle Excel time format "04.00" -> "04:00"
                if (preg_match('/^\d{1,2}\.\d{2}$/', $timeValue)) {
                    // Direct conversion from "04.00" to "04:00"
                    return str_replace('.', ':', $timeValue);
                }
                
                // Handle Excel time format "4.00" -> "04:00" (single digit hour)
                if (preg_match('/^\d{1}\.\d{2}$/', $timeValue)) {
                    $parts = explode('.', $timeValue);
                    $hour = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                    $minute = $parts[1];
                    return $hour . ':' . $minute;
                }
                
                // Handle Excel time format "4.5" -> "04:30" (decimal format)
                if (preg_match('/^\d{1,2}\.\d{1}$/', $timeValue)) {
                    $parts = explode('.', $timeValue);
                    $hour = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                    $decimal = $parts[1];
                    $minute = str_pad($decimal * 6, 2, '0', STR_PAD_LEFT); // Convert decimal to minutes
                    return $hour . ':' . $minute;
                }
                
                // Handle Excel time format "4.50" -> "04:30" (decimal format with 2 digits)
                if (preg_match('/^\d{1,2}\.\d{2}$/', $timeValue)) {
                    $parts = explode('.', $timeValue);
                    $hour = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                    $decimal = $parts[1];
                    // If decimal is 50, it means 30 minutes
                    if ($decimal == '50') {
                        $minute = '30';
                    } else {
                        $minute = str_pad($decimal, 2, '0', STR_PAD_LEFT);
                    }
                    return $hour . ':' . $minute;
                }
                
                // Handle Excel numeric time values (Excel stores time as decimal)
                if (is_numeric($timeValue)) {
                    // If it's a decimal between 0 and 1, it's a time value
                    if ($timeValue >= 0 && $timeValue < 1) {
                        $totalMinutes = round($timeValue * 1440); // 24 * 60 minutes
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                        return sprintf('%02d:%02d', $hours, $minutes);
                    }
                    // If it's greater than 1, it might have a date component
                    else if ($timeValue >= 1) {
                        $timeValue = $timeValue - floor($timeValue);
                        $totalMinutes = round($timeValue * 1440);
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                        return sprintf('%02d:%02d', $hours, $minutes);
                    }
                }
                
                // Handle various text formats
                $timeValue = str_replace(['.', ','], ':', $timeValue);
                
                // Handle "4:00" -> "04:00" (single digit hour)
                if (preg_match('/^\d{1}:\d{2}$/', $timeValue)) {
                    $parts = explode(':', $timeValue);
                    $hour = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                    $minute = $parts[1];
                    return $hour . ':' . $minute;
                }
                
                // Handle "04:00" format (already correct)
                if (preg_match('/^\d{2}:\d{2}$/', $timeValue)) {
                    return $timeValue;
                }
                
                // Try to parse with date_parse as fallback
                $parsed_time = date_parse($timeValue);
                if ($parsed_time['error_count'] == 0 && $parsed_time['hour'] !== false) {
                    return sprintf('%02d:%02d', $parsed_time['hour'], $parsed_time['minute']);
                }
                
                return null;
            };
            
            
            
            
            // Truncate tabel peserta_reject sebelum memulai import
            try {
                $this->peserta_reject_model->delete_all();
            } catch (Exception $e) {
                log_message('error', 'Failed to truncate peserta_reject table: ' . $e->getMessage());
                // Continue with import even if truncate fails
            }
            
            // Set database error handling to prevent fatal errors
            $this->db->db_debug = FALSE;
            
            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $nama_peserta = trim($sheet->getCellByColumnAndRow(0, $row)->getValue());
                $nomor_paspor = trim($sheet->getCellByColumnAndRow(1, $row)->getValue());
                $no_visa = trim($sheet->getCellByColumnAndRow(2, $row)->getValue());
                $tgl_lahir = trim($sheet->getCellByColumnAndRow(3, $row)->getValue());
                $password = trim($sheet->getCellByColumnAndRow(4, $row)->getValue());
                $nomor_hp = trim($sheet->getCellByColumnAndRow(5, $row)->getValue());
                $email = trim($sheet->getCellByColumnAndRow(6, $row)->getValue());
                $gender = trim($sheet->getCellByColumnAndRow(8, $row)->getValue());
                $status_Cek = trim($sheet->getCellByColumnAndRow(7, $row)->getValue());
                if($status_Cek == 'On Target'){
                    $status = 0;
                }elseif($status_Cek == 'Already'){
                    $status = 1;
                }elseif($status_Cek == 'Done'){
                    $status = 2;
                }elseif($status_Cek == 'Done!'){
                    $status = 2;
                }
                // Ambil tanggal & jam dari kolom Excel (misal index 8 & 9)
                $tanggal_excel = trim($sheet->getCellByColumnAndRow(9, $row)->getValue());
                $jam_excel = trim($sheet->getCellByColumnAndRow(10, $row)->getValue());
                $flag_doc = trim($sheet->getCellByColumnAndRow(11, $row)->getValue());
                
                // Skip empty rows
                if (empty($nama_peserta) && empty($nomor_paspor)) {
                    continue;
                }
                
                // Validate required fields
                if (empty($nama_peserta) || empty($nomor_paspor) || empty($password)) {
                    $errors[] = "Row $row: Nama Peserta, Nomor Paspor, dan Password harus diisi";
                    $error_count++;
                    
                    // Simpan data yang gagal ke tabel peserta_reject
                    $reject_data = [
                        'nama' => $nama_peserta,
                        'nomor_paspor' => $nomor_paspor,
                        'no_visa' => $no_visa ?: null,
                        'tgl_lahir' => '1900-01-01', // Default date untuk menghindari null
                        'password' => $password,
                        'nomor_hp' => $nomor_hp ?: null,
                        'email' => $email ?: null,
                        'gender' => 'L', // Default gender
                        'status' => 0,
                        'tanggal' => '1900-01-01', // Default date untuk menghindari null
                        'jam' => '00:00:00', // Default time untuk menghindari null
                        'flag_doc' => $flag_doc,
                        'reject_reason' => "Nama Peserta, Nomor Paspor, atau Password sudah ada format yang salah",
                        'row_number' => $row
                    ];
                    
                    if ($this->insert_reject_data($reject_data, $row)) {
                        $rejected_data[] = $reject_data;
                    }
                    continue;
                }
                

                
                // Process status
                $status_value = 0; // Default to 'On Target'
                if (!empty($status)) {
                    switch (strtolower($status)) {
                        case 'already':
                            $status_value = 1;
                            break;
                        case 'Done!':
                        case 'Done':    
                        case 'selesai':
                            $status_value = 2;
                            break;
                        default:
                            $status_value = 0;
                    }
                }
                
                // Process gender
                $gender_value = 'L';
                if (!empty($gender)) {
                    if (strtolower($gender) == 'p' || strtolower($gender) == 'perempuan' || strtolower($gender) == 'female') {
                        $gender_value = 'P';
                    }
                }else{
                    $gender_value = '';
                }
                
                // Konversi semua tanggal
                $tgl_lahir_value = $convertDate($tgl_lahir);
                $tanggal_value   = $convertDate($tanggal_excel);
                $jam_value       = $convertTime($jam_excel);

                // Validate email format - reject if contains double quotes
                if (!empty($email) && strpos($email, '"') !== false) {
                    $errors[] = "Row $row: Email '$email' mengandung tanda petik ganda yang tidak diperbolehkan";
                    $error_count++;
                    
                    // Simpan data yang gagal ke tabel peserta_reject
                    $reject_data = [
                        'nama' => $nama_peserta,
                        'nomor_paspor' => $nomor_paspor,
                        'no_visa' => $no_visa ?: null,
                        'tgl_lahir' => $tgl_lahir_value ?: '1900-01-01',
                        'password' => $password,
                        'nomor_hp' => $nomor_hp ?: null,
                        'email' => $email ?: null,
                        'gender' => $gender_value ?: 'L',
                        'status' => $status_value,
                        'tanggal' => $tanggal_value ?: '1900-01-01',
                        'jam' => $jam_value ?: '00:00:00',
                        'flag_doc' => $flag_doc,
                        'reject_reason' => "Email mengandung tanda petik ganda (\") yang tidak diperbolehkan",
                        'row_number' => $row
                    ];
                    
                    if ($this->insert_reject_data($reject_data, $row)) {
                        $rejected_data[] = $reject_data;
                    }
                    continue;
                }
    
                // Check if peserta already exists
                $existing_peserta = $this->transaksi_model->get_by_passport($nomor_paspor);
                if ($existing_peserta) {
                    $errors[] = "Row $row: Peserta dengan nomor paspor '$nomor_paspor' sudah ada";
                    $error_count++;
                    
                    // Simpan data yang gagal ke tabel peserta_reject
                    $reject_data = [
                        'nama' => $nama_peserta,
                        'nomor_paspor' => $nomor_paspor,
                        'no_visa' => $no_visa ?: null,
                        'tgl_lahir' => $tgl_lahir_value ?: '1900-01-01',
                        'password' => $password,
                        'nomor_hp' => $nomor_hp ?: null,
                        'email' => $email ?: null,
                        'gender' => $gender_value ?: 'L',
                        'status' => $status_value,
                        'tanggal' => $tanggal_value ?: '1900-01-01',
                        'jam' => $jam_value ?: '00:00:00',
                        'flag_doc' => $flag_doc,
                        'reject_reason' => "Nomor paspor '$nomor_paspor' sudah ada dalam database",
                        'row_number' => $row
                    ];
                    
                    if ($this->insert_reject_data($reject_data, $row)) {
                        $rejected_data[] = $reject_data;
                    }
                    continue;
                }
                
                // Insert peserta data
                $peserta_data = [
                    'nama' => $nama_peserta,
                    'nomor_paspor' => $nomor_paspor,
                    'no_visa' => $no_visa ?: null,
                    'tgl_lahir' => $tgl_lahir_value,
                    'password' => $password,
                    'nomor_hp' => $nomor_hp ?: null,
                    'email' => $email ?: null,
                    'gender' => $gender_value,
                    'status' => $status_value,
                    'tanggal' => $tanggal_value,
                    'jam' => $jam_value,
                    'flag_doc' => $flag_doc,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                try {
                    $result = $this->transaksi_model->insert($peserta_data);
                    if ($result) {
                        $success_count++;
                        
                        // Simpan data yang berhasil di import
                        $successful_data[] = [
                            'nama' => $nama_peserta,
                            'nomor_paspor' => $nomor_paspor,
                            'no_visa' => $no_visa ?: '',
                            'tgl_lahir' => $tgl_lahir_value ?: '',
                            'password' => $password,
                            'nomor_hp' => $nomor_hp ?: '',
                            'email' => $email ?: '',
                            'gender' => $gender_value,
                            'status' => $status_value,
                            'tanggal' => $tanggal_value ?: '',
                            'jam' => $jam_value ?: '',
                            'flag_doc' => $flag_doc,
                            'row_number' => $row
                        ];
                    } else {
                        $errors[] = "Row $row: Gagal menyimpan data peserta";
                        $error_count++;
                        
                        // Simpan data yang gagal ke tabel peserta_reject
                        $reject_data = [
                            'nama' => $nama_peserta,
                            'nomor_paspor' => $nomor_paspor,
                            'no_visa' => $no_visa ?: null,
                            'tgl_lahir' => $tgl_lahir_value ?: '1900-01-01',
                            'password' => $password,
                            'nomor_hp' => $nomor_hp ?: null,
                            'email' => $email ?: null,
                            'gender' => $gender_value ?: 'L',
                            'status' => $status_value,
                            'tanggal' => $tanggal_value ?: '1900-01-01',
                            'jam' => $jam_value ?: '00:00:00',
                            'flag_doc' => $flag_doc,
                            'reject_reason' => "Gagal menyimpan data ke database",
                            'row_number' => $row
                        ];
                        
                        if ($this->insert_reject_data($reject_data, $row)) {
                            $rejected_data[] = $reject_data;
                        }
                    }
                } catch (Exception $e) {
                    log_message('error', 'Failed to insert peserta data for row ' . $row . ': ' . $e->getMessage());
                    
                    // Handle specific database errors gracefully
                    $error_message = $e->getMessage();
                    $reject_reason = "Error database: " . $error_message;
                    
                    // Check for duplicate entry errors
                    if (strpos($error_message, 'Duplicate entry') !== false) {
                        if (strpos($error_message, 'no_visa') !== false) {
                            $reject_reason = "Nomor visa sudah ada dalam database";
                        } elseif (strpos($error_message, 'nomor_paspor') !== false) {
                            $reject_reason = "Nomor paspor sudah ada dalam database";
                        } elseif (strpos($error_message, 'email') !== false) {
                            $reject_reason = "Email sudah ada dalam database";
                        } else {
                            $reject_reason = "Data duplikat ditemukan dalam database";
                        }
                    }
                    
                    $errors[] = "Row $row: " . $reject_reason;
                    $error_count++;
                    
                    // Simpan data yang gagal ke tabel peserta_reject
                    $reject_data = [
                        'nama' => $nama_peserta,
                        'nomor_paspor' => $nomor_paspor,
                        'no_visa' => $no_visa ?: null,
                        'tgl_lahir' => $tgl_lahir_value ?: '1900-01-01',
                        'password' => $password,
                        'nomor_hp' => $nomor_hp ?: null,
                        'email' => $email ?: null,
                        'gender' => $gender_value ?: 'L',
                        'status' => $status_value,
                        'tanggal' => $tanggal_value ?: '1900-01-01',
                        'jam' => $jam_value ?: '00:00:00',
                        'flag_doc' => $flag_doc,
                        'reject_reason' => $reject_reason,
                        'row_number' => $row
                    ];
                    
                    if ($this->insert_reject_data($reject_data, $row)) {
                        $rejected_data[] = $reject_data;
                    }
                }
            }
            
            // Set flash messages
            if ($success_count > 0) {
                // Kirim notifikasi Telegram untuk import berhasil
                $this->telegram_notification->import_export_notification('Import', $file['name'], $success_count, true);
                
                $this->session->set_flashdata('success', "Berhasil mengimport $success_count data peserta");
                
                // Simpan data yang berhasil di import ke session untuk download (menggunakan userdata agar tidak hilang)
                $this->session->set_userdata('successful_count', count($successful_data));
                $this->session->set_userdata('successful_data', $successful_data);
                
                // Console log untuk data yang berhasil di import
                log_message('info', 'Import successful: ' . $success_count . ' records imported successfully');
                foreach ($successful_data as $data) {
                    log_message('info', 'Successfully imported: ' . $data['nama'] . ' - ' . $data['nomor_paspor'] . ' (Row: ' . $data['row_number'] . ')');
                }
            }
            if ($error_count > 0) {
                // Kirim notifikasi Telegram untuk import gagal
                $this->telegram_notification->import_export_notification('Import', $file['name'], $error_count, false);
                
                $this->session->set_flashdata('error', "Gagal mengimport $error_count data. " . implode('; ', array_slice($errors, 0, 5)));
                
                // Simpan informasi data yang ditolak ke session untuk ditampilkan di halaman import
                $this->session->set_flashdata('rejected_count', count($rejected_data));
                $this->session->set_flashdata('rejected_data', $rejected_data);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Import error: ' . $e->getMessage());
            
            // Handle specific database errors gracefully
            $error_message = $e->getMessage();
            $user_friendly_message = 'Terjadi kesalahan saat memproses file. Silakan coba lagi atau hubungi administrator.';
            
            // Check for specific database errors
            if (strpos($error_message, 'Duplicate entry') !== false) {
                $user_friendly_message = 'Ditemukan data duplikat dalam file. Data yang duplikat akan disimpan dalam file terpisah. Silakan download data yang ditolak untuk melihat detailnya.';
            } elseif (strpos($error_message, 'MySQL') !== false || strpos($error_message, 'database') !== false) {
                $user_friendly_message = 'Terjadi kesalahan database. Silakan coba lagi atau hubungi administrator.';
            }
            
            $this->session->set_flashdata('error', $user_friendly_message);
        }
        
        // Ambil URL untuk redirect
        $redirect_back = $this->input->post('redirect_back') ?: 'database/import';
        redirect($redirect_back);
    }
    

    public function download_template() {
        // Load PHPExcel library
        require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Hajj System")
                                   ->setLastModifiedBy("Hajj System")
                                   ->setTitle("Template Import Data Peserta")
                                   ->setSubject("Template untuk import data peserta")
                                   ->setDescription("Template Excel untuk import data peserta ke sistem hajj");
        
        // Add header row
        $headers = [
            'Nama Peserta',
            'Nomor Paspor',
            'No Visa',
            'Tanggal Lahir',
            'Password',
            'No. HP',
            'Email',
            'Status',
            'Gender',
            'Tanggal',
            'Jam',
            'Flag Dokumen'
        ];
        
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        // Set header style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '8B4513'],
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
        ];

        // Add headers
        foreach ($headers as $col => $header) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($col);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->applyFromArray($headerStyle);
        }
        
        // Add sample data row
        $sampleData = [
            'Ahmad Hidayat',
            'A1234567',
            'V123456',
            '15/03/1990',
            'password123',
            '08123456789',
            'ahmad@email.com',
            'On Target',
            'L',
            '2025-01-01',
            '12:00',
            'Batch-001'
        ];
        
        foreach ($sampleData as $col => $data) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($col);
            $sheet->setCellValue($colLetter . '2', $data);
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        
        // Set sheet title
        $sheet->setTitle('Template Import Peserta');
        
        // Set active sheet index to the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        // Redirect output to client browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="template_import_peserta.xlsx"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function rejected_data() {
        $data['title'] = 'Data Import Ditolak';
        
        // Get filters from GET parameters
        $filters = [
            'nama' => trim($this->input->get('nama')),
            'nomor_paspor' => trim($this->input->get('nomor_paspor')),
            'no_visa' => trim($this->input->get('no_visa')),
            'flag_doc' => trim($this->input->get('flag_doc')),
            'tanggaljam' => trim($this->input->get('tanggaljam')),
            'status' => trim($this->input->get('status')),
            'gender' => trim($this->input->get('gender'))
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Pagination settings
        $per_page = 10;
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $offset = ($page - 1) * $per_page;
        
        // Get data
        $data['rejected_data'] = $this->peserta_reject_model->get_paginated_filtered($per_page, $offset, $filters);
        
        // Get total count for pagination
        $total_rows = $this->peserta_reject_model->count_filtered($filters);
        
        // Load pagination library
        $this->load->library('pagination');
        
        // Build base URL with current filters
        $base_url = base_url('database/rejected_data');
        $query_params = [];
        
        // Preserve current filters in pagination links
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $query_params[$key] = $value;
            }
        }
        
        // Build query string
        if (!empty($query_params)) {
            $base_url .= '?' . http_build_query($query_params);
        }
        
        $config['base_url'] = $base_url;
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        
        // Pagination styling
        $config['full_tag_open'] = '<nav><ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['anchor_class'] = 'page-link';
        
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('database/rejected_data', $data);
        $this->load->view('templates/footer');
    }

    public function download_rejected_data() {
        // Load PHPExcel library
        require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        
        // Get all rejected data
        $rejected_data = $this->peserta_reject_model->get_all();
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Hajj System")
                                   ->setLastModifiedBy("Hajj System")
                                   ->setTitle("Data Import Ditolak")
                                   ->setSubject("Data peserta yang gagal diimport")
                                   ->setDescription("Data peserta yang ditolak saat proses import");
        
        // Add header row
        $headers = [
            'Nama Peserta',
            'Nomor Paspor',
            'No Visa',
            'Tanggal Lahir',
            'Password',
            'No. HP',
            'Email',
            'Gender',
            'Status',
            'Tanggal',
            'Jam',
            'Flag Dokumen',
            'Alasan Penolakan',
            'Nomor Baris Excel',
            'Tanggal Ditolak'
        ];
        
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        // Set header style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'DC3545'],
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
        ];

        // Add headers
        foreach ($headers as $col => $header) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($col);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->applyFromArray($headerStyle);
        }

        // Add data
        foreach ($rejected_data as $row => $data) {
            $row_num = $row + 2; // Start from row 2 (after header)
            
            $sheet->setCellValue('A' . $row_num, $data->nama);
            $sheet->setCellValue('B' . $row_num, $data->nomor_paspor);
            $sheet->setCellValue('C' . $row_num, $data->no_visa);
            $sheet->setCellValue('D' . $row_num, $data->tgl_lahir);
            $sheet->setCellValue('E' . $row_num, $data->password);
            $sheet->setCellValue('F' . $row_num, $data->nomor_hp);
            $sheet->setCellValue('G' . $row_num, $data->email);
            $sheet->setCellValue('H' . $row_num, $data->gender);
            $sheet->setCellValue('I' . $row_num, $data->status);
            $sheet->setCellValue('J' . $row_num, $data->tanggal);
            $sheet->setCellValue('K' . $row_num, $data->jam);
            $sheet->setCellValue('L' . $row_num, $data->flag_doc);
            $sheet->setCellValue('M' . $row_num, $data->reject_reason);
            $sheet->setCellValue('N' . $row_num, $data->row_number);
            $sheet->setCellValue('O' . $row_num, $data->created_at);
        }

        // Auto-size columns
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set filename
        $filename = 'data_import_ditolak_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Create Excel file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function clear_rejected_data() {
        $result = $this->peserta_reject_model->delete_all();
        
        if ($result) {
            $this->session->set_flashdata('success', 'Semua data yang ditolak berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data yang ditolak');
        }
        
        redirect('database/rejected_data');
    }

    public function delete_rejected($id) {
        $result = $this->peserta_reject_model->delete($id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Data yang ditolak berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data yang ditolak');
        }
        
        redirect('database/rejected_data');
    }

    public function download_failed_import() {
        // Load PHPExcel library
        require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        
        // Get all rejected data
        $rejected_data = $this->peserta_reject_model->get_all();
        
        if (empty($rejected_data)) {
            $this->session->set_flashdata('error', 'Tidak ada data yang gagal diimport');
            redirect('database/rejected_data');
        }
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()
            ->setCreator('Sistem Haji')
            ->setLastModifiedBy('Sistem Haji')
            ->setTitle('Data Import Gagal')
            ->setSubject('Data yang gagal masuk ke database')
            ->setDescription('Data peserta yang gagal diimport ke database')
            ->setKeywords('import, gagal, peserta')
            ->setCategory('Data Import');
        
        // Set headers
        $headers = [
            'Nama Peserta',
            'Nomor Paspor',
            'No Visa',
            'Tanggal Lahir',
            'Password',
            'No. HP',
            'Email',
            'Gender',
            'Status',
            'Tanggal',
            'Jam',
            'Flag Dokumen',
            'Alasan Penolakan',
            'Nomor Baris Excel',
            'Tanggal Ditolak'
        ];
        
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        // Set header style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'DC3545'],
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
        ];

        // Add headers
        foreach ($headers as $col => $header) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($col);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->applyFromArray($headerStyle);
        }

        // Add data
        foreach ($rejected_data as $row => $data) {
            $row_num = $row + 2; // Start from row 2 (after header)
            
            $sheet->setCellValue('A' . $row_num, $data->nama);
            $sheet->setCellValue('B' . $row_num, $data->nomor_paspor);
            $sheet->setCellValue('C' . $row_num, $data->no_visa);
            $sheet->setCellValue('D' . $row_num, $data->tgl_lahir);
            $sheet->setCellValue('E' . $row_num, $data->password);
            $sheet->setCellValue('F' . $row_num, $data->nomor_hp);
            $sheet->setCellValue('G' . $row_num, $data->email);
            $sheet->setCellValue('H' . $row_num, $data->gender);
            $sheet->setCellValue('I' . $row_num, $data->status);
            $sheet->setCellValue('J' . $row_num, $data->tanggal);
            $sheet->setCellValue('K' . $row_num, $data->jam);
            $sheet->setCellValue('L' . $row_num, $data->flag_doc);
            $sheet->setCellValue('M' . $row_num, $data->reject_reason);
            $sheet->setCellValue('N' . $row_num, $data->row_number);
            $sheet->setCellValue('O' . $row_num, $data->created_at);
        }

        // Auto-size columns
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set filename
        $filename = 'data_import_gagal_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Create Excel file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function download_successful_data() {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu');
            redirect('auth');
        }
        
        // Load PHPExcel library
        require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
        
        // Get successful data from session using userdata instead of flashdata
        $successful_data = $this->session->userdata('successful_data');
        
        // Debug logging
        log_message('info', 'Download successful data - Session data: ' . json_encode($successful_data));
        
        if (empty($successful_data)) {
            log_message('error', 'Download successful data - No data found in session');
            $this->session->set_flashdata('error', 'Tidak ada data yang berhasil diimport untuk didownload. Silakan lakukan import terlebih dahulu.');
            redirect('database/import');
        }
        
        try {
            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();
            
            // Set document properties
            $objPHPExcel->getProperties()
                ->setCreator('Sistem Haji')
                ->setLastModifiedBy('Sistem Haji')
                ->setTitle('Data Import Berhasil')
                ->setSubject('Data yang berhasil masuk ke database')
                ->setDescription('Data peserta yang berhasil diimport ke database')
                ->setKeywords('import, berhasil, peserta')
                ->setCategory('Data Import');
        
        // Add header row
        $headers = [
            'Nama Peserta',
            'Nomor Paspor',
            'No Visa',
            'Tanggal Lahir',
            'Password',
            'No. HP',
            'Email',
            'Gender',
            'Status',
            'Tanggal',
            'Jam',
            'Flag Dokumen',
            'Nomor Baris Excel'
        ];
        
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        // Set header style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '28A745'], // Green color for success
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
        ];

        // Add headers
        foreach ($headers as $col => $header) {
            $colLetter = PHPExcel_Cell::stringFromColumnIndex($col);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->applyFromArray($headerStyle);
        }

        // Add data
        foreach ($successful_data as $row => $data) {
            $row_num = $row + 2; // Start from row 2 (after header)
            
            $sheet->setCellValue('A' . $row_num, $data['nama']);
            $sheet->setCellValue('B' . $row_num, $data['nomor_paspor']);
            $sheet->setCellValue('C' . $row_num, $data['no_visa']);
            $sheet->setCellValue('D' . $row_num, $data['tgl_lahir']);
            $sheet->setCellValue('E' . $row_num, $data['password']);
            $sheet->setCellValue('F' . $row_num, $data['nomor_hp']);
            $sheet->setCellValue('G' . $row_num, $data['email']);
            $sheet->setCellValue('H' . $row_num, $data['gender']);
            $sheet->setCellValue('I' . $row_num, $data['status']);
            $sheet->setCellValue('J' . $row_num, $data['tanggal']);
            $sheet->setCellValue('K' . $row_num, $data['jam']);
            $sheet->setCellValue('L' . $row_num, $data['flag_doc']);
            $sheet->setCellValue('M' . $row_num, $data['row_number']);
        }

        // Auto-size columns
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set filename
        $filename = 'data_import_berhasil_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Create Excel file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        
        // Kirim notifikasi Telegram untuk download data berhasil
        $this->telegram_notification->download_notification('Data Import Berhasil', $filename, count($successful_data));
        
        // Clean up session data after successful download
        $this->session->unset_userdata('successful_count');
        $this->session->unset_userdata('successful_data');
        
        exit;
        
        } catch (Exception $e) {
            log_message('error', 'Download successful data error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat membuat file Excel. Error: ' . $e->getMessage());
            redirect('database/import');
        }
    }
    
    /**
     * Download barcode attachments as ZIP file based on filters
     */
    public function download_barcode_attachments() {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu');
            redirect('auth');
        }
        
        // Get filters from GET parameters
        $tanggaljam = $this->input->get('tanggaljam');
        $flag_doc = $this->input->get('flag_doc');
        $status = $this->input->get('status');
        
        // Build query to find records with barcode files
        $this->db->select('id, nama, barcode, tanggal, jam, flag_doc');
        $this->db->from('peserta');
        $this->db->where('barcode IS NOT NULL');
        $this->db->where('barcode !=', '');
        $this->db->where('barcode !=', '-');
        
        // Apply filters
        if (!empty($tanggaljam)) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $tanggaljam);
        }
        
        if (!empty($flag_doc)) {
            if ($flag_doc === 'null') {
                $this->db->where('(flag_doc IS NULL OR flag_doc = "")');
            } else {
                $this->db->where('flag_doc', $flag_doc);
            }
        }
        
        if (!empty($status) && $status !== '') {
            $this->db->where('status', $status);
        }
        
        $this->db->order_by('tanggal', 'ASC');
        $this->db->order_by('jam', 'ASC');
        
        $results = $this->db->get()->result();
        
        if (empty($results)) {
            $this->session->set_flashdata('error', 'Tidak ada file barcode yang ditemukan dengan filter yang diberikan');
            redirect('database');
        }
        
        // Create ZIP file
        $zip_filename = 'barcode_attachments_' . date('Y-m-d_H-i-s') . '.zip';
        $zip_path = FCPATH . 'temp/' . $zip_filename;
        
        // Create temp directory if not exists
        $temp_dir = FCPATH . 'temp/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
            $this->session->set_flashdata('error', 'Gagal membuat file ZIP');
            redirect('database');
        }
        
        $barcode_dir = FCPATH . 'assets/uploads/barcode/';
        $added_files = 0;
        
        foreach ($results as $record) {
            $barcode_file = $barcode_dir . $record->barcode;
            
            if (file_exists($barcode_file)) {
                // Create descriptive filename for ZIP
                $file_extension = pathinfo($record->barcode, PATHINFO_EXTENSION);
                $descriptive_name = sprintf(
                    '%s_%s_%s_%s.%s',
                    $record->nama,
                    $record->tanggal,
                    $record->jam,
                    $record->flag_doc,
                    $file_extension
                );
                
                // Clean filename for ZIP (remove special characters)
                $descriptive_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $descriptive_name);
                
                // Add file to ZIP
                if ($zip->addFile($barcode_file, $descriptive_name)) {
                    $added_files++;
                }
            }
        }
        
        $zip->close();
        
        if ($added_files === 0) {
            unlink($zip_path);
            $this->session->set_flashdata('error', 'Tidak ada file barcode yang valid ditemukan');
            redirect('database');
        }
        
        // Set headers for download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($zip_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        // Output file and delete
        readfile($zip_path);
        unlink($zip_path);
        exit;
    }
    
    /**
     * Delete barcode file from uploads directory
     */
    private function delete_barcode_file($filename) {
        if (empty($filename)) {
            return false;
        }
        
        // Validate filename for security
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            log_message('error', 'Invalid filename for deletion: ' . $filename);
            return false;
        }
        
        $file_path = FCPATH . 'assets/uploads/barcode/' . $filename;
        
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                log_message('info', 'Barcode file deleted: ' . $filename);
                return true;
            } else {
                log_message('error', 'Failed to delete barcode file: ' . $filename);
                return false;
            }
        } else {
            log_message('warning', 'Barcode file not found for deletion: ' . $filename);
            return false;
        }
    }
    
    /**
     * Get redirect URL with current filters and pagination
     */
    private function get_redirect_url_with_filters() {
        $base_url = base_url('database/index');
        $query_params = [];
        
        // Get current filters from GET parameters
        $filters = [
            'nama' => $this->input->get('nama'),
            'nomor_paspor' => $this->input->get('nomor_paspor'),
            'no_visa' => $this->input->get('no_visa'),
            'flag_doc' => $this->input->get('flag_doc'),
            'tanggaljam' => $this->input->get('tanggaljam'),
            'status' => $this->input->get('status'),
            'gender' => $this->input->get('gender'),
            'page' => $this->input->get('page')
        ];
        
        // Add non-empty filters to query parameters
        foreach ($filters as $key => $value) {
            if (!empty($value) && $value !== '') {
                $query_params[$key] = $value;
            }
        }
        
        // Build query string
        if (!empty($query_params)) {
            $base_url .= '?' . http_build_query($query_params);
        }
        
        return $base_url;
    }
} 