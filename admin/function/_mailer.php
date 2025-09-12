<?php
// function/_mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Use a reliable path to the autoloader 

function sendBookingEmail($booking_id, $recipient_email, $_conn_db) {
    if (empty($recipient_email) || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        error_log("Attempted to send email to invalid address: " . $recipient_email);
        return ['status' => 'error', 'message' => 'Invalid email address provided.'];
    }

    try {
        // --- 1. Fetch ALL necessary data from the database ---
        // ... (This part of your code is correct) ...
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
            return ['status' => 'error', 'message' => 'Could not find booking details.'];
        }

        $passengersStmt = $_conn_db->prepare("SELECT passenger_name, seat_code, passenger_age, passenger_gender FROM passengers WHERE booking_id = ?");
        $passengersStmt->execute([$booking_id]);
        $passengers = $passengersStmt->fetchAll(PDO::FETCH_ASSOC);

        // --- 2. Load the HTML email template using a reliable absolute path ---
        // --- THIS IS THE FIX ---
        $template_path = __DIR__ . '/email_templates/ticket_template.html';
        
        if (!file_exists($template_path)) {
            error_log("Email template file not found at path: " . $template_path);
            return ['status' => 'error', 'message' => 'Email template file not found.'];
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

        // --- 5. Configure and Send Email with PHPMailer ---
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sjsheoran111@gmail.com';
        $mail->Password   = 'lfse ihcq eioa zwns';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('no-reply@yourcompany.com', 'BPL Bus Tickets');
        $mail->addAddress($recipient_email);
        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmed! Ticket No: ' . $booking['ticket_no'];
        $mail->Body    = $final_html;
        $mail->AltBody = 'Your booking is confirmed. Please view this email in an HTML-compatible client. Ticket No: ' . $booking['ticket_no'];

        $mail->send();
        return ['status' => 'success', 'message' => 'Email sent successfully.'];

    } catch (Exception $e) {
        error_log("Mailer Error for {$recipient_email}: " . $mail->ErrorInfo);
        return ['status' => 'error', 'message' => "Email could not be sent. Please check server logs."];
    }
}
?>