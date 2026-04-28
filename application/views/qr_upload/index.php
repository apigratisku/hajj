<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-upload mr-2"></i>QR Upload (Auto Scan)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Upload gambar QR untuk langsung discan sistem. Data tidak disimpan ke database (mode percobaan).
                    </p>

                    <form id="qrUploadForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="qr_image">File QR (PNG/JPG/JPEG, max 5MB)</label>
                            <input type="file" class="form-control-file" id="qr_image" name="qr_image" accept=".png,.jpg,.jpeg,image/png,image/jpeg" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="scanBtn">
                            <i class="fas fa-qrcode mr-1"></i> Scan QR
                        </button>
                    </form>

                    <div id="scanAlert" class="alert mt-4 d-none" role="alert"></div>

                    <div class="form-group mt-3">
                        <label for="scanResult">Hasil Raw Scan</label>
                        <textarea id="scanResult" class="form-control" rows="7" readonly placeholder="Hasil scan QR akan tampil di sini..."></textarea>
                    </div>
                    <div id="qrReaderTemp" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function() {
    var form = document.getElementById('qrUploadForm');
    var button = document.getElementById('scanBtn');
    var alertBox = document.getElementById('scanAlert');
    var resultBox = document.getElementById('scanResult');
    var canUseClientDecoder = typeof Html5Qrcode !== 'undefined';
    var MAX_CLIENT_VARIANTS = 36;

    function showAlert(type, message) {
        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertBox.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
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

        return new Promise(function(resolve) {
            canvas.toBlob(function(blob) {
                resolve(new File([blob], cfg.name + '.png', { type: 'image/png' }));
            }, originalType || 'image/png', 1);
        });
    }

    function buildCropConfig(imgW, imgH, areaName, x, y, w, h, targetSize, rotate, gray, highContrast) {
        var safeX = Math.max(0, Math.floor(x));
        var safeY = Math.max(0, Math.floor(y));
        var safeW = Math.max(1, Math.min(Math.floor(w), imgW - safeX));
        var safeH = Math.max(1, Math.min(Math.floor(h), imgH - safeY));
        return {
            name: areaName + '_rot' + rotate + (gray ? '_gray' : '') + (highContrast ? '_hc' : ''),
            sx: safeX,
            sy: safeY,
            sw: safeW,
            sh: safeH,
            width: targetSize,
            height: targetSize,
            gray: gray,
            highContrast: highContrast
        };
    }

    function buildClientVariants(file) {
        return loadImage(file).then(function(img) {
            var w = img.width;
            var h = img.height;
            var targetSize = 900;
            var variants = [];

            // Heuristik area QR dari contoh kartu: area tengah-bawah.
            var areas = [
                { name: 'full', x: 0, y: 0, w: w, h: h },
                { name: 'centerLowerLarge', x: w * 0.18, y: h * 0.50, w: w * 0.64, h: h * 0.42 },
                { name: 'centerLowerMedium', x: w * 0.22, y: h * 0.54, w: w * 0.56, h: h * 0.36 },
                { name: 'centerSquare', x: w * 0.25, y: h * 0.47, w: w * 0.50, h: w * 0.50 }
            ];
            var grayscaleModes = [
                { gray: false, highContrast: false },
                { gray: true, highContrast: false },
                { gray: true, highContrast: true }
            ];
            var rotations = [0, 45, -45, 90, 135, -135, 180, 270];

            areas.forEach(function(area) {
                grayscaleModes.forEach(function(mode) {
                    rotations.forEach(function(rot) {
                        if (variants.length >= MAX_CLIENT_VARIANTS) {
                            return;
                        }
                        var cfg = buildCropConfig(
                            w,
                            h,
                            area.name,
                            area.x,
                            area.y,
                            area.w,
                            area.h,
                            targetSize,
                            rot,
                            mode.gray,
                            mode.highContrast
                        );
                        variants.push({ cfg: cfg, rotate: rot });
                    });
                });
            });

            var makePromises = variants.map(function(item) {
                // Rotate dilakukan di canvas kedua agar kualitas crop tetap baik.
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

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        var fileInput = document.getElementById('qr_image');
        if (!fileInput.files || !fileInput.files.length) {
            showAlert('error', 'Silakan pilih file QR terlebih dahulu.');
            return;
        }

        var file = fileInput.files[0];
        var formData = new FormData(form);
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
        resultBox.value = '';
        alertBox.classList.add('d-none');

        function finishLoading() {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-qrcode mr-1"></i> Scan QR';
        }

        function serverDecode() {
            fetch('<?= base_url('qr-upload/scan') ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.status) {
                    resultBox.value = data.raw_text || '';
                    showAlert('success', (data.message || 'QR berhasil discan.') + ' (' + (data.decoder_path || 'server') + ')');
                    return;
                }

                showAlert('error', data.message || 'Gagal scan QR.');
            })
            .catch(function() {
                showAlert('error', 'Terjadi kesalahan saat menghubungi server.');
            })
            .finally(function() {
                finishLoading();
            });
        }

        if (!canUseClientDecoder) {
            serverDecode();
            return;
        }

        buildClientVariants(file)
            .then(function(variants) {
                return scanClientVariants(variants).catch(function() {
                    return scanWithNativeBarcodeDetector(variants);
                });
            })
            .then(function(result) {
                resultBox.value = result.text || '';
                showAlert('success', 'QR berhasil discan. (client/' + result.label + ')');
                finishLoading();
            })
            .catch(function() {
                // Fallback ke server decoder (khanamiryan) jika semua variasi client gagal
                serverDecode();
            });
    });
})();
</script>
