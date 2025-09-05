<?php
session_start();
require_once 'db_connect.php';

// If the user is already logged in through the session, do nothing.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    return;
}

// Check if "Remember Me" cookies are set
if (isset($_COOKIE['user_id']) && isset($_COOKIE['token'])) {
    $user_id = $_COOKIE['user_id'];
    $token = $_COOKIE['token'];

    // Look for the token in the database
    $stmt = $conn->prepare("SELECT token FROM users_login_token WHERE user_id = ? AND status = 1 ORDER BY date_time DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_token);
        $stmt->fetch();

        // Verify the token from the cookie against the hashed token from the database
        if (password_verify($token, $hashed_token)) { 
            $user_stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_stmt->bind_result($id, $username);
            $user_stmt->fetch();
            
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            
            $user_stmt->close();
        }
    }
    $stmt->close();
}
?>