<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('agent_model');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        $data['title'] = 'Data Agent';
        
        // Pagination Configuration
        $config['base_url'] = base_url('master/index');
        $config['total_rows'] = $this->agent_model->count_all();
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        
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
        $data['agent'] = $this->agent_model->get_paginated($config['per_page'], $page);
        $data['pagination'] = $this->pagination->create_links();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('master/index', $data);
        $this->load->view('templates/footer');
    }
    
    public function tambah() {
        $this->form_validation->set_rules('nama_agent', 'Nama Agent', 'required|trim');
        $this->form_validation->set_rules('hp', 'HP', 'required|trim');
        
        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'Tambah Agent';
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('master/tambah', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'nama_agent' => $this->input->post('nama_agent'),
                'hp' => $this->input->post('hp'),
            ];
            
            $insert = $this->agent_model->insert($data);
            
            if ($insert) {
                $this->session->set_flashdata('success', 'Data agent berhasil ditambahkan');
            } else {
                $this->session->set_flashdata('error', 'Gagal menambahkan data agent');
            }
            
            redirect('master');
        }
    }
    
    public function edit($id) {
        $this->form_validation->set_rules('nama_agent', 'Nama Agent', 'required|trim');
        $this->form_validation->set_rules('hp', 'HP', 'required|trim');
        
        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'Edit Agent';
            $data['agent'] = $this->agent_model->get_by_id($id);
            
            if (!$data['agent']) {
                $this->session->set_flashdata('error', 'Data agent tidak ditemukan');
                redirect('master');
            }
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('master/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'nama_agent' => $this->input->post('nama_agent'),
                'hp' => $this->input->post('hp'),
            ];
            
            $update = $this->agent_model->update($id, $data);
            
            if ($update) {
                $this->session->set_flashdata('success', 'Data agent berhasil diupdate');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengupdate data agent');
            }
            
            redirect('master');
        }
    }
    
    public function hapus($id) {
        $agent = $this->agent_model->get_by_id($id);
        
        if (!$agent) {
            $this->session->set_flashdata('error', 'Data agent tidak ditemukan');
            redirect('master');
        }
        
        $delete = $this->agent_model->delete($id);
        
        if ($delete) {
            $this->session->set_flashdata('success', 'Data agent berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data agent');
        }
        
        redirect('master');
    }
    
    public function import() {
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'csv|xlsx|xls';
        $config['max_size'] = 2048;
        
        $this->load->library('upload', $config);
        
        if (!$this->upload->do_upload('file')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('master');
        } else {
            $file_data = $this->upload->data();
            $file_path = './uploads/' . $file_data['file_name'];
            
            // Load library untuk membaca Excel/CSV
            $this->load->library('PHPExcel');
            
            // Proses import disini
            // ...
            
            $this->session->set_flashdata('success', 'Data berhasil diimport');
            redirect('master');
        }
    }
    
    public function export() {
        $agent = $this->agent_model->get_all();
        
        // Header
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Data_Agent.xls");
        
        echo '<table border="1">';
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>Nama Agent</th>';
        echo '<th>HP</th>';
        echo '</tr>';
        
        $no = 1;
        foreach ($agent as $a) {
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . $a->nama_agent . '</td>';
            echo '<td>' . $a->hp . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }
} 