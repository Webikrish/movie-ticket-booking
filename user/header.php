<header >
    <?php
    // Include session manager
    require_once 'session_manager.php';
    
    // Get current user
    $currentUser = getCurrentUser();
    ?>
    
    <nav class="navbar navbar-expand-lg fixed-top" style="background:black">
        <div class="container" >
            <a class="navbar-brand" href="index.php"style="color:red;font-family: Oswald, sans-serif">
                <i class="fas fa-film"></i>CinemaKrish
            </a>
            
            <!-- Location Selector for Mobile -->
            <div class="d-lg-none d-flex align-items-center">
                <select class="form-select form-select-sm me-3" style="width: auto; background: #0a0a0a; color: white; border: 1px solid #d32f2f;">
                    <option selected style="background: #0a0a0a">Thoothukudi</option>
                    <option style="background: #0a0a0a">Thiruchendur</option>
                    <option style="background: #0a0a0a">Santhakulam</option>
                    <option style="background: #0a0a0a">Aathur</option>
                </select>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"><i class="fas fa-bars text-light"></i></span>
                </button>
            </div>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Navigation Links -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php"style="color:white">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#movies" style="color:white">Movies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="theatre.php"style="color:white">Theatres</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php"style="color:white">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php"style="color:white">Contact</a>
                    </li>
                    
                    <?php if(isLoggedIn() && isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php"style="color:white">Admin Panel</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"style="color:white">Dashboard</a>
                        </li>
                    <?php elseif(isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"style="color:white">Dashboard</a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Location Selector (Desktop) -->
                <!-- <div class="d-none d-lg-flex align-items-center me-4">
                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                    <select class="form-select form-select-sm" style="background: #0a0a0a; color: white; border: 1px solid #d32f2f;">
                        <option selected>Thoothukudi</option>
                        <option>Thiruchendur</option>
                        <option>Santhakulam</option>
                        <option>Aathur</option>
                    </select>
                </div> -->
                
                <!-- User Authentication Buttons -->
                <div class="d-flex align-items-center">
                    <?php if(isLoggedIn()): ?>
                        <!-- User is logged in -->
                        <div class="dropdown">
                            <button class="btn btn-login dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false"style="color:white">
                                <i class="fas fa-user me-2"></i>
                                <?php 
                                $displayName = !empty($currentUser['full_name']) ? $currentUser['full_name'] : $currentUser['username'];
                                echo "Hi, " . htmlspecialchars($displayName);
                                ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="background: #1a1a1a; border: 1px solid #d32f2f;">
                                <li>
                                    <span class="dropdown-item text-light" style="background: #1a1a1a;">
                                        <small>Logged in as</small><br>
                                        <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                                        <span class="badge bg-<?php echo isAdmin() ? 'danger' : 'warning'; ?> ms-2">
                                            <?php echo isAdmin() ? 'Admin' : 'User'; ?>
                                        </span>
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider" style="border-color: #444;"></li>
                                <li><a class="dropdown-item text-light" href="my_profile.php" style="background: #1a1a1a;">
                                    <i class="fas fa-user-circle me-2"></i>My Profile
                                </a></li>
                                <li><a class="dropdown-item text-light" href="my_bookings.php" style="background: #1a1a1a;">
                                    <i class="fas fa-ticket-alt me-2"></i>My Bookings
                                </a></li>
                                
                                <?php if(isAdmin()): ?>
                                    <li><hr class="dropdown-divider" style="border-color: #444;"></li>
                                    <li><a class="dropdown-item text-light" href="admin.php" style="background: #1a1a1a;">
                                        <i class="fas fa-cogs me-2"></i>Admin Panel
                                    </a></li>
                                    <li><a class="dropdown-item text-light" href="admin_movies.php" style="background: #1a1a1a;">
                                        <i class="fas fa-film me-2"></i>Manage Movies
                                    </a></li>
                                    <li><a class="dropdown-item text-light" href="admin_users.php" style="background: #1a1a1a;">
                                        <i class="fas fa-users me-2"></i>Manage Users
                                    </a></li>
                                <?php endif; ?>
                                
                                <li><hr class="dropdown-divider" style="border-color: #444;"></li>
                                <li>
                                    <a class="dropdown-item text-light" href="logout.php" style="background: #d32f2f;">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- User is not logged in -->
                        <button class="btn btn-login me-2" onclick="location.href='login.php'">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                        <button class="btn btn-register" onclick="location.href='register.php'">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>