<?php
/**
 * Backend AJAX handler for all route-related actions.
 * - Handles saving/updating routes, schedules, stops, and staff assignments.
 * - Handles route deletion.
 * - Handles toggling the 'popular' status.
 * - All database modifications are wrapped in transactions for data integrity.
 */

// Set header to return JSON, as this is an AJAX endpoint.
header('Content-Type: application/json');

// Include the database connection and core functions.
// We go up one directory ('../') to find the 'function' folder's root.
include_once('../_db.php');

/**
 * Sends a standardized JSON response and terminates the script.
 * @param string $res         - 'true' for success, 'false' for failure.
 * @param string $notif_type  - 'success', 'danger', 'warning', 'info'.
 * @param string $notif_title - The title for the notification.
 * @param string $notif_desc  - The main message for the notification.
 * @param string $goTo        - (Optional) A URL to redirect to upon success.
 */
function send_response($res, $notif_type, $notif_title, $notif_desc, $goTo = '') {
    echo json_encode([
        'res' => $res,
        'notif_type' => $notif_type,
        'notif_title' => $notif_title,
        'notif_desc' => $notif_desc,
        'goTo' => $goTo
    ]);
    exit(); // Stop script execution after sending the response.
}

// --- SECURITY: Only allow POST requests ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response('false', 'danger', 'Error', 'Invalid request method. Only POST is accepted.');
}

// --- ROUTER: Determine which action to perform ---
$action = $_POST['action'] ?? '';

// ===================================================
//  ACTION: DELETE ROUTE
// ===================================================
if ($action == 'delete_route') {
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
    if (!$route_id) {
        send_response('false', 'warning', 'Invalid Input', 'A valid Route ID was not provided.');
    }
    
    try {
        // Because of cascading deletes in the database schema, deleting a route
        // will automatically delete its related schedules, stops, and staff assignments.
        $stmt = $_conn_db->prepare("DELETE FROM routes WHERE route_id = ?");
        $stmt->execute([$route_id]);
        send_response('true', 'success', 'Success', 'The route and all its related data have been deleted.', 'view_routes.php');
    } catch (PDOException $e) {
        // Log the detailed error for the admin, but show a generic message to the user.
        error_log("Route Deletion Failed: " . $e->getMessage());
        send_response('false', 'danger', 'Database Error', 'Could not delete the route. It might be in use.');
    }
}

// ===================================================
//  ACTION: ADD OR UPDATE ROUTE (with staff)
// ===================================================
if ($action == 'save_route') {
    // 1. Get and sanitize all form data
    $bus_id = filter_input(INPUT_POST, 'bus_id', FILTER_VALIDATE_INT);
    $route_name = trim($_POST['route_name']);
    $starting_point = trim($_POST['starting_point']);
    $status = $_POST['status'];
    $action_type = $_POST['action_type'];
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);

    // Get array data (use null coalescing operator for safety)
    $operating_days_array = $_POST['operating_days'] ?? [];
    $departure_times_specific = $_POST['departure_time'] ?? [];
    $stop_names = $_POST['stop_name'] ?? [];
    $durations = $_POST['duration'] ?? [];
    $prices_sl = $_POST['price_sl'] ?? []; $prices_su = $_POST['price_su'] ?? [];
    $prices_ll = $_POST['price_ll'] ?? []; $prices_lu = $_POST['price_lu'] ?? [];

    // Get staff assignment data
    $driver_id = filter_input(INPUT_POST, 'staff_driver', FILTER_VALIDATE_INT);
    $co_driver_id = filter_input(INPUT_POST, 'staff_co_driver', FILTER_VALIDATE_INT);
    $conductor_id = filter_input(INPUT_POST, 'staff_conductor', FILTER_VALIDATE_INT);
    $co_conductor_id = filter_input(INPUT_POST, 'staff_co_conductor', FILTER_VALIDATE_INT);
    $helper_ids = $_POST['staff_helpers'] ?? [];

    // Calculate the ending point based on the last stop
    $ending_point = !empty($stop_names) ? end($stop_names) : $starting_point;

    // 2. Begin database transaction for data integrity
    $_conn_db->beginTransaction();
    try {
        // --- Part A: Insert or Update the main 'routes' table ---
        if ($action_type == 'update' && $route_id) {
            // For an update, modify the existing route record.
            $sql = "UPDATE routes SET bus_id = ?, route_name = ?, starting_point = ?, ending_point = ?, status = ? WHERE route_id = ?";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([$bus_id, $route_name, $starting_point, $ending_point, $status, $route_id]);
            
            // Clean up all old related data. This is simpler and more reliable than comparing changes.
            $_conn_db->prepare("DELETE FROM route_schedules WHERE route_id = ?")->execute([$route_id]);
            $_conn_db->prepare("DELETE FROM route_stops WHERE route_id = ?")->execute([$route_id]);
            $_conn_db->prepare("DELETE FROM route_staff_assignments WHERE route_id = ?")->execute([$route_id]);
            
            $current_route_id = $route_id;
        } else {
            // For a new route, insert a new record.
            $sql = "INSERT INTO routes (bus_id, route_name, starting_point, ending_point, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([$bus_id, $route_name, $starting_point, $ending_point, $status]);
            $current_route_id = $_conn_db->lastInsertId();
        }

        // --- Part B: Insert the 'route_schedules' ---
        if (!empty($operating_days_array)) {
            $sql_schedule = "INSERT INTO route_schedules (route_id, operating_day, departure_time) VALUES (?, ?, ?)";
            $stmt_schedule = $_conn_db->prepare($sql_schedule);
            foreach ($operating_days_array as $day) {
                if (!empty($departure_times_specific[$day])) {
                    $stmt_schedule->execute([$current_route_id, $day, $departure_times_specific[$day]]);
                }
            }
        }
        
        // --- Part C: Insert all 'route_stops' ---
        if (!empty($stop_names)) {
            $sql_stop = "INSERT INTO route_stops (route_id, stop_name, stop_order, duration_from_start_minutes, price_seater_lower, price_seater_upper, price_sleeper_lower, price_sleeper_upper) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_stop = $_conn_db->prepare($sql_stop);
            foreach ($stop_names as $index => $stop_name) {
                if (!empty(trim($stop_name))) {
                    $stmt_stop->execute([
                        $current_route_id, trim($stop_name), $index + 1,
                        $durations[$index] ?? 0,
                        !empty($prices_sl[$index]) ? $prices_sl[$index] : null,
                        !empty($prices_su[$index]) ? $prices_su[$index] : null,
                        !empty($prices_ll[$index]) ? $prices_ll[$index] : null,
                        !empty($prices_lu[$index]) ? $prices_lu[$index] : null
                    ]);
                }
            }
        }
        
        // --- Part D: Insert the 'route_staff_assignments' ---
        $sql_staff = "INSERT INTO route_staff_assignments (route_id, staff_id, role) VALUES (?, ?, ?)";
        $stmt_staff = $_conn_db->prepare($sql_staff);

        $single_assignments = [
            'Driver'       => $driver_id, 'Co-Driver'    => $co_driver_id,
            'Conductor'    => $conductor_id, 'Co-Conductor' => $co_conductor_id
        ];

        foreach ($single_assignments as $role => $staff_id) {
            if ($staff_id) { // This will be false for 0, null, or false
                $stmt_staff->execute([$current_route_id, $staff_id, $role]);
            }
        }
        
        foreach ($helper_ids as $helper_id) {
            $helper_id_int = filter_var($helper_id, FILTER_VALIDATE_INT);
            if ($helper_id_int) {
                $stmt_staff->execute([$current_route_id, $helper_id_int, 'Helper']);
            }
        }
 
        $_conn_db->commit();
        send_response('true', 'success', 'Success', 'Route has been saved successfully.', 'view_routes.php');

    } catch (Exception $e) {  
$_conn_db->rollBack(); 
error_log("Route Save Failed: " . $e->getMessage());  
send_response('false', 'danger', 'Database Error', 'Error: ' . $e->getMessage());
    }
}

// ===================================================
//  ACTION: TOGGLE POPULAR STATUS
// ===================================================
if ($action == 'toggle_popular') {
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
    $is_popular = filter_input(INPUT_POST, 'is_popular', FILTER_VALIDATE_INT);

    if (!$route_id) {
        send_response('false', 'warning', 'Invalid Input', 'Route ID was not provided.');
    }

    try {
        $stmt = $_conn_db->prepare("UPDATE routes SET is_popular = ? WHERE route_id = ?");
        $stmt->execute([$is_popular, $route_id]);
        
        $status_text = ($is_popular == 1) ? 'marked as popular' : 'unmarked as popular';
        send_response('true', 'success', 'Success', 'Route has been successfully ' . $status_text . '.');
    } catch (PDOException $e) {
        error_log("Toggle Popular Failed: " . $e->getMessage());
        send_response('false', 'danger', 'Database Error', 'Could not update the route status.');
    }
}
 
// --- FALLBACK: If no action matched ---
send_response('false', 'warning', 'Unknown Action', 'The requested action "' . htmlspecialchars($action) . '" is not valid.');
?>