<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' : '' ?>Sistem Pendataan Kunjungan Peserta Haji dan Umrah</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #654321;
            --accent-color: #D2691E;
            --dark-brown: #3E2723;
            --light-brown: #D7CCC8;
            --gold: #DAA520;
            --black: #1a1a1a;
            --cream: #F5F5DC;
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
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.1), rgba(62, 39, 35, 0.1));
        }
        
        /* Sidebar styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(to bottom, var(--dark-brown), var(--secondary-color));
            color: white;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            border-right: 2px solid var(--gold);
        }
        
        .sidebar .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            text-align: center;
            border-bottom: 1px solid rgba(218, 165, 32, 0.3);
        }
        
        .sidebar .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--gold);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .sidebar .user-info {
            padding: 5px 0;
            font-size: 0.85rem;
        }
        
        .sidebar ul.components {
            padding: 15px 0;
            flex: 1;
            overflow-y: auto;
        }
        
        .sidebar ul li {
            padding: 0;
            list-style-type: none;
            margin-bottom: 5px;
        }
        
        .sidebar ul li a {
            padding: 12px 20px;
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
            border-radius: 0 5px 5px 0;
        }
        
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            color: var(--gold);
            background: rgba(218, 165, 32, 0.2);
            border-left: 3px solid var(--gold);
            transform: translateX(5px);
        }
        
        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: var(--gold);
        }
        
        .sidebar ul.submenu {
            padding-left: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 0 0 5px 0;
            margin-bottom: 10px;
        }
        
        .sidebar ul.submenu li a {
            padding: 10px 15px 10px 45px;
            font-size: 0.9rem;
            position: relative;
        }
        
        .sidebar ul.submenu li a i {
            font-size: 0.8rem;
            width: 15px;
        }
        
        .nav-section-divider {
            height: 1px;
            background: rgba(218, 165, 32, 0.3);
            margin: 15px 20px;
        }
        
        .sidebar-footer {
            text-align: center;
            padding: 15px;
            background: rgba(0, 0, 0, 0.3);
            font-size: 0.75rem;
            color: rgba(218, 165, 32, 0.7);
            border-top: 1px solid rgba(218, 165, 32, 0.3);
        }
        
        /* Content styles */
        .content {
            width: calc(100% - 280px);
            margin-left: 280px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .content-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--light-brown);
        }
        
        .content-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--dark-brown);
            font-weight: bold;
        }
        
        .content-body {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--light-brown);
        }
        
        /* Card styles for dashboard */
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
            border-left: 5px solid var(--gold);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.95));
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .dashboard-card .icon {
            font-size: 3rem;
            color: var(--gold);
        }
        
        .dashboard-card .count {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--dark-brown);
        }
        
        .dashboard-card .title {
            font-size: 1.2rem;
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
            border-bottom: 2px solid var(--gold);
            font-weight: 600;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: rgba(218, 165, 32, 0.1);
        }
        
        /* Form styling */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Alert styling */
        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            border: none;
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.1);
            border-left: 4px solid var(--success-color);
            color: var(--dark-brown);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-left: 4px solid var(--danger-color);
            color: var(--dark-brown);
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
        
        /* Responsive adjustments */
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
                margin-right: 10px;
                font-size: 1rem;
            }
            
            .content {
                width: 100%;
                margin-left: 0;
                padding-top: 80px;
            }
            
            .sidebar-overlay.show {
                display: block;
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
                        <i class="fas fa-user me-1" style="color: var(--gold);"></i> 
                        <?= function_exists('get_instance') && get_instance()->session ? get_instance()->session->userdata('nama_lengkap') : '' ?> 
                        (<?= function_exists('get_instance') && get_instance()->session ? get_instance()->session->userdata('role') : '' ?>)
                    </span>
                </div>
            </div>
        </div>
        
