<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check admin authentication
if(!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$db = new Database();

// Get dashboard statistics
$db->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $db->single()->total_users;

$db->query("SELECT COUNT(*) as total_bookings FROM bookings WHERE booking_status = 'confirmed'");
$total_bookings = $db->single()->total_bookings;

$db->query("SELECT SUM(total_amount) as total_revenue FROM bookings 
            WHERE payment_status = 'success' AND booking_status = 'confirmed'");
$total_revenue = $db->single()->total_revenue ?: 0;

$db->query("SELECT COUNT(*) as active_movies FROM movies WHERE status = 'now_showing'");
$active_movies = $db->single()->active_movies;

// Recent bookings
$db->query("SELECT b.*, u.name as user_name, m.title as movie_title 
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN shows s ON b.show_id = s.id
            JOIN movies m ON s.movie_id = m.id
            ORDER BY b.booking_date DESC LIMIT 10");
$recent_bookings = $db->resultSet();

// Revenue chart data (last 7 days)
$db->query("SELECT DATE(booking_date) as date, SUM(total_amount) as revenue
            FROM bookings
            WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND payment_status = 'success'
            GROUP BY DATE(booking_date)
            ORDER BY date");
$revenue_data = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Admin CSS -->
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- Admin Header -->
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="movies/manage-movies.php">
                                <i class="bi bi-film"></i>
                                Movies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="theatres/manage-theatres.php">
                                <i class="bi bi-building"></i>
                                Theatres
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="bookings/all-bookings.php">
                                <i class="bi bi-ticket-perforated"></i>
                                Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users/manage-users.php">
                                <i class="bi bi-people"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports/revenue-report.php">
                                <i class="bi bi-bar-chart"></i>
                                Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings/site-settings.php">
                                <i class="bi bi-gear"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="text-muted me-3">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Users
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Bookings
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_bookings; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-ticket-perforated fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Revenue
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ₹<?php echo number_format($total_revenue, 2); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-rupee fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Active Movies
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $active_movies; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-film fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Revenue Overview (Last 7 Days)</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" width="100%" height="40"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="movies/add-movie.php" class="list-group-item list-group-item-action">
                                        <i class="bi bi-plus-circle me-2"></i>Add New Movie
                                    </a>
                                    <a href="theatres/add-theatre.php" class="list-group-item list-group-item-action">
                                        <i class="bi bi-plus-circle me-2"></i>Add New Theatre
                                    </a>
                                    <a href="theatres/add-show.php" class="list-group-item list-group-item-action">
                                        <i class="bi bi-clock me-2"></i>Add Show Time
                                    </a>
                                    <a href="bookings/all-bookings.php" class="list-group-item list-group-item-action">
                                        <i class="bi bi-list-check me-2"></i>View All Bookings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>User</th>
                                        <th>Movie</th>
                                        <th>Seats</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <a href="bookings/view-booking.php?id=<?php echo $booking->id; ?>">
                                                <?php echo $booking->booking_id; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $booking->user_name; ?></td>
                                        <td><?php echo $booking->movie_title; ?></td>
                                        <td><?php echo $booking->seat_numbers; ?></td>
                                        <td>₹<?php echo $booking->total_amount; ?></td>
                                        <td><?php echo formatDate($booking->booking_date); ?></td>
                                        <td>
                                            <?php if($booking->payment_status == 'success'): ?>
                                                <span class="badge bg-success">Confirmed</span>
                                            <?php elseif($booking->payment_status == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Revenue Chart
        const revenueData = {
            labels: [<?php echo implode(',', array_map(function($item) {
                return "'" . date('d M', strtotime($item->date)) . "'";
            }, $revenue_data)); ?>],
            datasets: [{
                label: 'Revenue (₹)',
                data: [<?php echo implode(',', array_map(function($item) {
                    return $item->revenue;
                }, $revenue_data)); ?>],
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: 'rgba(78, 115, 223, 1)',
                pointHoverRadius: 5,
                pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                tension: 0.3
            }]
        };

        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: revenueData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₹' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>