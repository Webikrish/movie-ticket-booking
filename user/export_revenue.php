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

$type = $_GET['type'] ?? 'monthly';

switch ($type) {
    case 'weekly':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        $filename = 'revenue_weekly_' . date('Y-m-d') . '.csv';
        break;
    case 'monthly':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        $filename = 'revenue_monthly_' . date('Y-m') . '.csv';
        break;
    case 'yearly':
        $start_date = date('Y-01-01');
        $end_date = date('Y-m-d');
        $filename = 'revenue_yearly_' . date('Y') . '.csv';
        break;
    default:
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $filename = 'revenue_custom_' . date('Y-m-d') . '.csv';
}

// Get revenue data
$revenue_data = $database->getDetailedRevenueReport($start_date, $end_date);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Date',
    'Total Bookings',
    'Confirmed Bookings',
    'Pending Bookings',
    'Cancelled Bookings',
    'Total Revenue',
    'Confirmed Revenue',
    'Pending Revenue',
    'Cancelled Revenue',
    'Average Ticket Price',
    'Total Seats Sold'
]);

// Add revenue data
foreach ($revenue_data as $data) {
    fputcsv($output, [
        $data['date'],
        $data['total_bookings'],
        $data['confirmed_bookings'],
        $data['pending_bookings'],
        $data['cancelled_bookings'],
        $data['total_revenue'],
        $data['confirmed_revenue'],
        $data['pending_revenue'],
        $data['cancelled_revenue'],
        $data['avg_ticket_price'],
        $data['total_seats']
    ]);
}

fclose($output);
exit();
?>