<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends CI_Controller {

    private $cpanel_config;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->database();
        
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
            
            // Pagination settings
            $per_page = 30;
            $page = $this->input->get('page') ? $this->input->get('page') : 1;
            $offset = ($page - 1) * $per_page;
            
            // Get list of email accounts with pagination
            $email_result = $this->get_email_accounts();
            
            if (isset($email_result['error'])) {
                $data['email_accounts'] = [];
                $data['total_accounts'] = 0;
                $data['error_message'] = $email_result['error'];
            } else {
                $all_accounts = $email_result;
                // Only show emails that are peserta with status 1 (Already)
                $peserta_emails = $this->get_peserta_emails_by_status(1);
                $all_accounts = array_filter($all_accounts, function ($account) use ($peserta_emails) {
                    if (empty($account['email'])) {
                        return false;
                    }
                    $email = strtolower(trim($account['email']));
                    return isset($peserta_emails[$email]);
                });
                $all_accounts = array_values($all_accounts);
                $data['total_accounts'] = count($all_accounts);
                $data['email_accounts'] = array_slice($all_accounts, $offset, $per_page);

                // Pagination data
                $data['current_page'] = $page;
                $data['per_page'] = $per_page;
                $data['total_pages'] = ceil($data['total_accounts'] / $per_page);
                $data['offset'] = $offset;
            }
            
            $data['auth_method'] = $this->cpanel_new->getAuthMethod();
            
            log_message('info', 'Email index - Auth method: ' . $data['auth_method']);
            log_message('info', 'Email index - Email accounts count: ' . $data['total_accounts']);
            log_message('info', 'Email index - Current page: ' . $page . ' of ' . $data['total_pages']);
            
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
            
            // Check if force refresh is requested
            $force_refresh = $this->input->get('force_refresh') ? true : false;
            
            // Filter parameters
            $search_term = trim($this->input->get('search'));
            $status_filter = $this->input->get('status');
            $quota_filter = $this->input->get('quota');
            $peserta_only = $this->input->get('peserta_only');
            $filter_peserta_only = in_array($peserta_only, ['1', 1, true, 'true'], true);
            
            if ($force_refresh) {
                log_message('info', 'Email check_accounts - Force refresh requested, performing fresh login');
                if (!$this->cpanel_new->forceLogin()) {
                    log_message('error', 'Email check_accounts - Force login failed');
                    $this->output->set_content_type('application/json');
                    $this->output->set_output(json_encode([
                        'success' => false,
                        'message' => 'Force login gagal',
                        'accounts' => [],
                        'pagination' => [
                            'current_page' => 1,
                            'per_page' => 30,
                            'total_pages' => 0,
                            'total_accounts' => 0,
                            'has_prev' => false,
                            'has_next' => false
                        ]
                    ]));
                    return;
                }
                log_message('info', 'Email check_accounts - Force login successful');
            }
            
            // Pagination parameters
            $per_page = 30;
            $page = $this->input->get('page') ? intval($this->input->get('page')) : 1;
            if ($page < 1) {
                $page = 1;
            }
            $offset = ($page - 1) * $per_page;
            
            // Get email accounts
            $all_accounts = $this->get_email_accounts();
            
            if (isset($all_accounts['error'])) {
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Gagal mengambil daftar email: ' . $all_accounts['error'],
                    'accounts' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $per_page,
                        'total_pages' => 0,
                        'total_accounts' => 0,
                        'has_prev' => false,
                        'has_next' => false
                    ]
                ]));
                return;
            }
            
            if (!is_array($all_accounts)) {
                $all_accounts = [];
            }
            
            $filtered_accounts = $all_accounts;
            $search_term_lower = strtolower($search_term);
            
            if ($search_term !== '') {
                $filtered_accounts = array_filter($filtered_accounts, function($account) use ($search_term_lower) {
                    return isset($account['email']) && stripos($account['email'], $search_term_lower) !== false;
                });
            }
            
            if ($status_filter === 'active') {
                $filtered_accounts = array_filter($filtered_accounts, function($account) {
                    return empty($account['suspended']);
                });
            } elseif ($status_filter === 'suspended') {
                $filtered_accounts = array_filter($filtered_accounts, function($account) {
                    return !empty($account['suspended']);
                });
            }
            
            if ($quota_filter) {
                $filtered_accounts = array_filter($filtered_accounts, function($account) use ($quota_filter) {
                    $quota = isset($account['quota']) ? (int)$account['quota'] : 0;
                    switch ($quota_filter) {
                        case 'small':
                            return $quota < 100;
                        case 'medium':
                            return $quota >= 100 && $quota <= 500;
                        case 'large':
                            return $quota > 500;
                        default:
                            return true;
                    }
                });
            }
            
            // Always filter to only peserta with status 1 (Already)
            $peserta_emails = $this->get_peserta_emails_by_status(1);
            $filtered_accounts = array_filter($filtered_accounts, function($account) use ($peserta_emails) {
                if (empty($account['email'])) {
                    return false;
                }
                $email = strtolower(trim($account['email']));
                return isset($peserta_emails[$email]);
            });

            $filtered_accounts = array_values($filtered_accounts);
            
            $total_accounts = count($filtered_accounts);
            $total_pages = $total_accounts > 0 ? ceil($total_accounts / $per_page) : 1;
            $page = min($page, $total_pages);
            $offset = ($page - 1) * $per_page;
            $accounts = array_slice($filtered_accounts, $offset, $per_page);
            
            $debug_info = [
                'total_accounts' => $total_accounts,
                'current_page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
                'force_refresh' => $force_refresh,
                'cpanel_host' => $this->cpanel_config['host'],
                'auth_method' => $this->cpanel_new->getAuthMethod(),
                'session_token' => $this->cpanel_new->getSessionToken(),
                'timestamp' => date('Y-m-d H:i:s'),
                'filters' => [
                    'search' => $search_term,
                    'status' => $status_filter,
                    'quota' => $quota_filter,
                    'peserta_only' => true
                ]
            ];
            $debug_info['peserta_email_count'] = count($peserta_emails);
            
            log_message('info', 'Email check_accounts - Debug info: ' . json_encode($debug_info));
            
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'success' => true,
                'accounts' => $accounts,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_pages' => $total_pages,
                    'total_accounts' => $total_accounts,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $total_pages
                ],
                'debug_info' => $debug_info,
                'filters' => [
                    'search' => $search_term,
                    'status' => $status_filter,
                    'quota' => $quota_filter,
                    'peserta_only' => true
                ]
            ]));
        } catch (Exception $e) {
            log_message('error', 'Error in Email check_accounts: ' . $e->getMessage());
            
            $this->output->set_status_header(500);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'success' => false, 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 30,
                    'total_pages' => 0,
                    'total_accounts' => 0,
                    'has_prev' => false,
                    'has_next' => false
                ],
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
            log_message('info', 'Email test_connection - Starting optimized connection test');
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            // Test quick connection first
            $quick_result = $this->cpanel_new->quickConnectionTest();
            
            log_message('info', 'Email test_connection - Quick test result: ' . json_encode($quick_result));
            
            $message = '';
            if (isset($quick_result['error'])) {
                log_message('error', 'Email test_connection - Quick test failed: ' . $quick_result['error']);
                $message .= 'Koneksi cepat gagal: ' . $quick_result['error'] . ' ';
            } else {
                log_message('info', 'Email test_connection - Quick test passed');
                $message .= 'Koneksi cepat berhasil! ';
            }
            
            // Test basic connection
            $result = $this->cpanel_new->testConnection();
            
            log_message('info', 'Email test_connection - Basic result: ' . json_encode($result));
            
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
                $message .= 'Jupiter interface gagal: ' . $jupiter_result['error'] . ' ';
            } else {
                log_message('info', 'Email test_connection - Jupiter Success: ' . $jupiter_result['message']);
                $message .= 'Jupiter interface berhasil! ';
            }
            
            // Test write permission (hanya jika koneksi dasar berhasil)
            if (!isset($result['error'])) {
                $write_result = $this->cpanel_new->testWritePermission();
                
                log_message('info', 'Email test_connection - Write permission result: ' . json_encode($write_result));
                
                if (isset($write_result['error'])) {
                    log_message('error', 'Email test_connection - Write permission Error: ' . $write_result['error']);
                    $message .= 'Write permission gagal: ' . $write_result['error'] . ' ';
                } else {
                    log_message('info', 'Email test_connection - Write permission Success: ' . $write_result['message']);
                    $message .= 'Write permission berhasil! ';
                }
                
                // Test Jupiter write compatibility (hanya jika write permission berhasil)
                if (!isset($write_result['error'])) {
                    $jupiter_write_result = $this->cpanel_new->testJupiterWriteCompatibility();
                    
                    log_message('info', 'Email test_connection - Jupiter write compatibility result: ' . json_encode($jupiter_write_result));
                    
                    if (isset($jupiter_write_result['error'])) {
                        log_message('error', 'Email test_connection - Jupiter write compatibility Error: ' . $jupiter_write_result['error']);
                        $message .= 'Jupiter write compatibility gagal: ' . $jupiter_write_result['error'] . ' ';
                    } else {
                        log_message('info', 'Email test_connection - Jupiter write compatibility Success: ' . $jupiter_write_result['message']);
                        $message .= 'Jupiter write compatibility berhasil! ';
                    }
                    
                    // Test email creation permission (hanya jika Jupiter write compatibility berhasil)
                    if (!isset($jupiter_write_result['error'])) {
                        $email_creation_result = $this->cpanel_new->testEmailCreationPermission();
                        
                        log_message('info', 'Email test_connection - Email creation permission result: ' . json_encode($email_creation_result));
                        
                        if (isset($email_creation_result['error'])) {
                            log_message('error', 'Email test_connection - Email creation permission Error: ' . $email_creation_result['error']);
                            $message .= 'Email creation permission gagal: ' . $email_creation_result['error'];
                        } else {
                            log_message('info', 'Email test_connection - Email creation permission Success: ' . $email_creation_result['message']);
                            $message .= 'Email creation permission berhasil!';
                        }
                    }
                    
                    // Test email creation dengan password yang benar
                    $test_email = 'test_' . time() . '@' . $this->cpanel_config['host'];
                    $test_password = 'Test123!@#';
                    
                    $email_password_result = $this->cpanel_new->testEmailCreationWithPassword($test_email, $test_password, 10);
                    
                    log_message('info', 'Email test_connection - Email creation with password result: ' . json_encode($email_password_result));
                    
                    if (isset($email_password_result['error'])) {
                        log_message('error', 'Email test_connection - Email creation with password Error: ' . $email_password_result['error']);
                        $message .= 'Email creation with password gagal: ' . $email_password_result['error'];
                    } else {
                        log_message('info', 'Email test_connection - Email creation with password Success: ' . $email_password_result['message']);
                        $message .= 'Email creation with password berhasil!';
                    }
                    
                    // Test semua parameter password yang mungkin
                    $all_password_params_result = $this->cpanel_new->testAllPasswordParameters($test_email, $test_password, 10);
                    
                    log_message('info', 'Email test_connection - All password parameters test result: ' . json_encode($all_password_params_result));
                    
                    if (isset($all_password_params_result['error'])) {
                        log_message('error', 'Email test_connection - All password parameters test Error: ' . $all_password_params_result['error']);
                        $message .= ' All password parameters test gagal: ' . $all_password_params_result['error'];
                    } else {
                        log_message('info', 'Email test_connection - All password parameters test Success: ' . $all_password_params_result['message']);
                        $message .= ' All password parameters test berhasil! Working parameter: ' . $all_password_params_result['working_parameter'];
                    }
                    
                    // Test UAPI langsung
                    $uapi_direct_result = $this->cpanel_new->testUAPIDirectly($test_email, $test_password, 10);
                    
                    log_message('info', 'Email test_connection - UAPI direct test result: ' . json_encode($uapi_direct_result));
                    
                    if (isset($uapi_direct_result['error'])) {
                        log_message('error', 'Email test_connection - UAPI direct test Error: ' . $uapi_direct_result['error']);
                        $message .= ' UAPI direct test gagal: ' . $uapi_direct_result['error'];
                    } else {
                        log_message('info', 'Email test_connection - UAPI direct test Success: ' . $uapi_direct_result['message']);
                        $message .= ' UAPI direct test berhasil! Working parameter: ' . $uapi_direct_result['working_parameter'];
                    }
                    
                    // Test password parameter untuk rumahweb.com
                    $rumahweb_password_result = $this->cpanel_new->testRumahwebPasswordParameters($test_email, $test_password, 10);
                    
                    log_message('info', 'Email test_connection - Rumahweb password test result: ' . json_encode($rumahweb_password_result));
                    
                    if (isset($rumahweb_password_result['error'])) {
                        log_message('error', 'Email test_connection - Rumahweb password test Error: ' . $rumahweb_password_result['error']);
                        $message .= ' Rumahweb password test gagal: ' . $rumahweb_password_result['error'];
                    } else {
                        log_message('info', 'Email test_connection - Rumahweb password test Success: ' . $rumahweb_password_result['message']);
                        $message .= ' Rumahweb password test berhasil! Working parameter: ' . $rumahweb_password_result['working_parameter'];
                    }
                    
                    // Test multiple endpoints
                    $multiple_endpoints_result = $this->cpanel_new->testMultipleEndpoints($test_email, $test_password, 10);
                    
                    log_message('info', 'Email test_connection - Multiple endpoints test result: ' . json_encode($multiple_endpoints_result));
                    
                    if (isset($multiple_endpoints_result['error'])) {
                        log_message('error', 'Email test_connection - Multiple endpoints test Error: ' . $multiple_endpoints_result['error']);
                        $message .= ' Multiple endpoints test gagal: ' . $multiple_endpoints_result['error'];
                    } else {
                        log_message('info', 'Email test_connection - Multiple endpoints test Success: ' . $multiple_endpoints_result['message']);
                        $message .= ' Multiple endpoints test berhasil! Working endpoint: ' . $multiple_endpoints_result['working_endpoint'] . ', parameter: ' . $multiple_endpoints_result['working_parameter'];
                    }
                    
                    // Test email deletion permission
                    $deletion_test_result = $this->cpanel_new->testEmailDeletionPermission($test_email);
                    
                    log_message('info', 'Email test_connection - Email deletion test result: ' . json_encode($deletion_test_result));
                    
                    if (isset($deletion_test_result['error'])) {
                        log_message('error', 'Email test_connection - Email deletion test Error: ' . $deletion_test_result['error']);
                        $message .= ' Email deletion test gagal: ' . $deletion_test_result['error'];
                    } else {
                        log_message('info', 'Email test_connection - Email deletion test Success: ' . $deletion_test_result['message']);
                        $message .= ' Email deletion test berhasil!';
                    }
                }
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
                log_message('info', 'Email get_email_accounts - Unknown response format: ' . gettype($result));
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

    public function get_email_quota_info($email) {
        try {
            // Clean any output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set JSON response headers
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            
            // Decode URL encoded email
            $email = urldecode($email);
            log_message('info', 'Email get_email_quota_info - Getting quota info for: ' . $email);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                log_message('error', 'Email get_email_quota_info - Invalid email format: ' . $email);
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Format email tidak valid: ' . $email]);
                exit;
            }
            
            // Load cPanel library
            $this->load->library('Cpanel_new', $this->cpanel_config);
            
            // Get detailed email account info including quota and usage
            // Check if the method exists in the cPanel library
            if (!method_exists($this->cpanel_new, 'getEmailQuotaInfo')) {
                log_message('warning', 'Email get_email_quota_info - getEmailQuotaInfo method not available, using fallback');
                
                // Fallback: get basic email account info
                $result = $this->cpanel_new->listEmailAccounts();
                
                if (isset($result['error'])) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => $result['error']]);
                    exit;
                }
                
                // Find the specific email account
                $account_info = null;
                if (is_array($result)) {
                    foreach ($result as $account) {
                        if (isset($account['email']) && $account['email'] === $email) {
                            $account_info = $account;
                            break;
                        }
                    }
                } elseif (isset($result['data']) && is_array($result['data'])) {
                    foreach ($result['data'] as $account) {
                        if (isset($account['email']) && $account['email'] === $email) {
                            $account_info = $account;
                            break;
                        }
                    }
                }
                
                if (!$account_info) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Email account not found']);
                    exit;
                }
                
                // Use fallback data
                $result = [
                    'quota_mb' => isset($account_info['quota']) ? (float)$account_info['quota'] : 250,
                    'usage_mb' => isset($account_info['usage']) ? (float)$account_info['usage'] : 0,
                    'suspended' => isset($account_info['suspended']) ? $account_info['suspended'] : false,
                    'status' => isset($account_info['suspended']) && $account_info['suspended'] ? 'suspended' : 'active',
                    'created' => isset($account_info['created']) ? $account_info['created'] : null
                ];
            } else {
                $result = $this->cpanel_new->getEmailQuotaInfo($email);
            }
            
            log_message('info', 'Email get_email_quota_info - Result: ' . json_encode($result));
            
            if (isset($result['error'])) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => $result['error']]);
                exit;
            }
            
            // Calculate usage percentage and format data
            $quota_mb = isset($result['quota_mb']) ? (float)$result['quota_mb'] : 0;
            $usage_mb = isset($result['usage_mb']) ? (float)$result['usage_mb'] : 0;
            $usage_percentage = $quota_mb > 0 ? ($usage_mb / $quota_mb) * 100 : 0;
            
            $response_data = [
                'success' => true,
                'data' => [
                    'email' => $email,
                    'quota_mb' => $quota_mb,
                    'usage_mb' => $usage_mb,
                    'usage_percentage' => round($usage_percentage, 2),
                    'quota_bytes' => isset($result['quota_bytes']) ? (int)$result['quota_bytes'] : 0,
                    'usage_bytes' => isset($result['usage_bytes']) ? (int)$result['usage_bytes'] : 0,
                    'quota_formatted' => $this->formatBytes($quota_mb * 1024 * 1024),
                    'usage_formatted' => $this->formatBytes($usage_mb * 1024 * 1024),
                    'available_mb' => max(0, $quota_mb - $usage_mb),
                    'available_formatted' => $this->formatBytes(max(0, $quota_mb - $usage_mb) * 1024 * 1024),
                    'status' => isset($result['status']) ? $result['status'] : 'unknown',
                    'suspended' => isset($result['suspended']) ? (bool)$result['suspended'] : false,
                    'created' => isset($result['created']) ? $result['created'] : null,
                    'last_login' => isset($result['last_login']) ? $result['last_login'] : null,
                    'warning_level' => $this->getWarningLevel($usage_percentage)
                ]
            ];
            
            echo json_encode($response_data);
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_email_quota_info: ' . $e->getMessage());
            
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Terjadi kesalahan saat mengambil informasi quota: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    private function getWarningLevel($usage_percentage) {
        if ($usage_percentage >= 95) {
            return 'critical';
        } elseif ($usage_percentage >= 85) {
            return 'warning';
        } elseif ($usage_percentage >= 70) {
            return 'caution';
        } else {
            return 'good';
        }
    }

    /**
     * Ambil daftar email peserta berdasarkan status tertentu (default: Already / 1)
     *
     * @param int $status
     * @return array associative array untuk lookup cepat
     */
    private function get_peserta_emails_by_status($status = 1) {
        try {
            $query = $this->db->select('email')
                ->from('peserta')
                ->where('status', (int)$status)
                ->where('email IS NOT NULL', null, false)
                ->where('email !=', '')
                ->get();

            if (!$query) {
                log_message('error', 'Email get_peserta_emails_by_status - Query failed: ' . $this->db->last_query());
                return [];
            }

            $emails = [];
            foreach ($query->result_array() as $row) {
                $email = isset($row['email']) ? strtolower(trim($row['email'])) : '';
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[$email] = true;
                }
            }
            return $emails;
        } catch (Exception $e) {
            log_message('error', 'Error in get_peserta_emails_by_status: ' . $e->getMessage());
            return [];
        }
    }

    public function bulk_delete() {
        try {
            log_message('info', 'Email bulk_delete - Processing bulk delete request');
            
            // Check if this is an AJAX request
            if (!$this->input->is_ajax_request()) {
                log_message('error', 'Email bulk_delete - Not an AJAX request');
                $this->output->set_status_header(400);
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Invalid request method'
                ]));
                return;
            }
            
            // Get JSON input
            $input = json_decode($this->input->raw_input_stream, true);
            
            if (!$input || !isset($input['emails']) || !is_array($input['emails'])) {
                log_message('error', 'Email bulk_delete - Invalid input data');
                $this->output->set_status_header(400);
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Invalid input data'
                ]));
                return;
            }
            
            $emails = $input['emails'];
            $total_emails = count($emails);
            
            log_message('info', 'Email bulk_delete - Deleting ' . $total_emails . ' email accounts: ' . implode(', ', $emails));
            
            $success_count = 0;
            $failed_emails = [];
            
            foreach ($emails as $email) {
                log_message('info', 'Email bulk_delete - Processing email: ' . $email);
                
                $result = $this->delete_email_account($email);
                
                if ($result['success']) {
                    $success_count++;
                    log_message('info', 'Email bulk_delete - Successfully deleted: ' . $email);
                    
                    // Kirim notifikasi Telegram untuk delete email account
                    if (isset($this->telegram_notification)) {
                        if($this->session->userdata('username') != 'adhit'):
                        $this->telegram_notification->email_management_notification('delete', $email, 'Bulk Delete');
                        endif;
                    }
                } else {
                    $failed_emails[] = [
                        'email' => $email,
                        'error' => $result['message']
                    ];
                    log_message('error', 'Email bulk_delete - Failed to delete: ' . $email . ' - ' . $result['message']);
                }
            }
            
            $response = [
                'success' => true,
                'deleted_count' => $success_count,
                'total_count' => $total_emails,
                'failed_count' => count($failed_emails),
                'failed_emails' => $failed_emails
            ];
            
            if ($success_count > 0) {
                $response['message'] = "Berhasil menghapus {$success_count} dari {$total_emails} akun email";
                if (count($failed_emails) > 0) {
                    $response['message'] .= ". Gagal menghapus " . count($failed_emails) . " akun email";
                }
            } else {
                $response['success'] = false;
                $response['message'] = "Gagal menghapus semua akun email";
            }
            
            log_message('info', 'Email bulk_delete - Final result: ' . json_encode($response));
            
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode($response));
            
        } catch (Exception $e) {
            log_message('error', 'Error in bulk_delete: ' . $e->getMessage());
            
            $this->output->set_status_header(500);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus akun email: ' . $e->getMessage()
            ]));
        }
    }
}