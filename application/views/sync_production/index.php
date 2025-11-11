<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-brown text-white d-flex align-items-center">
                    <h5 class="mb-0"><i class="fas fa-sync-alt me-2"></i> Sync Production</h5>
                </div>
                <?php $sync_error_details = $this->session->flashdata('sync_error_details'); ?>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('success'); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('error'); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Perhatian!</h6>
                            <p class="mb-1 small">
                                Proses ini akan menggantikan seluruh data di database lokal dengan data terbaru dari database production.
                                Pastikan Anda telah melakukan backup sebelum menjalankan sinkronisasi.
                            </p>
                            <p class="mb-0 small text-muted">Tabel `ci_sessions` tidak disinkronisasi untuk menghindari konflik sesi login.</p>
                        </div>
                    </div>

                    <?= form_open('syncproduction/run', ['id' => 'formSyncProduction', 'class' => 'mb-4']); ?>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirmSync();">
                                <i class="fas fa-cloud-download-alt me-2"></i> Mulai Sinkronisasi
                            </button>
                            <span class="text-muted small">
                                Proses sinkronisasi dapat memerlukan beberapa menit tergantung pada ukuran data.
                            </span>
                        </div>
                    <?= form_close(); ?>

                    <?php if (!empty($sync_summary) && is_array($sync_summary)): ?>
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i> Ringkasan Sinkronisasi Terakhir</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tabel</th>
                                                <th class="text-end">Jumlah Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sync_summary as $summary): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($summary['table'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?= number_format($summary['rows']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmSync() {
    return confirm('Apakah Anda yakin ingin menjalankan sinkronisasi dari database production? Proses ini akan menggantikan seluruh data di database lokal.');
}
</script>

<?php if (!empty($sync_error_details)): ?>
<script>
console.group('Sync Production Debug');
console.log('Detail kegagalan sinkronisasi:', <?= json_encode($sync_error_details); ?>);
console.groupEnd();
</script>
<?php endif; ?>

