<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$booking_id = isset($_GET['booking_id']) ? sanitize($_GET['booking_id']) : 
              (isset($_SESSION['last_booking_id']) ? $_SESSION['last_booking_id'] : '');

if(empty($booking_id)) {
    header('Location: my-bookings.php');
    exit();
}

$db = new Database();

// Get booking details
$db->query("SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                   m.title as movie_title, m.poster_image, m.duration, m.language,
                   t.name as theatre_name, t.address as theatre_address, t.city,
                   scr.screen_name,
                   s.show_date, s.show_time, s.ticket_price
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN shows s ON b.show_id = s.id
            JOIN movies m ON s.movie_id = m.id
            JOIN theatres t ON s.theatre_id = t.id
            JOIN screens scr ON s.screen_id = scr.id
            WHERE b.booking_id = ? AND b.user_id = ?");
$db->bind(1, $booking_id);
$db->bind(2, $_SESSION['user_id']);
$booking = $db->single();

if(!$booking) {
    header('Location: my-bookings.php');
    exit();
}

// Generate QR code (in real app, use a QR code library)
$qr_data = json_encode([
    'booking_id' => $booking->booking_id,
    'movie' => $booking->movie_title,
    'theatre' => $booking->theatre_name,
    'show_date' => $booking->show_date,
    'show_time' => $booking->show_time,
    'seats' => $booking->seat_numbers,
    'user' => $booking->user_name
]);

// Clear last booking from session
unset($_SESSION['last_booking_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        .confirmation-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        
        .ticket {
            background: white;
            border-radius: 10px;
            padding: 30px;
            position: relative;
            border: 2px dashed #dee2e6;
        }
        
        .ticket:before {
            content: '';
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background: #f8f9fa;
            border-radius: 50%;
            border: 2px dashed #dee2e6;
        }
        
        .ticket:after {
            content: '';
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background: #f8f9fa;
            border-radius: 50%;
            border: 2px dashed #dee2e6;
        }
        
        .qr-code-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            border: 2px solid var(--primary-color);
        }
        
        .qr-code-placeholder {
            width: 200px;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        
        .booking-id {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        .print-options {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .ticket {
                border: none;
                padding: 0;
            }
            
            .ticket:before, .ticket:after {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .qr-code-placeholder {
                width: 150px;
                height: 150px;
            }
            
            .booking-id {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Success Message -->
                <div class="confirmation-card mb-5">
                    <div class="p-5 text-center">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill display-1"></i>
                        </div>
                        <h1 class="mb-3">Booking Confirmed!</h1>
                        <p class="lead mb-0">Your tickets have been booked successfully</p>
                        <div class="booking-id mt-3"><?php echo $booking->booking_id; ?></div>
                    </div>
                </div>
                
                <!-- Ticket -->
                <div class="ticket mb-5">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h2><?php echo $booking->movie_title; ?></h2>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-primary"><?php echo $booking->language; ?></span>
                                    <span class="badge bg-success"><?php echo floor($booking->duration / 60); ?>h <?php echo $booking->duration % 60; ?>m</span>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>Theatre:</strong> <?php echo $booking->theatre_name; ?></p>
                                    <p><strong>Screen:</strong> <?php echo $booking->screen_name; ?></p>
                                    <p><strong>Address:</strong> <?php echo $booking->theatre_address; ?>, <?php echo $booking->city; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Date:</strong> <?php echo formatDate($booking->show_date); ?></p>
                                    <p><strong>Time:</strong> <?php echo formatTime($booking->show_time); ?></p>
                                    <p><strong>Seats:</strong> <?php echo $booking->seat_numbers; ?></p>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Booking Details</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Booking ID:</strong> <?php echo $booking->booking_id; ?></p>
                                        <p><strong>Booked On:</strong> <?php echo formatDate($booking->booking_date); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> <?php echo $booking->user_name; ?></p>
                                        <p><strong>Email:</strong> <?php echo $booking->user_email; ?></p>
                                        <p><strong>Phone:</strong> <?php echo $booking->user_phone; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <div class="qr-code-container mb-3">
                                <div class="qr-code-placeholder">
                                    <i class="bi bi-qr-code-scan display-1 text-muted"></i>
                                </div>
                            </div>
                            <p class="text-muted">Show QR code at theatre entry</p>
                            
                            <div class="mt-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Amount Paid</h5>
                                        <div class="display-6 fw-bold text-primary">
                                            ₹<?php echo number_format($booking->total_amount, 2); ?>
                                        </div>
                                        <small class="text-muted">Payment: <?php echo strtoupper($booking->payment_method); ?> | Status: <?php echo strtoupper($booking->payment_status); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Instructions -->
                <div class="row mb-5">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-qr-code-scan display-4 text-primary mb-3"></i>
                                <h5>Show QR Code</h5>
                                <p class="text-muted">Show the QR code at theatre entry and snacks counter</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-clock display-4 text-primary mb-3"></i>
                                <h5>Arrive Early</h5>
                                <p class="text-muted">Please arrive at least 30 minutes before showtime</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-phone display-4 text-primary mb-3"></i>
                                <h5>Mobile Ticket</h5>
                                <p class="text-muted">No need to print. Show digital ticket on your phone</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="print-options no-print">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-grid gap-2">
                                <button onclick="window.print()" class="btn btn-outline-primary">
                                    <i class="bi bi-printer"></i> Print Ticket
                                </button>
                                <button id="downloadTicket" class="btn btn-outline-success">
                                    <i class="bi bi-download"></i> Download PDF
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-grid gap-2">
                                <a href="my-bookings.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-list-check"></i> View All Bookings
                                </a>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="bi bi-house"></i> Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Share Options -->
                <div class="text-center mt-5 no-print">
                    <h5 class="mb-3">Share your booking</h5>
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </button>
                        <button class="btn btn-outline-info">
                            <i class="bi bi-telegram"></i> Telegram
                        </button>
                        <button class="btn btn-outline-dark">
                            <i class="bi bi-envelope"></i> Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Download PDF (simulated)
        document.getElementById('downloadTicket').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Downloading...';
            btn.disabled = true;
            
            // Simulate download
            setTimeout(() => {
                // In real app, this would download a PDF
                const link = document.createElement('a');
                link.href = '#';
                link.download = 'Ticket-<?php echo $booking->booking_id; ?>.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                alert('Ticket downloaded successfully!');
            }, 2000);
        });
        
        // Share functionality
        document.querySelectorAll('.btn-outline-primary, .btn-outline-info, .btn-outline-dark').forEach(btn => {
            btn.addEventListener('click', function() {
                const platform = this.querySelector('i').className.includes('whatsapp') ? 'WhatsApp' :
                                this.querySelector('i').className.includes('telegram') ? 'Telegram' : 'Email';
                
                const shareText = `I booked tickets for ${'<?php echo $booking->movie_title; ?>'} on ${'<?php echo SITE_NAME; ?>'}! Booking ID: ${'<?php echo $booking->booking_id; ?>'}`;
                
                if(platform === 'WhatsApp') {
                    window.open(`https://wa.me/?text=${encodeURIComponent(shareText)}`, '_blank');
                } else if(platform === 'Telegram') {
                    window.open(`https://t.me/share/url?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(shareText)}`, '_blank');
                } else {
                    window.location.href = `mailto:?subject=Ticket Booking Confirmation&body=${encodeURIComponent(shareText)}`;
                }
            });
        });
        
        // Auto-close alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getInstance(alert)?.close();
            });
        }, 5000);
        
        // Store booking in localStorage for offline access
        const bookingData = {
            id: '<?php echo $booking->booking_id; ?>',
            movie: '<?php echo $booking->movie_title; ?>',
            theatre: '<?php echo $booking->theatre_name; ?>',
            date: '<?php echo $booking->show_date; ?>',
            time: '<?php echo $booking->show_time; ?>',
            seats: '<?php echo $booking->seat_numbers; ?>',
            amount: '<?php echo $booking->total_amount; ?>'
        };
        
        localStorage.setItem('lastBooking', JSON.stringify(bookingData));
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>