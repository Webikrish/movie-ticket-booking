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

// Add Show
if (isset($_POST['add_show'])) {
    $movie_id = $_POST['movie_id'];
    $theatre_id = $_POST['theatre_id'];
    $screen_number = $_POST['screen_number'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];
    $ticket_price = $_POST['ticket_price'];
    $total_seats = $_POST['total_seats'];
    $available_seats = $total_seats; // Initially all seats are available
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Check for conflicting shows
    $conflict = $database->checkShowConflict($theatre_id, $screen_number, $show_date, $show_time);
    if ($conflict) {
        header('Location: admin_shows.php?error=There is already a show at this time on the same screen');
        exit();
    }

    if ($database->addShow($movie_id, $theatre_id, $screen_number, $show_date, $show_time, 
                          $ticket_price, $total_seats, $available_seats, $is_active)) {
        header('Location: admin_shows.php?success=Show added successfully');
        exit();
    } else {
        header('Location: admin_shows.php?error=Failed to add show');
        exit();
    }
}

// Edit Show
if (isset($_POST['edit_show'])) {
    $show_id = $_POST['show_id'];
    $movie_id = $_POST['movie_id'];
    $theatre_id = $_POST['theatre_id'];
    $screen_number = $_POST['screen_number'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];
    $ticket_price = $_POST['ticket_price'];
    $total_seats = $_POST['total_seats'];
    $available_seats = $_POST['available_seats'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Check for conflicting shows (excluding current show)
    $conflict = $database->checkShowConflict($theatre_id, $screen_number, $show_date, $show_time, $show_id);
    if ($conflict) {
        header('Location: admin_shows.php?error=There is already a show at this time on the same screen');
        exit();
    }

    if ($database->updateShow($show_id, $movie_id, $theatre_id, $screen_number, 
                             $show_date, $show_time, $ticket_price, 
                             $total_seats, $available_seats, $is_active)) {
        header('Location: admin_shows.php?success=Show updated successfully');
        exit();
    } else {
        header('Location: admin_shows.php?error=Failed to update show');
        exit();
    }
}
?>