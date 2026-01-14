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


    public function addShow(
    $movie_id,
    $theatre_id,
    $screen_number,
    $show_date,
    $show_time,
    $available_seats,
    $total_seats,
    $ticket_price,
    $is_active
) {
    $conn = $this->getConnection();

    $sql = "
        INSERT INTO shows
        (movie_id, theatre_id, screen_number, show_date, show_time, available_seats, total_seats, ticket_price, is_active)
        VALUES
        (:movie_id, :theatre_id, :screen_number, :show_date, :show_time, :available_seats, :total_seats, :ticket_price, :is_active)
    ";

    $stmt = $conn->prepare($sql);
    return $stmt->execute([
        ':movie_id' => $movie_id,
        ':theatre_id' => $theatre_id,
        ':screen_number' => $screen_number,
        ':show_date' => $show_date,
        ':show_time' => $show_time,
        ':available_seats' => $available_seats,
        ':total_seats' => $total_seats,
        ':ticket_price' => $ticket_price,
        ':is_active' => $is_active
    ]);
}




    


    public function getShowById($id)
{
    $conn = $this->getConnection();

    $sql = "
        SELECT 
            s.*,
            m.title AS movie_title,
            t.name AS theatre_name
        FROM shows s
        JOIN movies m ON s.movie_id = m.id
        JOIN theatres t ON s.theatre_id = t.id
        WHERE s.id = :id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}
public function updateShow(
    $show_id,
    $movie_id,
    $theatre_id,
    $screen_number,
    $show_date,
    $show_time,
    $available_seats,
    $total_seats,
    $ticket_price,
    $is_active
) {
    $conn = $this->getConnection();

    $sql = "
        UPDATE shows SET
            movie_id = :movie_id,
            theatre_id = :theatre_id,
            screen_number = :screen_number,
            show_date = :show_date,
            show_time = :show_time,
            available_seats = :available_seats,
            total_seats = :total_seats,
            ticket_price = :ticket_price,
            is_active = :is_active
        WHERE id = :id
    ";

    $stmt = $conn->prepare($sql);
    return $stmt->execute([
        ':id' => $show_id,
        ':movie_id' => $movie_id,
        ':theatre_id' => $theatre_id,
        ':screen_number' => $screen_number,
        ':show_date' => $show_date,
        ':show_time' => $show_time,
        ':available_seats' => $available_seats,
        ':total_seats' => $total_seats,
        ':ticket_price' => $ticket_price,
        ':is_active' => $is_active
    ]);
}
public function deleteShow($show_id)
{
    $conn = $this->getConnection();

    $stmt = $conn->prepare("DELETE FROM shows WHERE id = :id");
    return $stmt->execute([':id' => $show_id]);
}





// ========== REPORT FUNCTIONS ==========
public function getSystemStatistics() {
    try {
        $stats = [];
        
        // Total users
        $query = "SELECT COUNT(*) as total FROM users WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch()['total'] ?? 0;
        
        // New users this month
        $query = "SELECT COUNT(*) as total FROM users 
                  WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())
                  AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['new_users'] = $stmt->fetch()['total'] ?? 0;
        
        // Active movies (now showing)
        $query = "SELECT COUNT(*) as total FROM movies WHERE is_now_showing = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['active_movies'] = $stmt->fetch()['total'] ?? 0;
        
        // Total movies
        $query = "SELECT COUNT(*) as total FROM movies";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_movies'] = $stmt->fetch()['total'] ?? 0;
        
        // Today's bookings
        $query = "SELECT COUNT(*) as total FROM bookings WHERE DATE(booking_date) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['today_bookings'] = $stmt->fetch()['total'] ?? 0;
        
        // Total bookings
        $query = "SELECT COUNT(*) as total FROM bookings";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_bookings'] = $stmt->fetch()['total'] ?? 0;
        
        // Total revenue (only confirmed bookings)
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE booking_status = 'confirmed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        // Monthly revenue
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings 
                  WHERE booking_status = 'confirmed'
                  AND MONTH(booking_date) = MONTH(CURRENT_DATE()) 
                  AND YEAR(booking_date) = YEAR(CURRENT_DATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['monthly_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        return $stats;
    } catch (Exception $e) {
        error_log("getSystemStatistics error: " . $e->getMessage());
        return [
            'total_users' => 0,
            'new_users' => 0,
            'active_movies' => 0,
            'total_movies' => 0,
            'today_bookings' => 0,
            'total_bookings' => 0,
            'total_revenue' => 0,
            'monthly_revenue' => 0
        ];
    }
}

public function getRevenueStatistics($start_date, $end_date) {
    try {
        $stats = [];
        
        // Total revenue in date range
        $query = "SELECT 
                    COALESCE(SUM(total_amount), 0) as total_revenue,
                    COALESCE(AVG(total_amount), 0) as avg_daily,
                    COALESCE(MAX(total_amount), 0) as max_daily,
                    COALESCE(MIN(total_amount), 0) as min_daily
                  FROM bookings 
                  WHERE booking_status = 'confirmed'
                  AND DATE(booking_date) BETWEEN :start_date AND :end_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        $revenue_data = $stmt->fetch();
        
        $stats['total_revenue'] = $revenue_data['total_revenue'] ?? 0;
        $stats['avg_daily'] = $revenue_data['avg_daily'] ?? 0;
        $stats['max_daily'] = $revenue_data['max_daily'] ?? 0;
        $stats['min_daily'] = $revenue_data['min_daily'] ?? 0;
        
        // Revenue by payment method
        $query = "SELECT 
                    COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total_amount ELSE 0 END), 0) as cash,
                    COALESCE(SUM(CASE WHEN payment_method = 'credit_card' THEN total_amount ELSE 0 END), 0) as credit_card,
                    COALESCE(SUM(CASE WHEN payment_method = 'debit_card' THEN total_amount ELSE 0 END), 0) as debit_card,
                    COALESCE(SUM(CASE WHEN payment_method = 'paypal' THEN total_amount ELSE 0 END), 0) as paypal
                  FROM bookings 
                  WHERE booking_status = 'confirmed'
                  AND DATE(booking_date) BETWEEN :start_date AND :end_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        $payment_data = $stmt->fetch();
        
        $stats['payment_methods'] = [
            'cash' => $payment_data['cash'] ?? 0,
            'credit_card' => $payment_data['credit_card'] ?? 0,
            'debit_card' => $payment_data['debit_card'] ?? 0,
            'paypal' => $payment_data['paypal'] ?? 0
        ];
        
        return $stats;
    } catch (Exception $e) {
        error_log("getRevenueStatistics error: " . $e->getMessage());
        return [
            'total_revenue' => 0,
            'avg_daily' => 0,
            'max_daily' => 0,
            'min_daily' => 0,
            'payment_methods' => [
                'cash' => 0,
                'credit_card' => 0,
                'debit_card' => 0,
                'paypal' => 0
            ]
        ];
    }
}

public function getBookingStatisticsByDate($start_date, $end_date) {
    try {
        $stats = [];
        
        // Booking counts by status
        $query = "SELECT 
                    COALESCE(COUNT(*), 0) as total_bookings,
                    COALESCE(SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END), 0) as confirmed_bookings,
                    COALESCE(SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END), 0) as pending_bookings,
                    COALESCE(SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled_bookings,
                    COALESCE(AVG(total_seats), 0) as avg_seats
                  FROM bookings 
                  WHERE DATE(booking_date) BETWEEN :start_date AND :end_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        $booking_data = $stmt->fetch();
        
        $stats['total_bookings'] = $booking_data['total_bookings'] ?? 0;
        $stats['confirmed_bookings'] = $booking_data['confirmed_bookings'] ?? 0;
        $stats['pending_bookings'] = $booking_data['pending_bookings'] ?? 0;
        $stats['cancelled_bookings'] = $booking_data['cancelled_bookings'] ?? 0;
        $stats['avg_seats'] = $booking_data['avg_seats'] ?? 0;
        
        // Recent bookings (last 10)
        $query = "SELECT b.*, 
                         u.full_name as customer_name,
                         m.title as movie_title,
                         t.name as theatre_name
                  FROM bookings b
                  JOIN users u ON b.user_id = u.id
                  JOIN movies m ON b.movie_id = m.id
                  JOIN theatres t ON b.theatre_id = t.id
                  WHERE DATE(b.booking_date) BETWEEN :start_date AND :end_date
                  ORDER BY b.booking_date DESC
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        $stats['recent_bookings'] = $stmt->fetchAll();
        
        return $stats;
    } catch (Exception $e) {
        error_log("getBookingStatisticsByDate error: " . $e->getMessage());
        return [
            'total_bookings' => 0,
            'confirmed_bookings' => 0,
            'pending_bookings' => 0,
            'cancelled_bookings' => 0,
            'avg_seats' => 0,
            'recent_bookings' => []
        ];
    }
}

public function getMovieStatisticsReport($start_date, $end_date) {
    try {
        $stats = [];
        
        // Movie counts
        $query = "SELECT 
                    COALESCE(COUNT(*), 0) as total_movies,
                    COALESCE(SUM(CASE WHEN is_now_showing = 1 THEN 1 ELSE 0 END), 0) as now_showing,
                    COALESCE(SUM(CASE WHEN is_now_showing = 0 THEN 1 ELSE 0 END), 0) as coming_soon,
                    COALESCE(SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END), 0) as featured,
                    COALESCE(AVG(rating), 0) as avg_rating
                  FROM movies";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $movie_data = $stmt->fetch();
        
        $stats['total_movies'] = $movie_data['total_movies'] ?? 0;
        $stats['now_showing'] = $movie_data['now_showing'] ?? 0;
        $stats['coming_soon'] = $movie_data['coming_soon'] ?? 0;
        $stats['featured'] = $movie_data['featured'] ?? 0;
        $stats['avg_rating'] = $movie_data['avg_rating'] ?? 0;
        
        // Top movies by revenue
        $query = "SELECT 
                    m.id,
                    m.title,
                    COALESCE(m.rating, 0) as rating,
                    m.is_featured,
                    COALESCE(COUNT(b.id), 0) as bookings,
                    COALESCE(SUM(b.total_amount), 0) as revenue,
                    COALESCE(AVG(b.total_amount), 0) as avg_ticket
                  FROM movies m
                  LEFT JOIN bookings b ON m.id = b.movie_id
                    AND b.booking_status = 'confirmed'
                    AND DATE(b.booking_date) BETWEEN :start_date AND :end_date
                  GROUP BY m.id
                  ORDER BY revenue DESC
                  LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        $stats['top_movies'] = $stmt->fetchAll();
        
        // All movies with performance
        $query = "SELECT 
                    m.*,
                    l.name as language_name,
                    g.name as genre_name,
                    COALESCE(COUNT(b.id), 0) as bookings,
                    COALESCE(SUM(b.total_amount), 0) as revenue
                  FROM movies m
                  LEFT JOIN languages l ON m.language_id = l.id
                  LEFT JOIN genres g ON m.genre_id = g.id
                  LEFT JOIN bookings b ON m.id = b.movie_id
                    AND b.booking_status = 'confirmed'
                    AND DATE(b.booking_date) BETWEEN :start_date AND :end_date
                  GROUP BY m.id
                  ORDER BY revenue DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        $all_movies = $stmt->fetchAll();
        
        // Calculate occupancy percentage for each movie
        foreach ($all_movies as &$movie) {
            $movie['occupancy'] = 0;
            if ($movie['bookings'] > 0) {
                // Get total seats for this movie from shows
                $seat_query = "SELECT COALESCE(SUM(total_seats), 0) as total_seats 
                              FROM shows 
                              WHERE movie_id = :movie_id
                              AND show_date BETWEEN :start_date AND :end_date";
                $seat_stmt = $this->conn->prepare($seat_query);
                $seat_stmt->execute([
                    ':movie_id' => $movie['id'],
                    ':start_date' => $start_date,
                    ':end_date' => $end_date
                ]);
                $seat_data = $seat_stmt->fetch();
                $total_seats = $seat_data['total_seats'] ?? 1; // Avoid division by zero
                
                // Occupancy percentage (based on bookings vs total seats in shows)
                $movie['occupancy'] = $total_seats > 0 ? ($movie['bookings'] / $total_seats) * 100 : 0;
            }
        }
        
        $stats['all_movies'] = $all_movies;
        
        return $stats;
    } catch (Exception $e) {
        error_log("getMovieStatisticsReport error: " . $e->getMessage());
        return [
            'total_movies' => 0,
            'now_showing' => 0,
            'coming_soon' => 0,
            'featured' => 0,
            'avg_rating' => 0,
            'top_movies' => [],
            'all_movies' => []
        ];
    }
}

public function getTheatreStatisticsReport($start_date, $end_date) {
    try {
        $query = "SELECT 
                    t.id,
                    t.name,
                    t.city,
                    t.location,
                    COALESCE(COUNT(b.id), 0) as bookings,
                    COALESCE(SUM(b.total_amount), 0) as revenue
                  FROM theatres t
                  LEFT JOIN bookings b ON t.id = b.theatre_id
                    AND b.booking_status = 'confirmed'
                    AND DATE(b.booking_date) BETWEEN :start_date AND :end_date
                  GROUP BY t.id
                  ORDER BY revenue DESC
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        
        return [
            'top_theatres' => $stmt->fetchAll()
        ];
    } catch (Exception $e) {
        error_log("getTheatreStatisticsReport error: " . $e->getMessage());
        return [
            'top_theatres' => []
        ];
    }
}

public function getUserStatisticsReport($start_date, $end_date) {
    try {
        $query = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as admin_count,
                    SUM(CASE WHEN is_admin = 0 THEN 1 ELSE 0 END) as customer_count,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users
                  FROM users";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch() ?? [
            'total_users' => 0,
            'admin_count' => 0,
            'customer_count' => 0,
            'active_users' => 0
        ];
    } catch (Exception $e) {
        error_log("getUserStatisticsReport error: " . $e->getMessage());
        return [
            'total_users' => 0,
            'admin_count' => 0,
            'customer_count' => 0,
            'active_users' => 0
        ];
    }
}

public function getDailyRevenue($start_date, $end_date) {
    try {
        $query = "SELECT 
                    DATE(booking_date) as date,
                    COALESCE(COUNT(*), 0) as bookings,
                    COALESCE(SUM(total_amount), 0) as revenue,
                    COALESCE(AVG(total_amount), 0) as avg_booking
                  FROM bookings 
                  WHERE booking_status = 'confirmed'
                    AND DATE(booking_date) BETWEEN :start_date AND :end_date
                  GROUP BY DATE(booking_date)
                  ORDER BY date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        
        $results = $stmt->fetchAll();
        
        // Calculate percentage change
        $prev_revenue = 0;
        foreach ($results as &$result) {
            if ($prev_revenue > 0) {
                $result['change'] = (($result['revenue'] - $prev_revenue) / $prev_revenue) * 100;
            } else {
                $result['change'] = 0;
            }
            $prev_revenue = $result['revenue'];
        }
        
        // If no results, return empty array
        return $results ?: [];
    } catch (Exception $e) {
        error_log("getDailyRevenue error: " . $e->getMessage());
        return [];
    }
}

// Get payment method distribution
public function getPaymentMethodDistribution($start_date, $end_date) {
    $query = "SELECT 
                payment_method,
                COUNT(*) as count,
                SUM(total_amount) as amount
              FROM bookings 
              WHERE booking_status = 'confirmed'
                AND DATE(booking_date) BETWEEN :start_date AND :end_date
              GROUP BY payment_method";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute([
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
    
    return $stmt->fetchAll();
}

// Get booking trend data
public function getBookingTrendData($start_date, $end_date) {
    $query = "SELECT 
                DATE(booking_date) as date,
                COUNT(*) as bookings,
                SUM(total_seats) as seats
              FROM bookings 
              WHERE booking_status = 'confirmed'
                AND DATE(booking_date) BETWEEN :start_date AND :end_date
              GROUP BY DATE(booking_date)
              ORDER BY date";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute([
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
    
    return $stmt->fetchAll();
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

    public function addUser(
    $username,
    $email,
    $password,
    $full_name,
    $phone,
    $is_admin,
    $is_active
) {
    $sql = "INSERT INTO users
        (username, email, password_hash, full_name, phone, is_admin, is_active)
        VALUES
        (:username, :email, :password, :full_name, :phone, :is_admin, :is_active)";


    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);
    $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);

    return $stmt->execute();
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


      public function updateUser(
    $user_id,
    $username,
    $email,
    $password,
    $full_name,
    $phone,
    $is_admin,
    $is_active
) {
    if ($password) {
        // Update WITH password
        $sql = "UPDATE users SET
            username = :username,
            email = :email,
            password_hash = :password,
            full_name = :full_name,
            phone = :phone,
            is_admin = :is_admin,
            is_active = :is_active
        WHERE id = :id";

    } else {
        // Update WITHOUT password
        $sql = "UPDATE users SET
            username = :username,
            email = :email,
            password_hash = :password,
            full_name = :full_name,
            phone = :phone,
            is_admin = :is_admin,
            is_active = :is_active
        WHERE id = :id";

    }

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);
    $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($password) {
        $stmt->bindParam(':password', $password);
    }

    return $stmt->execute();
}



    // ========== GET USER BY ID ==========
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getUserByUsernameOrEmail($username, $email)
{
    $sql = "SELECT * FROM users 
            WHERE username = :username OR email = :email 
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
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
    public function addMovie(
    $title, $language_id, $genre_id, $rating, $duration,
    $ticket_price, $release_date, $poster_url, $description,
    $is_now_showing, $is_featured
) {
    $sql = "INSERT INTO movies 
            (title, language_id, genre_id, rating, duration, ticket_price,
             release_date, poster_url, description, is_now_showing, is_featured)
            VALUES
            (:title, :language_id, :genre_id, :rating, :duration, :ticket_price,
             :release_date, :poster_url, :description, :is_now_showing, :is_featured)";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':language_id', $language_id);
    $stmt->bindParam(':genre_id', $genre_id);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':duration', $duration);
    $stmt->bindParam(':ticket_price', $ticket_price);
    $stmt->bindParam(':release_date', $release_date);
    $stmt->bindParam(':poster_url', $poster_url);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':is_now_showing', $is_now_showing, PDO::PARAM_INT);
    $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);

    return $stmt->execute();   // ✅ NO arguments
}


    // ========== UPDATE MOVIE ==========
    public function updateMovie(
    $movie_id, $title, $language_id, $genre_id, $rating,
    $duration, $ticket_price, $release_date, $poster_url,
    $description, $is_now_showing, $is_featured
) {
    $sql = "UPDATE movies SET
                title = :title,
                language_id = :language_id,
                genre_id = :genre_id,
                rating = :rating,
                duration = :duration,
                ticket_price = :ticket_price,
                release_date = :release_date,
                poster_url = :poster_url,
                description = :description,
                is_now_showing = :is_now_showing,
                is_featured = :is_featured
            WHERE id = :movie_id";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':language_id', $language_id);
    $stmt->bindParam(':genre_id', $genre_id);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':duration', $duration);
    $stmt->bindParam(':ticket_price', $ticket_price);
    $stmt->bindParam(':release_date', $release_date);
    $stmt->bindParam(':poster_url', $poster_url);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':is_now_showing', $is_now_showing, PDO::PARAM_INT);
    $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
    $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);

    return $stmt->execute();   // ✅ returns true/false
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
 

public function updateSliderStatus($id, $is_active)
{
    // Ensure database connection
    if (!$this->conn) {
        $this->getConnection();
    }

    $sql = "UPDATE slider_images
            SET is_active = :is_active,
                updated_at = NOW()
            WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ':id'        => $id,
        ':is_active' => $is_active
    ]);
}



public function addSlider($title, $description, $image_url, $display_order = 1, $is_active = 1)
{
    // Ensure database connection
    if (!$this->conn) {
        $this->getConnection();
    }

    $sql = "INSERT INTO slider_images 
            (title, description, image_url, display_order, is_active, created_at, updated_at)
            VALUES 
            (:title, :description, :image_url, :display_order, :is_active, NOW(), NOW())";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ':title'         => $title,
        ':description'   => $description,
        ':image_url'     => $image_url,
        ':display_order' => (int)$display_order,
        ':is_active'     => $is_active
    ]);
}






    // ========== DELETE SLIDER IMAGE ==========
    public function deleteSlider($id) {
    $stmt = $this->conn->prepare("DELETE FROM slider_images WHERE id = ?");
    return $stmt->execute([$id]);
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

    public function getBookingById($booking_id)
{
    $sql = "SELECT 
                b.id,
                b.ticket_number,
                b.booking_date,
                b.booking_status,
                b.total_amount,
                b.total_seats,
                b.seat_numbers,
                b.special_notes,

                u.full_name AS customer_name,
                u.email AS customer_email,

                m.title AS movie_title,
                t.name AS theatre_name,

                s.show_date,
                s.show_time,

                p.payment_status,
                p.payment_method
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN shows s ON b.show_id = s.id
            JOIN movies m ON s.movie_id = m.id
            JOIN theatres t ON s.theatre_id = t.id
            LEFT JOIN payments p ON b.id = p.booking_id
            WHERE b.id = :id
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function deleteBooking($booking_id)
{
    $sql = "DELETE FROM bookings WHERE id = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $booking_id, PDO::PARAM_INT);
    return $stmt->execute();
}

public function getDistinctCities()
{
    $sql = "SELECT DISTINCT city FROM theatres WHERE city IS NOT NULL AND city != ''";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}




    // Get booking statistics
public function getBookingStatistics() {
    $query = "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                SUM(total_amount) as total_revenue
              FROM bookings";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetch();
}

// Get bookings with details
public function getBookingsWithDetails($limit = 50) {
    $query = "SELECT b.*, 
                     u.username as customer_username, 
                     u.email as customer_email,
                     m.title as movie_title,
                     t.name as theatre_name
              FROM bookings b
              JOIN users u ON b.user_id = u.id
              JOIN movies m ON b.movie_id = m.id
              JOIN theatres t ON b.theatre_id = t.id
              ORDER BY b.booking_date DESC
              LIMIT :limit";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Update booking status
public function updateBookingStatus($bookingId, $status) {
    $query = "UPDATE bookings SET booking_status = :status WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    return $stmt->execute([
        ':status' => $status,
        ':id' => $bookingId
    ]);
}

// Get all theatres
public function getAllTheatres() {
    $query = "SELECT * FROM theatres ORDER BY city, name";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Add theatre
public function addTheatre($name, $city, $location, $phone, $total_screens, $facilities)
{
    // Ensure DB connection exists
    if (!$this->conn) {
        $this->getConnection();
    }

    $sql = "INSERT INTO theatres 
            (name, city, location, phone, total_screens, facilities)
            VALUES 
            (:name, :city, :location, :phone, :total_screens, :facilities)";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ':name'          => $name,
        ':city'          => $city,
        ':location'      => $location,
        ':phone'         => $phone,
        ':total_screens' => (int)$total_screens,
        ':facilities'    => $facilities
    ]);
}

public function editTheatre($id, $name, $city, $location, $phone, $total_screens, $facilities)
{
    // Ensure DB connection exists
    if (!$this->conn) {
        $this->getConnection();
    }

    $sql = "UPDATE theatres
            SET name = :name,
                city = :city,
                location = :location,
                phone = :phone,
                total_screens = :total_screens,
                facilities = :facilities
            WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ':id'            => $id,
        ':name'          => $name,
        ':city'          => $city,
        ':location'      => $location,
        ':phone'         => $phone,
        ':total_screens' => (int)$total_screens,
        ':facilities'    => $facilities
    ]);
}


// Update theatre
public function updateTheatre($id, $name, $city, $location, $phone, $total_screens, $facilities)
{
    if (!$this->conn) {
        $this->getConnection();
    }

    // Validate ID
    if (empty($id)) {
        throw new Exception("Theatre ID cannot be empty");
    }

    $sql = "UPDATE theatres
            SET name = :name,
                city = :city,
                location = :location,
                phone = :phone,
                total_screens = :total_screens,
                facilities = :facilities
            WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ':id'            => $id,
        ':name'          => $name,
        ':city'          => $city,
        ':location'      => $location,
        ':phone'         => $phone,
        ':total_screens' => (int)$total_screens,
        ':facilities'    => $facilities
    ]);
}

public function getTheatreById($id)
{
    if (!$this->conn) $this->getConnection();

    $sql = "SELECT * FROM theatres WHERE id = :id LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(); // returns an associative array
}



// Delete theatre
public function deleteTheatre($id) {
    $query = "DELETE FROM theatres WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    return $stmt->execute([':id' => $id]);
}

// Get all theatres
// public function getAllTheatres() {
//     $query = "SELECT * FROM theatres WHERE is_active = 1 ORDER BY city, name";
//     $stmt = $this->conn->prepare($query);
//     $stmt->execute();
//     return $stmt->fetchAll();
// }

// Get all bookings with details
public function getAllBookingsWithDetails() {
    $query = "SELECT b.*, 
                     u.username as customer_username, 
                     u.email as customer_email,
                     u.phone as customer_phone,
                     m.title as movie_title,
                     t.name as theatre_name,
                     t.location as theatre_location
              FROM bookings b
              JOIN users u ON b.user_id = u.id
              JOIN movies m ON b.movie_id = m.id
              JOIN theatres t ON b.theatre_id = t.id
              ORDER BY b.booking_date DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get booking statistics
// public function getBookingStatistics() {
//     $query = "SELECT 
//                 COUNT(*) as total_bookings,
//                 SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
//                 SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending,
//                 SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
//                 SUM(total_amount) as total_revenue,
//                 AVG(total_amount) as avg_booking_value
//               FROM bookings
//               WHERE MONTH(booking_date) = MONTH(CURRENT_DATE()) 
//               AND YEAR(booking_date) = YEAR(CURRENT_DATE())";
    
//     $stmt = $this->conn->prepare($query);
//     $stmt->execute();
//     return $stmt->fetch();
// }



// Get revenue statistics by day for the last 7 days


// Get popular movies (most bookings)
public function getPopularMovies($limit = 5) {
    $query = "SELECT 
                m.id,
                m.title,
                m.poster_url,
                COUNT(b.id) as bookings_count,
                SUM(b.total_seats) as total_seats
              FROM bookings b
              JOIN movies m ON b.movie_id = m.id
              WHERE b.booking_status = 'confirmed'
              GROUP BY m.id, m.title
              ORDER BY bookings_count DESC
              LIMIT :limit";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Update booking status
// public function updateBookingStatus($bookingId, $status) {
//     $query = "UPDATE bookings SET booking_status = :status WHERE id = :id";
//     $stmt = $this->conn->prepare($query);
//     return $stmt->execute([
//         ':status' => $status,
//         ':id' => $bookingId
//     ]);
// }

// Search bookings
public function searchBookings($searchTerm) {
    $query = "SELECT b.*, 
                     u.username, u.email,
                     m.title as movie_title,
                     t.name as theatre_name
              FROM bookings b
              JOIN users u ON b.user_id = u.id
              JOIN movies m ON b.movie_id = m.id
              JOIN theatres t ON b.theatre_id = t.id
              WHERE b.ticket_number LIKE :search
                 OR u.username LIKE :search
                 OR u.email LIKE :search
                 OR m.title LIKE :search
                 OR t.name LIKE :search
              ORDER BY b.booking_date DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute([':search' => '%' . $searchTerm . '%']);
    return $stmt->fetchAll();
}

// Get system statistics for dashboard


// Get all shows with details
public function getShowsWithDetails() {
    $query = "SELECT s.*, 
                     m.title as movie_title,
                     t.name as theatre_name
              FROM shows s
              JOIN movies m ON s.movie_id = m.id
              JOIN theatres t ON s.theatre_id = t.id
              ORDER BY s.show_date DESC, s.show_time ASC";
    
    $stmt = $this->conn->prepare($query);
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