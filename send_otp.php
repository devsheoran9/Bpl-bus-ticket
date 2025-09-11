<?php
require "./admin/function/_db.php";
require 'config.php';

require  'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

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

                $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset OTP';
                $mail->Body    = "Hello,<br><br>Your OTP for password reset is: <b>$otp</b><br>This code will expire in 5 minutes.";

                $mail->send();

                $_SESSION['otp_email'] = $email;
                $_SESSION['message'] = "An OTP has been sent to your email.";
                header("Location: verify_otp.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['error_message'] = "OTP could not be sent. Please contact support.";
                error_log("PHPMailer Error: " . $mail->ErrorInfo);
                header("Location: forgot_password.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Failed to generate OTP. Please try again.";
            header("Location: forgot_password.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "No active account found with that email address.";
        header("Location: forgot_password.php");
        exit();
    }
}
