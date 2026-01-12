<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$booking_id = $_GET['id'];

$query = "SELECT b.*, m.title as movie_title, m.duration, m.rating, 
          DATE_FORMAT(b.show_date, '%d %b %Y') as formatted_date,
          TIME_FORMAT(b.show_time, '%h:%i %p') as formatted_time
          FROM bookings b
          JOIN movies m ON b.movie_id = m.id
          WHERE b.id = :booking_id AND b.user_id = :user_id";
          
$stmt = $db->prepare($query);
$stmt->execute([':booking_id' => $booking_id, ':user_id' => $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket - CinemaKrish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ticket {
            border: 2px solid #000;
            border-radius: 10px;
            padding: 2rem;
            max-width: 600px;
            margin: 2rem auto;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .ticket-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .ticket-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .qr-code {
            text-align: center;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="container mt-4">
        <div class="ticket">
            <div class="ticket-header">
                <h2>CinemaKrish</h2>
                <h4>MOVIE TICKET</h4>
                <p class="mb-0">Ticket #: <?php echo $booking['ticket_number']; ?></p>
            </div>
            
            <div class="ticket-body">
                <div>
                    <h4><?php echo htmlspecialchars($booking['movie_title']); ?></h4>
                    <p><strong>Date:</strong> <?php echo $booking['formatted_date']; ?></p>
                    <p><strong>Time:</strong> <?php echo $booking['formatted_time']; ?></p>
                    <p><strong>Seats:</strong> <?php echo $booking['seat_numbers']; ?></p>
                    <p><strong>Total Seats:</strong> <?php echo $booking['total_seats']; ?></p>
                    <p><strong>Amount:</strong> $<?php echo $booking['total_amount']; ?></p>
                    <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge <?php echo $booking['payment_status'] == 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                            <?php echo ucfirst($booking['payment_status']); ?>
                        </span>
                    </p>
                </div>
                
                <div class="qr-code">
                    <?php if($booking['qr_code']): ?>
                        <img src="<?php echo $booking['qr_code']; ?>" alt="QR Code" class="img-fluid">
                    <?php else: ?>
                        <div class="text-muted">QR Code not available</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="ticket-footer mt-3 pt-3 border-top">
                <p class="text-center mb-0">
                    <strong>Note:</strong> Please arrive at least 30 minutes before the show. 
                    Present this ticket at the counter for entry.
                </p>
            </div>
        </div>
        
        <div class="text-center mt-3 no-print">
            <button onclick="window.print()" class="btn btn-primary me-2">
                <i class="fas fa-print"></i> Print Ticket
            </button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
</body>
</html>