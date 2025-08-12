<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peserta_model extends CI_Model {

    private $table = 'peserta';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    public function get_all() {
        $this->db->select('peserta.*, agent.nama_agent');
        $this->db->from($this->table);
        $this->db->join('agent', 'agent.id_agent = peserta.id_agent', 'left');
        $this->db->order_by('peserta.id', 'DESC');
        return $this->db->get()->result();
    }
    
    public function get_by_id($id) {
        $this->db->select('peserta.*, agent.nama_agent');
        $this->db->from($this->table);
        $this->db->join('agent', 'agent.id_agent = peserta.id_agent', 'left');
        $this->db->where('peserta.id', $id);
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
        $this->db->select('peserta.*, agent.nama_agent');
        $this->db->from($this->table);
        $this->db->join('agent', 'agent.id_agent = peserta.id_agent', 'left');
        $this->db->where('peserta.status_trx', 0);
        $this->db->order_by('peserta.id', 'ASC');
        
        return $this->db->get()->result();
    }

    public function get_paginated($limit, $offset) {
        $this->db->select('peserta.*, agent.nama_agent');
        $this->db->from($this->table);
        $this->db->join('agent', 'agent.id_agent = peserta.id_agent', 'left');
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function get_paginated_filtered($limit, $offset, $filters = []) {
        $this->db->select('peserta.*, agent.nama_agent');
        $this->db->from($this->table);
        $this->db->join('agent', 'agent.id_agent = peserta.id_agent', 'left');
        
        if (!empty($filters['nama_agent'])) {
            $this->db->like('agent.nama_agent', $filters['nama_agent']);
        }
        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('peserta.nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('peserta.no_visa', $filters['no_visa']);
        }
        
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_filtered($filters = []) {
        $this->db->from($this->table);
        $this->db->join('agent', 'agent.id_agent = peserta.id_agent', 'left');
        
        if (!empty($filters['nama_agent'])) {
            $this->db->like('agent.nama_agent', $filters['nama_agent']);
        }
        if (!empty($filters['nama'])) {
            $this->db->like('peserta.nama', $filters['nama']);
        }
        if (!empty($filters['nomor_paspor'])) {
            $this->db->like('peserta.nomor_paspor', $filters['nomor_paspor']);
        }
        if (!empty($filters['no_visa'])) {
            $this->db->like('peserta.no_visa', $filters['no_visa']);
        }
        
        return $this->db->count_all_results();
    }
    
    public function get_all_agents() {
        return $this->db->get('agent')->result();
    }
}
