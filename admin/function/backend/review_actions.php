<?php
header('Content-Type: application/json');
include_once('../_db.php');
session_security_check();

function send_json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';

if ($action === 'delete_review') {
    // Permission Check: User must have the specific delete permission
    if (!user_has_permission('can_delete_review')) {
        send_json_response('error', 'Access Denied. You do not have permission to delete reviews.');
    }

    $review_id = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    if (!$review_id) {
        send_json_response('error', 'Invalid Review ID provided.');
    }

    try {
        $stmt = $_conn_db->prepare("DELETE FROM reviews WHERE id = ?");
        if ($stmt->execute([$review_id])) {
            if ($stmt->rowCount() > 0) {
                send_json_response('success', 'Review has been successfully deleted.');
            } else {
                send_json_response('error', 'Review not found or already deleted.');
            }
        } else {
            throw new Exception('Failed to execute the delete query.');
        }
    } catch (PDOException | Exception $e) {
        error_log("Delete Review Error: " . $e->getMessage());
        send_json_response('error', 'A database error occurred. Could not delete the review.');
    }
} else {
    send_json_response('error', 'Unknown action specified.');
}
?>