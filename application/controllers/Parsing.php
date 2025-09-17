<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parsing extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Increase memory and execution time limits
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 600); // 10 minutes
        ini_set('max_input_time', 600);
        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '100M');
        ini_set('max_file_uploads', 1);
        
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }
        
        // Disable CodeIgniter's output buffering for JSON responses
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->output->enable_profiler(FALSE);
        }
        
        // Log server configuration
        log_message('info', 'Parsing controller initialized - Memory: ' . ini_get('memory_limit') . ', Max execution time: ' . ini_get('max_execution_time'));
    }

    public function index()
    {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        $data['title'] = 'Parsing Data VISA';
        
        // Get parsing statistics if database is available
        try {
            $this->load->model('Parsing_model');
            $data['stats'] = $this->Parsing_model->get_parsing_statistics();
        } catch (Exception $e) {
            $data['stats'] = array(
                'total_records' => 0,
                'today_records' => 0,
                'month_records' => 0,
                'unique_passports' => 0
            );
        }
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('parsing/index', $data);
        $this->load->view('templates/footer');
    }

    public function view_data()
    {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        $data['title'] = 'Data Parsing VISA';
        
        // Get search parameter
        $search = $this->input->get('search');
        $data['search'] = $search;
        
        // Get parsing statistics if database is available
        try {
            $this->load->model('Parsing_model');
            $data['stats'] = $this->Parsing_model->get_parsing_statistics();
            
            // Get paginated data
            $page = $this->input->get('page') ?: 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            // Build query with search
            $this->db->select('*');
            $this->db->from('visa_data');
            
            // Add search condition if search term exists
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('nama', $search);
                $this->db->or_like('passport_no', $search);
                $this->db->or_like('visa_no', $search);
                $this->db->group_end();
            }
            
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit($limit, $offset);
            $query = $this->db->get();
            $data['parsing_data'] = $query->result_array();
            
            // Get total count for pagination (with search)
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('nama', $search);
                $this->db->or_like('passport_no', $search);
                $this->db->or_like('visa_no', $search);
                $this->db->group_end();
            }
            $data['total_records'] = $this->db->count_all_results('visa_data');
            $data['total_pages'] = ceil($data['total_records'] / $limit);
            $data['current_page'] = $page;
            $data['offset'] = $offset;
            
        } catch (Exception $e) {
            log_message('error', 'Error loading visa data: ' . $e->getMessage());
            $data['stats'] = array(
                'total_records' => 0,
                'today_records' => 0,
                'month_records' => 0,
                'unique_passports' => 0
            );
            $data['parsing_data'] = array();
            $data['total_records'] = 0;
            $data['total_pages'] = 0;
            $data['current_page'] = 1;
            $data['offset'] = 0;
        }
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('parsing/view_data', $data);
        $this->load->view('templates/footer');
    }

    public function delete_data($id = null)
    {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu');
            redirect('auth');
        }

        // Validate ID
        if (!$id || !is_numeric($id)) {
            $this->session->set_flashdata('error', 'ID data tidak valid');
            redirect('parsing/view_data');
        }

        try {
            // Check if record exists
            $this->db->where('id', $id);
            $query = $this->db->get('visa_data');
            
            if ($query->num_rows() == 0) {
                $this->session->set_flashdata('error', 'Data tidak ditemukan');
                redirect('parsing/view_data');
            }

            $record = $query->row();
            
            // Delete the record
            $this->db->where('id', $id);
            $result = $this->db->delete('visa_data');
            
            if ($result) {
                log_message('info', 'Deleted visa data: ID ' . $id . ', Name: ' . $record->nama);
                $this->session->set_flashdata('success', 'Data untuk ' . $record->nama . ' berhasil dihapus');
            } else {
                log_message('error', 'Failed to delete visa data: ID ' . $id);
                $this->session->set_flashdata('error', 'Gagal menghapus data');
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error deleting visa data: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menghapus data');
        }
        
        redirect('parsing/view_data');
    }

    public function test()
    {
        try {
            // Clean output buffer at the start
            $this->clean_output_buffer();
            
        // Test endpoint untuk debugging
        if (!$this->session->userdata('logged_in')) {
            return $this->json(401, array('error' => 'Anda harus login terlebih dahulu'));
        }

        // Test response
        return $this->json(200, array(
            'success' => true,
            'message' => 'Parsing endpoint berfungsi dengan baik',
            'timestamp' => date('Y-m-d H:i:s'),
            'user' => $this->session->userdata('username'),
            'server_info' => array(
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            )
        ));
        } catch (Throwable $e) {
            $this->clean_output_buffer();
            return $this->json(500, array(
                'error' => 'Test endpoint error: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        }
    }

    public function simple_test()
    {
        try {
            // Clean output buffer at the start
            $this->clean_output_buffer();
            
        // Simple test endpoint tanpa authentication
        $test_data = array(
            'success' => true,
            'message' => 'Simple test endpoint berfungsi',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_info' => array(
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            )
        );

        return $this->json(200, $test_data);
        } catch (Throwable $e) {
            $this->clean_output_buffer();
            return $this->json(500, array(
                'error' => 'Simple test endpoint error: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        }
    }

    public function debug()
    {
        // Debug endpoint untuk melihat server response
        if (!$this->session->userdata('logged_in')) {
            return $this->json(401, array('error' => 'Anda harus login terlebih dahulu'));
        }

        // Test multiple response formats
        $test_data = array(
            'success' => true,
            'message' => 'Debug endpoint berfungsi',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_info' => array(
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'output_buffering' => ini_get('output_buffering'),
                'zlib_output_compression' => ini_get('zlib_output_compression')
            ),
            'request_info' => array(
                'method' => $_SERVER['REQUEST_METHOD'],
                'content_type' => isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'not set',
                'content_length' => isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'not set',
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'not set'
            ),
            'session_info' => array(
                'logged_in' => $this->session->userdata('logged_in'),
                'username' => $this->session->userdata('username'),
                'session_id' => $this->session->userdata('session_id')
            )
        );

        return $this->json(200, $test_data);
    }

    public function debug_text()
    {
        // Debug endpoint untuk melihat teks yang diekstrak dari PDF
        if (!$this->session->userdata('logged_in')) {
            return $this->json(401, array('error' => 'Anda harus login terlebih dahulu'));
        }

        // Check if file is uploaded
        if (!isset($_FILES['pdf']) || !isset($_FILES['pdf']['tmp_name']) || $_FILES['pdf']['tmp_name'] === '') {
            return $this->json(400, array('error' => 'File PDF tidak ditemukan atau file kosong'));
        }

        $pdfPath = $_FILES['pdf']['tmp_name'];
        
        // Extract text from PDF
        $text = '';
        $extraction_method = '';
        
        // Try smalot/pdfparser first for debugging
        if (class_exists('\\Smalot\\PdfParser\\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($pdfPath);
                $text = $pdf->getText();
                $extraction_method = 'smalot/pdfparser';
            } catch (Exception $e) {
                log_message('error', 'Smalot PDF parse error: '.$e->getMessage());
            }
        }
        
        // Normalize text
        $normalized_text = $this->normalize_text($text);
        
        return $this->json(200, array(
            'success' => true,
            'extraction_method' => $extraction_method,
            'original_text_length' => strlen($text),
            'normalized_text_length' => strlen($normalized_text),
            'first_1000_chars' => substr($normalized_text, 0, 1000),
            'last_1000_chars' => substr($normalized_text, -1000),
            'text_preview' => array(
                'lines' => array_slice(explode("\n", $normalized_text), 0, 20), // First 20 lines
                'total_lines' => count(explode("\n", $normalized_text))
            ),
            'file_info' => array(
                'name' => $_FILES['pdf']['name'],
                'size' => $_FILES['pdf']['size'],
                'type' => $_FILES['pdf']['type']
            )
        ));
    }

    public function health_check()
    {
        // Simple health check endpoint tanpa authentication
        $health_data = array(
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_info' => array(
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_file_uploads' => ini_get('max_file_uploads')
            ),
            'codeigniter_info' => array(
                'version' => CI_VERSION,
                'base_url' => base_url(),
                'site_url' => site_url()
            ),
            'session_info' => array(
                'logged_in' => $this->session->userdata('logged_in'),
                'username' => $this->session->userdata('username')
            )
        );

        return $this->json(200, $health_data);
    }

    public function test_upload()
    {
        try {
            // Clean output buffer at the start
            $this->clean_output_buffer();
            
            // Test upload endpoint untuk debugging
            $test_data = array(
                'success' => true,
                'message' => 'Upload test endpoint berfungsi',
                'timestamp' => date('Y-m-d H:i:s'),
                'request_info' => array(
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'content_type' => isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'not set',
                    'content_length' => isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'not set'
                ),
                'files_info' => array(
                    'files_received' => isset($_FILES) ? count($_FILES) : 0,
                    'pdf_uploaded' => isset($_FILES['pdf']) ? true : false
                )
            );

            if (isset($_FILES['pdf'])) {
                $test_data['file_details'] = array(
                    'name' => $_FILES['pdf']['name'],
                    'size' => $_FILES['pdf']['size'],
                    'type' => $_FILES['pdf']['type'],
                    'error' => $_FILES['pdf']['error'],
                    'tmp_name' => $_FILES['pdf']['tmp_name'] ? 'set' : 'not set'
                );
            }

            return $this->json(200, $test_data);
        } catch (Throwable $e) {
            $this->clean_output_buffer();
            return $this->json(500, array(
                'error' => 'Test upload endpoint error: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        }
    }

    public function parse()
    {
        // Enhanced parse method with robust output handling
        try {
            // Disable CodeIgniter output completely
            $this->output->enable_profiler(FALSE);
            
            // Clean output buffer completely
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers first
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('X-Content-Type-Options: nosniff');
                http_response_code(200);
            }
            
            // Log start of parsing
            log_message('info', 'Parse method started');
            
            // Basic validation
            if (!$this->session->userdata('logged_in')) {
                $response = array('error' => 'Anda harus login terlebih dahulu');
                log_message('error', 'Parse failed: User not logged in');
                echo json_encode($response);
                exit;
            }
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response = array('error' => 'Method not allowed');
                log_message('error', 'Parse failed: Invalid method ' . $_SERVER['REQUEST_METHOD']);
                echo json_encode($response);
                exit;
            }
            
            if (!isset($_FILES['pdf'])) {
                $response = array('error' => 'File PDF tidak ditemukan');
                log_message('error', 'Parse failed: No PDF file uploaded');
                echo json_encode($response);
                exit;
            }
            
            if (!isset($_FILES['pdf']['tmp_name']) || $_FILES['pdf']['tmp_name'] === '') {
                $response = array('error' => 'File PDF kosong');
                log_message('error', 'Parse failed: PDF file is empty');
                echo json_encode($response);
                exit;
            }
            
            if ($_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
                $response = array('error' => 'Error upload file: ' . $_FILES['pdf']['error']);
                log_message('error', 'Parse failed: Upload error ' . $_FILES['pdf']['error']);
                echo json_encode($response);
                exit;
            }
            
            // Get file path
            $pdfPath = $_FILES['pdf']['tmp_name'];
            log_message('info', 'PDF file path: ' . $pdfPath);
            
            // Check if file exists and is readable
            if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
                $response = array('error' => 'File tidak dapat dibaca');
                log_message('error', 'Parse failed: File not readable - ' . $pdfPath);
                echo json_encode($response);
                exit;
            }
            
            log_message('info', 'File validation passed, starting text extraction');
            
            // Try to extract text with multiple methods
            $text = '';
            $extraction_method = '';
            
            log_message('info', 'Starting text extraction with multiple methods');
            
            // Method 1: Try smalot/pdfparser first
            if (class_exists('\\Smalot\\PdfParser\\Parser')) {
                try {
                    log_message('info', 'Trying Smalot PDF Parser');
                    $text = $this->extract_with_smalot($pdfPath);
                    if ($text && trim($text) !== '') {
                        $extraction_method = 'smalot/pdfparser';
                        log_message('info', 'Smalot extraction successful, text length: ' . strlen($text));
                    } else {
                        log_message('info', 'Smalot extraction returned empty text');
                    }
                } catch (Exception $e) {
                    log_message('error', 'Smalot extraction failed: ' . $e->getMessage());
                }
            } else {
                log_message('info', 'Smalot PDF Parser not available');
            }
            
            // Method 2: Try pdftotext if smalot failed
            if (!$text || trim($text) === '') {
                try {
                    log_message('info', 'Trying pdftotext');
            $text = $this->extract_with_pdftotext($pdfPath);
            if ($text && trim($text) !== '') {
                $extraction_method = 'pdftotext';
                log_message('info', 'pdftotext extraction successful, text length: ' . strlen($text));
            } else {
                        log_message('info', 'pdftotext extraction returned empty text');
                    }
                } catch (Exception $e) {
                    log_message('error', 'pdftotext extraction failed: ' . $e->getMessage());
                }
            }
            
            // Method 3: Try alternative pdftotext options
            if (!$text || trim($text) === '') {
                try {
                    log_message('info', 'Trying alternative pdftotext');
                    $text = $this->extract_with_pdftotext_alternative($pdfPath);
                if ($text && trim($text) !== '') {
                        $extraction_method = 'pdftotext_alternative';
                        log_message('info', 'Alternative pdftotext extraction successful, text length: ' . strlen($text));
                } else {
                        log_message('info', 'Alternative pdftotext extraction returned empty text');
                    }
                } catch (Exception $e) {
                    log_message('error', 'Alternative pdftotext extraction failed: ' . $e->getMessage());
                }
            }

            // Method 4: Try chunked processing for large files
            if (!$text || trim($text) === '') {
                try {
                    log_message('info', 'Trying chunked processing');
                    $text = $this->extract_with_chunked_processing($pdfPath);
                if ($text && trim($text) !== '') {
                        $extraction_method = 'chunked_processing';
                        log_message('info', 'Chunked processing extraction successful, text length: ' . strlen($text));
                } else {
                        log_message('info', 'Chunked processing extraction returned empty text');
                    }
                } catch (Exception $e) {
                    log_message('error', 'Chunked processing extraction failed: ' . $e->getMessage());
                }
            }
            
            // Method 5: Try basic file reading (for text-based PDFs)
            if (!$text || trim($text) === '') {
                try {
                    log_message('info', 'Trying basic file reading');
                    $text = $this->extract_with_basic_reading($pdfPath);
                    if ($text && trim($text) !== '') {
                        $extraction_method = 'basic_reading';
                        log_message('info', 'Basic reading extraction successful, text length: ' . strlen($text));
                    } else {
                        log_message('info', 'Basic reading extraction returned empty text');
                    }
                } catch (Exception $e) {
                    log_message('error', 'Basic reading extraction failed: ' . $e->getMessage());
                }
            }
            
            if (!$text || trim($text) === '') {
                $error_response = array(
                    'error' => 'Gagal mengekstrak teks dari PDF',
                    'debug_info' => array(
                        'file_size' => filesize($pdfPath),
                        'file_readable' => is_readable($pdfPath),
                        'smalot_available' => class_exists('\\Smalot\\PdfParser\\Parser'),
                        'pdftotext_available' => $this->check_pdftotext_availability()
                    )
                );
                log_message('error', 'All extraction methods failed');
                echo json_encode($error_response);
                exit;
            }
            
            log_message('info', 'Text extraction successful using: ' . $extraction_method);
            
            // Normalize text
            log_message('info', 'Normalizing text, original length: ' . strlen($text));
            $text = $this->normalize_text($text);
            log_message('info', 'Text normalized, new length: ' . strlen($text));
            
            // Split text into pages for multi-page processing
            $pages = $this->split_into_pages($text);
            log_message('info', 'Split PDF into ' . count($pages) . ' pages for processing');
            
            // Process each page
            $all_parsed_data = array();
            $successful_pages = 0;
            
            foreach ($pages as $page_index => $page_text) {
                try {
                    log_message('info', 'Processing page ' . ($page_index + 1) . ' of ' . count($pages) . ', text length: ' . strlen($page_text));
                    
                    // Parse each page
                $parsed_data = $this->parse_visa_page($page_text);
                
                    // Add page information
                    $parsed_data['page_number'] = $page_index + 1;
                    $parsed_data['raw'] = substr($page_text, 0, 1000); // Store first 1000 chars for debugging
                    
                    // Filter out "Stay Days" data completely
                    $is_stay_days = false;
                    
                    // Check if any field contains "Stay Days"
                    foreach ($parsed_data as $key => $value) {
                        if (!empty($value) && preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $value)) {
                            $is_stay_days = true;
                            log_message('info', 'Filtering out Stay Days data on page ' . ($page_index + 1) . ': ' . $key . ' = ' . $value);
                            break;
                        }
                    }
                    
                    // Only add if we found some data and it's not "Stay Days" data
                    if ((!empty($parsed_data['nama']) || !empty($parsed_data['visa_no']) || !empty($parsed_data['passport_no'])) && !$is_stay_days) {
                        $all_parsed_data[] = $parsed_data;
                        $successful_pages++;
                        log_message('info', 'Successfully parsed page ' . ($page_index + 1) . ': ' . 
                                  ($parsed_data['nama'] ?: 'No name') . ' - ' . 
                                  ($parsed_data['visa_no'] ?: 'No visa') . ' - ' . 
                                  ($parsed_data['passport_no'] ?: 'No passport'));
                } else {
                        if ($is_stay_days) {
                            log_message('info', 'Stay Days data filtered out on page ' . ($page_index + 1));
                        } else {
                            log_message('info', 'No data found on page ' . ($page_index + 1));
                        }
                    }
                    
                } catch (Exception $e) {
                    log_message('error', 'Error parsing page ' . ($page_index + 1) . ': ' . $e->getMessage());
                    // Continue with next page
                }
            }
            
            log_message('info', 'Page processing completed. Found ' . count($all_parsed_data) . ' records from ' . $successful_pages . ' successful pages');
            
            // Try to save data to database
            $saved_count = 0;
            if (!empty($all_parsed_data)) {
                try {
                    log_message('info', 'Attempting to save ' . count($all_parsed_data) . ' records to database');
                    $this->load->model('Parsing_model');
                    $saved_count = $this->Parsing_model->save_parsed_data($all_parsed_data);
                    log_message('info', 'Saved ' . $saved_count . ' records to database');
                } catch (Exception $e) {
                    log_message('error', 'Error saving to database: ' . $e->getMessage());
                }
            }

            // Prepare response
            $response_data = array(
                'success' => true,
                'count' => count($all_parsed_data),
                'saved_count' => $saved_count,
                'total_pages' => count($pages),
                'successful_pages' => $successful_pages,
                'data' => $all_parsed_data,
                'extraction_method' => $extraction_method,
                'file_info' => array(
                    'name' => $_FILES['pdf']['name'],
                    'size' => $_FILES['pdf']['size'],
                    'type' => $_FILES['pdf']['type']
                )
            );
            
            // Log response data for debugging
            log_message('info', 'Preparing response with ' . count($all_parsed_data) . ' records');
            log_message('info', 'Response data structure: ' . json_encode(array_keys($response_data)));
            
            // Ensure clean output
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Final output
            $json_output = json_encode($response_data);
            log_message('info', 'JSON output length: ' . strlen($json_output));
            
            // Ensure output is sent
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Force output
            echo $json_output;
            flush();
            exit;
            
        } catch (Exception $e) {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            
            log_message('error', 'Parse method Exception: ' . $e->getMessage());
            
            echo json_encode(array(
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
            exit;
        } catch (Error $e) {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            
            log_message('error', 'Parse method Error: ' . $e->getMessage());
            
            echo json_encode(array(
                'error' => 'Terjadi kesalahan fatal: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
            exit;
        } catch (Throwable $e) {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            
            log_message('error', 'Parse method Throwable: ' . $e->getMessage());
            
            $error_response = array(
                'error' => 'Terjadi kesalahan tidak terduga: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            
            echo json_encode($error_response);
            flush();
            exit;
        }
        
        // Fallback response - this should never be reached, but just in case
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        
        $fallback_response = array(
            'error' => 'Unexpected end of parsing method',
            'debug_info' => array(
                'message' => 'Parse method completed without proper response'
            )
        );
        
        echo json_encode($fallback_response);
        flush();
        exit;
    }


    private function extract_with_pdftotext($pdfPath)
    {
        try {
        $bin = 'pdftotext';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $bin = 'C:\Users\adhit\scoop\apps\poppler\current\bin\pdftotext.exe'; // Adjust path if needed
        }
        
        // Check if pdftotext is available
        $output = null;
        $return_var = null;
        @exec($bin . ' -v 2>&1', $output, $return_var);
        
        if ($return_var !== 0) {
                // Try alternative paths for Windows
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $alternative_paths = [
                        'C:\\poppler\\bin\C:\Users\adhit\scoop\apps\poppler\current\bin\pdftotext.exe',
                        'C:\\Program Files\\poppler\\bin\\pdftotext.exe',
                        'C:\\Program Files (x86)\\poppler\\bin\\pdftotext.exe',
                        'pdftotext.exe'
                    ];
                    
                    foreach ($alternative_paths as $alt_path) {
                        @exec($alt_path . ' -v 2>&1', $output, $return_var);
                        if ($return_var === 0) {
                            $bin = $alt_path;
                            break;
                        }
                    }
                    
                    if ($return_var !== 0) {
                        return null; // pdftotext not found
                    }
                } else {
                    return null; // pdftotext not found on non-Windows
                }
            }
            
            // Check if file exists and is readable
            if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
            return null;
        }
        
            // Try different pdftotext options
            $options = [
                '-layout -nopgbrk',
                '-raw -nopgbrk',
                '-table -nopgbrk',
                '-simple -nopgbrk',
                '-nopgbrk',
                ''
            ];
            
            foreach ($options as $option) {
                $cmd = escapeshellcmd($bin) . ' ' . $option . ' ' . escapeshellarg($pdfPath) . ' -';
        $out = @shell_exec($cmd);
        
                if ($out !== null && trim($out) !== '') {
                    return $out;
                }
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function extract_with_pdftotext_alternative($pdfPath)
    {
        try {
        $bin = 'pdftotext';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $bin = 'C:\\poppler\\Library\\bin\\pdftotext.exe';
        }
            
            // Check if pdftotext is available
            $output = null;
            $return_var = null;
            @exec($bin . ' -v 2>&1', $output, $return_var);
            
            if ($return_var !== 0) {
                return null;
            }
            
            // Check if file exists and is readable
            if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
                return null;
        }
        
        $options = array(
            '-raw -nopgbrk',
            '-table -nopgbrk',
            '-simple -nopgbrk',
            '-layout',
            '-nopgbrk'
        );
        
        foreach ($options as $option) {
            $cmd = escapeshellcmd($bin) . ' ' . $option . ' ' . escapeshellarg($pdfPath) . ' -';
            $out = @shell_exec($cmd);
            if ($out !== null && trim($out) !== '') {
                return $out;
            }
        }
        
        return null;
        } catch (Exception $e) {
        return null;
        }
    }

    private function extract_with_smalot($pdfPath)
    {
        try {
        // Check if Smalot PDF Parser is available
        if (!class_exists('\\Smalot\\PdfParser\\Parser')) {
            // Try to load Composer autoload if not loaded
            $autoloadPaths = [
                FCPATH . 'vendor/autoload.php',
                APPPATH . 'vendor/autoload.php',
                BASEPATH . 'vendor/autoload.php'
            ];
            
            foreach ($autoloadPaths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    break;
                }
            }
            
            if (!class_exists('\\Smalot\\PdfParser\\Parser')) {
                return null;
            }
        }
        
            // Check if file exists and is readable
            if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
                return null;
            }
            
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();
            
            if ($text && trim($text) !== '') {
                return $text;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        } catch (Error $e) {
            return null;
        }
    }

    private function extract_with_chunked_processing($pdfPath)
    {
        // For large files, try to extract text in chunks to avoid memory issues
        try {
            $bin = 'pdftotext';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $bin = 'C:\\poppler\\Library\\bin\\pdftotext.exe';
            }
            
            // Check if pdftotext is available
            $output = null;
            $return_var = null;
            @exec($bin . ' -v 2>&1', $output, $return_var);
            
            if ($return_var !== 0) {
                return null;
            }
            
            // Check if file exists and is readable
            if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
                return null;
            }
            
            // Try with minimal memory usage options
            $cmd = escapeshellcmd($bin) . ' -nopgbrk -raw ' . escapeshellarg($pdfPath) . ' -';
            $out = @shell_exec($cmd);
            
            if ($out !== null && trim($out) !== '') {
                // Limit text size to prevent memory issues
                $max_text_size = 1024 * 1024; // 1MB max
                if (strlen($out) > $max_text_size) {
                    $out = substr($out, 0, $max_text_size);
                }
                return $out;
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function extract_with_basic_reading($pdfPath)
    {
        // Try to extract text from PDF using basic file reading
        // This works for some text-based PDFs
        try {
            if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
                return null;
            }
            
            // Read file content
            $content = file_get_contents($pdfPath);
            if (!$content) {
                return null;
            }
            
            // Try to extract text using regex patterns
            $text = '';
            
            // Pattern 1: Extract text between BT and ET markers (PDF text objects)
            if (preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $matches)) {
                foreach ($matches[1] as $match) {
                    // Extract text from Tj and TJ operators
                    if (preg_match_all('/\((.*?)\)\s*Tj/', $match, $textMatches)) {
                        foreach ($textMatches[1] as $textMatch) {
                            $text .= $textMatch . ' ';
                        }
                    }
                    if (preg_match_all('/\[(.*?)\]\s*TJ/', $match, $textMatches)) {
                        foreach ($textMatches[1] as $textMatch) {
                            // Extract text from array format
                            if (preg_match_all('/\((.*?)\)/', $textMatch, $innerMatches)) {
                                foreach ($innerMatches[1] as $innerMatch) {
                                    $text .= $innerMatch . ' ';
                                }
                            }
                        }
                    }
                }
            }
            
            // Pattern 2: Extract text from stream objects
            if (empty($text) && preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $matches)) {
                foreach ($matches[1] as $match) {
                    // Try to decode and extract text
                    $decoded = @gzuncompress($match);
                    if ($decoded === false) {
                        $decoded = $match;
                    }
                    
                    // Look for text patterns
                    if (preg_match_all('/\((.*?)\)\s*Tj/', $decoded, $textMatches)) {
                        foreach ($textMatches[1] as $textMatch) {
                            $text .= $textMatch . ' ';
                        }
                    }
                }
            }
            
            // Clean up the text
            $text = trim($text);
            $text = preg_replace('/\s+/', ' ', $text);
            
            return !empty($text) ? $text : null;
            
        } catch (Exception $e) {
            return null;
        }
    }

    private function normalize_text($text)
    {
        try {
            if (empty($text) || !is_string($text)) {
                return '';
            }
            
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        $text = preg_replace('/[ \t\f\v]+/', ' ', $text);
        $text = preg_replace('/ *\n */', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return $text;
        } catch (Exception $e) {
            log_message('error', 'Error in normalize_text: ' . $e->getMessage());
            return $text; // Return original text if normalization fails
        }
    }

    private function split_into_pages($text)
    {
        try {
            // Enhanced page splitting for multiple visa documents
        $patterns = [
                // Pattern 1: Form feed character (most reliable for PDF page breaks)
                '/\f/',
                // Pattern 2: Split by visa document headers
                '/\b(?:Kingdom of Saudi Arabia|Visa No|Passport No|Full Name|Name|Visa|رﻗﻢ اﻟﺘﺄﺷﻴﺮة)\b/iu',
                // Pattern 3: Page numbers
            '/Page \d+/i',
                '/صفحة \d+/iu',
                // Pattern 4: Document separators (multiple newlines)
                '/\n\s*\n\s*\n\s*\n/',
                // Pattern 5: Visa number patterns (new document indicator)
                '/\b[A-Z]?\d{7,15}\s*(?:\*E\d+\*)?\b/',
                // Pattern 6: Passport number patterns
                '/\b[A-Z]\d{6,9}\b/',
                // Pattern 7: Split by common visa document patterns
                '/\b(?:Visa|VISA|visa)\s*(?:No|Number|#)\s*[:\-]?\s*[A-Z]?\d{7,15}/i',
                // Pattern 8: Split by passport patterns
                '/\b(?:Passport|PASSPORT|passport)\s*(?:No|Number|#)\s*[:\-]?\s*[A-Z]\d{6,9}/i'
            ];
            
            $best_split = null;
            $max_parts = 1;
            $best_pattern = '';
            
            foreach ($patterns as $index => $pattern) {
                $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            $parts = array_map('trim', $parts);
                $parts = array_filter($parts, function($part) {
                    return strlen($part) > 100; // Filter out very small parts
                });
                
                if (count($parts) > $max_parts) {
                    $best_split = array_values($parts);
                    $max_parts = count($parts);
                    $best_pattern = 'Pattern ' . ($index + 1);
                }
            }
            
            // If we found a good split, use it
            if ($best_split && count($best_split) > 1) {
                log_message('info', 'Split text into ' . count($best_split) . ' pages using ' . $best_pattern);
                return $best_split;
            }
            
            // Fallback: Try to split by length if text is very long
            if (strlen($text) > 3000) {
                $chunk_size = 1500; // Split into chunks of 1500 characters
                $parts = array();
                $lines = explode("\n", $text);
                $current_chunk = '';
                
                foreach ($lines as $line) {
                    if (strlen($current_chunk . $line) > $chunk_size && !empty($current_chunk)) {
                        $parts[] = trim($current_chunk);
                        $current_chunk = $line;
                    } else {
                        $current_chunk .= $line . "\n";
                    }
                }
                
                if (!empty($current_chunk)) {
                    $parts[] = trim($current_chunk);
                }
            
            if (count($parts) > 1) {
                    log_message('info', 'Split text into ' . count($parts) . ' chunks by length (fallback)');
                    return $parts;
            }
        }
        
            // If no good split found, return the whole text as a single page
            log_message('info', 'No page split found, using single page');
        return array($text);
            
        } catch (Exception $e) {
            log_message('error', 'Error in split_into_pages: ' . $e->getMessage());
            return array($text); // Return original text as single page
        }
    }

    private function parse_visa_page($page_text)
    {
        $result = array(
            'nama' => null,
            'visa_no' => null,
            'passport_no' => null,
            'tanggal_lahir' => null
        );
        
        try {
            // Validate input
            if (empty($page_text) || !is_string($page_text)) {
                log_message('error', 'Invalid page text provided to parse_visa_page');
                return $result;
            }
        
        // Log the page text for debugging (first 1000 chars)
        log_message('info', 'Parsing page text (first 1000 chars): ' . substr($page_text, 0, 1000));
        
        // Log the full page text for debugging (if not too long)
        if (strlen($page_text) < 5000) {
            log_message('info', 'Full page text: ' . $page_text);
        } else {
            log_message('info', 'Page text too long (' . strlen($page_text) . ' chars), showing first 2000 chars: ' . substr($page_text, 0, 2000));
        }
        
        // Log all numbers found in the text for debugging
        if (preg_match_all('/\b(\d{7,15})\b/', $page_text, $matches)) {
            log_message('info', 'All numbers found in text: ' . implode(', ', $matches[1]));
        }
        
        // Log all shorter numbers found in the text for debugging
        if (preg_match_all('/\b(\d{4,15})\b/', $page_text, $matches)) {
            log_message('info', 'All numbers (4-15 digits) found in text: ' . implode(', ', $matches[1]));
        }
        
        // Log all alphanumeric patterns found in the text for debugging
        if (preg_match_all('/\b([A-Za-z]\d{6,9})\b/', $page_text, $matches)) {
            log_message('info', 'All alphanumeric patterns found in text: ' . implode(', ', $matches[1]));
        }
        
        // Log all alphanumeric patterns (shorter) found in the text for debugging
        if (preg_match_all('/\b([A-Za-z]\d{4,10})\b/', $page_text, $matches)) {
            log_message('info', 'All alphanumeric patterns (4-10 chars) found in text: ' . implode(', ', $matches[1]));
        }
        
        // Log all dates found in the text for debugging
        if (preg_match_all('/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\b/', $page_text, $matches)) {
            log_message('info', 'All dates found in text: ' . implode(', ', $matches[1]));
        }
        
        // Log all names found in the text for debugging
        if (preg_match_all('/\b([A-Z][A-Z\s\.\'-]{3,})\b/', $page_text, $matches)) {
            log_message('info', 'All name-like patterns found in text: ' . implode(', ', $matches[1]));
        }
        
        // Log all words found in the text for debugging
        if (preg_match_all('/\b([A-Za-z]{3,})\b/', $page_text, $matches)) {
            log_message('info', 'All words (3+ chars) found in text: ' . implode(', ', $matches[1]));
        }
        
        // Extract Visa Number - enhanced patterns for multiple pages
        $visa_patterns = [
            '/Visa\s*No\.?\s*[:\-]?\s*([A-Z]?\d{7,15})/i',
            '/رﻗﻢ اﻟﺘﺄﺷﻴﺮة\s*[:\-]?\s*([A-Z]?\d{7,15})/iu',
            '/\b(\d{10})\s*(?:\*E\d+\*)/i', // Pattern for page 1: 6065933915 *E263448017*
            '/Visa\s*[:\-]?\s*([A-Z]?\d{7,15})/i',
            '/E\s*Visa\s*[:\-]?\s*([A-Z]?\d{7,15})/i',
            '/\b([A-Z]\d{9,15})\b/', // General pattern for visa numbers
            '/\b(\d{10,15})\b/', // General pattern for numeric visa numbers
            // Additional patterns for multiple pages
            '/Visa\s*Number\s*[:\-]?\s*([A-Z]?\d{7,15})/i',
            '/Visa\s*#\s*[:\-]?\s*([A-Z]?\d{7,15})/i',
            '/\b([A-Z]{1,3}\d{7,12})\b/', // Pattern for visa with letters
            '/\b(\d{8,15})\b/', // Pattern for long numeric visa numbers
            // More specific patterns for visa extraction
            '/Visa\s*No\s*[:\-]?\s*([A-Z]{1,2}\d{8,12})/i',
            '/Visa\s*Number\s*[:\-]?\s*([A-Z]{1,2}\d{8,12})/i',
            '/\b([A-Z]{1,2}\d{8,12})\b/', // Pattern for visa with 1-2 letters + 8-12 digits
            '/\b(\d{9,12})\b/', // Pattern for 9-12 digit visa numbers
            '/Visa\s*[:\-]?\s*([A-Z]{1,3}\d{7,15})/i',
            // NEW: Specific patterns based on e-visa document format
            '/Visa\s*No\.\s*[:\-]?\s*(\d{10})/i', // Exact 10-digit visa: 6065933915
            '/رﻗﻢ اﻟﺘﺄﺷﻴﺮة\s*[:\-]?\s*(\d{10})/iu', // Arabic label with 10 digits
            '/\b(\d{10})\b/', // Standalone 10-digit numbers
            '/Visa\s*No\.\s*(\d{10})/i', // Visa No. followed by 10 digits
            '/Visa\s*Number\s*(\d{10})/i', // Visa Number followed by 10 digits
            '/\b(\d{10})\s*(?:Date|Valid|Duration)/i', // 10 digits followed by date/valid/duration
            '/Visa\s*[:\-]?\s*(\d{10})/i', // Visa: followed by 10 digits
            '/E\s*Visa\s*[:\-]?\s*(\d{10})/i', // E Visa: followed by 10 digits
            '/\b(\d{10})\s*(?:\*E\d+\*)/i', // 10 digits with E-code suffix
            // NEW: Additional patterns for multiple e-visa formats
            '/رقم التأشيرة\s*[:\-]?\s*(\d{10})/iu', // Arabic: رقم التأشيرة
            '/Visa\s*No\s*[:\-]?\s*(\d{10})/i', // Visa No (without period)
            '/Visa\s*Number\s*[:\-]?\s*(\d{10})/i', // Visa Number (without period)
            '/\b(\d{10})\s*(?:Valid|from|until)/i', // 10 digits before Valid/from/until
            '/\b(\d{10})\s*(?:Duration|Stay)/i', // 10 digits before Duration/Stay
            '/\b(\d{10})\s*(?:Passport|No)/i', // 10 digits before Passport/No
            '/\b(\d{10})\s*(?:Place|issue)/i', // 10 digits before Place/issue
            '/\b(\d{10})\s*(?:Full|Name)/i', // 10 digits before Full/Name
            '/\b(\d{10})\s*(?:Nationality|الجنسية)/i', // 10 digits before Nationality
            '/\b(\d{10})\s*(?:Birth|Date)/i', // 10 digits before Birth/Date
            '/\b(\d{10})\s*(?:Type|Visa)/i', // 10 digits before Type/Visa
            '/\b(\d{10})\s*(?:Umrah|عمرة)/i', // 10 digits before Umrah
            '/\b(\d{10})\s*(?:Operator|مكتب)/i', // 10 digits before Operator
            '/\b(\d{10})\s*(?:Agent|الوكيل)/i', // 10 digits before Agent
            '/\b(\d{10})\s*(?:Border|الحدود)/i' // 10 digits before Border
        ];
        
        log_message('info', 'Trying to extract visa number with ' . count($visa_patterns) . ' patterns');
        
        // Log all visa patterns for debugging
        foreach ($visa_patterns as $i => $pattern) {
            log_message('info', 'Visa pattern ' . ($i + 1) . ': ' . $pattern);
        }
        
        // Try to find all visa numbers in the text
        $visa_numbers = array();
        foreach ($visa_patterns as $pattern) {
            if (preg_match_all($pattern, $page_text, $matches)) {
                foreach ($matches[1] as $match) {
                    $visa_numbers[] = trim($match);
                }
            }
        }
        
        // Additional context-based extraction for visa numbers
        // Look for visa numbers in table format: "Visa No. / رقم التأشيرة: 6065933915"
        if (preg_match('/Visa\s*No\.?\s*\/\s*رﻗﻢ اﻟﺘﺄﺷﻴﺮة\s*[:\-]?\s*(\d{10})/iu', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // Look for visa numbers after "Visa No." label
        if (preg_match('/Visa\s*No\.?\s*[:\-]?\s*(\d{10})/i', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // Look for visa numbers in e-visa format
        if (preg_match('/e-visa.*?(\d{10})/i', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // NEW: Additional context-based extraction for multiple formats
        // Look for visa numbers in KSA VISA format
        if (preg_match('/KSA\s*VISA.*?(\d{10})/i', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // Look for visa numbers in EVISA format
        if (preg_match('/EVISA.*?(\d{10})/i', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // Look for visa numbers before "Valid from" or "Valid until"
        if (preg_match('/(\d{10})\s*(?:Valid\s*from|Valid\s*until|صالح)/i', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // Look for visa numbers before "Duration of Stay"
        if (preg_match('/(\d{10})\s*(?:Duration\s*of\s*Stay|مدة الإقامة)/i', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // FORMAT 1 SPECIFIC: Look for visa numbers in traditional e-visa table structure
        // Pattern: "Visa No. / رقم التأشيرة: 6065933915" (exact format from Format 1)
        if (preg_match('/Visa\s*No\.\s*\/\s*رقم\s*التأشيرة\s*[:\-]?\s*(\d{10})/iu', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // FORMAT 1 SPECIFIC: Look for visa numbers in exact table format
        // Pattern: "Visa No. / رقم التأشيرة: 6065933915" with exact spacing
        if (preg_match('/Visa\s*No\.\s*\/\s*رقم\s*التأشيرة\s*:\s*(\d{10})/iu', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // FORMAT 1 SPECIFIC: Look for visa numbers in table with exact Arabic text
        // Pattern: "Visa No. / رقم التأشيرة: 6065933915" with proper Arabic characters
        if (preg_match('/Visa\s*No\.\s*\/\s*رقم\s*التأشيرة\s*:\s*(\d{10})/iu', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // FORMAT 1 SPECIFIC: Look for visa numbers in table row format
        // Pattern: "Visa No." followed by colon and 10 digits
        if (preg_match('/Visa\s*No\.\s*[:\-]\s*(\d{10})/i', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // FORMAT 1 SPECIFIC: Look for visa numbers in Arabic table format
        // Pattern: "رقم التأشيرة" followed by colon and 10 digits
        if (preg_match('/رقم\s*التأشيرة\s*[:\-]\s*(\d{10})/iu', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // FORMAT 1 SPECIFIC: Look for visa numbers in barcode section
        // Pattern: "Visa No. / رقم التأشيرة: 6065933915" in barcode area
        if (preg_match('/Visa\s*No\.\s*\/\s*رقم\s*التأشيرة\s*[:\-]?\s*(\d{10})\s*(?:App\.\s*No\.|رقم\s*الطلب)/iu', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // FORMAT 1 SPECIFIC: Look for visa numbers with flexible spacing
        // Pattern: "Visa No. / رقم التأشيرة: 6065933915" with any spacing
        if (preg_match('/Visa\s*No\.\s*\/\s*رقم\s*التأشيرة\s*[:\-]?\s*(\d{10})/iu', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // FORMAT 1 SPECIFIC: Look for visa numbers in table structure with flexible format
        // Pattern: "Visa No. / رقم التأشيرة: 6065933915" with flexible spacing and punctuation
        if (preg_match('/Visa\s*No\.?\s*\/\s*رقم\s*التأشيرة\s*[:\-]?\s*(\d{10})/iu', $page_text, $matches)) {
            $visa_numbers[] = trim($matches[1]);
        }
        
        // AGGRESSIVE PATTERN: Look for any 10-digit number that could be a visa
        if (preg_match_all('/\b(\d{10})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Skip if it looks like a date or other non-visa number
                if (!preg_match('/^(19|20)\d{8}$/', $match) && // Not a year-based date
                    !preg_match('/^\d{2}\d{2}\d{6}$/', $match)) { // Not a date format
                    $visa_numbers[] = trim($match);
                }
            }
        }
        
        // AGGRESSIVE PATTERN: Look for visa numbers in any context
        if (preg_match_all('/\b(\d{8,12})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Only consider 10-digit numbers as potential visa numbers
                if (strlen($match) == 10 && !preg_match('/^(19|20)\d{8}$/', $match)) {
                    $visa_numbers[] = trim($match);
                }
            }
        }
        
        // ULTRA AGGRESSIVE PATTERN: Look for any number that could be a visa
        if (preg_match_all('/\b(\d{7,15})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Consider various visa number lengths
                if (strlen($match) >= 7 && strlen($match) <= 15 && 
                    !preg_match('/^(19|20)\d{6,8}$/', $match) && // Not a year-based date
                    !preg_match('/^\d{2}\d{2}\d{4,6}$/', $match)) { // Not a date format
                    $visa_numbers[] = trim($match);
                }
            }
        }
        
        // ULTRA AGGRESSIVE PATTERN: Look for numbers in table-like structures
        if (preg_match_all('/\b(\d{7,15})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Look for numbers that appear to be in table format
                if (strlen($match) >= 7 && strlen($match) <= 15) {
                    $visa_numbers[] = trim($match);
                }
            }
        }
        
        // Use the first valid visa number found
        if (!empty($visa_numbers)) {
            $result['visa_no'] = $visa_numbers[0];
            log_message('info', 'Found visa_no: ' . $result['visa_no'] . ' from ' . count($visa_numbers) . ' candidates');
        } else {
            log_message('info', 'No visa numbers found in page text');
            
            // FALLBACK: Try to find any number that looks like a visa
            if (preg_match_all('/\b(\d{7,12})\b/', $page_text, $matches)) {
                foreach ($matches[1] as $match) {
                    if (strlen($match) >= 7 && strlen($match) <= 12) {
                        $result['visa_no'] = trim($match);
                        log_message('info', 'Found visa_no (fallback): ' . $result['visa_no']);
                        break;
                    }
                }
            }
        }
        
        // Extract Passport Number - enhanced patterns for multiple pages
        $passport_patterns = [
            '/Passport\s*No\.?\s*[:\-]?\s*([A-Z]\d{6,9})/i',
            '/رﻗﻢ ﺟﻮاز اﻟﺴﻔﺮ\s*[:\-]?\s*([A-Z]\d{6,9})/iu',
            '/([A-Z]\d{6,9})\s*Passport/i',
            '/Passport\s*[:\-]?\s*([A-Z]\d{6,9})/i',
            '/\b([A-Z]\d{6,9})\b/', // General pattern for passport numbers
            '/No\.\s*([A-Z]\d{6,9})/i', // Pattern for "No. A1234567"
            // Additional patterns for multiple pages
            '/Passport\s*Number\s*[:\-]?\s*([A-Z]\d{6,9})/i',
            '/Passport\s*#\s*[:\-]?\s*([A-Z]\d{6,9})/i',
            '/\b([A-Z]{1,2}\d{6,9})\b/', // Pattern for passport with 1-2 letters
            '/\b([A-Z]\d{7,9})\b/', // Pattern for longer passport numbers
            // More specific patterns for passport extraction
            '/Passport\s*No\s*[:\-]?\s*([A-Z]{1,2}\d{6,9})/i',
            '/Passport\s*Number\s*[:\-]?\s*([A-Z]{1,2}\d{6,9})/i',
            '/\b([A-Z]{1,2}\d{6,9})\b/', // Pattern for passport with 1-2 letters + 6-9 digits
            '/\b([A-Z]\d{6,9})\b/', // Pattern for single letter + 6-9 digits
            '/Passport\s*[:\-]?\s*([A-Z]{1,3}\d{6,9})/i',
            '/\b([A-Z]{1,3}\d{6,9})\b/', // Pattern for 1-3 letters + 6-9 digits
            // NEW: Specific patterns based on e-visa document format
            '/Passport\s*No\.\s*[:\-]?\s*([A-Z]\d{7})/i', // Exact format: C5305905 (1 letter + 7 digits)
            '/رﻗﻢ ﺟﻮاز اﻟﺴﻔﺮ\s*[:\-]?\s*([A-Z]\d{7})/iu', // Arabic label with 1 letter + 7 digits
            '/\b([A-Z]\d{7})\b/', // Standalone 1 letter + 7 digits
            '/Passport\s*No\.\s*([A-Z]\d{7})/i', // Passport No. followed by 1 letter + 7 digits
            '/Passport\s*Number\s*([A-Z]\d{7})/i', // Passport Number followed by 1 letter + 7 digits
            '/\b([A-Z]\d{7})\s*(?:Full|Name|Nationality)/i', // 1 letter + 7 digits followed by Full/Name/Nationality
            '/Passport\s*[:\-]?\s*([A-Z]\d{7})/i', // Passport: followed by 1 letter + 7 digits
            '/No\.\s*([A-Z]\d{7})/i', // No. followed by 1 letter + 7 digits
            '/\b([A-Z]\d{7})\s*(?:Indonesia|Umrah|Type)/i', // 1 letter + 7 digits followed by Indonesia/Umrah/Type
            '/\b([A-Z]\d{7})\s*(?:Mahram|Name)/i', // 1 letter + 7 digits followed by Mahram/Name
            // NEW: Additional patterns for multiple e-visa formats
            '/رقم الجواز\s*[:\-]?\s*([A-Z]\d{7})/iu', // Arabic: رقم الجواز
            '/رقم جواز السفر\s*[:\-]?\s*([A-Z]\d{7})/iu', // Arabic: رقم جواز السفر
            '/Passport\s*No\s*[:\-]?\s*([A-Z]\d{7})/i', // Passport No (without period)
            '/Passport\s*Number\s*[:\-]?\s*([A-Z]\d{7})/i', // Passport Number (without period)
            '/\b([A-Z]\d{7})\s*(?:Valid|from|until)/i', // 1 letter + 7 digits before Valid/from/until
            '/\b([A-Z]\d{7})\s*(?:Duration|Stay)/i', // 1 letter + 7 digits before Duration/Stay
            '/\b([A-Z]\d{7})\s*(?:Place|issue)/i', // 1 letter + 7 digits before Place/issue
            '/\b([A-Z]\d{7})\s*(?:Full|Name|الإسم)/i', // 1 letter + 7 digits before Full/Name/الإسم
            '/\b([A-Z]\d{7})\s*(?:Nationality|الجنسية)/i', // 1 letter + 7 digits before Nationality/الجنسية
            '/\b([A-Z]\d{7})\s*(?:Birth|Date|تاريخ)/i', // 1 letter + 7 digits before Birth/Date/تاريخ
            '/\b([A-Z]\d{7})\s*(?:Type|Visa|نوع)/i', // 1 letter + 7 digits before Type/Visa/نوع
            '/\b([A-Z]\d{7})\s*(?:Umrah|عمرة)/i', // 1 letter + 7 digits before Umrah/عمرة
            '/\b([A-Z]\d{7})\s*(?:Operator|مكتب)/i', // 1 letter + 7 digits before Operator/مكتب
            '/\b([A-Z]\d{7})\s*(?:Agent|الوكيل)/i', // 1 letter + 7 digits before Agent/الوكيل
            '/\b([A-Z]\d{7})\s*(?:Border|الحدود)/i' // 1 letter + 7 digits before Border/الحدود
        ];
        
        log_message('info', 'Trying to extract passport number with ' . count($passport_patterns) . ' patterns');
        
        // Log all passport patterns for debugging
        foreach ($passport_patterns as $i => $pattern) {
            log_message('info', 'Passport pattern ' . ($i + 1) . ': ' . $pattern);
        }
        
        // Try to find all passport numbers in the text
        $passport_numbers = array();
        foreach ($passport_patterns as $pattern) {
            if (preg_match_all($pattern, $page_text, $matches)) {
                foreach ($matches[1] as $match) {
                    $passport_numbers[] = strtoupper(trim($match));
                }
            }
        }
        
        // Additional context-based extraction for passport numbers
        // Look for passport numbers in table format: "Passport No. / رقم جواز السفر: C5305905"
        if (preg_match('/Passport\s*No\.?\s*\/\s*رﻗﻢ ﺟﻮاز اﻟﺴﻔﺮ\s*[:\-]?\s*([A-Z]\d{7})/iu', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // Look for passport numbers after "Passport No." label
        if (preg_match('/Passport\s*No\.?\s*[:\-]?\s*([A-Z]\d{7})/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // Look for passport numbers in e-visa format
        if (preg_match('/e-visa.*?([A-Z]\d{7})/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // Look for passport numbers before "Full Name" or "Nationality"
        if (preg_match('/([A-Z]\d{7})\s*(?:Full\s*Name|Nationality|الإسم الكامل)/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // NEW: Additional context-based extraction for multiple formats
        // Look for passport numbers in KSA VISA format
        if (preg_match('/KSA\s*VISA.*?([A-Z]\d{7})/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // Look for passport numbers in EVISA format
        if (preg_match('/EVISA.*?([A-Z]\d{7})/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // Look for passport numbers before "Valid from" or "Valid until"
        if (preg_match('/([A-Z]\d{7})\s*(?:Valid\s*from|Valid\s*until|صالح)/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // Look for passport numbers before "Duration of Stay"
        if (preg_match('/([A-Z]\d{7})\s*(?:Duration\s*of\s*Stay|مدة الإقامة)/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // Look for passport numbers before "Place of issue"
        if (preg_match('/([A-Z]\d{7})\s*(?:Place\s*of\s*issue|مصدر التأشيرة)/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // Look for passport numbers before "Birth Date"
        if (preg_match('/([A-Z]\d{7})\s*(?:Birth\s*Date|تاريخ الميلاد)/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers in traditional e-visa table structure
        // Pattern: "Passport No. / رقم جواز السفر: C5305905" (exact format from Format 1)
        if (preg_match('/Passport\s*No\.\s*\/\s*رقم\s*جواز\s*السفر\s*[:\-]?\s*([A-Z]\d{7})/iu', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers in exact table format
        // Pattern: "Passport No. / رقم جواز السفر: C5305905" with exact spacing
        if (preg_match('/Passport\s*No\.\s*\/\s*رقم\s*جواز\s*السفر\s*:\s*([A-Z]\d{7})/iu', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers in table with exact Arabic text
        // Pattern: "Passport No. / رقم جواز السفر: C5305905" with proper Arabic characters
        if (preg_match('/Passport\s*No\.\s*\/\s*رقم\s*جواز\s*السفر\s*:\s*([A-Z]\d{7})/iu', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers in table row format
        // Pattern: "Passport No." followed by colon and 1 letter + 7 digits
        if (preg_match('/Passport\s*No\.\s*[:\-]\s*([A-Z]\d{7})/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers in Arabic table format
        // Pattern: "رقم جواز السفر" followed by colon and 1 letter + 7 digits
        if (preg_match('/رقم\s*جواز\s*السفر\s*[:\-]\s*([A-Z]\d{7})/iu', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers before "Place of issue"
        // Pattern: C5305905 followed by "Place of issue / مصدر التأشيرة"
        if (preg_match('/([A-Z]\d{7})\s*Place\s*of\s*issue\s*\/\s*مصدر\s*التأشيرة/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers before "Full Name"
        // Pattern: C5305905 followed by "Full Name / الإسم الكامل"
        if (preg_match('/([A-Z]\d{7})\s*Full\s*Name\s*\/\s*الإسم\s*الكامل/i', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers with flexible spacing
        // Pattern: "Passport No. / رقم جواز السفر: C5305905" with any spacing
        if (preg_match('/Passport\s*No\.\s*\/\s*رقم\s*جواز\s*السفر\s*[:\-]?\s*([A-Z]\d{7})/iu', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // FORMAT 1 SPECIFIC: Look for passport numbers in table structure with flexible format
        // Pattern: "Passport No. / رقم جواز السفر: C5305905" with flexible spacing and punctuation
        if (preg_match('/Passport\s*No\.?\s*\/\s*رقم\s*جواز\s*السفر\s*[:\-]?\s*([A-Z]\d{7})/iu', $page_text, $matches)) {
            $passport_numbers[] = strtoupper(trim($matches[1]));
        }
        
        // AGGRESSIVE PATTERN: Look for any letter followed by 7 digits that could be a passport
        if (preg_match_all('/\b([A-Z]\d{7})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                $passport_numbers[] = strtoupper(trim($match));
            }
        }
        
        // AGGRESSIVE PATTERN: Look for passport numbers in any context
        if (preg_match_all('/\b([A-Z]\d{6,8})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Only consider 1 letter + 7 digits as potential passport numbers
                if (strlen($match) == 8 && preg_match('/^[A-Z]\d{7}$/', $match)) {
                    $passport_numbers[] = strtoupper(trim($match));
                }
            }
        }
        
        // AGGRESSIVE PATTERN: Look for passport numbers with different formats
        if (preg_match_all('/\b([A-Z]{1,2}\d{6,8})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Consider various passport formats
                if (preg_match('/^[A-Z]\d{7}$/', $match) || // 1 letter + 7 digits
                    preg_match('/^[A-Z]{2}\d{6}$/', $match)) { // 2 letters + 6 digits
                    $passport_numbers[] = strtoupper(trim($match));
                }
            }
        }
        
        // ULTRA AGGRESSIVE PATTERN: Look for any alphanumeric that could be a passport
        if (preg_match_all('/\b([A-Z]{1,3}\d{5,10})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Consider various passport formats
                if (preg_match('/^[A-Z]\d{6,8}$/', $match) || // 1 letter + 6-8 digits
                    preg_match('/^[A-Z]{2}\d{5,7}$/', $match) || // 2 letters + 5-7 digits
                    preg_match('/^[A-Z]{3}\d{5,6}$/', $match)) { // 3 letters + 5-6 digits
                    $passport_numbers[] = strtoupper(trim($match));
                }
            }
        }
        
        // ULTRA AGGRESSIVE PATTERN: Look for passport numbers in any context
        if (preg_match_all('/\b([A-Z]\d{6,9})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Look for any letter followed by 6-9 digits
                $passport_numbers[] = strtoupper(trim($match));
            }
        }
        
        // ULTRA AGGRESSIVE PATTERN: Look for passport numbers with mixed case
        if (preg_match_all('/\b([A-Za-z]\d{6,9})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                // Look for any letter (case insensitive) followed by 6-9 digits
                $passport_numbers[] = strtoupper(trim($match));
            }
        }
        
        // Use the first valid passport number found
        if (!empty($passport_numbers)) {
            $result['passport_no'] = $passport_numbers[0];
            log_message('info', 'Found passport_no: ' . $result['passport_no'] . ' from ' . count($passport_numbers) . ' candidates');
        } else {
            log_message('info', 'No passport numbers found in page text');
            
            // FALLBACK: Try to find any alphanumeric that looks like a passport
            if (preg_match_all('/\b([A-Za-z]\d{6,9})\b/', $page_text, $matches)) {
                foreach ($matches[1] as $match) {
                    if (strlen($match) >= 7 && strlen($match) <= 10) {
                        $result['passport_no'] = strtoupper(trim($match));
                        log_message('info', 'Found passport_no (fallback): ' . $result['passport_no']);
                        break;
                    }
                }
            }
        }
        
        // Extract Name - enhanced patterns for multiple pages
        $name_patterns = [
            '/Full\s*Name\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/i',
            '/Full\s*Name\s*\n\s*([A-Z][A-Z\s\.\'-]{3,})/i',
            '/اﻟﻜﺎﻣﻞ اﻹﺳﻢ\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/iu',
            '/Name\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/i',
            '/Nama\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/i',
            '/([A-Z][A-Z\s\.\'-]{3,})\s*(?:Umrah|Visa|Passport|Indonesia)/i',
            '/Full\s*Name\s*([^\n]+)\s*Nationality/i',
            // Pattern for table format: "Full Name" followed by name
            '/Full\s*Name\s*([A-Z][^\n]{3,})/i',
            // Pattern for simple name extraction
            '/\b([A-Z][a-z]+\s+[A-Z][a-z]+)\b/', // Basic pattern for "FirstName LastName"
            // Additional patterns for multiple pages
            '/Name\s*[:\-]?\s*([A-Z][a-z]+\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i',
            '/\b([A-Z][a-z]+\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/', // Multiple word names
            '/Given\s*Name\s*[:\-]?\s*([A-Z][a-z]+)/i',
            '/Family\s*Name\s*[:\-]?\s*([A-Z][a-z]+)/i',
            // NEW: Specific patterns based on e-visa document format
            '/Full\s*Name\s*\/\s*الإسم الكامل\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/iu', // Bilingual format
            '/Full\s*Name\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})\s*Nationality/i', // Before Nationality
            '/Full\s*Name\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})\s*Indonesia/i', // Before Indonesia
            '/Full\s*Name\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})\s*Umrah/i', // Before Umrah
            '/\b([A-Z][A-Z\s\.\'-]{3,})\s*Indonesia\s*Umrah/i', // Between Indonesia and Umrah
            '/\b([A-Z][A-Z\s\.\'-]{3,})\s*Nationality\s*Indonesia/i', // Between Nationality and Indonesia
            '/\b([A-Z][A-Z\s\.\'-]{3,})\s*Type\s*Of\s*Visa/i', // Before Type Of Visa
            '/\b([A-Z][A-Z\s\.\'-]{3,})\s*Umrah\s*-\s*عمرة/i' // Before Umrah - عمرة
        ];
        
        log_message('info', 'Trying to extract name with ' . count($name_patterns) . ' patterns');
        
        // Log all name patterns for debugging
        foreach ($name_patterns as $i => $pattern) {
            log_message('info', 'Name pattern ' . ($i + 1) . ': ' . $pattern);
        }
        
        // Try to find all names in the text
        $names = array();
        foreach ($name_patterns as $pattern) {
            if (preg_match_all($pattern, $page_text, $matches)) {
                foreach ($matches[1] as $match) {
                    $name = trim($match);
                // Clean up the name - remove any non-name text
                $name = preg_replace('/\s*(?:Umrah|Visa|Passport|Indonesia|Nationality|Full|Name).*$/i', '', $name);
                $name = preg_replace('/\s+/', ' ', $name);
                $name = trim($name);
                
                // Skip invalid names like "Stay Days"
                if (!empty($name) && strlen($name) > 3 && !preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                        $names[] = $this->format_name($name);
                    }
                }
            }
        }
        
        // Additional context-based extraction for names
        // Look for names in e-visa table format: "Full Name / الإسم الكامل: WASPI MARDA KARTAM واسبي ماردا كارتام"
        if (preg_match('/Full\s*Name\s*\/\s*الإسم الكامل\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/iu', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // Look for names between passport and nationality
        if (preg_match('/[A-Z]\d{7}\s*([A-Z][A-Z\s\.\'-]{3,})\s*Indonesia/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // Look for names in e-visa format
        if (preg_match('/e-visa.*?([A-Z][A-Z\s\.\'-]{3,})\s*Indonesia/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // NEW: Additional context-based extraction for multiple formats
        // Look for names in KSA VISA format
        if (preg_match('/KSA\s*VISA.*?([A-Z][A-Z\s\.\'-]{3,})\s*Indonesia/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // Look for names in EVISA format
        if (preg_match('/EVISA.*?([A-Z][A-Z\s\.\'-]{3,})\s*Indonesia/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // Look for names before "Nationality"
        if (preg_match('/([A-Z][A-Z\s\.\'-]{3,})\s*Nationality\s*Indonesia/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // Look for names before "Birth Date"
        if (preg_match('/([A-Z][A-Z\s\.\'-]{3,})\s*Birth\s*Date/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // Look for names before "Type Of Visa"
        if (preg_match('/([A-Z][A-Z\s\.\'-]{3,})\s*Type\s*Of\s*Visa/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // Look for names before "Umrah"
        if (preg_match('/([A-Z][A-Z\s\.\'-]{3,})\s*Umrah/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names in traditional e-visa table structure
        // Pattern: "Full Name / الإسم الكامل: WASPI MARDA KARTAM واسبي ماردا كارتام" (exact format from Format 1)
        if (preg_match('/Full\s*Name\s*\/\s*الإسم\s*الكامل\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/iu', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names in exact table format
        // Pattern: "Full Name / الإسم الكامل: WASPI MARDA KARTAM واسبي ماردا كارتام" with exact spacing
        if (preg_match('/Full\s*Name\s*\/\s*الإسم\s*الكامل\s*:\s*([A-Z][A-Z\s\.\'-]{3,})/iu', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names in table with exact Arabic text
        // Pattern: "Full Name / الإسم الكامل: WASPI MARDA KARTAM واسبي ماردا كارتام" with proper Arabic characters
        if (preg_match('/Full\s*Name\s*\/\s*الإسم\s*الكامل\s*:\s*([A-Z][A-Z\s\.\'-]{3,})/iu', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names in table row format
        // Pattern: "Full Name" followed by colon and name
        if (preg_match('/Full\s*Name\s*[:\-]\s*([A-Z][A-Z\s\.\'-]{3,})/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names in Arabic table format
        // Pattern: "الإسم الكامل" followed by colon and name
        if (preg_match('/الإسم\s*الكامل\s*[:\-]\s*([A-Z][A-Z\s\.\'-]{3,})/iu', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names before "Nationality"
        // Pattern: WASPI MARDA KARTAM followed by "Nationality / الجنسية"
        if (preg_match('/([A-Z][A-Z\s\.\'-]{3,})\s*Nationality\s*\/\s*الجنسية/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names before "Type Of Visa"
        // Pattern: WASPI MARDA KARTAM followed by "Type Of Visa / نوع التأشيرة"
        if (preg_match('/([A-Z][A-Z\s\.\'-]{3,})\s*Type\s*Of\s*Visa\s*\/\s*نوع\s*التأشيرة/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names before "Umrah - عمرة"
        // Pattern: WASPI MARDA KARTAM followed by "Umrah - عمرة"
        if (preg_match('/([A-Z][A-Z\s\.\'-]{3,})\s*Umrah\s*-\s*عمرة/i', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names with flexible spacing
        // Pattern: "Full Name / الإسم الكامل: WASPI MARDA KARTAM واسبي ماردا كارتام" with any spacing
        if (preg_match('/Full\s*Name\s*\/\s*الإسم\s*الكامل\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/iu', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // FORMAT 1 SPECIFIC: Look for names in table structure with flexible format
        // Pattern: "Full Name / الإسم الكامل: WASPI MARDA KARTAM واسبي ماردا كارتام" with flexible spacing and punctuation
        if (preg_match('/Full\s*Name\s*\/\s*الإسم\s*الكامل\s*[:\-]?\s*([A-Z][A-Z\s\.\'-]{3,})/iu', $page_text, $matches)) {
            $name = trim($matches[1]);
            if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $names[] = $this->format_name($name);
            }
        }
        
        // AGGRESSIVE PATTERN: Look for any name-like pattern that could be a full name
        if (preg_match_all('/\b([A-Z][A-Z\s\.\'-]{3,})\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                $name = trim($match);
                // Skip if it contains numbers, special characters, or common non-name words
                if (!preg_match('/\d/', $name) && // No numbers
                    !preg_match('/\b(?:Stay\s+Days|Days|Stay|Visa|Passport|No|Date|Birth|Type|Umrah|Nationality|Indonesia|Operator|Agent|Border|Place|issue|Valid|Duration|until|from|Days|مدة|الإقامة|صالح|لغاية|اعتباراً|مصدر|التأشيرة|الإسم|الكامل|الجنسية|تاريخ|الميلاد|نوع|التأشيرة|عمرة|مكتب|العمرة|الوكيل|الخارجي|الحدود|رقم|الطلب|App)\b/i', $name) && // No common non-name words
                    strlen($name) >= 4 && // At least 4 characters
                    strlen($name) <= 50) { // Not too long
                    $names[] = $this->format_name($name);
                }
            }
        }
        
        // AGGRESSIVE PATTERN: Look for names in various contexts
        if (preg_match_all('/\b([A-Z][a-z]+\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/', $page_text, $matches)) {
            foreach ($matches[1] as $match) {
                $name = trim($match);
                if (!preg_match('/\b(?:Stay\s+Days|Days|Stay|Visa|Passport|No|Date|Birth|Type|Umrah|Nationality|Indonesia|Operator|Agent|Border|Place|issue|Valid|Duration|until|from|Days)\b/i', $name)) {
                    $names[] = $this->format_name($name);
                }
            }
        }
        
        // Use the first valid name found
        if (!empty($names)) {
            $result['nama'] = $names[0];
            log_message('info', 'Found nama: ' . $result['nama'] . ' from ' . count($names) . ' candidates');
        } else {
            log_message('info', 'No names found in page text');
        }
        
        // For some pages, the name might be in a table format
        if (!$result['nama']) {
            if (preg_match('/Full\s*Name\s*([A-Z][^\n]{3,})\s*([A-Z][^\n]{3,})/i', $page_text, $matches)) {
                $name = trim($matches[1] . ' ' . $matches[2]);
                if (!preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $name)) {
                $result['nama'] = $this->format_name($name);
                log_message('info', 'Found nama (table format): ' . $result['nama']);
                }
            }
        }
        
        // Extract Date of Birth - more flexible patterns
        $dob_patterns = [
            '/Date\s*of\s*Birth\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/i',
            '/DOB\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/i',
            '/Birth\s*Date\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/i',
            '/تاريخ الميلاد\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/iu',
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Date of Birth|DOB|Birth Date)/i',
            '/Tanggal\s*Lahir\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/i',
            // General date pattern
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/',
            // NEW: Additional patterns for multiple e-visa formats
            '/Birth\s*Date\s*\/\s*تاريخ الميلاد\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/iu', // Bilingual format
            '/Birth\s*Date\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/i', // Birth Date (without "of")
            '/تاريخ الميلاد\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/iu', // Arabic: تاريخ الميلاد
            '/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Type|Visa|نوع)/i', // Date before Type/Visa/نوع
            '/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Umrah|عمرة)/i', // Date before Umrah/عمرة
            '/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Operator|مكتب)/i', // Date before Operator/مكتب
            '/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Agent|الوكيل)/i', // Date before Agent/الوكيل
            '/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Border|الحدود)/i', // Date before Border/الحدود
            // Specific DD/MM/YYYY format patterns
            '/Birth\s*Date\s*[:\-]?\s*(\d{2}[\/\.\-]\d{2}[\/\.\-]\d{4})/i', // DD/MM/YYYY format
            '/تاريخ الميلاد\s*[:\-]?\s*(\d{2}[\/\.\-]\d{2}[\/\.\-]\d{4})/iu', // Arabic DD/MM/YYYY format
            '/\b(\d{2}[\/\.\-]\d{2}[\/\.\-]\d{4})\s*(?:Type|Visa|نوع)/i', // DD/MM/YYYY before Type/Visa/نوع
            '/\b(\d{2}[\/\.\-]\d{2}[\/\.\-]\d{4})\s*(?:Umrah|عمرة)/i', // DD/MM/YYYY before Umrah/عمرة
            // Context-based extraction for birth date
            '/[A-Z]\d{7}\s*[A-Z][A-Z\s\.\'-]{3,}\s*Indonesia\s*(\d{2}[\/\.\-]\d{2}[\/\.\-]\d{4})/i', // After passport + name + Indonesia
            '/Full\s*Name.*?Indonesia\s*(\d{2}[\/\.\-]\d{2}[\/\.\-]\d{4})/i', // After Full Name ... Indonesia
            '/Nationality.*?Indonesia\s*(\d{2}[\/\.\-]\d{2}[\/\.\-]\d{4})/i', // After Nationality ... Indonesia
            // FORMAT 1 SPECIFIC: Look for birth date in traditional e-visa table structure
            '/Birth\s*Date\s*\/\s*تاريخ\s*الميلاد\s*[:\-]?\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/iu', // Bilingual format
            '/Birth\s*Date\s*[:\-]\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/i', // English format
            '/تاريخ\s*الميلاد\s*[:\-]\s*(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/iu', // Arabic format
            // FORMAT 1 SPECIFIC: Look for birth date before "Type Of Visa"
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*Type\s*Of\s*Visa\s*\/\s*نوع\s*التأشيرة/i', // Before Type Of Visa
            // FORMAT 1 SPECIFIC: Look for birth date before "Umrah"
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*Umrah\s*-\s*عمرة/i', // Before Umrah
            // AGGRESSIVE PATTERN: Look for any date that could be a birth date
            '/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{4})\b/', // Any DD/MM/YYYY or DD.MM.YYYY or DD-MM-YYYY
            '/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2})\b/', // Any DD/MM/YY or DD.MM.YY or DD-MM-YY
            // AGGRESSIVE PATTERN: Look for dates in various contexts
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Birth|Date|Lahir|الميلاد)/i', // Date before Birth/Date/Lahir/الميلاد
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Type|Visa|نوع)/i', // Date before Type/Visa/نوع
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Umrah|عمرة)/i', // Date before Umrah/عمرة
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Nationality|الجنسية)/i', // Date before Nationality/الجنسية
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Operator|مكتب)/i', // Date before Operator/مكتب
            '/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\s*(?:Agent|الوكيل)/i' // Date before Agent/الوكيل
        ];
        
        log_message('info', 'Trying to extract birth date with ' . count($dob_patterns) . ' patterns');
        
        // Log all birth date patterns for debugging
        foreach ($dob_patterns as $i => $pattern) {
            log_message('info', 'Birth date pattern ' . ($i + 1) . ': ' . $pattern);
        }
        
        foreach ($dob_patterns as $pattern) {
            if (preg_match($pattern, $page_text, $matches)) {
                $result['tanggal_lahir'] = $this->normalize_date($matches[1]);
                log_message('info', 'Found tanggal_lahir: ' . $result['tanggal_lahir'] . ' using pattern: ' . $pattern);
                break;
            }
        }
        
        if (!$result['tanggal_lahir']) {
            log_message('info', 'No birth date found in page text');
            
            // FALLBACK: Try to find any date that looks like a birth date
            if (preg_match_all('/\b(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})\b/', $page_text, $matches)) {
                foreach ($matches[1] as $match) {
                    // Skip if it looks like a current date (2024, 2025, etc.)
                    if (!preg_match('/\d{1,2}[\/\.\-]\d{1,2}[\/\.\-](2024|2025)/', $match)) {
                        $result['tanggal_lahir'] = $this->normalize_date($match);
                        log_message('info', 'Found tanggal_lahir (fallback): ' . $result['tanggal_lahir']);
                        break;
                    }
                }
            }
        }
        
        
        // Filter out "Stay Days" data completely
        if (!empty($result['nama']) && preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $result['nama'])) {
            log_message('info', 'Filtering out Stay Days data: ' . $result['nama']);
            $result['nama'] = null;
        }
        
        if (!empty($result['visa_no']) && preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $result['visa_no'])) {
            log_message('info', 'Filtering out Stay Days visa: ' . $result['visa_no']);
            $result['visa_no'] = null;
        }
        
        if (!empty($result['passport_no']) && preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $result['passport_no'])) {
            log_message('info', 'Filtering out Stay Days passport: ' . $result['passport_no']);
            $result['passport_no'] = null;
        }
        
        if (!empty($result['tanggal_lahir']) && preg_match('/\b(?:Stay\s+Days|Days|Stay)\b/i', $result['tanggal_lahir'])) {
            log_message('info', 'Filtering out Stay Days date: ' . $result['tanggal_lahir']);
            $result['tanggal_lahir'] = null;
        }
        
        // Log final result
        log_message('info', 'Parsed result: ' . json_encode($result));
        
        // Log summary of what was found
        $found_fields = [];
        if (!empty($result['nama'])) $found_fields[] = 'nama: ' . $result['nama'];
        if (!empty($result['visa_no'])) $found_fields[] = 'visa_no: ' . $result['visa_no'];
        if (!empty($result['passport_no'])) $found_fields[] = 'passport_no: ' . $result['passport_no'];
        if (!empty($result['tanggal_lahir'])) $found_fields[] = 'tanggal_lahir: ' . $result['tanggal_lahir'];
        
        log_message('info', 'Found fields: ' . (empty($found_fields) ? 'NONE' : implode(', ', $found_fields)));
        
        return $result;
        } catch (Exception $e) {
            log_message('error', 'Error in parse_visa_page: ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
        return $result;
        }
    }

    private function format_name($name)
    {
        // Convert to title case
        $name = strtolower($name);
        $name = preg_replace_callback('/\b([a-z])/', function($m) {
            return strtoupper($m[1]);
        }, $name);
        
        // Remove extra spaces and special characters
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/[^A-Za-z\s\-\.\']/', '', $name);
        
        return trim($name);
    }

    private function normalize_date($dateStr)
    {
        $dateStr = trim($dateStr);
        $dateStr = str_replace(array('.', '/'), '-', $dateStr);
        
        // Try different date formats
        $formats = [
            'd-m-Y', 'd-m-y', 'Y-m-d', 'y-m-d',
            'd/m/Y', 'd/m/y', 'Y/m/d', 'y/m/d',
            'd.m.Y', 'd.m.y', 'Y.m.d', 'y.m.d'
        ];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateStr);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        
        // Return original if we can't parse it
        return $dateStr;
    }

    private function clean_output_buffer()
    {
        // Clean any output buffer completely
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Disable any output compression
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }
        
        // Disable CodeIgniter output completely
        $this->output->set_output('');
        
        // Clear any existing headers only if not sent
        if (!headers_sent()) {
            header_remove();
        } else {
            // If headers already sent, log the issue
            log_message('warning', 'Headers already sent, cannot modify headers');
        }
    }


    private function json($code, $data)
    {
        try {
            // Clean output buffer
            $this->clean_output_buffer();
            
            // Set headers only if not already sent
            if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                header('X-Content-Type-Options: nosniff');
        
        // Set HTTP response code
        http_response_code($code);
            } else {
                log_message('warning', 'Headers already sent, cannot set JSON headers');
            }
        
        // Ensure no whitespace or output before JSON
            $output = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            // Validate JSON encoding
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JSON encoding error: ' . json_last_error_msg());
                $output = json_encode(array(
                    'error' => 'JSON encoding error: ' . json_last_error_msg(),
                    'original_data' => $data
                ), JSON_UNESCAPED_UNICODE);
            }
        
        // Log the response for debugging
        log_message('info', 'JSON Response: ' . substr($output, 0, 200) . (strlen($output) > 200 ? '...' : ''));
        
            // Send JSON response and terminate
        echo $output;
        exit;
        } catch (Exception $e) {
            log_message('error', 'Error in json method: ' . $e->getMessage());
            // Fallback response
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            echo json_encode(array('error' => 'Internal server error: ' . $e->getMessage()));
            exit;
        }
    }

    public function debug_parse()
    {
        try {
            // Clean output buffer at the start
            $this->clean_output_buffer();
            
            // Debug parsing endpoint
            if (!$this->session->userdata('logged_in')) {
                return $this->json(401, array('error' => 'Anda harus login terlebih dahulu'));
            }

            $debug_data = array(
                'success' => true,
                'message' => 'Debug parsing endpoint',
                'timestamp' => date('Y-m-d H:i:s'),
                'server_info' => array(
                    'php_version' => PHP_VERSION,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                    'max_file_uploads' => ini_get('max_file_uploads')
                ),
                'request_info' => array(
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'content_type' => isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'not set',
                    'content_length' => isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'not set'
                ),
                'files_info' => array(
                    'files_received' => isset($_FILES) ? count($_FILES) : 0,
                    'pdf_uploaded' => isset($_FILES['pdf']) ? true : false
                ),
                'extraction_tools' => array(
                    'pdftotext_available' => $this->check_pdftotext_availability(),
                    'smalot_available' => class_exists('\\Smalot\\PdfParser\\Parser'),
                    'finfo_available' => function_exists('finfo_open'),
                    'mime_content_type_available' => function_exists('mime_content_type')
                )
            );

            if (isset($_FILES['pdf'])) {
                $debug_data['file_details'] = array(
                    'name' => $_FILES['pdf']['name'],
                    'size' => $_FILES['pdf']['size'],
                    'type' => $_FILES['pdf']['type'],
                    'error' => $_FILES['pdf']['error'],
                    'tmp_name' => $_FILES['pdf']['tmp_name'] ? 'set' : 'not set',
                    'readable' => isset($_FILES['pdf']['tmp_name']) && is_readable($_FILES['pdf']['tmp_name'])
                );
            }

            return $this->json(200, $debug_data);
        } catch (Throwable $e) {
            $this->clean_output_buffer();
            return $this->json(500, array(
                'error' => 'Debug parsing endpoint error: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        }
    }

    private function check_pdftotext_availability()
    {
        try {
            $bin = 'pdftotext';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $bin = 'C:\\poppler\\Library\\bin\\pdftotext.exe';
            }
            
            $output = null;
            $return_var = null;
            @exec($bin . ' -v 2>&1', $output, $return_var);
            
            if ($return_var === 0) {
                return true;
            }
            
            // Try alternative paths for Windows
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $alternative_paths = [
                    'C:\\poppler\\bin\\pdftotext.exe',
                    'C:\\Program Files\\poppler\\bin\\pdftotext.exe',
                    'C:\\Program Files (x86)\\poppler\\bin\\pdftotext.exe',
                    'pdftotext.exe'
                ];
                
                foreach ($alternative_paths as $alt_path) {
                    @exec($alt_path . ' -v 2>&1', $output, $return_var);
                    if ($return_var === 0) {
                        return true;
                    }
                }
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function check_logs()
    {
        try {
            // Clean output buffer at the start
            $this->clean_output_buffer();
            
            // Check if user is logged in
            if (!$this->session->userdata('logged_in')) {
                return $this->json(401, array('error' => 'Anda harus login terlebih dahulu'));
            }

            $log_file = APPPATH . 'logs/log-' . date('Y-m-d') . '.php';
            $log_content = '';
            
            if (file_exists($log_file)) {
                $log_content = file_get_contents($log_file);
                // Remove PHP tags and get last 50 lines
                $log_content = preg_replace('/^<\?php.*?\?>/s', '', $log_content);
                $lines = explode("\n", $log_content);
                $log_content = implode("\n", array_slice($lines, -50));
            }

            return $this->json(200, array(
                'success' => true,
                'log_file' => $log_file,
                'log_exists' => file_exists($log_file),
                'log_content' => $log_content,
                'log_size' => file_exists($log_file) ? filesize($log_file) : 0
            ));
        } catch (Throwable $e) {
            $this->clean_output_buffer();
            return $this->json(500, array(
                'error' => 'Error checking logs: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        }
    }

    public function simple_parse()
    {
        try {
            // Clean output buffer at the start
            $this->clean_output_buffer();
            
            // Check if user is logged in
            if (!$this->session->userdata('logged_in')) {
                return $this->json(401, array('error' => 'Anda harus login terlebih dahulu'));
            }

            // Check if file is uploaded
            if (!isset($_FILES['pdf']) || !isset($_FILES['pdf']['tmp_name']) || $_FILES['pdf']['tmp_name'] === '') {
                return $this->json(400, array('error' => 'File PDF tidak ditemukan'));
            }

            $pdfPath = $_FILES['pdf']['tmp_name'];
            
            // Simple text extraction
            $text = '';
            $extraction_method = '';
            
            if (class_exists('\\Smalot\\PdfParser\\Parser')) {
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($pdfPath);
                    $text = $pdf->getText();
                    if ($text && trim($text) !== '') {
                        $extraction_method = 'smalot/pdfparser';
                    }
                } catch (Exception $e) {
                    log_message('error', 'Smalot extraction failed: ' . $e->getMessage());
                }
            }

            if (!$text || trim($text) === '') {
                return $this->json(500, array('error' => 'Gagal mengekstrak teks dari PDF'));
            }

            // Normalize text
            $text = $this->normalize_text($text);
            
            // Split text into pages for multi-page processing
            $pages = $this->split_into_pages($text);
            
            // Process each page
            $all_parsed_data = array();
            $successful_pages = 0;
            
            foreach ($pages as $page_index => $page_text) {
                try {
                    // Parse each page
                    $parsed_data = $this->parse_visa_page($page_text);
                    
                    // Add page information
                    $parsed_data['page_number'] = $page_index + 1;
                    
                    // Only add if we found some data
                    if (!empty($parsed_data['nama']) || !empty($parsed_data['visa_no']) || !empty($parsed_data['passport_no'])) {
                        $all_parsed_data[] = $parsed_data;
                        $successful_pages++;
                    }
                    
                } catch (Exception $e) {
                    log_message('error', 'Error parsing page ' . ($page_index + 1) . ' in simple parse: ' . $e->getMessage());
                    // Continue with next page
                }
            }

            return $this->json(200, array(
                'success' => true,
                'count' => count($all_parsed_data),
                'total_pages' => count($pages),
                'successful_pages' => $successful_pages,
                'data' => $all_parsed_data,
                'extraction_method' => $extraction_method ?: 'simple_test',
                'text_preview' => substr($text, 0, 200),
                'text_length' => strlen($text)
            ));
        } catch (Throwable $e) {
            $this->clean_output_buffer();
            return $this->json(500, array(
                'error' => 'Simple parse error: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        }
    }

    public function test_parsing()
    {
        try {
            // Clean output buffer at the start
            $this->clean_output_buffer();
            
            // Check if user is logged in
            if (!$this->session->userdata('logged_in')) {
                return $this->json(401, array('error' => 'Anda harus login terlebih dahulu'));
            }

            // Test all parsing functions
            $test_results = array(
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'tests' => array()
            );

            // Test 1: Check server configuration
            $test_results['tests']['server_config'] = array(
                'name' => 'Server Configuration',
                'status' => 'passed',
                'details' => array(
                    'php_version' => PHP_VERSION,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size')
                )
            );

            // Test 2: Check extraction tools
            $test_results['tests']['extraction_tools'] = array(
                'name' => 'Extraction Tools',
                'status' => 'passed',
                'details' => array(
                    'pdftotext_available' => $this->check_pdftotext_availability(),
                    'smalot_available' => class_exists('\\Smalot\\PdfParser\\Parser'),
                    'finfo_available' => function_exists('finfo_open'),
                    'mime_content_type_available' => function_exists('mime_content_type')
                )
            );

            // Test 3: Test text normalization
            $test_text = "Test\n\n\nText   with   spaces\r\n\r\nAnd\t\ttabs";
            $normalized = $this->normalize_text($test_text);
            $test_results['tests']['text_normalization'] = array(
                'name' => 'Text Normalization',
                'status' => 'passed',
                'details' => array(
                    'original' => $test_text,
                    'normalized' => $normalized,
                    'length_original' => strlen($test_text),
                    'length_normalized' => strlen($normalized)
                )
            );

            // Test 4: Test page splitting
            $test_text = "Page 1 content\n\nPage 2 content\n\nPage 3 content";
            $pages = $this->split_into_pages($test_text);
            $test_results['tests']['page_splitting'] = array(
                'name' => 'Page Splitting',
                'status' => 'passed',
                'details' => array(
                    'original_text' => $test_text,
                    'pages_count' => count($pages),
                    'pages' => $pages
                )
            );

            // Test 5: Test visa parsing with sample data
            $sample_text = "Full Name: John Doe\nPassport No: A1234567\nVisa No: 1234567890\nDate of Birth: 01/01/1990";
            $parsed = $this->parse_visa_page($sample_text);
            $test_results['tests']['visa_parsing'] = array(
                'name' => 'Visa Parsing',
                'status' => 'passed',
                'details' => array(
                    'sample_text' => $sample_text,
                    'parsed_result' => $parsed,
                    'extraction_success' => !empty($parsed['nama']) && !empty($parsed['visa_no'])
                )
            );

            // Test 6: Check database connection
            try {
                $this->load->model('Parsing_model');
                $stats = $this->Parsing_model->get_parsing_statistics();
                $test_results['tests']['database_connection'] = array(
                    'name' => 'Database Connection',
                    'status' => 'passed',
                    'details' => array(
                        'connection' => 'success',
                        'stats' => $stats
                    )
                );
            } catch (Exception $e) {
                $test_results['tests']['database_connection'] = array(
                    'name' => 'Database Connection',
                    'status' => 'failed',
                    'details' => array(
                        'error' => $e->getMessage()
                    )
                );
            }

            return $this->json(200, $test_results);
        } catch (Throwable $e) {
            $this->clean_output_buffer();
            return $this->json(500, array(
                'error' => 'Test parsing error: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        }
    }

    public function debug_parse_simple()
    {
        // Very simple debug endpoint to identify the issue
        try {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
            }
            
            // Basic response
            $response = array(
                'success' => true,
                'message' => 'Debug parse simple working',
                'timestamp' => date('Y-m-d H:i:s'),
                'request_method' => $_SERVER['REQUEST_METHOD'],
                'files_received' => isset($_FILES) ? count($_FILES) : 0,
                'session_logged_in' => $this->session->userdata('logged_in')
            );
            
            echo json_encode($response);
            exit;
            
        } catch (Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            echo json_encode(array('error' => 'Debug simple error: ' . $e->getMessage()));
            exit;
        }
    }

    public function test_basic()
    {
        // Ultra simple test endpoint
        try {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
            }
            
            // Ultra simple response
            $response = array(
                'success' => true,
                'message' => 'Basic test working',
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            echo json_encode($response);
            exit;
            
        } catch (Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            echo json_encode(array('error' => 'Basic test error: ' . $e->getMessage()));
            exit;
        }
    }

    public function test_pdf_extraction()
    {
        // Test PDF extraction specifically
        try {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
            }
            
            // Check if user is logged in
            if (!$this->session->userdata('logged_in')) {
                echo json_encode(array('error' => 'Anda harus login terlebih dahulu'));
                exit;
            }
            
            // Check if file is uploaded
            if (!isset($_FILES['pdf']) || !isset($_FILES['pdf']['tmp_name']) || $_FILES['pdf']['tmp_name'] === '') {
                echo json_encode(array('error' => 'File PDF tidak ditemukan'));
                exit;
            }
            
            $pdfPath = $_FILES['pdf']['tmp_name'];
            
            // Check if file exists and is readable
            if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
                echo json_encode(array('error' => 'File tidak dapat dibaca'));
                exit;
            }
            
            // Test all extraction methods
            $results = array(
                'success' => true,
                'file_info' => array(
                    'name' => $_FILES['pdf']['name'],
                    'size' => $_FILES['pdf']['size'],
                    'type' => $_FILES['pdf']['type'],
                    'readable' => is_readable($pdfPath)
                ),
                'extraction_tests' => array()
            );
            
            // Test 1: Smalot PDF Parser
            if (class_exists('\\Smalot\\PdfParser\\Parser')) {
                try {
                    $text = $this->extract_with_smalot($pdfPath);
                    $results['extraction_tests']['smalot'] = array(
                        'available' => true,
                        'success' => $text !== null,
                        'text_length' => $text ? strlen($text) : 0,
                        'text_preview' => $text ? substr($text, 0, 200) : null
                    );
                } catch (Exception $e) {
                    $results['extraction_tests']['smalot'] = array(
                        'available' => true,
                        'success' => false,
                        'error' => $e->getMessage()
                    );
                }
            } else {
                $results['extraction_tests']['smalot'] = array(
                    'available' => false,
                    'success' => false,
                    'error' => 'Smalot PDF Parser not available'
                );
            }
            
            // Test 2: pdftotext
            try {
                $text = $this->extract_with_pdftotext($pdfPath);
                $results['extraction_tests']['pdftotext'] = array(
                    'available' => $this->check_pdftotext_availability(),
                    'success' => $text !== null,
                    'text_length' => $text ? strlen($text) : 0,
                    'text_preview' => $text ? substr($text, 0, 200) : null
                );
            } catch (Exception $e) {
                $results['extraction_tests']['pdftotext'] = array(
                    'available' => $this->check_pdftotext_availability(),
                    'success' => false,
                    'error' => $e->getMessage()
                );
            }
            
            // Test 3: Alternative pdftotext
            try {
                $text = $this->extract_with_pdftotext_alternative($pdfPath);
                $results['extraction_tests']['pdftotext_alternative'] = array(
                    'available' => $this->check_pdftotext_availability(),
                    'success' => $text !== null,
                    'text_length' => $text ? strlen($text) : 0,
                    'text_preview' => $text ? substr($text, 0, 200) : null
                );
            } catch (Exception $e) {
                $results['extraction_tests']['pdftotext_alternative'] = array(
                    'available' => $this->check_pdftotext_availability(),
                    'success' => false,
                    'error' => $e->getMessage()
                );
            }
            
            // Test 4: Basic reading
            try {
                $text = $this->extract_with_basic_reading($pdfPath);
                $results['extraction_tests']['basic_reading'] = array(
                    'available' => true,
                    'success' => $text !== null,
                    'text_length' => $text ? strlen($text) : 0,
                    'text_preview' => $text ? substr($text, 0, 200) : null
                );
            } catch (Exception $e) {
                $results['extraction_tests']['basic_reading'] = array(
                    'available' => true,
                    'success' => false,
                    'error' => $e->getMessage()
                );
            }
            
            echo json_encode($results);
            exit;
            
        } catch (Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            echo json_encode(array('error' => 'PDF extraction test error: ' . $e->getMessage()));
            exit;
        }
    }

    public function install_guide()
    {
        // Provide installation guide for PDF tools
        try {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
            }
            
            $guide = array(
                'success' => true,
                'message' => 'PDF Tools Installation Guide',
                'tools_status' => array(
                    'smalot_available' => class_exists('\\Smalot\\PdfParser\\Parser'),
                    'pdftotext_available' => $this->check_pdftotext_availability()
                ),
                'installation_guide' => array(
                    'smalot' => array(
                        'status' => class_exists('\\Smalot\\PdfParser\\Parser') ? 'installed' : 'not_installed',
                        'description' => 'Smalot PDF Parser (PHP Library)',
                        'install_command' => 'composer require smalot/pdfparser',
                        'note' => 'Already installed via Composer'
                    ),
                    'pdftotext' => array(
                        'status' => $this->check_pdftotext_availability() ? 'installed' : 'not_installed',
                        'description' => 'Poppler pdftotext (Command Line Tool)',
                        'windows_install' => array(
                            'method1' => 'Download from https://blog.alivate.com.au/poppler-windows/',
                            'method2' => 'Install via Chocolatey: choco install poppler',
                            'method3' => 'Install via Scoop: scoop install poppler'
                        ),
                        'note' => 'Required for better PDF text extraction'
                    )
                ),
                'current_methods' => array(
                    'available' => array(
                        'smalot' => class_exists('\\Smalot\\PdfParser\\Parser'),
                        'basic_reading' => true
                    ),
                    'unavailable' => array(
                        'pdftotext' => !$this->check_pdftotext_availability()
                    )
                )
            );
            
            echo json_encode($guide);
            exit;
            
        } catch (Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            echo json_encode(array('error' => 'Install guide error: ' . $e->getMessage()));
            exit;
        }
    }

    public function debug_response()
    {
        // Debug endpoint to test response handling
        try {
            // Clean output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                http_response_code(200);
            }
            
            $debug_data = array(
                'success' => true,
                'message' => 'Debug response test',
                'timestamp' => date('Y-m-d H:i:s'),
                'server_info' => array(
                    'php_version' => PHP_VERSION,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'output_buffering' => ini_get('output_buffering'),
                    'zlib_output_compression' => ini_get('zlib_output_compression')
                ),
                'response_info' => array(
                    'headers_sent' => headers_sent(),
                    'output_buffering_level' => ob_get_level(),
                    'content_length' => 0
                )
            );
            
            $json_output = json_encode($debug_data);
            $debug_data['response_info']['content_length'] = strlen($json_output);
            
            // Re-encode with updated content length
            $json_output = json_encode($debug_data);
            
            echo $json_output;
            flush();
            exit;
            
        } catch (Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            echo json_encode(array('error' => 'Debug response error: ' . $e->getMessage()));
            flush();
            exit;
        }
    }

    public function test_simple()
    {
        // Ultra simple test endpoint
        try {
            // Clean output buffer completely
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
            }
            
            // Ultra simple response
            $response = array(
                'success' => true,
                'message' => 'Simple test working',
                'timestamp' => date('Y-m-d H:i:s')
            );
            
            $json_output = json_encode($response);
            
            // Force output
            echo $json_output;
            flush();
            exit;
            
        } catch (Exception $e) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            echo json_encode(array('error' => 'Simple test error: ' . $e->getMessage()));
            flush();
            exit;
        }
    }

    public function parse_alternative()
    {
        // Alternative parse method using CodeIgniter's built-in JSON response
        try {
            // Check if user is logged in
            if (!$this->session->userdata('logged_in')) {
                return $this->json(401, array('error' => 'Anda harus login terlebih dahulu'));
            }
            
            // Check if file is uploaded
            if (!isset($_FILES['pdf']) || !isset($_FILES['pdf']['tmp_name']) || $_FILES['pdf']['tmp_name'] === '') {
                return $this->json(400, array('error' => 'File PDF tidak ditemukan'));
            }
            
            $pdfPath = $_FILES['pdf']['tmp_name'];
            
            // Check if file exists and is readable
            if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
                return $this->json(400, array('error' => 'File tidak dapat dibaca'));
            }
            
            // Try to extract text with multiple methods
            $text = '';
            $extraction_method = '';
            
            // Method 1: Try smalot/pdfparser first
            if (class_exists('\\Smalot\\PdfParser\\Parser')) {
                try {
                    $text = $this->extract_with_smalot($pdfPath);
                    if ($text && trim($text) !== '') {
                        $extraction_method = 'smalot/pdfparser';
                    }
                } catch (Exception $e) {
                    // Continue to next method
                }
            }
            
            // Method 2: Try pdftotext if smalot failed
            if (!$text || trim($text) === '') {
                try {
                    $text = $this->extract_with_pdftotext($pdfPath);
                    if ($text && trim($text) !== '') {
                        $extraction_method = 'pdftotext';
                    }
                } catch (Exception $e) {
                    // Continue
                }
            }
            
            // Method 3: Try basic reading
            if (!$text || trim($text) === '') {
                try {
                    $text = $this->extract_with_basic_reading($pdfPath);
                    if ($text && trim($text) !== '') {
                        $extraction_method = 'basic_reading';
                    }
                } catch (Exception $e) {
                    // Continue
                }
            }
            
            if (!$text || trim($text) === '') {
                return $this->json(500, array(
                    'error' => 'Gagal mengekstrak teks dari PDF',
                    'debug_info' => array(
                        'file_size' => filesize($pdfPath),
                        'file_readable' => is_readable($pdfPath),
                        'smalot_available' => class_exists('\\Smalot\\PdfParser\\Parser'),
                        'pdftotext_available' => $this->check_pdftotext_availability()
                    )
                ));
            }
            
            // Normalize text
            $text = $this->normalize_text($text);
            
            // Split text into pages for multi-page processing
            $pages = $this->split_into_pages($text);
            
            // Process each page
            $all_parsed_data = array();
            $successful_pages = 0;
            
            foreach ($pages as $page_index => $page_text) {
                try {
                    // Parse each page
                    $parsed_data = $this->parse_visa_page($page_text);
                    
                    // Add page information
                    $parsed_data['page_number'] = $page_index + 1;
                    
                    // Only add if we found some data
                    if (!empty($parsed_data['nama']) || !empty($parsed_data['visa_no']) || !empty($parsed_data['passport_no'])) {
                        $all_parsed_data[] = $parsed_data;
                        $successful_pages++;
                    }
                    
                } catch (Exception $e) {
                    // Continue with next page
                }
            }
            
            // Try to save data to database
            $saved_count = 0;
            if (!empty($all_parsed_data)) {
                try {
                    $this->load->model('Parsing_model');
                    $saved_count = $this->Parsing_model->save_parsed_data($all_parsed_data);
                } catch (Exception $e) {
                    // Continue without failing the response
                }
            }
            
            // Save parsing result to session for download
            $this->session->set_userdata('last_parsing_result', $all_parsed_data);
            
            // Prepare response
            $response_data = array(
                'success' => true,
                'count' => count($all_parsed_data),
                'saved_count' => $saved_count,
                'total_pages' => count($pages),
                'successful_pages' => $successful_pages,
                'data' => $all_parsed_data,
                'extraction_method' => $extraction_method,
                'file_info' => array(
                    'name' => $_FILES['pdf']['name'],
                    'size' => $_FILES['pdf']['size'],
                    'type' => $_FILES['pdf']['type']
                )
            );
            
            return $this->json(200, $response_data);
            
        } catch (Exception $e) {
            return $this->json(500, array(
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        } catch (Error $e) {
            return $this->json(500, array(
                'error' => 'Terjadi kesalahan fatal: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        } catch (Throwable $e) {
            return $this->json(500, array(
                'error' => 'Terjadi kesalahan tidak terduga: ' . $e->getMessage(),
                'debug_info' => array(
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            ));
        }
    }

    public function download_excel()
    {
        try {
            // Check if user is logged in
            if (!$this->session->userdata('logged_in')) {
                $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu');
                redirect('auth');
            }

            // Load Excel library
            $this->load->library('Excel');
            
            // Get all visa data
            $this->load->model('Parsing_model');
            $this->db->select('*');
            $this->db->from('visa_data');
            $this->db->order_by('created_at', 'DESC');
            $query = $this->db->get();
            $data = $query->result_array();
            
            if (empty($data)) {
                $this->session->set_flashdata('error', 'Tidak ada data untuk didownload');
                redirect('parsing/view_data');
            }
            
            // Create new Excel object
            $objPHPExcel = new PHPExcel();
            
            // Set document properties
            $objPHPExcel->getProperties()
                ->setCreator("Hajj Management System")
                ->setLastModifiedBy("Hajj Management System")
                ->setTitle("Data VISA")
                ->setSubject("Data VISA Export")
                ->setDescription("Export data VISA dari sistem")
                ->setKeywords("visa, hajj, umrah")
                ->setCategory("Data Export");
            
            // Set active sheet
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            
            // Set sheet title
            $sheet->setTitle('Data VISA');
            
            // Set headers
            $headers = array(
                'A1' => 'No',
                'B1' => 'Nama Lengkap',
                'C1' => 'Nomor Paspor',
                'D1' => 'Nomor VISA',
                'E1' => 'Tanggal Lahir',
                'F1' => 'Tanggal Dibuat'
            );
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Style headers
            $headerStyle = array(
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF')
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4472C4')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            );
            
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(20);
            
            // Fill data
            $row = 2;
            foreach ($data as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item['nama']);
                $sheet->setCellValue('C' . $row, $item['passport_no']);
                $sheet->setCellValue('D' . $row, $item['visa_no']);
                $sheet->setCellValue('E' . $row, $item['tanggal_lahir']);
                $sheet->setCellValue('F' . $row, date('d/m/Y H:i', strtotime($item['created_at'])));
                $row++;
            }
            
            // Add borders to data
            $dataStyle = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                )
            );
            
            $lastRow = $row - 1;
            $sheet->getStyle('A1:F' . $lastRow)->applyFromArray($dataStyle);
            
            // Set filename
            $filename = 'Data_VISA_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Create writer and save
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            
            // Log download activity
            log_message('info', 'Excel download completed: ' . count($data) . ' records exported by user: ' . $this->session->userdata('username'));
            
        } catch (Exception $e) {
            log_message('error', 'Excel download error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal mendownload file Excel: ' . $e->getMessage());
            redirect('parsing/view_data');
        }
    }

    public function download_excel_filtered()
    {
        try {
            // Check if user is logged in
            if (!$this->session->userdata('logged_in')) {
                $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu');
                redirect('auth');
            }

            // Get search parameter
            $search = $this->input->get('search');
            
            // Load Excel library
            $this->load->library('Excel');
            
            // Get filtered visa data
            $this->load->model('Parsing_model');
            $this->db->select('*');
            $this->db->from('visa_data');
            
            // Add search condition if search term exists
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('nama', $search);
                $this->db->or_like('passport_no', $search);
                $this->db->or_like('visa_no', $search);
                $this->db->group_end();
            }
            
            $this->db->order_by('created_at', 'DESC');
            $query = $this->db->get();
            $data = $query->result_array();
            
            if (empty($data)) {
                $this->session->set_flashdata('error', 'Tidak ada data untuk didownload');
                redirect('parsing/view_data');
            }
            
            // Create new Excel object
            $objPHPExcel = new PHPExcel();
            
            // Set document properties
            $objPHPExcel->getProperties()
                ->setCreator("Hajj Management System")
                ->setLastModifiedBy("Hajj Management System")
                ->setTitle("Data VISA Filtered")
                ->setSubject("Data VISA Export Filtered")
                ->setDescription("Export data VISA yang difilter dari sistem")
                ->setKeywords("visa, hajj, umrah, filtered")
                ->setCategory("Data Export");
            
            // Set active sheet
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            
            // Set sheet title
            $sheet->setTitle('Data VISA Filtered');
            
            // Set headers
            $headers = array(
                'A1' => 'No',
                'B1' => 'Nama Lengkap',
                'C1' => 'Nomor Paspor',
                'D1' => 'Nomor VISA',
                'E1' => 'Tanggal Lahir',
                'F1' => 'Tanggal Dibuat'
            );
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Style headers
            $headerStyle = array(
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF')
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4472C4')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            );
            
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(20);
            
            // Fill data
            $row = 2;
            foreach ($data as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item['nama']);
                $sheet->setCellValue('C' . $row, $item['passport_no']);
                $sheet->setCellValue('D' . $row, $item['visa_no']);
                $sheet->setCellValue('E' . $row, $item['tanggal_lahir']);
                $sheet->setCellValue('F' . $row, date('d/m/Y H:i', strtotime($item['created_at'])));
                $row++;
            }
            
            // Add borders to data
            $dataStyle = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                )
            );
            
            $lastRow = $row - 1;
            $sheet->getStyle('A1:F' . $lastRow)->applyFromArray($dataStyle);
            
            // Set filename
            $filename = 'Data_VISA_Filtered_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Create writer and save
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            
            // Log download activity
            log_message('info', 'Filtered Excel download completed: ' . count($data) . ' records exported by user: ' . $this->session->userdata('username'));
            
        } catch (Exception $e) {
            log_message('error', 'Filtered Excel download error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal mendownload file Excel: ' . $e->getMessage());
            redirect('parsing/view_data');
        }
    }

    public function download_parsing_result()
    {
        try {
            // Check if user is logged in
            if (!$this->session->userdata('logged_in')) {
                $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu');
                redirect('auth');
            }

            // Get parsing data from session or request
            $parsing_data = $this->session->userdata('last_parsing_result');
            
            if (empty($parsing_data)) {
                $this->session->set_flashdata('error', 'Tidak ada data parsing untuk didownload');
                redirect('parsing');
            }

            // Load Excel library
            $this->load->library('Excel');
            
            // Create new Excel object
            $objPHPExcel = new PHPExcel();
            
            // Set document properties
            $objPHPExcel->getProperties()
                ->setCreator("Hajj Management System")
                ->setLastModifiedBy("Hajj Management System")
                ->setTitle("Hasil Parsing VISA")
                ->setSubject("Hasil Parsing VISA Export")
                ->setDescription("Export hasil parsing VISA dari PDF")
                ->setKeywords("visa, hajj, umrah, parsing")
                ->setCategory("Parsing Export");
            
            // Set active sheet
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            
            // Set sheet title
            $sheet->setTitle('Hasil Parsing VISA');
            
            // Set headers
            $headers = array(
                'A1' => 'No',
                'B1' => 'Halaman',
                'C1' => 'Nama Lengkap',
                'D1' => 'Nomor Paspor',
                'E1' => 'Nomor VISA',
                'F1' => 'Tanggal Lahir',
                'G1' => 'Status'
            );
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Style headers
            $headerStyle = array(
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF')
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4472C4')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            );
            
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(10);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            
            // Fill data
            $row = 2;
            foreach ($parsing_data as $index => $item) {
                $status = (!empty($item['nama']) && !empty($item['visa_no'])) ? 'Lengkap' : 'Tidak Lengkap';
                
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, isset($item['page_number']) ? $item['page_number'] : 'N/A');
                $sheet->setCellValue('C' . $row, isset($item['nama']) ? $item['nama'] : '-');
                $sheet->setCellValue('D' . $row, isset($item['passport_no']) ? $item['passport_no'] : '-');
                $sheet->setCellValue('E' . $row, isset($item['visa_no']) ? $item['visa_no'] : '-');
                $sheet->setCellValue('F' . $row, isset($item['tanggal_lahir']) ? $item['tanggal_lahir'] : '-');
                $sheet->setCellValue('G' . $row, isset($status) ? $status : '-');
                
                
                // Color code status
                if ($status === 'Lengkap') {
                    $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('28A745');
                } else {
                    $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('DC3545');
                }
                
                $row++;
            }
            
            // Add borders to data
            $dataStyle = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                )
            );
            
            $lastRow = $row - 1;
            $sheet->getStyle('A1:G' . $lastRow)->applyFromArray($dataStyle);
            
            // Add summary information
            $summaryRow = $lastRow + 3;
            $sheet->setCellValue('A' . $summaryRow, 'Ringkasan Parsing:');
            $sheet->setCellValue('A' . ($summaryRow + 1), 'Total Data: ' . count($parsing_data));
            $sheet->setCellValue('A' . ($summaryRow + 2), 'Data Lengkap: ' . count(array_filter($parsing_data, function($item) {
                return !empty($item['nama']) && !empty($item['visa_no']);
            })));
            $sheet->setCellValue('A' . ($summaryRow + 3), 'Data Tidak Lengkap: ' . count(array_filter($parsing_data, function($item) {
                return empty($item['nama']) || empty($item['visa_no']);
            })));
            $sheet->setCellValue('A' . ($summaryRow + 4), 'Tanggal Export: ' . date('d/m/Y H:i:s'));
            
            // Style summary
            $summaryStyle = array(
                'font' => array('bold' => true),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'F8F9FA')
                )
            );
            $sheet->getStyle('A' . $summaryRow . ':A' . ($summaryRow + 4))->applyFromArray($summaryStyle);
            
            // Set filename
            $filename = 'Hasil_Parsing_VISA_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Create writer and save
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            
            // Log download activity
            log_message('info', 'Parsing result Excel download completed: ' . count($parsing_data) . ' records exported by user: ' . $this->session->userdata('username'));
            
        } catch (Exception $e) {
            log_message('error', 'Parsing result Excel download error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal mendownload hasil parsing: ' . $e->getMessage());
            redirect('parsing');
        }
    }
}