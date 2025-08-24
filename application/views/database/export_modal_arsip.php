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
                        <select class="form-select" id="export_flag_doc" name="flag_doc">
                            <option value="">Semua Data Arsip</option>
                            <option value="null">Tanpa Flag Dokumen</option>
                            <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                <option value="<?= htmlspecialchars($flag->flag_doc) ?>">
                                    <?= htmlspecialchars($flag->flag_doc) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <div class="form-text">Pilih flag dokumen untuk memfilter data arsip yang akan di-export</div>
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
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
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
    
    // Submit form
    form.submit();
    
    // Reset button after a delay
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);
}

// Auto-submit when format is selected (optional)
document.getElementById('export_format').addEventListener('change', function() {
    if (this.value) {
        // Optional: auto-submit when format is selected
        // submitExport();
    }
});
</script>
