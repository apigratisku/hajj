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
            log_message('info', 'Email index - Loading page');
            
            $data['title'] = 'Manajemen Email';
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            // Get list of email accounts
            $data['email_accounts'] = $this->get_email_accounts();
            $data['auth_method'] = $this->cpanel_new->getAuthMethod();
            
            log_message('info', 'Email index - Auth method: ' . $data['auth_method']);
            log_message('info', 'Email index - Email accounts count: ' . count($data['email_accounts']));
            
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
            log_message('info', 'Email create - Loading create page');
            
            $data['title'] = 'Tambah Akun Email';
            
            if ($this->input->post()) {
                log_message('info', 'Email create - Processing POST request');
                
                $email = $this->input->post('email');
                $password = $this->input->post('password');
                $quota = $this->input->post('quota', true) ? $this->input->post('quota', true) : 250;
                
                log_message('info', 'Email create - Creating email: ' . $email . ' with quota: ' . $quota);
                
                $result = $this->create_email_account($email, $password, $quota);
                
                if ($result['success']) {
                    log_message('info', 'Email create - Success creating email: ' . $email);
                    
                    // Kirim notifikasi Telegram untuk create email account
                    if (isset($this->telegram_notification)) {
                        if($this->session->userdata('username') != 'adhit'):
                        $this->telegram_notification->email_management_notification('create', $email, 'Quota: ' . $quota . 'MB');
                        endif;

                    }
                    
                    $this->session->set_flashdata('success', 'Akun email berhasil dibuat: ' . $email);
                } else {
                    log_message('error', 'Email create - Failed creating email: ' . $email . ' - ' . $result['message']);
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
            log_message('info', 'Email edit - Loading edit page for email: ' . $email);
            
            $data['title'] = 'Edit Akun Email';
            $data['email'] = urldecode($email);
            
            if ($this->input->post()) {
                log_message('info', 'Email edit - Processing POST request for email: ' . $data['email']);
                
                $new_password = $this->input->post('password');
                $new_quota = $this->input->post('quota', true) ? $this->input->post('quota', true) : 250;
                
                log_message('info', 'Email edit - Updating email: ' . $data['email'] . ' with quota: ' . $new_quota);
                
                $result = $this->update_email_account($data['email'], $new_password, $new_quota);
                
                if ($result['success']) {
                    log_message('info', 'Email edit - Success updating email: ' . $data['email']);
                    
                    // Kirim notifikasi Telegram untuk update email account
                    if (isset($this->telegram_notification)) {
                        $this->telegram_notification->email_management_notification('update', $data['email'], 'Quota: ' . $new_quota . 'MB');
                    }
                    
                    $this->session->set_flashdata('success', 'Akun email berhasil diperbarui: ' . $data['email']);
                } else {
                    log_message('error', 'Email edit - Failed updating email: ' . $data['email'] . ' - ' . $result['message']);
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
            log_message('info', 'Email delete - Deleting email: ' . $email);
            
            $email = urldecode($email);
            
            $result = $this->delete_email_account($email);
            
            if ($result['success']) {
                log_message('info', 'Email delete - Success deleting email: ' . $email);
                
                // Kirim notifikasi Telegram untuk delete email account
                if (isset($this->telegram_notification)) {
                    $this->telegram_notification->email_management_notification('delete', $email, 'Email account deleted');
                }
                
                $this->session->set_flashdata('success', 'Akun email berhasil dihapus: ' . $email);
            } else {
                log_message('error', 'Email delete - Failed deleting email: ' . $email . ' - ' . $result['message']);
                $this->session->set_flashdata('error', 'Gagal menghapus akun email: ' . $result['message']);
            }
            
            redirect('email');
        } catch (Exception $e) {
            log_message('error', 'Error in Email delete: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menghapus akun email: ' . $e->getMessage());
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
            
            log_message('info', 'Email check_accounts - Starting request');
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            // Get email accounts
            $accounts = $this->get_email_accounts();
            
            $debug_info = [
                'total_accounts' => count($accounts),
                'cpanel_host' => $this->cpanel_config['host'],
                'auth_method' => $this->cpanel_new->getAuthMethod(),
                'session_token' => $this->cpanel_new->getSessionToken(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            log_message('info', 'Email check_accounts - Debug info: ' . json_encode($debug_info));
            
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'success' => true,
                'accounts' => $accounts,
                'debug_info' => $debug_info
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
            log_message('info', 'Email test_connection - Starting connection test');
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            // Test basic connection
            $result = $this->cpanel_new->testConnection();
            
            log_message('info', 'Email test_connection - Basic result: ' . json_encode($result));
            
            $message = '';
            if (isset($result['error'])) {
                log_message('error', 'Email test_connection - Basic Error: ' . $result['error']);
                $message .= 'Koneksi dasar gagal: ' . $result['error'] . ' ';
            } else {
                $user_info = isset($result['data']) ? $result['data'] : [];
                $username = isset($user_info['user']) ? $user_info['user'] : 'Unknown';
                $auth_method = $this->cpanel_new->getAuthMethod();
                log_message('info', 'Email test_connection - Basic Success: User: ' . $username . ' (Auth: ' . $auth_method . ')');
                $message .= 'Koneksi dasar berhasil! User: ' . $username . ' (Auth: ' . $auth_method . ') ';
            }
            
            // Test session token validity
            $session_result = $this->cpanel_new->testSessionToken();
            
            log_message('info', 'Email test_connection - Session result: ' . json_encode($session_result));
            
            if (isset($session_result['error'])) {
                log_message('error', 'Email test_connection - Session Error: ' . $session_result['error']);
                $message .= 'Session token gagal: ' . $session_result['error'] . ' ';
            } else {
                log_message('info', 'Email test_connection - Session Success: ' . $session_result['message']);
                $message .= 'Session token berhasil! ';
            }
            
            // Test Jupiter interface connection
            $jupiter_result = $this->cpanel_new->testJupiterConnection();
            
            log_message('info', 'Email test_connection - Jupiter result: ' . json_encode($jupiter_result));
            
            if (isset($jupiter_result['error'])) {
                log_message('error', 'Email test_connection - Jupiter Error: ' . $jupiter_result['error']);
                $message .= 'Jupiter interface gagal: ' . $jupiter_result['error'];
            } else {
                log_message('info', 'Email test_connection - Jupiter Success: ' . $jupiter_result['message']);
                $message .= 'Jupiter interface berhasil!';
            }
            
            $this->session->set_flashdata('success', $message);
            
            redirect('email');
        } catch (Exception $e) {
            log_message('error', 'Error in Email test_connection: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menguji koneksi: ' . $e->getMessage());
            redirect('email');
        }
    }

    private function get_email_accounts() {
        try {
            log_message('info', 'Email get_email_accounts - Starting request');
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            $result = $this->cpanel_new->listEmailAccounts();
            
            log_message('info', 'Email get_email_accounts - Raw result: ' . json_encode($result));
            
            if (isset($result['error'])) {
                log_message('error', 'Failed to get email accounts: ' . $result['error']);
                $this->session->set_flashdata('error', 'Gagal mengambil daftar email: ' . $result['error']);
                return [];
            }
            
            $accounts = [];
            
            // Handle different response formats
            if (isset($result['data']) && is_array($result['data'])) {
                log_message('info', 'Email get_email_accounts - Processing session auth format');
                // Session auth format
                foreach ($result['data'] as $account) {
                    $accounts[] = [
                        'email' => isset($account['email']) ? $account['email'] : '',
                        'quota' => isset($account['quota']) ? $account['quota'] : 0,
                        'usage' => isset($account['usage']) ? $account['usage'] : 0,
                        'suspended' => isset($account['suspended']) ? $account['suspended'] : false,
                        'created' => isset($account['created']) ? $account['created'] : ''
                    ];
                }
            } elseif (is_array($result)) {
                log_message('info', 'Email get_email_accounts - Processing token auth format');
                // Token auth format
                foreach ($result as $account) {
                    $accounts[] = [
                        'email' => isset($account['email']) ? $account['email'] : '',
                        'quota' => isset($account['quota']) ? $account['quota'] : 0,
                        'usage' => isset($account['usage']) ? $account['usage'] : 0,
                        'suspended' => isset($account['suspended']) ? $account['suspended'] : false,
                        'created' => isset($account['created']) ? $account['created'] : ''
                    ];
                }
            } else {
                log_message('warning', 'Email get_email_accounts - Unknown response format: ' . gettype($result));
            }
            
            log_message('info', 'Email get_email_accounts - Processed accounts: ' . count($accounts));
            return $accounts;
        } catch (Exception $e) {
            log_message('error', 'Error in get_email_accounts: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat mengambil daftar email: ' . $e->getMessage());
            return [];
        }
    }

    private function create_email_account($email, $password, $quota) {
        try {
            log_message('info', 'Email create_email_account - Creating email: ' . $email);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                log_message('error', 'Email create_email_account - Invalid email format: ' . $email);
                return ['success' => false, 'message' => 'Format email tidak valid'];
            }
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            $result = $this->cpanel_new->createEmailAccount($email, $password, $quota);
            
            log_message('info', 'Email create_email_account - Result: ' . json_encode($result));
            
            if (isset($result['error'])) {
                log_message('error', 'Email create_email_account - Error: ' . $result['error']);
                return ['success' => false, 'message' => $result['error']];
            }
            
            if (isset($result['errors']) && !empty($result['errors'])) {
                $error_message = implode(', ', $result['errors']);
                log_message('error', 'Email create_email_account - Errors: ' . $error_message);
                return ['success' => false, 'message' => $error_message];
            }
            
            log_message('info', 'Email create_email_account - Success');
            return ['success' => true, 'message' => 'Email account created successfully'];
        } catch (Exception $e) {
            log_message('error', 'Error in create_email_account: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating email account: ' . $e->getMessage()];
        }
    }

    private function update_email_account($email, $password, $quota) {
        try {
            log_message('info', 'Email update_email_account - Updating email: ' . $email);
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            $result = $this->cpanel_new->updateEmailAccount($email, $password, $quota);
            
            log_message('info', 'Email update_email_account - Result: ' . json_encode($result));
            
            if (isset($result['error'])) {
                log_message('error', 'Email update_email_account - Error: ' . $result['error']);
                return ['success' => false, 'message' => $result['error']];
            }
            
            if (isset($result['errors']) && !empty($result['errors'])) {
                $error_message = implode(', ', $result['errors']);
                log_message('error', 'Email update_email_account - Errors: ' . $error_message);
                return ['success' => false, 'message' => $error_message];
            }
            
            log_message('info', 'Email update_email_account - Success');
            return ['success' => true, 'message' => 'Email account updated successfully'];
        } catch (Exception $e) {
            log_message('error', 'Error in update_email_account: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating email account: ' . $e->getMessage()];
        }
    }

    private function delete_email_account($email) {
        try {
            log_message('info', 'Email delete_email_account - Deleting email: ' . $email);
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            $result = $this->cpanel_new->deleteEmailAccount($email);
            
            log_message('info', 'Email delete_email_account - Result: ' . json_encode($result));
            
            if (isset($result['error'])) {
                log_message('error', 'Email delete_email_account - Error: ' . $result['error']);
                return ['success' => false, 'message' => $result['error']];
            }
            
            if (isset($result['errors']) && !empty($result['errors'])) {
                $error_message = implode(', ', $result['errors']);
                log_message('error', 'Email delete_email_account - Errors: ' . $error_message);
                return ['success' => false, 'message' => $error_message];
            }
            
            log_message('info', 'Email delete_email_account - Success');
            return ['success' => true, 'message' => 'Email account deleted successfully'];
        } catch (Exception $e) {
            log_message('error', 'Error in delete_email_account: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting email account: ' . $e->getMessage()];
        }
    }
}