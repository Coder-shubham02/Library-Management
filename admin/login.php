<?php
session_start();

// Agar already logged in hai to dashboard par bhejo
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

require "config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'] ?? $admin['username'];
                $_SESSION['login_time'] = time();

                // Remember me functionality
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), "/", "", true, true); // 30 days, secure, httponly
                    
                    // Store token in database (implement if needed)
                }

                $success = "Access granted! Redirecting to dashboard...";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                </script>";

            } else {
                $error = "Invalid credentials. Please try again.";
            }

        } else {
            $error = "Account not found in our system.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>LibraryPro X | Enterprise Admin Login</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* Enterprise Grade CSS Variables */
        :root {
            /* Light Theme - Professional */
            --bg-primary: #f8fafd;
            --bg-secondary: #ffffff;
            --text-primary: #1a2639;
            --text-secondary: #5a6a7e;
            --text-muted: #8a9bb5;
            --card-bg: #ffffff;
            --input-bg: #f9fcff;
            --border-color: #e9eef4;
            --primary-color: #2a41cb;
            --primary-light: #eef3ff;
            --primary-dark: #1a2b8c;
            --success-color: #0cae74;
            --warning-color: #f9a826;
            --danger-color: #e54c4c;
            --gradient-1: linear-gradient(145deg, #2a41cb, #1a2b8c);
            --gradient-2: linear-gradient(145deg, #0cae74, #0a8e5e);
            --shadow-sm: 0 4px 20px rgba(0, 0, 0, 0.02);
            --shadow-md: 0 10px 40px rgba(0, 0, 0, 0.03);
            --shadow-lg: 0 20px 60px rgba(26, 41, 75, 0.06);
            --shadow-hover: 0 30px 80px rgba(42, 65, 203, 0.12);
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Dark Theme - Professional */
        [data-theme="dark"] {
            --bg-primary: #0b1120;
            --bg-secondary: #151f33;
            --text-primary: #f0f4fc;
            --text-secondary: #b0c0d4;
            --text-muted: #7b8ba3;
            --card-bg: #1a253b;
            --input-bg: #0f1a2f;
            --border-color: #2a3448;
            --primary-light: #1f2b44;
            --shadow-sm: 0 4px 20px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 10px 40px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.1s ease;
        }

        body {
            font-family: var(--font-sans);
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Professional Geometric Background */
        .geometric-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .geo-shape {
            position: absolute;
            opacity: 0.03;
        }

        .geo-shape-1 {
            top: -10%;
            right: -5%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--primary-color) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(80px);
        }

        .geo-shape-2 {
            bottom: -15%;
            left: -5%;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, var(--success-color) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(90px);
        }

        .geo-shape-3 {
            top: 40%;
            left: 30%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--warning-color) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(70px);
        }

        /* Grid Overlay */
        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(var(--border-color) 1px, transparent 1px),
                linear-gradient(90deg, var(--border-color) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.2;
            z-index: 0;
            pointer-events: none;
        }

        /* Main Container */
        .login-container {
            width: 100%;
            max-width: 1400px;
            position: relative;
            z-index: 10;
            animation: fadeInScale 0.8s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.98);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Theme Toggle - Enterprise Style */
        .theme-toggle-enterprise {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 1000;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 40px;
            padding: 6px;
            display: flex;
            gap: 4px;
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(10px);
        }

        .theme-option {
            padding: 10px 20px;
            border-radius: 34px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.2s ease;
        }

        .theme-option i {
            margin-right: 8px;
        }

        .theme-option.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(42, 65, 203, 0.3);
        }

        /* Login Card - Premium Design */
        .login-card {
            background: var(--card-bg);
            border-radius: 48px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            backdrop-filter: blur(20px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            box-shadow: var(--shadow-hover);
        }

        /* Left Side - Premium Branding */
        .premium-brand {
            background: linear-gradient(165deg, #1a2639 0%, #2a3a55 100%);
            padding: 60px 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .premium-brand::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><path d="M30 0 L60 30 L30 60 L0 30 Z" fill="white" fill-opacity="0.03"/></svg>');
            background-size: 60px 60px;
            opacity: 0.3;
        }

        .premium-brand::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            border-radius: 50%;
        }

        .brand-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 100px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .brand-icon-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .brand-icon-wrapper i {
            font-size: 40px;
            color: white;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
        }

        .premium-brand h1 {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: -1px;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .brand-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 40px;
            line-height: 1.6;
            max-width: 90%;
        }

        .enterprise-badges {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .enterprise-badge {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 40px;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            backdrop-filter: blur(5px);
        }

        .enterprise-badge i {
            margin-right: 8px;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Right Side - Premium Form */
        .premium-form {
            padding: 60px 50px;
            background: var(--card-bg);
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header .greeting {
            color: var(--primary-color);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 15px;
            display: block;
        }

        .form-header h2 {
            color: var(--text-primary);
            font-size: 2.4rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Premium Form Elements */
        .premium-input-group {
            margin-bottom: 25px;
        }

        .input-label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-field {
            width: 100%;
            padding: 16px 16px 16px 52px;
            border: 2px solid var(--border-color);
            border-radius: 20px;
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--card-bg);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.2rem;
            transition: color 0.2s ease;
            pointer-events: none;
        }

        .input-field:focus + .input-icon {
            color: var(--primary-color);
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        /* Form Options */
        .form-options-premium {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 0;
        }

        .checkbox-premium {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .checkbox-premium input {
            width: 20px;
            height: 20px;
            accent-color: var(--primary-color);
            cursor: pointer;
        }

        .checkbox-premium span {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .forgot-link-premium {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-link-premium:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Premium Button */
        .btn-premium {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 24px;
            background: var(--gradient-1);
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px -5px var(--primary-color);
        }

        .btn-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-premium:hover::before {
            left: 100%;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -5px var(--primary-color);
        }

        .btn-premium:active {
            transform: translateY(0);
        }

        .btn-premium i {
            margin-right: 10px;
        }

        /* Alert Messages */
        .alert-premium {
            padding: 16px 20px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-premium.error {
            background: rgba(229, 76, 76, 0.08);
            border: 1px solid rgba(229, 76, 76, 0.2);
            color: #e54c4c;
        }

        .alert-premium.success {
            background: rgba(12, 174, 116, 0.08);
            border: 1px solid rgba(12, 174, 116, 0.2);
            color: #0cae74;
        }

        .alert-premium i {
            font-size: 1.2rem;
        }

        /* Security Badges */
        .security-badges {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid var(--border-color);
        }

        .security-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .security-badge i {
            color: var(--success-color);
        }

        /* Premium Footer */
        .premium-footer {
            margin-top: 30px;
            text-align: center;
        }

        .premium-footer p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .developer-credit {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            background: var(--primary-light);
            border-radius: 40px;
            color: var(--primary-color);
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        .developer-credit:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .developer-credit i {
            font-size: 0.9rem;
        }

        /* Loading Spinner */
        .spinner-premium {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .premium-brand h1 {
                font-size: 2.5rem;
            }
            
            .form-header h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 992px) {
            .premium-brand {
                padding: 40px 30px;
            }
            
            .premium-form {
                padding: 40px 30px;
            }
        }

        @media (max-width: 768px) {
            .login-card {
                border-radius: 32px;
            }
            
            .premium-brand {
                padding: 40px 25px;
                text-align: center;
            }
            
            .brand-description {
                max-width: 100%;
            }
            
            .enterprise-badges {
                justify-content: center;
            }
            
            .form-header h2 {
                font-size: 1.8rem;
            }
            
            .theme-toggle-enterprise {
                top: 16px;
                right: 16px;
                padding: 4px;
            }
            
            .theme-option {
                padding: 8px 16px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .premium-form {
                padding: 30px 20px;
            }
            
            .input-field {
                padding: 14px 14px 14px 48px;
            }
            
            .security-badges {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 8px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Glassmorphism Effects */
        .glass-premium {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <!-- Geometric Background -->
    <div class="geometric-bg">
        <div class="geo-shape geo-shape-1"></div>
        <div class="geo-shape geo-shape-2"></div>
        <div class="geo-shape geo-shape-3"></div>
    </div>
    
    <!-- Grid Overlay -->
    <div class="grid-overlay"></div>

    <!-- Enterprise Theme Toggle -->
    <div class="theme-toggle-enterprise" id="themeToggle">
        <div class="theme-option active" data-theme="light" id="lightTheme">
            <i class="fas fa-sun"></i>
            <span>Light</span>
        </div>
        <div class="theme-option" data-theme="dark" id="darkTheme">
            <i class="fas fa-moon"></i>
            <span>Dark</span>
        </div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="row g-0 login-card">
            <!-- Left Side - Premium Branding -->
            <div class="col-lg-5 premium-brand">
                <div data-aos="fade-right" data-aos-duration="800">
                    <div class="brand-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>Enterprise Edition v2.0</span>
                    </div>
                    
                    <div class="brand-icon-wrapper">
                        <i class="fas fa-book-open"></i>
                    </div>
                    
                    <h1>LibraryPro<br>X</h1>
                    
                    <p class="brand-description">
                        Complete library ecosystem for modern educational institutions. Manage students, seats, payments with enterprise-grade security.
                    </p>
                    
                    <div class="enterprise-badges">
                        <span class="enterprise-badge">
                            <i class="fas fa-check-circle"></i> 500+ Libraries
                        </span>
                        <span class="enterprise-badge">
                            <i class="fas fa-check-circle"></i> 99.9% Uptime
                        </span>
                        <span class="enterprise-badge">
                            <i class="fas fa-check-circle"></i> 24/7 Support
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Side - Premium Form -->
            <div class="col-lg-7 premium-form">
                <div class="form-header" data-aos="fade-left" data-aos-duration="800">
                    <span class="greeting">WELCOME BACK</span>
                    <h2>Secure Admin Access</h2>
                    <p>Enter your credentials to access the library management dashboard. All activities are logged for security purposes.</p>
                </div>

                <!-- Error/Success Messages -->
                <?php if (!empty($error)): ?>
                    <div class="alert-premium error" data-aos="fade-in">
                        <i class="fas fa-shield-exclamation"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert-premium success" data-aos="fade-in">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm" data-aos="fade-left" data-aos-duration="800" data-aos-delay="100">
                    <!-- Username Field -->
                    <div class="premium-input-group">
                        <label class="input-label">Username or Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" 
                                   class="input-field" 
                                   name="username" 
                                   placeholder="Enter your username or email"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required
                                   autofocus>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="premium-input-group">
                        <label class="input-label">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   class="input-field" 
                                   name="password" 
                                   id="password"
                                   placeholder="Enter your password"
                                   required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>

                    <!-- Form Options -->
                    <div class="form-options-premium">
                        <label class="checkbox-premium">
                            <input type="checkbox" name="remember">
                            <span>Remember this device</span>
                        </label>
                        <a href="#" class="forgot-link-premium">Forgot password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-premium" id="loginBtn">
                        <i class="fas fa-lock-open"></i>
                        <span class="btn-text">Access Dashboard</span>
                        <span class="spinner-premium" style="display: none;"></span>
                    </button>
                </form>

                <!-- Security Badges -->
                <div class="security-badges">
                    <span class="security-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>256-bit SSL Encrypted</span>
                    </span>
                    <span class="security-badge">
                        <i class="fas fa-fingerprint"></i>
                        <span>Biometric Ready</span>
                    </span>
                    <span class="security-badge">
                        <i class="fas fa-clock"></i>
                        <span>Session Timeout: 30 min</span>
                    </span>
                </div>

                <!-- Premium Footer -->
                <div class="premium-footer">
                    <p>© 2026 LibraryPro X. All rights reserved.</p>
                    <a href="#" class="developer-credit">
                        <i class="fas fa-code"></i>
                        <span>Designed & Developed by Shubham</span>
                        <i class="fas fa-heart" style="color: #e54c4c;"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            easing: 'cubic-bezier(0.23, 1, 0.32, 1)'
        });

        // Theme Toggle
        const lightTheme = document.getElementById('lightTheme');
        const darkTheme = document.getElementById('darkTheme');
        const htmlElement = document.documentElement;

        // Check for saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-theme', savedTheme);
        updateThemeButtons(savedTheme);

        lightTheme.addEventListener('click', () => {
            htmlElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
            updateThemeButtons('light');
        });

        darkTheme.addEventListener('click', () => {
            htmlElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            updateThemeButtons('dark');
        });

        function updateThemeButtons(theme) {
            if (theme === 'light') {
                lightTheme.classList.add('active');
                darkTheme.classList.remove('active');
            } else {
                darkTheme.classList.add('active');
                lightTheme.classList.remove('active');
            }
        }

        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form Submission Animation
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = loginBtn.querySelector('.btn-text');
        const spinner = loginBtn.querySelector('.spinner-premium');

        loginForm.addEventListener('submit', function(e) {
            btnText.style.display = 'none';
            spinner.style.display = 'inline-block';
            loginBtn.disabled = true;
            
            // Add loading text
            const originalHtml = loginBtn.innerHTML;
            loginBtn.innerHTML = '<span class="spinner-premium"></span> Authenticating...';
        });

        // Remove loading state if form submission fails (back button)
        window.addEventListener('pageshow', function() {
            btnText.style.display = 'inline';
            spinner.style.display = 'none';
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="fas fa-lock-open"></i> <span class="btn-text">Access Dashboard</span> <span class="spinner-premium" style="display: none;"></span>';
        });

        // Input focus effects
        const inputFields = document.querySelectorAll('.input-field');
        inputFields.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.input-icon').style.color = 'var(--primary-color)';
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.querySelector('.input-icon').style.color = 'var(--text-muted)';
                }
            });
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>