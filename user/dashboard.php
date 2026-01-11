<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new Database();

// Get user details
$db->query("SELECT * FROM users WHERE id = ?");
$db->bind(1, $user_id);
$user = $db->single();

// Get recent bookings
$db->query("SELECT b.*, m.title as movie_title, m.poster_image, 
                   t.name as theatre_name, s.show_date, s.show_time
            FROM bookings b
            JOIN shows s ON b.show_id = s.id
            JOIN movies m ON s.movie_id = m.id
            JOIN theatres t ON s.theatre_id = t.id
            WHERE b.user_id = ?
            ORDER BY b.booking_date DESC
            LIMIT 5");
$db->bind(1, $user_id);
$recent_bookings = $db->resultSet();

// Get total bookings count
$db->query("SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?");
$db->bind(1, $user_id);
$stats = $db->single();
$total_bookings = $stats->total_bookings;

// Get total spent
$db->query("SELECT SUM(total_amount) as total_spent FROM bookings 
            WHERE user_id = ? AND payment_status = 'success'");
$db->bind(1, $user_id);
$spent_stats = $db->single();
$total_spent = $spent_stats->total_spent ?: 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .dashboard-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .recent-booking-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .quick-actions .btn {
            padding: 10px 15px;
            margin: 5px;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .dashboard-card {
                padding: 15px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="welcome-card dashboard-card mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Welcome back, <?php echo htmlspecialchars($user->name); ?>!</h2>
                    <p class="mb-0">Manage your bookings, update profile, and explore new movies.</p>
                </div>
                <div class="col-md-4 text-center">
                    <img src="../assets/uploads/profile/<?php echo $user->profile_image ?: 'default.jpg'; ?>" 
                         alt="Profile" class="profile-avatar">
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="stat-card dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-6 fw-bold"><?php echo $total_bookings; ?></h3>
                            <p class="mb-0">Total Bookings</p>
                        </div>
                        <i class="bi bi-ticket-perforated display-4 opacity-75"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="dashboard-card bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-6 fw-bold">₹<?php echo number_format($total_spent, 0); ?></h3>
                            <p class="mb-0">Total Spent</p>
                        </div>
                        <i class="bi bi-currency-rupee display-4 opacity-75"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="dashboard-card bg-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-6 fw-bold">0</h3>
                            <p class="mb-0">Upcoming Shows</p>
                        </div>
                        <i class="bi bi-calendar-check display-4 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="row">
            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions text-center">
                            <a href="my-bookings.php" class="btn btn-outline-primary d-block mb-2">
                                <i class="bi bi-ticket-perforated"></i> My Bookings
                            </a>
                            <a href="profile.php" class="btn btn-outline-success d-block mb-2">
                                <i class="bi bi-person"></i> Edit Profile
                            </a>
                            <a href="movies.php" class="btn btn-outline-info d-block mb-2">
                                <i class="bi bi-film"></i> Book Tickets
                            </a>
                            <a href="change-password.php" class="btn btn-outline-warning d-block mb-2">
                                <i class="bi bi-shield-lock"></i> Change Password
                            </a>
                            <a href="logout.php" class="btn btn-outline-danger d-block">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Summary -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Profile Summary</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong>Name:</strong> <?php echo htmlspecialchars($user->name); ?>
                            </li>
                            <li class="mb-2">
                                <strong>Email:</strong> <?php echo htmlspecialchars($user->email); ?>
                            </li>
                            <li class="mb-2">
                                <strong>Phone:</strong> <?php echo htmlspecialchars($user->phone); ?>
                            </li>
                            <li class="mb-2">
                                <strong>Member Since:</strong> <?php echo formatDate($user->created_at); ?>
                            </li>
                            <li>
                                <strong>Status:</strong> 
                                <span class="badge bg-<?php echo $user->status == 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($user->status); ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Bookings</h5>
                        <a href="my-bookings.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if(empty($recent_bookings)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-ticket display-4 text-muted"></i>
                            <h5 class="mt-3">No bookings yet</h5>
                            <p class="text-muted">Book your first movie ticket and enjoy the show!</p>
                            <a href="movies.php" class="btn btn-primary">Book Now</a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Movie</th>
                                        <th>Theatre</th>
                                        <th>Date & Time</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/uploads/movies/<?php echo $booking->poster_image; ?>" 
                                                     alt="<?php echo $booking->movie_title; ?>" 
                                                     class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <span><?php echo $booking->movie_title; ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo $booking->theatre_name; ?></td>
                                        <td>
                                            <?php echo formatDate($booking->show_date); ?><br>
                                            <small class="text-muted"><?php echo formatTime($booking->show_time); ?></small>
                                        </td>
                                        <td class="fw-bold">₹<?php echo number_format($booking->total_amount, 2); ?></td>
                                        <td>
                                            <?php if($booking->payment_status == 'success'): ?>
                                                <span class="badge bg-success">Confirmed</span>
                                            <?php elseif($booking->payment_status == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view-booking.php?id=<?php echo $booking->id; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recommended Movies -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recommended For You</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get recommended movies based on previous bookings
                        $db->query("SELECT m.* FROM movies m 
                                   WHERE m.status = 'now_showing' 
                                   ORDER BY m.rating DESC LIMIT 3");
                        $recommended_movies = $db->resultSet();
                        ?>
                        
                        <div class="row g-3">
                            <?php foreach($recommended_movies as $movie): ?>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <img src="../assets/uploads/movies/<?php echo $movie->poster_image; ?>" 
                                             alt="<?php echo $movie->title; ?>" 
                                             class="img-fluid rounded mb-3" style="height: 150px; object-fit: cover;">
                                        <h6 class="card-title"><?php echo $movie->title; ?></h6>
                                        <div class="mb-2">
                                            <span class="badge bg-primary"><?php echo $movie->language; ?></span>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-star-fill"></i> <?php echo $movie->rating; ?>
                                            </span>
                                        </div>
                                        <a href="movie-details.php?id=<?php echo $movie->id; ?>" 
                                           class="btn btn-sm btn-primary w-100">Book Now</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Auto-refresh bookings every 30 seconds
        setInterval(function() {
            fetch('../api/check-new-bookings.php?user_id=<?php echo $user_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if(data.new_bookings > 0) {
                        // Show notification
                        if(Notification.permission === 'granted') {
                            new Notification('New Booking!', {
                                body: 'You have new booking updates',
                                icon: '../assets/images/logo.png'
                            });
                        }
                        
                        // Refresh page
                        location.reload();
                    }
                });
        }, 30000);
        
        // Request notification permission
        if('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>