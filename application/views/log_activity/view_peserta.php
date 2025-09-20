<!-- Content Body -->
<div class="content-body">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-user text-primary me-2"></i>
                        Log Aktifitas Peserta
                    </h4>
                    <p class="text-muted mb-0">Riwayat aktivitas untuk peserta: <strong><?= $peserta->nama_peserta ?></strong></p>
                </div>
                <div>
                    <a href="<?= base_url('log_activity') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <a href="<?= base_url('database/view/' . $peserta->id) ?>" class="btn btn-info">
                        <i class="fas fa-eye me-1"></i>Lihat Peserta
                    </a>
                </div>
            </div>

            <!-- Participant Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Informasi Peserta
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>ID Peserta:</strong><br>
                            <span class="badge bg-primary"><?= $peserta->id ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Nama Peserta:</strong><br>
                            <?= $peserta->nama_peserta ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Nomor Peserta:</strong><br>
                            <?= $peserta->nomor_peserta ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Log:</strong><br>
                            <span class="badge bg-success"><?= count($logs) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Riwayat Aktivitas
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Tidak ada log aktivitas untuk peserta ini</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>ID Log</th>
                                        <th>User Operator</th>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Aktivitas</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $index => $log): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><span class="badge bg-secondary"><?= $log->id_log ?></span></td>
                                            <td>
                                                <a href="<?= base_url('log_activity/view_user/' . $log->user_operator) ?>" class="text-decoration-none">
                                                    <span class="badge bg-primary"><?= $log->user_operator ?></span>
                                                </a>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($log->tanggal)) ?></td>
                                            <td><?= date('H:i:s', strtotime($log->jam)) ?></td>
                                            <td>
                                                <div class="activity-text" style="max-width: 400px;">
                                                    <?= htmlspecialchars($log->aktivitas) ?>
                                                </div>
                                            </td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}
</style>
