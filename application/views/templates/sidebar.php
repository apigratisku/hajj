<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="admin-logo-small">
            <i class="fas fa-user-shield"></i>
        </div>
        <h3>Sistem Admin</h3>
        <div class="user-info mt-2">
            <small class="d-block text-light">
                <i class="fas fa-user"></i> 
                <?= $this->session->userdata('nama_lengkap') ?>
            </small>
            <small class="text-light badge" style="background: var(--accent-color); color: var(--white);" class="mt-1">
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
        
        <li class="nav-item">
            <a target="_blank" href="<?= base_url('qr') ?>" <?= $this->uri->segment(1) == 'qr' ? 'class="active"' : '' ?>>
                <i class="fas fa-qrcode"></i> <span>QR Code</span>
            </a>
        </li>
       
        <li class="nav-item">
            <a href="<?= base_url('qr-data') ?>" <?= $this->uri->segment(1) == 'qr-data' ? 'class="active"' : '' ?>>
                <i class="fas fa-database"></i> <span>QR Data</span>
            </a>
        </li>
        
        <div class="nav-section-divider"></div>
        
       
        
        <?php
        $filter_routes = ['filter_already_done', 'filter_on_target_done', 'filter_done', 'filter_already', 'filter_done_1_tahun', 'filter_cancel'];
        $current_filter_route = ($this->uri->segment(1) == 'database') ? $this->uri->segment(2) : '';
        $is_filter_menu_active = in_array($current_filter_route, $filter_routes, true);
        ?>
        <li class="nav-item">
            <a href="<?= base_url('database') ?>" <?= $this->uri->segment(1) == 'database' && $this->uri->segment(2) != 'rejected_data' && $this->uri->segment(2) != 'arsip' && !$is_filter_menu_active ? 'class="active"' : '' ?>>
                <i class="fas fa-user-friends"></i> <span>Data Peserta</span>
            </a>
        </li>
        <li class="nav-item nav-item-has-submenu">
            <a href="#" class="nav-submenu-toggle<?= $is_filter_menu_active ? ' active' : '' ?>" onclick="toggleFilterSubmenu(event)">
                <i class="fas fa-filter"></i> <span>Filter</span>
                <i class="fas fa-chevron-down submenu-chevron<?= $is_filter_menu_active ? ' rotated' : '' ?>"></i>
            </a>
            <ul class="submenu" id="filterSubmenu" style="<?= $is_filter_menu_active ? 'display: block;' : 'display: none;' ?>">
                
                <li>
                    <a href="<?= base_url('database/filter_on_target_done') ?>" <?= $current_filter_route == 'filter_on_target_done' ? 'class="active"' : '' ?>>
                        <i class="fas fa-arrow-right"></i> <span>On Target → Done</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('database/filter_already_done') ?>" <?= $current_filter_route == 'filter_already_done' ? 'class="active"' : '' ?>>
                        <i class="fas fa-arrow-right"></i> <span>Already → Done</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('database/filter_done') ?>" <?= $current_filter_route == 'filter_done' ? 'class="active"' : '' ?>>
                        <i class="fas fa-check"></i> <span>Done Gender</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('database/filter_already') ?>" <?= $current_filter_route == 'filter_already' ? 'class="active"' : '' ?>>
                        <i class="fas fa-clock"></i> <span>Already</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('database/filter_done_1_tahun') ?>" <?= $current_filter_route == 'filter_done_1_tahun' ? 'class="active"' : '' ?>>
                        <i class="fas fa-calendar-alt"></i> <span>Data > 1 Tahun</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('database/filter_cancel') ?>" <?= $current_filter_route == 'filter_cancel' ? 'class="active"' : '' ?>>
                        <i class="fas fa-ban"></i> <span>Filter Cancel</span>
                    </a>
                </li>
            </ul>
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
        
        
        
        <?php if ($this->session->userdata('role') == 'admin'): ?>
        <li class="nav-item">
            <a href="<?= base_url('database/arsip') ?>" <?= $this->uri->segment(1) == 'database' && $this->uri->segment(2) == 'arsip' ? 'class="active"' : '' ?>>
                <i class="fas fa-archive"></i> <span>Arsip Data</span>
            </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
            <a href="<?= base_url('laporan') ?>" <?= $this->uri->segment(1) == 'laporan' ? 'class="active"' : '' ?>>
                <i class="fas fa-chart-bar"></i> <span>Laporan</span>
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
        
        <!-- Email Management -->
        <?php if ($this->session->userdata('role') == 'admin'): ?> 
        <?php if ($this->session->userdata('username') == 'adhit' || $this->session->userdata('username') == 'mimin'): ?>
        <li class="nav-item">
            <a href="<?= base_url('email') ?>" <?= $this->uri->segment(1) == 'email' ? 'class="active"' : '' ?>>
                <i class="fas fa-envelope-open"></i> <span>Manajemen Email</span>
            </a>
        </li>
        <?php endif; ?>
        <?php endif; ?>
        
        <!-- System Settings -->
        <?php if ($this->session->userdata('role') == 'admin'): ?>
            <?php if ($this->session->userdata('username') == 'adhit' || $this->session->userdata('username') == 'mimin'): ?>
        <li class="nav-item">
            <a href="<?= base_url('settings') ?>" <?= $this->uri->segment(1) == 'settings' ? 'class="active"' : '' ?>>
                <i class="fas fa-cogs"></i> <span>Pengaturan Sistem</span>
            </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
            <a href="<?= base_url('sync-production') ?>" <?= ($this->uri->segment(1) == 'sync-production' || $this->uri->segment(1) == 'syncproduction') ? 'class="active"' : '' ?>>
                <i class="fas fa-sync-alt"></i> <span>Sync Production</span>
            </a>
        </li>
        <?php endif; ?>
        <!-- Log Aktifitas -->
        <?php if ($this->session->userdata('role') == 'admin'): ?>
        <li class="nav-item">
            <a href="<?= base_url('log_activity') ?>" <?= $this->uri->segment(1) == 'log_activity' ? 'class="active"' : '' ?>>
                <i class="fas fa-history"></i> <span>Log Aktifitas</span>
            </a>
        </li>
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
.admin-logo-small {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
    position: relative;
}

.admin-logo-small i {
    font-size: 1.5rem;
    color: var(--white);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.nav-item-has-submenu > .nav-submenu-toggle {
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.nav-item-has-submenu > .nav-submenu-toggle .submenu-chevron {
    font-size: 0.7rem;
    margin-left: auto;
    margin-right: 0;
    width: auto;
    transition: transform 0.2s ease;
}

.nav-item-has-submenu > .nav-submenu-toggle .submenu-chevron.rotated {
    transform: rotate(180deg);
}
</style>

<script>
function toggleFilterSubmenu(event) {
    event.preventDefault();
    const submenu = document.getElementById('filterSubmenu');
    const chevron = event.currentTarget.querySelector('.submenu-chevron');
    if (!submenu) return;

    const isVisible = submenu.style.display === 'block';
    submenu.style.display = isVisible ? 'none' : 'block';
    if (chevron) {
        chevron.classList.toggle('rotated', !isVisible);
    }
}
</script> 