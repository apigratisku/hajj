<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_management extends CI_Controller {

    private $cpanel_username;
    private $cpanel_password;
    private $cpanel_domain;
    private $cpanel_url;

    public function __construct() {
        // Suppress all PHP errors and warnings to prevent HTML output
        error_reporting(0);
        ini_set('display_errors', 0);

        // Clear any existing output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Load Telegram notification library if it exists
        if (file_exists(APPPATH . 'libraries/Telegram_notification.php')) {
            $this->load->library('telegram_notification');
        }
        
        // Load cPanel configuration
        $this->load_cpanel_config();
    }

    private function load_cpanel_config() {
        try {
            // Load cPanel configuration from config file
            $this->config->load('cpanel_config', TRUE);
            $cpanel_config = $this->config->item('cpanel', 'cpanel_config');
            
            if (!$cpanel_config) {
                log_message('error', 'cPanel configuration not found');
                $this->cpanel_username = 'demo';
                $this->cpanel_password = 'demo';
                $this->cpanel_domain = 'demo.com';
                $this->cpanel_url = 'https://demo.com:2083/execute/';
                return;
            }
            
            $this->cpanel_username = isset($cpanel_config['username']) ? $cpanel_config['username'] : 'demo';
            $this->cpanel_password = isset($cpanel_config['password']) ? $cpanel_config['password'] : 'demo';
            
            // Clean domain name (remove http://, https://, www. if present)
            $domain = isset($cpanel_config['domain']) ? $cpanel_config['domain'] : 'demo.com';
            log_message('info', 'Original domain from config: ' . $domain);
            
            $domain = preg_replace('/^https?:\/\//', '', $domain);
            $domain = preg_replace('/^www\./', '', $domain);
            $domain = rtrim($domain, '/');
            $this->cpanel_domain = $domain;
            
            log_message('info', 'Cleaned domain: ' . $this->cpanel_domain);
            
            // Build cPanel URL with proper format
            $protocol = isset($cpanel_config['protocol']) ? $cpanel_config['protocol'] : 'https';
            $port = isset($cpanel_config['port']) ? $cpanel_config['port'] : '2083';
            
            // Try different URL formats
            $url_formats = [
                $protocol . '://' . $this->cpanel_domain . ':' . $port . '/execute/',
                $protocol . '://' . $this->cpanel_domain . ':' . $port . '/json-api/',
                $protocol . '://' . $this->cpanel_domain . ':' . $port . '/uapi/',
                $protocol . '://' . $this->cpanel_domain . '/execute/',
                $protocol . '://' . $this->cpanel_domain . '/json-api/',
                $protocol . '://' . $this->cpanel_domain . '/uapi/'
            ];
            
            $this->cpanel_url = $url_formats[0]; // Default to first format
            
            log_message('info', 'cPanel URL: ' . $this->cpanel_url);
            log_message('info', 'cPanel Username: ' . $this->cpanel_username);
            log_message('info', 'cPanel Domain: ' . $this->cpanel_domain);
            log_message('info', 'cPanel Password (masked): ' . str_repeat('*', strlen($this->cpanel_password)));
            log_message('info', 'cPanel Password length: ' . strlen($this->cpanel_password));
            log_message('info', 'cPanel Password contains special chars: ' . (preg_match('/[^a-zA-Z0-9]/', $this->cpanel_password) ? 'YES' : 'NO'));
            
        } catch (Exception $e) {
            log_message('error', 'Error loading cPanel config: ' . $e->getMessage());
            // Set default values
            $this->cpanel_username = 'demo';
            $this->cpanel_password = 'demo';
            $this->cpanel_domain = 'demo.com';
            $this->cpanel_url = 'https://demo.com:2083/execute/';
        }
    }

    public function index() {
        try {
            $data['title'] = 'Manajemen Email';
            
            // Get list of email accounts
            $data['email_accounts'] = $this->get_email_accounts();
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/index', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_management index: ' . $e->getMessage());
            show_error('Terjadi kesalahan saat memuat halaman. Silakan coba lagi.', 500);
        }
    }

    public function create() {
        try {
            $data['title'] = 'Tambah Akun Email';
            
            if ($this->input->post()) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');
                $quota = $this->input->post('quota', true) ?: 250; // Default 250MB
                
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
                
                redirect('email_management');
            }
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/create', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_management create: ' . $e->getMessage());
            show_error('Terjadi kesalahan saat membuat akun email. Silakan coba lagi.', 500);
        }
    }

    public function edit($email) {
        try {
            $data['title'] = 'Edit Akun Email';
            $data['email'] = urldecode($email);
            
            // Get email account details
            $account_details = $this->get_email_account_details($data['email']);
            $data['account'] = $account_details;
            
            if ($this->input->post()) {
                $new_password = $this->input->post('password');
                $new_quota = $this->input->post('quota', true) ?: 250;
                
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
                
                redirect('email_management');
            }
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/edit', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_management edit: ' . $e->getMessage());
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
            
            redirect('email_management');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_management delete: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menghapus akun email.');
            redirect('email_management');
        }
    }

    public function check_accounts() {
        try {
            // AJAX endpoint to check email accounts
            if (!$this->input->is_ajax_request()) {
                $this->output->set_status_header(400);
                $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid request']));
                return;
            }
            
            $accounts = $this->get_email_accounts();
            
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'success' => true,
                'accounts' => $accounts
            ]));
        } catch (Exception $e) {
            log_message('error', 'Error in Email_management check_accounts: ' . $e->getMessage());
            $this->output->set_status_header(500);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Terjadi kesalahan server']));
        }
    }

    private function get_cpanel_session() {
        try {
            // Try to get session ID from cPanel login
            $login_url = 'https://' . $this->cpanel_domain . ':2083/login/';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $login_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-cPanel-API/1.0');
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                // Try to extract session ID from response
                if (preg_match('/cpsess(\d+)/', $response, $matches)) {
                    return $matches[1];
                }
            }
            
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error getting cPanel session: ' . $e->getMessage());
            return null;
        }
    }

    private function make_cpanel_request($module, $function, $params = []) {
        try {
            // Try different authentication methods
            $auth_methods = [
                'basic' => function($url) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    $encoded_password = urlencode($this->cpanel_password);
                    curl_setopt($ch, CURLOPT_USERPWD, $this->cpanel_username . ':' . $encoded_password);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-cPanel-API/1.0');
                    
                    if (!empty($params)) {
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                    
                    return $ch;
                },
                'header' => function($url) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Authorization: Basic ' . base64_encode($this->cpanel_username . ':' . $this->cpanel_password),
                        'User-Agent: CodeIgniter-cPanel-API/1.0'
                    ]);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    
                    if (!empty($params)) {
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                    
                    return $ch;
                }
            ];
            
            // Try different URL formats
            $url_formats = [
                'https://' . $this->cpanel_domain . ':2083/uapi/' . $module . '/' . $function,
                'https://' . $this->cpanel_domain . ':2083/execute/' . $module . '/' . $function,
                'https://' . $this->cpanel_domain . ':2083/json-api/' . $module . '/' . $function,
                'https://' . $this->cpanel_domain . '/uapi/' . $module . '/' . $function,
                'https://' . $this->cpanel_domain . '/execute/' . $module . '/' . $function,
                'https://' . $this->cpanel_domain . '/json-api/' . $module . '/' . $function
            ];
            
            foreach ($url_formats as $url_index => $url) {
                log_message('info', 'Trying URL format ' . ($url_index + 1) . ': ' . $url);
                
                foreach ($auth_methods as $auth_name => $auth_method) {
                    log_message('info', 'Trying authentication method: ' . $auth_name);
                    
                    $ch = $auth_method($url);
                    
                    // Log request details for debugging
                    log_message('info', 'cPanel Request URL: ' . $url);
                    log_message('info', 'cPanel Username: ' . $this->cpanel_username);
                    log_message('info', 'cPanel Domain: ' . $this->cpanel_domain);
                    log_message('info', 'cPanel Password (masked): ' . str_repeat('*', strlen($this->cpanel_password)));
                    
                    $response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_error($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);
                    
                    // Log detailed response for debugging
                    log_message('info', 'cPanel Request URL: ' . $url);
                    log_message('info', 'cPanel Request Username: ' . $this->cpanel_username);
                    log_message('info', 'cPanel Request Password (encoded): ' . urlencode($this->cpanel_password));
                    log_message('info', 'cPanel Response HTTP Code: ' . $http_code);
                    log_message('info', 'cPanel Response Info: ' . json_encode(array_intersect_key($info, array_flip(['url', 'http_code', 'total_time', 'connect_time']))));
                    
                    if ($error) {
                        log_message('error', 'cPanel cURL error: ' . $error);
                        continue; // Try next auth method
                    }
                    
                    // Extract headers and body from response
                    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                    $header = substr($response, 0, $header_size);
                    $body = substr($response, $header_size);
                    
                    log_message('info', 'cPanel Response Headers: ' . substr($header, 0, 500));
                    log_message('info', 'cPanel Response Body: ' . substr($body, 0, 500));
                    
                    if ($http_code === 401) {
                        log_message('error', 'cPanel Authentication failed with method ' . $auth_name);
                        continue; // Try next auth method
                    }
                    
                    if ($http_code === 404) {
                        log_message('error', 'cPanel Endpoint not found with method ' . $auth_name);
                        continue; // Try next auth method
                    }
                    
                    if ($http_code !== 200) {
                        log_message('error', 'cPanel HTTP error: ' . $http_code . ' with method ' . $auth_name);
                        continue; // Try next auth method
                    }
                    
                    $data = json_decode($body, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        log_message('error', 'cPanel JSON decode error: ' . json_last_error_msg() . ' with method ' . $auth_name);
                        continue; // Try next auth method
                    }
                    
                    // Success! Return the result
                    log_message('info', 'cPanel request successful with URL: ' . $url . ' and auth method: ' . $auth_name);
                    return ['success' => true, 'data' => $data, 'url' => $url, 'auth_method' => $auth_name];
                }
            }
            
            // If we get here, all attempts failed
            return ['success' => false, 'message' => 'All authentication methods and URL formats failed. Please check your cPanel configuration.'];
        } catch (Exception $e) {
            log_message('error', 'Error in make_cpanel_request: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Request error: ' . $e->getMessage()];
        }
    }

    private function get_email_accounts() {
        try {
            $result = $this->make_cpanel_request('Email', 'list_pops');
            
            if (!$result['success']) {
                log_message('error', 'Failed to get email accounts: ' . $result['message']);
                return [];
            }
            
            $accounts = [];
            if (isset($result['data']['data'])) {
                foreach ($result['data']['data'] as $account) {
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
            return [];
        }
    }

    private function get_email_account_details($email) {
        try {
            $result = $this->make_cpanel_request('Email', 'list_pops', ['email' => $email]);
            
            if (!$result['success']) {
                return null;
            }
            
            if (isset($result['data']['data'][0])) {
                return $result['data']['data'][0];
            }
            
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error in get_email_account_details: ' . $e->getMessage());
            return null;
        }
    }

    private function create_email_account($email, $password, $quota) {
        try {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Format email tidak valid'];
            }
            
            // Extract domain from email
            $domain = substr(strrchr($email, "@"), 1);
            
            $params = [
                'email' => $email,
                'pass' => $password,
                'quota' => $quota,
                'domain' => $domain
            ];
            
            $result = $this->make_cpanel_request('Email', 'add_pop', $params);
            
            if (!$result['success']) {
                return ['success' => false, 'message' => $result['message']];
            }
            
            if (isset($result['data']['errors']) && !empty($result['data']['errors'])) {
                $error_message = implode(', ', $result['data']['errors']);
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
            $params = [
                'email' => $email,
                'quota' => $quota
            ];
            
            // Only update password if provided
            if (!empty($password)) {
                $params['pass'] = $password;
            }
            
            $result = $this->make_cpanel_request('Email', 'edit_pop', $params);
            
            if (!$result['success']) {
                return ['success' => false, 'message' => $result['message']];
            }
            
            if (isset($result['data']['errors']) && !empty($result['data']['errors'])) {
                $error_message = implode(', ', $result['data']['errors']);
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
            $params = [
                'email' => $email
            ];
            
            $result = $this->make_cpanel_request('Email', 'delete_pop', $params);
            
            if (!$result['success']) {
                return ['success' => false, 'message' => $result['message']];
            }
            
            if (isset($result['data']['errors']) && !empty($result['data']['errors'])) {
                $error_message = implode(', ', $result['data']['errors']);
                return ['success' => false, 'message' => $error_message];
            }
            
            return ['success' => true, 'message' => 'Email account deleted successfully'];
        } catch (Exception $e) {
            log_message('error', 'Error in delete_email_account: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting email account: ' . $e->getMessage()];
        }
    }

    public function test_connection() {
        try {
            // Test with a simple endpoint
            $result = $this->make_cpanel_request('UAPI', 'get_user_information');
            
            if ($result['success']) {
                $working_url = $result['url'];
                $auth_method = $result['auth_method'];
                log_message('info', 'Found working cPanel URL: ' . $working_url . ' with auth method: ' . $auth_method);
                $this->session->set_flashdata('success', 'Koneksi ke cPanel berhasil! URL: ' . $working_url . ', Auth Method: ' . $auth_method);
            } else {
                log_message('error', 'cPanel connection failed: ' . $result['message']);
                $this->session->set_flashdata('error', 'Koneksi ke cPanel gagal: ' . $result['message'] . '. Periksa konfigurasi cPanel di file config/cpanel_config.php. Pastikan username, password, dan domain sudah benar. Username: ' . $this->cpanel_username . ', Domain: ' . $this->cpanel_domain . '. Coba akses cPanel secara manual untuk memastikan kredensial benar.');
            }
            
            redirect('email_management');
        } catch (Exception $e) {
            log_message('error', 'Error in test_connection: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menguji koneksi: ' . $e->getMessage());
            redirect('email_management');
        }
    }
}
