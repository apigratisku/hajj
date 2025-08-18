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
                                    <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="testBackupConnection()">
                                        <i class="fas fa-vial"></i> Test Connection
                                    </button>
                                    
                                 
                                    
                                    <!-- Debug Information -->
                                    <div class="mt-3 p-2 bg-light border rounded" 
                                         data-php-version="<?= $debug_info['php_version'] ?>"
                                         data-exec-available="<?= $debug_info['exec_available'] ? 'true' : 'false' ?>"
                                         data-db-connection="<?= $debug_info['db_connection'] ? 'true' : 'false' ?>"
                                         data-backup-dir="<?= $debug_info['backup_dir_exists'] ? 'exists' : 'missing' ?>"
                                         data-session-user="<?= $this->session->userdata('user_id') ?>"
                                         data-session-role="<?= $this->session->userdata('role') ?>"
                                         data-session-logged="<?= $this->session->userdata('logged_in') ? 'true' : 'false' ?>">
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
                                            <i class="fas fa-clock"></i> Max Exec Time: <?= ini_get('max_execution_time') ?>s<br>
                                            <i class="fas fa-memory"></i> Memory Limit: <?= ini_get('memory_limit') ?><br>
                                            <i class="fas fa-server"></i> Server: <?= isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown' ?><br>
                                            <i class="fas fa-calendar"></i> Time: <?= date('Y-m-d H:i:s') ?><br>
                                            <i class="fas fa-user"></i> Session: <?= $this->session->userdata('logged_in') ? 'Logged In' : 'Not Logged In' ?> 
                                            (<?= $this->session->userdata('role') ?>)<br>
                                            <i class="fas fa-key"></i> User ID: <?= $this->session->userdata('user_id') ?: 'None' ?>
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
    
    // Create AbortController for timeout handling
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 minutes timeout
    
    fetch('<?= base_url('settings/backup_database') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // Response is not JSON, get text first
            return response.text().then(text => {
                console.log('Non-JSON response:', text.substring(0, 500));
                throw new Error('Server mengembalikan response non-JSON. Kemungkinan session expired atau server error.');
            });
        }
        
        return response.json();
    })
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
            
            // Check if redirect is needed
            if (response.redirect) {
                setTimeout(() => {
                    window.location.href = response.redirect;
                }, 2000);
            }
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        loading.style.display = 'none';
        btn.disabled = false;
        
        console.error('=== BACKUP ERROR DETAILS ===');
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        console.error('Error cause:', error.cause);
        console.error('Timestamp:', new Date().toISOString());
        console.error('URL:', window.location.href);
        console.error('User Agent:', navigator.userAgent);
        console.error('=============================');
        
        let errorMessage = 'Terjadi kesalahan saat melakukan backup';
        let errorDetails = '';
        
        if (error.name === 'TypeError') {
            errorMessage = 'Network Error: Periksa koneksi internet';
            errorDetails = 'Detail: ' + error.message;
        } else if (error.name === 'AbortError') {
            errorMessage = 'Request timeout: Backup memakan waktu terlalu lama';
            errorDetails = 'Coba lagi atau hubungi administrator hosting';
        } else if (error.name === 'SyntaxError') {
            errorMessage = 'Server Error: Response tidak valid';
            errorDetails = 'Kemungkinan session expired atau server error. Silakan refresh halaman dan coba lagi.';
        } else if (error.message) {
            errorDetails = 'Detail: ' + error.message;
        }
        
        result.className = 'backup-result mt-3 error';
        result.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>Error!</strong> ${errorMessage}
                    ${errorDetails ? '<br><small class="text-muted">' + errorDetails + '</small>' : ''}
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showErrorDetails()">
                    <i class="fas fa-bug"></i> Lihat Detail Error
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="checkHostingCompatibility()">
                    <i class="fas fa-server"></i> Cek Kompatibilitas Hosting
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh Halaman
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.href='<?= base_url('auth') ?>'">
                    <i class="fas fa-sign-in-alt"></i> Login Ulang
                </button>
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

function showErrorDetails() {
    const details = `
=== DETAIL ERROR BACKUP ===
URL: ${window.location.href}
User Agent: ${navigator.userAgent}
Timestamp: ${new Date().toISOString()}
PHP Version: ${document.querySelector('[data-php-version]')?.dataset.phpVersion || 'Unknown'}
Exec Available: ${document.querySelector('[data-exec-available]')?.dataset.execAvailable || 'Unknown'}
Database Connected: ${document.querySelector('[data-db-connection]')?.dataset.dbConnection || 'Unknown'}
Backup Dir: ${document.querySelector('[data-backup-dir]')?.dataset.backupDir || 'Unknown'}
Session Logged In: ${document.querySelector('[data-session-logged]')?.dataset.sessionLogged || 'Unknown'}
Session Role: ${document.querySelector('[data-session-role]')?.dataset.sessionRole || 'Unknown'}
Session User ID: ${document.querySelector('[data-session-user]')?.dataset.sessionUser || 'Unknown'}
========================
    `;
    
    console.log(details);
    alert('Detail error telah ditampilkan di Console (F12 > Console). Silakan copy dan kirim ke administrator.');
}

function testBackupConnection() {
    console.log('=== TESTING BACKUP CONNECTION ===');
    
    // Test basic fetch to backup endpoint
    fetch('<?= base_url('settings/backup_database') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'test=1'
    })
    .then(response => {
        console.log('Test Response status:', response.status);
        console.log('Test Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.log('Test Non-JSON response:', text.substring(0, 500));
                throw new Error('Server mengembalikan response non-JSON. Kemungkinan session expired.');
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        alert('Test berhasil! Response: ' + JSON.stringify(data, null, 2));
    })
    .catch(error => {
        console.error('Test failed:', error);
        alert('Test gagal: ' + error.message + '\n\nLihat console untuk detail.');
    });
}

function checkHostingCompatibility() {
    const compatibilityCheck = {
        phpVersion: '<?= PHP_VERSION ?>',
        execAvailable: <?= function_exists('exec') ? 'true' : 'false' ?>,
        mysqliAvailable: <?= extension_loaded('mysqli') ? 'true' : 'false' ?>,
        ftpAvailable: <?= extension_loaded('ftp') ? 'true' : 'false' ?>,
        backupDirExists: <?= is_dir(FCPATH . 'backups') ? 'true' : 'false' ?>,
        backupDirWritable: <?= is_writable(FCPATH . 'backups') ? 'true' : 'false' ?>,
        maxExecutionTime: <?= ini_get('max_execution_time') ?>,
        memoryLimit: '<?= ini_get('memory_limit') ?>',
        uploadMaxFilesize: '<?= ini_get('upload_max_filesize') ?>',
        postMaxSize: '<?= ini_get('post_max_size') ?>'
    };
    
    console.log('=== HOSTING COMPATIBILITY CHECK ===');
    console.log('PHP Version:', compatibilityCheck.phpVersion);
    console.log('Exec Function:', compatibilityCheck.execAvailable ? 'Available' : 'Disabled');
    console.log('MySQLi Extension:', compatibilityCheck.mysqliAvailable ? 'Available' : 'Missing');
    console.log('FTP Extension:', compatibilityCheck.ftpAvailable ? 'Available' : 'Missing');
    console.log('Backup Directory Exists:', compatibilityCheck.backupDirExists);
    console.log('Backup Directory Writable:', compatibilityCheck.backupDirWritable);
    console.log('Max Execution Time:', compatibilityCheck.maxExecutionTime);
    console.log('Memory Limit:', compatibilityCheck.memoryLimit);
    console.log('Upload Max Filesize:', compatibilityCheck.uploadMaxFilesize);
    console.log('Post Max Size:', compatibilityCheck.postMaxSize);
    console.log('=====================================');
    
    let issues = [];
    if (!compatibilityCheck.execAvailable) issues.push('Exec function disabled');
    if (!compatibilityCheck.mysqliAvailable) issues.push('MySQLi extension missing');
    if (!compatibilityCheck.backupDirExists) issues.push('Backup directory not found');
    if (!compatibilityCheck.backupDirWritable) issues.push('Backup directory not writable');
    if (compatibilityCheck.maxExecutionTime < 300) issues.push('Max execution time too low (< 300s)');
    
    if (issues.length > 0) {
        alert('Kompatibilitas Hosting:\n\nMasalah yang ditemukan:\n' + issues.join('\n') + '\n\nDetail lengkap lihat di Console (F12 > Console)');
    } else {
        alert('Kompatibilitas Hosting: OK\n\nSemua requirement terpenuhi. Detail lengkap lihat di Console (F12 > Console)');
    }
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
