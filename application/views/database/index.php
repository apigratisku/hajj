    <!-- Content Body -->
    <div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 me-3">Data Peserta</h5>
                        <?php if (isset($update_stats) && isset($_GET['tanggal_pengerjaan'])): ?>
                        <div class="update-stats-info">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-calendar-check"></i>
                                Update <?= date('d-m-Y', strtotime($_GET['tanggal_pengerjaan'])) ?>: 
                                <strong><?= $update_stats ?> data</strong>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                   
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('database/tambah') ?>" class="btn btn-sm btn-tambah">
                            <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Tambah</span>
                        </a>
                        <a href="<?= base_url('database/import') ?>" class="btn btn-sm btn-import">
                            <i class="fas fa-file-import"></i> <span class="d-none d-sm-inline">Import</span>
                        </a>
                        <button type="button" class="btn btn-sm btn-export" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-file-export"></i> <span class="d-none d-sm-inline">Export</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-attachment" onclick="downloadBarcodeAttachments()">
                            <i class="fas fa-download"></i> <span class="d-none d-sm-inline">Download Attachment</span>
                        </button>
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
                                                <?= date('d-m-Y H:i:s', strtotime($tanggaljam->tanggaljam)) ?>
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
                                <div class="form-group">
                                    <select name="status" class="form-select mobile-input">
                                        <option value="">Status Data</option>
                                            <option value="0">On Target</option>
                                            <option value="1">Already</option>
                                            <option value="2">Done</option>
                                       
                                    </select>
                                </div>
                                <?php if($this->session->userdata('role') == 'admin'): ?>
                                <div class="form-group">
                                    <select name="tanggal_pengerjaan" class="form-select mobile-input">
                                        <option value="">Tanggal Pengerjaan</option>
                                        <?php if (!empty($tanggal_pengerjaan_list)): foreach ($tanggal_pengerjaan_list as $tanggal_pengerjaan): ?>
                                            <?php 
                                                $display_date = date('d-m-Y', strtotime($tanggal_pengerjaan->tanggal_pengerjaan));
                                                $value_date = date('d-m-Y', strtotime($tanggal_pengerjaan->tanggal_pengerjaan));
                                            ?>
                                            <option value="<?= htmlspecialchars($value_date) ?>" <?= (isset($_GET['tanggal_pengerjaan']) && $_GET['tanggal_pengerjaan'] === $value_date) ? 'selected' : '' ?>>
                                                <?= $display_date ?> (<?= $tanggal_pengerjaan->jumlah_update ?> data)
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select name="status_jadwal" class="form-select mobile-input">
                                        <option value="">Status Jadwal</option>
                                        <option value="2">Sudah dijadwalkan</option>
                                        <option value="1">Belum dijadwalkan</option>
                                    </select>
                                </div>
                                <?php endif; ?>
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
                                <div class="col-md-1">
                                    <input type="text" name="nama" value="<?= isset($_GET['nama']) ? htmlspecialchars($_GET['nama']) : '' ?>" class="form-control form-control-sm" placeholder="Nama Peserta">
                                </div>
                                <div class="col-md-1">
                                    <input type="text" name="nomor_paspor" value="<?= isset($_GET['nomor_paspor']) ? htmlspecialchars($_GET['nomor_paspor']) : '' ?>" class="form-control form-control-sm" placeholder="No Paspor" >
                                </div>
                                <div class="col-md-1">
                                    <input type="text" name="no_visa" value="<?= isset($_GET['no_visa']) ? htmlspecialchars($_GET['no_visa']) : '' ?>" class="form-control form-control-sm" placeholder="No Visa">
                                </div>
                                <div class="col-md-1">
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
                                <div class="col-md-1">
                                    <select name="tanggaljam" class="form-select form-control-sm">
                                        <option value="">Waktu</option>
                                        <?php if (!empty($tanggaljam_list)): foreach ($tanggaljam_list as $tanggaljam): ?>
                                            <option value="<?= htmlspecialchars($tanggaljam->tanggaljam) ?>" <?= (isset($_GET['tanggaljam']) && $_GET['tanggaljam'] === $tanggaljam->tanggaljam) ? 'selected' : '' ?>>
                                            <?= date('d-m-Y H:i:s', strtotime($tanggaljam->tanggaljam)) ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <select name="gender" class="form-select form-control-sm">
                                        <option value="">Gender</option>
                                        <option value="L" <?= (isset($_GET['gender']) && $_GET['gender'] === 'L') ? 'selected' : '' ?>>Laki-laki</option>
                                        <option value="P" <?= (isset($_GET['gender']) && $_GET['gender'] === 'P') ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <select name="status" class="form-select form-control-sm">
                                        <option value="">Status Data</option>
                                        <option value="0" <?= (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : '' ?>>On Target</option>
                                        <option value="1" <?= (isset($_GET['status']) && $_GET['status'] === '1') ? 'selected' : '' ?>>Already</option>
                                        <option value="2" <?= (isset($_GET['status']) && $_GET['status'] === '2') ? 'selected' : '' ?>>Done</option>
                                    </select>
                                </div>
                                <?php if($this->session->userdata('role') == 'admin'): ?>
                                <div class="col-md-1">
                                    <select name="tanggal_pengerjaan" class="form-select form-control-sm">
                                        <option value="">Tanggal Pengerjaan</option>
                                        <?php if (!empty($tanggal_pengerjaan_list)): foreach ($tanggal_pengerjaan_list as $tanggal_pengerjaan): ?>
                                            <?php 
                                                $display_date = date('d-m-Y', strtotime($tanggal_pengerjaan->tanggal_pengerjaan));
                                                $value_date = date('d-m-Y', strtotime($tanggal_pengerjaan->tanggal_pengerjaan));
                                            ?>
                                            <option value="<?= htmlspecialchars($value_date) ?>" <?= (isset($_GET['tanggal_pengerjaan']) && $_GET['tanggal_pengerjaan'] === $value_date) ? 'selected' : '' ?>>
                                                <?= $display_date ?> (<?= $tanggal_pengerjaan->jumlah_update ?> data)
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-1">
                                    <select name="status_jadwal" class="form-select form-control-sm">
                                        <option value="">Status Jadwal</option>
                                        <option value="2" <?= (isset($_GET['status_jadwal']) && $_GET['status_jadwal'] === '2') ? 'selected' : '' ?>>Sudah dijadwalkan</option>
                                        <option value="1" <?= (isset($_GET['status_jadwal']) && $_GET['status_jadwal'] === '1') ? 'selected' : '' ?>>Belum dijadwalkan</option>
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

                    <!-- Update Statistics Info -->
                    <?php if (isset($update_stats) && isset($update_stats_detail)): ?>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-chart-bar"></i> Statistik Update Data
                                </h6>
                                <p class="mb-2">
                                    <strong>Tanggal Pengerjaan:</strong> 
                                    <?= date('d-m-Y', strtotime($_GET['tanggal_pengerjaan'])) ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Total Data Diupdate:</strong> 
                                    <span class="badge bg-primary"><?= $update_stats ?> data</span>
                                </p>
                                <?php if (!empty($update_stats_detail)): ?>
                                <div class="mt-2">
                                    <strong>Breakdown Status:</strong>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        <?php foreach ($update_stats_detail as $stat): ?>
                                            <span class="badge bg-secondary">
                                                <?= $stat->status == 0 ? 'On Target' : ($stat->status == 1 ? 'Already' : 'Done') ?>: 
                                                <?= $stat->count ?> data
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

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
                                            <th class="col-barcode">Barcode</th>
                                            <th class="col-gender">L/P</th>
                                            <th class="col-tanggal">Tanggal</th>
                                            <th class="col-jam">Jam</th>
                                            <th class="col-status">Status</th>
                                            <th class="col-flag">Flag</th>
                                            <?php if($this->session->userdata('role') == 'admin'): ?>
                                            <th class="col-history">Update Terakhir</th>
                                            <?php endif; ?>
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
                                            <td class="col-nama" data-field="nama" data-value="<?= $p->nama ?>" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;">
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
                                            <td class="col-barcode" data-field="barcode" data-value="<?= $p->barcode ?>">
                                            <span class="value">
                                            <?php if($p->barcode): ?>
                                            <a href="<?= base_url('upload/view_barcode/' . $p->barcode) ?>" target="_blank" title="Lihat gambar barcode"><i class="fas fa-check-circle" style="color: green;"></i></a>
                                            <?php else: ?>
                                            <i class="fas fa-times-circle" style="color: red;" title="Tidak ada barcode"></i>
                                            <?php endif; ?>
                                            </span>
                                            <div class="mobile-edit-field" style="display:none;">
                                                <div class="mobile-barcode-edit-container">
                                                    <input type="text" class="mobile-table-edit-field" value="<?= $p->barcode ?>" placeholder="Barcode atau upload">
                                                    <button type="button" class="mobile-table-btn mobile-table-btn-upload barcode-upload-btn" title="Upload Gambar Barcode">
                                                        <i class="fas fa-camera"></i>
                                                    </button>
                                                </div>
                                                <input type="file" class="barcode-file-input" accept="image/*" style="display: none;">
                                                <div class="mobile-barcode-preview" style="display: none;">
                                                    <img class="barcode-preview-img" src="" alt="Preview" style="max-width: 60px; max-height: 45px;">
                                                </div>
                                                <button type="button" class="mobile-table-btn mobile-table-btn-danger barcode-remove-btn" style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                            </div>
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
                                            <span class="value" data-field="tanggal" data-value="<?= $p->tanggal ?>"><?= $p->tanggal ? date('d-m-Y', strtotime($p->tanggal)) : '-' ?></span>
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
                                            <select class="mobile-edit-field flag-doc-select" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                                <option value="">Pilih Flag Dokumen</option>
                                                <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                                    <option value="<?= htmlspecialchars($flag->flag_doc) ?>"
                                                        <?= (!empty($p->flag_doc) && $p->flag_doc === $flag->flag_doc) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($flag->flag_doc) ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                            <input type="hidden" name="redirect_back" value="<?= current_url() ?: site_url(uri_string()) ?>">
                                            </td>
                                            <?php if($this->session->userdata('role') == 'admin'): ?>
                                            <td class="col-history">
                                                <?php 
                                                $user = null;
                                                if ($p->history_update) {
                                                    $user = $this->user_model->get_user_by_id($p->history_update);
                                                }
                                                ?>
                                                <?= ($user && isset($user->nama_lengkap))
    ? '<a title="Updated at '.html_escape(!empty($p->updated_at)?date('d-m-Y H:i', strtotime($p->updated_at)):'-').'" onclick="return false;">'.html_escape($user->nama_lengkap).'</a>'
    : '-' ?>
                                            </td>
                                            <?php endif; ?>
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
                                                    <button type="button" class="mobile-table-btn mobile-table-btn-delete btn-delete" onclick="deleteData(<?= $p->id ?>)">
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
                                        <th class="text-center">Barcode</th>
                                        <th class="text-center">Gender</th>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Jam</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Flag Dokumen</th>
                                        <?php if($this->session->userdata('role') == 'admin'): ?>
                                        <th class="text-center">Update Terakhir</th>
                                        <?php endif; ?>
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
                                        <td class="barcode text-center" data-field="barcode" data-value="<?= $p->barcode ?>">
                                        <span class="display-value">
                                        <?php if($p->barcode): ?>
                                        <a href="<?= base_url('upload/view_barcode/' . $p->barcode) ?>" target="_blank" title="Lihat gambar barcode"><i class="fas fa-check-circle" style="color: green;"></i></a>
                                        <?php else: ?>
                                        <i class="fas fa-times-circle" style="color: red;" title="Tidak ada barcode"></i>
                                        <?php endif; ?>
                                        </span>
                                        
                                        <div class="edit-field" style="display:none;">
                                            <div class="barcode-edit-container">
                                                <input type="text" class="form-control" value="<?= $p->barcode ?>" placeholder="Masukkan barcode atau upload gambar">
                                                <button type="button" class="btn btn-outline-primary btn-sm ms-1 barcode-upload-btn" title="Upload Gambar Barcode">
                                                    <i class="fas fa-camera"></i>
                                                </button>
                                            </div>
                                            <input type="file" class="barcode-file-input" accept="image/*" style="display: none;">
                                            <div class="barcode-preview mt-2" style="display: none;">
                                                <img class="barcode-preview-img img-thumbnail" src="" alt="Preview" style="max-width: 100px; max-height: 75px;">
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger mt-1 barcode-remove-btn" style="display: none;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                        </div>
                                        </td>
                                        <td class="gender text-center" data-field="gender" data-value="<?= $p->gender ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->gender ?: '-', ENT_QUOTES) ?>', 'Gender')" title="Klik untuk copy"><?= $p->gender ?: '-' ?></span>
                                                <select class="form-select edit-field" style="display:none;">
                                                    <option value="">Pilih Gender</option>
                                                    <option value="" <?= $p->gender == '' ? 'selected' : '' ?>></option>
                                                    <option value="L" <?= $p->gender == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                    <option value="P" <?= $p->gender == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                </select>
                                        </td>
                                        <td class="tanggal text-center" data-field="tanggal" data-value="<?= $p->tanggal ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->tanggal ?: '-', ENT_QUOTES) ?>', 'Tanggal')" title="Klik untuk copy"><?= $p->tanggal ? date('d-m-Y', strtotime($p->tanggal)) : '-' ?></span>
                                        <input type="date" class="form-control edit-field" value="<?= $p->tanggal ?>" style="display:none;">
                                        </td>
                                        <td class="jam text-center" data-field="jam" data-value="<?= $p->jam ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->jam ?: '-', ENT_QUOTES) ?>', 'Jam')" title="Klik untuk copy"><?= $p->jam ?: '-' ?></span>
                                        <input type="time" class="form-control edit-field" value="<?= $p->jam ?>" style="display:none;">
                                        </td>
                                        <td class="status text-center" data-field="status" data-value="<?= $p->status ?>" style="white-space: nowrap;width: auto;">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->status == 0 ? 'On Target' : ($p->status == 1 ? 'Already' : 'Done') ?: '-', ENT_QUOTES) ?>', 'Status')" title="Klik untuk copy"><?= $p->status == 0 ? 'On Target' : ($p->status == 1 ? 'Already' : 'Done') ?></span>
                                                <select class="form-select edit-field" style="display:none;">
                                                    <option value="0" <?= $p->status == 0 ? 'selected' : '' ?>>On Target</option>
                                                    <option value="1" <?= $p->status == 1 ? 'selected' : '' ?>>Already</option>
                                                    <option value="2" <?= $p->status == 2 ? 'selected' : '' ?>>Done</option>
                                                </select>
                                        </td>
                                        <td class="flag-doc text-center" data-field="flag_doc" data-value="<?= $p->flag_doc ?>">
                                        <span class="display-value copyable-text" onclick="copyToClipboard('<?= htmlspecialchars($p->flag_doc ?: '-', ENT_QUOTES) ?>', 'Flag Dokumen')" title="Klik untuk copy"><?= $p->flag_doc ?: '-' ?></span>
                                                <select class="form-select edit-field flag-doc-select" style="display:none;" <?php if($this->session->userdata('role') == 'operator'): ?> readonly disabled <?php endif; ?>>
                                                <option value="">Pilih Flag Dokumen</option>
                                                <?php if (!empty($flag_doc_list)): foreach ($flag_doc_list as $flag): ?>
                                                    <option value="<?= htmlspecialchars($flag->flag_doc) ?>" <?= (!empty($p->flag_doc) && $p->flag_doc === $flag->flag_doc) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($flag->flag_doc) ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                            <input type="hidden" name="redirect_back" value="<?= current_url() ?: site_url(uri_string()) ?>">
                                        </td>
                                        <?php if($this->session->userdata('role') == 'admin'): ?>
                                        <td class="col-history text-center">
                                                <?php 
                                                $user = null;
                                                if ($p->history_update) {
                                                    $user = $this->user_model->get_user_by_id($p->history_update);
                                                }
                                                ?>
                                                <?= ($user && isset($user->nama_lengkap))
    ? '<a title="Updated at '.html_escape(!empty($p->updated_at)?date('d-m-Y H:i', strtotime($p->updated_at)):'-').'" onclick="return false;">'.html_escape($user->nama_lengkap).'</a>'
    : '-' ?>
                                            </td>
                                        <?php endif; ?>
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
                                                <button type="button" class="btn btn-sm btn-danger btn-action" data-bs-toggle="tooltip" title="Delete" onclick="deleteData(<?= $p->id ?>)">
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
    
    <!-- Mobile Floating Action Button 
    <div class="d-block d-lg-none">
        <a href="<?= base_url('database/tambah') ?>" class="mobile-fab" title="Tambah Data Baru">
            <i class="fas fa-plus"></i>
        </a>
    </div>-->
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

/* Button Styles for Header Actions */
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

.btn-import {
    background-color: var(--info-color) !important;
    border-color: var(--info-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-import:hover {
    background-color: #138496 !important;
    border-color: #138496 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-export {
    background-color: var(--warning-color) !important;
    border-color: var(--warning-color) !important;
    color: #212529 !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-export:hover {
    background-color: #e0a800 !important;
    border-color: #e0a800 !important;
    color: #212529 !important;
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

.btn-brown-light {
    background-color: var(--secondary-color) !important;
    border-color: var(--secondary-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
}

/* Update Statistics Badge Styles */
.update-stats-info .badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.update-stats-info .badge:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-hover);
}

.update-stats-info .badge i {
    margin-right: 0.25rem;
}

.update-stats-info .badge strong {
    color: var(--primary-color);
}
    transition: var(--transition);
}

.btn-brown-light:hover {
    background-color: var(--primary-light) !important;
    border-color: var(--primary-light) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

/* Barcode Upload Styles */
.btn-attachment {
    background-color: var(--accent-color) !important;
    border-color: var(--accent-color) !important;
    color: white !important;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-attachment:hover {
    background-color: #e55a2b !important;
    border-color: #e55a2b !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.barcode-edit-container {
    display: flex;
    align-items: center;
    gap: 5px;
}

.barcode-upload-btn {
    flex-shrink: 0;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.barcode-preview {
    text-align: center;
}

.barcode-preview-img {
    border: 1px solid #ddd;
    border-radius: 4px;
}

.barcode-remove-btn {
    margin-top: 5px;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    display: none; /* Hidden by default, shown when editing */
}

/* Mobile Barcode Edit Styles */
.mobile-barcode-edit-container {
    display: none; /* Hidden by default, shown when editing */
    align-items: center;
    gap: 2px;
    margin-bottom: 2px;
}

.mobile-barcode-edit-container .mobile-table-edit-field {
    flex: 1;
    min-width: 0;
    font-size: 9px;
    padding: 2px 4px;
    min-height: 20px;
    border: 1px solid var(--primary-color);
    border-radius: 3px;
}

.mobile-barcode-preview {
    display: none; /* Hidden by default, shown when editing */
    border: 1px dashed #dee2e6;
    border-radius: 4px;
    padding: 2px;
    text-align: center;
    background-color: #f8f9fa;
    margin-top: 2px;
}

.mobile-barcode-preview.active {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 4px;
    border: 1px dashed #dee2e6;
    border-radius: 4px;
    background-color: #f8f9fa;
    margin-top: 2px;
}

.mobile-barcode-preview img {
    border-radius: 2px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    max-width: 60px;
    max-height: 45px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.mobile-table-btn-upload {
    background: var(--info-color);
    color: white;
    min-width: 18px;
    height: 18px;
    padding: 1px;
    border: none;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.mobile-table-btn-danger {
    background: var(--danger-color);
    color: white;
    min-width: 16px;
    height: 16px;
    padding: 1px;
    margin-top: 1px;
    border: none;
    border-radius: 3px;
    display: none; /* Hidden by default, shown when editing */
    align-items: center;
    justify-content: center;
}

.mobile-table-btn-upload:hover,
.mobile-table-btn-danger:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
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
    
    .mobile-barcode-preview img {
        max-width: 50px;
        max-height: 40px;
    }
    
    .mobile-barcode-edit-container .mobile-table-edit-field {
        font-size: 8px;
        min-height: 18px;
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
    
    .mobile-barcode-preview img {
        max-width: 40px;
        max-height: 30px;
    }
    
    .mobile-barcode-edit-container .mobile-table-edit-field {
        font-size: 7px;
        min-height: 16px;
    }
    
    .mobile-table-btn-upload,
    .mobile-table-btn-danger {
        min-width: 14px;
        height: 14px;
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

<?php $this->load->view('database/export_modal'); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <style>
    /* Searchable select styles */
    .flag-doc-select {
        position: relative;
    }
    
    .flag-doc-select option {
        padding: 8px 12px;
    }
    
    /* Custom search input for select */
    .select-search-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .select-search-input {
        width: 100%;
        padding: 6px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .select-search-input:focus {
        outline: none;
        border-color: #8B4513;
        box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
    }
    </style>
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
        } /*else {
            showAlert('Gagal copy teks ke clipboard', 'error');
        }*/
    } catch (err) {
        console.error('Fallback copy failed:', err);
        //showAlert('Gagal copy teks ke clipboard', 'error');
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
    console.log('toggleEditMobileTable called');
    const row = button.closest('tr');
    const editFields = row.querySelectorAll('.mobile-edit-field');
    const displayValues = row.querySelectorAll('.value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    console.log('Found elements:', {
        row: row,
        editFields: editFields.length,
        displayValues: displayValues.length,
        editBtn: editBtn,
        saveBtn: saveBtn,
        cancelBtn: cancelBtn
    });
    
    // Add editing class for visual feedback
    row.classList.add('editing');
    
    editFields.forEach(field => {
        // Check if it's a container (like barcode) or regular input
        if (field.classList.contains('mobile-barcode-edit-container')) {
            field.style.display = 'flex';
        } else {
            field.style.display = 'block';
        }
    });
    displayValues.forEach(value => value.style.display = 'none');
    
    // Special handling for barcode field in mobile
    const barcodeContainer = row.querySelector('.mobile-barcode-edit-container');
    if (barcodeContainer) {
        barcodeContainer.style.display = 'flex';
        // Show preview if barcode exists
        const barcodeValue = row.querySelector('[data-field="barcode"]').getAttribute('data-value');
        console.log('Mobile barcode value for preview:', barcodeValue);
        
        if (barcodeValue && barcodeValue !== '-' && barcodeValue !== '') {
            const preview = row.querySelector('.mobile-barcode-preview');
            if (preview) {
                preview.classList.add('active');
                const previewImg = preview.querySelector('.barcode-preview-img');
                if (previewImg) {
                    // Add timestamp to prevent browser cache
                    const timestamp = new Date().getTime();
                    previewImg.src = '<?= base_url('upload/view_barcode/') ?>' + barcodeValue + '?t=' + timestamp;
                    console.log('Mobile preview image src set to:', previewImg.src);
                }
            }
            
            // Show remove button if barcode exists
            const removeBtn = row.querySelector('.barcode-remove-btn');
            if (removeBtn) {
                removeBtn.style.display = 'inline-flex';
                console.log('Mobile remove button displayed');
            } else {
                console.log('Mobile remove button not found');
            }
        } else {
            console.log('No mobile barcode value to show preview');
        }
    }
    
    // Special handling for barcode file input
    const barcodeFileInput = row.querySelector('.barcode-file-input');
    if (barcodeFileInput) {
        barcodeFileInput.style.display = 'none'; // Keep hidden
    }
    
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
    
    // Initialize searchable flag_doc selects for this row
    initializeFlagDocSearchable();
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
    
    // Special handling for barcode field in mobile
    const barcodeContainer = row.querySelector('.mobile-barcode-edit-container');
    if (barcodeContainer) {
        const barcodeInput = barcodeContainer.querySelector('input');
        const originalBarcode = row.querySelector('[data-field="barcode"]').getAttribute('data-value');
        if (barcodeInput) {
            barcodeInput.value = originalBarcode || '';
            console.log('Reset mobile barcode input to original value:', originalBarcode);
        }
        // Hide preview if exists
        const preview = row.querySelector('.mobile-barcode-preview');
        if (preview) {
            preview.classList.remove('active');
        }
        
        // Hide remove button
        const removeBtn = row.querySelector('.barcode-remove-btn');
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    }
    
    // Remove editing class
    row.classList.remove('editing');
    
    editFields.forEach(field => {
        // Check if it's a container (like barcode) or regular input
        if (field.classList.contains('mobile-barcode-edit-container')) {
            field.style.display = 'none';
        } else {
            field.style.display = 'none';
        }
    });
    displayValues.forEach(value => value.style.display = 'inline');
    
    editBtn.style.display = 'inline-block';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
}

function saveRowMobileTable(button) {
    console.log('saveRowMobileTable called');
    const row = button.closest('tr');
    const rowId = row.getAttribute('data-id');
    const editFields = row.querySelectorAll('.mobile-edit-field');
    const displayValues = row.querySelectorAll('.value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    console.log('Found elements:', {
        row: row,
        rowId: rowId,
        editFields: editFields.length,
        displayValues: displayValues.length,
        editBtn: editBtn,
        saveBtn: saveBtn,
        cancelBtn: cancelBtn
    });
    
    const data = {};
    let hasData = false;
    
    editFields.forEach((field, index) => {
        // Get the corresponding td to find the field name
        const td = field.closest('td');
        const fieldName = td.getAttribute('data-field');
        console.log(`Field ${index}:`, { field, td, fieldName, value: field.value });
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
                const valueElement = displayValues[index];
                const fieldName = valueElement.getAttribute('data-field');
                let displayValue = field.value;
                
                if (fieldName === 'status') {
                    const statusMap = {0: 'On Target', 1: 'Already', 2: 'Done'};
                    displayValue = statusMap[field.value] || field.value;
                    valueElement.className = `value mobile-status-badge mobile-status-${field.value}`;
                } else if (fieldName === 'tgl_lahir' && field.value) {
                    const date = new Date(field.value);
                    displayValue = date.toLocaleDateString('id-ID');
                } else if (fieldName === 'gender') {
                    const genderMap = {'L': 'Laki-laki', 'P': 'Perempuan', '': ''};
                    displayValue = genderMap[field.value] || field.value;
                }
                
                valueElement.textContent = displayValue;
                valueElement.setAttribute('data-value', field.value);
            });
            
            showAlert('Data berhasil diperbarui', 'success');
            // Redirect ke URL dengan filter yang sama setelah 1 detik
            setTimeout(() => {
                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                } else {
                    location.reload();
                }
            }, 1000);
            
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
        } else if (error.name === 'SyntaxError' && error.message.includes('JSON')) {
            showAlert('Response tidak valid. Silakan refresh halaman dan coba lagi.', 'error');
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
    
    // Special handling for barcode field
    const barcodeContainer = row.querySelector('.barcode-edit-container');
    if (barcodeContainer) {
        barcodeContainer.style.display = 'block';
        // Show preview if barcode exists
        const barcodeValue = row.querySelector('[data-field="barcode"]').getAttribute('data-value');
        console.log('Barcode value for preview:', barcodeValue);
        
        if (barcodeValue && barcodeValue !== '-' && barcodeValue !== '') {
            const preview = row.querySelector('.barcode-preview');
            if (preview) {
                preview.style.display = 'block';
                const previewImg = preview.querySelector('.barcode-preview-img');
                if (previewImg) {
                    // Add timestamp to prevent browser cache
                    const timestamp = new Date().getTime();
                    previewImg.src = '<?= base_url('upload/view_barcode/') ?>' + barcodeValue + '?t=' + timestamp;
                    console.log('Preview image src set to:', previewImg.src);
                }
            }
            
            // Show remove button if barcode exists
            const removeBtn = row.querySelector('.barcode-remove-btn');
            if (removeBtn) {
                removeBtn.style.display = 'inline-block';
                console.log('Remove button displayed');
            } else {
                console.log('Remove button not found');
            }
        } else {
            console.log('No barcode value to show preview');
        }
    }
    
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
    
    // Initialize searchable flag_doc selects for this row
    initializeFlagDocSearchable();
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
    
    // Special handling for barcode field
    const barcodeContainer = row.querySelector('.barcode-edit-container');
    if (barcodeContainer) {
        const barcodeInput = barcodeContainer.querySelector('input');
        const originalBarcode = row.querySelector('[data-field="barcode"]').getAttribute('data-value');
        if (barcodeInput) {
            barcodeInput.value = originalBarcode || '';
        }
        // Hide preview if exists
        const preview = row.querySelector('.barcode-preview');
        if (preview) {
            preview.style.display = 'none';
        }
        
        // Hide remove button
        const removeBtn = row.querySelector('.barcode-remove-btn');
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    }
    
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
    
    // Special handling for barcode field (it's inside a container)
    const barcodeContainer = row.querySelector('.barcode-edit-container');
    if (barcodeContainer) {
        const barcodeInput = barcodeContainer.querySelector('input');
        if (barcodeInput) {
            data['barcode'] = barcodeInput.value;
            console.log('Barcode value found in container:', barcodeInput.value);
        }
    } else {
        // Fallback: try to find barcode input directly
        const barcodeInput = row.querySelector('input[data-field="barcode"], .edit-field[data-field="barcode"]');
        if (barcodeInput) {
            data['barcode'] = barcodeInput.value;
            console.log('Barcode value found directly:', barcodeInput.value);
        }
    }
    
    // Debug logging
    console.log('=== DESKTOP SAVE DEBUG ===');
    console.log('Saving data to server:', data);
    console.log('Row ID:', rowId);
    console.log('Barcode value in data:', data.barcode);
    console.log('Data keys:', Object.keys(data));
    console.log('Barcode container found:', !!barcodeContainer);
    if (barcodeContainer) {
        const barcodeInput = barcodeContainer.querySelector('input');
        console.log('Barcode input found:', !!barcodeInput);
        console.log('Barcode input value:', barcodeInput ? barcodeInput.value : 'N/A');
    }
    console.log('==========================');
    
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
            // Redirect back to previous page with filters after 1 second
            setTimeout(() => {
                const currentUrl = new URL(window.location.href);
                const params = new URLSearchParams(currentUrl.search);
                
                // Build redirect URL with current filters
                let redirectUrl = '<?= base_url('database/index') ?>';
                const queryParams = [];
                
                // Add all current filters
                ['nama', 'nomor_paspor', 'no_visa', 'flag_doc', 'tanggaljam', 'status', 'gender', 'page'].forEach(param => {
                    if (params.has(param) && params.get(param)) {
                        queryParams.push(`${param}=${encodeURIComponent(params.get(param))}`);
                    }
                });
                
                if (queryParams.length > 0) {
                    redirectUrl += '?' + queryParams.join('&');
                }
                
                window.location.href = redirectUrl;
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

// Barcode Upload Functions
function initializeBarcodeUpload() {
    // Initialize barcode upload for desktop view
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('barcode-upload-btn')) {
            e.preventDefault();
            const fileInput = e.target.closest('.barcode-edit-container').nextElementSibling;
            fileInput.click();
        }
        
        if (e.target.classList.contains('barcode-remove-btn')) {
            e.preventDefault();
            removeBarcodeImage(e.target);
        }
    });
    
    // Handle file selection
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('barcode-file-input')) {
            uploadBarcodeImage(e.target);
        }
    });
}

function uploadBarcodeImage(fileInput) {
    const file = fileInput.files[0];
    if (!file) return;
    
    const row = fileInput.closest('tr');
    const flagDocField = row.querySelector('[data-field="flag_doc"]');
    const flagDoc = flagDocField ? flagDocField.getAttribute('data-value') : '';
    
    if (!flagDoc || flagDoc === '-' || flagDoc === '') {
        showAlert('Flag dokumen harus diisi terlebih dahulu sebelum upload barcode', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('barcode_image', file);
    formData.append('flag_doc', flagDoc);
    
    // Show loading state
    const uploadBtn = fileInput.previousElementSibling.querySelector('.barcode-upload-btn');
    const originalText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    uploadBtn.disabled = true;
    
    fetch('<?= base_url('upload/upload_barcode') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
        
        if (data.status === 'success') {
            const barcodeInput = fileInput.previousElementSibling.querySelector('input');
            barcodeInput.value = data.barcode_value;
            
            // Show preview
            const preview = row.querySelector('.barcode-preview');
            if (preview) {
                const previewImg = preview.querySelector('.barcode-preview-img');
                previewImg.src = data.file_url;
                preview.style.display = 'block';
            }
            
            showAlert('Gambar barcode berhasil diupload!', 'success');
        } else {
            showAlert('Upload error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
        console.error('Upload error:', error);
        showAlert('Terjadi kesalahan saat upload', 'error');
    });
}

function removeBarcodeImage(button) {
    const row = button.closest('tr');
    const barcodeInput = row.querySelector('.barcode-edit-container input');
    const preview = row.querySelector('.barcode-preview');
    
    barcodeInput.value = '';
    if (preview) {
        preview.style.display = 'none';
    }
    
    showAlert('Gambar barcode berhasil dihapus!', 'success');
}

// Download Barcode Attachments Function
function downloadBarcodeAttachments() {
    // Get current filters
    const urlParams = new URLSearchParams(window.location.search);
    const filters = {
        tanggaljam: urlParams.get('tanggaljam') || '',
        flag_doc: urlParams.get('flag_doc') || '',
        status: urlParams.get('status') || ''
    };
    
    // Show loading state
    const downloadBtn = document.querySelector('.btn-attachment');
    const originalText = downloadBtn.innerHTML;
    downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
    downloadBtn.disabled = true;
    
    // Create download URL with filters
    let downloadUrl = '<?= base_url('database/download_barcode_attachments') ?>';
    const queryParams = new URLSearchParams();
    
    if (filters.tanggaljam) queryParams.append('tanggaljam', filters.tanggaljam);
    if (filters.flag_doc) queryParams.append('flag_doc', filters.flag_doc);
    if (filters.status) queryParams.append('status', filters.status);
    
    if (queryParams.toString()) {
        downloadUrl += '?' + queryParams.toString();
    }
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = 'barcode_attachments.zip';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Reset button state
    setTimeout(() => {
        downloadBtn.innerHTML = originalText;
        downloadBtn.disabled = false;
        showAlert('Download attachment berhasil dimulai!', 'success');
    }, 1000);
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
        } /*else {
            showAlert('Gagal copy teks ke clipboard', 'error');
        }*/
    } catch (err) {
        console.error('Fallback copy failed:', err);
        //showAlert('Gagal copy teks ke clipboard', 'error');
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
function toggleEditMobileTable(button) {
    const row = button.closest('tr');
    const editFields = row.querySelectorAll('.mobile-edit-field');
    const displayValues = row.querySelectorAll('.value');
    const editBtn = row.querySelector('.btn-edit');
    const saveBtn = row.querySelector('.btn-save');
    const cancelBtn = row.querySelector('.btn-cancel');
    
    // Add editing class for visual feedback
    row.classList.add('editing');
    
    editFields.forEach(field => {
        // Check if it's a container (like barcode) or regular input
        if (field.classList.contains('mobile-barcode-edit-container')) {
            field.style.display = 'flex';
        } else {
            field.style.display = 'block';
        }
    });
    displayValues.forEach(value => value.style.display = 'none');
    
    // Special handling for barcode field in mobile
    const barcodeContainer = row.querySelector('.mobile-barcode-edit-container');
    if (barcodeContainer) {
        barcodeContainer.style.display = 'flex';
        // Show preview if barcode exists
        const barcodeValue = row.querySelector('[data-field="barcode"]').getAttribute('data-value');
        if (barcodeValue && barcodeValue !== '-' && barcodeValue !== '') {
            const preview = row.querySelector('.mobile-barcode-preview');
            if (preview) {
                preview.classList.add('active');
                const previewImg = preview.querySelector('.barcode-preview-img');
                if (previewImg) {
                    // Add timestamp to prevent browser cache
                    const timestamp = new Date().getTime();
                    previewImg.src = '<?= base_url('upload/view_barcode/') ?>' + barcodeValue + '?t=' + timestamp;
                }
            }
            
            // Show remove button if barcode exists
            const removeBtn = row.querySelector('.barcode-remove-btn');
            if (removeBtn) {
                removeBtn.style.display = 'inline-flex';
            }
        }
    }
    
    // Special handling for barcode file input
    const barcodeFileInput = row.querySelector('.barcode-file-input');
    if (barcodeFileInput) {
        barcodeFileInput.style.display = 'none'; // Keep hidden
    }
    
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
    
    // Special handling for barcode field in mobile
    const barcodeContainer = row.querySelector('.mobile-barcode-edit-container');
    if (barcodeContainer) {
        const barcodeInput = barcodeContainer.querySelector('input');
        const originalBarcode = row.querySelector('[data-field="barcode"]').getAttribute('data-value');
        if (barcodeInput) {
            barcodeInput.value = originalBarcode || '';
            console.log('Reset barcode input to original value:', originalBarcode);
        }
        // Hide preview if exists
        const preview = row.querySelector('.mobile-barcode-preview');
        if (preview) {
            preview.classList.remove('active');
        }
        
        // Hide remove button
        const removeBtn = row.querySelector('.barcode-remove-btn');
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    }
    
    // Remove editing class
    row.classList.remove('editing');
    
    editFields.forEach(field => {
        // Check if it's a container (like barcode) or regular input
        if (field.classList.contains('mobile-barcode-edit-container')) {
            field.style.display = 'none';
        } else {
            field.style.display = 'none';
        }
    });
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
    
    // Special handling for barcode field in mobile (it's inside a container)
    const barcodeContainer = row.querySelector('.mobile-barcode-edit-container');
    if (barcodeContainer) {
        const barcodeInput = barcodeContainer.querySelector('input');
        if (barcodeInput) {
            data['barcode'] = barcodeInput.value;
            console.log('Mobile barcode value found in container:', barcodeInput.value);
            console.log('Mobile barcode input field:', barcodeInput);
            console.log('Mobile barcode container:', barcodeContainer);
        } else {
            console.log('Mobile barcode input not found in container');
        }
    } else {
        console.log('Mobile barcode container not found');
    }
    
    // Validate that we have data to save
    if (!hasData || Object.keys(data).length === 0) {
        showAlert('Tidak ada data yang dapat disimpan', 'error');
        return;
    }
    
    // Debug logging
    console.log('=== MOBILE SAVE DEBUG ===');
    console.log('Saving data:', data);
    console.log('Row ID:', rowId);
    console.log('Barcode value in data:', data.barcode);
    console.log('Data keys:', Object.keys(data));
    console.log('Barcode container found:', !!barcodeContainer);
    if (barcodeContainer) {
        const barcodeInput = barcodeContainer.querySelector('input');
        console.log('Barcode input found:', !!barcodeInput);
        console.log('Barcode input value:', barcodeInput ? barcodeInput.value : 'N/A');
    }
    console.log('========================');
    
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
            
            // Special handling for barcode field display update
            const barcodeContainer = row.querySelector('.mobile-barcode-edit-container');
            if (barcodeContainer) {
                const barcodeInput = barcodeContainer.querySelector('input');
                const barcodeValueElement = row.querySelector('[data-field="barcode"] .value');
                if (barcodeInput && barcodeValueElement) {
                    const barcodeValue = barcodeInput.value;
                    // Update display value for barcode
                    if (barcodeValue && barcodeValue !== '') {
                        // Add timestamp to prevent browser cache
                        const timestamp = new Date().getTime();
                        barcodeValueElement.innerHTML = '<a href="<?= base_url('upload/view_barcode/') ?>' + barcodeValue + '?t=' + timestamp + '" target="_blank" title="Lihat gambar barcode"><i class="fas fa-check-circle" style="color: green;"></i></a>';
                    } else {
                        barcodeValueElement.innerHTML = '<i class="fas fa-times-circle" style="color: red;" title="Tidak ada barcode"></i>';
                    }
                    barcodeValueElement.setAttribute('data-value', barcodeValue);
                }
            }
            
            showAlert('Data berhasil diperbarui', 'success');
            // Redirect back to previous page with filters after 1 second
            setTimeout(() => {
                const currentUrl = new URL(window.location.href);
                const params = new URLSearchParams(currentUrl.search);
                
                // Build redirect URL with current filters
                let redirectUrl = '<?= base_url('database/index') ?>';
                const queryParams = [];
                
                // Add all current filters
                ['nama', 'nomor_paspor', 'no_visa', 'flag_doc', 'tanggaljam', 'status', 'gender', 'page'].forEach(param => {
                    if (params.has(param) && params.get(param)) {
                        queryParams.push(`${param}=${encodeURIComponent(params.get(param))}`);
                    }
                });
                
                if (queryParams.length > 0) {
                    redirectUrl += '?' + queryParams.join('&');
                }
                
                window.location.href = redirectUrl;
            }, 1000);
            
            // Remove editing class
            row.classList.remove('editing');
            
            editFields.forEach(field => {
                // Check if it's a container (like barcode) or regular input
                if (field.classList.contains('mobile-barcode-edit-container')) {
                    field.style.display = 'none';
                } else {
                    field.style.display = 'none';
                }
            });
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
            // Redirect back to previous page with filters after 1 second
            setTimeout(() => {
                const currentUrl = new URL(window.location.href);
                const params = new URLSearchParams(currentUrl.search);
                
                // Build redirect URL with current filters
                let redirectUrl = '<?= base_url('database/index') ?>';
                const queryParams = [];
                
                // Add all current filters
                ['nama', 'nomor_paspor', 'no_visa', 'flag_doc', 'tanggaljam', 'status', 'gender', 'page'].forEach(param => {
                    if (params.has(param) && params.get(param)) {
                        queryParams.push(`${param}=${encodeURIComponent(params.get(param))}`);
                    }
                });
                
                if (queryParams.length > 0) {
                    redirectUrl += '?' + queryParams.join('&');
                }
                
                window.location.href = redirectUrl;
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
    
    // Initialize barcode upload functionality
    initializeBarcodeUpload();
    
    // Any mobile-specific initialization can go here
});

// Delete data function with filter preservation
function deleteData(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        // Get current filters from URL
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        
        // Build redirect URL with current filters
        let redirectUrl = '<?= base_url('database/index') ?>';
        const queryParams = [];
        
        // Add all current filters
        ['nama', 'nomor_paspor', 'no_visa', 'flag_doc', 'tanggaljam', 'status', 'gender', 'page'].forEach(param => {
            if (params.has(param) && params.get(param)) {
                queryParams.push(`${param}=${encodeURIComponent(params.get(param))}`);
            }
        });
        
        if (queryParams.length > 0) {
            redirectUrl += '?' + queryParams.join('&');
        }
        
        // Show loading state
        const button = event.target.closest('button');
        if (button) {
            const originalHTML = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Perform delete with redirect
            window.location.href = '<?= base_url('database/delete/') ?>' + id + '?redirect=' + encodeURIComponent(redirectUrl);
        }
    }
}

// Download barcode attachments function
function downloadBarcodeAttachments() {
    // Get current filters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const flag_doc = urlParams.get('flag_doc');
    const tanggaljam = urlParams.get('tanggaljam');
    const status = urlParams.get('status');
    
    // Build download URL with current filters
    let downloadUrl = '<?= base_url('database/download_barcode_attachments') ?>?';
    const params = [];
    
    if (flag_doc) params.push('flag_doc=' + encodeURIComponent(flag_doc));
    if (tanggaljam) params.push('tanggaljam=' + encodeURIComponent(tanggaljam));
    if (status) params.push('status=' + encodeURIComponent(status));
    
    downloadUrl += params.join('&');
    
    // Show loading state
    const button = event.target.closest('.btn-attachment');
    if (button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="d-none d-sm-inline">Downloading...</span>';
        
        // Download file
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button after a delay
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        }, 2000);
    } else {
        // Fallback if button not found
        window.location.href = downloadUrl;
    }
}

// Barcode upload functions
function initializeBarcodeUpload() {
    // Add event listeners for barcode upload buttons
    document.addEventListener('click', function(e) {
        const uploadBtn = e.target.closest('.barcode-upload-btn');
        if (uploadBtn) {
            e.preventDefault();
            uploadBarcodeImage(uploadBtn);
        }
        
        const removeBtn = e.target.closest('.barcode-remove-btn');
        if (removeBtn) {
            e.preventDefault();
            removeBarcodeImage(removeBtn);
        }
    });
}

function uploadBarcodeImage(button) {
    const row = button.closest('tr');
    const fileInput = row.querySelector('.barcode-file-input');
    console.log('File input found:', fileInput);
    
    // Check if this is mobile or desktop table
    const isMobile = row.closest('.mobile-excel-table') !== null;
    
    let barcodeInput, flagDocCell, flagDocValue;
    
    if (isMobile) {
        // Mobile table
        barcodeInput = row.querySelector('.mobile-barcode-edit-container input');
        flagDocCell = row.querySelector('[data-field="flag_doc"]');
        flagDocValue = flagDocCell ? flagDocCell.getAttribute('data-value') : '';
        console.log('Mobile table detected. Barcode input:', barcodeInput);
    } else {
        // Desktop table
        barcodeInput = row.querySelector('.barcode-edit-container input');
        flagDocCell = row.querySelector('[data-field="flag_doc"]');
        flagDocValue = flagDocCell ? flagDocCell.getAttribute('data-value') : '';
        console.log('Desktop table detected. Barcode input:', barcodeInput);
    }
    
    // Check if flag_doc is empty or just dash
    if (!flagDocValue || flagDocValue === '-' || flagDocValue.trim() === '') {
        showAlert('Flag dokumen harus diisi terlebih dahulu. Silakan edit field Flag Dokumen dan pilih flag yang sesuai.', 'error');
        console.log('Flag doc validation failed. Value:', flagDocValue);
        return;
    }
    
    console.log('Flag doc validation passed. Value:', flagDocValue);
    
    fileInput.click();
    
    fileInput.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showAlert('Pilih file gambar yang valid', 'error');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showAlert('Ukuran file terlalu besar. Maksimal 5MB', 'error');
                return;
            }
            
            // Show preview based on table type
            let preview, previewImg;
            if (isMobile) {
                preview = row.querySelector('.mobile-barcode-preview');
                previewImg = row.querySelector('.mobile-barcode-preview .barcode-preview-img');
            } else {
                preview = row.querySelector('.barcode-preview');
                previewImg = row.querySelector('.barcode-preview .barcode-preview-img');
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                if (isMobile) {
                    preview.classList.add('active');
                } else {
                    preview.style.display = 'block';
                }
                console.log('Preview updated with selected file');
            };
            reader.readAsDataURL(file);
            
            // Upload file
            uploadBarcodeFile(file, flagDocValue, barcodeInput, row);
        }
    };
}

function uploadBarcodeFile(file, flagDoc, barcodeInput, row) {
    const formData = new FormData();
    formData.append('barcode_image', file);
    formData.append('flag_doc', flagDoc);
    
    // Get existing barcode filename for replacement from database value
    const barcodeTd = row.querySelector('[data-field="barcode"]');
    const existingBarcode = barcodeTd ? barcodeTd.getAttribute('data-value') : '';
    
    console.log('=== UPLOAD REPLACE DEBUG ===');
    console.log('Barcode input value:', barcodeInput.value);
    console.log('Database barcode value:', existingBarcode);
    console.log('Barcode TD element:', barcodeTd);
    console.log('================================');
    
    if (existingBarcode && existingBarcode.trim() !== '' && existingBarcode !== '-' && existingBarcode !== 'null') {
        formData.append('existing_barcode', existingBarcode);
        console.log('Replacing existing barcode:', existingBarcode);
    } else {
        console.log('No existing barcode to replace');
    }
    
    // Show loading state
    const button = row.querySelector('.barcode-upload-btn');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('<?= base_url('upload/upload_barcode') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
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
    .then(data => {
        if (!data) return;
        button.disabled = false;
        button.innerHTML = originalText;
        
        if (data.status === 'success') {
            barcodeInput.value = data.barcode_value; // Gunakan barcode_value untuk database
            console.log('=== UPLOAD SUCCESS DEBUG ===');
            console.log('Barcode uploaded successfully. Value set to:', data.barcode_value);
            console.log('Barcode input field:', barcodeInput);
            console.log('Barcode input value after set:', barcodeInput.value);
            console.log('Server response:', data);
            console.log('Flag doc used:', flagDoc);
            console.log('Row ID:', row.getAttribute('data-id'));
            console.log('================================');
            
            // Update data-value attribute of the td element
            const barcodeTd = row.querySelector('[data-field="barcode"]');
            if (barcodeTd) {
                barcodeTd.setAttribute('data-value', data.barcode_value);
                console.log('Updated barcode td data-value to:', data.barcode_value);
            }
            
            // Update preview with new image
            const isMobile = row.closest('.mobile-excel-table') !== null;
            let preview, previewImg;
            if (isMobile) {
                preview = row.querySelector('.mobile-barcode-preview');
                previewImg = row.querySelector('.mobile-barcode-preview .barcode-preview-img');
            } else {
                preview = row.querySelector('.barcode-preview');
                previewImg = row.querySelector('.barcode-preview .barcode-preview-img');
            }
            
            if (preview && previewImg) {
                // Add timestamp to prevent browser cache
                const timestamp = new Date().getTime();
                previewImg.src = '<?= base_url('upload/view_barcode/') ?>' + data.barcode_value + '?t=' + timestamp;
                if (isMobile) {
                    preview.classList.add('active');
                } else {
                    preview.style.display = 'block';
                }
                console.log('Updated preview with new image:', data.barcode_value);
            }
            
            showAlert('Gambar barcode berhasil diupload!', 'success');
            
            console.log('=== CALLING AUTO SAVE ===');
            console.log('Row element:', row);
            console.log('Row ID:', row.getAttribute('data-id'));
            console.log('Barcode value to save:', data.barcode_value);
            console.log('Is mobile table:', row.closest('.mobile-excel-table') !== null);
            console.log('================================');
            
            // Auto save barcode value to database
            autoSaveBarcodeToDatabase(row, data.barcode_value);
            
            // Reset file input for next upload
            const fileInput = row.querySelector('.barcode-file-input');
            if (fileInput) {
                fileInput.value = '';
            }
        } else {
            showAlert(data.message || 'Gagal mengupload gambar', 'error');
            // Remove preview on error
            const isMobile = row.closest('.mobile-excel-table') !== null;
            let preview;
            if (isMobile) {
                preview = row.querySelector('.mobile-barcode-preview');
                preview.classList.remove('active');
            } else {
                preview = row.querySelector('.barcode-preview');
                preview.style.display = 'none';
            }
            
            // Reset file input on error
            const fileInput = row.querySelector('.barcode-file-input');
            if (fileInput) {
                fileInput.value = '';
            }
        }
    })
    .catch(error => {
        button.disabled = false;
        button.innerHTML = originalText;
        showAlert('Terjadi kesalahan saat mengupload gambar', 'error');
        console.error('Upload error:', error);
        
        // Remove preview on error
        const isMobile = row.closest('.mobile-excel-table') !== null;
        let preview;
        if (isMobile) {
            preview = row.querySelector('.mobile-barcode-preview');
            preview.classList.remove('active');
        } else {
            preview = row.querySelector('.barcode-preview');
            preview.style.display = 'none';
        }
        
        // Reset file input on error
        const fileInput = row.querySelector('.barcode-file-input');
        if (fileInput) {
            fileInput.value = '';
        }
    });
}

function removeBarcodeImage(button) {
    const row = button.closest('tr');
    
    // Check if this is mobile or desktop table
    const isMobile = row.closest('.mobile-excel-table') !== null;
    
    let preview, fileInput, barcodeInput;
    
    if (isMobile) {
        // Mobile table
        preview = row.querySelector('.mobile-barcode-preview');
        fileInput = row.querySelector('.barcode-file-input');
        barcodeInput = row.querySelector('.mobile-barcode-edit-container input');
    } else {
        // Desktop table
        preview = row.querySelector('.barcode-preview');
        fileInput = row.querySelector('.barcode-file-input');
        barcodeInput = row.querySelector('.barcode-edit-container input');
    }
    
    // Get existing barcode value from database
    const barcodeTd = row.querySelector('[data-field="barcode"]');
    const existingBarcode = barcodeTd ? barcodeTd.getAttribute('data-value') : '';
    
    console.log('=== REMOVE BARCODE DEBUG ===');
    console.log('Existing barcode from database:', existingBarcode);
    console.log('Is mobile:', isMobile);
    console.log('================================');
    
    // Delete file from server if exists
    if (existingBarcode && existingBarcode.trim() !== '' && existingBarcode !== '-' && existingBarcode !== 'null') {
        deleteBarcodeFileFromServer(existingBarcode, isMobile);
        
        // Also clear barcode from database if not in edit mode
        const isEditing = row.classList.contains('editing') || row.querySelector('.edit-field[style*="block"]') !== null;
        if (!isEditing) {
            // Auto save empty barcode to database
            const rowId = row.getAttribute('data-id');
            if (rowId) {
                autoSaveBarcodeToDatabase(row, '');
            }
        }
    } else {
        // Show notification for mobile even if no file to delete
        if (isMobile) {
            showAlert('Barcode berhasil dihapus', 'success');
        }
    }
    
    if (isMobile) {
        preview.classList.remove('active');
    } else {
        preview.style.display = 'none';
    }
    fileInput.value = '';
    barcodeInput.value = '';
    
    // Update data-value attribute of the td element
    if (barcodeTd) {
        barcodeTd.setAttribute('data-value', '');
        console.log('Cleared barcode td data-value');
    }
    
    // Clear preview image source
    if (preview) {
        const previewImg = preview.querySelector('.barcode-preview-img');
        if (previewImg) {
            previewImg.src = '';
        }
    }
    
    console.log('Barcode image removed and preview cleared');
}

// Delete barcode file from server
function deleteBarcodeFileFromServer(filename, isMobile = false) {
    console.log('Attempting to delete barcode file from server:', filename);
    
    const formData = new FormData();
    formData.append('filename', filename);
    
    fetch('<?= base_url('upload/delete_barcode') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (response.status === 401) {
            console.error('Session expired while deleting barcode file');
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.status === 'success') {
            console.log('Barcode file successfully deleted from server:', filename);
            // Show success notification for mobile
            if (isMobile) {
                showAlert('File barcode berhasil dihapus dari server', 'success');
            }
        } else {
            console.error('Failed to delete barcode file from server:', data ? data.message : 'Unknown error');
            // Show error notification for mobile
            if (isMobile) {
                showAlert('Gagal menghapus file barcode dari server', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error deleting barcode file from server:', error);
        // Show error notification for mobile
        if (isMobile) {
            showAlert('Terjadi kesalahan saat menghapus file barcode', 'error');
        }
    });
}

// Auto save barcode to database after successful upload
function autoSaveBarcodeToDatabase(row, barcodeValue) {
    console.log('=== AUTO SAVE FUNCTION START ===');
    console.log('Row element:', row);
    console.log('Barcode value:', barcodeValue);
    
    const rowId = row.getAttribute('data-id');
    console.log('Row ID:', rowId);
    
    if (!rowId) {
        console.error('Row ID not found for auto save');
        return;
    }
    
    // Check if row is in edit mode
    const isMobile = row.closest('.mobile-excel-table') !== null;
    const isEditing = row.classList.contains('editing') || row.querySelector('.edit-field[style*="block"]') !== null;
    
    // Get existing barcode value from database
    const barcodeTd = row.querySelector('[data-field="barcode"]');
    const existingBarcode = barcodeTd ? barcodeTd.getAttribute('data-value') : '';
    
    console.log('=== AUTO SAVE REPLACE DEBUG ===');
    console.log('Existing barcode from database:', existingBarcode);
    console.log('New barcode value:', barcodeValue);
    console.log('Is replacing:', existingBarcode && existingBarcode !== '-' && existingBarcode !== 'null');
    console.log('==================================');
    
    const data = {
        barcode: barcodeValue
    };
    
    console.log('=== AUTO SAVE BARCODE DEBUG ===');
    console.log('Auto saving barcode to database. Row ID:', rowId, 'Barcode value:', barcodeValue);
    console.log('Row element:', row);
    console.log('Is mobile table:', isMobile);
    console.log('Is editing mode:', isEditing);
    console.log('Data to send:', data);
    console.log('================================');
    
    // For barcode upload/replace, always save immediately regardless of edit mode
    // This ensures the database is updated with the new barcode value
    if (isEditing && barcodeValue) {
        console.log('Row is in edit mode, but barcode upload should still save immediately');
        // Continue with auto-save even in edit mode for barcode uploads
    }
    
    // Show loading state
    const saveBtn = row.querySelector('.btn-save');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    console.log('=== SENDING FETCH REQUEST ===');
    console.log('URL:', '<?= base_url('database/update_ajax/') ?>' + rowId);
    console.log('Data to send:', data);
    console.log('================================');
    
    fetch('<?= base_url('database/update_ajax/') ?>' + rowId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('=== FETCH RESPONSE ===');
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        console.log('================================');
        
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
        
        console.log('=== DATABASE UPDATE RESULT ===');
        console.log('Server response:', result);
        console.log('Success status:', result.success);
        console.log('Message:', result.message);
        console.log('================================');
        
        if (result.success) {
            console.log('Barcode successfully saved to database');
            
            // Update display value for barcode
            let barcodeValueElement;
            
            if (isMobile) {
                barcodeValueElement = row.querySelector('[data-field="barcode"] .value');
            } else {
                barcodeValueElement = row.querySelector('[data-field="barcode"] .display-value');
            }
            
            console.log('=== DISPLAY UPDATE DEBUG ===');
            console.log('Is mobile:', isMobile);
            console.log('Barcode value element found:', barcodeValueElement);
            console.log('Barcode value to set:', barcodeValue);
            console.log('================================');
            
            if (barcodeValueElement) {
                if (barcodeValue && barcodeValue.trim() !== '') {
                    // Add timestamp to prevent browser cache
                    const timestamp = new Date().getTime();
                    barcodeValueElement.innerHTML = '<a href="<?= base_url('upload/view_barcode/') ?>' + barcodeValue + '?t=' + timestamp + '" target="_blank" title="Lihat gambar barcode"><i class="fas fa-check-circle" style="color: green;"></i></a>';
                } else {
                    // Show empty barcode icon
                    barcodeValueElement.innerHTML = '<i class="fas fa-times-circle" style="color: red;" title="Tidak ada barcode"></i>';
                }
                barcodeValueElement.setAttribute('data-value', barcodeValue);
                console.log('Updated barcode display value');
            }
            
            // Also update the data-value attribute of the td element
            const barcodeTd = row.querySelector('[data-field="barcode"]');
            if (barcodeTd) {
                barcodeTd.setAttribute('data-value', barcodeValue);
                console.log('Updated barcode td data-value to:', barcodeValue);
            }
            
            if (barcodeValue && barcodeValue.trim() !== '') {
                // Check if this is a replace operation
                const isReplacing = existingBarcode && existingBarcode.trim() !== '' && existingBarcode !== '-' && existingBarcode !== 'null';
                if (isReplacing) {
                    showAlert('Barcode berhasil diganti dan disimpan ke database', 'success');
                } else {
                    showAlert('Barcode berhasil disimpan ke database', 'success');
                }
            } else {
                showAlert('Barcode berhasil dihapus dari database', 'success');
            }
            
            // Auto refresh disabled for better user experience
            // Data is already updated in the display
            // setTimeout(() => {
            //     location.reload();
            // }, 1000);
        } else {
            console.error('Failed to save barcode to database:', result.message);
            showAlert('Gagal menyimpan barcode ke database: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error saving barcode to database:', error);
        showAlert('Terjadi kesalahan saat menyimpan barcode ke database', 'error');
    })
    .finally(() => {
        // Reset button state
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i>';
        }
    });
}

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

// Make flag_doc selects searchable
function makeFlagDocSelectsSearchable() {
    const flagDocSelects = document.querySelectorAll('.flag-doc-select');
    
    flagDocSelects.forEach(select => {
        // Create search input
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'select-search-input';
        searchInput.placeholder = 'Ketik untuk mencari flag dokumen...';
        searchInput.style.display = 'none';
        
        // Insert search input before the select
        select.parentNode.insertBefore(searchInput, select);
        
        // Show search input when select is focused
        select.addEventListener('focus', function() {
            searchInput.style.display = 'block';
            searchInput.focus();
        });
        
        // Filter options based on search input
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = select.querySelectorAll('option');
            
            options.forEach(option => {
                const optionText = option.textContent.toLowerCase();
                if (optionText.includes(searchTerm) || option.value === '') {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        });
        
        // Hide search input when select loses focus
        select.addEventListener('blur', function() {
            setTimeout(() => {
                if (!searchInput.matches(':focus')) {
                    searchInput.style.display = 'none';
                    searchInput.value = '';
                    // Reset all options to visible
                    const options = select.querySelectorAll('option');
                    options.forEach(option => {
                        option.style.display = '';
                    });
                }
            }, 200);
        });
        
        // Handle search input blur
        searchInput.addEventListener('blur', function() {
            setTimeout(() => {
                if (!select.matches(':focus')) {
                    this.style.display = 'none';
                    this.value = '';
                    // Reset all options to visible
                    const options = select.querySelectorAll('option');
                    options.forEach(option => {
                        option.style.display = '';
                    });
                }
            }, 200);
        });
    });
}

// Initialize searchable flag_doc selects when page loads
document.addEventListener('DOMContentLoaded', function() {
    makeFlagDocSelectsSearchable();
});

// Re-initialize when edit mode is toggled
function initializeFlagDocSearchable() {
    setTimeout(() => {
        makeFlagDocSelectsSearchable();
    }, 100);
}

// Function to handle redirect after AJAX update
function handleRedirectAfterUpdate(result) {
    console.log('=== REDIRECT DEBUG ===');
    console.log('Result:', result);
    console.log('Redirect URL from server:', result.redirect_url);
    console.log('Current URL:', window.location.href);
    
    if (result.redirect_url) {
        console.log('Using server redirect URL:', result.redirect_url);
        window.location.href = result.redirect_url;
    } else {
        console.log('Using fallback redirect URL');
        // Fallback: build redirect URL manually
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        
        let redirectUrl = '<?= base_url('database/index') ?>';
        const queryParams = [];
        
        ['nama', 'nomor_paspor', 'no_visa', 'flag_doc', 'tanggaljam', 'status', 'gender', 'page', 'status_jadwal', 'tanggal_pengerjaan'].forEach(param => {
            if (params.has(param) && params.get(param)) {
                queryParams.push(`${param}=${encodeURIComponent(params.get(param))}`);
            }
        });
        
        if (queryParams.length > 0) {
            redirectUrl += '?' + queryParams.join('&');
        }
        
        console.log('Fallback redirect URL:', redirectUrl);
        window.location.href = redirectUrl;
    }
    console.log('======================');
}

// Override the redirect logic in all AJAX update functions
document.addEventListener('DOMContentLoaded', function() {
    // Find all setTimeout calls that do redirect and replace them
    const scripts = document.querySelectorAll('script');
    scripts.forEach(script => {
        if (script.textContent.includes('window.location.href = redirectUrl')) {
            // This will be handled by the new redirect function
            console.log('Redirect logic found and will be handled by new function');
        }
    });
});
</script>