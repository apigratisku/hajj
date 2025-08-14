<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0"><i class="fas fa-file-import"></i> Import Data Peserta</h5>
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
                                    <strong>Error Import Data:</strong><br>
                                    <?= $this->session->flashdata('error') ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($this->session->flashdata('rejected_count')): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <h6><i class="fas fa-exclamation-triangle"></i> Data Import Ditolak</h6>
                            <p class="mb-2">Sebanyak <strong><?= $this->session->flashdata('rejected_count') ?></strong> data ditolak saat proses import.</p>
                            <div class="d-flex gap-2">
                               
                                <a href="<?= base_url('database/download_rejected_data') ?>" class="btn btn-danger btn-sm">
                                    <i class="fas fa-download"></i> Download Data Ditolak
                                </a>
                               
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-8">
                            <form action="<?= base_url('database/process_import') ?>" method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="excel_file" class="form-label">File Excel (.xls atau .xlsx)</label>
                                    <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xls,.xlsx" required>
                                    <div class="form-text">Pilih file Excel yang berisi data peserta</div>
                                </div>
                                <!--
                                <div class="mb-3">
                                    <label for="flag_doc" class="form-label">Flag Dokumen</label>
                                    <input type="text" class="form-control" id="flag_doc" name="flag_doc" placeholder="Contoh: Batch-001, Import-Jan2025" required>
                                    <div class="form-text">Masukkan identifier untuk file yang diupload (akan digunakan untuk filtering data)</div>
                                </div>
                                -->
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-brown">
                                        <i class="fas fa-upload"></i> Import Data
                                    </button>
                                    <a href="<?= base_url('database') ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Format Excel</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small mb-2">File Excel harus memiliki format kolom sebagai berikut:</p>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Kolom</th>
                                                    <th>Field</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                               
                                                <tr>
                                                    <td>A</td>
                                                    <td>Nama Peserta</td>
                                                    <td><strong>Wajib</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>B</td>
                                                    <td>Nomor Paspor</td>
                                                    <td><strong>Wajib</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>C</td>
                                                    <td>No Visa</td>
                                                    <td>Opsional</td>
                                                </tr>
                                                <tr>
                                                    <td>D</td>
                                                    <td>Tanggal Lahir</td>
                                                    <td>Opsional</td>
                                                </tr>
                                                <tr>
                                                    <td>E</td>
                                                    <td>Password</td>
                                                    <td><strong>Wajib</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>F</td>
                                                    <td>No. HP</td>
                                                    <td>Opsional</td>
                                                </tr>
                                                <tr>
                                                    <td>G</td>
                                                    <td>Email</td>
                                                    <td>Opsional</td>
                                                </tr>
                                                <tr>
                                                    <td>H</td>
                                                    <td>Gender</td>
                                                    <td>L/P atau Laki-laki/Perempuan</td>
                                                </tr>
                                                <tr>
                                                    <td>I</td>
                                                    <td>Status</td>
                                                    <td>On Target/Already/Done</td>
                                                </tr>
                                                <tr>
                                                    <td>J</td>
                                                    <td>Tanggal</td>
                                                    <td>YYYY/MM/DD</td>
                                                </tr>
                                                <tr>
                                                    <td>K</td>
                                                    <td>Jam</td>
                                                    <td>HH:MM</td>
                                                </tr>
                                                <tr>
                                                    <td>L</td>
                                                    <td>Flag Dokumen</td>
                                                    <td>Identifier untuk file (opsional)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <h6>Catatan:</h6>
                                        <ul class="small mb-0">
                                            <li>Baris pertama adalah header (akan diabaikan)</li>
                                            <li>Data dimulai dari baris kedua</li>
                                            <li>Jika agent belum ada, akan dibuat otomatis</li>
                                            <li>Nomor paspor harus unik</li>
                                            <li>Format tanggal: DD/MM/YYYY atau Excel date</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6><i class="fas fa-download"></i> Download Template Excel</h6>
                        <p class="text-muted">Download template Excel untuk memudahkan pengisian data:</p>
                        <a href="<?= base_url('database/download_template') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Download Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-brown {
    background-color: #8B4513 !important;
}

.btn-brown {
    background-color: #8B4513 !important;
    border-color: #8B4513 !important;
    color: white !important;
}

.btn-brown:hover {
    background-color: #A0522D !important;
    border-color: #A0522D !important;
    color: white !important;
}

.card-header {
    background-color: #8B4513 !important;
    color: white !important;
}

.table th {
    font-size: 0.875rem;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
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
