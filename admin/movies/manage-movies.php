<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check admin authentication
if(!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$db = new Database();

// Handle actions
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if($action == 'delete' && $id > 0) {
        // Delete movie
        $db->query("DELETE FROM movies WHERE id = ?");
        $db->bind(1, $id);
        if($db->execute()) {
            $_SESSION['success'] = 'Movie deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete movie';
        }
        header('Location: manage-movies.php');
        exit();
    } elseif($action == 'toggle_status' && $id > 0) {
        // Toggle status
        $db->query("UPDATE movies SET status = 
                    CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END 
                    WHERE id = ?");
        $db->bind(1, $id);
        $db->execute();
        header('Location: manage-movies.php');
        exit();
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$language = isset($_GET['language']) ? sanitize($_GET['language']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Pagination
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT * FROM movies WHERE 1=1";
$params = [];

if(!empty($search)) {
    $query .= " AND (title LIKE ? OR director LIKE ? OR cast LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if(!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

if(!empty($language)) {
    $query .= " AND language = ?";
    $params[] = $language;
}

// Count total records
$countQuery = str_replace('SELECT *', 'SELECT COUNT(*) as total', $query);
$db->query($countQuery);
foreach($params as $key => $value) {
    $db->bind($key + 1, $value);
}
$totalResult = $db->single();
$totalMovies = $totalResult->total;
$totalPages = ceil($totalMovies / $limit);

// Get movies with pagination
$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$db->query($query);

foreach($params as $key => $value) {
    $db->bind($key + 1, $value);
}
$db->bind(count($params) + 1, $limit, PDO::PARAM_INT);
$db->bind(count($params) + 2, $offset, PDO::PARAM_INT);

$movies = $db->resultSet();

// Get languages for filter
$db->query("SELECT DISTINCT language FROM movies ORDER BY language");
$languages = $db->resultSet();

// Show success/error messages
if(isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if(isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies - Admin Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Admin CSS -->
    <link href="../../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/admin-header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/admin-header.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Movies</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add-movie.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add New Movie
                        </a>
                    </div>
                </div>
                
                <?php if(isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Filter Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Title, director, cast...">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="now_showing" <?php echo $status == 'now_showing' ? 'selected' : ''; ?>>Now Showing</option>
                                    <option value="coming_soon" <?php echo $status == 'coming_soon' ? 'selected' : ''; ?>>Coming Soon</option>
                                    <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Language</label>
                                <select name="language" class="form-select">
                                    <option value="">All Languages</option>
                                    <?php foreach($languages as $lang): ?>
                                    <option value="<?php echo $lang->language; ?>"
                                            <?php echo $language == $lang->language ? 'selected' : ''; ?>>
                                        <?php echo $lang->language; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Movies Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Movies List</h5>
                        <span class="badge bg-primary">Total: <?php echo $totalMovies; ?></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="moviesTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Poster</th>
                                        <th>Title</th>
                                        <th>Language</th>
                                        <th>Genre</th>
                                        <th>Rating</th>
                                        <th>Status</th>
                                        <th>Release Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($movies)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="bi bi-film display-6 text-muted"></i>
                                            <h5 class="mt-3">No movies found</h5>
                                            <p class="text-muted">Try changing your filters or add a new movie</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($movies as $index => $movie): ?>
                                    <tr>
                                        <td><?php echo $offset + $index + 1; ?></td>
                                        <td>
                                            <img src="../../assets/uploads/movies/<?php echo $movie->poster_image; ?>" 
                                                 alt="<?php echo $movie->title; ?>" 
                                                 class="rounded" style="width: 50px; height: 75px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($movie->title); ?></strong>
                                            <small class="d-block text-muted"><?php echo $movie->director; ?></small>
                                        </td>
                                        <td><?php echo $movie->language; ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $movie->genre; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-star-fill"></i> <?php echo $movie->rating; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = $movie->status == 'now_showing' ? 'success' : 
                                                          ($movie->status == 'coming_soon' ? 'info' : 'secondary');
                                            ?>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $movie->status)); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($movie->release_date); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit-movie.php?id=<?php echo $movie->id; ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=toggle_status&id=<?php echo $movie->id; ?>" 
                                                   class="btn btn-outline-<?php echo $movie->status == 'inactive' ? 'success' : 'warning'; ?>" 
                                                   title="<?php echo $movie->status == 'inactive' ? 'Activate' : 'Deactivate'; ?>">
                                                    <i class="bi bi-power"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="confirmDelete(<?php echo $movie->id; ?>, '<?php echo htmlspecialchars($movie->title); ?>')"
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php 
                                    $params = $_GET;
                                    $params['page'] = $page - 1;
                                    echo http_build_query($params);
                                    ?>">Previous</a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php 
                                        $params = $_GET;
                                        $params['page'] = $i;
                                        echo http_build_query($params);
                                        ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php elseif($i == $page - 3 || $i == $page + 3): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php 
                                    $params = $_GET;
                                    $params['page'] = $page + 1;
                                    echo http_build_query($params);
                                    ?>">Next</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="movieTitle"></strong>?</p>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        This action cannot be undone. All related shows and bookings will also be deleted.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="deleteLink" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function confirmDelete(id, title) {
            document.getElementById('movieTitle').textContent = title;
            document.getElementById('deleteLink').href = '?action=delete&id=' + id;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        // Initialize DataTable
        $(document).ready(function() {
            $('#moviesTable').DataTable({
                "paging": false,
                "searching": false,
                "info": false,
                "ordering": true,
                "responsive": true
            });
        });
    </script>
    
    <?php include '../../includes/admin-footer.php'; ?>
</body>
</html>