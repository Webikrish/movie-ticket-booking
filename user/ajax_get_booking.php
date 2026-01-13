<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is admin
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] != 1
) {
    http_response_code(403);
    echo 'Unauthorized access';
    exit();
}

if (isset($_GET['id'])) {
    $booking = $database->getBookingById($_GET['id']);
    if ($booking) {
        ?>
        <div class="row">
            <div class="col-md-6">
                <h6>Booking Information</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Ticket Number:</th>
                        <td><?php echo htmlspecialchars($booking['ticket_number']); ?></td>
                    </tr>
                    <tr>
                        <th>Booking Date:</th>
                        <td><?php echo date('M d, Y h:i A', strtotime($booking['booking_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-<?php 
                                echo $booking['booking_status'] === 'confirmed' ? 'success' : 
                                ($booking['booking_status'] === 'pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($booking['booking_status']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Customer Information</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Name:</th>
                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($booking['customer_email']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <h6>Show Details</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Movie:</th>
                        <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                    </tr>
                    <tr>
                        <th>Theatre:</th>
                        <td><?php echo htmlspecialchars($booking['theatre_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Date & Time:</th>
                        <td>
                            <?php echo date('M d, Y', strtotime($booking['show_date'])); ?><br>
                            <?php echo date('h:i A', strtotime($booking['show_time'])); ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Payment Details</h6>
                <table class="table table-sm">
                    <tr>
                        <th>Total Amount:</th>
                        <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            <span class="badge bg-<?php echo $booking['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($booking['payment_status']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Method:</th>
                        <td><?php echo htmlspecialchars($booking['payment_method']); ?></td>
                    </tr>
                    <tr>
                        <th>Total Seats:</th>
                        <td><?php echo $booking['total_seats']; ?></td>
                    </tr>
                    <tr>
                        <th>Seat Numbers:</th>
                        <td><?php echo htmlspecialchars($booking['seat_numbers']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($booking['special_notes']): ?>
        <div class="row mt-3">
            <div class="col-md-12">
                <h6>Special Notes</h6>
                <div class="alert alert-info">
                    <?php echo htmlspecialchars($booking['special_notes']); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php
    } else {
        echo '<div class="alert alert-danger">Booking not found</div>';
    }
} else {
    echo '<div class="alert alert-danger">No booking ID provided</div>';
}
?>