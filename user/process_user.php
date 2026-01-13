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

// Add User
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_active = 1; // New users are active by default

    // Check if username or email already exists
    $existing_user = $database->getUserByUsernameOrEmail($username, $email);
    if ($existing_user) {
        header('Location: admin_users.php?error=Username or email already exists');
        exit();
    }

    if ($database->addUser($username, $email, $password, $full_name, $phone, $is_admin, $is_active)) {
        header('Location: admin_users.php?success=User added successfully');
        exit();
    } else {
        header('Location: admin_users.php?error=Failed to add user');
        exit();
    }
}

// Edit User
if (isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Check if username or email already exists (excluding current user)
    $existing_user = $database->getUserByUsernameOrEmail($username, $email);
    if ($existing_user && $existing_user['id'] != $user_id) {
        header('Location: admin_users.php?error=Username or email already exists');
        exit();
    }

    if ($database->updateUser($user_id, $username, $email, $password, $full_name, $phone, $is_admin, $is_active)) {
        header('Location: admin_users.php?success=User updated successfully');
        exit();
    } else {
        header('Location: admin_users.php?error=Failed to update user');
        exit();
    }
}
?>