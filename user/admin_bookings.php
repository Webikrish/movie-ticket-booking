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

// Handle actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';
$search_term = $_GET['search'] ?? '';

// Handle delete
if ($action == 'delete' && $id) {
    if ($database->deleteBooking($id)) {
        header('Location: admin_bookings.php?success=Booking deleted successfully');
        exit();
    } else {
        header('Location: admin_bookings.php?error=Failed to delete booking');
        exit();
    }
}

// Handle status change
if (isset($_GET['update_status'])) {
    $booking_id = $_GET['update_status'];
    $new_status = $_GET['status'];
    
    if ($database->updateBookingStatus($booking_id, $new_status)) {
        header('Location: admin_bookings.php?success=Booking status updated');
        exit();
    } else {
        header('Location: admin_bookings.php?error=Failed to update booking status');
        exit();
    }
}

// Get all bookings
$bookings = $database->getAllBookingsWithDetails();
$bookingStats = $database->getBookingStatistics();

// Search functionality
if ($search_term) {
    $bookings = $database->searchBookings($search_term);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - CinemaKrish Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        <?php include 'admin_styles.css'; ?>
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
            padding: 15px;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-confirmed {
            background-color: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }
        
        .status-pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }
        
        .status-cancelled {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .btn-action {
            padding: 5px 12px;
            margin: 0 3px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        
        .search-box {
            max-width: 400px;
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
            <h4>Booking Management</h4>
            <div class="d-flex align-items-center">
                <form method="GET" class="d-flex search-box me-3">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search bookings..." name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button class="btn btn-admin" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <button class="btn btn-admin me-2" onclick="exportBookings()">
                    <i class="fas fa-download me-2"></i> Export
                </button>
            </div>
        </div>

        <?php if ($search_term): ?>
            <div class="alert alert-info mb-4">
                Search results for: <strong><?php echo htmlspecialchars($search_term); ?></strong>
                <a href="admin_bookings.php" class="float-end">Clear Search</a>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Theatre</th>
                            <th>Show Date & Time</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Booked On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['ticket_number']); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['customer_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                                <td><?php echo htmlspecialchars($booking['theatre_name']); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($booking['show_date'])); ?><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($booking['show_time'])); ?></small>
                                </td>
                                <td><?php echo $booking['total_seats']; ?> seats<br>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['seat_numbers']); ?></small>
                                </td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <select class="form-select form-select-sm status-select" 
                                            data-booking-id="<?php echo $booking['id']; ?>"
                                            style="width: 120px;">
                                        <option value="pending" <?php echo $booking['booking_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $booking['booking_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $booking['booking_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <?php if ($booking['payment_status'] === 'completed'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?><br>
                                    <small class="text-muted"><?php echo $booking['payment_method']; ?></small>
                                </td>
                                <td><?php echo date('M d, Y h:i A', strtotime($booking['booking_date'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-action btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewBookingModal"
                                            onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                        <!-- <i class="fas fa-eye"></i> -->
                                    </button>
                                    <a href="admin_bookings.php?action=delete&id=<?php echo $booking['id']; ?>" 
                                       class="btn btn-sm btn-action btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this booking?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Booking Statistics -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Bookings</h6>
                    <h3 class="mb-0"><?php echo $bookingStats['total_bookings']; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Confirmed</h6>
                    <h3 class="mb-0"><?php echo $bookingStats['confirmed_bookings']; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Pending</h6>
                    <h3 class="mb-0"><?php echo $bookingStats['pending_bookings']; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Revenue</h6>
                    <h3 class="mb-0">$<?php echo number_format($bookingStats['total_revenue'], 2); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- View Booking Modal -->
    <div class="modal fade" id="viewBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingDetails">
                    <!-- Details will be loaded here via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-admin" onclick="printBooking()">
                        <i class="fas fa-print me-2"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function viewBooking(bookingId) {
            fetch('ajax_get_booking.php?id=' + bookingId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('bookingDetails').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }

        function printBooking() {
            const printContent = document.getElementById('bookingDetails').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }

        function exportBookings() {
            window.location.href = 'export_bookings.php';
        }

        // Handle status change
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const bookingId = this.dataset.bookingId;
                const newStatus = this.value;
                
                if (confirm('Are you sure you want to update the booking status?')) {
                    window.location.href = `admin_bookings.php?update_status=${bookingId}&status=${newStatus}`;
                }
            });
        });

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