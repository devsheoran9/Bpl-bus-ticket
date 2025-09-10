<?php
// Your header or another central file should handle session_start()
header('Content-Type: application/json');

try {
    // Use your global PDO database connection file
    require "./admin/function/_db.php";

    // Only allow POST requests
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
    $user_id = $_SESSION['user_id'] ?? null; // Assuming your session variable is user_id

    $travel_date_str = $_POST['travel_date'] ?? null;
    $d = DateTime::createFromFormat('Y-m-d', $travel_date_str);
    if (!$travel_date_str || !$d || $d->format('Y-m-d') !== $travel_date_str) {
        throw new Exception("Invalid or missing travel date. Please try again.");
    }
    $travel_date = $travel_date_str;

    // --- Server-Side Validation ---
    if (!$route_id || !$bus_id || empty($origin) || empty($destination) || empty($contact_name) || !$contact_email || empty($contact_mobile) || $total_fare === false) {
        throw new Exception("Incomplete booking data. Please fill all required fields.");
    }
    if (json_last_error() !== JSON_ERROR_NONE || empty($passengers) || !is_array($passengers)) {
        throw new Exception("Invalid or empty passenger data received.");
    }

    // --- Database Transaction ---
    $_conn_db->beginTransaction();

    // Final check for seat availability
    $seat_codes = array_column($passengers, 'seat_code');
    $placeholders = implode(',', array_fill(0, count($seat_codes), '?'));

    $check_sql = "
        SELECT p.seat_code FROM passengers p
        JOIN bookings b ON p.booking_id = b.booking_id
        WHERE b.bus_id = ? AND b.travel_date = ? AND b.booking_status = 'CONFIRMED' AND p.seat_code IN ($placeholders)
    ";

    $stmt_check = $_conn_db->prepare($check_sql);
    $params = array_merge([$bus_id, $travel_date], $seat_codes);
    $stmt_check->execute($params);

    if ($stmt_check->rowCount() > 0) {
        $booked_seat = $stmt_check->fetchColumn();
        $_conn_db->rollBack();
        throw new Exception("Sorry, seat {$booked_seat} is no longer available.");
    }

    // Insert into `bookings` table
    $ticket_no = 'BPL' . substr(str_shuffle(str_repeat('0123456789', 9)), 0, 9);
    $payment_status = 'PENDING';
    $booking_status = 'CONFIRMED';

    $booking_sql = "
        INSERT INTO bookings (ticket_no, route_id, bus_id, user_id, origin, destination, contact_name, contact_email, contact_mobile, travel_date, total_fare, payment_status, booking_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    $stmt_booking = $_conn_db->prepare($booking_sql);
    $stmt_booking->execute([
        $ticket_no,
        $route_id,
        $bus_id,
        $user_id,
        $origin,
        $destination,
        $contact_name,
        $contact_email,
        $contact_mobile,
        $travel_date,
        $total_fare,
        $payment_status,
        $booking_status
    ]);

    $booking_id = $_conn_db->lastInsertId();

    // Prepare statements for passenger insertion and seat_id lookup
    $passenger_sql = "
        INSERT INTO passengers (booking_id, seat_id, seat_code, passenger_name, passenger_age, passenger_gender, passenger_mobile, fare)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt_passenger = $_conn_db->prepare($passenger_sql);

    // --- NEW: Prepare statement to get seat_id from seat_code ---
    $get_seat_id_sql = "SELECT seat_id FROM seats WHERE bus_id = ? AND seat_code = ?";
    $stmt_get_seat_id = $_conn_db->prepare($get_seat_id_sql);


    foreach ($passengers as $p) {
        // Sanitize passenger data received from JSON
        $seat_code = trim($p['seat_code'] ?? '');
        $name = trim($p['name'] ?? '');
        $age = filter_var($p['age'] ?? null, FILTER_VALIDATE_INT);
        $gender = strtoupper(trim($p['gender'] ?? ''));
        $fare = filter_var($p['fare'] ?? null, FILTER_VALIDATE_FLOAT);

        // --- MODIFIED: Fetch the seat_id from the database ---
        $stmt_get_seat_id->execute([$bus_id, $seat_code]);
        $fetched_seat_id = $stmt_get_seat_id->fetchColumn(); // Fetches the single value (seat_id)

        // --- MODIFIED: Updated validation check ---
        if (!$fetched_seat_id || empty($seat_code) || empty($name) || !$age || $age <= 0 || !in_array($gender, ['MALE', 'FEMALE', 'OTHER']) || $fare === false) {
            // This error will now trigger for more accurate reasons (e.g., empty name, invalid age)
            throw new Exception("Invalid details for passenger in seat {$seat_code}. Please check all fields.");
        }

        // --- MODIFIED: Use the fetched_seat_id for insertion ---
        $stmt_passenger->execute([
            $booking_id,
            $fetched_seat_id, // Use the ID we found in the database
            $seat_code,
            $name,
            $age,
            $gender,
            $contact_mobile,
            $fare
        ]);
    }

    // If everything was successful, commit the transaction
    $_conn_db->commit();

    echo json_encode(['success' => true, 'message' => 'Booking successful!', 'booking_id' => $booking_id]);
} catch (Throwable $e) {
    // If a transaction is active, roll it back
    if (isset($_conn_db) && $_conn_db->inTransaction()) {
        $_conn_db->rollBack();
    }
    // Use a 400 Bad Request code for validation errors, 500 for true server errors
    $errorCode = ($e instanceof Exception) ? 400 : 500;
    http_response_code($errorCode);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
