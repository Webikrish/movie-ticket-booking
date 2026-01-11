<?php
// booking.php
require_once 'session_manager.php';
require_once 'db_connection.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['error_message'] = 'Please login to book tickets';
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$currentUser = getCurrentUser();

// Get movie ID from URL
$movieId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$movieId) {
    header('Location: index.php');
    exit();
}

// Get movie details
try {
    $movieQuery = "SELECT m.*, g.name as genre_name, l.name as language_name 
                   FROM movies m 
                   LEFT JOIN genres g ON m.genre_id = g.id 
                   LEFT JOIN languages l ON m.language_id = l.id 
                   WHERE m.id = :id";
    $movieStmt = $db->prepare($movieQuery);
    $movieStmt->bindParam(':id', $movieId, PDO::PARAM_INT);
    $movieStmt->execute();
    $movie = $movieStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching movie: " . $e->getMessage());
    $movie = null;
}

if (!$movie) {
    $_SESSION['error_message'] = 'Movie not found';
    header('Location: index.php');
    exit();
}

// Handle show selection
$selectedShowId = isset($_GET['show_id']) ? intval($_GET['show_id']) : 0;
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d', strtotime('+1 day'));

// Get available shows for the movie - FIXED QUERY
$shows = [];
try {
    $query = "SELECT s.*, t.name as theatre_name, t.location, t.city, 
                     m.title as movie_title, t.total_screens
              FROM shows s
              JOIN theatres t ON s.theatre_id = t.id
              JOIN movies m ON s.movie_id = m.id
              WHERE s.movie_id = :movie_id 
              AND s.show_date = :show_date
              AND s.available_seats > 0
              AND s.is_active = 1
              ORDER BY t.name, s.show_time";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
    $stmt->bindParam(':show_date', $selectedDate);
    $stmt->execute();
    $shows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching shows: " . $e->getMessage());
    $shows = [];
}

// If show is selected, get show details and redirect to seat selection
if ($selectedShowId > 0) {
    // Get show details
    try {
        $showQuery = "SELECT s.*, t.name as theatre_name, t.location, t.city, 
                             m.title as movie_title, t.total_screens
                      FROM shows s
                      JOIN theatres t ON s.theatre_id = t.id
                      JOIN movies m ON s.movie_id = m.id
                      WHERE s.id = :show_id 
                      AND s.is_active = 1";
        
        $showStmt = $db->prepare($showQuery);
        $showStmt->bindParam(':show_id', $selectedShowId, PDO::PARAM_INT);
        $showStmt->execute();
        $selectedShow = $showStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$selectedShow || $selectedShow['available_seats'] <= 0) {
            $_SESSION['error_message'] = 'Show not found or no seats available';
            header("Location: booking.php?id=$movieId&date=$selectedDate");
            exit();
        }
        
        // Get booked seats for this show
        $bookedSeats = [];
        try {
            $bookedQuery = "SELECT seat_numbers FROM bookings WHERE show_id = :show_id";
            $bookedStmt = $db->prepare($bookedQuery);
            $bookedStmt->bindParam(':show_id', $selectedShowId, PDO::PARAM_INT);
            $bookedStmt->execute();
            $bookedResults = $bookedStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($bookedResults as $booking) {
                if (!empty($booking['seat_numbers'])) {
                    $seats = explode(',', $booking['seat_numbers']);
                    $bookedSeats = array_merge($bookedSeats, $seats);
                }
            }
            $bookedSeats = array_unique($bookedSeats);
        } catch (PDOException $e) {
            error_log("Error fetching booked seats: " . $e->getMessage());
        }
        
        // Store show data in session for seat selection
        $_SESSION['selected_show'] = [
            'show_id' => $selectedShowId,
            'show_date' => $selectedShow['show_date'],
            'show_time' => $selectedShow['show_time'],
            'theatre_name' => $selectedShow['theatre_name'],
            'location' => $selectedShow['location'],
            'city' => $selectedShow['city'],
            'screen_number' => $selectedShow['screen_number'],
            'ticket_price' => $selectedShow['ticket_price'],
            'available_seats' => $selectedShow['available_seats'],
            'total_seats' => $selectedShow['total_seats'],
            'movie_id' => $movieId,
            'movie_title' => $movie['title'],
            'movie_poster' => $movie['poster_url'] ?? 'images/default-movie.jpg',
            'booked_seats' => $bookedSeats
        ];
        
        // Redirect to seat selection page
        header('Location: seat_bookings.php');
        exit();
        
    } catch (PDOException $e) {
        error_log("Error fetching show: " . $e->getMessage());
        $_SESSION['error_message'] = 'Error loading show details';
        header("Location: booking.php?id=$movieId&date=$selectedDate");
        exit();
    }
}

// Handle seat selection form submission (if coming from seat_bookings.php with POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_seats'])) {
    if (!isset($_SESSION['selected_show'])) {
        $_SESSION['error_message'] = 'Session expired. Please select show again.';
        header("Location: booking.php?id=$movieId");
        exit();
    }
    
    $selectedSeats = isset($_POST['seats']) ? $_POST['seats'] : [];
    
    if (empty($selectedSeats)) {
        $_SESSION['error_message'] = 'Please select at least one seat';
        header("Location: seat_bookings.php");
        exit();
    } else {
        // Store selected seats in session
        $_SESSION['selected_show']['selected_seats'] = $selectedSeats;
        $_SESSION['selected_show']['total_seats'] = count($selectedSeats);
        $_SESSION['selected_show']['convenience_fee'] = count($selectedSeats) * 1.5;
        $_SESSION['selected_show']['total_amount'] = ($_SESSION['selected_show']['ticket_price'] * count($selectedSeats)) + $_SESSION['selected_show']['convenience_fee'];
        
        // Redirect to customer details page
        header('Location: customer_details.php');
        exit();
    }
}

// Get upcoming dates (next 7 days)
$upcomingDates = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("+$i day"));
    $upcomingDates[] = $date;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tickets - <?php echo htmlspecialchars($movie['title']); ?> | CinemaKrish</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Oswald:wght@500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-dark: #0a0a0a;
            --secondary-dark: #1a1a1a;
            --accent-red: #d32f2f;
            --accent-gold: #ffc107;
            --text-light: #f8f9fa;
            --text-gray: #adb5bd;
        }

        body {
            background: var(--primary-dark);
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            padding-top: 80px;
        }

        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .movie-header {
            background: var(--secondary-dark);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(211, 47, 47, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .movie-poster-large {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        .movie-title {
            font-family: 'Oswald', sans-serif;
            font-size: 2.5rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .movie-meta {
            color: var(--text-gray);
            margin-bottom: 20px;
        }

        .movie-meta i {
            color: var(--accent-red);
            margin-right: 5px;
        }

        .booking-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .booking-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: var(--secondary-dark);
            z-index: 1;
        }

        .step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--secondary-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            font-size: 1.2rem;
            border: 3px solid var(--secondary-dark);
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--accent-red);
            border-color: var(--accent-red);
            color: white;
            transform: scale(1.1);
        }

        .step.completed .step-number {
            background: var(--accent-gold);
            border-color: var(--accent-gold);
            color: var(--primary-dark);
        }

        .step-label {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .step.active .step-label {
            color: var(--accent-red);
            font-weight: 600;
        }

        .booking-section {
            background: var(--secondary-dark);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            color: var(--accent-gold);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-red);
        }

        .date-selector {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .date-item {
            min-width: 80px;
            padding: 15px 10px;
            text-align: center;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            color: var(--text-light);
        }

        .date-item:hover {
            background: rgba(211, 47, 47, 0.1);
            transform: translateY(-2px);
            color: var(--text-light);
            text-decoration: none;
        }

        .date-item.active {
            background: var(--accent-red);
            border-color: var(--accent-red);
            color: white;
        }

        .date-day {
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .date-number {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .date-month {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .theatre-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .theatre-card:hover {
            border-color: var(--accent-red);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .theatre-name {
            color: var(--text-light);
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .theatre-location {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .show-times {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .show-time {
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            color: var(--text-light);
            display: inline-block;
            min-width: 100px;
        }

        .show-time:hover {
            background: rgba(211, 47, 47, 0.1);
            border-color: var(--accent-red);
            color: var(--text-light);
            text-decoration: none;
            transform: translateY(-2px);
        }

        .show-time.active {
            background: var(--accent-red);
            border-color: var(--accent-red);
            color: white;
        }

        .show-time.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            text-decoration: none;
            color: var(--text-gray);
        }

        .time-hour {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .time-price {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 3px;
        }

        .available-seats {
            font-size: 0.8rem;
            color: #4CAF50;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .screen-info {
            font-size: 0.8rem;
            color: var(--accent-gold);
            margin-top: 3px;
        }

        .no-shows {
            text-align: center;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            margin-top: 20px;
        }

        .no-shows i {
            font-size: 3rem;
            color: var(--text-gray);
            margin-bottom: 15px;
        }

        .btn-continue {
            background: linear-gradient(135deg, var(--accent-red) 0%, #9a0007 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
            color: white;
        }

        .btn-continue:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .booking-steps {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .booking-steps::before {
                display: none;
            }

            .step {
                width: 100%;
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .step-number {
                margin: 0;
            }

            .movie-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .booking-container {
                padding: 10px;
            }

            .movie-header {
                padding: 20px;
            }

            .booking-section {
                padding: 20px;
            }

            .date-selector {
                padding-bottom: 15px;
            }

            .date-item {
                min-width: 70px;
                padding: 10px 5px;
            }

            .movie-poster-large {
                height: 280px;
            }
        }

        @media (max-width: 576px) {
            .movie-title {
                font-size: 1.8rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .show-times {
                justify-content: center;
            }

            .show-time {
                min-width: 90px;
                padding: 8px 12px;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Main Booking Content -->
    <div class="booking-container">
        <!-- Movie Header -->
        <div class="movie-header">
            <div class="row">
                <div class="col-md-3 mb-4 mb-md-0">
                    <img src="<?php echo htmlspecialchars($movie['poster_url'] ?? 'images/default-movie.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                         class="movie-poster-large">
                </div>
                <div class="col-md-9">
                    <h1 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h1>
                    
                    <div class="movie-meta">
                        <span class="me-3">
                            <i class="fas fa-star"></i> <?php echo $movie['rating'] ?? 'N/A'; ?>/5
                        </span>
                        <span class="me-3">
                            <i class="far fa-clock"></i> <?php echo htmlspecialchars($movie['duration'] ?? 'N/A'); ?>
                        </span>
                        <span class="me-3">
                            <i class="fas fa-language"></i> <?php echo htmlspecialchars($movie['language_name'] ?? 'English'); ?>
                        </span>
                        <span class="me-3">
                            <i class="fas fa-film"></i> <?php echo htmlspecialchars($movie['genre_name'] ?? 'Action'); ?>
                        </span>
                    </div>
                    
                    <p class="text-gray"><?php echo htmlspecialchars($movie['description'] ?? 'No description available'); ?></p>
                    
                    <div class="mt-4">
                        <span class="badge bg-warning text-dark p-2">
                            <i class="fas fa-ticket-alt me-1"></i> 
                            <?php echo $movie['certificate'] ?? 'UA'; ?>
                        </span>
                        <span class="badge bg-info text-dark p-2 ms-2">
                            <i class="fas fa-dollar-sign me-1"></i> 
                            Starting from $<?php echo $movie['ticket_price'] ?? '12.99'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Steps -->
        <div class="booking-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Select Show</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Choose Seats</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Customer Details</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Payment</div>
            </div>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Step 1: Select Show -->
        <div class="booking-section">
            <h2 class="section-title">Select Date & Show</h2>
            
            <!-- Date Selector -->
            <div class="mb-4">
                <h5 class="text-light mb-3">Select Date:</h5>
                <div class="date-selector">
                    <?php foreach ($upcomingDates as $date): 
                        $day = date('D', strtotime($date));
                        $dayNumber = date('d', strtotime($date));
                        $month = date('M', strtotime($date));
                        $isActive = $date == $selectedDate;
                    ?>
                        <a href="booking.php?id=<?php echo $movieId; ?>&date=<?php echo $date; ?>" 
                           class="date-item <?php echo $isActive ? 'active' : ''; ?>">
                            <div class="date-day"><?php echo $day; ?></div>
                            <div class="date-number"><?php echo $dayNumber; ?></div>
                            <div class="date-month"><?php echo $month; ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shows for Selected Date -->
            <div class="mt-4">
                <h5 class="text-light mb-3">Available Shows for <?php echo date('l, F j, Y', strtotime($selectedDate)); ?>:</h5>
                
                <?php if (empty($shows)): ?>
                    <div class="no-shows">
                        <i class="fas fa-film"></i>
                        <h4 class="text-gray">No shows available for this date</h4>
                        <p class="text-gray">Please select another date or check back later</p>
                        
                        <?php 
                        // Try to find shows on other dates
                        $otherDatesShows = [];
                        try {
                            $otherDatesQuery = "SELECT DISTINCT show_date FROM shows 
                                               WHERE movie_id = :movie_id 
                                               AND available_seats > 0
                                               AND show_date >= CURDATE()
                                               ORDER BY show_date LIMIT 3";
                            $otherDatesStmt = $db->prepare($otherDatesQuery);
                            $otherDatesStmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
                            $otherDatesStmt->execute();
                            $otherDatesShows = $otherDatesStmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            error_log("Error fetching other dates: " . $e->getMessage());
                        }
                        
                        if (!empty($otherDatesShows)): ?>
                            <div class="mt-4">
                                <p class="text-light">Try these dates instead:</p>
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    <?php foreach ($otherDatesShows as $otherDate): 
                                        $displayDate = date('M j, Y', strtotime($otherDate['show_date']));
                                    ?>
                                        <a href="booking.php?id=<?php echo $movieId; ?>&date=<?php echo $otherDate['show_date']; ?>" 
                                           class="btn btn-outline-warning btn-sm">
                                            <?php echo $displayDate; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php
                    // Group shows by theatre
                    $theatreShows = [];
                    foreach ($shows as $show) {
                        $theatreId = $show['theatre_id'];
                        if (!isset($theatreShows[$theatreId])) {
                            $theatreShows[$theatreId] = [
                                'theatre' => [
                                    'name' => $show['theatre_name'],
                                    'location' => $show['location'],
                                    'city' => $show['city']
                                ],
                                'shows' => []
                            ];
                        }
                        $theatreShows[$theatreId]['shows'][] = $show;
                    }
                    ?>
                    
                    <?php foreach ($theatreShows as $theatreId => $theatreData): ?>
                        <div class="theatre-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="theatre-name">
                                        <?php echo htmlspecialchars($theatreData['theatre']['name']); ?>
                                    </div>
                                    <div class="theatre-location">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($theatreData['theatre']['location']); ?>, 
                                        <?php echo htmlspecialchars($theatreData['theatre']['city']); ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-film me-1"></i>
                                        <?php echo count($theatreData['shows']); ?> show(s)
                                    </span>
                                </div>
                            </div>
                            
                            <div class="show-times">
                                <?php 
                                foreach ($theatreData['shows'] as $show): 
                                    $time = date('h:i A', strtotime($show['show_time']));
                                    $availableSeats = $show['available_seats'];
                                    $totalSeats = $show['total_seats'];
                                    $seatPercentage = ($availableSeats / $totalSeats) * 100;
                                    
                                    // Determine seat availability status
                                    if ($availableSeats <= 0) {
                                        $statusClass = 'disabled';
                                        $statusText = 'Sold Out';
                                    } elseif ($availableSeats <= 20) {
                                        $statusClass = 'warning';
                                        $statusText = 'Few Seats';
                                    } else {
                                        $statusClass = 'available';
                                        $statusText = 'Available';
                                    }
                                ?>
                                    <a href="booking.php?id=<?php echo $movieId; ?>&date=<?php echo $selectedDate; ?>&show_id=<?php echo $show['id']; ?>" 
                                       class="show-time <?php echo $availableSeats <= 0 ? 'disabled' : ''; ?>"
                                       <?php echo $availableSeats <= 0 ? 'style="cursor: not-allowed; opacity: 0.5;" onclick="return false;"' : ''; ?>
                                       title="Screen <?php echo $show['screen_number']; ?>">
                                        <div class="time-hour"><?php echo $time; ?></div>
                                        <div class="time-price">$<?php echo number_format($show['ticket_price'], 2); ?></div>
                                        <div class="screen-info">
                                            <i class="fas fa-film"></i> Screen <?php echo $show['screen_number']; ?>
                                        </div>
                                        <?php if ($availableSeats > 0): ?>
                                            <div class="available-seats">
                                                <i class="fas fa-chair"></i> 
                                                <?php echo $availableSeats; ?> seats
                                                <?php if ($availableSeats <= 20): ?>
                                                    <span class="badge bg-warning text-dark ms-1">Few</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <small class="d-block text-danger mt-1">
                                                <i class="fas fa-times-circle"></i> Sold Out
                                            </small>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Quick Stats -->
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <div class="text-gray">
                                    <i class="fas fa-theater-masks me-1"></i>
                                    <?php echo count($theatreShows); ?> Theatres
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-gray">
                                    <i class="fas fa-ticket-alt me-1"></i>
                                    <?php echo count($shows); ?> Shows
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <?php 
                                $totalAvailableSeats = array_sum(array_column($shows, 'available_seats'));
                                ?>
                                <div class="text-gray">
                                    <i class="fas fa-chair me-1"></i>
                                    <?php echo $totalAvailableSeats; ?> Seats Available
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Movie Info Footer -->
        <div class="mt-4 text-center">
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <div>
                    <i class="fas fa-language me-1 text-warning"></i>
                    <span class="text-gray">Language: <?php echo htmlspecialchars($movie['language_name'] ?? 'English'); ?></span>
                </div>
                <div>
                    <i class="fas fa-film me-1 text-warning"></i>
                    <span class="text-gray">Genre: <?php echo htmlspecialchars($movie['genre_name'] ?? 'Action'); ?></span>
                </div>
                <div>
                    <i class="fas fa-certificate me-1 text-warning"></i>
                    <span class="text-gray">Certificate: <?php echo $movie['certificate'] ?? 'UA'; ?></span>
                </div>
                <div>
                    <i class="far fa-clock me-1 text-warning"></i>
                    <span class="text-gray">Duration: <?php echo htmlspecialchars($movie['duration'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle show time clicks with loading state
            const showTimeLinks = document.querySelectorAll('.show-time:not(.disabled)');
            showTimeLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!this.classList.contains('disabled')) {
                        // Show loading state
                        const originalHTML = this.innerHTML;
                        this.innerHTML = '<div class="loading"></div> Loading...';
                        this.style.pointerEvents = 'none';
                        
                        // Revert after 5 seconds if still on page
                        setTimeout(() => {
                            this.innerHTML = originalHTML;
                            this.style.pointerEvents = 'auto';
                        }, 5000);
                    }
                });
            });
            
            // Handle date selector clicks
            const dateItems = document.querySelectorAll('.date-item');
            dateItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (!this.classList.contains('active')) {
                        // Show loading state
                        const originalHTML = this.innerHTML;
                        this.innerHTML = '<div class="loading"></div>';
                        this.style.pointerEvents = 'none';
                        
                        // Also disable all other date items temporarily
                        dateItems.forEach(d => {
                            if (d !== this) {
                                d.style.opacity = '0.5';
                                d.style.pointerEvents = 'none';
                            }
                        });
                    }
                });
            });
            
            // Auto-scroll to shows section when date changes
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('date')) {
                setTimeout(() => {
                    document.querySelector('.booking-section')?.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            }
            
            // Show confirmation for low availability shows
            const lowSeatShows = document.querySelectorAll('.show-time .badge.bg-warning');
            lowSeatShows.forEach(badge => {
                const showTime = badge.closest('.show-time');
                if (showTime) {
                    showTime.addEventListener('click', function(e) {
                        if (this.classList.contains('disabled')) return;
                        
                        const seatText = this.querySelector('.available-seats')?.textContent || '';
                        if (seatText.includes('Few')) {
                            if (!confirm('Only a few seats left! Book now?')) {
                                e.preventDefault();
                            }
                        }
                    });
                }
            });
            
            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                // Left/Right arrows for date navigation
                if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                    e.preventDefault();
                    const activeDate = document.querySelector('.date-item.active');
                    if (activeDate) {
                        const dateItemsArray = Array.from(dateItems);
                        const currentIndex = dateItemsArray.indexOf(activeDate);
                        let newIndex;
                        
                        if (e.key === 'ArrowLeft' && currentIndex > 0) {
                            newIndex = currentIndex - 1;
                        } else if (e.key === 'ArrowRight' && currentIndex < dateItemsArray.length - 1) {
                            newIndex = currentIndex + 1;
                        }
                        
                        if (newIndex !== undefined) {
                            dateItemsArray[newIndex].click();
                        }
                    }
                }
                
                // Escape to refresh page
                if (e.key === 'Escape') {
                    location.reload();
                }
            });
        });
    </script>
</body>
</html>