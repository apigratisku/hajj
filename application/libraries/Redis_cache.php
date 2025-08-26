<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Redis_cache {
    
    private $redis = null;
    private $is_localhost = false;
    private $is_connected = false;
    private $socket_path = '/home/menb8295/tmp/redis.sock';
    
    public function __construct() {
        // Detect if running on localhost
        $this->is_localhost = $this->is_localhost_environment();
        
        // Only try to connect to Redis if not on localhost
        if (!$this->is_localhost) {
            $this->connect_redis();
        }
    }
    
    /**
     * Detect if running on localhost environment
     */
    private function is_localhost_environment() {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        
        // Check for localhost patterns
        $localhost_patterns = [
            'localhost',
            '127.0.0.1',
            '::1',
            'localhost:',
            '127.0.0.1:',
            'localhost/hajj',
            '127.0.0.1/hajj'
        ];
        
        foreach ($localhost_patterns as $pattern) {
            if (strpos($host, $pattern) !== false || 
                strpos($server_name, $pattern) !== false ||
                strpos($server_addr, $pattern) !== false) {
                return true;
            }
        }
        
        // Additional check for XAMPP/WAMP environment
        $document_root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
        if (strpos($document_root, 'htdocs') !== false ||
            strpos($document_root, 'www') !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Connect to Redis using Unix socket
     */
    private function connect_redis() {
        try {
            if (!extension_loaded('redis')) {
                log_message('warning', 'Redis extension not loaded. Cache will be disabled.');
                return false;
            }
            
            $this->redis = new Redis();
            
            // Try to connect using Unix socket
            if (file_exists($this->socket_path)) {
                $connected = $this->redis->connect($this->socket_path);
                if ($connected) {
                    $this->is_connected = true;
                    log_message('info', 'Redis connected successfully via Unix socket: ' . $this->socket_path);
                    return true;
                }
            }
            
            // Fallback to TCP connection if socket doesn't exist
            $connected = $this->redis->connect('127.0.0.1', 6379);
            if ($connected) {
                $this->is_connected = true;
                log_message('info', 'Redis connected successfully via TCP');
                return true;
            }
            
            log_message('warning', 'Failed to connect to Redis. Cache will be disabled.');
            return false;
            
        } catch (Exception $e) {
            log_message('error', 'Redis connection error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set cache data
     */
    public function set($key, $data, $ttl = 3600) {
        if ($this->is_localhost) {
            log_message('debug', 'Cache set skipped on localhost - Key: ' . $key);
            return true;
        }
        
        if (!$this->is_connected || !$this->redis) {
            return false;
        }
        
        try {
            $serialized_data = serialize($data);
            $result = $this->redis->setex($key, $ttl, $serialized_data);
            log_message('debug', 'Cache set - Key: ' . $key . ', TTL: ' . $ttl . ', Result: ' . ($result ? 'success' : 'failed'));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Redis set error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache data
     */
    public function get($key) {
        if ($this->is_localhost) {
            log_message('debug', 'Cache get skipped on localhost - Key: ' . $key);
            return false;
        }
        
        if (!$this->is_connected || !$this->redis) {
            return false;
        }
        
        try {
            $data = $this->redis->get($key);
            if ($data !== false) {
                $unserialized_data = unserialize($data);
                log_message('debug', 'Cache hit - Key: ' . $key);
                return $unserialized_data;
            } else {
                log_message('debug', 'Cache miss - Key: ' . $key);
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Redis get error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete cache data
     */
    public function delete($key) {
        if ($this->is_localhost) {
            log_message('debug', 'Cache delete skipped on localhost - Key: ' . $key);
            return true;
        }
        
        if (!$this->is_connected || !$this->redis) {
            return false;
        }
        
        try {
            $result = $this->redis->del($key);
            log_message('debug', 'Cache delete - Key: ' . $key . ', Result: ' . $result);
            return $result > 0;
        } catch (Exception $e) {
            log_message('error', 'Redis delete error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if key exists
     */
    public function exists($key) {
        if ($this->is_localhost) {
            return false;
        }
        
        if (!$this->is_connected || !$this->redis) {
            return false;
        }
        
        try {
            return $this->redis->exists($key);
        } catch (Exception $e) {
            log_message('error', 'Redis exists error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set cache with fragment key
     */
    public function set_fragment($fragment_name, $data, $ttl = 3600) {
        $key = 'fragment:' . $fragment_name;
        return $this->set($key, $data, $ttl);
    }
    
    /**
     * Get cache fragment
     */
    public function get_fragment($fragment_name) {
        $key = 'fragment:' . $fragment_name;
        return $this->get($key);
    }
    
    /**
     * Delete cache fragment
     */
    public function delete_fragment($fragment_name) {
        $key = 'fragment:' . $fragment_name;
        return $this->delete($key);
    }
    
    /**
     * Clear all cache
     */
    public function clear_all() {
        if ($this->is_localhost) {
            log_message('debug', 'Cache clear all skipped on localhost');
            return true;
        }
        
        if (!$this->is_connected || !$this->redis) {
            return false;
        }
        
        try {
            $result = $this->redis->flushDB();
            log_message('info', 'Cache cleared all - Result: ' . ($result ? 'success' : 'failed'));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Redis clear all error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache statistics
     */
    public function get_stats() {
        if ($this->is_localhost) {
            return [
                'environment' => 'localhost',
                'redis_connected' => false,
                'message' => 'Cache disabled on localhost'
            ];
        }
        
        if (!$this->is_connected || !$this->redis) {
            return [
                'environment' => 'hosting',
                'redis_connected' => false,
                'message' => 'Redis not connected'
            ];
        }
        
        try {
            $info = $this->redis->info();
            return [
                'environment' => 'hosting',
                'redis_connected' => true,
                'redis_version' => isset($info['redis_version']) ? $info['redis_version'] : 'unknown',
                'connected_clients' => isset($info['connected_clients']) ? $info['connected_clients'] : 0,
                'used_memory_human' => isset($info['used_memory_human']) ? $info['used_memory_human'] : 'unknown',
                'keyspace_hits' => isset($info['keyspace_hits']) ? $info['keyspace_hits'] : 0,
                'keyspace_misses' => isset($info['keyspace_misses']) ? $info['keyspace_misses'] : 0
            ];
        } catch (Exception $e) {
            return [
                'environment' => 'hosting',
                'redis_connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if Redis is available
     */
    public function is_available() {
        return !$this->is_localhost && $this->is_connected;
    }
    
    /**
     * Get environment info
     */
    public function get_environment() {
        return $this->is_localhost ? 'localhost' : 'hosting';
    }
}
