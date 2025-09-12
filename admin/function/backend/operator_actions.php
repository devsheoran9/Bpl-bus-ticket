<?php
// function/backend/operator_actions.php
header('Content-Type: application/json');
global $_conn_db;
include_once('../_db.php');
// session_start(); 

// Helper function to send consistent JSON responses for your AJAX script
function send_response($res, $notif_type, $notif_title, $notif_desc, $goTo = '') {
    echo json_encode([
        'res' => $res,
        'notif_type' => $notif_type,
        'notif_title' => $notif_title,
        'notif_desc' => $notif_desc,
        'goTo' => $goTo
    ]);
    exit();
}

// Security Check (ensure user is logged in, you can add role checks later)
if (!isset($_SESSION['user']['login']) || $_SESSION['user']['login'] !== 'true') {
    send_response('false', 'danger', 'Access Denied', 'You must be logged in to perform this action.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('false', 'danger', 'Error', 'Invalid request method.');
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'save_operator':
        // These variable names MUST match the `name` attributes in the HTML form
        $operator_name = trim($_POST['operator_name']);
        $contact_person = trim($_POST['contact_person']);
        $contact_phone = trim($_POST['contact_phone']);
        $contact_email = trim($_POST['contact_email']);
        $address = trim($_POST['address']);
        $status = $_POST['status'];
        
        $action_type = $_POST['action_type'];
        $operator_id = filter_input(INPUT_POST, 'operator_id', FILTER_VALIDATE_INT);

        if (empty($operator_name)) {
            send_response('false', 'warning', 'Validation Error', 'Driver Name is a required field.');
        }

        try {
            if ($action_type == 'update' && $operator_id) {
                // UPDATE query now includes all fields from the form
                $sql = "UPDATE operators SET operator_name = ?, contact_person = ?, contact_email = ?, contact_phone = ?, address = ?, status = ? WHERE operator_id = ?";
                $stmt = $_conn_db->prepare($sql);
                $stmt->execute([$operator_name, $contact_person, $contact_email, $contact_phone, $address, $status, $operator_id]);
                send_response('true', 'success', 'Success', 'Details updated successfully.', 'add_operator.php');
            } else {
                // INSERT query now includes all fields from the form
                $sql = "INSERT INTO operators (operator_name, contact_person, contact_email, contact_phone, address, status) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $_conn_db->prepare($sql);
                $stmt->execute([$operator_name, $contact_person, $contact_email, $contact_phone, $address, $status]);
                send_response('true', 'success', 'Success', 'New record added successfully.', 'add_operator.php');
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                send_response('false', 'danger', 'Duplicate Entry', 'An operator with this name might already exist.');
            }
            error_log("Operator Save Error: " . $e->getMessage());
            send_response('false', 'danger', 'Database Error', 'An unexpected error occurred. Please check server logs.');
        }
        break;

    case 'delete_operator':
        $operator_id = filter_input(INPUT_POST, 'operator_id', FILTER_VALIDATE_INT);
        if (!$operator_id) {
            send_response('false', 'warning', 'Invalid ID', 'Operator ID was not provided.');
        }

        try {
            $check_stmt = $_conn_db->prepare("SELECT COUNT(*) FROM buses WHERE operator_id = ?");
            $check_stmt->execute([$operator_id]);
            if ($check_stmt->fetchColumn() > 0) {
                 send_response('false', 'danger', 'Deletion Failed', 'Cannot delete: This driver is assigned to one or more buses.');
            } else {
                $delete_stmt = $_conn_db->prepare("DELETE FROM operators WHERE operator_id = ?");
                if ($delete_stmt->execute([$operator_id])) {
                    send_response('true', 'success', 'Deleted!', 'The record has been deleted successfully.');
                } else {
                    send_response('false', 'danger', 'Error', 'Failed to delete the record from the database.');
                }
            }
        } catch (PDOException $e) {
            error_log("Delete Operator Error: " . $e->getMessage());
            send_response('false', 'danger', 'Database Error', 'Could not delete the record.');
        }
        break;

    default:
        send_response('false', 'warning', 'Unknown Action', 'The requested action is not valid.');
        break;
}
?>