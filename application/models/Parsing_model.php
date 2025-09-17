<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parsing_model extends CI_Model
{
    protected $table = 'visa_data';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_parsing_statistics()
    {
        try {
            $stats = array();

            // Total records
            $stats['total_records'] = $this->db->count_all($this->table);

            // Records today
            $this->db->where('DATE(created_at)', date('Y-m-d'));
            $stats['today_records'] = $this->db->count_all_results($this->table);

            // Records this month
            $this->db->where('YEAR(created_at)', date('Y'));
            $this->db->where('MONTH(created_at)', date('m'));
            $stats['month_records'] = $this->db->count_all_results($this->table);

            // Unique passports
            $this->db->select('COUNT(DISTINCT passport_no) as unique_passports');
            $query = $this->db->get($this->table);
            $result = $query->row_array();
            $stats['unique_passports'] = $result['unique_passports'];

            return $stats;
        } catch (Exception $e) {
            log_message('error', 'Error getting parsing statistics: ' . $e->getMessage());
            return array(
                'total_records' => 0,
                'today_records' => 0,
                'month_records' => 0,
                'unique_passports' => 0
            );
        }
    }

    public function save_parsed_data($data)
    {
        try {
            $this->db->trans_start();
            $saved_count = 0;
            
            foreach ($data as $row) {
                $result = $this->upsert($row);
                if ($result !== false) {
                    $saved_count++;
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status()) {
                log_message('info', 'Successfully saved ' . $saved_count . ' out of ' . count($data) . ' records');
                return $saved_count;
            } else {
                log_message('error', 'Database transaction failed');
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Error saving parsed data: ' . $e->getMessage());
            return false;
        }
    }

    private function upsert($row)
    {
        // Validate required fields sesuai dengan database structure
        if (empty($row['nama']) || empty($row['passport_no']) || empty($row['visa_no']) || empty($row['tanggal_lahir'])) {
            log_message('error', 'Missing required fields for visa data');
            return false;
        }
        
        // Check if record already exists based on passport_no and visa_no
        $this->db->where('passport_no', $row['passport_no']);
        $this->db->where('visa_no', $row['visa_no']);
        $query = $this->db->get($this->table);
        
        // Prepare data sesuai dengan struktur database
        $data = array(
            'nama' => substr($row['nama'], 0, 255), // Limit to 255 chars
            'visa_no' => substr($row['visa_no'], 0, 50), // Limit to 50 chars
            'passport_no' => substr($row['passport_no'], 0, 50), // Limit to 50 chars
            'tanggal_lahir' => $row['tanggal_lahir'],
            'raw' => substr($row['raw'], 0, 65535) // Limit raw data size for longblob
        );
        
        if ($query->num_rows() > 0) {
            // Update existing record (tidak ada updated_at di database)
            $existing = $query->row();
            $this->db->where('id', $existing->id);
            $this->db->update($this->table, $data);
            log_message('info', 'Updated existing visa record: ID ' . $existing->id);
            return $existing->id;
        } else {
            // Insert new record dengan created_at
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            $insert_id = $this->db->insert_id();
            log_message('info', 'Inserted new visa record: ID ' . $insert_id);
            return $insert_id;
        }
    }
}