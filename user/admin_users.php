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

// Handle actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Handle delete
if ($action == 'delete' && $id) {
    if ($database->deleteUser($id)) {
        header('Location: admin_users.php?success=User deleted successfully');
        exit();
    } else {
        header('Location: admin_users.php?error=Failed to delete user');
        exit();
    }
}

// Handle status change
if (isset($_GET['toggle_status'])) {
    $user_id = $_GET['toggle_status'];
    $current_status = $_GET['current_status'];
    $new_status = $current_status == 1 ? 0 : 1;
    
    if ($database->updateUserStatus($user_id, $new_status)) {
        header('Location: admin_users.php?success=User status updated');
        exit();
    } else {
        header('Location: admin_users.php?error=Failed to update status');
        exit();
    }
}

// Handle admin status change
if (isset($_GET['toggle_admin'])) {
    $user_id = $_GET['toggle_admin'];
    $current_admin = $_GET['current_admin'];
    $new_admin = $current_admin == 1 ? 0 : 1;
    
    if ($new_admin) {
        if ($database->makeUserAdmin($user_id)) {
            header('Location: admin_users.php?success=User made admin');
            exit();
        }
    } else {
        if ($database->removeUserAdmin($user_id)) {
            header('Location: admin_users.php?success=Admin rights removed');
            exit();
        }
    }
    header('Location: admin_users.php?error=Failed to update admin status');
    exit();
}

// Get all users
$users = $database->getAllUsers();
$userStats = $database->getUserStatistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CinemaKrish Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        <?php include 'admin_styles.css'; ?>
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #2c3e50;
            padding: 15px;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }
        
        .status-inactive {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .btn-action {
            padding: 5px 12px;
            margin: 0 3px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navbar -->
        <?php include 'admin_navbar.php'; ?>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>User Management</h4>
            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i> Add New User
            </button>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
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
                                    <button class="btn btn-sm btn-action btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal"
                                            onclick="loadUserData(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <?php if ($user['is_active']): ?>
                                        <a href="admin_users.php?toggle_status=<?php echo $user['id']; ?>&current_status=1" 
                                           class="btn btn-sm btn-action btn-outline-warning">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="admin_users.php?toggle_status=<?php echo $user['id']; ?>&current_status=0" 
                                           class="btn btn-sm btn-action btn-outline-success">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($user['is_admin']): ?>
                                        <a href="admin_users.php?toggle_admin=<?php echo $user['id']; ?>&current_admin=1" 
                                           class="btn btn-sm btn-action btn-outline-secondary">
                                            Remove Admin
                                        </a>
                                    <?php else: ?>
                                        <a href="admin_users.php?toggle_admin=<?php echo $user['id']; ?>&current_admin=0" 
                                           class="btn btn-sm btn-action btn-outline-danger">
                                            Make Admin
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="admin_users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-action btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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

    <!-- Add User Modal -->
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
                                <input class="form-check-input" type="checkbox" name="is_admin" value="1">
                                <label class="form-check-label">Make Admin</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-admin">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_user.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (Leave blank to keep current)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_admin" name="is_admin" value="1">
                                <label class="form-check-label">Make Admin</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_user" class="btn btn-admin">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function loadUserData(userId) {
            fetch('ajax_get_user.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_user_id').value = data.id;
                    document.getElementById('edit_username').value = data.username;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_full_name').value = data.full_name;
                    document.getElementById('edit_phone').value = data.phone;
                    document.getElementById('edit_is_admin').checked = data.is_admin == 1;
                    document.getElementById('edit_is_active').checked = data.is_active == 1;
                })
                .catch(error => console.error('Error:', error));
        }

        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>