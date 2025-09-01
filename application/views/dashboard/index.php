<!-- Content Body -->
<div class="content-body">
    <!-- Flag Doc Filter -->
    <div class="row mb-0">
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

    <!-- Statistics Cards Toggle Button - Ultra Compact -->
    <div class="row mb-1">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                
                <div class="toggle-buttons">
                    <!-- Desktop Toggle -->
                    <button class="btn btn-outline-primary btn-sm d-none d-md-inline-block toggle-stats-btn" 
                            data-target="desktop-stats" 
                            data-action="toggle">
                        <i class="fas fa-eye-slash"></i> 
                        <span class="toggle-text">Tampilkan</span> Statistik
                    </button>
                    <!-- Mobile Toggle -->
                    <button class="btn btn-outline-primary btn-sm d-md-none toggle-stats-btn" 
                            data-target="mobile-stats" 
                            data-action="toggle">
                        <i class="fas fa-eye-slash"></i> 
                        <span class="toggle-text">Tampilkan</span> Statistik
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-1 stats-container" id="desktop-stats" style="display: none;">
        <div class="col-12">
            <div class="stats-horizontal-container">
                <div class="stats-item">
                    <div class="stats-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-count"><?= $total_peserta ?></div>
                        <div class="stats-title">Total Peserta</div>
                    </div>
                </div>
                
                <div class="stats-item">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-count"><?= $stats ?></div>
                        <div class="stats-title">Status Done <?= $selected_flag_doc ? '(' . $selected_flag_doc . ')' : '' ?></div>
                    </div>
                </div>
                
                <div class="stats-item">
                    <div class="stats-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-count"><?= $stats_already ?></div>
                        <div class="stats-title">Status Already <?= $selected_flag_doc ? '(' . $selected_flag_doc . ')' : '' ?></div>
                    </div>
                </div>
                
                <div class="stats-item">
                    <div class="stats-icon">
                        <i class="fas fa-crosshairs"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-count"><?= $stats_on_target ?></div>
                        <div class="stats-title">Status On Target <?= $selected_flag_doc ? '(' . $selected_flag_doc . ')' : '' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Statistics Cards -->
    <div class="row mb-1 stats-container d-md-none" id="mobile-stats" style="display: none;">
        <div class="col-12">
            <div class="stats-horizontal-container mobile-stats-horizontal">
                <div class="stats-item mobile-stats-item">
                    <div class="stats-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-count"><?= $total_peserta ?></div>
                        <div class="stats-title">Total Peserta</div>
                    </div>
                </div>
                
                <div class="stats-item mobile-stats-item">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-count"><?= $stats ?></div>
                        <div class="stats-title">Status Done</div>
                    </div>
                </div>
                
                <div class="stats-item mobile-stats-item">
                    <div class="stats-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-count"><?= $stats_already ?></div>
                        <div class="stats-title">Status Already</div>
                    </div>
                </div>
                
                <div class="stats-item mobile-stats-item">
                    <div class="stats-icon">
                        <i class="fas fa-crosshairs"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-count"><?= $stats_on_target ?></div>
                        <div class="stats-title">Status On Target</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Statistics -->
    <div class="row mb-2">
        
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
                                    
                                    
                                    <!-- Detail jam untuk tanggal ini -->
                                    <?php if (!empty($detail_jam)): ?>
                                        <div class="schedule-details">
                                           
                                            <div class="time-slots">
                                                <?php foreach ($detail_jam as $jam): ?>
                                                    <?php
                                                    // Cek kondisi untuk menampilkan tombol Selesai
                                                    $current_date = date('Y-m-d');
                                                    $current_time = date('H:i:s');
                                                    $schedule_datetime = $tanggal . ' ' . $jam->jam . ':00';
                                                    $current_datetime = $current_date . ' ' . $current_time;
                                                    
                                                    // Tombol muncul jika:
                                                    // 1. Tanggal sudah lewat, atau
                                                    // 2. Tanggal sama tapi jam sudah lewat
                                                    $show_complete_button = false;
                                                    if ($tanggal < $current_date) {
                                                        $show_complete_button = true;
                                                    } elseif ($tanggal == $current_date && $jam->jam <= $current_time) {
                                                        $show_complete_button = true;
                                                    }
                                                    ?>
                                                    <div class="time-slot">
                                                        <div class="time-info">
                                                            <span class="time-label">
                                                                <i class="fas fa-clock text-info"></i> <?= $jam->jam ? date('h:i A', strtotime($jam->jam)) : '-' ?>
                                                            </span>
                                                            <span class="time-count"><?= (int)$jam->total_count ?></span>
                                                            <?php if ($show_complete_button): ?>
                                                                <button class="btn btn-success btn-sm complete-btn" 
                                                                        data-tanggal="<?= $tanggal ?>" 
                                                                        data-jam="<?= $jam->jam ?>" 
                                                                        data-flag-doc="<?= $selected_flag_doc ?>">
                                                                    <i class="fas fa-check"></i> Selesai
                                                                </button>
                                                            <?php endif; ?>
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

/* Mobile Card Styles - Ultra Compact */
.mobile-card {
    border-radius: 8px; /* Smaller radius */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Smaller shadow */
    border: none;
    overflow: hidden;
    margin-bottom: 0.75rem; /* Smaller margin */
}

.mobile-card .card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border: none;
    padding: 0.5rem 1rem; /* Smaller padding */
}

.mobile-card .card-body {
    padding: 0.75rem; /* Smaller padding */
}

/* Filter Form Styles - Ultra Compact */
.filter-form {
    margin-bottom: 0;
}

.mobile-input {
    border: 1px solid #e9ecef; /* Reduced border */
    border-radius: 6px; /* Smaller radius */
    padding: 0.4rem 0.6rem; /* Smaller padding */
    font-size: 0.85rem; /* Smaller font */
    transition: var(--transition);
}

.mobile-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.1rem rgba(139, 69, 19, 0.25); /* Smaller shadow */
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

/* Schedule Styles - Ultra Compact Default */
.schedule-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem; /* Further reduced */
    padding: 0.25rem 0; /* Reduced padding */
}

.schedule-info {
    display: flex;
    align-items: center;
    gap: 0.25rem; /* Further reduced */
    flex: 1;
}

.schedule-date {
    font-weight: 600;
    font-size: 0.9rem; /* Smaller font */
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.15rem; /* Minimal gap */
}

.schedule-count {
    background: var(--primary-color);
    color: white;
    padding: 0.15rem 0.3rem; /* Even smaller */
    border-radius: 8px; /* Smaller radius */
    font-weight: bold;
    font-size: 0.8rem; /* Smaller font */
    min-width: 25px; /* Smaller width */
    text-align: center;
}

.schedule-gender-summary {
    display: flex;
    gap: 0.15rem; /* Minimal gap */
    flex-shrink: 0;
}

.gender-badge {
    padding: 0.1rem 0.3rem; /* Smaller padding */
    border-radius: 6px; /* Smaller radius */
    font-weight: 600;
    font-size: 0.7rem; /* Smaller font */
    display: flex;
    align-items: center;
    gap: 0.1rem; /* Minimal gap */
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
    border-radius: 4px; /* Smaller radius */
    padding: 0.25rem; /* Smaller padding */
    margin-top: 0.25rem; /* Smaller margin */
    border: 1px solid rgba(0,0,0,0.1);
}

.detail-title {
    color: var(--dark-color);
    font-weight: 600;
    margin-bottom: 0.25rem; /* Smaller margin */
    display: flex;
    align-items: center;
    gap: 0.15rem; /* Minimal gap */
    font-size: 0.8rem; /* Smaller font */
}

.time-slots {
    display: flex;
    flex-direction: column;
    gap: 0.15rem; /* Minimal gap */
}

.time-slot {
    background: rgba(248, 249, 250, 0.8);
    border-radius: 4px; /* Smaller radius */
    padding: 0.25rem; /* Smaller padding */
    border-left: 2px solid var(--info-color);
}

.time-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.15rem; /* Smaller margin */
}

.time-label {
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.15rem; /* Minimal gap */
    font-size: 0.8rem; /* Smaller font */
}

.time-count {
    background: var(--info-color);
    color: white;
    padding: 0.1rem 0.25rem; /* Smaller padding */
    border-radius: 6px; /* Smaller radius */
    font-weight: bold;
    font-size: 0.7rem; /* Smaller font */
}

.time-gender-breakdown {
    display: flex;
    justify-content: space-between;
    gap: 0.15rem; /* Minimal gap */
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

/* Button Styles - Ultra Compact */
.btn {
    border-radius: 6px; /* Smaller radius */
    padding: 0.4rem 0.8rem; /* Smaller padding */
    font-weight: 600;
    transition: var(--transition);
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem; /* Smaller gap */
    font-size: 0.85rem; /* Smaller font */
}

.btn-brown {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light)) !important;
    color: white !important;
}

.btn-brown:hover {
    background: linear-gradient(135deg, var(--primary-light), var(--primary-color)) !important;
    transform: translateY(-1px); /* Smaller transform */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15); /* Smaller shadow */
}

.btn-secondary {
    background: #6c757d !important;
    color: white !important;
}

.btn-secondary:hover {
    background: #5a6268 !important;
    transform: translateY(-1px); /* Smaller transform */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15); /* Smaller shadow */
}

/* Responsive Design - Ultra Compact */
@media (max-width: 768px) {
    .mobile-card .card-body {
        padding: 0.5rem; /* Even smaller */
    }
    
    .dashboard-card {
        padding: 0.5rem; /* Even smaller */
        margin-bottom: 0.5rem; /* Even smaller */
    }
    
    .dashboard-card .count {
        font-size: 1.1rem; /* Even smaller */
    }
    
    .dashboard-card .title {
        font-size: 0.75rem; /* Even smaller */
    }
    
    .gender-info, .hour-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.15rem; /* Minimal gap */
    }
    
    .gender-count, .hour-count {
        align-self: flex-end;
    }
    
    /* Mobile-optimized schedule layout */
    .schedule-header {
        flex-direction: row; /* Keep horizontal for mobile */
        align-items: center;
        gap: 0.25rem; /* Minimal gap */
        margin-bottom: 0.15rem; /* Minimal margin */
    }
    
    .schedule-info {
        width: auto;
        justify-content: flex-start;
        flex: 1;
    }
    
    .schedule-date {
        font-size: 0.8rem; /* Even smaller */
    }
    
    .schedule-count {
        padding: 0.1rem 0.25rem; /* Minimal padding */
        font-size: 0.75rem;
        min-width: 25px;
    }
    
    .schedule-gender-summary {
        width: auto;
        justify-content: flex-end;
        flex-shrink: 0;
    }
    
    .gender-badge {
        padding: 0.05rem 0.2rem; /* Minimal padding */
        font-size: 0.65rem;
    }
    
    .schedule-details {
        padding: 0.15rem; /* Minimal padding */
        margin-top: 0.15rem;
    }
    
    .detail-title {
        font-size: 0.75rem;
        margin-bottom: 0.15rem;
    }
    
    .time-slots {
        gap: 0.1rem; /* Minimal gap */
    }
    
    .time-slot {
        padding: 0.15rem; /* Minimal padding */
    }
    
    .time-info {
        margin-bottom: 0.1rem; /* Minimal margin */
    }
    
    .time-label {
        font-size: 0.75rem;
    }
    
    .time-count {
        padding: 0.05rem 0.2rem;
        font-size: 0.65rem;
    }
    
    .time-gender-breakdown {
        flex-direction: row; /* Keep horizontal */
        gap: 0.1rem; /* Minimal gap */
    }
    
    .filter-form .row {
        gap: 0.25rem; /* Minimal gap */
    }
    
    .filter-form .col-md-6 {
        width: 100%;
    }
    
    /* Filter form optimizations */
    .mobile-input {
        padding: 0.3rem 0.5rem; /* Even smaller */
        font-size: 0.8rem;
    }
    
    .btn {
        padding: 0.3rem 0.6rem; /* Even smaller */
        font-size: 0.8rem;
    }
}

    /* Mobile Toggle Button */
    .toggle-buttons {
        width: 100%;
        text-align: center;
        margin-top: 0.5rem;
    }

    .toggle-stats-btn {
        width: 100%;
        justify-content: center;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }

    /* Horizontal Stats Responsive */
    .stats-horizontal-container {
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.75rem;
    }

    .stats-item {
        width: 100%;
        justify-content: center;
        padding: 0.5rem;
        border-bottom: 1px solid rgba(139, 69, 19, 0.1);
    }

    .stats-item:last-child {
        border-bottom: none;
    }

    .stats-item::after {
        display: none;
    }

    .stats-icon {
        font-size: 1.5rem;
        min-width: 40px;
    }

    .stats-count {
        font-size: 1.4rem;
    }

    .stats-title {
        font-size: 0.8rem;
    }

    /* Mobile Stats Cards (Legacy) */
    .mobile-stats-card {
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .mobile-stats-card .icon {
        font-size: 1.5rem;
    }

    .mobile-stats-card .count {
        font-size: 1.25rem;
    }

    .mobile-stats-card .title {
        font-size: 0.75rem;
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

/* Ultra Compact Layout - Default for all devices */

/* Card-based Layout Alternative */
.schedule-card-layout {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.schedule-card {
    background: white;
    border-radius: 8px;
    padding: 0.75rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid var(--primary-color);
}

.schedule-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    padding-bottom: 0.25rem;
    border-bottom: 1px solid #eee;
}

.schedule-card .card-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--dark-color);
}

.schedule-card .card-count {
    background: var(--primary-color);
    color: white;
    padding: 0.15rem 0.4rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: bold;
}

.schedule-card .time-list {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.schedule-card .time-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 0.8rem;
}

/* List Layout Alternative */
.schedule-list-layout {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.schedule-list-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    background: white;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-left: 3px solid var(--primary-color);
}

.schedule-list-item .date-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.schedule-list-item .date-label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--dark-color);
}

.schedule-list-item .count-badge {
    background: var(--primary-color);
    color: white;
    padding: 0.1rem 0.3rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: bold;
}

.schedule-list-item .gender-info {
    display: flex;
    gap: 0.25rem;
    margin-left: 0.5rem;
}

.schedule-list-item .gender-badge {
    padding: 0.1rem 0.25rem;
    border-radius: 6px;
    font-size: 0.65rem;
}

/* Ultra Compact Mobile Layout */
@media (max-width: 480px) {
    .schedule-item {
        margin-bottom: 0.15rem; /* Even smaller */
        padding: 0.15rem; /* Even smaller */
    }
    
    .schedule-header {
        margin-bottom: 0.1rem; /* Minimal */
        padding: 0.1rem 0; /* Minimal */
    }
    
    .schedule-date {
        font-size: 0.75rem; /* Even smaller */
    }
    
    .schedule-count {
        padding: 0.05rem 0.2rem; /* Minimal */
        font-size: 0.65rem; /* Even smaller */
        min-width: 20px; /* Smaller */
    }
    
    .gender-badge {
        padding: 0.03rem 0.15rem; /* Minimal */
        font-size: 0.6rem; /* Even smaller */
    }
    
    .schedule-details {
        padding: 0.1rem; /* Minimal */
        margin-top: 0.1rem; /* Minimal */
    }
    
    .detail-title {
        font-size: 0.7rem; /* Even smaller */
        margin-bottom: 0.1rem; /* Minimal */
    }
    
    .time-slot {
        padding: 0.1rem; /* Minimal */
        margin-bottom: 0.05rem; /* Minimal */
    }
    
    .time-label {
        font-size: 0.7rem; /* Even smaller */
    }
    
    .time-count {
        padding: 0.03rem 0.15rem; /* Minimal */
        font-size: 0.6rem; /* Even smaller */
    }
    
    .time-gender-breakdown {
        gap: 0.05rem; /* Minimal */
    }
    
    .gender-link {
        padding: 0.15rem 0.3rem; /* Even smaller */
        margin: 0.05rem; /* Minimal */
        font-size: 0.65rem; /* Even smaller */
    }
    
    .complete-btn {
        padding: 0.1rem 0.3rem; /* Even smaller */
        font-size: 0.6rem; /* Even smaller */
        margin-left: 0.15rem; /* Smaller */
    }
    
    /* Filter optimizations for small screens */
    .mobile-card .card-body {
        padding: 0.25rem; /* Minimal */
    }
    
    .mobile-card .card-header {
        padding: 0.25rem 0.5rem; /* Minimal */
    }
    
    .mobile-input {
        padding: 0.2rem 0.4rem; /* Minimal */
        font-size: 0.75rem; /* Even smaller */
    }
    
    .btn {
        padding: 0.2rem 0.4rem; /* Minimal */
        font-size: 0.75rem; /* Even smaller */
    }
    
    .filter-form .row {
        gap: 0.15rem; /* Minimal */
    }
}

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

/* Tombol Selesai Styles */
.complete-btn {
    margin-left: 10px;
    padding: 5px 12px;
    font-size: 0.8rem;
    border-radius: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.complete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.complete-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Loading state untuk tombol */
.complete-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.complete-btn.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Toggle Button Styles */
.toggle-stats-btn {
    border-radius: var(--border-radius);
    padding: 0.5rem 1rem;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid var(--primary-color);
    background: transparent;
    color: var(--primary-color);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.toggle-stats-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.toggle-stats-btn.active {
    background: var(--primary-color);
    color: white;
}

.toggle-stats-btn.active i {
    transform: rotate(180deg);
}

.toggle-stats-btn i {
    transition: transform 0.3s ease;
}

/* Horizontal Stats Layout - Ultra Compact */
.stats-horizontal-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.95));
    border-radius: 8px; /* Smaller radius */
    padding: 0.5rem; /* Smaller padding */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Smaller shadow */
    border-left: 3px solid var(--gold); /* Smaller border */
    position: relative;
    overflow: hidden;
}

.stats-horizontal-container::before {
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

.stats-item {
    display: flex;
    align-items: center;
    gap: 0.5rem; /* Smaller gap */
    padding: 0.25rem 0.5rem; /* Smaller padding */
    border-radius: 6px; /* Smaller radius */
    transition: var(--transition);
    flex: 1;
    text-align: center;
    position: relative;
}

.stats-item:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 1px;
    height: 60%;
    background: linear-gradient(to bottom, transparent, var(--primary-color), transparent);
}

.stats-item:hover {
    background: rgba(139, 69, 19, 0.05);
    transform: translateY(-1px); /* Smaller transform */
}

.stats-icon {
    font-size: 1.4rem; /* Smaller icon */
    color: var(--gold);
    min-width: 35px; /* Smaller width */
    text-align: center;
}

.stats-content {
    flex: 1;
}

.stats-count {
    font-size: 1.3rem; /* Smaller font */
    font-weight: bold;
    color: var(--dark-color);
    margin-bottom: 0.1rem; /* Smaller margin */
    line-height: 1;
}

.stats-title {
    font-size: 0.8rem; /* Smaller font */
    color: var(--secondary-color);
    font-weight: 600;
    line-height: 1.2;
}

/* Mobile Horizontal Stats - Ultra Compact */
.mobile-stats-horizontal {
    flex-direction: column;
    gap: 0.5rem; /* Smaller gap */
    padding: 0.5rem; /* Smaller padding */
}

.mobile-stats-item {
    width: 100%;
    justify-content: center;
    padding: 0.4rem; /* Smaller padding */
    border-bottom: 1px solid rgba(139, 69, 19, 0.1);
}

.mobile-stats-item:last-child {
    border-bottom: none;
}

.mobile-stats-item::after {
    display: none;
}

.mobile-stats-item .stats-icon {
    font-size: 1.2rem; /* Smaller icon */
    min-width: 30px; /* Smaller width */
}

.mobile-stats-item .stats-count {
    font-size: 1.1rem; /* Smaller font */
}

.mobile-stats-item .stats-title {
    font-size: 0.7rem; /* Smaller font */
}

/* Mobile Stats Cards (Legacy - keeping for reference) */
.mobile-stats-card {
    padding: 1rem;
    text-align: center;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-left: 4px solid var(--gold);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.95));
    position: relative;
    overflow: hidden;
}

.mobile-stats-card::before {
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

.mobile-stats-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.mobile-stats-card .icon {
    font-size: 2rem;
    color: var(--gold);
    margin-bottom: 0.5rem;
}

.mobile-stats-card .count {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--dark-color);
    margin-bottom: 0.25rem;
}

.mobile-stats-card .title {
    font-size: 0.85rem;
    color: var(--secondary-color);
    font-weight: 600;
}

/* Stats Container Animation */
.stats-container {
    transition: all 0.3s ease;
    overflow: hidden;
}

.stats-container.show {
    animation: slideDown 0.3s ease-out;
}

.stats-container.hide {
    animation: slideUp 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
        max-height: 0;
    }
    to {
        opacity: 1;
        transform: translateY(0);
        max-height: 500px;
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        transform: translateY(0);
        max-height: 500px;
    }
    to {
        opacity: 0;
        transform: translateY(-20px);
        max-height: 0;
    }
}

/* Alert styles */
.alert {
    border-radius: var(--border-radius);
    border: none;
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border-left: 4px solid #dc3545;
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tombol Selesai
    document.addEventListener('click', function(e) {
        if (e.target.closest('.complete-btn')) {
            var button = e.target.closest('.complete-btn');
            var tanggal = button.getAttribute('data-tanggal');
            var jam = button.getAttribute('data-jam');
            var flagDoc = button.getAttribute('data-flag-doc');
            
            // Konfirmasi sebelum melakukan update
            if (!confirm('Apakah Anda yakin ingin menandai jadwal ' + tanggal + ' jam ' + jam + ' sebagai selesai? Ini akan mengupdate status semua peserta pada jadwal tersebut.')) {
                return;
            }
            
            // Set loading state
            button.classList.add('loading');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner"></i> Memproses...';
            
            // Kirim AJAX request
            fetch('<?= base_url("dashboard/mark_schedule_complete") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    tanggal: tanggal,
                    jam: jam,
                    flag_doc: flagDoc
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Tampilkan pesan sukses
                    showAlert('success', data.message);
                    
                    // Disable tombol setelah berhasil
                    button.classList.remove('btn-success');
                    button.classList.add('btn-secondary');
                    button.innerHTML = '<i class="fas fa-check"></i> Selesai';
                    button.disabled = true;
                    
                    // Reload halaman setelah 2 detik untuk memperbarui data
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    // Tampilkan pesan error
                    showAlert('danger', data.message);
                    
                    // Reset tombol
                    button.classList.remove('loading');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check"></i> Selesai';
                }
            })
            .catch(error => {
                // Tampilkan pesan error
                showAlert('danger', 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.');
                
                // Reset tombol
                button.classList.remove('loading');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-check"></i> Selesai';
            });
        }
    });

    // Handle tombol Toggle Statistics
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-stats-btn')) {
            var button = e.target.closest('.toggle-stats-btn');
            var target = button.getAttribute('data-target');
            var action = button.getAttribute('data-action');
            var targetElement = document.getElementById(target);
            var toggleText = button.querySelector('.toggle-text');
            var icon = button.querySelector('i');
            
            if (targetElement) {
                if (action === 'toggle' || action === 'hide') {
                    if (targetElement.style.display === 'none' || targetElement.style.display === '') {
                        // Show statistics
                        targetElement.style.display = 'block';
                        targetElement.classList.add('show');
                        targetElement.classList.remove('hide');
                        
                        // Update button
                        button.classList.add('active');
                        icon.className = 'fas fa-eye';
                        toggleText.textContent = 'Sembunyikan';
                        button.setAttribute('data-action', 'hide');
                        
                        // Save state to localStorage
                        localStorage.setItem('stats_' + target + '_visible', 'true');
                    } else {
                        // Hide statistics
                        targetElement.classList.add('hide');
                        targetElement.classList.remove('show');
                        
                        setTimeout(function() {
                            targetElement.style.display = 'none';
                        }, 300);
                        
                        // Update button
                        button.classList.remove('active');
                        icon.className = 'fas fa-eye-slash';
                        toggleText.textContent = 'Tampilkan';
                        button.setAttribute('data-action', 'toggle');
                        
                        // Save state to localStorage
                        localStorage.setItem('stats_' + target + '_visible', 'false');
                    }
                }
            }
        }
    });

    // Load saved state from localStorage
    function loadStatsState() {
        var desktopStats = document.getElementById('desktop-stats');
        var mobileStats = document.getElementById('mobile-stats');
        var desktopBtn = document.querySelector('[data-target="desktop-stats"]');
        var mobileBtn = document.querySelector('[data-target="mobile-stats"]');
        
        // Check desktop state
        if (desktopStats && desktopBtn) {
            var desktopVisible = localStorage.getItem('stats_desktop-stats_visible');
            if (desktopVisible === 'true') {
                desktopStats.style.display = 'block';
                desktopStats.classList.add('show');
                desktopBtn.classList.add('active');
                desktopBtn.querySelector('i').className = 'fas fa-eye';
                desktopBtn.querySelector('.toggle-text').textContent = 'Sembunyikan';
                desktopBtn.setAttribute('data-action', 'hide');
            }
        }
        
        // Check mobile state
        if (mobileStats && mobileBtn) {
            var mobileVisible = localStorage.getItem('stats_mobile-stats_visible');
            if (mobileVisible === 'true') {
                mobileStats.style.display = 'block';
                mobileStats.classList.add('show');
                mobileBtn.classList.add('active');
                mobileBtn.querySelector('i').className = 'fas fa-eye';
                mobileBtn.querySelector('.toggle-text').textContent = 'Sembunyikan';
                mobileBtn.setAttribute('data-action', 'hide');
            }
        }
    }

    // Initialize stats state
    loadStatsState();
    
    // Layout is now default ultra compact
    
    // Fungsi untuk menampilkan alert
    function showAlert(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + '"></i> ' +
                        message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
        
        // Tambahkan alert di bagian atas content
        var contentBody = document.querySelector('.content-body');
        if (contentBody) {
            contentBody.insertAdjacentHTML('afterbegin', alertHtml);
        }
        
            // Auto hide alert setelah 5 detik
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.opacity = '0';
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        });
    }, 5000);
}

// Layout is now default ultra compact for all devices
});
</script> 