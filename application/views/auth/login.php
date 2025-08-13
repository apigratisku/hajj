<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pendataan Kunjungan Peserta Haji dan Umrah</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="<?= base_url('assets/img/hajj_logo.png') ?>" type="image/x-icon">
    <style>
        :root {
            --primary-color: #A0522D;
            --secondary-color: #8B7355;
            --accent-color: #CD853F;
            --dark-brown: #654321;
            --light-brown: #F5E6D3;
            --gold: #DAA520;
            --black: #2F2F2F;
            --cream: #FDF5E6;
            --soft-brown: #DEB887;
            --warm-brown: #D2B48C;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, rgba(160, 82, 45, 0.3), rgba(139, 115, 85, 0.4));
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Background Images with Overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                linear-gradient(135deg, 
                    rgba(160, 82, 45, 0.4), 
                    rgba(139, 115, 85, 0.5)
                ),
                url('https://awsimages.detik.net.id/community/media/visual/2020/11/06/ilustrasi-ibadah-haji-dan-umrah-1.jpeg?w=600&q=90');
            background-size: cover;
            background-position: center;
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
                radial-gradient(circle at 30% 70%, rgba(218, 165, 32, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(160, 82, 45, 0.05) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }

        /* Alternative background for better performance */
        @media (max-width: 768px) {
            body::before {
                background: 
                    linear-gradient(135deg, 
                        rgba(160, 82, 45, 0.5), 
                        rgba(139, 115, 85, 0.6)
                    ),
                    url('https://images.unsplash.com/photo-1542810634-71277d95dcbb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80');
            }
        }

        @keyframes backgroundShift {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.05) rotate(1deg); }
        }

        /* Floating Elements Effect */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 15s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            font-size: 3rem;
            color: var(--gold);
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 20%;
            right: 15%;
            font-size: 2.5rem;
            color: var(--gold);
            animation-delay: 3s;
        }

        .floating-element:nth-child(3) {
            bottom: 30%;
            left: 20%;
            font-size: 2rem;
            color: var(--gold);
            animation-delay: 6s;
        }

        .floating-element:nth-child(4) {
            bottom: 20%;
            right: 10%;
            font-size: 2.5rem;
            color: var(--gold);
            animation-delay: 9s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background: rgba(62, 39, 35, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid var(--gold);
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
        
        .hajj-logo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--gold), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 25px rgba(218, 165, 32, 0.4);
            position: relative;
            animation: logoGlow 3s ease-in-out infinite;
            overflow: hidden;
        }

        .hajj-logo::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(135deg, var(--gold), var(--accent-color), var(--gold));
            border-radius: 50%;
            z-index: -1;
            animation: logoRotate 4s linear infinite;
        }

        .hajj-logo::after {
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
            0%, 100% { box-shadow: 0 10px 25px rgba(218, 165, 32, 0.4); }
            50% { box-shadow: 0 15px 35px rgba(218, 165, 32, 0.7); }
        }

        @keyframes logoRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .hajj-logo i {
            font-size: 3rem;
            color: var(--dark-brown);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            z-index: 2;
            position: relative;
        }
        
        .login-logo h2 {
            font-size: 1.8rem;
            margin-top: 15px;
            color: var(--gold);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .login-logo h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--accent-color));
            margin: 10px auto 0;
            border-radius: 1px;
        }
        
        .login-form .form-control {
            border: 2px solid var(--light-brown);
            border-radius: 10px;
            padding: 12px 15px;
            height: auto;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            color: var(--dark-brown);
            font-weight: 500;
        }

        .login-form .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(218, 165, 32, 0.25);
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
            border: 2px solid var(--gold);
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
            box-shadow: 0 5px 15px rgba(218, 165, 32, 0.4);
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        }
        
        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            border: none;
            background: rgba(62, 39, 35, 0.9);
            backdrop-filter: blur(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .alert-danger {
            background: rgba(139, 69, 19, 0.9);
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
            color: var(--secondary-color);
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
            background: var(--light-brown);
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
            
            .hajj-logo {
                width: 100px;
                height: 100px;
            }
            
            .hajj-logo i {
                font-size: 2.5rem;
            }
            
            .login-logo h2 {
                font-size: 1.5rem;
            }
            
            .floating-elements {
                display: none;
            }
            
            .hajj-symbols {
                top: 10px;
                right: 10px;
                font-size: 1.2rem;
            }
        }

        /* Additional Hajj-themed elements */
        .hajj-symbols {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5rem;
            color: var(--gold);
            opacity: 0.7;
        }

        .hajj-symbols i {
            margin-left: 10px;
            animation: symbolPulse 2s ease-in-out infinite;
        }

        .hajj-symbols i:nth-child(2) {
            animation-delay: 0.5s;
        }

        .hajj-symbols i:nth-child(3) {
            animation-delay: 1s;
        }

        @keyframes symbolPulse {
            0%, 100% { transform: scale(1); opacity: 0.7; }
            50% { transform: scale(1.2); opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Floating Elements -->
    <div class="floating-elements">
        <div class="floating-element">🕋</div>
        <div class="floating-element">☪️</div>
        <div class="floating-element">🕋</div>
        <div class="floating-element">☪️</div>
    </div>

    <!-- Hajj Symbols -->
    <div class="hajj-symbols">
        <i class="fas fa-kaaba"></i>
        <i class="fas fa-star-and-crescent"></i>
        <i class="fas fa-mosque"></i>
    </div>

    <div class="login-container">
        <div class="login-logo">
            <div class="hajj-logo">
                <i class="fas fa-kaaba"></i>
            </div>
            <h2>Sistem Pendataan Kunjungan Peserta Haji dan Umrah</h2>
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