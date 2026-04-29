<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Qr_data extends CI_Controller {

    private static $max_barcode_len = 4096;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('qr_data_model');

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index() {
        $data['title'] = 'QR Data';
        $filters = array(
            'booking_id' => (string) $this->input->get('booking_id', true),
            'barcode_data' => (string) $this->input->get('barcode_data', true),
            'tanggaljam' => (string) $this->input->get('tanggaljam', true)
        );
        $data['filters'] = $filters;
        $data['qr_list'] = $this->qr_data_model->get_filtered($filters);

        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('qr_data/index', $data);
        $this->load->view('templates/footer');
    }

    public function save() {
        $this->output->set_content_type('application/json');

        if (!$this->session->userdata('logged_in')) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Unauthorized'
            )));
            return;
        }

        $barcode = $this->input->post('barcode_data', true);
        if ($barcode === null || $barcode === '') {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Barcode Data wajib diisi.'
            )));
            return;
        }
        $barcode = trim((string) $barcode);
        if (strlen($barcode) > self::$max_barcode_len) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Barcode Data terlalu panjang.'
            )));
            return;
        }

        $booking_post = $this->input->post('booking_id', true);
        $booking = $booking_post !== null && $booking_post !== ''
            ? substr(trim((string) $booking_post), 0, 32)
            : '';
        if ($booking === '' && $barcode !== '') {
            $booking = substr($barcode, 0, 32);
        }

        $ticket_date = (string) $this->input->post('ticket_date', true);
        $ticket_time = (string) $this->input->post('ticket_time', true);
        if (strlen($ticket_date) > 128) {
            $ticket_date = substr($ticket_date, 0, 128);
        }
        if (strlen($ticket_time) > 64) {
            $ticket_time = substr($ticket_time, 0, 64);
        }

        $user_id = $this->session->userdata('user_id');
        $created_by = ($user_id !== null && $user_id !== '') ? (int) $user_id : null;

        $row = array(
            'booking_id' => $booking,
            'barcode_data' => $barcode,
            'ticket_date' => $ticket_date,
            'ticket_time' => $ticket_time,
            'foto_qr_path' => null,
            'created_by' => $created_by
        );

        // Compatibility: beberapa DB lama memakai nama kolom tanggal/waktu.
        $fields = $this->db->list_fields('qr_data');
        if (!in_array('ticket_date', $fields, true) && in_array('tanggal', $fields, true)) {
            $row['tanggal'] = $row['ticket_date'];
            unset($row['ticket_date']);
        }
        if (!in_array('ticket_time', $fields, true) && in_array('waktu', $fields, true)) {
            $row['waktu'] = $row['ticket_time'];
            unset($row['ticket_time']);
        }
        if (!in_array('foto_qr_path', $fields, true)) {
            unset($row['foto_qr_path']);
        }
        if (!in_array('created_by', $fields, true)) {
            unset($row['created_by']);
        }

        $insert = $this->qr_data_model->insert($row);
        if (!$insert['ok']) {
            $db_msg = isset($insert['error']['message']) ? (string) $insert['error']['message'] : 'Database error';
            $hint = '';
            if (stripos($db_msg, 'Unknown column') !== false) {
                $hint = ' Struktur tabel qr_data belum sesuai. Jalankan/cek file sql/qr_data.sql.';
            }
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Gagal simpan ke database: ' . $db_msg . $hint
            )));
            return;
        }

        $this->output->set_output(json_encode(array(
            'success' => true,
            'message' => 'Data QR berhasil disimpan.',
            'id' => $insert['id']
        )));
    }

    public function delete($id = null) {
        $this->output->set_content_type('application/json');

        if (!$this->session->userdata('logged_in')) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Unauthorized'
            )));
            return;
        }

        $id = (int) $id;
        if ($id < 1) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'ID tidak valid.'
            )));
            return;
        }

        $ok = $this->qr_data_model->delete_by_id($id);
        if (!$ok) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Data gagal dihapus.'
            )));
            return;
        }

        $this->output->set_output(json_encode(array(
            'success' => true,
            'message' => 'Data berhasil dihapus.'
        )));
    }
}
