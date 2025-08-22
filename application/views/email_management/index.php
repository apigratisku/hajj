<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope me-2"></i>Manajemen Email
                    </h5>
                    <div class="btn-group">
                        <a href="<?= base_url('email_middleware/create') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Tambah Email
                        </a>
                        <button type="button" class="btn btn-info btn-sm" onclick="checkAccounts()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <a href="<?= base_url('email_middleware/test_connection') ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-plug"></i> Test Koneksi
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Debug Panel -->
                    <div class="alert alert-info" id="debug-panel" style="display: none;">
                        <h6><i class="fas fa-bug me-2"></i>Debug Information</h6>
                        <div id="debug-content"></div>
                    </div>

                    <!-- Email Accounts Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="email-table">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Email Address</th>
                                    <th class="text-center">Quota</th>
                                    <th class="text-center">Usage</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="email-tbody">
                                <?php if (empty($email_accounts)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>Tidak ada akun email yang ditemukan
                                            <br><small>Silakan konfigurasi cPanel credentials di file config/cpanel_config.php</small>
                                            <br><button class="btn btn-sm btn-info mt-2" onclick="showDebugInfo()">Show Debug Info</button>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($email_accounts as $account): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td>
                                                <i class="fas fa-envelope me-2 text-primary"></i>
                                                <strong><?= htmlspecialchars($account['email']) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    <?= number_format($account['quota']) ?> MB
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?= number_format($account['usage']) ?> MB
                                            </td>
                                            <td class="text-center">
                                                <?php if ($account['suspended']): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-ban"></i> Suspended
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Active
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-info" 
                                                            onclick="showAccountDetails('<?= htmlspecialchars($account['email']) ?>')"
                                                            title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="<?= base_url('email_middleware/edit/' . urlencode($account['email'])) ?>" 
                                                       class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger" 
                                                            onclick="deleteAccount('<?= htmlspecialchars($account['email']) ?>')"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Details Modal -->
<div class="modal fade" id="accountDetailsModal" tabindex="-1" aria-labelledby="accountDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountDetailsModalLabel">
                    <i class="fas fa-envelope me-2"></i>Detail Akun Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="accountDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus akun email <strong id="deleteEmail"></strong>?</p>
                <p class="text-danger"><small>Perhatian: Tindakan ini tidak dapat dibatalkan!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash me-1"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let deleteEmailAddress = '';

// Check email accounts via AJAX
function checkAccounts() {
    const tbody = document.getElementById('email-tbody');
    const loadingRow = `
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Memuat data akun email...
            </td>
        </tr>
    `;
    
    tbody.innerHTML = loadingRow;
    
    fetch('<?= base_url('email_management/check_accounts') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateEmailTable(data.accounts);
        } else {
            showAlert('error', 'Gagal memuat data: ' + data.message);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Gagal memuat data akun email
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat memuat data');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Terjadi kesalahan saat memuat data
                </td>
            </tr>
        `;
    });
}

// Update email table with new data
function updateEmailTable(accounts) {
    const tbody = document.getElementById('email-tbody');
    
    if (accounts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <br>Tidak ada akun email yang ditemukan
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    accounts.forEach((account, index) => {
        const statusBadge = account.suspended ? 
            '<span class="badge bg-danger"><i class="fas fa-ban"></i> Suspended</span>' :
            '<span class="badge bg-success"><i class="fas fa-check"></i> Active</span>';
        
        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>
                    <i class="fas fa-envelope me-2 text-primary"></i>
                    <strong>${account.email}</strong>
                </td>
                <td class="text-center">
                    <span class="badge bg-info">${numberFormat(account.quota)} MB</span>
                </td>
                <td class="text-center">${numberFormat(account.usage)} MB</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info" 
                                onclick="showAccountDetails('${account.email}')"
                                title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="<?= base_url('email_management/edit/') ?>${encodeURIComponent(account.email)}" 
                           class="btn btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger" 
                                onclick="deleteAccount('${account.email}')"
                                title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Show account details in modal
function showAccountDetails(email) {
    const modal = new bootstrap.Modal(document.getElementById('accountDetailsModal'));
    const content = document.getElementById('accountDetailsContent');
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat detail akun...</p>
        </div>
    `;
    
    modal.show();
    
    // Here you would typically make an AJAX call to get account details
    // For now, we'll show a simple message
    setTimeout(() => {
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-envelope me-2"></i>Email Address</h6>
                    <p class="text-muted">${email}</p>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-calendar me-2"></i>Created Date</h6>
                    <p class="text-muted">Information not available</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6><i class="fas fa-hdd me-2"></i>Quota</h6>
                    <p class="text-muted">Information not available</p>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-chart-pie me-2"></i>Usage</h6>
                    <p class="text-muted">Information not available</p>
                </div>
            </div>
        `;
    }, 1000);
}

// Delete account confirmation
function deleteAccount(email) {
    deleteEmailAddress = email;
    document.getElementById('deleteEmail').textContent = email;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Confirm delete action
document.getElementById('confirmDelete').addEventListener('click', function() {
    if (deleteEmailAddress) {
        window.location.href = '<?= base_url('email_management/delete/') ?>' + encodeURIComponent(deleteEmailAddress);
    }
});

// Number formatting helper
function numberFormat(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Show alert message
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const cardBody = document.querySelector('.card-body');
    const existingAlert = cardBody.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = cardBody.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Auto refresh every 30 seconds
setInterval(checkAccounts, 30000);

// Debug functions
function showDebugInfo() {
    console.log('=== EMAIL MIDDLEWARE DEBUG ===');
    console.log('Current URL:', window.location.href);
    console.log('Base URL:', '<?= base_url() ?>');
    console.log('Check accounts URL:', '<?= base_url('email_middleware/check_accounts') ?>');
    console.log('Debug URL:', '<?= base_url('email_middleware/debug') ?>');
    
    // Test check accounts directly
    fetch('<?= base_url('email_middleware/check_accounts') ?>')
        .then(response => {
            console.log('Check accounts response status:', response.status);
            console.log('Check accounts response headers:', response.headers);
            return response.text();
        })
        .then(data => {
            console.log('Check accounts raw response:', data);
            try {
                const jsonData = JSON.parse(data);
                console.log('Check accounts parsed JSON:', jsonData);
                
                // Show debug info in UI
                document.getElementById('debug-content').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Response Status:</strong> Success<br>
                            <strong>Total Accounts:</strong> ${jsonData.accounts ? jsonData.accounts.length : 0}<br>
                            <strong>Timestamp:</strong> ${jsonData.debug_info ? jsonData.debug_info.timestamp : 'N/A'}<br>
                            <strong>Middleware URL:</strong> ${jsonData.debug_info ? jsonData.debug_info.middleware_url : 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Raw Response:</strong><br>
                            <pre style="font-size: 10px; max-height: 200px; overflow-y: auto;">${JSON.stringify(jsonData, null, 2)}</pre>
                        </div>
                    </div>
                `;
                document.getElementById('debug-panel').style.display = 'block';
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                console.log('Raw response was not valid JSON:', data);
                
                // Show error in UI
                document.getElementById('debug-content').innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> Response is not valid JSON<br>
                        <strong>Raw Response:</strong><br>
                        <pre style="font-size: 10px; max-height: 200px; overflow-y: auto;">${data}</pre>
                    </div>
                `;
                document.getElementById('debug-panel').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Check accounts fetch error:', error);
            
            // Show error in UI
            document.getElementById('debug-content').innerHTML = `
                <div class="alert alert-danger">
                    <strong>Fetch Error:</strong> ${error.message}<br>
                    <strong>Error Details:</strong><br>
                    <pre style="font-size: 10px; max-height: 200px; overflow-y: auto;">${error.stack}</pre>
                </div>
            `;
            document.getElementById('debug-panel').style.display = 'block';
        });
}

// Enhanced checkAccounts function with detailed logging
function checkAccounts() {
    console.log('=== CHECKING EMAIL ACCOUNTS ===');
    console.log('Timestamp:', new Date().toISOString());
    console.log('Request URL:', '<?= base_url('email_middleware/check_accounts') ?>');
    
    const tbody = document.getElementById('email-tbody');
    const loadingRow = `
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Memuat data email...</span>
            </div>
        </tr>
    `;
    
    tbody.innerHTML = loadingRow;
    
    fetch('<?= base_url('email_middleware/check_accounts') ?>')
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text();
        })
        .then(data => {
            console.log('Raw response data:', data);
            
            try {
                const result = JSON.parse(data);
                console.log('Parsed JSON result:', result);
                
                if (result.success) {
                    console.log('Success! Total accounts:', result.accounts.length);
                    console.log('Debug info:', result.debug_info);
                    
                    if (result.accounts.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <br>Tidak ada akun email yang ditemukan
                                    <br><small>Silakan konfigurasi cPanel credentials di file config/cpanel_config.php</small>
                                    <br><button class="btn btn-sm btn-info mt-2" onclick="showDebugInfo()">Show Debug Info</button>
                                </td>
                            </tr>
                        `;
                    } else {
                        let html = '';
                        result.accounts.forEach((account, index) => {
                            html += `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td>
                                        <i class="fas fa-envelope me-2 text-primary"></i>
                                        <strong>${account.email}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            ${numberFormat(account.quota)} MB
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        ${numberFormat(account.usage)} MB
                                    </td>
                                    <td class="text-center">
                                        ${account.suspended ? 
                                            '<span class="badge bg-danger"><i class="fas fa-ban"></i> Suspended</span>' : 
                                            '<span class="badge bg-success"><i class="fas fa-check"></i> Active</span>'
                                        }
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info" 
                                                    onclick="showAccountDetails('${account.email}')"
                                                    title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="<?= base_url('email_middleware/edit/') ?>${encodeURIComponent(account.email)}" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="deleteAccount('${account.email}')"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    }
                } else {
                    console.error('API returned error:', result.message);
                    console.error('Debug info:', result.debug_info);
                    
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-danger">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <br>Error: ${result.message}
                                <br><small>Check console for details</small>
                                <br><button class="btn btn-sm btn-info mt-2" onclick="showDebugInfo()">Show Debug Info</button>
                            </td>
                        </tr>
                    `;
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Raw response that failed to parse:', data);
                
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <br>Error: Invalid JSON response
                            <br><small>Check console for details</small>
                            <br><button class="btn btn-sm btn-info mt-2" onclick="showDebugInfo()">Show Debug Info</button>
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <br>Error: ${error.message}
                    <br><small>Check console for details</small>
                    <br><button class="btn btn-sm btn-info mt-2" onclick="showDebugInfo()">Show Debug Info</button>
                </td>
                </tr>
            `;
        });
}

// Initialize debug info on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== EMAIL MIDDLEWARE PAGE LOADED ===');
    console.log('Page URL:', window.location.href);
    console.log('User Agent:', navigator.userAgent);
    console.log('Timestamp:', new Date().toISOString());
});
</script>
