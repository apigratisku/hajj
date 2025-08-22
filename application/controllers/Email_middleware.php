<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_middleware extends CI_Controller {

    private $middleware_url;

    public function __construct() {
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
        
        // Set middleware URL - adjust this to your hosting path
        $this->middleware_url = 'https://menfins.site/cpanel_email_middleware.php';
    }

    public function index() {
        try {
            $data['title'] = 'Manajemen Email (Middleware)';
            
            // Get list of email accounts
            $data['email_accounts'] = $this->get_email_accounts();
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/index', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_middleware index: ' . $e->getMessage());
            show_error('Terjadi kesalahan saat memuat halaman. Silakan coba lagi.', 500);
        }
    }

    public function create() {
        try {
            $data['title'] = 'Tambah Akun Email';
            
            if ($this->input->post()) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');
                $quota = $this->input->post('quota', true) ?: 250;
                
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
                
                redirect('email_middleware');
            }
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/create', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_middleware create: ' . $e->getMessage());
            show_error('Terjadi kesalahan saat membuat akun email. Silakan coba lagi.', 500);
        }
    }

    public function edit($email) {
        try {
            $data['title'] = 'Edit Akun Email';
            $data['email'] = urldecode($email);
            
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
                
                redirect('email_middleware');
            }
            
            $this->load->view('templates/sidebar');
            $this->load->view('templates/header', $data);
            $this->load->view('email_management/edit', $data);
            $this->load->view('templates/footer');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_middleware edit: ' . $e->getMessage());
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
            
            redirect('email_middleware');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_middleware delete: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menghapus akun email.');
            redirect('email_middleware');
        }
    }

    public function check_accounts() {
        try {
            // Remove AJAX check to allow direct access for debugging
            $result = $this->call_middleware('list');
            
            // Log the result for debugging
            log_message('info', 'Check accounts result: ' . json_encode($result));
            
            if (!$result['success']) {
                $this->output->set_status_header(500);
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode([
                    'success' => false, 
                    'message' => $result['message'],
                    'debug_info' => [
                        'middleware_url' => $this->middleware_url,
                        'timestamp' => date('Y-m-d H:i:s'),
                        'error_details' => $result
                    ]
                ]));
                return;
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
            
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'success' => true,
                'accounts' => $accounts,
                'debug_info' => [
                    'total_accounts' => count($accounts),
                    'middleware_url' => $this->middleware_url,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]));
        } catch (Exception $e) {
            log_message('error', 'Error in Email_middleware check_accounts: ' . $e->getMessage());
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
            $result = $this->call_middleware('test');
            
            // Log the test result for debugging
            log_message('info', 'Test connection result: ' . json_encode($result));
            
            if ($result['success']) {
                $user_info = isset($result['data']['data']) ? $result['data']['data'] : [];
                $username = isset($user_info['user']) ? $user_info['user'] : 'Unknown';
                $this->session->set_flashdata('success', 'Koneksi ke cPanel berhasil! User: ' . $username);
            } else {
                $this->session->set_flashdata('error', 'Koneksi ke cPanel gagal: ' . $result['message'] . '. Pastikan file cpanel_email_middleware.php sudah diupload ke root hosting.');
            }
            
            redirect('email_middleware');
        } catch (Exception $e) {
            log_message('error', 'Error in Email_middleware test_connection: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menguji koneksi: ' . $e->getMessage());
            redirect('email_middleware');
        }
    }

    private function call_middleware($action, $params = []) {
        try {
            $url = $this->middleware_url . '?action=' . $action;
            
            // Add parameters to URL
            foreach ($params as $key => $value) {
                $url .= '&' . urlencode($key) . '=' . urlencode($value);
            }
            
            log_message('info', 'Calling middleware URL: ' . $url);
            
            // Use cURL instead of file_get_contents for better SSL handling
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'CodeIgniter-Email-Middleware/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_ENCODING, ''); // Accept any encoding
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            // Log detailed response for debugging
            log_message('info', 'Middleware Response HTTP Code: ' . $http_code);
            log_message('info', 'Middleware Response Content-Type: ' . $content_type);
            log_message('info', 'Middleware Response Length: ' . strlen($response));
            log_message('info', 'Middleware Response (first 500 chars): ' . substr($response, 0, 500));
            
            if ($error) {
                log_message('error', 'cURL error: ' . $error);
                return ['success' => false, 'message' => 'Connection error: ' . $error];
            }
            
            if ($http_code !== 200) {
                log_message('error', 'HTTP error: ' . $http_code . ' - Response: ' . $response);
                return ['success' => false, 'message' => 'HTTP error: ' . $http_code . ' - ' . $response];
            }
            
            if ($response === false || empty($response)) {
                log_message('error', 'Empty response from middleware: ' . $url);
                return ['success' => false, 'message' => 'Empty response from middleware'];
            }
            
            // Check if response starts with PHP tags or HTML (indicating PHP error)
            if (strpos($response, '<?php') !== false || strpos($response, '<!DOCTYPE') !== false || strpos($response, '<html') !== false) {
                log_message('error', 'Response contains PHP/HTML instead of JSON: ' . substr($response, 0, 200));
                return ['success' => false, 'message' => 'Server returned PHP/HTML instead of JSON. Check middleware file.'];
            }
            
            // Check if response is valid JSON
            $data = json_decode($response, true);
            $json_error = json_last_error();
            
            if ($json_error !== JSON_ERROR_NONE) {
                log_message('error', 'JSON decode error: ' . json_last_error_msg());
                log_message('error', 'Raw response: ' . $response);
                
                // Try to clean the response
                $clean_response = trim($response);
                $clean_response = preg_replace('/[\x00-\x1F\x7F]/', '', $clean_response);
                
                $data = json_decode($clean_response, true);
                $json_error = json_last_error();
                
                if ($json_error !== JSON_ERROR_NONE) {
                    return ['success' => false, 'message' => 'Invalid JSON response: ' . json_last_error_msg() . ' - Response: ' . substr($response, 0, 200)];
                }
            }
            
            if (isset($data['error'])) {
                log_message('error', 'Middleware error: ' . $data['error']);
                return ['success' => false, 'message' => $data['error']];
            }
            
            return ['success' => true, 'data' => $data];
        } catch (Exception $e) {
            log_message('error', 'Error calling middleware: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error calling middleware: ' . $e->getMessage()];
        }
    }

    private function get_email_accounts() {
        try {
            $result = $this->call_middleware('list');
            
            if (!$result['success']) {
                log_message('error', 'Failed to get email accounts: ' . $result['message']);
                $this->session->set_flashdata('error', 'Gagal mengambil daftar email: ' . $result['message']);
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
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat mengambil daftar email: ' . $e->getMessage());
            return [];
        }
    }

    private function create_email_account($email, $password, $quota) {
        try {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Format email tidak valid'];
            }
            
            $result = $this->call_middleware('create', [
                'email' => $email,
                'password' => $password,
                'quota' => $quota
            ]);
            
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
            $params = ['email' => $email, 'quota' => $quota];
            
            if (!empty($password)) {
                $params['password'] = $password;
            }
            
            $result = $this->call_middleware('update', $params);
            
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
            $result = $this->call_middleware('delete', ['email' => $email]);
            
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
    
    // Debug function to test middleware directly
    public function debug_middleware() {
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        $action = $this->input->get('action') ?: 'test';
        $result = $this->call_middleware($action);
        
        echo "<h2>Middleware Debug</h2>";
        echo "<p><strong>Action:</strong> " . $action . "</p>";
        echo "<p><strong>URL:</strong> " . $this->middleware_url . "?action=" . $action . "</p>";
        echo "<p><strong>Result:</strong></p>";
        echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        
        if (isset($result['data'])) {
            echo "<p><strong>Data:</strong></p>";
            echo "<pre>" . json_encode($result['data'], JSON_PRETTY_PRINT) . "</pre>";
        }
        
        // Add direct middleware test
        echo "<hr>";
        echo "<h3>Direct Middleware Test</h3>";
        echo "<p><a href='" . $this->middleware_url . "?action=" . $action . "' target='_blank'>Test Direct: " . $this->middleware_url . "?action=" . $action . "</a></p>";
        
        // Add cURL test
        echo "<h3>cURL Test</h3>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->middleware_url . "?action=" . $action);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Debug-Test/1.0');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "<p><strong>HTTP Code:</strong> " . $http_code . "</p>";
        echo "<p><strong>cURL Error:</strong> " . ($error ?: 'None') . "</p>";
        echo "<p><strong>Response:</strong></p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}
