<?php
$pageTitle = 'Manage Bookings - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

// You'll need to add these functions to your Database class:
// - getBookingsWithDetails()
// - getBookingStatistics()
// - updateBookingStatus()

// For now, let's create a simple version
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Booking Management</h5>
                <div class="btn-group">
                    <button class="btn btn-outline-primary">Today</button>
                    <button class="btn btn-outline-primary">This Week</button>
                    <button class="btn btn-outline-primary">This Month</button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="filterDate">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterTheatre">
                            <option value="">All Theatres</option>
                            <!-- Add theatre options -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                
                <!-- Bookings Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Movie</th>
                                <th>Theatre</th>
                                <th>Show Date</th>
                                <th>Seats</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Example - implement getBookingsWithDetails() in Database class
                            // $bookings = $db->getBookingsWithDetails();
                            // foreach($bookings as $booking):
                            ?>
                            <tr>
                                <td>#BK001</td>
                                <td>John Doe<br><small>john@example.com</small></td>
                                <td>Avengers: Endgame</td>
                                <td>Theatre 1</td>
                                <td>2024-01-10<br><small>18:30 PM</small></td>
                                <td>A1, A2, A3</td>
                                <td>$45.00</td>
                                <td>
                                    <select class="form-select form-select-sm status-select" data-booking-id="1">
                                        <option value="confirmed" selected>Confirmed</option>
                                        <option value="pending">Pending</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewBookingModal">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php // endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Booking Modal -->
<div class="modal fade" id="viewBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Booking details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update booking status
    $('.status-select').change(function() {
        var bookingId = $(this).data('booking-id');
        var newStatus = $(this).val();
        
        $.ajax({
            url: 'ajax/update_booking_status.php',
            method: 'POST',
            data: {
                booking_id: bookingId,
                status: newStatus
            },
            success: function(response) {
                alert('Booking status updated!');
            }
        });
    });
});
</script>

<?php require_once '../footer.php'; ?>