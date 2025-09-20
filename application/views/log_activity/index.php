<!-- Content Body -->
<div class="content-body">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-history text-primary me-2"></i>
                        Log Aktifitas User
                    </h4>
                    <p class="text-muted mb-0">Monitoring aktivitas user pada sistem (kecuali user adhit)</p>
                </div>
                <div>
                    <a href="<?= base_url('log_activity/export_excel') . '?' . http_build_query($filters) ?>" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-gradient rounded-circle p-3">
                                        <i class="fas fa-list text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Total Log</div>
                                    <div class="h4 mb-0 text-primary"><?= number_format($total_logs) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-gradient rounded-circle p-3">
                                        <i class="fas fa-users text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Total User</div>
                                    <div class="h4 mb-0 text-success"><?= count($unique_users) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info bg-gradient rounded-circle p-3">
                                        <i class="fas fa-user-friends text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Total Peserta</div>
                                    <div class="h4 mb-0 text-info"><?= count(array_unique(array_column($logs, 'id_peserta'))) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning bg-gradient rounded-circle p-3">
                                        <i class="fas fa-calendar-day text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Hari Ini</div>
                                    <div class="h4 mb-0 text-warning"><?= count(array_filter($logs, function($log) { return $log->tanggal === date('Y-m-d'); })) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filter Log Aktifitas
                    </h5>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('log_activity') ?>" class="filter-form">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="user_operator" class="form-label">
                                    <i class="fas fa-user"></i> User Operator
                                </label>
                                <select name="user_operator" id="user_operator" class="form-select">
                                    <option value="">Semua User</option>
                                    <?php foreach ($unique_users as $user): ?>
                                        <option value="<?= $user->user_operator ?>" <?= (isset($filters['user_operator']) && $filters['user_operator'] == $user->user_operator) ? 'selected' : '' ?>>
                                            <?= $user->user_operator ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="tanggal_dari" class="form-label">
                                    <i class="fas fa-calendar"></i> Tanggal Dari
                                </label>
                                <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="<?= isset($filters['tanggal_dari']) ? $filters['tanggal_dari'] : '' ?>">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="tanggal_sampai" class="form-label">
                                    <i class="fas fa-calendar"></i> Tanggal Sampai
                                </label>
                                <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="<?= isset($filters['tanggal_sampai']) ? $filters['tanggal_sampai'] : '' ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="aktivitas" class="form-label">
                                    <i class="fas fa-search"></i> Cari Aktivitas
                                </label>
                                <input type="text" name="aktivitas" id="aktivitas" class="form-control" placeholder="Kata kunci aktivitas..." value="<?= isset($filters['aktivitas']) ? $filters['aktivitas'] : '' ?>">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="<?= base_url('log_activity') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-alt me-2"></i>
                        Daftar Log Aktifitas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Tidak ada log aktivitas yang ditemukan</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>ID Log</th>
                                        <th>ID Peserta</th>
                                        <th>Nama Peserta</th>
                                        <th>User Operator</th>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Aktivitas</th>
                                        <th>Created At</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $index => $log): ?>
                                        <tr>
                                            <td><?= $index + 1 + (($current_page - 1) * 50) ?></td>
                                            <td><span class="badge bg-secondary"><?= $log->id_log ?></span></td>
                                            <td>
                                                <a href="<?= base_url('database/index?nama_travel=&flag_doc=&nama=ABDUL+HARIS+WIDODO&nomor_paspor=&no_visa=&tanggaljam=&gender=&status=&tanggal_pengerjaan=&status_jadwal=&startDate=&endDate=' . $log->id_peserta) ?>" class="text-decoration-none">
                                                    <span class="badge bg-info"><?= $log->id_peserta ?></span>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($log->nama_peserta): ?>
                                                    <a href="<?= base_url('log_activity/view_peserta/' . $log->id_peserta) ?>" class="text-decoration-none">
                                                        <?= $log->nama_peserta ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('log_activity/view_user/' . $log->user_operator) ?>" class="text-decoration-none">
                                                    <span class="badge bg-primary"><?= $log->user_operator ?></span>
                                                </a>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($log->tanggal)) ?></td>
                                            <td><?= date('H:i:s', strtotime($log->jam)) ?></td>
                                            <td>
                                                <div class="activity-text" style="max-width: 300px;">
                                                    <?= htmlspecialchars($log->aktivitas) ?>
                                                </div>
                                            </td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= base_url('log_activity/view_peserta/' . $log->id_peserta) ?>" class="btn btn-outline-info" title="Lihat Log Peserta">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                    <a href="<?= base_url('log_activity/view_user/' . $log->user_operator) ?>" class="btn btn-outline-primary" title="Lihat Log User">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Log pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= base_url('log_activity?' . http_build_query(array_merge($filters, ['page' => $current_page - 1]))) ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= base_url('log_activity?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= base_url('log_activity?' . http_build_query(array_merge($filters, ['page' => $current_page + 1]))) ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Section -->
            <?php if (!empty($top_users) || !empty($activity_summary)): ?>
                <div class="row mt-4">
                    <!-- Top Users -->
                    <?php if (!empty($top_users)): ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-trophy me-2"></i>
                                        Top 10 User Paling Aktif
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Total Aktivitas</th>
                                                    <th>Peserta Unik</th>
                                                    <th>Aktivitas Terakhir</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_users as $user): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="<?= base_url('log_activity/view_user/' . $user->user_operator) ?>" class="text-decoration-none">
                                                                <span class="badge bg-primary"><?= $user->user_operator ?></span>
                                                            </a>
                                                        </td>
                                                        <td><span class="badge bg-success"><?= $user->total_activities ?></span></td>
                                                        <td><span class="badge bg-info"><?= $user->unique_peserta ?></span></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($user->last_activity)) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Activity Summary -->
                    <?php if (!empty($activity_summary)): ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Ringkasan Aktivitas
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Aktivitas</th>
                                                    <th>Total</th>
                                                    <th>User Unik</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($activity_summary, 0, 10) as $activity): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="activity-text" style="max-width: 200px; font-size: 0.85rem;">
                                                                <?= htmlspecialchars($activity->aktivitas) ?>
                                                            </div>
                                                        </td>
                                                        <td><span class="badge bg-success"><?= $activity->total_count ?></span></td>
                                                        <td><span class="badge bg-info"><?= $activity->unique_users ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.activity-text {
    word-wrap: break-word;
    white-space: pre-wrap;
    font-size: 0.9rem;
}

.card {
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.bg-gradient {
    background: linear-gradient(45deg, var(--bs-primary), var(--bs-primary-dark));
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.pagination .page-link {
    color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.pagination .page-link:hover {
    color: var(--bs-primary);
    background-color: rgba(0, 123, 255, 0.1);
    border-color: var(--bs-primary);
}
</style>
