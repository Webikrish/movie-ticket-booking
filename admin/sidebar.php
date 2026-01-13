<div class="sidebar">
    <div class="sidebar-header">
        <h3>CinemaKrish</h3>
        <p>Admin Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        
        <li class="sidebar-title">Management</li>
        
        <li>
            <a href="modules/movies.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'movies.php' ? 'active' : ''; ?>">
                <i class="fas fa-film"></i> Movies
            </a>
        </li>
        
        <li>
            <a href="modules/bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> Bookings
            </a>
        </li>
        
        <li>
            <a href="modules/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        
        <li>
            <a href="modules/theatres.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'theatres.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i> Theatres
            </a>
        </li>
        
        <li>
            <a href="modules/shows.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'shows.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Shows
            </a>
        </li>
        
        <li class="sidebar-title">Content</li>
        
        <li>
            <a href="modules/slider.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'slider.php' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Slider
            </a>
        </li>
        
        <li>
            <a href="modules/content.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'content.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> Pages Content
            </a>
        </li>
        
        <li>
            <a href="modules/contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Contact Info
            </a>
        </li>
        
        <li class="sidebar-title">Settings</li>
        
        <li>
            <a href="modules/settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> System Settings
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <p>Version 1.0.0</p>
    </div>
</div>