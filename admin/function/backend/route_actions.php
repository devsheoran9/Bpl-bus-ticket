<?php
// Set header to return JSON for AJAX requests
header('Content-Type: application/json');

global $_conn_db;
include_once('../_db.php'); // Go up one directory to find _db.php

// --- AJAX RESPONSE HELPER FUNCTION ---
function send_response($res, $notif_type, $notif_title, $notif_desc, $goTo = '') {
    echo json_encode([
        'res' => $res,
        'notif_type' => $notif_type,
        'notif_title' => $notif_title,
        'notif_desc' => $notif_desc,
        'notif_popup' => 'false', // Using $.notify as per your script
        'goTo' => $goTo
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response('false', 'danger', 'Error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';

// --- ACTION: DELETE ROUTE ---
if ($action == 'delete_route') {
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
    if (!$route_id) {
        send_response('false', 'warning', 'Invalid Input', 'Route ID was not provided.');
    }
    
    try {
        $stmt = $_conn_db->prepare("DELETE FROM routes WHERE route_id = ?");
        $stmt->execute([$route_id]);
        send_response('true', 'success', 'Success', 'The route and all its schedules have been deleted.', 'view_routes.php');
    } catch (PDOException $e) {
        send_response('false', 'danger', 'Database Error', 'Could not delete the route.');
    }
}

// --- ACTION: ADD OR UPDATE ROUTE ---
if ($action == 'save_route') {
    // --- THIS IS THE CORRECTED LOGIC ---

    // 1. Get all data from the form
    $bus_id = $_POST['bus_id'];
    $route_name = trim($_POST['route_name']);
    $starting_point = trim($_POST['starting_point']);
    $status = $_POST['status'];
    
    // Day-specific schedules
    $operating_days_array = $_POST['operating_days'] ?? [];
    $departure_times_specific = $_POST['departure_time'] ?? []; // This is an array keyed by day, e.g., ['Mon' => '10:00']

    // Stop details
    $stop_names = $_POST['stop_name'] ?? [];
    $durations = $_POST['duration'] ?? [];
    $prices_sl = $_POST['price_sl'] ?? []; $prices_su = $_POST['price_su'] ?? [];
    $prices_ll = $_POST['price_ll'] ?? []; $prices_lu = $_POST['price_lu'] ?? [];
    
    $ending_point = !empty($stop_names) ? end($stop_names) : $starting_point;
    $action_type = $_POST['action_type'];
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);

    // 2. Start database transaction
    $_conn_db->beginTransaction();
    try {
        if ($action_type == 'update' && $route_id) {
            // On update, we only update the main details. Times are in the schedule table.
            $sql = "UPDATE routes SET bus_id = ?, route_name = ?, starting_point = ?, ending_point = ?, status = ? WHERE route_id = ?";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([$bus_id, $route_name, $starting_point, $ending_point, $status, $route_id]);
            
            // Clear old child records to re-insert them
            $_conn_db->prepare("DELETE FROM route_schedules WHERE route_id = ?")->execute([$route_id]);
            $_conn_db->prepare("DELETE FROM route_stops WHERE route_id = ?")->execute([$route_id]);
            $current_route_id = $route_id;
        } else {
            // On insert, same logic, but we get a new ID
            $sql = "INSERT INTO routes (bus_id, route_name, starting_point, ending_point, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([$bus_id, $route_name, $starting_point, $ending_point, $status]);
            $current_route_id = $_conn_db->lastInsertId();
        }

        // 3. Save the day-specific schedules
        if (!empty($operating_days_array)) {
            $sql_schedule = "INSERT INTO route_schedules (route_id, operating_day, departure_time) VALUES (?, ?, ?)";
            $stmt_schedule = $_conn_db->prepare($sql_schedule);
            foreach ($operating_days_array as $day) {
                // Check if a specific time for this day was submitted
                if (!empty($departure_times_specific[$day])) {
                    $stmt_schedule->execute([$current_route_id, $day, $departure_times_specific[$day]]);
                }
            }
        }
        
        // 4. Save all the stops with their durations and prices
        if (!empty($stop_names)) {
            $sql_stop = "INSERT INTO route_stops (route_id, stop_name, stop_order, duration_from_start_minutes, price_seater_lower, price_seater_upper, price_sleeper_lower, price_sleeper_upper) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_stop = $_conn_db->prepare($sql_stop);
            foreach ($stop_names as $index => $stop_name) {
                if (!empty(trim($stop_name))) {
                    $stmt_stop->execute([
                        $current_route_id, 
                        trim($stop_name), 
                        $index + 1, 
                        $durations[$index] ?? 0,
                        !empty($prices_sl[$index]) ? $prices_sl[$index] : null,
                        !empty($prices_su[$index]) ? $prices_su[$index] : null,
                        !empty($prices_ll[$index]) ? $prices_ll[$index] : null,
                        !empty($prices_lu[$index]) ? $prices_lu[$index] : null
                    ]);
                }
            }
        }

        // 5. If everything was successful, commit the changes
        $_conn_db->commit();
        send_response('true', 'success', 'Success', 'Route has been saved successfully.', 'view_routes.php');
    } catch (Exception $e) {
        // If anything failed, roll back all changes
        $_conn_db->rollBack();
        send_response('false', 'danger', 'Database Error', 'Could not save the route. ' . $e->getMessage());
    }
}

// Fallback for unknown actions
send_response('false', 'warning', 'Unknown Action', 'The requested action is not valid.');