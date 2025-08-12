<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    private $table = 'users';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    public function get_all_users() {
        $this->db->order_by('created_at', 'DESC');
        $this->db->where('username !=', 'adhit'); // hide user bernama adhit
        return $this->db->get($this->table)->result();
    }
    
    public function get_user_by_id($id) {
        return $this->db->get_where($this->table, ['id_user' => $id])->row();
    }
    
    public function get_user_by_username($username) {
        return $this->db->get_where($this->table, ['username' => $username])->row();
    }
    
    public function get_user_by_email($email) {
        return $this->db->get_where($this->table, ['email' => $email])->row();
    }
    
    public function create_user($data) {
        return $this->db->insert($this->table, $data);
    }
    
    public function update_user($id, $data) {
        $this->db->where('id_user', $id);
        return $this->db->update($this->table, $data);
    }
    
    public function delete_user($id) {
        $this->db->where('id_user', $id);
        return $this->db->delete($this->table);
    }
    
    public function check_username($username) {
        return $this->db->get_where($this->table, ['username' => $username])->num_rows();
    }
    
    public function count_all() {
        return $this->db->count_all($this->table);
    }
    
    public function check_password($id, $password) {
        $user = $this->get_user_by_id($id);
        if ($user) {
            return password_verify($password, $user->password);
        }
        return false;
    }
} 