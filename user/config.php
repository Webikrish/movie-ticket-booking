<?php
// config.php - Application configuration

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cinemakrish_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'CinemaKrish');
define('SITE_URL', 'http://localhost/cinemakrish/');
define('ADMIN_EMAIL', 'admin@cinemakrish.com');

// Timezone
date_default_timezone_set('America/New_York');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 0 in production

// Paths
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('ASSETS_PATH', SITE_URL . 'assets/');
?>