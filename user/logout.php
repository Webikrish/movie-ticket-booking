<?php
// logout.php
require_once 'session_manager.php';

// Check if user wants to confirm logout
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'true';

if ($confirmed) {
    // Store user info before logout for feedback
    $user_info = isset($_SESSION['user_full_name']) ? $_SESSION['user_full_name'] : 
                (isset($_SESSION['user_username']) ? $_SESSION['user_username'] : 'User');
    
    // Perform logout
    logoutUser();
    
    // Set session message for feedback
    session_start();
    $_SESSION['logout_message'] = "You have been successfully logged out.";
    $_SESSION['logout_user'] = $user_info;
    session_write_close();
    
    // Redirect to login page
    header('Location: login.php?logged_out=1&user=' . urlencode($user_info));
    exit();
}

// If not confirmed, show logout confirmation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - CinemaKrish</title>
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
            --warning: #ff9800;
            --border: rgba(255, 255, 255, 0.1);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            --glass: rgba(255, 255, 255, 0.05);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, #1a1a1a 100%);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
            opacity: 0.1;
            animation: float 15s infinite linear;
        }
        
        .bg-circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
            background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
        }
        
        .bg-circle:nth-child(2) {
            width: 400px;
            height: 400px;
            bottom: -200px;
            right: -200px;
            animation-delay: -5s;
            background: radial-gradient(circle, var(--secondary) 0%, transparent 70%);
        }
        
        .bg-circle:nth-child(3) {
            width: 200px;
            height: 200px;
            top: 50%;
            right: 10%;
            animation-delay: -10s;
            background: radial-gradient(circle, #08d9d6 0%, transparent 70%);
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }
            66% {
                transform: translate(-30px, 30px) rotate(240deg);
            }
        }
        
        /* Logout Container */
        .logout-container {
            width: 100%;
            max-width: 500px;
            background: rgba(30, 30, 30, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            animation: slideUp 0.6s ease-out;
            position: relative;
            z-index: 1;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Header */
        .logout-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .logout-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        }
        
        .logout-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
            display: inline-block;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        .brand {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            font-weight: 600;
            color: white;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .logout-title {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            margin-top: 10px;
        }
        
        /* Content */
        .logout-content {
            padding: 50px 40px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--glass);
            border-radius: 16px;
            border: 1px solid var(--border);
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .user-details h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-details p {
            color: var(--gray-light);
            font-size: 0.9rem;
        }
        
        /* Warning Box */
        .warning-box {
            background: rgba(255, 152, 0, 0.1);
            border: 1px solid rgba(255, 152, 0, 0.2);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 35px;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .warning-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .warning-icon {
            color: var(--warning);
            font-size: 1.5rem;
        }
        
        .warning-title {
            color: var(--warning);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .warning-content {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .warning-list {
            list-style: none;
            margin-top: 15px;
            padding-left: 10px;
        }
        
        .warning-list li {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        
        .warning-list li i {
            color: var(--warning);
            margin-top: 3px;
            font-size: 0.8rem;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .btn {
            flex: 1;
            padding: 18px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.5px;
        }
        
        .btn-logout {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }
        
        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(211, 47, 47, 0.3);
        }
        
        .btn-cancel {
            background: var(--glass);
            color: var(--light);
            border: 1px solid var(--border);
        }
        
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        /* Session Info */
        .session-info {
            background: var(--glass);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--border);
            margin-top: 30px;
        }
        
        .session-info h4 {
            font-size: 1rem;
            margin-bottom: 15px;
            color: var(--gray-light);
            font-weight: 500;
        }
        
        .session-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .session-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .session-label {
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .session-value {
            font-size: 0.95rem;
            color: var(--light);
            font-weight: 500;
        }
        
        /* Logout Success Message (Hidden by default) */
        .logout-success {
            text-align: center;
            padding: 60px 40px;
            display: none;
        }
        
        .success-icon {
            font-size: 4rem;
            color: var(--success);
            margin-bottom: 25px;
            animation: bounce 1s ease;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
        
        .success-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--light);
        }
        
        .success-message {
            color: var(--gray-light);
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .auto-redirect {
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        
        /* Footer */
        .logout-footer {
            text-align: center;
            padding: 25px 40px;
            border-top: 1px solid var(--border);
            background: rgba(0, 0, 0, 0.2);
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-top: 15px;
        }
        
        .footer-link {
            color: var(--gray);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .footer-link:hover {
            color: var(--secondary);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .logout-container {
                max-width: 100%;
                margin: 0 20px;
            }
            
            .logout-header,
            .logout-content {
                padding: 40px 25px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .session-details {
                grid-template-columns: 1fr;
            }
            
            .brand {
                font-size: 2rem;
            }
            
            .logout-icon {
                font-size: 3rem;
            }
        }
        
        @media (max-width: 480px) {
            .user-info {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 15px;
                align-items: center;
            }
            
            .logout-header {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
    </div>
    
    <!-- Logout Container -->
    <div class="logout-container">
        <!-- Logout Confirmation View -->
        <div id="confirmationView">
            <!-- Header -->
            <div class="logout-header">
                <div class="logout-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="brand">
                    <i class="fas fa-film"></i> CinemaKrish
                </div>
                <div class="logout-title">Secure Logout Confirmation</div>
            </div>
            
            <!-- Content -->
            <div class="logout-content">
                <!-- User Info -->
                <div class="user-info">
                    <div class="user-avatar">
                        <?php
                        $initial = isset($_SESSION['user_full_name']) ? substr($_SESSION['user_full_name'], 0, 1) : 
                                  (isset($_SESSION['user_username']) ? substr($_SESSION['user_username'], 0, 1) : 'U');
                        echo strtoupper($initial);
                        ?>
                    </div>
                    <div class="user-details">
                        <h3>
                            <?php
                            if (isset($_SESSION['user_full_name'])) {
                                echo htmlspecialchars($_SESSION['user_full_name']);
                            } elseif (isset($_SESSION['user_username'])) {
                                echo htmlspecialchars($_SESSION['user_username']);
                            } else {
                                echo 'User';
                            }
                            ?>
                        </h3>
                        <p>
                            <?php
                            if (isset($_SESSION['user_email'])) {
                                echo htmlspecialchars($_SESSION['user_email']);
                            } elseif (isset($_SESSION['user_type'])) {
                                echo ucfirst($_SESSION['user_type']);
                            } else {
                                echo 'CinemaKrish Account';
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Warning Box -->
                <div class="warning-box">
                    <div class="warning-header">
                        <i class="fas fa-exclamation-triangle warning-icon"></i>
                        <h3 class="warning-title">Are you sure you want to logout?</h3>
                    </div>
                    <div class="warning-content">
                        <p>You're about to sign out of your CinemaKrish account. This will:</p>
                        <ul class="warning-list">
                            <li><i class="fas fa-times"></i> End your current session</li>
                            <li><i class="fas fa-shopping-cart"></i> Clear any pending bookings</li>
                            <li><i class="fas fa-history"></i> Remove access to purchase history</li>
                            <li><i class="fas fa-user"></i> Require re-authentication for next login</li>
                        </ul>
                        <p>If you're using a public computer, this helps protect your account.</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="?confirm=true" class="btn btn-logout" id="confirmLogout">
                        <i class="fas fa-sign-out-alt"></i>
                        Yes, Logout Now
                    </a>
                    <a href="index.php" class="btn btn-cancel">
                        <i class="fas fa-arrow-left"></i>
                        Cancel & Return
                    </a>
                </div>
                
                <!-- Session Information -->
                <div class="session-info">
                    <h4>Session Information</h4>
                    <div class="session-details">
                        <div class="session-item">
                            <span class="session-label">Login Time</span>
                            <span class="session-value">
                                <?php echo isset($_SESSION['login_time']) ? 
                                    date('H:i', strtotime($_SESSION['login_time'])) : 
                                    'N/A'; ?>
                            </span>
                        </div>
                        <div class="session-item">
                            <span class="session-label">Session Duration</span>
                            <span class="session-value" id="sessionDuration">Calculating...</span>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="logout-footer">
                <p style="color: var(--gray); font-size: 0.9rem;">
                    <i class="fas fa-shield-alt"></i> Your security is our priority
                </p>
                <div class="footer-links">
                    <a href="privacy.php" class="footer-link">
                        <i class="fas fa-lock"></i> Privacy Policy
                    </a>
                    <a href="help.php" class="footer-link">
                        <i class="fas fa-question-circle"></i> Need Help?
                    </a>
                    <a href="contact.php" class="footer-link">
                        <i class="fas fa-envelope"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Success View (Hidden initially) -->
        <div id="successView" class="logout-success" style="display: none;">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="success-title">Successfully Logged Out</h2>
            <p class="success-message" id="successMessage">
                You have been securely logged out of your CinemaKrish account.
                <br>Thank you for using our service.
            </p>
            <a href="login.php" class="btn btn-logout" style="max-width: 200px; margin: 0 auto;">
                <i class="fas fa-sign-in-alt"></i>
                Login Again
            </a>
            <div class="auto-redirect">
                <i class="fas fa-clock"></i> Redirecting to login page in <span id="countdown">10</span> seconds...
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Calculate session duration
        function calculateSessionDuration() {
            const loginTime = <?php echo isset($_SESSION['login_time']) ? 
                strtotime($_SESSION['login_time']) * 1000 : 'null'; ?>;
            
            if (loginTime) {
                const now = Date.now();
                const diff = now - loginTime;
                
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                let duration = '';
                if (hours > 0) {
                    duration += hours + ' hour' + (hours > 1 ? 's' : '');
                    if (minutes > 0) {
                        duration += ' ' + minutes + ' minute' + (minutes > 1 ? 's' : '');
                    }
                } else {
                    duration = minutes + ' minute' + (minutes > 1 ? 's' : '');
                }
                
                document.getElementById('sessionDuration').textContent = duration;
            }
        }
        
        // Handle logout confirmation with animation
        document.getElementById('confirmLogout').addEventListener('click', function(e) {
            e.preventDefault();
            
            const confirmationView = document.getElementById('confirmationView');
            const successView = document.getElementById('successView');
            
            // Add fade out animation
            confirmationView.style.opacity = '0';
            confirmationView.style.transform = 'translateY(-20px)';
            confirmationView.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                confirmationView.style.display = 'none';
                successView.style.display = 'block';
                
                // Add fade in animation
                setTimeout(() => {
                    successView.style.opacity = '1';
                    successView.style.transform = 'translateY(0)';
                }, 50);
                
                // Start countdown for redirect
                startCountdown();
                
                // Perform logout after showing success message
                setTimeout(() => {
                    window.location.href = this.href;
                }, 1000);
            }, 500);
        });
        
        // Countdown timer for auto-redirect
        function startCountdown() {
            let countdown = 10;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'login.php?logged_out=1';
                }
            }, 1000);
        }
        
        // Cancel button hover effect
        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.2)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Calculate session duration
            calculateSessionDuration();
            setInterval(calculateSessionDuration, 60000); // Update every minute
            
            // Check if coming from immediate logout (back button case)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('logged_out') === '1') {
                // Show success view directly
                document.getElementById('confirmationView').style.display = 'none';
                document.getElementById('successView').style.display = 'block';
                startCountdown();
            }
            
            // Check if user is logged in
            <?php if (!isLoggedIn()): ?>
                // If not logged in, redirect to login
                window.location.href = 'login.php?session_expired=1';
            <?php endif; ?>
            
            // Add smooth animations to elements
            const elements = document.querySelectorAll('.user-info, .warning-box, .action-buttons, .session-info');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100 + 300);
            });
            
            // Add exit confirmation for page close/navigation away
            window.addEventListener('beforeunload', function(e) {
                if (!window.location.href.includes('confirm=true') && 
                    !window.location.href.includes('login.php')) {
                    // Don't show confirmation if we're logging out or going to login
                    return undefined;
                }
                
                // Show confirmation for other navigation
                const message = 'You are about to leave the logout page. Are you sure?';
                e.returnValue = message;
                return message;
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape key - cancel logout
            if (e.key === 'Escape') {
                window.location.href = 'index.php';
            }
            
            // Enter key - confirm logout
            if (e.key === 'Enter' && e.ctrlKey) {
                document.getElementById('confirmLogout').click();
            }
            
            // L key - logout (with confirmation)
            if (e.key === 'l' && e.altKey) {
                document.getElementById('confirmLogout').click();
            }
        });
    </script>
</body>
</html>