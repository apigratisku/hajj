<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log_activity_model extends CI_Model
{
    protected $table = 'log_aktivitas_user';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Insert log aktivitas baru
     */
    public function insert_log($data)
    {
        // Pastikan data yang diperlukan ada
        $required_fields = ['user_operator', 'tanggal', 'jam', 'aktivitas'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                log_message('error', "Log_activity_model: Missing required field: $field");
                return false;
            }
        }
        
        // Validasi khusus untuk id_peserta (boleh 0 untuk system activity)
        if (!isset($data['id_peserta'])) {
            log_message('error', "Log_activity_model: Missing required field: id_peserta");
            return false;
        }

        // created_at akan diisi otomatis oleh database dengan CURRENT_TIMESTAMP
        // Tidak perlu set manual karena sudah ada default value

        // Debug: Log data yang akan diinsert
        log_message('debug', "Log_activity_model: Attempting to insert data: " . json_encode($data));
        
        $result = $this->db->insert($this->table, $data);
        
        if ($result) {
            $insert_id = $this->db->insert_id();
            log_message('info', "Log_activity_model: Successfully inserted log for user {$data['user_operator']} - {$data['aktivitas']} - ID: {$insert_id}");
            return $insert_id;
        } else {
            $error = $this->db->error();
            log_message('error', "Log_activity_model: Failed to insert log - Query: " . $this->db->last_query());
            log_message('error', "Log_activity_model: DB Error: " . json_encode($error));
            return false;
        }
    }

    /**
     * Get semua log aktivitas (kecuali user adhit)
     */
    public function get_all_logs($limit = 100, $offset = 0, $filters = [])
    {
        $this->db->select('l.*, p.nama as nama_peserta, p.nomor_paspor as nomor_peserta');
        $this->db->from($this->table . ' l');
        $this->db->join('peserta p', 'l.id_peserta = p.id', 'left');
        
        // Exclude user adhit
        $this->db->where('l.user_operator !=', 'adhit');
        
        // Apply filters
        if (!empty($filters['user_operator'])) {
            $this->db->where('l.user_operator', $filters['user_operator']);
        }
        
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('l.tanggal >=', $filters['tanggal_dari']);
        }
        
        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('l.tanggal <=', $filters['tanggal_sampai']);
        }
        
        if (!empty($filters['aktivitas'])) {
            $this->db->like('l.aktivitas', $filters['aktivitas']);
        }
        
        if (!empty($filters['id_peserta'])) {
            $this->db->where('l.id_peserta', $filters['id_peserta']);
        }
        
        $this->db->order_by('l.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get log aktivitas berdasarkan ID peserta
     */
    public function get_logs_by_peserta($id_peserta, $limit = 50)
    {
        $this->db->select('l.*, p.nama as nama_peserta, p.nomor_paspor as nomor_peserta');
        $this->db->from($this->table . ' l');
        $this->db->join('peserta p', 'l.id_peserta = p.id', 'left');
        $this->db->where('l.id_peserta', $id_peserta);
        $this->db->where('l.user_operator !=', 'adhit');
        $this->db->order_by('l.created_at', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get log aktivitas berdasarkan user operator
     */
    public function get_logs_by_user($user_operator, $limit = 50)
    {
        $this->db->select('l.*, p.nama as nama_peserta, p.nomor_paspor as nomor_peserta');
        $this->db->from($this->table . ' l');
        $this->db->join('peserta p', 'l.id_peserta = p.id', 'left');
        $this->db->where('l.user_operator', $user_operator);
        $this->db->order_by('l.created_at', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get statistik log aktivitas
     */
    public function get_log_statistics($filters = [])
    {
        $this->db->select('
            COUNT(*) as total_logs,
            COUNT(DISTINCT user_operator) as total_users,
            COUNT(DISTINCT id_peserta) as total_peserta,
            DATE(created_at) as tanggal
        ');
        $this->db->from($this->table);
        $this->db->where('user_operator !=', 'adhit');
        
        // Apply filters
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('tanggal >=', $filters['tanggal_dari']);
        }
        
        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('tanggal <=', $filters['tanggal_sampai']);
        }
        
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('tanggal', 'DESC');
        $this->db->limit(30); // Last 30 days
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get top users by activity
     */
    public function get_top_users($limit = 10, $filters = [])
    {
        $this->db->select('
            user_operator,
            COUNT(*) as total_activities,
            COUNT(DISTINCT id_peserta) as unique_peserta,
            MAX(created_at) as last_activity
        ');
        $this->db->from($this->table);
        $this->db->where('user_operator !=', 'adhit');
        
        // Apply filters
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('tanggal >=', $filters['tanggal_dari']);
        }
        
        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('tanggal <=', $filters['tanggal_sampai']);
        }
        
        $this->db->group_by('user_operator');
        $this->db->order_by('total_activities', 'DESC');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get activity types summary
     */
    public function get_activity_summary($filters = [])
    {
        $this->db->select('
            aktivitas,
            COUNT(*) as total_count,
            COUNT(DISTINCT user_operator) as unique_users,
            COUNT(DISTINCT id_peserta) as unique_peserta
        ');
        $this->db->from($this->table);
        $this->db->where('user_operator !=', 'adhit');
        
        // Apply filters
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('tanggal >=', $filters['tanggal_dari']);
        }
        
        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('tanggal <=', $filters['tanggal_sampai']);
        }
        
        $this->db->group_by('aktivitas');
        $this->db->order_by('total_count', 'DESC');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get unique user operators (kecuali adhit)
     */
    public function get_unique_users()
    {
        $this->db->distinct();
        $this->db->select('user_operator');
        $this->db->from($this->table);
        $this->db->where('user_operator !=', 'adhit');
        $this->db->order_by('user_operator', 'ASC');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Count total logs
     */
    public function count_logs($filters = [])
    {
        $this->db->from($this->table);
        $this->db->where('user_operator !=', 'adhit');
        
        // Apply filters
        if (!empty($filters['user_operator'])) {
            $this->db->where('user_operator', $filters['user_operator']);
        }
        
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('tanggal >=', $filters['tanggal_dari']);
        }
        
        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('tanggal <=', $filters['tanggal_sampai']);
        }
        
        if (!empty($filters['aktivitas'])) {
            $this->db->like('aktivitas', $filters['aktivitas']);
        }
        
        if (!empty($filters['id_peserta'])) {
            $this->db->where('id_peserta', $filters['id_peserta']);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Delete old logs (older than specified days)
     */
    public function delete_old_logs($days = 90)
    {
        $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime("-{$days} days")));
        $result = $this->db->delete($this->table);
        
        if ($result) {
            log_message('info', "Log_activity_model: Deleted old logs older than {$days} days");
        }
        
        return $result;
    }
}
