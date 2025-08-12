<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_model extends CI_Model {

    private $table = 'peserta';
    
    public function __construct() {
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
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }
    
   
    public function insert($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
    
    public function count_all() {
        return $this->db->count_all($this->table);
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
        if (!empty($filters['flag_doc'])) {
            $this->db->like('peserta.flag_doc', $filters['flag_doc']);
        }
        
        $this->db->order_by('id', 'DESC');
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
        if (!empty($filters['flag_doc'])) {
            $this->db->like('peserta.flag_doc', $filters['flag_doc']);
        }
        
        return $this->db->count_all_results();
    }

    public function get_by_passport($nomor_paspor) {
        $this->db->where('nomor_paspor', $nomor_paspor);
        return $this->db->get($this->table)->row();
    }
    
    public function get_unique_flag_doc() {
        $this->db->select('flag_doc');
        $this->db->from($this->table);
        $this->db->where('flag_doc IS NOT NULL');
        $this->db->where('flag_doc !=', '');
        $this->db->group_by('flag_doc');
        $this->db->order_by('flag_doc', 'ASC');
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
} 