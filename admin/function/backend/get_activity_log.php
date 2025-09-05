<?php
include('../_db.php');
header('Content-Type: application/json');

try {
    // Fetch the last 20 activities, newest first
    $stmt = $_conn_db->prepare("SELECT admin_name, activity_type, ip_address, log_time 
                                FROM admin_activity_log 
                                ORDER BY log_time DESC 
                                LIMIT 20");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $logs]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Could not fetch logs.']);
}
?>