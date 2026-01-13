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

// Get date ranges
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last_week_start = date('Y-m-d', strtotime('-7 days'));
$last_month_start = date('Y-m-d', strtotime('-30 days'));
$current_month_start = date('Y-m-01');
$current_year_start = date('Y-01-01');

// Get statistics
$today_stats = $database->getRevenueByDateRange($today, $today);
$yesterday_stats = $database->getRevenueByDateRange($yesterday, $yesterday);
$week_stats = $database->getRevenueByDateRange($last_week_start, $today);
$month_stats = $database->getRevenueByDateRange($current_month_start, $today);
$year_stats = $database->getRevenueByDateRange($current_year_start, $today);

// Get popular movies this month
$popular_movies = $database->getPopularMoviesByDateRange($current_month_start, $today, 5);

// Get booking trends
$booking_trends = $database->getBookingTrends(30);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - CinemaKrish Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        <?php include 'admin_styles.css'; ?>
        .report-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .date-range-picker {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navbar -->
        <?php include 'admin_navbar.php'; ?>

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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Reports & Analytics</h4>
            <div class="d-flex">
                <div class="input-group date-range-picker me-3">
                    <input type="date" class="form-control" id="start_date" value="<?php echo $last_week_start; ?>">
                    <span class="input-group-text">to</span>
                    <input type="date" class="form-control" id="end_date" value="<?php echo $today; ?>">
                    <button class="btn btn-admin" onclick="loadCustomReport()">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-admin" onclick="generateReport('today')">
                        Today
                    </button>
                    <button class="btn btn-admin" onclick="generateReport('weekly')">
                        This Week
                    </button>
                    <button class="btn btn-admin" onclick="generateReport('monthly')">
                        This Month
                    </button>
                    <button class="btn btn-admin" onclick="generateReport('yearly')">
                        This Year
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="report-card text-center">
                    <small class="text-muted">Today's Revenue</small>
                    <div class="stat-number text-success">
                        $<?php echo number_format($today_stats['total_revenue'] ?? 0, 2); ?>
                    </div>
                    <small>
                        <i class="fas fa-calendar-day"></i> <?php echo date('M d'); ?>
                    </small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="report-card text-center">
                    <small class="text-muted">Yesterday's Revenue</small>
                    <div class="stat-number text-info">
                        $<?php echo number_format($yesterday_stats['total_revenue'] ?? 0, 2); ?>
                    </div>
                    <small>
                        <i class="fas fa-calendar"></i> <?php echo date('M d', strtotime('-1 day')); ?>
                    </small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="report-card text-center">
                    <small class="text-muted">Last 7 Days</small>
                    <div class="stat-number text-primary">
                        $<?php echo number_format($week_stats['total_revenue'] ?? 0, 2); ?>
                    </div>
                    <small>
                        <i class="fas fa-calendar-week"></i> Weekly
                    </small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="report-card text-center">
                    <small class="text-muted">This Month</small>
                    <div class="stat-number text-warning">
                        $<?php echo number_format($month_stats['total_revenue'] ?? 0, 2); ?>
                    </div>
                    <small>
                        <i class="fas fa-calendar-alt"></i> <?php echo date('F'); ?>
                    </small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="report-card text-center">
                    <small class="text-muted">This Year</small>
                    <div class="stat-number text-danger">
                        $<?php echo number_format($year_stats['total_revenue'] ?? 0, 2); ?>
                    </div>
                    <small>
                        <i class="fas fa-calendar-year"></i> <?php echo date('Y'); ?>
                    </small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="report-card text-center">
                    <small class="text-muted">Avg. Daily</small>
                    <div class="stat-number text-secondary">
                        $<?php 
                            $days = date('j');
                            echo number_format(($month_stats['total_revenue'] ?? 0) / ($days > 0 ? $days : 1), 2); 
                        ?>
                    </div>
                    <small>
                        <i class="fas fa-chart-line"></i> Average
                    </small>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-8">
                <div class="chart-container">
                    <h5>Revenue Trend (Last 30 Days)</h5>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h5>Popular Movies This Month</h5>
                    <div class="list-group">
                        <?php foreach ($popular_movies as $index => $movie): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                                    <?php echo htmlspecialchars($movie['title']); ?>
                                </div>
                                <div class="text-end">
                                    <div class="text-success">$<?php echo number_format($movie['revenue'], 2); ?></div>
                                    <small class="text-muted"><?php echo $movie['bookings_count']; ?> bookings</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Booking Status Distribution</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Payment Methods</h5>
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="report-card">
                    <h5>Export Reports</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-admin w-100 mb-2" onclick="exportReport('bookings')">
                                <i class="fas fa-file-csv me-2"></i> Bookings CSV
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-admin w-100 mb-2" onclick="exportReport('revenue')">
                                <i class="fas fa-file-excel me-2"></i> Revenue Excel
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-admin w-100 mb-2" onclick="exportReport('movies')">
                                <i class="fas fa-film me-2"></i> Movies Report
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-admin w-100 mb-2" onclick="exportReport('summary')">
                                <i class="fas fa-chart-pie me-2"></i> Summary PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($booking_trends, 'date')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($booking_trends, 'revenue')); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
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

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Confirmed', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [
                        <?php echo $month_stats['confirmed_bookings'] ?? 0; ?>,
                        <?php echo $month_stats['pending_bookings'] ?? 0; ?>,
                        <?php echo $month_stats['cancelled_bookings'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#27ae60',
                        '#f39c12',
                        '#e74c3c'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Payment Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'bar',
            data: {
                labels: ['Credit Card', 'Debit Card', 'PayPal', 'Cash'],
                datasets: [{
                    label: 'Transactions',
                    data: [
                        <?php echo $month_stats['credit_card_count'] ?? 0; ?>,
                        <?php echo $month_stats['debit_card_count'] ?? 0; ?>,
                        <?php echo $month_stats['paypal_count'] ?? 0; ?>,
                        <?php echo $month_stats['cash_count'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#003087',
                        '#95a5a6'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
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

        function generateReport(type) {
            window.open(`generate_report.php?type=${type}&print=true`, '_blank');
        }

        function loadCustomReport() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            window.open(`generate_report.php?start_date=${startDate}&end_date=${endDate}&print=true`, '_blank');
        }

        function exportReport(type) {
            switch(type) {
                case 'bookings':
                    window.location.href = 'export_bookings.php';
                    break;
                case 'revenue':
                    window.location.href = 'export_revenue.php?type=monthly';
                    break;
                case 'movies':
                    window.location.href = 'export_movies.php';
                    break;
                case 'summary':
                    window.open('generate_report.php?type=monthly&print=true', '_blank');
                    break;
            }
        }

        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>