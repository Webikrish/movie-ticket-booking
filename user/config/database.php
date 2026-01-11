<?php
// config/database.php
$host = "127.0.0.1";
$username = "root"; // Change as per your configuration
$password = ""; // Change as per your configuration
$database = "cinemakrish_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>