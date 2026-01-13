<?php
$pageTitle = 'Contact Information - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

$contactInfo = $db->getAllContactInfo();

// Handle contact info updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update') {
            foreach ($_POST['contact'] as $id => $data) {
                $query = "UPDATE contact_info SET 
                         info_value = :value,
                         icon_class = :icon_class,
                         display_order = :display_order,
                         is_active = :is_active
                         WHERE id = :id";
                
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':value' => $data['value'],
                    ':icon_class' => $data['icon_class'],
                    ':display_order' => $data['display_order'],
                    ':is_active' => isset($data['is_active']) ? 1 : 0,
                    ':id' => $id
                ]);
            }
            echo '<script>alert("Contact information updated successfully!"); window.location.reload();</script>';
        }
        
        if ($_POST['action'] == 'add') {
            $query = "INSERT INTO contact_info (info_type, info_key, info_value, icon_class, display_order) 
                     VALUES (:info_type, :info_key, :info_value, :icon_class, :display_order)";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':info_type' => $_POST['info_type'],
                ':info_key' => $_POST['info_key'],
                ':info_value' => $_POST['info_value'],
                ':icon_class' => $_POST['icon_class'],
                ':display_order' => $_POST['display_order']
            ]);
            
            echo '<script>alert("Contact item added successfully!"); window.location.reload();</script>';
        }
        
        if ($_POST['action'] == 'delete') {
            $query = "DELETE FROM contact_info WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $_POST['id']]);
            echo '<script>alert("Contact item deleted successfully!"); window.location.reload();</script>';
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Contact Information Management</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                    <i class="fas fa-plus"></i> Add New Item
                </button>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="table-responsive">
                        <table class="table table-hover" id="contactTable">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Key</th>
                                    <th>Value</th>
                                    <th>Icon Class</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($contactInfo as $info): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?php echo getTypeBadgeColor($info['info_type']); ?>">
                                            <?php echo ucfirst($info['info_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($info['info_key']); ?></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="contact[<?php echo $info['id']; ?>][value]" 
                                               value="<?php echo htmlspecialchars($info['info_value']); ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="contact[<?php echo $info['id']; ?>][icon_class]" 
                                               value="<?php echo htmlspecialchars($info['icon_class']); ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="contact[<?php echo $info['id']; ?>][display_order]" 
                                               value="<?php echo $info['display_order']; ?>" style="width: 70px;">
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="contact[<?php echo $info['id']; ?>][is_active]" 
                                                   value="1" <?php echo $info['is_active'] == 1 ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this item?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" name="action" value="update">
                            <i class="fas fa-save"></i> Save All Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Contact Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Info Type *</label>
                        <select class="form-select" name="info_type" required>
                            <option value="">Select Type</option>
                            <option value="address">Address</option>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                            <option value="social">Social Media</option>
                            <option value="hours">Business Hours</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Info Key *</label>
                        <input type="text" class="form-control" name="info_key" required 
                               placeholder="e.g., main_address, support_phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Info Value *</label>
                        <input type="text" class="form-control" name="info_value" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon Class</label>
                        <input type="text" class="form-control" name="icon_class" 
                               placeholder="e.g., fas fa-map-marker-alt">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="display_order" min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="add">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
function getTypeBadgeColor($type) {
    $colors = [
        'address' => 'primary',
        'phone' => 'success',
        'email' => 'info',
        'social' => 'warning',
        'hours' => 'secondary'
    ];
    return $colors[$type] ?? 'dark';
}
?>

<?php require_once '../footer.php'; ?>