<?php
// function/backend/settings_actions.php
header('Content-Type: application/json');
include_once('../_db.php');
session_security_check();

function send_json_response($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';

if ($action == 'save_settings') {
    if (!user_has_permission('can_manage_settings')) {
        send_json_response('error', 'You do not have permission to perform this action.');
    }

    $password = $_POST['password'] ?? '';
    $settings_data = $_POST['settings'] ?? [];

    if (empty($password)) {
        send_json_response('error', 'Password is required to confirm changes.');
    }

    try {
        // --- Step 1: Verify the current user's password ---
        $admin_id = $_SESSION['user']['id'];
        $stmt_pass = $_conn_db->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt_pass->execute([$admin_id]);
        $hashed_password = $stmt_pass->fetchColumn();

        if (!$hashed_password || !password_verify($password, $hashed_password)) {
            send_json_response('error', 'Incorrect password. Settings were not saved.');
        }

        // --- Step 2: If password is correct, update all settings ---
        $_conn_db->beginTransaction();

        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $_conn_db->prepare($sql);

        foreach ($settings_data as $key => $value) {
            $safe_key = htmlspecialchars(trim($key));
            // Execute with an array of parameters for each iteration
            $stmt->execute([$safe_key, trim($value)]);
        }

        $_conn_db->commit();
        send_json_response('success', 'Company settings have been updated successfully.');
    } catch (PDOException $e) {
        if ($_conn_db->inTransaction()) {
            $_conn_db->rollBack();
        }
        error_log("Settings Save Error: " . $e->getMessage());
        send_json_response('error', 'Database Error: ' . $e->getMessage());
    }
} else {
    send_json_response('error', 'Unknown action specified.');
}
