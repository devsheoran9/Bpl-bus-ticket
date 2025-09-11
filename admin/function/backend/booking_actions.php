<?php 
header('Content-Type: application/json');

include_once('../_db.php'); 
require_once('../../vendor/autoload.php'); 
use Razorpay\Api\Api;

 

function send_json_response($status, $data = [])
{
    $response = ['status' => $status];
    if (is_string($data)) {
        $response['message'] = $data;
    } else {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ACTION: Get stops for a route (Existing code - No changes)
if ($action == 'get_stops_for_route') {
    $route_id = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
    if (!$route_id) send_json_response('error', 'Invalid Route ID.');
    try {
        $stmt_start = $_conn_db->prepare("SELECT starting_point FROM routes WHERE route_id = ?");
        $stmt_start->execute([$route_id]);
        $start_point = $stmt_start->fetchColumn();
        $stmt_stops = $_conn_db->prepare("SELECT stop_name FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
        $stmt_stops->execute([$route_id]);
        $intermediate_stops = $stmt_stops->fetchAll(PDO::FETCH_COLUMN);
        $all_stops = array_merge([$start_point], $intermediate_stops);
        send_json_response('success', ['stops' => $all_stops]);
    } catch (PDOException $e) {
        send_json_response('error', 'Database error fetching stops.');
    }
}

// ACTION: Get seat layout and prices (Existing code - No changes)
if ($action == 'get_seat_layout') {
    $route_id = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
    $travel_date = $_GET['travel_date'] ?? '';
    $from_stop_name = $_GET['from_stop_name'] ?? '';
    $to_stop_name = $_GET['to_stop_name'] ?? '';
    if (!$route_id || empty($travel_date) || empty($from_stop_name) || empty($to_stop_name)) {
        send_json_response('error', 'Incomplete journey details.');
    }

    $day_of_week = date('D', strtotime($travel_date));
    try {
        $stmt_schedule = $_conn_db->prepare("SELECT r.bus_id FROM route_schedules rs JOIN routes r ON rs.route_id=r.route_id WHERE rs.route_id = ? AND rs.operating_day = ?");
        $stmt_schedule->execute([$route_id, $day_of_week]);
        $schedule = $stmt_schedule->fetch(PDO::FETCH_ASSOC);
        if (!$schedule) send_json_response('error', "Sorry, the bus does not run on " . date('l', strtotime($travel_date)) . "s.");

        $bus_id = $schedule['bus_id'];
        $stmt_all_stops = $_conn_db->prepare("SELECT * FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
        $stmt_all_stops->execute([$route_id]);
        $all_stops_data = $stmt_all_stops->fetchAll(PDO::FETCH_ASSOC);

        $route_start_point_stmt = $_conn_db->prepare("SELECT starting_point FROM routes WHERE route_id=?");
        $route_start_point_stmt->execute([$route_id]);
        $route_start_point = $route_start_point_stmt->fetchColumn();

        $stops_with_prices = array_merge([['stop_name' => $route_start_point, 'price_seater_lower' => 0, 'price_seater_upper' => 0, 'price_sleeper_lower' => 0, 'price_sleeper_upper' => 0]], $all_stops_data);

        $price_from = null;
        $price_to = null;
        foreach ($stops_with_prices as $stop) {
            if ($stop['stop_name'] == $from_stop_name) $price_from = $stop;
            if ($stop['stop_name'] == $to_stop_name) $price_to = $stop;
        }
        if ($price_from === null || $price_to === null) send_json_response('error', 'Could not determine pricing for the selected stops.');

        $final_prices = [
            'SEATER_LOWER' => ($price_to['price_seater_lower'] ?? 0) - ($price_from['price_seater_lower'] ?? 0),
            'SEATER_UPPER' => ($price_to['price_seater_upper'] ?? 0) - ($price_from['price_seater_upper'] ?? 0),
            'SLEEPER_LOWER' => ($price_to['price_sleeper_lower'] ?? 0) - ($price_from['price_sleeper_lower'] ?? 0),
            'SLEEPER_UPPER' => ($price_to['price_sleeper_upper'] ?? 0) - ($price_from['price_sleeper_upper'] ?? 0)
        ];

        $stmt_seats = $_conn_db->prepare("SELECT seat_id,bus_id,seat_code,deck,seat_type,x_coordinate,y_coordinate,width,height,orientation,gender_preference,is_bookable FROM seats WHERE bus_id=?");
        $stmt_seats->execute([$bus_id]);
        $seats = $stmt_seats->fetchAll(PDO::FETCH_ASSOC);

        $stmt_booked = $_conn_db->prepare("SELECT seat_id FROM booked_seats WHERE route_id = ? AND bus_id = ? AND travel_date = ?");
        $stmt_booked->execute([$route_id, $bus_id, $travel_date]);
        $booked_seat_ids = $stmt_booked->fetchAll(PDO::FETCH_COLUMN);

        foreach ($seats as &$seat) {
            $seat_price_key = $seat['seat_type'] . '_' . $seat['deck'];
            $seat['price'] = max(0, $final_prices[$seat_price_key] ?? 0);
            $seat['is_booked'] = in_array($seat['seat_id'], $booked_seat_ids);
        }
        send_json_response('success', ['bus_id' => $bus_id, 'seats' => $seats]);
    } catch (PDOException $e) {
        send_json_response('error', 'Database Error occurred.');
    }
}

// Shared function to create a booking entry (Existing code - No changes)
function createBookingEntry($data, $isCash = false)
{
    global $_conn_db;
    $booking_status = $isCash ? 'CONFIRMED' : 'PENDING';

    $_conn_db->beginTransaction();
    try {
        $ticket_no = 'BPL' . substr(str_shuffle(str_repeat('0123456789', 9)), 0, 9);
        $sql_booking = "INSERT INTO bookings (ticket_no, route_id, bus_id, origin, destination, booked_by_employee_id, contact_email, contact_mobile, travel_date, total_fare, booking_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_booking = $_conn_db->prepare($sql_booking);
        $stmt_booking->execute([$ticket_no, $data['route_id'], $data['bus_id'], $data['origin'], $data['destination'], $data['employee_id'], $data['contact_email'], $data['contact_mobile'], $data['travel_date'], $data['total_fare'], $booking_status]);
        $booking_id = $_conn_db->lastInsertId();

        $sql_passenger = "INSERT INTO passengers (booking_id, seat_id, seat_code, passenger_name, passenger_mobile, passenger_age, passenger_gender, fare) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_passenger = $_conn_db->prepare($sql_passenger);

        $sql_booked_seat = "INSERT INTO booked_seats (booking_id, route_id, bus_id, seat_id, travel_date) VALUES (?, ?, ?, ?, ?)";
        $stmt_booked_seat = $_conn_db->prepare($sql_booked_seat);

        foreach ($data['passengers'] as $p) {
            $stmt_passenger->execute([$booking_id, $p['seat_id'], $p['seat_code'], $p['name'], $p['mobile'], $p['age'] ?? null, $p['gender'], $p['fare']]);
            $stmt_booked_seat->execute([$booking_id, $data['route_id'], $data['bus_id'], $p['seat_id'], $data['travel_date']]);
        }

        $_conn_db->commit();
        return [
            'status' => 'success',
            'booking_id' => $booking_id,
            'ticket_no' => $ticket_no,
            'wtsp_no' => $data['contact_mobile'],
            'mail' => $data['contact_email']
        ];
    } catch (PDOException $e) {
        $_conn_db->rollBack();
        if ($e->getCode() == '23000') {
            return ['status' => 'error', 'message' => 'Booking failed: A unique constraint was violated. Please try again.'];
        }
        error_log("Create Booking Error: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Database Error: Could not create booking.'];
    }
}

// ACTION: Handle booking requests (Existing code - No changes)
if ($action == 'confirm_cash_booking') {
    if (!isset($_SESSION['user']['id'])) send_json_response('error', 'Authentication error. Please log in again.');
    $data = [
        'route_id' => filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT),
        'bus_id' => filter_input(INPUT_POST, 'bus_id', FILTER_VALIDATE_INT),
        'travel_date' => $_POST['travel_date'],
        'total_fare' => filter_input(INPUT_POST, 'total_fare', FILTER_VALIDATE_FLOAT),
        'origin' => isset($_POST['origin']) ? htmlspecialchars($_POST['origin'], ENT_QUOTES, 'UTF-8') : null,
        'destination' => isset($_POST['destination']) ? htmlspecialchars($_POST['destination'], ENT_QUOTES, 'UTF-8') : null,
        'passengers' => json_decode($_POST['passengers'], true),
        'employee_id' => $_SESSION['user']['id'],
        'contact_email' => filter_var($_POST['contact_email'] ?? null, FILTER_VALIDATE_EMAIL),
        'contact_mobile' => isset($_POST['contact_mobile']) ? htmlspecialchars($_POST['contact_mobile'], ENT_QUOTES, 'UTF-8') : null
    ];
    if (empty($data['route_id']) || empty($data['bus_id']) || empty($data['passengers'])) send_json_response('error', 'Incomplete booking data received.');
    $isCash = ($action == 'confirm_cash_booking');
    $result = createBookingEntry($data, $isCash);
    send_json_response($result['status'], $result);
}
if ($action == 'create_pending_booking') {
    if (!isset($_SESSION['user']['id'])) send_json_response('error', 'Authentication error.');
    
    $total_fare = filter_input(INPUT_POST, 'total_fare', FILTER_VALIDATE_FLOAT);
    if (!$total_fare || $total_fare <= 0) { send_json_response('error', 'Invalid total fare.'); }

    $keyId = 'rzp_test_xISbqnYlqqrWvs'; // Replace with your actual Razorpay Key ID
    $keySecret = 'RxquG8pfP9f5inluawqEAw92'; // Replace with your actual Razorpay Key Secret
    $api = new Api($keyId, $keySecret);

    $orderData = [
        'receipt'         => 'rcpt_' . bin2hex(random_bytes(6)),
        'amount'          => $total_fare * 100,
        'currency'        => 'INR'
    ];
    $razorpayOrder = $api->order->create($orderData);
    if (!$razorpayOrder) { send_json_response('error', 'Could not create Razorpay order.'); }
    
    $razorpayOrderId = $razorpayOrder['id'];

    $data = [
        'route_id' => filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT),
        'bus_id' => filter_input(INPUT_POST, 'bus_id', FILTER_VALIDATE_INT),
        'travel_date' => $_POST['travel_date'],
        'origin' => $_POST['origin'],
        'destination' => $_POST['destination'],
        'total_fare' => $total_fare,
        'passengers' => json_decode($_POST['passengers'], true),
        'employee_id' => $_SESSION['user']['id'],
        'contact_email' => filter_var($_POST['contact_email'] ?? null, FILTER_SANITIZE_EMAIL),
        'contact_mobile' => isset($_POST['contact_mobile']) ? htmlspecialchars($_POST['contact_mobile']) : null
    ];
    
    $result = createBookingEntry($data, false, $razorpayOrderId);
    send_json_response($result['status'], $result);
}


// ===================================================================
// --- NEW ACTIONS FOR THE 'view_bookings.php' PAGE START HERE ---
// ===================================================================

// ACTION: Get all buses that run on a specific route
if ($action == 'get_buses_for_route') {
    $route_id = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
    if (!$route_id) {
        send_json_response('error', 'Invalid Route ID.');
    }
    try {
        // Find the bus associated with this route.
        $stmt = $_conn_db->prepare("
            SELECT b.bus_id, b.bus_name, b.registration_number 
            FROM buses b
            JOIN routes r ON b.bus_id = r.bus_id
            WHERE r.route_id = ?
        ");
        $stmt->execute([$route_id]);
        $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        send_json_response('success', ['buses' => $buses]);
    } catch (PDOException $e) {
        send_json_response('error', 'Database error: ' . $e->getMessage());
    }
}

if ($action == 'get_route_dashboard_details') {
    $route_id = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
    $travel_date = $_GET['travel_date'] ?? date('Y-m-d'); // Default to today if not provided

    if (!$route_id) {
        send_json_response('error', 'Please select a route.');
    }

    try {
        $response_data = [];

        // 1. Get Route, Bus, and Operator Details
        $details_stmt = $_conn_db->prepare("
            SELECT 
                r.route_name, r.starting_point, r.ending_point,
                b.bus_name, b.registration_number, b.bus_type,
                o.operator_name, o.contact_phone AS operator_phone
            FROM routes r
            JOIN buses b ON r.bus_id = b.bus_id
            JOIN operators o ON b.operator_id = o.operator_id
            WHERE r.route_id = ?
        ");
        $details_stmt->execute([$route_id]);
        $response_data['details'] = $details_stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Get Full Route Timeline (All Stops)
        $stops_stmt = $_conn_db->prepare("SELECT stop_name FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC");
        $stops_stmt->execute([$route_id]);
        $intermediate_stops = $stops_stmt->fetchAll(PDO::FETCH_COLUMN);
        $response_data['timeline'] = array_merge([$response_data['details']['starting_point']], $intermediate_stops);

        // 3. Get Bookings for the selected date
        $booking_sql = "
            SELECT booking_id, ticket_no, total_fare, created_at
            FROM bookings
            WHERE route_id = ? AND travel_date = ?
            ORDER BY created_at DESC
        ";
        $booking_stmt = $_conn_db->prepare($booking_sql);
        $booking_stmt->execute([$route_id, $travel_date]);
        $bookings = $booking_stmt->fetchAll(PDO::FETCH_ASSOC);

        // For each booking, fetch its passengers
        $passenger_stmt = $_conn_db->prepare("SELECT passenger_name, seat_code FROM passengers WHERE booking_id = ?");
        foreach ($bookings as &$booking) {
            $passenger_stmt->execute([$booking['booking_id']]);
            $passengers = $passenger_stmt->fetchAll(PDO::FETCH_ASSOC);
            $booking['passenger_names'] = implode(', ', array_column($passengers, 'passenger_name'));
            $booking['seat_codes'] = implode(', ', array_column($passengers, 'seat_code'));
        }
        $response_data['bookings'] = $bookings;

        send_json_response('success', $response_data);
    } catch (PDOException $e) {
        send_json_response('error', 'Database error: ' . $e->getMessage());
    }
}
if ($action == 'delete_booking') {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

    if (!$booking_id) {
        send_json_response('error', 'Invalid Booking ID provided.');
    }

    // Use a transaction to ensure all related data is deleted or none at all
    $_conn_db->beginTransaction();
    try {
        // We delete from multiple tables, so order matters if there are foreign keys.
        // It's safest to delete from child tables first.

        // 1. Delete from passengers
        $stmt1 = $_conn_db->prepare("DELETE FROM passengers WHERE booking_id = ?");
        $stmt1->execute([$booking_id]);

        // 2. Delete from booked_seats
        $stmt2 = $_conn_db->prepare("DELETE FROM booked_seats WHERE booking_id = ?");
        $stmt2->execute([$booking_id]);

        // 3. Delete from transactions (if any)
        $stmt3 = $_conn_db->prepare("DELETE FROM transactions WHERE booking_id = ?");
        $stmt3->execute([$booking_id]);

        // 4. Finally, delete the main booking record
        $stmt4 = $_conn_db->prepare("DELETE FROM bookings WHERE booking_id = ?");
        $stmt4->execute([$booking_id]);

        // If all deletions were successful, commit the transaction
        $_conn_db->commit();
        send_json_response('success', 'Booking has been successfully deleted.');
    } catch (PDOException $e) {
        // If any error occurs, roll back the transaction
        $_conn_db->rollBack();
        error_log("Delete Booking Error: " . $e->getMessage());
        send_json_response('error', 'Database error: Could not delete the booking.');
    }
}


// --- ACTION: Mark a cash booking as collected by inserting into a log table ---
if ($action == 'mark_cash_collected') {
    if (!isset($_SESSION['user']['type']) || $_SESSION['user']['type'] !== 'main_admin') {
        send_json_response('error', 'Access Denied.');
    }

    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    if (!$booking_id) {
        send_json_response('error', 'Invalid Booking ID.');
    }

    try {
        // First, get the booking details to log them correctly
        $stmt_booking = $_conn_db->prepare("SELECT total_fare, booked_by_employee_id FROM bookings WHERE booking_id = ?");
        $stmt_booking->execute([$booking_id]);
        $booking = $stmt_booking->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            send_json_response('error', 'Booking not found.');
        }

        $amount = $booking['total_fare'];
        $employee_id = $booking['booked_by_employee_id'];
        $admin_id = $_SESSION['user']['id']; // The main admin who is clicking the button

        // Insert a record into the new log table
        $stmt = $_conn_db->prepare(
            "INSERT INTO cash_collections_log (booking_id, amount_collected, collected_by_admin_id, collected_from_employee_id) VALUES (?, ?, ?, ?)"
        );

        if ($stmt->execute([$booking_id, $amount, $admin_id, $employee_id])) {
            send_json_response('success', 'Cash collection has been successfully logged.');
        } else {
            send_json_response('error', 'Failed to log the collection in the database.');
        }
    } catch (PDOException $e) {
        // Handle unique constraint violation (if already collected)
        if ($e->getCode() == '23000') {
            send_json_response('error', 'This booking has already been marked as collected.');
        }
        error_log("Mark Cash Collected Error: " . $e->getMessage());
        send_json_response('error', 'A database error occurred.');
    }
}
