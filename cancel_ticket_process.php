<?php 
require './admin/function/_db.php'; 

// Security check: Only allow POST requests from logged-in users
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    // If not, redirect to login or home.
    header("Location: login.php");
    exit();
}

// --- MAIN CANCELLATION LOGIC ---
$booking_id_to_cancel = $_POST['booking_id'] ?? 0;
$passengers_to_cancel = $_POST['passengers_to_cancel'] ?? [];
$cancellation_reason = trim($_POST['cancellation_reason'] ?? 'Not provided');
$ticket_no_for_redirect = $_POST['ticket_no'] ?? '';
$email_for_redirect = $_POST['email'] ?? '';

if (empty($passengers_to_cancel)) {
    $_SESSION['error_message'] = "Please select at least one passenger to cancel.";
} else {
    try {
        $pdo->beginTransaction();

        // Re-fetch booking details to re-verify the cancellation deadline (CRITICAL security check)
        $verify_stmt = $pdo->prepare("
            SELECT b.route_id, b.travel_date, b.total_fare
            FROM bookings b
            WHERE b.booking_id = ? AND b.booking_status = 'CONFIRMED'
        ");
        $verify_stmt->execute([$booking_id_to_cancel]);
        $booking_info = $verify_stmt->fetch();

        if (!$booking_info) {
            throw new Exception("This booking is either invalid or already cancelled.");
        }

        $day_of_week = date('D', strtotime($booking_info['travel_date']));
        $time_stmt = $pdo->prepare("SELECT departure_time FROM route_schedules WHERE route_id = ? AND operating_day = ?");
        $time_stmt->execute([$booking_info['route_id'], $day_of_week]);
        $departure_time = $time_stmt->fetchColumn();

        if (!$departure_time) {
            throw new Exception("Could not determine the departure schedule. Cancellation failed.");
        }

        $departure_datetime_str = $booking_info['travel_date'] . ' ' . $departure_time;
        $departure_timestamp = strtotime($departure_datetime_str);
        $cancellation_deadline = $departure_timestamp - (30 * 60);

        if (time() > $cancellation_deadline) {
            throw new Exception("The cancellation period for this ticket has expired.");
        }

        $total_refund_amount = 0;

        foreach ($passengers_to_cancel as $passenger_id) {
            $passenger_stmt = $pdo->prepare("SELECT fare FROM passengers WHERE passenger_id = ? AND booking_id = ? AND passenger_status = 'CONFIRMED'");
            $passenger_stmt->execute([$passenger_id, $booking_id_to_cancel]);
            $passenger_fare = $passenger_stmt->fetchColumn();

            if ($passenger_fare !== false) {
                $update_passenger_stmt = $pdo->prepare("UPDATE passengers SET passenger_status = 'CANCELLED' WHERE passenger_id = ?");
                $update_passenger_stmt->execute([$passenger_id]);
                
                $total_refund_amount += $passenger_fare;

                // Log the cancellation with the reason
                $log_cancel_stmt = $pdo->prepare(
                    "INSERT INTO cancellations (booking_id, passenger_id, amount_refunded, cancellation_reason, status) VALUES (?, ?, ?, ?, 'COMPLETED')"
                );
                $log_cancel_stmt->execute([$booking_id_to_cancel, $passenger_id, $passenger_fare, $cancellation_reason]);
            }
        }

        if ($total_refund_amount > 0) {
            $new_total_fare = max(0, $booking_info['total_fare'] - $total_refund_amount); // Ensure fare doesn't go below zero
            $update_booking_fare_stmt = $pdo->prepare("UPDATE bookings SET total_fare = ? WHERE booking_id = ?");
            $update_booking_fare_stmt->execute([$new_total_fare, $booking_id_to_cancel]);
        }

        $check_all_cancelled_stmt = $pdo->prepare("SELECT COUNT(*) FROM passengers WHERE booking_id = ? AND passenger_status = 'CONFIRMED'");
        $check_all_cancelled_stmt->execute([$booking_id_to_cancel]);
        if ($check_all_cancelled_stmt->fetchColumn() == 0) {
            $update_booking_status_stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'CANCELLED', payment_status = 'REFUNDED' WHERE booking_id = ?");
            $update_booking_status_stmt->execute([$booking_id_to_cancel]);
        } else {
            $update_booking_status_stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'PENDING' WHERE booking_id = ?");
            $update_booking_status_stmt->execute([$booking_id_to_cancel]);
        }
        
        // NOTE: Actual refund API call would go here.

        $pdo->commit();
        $_SESSION['success_message'] = "Selected ticket(s) have been successfully cancelled. A refund of ₹" . number_format($total_refund_amount, 2) . " has been processed and refund in 24-48 hours in your account.";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Cancellation failed: " . $e->getMessage();
    }
}

// This redirect will now work correctly because no HTML has been sent.
header("Location: cancel_ticket.php?ticket_no=" . urlencode($ticket_no_for_redirect) . "&email=" . urlencode($email_for_redirect));
exit();