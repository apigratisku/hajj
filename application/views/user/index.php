<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                  
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('user/add'); ?>" class="btn btn-sm btn-tambah">
                            <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Tambah User</span>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    
                    <!-- Mobile User Cards -->
                    <div class="mobile-user-container d-block d-lg-none">
                        <?php if (empty($users)): ?>
                            <div class="no-data-mobile">
                                <i class="fas fa-users fa-3x text-muted"></i>
                                <p>Tidak ada data user</p>
                            </div>
                        <?php else: ?>
                            <?php $no = 1; foreach ($users as $user): ?>
                            <div class="mobile-user-card" data-id="<?= $user->id_user ?>">
                                <div class="mobile-user-header">
                                    <div class="user-avatar">
                                        <i class="fas fa-user-circle fa-2x"></i>
                                    </div>
                                    <div class="user-info">
                                        <h6 class="user-name"><?= $user->nama_lengkap ?></h6>
                                        <span class="user-username">@<?= $user->username ?></span>
                                    </div>
                                    <div class="user-role">
                                        <span class="role-badge role-<?= $user->role ?>"><?= ucfirst($user->role) ?></span>
                                    </div>
                                </div>
                                <div class="mobile-user-actions">
                                    <a href="<?= base_url('user/edit/'.$user->id_user); ?>" class="btn btn-sm btn-edit-mobile">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if($this->session->userdata('role') == 'admin'): ?>
                                    <a href="<?= base_url('user/delete/'.$user->id_user); ?>" class="btn btn-sm btn-delete-mobile" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-bordered table-striped table-hover" id="user-table">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Username</th>
                                    <th class="text-center">Nama Lengkap</th>
                                    <th class="text-center">Role</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data user</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($users as $user): ?>
                                    <tr data-id="<?= $user->id_user ?>">
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="text-center"><?= $user->username; ?></td>
                                        <td class="text-center"><?= $user->nama_lengkap; ?></td>
                                        <td class="text-center">
                                            <span class="role-badge role-<?= $user->role ?>"><?= ucfirst($user->role); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('user/edit/'.$user->id_user); ?>" class="btn btn-sm btn-brown btn-edit" data-bs-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if($this->session->userdata('role') == 'admin'): ?>
                                            <a href="<?= base_url('user/delete/'.$user->id_user); ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash Message -->
<?php if ($this->session->flashdata('message')) : ?>
    <div class="alert alert-success alert-dismissible fade show position-fixed" role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
        <?= $this->session->flashdata('message'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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

.btn-tambah {
    background-color: var(--success-color) !important;
    border-color: var(--success-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-tambah:hover {
    background-color: #218838 !important;
    border-color: #218838 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-brown {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-brown:hover {
    background-color: var(--primary-light) !important;
    border-color: var(--primary-light) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
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
    padding: 1rem;
}

/* Mobile User Container */
.mobile-user-container {
    padding: 1rem;
    background: white;
}

.mobile-user-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.mobile-user-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.mobile-user-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.user-avatar {
    color: var(--primary-color);
}

.user-info {
    flex: 1;
}

.user-name {
    margin: 0;
    font-weight: 600;
    color: var(--dark-color);
}

.user-username {
    color: #6c757d;
    font-size: 0.9rem;
}

.user-role {
    text-align: right;
}

.role-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.role-admin {
    background: var(--danger-color);
    color: white;
}

.role-operator {
    background: var(--info-color);
    color: white;
}

.mobile-user-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-edit-mobile {
    background: var(--warning-color);
    border-color: var(--warning-color);
    color: #212529;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-edit-mobile:hover {
    background: #e0a800;
    border-color: #e0a800;
    color: #212529;
    transform: translateY(-1px);
}

.btn-delete-mobile {
    background: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-delete-mobile:hover {
    background: #c82333;
    border-color: #c82333;
    color: white;
    transform: translateY(-1px);
}

/* No Data Mobile */
.no-data-mobile {
    text-align: center;
    padding: 3rem 1rem;
    color: #666;
}

.no-data-mobile i {
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Desktop Table Styles */
.table {
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.table thead th {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
    border: none;
    padding: 1rem 0.75rem;
    font-weight: 600;
    text-align: center;
}

.table tbody td {
    padding: 0.75rem;
    vertical-align: middle;
    border-color: #e9ecef;
}

.table tbody tr:hover {
    background-color: rgba(139, 69, 19, 0.05);
}

/* Button Styles */
.btn-edit {
    background: var(--warning-color);
    border-color: var(--warning-color);
    color: #212529;
}

.btn-edit:hover {
    background: #e0a800;
    border-color: #e0a800;
    color: #212529;
}

.btn-delete {
    background: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
}

.btn-delete:hover {
    background: #c82333;
    border-color: #c82333;
    color: white;
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

/* Mobile Media Queries */
@media (max-width: 768px) {
    .mobile-user-card {
        padding: 0.75rem;
    }
    
    .mobile-user-header {
        gap: 0.75rem;
    }
    
    .user-avatar i {
        font-size: 1.5rem;
    }
    
    .user-name {
        font-size: 1rem;
    }
    
    .user-username {
        font-size: 0.8rem;
    }
    
    .role-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
    }
    
    .mobile-user-actions {
        gap: 0.25rem;
    }
    
    .btn-edit-mobile, .btn-delete-mobile {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
}

@media (max-width: 576px) {
    .mobile-user-container {
        padding: 0.5rem;
    }
    
    .mobile-user-card {
        padding: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .mobile-user-header {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .user-role {
        text-align: center;
    }
    
    .mobile-user-actions {
        justify-content: center;
    }
}
</style> 