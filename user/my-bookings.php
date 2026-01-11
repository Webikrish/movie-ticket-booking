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

// Get filter parameters
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Pagination
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT b.*, m.title as movie_title, m.poster_image, 
                 t.name as theatre_name, s.show_date, s.show_time
          FROM bookings b
          JOIN shows s ON b.show_id = s.id
          JOIN movies m ON s.movie_id = m.id
          JOIN theatres t ON s.theatre_id = t.id
          WHERE b.user_id = ?";

$params = [$user_id];

if(!empty($status)) {
    $query .= " AND b.booking_status = ?";
    $params[] = $status;
}

if(!empty($date_from)) {
    $query .= " AND DATE(b.booking_date) >= ?";
    $params[] = $date_from;
}

if(!empty($date_to)) {
    $query .= " AND DATE(b.booking_date) <= ?";
    $params[] = $date_to;
}

// Count total records
$countQuery = str_replace('SELECT b.*, m.title as movie_title, m.poster_image, 
                 t.name as theatre_name, s.show_date, s.show_time', 
                 'SELECT COUNT(*) as total', $query);

$db->query($countQuery);
for($i = 0; $i < count($params); $i++) {
    $db->bind($i + 1, $params[$i]);
}
$totalResult = $db->single();
$totalBookings = $totalResult->total;
$totalPages = ceil($totalBookings / $limit);

// Get bookings with pagination
$query .= " ORDER BY b.booking_date DESC LIMIT ? OFFSET ?";
$db->query($query);

for($i = 0; $i < count($params); $i++) {
    $db->bind($i + 1, $params[$i]);
}
$db->bind(count($params) + 1, $limit, PDO::PARAM_INT);
$db->bind(count($params) + 2, $offset, PDO::PARAM_INT);

$bookings = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .booking-card {
            border-left: 4px solid var(--primary-color);
            border-radius: 8px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .booking-card.cancelled {
            border-left-color: #dc3545;
            opacity: 0.8;
        }
        
        .booking-card.completed {
            border-left-color: #28a745;
        }
        
        .booking-card.pending {
            border-left-color: #ffc107;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .filter-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .booking-card .row > div {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3">
                <!-- Filter Sidebar -->
                <div class="filter-card">
                    <h5 class="mb-4">Filter Bookings</h5>
                    
                    <form method="GET" id="filterForm">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" 
                                   value="<?php echo $date_from; ?>" 
                                   onchange="this.form.submit()">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" 
                                   value="<?php echo $date_to; ?>" 
                                   onchange="this.form.submit()">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="my-bookings.php" class="btn btn-outline-secondary">
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Stats -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">Booking Stats</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <small>Total Bookings:</small>
                                <span class="float-end fw-bold"><?php echo $totalBookings; ?></span>
                            </li>
                            <li class="mb-2">
                                <small>Confirmed:</small>
                                <span class="float-end">
                                    <?php
                                    $db->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND booking_status = 'confirmed'");
                                    $db->bind(1, $user_id);
                                    echo $db->single()->count;
                                    ?>
                                </span>
                            </li>
                            <li class="mb-2">
                                <small>Cancelled:</small>
                                <span class="float-end">
                                    <?php
                                    $db->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND booking_status = 'cancelled'");
                                    $db->bind(1, $user_id);
                                    echo $db->single()->count;
                                    ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Bookings List -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">My Bookings</h2>
                    <a href="movies.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Book New Tickets
                    </a>
                </div>
                
                <?php if($totalBookings == 0): ?>
                <div class="empty-state">
                    <i class="bi bi-ticket"></i>
                    <h4 class="mt-3">No bookings found</h4>
                    <p class="text-muted mb-4">
                        <?php if($status || $date_from || $date_to): ?>
                        Try changing your filters
                        <?php else: ?>
                        Book your first movie ticket and enjoy the show!
                        <?php endif; ?>
                    </p>
                    <a href="movies.php" class="btn btn-primary">Browse Movies</a>
                </div>
                <?php else: ?>
                <div class="booking-list">
                    <?php foreach($bookings as $booking): ?>
                    <div class="booking-card card <?php echo $booking->booking_status; ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-lg-2 col-md-3 text-center">
                                    <img src="../assets/uploads/movies/<?php echo $booking->poster_image; ?>" 
                                         alt="<?php echo $booking->movie_title; ?>" 
                                         class="img-fluid rounded" 
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                
                                <div class="col-lg-5 col-md-4">
                                    <h6 class="mb-1"><?php echo $booking->movie_title; ?></h6>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-building"></i> <?php echo $booking->theatre_name; ?>
                                    </p>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo formatDate($booking->show_date); ?> - 
                                        <?php echo formatTime($booking->show_time); ?>
                                    </p>
                                    <p class="mb-0 text-muted">
                                        <i class="bi bi-ticket-perforated"></i> 
                                        Seats: <?php echo $booking->seat_numbers; ?>
                                    </p>
                                </div>
                                
                                <div class="col-lg-3 col-md-3">
                                    <div class="mb-2">
                                        <span class="badge bg-primary">Booking ID: <?php echo $booking->booking_id; ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="status-badge bg-<?php 
                                            echo $booking->booking_status == 'confirmed' ? 'success' : 
                                                  ($booking->booking_status == 'cancelled' ? 'danger' : 
                                                  ($booking->booking_status == 'completed' ? 'info' : 'warning'));
                                        ?>">
                                            <?php echo ucfirst($booking->booking_status); ?>
                                        </span>
                                    </div>
                                    <div class="h6 mb-0">₹<?php echo number_format($booking->total_amount, 2); ?></div>
                                </div>
                                
                                <div class="col-lg-2 col-md-2 text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="view-booking.php?id=<?php echo $booking->id; ?>">
                                                    <i class="bi bi-eye"></i> View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="printTicket('<?php echo $booking->booking_id; ?>')">
                                                    <i class="bi bi-printer"></i> Print Ticket
                                                </a>
                                            </li>
                                            <?php if($booking->booking_status == 'confirmed' && strtotime($booking->show_date) > time()): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" 
                                                   onclick="cancelBooking('<?php echo $booking->id; ?>')">
                                                    <i class="bi bi-x-circle"></i> Cancel Booking
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php 
                            $params = $_GET;
                            $params['page'] = $page - 1;
                            echo http_build_query($params);
                            ?>">Previous</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php 
                                $params = $_GET;
                                $params['page'] = $i;
                                echo http_build_query($params);
                                ?>"><?php echo $i; ?></a>
                            </li>
                            <?php elseif($i == $page - 3 || $i == $page + 3): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php 
                            $params = $_GET;
                            $params['page'] = $page + 1;
                            echo http_build_query($params);
                            ?>">Next</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Cancel Booking Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking?</p>
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason (optional)</label>
                        <textarea class="form-control" id="cancellationReason" rows="3" 
                                  placeholder="Please provide a reason for cancellation"></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Cancellation charges may apply as per our cancellation policy.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmCancel">Cancel Booking</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let bookingIdToCancel = null;
        
        function cancelBooking(id) {
            bookingIdToCancel = id;
            const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
            modal.show();
        }
        
        document.getElementById('confirmCancel').addEventListener('click', function() {
            if(!bookingIdToCancel) return;
            
            const reason = document.getElementById('cancellationReason').value;
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Cancelling...';
            btn.disabled = true;
            
            fetch('../api/cancel-booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: bookingIdToCancel,
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Booking cancelled successfully!');
                    location.reload();
                } else {
                    alert('Failed to cancel booking: ' + data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
        
        function printTicket(bookingId) {
            // Open print view in new window
            const printWindow = window.open(`print-ticket.php?booking_id=${bookingId}`, '_blank');
            printWindow.focus();
            
            // After a delay, trigger print
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }
        
        // Auto-refresh bookings every minute
        setInterval(() => {
            const currentPage = <?php echo $page; ?>;
            const currentStatus = '<?php echo $status; ?>';
            
            fetch(`../api/check-booking-updates.php?page=${currentPage}&status=${currentStatus}`)
                .then(response => response.json())
                .then(data => {
                    if(data.updated) {
                        // Show update notification
                        const notification = document.createElement('div');
                        notification.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3';
                        notification.style.zIndex = '1050';
                        notification.innerHTML = `
                            <i class="bi bi-info-circle"></i> Bookings have been updated.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.body.appendChild(notification);
                        
                        // Auto-hide notification
                        setTimeout(() => {
                            bootstrap.Alert.getInstance(notification)?.close();
                        }, 5000);
                    }
                });
        }, 60000);
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>