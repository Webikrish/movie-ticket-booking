<?php
// test_db.php
require_once 'db_connection.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "Database connection successful!<br>";
    
    // Test inserting a record
    $testQuery = "INSERT INTO bookings (user_id, show_id, seat_numbers, total_seats, total_amount, convenience_fee, customer_name, customer_email, customer_phone, status, booking_date) VALUES (1, 1, 'A1,A2', 2, 50.00, 3.00, 'Test', 'test@test.com', '1234567890', 'pending', NOW())";
    
    try {
        $result = $db->exec($testQuery);
        echo "Test insert successful!<br>";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Database connection failed!<br>";
}
?>