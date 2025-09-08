<?php
// function/backend/route_actions.php

// AJAX अनुरोधों के लिए JSON लौटाने के लिए हेडर सेट करें
header('Content-Type: application/json');

global $_conn_db;
include_once('../_db.php'); // _db.php खोजने के लिए एक डायरेक्टरी ऊपर जाएँ

// --- AJAX प्रतिक्रिया सहायक फ़ंक्शन ---
function send_response($res, $notif_type, $notif_title, $notif_desc, $goTo = '') {
    echo json_encode([
        'res' => $res,
        'notif_type' => $notif_type,
        'notif_title' => $notif_title,
        'notif_desc' => $notif_desc,
        'goTo' => $goTo
    ]);
    exit(); // प्रतिक्रिया भेजने के बाद स्क्रिप्ट को रोकें
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
    // 1. फ़ॉर्म से सभी डेटा प्राप्त करें
    $bus_id = $_POST['bus_id'];
    $route_name = trim($_POST['route_name']);
    $starting_point = trim($_POST['starting_point']);
    $status = $_POST['status'];
    $operating_days_array = $_POST['operating_days'] ?? [];
    $departure_times_specific = $_POST['departure_time'] ?? [];
    $stop_names = $_POST['stop_name'] ?? [];
    $durations = $_POST['duration'] ?? [];
    $prices_sl = $_POST['price_sl'] ?? []; $prices_su = $_POST['price_su'] ?? [];
    $prices_ll = $_POST['price_ll'] ?? []; $prices_lu = $_POST['price_lu'] ?? [];
    $ending_point = !empty($stop_names) ? end($stop_names) : $starting_point;
    $action_type = $_POST['action_type'];
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);

    // 2. डेटाबेस लेनदेन शुरू करें
    $_conn_db->beginTransaction();
    try {
        if ($action_type == 'update' && $route_id) {
            $sql = "UPDATE routes SET bus_id = ?, route_name = ?, starting_point = ?, ending_point = ?, status = ? WHERE route_id = ?";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([$bus_id, $route_name, $starting_point, $ending_point, $status, $route_id]);
            $_conn_db->prepare("DELETE FROM route_schedules WHERE route_id = ?")->execute([$route_id]);
            $_conn_db->prepare("DELETE FROM route_stops WHERE route_id = ?")->execute([$route_id]);
            $current_route_id = $route_id;
        } else {
            $sql = "INSERT INTO routes (bus_id, route_name, starting_point, ending_point, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([$bus_id, $route_name, $starting_point, $ending_point, $status]);
            $current_route_id = $_conn_db->lastInsertId();
        }

        // 3. दिन-विशिष्ट शेड्यूल सहेजें
        if (!empty($operating_days_array)) {
            $sql_schedule = "INSERT INTO route_schedules (route_id, operating_day, departure_time) VALUES (?, ?, ?)";
            $stmt_schedule = $_conn_db->prepare($sql_schedule);
            foreach ($operating_days_array as $day) {
                if (!empty($departure_times_specific[$day])) {
                    $stmt_schedule->execute([$current_route_id, $day, $departure_times_specific[$day]]);
                }
            }
        }
        
        // 4. सभी स्टॉप को उनकी अवधि और कीमतों के साथ सहेजें
        if (!empty($stop_names)) {
            $sql_stop = "INSERT INTO route_stops (route_id, stop_name, stop_order, duration_from_start_minutes, price_seater_lower, price_seater_upper, price_sleeper_lower, price_sleeper_upper) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_stop = $_conn_db->prepare($sql_stop);
            foreach ($stop_names as $index => $stop_name) {
                if (!empty(trim($stop_name))) {
                    $stmt_stop->execute([
                        $current_route_id, trim($stop_name), $index + 1, $durations[$index] ?? 0,
                        !empty($prices_sl[$index]) ? $prices_sl[$index] : null,
                        !empty($prices_su[$index]) ? $prices_su[$index] : null,
                        !empty($prices_ll[$index]) ? $prices_ll[$index] : null,
                        !empty($prices_lu[$index]) ? $prices_lu[$index] : null
                    ]);
                }
            }
        }

        // 5. यदि सब कुछ सफल रहा, तो परिवर्तनों को सहेजें
        $_conn_db->commit();
        send_response('true', 'success', 'Success', 'Route has been saved successfully.', 'view_routes.php');
    } catch (Exception $e) {
        // यदि कुछ भी विफल रहा, तो सभी परिवर्तनों को वापस ले लें
        $_conn_db->rollBack();
        send_response('false', 'danger', 'Database Error', 'Could not save the route. ' . $e->getMessage());
    }
}

// --- ACTION: TOGGLE POPULAR STATUS (FIXED) ---
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

        // send_response फ़ंक्शन का उपयोग करके सही प्रतिक्रिया भेजें
        send_response('true', 'success', 'Success', 'Route has been successfully ' . $status_text . '.');
    } catch (PDOException $e) {
        send_response('false', 'danger', 'Database Error', 'Could not update the route status.');
    }
}
 
// यदि कोई भी if ब्लॉक मेल नहीं खाता है तो अंतिम प्रतिक्रिया
send_response('false', 'warning', 'Unknown Action', 'The requested action "' . htmlspecialchars($action) . '" is not valid.');

?>