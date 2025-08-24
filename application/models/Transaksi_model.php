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
            $filtered_data['updated_at'] = date('Y-m-d H:i:s');
            
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
        if (isset($filters['flag_doc'])) {
            // Handle flag_doc filter more precisely
            if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
            } else {
                $this->db->where('peserta.flag_doc', $filters['flag_doc']);
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

        if (isset($filters['flag_doc'])) {
            // Handle flag_doc filter more precisely
            if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
            } else {
                $this->db->where('peserta.flag_doc', $filters['flag_doc']);
            }
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
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('peserta.status', $filters['status']);
        }
        if (isset($filters['flag_doc'])) {
            // Handle flag_doc filter more precisely
            if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
            } else {
                $this->db->where('peserta.flag_doc', $filters['flag_doc']);
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
        
        return $this->db->count_all_results();
    }

    public function count_filtered_todo($filters = []) {
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
       
        if (isset($filters['flag_doc'])) {
            // Handle flag_doc filter more precisely
            if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                $this->db->where('(peserta.flag_doc IS NULL OR peserta.flag_doc = "")');
                $this->db->where('peserta.status', 0);
            } else {
                $this->db->where('peserta.flag_doc', $filters['flag_doc']);
                $this->db->where('peserta.status', 0);
            }
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
        
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
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->update($this->table, $data);
        
        // Log untuk debugging
        log_message('debug', 'Update massal - Tanggal: ' . $tanggal . ', Jam: ' . $jam . ', Flag Doc: ' . $flag_doc . ', Affected Rows: ' . $this->db->affected_rows());
        
        return $result;
    }
} 