<?php
header('Content-Type: application/json');
require './admin/vendor/autoload.php';
require 'config.php';
require './admin/function/_db.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables at the top
$booking_id = null;
$is_new_user_flag = false;

try {
    // --- 1. VERIFY PAYMENT SIGNATURE ---
    if (empty($_POST['razorpay_payment_id'])) {
        throw new Exception("Payment ID is missing.");
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
        throw new Exception('Razorpay Signature Verification Failed.');
    }

    // --- 2. RETRIEVE BOOKING DATA FROM SESSION ---
    $razorpay_order_id = $_POST['razorpay_order_id'];
    $session_key = 'pending_booking_' . $razorpay_order_id;

    if (!isset($_SESSION[$session_key])) {
        throw new Exception("Booking session data not found. Your payment was successful, please contact support with your Payment ID.");
    }

    $booking_data = $_SESSION[$session_key];
    $is_new_user_flag = $booking_data['new_user'];

    // --- 3. SAVE TO DATABASE (NOW THAT PAYMENT IS CONFIRMED) ---
    $_conn_db->beginTransaction();

    $user_id = $booking_data['user_id'];

    if ($is_new_user_flag && is_null($user_id)) {
        $password_hash = password_hash($booking_data['contact_mobile'], PASSWORD_DEFAULT);
        $stmt_create_user = $_conn_db->prepare("INSERT INTO users (username, password, mobile_no, email, ip_address, status, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt_create_user->execute([$booking_data['contact_name'], $password_hash, $booking_data['contact_mobile'], $booking_data['contact_email'], $_SERVER['REMOTE_ADDR']]);
        $user_id = $_conn_db->lastInsertId();
    }

    $ticket_no = 'BPL' . substr(str_shuffle(str_repeat('0123456789', 9)), 0, 9);
    $booking_sql = "INSERT INTO bookings (ticket_no, route_id, bus_id, user_id, origin, destination, contact_name, contact_email, contact_mobile, travel_date, total_fare, payment_status, booking_status, gateway_order_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PAID', 'CONFIRMED', ?, NOW())";
    $stmt_booking = $_conn_db->prepare($booking_sql);
    $stmt_booking->execute([
        $ticket_no,
        $booking_data['route_id'],
        $booking_data['bus_id'],
        $user_id,
        $booking_data['origin'],
        $booking_data['destination'],
        $booking_data['contact_name'],
        $booking_data['contact_email'],
        $booking_data['contact_mobile'],
        $booking_data['travel_date'],
        $booking_data['total_fare'],
        $razorpay_order_id
    ]);
    $booking_id = $_conn_db->lastInsertId(); // This line is crucial

    $passenger_sql = "INSERT INTO passengers (booking_id, seat_id, seat_code, passenger_name, passenger_age, passenger_gender, fare, passenger_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'CONFIRMED')";
    $stmt_passenger = $_conn_db->prepare($passenger_sql);
    $get_seat_id_sql = "SELECT seat_id FROM seats WHERE bus_id = ? AND seat_code = ?";
    $stmt_get_seat_id = $_conn_db->prepare($get_seat_id_sql);

    foreach ($booking_data['passengers'] as $p) {
        $stmt_get_seat_id->execute([$booking_data['bus_id'], $p['seat_code']]);
        $fetched_seat_id = $stmt_get_seat_id->fetchColumn();
        $stmt_passenger->execute([$booking_id, $fetched_seat_id, $p['seat_code'], $p['name'], $p['age'], $p['gender'], $p['fare']]);
    }

    $stmt_trans = $_conn_db->prepare("INSERT INTO transactions (booking_id, user_id, payment_gateway, gateway_payment_id, gateway_order_id, gateway_signature, amount, currency, payment_status, method, created_at) VALUES (?, ?, 'Razorpay', ?, ?, ?, ?, 'INR', 'CAPTURED', 'online', NOW())");
    $stmt_trans->execute([$booking_id, $user_id, $_POST['razorpay_payment_id'], $razorpay_order_id, $_POST['razorpay_signature'], $booking_data['total_fare']]);

    $_conn_db->commit();

    unset($_SESSION[$session_key]);

    // --- 4. SEND CONFIRMATION EMAIL (COMPLETE CODE) ---
    try {
        $stmt_details = $_conn_db->prepare("SELECT b.*, t.gateway_payment_id, bu.bus_name, bu.registration_number, rs.departure_time FROM bookings b LEFT JOIN transactions t ON b.booking_id = t.booking_id JOIN buses bu ON b.bus_id = bu.bus_id JOIN routes r ON b.route_id = r.route_id LEFT JOIN route_schedules rs ON b.route_id = rs.route_id AND rs.operating_day LIKE CONCAT('%', DATE_FORMAT(b.travel_date, '%a'), '%') WHERE b.booking_id = ? ORDER BY t.transaction_id DESC LIMIT 1");
        $stmt_details->execute([$booking_id]);
        $booking = $stmt_details->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            $route_departure_datetime = new DateTime($booking['travel_date'] . ' ' . ($booking['departure_time'] ?? '00:00'));
            $stmt_origin = $_conn_db->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
            $stmt_origin->execute([$booking['route_id'], $booking['origin']]);
            $origin_minutes = (int)$stmt_origin->fetchColumn();
            $stmt_dest = $_conn_db->prepare("SELECT duration_from_start_minutes FROM route_stops WHERE route_id = ? AND stop_name = ?");
            $stmt_dest->execute([$booking['route_id'], $booking['destination']]);
            $destination_minutes = (int)$stmt_dest->fetchColumn();
            $actual_departure_datetime = (clone $route_departure_datetime)->modify("+$origin_minutes minutes");
            $actual_arrival_datetime = (clone $route_departure_datetime)->modify("+$destination_minutes minutes");

            $tokenStmt = $_conn_db->prepare("SELECT token FROM ticket_access_tokens WHERE booking_id = ?");
            $tokenStmt->execute([$booking_id]);
            $token = $tokenStmt->fetchColumn();
            if (!$token) {
                $token = bin2hex(random_bytes(20));
                $insertStmt = $_conn_db->prepare("INSERT INTO ticket_access_tokens (booking_id, token) VALUES (?, ?)");
                $insertStmt->execute([$booking_id, $token]);
            }

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $base_url = rtrim($protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']), '/');
            $view_ticket_url = $base_url . '/ticket_public_view.php?token=' . urlencode($token);

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

            $passenger_rows_html = '';
            foreach ($passengers as $p) {
                $passenger_rows_html .= "<tr><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars($p['passenger_name']) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars($p['passenger_age']) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars(ucfirst(strtolower($p['passenger_gender']))) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6; font-weight: bold; text-align: right;'>" . htmlspecialchars($p['seat_code']) . "</td></tr>";
            }

            $account_info_html = '';
            if ($is_new_user_flag) {
                $account_info_html = "<div style='background-color:#e6f7ff; border:1px solid #91d5ff; padding:15px; border-radius:8px; margin: 20px 0; text-align: center;'><h4 style='margin:0 0 10px 0;'>Welcome! You can now login using:</h4><p style='margin:0;'><strong>Username:</strong> " . htmlspecialchars($booking['contact_email']) . " | <strong>Password:</strong> " . htmlspecialchars($booking['contact_mobile']) . "</p><small>(This is your mobile number. You can change it after logging in.)</small></div>";
            }

            $template_path = 'email_template.html';
            if (file_exists($template_path)) {
                $email_body = file_get_contents($template_path);
                $placeholders = ['{{contact_name}}', '{{account_info}}', '{{ticket_no}}', '{{travel_date}}', '{{bus_details}}', '{{total_fare}}', '{{boarding_from}}', '{{dropping_at}}', '{{passenger_rows}}', '{{payment_id}}', '{{view_ticket_url}}', '{{boarding_time}}', '{{arrival_time}}'];
                $replacements = [htmlspecialchars($booking['contact_name']), $account_info_html, htmlspecialchars($booking['ticket_no']), date('D, d M Y', strtotime($booking['travel_date'])), htmlspecialchars($booking['bus_name'] . ' (' . $booking['registration_number'] . ')'), number_format($booking['total_fare'], 2), htmlspecialchars($booking['origin']), htmlspecialchars($booking['destination']), $passenger_rows_html, htmlspecialchars($booking['gateway_payment_id'] ?? 'N/A'), $view_ticket_url, $actual_departure_datetime->format('h:i A'), 'Est. ' . $actual_arrival_datetime->format('h:i A')];
                $email_body = str_replace($placeholders, $replacements, $email_body);
                $mail->Body = $email_body;
            } else {
                $mail->Body = "Your booking is confirmed. Ticket No: " . htmlspecialchars($booking['ticket_no']);
            }
            $mail->send();
        }
    } catch (Exception $e) {
        error_log("Email could not be sent after payment for booking ID {$booking_id}. Mailer Error: {$mail->ErrorInfo}");
    }

    // Set the final successful response
    $response = ['success' => true, 'booking_id' => $booking_id,'ticket_no'  => $ticket_no,  'new_user' => $is_new_user_flag];
} catch (Throwable $e) {
    if (isset($_conn_db) && $_conn_db->inTransaction()) {
        $_conn_db->rollBack();
    }
    http_response_code(400);
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// === THE FIX IS HERE ===
// We echo the final $response variable, which is guaranteed to have the booking_id on success.
echo json_encode($response);
exit();
