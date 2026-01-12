<?php
require_once 'session_manager.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db_connection.php';

// Initialize database operations
$database = new Database();
$db = $database->getConnection();

// Get user details
$user_id = $_SESSION['user_id'];
$user = $database->getUserById($user_id);

// Get user's bookings
$bookings = getUserBookings($user_id, $db);

// Get total spent by user
$total_spent = getTotalSpent($user_id, $db);

// Function to get user bookings
function getUserBookings($user_id, $db) {
    $query = "SELECT b.*, m.title as movie_title, m.poster_url, 
              DATE_FORMAT(b.show_date, '%d %b %Y') as formatted_date,
              TIME_FORMAT(b.show_time, '%h:%i %p') as formatted_time
              FROM bookings b
              JOIN movies m ON b.movie_id = m.id
              WHERE b.user_id = :user_id
              ORDER BY b.booking_date DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get total spent by user
function getTotalSpent($user_id, $db) {
    $query = "SELECT SUM(total_amount) as total_spent 
              FROM bookings 
              WHERE user_id = :user_id AND payment_status = 'completed'";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_spent'] ? $result['total_spent'] : 0;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    // Check if email is already taken by another user
    $check_email = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_email->execute([$email, $user_id]);
    
    if ($check_email->fetch()) {
        $profile_error = "Email already taken by another user.";
    } else {
        // Update profile
        $update_query = "UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?";
        $stmt = $db->prepare($update_query);
        
        if ($stmt->execute([$full_name, $phone, $email, $user_id])) {
            $_SESSION['success'] = "Profile updated successfully!";
            // Refresh user data
            $user = $database->getUserById($user_id);
        } else {
            $profile_error = "Failed to update profile. Please try again.";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $user_query = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $user_query->execute([$user_id]);
    $user_data = $user_query->fetch();
    
    if (!password_verify($current_password, $user_data['password_hash'])) {
        $password_error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $password_error = "Password must be at least 6 characters.";
    } else {
        // Update password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        
        if ($update_pass->execute([$new_password_hash, $user_id])) {
            $_SESSION['success'] = "Password changed successfully!";
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
    <title>My Dashboard - CinemaKrish</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Oswald:wght@500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-dark: #0a0a0a;
            --accent-gold: #FFD700;
            --secondary-red: #DC3545;
            --light-gray: #f8f9fa;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Montserrat', sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #1a1a1a 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-bottom: 3px solid var(--accent-gold);
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            background: var(--accent-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-dark);
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .card-title {
            color: var(--primary-dark);
            font-family: 'Oswald', sans-serif;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--accent-gold);
            padding-bottom: 0.5rem;
        }
        
        .stat-card {
            text-align: center;
            padding: 2rem 1rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: var(--accent-gold);
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .booking-card {
            border-left: 4px solid var(--accent-gold);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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
        
        .sidebar {
            position: sticky;
            top: 20px;
        }
        
        .nav-pills .nav-link {
            color: #333;
            font-weight: 600;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
        }
        
        .nav-pills .nav-link.active {
            background: var(--accent-gold);
            color: var(--primary-dark);
        }
        
        .nav-pills .nav-link i {
            width: 25px;
        }
        
        .qr-code {
            width: 150px;
            height: 150px;
            border: 1px solid #ddd;
            padding: 10px;
            background: white;
        }
        
        .movie-poster-small {
            width: 80px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .form-control, .form-select {
            border: 1px solid #ddd;
            padding: 0.75rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25);
        }
        
        .btn-gold {
            background: var(--accent-gold);
            color: var(--primary-dark);
            font-weight: 600;
            border: none;
        }
        
        .btn-gold:hover {
            background: #e6c200;
            color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include "header.php"; ?>

    <!-- Success Message -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h1 class="display-5 fw-bold mb-2">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?> | 
                        <i class="fas fa-phone me-2 ms-3"></i><?php echo htmlspecialchars($user['phone']); ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="badge bg-warning text-dark fs-6 p-2 mb-2">
                        Member Since: <?php echo date('M Y', strtotime($user['created_at'])); ?>
                    </div>
                    <br>
                    <a href="index.php" class="btn btn-light me-2">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                    <a href="booking-history.php" class="btn btn-outline-light">
                        <i class="fas fa-history me-1"></i> All Bookings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="dashboard-card sidebar">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="v-pills-overview-tab" data-bs-toggle="pill" data-bs-target="#v-pills-overview" type="button" role="tab">
                            <i class="fas fa-chart-line me-2"></i> Overview
                        </button>
                        <button class="nav-link" id="v-pills-bookings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-bookings" type="button" role="tab">
                            <i class="fas fa-ticket-alt me-2"></i> My Bookings
                        </button>
                        <button class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab">
                            <i class="fas fa-user me-2"></i> Profile Settings
                        </button>
                        <button class="nav-link" id="v-pills-password-tab" data-bs-toggle="pill" data-bs-target="#v-pills-password" type="button" role="tab">
                            <i class="fas fa-lock me-2"></i> Change Password
                        </button>
                        <div class="mt-4 pt-3 border-top">
                            <a href="logout.php" class="nav-link text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="tab-content" id="v-pills-tabContent">
                    
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="v-pills-overview" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="dashboard-card stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div class="stat-number"><?php echo count($bookings); ?></div>
                                    <div class="stat-label">Total Bookings</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-card stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="stat-number">$<?php echo number_format($total_spent, 2); ?></div>
                                    <div class="stat-label">Total Spent</div>
                                </div>
                            </div>
                            
                            </div>
                        </div>

                        <!-- Recent Bookings -->
                        <div class="dashboard-card">
                            <h3 class="card-title">Recent Bookings</h3>
                            <?php if(count($bookings) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Movie</th>
                                                <th>Date & Time</th>
                                                <th>Seats</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach(array_slice($bookings, 0, 5) as $booking): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?php echo htmlspecialchars($booking['poster_url']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($booking['movie_title']); ?>" 
                                                                 class="movie-poster-small me-3">
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($booking['movie_title']); ?></strong><br>
                                                                <small class="text-muted">Ticket: <?php echo $booking['ticket_number']; ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php echo $booking['formatted_date']; ?><br>
                                                        <small class="text-muted"><?php echo $booking['formatted_time']; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo $booking['total_seats']; ?> seat(s)<br>
                                                        <small class="text-muted"><?php echo $booking['seat_numbers']; ?></small>
                                                    </td>
                                                    <td>$<?php echo $booking['total_amount']; ?></td>
                                                    <td>
                                                        <?php if($booking['payment_status'] == 'completed'): ?>
                                                            <span class="status-badge status-confirmed">Confirmed</span>
                                                        <?php elseif($booking['payment_status'] == 'pending'): ?>
                                                            <span class="status-badge status-pending">Pending</span>
                                                        <?php else: ?>
                                                            <span class="status-badge status-cancelled"><?php echo ucfirst($booking['payment_status']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="view-ticket.php?id=<?php echo $booking['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if($booking['payment_status'] == 'pending'): ?>
                                                            <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" 
                                                               class="btn btn-sm btn-outline-success ms-1">
                                                                <i class="fas fa-credit-card"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="booking-history.php" class="btn btn-gold">View All Bookings</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-ticket-alt fa-4x text-muted mb-3"></i>
                                    <h4 class="text-muted">No bookings yet</h4>
                                    <p>Book your first movie ticket now!</p>
                                    <a href="index.php#movies" class="btn btn-gold">Browse Movies</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- My Bookings Tab -->
                    <div class="tab-pane fade" id="v-pills-bookings" role="tabpanel">
                        <div class="dashboard-card">
                            <h3 class="card-title">My Bookings</h3>
                            <?php if(count($bookings) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Movie</th>
                                                <th>Date & Time</th>
                                                <th>Seats</th>
                                                <th>Amount</th>
                                                <th>Payment</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($bookings as $booking): ?>
                                                <tr>
                                                    <td>#<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($booking['movie_title']); ?></strong><br>
                                                        <small class="text-muted">Ticket: <?php echo $booking['ticket_number']; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo $booking['formatted_date']; ?><br>
                                                        <small class="text-muted"><?php echo $booking['formatted_time']; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo $booking['total_seats']; ?> seat(s)<br>
                                                        <small class="text-muted"><?php echo $booking['seat_numbers']; ?></small>
                                                    </td>
                                                    <td>$<?php echo $booking['total_amount']; ?></td>
                                                    <td>
                                                        <?php if($booking['payment_status'] == 'completed'): ?>
                                                            <span class="status-badge status-confirmed">Paid</span>
                                                        <?php elseif($booking['payment_status'] == 'pending'): ?>
                                                            <span class="status-badge status-pending">Pending</span>
                                                        <?php else: ?>
                                                            <span class="status-badge status-cancelled"><?php echo ucfirst($booking['payment_status']); ?></span>
                                                        <?php endif; ?>
                                                        <br>
                                                        <small><?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="view-ticket.php?id=<?php echo $booking['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary" 
                                                               title="View Ticket">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if($booking['payment_status'] == 'pending'): ?>
                                                                <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" 
                                                                   class="btn btn-sm btn-outline-success" 
                                                                   title="Complete Payment">
                                                                    <i class="fas fa-credit-card"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if(strtotime($booking['show_date']) > strtotime('+1 day')): ?>
                                                                <button class="btn btn-sm btn-outline-danger" 
                                                                        title="Cancel Booking"
                                                                        onclick="confirmCancel(<?php echo $booking['id']; ?>)">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-ticket-alt fa-4x text-muted mb-3"></i>
                                    <h4 class="text-muted">No bookings yet</h4>
                                    <p>Book your first movie ticket now!</p>
                                    <a href="index.php#movies" class="btn btn-gold">Browse Movies</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Profile Settings Tab -->
                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel">
                        <div class="dashboard-card">
                            <h3 class="card-title">Profile Settings</h3>
                            <?php if(isset($profile_error)): ?>
                                <div class="alert alert-danger"><?php echo $profile_error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                        <small class="text-muted">Username cannot be changed</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Type</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo $user['is_admin'] ? 'Administrator' : 'Customer'; ?>" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-gold px-4">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="v-pills-password" role="tabpanel">
                        <div class="dashboard-card">
                            <h3 class="card-title">Change Password</h3>
                            <?php if(isset($password_error)): ?>
                                <div class="alert alert-danger"><?php echo $password_error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                        <small class="text-muted">At least 6 characters</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Password must be at least 6 characters long. For better security, use a mix of letters, numbers, and symbols.
                                </div>
                                <div class="mt-4">
                                    <button type="submit" name="change_password" class="btn btn-gold px-4">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
   

    <!-- Bootstrap JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmCancel(bookingId) {
            if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                window.location.href = 'cancel-booking.php?id=' + bookingId;
            }
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-danger)');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>