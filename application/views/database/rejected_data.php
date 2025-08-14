<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-times-circle"></i> Data Import Ditolak</h5>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('database/download_rejected_data') ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-download"></i> Download Excel
                            </a>
                            <a href="<?= base_url('database/clear_rejected_data') ?>" class="btn btn-warning btn-sm" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus semua data yang ditolak?')">
                                <i class="fas fa-trash"></i> Hapus Semua
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show persistent-error" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div class="flex-grow-1">
                                    <strong>Error:</strong><br>
                                    <?= $this->session->flashdata('error') ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('database/download_rejected_data') ?>" class="btn btn-danger">
                                    <i class="fas fa-download"></i> Download Data Ditolak
                                </a>
                                <a href="<?= base_url('database/download_failed_import') ?>" class="btn btn-info">
                                    <i class="fas fa-file-excel"></i> Download Data Gagal Import
                                </a>
                                <a href="<?= base_url('database/clear_rejected_data') ?>" class="btn btn-warning" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus semua data yang ditolak?')">
                                    <i class="fas fa-trash"></i> Hapus Semua Data
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form method="get" action="<?= base_url('database/rejected_data') ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="nama" class="form-label">Nama Peserta</label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?= $this->input->get('nama') ?>" placeholder="Cari nama...">
                            </div>
                            <div class="col-md-3">
                                <label for="nomor_paspor" class="form-label">Nomor Paspor</label>
                                <input type="text" class="form-control" id="nomor_paspor" name="nomor_paspor" 
                                       value="<?= $this->input->get('nomor_paspor') ?>" placeholder="Cari paspor...">
                            </div>
                            <div class="col-md-2">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">Semua</option>
                                    <option value="L" <?= $this->input->get('gender') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= $this->input->get('gender') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Semua</option>
                                    <option value="0" <?= $this->input->get('status') == '0' ? 'selected' : '' ?>>On Target</option>
                                    <option value="1" <?= $this->input->get('status') == '1' ? 'selected' : '' ?>>Already</option>
                                    <option value="2" <?= $this->input->get('status') == '2' ? 'selected' : '' ?>>Done</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="<?= base_url('database/rejected_data') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php if(empty($rejected_data)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>Tidak ada data yang ditolak</h5>
                            <p class="text-muted">Semua data import berhasil diproses</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Peserta</th>
                                        <th>Nomor Paspor</th>
                                        <th>No Visa</th>
                                        <th>Gender</th>
                                        <th>Status</th>
                                        <th>Alasan Penolakan</th>
                                        <th>Baris Excel</th>
                                        <th>Tanggal Ditolak</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php foreach($rejected_data as $data): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($data->nama) ?></strong>
                                                <?php if($data->email): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($data->email) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($data->nomor_paspor) ?></code>
                                            </td>
                                            <td><?= htmlspecialchars($data->no_visa ?: '-') ?></td>
                                            <td>
                                                <?php if($data->gender == 'L'): ?>
                                                    <span class="badge bg-primary">Laki-laki</span>
                                                <?php elseif($data->gender == 'P'): ?>
                                                    <span class="badge bg-danger">Perempuan</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($data->status == 0): ?>
                                                    <span class="badge bg-warning">On Target</span>
                                                <?php elseif($data->status == 1): ?>
                                                    <span class="badge bg-info">Already</span>
                                                <?php elseif($data->status == 2): ?>
                                                    <span class="badge bg-success">Done</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <?= htmlspecialchars($data->reject_reason) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">Baris <?= $data->row_number ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($data->created_at)) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" data-bs-target="#detailModal<?= $data->id ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="<?= base_url('database/delete_rejected/' . $data->id) ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Detail Modal -->
                                        <div class="modal fade" id="detailModal<?= $data->id ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Data Ditolak</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <table class="table table-borderless">
                                                                    <tr>
                                                                        <td><strong>Nama Peserta:</strong></td>
                                                                        <td><?= htmlspecialchars($data->nama) ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Nomor Paspor:</strong></td>
                                                                        <td><?= htmlspecialchars($data->nomor_paspor) ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>No Visa:</strong></td>
                                                                        <td><?= htmlspecialchars($data->no_visa ?: '-') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Tanggal Lahir:</strong></td>
                                                                        <td><?= htmlspecialchars($data->tgl_lahir ?: '-') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>No. HP:</strong></td>
                                                                        <td><?= htmlspecialchars($data->nomor_hp ?: '-') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Email:</strong></td>
                                                                        <td><?= htmlspecialchars($data->email ?: '-') ?></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <table class="table table-borderless">
                                                                    <tr>
                                                                        <td><strong>Gender:</strong></td>
                                                                        <td>
                                                                            <?php if($data->gender == 'L'): ?>
                                                                                <span class="badge bg-primary">Laki-laki</span>
                                                                            <?php elseif($data->gender == 'P'): ?>
                                                                                <span class="badge bg-danger">Perempuan</span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-secondary">-</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Status:</strong></td>
                                                                        <td>
                                                                            <?php if($data->status == 0): ?>
                                                                                <span class="badge bg-warning">On Target</span>
                                                                            <?php elseif($data->status == 1): ?>
                                                                                <span class="badge bg-info">Already</span>
                                                                            <?php elseif($data->status == 2): ?>
                                                                                <span class="badge bg-success">Done</span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-secondary">-</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Tanggal:</strong></td>
                                                                        <td><?= htmlspecialchars($data->tanggal ?: '-') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Jam:</strong></td>
                                                                        <td><?= htmlspecialchars($data->jam ?: '-') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Flag Dokumen:</strong></td>
                                                                        <td><?= htmlspecialchars($data->flag_doc ?: '-') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Baris Excel:</strong></td>
                                                                        <td><span class="badge bg-secondary">Baris <?= $data->row_number ?></span></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="alert alert-danger">
                                                            <strong>Alasan Penolakan:</strong><br>
                                                            <?= htmlspecialchars($data->reject_reason) ?>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if(isset($pagination)): ?>
                            <div class="d-flex justify-content-center mt-4">
                                <?= $pagination ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-danger {
    background-color: #dc3545 !important;
}

.table th {
    font-size: 0.875rem;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    font-size: 0.75rem;
}

/* Persistent Error Alert Styles */
.persistent-error {
    border-left: 5px solid #dc3545 !important;
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
    color: #721c24 !important;
    animation: none !important;
    transition: none !important;
}

.persistent-error .btn-close {
    color: #721c24 !important;
    opacity: 0.8;
}

.persistent-error .btn-close:hover {
    opacity: 1;
}

/* Disable auto-dismiss for error alerts */
.persistent-error.alert-dismissible {
    padding-right: 1rem;
}

/* Ensure error alert stays visible */
.persistent-error.show {
    display: block !important;
    opacity: 1 !important;
}

/* Custom animation for error alert */
@keyframes errorPulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.persistent-error {
    animation: errorPulse 2s infinite;
}
</style>

<script>
// Disable auto-dismiss for error alerts
document.addEventListener('DOMContentLoaded', function() {
    // Find all error alerts and prevent auto-dismiss
    const errorAlerts = document.querySelectorAll('.persistent-error');
    errorAlerts.forEach(function(alert) {
        // Remove any auto-dismiss functionality
        alert.style.animation = 'none';
        alert.style.transition = 'none';
        
        // Ensure the alert stays visible
        alert.classList.add('show');
        alert.style.display = 'block';
        alert.style.opacity = '1';
    });
    
    // Override Bootstrap's auto-dismiss if any
    if (typeof bootstrap !== 'undefined') {
        const alertList = document.querySelectorAll('.persistent-error');
        alertList.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            // Disable auto-dismiss
            bsAlert._config.delay = 0;
            bsAlert._config.autohide = false;
        });
    }
});
</script>
