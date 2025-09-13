<?php
header('Content-Type: application/json');

require './admin/vendor/autoload.php';
require  'config.php';
require  './admin/function/_db.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    if (empty($_POST['razorpay_payment_id'])) {
        throw new Exception("Payment ID is missing from the response.");
    }

    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    try {
        $attributes = [
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        ];
        $api->utility->verifyPaymentSignature($attributes);
    } catch (SignatureVerificationError $e) {
        throw new Exception('Razorpay Signature Verification Failed: ' . $e->getMessage());
    }

    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    if (!$booking_id) {
        throw new Exception("Invalid Booking ID was provided during verification.");
    }

    $payment_id = $_POST['razorpay_payment_id'];
    $order_id = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];
    $is_new_user = filter_var($_POST['is_new_user'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

    $_conn_db->beginTransaction();

    $stmt_booking = $_conn_db->prepare("UPDATE bookings SET booking_status = 'CONFIRMED', payment_status = 'PAID' WHERE booking_id = ? AND booking_status = 'PENDING'");
    $stmt_booking->execute([$booking_id]);

    if ($stmt_booking->rowCount() === 0) {
        throw new Exception("Booking could not be updated. It may have been already processed.");
    }

    $stmt_trans = $_conn_db->prepare("
        INSERT INTO transactions 
            (booking_id, user_id, payment_gateway, gateway_payment_id, gateway_order_id, gateway_signature, amount, currency, payment_status, method, created_at)
        SELECT 
            ?, user_id, 'Razorpay', ?, ?, ?, total_fare, 'INR', 'CAPTURED', 'online', NOW()
        FROM bookings 
        WHERE booking_id = ?
    ");
    $stmt_trans->execute([$booking_id, $payment_id, $order_id, $signature, $booking_id]);

    $_conn_db->commit();

    $response = ['success' => true, 'message' => 'Payment successful and booking confirmed!'];

    try {
        // Re-fetch all necessary details for the email, now including the payment ID
        $stmt_details = $_conn_db->prepare("
            SELECT b.*, t.gateway_payment_id 
            FROM bookings b
            LEFT JOIN transactions t ON b.booking_id = t.booking_id
            WHERE b.booking_id = ? 
            ORDER BY t.transaction_id DESC 
            LIMIT 1
        ");
        $stmt_details->execute([$booking_id]);
        $booking = $stmt_details->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            $stmt_passengers = $_conn_db->prepare("SELECT * FROM passengers WHERE booking_id = ?");
            $stmt_passengers->execute([$booking_id]);
            $passengers = $stmt_passengers->fetchAll(PDO::FETCH_ASSOC);

            $stmt_bus = $_conn_db->prepare("SELECT bus_name, registration_number FROM buses WHERE bus_id = ?");
            $stmt_bus->execute([$booking['bus_id']]);
            $bus_info = $stmt_bus->fetch(PDO::FETCH_ASSOC);

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

            $passenger_rows_html = '';
            foreach ($passengers as $p) {
                $passenger_rows_html .= "<tr><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars($p['passenger_name']) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars($p['passenger_age']) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars(ucfirst(strtolower($p['passenger_gender']))) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6; font-weight: bold; text-align: right;'>" . htmlspecialchars($p['seat_code']) . "</td></tr>";
            }
            $account_info_html = '';
            if ($is_new_user) {
                $account_info_html = "<div style='background-color:#e6f7ff; border:1px solid #91d5ff; padding:15px; border-radius:8px; margin: 20px 0; text-align: center;'><h4 style='margin:0 0 10px 0;'>Welcome! An account has been created for you.</h4><p style='margin:0;'><strong>Username:</strong> " . htmlspecialchars($booking['contact_email']) . " | <strong>Password:</strong> " . htmlspecialchars($booking['contact_mobile']) . "</p><small>(This is your mobile number. You can change it after logging in.)</small></div>";
            }

            $template_path =  'email_template.html';
            if (file_exists($template_path)) {
                $email_body = file_get_contents($template_path);

                // *** NEW: Create the full View Ticket URL ***
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'];
                $path_parts = pathinfo($_SERVER['PHP_SELF']);
                $directory = $path_parts['dirname'] == '/' ? '' : $path_parts['dirname'];
                $base_url = rtrim($protocol . $host . $directory, '/');
                $view_ticket_url = $base_url . '/view_ticket?id=' . urlencode($booking['booking_id']) . '&pnr=' . urlencode($booking['ticket_no']);

                // *** UPDATED: Added new placeholders and values ***
                $placeholders = [
                    '{{contact_name}}',
                    '{{account_info}}',
                    '{{ticket_no}}',
                    '{{travel_date}}',
                    '{{bus_details}}',
                    '{{total_fare}}',
                    '{{boarding_from}}',
                    '{{dropping_at}}',
                    '{{passenger_rows}}',
                    '{{payment_id}}',
                    '{{view_ticket_url}}'
                ];
                $replacements = [
                    htmlspecialchars($booking['contact_name']),
                    $account_info_html,
                    htmlspecialchars($booking['ticket_no']),
                    date('D, d M Y', strtotime($booking['travel_date'])),
                    htmlspecialchars($bus_info['bus_name'] . ' (' . $bus_info['registration_number'] . ')'),
                    number_format($booking['total_fare'], 2),
                    htmlspecialchars($booking['origin']),
                    htmlspecialchars($booking['destination']),
                    $passenger_rows_html,
                    htmlspecialchars($booking['gateway_payment_id'] ?? 'N/A'),
                    $view_ticket_url
                ];

                $email_body = str_replace($placeholders, $replacements, $email_body);
                $mail->Body = $email_body;
            } else {
                error_log("CRITICAL: Email template file not found at " . $template_path);
                $mail->Body = "Your booking is confirmed. Ticket No: " . htmlspecialchars($booking['ticket_no']);
            }

            $mail->send();
        }
    } catch (Exception $e) {
        error_log("Email could not be sent after payment for booking ID {$booking_id}. Mailer Error: {$mail->ErrorInfo}");
    }
} catch (Throwable $e) {
    if (isset($_conn_db) && $_conn_db->inTransaction()) {
        $_conn_db->rollBack();
    }
    http_response_code(400);
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
