<?php
$qr_list = isset($qr_list) && is_array($qr_list) ? $qr_list : array();
$filters = isset($filters) && is_array($filters) ? $filters : array();
$filter_booking = isset($filters['booking_id']) ? (string) $filters['booking_id'] : '';
$filter_barcode = isset($filters['barcode_data']) ? (string) $filters['barcode_data'] : '';
$filter_tanggaljam = isset($filters['tanggaljam']) ? (string) $filters['tanggaljam'] : '';
$current_page = isset($current_page) ? max(1, (int) $current_page) : 1;
$total_pages = isset($total_pages) ? max(1, (int) $total_pages) : 1;
$total_rows = isset($total_rows) ? max(0, (int) $total_rows) : count($qr_list);
$per_page = isset($per_page) ? max(1, (int) $per_page) : 10;
$offset = isset($offset) ? max(0, (int) $offset) : (($current_page - 1) * $per_page);

$last_date_value = '09 Apr 2026';
$last_time_value = '16:00';
if (!empty($qr_list)) {
    $first_row = $qr_list[0];
    $first_date = isset($first_row->ticket_date) ? (string) $first_row->ticket_date : (isset($first_row->tanggal) ? (string) $first_row->tanggal : '');
    $first_time = isset($first_row->ticket_time) ? (string) $first_row->ticket_time : (isset($first_row->waktu) ? (string) $first_row->waktu : '');
    if (trim($first_date) !== '') {
        $last_date_value = trim($first_date);
    }
    if (trim($first_time) !== '') {
        $last_time_value = trim($first_time);
    }
}
$tanggaljam_options = array();
foreach ($qr_list as $opt_row) {
    $opt_date = isset($opt_row->ticket_date) ? (string) $opt_row->ticket_date : (isset($opt_row->tanggal) ? (string) $opt_row->tanggal : '');
    $opt_time = isset($opt_row->ticket_time) ? (string) $opt_row->ticket_time : (isset($opt_row->waktu) ? (string) $opt_row->waktu : '');
    $opt_val = trim($opt_date . ' ' . $opt_time);
    if ($opt_val !== '') {
        $tanggaljam_options[$opt_val] = $opt_val;
    }
}
$pagination_query = array();
if ($filter_booking !== '') {
    $pagination_query['booking_id'] = $filter_booking;
}
if ($filter_barcode !== '') {
    $pagination_query['barcode_data'] = $filter_barcode;
}
if ($filter_tanggaljam !== '') {
    $pagination_query['tanggaljam'] = $filter_tanggaljam;
}
?>
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-database mr-2"></i>QR Data</h5>
                </div>
                <div class="card-body">
                   
                    <div class="form-group">
                        <label>Scan dengan kamera HP</label>
                        <div id="qrCameraRegion" class="border rounded bg-dark mx-auto overflow-hidden qr-camera-box"></div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-primary btn-sm" id="btnCameraStart">
                                <i class="fas fa-video mr-1"></i> Aktifkan kamera (scanner)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCameraStop" disabled>
                                <i class="fas fa-stop mr-1"></i> Matikan kamera
                            </button>
                        </div>
                        <small id="cameraStatus" class="form-text text-muted mt-2">Status kamera: belum aktif.</small>
                    </div>

                    <div id="scanAlert" class="alert mt-3 d-none" role="alert"></div>

                    <div class="form-row mt-4">
                        <div class="form-group col-md-6">
                            <label for="dateInput">Tanggal (tampilan tiket)</label>
                            <input type="text" class="form-control" id="dateInput" placeholder="contoh: 09 Apr 2026" value="<?= html_escape($last_date_value) ?>" readonly>
                            <input type="date" class="d-none" id="datePickerNative">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="timeInput">Waktu (tampilan tiket)</label>
                            <input type="text" class="form-control" id="timeInput" placeholder="contoh: 16:00" value="<?= html_escape($last_time_value) ?>" readonly>
                            <input type="time" class="d-none" id="timePickerNative" step="60">
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <label for="bookingInput">Booking ID</label>
                        <input type="text" class="form-control" id="bookingInput" readonly>
                    </div>
                    <div class="form-group">
                        <label for="barcodeInput">Barcode Data</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="barcodeInput" autocomplete="off" placeholder="Terisi otomatis dari hasil scan kamera">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="btnPasteBarcode">Paste</button>
                                <button type="button" class="btn btn-outline-danger" id="btnClearBarcode">Clear</button>
                            </div>
                        </div>
                    </div>

                    <div class="btn-group flex-wrap mb-2" role="group">
                        <button type="button" class="btn btn-primary" id="btnSaveDb">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                        
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Pratinjau tiket</h6>
                </div>
                <div class="card-body">
                    <div class="qr-pass-tool">
                        <div class="qr-pass-tool-inner">
                            <div class="phone" id="captureArea">
                                <!-- <div class="top-gradient"></div>

                                <div class="status-bar">
                                    <div>19.09</div>
                                    <div>◌ ⌁ 📶 🔋</div>
                                </div>

                                <div class="header">
                                    <div>×</div>
                                    <div>You</div>
                                </div> -->
                                <div class="ticket-card">
                                    <div class="date" id="ticketDate">09 Apr 2026</div>
                                    <div class="time" id="ticketTime">16:00</div>
                                    <div class="divider"></div>
                                    <div class="notch-left"></div>
                                    <div class="notch-right"></div>
                                    <div class="qr-wrap">
                                        <div id="qrCode"></div>
                                    </div>
                                    <div class="ref" id="ticketRef">1145360165</div>
                                    <div class="avatar">س</div>
                                    <div class="you">You</div>
                                    <div class="notice">Please stay close. Your visit time is approaching.</div>
                                    <div class="cancel">Cancel booking</div>
                                </div>
                                <div class="warning">⚠ Please proceed now.</div>
                                <div class="gate-btn">➜ Directions to the Gate</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-0">Data tersimpan</h6>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('qr-data') ?>">
                    <div class="form-row align-items-end">
                        
                        <div class="form-group col-md-4">
                            <label for="filterTanggalJam">Filter Tanggal Jam</label>
                            <select class="form-control form-control-sm" id="filterTanggalJam" name="tanggaljam">
                                <option value="">Semua TanggalJam</option>
                                <?php foreach ($tanggaljam_options as $opt): ?>
                                <option value="<?= html_escape($opt) ?>" <?= $filter_tanggaljam === $opt ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-2"><i class="fas fa-search mr-1"></i>Cari</button>
                            <a href="<?= base_url('qr-data') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="card-body p-0 pt-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered mb-0" id="qrDataTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Booking ID</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Disimpan</th>
                                    <th style="width:210px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($qr_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada data.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($qr_list as $idx => $row): ?>
                                <?php
                                    $row_booking = isset($row->booking_id) ? (string) $row->booking_id : '';
                                    $row_barcode = isset($row->barcode_data) ? (string) $row->barcode_data : '';
                                    $row_date = isset($row->ticket_date) ? (string) $row->ticket_date : (isset($row->tanggal) ? (string) $row->tanggal : '');
                                    $row_time = isset($row->ticket_time) ? (string) $row->ticket_time : (isset($row->waktu) ? (string) $row->waktu : '');
                                ?>
                                <tr class="qr-data-row"
                                    data-booking="<?= htmlspecialchars($row_booking, ENT_QUOTES, 'UTF-8') ?>"
                                    data-barcode="<?= htmlspecialchars($row_barcode, ENT_QUOTES, 'UTF-8') ?>"
                                    data-date="<?= htmlspecialchars($row_date, ENT_QUOTES, 'UTF-8') ?>"
                                    data-time="<?= htmlspecialchars($row_time, ENT_QUOTES, 'UTF-8') ?>">
                                    <td><?= (int) ($offset + $idx + 1) ?></td>
                                    <td><code><?= html_escape($row_booking) ?></code></td>
                                    
                                    <td><?= html_escape($row_date) ?></td>
                                    <td><?= html_escape($row_time) ?></td>
                                    <td class="small"><?= html_escape(isset($row->created_at) ? $row->created_at : '') ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-success btn-download-row"
                                            data-barcode="<?= htmlspecialchars($row_barcode, ENT_QUOTES, 'UTF-8') ?>"
                                            data-date="<?= htmlspecialchars($row_date, ENT_QUOTES, 'UTF-8') ?>"
                                            data-time="<?= htmlspecialchars($row_time, ENT_QUOTES, 'UTF-8') ?>"
                                            data-booking="<?= htmlspecialchars($row_booking, ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fas fa-download"></i> QR
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-row" data-id="<?= (int) $row->id ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <small class="text-muted mb-2 mb-md-0">
                            Menampilkan <?= (int) count($qr_list) ?> dari <?= (int) $total_rows ?> data
                        </small>
                        <nav aria-label="QR data pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                    $prev_page = $current_page > 1 ? $current_page - 1 : 1;
                                    $next_page = $current_page < $total_pages ? $current_page + 1 : $total_pages;
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $current_page + 2);
                                    $prev_query = $pagination_query;
                                    $prev_query['page'] = $prev_page;
                                    $next_query = $pagination_query;
                                    $next_query['page'] = $next_page;
                                ?>
                                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $current_page <= 1 ? '#' : (base_url('qr-data') . '?' . http_build_query($prev_query)) ?>">Prev</a>
                                </li>
                                <?php for ($page_num = $start_page; $page_num <= $end_page; $page_num++): ?>
                                <?php $page_query = $pagination_query; $page_query['page'] = $page_num; ?>
                                <li class="page-item <?= $page_num === $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= base_url('qr-data') . '?' . http_build_query($page_query) ?>"><?= (int) $page_num ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $current_page >= $total_pages ? '#' : (base_url('qr-data') . '?' . http_build_query($next_query)) ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="qrCaptureOffscreenHost" aria-hidden="true">
    <div class="phone" id="captureAreaOffscreen">
        <!-- <div class="top-gradient"></div>

        <div class="status-bar">
            <div>19.09</div>
            <div>◌ ⌁ 📶 🔋</div>
        </div>

        <div class="header">
            <div>×</div>
            <div>You</div>
        </div> -->
        <div class="ticket-card">
            <div class="date" id="ticketDateOffscreen">09 Apr 2026</div>
            <div class="time" id="ticketTimeOffscreen">16:00</div>
            <div class="divider"></div>
            <div class="notch-left"></div>
            <div class="notch-right"></div>
            <div class="qr-wrap">
                <div id="qrCodeOffscreen"></div>
            </div>
            <div class="ref" id="ticketRefOffscreen">1145360165</div>
            <div class="avatar">س</div>
            <div class="you">You</div>
            <div class="notice">Please stay close. Your visit time is approaching.</div>
            <div class="cancel">Cancel booking</div>
        </div>
        <div class="warning">⚠ Please proceed now.</div>
        <div class="gate-btn">➜ Directions to the Gate</div>
    </div>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
.qr-camera-box { width:100%; max-width:400px; min-height:260px; }
.qr-pass-tool { background:#efefef; padding:16px; border-radius:8px; overflow:auto; }
.qr-pass-tool-inner { display:flex; justify-content:center; }
.qr-pass-tool * { box-sizing:border-box; }
.qr-pass-tool .phone{
  width:350px;
  height:700px;
  background:#efefef;
  position:relative;
  overflow:hidden;
  font-family:Arial,sans-serif;
}
.qr-pass-tool .ticket-card{
  position:absolute;
  top:60px;
  left:4px;
  right:4px;
  height:420px;
  background:#fff;
  border-radius:8px;
  box-shadow:0 1px 1px rgba(0,0,0,.04);
}
/* Isolate ticket rendering from global/admin CSS overrides. */
.qr-pass-tool .phone,
.qr-pass-tool .ticket-card,
.qr-pass-tool .date,
.qr-pass-tool .time,
.qr-pass-tool .ref,
.qr-pass-tool .you,
.qr-pass-tool .notice,
.qr-pass-tool .cancel,
.qr-pass-tool .warning,
.qr-pass-tool .gate-btn,
.qr-pass-tool .avatar {
  margin:0;
  line-height:normal;
  letter-spacing:normal;
}
.qr-pass-tool .divider{
  position:absolute;
  left:0;
  right:0;
  top:150px;
  border-top:2px dashed #e2e2e2;
  z-index:1;
}
.qr-pass-tool .notch-left,
.qr-pass-tool .notch-right{
  position:absolute;
  top:142px;
  width:8px;
  height:16px;
  background:#efefef;
  border-radius:50%;
  z-index:2;
}
.qr-pass-tool .notch-left{ left:-4px; }
.qr-pass-tool .notch-right{ right:-4px; }
.qr-pass-tool .date{
  padding-top:20px;
  text-align:center;
  font-size:18px;
  color:#222;
}
.qr-pass-tool .time{
  margin-top:4px;
  text-align:center;
  font-size:16px;
  color:#666;
}
.qr-pass-tool .qr-wrap{
  position:relative;
  z-index:3;
  margin:34px auto 0;
  width:115px;
  height:115px;
  background:#f2f2f2;
  border-radius:4px;
  padding:8px;
  display:flex;
  justify-content:center;
  align-items:center;
}
.qr-pass-tool .ref{
  margin-top:30px;
  text-align:center;
  font-size:16px;
  color:#333;
}
.qr-pass-tool .avatar{
  margin:18px auto 8px;
  width:42px;
  height:42px;
  border-radius:50%;
  background:#efefef;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#444;
  font-size:24px;
}
.qr-pass-tool .you{
  text-align:center;
  font-size:13px;
  color:#555;
  margin-top:35px;
}
.qr-pass-tool .notice{
  text-align:center;
  margin-top:15px;
  font-size:12px;
  color:#555;
}
.qr-pass-tool .cancel{
  text-align:center;
  margin-top:35px;
  font-size:12px;
  color:#d27b70;
  text-decoration:underline;
}
.qr-pass-tool .warning{
  position:absolute;
  bottom:72px;
  left:0;
  right:0;
  text-align:center;
  font-size:11px;
  color:#666;
}
.qr-pass-tool .gate-btn{
  position:absolute;
  bottom:12px;
  left:20px;
  right:20px;
  height:28px;
  background:#979797;
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#eee;
  font-size:12px;
}
.text-break { word-break: break-all; }

#qrCaptureOffscreenHost {
  position: fixed;
  left: -99999px;
  top: 0;
  width: 350px;
  height: 700px;
  overflow: hidden;
  opacity: 0;
  pointer-events: none;
}

#qrCaptureOffscreenHost,
#qrCaptureOffscreenHost * {
  box-sizing: border-box;
  margin: 0;
  line-height: normal;
  letter-spacing: normal;
}

#qrCaptureOffscreenHost .phone{
  width:350px;
  height:700px;
  background:#efefef;
  position:relative;
  overflow:hidden;
  font-family:Arial,sans-serif;
}
#qrCaptureOffscreenHost .ticket-card{
  position:absolute;
  top:60px;
  left:4px;
  right:4px;
  height:420px;
  background:#fff;
  border-radius:8px;
  box-shadow:0 1px 1px rgba(0,0,0,.04);
}
#qrCaptureOffscreenHost .divider{
  position:absolute;
  left:0;
  right:0;
  top:150px;
  border-top:2px dashed #e2e2e2;
  z-index:1;
}
#qrCaptureOffscreenHost .notch-left,
#qrCaptureOffscreenHost .notch-right{
  position:absolute;
  top:142px;
  width:8px;
  height:16px;
  background:#efefef;
  border-radius:50%;
  z-index:2;
}
#qrCaptureOffscreenHost .notch-left{ left:-4px; }
#qrCaptureOffscreenHost .notch-right{ right:-4px; }
#qrCaptureOffscreenHost .date{
  padding-top:20px;
  text-align:center;
  font-size:18px;
  color:#222;
}
#qrCaptureOffscreenHost .time{
  margin-top:4px;
  text-align:center;
  font-size:16px;
  color:#666;
}
#qrCaptureOffscreenHost .qr-wrap{
  position:relative;
  z-index:3;
  margin:34px auto 0;
  width:115px;
  height:115px;
  background:#f2f2f2;
  border-radius:4px;
  padding:8px;
  display:flex;
  justify-content:center;
  align-items:center;
}
#qrCaptureOffscreenHost .ref{
  margin-top:30px;
  text-align:center;
  font-size:16px;
  color:#333;
}
#qrCaptureOffscreenHost .avatar{
  margin:18px auto 8px;
  width:42px;
  height:42px;
  border-radius:50%;
  background:#efefef;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#444;
  font-size:24px;
}
#qrCaptureOffscreenHost .you{
  text-align:center;
  font-size:13px;
  color:#555;
  margin-top:35px;
}
#qrCaptureOffscreenHost .notice{
  text-align:center;
  margin-top:15px;
  font-size:12px;
  color:#555;
}
#qrCaptureOffscreenHost .cancel{
  text-align:center;
  margin-top:35px;
  font-size:12px;
  color:#d27b70;
  text-decoration:underline;
}
#qrCaptureOffscreenHost .warning{
  position:absolute;
  bottom:72px;
  left:0;
  right:0;
  text-align:center;
  font-size:11px;
  color:#666;
}
#qrCaptureOffscreenHost .gate-btn{
  position:absolute;
  bottom:12px;
  left:20px;
  right:20px;
  height:28px;
  background:#979797;
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#eee;
  font-size:12px;
}
</style>

<script>
(function() {
    var alertBox = document.getElementById('scanAlert');
    var btnCameraStart = document.getElementById('btnCameraStart');
    var btnCameraStop = document.getElementById('btnCameraStop');
    var html5QrCodeCamera = null;
    var lastDecodedCam = '';
    var canUseClientDecoder = typeof Html5Qrcode !== 'undefined';
    var isAutoSaving = false;
    var isCameraStarting = false;
    var lastScanAt = 0;
    var lastSavedBarcode = '';
    var decodeFailCount = 0;
    var decodeFailWindowStart = 0;
    var cameraStatusEl = document.getElementById('cameraStatus');

    var bookingInput = document.getElementById('bookingInput');
    var barcodeInput = document.getElementById('barcodeInput');
    var btnPasteBarcode = document.getElementById('btnPasteBarcode');
    var btnClearBarcode = document.getElementById('btnClearBarcode');
    var dateInput = document.getElementById('dateInput');
    var timeInput = document.getElementById('timeInput');
    var datePickerNative = document.getElementById('datePickerNative');
    var timePickerNative = document.getElementById('timePickerNative');
    var ticketRefEl = document.getElementById('ticketRef');
    var ticketDateEl = document.getElementById('ticketDate');
    var ticketTimeEl = document.getElementById('ticketTime');
    var qrCodeEl = document.getElementById('qrCode');
    var captureAreaEl = document.getElementById('captureArea');
    var ticketRefOffscreenEl = document.getElementById('ticketRefOffscreen');
    var ticketDateOffscreenEl = document.getElementById('ticketDateOffscreen');
    var ticketTimeOffscreenEl = document.getElementById('ticketTimeOffscreen');
    var qrCodeOffscreenEl = document.getElementById('qrCodeOffscreen');
    var captureAreaOffscreenEl = document.getElementById('captureAreaOffscreen');

    function setCameraStatus(message) {
        if (cameraStatusEl) {
            cameraStatusEl.textContent = 'Status kamera: ' + message;
        }
    }

    function showAlert(type, message) {
        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        if (type === 'success') {
            alertBox.classList.add('alert-success');
        } else if (type === 'warning') {
            alertBox.classList.add('alert-warning');
        } else {
            alertBox.classList.add('alert-danger');
        }
        alertBox.textContent = message;
    }

    function pad2(num) {
        return num < 10 ? ('0' + num) : String(num);
    }

    function formatDateDisplayFromIso(isoDate) {
        if (!isoDate || isoDate.indexOf('-') === -1) {
            return '';
        }
        var parts = isoDate.split('-');
        if (parts.length !== 3) {
            return '';
        }
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        var year = parts[0];
        var monthIdx = parseInt(parts[1], 10) - 1;
        var day = parseInt(parts[2], 10);
        if (monthIdx < 0 || monthIdx > 11 || !day) {
            return '';
        }
        return pad2(day) + ' ' + months[monthIdx] + ' ' + year;
    }

    function toIsoDateFromDisplay(displayDate) {
        if (!displayDate) {
            return '';
        }
        var text = displayDate.trim();
        var match = text.match(/^(\d{1,2})\s+([A-Za-z]{3})\s+(\d{4})$/);
        if (!match) {
            return '';
        }
        var day = parseInt(match[1], 10);
        var mon = match[2].toLowerCase();
        var year = match[3];
        var map = {
            jan: '01', feb: '02', mar: '03', apr: '04', mei: '05', may: '05', jun: '06',
            jul: '07', agu: '08', aug: '08', sep: '09', okt: '10', oct: '10', nov: '11', des: '12', dec: '12'
        };
        if (!map[mon] || !day) {
            return '';
        }
        return year + '-' + map[mon] + '-' + pad2(day);
    }

    function normalizeTimeDisplay(value) {
        if (!value) {
            return '';
        }
        var m = value.trim().match(/^(\d{1,2}):(\d{2})$/);
        if (!m) {
            return '';
        }
        var hh = parseInt(m[1], 10);
        var mm = parseInt(m[2], 10);
        if (hh < 0 || hh > 23 || mm < 0 || mm > 59) {
            return '';
        }
        return pad2(hh) + ':' + pad2(mm);
    }

    function getCameraStartErrorMessage(errorObj) {
        var errText = '';
        var errName = '';
        if (errorObj) {
            errText = (errorObj.message || errorObj.toString() || '').toString();
            errName = (errorObj.name || '').toString();
        }
        var joined = (errName + ' ' + errText).toLowerCase();
        if (joined.indexOf('notallowederror') !== -1 || joined.indexOf('permission denied') !== -1) {
            return 'Izin kamera ditolak. Buka izin kamera browser lalu coba lagi.';
        }
        if (joined.indexOf('notfounderror') !== -1 || joined.indexOf('devicesnotfounderror') !== -1) {
            return 'Kamera tidak ditemukan di perangkat ini.';
        }
        if (joined.indexOf('notreadableerror') !== -1 || joined.indexOf('trackstarterror') !== -1) {
            return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi lain lalu coba lagi.';
        }
        if (joined.indexOf('overconstrainederror') !== -1 || joined.indexOf('constraint') !== -1) {
            return 'Pengaturan kamera tidak didukung perangkat. Coba mulai ulang kamera.';
        }
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            return 'Kamera membutuhkan HTTPS atau localhost.';
        }
        return 'Gagal memulai kamera. Pastikan izin kamera aktif dan koneksi aman (HTTPS).';
    }

    function getCameraErrorDebug(errorObj) {
        if (!errorObj) {
            return '';
        }
        var name = (errorObj.name || '').toString().trim();
        var message = (errorObj.message || errorObj.toString() || '').toString().trim();
        if (!name && !message) {
            return '';
        }
        return ' [detail: ' + (name || 'Error') + (message ? ' - ' + message : '') + ']';
    }

    function safeStopCamera() {
        if (!html5QrCodeCamera) {
            return Promise.resolve();
        }
        return html5QrCodeCamera.stop()
            .catch(function() { return null; })
            .then(function() {
                try {
                    html5QrCodeCamera.clear();
                } catch (e1) {}
                html5QrCodeCamera = null;
                btnCameraStart.disabled = false;
                btnCameraStop.disabled = true;
                setCameraStatus('berhenti.');
            });
    }

    function saveQrData(autoMode) {
        if (isAutoSaving) {
            return Promise.resolve(false);
        }
        if (!barcodeInput.value.trim()) {
            showAlert('error', 'Isi barcode dari scan kamera terlebih dahulu.');
            return Promise.resolve(false);
        }
        isAutoSaving = true;
        var btnDb = document.getElementById('btnSaveDb');
        btnDb.disabled = true;
        var fd = new FormData();
        fd.append('booking_id', bookingInput.value || '');
        fd.append('barcode_data', barcodeInput.value.trim());
        fd.append('ticket_date', dateInput.value || '');
        fd.append('ticket_time', timeInput.value || '');
        return fetch('<?= base_url('qr-data/save') ?>', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data && data.success) {
                    lastSavedBarcode = barcodeInput.value.trim();
                    showAlert('success', data.message || 'Tersimpan.');
                    if (autoMode) {
                        setCameraStatus('scan berhasil, data tersimpan otomatis.');
                        return safeStopCamera().then(function() {
                            window.location.reload();
                            return true;
                        });
                    }
                    window.location.reload();
                    return true;
                }
                showAlert('error', (data && data.message) ? data.message : 'Gagal menyimpan.');
                return false;
            })
            .catch(function() {
                showAlert('error', 'Gagal menghubungi server.');
                return false;
            })
            .finally(function() {
                isAutoSaving = false;
                btnDb.disabled = false;
            });
    }

    function generateQR(data) {
        if (!qrCodeEl) {
            return;
        }
        qrCodeEl.innerHTML = '';
        new QRCode(qrCodeEl, {
            text: data,
            width: 100,
            height: 100,
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    function generateQRTo(containerEl, data) {
        if (!containerEl) {
            return false;
        }
        containerEl.innerHTML = '';
        new QRCode(containerEl, {
            text: data,
            width: 100,
            height: 100,
            correctLevel: QRCode.CorrectLevel.M
        });
        return true;
    }

    function updateTicket() {
        var barcodeValue = barcodeInput.value || '';
        var bookingId = barcodeValue.substring(0, 10);
        bookingInput.value = bookingId;
        if (ticketRefEl) {
            ticketRefEl.textContent = bookingId || '—';
        }
        if (ticketDateEl) {
            ticketDateEl.textContent = dateInput.value || '';
        }
        if (ticketTimeEl) {
            ticketTimeEl.textContent = timeInput.value || '';
        }
        generateQR(barcodeValue);
    }

    function saveCaptureLikeQrIndex() {
        if (!barcodeInput.value.trim()) {
            showAlert('error', 'Belum ada data barcode untuk diunduh.');
            return;
        }
        var barcodeValue = barcodeInput.value || '';
        var bookingValue = bookingInput.value || barcodeValue;
        var bookingId = bookingInput.value || 'ticket';
        var frame = document.createElement('iframe');
        frame.setAttribute('aria-hidden', 'true');
        frame.style.position = 'fixed';
        frame.style.left = '-10000px';
        frame.style.top = '0';
        frame.style.width = '380px';
        frame.style.height = '760px';
        frame.style.opacity = '0';
        frame.style.pointerEvents = 'none';
        frame.style.border = '0';
        document.body.appendChild(frame);

        var frameDoc = frame.contentDocument || (frame.contentWindow ? frame.contentWindow.document : null);
        if (!frameDoc || !frame.contentWindow) {
            try { document.body.removeChild(frame); } catch (e0) {}
            showAlert('error', 'Renderer PNG tidak siap.');
            return;
        }

        frameDoc.open();
        frameDoc.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' +
            '*{box-sizing:border-box;margin:0;padding:0;}' +
            'body{background:#efefef;font-family:Arial,sans-serif;}' +
            '.phone{width:350px;height:700px;background:#efefef;position:relative;overflow:hidden;}' +
            '.card{position:absolute;top:60px;left:4px;right:4px;height:420px;background:#fff;border-radius:8px;box-shadow:0 1px 1px rgba(0,0,0,.04);}' +
            '.divider{position:absolute;left:0;right:0;top:150px;border-top:2px dashed #e2e2e2;z-index:1;}' +
            '.notch-left,.notch-right{position:absolute;top:142px;width:8px;height:16px;background:#efefef;border-radius:50%;z-index:2;}' +
            '.notch-left{left:-4px;}.notch-right{right:-4px;}' +
            '.date{padding-top:20px;text-align:center;font-size:18px;color:#222;}' +
            '.time{margin-top:4px;text-align:center;font-size:16px;color:#666;}' +
            '.qr-wrap{position:relative;z-index:3;margin:34px auto 0;width:115px;height:115px;background:#f2f2f2;border-radius:4px;padding:8px;display:flex;justify-content:center;align-items:center;}' +
            '.ref{margin-top:30px;text-align:center;font-size:16px;color:#333;}' +
            '.avatar{margin:18px auto 8px;width:42px;height:42px;border-radius:50%;background:#efefef;display:flex;align-items:center;justify-content:center;color:#444;font-size:24px;}' +
            '.you{text-align:center;font-size:13px;color:#555;margin-top:35px;}' +
            '.notice{text-align:center;margin-top:15px;font-size:12px;color:#555;}' +
            '.cancel{text-align:center;margin-top:35px;font-size:12px;color:#d27b70;text-decoration:underline;}' +
            '.warning{position:absolute;bottom:72px;left:0;right:0;text-align:center;font-size:11px;color:#666;}' +
            '.gate-btn{position:absolute;bottom:12px;left:20px;right:20px;height:28px;background:#979797;border-radius:16px;display:flex;align-items:center;justify-content:center;color:#eee;font-size:12px;}' +
            '</style></head><body>' +
            '<div class="phone" id="captureAreaRef">' +
            '<div class="card">' +
            '<div class="date" id="ticketDateRef"></div>' +
            '<div class="time" id="ticketTimeRef"></div>' +
            '<div class="divider"></div><div class="notch-left"></div><div class="notch-right"></div>' +
            '<div class="qr-wrap"><div id="qrCodeRef"></div></div>' +
            '<div class="ref" id="ticketRefRef"></div>' +
            '<div class="avatar">س</div><div class="you">You</div>' +
            '<div class="notice">Please stay close. Your visit time is approaching.</div>' +
            '<div class="cancel">Cancel booking</div>' +
            '</div>' +
            '<div class="warning">⚠ Please proceed now.</div>' +
            '<div class="gate-btn">➜ Directions to the Gate</div>' +
            '</div></body></html>');
        frameDoc.close();

        var refDate = frameDoc.getElementById('ticketDateRef');
        var refTime = frameDoc.getElementById('ticketTimeRef');
        var refBooking = frameDoc.getElementById('ticketRefRef');
        var refQr = frameDoc.getElementById('qrCodeRef');
        var refCapture = frameDoc.getElementById('captureAreaRef');
        if (!refDate || !refTime || !refBooking || !refQr || !refCapture) {
            try { document.body.removeChild(frame); } catch (e1) {}
            showAlert('error', 'Template PNG gagal dibuat.');
            return;
        }

        refDate.textContent = dateInput.value || '';
        refTime.textContent = timeInput.value || '';
        refBooking.textContent = bookingValue || '—';
        new QRCode(refQr, {
            text: barcodeValue,
            width: 100,
            height: 100,
            correctLevel: QRCode.CorrectLevel.M
        });

        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                html2canvas(refCapture, { scale: 2 }).then(function(canvas) {
                    var link = document.createElement('a');
                    link.download = bookingId + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    try { document.body.removeChild(frame); } catch (e2) {}
                }).catch(function() {
                    try { document.body.removeChild(frame); } catch (e3) {}
                    showAlert('error', 'Gagal membuat PNG dari template referensi.');
                });
            });
        });
    }

    function applyRowToFormThenDownload(barcode, dateStr, timeStr) {
        barcodeInput.value = barcode || '';
        dateInput.value = dateStr || '';
        timeInput.value = timeStr || '';
        updateTicket();
        saveCaptureLikeQrIndex();
    }

    btnCameraStart.addEventListener('click', function() {
        if (!canUseClientDecoder) {
            showAlert('error', 'Pemindaian kamera tidak didukung di browser ini.');
            return;
        }
        if (isCameraStarting) {
            showAlert('warning', 'Kamera sedang dipersiapkan...');
            return;
        }
        if (html5QrCodeCamera) {
            showAlert('warning', 'Kamera sudah aktif.');
            return;
        }
        isCameraStarting = true;
        setCameraStatus('menyiapkan kamera...');
        html5QrCodeCamera = new Html5Qrcode('qrCameraRegion');
        var supportedFormats = [];
        if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
            supportedFormats = [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.ITF,
                Html5QrcodeSupportedFormats.CODABAR
            ];
        }
        var camConfig = {
            fps: 10,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                var edge = Math.floor(0.62 * Math.min(viewfinderWidth, viewfinderHeight));
                if (edge < 180) {
                    edge = 180;
                }
                if (edge > 320) {
                    edge = 320;
                }
                return { width: edge, height: edge };
            },
            aspectRatio: 1.3333333,
            formatsToSupport: supportedFormats
        };
        var onCamSuccess = function(decodedText) {
            if (!decodedText) {
                return;
            }
            var now = Date.now();
            if ((now - lastScanAt) < 1000) {
                return;
            }
            if (decodedText === lastDecodedCam) {
                return;
            }
            if (decodedText === lastSavedBarcode) {
                return;
            }
            if (isAutoSaving) {
                return;
            }
            lastScanAt = now;
            lastDecodedCam = decodedText;
            barcodeInput.value = decodedText;
            updateTicket();
            setCameraStatus('kode terbaca, menyimpan otomatis...');
            showAlert('warning', 'Kode terbaca, menyimpan otomatis...');
            saveQrData(true);
        };
        var onCamErr = function() {
            var now = Date.now();
            if (decodeFailWindowStart === 0 || (now - decodeFailWindowStart) > 7000) {
                decodeFailWindowStart = now;
                decodeFailCount = 0;
            }
            decodeFailCount++;
            if (decodeFailCount === 16) {
                showAlert('warning', 'Kamera aktif, tetapi kode belum terbaca. Dekatkan kamera, tambah cahaya, dan tahan ponsel agar stabil.');
            }
        };
        var cameraCandidates = [];
        var pushUniqueCameraCandidate = function(label, value) {
            if (!value) {
                return;
            }
            var i = 0;
            for (i = 0; i < cameraCandidates.length; i++) {
                if (cameraCandidates[i].value === value) {
                    return;
                }
            }
            cameraCandidates.push({ label: label, value: value });
        };
        var fallbackConstraintRear = {
            facingMode: 'environment',
            width: { ideal: 1280 },
            height: { ideal: 720 }
        };
        var fallbackConstraintFront = {
            facingMode: 'user',
            width: { ideal: 1280 },
            height: { ideal: 720 }
        };

        pushUniqueCameraCandidate('kamera belakang', fallbackConstraintRear);
        if (Html5Qrcode.getCameras) {
            Html5Qrcode.getCameras().then(function(cameras) {
                var rearFound = null;
                var i = 0;
                for (i = 0; i < cameras.length; i++) {
                    var cam = cameras[i] || {};
                    var label = (cam.label || '').toLowerCase();
                    if (label.indexOf('back') !== -1 || label.indexOf('rear') !== -1 || label.indexOf('environment') !== -1) {
                        rearFound = cam.id;
                        break;
                    }
                }
                if (rearFound) {
                    cameraCandidates.unshift({ label: 'kamera belakang (device)', value: rearFound });
                }
                if (cameras.length > 0) {
                    pushUniqueCameraCandidate('kamera default', cameras[0].id);
                }
                pushUniqueCameraCandidate('kamera depan', fallbackConstraintFront);
                return cameraCandidates;
            }).catch(function() {
                pushUniqueCameraCandidate('kamera depan', fallbackConstraintFront);
                return cameraCandidates;
            }).then(function() {
                var startOneByOne = function(index, firstError) {
                    if (index >= cameraCandidates.length) {
                        return Promise.reject(firstError || new Error('No camera candidate'));
                    }
                    var candidate = cameraCandidates[index];
                    return html5QrCodeCamera.start(candidate.value, camConfig, onCamSuccess, onCamErr)
                        .then(function() {
                            btnCameraStart.disabled = true;
                            btnCameraStop.disabled = false;
                            var statusMsg = 'aktif (' + candidate.label + ').';
                            setCameraStatus(statusMsg);
                            showAlert('success', 'Scanner aktif (' + candidate.label + '). Arahkan kamera ke QR/Barcode.');
                            return true;
                        })
                        .catch(function(errCandidate) {
                            return startOneByOne(index + 1, firstError || errCandidate);
                        });
                };
                return startOneByOne(0, null);
            })
            .catch(function(errFinal) {
                try {
                    if (html5QrCodeCamera) {
                        html5QrCodeCamera.clear();
                    }
                } catch (e2) {}
                html5QrCodeCamera = null;
                btnCameraStart.disabled = false;
                btnCameraStop.disabled = true;
                isCameraStarting = false;
                setCameraStatus('gagal aktif.');
                showAlert('error', getCameraStartErrorMessage(errFinal) + getCameraErrorDebug(errFinal));
            })
            .finally(function() {
                isCameraStarting = false;
            });
            return;
        }
        pushUniqueCameraCandidate('kamera depan', fallbackConstraintFront);
        html5QrCodeCamera.start(cameraCandidates[0].value, camConfig, onCamSuccess, onCamErr)
            .then(function() {
                btnCameraStart.disabled = true;
                btnCameraStop.disabled = false;
                setCameraStatus('aktif (kamera belakang).');
                showAlert('success', 'Scanner aktif. Arahkan kamera ke QR/Barcode.');
            })
            .catch(function(errNoEnum) {
                html5QrCodeCamera = null;
                btnCameraStart.disabled = false;
                btnCameraStop.disabled = true;
                setCameraStatus('gagal aktif.');
                showAlert('error', getCameraStartErrorMessage(errNoEnum) + getCameraErrorDebug(errNoEnum));
            })
            .finally(function() {
                isCameraStarting = false;
            });
    });

    btnCameraStop.addEventListener('click', function() {
        if (!html5QrCodeCamera) {
            return;
        }
        safeStopCamera().then(function() {
            lastDecodedCam = '';
            lastSavedBarcode = '';
        }).catch(function() {
            html5QrCodeCamera = null;
            btnCameraStart.disabled = false;
            btnCameraStop.disabled = true;
            setCameraStatus('berhenti.');
        });
    });

    document.getElementById('btnSaveDb').addEventListener('click', function() {
        saveQrData(false);
    });

    var btnDownloadQr = document.getElementById('btnDownloadQr');
    if (btnDownloadQr) {
        btnDownloadQr.addEventListener('click', saveCaptureLikeQrIndex);
    }

    var qrDataTable = document.getElementById('qrDataTable');
    if (qrDataTable) {
        qrDataTable.addEventListener('click', function(event) {
            var btn = event.target.closest('.btn-download-row');
            if (btn) {
                event.preventDefault();
                var b = (btn.getAttribute('data-barcode') || '').trim();
                var d = (btn.getAttribute('data-date') || '').trim();
                var t = (btn.getAttribute('data-time') || '').trim();
                if (!b) {
                    showAlert('error', 'Barcode kosong.');
                    return;
                }
                applyRowToFormThenDownload(b, d, t);
                return;
            }

            var deleteBtn = event.target.closest('.btn-delete-row');
            if (!deleteBtn) {
                return;
            }
            event.preventDefault();
            var id = parseInt(deleteBtn.getAttribute('data-id'), 10);
            if (!id || id < 1) {
                showAlert('error', 'ID data tidak valid.');
                return;
            }
            if (!confirm('Yakin ingin menghapus data ini secara permanen?')) {
                return;
            }
            deleteBtn.disabled = true;
            fetch('<?= base_url('qr-data/delete/') ?>' + id, {
                method: 'POST',
                credentials: 'same-origin'
            })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data && data.success) {
                        window.location.reload();
                        return;
                    }
                    showAlert('error', (data && data.message) ? data.message : 'Gagal menghapus data.');
                })
                .catch(function() {
                    showAlert('error', 'Gagal menghubungi server saat menghapus data.');
                })
                .finally(function() {
                    deleteBtn.disabled = false;
                });
        });
    }

    barcodeInput.addEventListener('input', updateTicket);
    dateInput.addEventListener('click', function() {
        if (!datePickerNative) {
            return;
        }
        var isoVal = toIsoDateFromDisplay(dateInput.value);
        if (isoVal) {
            datePickerNative.value = isoVal;
        }
        if (datePickerNative.showPicker) {
            datePickerNative.showPicker();
            return;
        }
        datePickerNative.focus();
        datePickerNative.click();
    });
    timeInput.addEventListener('click', function() {
        if (!timePickerNative) {
            return;
        }
        var norm = normalizeTimeDisplay(timeInput.value);
        if (norm) {
            timePickerNative.value = norm;
        }
        if (timePickerNative.showPicker) {
            timePickerNative.showPicker();
            return;
        }
        timePickerNative.focus();
        timePickerNative.click();
    });
    if (datePickerNative) {
        datePickerNative.addEventListener('change', function() {
            var displayDate = formatDateDisplayFromIso(datePickerNative.value);
            if (displayDate) {
                dateInput.value = displayDate;
                updateTicket();
            }
        });
    }
    if (timePickerNative) {
        timePickerNative.addEventListener('change', function() {
            var normTime = normalizeTimeDisplay(timePickerNative.value);
            if (normTime) {
                timeInput.value = normTime;
                updateTicket();
            }
        });
    }

    if (btnPasteBarcode) {
        btnPasteBarcode.addEventListener('click', function() {
            if (!navigator.clipboard || !navigator.clipboard.readText) {
                showAlert('error', 'Clipboard tidak didukung di browser ini.');
                return;
            }
            navigator.clipboard.readText().then(function(text) {
                var val = (text || '').trim();
                if (!val) {
                    showAlert('warning', 'Clipboard kosong.');
                    return;
                }
                barcodeInput.value = val;
                updateTicket();
                showAlert('success', 'Barcode berhasil di-paste.');
            }).catch(function() {
                showAlert('error', 'Gagal mengakses clipboard.');
            });
        });
    }
    if (btnClearBarcode) {
        btnClearBarcode.addEventListener('click', function() {
            barcodeInput.value = '';
            updateTicket();
            showAlert('warning', 'Barcode dibersihkan.');
        });
    }

    var initialIsoDate = toIsoDateFromDisplay(dateInput.value);
    if (datePickerNative && initialIsoDate) {
        datePickerNative.value = initialIsoDate;
    }
    var initialTime = normalizeTimeDisplay(timeInput.value);
    if (timePickerNative && initialTime) {
        timePickerNative.value = initialTime;
    }

    updateTicket();
})();
</script>
