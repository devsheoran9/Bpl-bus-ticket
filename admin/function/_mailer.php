<?php
// function/_mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Assuming your config file with constants is included via _db.php or a similar central file.
// If not, you would need: require_once(__DIR__ . '/config.php');

/**
 * Sends a booking confirmation email with an HTML template.
 *
 * @param int $booking_id The ID of the booking.
 * @param string $recipient_email The email address to send the ticket to.
 * @param object $_conn_db The PDO database connection object.
 * @return array ['status' => 'success'|'error', 'message' => '...']
 */
function sendBookingEmail($booking_id, $recipient_email, $_conn_db) {
    if (empty($recipient_email) || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        error_log("Attempted to send email to invalid address: " . $recipient_email);
        return ['status' => 'error', 'message' => 'Invalid email address provided.'];
    }

    try {
        // --- 1. Fetch ALL necessary data from the database ---
        $stmt = $_conn_db->prepare("
            SELECT b.ticket_no, b.travel_date, b.total_fare, b.origin, b.destination, r.route_name, sch.departure_time, bu.bus_name, bu.registration_number
            FROM bookings b
            JOIN routes r ON b.route_id = r.route_id
            JOIN buses bu ON b.bus_id = bu.bus_id
            LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
            WHERE b.booking_id = ?
        ");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return ['status' => 'error', 'message' => 'Could not find booking details for email.'];
        }

        $passengersStmt = $_conn_db->prepare("SELECT passenger_name, seat_code, passenger_age, passenger_gender FROM passengers WHERE booking_id = ?");
        $passengersStmt->execute([$booking_id]);
        $passengers = $passengersStmt->fetchAll(PDO::FETCH_ASSOC);

        // --- 2. Load the HTML email template ---
        $template_path = __DIR__ . '/email_templates/ticket_template.html';
        
        if (!file_exists($template_path)) {
            error_log("Email template file not found at path: " . $template_path);
            return ['status' => 'error', 'message' => 'Email template file could not be found.'];
        }
        $html_template = file_get_contents($template_path);

        // --- 3. Build the dynamic passenger rows HTML ---
        $passenger_rows_html = '';
        foreach ($passengers as $p) {
            $passenger_rows_html .= '
                <tr style="border-bottom: 1px solid #dfe1e6;">
                    <td style="padding: 12px; font-size: 14px;">' . htmlspecialchars($p['passenger_name']) . '</td>
                    <td style="padding: 12px; font-size: 14px;">' . htmlspecialchars($p['passenger_age'] ?? 'N/A') . '</td>
                    <td style="padding: 12px; font-size: 14px;">' . htmlspecialchars($p['passenger_gender']) . '</td>
                    <td style="padding: 12px; font-size: 14px; text-align: right; font-weight: bold;">' . htmlspecialchars($p['seat_code']) . '</td>
                </tr>';
        }

        // --- 4. Replace all placeholders in the template ---
        $replacements = [
            '{{contact_name}}'      => htmlspecialchars($passengers[0]['passenger_name'] ?? 'Customer'),
            '{{ticket_no}}'         => htmlspecialchars($booking['ticket_no']),
            '{{travel_date}}'       => date('D, d M Y', strtotime($booking['travel_date'])),
            '{{bus_details}}'       => htmlspecialchars($booking['bus_name'] . ' (' . $booking['registration_number'] . ')'),
            '{{total_fare}}'        => number_format($booking['total_fare'], 2),
            '{{boarding_from}}'     => htmlspecialchars($booking['origin']),
            '{{dropping_at}}'       => htmlspecialchars($booking['destination']),
            '{{passenger_rows}}'    => $passenger_rows_html,
        ];
        $final_html = str_replace(array_keys($replacements), array_values($replacements), $html_template);

        // --- 5. Configure and Send Email with PHPMailer using Constants ---
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOSTT;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAMEE;
        $mail->Password   = SMTP_PASSWORDD;
        $mail->SMTPSecure = SMTP_SECUREE;
        $mail->Port       = SMTP_PORTT;
        
        $mail->setFrom(MAIL_FROM_ADDRESSS, MAIL_FROM_NAMEE);
        $mail->addAddress($recipient_email);
        
        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmed! Ticket No: ' . $booking['ticket_no'];
        $mail->Body    = $final_html;
        $mail->AltBody = 'Your booking is confirmed. Please view this email in an HTML-compatible client. Ticket No: ' . $booking['ticket_no'];

        $mail->send();
        return ['status' => 'success', 'message' => 'Confirmation email sent successfully.'];

    } catch (Exception $e) {
        error_log("Mailer Error for {$recipient_email}: " . $mail->ErrorInfo);
        return ['status' => 'error', 'message' => "Email could not be sent. Please check server logs."];
    }
}


/**
 * Sends an email notification about the status of a cancellation request.
 *
 * @param int $cancellation_id The ID of the cancellation.
 * @param object $_conn_db The PDO database connection object.
 * @return array ['status' => 'success'|'error'|'skipped', 'message' => '...']
 */
function sendCancellationStatusEmail($cancellation_id, $_conn_db) {
    try {
        // Fetch all necessary details for the email, including the contact_email
        $stmt = $_conn_db->prepare("
            SELECT 
                b.contact_email, b.ticket_no,
                p.passenger_name, p.seat_code,
                c.status AS refund_status, c.amount_refunded, c.cancellation_reason, c.gateway_refund_id
            FROM cancellations c
            JOIN bookings b ON c.booking_id = b.booking_id
            JOIN passengers p ON c.passenger_id = p.passenger_id
            WHERE c.cancellation_id = ?
        ");
        $stmt->execute([$cancellation_id]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$details || empty($details['contact_email'])) {
            return ['status' => 'skipped', 'message' => 'Email not sent: No contact email address found for this booking.'];
        }
        
        $recipient_email = $details['contact_email'];
        $subject = '';
        $body = '';

        // Customize email content based on the refund status
        if ($details['refund_status'] === 'COMPLETED') {
            $subject = "Refund Processed for Ticket #" . $details['ticket_no'];
            $body = "
                <p>Dear Customer,</p>
                <p>We have successfully processed your refund for the cancellation of the ticket for passenger <strong>{$details['passenger_name']}</strong> (Seat: {$details['seat_code']}).</p>
                <p>A refund amount of <strong>₹{$details['amount_refunded']}</strong> has been initiated.</p>
                <p>Refund Transaction ID: <strong>" . htmlspecialchars($details['gateway_refund_id'] ?: 'N/A') . "</strong></p>
                <p>It may take 5-7 business days for the amount to reflect in your account.</p>
                <p>Thank you.</p>
            ";
        } elseif ($details['refund_status'] === 'FAILED') {
            $subject = "Important: Refund Failed for Ticket #" . $details['ticket_no'];
            $body = "
                <p>Dear Customer,</p>
                <p>We regret to inform you that the refund for the cancellation of the ticket for passenger <strong>{$details['passenger_name']}</strong> (Seat: {$details['seat_code']}) could not be processed.</p>
                <p>Reason: <strong>" . htmlspecialchars($details['cancellation_reason']) . "</strong></p>
                <p>Please contact our customer support for further assistance.</p>
                <p>We apologize for the inconvenience.</p>
            ";
        } else {
            return ['status' => 'skipped', 'message' => 'Email not sent for this status.'];
        }

        $mail = new PHPMailer(true);
        
        // --- UPDATED TO USE DEFINED CONSTANTS ---
        $mail->isSMTP();
        $mail->Host       = SMTP_HOSTT;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAMEE;
        $mail->Password   = SMTP_PASSWORDD;
        $mail->SMTPSecure = SMTP_SECUREE;
        $mail->Port       = SMTP_PORTT;

        //Recipients
        $mail->setFrom(MAIL_FROM_ADDRESSS, MAIL_FROM_NAMEE);
        $mail->addAddress($recipient_email);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();

        return ['status' => 'success', 'message' => 'Status notification email sent successfully.'];

    } catch (Exception $e) {
        error_log("Cancellation Email Error for ID {$cancellation_id}: " . $mail->ErrorInfo);
        return ['status' => 'error', 'message' => 'Failed to send email: ' . $mail->ErrorInfo];
    }
}
?>