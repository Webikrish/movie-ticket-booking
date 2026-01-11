<?php
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-camera-reels"></i> <?php echo SITE_NAME; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="movies.php"><i class="bi bi-film"></i> Movies</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="cityDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-geo-alt"></i> Select City
                    </a>
                    <ul class="dropdown-menu">
                        <?php
                        $cities = getCities();
                        foreach($cities as $city): ?>
                        <li><a class="dropdown-item" href="movies.php?city=<?php echo urlencode($city->city); ?>">
                            <?php echo $city->city; ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php"><i class="bi bi-info-circle"></i> About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php"><i class="bi bi-telephone"></i> Contact</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if($isLoggedIn): ?>
                <div class="dropdown me-3">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" 
                       id="userDropdown" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-4 me-2"></i>
                        <span><?php echo htmlspecialchars($userName); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li><a class="dropdown-item" href="my-bookings.php"><i class="bi bi-ticket-perforated"></i> My Bookings</a></li>
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="login.php" class="btn btn-outline-light me-2"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                <a href="register.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>