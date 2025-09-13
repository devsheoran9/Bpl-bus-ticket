<?php
require "./admin/function/_db.php";
require 'config.php';
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $_SESSION['error_message'] = "Invalid email format provided.";
        header("Location: forgot_password");
        exit();
    }

    $stmt = $_conn_db->prepare("SELECT id FROM users WHERE email = :email AND status = 1");
    $stmt->execute([':email' => $email]);

    if ($stmt->rowCount() > 0) {
        $otp = rand(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $update_stmt = $_conn_db->prepare("UPDATE users SET otp = :otp, otp_expires_at = :expiry WHERE email = :email");
        $update_stmt->execute([
            ':otp' => $otp,
            ':expiry' => $otp_expiry,
            ':email' => $email
        ]);

        if ($update_stmt->rowCount() > 0) {
            $mail = new PHPMailer(true);
            try {
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

                // --- NEW: AWESOME HTML EMAIL TEMPLATE ---
                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset Code is ' . $otp;

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
            <h1>Password Reset Request</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>We received a request to reset the password for your account. Please use the One-Time Password (OTP) below to proceed.</p>
            
            <div class="otp-box">
                <p style="margin-bottom:10px; font-size:14px; color:#6B778C;">Your OTP is:</p>
                <p class="otp-code">$otp</p>
            </div>

            <p class="instruction">For convenience, you can select the code above to copy it.</p>
            <p>This code is valid for <strong>5 minutes</strong>. If you did not request a password reset, please ignore this email or contact our support team immediately.</p>
        </div>
    </div>
</body>
</html>
HTML;

                $mail->Body = $email_body;
                $mail->AltBody = "Your OTP for password reset is: $otp. This code will expire in 5 minutes.";

                $mail->send();

                $_SESSION['otp_email'] = $email;
                $_SESSION['message'] = "An OTP has been sent to your email address.";
                header("Location: verify_otp");
                exit();
            } catch (Exception $e) {
                $_SESSION['error_message'] = "OTP could not be sent due to a mail server error. Please contact support.";
                error_log("PHPMailer Error: " . $mail->ErrorInfo);
                header("Location: forgot_password");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Failed to generate OTP for your account. Please try again later.";
            header("Location: forgot_password");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "No active account found with that email address. Please enter correct email.";
        header("Location: forgot_password");
        exit();
    }
}
