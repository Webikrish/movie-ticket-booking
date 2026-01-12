<?php
// save_notification.php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required parameters
if (!isset($_POST['movie_id']) || !isset($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$movie_id = intval($_POST['movie_id']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if notification already exists
    $checkQuery = "SELECT id FROM notifications WHERE movie_id = :movie_id AND email = :email";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':movie_id', $movie_id);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already subscribed for this movie']);
        exit;
    }
    
    // Insert notification
    $query = "INSERT INTO notifications (movie_id, email, created_at) VALUES (:movie_id, :email, NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':movie_id', $movie_id);
    $stmt->bindParam(':email', $email);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save notification']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}