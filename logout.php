<?php
 include "admin/function/_db.php";
 
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    try { 
        $stmt = $pdo->prepare("UPDATE users_login_token SET status = 2 WHERE user_id = ? AND status = 1");

        // Execute the statement by passing the parameters as an array.
        $stmt->execute([$user_id]);
    } catch (PDOException $e) { 
        error_log("Logout Error: " . $e->getMessage()); 
    }
}

// Unset all of the session variables.
$_SESSION = array();

// Destroy the session cookie.
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
 
session_destroy();
 
header("Location: login.php");
exit();
