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
                        <select class="form-select" id="export_flag_doc" name="flag_doc">
                            <option value="">Semua Data</option>
                            <option value="null">Tanpa Flag Dokumen</option>
                            <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                <option value="<?= htmlspecialchars($flag->flag_doc) ?>">
                                    <?= htmlspecialchars($flag->flag_doc) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <div class="form-text">Pilih flag dokumen untuk memfilter data yang akan di-export</div>
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
</style>

<script>
// Export Modal Functionality
function submitExport() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Add non-empty values to params
    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            params.append(key, value);
        }
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
    const extension = format === 'pdf' ? '.pdf' : '.xlsx';
    link.download = 'Database_Peserta_' + timestamp + extension;
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
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
        });
    }
});
</script>
