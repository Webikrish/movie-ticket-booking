<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is admin
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] != 1
) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['theatre_id'])) {
    $theatre = $database->getTheatreById($_GET['theatre_id']);
    if ($theatre) {
        header('Content-Type: application/json');
        echo json_encode(['total_screens' => $theatre['total_screens']]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Theatre not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No theatre ID provided']);
}
?>