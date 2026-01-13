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

// Add Movie
if (isset($_POST['add_movie'])) {
    $title = $_POST['title'];
    $language_id = $_POST['language_id'];
    $genre_id = $_POST['genre_id'];
    $rating = $_POST['rating'] ?? 0;
    $duration = $_POST['duration'] ?? '';
    $ticket_price = $_POST['ticket_price'] ?? 0;
    $release_date = $_POST['release_date'] ?? null;
    $poster_url = $_POST['poster_url'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_now_showing = isset($_POST['is_now_showing']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if ($database->addMovie($title, $language_id, $genre_id, $rating, $duration, 
                            $ticket_price, $release_date, $poster_url, $description,
                            $is_now_showing, $is_featured)) {
        header('Location: admin_movies.php?success=Movie added successfully');
        exit();
    } else {
        header('Location: admin_movies.php?error=Failed to add movie');
        exit();
    }
}

// Edit Movie
if (isset($_POST['edit_movie'])) {
    $movie_id = $_POST['movie_id'];
    $title = $_POST['title'];
    $language_id = $_POST['language_id'];
    $genre_id = $_POST['genre_id'];
    $rating = $_POST['rating'] ?? 0;
    $duration = $_POST['duration'] ?? '';
    $ticket_price = $_POST['ticket_price'] ?? 0;
    $release_date = $_POST['release_date'] ?? null;
    $poster_url = $_POST['poster_url'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_now_showing = isset($_POST['is_now_showing']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if ($database->updateMovie($movie_id, $title, $language_id, $genre_id, $rating, 
                              $duration, $ticket_price, $release_date, $poster_url, 
                              $description, $is_now_showing, $is_featured)) {
        header('Location: admin_movies.php?success=Movie updated successfully');
        exit();
    } else {
        header('Location: admin_movies.php?error=Failed to update movie');
        exit();
    }
}
?>