<?php
// function/backend/staff_actions.php
header('Content-Type: application/json');
global $_conn_db;
include_once('../_db.php');
// session_start();

// --- THIS FUNCTION IS NOW CORRECTED ---
// It sends the JSON structure your frontend's global AJAX handler expects.
function send_response($res, $notif_type, $notif_title, $notif_desc, $goTo = '') {
    echo json_encode([
        'res' => $res, // 'true' or 'false'
        'notif_type' => $notif_type,
        'notif_title' => $notif_title,
        'notif_desc' => $notif_desc,
        'goTo' => $goTo
    ]);
    exit();
}

if (!isset($_SESSION['user']['login']) || $_SESSION['user']['login'] !== 'true') {
    send_response('false', 'danger', 'Access Denied', 'You must be logged in.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('false', 'danger', 'Error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'save_staff':
        $name = trim($_POST['name']);
        $mobile = trim($_POST['mobile']);
        $designation = trim($_POST['designation']);
        $driving_licence_no = ($designation === 'Driver') ? trim($_POST['driving_licence_no']) : null;
        $aadhar_no = trim($_POST['aadhar_no']);
        $remark = trim($_POST['remark']);
        $action_type = $_POST['action_type'];
        $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
        $profile_image_path = $_POST['existing_image'] ?? null;

        // File Upload Handling
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $base_path = dirname(__DIR__, 2);
            $target_dir = $base_path . "/uploads/staff_images/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $new_filename = "staff_" . ($staff_id ?: time()) . "_" . bin2hex(random_bytes(4)) . "." . $file_extension;
            $target_file_path = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file_path)) {
                $profile_image_path = $new_filename;
            } else {
                send_response('false', 'danger', 'Upload Failed', 'Could not move the uploaded file. Check directory permissions.');
            }
        }
        
        try {
            if ($action_type == 'update' && $staff_id) {
                $sql = "UPDATE staff SET name=?, mobile=?, designation=?, driving_licence_no=?, aadhar_no=?, profile_image_path=?, remark=? WHERE staff_id=?";
                $stmt = $_conn_db->prepare($sql);
                $stmt->execute([$name, $mobile, $designation, $driving_licence_no, $aadhar_no, $profile_image_path, $remark, $staff_id]);
                send_response('true', 'success', 'Success', 'Staff details updated successfully.', 'add_staff.php');
            } else {
                $sql = "INSERT INTO staff (name, mobile, designation, driving_licence_no, aadhar_no, profile_image_path, remark) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $_conn_db->prepare($sql);
                $stmt->execute([$name, $mobile, $designation, $driving_licence_no, $aadhar_no, $profile_image_path, $remark]);
                send_response('true', 'success', 'Success', 'New staff member added successfully.', 'add_staff.php');
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                send_response('false', 'danger', 'Duplicate Entry', 'A staff member with this Mobile, Aadhar, or Licence No. already exists.');
            }
            error_log("Staff Save Error: " . $e->getMessage());
            send_response('false', 'danger', 'Database Error', 'An unexpected error occurred.');
        }
        break;

    case 'delete_staff':
        $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
        if (!$staff_id) {
            send_response('false', 'warning', 'Invalid ID', 'Staff ID was not provided.');
        }
        try {
            $stmt = $_conn_db->prepare("DELETE FROM staff WHERE staff_id = ?");
            if ($stmt->execute([$staff_id])) {
                // We send a different response here for the delete handler
                echo json_encode(['status' => 'success', 'message' => 'The staff member has been deleted.']);
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete the staff member.']);
                exit();
            }
        } catch (PDOException $e) {
            error_log("Delete Staff Error: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'A database error occurred.']);
            exit();
        }
        break;

    default:
        send_response('false', 'warning', 'Unknown Action', 'The requested action is not valid.');
        break;
}
?>