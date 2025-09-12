<?php
header('Content-Type: application/json'); 
require   './admin/vendor/autoload.php';
require  'config.php';
require  "./admin/function/_db.php";

use Razorpay\Api\Api;

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
    $passengers_json = $_POST['passengers'] ?? '[]';
    $passengers = json_decode($passengers_json, true);
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '::1';
    $travel_date = $_POST['travel_date'] ?? null;

    // --- Server-Side Validation ---
    if (!$route_id || !$bus_id || empty($origin) || empty($destination) || empty($contact_name) || !$contact_email || empty($contact_mobile) || $total_fare === false || !$travel_date || empty($passengers)) {
        throw new Exception("Incomplete or invalid booking data provided.");
    }

    if ($total_fare <= 0) {
        throw new Exception("Total fare must be greater than zero to proceed with payment.");
    }

    // --- Start Database Transaction ---
    $_conn_db->beginTransaction();

    $new_user_created = false;
    $plain_text_password = '';

    // --- Handle Guest Users & Auto-Registration ---
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

    // --- Create a PENDING booking record ---
    $ticket_no = 'BPL' . substr(str_shuffle(str_repeat('0123456789', 9)), 0, 9);
    $booking_sql = "INSERT INTO bookings (ticket_no, route_id, bus_id, user_id, origin, destination, contact_name, contact_email, contact_mobile, travel_date, total_fare, payment_status, booking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', 'PENDING', NOW())";
    $stmt_booking = $_conn_db->prepare($booking_sql);
    $stmt_booking->execute([$ticket_no, $route_id, $bus_id, $user_id, $origin, $destination, $contact_name, $contact_email, $contact_mobile, $travel_date, $total_fare]);
    $booking_id = $_conn_db->lastInsertId();

    // --- Insert passengers associated with the pending booking ---
    $passenger_sql = "INSERT INTO passengers (booking_id, seat_id, seat_code, passenger_name, passenger_age, passenger_gender, fare) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_passenger = $_conn_db->prepare($passenger_sql);
    $get_seat_id_sql = "SELECT seat_id FROM seats WHERE bus_id = ? AND seat_code = ?";
    $stmt_get_seat_id = $_conn_db->prepare($get_seat_id_sql);

    foreach ($passengers as $p) {
        $seat_code = trim($p['seat_code'] ?? '');
        $name = trim($p['name'] ?? '');
        $age = filter_var($p['age'] ?? 0, FILTER_VALIDATE_INT);
        $gender = strtoupper(trim($p['gender'] ?? ''));
        $fare = filter_var($p['fare'] ?? 0, FILTER_VALIDATE_FLOAT);

        $stmt_get_seat_id->execute([$bus_id, $seat_code]);
        $fetched_seat_id = $stmt_get_seat_id->fetchColumn();

        if (!$fetched_seat_id) {
            throw new Exception("Invalid seat code provided in passenger data: {$seat_code}");
        }

        $stmt_passenger->execute([$booking_id, $fetched_seat_id, $seat_code, $name, $age, $gender, $fare]);
    }

    // --- Create a Razorpay Order ---
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    $orderData = [
        'receipt'         => (string)$booking_id,
        'amount'          => $total_fare * 100, // Amount in paise
        'currency'        => 'INR',
        'notes'           => ['booking_id' => (string)$booking_id]
    ];
    $razorpayOrder = $api->order->create($orderData);
    $razorpayOrderId = $razorpayOrder['id'];

    // --- Store the Razorpay Order ID in your booking table for reference ---
    $stmt_update_order = $_conn_db->prepare("UPDATE bookings SET gateway_order_id = ? WHERE booking_id = ?");
    $stmt_update_order->execute([$razorpayOrderId, $booking_id]);

    // If everything is successful, commit the transaction to the database
    $_conn_db->commit();

    // --- Send a successful JSON response back to the JavaScript ---
    echo json_encode([
        'success'           => true,
        'booking_id'        => $booking_id,
        'razorpay_order_id' => $razorpayOrderId,
        'razorpay_key_id'   => RAZORPAY_KEY_ID,
        'amount'            => $total_fare * 100,
        'contact_name'      => $contact_name,
        'contact_email'     => $contact_email,
        'contact_mobile'    => $contact_mobile,
        'new_user'          => $new_user_created
    ]);
} catch (Throwable $e) {
    // If any error occurred, roll back the entire transaction
    if (isset($_conn_db) && $_conn_db->inTransaction()) {
        $_conn_db->rollBack();
    }
    // Send a 400 Bad Request status code and a JSON error message
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
