<?php
header('Content-Type: application/json');

require "./admin/function/_db.php";
require 'config.php';
require 'vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = false;
$error = "Payment Failed";

if (!empty($_POST['razorpay_payment_id'])) {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    try {
        // This is the crucial security step to verify the payment is legitimate
        $attributes = [
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        ];
        $api->utility->verifyPaymentSignature($attributes);
        $success = true;
    } catch (SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true) {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $payment_id = $_POST['razorpay_payment_id'];
    $is_new_user = filter_input(INPUT_POST, 'is_new_user', FILTER_VALIDATE_BOOLEAN);

    if (!$booking_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid Booking ID provided.']);
        exit;
    }

    try {
        $_conn_db->beginTransaction();

        // 1. Update the booking status from PENDING to CONFIRMED
        $stmt_booking = $_conn_db->prepare("UPDATE bookings SET booking_status = 'CONFIRMED', payment_status = 'PAID' WHERE booking_id = ? AND payment_status = 'PENDING'");
        $stmt_booking->execute([$booking_id]);

        // 2. Insert the transaction record for your reference
        $stmt_trans = $_conn_db->prepare("INSERT INTO transactions (booking_id, gateway_payment_id, gateway_order_id, amount, payment_status, method) SELECT ?, ?, ?, total_fare, 'CAPTURED', 'online' FROM bookings WHERE booking_id = ?");
        $stmt_trans->execute([$booking_id, $payment_id, $_POST['razorpay_order_id'], $booking_id]);

        $_conn_db->commit();

        // --- Send Confirmation Email AFTER successful verification ---
        try {
            // Re-fetch all booking details to build the comprehensive email
            $stmt_details = $_conn_db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
            $stmt_details->execute([$booking_id]);
            $booking = $stmt_details->fetch(PDO::FETCH_ASSOC);

            $stmt_passengers = $_conn_db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
            $stmt_passengers->execute([$booking_id]);
            $passengers = $stmt_passengers->fetchAll(PDO::FETCH_ASSOC);

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($booking['contact_email'], $booking['contact_name']);
            $mail->isHTML(true);
            $mail->Subject = 'Booking Confirmed! Your Ticket: ' . $booking['ticket_no'];

            // Build the email body
            $email_body = "<p>Hello " . htmlspecialchars($booking['contact_name']) . ",</p><p>Your payment was successful and your ticket is confirmed!</p>";

            // Conditionally add the welcome message for new users
            if ($is_new_user) {
                $email_body .= "<div style='background-color:#e3eeff; border:1px solid #b8d6fb; padding:15px; border-radius:8px; margin:20px 0;'><h4>Welcome! An account has been created for you.</h4><p>You can now log in to manage all your bookings:</p><p><strong>Username:</strong> " . htmlspecialchars($booking['contact_email']) . "</p><p><strong>Password:</strong> " . htmlspecialchars($booking['contact_mobile']) . " <em>(We recommend changing this after your first login.)</em></p></div>";
            }

            // Add ticket details to the email
            $email_body .= "<h3>Booking Details</h3><table border='1' cellpadding='10' cellspacing='0' style='width:100%; border-collapse:collapse;'><tr><td style='width:30%;'><strong>Ticket No (PNR):</strong></td><td><strong>" . htmlspecialchars($booking['ticket_no']) . "</strong></td></tr><tr><td><strong>Journey:</strong></td><td>" . htmlspecialchars($booking['origin']) . " to " . htmlspecialchars($booking['destination']) . "</td></tr><tr><td><strong>Travel Date:</strong></td><td>" . date('d M, Y', strtotime($booking['travel_date'])) . "</td></tr><tr><td><strong>Total Fare:</strong></td><td>₹" . number_format($booking['total_fare'], 2) . "</td></tr></table>";
            $email_body .= "<h3>Passenger Details</h3><table border='1' cellpadding='10' cellspacing='0' style='width:100%; border-collapse:collapse;'><thead><tr><th>Name</th><th>Age</th><th>Gender</th><th>Seat No</th></tr></thead><tbody>";
            foreach ($passengers as $p) {
                $email_body .= "<tr><td>" . htmlspecialchars($p['passenger_name']) . "</td><td>" . htmlspecialchars($p['passenger_age']) . "</td><td>" . htmlspecialchars(ucfirst(strtolower($p['passenger_gender']))) . "</td><td>" . htmlspecialchars($p['seat_code']) . "</td></tr>";
            }
            $email_body .= "</tbody></table><p>Thank you for booking with us. Have a safe journey!</p>";

            $mail->Body = $email_body;
            $mail->send();
        } catch (Exception $e) {
            // Log the email error but don't fail the response to the user
            error_log("Email could not be sent after payment for booking ID {$booking_id}. Mailer Error: {$mail->ErrorInfo}");
        }

        echo json_encode(['success' => true, 'message' => 'Payment successful and booking confirmed!']);
    } catch (PDOException $e) {
        $_conn_db->rollBack();
        error_log("Payment Verify DB Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database update failed after payment. Please contact support.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => $error]);
}
