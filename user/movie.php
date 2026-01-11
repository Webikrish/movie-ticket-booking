<?php
session_start();
require_once 'db_connection.php';

// Create database instance
$database = new Database();
$db = $database->getConnection();

// Check if movie ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$movie_id = intval($_GET['id']);
$movie = $db->getMovieById($movie_id);

// If movie not found, redirect to home
if (!$movie) {
    header('Location: index.php');
    exit();
}

// Get related movies (same genre or language)
$related_movies = [];
try {
    $related_query = "SELECT m.*, l.name as language_name, g.name as genre_name 
                      FROM movies m
                      LEFT JOIN languages l ON m.language_id = l.id
                      LEFT JOIN genres g ON m.genre_id = g.id
                      WHERE (m.genre_id = :genre_id OR m.language_id = :language_id) 
                      AND m.id != :movie_id
                      AND m.is_now_showing = 1
                      ORDER BY m.rating DESC
                      LIMIT 4";

    $stmt = $db->conn->prepare($related_query);
    $stmt->execute([
        ':genre_id' => $movie['genre_id'],
        ':language_id' => $movie['language_id'],
        ':movie_id' => $movie_id
    ]);
    $related_movies = $stmt->fetchAll();
} catch(Exception $e) {
    // If error, just show empty related movies
    $related_movies = [];
}

// Get showtimes for the movie
$showtimes = $db->getShowtimesForMovie($movie_id, 7);

// Group showtimes by date for display
$grouped_showtimes = [];
foreach ($showtimes as $show) {
    $date = date('Y-m-d', strtotime($show['show_date']));
    if (!isset($grouped_showtimes[$date])) {
        $grouped_showtimes[$date] = [
            'date_display' => date('D, M d', strtotime($show['show_date'])),
            'shows' => []
        ];
    }
    $grouped_showtimes[$date]['shows'][] = $show;
}

$page_title = $movie['title'] . " - Cinema Krish";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-dark: #0f0f1a;
            --secondary-dark: #1a1a2e;
            --accent-gold: #ffd700;
            --accent-blue: #3a86ff;
            --text-light: #ffffff;
            --text-gray: #b0b0b0;
            --gradient-primary: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 100%);
        }

        body {
            background: var(--gradient-primary);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding-top: 76px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: var(--secondary-dark);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--accent-gold);
            border-radius: 5px;
        }

        /* Movie Header */
        .movie-header {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .movie-backdrop {
            position: relative;
            height: 400px;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), 
                        url('<?php echo htmlspecialchars($movie['poster_url']); ?>');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-end;
            padding: 30px;
        }

        .movie-poster-container {
            position: relative;
            z-index: 2;
            margin-right: 30px;
        }

        .movie-poster {
            width: 300px;
            height: 450px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            border: 2px solid var(--accent-gold);
            transition: transform 0.3s ease;
        }

        .movie-poster:hover {
            transform: translateY(-10px);
        }

        .movie-info-container {
            flex: 1;
            color: var(--text-light);
        }

        .movie-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--accent-gold);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .movie-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }

        .meta-item i {
            color: var(--accent-gold);
        }

        .rating-badge {
            background: linear-gradient(45deg, var(--accent-gold), #ff6b00);
            color: var(--primary-dark);
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .price-tag {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent-gold);
            margin: 20px 0;
        }

        /* Buttons */
        .btn-book {
            background: linear-gradient(45deg, var(--accent-gold), #ff6b00);
            color: var(--primary-dark);
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-book:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3);
        }

        .btn-trailer {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 15px 30px;
            border: 2px solid var(--accent-gold);
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-trailer:hover {
            background: var(--accent-gold);
            color: var(--primary-dark);
        }

        /* Movie Details */
        .movie-details {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .section-title {
            color: var(--accent-gold);
            border-bottom: 2px solid var(--accent-gold);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        /* Cast & Crew */
        .cast-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .cast-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .cast-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 215, 0, 0.1);
        }

        .cast-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid var(--accent-gold);
        }

        /* Showtimes */
        .showtime-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--accent-gold);
        }

        .time-slot {
            background: rgba(255, 215, 0, 0.2);
            color: var(--accent-gold);
            padding: 8px 15px;
            border-radius: 50px;
            margin: 5px;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .time-slot:hover {
            background: var(--accent-gold);
            color: var(--primary-dark);
            border-color: var(--accent-gold);
        }

        /* Related Movies */
        .related-movies .movie-card {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 215, 0, 0.1);
            height: 100%;
        }

        .related-movies .movie-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-gold);
        }

        .related-movies .movie-poster {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .related-movies .movie-info {
            padding: 15px;
        }

        /* Reviews */
        .review-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent-gold);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .review-rating {
            color: var(--accent-gold);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .movie-backdrop {
                height: auto;
                flex-direction: column;
                padding: 20px;
            }

            .movie-poster-container {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .movie-poster {
                width: 200px;
                height: 300px;
            }

            .movie-title {
                font-size: 2rem;
            }

            .price-tag {
                font-size: 2rem;
            }

            .btn-book, .btn-trailer {
                width: 100%;
                margin-bottom: 10px;
            }

            .cast-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .movie-title {
                font-size: 1.5rem;
            }

            .movie-meta {
                flex-direction: column;
                gap: 10px;
            }

            .meta-item {
                width: 100%;
                justify-content: center;
            }

            .cast-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        /* Loading State */
        .loading-placeholder {
            background: linear-gradient(90deg, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.1) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .text-gold {
            color: var(--accent-gold) !important;
        }

        .text-gray {
            color: var(--text-gray) !important;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<?php include 'header.php'; ?>

<!-- Main Content -->
<main class="container py-5">
    <!-- Movie Header Section -->
    <div class="movie-header animate-fade-in-up">
        <div class="movie-backdrop">
            <div class="movie-poster-container d-flex flex-column align-items-center">
                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                     alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                     class="movie-poster img-fluid">
                
                <div class="price-tag mt-3">
                    $<?php echo number_format($movie['ticket_price'], 2); ?>
                </div>
            </div>
            
            <div class="movie-info-container">
                <h1 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h1>
                
                <div class="movie-meta">
                    <div class="meta-item">
                        <i class="fas fa-star"></i>
                        <span><?php echo $movie['rating']; ?>/5</span>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        <span><?php echo htmlspecialchars($movie['duration']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-language"></i>
                        <span><?php echo htmlspecialchars($movie['language_name']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-film"></i>
                        <span><?php echo htmlspecialchars($movie['genre_name']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-calendar-alt"></i>
                        <span><?php echo date('M d, Y', strtotime($movie['release_date'])); ?></span>
                    </div>
                    <?php if($movie['rating'] >= 4.0): ?>
                    <div class="meta-item rating-badge">
                        <i class="fas fa-crown"></i>
                        <span>TOP RATED</span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="movie-buttons d-flex flex-wrap gap-3 mt-4">
                    <?php if(!empty($showtimes)): ?>
                    <button class="btn btn-book" onclick="bookMovie(<?php echo $movie['id']; ?>)">
                        <i class="fas fa-ticket-alt me-2"></i>Book Tickets Now
                    </button>
                    <?php endif; ?>
                    
                    <?php if(!empty($movie['trailer_url'])): ?>
                    <button class="btn btn-trailer" data-bs-toggle="modal" data-bs-target="#trailerModal">
                        <i class="fas fa-play-circle me-2"></i>Watch Trailer
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Movie Details -->
    <div class="row">
        <!-- Left Column - Movie Information -->
        <div class="col-lg-8">
            <!-- Synopsis -->
            <div class="movie-details animate-fade-in-up" style="animation-delay: 0.2s">
                <h3 class="section-title">
                    <i class="fas fa-book-open me-2"></i>Synopsis
                </h3>
                <p class="lead"><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
                
                <!-- Movie Tags -->
                <div class="mt-4">
                    <span class="badge bg-dark text-gold me-2 p-2"><?php echo htmlspecialchars($movie['genre_name']); ?></span>
                    <span class="badge bg-dark text-gold me-2 p-2"><?php echo htmlspecialchars($movie['language_name']); ?></span>
                    <span class="badge bg-dark text-gold me-2 p-2"><?php echo $movie['rating']; ?> Stars</span>
                    <span class="badge bg-dark text-gold me-2 p-2"><?php echo $movie['duration']; ?></span>
                </div>
            </div>

            <!-- Showtimes -->
            <div class="movie-details animate-fade-in-up" style="animation-delay: 0.4s">
                <h3 class="section-title">
                    <i class="fas fa-clock me-2"></i>Showtimes
                </h3>
                
                <?php if(!empty($grouped_showtimes)): ?>
                    <?php foreach($grouped_showtimes as $date => $showday): ?>
                    <div class="showtime-card">
                        <h5 class="text-gold mb-3">
                            <i class="far fa-calendar me-2"></i><?php echo $showday['date_display']; ?>
                        </h5>
                        <div class="time-slots">
                            <?php foreach($showday['shows'] as $show): ?>
                            <span class="time-slot" onclick="bookShowtime(<?php echo $show['id']; ?>, '<?php echo $showday['date_display']; ?>', '<?php echo date('h:i A', strtotime($show['show_time'])); ?>')">
                                <?php echo date('h:i A', strtotime($show['show_time'])); ?>
                                <small class="ms-2">(<?php echo $show['theatre_name']; ?>)</small>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No showtimes available for this movie. Please check back later.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reviews -->
            <div class="movie-details animate-fade-in-up" style="animation-delay: 0.6s">
                <h3 class="section-title">
                    <i class="fas fa-comments me-2"></i>Audience Reviews
                </h3>
                
                <?php
                // Hardcoded reviews for now
                $reviews = [
                    ['user' => 'John D.', 'rating' => 5, 'comment' => 'Amazing movie! The visual effects were stunning and the story kept me engaged throughout.', 'date' => '2 days ago'],
                    ['user' => 'Sarah M.', 'rating' => 4, 'comment' => 'Great performances and direction. A must-watch for all movie lovers.', 'date' => '1 week ago'],
                    ['user' => 'Mike R.', 'rating' => 5, 'comment' => 'One of the best movies I\'ve seen this year. The cinematography is award-worthy.', 'date' => '3 days ago'],
                ];
                ?>
                
                <?php foreach($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div>
                            <strong><?php echo $review['user']; ?></strong>
                            <div class="review-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <small class="text-gray"><?php echo $review['date']; ?></small>
                    </div>
                    <p class="mb-0"><?php echo $review['comment']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Column - Cast & Related Movies -->
        <div class="col-lg-4">
            <!-- Cast & Crew -->
            <div class="movie-details animate-fade-in-up" style="animation-delay: 0.3s">
                <h3 class="section-title">
                    <i class="fas fa-users me-2"></i>Cast & Crew
                </h3>
                <div class="cast-grid">
                    <?php
                    // Hardcoded cast for now
                    $cast = [
                        ['name' => 'Lead Actor', 'role' => 'Lead', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&w=200&h=200&fit=crop&crop=face'],
                        ['name' => 'Supporting Actor', 'role' => 'Supporting', 'image' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&w=200&h=200&fit=crop&crop=face'],
                        ['name' => 'Director', 'role' => 'Director', 'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&w=200&h=200&fit=crop&crop=face'],
                        ['name' => 'Producer', 'role' => 'Producer', 'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?ixlib=rb-4.0.3&w=200&h=200&fit=crop&crop=face'],
                    ];
                    ?>
                    
                    <?php foreach($cast as $person): ?>
                    <div class="cast-card">
                        <img src="<?php echo $person['image']; ?>" 
                             alt="<?php echo $person['name']; ?>" 
                             class="cast-image">
                        <h6 class="mt-2 mb-0"><?php echo $person['name']; ?></h6>
                        <small class="text-gray"><?php echo $person['role']; ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Related Movies -->
            <div class="movie-details animate-fade-in-up" style="animation-delay: 0.5s">
                <h3 class="section-title">
                    <i class="fas fa-film me-2"></i>You May Also Like
                </h3>
                
                <div class="row related-movies">
                    <?php if(count($related_movies) > 0): ?>
                        <?php foreach($related_movies as $related): ?>
                        <div class="col-12 mb-3">
                            <div class="movie-card">
                                <a href="movie.php?id=<?php echo $related['id']; ?>" class="text-decoration-none text-light">
                                    <div class="row g-0">
                                        <div class="col-4">
                                            <img src="<?php echo htmlspecialchars($related['poster_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                                 class="movie-poster" style="width: 100%; height: 120px; object-fit: cover;">
                                        </div>
                                        <div class="col-8">
                                            <div class="movie-info">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($related['title']); ?></h6>
                                                <div class="d-flex align-items-center mb-1">
                                                    <small class="text-gold me-2">
                                                        <i class="fas fa-star"></i> <?php echo $related['rating']; ?>
                                                    </small>
                                                    <small class="text-gray"><?php echo $related['duration']; ?></small>
                                                </div>
                                                <small class="text-gray d-block mb-2">
                                                    <i class="fas fa-film me-1"></i><?php echo htmlspecialchars($related['genre_name']); ?>
                                                </small>
                                                <button class="btn btn-sm btn-book" onclick="window.location.href='movie.php?id=<?php echo $related['id']; ?>'">
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-gray">No related movies found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Movie Stats -->
            <div class="movie-details animate-fade-in-up" style="animation-delay: 0.7s">
                <h3 class="section-title">
                    <i class="fas fa-chart-bar me-2"></i>Movie Stats
                </h3>
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="stat-item">
                            <div class="stat-value text-gold"><?php echo rand(1000, 10000); ?>+</div>
                            <div class="stat-label text-gray">Tickets Sold</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-item">
                            <div class="stat-value text-gold"><?php echo $movie['rating'] * 20; ?>%</div>
                            <div class="stat-label text-gray">Audience Score</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item">
                            <div class="stat-value text-gold"><?php echo $movie['rating']; ?></div>
                            <div class="stat-label text-gray">IMDb Rating</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item">
                            <div class="stat-value text-gold"><?php echo ($movie['rating'] * 20) - 2; ?>%</div>
                            <div class="stat-label text-gray">Rotten Tomatoes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Trailer Modal -->
<?php if(!empty($movie['trailer_url'])): ?>
<div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-gold">
                    <i class="fas fa-play-circle me-2"></i>
                    <?php echo htmlspecialchars($movie['title']); ?> - Trailer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <?php 
                    // Convert YouTube URL to embed format if needed
                    $trailer_url = $movie['trailer_url'];
                    if (strpos($trailer_url, 'youtube.com') !== false || strpos($trailer_url, 'youtu.be') !== false) {
                        // Extract video ID
                        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $trailer_url, $matches);
                        if (isset($matches[1])) {
                            $trailer_url = 'https://www.youtube.com/embed/' . $matches[1];
                        }
                    }
                    ?>
                    <iframe src="<?php echo htmlspecialchars($trailer_url); ?>" 
                            title="Movie Trailer" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen 
                            style="border: none;">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Footer -->
<?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Movie booking function
    function bookMovie(movieId) {
        window.location.href = 'booking.php?movie=' + movieId;
    }

    // Showtime booking
    function bookShowtime(showId, date, time) {
        if(confirm(`Book show on ${date} at ${time}?`)) {
            window.location.href = 'booking.php?show=' + showId;
        }
    }

    // Add active class to clicked time slot
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.addEventListener('click', function() {
            // Remove active class from all time slots
            document.querySelectorAll('.time-slot').forEach(s => {
                s.classList.remove('active');
                s.style.background = 'rgba(255, 215, 0, 0.2)';
                s.style.color = 'var(--accent-gold)';
            });
            
            // Add active class to clicked time slot
            this.classList.add('active');
            this.style.background = 'var(--accent-gold)';
            this.style.color = 'var(--primary-dark)';
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add loading animation
    window.addEventListener('load', function() {
        document.querySelectorAll('.animate-fade-in-up').forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        });
    });

    // Share movie function
    function shareMovie() {
        const url = window.location.href;
        const title = "<?php echo htmlspecialchars($movie['title']); ?>";
        
        if (navigator.share) {
            navigator.share({
                title: title,
                text: 'Check out this movie on CinemaKrish!',
                url: url
            });
        } else {
            // Fallback for browsers that don't support Web Share API
            navigator.clipboard.writeText(url)
                .then(() => alert('Link copied to clipboard!'))
                .catch(err => console.error('Error copying text: ', err));
        }
    }
</script>
</body>
</html>