<?php
 
include_once('function/_db.php'); // Include your DB connection

// --- NEW CODE: ADD THIS ---
if (isset($_SESSION['user']['id'])) {
    try {
        $log_stmt = $_conn_db->prepare("INSERT INTO admin_activity_log (admin_id, admin_name, activity_type, ip_address) VALUES (?, ?, 'logout', ?)");
        $log_stmt->execute([
            $_SESSION['user']['id'],
            $_SESSION['user']['name'],
            $_SERVER['REMOTE_ADDR']
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log admin logout: " . $e->getMessage());
    }
}
// --- END OF NEW CODE ---

// Original logout code
session_destroy();
header('Location: index.php'); // Redirect to login page
exit();
?>