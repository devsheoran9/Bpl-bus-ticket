<?php
// payment_verify.php
header('Content-Type: application/json');

include_once('function/_db.php');
require_once('vendor/autoload.php'); // Razorpay Autoloader
include_once('function/_mailer.php');   // Include your mailer function file

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// Access the global variables for Razorpay keys if they are defined in _db.php or another included file
global $rozerapi, $rozersecretapi;

// Use keys from the global scope. If they aren't set, provide a default or handle the error.
$keyId = $rozerapi ?? 'YOUR_DEFAULT_RAZORPAY_KEY_ID';
$keySecret = $rozersecretapi ?? 'YOUR_DEFAULT_RAZORPAY_SECRET';

$success = false; // Default to false for better security
$error = "Payment Failed";

// Check if the required Razorpay POST data exists
if (!empty($_POST['razorpay_payment_id']) && !empty($_POST['razorpay_order_id']) && !empty($_POST['razorpay_signature'])) {
    $api = new Api($keyId, $keySecret);
    try {
        $attributes = [
            'razorpay_order_id'   => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature'  => $_POST['razorpay_signature']
        ];
        // This function will throw an exception if the signature is invalid
        $api->utility->verifyPaymentSignature($attributes);
        $success = true; // Signature is valid
    } catch (SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error: ' . $e->getMessage();
    }
} else {
    $error = "Required payment data is missing.";
}

if ($success === true) {
    // Sanitize all inputs from the POST request
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $payment_id = filter_input(INPUT_POST, 'razorpay_payment_id');
    $order_id = filter_input(INPUT_POST, 'razorpay_order_id');
    $signature = filter_input(INPUT_POST, 'razorpay_signature');

    if (!$booking_id || !$payment_id || !$order_id || !$signature) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid payment data received.']);
        exit;
    }

    $_conn_db->beginTransaction();
    try {
        // 1. Update the booking to CONFIRMED and PAID
        $stmt_booking = $_conn_db->prepare("UPDATE bookings SET booking_status = 'CONFIRMED', payment_status = 'PAID' WHERE booking_id = ? AND booking_status = 'PENDING'");
        $stmt_booking->execute([$booking_id]);

        // 2. Insert the transaction record
        // This query smartly fetches the total_fare from the booking itself
        $stmt_trans = $_conn_db->prepare("
            INSERT INTO transactions (booking_id, gateway_payment_id, gateway_order_id, gateway_signature, amount, payment_status, method) 
            SELECT ?, ?, ?, ?, total_fare, 'CAPTURED', 'online' 
            FROM bookings WHERE booking_id = ?
        ");
        $stmt_trans->execute([$booking_id, $payment_id, $order_id, $signature, $booking_id]);
        
        // 3. Commit the database changes
        $_conn_db->commit();

        // --- EMAIL SENDING LOGIC ---
        // After successfully saving, fetch the contact email and send the ticket
        try {
            $stmt_email = $_conn_db->prepare("SELECT contact_email FROM bookings WHERE booking_id = ?");
            $stmt_email->execute([$booking_id]);
            $recipient_email = $stmt_email->fetchColumn();

            if ($recipient_email && filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                sendBookingEmail($booking_id, $recipient_email, $_conn_db);
            }
        } catch (Exception $e) {
            // Log the email error but don't fail the entire transaction, as payment is already complete.
            error_log("Email failed to send for booking_id {$booking_id}: " . $e->getMessage());
        }
        // --- END OF EMAIL LOGIC ---

        // 4. Send the final success response to the frontend
        echo json_encode(['status' => 'success', 'message' => 'Payment successfully verified and booking confirmed!']);

    } catch (PDOException $e) {
        $_conn_db->rollBack();
        error_log("Payment Verify DB Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database update failed after payment verification.']);
    }
} else {
    // This will run if the signature was invalid or data was missing
    echo json_encode(['status' => 'error', 'message' => $error]);
}
?>