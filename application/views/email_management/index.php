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
                      
                        <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()" id="bulkDeleteBtn" style="display: none;">
                            <i class="fas fa-trash"></i> Hapus Terpilih
                        </button>
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

                    <!-- Search and Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" id="emailSearchInput" 
                                       placeholder="Cari email address..." 
                                       onkeyup="debouncedFilterEmails()">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter" onchange="filterEmails()">
                                <option value="">Semua Status</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="quotaFilter" onchange="filterEmails()">
                                <option value="">Semua Quota</option>
                                <option value="small">Kecil (< 100MB)</option>
                                <option value="medium">Sedang (100MB - 500MB)</option>
                                <option value="large">Besar (> 500MB)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search Results Info -->
                    <div id="searchResultsInfo" class="alert alert-info" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="searchResultsText"></span>
                    </div>

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
                                                                        <th class="text-center" style="width: 50px;">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" title="Pilih Semua">
                                    </th>
                                   
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
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>Tidak ada akun email yang ditemukan
                                            <br><small>Silakan konfigurasi cPanel credentials di file config/cpanel_config.php</small>
                                            <br><button class="btn btn-sm btn-info mt-2" onclick="showDebugInfo()">Show Debug Info</button>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($email_accounts as $account): ?>
                                        <tr>
                                                                                        <td class="text-center">
                                                <?php $emailValue = htmlspecialchars($account['email']); $isEmailValid = !empty($emailValue); ?>
                                                <input type="checkbox" class="email-checkbox" value="<?= $emailValue ?>" onchange="updateBulkDeleteButton()" <?= !$isEmailValid ? 'disabled' : '' ?>>
                                            </td>
                                            
                                            <td>
                                                <i class="fas fa-envelope me-2 text-primary"></i>
                                                <strong><?= $isEmailValid ? $emailValue : 'N/A' ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info" title="Total quota">
                                                    <?= number_format($account['quota']) ?> MB
                                                </span>
                                                <?php if (isset($account['usage']) && $account['quota'] > 0): 
                                                    $usage_percentage = ($account['usage'] / $account['quota']) * 100;
                                                ?>
                                                    <br><small class="text-muted">
                                                        <?= number_format($usage_percentage, 1) ?>% used
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-primary" title="Used space">
                                                    <?= number_format($account['usage']) ?> MB
                                                </span>
                                                <?php if (isset($account['usage']) && $account['quota'] > 0): 
                                                    $available = $account['quota'] - $account['usage'];
                                                ?>
                                                    <br><small class="text-success">
                                                        <?= number_format($available) ?> MB free
                                                    </small>
                                                <?php endif; ?>
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
                                                            onclick="showAccountDetails('<?= $emailValue ?>')"
                                                            title="Detail" <?= !$isEmailValid ? 'disabled' : '' ?>>
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                    <button type="button" class="btn btn-primary" 
                                            onclick="openEmailInbox('<?= $emailValue ?>')"
                                            title="Buka Inbox" <?= !$isEmailValid ? 'disabled' : '' ?>>
                                        <i class="fas fa-inbox"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" 
                                            onclick="refreshQuotaInfo('<?= $emailValue ?>')"
                                            title="Refresh Quota Info" <?= !$isEmailValid ? 'disabled' : '' ?>>
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                                    <a href="<?= base_url('email/edit/' . urlencode($account['email'])) ?>" 
                                                        class="btn btn-warning" title="Edit" <?= !$isEmailValid ? 'style="pointer-events: none; opacity: 0.6;"' : '' ?>>
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger" 
                                                            onclick="deleteAccount('<?= $emailValue ?>')"
                                                            title="Hapus" <?= !$isEmailValid ? 'disabled' : '' ?>>
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

<!-- Email Inbox Modal -->
<div class="modal fade" id="emailInboxModal" tabindex="-1" aria-labelledby="emailInboxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailInboxModalLabel">
                    <i class="fas fa-inbox me-2"></i>Inbox Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <!-- Email Folders -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-folder me-2"></i>Folders
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <a href="#" class="list-group-item list-group-item-action active" data-folder="INBOX">
                                        <i class="fas fa-inbox me-2"></i>Inbox
                                        <span class="badge bg-primary float-end" id="inboxCount">0</span>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-folder="SENT">
                                        <i class="fas fa-paper-plane me-2"></i>Sent
                                        <span class="badge bg-secondary float-end" id="sentCount">0</span>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-folder="DRAFTS">
                                        <i class="fas fa-file-alt me-2"></i>Drafts
                                        <span class="badge bg-warning float-end" id="draftsCount">0</span>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action" data-folder="TRASH">
                                        <i class="fas fa-trash me-2"></i>Trash
                                        <span class="badge bg-danger float-end" id="trashCount">0</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email Account Info -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Account Info
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="emailAccountInfo">
                                    <!-- Account info will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <!-- Email List -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-list me-2"></i><span id="currentFolderName">Inbox</span>
                                </h6>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshInbox()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="composeEmail()">
                                        <i class="fas fa-plus"></i> Compose
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div id="emailList">
                                    <div class="text-center p-4">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Memuat email...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email Content -->
                        <div class="card mt-3" id="emailContentCard" style="display: none;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-envelope-open me-2"></i>Email Content
                                </h6>
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="replyEmail()">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEmail()">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="emailContent">
                                <!-- Email content will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
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

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus Massal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Perhatian!</strong> Anda akan menghapus <span id="selectedCount" class="badge bg-danger">0</span> akun email yang dipilih.
                </div>
                <p>Apakah Anda yakin ingin menghapus akun email berikut?</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Email Address</th>
                            </tr>
                        </thead>
                        <tbody id="selectedEmailsList">
                            <!-- Selected emails will be listed here -->
                        </tbody>
                    </table>
                </div>
                <p class="text-danger"><small><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan dan akan menghapus semua akun email yang dipilih secara permanen!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDelete">
                    <i class="fas fa-trash me-1"></i>Hapus Semua
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
// Global variable for delete email address
window.deleteEmailAddress = '';
// Global variable to store all email accounts for filtering
window.allEmailAccounts = [];
// Global variables for email inbox
window.currentEmailAccount = '';
window.currentFolder = 'INBOX';
window.currentEmailId = '';

// Helper functions
function isValidEmail(email) {
    if (!email || typeof email !== 'string') {
        return false;
    }
    email = email.trim();
    return email !== '' && email !== 'N/A' && email.includes('@');
}

function getValidEmails(checkboxes) {
    return Array.from(checkboxes)
        .map(checkbox => checkbox.value)
        .filter(email => isValidEmail(email));
}

// Email filtering and search functions
function filterEmails() {
    const searchTerm = document.getElementById('emailSearchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const quotaFilter = document.getElementById('quotaFilter').value;
    const searchResultsInfo = document.getElementById('searchResultsInfo');
    const searchResultsText = document.getElementById('searchResultsText');
    
    if (!window.allEmailAccounts || window.allEmailAccounts.length === 0) {
        console.log('No accounts to filter');
        return;
    }
    
    let filteredAccounts = window.allEmailAccounts.filter(account => {
        // Search filter
        const matchesSearch = !searchTerm || 
            account.email.toLowerCase().includes(searchTerm);
        
        // Status filter
        const matchesStatus = !statusFilter || 
            (statusFilter === 'active' && !account.suspended) ||
            (statusFilter === 'suspended' && account.suspended);
        
        // Quota filter
        let matchesQuota = true;
        if (quotaFilter) {
            const quota = parseInt(account.quota) || 0;
            switch(quotaFilter) {
                case 'small':
                    matchesQuota = quota < 100;
                    break;
                case 'medium':
                    matchesQuota = quota >= 100 && quota <= 500;
                    break;
                case 'large':
                    matchesQuota = quota > 500;
                    break;
            }
        }
        
        return matchesSearch && matchesStatus && matchesQuota;
    });
    
    // Update search results info
    const totalAccounts = window.allEmailAccounts.length;
    const filteredCount = filteredAccounts.length;
    
    if (searchTerm || statusFilter || quotaFilter) {
        searchResultsInfo.style.display = 'block';
        searchResultsText.textContent = `Menampilkan ${filteredCount} dari ${totalAccounts} akun email`;
    } else {
        searchResultsInfo.style.display = 'none';
    }
    
    // Update table with filtered results
    updateEmailTable(filteredAccounts);
    
    console.log(`Filtered ${filteredCount} accounts from ${totalAccounts} total accounts`);
}

function clearSearch() {
    document.getElementById('emailSearchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('quotaFilter').value = '';
    filterEmails();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Debounced search function
const debouncedFilterEmails = debounce(filterEmails, 300);

// Email Inbox Functions
function openEmailInbox(email) {
    console.log('=== EMAIL OPEN INBOX ===');
    console.log('Opening inbox for email:', email);
    
    if (!isValidEmail(email)) {
        console.error('No valid email provided for inbox');
        showAlert('error', 'Email tidak valid untuk membuka inbox');
        return;
    }
    
    window.currentEmailAccount = email;
    window.currentFolder = 'INBOX';
    
    const emailInboxModal = document.getElementById('emailInboxModal');
    const modalTitle = document.getElementById('emailInboxModalLabel');
    
    if (!emailInboxModal) {
        console.error('Email inbox modal not found');
        showAlert('error', 'Modal inbox tidak ditemukan');
        return;
    }
    
    // Update modal title
    if (modalTitle) {
        modalTitle.innerHTML = `<i class="fas fa-inbox me-2"></i>Inbox Email - ${email}`;
    }
    
    // Load account info
    loadEmailAccountInfo(email);
    
    // Load inbox
    loadEmailFolder('INBOX');
    
    // Show modal with proper focus management
    const modal = new bootstrap.Modal(emailInboxModal, {
        focus: true,
        backdrop: 'static',
        keyboard: false
    });
    
    // Handle modal events for accessibility
    emailInboxModal.addEventListener('shown.bs.modal', function () {
        // Remove aria-hidden when modal is shown
        emailInboxModal.removeAttribute('aria-hidden');
        
        // Focus on first interactive element
        const firstButton = emailInboxModal.querySelector('button:not([disabled])');
        if (firstButton) {
            firstButton.focus();
        }
    });
    
    emailInboxModal.addEventListener('hidden.bs.modal', function () {
        // Clean up when modal is hidden
        window.currentEmailAccount = '';
        window.currentFolder = 'INBOX';
        window.currentEmailId = '';
        
        // Reset modal state
        const emailContentCard = document.getElementById('emailContentCard');
        if (emailContentCard) {
            emailContentCard.style.display = 'none';
        }
    });
    
    modal.show();
}

function loadEmailAccountInfo(email) {
    console.log('=== EMAIL LOAD ACCOUNT INFO ===');
    console.log('Loading account info for:', email);
    
    const accountInfo = document.getElementById('emailAccountInfo');
    if (!accountInfo) {
        console.error('Account info element not found');
        return;
    }
    
    // Show loading state
    accountInfo.innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 small">Memuat info akun...</p>
        </div>
    `;
    
    // Fetch detailed quota information from server
    const encodedEmail = encodeURIComponent(email);
    console.log('Fetching quota info for encoded email:', encodedEmail);
    
    fetch(`<?= base_url('email/get_quota_info/') ?>${encodedEmail}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Quota info response:', data);
        
        if (data.success && data.data) {
            const info = data.data;
            const warningClass = getWarningClass(info.warning_level);
            const progressClass = getProgressClass(info.usage_percentage);
            
            accountInfo.innerHTML = `
                <div class="mb-2">
                    <strong>Email:</strong><br>
                    <span class="text-primary">${info.email}</span>
                </div>
                <div class="mb-2">
                    <strong>Quota:</strong><br>
                    <span class="badge bg-info">${info.quota_formatted}</span>
                    <small class="text-muted d-block">${numberFormat(info.quota_mb)} MB</small>
                </div>
                <div class="mb-2">
                    <strong>Usage:</strong><br>
                    <span class="badge bg-secondary">${info.usage_formatted}</span>
                    <small class="text-muted d-block">${numberFormat(info.usage_mb)} MB</small>
                </div>
                <div class="mb-2">
                    <strong>Available:</strong><br>
                    <span class="badge bg-success">${info.available_formatted}</span>
                    <small class="text-muted d-block">${numberFormat(info.available_mb)} MB</small>
                </div>
                <div class="mb-2">
                    <strong>Status:</strong><br>
                    ${info.suspended ? 
                        '<span class="badge bg-danger">Suspended</span>' : 
                        '<span class="badge bg-success">Active</span>'
                    }
                    ${info.warning_level !== 'good' ? 
                        `<br><span class="badge ${warningClass} mt-1">${getWarningText(info.warning_level)}</span>` : 
                        ''
                    }
                </div>
                <div class="mb-0">
                    <strong>Usage:</strong> ${info.usage_percentage}%<br>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar ${progressClass}" 
                             style="width: ${Math.min(info.usage_percentage, 100)}%"
                             title="${info.usage_percentage}% used">
                            ${Math.round(info.usage_percentage)}%
                        </div>
                    </div>
                </div>
                ${info.created ? `
                <div class="mt-2">
                    <small class="text-muted">
                        <strong>Created:</strong> ${new Date(info.created).toLocaleDateString()}
                    </small>
                </div>
                ` : ''}
            `;
        } else {
            // Fallback to basic info if API fails
            const account = window.allEmailAccounts.find(acc => acc.email === email);
            if (account) {
                accountInfo.innerHTML = `
                    <div class="mb-2">
                        <strong>Email:</strong><br>
                        <span class="text-primary">${account.email}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Quota:</strong><br>
                        <span class="badge bg-info">${numberFormat(account.quota)} MB</span>
                    </div>
                    <div class="mb-2">
                        <strong>Usage:</strong><br>
                        <span class="badge bg-secondary">${numberFormat(account.usage)} MB</span>
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong><br>
                        ${account.suspended ? 
                            '<span class="badge bg-danger">Suspended</span>' : 
                            '<span class="badge bg-success">Active</span>'
                        }
                    </div>
                    <div class="mb-0">
                        <strong>Usage %:</strong><br>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar ${account.usage/account.quota > 0.8 ? 'bg-danger' : account.usage/account.quota > 0.6 ? 'bg-warning' : 'bg-success'}" 
                                 style="width: ${Math.min((account.usage/account.quota)*100, 100)}%">
                                ${Math.round((account.usage/account.quota)*100)}%
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Info dasar (detil tidak tersedia)
                        </small>
                    </div>
                `;
            } else {
                accountInfo.innerHTML = `
                    <div class="text-muted">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Informasi akun tidak ditemukan
                    </div>
                `;
            }
        }
    })
    .catch(error => {
        console.error('Error loading quota info:', error);
        
        // Show fallback info if available
        const account = window.allEmailAccounts.find(acc => acc.email === email);
        if (account) {
            accountInfo.innerHTML = `
                <div class="mb-2">
                    <strong>Email:</strong><br>
                    <span class="text-primary">${account.email}</span>
                </div>
                <div class="mb-2">
                    <strong>Quota:</strong><br>
                    <span class="badge bg-info">${numberFormat(account.quota)} MB</span>
                </div>
                <div class="mb-2">
                    <strong>Usage:</strong><br>
                    <span class="badge bg-secondary">${numberFormat(account.usage)} MB</span>
                </div>
                <div class="mb-2">
                    <strong>Status:</strong><br>
                    ${account.suspended ? 
                        '<span class="badge bg-danger">Suspended</span>' : 
                        '<span class="badge bg-success">Active</span>'
                    }
                </div>
                <div class="mb-0">
                    <strong>Usage %:</strong><br>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar ${account.usage/account.quota > 0.8 ? 'bg-danger' : account.usage/account.quota > 0.6 ? 'bg-warning' : 'bg-success'}" 
                             style="width: ${Math.min((account.usage/account.quota)*100, 100)}%">
                            ${Math.round((account.usage/account.quota)*100)}%
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Info dasar (API quota tidak tersedia)
                    </small>
                </div>
            `;
        } else {
            accountInfo.innerHTML = `
                <div class="text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Gagal memuat informasi quota
                    <br><small>${error.message}</small>
                    <br><small class="text-muted">Pastikan email address valid dan cPanel API dapat diakses</small>
                </div>
            `;
        }
    });
}

function getWarningClass(warningLevel) {
    switch(warningLevel) {
        case 'critical': return 'bg-danger';
        case 'warning': return 'bg-warning';
        case 'caution': return 'bg-info';
        default: return 'bg-success';
    }
}

function getProgressClass(usagePercentage) {
    if (usagePercentage >= 95) return 'bg-danger';
    if (usagePercentage >= 85) return 'bg-warning';
    if (usagePercentage >= 70) return 'bg-info';
    return 'bg-success';
}

function getWarningText(warningLevel) {
    switch(warningLevel) {
        case 'critical': return 'Critical - 95%+ used';
        case 'warning': return 'Warning - 85%+ used';
        case 'caution': return 'Caution - 70%+ used';
        default: return 'Good';
    }
}

function refreshQuotaInfo(email) {
    console.log('=== EMAIL REFRESH QUOTA INFO ===');
    console.log('Refreshing quota info for:', email);
    
    if (!isValidEmail(email)) {
        showAlert('error', 'Email tidak valid');
        return;
    }
    
    // Show loading state
    showAlert('info', 'Memuat informasi quota terbaru...');
    
    // Fetch detailed quota information from server
    const encodedEmail = encodeURIComponent(email);
    console.log('Fetching quota info for encoded email:', encodedEmail);
    
    fetch(`<?= base_url('email/get_quota_info/') ?>${encodedEmail}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Quota info response:', data);
        
        if (data.success && data.data) {
            const info = data.data;
            
            // Update the account in global array
            const accountIndex = window.allEmailAccounts.findIndex(acc => acc.email === email);
            if (accountIndex !== -1) {
                window.allEmailAccounts[accountIndex].quota = info.quota_mb;
                window.allEmailAccounts[accountIndex].usage = info.usage_mb;
                window.allEmailAccounts[accountIndex].suspended = info.suspended;
            }
            
            // Update the table display
            updateEmailTable(window.allEmailAccounts);
            
            // Show success message
            showAlert('success', `Quota info berhasil diupdate: ${info.usage_formatted} dari ${info.quota_formatted} (${info.usage_percentage}%)`);
        } else {
            showAlert('error', data.message || 'Gagal memuat informasi quota');
        }
    })
    .catch(error => {
        console.error('Error refreshing quota info:', error);
        
        // Show more specific error message
        let errorMessage = 'Terjadi kesalahan saat memuat informasi quota';
        if (error.message.includes('400')) {
            errorMessage = 'Format email tidak valid atau tidak ditemukan di cPanel';
        } else if (error.message.includes('500')) {
            errorMessage = 'Server error - cPanel API tidak dapat diakses';
        } else if (error.message.includes('404')) {
            errorMessage = 'Email account tidak ditemukan di cPanel';
        } else {
            errorMessage = 'Terjadi kesalahan saat memuat informasi quota: ' + error.message;
        }
        
        showAlert('error', errorMessage);
    });
}

function loadEmailFolder(folder) {
    console.log('=== EMAIL LOAD FOLDER ===');
    console.log('Loading folder:', folder, 'for account:', window.currentEmailAccount);
    
    window.currentFolder = folder;
    
    // Update folder selection
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.folder === folder) {
            item.classList.add('active');
        }
    });
    
    // Update current folder name
    const folderNames = {
        'INBOX': 'Inbox',
        'SENT': 'Sent',
        'DRAFTS': 'Drafts',
        'TRASH': 'Trash'
    };
    
    const currentFolderName = document.getElementById('currentFolderName');
    if (currentFolderName) {
        currentFolderName.textContent = folderNames[folder] || folder;
    }
    
    // Show loading state
    const emailList = document.getElementById('emailList');
    if (emailList) {
        emailList.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat email dari ${folderNames[folder]}...</p>
            </div>
        `;
    }
    
    // Hide email content
    const emailContentCard = document.getElementById('emailContentCard');
    if (emailContentCard) {
        emailContentCard.style.display = 'none';
    }
    
    // Simulate loading emails (in real implementation, this would be an AJAX call)
    setTimeout(() => {
        loadEmailsForFolder(folder);
    }, 1000);
}

function loadEmailsForFolder(folder) {
    console.log('=== EMAIL LOAD EMAILS FOR FOLDER ===');
    console.log('Loading emails for folder:', folder);
    
    const emailList = document.getElementById('emailList');
    if (!emailList) {
        console.error('Email list element not found');
        return;
    }
    
    // Simulate email data (in real implementation, this would come from server)
    const sampleEmails = generateSampleEmails(folder);
    
    if (sampleEmails.length === 0) {
        emailList.innerHTML = `
            <div class="text-center p-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>Tidak ada email di folder ini</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    sampleEmails.forEach((email, index) => {
        const isUnread = !email.read;
        const priorityClass = email.priority === 'high' ? 'border-start border-danger border-3' : 
                             email.priority === 'low' ? 'border-start border-info border-3' : '';
        
        html += `
            <div class="list-group-item list-group-item-action email-item ${priorityClass} ${isUnread ? 'bg-light' : ''}" 
                 onclick="loadEmailContent('${email.id}', '${folder}')" 
                 data-email-id="${email.id}">
                <div class="d-flex w-100 justify-content-between">
                    <div class="mb-1">
                        <h6 class="mb-1 ${isUnread ? 'fw-bold' : ''}">
                            ${email.from}
                            ${isUnread ? '<span class="badge bg-primary ms-2">New</span>' : ''}
                            ${email.priority === 'high' ? '<span class="badge bg-danger ms-1">High</span>' : ''}
                        </h6>
                        <p class="mb-1 ${isUnread ? 'fw-bold' : ''}">${email.subject}</p>
                        <small class="text-muted">${email.preview}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">${email.date}</small>
                        ${email.hasAttachment ? '<i class="fas fa-paperclip ms-2 text-muted"></i>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    emailList.innerHTML = html;
    
    // Update folder counts
    updateFolderCounts();
}

function generateSampleEmails(folder) {
    const sampleData = {
        'INBOX': [
            {
                id: 'inbox_1',
                from: 'noreply@github.com',
                subject: 'GitHub: Repository update notification',
                preview: 'Your repository has been updated with new commits...',
                date: '2 hours ago',
                read: false,
                priority: 'normal',
                hasAttachment: false
            },
            {
                id: 'inbox_2',
                from: 'support@cpanel.com',
                subject: 'Server maintenance scheduled',
                preview: 'We will be performing scheduled maintenance on...',
                date: '1 day ago',
                read: true,
                priority: 'high',
                hasAttachment: true
            },
            {
                id: 'inbox_3',
                from: 'newsletter@example.com',
                subject: 'Weekly Newsletter - Tech Updates',
                preview: 'Here are the latest technology updates and news...',
                date: '3 days ago',
                read: true,
                priority: 'low',
                hasAttachment: false
            }
        ],
        'SENT': [
            {
                id: 'sent_1',
                from: window.currentEmailAccount,
                subject: 'Re: Project proposal',
                preview: 'Thank you for your email regarding the project...',
                date: '1 hour ago',
                read: true,
                priority: 'normal',
                hasAttachment: true
            }
        ],
        'DRAFTS': [
            {
                id: 'draft_1',
                from: window.currentEmailAccount,
                subject: 'Meeting request for next week',
                preview: 'I would like to schedule a meeting for...',
                date: '2 days ago',
                read: true,
                priority: 'normal',
                hasAttachment: false
            }
        ],
        'TRASH': []
    };
    
    return sampleData[folder] || [];
}

function loadEmailContent(emailId, folder) {
    console.log('=== EMAIL LOAD CONTENT ===');
    console.log('Loading content for email:', emailId, 'in folder:', folder);
    
    window.currentEmailId = emailId;
    
    // Update email item selection
    document.querySelectorAll('.email-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.emailId === emailId) {
            item.classList.add('active');
        }
    });
    
    const emailContentCard = document.getElementById('emailContentCard');
    const emailContent = document.getElementById('emailContent');
    
    if (!emailContentCard || !emailContent) {
        console.error('Email content elements not found');
        return;
    }
    
    // Show loading state
    emailContent.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat konten email...</p>
        </div>
    `;
    
    emailContentCard.style.display = 'block';
    
    // Simulate loading email content
    setTimeout(() => {
        const sampleContent = generateEmailContent(emailId);
        emailContent.innerHTML = sampleContent;
    }, 500);
}

function generateEmailContent(emailId) {
    // Sample email content based on ID
    const contentData = {
        'inbox_1': {
            from: 'noreply@github.com',
            to: window.currentEmailAccount,
            subject: 'GitHub: Repository update notification',
            date: '2024-01-15 10:30:00',
            content: `
                <p>Hello,</p>
                <p>Your repository <strong>my-project</strong> has been updated with new commits:</p>
                <ul>
                    <li>Commit: <code>abc123</code> - Fixed bug in authentication</li>
                    <li>Commit: <code>def456</code> - Added new feature</li>
                </ul>
                <p>You can view the changes at: <a href="#">https://github.com/user/my-project</a></p>
                <p>Best regards,<br>GitHub Team</p>
            `
        },
        'inbox_2': {
            from: 'support@cpanel.com',
            to: window.currentEmailAccount,
            subject: 'Server maintenance scheduled',
            date: '2024-01-14 15:45:00',
            content: `
                <p>Dear Customer,</p>
                <p>We will be performing scheduled maintenance on our servers:</p>
                <p><strong>Date:</strong> January 20, 2024<br>
                <strong>Time:</strong> 02:00 - 04:00 UTC<br>
                <strong>Duration:</strong> 2 hours</p>
                <p>During this time, there may be brief interruptions to your services.</p>
                <p>We apologize for any inconvenience.</p>
                <p>Best regards,<br>cPanel Support Team</p>
            `
        }
    };
    
    const content = contentData[emailId] || {
        from: 'unknown@example.com',
        to: window.currentEmailAccount,
        subject: 'Sample Email',
        date: new Date().toISOString(),
        content: '<p>This is sample email content.</p>'
    };
    
    return `
        <div class="email-header mb-3">
            <div class="row">
                <div class="col-md-6">
                    <strong>From:</strong> ${content.from}
                </div>
                <div class="col-md-6 text-end">
                    <strong>Date:</strong> ${content.date}
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <strong>To:</strong> ${content.to}
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <strong>Subject:</strong> ${content.subject}
                </div>
            </div>
        </div>
        <hr>
        <div class="email-body">
            ${content.content}
        </div>
    `;
}

function updateFolderCounts() {
    // Update folder counts (in real implementation, this would come from server)
    document.getElementById('inboxCount').textContent = '3';
    document.getElementById('sentCount').textContent = '1';
    document.getElementById('draftsCount').textContent = '1';
    document.getElementById('trashCount').textContent = '0';
}

function refreshInbox() {
    console.log('=== EMAIL REFRESH INBOX ===');
    console.log('Refreshing inbox for:', window.currentEmailAccount);
    
    // Refresh account info first
    loadEmailAccountInfo(window.currentEmailAccount);
    
    // Then refresh folder
    loadEmailFolder(window.currentFolder);
}

function composeEmail() {
    console.log('=== EMAIL COMPOSE ===');
    showAlert('info', 'Fitur compose email akan tersedia dalam update selanjutnya');
}

function replyEmail() {
    console.log('=== EMAIL REPLY ===');
    showAlert('info', 'Fitur reply email akan tersedia dalam update selanjutnya');
}

function deleteEmail() {
    console.log('=== EMAIL DELETE ===');
    if (!window.currentEmailId) {
        showAlert('error', 'Tidak ada email yang dipilih untuk dihapus');
        return;
    }
    
    if (confirm('Apakah Anda yakin ingin menghapus email ini?')) {
        showAlert('success', 'Email berhasil dihapus');
        loadEmailFolder(window.currentFolder);
        document.getElementById('emailContentCard').style.display = 'none';
    }
}

// Folder click handlers
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for folder navigation
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-folder]')) {
            e.preventDefault();
            const folder = e.target.closest('[data-folder]').dataset.folder;
            loadEmailFolder(folder);
        }
    });
});

// Checkbox management functions
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const emailCheckboxes = document.querySelectorAll('.email-checkbox:not(:disabled)');
    
    if (selectAllCheckbox) {
        emailCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateBulkDeleteButton();
    }
}

function updateBulkDeleteButton() {
    const emailCheckboxes = document.querySelectorAll('.email-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    // Filter only valid emails using helper function
    const validEmails = getValidEmails(emailCheckboxes);
    
    if (bulkDeleteBtn) {
        if (validEmails.length > 0) {
            bulkDeleteBtn.style.display = 'inline-block';
            bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Hapus Terpilih (${validEmails.length})`;
            bulkDeleteBtn.disabled = false;
        } else {
            bulkDeleteBtn.style.display = 'none';
            bulkDeleteBtn.disabled = true;
        }
    }
    
    // Update select all checkbox state
    if (selectAllCheckbox) {
        const allValidCheckboxes = document.querySelectorAll('.email-checkbox:not(:disabled)');
        const checkedValidCheckboxes = document.querySelectorAll('.email-checkbox:not(:disabled):checked');
        selectAllCheckbox.checked = allValidCheckboxes.length > 0 && allValidCheckboxes.length === checkedValidCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedValidCheckboxes.length > 0 && checkedValidCheckboxes.length < allValidCheckboxes.length;
    }
    
    console.log('Bulk delete button updated - Selected:', emailCheckboxes.length, 'Valid:', validEmails.length);
}

function bulkDelete() {
    const emailCheckboxes = document.querySelectorAll('.email-checkbox:checked');
    
    if (emailCheckboxes.length === 0) {
        showAlert('error', 'Tidak ada akun email yang dipilih untuk dihapus');
        return;
    }
    
    // Check if bulk delete button is disabled
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (bulkDeleteBtn && bulkDeleteBtn.disabled) {
        showAlert('error', 'Tidak ada email yang valid untuk dihapus');
        return;
    }
    
    const selectedEmails = getValidEmails(emailCheckboxes);
    const selectedCount = selectedEmails.length;
    
    if (selectedCount === 0) {
        showAlert('error', 'Tidak ada email yang valid untuk dihapus');
        return;
    }
    
    console.log('Bulk delete - Valid emails to delete:', selectedEmails);
    
    // Update modal content
    const selectedCountElement = document.getElementById('selectedCount');
    const selectedEmailsList = document.getElementById('selectedEmailsList');
    
    if (selectedCountElement) {
        selectedCountElement.textContent = selectedCount;
    }
    
    if (selectedEmailsList) {
        let html = '';
        selectedEmails.forEach((email, index) => {
            if (isValidEmail(email)) {
                html += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td><i class="fas fa-envelope me-2 text-primary"></i>${email}</td>
                    </tr>
                `;
            }
        });
        selectedEmailsList.innerHTML = html;
    }
    
    // Show bulk delete modal
    const bulkDeleteModal = document.getElementById('bulkDeleteModal');
    if (bulkDeleteModal) {
        const modal = new bootstrap.Modal(bulkDeleteModal);
        modal.show();
    } else {
        console.error('Bulk delete modal not found');
        showAlert('error', 'Modal konfirmasi bulk delete tidak ditemukan');
    }
    
    console.log('Bulk delete modal shown for', selectedCount, 'emails');
}

function confirmBulkDelete() {
    const emailCheckboxes = document.querySelectorAll('.email-checkbox:checked');
    const selectedEmails = getValidEmails(emailCheckboxes);
    
    if (selectedEmails.length === 0) {
        showAlert('error', 'Tidak ada akun email yang valid untuk dihapus');
        return;
    }
    
    console.log('=== EMAIL BULK DELETE ===');
    console.log('Deleting emails:', selectedEmails);
    
    // Show loading state
    const confirmBulkDeleteBtn = document.getElementById('confirmBulkDelete');
    if (confirmBulkDeleteBtn) {
        confirmBulkDeleteBtn.disabled = true;
        confirmBulkDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
    }
    
    // Disable all checkboxes during deletion
    const allCheckboxes = document.querySelectorAll('.email-checkbox');
    allCheckboxes.forEach(checkbox => {
        checkbox.disabled = true;
    });
    
    // Send bulk delete request
    fetch('<?= base_url('email/bulk_delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            emails: selectedEmails
        })
    })
    .then(response => {
        console.log('Bulk delete response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        console.log('Bulk delete raw response:', data);
        try {
            const jsonData = JSON.parse(data);
            console.log('Bulk delete parsed response:', jsonData);
            
            if (jsonData.success) {
                const message = `Berhasil menghapus ${jsonData.deleted_count} dari ${jsonData.total_count} akun email`;
                if (jsonData.failed_count > 0) {
                    showAlert('warning', message + `. Gagal menghapus ${jsonData.failed_count} akun email.`);
                } else {
                    showAlert('success', message);
                }
                
                // Reset checkboxes and select all checkbox
                const selectAllCheckbox = document.getElementById('selectAll');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
                
                // Clear all checkboxes
                const allCheckboxes = document.querySelectorAll('.email-checkbox');
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                updateBulkDeleteButton();
                
                // Refresh the table
                checkAccounts();
                
                // Close modal
                const bulkDeleteModal = document.getElementById('bulkDeleteModal');
                if (bulkDeleteModal) {
                    const modal = bootstrap.Modal.getInstance(bulkDeleteModal);
                    if (modal) {
                        modal.hide();
                    }
                }
            } else {
                showAlert('error', 'Gagal menghapus akun email: ' + jsonData.message);
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            showAlert('error', 'Response tidak valid JSON: ' + e.message);
            
            // Close modal on error
            const bulkDeleteModal = document.getElementById('bulkDeleteModal');
            if (bulkDeleteModal) {
                const modal = bootstrap.Modal.getInstance(bulkDeleteModal);
                if (modal) {
                    modal.hide();
                }
            }
        }
    })
    .catch(error => {
        console.error('Bulk delete error:', error);
        showAlert('error', 'Terjadi kesalahan saat menghapus: ' + error.message);
        
        // Close modal on error
        const bulkDeleteModal = document.getElementById('bulkDeleteModal');
        if (bulkDeleteModal) {
            const modal = bootstrap.Modal.getInstance(bulkDeleteModal);
            if (modal) {
                modal.hide();
            }
        }
    })
    .finally(() => {
        // Reset button state
        if (confirmBulkDeleteBtn) {
            confirmBulkDeleteBtn.disabled = false;
            confirmBulkDeleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Hapus Semua';
        }
        
        // Re-enable all checkboxes
        allCheckboxes.forEach(checkbox => {
            checkbox.disabled = false;
        });
    });
}

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
            <td colspan="7" class="text-center">
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
                        <td colspan="7" class="text-center text-danger">
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
                    <td colspan="7" class="text-center text-danger">
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
                <td colspan="7" class="text-center text-danger">
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
    
    // Store all accounts in global variable for filtering
    if (Array.isArray(accounts)) {
        window.allEmailAccounts = accounts;
    }
    
    if (!Array.isArray(accounts)) {
        console.error('Accounts is not an array:', accounts);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger">
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
                <td colspan="7" class="text-center text-muted">
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
        
        const emailValue = account.email || '';
        const emailIsValid = isValidEmail(emailValue);
        
        html += `
            <tr>
                <td class="text-center">
                    <input type="checkbox" class="email-checkbox" value="${emailValue}" onchange="updateBulkDeleteButton()" ${!emailIsValid ? 'disabled' : ''}>
                </td>
                <td class="text-center">${index + 1}</td>
                <td>
                    <i class="fas fa-envelope me-2 text-primary"></i>
                    <strong>${emailIsValid ? emailValue : 'N/A'}</strong>
                </td>
                <td class="text-center">
                    <span class="badge bg-info" title="Total quota">${numberFormat(account.quota || 0)} MB</span>
                    ${account.quota > 0 ? `
                        <br><small class="text-muted">
                            ${((account.usage || 0) / account.quota * 100).toFixed(1)}% used
                        </small>
                    ` : ''}
                </td>
                <td class="text-center">
                    <span class="text-primary" title="Used space">${numberFormat(account.usage || 0)} MB</span>
                    ${account.quota > 0 ? `
                        <br><small class="text-success">
                            ${numberFormat(Math.max(0, account.quota - (account.usage || 0)))} MB free
                        </small>
                    ` : ''}
                </td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info" 
                                onclick="showAccountDetails('${emailValue}')"
                                title="Detail" ${!emailIsValid ? 'disabled' : ''}>
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-primary" 
                                onclick="openEmailInbox('${emailValue}')"
                                title="Buka Inbox" ${!emailIsValid ? 'disabled' : ''}>
                            <i class="fas fa-inbox"></i>
                        </button>
                        <button type="button" class="btn btn-info" 
                                onclick="refreshQuotaInfo('${emailValue}')"
                                title="Refresh Quota Info" ${!emailIsValid ? 'disabled' : ''}>
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <a href="<?= base_url('email/edit/') ?>${encodeURIComponent(emailValue)}" 
                            class="btn btn-warning" title="Edit" ${!emailIsValid ? 'style="pointer-events: none; opacity: 0.6;"' : ''}>
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger" 
                                onclick="deleteAccount('${emailValue}')"
                                title="Hapus" ${!emailIsValid ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    console.log('Table updated successfully with', accounts.length, 'accounts');
    
    // Update checkbox states after table update
    updateBulkDeleteButton();
    
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
    
    if (!isValidEmail(email)) {
        console.error('No valid email provided for details');
        showAlert('error', 'Email tidak valid untuk ditampilkan detailnya');
        return;
    }
    
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
    
    if (!isValidEmail(email)) {
        console.error('No valid email provided for deletion');
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
        
        if (isValidEmail(window.deleteEmailAddress)) {
            const deleteUrl = '<?= base_url('email/delete/') ?>' + encodeURIComponent(window.deleteEmailAddress);
            console.log('Redirecting to:', deleteUrl);
            window.location.href = deleteUrl;
        } else {
            console.error('No valid email address to delete');
            showAlert('error', 'Tidak ada email yang valid untuk dihapus');
        }
    });
} else {
    console.error('Confirm delete button not found');
}

// Confirm bulk delete action
const confirmBulkDeleteElement = document.getElementById('confirmBulkDelete');
if (confirmBulkDeleteElement) {
    confirmBulkDeleteElement.addEventListener('click', function() {
        confirmBulkDelete();
    });
} else {
    console.error('Confirm bulk delete button not found');
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
    
    let alertClass, iconClass;
    
    switch(type) {
        case 'success':
            alertClass = 'alert-success';
            iconClass = 'fas fa-check-circle';
            break;
        case 'warning':
            alertClass = 'alert-warning';
            iconClass = 'fas fa-exclamation-triangle';
            break;
        case 'error':
        default:
            alertClass = 'alert-danger';
            iconClass = 'fas fa-exclamation-circle';
            break;
    }
    
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
        'accountDetailsContent',
        'bulkDeleteModal',
        'confirmBulkDelete',
        'selectAll'
    ];
    
    const missingElements = requiredElements.filter(id => !document.getElementById(id));
    if (missingElements.length > 0) {
        console.warn('Missing required elements:', missingElements);
    } else {
        console.log('All required elements found');
    }
    
    // Initialize global variables
    window.deleteEmailAddress = '';
    
    // Initialize checkbox states
    updateBulkDeleteButton();
    
    // Add event listeners for checkbox changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('email-checkbox') || e.target.id === 'selectAll') {
            updateBulkDeleteButton();
        }
    });
    
    console.log('Email page initialization complete');
});
</script>