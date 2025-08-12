<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                   
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('database/tambah') ?>" class="btn btn-sm btn-add">
                            <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Tambah</span>
                        </a>
                        <a href="<?= base_url('database/import') ?>" class="btn btn-sm btn-import">
                            <i class="fas fa-file-import"></i> <span class="d-none d-sm-inline">Import</span>
                        </a>
                        <a href="<?= base_url('database/export') ?>" class="btn btn-sm btn-export" target="_blank">
                            <i class="fas fa-file-export"></i> <span class="d-none d-sm-inline">Export</span>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    
                    <!-- Mobile Search Form -->
                    <div class="mobile-search-container d-block d-md-none">
                        <div class="search-toggle" onclick="toggleMobileSearch()">
                            <i class="fas fa-search"></i> Cari Data
                        </div>
                        <div class="mobile-search-form" id="mobileSearchForm" style="display: none;">
                            <form method="get" action="<?= base_url('database/index') ?>" class="mobile-form" id="mobileSearchForm">
                                <div class="form-group">
                                    <input type="text" name="nama" value="<?= isset($_GET['nama']) ? htmlspecialchars($_GET['nama']) : '' ?>" class="form-control mobile-input" placeholder="Nama Peserta">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="nomor_paspor" value="<?= isset($_GET['nomor_paspor']) ? htmlspecialchars($_GET['nomor_paspor']) : '' ?>" class="form-control mobile-input" placeholder="No Paspor">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="no_visa" value="<?= isset($_GET['no_visa']) ? htmlspecialchars($_GET['no_visa']) : '' ?>" class="form-control mobile-input" placeholder="No Visa">
                                </div>
                                <div class="form-group">
                                    <select name="tanggaljam" class="form-select mobile-input">
                                        <option value="">Waktu</option>
                                        <?php if (!empty($tanggaljam_list)): foreach ($tanggaljam_list as $tanggaljam): ?>
                                            <option value="<?= htmlspecialchars($tanggaljam->tanggaljam) ?>" <?= (isset($_GET['tanggaljam']) && $_GET['tanggaljam'] === $tanggaljam->tanggaljam) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tanggaljam->tanggaljam) ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select name="flag_doc" class="form-select mobile-input">
                                        <option value="">Semua Flag Dokumen</option>
                                        <option value="null" <?= (isset($_GET['flag_doc']) && $_GET['flag_doc'] === 'null') ? 'selected' : '' ?>>Tanpa Flag Dokumen</option>
                                        <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                            <option value="<?= htmlspecialchars($flag->flag_doc) ?>" <?= (isset($_GET['flag_doc']) && $_GET['flag_doc'] === $flag->flag_doc) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($flag->flag_doc) ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-search">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <a href="<?= base_url('database/index') ?>" class="btn btn-reset">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Desktop Search Form -->
                    <div class="desktop-search-container d-none d-md-block">
                        <form method="get" action="<?= base_url('database/index') ?>" class="desktop-form">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-2">
                                    <input type="text" name="nama" value="<?= isset($_GET['nama']) ? htmlspecialchars($_GET['nama']) : '' ?>" class="form-control form-control-sm" placeholder="Nama Peserta">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="nomor_paspor" value="<?= isset($_GET['nomor_paspor']) ? htmlspecialchars($_GET['nomor_paspor']) : '' ?>" class="form-control form-control-sm" placeholder="No Paspor">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="no_visa" value="<?= isset($_GET['no_visa']) ? htmlspecialchars($_GET['no_visa']) : '' ?>" class="form-control form-control-sm" placeholder="No Visa">
                                </div>
                                <div class="col-md-2">
                                    <select name="flag_doc" class="form-select form-control-sm">
                                        <option value="">Semua Flag Dokumen</option>
                                        <option value="null" <?= (isset($_GET['flag_doc']) && $_GET['flag_doc'] === 'null') ? 'selected' : '' ?>>Tanpa Flag Dokumen</option>
                                        <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                            <option value="<?= htmlspecialchars($flag->flag_doc) ?>" <?= (isset($_GET['flag_doc']) && $_GET['flag_doc'] === $flag->flag_doc) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($flag->flag_doc) ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="tanggaljam" class="form-select form-control-sm">
                                        <option value="">Waktu</option>
                                        <?php if (!empty($tanggaljam_list)): foreach ($tanggaljam_list as $tanggaljam): ?>
                                            <option value="<?= htmlspecialchars($tanggaljam->tanggaljam) ?>" <?= (isset($_GET['tanggaljam']) && $_GET['tanggaljam'] === $tanggaljam->tanggaljam) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tanggaljam->tanggaljam) ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-brown btn-sm me-2">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <a href="<?= base_url('database/index') ?>" class="btn btn-brown-light btn-sm">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Mobile Data Cards -->
                    <div class="mobile-data-container d-block d-lg-none">
                        <?php if (empty($peserta)): ?>
                            <div class="no-data-mobile">
                                <i class="fas fa-inbox fa-3x text-muted"></i>
                                <p>Tidak ada data</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($peserta as $p): ?>
                                <div class="mobile-card-item" data-id="<?= $p->id ?>">
                                    <div class="card-header-mobile">
                                        <h6 class="card-title"><?= $p->nama ?></h6>
                                        
                                    </div>
                                    <div class="card-body-mobile">
                                        <div class="data-row">
                                            <span class="label">Nama</span>
                                            <span class="value" data-field="nama" data-value="<?= $p->nama ?>"><?= $p->nama ?></span>
                                            <input type="text" class="mobile-edit-field" value="<?= $p->nama ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </div>
                                        <div class="data-row">
                                            <span class="label">No Paspor</span>
                                            <span class="value" data-field="nomor_paspor" data-value="<?= $p->nomor_paspor ?>"><?= $p->nomor_paspor ?></span>
                                            <input type="text" class="mobile-edit-field" value="<?= $p->nomor_paspor ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </div>
                                        <div class="data-row">
                                            <span class="label">No Visa</span>
                                            <span class="value" data-field="no_visa" data-value="<?= $p->no_visa ?>"><?= $p->no_visa ?: '-' ?></span>
                                            <input type="text" class="mobile-edit-field" value="<?= $p->no_visa ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </div>
                                        <div class="data-row">
                                            <span class="label">No. HP</span>
                                            <span class="value" data-field="nomor_hp" data-value="<?= $p->nomor_hp ?>"><?= $p->nomor_hp ?: '-' ?></span>
                                            <input type="text" class="mobile-edit-field" value="<?= $p->nomor_hp ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </div>
                                        <div class="data-row">
                                            <span class="label">Email</span>
                                            <span class="value" data-field="email" data-value="<?= $p->email ?>"><?= $p->email ?: '-' ?></span>
                                            <input type="email" class="mobile-edit-field" value="<?= $p->email ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </div>
                                        <div class="data-row">
                                            <span class="label">Gender</span>
                                            <span class="value" data-field="gender" data-value="<?= $p->gender ?>"><?= $p->gender ?: '-' ?></span>
                                            <select class="mobile-edit-field" style="display:none;">
                                                <option value="">Pilih Gender</option>
                                                <option value="L" <?= $p->gender == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                <option value="P" <?= $p->gender == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="data-row">
                                            <span class="label">Status</span>
                                            <span class="value status-badge status-<?= $p->status ?>" data-field="status" data-value="<?= $p->status ?>">
                                                <?= $p->status == 0 ? 'On Target' : ($p->status == 1 ? 'Already' : 'Done') ?>
                                            </span>
                                            <select class="mobile-edit-field" style="display:none;">
                                                <option value="0" <?= $p->status == 0 ? 'selected' : '' ?>>On Target</option>
                                                <option value="1" <?= $p->status == 1 ? 'selected' : '' ?>>Already</option>
                                                <option value="2" <?= $p->status == 2 ? 'selected' : '' ?>>Done</option>
                                            </select>
                                        </div>
                                        <div class="data-row">
                                            <span class="label">Flag Doc</span>
                                            <span class="value" data-field="flag_doc" data-value="<?= $p->flag_doc ?>"><?= $p->flag_doc ?: '-' ?></span>    
                                            <select class="mobile-edit-field" style="display:none;">
                                                <option value="">Flag Doc:</option>
                                                <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                                    <option value="<?= htmlspecialchars($flag->flag_doc) ?>"
                                                        <?= (!empty($p->flag_doc) && $p->flag_doc === $flag->flag_doc) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($flag->flag_doc) ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>

                                        </div>
                                        
                                        <div class="data-row">
                                            <span class="label">Tanggal</span>
                                            <span class="value" data-field="tanggal" data-value="<?= $p->tanggal ?>"><?= $p->tanggal ?: '-' ?></span>
                                            <input type="date" class="mobile-edit-field" value="<?= $p->tanggal ?>" style="display:none;">
                                        </div>  
                                        <div class="data-row">
                                            <span class="label">Jam</span>
                                            <span class="value" data-field="jam" data-value="<?= $p->jam ?>"><?= $p->jam ?: '-' ?></span>
                                            <input type="time" class="mobile-edit-field" value="<?= $p->jam ?>" style="display:none;">
                                        </div>

                                        <div class="card-actions">
                                            <button class="btn btn-sm btn-edit-mobile" onclick="toggleEditMobile(this)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-save-mobile" style="display:none;" onclick="saveRowMobile(this)">
                                                <i class="fas fa-save"></i>
                                            </button>
                                            <button class="btn btn-sm btn-cancel-mobile" style="display:none;" onclick="cancelEditMobile(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <a href="<?= base_url('database/delete/' . $p->id) ?>" class="btn btn-sm btn-delete-mobile" onclick="return confirm('Hapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-lg-block">
                        <table class="table table-bordered table-striped table-hover" id="transaksi-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Nama Peserta</th>
                                    <th class="text-center">No Paspor</th>
                                    <th class="text-center">No Visa</th>
                                    <th class="text-center">Tgl. Lahir</th>
                                    <th class="text-center">Password</th>
                                    <th class="text-center">No. HP</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Gender</th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Jam</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Flag Dokumen</th>
                                    <th width="100" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="transaksi-tbody">
                                <?php if (empty($peserta)): ?>
                                    <tr>
                                        <td colspan="13" class="text-center">Tidak ada data</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($peserta as $p): ?>
                                        <tr data-id="<?= $p->id ?>">
                                            <td class="nama-peserta" data-field="nama" data-value="<?= $p->nama ?>">
                                                <span class="display-value"><?= $p->nama ?></span>
                                                <input type="text" class="form-control edit-field" value="<?= $p->nama ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="no-paspor text-center" data-field="nomor_paspor" data-value="<?= $p->nomor_paspor ?>">
                                                <span class="display-value"><?= $p->nomor_paspor ?></span>
                                                <input type="text" class="form-control edit-field" value="<?= $p->nomor_paspor ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="no-visa text-center" data-field="no_visa" data-value="<?= $p->no_visa ?>">
                                                <span class="display-value"><?= $p->no_visa ?></span>
                                                <input type="text" class="form-control edit-field" value="<?= $p->no_visa ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="tgl-lahir text-center" data-field="tgl_lahir" data-value="<?= $p->tgl_lahir ?>">
                                                <span class="display-value"><?= $p->tgl_lahir ? date('d/m/Y', strtotime($p->tgl_lahir)) : '-' ?></span>
                                                <input type="date" class="form-control edit-field" value="<?= $p->tgl_lahir ? date('Y-m-d', strtotime($p->tgl_lahir)) : '' ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="password text-center" data-field="password" data-value="<?= $p->password ?>">
                                                <span class="display-value"><?= $p->password ?></span>
                                                <input type="password" class="form-control edit-field" value="<?= $p->password ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="no-hp text-center" data-field="nomor_hp" data-value="<?= $p->nomor_hp ?>">
                                                <span class="display-value"><?= $p->nomor_hp ?></span>
                                                <input type="text" class="form-control edit-field" value="<?= $p->nomor_hp ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="email text-center" data-field="email" data-value="<?= $p->email ?>"> 
                                                <span class="display-value"><?= $p->email ?></span>
                                                <input type="email" class="form-control edit-field" value="<?= $p->email ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="gender text-center" data-field="gender" data-value="<?= $p->gender ?>">
                                                <span class="display-value"><?= $p->gender ?></span>
                                                <select class="form-select edit-field" style="display:none;">
                                                    <option value="">Pilih Gender</option>
                                                    <option value="" <?= $p->gender == '' ? 'selected' : '' ?>></option>
                                                    <option value="L" <?= $p->gender == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                    <option value="P" <?= $p->gender == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                </select>
                                            </td>
                                            <td class="tanggal text-center" data-field="tanggal" data-value="<?= $p->tanggal ?>">
                                                <span class="display-value"><?= $p->tanggal ?></span>
                                                <input type="date" class="form-control edit-field" value="<?= $p->tanggal ?>" style="display:none;">
                                            </td>
                                            <td class="jam text-center" data-field="jam" data-value="<?= $p->jam ?>">
                                                <span class="display-value"><?= $p->jam ?></span>
                                                <input type="time" class="form-control edit-field" value="<?= $p->jam ?>" style="display:none;">
                                            </td>
                                            <td class="status text-center" data-field="status" data-value="<?= $p->status ?>">
                                                <span class="display-value"><?= $p->status == 0 ? 'On Target' : ($p->status == 1 ? 'Already' : 'Done') ?></span>
                                                <select class="form-select edit-field" style="display:none;">
                                                    <option value="0" <?= $p->status == 0 ? 'selected' : '' ?>>On Target</option>
                                                    <option value="1" <?= $p->status == 1 ? 'selected' : '' ?>>Already</option>
                                                    <option value="2" <?= $p->status == 2 ? 'selected' : '' ?>>Done</option>
                                                </select>
                                            </td>
                                            <td class="flag-doc text-center" data-field="flag_doc" data-value="<?= $p->flag_doc ?>">
                                                <span class="display-value"><?= $p->flag_doc ?: '-' ?></span>
                                                <select class="form-select edit-field" style="display:none;">
                                                <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                                    <option value="<?= htmlspecialchars($flag->flag_doc) ?>" <?= (isset($_GET['flag_doc']) && $_GET['flag_doc'] === $flag->flag_doc) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($flag->flag_doc) ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                            </td>
                                            
                                            <td class="text-center aksi" style="width: 13%;">
                                                <button class="btn btn-sm btn-brown btn-edit" data-bs-toggle="tooltip" title="Edit" onclick="toggleEdit(this)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success btn-save" data-bs-toggle="tooltip" title="Simpan" style="display:none;" onclick="saveRow(this)">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                                <button class="btn btn-sm btn-secondary btn-cancel" data-bs-toggle="tooltip" title="Batal" style="display:none;" onclick="cancelEdit(this)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <a href="<?= base_url('database/delete/' . $p->id) ?>" class="btn btn-sm btn-danger btn-action" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini? <?= $p->nama ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Enhanced Pagination -->
                    <div class="pagination-container">
                        <?php if(isset($pagination) && !empty($pagination) && isset($total_rows) && isset($per_page) && $total_rows > $per_page): ?>
                            <?= $pagination ?>
                            <div class="text-center text-muted mt-2">
                                <small>Menampilkan <?= ($offset + 1) ?> - <?= min($offset + $per_page, $total_rows) ?> dari <?= $total_rows ?> data</small>
                            </div>
                        <?php elseif(isset($total_rows) && $total_rows > 0): ?>
                            <div class="text-center text-muted py-3">
                                <small>Menampilkan semua <?= $total_rows ?> data</small>
                            </div>
                        <?php elseif(isset($peserta) && is_array($peserta) && count($peserta) > 0): ?>
                            <div class="text-center text-muted py-3">
                                <small>Menampilkan <?= count($peserta) ?> data</small>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <small>Tidak ada data yang ditemukan</small>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Debug Info (remove in production) -->
                        <?php if(isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                            <div class="text-center text-info mt-2">
                                <small>
                                    Debug: total_rows=<?= isset($total_rows) ? $total_rows : 'null' ?>, 
                                    per_page=<?= isset($per_page) ? $per_page : 'null' ?>, 
                                    page=<?= isset($current_page) ? $current_page : 'null' ?>, 
                                    offset=<?= isset($offset) ? $offset : 'null' ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Floating Action Button 
    <div class="d-block d-lg-none">
        <a href="<?= base_url('database/tambah') ?>" class="mobile-fab" title="Tambah Data Baru">
            <i class="fas fa-plus"></i>
        </a>
    </div>-->
</div>

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

.btn-brown-light {
    background-color: var(--secondary-color) !important;
    border-color: var(--secondary-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-brown-light:hover {
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

/* Mobile Search Styles */
.mobile-search-container {
    padding: 1rem;
    background: var(--light-color);
    border-bottom: 1px solid #dee2e6;
}

.search-toggle {
    background: white;
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius);
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow);
}

.search-toggle:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

.mobile-search-form {
    margin-top: 1rem;
    padding: 1rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.mobile-form .form-group {
    margin-bottom: 1rem;
}

.mobile-input {
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    padding: 0.75rem;
    font-size: 1rem;
    transition: var(--transition);
}

.mobile-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
}

.form-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn-search, .btn-reset {
    flex: 1;
    padding: 0.75rem;
    border-radius: var(--border-radius);
    border: none;
    font-weight: 600;
    transition: var(--transition);
}

.btn-search {
    background: var(--primary-color);
    color: white;
}

.btn-search:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
}

.btn-reset {
    background: var(--danger-color);
    color: white;
}

.btn-reset:hover {
    background: #c82333;
    transform: translateY(-2px);
}

/* Mobile Data Cards */
.mobile-data-container {
    padding: 1rem;
}

.mobile-card-item {
    background: white;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
    box-shadow: var(--shadow);
    border: 1px solid #e9ecef;
    transition: var(--transition);
    overflow: hidden;
}

.mobile-card-item:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.card-header-mobile {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-edit-mobile, .btn-save-mobile, .btn-cancel-mobile, .btn-delete-mobile {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.btn-edit-mobile {
    background: var(--info-color);
    color: white;
}

.btn-save-mobile {
    background: var(--success-color);
    color: white;
}

.btn-cancel-mobile {
    background: var(--warning-color);
    color: white;
}

.btn-delete-mobile {
    background: var(--danger-color);
    color: white;
}

.card-body-mobile {
    padding: 1rem;
}
.data-row-inline {
    display: flex;
    align-items: center;
    gap: 8px; /* jarak antar elemen */
}

.data-row-inline .label {
    font-weight: bold;
}

.data-row-inline .mobile-edit-field {
    flex: none;
    width: auto;
}


.data-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.data-row:last-child {
    border-bottom: none;
}

.label {
    font-weight: 600;
    color: var(--dark-color);
    min-width: 80px;
}

.value {
    color: #666;
    text-align: right;
    flex: 1;
}

.mobile-edit-field {
    border: 2px solid var(--primary-color);
    border-radius: 8px;
    padding: 0.5rem;
    width: 100%;
    margin-top: 0.5rem;
}

/* Status Badges */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    text-align: center;
}

.status-0 {
    background: #e3f2fd;
    color: #1976d2;
}

.status-1 {
    background: #fff3e0;
    color: #f57c00;
}

.status-2 {
    background: #e8f5e8;
    color: #388e3c;
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

/* Enhanced Pagination Styles */
.pagination-container {
    padding: 1.5rem;
    background: white;
    border-top: 1px solid #e9ecef;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination .page-item {
    margin: 0 0.25rem;
}

.pagination .page-link {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    background: white;
    border-radius: var(--border-radius);
    padding: 0.75rem 1rem;
    font-weight: 600;
    transition: var(--transition);
    min-width: 44px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination .page-link:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.pagination .page-item.active .page-link {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    box-shadow: var(--shadow);
}

.pagination .page-item.disabled .page-link {
    border-color: #dee2e6;
    color: #6c757d;
    background: #f8f9fa;
    cursor: not-allowed;
}

/* Desktop Styles */
.desktop-search-container {
    padding: 1.5rem;
    background: var(--light-color);
    border-bottom: 1px solid #dee2e6;
}

.desktop-form .form-control {
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.desktop-form .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
}

/* Table Enhancements */
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

/* Button Enhancements */
.btn-add {
    background: var(--accent-color) !important;
    border-color: var(--accent-color) !important;
    color: white !important;
}

.btn-import {
    background: var(--info-color) !important;
    border-color: var(--info-color) !important;
    color: white !important;
}

.btn-export {
    background: var(--success-color) !important;
    border-color: var(--success-color) !important;
    color: white !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .mobile-card .card-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .card-actions {
        flex-wrap: wrap;
    }
    
    .pagination {
        gap: 0.25rem;
    }
    
    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        min-width: 40px;
        font-size: 0.875rem;
    }
}

@media (max-width: 576px) {
    .mobile-card-item {
        margin-bottom: 0.75rem;
    }
    
    .card-header-mobile {
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }
    
    .data-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .value {
        text-align: left;
        width: 100%;
    }
    
    .pagination .page-link {
        padding: 0.4rem 0.6rem;
        min-width: 36px;
        font-size: 0.8rem;
    }
}

/* Animation Effects */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.mobile-card-item {
    animation: slideIn 0.3s ease-out;
}

/* Edit Field Styles */
.edit-field {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
    border: 2px solid var(--primary-color);
    border-radius: 8px;
    transition: var(--transition);
}

.edit-field:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
}

/* Mobile Edit Field Styles */
.mobile-edit-field {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border: 2px solid var(--primary-color);
    border-radius: 8px;
    transition: var(--transition);
    width: 100%;
    background: white;
    margin-top: 0.25rem;
}

.mobile-edit-field:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
    outline: none;
    transform: translateY(-1px);
}

.mobile-edit-field[readonly],
.mobile-edit-field[disabled] {
    background-color: #f8f9fa;
    opacity: 0.6;
    cursor: not-allowed;
}

/* Edit Mode Visual Feedback */
.mobile-card-item.editing {
    border: 2px solid var(--primary-color);
    box-shadow: 0 0 20px rgba(139, 69, 19, 0.2);
}

.mobile-card-item.editing .card-header-mobile {
    background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
}

/* Save Button Animation */
.btn-save-mobile {
    background: var(--success-color);
    border-color: var(--success-color);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Alert Styles */
.alert {
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: none;
    margin-bottom: 1rem;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

/* Enhanced Pagination Styles */
.pagination-container {
    padding: 1.5rem;
    background: white;
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
}

/* Pagination Info Text */
.pagination-container .text-muted {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Ensure pagination is visible on all devices */
.pagination {
    display: flex !important;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin: 0;
    list-style: none;
    padding: 0;
}

.pagination li {
    margin: 0 0.25rem;
}

.pagination a,
.pagination span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    padding: 0.75rem 1rem;
    text-decoration: none;
    border: 2px solid var(--primary-color);
    border-radius: var(--border-radius);
    color: var(--primary-color);
    background: white;
    font-weight: 600;
    transition: var(--transition);
    text-align: center;
}

.pagination a:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.pagination .active a,
.pagination .active span {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    box-shadow: var(--shadow);
}

.pagination .disabled a,
.pagination .disabled span {
    border-color: #dee2e6;
    color: #6c757d;
    background: #f8f9fa;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination-custom {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin: 0;
}

.pagination-custom .page-item {
    margin: 0 0.25rem;
}

.pagination-custom .page-link {
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    background: white;
    border-radius: var(--border-radius);
    padding: 0.75rem 1rem;
    font-weight: 600;
    transition: var(--transition);
    min-width: 44px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.pagination-custom .page-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.pagination-custom .page-link:hover::before {
    left: 100%;
}

.pagination-custom .page-link:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
    border-color: var(--primary-color);
}

.pagination-custom .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border-color: var(--primary-color);
    color: white;
    box-shadow: var(--shadow);
    transform: scale(1.05);
}

.pagination-custom .page-item.disabled .page-link {
    border-color: #dee2e6;
    color: #6c757d;
    background: #f8f9fa;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.pagination-custom .page-item.disabled .page-link:hover {
    background: #f8f9fa;
    color: #6c757d;
    transform: none;
    box-shadow: none;
}

/* Mobile Pagination Enhancements */
@media (max-width: 768px) {
    .pagination-custom {
        gap: 0.25rem;
    }
    
    .pagination-custom .page-link {
        padding: 0.5rem 0.75rem;
        min-width: 40px;
        font-size: 0.875rem;
    }
    
    .pagination-custom .page-link i {
        font-size: 0.8rem;
    }
    
    /* Mobile pagination container */
    .pagination-container {
        padding: 1rem;
    }
    
    /* Mobile pagination links */
    .pagination a,
    .pagination span {
        min-width: 40px;
        height: 40px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 576px) {
    .pagination-custom .page-link {
        padding: 0.4rem 0.6rem;
        min-width: 36px;
        font-size: 0.8rem;
    }
    
    .pagination-custom .page-link i {
        font-size: 0.7rem;
    }
    
    /* Hide some pagination items on very small screens */
    .pagination-custom .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
}

/* Pagination Animation */
@keyframes paginationPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.pagination-custom .page-item.active .page-link {
    animation: paginationPulse 2s infinite;
}

/* Loading Overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    border-radius: var(--border-radius);
}

.loading-overlay .spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Floating Action Button for Mobile */
.mobile-fab {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--accent-color);
    color: white;
    border: none;
    box-shadow: var(--shadow-hover);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: var(--transition);
    z-index: 1000;
}

.mobile-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 20px rgba(0, 0, 0, 0.3);
}

/* Mobile Card Enhancements */
.mobile-card-item {
    position: relative;
    overflow: hidden;
}

.mobile-card-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--primary-color));
    background-size: 200% 100%;
    animation: gradientShift 3s ease-in-out infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Enhanced Button Styles */
.btn-add {
    background: var(--accent-color) !important;
    border-color: var(--accent-color) !important;
    color: white !important;
    border-radius: 25px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    transition: var(--transition);
}

.btn-add:hover {
    background: #e55a2b !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-import {
    background: var(--info-color) !important;
    border-color: var(--info-color) !important;
    color: white !important;
    border-radius: 25px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    transition: var(--transition);
}

.btn-import:hover {
    background: #138496 !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-export {
    background: var(--success-color) !important;
    border-color: var(--success-color) !important;
    color: white !important;
    border-radius: 25px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    transition: var(--transition);
}

.btn-export:hover {
    background: #218838 !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}
</style>

<script>
// Mobile search toggle
function toggleMobileSearch() {
    const form = document.getElementById('mobileSearchForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.style.animation = 'slideIn 0.3s ease-out';
    } else {
        form.style.display = 'none';
    }
}

// Enhanced pagination functionality
function enhancePagination() {
    // Debug: Log current URL and parameters
    console.log('Current URL:', window.location.href);
    console.log('Current filters:', {
        nama: new URLSearchParams(window.location.search).get('nama'),
        nomor_paspor: new URLSearchParams(window.location.search).get('nomor_paspor'),
        no_visa: new URLSearchParams(window.location.search).get('no_visa'),
        flag_doc: new URLSearchParams(window.location.search).get('flag_doc'),
        tanggaljam: new URLSearchParams(window.location.search).get('tanggaljam'),
        page: new URLSearchParams(window.location.search).get('page')
    });
    
    // Add click event listeners to pagination links
    const paginationLinks = document.querySelectorAll('.pagination a');
    console.log('Found pagination links:', paginationLinks.length);
    
    if (paginationLinks.length > 0) {
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                console.log('Pagination link clicked:', link.href);
                
                // Show loading state
                const container = document.querySelector('.mobile-data-container, .table-responsive');
                if (container) {
                    container.style.opacity = '0.6';
                    container.style.pointerEvents = 'none';
                    
                    // Add loading indicator
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'loading-overlay';
                    loadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                    loadingDiv.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 1000;';
                    
                    container.style.position = 'relative';
                    container.appendChild(loadingDiv);
                }
                
                // Add a small delay to show loading state
                setTimeout(() => {
                    window.location.href = link.href;
                }, 100);
            });
        });
    } else {
        console.log('No pagination links found');
    }
}

// Mobile edit functions
function toggleEditMobile(button) {
    const card = button.closest('.mobile-card-item');
    const editFields = card.querySelectorAll('.mobile-edit-field');
    const values = card.querySelectorAll('.value');
    const editBtn = card.querySelector('.btn-edit-mobile');
    const saveBtn = card.querySelector('.btn-save-mobile');
    const cancelBtn = card.querySelector('.btn-cancel-mobile');
    
    // Add editing class for visual feedback
    card.classList.add('editing');
    
    editFields.forEach(field => field.style.display = 'block');
    values.forEach(value => value.style.display = 'none');
    
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-flex';
    cancelBtn.style.display = 'inline-flex';
}

function cancelEditMobile(button) {
    const card = button.closest('.mobile-card-item');
    const editFields = card.querySelectorAll('.mobile-edit-field');
    const values = card.querySelectorAll('.value');
    const editBtn = card.querySelector('.btn-edit-mobile');
    const saveBtn = card.querySelector('.btn-save-mobile');
    const cancelBtn = card.querySelector('.btn-cancel-mobile');
    
    editFields.forEach((field, index) => {
        const valueElement = values[index];
        const originalValue = valueElement.getAttribute('data-value');
        
        if (field.tagName === 'SELECT') {
            field.value = originalValue || '';
        } else {
            field.value = originalValue || '';
        }
    });
    
    // Remove editing class
    card.classList.remove('editing');
    
    editFields.forEach(field => field.style.display = 'none');
    values.forEach(value => value.style.display = 'inline');
    
    editBtn.style.display = 'inline-flex';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
}

function saveRowMobile(button) {
    const card = button.closest('.mobile-card-item');
    const rowId = card.getAttribute('data-id');
    const editFields = card.querySelectorAll('.mobile-edit-field');
    const values = card.querySelectorAll('.value');
    const editBtn = card.querySelector('.btn-edit-mobile');
    const saveBtn = card.querySelector('.btn-save-mobile');
    const cancelBtn = card.querySelector('.btn-cancel-mobile');
    
    const data = {};
    let hasData = false;
    
    editFields.forEach((field, index) => {
        // Get the corresponding value element to find the field name
        const valueElement = values[index];
        const fieldName = valueElement.getAttribute('data-field');
        if (fieldName) {
            data[fieldName] = field.value;
            hasData = true;
        }
    });
    
    // Validate that we have data to save
    if (!hasData || Object.keys(data).length === 0) {
        showAlert('Tidak ada data yang dapat disimpan', 'error');
        return;
    }
    
    // Debug logging
    console.log('Saving data:', data);
    console.log('Row ID:', rowId);
    
    // Show loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Create AbortController for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
    
    fetch('<?= base_url('database/update_ajax/') ?>' + rowId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data),
        signal: controller.signal
    })
    .then(response => {
        if (response.status === 401) {
            showAlert('Session expired. Silakan login ulang.', 'error');
            setTimeout(() => {
                window.location.href = '<?= base_url('auth') ?>';
            }, 2000);
            return;
        }
        return response.json();
    })
    .then(result => {
        if (!result) return;
        
        if (result.success) {
            editFields.forEach((field, index) => {
                const valueElement = values[index];
                const fieldName = valueElement.getAttribute('data-field');
                let displayValue = field.value;
                
                if (fieldName === 'status') {
                    const statusMap = {0: 'On Target', 1: 'Already', 2: 'Done'};
                    displayValue = statusMap[field.value] || field.value;
                    valueElement.className = `value status-badge status-${field.value}`;
                }
                
                valueElement.textContent = displayValue;
                valueElement.setAttribute('data-value', field.value);
            });
            
            showAlert('Data berhasil diperbarui', 'success');
            
            // Remove editing class
            card.classList.remove('editing');
            
            editFields.forEach(field => field.style.display = 'none');
            values.forEach(value => value.style.display = 'inline');
            editBtn.style.display = 'inline-flex';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        } else {
            showAlert('Gagal memperbarui data: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.name === 'AbortError') {
            showAlert('Request timeout. Silakan coba lagi.', 'error');
        } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
            showAlert('Koneksi terputus. Silakan periksa koneksi internet Anda.', 'error');
        } else {
            showAlert('Terjadi kesalahan saat memperbarui data: ' + error.message, 'error');
        }
    })
    .finally(() => {
        // Clear timeout
        clearTimeout(timeoutId);
        // Reset button state
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i>';
    });
}

// Desktop edit functions (existing)
function toggleEdit(button) {
    const row = button.closest('tr');
    const editFields = row.querySelectorAll('.edit-field');
    const displayValues = row.querySelectorAll('.display-value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    editFields.forEach(field => field.style.display = 'block');
    displayValues.forEach(value => value.style.display = 'none');
    
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
}

function cancelEdit(button) {
    const row = button.closest('tr');
    const editFields = row.querySelectorAll('.edit-field');
    const displayValues = row.querySelectorAll('.display-value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    editFields.forEach((field, index) => {
        if (field.tagName === 'SELECT') {
            const originalValue = field.closest('td').getAttribute('data-value');
            field.value = originalValue || '';
        } else {
            field.value = field.closest('td').getAttribute('data-value') || '';
        }
    });
    
    editFields.forEach(field => field.style.display = 'none');
    displayValues.forEach(value => value.style.display = 'inline');
    
    editBtn.style.display = 'inline-block';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
}

function saveRow(button) {
    const row = button.closest('tr');
    const rowId = row.getAttribute('data-id');
    const editFields = row.querySelectorAll('.edit-field');
    const displayValues = row.querySelectorAll('.display-value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    const data = {};
    editFields.forEach(field => {
        const fieldName = field.closest('td').getAttribute('data-field');
        data[fieldName] = field.value;
    });
    
    fetch('<?= base_url('database/update_ajax/') ?>' + rowId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (response.status === 401) {
            showAlert('Session expired. Silakan login ulang.', 'error');
            setTimeout(() => {
                window.location.href = '<?= base_url('auth') ?>';
            }, 2000);
            return;
        }
        return response.json();
    })
    .then(result => {
        if (!result) return;
        
        if (result.success) {
            editFields.forEach((field, index) => {
                const fieldName = field.closest('td').getAttribute('data-field');
                let displayValue = field.value;
                
                if (fieldName === 'tgl_lahir' && field.value) {
                    const date = new Date(field.value);
                    displayValue = date.toLocaleDateString('id-ID');
                } else if (fieldName === 'status') {
                    const statusMap = {0: 'On Target', 1: 'Already', 2: 'Done'};
                    displayValue = statusMap[field.value] || field.value;
                } else if (fieldName === 'gender') {
                    const genderMap = {'L': 'Laki-laki', 'P': 'Perempuan', '': ''};
                    displayValue = genderMap[field.value] || field.value;
                }
                
                displayValues[index].textContent = displayValue;
                field.setAttribute('data-value', field.value);
            });
            
            showAlert('Data berhasil diperbarui', 'success');
            
            editFields.forEach(field => field.style.display = 'none');
            displayValues.forEach(value => value.style.display = 'inline');
            editBtn.style.display = 'inline-block';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        } else {
            showAlert('Gagal memperbarui data: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat memperbarui data', 'error');
    });
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 90vw;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Initialize functionality when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize pagination enhancement
    enhancePagination();
    
    // Initialize search form enhancement
    enhanceSearchForms();
    
    // Debug pagination data
    debugPaginationData();
    
    // Any mobile-specific initialization can go here
});

// Debug pagination data
function debugPaginationData() {
    console.log('=== Pagination Debug Info ===');
    console.log('Total rows:', <?= isset($total_rows) ? $total_rows : 'null' ?>);
    console.log('Per page:', <?= isset($per_page) ? $per_page : 'null' ?>);
    console.log('Current page:', <?= isset($current_page) ? $current_page : 'null' ?>);
    console.log('Offset:', <?= isset($offset) ? $offset : 'null' ?>);
    console.log('Pagination HTML:', document.querySelector('.pagination-container')?.innerHTML);
    console.log('=============================');
}

// Enhanced search form functionality
function enhanceSearchForms() {
    // Mobile search form
    const mobileForm = document.getElementById('mobileSearchForm');
    if (mobileForm) {
        mobileForm.addEventListener('submit', function(e) {
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';
            }
            
            // Add loading overlay to container
            const container = document.querySelector('.mobile-data-container, .table-responsive');
            if (container) {
                container.style.opacity = '0.6';
                container.style.pointerEvents = 'none';
            }
        });
    }
    
    // Desktop search form
    const desktopForm = document.querySelector('.desktop-form');
    if (desktopForm) {
        desktopForm.addEventListener('submit', function(e) {
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';
            }
        });
    }
}
</script>



