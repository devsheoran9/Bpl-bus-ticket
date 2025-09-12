<?php 
header('Content-Type: application/json');

require './admin/vendor/autoload.php';
require 'config.php';
require './admin/function/_db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

global $pdo;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

try {
    $username = trim($_POST['username'] ?? '');
    $mobile_no = trim($_POST['mobile_no'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '::1';

    // Validation
    if (empty($username) || empty($mobile_no) || empty($email) || empty($password)) {
        throw new Exception("Please fill in all required fields.");
    }
    if (!preg_match('/^[0-9]{10}$/', $mobile_no)) {
        throw new Exception("Please enter a valid 10-digit mobile number.");
    }
    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters long.");
    }

    // Check if an ACTIVE user already exists with this email or mobile
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR mobile_no = ?) AND status = 1");
    $stmt->execute([$email, $mobile_no]);
    if ($stmt->fetch()) {
        throw new Exception("This email or mobile number is already registered and verified.");
    }

    // Generate OTP and other details
    $otp = random_int(100000, 999999);
    $otp_expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if a PENDING user exists to UPDATE, otherwise INSERT
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status = 0");
    $stmt->execute([$email]);
    $pending_user = $stmt->fetch();

    if ($pending_user) {
        // A pending user exists, UPDATE their details with a new OTP
        $update_stmt = $pdo->prepare(
            "UPDATE users SET username = ?, password = ?, mobile_no = ?, otp = ?, otp_expires_at = ?, ip_address = ? WHERE id = ?"
        );
        $update_stmt->execute([$username, $hashed_password, $mobile_no, $otp, $otp_expires_at, $ip_address, $pending_user['id']]);
    } else {
        // No pending user, INSERT a new one with status 0 (pending)
        $insert_stmt = $pdo->prepare(
            "INSERT INTO users (username, password, mobile_no, email, ip_address, status, otp, otp_expires_at, created_at) 
             VALUES (?, ?, ?, ?, ?, 0, ?, ?, NOW())"
        );
        $insert_stmt->execute([$username, $hashed_password, $mobile_no, $email, $ip_address, $otp, $otp_expires_at]);
    }

    // Send OTP email
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;

    $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
    $mail->addAddress($email, $username);
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP for BPL Bus Registration';
    $mail->Body = "Dear {$username},<br><br>Your One-Time Password (OTP) to complete your registration is: <b>{$otp}</b><br><br>This OTP is valid for 5 minutes.<br><br>Thank you,<br>The BPL Bus Team";

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'OTP sent successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $t) {
    error_log($t);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Could not send email.']);
}
