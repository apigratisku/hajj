<!-- Export Modal for Arsip -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-brown text-white">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-file-excel"></i> Export Data Arsip Peserta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm" action="<?= base_url('database/export_arsip') ?>" method="GET">
                    <div class="mb-3">
                        <label for="export_format" class="form-label">
                            <i class="fas fa-file"></i> Format Export
                        </label>
                        <select class="form-select" id="export_format" name="format" required>
                            <option value="">Pilih Format</option>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                        <div class="form-text">Pilih format file yang akan di-export</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_flag_doc" class="form-label">
                            <i class="fas fa-tag"></i> Pilih Flag Dokumen
                        </label>
                        
                        <!-- Search input for flag dokumen -->
                        <div class="mb-2">
                            <input type="text" class="form-control" id="flagDocSearch" placeholder="🔍 Cari flag dokumen..." style="font-size: 0.9rem;">
                        </div>
                        
                        <!-- Flag dokumen selection area -->
                        <div class="flag-doc-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 10px; background-color: #f8f9fa;">
                            
                            <!-- Select All option -->
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="selectAllFlagDoc">
                                <label class="form-check-label fw-bold" for="selectAllFlagDoc">
                                    <i class="fas fa-check-double"></i> Pilih Semua
                                </label>
                            </div>
                            
                            <hr class="my-2">
                            
                            <!-- Individual flag dokumen options -->
                            <div class="form-check mb-1">
                                <input class="form-check-input flag-doc-checkbox" type="checkbox" name="flag_doc[]" value="" id="flag_doc_all">
                                <label class="form-check-label" for="flag_doc_all">
                                    <i class="fas fa-database"></i> Semua Data Arsip
                                </label>
                            </div>
                            
                            <div class="form-check mb-1">
                                <input class="form-check-input flag-doc-checkbox" type="checkbox" name="flag_doc[]" value="null" id="flag_doc_null">
                                <label class="form-check-label" for="flag_doc_null">
                                    <i class="fas fa-minus-circle"></i> Tanpa Flag Dokumen
                                </label>
                            </div>
                            
                            <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                <div class="form-check mb-1 flag-doc-item">
                                    <input class="form-check-input flag-doc-checkbox" type="checkbox" name="flag_doc[]" value="<?= htmlspecialchars($flag->flag_doc) ?>" id="flag_doc_<?= md5($flag->flag_doc) ?>">
                                    <label class="form-check-label" for="flag_doc_<?= md5($flag->flag_doc) ?>">
                                        <i class="fas fa-file-alt"></i> <?= htmlspecialchars($flag->flag_doc) ?>
                                    </label>
                                </div>
                            <?php endforeach; endif; ?>
                            
                            <!-- No results message -->
                            <div id="noFlagDocResults" class="text-muted text-center py-3" style="display: none;">
                                <i class="fas fa-search"></i> Tidak ada flag dokumen yang cocok
                            </div>
                        </div>
                        
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> 
                            Pilih satu atau lebih flag dokumen untuk memfilter data arsip yang akan di-export. 
                            Gunakan kotak pencarian di atas untuk menemukan flag dokumen dengan cepat.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_status" class="form-label">
                            <i class="fas fa-tasks"></i> Filter Status
                        </label>
                        <select class="form-select" id="export_status" name="status">
                            <option value="">Semua Status</option>
                            <option value="2">Done (Arsip)</option>
                        </select>
                        <div class="form-text">Data arsip hanya berisi status Done</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_nama" class="form-label">
                            <i class="fas fa-user"></i> Filter Nama
                        </label>
                        <input type="text" class="form-control" id="export_nama" name="nama" placeholder="Masukkan nama peserta">
                        <div class="form-text">Kosongkan untuk semua nama</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_nomor_paspor" class="form-label">
                            <i class="fas fa-passport"></i> Filter Nomor Paspor
                        </label>
                        <input type="text" class="form-control" id="export_nomor_paspor" name="nomor_paspor" placeholder="Masukkan nomor paspor">
                        <div class="form-text">Kosongkan untuk semua nomor paspor</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_no_visa" class="form-label">
                            <i class="fas fa-stamp"></i> Filter Nomor Visa
                        </label>
                        <input type="text" class="form-control" id="export_no_visa" name="no_visa" placeholder="Masukkan nomor visa">
                        <div class="form-text">Kosongkan untuk semua nomor visa</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-success" onclick="submitExport()">
                    <i class="fas fa-download"></i> Export Data Arsip
                </button>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Excel:</strong> Format spreadsheet dengan data arsip<br>
                        <strong>PDF:</strong> Format HTML yang dapat dicetak sebagai PDF
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
    border-radius: 15px 15px 0 0;
    border-bottom: none;
}

.modal-header .modal-title {
    font-weight: 600;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    border-top: none;
    padding: 1rem 2rem 2rem;
}

/* Form Styles */
.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.form-label i {
    margin-right: 0.5rem;
    color: var(--primary-color);
}

.form-select, .form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.form-select:focus, .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(30, 58, 95, 0.25);
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Flag Dokumen Checkbox Styles */
.flag-doc-container {
    scrollbar-width: thin;
    scrollbar-color: #1e3a5f #f8f9fa;
}

.flag-doc-container::-webkit-scrollbar {
    width: 6px;
}

.flag-doc-container::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.flag-doc-container::-webkit-scrollbar-thumb {
    background: #1e3a5f;
    border-radius: 3px;
}

.flag-doc-container::-webkit-scrollbar-thumb:hover {
    background: #1e40af;
}

.form-check-input:checked {
    background-color: #1e3a5f;
    border-color: #1e3a5f;
}

.form-check-input:focus {
    border-color: #1e3a5f;
    box-shadow: 0 0 0 0.2rem rgba(30, 58, 95, 0.25);
}

.form-check-label {
    cursor: pointer;
    user-select: none;
    font-size: 0.9rem;
}

.form-check-label:hover {
    color: #8B4513;
}

.flag-doc-item {
    transition: all 0.2s ease;
}

.flag-doc-item.hidden {
    display: none !important;
}

.flag-doc-item.highlight {
    background-color: rgba(139, 69, 19, 0.1);
    border-radius: 4px;
    padding: 2px 4px;
    margin: 0 -4px;
}

/* Button Styles */
.btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
    color: white;
    transform: translateY(-2px);
}

.btn-success {
    background-color: var(--success-color);
    border-color: var(--success-color);
    color: white;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    color: white;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 1rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .modal-footer {
        padding: 1rem;
    }
}
</style>

<script>
function submitExport() {
    const form = document.getElementById('exportForm');
    const format = document.getElementById('export_format').value;
    
    if (!format) {
        alert('Silakan pilih format export terlebih dahulu');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.btn-success');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    submitBtn.disabled = true;
    
    // Build export URL with form data
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Add non-empty values to params
    for (let [key, value] of formData.entries()) {
        // Handle flag_doc array specially - append each selected flag_doc
        if (key === 'flag_doc[]') {
            // For flag_doc, we want to include empty values too (for "Semua Data" and "Tanpa Flag Dokumen")
            // Handle special characters by using encodeURIComponent for safe transmission
            const safeValue = encodeURIComponent(value);
            params.append('flag_doc[]', safeValue);
        } else {
            // For other fields, only add non-empty values
            if (value.trim() !== '') {
                params.append(key, value);
            }
        }
    }
    
    // Build export URL
    const exportUrl = '<?= base_url('database/export_arsip') ?>' + (params.toString() ? '?' + params.toString() : '');
    
    // Use fetch to check for errors first
    fetch(exportUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            // Create download link
            const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
            const format = document.getElementById('export_format').value;
            const extension = format === 'pdf' ? '.html' : '.xlsx';
            const filename = 'Arsip_Data_Peserta_' + timestamp + extension;
            
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            // Show success message
            showAlert('Data arsip berhasil di-export!', 'success');
        })
        .catch(error => {
            console.error('Export error:', error);
            showAlert('Terjadi kesalahan saat export. Silakan coba lagi atau hubungi administrator.', 'error');
        });
    
    // Reset button after a delay
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 5000);
}

// Auto-submit when format is selected (optional)
document.getElementById('export_format').addEventListener('change', function() {
    if (this.value) {
        // Optional: auto-submit when format is selected
        // submitExport();
    }
});

// Initialize flag dokumen functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeFlagDocFunctionality();
});

// Flag Dokumen Functionality
function initializeFlagDocFunctionality() {
    const searchInput = document.getElementById('flagDocSearch');
    const selectAllCheckbox = document.getElementById('selectAllFlagDoc');
    const flagDocCheckboxes = document.querySelectorAll('.flag-doc-checkbox');
    const flagDocItems = document.querySelectorAll('.flag-doc-item');
    const noResultsDiv = document.getElementById('noFlagDocResults');
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            flagDocItems.forEach(item => {
                const label = item.querySelector('label').textContent.toLowerCase();
                const checkbox = item.querySelector('input[type="checkbox"]');
                
                if (label.includes(searchTerm)) {
                    item.classList.remove('hidden');
                    item.classList.add('highlight');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                    item.classList.remove('highlight');
                }
            });
            
            // Show/hide "no results" message
            if (visibleCount === 0 && searchTerm !== '') {
                noResultsDiv.style.display = 'block';
            } else {
                noResultsDiv.style.display = 'none';
            }
            
            // Update select all checkbox state
            updateSelectAllState();
        });
    }
    
    // Select All functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            flagDocCheckboxes.forEach(checkbox => {
                const item = checkbox.closest('.flag-doc-item, .form-check');
                if (!item.classList.contains('hidden')) {
                    checkbox.checked = isChecked;
                }
            });
        });
    }
    
    // Individual checkbox change handler
    flagDocCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
        });
    });
    
    // Update select all checkbox state
    function updateSelectAllState() {
        const visibleCheckboxes = Array.from(flagDocCheckboxes).filter(checkbox => {
            const item = checkbox.closest('.flag-doc-item, .form-check');
            return !item.classList.contains('hidden');
        });
        
        const checkedVisibleCheckboxes = visibleCheckboxes.filter(checkbox => checkbox.checked);
        
        if (checkedVisibleCheckboxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedVisibleCheckboxes.length === visibleCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        }
    }
}
</script>
