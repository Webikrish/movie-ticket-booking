<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'movie_booking');
define('BASE_URL', 'http://localhost/movie-ticket-booking/');
define('SITE_NAME', 'CineBook - Movie Ticket Booking');

// File upload paths
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/movie-ticket-booking/assets/uploads/');
define('POSTER_PATH', 'assets/uploads/movies/');
define('LOGO_PATH', 'assets/uploads/logos/');

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');

// Payment Gateway (Dummy)
define('PAYMENT_MERCHANT_ID', 'TEST_MERCHANT_ID');
define('PAYMENT_SALT_KEY', 'TEST_SALT_KEY');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>