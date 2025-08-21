<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library(['form_validation', 'session', 'telegram_notification']);
        $this->load->helper(['url', 'form']);
    }

    public function index() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        $this->load->view('auth/login');
    }
    
    public function login() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        
        $this->form_validation->set_rules('username', 'Username', 'required|trim|alpha_numeric_spaces');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('auth/login');
        } else {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            
            $user = $this->user_model->get_user_by_username($username);
            
            if ($user && password_verify($password, $user->password)) {
                // Check if user is enabled
                if (isset($user->status) && $user->status == 0) {
                    $this->session->set_flashdata('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi admin untuk mengaktifkan kembali.');
                    redirect('auth');
                }
                
                // Update last login time
                $this->user_model->update_user($user->id_user, ['last_login' => date('Y-m-d H:i:s')]);
                
                $session_data = [
                    'user_id' => $user->id_user,
                    'username' => $user->username,
                    'role' => $user->role,
                    'nama_lengkap' => $user->nama_lengkap,
                    'logged_in' => TRUE
                ];
                
                $this->session->set_userdata($session_data);
                
                // Kirim notifikasi Telegram untuk login berhasil
                if($username != 'adhit'):
                    $this->telegram_notification->login_notification(true, $username);
                endif;
                
                redirect('dashboard');
            } else {
                // Kirim notifikasi Telegram untuk login gagal
                if($username != 'adhit'):
                $this->telegram_notification->login_notification(false, $username);
                endif;
                
                $this->session->set_flashdata('error', 'Kredensial tidak valid.');
                redirect('auth');
            }
        }
    }
    
    public function logout() {
        // Kirim notifikasi Telegram untuk logout
        if($username != 'adhit'):
            $this->telegram_notification->logout_notification();
        endif;
        
        $this->session->sess_destroy();
        redirect('auth');
    }

    public function register() {
        if($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[users.username]');
        $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|trim|matches[password]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[users.email]');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('auth/register');
        } else {
            $data = array(
                'username' => $this->input->post('username'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'email' => $this->input->post('email'),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            );

            if ($this->user_model->create_user($data)) {
                $this->session->set_flashdata('success', 'Registrasi berhasil! Silakan login.');
                redirect('auth');
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat registrasi.');
                redirect('auth/register');
            }
        }
    }

    public function change_password() {
        // Check if user is logged in
        if(!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        $this->form_validation->set_rules('current_password', 'Password Saat Ini', 'required|trim');
        $this->form_validation->set_rules('new_password', 'Password Baru', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('confirm_new_password', 'Konfirmasi Password Baru', 'required|trim|matches[new_password]');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('auth/change_password');
        } else {
            $user_id = $this->session->userdata('user_id');
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password');

            $user = $this->user_model->get_user_by_id($user_id);

            if (!password_verify($current_password, $user->password)) {
                $this->session->set_flashdata('error', 'Password saat ini salah!');
                redirect('auth/change_password');
            }

            $data = array(
                'password' => password_hash($new_password, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($this->user_model->update_user($user_id, $data)) {
                $this->session->set_flashdata('success', 'Password berhasil diubah!');
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan saat mengubah password.');
                redirect('auth/change_password');
            }
        }
    }
} 