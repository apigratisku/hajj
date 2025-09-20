<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peserta extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('peserta_model');
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('log_activity');
        $this->load->library('form_validation');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        $data['title'] = 'Data Peserta';
        
        // Ambil filter dari GET
        $filters = [
            'nama' => $this->input->get('nama', TRUE),
            'nomor_paspor' => $this->input->get('nomor_paspor', TRUE),
            'no_visa' => $this->input->get('no_visa', TRUE),
        ];
        
        // Pagination Configuration
        $config['base_url'] = base_url('peserta/index');
        $config['total_rows'] = $this->peserta_model->count_filtered($filters);
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        
        // Tambahkan query string pada pagination
        $config['reuse_query_string'] = TRUE;
        
        // Pagination Styling
        $config['full_tag_open'] = '<ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data['peserta'] = $this->peserta_model->get_paginated_filtered($config['per_page'], $page, $filters);
        $data['pagination'] = $this->pagination->create_links();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('peserta/index', $data);
        $this->load->view('templates/footer');
    }

    public function tambah() {
        $data['title'] = 'Tambah Data Peserta';
        $data['agents'] = $this->peserta_model->get_all_agents();
        
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_agent', 'Agent', 'required');
            $this->form_validation->set_rules('nama', 'Nama Peserta', 'required');
            $this->form_validation->set_rules('nomor_paspor', 'Nomor Paspor', 'required');
            $this->form_validation->set_rules('no_visa', 'Nomor Visa', 'required');
            $this->form_validation->set_rules('tanggal_lahir', 'Tanggal Lahir', 'required');
            $this->form_validation->set_rules('password', 'Password', 'required');
            $this->form_validation->set_rules('no_hp', 'Nomor HP', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('gender', 'Gender', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                $data_insert = [
                    'id_agent' => $this->input->post('id_agent'),
                    'nama' => $this->input->post('nama'),
                    'nomor_paspor' => $this->input->post('nomor_paspor'),
                    'no_visa' => $this->input->post('no_visa'),
                    'tanggal_lahir' => $this->input->post('tanggal_lahir'),
                    'password' => $this->input->post('password'),
                    'no_hp' => $this->input->post('no_hp'),
                    'email' => $this->input->post('email'),
                    'gender' => $this->input->post('gender'),
                    'status' => 'Aktif'
                ];
                
                $peserta_id = $this->peserta_model->insert($data_insert);
                
                // Log activity
                log_peserta_activity($peserta_id, 'create', 'Menambah data peserta baru dengan nama: ' . $data_insert['nama']);
                
                $this->session->set_flashdata('success', 'Data peserta berhasil ditambahkan');
                redirect('peserta');
            }
        }
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('peserta/tambah', $data);
        $this->load->view('templates/footer');
    }

    public function edit($id) {
        $data['title'] = 'Edit Data Peserta';
        $data['peserta'] = $this->peserta_model->get_by_id($id);
        $data['agents'] = $this->peserta_model->get_all_agents();
        
        if (!$data['peserta']) {
            $this->session->set_flashdata('error', 'Data peserta tidak ditemukan');
            redirect('peserta');
        }
        
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('id_agent', 'Agent', 'required');
            $this->form_validation->set_rules('nama', 'Nama Peserta', 'required');
            $this->form_validation->set_rules('nomor_paspor', 'Nomor Paspor', 'required');
            $this->form_validation->set_rules('no_visa', 'Nomor Visa', 'required');
            $this->form_validation->set_rules('tanggal_lahir', 'Tanggal Lahir', 'required');
            $this->form_validation->set_rules('password', 'Password', 'required');
            $this->form_validation->set_rules('no_hp', 'Nomor HP', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('gender', 'Gender', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                // Get old data for comparison
                $old_data = $this->peserta_model->get_by_id($id);
                
                $data_update = [
                    'id_agent' => $this->input->post('id_agent'),
                    'nama' => $this->input->post('nama'),
                    'nomor_paspor' => $this->input->post('nomor_paspor'),
                    'no_visa' => $this->input->post('no_visa'),
                    'tanggal_lahir' => $this->input->post('tanggal_lahir'),
                    'password' => $this->input->post('password'),
                    'no_hp' => $this->input->post('no_hp'),
                    'email' => $this->input->post('email'),
                    'gender' => $this->input->post('gender'),
                    'status' => $this->input->post('status')
                ];
                
                $this->peserta_model->update($id, $data_update);
                
                // Log activity
                log_peserta_activity($id, 'update', 'Mengupdate data peserta: ' . $data_update['nama'], (array)$old_data, $data_update);
                
                $this->session->set_flashdata('success', 'Data peserta berhasil diupdate');
                redirect('peserta');
            }
        }
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('peserta/edit', $data);
        $this->load->view('templates/footer');
    }

    public function delete($id) {
        // Get data before deletion for logging
        $peserta_data = $this->peserta_model->get_by_id($id);
        
        $this->peserta_model->delete($id);
        
        // Log activity
        if ($peserta_data) {
            log_peserta_activity($id, 'delete', 'Menghapus data peserta: ' . $peserta_data->nama);
        }
        
        $this->session->set_flashdata('success', 'Data peserta berhasil dihapus');
        redirect('peserta');
    }

    public function export() {
        $peserta = $this->peserta_model->get_all();
        
        // Log activity
        log_system_activity('Export data peserta ke Excel - Total: ' . count($peserta) . ' data');
        
        // Load PHPExcel library
        $this->load->library('Excel');
        
        $excel = new PHPExcel();
        $excel->getProperties()->setTitle("Data Peserta")->setDescription("Data Peserta Hajj dan Umrah");
        
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        
        // Set header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Agent');
        $sheet->setCellValue('C1', 'Nama Peserta');
        $sheet->setCellValue('D1', 'No Paspor');
        $sheet->setCellValue('E1', 'No Visa');
        $sheet->setCellValue('F1', 'Tgl. Lahir');
        $sheet->setCellValue('G1', 'Password');
        $sheet->setCellValue('H1', 'No. HP');
        $sheet->setCellValue('I1', 'Email');
        $sheet->setCellValue('J1', 'Gender');
        $sheet->setCellValue('K1', 'Status');
        
        // Set data
        $no = 1;
        foreach ($peserta as $p) {
            $sheet->setCellValue('A' . ($no + 1), $no);
            $sheet->setCellValue('B' . ($no + 1), $p->nama_agent);
            $sheet->setCellValue('C' . ($no + 1), $p->nama);
            $sheet->setCellValue('D' . ($no + 1), $p->nomor_paspor);
            $sheet->setCellValue('E' . ($no + 1), $p->no_visa);
            $sheet->setCellValue('F' . ($no + 1), $p->tanggal_lahir);
            $sheet->setCellValue('G' . ($no + 1), $p->password);
            $sheet->setCellValue('H' . ($no + 1), $p->no_hp);
            $sheet->setCellValue('I' . ($no + 1), $p->email);
            $sheet->setCellValue('J' . ($no + 1), $p->gender);
            $sheet->setCellValue('K' . ($no + 1), $p->status);
            $no++;
        }
        
        // Auto size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set filename
        $filename = 'Data_Peserta_' . date('Y-m-d_H-i-s') . '.xls';
        
        // Set headers for download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}
