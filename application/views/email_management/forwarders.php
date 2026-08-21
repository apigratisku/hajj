<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-share-alt me-2"></i>Manajemen Forwarder
                    </h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary align-self-center">
                            <i class="fas fa-globe me-1"></i><?= htmlspecialchars($domain ?: 'Unknown') ?>
                        </span>
                        <?php if ($this->session->userdata('role') == 'admin'): ?>
                        <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()" id="bulkDeleteBtn" style="display: none;">
                            <i class="fas fa-trash"></i> Hapus Terpilih
                        </button>
                        <?php endif; ?>
                        <a href="<?= base_url('email') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Email
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

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gagal mengambil daftar forwarder: <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-3 g-2 align-items-center">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="forwarderSearchInput"
                                       placeholder="Cari forwarder address..." onkeyup="filterForwarderTable()">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <?php if (isset($total_forwarders)): ?>
                            <span class="badge bg-info fs-6 px-3 py-2">
                                <i class="fas fa-filter me-1"></i>
                                <?= number_format($total_forwarders) ?> forwarder sesuai data peserta
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="forwarder-table">
                            <thead class="table-dark">
                                <tr>
                                    <?php if ($this->session->userdata('role') == 'admin'): ?>
                                    <th class="text-center" width="40">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" title="Pilih Semua">
                                    </th>
                                    <?php endif; ?>
                                    <th class="text-center" width="50">#</th>
                                    <th>Forwarder Address</th>
                                    <th>Forward To</th>
                                    <th class="text-center" width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($forwarders)): ?>
                                    <tr>
                                        <td colspan="<?= $this->session->userdata('role') == 'admin' ? 5 : 4 ?>" class="text-center text-muted py-4">
                                            <i class="fas fa-share-alt fa-2x mb-2"></i>
                                            <br>Tidak ada forwarder yang cocok dengan data peserta untuk domain <?= htmlspecialchars($domain ?: '') ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = (isset($current_page) && $current_page > 1) ? (($current_page - 1) * $per_page) + 1 : 1; foreach ($forwarders as $fwd): ?>
                                        <?php
                                        $dest = isset($fwd['dest']) ? $fwd['dest'] : '';
                                        $forward = isset($fwd['forward']) ? $fwd['forward'] : '';
                                        if ($dest === '' || $forward === '') {
                                            continue;
                                        }
                                        $deleteUrl = base_url('email/delete_forwarder/' . urlencode($dest)) . '?forwarder=' . urlencode($forward);
                                        ?>
                                        <tr>
                                            <?php if ($this->session->userdata('role') == 'admin'): ?>
                                            <td class="text-center">
                                                <input type="checkbox" class="forwarder-checkbox" data-dest="<?= htmlspecialchars($dest) ?>" data-forward="<?= htmlspecialchars($forward) ?>" onchange="updateBulkDeleteButton()">
                                            </td>
                                            <?php endif; ?>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td>
                                                <i class="fas fa-envelope me-2 text-primary"></i>
                                                <strong><?= htmlspecialchars($dest) ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-arrow-right me-2 text-muted"></i>
                                                <?= htmlspecialchars($forward) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($this->session->userdata('role') == 'admin'): ?>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-forwarder"
                                                        onclick="deleteForwarder('<?= addslashes($deleteUrl) ?>', '<?= addslashes(htmlspecialchars($dest)) ?>', '<?= addslashes(htmlspecialchars($forward)) ?>')"
                                                        title="Hapus forwarder">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php else: ?>
                                                <span class="text-muted"><small>Tidak ada akses</small></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <nav aria-label="Pagination forwarder" class="mt-3">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=' . ($current_page - 1)) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <?php
                            // Tampilkan jendela halaman (maks 5 nomor) di sekitar halaman aktif
                            $window = 2;
                            $start = max(1, $current_page - $window);
                            $end = min($total_pages, $current_page + $window);
                            if ($start > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=1') ?>">1</a>
                            </li>
                            <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=' . $i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=' . $total_pages) ?>"><?= $total_pages ?></a>
                            </li>
                            <?php endif; ?>

                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=' . ($current_page + 1)) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                        <p class="text-center text-muted small mt-2 mb-0">
                            Menampilkan <?= number_format($offset + 1) ?> - <?= number_format(min($offset + $per_page, $total_forwarders)) ?> dari <?= number_format($total_forwarders) ?> forwarder sesuai data peserta
                        </p>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<?php if ($this->session->userdata('role') == 'admin'): ?>
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Massal Forwarder
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    Anda akan menghapus <strong id="selectedCount">0</strong> forwarder berikut secara massal. Tindakan ini tidak dapat dibatalkan.
                </div>
                
                <div class="table-responsive" style="max-height: 300px;">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr class="table-dark">
                                <th class="text-center" width="50">#</th>
                                <th>Alamat Asal (Dest)</th>
                                <th>Alamat Tujuan (Forward To)</th>
                            </tr>
                        </thead>
                        <tbody id="selectedForwardersList">
                            <!-- List email dynamic -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDelete" onclick="confirmBulkDelete()">
                    <i class="fas fa-trash me-1"></i> Ya, Hapus Semua Terpilih
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function deleteForwarder(url, dest, forward) {
    if (confirm('Apakah Anda yakin ingin menghapus forwarder ini?\n\n' + dest + '  ->  ' + forward + '\n\nTindakan ini tidak dapat dibatalkan.')) {
        window.location.href = url;
    }
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.forwarder-checkbox');
    
    if (selectAllCheckbox) {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateBulkDeleteButton();
    }
}

function updateBulkDeleteButton() {
    const checkboxes = document.querySelectorAll('.forwarder-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    if (bulkDeleteBtn) {
        if (checkboxes.length > 0) {
            bulkDeleteBtn.style.display = 'inline-block';
            bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Hapus Terpilih (${checkboxes.length})`;
            bulkDeleteBtn.disabled = false;
        } else {
            bulkDeleteBtn.style.display = 'none';
            bulkDeleteBtn.disabled = true;
        }
    }
    
    // Update select all checkbox state
    if (selectAllCheckbox) {
        const allCheckboxes = document.querySelectorAll('.forwarder-checkbox');
        selectAllCheckbox.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkboxes.length;
        selectAllCheckbox.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }
}

function bulkDelete() {
    const checkboxes = document.querySelectorAll('.forwarder-checkbox:checked');
    
    if (checkboxes.length === 0) {
        showAlert('error', 'Tidak ada forwarder yang dipilih untuk dihapus');
        return;
    }
    
    // Update modal content
    const selectedCountElement = document.getElementById('selectedCount');
    const selectedList = document.getElementById('selectedForwardersList');
    
    if (selectedCountElement) {
        selectedCountElement.textContent = checkboxes.length;
    }
    
    if (selectedList) {
        let html = '';
        checkboxes.forEach((checkbox, index) => {
            const dest = checkbox.getAttribute('data-dest');
            const forward = checkbox.getAttribute('data-forward');
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${escapeHtml(dest)}</strong></td>
                    <td><i class="fas fa-arrow-right me-2 text-muted"></i>${escapeHtml(forward)}</td>
                </tr>
            `;
        });
        selectedList.innerHTML = html;
    }
    
    // Show bulk delete modal
    const bulkDeleteModal = document.getElementById('bulkDeleteModal');
    if (bulkDeleteModal) {
        const modal = new bootstrap.Modal(bulkDeleteModal);
        modal.show();
    }
}

function confirmBulkDelete() {
    const checkboxes = document.querySelectorAll('.forwarder-checkbox:checked');
    const selectedForwarders = [];
    
    checkboxes.forEach(checkbox => {
        selectedForwarders.push({
            dest: checkbox.getAttribute('data-dest'),
            forward: checkbox.getAttribute('data-forward')
        });
    });
    
    if (selectedForwarders.length === 0) {
        showAlert('error', 'Tidak ada forwarder yang terpilih');
        return;
    }
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmBulkDelete');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
    }
    
    // Disable checkboxes
    const allCheckboxes = document.querySelectorAll('.forwarder-checkbox, #selectAll');
    allCheckboxes.forEach(cb => cb.disabled = true);
    
    // Send request
    fetch('<?= base_url('email/bulk_delete_forwarders') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            forwarders: selectedForwarders
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const message = data.message || 'Bulk delete sukses!';
            if (data.failed_count > 0) {
                showAlert('warning', message);
            } else {
                showAlert('success', message);
            }
            
            // Reset checkboxes and select all checkbox
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
            
            const allCheckboxes = document.querySelectorAll('.forwarder-checkbox');
            allCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            updateBulkDeleteButton();
            
            // Reload page or table after brief timeout
            setTimeout(() => {
                location.reload();
            }, 1500);
            
            // Close modal
            const bulkDeleteModal = document.getElementById('bulkDeleteModal');
            if (bulkDeleteModal) {
                const modal = bootstrap.Modal.getInstance(bulkDeleteModal);
                if (modal) modal.hide();
            }
        } else {
            showAlert('error', data.message || 'Gagal menghapus forwarder');
            resetModalState();
        }
    })
    .catch(error => {
        console.error('Error bulk delete forwarders:', error);
        showAlert('error', 'Terjadi kesalahan koneksi atau internal server.');
        resetModalState();
    });
}

function resetModalState() {
    const confirmBtn = document.getElementById('confirmBulkDelete');
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-trash me-1"></i> Ya, Hapus Semua Terpilih';
    }
    const allCheckboxes = document.querySelectorAll('.forwarder-checkbox, #selectAll');
    allCheckboxes.forEach(cb => cb.disabled = false);
    
    const bulkDeleteModal = document.getElementById('bulkDeleteModal');
    if (bulkDeleteModal) {
        const modal = bootstrap.Modal.getInstance(bulkDeleteModal);
        if (modal) modal.hide();
    }
}

// Show alert message like index.php
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

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function filterForwarderTable() {
    const input = document.getElementById('forwarderSearchInput');
    const query = input.value.toLowerCase().trim();
    const table = document.getElementById('forwarder-table');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        // Skip check for no-data row
        if (row.cells.length === 1) return;
        const text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(query) > -1 ? '' : 'none';
    });
}
</script>
