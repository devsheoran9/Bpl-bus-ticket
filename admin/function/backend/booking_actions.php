<?php
// function/backend/booking_actions.php
header('Content-Type: application/json');
include_once('../_db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function send_json_response($status, $data = []) {
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

// ACTION: Get stops for a route
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
    } catch (PDOException $e) { send_json_response('error', 'Database error fetching stops.'); }
}

// ACTION: Get seat layout and prices
if ($action == 'get_seat_layout') {
    $route_id = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
    $travel_date = $_GET['travel_date'] ?? '';
    $from_stop_name = $_GET['from_stop_name'] ?? '';
    $to_stop_name = $_GET['to_stop_name'] ?? '';
    if (!$route_id || empty($travel_date) || empty($from_stop_name) || empty($to_stop_name)) { send_json_response('error', 'Incomplete journey details.'); }

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
        
        $price_from = null; $price_to = null;
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
        
        // FIX: Added route_id to the query to check for booked seats
        $stmt_booked = $_conn_db->prepare("SELECT seat_id FROM booked_seats WHERE route_id = ? AND bus_id = ? AND travel_date = ?");
        $stmt_booked->execute([$route_id, $bus_id, $travel_date]);
        $booked_seat_ids = $stmt_booked->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($seats as &$seat) {
            $seat_price_key = $seat['seat_type'] . '_' . $seat['deck'];
            $seat['price'] = max(0, $final_prices[$seat_price_key] ?? 0);
            $seat['is_booked'] = in_array($seat['seat_id'], $booked_seat_ids);
        }
        send_json_response('success', ['bus_id' => $bus_id, 'seats' => $seats]);
    } catch (PDOException $e) { send_json_response('error', 'Database Error occurred.'); }
}

// Shared function to create a booking entry
function createBookingEntry($data, $isCash = false) {
    global $_conn_db;
    $booking_status = $isCash ? 'CONFIRMED' : 'PENDING';    

    $_conn_db->beginTransaction();
    try {
        // Generate a unique ticket number
        $ticket_no = 'BPL' . substr(str_shuffle(str_repeat('0123456789', 9)), 0, 9);

        // Added ticket_no, origin, and destination to the INSERT statement
        $sql_booking = "INSERT INTO bookings (ticket_no, route_id, bus_id, origin, destination, booked_by_employee_id, contact_email, contact_mobile, travel_date, total_fare, booking_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_booking = $_conn_db->prepare($sql_booking);
        $stmt_booking->execute([$ticket_no, $data['route_id'], $data['bus_id'], $data['origin'], $data['destination'], $data['employee_id'], $data['contact_email'], $data['contact_mobile'], $data['travel_date'], $data['total_fare'], $booking_status]);
        
        $booking_id = $_conn_db->lastInsertId();

        $sql_passenger = "INSERT INTO passengers (booking_id, seat_id, seat_code, passenger_name, passenger_mobile, passenger_age, passenger_gender, fare) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_passenger = $_conn_db->prepare($sql_passenger);
        
        // Added route_id to the INSERT statement for booked_seats
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
            'ticket_no' => $ticket_no, // Return the generated ticket number
            'wtsp_no' => $data['contact_mobile'],
            'mail' => $data['contact_email']
        ];
    } catch (PDOException $e) {
        $_conn_db->rollBack();
        if ($e->getCode() == '23000') {
            return ['status' => 'error', 'message' => 'Booking failed: A unique constraint was violated (e.g., seat already booked or duplicate ticket number). Please try again.'];
        }
        error_log("Create Booking Error: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Database Error: Could not create booking. Please check server logs.'];
    }
}

// ACTION: Handle booking requests
if ($action == 'confirm_cash_booking' || $action == 'create_pending_booking') {
    if (!isset($_SESSION['user']['id'])) {
        send_json_response('error', 'Authentication error. Please log in again.');
    }
    
    // Added origin and destination to the data array
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
    
    if (empty($data['route_id']) || empty($data['bus_id']) || empty($data['passengers']) || empty($data['origin']) || empty($data['destination'])) {
        send_json_response('error', 'Incomplete booking data received. Origin or destination might be missing.');
    }
    
    $isCash = ($action == 'confirm_cash_booking');
    $result = createBookingEntry($data, $isCash);

    send_json_response($result['status'], $result);
}
?>