<?php
header('Content-Type: application/json');

// Use __DIR__ for robust paths
require __DIR__ . '/admin/vendor/autoload.php';
require __DIR__ . '/config.php';
require __DIR__ . '/admin/function/_db.php'; // Ensure this path is correct

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Prepare a default response in case of early failure
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['razorpay_payment_id'])) {
        throw new Exception("Invalid request or Payment ID is missing.");
    }

    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    // Securely verify the payment signature from Razorpay
    try {
        $attributes = [
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        ];
        $api->utility->verifyPaymentSignature($attributes);
    } catch (SignatureVerificationError $e) {
        error_log('Razorpay Signature Verification Failed: ' . $e->getMessage());
        throw new Exception('Payment verification failed. If the amount was deducted, please contact support with your payment ID.');
    }

    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    if (!$booking_id) {
        throw new Exception("Invalid Booking ID was provided during verification.");
    }

    $payment_id = $_POST['razorpay_payment_id'];
    $order_id = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];
    $is_new_user = filter_var($_POST['is_new_user'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

    // --- Database Operations ---
    $_conn_db->beginTransaction();

    // 1. Update booking status from PENDING to CONFIRMED. Check rowCount to prevent reprocessing.
    $stmt_booking = $_conn_db->prepare("UPDATE bookings SET booking_status = 'CONFIRMED', payment_status = 'PAID' WHERE booking_id = ? AND booking_status = 'PENDING'");
    $stmt_booking->execute([$booking_id]);

    if ($stmt_booking->rowCount() === 0) {
        throw new Exception("Booking could not be updated. It may have been already processed or does not exist.");
    }

    // 2. Insert the transaction record into the `transactions` table
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

    // --- Send Confirmation Email (after DB commit) ---
    try {
        // Re-fetch all necessary details for the email
        $stmt_details = $_conn_db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
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

            // *** FIX: Safely load the email template ***
            $template_path = __DIR__ . '/email_template.html';
            if (file_exists($template_path)) {
                $email_body = file_get_contents($template_path);
                $email_body = str_replace(['{{contact_name}}', '{{account_info}}', '{{ticket_no}}', '{{travel_date}}', '{{bus_details}}', '{{total_fare}}', '{{boarding_from}}', '{{dropping_at}}', '{{passenger_rows}}'], [htmlspecialchars($booking['contact_name']), $account_info_html, htmlspecialchars($booking['ticket_no']), date('D, d M Y', strtotime($booking['travel_date'])), htmlspecialchars($bus_info['bus_name'] . ' (' . $bus_info['registration_number'] . ')'), number_format($booking['total_fare'], 2), htmlspecialchars($booking['origin']), htmlspecialchars($booking['destination']), $passenger_rows_html], $email_body);
                $mail->Body = $email_body;
            } else {
                // Fallback to a simple email if the template is missing, and log the error.
                error_log("Email template file not found at: " . $template_path);
                $mail->isHTML(false);
                $mail->Body = "Dear " . htmlspecialchars($booking['contact_name']) . ",\n\nYour booking has been confirmed.\n\nTicket No: " . htmlspecialchars($booking['ticket_no']) . "\nTravel Date: " . date('D, d M Y', strtotime($booking['travel_date'])) . "\nTotal Fare: INR " . number_format($booking['total_fare'], 2) . "\n\nThank you for choosing our service.";
            }

            $mail->send();
        }
    } catch (Exception $e) {
        // Log the email error for the admin, but don't let it break the user's flow.
        error_log("Email could not be sent for booking ID {$booking_id}. Mailer Error: " . (isset($mail) ? $mail->ErrorInfo : $e->getMessage()));
    }
} catch (Throwable $e) {
    if (isset($_conn_db) && $_conn_db->inTransaction()) {
        $_conn_db->rollBack();
    }
    http_response_code(400); // Send a "Bad Request" status code
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// ALWAYS send a valid JSON response back to the browser
echo json_encode($response);
