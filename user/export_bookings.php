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

// Get all bookings
$bookings = $database->getAllBookingsWithDetails();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=bookings_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Ticket Number',
    'Customer Name',
    'Customer Email',
    'Movie Title',
    'Theatre Name',
    'Show Date',
    'Show Time',
    'Total Seats',
    'Seat Numbers',
    'Total Amount',
    'Booking Status',
    'Payment Status',
    'Payment Method',
    'Booking Date'
]);

// Add booking data
foreach ($bookings as $booking) {
    fputcsv($output, [
        $booking['ticket_number'],
        $booking['customer_name'],
        $booking['customer_email'],
        $booking['movie_title'],
        $booking['theatre_name'],
        $booking['show_date'],
        $booking['show_time'],
        $booking['total_seats'],
        $booking['seat_numbers'],
        $booking['total_amount'],
        $booking['booking_status'],
        $booking['payment_status'],
        $booking['payment_method'],
        $booking['booking_date']
    ]);
}

fclose($output);
exit();
?>