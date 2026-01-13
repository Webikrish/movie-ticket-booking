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

// Update Booking Status
if (isset($_POST['update_booking_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    if ($database->updateBookingStatus($booking_id, $status)) {
        // If cancelling, release the seats
        if ($status === 'cancelled') {
            $booking = $database->getBookingById($booking_id);
            if ($booking) {
                // Increase available seats
                $database->increaseAvailableSeats($booking['show_id'], $booking['total_seats']);
            }
        }
        header('Location: admin_bookings.php?success=Booking status updated successfully');
        exit();
    } else {
        header('Location: admin_bookings.php?error=Failed to update booking status');
        exit();
    }
}

// Refund Booking
if (isset($_POST['refund_booking'])) {
    $booking_id = $_POST['booking_id'];
    
    // Update booking status to refunded
    if ($database->updateBookingStatus($booking_id, 'refunded')) {
        // Release seats
        $booking = $database->getBookingById($booking_id);
        if ($booking) {
            $database->increaseAvailableSeats($booking['show_id'], $booking['total_seats']);
        }
        
        // Update payment status
        $database->updatePaymentStatus($booking_id, 'refunded');
        
        header('Location: admin_bookings.php?success=Booking refunded successfully');
        exit();
    } else {
        header('Location: admin_bookings.php?error=Failed to refund booking');
        exit();
    }
}
?>