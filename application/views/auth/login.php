<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="<?= base_url('assets/img/hajj_logo.png') ?>" type="image/x-icon">
    <style>
        :root {
            --primary-color: #1e3a5f;
            --secondary-color: #2c5282;
            --accent-color: #3b82f6;
            --dark-blue: #1e3a5f;
            --light-blue: #e0f2fe;
            --white: #ffffff;
            --black: #2F2F2F;
            --blue-medium: #3b82f6;
            --blue-light: #60a5fa;
            --blue-dark: #1e40af;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 50%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Background Gradient Overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(30, 58, 95, 0.8), rgba(44, 82, 130, 0.6), rgba(255, 255, 255, 0.3));
            z-index: -1;
            animation: backgroundShift 20s ease-in-out infinite;
        }

        /* Additional background layer for depth */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 30% 70%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(30, 58, 95, 0.1) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }

        /* Alternative background for better performance */
        @media (max-width: 768px) {
            body::before {
                background: linear-gradient(135deg, rgba(30, 58, 95, 0.9), rgba(44, 82, 130, 0.7), rgba(255, 255, 255, 0.4));
            }
        }

        @keyframes backgroundShift {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.05) rotate(1deg); }
        }

        
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background: rgba(30, 58, 95, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid var(--accent-color);
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .admin-logo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
            position: relative;
            animation: logoGlow 3s ease-in-out infinite;
            overflow: hidden;
        }

        .admin-logo::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color), var(--accent-color));
            border-radius: 50%;
            z-index: -1;
            animation: logoRotate 4s linear infinite;
        }

        .admin-logo::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
        }

        @keyframes logoGlow {
            0%, 100% { box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4); }
            50% { box-shadow: 0 15px 35px rgba(59, 130, 246, 0.7); }
        }

        @keyframes logoRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .admin-logo i {
            font-size: 3rem;
            color: var(--white);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            z-index: 2;
            position: relative;
        }
        
        .login-logo h2 {
            font-size: 1.8rem;
            margin-top: 15px;
            color: var(--white);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .login-logo h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, var(--accent-color), var(--secondary-color));
            margin: 10px auto 0;
            border-radius: 1px;
        }
        
        .login-form .form-control {
            border: 2px solid var(--light-blue);
            border-radius: 10px;
            padding: 12px 15px;
            height: auto;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            color: var(--dark-blue);
            font-weight: 500;
        }

        .login-form .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 1);
        }
        
        .login-form .btn {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: 2px solid var(--accent-color);
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-form .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-form .btn:hover::before {
            left: 100%;
        }

        .login-form .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        }
        
        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            border: none;
            background: rgba(30, 58, 95, 0.9);
            backdrop-filter: blur(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .alert-danger {
            background: rgba(30, 58, 95, 0.9);
            border-left: 4px solid var(--accent-color);
        }

        .btn-close {
            transition: transform 0.3s ease;
            filter: invert(1);
        }

        .btn-close:hover {
            transform: rotate(90deg);
        }

        /* Input placeholder animation */
        .form-control::placeholder {
            transition: opacity 0.3s ease, transform 0.3s ease;
            color: var(--blue-medium);
        }

        .form-control:focus::placeholder {
            opacity: 0.5;
            transform: translateX(10px);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-blue);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Mobile Responsiveness */
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 20px;
            }
            
            .admin-logo {
                width: 100px;
                height: 100px;
            }
            
            .admin-logo i {
                font-size: 2.5rem;
            }
            
            .login-logo h2 {
                font-size: 1.5rem;
            }
        }

    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div class="admin-logo">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2>Sistem Admin</h2>
        </div>
        
        <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= validation_errors() ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="login-form">
            <?= form_open('auth/login') ?>
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" value="<?= set_value('username') ?>" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            <?= form_close() ?>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Add smooth fade-in animation on page load
            $('.login-container').hide().fadeIn(1000);
        });
    </script>
</body>
</html> 