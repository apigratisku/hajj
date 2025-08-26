<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-brown text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tachometer-alt"></i> Cache Monitor Dashboard
                        </h5>
                        <div>
                            <button class="btn btn-light btn-sm" onclick="refreshDashboard()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="btn btn-light btn-sm" onclick="testCacheOperations()">
                                <i class="fas fa-vial"></i> Test Operations
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Status Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-heartbeat"></i> Cache Health Status
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($health_status)): ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="health-indicator health-<?= $health_status['status'] ?> me-3">
                                        <i class="fas fa-circle"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?= ucfirst($health_status['status']) ?></h5>
                                        <p class="text-muted mb-0"><?= $health_status['message'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-<?= $health_status['severity'] === 'critical' ? 'danger' : ($health_status['severity'] === 'warning' ? 'warning' : 'success') ?> fs-6">
                                    <?= ucfirst($health_status['severity']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if (isset($health_status['checks'])): ?>
                        <div class="row mt-3">
                            <?php foreach ($health_status['checks'] as $check_name => $check): ?>
                            <div class="col-md-4 mb-2">
                                <div class="health-check-item">
                                    <div class="d-flex align-items-center">
                                        <div class="check-indicator check-<?= $check['status'] ?> me-2">
                                            <i class="fas fa-circle"></i>
                                        </div>
                                        <div>
                                            <strong><?= ucfirst($check_name) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= $check['message'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line"></i> Performance Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($performance_metrics)): ?>
                        <?php if (isset($performance_metrics['message'])): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <?= $performance_metrics['message'] ?>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-6">
                                    <div class="metric-card">
                                        <div class="metric-value"><?= $performance_metrics['hit_rate'] ?>%</div>
                                        <div class="metric-label">Hit Rate</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-card">
                                        <div class="metric-value"><?= $performance_metrics['miss_rate'] ?>%</div>
                                        <div class="metric-label">Miss Rate</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="metric-card">
                                        <div class="metric-value"><?= $performance_metrics['avg_response_time'] ?>ms</div>
                                        <div class="metric-label">Avg Response Time</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-card">
                                        <div class="metric-value"><?= number_format($performance_metrics['throughput']) ?></div>
                                        <div class="metric-label">Throughput (req/s)</div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-memory"></i> Usage Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($usage_stats)): ?>
                        <?php if (isset($usage_stats['message'])): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <?= $usage_stats['message'] ?>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-6">
                                    <div class="metric-card">
                                        <div class="metric-value"><?= number_format($usage_stats['total_keys']) ?></div>
                                        <div class="metric-label">Total Keys</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-card">
                                        <div class="metric-value"><?= $usage_stats['memory_usage'] ?></div>
                                        <div class="metric-label">Memory Usage</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="metric-card">
                                        <div class="metric-value"><?= $usage_stats['memory_peak'] ?></div>
                                        <div class="metric-label">Memory Peak</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-card">
                                        <div class="metric-value"><?= $usage_stats['connected_clients'] ?></div>
                                        <div class="metric-label">Connected Clients</div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Operations Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history"></i> Recent Cache Operations
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($recent_operations) && !empty($recent_operations)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Operation</th>
                                        <th>Key</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_operations as $operation): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $operation['operation'] === 'SET' ? 'primary' : 'info' ?>">
                                                <?= $operation['operation'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?= $operation['key'] ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $operation['status'] === 'success' || $operation['status'] === 'hit' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($operation['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $operation['duration'] ?></td>
                                        <td><?= $operation['timestamp'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No recent operations found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="<?= base_url('cache_monitor/statistics') ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chart-bar"></i> Detailed Statistics
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= base_url('cache_monitor/performance') ?>" class="btn btn-outline-success w-100">
                                <i class="fas fa-tachometer-alt"></i> Performance Analysis
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= base_url('cache_monitor/health') ?>" class="btn btn-outline-warning w-100">
                                <i class="fas fa-heartbeat"></i> Health Check
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= base_url('cache_monitor/management') ?>" class="btn btn-outline-info w-100">
                                <i class="fas fa-cog"></i> Cache Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Results Modal -->
<div class="modal fade" id="testResultsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cache Operations Test Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="testResultsContent">
                <!-- Test results will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.health-indicator {
    font-size: 2rem;
}

.health-healthy { color: #28a745; }
.health-warning { color: #ffc107; }
.health-critical { color: #dc3545; }
.health-disabled { color: #6c757d; }

.check-indicator {
    font-size: 0.8rem;
}

.check-healthy { color: #28a745; }
.check-warning { color: #ffc107; }
.check-critical { color: #dc3545; }
.check-skipped { color: #6c757d; }

.metric-card {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
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

.health-check-item {
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.table code {
    background: #f1f3f4;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.875rem;
}
</style>

<script>
function refreshDashboard() {
    location.reload();
}

function testCacheOperations() {
    // Show loading state
    $('#testResultsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Testing cache operations...</p></div>');
    $('#testResultsModal').modal('show');
    
    // Make AJAX request
    $.ajax({
        url: '<?= base_url("cache_monitor/test_operations") ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            let html = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Cache operations test completed successfully!</div>';
            html += '<div class="row">';
            html += '<div class="col-6"><strong>SET Operation:</strong> <span class="badge bg-' + (response.test_results.set ? 'success' : 'danger') + '">' + (response.test_results.set ? 'PASS' : 'FAIL') + '</span></div>';
            html += '<div class="col-6"><strong>GET Operation:</strong> <span class="badge bg-' + (response.test_results.get ? 'success' : 'danger') + '">' + (response.test_results.get ? 'PASS' : 'FAIL') + '</span></div>';
            html += '</div>';
            html += '<div class="row mt-2">';
            html += '<div class="col-6"><strong>DELETE Operation:</strong> <span class="badge bg-' + (response.test_results.delete ? 'success' : 'danger') + '">' + (response.test_results.delete ? 'PASS' : 'FAIL') + '</span></div>';
            html += '<div class="col-6"><strong>EXISTS Operation:</strong> <span class="badge bg-' + (response.test_results.exists ? 'success' : 'danger') + '">' + (response.test_results.exists ? 'PASS' : 'FAIL') + '</span></div>';
            html += '</div>';
            html += '<div class="mt-3"><small class="text-muted">Test completed at: ' + response.timestamp + '</small></div>';
            
            $('#testResultsContent').html(html);
        },
        error: function() {
            $('#testResultsContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Failed to test cache operations. Please try again.</div>');
        }
    });
}

// Auto-refresh every 30 seconds
setInterval(function() {
    // Only refresh if modal is not open
    if (!$('#testResultsModal').hasClass('show')) {
        // You can implement partial refresh here instead of full page reload
        // For now, we'll just update the timestamp
        console.log('Auto-refresh check - ' + new Date().toLocaleTimeString());
    }
}, 30000);
</script>
