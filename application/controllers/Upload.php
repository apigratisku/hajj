<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('session');
        $this->load->helper('url');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function upload_barcode() {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Session expired. Silakan login ulang.']));
            return;
        }
        
        // Check if request is AJAX or has X-Requested-With header
        if (!$this->input->is_ajax_request() && !$this->input->get_request_header('X-Requested-With')) {
            show_404();
            return;
        }

        try {
            // Get flag_doc from POST
            $flag_doc = $this->input->post('flag_doc');
            if (empty($flag_doc)) {
                throw new Exception('Flag dokumen tidak boleh kosong');
            }

            // Get existing barcode filename if any (for replacement)
            $existing_barcode = $this->input->post('existing_barcode');
            
            // Check if file was uploaded
            if (!isset($_FILES['barcode_image']) || $_FILES['barcode_image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Tidak ada file yang diupload atau terjadi error');
            }

            $file = $_FILES['barcode_image'];
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Tipe file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP');
            }

            // Validate file size (max 5MB)
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                throw new Exception('Ukuran file terlalu besar. Maksimal 5MB');
            }

            // Create upload directory if not exists
            $upload_dir = FCPATH . 'assets/uploads/barcode/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception('Gagal membuat direktori upload');
                }
            }

            // Generate encrypted filename
            $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            
            // Create encrypted filename: flagdoc_encrypted_originalname_timestamp.extension
            $encrypted_name = $this->encrypt_filename($original_name);
            $timestamp = time();
            $new_filename = $flag_doc . '_' . $encrypted_name . '_' . $timestamp . '.' . $extension;
            
            // Ensure filename is safe
            $new_filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $new_filename);
            
            $upload_path = $upload_dir . $new_filename;

            // Delete existing barcode file if it exists (for replacement)
            log_message('info', 'Upload barcode - Existing barcode value: ' . ($existing_barcode ?: 'EMPTY'));
            
            if (!empty($existing_barcode)) {
                $existing_file_path = $upload_dir . $existing_barcode;
                log_message('info', 'Upload barcode - Checking file path: ' . $existing_file_path);
                
                if (file_exists($existing_file_path)) {
                    log_message('info', 'Upload barcode - File exists, attempting to delete: ' . $existing_barcode);
                    if (!unlink($existing_file_path)) {
                        log_message('error', 'Upload barcode - Failed to delete existing barcode file: ' . $existing_barcode);
                    } else {
                        log_message('info', 'Upload barcode - Successfully deleted existing barcode file: ' . $existing_barcode);
                    }
                } else {
                    log_message('info', 'Upload barcode - Existing file not found: ' . $existing_file_path);
                }
            } else {
                log_message('info', 'Upload barcode - No existing barcode to replace');
            }

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Gagal menyimpan file');
            }

            // Return success response
            $response = [
                'status' => 'success',
                'message' => 'Gambar barcode berhasil diupload',
                'filename' => $new_filename,
                'file_url' => base_url('assets/uploads/barcode/' . $new_filename),
                'original_name' => $file['name'],
                'barcode_value' => $new_filename // Nama file untuk disimpan ke database
            ];

        } catch (Exception $e) {
            log_message('error', 'Upload barcode error: ' . $e->getMessage());
            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function delete_barcode() {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Session expired. Silakan login ulang.']));
            return;
        }
        
        // Check if request is AJAX or has X-Requested-With header
        if (!$this->input->is_ajax_request() && !$this->input->get_request_header('X-Requested-With')) {
            show_404();
            return;
        }

        try {
            $filename = $this->input->post('filename');
            
            if (empty($filename)) {
                throw new Exception('Nama file tidak boleh kosong');
            }

            // Validate filename for security
            if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
                throw new Exception('Nama file tidak valid');
            }

            $file_path = FCPATH . 'assets/uploads/barcode/' . $filename;
            
            if (file_exists($file_path)) {
                if (unlink($file_path)) {
                    $response = [
                        'status' => 'success',
                        'message' => 'File berhasil dihapus'
                    ];
                } else {
                    throw new Exception('Gagal menghapus file');
                }
            } else {
                throw new Exception('File tidak ditemukan');
            }

        } catch (Exception $e) {
            log_message('error', 'Delete barcode error: ' . $e->getMessage());
            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    private function encrypt_filename($filename) {
        // Simple encryption using base64 and md5
        $encrypted = base64_encode($filename);
        $encrypted = str_replace(['+', '/', '='], ['-', '_', ''], $encrypted); // URL safe
        $encrypted = substr(md5($encrypted), 0, 8); // Take first 8 characters of MD5
        return $encrypted;
    }

    public function view_barcode($filename) {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            // Return a default "access denied" image or redirect
            $this->output->set_status_header(403);
            $this->output->set_output('Access Denied');
            return;
        }
        
        // Additional security: Check if user has permission to view barcode images
        // You can add role-based checks here if needed
        $user_role = $this->session->userdata('role');
        if (!$user_role) {
            $this->output->set_status_header(403);
            $this->output->set_output('Access Denied - No role assigned');
            return;
        }
        
        // Validate filename for security
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            show_404();
            return;
        }

        $file_path = FCPATH . 'assets/uploads/barcode/' . $filename;
        
        if (!file_exists($file_path)) {
            show_404();
            return;
        }

        // Get file info
        $file_info = pathinfo($file_path);
        $extension = strtolower($file_info['extension']);
        
        // Set appropriate content type
        $content_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];

        if (!isset($content_types[$extension])) {
            show_404();
            return;
        }

        // Log access for security monitoring
        log_message('info', 'Barcode image accessed by user: ' . $this->session->userdata('username') . ' - File: ' . $filename);

        // Set security headers
        header('Content-Type: ' . $content_types[$extension]);
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=3600'); // Cache for 1 hour, private
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        // Output file
        readfile($file_path);
        exit;
    }
}
