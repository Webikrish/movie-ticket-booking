<?php
session_start();
require_once 'db_connection.php';

$database = new Database();
$database->getConnection(); // ✅ REQUIRED

if (isset($_POST['add_theatre'])) {
    $database->addTheatre(
        $_POST['name'],
        $_POST['city'],
        $_POST['location'],
        $_POST['phone'] ?? '',
        $_POST['total_screens'],
        $_POST['facilities'] ?? ''
    );

    header("Location: admin_theatres.php?success=Theatre added successfully");
    exit();
}

if (isset($_POST['edit_theatre'])) {
    $theatre_id = $_POST['theatre_id'] ?? null;

    if (!$theatre_id) {
        die("Theatre ID is missing");
    }

    $database->updateTheatre(
        $theatre_id,
        $_POST['name'],
        $_POST['city'],
        $_POST['location'],
        $_POST['phone'] ?? '',
        $_POST['total_screens'],
        $_POST['facilities'] ?? ''
    );

    header("Location: admin_theatres.php?success=Theatre updated successfully");
    exit();
}






// Add Theatre
if (isset($_POST['add_theatre'])) {
    $name = $_POST['name'];
    $city = $_POST['city'];
    $location = $_POST['location'];
    $phone = $_POST['phone'] ?? '';
    $total_screens = $_POST['total_screens'];
    $facilities = $_POST['facilities'] ?? '';

    if ($database->addTheatre($name, $city, $location, $phone, $total_screens, $facilities)) {
        header('Location: admin_theatres.php?success=Theatre added successfully');
        exit();
    } else {
        header('Location: admin_theatres.php?error=Failed to add theatre');
        exit();
    }
}

// Edit Theatre
if (isset($_POST['edit_theatre'])) {
    $theatre_id = $_POST['theatre_id'];
    $name = $_POST['name'];
    $city = $_POST['city'];
    $location = $_POST['location'];
    $phone = $_POST['phone'] ?? '';
    $total_screens = $_POST['total_screens'];
    $facilities = $_POST['facilities'] ?? '';

    if ($database->updateTheatre($theatre_id, $name, $city, $location, $phone, $total_screens, $facilities)) {
        header('Location: admin_theatres.php?success=Theatre updated successfully');
        exit();
    } else {
        header('Location: admin_theatres.php?error=Failed to update theatre');
        exit();
    }
}
?>