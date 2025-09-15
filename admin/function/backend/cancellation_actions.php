<?php
// function/backend/cancellation_actions.php
header('Content-Type: application/json');
include_once('../_db.php');
require_once '../vendor/autoload.php';
include_once('../_mailer.php');
session_security_check();

function send_json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if (!user_has_permission('can_manage_cancellations')) {
    send_json_response('error', 'Access Denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';
$cancellation_id = filter_input(INPUT_POST, 'cancellation_id', FILTER_VALIDATE_INT);

if (!$cancellation_id) {
    send_json_response('error', 'Invalid Cancellation ID provided.');
}


if ($action === 'mark_refunded') {
    $new_status = 'COMPLETED';
    $gateway_refund_id = trim($_POST['gateway_refund_id'] ?? '');

    try {
        $sql = "UPDATE cancellations SET status = ?, gateway_refund_id = ? WHERE cancellation_id = ? AND status = 'PENDING'";
        $stmt = $_conn_db->prepare($sql);
        $stmt->execute([$new_status, $gateway_refund_id, $cancellation_id]);

        if ($stmt->rowCount() > 0) {
            sendCancellationStatusEmail($cancellation_id, $_conn_db);
            send_json_response('success', 'Refund has been successfully marked as COMPLETED.');
        } else {
            send_json_response('error', 'Could not update status. The request may have already been processed.');
        }
    } catch (PDOException $e) {
        error_log("Mark Refunded Error: " . $e->getMessage());
        send_json_response('error', 'A database error occurred.');
    }

} elseif ($action === 'mark_failed') {
    $reason = trim($_POST['reason'] ?? 'Processing failed.');
    
    $_conn_db->beginTransaction();
    try {
        // Step 1: Get all necessary IDs from the cancellation record
        $get_ids_stmt = $_conn_db->prepare("
            SELECT c.booking_id, c.passenger_id, p.seat_id, b.route_id, b.bus_id, b.travel_date
            FROM cancellations c
            JOIN passengers p ON c.passenger_id = p.passenger_id
            JOIN bookings b ON c.booking_id = b.booking_id
            WHERE c.cancellation_id = ? AND c.status = 'PENDING'
        ");
        $get_ids_stmt->execute([$cancellation_id]);
        $ids = $get_ids_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ids) {
            throw new Exception('Could not process. The request may have already been handled.');
        }

        // Step 2: Update the cancellation record to FAILED
        $stmt_cancel = $_conn_db->prepare("UPDATE cancellations SET status = 'FAILED', cancellation_reason = ? WHERE cancellation_id = ?");
        $stmt_cancel->execute([$reason, $cancellation_id]);

        // Step 3: Restore the passenger's status to CONFIRMED
        $stmt_passenger = $_conn_db->prepare("UPDATE passengers SET passenger_status = 'CONFIRMED' WHERE passenger_id = ?");
        $stmt_passenger->execute([$ids['passenger_id']]);

        // Step 4: Re-insert the seat into the booked_seats table to make it unavailable again
        // Use IGNORE to prevent an error if the seat somehow already exists (safety net)
        $stmt_booked_seat = $_conn_db->prepare(
            "INSERT IGNORE INTO booked_seats (booking_id, route_id, bus_id, seat_id, travel_date) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt_booked_seat->execute([$ids['booking_id'], $ids['route_id'], $ids['bus_id'], $ids['seat_id'], $ids['travel_date']]);

        // Step 5: Check if the main booking needs to be reactivated
        // This is not strictly necessary if you never set the main booking to CANCELLED unless ALL passengers are cancelled,
        // but it's a good safety check.
        $stmt_main_booking = $_conn_db->prepare(
            "UPDATE bookings SET booking_status = 'CONFIRMED' WHERE booking_id = ? AND booking_status = 'CANCELLED'"
        );
        $stmt_main_booking->execute([$ids['booking_id']]);

        // If all steps were successful, commit the transaction
        $_conn_db->commit();

        // After committing, send the notification email
        sendCancellationStatusEmail($cancellation_id, $_conn_db);
        
        send_json_response('success', 'Refund marked as FAILED. The passenger\'s ticket has been reinstated.');

    } catch (Exception $e) {
        $_conn_db->rollBack();
        error_log("Mark Failed Error: " . $e->getMessage());
        send_json_response('error', 'A database error occurred: ' . $e->getMessage());
    }
} else {
    send_json_response('error', 'Unknown action specified.');
}