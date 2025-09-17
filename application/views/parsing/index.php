<!-- Content Body -->
<div class="content-body">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-file-pdf text-primary me-2"></i>
                        Parsing Data VISA
                    </h4>
                    <p class="text-muted mb-0">Upload dan parse file PDF VISA untuk mengekstrak data otomatis</p>
                </div>
                <div>
                    <a href="<?= base_url('parsing/view_data') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Lihat Data
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-gradient rounded-circle p-3">
                                        <i class="fas fa-database text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Total Data</div>
                                    <div class="h4 mb-0 text-primary"><?= number_format(isset($stats['total_records']) ? $stats['total_records'] : 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success bg-gradient rounded-circle p-3">
                                        <i class="fas fa-calendar-day text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Hari Ini</div>
                                    <div class="h4 mb-0 text-success"><?= number_format(isset($stats['today_records']) ? $stats['today_records'] : 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info bg-gradient rounded-circle p-3">
                                        <i class="fas fa-calendar-alt text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Bulan Ini</div>
                                    <div class="h4 mb-0 text-info"><?= number_format(isset($stats['month_records']) ? $stats['month_records'] : 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning bg-gradient rounded-circle p-3">
                                        <i class="fas fa-passport text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">Paspor Unik</div>
                                    <div class="h4 mb-0 text-warning"><?= number_format(isset($stats['unique_passports']) ? $stats['unique_passports'] : 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-upload me-2"></i>
                                Upload File PDF VISA
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Upload Form -->
                            <form id="parsingForm" enctype="multipart/form-data">
                                <div class="upload-area" id="uploadArea">
                                    <div class="upload-content text-center">
                                        <div class="upload-icon mb-3">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                                        </div>
                                        <h5>Drag & Drop File PDF</h5>
                                        <p class="text-muted">atau <span class="text-primary fw-bold">klik untuk memilih file</span></p>
                                        <input type="file" id="pdfFile" name="pdf" accept=".pdf" class="d-none" required>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Format yang didukung: PDF (maksimal 100MB)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- File Info -->
                                <div id="fileInfo" class="mt-3 d-none">
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="fas fa-file-pdf me-2"></i>
                                        <div class="flex-grow-1">
                                            <strong id="fileName"></strong>
                                            <div class="small text-muted">
                                                Ukuran: <span id="fileSize"></span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-end mt-4">
                                    <button type="submit" id="parseBtn" class="btn btn-primary btn-lg" disabled>
                                        <i class="fas fa-cogs me-2"></i>
                                        Parse Data VISA
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Info Panel -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi Parsing
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="text-primary">Format yang Didukung:</h6>
                                <ul class="list-unstyled small">
                                    <li><i class="fas fa-check text-success me-2"></i>VISA bilingual (Arab/Inggris)</li>
                                    <li><i class="fas fa-check text-success me-2"></i>VISA format standar</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Machine Readable Zone (MRZ)</li>
                                </ul>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="text-primary">Data yang Diekstrak:</h6>
                                <ul class="list-unstyled small">
                                    <li><i class="fas fa-user text-primary me-2"></i>Nama lengkap</li>
                                    <li><i class="fas fa-passport text-primary me-2"></i>Nomor paspor</li>
                                    <li><i class="fas fa-id-card text-primary me-2"></i>Nomor VISA</li>
                                    <li><i class="fas fa-birthday-cake text-primary me-2"></i>Tanggal lahir</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning small">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Tips:</strong> Pastikan file PDF tidak terproteksi dan dapat dibaca untuk hasil terbaik.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div id="resultsSection" class="mt-4 d-none">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            Hasil Parsing
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="parsingResults"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="progressModalLabel">
                    <i class="fas fa-cogs text-primary me-2"></i>
                    Memproses File PDF
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <h6>Sedang memproses file PDF...</h6>
                <p class="text-muted small mb-0">Mohon tunggu, proses parsing mungkin memakan waktu beberapa detik.</p>
                <div class="progress mt-3" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 3rem 2rem;
    transition: all 0.3s ease;
    cursor: pointer;
    background-color: #f8f9fa;
}

.upload-area:hover {
    border-color: #007bff;
    background-color: #f0f8ff;
}

.upload-area.dragover {
    border-color: #007bff;
    background-color: #e3f2fd;
    transform: scale(1.02);
}

.upload-content {
    pointer-events: none;
}

.card {
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.bg-gradient {
    background: linear-gradient(45deg, var(--bs-primary), var(--bs-primary-dark));
}

.spinner-border {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.table-responsive {
    border-radius: 10px;
    overflow: hidden;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}

.alert {
    border: none;
    border-radius: 10px;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
}

/* Modal accessibility fixes */
.modal {
    z-index: 1055;
}

.modal-content {
    outline: none;
}

.modal-content:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* Ensure modal backdrop doesn't interfere with focus */
.modal-backdrop {
    z-index: 1050;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('pdfFile');
    const fileInfo = document.getElementById('fileInfo');
    const parseBtn = document.getElementById('parseBtn');
    const parsingForm = document.getElementById('parsingForm');
    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
    
    // Modal event handlers for accessibility
    const modalElement = document.getElementById('progressModal');
    
    modalElement.addEventListener('show.bs.modal', function() {
        // Remove aria-hidden when modal is about to be shown
        modalElement.removeAttribute('aria-hidden');
    });
    
    modalElement.addEventListener('shown.bs.modal', function() {
        // Ensure modal is properly accessible when shown
        modalElement.removeAttribute('aria-hidden');
        // Focus on the modal content for accessibility
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.setAttribute('tabindex', '-1');
            modalContent.focus();
        }
    });
    
    modalElement.addEventListener('hide.bs.modal', function() {
        // Don't set aria-hidden during hide, let Bootstrap handle it
    });
    
    modalElement.addEventListener('hidden.bs.modal', function() {
        // Set aria-hidden when modal is completely hidden
        modalElement.setAttribute('aria-hidden', 'true');
    });
    
    // File upload handling
    uploadArea.addEventListener('click', function() { 
        fileInput.click(); 
    });
    
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function() {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        // Validate file type
        if (file.type !== 'application/pdf') {
            showAlert('error', 'File harus berformat PDF');
            return;
        }
        
        // Validate file size (100MB)
        if (file.size > 100 * 1024 * 1024) {
            showAlert('error', 'Ukuran file maksimal 100MB');
            return;
        }
        
        // Show file info
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        fileInfo.classList.remove('d-none');
        parseBtn.disabled = false;
        
        // Enable parse button
        parseBtn.disabled = false;
    }
    
    function removeFile() {
        fileInput.value = '';
        fileInfo.classList.add('d-none');
        parseBtn.disabled = true;
        
        // Disable parse button
        parseBtn.disabled = true;
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Form submission - using alternative parse as main function
    parsingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        const file = fileInput.files[0];
        
        if (!file) {
            showAlert('error', 'Pilih file PDF terlebih dahulu');
            return;
        }
        
        formData.append('pdf', file);
        
        // Show progress modal
        progressModal.show();
        
        // Submit form with timeout handling
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 minutes timeout
        
        fetch('<?= base_url('parsing/parse_alternative') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controller.signal
        })
        .then(function(response) {
            clearTimeout(timeoutId);
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            // Check if response is ok
            if (!response.ok) {
                throw new Error('HTTP Error: ' + response.status + ' ' + response.statusText);
            }
            
            // Check content type
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON. Content-Type: ' + contentType);
            }
            
            return response.text().then(function(text) {
                console.log('Response text length:', text ? text.length : 0);
                console.log('Response text preview:', text ? text.substring(0, 200) : 'EMPTY');
                
                if (!text || text.trim() === '') {
                    throw new Error('Empty response from server');
                }
                
                try {
                    const jsonData = JSON.parse(text);
                    console.log('Parsed JSON:', jsonData);
                    return jsonData;
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response text:', text);
                    throw new Error('Invalid JSON response: ' + parseError.message + '. Response: ' + text.substring(0, 500));
                }
            });
        })
        .then(function(data) {
            // Hide modal with proper accessibility handling
            progressModal.hide();
            
            if (data && data.success) {
                showResults(data);
                showAlert('success', 'Berhasil memparse ' + (data.count || 0) + ' data VISA');
            } else {
                showAlert('error', (data && data.error) ? data.error : 'Terjadi kesalahan saat parsing');
            }
        })
        .catch(function(error) {
            clearTimeout(timeoutId);
            // Hide modal with proper accessibility handling
            progressModal.hide();
            console.error('Error:', error);
            
            // Check if it's a timeout
            if (error.name === 'AbortError') {
                showAlert('error', 'Request timeout. File mungkin terlalu besar atau server sedang sibuk. Silakan coba lagi dengan file yang lebih kecil.');
                return;
            }
            
            let errorMessage = 'Terjadi kesalahan saat memproses file';
            if (error.message) {
                errorMessage += ': ' + error.message;
            }
            
            // Check if it's a network error or empty response
            if (error.message.includes('Empty response') || error.message.includes('Failed to fetch')) {
                errorMessage += '\n\nKemungkinan penyebab:\n';
                errorMessage += '• Server tidak merespons\n';
                errorMessage += '• File terlalu besar (maksimal 50MB)\n';
                errorMessage += '• Timeout server (maksimal 5 menit)\n';
                errorMessage += '• Masalah koneksi\n\n';
                errorMessage += 'Silakan coba lagi atau gunakan file yang lebih kecil.';
            }
            
            showAlert('error', errorMessage);
        });
    });
    
    function showResults(data) {
        const resultsSection = document.getElementById('resultsSection');
        const resultsDiv = document.getElementById('parsingResults');
        
        let html = '<div class="row mb-4">';
        html += '<div class="col-md-3">';
        html += '<div class="card border-0 bg-light">';
        html += '<div class="card-body text-center">';
        html += '<h4 class="text-success mb-1">' + data.count + '</h4>';
        html += '<p class="text-muted mb-0">Data Berhasil Diparse</p>';
        html += '</div></div></div>';
        html += '<div class="col-md-3">';
        html += '<div class="card border-0 bg-light">';
        html += '<div class="card-body text-center">';
        html += '<h4 class="text-info mb-1">' + (data.total_pages || 1) + '</h4>';
        html += '<p class="text-muted mb-0">Total Halaman</p>';
        html += '</div></div></div>';
        html += '<div class="col-md-3">';
        html += '<div class="card border-0 bg-light">';
        html += '<div class="card-body text-center">';
        html += '<h4 class="text-warning mb-1">' + (data.successful_pages || data.count) + '</h4>';
        html += '<p class="text-muted mb-0">Halaman Berhasil</p>';
        html += '</div></div></div>';
        html += '<div class="col-md-3">';
        html += '<div class="card border-0 bg-light">';
        html += '<div class="card-body text-center">';
        html += '<h4 class="text-primary mb-1">' + (data.extraction_method || 'Unknown') + '</h4>';
        html += '<p class="text-muted mb-0">Metode Ekstraksi</p>';
        html += '</div></div></div></div>';
        
        if (data.data && data.data.length > 0) {
            html += '<div class="table-responsive">';
            html += '<table class="table table-hover">';
            html += '<thead><tr>';
            html += '<th>No</th>';
            html += '<th>Halaman</th>';
            html += '<th>Nama</th>';
            html += '<th>No Paspor</th>';
            html += '<th>No VISA</th>';
            html += '<th>Tanggal Lahir</th>';
            html += '<th>Status</th>';
            html += '</tr></thead><tbody>';
            
            for (let i = 0; i < data.data.length; i++) {
                const item = data.data[i];
                const status = (item.nama && item.visa_no) ? 'success' : 'warning';
                const statusText = (item.nama && item.visa_no) ? 'Lengkap' : 'Tidak Lengkap';
                
                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td><span class="badge bg-secondary">' + (item.page_number || 'N/A') + '</span></td>';
                html += '<td>' + (item.nama || '<span class="text-muted">-</span>') + '</td>';
                html += '<td>' + (item.passport_no || '<span class="text-muted">-</span>') + '</td>';
                html += '<td>' + (item.visa_no || '<span class="text-muted">-</span>') + '</td>';
                html += '<td>' + (item.tanggal_lahir || '<span class="text-muted">-</span>') + '</td>';
                html += '<td><span class="badge bg-' + status + '">' + statusText + '</span></td>';
                html += '</tr>';
            }
            
            html += '</tbody></table></div>';
            html += '<div class="text-end mt-3">';
            html += '<button class="btn btn-success" onclick="downloadResults()">';
            html += '<i class="fas fa-download me-2"></i>Download Hasil';
            html += '</button></div>';
        }
        
        resultsDiv.innerHTML = html;
        resultsSection.classList.remove('d-none');
        
        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }
    
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
        const alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            '<i class="' + iconClass + ' me-2"></i>' + message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        for (let i = 0; i < existingAlerts.length; i++) {
            existingAlerts[i].remove();
        }
        
        // Insert new alert
        const container = document.querySelector('.content-body');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
    
    // Global functions
    window.downloadResults = function() {
        // Download parsing result Excel
        window.location.href = '<?= base_url('parsing/download_parsing_result') ?>';
    };
    
    window.removeFile = removeFile;
    
});
</script>