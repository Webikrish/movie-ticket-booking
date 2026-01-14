<?php
session_start();
require_once 'db_connection.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_show'])) {
        // Add new show
        try {
            $movie_id = $_POST['movie_id'];
            $theatre_id = $_POST['theatre_id'];
            $screen_number = $_POST['screen_number'];
            $show_date = $_POST['show_date'];
            $show_time = $_POST['show_time'];
            $total_seats = $_POST['total_seats'];
            $ticket_price = $_POST['ticket_price'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            // Add show to database
            $result = $database->addShow($movie_id, $theatre_id, $screen_number, $show_date, $show_time, $total_seats, $total_seats, $ticket_price, $is_active);
            
            if ($result) {
                $message = "Show added successfully!";
            } else {
                $error = "Failed to add show.";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_show'])) {
        // Update existing show
        try {
            $show_id = $_POST['show_id'];
            $movie_id = $_POST['movie_id'];
            $theatre_id = $_POST['theatre_id'];
            $screen_number = $_POST['screen_number'];
            $show_date = $_POST['show_date'];
            $show_time = $_POST['show_time'];
            $total_seats = $_POST['total_seats'];
            $available_seats = $_POST['available_seats'];
            $ticket_price = $_POST['ticket_price'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            // Update show in database
            $result = $database->updateShow($show_id, $movie_id, $theatre_id, $screen_number, $show_date, $show_time, $available_seats, $total_seats, $ticket_price, $is_active);
            
            if ($result) {
                $message = "Show updated successfully!";
            } else {
                $error = "Failed to update show.";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_show'])) {
        // Delete show
        try {
            $show_id = $_POST['show_id'];
            
            // Check if there are any bookings for this show
            $bookings_check = $database->conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE show_id = :show_id");
            $bookings_check->execute([':show_id' => $show_id]);
            $booking_data = $bookings_check->fetch();
            
            if ($booking_data['booking_count'] > 0) {
                $error = "Cannot delete show. There are existing bookings for this show.";
            } else {
                // Delete show from database
                $result = $database->deleteShow($show_id);
                
                if ($result) {
                    $message = "Show deleted successfully!";
                } else {
                    $error = "Failed to delete show.";
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['bulk_delete'])) {
        // Bulk delete shows
        if (isset($_POST['selected_shows']) && !empty($_POST['selected_shows'])) {
            try {
                $deleted_count = 0;
                $failed_count = 0;
                
                foreach ($_POST['selected_shows'] as $show_id) {
                    // Check if there are any bookings
                    $bookings_check = $database->conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE show_id = :show_id");
                    $bookings_check->execute([':show_id' => $show_id]);
                    $booking_data = $bookings_check->fetch();
                    
                    if ($booking_data['booking_count'] == 0) {
                        if ($database->deleteShow($show_id)) {
                            $deleted_count++;
                        } else {
                            $failed_count++;
                        }
                    } else {
                        $failed_count++;
                    }
                }
                
                if ($deleted_count > 0) {
                    $message = "Successfully deleted $deleted_count show(s).";
                    if ($failed_count > 0) {
                        $message .= " $failed_count show(s) could not be deleted (may have existing bookings).";
                    }
                } else {
                    $error = "No shows were deleted. They may have existing bookings.";
                }
            } catch (Exception $e) {
                $error = "Error during bulk delete: " . $e->getMessage();
            }
        } else {
            $error = "No shows selected for deletion.";
        }
    }
}

// Get all shows with details
$shows = $database->getShowsWithDetails();

// Get all movies for dropdown
$movies = $database->getAllMovies();

// Get all theatres for dropdown
$theatres = $database->getAllTheatres();

// Get show details for editing
$edit_show = null;
if (isset($_GET['edit'])) {
    $show_id = $_GET['edit'];
    $edit_show = $database->getShowById($show_id);
}

// Get show details for viewing
$view_show = null;
if (isset($_GET['view'])) {
    $show_id = $_GET['view'];
    $view_show = $database->getShowById($show_id);
    
    // Get booked seats for this show
    $booked_seats_query = $database->conn->prepare("
        SELECT seat_numbers 
        FROM bookings 
        WHERE show_id = :show_id AND booking_status = 'confirmed'
    ");
    $booked_seats_query->execute([':show_id' => $show_id]);
    $booked_seats_data = $booked_seats_query->fetchAll();
    
    $booked_seats = [];
    foreach ($booked_seats_data as $data) {
        $seats = explode(',', $data['seat_numbers']);
        $booked_seats = array_merge($booked_seats, $seats);
    }
}

// Filter shows by date
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$filter_movie = isset($_GET['filter_movie']) ? $_GET['filter_movie'] : '';
$filter_theatre = isset($_GET['filter_theatre']) ? $_GET['filter_theatre'] : '';

if ($filter_date || $filter_movie || $filter_theatre) {
    $shows = array_filter($shows, function($show) use ($filter_date, $filter_movie, $filter_theatre) {
        $match = true;
        if ($filter_date && $show['show_date'] != $filter_date) {
            $match = false;
        }
        if ($filter_movie && $show['movie_id'] != $filter_movie) {
            $match = false;
        }
        if ($filter_theatre && $show['theatre_id'] != $filter_theatre) {
            $match = false;
        }
        return $match;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shows - CinemaKrish Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .admin-container {
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .seat-layout {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 5px;
            max-width: 800px;
            margin: 0 auto;
        }
        .seat {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }
        .seat.booked {
            background: #dc3545;
            color: white;
            cursor: not-allowed;
        }
        .seat.available {
            background: #28a745;
            color: white;
        }
        .seat.vip {
            background: #ffc107;
            color: #000;
        }
        .screen {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            border-radius: 5px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stat-card .count {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 0.875rem;
        }
        .badge-custom {
            font-size: 0.75em;
            padding: 3px 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid admin-container">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <?php include 'admin_sidebar.php'; ?>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">Manage Shows</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShowModal">
                        <i class="fas fa-plus"></i> Add New Show
                    </button>
                </div>

                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filter Shows</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="filter_date" value="<?php echo $filter_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Movie</label>
                                <select class="form-select" name="filter_movie">
                                    <option value="">All Movies</option>
                                    <?php foreach ($movies as $movie): ?>
                                        <option value="<?php echo $movie['id']; ?>" <?php echo ($filter_movie == $movie['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($movie['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Theatre</label>
                                <select class="form-select" name="filter_theatre">
                                    <option value="">All Theatres</option>
                                    <?php foreach ($theatres as $theatre): ?>
                                        <option value="<?php echo $theatre['id']; ?>" <?php echo ($filter_theatre == $theatre['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($theatre['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="admin_shows.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-calendar-alt text-primary"></i>
                            <div class="count"><?php echo count($shows); ?></div>
                            <div class="label">Total Shows</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-film text-success"></i>
                            <div class="count"><?php echo count($movies); ?></div>
                            <div class="label">Movies</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-building text-warning"></i>
                            <div class="count"><?php echo count($theatres); ?></div>
                            <div class="label">Theatres</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-clock text-info"></i>
                            <div class="count">
                                <?php 
                                    $today = date('Y-m-d');
                                    $today_shows = array_filter($shows, function($show) use ($today) {
                                        return $show['show_date'] == $today;
                                    });
                                    echo count($today_shows);
                                ?>
                            </div>
                            <div class="label">Today's Shows</div>
                        </div>
                    </div>
                </div>

                <!-- Shows Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Shows</h5>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete selected shows?');">
                            <input type="hidden" name="bulk_delete">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="showsTable">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>ID</th>
                                        <th>Movie</th>
                                        <th>Theatre</th>
                                        <th>Date & Time</th>
                                        <th>Screen</th>
                                        <th>Seats</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($shows)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No shows found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($shows as $show): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_shows[]" value="<?php echo $show['id']; ?>" class="show-checkbox">
                                                </td>
                                                <td><?php echo $show['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($show['movie_title']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Screen: <?php echo $show['screen_number']; ?>
                                                    </small>
                                                </td>
                                                <td><?php echo htmlspecialchars($show['theatre_name']); ?></td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($show['show_date'])); ?><br>
                                                    <small><?php echo date('h:i A', strtotime($show['show_time'])); ?></small>
                                                </td>
                                                <td><?php echo $show['screen_number']; ?></td>
                                                <td>
                                                    <span class="badge bg-success"><?php echo $show['available_seats']; ?> Available</span><br>
                                                    <small class="text-muted">/ <?php echo $show['total_seats']; ?> Total</small>
                                                </td>
                                                <td>$<?php echo $show['ticket_price']; ?></td>
                                                <td>
                                                    <?php if ($show['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?view=<?php echo $show['id']; ?>" 
                                                           class="btn btn-sm btn-info btn-action" 
                                                           data-bs-toggle="tooltip" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="?edit=<?php echo $show['id']; ?>" 
                                                           class="btn btn-sm btn-warning btn-action" 
                                                           data-bs-toggle="tooltip" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this show?');">
                                                            <input type="hidden" name="show_id" value="<?php echo $show['id']; ?>">
                                                            <input type="hidden" name="delete_show">
                                                            <button type="submit" class="btn btn-sm btn-danger btn-action" 
                                                                    data-bs-toggle="tooltip" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Show Modal -->
    <div class="modal fade" id="addShowModal" tabindex="-1" aria-labelledby="addShowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addShowModalLabel">
                            <?php echo $edit_show ? 'Edit Show' : 'Add New Show'; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Movie *</label>
                                <select class="form-select" name="movie_id" required>
                                    <option value="">Select Movie</option>
                                    <?php foreach ($movies as $movie): ?>
                                        <option value="<?php echo $movie['id']; ?>"
                                            <?php echo ($edit_show && $edit_show['movie_id'] == $movie['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($movie['title']); ?> (<?php echo $movie['duration']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Theatre *</label>
                                <select class="form-select" name="theatre_id" required>
                                    <option value="">Select Theatre</option>
                                    <?php foreach ($theatres as $theatre): ?>
                                        <option value="<?php echo $theatre['id']; ?>"
                                            <?php echo ($edit_show && $edit_show['theatre_id'] == $theatre['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($theatre['name']); ?> - <?php echo $theatre['city']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Screen Number *</label>
                                <input type="number" class="form-control" name="screen_number" 
                                       value="<?php echo $edit_show ? $edit_show['screen_number'] : '1'; ?>" 
                                       min="1" max="10" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Show Date *</label>
                                <input type="date" class="form-control" name="show_date" 
                                       value="<?php echo $edit_show ? $edit_show['show_date'] : date('Y-m-d'); ?>" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Show Time *</label>
                                <input type="time" class="form-control" name="show_time" 
                                       value="<?php echo $edit_show ? $edit_show['show_time'] : '18:00'; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Ticket Price ($) *</label>
                                <input type="number" class="form-control" name="ticket_price" 
                                       value="<?php echo $edit_show ? $edit_show['ticket_price'] : '10.00'; ?>" 
                                       min="0" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Seats *</label>
                                <input type="number" class="form-control" name="total_seats" 
                                       value="<?php echo $edit_show ? $edit_show['total_seats'] : '100'; ?>" 
                                       min="10" max="500" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?php if ($edit_show): ?>
                                    <label class="form-label">Available Seats *</label>
                                    <input type="number" class="form-control" name="available_seats" 
                                           value="<?php echo $edit_show['available_seats']; ?>" 
                                           min="0" max="<?php echo $edit_show['total_seats']; ?>" required>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                           <?php echo ($edit_show && $edit_show['is_active']) || !$edit_show ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active Show
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <?php if ($edit_show): ?>
                            <input type="hidden" name="show_id" value="<?php echo $edit_show['id']; ?>">
                            <button type="submit" name="update_show" class="btn btn-primary">Update Show</button>
                        <?php else: ?>
                            <button type="submit" name="add_show" class="btn btn-primary">Add Show</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Show Modal -->
    <?php if ($view_show): ?>
        <div class="modal fade" id="viewShowModal" tabindex="-1" aria-labelledby="viewShowModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewShowModalLabel">Show Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Show Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Show ID:</strong></td>
                                        <td><?php echo $view_show['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Movie:</strong></td>
                                        <td><?php echo htmlspecialchars($view_show['movie_title']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Theatre:</strong></td>
                                        <td><?php echo htmlspecialchars($view_show['theatre_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Screen:</strong></td>
                                        <td>Screen <?php echo $view_show['screen_number']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date & Time:</strong></td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($view_show['show_date'])); ?> 
                                            at <?php echo date('h:i A', strtotime($view_show['show_time'])); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ticket Price:</strong></td>
                                        <td>$<?php echo $view_show['ticket_price']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <?php if ($view_show['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Seat Information</h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Seat Availability:</strong><br>
                                    Total Seats: <?php echo $view_show['total_seats']; ?><br>
                                    Available: <span class="text-success"><?php echo $view_show['available_seats']; ?></span><br>
                                    Booked: <span class="text-danger"><?php echo $view_show['total_seats'] - $view_show['available_seats']; ?></span>
                                </div>
                                
                                <?php if (!empty($booked_seats)): ?>
                                    <h6>Booked Seats:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($booked_seats as $seat): ?>
                                            <span class="badge bg-danger"><?php echo $seat; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Seat Layout -->
                        <div class="mt-4">
                            <h6>Seat Layout (Sample)</h6>
                            <div class="screen mb-3">SCREEN</div>
                            <div class="seat-layout">
                                <?php 
                                // Generate seat layout (10 columns, 10 rows)
                                $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                                foreach ($rows as $row) {
                                    for ($col = 1; $col <= 10; $col++) {
                                        $seat = $row . $col;
                                        $is_booked = in_array($seat, $booked_seats);
                                ?>
                                        <div class="seat <?php echo $is_booked ? 'booked' : 'available'; ?>">
                                            <?php echo $seat; ?>
                                        </div>
                                <?php 
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="?edit=<?php echo $view_show['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Show
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#showsTable').DataTable({
                "order": [[1, "desc"]],
                "pageLength": 25,
                "responsive": true
            });

            // Select all checkbox
            $('#selectAll').click(function() {
                $('.show-checkbox').prop('checked', this.checked);
            });

            // Show modal if edit parameter exists
            <?php if ($edit_show): ?>
                var addShowModal = new bootstrap.Modal(document.getElementById('addShowModal'));
                addShowModal.show();
            <?php endif; ?>

            // Show view modal if view parameter exists
            <?php if ($view_show): ?>
                var viewShowModal = new bootstrap.Modal(document.getElementById('viewShowModal'));
                viewShowModal.show();
            <?php endif; ?>

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Auto-refresh page after modal close if in edit mode
            <?php if ($edit_show): ?>
                $('#addShowModal').on('hidden.bs.modal', function () {
                    window.location.href = 'admin_shows.php';
                });
            <?php endif; ?>

            <?php if ($view_show): ?>
                $('#viewShowModal').on('hidden.bs.modal', function () {
                    window.location.href = 'admin_shows.php';
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>