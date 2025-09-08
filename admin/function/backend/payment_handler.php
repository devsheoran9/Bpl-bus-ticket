<?php
// function/backend/payment_handler.php
header('Content-Type: application/json');
include_once('../../function/_db.php');
session_security_check();

$action = $_POST['action'] ?? '';

if ($action === 'log_successful_payment') {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_details = json_decode($_POST['payment_details'], true);

    if (!$booking_id || !$amount || empty($payment_details)) {
        send_json_response('error', 'Invalid payment data received.');
    }

    $_conn_db->beginTransaction();
    try {
        // Step 1: Log the detailed transaction
        $sql_transaction = "INSERT INTO transactions 
                            (booking_id, employee_id, gateway_payment_id, gateway_order_id, gateway_signature, 
                             amount, payment_status, method) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_transaction = $_conn_db->prepare($sql_transaction);
        $stmt_transaction->execute([
            $booking_id,
            $_SESSION['user']['id'],
            $payment_details['gateway_payment_id'],
            $payment_details['gateway_order_id'],
            $payment_details['gateway_signature'],
            $amount,
            $payment_details['status'], // 'CAPTURED'
            $payment_details['method']  // 'upi', 'card' etc.
        ]);

        // Step 2: Update the main booking table to reflect the successful payment
        $sql_update_booking = "UPDATE bookings SET 
                               payment_status = 'PAID',
                               booking_status = 'CONFIRMED'
                               WHERE booking_id = ?";
        $stmt_update = $_conn_db->prepare($sql_update_booking);
        $stmt_update->execute([$booking_id]);

        $_conn_db->commit();
        send_json_response('success', 'Transaction logged and booking confirmed.');

    } catch (PDOException $e) {
        $_conn_db->rollBack();
        error_log("Log Payment Error: " . $e->getMessage());
        send_json_response('error', 'Database error while logging payment.');
    }
}
?>