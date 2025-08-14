<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peserta_reject_model extends CI_Model {

    private $table = 'peserta_reject';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
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
                log_message('debug', 'Reject insert successful - Data: ' . json_encode($filtered_data));
                return $this->db->insert_id();
            } else {
                $error = $this->db->error();
                log_message('error', 'Reject insert failed - Last query: ' . $this->db->last_query());
                log_message('error', 'DB Error: ' . json_encode($error));
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Exception in reject insert method: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function get_all() {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }
    
    public function get_by_id($id) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }
    
    public function count_all() {
        return $this->db->count_all($this->table);
    }
    
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
    
    public function delete_all() {
        return $this->db->empty_table($this->table);
    }
    
    public function get_paginated($limit, $offset) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }
    
    public function count_filtered($filters = []) {
        $this->db->from($this->table);
        if (!empty($filters['nama'])) {
            $this->db->like('nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('no_visa', $filters['no_visa']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['gender'])) {
            $this->db->where('gender', $filters['gender']);
        }
        if (isset($filters['flag_doc'])) {
            if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                $this->db->where('(flag_doc IS NULL OR flag_doc = "")');
            } else {
                $this->db->where('flag_doc', $filters['flag_doc']);
            }
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
        
        return $this->db->count_all_results();
    }
    
    public function get_paginated_filtered($limit, $offset, $filters = []) {
        $this->db->select('*');
        $this->db->from($this->table);
    
        if (!empty($filters['nama'])) {
            $this->db->like('nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('no_visa', $filters['no_visa']);
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['gender'])) {
            $this->db->where('gender', $filters['gender']);
        }
        if (isset($filters['flag_doc'])) {
            if ($filters['flag_doc'] === null || $filters['flag_doc'] === 'null' || $filters['flag_doc'] === 'NULL') {
                $this->db->where('(flag_doc IS NULL OR flag_doc = "")');
            } else {
                $this->db->where('flag_doc', $filters['flag_doc']);
            }
        }
        if (!empty($filters['tanggaljam'])) {
            $this->db->like("CONCAT(tanggal, ' ', jam)", $filters['tanggaljam']);
        }
    
        $this->db->order_by('nama', 'ASC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }
}
