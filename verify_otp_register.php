<?php 
header('Content-Type: application/json');

require './admin/function/_db.php';

global $pdo;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

try {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $user_otp = trim($_POST['otp'] ?? '');

    if (empty($email) || empty($user_otp)) {
        throw new Exception("Email or OTP is missing. Please try again.");
    }

    // Find the pending user by email
    $stmt = $pdo->prepare("SELECT id, otp, otp_expires_at FROM users WHERE email = ? AND status = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("No pending registration found for this email. Please register again.");
    }

    $current_time = date('Y-m-d H:i:s');

    // Check if OTP has expired
    if ($current_time > $user['otp_expires_at']) {
        throw new Exception("OTP has expired. Please request a new one by registering again.");
    }

    // Check if OTP is correct
    if ($user_otp != $user['otp']) {
        throw new Exception("The OTP you entered is incorrect.");
    }

    // --- OTP is valid, proceed to ACTIVATE user ---
    // Set status to 1 (Active) and clear the OTP fields for security
    $update_stmt = $pdo->prepare(
        "UPDATE users SET status = 1, otp = NULL, otp_expires_at = NULL WHERE id = ?"
    );
    $update_stmt->execute([$user['id']]);

    // Optional: Log the user in immediately after verification
    // $_SESSION['user_id'] = $user['id'];
    // $_SESSION['username'] = ... (you might want to fetch username here too)

    echo json_encode(['success' => true, 'redirectUrl' => 'login.php']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
