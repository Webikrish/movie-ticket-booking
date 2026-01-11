<?php
// booking_confirmation.php
require_once 'session_manager.php';

// Check if payment was successful
if (!isset($_SESSION['payment_success'])) {
    $_SESSION['error_message'] = 'No booking found. Please start again.';
    header('Location: index.php');
    exit();
}

$paymentData = $_SESSION['payment_success'];
$showData = $_SESSION['selected_show'];

// Clear booking session data (except for this confirmation)
unset($_SESSION['selected_show']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | CinemaKrish</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-dark: #0a0a0a;
            --accent-red: #d32f2f;
            --accent-gold: #ffc107;
        }

        body {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #1a1a1a 100%);
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }

        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .confirmation-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-red) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .ticket-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            border: 2px solid var(--accent-gold);
            position: relative;
            overflow: hidden;
        }

        .ticket-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--accent-red), var(--accent-gold));
        }

        .qr-code {
            width: 150px;
            height: 150px;
            background: #fff;
            padding: 10px;
            border-radius: 10px;
            margin: 0 auto;
        }

        .qr-code img {
            width: 100%;
            height: 100%;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .btn-download {
            background: linear-gradient(135deg, var(--accent-red) 0%, #9a0007 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-download:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(211, 47, 47, 0.3);
        }

        .btn-home {
            background: transparent;
            border: 2px solid var(--accent-gold);
            color: var(--accent-gold);
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: var(--accent-gold);
            color: #000;
        }

        @media print {
            body {
                background: white;
                color: black;
            }
            
            .confirmation-container {
                background: white;
                border: 1px solid #ddd;
                box-shadow: none;
            }
            
            .ticket-card {
                background: white;
                border: 2px solid #000;
            }
            
            .btn-download, .btn-home {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="text-center">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 class="confirmation-title">Booking Confirmed!</h1>
            <p class="lead mb-4">Your tickets have been booked successfully. An e-ticket has been sent to your email.</p>
            
            <div class="alert alert-success d-inline-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                Payment completed successfully. Transaction ID: <?php echo $paymentData['transaction_id']; ?>
            </div>
        </div>

        <div class="ticket-card">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-4">
                        <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($showData['movie_title']); ?></h3>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-ticket-alt me-2"></i> Ticket Number:</span>
                            <strong><?php echo $paymentData['ticket_number']; ?></strong>
                        </div>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-calendar me-2"></i> Show Date:</span>
                            <strong><?php echo date('l, F j, Y', strtotime($showData['show_date'])); ?></strong>
                        </div>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-clock me-2"></i> Show Time:</span>
                            <strong><?php echo date('h:i A', strtotime($showData['show_time'])); ?></strong>
                        </div>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-theater-masks me-2"></i> Theatre:</span>
                            <strong><?php echo htmlspecialchars($showData['theatre_name']); ?></strong>
                        </div>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-map-marker-alt me-2"></i> Location:</span>
                            <strong><?php echo $showData['location'] . ', ' . $showData['city']; ?></strong>
                        </div>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-couch me-2"></i> Seats:</span>
                            <strong><?php echo implode(', ', $showData['selected_seats']); ?></strong>
                        </div>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-user me-2"></i> Customer:</span>
                            <strong><?php echo htmlspecialchars($showData['customer_details']['name']); ?></strong>
                        </div>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-credit-card me-2"></i> Payment Method:</span>
                            <strong class="text-uppercase"><?php echo str_replace('_', ' ', $paymentData['payment_method']); ?></strong>
                        </div>
                        
                        <div class="detail-item">
                            <span><i class="fas fa-dollar-sign me-2"></i> Amount Paid:</span>
                            <strong class="text-success">$<?php echo number_format($paymentData['amount'], 2); ?></strong>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Instructions:</strong> Show this QR code at the theatre entrance. Please arrive 15 minutes before showtime.
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="qr-code mb-3">
                            <img src="<?php echo $paymentData['qr_code']; ?>" alt="QR Code">
                        </div>
                        <p class="small">Scan QR code at entrance</p>
                        <div class="mt-3">
                            <button onclick="window.print()" class="btn-download w-100 mb-2">
                                <i class="fas fa-print me-2"></i> Print Ticket
                            </button>
                            <a href="index.php" class="btn-home w-100">
                                <i class="fas fa-home me-2"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-muted">
                <i class="fas fa-envelope me-1"></i>
                E-ticket has been sent to <?php echo $showData['customer_details']['email']; ?>
            </p>
            <p class="text-muted">
                <i class="fas fa-phone me-1"></i>
                Need help? Call us at +1 (555) 123-4567
            </p>
        </div>
    </div>

    <script>
        // Auto-clear session after 30 seconds
        setTimeout(() => {
            fetch('clear_booking_session.php');
        }, 30000);
    </script>
</body>
</html>