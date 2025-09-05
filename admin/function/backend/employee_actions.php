<?php
// function/backend/employee_actions.php

// Set the content type to JSON for all responses
header('Content-Type: application/json');

include('../_db.php');

// Ensure an action is specified
if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'No action specified.']);
    exit();
}

$action = $_POST['action'];

// --- ACTION: ADD A NEW EMPLOYEE ---
if ($action === 'add_employee') {
    // Server-side permission check for security
    if (!user_has_permission('can_manage_employees')) {
        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to perform this action.']);
        exit();
    }

    // Validate input
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($mobile) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
        exit();
    }

    try {
        // Check if email or mobile already exist
        $stmt = $_conn_db->prepare("SELECT id FROM admin WHERE email = ? OR mobile = ?");
        $stmt->execute([$email, $mobile]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'An account with this email or mobile number already exists.']);
            exit();
        }

        // Process permissions
        $permissions_array = [];
        if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
            foreach ($_POST['permissions'] as $perm) {
                // Sanitize the permission key to prevent any malicious input
                $permissions_array[htmlspecialchars($perm)] = true;
            }
        }
        $permissions_json = json_encode($permissions_array);

        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $salt = '123456'; // Note: This salt is not used by password_hash, but kept for schema compatibility

        // Insert new employee into the database
        $insert_stmt = $_conn_db->prepare(
            "INSERT INTO admin (name, mobile, email, password, password_salt, type, permissions, status) 
             VALUES (?, ?, ?, ?, ?, 'employee', ?, '1')"
        );
        $insert_stmt->execute([$name, $mobile, $email, $hashed_password, $salt, $permissions_json]);

        echo json_encode(['status' => 'success', 'message' => 'Employee account created successfully!']);

    } catch (PDOException $e) {
        error_log("Employee Add Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error. Could not add employee.']);
    }
}

// --- ACTION: TOGGLE EMPLOYEE STATUS ---
elseif ($action === 'toggle_status') {
    if (!user_has_permission('can_manage_employees')) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied.']);
        exit();
    }

    $employee_id = $_POST['employee_id'] ?? 0;
    $new_status = $_POST['new_status'] ?? 0;

    if (empty($employee_id) || !in_array($new_status, [1, 2])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
        exit();
    }

    try {
        $stmt = $_conn_db->prepare("UPDATE admin SET status = ? WHERE id = ? AND type = 'employee'");
        $stmt->execute([$new_status, $employee_id]);
        $status_text = ($new_status == 1) ? 'activated' : 'deactivated';
        echo json_encode(['status' => 'success', 'message' => 'Employee status has been ' . $status_text . '.']);
    } catch (PDOException $e) {
        error_log("Employee Status Toggle Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error. Could not update status.']);
    }
}

// --- ACTION: DELETE AN EMPLOYEE ---
elseif ($action === 'delete_employee') {
    if (!user_has_permission('can_manage_employees')) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied.']);
        exit();
    }

    $employee_id = $_POST['employee_id'] ?? 0;

    if (empty($employee_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid employee ID.']);
        exit();
    }

    try {
        $stmt = $_conn_db->prepare("DELETE FROM admin WHERE id = ? AND type = 'employee'");
        $stmt->execute([$employee_id]);
        echo json_encode(['status' => 'success', 'message' => 'Employee account has been permanently deleted.']);
    } catch (PDOException $e) {
        error_log("Employee Delete Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error. Could not delete employee.']);
    }
}

// --- Fallback for unknown actions ---
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
}
?>