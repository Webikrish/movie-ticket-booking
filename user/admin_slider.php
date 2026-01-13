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
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $slider_id = $_GET['id'] ?? 0;

    if ($slider_id && $database->deleteSlider($slider_id)) {
        header('Location: admin_slider.php?success=Slider deleted successfully');
        exit();
    } else {
        header('Location: admin_slider.php?error=Failed to delete slider');
        exit();
    }
}




// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $slider_id = $_GET['toggle_status'];
    $current_status = $_GET['current_status'];
    $new_status = $current_status == 1 ? 0 : 1;
    
    if ($database->updateSliderStatus($slider_id, $new_status)) {
        header('Location: admin_slider.php?success=Slider status updated');
        exit();
    } else {
        header('Location: admin_slider.php?error=Failed to update slider status');
        exit();
    }
}

// Get all slider images
$slider_images = $database->getAllSliderImages();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slider Management - CinemaKrish Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        <?php include 'admin_styles.css'; ?>
        .slider-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .slider-card:hover {
            transform: translateY(-5px);
        }
        
        .slider-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .slider-actions {
            padding: 15px;
        }
        
        .btn-action {
            padding: 5px 12px;
            margin: 0 3px;
            border-radius: 5px;
            font-size: 0.85rem;
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
            <h4>Slider Management</h4>
            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addSliderModal">
                <i class="fas fa-plus-circle me-2"></i> Add New Slider
            </button>
        </div>

        <!-- Slider Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Sliders</h6>
                    <h3 class="mb-0"><?php echo count($slider_images); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Active</h6>
                    <h3 class="mb-0"><?php echo count(array_filter($slider_images, function($slider) { return $slider['is_active'] == 1; })); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Inactive</h6>
                    <h3 class="mb-0"><?php echo count(array_filter($slider_images, function($slider) { return $slider['is_active'] == 0; })); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Max Order</h6>
                    <h3 class="mb-0"><?php echo count($slider_images) > 0 ? max(array_column($slider_images, 'display_order')) : 0; ?></h3>
                </div>
            </div>
        </div>

        <!-- Sliders Grid -->
        <div class="row">
            <?php foreach ($slider_images as $slider): ?>
                <div class="col-md-4 mb-4">
                    <div class="slider-card">
                        <img src="<?php echo htmlspecialchars($slider['image_url']); ?>" 
                             class="slider-image" 
                             alt="<?php echo htmlspecialchars($slider['title']); ?>"
                             onerror="this.src='https://via.placeholder.com/400x200?text=Image+Not+Found'">
                        
                        <div class="slider-actions">
                            <h6 class="mb-2"><?php echo htmlspecialchars($slider['title']); ?></h6>
                            <p class="text-muted small mb-2">
                                <?php echo htmlspecialchars(substr($slider['description'], 0, 80)); ?>
                                <?php echo strlen($slider['description']) > 80 ? '...' : ''; ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-<?php echo $slider['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $slider['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                <small class="text-muted">
                                    Order: <?php echo $slider['display_order']; ?>
                                </small>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <!-- <button class="btn btn-sm btn-action btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editSliderModal"
                                        onclick="loadSliderData(<?php echo $slider['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button> -->
                                
                                <a href="admin_slider.php?toggle_status=<?php echo $slider['id']; ?>&current_status=<?php echo $slider['is_active']; ?>"
                                   class="btn btn-sm btn-action btn-outline-<?php echo $slider['is_active'] ? 'warning' : 'success'; ?>">
                                    <?php echo $slider['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                </a>
                                
                                <!-- <button class="btn btn-sm btn-action btn-outline-danger" 
        onclick="deleteSlider(<?php echo $slider['id']; ?>)">
    <i class="fas fa-trash"></i>
</button> -->

                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Slider Modal -->
    <div class="modal fade" id="addSliderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Slider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_slider.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL *</label>
                            <input type="text" class="form-control" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" min="1">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_slider" class="btn btn-admin">Add Slider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Slider Modal -->
    <div class="modal fade" id="editSliderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Slider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_slider.php" method="POST">
                    <input type="hidden" id="edit_slider_id" name="slider_id" value="">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL *</label>
                            <input type="text" class="form-control" id="edit_image_url" name="image_url" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="edit_display_order" name="display_order" min="1">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_slider" class="btn btn-admin">Update Slider</button>
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

        function loadSliderData(sliderId) {
    fetch('ajax_get_slider.php?id=' + sliderId)
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit_slider_id').value = data.id;
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_image_url').value = data.image_url;
            document.getElementById('edit_display_order').value = data.display_order;
            document.getElementById('edit_is_active').checked = data.is_active == 1;
        });
}


if (isset($_GET['action']) && $_GET['action'] == 'toggle_status') {
    $slider_id = $_GET['id'];
    $new_status = $_GET['status']; // 0 or 1

    if ($database->updateSliderStatus($slider_id, $new_status)) {
        header('Location: admin_slider.php?success=Slider status updated');
        exit();
    } else {
        header('Location: admin_slider.php?error=Failed to update status');
        exit();
    }
}


        function deleteSlider(sliderId) {
    if (confirm('Are you sure you want to delete this slider?')) {
        window.location.href = 'admin_slider.php?action=delete&id=' + sliderId;
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