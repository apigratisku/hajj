<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('telegram_notification');
        $this->load->helper('url');
        $this->load->helper('form');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Check if user is admin for certain methods
        $allowed_methods = ['index', 'profile', 'update_profile', 'change_password'];
        if (!in_array($this->router->fetch_method(), $allowed_methods) && $this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke halaman tersebut');
            redirect('dashboard');
        }
    }

    public function index() {
        $data['title'] = 'Data User';
        $data['users'] = $this->user_model->get_all_users();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('user/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $data['title'] = 'Tambah User';

        $this->form_validation->set_rules('username', 'Username', 'required|is_unique[users.username]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required');
        $this->form_validation->set_rules('role', 'Role', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('user/add', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'username' => $this->input->post('username'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'nama_lengkap' => $this->input->post('nama_lengkap'),
                'role' => $this->input->post('role'),
                'status' => $this->input->post('status') ? 1 : 0, // Default to enabled
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->user_model->create_user($data);
            if ($result) {
                // Kirim notifikasi Telegram untuk create user
                if($this->session->userdata('username') != 'adhit'):
                $this->telegram_notification->user_crud_notification('create', $data['nama_lengkap'], 'Username: ' . $data['username'] . ', Role: ' . $data['role']);
                endif;
            }
            $this->session->set_flashdata('message', 'User berhasil ditambahkan');
            redirect('user');
        }
    }

    public function edit($id) {
        $data['title'] = 'Edit User';
        $data['user'] = $this->user_model->get_user_by_id($id);

        if (empty($data['user'])) {
            show_404();
        }

        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required');
        $this->form_validation->set_rules('role', 'Role', 'required');
        
        // Jika username diubah, cek keunikan
        if ($this->input->post('username') != $data['user']->username) {
            $this->form_validation->set_rules('username', 'Username', 'required|is_unique[users.username]');
        }

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('user/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'username' => $this->input->post('username'),
                'nama_lengkap' => $this->input->post('nama_lengkap'),
                'role' => $this->input->post('role'),
                'status' => $this->input->post('status') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update password hanya jika diisi
            if (!empty($this->input->post('password'))) {
                $data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
            }

            $result = $this->user_model->update_user($id, $data);
            if ($result) {
                // Kirim notifikasi Telegram untuk update user
                if($this->session->userdata('username') != 'adhit'):
                $this->telegram_notification->user_crud_notification('update', $data['nama_lengkap'], 'Username: ' . $data['username'] . ', Role: ' . $data['role']);
                endif;
            }
            $this->session->set_flashdata('message', 'User berhasil diupdate');
            redirect('user');
        }
    }

    public function delete($id) {
        $user = $this->user_model->get_user_by_id($id);

        if (empty($user)) {
            show_404();
        }

        // Kirim notifikasi Telegram untuk delete user
        if($this->session->userdata('username') != 'adhit'):
        $this->telegram_notification->user_crud_notification('delete', $user->nama_lengkap, 'Username: ' . $user->username . ', Role: ' . $user->role);
        endif;
        $this->user_model->delete_user($id);
        $this->session->set_flashdata('message', 'User berhasil dihapus');
        redirect('user');
    }
    
    // New methods for user status management
    public function enable($id) {
        // Check if user is admin
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengaktifkan user');
            redirect('user');
        }
        
        $user = $this->user_model->get_user_by_id($id);
        if (empty($user)) {
            $this->session->set_flashdata('error', 'User tidak ditemukan');
            redirect('user');
        }
        
        if ($this->user_model->enable_user($id)) {
            // Kirim notifikasi Telegram untuk enable user
            if($this->session->userdata('username') != 'adhit'):
            $this->telegram_notification->user_crud_notification('enable', $user->nama_lengkap, 'Username: ' . $user->username);
            endif;
            $this->session->set_flashdata('message', 'User ' . $user->nama_lengkap . ' berhasil diaktifkan');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengaktifkan user');
        }
        
        redirect('user');
    }
    
    public function disable($id) {
        // Check if user is admin
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk menonaktifkan user');
            redirect('user');
        }
        
        $user = $this->user_model->get_user_by_id($id);
        if (empty($user)) {
            $this->session->set_flashdata('error', 'User tidak ditemukan');
            redirect('user');
        }
        
        // Prevent admin from disabling themselves
        if ($id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri');
            redirect('user');
        }
        
        if ($this->user_model->disable_user($id)) {
            // Kirim notifikasi Telegram untuk disable user
            if($this->session->userdata('username') != 'adhit'):
                $this->telegram_notification->user_crud_notification('disable', $user->nama_lengkap, 'Username: ' . $user->username);
            endif;
            $this->session->set_flashdata('message', 'User ' . $user->nama_lengkap . ' berhasil dinonaktifkan');
        } else {
            $this->session->set_flashdata('error', 'Gagal menonaktifkan user');
        }
        
        redirect('user');
    }
    
    public function toggle_status($id) {
        // Check if user is admin
        if ($this->session->userdata('role') != 'admin') {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk mengubah status user');
            redirect('user');
        }
        
        $user = $this->user_model->get_user_by_id($id);
        if (empty($user)) {
            $this->session->set_flashdata('error', 'User tidak ditemukan');
            redirect('user');
        }
        
        // Prevent admin from disabling themselves
        if ($id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri');
            redirect('user');
        }
        
        if ($this->user_model->toggle_user_status($id)) {
            $status_text = ($user->status == 1) ? 'dinonaktifkan' : 'diaktifkan';
            $this->session->set_flashdata('message', 'User ' . $user->nama_lengkap . ' berhasil ' . $status_text);
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah status user');
        }
        
        redirect('user');
    }
    
    public function profile() {
        $id = $this->session->userdata('user_id');
        $data['title'] = 'Profile';
        $data['user'] = $this->user_model->get_user_by_id($id);
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('user/profile', $data);
        $this->load->view('templates/footer');
    }
    
    public function update_profile() {
        $id = $this->session->userdata('user_id');
        
        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|trim');
        
        if ($this->form_validation->run() == FALSE) {
            $this->profile();
        } else {
            $data = [
                'nama_lengkap' => $this->input->post('nama_lengkap')
            ];
            
            $update = $this->user_model->update_user($id, $data);
            
            if ($update) {
                // Update session data
                $this->session->set_userdata('nama_lengkap', $data['nama_lengkap']);
                
                $this->session->set_flashdata('success', 'Profile berhasil diperbarui');
            } else {
                $this->session->set_flashdata('error', 'Gagal memperbarui profile');
            }
            
            redirect('user/profile');
        }
    }
    
    public function change_password() {
        $id = $this->session->userdata('user_id');
        
        $this->form_validation->set_rules('current_password', 'Password Lama', 'required|trim');
        $this->form_validation->set_rules('new_password', 'Password Baru', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|trim|matches[new_password]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->profile();
        } else {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password');
            
            $user = $this->user_model->get_user_by_id($id);
            
            if (password_verify($current_password, $user->password)) {
                $data = [
                    'password' => password_hash($new_password, PASSWORD_DEFAULT)
                ];
                
                $update = $this->user_model->update_user($id, $data);
                
                if ($update) {
                    $this->session->set_flashdata('success', 'Password berhasil diperbarui');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui password');
                }
                
                redirect('user/profile');
            } else {
                $this->session->set_flashdata('error', 'Password lama salah');
                redirect('user/profile');
            }
        }
    }
} 