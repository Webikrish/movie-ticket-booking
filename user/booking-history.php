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

$query = "SELECT b.*, m.title as movie_title, m.poster_url, 
          DATE_FORMAT(b.show_date, '%d %b %Y') as formatted_date,
          TIME_FORMAT(b.show_time, '%h:%i %p') as formatted_time
          FROM bookings b
          JOIN movies m ON b.movie_id = m.id
          WHERE b.user_id = :user_id
          ORDER BY b.booking_date DESC";
          
$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History - CinemaKrish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="container mt-4">
        <h1 class="mb-4">Booking History</h1>
        
        <?php if(count($bookings) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Movie</th>
                            <th>Show Date</th>
                            <th>Show Time</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['ticket_number']; ?></td>
                                <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                                <td><?php echo $booking['formatted_date']; ?></td>
                                <td><?php echo $booking['formatted_time']; ?></td>
                                <td><?php echo $booking['seat_numbers']; ?></td>
                                <td>$<?php echo $booking['total_amount']; ?></td>
                                <td>
                                    <?php if($booking['payment_status'] == 'completed'): ?>
                                        <span class="badge bg-success">Confirmed</span>
                                    <?php elseif($booking['payment_status'] == 'pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?php echo ucfirst($booking['payment_status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="view-ticket.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">View Ticket</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No bookings found.
            </div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
   
</body>
</html>