<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check admin authentication
if(!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$db = new Database();
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $duration = (int)$_POST['duration'];
    $language = sanitize($_POST['language']);
    $genre = sanitize($_POST['genre']);
    $release_date = $_POST['release_date'];
    $rating = (float)$_POST['rating'];
    $director = sanitize($_POST['director']);
    $cast = sanitize($_POST['cast']);
    $status = sanitize($_POST['status']);
    $trailer_url = sanitize($_POST['trailer_url']);
    
    // Validate
    if(empty($title) || empty($description) || $duration <= 0) {
        $error = 'Please fill all required fields';
    } else {
        // Handle file uploads
        $poster_image = '';
        $banner_image = '';
        
        // Upload poster
        if(isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] == 0) {
            $result = uploadImage($_FILES['poster_image'], '../../assets/uploads/movies/');
            if($result['success']) {
                $poster_image = $result['filename'];
            } else {
                $error = $result['message'];
            }
        }
        
        // Upload banner
        if(empty($error) && isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
            $result = uploadImage($_FILES['banner_image'], '../../assets/uploads/movies/');
            if($result['success']) {
                $banner_image = $result['filename'];
            } else {
                $error = $result['message'];
            }
        }
        
        if(empty($error)) {
            // Insert movie
            $db->query("INSERT INTO movies (title, description, duration, language, genre, 
                         release_date, rating, poster_image, banner_image, trailer_url, 
                         director, cast, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $db->bind(1, $title);
            $db->bind(2, $description);
            $db->bind(3, $duration);
            $db->bind(4, $language);
            $db->bind(5, $genre);
            $db->bind(6, $release_date);
            $db->bind(7, $rating);
            $db->bind(8, $poster_image);
            $db->bind(9, $banner_image);
            $db->bind(10, $trailer_url);
            $db->bind(11, $director);
            $db->bind(12, $cast);
            $db->bind(13, $status);
            
            if($db->execute()) {
                $success = 'Movie added successfully!';
                // Clear form
                $_POST = array();
            } else {
                $error = 'Failed to add movie';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie - Admin Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Admin CSS -->
    <link href="../../assets/css/admin.css" rel="stylesheet">
    
    <style>
        .image-preview-container {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            background: #f8f9fa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
        }
        
        .required:after {
            content: " *";
            color: #dc3545;
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-section h5 {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/admin-header.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add New Movie</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="manage-movies.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Movies
                        </a>
                    </div>
                </div>
                
                <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" id="movieForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5>Basic Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Movie Title</label>
                                <input type="text" name="title" class="form-control" 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Language</label>
                                <input type="text" name="language" class="form-control" 
                                       value="<?php echo isset($_POST['language']) ? htmlspecialchars($_POST['language']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Genre</label>
                                <input type="text" name="genre" class="form-control" 
                                       value="<?php echo isset($_POST['genre']) ? htmlspecialchars($_POST['genre']) : ''; ?>" 
                                       placeholder="Action, Drama, Comedy" required>
                                <small class="text-muted">Separate multiple genres with commas</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Duration (minutes)</label>
                                <input type="number" name="duration" class="form-control" 
                                       value="<?php echo isset($_POST['duration']) ? $_POST['duration'] : ''; ?>" 
                                       min="1" max="300" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Release Date</label>
                                <input type="date" name="release_date" class="form-control" 
                                       value="<?php echo isset($_POST['release_date']) ? $_POST['release_date'] : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating (out of 10)</label>
                                <input type="number" name="rating" class="form-control" 
                                       value="<?php echo isset($_POST['rating']) ? $_POST['rating'] : '0'; ?>" 
                                       min="0" max="10" step="0.1">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Director</label>
                                <input type="text" name="director" class="form-control" 
                                       value="<?php echo isset($_POST['director']) ? htmlspecialchars($_POST['director']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Cast</label>
                                <textarea name="cast" class="form-control" rows="2" required><?php echo isset($_POST['cast']) ? htmlspecialchars($_POST['cast']) : ''; ?></textarea>
                                <small class="text-muted">Separate actor names with commas</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="now_showing" <?php echo (isset($_POST['status']) && $_POST['status'] == 'now_showing') ? 'selected' : ''; ?>>Now Showing</option>
                                    <option value="coming_soon" <?php echo (isset($_POST['status']) && $_POST['status'] == 'coming_soon') ? 'selected' : ''; ?>>Coming Soon</option>
                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trailer URL (YouTube)</label>
                                <input type="url" name="trailer_url" class="form-control" 
                                       value="<?php echo isset($_POST['trailer_url']) ? htmlspecialchars($_POST['trailer_url']) : ''; ?>" 
                                       placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-section">
                        <h5>Movie Description</h5>
                        <div class="mb-3">
                            <label class="form-label required">Description</label>
                            <textarea name="description" class="form-control" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Images -->
                    <div class="form-section">
                        <h5>Movie Images</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label required">Poster Image</label>
                                <div class="image-preview-container" id="posterPreview">
                                    <div class="text-center">
                                        <i class="bi bi-image display-4 text-muted mb-3"></i>
                                        <p class="text-muted mb-0">Poster preview will appear here</p>
                                        <small class="text-muted">Recommended: 400x600px, JPG/PNG</small>
                                    </div>
                                </div>
                                <input type="file" name="poster_image" class="form-control" 
                                       accept="image/*" id="posterInput" required>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Banner Image (Optional)</label>
                                <div class="image-preview-container" id="bannerPreview">
                                    <div class="text-center">
                                        <i class="bi bi-image display-4 text-muted mb-3"></i>
                                        <p class="text-muted mb-0">Banner preview will appear here</p>
                                        <small class="text-muted">Recommended: 1200x400px, JPG/PNG</small>
                                    </div>
                                </div>
                                <input type="file" name="banner_image" class="form-control" 
                                       accept="image/*" id="bannerInput">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit -->
                    <div class="form-section">
                        <div class="d-flex justify-content-between">
                            <a href="manage-movies.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Add Movie
                            </button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
    
    <script>
        // Image preview for poster
        document.getElementById('posterInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('posterPreview');
                    preview.innerHTML = `<img src="${e.target.result}" class="image-preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Image preview for banner
        document.getElementById('bannerInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('bannerPreview');
                    preview.innerHTML = `<img src="${e.target.result}" class="image-preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Form validation
        document.getElementById('movieForm').addEventListener('submit', function(e) {
            const posterInput = document.getElementById('posterInput');
            if(posterInput.files.length === 0) {
                e.preventDefault();
                alert('Please select a poster image');
                return false;
            }
            
            const file = posterInput.files[0];
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if(!validTypes.includes(file.type)) {
                e.preventDefault();
                alert('Please select a valid image file (JPG, PNG, GIF)');
                return false;
            }
            
            if(file.size > 5 * 1024 * 1024) { // 5MB
                e.preventDefault();
                alert('Image size must be less than 5MB');
                return false;
            }
            
            return true;
        });
        
        // Auto-fill current date for release date
        document.addEventListener('DOMContentLoaded', function() {
            const releaseDateInput = document.querySelector('input[name="release_date"]');
            if(!releaseDateInput.value) {
                const today = new Date().toISOString().split('T')[0];
                releaseDateInput.value = today;
            }
        });
    </script>
    
    <?php include '../../includes/admin-footer.php'; ?>
</body>
</html>