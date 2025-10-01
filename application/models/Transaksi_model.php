<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_model extends CI_Model {

    private $table = 'peserta';
    
    public function __construct() {
        // Suppress all PHP errors and warnings to prevent HTML output
        error_reporting(0);
        ini_set('display_errors', 0);

        // Clear any existing output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        parent::__construct();
        $this->load->database();
    }
    
    public function get_all() {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }
    
    
    public function get_by_id($id) {
        // Suppress all PHP errors and warnings to prevent HTML output
        error_reporting(0);
        ini_set('display_errors', 0);

        // Clear any existing output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }
    
   
    public function insert($data) {
        try {
            // Get table structure to check if fields exist
            $fields = $this->db->list_fields($this->table);
            
            // Filter data to only include existing fields
            $filtered_data = array_intersect_key($data, array_flip($fields));
            
            // Add timestamps
            $filtered_data['created_at'] = date('Y-m-d H:i:s');
            $filtered_data['updated_at'] = date('Y-m-d H:i:s');
            
            $this->db->insert($this->table, $filtered_data);
            
            if ($this->db->affected_rows() > 0) {
                log_message('debug', 'Insert successful - Data: ' . json_encode($filtered_data));
                return $this->db->insert_id();
            } else {
                $error = $this->db->error();
                log_message('error', 'Insert failed - Last query: ' . $this->db->last_query());
                log_message('error', 'DB Error: ' . json_encode($error));
                log_message('error', 'DB Error Code: ' . $error['code']);
                log_message('error', 'DB Error Message: ' . $error['message']);
                log_message('error', 'Filtered data that failed: ' . json_encode($filtered_data));
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Exception in insert method: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function update($id, $data) {
        // Suppress all PHP errors and warnings to prevent HTML output
        error_reporting(0);
        ini_set('display_errors', 0);

        // Clear any existing output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            // Get table structure to check if fields exist
            $fields = $this->db->list_fields($this->table);
            
            // Filter data to only include existing fields
            $filtered_data = array_intersect_key($data, array_flip($fields));
            
            // Debug: Log field filtering
            log_message('debug', 'Transaksi_model update - Available fields: ' . json_encode($fields));
            log_message('debug', 'Transaksi_model update - Input data: ' . json_encode($data));
            log_message('debug', 'Transaksi_model update - Filtered data: ' . json_encode($filtered_data));
            log_message('debug', 'Transaksi_model update - Barcode in filtered data: ' . (isset($filtered_data['barcode']) ? $filtered_data['barcode'] : 'NOT FOUND'));
            
            // Add updated_at timestamp
            if (isset($filtered_data['status']) && in_array((string)$filtered_data['status'], ['1','2'], true)) {
                $filtered_data['updated_at'] = date('Y-m-d H:i:s');
            }
            
            
            $this->db->where('id', $id);
            $result = $this->db->update($this->table, $filtered_data);
            
            // Debug: Log the last query and any errors
            if (!$result) {
                $error = $this->db->error();
                log_message('error', 'Update failed for ID: ' . $id . ' - Last query: ' . $this->db->last_query());
                log_message('error', 'DB Error: ' . json_encode($error));
            } else {
                log_message('debug', 'Update successful for ID: ' . $id . ' - Data: ' . json_encode($filtered_data));
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Exception in update method: ' . $e->getMessage());
            throw $e;
        } catch (Error $e) {
            log_message('error', 'Error in update method: ' . $e->getMessage());
            throw $e;
        } catch (Throwable $e) {
            log_message('error', 'Throwable in update method: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
    
    public function count_all() {
        return $this->db->count_all($this->table);
    }

    public function count_all_filtered($flag_doc) {
        $this->db->from($this->table);
        $this->db->where('flag_doc', $flag_doc);
        return $this->db->count_all_results();
    }
    
    

    public function get_all_active() {
        $this->db->select('peserta.*');
        $this->db->from('peserta');
        $this->db->where('peserta.status', 0);
        $this->db->order_by('peserta.id', 'ASC');
        
        return $this->db->get()->result();
    }

    public function get_paginated($limit, $offset) {
        $this->db->select('peserta.*');
        $this->db->from($this->table);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_paginated_filtered($limit, $offset, $filters = []) {
        $this->db->select('peserta.*');
        $this->db->from($this->table);
    
        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('peserta.nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('peserta.no_visa', $filters['no_visa']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('peserta.status', $filters['status']);
        }
        if (!empty($filters['gender'])) {
            $this->db->where('peserta.gender', $filters['gender']);
        }
        if (!empty($filters['history_done'])) {
            $this->db->where('peserta.history_done', $filters['history_done']);
        }
        if (!empty($filters['nama_travel'])) {
            $this->db->where('peserta.nama_travel', $filters['nama_travel']);
        }
        if (isset($filters['flag_doc'])) {
            // Handle multiple flag_doc selection
            if (is_array($filters['flag_doc'])) {
                // Multiple flag_doc selected
                $flag_docs = [];
                $has_null = false;
                
                foreach ($filters['flag_doc'] as $flag_doc) {
                    if ($flag_doc === null || $flag_doc === 'null' || $flag_doc === 'NULL' || $flag_doc === '') {
                        $has_null = true;
                    } else {
                        $flag_docs[] = $flag_doc;
                    }
                }
                
                if (!empty($flag_docs) && $has_null) {
                    // Both specific flag_docs and null values
                    $this->db->group_start();
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                    $this->db->or_where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                    $this->db->group_end();
                } elseif (!empty($flag_docs)) {
                    // Only specific flag_docs
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                } elseif ($has_null) {
                    // Only null values
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                }
            } else {
                // Single flag_doc (backward compatibility)
                if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                } else {
                    $this->db->where('peserta.flag_doc', $filters['flag_doc']);
                }
            }
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
        if (!empty($filters['tanggal_pengerjaan'])) {
            // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
            $tanggal_pengerjaan = $filters['tanggal_pengerjaan'];
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
                // Convert from dd-mm-yyyy to yyyy-mm-dd
                $date_parts = explode('-', $tanggal_pengerjaan);
                $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
            $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        }
        
        // Filter berdasarkan tanggal updated_at (Sortir Mulai dan Sortir Akhir)
        if (!empty($filters['startDate'])) {
            $this->db->where('DATE(updated_at) >=', $filters['startDate']);
        }
        if (!empty($filters['endDate'])) {
            $this->db->where('DATE(updated_at) <=', $filters['endDate']);
        }
        
        if (!empty($filters['status_jadwal'])) {
            if ($filters['status_jadwal'] === '2') {
                $this->db->where('peserta.status', 2);
                $this->db->where('peserta.tanggal IS NOT NULL');
                $this->db->where('peserta.jam IS NOT NULL');
            } else {
                $this->db->where('peserta.status', 2);
                $this->db->where('peserta.tanggal IS NULL');
                $this->db->where('peserta.jam IS NULL');
            }
        }
        
        // Filter berdasarkan kondisi barcode
        if (isset($filters['has_barcode'])) {
            if ($filters['has_barcode'] === '1') {
                // Ada barcode
                $this->db->where('peserta.barcode IS NOT NULL');
                $this->db->where('peserta.barcode !=', '');
            } elseif ($filters['has_barcode'] === '0') {
                // Tidak ada barcode
                $this->db->group_start();
                $this->db->where('peserta.barcode IS NULL');
                $this->db->or_where('peserta.barcode =', '');
                $this->db->group_end();
            }
        }
    
        // Urut berdasarkan abjad nama
        $this->db->order_by('peserta.flag_doc', 'DESC');
        $this->db->order_by('peserta.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_paginated_filtered_todo($limit, $offset, $filters = []) {
        $this->db->select('peserta.*');
        $this->db->from($this->table);

        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nama_travel'])) {
            $this->db->where('peserta.nama_travel', $filters['nama_travel']);
        }

        if (isset($filters['flag_doc'])) {
            // Handle flag_doc filter more precisely
            if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
            } else {
                $this->db->where('peserta.flag_doc', $filters['flag_doc']);
            }
        }

        if (!empty($filters['nama_travel'])) {
            $this->db->where('peserta.nama_travel', $filters['nama_travel']);
        }

        $this->db->where('peserta.status', 0);
        $this->db->order_by('peserta.flag_doc', 'DESC');
        $this->db->order_by('peserta.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }


    public function count_filtered($filters = []) {
        $this->db->from($this->table);
        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('peserta.nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('peserta.no_visa', $filters['no_visa']);
        }
        if (!empty($filters['gender'])) {
            $this->db->where('peserta.gender', $filters['gender']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('peserta.status', $filters['status']);
        }
        if (!empty($filters['history_done'])) {
            $this->db->where('peserta.history_done', $filters['history_done']);
        }
        if (!empty($filters['nama_travel'])) {
            $this->db->where('peserta.nama_travel', $filters['nama_travel']);
        }
        if (isset($filters['flag_doc'])) {
            // Handle multiple flag_doc selection
            if (is_array($filters['flag_doc'])) {
                // Multiple flag_doc selected
                $flag_docs = [];
                $has_null = false;
                
                foreach ($filters['flag_doc'] as $flag_doc) {
                    if ($flag_doc === null || $flag_doc === 'null' || $flag_doc === 'NULL' || $flag_doc === '') {
                        $has_null = true;
                    } else {
                        $flag_docs[] = $flag_doc;
                    }
                }
                
                if (!empty($flag_docs) && $has_null) {
                    // Both specific flag_docs and null values
                    $this->db->group_start();
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                    $this->db->or_where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                    $this->db->group_end();
                } elseif (!empty($flag_docs)) {
                    // Only specific flag_docs
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                } elseif ($has_null) {
                    // Only null values
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                }
            } else {
                // Single flag_doc (backward compatibility)
                if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                } else {
                    $this->db->where('peserta.flag_doc', $filters['flag_doc']);
                }
            }
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
        if (!empty($filters['tanggal_pengerjaan'])) {
            // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
            $tanggal_pengerjaan = $filters['tanggal_pengerjaan'];
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
                // Convert from dd-mm-yyyy to yyyy-mm-dd
                $date_parts = explode('-', $tanggal_pengerjaan);
                $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
            $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        }
        
        // Filter berdasarkan tanggal updated_at (Sortir Mulai dan Sortir Akhir)
        if (!empty($filters['startDate'])) {
            $this->db->where('DATE(updated_at) >=', $filters['startDate']);
        }
        if (!empty($filters['endDate'])) {
            $this->db->where('DATE(updated_at) <=', $filters['endDate']);
        }
        
        if (!empty($filters['status_jadwal'])) {
            if ($filters['status_jadwal'] === '2') {
                $this->db->where('peserta.status', 2);
                $this->db->where('peserta.tanggal IS NOT NULL');
                $this->db->where('peserta.jam IS NOT NULL');
            } else {
                $this->db->where('peserta.status', 2);
                $this->db->where('peserta.tanggal IS NULL');
                $this->db->where('peserta.jam IS NULL');
            }
        }
        
        // Filter berdasarkan kondisi barcode
        if (isset($filters['has_barcode'])) {
            if ($filters['has_barcode'] === '1') {
                // Ada barcode
                $this->db->where('peserta.barcode IS NOT NULL');
                $this->db->where('peserta.barcode !=', '');
            } elseif ($filters['has_barcode'] === '0') {
                // Tidak ada barcode
                $this->db->group_start();
                $this->db->where('peserta.barcode IS NULL');
                $this->db->or_where('peserta.barcode =', '');
                $this->db->group_end();
            }
        }
        
        return $this->db->count_all_results();
    }

    public function count_filtered_todo($filters = []) {
        $this->db->from($this->table);
        $this->db->where('peserta.status', 0);
        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('peserta.nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('peserta.no_visa', $filters['no_visa']);
        }
        if (isset($filters['flag_doc'])) {
            // Handle flag_doc filter more precisely
            if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
            } else {
                $this->db->where('peserta.flag_doc', $filters['flag_doc']);
            }
        }
        if (!empty($filters['nama_travel'])) {
            $this->db->where('peserta.nama_travel', $filters['nama_travel']);
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
        
        // Always filter by status = 0 for todo
        $this->db->where('peserta.status', 0);
        
            return $this->db->count_all_results();
        }

    public function get_by_passport($nomor_paspor) {
        $this->db->where('nomor_paspor', $nomor_paspor);
        return $this->db->get($this->table)->row();
    }
    
    public function get_unique_flag_doc() {
        $this->db->select('flag_doc, MAX(created_at) as created_at');
        $this->db->from($this->table);
        $this->db->where('flag_doc IS NOT NULL');
        $this->db->where('flag_doc !=', '');
        $this->db->group_by('flag_doc');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result();
    }
    
    public function get_unique_nama_travel() {
        $this->db->select('nama_travel, MAX(created_at) as created_at');
        $this->db->from($this->table);
        $this->db->where('nama_travel IS NOT NULL');
        $this->db->where('nama_travel !=', '');
        $this->db->group_by('nama_travel');
        $this->db->order_by('created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function get_unique_flag_doc_Export() {
        $this->db->select('flag_doc, MAX(created_at) as created_at');
        $this->db->from($this->table);
        $this->db->where('flag_doc IS NOT NULL');
        $this->db->where('flag_doc !=', '');
        $this->db->group_by('flag_doc');
        // pastikan hanya grup yang tidak ada status=0
        $this->db->having('SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) =', 0);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result();
    }
    
    public function get_unique_flag_doc_todo() {
        $this->db->select('flag_doc, MAX(created_at) as created_at');
        $this->db->from($this->table);
        $this->db->where('flag_doc IS NOT NULL');
        $this->db->where('flag_doc !=', '');
        $this->db->where('status', 0);
        $this->db->group_by('flag_doc');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result();
    }
    
    public function get_unique_tanggaljam() {
        $this->db->select("nama, CONCAT(tanggal, ' ', jam) AS tanggaljam");
        $this->db->from($this->table);
        $this->db->where("tanggal IS NOT NULL");
        $this->db->where("jam IS NOT NULL");
        $this->db->where("tanggal != ''");
        $this->db->where("jam != ''");
        $this->db->group_by("tanggaljam");
        $this->db->order_by('tanggaljam', 'ASC');
        return $this->db->get()->result();
    }

    public function get_unique_tanggal_pengerjaan() {
        $this->db->select("DATE(updated_at) as tanggal_pengerjaan, COUNT(*) as jumlah_update");
        $this->db->from($this->table);
        $this->db->where("updated_at IS NOT NULL");
        $this->db->group_by("DATE(updated_at)");
        $this->db->order_by("tanggal_pengerjaan", "ASC");
        return $this->db->get()->result();
    }
    
    
    
    public function get_dashboard_stats($flag_doc = null) {
        $this->db->select('COUNT(*) as total_done');
        $this->db->from($this->table);
        $this->db->where('status', 2); // Only Done status
        
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        
        $result = $this->db->get()->row();
        return $result ? $result->total_done : 0;
    }
    
    public function get_dashboard_stats_on_target($flag_doc = null) {
        $this->db->select('COUNT(*) as total_on_target');
        $this->db->from($this->table);
        $this->db->where('status', 0); // Only On Target status
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        $result = $this->db->get()->row();
        return $result ? $result->total_on_target : 0;
    }
    
    public function get_dashboard_stats_already($flag_doc = null) {
        $this->db->select('COUNT(*) as total_already');
        $this->db->from($this->table);
        $this->db->where('status', 1); // Only Already status
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        $result = $this->db->get()->row();
        return $result ? $result->total_already : 0;
    }
    
    /**
     * Get statistics for data updated on specific date
     */
    public function get_update_stats_by_date($tanggal_pengerjaan) {
        // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
            $date_parts = explode('-', $tanggal_pengerjaan);
            $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        }
        
        $this->db->select('COUNT(*) as total_updated');
        $this->db->from($this->table);
        $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        $result = $this->db->get()->row();
        return $result ? $result->total_updated : 0;
    }
    
    /**
     * Get detailed update statistics by date with status breakdown
     */
    public function get_update_stats_detail_by_date($tanggal_pengerjaan) {
        // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
            $date_parts = explode('-', $tanggal_pengerjaan);
            $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        }
        
        $this->db->select('status, COUNT(*) as count');
        $this->db->from($this->table);
        $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        $this->db->group_by('status');
        $this->db->order_by('status', 'ASC');
        return $this->db->get()->result();
    }

    // ==================== ARSIP METHODS ====================
    
    /**
     * Get paginated filtered data for arsip (status = 2)
     */
    public function get_paginated_filtered_arsip($limit, $offset, $filters = []) {
        $this->db->select('peserta.*');
        $this->db->from($this->table);
        $this->db->where('peserta.selesai', 2); // Only archived data (selesai=2)
    
        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('peserta.nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('peserta.no_visa', $filters['no_visa']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('peserta.status', $filters['status']);
        }
        if (!empty($filters['gender'])) {
            $this->db->where('peserta.gender', $filters['gender']);
        }
        if (!empty($filters['history_done'])) {
            $this->db->where('peserta.history_done', $filters['history_done']);
        }
        if (isset($filters['flag_doc'])) {
            // Handle multiple flag_doc selection
            if (is_array($filters['flag_doc'])) {
                // Multiple flag_doc selected
                $flag_docs = [];
                $has_null = false;
                
                foreach ($filters['flag_doc'] as $flag_doc) {
                    if ($flag_doc === null || $flag_doc === 'null' || $flag_doc === 'NULL' || $flag_doc === '') {
                        $has_null = true;
                    } else {
                        $flag_docs[] = $flag_doc;
                    }
                }
                
                if (!empty($flag_docs) && $has_null) {
                    // Both specific flag_docs and null values
                    $this->db->group_start();
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                    $this->db->or_where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                    $this->db->group_end();
                } elseif (!empty($flag_docs)) {
                    // Only specific flag_docs
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                } elseif ($has_null) {
                    // Only null values
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                }
            } else {
                // Single flag_doc (backward compatibility)
                if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                } else {
                    $this->db->where('peserta.flag_doc', $filters['flag_doc']);
                }
            }
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
        if (!empty($filters['tanggal_pengerjaan'])) {
            // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
            $tanggal_pengerjaan = $filters['tanggal_pengerjaan'];
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
                // Convert from dd-mm-yyyy to yyyy-mm-dd
                $date_parts = explode('-', $tanggal_pengerjaan);
                $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
            $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        }
        if (!empty($filters['tanggal_pengarsipan'])) {
            // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
            $tanggal_pengarsipan = $filters['tanggal_pengarsipan'];
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengarsipan)) {
                // Convert from dd-mm-yyyy to yyyy-mm-dd
                $date_parts = explode('-', $tanggal_pengarsipan);
                $tanggal_pengarsipan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
            $this->db->where('DATE(arsip_create_at)', $tanggal_pengarsipan);
        }
    
        // Debug: Log the query
        $this->db->order_by('peserta.flag_doc', 'DESC');
        $this->db->order_by('peserta.id', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        $result = $query->result();
        
        // Debug: Log the query and result count
        log_message('debug', 'get_paginated_filtered_arsip - Query: ' . $this->db->last_query());
        log_message('debug', 'get_paginated_filtered_arsip - Result count: ' . count($result));
        
        return $result;
    }

    /**
     * Count filtered data for arsip
     */
    public function count_filtered_arsip($filters = []) {
        $this->db->from($this->table);
        $this->db->where('peserta.selesai', 2); // Only archived data (selesai=2)
        
        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('peserta.nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('peserta.no_visa', $filters['no_visa']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('peserta.status', $filters['status']);
        }
        if (isset($filters['flag_doc'])) {
            // Handle multiple flag_doc selection
            if (is_array($filters['flag_doc'])) {
                // Multiple flag_doc selected
                $flag_docs = [];
                $has_null = false;
                
                foreach ($filters['flag_doc'] as $flag_doc) {
                    if ($flag_doc === null || $flag_doc === 'null' || $flag_doc === 'NULL' || $flag_doc === '') {
                        $has_null = true;
                    } else {
                        $flag_docs[] = $flag_doc;
                    }
                }
                
                if (!empty($flag_docs) && $has_null) {
                    // Both specific flag_docs and null values
                    $this->db->group_start();
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                    $this->db->or_where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                    $this->db->group_end();
                } elseif (!empty($flag_docs)) {
                    // Only specific flag_docs
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                } elseif ($has_null) {
                    // Only null values
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                }
            } else {
                // Single flag_doc (backward compatibility)
                if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                } else {
                    $this->db->where('peserta.flag_doc', $filters['flag_doc']);
                }
            }
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
        if (!empty($filters['tanggal_pengerjaan'])) {
            // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
            $tanggal_pengerjaan = $filters['tanggal_pengerjaan'];
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
                // Convert from dd-mm-yyyy to yyyy-mm-dd
                $date_parts = explode('-', $tanggal_pengerjaan);
                $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
            $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        }
        if (!empty($filters['tanggal_pengarsipan'])) {
            // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
            $tanggal_pengarsipan = $filters['tanggal_pengarsipan'];
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengarsipan)) {
                // Convert from dd-mm-yyyy to yyyy-mm-dd
                $date_parts = explode('-', $tanggal_pengarsipan);
                $tanggal_pengarsipan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
            $this->db->where('DATE(arsip_create_at)', $tanggal_pengarsipan);
        }
        
        $count = $this->db->count_all_results();
        
        // Debug: Log the count
        log_message('debug', 'count_filtered_arsip - Count: ' . $count);
        
        return $count;
    }

    /**
     * Get unique flag_doc for arsip
     */
    public function get_unique_flag_doc_arsip() {
        $this->db->select('flag_doc, MAX(created_at) as created_at');
        $this->db->from($this->table);
        $this->db->where('flag_doc IS NOT NULL');
        $this->db->where('flag_doc !=', '');
        $this->db->where('selesai', 2); // Only archived data (selesai=2)
        $this->db->group_by('flag_doc');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result();
    }
    
    /**
     * Get unique tanggaljam for arsip
     */
    public function get_unique_tanggaljam_arsip() {
        $this->db->select("nama, CONCAT(tanggal, ' ', jam) AS tanggaljam");
        $this->db->from($this->table);
        $this->db->where("tanggal IS NOT NULL");
        $this->db->where("jam IS NOT NULL");
        $this->db->where("tanggal != ''");
        $this->db->where("jam != ''");
        $this->db->where('selesai', 2); // Only archived data
        $this->db->group_by("tanggaljam");
        $this->db->order_by('tanggaljam', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get unique tanggal pengerjaan for arsip
     */
    public function get_unique_tanggal_pengerjaan_arsip() {
        $this->db->select("DATE(updated_at) as tanggal_pengerjaan, COUNT(*) as jumlah_update");
        $this->db->from($this->table);
        $this->db->where("updated_at IS NOT NULL");
        $this->db->where('selesai', 2); // Only archived data
        $this->db->group_by("DATE(updated_at)");
        $this->db->order_by("tanggal_pengerjaan", "DESC");
        return $this->db->get()->result();
    }
    
    /**
     * Get unique tanggal pengarsipan for arsip
     */
    public function get_unique_tanggal_pengarsipan_arsip() {
        $this->db->select("DATE(arsip_create_at) as tanggal_pengarsipan, COUNT(*) as jumlah_arsip");
        $this->db->from($this->table);
        $this->db->where("arsip_create_at IS NOT NULL");
        $this->db->where('selesai', 2); // Only archived data
        $this->db->group_by("DATE(arsip_create_at)");
        $this->db->order_by("tanggal_pengarsipan", "DESC");
        return $this->db->get()->result();
    }

    /**
     * Get update statistics by date for arsip
     */
    public function get_update_stats_by_date_arsip($tanggal_pengerjaan) {
        // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
            $date_parts = explode('-', $tanggal_pengerjaan);
            $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        }
        
        $this->db->select('COUNT(*) as total_updated');
        $this->db->from($this->table);
        $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        $this->db->where('selesai', 2); // Only archived data
        $result = $this->db->get()->row();
        return $result ? $result->total_updated : 0;
    }
    
    /**
     * Get detailed update statistics by date with status breakdown for arsip
     */
    public function get_update_stats_detail_by_date_arsip($tanggal_pengerjaan) {
        // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
            $date_parts = explode('-', $tanggal_pengerjaan);
            $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        }
        
        $this->db->select('selesai, COUNT(*) as count');
        $this->db->from($this->table);
        $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        $this->db->where('selesai', 2); // Only archived data (selesai=2)
        $this->db->group_by('selesai');
        $this->db->order_by('selesai', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Get archive statistics by date for pengarsipan
     */
    public function get_arsip_stats_by_date($tanggal_pengarsipan) {
        // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengarsipan)) {
            $date_parts = explode('-', $tanggal_pengarsipan);
            $tanggal_pengarsipan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        }
        
        $this->db->select('COUNT(*) as total_archived');
        $this->db->from($this->table);
        $this->db->where('DATE(arsip_create_at)', $tanggal_pengarsipan);
        $this->db->where('selesai', 2); // Only archived data
        $result = $this->db->get()->row();
        return $result ? $result->total_archived : 0;
    }
    
    /**
     * Get detailed archive statistics by date with status breakdown
     */
    public function get_arsip_stats_detail_by_date($tanggal_pengarsipan) {
        // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengarsipan)) {
            $date_parts = explode('-', $tanggal_pengarsipan);
            $tanggal_pengarsipan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        }
        
        $this->db->select('status, COUNT(*) as count');
        $this->db->from($this->table);
        $this->db->where('DATE(arsip_create_at)', $tanggal_pengarsipan);
        $this->db->where('selesai', 2); // Only archived data (selesai=2)
        $this->db->group_by('status');
        $this->db->order_by('status', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get all archived data for export (selesai=2)
     */
    public function get_all_archived_data_for_export($filters = []) {
        $this->db->select('peserta.*');
        $this->db->from($this->table);
        $this->db->where('peserta.selesai', 2); // Strict filter for archived data
        
        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('peserta.nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('peserta.no_visa', $filters['no_visa']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('peserta.status', $filters['status']);
        }
        if (isset($filters['flag_doc'])) {
            // Handle multiple flag_doc selection
            if (is_array($filters['flag_doc'])) {
                // Multiple flag_doc selected
                $flag_docs = [];
                $has_null = false;
                
                foreach ($filters['flag_doc'] as $flag_doc) {
                    if ($flag_doc === null || $flag_doc === 'null' || $flag_doc === 'NULL' || $flag_doc === '') {
                        $has_null = true;
                    } else {
                        $flag_docs[] = $flag_doc;
                    }
                }
                
                if (!empty($flag_docs) && $has_null) {
                    // Both specific flag_docs and null values
                    $this->db->group_start();
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                    $this->db->or_where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                    $this->db->group_end();
                } elseif (!empty($flag_docs)) {
                    // Only specific flag_docs
                    $this->db->where_in('peserta.flag_doc', $flag_docs);
                } elseif ($has_null) {
                    // Only null values
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                }
            } else {
                // Single flag_doc (backward compatibility)
                if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                    $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                } else {
                    $this->db->where('peserta.flag_doc', $filters['flag_doc']);
                }
            }
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
        if (!empty($filters['tanggal_pengerjaan'])) {
            // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
            $tanggal_pengerjaan = $filters['tanggal_pengerjaan'];
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengerjaan)) {
                // Convert from dd-mm-yyyy to yyyy-mm-dd
                $date_parts = explode('-', $tanggal_pengerjaan);
                $tanggal_pengerjaan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
            $this->db->where('DATE(updated_at)', $tanggal_pengerjaan);
        }
        if (!empty($filters['tanggal_pengarsipan'])) {
            // Convert dd-mm-yyyy format to yyyy-mm-dd for database comparison
            $tanggal_pengarsipan = $filters['tanggal_pengarsipan'];
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $tanggal_pengarsipan)) {
                // Convert from dd-mm-yyyy to yyyy-mm-dd
                $date_parts = explode('-', $tanggal_pengarsipan);
                $tanggal_pengarsipan = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
            }
            $this->db->where('DATE(arsip_create_at)', $tanggal_pengarsipan);
        }
        
        $this->db->order_by('peserta.flag_doc', 'DESC');
        $this->db->order_by('peserta.id', 'DESC');
        
        $query = $this->db->get();
        $result = $query->result();
        
        // Debug: Log the query and result count
        log_message('debug', 'get_all_archived_data_for_export - Query: ' . $this->db->last_query());
        log_message('debug', 'get_all_archived_data_for_export - Result count: ' . count($result));
        
        return $result;
    }
    
    public function get_gender_stats($flag_doc = null) {
        $this->db->select('gender, COUNT(*) as count');
        $this->db->from($this->table);
        $this->db->where('status', 2); // Only Done status
        
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        
        $this->db->group_by('gender');
        $this->db->order_by('gender', 'ASC');
        return $this->db->get()->result();
    }
    
    public function get_hour_stats($flag_doc = null) {
        $this->db->select('jam,tanggal, COUNT(*) as count');
        $this->db->from($this->table);
        $this->db->where('status', 2); // Only Done status
        $this->db->where('jam IS NOT NULL');
        $this->db->where('jam !=', '');
        
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        
        $this->db->group_by('jam');
        $this->db->order_by('jam', 'ASC');
        return $this->db->get()->result();
    }

    public function get_hour_gender_stats($flag_doc = null) {
        $this->db->select("jam,
            SUM(CASE WHEN gender = 'L' THEN 1 ELSE 0 END) AS male_count,
            SUM(CASE WHEN gender = 'P' THEN 1 ELSE 0 END) AS female_count,
            COUNT(*) AS total_count");
        $this->db->from($this->table);
        $this->db->where('status', 2); // Only Done status
        $this->db->where('jam IS NOT NULL');
        $this->db->where('jam !=', '');
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        $this->db->group_by('jam');
        $this->db->order_by('jam', 'ASC');
        return $this->db->get()->result();
    }

    public function get_schedule_by_date($flag_doc = null) {
        $this->db->select("tanggal,
            SUM(CASE WHEN gender = 'L' THEN 1 ELSE 0 END) AS total_male,
            SUM(CASE WHEN gender = 'P' THEN 1 ELSE 0 END) AS total_female,
            COUNT(*) AS total_count");
        $this->db->from($this->table);
        $this->db->where('status', 2); // Only Done status
        $this->db->where('tanggal IS NOT NULL');
        $this->db->where('tanggal !=', '');
        $this->db->where('jam IS NOT NULL');
        $this->db->where('jam !=', '');
        $this->db->where('selesai !=', 2);
        
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        
        $this->db->group_by('tanggal');
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result();
    }

    public function get_schedule_detail_by_date($tanggal, $flag_doc = null) {
        $this->db->select("jam,
            SUM(CASE WHEN gender = 'L' AND (barcode IS NOT NULL AND barcode != '') THEN 1 ELSE 0 END) AS male_with_barcode,
            SUM(CASE WHEN gender = 'L' AND (barcode IS NULL OR barcode = '') THEN 1 ELSE 0 END) AS male_no_barcode,
            SUM(CASE WHEN gender = 'P' AND (barcode IS NOT NULL AND barcode != '') THEN 1 ELSE 0 END) AS female_with_barcode,
            SUM(CASE WHEN gender = 'P' AND (barcode IS NULL OR barcode = '') THEN 1 ELSE 0 END) AS female_no_barcode,
            SUM(CASE WHEN gender = 'L' THEN 1 ELSE 0 END) AS male_count,
            SUM(CASE WHEN gender = 'P' THEN 1 ELSE 0 END) AS female_count,
            COUNT(*) AS total_count");
        $this->db->from($this->table);
        $this->db->where('status', 2); // Only Done status
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam IS NOT NULL');
        $this->db->where('jam !=', '');
        $this->db->where('selesai !=', 2);
        
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        
        $this->db->group_by('jam');
        $this->db->order_by('jam', 'ASC');
        return $this->db->get()->result();
    }

    public function update_status_massal($tanggal, $jam, $flag_doc = null) {
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam', $jam);
        $this->db->where('selesai !=', 2); // Update hanya yang belum status 2
        
        if ($flag_doc) {
            $this->db->where('flag_doc', $flag_doc);
        }
        
        $data = [
            'selesai' => 2,
            'arsip_create_at' => date('Y-m-d H:i:s'),
            'eksekutor_arsip_create_at' => $this->session->userdata('user_id') ?: null
        ];
        
        $result = $this->db->update($this->table, $data);
        
        // Log untuk debugging
        log_message('debug', 'Update massal - Tanggal: ' . $tanggal . ', Jam: ' . $jam . ', Flag Doc: ' . $flag_doc . ', Affected Rows: ' . $this->db->affected_rows());
        
        return $result;
    }

    /**
     * Get statistics by flag_doc for export
     * Logic: Only export flags that have NO status 0 (On Target) data
     * Only include status 1 (Already) and 2 (Done) data
     */
    public function get_statistik_by_flag_doc($filters = []) {
        try {
            log_message('debug', 'get_statistik_by_flag_doc - Input filters: ' . json_encode($filters));
            
            // Build exclusion query using raw SQL - only exclude flags that have status 0 data
            $exclusion_conditions = ['status = 0'];
            $exclusion_params = [];
            
            // Apply filters for the exclusion query - but only if they don't conflict with flag_doc filter
        if (!empty($filters['nama'])) {
                $exclusion_conditions[] = 'nama LIKE ?';
                $exclusion_params[] = '%' . $filters['nama'] . '%';
        }
        if (!empty($filters['nomor_paspor'])) {
                $exclusion_conditions[] = 'nomor_paspor LIKE ?';
                $exclusion_params[] = '%' . $filters['nomor_paspor'] . '%';
        }
        if (!empty($filters['no_visa'])) {
                $exclusion_conditions[] = 'no_visa LIKE ?';
                $exclusion_params[] = '%' . $filters['no_visa'] . '%';
            }
            if (!empty($filters['nama_travel'])) {
                $exclusion_conditions[] = 'nama_travel = ?';
                $exclusion_params[] = $filters['nama_travel'];
            }
            
            // Don't apply flag_doc filter to exclusion query when we're filtering by specific flag_docs
            // This prevents excluding the very flags we want to include
            
            $exclusion_query = "SELECT DISTINCT flag_doc FROM peserta WHERE " . implode(' AND ', $exclusion_conditions);
            log_message('debug', 'Exclusion query: ' . $exclusion_query);
            log_message('debug', 'Exclusion params: ' . json_encode($exclusion_params));
            
            $excluded_flags = $this->db->query($exclusion_query, $exclusion_params)->result();
            
            log_message('debug', 'Exclusion query - Result count: ' . count($excluded_flags));
            log_message('debug', 'Excluded flags: ' . json_encode($excluded_flags));
        
        // Get flag_doc values to exclude
        $excluded_flag_docs = [];
        foreach ($excluded_flags as $flag) {
            $excluded_flag_docs[] = $flag->flag_doc;
        }
        
            log_message('debug', 'Excluded flag_docs array: ' . json_encode($excluded_flag_docs));
            
            // Build main query using raw SQL
            $main_conditions = ['status IN (1, 2)'];
            $main_params = [];
            
            // Exclude flags that have status 0 data, but only if we're not filtering by specific flag_docs
            if (!empty($excluded_flag_docs) && !isset($filters['flag_doc'])) {
                $placeholders = str_repeat('?,', count($excluded_flag_docs) - 1) . '?';
                $main_conditions[] = "flag_doc NOT IN ($placeholders)";
                $main_params = array_merge($main_params, $excluded_flag_docs);
            }
            
            // Apply filters for main query
        if (!empty($filters['nama'])) {
                $main_conditions[] = 'nama LIKE ?';
                $main_params[] = '%' . $filters['nama'] . '%';
        }
        if (!empty($filters['nomor_paspor'])) {
                $main_conditions[] = 'nomor_paspor LIKE ?';
                $main_params[] = '%' . $filters['nomor_paspor'] . '%';
        }
        if (!empty($filters['no_visa'])) {
                $main_conditions[] = 'no_visa LIKE ?';
                $main_params[] = '%' . $filters['no_visa'] . '%';
            }
            if (!empty($filters['nama_travel'])) {
                $main_conditions[] = 'nama_travel = ?';
                $main_params[] = $filters['nama_travel'];
        }
        if (isset($filters['flag_doc'])) {
            if (is_array($filters['flag_doc'])) {
                $flag_docs = [];
                $has_null = false;
                $has_empty = false;
                
                foreach ($filters['flag_doc'] as $flag_doc) {
                    if ($flag_doc === null || $flag_doc === 'null' || $flag_doc === 'NULL') {
                        $has_null = true;
                    } elseif ($flag_doc === '') {
                        $has_empty = true;
                    } else {
                        $flag_docs[] = $flag_doc;
                    }
                }
                
                    if (!empty($flag_docs)) {
                        $placeholders = str_repeat('?,', count($flag_docs) - 1) . '?';
                        $main_conditions[] = "flag_doc IN ($placeholders)";
                        $main_params = array_merge($main_params, $flag_docs);
                    }
                    
                    if ($has_null || $has_empty) {
                        $main_conditions[] = '(flag_doc IS NULL OR flag_doc = "")';
                }
            } else {
                if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                        $main_conditions[] = '(flag_doc IS NULL OR flag_doc = "")';
                } elseif ($filters['flag_doc'] === '') {
                        $main_conditions[] = 'flag_doc = ?';
                        $main_params[] = '';
                } else {
                        $main_conditions[] = 'flag_doc = ?';
                        $main_params[] = $filters['flag_doc'];
                    }
                }
            }
            
            $main_query = "SELECT 
                flag_doc,
                COUNT(*) as total,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as done,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as already
            FROM peserta 
            WHERE " . implode(' AND ', $main_conditions) . "
            GROUP BY flag_doc 
            ORDER BY flag_doc ASC";
            
            log_message('debug', 'Main query: ' . $main_query);
            log_message('debug', 'Main params: ' . json_encode($main_params));
            
            $result = $this->db->query($main_query, $main_params)->result();
            log_message('debug', 'get_statistik_by_flag_doc - Result count: ' . count($result));
            log_message('debug', 'Main query - Result data: ' . json_encode($result));
            
            return $result;
        
        } catch (Exception $e) {
            log_message('error', 'Error in get_statistik_by_flag_doc: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Get all data for export (for Telegram bot)
     */
    public function get_all_for_export() {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by('nama', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get operator statistics for performance report
     * Shows Done and Already status data for each operator
     */
    public function get_operator_statistics($filters = []) {
        $this->db->select('
            u.id_user,
            u.nama_lengkap,
            u.username,
            COUNT(CASE WHEN p.status = 2 THEN 1 END) as done_count,
            COUNT(CASE WHEN p.status = 1 THEN 1 END) as already_count,
            COUNT(CASE WHEN p.status IN (1, 2) THEN 1 END) as total_processed,
            MAX(p.updated_at) as last_activity
        ');
        $this->db->from('users u');
        $this->db->join('peserta p', 'u.id_user = p.history_done', 'left');
        //$this->db->where('u.role', 'operator');
        $this->db->where('u.username !=', 'adhit');
        $this->db->where('u.username !=', 'mimin');
        $this->db->where('u.status', 1); // Active users only
        
        // Apply date range filters if provided
        if (!empty($filters['start_date'])) {
            $this->db->where('DATE(p.updated_at) >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('DATE(p.updated_at) <=', $filters['end_date']);
        }
        
        $this->db->group_by('u.id_user, u.nama_lengkap, u.username');
        $this->db->order_by('total_processed', 'DESC');
        $this->db->order_by('u.nama_lengkap', 'ASC');
        
        $result = $this->db->get()->result();
        
        // Add additional statistics for each operator
        foreach ($result as $operator) {
            // Get today's statistics
            $this->db->select('
                COUNT(CASE WHEN p.status = 2 THEN 1 END) as today_done,
                COUNT(CASE WHEN p.status = 1 THEN 1 END) as today_already
            ');
            $this->db->from('peserta p');
            $this->db->where('p.history_done', $operator->id_user);
            $this->db->where('DATE(p.updated_at)', date('Y-m-d'));
            $today_stats = $this->db->get()->row();
            
            $operator->today_done = $today_stats ? $today_stats->today_done : 0;
            $operator->today_already = $today_stats ? $today_stats->today_already : 0;
            $operator->today_total = $operator->today_done + $operator->today_already;
            
            // Get this week's statistics
            $this->db->select('
                COUNT(CASE WHEN p.status = 2 THEN 1 END) as week_done,
                COUNT(CASE WHEN p.status = 1 THEN 1 END) as week_already
            ');
            $this->db->from('peserta p');
            $this->db->where('p.history_done', $operator->id_user);
            $this->db->where('p.updated_at >=', date('Y-m-d', strtotime('monday this week')));
            $this->db->where('p.updated_at <=', date('Y-m-d 23:59:59', strtotime('sunday this week')));
            $week_stats = $this->db->get()->row();
            
            $operator->week_done = $week_stats ? $week_stats->week_done : 0;
            $operator->week_already = $week_stats ? $week_stats->week_already : 0;
            $operator->week_total = $operator->week_done + $operator->week_already;
            
            // Get this month's statistics
            $this->db->select('
                COUNT(CASE WHEN p.status = 2 THEN 1 END) as month_done,
                COUNT(CASE WHEN p.status = 1 THEN 1 END) as month_already
            ');
            $this->db->from('peserta p');
            $this->db->where('p.history_done', $operator->id_user);
            $this->db->where('MONTH(p.updated_at)', date('m'));
            $this->db->where('YEAR(p.updated_at)', date('Y'));
            $month_stats = $this->db->get()->row();
            
            $operator->month_done = $month_stats ? $month_stats->month_done : 0;
            $operator->month_already = $month_stats ? $month_stats->month_already : 0;
            $operator->month_total = $operator->month_done + $operator->month_already;
        }
        
        return $result;
    }
    
    /**
     * Get monthly visa import statistics for the last 12 months
     */
    public function get_monthly_visa_import_stats() {
        $this->db->select('
            DATE_FORMAT(created_at, "%Y-%m") as month_year,
            COUNT(*) as total_imported,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as on_target,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as already,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as done
        ');
        $this->db->from($this->table);
        $this->db->where('created_at >=', date('Y-m-01', strtotime('-11 months')));
        $this->db->group_by('DATE_FORMAT(created_at, "%Y-%m")');
        $this->db->order_by('month_year', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get monthly visa import statistics by travel for the last 12 months
     */
    public function get_monthly_visa_import_by_travel($nama_travel = null) {
        if (!empty($nama_travel)) {
            // Filter by specific travel
            $this->db->select('
                DATE_FORMAT(created_at, "%Y-%m") as month_year,
                nama_travel,
                COUNT(*) as total_imported,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as on_target,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as already,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as done
            ');
            $this->db->from($this->table);
            $this->db->where('created_at >=', date('Y-m-01', strtotime('-11 months')));
            $this->db->where('nama_travel', $nama_travel);
            $this->db->group_by('DATE_FORMAT(created_at, "%Y-%m"), nama_travel');
            $this->db->order_by('month_year', 'ASC');
        } else {
            // Show total for all travels combined
            $this->db->select('
                DATE_FORMAT(created_at, "%Y-%m") as month_year,
                "All Travels" as nama_travel,
                COUNT(*) as total_imported,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as on_target,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as already,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as done
            ');
            $this->db->from($this->table);
            $this->db->where('created_at >=', date('Y-m-01', strtotime('-11 months')));
            $this->db->group_by('DATE_FORMAT(created_at, "%Y-%m")');
            $this->db->order_by('month_year', 'ASC');
        }
        
        return $this->db->get()->result();
    }

    /**
     * Get schedule data for API
     * @param string $tanggal Date in YYYY-MM-DD format
     * @return array
     */
    public function get_schedule_for_api($tanggal) {
        $this->db->select('tanggal, jam, COUNT(*) as total_peserta, flag_doc');
        $this->db->from($this->table);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('tanggal IS NOT NULL');
        $this->db->where('tanggal !=', '');
        $this->db->where('status', 2);
        $this->db->where('jam IS NOT NULL');
        $this->db->where('jam !=', '');
        $this->db->group_by('tanggal, jam, flag_doc');
        $this->db->order_by('jam', 'ASC');
        
        $result = $this->db->get();
        return $result->result_array();
    }

    /**
     * Get pending barcode data for specific schedule
     * @param string $tanggal Date in YYYY-MM-DD format
     * @param string $jam Time in HH:MM:SS format
     * @return array
     */
    public function get_pending_barcode_for_api($tanggal, $jam) {
        // Normalize jam format - handle both HH:MM and HH:MM:SS
        if (strlen($jam) == 5) {
            $jam = $jam . ':00'; // Add seconds if missing
        }
        
        $this->db->select('id, nama, tanggal, jam, nomor_paspor, flag_doc, barcode, gender, no_visa, nama_travel, status');
        $this->db->from($this->table);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam', $jam);
        $this->db->where('tanggal IS NOT NULL');
        $this->db->where('tanggal !=', '');
        $this->db->where('jam IS NOT NULL');
        $this->db->where('jam !=', '');
        $this->db->order_by('nama', 'ASC');
        
        $result = $this->db->get();
        return $result->result_array();
    }

    /**
     * Get all pending barcode data for a specific date
     * @param string $tanggal Date in YYYY-MM-DD format
     * @return array
     */
    public function get_pending_barcode_all_for_api($tanggal) {
        $this->db->select('id, nama, tanggal, jam, nomor_paspor, flag_doc');
        $this->db->from($this->table);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('status', '0'); // Hanya yang belum selesai
        $this->db->where('(barcode IS NULL OR barcode = "")'); // Belum upload barcode
        $this->db->order_by('jam ASC, nama ASC');
        
        $result = $this->db->get();
        return $result->result_array();
    }

    /**
     * Get overdue schedules
     * @return array
     */
    public function get_overdue_schedules_for_api() {
        $current_datetime = date('Y-m-d H:i:s');
        
        $this->db->select('tanggal, jam, COUNT(*) as count, 
                          TIMESTAMPDIFF(HOUR, CONCAT(tanggal, " ", jam), NOW()) as overdue_hours');
        $this->db->from($this->table);
        $this->db->where('CONCAT(tanggal, " ", jam) <', $current_datetime);
        $this->db->where('status', '0'); // Hanya yang belum selesai
        $this->db->where('(barcode IS NULL OR barcode = "")'); // Belum upload barcode
        $this->db->group_by('tanggal, jam');
        $this->db->order_by('tanggal DESC, jam DESC');
        $this->db->limit(50); // Limit untuk performa
        
        $result = $this->db->get();
        return $result->result_array();
    }

    /**
     * Get schedule data with pending barcode count
     * @param string $tanggal Date in YYYY-MM-DD format
     * @return array
     */
    public function get_schedule_with_pending_count_for_api($tanggal) {
        $this->db->select('tanggal, jam, 
                          COUNT(*) as total_peserta,
                          SUM(CASE WHEN (barcode IS NULL OR barcode = "") THEN 1 ELSE 0 END) as pending_barcode,
                          flag_doc');
        $this->db->from($this->table);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('tanggal IS NOT NULL');
        $this->db->where('tanggal !=', '');
        $this->db->where('jam IS NOT NULL');
        $this->db->where('jam !=', '');
        $this->db->group_by('tanggal, jam, flag_doc');
        $this->db->order_by('jam', 'ASC');
        
        $result = $this->db->get();
        return $result->result_array();
    }

    /**
     * Get all data for specific date and time (for debugging)
     * @param string $tanggal Date in YYYY-MM-DD format
     * @param string $jam Time in HH:MM:SS format
     * @return array
     */
    public function get_all_data_for_debug($tanggal, $jam) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam', $jam);
        $this->db->order_by('id', 'ASC');
        
        $result = $this->db->get();
        return $result->result_array();
    }

    /**
     * Get data with flexible time matching (for API)
     * @param string $tanggal Date in YYYY-MM-DD format
     * @param string $jam Time in HH:MM or HH:MM:SS format
     * @return array
     */
    public function get_data_flexible_time($tanggal, $jam) {
        // Try exact match first
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('tanggal', $tanggal);
        $this->db->where('jam', $jam);
        $result = $this->db->get();
        
        if ($result->num_rows() > 0) {
            return $result->result_array();
        }
        
        // Try with seconds added
        if (strlen($jam) == 5) {
            $jam_with_seconds = $jam . ':00';
            $this->db->select('*');
            $this->db->from($this->table);
            $this->db->where('tanggal', $tanggal);
            $this->db->where('jam', $jam_with_seconds);
            $result = $this->db->get();
            
            if ($result->num_rows() > 0) {
                return $result->result_array();
            }
        }
        
        // Try with seconds removed
        if (strlen($jam) == 8) {
            $jam_without_seconds = substr($jam, 0, 5);
            $this->db->select('*');
            $this->db->from($this->table);
            $this->db->where('tanggal', $tanggal);
            $this->db->where('jam', $jam_without_seconds);
            $result = $this->db->get();
            
            if ($result->num_rows() > 0) {
                return $result->result_array();
            }
        }
        
        // Try LIKE match for partial time
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('tanggal', $tanggal);
        $this->db->like('jam', $jam);
        $result = $this->db->get();
        
        return $result->result_array();
    }
} 