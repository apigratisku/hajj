<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends CI_Controller {

    private $cpanel_config;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Load cPanel config
        $this->load->config('cpanel_config');
        $this->cpanel_config = $this->config->item('cpanel');
        
        // Load Telegram notification library if it exists
        if (file_exists(APPPATH . 'libraries/Telegram_notification.php')) {
            $this->load->library('telegram_notification');
        }
    }

    public function index() {
        try {
            $data['title'] = 'Manajemen Email (cPanel)';
            
            // Load cPanel library
            $this->load->library('Cpanel', $this->cpanel_config);
            
            // Get list of email accounts
            $data['email_accounts'] = $this->get_email_accounts();
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/index', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email index: ' . $e->getMessage());
            show_error('Terjadi kesalahan saat memuat halaman. Silakan coba lagi.', 500);
        }
    }

    public function create() {
        try {
            $data['title'] = 'Tambah Akun Email';
            
            if ($this->input->post()) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');
                $quota = $this->input->post('quota', true) ? $this->input->post('quota', true) : 250;
                
                $result = $this->create_email_account($email, $password, $quota);
                
                if ($result['success']) {
                    // Kirim notifikasi Telegram untuk create email account
                    if (isset($this->telegram_notification)) {
                        $this->telegram_notification->email_management_notification('create', $email, 'Quota: ' . $quota . 'MB');
                    }
                    
                    $this->session->set_flashdata('success', 'Akun email berhasil dibuat: ' . $email);
                } else {
                    $this->session->set_flashdata('error', 'Gagal membuat akun email: ' . $result['message']);
                }
                
                redirect('email');
            }
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/create', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email create: ' . $e->getMessage());
            show_error('Terjadi kesalahan saat membuat akun email. Silakan coba lagi.', 500);
        }
    }

    public function edit($email) {
        try {
            $data['title'] = 'Edit Akun Email';
            $data['email'] = urldecode($email);
            
            if ($this->input->post()) {
                $new_password = $this->input->post('password');
                $new_quota = $this->input->post('quota', true) ? $this->input->post('quota', true) : 250;
                
                $result = $this->update_email_account($data['email'], $new_password, $new_quota);
                
                if ($result['success']) {
                    // Kirim notifikasi Telegram untuk update email account
                    if (isset($this->telegram_notification)) {
                        $this->telegram_notification->email_management_notification('update', $data['email'], 'Quota: ' . $new_quota . 'MB');
                    }
                    
                    $this->session->set_flashdata('success', 'Akun email berhasil diperbarui: ' . $data['email']);
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui akun email: ' . $result['message']);
                }
                
                redirect('email');
            }
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/edit', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email edit: ' . $e->getMessage());
            show_error('Terjadi kesalahan saat mengedit akun email. Silakan coba lagi.', 500);
        }
    }

    public function delete($email) {
        try {
            $email = urldecode($email);
            
            $result = $this->delete_email_account($email);
            
            if ($result['success']) {
                // Kirim notifikasi Telegram untuk delete email account
                if (isset($this->telegram_notification)) {
                    $this->telegram_notification->email_management_notification('delete', $email, '');
                }
                
                $this->session->set_flashdata('success', 'Akun email berhasil dihapus: ' . $email);
            } else {
                $this->session->set_flashdata('error', 'Gagal menghapus akun email: ' . $result['message']);
            }
            
            redirect('email');
        } catch (Exception $e) {
            log_message('error', 'Error in Email delete: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menghapus akun email.');
            redirect('email');
        }
    }

    public function check_accounts() {
        try {
            // Set error reporting to prevent any output
            error_reporting(0);
            ini_set('display_errors', 0);
            
            // Clear any output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Load cPanel library
            $this->load->library('Cpanel', $this->cpanel_config);
            
            // Get email accounts
            $accounts = $this->get_email_accounts();
            
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'success' => true,
                'accounts' => $accounts,
                'debug_info' => [
                    'total_accounts' => count($accounts),
                    'cpanel_host' => $this->cpanel_config['host'],
                    'session_token' => $this->cpanel->getSessionToken(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]));
        } catch (Exception $e) {
            log_message('error', 'Error in Email check_accounts: ' . $e->getMessage());
            
            $this->output->set_status_header(500);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'success' => false, 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
                'debug_info' => [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]));
        }
    }

    public function test_connection() {
        try {
            // Load cPanel library
            $this->load->library('Cpanel', $this->cpanel_config);
            
            $result = $this->cpanel->testConnection();
            
            if (isset($result['error'])) {
                $this->session->set_flashdata('error', 'Koneksi ke cPanel gagal: ' . $result['error']);
            } else {
                $user_info = isset($result['data']) ? $result['data'] : [];
                $username = isset($user_info['user']) ? $user_info['user'] : 'Unknown';
                $this->session->set_flashdata('success', 'Koneksi ke cPanel berhasil! User: ' . $username);
            }
            
            redirect('email');
        } catch (Exception $e) {
            log_message('error', 'Error in Email test_connection: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menguji koneksi: ' . $e->getMessage());
            redirect('email');
        }
    }

    private function get_email_accounts() {
        try {
            $domain = $this->cpanel_config['host'];
            $result = $this->cpanel->listEmailAccounts($domain);
            
            if (isset($result['error'])) {
                log_message('error', 'Failed to get email accounts: ' . $result['error']);
                $this->session->set_flashdata('error', 'Gagal mengambil daftar email: ' . $result['error']);
                return [];
            }
            
            $accounts = [];
            if (isset($result['data'])) {
                foreach ($result['data'] as $account) {
                    $accounts[] = [
                        'email' => isset($account['email']) ? $account['email'] : '',
                        'quota' => isset($account['quota']) ? $account['quota'] : 0,
                        'usage' => isset($account['usage']) ? $account['usage'] : 0,
                        'suspended' => isset($account['suspended']) ? $account['suspended'] : false,
                        'created' => isset($account['created']) ? $account['created'] : ''
                    ];
                }
            }
            
            return $accounts;
        } catch (Exception $e) {
            log_message('error', 'Error in get_email_accounts: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat mengambil daftar email: ' . $e->getMessage());
            return [];
        }
    }

    private function create_email_account($email, $password, $quota) {
        try {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Format email tidak valid'];
            }
            
            $result = $this->cpanel->createEmailAccount($email, $password, $quota);
            
            if (isset($result['error'])) {
                return ['success' => false, 'message' => $result['error']];
            }
            
            if (isset($result['errors']) && !empty($result['errors'])) {
                $error_message = implode(', ', $result['errors']);
                return ['success' => false, 'message' => $error_message];
            }
            
            return ['success' => true, 'message' => 'Email account created successfully'];
        } catch (Exception $e) {
            log_message('error', 'Error in create_email_account: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating email account: ' . $e->getMessage()];
        }
    }

    private function update_email_account($email, $password, $quota) {
        try {
            $result = $this->cpanel->updateEmailAccount($email, $password, $quota);
            
            if (isset($result['error'])) {
                return ['success' => false, 'message' => $result['error']];
            }
            
            if (isset($result['errors']) && !empty($result['errors'])) {
                $error_message = implode(', ', $result['errors']);
                return ['success' => false, 'message' => $error_message];
            }
            
            return ['success' => true, 'message' => 'Email account updated successfully'];
        } catch (Exception $e) {
            log_message('error', 'Error in update_email_account: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating email account: ' . $e->getMessage()];
        }
    }

    private function delete_email_account($email) {
        try {
            $result = $this->cpanel->deleteEmailAccount($email);
            
            if (isset($result['error'])) {
                return ['success' => false, 'message' => $result['error']];
            }
            
            if (isset($result['errors']) && !empty($result['errors'])) {
                $error_message = implode(', ', $result['errors']);
                return ['success' => false, 'message' => $error_message];
            }
            
            return ['success' => true, 'message' => 'Email account deleted successfully'];
        } catch (Exception $e) {
            log_message('error', 'Error in delete_email_account: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting email account: ' . $e->getMessage()];
        }
    }
}
