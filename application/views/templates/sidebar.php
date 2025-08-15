<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="hajj-logo-small">
            <i class="fas fa-kaaba"></i>
        </div>
        <h3>Sistem Hajj</h3>
        <div class="user-info mt-2">
            <small class="d-block text-light">
                <i class="fas fa-user"></i> 
                <?= $this->session->userdata('nama_lengkap') ?>
            </small>
            <small class="text-light badge" style="background: var(--gold); color: var(--dark-brown);" class="mt-1">
                <?= ucfirst($this->session->userdata('role')) ?>
            </small>
        </div>
    </div>
    
    <ul class="components">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" <?= $this->uri->segment(1) == 'dashboard' && !$this->uri->segment(2) ? 'class="active"' : '' ?>>
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>
        
        <div class="nav-section-divider"></div>
        
       
        
        <li class="nav-item">
            <a href="<?= base_url('database') ?>" <?= $this->uri->segment(1) == 'database' && $this->uri->segment(2) != 'rejected_data' ? 'class="active"' : '' ?>>
                <i class="fas fa-user-friends"></i> <span>Data Peserta</span>
            </a>
        </li>
        <!-- <li class="nav-item">
            <a href="<?= base_url('database/rejected_data') ?>" <?= $this->uri->segment(1) == 'database' && $this->uri->segment(2) == 'rejected_data' ? 'class="active"' : '' ?>>
                <i class="fas fa-times-circle"></i> <span>Data Ditolak</span>
            </a>
        </li> -->
        <li class="nav-item">
            <a href="<?= base_url('todo') ?>" <?= $this->uri->segment(1) == 'todo' ? 'class="active"' : '' ?>>
                <i class="fas fa-list-ul"></i> <span>To Do List</span>
            </a>
        </li>
       
        <div class="nav-section-divider"></div>
        
        <!-- User Management -->
        <?php if ($this->session->userdata('role') == 'admin'): ?>
        <li class="nav-item">
            <a href="<?= base_url('user') ?>" <?= $this->uri->segment(1) == 'user' && $this->uri->segment(2) != 'profile' ? 'class="active"' : '' ?>>
                <i class="fas fa-user-cog"></i> <span>Manajemen User</span>
            </a>
        </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <a href="<?= base_url('user/profile') ?>" <?= $this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'profile' ? 'class="active"' : '' ?>>
                <i class="fas fa-user-edit"></i> <span>Pengaturan User</span>
            </a>
        </li>
        
        <!-- System Settings -->
        <?php if ($this->session->userdata('role') == 'admin'): ?>
            <?php if ($this->session->userdata('username') == 'adhit' || $this->session->userdata('username') == 'mimin'): ?>
        <li class="nav-item">
            <a href="<?= base_url('settings') ?>" <?= $this->uri->segment(1) == 'settings' ? 'class="active"' : '' ?>>
                <i class="fas fa-cogs"></i> <span>Pengaturan Sistem</span>
            </a>
        </li>
        <?php endif; ?>
        <?php endif; ?>
        
        <li class="nav-item mt-3">
            <a href="<?= base_url('auth/logout') ?>" class="text-light" onclick="return confirm('Apakah Anda yakin ingin logout?');">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <small class="text-light">© <?= date('Y') ?> Sistem Pendaftaran Peserta Hajj</small>
    </div>
</div>

<style>
.hajj-logo-small {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--gold), var(--accent-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    box-shadow: 0 5px 15px rgba(218, 165, 32, 0.4);
    position: relative;
}

.hajj-logo-small i {
    font-size: 1.5rem;
    color: var(--dark-brown);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}
</style> 