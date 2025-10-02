<?php 

// Make sure the autoloader path is correct
require_once './vendor/autoload.php';
use WhichBrowser\Parser;

include_once('function/_db.php');

if (isset($_SESSION['user']['id'])) {
    // --- GATHER LOCATION & DEVICE DATA FROM THE POST REQUEST ---
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent_string = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

    // --- PARSE DEVICE INFO ---
    try {
        $parser = new Parser($user_agent_string);
        $device_info = [
            'browser' => $parser->browser->toString() ?? 'Unknown Browser',
            'os' => $parser->os->toString() ?? 'Unknown OS',
            'device_type' => $parser->device->type ?? 'desktop',
        ];
    } catch (Exception $e) {
        $device_info = ['browser' => 'Parse Error', 'os' => 'Parse Error', 'device_type' => 'unknown'];
    }

    try {
        $user_id = $_SESSION['user']['id'];
        $user_name = $_SESSION['user']['name'];

        // --- SAVE LOG TO DATABASE (WITHOUT IMAGE) ---
        $log_stmt = $_conn_db->prepare(
            "INSERT INTO admin_activity_log 
                (admin_id, admin_name, activity_type, ip_address, user_agent, device_type, os, browser, geo_lat, geo_long) 
             VALUES (?, ?, 'logout', ?, ?, ?, ?, ?, ?, ?)"
        );
        $log_stmt->execute([
            $user_id, $user_name, $ip_address, $user_agent_string,
            $device_info['device_type'], $device_info['os'], $device_info['browser'],
            $latitude,
            $longitude
        ]);

        // Clear the session token from the admin table
        $clear_token_stmt = $_conn_db->prepare("UPDATE admin SET session_token = NULL WHERE id = ?");
        $clear_token_stmt->execute([$user_id]);

    } catch (PDOException $e) {
        // Log the error but continue to log the user out
        error_log("Failed to log admin logout: " . $e->getMessage());
    }
}

// Finally, destroy the session and redirect
session_destroy();
header('Location: index.php'); // Redirect to your login page
exit();