<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i> Data Parsing VISA
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('parsing') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload"></i> Upload PDF Baru
                        </a>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="card-body">
                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= number_format(isset($stats['total_records']) ? $stats['total_records'] : 0) ?></h3>
                                    <p>Total Data</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-database"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?= number_format(isset($stats['today_records']) ? $stats['today_records'] : 0) ?></h3>
                                    <p>Data Hari Ini</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?= number_format(isset($stats['month_records']) ? $stats['month_records'] : 0) ?></h3>
                                    <p>Data Bulan Ini</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?= number_format(isset($stats['unique_passports']) ? $stats['unique_passports'] : 0) ?></h3>
                                    <p>Paspor Unik</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-passport"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="<?= base_url('parsing/view_data') ?>">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Cari berdasarkan nama, no paspor, atau no visa..." 
                                           value="<?= htmlspecialchars($search) ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <span class="text-muted">
                                Menampilkan <?= count($parsing_data) ?> dari <?= number_format($total_records) ?> data
                            </span>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="25%">Nama</th>
                                    <th width="15%">No Paspor</th>
                                    <th width="15%">No Visa</th>
                                    <th width="12%">Tanggal Lahir</th>
                                    <th width="12%">File Asal</th>
                                    <th width="10%">Parsed At</th>
                                    <th width="6%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($parsing_data)): ?>
                                    <?php $no = (isset($offset) ? $offset : 0) + 1; ?>
                                    <?php foreach ($parsing_data as $data): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($data['nama']) ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-primary"><?= htmlspecialchars($data['no_paspor']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-success"><?= htmlspecialchars($data['no_visa']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?= date('d/m/Y', strtotime($data['tanggal_lahir'])) ?>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($data['file_name']) ?>
                                                    <?php if ($data['file_size'] > 0): ?>
                                                        <br><span class="badge badge-secondary">
                                                            <?= number_format($data['file_size'] / 1024, 1) ?> KB
                                                        </span>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <small>
                                                    <?= date('d/m/Y H:i', strtotime($data['parsed_at'])) ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="deleteData(<?= $data['id'] ?>, '<?= htmlspecialchars($data['nama']) ?>')"
                                                        title="Hapus Data">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-database fa-3x mb-3"></i>
                                            <br>
                                            <?php if (!empty($search)): ?>
                                                Tidak ada data yang ditemukan untuk pencarian "<?= htmlspecialchars($search) ?>"
                                            <?php else: ?>
                                                Belum ada data parsing. <a href="<?= base_url('parsing') ?>">Upload PDF</a> untuk memulai parsing.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($current_page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                                    <i class="fas fa-angle-double-left"></i>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $current_page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                                    <i class="fas fa-angle-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($current_page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $current_page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                                    <i class="fas fa-angle-right"></i>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                                    <i class="fas fa-angle-double-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data parsing untuk:</p>
                <p><strong id="deleteName"></strong></p>
                <p class="text-danger"><small>Data yang dihapus tidak dapat dikembalikan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;

// Safe auto hide alerts function
function safeHideAlerts() {
    try {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            }
        });
    } catch (e) {
        console.log('Error hiding alerts:', e);
    }
}

// Auto hide alerts after 5 seconds
setTimeout(safeHideAlerts, 5000);

// Enhanced delete function with better error handling
function deleteData(id, name) {
    try {
        deleteId = id;
        const deleteNameElement = document.getElementById('deleteName');
        if (deleteNameElement) {
            deleteNameElement.textContent = name;
        }
        
        // Use jQuery modal if available, otherwise use vanilla JS
        if (typeof $ !== 'undefined' && $('#deleteModal').length) {
            $('#deleteModal').modal('show');
        } else {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.style.display = 'block';
                modal.classList.add('show');
            }
        }
    } catch (e) {
        console.error('Error showing delete modal:', e);
        // Fallback: direct redirect
        if (confirm('Apakah Anda yakin ingin menghapus data ' + name + '?')) {
            window.location.href = '<?= base_url('parsing/delete_data') ?>/' + id;
        }
    }
}

// Enhanced confirm delete with loading state
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (deleteId) {
                try {
                    // Show loading state
                    const btn = this;
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
                    btn.disabled = true;
                    
                    // Redirect after a short delay to show loading
                    setTimeout(function() {
                        window.location.href = '<?= base_url('parsing/delete_data') ?>/' + deleteId;
                    }, 500);
                } catch (e) {
                    console.error('Error in delete confirmation:', e);
                    // Fallback: direct redirect
                    window.location.href = '<?= base_url('parsing/delete_data') ?>/' + deleteId;
                }
            }
        });
    }
});
</script>