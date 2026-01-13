<?php
$pageTitle = 'Manage Theatres - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

// Get all theatres
$query = "SELECT * FROM theatres ORDER BY city, name";
$stmt = $db->prepare($query);
$stmt->execute();
$theatres = $stmt->fetchAll();

// Handle theatre actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    ':name' => $_POST['name'],
                    ':location' => $_POST['location'],
                    ':city' => $_POST['city'],
                    ':total_screens' => $_POST['total_screens'],
                    ':facilities' => $_POST['facilities']
                ];
                
                $query = "INSERT INTO theatres (name, location, city, total_screens, facilities) 
                         VALUES (:name, :location, :city, :total_screens, :facilities)";
                $stmt = $db->prepare($query);
                if ($stmt->execute($data)) {
                    echo '<script>alert("Theatre added successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $data = [
                    ':name' => $_POST['name'],
                    ':location' => $_POST['location'],
                    ':city' => $_POST['city'],
                    ':total_screens' => $_POST['total_screens'],
                    ':facilities' => $_POST['facilities'],
                    ':id' => $id
                ];
                
                $query = "UPDATE theatres SET 
                         name = :name,
                         location = :location,
                         city = :city,
                         total_screens = :total_screens,
                         facilities = :facilities
                         WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute($data)) {
                    echo '<script>alert("Theatre updated successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'delete':
                $query = "DELETE FROM theatres WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute([':id' => $_POST['id']])) {
                    echo '<script>alert("Theatre deleted successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'toggle_status':
                $query = "UPDATE theatres SET is_active = :status WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute([':status' => $_POST['status'], ':id' => $_POST['id']])) {
                    echo '<script>alert("Theatre status updated!"); window.location.reload();</script>';
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
                <h5>Theatre Management</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTheatreModal">
                    <i class="fas fa-plus"></i> Add New Theatre
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="theatresTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Theatre Name</th>
                                <th>Location</th>
                                <th>City</th>
                                <th>Screens</th>
                                <th>Facilities</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($theatres as $theatre): ?>
                            <tr>
                                <td><?php echo $theatre['id']; ?></td>
                                <td><?php echo htmlspecialchars($theatre['name']); ?></td>
                                <td><?php echo htmlspecialchars($theatre['location']); ?></td>
                                <td><?php echo htmlspecialchars($theatre['city']); ?></td>
                                <td><?php echo $theatre['total_screens']; ?></td>
                                <td>
                                    <?php 
                                    $facilities = explode(',', $theatre['facilities']);
                                    foreach($facilities as $facility):
                                        echo '<span class="badge bg-info me-1 mb-1">' . trim($facility) . '</span>';
                                    endforeach;
                                    ?>
                                </td>
                                <td>
                                    <?php if ($theatre['is_active'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewTheatreModal"
                                                data-id="<?php echo $theatre['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($theatre['name']); ?>"
                                                data-location="<?php echo htmlspecialchars($theatre['location']); ?>"
                                                data-city="<?php echo htmlspecialchars($theatre['city']); ?>"
                                                data-screens="<?php echo $theatre['total_screens']; ?>"
                                                data-facilities="<?php echo htmlspecialchars($theatre['facilities']); ?>"
                                                data-status="<?php echo $theatre['is_active']; ?>"
                                                data-created="<?php echo $theatre['created_at']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTheatreModal"
                                                data-id="<?php echo $theatre['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($theatre['name']); ?>"
                                                data-location="<?php echo htmlspecialchars($theatre['location']); ?>"
                                                data-city="<?php echo htmlspecialchars($theatre['city']); ?>"
                                                data-total_screens="<?php echo $theatre['total_screens']; ?>"
                                                data-facilities="<?php echo htmlspecialchars($theatre['facilities']); ?>"
                                                data-is_active="<?php echo $theatre['is_active']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $theatre['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="status" value="<?php echo $theatre['is_active'] == 1 ? 0 : 1; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $theatre['is_active'] == 1 ? 'btn-warning' : 'btn-success'; ?>"
                                                    onclick="return confirm('Change theatre status?')">
                                                <i class="fas <?php echo $theatre['is_active'] == 1 ? 'fa-ban' : 'fa-check'; ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $theatre['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this theatre?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

<!-- Add Theatre Modal -->
<div class="modal fade" id="addTheatreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Theatre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Theatre Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location *</label>
                        <input type="text" class="form-control" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City *</label>
                        <input type="text" class="form-control" name="city" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Screens</label>
                        <input type="number" class="form-control" name="total_screens" min="1" max="20" value="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Facilities (comma separated)</label>
                        <textarea class="form-control" name="facilities" rows="3" 
                                  placeholder="e.g., Dolby Atmos, 4K Projection, Recliner Seats, Food Court"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="add">Add Theatre</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Theatre Modal -->
<div class="modal fade" id="editTheatreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Theatre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Theatre Name *</label>
                        <input type="text" class="form-control" name="name" id="edit-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location *</label>
                        <input type="text" class="form-control" name="location" id="edit-location" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City *</label>
                        <input type="text" class="form-control" name="city" id="edit-city" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Screens</label>
                        <input type="number" class="form-control" name="total_screens" id="edit-total_screens" min="1" max="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Facilities (comma separated)</label>
                        <textarea class="form-control" name="facilities" id="edit-facilities" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit-is_active" value="1">
                            <label class="form-check-label" for="edit-is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="edit">Update Theatre</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Theatre Modal -->
<div class="modal fade" id="viewTheatreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Theatre Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Theatre Name:</th>
                        <td id="view-name"></td>
                    </tr>
                    <tr>
                        <th>Location:</th>
                        <td id="view-location"></td>
                    </tr>
                    <tr>
                        <th>City:</th>
                        <td id="view-city"></td>
                    </tr>
                    <tr>
                        <th>Total Screens:</th>
                        <td id="view-screens"></td>
                    </tr>
                    <tr>
                        <th>Facilities:</th>
                        <td id="view-facilities"></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span id="view-status" class="badge bg-success">Active</span>
                        </td>
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
    // Edit Theatre Modal
    $('#editTheatreModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#edit-id').val(button.data('id'));
        $('#edit-name').val(button.data('name'));
        $('#edit-location').val(button.data('location'));
        $('#edit-city').val(button.data('city'));
        $('#edit-total_screens').val(button.data('total_screens'));
        $('#edit-facilities').val(button.data('facilities'));
        $('#edit-is_active').prop('checked', button.data('is_active') == 1);
    });
    
    // View Theatre Modal
    $('#viewTheatreModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#view-name').text(button.data('name'));
        $('#view-location').text(button.data('location'));
        $('#view-city').text(button.data('city'));
        $('#view-screens').text(button.data('screens'));
        $('#view-facilities').html(button.data('facilities').split(',').map(f => 
            '<span class="badge bg-info me-1 mb-1">' + f.trim() + '</span>'
        ).join(''));
        
        if (button.data('status') == 1) {
            $('#view-status').removeClass('bg-danger').addClass('bg-success').text('Active');
        } else {
            $('#view-status').removeClass('bg-success').addClass('bg-danger').text('Inactive');
        }
        
        $('#view-created').text(new Date(button.data('created')).toLocaleString());
    });
});
</script>

<?php require_once '../footer.php'; ?>