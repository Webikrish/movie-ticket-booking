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
    if ($database->deleteContactInfo($id)) {
        header('Location: admin_contact.php?success=Contact info deleted successfully');
        exit();
    } else {
        header('Location: admin_contact.php?error=Failed to delete contact info');
        exit();
    }
}

// Handle bulk save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all'])) {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'contact_') === 0) {
            $contact_id = substr($key, 8);
            $info_value = $value;
            $display_order = $_POST['order_' . $contact_id] ?? 1;
            $is_active = isset($_POST['active_' . $contact_id]) ? 1 : 0;
            
            $database->updateContactInfo($contact_id, $info_value, $display_order, $is_active);
        }
    }
    
    // Handle new contact info
    if (!empty($_POST['new_info_key'])) {
        $database->addContactInfo(
            $_POST['new_info_type'],
            $_POST['new_info_key'],
            $_POST['new_info_value'],
            $_POST['new_icon_class'] ?? '',
            $_POST['new_display_order'] ?? 1,
            isset($_POST['new_is_active']) ? 1 : 0
        );
    }
    
    header('Location: admin_contact.php?success=Contact information updated successfully');
    exit();
}

// Get all contact info
$contact_info = $database->getAllContactInfo();
$info_types = ['email', 'phone', 'address', 'social', 'other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Information - CinemaKrish Admin</title>
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
        
        .type-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .type-email {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .type-phone {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .type-address {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }
        
        .type-social {
            background-color: rgba(230, 126, 34, 0.1);
            color: #e67e22;
        }
        
        .type-other {
            background-color: rgba(149, 165, 166, 0.1);
            color: #95a5a6;
        }
        
        .btn-action {
            padding: 5px 12px;
            margin: 0 3px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        
        .form-control-sm {
            max-width: 200px;
        }
        
        .order-input {
            width: 80px;
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
            <h4>Contact Information Management</h4>
            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addContactModal">
                <i class="fas fa-plus-circle me-2"></i> Add New Contact Info
            </button>
        </div>

        <!-- Contact Info Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Entries</h6>
                    <h3 class="mb-0"><?php echo count($contact_info); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Active</h6>
                    <h3 class="mb-0"><?php echo count(array_filter($contact_info, function($info) { return $info['is_active'] == 1; })); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Email Types</h6>
                    <h3 class="mb-0"><?php echo count(array_filter($contact_info, function($info) { return $info['info_type'] === 'email'; })); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Phone Types</h6>
                    <h3 class="mb-0"><?php echo count(array_filter($contact_info, function($info) { return $info['info_type'] === 'phone'; })); ?></h3>
                </div>
            </div>
        </div>

        <div class="table-container">
            <form method="POST" id="contactForm">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Key</th>
                                <th>Value</th>
                                <th>Icon</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contact_info as $contact): ?>
                                <tr>
                                    <td>
                                        <span class="type-badge type-<?php echo $contact['info_type']; ?>">
                                            <?php echo ucfirst($contact['info_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               name="key_<?php echo $contact['id']; ?>" 
                                               value="<?php echo htmlspecialchars($contact['info_key']); ?>"
                                               readonly>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               class="form-control" 
                                               name="contact_<?php echo $contact['id']; ?>" 
                                               value="<?php echo htmlspecialchars($contact['info_value']); ?>">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               name="icon_<?php echo $contact['id']; ?>" 
                                               value="<?php echo htmlspecialchars($contact['icon_class']); ?>">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               class="form-control order-input" 
                                               name="order_<?php echo $contact['id']; ?>" 
                                               value="<?php echo $contact['display_order']; ?>" 
                                               min="1">
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="active_<?php echo $contact['id']; ?>"
                                                   <?php echo $contact['is_active'] ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-action btn-outline-danger"
                                                onclick="deleteContact(<?php echo $contact['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" name="save_all" class="btn btn-admin">
                        <i class="fas fa-save me-2"></i> Save All Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Contact Modal -->
    <div class="modal fade" id="addContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Contact Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="admin_contact.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="new_info_type" required>
                                <option value="">Select Type</option>
                                <?php foreach ($info_types as $type): ?>
                                    <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Key *</label>
                            <input type="text" class="form-control" name="new_info_key" required placeholder="e.g., support_email, phone_number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Value *</label>
                            <input type="text" class="form-control" name="new_info_value" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon Class (FontAwesome)</label>
                            <input type="text" class="form-control" name="new_icon_class" placeholder="e.g., fas fa-envelope">
                            <small class="text-muted">Optional. Use FontAwesome classes.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="new_display_order" min="1" value="1">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="new_is_active" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_all" class="btn btn-admin">Add Contact Info</button>
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

        function deleteContact(contactId) {
            if (confirm('Are you sure you want to delete this contact information?')) {
                window.location.href = 'admin_contact.php?action=delete&id=' + contactId;
            }
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