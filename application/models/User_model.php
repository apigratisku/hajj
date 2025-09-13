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
        $this->db->where('username !=', 'mimin'); // hide user bernama mimin
        return $this->db->get($this->table)->result();
    }
    public function get_all_users_for_filter() {
        $this->db->order_by('created_at', 'DESC');
        $this->db->where('username !=', 'adhit'); // hide user bernama adhit
        $this->db->where('username !=', 'mimin'); // hide user bernama mimin
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
        // Set default status to enabled (1) for new users
        if (!isset($data['status'])) {
            $data['status'] = 1;
        }
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
    
    // New methods for user status management
    public function enable_user($id) {
        $this->db->where('id_user', $id);
        return $this->db->update($this->table, ['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }
    
    public function disable_user($id) {
        $this->db->where('id_user', $id);
        return $this->db->update($this->table, ['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }
    
    public function toggle_user_status($id) {
        $user = $this->get_user_by_id($id);
        if ($user) {
            $new_status = ($user->status == 1) ? 0 : 1;
            return $this->update_user($id, ['status' => $new_status, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        return false;
    }
    
    public function get_user_status($id) {
        $user = $this->get_user_by_id($id);
        return $user ? $user->status : null;
    }
    
    public function is_user_enabled($id) {
        $user = $this->get_user_by_id($id);
        return $user && $user->status == 1;
    }
    
    public function get_enabled_users() {
        $this->db->where('status', 1);
        $this->db->where('username !=', 'adhit');
        $this->db->where('username !=', 'mimin');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table)->result();
    }
    
    public function get_disabled_users() {
        $this->db->where('status', 0);
        $this->db->where('username !=', 'adhit');
        $this->db->where('username !=', 'mimin');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table)->result();
    }
} 