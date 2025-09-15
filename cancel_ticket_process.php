<?php 
require './admin/function/_db.php'; 

// Security check: Only allow POST requests from logged-in users
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- MAIN CANCELLATION LOGIC ---
$booking_id_to_cancel = $_POST['booking_id'] ?? 0;
$passengers_to_cancel = $_POST['passengers_to_cancel'] ?? [];
$cancellation_reason = trim($_POST['cancellation_reason'] ?? 'Not provided');
$ticket_no_for_redirect = $_POST['ticket_no'] ?? '';
$redirect_url = "cancel_ticket.php?ticket_no=" . urlencode($ticket_no_for_redirect);

if (empty($passengers_to_cancel)) {
    $_SESSION['error_message'] = "Please select at least one passenger to cancel.";
} else {
    try {
        $pdo->beginTransaction();

        // Re-fetch booking details to re-verify the cancellation deadline
        $verify_stmt = $pdo->prepare("SELECT b.route_id, b.travel_date FROM bookings b WHERE b.booking_id = ?");
        $verify_stmt->execute([$booking_id_to_cancel]);
        $booking_info = $verify_stmt->fetch();

        if (!$booking_info) {
            throw new Exception("This booking is invalid.");
        }

        // Re-verify the cancellation time window on the server-side
        $day_of_week = date('D', strtotime($booking_info['travel_date']));
        $time_stmt = $pdo->prepare("SELECT departure_time FROM route_schedules WHERE route_id = ? AND operating_day LIKE ?");
        $time_stmt->execute([$booking_info['route_id'], '%' . $day_of_week . '%']);
        $departure_time = $time_stmt->fetchColumn();

        if (!$departure_time) {
            throw new Exception("Could not determine the departure schedule. Cancellation failed.");
        }

        $departure_timestamp = strtotime($booking_info['travel_date'] . ' ' . $departure_time);
        $cancellation_deadline = $departure_timestamp - (12 * 60 * 60); // 12-hour rule

        if (time() > $cancellation_deadline) {
            throw new Exception("The cancellation period for this ticket has expired.");
        }

        foreach ($passengers_to_cancel as $passenger_id) {
            // Check 1: Ensure the passenger is actually 'CONFIRMED'
            $passenger_check_stmt = $pdo->prepare("SELECT passenger_id FROM passengers WHERE passenger_id = ? AND booking_id = ? AND passenger_status = 'CONFIRMED'");
            $passenger_check_stmt->execute([$passenger_id, $booking_id_to_cancel]);

            if ($passenger_check_stmt->fetch()) {
                // Check 2: Ensure a 'PENDING' request for this passenger doesn't already exist
                $pending_check_stmt = $pdo->prepare("SELECT cancellation_id FROM cancellations WHERE passenger_id = ? AND status = 'PENDING'");
                $pending_check_stmt->execute([$passenger_id]);

                if (!$pending_check_stmt->fetch()) {
                    // All checks passed, insert the cancellation request
                    $log_cancel_stmt = $pdo->prepare(
                        "INSERT INTO cancellations (booking_id, passenger_id, amount_refunded, cancellation_reason, status) VALUES (?, ?, 0, ?, 'PENDING')"
                    );
                    // amount_refunded is 0 because it's a request, not a completed refund.
                    $log_cancel_stmt->execute([$booking_id_to_cancel, $passenger_id, $cancellation_reason]);
                }
            }
        }

        $pdo->commit();

        // New, more accurate Success Message
        $_SESSION['success_message'] = "Your cancellation request has been submitted successfully. You will be notified via email once it is confirmed by our team. If approved, the refund will be processed within 24-48 hours.";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Cancellation request failed: " . $e->getMessage();
    }
}

// Redirect back to the cancellation page to show the success/error message
header("Location: " . $redirect_url);
exit();
