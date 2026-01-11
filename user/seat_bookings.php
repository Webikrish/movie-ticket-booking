<?php
// seat_bookings.php
require_once 'session_manager.php';
require_once 'db_connection.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['error_message'] = 'Please login to book tickets';
    header('Location: login.php');
    exit();
}

// Check if show is selected
if (!isset($_SESSION['selected_show'])) {
    $_SESSION['error_message'] = 'Please select a show first';
    header('Location: index.php');
    exit();
}

$showData = $_SESSION['selected_show'];
$selectedSeats = $showData['selected_seats'] ?? [];
$bookedSeats = $showData['booked_seats'] ?? [];

// Define seat type prices (based on base ticket price)
// seat_bookings.php (Add after line ~25)

// Define seat type prices (you might want to store this in DB instead)
$seatTypePrices = [
    'regular' => $showData['ticket_price'], // Base price
    'executive' => $showData['ticket_price'] + 2.00, // Executive costs $2 more
    'premium' => $showData['ticket_price'] + 5.00,   // Premium costs $5 more
];

// Handle seat selection form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_seats'])) {
    $selectedSeats = isset($_POST['seats']) ? $_POST['seats'] : [];
    $selectedSeatTypes = isset($_POST['seat_types']) ? $_POST['seat_types'] : [];
    
    if (empty($selectedSeats)) {
        $_SESSION['error_message'] = 'Please select at least one seat';
    } else {
        // Combine seats with their types for easier processing
        $selectedSeatsData = [];
        for ($i = 0; $i < count($selectedSeats); $i++) {
            $selectedSeatsData[] = [
                'seat' => $selectedSeats[$i],
                'type' => $selectedSeatTypes[$i] ?? 'regular'
            ];
        }
        
        // Check if seats are already booked
        $available = true;
        foreach ($selectedSeatsData as $seatData) {
            if (in_array($seatData['seat'], $bookedSeats)) {
                $_SESSION['error_message'] = "Seat {$seatData['seat']} is already booked. Please select different seats.";
                $available = false;
                break;
            }
        }
        
        if ($available) {
            // Store selected seats with types
            $_SESSION['selected_show']['selected_seats_data'] = $selectedSeatsData;
            $_SESSION['selected_show']['selected_seats'] = array_column($selectedSeatsData, 'seat');
            
            // Calculate totals based on seat types
            $seatTypeCounts = [
                'regular' => 0,
                'executive' => 0,
                'premium' => 0
            ];
            
            foreach ($selectedSeatsData as $seatData) {
                $seatTypeCounts[$seatData['type']]++;
            }
            
            // Calculate subtotal
            $subtotal = 0;
            foreach ($seatTypeCounts as $type => $count) {
                $subtotal += $count * $seatTypePrices[$type];
            }
            
            $convenienceFee = count($selectedSeats) * 1.5;
            $totalAmount = $subtotal + $convenienceFee;
            
            $_SESSION['selected_show']['total_seats'] = count($selectedSeats);
            $_SESSION['selected_show']['seat_type_counts'] = $seatTypeCounts;
            $_SESSION['selected_show']['subtotal'] = $subtotal;
            $_SESSION['selected_show']['convenience_fee'] = $convenienceFee;
            $_SESSION['selected_show']['total_amount'] = $totalAmount;
            $_SESSION['selected_show']['seat_type_prices'] = $seatTypePrices;
            
            // Redirect to customer details page
            header('Location: customer_details.php');
            exit();
        }
    }
}
$seatTypePrices = [
    'regular' => $showData['ticket_price'] ?? 0,
    'executive' => ($showData['ticket_price'] ?? 0) + 5.00, // +$5 for executive
    'premium' => ($showData['ticket_price'] ?? 0) + 10.00  // +$10 for premium
];

// Handle seat selection form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_seats'])) {
    $selectedSeats = isset($_POST['seats']) ? $_POST['seats'] : [];
    $selectedSeatTypes = isset($_POST['seat_types']) ? $_POST['seat_types'] : [];
    
    if (empty($selectedSeats)) {
        $_SESSION['error_message'] = 'Please select at least one seat';
    } else {
        // Check if seats are already booked
        $available = true;
        foreach ($selectedSeats as $seat) {
            if (in_array($seat, $bookedSeats)) {
                $_SESSION['error_message'] = "Seat $seat is already booked. Please select different seats.";
                $available = false;
                break;
            }
        }
        
        if ($available) {
            // Calculate total amount with seat type pricing
            $totalAmount = 0;
            $seatDetails = [];
            
            for ($i = 0; $i < count($selectedSeats); $i++) {
                $seatNumber = $selectedSeats[$i];
                $seatType = $selectedSeatTypes[$i] ?? 'regular';
                $seatPrice = $seatTypePrices[$seatType] ?? $seatTypePrices['regular'];
                
                $seatDetails[] = [
                    'seat' => $seatNumber,
                    'type' => $seatType,
                    'price' => $seatPrice
                ];
                
                $totalAmount += $seatPrice;
            }
            
            $convenienceFee = count($selectedSeats) * 1.5;
            $finalTotal = $totalAmount + $convenienceFee;
            
            // Store selected seats in session with pricing details
            $_SESSION['selected_show']['selected_seats'] = $selectedSeats;
            $_SESSION['selected_show']['selected_seat_types'] = $selectedSeatTypes;
            $_SESSION['selected_show']['seat_details'] = $seatDetails;
            $_SESSION['selected_show']['total_seats'] = count($selectedSeats);
            $_SESSION['selected_show']['ticket_total'] = $totalAmount;
            $_SESSION['selected_show']['convenience_fee'] = $convenienceFee;
            $_SESSION['selected_show']['total_amount'] = $finalTotal;
            
            // Redirect to customer details page
            header('Location: customer_details.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats | CinemaKrish</title>
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

        /* Seat Selection Styles */
        .screen-area {
            background: linear-gradient(to bottom, #333, #666);
            color: white;
            text-align: center;
            padding: 20px;
            margin: 30px 0;
            border-radius: 10px;
            font-family: 'Oswald', sans-serif;
            font-size: 1.5rem;
            letter-spacing: 3px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .screen-area::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(to bottom, #ffc107, transparent);
            border-radius: 10px 10px 0 0;
        }

        .seats-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
        }

        .seat-row {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
            align-items: center;
        }

        .row-label {
            width: 30px;
            text-align: center;
            font-weight: 600;
            color: var(--accent-gold);
            margin-right: 15px;
        }

        .seat {
            width: 40px;
            height: 40px;
            margin: 5px;
            background: #444;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            font-size: 0.8rem;
            color: white;
            font-weight: 600;
        }

        .seat:hover {
            transform: scale(1.1);
        }

        .seat.available {
            background: #666;
        }

        .seat.available:hover {
            background: var(--accent-gold);
            color: var(--primary-dark);
        }

        .seat.selected {
            background: var(--accent-red);
            color: white;
            transform: scale(1.1);
        }

        .seat.booked {
            background: #222;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .seat.regular {
            border: 2px solid #6c757d;
        }

        .seat.executive {
            border: 2px solid #ffc107;
            background: linear-gradient(135deg, #666 0%, #444 100%);
        }

        .seat.executive.selected {
            background: linear-gradient(135deg, #d32f2f 0%, #9a0007 100%);
        }

        .seat.premium {
            border: 2px solid #17a2b8;
            background: linear-gradient(135deg, #666 0%, #444 100%);
        }

        .seat.premium.selected {
            background: linear-gradient(135deg, #d32f2f 0%, #9a0007 100%);
        }

        .seat-price {
            position: absolute;
            bottom: -20px;
            font-size: 0.6rem;
            white-space: nowrap;
        }

        .regular .seat-price {
            color: #6c757d;
        }

        .executive .seat-price {
            color: #ffc107;
        }

        .premium .seat-price {
            color: #17a2b8;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }

        .legend-available {
            background: #666;
            border: 1px solid #888;
        }

        .legend-selected {
            background: var(--accent-red);
        }

        .legend-booked {
            background: #222;
        }

        .legend-regular {
            border: 2px solid #6c757d;
            background: #666;
        }

        .legend-executive {
            border: 2px solid #ffc107;
            background: #666;
        }

        .legend-premium {
            border: 2px solid #17a2b8;
            background: #666;
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
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .seat-type-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            margin-left: 5px;
        }

        .badge-regular {
            background: #6c757d;
        }

        .badge-executive {
            background: #ffc107;
            color: #000;
        }

        .badge-premium {
            background: #17a2b8;
        }

        .price-breakdown {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .breakdown-item .seat-type {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-continue {
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

        .btn-continue:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(211, 47, 47, 0.3);
            color: white;
        }

        .btn-continue:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Price Tooltip */
        .seat-tooltip {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 1000;
            display: none;
            pointer-events: none;
            border: 1px solid var(--accent-gold);
        }

        .seat:hover .seat-tooltip {
            display: block;
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

            .seat {
                width: 35px;
                height: 35px;
                margin: 3px;
                font-size: 0.7rem;
            }

            .screen-area {
                font-size: 1.2rem;
                padding: 15px;
            }

            .seat-price {
                bottom: -18px;
                font-size: 0.5rem;
            }
        }

        @media (max-width: 576px) {
            .section-title {
                font-size: 1.5rem;
            }

            .seat {
                width: 30px;
                height: 30px;
                margin: 2px;
            }

            .seat-legend {
                flex-direction: column;
                align-items: center;
                gap: 10px;
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
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Choose Seats</div>
            </div>
            <div class="step">
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

        <form method="POST" action="" id="bookingForm">
            <input type="hidden" name="show_id" value="<?php echo $showData['show_id']; ?>">
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="booking-section">
                        <h2 class="section-title">Select Seats</h2>
                        
                        <!-- Show Details -->
                        <div class="mb-4">
                            <h5 class="text-light mb-2">Show Details:</h5>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <div class="text-gray">
                                        <i class="fas fa-film me-2"></i>
                                        <?php echo htmlspecialchars($showData['movie_title']); ?>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="text-gray">
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo date('l, F j, Y', strtotime($showData['show_date'])); ?>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="text-gray">
                                        <i class="fas fa-clock me-2"></i>
                                        <?php echo date('h:i A', strtotime($showData['show_time'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6 mb-2">
                                    <div class="text-gray">
                                        <i class="fas fa-theater-masks me-2"></i>
                                        <?php echo htmlspecialchars($showData['theatre_name']); ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="text-gray">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <?php echo htmlspecialchars($showData['location'] . ', ' . $showData['city']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Screen -->
                        <div class="screen-area">
                            <i class="fas fa-film me-2"></i> SCREEN
                        </div>

                        <!-- Seats Selection -->
                        <div class="seats-container">
                            <?php
                            // Generate seat layout (10 rows A-J, 10 seats per row)
                            $rows = range('A', 'J');
                            
                            foreach ($rows as $row): ?>
                                <div class="seat-row">
                                    <div class="row-label"><?php echo $row; ?></div>
                                    <?php for ($col = 1; $col <= 10; $col++): 
                                        $seatNumber = $row . $col;
                                        $isBooked = in_array($seatNumber, $bookedSeats);
                                        $isSelected = in_array($seatNumber, $selectedSeats);
                                        
                                        // Determine seat type and price
                                        $seatType = 'regular';
                                        $seatPrice = $seatTypePrices['regular'];
                                        
                                        if (in_array($row, ['A', 'B', 'C'])) {
                                            $seatType = 'executive';
                                            $seatPrice = $seatTypePrices['executive'];
                                        } elseif (in_array($row, ['D', 'E', 'F'])) {
                                            $seatType = 'premium';
                                            $seatPrice = $seatTypePrices['premium'];
                                        }
                                    ?>
                                        <div class="seat 
                                            <?php echo $seatType; ?> 
                                            <?php echo $isBooked ? 'booked' : 'available'; ?>
                                            <?php echo $isSelected ? 'selected' : ''; ?>"
                                            data-seat="<?php echo $seatNumber; ?>"
                                            data-type="<?php echo $seatType; ?>"
                                            data-price="<?php echo $seatPrice; ?>"
                                            <?php if (!$isBooked): ?>
                                                onclick="toggleSeat('<?php echo $seatNumber; ?>', '<?php echo $seatType; ?>', <?php echo $seatPrice; ?>)"
                                            <?php endif; ?>
                                            title="<?php echo ucfirst($seatType); ?> Seat - $<?php echo number_format($seatPrice, 2); ?>">
                                            <?php echo $col; ?>
                                            <div class="seat-price">$<?php echo number_format($seatPrice, 0); ?></div>
                                            <div class="seat-tooltip">
                                                <?php echo ucfirst($seatType); ?>: $<?php echo number_format($seatPrice, 2); ?>
                                            </div>
                                        </div>
                                        <?php if ($col == 5): ?>
                                            <div style="width: 40px;"></div> <!-- Aisle -->
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            <?php endforeach; ?>

                            <!-- Seat Legend -->
                            <div class="seat-legend">
                                <div class="legend-item">
                                    <div class="legend-color legend-available"></div>
                                    <span>Available</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color legend-selected"></div>
                                    <span>Selected</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color legend-booked"></div>
                                    <span>Booked</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color legend-regular"></div>
                                    <span>Regular: $<?php echo number_format($seatTypePrices['regular'], 2); ?></span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color legend-executive"></div>
                                    <span>Executive: $<?php echo number_format($seatTypePrices['executive'], 2); ?></span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color legend-premium"></div>
                                    <span>Premium: $<?php echo number_format($seatTypePrices['premium'], 2); ?></span>
                                </div>
                            </div>

                            <!-- Seat Type Info -->
                            <div class="mt-4 pt-3 border-top border-secondary">
                                <div class="row text-center">
                                    <div class="col-md-4 mb-3">
                                        <div class="text-gray">
                                            <i class="fas fa-couch text-regular me-1"></i>
                                            <span class="text-regular">Regular Seats</span>
                                            <div class="small">Standard viewing experience</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="text-gray">
                                            <i class="fas fa-couch text-warning me-1"></i>
                                            <span class="text-warning">Executive Seats</span>
                                            <div class="small">Extra legroom +$5.00</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="text-gray">
                                            <i class="fas fa-couch text-info me-1"></i>
                                            <span class="text-info">Premium Seats</span>
                                            <div class="small">Best view +$10.00</div>
                                        </div>
                                    </div>
                                </div>
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
                            <span>Selected Seats:</span>
                            <div class="selected-seats-container" id="selectedSeatsContainer">
                                <?php 
                                if (!empty($selectedSeats)) {
                                    for ($i = 0; $i < count($selectedSeats); $i++) {
                                        $seatNumber = $selectedSeats[$i];
                                        $seatType = $selectedSeatTypes[$i] ?? 'regular';
                                        $seatPrice = $seatTypePrices[$seatType] ?? $seatTypePrices['regular'];
                                        ?>
                                        <span class="selected-seat-badge" data-seat="<?php echo $seatNumber; ?>" data-type="<?php echo $seatType; ?>">
                                            <?php echo $seatNumber; ?>
                                            <span class="seat-type-badge badge-<?php echo $seatType; ?>">
                                                <?php echo ucfirst($seatType); ?>
                                            </span>
                                            <i class="fas fa-times" onclick="removeSeat('<?php echo $seatNumber; ?>')"></i>
                                        </span>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="price-breakdown" id="priceBreakdown">
                            <?php
                            $seatCounts = [
                                'regular' => 0,
                                'executive' => 0,
                                'premium' => 0
                            ];
                            
                            $seatTotals = [
                                'regular' => 0,
                                'executive' => 0,
                                'premium' => 0
                            ];
                            
                            if (!empty($selectedSeats)) {
                                for ($i = 0; $i < count($selectedSeats); $i++) {
                                    $seatType = $selectedSeatTypes[$i] ?? 'regular';
                                    $seatPrice = $seatTypePrices[$seatType];
                                    
                                    $seatCounts[$seatType]++;
                                    $seatTotals[$seatType] += $seatPrice;
                                }
                            }
                            
                            foreach (['regular', 'executive', 'premium'] as $type) {
                                if ($seatCounts[$type] > 0) {
                                    ?>
                                    <div class="breakdown-item">
                                        <div class="seat-type">
                                            <span class="badge badge-<?php echo $type; ?>"><?php echo ucfirst($type); ?></span>
                                            <span><?php echo $seatCounts[$type]; ?> x $<?php echo number_format($seatTypePrices[$type], 2); ?></span>
                                        </div>
                                        <span>$<?php echo number_format($seatTotals[$type], 2); ?></span>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="summary-item">
                            <span>Ticket Total:</span>
                            <span>$<span id="ticketTotal"><?php 
                                $ticketTotal = 0;
                                if (!empty($selectedSeats)) {
                                    for ($i = 0; $i < count($selectedSeats); $i++) {
                                        $seatType = $selectedSeatTypes[$i] ?? 'regular';
                                        $ticketTotal += $seatTypePrices[$seatType];
                                    }
                                }
                                echo number_format($ticketTotal, 2);
                            ?></span></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>No. of Tickets:</span>
                            <span id="ticketCount"><?php echo count($selectedSeats); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Convenience Fee:</span>
                            <span>$<span id="convenienceFee"><?php echo number_format(count($selectedSeats) * 1.5, 2); ?></span></span>
                        </div>
                        
                        <div class="summary-item summary-total">
                            <span>Total Amount:</span>
                            <span>$<span id="totalAmount"><?php 
                                $totalAmount = $ticketTotal + (count($selectedSeats) * 1.5);
                                echo number_format($totalAmount, 2);
                            ?></span></span>
                        </div>
                        
                        <button type="submit" name="select_seats" 
                                class="btn-continue" 
                                id="continueBtn"
                                <?php echo empty($selectedSeats) ? 'disabled' : ''; ?>>
                            <i class="fas fa-arrow-right"></i>
                            <span id="continueBtnText">
                                <?php if (empty($selectedSeats)): ?>
                                    Select Seats to Continue
                                <?php else: ?>
                                    Continue with <?php echo count($selectedSeats); ?> Ticket<?php echo count($selectedSeats) > 1 ? 's' : ''; ?>
                                <?php endif; ?>
                            </span>
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-gray">
                                <i class="fas fa-lock me-1"></i>
                                Secure Payment • Free Cancellation
                            </small>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="booking.php?id=<?php echo $showData['movie_id']; ?>&date=<?php echo $showData['show_date']; ?>" 
                               class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-left me-1"></i> Back to Shows
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
   // Replace the existing JavaScript with this updated version

<script>
    // Global variables
    let selectedSeats = []; // Will store objects: {seat: 'A1', type: 'executive'}
    const seatTypePrices = {
        regular: <?php echo $showData['ticket_price']; ?>,
        executive: <?php echo $showData['ticket_price'] + 2; ?>,
        premium: <?php echo $showData['ticket_price'] + 5; ?>
    };
    
    // Function to toggle seat selection
    function toggleSeat(seatNumber, seatType) {
        const seatIndex = selectedSeats.findIndex(s => s.seat === seatNumber);
        const seatElement = document.querySelector(`.seat[data-seat="${seatNumber}"]`);
        
        if (seatIndex === -1) {
            // Add seat
            selectedSeats.push({seat: seatNumber, type: seatType});
            seatElement.classList.add('selected');
            
            // Add to selected seats container
            const badge = document.createElement('span');
            badge.className = 'selected-seat-badge';
            badge.innerHTML = `
                ${seatNumber} 
                <span class="badge bg-warning text-dark ms-1">${seatType.charAt(0).toUpperCase() + seatType.slice(1)}</span>
                <i class="fas fa-times ms-2" onclick="removeSeat('${seatNumber}')"></i>
            `;
            document.getElementById('selectedSeatsContainer').appendChild(badge);
        } else {
            // Remove seat
            selectedSeats.splice(seatIndex, 1);
            seatElement.classList.remove('selected');
            
            // Remove from selected seats container
            const badges = document.querySelectorAll('.selected-seat-badge');
            badges.forEach(badge => {
                if (badge.textContent.includes(seatNumber)) {
                    badge.remove();
                }
            });
        }
        
        updateBookingSummary();
    }
    
    // Function to remove seat
    function removeSeat(seatNumber) {
        event.stopPropagation();
        const seatIndex = selectedSeats.findIndex(s => s.seat === seatNumber);
        if (seatIndex !== -1) {
            const seat = selectedSeats[seatIndex];
            const seatElement = document.querySelector(`.seat[data-seat="${seatNumber}"]`);
            seatElement.classList.remove('selected');
            selectedSeats.splice(seatIndex, 1);
            
            // Remove from selected seats container
            const badges = document.querySelectorAll('.selected-seat-badge');
            badges.forEach(badge => {
                if (badge.textContent.includes(seatNumber)) {
                    badge.remove();
                }
            });
            
            updateBookingSummary();
        }
    }
    
    // Function to update booking summary
    function updateBookingSummary() {
        const ticketCount = selectedSeats.length;
        
        // Calculate seat type counts and subtotal
        let seatTypeCounts = {
            regular: 0,
            executive: 0,
            premium: 0
        };
        
        let subtotal = 0;
        selectedSeats.forEach(seatData => {
            seatTypeCounts[seatData.type]++;
            subtotal += seatTypePrices[seatData.type];
        });
        
        const convenienceFee = ticketCount * 1.5;
        const totalAmount = subtotal + convenienceFee;
        
        // Update elements
        document.getElementById('ticketCount').textContent = ticketCount;
        document.getElementById('convenienceFee').textContent = convenienceFee.toFixed(2);
        document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
        
        // Update seat type breakdown (add this section to your HTML)
        updateSeatTypeBreakdown(seatTypeCounts, subtotal);
        
        // Update button state and text
        const continueBtn = document.getElementById('continueBtn');
        const continueBtnText = document.getElementById('continueBtnText');
        
        if (ticketCount > 0) {
            continueBtn.disabled = false;
            continueBtnText.textContent = `Continue with ${ticketCount} Ticket${ticketCount > 1 ? 's' : ''}`;
        } else {
            continueBtn.disabled = true;
            continueBtnText.textContent = 'Select Seats to Continue';
        }
        
        // Update hidden form fields
        // Remove existing inputs
        const existingSeatInputs = document.querySelectorAll('input[name="seats[]"]');
        const existingTypeInputs = document.querySelectorAll('input[name="seat_types[]"]');
        existingSeatInputs.forEach(input => input.remove());
        existingTypeInputs.forEach(input => input.remove());
        
        // Add new inputs
        selectedSeats.forEach(seatData => {
            const seatInput = document.createElement('input');
            seatInput.type = 'hidden';
            seatInput.name = 'seats[]';
            seatInput.value = seatData.seat;
            document.getElementById('bookingForm').appendChild(seatInput);
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'seat_types[]';
            typeInput.value = seatData.type;
            document.getElementById('bookingForm').appendChild(typeInput);
        });
    }
    
    // Function to update seat type breakdown
    function updateSeatTypeBreakdown(counts, subtotal) {
        const breakdownElement = document.getElementById('seatTypeBreakdown');
        if (!breakdownElement) return;
        
        let html = '';
        
        if (counts.regular > 0) {
            const price = seatTypePrices.regular;
            const total = counts.regular * price;
            html += `
                <div class="summary-item">
                    <span>
                        Regular (${counts.regular} × $${price.toFixed(2)})
                    </span>
                    <span>$${total.toFixed(2)}</span>
                </div>
            `;
        }
        
        if (counts.executive > 0) {
            const price = seatTypePrices.executive;
            const total = counts.executive * price;
            html += `
                <div class="summary-item">
                    <span>
                        Executive (${counts.executive} × $${price.toFixed(2)})
                    </span>
                    <span>$${total.toFixed(2)}</span>
                </div>
            `;
        }
        
        if (counts.premium > 0) {
            const price = seatTypePrices.premium;
            const total = counts.premium * price;
            html += `
                <div class="summary-item">
                    <span>
                        Premium (${counts.premium} × $${price.toFixed(2)})
                    </span>
                    <span>$${total.toFixed(2)}</span>
                </div>
            `;
        }
        
        // Add subtotal
        html += `
            <div class="summary-item">
                <span>Subtotal:</span>
                <span>$${subtotal.toFixed(2)}</span>
            </div>
        `;
        
        breakdownElement.innerHTML = html;
    }
    
    // Initialize summary on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize selected seats from PHP
        const phpSelectedSeats = <?php echo json_encode($selectedSeats); ?>;
        const phpSelectedSeatsData = <?php echo json_encode($selectedSeatsData ?? []); ?>;
        
        if (phpSelectedSeatsData && phpSelectedSeatsData.length > 0) {
            selectedSeats = phpSelectedSeatsData;
        } else if (phpSelectedSeats && phpSelectedSeats.length > 0) {
            // Backward compatibility: convert old format to new
            selectedSeats = phpSelectedSeats.map(seat => {
                const seatElement = document.querySelector(`.seat[data-seat="${seat}"]`);
                const type = seatElement ? seatElement.dataset.type : 'regular';
                return {seat: seat, type: type};
            });
            
            // Mark seats as selected visually
            selectedSeats.forEach(seatData => {
                const seatElement = document.querySelector(`.seat[data-seat="${seatData.seat}"]`);
                if (seatElement) {
                    seatElement.classList.add('selected');
                }
            });
        }
        
        updateBookingSummary();
        
        // Form submission handler
        document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
            if (selectedSeats.length === 0) {
                e.preventDefault();
                alert('Please select at least one seat');
                return false;
            }
        });
    });
</script>
</body>
</html>