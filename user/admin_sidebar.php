<?php
// Get current page for active class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="text-center py-4">
        <h2 class="text-white mb-0"><i class="fas fa-film"></i> CinemaKrish</h2>
        <small class="text-light">Admin Dashboard</small>
    </div>
    
    <div class="nav flex-column">
        <a href="admin.php" class="nav-link <?php echo $current_page == 'admin.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="admin_users.php" class="nav-link <?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="admin_movies.php" class="nav-link <?php echo $current_page == 'admin_movies.php' ? 'active' : ''; ?>">
            <i class="fas fa-film"></i> Movies
        </a>
        <a href="admin_bookings.php" class="nav-link <?php echo $current_page == 'admin_bookings.php' ? 'active' : ''; ?>">
            <i class="fas fa-ticket-alt"></i> Bookings
        </a>
        <a href="admin_theatres.php" class="nav-link <?php echo $current_page == 'admin_theatres.php' ? 'active' : ''; ?>">
            <i class="fas fa-theater-masks"></i> Theatres
        </a>
        <a href="admin_shows.php" class="nav-link <?php echo $current_page == 'admin_shows.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Shows
        </a>
        <a href="admin_slider.php" class="nav-link <?php echo $current_page == 'admin_slider.php' ? 'active' : ''; ?>">
            <i class="fas fa-images"></i> Slider
        </a>
        <a href="admin_contact.php" class="nav-link <?php echo $current_page == 'admin_contact.php' ? 'active' : ''; ?>">
            <i class="fas fa-address-book"></i> Contact Info
        </a>
        <a href="admin_reports.php" class="nav-link <?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        <div class="mt-auto p-3">
            <a href="logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>