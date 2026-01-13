<?php
require_once 'db_connection.php';
$database = new Database();
$database->getConnection();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'No theatre ID provided']);
    exit;
}

$theatre = $database->getTheatreById($id); // Fetch single theatre
if (!$theatre) {
    echo json_encode(['error' => 'Theatre not found']);
    exit;
}

echo json_encode($theatre);


if (isset($_GET['id'])) {
    $theatre = $database->getTheatreById($_GET['id']);
    if ($theatre) {
        header('Content-Type: application/json');
        echo json_encode($theatre);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Theatre not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No ID provided']);
}
?>