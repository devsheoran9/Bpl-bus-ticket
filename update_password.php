<?php
require "./admin/function/_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['password_reset_user_id'])) {
        header("Location: login");
        exit();
    }

    $user_id = $_SESSION['password_reset_user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        die("Passwords do not match. Please go back and try again.");
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // FIX: PDO syntax for UPDATE
    $stmt = $_conn_db->prepare("UPDATE users SET password = :password, otp = NULL, otp_expires_at = NULL WHERE id = :id");

    if ($stmt->execute([':password' => $hashed_password, ':id' => $user_id])) {
        unset($_SESSION['password_reset_user_id']);
        unset($_SESSION['otp_email']);

        $_SESSION['success_message'] = "Your password has been updated successfully. Please login.";
        header("Location: login");
        exit();
    } else {
        die("Error updating password. Please try again.");
    }
}
