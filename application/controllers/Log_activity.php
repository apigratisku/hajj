<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log_activity extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('log_activity_model');
        $this->load->model('peserta_model');
        $this->load->library('session');
        $this->load->helper('url');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    /**
     * Main index page - show all activity logs
     */
    public function index()
    {
        $data['title'] = 'Log Aktifitas User';
        
        // Get filters from GET parameters
        $filters = [
            'user_operator' => $this->input->get('user_operator'),
            'tanggal_dari' => $this->input->get('tanggal_dari'),
            'tanggal_sampai' => $this->input->get('tanggal_sampai'),
            'aktivitas' => $this->input->get('aktivitas'),
            'id_peserta' => $this->input->get('id_peserta')
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return !empty($value);
        });
        
        // Pagination
        $page = $this->input->get('page') ?: 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        // Get logs
        $data['logs'] = $this->log_activity_model->get_all_logs($limit, $offset, $filters);
        $data['total_logs'] = $this->log_activity_model->count_logs($filters);
        $data['current_page'] = $page;
        $data['total_pages'] = ceil($data['total_logs'] / $limit);
        
        // Get filter options
        $data['unique_users'] = $this->log_activity_model->get_unique_users();
        
        // Get statistics
        $data['statistics'] = $this->log_activity_model->get_log_statistics($filters);
        $data['top_users'] = $this->log_activity_model->get_top_users(10, $filters);
        $data['activity_summary'] = $this->log_activity_model->get_activity_summary($filters);
        
        // Pass filters to view
        $data['filters'] = $filters;
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('log_activity/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * View logs for specific participant
     */
    public function view_peserta($id_peserta = null)
    {
        if (!$id_peserta) {
            show_404();
        }
        
        $data['title'] = 'Log Aktifitas Peserta';
        
        // Get participant info
        $data['peserta'] = $this->peserta_model->get_by_id($id_peserta);
        if (!$data['peserta']) {
            show_404();
        }
        
        // Get logs for this participant
        $data['logs'] = $this->log_activity_model->get_logs_by_peserta($id_peserta);
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('log_activity/view_peserta', $data);
        $this->load->view('templates/footer');
    }

    /**
     * View logs for specific user
     */
    public function view_user($user_operator = null)
    {
        if (!$user_operator) {
            show_404();
        }
        
        $data['title'] = 'Log Aktifitas User: ' . $user_operator;
        $data['user_operator'] = $user_operator;
        
        // Get logs for this user
        $data['logs'] = $this->log_activity_model->get_logs_by_user($user_operator);
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('log_activity/view_user', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Export logs to Excel
     */
    public function export_excel()
    {
        // Get filters from GET parameters
        $filters = [
            'user_operator' => $this->input->get('user_operator'),
            'tanggal_dari' => $this->input->get('tanggal_dari'),
            'tanggal_sampai' => $this->input->get('tanggal_sampai'),
            'aktivitas' => $this->input->get('aktivitas'),
            'id_peserta' => $this->input->get('id_peserta')
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return !empty($value);
        });
        
        // Get all logs (no limit for export)
        $logs = $this->log_activity_model->get_all_logs(10000, 0, $filters);
        
        // Load Excel library
        $this->load->library('excel');
        
        // Create new Excel object
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set title
        $sheet->setTitle('Log Aktifitas User');
        
        // Set headers
        $headers = [
            'A1' => 'ID Log',
            'B1' => 'ID Peserta',
            'C1' => 'Nama Peserta',
            'D1' => 'Nomor Peserta',
            'E1' => 'User Operator',
            'F1' => 'Tanggal',
            'G1' => 'Jam',
            'H1' => 'Aktivitas',
            'I1' => 'Created At'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'E0E0E0']
            ]
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        // Add data
        $row = 2;
        foreach ($logs as $log) {
            $sheet->setCellValue('A' . $row, $log->id_log);
            $sheet->setCellValue('B' . $row, $log->id_peserta);
            $sheet->setCellValue('C' . $row, $log->nama_peserta);
            $sheet->setCellValue('D' . $row, $log->nomor_peserta);
            $sheet->setCellValue('E' . $row, $log->user_operator);
            $sheet->setCellValue('F' . $row, $log->tanggal);
            $sheet->setCellValue('G' . $row, $log->jam);
            $sheet->setCellValue('H' . $row, $log->aktivitas);
            $sheet->setCellValue('I' . $row, $log->created_at);
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Set filename
        $filename = 'Log_Aktifitas_User_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Save file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * AJAX endpoint to get logs for specific participant
     */
    public function get_logs_ajax()
    {
        $id_peserta = $this->input->post('id_peserta');
        
        if (!$id_peserta) {
            echo json_encode(['status' => false, 'message' => 'ID Peserta tidak valid']);
            return;
        }
        
        $logs = $this->log_activity_model->get_logs_by_peserta($id_peserta, 20);
        
        echo json_encode([
            'status' => true,
            'data' => $logs
        ]);
    }

    /**
     * AJAX endpoint to get activity statistics
     */
    public function get_statistics_ajax()
    {
        $filters = [
            'tanggal_dari' => $this->input->post('tanggal_dari'),
            'tanggal_sampai' => $this->input->post('tanggal_sampai')
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return !empty($value);
        });
        
        $statistics = $this->log_activity_model->get_log_statistics($filters);
        $top_users = $this->log_activity_model->get_top_users(10, $filters);
        $activity_summary = $this->log_activity_model->get_activity_summary($filters);
        
        echo json_encode([
            'status' => true,
            'statistics' => $statistics,
            'top_users' => $top_users,
            'activity_summary' => $activity_summary
        ]);
    }

    /**
     * Clean old logs (admin only)
     */
    public function clean_old_logs()
    {
        // Check if user is admin (you can modify this check as needed)
        if ($this->session->userdata('username') !== 'admin') {
            show_404();
        }
        
        $days = $this->input->post('days') ?: 90;
        
        $result = $this->log_activity_model->delete_old_logs($days);
        
        if ($result) {
            $this->session->set_flashdata('success', "Berhasil menghapus log aktivitas yang lebih tua dari {$days} hari");
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus log aktivitas lama');
        }
        
        redirect('log_activity');
    }
}
