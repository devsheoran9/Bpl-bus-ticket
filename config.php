<?php
// PHPMailer SMTP Configuration
// Replace these with your own email server details.
// This example uses Gmail, but you can use any SMTP provider (SendGrid, Mailgun, etc.).

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password');   // Your Gmail App Password (NOT your regular password)
define('SMTP_PORT', 587); // Or 465 for SSL
define('SMTP_SECURE', 'tls'); // Use 'ssl' for port 465

// Email "From" address
define('MAIL_FROM_ADDRESS', 'your-email@gmail.com');
define('MAIL_FROM_NAME', 'Your Bus Booking'); // The name recipients will see

?>