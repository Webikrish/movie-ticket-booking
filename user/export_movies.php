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

// Get movies with statistics
$movies = $database->getAllMoviesWithStatistics();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=movies_report_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Movie ID',
    'Title',
    'Language',
    'Genre',
    'Rating',
    'Duration',
    'Ticket Price',
    'Release Date',
    'Status',
    'Total Bookings',
    'Total Revenue',
    'Total Seats Sold',
    'Last Show Date'
]);

// Add movie data
foreach ($movies as $movie) {
    fputcsv($output, [
        $movie['id'],
        $movie['title'],
        $movie['language_name'],
        $movie['genre_name'],
        $movie['rating'],
        $movie['duration'],
        $movie['ticket_price'],
        $movie['release_date'],
        $movie['is_now_showing'] ? 'Now Showing' : 'Coming Soon',
        $movie['total_bookings'] ?? 0,
        $movie['total_revenue'] ?? 0,
        $movie['total_seats'] ?? 0,
        $movie['last_show_date'] ?? 'N/A'
    ]);
}

fclose($output);
exit();
?>