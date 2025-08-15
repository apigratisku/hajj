<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Edit Data Peserta</h5>
                    <a href="<?= base_url('database') ?>" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Kembali</span>
                    </a>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('database/update/' . $peserta->id) ?>" method="POST" class="mobile-form">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="nama" class="form-label">
                                        <i class="fas fa-user"></i> Nama Peserta <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="nama" name="nama" value="<?= $peserta->nama ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="nomor_paspor" class="form-label">
                                        <i class="fas fa-passport"></i> Nomor Paspor <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="nomor_paspor" name="nomor_paspor" value="<?= $peserta->nomor_paspor ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="no_visa" class="form-label">
                                        <i class="fas fa-stamp"></i> Nomor Visa
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="no_visa" name="no_visa" value="<?= $peserta->no_visa ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="tgl_lahir" class="form-label">
                                        <i class="fas fa-calendar"></i> Tanggal Lahir
                                    </label>
                                    <input type="date" class="form-control mobile-input" id="tgl_lahir" name="tgl_lahir" value="<?= $peserta->tgl_lahir ? date('Y-m-d', strtotime($peserta->tgl_lahir)) : '' ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i> Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control mobile-input" id="password" name="password" value="<?= $peserta->password ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="nomor_hp" class="form-label">
                                        <i class="fas fa-phone"></i> Nomor HP
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="nomor_hp" name="nomor_hp" value="<?= $peserta->nomor_hp ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i> Email
                                    </label>
                                    <input type="email" class="form-control mobile-input" id="email" name="email" value="<?= $peserta->email ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="barcode" class="form-label">
                                        <i class="fas fa-barcode"></i> Barcode
                                    </label>
                                    <div class="barcode-input-container">
                                        <input type="text" class="form-control mobile-input" id="barcode" name="barcode" value="<?= $peserta->barcode ?>" placeholder="Masukkan barcode atau upload gambar">
                                        <button type="button" class="btn btn-outline-primary btn-sm ms-2" id="btnUploadBarcode" title="Upload Gambar Barcode">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Hidden file input -->
                                    <input type="file" id="barcodeImageInput" accept="image/*" style="display: none;">
                                    
                                    <!-- Barcode image preview -->
                                    <div id="barcodePreview" class="mt-2" style="display: none;">
                                        <div class="barcode-preview-container">
                                            <img id="barcodePreviewImg" src="" alt="Preview Barcode" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                                            <div class="barcode-preview-actions mt-2">
                                                <button type="button" class="btn btn-sm btn-danger" id="btnRemoveBarcode">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Upload progress -->
                                    <div id="uploadProgress" class="mt-2" style="display: none;">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted">Mengupload gambar...</small>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars"></i> Gender
                                    </label>
                                    <select name="gender" id="gender" class="form-select mobile-input">
                                        <option value="">Pilih Gender</option>
                                        <option value="L" <?= $peserta->gender == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                        <option value="P" <?= $peserta->gender == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-tasks"></i> Status
                                    </label>
                                    <select name="status" id="status" class="form-select mobile-input">
                                        <option value="0" <?= $peserta->status == 0 ? 'selected' : '' ?>>On Target</option>
                                        <option value="1" <?= $peserta->status == 1 ? 'selected' : '' ?>>On Schedule</option>
                                        <option value="2" <?= $peserta->status == 2 ? 'selected' : '' ?>>Done</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="tanggal" class="form-label">
                                        <i class="fas fa-calendar-day"></i> Tanggal
                                    </label>
                                    <input type="date" class="form-control mobile-input" id="tanggal" name="tanggal" value="<?= $peserta->tanggal ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="jam" class="form-label">
                                        <i class="fas fa-clock"></i> Jam
                                    </label>
                                    <input type="time" class="form-control mobile-input" id="jam" name="jam" value="<?= $peserta->jam ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="flag_doc" class="form-label">
                                        <i class="fas fa-tag"></i> Flag Dokumen
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="flag_doc" name="flag_doc" value="<?= $peserta->flag_doc ?>" placeholder="Contoh: Batch-001, Manual-Entry">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="<?= base_url('database') ?>" class="btn btn-secondary btn-cancel">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-brown btn-update">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Android App-like Design Variables */
:root {
    --primary-color: #8B4513;
    --primary-light: #A0522D;
    --primary-dark: #654321;
    --secondary-color: #D2691E;
    --accent-color: #FF6B35;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --border-radius: 12px;
    --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.2);
    --transition: all 0.3s ease;
}

/* Mobile Card Styles */
.mobile-card {
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: none;
    overflow: hidden;
}

.mobile-card .card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border: none;
    padding: 1.5rem;
}

.mobile-card .card-body {
    padding: 2rem;
}

/* Form Styles */
.mobile-form {
    max-width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-label i {
    color: var(--primary-color);
    width: 16px;
}

.mobile-input {
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: var(--transition);
    background: white;
}

.mobile-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
    outline: none;
    transform: translateY(-2px);
}

.form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%238B4513' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
}

/* Button Styles */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}

.btn {
    border-radius: var(--border-radius);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 120px;
    justify-content: center;
}

.btn-brown {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light)) !important;
    color: white !important;
}

.btn-brown:hover {
    background: linear-gradient(135deg, var(--primary-light), var(--primary-color)) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-secondary {
    background: #6c757d !important;
    color: white !important;
}

.btn-secondary:hover {
    background: #5a6268 !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-cancel {
    background: var(--danger-color) !important;
    color: white !important;
}

.btn-cancel:hover {
    background: #c82333 !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-update {
    background: var(--warning-color) !important;
    color: var(--dark-color) !important;
}

.btn-update:hover {
    background: #e0a800 !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

/* Base Styles */
.bg-brown {
    background-color: var(--primary-color) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .mobile-card .card-body {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn {
        width: 100%;
        min-width: auto;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .mobile-input {
        padding: 0.875rem 1rem;
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .mobile-card .card-header {
        padding: 1rem;
        text-align: center;
    }
    
    .mobile-card .card-body {
        padding: 1rem;
    }
    
    .form-label {
        font-size: 0.95rem;
    }
    
    .mobile-input {
        padding: 0.75rem 0.875rem;
        font-size: 0.95rem;
    }
}

/* Animation Effects */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-group {
    animation: slideInUp 0.3s ease-out;
    animation-fill-mode: both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }
.form-group:nth-child(4) { animation-delay: 0.4s; }
.form-group:nth-child(5) { animation-delay: 0.5s; }
.form-group:nth-child(6) { animation-delay: 0.6s; }
.form-group:nth-child(7) { animation-delay: 0.7s; }
.form-group:nth-child(8) { animation-delay: 0.8s; }
.form-group:nth-child(9) { animation-delay: 0.9s; }
.form-group:nth-child(10) { animation-delay: 1.0s; }
.form-group:nth-child(11) { animation-delay: 1.1s; }
.form-group:nth-child(12) { animation-delay: 1.2s; }

/* Input Focus Effects */
.mobile-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
    outline: none;
    transform: translateY(-2px);
}

/* Required Field Indicator */
.text-danger {
    color: var(--danger-color) !important;
    font-weight: bold;
}

/* Form Validation Styles */
.form-control.is-invalid {
    border-color: var(--danger-color);
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control.is-valid {
    border-color: var(--success-color);
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Loading State */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Update Button Animation */
@keyframes updatePulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn-update:active {
    animation: updatePulse 0.3s ease-in-out;
}

/* Pre-filled Input Styles */
.mobile-input[value]:not([value=""]) {
    background-color: #f8f9fa;
    border-color: var(--primary-color);
}

.mobile-input[value]:not([value=""]):focus {
    background-color: white;
}

/* Barcode Upload Styles */
.barcode-input-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.barcode-input-container .form-control {
    flex: 1;
}

.barcode-preview-container {
    border: 2px dashed #dee2e6;
    border-radius: var(--border-radius);
    padding: 1rem;
    text-align: center;
    background-color: #f8f9fa;
}

.barcode-preview-container img {
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.barcode-preview-actions {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.progress {
    height: 0.5rem;
    border-radius: var(--border-radius);
    background-color: #e9ecef;
}

.progress-bar {
    background-color: var(--primary-color);
    transition: width 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Barcode upload functionality
    const btnUploadBarcode = document.getElementById('btnUploadBarcode');
    const barcodeImageInput = document.getElementById('barcodeImageInput');
    const barcodePreview = document.getElementById('barcodePreview');
    const barcodePreviewImg = document.getElementById('barcodePreviewImg');
    const btnRemoveBarcode = document.getElementById('btnRemoveBarcode');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = uploadProgress.querySelector('.progress-bar');
    const barcodeInput = document.getElementById('barcode');
    const flagDocInput = document.getElementById('flag_doc');
    
    let uploadedFilename = null;
    
    // Trigger file input when upload button is clicked
    btnUploadBarcode.addEventListener('click', function() {
        barcodeImageInput.click();
    });
    
    // Handle file selection
    barcodeImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Pilih file gambar yang valid');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 5MB');
                return;
            }
            
            // Check if flag_doc is filled
            if (!flagDocInput.value.trim()) {
                alert('Flag dokumen harus diisi terlebih dahulu');
                flagDocInput.focus();
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                barcodePreviewImg.src = e.target.result;
                barcodePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
            
            // Upload file
            uploadBarcodeImage(file);
        }
    });
    
    // Remove barcode image
    btnRemoveBarcode.addEventListener('click', function() {
        barcodePreview.style.display = 'none';
        barcodeImageInput.value = '';
        uploadedFilename = null;
        
        // Clear barcode input if it contains the uploaded filename
        if (barcodeInput.value === uploadedFilename) {
            barcodeInput.value = '';
        }
    });
    
    function uploadBarcodeImage(file) {
        const formData = new FormData();
        formData.append('barcode_image', file);
        formData.append('flag_doc', flagDocInput.value.trim());
        
        // Show progress
        uploadProgress.style.display = 'block';
        progressBar.style.width = '0%';
        
        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);
        
        fetch('<?= base_url('upload/upload_barcode') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            
            setTimeout(() => {
                uploadProgress.style.display = 'none';
                
                if (data.status === 'success') {
                    uploadedFilename = data.barcode_value;
                    barcodeInput.value = data.barcode_value; // Gunakan barcode_value untuk database
                    
                    // Show success message
                    showAlert('success', 'Gambar barcode berhasil diupload!');
                } else {
                    showAlert('error', data.message || 'Gagal mengupload gambar');
                    barcodePreview.style.display = 'none';
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(progressInterval);
            uploadProgress.style.display = 'none';
            barcodePreview.style.display = 'none';
            showAlert('error', 'Terjadi kesalahan saat mengupload gambar');
            console.error('Upload error:', error);
        });
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="${icon} me-2"></i>
                    <div class="flex-grow-1">${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert alert at the top of card-body
        const cardBody = document.querySelector('.card-body');
        cardBody.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    }
});
</script> 