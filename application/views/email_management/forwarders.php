<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-share-alt me-2"></i>Manajemen Forwarder
                    </h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary align-self-center">
                            <i class="fas fa-globe me-1"></i><?= htmlspecialchars($domain ?: 'Unknown') ?>
                        </span>
                        <a href="<?= base_url('email') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Email
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gagal mengambil daftar forwarder: <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-3 g-2 align-items-center">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="forwarderSearchInput"
                                       placeholder="Cari forwarder address..." onkeyup="filterForwarderTable()">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <?php if (isset($total_forwarders)): ?>
                            <span class="badge bg-info fs-6 px-3 py-2">
                                <i class="fas fa-filter me-1"></i>
                                <?= number_format($total_forwarders) ?> forwarder sesuai data peserta
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="forwarder-table">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" width="50">#</th>
                                    <th>Forwarder Address</th>
                                    <th>Forward To</th>
                                    <th class="text-center" width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($forwarders)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-share-alt fa-2x mb-2"></i>
                                            <br>Tidak ada forwarder yang cocok dengan data peserta untuk domain <?= htmlspecialchars($domain ?: '') ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = (isset($current_page) && $current_page > 1) ? (($current_page - 1) * $per_page) + 1 : 1; foreach ($forwarders as $fwd): ?>
                                        <?php
                                        $dest = isset($fwd['dest']) ? $fwd['dest'] : '';
                                        $forward = isset($fwd['forward']) ? $fwd['forward'] : '';
                                        if ($dest === '' || $forward === '') {
                                            continue;
                                        }
                                        $deleteUrl = base_url('email/delete_forwarder/' . urlencode($dest)) . '?forwarder=' . urlencode($forward);
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td>
                                                <i class="fas fa-envelope me-2 text-primary"></i>
                                                <strong><?= htmlspecialchars($dest) ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-arrow-right me-2 text-muted"></i>
                                                <?= htmlspecialchars($forward) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($this->session->userdata('role') == 'admin'): ?>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-forwarder"
                                                        onclick="deleteForwarder('<?= addslashes($deleteUrl) ?>', '<?= addslashes(htmlspecialchars($dest)) ?>', '<?= addslashes(htmlspecialchars($forward)) ?>')"
                                                        title="Hapus forwarder">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php else: ?>
                                                <span class="text-muted"><small>Tidak ada akses</small></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <nav aria-label="Pagination forwarder" class="mt-3">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=' . ($current_page - 1)) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <?php
                            // Tampilkan jendela halaman (maks 5 nomor) di sekitar halaman aktif
                            $window = 2;
                            $start = max(1, $current_page - $window);
                            $end = min($total_pages, $current_page + $window);
                            if ($start > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=1') ?>">1</a>
                            </li>
                            <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=' . $i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=' . $total_pages) ?>"><?= $total_pages ?></a>
                            </li>
                            <?php endif; ?>

                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= base_url('email/forwarders?domain=' . urlencode($domain) . '&page=' . ($current_page + 1)) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                        <p class="text-center text-muted small mt-2 mb-0">
                            Menampilkan <?= number_format($offset + 1) ?> - <?= number_format(min($offset + $per_page, $total_forwarders)) ?> dari <?= number_format($total_forwarders) ?> forwarder sesuai data peserta
                        </p>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteForwarder(url, dest, forward) {
    if (confirm('Apakah Anda yakin ingin menghapus forwarder ini?\n\n' + dest + '  ->  ' + forward + '\n\nTindakan ini tidak dapat dibatalkan.')) {
        window.location.href = url;
    }
}

function filterForwarderTable() {
    const input = document.getElementById('forwarderSearchInput');
    const query = input.value.toLowerCase().trim();
    const table = document.getElementById('forwarder-table');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(query) > -1 ? '' : 'none';
    });
}
</script>
