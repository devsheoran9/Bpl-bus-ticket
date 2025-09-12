<?php
// email_ticket.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include necessary files
include_once('../_db.php');
require_once('../tcpdf/tcpdf.php'); // For generating the PDF
require_once('../PHPMailer/src/Exception.php'); // Adjust path to PHPMailer
require_once('../PHPMailer/src/PHPMailer.php');
require_once('../PHPMailer/src/SMTP.php');

// The `generateTicketPDF` function would need to be in a shared file or copied here
// For simplicity, I'll include it here. In a real app, put it in a helper file.
function generateTicketPDF($booking_details, $passengers) { /* ... copy the function from ticket_view.php ... */ }

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* handle error */ }

$booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
$email_to = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

if (!$booking_id || !$email_to) { die(json_encode(['status' => 'error', 'message' => 'Invalid input'])); }

// Fetch data (same as in ticket_view.php)
// ...

// Generate the PDF content as a string
$pdf_content = generateTicketPDF($booking_details, $passengers);

// Send the email
$mail = new PHPMailer(true);
try {
    //Server settings (use your own SMTP settings)
    $mail->isSMTP();
    $mail->Host       = 'smtp.example.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'user@example.com';
    $mail->Password   = 'secret';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('no-reply@yourcompany.com', 'BPL Bus Tickets');
    $mail->addAddress($email_to);

    //Attachments
    $mail->addStringAttachment($pdf_content, 'E-Ticket-'.$booking_id.'.pdf');

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Bus Ticket is Confirmed! Booking ID: ' . $booking_id;
    $mail->Body    = 'Dear Customer,<br><br>Your booking is confirmed. Please find your e-ticket attached to this email.<br><br>Thank you for choosing us!';

    $mail->send();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>