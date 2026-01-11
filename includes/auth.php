<?php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($data) {
        // Validate email
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Check if email exists
        $this->db->query("SELECT id FROM users WHERE email = :email");
        $this->db->bind(':email', $data['email']);
        if($this->db->single()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Check if phone exists
        $this->db->query("SELECT id FROM users WHERE phone = :phone");
        $this->db->bind(':phone', $data['phone']);
        if($this->db->single()) {
            return ['success' => false, 'message' => 'Phone number already registered'];
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));
        
        // Insert user
        $this->db->query("INSERT INTO users (name, email, phone, password, verification_token) 
                         VALUES (:name, :email, :phone, :password, :token)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':token', $verification_token);
        
        if($this->db->execute()) {
            // Send verification email (implement email function)
            // $this->sendVerificationEmail($data['email'], $verification_token);
            
            return ['success' => true, 'message' => 'Registration successful'];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }
    
    public function login($email, $password) {
        $this->db->query("SELECT * FROM users WHERE email = :email OR phone = :email");
        $this->db->bind(':email', $email);
        $user = $this->db->single();
        
        if($user) {
            if(password_verify($password, $user->password)) {
                if($user->status == 'blocked') {
                    return ['success' => false, 'message' => 'Account is blocked'];
                }
                
                // Set session
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['user_email'] = $user->email;
                
                // Update last login
                $this->db->query("UPDATE users SET last_login = NOW() WHERE id = :id");
                $this->db->bind(':id', $user->id);
                $this->db->execute();
                
                return ['success' => true, 'message' => 'Login successful'];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    public function adminLogin($username, $password) {
        $this->db->query("SELECT * FROM admins WHERE username = :username OR email = :username");
        $this->db->bind(':username', $username);
        $admin = $this->db->single();
        
        if($admin && password_verify($password, $admin->password)) {
            $_SESSION['admin_id'] = $admin->id;
            $_SESSION['admin_name'] = $admin->username;
            $_SESSION['admin_role'] = $admin->role;
            return true;
        }
        return false;
    }
    
    public function forgotPassword($email) {
        $this->db->query("SELECT id FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        $user = $this->db->single();
        
        if($user) {
            $reset_token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->db->query("UPDATE users SET reset_token = :token, reset_expiry = :expiry WHERE id = :id");
            $this->db->bind(':token', $reset_token);
            $this->db->bind(':expiry', $expiry);
            $this->db->bind(':id', $user->id);
            
            if($this->db->execute()) {
                // Send reset email (implement)
                return true;
            }
        }
        return false;
    }
    
    public function resetPassword($token, $password) {
        $this->db->query("SELECT id FROM users WHERE reset_token = :token AND reset_expiry > NOW()");
        $this->db->bind(':token', $token);
        $user = $this->db->single();
        
        if($user) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $this->db->query("UPDATE users SET password = :password, reset_token = NULL, reset_expiry = NULL WHERE id = :id");
            $this->db->bind(':password', $hashedPassword);
            $this->db->bind(':id', $user->id);
            
            return $this->db->execute();
        }
        return false;
    }
}
?>