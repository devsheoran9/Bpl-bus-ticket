<?php
header('Content-Type: application/json');
global $_conn_db;
include_once('../_db.php');
// session_start();

function send_json_response($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

// Security Check: Only main admin can perform these actions
if (!isset($_SESSION['user']['type']) || $_SESSION['user']['type'] !== 'main_admin') {
    send_json_response('error', 'Access Denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';

if ($action == 'collect_all_cash') {
    $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
    if (!$employee_id) {
        send_json_response('error', 'Invalid Employee ID.');
    }

    $admin_id = $_SESSION['user']['id'];

    $_conn_db->beginTransaction();
    try {
        // 1. Find all uncollected CASH bookings for this employee
        $sql_find = "
            SELECT b.booking_id, b.total_fare
            FROM bookings b
            LEFT JOIN transactions t ON b.booking_id = t.booking_id
            LEFT JOIN cash_collections_log ccl ON b.booking_id = ccl.booking_id
            WHERE b.booked_by_employee_id = ?
              AND t.transaction_id IS NULL
              AND ccl.collection_id IS NULL
        ";
        $stmt_find = $_conn_db->prepare($sql_find);
        $stmt_find->execute([$employee_id]);
        $bookings_to_collect = $stmt_find->fetchAll(PDO::FETCH_ASSOC);

        if (empty($bookings_to_collect)) {
            send_json_response('info', 'No pending cash to collect for this employee.');
        }

        // 2. Insert a log record for each collected booking
        $sql_log = "INSERT INTO cash_collections_log (booking_id, amount_collected, collected_by_admin_id, collected_from_employee_id) VALUES (?, ?, ?, ?)";
        $stmt_log = $_conn_db->prepare($sql_log);

        foreach ($bookings_to_collect as $booking) {
            $stmt_log->execute([$booking['booking_id'], $booking['total_fare'], $admin_id, $employee_id]);
        }
        
        $_conn_db->commit();
        send_json_response('success', 'All pending cash has been successfully marked as collected.');

    } catch (PDOException $e) {
        $_conn_db->rollBack();
        error_log("Collect All Cash Error: " . $e->getMessage());
        send_json_response('error', 'A database error occurred. The collection could not be logged.');
    }
} else {
    send_json_response('error', 'Unknown action.');
}
?>