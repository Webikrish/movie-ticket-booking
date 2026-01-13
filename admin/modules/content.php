<?php
$pageTitle = 'Content Management - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

// Get all content
$query = "SELECT * FROM about_content ORDER BY display_order ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$contentItems = $stmt->fetchAll();

// Handle content actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    ':section_title' => $_POST['section_title'],
                    ':section_content' => $_POST['section_content'],
                    ':section_type' => $_POST['section_type'],
                    ':image_url' => $_POST['image_url'],
                    ':icon_class' => $_POST['icon_class'],
                    ':display_order' => $_POST['display_order']
                ];
                
                $query = "INSERT INTO about_content (section_title, section_content, section_type, image_url, icon_class, display_order) 
                         VALUES (:section_title, :section_content, :section_type, :image_url, :icon_class, :display_order)";
                $stmt = $db->prepare($query);
                if ($stmt->execute($data)) {
                    echo '<script>alert("Content added successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $data = [
                    ':section_title' => $_POST['section_title'],
                    ':section_content' => $_POST['section_content'],
                    ':section_type' => $_POST['section_type'],
                    ':image_url' => $_POST['image_url'],
                    ':icon_class' => $_POST['icon_class'],
                    ':display_order' => $_POST['display_order'],
                    ':is_active' => isset($_POST['is_active']) ? 1 : 0,
                    ':id' => $id
                ];
                
                $query = "UPDATE about_content SET 
                         section_title = :section_title,
                         section_content = :section_content,
                         section_type = :section_type,
                         image_url = :image_url,
                         icon_class = :icon_class,
                         display_order = :display_order,
                         is_active = :is_active
                         WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute($data)) {
                    echo '<script>alert("Content updated successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'delete':
                $query = "DELETE FROM about_content WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute([':id' => $_POST['id']])) {
                    echo '<script>alert("Content deleted successfully!"); window.location.reload();</script>';
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
                <h5>Content Management (About Page)</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContentModal">
                    <i class="fas fa-plus"></i> Add New Content
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($contentItems as $item): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><?php echo htmlspecialchars($item['section_title']); ?></h6>
                                <span class="badge bg-<?php echo getSectionTypeBadge($item['section_type']); ?>">
                                    <?php echo ucfirst($item['section_type']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <?php if ($item['image_url']): ?>
                                <img src="<?php echo $item['image_url']; ?>" 
                                     class="img-fluid rounded mb-3" 
                                     alt="<?php echo htmlspecialchars($item['section_title']); ?>"
                                     style="height: 150px; width: 100%; object-fit: cover;">
                                <?php endif; ?>
                                
                                <p class="card-text text-muted">
                                    <?php echo substr(strip_tags($item['section_content']), 0, 150) . '...'; ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if ($item['icon_class']): ?>
                                        <span class="text-primary">
                                            <i class="<?php echo $item['icon_class']; ?>"></i>
                                        </span>
                                        <?php endif; ?>
                                        <small class="text-muted">Order: <?php echo $item['display_order']; ?></small>
                                    </div>
                                    <span class="badge bg-<?php echo $item['is_active'] == 1 ? 'success' : 'danger'; ?>">
                                        <?php echo $item['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewContentModal"
                                            data-id="<?php echo $item['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($item['section_title']); ?>"
                                            data-content="<?php echo htmlspecialchars($item['section_content']); ?>"
                                            data-type="<?php echo $item['section_type']; ?>"
                                            data-image_url="<?php echo $item['image_url']; ?>"
                                            data-icon_class="<?php echo $item['icon_class']; ?>"
                                            data-display_order="<?php echo $item['display_order']; ?>"
                                            data-is_active="<?php echo $item['is_active']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editContentModal"
                                            data-id="<?php echo $item['id']; ?>"
                                            data-section_title="<?php echo htmlspecialchars($item['section_title']); ?>"
                                            data-section_content="<?php echo htmlspecialchars($item['section_content']); ?>"
                                            data-section_type="<?php echo $item['section_type']; ?>"
                                            data-image_url="<?php echo $item['image_url']; ?>"
                                            data-icon_class="<?php echo $item['icon_class']; ?>"
                                            data-display_order="<?php echo $item['display_order']; ?>"
                                            data-is_active="<?php echo $item['is_active']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this content?')">
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

<!-- Add Content Modal -->
<div class="modal fade" id="addContentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Section Title *</label>
                        <input type="text" class="form-control" name="section_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea class="form-control" name="section_content" rows="5" required></textarea>
                        <small class="text-muted">You can use HTML tags for formatting.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Section Type *</label>
                                <select class="form-select" name="section_type" required>
                                    <option value="">Select Type</option>
                                    <option value="mission">Mission</option>
                                    <option value="vision">Vision</option>
                                    <option value="story">Story</option>
                                    <option value="team">Team</option>
                                    <option value="facilities">Facilities</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" min="0" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Image URL</label>
                                <input type="url" class="form-control" name="image_url">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Icon Class</label>
                                <input type="text" class="form-control" name="icon_class" 
                                       placeholder="e.g., fas fa-film">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="add">Add Content</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Content Modal -->
<div class="modal fade" id="editContentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Section Title *</label>
                        <input type="text" class="form-control" name="section_title" id="edit-section_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea class="form-control" name="section_content" id="edit-section_content" rows="5" required></textarea>
                        <small class="text-muted">You can use HTML tags for formatting.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Section Type *</label>
                                <select class="form-select" name="section_type" id="edit-section_type" required>
                                    <option value="">Select Type</option>
                                    <option value="mission">Mission</option>
                                    <option value="vision">Vision</option>
                                    <option value="story">Story</option>
                                    <option value="team">Team</option>
                                    <option value="facilities">Facilities</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" class="form-control" name="display_order" id="edit-display_order" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Image URL</label>
                                <input type="url" class="form-control" name="image_url" id="edit-image_url">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Icon Class</label>
                                <input type="text" class="form-control" name="icon_class" id="edit-icon_class">
                            </div>
                        </div>
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
                    <button type="submit" class="btn btn-primary" name="action" value="edit">Update Content</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Content Modal -->
<div class="modal fade" id="viewContentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Content Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <h4 id="view-title" class="text-primary"></h4>
                        <span id="view-type" class="badge bg-success"></span>
                    </div>
                </div>
                <div class="row">
                    <?php if ($item['image_url']): ?>
                    <div class="col-md-4">
                        <img id="view-image_url" src="" alt="Content Image" class="img-fluid rounded">
                    </div>
                    <div class="col-md-8">
                    <?php else: ?>
                    <div class="col-md-12">
                    <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Content:</label>
                            <div id="view-content" class="p-3 bg-light rounded"></div>
                        </div>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Icon Class:</th>
                                <td id="view-icon_class"></td>
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
    // Edit Content Modal
    $('#editContentModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#edit-id').val(button.data('id'));
        $('#edit-section_title').val(button.data('section_title'));
        $('#edit-section_content').val(button.data('section_content'));
        $('#edit-section_type').val(button.data('section_type'));
        $('#edit-image_url').val(button.data('image_url'));
        $('#edit-icon_class').val(button.data('icon_class'));
        $('#edit-display_order').val(button.data('display_order'));
        $('#edit-is_active').prop('checked', button.data('is_active') == 1);
    });
    
    // View Content Modal
    $('#viewContentModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#view-title').text(button.data('title'));
        $('#view-type').removeClass().addClass('badge').text(button.data('type'));
        
        // Set badge color based on type
        var typeColors = {
            'mission': 'bg-primary',
            'vision': 'bg-success',
            'story': 'bg-info',
            'team': 'bg-warning',
            'facilities': 'bg-danger'
        };
        $('#view-type').addClass(typeColors[button.data('type')] || 'bg-secondary');
        
        $('#view-content').html(button.data('content'));
        $('#view-icon_class').html('<i class="' + button.data('icon_class') + '"></i> ' + button.data('icon_class'));
        $('#view-display_order').text(button.data('display_order'));
        
        if (button.data('image_url')) {
            $('#view-image_url').attr('src', button.data('image_url')).parent().show();
        } else {
            $('#view-image_url').parent().hide();
        }
        
        if (button.data('is_active') == 1) {
            $('#view-is_active').removeClass('bg-danger').addClass('bg-success').text('Active');
        } else {
            $('#view-is_active').removeClass('bg-success').addClass('bg-danger').text('Inactive');
        }
    });
});
</script>

<?php
function getSectionTypeBadge($type) {
    $colors = [
        'mission' => 'primary',
        'vision' => 'success',
        'story' => 'info',
        'team' => 'warning',
        'facilities' => 'danger'
    ];
    return $colors[$type] ?? 'secondary';
}
?>

<?php require_once '../footer.php'; ?>