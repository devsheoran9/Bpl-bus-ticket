<?php
// generate_ticket_pdf.php
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload
include_once('function/_db.php'); // <-- DB CONNECTION MOVED HERE
 

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    die("Error: Invalid or missing Booking ID.");
}

// --- FIX: ALL DATABASE LOGIC NOW RESIDES IN THIS MAIN FILE ---
try {
    $stmt = $_conn_db->prepare("
        SELECT 
            b.*,
            r.route_name, r.route_id,
            sch.departure_time,
            bu.bus_name, bu.registration_number
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        JOIN buses bu ON b.bus_id = bu.bus_id
        LEFT JOIN route_schedules sch ON r.route_id = sch.route_id AND sch.operating_day = DATE_FORMAT(b.travel_date, '%a')
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        die("Booking details not found.");
    }
    $booking_details = $results[0];
} catch (PDOException $e) { 
    die("Database error: " . $e->getMessage()); 
}
// --- END OF DATABASE LOGIC ---


try {
    ob_start();
    
    // Now, we include the HTML template. 
    // The variables $booking_id, $results, and $booking_details are automatically available to it.
    include 'generate_ticket_html.php';
    
    $htmlContent = ob_get_clean();
    
    $html2pdf = new Html2Pdf('P', 'A4', 'en', true, 'UTF-8', [10, 10, 10, 10]);
    $html2pdf->writeHTML($htmlContent);
    $html2pdf->output('ticket_' . $booking_id . '.pdf', 'I');

} catch (Html2PdfException $e) {
    $formatter = new ExceptionFormatter($e);
    echo $formatter->getHtmlMessage();
}