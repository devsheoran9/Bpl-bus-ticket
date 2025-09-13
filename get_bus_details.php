<?php 
header('Content-Type: application/json');
include 'db_connect.php';

$schedule_id = $_GET['schedule_id'] ?? null;
$journey_date = $_GET['date'] ?? date('Y-m-d');

if (!$schedule_id) {
    echo json_encode(['success' => false, 'message' => 'Schedule ID is required.']);
    exit;
}

$response = ['success' => true];

try {
    $stmt = $_conn_db->prepare("
        SELECT r.bus_id, b.bus_name
        FROM route_schedules rsch
        JOIN routes r ON rsch.route_id = r.route_id
        JOIN buses b ON r.bus_id = b.bus_id
        WHERE rsch.schedule_id = :schedule_id
    ");
    $stmt->bindParam(':schedule_id', $schedule_id, PDO::PARAM_INT);
    $stmt->execute();
    $bus_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bus_info) {
        throw new Exception("Bus not found.");
    }

    $bus_id = $bus_info['bus_id'];
    $response['bus_name'] = $bus_info['bus_name'];

    // Fetch Images
    $stmt_images = $_conn_db->prepare("SELECT image_path FROM bus_bus_images WHERE bus_id = :bus_id");
    $stmt_images->bindParam(':bus_id', $bus_id, PDO::PARAM_INT);
    $stmt_images->execute();
    $response['images'] = $stmt_images->fetchAll(PDO::FETCH_COLUMN, 0);

    // Fetch Seat Layout
    $stmt_layout = $_conn_db->prepare("SELECT * FROM seats WHERE bus_id = :bus_id ORDER BY deck, y_coordinate, x_coordinate");
    $stmt_layout->bindParam(':bus_id', $bus_id, PDO::PARAM_INT);
    $stmt_layout->execute();
    $all_seats_layout = $stmt_layout->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Booked Seats
    $stmt_booked = $_conn_db->prepare("SELECT seat_code, passenger_gender FROM bookings WHERE schedule_id = :schedule_id AND journey_date = :journey_date");
    $stmt_booked->bindParam(':schedule_id', $schedule_id, PDO::PARAM_INT);
    $stmt_booked->bindParam(':journey_date', $journey_date);
    $stmt_booked->execute();
    $booked_seats_info = $stmt_booked->fetchAll(PDO::FETCH_KEY_PAIR);

    $lower_deck_seats = [];
    $upper_deck_seats = [];
    $lower_deck_height = 400;
    $upper_deck_height = 400;

    foreach ($all_seats_layout as $seat) {
        $is_booked = isset($booked_seats_info[$seat['seat_code']]);
        $seat['final_status'] = ($seat['status'] !== 'AVAILABLE' || $seat['is_bookable'] == 0 || $is_booked) ? 'SOLD' : 'AVAILABLE';
        $seat['booked_by_gender'] = $is_booked ? strtoupper($booked_seats_info[$seat['seat_code']]) : null;
        if (strtoupper($seat['deck']) === 'LOWER') {
            $lower_deck_seats[] = $seat;
            $lower_deck_height = max($lower_deck_height, $seat['y_coordinate'] + $seat['height']);
        } else {
            $upper_deck_seats[] = $seat;
            $upper_deck_height = max($upper_deck_height, $seat['y_coordinate'] + $seat['height']);
        }
    }

    $response['lower_deck_seats'] = $lower_deck_seats;
    $response['upper_deck_seats'] = $upper_deck_seats;
    $response['lower_deck_height'] = $lower_deck_height + 20;
    $response['upper_deck_height'] = $upper_deck_height + 20;
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
