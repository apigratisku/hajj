<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_log extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('log_activity_model');
        $this->load->helper('log_activity');
    }

    public function index()
    {
        echo "<h2>Debug Log Activity Insert</h2>";
        
        // Set session untuk testing
        $this->session->set_userdata('username', 'debug_test');
        $this->session->set_userdata('logged_in', true);
        
        echo "<h3>1. Database Connection Test</h3>";
        try {
            $this->load->database();
            $query = $this->db->query("SELECT 1 as test");
            echo "✅ Database connection: OK<br>";
        } catch (Exception $e) {
            echo "❌ Database connection: FAILED - " . $e->getMessage() . "<br>";
            exit;
        }
        
        echo "<h3>2. Table Existence Check</h3>";
        $table_exists = $this->db->table_exists('log_aktivitas_user');
        echo "Table log_aktivitas_user exists: " . ($table_exists ? "✅ YES" : "❌ NO") . "<br>";
        
        if (!$table_exists) {
            echo "❌ Table tidak ada! Jalankan setup terlebih dahulu.<br>";
            exit;
        }
        
        echo "<h3>3. Table Structure Check</h3>";
        $fields = $this->db->list_fields('log_aktivitas_user');
        echo "Table fields: " . implode(', ', $fields) . "<br>";
        
        echo "<h3>4. Direct Model Insert Test</h3>";
        $test_data = [
            'id_peserta' => 1,
            'user_operator' => 'debug_test',
            'tanggal' => date('Y-m-d'),
            'jam' => date('H:i:s'),
            'aktivitas' => 'Debug test insert dari model'
            // created_at akan diisi otomatis oleh database
        ];
        
        echo "Test data: " . json_encode($test_data) . "<br>";
        
        $result = $this->log_activity_model->insert_log($test_data);
        echo "Model insert result: " . ($result ? "✅ SUCCESS (ID: $result)" : "❌ FAILED") . "<br>";
        
        if (!$result) {
            echo "Last query: " . $this->db->last_query() . "<br>";
            echo "DB error: " . json_encode($this->db->error()) . "<br>";
        }
        
        echo "<h3>5. Helper Function Test</h3>";
        $helper_result = log_user_activity(2, 'Debug helper test', 'debug_test');
        echo "Helper function result: " . ($helper_result ? "✅ SUCCESS (ID: $helper_result)" : "❌ FAILED") . "<br>";
        
        echo "<h3>6. Check Data in Table</h3>";
        $logs = $this->log_activity_model->get_all_logs(5, 0, []);
        echo "Total logs: " . count($logs) . "<br>";
        
        if (!empty($logs)) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>User</th><th>Peserta</th><th>Activity</th><th>Date</th><th>Time</th></tr>";
            foreach ($logs as $log) {
                echo "<tr>";
                echo "<td>" . $log->id_log . "</td>";
                echo "<td>" . $log->user_operator . "</td>";
                echo "<td>" . $log->id_peserta . "</td>";
                echo "<td>" . htmlspecialchars($log->aktivitas) . "</td>";
                echo "<td>" . $log->tanggal . "</td>";
                echo "<td>" . $log->jam . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<h3>7. Raw SQL Test</h3>";
        $raw_sql = "INSERT INTO log_aktivitas_user (id_peserta, user_operator, tanggal, jam, aktivitas) VALUES (?, ?, ?, ?, ?)";
        $raw_data = [3, 'raw_test', date('Y-m-d'), date('H:i:s'), 'Raw SQL test'];
        
        $raw_result = $this->db->query($raw_sql, $raw_data);
        echo "Raw SQL result: " . ($raw_result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
        
        if (!$raw_result) {
            echo "Raw SQL error: " . json_encode($this->db->error()) . "<br>";
        }
        
        echo "<br><h3>Debug completed!</h3>";
        echo "<a href='" . base_url('log_activity') . "'>View Log Activity Page</a> | ";
        echo "<a href='" . base_url('debug_log') . "'>Run Debug Again</a>";
    }
}
