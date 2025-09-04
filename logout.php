<?php
session_start();
require 'db_connect.php';

// Check if a user is actually logged in before trying to log them out
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Set status to 2 (logged out) for all active tokens of this user
    // This ensures all sessions for this user are invalidated
    $stmt = $conn->prepare("UPDATE users_login_token SET status = 2 WHERE user_id = ? AND status = 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
