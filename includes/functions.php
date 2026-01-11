<?php
require_once 'config.php';
require_once 'database.php';

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateBookingID() {
    return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function generateQRCode($booking_id) {
    // Dummy function - integrate with QR code library
    return 'qrcodes/' . $booking_id . '.png';
}

function uploadImage($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'File is not an image.'];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'File is too large.'];
    }
    
    // Allow certain file formats
    if(!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_path = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'Error uploading file.'];
    }
}

function sendEmail($to, $subject, $message) {
    // Dummy function - implement PHPMailer
    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatTime($time) {
    return date('h:i A', strtotime($time));
}

function getCities() {
    $db = new Database();
    $db->query("SELECT DISTINCT city FROM theatres WHERE status = 'active' ORDER BY city");
    return $db->resultSet();
}

function getLanguages() {
    $db = new Database();
    $db->query("SELECT DISTINCT language FROM movies WHERE status IN ('now_showing', 'coming_soon') ORDER BY language");
    return $db->resultSet();
}

function getGenres() {
    $db = new Database();
    $db->query("SELECT DISTINCT genre FROM movies WHERE status IN ('now_showing', 'coming_soon')");
    $result = $db->resultSet();
    $genres = [];
    foreach($result as $row) {
        $genre_list = explode(',', $row->genre);
        foreach($genre_list as $genre) {
            $genres[] = trim($genre);
        }
    }
    return array_unique($genres);
}
?>