<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is admin
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] != 1
) {
    header('Location: login.php');
    exit();
}

// Handle actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Handle delete
if ($action == 'delete' && $id) {
    if ($database->deleteMovie($id)) {
        header('Location: admin_movies.php?success=Movie deleted successfully');
        exit();
    } else {
        header('Location: admin_movies.php?error=Failed to delete movie');
        exit();
    }
}

// Get all movies
$movies = $database->getAllMovies();
$movieStats = $database->getMovieStatistics();
$languages = $database->getLanguages();
$genres = $database->getGenres();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Management - CinemaKrish Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        <?php include 'admin_styles.css'; ?>
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
            padding: 15px;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }
        
        .status-inactive {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .btn-action {
            padding: 5px 12px;
            margin: 0 3px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        
        .movie-poster {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navbar -->
        <?php include 'admin_navbar.php'; ?>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Movie Management</h4>
            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addMovieModal">
                <i class="fas fa-plus-circle me-2"></i> Add New Movie
            </button>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Poster</th>
                            <th>Title</th>
                            <th>Language</th>
                            <th>Genre</th>
                            <th>Rating</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Release Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $movie): ?>
                            <tr>
                                <td><?php echo $movie['id']; ?></td>
                                <td>
                                    <?php if ($movie['poster_url']): ?>
                                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                                             class="movie-poster">
                                    <?php else: ?>
                                        <div class="movie-poster bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-film text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($movie['title']); ?></strong>
                                    <?php if ($movie['is_featured']): ?>
                                        <span class="badge bg-warning ms-1">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($movie['language_name']); ?></td>
                                <td><?php echo htmlspecialchars($movie['genre_name']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="fas fa-star"></i> <?php echo $movie['rating']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($movie['duration']); ?></td>
                                <td>$<?php echo number_format($movie['ticket_price'], 2); ?></td>
                                <td>
                                    <?php if ($movie['is_now_showing']): ?>
                                        <span class="status-badge status-active">Now Showing</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Coming Soon</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($movie['release_date'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-action btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editMovieModal"
                                            onclick="loadMovieData(<?php echo $movie['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="admin_movies.php?action=delete&id=<?php echo $movie['id']; ?>" 
                                       class="btn btn-sm btn-action btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this movie?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Movie Statistics -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Movies</h6>
                    <h3 class="mb-0"><?php echo $movieStats['total_movies']; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Now Showing</h6>
                    <h3 class="mb-0"><?php echo $movieStats['now_showing']; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Coming Soon</h6>
                    <h3 class="mb-0"><?php echo $movieStats['coming_soon']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Movie Modal -->
    <div class="modal fade" id="addMovieModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_movie.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Language *</label>
                                <select class="form-select" name="language_id" required>
                                    <?php foreach ($languages as $lang): ?>
                                        <option value="<?php echo $lang['id']; ?>"><?php echo $lang['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Genre *</label>
                                <select class="form-select" name="genre_id" required>
                                    <?php foreach ($genres as $genre): ?>
                                        <option value="<?php echo $genre['id']; ?>"><?php echo $genre['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating (0-5)</label>
                                <input type="number" class="form-control" name="rating" min="0" max="5" step="0.1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Duration</label>
                                <input type="text" class="form-control" name="duration" placeholder="e.g., 2h 30m">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ticket Price</label>
                                <input type="number" class="form-control" name="ticket_price" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Release Date</label>
                                <input type="date" class="form-control" name="release_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poster URL</label>
                                <input type="text" class="form-control" name="poster_url" placeholder="Image URL">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_now_showing" checked>
                                    <label class="form-check-label">Now Showing</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_featured">
                                    <label class="form-check-label">Featured Movie</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_movie" class="btn btn-admin">Add Movie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Movie Modal -->
    <div class="modal fade" id="editMovieModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_movie.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="edit_movie_id" name="movie_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Language *</label>
                                <select class="form-select" id="edit_language_id" name="language_id" required>
                                    <?php foreach ($languages as $lang): ?>
                                        <option value="<?php echo $lang['id']; ?>"><?php echo $lang['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Genre *</label>
                                <select class="form-select" id="edit_genre_id" name="genre_id" required>
                                    <?php foreach ($genres as $genre): ?>
                                        <option value="<?php echo $genre['id']; ?>"><?php echo $genre['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating (0-5)</label>
                                <input type="number" class="form-control" id="edit_rating" name="rating" min="0" max="5" step="0.1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Duration</label>
                                <input type="text" class="form-control" id="edit_duration" name="duration" placeholder="e.g., 2h 30m">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ticket Price</label>
                                <input type="number" class="form-control" id="edit_ticket_price" name="ticket_price" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Release Date</label>
                                <input type="date" class="form-control" id="edit_release_date" name="release_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poster URL</label>
                                <input type="text" class="form-control" id="edit_poster_url" name="poster_url" placeholder="Image URL">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_now_showing" name="is_now_showing">
                                    <label class="form-check-label">Now Showing</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_featured" name="is_featured">
                                    <label class="form-check-label">Featured Movie</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_movie" class="btn btn-admin">Update Movie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function loadMovieData(movieId) {
            fetch('ajax_get_movie.php?id=' + movieId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_movie_id').value = data.id;
                    document.getElementById('edit_title').value = data.title;
                    document.getElementById('edit_language_id').value = data.language_id;
                    document.getElementById('edit_genre_id').value = data.genre_id;
                    document.getElementById('edit_rating').value = data.rating;
                    document.getElementById('edit_duration').value = data.duration;
                    document.getElementById('edit_ticket_price').value = data.ticket_price;
                    document.getElementById('edit_release_date').value = data.release_date;
                    document.getElementById('edit_poster_url').value = data.poster_url;
                    document.getElementById('edit_description').value = data.description;
                    document.getElementById('edit_is_now_showing').checked = data.is_now_showing == 1;
                    document.getElementById('edit_is_featured').checked = data.is_featured == 1;
                })
                .catch(error => console.error('Error:', error));
        }

        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>