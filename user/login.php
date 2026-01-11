<?php
// login.php
require_once 'db_connection.php';
require_once 'session_manager.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin.php');
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $user = $database->authenticateUser($username, $password);
        
        if ($user) {
            loginUser($user);
            
            $is_admin = isset($user['is_admin']) ? $user['is_admin'] : 0;
            
            if ($is_admin == 1) {
                header('Location: admin.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CinemaKrish</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cinzel:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #d32f2f;
            --primary-dark: #9a0007;
            --secondary: #ffc107;
            --dark: #121212;
            --dark-light: #1e1e1e;
            --light: #f5f5f5;
            --gray: #757575;
            --gray-light: #e0e0e0;
            --success: #4caf50;
            --border: rgba(255, 255, 255, 0.1);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Background Pattern */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(211, 47, 47, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 193, 7, 0.1) 0%, transparent 50%);
            z-index: -1;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background: var(--dark-light);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
            border: 1px solid var(--border);
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Header */
        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        }
        
        .brand {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            font-weight: 600;
            color: white;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .brand-subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        /* Form */
        .login-form {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .form-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .form-control {
            width: 100%;
            padding: 16px 16px 16px 48px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--light);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            height: 52px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
        }
        
        .form-control:focus + .form-icon {
            color: var(--primary);
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray-light);
            font-size: 0.9rem;
        }
        
        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            padding: 4px;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        /* Form Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
            cursor: pointer;
            appearance: none;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .form-check-input:checked {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .form-check-input:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .form-check-label {
            color: var(--gray);
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }
        
        .forgot-password {
            color: var(--secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: var(--primary);
        }
        
        /* Submit Button */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(211, 47, 47, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        
        .divider span {
            padding: 0 15px;
        }
        
        /* Social Login */
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .social-btn {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .social-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
        }
        
        /* Links */
        .login-links {
            text-align: center;
            padding-top: 25px;
            border-top: 1px solid var(--border);
        }
        
        .login-links p {
            color: var(--gray);
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        
        .register-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 8px 16px;
            border: 1px solid transparent;
            border-radius: 8px;
        }
        
        .register-link:hover {
            color: var(--primary);
            background: rgba(211, 47, 47, 0.1);
            border-color: rgba(211, 47, 47, 0.2);
        }
        
        /* Error Message */
        .alert {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
        }
        
        .alert-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        /* Demo Section */
        .demo-section {
            background: rgba(255, 193, 7, 0.05);
            border: 1px solid rgba(255, 193, 7, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
        }
        
        .demo-title {
            color: var(--secondary);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .demo-credentials {
            font-size: 0.85rem;
            color: var(--gray-light);
        }
        
        .demo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .demo-item:last-child {
            border-bottom: none;
        }
        
        .demo-role {
            color: var(--secondary);
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .demo-copy {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .demo-copy:hover {
            color: var(--primary);
            background: rgba(211, 47, 47, 0.1);
        }
        
        /* Back Link */
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--gray);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            z-index: 100;
        }
        
        .back-link:hover {
            color: var(--light);
        }
        
        /* Loading State */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                border-radius: 15px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-form {
                padding: 30px 25px;
            }
            
            .social-login {
                flex-direction: column;
            }
            
            .brand {
                font-size: 2rem;
            }
        }
        
        /* Admin Mode */
        .admin-mode .login-header {
            background: linear-gradient(135deg, #2d3748, #4a5568);
        }
        
        .admin-mode .btn-login {
            background: linear-gradient(135deg, #2d3748, #4a5568);
        }
        
        .admin-mode .btn-login:hover {
            box-shadow: 0 10px 20px rgba(45, 55, 72, 0.3);
        }
        
        /* Role Switch */
        .role-switch {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .role-btn {
            flex: 1;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--gray);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .role-btn.active {
            background: rgba(211, 47, 47, 0.1);
            border-color: var(--primary);
            color: var(--light);
        }
        
        .role-btn.admin.active {
            background: rgba(45, 55, 72, 0.2);
            border-color: #4a5568;
        }
    </style>
</head>
<body>
    <!-- Background Pattern -->
    <div class="bg-pattern"></div>
    
    <!-- Back to Home -->
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Home</span>
    </a>
    
    <div class="login-container" id="loginContainer">
        <!-- Header -->
        <div class="login-header">
            <div class="brand">
                <i class="fas fa-film"></i> CinemaKrish
            </div>
            <div class="brand-subtitle">Premium Cinema Experience</div>
        </div>
        
        <!-- Role Selection -->
        <div class="login-form">
            <div class="role-switch">
                <button class="role-btn active" data-role="user" onclick="switchRole('user')">
                    <i class="fas fa-user"></i> User Login
                </button>
                <button class="role-btn" data-role="admin" onclick="switchRole('admin')">
                    <i class="fas fa-shield-alt"></i> Admin Login
                </button>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="username">Username or Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user form-icon"></i>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your username or email"
                               required
                               autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span id="loginText">Sign In to Account</span>
                </button>
            </form>
            
            <div class="divider">
                <span>Or continue with</span>
            </div>
            
            <!-- Social Login -->
            <div class="social-login">
                <button class="social-btn" onclick="socialLogin('google')">
                    <i class="fab fa-google"></i>
                    Google
                </button>
                <button class="social-btn" onclick="socialLogin('facebook')">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </button>
            </div>
            
            <!-- Demo Credentials -->
           
            
            <!-- Register Link -->
            <div class="login-links">
                <p>Don't have an account?</p>
                <a href="register.php" class="register-link">
                    <i class="fas fa-user-plus"></i>
                    Create New Account
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Role switching
        let currentRole = 'user';
        
        function switchRole(role) {
            if (currentRole === role) return;
            
            const container = document.getElementById('loginContainer');
            const roleBtns = document.querySelectorAll('.role-btn');
            const loginBtn = document.getElementById('loginBtn');
            const loginText = document.getElementById('loginText');
            
            // Update role buttons
            roleBtns.forEach(btn => {
                if (btn.dataset.role === role) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
            // Update UI for admin mode
            if (role === 'admin') {
                container.classList.add('admin-mode');
                loginText.textContent = 'Sign In to Admin Panel';
                
                // Show admin notice
                showAdminNotice();
            } else {
                container.classList.remove('admin-mode');
                loginText.textContent = 'Sign In to Account';
                
                // Remove admin notice if exists
                removeAdminNotice();
            }
            
            currentRole = role;
        }
        
        function showAdminNotice() {
            if (document.querySelector('.admin-notice')) return;
            
            const notice = document.createElement('div');
            notice.className = 'alert alert-danger admin-notice';
            notice.innerHTML = `
                <i class="fas fa-shield-alt alert-icon"></i>
                <span>Admin access requires special privileges. Use admin credentials to continue.</span>
            `;
            
            const form = document.getElementById('loginForm');
            form.parentNode.insertBefore(notice, form);
        }
        
        function removeAdminNotice() {
            const notice = document.querySelector('.admin-notice');
            if (notice) notice.remove();
        }
        
        // Copy credentials
        function copyCredential(type) {
            let credentials = {
                'admin': { user: 'admin', pass: 'admin123' },
                'john': { user: 'john', pass: 'user123' },
                'alice': { user: 'alice', pass: 'user123' }
            };
            
            const cred = credentials[type];
            const text = `Username: ${cred.user}\nPassword: ${cred.pass}`;
            
            navigator.clipboard.writeText(text).then(() => {
                const btn = event.currentTarget;
                const originalHTML = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.style.color = 'var(--success)';
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.color = '';
                }, 2000);
            });
        }
        
        // Social login placeholder
        function socialLogin(provider) {
            const btn = event.currentTarget;
            const originalHTML = btn.innerHTML;
            
            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Connecting...`;
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                
                alert(`${provider.charAt(0).toUpperCase() + provider.slice(1)} login integration coming soon!`);
            }, 1500);
        }
        
        // Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('loginBtn');
            const submitText = document.getElementById('loginText');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.classList.add('btn-loading');
            submitText.textContent = 'Signing In...';
            
            // Validate admin login attempt
            if (currentRole === 'admin') {
                const username = document.getElementById('username').value;
                if (!username.includes('admin') && !confirm('Are you sure you want to login as admin?')) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-loading');
                    submitText.textContent = 'Sign In to Admin Panel';
                }
            }
        });
        
        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
            
            // Check URL for admin parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('admin') === '1') {
                switchRole('admin');
            }
            
            // Check for success messages from registration
            if (urlParams.get('registered') === '1') {
                showSuccessMessage();
            }
        });
        
        // Show success message if coming from registration
        function showSuccessMessage() {
            const successAlert = document.createElement('div');
            successAlert.className = 'alert';
            successAlert.style.background = 'rgba(76, 175, 80, 0.1)';
            successAlert.style.borderColor = 'rgba(76, 175, 80, 0.2)';
            successAlert.style.color = '#81c784';
            successAlert.innerHTML = `
                <i class="fas fa-check-circle alert-icon"></i>
                <span>Registration successful! Please login with your new account.</span>
            `;
            
            const form = document.getElementById('loginForm');
            form.parentNode.insertBefore(successAlert, form);
            
            // Remove after 5 seconds
            setTimeout(() => {
                successAlert.remove();
            }, 5000);
        }
        
        // Enhanced input animations
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>