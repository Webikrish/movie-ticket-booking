<?php
// Define page titles
$page_titles = [
    'admin.php' => 'Dashboard',
    'admin_users.php' => 'User Management',
    'admin_movies.php' => 'Movie Management',
    'admin_bookings.php' => 'Booking Management',
    'admin_theatres.php' => 'Theatre Management',
    'admin_shows.php' => 'Show Management',
    'admin_slider.php' => 'Slider Management',
    'admin_contact.php' => 'Contact Information',
    'admin_reports.php' => 'Reports & Analytics'
];

$current_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_titles[$current_page] ?? 'Admin Panel';
?>
<!-- Navbar -->
<nav class="navbar navbar-light bg-light mb-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between w-100 align-items-center">
            <h4 class="mb-0"><?php echo $page_title; ?></h4>
            <div class="d-flex align-items-center">
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