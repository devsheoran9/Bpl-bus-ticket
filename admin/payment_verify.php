<?php
// payment_verify.php
header('Content-Type: application/json');
include_once('function/_db.php');
require_once('vendor/autoload.php'); // Razorpay Autoloader
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

 

$keyId = $rozerapi;
$keySecret = $rozersecretapi;

$success = true;
$error = "Payment Failed";

if (empty($_POST['razorpay_payment_id']) === false) {
    $api = new Api($keyId, $keySecret);
    try {
        $attributes = [
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        ];
        $api->utility->verifyPaymentSignature($attributes);
    } catch(SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true) {
    // Signature is valid, now update our database
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $payment_id = $_POST['razorpay_payment_id'];
    $order_id = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];

    $_conn_db->beginTransaction();
    try {
        // 1. Update the booking to CONFIRMED and PAID
        $stmt_booking = $_conn_db->prepare("UPDATE bookings SET booking_status = 'CONFIRMED', payment_status = 'PAID' WHERE booking_id = ?");
        $stmt_booking->execute([$booking_id]);

        // 2. Insert the transaction record
        $stmt_trans = $_conn_db->prepare("INSERT INTO transactions (booking_id, gateway_payment_id, gateway_order_id, gateway_signature, amount, payment_status, method) SELECT ?, ?, ?, ?, total_fare, 'CAPTURED', 'online' FROM bookings WHERE booking_id = ?");
        $stmt_trans->execute([$booking_id, $payment_id, $order_id, $signature, $booking_id]);
        
        $_conn_db->commit();

        echo json_encode(['status' => 'success', 'message' => 'Payment successfully verified and booking confirmed!']);
    } catch (PDOException $e) {
        $_conn_db->rollBack();
        error_log("Payment Verify DB Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database update failed after payment verification.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => $error]);
}
?>