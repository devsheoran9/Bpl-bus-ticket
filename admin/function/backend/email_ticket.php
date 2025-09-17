<?php
// function/backend/email_ticket.php
header('Content-Type: application/json');
include_once('../_db.php');
include_once('../_mailer.php'); // Your file with the sendBookingEmail function
session_security_check();

function send_json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Invalid request method.');
}

 

$booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
$recipient_email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$booking_id || !$recipient_email) {
    send_json_response('error', 'Invalid booking ID or email address provided.');
}

try { 
    $emailResult = sendBookingEmail($booking_id, $recipient_email, $_conn_db);
 
    send_json_response($emailResult['status'], $emailResult['message']);

} catch (Exception $e) {
    error_log("Email Ticket Action Error: " . $e->getMessage());
    send_json_response('error', 'A critical server error occurred while trying to send the email.');
}