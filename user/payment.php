<?php
// payment.php
require_once 'session_manager.php';
require_once 'db_connection.php';

// Check if booking details exist
if (!isset($_SESSION['selected_show']['booking_id'])) {
    $_SESSION['error_message'] = 'No booking found. Please start again.';
    header('Location: index.php');
    exit();
}

$showData   = $_SESSION['selected_show'];
$booking_id = $showData['booking_id'];

// Get user id
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Session expired. Please login again.';
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from DB
$stmt = $db->prepare("SELECT id, full_name, email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    $_SESSION['error_message'] = 'User not found.';
    header('Location: login.php');
    exit();
}


// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    try {
        // Begin transaction
        $db->beginTransaction();
        
        // Generate transaction ID
        $transaction_id = 'TXN' . date('YmdHis') . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
        
        // Create transaction record
        $transaction_sql = "INSERT INTO transactions (
            booking_id, user_id, transaction_id, payment_method, amount, 
            currency, status, gateway_response
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($transaction_sql);
        
        $payment_method = $showData['customer_details']['payment_method'];
        $payment_success = ($payment_method === 'cash' || rand(1, 10) > 2); // Simulate 80% success rate for demo
        
        $result = $stmt->execute([
            $booking_id,
            $userData['id'],
            $transaction_id,
            $payment_method,
            $showData['total_amount'],
            'USD',
            $payment_success ? 'completed' : 'failed',
            json_encode(['status' => $payment_success ? 'success' : 'failed', 'timestamp' => date('Y-m-d H:i:s')])
        ]);
        
        if ($payment_success) {
            // Update booking status
            $update_booking_sql = "UPDATE bookings SET 
                payment_status = 'completed',
                booking_status = 'confirmed',
                transaction_id = ?
                WHERE id = ?";
            
            $stmt = $db->prepare($update_booking_sql);
            $stmt->execute([$transaction_id, $booking_id]);
            
            // Generate QR code data (in real app, use a QR code library)
            $qr_data = json_encode([
                'booking_id' => $booking_id,
                'ticket_number' => $showData['ticket_number'],
                'movie' => $showData['movie_title'],
                'show_time' => $showData['show_date'] . ' ' . $showData['show_time'],
                'seats' => implode(',', $showData['selected_seats'])
            ]);
            
            $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qr_data);
            
            // Update booking with QR code
            $update_qr_sql = "UPDATE bookings SET qr_code = ? WHERE id = ?";
            $stmt = $db->prepare($update_qr_sql);
            $stmt->execute([$qr_code_url, $booking_id]);
            
            // Commit transaction
            $db->commit();
            
            // Send confirmation email (simulated)
            sendConfirmationEmail($showData);
            
            // Store success data and redirect to confirmation
            $_SESSION['payment_success'] = [
                'booking_id' => $booking_id,
                'transaction_id' => $transaction_id,
                'ticket_number' => $showData['ticket_number'],
                'amount' => $showData['total_amount'],
                'payment_method' => $payment_method,
                'qr_code' => $qr_code_url
            ];
            
            header('Location: booking_confirmation.php');
            exit();
            
        } else {
            // Payment failed
            $db->commit();
            
            $_SESSION['error_message'] = 'Payment failed. Please try again or use a different payment method.';
            header('Location: payment.php');
            exit();
        }
        
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = 'Payment processing error: ' . $e->getMessage();
    }
}

// Function to send confirmation email (simulated)
function sendConfirmationEmail($bookingData) {
    // In a real application, implement email sending here
    // For now, just log to error log
    error_log("Email sent for booking #{$bookingData['booking_id']} to {$bookingData['customer_details']['email']}");
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | CinemaKrish</title>
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

        .payment-container {
            max-width: 800px;
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

        .payment-section {
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

        .payment-method-card {
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .payment-method-card.active {
            border-color: var(--accent-red);
            background: rgba(211, 47, 47, 0.1);
        }

        .payment-icon {
            font-size: 2rem;
            margin-right: 15px;
        }

        .card-details-form {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        .card-details-form.active {
            display: block;
        }

        .form-label {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 12px 15px;
            border-radius: 8px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-red);
            color: var(--text-light);
            box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.25);
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-gray);
        }

        .btn-pay {
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
        }

        .btn-pay:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(211, 47, 47, 0.3);
        }

        .btn-pay:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: rgba(255, 193, 7, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }

        .booking-summary {
            background: rgba(255, 193, 7, 0.1);
            border-radius: 10px;
            padding: 25px;
            border: 1px solid rgba(255, 193, 7, 0.2);
            margin-bottom: 20px;
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

        /* Payment Status Animation */
        .payment-processing {
            text-align: center;
            padding: 40px;
            display: none;
        }

        .payment-processing.active {
            display: block;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255, 255, 255, 0.1);
            border-top-color: var(--accent-red);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <div class="payment-container">
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
            <div class="step completed">
                <div class="step-number">3</div>
                <div class="step-label">Customer Details</div>
            </div>
            <div class="step active">
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

        <div class="row">
            <div class="col-lg-8">
                <div class="payment-section">
                    <h2 class="section-title">Complete Payment</h2>
                    
                    <!-- Payment Method Display -->
                    <div class="mb-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Selected Payment Method: 
                            <strong class="text-uppercase">
                                <?php echo str_replace('_', ' ', $showData['customer_details']['payment_method']); ?>
                            </strong>
                        </div>
                    </div>

                    <!-- Payment Forms -->
                    <form method="POST" action="" id="paymentForm">
                        <input type="hidden" name="process_payment" value="1">
                        
                        <?php if ($showData['customer_details']['payment_method'] === 'credit_card' || 
                                  $showData['customer_details']['payment_method'] === 'debit_card'): ?>
                            
                            <!-- Card Payment Form -->
                            <div class="payment-method-card active">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-credit-card payment-icon"></i>
                                    <div>
                                        <h4 class="mb-1">Card Details</h4>
                                        <p class="small text-gray mb-0">Enter your card information</p>
                                    </div>
                                </div>
                                
                                <div class="card-details-form active">
                                    <div class="mb-3">
                                        <label class="form-label">Card Number</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="1234 5678 9012 3456" 
                                                   maxlength="19" id="cardNumber" required>
                                            <span class="input-group-text">
                                                <i class="fas fa-credit-card"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Expiry Date</label>
                                            <input type="text" class="form-control" placeholder="MM/YY" 
                                                   maxlength="5" id="expiryDate" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CVV</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" placeholder="123" 
                                                       maxlength="4" id="cvv" required>
                                                <span class="input-group-text">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Cardholder Name</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo htmlspecialchars($showData['customer_details']['name']); ?>" 
                                               id="cardholderName" required>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="saveCard">
                                        <label class="form-check-label small" for="saveCard">
                                            Save card for future payments
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                        <?php elseif ($showData['customer_details']['payment_method'] === 'paypal'): ?>
                            
                            <!-- PayPal Form -->
                            <div class="payment-method-card active">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fab fa-paypal payment-icon"></i>
                                    <div>
                                        <h4 class="mb-1">PayPal</h4>
                                        <p class="small text-gray mb-0">You will be redirected to PayPal</p>
                                    </div>
                                </div>
                                
                                <div class="text-center p-4">
                                    <i class="fab fa-paypal fa-4x mb-3 text-primary"></i>
                                    <p class="mb-3">Click the button below to pay with your PayPal account</p>
                                    <button type="button" class="btn btn-primary btn-lg" id="paypalButton">
                                        <i class="fab fa-paypal me-2"></i> Pay with PayPal
                                    </button>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            
                            <!-- Cash Payment -->
                            <div class="payment-method-card active">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-money-bill-wave payment-icon"></i>
                                    <div>
                                        <h4 class="mb-1">Pay at Counter</h4>
                                        <p class="small text-gray mb-0">Pay cash at theatre counter</p>
                                    </div>
                                </div>
                                
                                <div class="text-center p-4">
                                    <i class="fas fa-store fa-4x mb-3 text-success"></i>
                                    <p class="mb-3">Your booking is confirmed. Please pay at the theatre counter before the show.</p>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Please arrive 30 minutes before showtime to complete payment
                                    </div>
                                </div>
                            </div>
                            
                        <?php endif; ?>

                        <!-- Security Badge -->
                        <div class="security-badge">
                            <i class="fas fa-shield-alt fa-2x text-success"></i>
                            <div>
                                <strong>100% Secure Payment</strong>
                                <div class="small">Your payment information is encrypted</div>
                            </div>
                        </div>

                        <!-- Payment Processing Animation -->
                        <div class="payment-processing" id="paymentProcessing">
                            <div class="spinner"></div>
                            <h4>Processing Payment...</h4>
                            <p class="text-gray">Please do not refresh the page</p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-pay" id="submitPayment">
                            <i class="fas fa-lock me-2"></i>
                            Pay $<?php echo number_format($showData['total_amount'], 2); ?>
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="customer_details.php" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left me-1"></i> Back to Details
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Booking Summary -->
                <div class="booking-summary">
                    <h3 class="section-title mb-4">Booking Summary</h3>
                    
                    <div class="summary-item">
                        <span>Booking ID:</span>
                        <strong>#<?php echo $showData['booking_id']; ?></strong>
                    </div>
                    
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
                        <span>Seats:</span>
                        <div class="text-end">
                            <?php foreach ($showData['selected_seats'] as $seat): ?>
                                <span class="badge bg-dark me-1"><?php echo $seat; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="summary-item">
                        <span>Customer:</span>
                        <div class="text-end">
                            <div><?php echo htmlspecialchars($showData['customer_details']['name']); ?></div>
                            <small><?php echo $showData['customer_details']['email']; ?></small>
                        </div>
                    </div>
                    
                    <div class="summary-item summary-total">
                        <span>Total Amount:</span>
                        <span>$<?php echo number_format($showData['total_amount'], 2); ?></span>
                    </div>
                    
                    <div class="mt-4 p-3 bg-dark rounded">
                        <div class="small">
                            <i class="fas fa-ticket-alt me-2"></i>
                            <strong>E-Ticket Instructions:</strong>
                            <ul class="mt-2 mb-0 ps-3">
                                <li>Show QR code at entrance</li>
                                <li>Carry valid ID proof</li>
                                <li>Arrive 15 minutes before show</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Card number formatting
        document.getElementById('cardNumber')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formatted = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = formatted.substring(0, 19);
        });

        // Expiry date formatting
        document.getElementById('expiryDate')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value.substring(0, 5);
        });

        // CVV formatting
        document.getElementById('cvv')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 4);
        });

        // PayPal button handler
        document.getElementById('paypalButton')?.addEventListener('click', function() {
            document.getElementById('submitPayment').click();
        });

        // Form submission handler
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate card details if card payment
            const paymentMethod = '<?php echo $showData['customer_details']['payment_method']; ?>';
            
            if (paymentMethod === 'credit_card' || paymentMethod === 'debit_card') {
                const cardNumber = document.getElementById('cardNumber').value.replace(/\s+/g, '');
                const expiryDate = document.getElementById('expiryDate').value;
                const cvv = document.getElementById('cvv').value;
                
                if (cardNumber.length < 16) {
                    alert('Please enter a valid 16-digit card number');
                    return false;
                }
                
                if (!expiryDate.match(/^(0[1-9]|1[0-2])\/?([0-9]{2})$/)) {
                    alert('Please enter a valid expiry date (MM/YY)');
                    return false;
                }
                
                if (cvv.length < 3) {
                    alert('Please enter a valid CVV');
                    return false;
                }
            }
            
            // Show processing animation
            document.getElementById('paymentProcessing').classList.add('active');
            document.getElementById('submitPayment').disabled = true;
            document.getElementById('submitPayment').innerHTML = 
                '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
            
            // Submit form after delay (simulate processing)
            setTimeout(() => {
                this.submit();
            }, 2000);
        });
    </script>
</body>
</html>