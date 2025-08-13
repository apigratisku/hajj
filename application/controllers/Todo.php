<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Todo extends CI_Controller {

    private function is_ajax_request() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function __construct() {
        parent::__construct();
        $this->load->model('transaksi_model');
        $this->load->model('agent_model');
        $this->load->library('form_validation');
        $this->load->library('session');
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
        $data['title'] = 'To Do List';
        // Get filters from GET parameters and clean them
        $filters = [
            'nama' => trim($this->input->get('nama')),
            'nomor_paspor' => trim($this->input->get('nomor_paspor')),
            'no_visa' => trim($this->input->get('no_visa')),
            'flag_doc' => trim($this->input->get('flag_doc')),
            'tanggaljam' => trim($this->input->get('tanggaljam'))
        ];
        
        // Remove empty filters to avoid unnecessary WHERE clauses
        $filters = array_filter($filters, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Pagination settings
        $per_page = 100;
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $offset = ($page - 1) * $per_page;
        
        // Get data
        $data['peserta'] = $this->transaksi_model->get_paginated_filtered_todo($per_page, $offset, $filters);
        // Provide flag_doc options for filter select
        $data['flag_doc_list'] = $this->transaksi_model->get_unique_flag_doc();
        $data['tanggaljam_list'] = $this->transaksi_model->get_unique_tanggaljam();
        
        // Get total count for pagination
        $total_rows = $this->transaksi_model->count_filtered($filters);
        
        // Load pagination library
        $this->load->library('pagination');
        
        // Build base URL with current filters
        $base_url = base_url('todo/index');
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
        $this->load->view('database/todo', $data);
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
            $this->transaksi_model->delete($id);
            redirect('database');
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
            $data = [
                'nama' => $this->input->post('nama'),
                'nomor_paspor' => $this->input->post('nomor_paspor'),
                'no_visa' => $this->input->post('no_visa'),
                'tgl_lahir' => $this->input->post('tgl_lahir') ? $this->input->post('tgl_lahir') : null,
                'password' => $this->input->post('password'),
                'nomor_hp' => $this->input->post('nomor_hp'),
                'email' => $this->input->post('email'),
                'gender' => $this->input->post('gender'),
                'status' => $this->input->post('status') ? $this->input->post('status') : 0,
                'tanggal' => $this->input->post('tanggal'),
                'jam' => $this->input->post('jam'),
                'flag_doc' => $this->input->post('flag_doc'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->transaksi_model->update($id, $data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Data peserta berhasil diperbarui');
            } else {
                $this->session->set_flashdata('error', 'Gagal memperbarui data peserta');
            }
            
            redirect('todo');
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
                if ($field === 'tgl_lahir' && empty($input[$field])) {
                    $data[$field] = null;
                } else {
                    $data[$field] = trim($input[$field]) ?: null;
                }
            }
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Debug: Log the data being updated
        log_message('debug', 'Todo update_ajax - Updating peserta ID: ' . $id . ' with data: ' . json_encode($data));
        
        try {
            // Update data
            $result = $this->transaksi_model->update($id, $data);
            
            if ($result) {
                $this->output->set_output(json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']));
            } else {
                $this->output->set_status_header(500);
                $this->output->set_output(json_encode(['success' => false, 'message' => 'Gagal memperbarui data. Silakan coba lagi.']));
            }
        } catch (Exception $e) {
            log_message('error', 'Exception in Todo update_ajax: ' . $e->getMessage());
            $this->output->set_status_header(500);
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
            'flag_doc' => $this->input->get('flag_doc')
        ];
        
        // Get data
        $peserta = $this->transaksi_model->get_paginated_filtered(1000, 0, $filters);
        
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
                ->setCellValue('H1', 'Gender')
                ->setCellValue('I1', 'Tanggal')
                ->setCellValue('J1', 'Jam')
                ->setCellValue('K1', 'Status')
                ->setCellValue('L1', 'Flag Dokumen');
            
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
            
            $excel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($headerStyle);
            
            // Populate data
            $row = 2;
            foreach ($peserta as $p) {
                $status = '';
                if ($p->status == 0) {
                    $status = 'On Target';
                } elseif ($p->status == 1) {
                    $status = 'On Schedule';
                } elseif ($p->status == 2) {
                    $status = 'Done';
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
                    ->setCellValue('H' . $row, $gender ?: '-')
                    ->setCellValue('I' . $row, $p->tanggal ?: '-')
                    ->setCellValue('J' . $row, $p->jam ?: '-')
                    ->setCellValue('K' . $row, $status)
                    ->setCellValue('L' . $row, $p->flag_doc ?: '-');
                $row++;
            }
            
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
                $excel->getActiveSheet()->getStyle('A2:L' . ($row - 1))->applyFromArray($dataStyle);
            }
            
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
            $this->session->set_flashdata('error', 'Error saat export: ' . $e->getMessage());
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
                'nama' => $this->input->post('nama'),
                'nomor_paspor' => $this->input->post('nomor_paspor'),
                'no_visa' => $this->input->post('no_visa'),
                'tgl_lahir' => $this->input->post('tgl_lahir') ? $this->input->post('tgl_lahir') : null,
                'password' => $this->input->post('password'),
                'nomor_hp' => $this->input->post('nomor_hp'),
                'email' => $this->input->post('email'),
                'gender' => $this->input->post('gender'),
                'status' => $this->input->post('status') ? $this->input->post('status') : 0,
                'tanggal' => $this->input->post('tanggal'),
                'jam' => $this->input->post('jam'),
                'flag_doc' => $this->input->post('flag_doc'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->transaksi_model->insert($data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Data peserta berhasil ditambahkan');
            } else {
                $this->session->set_flashdata('error', 'Gagal menambahkan data peserta');
            }
            
            redirect('database');
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
            
            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $nama_peserta = trim($sheet->getCellByColumnAndRow(0, $row)->getValue());
                $nomor_paspor = trim($sheet->getCellByColumnAndRow(1, $row)->getValue());
                $no_visa = trim($sheet->getCellByColumnAndRow(2, $row)->getValue());
                $tgl_lahir = trim($sheet->getCellByColumnAndRow(3, $row)->getValue());
                $password = trim($sheet->getCellByColumnAndRow(4, $row)->getValue());
                $nomor_hp = trim($sheet->getCellByColumnAndRow(5, $row)->getValue());
                $email = trim($sheet->getCellByColumnAndRow(6, $row)->getValue());
                $gender = "";
                $status = 0;
                $tanggal = "";
                $jam = "";
                $flag_doc = trim($sheet->getCellByColumnAndRow(11, $row)->getValue());
                
                // Skip empty rows
                if (empty($nama_peserta) && empty($nomor_paspor)) {
                    continue;
                }
                
                // Validate required fields
                if (empty($nama_peserta) || empty($nomor_paspor) || empty($password)) {
                    $errors[] = "Row $row: Nama Peserta, Nomor Paspor, dan Password harus diisi";
                    $error_count++;
                    continue;
                }
                
                
                // Process status
                $status_value = 0; // Default to 'On Target'
                if (!empty($status)) {
                    switch (strtolower($status)) {
                        case 'on schedule':
                        case 'on schedule':
                            $status_value = 1;
                            break;
                        case 'done':
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
                
                // Process date
                $tgl_lahir_value = null;

                if (!empty($tgl_lahir)) {
                    if (is_numeric($tgl_lahir)) {
                        // Format numeric Excel
                        $tgl_lahir_value = PHPExcel_Shared_Date::ExcelToPHP($tgl_lahir);
                        $tgl_lahir_value = date('Y-m-d', $tgl_lahir_value);
                    } else {
                        // Hilangkan spasi ekstra
                        $tgl_lahir = trim($tgl_lahir);

                        // Mapping bulan Indonesia ke angka
                        $bulan_map = [
                            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
                            'Mei' => '05', 'Jun' => '06', 'Jul' => '07', 'Agu' => '08',
                            'Sep' => '09', 'Okt' => '10', 'Nov' => '11', 'Des' => '12'
                        ];

                        // Format dd/mm/yyyy
                        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $tgl_lahir)) {
                            $dt = DateTime::createFromFormat('d/m/Y', $tgl_lahir);
                            if ($dt) {
                                $tgl_lahir_value = $dt->format('Y-m-d');
                            }
                        }
                        // Format dd-MMM-yyyy (Indonesia)
                        elseif (preg_match('/^\d{1,2}-[A-Za-z]{3}-\d{4}$/', $tgl_lahir)) {
                            list($d, $m, $y) = explode('-', $tgl_lahir);
                            $m = ucfirst(strtolower($m)); // Pastikan kapitalisasi benar
                            if (isset($bulan_map[$m])) {
                                $tgl_lahir_value = sprintf('%04d-%02d-%02d', $y, $bulan_map[$m], $d);
                            }
                        }
                        // Fallback: coba parse otomatis
                        else {
                            $parsed_date = date_parse($tgl_lahir);
                            if ($parsed_date['error_count'] == 0 && checkdate($parsed_date['month'], $parsed_date['day'], $parsed_date['year'])) {
                                $tgl_lahir_value = date('Y-m-d', strtotime($tgl_lahir));
                            }
                        }
                    }
                }

                
                // Check if peserta already exists
                $existing_peserta = $this->transaksi_model->get_by_passport($nomor_paspor);
                if ($existing_peserta) {
                    $errors[] = "Row $row: Peserta dengan nomor paspor '$nomor_paspor' sudah ada";
                    $error_count++;
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
                    'tanggal' => $tanggal,
                    'jam' => $jam,
                    'flag_doc' => $flag_doc,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $result = $this->transaksi_model->insert($peserta_data);
                if ($result) {
                    $success_count++;
                } else {
                    $errors[] = "Row $row: Gagal menyimpan data peserta";
                    $error_count++;
                }
            }
            
            // Set flash messages
            if ($success_count > 0) {
                $this->session->set_flashdata('success', "Berhasil mengimport $success_count data peserta");
            }
            if ($error_count > 0) {
                $this->session->set_flashdata('error', "Gagal mengimport $error_count data. " . implode('; ', array_slice($errors, 0, 5)));
            }
            
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Error saat membaca file: ' . $e->getMessage());
        }
        
        redirect('database/import');
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
            'Gender',
            'Status',
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
            'L',
            'On Target',
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
} 