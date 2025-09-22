<?php
header('Content-Type: application/json');
include_once('../_db.php');
session_security_check();

/**
 * Sends a standardized JSON response and terminates the script.
 * @param string $status  - 'success' or 'error'.
 * @param string $message - A descriptive message for the frontend.
 * @param array  $data    - (Optional) An associative array of data to send back.
 */
function send_json_response($status, $message, $data = []) {
    $response = ['status' => $status, 'message' => $message];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

// Security Check: Only main admin can perform these actions
if (!user_has_permission('main_admin')) {
    send_json_response('error', 'Access Denied. You do not have permission to perform this action.');
}

// Get the action from either GET or POST request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        // ACTION: Toggle a user's status (Activate/Deactivate)
        case 'toggle_user_status':
            // This action modifies data, so we enforce POST method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                send_json_response('error', 'Invalid request method for this action.');
            }

            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $new_status = filter_input(INPUT_POST, 'new_status', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2]]);

            if (!$user_id || !$new_status) {
                throw new Exception('Invalid data provided for status toggle.');
            }

            $stmt = $_conn_db->prepare("UPDATE users SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status, $user_id])) {
                $status_text = ($new_status == 1) ? 'activated' : 'deactivated';
                send_json_response('success', 'User account has been successfully ' . $status_text . '.');
            } else {
                throw new Exception('Failed to update user status in the database.');
            }
            break;

        // ACTION: Get detailed stats and bookings for a single user
        case 'get_user_details':
            // This action fetches data, so we allow GET method
            $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
            if (!$user_id) {
                throw new Exception('Invalid user ID provided.');
            }
            
            // Query for user's booking statistics
            $stmt_stats = $_conn_db->prepare("
                SELECT 
                    COUNT(booking_id) as total_bookings,
                    COALESCE(SUM(total_fare), 0) as total_spent,
                    MAX(travel_date) as last_travel_date
                FROM bookings 
                WHERE user_id = ? AND booking_status = 'CONFIRMED'
            ");
            $stmt_stats->execute([$user_id]);
            $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

            // Query for user's most recent bookings
            $stmt_bookings = $_conn_db->prepare("
                SELECT ticket_no, travel_date, total_fare, route_name 
                FROM bookings b
                JOIN routes r ON b.route_id = r.route_id
                WHERE b.user_id = ? 
                ORDER BY b.travel_date DESC 
                LIMIT 5
            ");
            $stmt_bookings->execute([$user_id]);
            $recent_bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);

            // Combine all data into a single payload
            $data = [
                'stats' => $stats,
                'recent_bookings' => $recent_bookings
            ];

            send_json_response('success', 'User details fetched successfully.', ['details' => $data]);
            break;

        default:
            throw new Exception('Unknown or invalid action specified.');
    }
} catch (PDOException $e) {
    // Catch database-specific errors
    error_log("User Actions Error (PDO): " . $e->getMessage());
    send_json_response('error', 'A database error occurred. Please check the server logs.');
} catch (Exception $e) {
    // Catch logical errors (like invalid IDs)
    send_json_response('error', $e->getMessage());
}