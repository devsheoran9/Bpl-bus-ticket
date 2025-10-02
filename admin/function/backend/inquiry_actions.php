<?php
// function/backend/inquiry_actions.php
header('Content-Type: application/json');
include_once('../_db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic security check
if (!isset($_SESSION['user']['id']) || !user_has_permission('main_admin')) {
    echo json_encode(['success' => false, 'message' => 'Access Denied.']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $inquiry_id = filter_input(INPUT_POST, 'inquiry_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    $allowed_statuses = ['Pending', 'Contacted', 'Booked', 'Closed'];

    if (!$inquiry_id || !in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit();
    }

    try {
        $stmt = $_conn_db->prepare("UPDATE charter_inquiries SET status = ? WHERE inquiry_id = ?");
        $stmt->execute([$status, $inquiry_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Inquiry Status Update Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit();
}

if ($action === 'delete_inquiry') {
    $inquiry_id = filter_input(INPUT_POST, 'inquiry_id', FILTER_VALIDATE_INT);

    if (!$inquiry_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid Inquiry ID.']);
        exit();
    }

    try {
        $stmt = $_conn_db->prepare("DELETE FROM charter_inquiries WHERE inquiry_id = ?");
        $stmt->execute([$inquiry_id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Inquiry not found.']);
        }
    } catch (PDOException $e) {
        error_log("Inquiry Deletion Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'No valid action specified.']);