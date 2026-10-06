<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model untuk mencatat batch import dan daftar peserta hasil import,
 * sehingga proses import dapat dibatalkan (rollback data yang diimport).
 */
class Import_batch_model extends CI_Model {

    private $table_batch = 'import_batch';
    private $table_item  = 'import_batch_item';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Cek apakah tabel batch sudah tersedia (dibuat via Setup / sql/import_batch.sql).
     */
    public function is_available() {
        return $this->db->table_exists($this->table_batch) && $this->db->table_exists($this->table_item);
    }

    /**
     * Simpan satu record batch import beserta daftar id peserta hasil import.
     *
     * @param array $batch  ['nama_file','flag_doc','user_operator']
     * @param array $id_peserta_list  daftar id peserta hasil insert
     * @return int|false id_batch atau false jika gagal
     */
    public function create_batch($batch, $id_peserta_list) {
        if (!$this->is_available()) {
            log_message('error', 'Import_batch_model: tabel import_batch/import_batch_item tidak ditemukan');
            return false;
        }

        try {
            $this->db->insert($this->table_batch, [
                'nama_file'     => isset($batch['nama_file']) ? $batch['nama_file'] : null,
                'flag_doc'      => isset($batch['flag_doc']) ? $batch['flag_doc'] : null,
                'total_data'    => is_array($id_peserta_list) ? count($id_peserta_list) : 0,
                'user_operator' => isset($batch['user_operator']) ? $batch['user_operator'] : null,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            $id_batch = $this->db->insert_id();
            if (!$id_batch) {
                return false;
            }

            if (!empty($id_peserta_list)) {
                $rows = [];
                foreach ($id_peserta_list as $id_peserta) {
                    $rows[] = [
                        'id_batch'   => $id_batch,
                        'id_peserta' => (int)$id_peserta,
                    ];
                }
                $this->db->insert_batch($this->table_item, $rows);
            }

            return $id_batch;
        } catch (Exception $e) {
            log_message('error', 'Import_batch_model::create_batch error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil daftar batch import terbaru beserta info jumlah data.
     *
     * @param int $limit
     * @return array
     */
    public function get_batches($limit = 20) {
        if (!$this->is_available()) {
            return [];
        }

        $this->db->from($this->table_batch);
        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('id_batch', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }
        return $this->db->get()->result();
    }

    /**
     * Ambil detail batch.
     */
    public function get_batch($id_batch) {
        if (!$this->is_available()) {
            return null;
        }
        $this->db->where('id_batch', $id_batch);
        return $this->db->get($this->table_batch)->row();
    }

    /**
     * Ambil daftar id peserta pada batch tertentu.
     *
     * @param int $id_batch
     * @return array daftar id_peserta
     */
    public function get_item_ids($id_batch) {
        if (!$this->is_available()) {
            return [];
        }
        $this->db->select('id_peserta');
        $this->db->where('id_batch', $id_batch);
        $rows = $this->db->get($this->table_item)->result();
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int)$row->id_peserta;
        }
        return $ids;
    }

    /**
     * Hapus record batch & itemnya.
     */
    public function delete_batch($id_batch) {
        if (!$this->is_available()) {
            return false;
        }
        $this->db->where('id_batch', $id_batch);
        $this->db->delete($this->table_item);
        $this->db->where('id_batch', $id_batch);
        return $this->db->delete($this->table_batch);
    }
}
