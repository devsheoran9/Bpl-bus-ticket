<?php
// logout.php
session_start();
include_once('function/_db.php');

if (isset($_SESSION['user']['id'])) {
    try {
        // Log the logout activity
        $log_stmt = $_conn_db->prepare("INSERT INTO admin_activity_log (admin_id, admin_name, activity_type, ip_address) VALUES (?, ?, 'logout', ?)");
        $log_stmt->execute([
            $_SESSION['user']['id'], $_SESSION['user']['name'], $_SERVER['REMOTE_ADDR']
        ]);

        // Clear the session token from the database
        $clear_token_stmt = $_conn_db->prepare("UPDATE admin SET session_token = NULL WHERE id = ?");
        $clear_token_stmt->execute([$_SESSION['user']['id']]);

    } catch (PDOException $e) {
        error_log("Failed to log admin logout: " . $e->getMessage());
    }
}

session_destroy();
header('Location: index.php');
exit();
?>