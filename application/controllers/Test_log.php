<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_log extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('log_activity_model');
        $this->load->helper('log_activity');
    }

    public function index()
    {
        echo "<h2>Testing Log Activity Functions</h2>";
        
        // Simulasi session
        $this->session->set_userdata('username', 'test_user');
        $this->session->set_userdata('logged_in', true);
        
        echo "<h3>Test 1: Database Connection</h3>";
        try {
            $this->load->database();
            $query = $this->db->query("SELECT 1 as test");
            echo "Database connection: <span style='color: green;'>SUCCESS</span><br>";
        } catch (Exception $e) {
            echo "Database connection: <span style='color: red;'>FAILED - " . $e->getMessage() . "</span><br>";
        }
        
        echo "<h3>Test 2: Check Table Exists</h3>";
        $table_exists = $this->db->table_exists('log_aktivitas_user');
        echo "Table log_aktivitas_user exists: " . ($table_exists ? "<span style='color: green;'>YES</span>" : "<span style='color: red;'>NO</span>") . "<br>";
        
        if ($table_exists) {
            echo "<h3>Test 3: Table Structure</h3>";
            $fields = $this->db->list_fields('log_aktivitas_user');
            echo "Table fields: " . implode(', ', $fields) . "<br>";
        }
        
        echo "<h3>Test 4: Log user activity</h3>";
        $result1 = log_user_activity(1, 'Test aktivitas dari controller', 'test_user');
        echo "Result: " . ($result1 ? "<span style='color: green;'>SUCCESS (ID: $result1)</span>" : "<span style='color: red;'>FAILED</span>") . "<br><br>";
        
        echo "<h3>Test 5: Log peserta activity</h3>";
        $result2 = log_peserta_activity(1, 'create', 'Test create peserta dari controller');
        echo "Result: " . ($result2 ? "<span style='color: green;'>SUCCESS (ID: $result2)</span>" : "<span style='color: red;'>FAILED</span>") . "<br><br>";
        
        echo "<h3>Test 6: Log system activity</h3>";
        $result3 = log_system_activity('Test system activity dari controller', 'test_user');
        echo "Result: " . ($result3 ? "<span style='color: green;'>SUCCESS (ID: $result3)</span>" : "<span style='color: red;'>FAILED</span>") . "<br><br>";
        
        echo "<h3>Test 7: Direct Model Insert</h3>";
        $test_data = [
            'id_peserta' => 999,
            'user_operator' => 'test_direct',
            'tanggal' => date('Y-m-d'),
            'jam' => date('H:i:s'),
            'aktivitas' => 'Test direct model insert',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $result4 = $this->log_activity_model->insert_log($test_data);
        echo "Direct model insert: " . ($result4 ? "<span style='color: green;'>SUCCESS (ID: $result4)</span>" : "<span style='color: red;'>FAILED</span>") . "<br><br>";
        
        echo "<h3>Test 8: Check database</h3>";
        $logs = $this->log_activity_model->get_all_logs(10, 0, []);
        echo "Total logs in database: " . count($logs) . "<br>";
        
        if (!empty($logs)) {
            echo "<h4>Latest logs:</h4>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>User</th><th>Peserta ID</th><th>Activity</th><th>Date</th><th>Time</th></tr>";
            foreach (array_slice($logs, 0, 5) as $log) {
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
        
        echo "<br><h3>Test completed!</h3>";
        echo "<a href='" . base_url('log_activity') . "'>View Log Activity Page</a> | ";
        echo "<a href='" . base_url('test_log') . "'>Run Test Again</a>";
    }
}
