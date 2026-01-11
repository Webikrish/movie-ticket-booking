<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if(!isset($_GET['id'])) {
    header('Location: movies.php');
    exit();
}

$movie_id = (int)$_GET['id'];
$db = new Database();

// Get movie details
$db->query("SELECT * FROM movies WHERE id = ?");
$db->bind(1, $movie_id);
$movie = $db->single();

if(!$movie) {
    header('Location: movies.php');
    exit();
}

// Get theatres showing this movie
$db->query("SELECT DISTINCT t.* FROM theatres t
            JOIN shows s ON t.id = s.theatre_id
            WHERE s.movie_id = ? AND s.show_date >= CURDATE() AND s.status = 'active'
            ORDER BY t.city, t.name");
$db->bind(1, $movie_id);
$theatres = $db->resultSet();

// Get cast as array
$cast = !empty($movie->cast) ? explode(',', $movie->cast) : [];

// Get reviews
$db->query("SELECT r.*, u.name as user_name FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.movie_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC LIMIT 5");
$db->bind(1, $movie_id);
$reviews = $db->resultSet();

// Calculate average rating
$db->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews 
            WHERE movie_id = ? AND status = 'approved'");
$db->bind(1, $movie_id);
$rating_stats = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $movie->title; ?> - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .movie-header {
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), 
                        url('../assets/uploads/movies/<?php echo $movie->banner_image ?: $movie->poster_image; ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
        }
        
        .movie-poster-large {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .trailer-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 10px;
        }
        
        .trailer-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .cast-card {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            transition: transform 0.3s;
        }
        
        .cast-card:hover {
            transform: translateY(-5px);
        }
        
        .cast-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid var(--primary-color);
        }
        
        .showtime-tabs .nav-link {
            margin: 0 5px;
            border-radius: 5px;
        }
        
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .movie-header {
                padding: 40px 0;
            }
            
            .movie-poster-large {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Movie Header -->
    <div class="movie-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-5">
                    <div class="movie-poster-large">
                        <img src="../assets/uploads/movies/<?php echo $movie->poster_image; ?>" 
                             alt="<?php echo $movie->title; ?>" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-8 col-md-7">
                    <div class="movie-info">
                        <h1 class="display-4 fw-bold mb-3"><?php echo $movie->title; ?></h1>
                        
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-primary fs-6"><?php echo $movie->language; ?></span>
                            <span class="badge bg-success fs-6"><?php echo $movie->genre; ?></span>
                            <span class="badge bg-warning fs-6">
                                <i class="bi bi-star-fill"></i> <?php echo $movie->rating; ?>/10
                            </span>
                            <span class="badge bg-info fs-6"><?php echo $movie->duration; ?> min</span>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="mb-2">Director: <span class="text-light"><?php echo $movie->director; ?></span></h5>
                            <p class="lead"><?php echo $movie->description; ?></p>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <?php if($movie->status == 'now_showing'): ?>
                            <a href="#showtimes" class="btn btn-primary btn-lg">
                                <i class="bi bi-ticket-perforated"></i> Book Tickets
                            </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($movie->trailer_url)): ?>
                            <button type="button" class="btn btn-outline-light btn-lg" data-bs-toggle="modal" data-bs-target="#trailerModal">
                                <i class="bi bi-play-circle"></i> Watch Trailer
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container py-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Cast & Crew -->
                <?php if(!empty($cast)): ?>
                <section class="mb-5">
                    <h3 class="mb-4">Cast & Crew</h3>
                    <div class="row g-3">
                        <?php foreach($cast as $actor): ?>
                        <div class="col-6 col-sm-4 col-md-3">
                            <div class="cast-card">
                                <div class="cast-avatar-placeholder">
                                    <i class="bi bi-person-circle display-5 text-muted"></i>
                                </div>
                                <h6 class="mb-0"><?php echo trim($actor); ?></h6>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
                
                <!-- Showtimes -->
                <section id="showtimes" class="mb-5">
                    <h3 class="mb-4">Showtimes</h3>
                    
                    <?php if(empty($theatres)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No shows available for this movie.
                    </div>
                    <?php else: ?>
                    <div class="accordion" id="theatreAccordion">
                        <?php foreach($theatres as $index => $theatre): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" 
                                        type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#theatre<?php echo $theatre->id; ?>">
                                    <div class="d-flex justify-content-between w-100 me-3">
                                        <div>
                                            <strong><?php echo $theatre->name; ?></strong>
                                            <small class="text-muted ms-2"><?php echo $theatre->city; ?></small>
                                        </div>
                                        <div>
                                            <small class="text-muted"><?php echo $theatre->total_screens; ?> screens</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="theatre<?php echo $theatre->id; ?>" 
                                 class="accordion-collapse collapse <?php echo $index == 0 ? 'show' : ''; ?>" 
                                 data-bs-parent="#theatreAccordion">
                                <div class="accordion-body">
                                    <?php
                                    // Get showtimes for this theatre and movie
                                    $db->query("SELECT s.* FROM shows s
                                               WHERE s.movie_id = ? AND s.theatre_id = ? 
                                               AND s.show_date >= CURDATE() AND s.status = 'active'
                                               ORDER BY s.show_date, s.show_time");
                                    $db->bind(1, $movie_id);
                                    $db->bind(2, $theatre->id);
                                    $showtimes = $db->resultSet();
                                    
                                    // Group by date
                                    $shows_by_date = [];
                                    foreach($showtimes as $show) {
                                        $date = $show->show_date;
                                        if(!isset($shows_by_date[$date])) {
                                            $shows_by_date[$date] = [];
                                        }
                                        $shows_by_date[$date][] = $show;
                                    }
                                    ?>
                                    
                                    <div class="showtime-tabs">
                                        <ul class="nav nav-pills mb-3" id="dateTabs<?php echo $theatre->id; ?>" role="tablist">
                                            <?php $dateIndex = 0; ?>
                                            <?php foreach($shows_by_date as $date => $shows): ?>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link <?php echo $dateIndex == 0 ? 'active' : ''; ?>" 
                                                        id="date-<?php echo $theatre->id . '-' . $dateIndex; ?>" 
                                                        data-bs-toggle="tab" 
                                                        data-bs-target="#date-<?php echo $theatre->id . '-' . $dateIndex; ?>-content" 
                                                        type="button">
                                                    <?php echo date('D, M d', strtotime($date)); ?>
                                                </button>
                                            </li>
                                            <?php $dateIndex++; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                        
                                        <div class="tab-content">
                                            <?php $dateIndex = 0; ?>
                                            <?php foreach($shows_by_date as $date => $shows): ?>
                                            <div class="tab-pane fade <?php echo $dateIndex == 0 ? 'show active' : ''; ?>" 
                                                 id="date-<?php echo $theatre->id . '-' . $dateIndex; ?>-content">
                                                <div class="row g-2">
                                                    <?php foreach($shows as $show): ?>
                                                    <div class="col-6 col-md-4 col-lg-3">
                                                        <a href="seat-selection.php?show_id=<?php echo $show->id; ?>" 
                                                           class="btn btn-outline-primary w-100">
                                                            <?php echo date('h:i A', strtotime($show->show_time)); ?>
                                                            <br>
                                                            <small>₹<?php echo $show->ticket_price; ?></small>
                                                        </a>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php $dateIndex++; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>
                
                <!-- Reviews -->
                <section class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Reviews & Ratings</h3>
                        <div class="text-end">
                            <div class="display-6 fw-bold text-warning">
                                <?php echo number_format($rating_stats->avg_rating, 1); ?>/10
                            </div>
                            <small class="text-muted">Based on <?php echo $rating_stats->total_reviews; ?> reviews</small>
                        </div>
                    </div>
                    
                    <?php if(empty($reviews)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No reviews yet. Be the first to review!
                    </div>
                    <?php else: ?>
                    <div class="row g-4">
                        <?php foreach($reviews as $review): ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="card-title mb-0"><?php echo $review->user_name; ?></h6>
                                        <small class="text-muted"><?php echo formatDate($review->created_at); ?></small>
                                    </div>
                                    <div class="rating-stars mb-2">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $review->rating ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                        <span class="ms-2"><?php echo $review->rating; ?>/5</span>
                                    </div>
                                    <p class="card-text"><?php echo $review->comment; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Movie Stats -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Movie Details</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong>Release Date:</strong> 
                                <?php echo formatDate($movie->release_date); ?>
                            </li>
                            <li class="mb-2">
                                <strong>Duration:</strong> 
                                <?php echo floor($movie->duration / 60); ?>h <?php echo $movie->duration % 60; ?>m
                            </li>
                            <li class="mb-2">
                                <strong>Language:</strong> <?php echo $movie->language; ?>
                            </li>
                            <li class="mb-2">
                                <strong>Genre:</strong> <?php echo $movie->genre; ?>
                            </li>
                            <li>
                                <strong>Director:</strong> <?php echo $movie->director; ?>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Facilities -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Available at Theatres With</h5>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="text-center p-2 border rounded">
                                    <i class="bi bi-cup-hot fs-4 text-primary"></i>
                                    <small class="d-block">Food Court</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 border rounded">
                                    <i class="bi bi-car-front fs-4 text-primary"></i>
                                    <small class="d-block">Parking</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 border rounded">
                                    <i class="bi bi-snow fs-4 text-primary"></i>
                                    <small class="d-block">AC</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 border rounded">
                                    <i class="bi bi-wheelchair fs-4 text-primary"></i>
                                    <small class="d-block">Wheelchair</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Similar Movies -->
                <?php
                // Get similar movies
                $db->query("SELECT * FROM movies 
                           WHERE genre LIKE ? AND id != ? AND status = 'now_showing'
                           ORDER BY rating DESC LIMIT 3");
                $db->bind(1, "%" . explode(',', $movie->genre)[0] . "%");
                $db->bind(2, $movie_id);
                $similar_movies = $db->resultSet();
                
                if(!empty($similar_movies)):
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Similar Movies</h5>
                        <div class="row g-3">
                            <?php foreach($similar_movies as $similar): ?>
                            <div class="col-12">
                                <div class="d-flex">
                                    <div style="width: 60px; flex-shrink: 0;">
                                        <img src="../assets/uploads/movies/<?php echo $similar->poster_image; ?>" 
                                             alt="<?php echo $similar->title; ?>" 
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-1">
                                            <a href="movie-details.php?id=<?php echo $similar->id; ?>" 
                                               class="text-decoration-none">
                                                <?php echo $similar->title; ?>
                                            </a>
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            <small class="text-warning me-2">
                                                <i class="bi bi-star-fill"></i> <?php echo $similar->rating; ?>
                                            </small>
                                            <small class="text-muted"><?php echo $similar->language; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Trailer Modal -->
    <?php if(!empty($movie->trailer_url)): ?>
    <div class="modal fade" id="trailerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $movie->title; ?> - Trailer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="trailer-container">
                        <iframe src="https://www.youtube.com/embed/<?php echo getYoutubeId($movie->trailer_url); ?>" 
                                frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Helper function to get YouTube ID
        function getYoutubeId(url) {
            const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
            return match ? match[1] : null;
        }
        
        // Auto-play trailer when modal opens
        const trailerModal = document.getElementById('trailerModal');
        if(trailerModal) {
            trailerModal.addEventListener('shown.bs.modal', function() {
                const iframe = this.querySelector('iframe');
                iframe.src = iframe.src + '&autoplay=1';
            });
            
            trailerModal.addEventListener('hidden.bs.modal', function() {
                const iframe = this.querySelector('iframe');
                iframe.src = iframe.src.replace('&autoplay=1', '');
            });
        }
        
        // Smooth scroll to showtimes
        document.querySelectorAll('a[href="#showtimes"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('#showtimes').scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>