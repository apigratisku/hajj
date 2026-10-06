<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.bm-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: flex-end;
}
.bm-filters .form-group { margin-bottom: 0; }
.bm-pane {
    border: 1px solid #d8dee6;
    border-radius: 8px;
    background: #f7f9fc;
    min-height: 420px;
    display: flex;
    flex-direction: column;
}
.bm-pane-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border-bottom: 1px solid #d8dee6;
    background: #eef3fa;
    border-radius: 8px 8px 0 0;
    gap: 8px;
    flex-wrap: wrap;
}
.bm-breadcrumb { font-size: 0.85rem; color: #33415c; }
.bm-breadcrumb i { color: #2563eb; margin-right: 6px; }
.bm-list { padding: 10px; overflow-y: auto; flex: 1; }

/* Grid (icon) view */
.bm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(118px, 1fr));
    gap: 10px;
}
.bm-tile {
    position: relative;
    background: #fff;
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 8px;
    text-align: center;
    cursor: pointer;
    user-select: none;
    transition: all .15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,.06);
}
.bm-tile:hover { border-color: #93c5fd; transform: translateY(-2px); }
.bm-tile.selected { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 2px rgba(37,99,235,.25); }
.bm-tile .thumb {
    width: 100%;
    height: 78px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 6px;
}
.bm-tile .thumb img { max-width: 100%; max-height: 78px; object-fit: contain; }
.bm-tile .thumb .doc-icon { font-size: 2rem; color: #f59e0b; }
.bm-tile .fname {
    font-size: 0.72rem;
    color: #1f2937;
    word-break: break-all;
    line-height: 1.2;
    max-height: 2.4em;
    overflow: hidden;
}
.bm-tile .fmeta { font-size: 0.66rem; color: #6b7280; margin-top: 2px; }
.bm-tile .bm-check {
    position: absolute;
    top: 6px;
    left: 6px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}
.bm-tile .bm-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    font-size: 0.6rem;
    padding: 1px 5px;
    border-radius: 8px;
    background: #dcfce7;
    color: #166534;
}
.bm-tile .bm-badge.orphan { background: #fee2e2; color: #991b1b; }

/* List (detail) view */
.bm-table { width: 100%; border-collapse: collapse; background:#fff; font-size:0.82rem; }
.bm-table th, .bm-table td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: middle; }
.bm-table th { background: #eef3fa; position: sticky; top: 0; z-index: 1; font-weight: 600; }
.bm-table tr.selected { background: #eff6ff; }
.bm-table tr:hover { background: #f8fafc; }
.bm-empty { text-align: center; color: #94a3b8; padding: 60px 10px; }
.bm-empty i { font-size: 2.4rem; display:block; margin-bottom: 10px; }

.bm-preview-body { text-align: center; }
.bm-preview-body img { max-width: 100%; max-height: 70vh; border: 1px solid #e5e7eb; border-radius: 6px; }
.bm-detail-list { text-align: left; font-size: 0.85rem; }
.bm-detail-list dt { color: #6b7280; font-weight: 500; }
.bm-detail-list dd { margin-bottom: 8px; word-break: break-all; }
.bm-status-chip {
    display:inline-block; padding:2px 8px; border-radius:10px; font-size:0.72rem; font-weight:600;
}
.bm-s0 { background:#e5e7eb; color:#374151; }
.bm-s1 { background:#dbeafe; color:#1e40af; }
.bm-s2 { background:#dcfce7; color:#166534; }
.bm-s3 { background:#fee2e2; color:#991b1b; }
</style>

<div class="container-fluid mt-3">
    <!-- Toolbar + Filters -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="bm-toolbar">
                <div class="form-group bm-filters">
                    <label class="small mb-0">Tanggal Dari</label>
                    <input type="date" class="form-control form-control-sm" id="bmTanggalDari" style="width:150px;">
                </div>
                <div class="form-group bm-filters">
                    <label class="small mb-0">Tanggal Sampai</label>
                    <input type="date" class="form-control form-control-sm" id="bmTanggalSampai" style="width:150px;">
                </div>
                <div class="form-group bm-filters">
                    <label class="small mb-0">Jam</label>
                    <input type="time" class="form-control form-control-sm" id="bmJam" style="width:120px;">
                </div>
                <div class="form-group bm-filters">
                    <label class="small mb-0">Selesai</label>
                    <select class="form-control form-control-sm" id="bmSelesai" style="width:120px;">
                        <option value="">Semua</option>
                        <option value="0">Aktif</option>
                        <option value="1">Rejected</option>
                        <option value="2">Done</option>
                    </select>
                </div>
                <div class="form-group bm-filters">
                    <label class="small mb-0">Status</label>
                    <select class="form-control form-control-sm" id="bmStatus" style="width:120px;">
                        <option value="">Semua</option>
                        <option value="0">On Target</option>
                        <option value="1">Already</option>
                        <option value="2">Done</option>
                        <option value="3">Fasttrack</option>
                    </select>
                </div>
                <div class="form-group bm-filters">
                    <label class="small mb-0">Cari nama file</label>
                    <input type="text" class="form-control form-control-sm" id="bmSearch" placeholder="nama file..." style="width:180px;">
                </div>
                <div class="form-group bm-filters">
                    <label class="small mb-0 d-block">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-primary" id="bmApply"><i class="fas fa-search"></i> Terapkan</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="bmReset">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Explorer pane -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="bm-pane">
                <div class="bm-pane-header">
                    <div class="bm-breadcrumb">
                        <i class="fas fa-folder-open"></i> assets <span class="text-muted">›</span> uploads <span class="text-muted">›</span> <strong>barcode</strong>
                        <span class="badge bg-secondary ms-2" id="bmCount">0 file</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active" id="bmViewGrid" title="Tampilan ikon"><i class="fas fa-th-large"></i></button>
                            <button type="button" class="btn btn-outline-secondary" id="bmViewList" title="Tampilan detail"><i class="fas fa-list"></i></button>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary" id="bmSelectAll"><i class="fas fa-check-square"></i> Pilih Semua</button>
                            <button type="button" class="btn btn-outline-secondary" id="bmClearSel"><i class="fas fa-times"></i> Bersihkan</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger disabled" id="bmMassDelete" disabled>
                            <i class="fas fa-trash"></i> Hapus Terpilih (<span id="bmSelCount">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="bmMassDeleteFiltered">
                            <i class="fas fa-trash-alt"></i> Hapus Filter Ini
                        </button>
                    </div>
                </div>
                <div class="bm-list" id="bmList">
                    <div class="bm-empty"><i class="fas fa-spinner fa-spin"></i>Memuat data...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="small text-muted mt-2" id="bmStats"></div>
</div>

<!-- Modal konfirmasi mass delete -->
<div class="modal fade" id="bmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Anda akan menghapus <strong id="bmDeleteCount">0</strong> file barcode secara permanen.</p>
                <div class="alert alert-warning small mb-3" style="max-height:180px;overflow:auto;">
                    <ul class="mb-0 ps-3" id="bmDeleteList"></ul>
                </div>
                <p class="small text-muted mb-1">Ketik <strong>HAPUS</strong> untuk konfirmasi:</p>
                <input type="text" class="form-control" id="bmConfirmInput" placeholder="HAPUS" autocomplete="off">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="bmConfirmDelete" disabled><i class="fas fa-trash"></i> Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal preview -->
<div class="modal fade" id="bmPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-image"></i> <span id="bmPreviewTitle">Pratinjau</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7 bm-preview-body" id="bmPreviewImage"></div>
                    <div class="col-md-5">
                        <dl class="bm-detail-list" id="bmPreviewDetail"></dl>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="#" class="btn btn-sm btn-primary" id="bmPreviewDownload" target="_blank"><i class="fas fa-download"></i> Unduh</a>
                            <button type="button" class="btn btn-sm btn-danger" id="bmPreviewDelete"><i class="fas fa-trash"></i> Hapus File</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var BASE = '<?= base_url('barcode-manager') ?>';
    var allFiles = [];
    var selected = new Set();
    var viewMode = 'grid';
    var pendingDelete = [];
    var activePreview = null;

    var listEl = document.getElementById('bmList');
    var countEl = document.getElementById('bmCount');
    var selCountEl = document.getElementById('bmSelCount');
    var statsEl = document.getElementById('bmStats');
    var massDeleteBtn = document.getElementById('bmMassDelete');

    var deleteModalEl = document.getElementById('bmDeleteModal');
    var deleteModal = null;
    var confirmInput = document.getElementById('bmConfirmInput');
    var confirmBtn = document.getElementById('bmConfirmDelete');

    var previewModalEl = document.getElementById('bmPreviewModal');
    var previewModal = null;

    function initModals() {
        if (typeof bootstrap !== 'undefined') {
            deleteModal = new bootstrap.Modal(deleteModalEl);
            previewModal = new bootstrap.Modal(previewModalEl);
        }
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    var STATUS_LABEL = { '0': 'On Target', '1': 'Already', '2': 'Done', '3': 'Fasttrack' };
    var SELESAI_LABEL = { '0': 'Aktif', '1': 'Rejected', '2': 'Done' };

    function statusChip(v) {
        if (v === '' || v === null || typeof v === 'undefined') return '<span class="text-muted">-</span>';
        var label = STATUS_LABEL[String(v)] || v;
        return '<span class="bm-status-chip bm-s' + esc(v) + '">' + esc(label) + '</span>';
    }

    function selesaiChip(v) {
        if (v === '' || v === null || typeof v === 'undefined') return '<span class="text-muted">-</span>';
        var label = SELESAI_LABEL[String(v)] || v;
        return '<span class="bm-status-chip bm-s' + esc(v) + '">' + esc(label) + '</span>';
    }

    function isImage(name) {
        return /\.(jpg|jpeg|png|gif|webp|bmp)$/i.test(name);
    }

    function loadFiles() {
        listEl.innerHTML = '<div class="bm-empty"><i class="fas fa-spinner fa-spin"></i>Memuat data...</div>';
        var params = new URLSearchParams({
            tanggal_dari: document.getElementById('bmTanggalDari').value,
            tanggal_sampai: document.getElementById('bmTanggalSampai').value,
            jam: document.getElementById('bmJam').value,
            selesai: document.getElementById('bmSelesai').value,
            status: document.getElementById('bmStatus').value,
            q: document.getElementById('bmSearch').value
        });
        fetch(BASE + '/list?' + params.toString(), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.success) {
                    listEl.innerHTML = '<div class="bm-empty"><i class="fas fa-exclamation-triangle"></i>Gagal memuat data.</div>';
                    return;
                }
                allFiles = data.files || [];
                // buang seleksi untuk file yang tak lagi tampil
                var visible = new Set(allFiles.map(function (f) { return f.filename; }));
                selected.forEach(function (n) { if (!visible.has(n)) selected.delete(n); });
                countEl.textContent = allFiles.length + ' file';
                statsEl.innerHTML = 'Total file: <strong>' + data.stats.total_files +
                    '</strong> &middot; Terhubung peserta: <strong>' + data.stats.terhubung +
                    '</strong> &middot; Tanpa peserta: <strong>' + data.stats.tanpa_peserta + '</strong>';
                render();
                updateSelectionUI();
            })
            .catch(function () {
                listEl.innerHTML = '<div class="bm-empty"><i class="fas fa-exclamation-triangle"></i>Terjadi kesalahan jaringan.</div>';
            });
    }

    function render() {
        if (!allFiles.length) {
            listEl.innerHTML = '<div class="bm-empty"><i class="fas fa-folder-open"></i>Tidak ada file barcode yang cocok.</div>';
            return;
        }
        if (viewMode === 'grid') renderGrid(); else renderList();
    }

    function renderGrid() {
        var html = '<div class="bm-grid">';
        allFiles.forEach(function (f) {
            var sel = selected.has(f.filename) ? ' selected' : '';
            var thumb = isImage(f.filename)
                ? '<img src="' + esc(f.url) + '" loading="lazy" alt="">'
                : '<i class="fas fa-file-pdf doc-icon"></i>';
            var badge = f.terhubung
                ? '<span class="bm-badge">ok</span>'
                : '<span class="bm-badge orphan">orphan</span>';
            html += '<div class="bm-tile' + sel + '" data-file="' + esc(f.filename) + '">' +
                '<input type="checkbox" class="bm-check" data-file="' + esc(f.filename) + '"' + (sel ? ' checked' : '') + '>' +
                badge +
                '<div class="thumb">' + thumb + '</div>' +
                '<div class="fname" title="' + esc(f.filename) + '">' + esc(f.filename) + '</div>' +
                '<div class="fmeta">' + esc(f.size_human) + '</div>' +
                '</div>';
        });
        html += '</div>';
        listEl.innerHTML = html;
    }

    function renderList() {
        var html = '<table class="bm-table"><thead><tr>' +
            '<th style="width:36px;"><input type="checkbox" id="bmHeadCheck"></th>' +
            '<th>File</th><th>Nama Peserta</th><th>Tanggal</th><th>Jam</th>' +
            '<th>Selesai</th><th>Status</th><th>Ukuran</th><th>Diubah</th><th></th>' +
            '</tr></thead><tbody>';
        allFiles.forEach(function (f) {
            var sel = selected.has(f.filename) ? ' class="selected"' : '';
            html += '<tr' + sel + ' data-file="' + esc(f.filename) + '">' +
                '<td><input type="checkbox" class="bm-check" data-file="' + esc(f.filename) + '"' + (sel ? ' checked' : '') + '></td>' +
                '<td><code class="small">' + esc(f.filename) + '</code></td>' +
                '<td>' + esc(f.nama || '-') + '</td>' +
                '<td>' + esc(f.tanggal || '-') + '</td>' +
                '<td>' + esc(f.jam || '-') + '</td>' +
                '<td>' + selesaiChip(f.selesai) + '</td>' +
                '<td>' + statusChip(f.status) + '</td>' +
                '<td>' + esc(f.size_human) + '</td>' +
                '<td class="small text-muted">' + esc(f.mtime) + '</td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-primary bm-open" data-file="' + esc(f.filename) + '"><i class="fas fa-eye"></i></button></td>' +
                '</tr>';
        });
        html += '</tbody></table>';
        listEl.innerHTML = html;
        var head = document.getElementById('bmHeadCheck');
        if (head) {
            head.checked = allFiles.length > 0 && allFiles.every(function (f) { return selected.has(f.filename); });
        }
    }

    function updateSelectionUI() {
        selCountEl.textContent = selected.size;
        if (selected.size > 0) {
            massDeleteBtn.classList.remove('disabled');
            massDeleteBtn.disabled = false;
        } else {
            massDeleteBtn.classList.add('disabled');
            massDeleteBtn.disabled = true;
        }
    }

    function toggleSelect(filename, on) {
        if (on) selected.add(filename); else selected.delete(filename);
        var nodes = listEl.querySelectorAll('.bm-tile[data-file="' + cssEsc(filename) + '"], tr[data-file="' + cssEsc(filename) + '"]');
        nodes.forEach(function (n) {
            if (on) n.classList.add('selected'); else n.classList.remove('selected');
        });
        var cbs = listEl.querySelectorAll('.bm-check[data-file="' + cssEsc(filename) + '"]');
        cbs.forEach(function (cb) { cb.checked = on; });
        updateSelectionUI();
    }

    function cssEsc(s) {
        return String(s).replace(/["\\]/g, '\\$&');
    }

    // Click handling (delegation)
    listEl.addEventListener('click', function (e) {
        var chk = e.target.closest('.bm-check');
        if (chk) {
            e.stopPropagation();
            toggleSelect(chk.getAttribute('data-file'), chk.checked);
            return;
        }
        var head = e.target.closest('#bmHeadCheck');
        if (head) {
            var on = head.checked;
            allFiles.forEach(function (f) { if (on) selected.add(f.filename); else selected.delete(f.filename); });
            render();
            updateSelectionUI();
            return;
        }
        var openBtn = e.target.closest('.bm-open');
        if (openBtn) {
            e.stopPropagation();
            openPreview(openBtn.getAttribute('data-file'));
            return;
        }
        var tile = e.target.closest('.bm-tile');
        if (tile) {
            // single click = toggle, double click = preview
            openPreview(tile.getAttribute('data-file'));
            return;
        }
        var row = e.target.closest('tr[data-file]');
        if (row) {
            var name = row.getAttribute('data-file');
            toggleSelect(name, !selected.has(name));
        }
    });

    // Double-click opens preview for list rows too
    listEl.addEventListener('dblclick', function (e) {
        var row = e.target.closest('tr[data-file]');
        if (row) openPreview(row.getAttribute('data-file'));
    });

    function openPreview(filename) {
        var f = allFiles.find(function (x) { return x.filename === filename; });
        if (!f) return;
        activePreview = f;
        document.getElementById('bmPreviewTitle').textContent = f.filename;
        var imgBox = document.getElementById('bmPreviewImage');
        imgBox.innerHTML = isImage(f.filename)
            ? '<img src="' + esc(f.view_url) + '" alt="">'
            : '<div class="text-center text-muted py-5"><i class="fas fa-file-pdf" style="font-size:4rem;"></i><p class="mt-2">Pratinjau tidak tersedia. Gunakan tombol Unduh.</p></div>';
        document.getElementById('bmPreviewDetail').innerHTML =
            '<dt>Nama File</dt><dd>' + esc(f.filename) + '</dd>' +
            '<dt>Peserta</dt><dd>' + esc(f.nama || '(tidak terhubung)') + '</dd>' +
            '<dt>Flag Doc</dt><dd>' + esc(f.flag_doc || '-') + '</dd>' +
            '<dt>Nomor Paspor</dt><dd>' + esc(f.nomor_paspor || '-') + '</dd>' +
            '<dt>Tanggal</dt><dd>' + esc(f.tanggal || '-') + '</dd>' +
            '<dt>Jam</dt><dd>' + esc(f.jam || '-') + '</dd>' +
            '<dt>Selesai</dt><dd>' + selesaiChip(f.selesai) + '</dd>' +
            '<dt>Status</dt><dd>' + statusChip(f.status) + '</dd>' +
            '<dt>Ukuran</dt><dd>' + esc(f.size_human) + '</dd>' +
            '<dt>Terakhir diubah</dt><dd>' + esc(f.mtime) + '</dd>';
        document.getElementById('bmPreviewDownload').href = f.download_url;
        if (previewModal) previewModal.show();
    }

    document.getElementById('bmPreviewDelete').addEventListener('click', function () {
        if (!activePreview) return;
        if (previewModal) previewModal.hide();
        askDelete([activePreview.filename]);
    });

    // Filters
    document.getElementById('bmApply').addEventListener('click', loadFiles);
    document.getElementById('bmReset').addEventListener('click', function () {
        document.getElementById('bmTanggalDari').value = '';
        document.getElementById('bmTanggalSampai').value = '';
        document.getElementById('bmJam').value = '';
        document.getElementById('bmSelesai').value = '';
        document.getElementById('bmStatus').value = '';
        document.getElementById('bmSearch').value = '';
        loadFiles();
    });
    document.getElementById('bmSearch').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') loadFiles();
    });

    // View switch
    document.getElementById('bmViewGrid').addEventListener('click', function () {
        viewMode = 'grid';
        this.classList.add('active');
        document.getElementById('bmViewList').classList.remove('active');
        render();
    });
    document.getElementById('bmViewList').addEventListener('click', function () {
        viewMode = 'list';
        this.classList.add('active');
        document.getElementById('bmViewGrid').classList.remove('active');
        render();
    });

    document.getElementById('bmSelectAll').addEventListener('click', function () {
        allFiles.forEach(function (f) { selected.add(f.filename); });
        render();
        updateSelectionUI();
    });
    document.getElementById('bmClearSel').addEventListener('click', function () {
        selected.clear();
        render();
        updateSelectionUI();
    });

    massDeleteBtn.addEventListener('click', function () {
        if (selected.size === 0) return;
        askDelete(Array.from(selected));
    });

    document.getElementById('bmMassDeleteFiltered').addEventListener('click', function () {
        if (!allFiles.length) { alert('Tidak ada file pada filter ini.'); return; }
        askDelete(allFiles.map(function (f) { return f.filename; }));
    });

    function askDelete(files) {
        pendingDelete = files;
        document.getElementById('bmDeleteCount').textContent = files.length;
        var ul = document.getElementById('bmDeleteList');
        var limit = 100;
        var items = files.slice(0, limit).map(function (n) { return '<li>' + esc(n) + '</li>'; });
        if (files.length > limit) items.push('<li>... dan ' + (files.length - limit) + ' file lainnya</li>');
        ul.innerHTML = items.join('');
        confirmInput.value = '';
        confirmBtn.disabled = true;
        if (deleteModal) deleteModal.show();
        setTimeout(function () { confirmInput.focus(); }, 400);
    }

    confirmInput.addEventListener('input', function () {
        confirmBtn.disabled = this.value.trim().toUpperCase() !== 'HAPUS';
    });

    confirmBtn.addEventListener('click', function () {
        var files = pendingDelete.slice();
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
        fetch(BASE + '/mass_delete', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ files: files, confirmation: 'HAPUS' })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                if (deleteModal) deleteModal.hide();
                if (data && data.success) {
                    files.forEach(function (n) { selected.delete(n); });
                    loadFiles();
                    if (data.failed && data.failed.length) {
                        alert(data.message);
                    }
                } else {
                    alert((data && data.message) ? data.message : 'Gagal menghapus.');
                }
            })
            .catch(function () {
                confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                alert('Terjadi kesalahan jaringan.');
            });
    });

    function boot() {
        initModals();
        loadFiles();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
