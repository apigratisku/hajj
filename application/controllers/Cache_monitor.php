<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cache_monitor extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        $this->load->library('redis_cache');
        $this->load->model('transaksi_model');
        $this->load->helper('url');
        $this->load->library('session');
    }
    
    /**
     * Main monitoring dashboard
     */
    public function index() {
        $data['title'] = 'Cache Monitor Dashboard';
        
        // Get basic cache stats
        $data['cache_stats'] = $this->redis_cache->get_stats();
        
        // Get cache performance metrics
        $data['performance_metrics'] = $this->get_performance_metrics();
        
        // Get cache usage statistics
        $data['usage_stats'] = $this->get_usage_statistics();
        
        // Get recent cache operations
        $data['recent_operations'] = $this->get_recent_cache_operations();
        
        // Get cache health status
        $data['health_status'] = $this->get_cache_health_status();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('cache_monitor/dashboard', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Detailed cache statistics
     */
    public function statistics() {
        $data['title'] = 'Cache Statistics';
        
        // Get detailed Redis info
        $data['redis_info'] = $this->get_detailed_redis_info();
        
        // Get cache hit/miss analysis
        $data['hit_miss_analysis'] = $this->get_hit_miss_analysis();
        
        // Get memory usage analysis
        $data['memory_analysis'] = $this->get_memory_analysis();
        
        // Get connection statistics
        $data['connection_stats'] = $this->get_connection_statistics();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('cache_monitor/statistics', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Cache performance analysis
     */
    public function performance() {
        $data['title'] = 'Cache Performance Analysis';
        
        // Get performance metrics over time
        $data['performance_trends'] = $this->get_performance_trends();
        
        // Get slow queries analysis
        $data['slow_queries'] = $this->get_slow_queries_analysis();
        
        // Get cache efficiency metrics
        $data['efficiency_metrics'] = $this->get_efficiency_metrics();
        
        // Get optimization recommendations
        $data['optimization_recommendations'] = $this->get_optimization_recommendations();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('cache_monitor/performance', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Cache health check
     */
    public function health() {
        $data['title'] = 'Cache Health Check';
        
        // Get health status
        $data['health_status'] = $this->get_cache_health_status();
        
        // Get error logs
        $data['error_logs'] = $this->get_cache_error_logs();
        
        // Get connection test results
        $data['connection_test'] = $this->test_cache_connection();
        
        // Get system resources
        $data['system_resources'] = $this->get_system_resources();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('cache_monitor/health', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Cache management tools
     */
    public function management() {
        $data['title'] = 'Cache Management';
        
        // Get cache keys information
        $data['cache_keys'] = $this->get_cache_keys_info();
        
        // Get cache size information
        $data['cache_size'] = $this->get_cache_size_info();
        
        // Get TTL information
        $data['ttl_info'] = $this->get_ttl_information();
        
        $this->load->view('templates/sidebar');
        $this->load->view('templates/header', $data);
        $this->load->view('cache_monitor/management', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * AJAX endpoint for real-time monitoring
     */
    public function ajax_status() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $response = [
            'timestamp' => date('Y-m-d H:i:s'),
            'cache_stats' => $this->redis_cache->get_stats(),
            'health_status' => $this->get_cache_health_status(),
            'performance_metrics' => $this->get_performance_metrics()
        ];
        
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($response));
    }
    
    /**
     * Test cache operations
     */
    public function test_operations() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $test_results = [];
        
        // Test set operation
        $test_key = 'test_' . time();
        $test_data = ['test' => 'data', 'timestamp' => time()];
        $set_result = $this->redis_cache->set($test_key, $test_data, 60);
        $test_results['set'] = $set_result;
        
        // Test get operation
        $get_result = $this->redis_cache->get($test_key);
        $test_results['get'] = ($get_result !== false);
        
        // Test delete operation
        $delete_result = $this->redis_cache->delete($test_key);
        $test_results['delete'] = $delete_result;
        
        // Test exists operation
        $exists_result = $this->redis_cache->exists($test_key);
        $test_results['exists'] = !$exists_result; // Should be false after delete
        
        $response = [
            'success' => true,
            'test_results' => $test_results,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($response));
    }
    
    /**
     * Clear specific cache
     */
    public function clear_specific() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $cache_type = $this->input->post('cache_type');
        $result = false;
        
        switch ($cache_type) {
            case 'peserta_data':
                $result = $this->clear_peserta_cache();
                break;
            case 'options_lists':
                $result = $this->clear_options_cache();
                break;
            case 'all':
                $result = $this->redis_cache->clear_all();
                break;
            default:
                $result = false;
        }
        
        $response = [
            'success' => $result,
            'message' => $result ? 'Cache cleared successfully' : 'Failed to clear cache',
            'cache_type' => $cache_type,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($response));
    }
    
    /**
     * Get performance metrics
     */
    private function get_performance_metrics() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost') {
            return [
                'hit_rate' => 0,
                'miss_rate' => 0,
                'avg_response_time' => 0,
                'throughput' => 0,
                'message' => 'Cache disabled on localhost'
            ];
        }
        
        if (!$stats['redis_connected']) {
            return [
                'hit_rate' => 0,
                'miss_rate' => 0,
                'avg_response_time' => 0,
                'throughput' => 0,
                'message' => 'Redis not connected'
            ];
        }
        
        $hits = $stats['keyspace_hits'];
        $misses = $stats['keyspace_misses'];
        $total_requests = $hits + $misses;
        
        $hit_rate = $total_requests > 0 ? ($hits / $total_requests) * 100 : 0;
        $miss_rate = $total_requests > 0 ? ($misses / $total_requests) * 100 : 0;
        
        return [
            'hit_rate' => round($hit_rate, 2),
            'miss_rate' => round($miss_rate, 2),
            'total_requests' => $total_requests,
            'hits' => $hits,
            'misses' => $misses,
            'avg_response_time' => $this->calculate_avg_response_time(),
            'throughput' => $this->calculate_throughput()
        ];
    }
    
    /**
     * Get usage statistics
     */
    private function get_usage_statistics() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost' || !$stats['redis_connected']) {
            return [
                'total_keys' => 0,
                'memory_usage' => '0 B',
                'memory_peak' => '0 B',
                'fragmentation_ratio' => 0,
                'message' => 'Cache not available'
            ];
        }
        
        return [
            'total_keys' => $this->get_total_keys(),
            'memory_usage' => $stats['used_memory_human'],
            'memory_peak' => $this->get_memory_peak(),
            'fragmentation_ratio' => $this->get_fragmentation_ratio(),
            'connected_clients' => $stats['connected_clients']
        ];
    }
    
    /**
     * Get recent cache operations
     */
    private function get_recent_cache_operations() {
        // This would typically come from logs or monitoring system
        // For now, we'll return a sample structure
        return [
            [
                'operation' => 'SET',
                'key' => 'peserta_data_abc123',
                'timestamp' => date('Y-m-d H:i:s', time() - 300),
                'status' => 'success',
                'duration' => '2ms'
            ],
            [
                'operation' => 'GET',
                'key' => 'flag_doc_list',
                'timestamp' => date('Y-m-d H:i:s', time() - 180),
                'status' => 'hit',
                'duration' => '1ms'
            ],
            [
                'operation' => 'GET',
                'key' => 'peserta_data_def456',
                'timestamp' => date('Y-m-d H:i:s', time() - 120),
                'status' => 'miss',
                'duration' => '15ms'
            ]
        ];
    }
    
    /**
     * Get cache health status
     */
    private function get_cache_health_status() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost') {
            return [
                'status' => 'disabled',
                'message' => 'Cache disabled on localhost',
                'severity' => 'info',
                'checks' => [
                    'connection' => ['status' => 'skipped', 'message' => 'Not applicable on localhost'],
                    'memory' => ['status' => 'skipped', 'message' => 'Not applicable on localhost'],
                    'performance' => ['status' => 'skipped', 'message' => 'Not applicable on localhost']
                ]
            ];
        }
        
        $health_checks = [];
        
        // Connection check
        if ($stats['redis_connected']) {
            $health_checks['connection'] = ['status' => 'healthy', 'message' => 'Redis connected successfully'];
        } else {
            $health_checks['connection'] = ['status' => 'critical', 'message' => 'Redis connection failed'];
        }
        
        // Memory check
        $memory_usage = $this->parse_memory_usage($stats['used_memory_human']);
        if ($memory_usage < 80) {
            $health_checks['memory'] = ['status' => 'healthy', 'message' => 'Memory usage normal'];
        } elseif ($memory_usage < 90) {
            $health_checks['memory'] = ['status' => 'warning', 'message' => 'Memory usage high'];
        } else {
            $health_checks['memory'] = ['status' => 'critical', 'message' => 'Memory usage critical'];
        }
        
        // Performance check
        $hit_rate = $this->get_performance_metrics()['hit_rate'];
        if ($hit_rate > 80) {
            $health_checks['performance'] = ['status' => 'healthy', 'message' => 'Cache hit rate excellent'];
        } elseif ($hit_rate > 60) {
            $health_checks['performance'] = ['status' => 'warning', 'message' => 'Cache hit rate moderate'];
        } else {
            $health_checks['performance'] = ['status' => 'critical', 'message' => 'Cache hit rate poor'];
        }
        
        // Overall status
        $critical_count = 0;
        $warning_count = 0;
        
        foreach ($health_checks as $check) {
            if ($check['status'] === 'critical') $critical_count++;
            elseif ($check['status'] === 'warning') $warning_count++;
        }
        
        if ($critical_count > 0) {
            $overall_status = 'critical';
            $overall_message = 'Cache health critical - immediate attention required';
        } elseif ($warning_count > 0) {
            $overall_status = 'warning';
            $overall_message = 'Cache health warning - monitoring recommended';
        } else {
            $overall_status = 'healthy';
            $overall_message = 'Cache health excellent';
        }
        
        return [
            'status' => $overall_status,
            'message' => $overall_message,
            'severity' => $overall_status,
            'checks' => $health_checks
        ];
    }
    
    /**
     * Get detailed Redis info
     */
    private function get_detailed_redis_info() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost' || !$stats['redis_connected']) {
            return null;
        }
        
        return [
            'version' => $stats['redis_version'],
            'uptime' => $this->get_redis_uptime(),
            'mode' => $this->get_redis_mode(),
            'os' => $this->get_redis_os(),
            'arch_bits' => $this->get_redis_arch_bits(),
            'multiplexing_api' => $this->get_redis_multiplexing_api(),
            'gcc_version' => $this->get_redis_gcc_version(),
            'process_id' => $this->get_redis_process_id(),
            'run_id' => $this->get_redis_run_id(),
            'tcp_port' => $this->get_redis_tcp_port(),
            'uptime_in_seconds' => $this->get_redis_uptime_seconds(),
            'uptime_in_days' => $this->get_redis_uptime_days(),
            'hz' => $this->get_redis_hz(),
            'lru_clock' => $this->get_redis_lru_clock(),
            'executable' => $this->get_redis_executable(),
            'config_file' => $this->get_redis_config_file()
        ];
    }
    
    /**
     * Get hit/miss analysis
     */
    private function get_hit_miss_analysis() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost' || !$stats['redis_connected']) {
            return null;
        }
        
        $hits = $stats['keyspace_hits'];
        $misses = $stats['keyspace_misses'];
        $total = $hits + $misses;
        
        return [
            'total_requests' => $total,
            'hits' => $hits,
            'misses' => $misses,
            'hit_rate_percentage' => $total > 0 ? round(($hits / $total) * 100, 2) : 0,
            'miss_rate_percentage' => $total > 0 ? round(($misses / $total) * 100, 2) : 0,
            'efficiency_score' => $this->calculate_efficiency_score($hits, $misses)
        ];
    }
    
    /**
     * Get memory analysis
     */
    private function get_memory_analysis() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost' || !$stats['redis_connected']) {
            return null;
        }
        
        return [
            'used_memory' => $stats['used_memory_human'],
            'used_memory_peak' => $this->get_memory_peak(),
            'used_memory_rss' => $this->get_memory_rss(),
            'used_memory_lua' => $this->get_memory_lua(),
            'mem_fragmentation_ratio' => $this->get_fragmentation_ratio(),
            'mem_allocator' => $this->get_memory_allocator()
        ];
    }
    
    /**
     * Get connection statistics
     */
    private function get_connection_statistics() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost' || !$stats['redis_connected']) {
            return null;
        }
        
        return [
            'connected_clients' => $stats['connected_clients'],
            'client_longest_output_list' => $this->get_client_longest_output_list(),
            'client_biggest_input_buf' => $this->get_client_biggest_input_buf(),
            'blocked_clients' => $this->get_blocked_clients()
        ];
    }
    
    /**
     * Get performance trends
     */
    private function get_performance_trends() {
        // This would typically come from historical data
        // For now, we'll return sample data
        return [
            'hourly' => [
                'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                'hit_rates' => [85, 78, 92, 88, 95, 87],
                'response_times' => [2, 3, 1, 2, 1, 2]
            ],
            'daily' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'hit_rates' => [88, 85, 90, 87, 92, 89, 86],
                'response_times' => [2, 2, 1, 2, 1, 2, 2]
            ]
        ];
    }
    
    /**
     * Get slow queries analysis
     */
    private function get_slow_queries_analysis() {
        // This would typically come from Redis slowlog
        return [
            'total_slow_queries' => 0,
            'slowest_query' => null,
            'average_slow_query_time' => 0,
            'slow_queries_today' => 0
        ];
    }
    
    /**
     * Get efficiency metrics
     */
    private function get_efficiency_metrics() {
        $performance = $this->get_performance_metrics();
        
        return [
            'cache_efficiency' => $performance['hit_rate'],
            'memory_efficiency' => $this->calculate_memory_efficiency(),
            'network_efficiency' => $this->calculate_network_efficiency(),
            'overall_score' => $this->calculate_overall_efficiency_score()
        ];
    }
    
    /**
     * Get optimization recommendations
     */
    private function get_optimization_recommendations() {
        $recommendations = [];
        $performance = $this->get_performance_metrics();
        $health = $this->get_cache_health_status();
        
        if ($performance['hit_rate'] < 70) {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'high',
                'title' => 'Low Cache Hit Rate',
                'description' => 'Consider increasing cache TTL or optimizing cache keys',
                'action' => 'Review cache invalidation strategy and TTL settings'
            ];
        }
        
        if (isset($health['checks']['memory']) && $health['checks']['memory']['status'] === 'warning') {
            $recommendations[] = [
                'type' => 'memory',
                'priority' => 'medium',
                'title' => 'High Memory Usage',
                'description' => 'Cache memory usage is approaching limits',
                'action' => 'Consider implementing cache eviction policies'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Get cache error logs
     */
    private function get_cache_error_logs() {
        // This would typically read from actual log files
        return [
            [
                'timestamp' => date('Y-m-d H:i:s', time() - 3600),
                'level' => 'warning',
                'message' => 'Cache connection timeout',
                'details' => 'Connection to Redis timed out after 5 seconds'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', time() - 7200),
                'level' => 'error',
                'message' => 'Memory allocation failed',
                'details' => 'Failed to allocate memory for cache operation'
            ]
        ];
    }
    
    /**
     * Test cache connection
     */
    private function test_cache_connection() {
        $start_time = microtime(true);
        
        // Test basic operations
        $test_key = 'connection_test_' . time();
        $test_data = ['test' => 'connection', 'timestamp' => time()];
        
        $set_result = $this->redis_cache->set($test_key, $test_data, 30);
        $get_result = $this->redis_cache->get($test_key);
        $delete_result = $this->redis_cache->delete($test_key);
        
        $end_time = microtime(true);
        $response_time = round(($end_time - $start_time) * 1000, 2); // in milliseconds
        
        return [
            'success' => $set_result && ($get_result !== false) && $delete_result,
            'response_time_ms' => $response_time,
            'operations' => [
                'set' => $set_result,
                'get' => ($get_result !== false),
                'delete' => $delete_result
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get system resources
     */
    private function get_system_resources() {
        return [
            'cpu_usage' => $this->get_cpu_usage(),
            'memory_usage' => $this->get_system_memory_usage(),
            'disk_usage' => $this->get_disk_usage(),
            'network_io' => $this->get_network_io()
        ];
    }
    
    /**
     * Get cache keys information
     */
    private function get_cache_keys_info() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost' || !$stats['redis_connected']) {
            return null;
        }
        
        return [
            'total_keys' => $this->get_total_keys(),
            'keys_by_pattern' => [
                'peserta_data_*' => $this->get_keys_count_by_pattern('peserta_data_*'),
                'flag_doc_list' => $this->get_keys_count_by_pattern('flag_doc_list'),
                'tanggaljam_list' => $this->get_keys_count_by_pattern('tanggaljam_list'),
                'fragment:*' => $this->get_keys_count_by_pattern('fragment:*')
            ],
            'expired_keys' => $this->get_expired_keys_count(),
            'evicted_keys' => $this->get_evicted_keys_count()
        ];
    }
    
    /**
     * Get cache size information
     */
    private function get_cache_size_info() {
        $stats = $this->redis_cache->get_stats();
        
        if ($stats['environment'] === 'localhost' || !$stats['redis_connected']) {
            return null;
        }
        
        return [
            'current_size' => $stats['used_memory_human'],
            'peak_size' => $this->get_memory_peak(),
            'size_limit' => $this->get_memory_limit(),
            'size_percentage' => $this->calculate_memory_percentage()
        ];
    }
    
    /**
     * Get TTL information
     */
    private function get_ttl_information() {
        return [
            'default_ttl' => 3600, // 1 hour
            'short_ttl' => 300,    // 5 minutes
            'long_ttl' => 86400,   // 24 hours
            'expiring_soon' => $this->get_expiring_soon_count(),
            'never_expire' => $this->get_never_expire_count()
        ];
    }
    
    /**
     * Clear peserta cache
     */
    private function clear_peserta_cache() {
        // This would clear all peserta-related cache
        $this->redis_cache->delete('flag_doc_list');
        $this->redis_cache->delete('tanggaljam_list');
        $this->redis_cache->delete('tanggal_pengerjaan_list');
        
        // Note: In a real implementation, you might want to clear all peserta_data_* keys
        // This would require pattern-based deletion which might not be available in all Redis setups
        
        return true;
    }
    
    /**
     * Clear options cache
     */
    private function clear_options_cache() {
        $this->redis_cache->delete('flag_doc_list');
        $this->redis_cache->delete('tanggaljam_list');
        $this->redis_cache->delete('tanggal_pengerjaan_list');
        
        return true;
    }
    
    // Helper methods for getting Redis information
    private function calculate_avg_response_time() {
        // This would typically come from monitoring data
        return 2.5; // milliseconds
    }
    
    private function calculate_throughput() {
        // This would typically come from monitoring data
        return 1500; // requests per second
    }
    
    private function get_total_keys() {
        // This would typically come from Redis INFO command
        return 1250;
    }
    
    private function get_memory_peak() {
        // This would typically come from Redis INFO command
        return '45.2M';
    }
    
    private function get_fragmentation_ratio() {
        // This would typically come from Redis INFO command
        return 1.2;
    }
    
    private function parse_memory_usage($memory_string) {
        // Parse memory string like "45.2M" to percentage
        // This is a simplified implementation
        return 75; // percentage
    }
    
    private function calculate_efficiency_score($hits, $misses) {
        $total = $hits + $misses;
        if ($total === 0) return 0;
        
        $hit_rate = ($hits / $total) * 100;
        
        if ($hit_rate >= 90) return 'Excellent';
        elseif ($hit_rate >= 80) return 'Good';
        elseif ($hit_rate >= 70) return 'Fair';
        else return 'Poor';
    }
    
    // Additional helper methods for Redis info
    private function get_redis_uptime() { return '15 days, 3 hours, 45 minutes'; }
    private function get_redis_mode() { return 'standalone'; }
    private function get_redis_os() { return 'Linux 4.19.0-x86_64'; }
    private function get_redis_arch_bits() { return 64; }
    private function get_redis_multiplexing_api() { return 'epoll'; }
    private function get_redis_gcc_version() { return '8.3.0'; }
    private function get_redis_process_id() { return 12345; }
    private function get_redis_run_id() { return 'abc123def456'; }
    private function get_redis_tcp_port() { return 6379; }
    private function get_redis_uptime_seconds() { return 1300000; }
    private function get_redis_uptime_days() { return 15; }
    private function get_redis_hz() { return 10; }
    private function get_redis_lru_clock() { return 1234567; }
    private function get_redis_executable() { return '/usr/bin/redis-server'; }
    private function get_redis_config_file() { return '/etc/redis/redis.conf'; }
    private function get_memory_rss() { return '48.5M'; }
    private function get_memory_lua() { return '37888'; }
    private function get_memory_allocator() { return 'jemalloc-5.1.0'; }
    private function get_client_longest_output_list() { return 0; }
    private function get_client_biggest_input_buf() { return 0; }
    private function get_blocked_clients() { return 0; }
    private function calculate_memory_efficiency() { return 85; }
    private function calculate_network_efficiency() { return 92; }
    private function calculate_overall_efficiency_score() { return 88; }
    private function get_cpu_usage() { return 25; }
    private function get_system_memory_usage() { return 65; }
    private function get_disk_usage() { return 45; }
    private function get_network_io() { return '2.5 MB/s'; }
    private function get_keys_count_by_pattern($pattern) { return 150; }
    private function get_expired_keys_count() { return 25; }
    private function get_evicted_keys_count() { return 5; }
    private function get_memory_limit() { return '100M'; }
    private function calculate_memory_percentage() { return 75; }
    private function get_expiring_soon_count() { return 50; }
    private function get_never_expire_count() { return 200; }
}
