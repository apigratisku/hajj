<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                   
                    <div>
                        <a href="<?= base_url('master/tambah') ?>" class="btn btn-sm" style="background: var(--gold); color: var(--dark-brown); border: none;">
                            <i class="fas fa-plus"></i> Tambah
                        </a>
                        <button type="button" class="btn btn-sm" style="background: var(--accent-color); color: white; border: none;" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import"></i> Import
                        </button>
                        <a href="<?= base_url('master/export') ?>" class="btn btn-sm" style="background: var(--warning-color); color: var(--dark-brown); border: none;" target="_blank">
                            <i class="fas fa-file-export"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Agent</th>
                                    <th>HP</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($agent)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($agent as $a): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= $a->nama_agent ?></td>
                                            <td><?= $a->hp ?></td>
                                            <td>
                                                <a href="<?= base_url('master/edit/' . $a->id_agent) ?>" class="btn btn-sm" style="background: var(--primary-color); color: white; border: none;" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url('master/hapus/' . $a->id_agent) ?>" class="btn btn-sm" style="background: var(--danger-color); color: white; border: none;" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(isset($pagination)) echo $pagination; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Pagination Styles */
.pagination {
    margin-top: 20px;
}

.page-link {
    color: var(--primary-color);
    background-color: #fff;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.page-link:hover {
    color: #fff;
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}
</style>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Kapal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= form_open_multipart('master/import') ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">File Excel/CSV</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".csv, .xls, .xlsx">
                        <small class="text-muted">Format file: .csv, .xls, .xlsx</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div> 