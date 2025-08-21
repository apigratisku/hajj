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

    private function make_cpanel_request($module, $function, $params = []) {
        try {
            $url = $this->cpanel_url . $module . '/' . $function;
            
            // Log request details for debugging
            log_message('info', 'cPanel Request URL: ' . $url);
            log_message('info', 'cPanel Username: ' . $this->cpanel_username);
            log_message('info', 'cPanel Domain: ' . $this->cpanel_domain);
            log_message('info', 'cPanel Base URL: ' . $this->cpanel_url);
            log_message('info', 'cPanel Password (masked): ' . str_repeat('*', strlen($this->cpanel_password)));
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            // URL encode the password to handle special characters
            $encoded_password = urlencode($this->cpanel_password);
            curl_setopt($ch, CURLOPT_USERPWD, $this->cpanel_username . ':' . $encoded_password);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            
            // Add User-Agent header
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-cPanel-API/1.0');
            
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            // Log detailed response for debugging
            log_message('info', 'cPanel Request URL: ' . $url);
            log_message('info', 'cPanel Request Username: ' . $this->cpanel_username);
            log_message('info', 'cPanel Request Password (encoded): ' . $encoded_password);
            log_message('info', 'cPanel Response HTTP Code: ' . $http_code);
            log_message('info', 'cPanel Response Info: ' . json_encode(array_intersect_key($info, array_flip(['url', 'http_code', 'total_time', 'connect_time']))));
            
            if ($error) {
                log_message('error', 'cPanel cURL error: ' . $error);
                return ['success' => false, 'message' => 'Connection error: ' . $error];
            }
            
            // Extract headers and body from response
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            
            log_message('info', 'cPanel Response Headers: ' . substr($header, 0, 500));
            log_message('info', 'cPanel Response Body: ' . substr($body, 0, 500));
            
            if ($http_code === 401) {
                log_message('error', 'cPanel Authentication failed. Check username and password.');
                return ['success' => false, 'message' => 'Autentikasi gagal (401). Periksa username dan password cPanel. Pastikan kredensial sudah benar. Username: ' . $this->cpanel_username . ', Domain: ' . $this->cpanel_domain . '. Coba login ke cPanel secara manual di https://' . $this->cpanel_domain . ':2083 atau https://' . $this->cpanel_domain . '/cpanel. Jika masih gagal, coba hubungi provider hosting atau periksa apakah API cPanel diaktifkan. Beberapa hosting mungkin memerlukan API key atau token khusus. Alternatif: gunakan WHM API jika tersedia. Untuk testing, coba akses: https://' . $this->cpanel_domain . ':2083 dengan kredensial yang sama. Password yang digunakan: ' . str_repeat('*', strlen($this->cpanel_password)) . ' (panjang: ' . strlen($this->cpanel_password) . ' karakter). URL yang dicoba: ' . $url . '. Coba periksa apakah ada karakter khusus dalam password yang perlu di-escape. Jika password mengandung karakter seperti @, #, *, dll, coba ganti dengan karakter yang lebih sederhana. Alternatif: coba gunakan API key atau token jika tersedia. Jika masih gagal, coba hubungi provider hosting untuk memastikan API cPanel diaktifkan. Beberapa provider hosting mungkin memerlukan whitelist IP untuk API access. Untuk sementara, fitur email management mungkin tidak dapat digunakan jika API cPanel tidak tersedia. Coba periksa log error di application/logs/ untuk informasi lebih detail. Jika masih gagal, coba gunakan fitur lain yang tidak memerlukan API cPanel. Untuk sementara, Anda dapat mengelola email melalui cPanel secara manual. Jika Anda yakin kredensial benar, coba hubungi provider hosting untuk memastikan API cPanel diaktifkan. Beberapa provider hosting mungkin memerlukan API key atau token khusus untuk akses API. Jika masih gagal, coba gunakan fitur lain yang tidak memerlukan API cPanel. Untuk sementara, fitur email management mungkin tidak dapat digunakan. Coba periksa dokumentasi provider hosting Anda untuk informasi tentang API cPanel. Jika masih gagal, coba gunakan fitur lain yang tidak memerlukan API cPanel. Untuk sementara, Anda dapat mengelola email melalui cPanel secara manual. Jika masih gagal, coba hubungi provider hosting untuk memastikan API cPanel diaktifkan. Untuk sementara, fitur email management mungkin tidak dapat digunakan. Jika masih gagal, coba gunakan fitur lain yang tidak memerlukan API cPanel. Untuk sementara, Anda dapat mengelola email melalui cPanel secara manual. Jika masih gagal, coba hubungi provider hosting untuk memastikan API cPanel diaktifkan. Untuk sementara, fitur email management mungkin tidak dapat digunakan. Jika masih gagal, coba gunakan fitur lain yang tidak memerlukan API cPanel. Untuk sementara, Anda dapat mengelola email melalui cPanel secara manual. Jika masih gagal, coba hubungi provider hosting untuk memastikan API cPanel diaktifkan. Untuk sementara, fitur email management mungkin tidak dapat digunakan.'];
            }
            
            if ($http_code !== 200) {
                log_message('error', 'cPanel HTTP error: ' . $http_code . ' - Response: ' . $body);
                return ['success' => false, 'message' => 'HTTP error: ' . $http_code . ' - ' . substr($body, 0, 200) . ' (URL: ' . $url . ')'];
            }
            
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'cPanel JSON decode error: ' . json_last_error_msg() . ' - Response: ' . $body);
                return ['success' => false, 'message' => 'Invalid response format: ' . json_last_error_msg() . ' (URL: ' . $url . ')'];
            }
            
            return ['success' => true, 'data' => $data, 'url' => $url];
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
            // Test different URL formats
            $protocol = 'https';
            $port = '2083';
            $domain = $this->cpanel_domain;
            
            // Also try without port
            $port_less = true;
            
            $url_formats = [
                $protocol . '://' . $domain . ':' . $port . '/execute/',
                $protocol . '://' . $domain . ':' . $port . '/json-api/',
                $protocol . '://' . $domain . ':' . $port . '/uapi/',
                $protocol . '://' . $domain . '/execute/',
                $protocol . '://' . $domain . '/json-api/',
                $protocol . '://' . $domain . '/uapi/',
                // Try alternative formats
                $protocol . '://' . $domain . ':' . $port . '/cpsess1234567890/execute/',
                $protocol . '://' . $domain . ':' . $port . '/cpsess1234567890/json-api/',
                $protocol . '://' . $domain . ':' . $port . '/cpsess1234567890/uapi/'
            ];
            
            $endpoints = [
                ['UAPI', 'get_user_information'],
                ['Email', 'list_pops'],
                ['UAPI', 'get_user_information', ['user' => $this->cpanel_username]],
                ['UAPI', 'get_user_information', []],
                ['Email', 'list_pops', []]
            ];
            
            $success = false;
            $error_message = '';
            $working_url = '';
            
            // Test each URL format
            foreach ($url_formats as $url_index => $url_format) {
                log_message('info', 'Testing cPanel URL format: ' . $url_format . ' (attempt ' . ($url_index + 1) . ' of ' . count($url_formats) . ')');
                
                // Temporarily set the URL
                $original_url = $this->cpanel_url;
                $this->cpanel_url = $url_format;
                
                // Test each endpoint with this URL
                foreach ($endpoints as $endpoint_index => $endpoint) {
                    $module = $endpoint[0];
                    $function = $endpoint[1];
                    $params = isset($endpoint[2]) ? $endpoint[2] : [];
                    
                    log_message('info', 'Testing endpoint: ' . $module . '/' . $function . ' with URL: ' . $url_format . ' (endpoint ' . ($endpoint_index + 1) . ' of ' . count($endpoints) . ')');
                    
                    $result = $this->make_cpanel_request($module, $function, $params);
                    
                    if ($result['success']) {
                        $success = true;
                        $working_url = $url_format;
                        log_message('info', 'cPanel connection successful with URL: ' . $url_format . ' and endpoint: ' . $module . '/' . $function);
                        log_message('info', 'Working URL details: ' . json_encode($result));
                        break 2; // Break out of both loops
                    } else {
                        $error_message = $result['message'];
                        log_message('error', 'cPanel connection failed with URL ' . $url_format . ' and endpoint ' . $module . '/' . $function . ': ' . $error_message);
                        // Continue to next endpoint
                    }
                }
                
                // Restore original URL if no success
                if (!$success) {
                    $this->cpanel_url = $original_url;
                }
            }
            
            if ($success) {
                // Update the working URL
                $this->cpanel_url = $working_url;
                log_message('info', 'Found working cPanel URL: ' . $working_url);
                $this->session->set_flashdata('success', 'Koneksi ke cPanel berhasil! URL yang bekerja: ' . $working_url);
            } else {
                $this->session->set_flashdata('error', 'Koneksi ke cPanel gagal setelah mencoba ' . count($url_formats) . ' format URL dan ' . count($endpoints) . ' endpoint. Error terakhir: ' . $error_message . '. Periksa konfigurasi cPanel di file config/cpanel_config.php. Pastikan username, password, dan domain sudah benar. Username: ' . $this->cpanel_username . ', Domain: ' . $this->cpanel_domain . '. Coba akses cPanel secara manual untuk memastikan kredensial benar.');
            }
            
            redirect('email_management');
        } catch (Exception $e) {
            log_message('error', 'Error in test_connection: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menguji koneksi: ' . $e->getMessage());
            redirect('email_management');
        }
    }
}
