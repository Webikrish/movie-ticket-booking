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
    if ($database->deleteShow($id)) {
        header('Location: admin_shows.php?success=Show deleted successfully');
        exit();
    } else {
        header('Location: admin_shows.php?error=Failed to delete show');
        exit();
    }
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $show_id = $_GET['toggle_status'];
    $current_status = $_GET['current_status'];
    $new_status = $current_status == 1 ? 0 : 1;
    
    if ($database->updateShowStatus($show_id, $new_status)) {
        header('Location: admin_shows.php?success=Show status updated');
        exit();
    } else {
        header('Location: admin_shows.php?error=Failed to update show status');
        exit();
    }
}

// Get all shows
$shows = $database->getShowsWithDetails();
$movies = $database->getActiveMovies();
$theatres = $database->getAllTheatres();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Management - CinemaKrish Admin</title>
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
            cursor: pointer;
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
            <h4>Show Management</h4>
            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addShowModal">
                <i class="fas fa-plus-circle me-2"></i> Add New Show
            </button>
        </div>

        <!-- Show Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Shows</h6>
                    <h3 class="mb-0"><?php echo count($shows); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Active Shows</h6>
                    <h3 class="mb-0"><?php echo count(array_filter($shows, function($show) { return $show['is_active'] == 1; })); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Movies</h6>
                    <h3 class="mb-0"><?php echo count(array_unique(array_column($shows, 'movie_title'))); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Theatres</h6>
                    <h3 class="mb-0"><?php echo count(array_unique(array_column($shows, 'theatre_id'))); ?></h3>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Movie</th>
                            <th>Theatre</th>
                            <th>Screen</th>
                            <th>Date & Time</th>
                            <th>Seats</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shows as $show): ?>
                            <tr>
                                <td><?php echo $show['id']; ?></td>
                                <td><?php echo htmlspecialchars($show['movie_title']); ?></td>
                                <td><?php echo htmlspecialchars($show['theatre_name']); ?></td>
                                <td>Screen <?php echo $show['screen_number']; ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($show['show_date'])); ?><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($show['show_time'])); ?></small>
                                </td>
                                <td>
                                    <?php echo $show['available_seats']; ?>/<?php echo $show['total_seats']; ?><br>
                                    <small class="text-muted">
                                        <?php 
                                            $percentage = $show['total_seats'] > 0 ? round(($show['available_seats'] / $show['total_seats']) * 100) : 0;
                                            echo $percentage . '% available';
                                        ?>
                                    </small>
                                </td>
                                <td>$<?php echo number_format($show['ticket_price'], 2); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $show['is_active'] ? 'status-active' : 'status-inactive'; ?>"
                                          onclick="toggleShowStatus(<?php echo $show['id']; ?>, <?php echo $show['is_active']; ?>)">
                                        <?php echo $show['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-action btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editShowModal"
                                            onclick="loadShowData(<?php echo $show['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-action btn-outline-danger" 
                                            onclick="deleteShow(<?php echo $show['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Show Modal -->
    <div class="modal fade" id="addShowModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Show</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_show.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Movie *</label>
                                <select class="form-select" name="movie_id" required>
                                    <option value="">Select Movie</option>
                                    <?php foreach ($movies as $movie): ?>
                                        <option value="<?php echo $movie['id']; ?>">
                                            <?php echo htmlspecialchars($movie['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Theatre *</label>
                                <select class="form-select" id="theatre_select" name="theatre_id" required onchange="loadScreens(this.value)">
                                    <option value="">Select Theatre</option>
                                    <?php foreach ($theatres as $theatre): ?>
                                        <option value="<?php echo $theatre['id']; ?>">
                                            <?php echo htmlspecialchars($theatre['name']); ?> - <?php echo htmlspecialchars($theatre['city']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Screen Number *</label>
                                <select class="form-select" id="screen_select" name="screen_number" required>
                                    <option value="">Select Theatre First</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Show Date *</label>
                                <input type="date" class="form-control" name="show_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Show Time *</label>
                                <input type="time" class="form-control" name="show_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ticket Price *</label>
                                <input type="number" class="form-control" name="ticket_price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Seats *</label>
                                <input type="number" class="form-control" name="total_seats" required min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" checked>
                                    <label class="form-check-label">Active Show</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_show" class="btn btn-admin">Add Show</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Show Modal -->
    <div class="modal fade" id="editShowModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Show</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_show.php" method="POST">
                    <input type="hidden" id="edit_show_id" name="show_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Movie *</label>
                                <select class="form-select" id="edit_movie_id" name="movie_id" required>
                                    <?php foreach ($movies as $movie): ?>
                                        <option value="<?php echo $movie['id']; ?>">
                                            <?php echo htmlspecialchars($movie['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Theatre *</label>
                                <select class="form-select" id="edit_theatre_id" name="theatre_id" required>
                                    <?php foreach ($theatres as $theatre): ?>
                                        <option value="<?php echo $theatre['id']; ?>">
                                            <?php echo htmlspecialchars($theatre['name']); ?> - <?php echo htmlspecialchars($theatre['city']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Screen Number *</label>
                                <input type="number" class="form-control" id="edit_screen_number" name="screen_number" required min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Show Date *</label>
                                <input type="date" class="form-control" id="edit_show_date" name="show_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Show Time *</label>
                                <input type="time" class="form-control" id="edit_show_time" name="show_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ticket Price *</label>
                                <input type="number" class="form-control" id="edit_ticket_price" name="ticket_price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Seats *</label>
                                <input type="number" class="form-control" id="edit_total_seats" name="total_seats" required min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Available Seats *</label>
                                <input type="number" class="form-control" id="edit_available_seats" name="available_seats" required min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                    <label class="form-check-label">Active Show</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_show" class="btn btn-admin">Update Show</button>
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

        function loadScreens(theatreId) {
            if (!theatreId) return;
            
            fetch('ajax_get_screens.php?theatre_id=' + theatreId)
                .then(response => response.json())
                .then(data => {
                    const screenSelect = document.getElementById('screen_select');
                    screenSelect.innerHTML = '<option value="">Select Screen</option>';
                    
                    for (let i = 1; i <= data.total_screens; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = 'Screen ' + i;
                        screenSelect.appendChild(option);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function loadShowData(showId) {
            fetch('ajax_get_show.php?id=' + showId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_show_id').value = data.id;
                    document.getElementById('edit_movie_id').value = data.movie_id;
                    document.getElementById('edit_theatre_id').value = data.theatre_id;
                    document.getElementById('edit_screen_number').value = data.screen_number;
                    document.getElementById('edit_show_date').value = data.show_date;
                    document.getElementById('edit_show_time').value = data.show_time;
                    document.getElementById('edit_ticket_price').value = data.ticket_price;
                    document.getElementById('edit_total_seats').value = data.total_seats;
                    document.getElementById('edit_available_seats').value = data.available_seats;
                    document.getElementById('edit_is_active').checked = data.is_active == 1;
                })
                .catch(error => console.error('Error:', error));
        }

        function toggleShowStatus(showId, currentStatus) {
            if (confirm('Are you sure you want to toggle show status?')) {
                window.location.href = `admin_shows.php?toggle_status=${showId}&current_status=${currentStatus}`;
            }
        }

        function deleteShow(showId) {
            if (confirm('Are you sure you want to delete this show?')) {
                window.location.href = 'admin_shows.php?action=delete&id=' + showId;
            }
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