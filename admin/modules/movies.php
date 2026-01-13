<?php
$pageTitle = 'Manage Movies - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

$movies = $db->getAllMovies();
$languages = $db->getLanguages();
$genres = $db->getGenres();

// Handle movie actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    ':title' => $_POST['title'],
                    ':description' => $_POST['description'],
                    ':language_id' => $_POST['language_id'],
                    ':genre_id' => $_POST['genre_id'],
                    ':rating' => $_POST['rating'],
                    ':duration' => $_POST['duration'],
                    ':poster_url' => $_POST['poster_url'],
                    ':release_date' => $_POST['release_date'],
                    ':is_now_showing' => isset($_POST['is_now_showing']) ? 1 : 0,
                    ':ticket_price' => $_POST['ticket_price'],
                    ':is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];
                
                if ($db->addMovie($data)) {
                    echo '<script>alert("Movie added successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $data = [
                    ':title' => $_POST['title'],
                    ':description' => $_POST['description'],
                    ':language_id' => $_POST['language_id'],
                    ':genre_id' => $_POST['genre_id'],
                    ':rating' => $_POST['rating'],
                    ':duration' => $_POST['duration'],
                    ':poster_url' => $_POST['poster_url'],
                    ':release_date' => $_POST['release_date'],
                    ':is_now_showing' => isset($_POST['is_now_showing']) ? 1 : 0,
                    ':ticket_price' => $_POST['ticket_price'],
                    ':is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];
                
                if ($db->updateMovie($id, $data)) {
                    echo '<script>alert("Movie updated successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'delete':
                if ($db->deleteMovie($_POST['id'])) {
                    echo '<script>alert("Movie deleted successfully!"); window.location.reload();</script>';
                }
                break;
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Movie Management</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMovieModal">
                    <i class="fas fa-plus"></i> Add New Movie
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="moviesTable">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($movies as $movie): ?>
                            <tr>
                                <td><?php echo $movie['id']; ?></td>
                                <td>
                                    <img src="<?php echo $movie['poster_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                         style="width: 60px; height: 80px; object-fit: cover; border-radius: 5px;">
                                </td>
                                <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                <td><?php echo htmlspecialchars($movie['language_name']); ?></td>
                                <td><?php echo htmlspecialchars($movie['genre_name']); ?></td>
                                <td>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-star"></i> <?php echo $movie['rating']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($movie['duration']); ?></td>
                                <td>$<?php echo number_format($movie['ticket_price'], 2); ?></td>
                                <td>
                                    <?php if ($movie['is_now_showing'] == 1): ?>
                                        <span class="badge bg-success">Now Showing</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Coming Soon</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($movie['is_featured'] == 1): ?>
                                        <span class="badge bg-warning mt-1">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewMovieModal"
                                                data-id="<?php echo $movie['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($movie['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($movie['description']); ?>"
                                                data-language="<?php echo htmlspecialchars($movie['language_name']); ?>"
                                                data-genre="<?php echo htmlspecialchars($movie['genre_name']); ?>"
                                                data-rating="<?php echo $movie['rating']; ?>"
                                                data-duration="<?php echo $movie['duration']; ?>"
                                                data-poster="<?php echo $movie['poster_url']; ?>"
                                                data-release="<?php echo $movie['release_date']; ?>"
                                                data-price="<?php echo $movie['ticket_price']; ?>"
                                                data-showing="<?php echo $movie['is_now_showing']; ?>"
                                                data-featured="<?php echo $movie['is_featured']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editMovieModal"
                                                data-id="<?php echo $movie['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($movie['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($movie['description']); ?>"
                                                data-language_id="<?php echo $movie['language_id']; ?>"
                                                data-genre_id="<?php echo $movie['genre_id']; ?>"
                                                data-rating="<?php echo $movie['rating']; ?>"
                                                data-duration="<?php echo $movie['duration']; ?>"
                                                data-poster_url="<?php echo $movie['poster_url']; ?>"
                                                data-release_date="<?php echo $movie['release_date']; ?>"
                                                data-ticket_price="<?php echo $movie['ticket_price']; ?>"
                                                data-is_now_showing="<?php echo $movie['is_now_showing']; ?>"
                                                data-is_featured="<?php echo $movie['is_featured']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $movie['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this movie?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Language *</label>
                                <select class="form-select" name="language_id" required>
                                    <option value="">Select Language</option>
                                    <?php foreach($languages as $lang): ?>
                                    <option value="<?php echo $lang['id']; ?>"><?php echo htmlspecialchars($lang['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Genre *</label>
                                <select class="form-select" name="genre_id" required>
                                    <option value="">Select Genre</option>
                                    <?php foreach($genres as $genre): ?>
                                    <option value="<?php echo $genre['id']; ?>"><?php echo htmlspecialchars($genre['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Rating (0-5)</label>
                                <input type="number" class="form-control" name="rating" min="0" max="5" step="0.1" value="4.0">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Duration (e.g., 2h 30m)</label>
                                <input type="text" class="form-control" name="duration">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Poster URL</label>
                                <input type="url" class="form-control" name="poster_url">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Release Date</label>
                                <input type="date" class="form-control" name="release_date">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Ticket Price ($)</label>
                                <input type="number" class="form-control" name="ticket_price" step="0.01" min="0" value="10.99">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_now_showing" id="is_now_showing" checked>
                                <label class="form-check-label" for="is_now_showing">Now Showing</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                                <label class="form-check-label" for="is_featured">Featured Movie</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="add">Add Movie</button>
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
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" id="edit-title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Language *</label>
                                <select class="form-select" name="language_id" id="edit-language_id" required>
                                    <option value="">Select Language</option>
                                    <?php foreach($languages as $lang): ?>
                                    <option value="<?php echo $lang['id']; ?>"><?php echo htmlspecialchars($lang['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Genre *</label>
                                <select class="form-select" name="genre_id" id="edit-genre_id" required>
                                    <option value="">Select Genre</option>
                                    <?php foreach($genres as $genre): ?>
                                    <option value="<?php echo $genre['id']; ?>"><?php echo htmlspecialchars($genre['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Rating (0-5)</label>
                                <input type="number" class="form-control" name="rating" id="edit-rating" min="0" max="5" step="0.1">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Duration (e.g., 2h 30m)</label>
                                <input type="text" class="form-control" name="duration" id="edit-duration">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Poster URL</label>
                                <input type="url" class="form-control" name="poster_url" id="edit-poster_url">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Release Date</label>
                                <input type="date" class="form-control" name="release_date" id="edit-release_date">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Ticket Price ($)</label>
                                <input type="number" class="form-control" name="ticket_price" id="edit-ticket_price" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit-description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_now_showing" id="edit-is_now_showing">
                                <label class="form-check-label" for="edit-is_now_showing">Now Showing</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="edit-is_featured">
                                <label class="form-check-label" for="edit-is_featured">Featured Movie</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="edit">Update Movie</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Movie Modal -->
<div class="modal fade" id="viewMovieModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Movie Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img id="view-poster" src="" alt="Poster" class="img-fluid rounded mb-3" 
                             style="width: 100%; height: 300px; object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Title:</th>
                                <td id="view-title"></td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td id="view-description"></td>
                            </tr>
                            <tr>
                                <th>Language:</th>
                                <td id="view-language"></td>
                            </tr>
                            <tr>
                                <th>Genre:</th>
                                <td id="view-genre"></td>
                            </tr>
                            <tr>
                                <th>Rating:</th>
                                <td>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-star"></i> <span id="view-rating"></span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Duration:</th>
                                <td id="view-duration"></td>
                            </tr>
                            <tr>
                                <th>Release Date:</th>
                                <td id="view-release"></td>
                            </tr>
                            <tr>
                                <th>Ticket Price:</th>
                                <td>$<span id="view-price"></span></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span id="view-showing" class="badge bg-success">Now Showing</span>
                                    <span id="view-featured" class="badge bg-warning">Featured</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit Movie Modal
    $('#editMovieModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#edit-id').val(button.data('id'));
        $('#edit-title').val(button.data('title'));
        $('#edit-description').val(button.data('description'));
        $('#edit-language_id').val(button.data('language_id'));
        $('#edit-genre_id').val(button.data('genre_id'));
        $('#edit-rating').val(button.data('rating'));
        $('#edit-duration').val(button.data('duration'));
        $('#edit-poster_url').val(button.data('poster_url'));
        $('#edit-release_date').val(button.data('release_date'));
        $('#edit-ticket_price').val(button.data('ticket_price'));
        $('#edit-is_now_showing').prop('checked', button.data('is_now_showing') == 1);
        $('#edit-is_featured').prop('checked', button.data('is_featured') == 1);
    });
    
    // View Movie Modal
    $('#viewMovieModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#view-title').text(button.data('title'));
        $('#view-description').text(button.data('description'));
        $('#view-language').text(button.data('language'));
        $('#view-genre').text(button.data('genre'));
        $('#view-rating').text(button.data('rating'));
        $('#view-duration').text(button.data('duration'));
        $('#view-poster').attr('src', button.data('poster'));
        $('#view-release').text(button.data('release'));
        $('#view-price').text(button.data('price'));
        
        if (button.data('showing') == 1) {
            $('#view-showing').show();
        } else {
            $('#view-showing').hide();
        }
        
        if (button.data('featured') == 1) {
            $('#view-featured').show();
        } else {
            $('#view-featured').hide();
        }
    });
});
</script>

<?php require_once '../footer.php'; ?>