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
                        <a href="<?= base_url('email/create') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Tambah Email
                        </a>
                        <button type="button" class="btn btn-info btn-sm" onclick="checkAccounts()" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="forceRefresh()" id="forceRefreshBtn">
                            <i class="fas fa-redo"></i> Force Refresh
                        </button>
                        <a href="<?= base_url('email/test_connection') ?>" class="btn btn-warning btn-sm">
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

                    <!-- Auth Method Info -->
                    <?php if (isset($auth_method)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Authentication Method:</strong> <?= $auth_method ?>
                        </div>
                    <?php endif; ?>

                    <!-- Debug Panel -->
                    <div class="alert alert-info" id="debug-panel" style="display: none;">
                        <h6><i class="fas fa-bug me-2"></i>Debug Information</h6>
                        <div id="debug-content"></div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Connection Tips:</strong>
                                <ul class="mb-0">
                                    <li>Pastikan cPanel credentials benar</li>
                                    <li>Periksa apakah port 2083 terbuka</li>
                                    <li>Coba test koneksi terlebih dahulu</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <strong>HTTP 403 Solutions:</strong>
                                <ul class="mb-0">
                                    <li>Session token mungkin expired</li>
                                    <li>Coba force login ulang</li>
                                    <li>Periksa permission di cPanel</li>
                                </ul>
                            </div>
                        </div>
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
                                                    <a href="<?= base_url('email/edit/' . urlencode($account['email'])) ?>" 
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
                    
                    <!-- Pagination -->
                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan <?= $offset + 1 ?> - <?= min($offset + $per_page, $total_accounts) ?> dari <?= $total_accounts ?> akun email
                            </div>
                            <nav aria-label="Email pagination">
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Previous Page -->
                                    <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= base_url('email?page=' . ($current_page - 1)) ?>">
                                                <i class="fas fa-chevron-left"></i> Sebelumnya
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="fas fa-chevron-left"></i> Sebelumnya
                                            </span>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- Page Numbers -->
                                    <?php
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $current_page + 2);
                                    
                                    if ($start_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= base_url('email?page=1') ?>">1</a>
                                        </li>
                                        <?php if ($start_page > 2): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= base_url('email?page=' . $i) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($end_page < $total_pages): ?>
                                        <?php if ($end_page < $total_pages - 1): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= base_url('email?page=' . $total_pages) ?>"><?= $total_pages ?></a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- Next Page -->
                                    <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= base_url('email?page=' . ($current_page + 1)) ?>">
                                                Selanjutnya <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                Selanjutnya <i class="fas fa-chevron-right"></i>
                                            </span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
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
// Global variable for delete email address
window.deleteEmailAddress = '';

// Check email accounts via AJAX
function checkAccounts(page = 1) {
    const tbody = document.getElementById('email-tbody');
    const refreshBtn = document.getElementById('refreshBtn');
    
    if (!tbody) {
        console.error('Email tbody element not found');
        return;
    }
    
    // Disable refresh button and show loading state
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    }
    
    const loadingRow = `
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Memuat data akun email...
            </td>
        </tr>
    `;
    
    tbody.innerHTML = loadingRow;
    
    console.log('=== EMAIL CHECK ACCOUNTS ===');
    console.log('Requesting URL:', '<?= base_url('email/check_accounts') ?>?page=' + page);
    
    fetch('<?= base_url('email/check_accounts') ?>?page=' + page, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text();
    })
    .then(data => {
        console.log('Raw response:', data);
        
        try {
            const jsonData = JSON.parse(data);
            console.log('Parsed JSON:', jsonData);
            
            if (jsonData.success) {
                updateEmailTable(jsonData.accounts, jsonData.pagination);
                console.log('Debug info:', jsonData.debug_info);
                console.log('Pagination info:', jsonData.pagination);
            } else {
                console.error('API Error:', jsonData.message);
                showAlert('error', 'Gagal memuat data: ' + jsonData.message);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gagal memuat data akun email: ${jsonData.message}
                        </td>
                    </tr>
                `;
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Raw response was not valid JSON:', data);
            showAlert('error', 'Response tidak valid JSON');
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Response tidak valid JSON
                        <br><small>Silakan cek console untuk detail</small>
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showAlert('error', 'Terjadi kesalahan saat memuat data: ' + error.message);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Terjadi kesalahan saat memuat data
                    <br><small>Error: ${error.message}</small>
                </td>
            </tr>
        `;
    })
    .finally(() => {
        // Re-enable refresh button
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }
    });
}

// Force refresh dengan fresh login
function forceRefresh() {
    const forceRefreshBtn = document.getElementById('forceRefreshBtn');
    
    if (forceRefreshBtn) {
        forceRefreshBtn.disabled = true;
        forceRefreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Force Login...';
    }
    
    console.log('=== EMAIL FORCE REFRESH ===');
    console.log('Performing force refresh with fresh login');
    
    // Lakukan force refresh dengan parameter khusus
    fetch('<?= base_url('email/check_accounts') ?>?force_refresh=1', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        try {
            const jsonData = JSON.parse(data);
            if (jsonData.success) {
                updateEmailTable(jsonData.accounts, jsonData.pagination);
                showAlert('success', 'Force refresh berhasil! Data diperbarui dengan fresh login.');
            } else {
                showAlert('error', 'Force refresh gagal: ' + jsonData.message);
            }
        } catch (e) {
            showAlert('error', 'Response tidak valid JSON');
        }
    })
    .catch(error => {
        console.error('Force refresh error:', error);
        showAlert('error', 'Terjadi kesalahan saat force refresh: ' + error.message);
    })
    .finally(() => {
        if (forceRefreshBtn) {
            forceRefreshBtn.disabled = false;
            forceRefreshBtn.innerHTML = '<i class="fas fa-redo"></i> Force Refresh';
        }
    });
}

// Update email table with new data
function updateEmailTable(accounts, pagination = null) {
    const tbody = document.getElementById('email-tbody');
    if (!tbody) {
        console.error('Email tbody element not found');
        return;
    }
    
    console.log('=== EMAIL UPDATE TABLE ===');
    console.log('Updating table with accounts:', accounts);
    console.log('Pagination data:', pagination);
    
    if (!Array.isArray(accounts)) {
        console.error('Accounts is not an array:', accounts);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error: Invalid data format
                </td>
            </tr>
        `;
        return;
    }
    
    if (accounts.length === 0) {
        console.log('No accounts found');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <br>Tidak ada akun email yang ditemukan
                    <br><button class="btn btn-sm btn-info mt-2" onclick="showDebugInfo()">Show Debug Info</button>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    accounts.forEach((account, index) => {
        console.log('Processing account:', account);
        
        const statusBadge = account.suspended ? 
            '<span class="badge bg-danger"><i class="fas fa-ban"></i> Suspended</span>' :
            '<span class="badge bg-success"><i class="fas fa-check"></i> Active</span>';
        
        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>
                    <i class="fas fa-envelope me-2 text-primary"></i>
                    <strong>${account.email || 'N/A'}</strong>
                </td>
                <td class="text-center">
                    <span class="badge bg-info">${numberFormat(account.quota || 0)} MB</span>
                </td>
                <td class="text-center">${numberFormat(account.usage || 0)} MB</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info" 
                                onclick="showAccountDetails('${account.email || ''}')"
                                title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="<?= base_url('email/edit/') ?>${encodeURIComponent(account.email || '')}" 
                            class="btn btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger" 
                                onclick="deleteAccount('${account.email || ''}')"
                                title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    console.log('Table updated successfully with', accounts.length, 'accounts');
    
    // Update pagination if provided
    if (pagination) {
        updatePagination(pagination);
    }
}

// Update pagination controls
function updatePagination(pagination) {
    console.log('=== EMAIL UPDATE PAGINATION ===');
    console.log('Updating pagination with:', pagination);
    
    // Find existing pagination container
    let paginationContainer = document.querySelector('.pagination-container');
    
    // If no pagination container exists, create one
    if (!paginationContainer) {
        const tableContainer = document.querySelector('.table-responsive');
        if (tableContainer) {
            const paginationDiv = document.createElement('div');
            paginationDiv.className = 'd-flex justify-content-between align-items-center mt-3 pagination-container';
            tableContainer.parentNode.insertBefore(paginationDiv, tableContainer.nextSibling);
            paginationContainer = paginationDiv;
        }
    }
    
    if (!paginationContainer) {
        console.error('Could not find or create pagination container');
        return;
    }
    
    if (pagination.total_pages <= 1) {
        paginationContainer.style.display = 'none';
        return;
    }
    
    paginationContainer.style.display = 'flex';
    
    const startItem = (pagination.current_page - 1) * pagination.per_page + 1;
    const endItem = Math.min(pagination.current_page * pagination.per_page, pagination.total_accounts);
    
    let html = `
        <div class="text-muted">
            Menampilkan ${startItem} - ${endItem} dari ${pagination.total_accounts} akun email
        </div>
        <nav aria-label="Email pagination">
            <ul class="pagination pagination-sm mb-0">
    `;
    
    // Previous button
    if (pagination.has_prev) {
        html += `
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="checkAccounts(${pagination.current_page - 1})">
                    <i class="fas fa-chevron-left"></i> Sebelumnya
                </a>
            </li>
        `;
    } else {
        html += `
            <li class="page-item disabled">
                <span class="page-link">
                    <i class="fas fa-chevron-left"></i> Sebelumnya
                </span>
            </li>
        `;
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    if (startPage > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="checkAccounts(1)">1</a>
            </li>
        `;
        if (startPage > 2) {
            html += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        html += `
            <li class="page-item ${activeClass}">
                <a class="page-link" href="javascript:void(0)" onclick="checkAccounts(${i})">${i}</a>
            </li>
        `;
    }
    
    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            html += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
        html += `
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="checkAccounts(${pagination.total_pages})">${pagination.total_pages}</a>
            </li>
        `;
    }
    
    // Next button
    if (pagination.has_next) {
        html += `
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="checkAccounts(${pagination.current_page + 1})">
                    Selanjutnya <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
    } else {
        html += `
            <li class="page-item disabled">
                <span class="page-link">
                    Selanjutnya <i class="fas fa-chevron-right"></i>
                </span>
            </li>
        `;
    }
    
    html += `
            </ul>
        </nav>
    `;
    
    paginationContainer.innerHTML = html;
    console.log('Pagination updated successfully');
}

// Show account details in modal
function showAccountDetails(email) {
    console.log('=== EMAIL SHOW ACCOUNT DETAILS ===');
    console.log('Showing details for email:', email);
    
    const accountDetailsModal = document.getElementById('accountDetailsModal');
    const content = document.getElementById('accountDetailsContent');
    
    if (!accountDetailsModal || !content) {
        console.error('Account details modal elements not found');
        showAlert('error', 'Modal detail akun tidak ditemukan');
        return;
    }
    
    const modal = new bootstrap.Modal(accountDetailsModal);
    
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
                    <p class="text-muted">${email || 'N/A'}</p>
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
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Detailed account information will be available in future updates.
                    </div>
                </div>
            </div>
        `;
    }, 1000);
}

// Delete account confirmation
function deleteAccount(email) {
    console.log('=== EMAIL DELETE ACCOUNT ===');
    console.log('Deleting email:', email);
    
    if (!email) {
        console.error('No email provided for deletion');
        showAlert('error', 'Email tidak valid untuk dihapus');
        return;
    }
    
    window.deleteEmailAddress = email;
    const deleteEmailElement = document.getElementById('deleteEmail');
    if (deleteEmailElement) {
        deleteEmailElement.textContent = email;
    }
    
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        const modal = new bootstrap.Modal(deleteModal);
        modal.show();
    } else {
        console.error('Delete modal not found');
        showAlert('error', 'Modal konfirmasi tidak ditemukan');
    }
}

// Confirm delete action
const confirmDeleteElement = document.getElementById('confirmDelete');
if (confirmDeleteElement) {
    confirmDeleteElement.addEventListener('click', function() {
        console.log('=== EMAIL CONFIRM DELETE ===');
        console.log('Confirming deletion of email:', window.deleteEmailAddress);
        
        if (window.deleteEmailAddress) {
            const deleteUrl = '<?= base_url('email/delete/') ?>' + encodeURIComponent(window.deleteEmailAddress);
            console.log('Redirecting to:', deleteUrl);
            window.location.href = deleteUrl;
        } else {
            console.error('No email address to delete');
            showAlert('error', 'Tidak ada email yang dipilih untuk dihapus');
        }
    });
} else {
    console.error('Confirm delete button not found');
}

// Number formatting helper
function numberFormat(number) {
    if (number === null || number === undefined || isNaN(number)) {
        return '0';
    }
    return new Intl.NumberFormat('id-ID').format(number);
}

// Show alert message
function showAlert(type, message) {
    console.log('=== EMAIL SHOW ALERT ===');
    console.log('Alert type:', type);
    console.log('Alert message:', message);
    
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const cardBody = document.querySelector('.card-body');
    if (!cardBody) {
        console.error('Card body not found');
        // Try to find any container to show the alert
        const container = document.querySelector('.content-body') || document.body;
        if (container) {
            container.insertAdjacentHTML('afterbegin', alertHtml);
        }
        return;
    }
    
    const existingAlert = cardBody.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = cardBody.querySelector('.alert') || document.querySelector('.alert');
        if (alert) {
            try {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } catch (e) {
                console.error('Error closing alert:', e);
                alert.remove();
            }
        }
    }, 5000);
}

// Debug functions
function showDebugInfo() {
    console.log('=== EMAIL DEBUG ===');
    console.log('Current URL:', window.location.href);
    console.log('Base URL:', '<?= base_url() ?>');
    console.log('Check accounts URL:', '<?= base_url('email/check_accounts') ?>');
    
    const debugPanel = document.getElementById('debug-panel');
    const debugContent = document.getElementById('debug-content');
    
    if (!debugPanel || !debugContent) {
        console.error('Debug panel elements not found');
        return;
    }
    
    // Show loading state
    debugContent.innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading debug information...</p>
        </div>
    `;
    debugPanel.style.display = 'block';
    
    // Test check accounts directly
    fetch('<?= base_url('email/check_accounts') ?>')
        .then(response => {
            console.log('Check accounts response status:', response.status);
            console.log('Check accounts response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text();
        })
        .then(data => {
            console.log('Check accounts raw response:', data);
            try {
                const jsonData = JSON.parse(data);
                console.log('Check accounts parsed JSON:', jsonData);
                
                // Show debug info in UI
                debugContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Response Status:</strong> Success<br>
                            <strong>Total Accounts:</strong> ${jsonData.accounts ? jsonData.accounts.length : 0}<br>
                            <strong>Auth Method:</strong> ${jsonData.debug_info ? jsonData.debug_info.auth_method : 'N/A'}<br>
                            <strong>Session Token:</strong> ${jsonData.debug_info ? jsonData.debug_info.session_token : 'N/A'}<br>
                            <strong>Timestamp:</strong> ${jsonData.debug_info ? jsonData.debug_info.timestamp : 'N/A'}
                        </div>
                        <div class="col-md-6">
                            <strong>Raw Response:</strong><br>
                            <pre style="font-size: 10px; max-height: 200px; overflow-y: auto;">${JSON.stringify(jsonData, null, 2)}</pre>
                        </div>
                    </div>
                `;
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                console.log('Raw response was not valid JSON:', data);
                
                // Show error in UI
                debugContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> Response is not valid JSON<br>
                        <strong>JSON Error:</strong> ${e.message}<br>
                        <strong>Raw Response:</strong><br>
                        <pre style="font-size: 10px; max-height: 200px; overflow-y: auto;">${data}</pre>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Check accounts fetch error:', error);
            
            // Show error in UI
            debugContent.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Fetch Error:</strong> ${error.message}<br>
                    <strong>Error Details:</strong><br>
                    <pre style="font-size: 10px; max-height: 200px; overflow-y: auto;">${error.stack}</pre>
                </div>
            `;
        });
}

// Auto refresh every 30 seconds
// setInterval(checkAccounts, 30000);

// Initialize debug info on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== EMAIL PAGE LOADED ===');
    console.log('Page URL:', window.location.href);
    console.log('User Agent:', navigator.userAgent);
    console.log('Timestamp:', new Date().toISOString());
    
    // Check if required elements exist
    const requiredElements = [
        'email-tbody',
        'debug-panel',
        'debug-content',
        'deleteModal',
        'deleteEmail',
        'confirmDelete',
        'accountDetailsModal',
        'accountDetailsContent'
    ];
    
    const missingElements = requiredElements.filter(id => !document.getElementById(id));
    if (missingElements.length > 0) {
        console.warn('Missing required elements:', missingElements);
    } else {
        console.log('All required elements found');
    }
    
    // Initialize global variables
    window.deleteEmailAddress = '';
    
    console.log('Email page initialization complete');
});
</script>