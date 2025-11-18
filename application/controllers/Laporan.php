<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('transaksi_model');
        $this->load->library('session');
        $this->load->helper(['url', 'form']);

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        $default_end = date('Y-m-d');
        $default_start = date('Y-m-d', strtotime('-6 days'));

        $start_date = $this->input->get('start_date') ?: $default_start;
        $end_date = $this->input->get('end_date') ?: $default_end;
        $selected_flag_doc = trim($this->input->get('flag_doc'));
        
        // Handle nama_travel as array (from checkbox) or string (backward compatibility)
        $selected_travel = $this->input->get('nama_travel');
        if (is_array($selected_travel)) {
            $selected_travel = array_filter($selected_travel); // Remove empty values
            if (empty($selected_travel)) {
                $selected_travel = [];
            }
        } else {
            $selected_travel = trim($selected_travel);
            if (empty($selected_travel)) {
                $selected_travel = [];
            } else {
                $selected_travel = [$selected_travel]; // Convert to array for consistency
            }
        }

        // Normalize date order
        if (strtotime($start_date) > strtotime($end_date)) {
            $tmp = $start_date;
            $start_date = $end_date;
            $end_date = $tmp;
        }

        $filters = [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];

        if (!empty($selected_flag_doc)) {
            $filters['flag_doc'] = $selected_flag_doc;
        }
        if (!empty($selected_travel)) {
            $filters['nama_travel'] = $selected_travel;
        }

        $summary = $this->transaksi_model->get_flagdoc_summary($filters);
        $flag_doc_list = $this->transaksi_model->get_unique_flag_doc();
        $travel_list = $this->transaksi_model->get_unique_nama_travel();

        // Group summary by nama_travel
        $summary_by_travel = [];
        $totals = [
            'todo' => 0,
            'already' => 0,
            'done' => 0,
            'total' => 0,
        ];

        // Get sudah_report status for each row
        $sudah_report_status = [];
        foreach ($summary as $row) {
            $travel_name = !empty($row->nama_travel) ? $row->nama_travel : 'Tanpa Travel';
            $travel_value = !empty($row->nama_travel) ? $row->nama_travel : 'null';
            
            // Create unique key for status lookup
            $status_key = $row->flag_doc . '|' . $travel_value . '|' . $row->tanggal_upload;
            
            // Get status sudah_report
            $status = $this->transaksi_model->get_sudah_report_status(
                $row->flag_doc,
                $travel_value,
                $row->tanggal_upload
            );
            
            $sudah_report_status[$status_key] = $status;
            $row->sudah_report = $status; // Attach status to row object
            
            if (!isset($summary_by_travel[$travel_name])) {
                $summary_by_travel[$travel_name] = [
                    'rows' => [],
                    'totals' => [
                        'todo' => 0,
                        'already' => 0,
                        'done' => 0,
                        'total' => 0,
                    ],
                ];
            }
            
            $summary_by_travel[$travel_name]['rows'][] = $row;
            $summary_by_travel[$travel_name]['totals']['todo'] += (int) $row->todo_count;
            $summary_by_travel[$travel_name]['totals']['already'] += (int) $row->already_count;
            $summary_by_travel[$travel_name]['totals']['done'] += (int) $row->done_count;
            $summary_by_travel[$travel_name]['totals']['total'] += (int) $row->total;
            
            $totals['todo'] += (int) $row->todo_count;
            $totals['already'] += (int) $row->already_count;
            $totals['done'] += (int) $row->done_count;
            $totals['total'] += (int) $row->total;
        }

        // Sort by travel name
        ksort($summary_by_travel);

        $data = [
            'title' => 'Laporan Flag Dokumen',
            'summary' => $summary,
            'summary_by_travel' => $summary_by_travel,
            'sudah_report_status' => $sudah_report_status,
            'flag_doc_list' => $flag_doc_list,
            'travel_list' => $travel_list,
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'flag_doc' => $selected_flag_doc,
                'nama_travel' => $selected_travel,
            ],
            'totals' => $totals,
        ];

        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('laporan/index', $data);
        $this->load->view('templates/footer');
    }

    public function export() {
        $start_date = $this->input->get('start_date') ?: date('Y-m-d', strtotime('-6 days'));
        $end_date = $this->input->get('end_date') ?: date('Y-m-d');
        $flag_doc = trim($this->input->get('flag_doc_export'));
        
        // Handle nama_travel as array (from checkbox) or string (backward compatibility)
        $nama_travel = $this->input->get('nama_travel');
        if (is_array($nama_travel)) {
            $nama_travel = array_filter($nama_travel); // Remove empty values
            if (empty($nama_travel)) {
                $nama_travel = [];
            }
        } else {
            $nama_travel = trim($nama_travel);
            if (empty($nama_travel)) {
                $nama_travel = [];
            } else {
                $nama_travel = [$nama_travel]; // Convert to array for consistency
            }
        }

        $redirect_params = http_build_query([
            'start_date' => $start_date,
            'end_date' => $end_date,
            'flag_doc' => $this->input->get('flag_doc'),
        ]);
        if (!empty($nama_travel)) {
            foreach ($nama_travel as $travel) {
                $redirect_params .= '&nama_travel[]=' . urlencode($travel);
            }
        }

        if (empty($flag_doc)) {
            $this->session->set_flashdata('error', 'Silakan pilih Flag Doc untuk diekspor.');
            redirect('laporan?' . $redirect_params);
        }

        if (strtotime($start_date) > strtotime($end_date)) {
            $tmp = $start_date;
            $start_date = $end_date;
            $end_date = $tmp;
        }

        $filters = [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
        if (!empty($nama_travel)) {
            $filters['nama_travel'] = $nama_travel;
        }

        $summary = $this->transaksi_model->get_flagdoc_summary_by_flag($flag_doc, $filters);

        if (empty($summary)) {
            $this->session->set_flashdata('error', 'Data laporan tidak ditemukan untuk Flag Doc terpilih.');
            redirect('laporan?' . $redirect_params);
        }

        require_once APPPATH . 'third_party/PHPExcel/Classes/PHPExcel.php';

        $excel = new PHPExcel();
        $excel->getProperties()
            ->setCreator('Sistem Hajj')
            ->setLastModifiedBy('Sistem Hajj')
            ->setTitle('Laporan Flag Doc ' . $flag_doc)
            ->setSubject('Laporan Flag Doc')
            ->setDescription('Laporan ringkas Flag Doc berdasarkan tanggal upload');

        $sheet = $excel->setActiveSheetIndex(0);
        $sheet->setTitle('Laporan Flag Doc');

        $headers = ['No', 'Tanggal Upload', 'Flag Doc', 'Todo', 'Already', 'Done', 'Total'];
        foreach ($headers as $idx => $header) {
            $col = PHPExcel_Cell::stringFromColumnIndex($idx);
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => '1e3a5f'],
                ],
            ]);
        }

        $rowNumber = 2;
        $report_rows = [];
        foreach ($summary as $index => $row) {
            $sheet->setCellValue('A' . $rowNumber, $index + 1);
            $sheet->setCellValue('B' . $rowNumber, date('d-m-Y', strtotime($row->tanggal_upload)));
            $sheet->setCellValue('C' . $rowNumber, $row->flag_doc);
            $sheet->setCellValue('D' . $rowNumber, (int) $row->todo_count);
            $sheet->setCellValue('E' . $rowNumber, (int) $row->already_count);
            $sheet->setCellValue('F' . $rowNumber, (int) $row->done_count);
            $sheet->setCellValue('G' . $rowNumber, (int) $row->total);

            $report_rows[] = [
                'flag_doc' => $row->flag_doc,
                'nama_travel' => !empty($row->nama_travel) ? $row->nama_travel : null,
                'tanggal_upload' => $row->tanggal_upload,
                'periode_start' => $start_date,
                'periode_end' => $end_date,
                'jumlah_todo' => (int) $row->todo_count,
                'jumlah_already' => (int) $row->already_count,
                'jumlah_done' => (int) $row->done_count,
                'jumlah_total' => (int) $row->total,
                'reported_by' => $this->session->userdata('user_id'),
                'reported_at' => date('Y-m-d H:i:s'),
            ];
            $rowNumber++;
        }

        if (!empty($report_rows)) {
            $this->db->trans_start();
            $inserted = $this->transaksi_model->insert_laporan_flag_doc_batch($report_rows);
            $this->db->trans_complete();

            if (!$inserted || $this->db->trans_status() === false) {
                $this->session->set_flashdata('error', 'Gagal mencatat metadata laporan. Silakan coba lagi.');
                redirect('laporan?' . $redirect_params);
            }
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = sprintf(
            'laporan_flagdoc_%s_%s_sampai_%s.xlsx',
            preg_replace('/[^A-Za-z0-9_\-]/', '_', $flag_doc),
            date('Ymd', strtotime($start_date)),
            date('Ymd', strtotime($end_date))
        );

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }

    /**
     * AJAX endpoint untuk update field sudah_report
     */
    public function update_sudah_report() {
        // Set header untuk JSON response
        header('Content-Type: application/json');
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            echo json_encode(['status' => false, 'message' => 'Unauthorized access']);
            return;
        }

        // Get POST parameters
        $flag_doc = trim($this->input->post('flag_doc'));
        $nama_travel = trim($this->input->post('nama_travel'));
        $tanggal_upload = trim($this->input->post('tanggal_upload'));
        $sudah_report = (int) $this->input->post('sudah_report'); // 1 or 0

        // Validate input
        if (empty($flag_doc) || empty($tanggal_upload)) {
            echo json_encode(['status' => false, 'message' => 'Flag Doc dan Tanggal Upload harus diisi']);
            return;
        }

        // Normalize nama_travel
        if (empty($nama_travel) || $nama_travel === 'Tanpa Travel') {
            $nama_travel = 'null';
        }

        // Validate sudah_report value
        if ($sudah_report !== 0 && $sudah_report !== 1) {
            echo json_encode(['status' => false, 'message' => 'Nilai sudah_report tidak valid']);
            return;
        }

        // Update database
        $affected_rows = $this->transaksi_model->update_sudah_report(
            $flag_doc,
            $nama_travel,
            $tanggal_upload,
            $sudah_report
        );

        if ($affected_rows !== false && $affected_rows >= 0) {
            $status_text = $sudah_report == 1 ? 'sudah' : 'belum';
            echo json_encode([
                'status' => true,
                'message' => "Status sudah report berhasil diupdate menjadi {$status_text} untuk {$affected_rows} data peserta",
                'affected_rows' => $affected_rows
            ]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal mengupdate status sudah report']);
        }
    }
}

