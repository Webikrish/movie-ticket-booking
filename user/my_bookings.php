<?php
// my_bookings.php
require_once 'session_manager.php';
require_once 'db_connection.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$currentUser = getCurrentUser();

// Get user's bookings
try {
    $query = "SELECT b.*, m.title as movie_title, m.poster_url, 
                     t.name as theatre_name, t.location,
                     CASE 
                         WHEN b.show_date < CURDATE() THEN 'watched'
                         WHEN b.show_date = CURDATE() AND b.show_time < CURTIME() THEN 'watched'
                         ELSE 'upcoming'
                     END as status
              FROM bookings b
              JOIN movies m ON b.movie_id = m.id
              JOIN theatres t ON b.theatre_id = t.id
              WHERE b.user_id = :user_id
              ORDER BY b.show_date DESC, b.show_time DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $currentUser['id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error fetching bookings: " . $e->getMessage());
    $bookings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | CinemaKrish</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-dark: #0a0a0a;
            --secondary-dark: #1a1a1a;
            --accent-red: #d32f2f;
            --accent-gold: #ffc107;
            --text-light: #f8f9fa;
            --text-gray: #adb5bd;
        }

        body {
            background: var(--primary-dark);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 80px;
        }

        .container {
            max-width: 1200px;
        }

        .booking-card {
            background: var(--secondary-dark);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            border-color: var(--accent-red);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .booking-card.upcoming {
            border-left: 5px solid #28a745;
        }

        .booking-card.watched {
            border-left: 5px solid #6c757d;
            opacity: 0.8;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-upcoming {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .status-watched {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
        }

        .seat-badge {
            background: var(--accent-red);
            color: white;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--accent-gold);
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4" style="color: var(--accent-gold);">
            <i class="fas fa-ticket-alt me-2"></i> My Bookings
        </h1>
        
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="mb-3">No Bookings Yet</h3>
                <p class="text-gray mb-4">You haven't made any bookings yet. Start by exploring our movies!</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-film me-2"></i> Browse Movies
                </a>
            </div>
        <?php else: ?>
            <!-- Filter Tabs -->
            <div class="d-flex mb-4">
                <button class="btn btn-outline-light me-2 active" onclick="filterBookings('all')">All Bookings</button>
                <button class="btn btn-outline-success me-2" onclick="filterBookings('upcoming')">Upcoming</button>
                <button class="btn btn-outline-secondary" onclick="filterBookings('watched')">Watched</button>
            </div>
            
            <!-- Bookings List -->
            <div id="bookingsList">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card <?php echo $booking['status']; ?>" data-status="<?php echo $booking['status']; ?>">
                        <div class="row">
                            <div class="col-md-2 mb-3 mb-md-0">
                                <img src="<?php echo htmlspecialchars($booking['poster_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($booking['movie_title']); ?>" 
                                     class="img-fluid rounded" style="max-height: 150px;">
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($booking['movie_title']); ?></h5>
                                        <p class="text-gray mb-1">
                                            <i class="fas fa-theater-masks me-1"></i>
                                            <?php echo htmlspecialchars($booking['theatre_name']); ?>
                                        </p>
                                    </div>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-6 col-md-3">
                                        <small class="text-gray">Date:</small>
                                        <div><?php echo date('M j, Y', strtotime($booking['show_date'])); ?></div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-gray">Time:</small>
                                        <div><?php echo date('h:i A', strtotime($booking['show_time'])); ?></div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-gray">Seats:</small>
                                        <div>
                                            <?php 
                                            $seats = explode(',', $booking['seat_numbers']);
                                            foreach ($seats as $seat): 
                                            ?>
                                                <span class="seat-badge"><?php echo $seat; ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <small class="text-gray">Amount:</small>
                                        <div style="color: var(--accent-gold); font-weight: 600;">
                                            $<?php echo number_format($booking['total_amount'], 2); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-dark">
                                        <i class="fas fa-ticket me-1"></i>
                                        <?php echo $booking['ticket_number']; ?>
                                    </span>
                                    <span class="badge bg-info">
                                        <i class="fas fa-credit-card me-1"></i>
                                        <?php echo ucfirst($booking['payment_method']); ?>
                                    </span>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-2 text-md-end">
                                <a href="booking_confirmation.php?id=<?php echo $booking['id']; ?>" 
                                   class="btn btn-sm btn-outline-light">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Filter bookings
        function filterBookings(status) {
            const bookingCards = document.querySelectorAll('.booking-card');
            const filterButtons = document.querySelectorAll('.btn-outline-light, .btn-outline-success, .btn-outline-secondary');
            
            // Update active button
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase().includes(status)) {
                    btn.classList.add('active');
                }
            });
            
            // Show/hide bookings
            bookingCards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers to filter buttons
            document.querySelectorAll('.btn-outline-light, .btn-outline-success, .btn-outline-secondary').forEach(btn => {
                btn.addEventListener('click', function() {
                    const status = this.textContent.toLowerCase();
                    filterBookings(status === 'all bookings' ? 'all' : status);
                });
            });
        });
    </script>
</body>
</html>