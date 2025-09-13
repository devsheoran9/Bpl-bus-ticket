<?php
// This is an AJAX endpoint, so it will only return JSON.
header('Content-Type: application/json');

require "./admin/function/_db.php";
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure a user is actually in the OTP process
if (!isset($_SESSION['otp_email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start over.']);
    exit();
}

$email = $_SESSION['otp_email'];

try {
    // Generate a new OTP and expiry time
    $otp = rand(100000, 999999);
    $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // Update the user's record with the new OTP and expiry time
    $update_stmt = $_conn_db->prepare("UPDATE users SET otp = :otp, otp_expires_at = :expiry WHERE email = :email");
    $update_stmt->execute([
        ':otp' => $otp,
        ':expiry' => $otp_expiry,
        ':email' => $email
    ]);

    if ($update_stmt->rowCount() > 0) {
        $mail = new PHPMailer(true);

        // Server settings from config
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        //Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($email);

        // --- USING THE SAME BEAUTIFUL HTML TEMPLATE ---
        $mail->isHTML(true);
        $mail->Subject = 'Your New Password Reset Code is ' . $otp;

        // This is the full HTML body, ensuring a consistent design
        $email_body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Password Reset OTP</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; background-color: #f4f5f7; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #172B4D; font-size: 24px; }
        .content p { color: #6B778C; line-height: 1.6; }
        .otp-box { background-color: #e3eeff; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0; }
        .otp-code { font-size: 36px; font-weight: 700; color: #0052CC; letter-spacing: 5px; margin: 0; }
        .instruction { text-align: center; font-size: 14px; color: #6B778C; }
        .footer { text-align: center; margin-top: 30px; color: #97A0AF; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Password Reset Code</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>As requested, here is a new One-Time Password (OTP) to proceed with resetting your password.</p>
            
            <div class="otp-box">
                <p style="margin-bottom:10px; font-size:14px; color:#6B778C;">Your new OTP is:</p>
                <p class="otp-code">$otp</p>
            </div>

            <p class="instruction">For convenience, you can select the code above to copy it.</p>
            <p>This new code is also valid for <strong>5 minutes</strong>. If you did not request this, please secure your account immediately.</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date("Y") . ' ' . MAIL_FROM_NAME . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;

        $mail->Body = $email_body;
        $mail->AltBody = "Your new OTP for password reset is: $otp. This code will expire in 5 minutes.";

        $mail->send();

        // Success: return the new expiry time so the JS timer can restart
        echo json_encode(['success' => true, 'message' => 'A new OTP has been sent.', 'otp_expires_at' => strtotime($otp_expiry) * 1000]);
    } else {
        throw new Exception('Failed to update OTP in the database.');
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Resend OTP Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Could not resend OTP. Please try again.']);
}
