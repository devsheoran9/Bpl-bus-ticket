<?php
session_start();
require 'db_connect.php';

// SECURITY: Check if the user is logged in and the request is a POST request.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- HANDLE PROFILE DETAILS UPDATE ---
if (isset($_POST['update_details'])) {
    $username = trim($_POST['username']);
    $mobile_no = trim($_POST['mobile_no']);
    $email = trim($_POST['email']);

    // Validation
    if (empty($username) || empty($mobile_no) || empty($email)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
    } else {
        // Prepare the update statement
        $stmt = $conn->prepare("UPDATE users SET username = ?, mobile_no = ?, email = ? WHERE id = ?");
        // The types are: s=string, s=string, s=string, i=integer
        $stmt->bind_param("sssi", $username, $mobile_no, $email, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Profile details updated successfully!";
            // Update the session username in case it was changed
            $_SESSION['username'] = $username;
        } else {
            $_SESSION['error_message'] = "Error updating record: " . $conn->error;
        }
        $stmt->close();
    }
    header("Location: profile.php");
    exit();
}

// --- HANDLE PASSWORD UPDATE ---
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Please fill in all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "New password and confirm password do not match.";
    } else {
        // Get the current hashed password from the DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify the current password
        if (password_verify($current_password, $hashed_password)) {
            // Hash the new password before storing it
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Prepare the update statement
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password_hash, $user_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Password changed successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating password: " . $conn->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Incorrect current password.";
        }
    }
    header("Location: profile.php");
    exit();
}

// If neither form was submitted, just redirect
header("Location: profile.php");
exit();
?>