<?php
// clear_booking_session.php
session_start();
unset($_SESSION['payment_success']);
echo 'Session cleared';
?>