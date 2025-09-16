<?php
header('Content-Type: application/json');
require './admin/vendor/autoload.php';
require 'config.php';
require "./admin/function/_db.php";

use Razorpay\Api\Api;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    // --- Data Sanitization and Retrieval ---
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
    $bus_id = filter_input(INPUT_POST, 'bus_id', FILTER_VALIDATE_INT);
    $schedule_id = filter_input(INPUT_POST, 'schedule_id', FILTER_VALIDATE_INT);
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_VALIDATE_EMAIL);
    $contact_mobile = trim($_POST['contact_mobile'] ?? '');
    $total_fare = filter_input(INPUT_POST, 'total_fare', FILTER_VALIDATE_FLOAT);
    $passengers_json = $_POST['passengers'] ?? '[]';
    $passengers = json_decode($passengers_json, true);
    $user_id = $_SESSION['user_id'] ?? null;
    $travel_date = $_POST['travel_date'] ?? null;

    // --- Server-Side Validation ---
    if (!$route_id || !$bus_id || !$schedule_id || empty($origin) || empty($destination) || empty($contact_name) || !$contact_email || empty($contact_mobile) || $total_fare === false || !$travel_date || empty($passengers)) {
        throw new Exception("Please fill all required fields before proceeding.");
    }
    if ($total_fare <= 0) {
        throw new Exception("Booking amount must be greater than zero.");
    }

    $new_user_created = false;

    // --- FIXED: GUEST & EXISTING USER LOGIC ---
    // This logic runs only if the user is not logged in.
    if (!$user_id) {
        // Priority 1: Check if an account already exists with the provided email.
        $stmt_find_user = $_conn_db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_find_user->execute([$contact_email]);
        $existing_user = $stmt_find_user->fetch();

        if ($existing_user) {
            // USER FOUND BY EMAIL: Use their user_id for the booking.
            // The mobile number provided will be used for this booking's contact info,
            // but the user's account mobile remains unchanged.
            $user_id = $existing_user['id'];
            $new_user_created = false;
        } else {
            // USER NOT FOUND BY EMAIL: Create a new account for the guest.
            $password_hash = password_hash($contact_mobile, PASSWORD_DEFAULT); // Use mobile as a temporary password
            $stmt_create_user = $_conn_db->prepare(
                "INSERT INTO users (username, password, mobile_no, email, ip_address, status, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())"
            );
            $stmt_create_user->execute([$contact_name, $password_hash, $contact_mobile, $contact_email, $_SERVER['REMOTE_ADDR']]);
            $user_id = $_conn_db->lastInsertId();
            $new_user_created = true;
        }
    }

    // --- STORE BOOKING DATA IN SESSION ---
    $temp_booking_data = [
        'route_id' => $route_id,
        'bus_id' => $bus_id,
        'schedule_id' => $schedule_id,
        'user_id' => $user_id, // This now contains the correct ID for all cases
        'origin' => $origin,
        'destination' => $destination,
        'contact_name' => $contact_name,
        'contact_email' => $contact_email,
        'contact_mobile' => $contact_mobile,
        'travel_date' => $travel_date,
        'total_fare' => $total_fare,
        'passengers' => $passengers,
        'new_user' => $new_user_created
    ];

    // Create a Razorpay Order
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    $orderData = [
        'amount'          => $total_fare * 100,
        'currency'        => 'INR',
        'notes'           => ['info' => 'Bus Ticket Booking']
    ];
    $razorpayOrder = $api->order->create($orderData);
    $razorpayOrderId = $razorpayOrder['id'];

    // Store the booking data in the session, keyed by the unique Razorpay Order ID
    $_SESSION['pending_booking_' . $razorpayOrderId] = $temp_booking_data;

    // Send a successful JSON response to the frontend to initiate payment
    echo json_encode([
        'success'           => true,
        'razorpay_order_id' => $razorpayOrderId,
        'razorpay_key_id'   => RAZORPAY_KEY_ID,
        'amount'            => $total_fare * 100,
        'contact_name'      => $contact_name,
        'contact_email'     => $contact_email,
        'contact_mobile'    => $contact_mobile,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
