<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $data = [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'full_name' => $_POST['full_name'],
            'phone' => $_POST['phone'],
            'is_admin' => isset($_POST['is_admin']) ? 1 : 0
        ];
        
        if ($database->addUser($data)) {
            header('Location: admin.php?success=User added successfully');
        } else {
            header('Location: admin.php?error=Failed to add user');
        }
        exit();
    }
    elseif ($action === 'edit') {
        $data = [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'full_name' => $_POST['full_name'],
            'phone' => $_POST['phone'],
            'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        if ($database->updateUser($id, $data)) {
            header('Location: admin.php?success=User updated successfully');
        } else {
            header('Location: admin.php?error=Failed to update user');
        }
        exit();
    }
    elseif ($action === 'delete') {
        if ($database->deleteUser($id)) {
            header('Location: admin.php?success=User deleted successfully');
        } else {
            header('Location: admin.php?error=Failed to delete user');
        }
        exit();
    }
}
?>