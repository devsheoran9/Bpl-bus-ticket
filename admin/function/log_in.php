<?php 

require_once dirname(__DIR__) . '/vendor/autoload.php'; 
use WhichBrowser\Parser;

include('./_db.php');

$response = [
    'res' => 'false', 'notif_title' => 'Oops!', 'notif_desc' => 'An unknown error occurred.',  
    'notif_type' => 'danger', 'goTo' => ''
];

// --- GATHER DATA (NO webcam_image) ---
$username = trim($_POST["username"] ?? '');
$password = trim($_POST["password"] ?? '');
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;

$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$user_agent_string = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
$login_status = 'failed';

// --- DEVICE PARSING ---
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

if (empty($username) || empty($password)) {
    $response['notif_desc'] = 'Username and password are required.';
    echo json_encode($response);
    exit();
}

$user = null;
try {
    $stmt = $_conn_db->prepare("SELECT id, name, email, mobile, password, status, type, permissions FROM admin WHERE email = :emails OR mobile = :mobiles");
    $stmt->execute(['emails' => $username, 'mobiles' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] == '1') {
            $login_status = 'login';
            $session_token = bin2hex(random_bytes(32));
            $_SESSION['user'] = [
                'login' => 'true', 'id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email'],
                'mobile' => $user['mobile'], 'type' => $user['type'],
                'permissions' => json_decode($user['permissions'] ?? '[]', true), 'session_token' => $session_token,
            ];

            $update_stmt = $_conn_db->prepare("UPDATE admin SET session_token = ?, last_login_time = NOW(), last_login_ip = ? WHERE id = ?");
            $update_stmt->execute([$session_token, $ip_address, $user['id']]);
            
            $response = [
                'res' => 'true', 'notif_title' => 'Login Successful!', 'notif_desc' => 'Redirecting...',
                'notif_type' => 'success', 'goTo' => 'dashboard.php'
            ];
        } else {
            $login_status = 'deactivated_attempt';
            $response['notif_desc'] = 'Your account is inactive. Contact an administrator.';
        }
    } else {
        $login_status = 'failed_attempt';
        $response['notif_desc'] = 'The email/mobile or password you entered is incorrect.';
    }

} catch (PDOException $e) {
    error_log("Login DB Error: " . $e->getMessage());
    $response['notif_desc'] = 'A database error occurred.';
} finally {
    $user_id = $user['id'] ?? null;
    $user_name = $user['name'] ?? 'Attempt on: ' . $username;
    
    try {
        // --- UPDATED INSERT QUERY (NO captured_image) ---
        $log_stmt = $_conn_db->prepare(
            "INSERT INTO admin_activity_log 
                (admin_id, admin_name, activity_type, ip_address, user_agent, device_type, os, browser, geo_lat, geo_long) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $log_stmt->execute([
            $user_id, $user_name, $login_status, $ip_address, $user_agent_string,
            $device_info['device_type'], $device_info['os'], $device_info['browser'],
            $latitude,
            $longitude
        ]);
    } catch (PDOException $e) {
        error_log("Failed to log full admin activity: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);