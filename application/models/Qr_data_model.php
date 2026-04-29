<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Qr_data_model extends CI_Model {

    private $table = 'qr_data';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * @param array $data keys: booking_id, barcode_data, ticket_date, ticket_time, foto_qr_path, created_by
     * @return int insert id
     */
    public function insert($data) {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $prev_debug = $this->db->db_debug;
        $this->db->db_debug = false;
        $ok = $this->db->insert($this->table, $data);
        $err = $this->db->error();
        $this->db->db_debug = $prev_debug;

        return array(
            'ok' => (bool) $ok,
            'id' => $ok ? (int) $this->db->insert_id() : 0,
            'error' => is_array($err) ? $err : array('code' => 0, 'message' => 'Unknown DB error')
        );
    }

    /**
     * Cek baris dengan booking_id dan barcode_data sama persis (anti-duplikat).
     *
     * @param string $booking_id
     * @param string $barcode_data
     * @return int|null id baris pertama jika ada, null jika belum ada
     */
    public function exists_by_booking_and_barcode($booking_id, $barcode_data) {
        $this->db->select('id');
        $this->db->from($this->table);
        $this->db->where('booking_id', (string) $booking_id);
        $this->db->where('barcode_data', (string) $barcode_data);
        $this->db->limit(1);
        $row = $this->db->get()->row();
        if ($row === null || !isset($row->id)) {
            return null;
        }
        return (int) $row->id;
    }

    /**
     * @return object[]
     */
    public function get_all() {
        $this->db->from($this->table);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }

    private function apply_filters($filters = array()) {
        $fields = $this->db->list_fields($this->table);
        $date_col = in_array('ticket_date', $fields, true) ? 'ticket_date' : (in_array('tanggal', $fields, true) ? 'tanggal' : null);
        $time_col = in_array('ticket_time', $fields, true) ? 'ticket_time' : (in_array('waktu', $fields, true) ? 'waktu' : null);

        $booking = isset($filters['booking_id']) ? trim((string) $filters['booking_id']) : '';
        $barcode = isset($filters['barcode_data']) ? trim((string) $filters['barcode_data']) : '';
        $tanggaljam = isset($filters['tanggaljam']) ? trim((string) $filters['tanggaljam']) : '';

        if ($booking !== '') {
            $this->db->like('booking_id', $booking);
        }
        if ($barcode !== '') {
            $this->db->like('barcode_data', $barcode);
        }
        if ($tanggaljam !== '' && $date_col !== null && $time_col !== null) {
            $concat_expr = "CONCAT(COALESCE(`{$date_col}`, ''), ' ', COALESCE(`{$time_col}`, '')) LIKE " . $this->db->escape('%' . $this->db->escape_like_str($tanggaljam) . '%');
            $this->db->where($concat_expr, null, false);
        } elseif ($tanggaljam !== '' && $date_col !== null) {
            $this->db->like($date_col, $tanggaljam);
        }
    }

    /**
     * @param array $filters keys: booking_id, barcode_data, tanggaljam
     * @param int|null $limit
     * @param int|null $offset
     * @return object[]
     */
    public function get_filtered($filters = array(), $limit = null, $offset = null) {
        $this->db->from($this->table);
        $this->apply_filters($filters);
        $this->db->order_by('id', 'DESC');
        if ($limit !== null) {
            $safe_limit = max(1, (int) $limit);
            $safe_offset = max(0, (int) $offset);
            $this->db->limit($safe_limit, $safe_offset);
        }
        return $this->db->get()->result();
    }

    /**
     * @param array $filters keys: booking_id, barcode_data, tanggaljam
     * @return int
     */
    public function count_filtered($filters = array()) {
        $this->db->from($this->table);
        $this->apply_filters($filters);
        return (int) $this->db->count_all_results();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete_by_id($id) {
        $this->db->where('id', (int) $id);
        return (bool) $this->db->delete($this->table);
    }
}
