<?php
// function/log_in.php

include('./_db.php');

// डिफ़ॉल्ट प्रतिक्रिया सेट करें
$response = [
    'page'        => 'login', 
    'res'         => 'false', 
    'notif_title' => 'Oops!', 
    'notif_desc'  => 'An unknown error occurred.',  
    'notif_type'  => 'danger', 
    'goTo'        => ''
];

// उपयोगकर्ता इनपुट को सुरक्षित करें
$username = trim($_POST["username"] ?? '');
$password = trim($_POST["password"] ?? '');

if (empty($username) || empty($password)) {
    $response['notif_desc'] = 'Username and password are required.';
    $response['notif_type'] = 'warning';
    echo json_encode($response);
    exit();
}

try {
    // डेटाबेस से उपयोगकर्ता की जानकारी प्राप्त करें
    $stmt = $_conn_db->prepare(
        "SELECT id, name, email, mobile, password, status, type, permissions 
         FROM admin 
         WHERE email = :emails OR mobile = :mobiles"
    );
    $stmt->execute(['emails' => $username, 'mobiles' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] == '1') {
            // सत्र टोकन उत्पन्न करें
            $session_token = bin2hex(random_bytes(32));

            // सत्र चर सेट करें
            $_SESSION['user'] = [
                'login'         => 'true',
                'id'            => (int)$user['id'],
                'name'          => $user['name'],
                'email'         => $user['email'],
                'mobile'        => $user['mobile'],
                'type'          => $user['type'],
                'permissions'   => json_decode($user['permissions'] ?? '[]', true),
                'session_token' => $session_token,
            ];

            $ip_address = $_SERVER['REMOTE_ADDR'];
            
            // admin टेबल में सत्र टोकन और अंतिम लॉगिन विवरण अपडेट करें
            $update_stmt = $_conn_db->prepare(
                "UPDATE admin SET session_token = ?, last_login_time = NOW(), last_login_ip = ? WHERE id = ?"
            );
            $update_stmt->execute([$session_token, $ip_address, $user['id']]);

            // गतिविधि लॉग में लॉगिन रिकॉर्ड करें
            $log_stmt = $_conn_db->prepare("INSERT INTO admin_activity_log (admin_id, admin_name, activity_type, ip_address) VALUES (?, ?, 'login', ?)");
            $log_stmt->execute([$user['id'], $user['name'], $ip_address]);
            
            // सफलता प्रतिक्रिया सेट करें
            $response = [
                'res'         => 'true',
                'notif_title' => 'Login Successful!',
                'notif_desc'  => 'Redirecting to your dashboard...',
                'notif_type'  => 'success',
                'goTo'        => 'dashboard.php'
            ];

        } else {
            $response['notif_title'] = 'Account Deactivated';
            $response['notif_desc'] = 'Your account is currently inactive. Please contact an administrator.';
        }
    } else {
        $response['notif_title'] = 'Invalid Credentials';
        $response['notif_desc'] = 'The email/mobile or password you entered is incorrect.';
    }

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    $response['notif_desc'] = 'A database error occurred during login. Please try again later.';
}

// JSON प्रतिक्रिया भेजें
header('Content-Type: application/json');
echo json_encode($response);
?>