<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Get date range from URL or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['type']) ? $_GET['type'] : 'overview';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get report data
try {
    $stats = $database->getSystemStatistics();
    $revenue_stats = $database->getRevenueStatistics($start_date, $end_date);
    $booking_stats = $database->getBookingStatisticsByDate($start_date, $end_date);
    $movie_stats = $database->getMovieStatisticsReport($start_date, $end_date);
    $theatre_stats = $database->getTheatreStatisticsReport($start_date, $end_date);
    $user_stats = $database->getUserStatisticsReport($start_date, $end_date);
    $daily_revenue = $database->getDailyRevenue($start_date, $end_date);
    $payment_distribution = $database->getPaymentMethodDistribution($start_date, $end_date);
    $booking_trend = $database->getBookingTrendData($start_date, $end_date);
    
    // Prepare data for charts
    $chart_labels = [];
    $chart_revenue = [];
    $chart_bookings = [];
    $chart_seats = [];
    
    foreach ($daily_revenue as $day) {
        $chart_labels[] = date('M d', strtotime($day['date']));
        $chart_revenue[] = $day['revenue'];
        $chart_bookings[] = $day['bookings'];
    }
    
    // Prepare payment method data
    $payment_labels = ['Cash', 'Credit Card', 'Debit Card', 'PayPal'];
    $payment_data = [
        $revenue_stats['payment_methods']['cash'],
        $revenue_stats['payment_methods']['credit_card'],
        $revenue_stats['payment_methods']['debit_card'],
        $revenue_stats['payment_methods']['paypal']
    ];
    
    // Prepare booking status data
    $status_labels = ['Confirmed', 'Pending', 'Cancelled'];
    $status_data = [
        $booking_stats['confirmed_bookings'],
        $booking_stats['pending_bookings'],
        $booking_stats['cancelled_bookings']
    ];
    
} catch (Exception $e) {
    error_log("Report generation error: " . $e->getMessage());
    $error = "Failed to generate report. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports - CinemaKrish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            border-left: 4px solid;
        }
        .revenue-card { border-color: #28a745; }
        .booking-card { border-color: #17a2b8; }
        .user-card { border-color: #ffc107; }
        .movie-card { border-color: #dc3545; }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .nav-tabs .nav-link.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .export-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .export-btn:hover {
            background: #218838;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .stat-change {
            font-size: 0.9rem;
        }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.1);
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .rating-stars {
            color: #ffc107;
        }
        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
        .trend-neutral { color: #6c757d; }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-chart-line me-2"></i>Admin Reports & Analytics</h1>
                    <p class="mb-0">Comprehensive insights and performance metrics</p>
                </div>
                <div class="col-md-6 text-end">
                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-4">
                            <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="type">
                                <option value="overview" <?php echo $report_type == 'overview' ? 'selected' : ''; ?>>Overview</option>
                                <option value="revenue" <?php echo $report_type == 'revenue' ? 'selected' : ''; ?>>Revenue</option>
                                <option value="bookings" <?php echo $report_type == 'bookings' ? 'selected' : ''; ?>>Bookings</option>
                                <option value="movies" <?php echo $report_type == 'movies' ? 'selected' : ''; ?>>Movies</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-light w-100">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card revenue-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Total Revenue</h6>
                                <h2 class="stat-number">₹<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-rupee-sign fa-3x text-success"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="stat-change <?php echo $stats['monthly_revenue'] > 0 ? 'positive' : 'negative'; ?>">
                                <i class="fas fa-arrow-<?php echo $stats['monthly_revenue'] > 0 ? 'up' : 'down'; ?>"></i> 
                                ₹<?php echo number_format($stats['monthly_revenue'], 2); ?> this month
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card booking-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Total Bookings</h6>
                                <h2 class="stat-number"><?php echo number_format($stats['total_bookings']); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-ticket-alt fa-3x text-info"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> 
                                <?php echo $stats['today_bookings']; ?> today
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card user-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Total Users</h6>
                                <h2 class="stat-number"><?php echo number_format($stats['total_users']); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-3x text-warning"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> 
                                <?php echo $stats['new_users']; ?> new this month
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card movie-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Active Movies</h6>
                                <h2 class="stat-number"><?php echo number_format($stats['active_movies']); ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-film fa-3x text-danger"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> 
                                <?php echo $stats['total_movies']; ?> total movies
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="reportTabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type == 'overview' ? 'active' : ''; ?>" 
                   href="?type=overview&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                    <i class="fas fa-chart-pie me-1"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type == 'revenue' ? 'active' : ''; ?>" 
                   href="?type=revenue&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                    <i class="fas fa-money-bill-wave me-1"></i> Revenue
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type == 'bookings' ? 'active' : ''; ?>" 
                   href="?type=bookings&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                    <i class="fas fa-ticket-alt me-1"></i> Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type == 'movies' ? 'active' : ''; ?>" 
                   href="?type=movies&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>">
                    <i class="fas fa-film me-1"></i> Movies
                </a>
            </li>
        </ul>

        <!-- Report Content -->
        <div class="tab-content">
            <?php if ($report_type == 'overview'): ?>
                <!-- Overview Tab -->
                <div class="row">
                    <!-- Revenue Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Revenue Trend
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($chart_labels)): ?>
                                    <div class="chart-container">
                                        <canvas id="revenueChart"></canvas>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                                        <p>No revenue data available for the selected period</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Status -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>Booking Status
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="bookingStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Movies -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>Top 5 Movies by Revenue
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Movie</th>
                                                <th>Bookings</th>
                                                <th>Revenue</th>
                                                <th>Avg. Ticket</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($movie_stats['top_movies'])): ?>
                                                <?php foreach ($movie_stats['top_movies'] as $movie): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($movie['title']); ?></strong>
                                                            <?php if ($movie['is_featured']): ?>
                                                                <span class="badge bg-warning ms-1">Featured</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo $movie['bookings']; ?></td>
                                                        <td>₹<?php echo number_format($movie['revenue'], 2); ?></td>
                                                        <td>₹<?php echo number_format($movie['avg_ticket'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No movie data available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Theatres -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-theater-masks me-2"></i>Top Theatres
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Theatre</th>
                                                <th>City</th>
                                                <th>Bookings</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($theatre_stats['top_theatres'])): ?>
                                                <?php foreach ($theatre_stats['top_theatres'] as $theatre): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($theatre['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($theatre['city']); ?></td>
                                                        <td><?php echo $theatre['bookings']; ?></td>
                                                        <td>₹<?php echo number_format($theatre['revenue'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No theatre data available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type == 'revenue'): ?>
                <!-- Revenue Tab -->
                <div class="row">
                    <!-- Revenue Summary -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Revenue Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Total Revenue</p>
                                        <h3>₹<?php echo number_format($revenue_stats['total_revenue'], 2); ?></h3>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Average Daily</p>
                                        <h3>₹<?php echo number_format($revenue_stats['avg_daily'], 2); ?></h3>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Highest Day</p>
                                        <h4 class="positive">
                                            ₹<?php echo number_format($revenue_stats['max_daily'], 2); ?>
                                        </h4>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Lowest Day</p>
                                        <h4 class="negative">
                                            ₹<?php echo number_format($revenue_stats['min_daily'], 2); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue by Payment Method -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Revenue by Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="paymentMethodChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Revenue Table -->
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Daily Revenue Breakdown</h5>
                                <button class="btn btn-success btn-sm" onclick="exportData('revenue')">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($daily_revenue)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Bookings</th>
                                                    <th>Revenue</th>
                                                    <th>Avg. Revenue/Booking</th>
                                                    <th>Trend</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($daily_revenue as $day): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                                    <td><?php echo $day['bookings']; ?></td>
                                                    <td>₹<?php echo number_format($day['revenue'], 2); ?></td>
                                                    <td>₹<?php echo number_format($day['avg_booking'], 2); ?></td>
                                                    <td>
                                                        <?php if ($day['change'] > 5): ?>
                                                            <span class="trend-up">
                                                                <i class="fas fa-arrow-up"></i> 
                                                                <?php echo number_format($day['change'], 1); ?>%
                                                            </span>
                                                        <?php elseif ($day['change'] < -5): ?>
                                                            <span class="trend-down">
                                                                <i class="fas fa-arrow-down"></i> 
                                                                <?php echo number_format(abs($day['change']), 1); ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="trend-neutral">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                        <p>No revenue data available for the selected period</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type == 'bookings'): ?>
                <!-- Bookings Tab -->
                <div class="row">
                    <!-- Booking Statistics -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Booking Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Total Bookings</p>
                                    <h2><?php echo number_format($booking_stats['total_bookings']); ?></h2>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Confirmed</p>
                                        <h4 class="positive"><?php echo $booking_stats['confirmed_bookings']; ?></h4>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Pending</p>
                                        <h4 class="text-warning"><?php echo $booking_stats['pending_bookings']; ?></h4>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Cancelled</p>
                                        <h4 class="negative"><?php echo $booking_stats['cancelled_bookings']; ?></h4>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Avg Seats/Booking</p>
                                        <h4><?php echo number_format($booking_stats['avg_seats'], 1); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Trend -->
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Booking Trend</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($chart_labels)): ?>
                                    <div class="chart-container">
                                        <canvas id="bookingTrendChart"></canvas>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                        <p>No booking data available for the selected period</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Booking List -->
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Bookings</h5>
                                <button class="btn btn-success btn-sm" onclick="exportData('bookings')">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($booking_stats['recent_bookings'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Ticket #</th>
                                                    <th>Customer</th>
                                                    <th>Movie</th>
                                                    <th>Theatre</th>
                                                    <th>Show Date</th>
                                                    <th>Seats</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($booking_stats['recent_bookings'] as $booking): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($booking['ticket_number'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['customer_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['movie_title'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['theatre_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($booking['show_date'] ?? 'now')); ?></td>
                                                    <td><?php echo htmlspecialchars($booking['seat_numbers'] ?? 'N/A'); ?></td>
                                                    <td>₹<?php echo number_format($booking['total_amount'] ?? 0, 2); ?></td>
                                                    <td>
                                                        <?php if (($booking['booking_status'] ?? '') == 'confirmed'): ?>
                                                            <span class="badge bg-success">Confirmed</span>
                                                        <?php elseif (($booking['booking_status'] ?? '') == 'pending'): ?>
                                                            <span class="badge bg-warning text-dark">Pending</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Cancelled</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                        <p>No booking data available for the selected period</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type == 'movies'): ?>
                <!-- Movies Tab -->
                <div class="row">
                    <!-- Movie Performance -->
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Movie Performance</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($movie_stats['top_movies'])): ?>
                                    <div class="chart-container">
                                        <canvas id="moviePerformanceChart"></canvas>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <i class="fas fa-film fa-3x mb-3"></i>
                                        <p>No movie performance data available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Movie Statistics -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Movie Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Total Movies</p>
                                    <h2><?php echo number_format($movie_stats['total_movies']); ?></h2>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Now Showing</p>
                                        <h4 class="positive"><?php echo $movie_stats['now_showing']; ?></h4>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Coming Soon</p>
                                        <h4 class="text-info"><?php echo $movie_stats['coming_soon']; ?></h4>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Featured</p>
                                        <h4 class="text-warning"><?php echo $movie_stats['featured']; ?></h4>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Avg Rating</p>
                                        <h4><?php echo number_format($movie_stats['avg_rating'], 1); ?>/5</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Movie List -->
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">All Movies Performance</h5>
                                <button class="btn btn-success btn-sm" onclick="exportData('movies')">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($movie_stats['all_movies'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Movie</th>
                                                    <th>Language</th>
                                                    <th>Genre</th>
                                                    <th>Rating</th>
                                                    <th>Bookings</th>
                                                    <th>Revenue</th>
                                                    <th>Occupancy %</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($movie_stats['all_movies'] as $movie): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($movie['title']); ?></strong>
                                                        <?php if ($movie['is_featured']): ?>
                                                            <span class="badge bg-warning ms-1">Featured</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($movie['language_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($movie['genre_name'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <div class="rating-stars">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?php echo $i <= $movie['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                            <?php endfor; ?>
                                                            <span class="ms-1"><?php echo number_format($movie['rating'], 1); ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $movie['bookings']; ?></td>
                                                    <td>₹<?php echo number_format($movie['revenue'], 2); ?></td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar <?php echo $movie['occupancy'] > 70 ? 'bg-success' : ($movie['occupancy'] > 30 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                                 role="progressbar" 
                                                                 style="width: <?php echo min($movie['occupancy'], 100); ?>%">
                                                                <?php echo number_format($movie['occupancy'], 1); ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($movie['is_now_showing']): ?>
                                                            <span class="badge bg-success">Now Showing</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-info">Coming Soon</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <i class="fas fa-film fa-3x mb-3"></i>
                                        <p>No movie data available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Export Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-download me-2"></i>Export Reports
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-primary w-100" onclick="exportData('overview')">
                                    <i class="fas fa-file-alt me-1"></i> Overview Report
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-success w-100" onclick="exportData('revenue')">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> Revenue Report
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-info w-100" onclick="exportData('bookings')">
                                    <i class="fas fa-file-invoice me-1"></i> Booking Report
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-warning w-100" onclick="exportData('movies')">
                                    <i class="fas fa-file-video me-1"></i> Movie Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prepare chart data from PHP
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        const chartRevenue = <?php echo json_encode($chart_revenue); ?>;
        const chartBookings = <?php echo json_encode($chart_bookings); ?>;
        const paymentData = <?php echo json_encode($payment_data); ?>;
        const statusData = <?php echo json_encode($status_data); ?>;
        const topMovies = <?php echo json_encode($movie_stats['top_movies']); ?>;

        // Initialize charts only if data exists
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($chart_labels)): ?>
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Daily Revenue',
                            data: chartRevenue,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '₹' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
            <?php endif; ?>

            // Booking Status Chart
            const bookingStatusCtx = document.getElementById('bookingStatusChart');
            if (bookingStatusCtx) {
                new Chart(bookingStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Confirmed', 'Pending', 'Cancelled'],
                        datasets: [{
                            data: statusData,
                            backgroundColor: [
                                '#28a745',
                                '#ffc107',
                                '#dc3545'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Payment Method Chart
            const paymentMethodCtx = document.getElementById('paymentMethodChart');
            if (paymentMethodCtx) {
                new Chart(paymentMethodCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Cash', 'Credit Card', 'Debit Card', 'PayPal'],
                        datasets: [{
                            data: paymentData,
                            backgroundColor: [
                                '#28a745',
                                '#17a2b8',
                                '#ffc107',
                                '#007bff'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ₹${value.toLocaleString()} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Booking Trend Chart
            const bookingTrendCtx = document.getElementById('bookingTrendChart');
            if (bookingTrendCtx && chartLabels.length > 0) {
                new Chart(bookingTrendCtx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Daily Bookings',
                            data: chartBookings,
                            backgroundColor: '#17a2b8',
                            borderColor: '#117a8b',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Movie Performance Chart
            const moviePerformanceCtx = document.getElementById('moviePerformanceChart');
            if (moviePerformanceCtx && topMovies.length > 0) {
                const movieLabels = topMovies.map(movie => movie.title.substring(0, 20));
                const movieRevenue = topMovies.map(movie => movie.revenue);
                const movieBookings = topMovies.map(movie => movie.bookings);
                
                new Chart(moviePerformanceCtx, {
                    type: 'bar',
                    data: {
                        labels: movieLabels,
                        datasets: [{
                            label: 'Revenue',
                            data: movieRevenue,
                            backgroundColor: '#dc3545',
                            borderColor: '#bd2130',
                            borderWidth: 1,
                            yAxisID: 'y'
                        }, {
                            label: 'Bookings',
                            data: movieBookings,
                            backgroundColor: '#ffc107',
                            borderColor: '#d39e00',
                            borderWidth: 1,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString();
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false
                                },
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        });

        // Export functions
        function exportData(type) {
            const startDate = '<?php echo $start_date; ?>';
            const endDate = '<?php echo $end_date; ?>';
            
            // Create export URL
            let url = `export_report.php?type=${type}&start_date=${startDate}&end_date=${endDate}`;
            
            // Add specific parameters based on type
            if (type === 'bookings') {
                url += '&format=csv';
            }
            
            // Open in new tab
            window.open(url, '_blank');
        }

        // Auto-refresh every 5 minutes (300000 ms)
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>