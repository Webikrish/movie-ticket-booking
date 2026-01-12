<?php
// Start session FIRST - before any output
// session_start();

// Include database connection

require_once 'session_manager.php';
require_once 'db_connection.php';


// Initialize database operations
$database = new Database();
$db = $database->getConnection();

// Handle notification submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notify_email'])) {
    $movie_id = $_POST['movie_id'];
    $email = $_POST['notify_email'];
    
    // Save notification to database
    $stmt = $db->prepare("INSERT INTO notifications (movie_id, email) VALUES (?, ?)");
    $result = $stmt->execute([$movie_id, $email]);
    
    if ($result) {
        $_SESSION['notification_success'] = "Thank you! We'll notify you when tickets are available.";
    } else {
        $_SESSION['notification_error'] = "Failed to save notification. Please try again.";
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get all data from database
$slider_images = $database->getSliderImages();
$languages = $database->getLanguages();
$genres = $database->getGenres();

// Handle filter requests
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filters['search'] = trim($_GET['search']);
    }
    if (isset($_GET['language']) && $_GET['language'] !== 'all') {
        $filters['language'] = $_GET['language'];
    }
    if (isset($_GET['genre']) && $_GET['genre'] !== 'all') {
        $filters['genre'] = $_GET['genre'];
    }
    if (isset($_GET['rating']) && $_GET['rating'] !== 'all') {
        $filters['rating'] = $_GET['rating'];
    }
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        $filters['date'] = $_GET['date'];
    }
}

// Get movies based on filters
$now_showing_movies = $database->getNowShowingMovies($filters);
$coming_soon_movies = $database->getComingSoonMovies();
$contact_info = $database->getContactInfo();
$social_links = $database->getSocialLinks();
$footer_links = $database->getQuickLinks('footer');
$address_info = $database->getContactByType('address');
$phone_info = $database->getContactByType('phone');
$email_info = $database->getContactByType('email');
$hours_info = $database->getContactByType('hours');

// Get featured movies
$featured_movies = $database->getFeaturedMovies();

// Get notification messages from session
$notification_success = $_SESSION['notification_success'] ?? null;
$notification_error = $_SESSION['notification_error'] ?? null;

// Clear session messages after retrieving
unset($_SESSION['notification_success'], $_SESSION['notification_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaKrish - Movie Ticket Booking</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Oswald:wght@500;600&display=swap" rel="stylesheet">
    
    <style>
        /* Notification Modal Styles */
        .notification-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .notification-content {
            background: var(--primary-dark);
            padding: 2rem;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            border: 2px solid var(--accent-gold);
        }
        
        .notification-content h3 {
            color: var(--accent-gold);
            margin-bottom: 1rem;
        }
        
        .notification-content .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            margin-bottom: 1rem;
        }
        
        .notification-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        /* Toast notification */
        .toast-notification {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            display: none;
        }
        
        /* Enhanced filter styles */
        .search-section {
            background: linear-gradient(135deg, rgba(10, 10, 10, 0.9), rgba(20, 20, 20, 0.9));
            padding: 2rem;
            border-radius: 10px;
            margin-top: -50px;
            position: relative;
            z-index: 100;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .search-section h3 {
            color: var(--accent-gold);
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
        }
        
        .filter-group {
            margin-bottom: 1rem;
        }
        
        .filter-label {
            color: #fff;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            background: rgba(0, 0, 0, 0.5) !important;
            border: 1px solid rgba(255, 215, 0, 0.3) !important;
            color: white !important;
            padding: 0.75rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-gold) !important;
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25) !important;
        }
        
        #searchMovie {
            background: rgba(255, 255, 255, 0.9) !important;
            color: black !important;
        }
        
        #searchMovie::placeholder {
            color: #666;
        }
        
        #searchMovie:focus {
            background: white !important;
        }
        
        /* Custom scrollbar for dropdowns */
        .form-select option {
            background: var(--primary-dark);
            color: white;
            padding: 10px;
        }
        
        /* Date input styling */
        #filterDate::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
        
        /* Filter button hover effects */
        #applyFilters:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
    </style>
    <link href="style.css" rel="stylesheet">
    
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader"></div>
    </div>

    <!-- Particle Background -->
    <div class="particles" id="particles"></div>

    <!-- Notification Toast -->
    <div class="toast-notification alert" id="notificationToast" role="alert">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="hideToast()"></button>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="notification-modal" id="notificationModal">
        <div class="notification-content">
            <h3><i class="fas fa-bell me-2"></i>Get Notified</h3>
            <p>Enter your email to get notified when tickets are available:</p>
            <form id="notifyForm" method="POST">
                <input type="hidden" name="movie_id" id="notifyMovieId">
                <div class="mb-3">
                    <input type="email" class="form-control" name="notify_email" id="notifyEmail" placeholder="your@email.com" required>
                </div>
                <div class="notification-buttons">
                    <button type="submit" class="btn btn-hero flex-grow-1">
                        <i class="fas fa-paper-plane me-2"></i>Notify Me
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeNotificationModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header Section -->
    <?php include "header.php"?>
    <!-- Dynamic Hero Banner Section -->
    <section class="hero-slider">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php if(count($slider_images) > 0): ?>
                    <?php foreach($slider_images as $index => $slide): ?>
                        <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?> hero-slide" style="background-image: url('<?php echo htmlspecialchars($slide['image_url']); ?>');">
                            <div class="hero-content">
                                <h1 class="hero-title"><?php echo htmlspecialchars($slide['title']); ?></h1>
                                <p class="hero-description"><?php echo htmlspecialchars($slide['description']); ?></p>
                                <button class="btn btn-hero" onclick="handleSliderAction('<?php echo $slide['button_action']; ?>')">
                                    <span><?php echo htmlspecialchars($slide['button_text']); ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback if no slider images in database -->
                    <div class="carousel-item active hero-slide" style="background-image: url('https://images.unsplash.com/photo-1489599809516-9827b6d1cf13?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                        <div class="hero-content">
                            <h1 class="hero-title">Experience Movies Like Never Before</h1>
                            <p class="hero-description">Immerse yourself in stunning 4K projection and Dolby Atmos sound at CinemaKrish. Book your tickets now for the latest blockbusters.</p>
                            <button class="btn btn-hero">Book Tickets Now</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container">
        <!-- Search & Filter Section (Dynamic) -->
        <section class="search-section">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="text-center mb-4"><i class="fas fa-search me-2"></i>Find Your Perfect Movie</h3>
                </div>
                
                <form method="GET" action="" id="filterForm">
                    <div class="row">
                         <!-- <div class="col-md-8">
                             <div class="filter-group">
                                <label class="filter-label">Search Movie</label>
                                <input type="text" class="form-control" name="search" id="searchMovie" 
                                       placeholder="Enter movie name, actor, or director..." 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div> 
                        </div>  -->
                        
                        <div class="col-md-4">
                            <div class="filter-group">
                                <label class="filter-label">Date</label>
                                <input type="date" class="form-control" name="date" id="filterDate" 
                                       value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label class="filter-label">Language</label>
                                <select class="form-select" name="language" id="filterLanguage">
                                    <option value="all">All Languages</option>
                                    <?php foreach($languages as $language): ?>
                                        <option value="<?php echo $language['id']; ?>" 
                                            <?php echo (isset($_GET['language']) && $_GET['language'] == $language['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($language['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label class="filter-label">Genre</label>
                                <select class="form-select" name="genre" id="filterGenre">
                                    <option value="all">All Genres</option>
                                    <?php foreach($genres as $genre): ?>
                                        <option value="<?php echo $genre['id']; ?>" 
                                            <?php echo (isset($_GET['genre']) && $_GET['genre'] == $genre['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($genre['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label class="filter-label">Rating</label>
                                <select class="form-select" name="rating" id="filterRating">
                                    <option value="all">All Ratings</option>
                                    <option value="5" <?php echo (isset($_GET['rating']) && $_GET['rating'] == '5') ? 'selected' : ''; ?>>5 Stars & Above</option>
                                    <option value="4" <?php echo (isset($_GET['rating']) && $_GET['rating'] == '4') ? 'selected' : ''; ?>>4 Stars & Above</option>
                                    <option value="3" <?php echo (isset($_GET['rating']) && $_GET['rating'] == '3') ? 'selected' : ''; ?>>3 Stars & Above</option>
                                    <option value="2" <?php echo (isset($_GET['rating']) && $_GET['rating'] == '2') ? 'selected' : ''; ?>>2 Stars & Above</option>
                                    <option value="1" <?php echo (isset($_GET['rating']) && $_GET['rating'] == '1') ? 'selected' : ''; ?>>1 Star & Above</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-hero w-100 me-2" id="applyFilters">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <?php if(isset($_GET['search']) || isset($_GET['language']) || isset($_GET['genre']) || isset($_GET['rating']) || isset($_GET['date'])): ?>
                                <a href="index.php" class="btn btn-warning">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                
                <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <div class="col-12 mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Showing results for: "<strong><?php echo htmlspecialchars($_GET['search']); ?></strong>"
                            <?php if(count($now_showing_movies) > 0): ?>
                                - Found <?php echo count($now_showing_movies); ?> movie(s)
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Featured Movies -->
        <?php if(count($featured_movies) > 0): ?>
        <section class="mb-5">
            <h2 class="section-title">
                <i class="fas fa-crown me-2" style="color: var(--accent-gold);"></i>
                Featured Movies
            </h2>
            <div class="row">
                <?php foreach($featured_movies as $movie): ?>
                    <div class="col-md-4 mb-4">
                        <div class="movie-card featured-card">
                            <div class="featured-badge">
                                <i class="fas fa-star"></i> Featured
                            </div>
                            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                            <div class="movie-info">
                                <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                <div class="movie-meta">
                                    <span><i class="fas fa-language me-1"></i><?php echo htmlspecialchars($movie['language_name']); ?></span> | 
                                    <span><i class="fas fa-film me-1"></i><?php echo htmlspecialchars($movie['genre_name']); ?></span>
                                </div>
                                <div class="movie-meta">
                                    <span class="rating"><i class="fas fa-star me-1"></i><?php echo $movie['rating']; ?>/5</span> | 
                                    <span><i class="far fa-clock me-1"></i><?php echo htmlspecialchars($movie['duration']); ?></span>
                                </div>
                                <button class="btn btn-book" data-id="<?php echo $movie['id']; ?>">
                                    <i class="fas fa-ticket-alt me-2"></i>Book Now - $<?php echo $movie['ticket_price']; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Now Running Movies (Dynamic from Database) -->
        <section id="movies" class="mb-5">
            <h2 class="section-title">Now Running</h2>
            
            <?php if(count($now_showing_movies) > 0): ?>
                <div class="row" id="nowRunningMovies">
                    <?php foreach($now_showing_movies as $movie): ?>
                        <div class="col-md-4 col-lg-4 mb-4">
                            <div class="movie-card">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                                <div class="movie-info">
                                    <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                    <div class="movie-meta">
                                        <span><i class="fas fa-language me-1"></i><?php echo htmlspecialchars($movie['language_name']); ?></span> | 
                                        <span><i class="fas fa-film me-1"></i><?php echo htmlspecialchars($movie['genre_name']); ?></span>
                                    </div>
                                    <div class="movie-meta">
                                        <span class="rating"><i class="fas fa-star me-1"></i><?php echo $movie['rating']; ?>/5</span> | 
                                        <span><i class="far fa-clock me-1"></i><?php echo htmlspecialchars($movie['duration']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-gold fw-bold">$<?php echo $movie['ticket_price']; ?></span>
                                        <button class="btn btn-book" data-id="<?php echo $movie['id']; ?>">
                                            <i class="fas fa-ticket-alt me-2"></i>Book Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-film fa-4x text-gray mb-4"></i>
                    <h3 class="text-gray">No movies found</h3>
                    <p class="text-gray">Try changing your search criteria or check back later for new releases.</p>
                    <a href="index.php" class="btn btn-hero">
                        <i class="fas fa-redo me-2"></i>Clear Filters
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Coming Soon Movies (Dynamic from Database) -->
        <section class="mb-5">
            <h2 class="section-title">Coming Soon</h2>
            
            <div class="row" id="comingSoonMovies">
                <?php if(count($coming_soon_movies) > 0): ?>
                    <?php foreach($coming_soon_movies as $movie): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="movie-card">
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                                <div class="movie-info">
                                    <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                    <div class="movie-meta">
                                        <span><i class="fas fa-language me-1"></i><?php echo htmlspecialchars($movie['language_name']); ?></span> | 
                                        <span><i class="fas fa-film me-1"></i><?php echo htmlspecialchars($movie['genre_name']); ?></span>
                                    </div>
                                    <div class="movie-meta">
                                        <i class="far fa-calendar-alt me-1"></i>Release: <?php echo date('M d, Y', strtotime($movie['release_date'])); ?>
                                    </div>
                                    <button class="btn btn-notify mt-3" data-id="<?php echo $movie['id']; ?>" data-title="<?php echo htmlspecialchars($movie['title']); ?>">
                                        <i class="far fa-bell me-2"></i>Notify Me
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-gray">No upcoming movies scheduled yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Theatre Highlights -->
        <section id="theatres" class="mb-5">
            <h2 class="section-title">Theatre Highlights</h2>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-volume-up"></i>
                        </div>
                        <h3 class="feature-title">Dolby Atmos Sound</h3>
                        <p>Experience immersive audio with our state-of-the-art Dolby Atmos sound system that places you at the center of the action.</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-tv"></i>
                        </div>
                        <h3 class="feature-title">4K Laser Projection</h3>
                        <p>Watch movies in stunning 4K resolution with our advanced laser projection technology for crystal clear images.</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-couch"></i>
                        </div>
                        <h3 class="feature-title">Premium Recliner Seats</h3>
                        <p>Relax in comfort with our luxurious recliner seats that include adjustable headrests and footrests.</p>
                    </div>
                </div>
            </div>
        </section>

   
    </main>

    <!-- Footer (Dynamic from Database) -->
   <?php include "footer.php"?>

    <!-- Bootstrap JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Include additional JavaScript -->
    <script src="js/particles.js"></script>
    <script src="js/animations.js"></script>
    
    <script>
        // Function to handle slider button actions
        function handleSliderAction(action) {
            switch(action) {
                case 'book_tickets':
                    window.location.href = '#movies';
                    break;
                case 'explore_theatres':
                    window.location.href = '#theatres';
                    break;
                case 'view_offers':
                    window.location.href = '#offers';
                    break;
                default:
                    window.location.href = '#';
            }
        }

        // Notification Modal Functions
        function openNotificationModal(movieId, movieTitle) {
            document.getElementById('notifyMovieId').value = movieId;
            document.getElementById('notificationModal').style.display = 'flex';
            
            // Update modal content with movie title
            const modalContent = document.querySelector('.notification-content p');
            modalContent.innerHTML = `Get notified when tickets for "${movieTitle}" are available:`;
        }

        function closeNotificationModal() {
            document.getElementById('notificationModal').style.display = 'none';
            document.getElementById('notifyForm').reset();
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('notificationToast');
            const toastBody = toast.querySelector('.toast-body');
            
            toastBody.innerHTML = message;
            toast.className = `toast-notification alert alert-${type} show`;
            toast.style.display = 'block';
            
            // Auto hide after 5 seconds
            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('notificationToast');
            toast.style.display = 'none';
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize particles
            if (typeof initParticles === 'function') {
                initParticles();
            }
            
            // Set today's date as default for date filter if not already set
            const dateInput = document.getElementById('filterDate');
            if (dateInput && !dateInput.value) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            }
            
            // Initialize filter functionality
            initFilters();
            
            // Initialize form submissions
            initForms();
            
            // Initialize other animations
            initAnimations();
            
            // Show notification success/error message if exists
            <?php if(isset($notification_success)): ?>
                showToast('<?php echo addslashes($notification_success); ?>', 'success');
            <?php elseif(isset($notification_error)): ?>
                showToast('<?php echo addslashes($notification_error); ?>', 'danger');
            <?php endif; ?>
        });

        // Initialize filter functionality
        function initFilters() {
            const filterForm = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchMovie');
            
            if (!filterForm) return;
            
            // Real-time search with debounce
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        if (this.value.length >= 3 || this.value.length === 0) {
                            filterForm.submit();
                        }
                    }, 500);
                });
            }
            
            // Auto-submit on filter change
            const filterSelects = ['filterLanguage', 'filterGenre', 'filterRating', 'filterDate'];
            filterSelects.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('change', function() {
                        filterForm.submit();
                    });
                }
            });
        }

        // Initialize form submissions
        function initForms() {
            // Contact form
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                    submitBtn.disabled = true;
                    
                    // In a real application, this would be an AJAX call
                    setTimeout(() => {
                        alert('Thank you for your message! We will get back to you within 24 hours.');
                        this.reset();
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 1500);
                });
            }
            
            // Newsletter form
            const newsletterForm = document.getElementById('newsletterForm');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const emailInput = this.querySelector('input[name="email"]');
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    submitBtn.disabled = true;
                    
                    // In a real application, this would be an AJAX call
                    setTimeout(() => {
                        alert('Thank you for subscribing to our newsletter!');
                        this.reset();
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 1000);
                });
            }
        }

        // Initialize animations
        function initAnimations() {
            // Sticky navbar with enhanced effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (navbar) {
                    if (window.scrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                }
                
                // Add parallax effect to hero section
                const heroSection = document.querySelector('.hero-slider');
                if (heroSection) {
                    const scrolled = window.pageYOffset;
                    heroSection.style.transform = `translate3d(0, ${scrolled * 0.05}px, 0)`;
                }
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                        
                        // Close mobile navbar if open
                        const navbarToggler = document.querySelector('.navbar-toggler');
                        const navbarCollapse = document.querySelector('.navbar-collapse');
                        if (navbarToggler && navbarCollapse && navbarCollapse.classList.contains('show')) {
                            navbarToggler.click();
                        }
                    }
                });
            });
            
            // Book Now button click handler
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-book') || e.target.closest('.btn-book')) {
                    const button = e.target.classList.contains('btn-book') ? e.target : e.target.closest('.btn-book');
                    const movieId = button.getAttribute('data-id');
                    
                    // Store movie ID in session for booking page
                    if (typeof sessionStorage !== 'undefined') {
                        sessionStorage.setItem('selectedMovie', movieId);
                    }
                    
                    // Redirect to booking page
                    window.location.href = 'booking.php?id=' + movieId;
                }
                
                // Notify Me button click handler
                if (e.target.classList.contains('btn-notify') || e.target.closest('.btn-notify')) {
                    const button = e.target.classList.contains('btn-notify') ? e.target : e.target.closest('.btn-notify');
                    const movieId = button.getAttribute('data-id');
                    const movieTitle = button.getAttribute('data-title');
                    
                    // Open notification modal
                    openNotificationModal(movieId, movieTitle);
                }
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                const modal = document.getElementById('notificationModal');
                if (e.target === modal) {
                    closeNotificationModal();
                }
            });
        }
    </script>
</body>
</html>