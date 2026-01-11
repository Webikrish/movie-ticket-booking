<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['show_id'])) {
    header('Location: movies.php');
    exit();
}

$show_id = (int)$_POST['show_id'];
$selected_seats = isset($_POST['selected_seats']) ? sanitize($_POST['selected_seats']) : '';
$total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;

if(empty($selected_seats) || $total_amount <= 0) {
    header('Location: theatres.php');
    exit();
}

$db = new Database();

// Get show details
$db->query("SELECT s.*, m.title, m.poster_image, t.name as theatre_name, 
            t.address as theatre_address, t.city, scr.screen_name
            FROM shows s
            JOIN movies m ON s.movie_id = m.id
            JOIN theatres t ON s.theatre_id = t.id
            JOIN screens scr ON s.screen_id = scr.id
            WHERE s.id = ?");
$db->bind(1, $show_id);
$show = $db->single();

if(!$show) {
    header('Location: movies.php');
    exit();
}

// Convert seat string to array
$seats_array = explode(',', $selected_seats);
$seat_count = count($seats_array);

// Calculate taxes (18% GST)
$tax_rate = 0.18;
$tax_amount = $total_amount * $tax_rate;
$grand_total = $total_amount + $tax_amount;

// Store booking data in session for payment
$_SESSION['booking_data'] = [
    'show_id' => $show_id,
    'selected_seats' => $selected_seats,
    'seat_count' => $seat_count,
    'total_amount' => $total_amount,
    'tax_amount' => $tax_amount,
    'grand_total' => $grand_total
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Summary - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .summary-card {
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .summary-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
        }
        
        .seat-badge {
            font-size: 1rem;
            padding: 8px 12px;
            margin: 5px;
            border-radius: 5px;
        }
        
        .price-breakdown {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dashed #dee2e6;
        }
        
        .price-row.total {
            border-bottom: 2px solid var(--primary-color);
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            .qr-code {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h2>Booking Summary</h2>
                    <p class="text-muted">Review your booking details before proceeding to payment</p>
                </div>
                
                <div class="summary-card">
                    <div class="summary-header">
                        <h4 class="mb-0">Booking Details</h4>
                    </div>
                    
                    <div class="p-4">
                        <!-- Movie Info -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <img src="../assets/uploads/movies/<?php echo $show->poster_image; ?>" 
                                     alt="<?php echo $show->title; ?>" class="img-fluid rounded">
                            </div>
                            <div class="col-md-9">
                                <h4><?php echo $show->title; ?></h4>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <p><strong>Theatre:</strong> <?php echo $show->theatre_name; ?></p>
                                        <p><strong>Address:</strong> <?php echo $show->theatre_address; ?>, <?php echo $show->city; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Screen:</strong> <?php echo $show->screen_name; ?></p>
                                        <p><strong>Date & Time:</strong> 
                                            <?php echo formatDate($show->show_date); ?> - 
                                            <?php echo formatTime($show->show_time); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Seats Selection -->
                        <div class="mb-4">
                            <h5>Selected Seats (<?php echo $seat_count; ?>)</h5>
                            <div class="d-flex flex-wrap mt-3">
                                <?php foreach($seats_array as $seat): ?>
                                <span class="seat-badge bg-primary"><?php echo trim($seat); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Price Breakdown -->
                        <div class="price-breakdown">
                            <h5 class="mb-3">Price Breakdown</h5>
                            
                            <div class="price-row">
                                <span>Ticket Price (<?php echo $seat_count; ?> seats)</span>
                                <span>₹<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                            
                            <div class="price-row">
                                <span>Convenience Fee</span>
                                <span>₹0.00</span>
                            </div>
                            
                            <div class="price-row">
                                <span>GST (18%)</span>
                                <span>₹<?php echo number_format($tax_amount, 2); ?></span>
                            </div>
                            
                            <div class="price-row total mt-3">
                                <span>Total Amount</span>
                                <span>₹<?php echo number_format($grand_total, 2); ?></span>
                            </div>
                        </div>
                        
                        <!-- Terms & Conditions -->
                        <div class="mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the 
                                    <a href="#" class="text-decoration-none">Terms & Conditions</a> and 
                                    <a href="#" class="text-decoration-none">Cancellation Policy</a>.
                                    I understand that tickets once booked cannot be cancelled or refunded.
                                </label>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between mt-5">
                            <a href="seat-selection.php?show_id=<?php echo $show_id; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Seat Selection
                            </a>
                            
                            <button type="button" id="proceedToPayment" class="btn btn-primary btn-lg">
                                Proceed to Payment <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Info -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-shield-check display-6 text-primary mb-3"></i>
                                <h6>100% Secure Payment</h6>
                                <p class="small text-muted">Your payment information is protected with encryption</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-phone display-6 text-primary mb-3"></i>
                                <h6>Mobile Ticket</h6>
                                <p class="small text-muted">Show QR code at theatre. No need to print</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-headset display-6 text-primary mb-3"></i>
                                <h6>24/7 Support</h6>
                                <p class="small text-muted">Contact us for any booking related queries</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="qr-code mb-4">
                        <i class="bi bi-qr-code-scan display-1 text-muted"></i>
                    </div>
                    <h5 class="mb-3">Final Amount: ₹<?php echo number_format($grand_total, 2); ?></h5>
                    <p class="text-muted">Please confirm to proceed to payment gateway</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="payment.php" method="POST">
                        <input type="hidden" name="confirm_booking" value="1">
                        <button type="submit" class="btn btn-primary">Confirm & Pay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('proceedToPayment').addEventListener('click', function() {
            const termsChecked = document.getElementById('terms').checked;
            
            if(!termsChecked) {
                alert('Please agree to the Terms & Conditions to proceed');
                return;
            }
            
            // Check seat availability again
            fetch('../api/check-seats-bulk.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    show_id: <?php echo $show_id; ?>,
                    seats: '<?php echo $selected_seats; ?>',
                    user_id: <?php echo $_SESSION['user_id']; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.available) {
                    // Show confirmation modal
                    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                    modal.show();
                } else {
                    alert('Some of your selected seats are no longer available. Please select different seats.');
                    window.location.href = 'seat-selection.php?show_id=<?php echo $show_id; ?>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
        
        // Auto-check seat availability every 30 seconds
        setInterval(function() {
            fetch('../api/check-seats-bulk.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    show_id: <?php echo $show_id; ?>,
                    seats: '<?php echo $selected_seats; ?>',
                    user_id: <?php echo $_SESSION['user_id']; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if(!data.available) {
                    alert('Your selected seats are no longer available. Redirecting to seat selection...');
                    window.location.href = 'seat-selection.php?show_id=<?php echo $show_id; ?>';
                }
            });
        }, 30000);
        
        // Auto-expire booking after 10 minutes
        setTimeout(function() {
            if(!document.querySelector('#confirmationModal').classList.contains('show')) {
                alert('Your booking session has expired. Please start again.');
                window.location.href = 'movies.php';
            }
        }, 10 * 60 * 1000);
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>