<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if(!isset($_GET['show_id'])) {
    header('Location: movies.php');
    exit();
}

$db = new Database();
$show_id = $_GET['show_id'];

// Get show details
$db->query("SELECT s.*, m.title, m.poster_image, t.name as theatre_name, t.city, 
            scr.seat_layout, scr.total_seats
            FROM shows s
            JOIN movies m ON s.movie_id = m.id
            JOIN theatres t ON s.theatre_id = t.id
            JOIN screens scr ON s.screen_id = scr.id
            WHERE s.id = ?");
$db->bind(1, $show_id);
$show = $db->single();

// Get already booked seats
$db->query("SELECT seat_numbers FROM bookings 
            WHERE show_id = ? AND booking_status = 'confirmed'");
$db->bind(1, $show_id);
$bookings = $db->resultSet();

$booked_seats = [];
foreach($bookings as $booking) {
    $seats = explode(',', $booking->seat_numbers);
    $booked_seats = array_merge($booked_seats, $seats);
}

$seat_layout = json_decode($show->seat_layout, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .seat-layout-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .seat {
            margin: 5px;
            width: 50px;
            height: 50px;
        }
        @media (max-width: 768px) {
            .seat {
                width: 35px;
                height: 35px;
                margin: 3px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <!-- Show Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <img src="../assets/uploads/movies/<?php echo $show->poster_image; ?>" 
                             alt="<?php echo $show->title; ?>" class="img-fluid rounded">
                    </div>
                    <div class="col-md-9">
                        <h2><?php echo $show->title; ?></h2>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <p><strong>Theatre:</strong> <?php echo $show->theatre_name; ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Date & Time:</strong> 
                                    <?php echo formatDate($show->show_date); ?> - 
                                    <?php echo formatTime($show->show_time); ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Price:</strong> ₹<?php echo $show->ticket_price; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seat Selection -->
        <div class="seat-layout-container">
            <div class="text-center mb-4">
                <div class="screen-display">SCREEN THIS WAY</div>
            </div>

            <!-- Seat Layout -->
            <div id="seatMap" class="mb-4">
                <?php if($seat_layout): ?>
                <?php foreach($seat_layout as $row => $seats): ?>
                <div class="seat-row mb-2">
                    <div class="row-label me-3 align-self-center"><?php echo $row; ?></div>
                    <div class="d-flex flex-wrap">
                        <?php foreach($seats as $seat_number): 
                            $seat_id = $row . $seat_number;
                            $is_booked = in_array($seat_id, $booked_seats);
                            $seat_type = ($seat_number <= 5 || $seat_number >= count($seats)-5) ? 'premium' : 'regular';
                        ?>
                        <div class="seat <?php echo $is_booked ? 'booked' : 'available'; ?> 
                                         <?php echo $seat_type; ?>"
                             data-seat="<?php echo $seat_id; ?>"
                             data-type="<?php echo $seat_type; ?>"
                             onclick="selectSeat(this)"
                             <?php echo $is_booked ? 'style="cursor:not-allowed;"' : ''; ?>>
                            <?php echo $seat_number; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Seat Legend -->
            <div class="seat-legend mb-4">
                <div class="legend-item">
                    <div class="seat available"></div>
                    <span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="seat selected"></div>
                    <span>Selected</span>
                </div>
                <div class="legend-item">
                    <div class="seat booked"></div>
                    <span>Booked</span>
                </div>
                <div class="legend-item">
                    <div class="seat premium available"></div>
                    <span>Premium</span>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Selected Seats</h5>
                            <div id="selectedSeats" class="mb-3">
                                <span class="text-muted">No seats selected</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Regular Seats (<span id="regularCount">0</span>)</span>
                                <span>₹<span id="regularTotal">0</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Premium Seats (<span id="premiumCount">0</span>)</span>
                                <span>₹<span id="premiumTotal">0</span></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fs-5 fw-bold">
                                <span>Total Amount:</span>
                                <span>₹<span id="totalAmount">0</span></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <form id="bookingForm" action="booking-summary.php" method="POST">
                            <input type="hidden" name="show_id" value="<?php echo $show_id; ?>">
                            <input type="hidden" name="selected_seats" id="selectedSeatsInput">
                            <input type="hidden" name="total_amount" id="totalAmountInput">
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="theatres.php?movie_id=<?php echo $show->movie_id; ?>" 
                                   class="btn btn-outline-secondary me-2">
                                    Back
                                </a>
                                <button type="submit" class="btn btn-primary" id="proceedBtn" disabled>
                                    Proceed to Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedSeats = [];
        const regularPrice = <?php echo $show->ticket_price; ?>;
        const premiumPrice = <?php echo $show->ticket_price * 1.5; ?>;
        
        function selectSeat(seatElement) {
            const seat = seatElement.getAttribute('data-seat');
            const seatType = seatElement.getAttribute('data-type');
            
            if(seatElement.classList.contains('booked')) {
                return;
            }
            
            if(seatElement.classList.contains('selected')) {
                // Deselect seat
                seatElement.classList.remove('selected');
                seatElement.classList.add('available');
                selectedSeats = selectedSeats.filter(s => s.seat !== seat);
            } else {
                // Select seat
                seatElement.classList.remove('available');
                seatElement.classList.add('selected');
                selectedSeats.push({
                    seat: seat,
                    type: seatType,
                    price: seatType === 'premium' ? premiumPrice : regularPrice
                });
            }
            
            updateBookingSummary();
        }
        
        function updateBookingSummary() {
            const selectedSeatsDiv = document.getElementById('selectedSeats');
            const regularCount = document.getElementById('regularCount');
            const premiumCount = document.getElementById('premiumCount');
            const regularTotal = document.getElementById('regularTotal');
            const premiumTotal = document.getElementById('premiumTotal');
            const totalAmount = document.getElementById('totalAmount');
            const selectedSeatsInput = document.getElementById('selectedSeatsInput');
            const totalAmountInput = document.getElementById('totalAmountInput');
            const proceedBtn = document.getElementById('proceedBtn');
            
            if(selectedSeats.length === 0) {
                selectedSeatsDiv.innerHTML = '<span class="text-muted">No seats selected</span>';
                proceedBtn.disabled = true;
            } else {
                selectedSeatsDiv.innerHTML = selectedSeats.map(s => 
                    `<span class="badge bg-primary me-2">${s.seat}</span>`
                ).join('');
                
                const regularSeats = selectedSeats.filter(s => s.type === 'regular');
                const premiumSeats = selectedSeats.filter(s => s.type === 'premium');
                
                const regularSeatCount = regularSeats.length;
                const premiumSeatCount = premiumSeats.length;
                const regularSeatTotal = regularSeats.reduce((sum, seat) => sum + seat.price, 0);
                const premiumSeatTotal = premiumSeats.reduce((sum, seat) => sum + seat.price, 0);
                const total = regularSeatTotal + premiumSeatTotal;
                
                regularCount.textContent = regularSeatCount;
                premiumCount.textContent = premiumSeatCount;
                regularTotal.textContent = regularSeatTotal;
                premiumTotal.textContent = premiumSeatTotal;
                totalAmount.textContent = total;
                
                selectedSeatsInput.value = selectedSeats.map(s => s.seat).join(',');
                totalAmountInput.value = total;
                
                proceedBtn.disabled = false;
            }
        }
        
        // Auto-reserve seats for 10 minutes
        let reservationTimer;
        function reserveSeats() {
            if(selectedSeats.length > 0) {
                const seatNumbers = selectedSeats.map(s => s.seat).join(',');
                
                fetch('../api/reserve-seats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        show_id: <?php echo $show_id; ?>,
                        seats: seatNumbers,
                        session_id: '<?php echo session_id(); ?>'
                    })
                });
                
                // Renew reservation every 5 minutes
                reservationTimer = setTimeout(reserveSeats, 5 * 60 * 1000);
            }
        }
        
        // Start reservation when seats are selected
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.seat').forEach(seat => {
                seat.addEventListener('click', function() {
                    reserveSeats();
                });
            });
        });
        
        // Clear reservation on page unload
        window.addEventListener('beforeunload', function() {
            if(selectedSeats.length > 0) {
                fetch('../api/clear-reservation.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        show_id: <?php echo $show_id; ?>,
                        session_id: '<?php echo session_id(); ?>'
                    })
                });
            }
            clearTimeout(reservationTimer);
        });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>