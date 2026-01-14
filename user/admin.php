<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is admin
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] != 1
) {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
$systemStats = $database->getSystemStatistics();
$recent_activities = $database->getRecentActivities(10);
// $revenueStats = $database->getRevenueStatistics();
$popularMovies = $database->getPopularMovies(5);
$theatres = $database->getAllTheatres();
$shows = $database->getShowsWithDetails();
$userStats = $database->getUserStatistics();
$movieStats = $database->getMovieStatistics();
$bookingStats = $database->getBookingStatistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaKrish Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a252f 100%);
            min-height: 100vh;
            position: fixed;
            width: 250px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: #bdc3c7;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(52, 152, 219, 0.2);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 24px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border-left: 5px solid var(--secondary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .stat-card h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-card p {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            color: white;
        }
        
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                transition: margin-left 0.3s ease;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
                <?php include 'admin_sidebar.php'; ?>
    

    <!-- Main Content -->
    <div class="main-content">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navbar -->
        <nav class="navbar navbar-light bg-light mb-4">
            <div class="container-fluid">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <h4 class="mb-0">Admin Dashboard</h4>
                    <div class="d-flex align-items-center">
                        <div class="dropdown ms-3">
                            <button class="btn btn-admin dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2"></i> <?php echo $_SESSION['username']; ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admin.php"><i class="fas fa-user me-2"></i>Profile</a></li>
   
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard Content -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h2><?php echo $systemStats['total_users']; ?></h2>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-film"></i>
                    <h2><?php echo $systemStats['active_movies']; ?></h2>
                    <p>Active Movies</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-ticket-alt"></i>
                    <h2><?php echo $systemStats['today_bookings']; ?></h2>
                    <p>Today's Bookings</p>
                </div>
            </div>
            <!-- <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <h2>$<?php echo number_format($systemStats['today_revenue'], 2); ?></h2>
                    <p>Today's Revenue</p>
                </div>
            </div> -->
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- <div class="chart-container">
                    <h5>Revenue Trends (Last 7 Days)</h5>
                    <canvas id="revenueChart"></canvas>
                </div> -->
            </div>
           

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Popular Movies (Most Bookings)</h5>
                    <ul class="list-group">
                        <?php foreach ($popularMovies as $movie): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($movie['title']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $movie['bookings_count']; ?> bookings</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Quick Stats</h5>
                    <div class="row">
                        <!-- <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total Theatres</small>
                                <h4 class="mb-0"><?php echo $systemStats['total_theatres']; ?></h4>
                            </div>
                        </div> -->
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total Screens</small>
                                <h4 class="mb-0"><?php echo array_sum(array_column($theatres, 'total_screens')); ?></h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Total Shows</small>
                                <h4 class="mb-0"><?php echo count($shows); ?></h4>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Avg. Ticket Price</small>
                                <h4 class="mb-0">$<?php echo number_format(array_sum(array_column($shows, 'ticket_price')) / count($shows), 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <div class="row mt-6">
                <div class="recent-activity">
                    <h5>Recent Activities</h5>
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php if ($activity['type'] === 'user_registered'): ?>
                                    <i class="fas fa-user-plus text-primary"></i>
                                <?php elseif ($activity['type'] === 'movie_added'): ?>
                                    <i class="fas fa-film text-danger"></i>
                                <?php else: ?>
                                    <i class="fas fa-ticket-alt text-success"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date('M d, Y', strtotime($activity['date'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart.js for Dashboard
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($revenueStats, 'date')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($revenueStats, 'revenue')); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });

        // Sidebar toggle for mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>