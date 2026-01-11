<?php
// customer_details.php
require_once 'session_manager.php';
require_once 'db_connection.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['error_message'] = 'Please login to book tickets';
    header('Location: login.php');
    exit();
}

// Check if seats are selected
if (!isset($_SESSION['selected_show']) || empty($_SESSION['selected_show']['selected_seats'])) {
    $_SESSION['error_message'] = 'Please select seats first';
    header('Location: seat_bookings.php');
    exit();
}

$showData = $_SESSION['selected_show'];
$userId   = $_SESSION['user_id'];
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);






// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_details'])) {
    // Get form data
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $payment_method = $_POST['payment_method'];
    
    // Validate form data
    $errors = [];
    
    if (empty($customer_name)) {
        $errors[] = 'Please enter your name';
    }
    
    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($customer_phone)) {
        $errors[] = 'Please enter your phone number';
    }
    
    if (empty($payment_method)) {
        $errors[] = 'Please select a payment method';
    }
    
    // If no errors, proceed to payment
    if (empty($errors)) {
        // Update session with customer details
        $_SESSION['selected_show']['customer_details'] = [
            'name' => $customer_name,
            'email' => $customer_email,
            'phone' => $customer_phone,
            'payment_method' => $payment_method
        ];
        
        // Generate a unique booking reference
        $booking_ref = 'CK' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
        $_SESSION['selected_show']['booking_ref'] = $booking_ref;
        
        // Store in database first (pending payment)
        try {
            // Begin transaction
            $db->beginTransaction();
            
            // Get show details
            $show_id = $showData['show_id'];
            $stmt = $db->prepare("SELECT * FROM shows WHERE id = ?");
            $stmt->execute([$show_id]);
            $show = $stmt->fetch();
            
            if (!$show) {
                throw new Exception("Show not found");
            }
            
            // Create booking record
            $booking_sql = "INSERT INTO bookings (
                user_id, movie_id, theatre_id, show_id, show_date, show_time, 
                total_seats, seat_numbers, total_amount, payment_method, 
                booking_status, ticket_number, customer_name, customer_email, customer_phone
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($booking_sql);
            
            $seat_numbers = implode(',', $showData['selected_seats']);
            $ticket_number = 'TICKET' . date('YmdHis') . $userId;

            
            $result = $stmt->execute([
    $userId,
    $showData['movie_id'],
    $show['theatre_id'],
    $show_id,
    $showData['show_date'],
    $showData['show_time'],
    $showData['total_seats'],
    $seat_numbers,
    $showData['total_amount'],
    $payment_method,
    'pending',
    $ticket_number,
    $customer_name,
    $customer_email,
    $customer_phone
]);


            
            $booking_id = $db->lastInsertId();
            
            // Update seats as booked
            foreach ($showData['selected_seats'] as $seat) {
                $update_seat_sql = "UPDATE seats SET is_booked = 1, booking_id = ? 
                                   WHERE show_id = ? AND seat_number = ? AND is_booked = 0";
                $stmt = $db->prepare($update_seat_sql);
                $stmt->execute([$booking_id, $show_id, $seat]);
            }
            
            // Update available seats in shows table
            $update_show_sql = "UPDATE shows SET available_seats = available_seats - ? 
                               WHERE id = ? AND available_seats >= ?";
            $stmt = $db->prepare($update_show_sql);
            $stmt->execute([
                $showData['total_seats'],
                $show_id,
                $showData['total_seats']
            ]);
            
            // Commit transaction
            $db->commit();
            
            // Store booking ID in session
            $_SESSION['selected_show']['booking_id'] = $booking_id;
            $_SESSION['selected_show']['ticket_number'] = $ticket_number;
            
            // Redirect to payment page
            header('Location: payment.php');
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error_message'] = 'Booking failed: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details | CinemaKrish</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Oswald:wght@500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-dark: #0a0a0a;
            --secondary-dark: #1a1a1a;
            --accent-red: #d32f2f;
            --accent-gold: #ffc107;
            --text-light: #f8f9fa;
            --text-gray: #adb5bd;
        }

        body {
            background: var(--primary-dark);
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            padding-top: 80px;
        }

        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .booking-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .booking-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: var(--secondary-dark);
            z-index: 1;
        }

        .step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--secondary-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            font-size: 1.2rem;
            border: 3px solid var(--secondary-dark);
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--accent-red);
            border-color: var(--accent-red);
            color: white;
            transform: scale(1.1);
        }

        .step.completed .step-number {
            background: var(--accent-gold);
            border-color: var(--accent-gold);
            color: var(--primary-dark);
        }

        .step-label {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .step.active .step-label {
            color: var(--accent-red);
            font-weight: 600;
        }

        .booking-section {
            background: var(--secondary-dark);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            color: var(--accent-gold);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-red);
        }

        .form-label {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-red);
            color: var(--text-light);
            box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.25);
        }

        .form-control::placeholder {
            color: var(--text-gray);
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-gray);
        }

        /* Booking Summary */
        .booking-summary {
            background: rgba(255, 193, 7, 0.1);
            border-radius: 10px;
            padding: 25px;
            border: 1px solid rgba(255, 193, 7, 0.2);
            position: sticky;
            top: 100px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .summary-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-gold);
            margin-top: 20px;
        }

        .selected-seats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .selected-seat-badge {
            background: var(--accent-red);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .btn-proceed {
            background: linear-gradient(135deg, var(--accent-red) 0%, #9a0007 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-proceed:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(211, 47, 47, 0.3);
            color: white;
        }

        /* Payment Method Cards */
        .payment-method {
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
        }

        .payment-method:hover {
            border-color: var(--accent-gold);
            background: rgba(255, 193, 7, 0.05);
        }

        .payment-method.selected {
            border-color: var(--accent-red);
            background: rgba(211, 47, 47, 0.1);
        }

        .payment-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .payment-details {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .booking-steps {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .booking-steps::before {
                display: none;
            }

            .step {
                width: 100%;
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .step-number {
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .booking-container {
                padding: 10px;
            }

            .booking-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <div class="booking-container">
        <!-- Booking Steps -->
        <div class="booking-steps">
            <div class="step completed">
                <div class="step-number">1</div>
                <div class="step-label">Select Show</div>
            </div>
            <div class="step completed">
                <div class="step-number">2</div>
                <div class="step-label">Choose Seats</div>
            </div>
            <div class="step active">
                <div class="step-number">3</div>
                <div class="step-label">Customer Details</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Payment</div>
            </div>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form method="POST" action="" id="customerForm">
            <div class="row">
                <div class="col-lg-8">
                    <div class="booking-section">
                        <h2 class="section-title">Customer Details</h2>
                        
                        <!-- Customer Information -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="customer_name" 
                                       value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="customer_email" 
                                       value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" name="customer_phone" 
                                       value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Booking Reference</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo isset($_SESSION['selected_show']['booking_ref']) ? $_SESSION['selected_show']['booking_ref'] : 'Will be generated'; ?>" 
                                       readonly>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <h3 class="section-title">Select Payment Method</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="payment-method" onclick="selectPaymentMethod('credit_card')">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               value="credit_card" id="creditCard" checked>
                                        <label class="form-check-label d-flex align-items-center" for="creditCard">
                                            <i class="fas fa-credit-card payment-icon"></i>
                                            <div>
                                                <strong>Credit Card</strong>
                                                <div class="small text-gray">Visa, MasterCard, American Express</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="payment-method" onclick="selectPaymentMethod('debit_card')">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               value="debit_card" id="debitCard">
                                        <label class="form-check-label d-flex align-items-center" for="debitCard">
                                            <i class="fas fa-credit-card payment-icon"></i>
                                            <div>
                                                <strong>Debit Card</strong>
                                                <div class="small text-gray">All major debit cards</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="payment-method" onclick="selectPaymentMethod('paypal')">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               value="paypal" id="paypal">
                                        <label class="form-check-label d-flex align-items-center" for="paypal">
                                            <i class="fab fa-paypal payment-icon"></i>
                                            <div>
                                                <strong>PayPal</strong>
                                                <div class="small text-gray">Pay with your PayPal account</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="payment-method" onclick="selectPaymentMethod('cash')">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               value="cash" id="cash">
                                        <label class="form-check-label d-flex align-items-center" for="cash">
                                            <i class="fas fa-money-bill-wave payment-icon"></i>
                                            <div>
                                                <strong>Pay at Counter</strong>
                                                <div class="small text-gray">Pay cash at theatre counter</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="mt-4 p-3 bg-dark rounded">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label small" for="terms">
                                    I agree to the <a href="terms.php" class="text-gold">Terms & Conditions</a> and 
                                    <a href="privacy.php" class="text-gold">Privacy Policy</a>. I understand that tickets 
                                    are non-refundable and can only be exchanged 24 hours before showtime.
                                </label>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="seat_bookings.php" class="btn btn-outline-light w-100 py-3">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Seats
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" name="confirm_details" class="btn-proceed">
                                    <i class="fas fa-lock me-2"></i> Proceed to Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="col-lg-4">
                    <div class="booking-summary">
                        <h3 class="section-title mb-4">Booking Summary</h3>
                        
                        <div class="summary-item">
                            <span>Movie:</span>
                            <strong><?php echo htmlspecialchars($showData['movie_title']); ?></strong>
                        </div>
                        
                        <div class="summary-item">
                            <span>Date & Time:</span>
                            <div class="text-end">
                                <div><?php echo date('M j, Y', strtotime($showData['show_date'])); ?></div>
                                <small><?php echo date('h:i A', strtotime($showData['show_time'])); ?></small>
                            </div>
                        </div>
                        
                        <div class="summary-item">
                            <span>Theatre:</span>
                            <div class="text-end">
                                <div><?php echo htmlspecialchars($showData['theatre_name']); ?></div>
                                <small><?php echo $showData['location'] . ', ' . $showData['city']; ?></small>
                            </div>
                        </div>
                        
                        <div class="summary-item">
                            <span>Selected Seats (<?php echo $showData['total_seats']; ?>):</span>
                            <div class="selected-seats-container">
                                <?php foreach ($showData['selected_seats'] as $seat): ?>
                                    <span class="selected-seat-badge"><?php echo $seat; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="price-breakdown mt-3 p-3 bg-dark rounded">
                            <?php if (isset($showData['seat_type_counts'])): ?>
                                <?php foreach ($showData['seat_type_counts'] as $type => $count): ?>
                                    <?php if ($count > 0): ?>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small">
                                                <?php echo ucfirst($type); ?> (<?php echo $count; ?> × $<?php echo number_format($showData['seat_type_prices'][$type], 2); ?>)
                                            </span>
                                            <span class="small">$<?php echo number_format($count * $showData['seat_type_prices'][$type], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small">Subtotal:</span>
                                <span class="small">$<?php echo number_format($showData['subtotal'] ?? $showData['ticket_total'] ?? 0, 2); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small">Convenience Fee:</span>
                                <span class="small">$<?php echo number_format($showData['convenience_fee'], 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="summary-item summary-total">
                            <span>Total Amount:</span>
                            <span>$<?php echo number_format($showData['total_amount'], 2); ?></span>
                        </div>
                        
                        <div class="text-center mt-4">
                            <small class="text-gray">
                                <i class="fas fa-shield-alt me-1"></i>
                                100% Secure Payment • SSL Encrypted
                            </small>
                        </div>
                        
                        <div class="text-center mt-2">
                            <small class="text-gray">
                                <i class="fas fa-ticket-alt me-1"></i>
                                E-Ticket will be sent to your email
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Payment method selection
        function selectPaymentMethod(method) {
            // Remove selected class from all
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Add selected class to clicked
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById(method === 'credit_card' ? 'creditCard' : 
                                   method === 'debit_card' ? 'debitCard' :
                                   method === 'paypal' ? 'paypal' : 'cash').checked = true;
        }

        // Initialize selected payment method
        document.addEventListener('DOMContentLoaded', function() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (selectedMethod) {
                const methodDiv = selectedMethod.closest('.payment-method');
                if (methodDiv) {
                    methodDiv.classList.add('selected');
                }
            }
        });

        // Form validation
        document.getElementById('customerForm').addEventListener('submit', function(e) {
            if (!document.getElementById('terms').checked) {
                e.preventDefault();
                alert('Please agree to the Terms & Conditions');
                return false;
            }
        });
    </script>
</body>
</html>