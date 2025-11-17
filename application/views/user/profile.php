<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle"></i> Profile Saya
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Profile Section -->
                        <div class="col-lg-6 mb-4">
                            <div class="profile-section">
                                <div class="section-header">
                                    <h6 class="section-title">
                                        <i class="fas fa-user-edit"></i> Update Profile
                                    </h6>
                                </div>
                                
                                <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
                                
                                <?= form_open('user/update_profile', ['method' => 'post', 'class' => 'modern-form']) ?>
                                    <div class="form-group mb-3">
                                        <label for="username" class="form-label">
                                            <i class="fas fa-user"></i> Username
                                        </label>
                                        <input type="text" class="form-control modern-input" id="username" value="<?= $user->username ?>" readonly>
                                        <small class="text-muted">Username tidak dapat diubah</small>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="nama_lengkap" class="form-label">
                                            <i class="fas fa-id-card"></i> Nama Lengkap <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control modern-input" id="nama_lengkap" name="nama_lengkap" value="<?= set_value('nama_lengkap', $user->nama_lengkap) ?>" required>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="role" class="form-label">
                                            <i class="fas fa-user-tag"></i> Role
                                        </label>
                                        <input type="text" class="form-control modern-input" id="role" value="<?= ucfirst($user->role) ?>" readonly>
                                        <small class="text-muted">Role tidak dapat diubah</small>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-tambah">
                                            <i class="fas fa-save"></i> Update Profile
                                        </button>
                                    </div>
                                <?= form_close() ?>
                            </div>
                        </div>
                        
                        <!-- Change Password Section -->
                        <div class="col-lg-6 mb-4">
                            <div class="password-section">
                                <div class="section-header">
                                    <h6 class="section-title">
                                        <i class="fas fa-key"></i> Ganti Password
                                    </h6>
                                </div>
                                
                                <?= form_open('user/change_password', ['method' => 'post', 'class' => 'modern-form']) ?>
                                    <div class="form-group mb-3">
                                        <label for="current_password" class="form-label">
                                            <i class="fas fa-lock"></i> Password Lama <span class="text-danger">*</span>
                                        </label>
                                        <div class="password-input-group">
                                            <input type="password" class="form-control modern-input" id="current_password" name="current_password" required>
                                            <button type="button" class="btn btn-outline-secondary password-toggle" onclick="togglePassword('current_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="new_password" class="form-label">
                                            <i class="fas fa-lock-open"></i> Password Baru <span class="text-danger">*</span>
                                        </label>
                                        <div class="password-input-group">
                                            <input type="password" class="form-control modern-input" id="new_password" name="new_password" required>
                                            <button type="button" class="btn btn-outline-secondary password-toggle" onclick="togglePassword('new_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <small class="text-info">
                                            <i class="fas fa-info-circle"></i> Password minimal 6 karakter
                                        </small>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-lock-open"></i> Konfirmasi Password <span class="text-danger">*</span>
                                        </label>
                                        <div class="password-input-group">
                                            <input type="password" class="form-control modern-input" id="confirm_password" name="confirm_password" required>
                                            <button type="button" class="btn btn-outline-secondary password-toggle" onclick="togglePassword('confirm_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key"></i> Ganti Password
                                        </button>
                                    </div>
                                <?= form_close() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show position-fixed" role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
        <?= $this->session->flashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show position-fixed" role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
        <?= $this->session->flashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
/* Android App-like Design Variables */
:root {
    --primary-color: #1e3a5f;
    --primary-light: #2c5282;
    --primary-dark: #1e40af;
    --secondary-color: #2c5282;
    --accent-color: #3b82f6;
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

/* Section Styles */
.profile-section, .password-section {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    height: 100%;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.profile-section:hover, .password-section:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.section-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--primary-color);
}

.section-title {
    margin: 0;
    color: var(--primary-color);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    font-size: 1.1rem;
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

.modern-input:read-only {
    background-color: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
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

/* Text Muted */
.text-muted {
    color: #6c757d !important;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
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

.btn-warning {
    background-color: var(--warning-color) !important;
    border-color: var(--warning-color) !important;
    color: #212529 !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
}

.btn-warning:hover {
    background-color: #e0a800 !important;
    border-color: #e0a800 !important;
    color: #212529 !important;
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

/* Alert Styles */
.alert {
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: none;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

/* Mobile Responsive */
@media (max-width: 991px) {
    .profile-section, .password-section {
        margin-bottom: 1rem;
    }
}

@media (max-width: 768px) {
    .mobile-card .card-body {
        padding: 1.5rem;
    }
    
    .profile-section, .password-section {
        padding: 1rem;
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
    
    .profile-section, .password-section {
        padding: 0.75rem;
    }
    
    .modern-input {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.9rem;
    }
    
    .section-title {
        font-size: 1rem;
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

.form-group:hover .modern-input:not(:read-only) {
    border-color: var(--primary-light);
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

// Add loading state to form submissions
document.querySelectorAll('.modern-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        
        // Re-enable after 5 seconds as fallback
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }, 5000);
    });
});

// Auto-hide flash messages after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.remove();
    });
}, 5000);
</script> 