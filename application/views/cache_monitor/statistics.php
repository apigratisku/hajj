<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-brown text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar"></i> Cache Statistics
                        </h5>
                        <div>
                            <a href="<?= base_url('cache_monitor') ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Redis Information Section -->
    <?php if (isset($redis_info) && $redis_info): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-server"></i> Redis Server Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Version:</strong></td>
                                    <td><?= $redis_info['version'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Mode:</strong></td>
                                    <td><?= $redis_info['mode'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Operating System:</strong></td>
                                    <td><?= $redis_info['os'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Architecture:</strong></td>
                                    <td><?= $redis_info['arch_bits'] ?> bits</td>
                                </tr>
                                <tr>
                                    <td><strong>Uptime:</strong></td>
                                    <td><?= $redis_info['uptime'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Process ID:</strong></td>
                                    <td><?= $redis_info['process_id'] ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>TCP Port:</strong></td>
                                    <td><?= $redis_info['tcp_port'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Multiplexing API:</strong></td>
                                    <td><?= $redis_info['multiplexing_api'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>GCC Version:</strong></td>
                                    <td><?= $redis_info['gcc_version'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Hz:</strong></td>
                                    <td><?= $redis_info['hz'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Executable:</strong></td>
                                    <td><code><?= $redis_info['executable'] ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>Config File:</strong></td>
                                    <td><code><?= $redis_info['config_file'] ?></code></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hit/Miss Analysis Section -->
    <?php if (isset($hit_miss_analysis) && $hit_miss_analysis): ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bullseye"></i> Hit/Miss Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="metric-card">
                                <div class="metric-value text-success"><?= $hit_miss_analysis['hit_rate_percentage'] ?>%</div>
                                <div class="metric-label">Hit Rate</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-card">
                                <div class="metric-value text-warning"><?= $hit_miss_analysis['miss_rate_percentage'] ?>%</div>
                                <div class="metric-label">Miss Rate</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="metric-card">
                                <div class="metric-value"><?= number_format($hit_miss_analysis['hits']) ?></div>
                                <div class="metric-label">Total Hits</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="metric-card">
                                <div class="metric-value"><?= number_format($hit_miss_analysis['misses']) ?></div>
                                <div class="metric-label">Total Misses</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="metric-card">
                                <div class="metric-value"><?= $hit_miss_analysis['efficiency_score'] ?></div>
                                <div class="metric-label">Efficiency Score</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-memory"></i> Memory Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($memory_analysis) && $memory_analysis): ?>
                        <div class="row">
                            <div class="col-6">
                                <div class="metric-card">
                                    <div class="metric-value"><?= $memory_analysis['used_memory'] ?></div>
                                    <div class="metric-label">Used Memory</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-card">
                                    <div class="metric-value"><?= $memory_analysis['used_memory_peak'] ?></div>
                                    <div class="metric-label">Memory Peak</div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="metric-card">
                                    <div class="metric-value"><?= $memory_analysis['used_memory_rss'] ?></div>
                                    <div class="metric-label">RSS Memory</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-card">
                                    <div class="metric-value"><?= $memory_analysis['mem_fragmentation_ratio'] ?></div>
                                    <div class="metric-label">Fragmentation Ratio</div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="metric-card">
                                    <div class="metric-value"><?= $memory_analysis['mem_allocator'] ?></div>
                                    <div class="metric-label">Memory Allocator</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Memory analysis not available
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Connection Statistics Section -->
    <?php if (isset($connection_stats) && $connection_stats): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-network-wired"></i> Connection Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value"><?= $connection_stats['connected_clients'] ?></div>
                                <div class="metric-label">Connected Clients</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value"><?= $connection_stats['client_longest_output_list'] ?></div>
                                <div class="metric-label">Longest Output List</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value"><?= $connection_stats['client_biggest_input_buf'] ?></div>
                                <div class="metric-label">Biggest Input Buffer</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-card">
                                <div class="metric-value"><?= $connection_stats['blocked_clients'] ?></div>
                                <div class="metric-label">Blocked Clients</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cache Not Available Message -->
    <?php if (!isset($redis_info) || !$redis_info): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Cache Statistics Not Available</h5>
                    <p class="text-muted">
                        <?php if (isset($cache_stats) && $cache_stats['environment'] === 'localhost'): ?>
                            Cache is disabled on localhost environment for development purposes.
                        <?php else: ?>
                            Redis connection is not available or cache is not properly configured.
                        <?php endif; ?>
                    </p>
                    <a href="<?= base_url('cache_monitor') ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.metric-card {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    margin-bottom: 1rem;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #495057;
}

.metric-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.table-borderless td {
    padding: 0.5rem 0;
    border: none;
}

.table-borderless td:first-child {
    font-weight: 600;
    color: #495057;
}

code {
    background: #f1f3f4;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.875rem;
    color: #d63384;
}
</style>
