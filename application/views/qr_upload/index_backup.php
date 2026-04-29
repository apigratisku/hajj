<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-upload mr-2"></i>QR Upload &amp; Booking Pass</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Unggah satu atau banyak gambar QR (PNG/JPG/JPEG, maks 5MB per file). Isi <strong>Tanggal</strong> dan <strong>Waktu</strong> jadwal terlebih dahulu, lalu scan. Booking ID dan Barcode Data terisi otomatis dari hasil scan (pilih baris untuk pratinjau tiket).
                    </p>

                    <form id="qrUploadForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="qr_image">File QR (boleh banyak sekaligus)</label>
                            <input type="file" class="form-control-file" id="qr_image" name="qr_image[]" accept=".png,.jpg,.jpeg,image/png,image/jpeg" multiple required>
                            <div id="dropZone" class="qr-dropzone mt-2" role="button" tabindex="0" aria-label="Drag and drop file barcode">
                                <div><strong>Drag & drop file barcode di sini</strong></div>
                                <div class="small text-muted">Atau klik area ini untuk pilih file. Anda bisa menambah file berkali-kali.</div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <small id="selectedFilesInfo" class="text-muted">Belum ada file dipilih.</small>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFilesBtn">Clear</button>
                            </div>
                            <div id="selectedFilesList" class="small text-muted mt-1"></div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="scanBtn">
                            <i class="fas fa-qrcode mr-1"></i> Scan QR
                        </button>
                    </form>

                    <div id="scanAlert" class="alert mt-3 d-none" role="alert"></div>

                    <div class="form-row mt-4">
                        <div class="form-group col-md-6">
                            <label for="dateInput">Tanggal (tampilan tiket)</label>
                            <input type="text" class="form-control" id="dateInput" placeholder="contoh: 09 Apr 2026" value="09 Apr 2026">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="timeInput">Waktu (tampilan tiket)</label>
                            <input type="text" class="form-control" id="timeInput" placeholder="contoh: 16:00" value="16:00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="scanResult">Hasil raw scan (semua file)</label>
                        <textarea id="scanResult" class="form-control" rows="6" readonly placeholder="Hasil scan tiap file akan tampil di sini..."></textarea>
                    </div>

                    <h6 class="mt-3">Hasil per file</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="scanRowsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>File</th>
                                    <th>Status</th>
                                    <th>Booking ID</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="scanRowsBody"></tbody>
                        </table>
                    </div>

                    <div class="form-group mt-3">
                        <label for="bookingInput">Booking ID (otomatis dari barcode)</label>
                        <input type="text" class="form-control" id="bookingInput" readonly>
                    </div>
                    <div class="form-group">
                        <label for="barcodeInput">Barcode Data (dari scan / dapat diedit)</label>
                        <input type="text" class="form-control" id="barcodeInput" autocomplete="off">
                    </div>

                    <div class="btn-group flex-wrap mb-3" role="group">
                        <button type="button" class="btn btn-success" id="btnSaveCapture">
                            <i class="fas fa-camera mr-1"></i> Simpan Capture PNG (tiket terpilih)
                        </button>
                        <button type="button" class="btn btn-outline-success" id="btnSaveAllPng">
                            <i class="fas fa-images mr-1"></i> Simpan semua PNG (mass)
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
                                <div class="ticket-card">
                                    <div class="date" id="ticketDate">09 Apr 2026</div>
                                    <div class="time" id="ticketTime">16:00</div>
                                    <div class="divider"></div>
                                    <div class="notch-left"></div>
                                    <div class="notch-right"></div>
                                    <div class="qr-wrap">
                                        <div id="qrCode"></div>
                                    </div>
                                    <div class="ref" id="ticketRef">—</div>
                                    <div class="avatar">س</div>
                                    <div class="you">You</div>
                                    <div class="notice">Please stay close. Your visit time is approaching.</div>
                                    <div class="cancel">Cancel booking</div>
                                </div>
                                <div class="warning">Please proceed now.</div>
                                <div class="gate-btn">Directions to the Gate</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="qrReaderTemp" style="display:none;"></div>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
.qr-pass-tool { background:#efefef; padding:16px; border-radius:8px; overflow:auto; }
.qr-pass-tool-inner { display:flex; justify-content:center; }
.qr-pass-tool * { box-sizing:border-box; }
.qr-pass-tool .phone{
  width:350px;
  min-height:700px;
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
.qr-dropzone{
  border:2px dashed #9aa5b1;
  border-radius:8px;
  background:#f8fafc;
  padding:14px;
  text-align:center;
  cursor:pointer;
  transition:all .15s ease-in-out;
}
.qr-dropzone.dragover{
  border-color:#2f80ed;
  background:#eef6ff;
}
</style>

<script>
(function() {
    var form = document.getElementById('qrUploadForm');
    var button = document.getElementById('scanBtn');
    var alertBox = document.getElementById('scanAlert');
    var resultBox = document.getElementById('scanResult');
    var scanRowsBody = document.getElementById('scanRowsBody');
    var fileInput = document.getElementById('qr_image');
    var dropZone = document.getElementById('dropZone');
    var selectedFilesInfo = document.getElementById('selectedFilesInfo');
    var selectedFilesList = document.getElementById('selectedFilesList');
    var clearFilesBtn = document.getElementById('clearFilesBtn');
    var canUseClientDecoder = typeof Html5Qrcode !== 'undefined';
    var MAX_CLIENT_VARIANTS = 36;

    var bookingInput = document.getElementById('bookingInput');
    var barcodeInput = document.getElementById('barcodeInput');
    var dateInput = document.getElementById('dateInput');
    var timeInput = document.getElementById('timeInput');

    var scanRowsList = [];
    var selectedRowIndex = -1;
    var qrInstance = null;
    var selectedFiles = [];

    function fileKey(file) {
        return [file.name, file.size, file.lastModified].join('|');
    }

    function syncNativeInputFiles() {
        if (typeof DataTransfer === 'undefined') {
            return;
        }
        var dt = new DataTransfer();
        selectedFiles.forEach(function(file) {
            dt.items.add(file);
        });
        fileInput.files = dt.files;
    }

    function renderSelectedFiles() {
        if (!selectedFiles.length) {
            selectedFilesInfo.textContent = 'Belum ada file dipilih.';
            selectedFilesList.textContent = '';
            return;
        }
        selectedFilesInfo.textContent = selectedFiles.length + ' file siap discan.';
        selectedFilesList.textContent = selectedFiles.map(function(f) { return f.name; }).join(', ');
    }

    function addSelectedFiles(fileList) {
        var map = {};
        selectedFiles.forEach(function(f) {
            map[fileKey(f)] = true;
        });
        Array.prototype.forEach.call(fileList || [], function(file) {
            if (!file || !file.type || file.type.indexOf('image/') !== 0) {
                return;
            }
            var key = fileKey(file);
            if (!map[key]) {
                selectedFiles.push(file);
                map[key] = true;
            }
        });
        syncNativeInputFiles();
        renderSelectedFiles();
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

    function loadImage(file) {
        return new Promise(function(resolve, reject) {
            var reader = new FileReader();
            reader.onload = function() {
                var image = new Image();
                image.onload = function() {
                    resolve(image);
                };
                image.onerror = reject;
                image.src = reader.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    function makeVariantFile(image, cfg, originalType) {
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        canvas.width = cfg.width;
        canvas.height = cfg.height;

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(
            image,
            cfg.sx, cfg.sy, cfg.sw, cfg.sh,
            0, 0, cfg.width, cfg.height
        );

        if (cfg.gray) {
            var imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            var data = imgData.data;
            for (var i = 0; i < data.length; i += 4) {
                var luminance = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                if (cfg.highContrast) {
                    luminance = luminance > 140 ? 255 : 0;
                }
                data[i] = luminance;
                data[i + 1] = luminance;
                data[i + 2] = luminance;
            }
            ctx.putImageData(imgData, 0, 0);
        }

        if (cfg.invert) {
            var invData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            var d = invData.data;
            for (var j = 0; j < d.length; j += 4) {
                d[j] = 255 - d[j];
                d[j + 1] = 255 - d[j + 1];
                d[j + 2] = 255 - d[j + 2];
            }
            ctx.putImageData(invData, 0, 0);
        }

        return new Promise(function(resolve) {
            canvas.toBlob(function(blob) {
                resolve(new File([blob], cfg.name + '.png', { type: 'image/png' }));
            }, originalType || 'image/png', 1);
        });
    }

    function buildCropConfig(imgW, imgH, areaName, x, y, w, h, targetSize, rotate, gray, highContrast, invert) {
        var safeX = Math.max(0, Math.floor(x));
        var safeY = Math.max(0, Math.floor(y));
        var safeW = Math.max(1, Math.min(Math.floor(w), imgW - safeX));
        var safeH = Math.max(1, Math.min(Math.floor(h), imgH - safeY));
        return {
            name: areaName + '_rot' + rotate + (gray ? '_gray' : '') + (highContrast ? '_hc' : '') + (invert ? '_inv' : ''),
            sx: safeX,
            sy: safeY,
            sw: safeW,
            sh: safeH,
            width: targetSize,
            height: targetSize,
            gray: gray,
            highContrast: highContrast,
            invert: !!invert
        };
    }

    function buildClientVariants(file) {
        return loadImage(file).then(function(img) {
            var w = img.width;
            var h = img.height;
            var targetSize = Math.min(900, Math.max(240, Math.round(Math.max(w, h) * 2)));
            var variants = [];

            var rotationsPriority = [0, 45, -45, 90, 180, 270];
            var modes = [
                { gray: false, highContrast: false },
                { gray: true, highContrast: false },
                { gray: true, highContrast: true }
            ];
            var fullArea = { name: 'full', x: 0, y: 0, w: w, h: h };
            var secondaryAreas = [
                { name: 'centerLowerLarge', x: w * 0.18, y: h * 0.50, w: w * 0.64, h: h * 0.42 },
                { name: 'centerLowerMedium', x: w * 0.22, y: h * 0.54, w: w * 0.56, h: h * 0.36 },
                { name: 'centerSquare', x: w * 0.25, y: h * 0.47, w: w * 0.50, h: w * 0.50 }
            ];

            var inv, ri, mi, ai;
            outer: for (inv = 0; inv < 2; inv++) {
                var invert = inv === 1;
                for (ri = 0; ri < rotationsPriority.length; ri++) {
                    for (mi = 0; mi < modes.length; mi++) {
                        if (variants.length >= MAX_CLIENT_VARIANTS) {
                            break outer;
                        }
                        var rot = rotationsPriority[ri];
                        var mode = modes[mi];
                        variants.push({
                            cfg: buildCropConfig(
                                w,
                                h,
                                fullArea.name,
                                fullArea.x,
                                fullArea.y,
                                fullArea.w,
                                fullArea.h,
                                targetSize,
                                rot,
                                mode.gray,
                                mode.highContrast,
                                invert
                            ),
                            rotate: rot
                        });
                    }
                }
            }

            for (ai = 0; ai < secondaryAreas.length; ai++) {
                if (variants.length >= MAX_CLIENT_VARIANTS) {
                    break;
                }
                var area = secondaryAreas[ai];
                for (inv = 0; inv < 2; inv++) {
                    if (variants.length >= MAX_CLIENT_VARIANTS) {
                        break;
                    }
                    var invertSec = inv === 1;
                    for (ri = 0; ri < rotationsPriority.length; ri++) {
                        if (variants.length >= MAX_CLIENT_VARIANTS) {
                            break;
                        }
                        var rotSec = rotationsPriority[ri];
                        for (mi = 0; mi < modes.length; mi++) {
                            if (variants.length >= MAX_CLIENT_VARIANTS) {
                                break;
                            }
                            var modeSec = modes[mi];
                            variants.push({
                                cfg: buildCropConfig(
                                    w,
                                    h,
                                    area.name,
                                    area.x,
                                    area.y,
                                    area.w,
                                    area.h,
                                    targetSize,
                                    rotSec,
                                    modeSec.gray,
                                    modeSec.highContrast,
                                    invertSec
                                ),
                                rotate: rotSec
                            });
                        }
                    }
                }
            }

            var makePromises = variants.map(function(item) {
                return makeVariantFile(img, item.cfg, file.type).then(function(fileVariant) {
                    return { file: fileVariant, label: item.cfg.name, rotate: item.rotate };
                });
            });

            return Promise.all(makePromises).then(function(basicVariants) {
                var rotatedPromises = basicVariants.map(function(variant) {
                    if (variant.rotate === 0) {
                        return Promise.resolve(variant);
                    }

                    return loadImage(variant.file).then(function(rotImg) {
                        var canvas = document.createElement('canvas');
                        var ctx = canvas.getContext('2d');
                        canvas.width = rotImg.width;
                        canvas.height = rotImg.height;
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.translate(canvas.width / 2, canvas.height / 2);
                        ctx.rotate((variant.rotate * Math.PI) / 180);
                        ctx.drawImage(rotImg, -rotImg.width / 2, -rotImg.height / 2);
                        return new Promise(function(resolve) {
                            canvas.toBlob(function(blob) {
                                resolve({
                                    file: new File([blob], variant.label + '.png', { type: 'image/png' }),
                                    label: variant.label
                                });
                            }, 'image/png', 1);
                        });
                    });
                });

                return Promise.all(rotatedPromises);
            });
        });
    }

    function scanWithNativeBarcodeDetector(variants) {
        if (typeof BarcodeDetector === 'undefined') {
            return Promise.reject(new Error('barcode_detector_not_available'));
        }

        var detector;
        try {
            detector = new BarcodeDetector({ formats: ['qr_code'] });
        } catch (error) {
            return Promise.reject(error);
        }

        var index = 0;

        function next(resolve, reject) {
            if (index >= variants.length) {
                reject(new Error('native_detector_all_failed'));
                return;
            }

            var current = variants[index];
            index += 1;

            loadImage(current.file)
                .then(function(image) {
                    return detector.detect(image);
                })
                .then(function(codes) {
                    if (codes && codes.length > 0 && codes[0].rawValue) {
                        resolve({
                            text: codes[0].rawValue,
                            label: current.label + '_native'
                        });
                        return;
                    }
                    next(resolve, reject);
                })
                .catch(function() {
                    next(resolve, reject);
                });
        }

        return new Promise(function(resolve, reject) {
            next(resolve, reject);
        });
    }

    function scanClientVariants(variants) {
        var qr = new Html5Qrcode('qrReaderTemp');
        var index = 0;

        function cleanup() {
            try {
                qr.clear();
            } catch (err) {}
        }

        function tryNext(resolve, reject) {
            if (index >= variants.length) {
                cleanup();
                reject(new Error('all_variants_failed'));
                return;
            }

            var current = variants[index];
            index += 1;

            qr.scanFile(current.file, true)
                .then(function(text) {
                    cleanup();
                    resolve({
                        text: text,
                        label: current.label
                    });
                })
                .catch(function() {
                    tryNext(resolve, reject);
                });
        }

        return new Promise(function(resolve, reject) {
            tryNext(resolve, reject);
        });
    }

    function generateQR(data) {
        var qrContainer = document.getElementById('qrCode');
        qrContainer.innerHTML = '';
        qrInstance = new QRCode(qrContainer, {
            text: data || ' ',
            width: 100,
            height: 100,
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    function updateTicket() {
        var barcodeValue = barcodeInput.value || '';
        var bookingId = barcodeValue.length >= 10 ? barcodeValue.substring(0, 10) : barcodeValue;
        bookingInput.value = bookingId;
        document.getElementById('ticketRef').textContent = bookingId || '—';
        document.getElementById('ticketDate').textContent = dateInput.value || '';
        document.getElementById('ticketTime').textContent = timeInput.value || '';
        generateQR(barcodeValue.trim() ? barcodeValue : ' ');
    }

    function applyScanResponse(data) {
        scanRowsList = [];
        scanRowsBody.innerHTML = '';
        selectedRowIndex = -1;

        if (!data.results || !data.results.length) {
            resultBox.value = '';
            bookingInput.value = '';
            barcodeInput.value = '';
            updateTicket();
            return;
        }

        var lines = [];
        data.results.forEach(function(r, idx) {
            if (r.ok && r.raw_text) {
                scanRowsList.push({
                    name: r.name,
                    raw_text: r.raw_text,
                    bookingId: r.raw_text.substring(0, Math.min(10, r.raw_text.length))
                });
                lines.push(r.name + ': ' + r.raw_text);
            } else {
                lines.push(r.name + ': (gagal) ' + (r.message || ''));
            }
        });
        resultBox.value = lines.join('\n');

        var okRowCounter = 0;
        data.results.forEach(function(r, idx) {
            var tr = document.createElement('tr');
            if (r.ok) {
                var previewIdx = okRowCounter;
                okRowCounter += 1;
                tr.innerHTML =
                    '<td>' + (idx + 1) + '</td>' +
                    '<td>' + escapeHtml(r.name) + '</td>' +
                    '<td><span class="badge badge-success">OK</span></td>' +
                    '<td><code>' + escapeHtml(r.raw_text.substring(0, Math.min(10, r.raw_text.length))) + '</code></td>' +
                    '<td><button type="button" class="btn btn-sm btn-outline-primary btn-preview-row" data-idx="' + previewIdx + '">Pratinjau</button></td>';
            } else {
                tr.innerHTML =
                    '<td>' + (idx + 1) + '</td>' +
                    '<td>' + escapeHtml(r.name) + '</td>' +
                    '<td><span class="badge badge-danger">Gagal</span></td>' +
                    '<td>—</td>' +
                    '<td>—</td>';
            }
            scanRowsBody.appendChild(tr);
        });

        scanRowsBody.querySelectorAll('.btn-preview-row').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var ix = parseInt(btn.getAttribute('data-idx'), 10);
                if (!isNaN(ix) && ix >= 0 && ix < scanRowsList.length) {
                    selectRow(ix);
                }
            });
        });

        if (scanRowsList.length > 0) {
            selectRow(0);
        } else {
            bookingInput.value = '';
            barcodeInput.value = '';
            updateTicket();
        }
    }

    function escapeHtml(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function selectRow(ix) {
        selectedRowIndex = ix;
        var row = scanRowsList[ix];
        if (!row) return;
        barcodeInput.value = row.raw_text;
        updateTicket();
    }

    function serverDecodeFromFormData(formData) {
        return fetch('<?= base_url('qr-upload/scan') ?>', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.results && data.results.length) {
                applyScanResponse(data);
                if (data.status) {
                    showAlert('success', data.message || 'Scan selesai.');
                } else {
                    showAlert('error', data.message || 'Semua file gagal discan.');
                }
                return;
            }
            if (data.status && data.raw_text) {
                applyScanResponse({
                    results: [{ name: 'upload', ok: true, raw_text: data.raw_text, decoder_path: data.decoder_path || '' }]
                });
                showAlert('success', data.message || 'OK');
                return;
            }
            showAlert('error', data.message || 'Gagal scan QR.');
        })
        .catch(function() {
            showAlert('error', 'Terjadi kesalahan saat menghubungi server.');
        });
    }

    function finishLoading() {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-qrcode mr-1"></i> Scan QR';
    }

    fileInput.addEventListener('change', function() {
        addSelectedFiles(fileInput.files);
    });

    clearFilesBtn.addEventListener('click', function() {
        selectedFiles = [];
        fileInput.value = '';
        syncNativeInputFiles();
        renderSelectedFiles();
    });

    dropZone.addEventListener('click', function() {
        fileInput.click();
    });
    dropZone.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput.click();
        }
    });
    ['dragenter', 'dragover'].forEach(function(evt) {
        dropZone.addEventListener(evt, function(event) {
            event.preventDefault();
            event.stopPropagation();
            dropZone.classList.add('dragover');
        });
    });
    ['dragleave', 'drop'].forEach(function(evt) {
        dropZone.addEventListener(evt, function(event) {
            event.preventDefault();
            event.stopPropagation();
            dropZone.classList.remove('dragover');
        });
    });
    dropZone.addEventListener('drop', function(event) {
        var files = event.dataTransfer ? event.dataTransfer.files : null;
        addSelectedFiles(files);
    });

    barcodeInput.addEventListener('input', updateTicket);
    dateInput.addEventListener('input', updateTicket);
    timeInput.addEventListener('input', updateTicket);

    document.getElementById('btnSaveCapture').addEventListener('click', function() {
        if (!barcodeInput.value.trim()) {
            showAlert('error', 'Belum ada data barcode untuk disimpan.');
            return;
        }
        var bookingId = bookingInput.value || 'ticket';
        html2canvas(document.getElementById('captureArea'), { scale: 2 }).then(function(canvas) {
            var link = document.createElement('a');
            link.download = bookingId + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            showAlert('success', 'PNG berhasil diunduh: ' + link.download);
        });
    });

    document.getElementById('btnSaveAllPng').addEventListener('click', function() {
        if (!scanRowsList.length) {
            showAlert('error', 'Tidak ada hasil scan yang sukses. Scan dulu.');
            return;
        }
        var btnAll = document.getElementById('btnSaveAllPng');
        btnAll.disabled = true;
        var i = 0;

        function nextSave() {
            if (i >= scanRowsList.length) {
                btnAll.disabled = false;
                showAlert('success', 'Selesai mengunduh ' + scanRowsList.length + ' file PNG.');
                return;
            }
            var row = scanRowsList[i];
            barcodeInput.value = row.raw_text;
            updateTicket();
            setTimeout(function() {
                html2canvas(document.getElementById('captureArea'), { scale: 2 }).then(function(canvas) {
                    var link = document.createElement('a');
                    link.download = row.bookingId + '_' + (i + 1) + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    i += 1;
                    setTimeout(nextSave, 450);
                });
            }, 200);
        }
        nextSave();
    });

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        if (!selectedFiles.length) {
            showAlert('error', 'Silakan pilih file QR terlebih dahulu.');
            return;
        }

        var files = selectedFiles.slice();
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
        resultBox.value = '';
        scanRowsBody.innerHTML = '';
        alertBox.classList.add('d-none');

        var formDataMulti = new FormData();
        for (var fi = 0; fi < files.length; fi++) {
            formDataMulti.append('qr_image[]', files[fi]);
        }

        if (files.length > 1 || !canUseClientDecoder) {
            serverDecodeFromFormData(formDataMulti).finally(finishLoading);
            return;
        }

        var file = files[0];
        loadImage(file)
            .then(function() {
                return buildClientVariants(file)
                    .then(function(variants) {
                        return scanClientVariants(variants).catch(function() {
                            return scanWithNativeBarcodeDetector(variants);
                        });
                    })
                    .then(function(result) {
                        applyScanResponse({
                            results: [{ name: file.name, ok: true, raw_text: result.text || '', decoder_path: 'client/' + (result.label || '') }]
                        });
                        showAlert('success', 'QR berhasil discan. (client)');
                    })
                    .catch(function() {
                        return serverDecodeFromFormData(formDataMulti);
                    });
            })
            .catch(function() {
                showAlert('error', 'Tidak dapat membaca file gambar.');
            })
            .finally(finishLoading);
    });

    updateTicket();
    renderSelectedFiles();
})();
</script>
