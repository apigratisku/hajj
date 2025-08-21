<!-- Content Body -->
<div class="content-body">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Akun Email Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('email_management/create') ?>" id="createEmailForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </label>
                                    <div class="input-group">
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="user@domain.com" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="generateEmail()">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Format: username@domain.com
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="domain" class="form-label">
                                        <i class="fas fa-globe me-2"></i>Domain
                                    </label>
                                    <select class="form-select" id="domain" name="domain" required>
                                        <option value="">Pilih Domain</option>
                                        <option value="localhost">localhost</option>
                                        <option value="example.com">example.com</option>
                                    </select>
                                    <div class="form-text">
                                        Domain untuk akun email
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Masukkan password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="passwordToggle"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" onclick="generatePassword()">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Minimal 8 karakter, kombinasi huruf dan angka
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Konfirmasi Password
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Konfirmasi password" required>
                                    <div class="form-text">
                                        Masukkan ulang password yang sama
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quota" class="form-label">
                                        <i class="fas fa-hdd me-2"></i>Quota Email (MB)
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quota" name="quota" 
                                               value="250" min="10" max="10000" required>
                                        <span class="input-group-text">MB</span>
                                    </div>
                                    <div class="form-text">
                                        Kapasitas penyimpanan email (10MB - 10GB)
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-preset me-2"></i>Quota Preset
                                    </label>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuota(100)">100MB</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuota(250)">250MB</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuota(500)">500MB</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuota(1000)">1GB</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-2"></i>Catatan (Opsional)
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Catatan tambahan untuk akun email ini..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('email_management') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>Buat Akun Email
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Generate random email
function generateEmail() {
    const domains = ['localhost', 'example.com'];
    const randomDomain = domains[Math.floor(Math.random() * domains.length)];
    const randomUser = 'user' + Math.floor(Math.random() * 1000);
    const email = randomUser + '@' + randomDomain;
    
    document.getElementById('email').value = email;
    document.getElementById('domain').value = randomDomain;
}

// Generate random password
function generatePassword() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('confirm_password').value = password;
    checkPasswordStrength();
}

// Toggle password visibility
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggle');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Set quota from preset
function setQuota(quota) {
    document.getElementById('quota').value = quota;
}

// Check password strength
function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthIndicator = document.getElementById('passwordStrength');
    
    if (!strengthIndicator) return;
    
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    switch (strength) {
        case 0:
        case 1:
            feedback = '<span class="text-danger">Sangat Lemah</span>';
            break;
        case 2:
            feedback = '<span class="text-warning">Lemah</span>';
            break;
        case 3:
            feedback = '<span class="text-info">Sedang</span>';
            break;
        case 4:
            feedback = '<span class="text-primary">Kuat</span>';
            break;
        case 5:
            feedback = '<span class="text-success">Sangat Kuat</span>';
            break;
    }
    
    strengthIndicator.innerHTML = feedback;
}

// Reset form
function resetForm() {
    document.getElementById('createEmailForm').reset();
    document.getElementById('quota').value = '250';
}

// Form validation
document.getElementById('createEmailForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const quota = document.getElementById('quota').value;
    
    // Email validation
    if (!email.includes('@')) {
        e.preventDefault();
        showAlert('error', 'Format email tidak valid');
        return;
    }
    
    // Password validation
    if (password.length < 8) {
        e.preventDefault();
        showAlert('error', 'Password minimal 8 karakter');
        return;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        showAlert('error', 'Konfirmasi password tidak cocok');
        return;
    }
    
    // Quota validation
    if (quota < 10 || quota > 10000) {
        e.preventDefault();
        showAlert('error', 'Quota harus antara 10MB - 10GB');
        return;
    }
    
    // Show loading
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Membuat Akun...';
    submitBtn.disabled = true;
});

// Show alert message
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const cardBody = document.querySelector('.card-body');
    const existingAlert = cardBody.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = cardBody.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate email on page load
    generateEmail();
    
    // Add password strength checker
    document.getElementById('password').addEventListener('input', checkPasswordStrength);
});
</script>
