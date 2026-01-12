<?php
// theatre.php - CinemaKrish Theatres Page
session_start();

// Database connection
require_once 'config/database.php';

$theatres = [];

$sql = "SELECT * FROM theatres ORDER BY city, name";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $theatres[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaKrish Theatres</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Oswald:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #0a0a0a;
            --secondary-dark: #1a1a1a;
            --accent-red: #d32f2f;
            --accent-gold: #ffc107;
            --text-light: #f8f9fa;
            --text-gray: #adb5bd;
            --card-bg: #1e1e1e;
            --gradient-red: linear-gradient(135deg, #d32f2f 0%, #9a0007 100%);
            --gradient-gold: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-hover: 0 15px 40px rgba(211, 47, 47, 0.2);
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--primary-dark);
            color: var(--text-light);
            overflow-x: hidden;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--secondary-dark);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--accent-red);
            border-radius: 5px;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(10, 10, 10, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            font-weight: 600;
            background: var(--gradient-red);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            margin: 0 10px;
            padding: 8px 16px !important;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: var(--gradient-red);
            color: white !important;
            transform: translateY(-2px);
        }
        
        /* Hero Section */
        .hero-section {
            position: relative;
            padding: 180px 0 120px;
            background: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.9)), 
                        url('https://images.unsplash.com/photo-1536440136628-849c177e76a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1925&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, transparent 0%, rgba(0, 0, 0, 0.9) 100%);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero-title {
            font-family: 'Oswald', sans-serif;
            font-size: 4rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--accent-gold), var(--accent-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-gray);
            margin-bottom: 30px;
            line-height: 1.8;
        }
        
        .btn-hero {
            background: var(--gradient-red);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-hero:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        /* Stats Section */
        .stats-section {
            padding: 60px 0;
            background: var(--secondary-dark);
            position: relative;
        }
        
        .stat-card {
            text-align: center;
            padding: 30px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Theatre Cards */
        .theatre-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        
        .theatre-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-red);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .theatre-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-hover);
            border-color: var(--accent-red);
        }
        
        .theatre-card:hover::before {
            transform: scaleX(1);
        }
        
        .theatre-image {
            height: 200px;
            background: linear-gradient(45deg, #2c3e50, #4a6491);
            position: relative;
            overflow: hidden;
        }
        
        .theatre-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
        }
        
        .theatre-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--gradient-red);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .theatre-header {
            padding: 25px 25px 0;
        }
        
        .theatre-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            color: var(--accent-gold);
            margin-bottom: 10px;
        }
        
        .theatre-location {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .theatre-body {
            padding: 0 25px 25px;
        }
        
        .theatre-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--accent-gold);
        }
        
        .facilities-container {
            margin: 20px 0;
        }
        
        .facility-tag {
            display: inline-block;
            background: rgba(255, 193, 7, 0.1);
            color: var(--accent-gold);
            padding: 6px 15px;
            margin: 0 8px 8px 0;
            border-radius: 20px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }
        
        .theatre-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-theatre {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-book {
            background: var(--gradient-red);
            color: white;
            border: none;
        }
        
        .btn-details {
            background: transparent;
            color: var(--accent-gold);
            border: 2px solid var(--accent-gold);
        }
        
        .btn-book:hover, .btn-details:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        /* Filter Section */
        .filter-section {
            background: var(--secondary-dark);
            padding: 40px;
            border-radius: 15px;
            margin: -60px auto 50px;
            position: relative;
            z-index: 10;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 1200px;
        }
        
        .filter-title {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            color: var(--accent-gold);
            margin-bottom: 25px;
        }
        
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 15px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-red);
            box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.25);
        }
        
        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-gray);
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(to bottom, var(--secondary-dark), var(--primary-dark));
            padding: 80px 0 30px;
            margin-top: 100px;
            position: relative;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-red);
        }
        
        .footer-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            color: var(--accent-gold);
            margin-bottom: 25px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: var(--text-gray);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .footer-links a:hover {
            color: var(--accent-red);
            padding-left: 10px;
        }
        
        .footer-links a::before {
            content: '▶';
            margin-right: 10px;
            font-size: 0.8rem;
            color: var(--accent-red);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover::before {
            opacity: 1;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background: var(--gradient-red);
            transform: translateY(-5px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 50px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-gray);
            font-size: 0.9rem;
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .filter-section {
                margin: -40px 20px 50px;
                padding: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-section {
                padding: 150px 0 80px;
            }
            
            .theatre-card {
                margin-bottom: 20px;
            }
            
            .theatre-actions {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-film me-2"></i>CINEMAKRISH
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="theatre.php">Theatres</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="index.php">Movies</a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="my_bookings.php">Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" style="background: var(--gradient-gold);">
                            <i class=""></i> welcome
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title floating">EXPERIENCE CINEMA REDEFINED</h1>
                <p class="hero-subtitle">
                    Immerse yourself in luxury at our state-of-the-art theatres across Tamil Nadu. 
                    Experience crystal-clear 4K projection, Dolby Atmos sound, and premium seating.
                </p>
                <a href="#theatres" class="btn btn-hero">
                    <i class="fas fa-map-marker-alt me-2"></i> EXPLORE THEATRES
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Theatres</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-number">120+</div>
                        <div class="stat-label">Screens</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Seats</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="stat-number">4K</div>
                        <div class="stat-label">Projection</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <div class="container">
        <div class="filter-section">
            <h2 class="filter-title text-center">Find Your Perfect Theatre</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="cityFilter" class="form-label">City</label>
                    <select class="form-select" id="cityFilter" style="background: #0a0a0a">
                        <option value="all">All Cities</option>
                        <option value="Thoothukudi">Thoothukudi</option>
                        <option value="Thiruchendur">Thiruchendur</option>
                        <option value="Santhakulam">Santhakulam</option>
                        <option value="Aathur">Aathur</option>
                    </select>
                </div>
                <div class="col-md-6 mb-4" >
                    <label for="searchTheatre" class="form-label" >Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchTheatre" placeholder="Search theatre or location..." style="color: white">
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <button class="btn btn-outline-light" id="resetFilters">
                    <i class="fas fa-redo me-2"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Theatres Grid -->
    <div class="container" id="theatres">
        <div class="row mb-5">
            <div class="col">
                <h2 class="text-center mb-3" style="font-family: 'Oswald', sans-serif; font-size: 2.5rem; color: var(--accent-gold);">
                    OUR THEATRES
                </h2>
                <p class="text-center text-gray">
                    Showing <span id="theatreCount" class="text-gold"><?php echo count($theatres); ?></span> premium theatres
                </p>
            </div>
        </div>
        
        <div class="row" id="theatreContainer">
            <?php
            $host = "127.0.0.1";
            $username = "root";
            $password = "";
            $database = "cinemakrish_db";
            
            $conn = new mysqli($host, $username, $password, $database);
            
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            $sql = "SELECT * FROM theatres ORDER BY city, name";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $facilities = !empty($row['facilities']) ? explode(', ', $row['facilities']) : [];
                    $status = $row['is_active'] ? "Active" : "Inactive";
                    
                    echo '
                    <div class="col-lg-4 col-md-6 theatre-item" data-city="' . $row['city'] . '">
                        <div class="theatre-card">
                            <div class="theatre-image">
                                <span class="theatre-badge">
                                    <i class="fas fa-tv me-1"></i> ' . $row['total_screens'] . ' SCREENS
                                </span>
                            </div>
                            <div class="theatre-header">
                                <h3 class="theatre-title">' . $row['name'] . '</h3>
                                <p class="theatre-location">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    ' . $row['location'] . ', ' . $row['city'] . '
                                </p>
                            </div>
                            <div class="theatre-body">
                                <div class="theatre-info">
                                    <div class="info-icon">
                                        <i class="fas fa-chair"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Premium Seating</h6>
                                        <small class="text-gray">Recliner & VIP seats available</small>
                                    </div>
                                </div>
                                
                                <div class="theatre-info">
                                    <div class="info-icon">
                                        <i class="fas fa-volume-up"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Dolby Atmos</h6>
                                        <small class="text-gray">Immersive sound experience</small>
                                    </div>
                                </div>
                                
                                <div class="facilities-container">
                                    <h6>Facilities:</h6>
                                    <div>';
                                    
                                    if (!empty($facilities)) {
                                        foreach (array_slice($facilities, 0, 4) as $facility) {
                                            echo '<span class="facility-tag">' . $facility . '</span>';
                                        }
                                        if (count($facilities) > 4) {
                                            echo '<span class="facility-tag">+' . (count($facilities) - 4) . ' more</span>';
                                        }
                                    } else {
                                        echo '<span class="facility-tag">Dolby Atmos</span>';
                                        echo '<span class="facility-tag">4K Projection</span>';
                                        echo '<span class="facility-tag">Food Court</span>';
                                    }
                                    
                                    echo '</div>
                                </div>
                                
                                <div class="theatre-actions">
                                    <a href="seat_bookings.php?theatre_id=' . $row['id'] . '" class="btn btn-theatre btn-book">
                                        <i class="fas fa-ticket-alt me-2"></i> BOOK NOW
                                    </a>
                                    <a href="https://www.google.com/maps/search/?api=1&query=Current+Location" class="btn btn-theatre btn-details" target="_blank">
                                        <i class="fas fa-directions me-2"></i> DIRECTIONS
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-theater-masks fa-3x text-gray mb-3"></i>
                            <h3 class="text-gray">No theatres available at the moment</h3>
                        </div>
                      </div>';
            }
            
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5">
                    <h3 class="footer-title">CINEMAKRISH</h3>
                    <p class="text-gray mb-4">
                        Redefining cinema experience with cutting-edge technology and luxurious comfort across Tamil Nadu.
                    </p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-5">
                    <h4 class="mb-4" style="color: var(--accent-gold);">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="theatre.php">Theatres</a></li>
                        <li><a href="movies.php">Movies</a></li>
                        <li><a href="bookings.php">My Bookings</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-5">
                    <h4 class="mb-4" style="color: var(--accent-gold);">Services</h4>
                    <ul class="footer-links">
                        <li><a href="#">Online Booking</a></li>
                        <li><a href="#">Group Bookings</a></li>
                        <li><a href="#">Corporate Events</a></li>
                        <li><a href="#">Food & Beverages</a></li>
                        <li><a href="#">Gift Cards</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-5">
                    <h4 class="mb-4" style="color: var(--accent-gold);">Contact Info</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="#">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Anna Road, Thoothukudi
                            </a>
                        </li>
                        <li>
                            <a href="tel:+919876543210">
                                <i class="fas fa-phone me-2"></i>
                                +91 98765 43210
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@cinemakrish.com">
                                <i class="fas fa-envelope me-2"></i>
                                info@cinemakrish.com
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> CinemaKrish. All rights reserved. | Designed with <i class="fas fa-heart text-danger"></i> for cinema lovers</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced Theatre Filtering
        document.addEventListener('DOMContentLoaded', function() {
            const cityFilter = document.getElementById('cityFilter');
            const searchInput = document.getElementById('searchTheatre');
            const resetBtn = document.getElementById('resetFilters');
            const theatreItems = document.querySelectorAll('.theatre-item');
            const theatreCount = document.getElementById('theatreCount');
            
            function filterTheatres() {
                const selectedCity = cityFilter.value;
                const searchText = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;
                
                theatreItems.forEach(item => {
                    const city = item.getAttribute('data-city');
                    const theatreName = item.querySelector('.theatre-title').textContent.toLowerCase();
                    const theatreLocation = item.querySelector('.theatre-location').textContent.toLowerCase();
                    
                    const cityMatch = selectedCity === 'all' || city === selectedCity;
                    const searchMatch = searchText === '' || 
                                        theatreName.includes(searchText) || 
                                        theatreLocation.includes(searchText);
                    
                    if (cityMatch && searchMatch) {
                        item.style.display = 'block';
                        visibleCount++;
                        // Add animation
                        item.style.opacity = '0';
                        setTimeout(() => {
                            item.style.transition = 'opacity 0.5s ease';
                            item.style.opacity = '1';
                        }, 50);
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                theatreCount.textContent = visibleCount;
                
                // Show no results message
                if (visibleCount === 0) {
                    if (!document.getElementById('noResults')) {
                        const noResults = document.createElement('div');
                        noResults.id = 'noResults';
                        noResults.className = 'col-12 text-center py-5';
                        noResults.innerHTML = `
                            <i class="fas fa-search fa-3x text-gray mb-3"></i>
                            <h3 class="text-gray mb-3">No theatres found</h3>
                            <p class="text-gray">Try adjusting your search or filter criteria</p>
                        `;
                        document.getElementById('theatreContainer').appendChild(noResults);
                    }
                } else {
                    const noResults = document.getElementById('noResults');
                    if (noResults) noResults.remove();
                }
            }
            
            // Add event listeners
            cityFilter.addEventListener('change', filterTheatres);
            searchInput.addEventListener('input', filterTheatres);
            resetBtn.addEventListener('click', function() {
                cityFilter.value = 'all';
                searchInput.value = '';
                filterTheatres();
            });
            
            // Add smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Initialize
            filterTheatres();
            
            // Add hover effects
            theatreItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const card = this.querySelector('.theatre-card');
                    card.style.transform = 'translateY(-15px) scale(1.02)';
                });
                
                item.addEventListener('mouseleave', function() {
                    const card = this.querySelector('.theatre-card');
                    card.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>