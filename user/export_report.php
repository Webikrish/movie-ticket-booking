<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Get export parameters
$export_type = $_GET['type'] ?? 'overview';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$format = $_GET['format'] ?? 'csv';

// Initialize database
$database = new Database();
$db = $database->getConnection();

// Generate filename
$filename = "cinemakrish_report_{$export_type}_" . date('Y-m-d_H-i-s') . ".csv";

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Output UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Export data based on type
switch ($export_type) {
    case 'revenue':
        exportRevenueData($database, $start_date, $end_date, $output);
        break;
    case 'bookings':
        exportBookingsData($database, $start_date, $end_date, $output);
        break;
    case 'movies':
        exportMoviesData($database, $start_date, $end_date, $output);
        break;
    case 'overview':
    default:
        exportOverviewData($database, $start_date, $end_date, $output);
        break;
}

fclose($output);
exit;

// Export functions
function exportRevenueData($database, $start_date, $end_date, $output) {
    $daily_revenue = $database->getDailyRevenue($start_date, $end_date);
    
    // Header
    fputcsv($output, ['CinemaKrish - Revenue Report']);
    fputcsv($output, ['Period:', $start_date . ' to ' . $end_date]);
    fputcsv($output, ['Generated:', date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Date', 'Bookings', 'Revenue (₹)', 'Avg. Revenue/Booking (₹)', 'Trend %']);
    
    // Data
    foreach ($daily_revenue as $day) {
        fputcsv($output, [
            date('Y-m-d', strtotime($day['date'])),
            $day['bookings'],
            number_format($day['revenue'], 2),
            number_format($day['avg_booking'], 2),
            number_format($day['change'] ?? 0, 2)
        ]);
    }
    
    // Summary
    fputcsv($output, []);
    fputcsv($output, ['Summary:']);
    $revenue_stats = $database->getRevenueStatistics($start_date, $end_date);
    fputcsv($output, ['Total Revenue:', '₹' . number_format($revenue_stats['total_revenue'], 2)]);
    fputcsv($output, ['Average Daily:', '₹' . number_format($revenue_stats['avg_daily'], 2)]);
    fputcsv($output, ['Highest Day:', '₹' . number_format($revenue_stats['max_daily'], 2)]);
    fputcsv($output, ['Lowest Day:', '₹' . number_format($revenue_stats['min_daily'], 2)]);
}

function exportBookingsData($database, $start_date, $end_date, $output) {
    $booking_stats = $database->getBookingStatisticsByDate($start_date, $end_date);
    
    // Header
    fputcsv($output, ['CinemaKrish - Bookings Report']);
    fputcsv($output, ['Period:', $start_date . ' to ' . $end_date]);
    fputcsv($output, ['Generated:', date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    fputcsv($output, ['Booking Statistics:']);
    fputcsv($output, ['Total Bookings:', $booking_stats['total_bookings']]);
    fputcsv($output, ['Confirmed:', $booking_stats['confirmed_bookings']]);
    fputcsv($output, ['Pending:', $booking_stats['pending_bookings']]);
    fputcsv($output, ['Cancelled:', $booking_stats['cancelled_bookings']]);
    fputcsv($output, ['Avg Seats/Booking:', number_format($booking_stats['avg_seats'], 1)]);
    fputcsv($output, []);
    
    // Booking details
    fputcsv($output, ['Recent Bookings:']);
    fputcsv($output, ['Ticket #', 'Customer', 'Movie', 'Theatre', 'Show Date', 'Seats', 'Amount (₹)', 'Status']);
    
    foreach ($booking_stats['recent_bookings'] as $booking) {
        fputcsv($output, [
            $booking['ticket_number'] ?? 'N/A',
            $booking['customer_name'] ?? 'N/A',
            $booking['movie_title'] ?? 'N/A',
            $booking['theatre_name'] ?? 'N/A',
            date('Y-m-d', strtotime($booking['show_date'] ?? 'now')),
            $booking['seat_numbers'] ?? 'N/A',
            number_format($booking['total_amount'] ?? 0, 2),
            $booking['booking_status'] ?? 'N/A'
        ]);
    }
}

function exportMoviesData($database, $start_date, $end_date, $output) {
    $movie_stats = $database->getMovieStatisticsReport($start_date, $end_date);
    
    // Header
    fputcsv($output, ['CinemaKrish - Movies Report']);
    fputcsv($output, ['Period:', $start_date . ' to ' . $end_date]);
    fputcsv($output, ['Generated:', date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    // Summary
    fputcsv($output, ['Movie Statistics:']);
    fputcsv($output, ['Total Movies:', $movie_stats['total_movies']]);
    fputcsv($output, ['Now Showing:', $movie_stats['now_showing']]);
    fputcsv($output, ['Coming Soon:', $movie_stats['coming_soon']]);
    fputcsv($output, ['Featured:', $movie_stats['featured']]);
    fputcsv($output, ['Average Rating:', number_format($movie_stats['avg_rating'], 1)]);
    fputcsv($output, []);
    
    // Top movies
    fputcsv($output, ['Top 5 Movies by Revenue:']);
    fputcsv($output, ['Movie', 'Bookings', 'Revenue (₹)', 'Avg. Ticket (₹)']);
    
    foreach ($movie_stats['top_movies'] as $movie) {
        fputcsv($output, [
            $movie['title'],
            $movie['bookings'],
            number_format($movie['revenue'], 2),
            number_format($movie['avg_ticket'], 2)
        ]);
    }
    
    fputcsv($output, []);
    
    // All movies
    fputcsv($output, ['All Movies Performance:']);
    fputcsv($output, ['Movie', 'Language', 'Genre', 'Rating', 'Bookings', 'Revenue (₹)', 'Occupancy %', 'Status']);
    
    foreach ($movie_stats['all_movies'] as $movie) {
        fputcsv($output, [
            $movie['title'],
            $movie['language_name'] ?? 'N/A',
            $movie['genre_name'] ?? 'N/A',
            number_format($movie['rating'], 1),
            $movie['bookings'],
            number_format($movie['revenue'], 2),
            number_format($movie['occupancy'], 1),
            $movie['is_now_showing'] ? 'Now Showing' : 'Coming Soon'
        ]);
    }
}

function exportOverviewData($database, $start_date, $end_date, $output) {
    // Get all data for overview
    $stats = $database->getSystemStatistics();
    $revenue_stats = $database->getRevenueStatistics($start_date, $end_date);
    $booking_stats = $database->getBookingStatisticsByDate($start_date, $end_date);
    $movie_stats = $database->getMovieStatisticsReport($start_date, $end_date);
    $theatre_stats = $database->getTheatreStatisticsReport($start_date, $end_date);
    
    // Header
    fputcsv($output, ['CinemaKrish - Overview Report']);
    fputcsv($output, ['Period:', $start_date . ' to ' . $end_date]);
    fputcsv($output, ['Generated:', date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    // System Statistics
    fputcsv($output, ['System Statistics:']);
    fputcsv($output, ['Total Users:', $stats['total_users']]);
    fputcsv($output, ['New Users (This Month):', $stats['new_users']]);
    fputcsv($output, ['Active Movies:', $stats['active_movies']]);
    fputcsv($output, ['Total Movies:', $stats['total_movies']]);
    fputcsv($output, ['Total Bookings:', $stats['total_bookings']]);
    fputcsv($output, ['Today\'s Bookings:', $stats['today_bookings']]);
    fputcsv($output, ['Total Revenue:', '₹' . number_format($stats['total_revenue'], 2)]);
    fputcsv($output, ['Monthly Revenue:', '₹' . number_format($stats['monthly_revenue'], 2)]);
    fputcsv($output, []);
    
    // Revenue Statistics
    fputcsv($output, ['Revenue Statistics:']);
    fputcsv($output, ['Total Revenue (Period):', '₹' . number_format($revenue_stats['total_revenue'], 2)]);
    fputcsv($output, ['Average Daily:', '₹' . number_format($revenue_stats['avg_daily'], 2)]);
    fputcsv($output, ['Highest Day:', '₹' . number_format($revenue_stats['max_daily'], 2)]);
    fputcsv($output, ['Lowest Day:', '₹' . number_format($revenue_stats['min_daily'], 2)]);
    fputcsv($output, []);
    
    // Payment Methods
    fputcsv($output, ['Revenue by Payment Method:']);
    fputcsv($output, ['Cash:', '₹' . number_format($revenue_stats['payment_methods']['cash'], 2)]);
    fputcsv($output, ['Credit Card:', '₹' . number_format($revenue_stats['payment_methods']['credit_card'], 2)]);
    fputcsv($output, ['Debit Card:', '₹' . number_format($revenue_stats['payment_methods']['debit_card'], 2)]);
    fputcsv($output, ['PayPal:', '₹' . number_format($revenue_stats['payment_methods']['paypal'], 2)]);
    fputcsv($output, []);
    
    // Booking Statistics
    fputcsv($output, ['Booking Statistics:']);
    fputcsv($output, ['Total Bookings:', $booking_stats['total_bookings']]);
    fputcsv($output, ['Confirmed:', $booking_stats['confirmed_bookings']]);
    fputcsv($output, ['Pending:', $booking_stats['pending_bookings']]);
    fputcsv($output, ['Cancelled:', $booking_stats['cancelled_bookings']]);
    fputcsv($output, ['Avg Seats/Booking:', number_format($booking_stats['avg_seats'], 1)]);
    fputcsv($output, []);
    
    // Top Movies
    fputcsv($output, ['Top 5 Movies by Revenue:']);
    fputcsv($output, ['Rank', 'Movie', 'Bookings', 'Revenue (₹)', 'Avg. Ticket (₹)']);
    
    $rank = 1;
    foreach ($movie_stats['top_movies'] as $movie) {
        fputcsv($output, [
            $rank++,
            $movie['title'],
            $movie['bookings'],
            number_format($movie['revenue'], 2),
            number_format($movie['avg_ticket'], 2)
        ]);
    }
    fputcsv($output, []);
    
    // Top Theatres
    fputcsv($output, ['Top Theatres:']);
    fputcsv($output, ['Theatre', 'City', 'Bookings', 'Revenue (₹)']);
    
    foreach ($theatre_stats['top_theatres'] as $theatre) {
        fputcsv($output, [
            $theatre['name'],
            $theatre['city'],
            $theatre['bookings'],
            number_format($theatre['revenue'], 2)
        ]);
    }
}