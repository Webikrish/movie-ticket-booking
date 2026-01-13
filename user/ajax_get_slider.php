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

function deleteSlider(sliderId) {
    if (confirm('Are you sure you want to delete this slider?')) {
        fetch('admin_slider.php?action=delete&id=' + sliderId)
        .then(() => {
            // Remove the slider card immediately
            const card = document.querySelector(`#slider-card-${sliderId}`);
            if(card) card.remove();
        });
    }
}


if (isset($_GET['id'])) {
    $slider = $database->getSliderById($_GET['id']);
    if ($slider) {
        header('Content-Type: application/json');
        echo json_encode($slider);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Slider not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No ID provided']);
}
?>