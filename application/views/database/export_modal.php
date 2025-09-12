<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-brown text-white">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-file-excel"></i> Export Data Peserta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm" action="<?= base_url('database/export') ?>" method="GET">
                    <div class="mb-3">
                        <label for="export_data" class="form-label">
                            <i class="fas fa-file"></i> Pilih Data Export
                        </label>
                        <select class="form-select" id="export_data" name="export_data" required>
                            <option value="">Pilih Data</option>
                            <option value="statistik">Export Data Statistik</option>
                            <option value="peserta">Export Data Peserta</option>
                        </select>
                        <div class="form-text">Pilih jenis data yang akan di-export</div>
                    </div>
                    
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
                        <label for="export_nama_travel" class="form-label">
                            <i class="fas fa-plane"></i> Pilih Nama Travel
                        </label>
                        <select class="form-select" id="export_nama_travel" name="nama_travel">
                            <option value="">Semua Travel</option>
                            <?php if (!empty($travel_list)): foreach ($travel_list as $travel): ?>
                                <option value="<?= htmlspecialchars($travel->nama_travel) ?>">
                                    <?= htmlspecialchars($travel->nama_travel) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> 
                            Pilih nama travel untuk mengexport semua flag dokumen dari travel tersebut. 
                            Jika dipilih, semua flag dokumen dengan nama travel yang sama akan diexport.
                        </div>
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
                                    <i class="fas fa-database"></i> Semua Data
                                </label>
                            </div>
                            
                            <div class="form-check mb-1">
                                <input class="form-check-input flag-doc-checkbox" type="checkbox" name="flag_doc[]" value="null" id="flag_doc_null">
                                <label class="form-check-label" for="flag_doc_null">
                                    <i class="fas fa-minus-circle"></i> Tanpa Flag Dokumen
                                </label>
                            </div>
                            
                            <?php if (!empty($flag_doc_list_export)): foreach ($flag_doc_list_export as $flag): ?>
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
                            Pilih satu atau lebih flag dokumen untuk memfilter data yang akan di-export. 
                            Gunakan kotak pencarian di atas untuk menemukan flag dokumen dengan cepat.
                        </div>
                    </div>
                    
                    <!-- Filter Tanggal Updated -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="export_startDate" class="form-label">
                                <i class="fas fa-calendar"></i> Sortir Mulai
                            </label>
                            <input type="date" class="form-control" id="export_startDate" name="startDate">
                            <div class="form-text">Filter data berdasarkan tanggal mulai updated_at</div>
                        </div>
                        <div class="col-md-6">
                            <label for="export_endDate" class="form-label">
                                <i class="fas fa-calendar"></i> Sortir Akhir
                            </label>
                            <input type="date" class="form-control" id="export_endDate" name="endDate">
                            <div class="form-text">Filter data berdasarkan tanggal akhir updated_at</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_status" class="form-label">
                            <i class="fas fa-tasks"></i> Filter Status
                        </label>
                        <select class="form-select" id="export_status" name="status">
                            <option value="">Semua Status</option>
                            <option value="0">On Target</option>
                            <option value="1">Already</option>
                            <option value="2">Done</option>
                        </select>
                        <div class="form-text">Pilih status untuk memfilter data yang akan di-export</div>
                    </div>
                    
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-success" onclick="submitExport()">
                    <i class="fas fa-download"></i> Export Data
                </button>
                
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
    border-radius: 12px 12px 0 0;
    border-bottom: none;
}

.modal-footer {
    border-radius: 0 0 12px 12px;
    border-top: none;
}

.form-select, .form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.form-select:focus, .form-control:focus {
    border-color: #8B4513;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
}

.form-label {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-label i {
    color: #8B4513;
    width: 16px;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}

/* Flag Dokumen Checkbox Styles */
.flag-doc-container {
    scrollbar-width: thin;
    scrollbar-color: #8B4513 #f8f9fa;
}

.flag-doc-container::-webkit-scrollbar {
    width: 6px;
}

.flag-doc-container::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.flag-doc-container::-webkit-scrollbar-thumb {
    background: #8B4513;
    border-radius: 3px;
}

.flag-doc-container::-webkit-scrollbar-thumb:hover {
    background: #6d3410;
}

.form-check-input:checked {
    background-color: #8B4513;
    border-color: #8B4513;
}

.form-check-input:focus {
    border-color: #8B4513;
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
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
</style>

<script>
// Export Modal Functionality
function submitExport() {
    const form = document.getElementById('exportForm');
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
    
    // Check if export data type is selected
    const exportData = formData.get('export_data');
    if (!exportData) {
        showAlert('Silakan pilih jenis data export terlebih dahulu!', 'error');
        return;
    }
    
    // Check if format is selected
    const format = formData.get('format');
    if (!format) {
        showAlert('Silakan pilih format export terlebih dahulu!', 'error');
        return;
    }
    
    // Show loading state
    const exportBtn = document.querySelector('#exportModal .btn-success');
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    exportBtn.disabled = true;
    
    // Build export URL
    const exportUrl = '<?= base_url('database/export') ?>' + (params.toString() ? '?' + params.toString() : '');
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    
    // Set filename based on format
    const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
    const extension = format === 'pdf' ? '.html' : '.xlsx';
    link.download = 'Database_Peserta_' + timestamp + extension;
    
    // For PDF, open in new window first to check for errors
    if (format === 'pdf') {
        const newWindow = window.open(exportUrl, '_blank');
        if (!newWindow) {
            showAlert('Pop-up blocker mungkin mencegah download. Silakan izinkan pop-up untuk situs ini.', 'warning');
        }
    } else {
        // For Excel, use fetch to check for errors first
        fetch(exportUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.blob();
            })
            .then(blob => {
                // Create download link
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'Database_Peserta_' + timestamp + extension;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Export error:', error);
                showAlert('Terjadi kesalahan saat export. Silakan coba lagi atau hubungi administrator.', 'error');
            });
    }
    
    // Reset button and close modal
    setTimeout(() => {
        exportBtn.innerHTML = originalText;
        exportBtn.disabled = false;
        const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
        modal.hide();
        
        // Show success message
        showAlert('Data berhasil di-export!', 'success');
    }, 1000);
}

// Reset form when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const exportModal = document.getElementById('exportModal');
    if (exportModal) {
        exportModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('exportForm').reset();
            // Reset flag dokumen search and selections
            resetFlagDocSelections();
        });
    }
    
    // Initialize flag dokumen functionality
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

// Reset flag dokumen selections
function resetFlagDocSelections() {
    const searchInput = document.getElementById('flagDocSearch');
    const selectAllCheckbox = document.getElementById('selectAllFlagDoc');
    const flagDocCheckboxes = document.querySelectorAll('.flag-doc-checkbox');
    const flagDocItems = document.querySelectorAll('.flag-doc-item');
    const noResultsDiv = document.getElementById('noFlagDocResults');
    
    // Reset search
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Reset checkboxes
    flagDocCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
    
    // Show all items and remove highlights
    flagDocItems.forEach(item => {
        item.classList.remove('hidden', 'highlight');
    });
    
    // Hide no results message
    if (noResultsDiv) {
        noResultsDiv.style.display = 'none';
    }
}
</script>
