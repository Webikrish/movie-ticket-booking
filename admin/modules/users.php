<?php
$pageTitle = 'Manage Users - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

$users = $db->getAllUsers();
$userStats = $db->getUserStatistics();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $userId = $_POST['user_id'];
        
        switch ($_POST['action']) {
            case 'make_admin':
                if ($db->makeUserAdmin($userId)) {
                    echo '<script>alert("User promoted to admin!"); window.location.reload();</script>';
                }
                break;
                
            case 'remove_admin':
                if ($db->removeUserAdmin($userId)) {
                    echo '<script>alert("Admin privileges removed!"); window.location.reload();</script>';
                }
                break;
                
            case 'toggle_status':
                $currentStatus = $_POST['current_status'];
                $newStatus = $currentStatus == 1 ? 0 : 1;
                if ($db->updateUserStatus($userId, $newStatus)) {
                    echo '<script>alert("User status updated!"); window.location.reload();</script>';
                }
                break;
                
            case 'delete':
                if ($db->deleteUser($userId)) {
                    echo '<script>alert("User deleted!"); window.location.reload();</script>';
                }
                break;
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>User Management</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i> Add New User
                </button>
            </div>
            <div class="card-body">
                <!-- Statistics Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card-sm">
                            <div class="card-body">
                                <h6>Total Users</h6>
                                <h3><?php echo $userStats['total_users']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-sm">
                            <div class="card-body">
                                <h6>Admins</h6>
                                <h3 class="text-success"><?php echo $userStats['admin_count']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-sm">
                            <div class="card-body">
                                <h6>Customers</h6>
                                <h3 class="text-primary"><?php echo $userStats['customer_count']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-sm">
                            <div class="card-body">
                                <h6>Active Users</h6>
                                <h3 class="text-warning"><?php echo $userStats['active_users']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td>
                                    <?php if ($user['is_admin'] == 1): ?>
                                        <span class="badge bg-success">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['is_active'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewUserModal"
                                                data-id="<?php echo $user['id']; ?>"
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                data-fullname="<?php echo htmlspecialchars($user['full_name']); ?>"
                                                data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
                                                data-created="<?php echo $user['created_at']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($user['is_admin'] == 0): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="make_admin">
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Make this user an admin?')">
                                                <i class="fas fa-user-shield"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="remove_admin">
                                                <button type="submit" class="btn btn-sm btn-warning" 
                                                        onclick="return confirm('Remove admin privileges?')">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="current_status" value="<?php echo $user['is_active']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $user['is_active'] == 1 ? 'btn-danger' : 'btn-success'; ?>"
                                                    onclick="return confirm('Change user status?')">
                                                <i class="fas <?php echo $user['is_active'] == 1 ? 'fa-ban' : 'fa-check'; ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User Type</label>
                        <select class="form-select" name="is_admin">
                            <option value="0">Customer</option>
                            <option value="1">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="add_user">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Username:</th>
                        <td id="view-username"></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td id="view-email"></td>
                    </tr>
                    <tr>
                        <th>Full Name:</th>
                        <td id="view-fullname"></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td id="view-phone"></td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td id="view-created"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // View User Modal
    $('#viewUserModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#view-username').text(button.data('username'));
        $('#view-email').text(button.data('email'));
        $('#view-fullname').text(button.data('fullname'));
        $('#view-phone').text(button.data('phone'));
        $('#view-created').text(new Date(button.data('created')).toLocaleString());
    });
});
</script>

<style>
.stat-card-sm {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    padding: 15px;
}

.stat-card-sm h6 {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 5px;
}

.stat-card-sm h3 {
    color: #343a40;
    font-size: 1.5rem;
    margin: 0;
}
</style>

<?php require_once '../footer.php'; ?>