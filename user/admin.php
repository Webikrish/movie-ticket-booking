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


// Get database statistics
$userStats = $database->getUserStatistics();
$movieStats = $database->getMovieStatistics();
$bookingStats = $database->getBookingStatistics();
$systemStats = $database->getSystemStatistics();
$revenueStats = $database->getRevenueStatistics();
$popularMovies = $database->getPopularMovies(5);

// Handle actions
$action = $_GET['action'] ?? '';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_action']) && isset($_POST['selected_items'])) {
        $selected_items = $_POST['selected_items'];
        $bulk_action = $_POST['bulk_action'];
        
        foreach ($selected_items as $item_id) {
            switch ($bulk_action) {
                case 'activate':
                    $database->updateUserStatus($item_id, 1);
                    break;
                case 'deactivate':
                    $database->updateUserStatus($item_id, 0);
                    break;
                case 'make_admin':
                    $database->makeUserAdmin($item_id);
                    break;
                case 'remove_admin':
                    $database->removeUserAdmin($item_id);
                    break;
                case 'delete':
                    $database->deleteUser($item_id);
                    break;
            }
        }
        header('Location: admin.php?success=Bulk action completed');
        exit();
    }
}

// Handle search
$search_term = $_GET['search'] ?? '';
$search_results = [];
if ($search_term) {
    $search_results = $database->searchBookings($search_term);
}

// Get recent activities
$recent_activities = $database->getRecentActivities(10);

// Get all data for tables
$users = $database->getAllUsers();
$movies = $database->getAllMovies();
$bookings = $database->getAllBookingsWithDetails();
$theatres = $database->getAllTheatres();
$shows = $database->getShowsWithDetails();
$slider_images = $database->getAllSliderImages();
$contact_info = $database->getAllContactInfo();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaKrish Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a252f 100%);
            min-height: 100vh;
            position: fixed;
            width: 250px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: #bdc3c7;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(52, 152, 219, 0.2);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 24px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border-left: 5px solid var(--secondary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .stat-card h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-card p {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table th {
            background-color: var(--light-bg);
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
            padding: 15px;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            color: white;
        }
        
        .btn-action {
            padding: 5px 12px;
            margin: 0 3px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        
        .search-box {
            max-width: 400px;
        }
        
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .activity-icon.user {
            color: var(--secondary-color);
        }
        
        .activity-icon.movie {
            color: var(--accent-color);
        }
        
        .activity-icon.booking {
            color: var(--success-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                transition: margin-left 0.3s ease;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
        }
        
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }
        
        .status-inactive {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--accent-color);
        }
        
        .status-pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }
        
        .status-confirmed {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }
        
        .status-cancelled {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="text-center py-4">
            <h2 class="text-white mb-0"><i class="fas fa-film"></i> CinemaKrish</h2>
            <small class="text-light">Admin Dashboard</small>
        </div>
        
        <div class="nav flex-column">
            <a href="#dashboard" class="nav-link active" onclick="showTab('dashboard')">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="#users" class="nav-link" onclick="showTab('users')">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="#movies" class="nav-link" onclick="showTab('movies')">
                <i class="fas fa-film"></i> Movies
            </a>
            <a href="#bookings" class="nav-link" onclick="showTab('bookings')">
                <i class="fas fa-ticket-alt"></i> Bookings
            </a>
            <a href="#theatres" class="nav-link" onclick="showTab('theatres')">
                <i class="fas fa-theater-masks"></i> Theatres
            </a>
            <a href="#shows" class="nav-link" onclick="showTab('shows')">
                <i class="fas fa-calendar-alt"></i> Shows
            </a>
            <a href="#slider" class="nav-link" onclick="showTab('slider')">
                <i class="fas fa-images"></i> Slider
            </a>
            <a href="#contact" class="nav-link" onclick="showTab('contact')">
                <i class="fas fa-address-book"></i> Contact Info
            </a>
            <a href="#reports" class="nav-link" onclick="showTab('reports')">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <div class="mt-auto p-3">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navbar -->
        <nav class="navbar navbar-light bg-light mb-4">
            <div class="container-fluid">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <h4 class="mb-0" id="page-title">Admin Dashboard</h4>
                    <div class="d-flex align-items-center">
                        <form method="GET" class="d-flex search-box" id="search-form">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search bookings..." name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                                <button class="btn btn-admin" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        <div class="dropdown ms-3">
                            <button class="btn btn-admin dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2"></i> <?php echo $_SESSION['username']; ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tab Content -->
        <div id="tab-content">
            
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h2><?php echo $systemStats['total_users']; ?></h2>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-film"></i>
                            <h2><?php echo $systemStats['active_movies']; ?></h2>
                            <p>Active Movies</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-ticket-alt"></i>
                            <h2><?php echo $systemStats['today_bookings']; ?></h2>
                            <p>Today's Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-dollar-sign"></i>
                            <h2>$<?php echo number_format($systemStats['today_revenue'], 2); ?></h2>
                            <p>Today's Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="chart-container">
                            <h5>Revenue Trends (Last 7 Days)</h5>
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="recent-activity">
                            <h5>Recent Activities</h5>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $activity['type']; ?>">
                                        <?php if ($activity['type'] === 'user_registered'): ?>
                                            <i class="fas fa-user-plus"></i>
                                        <?php elseif ($activity['type'] === 'movie_added'): ?>
                                            <i class="fas fa-film"></i>
                                        <?php else: ?>
                                            <i class="fas fa-ticket-alt"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                        <small class="text-muted">
                                            <?php 
                                                $time = strtotime($activity['date']);
                                                echo date('M d, Y', $time);
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Popular Movies (Most Bookings)</h5>
                            <ul class="list-group">
                                <?php foreach ($popularMovies as $movie): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($movie['title']); ?>
                                        <span class="badge bg-primary rounded-pill"><?php echo $movie['bookings_count']; ?> bookings</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Quick Stats</h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted">Total Theatres</small>
                                        <h4 class="mb-0"><?php echo $systemStats['total_theatres']; ?></h4>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted">Total Screens</small>
                                        <h4 class="mb-0"><?php echo array_sum(array_column($theatres, 'total_screens')); ?></h4>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted">Total Shows</small>
                                        <h4 class="mb-0"><?php echo count($shows); ?></h4>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted">Avg. Ticket Price</small>
                                        <h4 class="mb-0">$<?php echo number_format(array_sum(array_column($shows, 'ticket_price')) / count($shows), 2); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div id="users" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>User Management</h4>
                    <button class="btn btn-admin" onclick="openModal('addUserModal')">
                        <i class="fas fa-user-plus me-2"></i> Add New User
                    </button>
                </div>

                <div class="table-container">
                    <form method="POST" id="userBulkForm">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <select class="form-select me-2" name="bulk_action" style="width: 200px;">
                                    <option value="">Bulk Actions</option>
                                    <option value="activate">Activate Selected</option>
                                    <option value="deactivate">Deactivate Selected</option>
                                    <option value="make_admin">Make Admin</option>
                                    <option value="remove_admin">Remove Admin</option>
                                    <option value="delete">Delete Selected</option>
                                </select>
                                <button type="submit" class="btn btn-admin">Apply</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllUsers">
                                        </th>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Full Name</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_items[]" value="<?php echo $user['id']; ?>">
                                            </td>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td>
                                                <?php if ($user['is_admin']): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Customer</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="status-badge status-active">Active</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-inactive">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-action btn-outline-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['is_active']): ?>
                                                    <button class="btn btn-sm btn-action btn-outline-warning" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 0)">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-action btn-outline-success" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 1)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($user['is_admin']): ?>
                                                    <button class="btn btn-sm btn-action btn-outline-secondary" onclick="toggleAdminStatus(<?php echo $user['id']; ?>, 0)">
                                                        Remove Admin
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-action btn-outline-danger" onclick="toggleAdminStatus(<?php echo $user['id']; ?>, 1)">
                                                        Make Admin
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>

                <!-- User Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted">Total Users</h6>
                            <h3 class="mb-0"><?php echo $userStats['total_users']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted">Admins</h6>
                            <h3 class="mb-0"><?php echo $userStats['admin_count']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted">Customers</h6>
                            <h3 class="mb-0"><?php echo $userStats['customer_count']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted">Active Users</h6>
                            <h3 class="mb-0"><?php echo $userStats['active_users']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movies Tab -->
            <div id="movies" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Movie Management</h4>
                    <button class="btn btn-admin" onclick="openModal('addMovieModal')">
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
                                                     style="width: 60px; height: 80px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 80px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
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
                                            <button class="btn btn-sm btn-action btn-outline-primary" onclick="editMovie(<?php echo $movie['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-action btn-outline-danger" onclick="deleteMovie(<?php echo $movie['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

            <!-- Bookings Tab -->
            <div id="bookings" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Booking Management</h4>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-admin me-2" onclick="exportBookings()">
                            <i class="fas fa-download me-2"></i> Export CSV
                        </button>
                        <button class="btn btn-admin" onclick="printBookings()">
                            <i class="fas fa-print me-2"></i> Print Report
                        </button>
                    </div>
                </div>

                <?php if ($search_term): ?>
                    <div class="alert alert-info">
                        Search results for: <strong><?php echo htmlspecialchars($search_term); ?></strong>
                        <a href="admin.php" class="float-end">Clear Search</a>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Customer</th>
                                    <th>Movie</th>
                                    <th>Theatre</th>
                                    <th>Show Date & Time</th>
                                    <th>Seats</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Booked On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['ticket_number']); ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['customer_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['theatre_name']); ?></td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($booking['show_date'])); ?><br>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($booking['show_time'])); ?></small>
                                        </td>
                                        <td><?php echo $booking['total_seats']; ?> seats<br>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['seat_numbers']); ?></small>
                                        </td>
                                        <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                        <td>
                                            <?php if ($booking['booking_status'] === 'confirmed'): ?>
                                                <span class="status-badge status-confirmed">Confirmed</span>
                                            <?php elseif ($booking['booking_status'] === 'pending'): ?>
                                                <span class="status-badge status-pending">Pending</span>
                                            <?php else: ?>
                                                <span class="status-badge status-cancelled">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['payment_status'] === 'completed'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?><br>
                                            <small class="text-muted"><?php echo $booking['payment_method']; ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($booking['booking_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-action btn-outline-info" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($booking['booking_status'] !== 'cancelled'): ?>
                                                <button class="btn btn-sm btn-action btn-outline-danger" onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                                    Cancel
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Booking Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted">Total Bookings</h6>
                            <h3 class="mb-0"><?php echo $bookingStats['total_bookings']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted">Confirmed</h6>
                            <h3 class="mb-0"><?php echo $bookingStats['confirmed_bookings']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted">Pending</h6>
                            <h3 class="mb-0"><?php echo $bookingStats['pending_bookings']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted">Total Revenue</h6>
                            <h3 class="mb-0">$<?php echo number_format($bookingStats['total_revenue'], 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theatres Tab -->
            <div id="theatres" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Theatre Management</h4>
                    <button class="btn btn-admin" onclick="openModal('addTheatreModal')">
                        <i class="fas fa-plus-circle me-2"></i> Add New Theatre
                    </button>
                </div>

                <div class="row">
                    <?php foreach ($theatres as $theatre): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($theatre['name']); ?></h5>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($theatre['location']); ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-primary"><?php echo $theatre['total_screens']; ?> Screens</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">Facilities:</small>
                                        <?php 
                                        $facilities = explode(',', $theatre['facilities']);
                                        foreach ($facilities as $facility): 
                                        ?>
                                            <span class="badge bg-light text-dark me-1 mb-1"><?php echo trim($facility); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-city me-1"></i><?php echo htmlspecialchars($theatre['city']); ?>
                                        </small>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editTheatre(<?php echo $theatre['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTheatre(<?php echo $theatre['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Theatre Summary -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h5>Theatre Summary</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <small class="text-muted">Total Theatres</small>
                                    <h4 class="mb-0"><?php echo count($theatres); ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Total Screens</small>
                                    <h4 class="mb-0"><?php echo array_sum(array_column($theatres, 'total_screens')); ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Cities</small>
                                    <h4 class="mb-0"><?php echo count(array_unique(array_column($theatres, 'city'))); ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Avg Screens/Theatre</small>
                                    <h4 class="mb-0"><?php echo round(array_sum(array_column($theatres, 'total_screens')) / count($theatres), 1); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shows Tab -->
            <div id="shows" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Show Management</h4>
                    <button class="btn btn-admin" onclick="openModal('addShowModal')">
                        <i class="fas fa-plus-circle me-2"></i> Add New Show
                    </button>
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
                                            <small class="text-muted"><?php echo round(($show['available_seats'] / $show['total_seats']) * 100); ?>% available</small>
                                        </td>
                                        <td>$<?php echo number_format($show['ticket_price'], 2); ?></td>
                                        <td>
                                            <?php if ($show['is_active']): ?>
                                                <span class="status-badge status-active">Active</span>
                                            <?php else: ?>
                                                <span class="status-badge status-inactive">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-action btn-outline-primary" onclick="editShow(<?php echo $show['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-action btn-outline-danger" onclick="deleteShow(<?php echo $show['id']; ?>)">
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

            <!-- Slider Tab -->
            <div id="slider" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Slider Management</h4>
                    <button class="btn btn-admin" onclick="openModal('addSliderModal')">
                        <i class="fas fa-plus-circle me-2"></i> Add New Slider
                    </button>
                </div>

                <div class="row">
                    <?php foreach ($slider_images as $slider): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($slider['image_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($slider['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($slider['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($slider['description'], 0, 100)); ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?php echo $slider['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $slider['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editSlider(<?php echo $slider['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSlider(<?php echo $slider['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contact Info Tab -->
            <div id="contact" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Contact Information</h4>
                    <button class="btn btn-admin" onclick="saveAllContactInfo()">
                        <i class="fas fa-save me-2"></i> Save All Changes
                    </button>
                </div>

                <div class="table-container">
                    <form id="contactForm">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Key</th>
                                        <th>Value</th>
                                        <th>Icon</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contact_info as $contact): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-info"><?php echo $contact['info_type']; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($contact['info_key']); ?></td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="contact_<?php echo $contact['id']; ?>" 
                                                       value="<?php echo htmlspecialchars($contact['info_value']); ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars($contact['icon_class']); ?></td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm" 
                                                       name="order_<?php echo $contact['id']; ?>" 
                                                       value="<?php echo $contact['display_order']; ?>" 
                                                       style="width: 80px;">
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="active_<?php echo $contact['id']; ?>"
                                                           <?php echo $contact['is_active'] ? 'checked' : ''; ?>>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reports Tab -->
            <div id="reports" class="tab-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Reports & Analytics</h4>
                    <div class="btn-group">
                        <button class="btn btn-admin" onclick="generateReport('weekly')">
                            Weekly Report
                        </button>
                        <button class="btn btn-admin" onclick="generateReport('monthly')">
                            Monthly Report
                        </button>
                        <button class="btn btn-admin" onclick="generateReport('yearly')">
                            Yearly Report
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Booking Status Distribution</h5>
                            <canvas id="bookingChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Revenue by Month</h5>
                            <canvas id="monthlyRevenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="chart-container">
                            <h5>Detailed Booking Report</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Total Bookings</th>
                                            <th>Confirmed</th>
                                            <th>Cancelled</th>
                                            <th>Revenue</th>
                                            <th>Avg Ticket Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($revenueStats as $stat): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($stat['date'])); ?></td>
                                                <td><?php echo $stat['bookings']; ?></td>
                                                <td><?php echo $stat['bookings']; ?></td>
                                                <td>0</td>
                                                <td>$<?php echo number_format($stat['revenue'], 2); ?></td>
                                                <td>$<?php echo number_format($stat['revenue'] / $stat['bookings'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_user.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_admin">
                                <label class="form-check-label">Make Admin</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-admin">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                                    <?php foreach ($database->getLanguages() as $lang): ?>
                                        <option value="<?php echo $lang['id']; ?>"><?php echo $lang['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Genre *</label>
                                <select class="form-select" name="genre_id" required>
                                    <?php foreach ($database->getGenres() as $genre): ?>
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
                        <button type="submit" class="btn btn-admin">Add Movie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart.js for Dashboard
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($revenueStats, 'date')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($revenueStats, 'revenue')); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });

        // Tab navigation
        function showTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'users': 'User Management',
                'movies': 'Movie Management',
                'bookings': 'Booking Management',
                'theatres': 'Theatre Management',
                'shows': 'Show Management',
                'slider': 'Slider Management',
                'contact': 'Contact Information',
                'reports': 'Reports & Analytics'
            };
            document.getElementById('page-title').textContent = titles[tabId];
        }

        // Sidebar toggle for mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Modal functions
        function openModal(modalId) {
            const modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        }

        // Bulk selection for users
        document.getElementById('selectAllUsers').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // User management functions
        function editUser(userId) {
            // Fetch user data and populate edit modal
            fetch('get_user.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    // Populate edit form
                    console.log('Edit user:', data);
                    // Show edit modal with populated data
                    openModal('editUserModal');
                });
        }

        function toggleUserStatus(userId, status) {
            if (confirm('Are you sure?')) {
                window.location.href = `update_user_status.php?id=${userId}&status=${status}`;
            }
        }

        function toggleAdminStatus(userId, isAdmin) {
            if (confirm('Are you sure?')) {
                window.location.href = `update_admin_status.php?id=${userId}&is_admin=${isAdmin}`;
            }
        }

        // Movie management functions
        function editMovie(movieId) {
            window.location.href = `edit_movie.php?id=${movieId}`;
        }

        function deleteMovie(movieId) {
            if (confirm('Are you sure you want to delete this movie?')) {
                window.location.href = `delete_movie.php?id=${movieId}`;
            }
        }

        // Booking management functions
        function viewBooking(bookingId) {
            window.open(`view_booking.php?id=${bookingId}`, '_blank');
        }

        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                window.location.href = `cancel_booking.php?id=${bookingId}`;
            }
        }

        // Export functions
        function exportBookings() {
            window.location.href = 'export_bookings.php';
        }

        function printBookings() {
            window.print();
        }

        // Theatre management
        function editTheatre(theatreId) {
            window.location.href = `edit_theatre.php?id=${theatreId}`;
        }

        function deleteTheatre(theatreId) {
            if (confirm('Are you sure you want to delete this theatre?')) {
                window.location.href = `delete_theatre.php?id=${theatreId}`;
            }
        }

        // Show management
        function editShow(showId) {
            window.location.href = `edit_show.php?id=${showId}`;
        }

        function deleteShow(showId) {
            if (confirm('Are you sure you want to delete this show?')) {
                window.location.href = `delete_show.php?id=${showId}`;
            }
        }

        // Slider management
        function editSlider(sliderId) {
            window.location.href = `edit_slider.php?id=${sliderId}`;
        }

        function deleteSlider(sliderId) {
            if (confirm('Are you sure you want to delete this slider?')) {
                window.location.href = `delete_slider.php?id=${sliderId}`;
            }
        }

        // Contact info save
        function saveAllContactInfo() {
            if (confirm('Save all changes to contact information?')) {
                document.getElementById('contactForm').submit();
            }
        }

        // Report generation
        function generateReport(type) {
            window.location.href = `generate_report.php?type=${type}`;
        }

        // Initialize dashboard with first tab
        document.addEventListener('DOMContentLoaded', function() {
            showTab('dashboard');
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>