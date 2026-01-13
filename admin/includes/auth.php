<?php
session_start();
require_once __DIR__ . '/../../user/db_connection.php';

class AdminAuth {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function login($username, $password) {
        // Prepare SQL statement with proper column names
        $stmt = $this->db->prepare("
            SELECT id, username, email, password_hash, full_name, is_admin 
            FROM users 
            WHERE username = :username 
            AND is_admin = 1
            AND is_active = 1
            LIMIT 1
        ");
        
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and verify password
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_fullname'] = $user['full_name'];
            $_SESSION['admin_is_admin'] = $user['is_admin'];

            // Update last login timestamp
            $this->updateLastLogin($user['id']);
            
            return true;
        }

        return false;
    }
    
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET last_login = NOW() 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $userId]);
    }
    
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) 
               && $_SESSION['admin_logged_in'] === true
               && isset($_SESSION['admin_is_admin'])
               && $_SESSION['admin_is_admin'] == 1;
    }
    
    public function getAdminInfo() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'email' => $_SESSION['admin_email'],
                'fullname' => $_SESSION['admin_fullname']
            ];
        }
        return null;
    }
}

$adminAuth = new AdminAuth();
?>