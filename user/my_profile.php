<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$userQuery = "SELECT * FROM users WHERE id = :user_id";
$userStmt = $db->prepare($userQuery);
$userStmt->execute([':user_id' => $user_id]);
$user = $userStmt->fetch();

if (!$user) {
    // User not found, redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

// Fetch user bookings with movie details - REMOVED JOIN TO NON-EXISTENT TABLES
$bookingsQuery = "
    SELECT 
        b.*,
        m.title as movie_title,
        m.poster_url,
        m.duration,
        m.rating
    FROM bookings b
    LEFT JOIN movies m ON b.movie_id = m.id
    WHERE b.user_id = :user_id
    ORDER BY b.booking_date DESC
    LIMIT 10
";

$bookingsStmt = $db->prepare($bookingsQuery);
$bookingsStmt->execute([':user_id' => $user_id]);
$bookings = $bookingsStmt->fetchAll();

// Calculate booking statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_bookings,
        SUM(total_seats) as total_tickets,
        SUM(total_amount) as total_spent,
        SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
        SUM(CASE WHEN payment_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
    FROM bookings 
    WHERE user_id = :user_id
";

$statsStmt = $db->prepare($statsQuery);
$statsStmt->execute([':user_id' => $user_id]);
$stats = $statsStmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    
    // Check if email already exists for another user
    $emailCheckQuery = "SELECT id FROM users WHERE email = :email AND id != :user_id";
    $emailCheckStmt = $db->prepare($emailCheckQuery);
    $emailCheckStmt->execute([':email' => $email, ':user_id' => $user_id]);
    
    if ($emailCheckStmt->fetch()) {
        $error = "Email already exists. Please use a different email.";
    } else {
        // Update user profile
        $updateQuery = "
            UPDATE users 
            SET full_name = :full_name, 
                email = :email, 
                phone = :phone,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = :user_id
        ";
        
        $updateStmt = $db->prepare($updateQuery);
        if ($updateStmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':phone' => $phone,
            ':user_id' => $user_id
        ])) {
            $success = "Profile updated successfully!";
            // Refresh user data
            $userStmt->execute([':user_id' => $user_id]);
            $user = $userStmt->fetch();
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!password_verify($current_password, $user['password_hash'])) {
        $password_error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $password_error = "Password must be at least 6 characters long.";
    } else {
        // Update password
        $passwordUpdateQuery = "
            UPDATE users 
            SET password_hash = :password_hash,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = :user_id
        ";
        
        $passwordUpdateStmt = $db->prepare($passwordUpdateQuery);
        if ($passwordUpdateStmt->execute([
            ':password_hash' => password_hash($new_password, PASSWORD_DEFAULT),
            ':user_id' => $user_id
        ])) {
            $password_success = "Password changed successfully!";
        } else {
            $password_error = "Failed to change password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Cinema Krish</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #4facfe;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .profile-name {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .profile-role {
            font-size: 1.2em;
            opacity: 0.9;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            padding: 40px;
        }

        @media (max-width: 900px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }

        .sidebar {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: #4facfe;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9em;
            color: #666;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4facfe;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #4facfe;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #4facfe;
        }

        .btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .booking-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            align-items: center;
            transition: box-shadow 0.3s;
        }

        .booking-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .booking-poster {
            width: 80px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
        }

        .booking-info {
            flex: 1;
        }

        .booking-title {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .booking-detail {
            display: flex;
            gap: 15px;
            margin-bottom: 5px;
            font-size: 0.95em;
            color: #666;
        }


        .booking-detail i {
            color: #4facfe;
            width: 20px;
        }

        .booking-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            margin-left: 10px;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 20px;
            color: #ccc;
        }

        .nav-tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }

        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            font-size: 1.1em;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            position: relative;
            transition: color 0.3s;
        }

        .tab-btn:hover {
            color: #4facfe;
        }

        .tab-btn.active {
            color: #4facfe;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #4facfe;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            color: #ff416c;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            text-decoration: underline;
        }
        .tab-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin:10px;
    padding: 12px 22px;
    border-radius: 14px;

    background: linear-gradient(135deg, #ffcc00, #ff9f00);
    color: #000;

    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;

    box-shadow: 0 6px 16px rgba(255, 180, 0, 0.45);
    transition: all 0.3s ease;
}

.tab-btn i {
    font-size: 1.05rem;
}

.tab-btn:hover {
    background: linear-gradient(135deg, #ffd700, #ffb700);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(255, 215, 0, 0.65);
    color: #000;
}

/* Mobile Friendly */
@media (max-width: 576px) {
    .tab-btn {
        width: 100%;
        padding: 14px;
    }
}

    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h1>
            <div class="profile-role">
                <?php echo $user['is_admin'] ? 'Administrator' : 'Customer'; ?>
            </div>
        </div>

        <div class="nav-tabs">
            <button class="tab-btn active" onclick="showTab('profile')">
                <i class="fas fa-user-circle"></i> Profile
            </button>
            <button class="tab-btn" onclick="showTab('bookings')">
                <i class="fas fa-ticket-alt"></i> My Bookings
            </button>
            <button class="tab-btn" onclick="showTab('password')">
                <i class="fas fa-lock"></i> Change Password
            </button>
            <a href="index.php" class="tab-btn">
    <i class="fas fa-home"></i>
    <span>Back to Home</span>
</a>

        </div>

        <div class="profile-content">
            <div class="sidebar">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_bookings'] ?? 0; ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_tickets'] ?? 0; ?></div>
                        <div class="stat-label">Tickets</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">$<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['confirmed_bookings'] ?? 0; ?></div>
                        <div class="stat-label">Confirmed</div>
                    </div>
                </div>

                <div class="section">
                    <h3 class="section-title"><i class="fas fa-info-circle"></i> Account Info</h3>
                    <div class="form-group">
                        <div class="form-label">Member Since</div>
                        <div><?php echo date('F d, Y', strtotime($user['created_at'])); ?></div>
                    </div>
                    <div class="form-group">
                        <div class="form-label">Username</div>
                        <div><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <div class="form-group">
                        <div class="form-label">Account Status</div>
                        <div>
                            <span style="color: <?php echo $user['is_active'] ? 'green' : 'red'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <div class="main-content">
                <!-- Profile Tab -->
                <div id="profile" class="tab-content active">
                    <div class="section">
                        <h2 class="section-title"><i class="fas fa-user-edit"></i> Edit Profile</h2>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-error"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label" for="full_name">Full Name</label>
                                <input type="text" class="form-input" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="email">Email Address</label>
                                <input type="email" class="form-input" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="phone">Phone Number</label>
                                <input type="tel" class="form-input" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>

                            <input type="hidden" name="update_profile" value="1">
                            <button type="submit" class="btn">Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Bookings Tab -->
                <div id="bookings" class="tab-content">
                    <div class="section">
                        <h2 class="section-title"><i class="fas fa-history"></i> Booking History</h2>
                        
                        <?php if (empty($bookings)): ?>
                            <div class="empty-state">
                                <i class="fas fa-ticket-alt"></i>
                                <h3>No Bookings Yet</h3>
                                <p>You haven't made any bookings yet. Start your movie experience now!</p>
                                <a href="index.php#movies" class="btn" style="margin-top: 20px;">Browse Movies</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <div class="booking-card">
                                    <?php if (!empty($booking['poster_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($booking['poster_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($booking['movie_title']); ?>" 
                                             class="booking-poster">
                                    <?php else: ?>
                                        <div class="booking-poster" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-film" style="font-size: 2em; color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="booking-info">
                                        <div class="booking-title">
                                            <?php echo htmlspecialchars($booking['movie_title']); ?>
                                            <span class="booking-status status-<?php echo $booking['payment_status']; ?>">
                                                <?php echo ucfirst($booking['payment_status']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="booking-detail">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><?php echo date('M d, Y', strtotime($booking['show_date'])); ?> at <?php echo date('g:i A', strtotime($booking['show_time'])); ?></span>
                                        </div>
                                        
                                        <div class="booking-detail">
                                            <i class="fas fa-chair"></i>
                                            <span>Seats: <?php echo htmlspecialchars($booking['seat_numbers']); ?> (<?php echo $booking['total_seats']; ?> seats)</span>
                                        </div>
                                        
                                        <div class="booking-detail">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span>Amount: $<?php echo number_format($booking['total_amount'], 2); ?></span>
                                        </div>
                                        
                                        <div class="booking-detail">
                                            <i class="fas fa-tag"></i>
                                            <span>Ticket #: <?php echo htmlspecialchars($booking['ticket_number']); ?></span>
                                        </div>
                                        
                                        <div class="booking-detail">
                                            <i class="fas fa-credit-card"></i>
                                            <span>Payment: <?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?></span>
                                        </div>
                                        
                                        <?php if ($booking['qr_code'] && $booking['payment_status'] == 'completed'): ?>
                                            <div class="booking-detail">
                                                <i class="fas fa-qrcode"></i>
                                                <span>QR Code: <a href="<?php echo htmlspecialchars($booking['qr_code']); ?>" target="_blank">View</a></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($booking['payment_status'] == 'pending'): ?>
                                            <div class="booking-detail">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <span style="color: #ff9900;">Payment pending. 
                                                    <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" style="color: #4facfe;">Complete payment</a>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="booking-history.php" class="btn">View All Bookings</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Password Tab -->
                <div id="password" class="tab-content">
                    <div class="section">
                        <h2 class="section-title"><i class="fas fa-key"></i> Change Password</h2>
                        
                        <?php if (isset($password_success)): ?>
                            <div class="alert alert-success"><?php echo $password_success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($password_error)): ?>
                            <div class="alert alert-error"><?php echo $password_error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label" for="current_password">Current Password</label>
                                <input type="password" class="form-input" id="current_password" name="current_password" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="new_password">New Password</label>
                                <input type="password" class="form-input" id="new_password" name="new_password" required minlength="6">
                                <small style="color: #666; font-size: 0.9em;">Password must be at least 6 characters long</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-input" id="confirm_password" name="confirm_password" required minlength="6">
                            </div>

                            <input type="hidden" name="change_password" value="1">
                            <button type="submit" class="btn">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Deactivate all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Activate clicked tab button
            event.currentTarget.classList.add('active');
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.querySelector('form[action=""]');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert('New passwords do not match!');
                    }
                });
            }
        });
    </script>
</body>
</html>