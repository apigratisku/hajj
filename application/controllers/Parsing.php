<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parsing extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Load required libraries
        $this->load->library('upload');
        $this->load->library('form_validation');
        $this->load->helper('file');
        $this->load->model('Parsing_model');
        $this->load->database();
    }

    public function index() {
        $data['title'] = 'Parsing Data VISA';
        $data['page'] = 'parsing/index';
        
        // Get parsing statistics
        try {
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

    /**
     * View parsed data
     */
    public function view_data() {
        $data['title'] = 'Data Parsing VISA';
        $data['page'] = 'parsing/view_data';
        
        // Get pagination parameters
        $page = $this->input->get('page') ?: 1;
        $search = $this->input->get('search') ?: '';
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get data
        $data['parsing_data'] = $this->Parsing_model->get_parsing_data($limit, $offset, $search);
        $data['total_records'] = $this->Parsing_model->count_parsing_data($search);
        $data['current_page'] = $page;
        $data['total_pages'] = ceil($data['total_records'] / $limit);
        $data['search'] = $search;
        
        // Get statistics
        try {
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
        $this->load->view('parsing/view_data', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Delete parsing data
     */
    public function delete_data($id) {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu');
            redirect('auth');
        }
        
        $result = $this->Parsing_model->delete_parsing_data($id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('parsing/view_data');
    }

    public function upload_pdf() {
        // Clean any existing output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON header for AJAX response
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        // Enable error logging but disable display
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        
        // Create error log file if it doesn't exist
        $log_file = APPPATH . 'logs/parsing_upload.log';
        if (!file_exists($log_file)) {
            if (!is_dir(APPPATH . 'logs/')) {
                mkdir(APPPATH . 'logs/', 0755, true);
            }
            file_put_contents($log_file, '');
        }
        
        // Log the request to file
        error_log(date('Y-m-d H:i:s') . " - Upload PDF request received\n", 3, $log_file);
        error_log(date('Y-m-d H:i:s') . " - POST data: " . json_encode($_POST) . "\n", 3, $log_file);
        error_log(date('Y-m-d H:i:s') . " - FILES data: " . json_encode($_FILES) . "\n", 3, $log_file);
        
        try {
            // Check if user is logged in
            error_log(date('Y-m-d H:i:s') . " - Checking user authentication\n", 3, $log_file);
            
            if (!$this->session->userdata('logged_in')) {
                error_log(date('Y-m-d H:i:s') . " - User not authenticated\n", 3, $log_file);
                http_response_code(401);
                $response = json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
                echo $response;
                exit;
            }
            
            error_log(date('Y-m-d H:i:s') . " - User authenticated: " . $this->session->userdata('username') . "\n", 3, $log_file);

            // Validate file upload
            if (empty($_FILES['pdf_file']['name'])) {
                http_response_code(400);
                $response = json_encode(['success' => false, 'message' => 'Pilih file PDF terlebih dahulu']);
                echo $response;
                exit;
            }

            $file = $_FILES['pdf_file'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validate file extension
            if ($file_ext !== 'pdf') {
                http_response_code(400);
                $response = json_encode(['success' => false, 'message' => 'File harus berformat PDF']);
                echo $response;
                exit;
            }

            // Validate file size (max 100MB)
            if ($file['size'] > 100 * 1024 * 1024) {
                http_response_code(400);
                $response = json_encode(['success' => false, 'message' => 'Ukuran file maksimal 100MB']);
                echo $response;
                exit;
            }

            // Log upload attempt
            log_message('info', 'PDF upload attempt started');
            log_message('info', 'File info: ' . json_encode($_FILES['pdf_file']));
            
            // Create upload directory if not exists
            $upload_path = './uploads/parsing/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
                log_message('info', 'Created upload directory: ' . $upload_path);
            }

            // Generate unique filename
            $filename = 'visa_parsing_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.pdf';
            $file_path = $upload_path . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                log_message('error', 'Failed to move uploaded file from ' . $file['tmp_name'] . ' to ' . $file_path);
                throw new Exception('Gagal mengupload file');
            }
            
            log_message('info', 'File uploaded successfully: ' . $file_path);

           // Parse PDF content
           $parsed_data = $this->parse_pdf_visa($file_path);
           
           if (empty($parsed_data)) {
               // Clean up uploaded file
               unlink($file_path);
               error_log(date('Y-m-d H:i:s') . " - No data extracted from PDF\n", 3, $log_file);
               http_response_code(400);
               $response = json_encode(['success' => false, 'message' => 'Tidak dapat mengekstrak data VISA dari file PDF']);
               echo $response;
               exit;
           }

           error_log(date('Y-m-d H:i:s') . " - Extracted " . count($parsed_data) . " records from PDF\n", 3, $log_file);

           // Save parsed data to database
           $file_info = array(
               'name' => $file['name'],
               'size' => $file['size'],
               'parsed_by' => $this->session->userdata('username')
           );

           $save_result = $this->Parsing_model->save_multiple_parsing_data($parsed_data, $file_info);
           
           error_log(date('Y-m-d H:i:s') . " - Database save result: " . json_encode($save_result) . "\n", 3, $log_file);

           // Store parsed data in session for download (backup)
           $this->session->set_userdata('parsed_visa_data', $parsed_data);
           $this->session->set_userdata('parsed_filename', $filename);

           // Clean up uploaded file
           unlink($file_path);

           // Prepare success message
           $message = 'File PDF berhasil diparsing dan disimpan ke database. ';
           $message .= 'Ditemukan ' . count($parsed_data) . ' data VISA. ';
           $message .= 'Berhasil disimpan: ' . $save_result['success'] . ', ';
           $message .= 'Diupdate: ' . $save_result['updated'] . ', ';
           $message .= 'Gagal: ' . $save_result['failed'];

           // Return success response
           $response = json_encode([
               'success' => true, 
               'message' => $message,
               'count' => count($parsed_data),
               'saved' => $save_result['success'],
               'updated' => $save_result['updated'],
               'failed' => $save_result['failed'],
               'data_preview' => array_slice($parsed_data, 0, 3), // Preview first 3 records
               'errors' => $save_result['errors']
           ]);
           echo $response;
           exit;

        } catch (Exception $e) {
            // Clean up uploaded file if exists
            if (isset($file_path) && file_exists($file_path)) {
                unlink($file_path);
            }
            
            error_log(date('Y-m-d H:i:s') . " - PDF parsing error: " . $e->getMessage() . "\n", 3, $log_file);
            error_log(date('Y-m-d H:i:s') . " - Stack trace: " . $e->getTraceAsString() . "\n", 3, $log_file);
            
            http_response_code(500);
            $response = json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            echo $response;
            exit;
        } catch (Error $e) {
            // Catch PHP 7+ errors
            error_log(date('Y-m-d H:i:s') . " - PHP Error: " . $e->getMessage() . "\n", 3, $log_file);
            error_log(date('Y-m-d H:i:s') . " - Stack trace: " . $e->getTraceAsString() . "\n", 3, $log_file);
            
            http_response_code(500);
            $response = json_encode(['success' => false, 'message' => 'Terjadi kesalahan PHP: ' . $e->getMessage()]);
            echo $response;
            exit;
        }
    }

    private function parse_pdf_visa($file_path) {
        // PDF parsing library is loaded via Composer autoload
        
        try {
            log_message('info', 'Starting PDF parsing for file: ' . $file_path);
            
            // Check if file exists
            if (!file_exists($file_path)) {
                log_message('error', 'PDF file does not exist: ' . $file_path);
                return [];
            }
            
            // Check file size
            $file_size = filesize($file_path);
            log_message('info', 'PDF file size: ' . $file_size . ' bytes');
            
            if ($file_size === 0) {
                log_message('error', 'PDF file is empty: ' . $file_path);
                return [];
            }
            
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($file_path);
            $text = $pdf->getText();
            
            log_message('info', 'PDF text extracted, length: ' . strlen($text));
            
            if (empty($text)) {
                log_message('warning', 'PDF text is empty, may be image-based PDF');
                return [];
            }
            
            // Parse VISA data from text
            $visa_data = $this->extract_visa_data($text);
            
            log_message('info', 'VISA data extracted: ' . count($visa_data) . ' records');
            
            return $visa_data;
            
        } catch (Exception $e) {
            log_message('error', 'PDF parsing error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }

    private function extract_visa_data($text) {
        $visa_data = [];
        
        // Split text into lines
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $current_visa = [];
        
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
            
            // Look for Visa No. pattern
            if (preg_match('/visa\s*no[\.:]?\s*([A-Z0-9]+)/i', $line, $matches)) {
                if (!empty($current_visa)) {
                    $visa_data[] = $current_visa;
                }
                $current_visa = [
                    'visa_no' => $matches[1],
                    'passport_no' => '',
                    'full_name' => '',
                    'birth_date' => ''
                ];
                continue;
            }
            
            // Look for Passport No. pattern
            if (preg_match('/passport\s*no[\.:]?\s*([A-Z0-9]+)/i', $line, $matches)) {
                if (!empty($current_visa)) {
                    $current_visa['passport_no'] = $matches[1];
                }
                continue;
            }
            
            // Look for Name pattern (Full Name)
            if (preg_match('/name[\.:]?\s*(.+)/i', $line, $matches) || 
                preg_match('/full\s*name[\.:]?\s*(.+)/i', $line, $matches)) {
                if (!empty($current_visa) && empty($current_visa['full_name'])) {
                    $current_visa['full_name'] = trim($matches[1]);
                }
                continue;
            }
            
            // Look for Birth Date pattern
            if (preg_match('/birth\s*date[\.:]?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}|\d{2,4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2})/i', $line, $matches)) {
                if (!empty($current_visa)) {
                    $current_visa['birth_date'] = $matches[1];
                }
                continue;
            }
            
            // Alternative patterns for different PDF formats
            // Look for patterns like "V123456789" or "P123456789"
            if (preg_match('/^[VP]\d{6,12}$/i', $line)) {
                if (!empty($current_visa)) {
                    $visa_data[] = $current_visa;
                }
                $current_visa = [
                    'visa_no' => $line,
                    'passport_no' => '',
                    'full_name' => '',
                    'birth_date' => ''
                ];
                continue;
            }
            
            // Look for passport patterns like "A1234567"
            if (preg_match('/^[A-Z]\d{6,12}$/', $line) && !empty($current_visa) && empty($current_visa['passport_no'])) {
                $current_visa['passport_no'] = $line;
                continue;
            }
            
            // Look for date patterns
            if (preg_match('/^\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}$/', $line) && !empty($current_visa) && empty($current_visa['birth_date'])) {
                $current_visa['birth_date'] = $line;
                continue;
            }
            
            // Look for name patterns (if line contains only letters and spaces)
            if (preg_match('/^[A-Za-z\s\.]+$/', $line) && strlen($line) > 3 && !empty($current_visa) && empty($current_visa['full_name'])) {
                $current_visa['full_name'] = $line;
                continue;
            }
        }
        
        // Add the last visa data if exists
        if (!empty($current_visa) && !empty($current_visa['visa_no'])) {
            $visa_data[] = $current_visa;
        }
        
        log_message('info', 'Extracted ' . count($visa_data) . ' visa records');
        
        // Convert to database format
        $converted_data = array();
        foreach ($visa_data as $visa) {
            if (!empty($visa['visa_no']) && !empty($visa['passport_no']) && 
                !empty($visa['full_name']) && !empty($visa['birth_date'])) {
                
                $converted_data[] = array(
                    'nama' => trim($visa['full_name']),
                    'no_paspor' => strtoupper(trim($visa['passport_no'])),
                    'no_visa' => strtoupper(trim($visa['visa_no'])),
                    'tanggal_lahir' => $this->convert_date_format($visa['birth_date'])
                );
            }
        }
        
        return $converted_data;
    }

    /**
     * Convert date format to YYYY-MM-DD
     */
    private function convert_date_format($date_str) {
        $date_str = trim($date_str);
        
        // Try different date formats
        $formats = array(
            'm/d/Y', 'd/m/Y', 'Y/m/d', 'm-d-Y', 'd-m-Y', 'Y-m-d',
            'm.d.Y', 'd.m.Y', 'Y.m.d', 'm/d/y', 'd/m/y', 'y/m/d',
            'm-d-y', 'd-m-y', 'y-m-d', 'm.d.y', 'd.m.y', 'y.m.d'
        );
        
        foreach ($formats as $format) {
            $date_obj = DateTime::createFromFormat($format, $date_str);
            if ($date_obj !== false) {
                return $date_obj->format('Y-m-d');
            }
        }
        
        // If no format matches, try to parse manually
        $date_parts = preg_split('/[\/\-\.]/', $date_str);
        if (count($date_parts) == 3) {
            $month = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
            $day = str_pad($date_parts[1], 2, '0', STR_PAD_LEFT);
            $year = $date_parts[2];
            
            // Validate year
            if (strlen($year) == 2) {
                $year = ($year > 50) ? '19' . $year : '20' . $year;
            }
            
            // Validate date
            if (checkdate($month, $day, $year)) {
                return $year . '-' . $month . '-' . $day;
            }
        }
        
        // Return default date if parsing fails
        return '1900-01-01';
    }

    public function download_excel() {
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Anda harus login terlebih dahulu');
            redirect('auth');
        }
        
        // Get parsed data from session
        $parsed_data = $this->session->userdata('parsed_visa_data');
        
        if (empty($parsed_data)) {
            $this->session->set_flashdata('error', 'Tidak ada data yang diparsing untuk didownload. Silakan upload dan parse file PDF terlebih dahulu.');
            redirect('parsing');
        }
        
        try {
            // Load PHPExcel library
            require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';
            
            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();
            
            // Set document properties
            $objPHPExcel->getProperties()
                ->setCreator('Sistem Haji')
                ->setLastModifiedBy('Sistem Haji')
                ->setTitle('Data VISA Parsing')
                ->setSubject('Data VISA yang diparsing dari PDF')
                ->setDescription('Data VISA yang berhasil diparsing dari file PDF')
                ->setKeywords('visa, parsing, pdf, excel')
                ->setCategory('Data VISA');
        
            // Set active sheet
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle('Data VISA');
            
            // Set headers
            $headers = [
                'A1' => 'Nama',
                'B1' => 'No Paspor', 
                'C1' => 'No Visa',
                'D1' => 'Tanggal Lahir'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Style headers
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => '4472C4'], // Blue color
                ],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
            
            // Add data
            $row = 2;
            foreach ($parsed_data as $data) {
                $sheet->setCellValue('A' . $row, $data['full_name']);
                $sheet->setCellValue('B' . $row, $data['passport_no']);
                $sheet->setCellValue('C' . $row, $data['visa_no']);
                $sheet->setCellValue('D' . $row, $data['birth_date']);
                $row++;
            }
            
            // Style data rows
            $dataStyle = [
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            if ($row > 2) {
                $sheet->getStyle('A2:D' . ($row - 1))->applyFromArray($dataStyle);
            }
            
            // Auto-size columns
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Set filename
            $filename = 'Data_VISA_Parsing_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Create Excel writer
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            
            // Clean up session data after successful download
            $this->session->unset_userdata('parsed_visa_data');
            $this->session->unset_userdata('parsed_filename');
            
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Excel export error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat membuat file Excel: ' . $e->getMessage());
            redirect('parsing');
        }
    }

    public function clear_session() {
        $this->session->unset_userdata('parsed_visa_data');
        $this->session->unset_userdata('parsed_filename');
        
        echo json_encode(['success' => true, 'message' => 'Session data berhasil dihapus']);
    }
}
