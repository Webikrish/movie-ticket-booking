<?php
// session_manager.php - Updated with is_admin column support

session_start();

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin (using is_admin column)
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Get current user data
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'is_admin' => $_SESSION['is_admin'] ?? 0,
            'phone' => $_SESSION['phone'] ?? ''
        ];
    }
    return null;
}

// Redirect if not logged in
function requireLogin($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirectTo);
        exit();
    }
}

// Redirect if not admin
function requireAdmin($redirectTo = 'index.php') {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
        header('Location: ' . $redirectTo);
        exit();
    }
}

// Login function with is_admin support
function loginUser($user) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'] ?? '';
    $_SESSION['email'] = $user['email'] ?? '';
    $_SESSION['phone'] = $user['phone'] ?? '';
    $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
    $_SESSION['login_time'] = time();
    
    // Update last login in database
    if (isset($user['id'])) {
        try {
            require_once 'db_connection.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $user['id']]);
        } catch (Exception $e) {
            error_log("Error updating last login: " . $e->getMessage());
        }
    }
}

// Logout function
function logoutUser() {
    $_SESSION = array();
    
    // If it's desired to kill the session, also delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Check if user exists
function userExists($username, $email, $db) {
    $query = "SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':username' => $username,
        ':email' => $email
    ]);
    return $stmt->fetch() !== false;
}

// Get user role label
function getUserRoleLabel($is_admin) {
    return $is_admin == 1 ? 'Admin' : 'Customer';
}

// Get user role badge color
function getUserRoleBadgeColor($is_admin) {
    return $is_admin == 1 ? 'danger' : 'info';
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Redirect with message
function redirectWithMessage($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header('Location: ' . $url);
    exit();
}

// Get flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Display flash message
function displayFlashMessage() {
    $message = getFlashMessage();
    if ($message) {
        $alertClass = '';
        switch ($message['type']) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
            case 'danger':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            case 'info':
            default:
                $alertClass = 'alert-info';
        }
        
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-info-circle me-2"></i>' . $message['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// Check if user can access admin panel
function canAccessAdminPanel() {
    return isLoggedIn() && isAdmin();
}
?>