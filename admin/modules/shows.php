<?php
$pageTitle = 'Manage Shows - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

// Get all shows with movie and theatre details
$query = "SELECT s.*, 
                 m.title as movie_title,
                 m.poster_url as movie_poster,
                 t.name as theatre_name,
                 t.location as theatre_location
          FROM shows s
          JOIN movies m ON s.movie_id = m.id
          JOIN theatres t ON s.theatre_id = t.id
          ORDER BY s.show_date DESC, s.show_time ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$shows = $stmt->fetchAll();

// Get movies and theatres for dropdowns
$movies = $db->getNowShowingMovies();
$theatres = $db->getAllTheatres();

// Handle show actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    ':movie_id' => $_POST['movie_id'],
                    ':theatre_id' => $_POST['theatre_id'],
                    ':screen_number' => $_POST['screen_number'],
                    ':show_date' => $_POST['show_date'],
                    ':show_time' => $_POST['show_time'],
                    ':available_seats' => $_POST['total_seats'],
                    ':total_seats' => $_POST['total_seats'],
                    ':ticket_price' => $_POST['ticket_price']
                ];
                
                $query = "INSERT INTO shows (movie_id, theatre_id, screen_number, show_date, show_time, available_seats, total_seats, ticket_price) 
                         VALUES (:movie_id, :theatre_id, :screen_number, :show_date, :show_time, :available_seats, :total_seats, :ticket_price)";
                $stmt = $db->prepare($query);
                if ($stmt->execute($data)) {
                    echo '<script>alert("Show added successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $data = [
                    ':movie_id' => $_POST['movie_id'],
                    ':theatre_id' => $_POST['theatre_id'],
                    ':screen_number' => $_POST['screen_number'],
                    ':show_date' => $_POST['show_date'],
                    ':show_time' => $_POST['show_time'],
                    ':total_seats' => $_POST['total_seats'],
                    ':ticket_price' => $_POST['ticket_price'],
                    ':id' => $id
                ];
                
                $query = "UPDATE shows SET 
                         movie_id = :movie_id,
                         theatre_id = :theatre_id,
                         screen_number = :screen_number,
                         show_date = :show_date,
                         show_time = :show_time,
                         total_seats = :total_seats,
                         ticket_price = :ticket_price
                         WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute($data)) {
                    echo '<script>alert("Show updated successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'delete':
                $query = "DELETE FROM shows WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute([':id' => $_POST['id']])) {
                    echo '<script>alert("Show deleted successfully!"); window.location.reload();</script>';
                }
                break;
                
            case 'toggle_status':
                $query = "UPDATE shows SET is_active = :status WHERE id = :id";
                $stmt = $db->prepare($query);
                if ($stmt->execute([':status' => $_POST['status'], ':id' => $_POST['id']])) {
                    echo '<script>alert("Show status updated!"); window.location.reload();</script>';
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
                <h5>Show Management</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShowModal">
                    <i class="fas fa-plus"></i> Add New Show
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="showsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Movie</th>
                                <th>Theatre</th>
                                <th>Screen</th>
                                <th>Date & Time</th>
                                <th>Seats</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($shows as $show): ?>
                            <tr>
                                <td><?php echo $show['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $show['movie_poster']; ?>" 
                                             alt="<?php echo htmlspecialchars($show['movie_title']); ?>"
                                             style="width: 40px; height: 60px; object-fit: cover; border-radius: 3px; margin-right: 10px;">
                                        <div>
                                            <strong><?php echo htmlspecialchars($show['movie_title']); ?></strong><br>
                                            <small class="text-muted"><?php echo date('d M', strtotime($show['show_date'])); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($show['theatre_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($show['theatre_location']); ?></small>
                                </td>
                                <td>Screen <?php echo $show['screen_number']; ?></td>
                                <td>
                                    <?php echo date('d M Y', strtotime($show['show_date'])); ?><br>
                                    <small class="text-primary"><?php echo date('h:i A', strtotime($show['show_time'])); ?></small>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <?php 
                                        $percentage = ($show['available_seats'] / $show['total_seats']) * 100;
                                        $color = $percentage > 50 ? 'bg-success' : ($percentage > 20 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <div class="progress-bar <?php echo $color; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $percentage; ?>%"
                                             aria-valuenow="<?php echo $percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo $show['available_seats']; ?>/<?php echo $show['total_seats']; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">$<?php echo number_format($show['ticket_price'], 2); ?></span>
                                </td>
                                <td>
                                    <?php if ($show['is_active'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewShowModal"
                                                data-id="<?php echo $show['id']; ?>"
                                                data-movie="<?php echo htmlspecialchars($show['movie_title']); ?>"
                                                data-theatre="<?php echo htmlspecialchars($show['theatre_name']); ?>"
                                                data-screen="<?php echo $show['screen_number']; ?>"
                                                data-date="<?php echo $show['show_date']; ?>"
                                                data-time="<?php echo $show['show_time']; ?>"
                                                data-seats="<?php echo $show['available_seats'] . '/' . $show['total_seats']; ?>"
                                                data-price="<?php echo $show['ticket_price']; ?>"
                                                data-status="<?php echo $show['is_active']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editShowModal"
                                                data-id="<?php echo $show['id']; ?>"
                                                data-movie_id="<?php echo $show['movie_id']; ?>"
                                                data-theatre_id="<?php echo $show['theatre_id']; ?>"
                                                data-screen_number="<?php echo $show['screen_number']; ?>"
                                                data-show_date="<?php echo $show['show_date']; ?>"
                                                data-show_time="<?php echo $show['show_time']; ?>"
                                                data-total_seats="<?php echo $show['total_seats']; ?>"
                                                data-ticket_price="<?php echo $show['ticket_price']; ?>"
                                                data-is_active="<?php echo $show['is_active']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $show['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this show?')">
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

<!-- Add Show Modal -->
<div class="modal fade" id="addShowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Show</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Movie *</label>
                        <select class="form-select" name="movie_id" required>
                            <option value="">Select Movie</option>
                            <?php foreach($movies as $movie): ?>
                            <option value="<?php echo $movie['id']; ?>">
                                <?php echo htmlspecialchars($movie['title']); ?> 
                                (<?php echo htmlspecialchars($movie['language_name']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Theatre *</label>
                        <select class="form-select" name="theatre_id" required>
                            <option value="">Select Theatre</option>
                            <?php foreach($theatres as $theatre): ?>
                            <option value="<?php echo $theatre['id']; ?>">
                                <?php echo htmlspecialchars($theatre['name']); ?> - <?php echo htmlspecialchars($theatre['city']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Screen Number</label>
                                <input type="number" class="form-control" name="screen_number" min="1" max="20" value="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Total Seats</label>
                                <input type="number" class="form-control" name="total_seats" min="50" max="500" value="150">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Show Date *</label>
                                <input type="date" class="form-control" name="show_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Show Time *</label>
                                <input type="time" class="form-control" name="show_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ticket Price ($) *</label>
                        <input type="number" class="form-control" name="ticket_price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="action" value="add">Add Show</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Show Modal -->
<div class="modal fade" id="editShowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Show</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Movie *</label>
                        <select class="form-select" name="movie_id" id="edit-movie_id" required>
                            <option value="">Select Movie</option>
                            <?php foreach($movies as $movie): ?>
                            <option value="<?php echo $movie['id']; ?>">
                                <?php echo htmlspecialchars($movie['title']); ?> 
                                (<?php echo htmlspecialchars($movie['language_name']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Theatre *</label>
                        <select class="form-select" name="theatre_id" id="edit-theatre_id" required>
                            <option value="">Select Theatre</option>
                            <?php foreach($theatres as $theatre): ?>
                            <option value="<?php echo $theatre['id']; ?>">
                                <?php echo htmlspecialchars($theatre['name']); ?> - <?php echo htmlspecialchars($theatre['city']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Screen Number</label>
                                <input type="number" class="form-control" name="screen_number" id="edit-screen_number" min="1" max="20">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Total Seats</label>
                                <input type="number" class="form-control" name="total_seats" id="edit-total_seats" min="50" max="500">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Show Date *</label>
                                <input type="date" class="form-control" name="show_date" id="edit-show_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Show Time *</label>
                                <input type="time" class="form-control" name="show_time" id="edit-show_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ticket Price ($) *</label>
                        <input type="number" class="form-control" name="ticket_price" id="edit-ticket_price" step="0.01" min="0" required>
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
                    <button type="submit" class="btn btn-primary" name="action" value="edit">Update Show</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Show Modal -->
<div class="modal fade" id="viewShowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Show Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Movie:</th>
                        <td id="view-movie"></td>
                    </tr>
                    <tr>
                        <th>Theatre:</th>
                        <td id="view-theatre"></td>
                    </tr>
                    <tr>
                        <th>Screen:</th>
                        <td id="view-screen"></td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td id="view-date"></td>
                    </tr>
                    <tr>
                        <th>Time:</th>
                        <td id="view-time"></td>
                    </tr>
                    <tr>
                        <th>Available Seats:</th>
                        <td id="view-seats"></td>
                    </tr>
                    <tr>
                        <th>Ticket Price:</th>
                        <td>$<span id="view-price"></span></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span id="view-status" class="badge bg-success">Active</span>
                        </td>
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
    // Set minimum date to today for show date
    var today = new Date().toISOString().split('T')[0];
    $('input[name="show_date"]').attr('min', today);
    
    // Edit Show Modal
    $('#editShowModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#edit-id').val(button.data('id'));
        $('#edit-movie_id').val(button.data('movie_id'));
        $('#edit-theatre_id').val(button.data('theatre_id'));
        $('#edit-screen_number').val(button.data('screen_number'));
        $('#edit-show_date').val(button.data('show_date'));
        $('#edit-show_time').val(button.data('show_time'));
        $('#edit-total_seats').val(button.data('total_seats'));
        $('#edit-ticket_price').val(button.data('ticket_price'));
        $('#edit-is_active').prop('checked', button.data('is_active') == 1);
    });
    
    // View Show Modal
    $('#viewShowModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $('#view-movie').text(button.data('movie'));
        $('#view-theatre').text(button.data('theatre'));
        $('#view-screen').text('Screen ' + button.data('screen'));
        $('#view-date').text(new Date(button.data('date')).toLocaleDateString());
        $('#view-time').text(new Date('1970-01-01T' + button.data('time')).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
        $('#view-seats').text(button.data('seats'));
        $('#view-price').text(button.data('price'));
        
        if (button.data('status') == 1) {
            $('#view-status').removeClass('bg-danger').addClass('bg-success').text('Active');
        } else {
            $('#view-status').removeClass('bg-success').addClass('bg-danger').text('Inactive');
        }
    });
});
</script>

<?php require_once '../footer.php'; ?>