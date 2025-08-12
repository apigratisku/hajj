    <!-- Content Body -->
    <div class="content-body">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card mobile-card">
                    <div class="card-body p-0">
                        
                        <!-- Mobile Search Form -->
                        <div class="mobile-search-container d-block d-md-none">
                            <div class="search-toggle" onclick="toggleMobileSearch()">
                                <i class="fas fa-search"></i> Cari Data
                            </div>
                            <div class="mobile-search-form" id="mobileSearchForm" style="display: none;">
                                <form method="get" action="<?= base_url('todo/index') ?>" class="mobile-form" id="mobileSearchForm">
                                
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
                                        <a href="<?= base_url('todo/index') ?>" class="btn btn-reset">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Desktop Search Form -->
                    <div class="desktop-search-container d-none d-md-block">
                        <form method="get" action="<?= base_url('todo/index') ?>" class="desktop-form">
                            <div class="row g-2 align-items-center">
                                
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

                        <!-- Mobile Excel-like Table -->
                        <div class="mobile-table-container d-block d-lg-none">
                            
                            <div class="mobile-table-scroll">
                                <table class="mobile-excel-table" id="mobile-table">
                                    <thead>
                                        <tr>
                                            <th class="col-nama">Nama</th>
                                            <th class="col-paspor">Paspor</th>
                                            <th class="col-visa">Visa</th>
                                            <th class="col-tgl">Tgl Lahir</th>
                                            <th class="col-password">Pass</th>
                                            <th class="col-hp">HP</th>
                                            <th class="col-email">Email</th>
                                            <th class="col-gender">L/P</th>
                                            <th class="col-tanggal">Tanggal</th>
                                            <th class="col-jam">Jam</th>
                                            <th class="col-status">Status</th>
                                            <th class="col-flag">Flag</th>
                                            <th class="col-aksi">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="mobile-table-tbody">
                                        <!-- Sample Data Row 1 -->
                                        <?php if (empty($peserta)): ?>
                                            <div class="no-data-mobile">
                                                <i class="fas fa-inbox fa-3x text-muted"></i>
                                                <p>Tidak ada data</p>
                                            </div>
                                        <?php else: ?>
                                        <?php foreach ($peserta as $p): ?>
                                        <tr data-id="<?= $p->id ?>">
                                            <td class="col-nama" data-field="nama" data-value="<?= $p->nama ?>">
                                            <span class="value copyable-text" data-field="nama" data-value="<?= $p->nama ?>" onclick="copyToClipboard('<?= htmlspecialchars($p->nama, ENT_QUOTES) ?>', 'Nama Peserta')" title="Klik untuk copy"><?= $p->nama ?></span>
                                            <input type="text" class="mobile-edit-field" value="<?= $p->nama ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="col-paspor" data-field="nomor_paspor" data-value="<?= $p->nomor_paspor ?>">
                                            <span class="value copyable-text" data-field="nomor_paspor" data-value="<?= $p->nomor_paspor ?>" onclick="copyToClipboard('<?= htmlspecialchars($p->nomor_paspor, ENT_QUOTES) ?>', 'Nomor Paspor')" title="Klik untuk copy"><?= $p->nomor_paspor ?></span>
                                            <input type="text" class="mobile-edit-field" value="<?= $p->nomor_paspor ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="col-visa" data-field="no_visa" data-value="<?= $p->no_visa ?>">
                                            <span class="value copyable-text" data-field="no_visa" data-value="<?= $p->no_visa ?>" onclick="copyToClipboard('<?= htmlspecialchars($p->no_visa ?: '-', ENT_QUOTES) ?>', 'Nomor Visa')" title="Klik untuk copy"><?= $p->no_visa ?: '-' ?></span>
                                            <input type="text" class="mobile-edit-field" value="<?= $p->no_visa ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="col-tgl" data-field="tgl_lahir" data-value="<?= $p->tgl_lahir ?>">
                                            <span class="value copyable-text" data-field="tgl_lahir" data-value="<?= $p->tgl_lahir ?>" onclick="copyToClipboard('<?= htmlspecialchars($p->tgl_lahir ? date('d/m/Y', strtotime($p->tgl_lahir)) : '-', ENT_QUOTES) ?>', 'Tanggal Lahir')" title="Klik untuk copy"><?= $p->tgl_lahir ? date('d/m/Y', strtotime($p->tgl_lahir)) : '-' ?></span>
                                            <input type="date" class="mobile-edit-field" value="<?= $p->tgl_lahir ? date('Y-m-d', strtotime($p->tgl_lahir)) : '' ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="col-password" data-field="password" data-value="<?= $p->password ?>">
                                            <span class="value copyable-text" data-field="password" data-value="<?= $p->password ?>" onclick="copyToClipboard('<?= htmlspecialchars($p->password, ENT_QUOTES) ?>', 'Password')" title="Klik untuk copy"><?= $p->password ?: '-' ?></span>
                                            <input type="password" class="mobile-edit-field" value="<?= $p->password ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="col-hp" data-field="nomor_hp" data-value="<?= $p->nomor_hp ?>">
                                            <span class="value copyable-text" data-field="nomor_hp" data-value="<?= $p->nomor_hp ?>" onclick="copyToClipboard('<?= htmlspecialchars($p->nomor_hp ?: '-', ENT_QUOTES) ?>', 'Nomor HP')" title="Klik untuk copy"><?= $p->nomor_hp ?: '-' ?></span>
                                            <input type="text" class="mobile-edit-field" value="<?= $p->nomor_hp ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="col-email" data-field="email" data-value="<?= $p->email ?>">
                                            <span class="value copyable-text" data-field="email" data-value="<?= $p->email ?>" onclick="copyToClipboard('<?= htmlspecialchars($p->email ?: '-', ENT_QUOTES) ?>', 'Email')" title="Klik untuk copy"><?= $p->email ?: '-' ?></span>
                                            <input type="email" class="mobile-edit-field" value="<?= $p->email ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                            </td>
                                            <td class="col-gender" data-field="gender" data-value="<?= $p->gender ?>">
                                            <span class="value" data-field="gender" data-value="<?= $p->gender ?>"><?= $p->gender ?: '-' ?></span>
                                            <select class="mobile-edit-field" style="display:none;">
                                                <option value="">Pilih Gender</option>
                                                <option value="L" <?= $p->gender == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                <option value="P" <?= $p->gender == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                            </select>
                                            </td>
                                            <td class="col-tanggal" data-field="tanggal" data-value="<?= $p->tanggal ?>">
                                            <span class="value" data-field="tanggal" data-value="<?= $p->tanggal ?>"><?= $p->tanggal ?: '-' ?></span>
                                            <input type="date" class="mobile-edit-field" value="<?= $p->tanggal ?>" style="display:none;">
                                            </td>
                                            <td class="col-jam" data-field="jam" data-value="<?= $p->jam ?>">
                                            <span class="value" data-field="jam" data-value="<?= $p->jam ?>"><?= $p->jam ?: '-' ?></span>
                                            <input type="time" class="mobile-edit-field" value="<?= $p->jam ?>" style="display:none;">
                                            </td>
                                            <td class="col-status" data-field="status" data-value="<?= $p->status ?>">
                                                <span class="value mobile-status-badge mobile-status-<?= $p->status ?>" data-field="status" data-value="<?= $p->status ?>">
                                                <?= $p->status == 0 ? 'On Target' : ($p->status == 1 ? 'Already' : 'Done') ?>
                                            </span>
                                                <select class="mobile-edit-field" style="display:none;">
                                                <option value="0" <?= $p->status == 0 ? 'selected' : '' ?>>On Target</option>
                                                <option value="1" <?= $p->status == 1 ? 'selected' : '' ?>>Already</option>
                                                <option value="2" <?= $p->status == 2 ? 'selected' : '' ?>>Done</option>
                                            </select>
                                            </td>
                                            <td class="col-flag" data-field="flag_doc" data-value="<?= $p->flag_doc ?>">
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
                                            </td>
                                            <td class="col-aksi">
                                                <button class="mobile-table-btn mobile-table-btn-edit btn-edit" onclick="toggleEditMobileTable(this)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="mobile-table-btn mobile-table-btn-save btn-save" style="display:none;" onclick="saveRowMobileTable(this)">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                                <button class="mobile-table-btn mobile-table-btn-cancel btn-cancel" style="display:none;" onclick="cancelEditMobileTable(this)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <?php if($this->session->userdata('role') == 'admin'): ?>
                                                <button class="mobile-table-btn mobile-table-btn-delete btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>   
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Desktop Table (unchanged) -->
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
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->nama, ENT_QUOTES) ?>', 'Nama Peserta')" title="Klik untuk copy"><?= $p->nama ?></span>
                                        <input type="text" class="form-control edit-field" value="<?= $p->nama ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </td>
                                        <td class="no-paspor text-center" data-field="nomor_paspor" data-value="<?= $p->nomor_paspor ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->nomor_paspor, ENT_QUOTES) ?>', 'Nomor Paspor')" title="Klik untuk copy"><?= $p->nomor_paspor ?></span>
                                        <input type="text" class="form-control edit-field" value="<?= $p->nomor_paspor ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </td>
                                        <td class="no-visa text-center" data-field="no_visa" data-value="<?= $p->no_visa ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->no_visa ?: '-', ENT_QUOTES) ?>', 'Nomor Visa')" title="Klik untuk copy"><?= $p->no_visa ?></span>
                                                <input type="text" class="form-control edit-field" value="<?= $p->no_visa ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </td>
                                        <td class="tgl-lahir text-center" data-field="tgl_lahir" data-value="<?= $p->tgl_lahir ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->tgl_lahir ? date('d/m/Y', strtotime($p->tgl_lahir)) : '-', ENT_QUOTES) ?>', 'Tanggal Lahir')" title="Klik untuk copy"><?= $p->tgl_lahir ? date('d/m/Y', strtotime($p->tgl_lahir)) : '-' ?></span>
                                        <input type="date" class="form-control edit-field" value="<?= $p->tgl_lahir ? date('Y-m-d', strtotime($p->tgl_lahir)) : '' ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </td>
                                        <td class="password text-center" data-field="password" data-value="<?= $p->password ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->password, ENT_QUOTES) ?>', 'Password')" title="Klik untuk copy"><?= $p->password ?></span>
                                        <input type="password" class="form-control edit-field" value="<?= $p->password ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </td>
                                        <td class="no-hp text-center" data-field="nomor_hp" data-value="<?= $p->nomor_hp ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->nomor_hp ?: '-', ENT_QUOTES) ?>', 'Nomor HP')" title="Klik untuk copy"><?= $p->nomor_hp ?></span>
                                        <input type="text" class="form-control edit-field" value="<?= $p->nomor_hp ?>" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                        </td>
                                        <td class="email text-center" data-field="email" data-value="<?= $p->email ?>"> 
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->email ?: '-', ENT_QUOTES) ?>', 'Email')" title="Klik untuk copy"><?= $p->email ?></span>
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
                                        <td class="status text-center" data-field="status" data-value="<?= $p->status ?>" style="white-space: nowrap;width: auto;">
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
                                        <td class="text-center aksi"  style="white-space: nowrap;width: auto;">
                                            <button class="btn btn-sm btn-brown btn-edit" data-bs-toggle="tooltip" title="Edit" onclick="toggleEdit(this)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-success btn-save" data-bs-toggle="tooltip" title="Simpan" style="display:none;" onclick="saveRow(this)">
                                                <i class="fas fa-save"></i>
                                            </button>
                                            <button class="btn btn-sm btn-secondary btn-cancel" data-bs-toggle="tooltip" title="Batal" style="display:none;" onclick="cancelEdit(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php if($this->session->userdata('role') == 'admin'): ?>
                                            <button class="btn btn-sm btn-danger btn-action btn-delete" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
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

/* Mobile Excel-like Table Styles */
.mobile-table-container {
    padding: 1rem;
    background: white;
}

.mobile-excel-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    line-height: 1.3;
    box-shadow: var(--shadow);
    border-radius: var(--border-radius);
    overflow: hidden;
    background: white;
}

.mobile-excel-table thead {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
}

.mobile-excel-table th {
    color: white;
    font-weight: 600;
    padding: 8px 4px;
    text-align: center;
    border: 1px solid #dee2e6;
    font-size: 10px;
    min-width: 60px;
    max-width: 80px;
    word-wrap: break-word;
    vertical-align: middle;
}

.mobile-excel-table td {
    padding: 6px 4px;
    border: 1px solid #dee2e6;
    text-align: center;
    vertical-align: middle;
    font-size: 10px;
    min-width: 60px;
    max-width: 80px;
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    position: relative;
}

.mobile-excel-table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.mobile-excel-table tbody tr:hover {
    background-color: rgba(139, 69, 19, 0.05);
}

.mobile-excel-table tbody tr.editing {
    background-color: rgba(139, 69, 19, 0.1);
    border: 2px solid var(--primary-color);
}

/* Column specific widths for mobile */
.mobile-excel-table .col-nama {
    min-width: 80px;
    max-width: 90px;
}

.mobile-excel-table .col-paspor {
    min-width: 70px;
    max-width: 80px;
}

.mobile-excel-table .col-visa {
    min-width: 60px;
    max-width: 70px;
}

.mobile-excel-table .col-tgl {
    min-width: 65px;
    max-width: 75px;
}

.mobile-excel-table .col-password {
    min-width: 60px;
    max-width: 70px;
}

.mobile-excel-table .col-hp {
    min-width: 70px;
    max-width: 80px;
}

.mobile-excel-table .col-email {
    min-width: 80px;
    max-width: 90px;
}

.mobile-excel-table .col-gender {
    min-width: 40px;
    max-width: 50px;
}

.mobile-excel-table .col-tanggal {
    min-width: 60px;
    max-width: 70px;
}

.mobile-excel-table .col-jam {
    min-width: 50px;
    max-width: 60px;
}

.mobile-excel-table .col-status {
    min-width: 60px;
    max-width: 70px;
}

.mobile-excel-table .col-flag {
    min-width: 60px;
    max-width: 70px;
}

.mobile-excel-table .col-aksi {
    min-width: 80px;
    max-width: 90px;
}

/* Mobile Table Scroll */
.mobile-table-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: var(--primary-color) #f1f1f1;
}

.mobile-table-scroll::-webkit-scrollbar {
    height: 6px;
}

.mobile-table-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.mobile-table-scroll::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

.mobile-table-scroll::-webkit-scrollbar-thumb:hover {
    background: var(--primary-light);
}

/* Mobile Edit Fields */
.mobile-edit-field {
    width: 100%;
    padding: 2px 4px;
    border: 1px solid var(--primary-color);
    border-radius: 3px;
    font-size: 10px;
    text-align: center;
    background: white;
    min-height: 24px;
}

.mobile-edit-field:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 1px rgba(139, 69, 19, 0.3);
}

.mobile-table-edit-field {
    width: 100%;
    padding: 2px 4px;
    border: 1px solid var(--primary-color);
    border-radius: 3px;
    font-size: 10px;
    text-align: center;
    background: white;
    min-height: 24px;
}

.mobile-table-edit-field:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 1px rgba(139, 69, 19, 0.3);
}

/* Mobile Table Action Buttons */
.mobile-table-btn {
    padding: 2px 4px;
    margin: 1px;
    border: none;
    border-radius: 3px;
    font-size: 10px;
    min-width: 20px;
    height: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.mobile-table-btn i {
    font-size: 8px;
}

.mobile-table-btn-edit {
    background: var(--info-color);
    color: white;
}

.mobile-table-btn-save {
    background: var(--success-color);
    color: white;
}

.mobile-table-btn-cancel {
    background: var(--warning-color);
    color: white;
}

.mobile-table-btn-delete {
    background: var(--danger-color);
    color: white;
}

.mobile-table-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Status Badges for Mobile Table */
.mobile-status-badge {
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 9px;
    font-weight: 600;
    display: inline-block;
    min-width: 50px;
}

.mobile-status-0 {
    background: #e3f2fd;
    color: #1976d2;
}

.mobile-status-1 {
    background: #fff3e0;
    color: #f57c00;
}

.mobile-status-2 {
    background: #e8f5e8;
    color: #388e3c;
}

/* Copyable Text for Mobile Table */
.mobile-copyable-text {
    cursor: pointer;
    transition: var(--transition);
    border-radius: 2px;
    padding: 1px 2px;
}

.mobile-copyable-text:hover {
    background-color: rgba(139, 69, 19, 0.1);
    transform: scale(1.05);
}

.mobile-copyable-text:active {
    background-color: rgba(139, 69, 19, 0.2);
    transform: scale(0.98);
}

/* Copyable Text Styles */
.copyable-text {
    cursor: pointer;
    transition: var(--transition);
    border-radius: 2px;
    padding: 1px 2px;
    position: relative;
}

.copyable-text:hover {
    background-color: rgba(139, 69, 19, 0.1);
    transform: scale(1.02);
}

.copyable-text:active {
    background-color: rgba(139, 69, 19, 0.2);
    transform: scale(0.98);
}

.copyable-text::after {
    content: '📋';
    position: absolute;
    top: -8px;
    right: -8px;
    font-size: 10px;
    opacity: 0;
    transition: var(--transition);
}

.copyable-text:hover::after {
    opacity: 1;
}

/* Mobile Copyable Text Adjustments */
@media (max-width: 768px) {
    .copyable-text {
        padding: 2px 4px;
        min-height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .copyable-text:active {
        background-color: rgba(139, 69, 19, 0.3);
        transform: scale(0.95);
    }
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

/* Desktop Styles (unchanged) */
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

/* Enhanced Pagination Styles */
.pagination-container {
    padding: 1.5rem;
    background: white;
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
}

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

/* Mobile Pagination Enhancements */
@media (max-width: 768px) {
    .pagination a,
    .pagination span {
        min-width: 40px;
        height: 40px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 576px) {
    .pagination a,
    .pagination span {
        min-width: 36px;
        height: 36px;
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
    }
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

.alert-warning {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    color: #856404;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    color: #0c5460;
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

/* Mobile Media Queries */
@media (max-width: 768px) {
    .mobile-excel-table {
        font-size: 10px;
    }
    
    .mobile-excel-table th,
    .mobile-excel-table td {
        padding: 4px 2px;
        font-size: 9px;
        min-width: 50px;
        max-width: 70px;
    }
    
    .mobile-table-edit-field {
        font-size: 9px;
        min-height: 20px;
    }
    
    .mobile-table-btn {
        min-width: 18px;
        height: 18px;
    }
    
    .mobile-table-btn i {
        font-size: 7px;
    }
    
    .mobile-status-badge {
        font-size: 8px;
        padding: 1px 4px;
        min-width: 40px;
    }
}

@media (max-width: 576px) {
    .mobile-table-container {
        padding: 0.5rem;
    }
    
    .mobile-excel-table th,
    .mobile-excel-table td {
        padding: 3px 1px;
        font-size: 8px;
        min-width: 40px;
        max-width: 60px;
    }
    
    .mobile-table-edit-field {
        font-size: 8px;
        min-height: 18px;
    }
    
    .mobile-table-btn {
        min-width: 16px;
        height: 16px;
        margin: 0.5px;
    }
    
    .mobile-table-btn i {
        font-size: 6px;
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

.mobile-excel-table {
    animation: slideIn 0.3s ease-out;
}

.mobile-excel-table tbody tr {
    animation: slideIn 0.2s ease-out;
}
</style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
// Copy to clipboard function (unchanged)
function copyToClipboard(text, fieldName) {
    // Handle empty or dash values
    if (!text || text === '-' || text === '') {
        showAlert('Tidak ada teks untuk di-copy', 'warning');
        return;
    }
    
    // Add loading state
    const clickedElement = event.target;
    const removeLoading = addCopyLoadingState(clickedElement);
    
    // Try to use the modern Clipboard API first
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            removeLoading();
            showCopySuccess(fieldName, text);
        }).catch(err => {
            console.error('Clipboard API failed:', err);
            removeLoading();
            fallbackCopyTextToClipboard(text, fieldName);
        });
    } else {
        // Fallback for older browsers or non-secure contexts
        removeLoading();
        fallbackCopyTextToClipboard(text, fieldName);
    }
}

// Fallback copy function for older browsers (unchanged)
function fallbackCopyTextToClipboard(text, fieldName) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    
    // Avoid scrolling to bottom
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(fieldName, text);
        } else {
            showAlert('Gagal copy teks ke clipboard', 'error');
        }
    } catch (err) {
        console.error('Fallback copy failed:', err);
        showAlert('Gagal copy teks ke clipboard', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Show copy success message (unchanged)
function showCopySuccess(fieldName, text) {
    // Create a temporary success indicator on the clicked element
    const clickedElement = event.target;
    const originalText = clickedElement.textContent;
    
    // Show visual feedback
    clickedElement.style.backgroundColor = 'rgba(40, 167, 69, 0.2)';
    clickedElement.style.color = '#155724';
    clickedElement.style.fontWeight = 'bold';
    
    // Show success message
    showAlert(`${fieldName} berhasil di-copy: "${text}"`, 'success');
    
    // Reset visual feedback after 1 second
    setTimeout(() => {
        clickedElement.style.backgroundColor = '';
        clickedElement.style.color = '';
        clickedElement.style.fontWeight = '';
    }, 1000);
}

// Add loading state to copy function (unchanged)
function addCopyLoadingState(element) {
    const originalText = element.textContent;
    element.textContent = '📋 Copying...';
    element.style.opacity = '0.7';
    element.style.pointerEvents = 'none';
    
    return () => {
        element.textContent = originalText;
        element.style.opacity = '';
        element.style.pointerEvents = '';
    };
}

// Mobile search toggle (unchanged)
function toggleMobileSearch() {
    const form = document.getElementById('mobileSearchForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.style.animation = 'slideIn 0.3s ease-out';
    } else {
        form.style.display = 'none';
    }
}

// Reset search function
function resetSearch() {
    // Reset form fields
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const selects = form.querySelectorAll('select');
        selects.forEach(select => select.value = '');
    });
    
    showAlert('Filter pencarian telah direset', 'info');
}

// Mobile Table Edit Functions
function toggleEditMobileTable(button) {
    const row = button.closest('tr');
    const editFields = row.querySelectorAll('.mobile-edit-field');
    const displayValues = row.querySelectorAll('.value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    // Add editing class for visual feedback
    row.classList.add('editing');
    
    editFields.forEach(field => field.style.display = 'block');
    displayValues.forEach(value => value.style.display = 'none');
    
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
}

function cancelEditMobileTable(button) {
    const row = button.closest('tr');
    const editFields = row.querySelectorAll('.mobile-edit-field');
    const displayValues = row.querySelectorAll('.value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    // Reset values to original
    editFields.forEach((field, index) => {
        const td = field.closest('td');
        const originalValue = td.getAttribute('data-value');
        
        if (field.tagName === 'SELECT') {
            field.value = originalValue || '';
        } else {
            field.value = originalValue || '';
        }
    });
    
    // Remove editing class
    row.classList.remove('editing');
    
    editFields.forEach(field => field.style.display = 'none');
    displayValues.forEach(value => value.style.display = 'inline');
    
    editBtn.style.display = 'inline-block';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
}

function saveRowMobileTable(button) {
    const row = button.closest('tr');
    const rowId = row.getAttribute('data-id');
    const editFields = row.querySelectorAll('.mobile-edit-field');
    const displayValues = row.querySelectorAll('.value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    const data = {};
    let hasData = false;
    
    editFields.forEach((field, index) => {
        // Get the corresponding td to find the field name
        const td = field.closest('td');
        const fieldName = td.getAttribute('data-field');
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
    
    fetch('<?= base_url('todo/update_ajax/') ?>' + rowId, {
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
                const valueElement = displayValues[index];
                const fieldName = valueElement.getAttribute('data-field');
                let displayValue = field.value;
                
                if (fieldName === 'status') {
                    const statusMap = {0: 'On Target', 1: 'Already', 2: 'Done'};
                    displayValue = statusMap[field.value] || field.value;
                    valueElement.className = `value mobile-status-badge mobile-status-${field.value}`;
                }
                
                valueElement.textContent = displayValue;
                valueElement.setAttribute('data-value', field.value);
            });
            
            showAlert('Data berhasil diperbarui', 'success');
            
            // Remove editing class
            row.classList.remove('editing');
            
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
    
    fetch('<?= base_url('todo/update_ajax/') ?>' + rowId, {
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
            // Auto refresh setelah 1 detik
            setTimeout(() => {
                location.reload();
            }, 1000);
            
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
    
    // Determine alert class based on type
    let alertClass = 'alert-danger';
    if (type === 'success') {
        alertClass = 'alert-success';
    } else if (type === 'warning') {
        alertClass = 'alert-warning';
    } else if (type === 'info') {
        alertClass = 'alert-info';
    }
    
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
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
</script><script>
// Copy to clipboard function
function copyToClipboard(text, fieldName) {
    // Handle empty or dash values
    if (!text || text === '-' || text === '') {
        showAlert('Tidak ada teks untuk di-copy', 'warning');
        return;
    }
    
    // Add loading state
    const clickedElement = event.target;
    const removeLoading = addCopyLoadingState(clickedElement);
    
    // Try to use the modern Clipboard API first
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            removeLoading();
            showCopySuccess(fieldName, text);
        }).catch(err => {
            console.error('Clipboard API failed:', err);
            removeLoading();
            fallbackCopyTextToClipboard(text, fieldName);
        });
    } else {
        // Fallback for older browsers or non-secure contexts
        removeLoading();
        fallbackCopyTextToClipboard(text, fieldName);
    }
}

// Fallback copy function for older browsers
function fallbackCopyTextToClipboard(text, fieldName) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    
    // Avoid scrolling to bottom
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(fieldName, text);
        } else {
            showAlert('Gagal copy teks ke clipboard', 'error');
        }
    } catch (err) {
        console.error('Fallback copy failed:', err);
        showAlert('Gagal copy teks ke clipboard', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Show copy success message
function showCopySuccess(fieldName, text) {
    // Create a temporary success indicator on the clicked element
    const clickedElement = event.target;
    const originalText = clickedElement.textContent;
    
    // Show visual feedback
    clickedElement.style.backgroundColor = 'rgba(40, 167, 69, 0.2)';
    clickedElement.style.color = '#155724';
    clickedElement.style.fontWeight = 'bold';
    
    // Show success message
    showAlert(`${fieldName} berhasil di-copy: "${text}"`, 'success');
    
    // Reset visual feedback after 1 second
    setTimeout(() => {
        clickedElement.style.backgroundColor = '';
        clickedElement.style.color = '';
        clickedElement.style.fontWeight = '';
    }, 1000);
}

// Add loading state to copy function
function addCopyLoadingState(element) {
    const originalText = element.textContent;
    element.textContent = '📋 Copying...';
    element.style.opacity = '0.7';
    element.style.pointerEvents = 'none';
    
    return () => {
        element.textContent = originalText;
        element.style.opacity = '';
        element.style.pointerEvents = '';
    };
}

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
    
    fetch('<?= base_url('todo/update_ajax/') ?>' + rowId, {
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
            // Auto refresh setelah 1 detik
            setTimeout(() => {
                location.reload();
            }, 1000);
            
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
    
    // Determine alert class based on type
    let alertClass = 'alert-danger';
    if (type === 'success') {
        alertClass = 'alert-success';
    } else if (type === 'warning') {
        alertClass = 'alert-warning';
    } else if (type === 'info') {
        alertClass = 'alert-info';
    }
    
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
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