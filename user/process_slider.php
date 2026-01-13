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




if (isset($_POST['add_slider'])) {
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $image_url = $_POST['image_url'];
    $display_order = $_POST['display_order'] ?? 1;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($database->addSlider($title, $description, $image_url, $display_order, $is_active)) {
        header('Location: admin_slider.php?success=Slider added successfully');
        exit();
    } else {
        header('Location: admin_slider.php?error=Failed to add slider');
        exit();
    }
}



// Add Slider
if (isset($_POST['add_slider'])) {
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $image_url = $_POST['image_url'];
    $display_order = $_POST['display_order'] ?? 1;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($database->addSlider($title, $description, $image_url, $display_order, $is_active)) {
        header('Location: admin_slider.php?success=Slider added successfully');
        exit();
    } else {
        header('Location: admin_slider.php?error=Failed to add slider');
        exit();
    }
}

// Edit Slider
if (isset($_POST['edit_slider'])) {
    $slider_id = $_POST['slider_id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $image_url = $_POST['image_url'];
    $display_order = $_POST['display_order'] ?? 1;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($database->updateSlider($slider_id, $title, $description, $image_url, $display_order, $is_active)) {
        header('Location: admin_slider.php?success=Slider updated successfully');
        exit();
    } else {
        header('Location: admin_slider.php?error=Failed to update slider');
        exit();
    }
}
?>