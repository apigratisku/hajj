<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit"></i> Edit User
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('user/edit/'.$user->id_user); ?>" method="post" class="modern-form">
                        <!-- CSRF Token -->
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user"></i> Username <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control modern-input" id="username" name="username" value="<?= $user->username; ?>" placeholder="Masukkan username" required>
                                    <?= form_error('username', '<small class="text-danger error-message">', '</small>'); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i> Password
                                    </label>
                                    <div class="password-input-group">
                                        <input type="password" class="form-control modern-input" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                                        <button type="button" class="btn btn-outline-secondary password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-info">
                                        <i class="fas fa-info-circle"></i> Kosongkan jika tidak ingin mengubah password
                                    </small>
                                    <?= form_error('password', '<small class="text-danger error-message">', '</small>'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nama_lengkap" class="form-label">
                                        <i class="fas fa-id-card"></i> Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control modern-input" id="nama_lengkap" name="nama_lengkap" value="<?= $user->nama_lengkap; ?>" placeholder="Masukkan nama lengkap" required>
                                    <?= form_error('nama_lengkap', '<small class="text-danger error-message">', '</small>'); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="role" class="form-label">
                                        <i class="fas fa-user-tag"></i> Role <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select modern-input" id="role" name="role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="admin" <?= $user->role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="operator" <?= $user->role == 'operator' ? 'selected' : ''; ?>>Operator</option>
                                    </select>
                                    <?= form_error('role', '<small class="text-danger error-message">', '</small>'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on"></i> Status User
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" <?= (isset($user->status) && $user->status == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="status">
                                            User Aktif (dapat login)
                                        </label>
                                    </div>
                                    <small class="text-muted">Centang untuk mengaktifkan user, kosongkan untuk menonaktifkan</small>
                                </div>
                            </div>
                        </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-tambah btn-lg">
                                <i class="fas fa-save"></i> Update User
                            </button>
                            <a href="<?= base_url('user'); ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
/* Android App-like Design Variables */
:root {
    --primary-color: #8B4513;
    --primary-light: #A0522D;
    --primary-dark: #654321;
    --secondary-color: #654321;
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

/* Base Styles */
.bg-brown {
    background-color: var(--primary-color) !important;
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

/* Modern Form Styles */
.modern-form {
    max-width: 100%;
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

.modern-input {
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: var(--transition);
    background: white;
}

.modern-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
    outline: none;
}

.modern-input::placeholder {
    color: #adb5bd;
}

/* Password Input Group */
.password-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-group .form-control {
    padding-right: 3rem;
}

.password-toggle {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.password-toggle:hover {
    color: var(--primary-color);
    background: rgba(139, 69, 19, 0.1);
}

/* Info Text */
.text-info {
    color: var(--info-color) !important;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.text-info i {
    font-size: 0.75rem;
}

/* Button Styles */
.btn-tambah {
    background-color: var(--success-color) !important;
    border-color: var(--success-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
}

.btn-tambah:hover {
    background-color: #218838 !important;
    border-color: #218838 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-secondary {
    background-color: var(--secondary-color) !important;
    border-color: var(--secondary-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
}

.btn-secondary:hover {
    background-color: var(--primary-light) !important;
    border-color: var(--primary-light) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.form-actions .btn {
    flex: 1;
    min-width: 200px;
}

/* Error Messages */
.error-message {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .mobile-card .card-body {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        min-width: 100%;
    }
    
    .modern-input {
        padding: 0.625rem 0.875rem;
        font-size: 0.9rem;
    }
    
    .form-label {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .mobile-card .card-body {
        padding: 1rem;
    }
    
    .mobile-card .card-header {
        padding: 1rem;
    }
    
    .modern-input {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.9rem;
    }
}

/* Animation Effects */
.modern-input:focus {
    animation: inputFocus 0.3s ease-out;
}

@keyframes inputFocus {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* Form Group Hover Effects */
.form-group:hover .form-label {
    color: var(--primary-color);
}

.form-group:hover .modern-input {
    border-color: var(--primary-light);
}

/* Form Switch Styles */
.form-check-input {
    width: 3rem;
    height: 1.5rem;
    margin-top: 0;
    background-color: #dee2e6;
    border: 1px solid #dee2e6;
    border-radius: 1rem;
    transition: var(--transition);
}

.form-check-input:checked {
    background-color: var(--success-color);
    border-color: var(--success-color);
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
}

.form-check-label {
    margin-left: 0.5rem;
    font-weight: 500;
    color: var(--dark-color);
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggleBtn = input.nextElementSibling;
    const icon = toggleBtn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        toggleBtn.title = 'Sembunyikan password';
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        toggleBtn.title = 'Tampilkan password';
    }
}

// Add loading state to form submission
document.querySelector('.modern-form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupdate...';
    
    // Re-enable after 5 seconds as fallback
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }, 5000);
});
</script> 