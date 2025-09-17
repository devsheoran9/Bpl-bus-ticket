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




// ====================================================================
// --- ACTION: MARK REFUND AS COMPLETED (WITH AUTOMATIC AMOUNT) ---
// ====================================================================
if ($action === 'mark_refunded') {
    $gateway_refund_id = trim($_POST['gateway_refund_id'] ?? '');
    
    $_conn_db->beginTransaction();
    try {
        // --- STEP 1: GET THE ORIGINAL PASSENGER FARE ---
        // We join cancellations with passengers to find the original fare paid.
        $stmt_fare = $_conn_db->prepare("
        SELECT p.fare, p.passenger_id
        FROM cancellations c
        JOIN passengers p ON c.passenger_id = p.passenger_id
        WHERE c.cancellation_id = ?
    ");
    $stmt_fare->execute([$cancellation_id]);
    $row = $stmt_fare->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        throw new Exception('Could not find original fare for this cancellation request.');
    }
    
    $original_fare = $row['fare'];
    $passenger_id  = $row['passenger_id'];
    
    // delete passenger
   
        
        // --- STEP 2: UPDATE THE CANCELLATION RECORD ---
        // The `amount_refunded` is now the fetched `original_fare`.
        $sql = "UPDATE cancellations 
                SET 
                    status = 'COMPLETED', 
                    amount_refunded = ?, 
                    gateway_refund_id = ? 
                WHERE 
                    cancellation_id = ? AND status = 'PENDING'";
        $stmt = $_conn_db->prepare($sql);
        $stmt->execute([$original_fare, $gateway_refund_id, $cancellation_id]);

        if ($stmt->rowCount() > 0) {
            // If the update was successful, commit the changes
            $_conn_db->commit();
            // Then send the confirmation email
            sendCancellationStatusEmail($cancellation_id, $_conn_db);
            $stmt_del = $_conn_db->prepare("DELETE FROM passengers WHERE passenger_id = ?");
            $stmt_del->execute([$passenger_id]);
                
                if ($stmt_del === false) {
                    throw new Exception('error');
                }
            send_json_response('success', 'Refund has been successfully marked as COMPLETED for ₹' . number_format($original_fare, 2) . '.');
        } else {
            // This means the cancellation was not in PENDING state or didn't exist
            throw new Exception('Could not update status. The request may have already been processed.');
        }
    } catch (Exception $e) {
        $_conn_db->rollBack();
        error_log("Mark Refunded Error: " . $e->getMessage());
        send_json_response('error', $e->getMessage());
    }

} 
// ====================================================================
// --- ACTION: MARK REFUND AS FAILED (REINSTATE TICKET) ---
// ====================================================================
elseif ($action === 'mark_failed') {
    // This logic is for reinstating the ticket and is already correct.
    $reason = trim($_POST['reason'] ?? 'Processing failed.');
    
    $_conn_db->beginTransaction();
    try {
        $get_ids_stmt = $_conn_db->prepare("SELECT c.booking_id, c.passenger_id, p.seat_id, b.route_id, b.bus_id, b.travel_date FROM cancellations c JOIN passengers p ON c.passenger_id = p.passenger_id JOIN bookings b ON c.booking_id = b.booking_id WHERE c.cancellation_id = ? AND c.status = 'PENDING'");
        $get_ids_stmt->execute([$cancellation_id]);
        $ids = $get_ids_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ids) { throw new Exception('Request already processed.'); }

        $stmt_cancel = $_conn_db->prepare("UPDATE cancellations SET status = 'FAILED', cancellation_reason = ? WHERE cancellation_id = ?");
        $stmt_cancel->execute([$reason, $cancellation_id]);

        $stmt_passenger = $_conn_db->prepare("UPDATE passengers SET passenger_status = 'CONFIRMED' WHERE passenger_id = ?");
        $stmt_passenger->execute([$ids['passenger_id']]);

      

        $stmt_main_booking = $_conn_db->prepare("UPDATE bookings SET booking_status = 'CONFIRMED' WHERE booking_id = ? AND booking_status = 'CANCELLED'");
        $stmt_main_booking->execute([$ids['booking_id']]);

        $_conn_db->commit();
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