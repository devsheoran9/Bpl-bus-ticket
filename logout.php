<?php
session_start();
require 'db_connect.php';

// If a "Remember Me" cookie exists, update the token status in the database
if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
    // Set status to 2 (logged out) for all active tokens of this user
    $stmt = $conn->prepare("UPDATE user_login_token SET status = 2 WHERE user_id = ? AND status = 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear the "Remember Me" cookies
setcookie("user_id", "", time() - 3600, "/");
setcookie("token", "", time() - 3600, "/");

// Redirect to login page
header("Location: login.php");
exit();
?>