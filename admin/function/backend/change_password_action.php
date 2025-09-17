<?php
// function/backend/change_password_action.php
header('Content-Type: application/json');
include_once('../_db.php');
session_security_check(); // Ensures the user is logged in

function send_json_response($status, $message, $redirect = '') {
    echo json_encode(['status' => $status, 'message' => $message, 'redirect' => $redirect]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Invalid request method.');
}

// 1. Get input data
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$employee_id = $_SESSION['user']['id']; // Get the ID of the logged-in user

// 2. Validate input
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    send_json_response('error', 'All password fields are required.');
}
if (strlen($new_password) < 6) {
    send_json_response('error', 'New password must be at least 6 characters long.');
}
if ($new_password !== $confirm_password) {
    send_json_response('error', 'New password and confirmation do not match.');
}
if ($new_password === $current_password) {
    send_json_response('error', 'New password cannot be the same as the current password.');
}

try {
    // 3. Verify the current password
    $stmt = $_conn_db->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->execute([$employee_id]);
    $db_password_hash = $stmt->fetchColumn();

    if (!$db_password_hash || !password_verify($current_password, $db_password_hash)) {
        send_json_response('error', 'The current password you entered is incorrect.');
    }

    // 4. If current password is correct, hash and update the new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $update_stmt = $_conn_db->prepare("UPDATE admin SET password = ? WHERE id = ?");
    if ($update_stmt->execute([$new_password_hash, $employee_id])) {
        // For enhanced security, terminate all other sessions by updating the session token
        $new_session_token = bin2hex(random_bytes(32));
        $token_stmt = $_conn_db->prepare("UPDATE admin SET session_token = ? WHERE id = ?");
        $token_stmt->execute([$new_session_token, $employee_id]);
        $_SESSION['user']['session_token'] = $new_session_token; // Update current session

        send_json_response('success', 'Your password has been changed successfully.', 'dashboard.php');
    } else {
        send_json_response('error', 'Failed to update password in the database.');
    }

} catch (PDOException $e) {
    error_log("Change Password Error: " . $e->getMessage());
    send_json_response('error', 'A database error occurred.');
}