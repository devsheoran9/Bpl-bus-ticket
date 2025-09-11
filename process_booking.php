<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'config.php';
require "./admin/function/_db.php";

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // --- Data Sanitization and Retrieval ---
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
    $bus_id = filter_input(INPUT_POST, 'bus_id', FILTER_VALIDATE_INT);
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_VALIDATE_EMAIL);
    $contact_mobile = trim($_POST['contact_mobile'] ?? '');
    $total_fare = filter_input(INPUT_POST, 'total_fare', FILTER_VALIDATE_FLOAT);
    $passengers = json_decode($_POST['passengers'] ?? '[]', true);
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '::1';
    $travel_date = $_POST['travel_date'] ?? null;

    if (!$route_id || !$bus_id || empty($origin) || empty($destination) || empty($contact_name) || !$contact_email || empty($contact_mobile) || $total_fare === false || !$travel_date || empty($passengers)) {
        throw new Exception("Incomplete booking data. Please fill all required fields.");
    }

    $_conn_db->beginTransaction();

    $new_user_created = false;
    $plain_text_password = '';

    if (!$user_id) {
        $stmt_find_user = $_conn_db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_find_user->execute([$contact_email]);
        $existing_user = $stmt_find_user->fetch();

        if ($existing_user) {
            $user_id = $existing_user['id'];
        } else {
            $plain_text_password = $contact_mobile;
            $password_hash = password_hash($plain_text_password, PASSWORD_DEFAULT);
            $stmt_create_user = $_conn_db->prepare("INSERT INTO users (username, password, mobile_no, email, ip_address, status, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
            $stmt_create_user->execute([$contact_name, $password_hash, $contact_mobile, $contact_email, $ip_address]);
            $user_id = $_conn_db->lastInsertId();
            $new_user_created = true;
        }
    }

    // --- SEAT AVAILABILITY CHECK (FIXED) ---
    $seat_codes = array_column($passengers, 'seat_code');
    $placeholders = implode(',', array_fill(0, count($seat_codes), '?'));

    // --- MODIFIED: Added route_id to the query to make the check specific to this journey ---
    $check_sql = "SELECT p.seat_code 
                  FROM passengers p 
                  JOIN bookings b ON p.booking_id = b.booking_id 
                  WHERE b.route_id = ? AND b.bus_id = ? AND b.travel_date = ? AND b.booking_status = 'CONFIRMED' AND p.seat_code IN ($placeholders)";

    $stmt_check = $_conn_db->prepare($check_sql);

    // --- MODIFIED: Added $route_id to the parameters being executed ---
    $stmt_check->execute(array_merge([$route_id, $bus_id, $travel_date], $seat_codes));

    if ($stmt_check->rowCount() > 0) {
        $booked_seat = $stmt_check->fetchColumn();
        $_conn_db->rollBack();
        throw new Exception("Sorry, seat {$booked_seat} is no longer available for this route.");
    }

    // --- Insert into `bookings` table ---
    // The rest of the logic proceeds as normal
    $ticket_no = 'BPL' . substr(str_shuffle(str_repeat('0123456789', 9)), 0, 9);
    $booking_sql = "INSERT INTO bookings (ticket_no, route_id, bus_id, user_id, origin, destination, contact_name, contact_email, contact_mobile, travel_date, total_fare, payment_status, booking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PAID', 'CONFIRMED', NOW())";
    $stmt_booking = $_conn_db->prepare($booking_sql);
    $stmt_booking->execute([$ticket_no, $route_id, $bus_id, $user_id, $origin, $destination, $contact_name, $contact_email, $contact_mobile, $travel_date, $total_fare]);
    $booking_id = $_conn_db->lastInsertId();

    // --- Insert into `passengers` table ---
    $passenger_sql = "INSERT INTO passengers (booking_id, seat_id, seat_code, passenger_name, passenger_age, passenger_gender, fare) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_passenger = $_conn_db->prepare($passenger_sql);
    $get_seat_id_sql = "SELECT seat_id, base_price FROM seats WHERE bus_id = ? AND seat_code = ?";
    $stmt_get_seat_id = $_conn_db->prepare($get_seat_id_sql);

    foreach ($passengers as $p) {
        $stmt_get_seat_id->execute([$bus_id, $p['seat_code']]);
        $seat_info = $stmt_get_seat_id->fetch(PDO::FETCH_ASSOC);
        if (!$seat_info) throw new Exception("Invalid seat code: {$p['seat_code']}");
        $stmt_passenger->execute([$booking_id, $seat_info['seat_id'], $p['seat_code'], $p['name'], $p['age'], $p['gender'], $seat_info['base_price']]);
    }

    $_conn_db->commit();

    // --- Email Sending Logic ---
    try {
        $stmt_bus = $_conn_db->prepare("SELECT bus_name, registration_number FROM buses WHERE bus_id = ?");
        $stmt_bus->execute([$bus_id]);
        $bus_info = $stmt_bus->fetch(PDO::FETCH_ASSOC);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($contact_email, $contact_name);
        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmed! Your Ticket: ' . $ticket_no;

        $passenger_rows_html = '';
        foreach ($passengers as $p) {
            $passenger_rows_html .= "<tr><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars($p['name']) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars($p['age']) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6;'>" . htmlspecialchars(ucfirst(strtolower($p['gender']))) . "</td><td style='padding: 12px; border-bottom: 1px solid #dee2e6; font-weight: bold; text-align: right;'>" . htmlspecialchars($p['seat_code']) . "</td></tr>";
        }
        $account_info_html = '';
        if ($new_user_created) {
            $account_info_html = "<div style='background-color:#e6f7ff; border:1px solid #91d5ff; padding:15px; border-radius:8px; margin: 20px 0; text-align: center;'><h4 style='margin:0 0 10px 0;'>Welcome! An account has been created for you.</h4><p style='margin:0;'><strong>Username:</strong> " . htmlspecialchars($contact_email) . " | <strong>Password:</strong> " . htmlspecialchars($plain_text_password) . "</p><small>(This is your mobile number. You can change it after logging in.)</small></div>";
        }

        $email_body = file_get_contents('email_template.html');
        $email_body = str_replace('{{contact_name}}', htmlspecialchars($contact_name), $email_body);
        $email_body = str_replace('{{account_info}}', $account_info_html, $email_body);
        $email_body = str_replace('{{ticket_no}}', htmlspecialchars($ticket_no), $email_body);
        $email_body = str_replace('{{travel_date}}', date('D, d M Y', strtotime($travel_date)), $email_body);
        $email_body = str_replace('{{bus_details}}', htmlspecialchars($bus_info['bus_name'] . ' (' . $bus_info['registration_number'] . ')'), $email_body);
        $email_body = str_replace('{{total_fare}}', number_format($total_fare, 2), $email_body);
        $email_body = str_replace('{{boarding_from}}', htmlspecialchars($origin), $email_body);
        $email_body = str_replace('{{dropping_at}}', htmlspecialchars($destination), $email_body);
        $email_body = str_replace('{{passenger_rows}}', $passenger_rows_html, $email_body);

        $mail->Body = $email_body;
        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    echo json_encode(['success' => true, 'message' => 'Booking successful!', 'booking_id' => $booking_id, 'new_user' => $new_user_created]);
} catch (Throwable $e) {
    if (isset($_conn_db) && $_conn_db->inTransaction()) {
        $_conn_db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
