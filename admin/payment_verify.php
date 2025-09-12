<?php
// payment_verify.php
header('Content-Type: application/json');

include_once('function/_db.php');
require_once('vendor/autoload.php'); // Razorpay Autoloader
include_once('function/_mailer.php');   // Include your mailer function file

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// Access the global variables for Razorpay keys defined in another file (e.g., _db.php)
global $rozerapi, $rozersecretapi;

// Use keys from the global scope. Provide a fallback if they aren't set.
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
    // --- KEY FIX: GET THE EMAIL DIRECTLY FROM THE POST DATA ---
    // This comes from the JavaScript fix we made earlier.
    $recipient_email = filter_input(INPUT_POST, 'contact_email', FILTER_VALIDATE_EMAIL);
    
    // Sanitize other inputs from the POST request
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $payment_id = isset($_POST['razorpay_payment_id']) ? htmlspecialchars($_POST['razorpay_payment_id'], ENT_QUOTES, 'UTF-8') : null;
    $order_id = isset($_POST['razorpay_order_id']) ? htmlspecialchars($_POST['razorpay_order_id'], ENT_QUOTES, 'UTF-8') : null;
    $signature = isset($_POST['razorpay_signature']) ? htmlspecialchars($_POST['razorpay_signature'], ENT_QUOTES, 'UTF-8') : null;

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
        $stmt_trans = $_conn_db->prepare("
            INSERT INTO transactions (booking_id, gateway_payment_id, gateway_order_id, gateway_signature, amount, payment_status, method) 
            SELECT ?, ?, ?, ?, total_fare, 'CAPTURED', 'online' 
            FROM bookings WHERE booking_id = ?
        ");
        $stmt_trans->execute([$booking_id, $payment_id, $order_id, $signature, $booking_id]);
        
        // 3. Commit the database changes
        $_conn_db->commit();

        // --- CORRECTED EMAIL SENDING LOGIC ---
        $email_status = 'not_sent';
        $email_message = 'No email address was provided.';
        
        // Use the $recipient_email variable we received directly from the frontend
        if ($recipient_email) {
            try {
                $emailResult = sendBookingEmail($booking_id, $recipient_email, $_conn_db);
                $email_status = $emailResult['status'];
                $email_message = $emailResult['message'];
            } catch (Exception $e) {
                error_log("Email failed to send for booking_id {$booking_id}: " . $e->getMessage());
                $email_status = 'error';
                $email_message = 'Failed to send ticket email due to a server error.';
            }
        }
        // --- END OF EMAIL LOGIC ---

        // 4. Send the final success response to the frontend, including email status
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment successfully verified and booking confirmed!',
            'email_status' => $email_status,
            'email_message' => $email_message
        ]);

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