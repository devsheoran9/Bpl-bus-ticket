<?php
// function/backend/employee_actions.php

header('Content-Type: application/json');
include('../_db.php');

// सुनिश्चित करें कि उपयोगकर्ता लॉग इन है और एक सत्र है
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required. Please log in again.']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// सभी क्रियाओं के लिए अनुमति जांच
if (!user_has_permission('can_manage_employees')) {
    echo json_encode(['status' => 'error', 'message' => 'You do not have permission to perform this action.']);
    exit();
}

try {
    switch ($action) {
        // --- ACTION: ADD A NEW EMPLOYEE ---
        case 'add_employee':
            $name = trim($_POST['name'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (empty($name) || empty($mobile) || empty($email) || empty($password)) {
                throw new Exception('All fields are required.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address.');
            }
            $stmt = $_conn_db->prepare("SELECT id FROM admin WHERE email = ? OR mobile = ?");
            $stmt->execute([$email, $mobile]);
            if ($stmt->fetch()) {
                throw new Exception('An account with this email or mobile number already exists.');
            }
            $permissions_array = [];
            if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                foreach ($_POST['permissions'] as $perm) {
                    $permissions_array[htmlspecialchars($perm)] = true;
                }
            }
            $permissions_json = json_encode($permissions_array);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $_conn_db->prepare("INSERT INTO admin (name, mobile, email, password, password_salt, type, permissions, status) VALUES (?, ?, ?, ?, ?, 'employee', ?, '1')");
            $insert_stmt->execute([$name, $mobile, $email, $hashed_password, '123456', $permissions_json]);
            echo json_encode(['status' => 'success', 'message' => 'Employee account created successfully!']);
            break;

        // --- ACTION: TOGGLE EMPLOYEE STATUS ---
        case 'toggle_status':
            $employee_id = (int)($_POST['employee_id'] ?? 0);
            $new_status = (int)($_POST['new_status'] ?? 0);
            if (empty($employee_id) || !in_array($new_status, [1, 2])) {
                throw new Exception('Invalid data provided.');
            }
            $stmt = $_conn_db->prepare("UPDATE admin SET status = ? WHERE id = ? AND type = 'employee'");
            $stmt->execute([$new_status, $employee_id]);
            $status_text = ($new_status == 1) ? 'activated' : 'deactivated';
            echo json_encode(['status' => 'success', 'message' => 'Employee status has been ' . $status_text . '.']);
            break;

        // --- ACTION: DELETE AN EMPLOYEE ---
        case 'delete_employee':
            $employee_id = (int)($_POST['employee_id'] ?? 0);
            if (empty($employee_id)) {
                throw new Exception('Invalid employee ID.');
            }
            $stmt = $_conn_db->prepare("DELETE FROM admin WHERE id = ? AND type = 'employee'");
            $stmt->execute([$employee_id]);
            echo json_encode(['status' => 'success', 'message' => 'Employee account has been permanently deleted.']);
            break;

        // --- ACTION: FORCE LOGOUT A USER ---
        case 'force_logout':
            $employee_id = (int)($_POST['employee_id'] ?? 0);
            if (empty($employee_id)) {
                throw new Exception('Invalid employee ID.');
            }
            $stmt = $_conn_db->prepare("UPDATE admin SET session_token = NULL WHERE id = ? AND type = 'employee'");
            $stmt->execute([$employee_id]);
            echo json_encode(['status' => 'success', 'message' => 'User session has been terminated.']);
            break;

        // --- ACTION: GET LOGIN HISTORY FOR A USER ---
        case 'get_login_history':
            $employee_id = (int)($_GET['employee_id'] ?? 0);
            if (empty($employee_id)) {
                throw new Exception('Invalid employee ID.');
            }
            $stmt = $_conn_db->prepare("SELECT activity_type, ip_address, log_time FROM admin_activity_log WHERE admin_id = ? ORDER BY log_time DESC LIMIT 50");
            $stmt->execute([$employee_id]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'history' => $history]);
            break;

        default:
            throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    error_log("Employee Action Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A database error occurred. Please check the logs.']);
}
?>