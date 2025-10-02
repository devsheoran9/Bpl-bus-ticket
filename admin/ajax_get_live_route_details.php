<?php
// ajax_get_live_route_details.php (FIXED to handle NULL stop names)

global $_conn_db;
include_once('function/_db.php');
session_security_check(); 

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (isset($_POST['route_id']) && is_numeric($_POST['route_id'])) {
    $route_id = (int)$_POST['route_id'];
    $current_day_short = date('D'); 
    $today_date = date('Y-m-d');
    $current_time = new DateTime();

    try {
        // Step 1: Get route, departure, and bus details
        $stmt_route = $_conn_db->prepare(
            "SELECT 
                 r.route_name, r.starting_point, r.ending_point, 
                 rs.departure_time,
                 b.bus_id, b.bus_name, b.registration_number
             FROM routes r
             JOIN route_schedules rs ON r.route_id = rs.route_id
             JOIN buses b ON r.bus_id = b.bus_id
             WHERE r.route_id = ? AND rs.operating_day = ?"
        );
        $stmt_route->execute([$route_id, $current_day_short]);
        $route_info = $stmt_route->fetch(PDO::FETCH_ASSOC);

        if (!$route_info) {
            throw new Exception("This route is not scheduled to run today.");
        }
        $bus_id = $route_info['bus_id'];

        // Step 2: Get assigned staff
        $stmt_staff = $_conn_db->prepare(
            "SELECT s.name, rsa.role 
             FROM route_staff_assignments rsa JOIN staff s ON rsa.staff_id = s.staff_id
             WHERE rsa.route_id = ? ORDER BY FIELD(rsa.role, 'Driver', 'Co-Driver', 'Conductor', 'Helper')"
        );
        $stmt_staff->execute([$route_id]);
        $staff_list = $stmt_staff->fetchAll(PDO::FETCH_ASSOC);

        // Step 3: Calculate Live Journey Stats
        $stmt_stats = $_conn_db->prepare(
            "SELECT
                COALESCE(COUNT(p.passenger_id), 0) AS booked_seats,
                COALESCE(SUM(b.total_fare), 0) AS total_income,
                (SELECT COUNT(s.seat_id) FROM seats s WHERE s.bus_id = ? AND s.is_bookable = 1) as total_seats
             FROM bookings b
             JOIN passengers p ON b.booking_id = p.booking_id
             WHERE b.route_id = ? AND b.travel_date = ? AND p.passenger_status = 'CONFIRMED'
            "
        );
        $stmt_stats->execute([$bus_id, $route_id, $today_date]);
        $live_stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
        $live_stats['seats_left'] = ($live_stats['total_seats'] ?? 0) - ($live_stats['booked_seats'] ?? 0);

        // Step 4: Check if the route is chartered
        $stmt_charter = $_conn_db->prepare("SELECT COUNT(*) FROM charter_bookings WHERE route_id = ? AND travel_date = ?");
        $stmt_charter->execute([$route_id, $today_date]);
        $is_chartered = $stmt_charter->fetchColumn() > 0;

        // Step 5: Get stops for timeline calculation
        $stmt_stops = $_conn_db->prepare(
            "SELECT stop_name, duration_from_start_minutes FROM route_stops WHERE route_id = ? ORDER BY stop_order ASC"
        );
        $stmt_stops->execute([$route_id]);
        $stops = $stmt_stops->fetchAll(PDO::FETCH_ASSOC);

        // Step 6: Build the timeline array
        $departure_time = new DateTime($today_date . ' ' . $route_info['departure_time']);
        $timeline = [];
        $found_current_segment = false;
        
        $timeline[] = ['name' => $route_info['starting_point'], 'time' => 'Departed at ' . $departure_time->format('h:i A'), 'status' => ($current_time >= $departure_time) ? 'completed' : 'upcoming', 'type' => 'start'];
        
        foreach ($stops as $stop) {
            // --- THIS IS THE FIX ---
            // Only add the stop to the timeline if its name is not empty
            if (!empty($stop['stop_name'])) {
                $stop_arrival_time = (clone $departure_time)->modify('+' . $stop['duration_from_start_minutes'] . ' minutes');
                $status = ($current_time >= $stop_arrival_time) ? 'completed' : 'upcoming';
                
                if (end($timeline)['status'] === 'completed' && $status === 'upcoming' && !$found_current_segment) {
                    $timeline[count($timeline) - 1]['status'] = 'current_segment';
                    $found_current_segment = true;
                }
                
                $timeline[] = [
                    'name'   => $stop['stop_name'], // Now we are sure this key exists and is not empty
                    'time'   => 'Approx. Arrival: ' . $stop_arrival_time->format('h:i A'),
                    'status' => $status,
                    'type'   => 'stop'
                ];
            }
        }
        
        $last_stop_duration = !empty($stops) ? end($stops)['duration_from_start_minutes'] : 0;
        $final_arrival_time = (clone $departure_time)->modify('+' . ($last_stop_duration + 30) . ' minutes');
        $final_status = ($current_time >= $final_arrival_time) ? 'completed' : 'upcoming';
        $timeline[] = ['name' => $route_info['ending_point'], 'time' => 'Final Destination', 'status' => $final_status, 'type' => 'end'];
        
        if (!$found_current_segment && end($timeline)['status'] === 'upcoming' && prev($timeline)['status'] === 'completed') {
             $timeline[count($timeline) - 2]['status'] = 'current_segment';
        }

        // Step 7: Prepare the final, successful response
        $response = [
            'success'      => true,
            'routeName'    => $route_info['route_name'],
            'isChartered'  => $is_chartered,
            'busDetails'   => ['name' => $route_info['bus_name'], 'reg_no' => $route_info['registration_number']],
            'staff'        => $staff_list,
            'stats'        => $live_stats,
            'timeline'     => $timeline
        ];

    } catch (Exception $e) {
        $response['message'] = 'Error fetching route details: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request. Route ID not provided.';
}

header('Content-Type: application/json');
echo json_encode($response);