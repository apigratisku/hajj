<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">
                    <i class="fas fa-file-pdf text-primary"></i>
                    Parsing Data VISA
                </h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Parsing Data</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload"></i>
                        Upload File PDF VISA
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= number_format(isset($stats['total_records']) ? $stats['total_records'] : 0) ?></h3>
                                    <p>Total Data Parsing</p>
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

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle"></i> Informasi Parsing</h5>
                                <p class="mb-0">
                                    Upload file PDF VISA untuk melakukan parsing otomatis. 
                                    Data yang berhasil diparsing akan disimpan ke database dan dapat dilihat di 
                                    <a href="<?= base_url('parsing/view_data') ?>" class="alert-link">halaman Data Parsing</a>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <div class="row">
                        <div class="col-md-8">
                            <form id="uploadForm" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="pdf_file" class="form-label">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                        Pilih File PDF VISA
                                    </label>
                                    <input type="file" class="form-control" id="pdf_file" name="pdf_file" 
                                           accept=".pdf" required>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle text-info"></i>
                                        File harus berformat PDF dengan ukuran maksimal 100MB
                                    </div>
                                </div>
                                
                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                                        <i class="fas fa-upload"></i>
                                        Upload & Parse PDF
                                    </button>
                                    <button type="button" class="btn btn-secondary ml-2" id="clearBtn" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                        Hapus Data
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Informasi Parsing</h6>
                                <ul class="mb-0 small">
                                    <li>Sistem akan mengekstrak data VISA dari file PDF</li>
                                    <li>Data yang diekstrak: Visa No., Passport No., Full Name, Birth Date</li>
                                    <li>Hasil parsing dapat didownload dalam format Excel</li>
                                    <li>Format Excel: Nama, No Paspor, No Visa, Tanggal Lahir</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div id="progressContainer" style="display: none;">
                        <div class="progress mt-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="progressText">Memproses file PDF...</small>
                    </div>

                    <!-- Results Section -->
                    <div id="resultsSection" style="display: none;">
                        <hr>
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Parsing Berhasil!</h5>
                            <p class="mb-2" id="successMessage"></p>
                            <button type="button" class="btn btn-success" id="downloadBtn">
                                <i class="fas fa-download"></i>
                                Download Excel
                            </button>
                        </div>

                        <!-- Data Preview -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-eye"></i>
                                    Preview Data
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped" id="previewTable">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>No Paspor</th>
                                                <th>No Visa</th>
                                                <th>Tanggal Lahir</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="errorMessage">
                <!-- Error message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const clearBtn = document.getElementById('clearBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.querySelector('.progress-bar');
    const progressText = document.getElementById('progressText');
    const resultsSection = document.getElementById('resultsSection');
    const successMessage = document.getElementById('successMessage');
    const previewTableBody = document.getElementById('previewTableBody');
    const errorModal = document.getElementById('errorModal');
    const errorMessage = document.getElementById('errorMessage');

    // Upload form submission
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('pdf_file');
        const file = fileInput.files[0];
        
        if (!file) {
            showError('Pilih file PDF terlebih dahulu');
            return;
        }
        
        if (file.type !== 'application/pdf') {
            showError('File harus berformat PDF');
            return;
        }
        
        if (file.size > 100 * 1024 * 1024) {
            showError('Ukuran file maksimal 100MB');
            return;
        }
        
        uploadFile(file);
    });

    // Clear data button
    clearBtn.addEventListener('click', function() {
        if (confirm('Apakah Anda yakin ingin menghapus data parsing?')) {
            clearSessionData();
        }
    });

    // Download button
    downloadBtn.addEventListener('click', function() {
        window.location.href = '<?= base_url('parsing/download_excel') ?>';
    });

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('pdf_file', file);
        
        // Show progress
        showProgress();
        
        // Disable upload button
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        
        fetch('<?= base_url('parsing/upload_pdf') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideProgress();
            
            if (data.success) {
                showResults(data);
            } else {
                showError(data.message || 'Terjadi kesalahan saat parsing file PDF');
            }
        })
        .catch(error => {
            hideProgress();
            showError('Terjadi kesalahan: ' + error.message);
        })
        .finally(() => {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Parse PDF';
        });
    }

    function showProgress() {
        progressContainer.style.display = 'block';
        progressBar.style.width = '0%';
        progressText.textContent = 'Memproses file PDF...';
        
        // Simulate progress
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            
            progressBar.style.width = progress + '%';
            
            if (progress < 30) {
                progressText.textContent = 'Membaca file PDF...';
            } else if (progress < 60) {
                progressText.textContent = 'Mengekstrak data VISA...';
            } else if (progress < 90) {
                progressText.textContent = 'Memproses data...';
            }
        }, 500);
        
        // Store interval ID for cleanup
        progressContainer.dataset.interval = interval;
    }

    function hideProgress() {
        const interval = progressContainer.dataset.interval;
        if (interval) {
            clearInterval(interval);
        }
        
        progressBar.style.width = '100%';
        progressText.textContent = 'Selesai!';
        
        setTimeout(() => {
            progressContainer.style.display = 'none';
        }, 1000);
    }

    function showResults(data) {
        successMessage.textContent = data.message;
        
        // Show preview data
        if (data.data_preview && data.data_preview.length > 0) {
            previewTableBody.innerHTML = '';
            data.data_preview.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${item.full_name || '-'}</td>
                    <td>${item.passport_no || '-'}</td>
                    <td>${item.visa_no || '-'}</td>
                    <td>${item.birth_date || '-'}</td>
                `;
                previewTableBody.appendChild(row);
            });
        }
        
        resultsSection.style.display = 'block';
        clearBtn.style.display = 'inline-block';
        
        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }

    function showError(message) {
        errorMessage.innerHTML = `<p class="mb-0">${message}</p>`;
        $('#errorModal').modal('show');
    }

    function clearSessionData() {
        fetch('<?= base_url('parsing/clear_session') ?>', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultsSection.style.display = 'none';
                clearBtn.style.display = 'none';
                document.getElementById('pdf_file').value = '';
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    Data parsing berhasil dihapus
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                `;
                
                document.querySelector('.card-body').insertBefore(alert, document.querySelector('form'));
                
                // Auto dismiss after 3 seconds
                setTimeout(() => {
                    alert.remove();
                }, 3000);
            }
        })
        .catch(error => {
            showError('Terjadi kesalahan saat menghapus data: ' + error.message);
        });
    }

    // File input change event
    document.getElementById('pdf_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Reset results section
            resultsSection.style.display = 'none';
            clearBtn.style.display = 'none';
        }
    });
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.progress {
    height: 1.5rem;
}

.progress-bar {
    font-size: 0.875rem;
    line-height: 1.5rem;
}

.table th {
    font-weight: 600;
    border-top: none;
}

.alert {
    border: none;
    border-radius: 0.375rem;
}

.btn {
    border-radius: 0.375rem;
}

.form-control {
    border-radius: 0.375rem;
}

.page-title-box {
    margin-bottom: 1.5rem;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #6c757d;
}
</style>
