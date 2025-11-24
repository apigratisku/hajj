<!-- Content Body -->
<div class="content-body">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-body">
                    <form method="get" action="<?= base_url('laporan') ?>">
                        <div class="filter-single-row">
                            <!-- Tanggal Mulai -->
                            <div class="filter-item">
                                <label class="form-label small text-muted mb-1">
                                    <i class="fas fa-calendar-alt me-1"></i> Tanggal Mulai
                                </label>
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                    value="<?= html_escape($filters['start_date']) ?>">
                            </div>
                            
                            <!-- Tanggal Selesai -->
                            <div class="filter-item">
                                <label class="form-label small text-muted mb-1">
                                    <i class="fas fa-calendar-check me-1"></i> Tanggal Selesai
                                </label>
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="<?= html_escape($filters['end_date']) ?>">
                            </div>
                            
                            <!-- Flag Doc -->
                            <div class="filter-item">
                                <label class="form-label small text-muted mb-1">
                                    <i class="fas fa-tag me-1"></i> Flag Doc
                                </label>
                                <select name="flag_doc" class="form-select form-select-sm">
                                    <option value="">Semua Flag Doc</option>
                                    <?php foreach ($flag_doc_list as $flag): ?>
                                        <option value="<?= html_escape($flag->flag_doc) ?>"
                                            <?= $filters['flag_doc'] === $flag->flag_doc ? 'selected' : '' ?>>
                                            <?= html_escape($flag->flag_doc) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Nama Travel Dropdown -->
                            <div class="filter-item filter-travel-dropdown">
                                <label class="form-label small text-muted mb-1">
                                    <i class="fas fa-building me-1"></i> Nama Travel
                                </label>
                                <div class="travel-dropdown-wrapper">
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 text-start travel-toggle-btn" 
                                        id="travelToggleBtn" onclick="toggleTravelPanel()">
                                        <span class="travel-selected-count" id="travelSelectedCount">
                                            <?= !empty($filters['nama_travel']) ? count($filters['nama_travel']) : 0 ?> dipilih
                                        </span>
                                        <i class="fas fa-chevron-down float-end mt-1"></i>
                                    </button>
                                    
                                    <div class="travel-panel collapse" id="travelPanel">
                                        <div class="travel-panel-content">
                                            <div class="form-check mb-2 border-bottom pb-2">
                                                <input class="form-check-input" type="checkbox" id="selectAllTravel" onchange="toggleAllTravel(this)">
                                                <label class="form-check-label fw-bold" for="selectAllTravel">
                                                    Pilih Semua
                                                </label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input travel-checkbox" type="checkbox" name="nama_travel[]" 
                                                    value="null" id="travel_null"
                                                    <?= in_array('null', $filters['nama_travel']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="travel_null">
                                                    Tanpa Travel
                                                </label>
                                            </div>
                                            <?php if (!empty($travel_list)): ?>
                                                <?php foreach ($travel_list as $travel): ?>
                                                    <div class="form-check mb-1">
                                                        <input class="form-check-input travel-checkbox" type="checkbox" name="nama_travel[]" 
                                                            value="<?= html_escape($travel->nama_travel) ?>" 
                                                            id="travel_<?= md5($travel->nama_travel) ?>"
                                                            <?= in_array($travel->nama_travel, $filters['nama_travel']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="travel_<?= md5($travel->nama_travel) ?>">
                                                            <?= html_escape($travel->nama_travel) ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tombol Action -->
                            <div class="filter-item filter-actions">
                                <label class="form-label small text-muted mb-1 d-block">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-brown btn-sm">
                                        <i class="fas fa-filter me-1"></i> Tampilkan
                                    </button>
                                    <a href="<?= base_url('laporan') ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <p class="text-muted mb-1">Total Todo</p>
                            <h4 class="text-primary"><?= number_format($totals['todo']) ?></h4>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <p class="text-muted mb-1">Total Already</p>
                            <h4 class="text-warning"><?= number_format($totals['already']) ?></h4>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <p class="text-muted mb-1">Total Done</p>
                            <h4 class="text-success"><?= number_format($totals['done']) ?></h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Total Keseluruhan</p>
                            <h4 class="text-dark"><?= number_format($totals['total']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($summary_by_travel)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card mobile-card">
                    <div class="card-body">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Tidak ada data untuk rentang tanggal ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($summary_by_travel as $travel_name => $travel_data): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card mobile-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-building me-2"></i>
                                <?= html_escape($travel_name) ?>
                                <span class="badge bg-light text-dark ms-2">
                                    Total: <?= number_format($travel_data['totals']['total']) ?>
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="60">No</th>
                                            <th class="text-center">Tanggal Upload</th>
                                            <th class="text-center"> <?= html_escape($travel_name) ?></th>
                                            <th class="text-center">Todo</th>
                                            <th class="text-center">Already</th>
                                            <th class="text-center">Done</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center" width="120">Sudah Report</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($travel_data['rows'])): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">Tidak ada data untuk travel ini.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($travel_data['rows'] as $index => $row): ?>
                                                <?php
                                                $travel_value = !empty($row->nama_travel) ? $row->nama_travel : 'null';
                                                $is_checked = isset($row->sudah_report) && $row->sudah_report == 1;
                                                ?>
                                                <tr class="<?= ((int)$row->total === 0) ? 'table-warning' : '' ?>">
                                                    <td class="text-center"><?= $index + 1 ?></td>
                                                    <td class="text-center">
                                                        <?= date('d/m/Y', strtotime($row->tanggal_upload)) ?>
                                                    </td>
                                                    <td class="text-left"><?= html_escape($row->flag_doc) ?></td>
                                                    <td class="text-center text-primary"><?= (int) $row->todo_count ?></td>
                                                    <td class="text-center text-warning"><?= (int) $row->already_count ?></td>
                                                    <td class="text-center text-success"><?= (int) $row->done_count ?></td>
                                                    <td class="text-center fw-bold"><?= (int) $row->total ?></td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input 
                                                                class="form-check-input sudah-report-checkbox" 
                                                                type="checkbox" 
                                                                id="sudah_report_<?= md5($row->flag_doc . '|' . $travel_value . '|' . $row->tanggal_upload) ?>"
                                                                data-flag-doc="<?= html_escape($row->flag_doc) ?>"
                                                                data-nama-travel="<?= html_escape($travel_value) ?>"
                                                                data-tanggal-upload="<?= html_escape($row->tanggal_upload) ?>"
                                                                <?= $is_checked ? 'checked' : '' ?>
                                                                onchange="updateSudahReport(this)">
                                                            <label class="form-check-label ms-2" for="sudah_report_<?= md5($row->flag_doc . '|' . $travel_value . '|' . $row->tanggal_upload) ?>">
                                                                <small class="text-muted">Sudah</small>
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-info fw-bold">
                                                <td colspan="3" class="text-end">Total </td>
                                                <td class="text-center text-primary"><?= number_format($travel_data['totals']['todo']) ?></td>
                                                <td class="text-center text-warning"><?= number_format($travel_data['totals']['already']) ?></td>
                                                <td class="text-center text-success"><?= number_format($travel_data['totals']['done']) ?></td>
                                                <td class="text-center"><?= number_format($travel_data['totals']['total']) ?></td>
                                                <td></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .table-warning {
        background: #fff8e1 !important;
    }
    .travel-checkbox {
        cursor: pointer;
    }
    .travel-checkbox:checked + label {
        font-weight: 600;
        color: var(--primary-color);
    }
    .sudah-report-checkbox {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }
    .sudah-report-checkbox:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }
    .sudah-report-checkbox.loading {
        opacity: 0.5;
    }
    
    /* Filter Single Row Layout */
    .filter-single-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }
    
    .filter-item {
        flex: 1;
        min-width: 150px;
    }
    
    .filter-travel-dropdown {
        position: relative;
        min-width: 200px;
    }
    
    .filter-actions {
        flex: 0 0 auto;
        min-width: auto;
    }
    
    /* Travel Dropdown */
    .travel-dropdown-wrapper {
        position: relative;
    }
    
    .travel-toggle-btn {
        position: relative;
        transition: all 0.3s ease;
    }
    
    .travel-toggle-btn i {
        transition: transform 0.3s ease;
    }
    
    .travel-toggle-btn.active i {
        transform: rotate(180deg);
    }
    
    .travel-panel {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1000;
        margin-top: 5px;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, opacity 0.3s ease;
        opacity: 0;
    }
    
    .travel-panel.show {
        max-height: 400px;
        opacity: 1;
    }
    
    .travel-panel-content {
        padding: 12px;
        max-height: 380px;
        overflow-y: auto;
    }
    
    .travel-panel-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .travel-panel-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .travel-panel-content::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .travel-panel-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .travel-selected-count {
        font-weight: 500;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .filter-single-row {
            flex-direction: column;
        }
        
        .filter-item {
            width: 100%;
            min-width: 100%;
        }
        
        .filter-actions {
            width: 100%;
        }
        
        .travel-panel {
            position: relative;
            margin-top: 10px;
        }
    }
</style>

<script>
// Toggle Travel Panel
function toggleTravelPanel() {
    const panel = document.getElementById('travelPanel');
    const btn = document.getElementById('travelToggleBtn');
    
    if (panel && btn) {
        panel.classList.toggle('show');
        btn.classList.toggle('active');
    }
}

// Close panel when clicking outside
document.addEventListener('click', function(event) {
    const panel = document.getElementById('travelPanel');
    const btn = document.getElementById('travelToggleBtn');
    const wrapper = document.querySelector('.travel-dropdown-wrapper');
    
    if (panel && btn && wrapper) {
        if (!wrapper.contains(event.target) && panel.classList.contains('show')) {
            panel.classList.remove('show');
            btn.classList.remove('active');
        }
    }
});

// Update travel selected count
function updateTravelCount() {
    const checkboxes = document.querySelectorAll('.travel-checkbox:checked');
    const countElement = document.getElementById('travelSelectedCount');
    
    if (countElement) {
        const count = checkboxes.length;
        countElement.textContent = count > 0 ? count + ' dipilih' : '0 dipilih';
    }
}

function toggleAllTravel(checkbox) {
    const travelCheckboxes = document.querySelectorAll('.travel-checkbox');
    travelCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateTravelCount();
}

function updateSudahReport(checkbox) {
    const flagDoc = checkbox.getAttribute('data-flag-doc');
    const namaTravel = checkbox.getAttribute('data-nama-travel');
    const tanggalUpload = checkbox.getAttribute('data-tanggal-upload');
    const sudahReport = checkbox.checked ? 1 : 0;
    
    // Disable checkbox and show loading state
    checkbox.disabled = true;
    checkbox.classList.add('loading');
    
    // Store original state for revert on error
    const originalChecked = checkbox.checked;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('flag_doc', flagDoc);
    formData.append('nama_travel', namaTravel);
    formData.append('tanggal_upload', tanggalUpload);
    formData.append('sudah_report', sudahReport);
    
    // Send AJAX request
    fetch('<?= base_url('laporan/update_sudah_report') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Re-enable checkbox
        checkbox.disabled = false;
        checkbox.classList.remove('loading');
        
        if (data.status) {
            // Show success message
            showAlert('success', data.message);
        } else {
            // Revert checkbox state on error
            checkbox.checked = !originalChecked;
            showAlert('danger', data.message || 'Gagal mengupdate status sudah report');
        }
    })
    .catch(error => {
        // Re-enable checkbox
        checkbox.disabled = false;
        checkbox.classList.remove('loading');
        
        // Revert checkbox state on error
        checkbox.checked = !originalChecked;
        
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat mengupdate status. Silakan coba lagi.');
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert alert at the top of content-body
    const contentBody = document.querySelector('.content-body');
    if (contentBody) {
        const existingAlert = contentBody.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        contentBody.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            const alert = contentBody.querySelector('.alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
}

// Update "Pilih Semua" state when individual checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    const travelCheckboxes = document.querySelectorAll('.travel-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllTravel');
    
    // Add event listener to each travel checkbox
    travelCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = Array.from(travelCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(travelCheckboxes).some(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
            
            // Update travel count
            updateTravelCount();
        });
    });
    
    // Set initial state
    const allChecked = Array.from(travelCheckboxes).every(cb => cb.checked);
    const someChecked = Array.from(travelCheckboxes).some(cb => cb.checked);
    selectAllCheckbox.checked = allChecked;
    selectAllCheckbox.indeterminate = someChecked && !allChecked;
    
    // Update initial travel count
    updateTravelCount();
});
</script>

