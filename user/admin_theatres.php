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
    if ($database->deleteTheatre($id)) {
        header('Location: admin_theatres.php?success=Theatre deleted successfully');
        exit();
    } else {
        header('Location: admin_theatres.php?error=Failed to delete theatre');
        exit();
    }
}

// Get all theatres
$theatres = $database->getAllTheatres();
$cities = $database->getDistinctCities();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theatre Management - CinemaKrish Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        <?php include 'admin_styles.css'; ?>
        .theatre-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .theatre-card:hover {
            transform: translateY(-5px);
        }
        
        .facility-badge {
            margin: 2px;
            padding: 5px 10px;
            background: #f8f9fa;
            border-radius: 20px;
            font-size: 0.85rem;
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
            <h4>Theatre Management</h4>
            <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addTheatreModal">
                <i class="fas fa-plus-circle me-2"></i> Add New Theatre
            </button>
        </div>

        <!-- Theatre Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Theatres</h6>
                    <h3 class="mb-0"><?php echo count($theatres); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Total Screens</h6>
                    <h3 class="mb-0"><?php echo array_sum(array_column($theatres, 'total_screens')); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Cities</h6>
                    <h3 class="mb-0"><?php echo count($cities); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h6 class="text-muted">Avg Screens/Theatre</h6>
                    <h3 class="mb-0"><?php echo count($theatres) > 0 ? round(array_sum(array_column($theatres, 'total_screens')) / count($theatres), 1) : 0; ?></h3>
                </div>
            </div>
        </div>

        <!-- Theatres Grid -->
        <div class="row">
            <?php foreach ($theatres as $theatre): ?>
                <div class="col-md-6 mb-4">
                    <div class="theatre-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($theatre['name']); ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($theatre['location']); ?>
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-city me-2"></i><?php echo htmlspecialchars($theatre['city']); ?>
                                </p>
                            </div>
                            <div>
                                <span class="badge bg-primary p-2"><?php echo $theatre['total_screens']; ?> Screens</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($theatre['facilities'])): ?>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">Facilities:</small>
                                <?php 
                                $facilities = explode(',', $theatre['facilities']);
                                foreach ($facilities as $facility): 
                                    if (trim($facility)): 
                                ?>
                                    <span class="facility-badge"><?php echo trim($facility); ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
    <i class="fas fa-phone me-1"></i>
    <?php echo htmlspecialchars($theatre['phone'] ?? 'N/A'); ?>
</small>

                            </div>
                            <div>
                                <button class="btn btn-sm btn-action btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editTheatreModal"
                                        onclick="loadTheatreData(<?php echo $theatre['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-action btn-outline-danger" 
                                        onclick="deleteTheatre(<?php echo $theatre['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Theatre Modal -->
    <div class="modal fade" id="addTheatreModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Theatre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_theatre.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Theatre Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Location *</label>
                                <input type="text" class="form-control" name="location" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Screens *</label>
                                <input type="number" class="form-control" name="total_screens" required min="1">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Facilities (comma separated)</label>
                                <input type="text" class="form-control" name="facilities" placeholder="e.g., Parking, Food Court, 3D">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_theatre" class="btn btn-admin">Add Theatre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Theatre Modal -->
    <div class="modal fade" id="editTheatreModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Theatre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_theatre.php" method="POST">
                    <input type="hidden" id="edit_theatre_id" name="theatre_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Theatre Name *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" class="form-control" id="edit_city" name="city" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Location *</label>
                                <input type="text" class="form-control" id="edit_location" name="location" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Screens *</label>
                                <input type="number" class="form-control" id="edit_total_screens" name="total_screens" required min="1">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Facilities (comma separated)</label>
                                <input type="text" class="form-control" id="edit_facilities" name="facilities" placeholder="e.g., Parking, Food Court, 3D">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_theatre" class="btn btn-admin">Update Theatre</button>
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

        function loadTheatreData(theatreId) {
            fetch('ajax_get_theatre.php?id=' + theatreId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_theatre_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_city').value = data.city;
                    document.getElementById('edit_location').value = data.location;
                    document.getElementById('edit_phone').value = data.phone;
                    document.getElementById('edit_total_screens').value = data.total_screens;
                    document.getElementById('edit_facilities').value = data.facilities;
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteTheatre(theatreId) {
            if (confirm('Are you sure you want to delete this theatre?')) {
                window.location.href = 'admin_theatres.php?action=delete&id=' + theatreId;
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