<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-user-plus"></i> Tambah Data Peserta</h5>
                    <a href="<?= base_url('database') ?>" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Kembali</span>
                    </a>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('database/store') ?>" method="POST" class="mobile-form">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="nama" class="form-label">
                                        <i class="fas fa-user"></i> Nama Peserta <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="nama" name="nama" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="nomor_paspor" class="form-label">
                                        <i class="fas fa-passport"></i> Nomor Paspor <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="nomor_paspor" name="nomor_paspor" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="no_visa" class="form-label">
                                        <i class="fas fa-stamp"></i> Nomor Visa
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="no_visa" name="no_visa">
                                </div>
                                
                                <div class="form-group">
                                    <label for="tgl_lahir" class="form-label">
                                        <i class="fas fa-calendar"></i> Tanggal Lahir
                                    </label>
                                    <input type="date" class="form-control mobile-input" id="tgl_lahir" name="tgl_lahir">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i> Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control mobile-input" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="nomor_hp" class="form-label">
                                        <i class="fas fa-phone"></i> Nomor HP
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="nomor_hp" name="nomor_hp">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i> Email
                                    </label>
                                    <input type="email" class="form-control mobile-input" id="email" name="email">
                                </div>
                                
                                <div class="form-group">
                                    <label for="barcode" class="form-label">
                                        <i class="fas fa-barcode"></i> Barcode
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="barcode" name="barcode" placeholder="Masukkan barcode">
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars"></i> Gender
                                    </label>
                                    <select name="gender" id="gender" class="form-select mobile-input">
                                        <option value="">Pilih Gender</option>
                                        <option value=""></option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-tasks"></i> Status
                                    </label>
                                    <select name="status" id="status" class="form-select mobile-input">
                                        <option value="0">On Target</option>
                                        <option value="1">Already</option>
                                        <option value="2">Done</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="tanggal" class="form-label">
                                        <i class="fas fa-calendar-day"></i> Tanggal
                                    </label>
                                    <input type="date" class="form-control mobile-input" id="tanggal" name="tanggal">
                                </div>
                                
                                <div class="form-group">
                                    <label for="jam" class="form-label">
                                        <i class="fas fa-clock"></i> Jam
                                    </label>
                                    <input type="time" class="form-control mobile-input" id="jam" name="jam">
                                </div>
                                
                                <div class="form-group">
                                    <label for="flag_doc" class="form-label">
                                        <i class="fas fa-tag"></i> Flag Dokumen
                                    </label>
                                    <input type="text" class="form-control mobile-input" id="flag_doc" name="flag_doc" placeholder="Contoh: Batch-001, Manual-Entry">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="<?= base_url('database') ?>" class="btn btn-secondary btn-cancel">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-brown btn-save">
                                <i class="fas fa-save"></i> Simpan
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

.btn-save {
    background: var(--success-color) !important;
    color: white !important;
}

.btn-save:hover {
    background: #218838 !important;
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

/* Success Message Animation */
@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn-save:active {
    animation: successPulse 0.3s ease-in-out;
}
</style>
