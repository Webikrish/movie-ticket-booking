<?php
$pageTitle = 'Manage Slider - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

$sliderImages = $db->getAllSliderImages();

// Handle slider actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    ':title' => $_POST['title'],
                    ':description' => $_POST['description'],
                    ':image_url' => $_POST['image_url'],
                    ':button_text' => $_POST['button_text'],
                    ':button_action' => $_POST['button_action'],
                    ':display_order' => $_POST['display_order']
                ];
                
                if ($db->addSliderImage($data)) {
                    echo '<script>alert("Slider image added successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $data = [
                    ':title' => $_POST['title'],
                    ':description' => $_POST['description'],
                    ':image_url' => $_POST['image_url'],
                    ':button_text' => $_POST['button_text'],
                    ':button_action' => $_POST['button_action'],
                    ':display_order' => $_POST['display_order'],
                    ':is_active' => isset($_POST['is_active']) ? 1 : 0,
                    ':id' => $id
                ];
                
                if ($db->updateSliderImage($id, $data)) {
                    echo '<script>alert("Slider image updated successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'delete':
                if ($db->deleteSliderImage($_POST['id'])) {
                    echo '<script>alert("Slider image deleted successfully!"); window.location.reload();</script>';
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
                <h5>Slider Management</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSliderModal">
                    <i class="fas fa-plus"></i> Add New Slide
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($sliderImages as $slide): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo $slide['image_url']; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($slide['title']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($slide['title']); ?></h5>
                                <p class="card-text text-muted small"><?php echo substr($slide['description'], 0, 100) . '...'; ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-<?php echo $slide['is_active'] == 1 ? 'success' : 'danger'; ?>">
                                        <?php echo $slide['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <span class="badge bg-info">
                                        Order: <?php echo $slide['display_order']; ?>
                                    </span>
                                </div>
                                
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewSliderModal"
                                            data-id="<?php echo $slide['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($slide['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($slide['description']); ?>"
                                            data-image_url="<?php echo $slide['image_url']; ?>"
                                            data-button_text="<?php echo htmlspecialchars($slide['button_text']); ?>"
                                            data-button_action="<?php echo $slide['button_action']; ?>"
                                            data-display_order="<?php echo $slide['display_order']; ?>"
                                            data-is_active="<?php echo $slide['is_active']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editSliderModal"
                                            data-id="<?php echo $slide['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($slide['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($slide['description']); ?>"
                                            data-image_url="<?php echo $slide['image_url']; ?>"
                                            data-button_text="<?php echo htmlspecialchars($slide['button_text']); ?>"
                                            data-button_action="<?php echo $slide['button_action']; ?>"
                                            data-display_order="<?php echo $slide['display_order']; ?>"
                                            data-is_active="<?php echo $slide['is_active']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this slide?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Slider Modal -->
<div class="modal fade" id="addSliderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Slide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL *</label>
                        <input type="url" class="form-control" name="image_url" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Button Text *</label>
                                <input type="text" class="form-control" name="button_text" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Button Action</label>
                                <select class="form-select" name="button_action">
                                    <option value="book_tickets">Book Tickets</option>
                                    <option value="explore_theatres">Explore Theatres</option>
                                    <option value="view_offers">View Offers</option>
                                    <option value="custom_link">Custom Link</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="display_order" min="1" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="add">Add Slide</button>
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
                <h5 class="modal-title">Edit Slide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" id="edit-title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" id="edit-description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL *</label>
                        <input type="url" class="form-control" name="image_url" id="edit-image_url" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Button Text *</label>
                                <input type="text" class="form-control" name="button_text" id="edit-button_text" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Button Action</label>
                                <select class="form-select" name="button_action" id="edit-button_action">
                                    <option value="book_tickets">Book Tickets</option>
                                    <option value="explore_theatres">Explore Theatres</option>
                                    <option value="view_offers">View Offers</option>
                                    <option value="custom_link">Custom Link</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="edit-display_order" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="edit-is_active" value="1">
                                    <label class="form-check-label" for="edit-is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="edit">Update Slide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Slider Modal -->
<div class="modal fade" id="viewSliderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Slide Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img id="view-image_url" src="" alt="Slide Image" class="img-fluid rounded">
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Title:</th>
                                <td id="view-title"></td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td id="view-description"></td>
                            </tr>
                            <tr>
                                <th>Button Text:</th>
                                <td id="view-button_text"></td>
                            </tr>
                            <tr>
                                <th>Button Action:</th>
                                <td id="view-button_action"></td>
                            </tr>
                            <tr>
                                <th>Display Order:</th>
                                <td id="view-display_order"></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span id="view-is_active" class="badge bg-success">Active</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit Slider Modal
    $('#editSliderModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#edit-id').val(button.data('id'));
        $('#edit-title').val(button.data('title'));
        $('#edit-description').val(button.data('description'));
        $('#edit-image_url').val(button.data('image_url'));
        $('#edit-button_text').val(button.data('button_text'));
        $('#edit-button_action').val(button.data('button_action'));
        $('#edit-display_order').val(button.data('display_order'));
        $('#edit-is_active').prop('checked', button.data('is_active') == 1);
    });
    
    // View Slider Modal
    $('#viewSliderModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#view-image_url').attr('src', button.data('image_url'));
        $('#view-title').text(button.data('title'));
        $('#view-description').text(button.data('description'));
        $('#view-button_text').text(button.data('button_text'));
        $('#view-button_action').text(button.data('button_action'));
        $('#view-display_order').text(button.data('display_order'));
        
        if (button.data('is_active') == 1) {
            $('#view-is_active').removeClass('bg-danger').addClass('bg-success').text('Active');
        } else {
            $('#view-is_active').removeClass('bg-success').addClass('bg-danger').text('Inactive');
        }
    });
});
</script>

<?php require_once '../footer.php'; ?>