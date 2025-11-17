<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' : '' ?>Sistem Pendataan Kunjungan Peserta Haji dan Umrah</title>
    
    <!-- Favicon Configuration -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/img/hajj_logo.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/img/hajj_logo.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('assets/img/hajj_logo.png') ?>" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/hajj_logo.png') ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #1e3a5f;
            --secondary-color: #2c5282;
            --accent-color: #3b82f6;
            --dark-blue: #1e3a5f;
            --light-blue: #e0f2fe;
            --white: #ffffff;
            --black: #1a1a1a;
            --blue-medium: #3b82f6;
            --blue-light: #60a5fa;
            --blue-dark: #1e40af;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(30, 58, 95, 0.1), rgba(44, 82, 130, 0.1));
        }
        
        /* Sidebar styles - Ultra Compact */
        .sidebar {
            width: 280px;
            background: linear-gradient(to bottom, var(--dark-blue), var(--secondary-color));
            color: white;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            border-right: 2px solid var(--accent-color);
        }
        
        .sidebar .sidebar-header {
            padding: 15px; /* Reduced from 20px */
            background: rgba(0, 0, 0, 0.3);
            text-align: center;
            border-bottom: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .sidebar .sidebar-header h3 {
            margin: 0;
            font-size: 1.3rem; /* Reduced from 1.5rem */
            color: var(--white);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .sidebar .user-info {
            padding: 3px 0; /* Reduced from 5px */
            font-size: 0.8rem; /* Reduced from 0.85rem */
        }
        
        .sidebar ul.components {
            padding: 12px 0; /* Reduced from 15px */
            flex: 1;
            overflow-y: auto;
        }
        
        .sidebar ul li {
            padding: 0;
            list-style-type: none;
            margin-bottom: 3px; /* Reduced from 5px */
        }
        
        .sidebar ul li a {
            padding: 10px 15px; /* Reduced from 12px 20px */
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem; /* Reduced from 0.95rem */
            border-left: 3px solid transparent;
            border-radius: 0 5px 5px 0;
        }
        
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            color: var(--white);
            background: rgba(59, 130, 246, 0.2);
            border-left: 3px solid var(--accent-color);
            transform: translateX(5px);
        }
        
        /* Global Persistent Error Alert Styles */
        .persistent-error {
            border-left: 5px solid #dc3545 !important;
            background-color: #f8d7da !important;
            border-color: #f5c6cb !important;
            color: #721c24 !important;
            animation: none !important;
            transition: none !important;
        }
        
        .persistent-error .btn-close {
            color: #721c24 !important;
            opacity: 0.8;
        }
        
        .persistent-error .btn-close:hover {
            opacity: 1;
        }
        
        /* Disable auto-dismiss for error alerts */
        .persistent-error.alert-dismissible {
            padding-right: 1rem;
        }
        
        /* Ensure error alert stays visible */
        .persistent-error.show {
            display: block !important;
            opacity: 1 !important;
        }
        
        /* Custom animation for error alert */
        @keyframes errorPulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        
        .persistent-error {
            animation: errorPulse 2s infinite;
        }
        
        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: var(--white);
        }
        
        .sidebar ul.submenu {
            padding-left: 8px; /* Reduced from 10px */
            background: rgba(0, 0, 0, 0.2);
            border-radius: 0 0 5px 0;
            margin-bottom: 8px; /* Reduced from 10px */
        }
        
        .sidebar ul.submenu li a {
            padding: 8px 12px 8px 40px; /* Reduced from 10px 15px 10px 45px */
            font-size: 0.85rem; /* Reduced from 0.9rem */
            position: relative;
        }
        
        .sidebar ul.submenu li a i {
            font-size: 0.75rem; /* Reduced from 0.8rem */
            width: 15px;
        }
        
        .nav-section-divider {
            height: 1px;
            background: rgba(59, 130, 246, 0.3);
            margin: 12px 15px; /* Reduced from 15px 20px */
        }
        
        .sidebar-footer {
            text-align: center;
            padding: 12px; /* Reduced from 15px */
            background: rgba(0, 0, 0, 0.3);
            font-size: 0.7rem; /* Reduced from 0.75rem */
            color: rgba(255, 255, 255, 0.7);
            border-top: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        /* Content styles - Ultra Compact */
        .content {
            width: calc(100% - 280px);
            margin-left: 280px;
            padding: 15px; /* Reduced from 20px */
            transition: all 0.3s;
        }
        
        .content-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 15px; /* Reduced from 15px 20px */
            border-radius: 8px; /* Reduced from 10px */
            margin-bottom: 15px; /* Reduced from 20px */
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); /* Reduced shadow */
            border: 1px solid var(--light-blue);
        }
        
        .content-header h1 {
            margin: 0;
            font-size: 1.5rem; /* Reduced from 1.8rem */
            color: var(--dark-blue);
            font-weight: bold;
        }
        
        .content-body {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px; /* Reduced from 20px */
            border-radius: 8px; /* Reduced from 10px */
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); /* Reduced shadow */
            border: 1px solid var(--light-blue);
        }
        
        /* Card styles for dashboard - Ultra Compact */
        .dashboard-card {
            border-radius: 8px; /* Reduced from 10px */
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); /* Reduced shadow */
            padding: 15px; /* Reduced from 20px */
            margin-bottom: 15px; /* Reduced from 20px */
            transition: all 0.3s;
            border-left: 4px solid var(--accent-color); /* Reduced from 5px */
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.95));
        }
        
        .dashboard-card:hover {
            transform: translateY(-3px); /* Reduced from -5px */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15); /* Reduced shadow */
        }
        
        .dashboard-card .icon {
            font-size: 2.5rem; /* Reduced from 3rem */
            color: var(--accent-color);
        }
        
        .dashboard-card .count {
            font-size: 2rem; /* Reduced from 2.5rem */
            font-weight: bold;
            color: var(--dark-blue);
        }
        
        .dashboard-card .title {
            font-size: 1rem; /* Reduced from 1.2rem */
            color: var(--secondary-color);
        }
        
        /* Table styling */
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: 2px solid var(--accent-color);
            font-weight: 600;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        /* Form styling */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Alert styling - Ultra Compact */
        .alert {
            margin-bottom: 15px; /* Reduced from 20px */
            border-radius: 8px; /* Reduced from 10px */
            border: none;
            padding: 10px 15px; /* Reduced padding */
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.1);
            border-left: 3px solid var(--success-color); /* Reduced from 4px */
            color: var(--dark-blue);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-left: 3px solid var(--danger-color); /* Reduced from 4px */
            color: var(--dark-blue);
        }
        
        /* Mobile Sidebar Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: var(--primary-light);
            transform: scale(1.1);
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            transition: all 0.3s ease;
        }
        
        /* Responsive adjustments - Ultra Compact */
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                text-align: left;
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar .sidebar-header h3 {
                display: block;
            }
            
            .sidebar ul li a span {
                display: inline;
            }
            
            .sidebar ul li a i {
                margin-right: 8px; /* Reduced from 10px */
                font-size: 0.9rem; /* Reduced from 1rem */
            }
            
            /* Mobile sidebar optimizations */
            .sidebar .sidebar-header {
                padding: 12px; /* Reduced from 15px */
            }
            
            .sidebar .sidebar-header h3 {
                font-size: 1.1rem; /* Reduced from 1.3rem */
            }
            
            .sidebar ul.components {
                padding: 10px 0; /* Reduced from 12px */
            }
            
            .sidebar ul li a {
                padding: 8px 12px; /* Reduced from 10px 15px */
                font-size: 0.85rem; /* Reduced from 0.9rem */
            }
            
            .sidebar ul.submenu li a {
                padding: 6px 10px 6px 35px; /* Reduced from 8px 12px 8px 40px */
                font-size: 0.8rem; /* Reduced from 0.85rem */
            }
            
            .sidebar-footer {
                padding: 10px; /* Reduced from 12px */
                font-size: 0.65rem; /* Reduced from 0.7rem */
            }
            
            .content {
                width: 100%;
                margin-left: 0;
                padding-top: 70px; /* Reduced from 80px */
                padding: 10px; /* Reduced from 15px */
            }
            
            .content-header {
                padding: 8px 12px; /* Reduced from 10px 15px */
                margin-bottom: 10px; /* Reduced from 15px */
            }
            
            .content-header h1 {
                font-size: 1.3rem; /* Reduced from 1.5rem */
            }
            
            .content-body {
                padding: 10px; /* Reduced from 15px */
            }
            
            .dashboard-card {
                padding: 10px; /* Reduced from 15px */
                margin-bottom: 10px; /* Reduced from 15px */
            }
            
            .dashboard-card .icon {
                font-size: 2rem; /* Reduced from 2.5rem */
            }
            
            .dashboard-card .count {
                font-size: 1.5rem; /* Reduced from 2rem */
            }
            
            .dashboard-card .title {
                font-size: 0.9rem; /* Reduced from 1rem */
            }
            
            .alert {
                margin-bottom: 10px; /* Reduced from 15px */
                padding: 8px 12px; /* Reduced from 10px 15px */
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        /* Ultra Small Mobile - Extra Compact */
        @media (max-width: 480px) {
            .content {
                padding: 8px; /* Even smaller */
                padding-top: 60px; /* Reduced from 70px */
            }
            
            .content-header {
                padding: 6px 10px; /* Even smaller */
                margin-bottom: 8px; /* Even smaller */
            }
            
            .content-header h1 {
                font-size: 1.1rem; /* Even smaller */
            }
            
            .content-body {
                padding: 8px; /* Even smaller */
            }
            
            .dashboard-card {
                padding: 8px; /* Even smaller */
                margin-bottom: 8px; /* Even smaller */
            }
            
            .dashboard-card .icon {
                font-size: 1.5rem; /* Even smaller */
            }
            
            .dashboard-card .count {
                font-size: 1.2rem; /* Even smaller */
            }
            
            .dashboard-card .title {
                font-size: 0.8rem; /* Even smaller */
            }
            
            .alert {
                margin-bottom: 8px; /* Even smaller */
                padding: 6px 10px; /* Even smaller */
                font-size: 0.85rem; /* Smaller font */
            }
            
            /* Sidebar toggle button optimization */
            .sidebar-toggle {
                width: 45px; /* Smaller button */
                height: 45px; /* Smaller button */
                font-size: 1rem; /* Smaller icon */
                top: 15px; /* Closer to top */
                left: 15px; /* Closer to left */
            }
            
            /* Ultra small mobile sidebar optimizations */
            .sidebar .sidebar-header {
                padding: 10px; /* Even smaller */
            }
            
            .sidebar .sidebar-header h3 {
                font-size: 1rem; /* Even smaller */
            }
            
            .sidebar ul.components {
                padding: 8px 0; /* Even smaller */
            }
            
            .sidebar ul li a {
                padding: 6px 10px; /* Even smaller */
                font-size: 0.8rem; /* Even smaller */
            }
            
            .sidebar ul.submenu li a {
                padding: 5px 8px 5px 30px; /* Even smaller */
                font-size: 0.75rem; /* Even smaller */
            }
            
            .sidebar-footer {
                padding: 8px; /* Even smaller */
                font-size: 0.6rem; /* Even smaller */
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Main Content Container -->
    <div class="content">
        <!-- Alert messages -->
        <?php if (function_exists('get_instance') && get_instance()->session && get_instance()->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= get_instance()->session->flashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (function_exists('get_instance') && get_instance()->session && get_instance()->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= get_instance()->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Content Header -->
        <div class="content-header">
            <div class="row">
                <div class="col-md-6">
                    <h1><?= isset($title) ? $title : 'Dashboard' ?></h1>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-secondary">
                        <i class="fas fa-user me-1" style="color: var(--accent-color);"></i> 
                        <?= function_exists('get_instance') && get_instance()->session ? get_instance()->session->userdata('nama_lengkap') : '' ?> 
                        (<?= function_exists('get_instance') && get_instance()->session ? get_instance()->session->userdata('role') : '' ?>)
                    </span>
                </div>
            </div>
        </div>
        
