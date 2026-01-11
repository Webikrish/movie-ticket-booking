<?php
// admin.php - Admin Panel with is_admin check
require_once 'session_manager.php';
require_once 'db_connection.php';

// Only admin users can access this page
if (!isAdmin()) {
    // If not admin, redirect to homepage with error message
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: index.php');
    exit();
}

// Get user statistics
$database = new Database();
$db = $database->getConnection();

$userStats = $database->getUserStatistics();
$movieStats = $database->getMovieStatistics();
$adminUsers = $database->getAdminUsers();
$regularUsers = $database->getRegularUsers();
$recentActivities = $database->getRecentActivities(5);

$currentUser = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CinemaKrish</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .admin-sidebar {
            background: var(--secondary-dark);
            min-height: 100vh;
            position: fixed;
            width: 250px;
            border-right: 2px solid var(--accent-red);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .admin-main {
            margin-left: 250px;
            padding: 20px;
        }

        .admin-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent-red);
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }

        .admin-nav {
            padding: 20px 0;
        }

        .nav-link {
            color: var(--text-light);
            padding: 12px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(211, 47, 47, 0.1);
            border-left-color: var(--accent-red);
            color: var(--accent-gold);
        }

        .nav-link i {
            width: 25px;
            font-size: 1.1rem;
        }

        .admin-header {
            background: var(--secondary-dark);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid rgba(211, 47, 47, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .user-welcome {
            color: var(--accent-gold);
            font-weight: 600;
        }

        .stats-card {
            background: var(--secondary-dark);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-red);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .stats-icon {
            font-size: 2.5rem;
            color: var(--accent-gold);
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-gold);
            line-height: 1;
        }

        .stats-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .admin-section {
            background: var(--secondary-dark);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            color: var(--accent-gold);
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-red);
        }

        .table-dark {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table-dark th {
            background: rgba(211, 47, 47, 0.1);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--accent-gold);
            font-weight: 600;
        }

        .table-dark td {
            border-color: rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }

        .badge-admin {
            background: var(--accent-red);
            color: white;
        }

        .badge-user {
            background: var(--accent-gold);
            color: var(--primary-dark);
        }

        .btn-admin {
            background: var(--accent-red);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-admin:hover {
            background: var(--accent-gold);
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline-admin {
            background: transparent;
            border: 2px solid var(--accent-red);
            color: var(--accent-red);
            padding: 8px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-outline-admin:hover {
            background: var(--accent-red);
            color: white;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background: rgba(211, 47, 47, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--accent-red);
        }

        .activity-details {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            color: var(--text-light);
        }

        .activity-date {
            color: var(--text-gray);
            font-size: 0.8rem;
        }

        @media (max-width: 992px) {
            .admin-sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-nav {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav-link {
                border-left: none;
                border-bottom: 3px solid transparent;
            }

            .nav-link:hover, .nav-link.active {
                border-left: none;
                border-bottom-color: var(--accent-red);
            }
        }

        @media (max-width: 768px) {
            .admin-header {
                padding: 15px;
            }

            .admin-section {
                padding: 20px;
            }

            .stats-number {
                font-size: 1.5rem;
            }

            .admin-actions {
                flex-direction: column;
            }

            .btn-admin, .btn-outline-admin {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-brand">
            <i class="fas fa-cogs"></i> CinemaKrish Admin
        </div>

        <!-- User Profile -->
        <div class="text-center p-4">
            <div class="mb-3">
                <i class="fas fa-user-circle fa-4x" style="color: var(--accent-gold);"></i>
            </div>
            <h5 class="user-welcome"><?php echo htmlspecialchars($currentUser['username']); ?></h5>
            <span class="badge badge-admin">Administrator</span>
            <small class="d-block text-muted mt-2">Last login: <?php echo date('Y-m-d H:i'); ?></small>
        </div>

        <!-- Navigation -->
        <div class="admin-nav">
            <a href="admin.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_movies.php" class="nav-link">
                <i class="fas fa-film"></i> Manage Movies
            </a>
            <a href="admin_users.php" class="nav-link">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="admin_sliders.php" class="nav-link">
                <i class="fas fa-images"></i> Manage Sliders
            </a>
            <a href="admin_contact.php" class="nav-link">
                <i class="fas fa-address-book"></i> Contact Info
            </a>
            <a href="admin_settings.php" class="nav-link">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="admin_reports.php" class="nav-link">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            
            <div class="mt-4 px-3">
                <a href="index.php" class="btn btn-outline-admin w-100 mb-2">
                    <i class="fas fa-home"></i> View Site
                </a>
                <a href="logout.php" class="btn-admin w-100">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Admin Header -->
        <div class="admin-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Admin Dashboard</h1>
                    <p class="text-muted mb-0">Welcome to CinemaKrish Administration Panel</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <small class="text-muted">System Time: <?php echo date('l, F j, Y H:i:s'); ?></small>
                </div>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users stats-icon"></i>
                    <div class="stats-number"><?php echo $userStats['total_users'] ?? 0; ?></div>
                    <div class="stats-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card text-center">
                    <i class="fas fa-shield-alt stats-icon"></i>
                    <div class="stats-number"><?php echo $userStats['admin_count'] ?? 0; ?></div>
                    <div class="stats-label">Admin Users</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card text-center">
                    <i class="fas fa-film stats-icon"></i>
                    <div class="stats-number"><?php echo $movieStats['total_movies'] ?? 0; ?></div>
                    <div class="stats-label">Total Movies</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card text-center">
                    <i class="fas fa-play-circle stats-icon"></i>
                    <div class="stats-number"><?php echo $movieStats['now_showing'] ?? 0; ?></div>
                    <div class="stats-label">Now Showing</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Admin Users -->
            <div class="col-lg-6 mb-4">
                <div class="admin-section">
                    <h3 class="section-title">
                        <i class="fas fa-shield-alt me-2"></i>Admin Users
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>Last Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($adminUsers) > 0): ?>
                                    <?php foreach($adminUsers as $admin): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
                                            <?php if($admin['id'] == $currentUser['id']): ?>
                                                <span class="badge badge-admin ms-1">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['full_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if($admin['last_login']): ?>
                                                <?php echo date('M d, H:i', strtotime($admin['last_login'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No admin users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Recent Activities -->
            <div class="col-lg-6 mb-4">
                <div class="admin-section">
                    <h3 class="section-title">
                        <i class="fas fa-history me-2"></i>Recent Activities
                    </h3>
                    <div class="activities-list">
                        <?php if(count($recentActivities) > 0): ?>
                            <?php foreach($recentActivities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php if($activity['type'] == 'user_registered'): ?>
                                        <i class="fas fa-user-plus"></i>
                                    <?php else: ?>
                                        <i class="fas fa-film"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                        <?php if($activity['type'] == 'user_registered'): ?>
                                            <span class="badge bg-success ms-1">New User</span>
                                        <?php else: ?>
                                            <span class="badge bg-info ms-1">New Movie</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-date">
                                        <?php echo date('M d, Y H:i', strtotime($activity['date'])); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-clock fa-2x mb-3"></i>
                                <p>No recent activities</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-section">
            <h3 class="section-title">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </h3>
            <div class="admin-actions">
                <a href="admin_add_movie.php" class="btn-admin">
                    <i class="fas fa-plus me-2"></i>Add New Movie
                </a>
                <a href="admin_add_user.php" class="btn-admin">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                </a>
                <a href="admin_add_slider.php" class="btn-admin">
                    <i class="fas fa-image me-2"></i>Add Slider Image
                </a>
                <a href="admin_users.php" class="btn-outline-admin">
                    <i class="fas fa-users me-2"></i>Manage All Users
                </a>
                <a href="admin_movies.php" class="btn-outline-admin">
                    <i class="fas fa-film me-2"></i>Manage All Movies
                </a>
            </div>
        </div>

        <!-- System Information -->
        <div class="admin-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle me-2"></i>System Information
            </h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-database stats-icon"></i>
                        <div class="stats-number">MySQL</div>
                        <div class="stats-label">Database</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-code stats-icon"></i>
                        <div class="stats-number">PHP <?php echo phpversion(); ?></div>
                        <div class="stats-label">Version</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-server stats-icon"></i>
                        <div class="stats-number"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></div>
                        <div class="stats-label">Server</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto refresh statistics every 30 seconds
        setInterval(function() {
            fetch('admin_stats.php')
                .then(response => response.json())
                .then(data => {
                    // Update statistics cards if needed
                    console.log('Stats updated:', data);
                })
                .catch(error => console.error('Error updating stats:', error));
        }, 30000);

        // Real-time clock update
        function updateClock() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const timeString = now.toLocaleDateString('en-US', options);
            document.querySelector('.admin-header small').textContent = 'System Time: ' + timeString;
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock();

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId !== '#') {
                    const targetElement = document.querySelector(targetId);
                    if(targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        // Highlight active nav link
        const currentPage = window.location.pathname.split('/').pop();
        document.querySelectorAll('.nav-link').forEach(link => {
            const linkHref = link.getAttribute('href');
            if(linkHref === currentPage || (currentPage === 'admin.php' && linkHref === 'admin.php')) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    </script>
</body>
</html>