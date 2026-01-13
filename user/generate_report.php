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

$type = $_GET['type'] ?? 'weekly';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Generate reports based on type
switch ($type) {
    case 'weekly':
        $title = "Weekly Report (" . date('M d', strtotime($start_date)) . " - " . date('M d', strtotime($end_date)) . ")";
        $bookings = $database->getBookingsByDateRange($start_date, $end_date);
        $revenue = $database->getRevenueByDateRange($start_date, $end_date);
        $movies = $database->getPopularMoviesByDateRange($start_date, $end_date, 10);
        break;
        
    case 'monthly':
        $month = date('m');
        $year = date('Y');
        $title = "Monthly Report (" . date('F Y') . ")";
        $bookings = $database->getBookingsByMonth($month, $year);
        $revenue = $database->getRevenueByMonth($month, $year);
        $movies = $database->getPopularMoviesByMonth($month, $year, 10);
        break;
        
    case 'yearly':
        $year = date('Y');
        $title = "Yearly Report (" . $year . ")";
        $bookings = $database->getBookingsByYear($year);
        $revenue = $database->getRevenueByYear($year);
        $movies = $database->getPopularMoviesByYear($year, 10);
        break;
        
    default:
        $title = "Custom Report";
        $bookings = $database->getBookingsByDateRange($start_date, $end_date);
        $revenue = $database->getRevenueByDateRange($start_date, $end_date);
        $movies = $database->getPopularMoviesByDateRange($start_date, $end_date, 10);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - CinemaKrish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12pt; }
            .container { width: 100% !important; }
        }
        body { background: white; }
        .report-header { border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .stat-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="report-header">
            <div class="row">
                <div class="col-md-6">
                    <h1 class="mb-0">CinemaKrish</h1>
                    <h3><?php echo $title; ?></h3>
                    <p>Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <button onclick="window.print()" class="btn btn-primary no-print">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <button onclick="window.close()" class="btn btn-secondary no-print">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-box text-center">
                    <h5>Total Bookings</h5>
                    <h2 class="text-primary"><?php echo count($bookings); ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box text-center">
                    <h5>Total Revenue</h5>
                    <h2 class="text-success">$<?php echo number_format($revenue['total_revenue'], 2); ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box text-center">
                    <h5>Avg. Ticket Price</h5>
                    <h2 class="text-info">
                        $<?php echo count($bookings) > 0 ? number_format($revenue['total_revenue'] / count($bookings), 2) : '0.00'; ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box text-center">
                    <h5>Total Seats Sold</h5>
                    <h2 class="text-warning"><?php echo $revenue['total_seats'] ?? 0; ?></h2>
                </div>
            </div>
        </div>

        <!-- Popular Movies -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Top 10 Popular Movies</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Movie Title</th>
                            <th>Bookings</th>
                            <th>Revenue</th>
                            <th>Seats Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $index => $movie): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                <td><?php echo $movie['bookings_count']; ?></td>
                                <td>$<?php echo number_format($movie['revenue'], 2); ?></td>
                                <td><?php echo $movie['seats_sold']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Booking Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Date</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['ticket_number']); ?></td>
                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['show_date'])); ?></td>
                                <td><?php echo $booking['total_seats']; ?></td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $booking['booking_status'] === 'confirmed' ? 'success' : 
                                        ($booking['booking_status'] === 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="card">
            <div class="card-header">
                <h5>Revenue Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th>Confirmed Bookings Revenue:</th>
                                <td class="text-success">$<?php echo number_format($revenue['confirmed_revenue'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Pending Bookings Revenue:</th>
                                <td class="text-warning">$<?php echo number_format($revenue['pending_revenue'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Cancelled Bookings Revenue:</th>
                                <td class="text-danger">$<?php echo number_format($revenue['cancelled_revenue'] ?? 0, 2); ?></td>
                            </tr>
                            <tr class="table-primary">
                                <th><strong>Net Revenue:</strong></th>
                                <td><strong>$<?php echo number_format($revenue['total_revenue'], 2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th>Online Payments:</th>
                                <td>$<?php echo number_format($revenue['online_payments'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Cash Payments:</th>
                                <td>$<?php echo number_format($revenue['cash_payments'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Card Payments:</th>
                                <td>$<?php echo number_format($revenue['card_payments'] ?? 0, 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center no-print">
            <p class="text-muted">Report generated by: <?php echo $_SESSION['username']; ?></p>
            <hr>
            <small class="text-muted">CinemaKrish Admin Panel - This is a computer-generated report.</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-print if requested
        if (window.location.search.includes('print=true')) {
            window.print();
        }
    </script>
</body>
</html>