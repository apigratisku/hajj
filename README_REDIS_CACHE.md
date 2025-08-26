# Redis Cache Implementation

## Overview
Implementasi Redis cache dengan socket path `/home/menb8295/tmp/redis.sock` dan kondisi otomatis untuk localhost vs hosting cpanel.

## Features

### 1. Environment Detection
- **Localhost:** Cache operations disabled untuk development
- **Hosting:** Cache operations enabled dengan Redis

### 2. Socket Connection
- Primary: Unix socket `/home/menb8295/tmp/redis.sock`
- Fallback: TCP connection `127.0.0.1:6379`

### 3. Cache Operations
- Set cache data dengan TTL
- Get cache data
- Delete cache data
- Fragment cache support
- Clear all cache

## Installation

### 1. Redis Extension
Pastikan Redis extension PHP sudah terinstall:
```bash
# Ubuntu/Debian
sudo apt-get install php-redis

# CentOS/RHEL
sudo yum install php-redis

# Windows (XAMPP)
# Download dan install Redis extension untuk Windows
```

### 2. Redis Server (Hosting)
Untuk hosting cpanel, Redis server biasanya sudah tersedia. Pastikan socket file ada di:
```
/home/menb8295/tmp/redis.sock
```

## Usage

### 1. Load Library
```php
$this->load->library('redis_cache');
```

### 2. Basic Cache Operations
```php
// Set cache
$this->redis_cache->set('key', $data, 3600); // TTL 1 hour

// Get cache
$data = $this->redis_cache->get('key');

// Delete cache
$this->redis_cache->delete('key');

// Check if exists
if ($this->redis_cache->exists('key')) {
    // Key exists
}
```

### 3. Fragment Cache
```php
// Set fragment cache
$this->redis_cache->set_fragment('user_list', $user_data, 1800);

// Get fragment cache
$user_data = $this->redis_cache->get_fragment('user_list');

// Delete fragment cache
$this->redis_cache->delete_fragment('user_list');
```

### 4. Cache Statistics
```php
// Get cache statistics
$stats = $this->redis_cache->get_stats();

// Check if Redis is available
if ($this->redis_cache->is_available()) {
    // Redis is connected
}

// Get environment
$env = $this->redis_cache->get_environment(); // 'localhost' or 'hosting'
```

## Implementation in Database Controller

### 1. Cache Data Peserta
```php
// Try to get data from cache first
$cache_key = 'peserta_data_' . md5(serialize($filters) . $per_page . $offset);
$cached_data = $this->redis_cache->get($cache_key);

if ($cached_data !== false) {
    $data['peserta'] = $cached_data;
} else {
    // Get data from database
    $data['peserta'] = $this->transaksi_model->get_paginated_filtered($per_page, $offset, $filters);
    
    // Cache the data for 5 minutes
    $this->redis_cache->set($cache_key, $data['peserta'], 300);
}
```

### 2. Cache Options Lists
```php
// Cache flag_doc options (1 hour)
$flag_doc_cache_key = 'flag_doc_list';
$cached_flag_doc = $this->redis_cache->get($flag_doc_cache_key);

if ($cached_flag_doc !== false) {
    $data['flag_doc_list'] = $cached_flag_doc;
} else {
    $data['flag_doc_list'] = $this->transaksi_model->get_unique_flag_doc();
    $this->redis_cache->set($flag_doc_cache_key, $data['flag_doc_list'], 3600);
}
```

### 3. Cache Invalidation
```php
// Invalidate cache when data is updated
private function invalidate_peserta_cache() {
    $this->redis_cache->delete('flag_doc_list');
    $this->redis_cache->delete('tanggaljam_list');
    $this->redis_cache->delete('tanggal_pengerjaan_list');
}
```

## Cache Management Interface

### 1. View Cache Statistics
URL: `database/cache_stats`

Features:
- Environment detection (localhost/hosting)
- Redis connection status
- Redis statistics (version, memory, hit rate)
- Cache management tools

### 2. Clear All Cache
URL: `database/clear_cache`

Features:
- Clear all cached data
- Confirmation dialog
- Redirect to cache statistics

## Environment Behavior

### Localhost Environment
- Cache operations are skipped
- Debug logs show cache operations
- No Redis connection attempted
- Application runs normally without cache

### Hosting Environment
- Redis connection attempted via socket
- Fallback to TCP if socket unavailable
- Cache operations enabled
- Performance optimization active

## Configuration

### Socket Path
Default socket path: `/home/menb8295/tmp/redis.sock`

To change socket path, modify in `Redis_cache.php`:
```php
private $socket_path = '/path/to/your/redis.sock';
```

### TTL Defaults
- Data peserta: 5 minutes (300 seconds)
- Options lists: 1 hour (3600 seconds)
- Fragment cache: 1 hour (3600 seconds)

## Monitoring

### Log Messages
Cache operations are logged with different levels:
- `debug`: Cache hits/misses, operations
- `info`: Successful connections, cache clears
- `warning`: Connection failures, extension missing
- `error`: Redis errors, exceptions

### Statistics Available
- Redis version
- Connected clients
- Used memory
- Cache hits/misses
- Hit rate percentage

## Troubleshooting

### Common Issues

1. **Redis Extension Not Loaded**
   - Install Redis extension for PHP
   - Check php.ini configuration

2. **Socket File Not Found**
   - Verify socket path exists
   - Check permissions
   - Fallback to TCP connection

3. **Connection Failed**
   - Check Redis server status
   - Verify socket permissions
   - Check firewall settings

### Debug Information
Enable debug logging to see cache operations:
```php
log_message('debug', 'Cache operation details');
```

## Performance Benefits

### With Cache
- Reduced database queries
- Faster page load times
- Lower server load
- Better user experience

### Without Cache (Localhost)
- Normal development experience
- No performance impact
- Easy debugging
- No Redis dependency

## Security Considerations

1. **Cache Keys**: Use unique, descriptive keys
2. **TTL**: Set appropriate expiration times
3. **Data Serialization**: Sensitive data is serialized
4. **Access Control**: Cache management requires login

## Best Practices

1. **Cache Key Naming**: Use descriptive, unique keys
2. **TTL Management**: Set appropriate expiration times
3. **Cache Invalidation**: Clear cache when data changes
4. **Error Handling**: Graceful fallback when Redis unavailable
5. **Monitoring**: Regular cache statistics review
