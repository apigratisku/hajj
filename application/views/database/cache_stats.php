<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-brown text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-database"></i> Cache Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($cache_stats)): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-info-circle"></i> Environment Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Environment:</strong></td>
                                                <td>
                                                    <span class="badge bg-<?= $cache_stats['environment'] === 'localhost' ? 'warning' : 'success' ?>">
                                                        <?= ucfirst($cache_stats['environment']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Redis Connected:</strong></td>
                                                <td>
                                                    <span class="badge bg-<?= $cache_stats['redis_connected'] ? 'success' : 'danger' ?>">
                                                        <?= $cache_stats['redis_connected'] ? 'Yes' : 'No' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php if (isset($cache_stats['message'])): ?>
                                            <tr>
                                                <td><strong>Message:</strong></td>
                                                <td><?= $cache_stats['message'] ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($cache_stats['environment'] === 'hosting' && $cache_stats['redis_connected']): ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-chart-bar"></i> Redis Statistics
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Redis Version:</strong></td>
                                                <td><?= $cache_stats['redis_version'] ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Connected Clients:</strong></td>
                                                <td><?= $cache_stats['connected_clients'] ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Used Memory:</strong></td>
                                                <td><?= $cache_stats['used_memory_human'] ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Cache Hits:</strong></td>
                                                <td><?= number_format($cache_stats['keyspace_hits']) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Cache Misses:</strong></td>
                                                <td><?= number_format($cache_stats['keyspace_misses']) ?></td>
                                            </tr>
                                            <?php 
                                            $hit_rate = 0;
                                            if (($cache_stats['keyspace_hits'] + $cache_stats['keyspace_misses']) > 0) {
                                                $hit_rate = ($cache_stats['keyspace_hits'] / ($cache_stats['keyspace_hits'] + $cache_stats['keyspace_misses'])) * 100;
                                            }
                                            ?>
                                            <tr>
                                                <td><strong>Hit Rate:</strong></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: <?= $hit_rate ?>%" 
                                                             aria-valuenow="<?= $hit_rate ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?= number_format($hit_rate, 1) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-tools"></i> Cache Management
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Cache Operations</h6>
                                                <p class="text-muted">
                                                    <?php if ($cache_stats['environment'] === 'localhost'): ?>
                                                        Cache operations are disabled on localhost environment for development purposes.
                                                    <?php else: ?>
                                                        Cache operations are available for production environment.
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <?php if ($cache_stats['environment'] === 'hosting' && $cache_stats['redis_connected']): ?>
                                                <a href="<?= base_url('database/clear_cache') ?>" 
                                                   class="btn btn-danger"
                                                   onclick="return confirm('Are you sure you want to clear all cache?')">
                                                    <i class="fas fa-trash"></i> Clear All Cache
                                                </a>
                                                <?php else: ?>
                                                <button class="btn btn-secondary" disabled>
                                                    <i class="fas fa-trash"></i> Clear All Cache
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Cache statistics not available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: none;
}

.progress {
    border-radius: 10px;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.table-borderless td {
    padding: 0.5rem 0;
    border: none;
}
</style>
