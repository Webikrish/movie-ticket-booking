<?php
// db_connection.php - Complete database connection and operations

class Database {
    private $host = "localhost";
    private $db_name = "cinemakrish_db";
    private $username = "root"; // Change according to your setup
    private $password = ""; // Change according to your setup
    public $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            die("Connection error. Please check your database configuration.");
        }
        
        return $this->conn;
    }

    // ========== SLIDER IMAGES ==========
    public function getSliderImages() {
        $query = "SELECT * FROM slider_images WHERE is_active = 1 ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== LANGUAGES ==========
    public function getLanguages() {
        $query = "SELECT * FROM languages WHERE is_active = 1 ORDER BY display_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== GENRES ==========
    public function getGenres() {
        $query = "SELECT * FROM genres WHERE is_active = 1 ORDER BY display_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== MOVIES ==========
    public function getNowShowingMovies($filters = []) {
        $query = "SELECT m.*, l.name as language_name, g.name as genre_name 
                  FROM movies m
                  LEFT JOIN languages l ON m.language_id = l.id
                  LEFT JOIN genres g ON m.genre_id = g.id
                  WHERE m.is_now_showing = 1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters)) {
            if (!empty($filters['language'])) {
                $query .= " AND m.language_id = :language_id";
                $params[':language_id'] = $filters['language'];
            }
            
            if (!empty($filters['genre'])) {
                $query .= " AND m.genre_id = :genre_id";
                $params[':genre_id'] = $filters['genre'];
            }
            
            if (!empty($filters['rating'])) {
                $query .= " AND m.rating >= :min_rating";
                $params[':min_rating'] = $filters['rating'];
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND (m.title LIKE :search OR m.description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            if (!empty($filters['date'])) {
                $query .= " AND DATE(m.release_date) <= :selected_date";
                $params[':selected_date'] = $filters['date'];
            }
        }
        
        $query .= " ORDER BY m.release_date DESC LIMIT 6";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getComingSoonMovies() {
        $query = "SELECT m.*, l.name as language_name, g.name as genre_name 
                  FROM movies m
                  LEFT JOIN languages l ON m.language_id = l.id
                  LEFT JOIN genres g ON m.genre_id = g.id
                  WHERE m.is_now_showing = 0 
                  ORDER BY m.release_date ASC LIMIT 4";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== CONTACT INFORMATION ==========
    public function getContactInfo() {
        $query = "SELECT * FROM contact_info WHERE is_active = 1 ORDER BY info_type, display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getContactByType($type) {
        $query = "SELECT * FROM contact_info WHERE info_type = :type AND is_active = 1 ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':type' => $type]);
        return $stmt->fetchAll();
    }

    // ========== SOCIAL LINKS ==========
    public function getSocialLinks() {
        $query = "SELECT * FROM social_links WHERE is_active = 1 ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== QUICK LINKS ==========
    public function getQuickLinks($type = 'footer') {
        $query = "SELECT * FROM quick_links WHERE is_active = 1 AND link_type = :type ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':type' => $type]);
        return $stmt->fetchAll();
    }

    // ========== USER AUTHENTICATION ==========
    public function authenticateUser($username, $password) {
        $query = "SELECT * FROM users WHERE (username = :username1 OR email = :username2) AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':username1' => $username,
            ':username2' => $username
        ]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    // ========== USER REGISTRATION ==========
    public function registerUser($userData) {
        // Check if username or email already exists
        $checkQuery = "SELECT id FROM users WHERE username = :username OR email = :email";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([
            ':username' => $userData['username'],
            ':email' => $userData['email']
        ]);
        
        if ($checkStmt->fetch()) {
            return false; // User already exists
        }
        
        // Insert new user with is_admin = 0 by default
        $query = "INSERT INTO users (username, email, password_hash, full_name, phone, is_admin) 
                  VALUES (:username, :email, :password_hash, :full_name, :phone, 0)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            ':username' => $userData['username'],
            ':email' => $userData['email'],
            ':password_hash' => password_hash($userData['password'], PASSWORD_DEFAULT),
            ':full_name' => $userData['full_name'],
            ':phone' => $userData['phone']
        ]);
    }

    // ========== GET ALL USERS ==========
    public function getAllUsers() {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== GET ADMIN USERS ==========
    public function getAdminUsers() {
        $query = "SELECT * FROM users WHERE is_admin = 1 AND is_active = 1 ORDER BY username";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== GET REGULAR USERS ==========
    public function getRegularUsers() {
        $query = "SELECT * FROM users WHERE is_admin = 0 AND is_active = 1 ORDER BY username";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== GET USER STATISTICS ==========
    public function getUserStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as admin_count,
                    SUM(CASE WHEN is_admin = 0 THEN 1 ELSE 0 END) as customer_count,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users
                  FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    // ========== GET MOVIE STATISTICS ==========
    public function getMovieStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_movies,
                    SUM(CASE WHEN is_now_showing = 1 THEN 1 ELSE 0 END) as now_showing,
                    SUM(CASE WHEN is_now_showing = 0 THEN 1 ELSE 0 END) as coming_soon
                  FROM movies";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    // ========== MAKE USER ADMIN ==========
    public function makeUserAdmin($userId) {
        $query = "UPDATE users SET is_admin = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $userId]);
    }

    // ========== REMOVE ADMIN PRIVILEGES ==========
    public function removeUserAdmin($userId) {
        $query = "UPDATE users SET is_admin = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $userId]);
    }

    // ========== UPDATE USER STATUS ==========
    public function updateUserStatus($id, $status) {
        $query = "UPDATE users SET is_active = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }

    // ========== GET USER BY ID ==========
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ========== UPDATE USER PROFILE ==========
    public function updateUserProfile($id, $data) {
        $query = "UPDATE users SET full_name = :full_name, phone = :phone, email = :email WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
            ':id' => $id
        ]);
    }

    // ========== DELETE USER ==========
    public function deleteUser($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    // ========== SEARCH MOVIES ==========
    public function searchMovies($keyword) {
        $query = "SELECT m.*, l.name as language_name, g.name as genre_name 
                  FROM movies m
                  LEFT JOIN languages l ON m.language_id = l.id
                  LEFT JOIN genres g ON m.genre_id = g.id
                  WHERE (m.title LIKE :keyword OR m.description LIKE :keyword OR l.name LIKE :keyword OR g.name LIKE :keyword)
                  AND m.is_now_showing = 1
                  ORDER BY m.release_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':keyword' => '%' . $keyword . '%']);
        return $stmt->fetchAll();
    }

    // ========== GET MOVIE BY ID ==========
    public function getMovieById($id) {
        $query = "SELECT m.*, l.name as language_name, g.name as genre_name 
                  FROM movies m
                  LEFT JOIN languages l ON m.language_id = l.id
                  LEFT JOIN genres g ON m.genre_id = g.id
                  WHERE m.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ========== GET FEATURED MOVIES ==========
    public function getFeaturedMovies() {
        $query = "SELECT m.*, l.name as language_name, g.name as genre_name 
                  FROM movies m
                  LEFT JOIN languages l ON m.language_id = l.id
                  LEFT JOIN genres g ON m.genre_id = g.id
                  WHERE m.is_featured = 1 AND m.is_now_showing = 1
                  ORDER BY m.release_date DESC LIMIT 3";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== GET ALL MOVIES ==========
    public function getAllMovies() {
        $query = "SELECT m.*, l.name as language_name, g.name as genre_name 
                  FROM movies m
                  LEFT JOIN languages l ON m.language_id = l.id
                  LEFT JOIN genres g ON m.genre_id = g.id
                  ORDER BY m.release_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== ADD MOVIE ==========
    public function addMovie($data) {
        $query = "INSERT INTO movies (title, description, language_id, genre_id, rating, duration, poster_url, release_date, is_now_showing, ticket_price, is_featured) 
                  VALUES (:title, :description, :language_id, :genre_id, :rating, :duration, :poster_url, :release_date, :is_now_showing, :ticket_price, :is_featured)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    // ========== UPDATE MOVIE ==========
    public function updateMovie($id, $data) {
        $query = "UPDATE movies SET 
                  title = :title,
                  description = :description,
                  language_id = :language_id,
                  genre_id = :genre_id,
                  rating = :rating,
                  duration = :duration,
                  poster_url = :poster_url,
                  release_date = :release_date,
                  is_now_showing = :is_now_showing,
                  ticket_price = :ticket_price,
                  is_featured = :is_featured
                  WHERE id = :id";
        
        $data[':id'] = $id;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    // ========== DELETE MOVIE ==========
    public function deleteMovie($id) {
        $query = "DELETE FROM movies WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    // ========== ADD SLIDER IMAGE ==========
    public function addSliderImage($data) {
        $query = "INSERT INTO slider_images (title, description, image_url, button_text, button_action, display_order) 
                  VALUES (:title, :description, :image_url, :button_text, :button_action, :display_order)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    // ========== GET ALL SLIDER IMAGES ==========
    public function getAllSliderImages() {
        $query = "SELECT * FROM slider_images ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== UPDATE SLIDER IMAGE ==========
    public function updateSliderImage($id, $data) {
        $query = "UPDATE slider_images SET 
                  title = :title,
                  description = :description,
                  image_url = :image_url,
                  button_text = :button_text,
                  button_action = :button_action,
                  display_order = :display_order,
                  is_active = :is_active
                  WHERE id = :id";
        
        $data[':id'] = $id;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    // ========== DELETE SLIDER IMAGE ==========
    public function deleteSliderImage($id) {
        $query = "DELETE FROM slider_images WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    // ========== UPDATE CONTACT INFO ==========
    public function updateContactInfo($key, $value) {
        $query = "UPDATE contact_info SET info_value = :value WHERE info_key = :key";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':value' => $value, ':key' => $key]);
    }

    // ========== GET ALL CONTACT INFO ==========
    public function getAllContactInfo() {
        $query = "SELECT * FROM contact_info ORDER BY info_type, display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== GET RECENT ACTIVITIES ==========
    public function getRecentActivities($limit = 10) {
        $query = "SELECT 
                    'user_registered' as type,
                    username as title,
                    created_at as date
                  FROM users
                  UNION ALL
                  SELECT 
                    'movie_added' as type,
                    title,
                    created_at as date
                  FROM movies
                  ORDER BY date DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ========== GET DAILY STATISTICS ==========
    public function getDailyStatistics() {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as registrations
                  FROM users
                  WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    
}

// Create database connection instance
$database = new Database();
$db = $database->getConnection();
?>