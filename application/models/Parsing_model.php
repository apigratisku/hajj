<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parsing_model extends CI_Model {

    private $table = 'parsing';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Simpan data parsing ke database
     */
    public function save_parsing_data($data) {
        try {
            // Validate required fields
            if (empty($data['nama']) || empty($data['no_paspor']) || empty($data['no_visa']) || empty($data['tanggal_lahir'])) {
                return array('success' => false, 'message' => 'Data parsing tidak lengkap');
            }

            // Prepare data for insert
            $insert_data = array(
                'nama' => trim($data['nama']),
                'no_paspor' => trim($data['no_paspor']),
                'no_visa' => trim($data['no_visa']),
                'tanggal_lahir' => $data['tanggal_lahir'],
                'file_name' => isset($data['file_name']) ? $data['file_name'] : '',
                'file_size' => isset($data['file_size']) ? $data['file_size'] : 0,
                'parsed_by' => isset($data['parsed_by']) ? $data['parsed_by'] : 'system',
                'status' => 'active'
            );

            // Check if data already exists
            $this->db->where('no_paspor', $insert_data['no_paspor']);
            $this->db->where('no_visa', $insert_data['no_visa']);
            $this->db->where('status', 'active');
            $existing = $this->db->get($this->table);

            if ($existing->num_rows() > 0) {
                // Update existing record
                $this->db->where('no_paspor', $insert_data['no_paspor']);
                $this->db->where('no_visa', $insert_data['no_visa']);
                $this->db->where('status', 'active');
                $result = $this->db->update($this->table, $insert_data);
                
                return array('success' => true, 'message' => 'Data parsing berhasil diupdate', 'action' => 'update');
            } else {
                // Insert new record
                $result = $this->db->insert($this->table, $insert_data);
                
                if ($result) {
                    return array('success' => true, 'message' => 'Data parsing berhasil disimpan', 'action' => 'insert', 'id' => $this->db->insert_id());
                } else {
                    return array('success' => false, 'message' => 'Gagal menyimpan data parsing');
                }
            }

        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Simpan multiple data parsing
     */
    public function save_multiple_parsing_data($data_array, $file_info = array()) {
        try {
            $results = array(
                'success' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => array()
            );

            foreach ($data_array as $index => $data) {
                // Add file info to each data
                $data['file_name'] = isset($file_info['name']) ? $file_info['name'] : '';
                $data['file_size'] = isset($file_info['size']) ? $file_info['size'] : 0;
                $data['parsed_by'] = isset($file_info['parsed_by']) ? $file_info['parsed_by'] : 'system';

                $result = $this->save_parsing_data($data);
                
                if ($result['success']) {
                    if ($result['action'] === 'insert') {
                        $results['success']++;
                    } else {
                        $results['updated']++;
                    }
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Record " . ($index + 1) . ": " . $result['message'];
                }
            }

            return $results;

        } catch (Exception $e) {
            return array('success' => 0, 'updated' => 0, 'failed' => count($data_array), 'errors' => array($e->getMessage()));
        }
    }

    /**
     * Get parsing data dengan pagination
     */
    public function get_parsing_data($limit = 10, $offset = 0, $search = '') {
        try {
            $this->db->select('*');
            $this->db->from($this->table);
            $this->db->where('status', 'active');

            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('nama', $search);
                $this->db->or_like('no_paspor', $search);
                $this->db->or_like('no_visa', $search);
                $this->db->group_end();
            }

            $this->db->order_by('parsed_at', 'DESC');
            $this->db->limit($limit, $offset);

            $query = $this->db->get();
            return $query->result_array();

        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Count total parsing data
     */
    public function count_parsing_data($search = '') {
        try {
            $this->db->from($this->table);
            $this->db->where('status', 'active');

            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('nama', $search);
                $this->db->or_like('no_paspor', $search);
                $this->db->or_like('no_visa', $search);
                $this->db->group_end();
            }

            return $this->db->count_all_results();

        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get parsing statistics
     */
    public function get_parsing_statistics() {
        try {
            $stats = array();

            // Total records
            $this->db->where('status', 'active');
            $stats['total_records'] = $this->db->count_all_results($this->table);

            // Records today
            $this->db->where('status', 'active');
            $this->db->where('DATE(parsed_at)', date('Y-m-d'));
            $stats['today_records'] = $this->db->count_all_results($this->table);

            // Records this month
            $this->db->where('status', 'active');
            $this->db->where('YEAR(parsed_at)', date('Y'));
            $this->db->where('MONTH(parsed_at)', date('m'));
            $stats['month_records'] = $this->db->count_all_results($this->table);

            // Unique passports
            $this->db->select('COUNT(DISTINCT no_paspor) as unique_passports');
            $this->db->from($this->table);
            $this->db->where('status', 'active');
            $query = $this->db->get();
            $result = $query->row_array();
            $stats['unique_passports'] = $result['unique_passports'];

            return $stats;

        } catch (Exception $e) {
            return array(
                'total_records' => 0,
                'today_records' => 0,
                'month_records' => 0,
                'unique_passports' => 0
            );
        }
    }

    /**
     * Check if parsing data exists
     */
    public function get_parsing_data_by_id($id) {
        try {
            $this->db->where('id', $id);
            $this->db->where('status', 'active');
            $query = $this->db->get($this->table);
            return $query->row_array();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Delete parsing data (soft delete)
     */
    public function delete_parsing_data($id) {
        try {
            // Check if data exists
            $existing_data = $this->get_parsing_data_by_id($id);
            if (!$existing_data) {
                return array('success' => false, 'message' => 'Data parsing tidak ditemukan');
            }

            // Perform soft delete
            $this->db->where('id', $id);
            $result = $this->db->update($this->table, array('status' => 'deleted'));

            if ($result) {
                return array('success' => true, 'message' => 'Data parsing berhasil dihapus');
            } else {
                return array('success' => false, 'message' => 'Gagal menghapus data parsing');
            }

        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete parsing data (soft delete)
     */
    public function bulk_delete_parsing_data($ids) {
        try {
            if (empty($ids) || !is_array($ids)) {
                return array('success' => false, 'message' => 'ID data tidak valid');
            }

            // Validate all IDs
            $valid_ids = array();
            foreach ($ids as $id) {
                if (is_numeric($id) && $id > 0) {
                    $valid_ids[] = $id;
                }
            }

            if (empty($valid_ids)) {
                return array('success' => false, 'message' => 'Tidak ada ID yang valid');
            }

            // Perform bulk soft delete
            $this->db->where_in('id', $valid_ids);
            $result = $this->db->update($this->table, array('status' => 'deleted'));

            if ($result) {
                return array('success' => true, 'message' => count($valid_ids) . ' data parsing berhasil dihapus');
            } else {
                return array('success' => false, 'message' => 'Gagal menghapus data parsing');
            }

        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }
    }
}
?>