<?php
// function/backend/employee_actions.php

header('Content-Type: application/json');
// It's better to include files at the top.
include_once('../_db.php'); 

// We need to start the session to check for login and permissions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function for sending JSON responses
function send_json_response($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

try {
    // Check if user is logged in for all actions
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Authentication required. Please log in again.');
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // Check general permission for managing employees for all actions
    if (!user_has_permission('can_manage_employees')) {
        throw new Exception('You do not have permission to manage employees.');
    }

    switch ($action) {
        // --- ACTION: ADD A NEW EMPLOYEE ---
        case 'add_employee':
            $name = trim($_POST['name'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            // --- NEW: Get the linked staff ID ---
            $linked_staff_id = filter_input(INPUT_POST, 'linked_staff_id', FILTER_VALIDATE_INT) ?: null;

            if (empty($name) || empty($mobile) || empty($email) || empty($password)) {
                throw new Exception('All fields are required.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address format.');
            }
            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters long.');
            }

            $stmt = $_conn_db->prepare("SELECT id FROM admin WHERE email = ? OR mobile = ?");
            $stmt->execute([$email, $mobile]);
            if ($stmt->fetch()) {
                throw new Exception('An employee with this email or mobile number already exists.');
            }

            $permissions_posted = $_POST['permissions'] ?? [];
            $permissions_json = [];
            if (is_array($permissions_posted)) {
                foreach ($permissions_posted as $perm) {
                    $safe_perm = htmlspecialchars(trim($perm));
                    if (!empty($safe_perm)) {
                        $permissions_json[$safe_perm] = true;
                    }
                }
            }
            $permissions_to_store = json_encode($permissions_json);

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $password_salt = 'bcrypt';

            // --- NEW: Updated INSERT query ---
            $insert_stmt = $_conn_db->prepare(
                "INSERT INTO admin (name, mobile, email, password, password_salt, type, permissions, status, linked_staff_id) 
                 VALUES (?, ?, ?, ?, ?, 'employee', ?, '1', ?)"
            );
            $insert_stmt->execute([$name, $mobile, $email, $hashed_password, $password_salt, $permissions_to_store, $linked_staff_id]);
            
            send_json_response('true', 'success', 'Employee account created successfully!');
            break;
            
            case 'update_employee':
                $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
                $name = trim($_POST['name'] ?? '');
                $mobile = trim($_POST['mobile'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                // --- NEW: Get the linked staff ID ---
                $linked_staff_id = filter_input(INPUT_POST, 'linked_staff_id', FILTER_VALIDATE_INT) ?: null;
                
                if (!$employee_id || empty($name) || empty($mobile) || empty($email)) {
                    throw new Exception('Required fields (Name, Mobile, Email) are missing.');
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email address format.');
                }
    
                $stmt = $_conn_db->prepare("SELECT id FROM admin WHERE (email = ? OR mobile = ?) AND id != ?");
                $stmt->execute([$email, $mobile, $employee_id]);
                if ($stmt->fetch()) {
                    throw new Exception('An account with this email or mobile number already exists.');
                }
    
                $permissions_posted = $_POST['permissions'] ?? [];
                $permissions_json_arr = [];
                if (is_array($permissions_posted)) {
                    foreach ($permissions_posted as $perm) {
                        $safe_perm = htmlspecialchars(trim($perm));
                        if (!empty($safe_perm)) {
                            $permissions_json_arr[$safe_perm] = true;
                        }
                    }
                }
                $permissions_to_store = json_encode($permissions_json_arr);
                
                // --- NEW: Updated UPDATE query ---
                $sql = "UPDATE admin SET name = ?, mobile = ?, email = ?, permissions = ?, linked_staff_id = ? 
                        WHERE id = ? AND type = 'employee'";
                $stmt = $_conn_db->prepare($sql);
                $stmt->execute([$name, $mobile, $email, $permissions_to_store, $linked_staff_id, $employee_id]);
    
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        throw new Exception('New password must be at least 6 characters long.');
                    }
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $pass_stmt = $_conn_db->prepare("UPDATE admin SET password = ? WHERE id = ?");
                    $pass_stmt->execute([$hashed_password, $employee_id]);
                }
                
                send_json_response('success', 'Employee details updated successfully!');
                break;
    

           

      
        case 'toggle_status':
            $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            $new_status = filter_input(INPUT_POST, 'new_status', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2]]);
            
            if (!$employee_id || !$new_status) {
                throw new Exception('Invalid data provided for status toggle.');
            }
            
            $stmt = $_conn_db->prepare("UPDATE admin SET status = ? WHERE id = ? AND type = 'employee'");
            $stmt->execute([$new_status, $employee_id]);
            $status_text = ($new_status == 1) ? 'activated' : 'deactivated';
            send_json_response('success', 'Employee status has been ' . $status_text . '.');
            break;

        // --- ACTION: DELETE AN EMPLOYEE ---
        case 'delete_employee':
            $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            if (!$employee_id) {
                throw new Exception('Invalid employee ID.');
            }
            $stmt = $_conn_db->prepare("DELETE FROM admin WHERE id = ? AND type = 'employee'");
            $stmt->execute([$employee_id]);
            send_json_response('success', 'Employee account has been permanently deleted.');
            break;

        // --- ACTION: FORCE LOGOUT A USER ---
        case 'force_logout':
            $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
            if (!$employee_id) {
                throw new Exception('Invalid employee ID.');
            }
            $stmt = $_conn_db->prepare("UPDATE admin SET session_token = NULL WHERE id = ? AND type = 'employee'");
            $stmt->execute([$employee_id]);
            send_json_response('success', 'User session has been terminated.');
            break;

        // --- ACTION: GET LOGIN HISTORY FOR A USER ---
        case 'get_login_history':
            $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
            if (!$employee_id) {
                throw new Exception('Invalid employee ID.');
            }
            $stmt = $_conn_db->prepare("SELECT activity_type, ip_address, log_time FROM admin_activity_log WHERE admin_id = ? ORDER BY log_time DESC LIMIT 50");
            $stmt->execute([$employee_id]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_json_response('success', 'History fetched.', ['history' => $history]);
            break;

        default:
            throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    // Catch logical errors
    send_json_response('error', $e->getMessage());
} catch (PDOException $e) {
    // Catch database-specific errors
    error_log("Employee Action Error: " . $e->getMessage());
    send_json_response('error', 'A database error occurred. Please try again.');
}
?>