<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Pengaturan Sistem</h5>
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

                    <!-- Database Backup Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-database"></i> Backup Database Lokal</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        Membuat backup database dan menyimpannya di server lokal untuk didownload.
                                    </p>
                                    <button type="button" class="btn btn-primary btn-backup-local" id="btnBackupLocal">
                                        <i class="fas fa-download"></i> Backup & Download
                                    </button>
                                    
                                    <!-- Debug info -->
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            <a href="<?= base_url('test_mysqldump') ?>" target="_blank" class="text-decoration-none">
                                                Test konfigurasi mysqldump
                                            </a>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-server"></i> 
                                            <strong>cPanel Ready:</strong> Sistem backup sudah dioptimalkan untuk cPanel hosting
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            <strong>Troubleshooting:</strong> Jika terjadi error "Access denied", periksa privilege user database
                                        </small>
                                    </div>
                                    
                                    <!-- Debug Information -->
                                    <div class="mt-3 p-2 bg-light border rounded">
                                        <small class="text-muted">
                                            <strong>Debug Info:</strong><br>
                                            <i class="fas fa-code"></i> PHP: <?= $debug_info['php_version'] ?><br>
                                            <i class="fas fa-terminal"></i> Exec: <?= $debug_info['exec_available'] ? 'Available' : 'Disabled' ?><br>
                                            <i class="fas fa-database"></i> DB: <?= $debug_info['db_connection'] ? 'Connected' : 'Error: ' . $debug_info['db_error'] ?><br>
                                            <i class="fas fa-folder"></i> Backup Dir: <?= $debug_info['backup_dir_exists'] ? 'Exists' : 'Missing' ?> 
                                            (<?= $debug_info['backup_dir_writable'] ? 'Writable' : 'Not Writable' ?>)<br>
                                            <?php if ($debug_info['exec_available']): ?>
                                            <i class="fas fa-search"></i> mysqldump: <?= $debug_info['mysqldump_path'] ? $debug_info['mysqldump_path'] : 'Not Found' ?><br>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    
                                    <!-- Loading indicator -->
                                    <div class="loading-indicator mt-3" id="loadingLocal" style="display: none;">
                                        <div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <span class="text-primary">Sedang membuat backup database...</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Result area -->
                                    <div class="backup-result mt-3" id="backupResultLocal" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-upload"></i> Backup ke Server FTP</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        Membuat backup database dan menguploadnya ke server FTP eksternal.
                                    </p>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ftpModal">
                                        <i class="fas fa-upload"></i> Backup ke FTP
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Files List -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-folder-open"></i> File Backup Tersedia</h6>
                                    <button type="button" class="btn btn-light btn-sm" id="btnRefreshBackups">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="backupFilesTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Nama File</th>
                                                    <th>Ukuran</th>
                                                    <th>Tanggal Dibuat</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="backupFilesList">
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        <i class="fas fa-spinner fa-spin"></i> Memuat data backup...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FTP Configuration Modal -->
<div class="modal fade" id="ftpModal" tabindex="-1" aria-labelledby="ftpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="ftpModalLabel">
                    <i class="fas fa-upload"></i> Konfigurasi FTP Server
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ftpForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ftp_host" class="form-label">FTP Host <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ftp_host" name="ftp_host" required 
                                       placeholder="ftp.example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ftp_port" class="form-label">FTP Port</label>
                                <input type="number" class="form-control" id="ftp_port" name="ftp_port" 
                                       value="21" placeholder="21">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ftp_username" class="form-label">FTP Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ftp_username" name="ftp_username" required 
                                       placeholder="username">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ftp_password" class="form-label">FTP Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="ftp_password" name="ftp_password" required 
                                       placeholder="password">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ftp_path" class="form-label">FTP Path</label>
                        <input type="text" class="form-control" id="ftp_path" name="ftp_path" 
                               value="/" placeholder="/backup/">
                        <div class="form-text">Path di server FTP tempat file backup akan disimpan</div>
                    </div>
                    
                    <!-- Loading indicator -->
                    <div class="loading-indicator mt-3" id="loadingFtp" style="display: none;">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm text-success me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="text-success">Sedang mengupload backup ke server FTP...</span>
                        </div>
                    </div>
                    
                    <!-- Result area -->
                    <div class="backup-result mt-3" id="backupResultFtp" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnBackupFtp">
                        <i class="fas fa-upload"></i> Backup ke FTP
                    </button>
                </div>
            </form>
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

.loading-indicator {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}

.backup-result {
    padding: 15px;
    border-radius: 5px;
    margin-top: 15px;
}

.backup-result.success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.backup-result.error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.table th {
    font-size: 0.875rem;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.btn-action {
    padding: 0.25rem 0.5rem;
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
document.addEventListener('DOMContentLoaded', function() {
    // Load backup files on page load
    loadBackupFiles();
    
    // Backup to local
    document.getElementById('btnBackupLocal').addEventListener('click', function() {
        backupDatabaseLocal();
    });
    
    // Backup to FTP
    document.getElementById('ftpForm').addEventListener('submit', function(e) {
        e.preventDefault();
        backupDatabaseFtp();
    });
    
    // Refresh backup files
    document.getElementById('btnRefreshBackups').addEventListener('click', function() {
        loadBackupFiles();
    });
    
    // Delete backup file
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-backup')) {
            const filename = e.target.closest('.btn-delete-backup').dataset.filename;
            if (confirm('Apakah Anda yakin ingin menghapus file backup ini?')) {
                deleteBackupFile(filename);
            }
        }
    });
});

function backupDatabaseLocal() {
    const btn = document.getElementById('btnBackupLocal');
    const loading = document.getElementById('loadingLocal');
    const result = document.getElementById('backupResultLocal');
    
    // Show loading
    btn.disabled = true;
    loading.style.display = 'block';
    result.style.display = 'none';
    
    fetch('<?= base_url('settings/backup_database') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 300000 // 5 minutes timeout
    })
    .then(response => response.json())
    .then(response => {
        loading.style.display = 'none';
        btn.disabled = false;
        
        if (response.status === 'success') {
            result.className = 'backup-result mt-3 success';
            result.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>
                        <strong>Berhasil!</strong> ${response.message}<br>
                        <small>File: ${response.filename} (${response.file_size})</small>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="${response.download_url}" class="btn btn-sm btn-primary">
                        <i class="fas fa-download"></i> Download File
                    </a>
                </div>
            `;
            result.style.display = 'block';
            
            // Refresh backup files list
            loadBackupFiles();
            
            // Show flash message
            showFlashMessage('success', response.message);
        } else {
            result.className = 'backup-result mt-3 error';
            result.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Error!</strong> ${response.message}
                    </div>
                </div>
            `;
            result.style.display = 'block';
            
            // Show flash message
            showFlashMessage('error', response.message);
        }
    })
    .catch(error => {
        loading.style.display = 'none';
        btn.disabled = false;
        
        let errorMessage = 'Terjadi kesalahan saat melakukan backup';
        
        if (error.name === 'TypeError') {
            errorMessage = 'Network Error: Periksa koneksi internet';
        }
        
        result.className = 'backup-result mt-3 error';
        result.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>Error!</strong> ${errorMessage}
                </div>
            </div>
        `;
        result.style.display = 'block';
        
        // Show flash message
        showFlashMessage('error', errorMessage);
    });
}

function backupDatabaseFtp() {
    const btn = document.getElementById('btnBackupFtp');
    const loading = document.getElementById('loadingFtp');
    const result = document.getElementById('backupResultFtp');
    const form = document.getElementById('ftpForm');
    
    // Show loading
    btn.disabled = true;
    loading.style.display = 'block';
    result.style.display = 'none';
    
    // Get form data
    const formData = new FormData(form);
    
    fetch('<?= base_url('settings/backup_database_ftp') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData,
        timeout: 300000 // 5 minutes timeout
    })
    .then(response => response.json())
    .then(response => {
        loading.style.display = 'none';
        btn.disabled = false;
        
        if (response.status === 'success') {
            result.className = 'backup-result mt-3 success';
            result.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>
                        <strong>Berhasil!</strong> ${response.message}<br>
                        <small>File: ${response.filename} | Server: ${response.ftp_host}</small>
                    </div>
                </div>
            `;
            result.style.display = 'block';
            
            // Show flash message
            showFlashMessage('success', response.message);
            
            // Close modal after 2 seconds
            setTimeout(function() {
                const modal = bootstrap.Modal.getInstance(document.getElementById('ftpModal'));
                if (modal) {
                    modal.hide();
                }
            }, 2000);
        } else {
            result.className = 'backup-result mt-3 error';
            result.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Error!</strong> ${response.message}
                    </div>
                </div>
            `;
            result.style.display = 'block';
            
            // Show flash message
            showFlashMessage('error', response.message);
        }
    })
    .catch(error => {
        loading.style.display = 'none';
        btn.disabled = false;
        
        let errorMessage = 'Terjadi kesalahan saat mengupload ke FTP';
        
        if (error.name === 'TypeError') {
            errorMessage = 'Network Error: Periksa koneksi internet';
        }
        
        result.className = 'backup-result mt-3 error';
        result.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>Error!</strong> ${errorMessage}
                </div>
            </div>
        `;
        result.style.display = 'block';
        
        // Show flash message
        showFlashMessage('error', errorMessage);
    });
}

function loadBackupFiles() {
    const tbody = document.getElementById('backupFilesList');
    
    tbody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center text-muted">
                <i class="fas fa-spinner fa-spin"></i> Memuat data backup...
            </td>
        </tr>
    `;
    
    fetch('<?= base_url('settings/get_backup_files') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(files => {
        if (files.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        <i class="fas fa-folder-open"></i> Tidak ada file backup tersedia
                    </td>
                </tr>
            `;
        } else {
            let html = '';
            files.forEach(function(file) {
                html += `
                    <tr>
                        <td>
                            <i class="fas fa-file-archive text-primary me-2"></i>
                            ${file.filename}
                        </td>
                        <td><span class="badge bg-info">${file.size}</span></td>
                        <td>${file.date}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="${file.download_url}" class="btn btn-sm btn-primary btn-action" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger btn-action btn-delete-backup" 
                                        data-filename="${file.filename}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }
    })
    .catch(error => {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle"></i> Gagal memuat data backup
                </td>
            </tr>
        `;
    });
}

function deleteBackupFile(filename) {
    fetch('<?= base_url('settings/delete_backup/') ?>' + filename, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(response => {
        if (response.status === 'success') {
            showFlashMessage('success', response.message);
            loadBackupFiles();
        } else {
            showFlashMessage('error', response.message);
        }
    })
    .catch(error => {
        showFlashMessage('error', 'Terjadi kesalahan saat menghapus file backup');
    });
}

function showFlashMessage(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
    
    const flashHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="${icon} me-2"></i>
                <div class="flex-grow-1">${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert flash message at the top of card-body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', flashHtml);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
}

// Reset modal when closed
document.addEventListener('DOMContentLoaded', function() {
    const ftpModal = document.getElementById('ftpModal');
    if (ftpModal) {
        ftpModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('ftpForm').reset();
            document.getElementById('loadingFtp').style.display = 'none';
            document.getElementById('backupResultFtp').style.display = 'none';
            document.getElementById('btnBackupFtp').disabled = false;
        });
    }
});
</script>
