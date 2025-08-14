<!-- Content Body -->
<div class="content-body">
    <!-- Flag Doc Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Data</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('dashboard') ?>" class="filter-form">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label for="flag_doc" class="form-label">
                                    <i class="fas fa-tag"></i> Pilih Flag Dokumen
                                </label>
                                <select name="flag_doc" id="flag_doc" class="form-select mobile-input" onchange="this.form.submit()">
                                    <option value="">Semua Flag Dokumen</option>
                                    <?php foreach ($flag_doc_list as $flag): ?>
                                        <option value="<?= $flag->flag_doc ?>" <?= $selected_flag_doc == $flag->flag_doc ? 'selected' : '' ?>>
                                            <?= $flag->flag_doc ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-brown">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="count"><?= $total_peserta ?></div>
                        <div class="title">Total Peserta</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="count"><?= $stats ?></div>
                        <div class="title">Status Done <?= $selected_flag_doc ? '(' . $selected_flag_doc . ')' : '' ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="count"><?= $stats_already ?></div>
                        <div class="title">Status Already <?= $selected_flag_doc ? '(' . $selected_flag_doc . ')' : '' ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="icon">
                        <i class="fas fa-crosshairs"></i>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="count"><?= $stats_on_target ?></div>
                        <div class="title">Status On Target <?= $selected_flag_doc ? '(' . $selected_flag_doc . ')' : '' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Statistics -->
    <div class="row mb-4">
        
        <!-- Schedule Statistics -->
        <div class="col-md-12">
            <div class="card mobile-card">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Jadwal Kunjungan</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($schedule_by_date)): ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-calendar fa-3x mb-3"></i>
                            <p>Tidak ada data jadwal untuk ditampilkan</p>
                        </div>
                    <?php else: ?>
                        <div class="schedule-stats">
                            <?php 
                            $maxDateCount = !empty($schedule_by_date) ? max(array_map(function($s){ return $s->total_count; }, $schedule_by_date)) : 1;
                            ?>
                            <?php foreach ($schedule_by_date as $schedule): ?>
                                <?php
                                $tanggal = $schedule->tanggal;
                                // Format tanggal ke bahasa Indonesia
                                setlocale(LC_TIME, 'id_ID.UTF-8');
                                $tanggal_formatted = strftime('%d %B %Y', strtotime($tanggal));
                                
                                // Get detail jam untuk tanggal ini
                                $detail_jam = $this->transaksi_model->get_schedule_detail_by_date($tanggal, $selected_flag_doc);
                                ?>
                                <div class="schedule-item">
                                    <div class="schedule-header">
                                        <div class="schedule-info">
                                            <span class="schedule-date">
                                                <i class="fas fa-calendar-day text-primary"></i> <?= $tanggal_formatted ?>
                                            </span>
                                            <span class="schedule-count"><?= (int)$schedule->total_count ?></span>
                                        </div>
                                        <div class="schedule-gender-summary">
                                            <span class="gender-badge male">
                                                <i class="fas fa-mars"></i> <?= (int)$schedule->total_male ?>
                                            </span>
                                            <span class="gender-badge female">
                                                <i class="fas fa-venus"></i> <?= (int)$schedule->total_female ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-primary" style="width: <?= $maxDateCount > 0 ? ($schedule->total_count / $maxDateCount) * 100 : 0 ?>%"></div>
                                    </div>
                                    
                                    <!-- Detail jam untuk tanggal ini -->
                                    <?php if (!empty($detail_jam)): ?>
                                        <div class="schedule-details">
                                            <h6 class="detail-title">
                                                <i class="fas fa-clock text-warning"></i> Detail Jam Kunjungan:
                                            </h6>
                                            <div class="time-slots">
                                                <?php foreach ($detail_jam as $jam): ?>
                                                    <div class="time-slot">
                                                        <div class="time-info">
                                                            <span class="time-label">
                                                                <i class="fas fa-clock text-info"></i> <?= $jam->jam ?>
                                                            </span>
                                                            <span class="time-count"><?= (int)$jam->total_count ?></span>
                                                        </div>
                                                        <div class="time-gender-breakdown">
                                                            <a href="<?= base_url('database/index?flag_doc=' . ($selected_flag_doc ?: '') . '&tanggaljam=' . $tanggal . ' ' . $jam->jam . '&status=&gender=L') ?>" 
                                                               class="gender-link male">
                                                                <span><i class="fas fa-mars text-primary"></i> Laki-laki: <strong><?= (int)$jam->male_count ?></strong></span>
                                                            </a>
                                                            <a href="<?= base_url('database/index?flag_doc=' . ($selected_flag_doc ?: '') . '&tanggaljam=' . $tanggal . ' ' . $jam->jam . '&status=&gender=P') ?>" 
                                                               class="gender-link female">
                                                                <span><i class="fas fa-venus text-danger"></i> Perempuan: <strong><?= (int)$jam->female_count ?></strong></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    
</div>

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

/* Mobile Card Styles */
.mobile-card {
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: none;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.mobile-card .card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border: none;
    padding: 1rem 1.5rem;
}

.mobile-card .card-body {
    padding: 1.5rem;
}

/* Filter Form Styles */
.filter-form {
    margin-bottom: 0;
}

.mobile-input {
    border: 2px solid #e9ecef;
    border-radius: var(--border-radius);
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: var(--transition);
}

.mobile-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
    outline: none;
}

/* Dashboard Card Enhancements */
.dashboard-card {
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    transition: var(--transition);
    border-left: 5px solid var(--gold);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.95));
    position: relative;
    overflow: hidden;
}

.dashboard-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--primary-color));
    background-size: 200% 100%;
    animation: gradientShift 3s ease-in-out infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.dashboard-card .icon {
    font-size: 2.5rem;
    color: var(--gold);
    margin-bottom: 0.5rem;
}

.dashboard-card .count {
    font-size: 2rem;
    font-weight: bold;
    color: var(--dark-color);
    margin-bottom: 0.25rem;
}

.dashboard-card .title {
    font-size: 1rem;
    color: var(--secondary-color);
    font-weight: 600;
}

/* Statistics Styles */
.gender-stats, .hour-stats, .schedule-stats {
    max-height: 500px;
    overflow-y: auto;
}

.gender-item, .hour-item, .schedule-item {
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--primary-color);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.gender-info, .hour-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.gender-label, .hour-label {
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.gender-count, .hour-count {
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
}

/* Schedule Styles */
.schedule-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.schedule-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.schedule-date {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.schedule-count {
    background: var(--primary-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: bold;
    font-size: 1.1rem;
    min-width: 50px;
    text-align: center;
}

.schedule-gender-summary {
    display: flex;
    gap: 0.5rem;
}

.gender-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.gender-badge.male {
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
    border: 1px solid rgba(0, 123, 255, 0.3);
}

.gender-badge.female {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.3);
}

.schedule-details {
    background: rgba(255, 255, 255, 0.8);
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-top: 1rem;
    border: 1px solid rgba(0,0,0,0.1);
}

.detail-title {
    color: var(--dark-color);
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.time-slots {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.time-slot {
    background: rgba(248, 249, 250, 0.8);
    border-radius: 8px;
    padding: 0.75rem;
    border-left: 3px solid var(--info-color);
}

.time-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.time-label {
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.time-count {
    background: var(--info-color);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 15px;
    font-weight: bold;
    font-size: 0.85rem;
}

.time-gender-breakdown {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}

.progress {
    height: 8px;
    border-radius: 4px;
    background: rgba(139, 69, 19, 0.1);
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    transition: width 0.6s ease;
}

/* Button Styles */
.btn {
    border-radius: var(--border-radius);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-brown {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light)) !important;
    color: white !important;
}

.btn-brown:hover {
    background: linear-gradient(135deg, var(--primary-light), var(--primary-color)) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn-secondary {
    background: #6c757d !important;
    color: white !important;
}

.btn-secondary:hover {
    background: #5a6268 !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

/* Responsive Design */
@media (max-width: 768px) {
    .mobile-card .card-body {
        padding: 1rem;
    }
    
    .dashboard-card {
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .dashboard-card .count {
        font-size: 1.5rem;
    }
    
    .dashboard-card .title {
        font-size: 0.9rem;
    }
    
    .gender-info, .hour-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .gender-count, .hour-count {
        align-self: flex-end;
    }
    
    .schedule-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .schedule-info {
        width: 100%;
        justify-content: space-between;
    }
    
    .schedule-gender-summary {
        width: 100%;
        justify-content: flex-start;
    }
    
    .time-gender-breakdown {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-form .row {
        gap: 1rem;
    }
    
    .filter-form .col-md-6 {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .mobile-card .card-header {
        padding: 0.75rem 1rem;
    }
    
    .mobile-card .card-body {
        padding: 0.75rem;
    }
    
    .dashboard-card .icon {
        font-size: 2rem;
    }
    
    .dashboard-card .count {
        font-size: 1.25rem;
    }
    
    .gender-item, .hour-item {
        padding: 0.75rem;
    }
}

/* Animation Effects */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dashboard-card, .mobile-card {
    animation: slideInUp 0.3s ease-out;
    animation-fill-mode: both;
}

.dashboard-card:nth-child(1) { animation-delay: 0.1s; }
.dashboard-card:nth-child(2) { animation-delay: 0.2s; }
.dashboard-card:nth-child(3) { animation-delay: 0.3s; }

/* Scrollbar Styling */
.gender-stats::-webkit-scrollbar,
.hour-stats::-webkit-scrollbar {
    width: 6px;
}

.gender-stats::-webkit-scrollbar-track,
.hour-stats::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.gender-stats::-webkit-scrollbar-thumb,
.hour-stats::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

.gender-stats::-webkit-scrollbar-thumb:hover,
.hour-stats::-webkit-scrollbar-thumb:hover,
.schedule-stats::-webkit-scrollbar-thumb:hover {
    background: var(--primary-light);
}

.schedule-stats::-webkit-scrollbar {
    width: 6px;
}

.schedule-stats::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.schedule-stats::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}
.gender-link {
    display: inline-flex;
    align-items: center;
    padding: 10px 16px;
    margin: 5px;
    border-radius: 8px;
    background-color: #f8f9fa;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.gender-link:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    color: #fff;
}

.gender-link i.fa-mars {
    transition: color 0.3s ease;
}

.gender-link i.fa-venus {
    transition: color 0.3s ease;
}

.gender-link:hover i.fa-mars {
    color: #007bff !important;
}

.gender-link:hover i.fa-venus {
    color: #e63946 !important;
}

/* Spesifik untuk Laki-laki */
.gender-link.male:hover {
    background-color: #007bff;
}

/* Spesifik untuk Perempuan */
.gender-link.female:hover {
    background-color: #e63946;
}
</style> 