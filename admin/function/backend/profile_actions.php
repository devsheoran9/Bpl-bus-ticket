<?php
// function/backend/profile_actions.php
header('Content-Type: application/json');
include_once('../_db.php');
session_security_check(); // Ensures the user is logged in

function send_json_response($status, $message, $extra_data = []) {
    $response = ['status' => $status, 'message' => $message];
    if (!empty($extra_data)) {
        $response = array_merge($response, $extra_data);
    }
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Invalid request method.');
}

// 1. Get user input from the form
$employee_id = $_SESSION['user']['id'];
$name = trim($_POST['name'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_confirm = $_POST['password_confirm'] ?? '';

// 2. Validate input
if (empty($name) || empty($mobile) || empty($email) || empty($password_confirm)) {
    send_json_response('error', 'All fields, including password, are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json_response('error', 'Invalid email address format.');
}

try {
    // 3. Check for duplicates (mobile/email), excluding the current user
    $stmt_check = $_conn_db->prepare("SELECT id FROM admin WHERE (email = ? OR mobile = ?) AND id != ?");
    $stmt_check->execute([$email, $mobile, $employee_id]);
    if ($stmt_check->fetch()) {
        send_json_response('error', 'Another account with this email or mobile number already exists.');
    }
    
    // 4. Verify the current password to authorize the change
    $stmt_pass = $_conn_db->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt_pass->execute([$employee_id]);
    $db_password_hash = $stmt_pass->fetchColumn();

    if (!$db_password_hash || !password_verify($password_confirm, $db_password_hash)) {
        send_json_response('error', 'The password you entered is incorrect. Changes were not saved.');
    }

    // 5. If password is correct, update the user's details
    $update_stmt = $_conn_db->prepare("UPDATE admin SET name = ?, mobile = ?, email = ? WHERE id = ?");
    
    if ($update_stmt->execute([$name, $mobile, $email, $employee_id])) {
        // IMPORTANT: Update the session with the new details immediately!
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['mobile'] = $mobile;
        $_SESSION['user']['email'] = $email;
        
        send_json_response('success', 'Your profile has been updated successfully.', ['new_name' => $name]);
    } else {
        send_json_response('error', 'Failed to update profile in the database.');
    }

} catch (PDOException $e) {
    error_log("Profile Update Error: " . $e->getMessage());
    send_json_response('error', 'A database error occurred.');
}