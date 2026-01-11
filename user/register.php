<?php
// register.php
require_once 'db_connection.php';
require_once 'session_manager.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($errors)) {
        // Check if username or email already exists
        $database = new Database();
        $db = $database->getConnection();
        
        $checkQuery = "SELECT id FROM users WHERE username = :username OR email = :email";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([':username' => $username, ':email' => $email]);
        
        if ($checkStmt->fetch()) {
            $errors[] = 'Username or email already exists.';
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $insertQuery = "INSERT INTO users (username, email, password_hash, full_name, phone, user_type, created_at) 
                           VALUES (:username, :email, :password_hash, :full_name, :phone, 'customer', NOW())";
            
            $insertStmt = $db->prepare($insertQuery);
            
            if ($insertStmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $hashedPassword,
                ':full_name' => $full_name,
                ':phone' => $phone
            ])) {
                $success = 'Registration successful! You can now login.';
                // Optionally auto-login after registration
                // header('Location: login.php?registered=1');
                // exit();
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CinemaKrish</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #d32f2f 0%, #ff4081 100%);
            --secondary-gradient: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            --dark-bg: #0a0a0a;
            --card-bg: rgba(18, 18, 18, 0.95);
            --input-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.1);
            --neon-red: #ff1744;
            --neon-yellow: #ffea00;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--dark-bg);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 64, 129, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(255, 193, 7, 0.1) 0%, transparent 20%);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
            overflow-x: hidden;
        }
        
        .register-container {
            max-width: 520px;
            width: 100%;
            background: var(--card-bg);
            padding: 50px;
            border-radius: 24px;
            box-shadow: 
                0 20px 60px rgba(211, 47, 47, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            transform-style: preserve-3d;
            perspective: 1000px;
            animation: float 6s ease-in-out infinite;
            position: relative;
            overflow: hidden;
        }
        
        .register-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(
                transparent, transparent, transparent, var(--neon-red)
            );
            animation: rotate 4s linear infinite;
            z-index: -1;
        }
        
        .register-container::after {
            content: '';
            position: absolute;
            inset: 2px;
            background: var(--card-bg);
            border-radius: 22px;
            z-index: 1;
        }
        
        .register-content {
            position: relative;
            z-index: 2;
        }
        
        .register-brand {
            font-family: 'Montserrat', sans-serif;
            font-size: 3rem;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 10px;
            letter-spacing: 1px;
            text-shadow: 0 2px 10px rgba(211, 47, 47, 0.3);
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .register-brand::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--secondary-gradient);
            border-radius: 2px;
        }
        
        .register-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 40px;
            font-weight: 300;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            background: transparent;
            border: 2px solid var(--glass-border);
            border-right: none;
            color: var(--neon-yellow);
            font-size: 1.1rem;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        
        .form-control {
            background: var(--input-bg);
            border: 2px solid var(--glass-border);
            border-left: none;
            color: var(--text-primary);
            border-radius: 0 12px 12px 0;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            height: 52px;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--neon-red);
            box-shadow: 
                0 0 0 4px rgba(255, 23, 68, 0.15),
                inset 0 0 20px rgba(255, 23, 68, 0.1);
            color: var(--text-primary);
        }
        
        .floating-label {
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.3s ease;
            background: var(--card-bg);
            padding: 0 10px;
            z-index: 10;
        }
        
        .form-control:focus + .floating-label,
        .form-control:not(:placeholder-shown) + .floating-label {
            top: 0;
            font-size: 0.85rem;
            color: var(--neon-yellow);
            background: var(--card-bg);
            padding: 0 10px;
        }
        
        .btn-register {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
            margin-top: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-register:hover::before {
            left: 100%;
        }
        
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 15px 30px rgba(211, 47, 47, 0.4),
                0 0 20px rgba(255, 23, 68, 0.3);
        }
        
        .btn-register:active {
            transform: translateY(-1px);
        }
        
        .password-strength-container {
            margin-top: 10px;
        }
        
        .password-strength-text {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        
        .password-strength-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        
        .strength-weak { 
            background: linear-gradient(90deg, #ff4757, #ff3838);
            width: 25%;
            box-shadow: 0 0 10px rgba(255, 71, 87, 0.5);
        }
        .strength-fair { 
            background: linear-gradient(90deg, #ffa502, #ff9f1a);
            width: 50%;
            box-shadow: 0 0 10px rgba(255, 165, 2, 0.5);
        }
        .strength-good { 
            background: linear-gradient(90deg, #2ed573, #1dd1a1);
            width: 75%;
            box-shadow: 0 0 10px rgba(46, 213, 115, 0.5);
        }
        .strength-strong { 
            background: linear-gradient(90deg, #1e90ff, #3742fa);
            width: 100%;
            box-shadow: 0 0 10px rgba(30, 144, 255, 0.5);
        }
        
        .register-links {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .register-links a {
            color: var(--neon-yellow);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            padding: 5px 10px;
            border-radius: 6px;
        }
        
        .register-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--neon-red);
            transition: width 0.3s ease;
        }
        
        .register-links a:hover {
            color: var(--neon-red);
            background: rgba(255, 23, 68, 0.1);
        }
        
        .register-links a:hover::after {
            width: 100%;
        }
        
        .terms-check {
            display: flex;
            align-items: center;
            margin: 25px 0;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .form-check-input {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            background: var(--input-bg);
            border: 2px solid var(--glass-border);
            cursor: pointer;
        }
        
        .form-check-input:checked {
            background: var(--neon-red);
            border-color: var(--neon-red);
        }
        
        .form-check-label {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .form-check-label a {
            color: var(--neon-yellow);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .form-check-label a:hover {
            color: var(--neon-red);
        }
        
        .alert {
            border-radius: 12px;
            border: 1px solid transparent;
            padding: 15px 20px;
            margin-bottom: 25px;
            animation: slideDown 0.5s ease-out;
        }
        
        .alert-danger {
            background: rgba(255, 71, 87, 0.1);
            border-color: rgba(255, 71, 87, 0.3);
            color: #ffcccc;
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.2);
        }
        
        .alert-success {
            background: rgba(46, 213, 115, 0.1);
            border-color: rgba(46, 213, 115, 0.3);
            color: #d4ffd4;
            box-shadow: 0 5px 15px rgba(46, 213, 115, 0.2);
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 30px 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.2rem;
        }
        
        .feature-text {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotateX(0deg); }
            50% { transform: translateY(-10px) rotateX(2deg); }
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 5px rgba(255, 23, 68, 0.5); }
            50% { box-shadow: 0 0 20px rgba(255, 23, 68, 0.8); }
        }
        
        @media (max-width: 768px) {
            .register-container {
                padding: 30px;
                margin: 10px;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
            
            .register-brand {
                font-size: 2.5rem;
            }
        }
        
        .decoration {
            position: fixed;
            z-index: -1;
        }
        
        .decoration.circle {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(211, 47, 47, 0.1) 0%, transparent 70%);
            top: -150px;
            right: -150px;
        }
        
        .decoration.square {
            width: 200px;
            height: 200px;
            background: linear-gradient(45deg, transparent, rgba(255, 193, 7, 0.05));
            bottom: -100px;
            left: -100px;
            transform: rotate(45deg);
        }
    </style>
</head>
<body>
    <!-- Background Decorations -->
    <div class="decoration circle"></div>
    <div class="decoration square"></div>
    
    <div class="container d-flex justify-content-center align-items-center">
        <div class="register-container">
            <div class="register-content">
                <div class="register-brand">
                    <i class="fas fa-film me-2"></i>CinemaKrish
                </div>
                
                <div class="register-subtitle">
                    Join the ultimate cinematic experience
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <div class="mt-3">
                            <a href="login.php" class="btn btn-register">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Now
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($success)): ?>
                <form method="POST" action="" id="registerForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" name="full_name" 
                                           placeholder="John Doe" required>
                                    <label class="floating-label">Full Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user-tag"></i>
                                    </span>
                                    <input type="text" class="form-control" name="username" 
                                           placeholder="johndoe" required>
                                    <label class="floating-label">Username</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" name="email" 
                                   placeholder="john@example.com" required>
                            <label class="floating-label">Email Address</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="tel" class="form-control" name="phone" 
                                   placeholder="+1 234 567 8900">
                            <label class="floating-label">Phone Number (Optional)</label>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" name="password" 
                                           id="password" placeholder="••••••" required>
                                    <label class="floating-label">Password</label>
                                </div>
                                <div class="password-strength-container">
                                    <div class="password-strength-text">
                                        <span>Password Strength:</span>
                                        <span id="strengthText">None</span>
                                    </div>
                                    <div class="password-strength-bar">
                                        <div class="password-strength-fill" id="passwordStrength"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" name="confirm_password" 
                                           placeholder="••••••" required>
                                    <label class="floating-label">Confirm Password</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feature-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="feature-text">Book tickets instantly</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="feature-text">Exclusive member deals</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="feature-text">Personal watchlist</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="feature-text">Rewards & points</div>
                        </div>
                    </div>
                    
                    <div class="terms-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="terms.php">Terms & Conditions</a> and 
                            <a href="privacy.php">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-register">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </form>
                <?php endif; ?>
                
                <div class="register-links">
                    <span style="color: var(--text-secondary);">Already have an account?</span>
                    <a href="login.php" class="ms-2">Sign In Now</a>
                    <div class="mt-3">
                        <a href="index.php">
                            <i class="fas fa-arrow-left me-1"></i>Back to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password strength indicator with improved logic
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            // Reset classes
            strengthBar.className = 'password-strength-fill';
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = 'None';
                strengthText.style.color = '#b0b0b0';
                return;
            }
            
            let score = 0;
            let strength = '';
            let color = '';
            
            // Length check
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            
            // Complexity checks
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            // Determine strength level
            if (password.length < 6) {
                strength = 'Too Short';
                color = '#ff4757';
                strengthBar.classList.add('strength-weak');
                strengthBar.style.width = '10%';
            } else if (score <= 3) {
                strength = 'Weak';
                color = '#ff4757';
                strengthBar.classList.add('strength-weak');
                strengthBar.style.width = '25%';
            } else if (score <= 5) {
                strength = 'Fair';
                color = '#ffa502';
                strengthBar.classList.add('strength-fair');
                strengthBar.style.width = '50%';
            } else if (score <= 7) {
                strength = 'Good';
                color = '#2ed573';
                strengthBar.classList.add('strength-good');
                strengthBar.style.width = '75%';
            } else {
                strength = 'Strong';
                color = '#1e90ff';
                strengthBar.classList.add('strength-strong');
                strengthBar.style.width = '100%';
            }
            
            strengthText.textContent = strength;
            strengthText.style.color = color;
            
            // Add animation
            strengthBar.style.transition = 'all 0.3s ease';
        });
        
        // Form validation enhancement
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                const confirmField = document.querySelector('input[name="confirm_password"]');
                confirmField.style.borderColor = '#ff4757';
                confirmField.style.boxShadow = '0 0 0 4px rgba(255, 71, 87, 0.2)';
                
                // Show error message
                if (!document.querySelector('.password-match-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger password-match-error mt-2';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Passwords do not match';
                    confirmField.parentElement.parentElement.appendChild(errorDiv);
                }
            }
        });
        
        // Remove error when user starts typing
        document.querySelector('input[name="confirm_password"]').addEventListener('input', function() {
            this.style.borderColor = '';
            this.style.boxShadow = '';
            const errorDiv = document.querySelector('.password-match-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        });
        
        // Add focus effects to all inputs
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>