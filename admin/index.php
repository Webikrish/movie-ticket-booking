<?php
$pageTitle = 'Dashboard - Admin Panel';
require_once 'header.php';

// Use correct path to db_connection.php
$database = new Database();
$db = $database->getConnection();

// Get statistics
$userStats = $db->getUserStatistics();
$movieStats = $db->getMovieStatistics();
$recentActivities = $db->getRecentActivities(5);
?>


<div class="row">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5>Total Users</h5>
                <h2><?php echo $userStats['total_users']; ?></h2>
                <div class="stat-details">
                    <span class="text-success"><?php echo $userStats['admin_count']; ?> Admin</span>
                    <span class="text-primary"><?php echo $userStats['customer_count']; ?> Customers</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-film"></i>
                </div>
                <h5>Total Movies</h5>
                <h2><?php echo $movieStats['total_movies']; ?></h2>
                <div class="stat-details">
                    <span class="text-success"><?php echo $movieStats['now_showing']; ?> Now Showing</span>
                    <span class="text-warning"><?php echo $movieStats['coming_soon']; ?> Coming Soon</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h5>Total Bookings</h5>
                <h2>128</h2>
                <div class="stat-details">
                    <span class="text-success">120 Confirmed</span>
                    <span class="text-danger">8 Pending</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h5>Revenue</h5>
                <h2>$12,540</h2>
                <div class="stat-details">
                    <span class="text-success">This Month</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Recent Bookings</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Movie</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Example booking data - you should implement getRecentBookings() in Database class
                            // $recentBookings = $db->getRecentBookings(5);
                            // foreach($recentBookings as $booking):
                            ?>
                            <tr>
                                <td>#BK001</td>
                                <td>John Doe</td>
                                <td>Avengers</td>
                                <td>2024-01-10</td>
                                <td>$45.00</td>
                                <td><span class="badge bg-success">Confirmed</span></td>
                            </tr>
                            <?php // endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Recent Activities</h5>
            </div>
            <div class="card-body">
                <ul class="activity-list">
                    <?php foreach($recentActivities as $activity): ?>
                    <li>
                        <div class="activity-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <h6><?php echo htmlspecialchars($activity['title']); ?></h6>
                            <p><?php echo date('h:i A', strtotime($activity['date'])); ?></p>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>