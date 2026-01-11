<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new Database();

// Get user details
$db->query("SELECT * FROM users WHERE id = ?");
$db->bind(1, $user_id);
$user = $db->single();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? sanitize($_POST['action']) : '';
    
    if($action == 'update_profile') {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        
        // Validate email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } else {
            // Check if email already exists for another user
            $db->query("SELECT id FROM users WHERE email = ? AND id != ?");
            $db->bind(1, $email);
            $db->bind(2, $user_id);
            
            if($db->single()) {
                $error = 'Email already registered by another user';
            } else {
                // Check if phone already exists for another user
                $db->query("SELECT id FROM users WHERE phone = ? AND id != ?");
                $db->bind(1, $phone);
                $db->bind(2, $user_id);
                
                if($db->single()) {
                    $error = 'Phone number already registered by another user';
                } else {
                    // Update profile
                    $db->query("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                    $db->bind(1, $name);
                    $db->bind(2, $email);
                    $db->bind(3, $phone);
                    $db->bind(4, $user_id);
                    
                    if($db->execute()) {
                        // Update session
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        
                        $success = 'Profile updated successfully!';
                        $user->name = $name;
                        $user->email = $email;
                        $user->phone = $phone;
                    } else {
                        $error = 'Failed to update profile';
                    }
                }
            }
        }
    } elseif($action == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required';
        } elseif($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif(strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters';
        } elseif(!password_verify($current_password, $user->password)) {
            $error = 'Current password is incorrect';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $db->query("UPDATE users SET password = ? WHERE id = ?");
            $db->bind(1, $hashed_password);
            $db->bind(2, $user_id);
            
            if($db->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password';
            }
        }
    } elseif($action == 'update_avatar') {
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $result = uploadImage($_FILES['avatar'], '../assets/uploads/profile/');
            
            if($result['success']) {
                // Delete old avatar if not default
                if($user->profile_image != 'default.jpg') {
                    $old_path = '../assets/uploads/profile/' . $user->profile_image;
                    if(file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                
                // Update database
                $db->query("UPDATE users SET profile_image = ? WHERE id = ?");
                $db->bind(1, $result['filename']);
                $db->bind(2, $user_id);
                
                if($db->execute()) {
                    $user->profile_image = $result['filename'];
                    $success = 'Profile picture updated successfully!';
                } else {
                    $error = 'Failed to update profile picture in database';
                }
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 0;
            margin-bottom: 30px;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
        }
        
        .profile-card {
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        .nav-pills .nav-link {
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s;
        }
        
        @media (max-width: 768px) {
            .profile-avatar {
                width: 120px;
                height: 120px;
            }
            
            .profile-header {
                padding: 30px 0;
            }
            
            .profile-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <div class="position-relative d-inline-block">
                        <img src="../assets/uploads/profile/<?php echo $user->profile_image ?: 'default.jpg'; ?>" 
                             alt="Profile" class="profile-avatar" 
                             id="avatarPreview"
                             data-bs-toggle="modal" data-bs-target="#avatarModal">
                        <div class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-2 border border-3 border-white">
                            <i class="bi bi-camera text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <h1 class="display-5 fw-bold mb-2"><?php echo htmlspecialchars($user->name); ?></h1>
                    <p class="lead mb-3">Welcome to your profile dashboard</p>
                    <div class="d-flex flex-wrap gap-3">
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user->email); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-phone"></i> <?php echo htmlspecialchars($user->phone); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-calendar"></i> Member since <?php echo date('M Y', strtotime($user->created_at)); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container py-4">
        <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="profile-card">
                    <ul class="nav nav-pills flex-column" id="profileTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" 
                                    data-bs-target="#personal" type="button">
                                <i class="bi bi-person me-2"></i> Personal Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" 
                                    data-bs-target="#password" type="button">
                                <i class="bi bi-shield-lock me-2"></i> Change Password
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" 
                                    data-bs-target="#bookings" type="button">
                                <i class="bi bi-ticket-perforated me-2"></i> Booking History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" 
                                    data-bs-target="#preferences" type="button">
                                <i class="bi bi-gear me-2"></i> Preferences
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-card">
                    <div class="tab-content" id="profileTabContent">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <h4 class="mb-4">Personal Information</h4>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user->name); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user->email); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($user->phone); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Status</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo ucfirst($user->status); ?>" readonly>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo formatDate($user->created_at); ?>" readonly>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Login</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo $user->last_login ? formatDate($user->last_login) : 'Never'; ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Change Password Tab -->
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <h4 class="mb-4">Change Password</h4>
                            
                            <form method="POST" id="passwordForm">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Password</label>
                                        <div class="input-group">
                                            <input type="password" name="current_password" 
                                                   class="form-control" id="currentPassword" required>
                                            <button class="btn btn-outline-secondary" type="button" 
                                                    onclick="togglePassword('currentPassword', this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">New Password</label>
                                        <div class="input-group">
                                            <input type="password" name="new_password" 
                                                   class="form-control" id="newPassword" required>
                                            <button class="btn btn-outline-secondary" type="button" 
                                                    onclick="togglePassword('newPassword', this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength" id="passwordStrength"></div>
                                        <small class="text-muted">Password must be at least 6 characters</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" name="confirm_password" 
                                                   class="form-control" id="confirmPassword" required>
                                            <button class="btn btn-outline-secondary" type="button" 
                                                    onclick="togglePassword('confirmPassword', this)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <small id="confirmHelp" class="form-text"></small>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-key"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Booking History Tab -->
                        <div class="tab-pane fade" id="bookings" role="tabpanel">
                            <h4 class="mb-4">Recent Bookings</h4>
                            
                            <?php
                            $db->query("SELECT b.*, m.title as movie_title, t.name as theatre_name, 
                                               s.show_date, s.show_time
                                        FROM bookings b
                                        JOIN shows s ON b.show_id = s.id
                                        JOIN movies m ON s.movie_id = m.id
                                        JOIN theatres t ON s.theatre_id = t.id
                                        WHERE b.user_id = ?
                                        ORDER BY b.booking_date DESC
                                        LIMIT 5");
                            $db->bind(1, $user_id);
                            $recent_bookings = $db->resultSet();
                            ?>
                            
                            <?php if(empty($recent_bookings)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-ticket display-4 text-muted"></i>
                                <h5 class="mt-3">No bookings yet</h5>
                                <p class="text-muted">You haven't made any bookings yet</p>
                                <a href="movies.php" class="btn btn-primary">Browse Movies</a>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Movie</th>
                                            <th>Theatre</th>
                                            <th>Date & Time</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo $booking->movie_title; ?></td>
                                            <td><?php echo $booking->theatre_name; ?></td>
                                            <td>
                                                <?php echo formatDate($booking->show_date); ?><br>
                                                <small><?php echo formatTime($booking->show_time); ?></small>
                                            </td>
                                            <td>₹<?php echo number_format($booking->total_amount, 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $booking->booking_status == 'confirmed' ? 'success' : 
                                                          ($booking->booking_status == 'cancelled' ? 'danger' : 
                                                          ($booking->booking_status == 'completed' ? 'info' : 'warning'));
                                                ?>">
                                                    <?php echo ucfirst($booking->booking_status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view-booking.php?id=<?php echo $booking->id; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="my-bookings.php" class="btn btn-outline-primary">
                                    View All Bookings
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Preferences Tab -->
                        <div class="tab-pane fade" id="preferences" role="tabpanel">
                            <h4 class="mb-4">Preferences</h4>
                            
                            <form method="POST" id="preferencesForm">
                                <div class="mb-4">
                                    <h6 class="mb-3">Notification Settings</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                        <label class="form-check-label" for="emailNotifications">
                                            Email notifications for new bookings
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="smsNotifications" checked>
                                        <label class="form-check-label" for="smsNotifications">
                                            SMS notifications for booking updates
                                        </label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="promotionalEmails">
                                        <label class="form-check-label" for="promotionalEmails">
                                            Promotional emails and offers
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="mb-3">Preferred City</h6>
                                    <select class="form-select" style="max-width: 300px;">
                                        <option>Select your preferred city</option>
                                        <?php
                                        $cities = getCities();
                                        foreach($cities as $city):
                                        ?>
                                        <option value="<?php echo $city->city; ?>">
                                            <?php echo $city->city; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="mb-3">Preferred Language</h6>
                                    <select class="form-select" style="max-width: 300px;">
                                        <option>Select preferred movie language</option>
                                        <?php
                                        $languages = getLanguages();
                                        foreach($languages as $lang):
                                        ?>
                                        <option value="<?php echo $lang->language; ?>">
                                            <?php echo $lang->language; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Avatar Modal -->
    <div class="modal fade" id="avatarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_avatar">
                        
                        <div class="text-center mb-4">
                            <img src="../assets/uploads/profile/<?php echo $user->profile_image ?: 'default.jpg'; ?>" 
                                 alt="Current Avatar" class="img-fluid rounded-circle mb-3" 
                                 style="width: 150px; height: 150px; object-fit: cover;" id="avatarPreview2">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Choose new profile picture</label>
                            <input type="file" name="avatar" class="form-control" 
                                   accept="image/*" id="avatarInput">
                            <small class="text-muted">Maximum file size: 2MB. Supported formats: JPG, PNG, GIF</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload Picture
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Password strength indicator
        const newPasswordInput = document.getElementById('newPassword');
        const strengthBar = document.getElementById('passwordStrength');
        
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check password strength
            if(password.length >= 6) strength++;
            if(password.length >= 8) strength++;
            if(/[A-Z]/.test(password)) strength++;
            if(/[0-9]/.test(password)) strength++;
            if(/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update strength bar
            switch(strength) {
                case 0:
                case 1:
                    strengthBar.style.width = '20%';
                    strengthBar.style.backgroundColor = '#dc3545';
                    break;
                case 2:
                    strengthBar.style.width = '40%';
                    strengthBar.style.backgroundColor = '#fd7e14';
                    break;
                case 3:
                    strengthBar.style.width = '60%';
                    strengthBar.style.backgroundColor = '#ffc107';
                    break;
                case 4:
                    strengthBar.style.width = '80%';
                    strengthBar.style.backgroundColor = '#28a745';
                    break;
                case 5:
                    strengthBar.style.width = '100%';
                    strengthBar.style.backgroundColor = '#20c997';
                    break;
            }
            
            // Check password match
            checkPasswordMatch();
        });
        
        // Check password confirmation
        function checkPasswordMatch() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const confirmHelp = document.getElementById('confirmHelp');
            
            if(confirmPassword === '') {
                confirmHelp.textContent = '';
                confirmHelp.className = 'form-text';
            } else if(newPassword === confirmPassword) {
                confirmHelp.textContent = '✓ Passwords match';
                confirmHelp.className = 'form-text text-success';
            } else {
                confirmHelp.textContent = '✗ Passwords do not match';
                confirmHelp.className = 'form-text text-danger';
            }
        }
        
        document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
        
        // Toggle password visibility
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if(input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
        
        // Avatar preview
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarPreview2 = document.getElementById('avatarPreview2');
        
        avatarInput.addEventListener('change', function() {
            if(this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    avatarPreview2.src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if(newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long');
                return false;
            }
            
            if(newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match');
                return false;
            }
            
            return true;
        });
        
        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getInstance(alert)?.close();
            });
        }, 5000);
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>